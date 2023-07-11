<?php

use GPX\Model\UserMeta;
use Illuminate\Support\Arr;


/**
 *
 *
 *
 *
 */
function gpx_post_will_bank($postdata='', $addtocart = '')
{
    global $wpdb;
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    if(!empty($postdata))
    {
        $_POST = (array) $postdata;
    }
    $msg = '';

    $cid = gpx_get_switch_user_cookie();
    $usermeta = UserMeta::load($cid);

    $depositBy = stripslashes(str_replace("&", "&amp;",$usermeta->FirstName1))." ".stripslashes(str_replace("&", "&amp;",$usermeta->LastName1));

    $agent = false;
    if($cid != get_current_user_id())
    {
        $agent = true;
        $agentmeta =  UserMeta::load(get_current_user_id());
        $depositBy = stripslashes(str_replace("&", "&amp;",$agentmeta->first_name))." ".stripslashes(str_replace("&", "&amp;",$agentmeta->last_name));

    }

    $weekTypeError = false;
    if(isset($_POST['OwnershipType']))
    {
        switch($_POST['Usage__c'])
        {
            case 'Odd':
                if(date('Y', strtotime($_POST['Check_In_Date__c'])) % 2 == 0)
                    $weekTypeError = true;
                break;

            case 'Even':
                if(date('Y', strtotime($_POST['Check_In_Date__c'])) % 2 != 0)
                    $weekTypeError = true;
                break;

            default:
                $weekTypeError = false;
                break;

        }
    }

    $sql = $wpdb->prepare("SELECT deposit_year FROM wp_credit WHERE deposit_year=%s AND interval_number=%s", [date('Y', strtotime($_POST['Check_In_Date__c'])), $_POST['Contract_ID__c']]);
    $duplicateYear = $wpdb->get_row($sql);


    /*
     * Ok, new thing - I just logged in as OwnerRepository Talie Dwayne Scott and tried to do a late
     * deposit, but the option to bank within 14 days isn't there. It's functioning like
     * prod does now, so I can't deposit multiple weeks for the same year either (which
     * is allowed in beta).
     */
    if($agent)
    {
        //if this is an agent then duplicate year doesn't matter and datechecks only need payment form
        if( date("Y-m-d H:i:s", strtotime('+15 days')) > date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])))
        {
            $ldFee = get_option('gpx_late_deposit_fee');

            if(date("Y-m-d H:i:s", strtotime('+7 days')) > date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])))
            {
                $ldFee = get_option('gpx_late_deposit_fee_within');
            }
        }
    }

    elseif(date("Y-m-d H:i:s") > date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])))
    {
        $return = array('success'=>true, 'message'=>'You are not allowed to bank a previous date!');
    }
    elseif(date("Y-m-d H:i:s", strtotime('+15 days')) > date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])))
    {
        $ldFee = get_option('gpx_late_deposit_fee');

        if(date("Y-m-d H:i:s", strtotime('+7 days')) > date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])))
        {
            $ldFee = get_option('gpx_late_deposit_fee_within');
        }
    }
    elseif(date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])) > date("Y-m-d H:i:s", strtotime('+2 years')))
    {
        $return = array('success'=>true, 'message'=>'You are allowed to bank up to two years from today!  Please call us if you feel this is an error.');
    }
    elseif(isset($weekTypeError) && $weekTypeError)
    {
        $return = array('success'=>true, 'message'=>'Your ownership includes '.strtolower($_POST['Usage__c']).' year entitlement.  Please select an applicable date.  Reservations for your owner use week should be made prior to depositing in GPX.');
    }


    if(empty($returntocart) && isset($return))
    {
        wp_send_json($return);
    }

    $sql = $wpdb->prepare("SELECT b.resortID FROM  wp_resorts b WHERE b.gprID=%s", $_POST['GPX_Resort__c']);
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

                // TODO more if do nothing - fix
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
                                    $ldFee = '';
                                }
                                else
                                {
                                    $ldFee = $rmval;
                                }
                            }
                        }
                    }
                }
            }
        }
    } //end resort meta fees

    if(!isset($return['succes']))
    {

        //add to database
        $db = [
            'created_date' => date('Y-m-d H:i:s'),
            'interval_number' => $_POST['Contract_ID__c'],
            'resort_name' => stripslashes(str_replace("&", "&amp;",$_POST['Resort_Name__c'])),
            'deposit_year' => date('Y', strtotime($_POST['Check_In_Date__c'])),
            'check_in_date' => date('Y-m-d', strtotime($_POST['Check_In_Date__c'])),
            'owner_id' => $cid,
            'unit_type' => $_POST['Unit_Type__c'],
            'unitinterval' => $_POST['Resort_Unit_Week__c'],
        ];

        if(!empty($_POST['Reservation__c']))
        {
            $db['reservation_number'] = $_POST['Reservation__c'];
        }

        if(!empty($ldFee) && empty($addtocart))
        {
            //add this to the temp table
            $wpdb->insert('wp_temp_cart', array('item'=>'deposit', 'user_id'=>$cid, 'data'=>json_encode($_POST)));
            $tempID = $wpdb->insert_id;
            $agentReturn = [
                'paymentrequired'=>true,
                'amount'=>get_option('gpx_late_deposit_fee'),
                'type'=>'late_deposit',
                'html'=>'<h5>You will be required to pay a late deposit fee of $'.$ldFee.' to complete this transaction.</h5><br /><br /><span class="usw-button"><button class="dgt-btn add-fee-to-cart-direct" data-type="late_deposit_fee" data-fee="'.$ldFee.'" data-tid="'.$tempID.'" data-cart="" data-skip="No">Add To Cart</button>',
            ];

            if($cid != get_current_user_id())
            {
                $agentReturn['html'] .= '<br /><br /><button class="dgt-btn add-fee-to-cart-direct af-agent-skip" data-fee="'.$ldFee.'" data-tid="'.$tempID.'" data-type="late_deposit_fee" data-cart="" data-skip="Yes">Waive Fee</button>';
            }
        }
        else
        {
            $wpdb->insert('wp_credit', $db);

            $insertid = $wpdb->insert_id;
            $_POST['GPX_Deposit_ID__c'] = $wpdb->insert_id;

            //send the details to SF
            $sf = Salesforce::getInstance();

            $sql = $wpdb->prepare("SELECT RIOD_Key_Full FROM wp_mapuser2oid WHERE gpx_user_id=%s AND unitweek=%s", [$cid, $_POST['Resort_Unit_Week__c']]);
            $roid = $wpdb->get_var($sql);
            //get the ownership interval id
            $query = $wpdb->prepare("SELECT ID, Name FROM Ownership_Interval__c WHERE ROID_Key_Full__c = %s", $roid);
            $results = $sf->query($query);
            $interval = $results ? Arr::first($results)->Id : null;

            $email = gpx_get_user_email($cid);

            $sfDepositData = [
                'Check_In_Date__c'=>date('Y-m-d', strtotime($_POST['Check_In_Date__c'])),
                'Deposit_Year__c'=>date('Y', strtotime($_POST['Check_In_Date__c'])),
                'Account_Name__c'=>$_POST['Account_Name__c'],
                'GPX_Member__c'=>$cid,
                'Deposit_Date__c'=>date('Y-m-d'),
                'Resort__c'=>$_POST['GPX_Resort__c'],
                'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;",$_POST['Resort_Name__c'])),
                'Resort_Unit_Week__c'=>$_POST['Resort_Unit_Week__c'],
                'GPX_Deposit_ID__c'=>$_POST['GPX_Deposit_ID__c'],
                'Coupon__c'=>$_POST['twofer'],
                'Unit_Type__c'=>$_POST['Unit_Type__c'],
                'Member_Email__c'=>$email,
                'Member_First_Name__c'=>stripslashes(str_replace("&", "&amp;",$usermeta->FirstName1)),
                'Member_Last_Name__c'=>stripslashes(str_replace("&", "&amp;",$usermeta->LastName1)),
                'Ownership_Interval__c'=>$interval,
                'Deposited_by__c'=>$depositBy,
            ];
            if(!empty($_POST['Reservation__c']))
            {
                $sfDepositData['Reservation__c'] = $_POST['Reservation__c'];
            }

            $sfType = 'GPX_Deposit__c';
            $sfObject = 'GPX_Deposit_ID__c';

            $sfFields = [];
            $sfFields[0] = new SObject();
            $sfFields[0]->fields = $sfDepositData;
            $sfFields[0]->type = $sfType;

            $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);

            $record = $sfDepositAdd[0]->id;

            $wpdb->update('wp_credit', array('record_id'=>$record), array('id'=>$insertid));

            $msg = "Your week has been banked. Please allow 48-72 hours for our system to verify the transaction.";
        }
        if(isset($agentReturn))
        {
            $return = $agentReturn;
            $return['credit'] = 1;
            $return['success'] = true;
            $return['message'] = $msg;
        }
        else
        {
            $return = array('credit'=>1, 'success'=>true, 'message'=>$msg, 'creditid'=>$insertid);
        }
    }

    if(!empty($addtocart))
    {
        return $return;
    }
    else
    {
        wp_send_json($return);
    }
}
add_action("wp_ajax_gpx_post_will_bank","gpx_post_will_bank");
add_action("wp_ajax_nopriv_gpx_post_will_bank", "gpx_post_will_bank");

