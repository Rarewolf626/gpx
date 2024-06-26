<?php

namespace GPX\CLI\CustomRequests;

use GPX\CLI\BaseCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixCustomRequestEscapingCommand extends BaseCommand
{

    protected bool $debug = false;
    protected ?SymfonyStyle $io;

    protected function configure(): void
    {
        $this->setName('request:fix-escape');
        $this->setDescription('Fixes double escaped custom requests');
        $this->setHelp('Fixes double escaped custom requests');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Fix double escaped custom requests');
        $this->io->writeln(date('m/d/Y g:i:s A'));

        global $wpdb;
        $sql = "select id,region,city,resort from wp_gpxCustomRequest where resort like '%\\\\\\\\%' OR region like '%\\\\\\\\%' or city like '%\\\\\\\\%'";
        $requests = $wpdb->get_results($sql, ARRAY_A);
        if (empty($requests)) {
            $this->io->success('There are no custom requests to fix');

            return Command::SUCCESS;
        }
        $this->io->writeln(sprintf('Fixing %d custom requests', count($requests)));

        foreach ($requests as $request) {
            $fixed = [
                'region' => stripslashes($request['region']),
                'city' => stripslashes($request['city']),
                'resort' => stripslashes($request['resort']),
            ];

            $this->io->horizontalTable(
                ['', 'Region', 'City', 'Resort'],
                [
                    ['Original', $request['resort'], $request['region'], $request['city']],
                    ['Fixed', $fixed['resort'], $fixed['region'], $fixed['city']]
                ]
            );
            $wpdb->update('wp_gpxCustomRequest', $fixed, ['id' => $request['id']]);
        }

        return Command::SUCCESS;
    }
}
