<?php


/**
 *
 *
 *
 */
function get_dae_user_info()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    $userdata = $gpx->returnDAEGetMemberDetails($_GET['daememberno']);

    wp_send_json($userdata);
}
add_action('wp_ajax_get_dae_user_info', 'get_dae_user_info');
add_action('wp_ajax_nopriv_get_dae_user_info', 'get_dae_user_info');

/**
 *
 *
 *
 */
function get_dae_users()
{

    global $wpdb;
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);


    $sql = "SELECT * FROM wp_users a
            INNER JOIN wp_usermeta b on a.ID=b.user_id
            WHERE b.meta_key='DAEMemberNo'
            ORDER BY a.ID desc";

    $results = $wpdb->get_results($sql);
    foreach($results as $key=>$result)
    {

    }

    wp_send_json($data);
}
add_action('wp_ajax_get_dae_users', 'get_dae_users');
add_action('wp_ajax_nopriv_get_dae_users', 'get_dae_users');



/**
 *
 *
 *
 */
function create_dae_user()
{
    global $wpdb;
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $memberDetails = array(
        'AccountName'=>'Test, User',
        'Address1'=>'209 S Walnut St',
        'Address3'=>'McPherson',
        'Address4'=>'KS',
        'Address5'=>'USA',
        'Email'=>'chris@gogowebdev.com',
        'Email2'=>'chris@4eightyeast.com',
        'Salutation'=>'Mr',
        'Title1'=>'Title 1',
        'Title2'=>'Title 2',
        'FirstName1'=>'Chris',
        'HomePhone'=>'6207556898',
        'LastName1'=>'Goering',
        'MailName'=>'Chris Goering',
        'NewsletterStatus'=>'NOT_SUBSCRIBED',
        'PostCode'=>'67460',
        'ReferalID'=>'0',
        'MailOut'=>True,
        'SMSStatus'=>'NOT_SUBSCRIBED',
        'SMSNumber'=>'6207556898',
    );

    $data = $gpx->DAECreateMemeber($memberDetails);

    wp_send_json($data);
}



add_action('wp_ajax_create_dae_user', 'create_dae_user');
add_action('wp_ajax_nopriv_create_dae_user', 'create_dae_user');

/**
 *
 *
 *
 */
function gpx_dae_ws_submit()
{

    $field = $_POST['field'];
    $val = $_POST['val'];

    update_option($field, $val);

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_dae_ws_submit","gpx_dae_ws_submit");

