<?php
use GPX\Repository\OwnerRepository;


/**
 *
 *
 *
 *
 */
function gpx_get_owner_credits()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_get_owner_credits();

    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_get_owner_credits', 'gpx_get_owner_credits');

function gpx_temp_import_owners()
{
    global $wpdb;

    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $sql = "SELECT * from temp_import_owner where imported=0 limit 500";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {
        $imported = $gpx->DAEGetMemberDetails($row->accountid, '', '', 'Welcome');
        if(!empty($imported))
        {
            $wpdb->update('temp_import_owner', array('imported'=>'1'), array('id'=>$row->id));
        }
    }


    wp_send_json($imported);
    wp_die();
}
add_action('wp_ajax_temp_import_owners', 'gpx_temp_import_owners');



/**
 *
 *
 *
 *
 */
function function_Ownership_mapping() {
    global $wpdb;
    $check_wp_mapuser2oid = $wpdb->get_results("SELECT usr.ID as gpx_user_id, usr.user_nicename as gpx_username, Name as gpr_oid, oint.ownerID as gpr_oid_interval, resortID, user_status, Delinquent__c, unitweek  FROM wp_GPR_Owner_ID__c oid INNER JOIN wp_owner_interval oint ON oid.Name = oint.ownerID INNER JOIN wp_users usr ON usr.user_email = oid.SPI_Email__c");

    if(isset($check_wp_mapuser2oid)){

        if (count($check_wp_mapuser2oid) != 0){

            foreach ($check_wp_mapuser2oid as $value) {

                $sql = $wpdb->prepare("SELECT *  FROM `wp_mapuser2oid` WHERE `gpx_user_id` = %s AND `gpx_username` LIKE %s AND `gpr_oid` = %s AND `gpr_oid_interval` = %s", [$value->gpx_user_id, $wpdb->esc_like($value->gpx_username), $value->gpr_oid, $value->gpr_oid_interval]);
                $check_available = $wpdb->get_results($sql);

                if (count($check_available) == 0){
                    $wpdb->insert('wp_mapuser2oid', [
                        'gpx_user_id' => $value->gpx_user_id,
                        'gpx_username' => $value->gpx_username,
                        'gpr_oid' => $value->gpr_oid,
                        'gpr_oid_interval' => $value->gpr_oid_interval,
                        'resortID' => $value->resortID,
                        'user_status' => $value->user_status,
                        'Delinquent__c' => $value->Delinquent__c,
                        'unitweek' => $value->unitweek,
                    ]);
                }
            }
        }
    }
}

add_action('hook_cron_function_Ownership_mapping', 'function_Ownership_mapping');



/**
 *
 *
 *
 *
 */
function gpx_owner_reassign()
{
    global $wpdb;

    if(isset($_REQUEST['vestID']))
    {
        $wpdb->update('wp_credit', array('owner_id'=>$_REQUEST['vestID']), array('owner_id'=>$_REQUEST['legacyID']));

        $sql = $wpdb->prepare("SELECT id, data FROM wp_gpxTransactions WHERE userID=%s", $_REQUEST['legacyID']);
        $rows = $wpdb->get_results($sql);

        foreach($rows as $row)
        {
            $id = $row->id;
            $tData = json_decode($row->data, true);

            $tData['MemberNumber'] = $_REQUEST['vestID'];
            $wpdb->update('wp_gpxTransactions', array('userID'=>$_REQUEST['vestID'], 'data'=>json_encode($tData)), array('id'=>$id));
        }
    }

}
add_action('wp_ajax_gpx_owner_reassign', 'gpx_owner_reassign');



/**
 *
 *
 *
 *
 */
function rework_ids_r()
{
    global $wpdb;

    $sf = Salesforce::getInstance();

    $limit = 500;

    $sql = $wpdb->prepare("SELECT user_id FROM `wp_GPR_Owner_ID__c` WHERE meta_rework=0 LIMIT %d",$limit);
    $users = $wpdb->get_results($sql);
    foreach($users as $olduser)
    {

        $wpdb->update('wp_GPR_Owner_ID__c', array('meta_rework'=>1), array('user_id'=>$olduser->user_id));

        $daeMemberNo = get_user_meta($olduser->user_id, 'DAEMemberNo', true);

        if($olduser->user_id == $daeMemberNo)
        {
            continue;
        }
        update_user_meta($olduser->user_id, 'DAEMemberNo', $olduser->user_id);

        //get the real id
        $query = "SELECT GPX_Member_VEST__c  FROM GPR_Owner_ID__c where
                   Name='".$olduser->Name."'";

        $results = $sf->query($query);

        $nu = $results[0]->fields->GPX_Member_VEST__c;

        $user = reset(
            get_users(
                array(
                    'meta_key' => 'GPX_Member_VEST__c',
                    'meta_value' => $nu,
                    'number' => 1,
                    'count_total' => false
                )
            )
        );

        $ou = $user->ID;

        if($nu != $olduser->user_id)
        {
            $wpdb->update('wp_GPR_Owner_ID__c', array('user_id'=>$nu), array('id'=>$olduser->id));
            $wpdb->update('wp_mapuser2oid', array('gpx_user_id'=>$nu), array('gpr_oid'=>$olduser->Name));
            $wpdb->update('wp_owner_interval', array('userID'=>$nu), array('ownerID'=>$olduser->Name));
        }

    }

    $sql = "SELECT count(user_id) FROM `wp_GPR_Owner_ID__c` WHERE meta_rework=0";

    $tcnt = $wpdb->get_var($sql);

    if($tcnt>0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('remaining'=>$tcnt));
    wp_die();

}
add_action('wp_ajax_rework_ids_r', 'rework_ids_r');



