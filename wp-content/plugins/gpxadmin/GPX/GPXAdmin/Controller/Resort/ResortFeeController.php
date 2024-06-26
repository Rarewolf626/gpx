<?php

namespace GPX\GPXAdmin\Controller\Resort;

use GPX\Model\Resort;

class ResortFeeController {

    public function display(): void {
        $id = gpx_request('id');
        if (!$id) {
            wp_send_json([
                'success' => false,
                'message' => 'Resort ID is required',
            ], 404);
        }
        $resort = Resort::find($id);
        if (!$resort) {
            wp_send_json([
                'success' => false,
                'message' => 'Resort not found',
            ], 404);
        }
        $settings = filter_var_array(gpx_request()->input('settings', []), [
            'enabled' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'options' => ['default' => false],
            ],
            'fee' => [
                'filter' => FILTER_VALIDATE_FLOAT,
                'options' => ['decimal' => '.', 'min_range' => 0, 'default' => 0.00],
            ],
            'frequency' => FILTER_SANITIZE_STRING,
        ], true);

        if ($settings['enabled'] && $settings['fee'] <= 0) {
            wp_send_json([
                'success' => false,
                'message' => 'Fee is required if enabled',
            ], 422);
        }
        $meta = [
            'enabled' => (bool) $settings['enabled'],
            'fee' => (float) $settings['fee'],
            'frequency' => $settings['frequency'] === 'daily' ? 'daily' : 'weekly',
        ];
        global $wpdb;
        $sql = "SELECT `meta_key`,`id`,`meta_value` FROM wp_resorts_meta WHERE ResortID = %s AND meta_key = 'ResortFeeSettings' LIMIT 1";
        $record = $wpdb->get_row($wpdb->prepare($sql, [$resort->ResortID]));
        if ($record) {
            $wpdb->update('wp_resorts_meta', ['meta_value' => json_encode($meta)], ['id' => $record->id]);
        } else {
            $wpdb->insert('wp_resorts_meta', [
                'ResortID' => $resort->ResortID,
                'meta_key' => 'ResortFeeSettings',
                'meta_value' => json_encode($meta),
            ]);
        }


        wp_send_json([
            'success' => true,
            'message' => 'Resort fee settings updated!',
            'settings' => $meta,
        ]);
    }
}
