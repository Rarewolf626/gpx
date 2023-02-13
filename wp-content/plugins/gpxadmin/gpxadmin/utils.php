<?php

/**
 *
 *
 *
 *
 */
function gpx_monthyear_dd()
{
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $country = '';
    $region = '';

    if(isset($_GET['country']))
        $country = $_GET['country'];

    if(isset($_GET['region']))
        $region = $_GET['region'];

    $resorts = $gpx->return_gpx_monthyear_dd($country, $region);

    wp_send_json($resorts);
}
add_action("wp_ajax_gpx_monthyear_dd","gpx_monthyear_dd");
add_action("wp_ajax_nopriv_gpx_monthyear_dd", "gpx_monthyear_dd");


/**
 *
 *
 *
 *
 */
function is_gpr()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_is_gpr();

    wp_send_json($data);
}

add_action('wp_ajax_is_gpr', 'is_gpr');

