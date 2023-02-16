<?php

use GPX\Form\Admin\TradePartner\AddTradePartnerForm;

function gpx_partner_add()
{
    global $wpdb;
    /** @var AddTradePartnerForm $form */
    $form   = gpx(AddTradePartnerForm::class);
    $values = $form->validate();

    $user    = [
        'user_pass'  => wp_generate_password(),
        'user_login' => sanitize_user($values['username'], true),
        'user_email' => $values['email'],
        'first_name' => $values['name'],
        'nickname'   => $values['name'],
        'role'       => 'gpx_trade_partner',
    ];
    $user_id = wp_insert_user($user);
    if (is_wp_error($user_id)) {
        wp_send_json(['success' => false, 'message' => $user_id->get_error_message()], 500);
    }
    $userrole = new WP_User($user_id);
    $userrole->add_role('gpx_member');
    //add the details to the wp_partners table
    $insert = [
        'user_id'       => $user_id,
        'create_date'   => date('Y-m-d H:i:s'),
        'username'      => $user['user_login'],
        'name'          => $values['name'],
        'email'         => $values['email'],
        'phone'         => $values['phone'],
        'address'       => $values['address'],
        'sf_account_id' => $values['sf_account_id'],
    ];

    $wpdb->insert('wp_partner', $insert);

    wp_send_json(['success' => true, 'message' => 'Trade partner was added']);
}
add_action('wp_ajax_gpx_partner_add', 'gpx_partner_add');
add_action('wp_ajax_nopriv_gpx_partner_add', 'gpx_partner_add');

/**
 *
 *
 *
 *
 */
function partner_autocomplete(){
    global $wpdb;

    $search = $_REQUEST['search'];
    $type = $_REQUEST['type'];
    $acType = $_REQUEST['actype'];


    if($_REQUEST['availability'])
    {

        if($type == '3')
        {
            $partnerSearch = true;
        }
        if($type == '2')
        {
            $ownerSearch = true;
        }
    }
    else
    {
        if($type == '1')
        {
            $ownerSearch = true;
        }
        if($type == '3')
        {
            $partnerSearch = true;
        }
    }
    //
    if($partnerSearch)
    {
        $sql = $wpdb->prepare("SELECT user_id, name FROM `wp_partner` WHERE name like %s GROUP BY record_id ORDER BY `type` ASC", '%'.$wpdb->esc_like($search).'%');
    }
    else
    {
        $sql = $wpdb->prepare("SELECT user_id, SPI_Owner_Name_1st__c as name  FROM `wp_GPR_Owner_ID__c` WHERE name like '%s or `SPI_Owner_Name_1st__c` LIKE %s GROUP BY user_id ORDER BY `SPI_Owner_Name_1st__c` ASC", ['%'.$wpdb->esc_like($search).'%','%'.$wpdb->esc_like($search).'%']);
    }

    $rows = $wpdb->get_results($sql);
    $response = array();

    foreach ($rows as $row) {
        if($acType == 'select2')
        {
            $response['items'][] = array("id"=>$row->user_id,"text"=>$row->name);
        }
        else
        {
            $response[] = array("value"=>$row->user_id,"label"=>$row->name);
        }
    }

    wp_send_json($response);
}
add_action('wp_ajax_partner_autocomplete', 'partner_autocomplete');
add_action('wp_ajax_nopriv_partner_autocomplete', 'partner_autocomplete');



/**
 *
 *
 *
 *
 */
function gpx_impot_partners()
{
    global $wpdb;

    $sql = "SELECT * FROM wp_partner WHERE user_id IS NULL";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {
        $user = get_user_by('email', $row->email);

        if(!empty($user))
        {
            $userID = $user->ID;
        }
        else
        {

            $userID = wp_create_user( $row->email, wp_generate_password(), $row->email );

        }

        $userdets = [
            'ID'=>$userID,
            'first_name'=>$row->name,
        ];

        $up = wp_update_user($userdets);

        update_user_meta($userID, 'first_name', $row->name);
        update_user_meta($userID, 'DAEMemberNo', $row->username);

        $wpdb->update('wp_partner', array('user_id'=>$userID), array('record_id'=>$row->record_id));
    }
}
add_action('wp_ajax_gpx_impot_partners', 'gpx_impot_partners');


