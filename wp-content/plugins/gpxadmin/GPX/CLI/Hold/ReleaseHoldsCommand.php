<?php

namespace GPX\CLI\Hold;

use DB;
use GPX\Model\Week;
use GPX\Model\PreHold;
use GPX\CLI\BaseCommand;
use Illuminate\Support\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReleaseHoldsCommand extends BaseCommand {

    protected function configure(): void {
        $this->setName('hold:release');
        $this->setDescription('Release expired holds');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {

        $this->releaseHolds($input, $output);
        $this->checkBookedWeeks($input, $output);

        return Command::SUCCESS;
    }

    private function releaseHolds(InputInterface $input, OutputInterface $output) {
        $io = $this->io($input, $output);
        $io->title('Release expired holds');
        $now = Carbon::now();
        $io->info(sprintf('Checking holds to release before %s', $now->format('Y-m-d H:i:s')));

        PreHold::select([
            'wp_gpxPreHold.*',
            'wp_room.active_specific_date',
            DB::raw("EXISTS(SELECT 1 FROM wp_gpxTransactions WHERE wp_gpxTransactions.weekId = wp_gpxPreHold.weekId AND wp_gpxTransactions.cancelled = 0 LIMIT 1) as is_booked"),
        ])
               ->withCasts(['active_specific_date' => 'datetime', 'is_booked' => 'boolean'])
               ->join('wp_room', 'wp_gpxPreHold.propertyID', '=', 'wp_room.record_id')
               ->released(false)
               ->whereNotNull('wp_gpxPreHold.release_on')
               ->where('wp_gpxPreHold.release_on', '<=', $now)
               ->get()
               ->each(function (PreHold $hold) use ($now, $io) {
                   $io->section(sprintf('Hold %s for week %s', $hold->id, $hold->weekId));
                   $io->writeln(sprintf('Hold releases on %s', $hold->release_on->format('Y-m-d H:i:s')));
                   if ($hold->is_booked) {
                       $io->writeln('Room is booked, set week to inactive');
                       Week::where('record_id', $hold->weekId)->update(['active' => false]);
                       $io->success('Room is now inactive');
                   } else {
                       $io->writeln('Room is not booked');
                       if ($hold->active_specific_date->isPast()) {
                           $io->writeln(sprintf('Room is past the active specific date of %s, set Week to active', $hold->active_specific_date->format('Y-m-d H:i:s')));
                           Week::where('record_id', $hold->weekId)->update(['active' => true]);
                           $io->success('Room is now active');
                       } else {
                           $io->writeln(sprintf('Room wont be active until %s', $hold->active_specific_date->format('Y-m-d H:i:s')));
                       }
                   }
                   $holdDets = $hold->data;
                   $holdDets[time()] = [
                       'action' => 'released',
                       'by' => 'System',
                   ];
                   $hold->data = $holdDets;
                   $hold->released = true;
                   $hold->save();
                   $io->success('Hold released');
               });
    }

    private function checkBookedWeeks(InputInterface $input, OutputInterface $output) {
        $io = $this->io($input, $output);
        $io->title('Check for booked / on hold weeks');

        $io->info('Checking for active weeks that are booked or on hold');

        Week::select([
            'wp_room.record_id',
            'wp_room.active',
            DB::raw("EXISTS(SELECT 1 FROM wp_gpxPreHold WHERE wp_gpxPreHold.propertyID = wp_room.record_id AND wp_gpxPreHold.released = 0 LIMIT 1) as has_hold"),
            DB::raw("EXISTS(SELECT 1 FROM wp_gpxTransactions WHERE wp_gpxTransactions.weekId = wp_room.record_id AND wp_gpxTransactions.cancelled = 0 LIMIT 1) as is_booked"),
        ])
            ->withCasts(['has_hold' => 'boolean', 'is_booked' => 'boolean'])
            ->where('wp_room.active', '=', true)
            ->where(fn($query) => $query
                ->orWhereRaw("EXISTS(SELECT 1 FROM wp_gpxPreHold WHERE wp_gpxPreHold.propertyID = wp_room.record_id AND wp_gpxPreHold.released = 0 LIMIT 1)")
                ->orWhereRaw("EXISTS(SELECT 1 FROM wp_gpxTransactions WHERE wp_gpxTransactions.weekId = wp_room.record_id AND wp_gpxTransactions.cancelled = 0 LIMIT 1)")
            )
            ->get()
            ->each(function (Week $week) use ($io) {
                $io->section('Week ' . $week->record_id);
                if ($week->is_booked) {
                    $io->writeln('Week is booked');
                }
                if ($week->has_hold) {
                    $io->writeln('Week is on hold');
                }
                $week->update(['active' => false]);
                $io->success('Week is now inactive');
            });

    }
}
