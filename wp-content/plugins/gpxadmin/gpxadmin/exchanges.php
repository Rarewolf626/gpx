<?php




/**
 *
 *
 *
 *
 */
function get_bonus()
{
    $function = 'DAEGetBonusRentalAvailability';
    if(isset($_GET['function']))
    {
        $function = 'NewAddDAEGetBonusRentalAvailability';
    }
    $month = 3;
    if(isset($_GET['month']))
        $month = $_GET['month'];
    $year = '2019';
    if(isset($_GET['year']))
        $year = $_GET['year'];
    $country = "14";
    if(isset($_GET['country']))
        $country = $_GET['country'];
    $region = "?";
    if(isset($_GET['region']))
        $region = $_GET['region'];
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $inputMembers = array(
        'DAEMemberNo'=>true,
        'CountryID'=>$country,
        'RegionID'=>$region,
        'Month'=>$month,
        'Year'=>$year,
        'WeeksToShow'=>'ALL',
        'Sort'=>'Default',
    );
    if(isset($_GET['quick']))
    {
        $inputMembers['quick'] = true;
    }
    $data = $gpx->$function($inputMembers);
    wp_send_json($data);
}

add_action('wp_ajax_get_bonus', 'get_bonus');
add_action('wp_ajax_nopriv_get_bonus', 'get_bonus');



/**
 *
 *
 *
 *
 */
function get_exchange()
{
    global $wpdb;
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $function = 'DAEGetExchangeAvailability';
    if(isset($_GET['function']))
    {
        $function = 'NewAddDAEGetExchangeAvailability';
    }
    $month = 3;
    if(isset($_GET['month']))
        $month = $_GET['month'];
    $year = '2019';
    if(isset($_GET['year']))
        $year = $_GET['year'];
    $country = "14";
    if(isset($_GET['country']))
        $country = $_GET['country'];
    $region = "?";

    if($country == '14' && $_GET['all'] == 'all')
    {
        $sql = "SELECT a.id FROM wp_properties a
                INNER JOIN wp_resorts  b on a.resortJoinID = b.id
                INNER JOIN wp_gpxRegion c on b.gpxRegionID = c.id
                WHERE c.lft BETWEEN 1939 AND 2924
                AND a.active='1'
                AND b.active='1'
                AND a.WeekType='ExchangeWeek'";
        $toCheck = $wpdb->get_results($sql);
        foreach($toCheck as $tc)
        {
            $allActive[$tc->id] = $tc->id;
        }
        $session = strtotime('NOW');
        $wpdb->insert('wp_refresh_to_remove', array('session'=>$session, 'weeks_all'=>json_encode($allActive)));
        $dbActiveRefresh = $wpdb->insert_id;

        //get all the regions
        $allRegions = [
            'SECA',
            'RMSE',
            'NENG',
            'MWES',
            'MSOU',
            'MATL',
            'HAWA',
            'GULF',
            'PCOA',
        ];
        $pullDates[] = date('n/Y');
        $date  = date('m/d/Y');
        for($i=1;$i<13;$i++)
        {
            $pullDates[] = date('n/Y', strtotime("+".$i." months", strtotime($date)));
        }

        foreach($allRegions as $region)
        {
            foreach($pullDates as $pd)
            {
                $pds = explode("/", $pd);
                $pullyear = $pds[1];
                $pullmonth = $pds[0];
                $inputMembers = array(
                    'DAEMemberNo'=>true,
                    'CountryID'=>$country,
                    'RegionID'=>$region,
                    'Month'=>$pullmonth,
                    'Year'=>$pullyear,
                    'ShowSplitWeeks'=>True,
                );
                if(isset($dbActiveRefresh))
                {
                    $inputMembers['dbActiveRefresh'] = $dbActiveRefresh;
                }
                $data = $gpx->$function($inputMembers);

                if(isset($data['weeks_added']))
                {
                    $addedArr = $data['weeks_added'];
                }
                foreach($addedArr as $ar)
                {
                    unset($allActive[$ar]);
                }
            }
        }

        if(isset($session))
        {
            //get all the weeks that were added
            if(isset($data['weeks_added']))
            {
                $addedArr = $data['weeks_added'];
            }

            foreach($addedArr as $ar)
            {
                unset($allActive[$ar]);
            }
            //now we have all the weeks that aren't active
            foreach($allActive as $aa)
            {
                $wpdb->update('wp_properties', array('active'=>'0'), array('id'=>$aa));
            }
        }
        wp_send_json($data);
    }

    if(isset($_GET['region']))
    {
        $region = $_GET['region'];
    }
    $inputMembers = array(
        'DAEMemberNo'=>true,
        'CountryID'=>$country,
        'RegionID'=>$region,
        'Month'=>$month,
        'Year'=>$year,
        'ShowSplitWeeks'=>True,
    );
    $data = $gpx->$function($inputMembers);
    wp_send_json($data);
}

