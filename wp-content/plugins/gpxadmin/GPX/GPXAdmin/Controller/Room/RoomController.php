<?php

namespace GPX\GPXAdmin\Controller\Room;

use DB;
use GPX\Model\Week;
use GPX\Model\Resort;
use GPX\Model\UnitType;
use GPX\Model\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use GPX\Command\Week\DeleteWeek;
use Illuminate\Support\MessageBag;
use GPX\Repository\WeekRepository;
use GPX\Form\Admin\Room\EditRoomForm;
use Illuminate\Contracts\Bus\Dispatcher;

class RoomController {
    public function index() {}

    public function add() {}

    public function edit(int $id) {
        $week = Week::with(['partner', 'available_partner'])->find($id);
        if (!$week) {
            return wp_redirect(gpx_admin_route('room_all'));
        }
        $errors = new MessageBag();
        $message = null;
        $resorts = Resort::select(['id', 'ResortName'])->active()->get();
        $unit_types = UnitType::select(['record_id', 'name'])->byResort($week->resort)->get();
        $is_booked = $week->isBooked();
        $status = $week->getStatus();
        $history = $week->getUpdateHistory();
        $can_edit = gpx_user_has_role('gpx_admin') || !$is_booked;
        
        return gpx_render_blade('admin::room.edit', compact('week', 'errors', 'message', 'resorts', 'unit_types', 'can_edit', 'is_booked', 'status', 'history'), false);
    }

    public function unitTypes(int $resort_id) {
        $unit_types = UnitType::select(['record_id', 'name'])->byResort($resort_id)->get();

        wp_send_json($unit_types->toArray());
    }

    public function update(int $id) {
        $week = Week::findOrFail($id);

        $is_booked = $week->isBooked();
        $can_edit = gpx_user_has_role('gpx_admin') || !$is_booked;
        if (!$can_edit) {
            wp_send_json([
                'success' => false,
                'message' => 'You do not have permission to edit a booked week.',
            ], 403);
        }

        /** @var EditRoomForm $form */
        $form = gpx(EditRoomForm::class);
        $values = $form->validate();
        $update = [
            'resort_confirmation_number' => $values['resort_confirmation_number'] ?: '',
            'check_in_date' => $values['check_in_date'],
            'check_out_date' => $values['check_out_date'],
            'resort' => $values['resort'] ?: 0,
            'unit_type' => $values['unit_type'] ?: 0,
            'source_num' => $values['source_num'],
            'source_partner_id' => $values['source_partner_id'] ?: 0,
            'active' => $values['active'],
            'active_type' => $values['active_type'] ?: 0,
            'active_specific_date' => $values['active_specific_date'],
            'active_week_month' => $values['active_week_month'] ?: 0,
            'availability' => $values['availability'],
            'available_to_partner_id' => $values['available_to_partner_id'] ?: 0,
            'type' => $values['type'],
            'price' => $values['price'] ?: null,
            'active_rental_push_date' => $values['active_rental_push_date'],
            'note' => $values['note'],
            'last_modified_date' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
        $checkin = Carbon::parse($update['check_in_date'])->startOfDay();
        if (empty($update['check_out_date'])) {
            $update['check_out_date'] = $checkin->clone()->addWeek()->startOfDay()->format('Y-m-d');
        }
        if (empty($update['active_specific_date'])) {
            $update['active_specific_date'] = $checkin->clone()->subYear()->startOfMonth()->format('Y-m-d');
        } else {
            $update['active_specific_date'] = Carbon::parse($update['active_specific_date'])->startOfMonth()->format('Y-m-d');
        }
        if ($update['active_type'] == 'date') {
            $update['active_week_month'] = 0;
        } elseif ($update['active_type'] === 'weeks') {
            $update['active_specific_date'] = $checkin->clone()->subWeeks($update['active_week_month'])->startOfMonth()->format('Y-m-d');
        } elseif ($update['active_type'] === 'months') {
            $update['active_specific_date'] = $checkin->clone()->subMonths($update['active_week_month'])->startOfMonth()->format('Y-m-d');
        }

        if (empty($update['active_rental_push_date'])) {
            $update['active_rental_push_date'] = $checkin->clone()->subMonths(6)->startOfDay()->format('Y-m-d');
        }

        if ($update['active_rental_push_date'] < $update['active_specific_date']) {
            $update['active_rental_push_date'] = $update['active_specific_date'];
        }
        $updates = array_filter([
            'resort_confirmation_number' => [
                'old' => $week->resort_confirmation_number,
                'new' => $update['resort_confirmation_number'],
            ],
            'check_in_date' => [
                'old' => $week->check_in_date?->format('Y-m-d'),
                'new' => $update['check_in_date'],
            ],
            'check_out_date' => [
                'old' => $week->check_out_date?->format('Y-m-d'),
                'new' => $update['check_out_date'],
            ],
            'resort' => [
                'old' => $week->resort,
                'new' => $update['resort'],
            ],
            'unit_type' => [
                'old' => $week->unit_type,
                'new' => $update['unit_type'],
            ],
            'source_num' => [
                'old' => $week->source_num,
                'new' => $update['source_num'],
            ],
            'source_partner_id' => [
                'old' => $week->source_partner_id,
                'new' => $update['source_partner_id'],
            ],
            'active' => [
                'old' => $week->active ? 'Yes' : 'No',
                'new' => $update['active'] ? 'Yes' : 'No',
            ],
            'active_type' => [
                'old' => $week->active_type,
                'new' => $update['active_type'],
            ],
            'active_specific_date' => [
                'old' => $week->active_specific_date?->format('Y-m-d'),
                'new' => $update['active_specific_date'],
            ],
            'active_week_month' => [
                'old' => $week->active_week_month,
                'new' => $update['active_week_month'],
            ],
            'availability' => [
                'old' => $week->availability,
                'new' => $update['availability'],
            ],
            'available_to_partner_id' => [
                'old' => $week->available_to_partner_id,
                'new' => $update['available_to_partner_id'],
            ],
            'type' => [
                'old' => $week->type,
                'new' => $update['type'],
            ],
            'price' => [
                'old' => $week->price,
                'new' => $update['price'],
            ],
            'active_rental_push_date' => [
                'old' => $week->active_rental_push_date?->format('Y-m-d'),
                'new' => $update['active_rental_push_date'],
            ],
            'note' => [
                'old' => $week->note,
                'new' => $update['note'],
            ],
        ], fn($field) => $field['old'] != $field['new']);

        if (empty($updates)) {
            // No data was changed
            wp_send_json([
                'success' => true,
                'message' => 'Updated successful',
            ]);

        }

        // there is something to update
        $week->fill($update);
        $details = array_filter($week->update_details);
        $details[time()] = [
            'update_by' => get_current_user_id(),
            'details' => $updates,
        ];
        $week->update_details = json_encode($details);
        $week->save();

        wp_send_json([
            'success' => true,
            'message' => 'Updated successful',
        ]);
    }

    public function destroy(int $id) {
        $week = Week::findOrFail($id);
        $result = gpx_dispatch(new DeleteWeek($week));
        wp_send_json(array_merge($result, [
            'redirect' => $result['success'] ? gpx_admin_route('room_all') : null,
        ]));
    }
}
