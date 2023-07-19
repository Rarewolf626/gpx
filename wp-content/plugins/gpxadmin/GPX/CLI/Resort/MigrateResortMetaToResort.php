<?php

namespace GPX\CLI\Resort;

use GPX\CLI\BaseCommand;
use GPX\Model\Resort;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateResortMetaToResort extends BaseCommand
{

    private array $fields = [
        'AreaDescription', 'UnitDescription', 'AdditionalInfo', 'Description', 'Website', 'CheckInDays',
        'CheckInEarliest', 'CheckInLatest', 'CheckOutEarliest', 'CheckOutLatest', 'Address1', 'Address2',
        'Town', 'Region', 'Country', 'PostCode', 'Phone', 'Fax', 'Airport', 'Directions',
    ];

    protected function configure(): void
    {
        $this->setName('resort:migrate:descriptions');
        $this->setDescription('Copies current description data from the wp_resorts_meta table to the wp_resorts table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = $this->io($input, $output);
        $this->io->title('Migrating resort descriptions');

        Resort::select(['id', 'ResortID', 'ResortName', ...$this->fields])
            ->each(function (Resort $resort) {
                $this->io->section(sprintf('Resort %s: %s', $resort->ResortID, $resort->ResortName));
                $this->backupCurrentData($resort);
                $meta = $this->getResortMeta($resort->ResortID);
                if (empty($meta)) {
                    $this->io->warning('No meta data needs updating');
                    return;
                }
                $resort->update($meta);
                $this->io->success('Updated resort fields');
                $this->io->listing(array_keys($meta));
            });


        return static::SUCCESS;
    }

    private function getResortMeta(string $resort_id): array
    {
        global $wpdb;
        $placeholders = gpx_db_placeholders($this->fields);
        $sql = $wpdb->prepare("SELECT meta_key,meta_value FROM wp_resorts_meta WHERE ResortID = %s AND meta_key IN ({$placeholders})", [$resort_id, ...$this->fields]);
        $meta = $wpdb->get_results($sql, OBJECT_K);
        return array_map([$this, 'getCurrentMetaValue'], $meta);
    }

    private function getCurrentMetaValue($meta): string
    {
        $value = json_decode($meta->meta_value, true);
        if (empty($value)) {
            return '';
        }
        ksort($value);
        $value = Arr::last($value);
        if (Arr::isList($value)) {
            $value = Arr::last($value);
        }
        return stripslashes_from_strings_only($value['desc'] ?? '');
    }

    private function backupCurrentData(Resort $resort)
    {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM wp_resorts_meta WHERE ResortID = %s AND meta_key = 'ResortMetaBackup'", [$resort->ResortID]);
        $current = $wpdb->get_row($sql, ARRAY_A);
        if ($current) {
            // already backed up,
            $this->io->info('Current data already backed up');
            return;
        }
        $wpdb->insert('wp_resorts_meta', [
            'ResortID' => $resort->ResortID,
            'meta_key' => 'ResortMetaBackup',
            'meta_value' => json_encode($resort->only($this->fields)),
        ]);
        $this->io->info('Current data backed up');
    }
}