/**
 *
 *
 *
 *
 */
function gpx_alert_submit()
{

    $option = $_POST['msg'];

    update_option('gpx_alert_msg_msg', $option);

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_alert_submit","gpx_alert_submit");


/**
 *
 *
 *
 *
 */
function gpx_switch_alert()
{

    $option = $_POST['active'];

    update_option('gpx_alert_msg_active', $option);

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_switch_alert","gpx_switch_alert");


/**
 *
 *
 *
 *
 */
function gpx_switch_booking_disabled()
{

    $option = $_POST['active'];

    update_option('gpx_booking_disabled_active', $option);

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_switch_booking_disabled","gpx_switch_booking_disabled");


/**
 *
 *
 *
 *
 */
function gpx_booking_disabled_submit()
{

    $option = $_POST['msg'];

    update_option('gpx_booking_disabled_msg', $option);

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_booking_disabeled_submit","gpx_booking_disabled_submit");

/**
 *
 *
 *
 *
 */
function gpx_ExtensionFee_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_extension_fee', $option);

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_ExtensionFee_submit","gpx_ExtensionFee_submit");


/**
 *
 *
 *
 *
 */
function gpx_lateDepositFee_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_late_deposit_fee', $option);

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_lateDepositFee_submit","gpx_lateDepositFee_submit");


