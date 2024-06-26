<?php

namespace GPX\CLI\Resort;

use GPX\CLI\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixResortMetaDescriptions extends BaseCommand {

    private array $fields = [
        'AreaDescription', 'UnitDescription', 'AdditionalInfo', 'Description', 'Website', 'CheckInDays',
        'CheckInEarliest', 'CheckInLatest', 'CheckOutEarliest', 'CheckOutLatest', 'Address1', 'Address2',
        'Town', 'Region', 'Country', 'PostCode', 'Phone', 'Fax', 'Airport', 'Directions',
    ];

    protected function configure(): void
    {
        $this->setName('resort:descriptions:fix');
        $this->setDescription('Deletes the old broken resort meta descriptions');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $wpdb;
        $this->io = $this->io($input, $output);
        $this->io->title('Fixing resort descriptions');

        $sql = "SELECT `id`,`ResortID`,`ResortName`, (SELECT `meta_value` FROM `wp_resorts_meta` WHERE `ResortID` = `wp_resorts`.`ResortID` AND `meta_key` = 'ResortMetaBackup' LIMIT 1) as Backup FROM `wp_resorts` WHERE
                                             EXISTS(SELECT `id` FROM `wp_resorts_meta` WHERE `ResortID` = `wp_resorts`.`ResortID` AND `meta_key` = 'ResortMetaBackup')
                                             AND NOT EXISTS(SELECT `id` FROM `wp_resorts_meta` WHERE `ResortID` = `wp_resorts`.`ResortID` AND `meta_key` = 'ResortMetaBackupFix')
                                         ";
        $resorts = $wpdb->get_results($sql, ARRAY_A);
        foreach($resorts as $resort){
            $this->io->section(sprintf('Resort %s: %s', $resort['ResortID'], $resort['ResortName']));
            $backup = json_decode($resort['Backup'], true);
            $fields = array_keys($backup);
            $placeholders = gpx_db_placeholders($fields);
            $sql = $wpdb->prepare("SELECT id,meta_key,meta_value FROM wp_resorts_meta WHERE ResortID = %s AND meta_key IN ({$placeholders})", [$resort['ResortID'], ...$fields]);
            $meta = $wpdb->get_results($sql, ARRAY_A);
            if(!$meta){
                $this->io->warning('No meta data needs cleaning');
                continue;
            }
            $sql = $wpdb->prepare("INSERT INTO `wp_resorts_meta` SET `meta_key` = 'ResortMetaBackupFix', ResortID=%s, `meta_value`=%s", [$resort['ResortID'], json_encode($meta)]);
            $wpdb->query($sql);
            $meta_ids = array_column($meta, 'id');
            $placeholders = gpx_db_placeholders($meta_ids);
            $sql = $wpdb->prepare("DELETE FROM `wp_resorts_meta` WHERE `id` IN ({$placeholders})", $meta_ids);
            $wpdb->query($sql);
            $this->io->success('Deleted old meta fields');
        }

        return static::SUCCESS;
    }
}
