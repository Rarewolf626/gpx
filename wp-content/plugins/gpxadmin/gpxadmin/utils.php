<?php

/**
 *
 *
 *
 *
 */
function gpx_monthyear_dd()
{
    header('content-type: application/json; charset=utf-8');
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $country = '';
    $region = '';

    if(isset($_GET['country']))
        $country = $_GET['country'];

    if(isset($_GET['region']))
        $region = $_GET['region'];

    $resorts = $gpx->return_gpx_monthyear_dd($country, $region);

    echo wp_send_json($resorts);
    exit();
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
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_is_gpr();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_is_gpr', 'is_gpr');