/**
 *
 *
 *
 *
 */function rework_zero_ids()
{
    global $wpdb;

    $sql = "SELECT a.id, b.user_id  FROM `wp_mapuser2oid` a
INNER JOIN wp_GPR_Owner_ID__c b ON a.gpr_oid=b.Name
WHERE `gpx_user_id` = 0";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {
        $wpdb->update('wp_mapuser2oid', array('gpx_user_id'=>$row->user_id), array('id'=>$row->id));
    }

    $sql = "SELECT a.id, b.user_id  FROM `wp_owner_interval` a
INNER JOIN wp_GPR_Owner_ID__c b ON a.ownerID=b.Name
WHERE `userID` = 0";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {
        $wpdb->update('wp_owner_interval', array('userID'=>$row->user_id), array('id'=>$row->id));
    }

}
add_action('wp_ajax_rework_zero_ids', 'rework_zero_ids');




/**
 *
 *
 *
 *
 */
function rework_username()
{
    global $wpdb;

    $sqlOP = "SELECT ID FROM wp_users WHERE user_login LIKE '%NOT_A_VALID_EMAIL%' LIMIT 100";
    $rows = $wpdb->get_results($sqlOP);

    foreach($rows as $row)
    {
        $wpdb->update('wp_users', array('user_login'=>$row->ID), array('ID'=>$row->ID));
    }


    $sql = "SELECT COUNT(ID) AS cnt FROM wp_users WHERE user_login LIKE '%NOT_A_VALID_EMAIL%'";
    $remain = $wpdb->get_var($sql);
    if($remain > 0)
    {
        sleep(1);
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('remaining'=>$remain));
    wp_die();
}
add_action('wp_ajax_rework_username', 'rework_username');



/**
 *
 *
 *
 *
 */
function rework_ids()
{
    global $wpdb;

    $sql = "SELECT ID, user_login FROM `wp_users` WHERE `user_login` LIKE 'U%' ORDER BY ID DESC";
    $users = $wpdb->get_results($sql);

    foreach($users as $user)
    {
        $userID = $user->ID;
        $ul = str_replace("U", "", $user->user_login);
        $ul = str_replace(" ", "", $ul);
    }

    $of = $offset+$limit;

    wp_send_json(array('remaining'=>$tcnt));
    wp_die();
}
add_action('wp_ajax_rework_ids', 'rework_ids');


/**
 *
 *
 *
 *
 */
