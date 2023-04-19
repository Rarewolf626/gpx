<?php

namespace GPX\CLI\Search;

use GPX\CLI\BaseCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateSearchColumnsCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('search:column');
        $this->setDescription('Populates search columns');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $wpdb;
        $sql = "SELECT id, IF(IFNULL(displayName, '') != '', displayName, IF(IFNULL(subName, '') != '', subName, IF(IFNULL(name, '') != '', name, ''))) as name FROM wp_gpxRegion";
        $records = $wpdb->get_results($sql, ARRAY_A);
        foreach ($records as $record) {
            $name = gpx_search_string($record['name']);
            $wpdb->update('wp_gpxRegion', ['search_name' => $name], ['id' => $record['id']]);
        }

        $sql = "SELECT id, country as name FROM wp_gpxCategory";
        $records = $wpdb->get_results($sql, ARRAY_A);
        foreach ($records as $record) {
            $name = gpx_search_string($record['name']);
            $wpdb->update('wp_gpxCategory', ['search_name' => $name], ['id' => $record['id']]);
        }

        $sql = "SELECT id, ResortName as name FROM wp_resorts";
        $records = $wpdb->get_results($sql, ARRAY_A);
        foreach ($records as $record) {
            $name = gpx_search_string($record['name']);
            $wpdb->update('wp_resorts', ['search_name' => $name], ['id' => $record['id']]);
        }

        return Command::SUCCESS;
    }
}