/**
 *
 *
 *
 *
 */
function gpx_partner_credits()
{
    global $wpdb;

    $sql = "SELECT * FROM wp_gpxTransactions";


    $sql = "SELECT record_id FROM import_partner_credits WHERE imported=0 LIMIT 1000";
    $rows = $wpdb->get_results($sql);


    foreach($rows as $row)
    {
        $wpdb->update('import_partner_credits', array('imported'=>1), array('id'=>$row->id));

        $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s", $row->record_id);
        $dweek = $wpdb->get_var($sql);

        $dp = [
            'transactionID'=>$dweek,
            'record_id'=>$row->record_id,
        ];

        $exception = json_encode($row);
        $wpdb->insert("reimport_exceptions", array('type'=>'delete trade partner', 'data'=>$exception));

        //temp delete
        $wpdb->delete('wp_gpxTransactions', array('weekId'=>$row->record_id));
        $wpdb->delete('wp_room', array('record_id'=>$row->record_id));


        $raw_spd = $row->source_partner_id;
        if($raw_spd == 'GPR')
        {
            $raw_spd = '';
        }
        else
        {
            $sql = $wpdb->prepare("SELECT user_id FROM wp_partner WHERE username=%s", $raw_spd);
            $spd = $wpdb->get_var($sql);

            if(empty($spd))
            {
                $exception = json_encode($row);
                continue;
            }
        }

        $gpd = '';

        if(!empty($row->Given_to_Partner_id))
        {
            $sql = $wpdb->prepare("SELECT user_id, name FROM wp_partner WHERE username=%s", $row->Given_to_Partner_id);
            $gpdRow = $wpdb->get_row($sql);

            $gpd = $gpdRow->user_id;
            $gpName = $gpdRow->name;

            if(empty($gpd))
            {
                $exception = json_encode($row);
                $wpdb->insert("reimport_exceptions", array('type'=>'partner credit given partner id', 'data'=>$exception));
                continue;
            }

        }

        $resortID = $row->resort;
        $sql = $wpdb->prepare("SELECT resortID, ResortName FROM wp_resorts WHERE id=%s", $resortID);
        $resort = $wpdb->get_row($sql);

        $daeResortID = $resort->resortID;
        $ResortName = $resort->ResortName;

        $unitType = $row->Unit_Type;
        $sql = $wpdb->prepare("SELECT record_id FROM wp_unit_type WHERE resort_id=%s AND name=%s", [$resortID, $unitType]);
        $unitID = $wpdb->get_var($sql);

        $bs = explode("/", $unitType);
        $beds = $bs[0];
        $beds = str_replace("b", "", $beds);
        if($beds == 'St')
        {
            $beds = 'STD';
        }
        $sleeps = $bs[1];
        if(empty($unitID))
        {
            $insert = [
                'name'=>$unitType,
                'create_date'=>date('Y-m-d'),
                'number_of_bedrooms'=>$beds,
                'sleeps_total'=>$sleeps,
                'resort_id'=>$resortID,
            ];
            $wpdb->insert('wp_unit_type', $insert);
            $unitID = $wpdb->insert_id;
        }

        $sql = $wpdb->prepare("SELECT record_id FROM wp_room WHERE record_id=%s", $row->record_id);
        $room = $wpdb->get_var($sql);

        $active = '0';

        if(trim($row->Active) == '1')
        {
            $active = '1';
        }

        $wp_room = [
            'record_id'=>$row->record_id,
            'active_specific_date' => date("Y-m-d 00:00:00", strtotime($row->active_specific_date)),
            'check_in_date' => date('Y-m-d 00:00:00', strtotime($row->check_in_date)),
            'check_out_date' => date('Y-m-d 00:00:00', strtotime($row->check_out_date)),
            'resort' => $resortID,
            'unit_type' => $unitID,
            'source_num' => $row->source_num,
            'source_partner_id' => $spd,
            'sourced_by_partner_on' => date('Y-m-d 00:00:00', strtotime($row->sourced_by_partner_on)),
            'resort_confirmation_number' => $row->resort_confirmation_number,
            'active' => $active,
            'availability' => $row->availability,
            'available_to_partner_id' => '0',
            'type' => $row->Type,
            'active_rental_push_date' => date('Y-m-d', strtotime($row->active_rental_push_date)),
            'price' => '0',
            'points' => NULL,
            'note' => '',
            'given_to_partner_id' => $row->Given_to_Partner_id,
            'import_id' => '0',
            'active_type' => '0',
            'active_week_month' => '0',
            'create_by' => '5',
            'archived' => '0',
        ];
        $sql = $wpdb->prepare("SELECT record_id FROM wp_room WHERE record_id=%s", $row->record_id);
        $week = $wpdb->get_row($sql);

        if(empty($gpd))
        {
            //was this transaction added before?
            $sql = $wpdb->prepare("SELECT id, weekId, data FROM wp_gpxTransactions WHERE weekId=%s", $row->record_id);
            $dups = $wpdb->get_results($sql);
            foreach($dups as $dup)
            {
                $dlt = false;
                $dupid = $dup->id;
                $dupJSON = json_decode($dup->data);
                if(empty($dupJSON->MemberNumber))
                {

                    $wpdb->delete('wp_gpxTransactions', array('id'=>$dupid));
                    $dlt = true;
                    break;
                }
                $sql = $wpdb->prepare("SELECT weekId FROM transactions_import WHERE MemberNumber=%s AND weekId=%s", [$dupJSON->MemberNumber, $row->record_id]);
                $nd = $wpdb->get_var($sql);
                if(empty($nd))
                {

                    $wpdb->delete('wp_gpxTransactions', array('id'=>$dupid));
                    $dlt = true;
                    break;
                }
            }
            //add to the exception report for traci
            if($dlt)
            {
                $exception = json_encode($dups);
                $wpdb->insert("reimport_exceptions", array('type'=>'Transaction Import Error Transaction Deleted', 'data'=>$exception));
            }
        }
        else
        {
            continue;
            $cpo = "NOT TAKEN";

            $data = [
                "MemberNumber"=>$row->Given_to_Partner_id,
                "MemberName"=>$gpName,
                "GuestName"=>'',
                "Adults"=>2,
                "Children"=>0,
                "UpgradeFee"=>'',
                "CPO"=>$cpo,
                "CPOFee"=>'',
                "Paid"=>'0',
                "Balance"=>"0",
                "ResortID"=>$daeResortID,
                "ResortName"=>$ResortName,
                "room_type"=>$row->Unit_Type,
                "WeekType"=>'Exchange',
                "sleeps"=>$sleeps,
                "bedrooms"=>$beds,
                "Size"=>$unitType,
                "noNights"=>"7",
                "checkIn"=>date('Y-m-d', strtotime($row->check_in_date)),
                "processedBy"=>5,
                'actWeekPrice' => '0',
                'actcpoFee' => '0',
                'actextensionFee' => '0',
                'actguestFee' => '0',
                'actupgradeFee' => '0',
                'acttax' => '0',
                'actlatedeposit' => '0',
            ];

            $wp_gpxTransactions = [
                'transactionType' => 'booking',
                'cartID' => $gpd.'-'.$row->record_id,
                'sessionID' => '',
                'userID' => $gpd,
                'resortID' => $daeResortID,
                'weekId' => $row->record_id,
                'check_in_date' => date('Y-m-d', strtotime($row->check_in_date)),
                'datetime' => date('Y-m-d', strtotime($row->sourced_by_partner_on)),
                'depositID' => NULL,
                'paymentGatewayID' => '',
                'transactionRequestId' => NULL,
                'transactionData' => '',
                'sfid' => '0',
                'sfData' => '',
                'data' => json_encode($data),
            ];

            $sql =  $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s", $row->record_id);
            $weekID = $wpdb->get_var($sql);

            if(!empty($weekID))
            {
                $exception = json_encode($row);
                $wpdb->insert("reimport_exceptions", array('type'=>'partner import duplicate week', 'data'=>$exception));
                continue;
            }

            $wpdb->insert('wp_gpxTransactions', $wp_gpxTransactions);
            if($wpdb->last_error)
            {
                $exception = json_encode($row);
                $wpdb->insert("reimport_exceptions", array('type'=>'partner credit insert transaction', 'data'=>$exception));
            }
        }
    }
    $sql = "SELECT count(id) as cnt FROM import_partner_credits WHERE imported=0 LIMIT 100";
    $remain = $wpdb->get_var($sql);

    if($remain > 0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('remaining'=>$remain));
}
add_action('wp_ajax_gpx_partner_credits', 'gpx_partner_credits');



