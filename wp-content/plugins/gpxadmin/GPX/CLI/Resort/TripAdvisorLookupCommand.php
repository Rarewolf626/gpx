<?php

namespace GPX\CLI\Resort;

use Exception;
use GPX\Api\TripAdvisor\TripAdvisor;
use GPX\CLI\BaseCommand;
use GPX\Model\Resort;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TripAdvisorLookupCommand extends BaseCommand
{
    private TripAdvisor $api;

    public function __construct(TripAdvisor $api)
    {
        $this->api = $api;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('resort:tripadvisor');
        $this->setDescription('Looks up trip advisor data for resorts');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = $this->io($input, $output);
        $this->io->title('Populate missing trip advisor locations');

        Resort::query()
            ->where('taID', '=', 0)
            ->where(fn($query) => $query
                ->where('LatitudeLongitude', '!=', '')
                ->orWhere(fn($query) => $query
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                )

            )
            ->each(function (Resort $resort) {
                $this->io->section(sprintf('Resort %s', $resort->id));
                $this->io->writeln($resort->ResortName);
                $this->io->writeln(sprintf('%s %s', $resort->Address1, $resort->Address2));
                $this->io->writeln(sprintf('%s, %s  %s, %s', $resort->Town, $resort->Region, $resort->PostCode, $resort->Country));
                if ($resort->latitude && $resort->longitude) {
                    $latitude = $resort->latitude;
                    $longitude = $resort->longitude;
                } else {
                    [$latitude, $longitude] = explode(',', $resort->LatitudeLongitude);
                }
                $this->io->writeln(sprintf('Coordinates: %s, %s', $latitude, $longitude));
                $this->io->newLine();
                $this->io->writeln('<header>Looking up trip advisor data</header>');
                try {
                    $locations = $this->api->search($latitude, $longitude, $resort->ResortName);
                    $location = Arr::first($locations->data);
                    if (!$location) {
                        $this->io->warning('Could not find any locations from trip advisor');
                        return;
                    }
                    //dd($location);
                    $this->io->newLine();
                    $this->io->writeln('<info>Found location</info>');
                    $this->io->writeln($location->location_id);
                    $this->io->writeln($location->name);
                    $this->io->writeln($location->address_obj->address_string);

                    //$resort->update(['taID' => $location->location_id]);
                    $this->io->success(sprintf('Set TripAdvisor location id to %d', $location->location_id));
                } catch (Exception $e) {
                    $this->io->success('Could not find location from trip advisor');
                    $this->io->error($e);
                    //$resort->update(['taID' => 1]);
                }
            });
    }
}