add_action('wp_ajax_get_exchange', 'get_exchange');
add_action('wp_ajax_nopriv_get_exchange', 'get_exchange');





/**
 *
 *
 *
 *
 */
function get_add_bonus()
{

    global $wpdb;

    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $gpxapi = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $starttime = microtime(true);

    $date = date('Y-m-d H:i:s', strtotime('-7 hours'));
    $dateMinus = strtotime($date) - 82800;

    $dateFrom = date('Y-m-d H:i:s',$dateMinus);


    //now we are going to pull all regions within a country
    if(isset($_GET['country']))
    {
        $countries[] = $_GET['country'];
        if(isset($_GET['region']))
        {
            $regions[$_GET['country']][] = $_GET['region'];
        }
        else
        {
            $regions[$_GET['country']][] = '?';
        }
    }
    else
    {
        $sql = "SELECT DISTINCT CountryID FROM wp_daeCountry WHERE (CountryID <> '14' OR CountryID <> '7' OR CountryID <> '26') AND active=1";
        $allCountries = $wpdb->get_results($sql);
        foreach($allCountries as $oneCountry)
        {
            $countries[] = $oneCountry->CountryID;
            $regions[$oneCountry->CountryID][] = '?';
        }
    }

    $allRegionsNA = [
        '4',
        '5',
        '6',
        '13',
        '23',
        '25',
        '14',
    ];

    // europe cannot be all regions
    foreach($allRegionsNA as $ana)
    {
        if(in_array($ana, $countries))
        {
            unset($regions[$ana]);
            $sql = $wpdb->prepare("SELECT DISTINCT RegionID FROM wp_daeRegion WHERE CountryID=%s AND active=1 and RegionID<>'?'", $ana);
            $allRegions = $wpdb->get_results($sql);
            foreach($allRegions as $oneRegion)
            {
                $regions[$ana][] = $oneRegion->RegionID;
            }
        }
    }

    $pullDates[] = date('n/Y');

    for($i=1;$i<13;$i++)
    {
        $pullDates[] = date('n/Y', strtotime("+".$i." months", strtotime($date)));
    }

    foreach($countries as $country)
    {
        foreach($regions[$country] as $region)
        {
            foreach($pullDates as $pd)
            {
                $pds = explode("/", $pd);
                $pullyear = $pds[1];
                $pullmonth = $pds[0];
                $subtime = microtime(true);


                $subtimediff = $starttime - $subtime;
                $inputMembers = array(
                    'DAEMemberNo'=>true,
                    'CountryID'=>$country,
                    'RegionID'=>$region,
                    'Month'=>$pullmonth,
                    'Year'=>$pullyear,
                    'WeeksToShow'=>'ALL',
                    'Sort'=>'Default',
                );

                $data = $gpxapi->NewAddDAEGetBonusRentalAvailability($inputMembers);

                //update the most recent pulls with info...
                $wpdb->insert('wp_daeRefresh', array('called'=>'bonus', 'country'=>$country, 'pulled'=>$pullmonth."/".$pullyear));

            }
        }
    }


    $data = array('success'=>true);

    wp_send_json($data);
}

