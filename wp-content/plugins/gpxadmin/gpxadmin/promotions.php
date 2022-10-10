<?php

/**
 *
 *
 *
 *
 */
function rework_coupon()
{
    global $wpdb;

    $sql = "SELECT id, Properties FROM `wp_specials` WHERE `Amount` = '100' and SpecUsage='customer' and reworked=1 and active=1 ORDER BY `id`  DESC LIMIT 1";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {
        $data = json_decode($row->Properties);
        $specificCustomer = json_decode($data->specificCustomer, true);
        $useExc = $data->useExc;

        foreach($specificCustomer as $sc)
        {
            $sql = $wpdb->prepare("SELECT new_id FROM vest_rework_users WHERE old_id=%s", $sc);
            $newID = $wpdb->get_var($sql);
            if(!empty($newID))
            {
                if(!in_array($newID, $specificCustomer))
                {
                    $specificCustomer[] = $newID;
                }

                $data->useExc = str_replace('\"'.$sc.'\"', '\"'.$newID.'\"', $data->useExc);
            }
        }

        $upp = json_encode($specificCustomer);
        $data->specificCustomer = $upp;
        $wpdb->update('wp_specials', array('Properties'=>json_encode($data), 'reworked'=>'2'), array('id'=>$row->id));
    }
}
add_action('wp_ajax_gpx_rework_coupon', 'rework_coupon');



/**
 *
 *
 *
 *
 */
function rework_mc_expire()
{
    global $wpdb;

    $sql = "SELECT a.id, b.datetime FROM wp_gpxOwnerCreditCoupon a
            INNER JOIN wp_gpxOwnerCreditCoupon_activity b on b.couponID=a.id
            WHERE a.created_date is null
            AND b.activity='created'";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {
        $wpdb->update('wp_gpxOwnerCreditCoupon', array('expirationDate'=>date('Y-m-d', strtotime($row->datetime."+1 year")), 'created_date'=>date('Y-m-d', strtotime($row->datetime))), array('id'=>$row->id));
    }

    $sql = "SELECT count(id) as cnt FROM `wp_gpxOwnerCreditCoupon` WHERE `created_date` is null";
    $tcnt = $wpdb->get_var($sql);

    if($tcnt > 0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }
    wp_send_json(array('remaining'=>$tcnt));
    wp_die();
}
add_action('wp_ajax_gpx_rework_mc_expire', 'rework_mc_expire');


/**
 *
 *
 *
 *
 */
function get_gpx_promos()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_promos();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_promos', 'get_gpx_promos');
add_action('wp_ajax_nopriv_get_gpx_promos', 'get_gpx_promos');



/**
 *
 *
 *
 *
 */
function get_gpx_desccoupons()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_desccoupons();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_desccoupons', 'get_gpx_desccoupons');



/**
 *
 *
 *
 *
 */
function add_gpx_promo()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_add_gpx_promo($_POST);

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_add_gpx_promo', 'add_gpx_promo');
add_action('wp_ajax_nopriv_add_gpx_promo', 'add_gpx_promo');



/**
 *
 *
 *
 *
 */
function gpx_get_coupon_template()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $selected = '';
    if(isset($_POST['selected']) && !empty($_POST['selected']))
        $selected = $_POST['selected'];

    $templates = $gpx->gpx_retrieve_coupon_templates($selected);

    echo wp_send_json(array('html'=>$templates));
    exit();
}
add_action('wp_ajax_gpx_get_coupon_template', 'gpx_get_coupon_template');
add_action('wp_ajax_nopriv_gpx_get_coupon_template', 'gpx_get_coupon_template');



/**
 *
 *
 *
 *
 */
function gpx_twoforone_validate()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $return = $gpx->get_twoforone_validate($_POST['coupon'], $_POST['setdate'], $_POST['resortID']);

    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_twoforone_validate","gpx_twoforone_validate");
add_action("wp_ajax_nopriv_gpx_twoforone_validate", "gpx_twoforone_validate");


function get_gpx_promoautocouponexceptions()
{
    global $wpdb;

    $data = [];

    $sql = "SELECT * FROM wp_gpx_import_account_credit WHERE is_added=2";
    $results = $wpdb->get_results($sql);

    $i = 0;
    foreach($results as $row)
    {
        $data[$i]['account'] = $row->account;
        $data[$i]['business_date'] = $row->business_date;
        $data[$i]['amount'] = $row->amount;
        $i++;
    }

    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_get_gpx_promoautocouponexceptions', 'get_gpx_promoautocouponexceptions');


/**
 *
 *
 *
 *
 */
function gpx_promo_dup_check()
{
    global $wpdb;

    $data = array('success'=>true);

    if(isset($_POST['slug']))
    {
        $sql = $wpdb->prepare("SELECT * FROM wp_specials WHERE slug LIKE %s", $wpdb->esc_like($_POST['slug']));
        $row =  $wpdb->get_row($sql);

        if(!empty($row))
        {
            $data = array('error'=>'You already used this slug.');
        }
    }

    wp_send_json($data);
}
add_action('wp_ajax_gpx_promo_dup_check', 'gpx_promo_dup_check');


/**
 *
 *
 *
 *
 */
function get_gpx_promoautocoupons()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_promoautocoupons();

    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_get_gpx_promoautocoupons', 'get_gpx_promoautocoupons');
add_action('wp_ajax_nopriv_get_gpx_promoautocoupons', 'get_gpx_promoautocoupons');
