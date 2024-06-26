<?php

use GPX\Output\StreamAndOutput;
use Symfony\Component\HttpFoundation\StreamedResponse;

function gpx_check_inactive_coupons()
{
    if (!gpx_is_administrator(false)) {
        wp_send_json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $response = new StreamedResponse(function () {
        $path = WP_CONTENT_DIR . '/logs/check-inactive-coupons.log';
        $stream = fopen($path, 'w+');
        $output = new StreamAndOutput($stream);
        gpx_run_command(['command' => 'coupon:expired'], $output);
        fclose($stream);
    }, 200, ['Content-Type' => 'text/plain']);
    gpx_send_response($response);
}
add_action('wp_ajax_cron_inactive_coupons', 'gpx_check_inactive_coupons');