add_action('wp_ajax_get_add_bonus', 'get_add_bonus');
add_action('wp_ajax_nopriv_get_add_bonus', 'get_add_bonus');




/**
 *
 *
 *
 *
 */
function get_add_exchange()
{

    global $wpdb;

    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $gpxapi = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $starttime = microtime(true);

    $date = date('Y-m-d H:i:s', strtotime('-7 hours'));
    $dateMinus = strtotime($date) - 82800;

    $dateFrom = date('Y-m-d H:i:s',$dateMinus);
    $minute = date('i');

    //now we are going to pull all regions within a country
    if(isset($_GET['country']))
    {
        $countries[] = $_GET['country'];
        if(isset($_GET['region']))
        {
            $regions[$_GET['country']][] = $_GET['region'];
        }
        else
        {
            $regions[$_GET['country']][] = '?';
        }
    }
    else
    {
        $sql = "SELECT DISTINCT CountryID FROM wp_daeCountry WHERE (CountryID <> '14' OR CountryID <> '7' OR CountryID <> '26') AND active=1";
        $allCountries = $wpdb->get_results($sql);
        foreach($allCountries as $oneCountry)
        {
            $countries[] = $oneCountry->CountryID;
            $regions[$oneCountry->CountryID][] = '?';
        }
    }

    $allRegionsNA = [
        '4',
        '5',
        '6',
        '13',
        '23',
        '25',
        '14'
    ];

    // europe cannot be all regions
    foreach($allRegionsNA as $ana)
    {
        if(in_array($ana, $countries))
        {
            unset($regions[$ana]);
            $sql = $wpdb->prepare("SELECT DISTINCT RegionID FROM wp_daeRegion WHERE CountryID=%s AND active=1 and RegionID<>'?'", $ana);
            $allRegions = $wpdb->get_results($sql);
            foreach($allRegions as $oneRegion)
            {
                $regions[$ana][] = $oneRegion->RegionID;
            }
        }
    }

    $pullDates[] = date('n/Y');
    $startDate = date('n');

    for($i=1;$i<13;$i++)
    {
        $pullDates[] = date('n/Y', strtotime("+".$i." months", strtotime($date)));
    }

    foreach($countries as $country)
    {
        foreach($regions[$country] as $region)
        {
            foreach($pullDates as $pd)
            {
                $pds = explode("/", $pd);
                $pullyear = $pds[1];
                $pullmonth = $pds[0];
                $subtime = microtime(true);
                $inputMembers = array(
                    'DAEMemberNo'=>true,
                    'CountryID'=>$country,
                    'RegionID'=>$region,
                    'Month'=>$pullmonth,
                    'Year'=>$pullyear,
                    'ShowSplitWeeks'=>True,
                );

                $data = $gpxapi->NewAddDAEGetExchangeAvailability($inputMembers);

                //update the most recent pulls with info...

                $wpdb->insert('wp_daeRefresh', array('called'=>'exchange', 'country'=>$country, 'pulled'=>$pullmonth."/".$pullyear));
            }
        }

    }

    $data = array('success'=>true);

    wp_send_json($data);
}

add_action('wp_ajax_get_add_exchange', 'get_add_exchange');
add_action('wp_ajax_nopriv_get_add_exchange', 'get_add_exchange');




/**
 *
 *
 *
 *
 */