add_action('hook_cron_GPX_Owner', 'function_GPX_Owner');
function function_GPX_Owner($isException='', $byOwnerID='') {

    global $wpdb;

    $sf = Salesforce::getInstance();

    $queryDays = '1';
    if(isset($_REQUEST['days']))
    {
        $queryDays = $_REQUEST['days'];
    }

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

    $minDate = '2016-11-10';

    /*
     * @TODO: check exclude developer/hoa from query
     */

    $query = "SELECT ".implode(",", $sels)."  FROM GPR_Owner_ID__c where
                    SystemModStamp >= LAST_N_DAYS:".$queryDays."
                            AND HOA_Developer__c = false
                            AND Id NOT IN (SELECT GPR_Owner_ID__c FROM Ownership_Interval__c WHERE Resort_ID_v2__c='GPVC')
                ORDER BY CreatedDate desc";

    if(isset($_GET['vestID']))
    {
        $isException = $_GET['vestID'];
    }
    if(!empty($isException))
    {
        if(!empty($byOwnerID))
        {
            $exWhere = 'Name';
        }
        else
        {
            $exWhere = 'GPX_Member_VEST__c';
        }
        $query = "SELECT ".implode(",", $sels)."  FROM GPR_Owner_ID__c where ";
        $query.= $exWhere."='".$isException."'";
    }

    if(isset($_REQUEST['limit']))
    {
        $query .= ' LIMIT '.$_REQUEST['limit'];
        if(isset($_REQUEST['offset']))
        {
            $query .= ' OFFSET '.$_REQUEST['offset'];
        }
    }

    $results = $sf->query($query);

    $selects['Email'] = 'SPI_Email__c';
    $selects['Email1'] = 'SPI_Email__c';

    $testaccs = [
        '112220440',
        '112220435',
        '112220432',
        '112220427',
        '112220439',
    ];
    // not found in SF
    if(empty($results))
    {
        return '';
    }

    foreach ($results as $result)
    {
        $value = $result->fields;
        $wpdb->update('import_owner_no_vest', array('imported'=>'5'), array('id'=>$iowners[$value->Name]));
        $ocd = explode("T", $value->CreatedDate);

        $fq = false;
        $cd = $value->CreatedDate;
        $lo++;


        $selects2 = [
            'Owner_ID__c',
            'GPR_Resort__c',
            'Contract_ID__c',
            'UnitWeek__c',
            'Contract_Status__c',
            'Delinquent__c',
            'Days_Past_Due__c',
            'Total_Amount_Past_Due__c',
            'Room_Type__c',
            'ROID_Key_Full__c',
        ];

        //update the ownership intervals
        $query2 = "SELECT ".implode(", ", $selects2)."
                    FROM Ownership_Interval__c
                       WHERE Owner_ID__c='".$value->Name."'";

        $results2 =  $sf->query($query2);
        $isGPVC = $results2->fields->Resort_ID_v2__c;

        // no ownerships in SF
        if(empty($results2) || $isGPVC === 'GPVC')
        {
            continue;
        }

        $user = '';
        if(!empty($_GET['vestID']) && !empty($_GET['split']))
        {
            //change the vestID for the owner with the email that matches 'split'
            $updateUser = get_user_by('email', $_GET['split']);
            if(empty($updateUser))
            {
                $sql = $wpdb->prepare("SELECT user_id FROM wp_GPR_Owner_ID__c WHERE SPI_Email__c=%s", $_GET['split']);
                $newUserID = $wpdb->get_var($sql);
            }
            else
            {
                $newUserID = $updateUser->ID;
            }

            if(!empty($newUserID))
            {
                update_user_meta($newUserID, 'GPX_Member_VEST__c', $_GET['vestID']);
            }
            else
            {
                // user with that email could not be found
                exit;
            }
        }

        $oldVestID = '';

        if(isset($value->GPX_Member_VEST__c) && !empty($value->GPX_Member_VEST__c))
        {
            $oldVestID = $value->GPX_Member_VEST__c;
            $user = reset(
                get_users(
                    array(
                        'meta_key' => 'GPX_Member_VEST__c',
                        'meta_value' => $value->GPX_Member_VEST__c,
                    )
                )
            );
        }

        if(empty($user) && (isset($value->GPX_Member_VEST__c) && !empty($value->GPX_Member_VEST__c)))
        {
            $user = reset(
                get_users(
                    array(
                        'meta_key' => 'DAEMemberNo',
                        'meta_value' => $value->GPX_Member_VEST__c,
                    )
                )
            );
        }
        if(isset($_GET['vestID']) && empty($user) && !empty($value->SPI_Email__c))
        {
            // owner not found in vest
        }
        if(isset($_GET['test']))
        {
            exit;
        }
        if(!empty($user))
        {
            $value->GPX_Member_No__c = $user->ID;
            $user_id = $user->ID;

        }
        else
        {

            if(empty($value->SPI_Email__c))
            {
                $value->SPI_Email__c = 'gpr'.$value->Name.'@NOT_A_VALID_EMAIL.com';
            }
            elseif(email_exists($value->SPI_Email__c))
            {
                $splitEmail = explode("@", $value->SPI_Email__c);
                $splitEmail[0] += '+'.$value->Name;
                $value->SPI_Email__c = implode("@", $splitEmail);
                //is this $byOwnerID  if so then we want to force it to create this account
                if($removeUser = email_exists($value->SPI_Email__c))
                {
                    wp_delete_user($removeUser);
                }
            }
            $isInWP = '';

            //does this id exist?  if not, then we can add this user with this account
            if(!empty($value->GPX_Member_VEST__c))
            {
                $sql = $wpdb->prepare("SELECT ID FROM wp_users WHERE ID=%s", $value->GPX_Member_VEST__c);
                $isInWP = $wpdb->get_var($sql);
            }

            if(empty($isInWP))
            {
                $user_login = wp_slash( $value->SPI_Email__c );
                $user_email = wp_slash( $value->SPI_Email__c );
                $user_pass = wp_generate_password();

                $userdata = [
                    'user_login'=>$user_login,
                    'user_email'=>$user_email,
                    'user_pass'=>$user_pass,
                ];

                $user_id = wp_insert_user($userdata);;

            }
            else
            {
                // TODO anoher ifdonothing FIX
                if($user_id = email_exists($value->SPI_Email__c))
                {
                    //nothing needs to happen
                }
                else
                {
                    $user_id = wp_create_user( $value->SPI_Email__c, wp_generate_password(), $value->SPI_Email__c );
                }
            }



            if(empty($user_id) ||  is_wp_error($user_id) )
            {

                $errorDets = [
                    'owner_id'=>$value->Owner_ID__c,
                    'updated_at'=>date('Y-m-d H:i:s'),
                    'data'=>json_encode([
                        'error_message' => $user_id->errors[$error_code][0],
                        'sfDetails'=>json_encode($value),
                    ]),
                ];
                $wpdb->insert('wp_owner_spi_error', $errorDets);
                if(isset($_REQUEST['user_id_debug']))
                {
                    exit;
                }

                continue;
            }

        }
        $userdets = [
            'ID'=>$user_id,
            'first_name'=>$value->SPI_First_Name__c,
            'last_name'=>$value->SPI_Last_Name__c,
        ];
        $up = wp_update_user($userdets);
        update_user_meta($user_id, 'first_name', $value->SPI_First_Name__c);
        update_user_meta($user_id, 'last_name', $value->SPI_Last_Name__c);
        update_user_meta($user_id, 'DAEMemberNo', $user_id);

        $userrole = new WP_User( $user_id );

        $userrole->set_role('gpx_member');

        foreach($selects as $sk=>$sv)
        {
            if($sk == 'DAEMemberNo' || $sv == 'DAEMemberNo')
            {
                continue;
            }
            if($sk == 'GP_Preferred')
            {
                if($value->$sv == 'true')
                {
                    $value->$sv = "Yes";
                }
                if($value->$sv == 'false')
                {
                    $value->$sv = 'No';
                }
            }
            update_user_meta($user_id, $sk, $value->$sv);
            update_user_meta($user_id, $sv, $value->$sv);

        }

        $sql = $wpdb->prepare("SELECT * FROM wp_GPR_Owner_ID__c WHERE Name LIKE %s", $wpdb->esc_like($value->Name));
        $check_if_exist = $wpdb->get_results($sql);

        if(count($check_if_exist) <= 0){
            $fullname = $value->SPI_First_Name__c." ".$value->SPI_Last_Name__c;
            $wpdb->insert('wp_GPR_Owner_ID__c', array('Name'=>$value->Name, 'user_id'=>$user_id, 'SPI_Owner_Name_1st__c'=>$fullname, 'SPI_Email__c'=> $value->SPI_Email__c, 'SPI_Home_Phone__c'=> $value->SPI_Home_Phone__c, 'SPI_Work_Phone__c'=> $value->SPI_Work_Phone__c, 'SPI_Street__c'=> $value->SPI_Street__c, 'SPI_City__c'=> $value->SPI_City__c, 'SPI_State__c'=> $value->SPI_State__c, 'SPI_Zip_Code__c'=> $value->SPI_Zip_Code__c, 'SPI_Country__c'=> $value->SPI_Country__c));

            //does this user have an id?

        }
        else
        {
            $fullname = $value->SPI_First_Name__c." ".$value->SPI_Last_Name__c;

            $wpdb->update('wp_GPR_Owner_ID__c', array('user_id'=>$user_id),  array("Name" => $check_if_exist[0]->Name));

        }

        if( !empty($value->Name) && $user_id != $oldVestID)
        {
            $sfOwnerData['GPX_Member_VEST__c'] = $user_id;
            $sfOwnerData['Name'] = $value->Name;


            $sfType = 'GPR_Owner_ID__c';
            $sfObject = 'Name';
            $sfFields = [];
            $sfFields[0] = new SObject();
            $sfFields[0]->fields = $sfOwnerData;
            $sfFields[0]->type = $sfType;
            $sfAdd = $sf->gpxUpsert($sfObject, $sfFields);
            update_user_meta($user_id, 'GPX_Member_VEST__c', $user_id);
        }


        foreach($results2 as $restults2)
        {
            $r2 = $restults2->fields;

            $interval = [
                'userID'=>$user_id,
                'ownerID'=>$r2->Owner_ID__c,
                'resortID'=>substr($r2->GPR_Resort__c, 0, 15),
                'contractID'=>$r2->Contract_ID__c,
                'unitweek'=>$r2->UnitWeek__c,
                'Contract_Status__c'=>$r2->Contract_Status__c,
                'Delinquent__c'=>$r2->Delinquent__c,
                'Days_past_due__c'=>$r2->Days_Past_Due__c,
                'Total_Amount_Past_Due__c'=>$r2->Total_Amount_Past_Due__c,
                'Room_type__c'=>$r2->Room_Type__c,
                'Year_Last_Banked__c'=>$r2->Year_Last_Banked__c,
                'RIOD_Key_Full'=>$r2->ROID_Key_Full__c,
            ];

            $sql = $wpdb->prepare("SELECT id FROM wp_owner_interval WHERE RIOD_Key_Full=%s", $r2->ROID_Key_Full__c);
            $row = $wpdb->get_row($sql);

            if(empty($row))
            {
                $wpdb->insert('wp_owner_interval',$interval);

            }
            else
            {
                $wpdb->update('wp_owner_interval', $interval, array('RIOD_Key_Full'=>$r2->ROID_Key_Full__c));
            }
            //is this resort added?
            $sql = $wpdb->prepare("SELECT id FROM wp_resorts WHERE gprID=%s", $r2->GPR_Resort__c);
            $row = $wpdb->get_row($sql);

            if(empty($row))
            {
                //can we update this resort?
                $selects = [
                    'Name',
                ];

                $resortQ = "SELECT ".implode(", ", $selects)."
                    FROM Resort__c
                       WHERE ID='".$interval['resortID']."'";
                $resortResults = $sf->query($resortQ);

                foreach($resortResults as $rr)
                {
                    $resort = $rr->fields;
                    $resortName = $resort->Name;

                    $rsql = $wpdb->prepare("SELECT id FROM wp_resorts WHERE ResortName LIKE %s", $wpdb->esc_like($resortName));
                    $rRow = $wpdb->get_var($rsql);

                    //add the GPR Number
                    if(!empty($rRow))
                    {
                        $wpdb->update('wp_resort', array('gprID'=>$interval['resortID']), array('id'=>$rRow));
                    }
                    else
                    {
                        $resortNotAvailable[] = $interval['resortID'];
                    }
                }

            }

            $map = [
                'gpx_user_id'=>$user_id,
                'gpx_username'=>$value->SPI_Email__c,
                'gpr_oid'=>$r2->Owner_ID__c,
                'gpr_oid_interval'=>$r2->Owner_ID__c,
                'resortID'=>substr($r2->GPR_Resort__c, 0, 15),
                'user_status'=>0,
                'Delinquent__c'=>$r2->Delinquent__c,
                'unitweek'=>$r2->UnitWeek__c,
                'RIOD_Key_Full'=>$r2->ROID_Key_Full__c,
            ];

            //are they mapped?
            $sql = $wpdb->prepare("SELECT id FROM wp_mapuser2oid WHERE RIOD_Key_Full=%s", $r2->ROID_Key_Full__c);
            $row = $wpdb->get_row($sql);
            if(empty($row))
            {
                $wpdb->insert('wp_mapuser2oid', $map);
            }
            else
            {
                $wpdb->update('wp_mapuser2oid', $map, array('id'=>$row->id));
            }
        }
        $imported['Import ID'][] = $user_id;
    }

    if(!empty($isException))
    {
        return $user_id;
    }

    wp_send_json($imported);
    wp_die();

}

