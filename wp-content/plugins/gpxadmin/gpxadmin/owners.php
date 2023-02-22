<?php

use Illuminate\Support\Arr;
use GPX\Repository\OwnerRepository;

function gpx_get_member_number( $cid ) {
    global $wpdb;

    $sql = $wpdb->prepare( "SELECT `gpr_oid` FROM `wp_mapuser2oid` WHERE `gpx_user_id` = %d LIMIT 1", $cid );
    $memberno = $wpdb->get_var( $sql );
    if ( $memberno ) {
        return $memberno;
    }
    $sql = $wpdb->prepare( "SELECT `Name` FROM `wp_GPR_Owner_ID__c` WHERE `user_id` = %d LIMIT 1", $cid );
    $memberno = $wpdb->get_var( $sql );
    if ( $memberno ) {
        return $memberno;
    }

    return get_user_meta( $cid, 'DAEMemberNo', true );
}

function gpx_get_member_number( $cid ) {
    global $wpdb;

    $sql = $wpdb->prepare( "SELECT `gpr_oid` FROM `wp_mapuser2oid` WHERE `gpx_user_id` = %d LIMIT 1", $cid );
    $memberno = $wpdb->get_var( $sql );
    if ( $memberno ) {
        return $memberno;
    }
    $sql = $wpdb->prepare( "SELECT `Name` FROM `wp_GPR_Owner_ID__c` WHERE `user_id` = %d LIMIT 1", $cid );
    $memberno = $wpdb->get_var( $sql );
    if ( $memberno ) {
        return $memberno;
    }

    return get_user_meta( $cid, 'DAEMemberNo', true );
}

/**
 *
 *
 *
 *
 */
function gpx_get_owner_credits() {
    $gpx = new GpxAdmin( GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR );

    $data = $gpx->return_gpx_get_owner_credits();

    wp_send_json( $data );
}

add_action( 'wp_ajax_gpx_get_owner_credits', 'gpx_get_owner_credits' );


/**
 * @depreacted
 */
function gpx_temp_import_owners() {
    wp_send_json([]);
}

add_action( 'wp_ajax_temp_import_owners', 'gpx_temp_import_owners' );


/**
 *
 *
 *
 *
 */
function function_Ownership_mapping() {
    global $wpdb;
    $check_wp_mapuser2oid = $wpdb->get_results( "SELECT usr.ID as gpx_user_id, usr.user_nicename as gpx_username, Name as gpr_oid, oint.ownerID as gpr_oid_interval, resortID, user_status, Delinquent__c, unitweek  FROM wp_GPR_Owner_ID__c oid INNER JOIN wp_owner_interval oint ON oid.Name = oint.ownerID INNER JOIN wp_users usr ON usr.user_email = oid.SPI_Email__c" );

    if ( isset( $check_wp_mapuser2oid ) ) {
        if ( count( $check_wp_mapuser2oid ) != 0 ) {
            foreach ( $check_wp_mapuser2oid as $value ) {
                $sql = $wpdb->prepare( "SELECT *  FROM `wp_mapuser2oid` WHERE `gpx_user_id` = %s AND `gpx_username` LIKE %s AND `gpr_oid` = %s AND `gpr_oid_interval` = %s",
                                       [
                                           $value->gpx_user_id,
                                           $wpdb->esc_like( $value->gpx_username ),
                                           $value->gpr_oid,
                                           $value->gpr_oid_interval,
                                       ] );
                $check_available = $wpdb->get_results( $sql );

                if ( count( $check_available ) == 0 ) {
                    $wpdb->insert( 'wp_mapuser2oid', [
                        'gpx_user_id' => $value->gpx_user_id,
                        'gpx_username' => $value->gpx_username,
                        'gpr_oid' => $value->gpr_oid,
                        'gpr_oid_interval' => $value->gpr_oid_interval,
                        'resortID' => $value->resortID,
                        'user_status' => $value->user_status,
                        'Delinquent__c' => $value->Delinquent__c,
                        'unitweek' => $value->unitweek,
                    ] );
                }
            }
        }
    }
}