function gpx_deposit_on_exchange()
{
    global $wpdb;
    //     is this an agent
    $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $_POST['cid'] ) );

    $sql = $wpdb->prepare("SELECT b.resortID FROM wp_room a
            INNER JOIN wp_resorts b ON b.id=a.resort
            WHERE a.record_id=%s", $_POST['pid']);
    $row = $wpdb->get_row($sql);

    $sql = $wpdb->prepare("SELECT * FROM wp_resorts_meta WHERE ResortID=%s", $row->resortID);

    $resortMetas = $wpdb->get_results($sql);

    $rmFees = [
        'LateDepositFeeOverride'=>[],
    ];
    foreach($resortMetas as $rm)
    {
        //reset the resort meta items
        $rmk = $rm->meta_key;
        if($rmArr = json_decode($rm->meta_value, true))
        {

            foreach($rmArr as $rmdate=>$rmvalues);
            {

                $thisVal = '';
                $rmdates = explode("_", $rmdate);


                // TODO  if do nothing again.. refactor
                if(count($rmdates) == 1 && $rmdates[0] == '0')
                {
                    //do nothing
                }
                else
                {
                    //check to see if the from date has started
                    if($rmdates[0] < strtotime($_POST['Check_In_Date__c']))
                    {
                        //this date has started we can keep working
                    }
                    else
                    {
                        //these meta items don't need to be used
                        continue;
                    }
                    //check to see if the to date has passed
                    if(isset($rmdates[1]) && ($rmdates[1] >= strtotime($_POST['Check_In_Date__c'])))
                    {
                        //these meta items don't need to be used
                        continue;
                    }
                    else
                    {
                        //this date is sooner than the end date we can keep working
                    }
                    foreach($rmvalues as $rmval)
                    {
                        //do we need to reset any of the fees?
                        if(array_key_exists($rmk, $rmFees))
                        {

                            //set this fee
                            if($rmk == 'LateDepositFeeOverride')
                            {
                                if($rmval == '0')
                                {
                                    $skipOverride = '1';
                                }
                                else
                                {
                                    $skipOverride = $rmval;
                                }
                            }
                        }
                    }
                }
            }
        }
    } //end resort meta fees

    if( !isset($skipOverride))
    {

        // TODO more if do nothing - refactor
        if(isset($_POST['add_to_cart']) && $_POST['add_to_cart'] == '2')
        {
            //nothing to do here
        }
        elseif(date("Y-m-d H:i:s", strtotime('+15 days')) > date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])))
        {
            $ldFee = get_option('gpx_late_deposit_fee');

            if(date("Y-m-d H:i:s", strtotime('+7 days')) > date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])))
            {
                $ldFee = get_option('gpx_late_deposit_fee_within');
            }

            $agentReturn = [
                'paymentrequired'=>true,
                'amount'=>$ldFee,
                'type'=>'late_deposit',
                'html'=>'<h5>You will be required to pay a late deposit fee of $'.$ldFee.' to complete this transaction.</h5><br /><br /> <span class="usw-button"><button class="dgt-btn add-fee-to-cart" data-cart="'.$_POST['cartID'].'" data-skip="No">Add To Cart</button>',
            ];

            if(get_current_user_id() != $_POST['cid'])
            {
                $agentReturn['html'] .= '<br /><br /><button class="dgt-btn add-fee-to-cart af-agent-skip" data-cart="'.$_POST['cartID'].'" data-skip="Yes">Waive Fee</button>';
            }

            $_POST['cartID'] = $_COOKIE['gpx-cart'];
            if( !isset($_POST['add_to_cart']) || ( isset($_POST['add_to_cart']) && $_POST['add_to_cart'] != '1' ) )
            {
                $_POST['add_to_cart'] = true;
                $return = $agentReturn;
                $return['posted'] = $_POST;
                wp_send_json($return);
            }
            if($_POST['add_to_cart'] == '1')
            {
                //add this to the cart
                $sql = $wpdb->prepare("SELECT data FROM wp_cart WHERE cartID=%s", $_POST['cartID']);
                $row = $wpdb->get_row($sql);
                $cartData = json_decode($row->data, true);
                $cartData['late_deposit_fee'] = $agentReturn['amount'];
                $cd = [
                    'data'=>json_encode($cartData),
                ];
                $wpdb->update('wp_cart', $cd, array('cartID'=>$_POST['cartID']));
            }
            unset($agentReturn);
        }
    }
    //     }

    $credit = [
        'created_date'=>date('Y-m-d H:i:s'),
        'deposit_year'=>date('Y', strtotime($_POST['Check_In_Date__c'])),
        'resort_name'=>stripslashes(str_replace("&", "&amp;",$_POST['Resort_Name__c'])),
        'check_in_date'=>date('Y-m-d', strtotime($_POST['Check_In_Date__c'])),
        'owner_id'=>$_POST['cid'],
        'interval_number'=>$_POST['Contract_ID__c'],
        'unit_type'=>$_POST['unit_type'],
        'status'=>'DOE',
    ];

    $sql = $wpdb->prepare("SELECT * FROM wp_owner_interval WHERE contractID=%s", $_POST['Contract_ID__c']);
    $interval = $wpdb->get_row($sql);

    foreach($interval as $intK=>$intV)
    {
        $_POST[$intK] = $intV;
    }

    $wpdb->insert('wp_credit', $credit);

    $creditID = $wpdb->insert_id;

    foreach($credit as $ck=>$cv)
    {
        if(empty($_POST[$ck]))
        {
            $_POST[$ck] = $cv;
        }
    }

    $_POST['GPX_Deposit_ID__c'] = $wpdb->insert_id;

    $json = json_encode($_POST);

    $wpdb->insert('wp_gpxDepostOnExchange', array('creditID'=>$creditID, 'data'=>$json));

    if(isset($agentReturn))
    {
        $return = $agentReturn;
        $return['success'] = true;
        $return['id'] = $wpdb->insert_id;
    }
    else
    {
        $return = array('id'=>$wpdb->insert_id);
    }

    wp_send_json($return);
}
add_action('wp_ajax_gpx_deposit_on_exchange', 'gpx_deposit_on_exchange');
add_action('wp_ajax_nopriv_gpx_deposit_on_exchange', 'gpx_deposit_on_exchange');

