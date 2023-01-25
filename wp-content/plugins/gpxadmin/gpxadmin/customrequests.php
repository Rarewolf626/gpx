<?php

/**
 *
 *
 *
 *
 */
function get_gpx_customrequests() {
    $gpx = new GpxAdmin( GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR );

    $data = $gpx->return_gpx_customrequests();

    wp_send_json( $data );
}

add_action( 'wp_ajax_get_gpx_customrequests', 'get_gpx_customrequests' );
add_action( 'wp_ajax_nopriv_get_gpx_customrequests', 'get_gpx_customrequests' );


/**
 *
 *
 *
 *
 */
function gpx_cr_pdf_reports() {
    if ( isset( $_REQUEST['cr_pdf_reports'] ) ) {
        $gpx = new GpxAdmin( GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR );

        $html = $gpx->return_custom_request_report();


        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml( $html );

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper( 'A4', 'portrait' );

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
    }
}

add_action( 'init', 'gpx_cr_pdf_reports' );

/**
 *
 *
 *
 *
 */
function cr_form_remove_visual( $c ) {
    if ( isset( $_REQUEST['gpx-pg'] ) && $_REQUEST['gpx-pg'] == 'customrequests_form' ) {
        return false;
    }

    return $c;
}

//remove visual editor from custom request form form
add_filter( 'user_can_richedit', 'cr_form_remove_visual' );


function gpx_check_custom_requests() {
    if ( ! check_user_role( [ 'gpx_admin', 'gpx_call_center', 'administrator', 'administrator_plus' ] ) ) {
        gpx_response( 'You do not have permission to run command', 403 );
    }
    $params = [ 'command' => 'request:checker' ];
    if ( gpx_request( 'debug' ) ) {
        $params['--debug'] = true;
    }
    $response = gpx_run_command( $params, true, true );
    gpx_response( $response );
}

add_action( 'wp_ajax_gpx_check_custom_requests', 'gpx_check_custom_requests' );
add_action( 'wp_ajax_nopriv_gpx_check_custom_requests', 'gpx_check_custom_requests' );
