<?php

namespace GPX\GPXAdmin\Controller;

class DashboardController {
    public function dashboard() {
        return gpx_render_blade('admin::dashboard', [], false);
    }

    public function redirect() {
        return wp_redirect(gpx_admin_route(''), 301);
    }

    public function alert() {
        $values = [
            'enabled' => !!get_option('gpx_alert_msg_active', false),
            'message' => get_option('gpx_alert_msg_msg', ''),
        ];
        $values['enabled'] = !!gpx_request('enabled', $values['enabled']);
        $values['message'] = gpx_request('message', $values['message']);

        update_option('gpx_alert_msg_active', $values['enabled'], false);
        update_option('gpx_alert_msg_msg', $values['message'], false);

        wp_send_json([
            'success' => true,
            'data' => $values,
        ]);
    }

    public function booking() {
        $values = [
            'disabled' => !!get_option('gpx_booking_disabled_active', false),
            'message' => get_option('gpx_booking_disabled_msg', ''),
        ];
        $values['disabled'] = !!gpx_request('disabled', $values['disabled']);
        $values['message'] = gpx_request('message', $values['message']);

        update_option('gpx_booking_disabled_active', $values['disabled'], false);
        update_option('gpx_booking_disabled_msg', $values['message'], false);

        wp_send_json([
            'success' => true,
            'data' => $values,
        ]);
    }

    public function hold() {
        $values = [
            'time' => (int) get_option('gpx_hold_limt_time', 1),
            'message' => get_option('gpx_hold_limt_timer', ''),
            'error' => get_option('gpx_hold_error_message', ''),
        ];

        $field = gpx_request('field');
        switch ($field) {
            case 'time':
                $values['time'] = (int) gpx_request('time', $values['time']);
                update_option('gpx_hold_limt_time', $values['time'], false);
                break;
            case 'message':
                $values['message'] = gpx_request('message', $values['message']);
                update_option('gpx_hold_limt_timer', $values['message'], false);
                break;
            case 'error':
                $values['error'] = gpx_request('error', $values['error']);
                update_option('gpx_hold_error_message', $values['error'], false);
                break;
        }

        wp_send_json([
            'success' => true,
            'data' => $values,
        ]);
    }

    public function fee() {
        $values = [
            'min_rental' => (int)get_option( 'gpx_min_rental_fee', 0 ),
            'flex' => (int)get_option( 'gpx_fb_fee', 0 ),
            'late_deposit' => (int)get_option( 'gpx_late_deposit_fee', 0 ),
            'late_deposit_within' => (int)get_option( 'gpx_late_deposit_fee_within', 0 ),
            'third_party_deposit' => (float)get_option( 'gpx_third_party_fee', 50 ),
            'third_party_deposit_days' => (int)get_option( 'gpx_third_party_fee_days', 60 ),
            'extension' => (int)get_option( 'gpx_extension_fee', 0 ),
            'exchange' => (int)get_option( 'gpx_exchange_fee', 0 ),
            'exchange_legacy' => (int)get_option( 'gpx_legacy_owner_exchange_fee', get_option( 'gpx_exchange_fee', 0 ) ),
            'guest' => [
                'enabled' => !!get_option( 'gpx_global_guest_fees', false ),
                'amount' => (int)get_option( 'gpx_gf_amount', 0 ),
            ],
        ];

        $field = gpx_request('field');
        switch ($field) {
            case 'min_rental':
                $values['min_rental'] = (int) gpx_request('value', $values['min_rental']);
                update_option('gpx_min_rental_fee', $values['min_rental'], false);
                break;
            case 'flex':
                $values['flex'] = (int) gpx_request('value', $values['flex']);
                update_option('gpx_fb_fee', $values['flex'], false);
                break;
            case 'late_deposit':
                $values['late_deposit'] = (int) gpx_request('value', $values['late_deposit']);
                update_option('gpx_late_deposit_fee', $values['late_deposit'], false);
                break;
            case 'late_deposit_within':
                $values['late_deposit_within'] = (int) gpx_request('value', $values['late_deposit_within']);
                update_option('gpx_late_deposit_fee_within', $values['late_deposit_within'], false);
                break;
            case 'third_party_deposit':
                $values['third_party_deposit'] = (int) gpx_request('value', $values['third_party_deposit']);
                update_option('gpx_third_party_fee', $values['third_party_deposit'], false);
                break;
            case 'third_party_deposit_days':
                $values['third_party_deposit_days'] = (int) gpx_request('value', $values['third_party_deposit_days']);
                update_option('gpx_third_party_fee_days', $values['third_party_deposit_days'], false);
                break;
            case 'extension':
                $values['extension'] = (int) gpx_request('value', $values['extension']);
                update_option('gpx_extension_fee', $values['extension'], false);
                break;
            case 'exchange':
                $values['exchange'] = (int) gpx_request('value', $values['exchange']);
                update_option('gpx_exchange_fee', $values['exchange'], false);
                break;
            case 'exchange_legacy':
                $values['exchange_legacy'] = (int) gpx_request('value', $values['exchange_legacy']);
                update_option('gpx_legacy_owner_exchange_fee', $values['exchange_legacy'], false);
                break;
            case 'guest':
                $values['guest']['enabled'] = !!gpx_request('enabled', $values['guest']['enabled']);
                $values['guest']['amount'] = (int) gpx_request('amount', $values['guest']['amount']);
                update_option('gpx_global_guest_fees', $values['guest']['enabled'], false);
                update_option('gpx_gf_amount', $values['guest']['amount'], false);
                break;
        }

        wp_send_json([
            'success' => true,
            'data' => $values,
        ]);
    }
}
