<?php

namespace GPX\Command\Week;

use WP_User;
use GPX\Model\Week;
use GPX\Model\PreHold;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AdminHoldWeeks {

    public Collection $weeks;
    public WP_User $user;
    public string $date;

    public function __construct(array|int $weeks, int|WP_User $user = null, string $date = null) {
        $this->weeks = Week::whereIn('record_id', Arr::wrap($weeks))->get();
        $this->user = $user instanceof WP_User ? $user : get_userdata($user ?? get_current_user_id());
        $this->date = date('Y-m-d', strtotime($date ?? '+1 year'));
    }

    public function handle(): void {
        $this->weeks->each(fn($week) => $this->holdWeek($week));
    }

    private function holdWeek(Week $week): void {
        $hold = PreHold::forWeek($week->record_id)->forUser($this->user->ID)->firstorNew();
        $holdDets = $hold?->data ?? [];
        $holdDets[time()] = [
            'action' => 'held',
            'by' => 'Admin ('.$this->user->first_name . " " . $this->user->last_name.')',
        ];
        if (!empty($_REQUEST['date'])) {
            $releaseOn = date('Y-m-d', strtotime($_REQUEST['date']));
        } else {
            $releaseOn = date('Y-m-d', strtotime('+1 year'));
        }
        $data = [
            'propertyID' => $week->record_id,
            'weekId' => $week->record_id,
            'user' => $this->user->ID,
            'data' => $holdDets,
            'released' => 0,
            'release_on' => $releaseOn,
            'weekType' => '',
            'lpid' => 0,
        ];
        $hold->fill($data);
        $hold->save();

        $week->update(['active' => false]);
    }
}
