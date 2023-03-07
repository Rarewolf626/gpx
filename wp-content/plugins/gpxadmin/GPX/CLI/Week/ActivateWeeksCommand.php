<?php

namespace GPX\CLI\Week;

use GPX\CLI\BaseCommand;
use Illuminate\Support\Carbon;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActivateWeeksCommand extends BaseCommand
{
    protected LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = gpx_logger();
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('week:activate');
        $this->setDescription('Activates weeks');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->io($input, $output);
        $io->title('Activate Weeks');
        global $wpdb;
        $today = Carbon::now();
        $checkin = Carbon::now()->addWeek();

        $io->section(sprintf("Activate weeks with checkin >= %s and scheduled for <= %s", $checkin->format('m/d/Y'), $today->format('m/d/Y')));
        $sql = $wpdb->prepare("UPDATE `wp_room` r
            SET r.`active` = 1
            WHERE r.check_in_date >= %s
              AND r.`active_specific_date` BETWEEN '2020-12-06' AND %s
              AND r.`active` = 0
              AND r.archived = 0
              AND NOT EXISTS(SELECT t.weekId FROM wp_gpxTransactions t WHERE t.weekId = r.record_id AND t.cancelled != 1 LIMIT 1)
              AND NOT EXISTS(SELECT h.weekId FROM wp_gpxPreHold h WHERE h.weekId = r.record_id AND h.released = 0 LIMIT 1)
              ", [$checkin->format('Y-m-d'), $today->format('Y-m-d')]);
        $io->writeln($sql);
        $added = $wpdb->query($sql);
        $io->success(sprintf("Activated %d weeks", $added));

        $io->section(sprintf("Deactivate weeks with checkin < %s", $checkin->format('m/d/Y')));
        $sql = $wpdb->prepare("UPDATE wp_room SET active = 0 WHERE active = 1 AND check_in_date < %s", $checkin->format('Y-m-d'));
        $io->writeln($sql);
        $removed = $wpdb->query($sql);
        $io->success(sprintf("Deactivated %d weeks", $removed));


        return Command::SUCCESS;
    }
}