add_action('hook_cron_GPX_Owner', 'function_GPX_Owner');
add_action('wp_ajax_cron_GPX_Owner', 'function_GPX_Owner');




/**
 *
 *
 *
 *
 */
function vest_import_owner()
{
    global $wpdb;

    $sql = "SELECT * FROM temp_users WHERE user_login NOT IN (select user_login FROM wp_users) LIMIT 100";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {

        $import = [
            'user_login'=>$row->user_login,
            'user_pass'=>wp_generate_password(),
            'user_email'=>$row->user_email,
            'user_nicename'=>$row->user_nicename,
            'user_url'=>$row->user_url,
            'user_registered'=>$row->user_registered,
            'user_activation_key'=>$row->user_activation_key,
            'user_status'=>$row->user_status,
            'display_name'=>$row->display_name,
        ];
        $wpdb->insert('wp_users', $import);
        if($wpdb->last_error)
        {
            exit;
        }
        $id = $wpdb->insert_id;

        $sql = $wpdb->prepare("SELECT * FROM temp_usermeta WHERE user_id=%s", $row->user_id);
        $ums = $wpdb->get_results($sql);

        foreach($ums as $um)
        {
            $importMeta = [
                'user_id'=>$id,
                'meta_key'=>$um->meta_key,
                'meta_value'=>$um->meta_value,
            ];
            $wpdb->insert('wp_usermeta', $importMeta);
            if($wpdb->last_error)
            {
                exit;
            }
        }

    }

    $sql = "SELECT COUNT(id) as cnt FROM temp_users WHERE user_login NOT IN (select user_login FROM wp_users)";
    $remain = $wpdb->get_var($sql);

    if($remain > 0)
    {
        echo $remain;
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('remaining'=>$remain));
    wp_die();

}
add_action('wp_ajax_vest_import_owner', 'vest_import_owner');



