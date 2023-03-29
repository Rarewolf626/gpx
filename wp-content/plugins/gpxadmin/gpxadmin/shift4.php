<?php

use Illuminate\Support\Arr;

/**
 *
 *
 *
 *
 */
function gpx_shiftfour_sale_test()
{
    require_once GPXADMIN_API_DIR.'/functions/class.shiftfour.php';
    $shift4 = new Shiftfour();

    $data = $shift4->shift_auth();

    wp_send_json($data);
}
add_action('wp_ajax_gpx_shiftfour_sale_test', 'gpx_shiftfour_sale_test');
add_action('wp_ajax_nopriv_gpx_shiftfour_sale_test', 'gpx_shiftfour_sale_test');


/**
 *
 *
 *
 *
 */
function gpx_i4goauth()
{
    require_once GPXADMIN_API_DIR.'/functions/class.shiftfour.php';
    $shift4 = new Shiftfour();

    $i4go = $shift4->i_four_go_auth();

    $data = [
        'data' => json_decode($i4go['i4go']),
        'paymentID' => $i4go['paymentID'],
    ];

    wp_send_json($data);
}
add_action('wp_ajax_gpx_i4goauth', 'gpx_i4goauth');
add_action('wp_ajax_nopriv_gpx_i4goauth', 'gpx_i4goauth');


/**
 *
 *
 *
 *
 */
function gpx_14gostatus()
{
    global $wpdb;

    //we just need to update the database with the response
    $update = $_REQUEST['data'];

    $update['i4go_object'] = json_encode($update['otn']);
    $update = Arr::only($update, [
        'i4go_response', 'i4go_responsetext', 'i4go_accessblock', 'i4go_cardtype',
        'i4go_responsecode', 'i4go_object', 'i4go_streetaddress', 'i4go_postalcode',
        'i4go_cardholdername', 'i4go_expirationmonth', 'i4go_expirationyear', 'i4go_uniqueid',
        'i4go_utoken'
    ]);

    $wpdb->update('wp_payments', $update, array('id' => $_REQUEST['paymentID']));
    $data['i4go_response'] = $update['i4go_response'];
    $data['i4go_responsecode'] = $update['i4go_responsecode'];
    if (isset($update['i4go_responsetext'])) {
        //just the text
        $responsetext = explode(" (", $update['i4go_responsetext']);
        $data['i4go_responsetext'] = $responsetext[0];
    }
    $data['paymentID'] = $_REQUEST['paymentID'];

    wp_send_json($data);
}
add_action('wp_ajax_gpx_14gostatus', 'gpx_14gostatus');
add_action('wp_ajax_nopriv_gpx_14gostatus', 'gpx_14gostatus');

/**
 *
 *
 *
 *
 */
function gpx_cc_fix()
{
    global $wpdb;

    $sql = "SELECT * FROM `wp_gpxMemberSearch` WHERE `data` LIKE '%CardNo%'";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {
        $data = json_decode($row->data);
        foreach($data as $mk=>$d)
        {
            foreach($d as $k=>$v)
            {
                $oldNum = '';
                $newNum = '';
                if($k == 'Payment')
                {
                    $oldNum = $v->CardNo;
                    $newNum = substr($v->CardNo, -4);
                    if($oldNum != $newNum)
                    {
                        $data->$mk->Payment->CardNo = $newNum;
                        $updata = json_encode($data);
                        $wpdb->update('wp_gpxMemberSearch', array('data'=>$updata), array('id'=>$row->id));
                    }
                }
            }

        }

    }

}
add_action("wp_ajax_gpx_cc_fix","gpx_cc_fix");
add_action("wp_ajax_nopriv_gpx_cc_fix", "gpx_cc_fix");


