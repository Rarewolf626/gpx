<?php

namespace GPX\CLI\Salesforce\Resort;

use GPX\Api\Salesforce\Salesforce;
use GPX\Api\Salesforce\SalesforceException;
use GPX\CLI\BaseCommand;
use GPX\Model\Resort;
use GPX\Repository\ResortRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushMissingResortsCommand extends BaseCommand
{
    protected Salesforce $sf;
    protected LoggerInterface $logger;
    private ResortRepository $repository;

    public function __construct(Salesforce $sf)
    {
        $this->sf = $sf;
        $this->logger = gpx_logger();
        $this->repository = ResortRepository::instance();
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('sf:resort:push');
        $this->setDescription('Pushes any missing resorts to salesforce');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $wpdb;
        $io = $this->io($input, $output);
        $io->title('Pull resorts not connected to salesforce');

        Resort::where(fn($query) => $query
            ->orWhereRaw("(sf_GPX_Resort__c IS NULL OR sf_GPX_Resort__c = '')")
            ->orWhereRaw("(gprID IS NULL OR gprID = '')")
        )
            ->orderBy('id')
            ->each(function (Resort $resort) use ($wpdb, $io) {
                $io->section($resort->ResortName);
                if (!$resort->gprID) {
                    $io->writeln('Does not have a gprID');
                    $query = $wpdb->prepare("SELECT Id, Name from Resort__c WHERE Name = %s LIMIT 1", $resort->ResortName);
                    $resorts = $this->sf->query($query);
                    if (!empty($resorts)) {
                        $io->writeln('Was found in salesforce, set gprID');
                        $resort->update([
                            'gprID' => $resorts[0]->Id,
                        ]);
                    } else {
                        $io->warning('Was not found in salesforce, skip for now');
                    }
                }
                if (!$resort->sf_GPX_Resort__c) {
                    $io->writeln('Does not have a sf_GPX_Resort__c');
                    $query = $wpdb->prepare("SELECT Id, Name from GPX_Resort__c WHERE Name = %s LIMIT 1", $resort->ResortName);
                    $resorts = $this->sf->query($query);
                    if (!empty($resorts)) {
                        $io->writeln('Was found in salesforce, set sf_GPX_Resort__c');
                        $resort->update([
                            'sf_GPX_Resort__c' => $resorts[0]->Id,
                        ]);
                    } else {
                        $io->writeln('Was not found in salesforce, send to salesforce');
                        try {
                            $sfid = $this->repository->send_to_salesforce($resort);
                            $io->success(sprintf('Sent to salesforce with id %s', $sfid));
                        } catch (\Exception $e) {
                            $io->error('Failed to send to salesforce');
                            if ($e instanceof SalesforceException) {
                                $io->warning(print_r($e->response(), true));
                            }
                            $io->writeln($e);
                        }
                    }
                }
            });

        return Command::SUCCESS;
    }
}
