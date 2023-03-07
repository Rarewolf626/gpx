<?php

/**
 *
 *
 *
 *
 */
function rework_interval()
{
    global $wpdb;

    $sf = Salesforce::getInstance();

    $sql = "SELECT * FROM wp_GPR_Owner_ID__c WHERE meta_rework=0 ORDER BY `user_id` DESC LIMIT 500";
    $users = $wpdb->get_results($sql);

    $selects = [
        'CreatedDate'=>'CreatedDate',
        'DAEMemberNo'=>'Name',
        'first_name'=>'SPI_First_Name__c',
        'last_name'=>'SPI_Last_Name__c',
        'FirstName1'=>'SPI_First_Name__c',
        'FirstName2'=>'SPI_First_Name2__c',
        'LastName1'=>'SPI_Last_Name__c',
        'LastName2'=>'SPI_Last_Name2__c',
        'email'=>'SPI_Email__c',
        'phone'=>'SPI_Home_Phone__c',
        'DayPhone'=>'SPI_Home_Phone__c',
        'work_phone'=>'SPI_Work_Phone__c',
        'address'=> 'SPI_Street__c',
        'Address1'=> 'SPI_Street__c',
        'city'=>'SPI_City__c',
        'Address3'=>'SPI_City__c',
        'state'=>'SPI_State__c',
        'Address4'=>'SPI_State__c',
        'zip'=>'SPI_Zip_Code__c',
        'Address5'=>'SPI_Zip_Code__c',
        'PostCode'=>'SPI_Zip_Code__c',
        'country'=>'SPI_Country__c',
        'ExternalPartyID'=>'SpiOwnerId__c',
        'Property_Owner'=>'Property_Owner__c',
        'GP_Preferred'=>'Legacy_Preferred_Program_Member__c',
        'GPX_Member_VEST__c'=>'GPX_Member_VEST__c',
    ];

    foreach($selects as $sk=>$sel)
    {
        $sels[$sel] = $sel;
    }

    foreach($users as $user)
    {
        $wpdb->update('wp_GPR_Owner_ID__c', array('meta_rework'=>1), array('id'=>$user->id));
        $gmvc = get_user_meta($user->user_id, 'GPX_Member_VEST__c', true);
        if($gmvc != $user->user_id)
        {
            $wpdb->update('wp_GPR_Owner_ID__c', array('meta_rework'=>2), array('id'=>$user->id));
            $userIDs[] = $user->Name;
            $oldUserIDs[$user->Name] = $user->user_id;
        }
    }

    if(!empty($userIDs))
    {
        $query = "SELECT ".implode(",", $sels)."  FROM GPR_Owner_ID__c where
                   Name IN ('".implode("','", $userIDs)."'";
        $results = $sf->query($query);

        foreach($results as $result)
        {
            $wpdb->update('wp_GPR_Owner_ID__c', array('meta_rework'=>3), array('id'=>$user->id));
            $value = $results[0]->fields;

            $wpdb->update('wp_GPR_Owner_ID__c', array('user_id'=>$value->GPX_Member_VEST__c), array('user_id'=>$oldUserIDs[$value->Name]));
            $wpdb->update('wp_mapuser2oid', array('gpx_user_id'=>$value->GPX_Member_VEST__c), array('gpx_user_id'=>$oldUserIDs[$value->Name]));
            $wpdb->update('wp_owner_interval', array('userID'=>$value->GPX_Member_VEST__c), array('userID'=>$oldUserIDs[$value->Name]));
        }
    }

    $sql = "SELECT * FROM wp_GPR_Owner_ID__c WHERE meta_rework=0";
    $tcnt = $wpdb->get_var($sql);

    $of = $offset+$limit;
    if($of < $tcnt)
    {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('remaining'=>$tcnt));
}
add_action('wp_ajax_rework_interval', 'rework_interval');


/**
 *
 *
 *
 *
 */
function gpx_import_rooms()
{
    global $wpdb;

    $sql = "SELECT * FROM import_rooms WHERE imported=0 LIMIT 200";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {

        $wpdb->update('import_rooms', array('imported'=>1), array('id'=>$row->id));

        $resortName = $row->ResortName;
        $resortName = str_replace("- VI", "", $resortName);
        $resortName = trim($resortName);
        $sql = $wpdb->prepare("SELECT id, resortID FROM wp_resorts WHERE ResortName=%s", $resortName);
        $resort = $wpdb->get_row($sql);

        if(empty($resort))
        {
            $exception = json_encode($row);
            $wpdb->insert("reimport_exceptions", array('type'=>'trade partner inventory resort', 'data'=>$exception));
            continue;
        }
        else
        {
            $resortID = $resort->id;
            $daeResortID = $resort->resortID;
        }

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

        $active = '1';
        if($row->active == 'FALSE')
        {
            $active = '0';
        }
        $type ='3';
        if(trim($row->Type) == 'Exchange')
        {
            $type = '1';
        }
        $spi = '0';
        if(!empty($row->source_partner_id))
        {
            $spi = $row->source_partner_id;
        }
        $wpdb->delete('wp_room', array('record_id'=>$row->record_id));
        $record_id = trim($row->record_id);
        $wp_room = [
            'record_id'=>$record_id,
            'active_specific_date' => date("Y-m-d 00:00:00"),
            'check_in_date' => date('Y-m-d 00:00:00', strtotime($row->StartDate)),
            'check_out_date' => date('Y-m-d 00:00:00', strtotime($row->StartDate.' +7 days')),
            'resort' => $resortID,
            'unit_type' => $unitID,
            'source_num' => '2',
            'source_partner_id' => $spi,
            'sourced_by_partner_on' => '',
            'resort_confirmation_number' => '',
            'active' => $active,
            'availability' => '1',
            'available_to_partner_id' => '0',
            'type' => $type,
            'active_rental_push_date' => date('Y-m-d', strtotime($row->active_rental_push_date)),
            'price' => $row->Price,
            'points' => NULL,
            'note' => 'From: '.$row->note,
            'given_to_partner_id' => NULL,
            'import_id' => '0',
            'active_type' => '0',
            'active_week_month' => '0',
            'create_by' => '5',
            'archived' => '0',
        ];

        $sql = $wpdb->prepare("SELECT record_id FROM wp_room WHERE record_id=%s", $record_id);
        $week = $wpdb->get_row($sql);
        if(!empty($week))
        {
            $wpdb->update('wp_room', $wp_room, array('record_id'=>$record_id));
        }
        else
        {
            $wpdb->insert('wp_room', $wp_room);
        }
    }

    $sql = "SELECT COUNT(id) as cnt FROM import_rooms WHERE imported=0";
    $remain = $wpdb->get_var($sql);

    if($remain > 0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('remaining'=>$remain));
}
add_action('wp_ajax_gpx_import_rooms', 'gpx_import_rooms');




/**
 *
 *
 *
 *
 */
function gpx_tp_inventory() {
    global $wpdb;
    $data = array();

    /** @var ?array $search */
    $search = isset($_REQUEST['filter']) ? json_decode(stripslashes($_REQUEST['filter']), true) : null;

    $query = Db::table('wp_room', 'a')
        ->where(fn($query) => $query
            ->where('check_in_date', '!=', '0000-00-00 00:00:00')
            ->orWhere('check_out_date', '!=', '0000-00-00 00:00:00')
        )
        ->where('resort', '!=', '0')
        ->whereNotNull('resort')
        ->whereNotNull('unit_type')
        ->where('archived', '=', 0)
        ->when($search, fn($query) => $query->where(function($query) use ($search) {
            foreach($search as $sk=>$sv)
            {
                $query->when($sk == 'record_id', fn($query) => $query->orWhereRaw('CAST(record_id as CHAR) LIKE ?', gpx_esc_like($sv).'%'));
                $query->when($sk == 'check_in_date', fn($query) => $query->orWhereBetween('check_in_date', [date('Y-m-d 00:00:00', strtotime($sv)), date('Y-m-d 23:59:59', strtotime($sv))]));
                $query->when(!in_array($sk,['record_id','check_in_date']), fn($query) => $query->orWhere($sk, 'LIKE', '%'.gpx_esc_like($sv).'%'));
            }
        }));

    $data['total'] = $query->count('record_id');

    $results = $query
        ->selectRaw( 'a.*, b.ResortName' )
        ->join( 'wp_resorts as b', 'b.id', '=', 'a.resort' )
        ->when( isset( $_REQUEST['offset'] ), fn( $query ) => $query->skip( $_REQUEST['offset'] ) )
        ->when( isset( $_REQUEST['limit'] ), fn( $query ) => $query->take( $_REQUEST['limit'] ) )
        ->when( isset( $_REQUEST['sort'] ), fn( $query ) => $query->orderBy( $_REQUEST['sort'], gpx_esc_orderby( $_REQUEST['order'] ) ) )
        ->get()->toArray();

    $i = 0;

    foreach($results as $result)
    {
        if($result->active == 0)
        {
            //was this held by this owner
            $sql = $wpdb->prepare("SELECT id FROM wp_gpxPreHold WHERE propertyID=%s AND user=%s AND released=0", [$result->record_id,$_REQUEST['user']]);
            $held = $wpdb->get_row($sql);
            if(!empty($held)) $data[$i]['active'] = 'Held';
        }
        $data['rows'][$i]['record_id'] = $result->record_id;
        $data['rows'][$i]['create_date'] = $result->create_date;
        $data['rows'][$i]['last_modified_date'] = $result->last_modified_date;
        $data['rows'][$i]['create_date'] = '<span data-date="'.date('Y-m-d', strtotime($result->create_date)).'">'.date('m/d/Y', strtotime($result->create_date)).'</span>';
        $data['rows'][$i]['last_modified_date'] = '<span data-date="'.date('Y-m-d', strtotime($result->last_modified_date)).'">'.date('m/d/Y', strtotime($result->last_modified_date)).'</span>';
        $data['rows'][$i]['check_in_date'] = '<span data-date="'.date('Y-m-d', strtotime($result->check_in_date)).'">'.date('m/d/Y', strtotime($result->check_in_date)).'</span>';
        $data['rows'][$i]['check_out_date'] = '<span data-date="'.date('Y-m-d', strtotime($result->check_out_date)).'">'.date('m/d/Y', strtotime($result->check_out_date)).'</span>';
        $data['rows'][$i]['price'] = '';
        if($result->type != '1' && !empty($result->price))
        {
            $data['rows'][$i]['price'] = '$'.$result->price;
        }

        $unit_type = $wpdb->prepare("SELECT * FROM `wp_unit_type` WHERE `record_id` = %s", $result->unit_type);
        $unit = $wpdb->get_results($unit_type);
        $data['rows'][$i]['unit_type_id'] = $unit[0]->name;

        $spid = $wpdb->prepare("SELECT * FROM wp_users a INNER JOIN wp_usermeta b on a.ID=b.user_id WHERE b.meta_key='DAEMemberNo' AND ID = %s", $result->source_partner_id);
        $spid_result = $wpdb->get_results($spid);
        $data['rows'][$i]['source_partner_id'] = $spid_result[0]->display_name;

        //resort
        $data['rows'][$i]['ResortName'] = $result->ResortName;

        if(!isset($data[$i]['active']))
        {
            $active = "";
            if(isset($result->active)){

                if($result->active == 1){
                    $active = "Yes";
                }
                else{
                    $active = "No";
                }
            }
            $data['rows'][$i]['active'] = $active;
        }


        $availability = "";

        if(isset($result->availability)){

            if($result->availability == 0){
                $availability = "--";
            }
            elseif($result->availability == 1){
                $availability = "All";
            }
            elseif($result->availability == 2){
                $availability = "Owner Only";
            }
            else {
                $availability = "Partner Only";
            }

        }

        $data['rows'][$i]['availability'] = $availability;

        $avltop = $wpdb->prepare("SELECT * FROM `wp_partner` WHERE record_id = %s", $result->available_to_partner_id);
        $avltop_result = $wpdb->get_results($avltop);

        $data['rows'][$i]['available_to_partner_id'] = $avltop_result[0]->name;

        $type = "";
        if(isset($result->type)){
            if($result->type == 1){
                $type = "Exchange";
            }
            elseif($result->type == 2){
                $type = "Rental";
            }
            elseif($result->type == 3){
                $type = 'Exchange/Rental';
            }
            else{
                $type = "--";
            }

        }

        $data['rows'][$i]['type'] = $type;

        $i++;
    }

    wp_send_json($data);
}

add_action('wp_ajax_gpx_tp_inventory', 'gpx_tp_inventory');
add_action('wp_ajax_nopriv_gpx_tp_inventory', 'gpx_tp_inventory');



/**
 *
 *
 *
 *
 */
function gpx_tp_activity()
{
    global $wpdb;

    $data = [];
    $table = [];

    $id = $_GET['id'];
    //get the rooms added
    $sql = $wpdb->prepare("SELECT a.record_id, a.check_in_date, a.resort_confirmation_number, a.sourced_by_partner_on, b.ResortName, c.name AS unit_type  FROM wp_room a
              INNER JOIN wp_resorts b ON b.id=a.resort
              INNER JOIN wp_unit_type c ON c.record_id=a.unit_type
              WHERE source_partner_id=%s and archived=0 ORDER BY sourced_by_partner_on", $id);
    $results = $wpdb->get_results($sql);

    $i = 0;
    foreach($results as $rv)
    {
        $k = strtotime($rv->sourced_by_partner_on).$i;

        $checkin = '';
        if(!empty($rv->check_in_date))
        {
            $checkin = date('m/d/Y', strtotime($rv->check_in_date));
        }

        $table[$k]['edit'] = '<a data-back="#tp_id_'.$id.'" href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id='.$rv->record_id.'" target="_blank"><i class="fa fa-pencil"></i></a>';
        $table[$k]['ID'] = $rv->record_id;
        $table[$k]['activity'] = 'Deposit';
        $table[$k]['check_in_date'] = $checkin;
        $table[$k]['resort'] = $rv->ResortName;
        $table[$k]['unit_type'] = $rv->unit_type;
        $table[$k]['resort_confirmation_number'] = $rv->resort_confirmation_number;
        $table[$k]['debit'] = '';

        $i++;
    }
    //get the rooms booked
    $sql = $wpdb->prepare("SELECT t.id, t.transactionType, t.data, t.datetime, a.record_id, a.price, a.check_in_date, a.resort_confirmation_number, b.ResortName, c.name AS unit_type  FROM
              wp_gpxTransactions t
              LEFT OUTER JOIN wp_room a ON t.weekID=a.record_id
              LEFT OUTER JOIN wp_resorts b ON b.id=a.resort
              LEFT OUTER JOIN wp_unit_type c on c.record_id=a.unit_type
              WHERE t.userID=%s
              AND t.cancelled IS NULL
              ORDER BY t.datetime", $id);
    $results = $wpdb->get_results($sql);

    foreach($results as $rv)
    {
        $k = strtotime($rv->datetime).$i;

        $data = json_decode($rv->data);

        $debit = '';
        if(strtolower($data->WeekType) == 'rental')
        {
            $debit = "-$".$data->Paid;
        }

        $activity = ucwords($rv->transactionType);
        if($rv->transactionType == 'pay_debit')
        {
            $activity = 'Pay Debit';
            $debit = "$".$data->Paid;
        }

        $checkin = '';
        if(!empty($rv->check_in_date))
        {
            $checkin = date('m/d/Y', strtotime($rv->check_in_date));
        }
        $table[$k]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=transactions_view&id='.$rv->id.'" class="in-modal"><i class="fa fa-eye" aria-hidden="true"></i></a>';
        $table[$k]['ID'] = $rv->record_id;
        $table[$k]['activity'] = $activity;
        $table[$k]['check_in_date'] = $checkin;
        $table[$k]['resort'] = $rv->ResortName;
        $table[$k]['unit_type'] = $rv->unit_type;
        $table[$k]['resort_confirmation_number'] = $rv->resort_confirmation_number;
        $table[$k]['guest_name'] = $data->GuestName;
        $table[$k]['debit'] = $debit;

        $i++;
    }

    $sql = $wpdb->prepare("SELECT t.id, t.release_on, a.record_id, a.check_in_date, a.resort_confirmation_number, b.ResortName, c.name AS unit_type FROM wp_gpxPreHold t
              INNER JOIN wp_room a ON t.weekID=a.record_id
              INNER JOIN wp_resorts b ON b.id=a.resort
              INNER JOIN wp_unit_type c on c.record_id=a.unit_type
              WHERE t.user=%s AND t.released=0 ORDER BY t.release_on", $id);
    $results = $wpdb->get_results($sql);

    foreach($results as $rv)
    {
        $k = strtotime($rv->release_on).$i;

        $table[$k]['edit'] = '<a href="#" data-id="'.$rv->id.'" class="release-week" title="release"><i class="fa fa-calendar-times-o" aria-hidden="true"></i></a>';
        $table[$k]['ID'] = $rv->record_id;
        $table[$k]['activity'] = 'Held';
        $table[$k]['check_in_date'] = date('m/d/Y', strtotime($rv->check_in_date));
        $table[$k]['resort'] = $rv->ResortName;
        $table[$k]['unit_type'] = $rv->unit_type;
        $table[$k]['resort_confirmation_number'] = $rv->resort_confirmation_number;
        $table[$k]['guest_name'] = '';
        $table[$k]['debit'] = '';

        $i++;
    }
    ksort($table);

    $table = array_values($table);

    wp_send_json($table);
}
add_action('wp_ajax_gpx_tp_activity', 'gpx_tp_activity');



/**
 *
 *
 *
 *
 */
function gpx_Room()
{
    global $wpdb;

    $data = array();
    $search = isset($_REQUEST['filter']) ? json_decode(stripslashes($_REQUEST['filter']), true) : null;
    $query = DB::table('wp_room', 'r')
        ->join('wp_unit_type as u', 'u.record_id', '=', 'r.unit_type')
        ->join('wp_resorts as rs', 'rs.id', '=', 'r.resort')
        ->leftJoin('wp_partner as ps', 'r.source_partner_id', '=', 'ps.user_id')
        ->leftJoin('wp_partner as pg', 'r.given_to_partner_id', '=', 'ps.user_id')
        ->when(isset($_REQUEST['Archived']), fn($query) => $query->where('r.archived', '=', $_REQUEST['Archived']))
        ->when(!isset($_REQUEST['future_dates']) || $_REQUEST['future_dates'] != '0', fn($query) => $query->whereRaw('DATE(r.check_in_date) >= CURRENT_DATE()'))
        ->when( $search, function ( $query ) use ( $search ) {
            foreach ( $search as $sk => $sv ) {
                $query->when( $sk == 'record_id', fn( $query ) => $query->where( 'r.record_id',  'LIKE', '%'. gpx_esc_like( $sv ) . '%' ) );
                $query->when( $sk == 'check_in_date', fn( $query ) => $query->whereDate( 'check_in_date', '=', date( 'Y-m-d', strtotime( $sv ) ) ) );
                $query->when( $sk == 'active', fn( $query ) => $query->where( 'r.active', '=',  mb_strtolower($sv) == 'yes' ? 1 : 0));
                $query->when( ! in_array( $sk, [ 'record_id', 'check_in_date', 'active' ] ), fn( $query ) => $query->where( $sk, 'LIKE', '%' . gpx_esc_like( $sv ) . '%' ) );
            }
        } );

    $data['total'] = $query->count('r.record_id');

    $results = $query
        ->selectRaw('r.*, u.name as room_type, rs.ResortName, ps.name as source_name, pg.name as given_name')
        ->when( isset( $_REQUEST['offset'] ), fn( $query ) => $query->skip( $_REQUEST['offset'] ) )
        ->when( isset( $_REQUEST['limit'] ), fn( $query ) => $query->take( $_REQUEST['limit'] ) )
        ->when( (isset($_REQUEST['from_date']) && isset($_REQUEST['to_date']) ), fn( $query ) => $query->take( 20 ) )
        ->when( isset( $_REQUEST['sort'] ), fn( $query ) => $query->orderBy( $_REQUEST['sort'], gpx_esc_orderby( $_REQUEST['order'] ) ) )
        ->get()->toArray();

    $i = 0;
    foreach($results as $result)
    {
        //what is the status
        if($result->active == '1')
        {
            $result->status = 'Available';
        }
        else
        {
            $sql = $wpdb->prepare("select `gpx`.`wp_gpxTransactions`.`weekId`
						from `gpx`.`wp_gpxTransactions` where `gpx`.`wp_gpxTransactions`.`weekId` = %s AND `gpx`.`wp_gpxTransactions`.`cancelled` IS NULL", $result->record_id);
            $booked = $wpdb->get_var($sql);

            if(!empty($booked))
            {
                $result->status = 'Booked';
            }
            else
            {
                $sql = $wpdb->prepare("select `wp_gpxPreHold`.`weekId`
                        from `wp_gpxPreHold`
                        where (`wp_gpxPreHold`.`released` = 0) AND `wp_gpxPreHold`.`propertyID`=%s", $result->record_id);
                $held = $wpdb->get_var($sql);
                if(!empty($held))
                {
                    $result->status = 'Held';
                }

                else
                {
                    $result->status = 'Available';
                }
            }
        }

        $data['rows'][$i]['action'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id='.$result->record_id.'"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
        $data['rows'][$i]['action'] .= '&nbsp;&nbsp;<a href="#" class="deleteWeek" data-id='.$result->record_id.'"><i class="fa fa-trash" aria-hidden="true" style="color: #d9534f;"></i></a>';
        $data['rows'][$i]['record_id'] = $result->record_id;
        $data['rows'][$i]['create_date'] = $result->create_date;
        $data['rows'][$i]['last_modified_date'] = $result->last_modified_date;
        $data['rows'][$i]['check_in_date'] = date('m/d/Y', strtotime($result->check_in_date));
        $data['rows'][$i]['check_out_date'] = date('m/d/Y', strtotime($result->check_out_date));
        $data['rows'][$i]['price'] = '';
        $data['rows'][$i]['room_type'] = $result->room_type;
        $data['rows'][$i]['unit_type_id'] = $result->room_type;
        if($result->type != '1' && !empty($result->price))
        {
            $data['rows'][$i]['price'] = '$'.$result->price;
        }
        $data['rows'][$i]['source_partner_id'] = $result->source_name;
        $data['rows'][$i]['ResortName'] = $result->ResortName;

        $data['rows'][$i]['sourced_by_partner_on'] = $result->sourced_by_partner_on;
        $data['rows'][$i]['resort_confirmation_number'] = $result->resort_confirmation_number;
        $data['rows'][$i]['active'] = $result->active;

        $data['rows'][$i]['available_to_partner_id'] = $result->given_name;
        $data['rows'][$i]['room_status'] = $result->status;


        $active = "";
        if(isset($result->active)){

            if($result->active == 1){
                $active = "Yes";
            }
            else{
                $active = "No";
                if(isset($result->Held) && $result->Held > 0)
                {
                    $active = 'Held';

                    $data['rows'][$i]['action'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id='.$result->record_id.'"><i class="fa fa-pencil" aria-hidden="true"></i></a>';

                }
            }
        }

        $archive = "";
        if(isset($result->archived)){

            if($result->archived == 1){
                $archive = "Yes";
                $data['rows'][$i]['action'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id='.$result->record_id.'"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
            }
            else{
                $archive = "No";
            }
        }

        $data['rows'][$i]['active'] = $active;
        $data['rows'][$i]['archived'] = $archive;

        $type = "";
        if(isset($result->type)){
            if($result->type == 1){
                $type = "Exchange";
            }
            elseif($result->type == 2){
                $type = "Rental";
            }
            elseif($result->type == 3){
                $type = 'Exchange/Rental';
            }
            else{
                $type = "--";
            }

        }

        $data['rows'][$i]['type'] = $type;

        $i++;
    }

    wp_send_json($data);
}

add_action('wp_ajax_gpx_Room', 'gpx_Room');
add_action('wp_ajax_nopriv_gpx_Room', 'gpx_Room');

/**
 *
 *
 *
 *
 */
function gpx_remove_room()
{
    global $wpdb;

    $return['success'] = false;
    if(!empty($_REQUEST['id']))
    {
        $return['success'] = true;

        $sql = $wpdb->prepare("SELECT source_partner_id, update_details FROM wp_room WHERE record_id=%s", $_REQUEST['id']);
        $roomRow = $wpdb->get_row($sql);

        $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s", $_REQUEST['id']);
        $row = $wpdb->get_row($sql);

        //Need to add capability to delete/archive weeks. If a week has had a booking on it, it should only be able to be archived (to keep the history intact). Weeks without a booking can be truly deleted from the database.
        if(empty($row))
        {
            $wpdb->delete('wp_room', array('record_id'=>$_REQUEST['id']));
            $return['deleted'] = true;
        }
        else
        {
            $row = $roomRow;

            $updateDets = json_decode($row->update_details, ARRAY_A);

            $updateDets[strtotime('NOW')] = [
                'update_by' => get_current_user_id(),
                'details'=>base64_encode(json_encode(array('room_archived'=>date('m/d/Y H:i:s')))),
            ];

            $data = [
                'active'=>'0',
                'archived'=>'1',
                'update_details'=>json_encode($updateDets),
            ];

            $wpdb->update('wp_room', $data, array('record_id'=>$_REQUEST['id']));

            $return['success'] = true;
            $return['archived'] = true;
        }
        //if this was a trade partner then adjust their rooms given
        if($roomRow->source_partner_id != 0)
        {
            $sql = $wpdb->prepare("UPDATE wp_partner set no_of_rooms_given = no_of_rooms_given - 1, trade_balance = trade_balance - 1 WHERE user_id=%s", $roomRow->source_partner_id);
            $wpdb->query($sql);
        }
    }

    wp_send_json($return);
}
add_action('wp_ajax_gpx_remove_room', 'gpx_remove_room');


/**
 *
 *
 *
 *
 */
function gpx_Room_error_ajax() {
    global $wpdb;
    $sql = "SELECT *  FROM `wp_room` WHERE `check_in_date` = '0000-00-00 00:00:00' or `check_out_date` = '0000-00-00 00:00:00' or resort ='0' or resort ='null' or unit_type ='null'";
    $results = $wpdb->get_results($sql);
    wp_send_json($results);
}

add_action('wp_ajax_gpx_Room_error_ajax', 'gpx_Room_error_ajax');
add_action('wp_ajax_nopriv_gpx_Room_error_ajax', 'gpx_Room_error_ajax');




/**
 *
 *
 *
 *
 */
function gpx_Room_error_page() {
    global $wpdb;
    $sql = "SELECT *  FROM `wp_room` WHERE `check_in_date` = '0000-00-00 00:00:00' or `check_out_date` = '0000-00-00 00:00:00' or resort ='0' or resort ='null' or unit_type ='null'";
    $results = $wpdb->get_results($sql);
    $i = 0;
    $data = array();

    foreach($results as $result)
    {

        $data[$i]['record_id'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id='.$result->record_id.'"><i class="fa fa-pencil" aria-hidden="true"></i><i class="fa fa-warning" aria-hidden="true" style="font-size:18px;color:red"></i></a>';
        $data[$i]['ID'] = $result->record_id;
        $data[$i]['create_date'] = $result->create_date;
        $data[$i]['last_modified_date'] = $result->last_modified_date;
        $data[$i]['check_in_date'] = $result->check_in_date;
        $data[$i]['check_out_date'] = $result->check_out_date;

        $unit_type = $wpdb->prepare("SELECT * FROM `wp_unit_type` WHERE `record_id` = %s", $result->unit_type);
        $unit = $wpdb->get_results($unit_type);
        $data[$i]['unit_type_id'] = $unit[0]->name;

        $spid = $wpdb->prepare("SELECT * FROM wp_users a INNER JOIN wp_usermeta b on a.ID=b.user_id WHERE b.meta_key='DAEMemberNo' AND ID = %s", $result->source_partner_id);
        $spid_result = $wpdb->get_results($spid);

        $res = $wpdb->prepare("SELECT *  FROM `wp_resorts` WHERE `id` = %s", $result->resort);
        $res_result = $wpdb->get_results($res);

        $data[$i]['resort'] = $res_result[0]->ResortName;

        $data[$i]['source_partner_id'] = $spid_result[0]->display_name;


        $data[$i]['sourced_by_partner_on'] = $result->sourced_by_partner_on;
        $data[$i]['resort_confirmation_number'] = $result->resort_confirmation_number;
        $active = "";
        if(isset($result->active)){

            if($result->active = 1){
                $active = "Yes";
            }
            else{
                $active = "No";
            }
        }

        $availability = "";

        if(isset($result->availability)){

            if($result->availability = 0){
                $availability = "--";
            }
            elseif($result->availability = 1){
                $availability = "All";
            }
            elseif($result->availability = 2){
                $availability = "Owner Only";
            }
            else {
                $availability = "Partner Only";
            }

        }


        $data[$i]['active'] = $active;
        $data[$i]['availability'] = $availability;

        $avltop = $wpdb->prepare("SELECT * FROM `wp_partner` WHERE record_id = %s", $result->available_to_partner_id);
        $avltop_result = $wpdb->get_results($avltop);

        $data[$i]['available_to_partner_id'] = $avltop_result[0]->name;

        $type = "";
        if(isset($result->type)){

            if($result->type == 1){
                $type = "Exchange";
            }
            elseif($result->type == 2){
                $type = "Rental";
            }
            elseif($result->type == 3){
                $type = "Exchange/Rental";
            }

        }

        $data[$i]['type'] = $type;

        $i++;
    }

    wp_send_json($data);
}

add_action('wp_ajax_gpx_Room_error_page', 'gpx_Room_error_page');
add_action('wp_ajax_nopriv_gpx_Room_error_page', 'gpx_Room_error_page');

/**
 *
 *
 *
 *
 */
function gpx_release_week()
{
    global $wpdb;

    $activeUser = get_userdata(get_current_user_id());

    $sql = $wpdb->prepare("SELECT propertyID, data FROM wp_gpxPreHold WHERE id=%s", $_POST['id']);
    $row = $wpdb->get_row($sql);

    $holdDets = json_decode($row->data, true);
    $holdDets[strtotime('now')] = [
        'action'=>'released',
        'by'=>$activeUser->first_name." ".$activeUser->last_name,
    ];

    $wpdb->update('wp_gpxPreHold', array('released'=>'1', 'data'=>json_encode($holdDets)), array('id'=>$_POST['id']));


    $sql = $wpdb->prepare("SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId=%s AND cancelled IS NULL", $row->propertyID);
    $trow = $wpdb->get_var($sql);


    // TODO more if do nothing - fix
    if($trow > 0)
    {
        //nothing to do
    }
    else
    {

        //we always need to check the "display date" prior to making it active. Only make this active when the sell date is in the future.
        $sql = $wpdb->prepare("SELECT active_specific_date FROM wp_room WHERE record_id=%d",$row->propertyID);
        $activeDate = $wpdb->get_var($sql);

        if(strtotime('NOW') >  strtotime($activeDate))
        {
            $wpdb->update('wp_room', array('active'=>1), array('record_id'=>$row->propertyID));
        }
    }



    $data['success'] = true;

    wp_send_json($data);
}
add_action('wp_ajax_gpx_release_week', 'gpx_release_week');



/**
 *
 *
 *
 *
 */
function gpx_extend_week()
{
    global $wpdb;

    $sql = $wpdb->prepare("SELECT user, weekId FROM wp_gpxPreHold WHERE id=%s", $_REQUEST['id']);
    $row = $wpdb->get_row($sql);

    $cid = $row->user;

    $sql = $wpdb->prepare("SELECT id FROM wp_gpxPreHold WHERE user != %s AND weekId=%s AND released=0", [$row->user,$row->weekId]);
    $dup = $wpdb->get_row($sql);

    if(!empty($dup))
    {
        //this is a duplicate return an error
        $data['error'] = 'Another owner has this week on hold.';
        wp_send_json($data);
    }

    $newdate = date('Y-m-d 23:59:59', strtotime('+1 DAY'));

    if(isset($_REQUEST['newdate']) && !empty($_REQUEST['newdate']))
    {
        $newdate = date('Y-m-d 23:59:59', strtotime($_REQUEST['newdate']));
    }

    $wpdb->update('wp_gpxPreHold', array('release_on'=>$newdate, 'released'=>'0'), array('id'=>$_POST['id']));

    $sql = $wpdb->prepare("SELECT propertyID FROM wp_gpxPreHold WHERE id=%s", $_POST['id']);
    $row = $wpdb->get_row($sql);

    $wpdb->update('wp_room', array('active'=>'0'), array('record_id'=>$row->propertyID));
    $data['success'] = true;
    $data['cid'] = $cid;

    wp_send_json($data);
}
add_action('wp_ajax_gpx_extend_week', 'gpx_extend_week');




/**
 *
 *
 *
 *
 */
function resort_availability_calendar()
{
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $resort = '';
    $beds = '';
    $weektype = '';
    if(isset($_GET['resort']))
        $resort = $_GET['resort'];
    if(isset($_GET['beds']))
        $beds = $_GET['beds'];
    if(isset($_GET['weektype']))
        $weektype = $_GET['weektype'];
    $events = $gpx->resort_availability_calendar($resort, $beds, $weektype);

    wp_send_json($events);
}
add_action("wp_ajax_resort_availability_calendar","resort_availability_calendar");
add_action("wp_ajax_nopriv_resort_availability_calendar", "resort_availability_calendar");




/**
 *
 *
 *
 *
 */
function gpx_bonus_week_details()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $return = $gpx->get_bonus_week_details();

    wp_send_json($return);
}
add_action("wp_ajax_gpx_bonus_week_details","gpx_bonus_week_details");
add_action("wp_ajax_nopriv_gpx_bonus_week_details", "gpx_bonus_week_details");

