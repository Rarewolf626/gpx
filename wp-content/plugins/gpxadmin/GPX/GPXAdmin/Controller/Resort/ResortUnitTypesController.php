<?php

namespace GPX\GPXAdmin\Controller\Resort;

use GPX\Model\UnitType;
use Illuminate\Support\Carbon;
use GPX\Form\Admin\Resort\ResortUnitTypeForm;

class ResortUnitTypesController {
    public function index() {}

    public function add(): void {
        /** @var ResortUnitTypeForm $form */
        $form = gpx(ResortUnitTypeForm::class);

        $values = $form->validate();
        $now = Carbon::now();

        // this is an add
        $unit_type = UnitType::create([
            'resort_id' => $values['resort_id'],
            'name' => $values['name'],
            'number_of_bedrooms' => $values['number_of_bedrooms'],
            'bedrooms_override' => $values['bedrooms_override'],
            'sleeps_total' => $values['sleeps_total'],
            'xref' => 0,
            'create_date' => $now,
            'last_modified_date' => $now,
        ]);

        wp_send_json([
            'success' => true,
            'message' => 'Unit Type added successfully.',
            'unit_type' => [
                'record_id' => $unit_type->record_id,
                'name' => $unit_type->name,
                'number_of_bedrooms' => $unit_type->number_of_bedrooms,
                'sleeps_total' => $unit_type->sleeps_total,
            ],
        ]);
    }

    public function edit(): void {
        $resort_id = (int) gpx_request('resort_id');
        $unit_id = (int) gpx_request('record_id');
        if (!$resort_id || !$unit_id) {
            wp_send_json([
                'success' => false,
                'message' => 'Invalid request.',
            ], 400);
        }
        $unit_type = UnitType::ByResort($resort_id)->find($unit_id);
        if (!$unit_type) {
            wp_send_json([
                'success' => false,
                'message' => 'Unit Type not found.',
            ], 404);
        }
        /** @var ResortUnitTypeForm $form */
        $form = gpx(ResortUnitTypeForm::class);
        $values = $form->validate();

        $unit_type->update([
            'name' => $values['name'],
            'number_of_bedrooms' => $values['number_of_bedrooms'],
          //  'bedrooms_override' => $values['bedrooms_override'],
            'sleeps_total' => $values['sleeps_total'],
            'last_modified_date' => Carbon::now(),
        ]);

        wp_send_json([
            'success' => true,
            'message' => 'Unit Type updated successfully.',
            'unit_type' => [
                'record_id' => $unit_type->record_id,
                'name' => $unit_type->name,
                'number_of_bedrooms' => $unit_type->number_of_bedrooms,
                'bedrooms_override' => $unit_type->bedrooms_override,
                'sleeps_total' => $unit_type->sleeps_total,
            ],
        ]);
    }

    public function destroy(): void {
        $resort_id = (int) gpx_request('resort_id');
        $unit_id = (int) gpx_request('record_id');
        if (!$resort_id || !$unit_id) {
            wp_send_json([
                'success' => false,
                'message' => 'Invalid request.',
            ], 400);
        }
        $unit_type = UnitType::ByResort($resort_id)->find($unit_id);
        if (!$unit_type) {
            wp_send_json([
                'success' => false,
                'message' => 'Unit Type not found.',
            ], 404);
        }
        $unit_type->delete();

        wp_send_json([
            'success' => true,
            'message' => 'Unit Type deleted successfully.',
        ]);
    }
}