add_action( 'hook_cron_function_Ownership_mapping', 'function_Ownership_mapping' );


/**
 *
 *
 *
 *
 */
function gpx_owner_reassign() {
    global $wpdb;

    if ( isset( $_REQUEST['vestID'] ) ) {
        $wpdb->update( 'wp_credit', [ 'owner_id' => $_REQUEST['vestID'] ], [ 'owner_id' => $_REQUEST['legacyID'] ] );

        $sql = $wpdb->prepare( "SELECT id, data FROM wp_gpxTransactions WHERE userID=%s", $_REQUEST['legacyID'] );
        $rows = $wpdb->get_results( $sql );

        foreach ( $rows as $row ) {
            $id = $row->id;
            $tData = json_decode( $row->data, true );

            $tData['MemberNumber'] = $_REQUEST['vestID'];
            $wpdb->update( 'wp_gpxTransactions',
                           [ 'userID' => $_REQUEST['vestID'], 'data' => json_encode( $tData ) ],
                           [ 'id' => $id ] );
        }
    }
}

add_action( 'wp_ajax_gpx_owner_reassign', 'gpx_owner_reassign' );


/**
 *
 *
 *
 *
 */
function rework_ids_r() {
    global $wpdb;

    $sf = Salesforce::getInstance();

    $limit = 500;

    $sql = $wpdb->prepare( "SELECT user_id FROM `wp_GPR_Owner_ID__c` WHERE meta_rework=0 LIMIT %d", $limit );
    $users = $wpdb->get_results( $sql );
    foreach ( $users as $olduser ) {
        $wpdb->update( 'wp_GPR_Owner_ID__c', [ 'meta_rework' => 1 ], [ 'user_id' => $olduser->user_id ] );

        $daeMemberNo = get_user_meta( $olduser->user_id, 'DAEMemberNo', true );

        if ( $olduser->user_id == $daeMemberNo ) {
            continue;
        }
        update_user_meta( $olduser->user_id, 'DAEMemberNo', $olduser->user_id );

        //get the real id
        $query = "SELECT GPX_Member_VEST__c  FROM GPR_Owner_ID__c where
                   Name='" . $olduser->Name . "'";

        $results = $sf->query( $query );

        $nu = $results[0]->fields->GPX_Member_VEST__c;

        $user = reset(
            get_users(
                [
                    'meta_key' => 'GPX_Member_VEST__c',
                    'meta_value' => $nu,
                    'number' => 1,
                    'count_total' => false,
                ]
            )
        );

        $ou = $user->ID;

        if ( $nu != $olduser->user_id ) {
            $wpdb->update( 'wp_GPR_Owner_ID__c', [ 'user_id' => $nu ], [ 'id' => $olduser->id ] );
            $wpdb->update( 'wp_mapuser2oid', [ 'gpx_user_id' => $nu ], [ 'gpr_oid' => $olduser->Name ] );
            $wpdb->update( 'wp_owner_interval', [ 'userID' => $nu ], [ 'ownerID' => $olduser->Name ] );
        }
    }

    $sql = "SELECT count(user_id) FROM `wp_GPR_Owner_ID__c` WHERE meta_rework=0";

    $tcnt = $wpdb->get_var( $sql );

    if ( $tcnt > 0 ) {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json( [ 'remaining' => $tcnt ] );
}

add_action( 'wp_ajax_rework_ids_r', 'rework_ids_r' );


/**
 *
 *
 *
 *
 */
function rework_zero_ids() {
    global $wpdb;

    $sql = "SELECT a.id, b.user_id  FROM `wp_mapuser2oid` a
INNER JOIN wp_GPR_Owner_ID__c b ON a.gpr_oid=b.Name
WHERE `gpx_user_id` = 0";
    $rows = $wpdb->get_results( $sql );

    foreach ( $rows as $row ) {
        $wpdb->update( 'wp_mapuser2oid', [ 'gpx_user_id' => $row->user_id ], [ 'id' => $row->id ] );
    }

    $sql = "SELECT a.id, b.user_id  FROM `wp_owner_interval` a
INNER JOIN wp_GPR_Owner_ID__c b ON a.ownerID=b.Name
WHERE `userID` = 0";
    $rows = $wpdb->get_results( $sql );

    foreach ( $rows as $row ) {
        $wpdb->update( 'wp_owner_interval', [ 'userID' => $row->user_id ], [ 'id' => $row->id ] );
    }
}

add_action( 'wp_ajax_rework_zero_ids', 'rework_zero_ids' );


/**
 *
 *
 *
 *
 */
function rework_username() {
    global $wpdb;

    $sqlOP = "SELECT ID FROM wp_users WHERE user_login LIKE '%NOT_A_VALID_EMAIL%' LIMIT 100";
    $rows = $wpdb->get_results( $sqlOP );

    foreach ( $rows as $row ) {
        $wpdb->update( 'wp_users', [ 'user_login' => $row->ID ], [ 'ID' => $row->ID ] );
    }


    $sql = "SELECT COUNT(ID) AS cnt FROM wp_users WHERE user_login LIKE '%NOT_A_VALID_EMAIL%'";
    $remain = $wpdb->get_var( $sql );
    if ( $remain > 0 ) {
        sleep( 1 );
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json( [ 'remaining' => $remain ] );
}

add_action( 'wp_ajax_rework_username', 'rework_username' );


/**
 *
 *
 *
 *
 */
function rework_ids() {
    global $wpdb;

    $sql = "SELECT ID, user_login FROM `wp_users` WHERE `user_login` LIKE 'U%' ORDER BY ID DESC";
    $users = $wpdb->get_results( $sql );

    foreach ( $users as $user ) {
        $userID = $user->ID;
        $ul = str_replace( "U", "", $user->user_login );
        $ul = str_replace( " ", "", $ul );
    }

    $of = $offset + $limit;

    wp_send_json( [ 'remaining' => $tcnt ] );
}

add_action( 'wp_ajax_rework_ids', 'rework_ids' );


/**
 *
 *
 *
 *
 */
                               'Name' => $value->Name,
                               'user_id' => $user_id,
                               'SPI_Owner_Name_1st__c' => $fullname,
                               'SPI_Email__c' => $value->SPI_Email__c,
                               'SPI_Home_Phone__c' => $value->SPI_Home_Phone__c,
                               'SPI_Work_Phone__c' => $value->SPI_Work_Phone__c,
                               'SPI_Street__c' => $value->SPI_Street__c,
                               'SPI_City__c' => $value->SPI_City__c,
                               'SPI_State__c' => $value->SPI_State__c,
                               'SPI_Zip_Code__c' => $value->SPI_Zip_Code__c,
                               'SPI_Country__c' => $value->SPI_Country__c,
                           ] );
                                            $wpdb->esc_like( $resortName ) );