/**
 *
 *
 *
 *
 */
function get_gpx_tradepartners()
{
    global $wpdb;

    $sql = "SELECT * FROM wp_partner";
    $partners = $wpdb->get_results($sql);

    $i = 0;

    foreach($partners as $partner)
    {
        $sql = $wpdb->prepare("SELECT id FROM wp_gpxPreHold WHERE released='0' AND user=%s", $partner->user_id);
        $holds = $wpdb->get_results($sql);

        $data[$i]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=tradepartners_edit&id='.$partner->record_id.'" class="tp-in-modal data-user" data-user="'.$partner->user_id.'" data-type="edit" data-title="Edit '.$partner->name.'" data-select="admin-modal-content"><i class="fa fa-pencil"></i></a>';
        $data[$i]['edit'] .= '&nbsp;&nbsp;<a id="tp_id_'.$partner->user_id.'" href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=tradepartners_view&id='.$partner->user_id.'" class="tp-in-modal" data-type="activity" data-title="'.$partner->name.'" data-select="admin-modal-content"><i class="fa fa-eye"></i></a>';
        $data[$i]['edit'] .= '&nbsp;&nbsp;<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_add&tp='.$partner->user_id.'" class="tp-in-modal" data-type="add" data-title="Add Week for '.$partner->name.'" data-select="admin-modal-content"><i class="fa fa-plus"></i></a>';
        $data[$i]['edit'] .= '&nbsp;&nbsp;<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=tradepartners_inventory&tp='.$partner->user_id.'" class="tp-in-modal" data-type="inventory" data-title="'.$partner->name.'" data-select="admin-modal-content"><i class="fa fa-minus"></i></a>';
        $data[$i]['edit'] .= '&nbsp;&nbsp;<a href="#" class="debitModal" data-toggle="modal" data-target="#gpxModalBalance" data-name="'.$partner->name.'" data-balance="'.$partner->debit_balance.'" data-id="'.$partner->user_id.'"><i class="fa fa-usd"></i></a>';
        $data[$i]['name'] = $partner->name;
        $data[$i]['email']= $partner->email;
        $data[$i]['phone'] = $partner->phone;
        $data[$i]['address'] = $partner->address;
        $data[$i]['rooms_given'] = $partner->no_of_rooms_given;
        $data[$i]['rooms_received'] = $partner->no_of_rooms_received_taken;
        $data[$i]['trade_balance'] = $partner->trade_balance;
        $data[$i]['holds'] = count($holds);
        $i++;
    }

    wp_send_json($data);
}
add_action("wp_ajax_get_gpx_tradepartners", "get_gpx_tradepartners");

