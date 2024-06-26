<?php


/**
 * @depreacted
 */
function get_dae_users()
{
    global $wpdb;
    $sql = "SELECT * FROM wp_users a
            INNER JOIN wp_usermeta b on a.ID=b.user_id
            WHERE b.meta_key='DAEMemberNo'
            ORDER BY a.ID desc";

    $results = $wpdb->get_results($sql);


    wp_send_json($results);
}
add_action('wp_ajax_get_dae_users', 'get_dae_users');
add_action('wp_ajax_nopriv_get_dae_users', 'get_dae_users');

