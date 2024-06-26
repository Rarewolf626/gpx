<?php

/**
 *
 *
 *
 *
 */
function gpx_monthyear_dd() {
    global $wpdb;
    $term = (!empty($_GET['term'])) ? sanitize_text_field($_GET['term']) : '';
    $country = $_GET['country'] ?? '';
    $region = $_GET['region'] ?? '';

    $dates = [];
    $output = '<option value="0" disabled selected ></option>';
    $sql = $wpdb->prepare("SELECT lft, rght FROM wp_gpxRegion WHERE RegionID=%s", $region);
    $row = $wpdb->get_row($sql);
    $lft = $row->lft + 1;
    $sql = $wpdb->prepare("SELECT id, lft, rght FROM wp_gpxRegion
            WHERE lft BETWEEN %d AND %d
            ORDER BY lft ASC", [$lft, $row->rght]);
    $gpxRegions = $wpdb->get_results($sql);
    foreach ($gpxRegions as $gpxRegion) {
        $sql = $wpdb->prepare("SELECT DISTINCT a.checkIn FROM wp_properties a
                    INNER JOIN wp_resorts b ON a.resortId=b.ResortID
                    WHERE b.gpxRegionID=%s", $gpxRegion->id);
        $rows = $wpdb->get_results($sql);
        foreach ($rows as $row) {
            $my = date('M Y', strtotime($row->checkIn));
            if (!in_array($my, $dates)) {
                $dates[] = $my;
            }
        }
    }
    foreach ($dates as $date) {
        $output .= '<option>' . $date . '</option>';
    }

    wp_send_json($output);
}

add_action("wp_ajax_gpx_monthyear_dd", "gpx_monthyear_dd");
add_action("wp_ajax_nopriv_gpx_monthyear_dd", "gpx_monthyear_dd");


/**
 *
 *
 *
 *
 */
function is_gpr() {
    global $wpdb;

    $gpr = $_POST['gpr'];

    if ($gpr == 0) {
        $newstatus = 1;
        $msg = "GPR Resort!";
        $fa = "fa-check-square";
    } else {
        $newstatus = 0;
        $msg = "Not GPR Resort!";
        $fa = "fa-square";
    }

    $wpdb->update('wp_resorts', ['gpr' => $newstatus], ['ResortID' => $_POST['resort']]);
    $data = ['success' => true, 'msg' => $msg, 'fastatus' => $fa, 'status' => $newstatus];

    wp_send_json($data);
}

add_action('wp_ajax_is_gpr', 'is_gpr');