/**
 *
 *
 *
 *
 */
function owner_check()
{
    global $wpdb;

    $sf = Salesforce::getInstance();

    $selects = [
        'Owner_ID__c',
        'Contract_ID__c',
        'Status__c',
        'GPX_Deposit__c',
    ];

    $query =  "select ".implode(", ", $selects)." from Ownership_Interval__c";
    $results = $sf->query($query);

    foreach($results as $result)
    {
        $data = $result->field;

        $sql = "SELECT m.* FROM wp_owner_interval oi
                INNER JOIN wp_mapuser2oid m ON m.gpx_user_id=oi.userID
               WHERE contractID='".$data->Contract_ID__c."'";

    }

    wp_send_json($dataset);
    wp_die();
}
add_action('wp_ajax_owner_check', 'owner_check');




/**
 *
 *
 *
 *
 */
function gpx_add_owner()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    if(isset($_POST['DAEMemberNo']) && isset($_POST['RMN']) && isset($_POST['password']))
    {
        $user = $gpx->DAEGetMemberDetails($_POST['DAEMemberNo'], '', $_POST, $_POST['password']);
        $data = $user;
    }
    else
        $data = array('error'=>'Member number, Resort Member Number and password are required');

    wp_send_json($data);
    wp_die();
}
add_action("wp_ajax_gpx_add_owner","gpx_add_owner");
add_action("wp_ajax_nopriv_gpx_add_owner", "gpx_add_owner");



/**
 *
 *
 *
 *
 */
function gpx_mass_update_owners()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpxadmin = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $offset = '';
    if(isset($_GET['offset']))
        $offset = $_GET['offset'];

    $owners = $gpxadmin->return_mass_update_owners($_GET['orderby'], $_GET['order'], $offset);

    wp_send_json($data);
    wp_die();
}
add_action("wp_ajax_gpx_mass_update_owners","gpx_mass_update_owners");
add_action("wp_ajax_nopriv_gpx_mass_update_owners", "gpx_mass_update_owners");


/**
 *
 *
 *
 *
 */
function get_gpx_customers()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data['html'] = $gpx->return_gpx_owner_search();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_customers', 'get_gpx_customers');
add_action('wp_ajax_nopriv_get_gpx_customers', 'get_gpx_customers');


/**
 *
 *
 *
 *
 */
function get_gpx_findowner()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    if(strlen($_GET['search']) > 0)
    {
        $data['html'] = $gpx->return_get_gpx_findowner($_GET['search']);
    }
    else
    {
        $data = false;
    }

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_findowner', 'get_gpx_findowner');
add_action('wp_ajax_nopriv_get_gpx_findowner', 'get_gpx_findowner');



