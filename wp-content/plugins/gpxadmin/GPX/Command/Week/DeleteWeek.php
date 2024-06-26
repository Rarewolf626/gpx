<?php

namespace GPX\Command\Week;

use DB;
use GPX\Model\Week;
use GPX\Model\Transaction;
use GPX\Event\Week\WeekWasDeleted;
use GPX\Event\Week\WeekWasArchived;

class DeleteWeek {
    public function __construct(public Week $week) {}

    public function handle(): array {

        if ($this->week->source_partner_id) {
            // if the week was given to a partner we need to update their trade balance
            $this->week->partner()->update([
                'no_of_rooms_given' => DB::raw('no_of_rooms_given - 1'),
                'trade_balance' => DB::raw('trade_balance - 1'),
            ]);
        }

        $has_transactions = Transaction::forWeek($this->week->record_id)->exists();
        if (!$has_transactions) {
            // if there are no transactions we can fully delete the week
            $this->week->delete();
            gpx_event(new WeekWasDeleted($this->week));

            return [
                'success' => true,
                'deleted' => true,
                'archived' => false,
                'message' => 'Room deleted Successfully.',
            ];
        }

        $details = $this->week->update_details;
        $details[time()] = [
            'update_by' => get_current_user_id(),
            'details' => ['room_archived' => date('m/d/Y H:i:s')],
        ];

        $this->week->active = false;
        $this->week->archived = true;
        $this->week->update_details = json_encode($details);
        $this->week->save();
        gpx_event(new WeekWasArchived($this->week));

        return [
            'success' => true,
            'deleted' => false,
            'archived' => true,
            'message' => 'Room archived Successfully.',
        ];
    }
}