function vest_import_owner() {
    global $wpdb;

    $sql = "SELECT * FROM temp_users WHERE user_login NOT IN (select user_login FROM wp_users) LIMIT 100";
    $rows = $wpdb->get_results( $sql );

    foreach ( $rows as $row ) {
        $import = [
            'user_login' => $row->user_login,
            'user_pass' => wp_generate_password(),
            'user_email' => $row->user_email,
            'user_nicename' => $row->user_nicename,
            'user_url' => $row->user_url,
            'user_registered' => $row->user_registered,
            'user_activation_key' => $row->user_activation_key,
            'user_status' => $row->user_status,
            'display_name' => $row->display_name,
        ];
        $wpdb->insert( 'wp_users', $import );
        if ( $wpdb->last_error ) {
            exit;
        }
        $id = $wpdb->insert_id;

        $sql = $wpdb->prepare( "SELECT * FROM temp_usermeta WHERE user_id=%s", $row->user_id );
        $ums = $wpdb->get_results( $sql );

        foreach ( $ums as $um ) {
            $importMeta = [
                'user_id' => $id,
                'meta_key' => $um->meta_key,
                'meta_value' => $um->meta_value,
            ];
            $wpdb->insert( 'wp_usermeta', $importMeta );
            if ( $wpdb->last_error ) {
                exit;
            }
        }
    }

    $sql = "SELECT COUNT(id) as cnt FROM temp_users WHERE user_login NOT IN (select user_login FROM wp_users)";
    $remain = $wpdb->get_var( $sql );

    if ( $remain > 0 ) {
        echo $remain;
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json( [ 'remaining' => $remain ] );
}

add_action( 'wp_ajax_vest_import_owner', 'vest_import_owner' );


/**
 *
 *
 *
 *
 */
function owner_check() {
    wp_send_json([]);
}

add_action( 'wp_ajax_owner_check', 'owner_check' );


/**
 * @depreacted
 */
function gpx_add_owner() {
    if(isset($_POST['DAEMemberNo']) && isset($_POST['RMN']) && isset($_POST['password'])) {
        $data = [ 'error' => 'EMS Error!' ];
    } else {
        $data = [ 'error' => 'Member number, Resort Member Number and password are required' ];
    } else {
        $data = [ 'error' => 'Member number, Resort Member Number and password are required' ];
    }
    wp_send_json( $data );
}

add_action( "wp_ajax_gpx_add_owner", "gpx_add_owner" );
add_action( "wp_ajax_nopriv_gpx_add_owner", "gpx_add_owner" );


/**
 *
 *
 *
 *
 */
function gpx_mass_update_owners() {
    $gpxadmin = new GpxAdmin( GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR );
    $offset = $_GET['offset'] ?? '';
    }

    $owners = $gpxadmin->return_mass_update_owners( $_GET['orderby'], $_GET['order'], $offset );

    wp_send_json($owners);
}

