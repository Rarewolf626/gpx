<?php

/**
 *
 *
 *
 *
 */
function get_gpx_customrequests()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_customrequests();

    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_get_gpx_customrequests', 'get_gpx_customrequests');
add_action('wp_ajax_nopriv_get_gpx_customrequests', 'get_gpx_customrequests');




/**
 *
 *
 *
 *
 */
function gpx_cr_pdf_reports(){
    if (isset($_REQUEST['cr_pdf_reports'])){

        require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
        $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

        $html = $gpx->return_custom_request_report();



        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
    }
}

add_action('init', 'gpx_cr_pdf_reports');

/**
 *
 *
 *
 *
 */
function cr_form_remove_visual($c) {
    if (isset($_REQUEST['gpx-pg']) && $_REQUEST['gpx-pg'] == 'customrequests_form')
        return false;
    return $c;
}
//remove visual editor from custom request form form
add_filter( 'user_can_richedit', 'cr_form_remove_visual');

/**
 *
 *
 *
 *
 */
function cron_check_custom_requests_ajax()
{
    global $wpdb;

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $gpx->return_cron_check_custom_requests();

    $data = array('success'=>true);
}
add_action('wp_ajax_cron_check_custom_requests_ajax', 'cron_check_custom_requests_ajax');
add_action('wp_ajax_nopriv_cron_check_custom_requests_ajax', 'cron_check_custom_requests_ajax');