/**
 *
 *
 *
 *
 */
function gpx_load_exchange_form()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $return = $gpx->get_exchange_form();

    wp_send_json($return);
}
add_action("wp_ajax_gpx_load_exchange_form","gpx_load_exchange_form");
add_action("wp_ajax_nopriv_gpx_load_exchange_form", "gpx_load_exchange_form");


/**
 *
 *
 *
 *
 */
function gpx_hold_limit_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_hold_error_message', $option);

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_hold_limit_submit","gpx_hold_limit_submit");


/**
 *
 *
 *
 *
 */
function gpx_hold_limit_time_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_hold_limt_time', $option);

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_hold_limit_time_submit","gpx_hold_limit_time_submit");

/**
 *
 *
 *
 *
 */
function gpx_hold_limit_timer_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_hold_limt_timer', $option);

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_hold_limit_timer_submit","gpx_hold_limit_timer_submit");


/**
 *
 *
 *
 *
 */
function gpx_exchangefee_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_exchange_fee', $option);

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_exchangefee_submit","gpx_exchangefee_submit");



/**
 *
 *
 *
 *
 */
function gpx_lateDepositFee_submit_within()
{

    $option = $_POST['amt'];

    update_option('gpx_late_deposit_fee_within', $option);

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_lateDepositFee_submit_within","gpx_lateDepositFee_submit_within");

/**
 *
 *
 *
 *
 */
function gpx_fbfee_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_fb_fee', $option);

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_fbfee_submit","gpx_fbfee_submit");

/**
 *
 *
 *
 *
 */
function gpx_min_rental_fee()
{

    $option = $_POST['min_rental'];

    update_option('gpx_min_rental_fee', $option);

    $return = array('success'=>true);

    wp_send_json($return);
}
add_action("wp_ajax_gpx_min_rental_fee","gpx_min_rental_fee");

/**
 *
 *
 *
 *
 */
function gpx_gfamount_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_gf_amount', $option);

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_gfamount_submit","gpx_gfamount_submit");


/**
 *
 *
 *
 *
 */
function gpx_admin_toolbar_link( $wp_admin_bar ) {
    $args = array(
        'id'    => 'gpx_admin',
        'title' => 'GPX Admin',
        'href'  => '/wp-admin/admin.php?page=gpx-admin-page',
        'meta'  => array( 'class' => 'my-toolbar-gpx-page' )
    );
    $wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'gpx_admin_toolbar_link', 999 );