add_action( "wp_ajax_gpx_mass_update_owners", "gpx_mass_update_owners" );
add_action( "wp_ajax_nopriv_gpx_mass_update_owners", "gpx_mass_update_owners" );


/**
 *
 *
 *
 *
 */
function get_gpx_customers() {
    $gpx = new GpxAdmin( GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR );

    $data['html'] = $gpx->return_gpx_owner_search();

    wp_send_json( $data );
}

add_action( 'wp_ajax_get_gpx_customers', 'get_gpx_customers' );
add_action( 'wp_ajax_nopriv_get_gpx_customers', 'get_gpx_customers' );


/**
 *
 *
 *
 *
 */
function get_gpx_findowner() {
    $search = $_GET['search'] ?? '';
    $gpx = new GpxAdmin( GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR );
    if(mb_strlen($search) > 0) {
        $data['html'] = $gpx->return_get_gpx_findowner($search);
    } else {
        $data = false;
    }

    wp_send_json( $data );
}

add_action( 'wp_ajax_get_gpx_findowner', 'get_gpx_findowner' );
add_action( 'wp_ajax_nopriv_get_gpx_findowner', 'get_gpx_findowner' );


/**
 *
 *
 *
 *
 */
function gpx_get_owner_for_add_transaction() {
    if ( isset( $_GET['memberNo'] ) && ! empty( $_GET['memberNo'] ) ) {
    $data = null;
    $memberno = $_GET['memberNo'] ?? null;
    if(!$memberno){
        wp_send_json(null);
    }
    $user = Arr::first(
        get_users(
            array(
                'meta_key' => 'DAEMemberNo',
                'meta_value' => $memberno,
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
    wp_send_json( $data );
}

add_action( 'wp_ajax_gpx_get_owner_for_add_transaction', 'gpx_get_owner_for_add_transaction' );
add_action( 'wp_ajax_nopriv_gpx_get_owner_for_add_transaction', 'gpx_get_owner_for_add_transaction' );

/**
 *
 *
 *
 *
 */
function gpx_load_ownership( $id ) {
    $gpx = new GpxAdmin( GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR );
    $cid = gpx_get_switch_user_cookie();
    $usermeta = gpx_get_usermeta($cid);
        return $a[0];
    }, get_user_meta( $cid ) );

    $daeMemberNo = $usermeta->DAEMemberNo;
    if ( isset( $_REQUEST['member_no'] ) ) {
        $daeMemberNo = $_REQUEST['member_no'];
    }
    $ownership = $gpx->load_ownership( $daeMemberNo );

    $data['html'] = $ownership;

    wp_send_json( $data );
}

add_action( 'wp_ajax_gpx_load_ownership', 'gpx_load_ownership' );


/**
 *
 *
 *
 *
 */
function gpx_import_owner_credit() {
    global $wpdb;

    $gpx = new GpxAdmin( GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR );

    $sql = "SELECT * FROM wp_gpx_import_account_credit WHERE is_added=0 LIMIT 100";
    $results = $wpdb->get_results( $sql );

    foreach ( $results as $row ) {
        $name = 'ac' . $row->id . $row->account;

        $userid = gpx_user_id_by_daenumber( $row->account );

        if(empty($userid)) {
            continue;
        }

        $occ = [
            'Name' => $name,
            'Slug' => $name,
            'Active' => 1,
            'singleuse' => 0,
            'amount' => $row->amount,
            'owners' => [ $userid ],
            'expirationDate' => date( 'Y-m-d', strtotime( $row->business_date ) ),
            'comments' => 'Imported Credit',
        ];

        $gpx->promodeccouponsadd( $occ );

        $wpdb->update( 'wp_gpx_import_account_credit', [ 'is_added' => 1 ], [ 'id' => $row->id ] );
    }
}

add_action( 'wp_ajax_gpx_import_owner_credit', 'gpx_import_owner_credit' );
add_action( 'wp_ajax_nopriv_gpx_import_owner_credit', 'gpx_import_owner_credit' );


/**
 *
 *
 *
 *
 */
function gpx_user_id_by_daenumber( $daeNumber ) {
    global $wpdb;

    $sql = $wpdb->prepare( "SELECT user_id FROM wp_usermeta WHERE meta_key='DAEMemberNo' AND meta_value=%s",
    return $wpdb->get_var($sql);
}


/**
 *
 *
 *
 *
 */
function get_booking_available_credits() {
    global $wpdb;

    $gpx = new GpxAdmin( GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR );

    $data['disabled'] = true;
    $data['msg'] = 'Please log in to continue.';
    if ( is_user_logged_in() ) {
        $cid = $_REQUEST['cid'];

        $sql = $wpdb->prepare( "SELECT SUM(credit_amount) AS total_credit_amount, SUM(credit_used) AS total_credit_used FROM wp_credit WHERE owner_id IN (SELECT gpx_user_id FROM wp_mapuser2oid WHERE gpx_user_id=%s) AND (credit_expiration_date IS NULL OR credit_expiration_date > %s)",
                               [ $cid, date( 'Y-m-d' ) ] );
        $credit = $wpdb->get_row( $sql );

        $credits = $credit->total_credit_amount - $credit->total_credit_used;

        $sql = $wpdb->prepare( "SELECT *  FROM `wp_mapuser2oid` WHERE `gpx_user_id` = %s", $cid );
        $wp_mapuser2oid = $gpx->GetMappedOwnerByCID( $cid );

        $memberNumber = '';

        if ( ! empty( $wp_mapuser2oid ) ) {
            $memberNumber = $wp_mapuser2oid->gpr_oid;
        }

        $sql = $wpdb->prepare( "SELECT a.*, b.ResortName, c.deposit_year FROM wp_owner_interval a
                INNER JOIN wp_resorts b ON b.gprID LIKE CONCAT(BINARY a.resortID, '%%')
                LEFT JOIN (SELECT MAX(deposit_year) as deposit_year, interval_number FROM wp_credit WHERE status != 'Pending' GROUP BY interval_number) c ON c.interval_number=a.contractID
                WHERE a.Contract_Status__c != 'Cancelled'
                    AND a.ownerID IN
                    (SELECT gpr_oid
                        FROM wp_mapuser2oid
                        WHERE gpx_user_id IN
                            (SELECT gpx_user_id
                            FROM wp_mapuser2oid
                            WHERE gpr_oid=%s))",
                               $memberNumber );
        $ownerships = $wpdb->get_results( $sql, ARRAY_A );

        //Rule is # of Ownerships  (i.e. � have 2 weeks, can have account go to negative 2, one per week)
        $newcredit = ( ( $credits ) - 1 ) * - 1;
        if ( $newcredit > count( $ownerships ) ) {
            $data['msg'] = 'Please deposit a week to continue.';
        } else {
            $data['success'] = true;
        }
    }

    wp_send_json( $data );
}

add_action( 'wp_ajax_get_booking_available_credits', 'get_booking_available_credits' );
add_action( 'wp_ajax_nopriv_get_booking_available_credits', 'get_booking_available_credits' );


/**
 *
 *
 *
 *
 */
function add_ice_permission() {
    $wp_user_query = new WP_User_Query( [
                                            'role' => 'gpx_member',
                                            'meta_query' => [
                                                'key' => 'ICEStore',
                                                'compare' => 'NOT EXIST',
                                            ],
                                            'number' => 10000,
                                        ] );

    $users = $wp_user_query->get_results();

    if ( ! empty( $users ) ) {
        foreach ( $users as $user ) {
            add_user_meta( $user->id, 'ICEStore', 'Yes', true );
        }
    }
}

add_action( 'wp_ajax_add_ice_permission', 'add_ice_permission' );
add_action( 'wp_ajax_nopriv_add_ice_permission', 'add_ice_permission' );


/**
 *
 *
 *
 *
 */
function get_iceDailyKey() {
    $ice = new Ice( GPXADMIN_API_URI, GPXADMIN_API_DIR );

    $data = $ice->ICEGetDailyKey();

    wp_send_json( $data );
}

add_action( 'wp_ajax_get_iceDailyKey', 'get_iceDailyKey' );
add_action( 'wp_ajax_nopriv_get_iceDailyKey', 'get_iceDailyKey' );


/**
 *
 *
 *
 *
 */
function all_ice() {
    global $wpdb;

    $sql = "SELECT user_id FROM  wp_GPR_Owner_ID__c where meta_rework < 5 AND user_id IN (SELECT user_id FROM `wp_usermeta` WHERE `meta_key` IN ('ICEStore', 'ICENameId', 'ICENameId')) order by id desc LIMIT 100";
    $rows = $wpdb->get_results( $sql );

    if ( ! empty( $rows ) ) {
        foreach ( $rows as $row ) {
            $user = $row->user_id;
            $allUsers[] = $user;
            $toSF = post_IceMemeberJWT( $user );
            $wpdb->update( 'wp_GPR_Owner_ID__c', [ 'meta_rework' => 5 ], [ 'user_id' => $user ] );
            if ( $wpdb->last_error ) {
                exit;
            }
        }
        if ( isset( $_GET['reload'] ) ) {
            $sql = "SELECT count(user_id) FROM  wp_GPR_Owner_ID__c where meta_rework < 5 AND user_id IN (SELECT user_id FROM `wp_usermeta` WHERE `meta_key` IN ('ICEStore', 'ICENameId', 'ICENameId')) order by id desc";
            $rows = $wpdb->get_var( $sql );
            {
                sleep( 1 );
                echo '<script type="text/javascript">window.location.reload();</script>';
            }
        }
    }
}

add_action( 'wp_ajax_nopriv_all_ice', 'all_ice' );
add_action( 'wp_ajax_all_ice', 'all_ice' );


/**
 *
 *
 *
 *
 */
function post_IceMemeberJWT( $setUser = '' ) {
    global $wpdb;

    $ice = new Ice( GPXADMIN_API_URI, GPXADMIN_API_DIR );

    $cid = gpx_get_switch_user_cookie();

    if ( ! empty( $setUser ) ) {
        $cid = $setUser;
    }

    $user = get_userdata( $cid );

    if ( isset( $user ) && ! empty( $user ) ) {
        $usermeta = (object) array_map( function ( $a ) {
            return $a[0];
        }, get_user_meta( $cid ) );
    }

    $data = $ice->newIceMemberJWT();


    $sql = $wpdb->prepare( "SELECT Name FROM wp_GPR_Owner_ID__c WHERE user_id=%d", $cid );
    $Name = $wpdb->get_var( $sql );
    $sf = Salesforce::getInstance();

    $sfOwnerData['Name'] = $Name;
    $sfOwnerData['Arrivia_Activated__c'] = 'true';


    $sfType = 'GPR_Owner_ID__c';
    $sfObject = 'Name';
    $sfFields = [];
    $sfFields[0] = new SObject();
    $sfFields[0]->fields = $sfOwnerData;
    $sfFields[0]->type = $sfType;
    $sfAdd = $sf->gpxUpsert( $sfObject, $sfFields );

    if ( empty( $setUser ) ) {
        wp_send_json( $data );
    } else {
        return true;
    }
}


/**
 *
 *
 *
 *
 */
function post_IceMemeber( $cid = '', $nojson = '' ) {
    $ice = new Ice( GPXADMIN_API_URI, GPXADMIN_API_DIR );

    if ( empty( $cid ) ) {
        $icereturn = true;
        $cid = gpx_get_switch_user_cookie();
    }

    $user = get_userdata( $cid );

    if ( isset( $user ) && ! empty( $user ) ) {
        $usermeta = (object) array_map( function ( $a ) {
            return $a[0];
        }, get_user_meta( $cid ) );
    }

    $search = save_search( $usermeta, 'ICE', 'ICE', '', '', $cid );

    if ( isset( $usermeta->ICENameId ) && ! empty( $usermeta->ICENameId ) ) {
        $data = $ice->newIceMember();
    } else {
        $data = $ice->newIceMember();
    }

    if ( ! empty( $nojson ) ) {
        return $data;
    }

    if ( $icereturn ) {
        wp_send_json( $data );
    }
}

add_action( 'wp_ajax_post_IceMemeber', 'post_IceMemeber' );
add_action( 'wp_ajax_nopriv_post_IceMemeber', 'post_IceMemeber' );
add_shortcode( 'gpxpostice', 'post_IceMemeber' );

//JWT Version
add_action( 'wp_ajax_post_IceMemeberJWT', 'post_IceMemeberJWT' );
add_action( 'wp_ajax_nopriv_post_IceMemeberJWT', 'post_IceMemeberJWT' );

/**
 *
 *
 *
 *
 */
function gpx_search_no_action() {
    $gpx = new GpxAdmin( GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR );

    $output = $gpx->return_search_no_action();

    wp_send_json($output);
}

add_action( "wp_ajax_gpx_search_no_action", "gpx_search_no_action" );
add_action( "wp_ajax_nopriv_gpx_search_no_action", "gpx_search_no_action" );

/**
 *
 *
 *
 *
 */
function gpx_ownercredit_report() {
    $gpx = new GpxAdmin( GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR );
    $return = $gpx->reportownercreditcoupon();
    if ( file_exists( $return ) ) {
        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="' . basename( $return ) . '"' );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate' );
        header( 'Pragma: public' );
        header( 'Content-Length: ' . filesize( $return ) );
        readfile( $return );
        exit;
    }
}
add_action( "wp_ajax_gpx_ownercredit_report", "gpx_ownercredit_report" );
function gpx_Owner_id_c() {
    /** @var ?array $search */
    $search = isset( $_REQUEST['filter'] ) ? json_decode( stripslashes( $_REQUEST['filter'] ), true ) : null;
    $query  = DB::table( 'wp_GPR_Owner_ID__c' )
                ->whereNotNull( 'Name' )
                ->when( $search, fn( $query ) => $query->where( function ( $query ) use ( $search ) {
                    foreach ( $search as $sk => $sv ) {
                        $query->orWhere( $sk == 'id' ? 'user_id' : $sk, 'LIKE', '%' . gpx_esc_like( $sv ) . '%' );
                    }
                } ) );

    $total = $query->count();
    $data  = [
        'total' => $total,
        'rows'  => [],
    ];
    if ( $total <= 0 ) {
        wp_send_json( $data );
    }

    $results = $query
        ->select( [ 'id', 'user_id', 'Name', 'SPI_Owner_Name_1st__c', 'SPI_Email__c', 'SPI_Home_Phone__c', 'SPI_Street__c', 'SPI_City__c', 'SPI_State__c', ] )
        ->addSelect(
            [
                DB::raw( "IFNULL((SELECT IF(meta_value, 1, 0) FROM wp_usermeta WHERE meta_key = 'GPXOwnerAccountDisabled' AND wp_usermeta.user_id = wp_GPR_Owner_ID__c.user_id), 0) as disabled" ),
                DB::raw( "EXISTS(SELECT wp_users.ID FROM wp_users WHERE wp_users.ID = wp_GPR_Owner_ID__c.user_id) as has_login" ),
                DB::raw( "(SELECT COUNT(id) as cnt FROM wp_owner_interval WHERE Contract_Status__c='Active' AND userID = wp_GPR_Owner_ID__c.user_id) as intervals" ),
            ]
        )
        ->when( isset( $_REQUEST['offset'] ), fn( $query ) => $query->skip( $_REQUEST['offset'] ) )
        ->when( isset( $_REQUEST['limit'] ), fn( $query ) => $query->take( $_REQUEST['limit'] ) )
        ->when( isset( $_REQUEST['sort'] ),
            fn( $query ) => $query->orderBy( $_REQUEST['sort'], gpx_esc_orderby( $_REQUEST['order'] ) ) )
        ->get();

    $data['rows'] = $results->map( function ( $result ) {
        if ( ! $result->has_login ) {
            $action = '<i title="No account login" class="fa fa-ban" aria-hidden="true"></i>';
        } else {
            $action = '<a href="#" class="switch_user" data-user="' . esc_attr($result->user_id) . '" title="Select Owner and Return"><i class="fa fa-refresh fa-rotate-90" aria-hidden="true"></i></a>';
        }
        $action .= '  <a  href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_edit&amp;id=' . esc_attr($result->user_id) . '" title="Edit Owner Account" ><i class="fa fa-pencil" aria-hidden="true"></i></a>';

        return [
            'action' => $action,
            'id' => esc_html($result->user_id),
            'Name' => esc_html($result->Name),
            'SPI_Owner_Name_1st__c' => esc_html($result->SPI_Owner_Name_1st__c),
            'SPI_Email__c' => esc_html(OwnerRepository::instance()->get_email( $result->user_id )),
            'SPI_Home_Phone__c' => esc_html($result->SPI_Home_Phone__c),
            'SPI_Street__c' => esc_html($result->SPI_Street__c),
            'SPI_City__c' => esc_html($result->SPI_City__c),
            'SPI_State__c' => esc_html($result->SPI_State__c),
            'Intervals' => esc_html($result->intervals),
        ];
    } );
    wp_send_json( $data );
}

add_action( 'wp_ajax_gpx_Owner_id_c', 'gpx_Owner_id_c' );
add_action( 'wp_ajax_nopriv_gpx_Owner_id_c', 'gpx_Owner_id_c' );