/**
 *
 *
 *
 *
 */
function gpx_get_owner_for_add_transaction()
{

    if(isset($_GET['memberNo']) && !empty($_GET['memberNo']))
    {
        $user = reset(
            get_users(
                array(
                    'meta_key' => 'DAEMemberNo',
                    'meta_value' => $_GET['memberNo'],
                    'number' => 1,
                    'count_total' => false
                )
            )
        );

        $data['FirstName1'] = $user->FirstName1;
        $data['LastName1'] = $user->LastName1;
        $data['Email'] = $user->Email;
        $data['HomePhone'] = $user->HomePhone;
        $data['Mobile'] = $user->Mobile;
        $data['Address1'] = $user->Address1;
        $data['Address3'] = $user->Address3;
        $data['Address4'] = $user->Address4;
        $data['PostCode'] = $user->PostCode;
        $data['Address5'] = $user->Address5;

    }

    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_get_owner_for_add_transaction', 'gpx_get_owner_for_add_transaction');
add_action('wp_ajax_nopriv_gpx_get_owner_for_add_transaction', 'gpx_get_owner_for_add_transaction');

/**
 *
 *
 *
 *
 */
function gpx_load_ownership($id)
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $cid = get_current_user_id();

    if(isset($_COOKIE['switchuser']))
        $cid = $_COOKIE['switchuser'];

    $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );

    $daeMemberNo = $usermeta->DAEMemberNo;
    if(isset($_REQUEST['member_no']))
    {
        $daeMemberNo = $_REQUEST['member_no'];
    }
    $ownership = $gpx->load_ownership($daeMemberNo);

    $data['html'] = $ownership;

    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_load_ownership', 'gpx_load_ownership');



/**
 *
 *
 *
 *
 */
function gpx_import_owner_credit()
{
    global $wpdb;

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $sql = "SELECT * FROM wp_gpx_import_account_credit WHERE is_added=0 LIMIT 100";
    $results = $wpdb->get_results($sql);

    foreach($results as $row)
    {
        $name = 'ac'.$row->id.$row->account;

        $userid = gpx_user_id_by_daenumber($row->account);

        if(empty($userid))
        {
            $wpdb->update('wp_gpx_import_account_credit', array('is_added'=>2), array('id'=>$row->id));
            continue;
        }

        $occ = [
            'Name'=>$name,
            'Slug'=>$name,
            'Active'=>1,
            'singleuse'=>0,
            'amount'=>$row->amount,
            'owners'=>[$userid],
            'expirationDate'=>date('Y-m-d', strtotime($row->business_date)),
            'comments'=>'Imported Credit',
        ];

        $gpx->promodeccouponsadd($occ);

        $wpdb->update('wp_gpx_import_account_credit', array('is_added'=>1), array('id'=>$row->id));
    }

}
add_action('wp_ajax_gpx_import_owner_credit', 'gpx_import_owner_credit');
add_action('wp_ajax_nopriv_gpx_import_owner_credit', 'gpx_import_owner_credit');


/**
 *
 *
 *
 *
 */
function gpx_user_id_by_daenumber($daeNumber)
{
    global $wpdb;

    $sql = $wpdb->prepare("SELECT user_id FROM wp_usermeta WHERE meta_key='DAEMemberNo' AND meta_value=%s", $daeNumber);
    $user_id = $wpdb->get_var($sql);

    return $user_id;
}



/**
 *
 *
 *
 *
 */
