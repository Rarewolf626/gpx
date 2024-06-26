<?php
use GPX\Api\Salesforce\Salesforce;

/**
 *
 *  sf_import_resorts
 *
 * https://www.gpxvacations.com/wp-admin/admin-ajax.php?action=sf_import_resorts
 * import/sync resorts
 * ran manually with URL only
 *
 * finds resorts in SF that have been changed in last 14 days,
 * attempts to match them to resort names in GPX then updates GPX (wp_resorts) with the
 * SF id for that resort
 *
 */
function sf_import_resorts($resortid = '') {
    global $wpdb;

    $sf = Salesforce::getInstance();

    $query = /** @lang sfquery */
        "select Id,Name from Resort__c  where SystemModStamp >= LAST_N_DAYS: 14";
    $results = $sf->query($query);
    $dataset = [];
    foreach ($results as $result) {
        $fields = $result->fields;
        $id = $result->Id;

        $sql = $wpdb->prepare('SELECT * FROM wp_resorts WHERE ResortName LIKE %s', $wpdb->esc_like($fields->Name) . '%');
        $row = $wpdb->get_row($sql);
        if (!empty($row)) {
            $wpdb->update('wp_resorts', ['gprID' => $id], ['id' => $row->id]);
            $dataset['just set'][] = $fields->Name;
        } else {
            $dataset['no match'][] = $sql;
        }

    }
    wp_send_json($dataset);
}

add_action('wp_ajax_sf_import_resorts', 'sf_import_resorts');