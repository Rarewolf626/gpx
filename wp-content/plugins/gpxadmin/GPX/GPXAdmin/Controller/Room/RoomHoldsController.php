<?php

namespace GPX\GPXAdmin\Controller\Room;

use DB;
use GPX\Model\PreHold;
use GPX\Model\UserMeta;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class RoomHoldsController {
    public function index(int $id) {
        $holds = PreHold::select(['wp_gpxPreHold.*', DB::raw('EXISTS(SELECT cancelled FROM wp_gpxTransactions WHERE weekId = wp_gpxPreHold.weekId AND cancelled = 0 LIMIT 1) as is_booked')])->with(['week','theuser','week.theresort', 'week.unit'])->forWeek($id)->orderBy('id')->get();
        wp_send_json(
            $holds->map(function(PreHold $hold){
                $meta = UserMeta::load($hold->user);

                $released = $hold->released ? 'Yes' : 'No';
                if(!$hold->week->active && $hold->is_booked){
                    $released = 'Booked';
                }

                return [
                    'id' => $hold->id,
                    'user' => $hold->user,
                    'week' => $hold->weekId,
                    'owner' => $meta?->getName() ?? null,
                    'resort' => $hold->week->theresort?->ResortName ?? null,
                    'room_size' => $hold->week->unit?->name ?? null,
                    'checkin' => $hold->week->check_in_date?->format('m/d/Y'),
                    'release_on' => $hold->release_on?->format('m/d/Y g:i A'),
                    'released' => $released,
                    'can_extend' => $released === 'No',
                    'can_release' => $released === 'No',
                ];
            })->toArray()
        );
    }

    public function details(int $id) {
        $hold = PreHold::query()
                       ->with(['week','theuser','week.theresort', 'week.unit'])
                       ->findOrFail($id);

        $meta = UserMeta::load($hold->user);

        return [
            'success' => true,
            'hold' => [
                'id' => $hold->id,
                'user' => $hold->user,
                'week' => $hold->weekId,
                'owner' => $meta?->getName() ?? null,
                'resort' => $hold->week->theresort?->ResortName ?? null,
                'room_size' => $hold->week->unit?->name ?? null,
                'checkin' => $hold->week->check_in_date?->format('m/d/Y'),
                'checkin_iso' => $hold->week->check_in_date?->format('Y-m-d'),
                'released' => $hold->released,
                'release_on' => $hold->release_on?->format('m/d/Y g:i A'),
                'activity' => Arr::map($hold->data, fn($activity, $time) => [
                    'action' => $activity['action'] ?? null,
                    'user' => $activity['by'] ?? null,
                    'time' => Carbon::createFromTimestamp($time)->format('m/d/Y g:i A'),
                ]),
            ],

        ];
    }
}
