<?php


/**
 *
 *
 *
 *
 */
function gpx_add_fee_to_cart()
{
    global $wpdb;

    $return = [];

    if(isset($_POST['tempID']))
    {
        $tempID = $_POST['tempID'];
        $skip = $_POST['skip'];
        $fee = $_POST['fee'];
        $type = $_POST['type'];

        //get the details that need to be added
        $sql = $wpdb->prepare("SElECT * FROM wp_temp_cart WHERE id=%s", $tempID);
        $tempRow = $wpdb->get_row($sql);

        $cid = $tempRow->user_id;
        $tempData = json_decode($tempRow->data, true);

        if($skip == 'Yes')
        {

            //add the deposit
            if($tempRow->item == 'deposit')
            {
                $return = gpx_post_will_bank($tempData, $cid);
            }

            if($tempRow->item == 'guest')
            {
                $return = gpx_reasign_guest_name($tempData, $cid);
            }

        }
        else
        {
            //add to the cart

            $_POST['user_type'] = 'Owner';
            $loggedinuser =  get_current_user_id();
            if($loggedinuser != $cid)
            {
                $user_type = 'Agent';
            }

            $user = get_userdata($cid);
            if(isset($user) && !empty($user))
            {
                $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $_POST['user'] ) );
            }

            $searchSessionID = '';
            if(isset($usermeta->searchSessionID))
            {
                $searchSessionID = $usermeta->searchSessionID;
            }

            $cartID = $tempRow->id."_".$cid;
            if(!empty($_COOKIE['gpx-cart']))
            {
                $cartID = $_COOKIE['gpx-cart'];
            }

            $sql = $wpdb->prepare("SELECT id, data FROM wp_cart WHERE cartID=%s", $cartID);
            $row = $wpdb->get_row($sql);

            $cart = [
                'user'=>$cid,
                $type=>$fee,
            ];

            if(!empty($row))
            {
                $jsonData = json_decode($row->data, true);
                foreach($jsonData as $jdK=>$jdV)
                {
                    if(!isset($cart[$jdK]))
                    {
                        $cart[$jdK] = $jdV;
                    }
                }
            }
            $json = json_encode($_POST);

            $data['data'] = $json;

            if(!empty($row))
            {
                $update = $wpdb->update('wp_cart', $data, array('id'=>$row->id));
            }
            else
            {
                $data['user'] = $cid;
                $data['cartID'] = $cartID;
                $data['sessionID'] = $searchSessionID;
                $data['propertyID'] = 'nobook';
                $data['weekId'] = '1';
                $insert = $wpdb->insert('wp_cart', $data);
            }

            $return['redirect'] = true;
            $return['cartid'] = $cartID;
        }
    }
    if (!empty($cartID)) {
        setcookie('gpx-cart', $cartID, 0, '/', parse_url(site_url(), PHP_URL_HOST), true, false);
    }

    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_add_fee_to_cart","gpx_add_fee_to_cart");


/**
 *
 *
 *
 *
 */
function gpx_remove_from_cart_fn()
{
    global $wpdb;

    require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    if(empty($_GET['pid']))
    {
        $cart = $_COOKIE['gpx-cart'];
        $wpdb->delete('wp_cart', array('cartID'=>$cart));
        $output['rr'] = 'redirect';
    }
    else
    {
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $_GET['cid'] ) );

        $activeUser = get_userdata(get_current_user_id());

        $sql = $wpdb->prepare("SELECT propertyID, data FROM wp_gpxPreHold WHERE user=%s AND weekId=%s", [$_GET['cid'], $_GET['pid']]);
        $row = $wpdb->get_row($sql);

        $holdDets = json_decode($row->data, true);
        $holdDets[strtotime('now')] = [
            'action'=>'released',
            'by'=>$activeUser->first_name." ".$activeUser->last_name,
        ];

        $sql = $wpdb->prepare("SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId=%s AND cancelled IS NULL", $_GET['pid']);
        $trow = $wpdb->get_var($sql);

        // TODO anothar if do nothing - fix
        if($trow > 0)
        {
            //nothing to do
        }
        else
        {
            //we always need to check the "display date" prior to making it active. Only make this active when the sell date is in the future.
            $sql = $wpdb->prepare("SELECT active_specific_date FROM wp_room WHERE record_id=",$_GET['pid']);
            $activeDate = $wpdb->get_var($sql);

            if(strtotime('NOW') >  strtotime($activeDate))
            {
                $wpdb->update('wp_room', array('active'=>1), array('record_id'=>$_GET['pid']));
            }
        }

        $existsrow_sql = $wpdb->prepare("SELECT id, release_on FROM wp_gpxPreHold WHERE user=%s AND weekId=%s ORDER BY id DESC LIMIT 1", [$_GET['cid'], $_GET['pid']]);
        $exist_hold_row = $wpdb->get_row($existsrow_sql);
        if(isset($exist_hold_row->id)){

            $wpdb->query($wpdb->prepare("DELETE FROM `wp_gpxPreHold` WHERE user=%s AND weekId=%s AND id != %d", [$_GET['cid'], $_GET['pid'], $exist_hold_row->id]));
            $wpdb->last_query;

        }


        $wpdb->update('wp_gpxPreHold', array('released'=>1, 'data'=>json_encode($holdDets)), array('user'=>$_GET['cid'], 'weekId'=>$_GET['pid']));

        $remove = array(
            'user'=>$_GET['cid'],
            'propertyID'=>$_GET['pid'],
        );
        $output = array();
        if(!isset($_GET['nocart']))
        {
            $wpdb->delete('wp_cart', $remove);

            $sql = $wpdb->prepare("SELECT * FROM wp_cart WHERE cartID=%s", $_COOKIE['gpx-cart']);
            $cart = $wpdb->get_results($sql);
            if(!empty($cart))
            {
                //update coupons for any cart
                foreach($cart as $value)
                {
                    $data = json_decode($value->data);
                    if(isset($data->coupon))
                        unset($data->coupon);

                    $update = json_encode($data);
                    $wpdb->update('wp_cart', array('data'=>$update), array('id'=>$value->id));
                }

                $output['rr'] = 'refresh';
            }
            else
            {
                $output['rr'] = 'redirect';
            }
        }
    }
    echo wp_send_json($output);
    exit();
}
add_action("wp_ajax_gpx_remove_from_cart","gpx_remove_from_cart_fn");
add_action("wp_ajax_nopriv_gpx_remove_from_cart", "gpx_remove_from_cart_fn");