function get_booking_available_credits()
{
    global $wpdb;

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data['disabled'] = true;
    $data['msg'] = 'Please log in to continue.';
    if(is_user_logged_in())
    {
        $cid = $_REQUEST['cid'];

        $sql = $wpdb->prepare("SELECT SUM(credit_amount) AS total_credit_amount, SUM(credit_used) AS total_credit_used FROM wp_credit WHERE owner_id IN (SELECT gpx_user_id FROM wp_mapuser2oid WHERE gpx_user_id=%s) AND (credit_expiration_date IS NULL OR credit_expiration_date > %s)", [$cid, date('Y-m-d')]);
        $credit = $wpdb->get_row($sql);

        $credits = $credit->total_credit_amount - $credit->total_credit_used;

        $sql = $wpdb->prepare("SELECT *  FROM `wp_mapuser2oid` WHERE `gpx_user_id` = %s", $cid);
        $wp_mapuser2oid = $gpx->GetMappedOwnerByCID($cid);

        $memberNumber = '';

        if(!empty($wp_mapuser2oid))
        {
            $memberNumber = $wp_mapuser2oid->gpr_oid;
        }

        $sql = $wpdb->prepare("SELECT a.*, b.ResortName, c.deposit_year FROM wp_owner_interval a
                INNER JOIN wp_resorts b ON b.gprID LIKE CONCAT(BINARY a.resortID, '%%')
                LEFT JOIN (SELECT MAX(deposit_year) as deposit_year, interval_number FROM wp_credit WHERE status != 'Pending' GROUP BY interval_number) c ON c.interval_number=a.contractID
                WHERE a.Contract_Status__c != 'Cancelled'
                    AND a.ownerID IN
                    (SELECT gpr_oid
                        FROM wp_mapuser2oid
                        WHERE gpx_user_id IN
                            (SELECT gpx_user_id
                            FROM wp_mapuser2oid
                            WHERE gpr_oid=%s))", $memberNumber);
        $ownerships = $wpdb->get_results($sql, ARRAY_A);

        //Rule is # of Ownerships  (i.e. � have 2 weeks, can have account go to negative 2, one per week)
        $newcredit = (($credits) - 1) * -1;
        if($newcredit > count($ownerships))
        {
            $data['msg'] = 'Please deposit a week to continue.';
        }
        else
        {
            $data['success'] = true;
        }
    }

    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_get_booking_available_credits', 'get_booking_available_credits');
add_action('wp_ajax_nopriv_get_booking_available_credits', 'get_booking_available_credits');


/**
 *
 *
 *
 *
 */
function add_ice_permission()
{
    $wp_user_query = new WP_User_Query(array('role' => 'gpx_member',
        'meta_query' => array(
            'key' => 'ICEStore',
            'compare' => 'NOT EXIST',
        ),
        'number'=>10000,
    ));

    $users = $wp_user_query->get_results();

    if (!empty($users)) {

        foreach ($users as $user)
        {
            add_user_meta( $user->id, 'ICEStore', 'Yes', true );
        }

    }
}

add_action('wp_ajax_add_ice_permission', 'add_ice_permission');
add_action('wp_ajax_nopriv_add_ice_permission', 'add_ice_permission');


/**
 *
 *
 *
 *
 */
function get_iceDailyKey()
{
    require_once GPXADMIN_API_DIR.'/functions/class.ice.php';
    $ice = new Ice(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $data = $ice->ICEGetDailyKey();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_iceDailyKey', 'get_iceDailyKey');
add_action('wp_ajax_nopriv_get_iceDailyKey', 'get_iceDailyKey');


/**
 *
 *
 *
 *
 */
function all_ice()
{
    global $wpdb;

    $sql = "SELECT user_id FROM  wp_GPR_Owner_ID__c where meta_rework < 5 AND user_id IN (SELECT user_id FROM `wp_usermeta` WHERE `meta_key` IN ('ICEStore', 'ICENameId', 'ICENameId')) order by id desc LIMIT 100";
    $rows = $wpdb->get_results($sql);

    if(!empty($rows))
    {
        foreach($rows as $row)
        {
            $user = $row->user_id;
            $allUsers[] = $user;
            $toSF = post_IceMemeberJWT($user);
            $wpdb->update('wp_GPR_Owner_ID__c', array('meta_rework'=>5), array('user_id'=>$user));
            if($wpdb->last_error)
            {
                exit;
            }
        }
        if(isset($_GET['reload']))
        {
            $sql = "SELECT count(user_id) FROM  wp_GPR_Owner_ID__c where meta_rework < 5 AND user_id IN (SELECT user_id FROM `wp_usermeta` WHERE `meta_key` IN ('ICEStore', 'ICENameId', 'ICENameId')) order by id desc";
            $rows = $wpdb->get_var($sql);
            {

                sleep(1);
                echo '<script type="text/javascript">window.location.reload();</script>';
            }
        }
    }
}
add_action('wp_ajax_nopriv_all_ice', 'all_ice');
add_action('wp_ajax_all_ice', 'all_ice');



/**
 *
 *
 *
 *
 */
function post_IceMemeberJWT($setUser='') {
    global $wpdb;

    require_once GPXADMIN_API_DIR.'/functions/class.ice.php';
    $ice = new Ice(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $cid = get_current_user_id();

    if (isset($_COOKIE['switchuser'])) {
        $cid = $_COOKIE['switchuser'];
    }

    if(!empty($setUser))
    {
        $cid = $setUser;
    }

    $user = get_userdata($cid);

    if(isset($user) && !empty($user)) {
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
    }

    $data = $ice->newIceMemberJWT();


    $sql = $wpdb->prepare("SELECT Name FROM wp_GPR_Owner_ID__c WHERE user_id=%d",$cid);
    $Name = $wpdb->get_var($sql);
    $sf = Salesforce::getInstance();

    $sfOwnerData['Name'] = $Name;
    $sfOwnerData['Arrivia_Activated__c'] = 'true';


    $sfType = 'GPR_Owner_ID__c';
    $sfObject = 'Name';
    $sfFields = [];
    $sfFields[0] = new SObject();
    $sfFields[0]->fields = $sfOwnerData;
    $sfFields[0]->type = $sfType;
    $sfAdd = $sf->gpxUpsert($sfObject, $sfFields);

    if(empty($setUser))
    {
        wp_send_json($data);
        wp_die();
    }
    else
    {
        return true;
    }
}


/**
 *
 *
 *
 *
 */
function post_IceMemeber($cid = '', $nojson='')
{
    require_once GPXADMIN_API_DIR.'/functions/class.ice.php';
    $ice = new Ice(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    if(empty($cid))
    {
        $icereturn = true;
        $cid = get_current_user_id();

        if(isset($_COOKIE['switchuser']))
        {
            $cid = $_COOKIE['switchuser'];
        }
    }

    $user = get_userdata($cid);

    if(isset($user) && !empty($user))
    {
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
    }

    $search = save_search($usermeta, 'ICE', 'ICE', '', '', $cid);

    if(isset($usermeta->ICENameId) && !empty($usermeta->ICENameId))
    {
        $data = $ice->newIceMember();
    }
    else
    {

        $data = $ice->newIceMember();
    }

    if(!empty($nojson))
    {
        return $data;
    }

    if($icereturn)
    {
        wp_send_json($data);
        wp_die();
    }
}

add_action('wp_ajax_post_IceMemeber', 'post_IceMemeber');
add_action('wp_ajax_nopriv_post_IceMemeber', 'post_IceMemeber');
add_shortcode('gpxpostice', 'post_IceMemeber');

//JWT Version
add_action('wp_ajax_post_IceMemeberJWT', 'post_IceMemeberJWT');
add_action('wp_ajax_nopriv_post_IceMemeberJWT', 'post_IceMemeberJWT');

/**
 *
 *
 *
 *
 */
function gpx_search_no_action()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $output = $gpx->return_search_no_action();

    echo wp_send_json($output);
    exit();
}
add_action("wp_ajax_gpx_search_no_action","gpx_search_no_action");
add_action("wp_ajax_nopriv_gpx_search_no_action", "gpx_search_no_action");

/**
 *
 *
 *
 *
 */
function gpx_ownercredit_report()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $return = $gpx->reportownercreditcoupon();
    if (file_exists($return)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($return).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($return));
        readfile($return);
        exit;
    }
}
add_action("wp_ajax_gpx_ownercredit_report","gpx_ownercredit_report");





/**
 *
 *
 *
 *
 */
function gpx_Owner_id_c(){
    global $wpdb;

    $data = array();

    $map2db = [
        'Name' => 'Name',
        'SPI_Owner_Name_1st__c' => 'SPI_Owner_Name_1st__c',
        'SPI_Email__c' => 'SPI_Email__c',
        'id' => 'id'
    ];
    /** @var ?array $search */
    $search = isset($_REQUEST['filter']) ? json_decode(stripslashes($_REQUEST['filter']), true) : null;
    $results = DB::table('wp_GPR_Owner_ID__c')
        ->select(['id', 'user_id', 'Name', 'SPI_Owner_Name_1st__c', 'SPI_Email__c', 'SPI_Home_Phone__c', 'SPI_Street__c', 'SPI_City__c', 'SPI_State__c'])
        ->when(isset($_REQUEST['offset']), fn($query) => $query->skip($_REQUEST['offset']))
        ->when(isset($_REQUEST['limit']), fn($query) => $query->take($_REQUEST['limit']))
        ->when(isset($_REQUEST['sort']), fn($query) => $query->orderBy($_REQUEST['sort'], gpx_esc_orderby($_REQUEST['order'])))
        ->when($search, fn($query) => $query->where(function($query) use ( $search ) {
            foreach($search as $sk => $sv) {
                $query->orWhere($sk == 'id' ? 'user_id' : $sk, 'LIKE', '%'.gpx_esc_like($sv).'%');
            }
        }))
        ->get()->toArray();

    $tsql = "SELECT COUNT(distinct user_id) as cnt  FROM `wp_GPR_Owner_ID__c` WHERE `user_id` IS NOT NULL and `Name` IN (SELECT `gpr_oid` FROM `wp_mapuser2oid`)";
    $data['total'] = (int) $wpdb->get_var($tsql);

    $i = 0;
    $dups = [];
    foreach($results as $result)
    {
        if(in_array($result->Name, $dups))
        {
            continue;
        }

        $dups[] = $result->Name;
        $welcomeEmailLink = '';

        $sql = $wpdb->prepare("SELECT COUNT(id) as cnt FROM wp_owner_interval WHERE Contract_Status__c='Active' AND userID=%s", $result->user_id);
        $intervals = $wpdb->get_var($sql);

        $data['rows'][$i]['action'] = '<a href="#" class="switch_user" data-user="'.$result->user_id.'" title="Select OwnerRepository and Return"><i class="fa fa-refresh fa-rotate-90" aria-hidden="true"></i></a>  <a  href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_edit&amp;id='.$result->user_id.'" title="Edit OwnerRepository Account" ><i class="fa fa-pencil" aria-hidden="true"></i>'.$welcomeEmailLink.'</a>';
        $data['rows'][$i]['id'] = $result->user_id;
        $data['rows'][$i]['Name'] = $result->Name;
        $data['rows'][$i]['SPI_Owner_Name_1st__c'] = $result->SPI_Owner_Name_1st__c;
        $data['rows'][$i]['SPI_Email__c'] = OwnerRepository::get_email($result->user_id);
        $data['rows'][$i]['SPI_Home_Phone__c'] = $result->SPI_Home_Phone__c;
        $data['rows'][$i]['SPI_Street__c'] = $result->SPI_Street__c;
        $data['rows'][$i]['SPI_City__c'] = $result->SPI_City__c;
        $data['rows'][$i]['SPI_State__c'] = $result->SPI_State__c;
        $data['rows'][$i]['Intervals'] = $intervals;
        $i++;
    }


    wp_send_json($data);
    wp_die();

}

add_action('wp_ajax_gpx_Owner_id_c', 'gpx_Owner_id_c');
add_action('wp_ajax_nopriv_gpx_Owner_id_c', 'gpx_Owner_id_c');
