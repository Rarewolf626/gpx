@extends('admin.layout', ['title' => 'GPX Dashboard', 'active' => 'dashboard'])
@section('content')

    <div id="gpxadmin-dashboard"
         data-props="<?= esc_attr(json_encode([
                'alert' => [
                    'enabled' => !!get_option( 'gpx_alert_msg_active', false ),
                    'message' => get_option( 'gpx_alert_msg_msg', '' ),
                ],
                'booking' => [
                    'disabled' => !!get_option( 'gpx_booking_disabled_active', false ),
                    'message' => get_option( 'gpx_booking_disabled_msg', '' ),
                ],
                'hold' => [
                    'time' => (int)get_option( 'gpx_hold_limt_time', 1),
                    'message' => get_option( 'gpx_hold_limt_timer', '' ),
                    'error' => get_option( 'gpx_hold_error_message', '' ),
                ],
                'fees' => [
                    'min_rental' => (int)get_option( 'gpx_min_rental_fee', 0 ),
                    'flex' => (int)get_option( 'gpx_fb_fee', 0 ),
                    'late_deposit' => (int)get_option( 'gpx_late_deposit_fee', 0 ),
                    'late_deposit_within' => (int)get_option( 'gpx_late_deposit_fee_within', 0 ),
                    'extension' => (int)get_option( 'gpx_extension_fee', 0 ),
                    'exchange' => (int)get_option( 'gpx_exchange_fee', 0 ),
                    'exchange_legacy' => (int)get_option( 'gpx_legacy_owner_exchange_fee', get_option( 'gpx_exchange_fee', 0 ) ),
                    'guest' => [
                        'enabled' => !!get_option( 'gpx_global_guest_fees', false ),
                        'amount' => (int)get_option( 'gpx_gf_amount', 0 ),
                    ],
                    'third_party_deposit' => (float)get_option( 'gpx_third_party_fee', 50 ),
                    'third_party_deposit_days' => (int)get_option( 'gpx_third_party_fee_days', 60 ),
                ],
                'tax' => [
                    'bonus' => !!get_option( 'gpx_tax_transaction_bonus', false ),
                    'exchange' => !!get_option( 'gpx_tax_transaction_exchange', false ),
                ],
            ]))?>"
    ></div>

@endsection
