<?php

use GPX\Model\Reports\MasterAvailability;

function gpx_remove_report()
{
    global $wpdb;

    if(isset($_POST['id']))
    {
        $wpdb->delete('wp_gpx_report_writer', array('id'=>$_POST['id']));
    }

    wp_send_json(['success' => true]);
}
add_action('wp_ajax_gpx_remove_report', 'gpx_remove_report');

function get_gpx_reportsearches()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_reportsearches();

    wp_send_json($data);
}

add_action('wp_ajax_get_gpx_reportsearches', 'get_gpx_reportsearches');
add_action('wp_ajax_nopriv_get_gpx_reportsearches', 'get_gpx_reportsearches');

function edit_gpx_resort()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_edit_gpx_resort();

    wp_send_json($data);
}

add_action('wp_ajax_edit_gpx_resort', 'edit_gpx_resort');
add_action('wp_ajax_nopriv_edit_gpx_resort', 'edit_gpx_resort');

function gpx_report_write_send()
{
    global $wpdb;

    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $sql = "SELECT id, emailrepeat FROM wp_gpx_report_writer WHERE emailrecipients != ''";
    $results = $wpdb->get_results($sql);

    $weekday = date('N');
    $day = date('l');
    $month = date('j');

    $data = [];
    foreach($results as $result)
    {
        if(strtolower($day) == strtolower($result->emailrepeat))
        {
            $run = true;
        }
        else
        {
            switch($result->emailrepeat)
            {
                case 'Daily':
                    $run = true;
                    break;

                case 'Weekdays':
                    if($weekday < 6)
                    {
                        $run = true;
                    }
                    break;

                case 'Monthly':
                    if($month == '1')
                    {
                        $run = true;
                    }
                    break;
            }
        }

        if(isset($run))
        {
            $data[] = $gpx->reportwriter($result->id, true);
        }
    }

    wp_send_json($data);
}
add_action('hook_cron_gpx_report_write_send', 'gpx_report_write_send');
add_action('wp_ajax_cron_grws', 'gpx_report_write_send');

function gpx_report_writer_table()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->reportwriter($_GET['id']);

    wp_send_json($data);
}
add_action('wp_ajax_gpx_report_writer_table', 'gpx_report_writer_table');

function gpx_retarget_report()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $table = 'wp_cart';
    if(isset($_GET['table']))
        $table = $_GET['table'];
    $column = 'data';
    if(isset($_GET['column']))
        $column = $_GET['column'];
    $return = $gpx->reportretarget();
    if (file_exists($return)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($return).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($return));
        readfile($return);
        exit;
    }
}
add_action("wp_ajax_gpx_retarget_report","gpx_retarget_report");
add_action("wp_ajax_nopriv_gpx_retarget_report", "gpx_retarget_report");

function gpx_json_reports()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $table = 'wp_cart';
    if(isset($_GET['table']))
        $table = $_GET['table'];
    $days = '10';
    if(isset($_GET['days']))
        $days = $_GET['days'];
    $return = $gpx->get_gpx_json_reports($table, $days);

    wp_send_json($return);
}
add_action("wp_ajax_gpx_json_reports","gpx_json_reports");
add_action("wp_ajax_nopriv_gpx_json_reports", "gpx_json_reports");

function gpx_csv_download()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $table = 'wp_cart';
    if(isset($_GET['table']))
        $table = $_GET['table'];
    $column = 'data';
    if(isset($_GET['column']))
        $column = $_GET['column'];
    $days = '60';
    if(isset($_GET['days']))
        $days = $_GET['days'];
    $dateFrom = date('Y-m-d', strtotime('-2 days'));
    if(isset($_GET['datefrom']))
    {
        $dateFrom = $_GET['datefrom'];
    }

    $dateTo = date('Y-m-d');
    if(!empty($_GET['dateto']))
    {
        $dateTo = $_GET['dateto'];
    }
    $return = $gpx->get_csv_download($table, $column, $days, '', $dateFrom, $dateTo);

    if (file_exists($return)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($return).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($return));
        readfile($return);
        exit;
    }
}
add_action("wp_ajax_gpx_csv_download","gpx_csv_download");
add_action("wp_ajax_nopriv_gpx_csv_download", "gpx_csv_download");

/**
 *   Master Availability
 */
function gpx_report_availability() {
    $ma = new MasterAvailability();
    $ma->filter->dates( gpx_request('date-start'), gpx_request('date-end') );
    $data = $ma->run();
    wp_send_json($data);
}
add_action("wp_ajax_gpx_get_report_availability","gpx_report_availability");
add_action("wp_ajax_nopriv_gpx_get_report_availability","gpx_report_availability");

