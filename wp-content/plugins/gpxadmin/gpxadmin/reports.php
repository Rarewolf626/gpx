<?php

use GPX\Model\Reports\MasterAvailability;

function gpx_remove_report() {
    global $wpdb;

    if (isset($_POST['id'])) {
        $wpdb->delete('wp_gpx_report_writer', ['id' => $_POST['id']]);
    }

    wp_send_json(['success' => true]);
}

add_action('wp_ajax_gpx_remove_report', 'gpx_remove_report');

function get_gpx_reportsearches() {
    global $wpdb;
    $data = [];
    $sql = "SELECT a.*, b.user_login, b.display_name FROM wp_gpxMemberSearch a
                INNER JOIN wp_users b ON a.userID = b.ID
                where a.datetime between '2018-04-02' and '2018-04-30'";
    $searches = $wpdb->get_results($sql);
    $i = 0;
    foreach ($searches as $search) {
        $searchVal = '';
        $jsondata = json_decode($search->data);
        foreach ($jsondata as $key => $value) {
            if (substr($key, 0, 6) == 'resort') {
                $searchVal = $value;
            }
        }
        if (empty($searchVal)) {
            continue;
        }
        $data[$i]['resort'] = stripslashes($searchVal->ResortName);
        $data[$i]['ref'] = $searchVal->refDomain;
        $data[$i]['date'] = $searchVal->DateViewed;
        $data[$i]['resortID'] = $searchVal->id;
        $data[$i]['userID'] = $search->user_login;
        $data[$i]['user_name'] = $search->display_name;
        $data[$i]['search_location'] = stripslashes($searchVal->search_location);
        $data[$i]['search_month'] = $searchVal->search_month;
        $data[$i]['search_year'] = $searchVal->search_year;
        $i++;
    }

    wp_send_json($data);
}

add_action('wp_ajax_get_gpx_reportsearches', 'get_gpx_reportsearches');
add_action('wp_ajax_nopriv_get_gpx_reportsearches', 'get_gpx_reportsearches');

function edit_gpx_resort() {
    global $wpdb;

    $output = ['success' => false];
    if (isset($_POST['ResortID'])) {
        $ResortID = $_POST['ResortID'];
        unset($_POST['ResortID']);
        foreach ($_POST as $key => $value) {
            $where = ['ResortID' => $ResortID, 'meta_key' => $key];
            $wpdb->delete('wp_resorts_meta', $where);
            $id = '1';
            if (!empty($value)) {
                $value = stripslashes($value);
                $data = ['ResortID' => $ResortID, 'meta_key' => $key, 'meta_value' => $value];
                $wpdb->replace('wp_resorts_meta', $data);
                $id = $wpdb->insert_id;
            }

        }

        if (!empty($id)) {
            $output = ['success' => true, 'msg' => 'Edit Successful!'];
        } else {
            $output['msg'] = 'Nothing to update!';
        }
    } else {
        $output['msg'] = 'Resort not updated!';
    }

    wp_send_json($output);
}

add_action('wp_ajax_edit_gpx_resort', 'edit_gpx_resort');
add_action('wp_ajax_nopriv_edit_gpx_resort', 'edit_gpx_resort');

function gpx_report_writer($return) {
    global $wpdb;

    /*
     * the first key is the table that will be used
     * Name is the name that will be displayed on a page
     * Fields are the field being used
     *      if the field is an array then it is a different type
     *          join is a joined table
     *              xref on join is the field as used when writing the query
     *          case is used when an integer (enum) represents a variable -- for example: wp_room type is 1=Exchange, 2=Rental, 3=Both
     *              xref on case is the field as used when writing the query
     *			joincase is both a join and a case
      *          usermeta pulls from usermeta table
     *			json is used to extract json data from the table -- Key is the json object key and value is what is displayed on the writer or as a column heading
     */
    //transactins add member address and phone, guest phone,
    $tables = [
        'wp_gpxOwnerCreditCoupon' => [
            'table' => 'wp_gpxOwnerCreditCoupon',
            'name' => 'Owner Credit Coupon',
            'fields' => [
                'id' => 'ID',
                'name' => 'Name',
                'couponcode' => 'Coupon Code',
                'comments' => 'Comments',
                'singleuse' => [
                    'type' => 'case',
                    'column' => 'singleuse',
                    'name' => 'Single Use',
                    'xref' => 'wp_gpxOwnerCreditCoupon.singleuse',
                    'case' => [
                        '0' => 'No',
                        '1' => 'Yes',
                    ],
                ],
                'active' => [
                    'type' => 'case',
                    'column' => 'active',
                    'name' => 'Active',
                    'xref' => 'wp_gpxOwnerCreditCoupon.active',
                    'case' => [
                        '0' => 'No',
                        '1' => 'Yes',
                    ],
                ],
                'expirationDate' => 'Expiration Date',

                'memberFirstName' => [
                    'type' => 'usermeta',
                    'xref' => 'ownerID',
                    'column' => 'first_name',
                    'name' => 'Owner First Name',
                    'key' => 'memberFirstName',
                    'on' => [
                        'wp_gpxOwnerCreditCoupon_owner ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_owner.couponID',
                    ],
                ],
                'memberLastName' => [
                    'type' => 'usermeta',
                    'xref' => 'ownerID',
                    'column' => 'last_name',
                    'name' => 'Owner Last Name',
                    'key' => 'memberLastName',
                    'on' => [
                        'wp_gpxOwnerCreditCoupon_owner ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_owner.couponID',
                    ],
                ],
                'memberEmail' => [
                    'type' => 'usermeta',
                    'xref' => 'ownerID',
                    'column' => 'user_email',
                    'name' => 'Owner Email',
                    'key' => 'memberEmail',
                    'on' => [
                        'wp_gpxOwnerCreditCoupon_owner ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_owner.couponID',
                    ],
                ],
                'activity' => [
                    'type' => 'join',
                    'column' => 'activity',
                    'name' => 'Activity',
                    'xref' => 'wp_gpxOwnerCreditCoupon.activity',
                    'where' => 'wp_gpxOwnerCreditCoupon_activity.activity',
                    'on' => [
                        'wp_gpxOwnerCreditCoupon_activity ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_activity.couponID',
                    ],
                ],
                'amount' => [
                    'type' => 'join',
                    'column' => 'amount',
                    'name' => 'Amount',
                    'xref' => 'wp_gpxOwnerCreditCoupon.amount',
                    'where' => 'wp_gpxOwnerCreditCoupon_activity.amount',
                    'on' => [
                        'wp_gpxOwnerCreditCoupon_activity ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_activity.couponID',
                    ],
                ],
                'activity_comments' => [
                    'type' => 'join',
                    'column' => 'activity_comments',
                    'name' => 'Activity Comments',
                    'xref' => 'wp_gpxOwnerCreditCoupon.activity_comments',
                    'on' => [
                        'wp_gpxOwnerCreditCoupon_activity ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_activity.couponID',
                    ],
                ],
                'activity_date' => [
                    'type' => 'join',
                    'column' => 'datetime',
                    'name' => 'Activity Date',
                    'xref' => 'wp_gpxOwnerCreditCoupon.activity_date',
                    'where' => 'wp_gpxOwnerCreditCoupon_activity.datetime',
                    'on' => [
                        'wp_gpxOwnerCreditCoupon_activity ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_activity.couponID',
                    ],
                ],
                'issuerFirstName' => [
                    'type' => 'usermeta',
                    'xref' => 'userID',
                    'column' => 'first_name',
                    'name' => 'Issued by First Name',
                    'key' => 'issuerFirstName',
                    'on' => [
                        'wp_gpxOwnerCreditCoupon_activity ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_activity.couponID',
                    ],
                ],
                'issuerLastName' => [
                    'type' => 'usermeta',
                    'xref' => 'userID',
                    'column' => 'last_name',
                    'name' => 'Issued by Last Name',
                    'key' => 'issuerLastName',
                    'on' => [
                        'wp_gpxOwnerCreditCoupon_activity ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_activity.couponID',
                    ],
                ],
            ],
        ],
        'wp_room' => [
            'table' => 'wp_room',
            'name' => 'Inventory',
            'fields' => [
                'record_id' => 'ID',
                'GuestName' => [
                    'type' => 'join_json',
                    'column' => 'data.GuestName',
                    'name' => 'Guest Name',
                    'xref' => 'wp_room.GuestName',
                    'on' => [
                        'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id',
                    ],
                ],
                // Credits Used
                'credit_add' => [
                    'type' => 'join_case',
                    'column' => 'wp_partner.user_id',
                    'column_special' => 'credit_add',
                    'name' => 'Credit Add',
                    'xref' => 'wp_room.credit_add',
                    'where' => 'wp_partner.user_id',
                    'column_override' => 'credit_add',
                    'as' => 'credit_add',
                    'case_special' => [
                        'NULL' => '0',
                        'NOT NULL' => '1',
                    ],
                    'on' => [
                        'wp_partner ON wp_partner.user_id=wp_room.source_partner_id',
                    ],
                ],
                'credit_subtract' => [
                    'type' => 'join_case',
                    'column' => 'query|credit_subtract|(SELECT COUNT(*) FROM wp_partner WHERE wp_partner.user_id=wp_gpxTransactions.userID)',
                    'column_special' => 'credit_subtract',
                    'name' => 'Credit Subtract',
                    'xref' => 'wp_room.credit_subtract',
                    'where' => 'wp_partner.name',
                    'column_override' => 'credit_subtract',
                    'as' => 'credit_subtract',
                    'case' => [
                        '0' => '0',
                        '1' => '1',
                    ],
                    'on' => [
                        'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id',
                    ],
                ],
                'resort_confirmation_number' => 'Resort Confirmation Number',
                'create_date' => 'Created Date',
                'active' => [
                    'type' => 'case',
                    'column' => 'active',
                    'name' => 'Active Yes or No',
                    'xref' => 'wp_room.active',
                    'case' => [
                        '0' => 'No',
                        '1' => 'Yes',
                    ],
                ],
                'source_partner_id' => [
                    'type' => 'join',
                    'column' => 'source_partner_id',
                    'name' => 'Partner ID',
                    'xref' => 'wp_room.source_partner_id',
                    'on' => [
                        'wp_partner ON wp_partner.record_id=wp_room.source_partner_id',
                    ],
                ],
                'source_partner_name' => [
                    'type' => 'join',
                    'column' => 'stbl.name',
                    'column_override' => 'source_partner_name',
                    'as' => ' source_partner_name',
                    'name' => 'Source Partner Name',
                    'xref' => 'wp_room.source_partner_name',
                    'on' => [
                        'wp_partner stbl ON stbl.user_id=wp_room.source_partner_id',
                    ],
                ],
                'booked_by_partner_name' => [
                    'type' => 'join',
                    'column' => 'btbl.name',
                    'column_override' => 'booked_by_partner_name',
                    'as' => 'booked_by_partner_name',
                    'name' => 'Booked By Partner Name',
                    'xref' => 'wp_room.booked_by_partner_name',
                    'on' => [
                        'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id',
                        'wp_partner btbl ON btbl.user_id=wp_gpxTransactions.userID',
                    ],
                ],
                'partner_name' => [
                    'type' => 'join',
                    'column' => 'COALESCE(stbl.name, btbl.name)',
//                         'columns'=>[
//                             'name'=>'wp_room.partner_name',
//                             'cols'=>[
//                                 'booked_by_partner_name',
//                                 'source_partner_name',
//                                 ],
//                             ],
                    'name' => 'Partner Name',
                    'column_override' => 'partner_name',
                    'as' => 'partner_name',
                    'xref' => 'wp_room.partner_name',
                    'where' => 'COALESCE(stbl.name, btbl.name)',
                    'on' => [
                        'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id',
                        'wp_partner btbl ON btbl.user_id=wp_gpxTransactions.userID',
                        'wp_partner stbl ON stbl.user_id=wp_room.source_partner_id',
                    ],
                ],
                'status' => [
                    'type' => 'join',
                    'column' => 'status',
                    'name' => 'Status',
                    'xref' => 'wp_room.status',
                    'on' => [
                        'room_status ON room_status.weekId=wp_room.record_id',
                    ],
                ],
                'transactionCancelled' => [
                    'type' => 'join_case',
                    'column' => 'cancelledDate',
                    'name' => 'Transaction Cancelled Date',
                    'on' => [
                        'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id',
                    ],
                ],
                'user' => [
                    'type' => 'join',
                    'column' => 'user',
                    'name' => 'Held For',
                    'xref' => 'wp_room.user',
                    'on' => [
                        'wp_gpxPreHold ON wp_room.record_id=wp_gpxPreHold.weekId AND wp_gpxPreHold.released=0',
                    ],
                ],
                'release_on' => [
                    'type' => 'join',
                    'column' => 'release_on',
                    'name' => 'Release Hold On',
                    'xref' => 'wp_room.release_on',
                    'on' => [
                        'wp_gpxPreHold ON wp_room.record_id=wp_gpxPreHold.weekId AND wp_gpxPreHold.released=0',
                    ],
                ],
                'active_specific_date' => 'Active Date',
                'check_in_date' => 'Check In',
                'check_out_date' => 'Check Out',
                'price' => 'Price',
                'resort_name' => [
                    'type' => 'join',
                    'column' => 'ResortName',
                    'name' => 'Resort Name',
                    'xref' => 'wp_room.resort_name',
                    'where' => 'wp_resorts.ResortName',
                    'on' => [
                        'wp_resorts ON wp_room.resort=wp_resorts.id',
                    ],
                ],
                'resort_country' => [
                    'type' => 'join',
                    'column' => 'Country',
                    'name' => 'Country',
                    'xref' => 'wp_room.resort_country',
                    'on' => [
                        'wp_resorts ON wp_room.resort=wp_resorts.id',
                    ],
                ],
                'resort_state' => [
                    'type' => 'join',
                    'column' => 'Region',
                    'name' => 'State',
                    'xref' => 'wp_room.resort_state',
                    'on' => [
                        'wp_resorts ON wp_room.resort=wp_resorts.id',
                    ],
                ],
                'resort_city' => [
                    'type' => 'join',
                    'column' => 'Town',
                    'name' => 'City',
                    'xref' => 'wp_room.resort_city',
                    'on' => [
                        'wp_resorts ON wp_room.resort=wp_resorts.id',
                    ],
                ],

                'name' => [
                    'type' => 'join',
                    'column' => 'wp_unit_type.name',
                    'name' => 'Unit Type',
                    'xref' => 'wp_room.name',
                    'column_override' => 'name',
                    'on' => [
                        'wp_unit_type ON wp_unit_type.record_id=wp_room.unit_type',
                    ],
                ],
                'type' => [
                    'type' => 'case',
                    'column' => 'type',
                    'name' => 'Type',
                    'xref' => 'wp_room.type',
                    'case' => [
                        '1' => 'Exchange',
                        '2' => 'Rental',
                        '3' => 'Both',
                    ],
                ],
                'WeekType' => [
                    'type' => 'join_json',
                    'column' => 'data.WeekType',
                    'name' => 'Week Type',
                    'xref' => 'wp_room.WeekType',
                    'as' => 'WeekType',
                    'column_override' => 'WeekType',
                    'on' => [
                        'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id',
                    ],
                ],
                'source_num' => [
                    'type' => 'case',
                    'column' => 'source_num',
                    'name' => 'Source',
                    'xref' => 'wp_room.source_num',
                    'case' => [
                        '1' => 'Owner',
                        '2' => 'GPR',
                        '3' => 'Trade Partner',
                    ],
                ],
                'cancelledDate' => [
                    'type' => 'join',
                    'column' => 'wp_gpxTransactions.cancelledDate',
                    'name' => 'Transaction Cancelled Date',
                    'xref' => 'wp_room.cancelledDate',
                    'where' => 'wp_gpxTransactions.cancelledDate',
                    'on' => [
                        'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id',
                    ],
                ],
            ],
        ],
        'wp_credit' => [
            'table' => 'wp_credit',
            'name' => 'Credit',
            'groupBy' => 'wp_credit.id',
            'fields' => [
                'id' => 'ID',
                'created_date' => 'Timestamp',
                'credit_amount' => "Credit Banked",
                'credit_used' => 'Credit Used',
                'credit_expiration_date' => 'Expiration Date',
                'interval_number' => 'Interval',
                'unitinterval' => 'Unit Week',
                'resort_name' => 'Resort',
                'deposit_year' => 'Entitlement Year',
                'owner_id' => 'Member ID',
                'status' => 'Status',
                'memberFirstName' => [
                    'type' => 'usermeta',
                    'xref' => 'owner_id',
                    'column' => 'first_name',
                    'name' => 'Member First Name',
                    'key' => 'memberFirstName',
                ],
                'memberLastName' => [
                    'type' => 'usermeta',
                    'xref' => 'owner_id',
                    'column' => 'last_name',
                    'name' => 'Member Last Name',
                    'key' => 'memberLastName',
                ],
                'memberEmail' => [
                    'type' => 'usermeta',
                    'xref' => 'owner_id',
                    'column' => 'user_email',
                    'name' => 'Member Email',
                    'key' => 'memberEmail',
                ],
                'check_in_date' => 'Arrival Date',
                'extension_date' => 'Extension Date',
            ],
        ],
        'wp_partner' => [
            'table' => 'wp_partner',
            'name' => 'Partners',
            'fields' => [
                'record_id' => 'Partner ID',
                'create_date' => 'Timestamp',
                'name' => 'Account Name',
                'no_of_rooms_given' => 'Rooms Given',
                'no_of_rooms_received_taken' => 'Rooms Received',
                'trade_balance' => 'Trade Balance',
                'debit_balance' => 'Amount Due',
                'week_id' => [
                    'type' => 'join',
                    'column' => 'record_id',
                    'name' => 'Week ID',
                    'on' => [
                        'wp_room ON wp_room.source_partner_id=wp_partner.id',  // TODO: CONFIRM THIS WORKS!
                    ],
                    'xref' => 'wp_room.record_id',
                ],
                'check_in' => [
                    'type' => 'join',
                    'column' => 'check_in',
                    'name' => 'Check In',
                    //      'xref'=>'wp_room.check_in',
                    'where' => 'wp_room.check_in_date',
                    'on' => [
                        'wp_room ON wp_room.partner_id=wp_partner.id',
                    ],
                    'xref' => 'wp_partner.check_in',
                ],
                'ResortName' => [
                    'type' => 'join',
                    'column' => 'ResortName',
                    'name' => 'Resort Name',
                    'xref' => 'wp_resorts.ResortName',
                    'on' => [
                        'wp_room ON wp_room.partner_id=wp_partner.id',
                        'wp_resorts ON wp_room.resort=wp_resorts.id',
                    ],
                ],
                'guest_first_name' => [
                    'type' => 'join',
                    'column' => 'data.guest_last_name',
                    'name' => 'Guest First Name',
                    'on' => [
                        'wp_room ON wp_room.partner_id=wp_partner.id',
                        'wp_gpxTransactions ON wp_room.id=wp_gpxTransactions.weekId AND wp_gpxTransactions.cancelled=0',
                    ],
                    'xref' => 'wp_partner.guest_first_name',
                ],
                'guest_last_name' => [
                    'type' => 'join',
                    'column' => 'data.guest_last_name',
                    'name' => 'Guest Last Name',
                    'on' => [
                        'wp_room ON wp_room.partner_id=wp_partner.id',
                        'wp_gpxTransactions ON wp_room.id=wp_gpxTransactions.weekId AND wp_gpxTransactions.cancelled=0',
                    ],
                    'xref' => 'wp_partner.guest_last_name',
                ],
            ],
        ],
        'wp_gpxTransactions' => [
            'table' => 'wp_gpxTransactions',
            'name' => 'Transactions',
            'fields' => [
                'id' => 'ID',
                'transactionType' => 'Transaction Type',
                'cartID' => 'Cart ID',
                'sessionID' => 'Session ID',
                'userID' => 'User ID',
                //                      'resortID'=>'Resort ID',
                'resort_name' => [
                    'type' => 'join',
                    'column' => 'ResortName',
                    'name' => 'Resort Name',
                    'xref' => 'wp_gpxTransactions.resort_name',
                    'where' => 'wp_resorts.ResortName',
                    'on' => [
                        'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                        'wp_resorts ON wp_room.resort=wp_resorts.id',
                    ],
                ],
                'room_check_in_date' => [
                    'type' => 'join',
                    'column' => 'wp_room.check_in_date',
                    'name' => 'Inventory Check In',
                    'xref' => 'wp_gpxTransactions.room_check_in_date',
                    'column_override' => 'check_in_date',
                    'where' => 'wp_room.check_in_date',
                    'on' => [
                        'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                    ],
                ],
                'resort_city' => [
                    'type' => 'join',
                    'column' => 'Town',
                    'name' => 'Resort City',
                    'xref' => 'wp_gpxTransactions.resort_city',
                    'where' => 'wp_resorts.Town',
                    'on' => [
                        'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                        'wp_resorts ON wp_room.resort=wp_resorts.id',
                    ],
                ],
                'resort_state' => [
                    'type' => 'join',
                    'column' => 'Region',
                    'name' => 'Resort State',
                    'xref' => 'wp_gpxTransactions.resort_state',
                    'where' => 'wp_resorts.State',
                    'on' => [
                        'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                        'wp_resorts ON wp_room.resort=wp_resorts.id',
                    ],
                ],
                'resort_confirmation_number' => [
                    'type' => 'join',
                    'column' => 'resort_confirmation_number',
                    'name' => 'Resort Confirmation Number',
                    'xref' => 'wp_gpxTransactions.resort_confirmation_number',
                    'where' => 'wp_room.resort_confirmation_number',
                    'on' => [
                        'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                    ],
                ],
                'name' => [
                    'type' => 'join',
                    'column' => 'name',
                    'name' => 'Partner Name',
                    'xref' => 'wp_gpxTransactions.name',
                    'where' => 'wp_partner.name',
                    'on' => [
                        'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                        'wp_partner ON wp_room.source_partner_id=wp_partner.user_id',
                    ],
                ],
                'unitType' => [
                    'type' => 'join',
                    'column' => 'name',
                    'name' => 'Unit Type',
                    'xref' => 'wp_gpxTransactions.unitType',
                    'on' => [
                        'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                        'wp_unit_type ON wp_unit_type.record_id=wp_room.unit_type',
                    ],
                ],
                'inventoryType' => [
                    'type' => 'join_case',
                    'column' => 'source_num',
                    'name' => 'Inventory Type',
                    'on' => [
                        'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                    ],
                    'case' => [
                        '1' => 'Owner',
                        '2' => 'GPR',
                        '3' => 'Trade Partner',
                    ],
                    'xref' => 'wp_gpxTransactions.inventoryType',
                ],
                'weekId' => 'Week ID',
                'paymentGatewayID' => 'Payment Gateway ID',
                'sfData' => 'Salesforce Return Data',
                'check_in_date' => 'Check In Date',
                'Email' => [
                    'type' => 'usermeta',
                    'xref' => 'userID',
                    'column' => 'Email',
                    'name' => 'Member Email',
                    'key' => 'Email',
                ],
                'DayPhone' => [
                    'type' => 'usermeta',
                    'xref' => 'userID',
                    'column' => 'DayPhone',
                    'name' => 'Member Phone',
                    'key' => 'DayPhone',
                ],
                'address' => [
                    'type' => 'usermeta',
                    'xref' => 'userID',
                    'column' => 'address',
                    'name' => 'Member Address',
                    'key' => 'address',
                ],
                'city' => [
                    'type' => 'usermeta',
                    'xref' => 'userID',
                    'column' => 'city',
                    'name' => 'Member City',
                    'key' => 'city',
                ],
                'state' => [
                    'type' => 'usermeta',
                    'xref' => 'userID',
                    'column' => 'state',
                    'name' => 'Member State',
                    'key' => 'state',
                ],
                'country' => [
                    'type' => 'usermeta',
                    'xref' => 'userID',
                    'column' => 'country',
                    'name' => 'Member Country',
                    'key' => 'country',
                ],
                'data' => [
                    'type' => 'json',
                    'title' => 'Transaction Details',
                    'data' => [
                        'MemberNumber' => 'Member Number',
                        'MemberName' => 'Member Name',
                        'GuestName' => 'Guest Name',
//                            'Email'=>'Guest Email',
                        'Adults' => 'Adults',
                        'Children' => 'Children',
                        'UpgradeFee' => 'Upgrade Fee',
                        'CPO' => 'Flex Booking',
//                            'CPOFee'=>'CPO Fee',
                        'Paid' => 'Paid',
                        'WeekType' => 'Week Type',
//                            'resortName'=>'Resort Name',
                        'WeekPrice' => 'Week Price',
                        'Balance' => 'Balance',
                        'ResortID' => 'Resort ID',
                        'sleeps' => 'Sleeps',
                        'bedrooms' => 'Bedrooms',
                        'Size' => 'Size',
                        'noNights' => 'Number of Nights',
                        'checkIn' => 'Check In',
                        'processedBy' => 'Processed By ID',
                        'specialRequest' => 'Special Request',
                        'promoName' => 'Promo Name',
                        'discount' => 'Discount',
                        'coupon' => 'Coupon',
                        'couponDiscount' => 'Coupon Discount',
//                            'taxCharged'=>'Tax Charged',
                        'actWeekPrice' => 'Actual Week Price Paid',
                        'actcpoFee' => 'Actual Flex Fee Paid',
                        'actextensionFee' => 'Actual Extension Fee Paid',
                        'actguestFee' => 'Actual Guest Fee Paid',
                        'actupgradeFee' => 'Actual Upgrade Fee Paid',
                        'acttax' => 'Actual Tax Paid',
                    ],
                ],
                'agent' => [
                    'type' => 'agentname',
                    'from' => 'data.processedBy',
                    'column' => 'agent',
                    'name' => 'Processed By Name',
                    'xref' => 'wp_gpxTransations.agent',
                ],
                'datetime' => 'Timestamp',
                'cancelled' => [
                    'type' => 'case',
                    'column' => 'cancelled',
                    'name' => 'Cancelled',
                    'xref' => 'wp_gpxTransactions.cancelled',
                    'case' => [
                        '0' => 'No',
                        '1' => 'Yes',
                    ],
                ],
                'cancelledDate' => 'Transaction Cancelled Date',
                'cancelledData' => [
                    'type' => 'json_split',
                    'title' => 'Edit Details',
                    'cancelledData' => [
//                              'type'=>'Cancelled Type',
                        'action' => 'Cancelled Action',
                        'amount_sub' => 'Cancelled Amount Subtotal',
                        'amount' => 'Cancelled Amount',
                        'name' => 'Cancel Performed By',
//                              'date'=>'Cancel Date',
                    ],
                ],
            ],
        ],
    ];

    if ($return == 'tables') {
        $output = $tables;
    }

    return $output;
}

function gpx_run_report($id, $cron = null): array {
    global $wpdb;
    $data = [
        'reportid' => $id,
        'available_roles' => gpx_report_roles(),
    ];
    $groupBy = [];

    /*
     * 'rw' is an array that is used to identify how to handle each item that can be selected
     */
    $data['rw'] = gpx_report_writer('tables');

    $sql = $wpdb->prepare("SELECT * FROM wp_gpx_report_writer WHERE id=%s", $id);
    $row = $wpdb->get_row($sql);

    $data['reportHeadName'] = $row->name;

    $tds = json_decode($row->data);

    /*
     * get the details from the database and then build the query and tables
     */
    foreach ($tds as $td) {
        //does this table have a groupBy
        if (isset($extracted) && $data['rw'][$extracted[0]]['groupBy']) {
            $groupBy[] = $data['rw'][$extracted[0]]['groupBy'];
        }

        $data['th'][$td] = $td;
        $extracted = explode('.', $td);

        //do we have an "as" overwrite?
        if (isset($data['rw'][$extracted[0]]['fields'][$extracted[1]]['as'])) {
            $queryAs[$extracted[1]] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['as'];
        }

        //is this a joined table?
        $field = $data['rw'][$extracted[0]]['fields'][$extracted[1]];
        $type_query = $field['type'] ?? null;
        if ($type_query == 'join_json' || $type_query == 'join' || $type_query == 'join_case' || $type_query == 'join_usermeta') {
            foreach ($field['on'] as $jk => $joins) {
                /*
                 * $qj = query joins
                 */
                $qj[$joins] = $joins;
            }

            /*
             * $case = cases
             */
            $case[$td] = $field['case'] ?? null;
            $case_special[$td] = $field['case_special'] ?? null;
            $case_special_column[$td] = $field['column_special'];
            $tables[$extracted[0]][$extracted[1]] = $field['column'] ?? null;
            if (isset($field['column_override'])) {
                $tables[$extracted[0]][$extracted[1]] = $field['column_override'];
            }
            $queryData[$extracted[0]][$extracted[1]] = $field['column'];

        } elseif (($data['rw'][$extracted[0]]['fields'][$extracted[2]]['type'] ?? null) == 'post_merge') {
            $tables[$extracted[0]][$data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref']] = $field['xref'];

            foreach ($field['on'] as $jk => $joins) {
                /*
                 * $qj = query joins
                 */
                $qj[$joins] = $joins;
            }

        } elseif (($data['rw'][$extracted[0]]['fields'][$extracted[2]]['type'] ?? null) == 'agentname') {
            $tables[$extracted[0]][$data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref']] = $field['xref'];
            $queryData[$extracted[0]][$data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref']] = $field['xref'];
            $data['agentname'][$extracted[1]][$extracted[1]] = $field['from'];
        } elseif ($type_query == 'usermeta') {
            foreach ($field['on'] as $jk => $joins) {
                /*
                 * $qj = query joins
                 */
                $qj[$joins] = $joins;
            }
            $tables[$extracted[0]][$extracted[1]] = $extracted[1];
            $queryData[$extracted[0]][$extracted[1]] = $extracted[0] . "." . $extracted[1];
            $data['usermeta'][$extracted[1]] = $field['column'];
            $data['usermetaxref'][$extracted[1]] = $field['xref'];
            $data['usermetakey'][$extracted[1]] = $extracted[0] . "." . $extracted[1] . "." . $field['key'];
        } elseif (($data['rw'][$extracted[0]]['fields'][$extracted[2]]['type'] ?? null) == 'usermeta') {
            foreach ($data['rw'][$extracted[0]]['fields'][$extracted[2]]['on'] as $jk => $joins) {
                /*
                 * $qj = query joins
                 */
                $qj[$joins] = $joins;
            }


            $tables[$extracted[0]][$data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref']] = $data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref'];
            $queryData[$extracted[0]][$data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref']] = $data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref'];
            $data['usermeta'][$extracted[1]][$extracted[2]] = $data['rw'][$extracted[0]]['fields'][$extracted[2]]['column'];
            $data['usermetaxref'][$extracted[1]][$extracted[2]] = $data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref'];
            $data['usermetakey'][$extracted[1]][$extracted[2]] = $extracted[0] . "." . $extracted[1] . "." . $data['rw'][$extracted[0]]['fields'][$extracted[2]]['key'];
        } else {

            $tables[$extracted[0]][$extracted[1]] = $extracted[1];
            $queryData[$extracted[0]][$extracted[1]] = $extracted[0] . "." . $extracted[1];
            if (isset($extracted[2])) {
                $data['subfields'][$extracted[1]][$extracted[2]] = $extracted[2];
            }
        }
    }//end foreach

    //add the conditions
    $conditions = json_decode($row->conditions);

    foreach ($conditions as $condition) {
        switch ($condition->operator) {
            case "equals":
                $operator = "=";
                if (empty($condition->conditionValue)) {
                    $operator = 'IS';
                    $condition->conditionValue = 'NULL';
                } else {
                    $dt = date_parse($condition->conditionValue);
                    if ($dt['year'] > 0) {
                        $condition->conditionValue = $dt['year'] . "-" . $dt['month'] . "-" . $dt['day'];
                    }
                }
                break;
            case "not_equals":
                $operator = "!=";
                break;

            case "greater":
                $operator = ">";
                if ($dt = date_parse($condition->conditionValue)) {
                    $condition->conditionValue = $dt['year'] . "-" . $dt['month'] . "-" . $dt['day'];
                }
                break;

            case "less":
                $operator = "<";
                if ($dt = date_parse($condition->conditionValue)) {
                    $condition->conditionValue = $dt['year'] . "-" . $dt['month'] . "-" . $dt['day'];
                }
                break;

            case "like":
                $operator = "LIKE ";
                break;

            case 'yesterday':
                $operator = "BETWEEN ";
                $condition->conditionValue = date('Y-m-d 00:00:00', strtotime('yesterday')) . "' AND '" . date('Y-m-d 23:59:59', strtotime('yesterday'));
                break;

            case 'today':
                $operator = "BETWEEN ";
                $condition->conditionValue = date('Y-m-d 00:00:00') . "' AND '" . date('Y-m-d 23:59:59');
                break;

            case 'this_year':
                $operator = "BETWEEN";
                $condition->conditionValue = date('Y-01-01 00:00:00', strtotime('today')) . "' AND '" . date('Y-12-t 23:59:59', strtotime('today'));
                break;

            case 'last_year':
                $operator = "BETWEEN";
                $month_ini = new DateTime("first day of last year");
                $month_end = new DateTime("last day of last year");
                $condition->conditionValue = $month_ini->format('Y-m-d 00:00:00') . "' AND '" . $month_end->format('Y-m-d 23:59:59');
                break;

            case 'this_month':
                $operator = "BETWEEN";
                $condition->conditionValue = date('Y-m-1 00:00:00', strtotime('today')) . "' AND '" . date('Y-m-t 23:59:59', strtotime('today'));
                break;

            case 'last_month':
                $operator = "BETWEEN";
                $month_ini = new DateTime("first day of last month");
                $month_end = new DateTime("last day of last month");
                $condition->conditionValue = $month_ini->format('Y-m-d 00:00:00') . "' AND '" . $month_end->format('Y-m-d 23:59:59');
                break;

            case 'this_week':
                $operator = "BETWEEN";
                $condition->conditionValue = date('Y-m-d 00:00:00', strtotime('today 00:00:00')) . "' AND '" . date('Y-m-d 23:59:59', strtotime('+6 days 23:59:59'));
                break;

            case 'last_week':
                $operator = "BETWEEN";
                $condition->conditionValue = date('Y-m-d 00:00:00', strtotime('-6 days 00:00:00')) . "' AND '" . date('Y-m-d 23:59:59', strtotime('today 23:59:59'));
                break;

            default:
                $operator = "=";
                break;
        }
        $operand = '';
        if (isset($condition->operand)) {
            $operand = $condition->operand;
        }

        if ($operator == 'IS') {
            $wheres[] = $operand . " " . $condition->condition . " " . $operator . " " . $condition->conditionValue . "";
        } else {
            $wheres[] = $operand . " " . $condition->condition . " " . $operator . " '" . $condition->conditionValue . "'";
        }
        //if this is cancelled date then we also need to only show cancelled transactions
        if ($condition->condition == 'wp_gpxTransactions.cancelledDate') {
            if ($operator != 'IS') {
                $wheres['cancelledNotNull'] = " AND wp_gpxTransactions.cancelled = 1";
            }
        }
    }//end foreach conditions
    if (wp_doing_ajax() || !empty($cron)) {
        $i = 0;
        /*
         * $ajax = column labels and results
         */
        $ajax = [];

        foreach ($queryData as $tk => $td) {
            foreach ($td as $tdk => $tdv) {
                $colSelect = $tdv;

                $qq = explode('|', $tdv);
                if (count($qq) == 3) {
                    $td[$tdk] = $qq[1];
                    $colSelect = $qq[2];
                }

                $texp = explode('.', $tdv);
                if (count($texp) == 2) {
                    if ($texp[0] == 'data') {
                        $colSelect = $texp[0];
                    }
                    $td[$tdk] = $texp[1];
                }

                $as = $td[$tdk];
                if (isset($queryAs[$tdk])) {
                    $as = $queryAs[$tdk];
                }

                $tdas[] = $colSelect . " AS " . $as;
            }

            $sql = "SELECT " . implode(", ", $tdas) . " FROM " . $tk . " ";

            if (isset($qj)) {
                $sql .= " LEFT OUTER JOIN ";
                $sql .= implode(" LEFT OUTER JOIN ", $qj);
            }

            if (isset($wheres)) {
                $sql .= " WHERE " . implode(" ", $wheres);
            }
            if ($tk == 'wp_room' || $tk == 'wp_gpxTransactions') {
                if (isset($wheres)) {
                    $sql .= " AND wp_room.archived=0";
                } else {
                    $sql .= "WHERE wp_room.archived=0";
                }
            }

            if (!empty($groupBy)) {
                $sql .= ' GROUP BY ' . implode(", ", $groupBy);
            }
            // @TODO - fix this query
            $results = $wpdb->get_results($sql);
            if (isset($_REQUEST['sql_exit'])) {
                exit;
            }
            foreach ($results as $result) {
                foreach ($td as $tdK => $t) {
                    $ajax[$i][$tk . "." . $t] = $result->$t;

                    if ($tdK == 'source_partner_name') {
                        $ajax[$i]['wp_room.source_partner_name'] = $result->source_partner_name;
                    } elseif (isset($data['subfields'][$t]))//is this a regular field or is it json?
                    {
                        if (isset($data['rw'][$tk][$t]['type']) && $data['rw'][$tk][$t]['type'] == 'join') {
                            $co = $data['rw'][$tk][$t]['column'];
                            $ajax[$i][$tk . "." . $t] = $result->$co;
                            if (is_array($result->$co) || is_object($result->$co)) {
                                $ajax[$i][$tk . "." . $t] = implode(", ", (array) $result->$co);
                            }
                        } //this is json the result is a json
                        elseif (!isset($json[$t])) {
                            $json[$t] = json_decode($result->$t);
                        }
                        foreach ($data['subfields'][$t] as $st) {

                            if (gpx_validate_date($json[$t]->$st)) {
                                $json[$t]->$st = date('m/d/Y', strtotime($json[$t]->$st));
                            }
                            if (gpx_validate_date($json[$t]->$st, 'Y-m-d')) {
                                $json[$t]->$st = date('m/d/Y', strtotime($json[$t]->$st));
                            }

                            $ajax[$i][$tk . "." . $t . "." . $st] = $json[$t]->$st;

                            if ($t == 'cancelledData') {
                                $isCancelled = true;
                                $ti = 0;
                                $cdMark = $i;
                                $amountSum[$cdMark][] = 0;
                                $totJsonT = count((array) $json[$t]);

                                foreach ($json[$t] as $jsnt) {

                                    if (gpx_validate_date($jsnt->$st)) {
                                        $jsnt->$st = date('m/d/Y', strtotime($jsnt->$st));
                                    }
                                    if (gpx_validate_date($json[$t]->$st, 'Y-m-d')) {
                                        $jsnt->$st = date('m/d/Y', strtotime($jsnt->$st));
                                    }

                                    $zti = '';
                                    if ($ti > 0) {
                                        if ($st == 'amount') {
                                            $lastAjax = $ajax[$i];
                                            if (isset($lastAjax['wp_gpxTransactions.cancelledDate'])) {
                                                $lastAjax['wp_gpxTransactions.cancelledDate'] = date('m/d/Y', strtotime($lastAjax['wp_gpxTransactions.cancelledDate']));
                                            }
                                            $i++;
                                            $ajax[$i] = $lastAjax;
                                        }
                                    }
                                    $ti++;

                                    if ($jsnt->st != 0 && empty($jsnt->$st)) {
                                        continue;
                                    }

                                    if ($st == 'amount') {
                                        $ajax[$i][$tk . "." . $t . ".amount_sub"] = number_format($jsnt->$st, 2);

                                        $showAmount = '';
                                        $amountSum[$cdMark][] = $jsnt->$st;
                                        if ($ti === $totJsonT) {
                                            $showAmount = array_sum($amountSum[$cdMark]);
                                        }

                                        $jsnt->$st = number_format($showAmount, 2);
                                    }

                                    $ajax[$i][$tk . "." . $t . "." . $st] = $jsnt->$st;

                                }
                            } elseif (is_array($json[$t]->$st) || is_object($json[$t]->$st)) {
                                $ajax[$i][$tk . "." . $t . "." . $st] = implode(", ", (array) $json[$t]->$st);
                            }
                        }

                    } elseif (isset($data['rw'][$tk]['fields'][$tdK]['type']) && $data['rw'][$tk]['fields'][$tdK]['type'] == 'agentname') {
                        $from = $data['agentname'][$tk][$tdK];
                        $expFrom = explode('.', $from);

                        if (count($expFrom) == 1) {
                            $agentNum = $result->$expFrom[0];
                        } else {
                            $agentNum = $json[$expFrom[0]]->$expFrom[1];
                        }

                        $agentName = [];
                        $agentName['first'] = get_user_meta($agentNum, 'first_name', true);
                        $agentName['last'] = get_user_meta($agentNum, 'last_name', true);

                        $ajax[$i][$tk . "." . $t] = implode(" ", $agentName);
                    } elseif (isset($data['rw'][$tk]['fields'][$tdK]['type']) && $data['rw'][$tk]['fields'][$tdK]['type'] == 'case') {
                        $ajax[$i][$tk . "." . $t] = $data['rw'][$tk]['fields'][$tdK]['case'][$result->$t];
                        if (is_array($data['rw'][$tk]['fields'][$tdK]['case'][$result->$t]) || is_object($data['rw'][$tk]['fields'][$tdK]['case'][$result->$t])) {
                            $ajax[$i][$tk . "." . $t] = implode(", ", (array) $data['rw'][$tk]['fields'][$tdK]['case'][$result->$t]);
                        }
                    } elseif (isset($data['rw'][$tk]['fields'][$tdK]['type']) && $data['rw'][$tk]['fields'][$tdK]['type'] == 'join_json') {
                        $ajaxJson = json_decode($result->$t);
                        $ajax[$i][$tk . "." . $t] = stripslashes($ajaxJson->$t);
                    } elseif (isset($case[$tk . "." . $tdK])) {
                        $ajax[$i][$tk . "." . $t] = $case[$tk . "." . $tdK][$result->$t];
                    } elseif (isset($data['usermeta'][$t])) {
                        //this is usermeta -- get the results
                        foreach ($data['usermeta'][$t] as $ut) {
                            if ($t == 'userID' || $t == 'ownerID') {
                                foreach ($data['usermeta'][$t] as $umK => $umT) {
                                    if ($umT == $ut) {
                                        $akK = $umK;
                                        break;
                                    }
                                }
                                $ak = $data['usermetakey'][$t][$akK];

                            } else {
                                switch ($ut) {
                                    case 'first_name':
                                        $ak = 'wp_credit.owner_id.memberFirstName';
                                        break;
                                    case 'last_name':
                                        $ak = 'wp_credit.owner_id.memberLastName';
                                        break;
                                    case 'user_email':
                                        $ak = 'wp_credit.owner_id.memberEmail';
                                        break;
                                    case 'Email':
                                        $ak = 'wp_gpxTransactions.userID.Email';
                                        break;
                                    case 'DayPhone':
                                        $ak = 'wp_gpxTransactions.userID.DayPhone';
                                        break;
                                    case 'address':
                                        $ak = 'wp_gpxTransactions.userID.address';
                                        break;
                                    case 'city':
                                        $ak = 'wp_gpxTransactions.userID.city';
                                        break;
                                    case 'state':
                                        $ak = 'wp_gpxTransactions.userID.state';
                                        break;
                                    case 'country':
                                        $ak = 'wp_gpxTransactions.userID.country';
                                        break;
                                    default:
                                        $ak = '';
                                        break;
                                }
                            }
                            $ajax[$i][$ak] = get_user_meta($result->$t, $ut, true);

                            if (empty($ajax[$i][$ak])) {
                                //maybe this is the user object
                                $user_info = get_userdata($result->$t);
                                $ajax[$i][$ak] = $user_info->$ut;
                            }

                        }
                    } elseif (isset($data['usermeta_hold'][$t])) {
                        //this is usermeta -- get the results
                        $um = [];
                        foreach ($data['usermeta_hold'][$t] as $ut) {
                            $um[] = get_user_meta($result->$t, $ut, true);
                        }
                        if (!empty($um)) {
                            $ajax[$i][$ak] = implode(' ', $um);
                        }
                    } elseif (isset($case_special[$tk . "." . $tdK])) {
                        if ($data['rw'][$tk]['fields'][$tdK]['as']) {
                            $t = $data['rw'][$tk]['fields'][$tdK]['as'];
                        }
                        if (isset($case_special[$tk . "." . $tdK]['NULL']) && isset($case_special[$tk . "." . $tdK]['NOT NULL'])) {

                            if (is_null($result->$t)) {
                                $ajax[$i][$tk . "." . $t] = $case_special[$tk . "." . $tdK]['NULL'];
                            } else {
                                $ajax[$i][$tk . "." . $t] = $case_special[$tk . "." . $tdK]['NOT NULL'];
                            }
                        } else {
                            $ajax[$i][$tk . "." . $t] = $result->$t;
                        }
                    } else {

                        //is this an as
                        if (isset($queryAs[$tdK])) {
                            $t = $queryAs[$tdK];
                        }

                        $ajax[$i][$tk . "." . $t] = stripslashes($result->$t);


                        if (is_array($result->$t) || is_object($result->$t)) {
                            $ajax[$i][$tk . "." . $t] = implode(", ", (array) $result->$t);
                        }
                    }
                    unset($json[$t]);
                    $field = $data['rw'][$tk]['fields'][$tdK] ?? null;
                    if ($field['columns'] ?? null) {
                        $columnsCount = count($field['columns']['cols']);
                        foreach ($field['columns']['cols'] as $col) {
                            if (isset($ajax[$i][$col])) {
                                $maybeRemoveAjax[$i][] = $ajax[$i][$col];
                            }
                        }

                        for ($di = 0; $di < $columnsCount; $di++) {
                            if ($di > 0) {
                                $i++;
                            }

                            $ajax[$i][$data['rw'][$tk]['fields'][$tdK]['columns']['name']] = $maybeRemoveAjax[$i][$di];

                        }
                        unset($maybeRemoveAjax);
                    }
                }//end foreach columns
                foreach ($ajax[$i] as $ak => $av) {
                    if (gpx_validate_date($av)) {
                        $ajax[$i][$ak] = date('m/d/Y', strtotime($av));
                    }
                    if (gpx_validate_date($av, 'Y-m-d')) {
                        $ajax[$i][$ak] = date('m/d/Y', strtotime($av));
                    }
                }

                //rental weeks don't need credits
                if (isset($ajax[$i]['wp_room.WeekType'])
                    && strtolower(substr($ajax[$i]['wp_room.WeekType'], 0, 1)) == 'r'
                ) {
                    //credit add and credit subtract need to be 0
                    $ajax[$i]['wp_room.credit_subtract'] = 0;

                }

                //if isset partner name and isset both given and taken
                if (isset($ajax[$i]['wp_room.partner_name'])
                    && (isset($ajax[$i]['wp_room.source_partner_name']) && !empty($ajax[$i]['wp_room.source_partner_name']))
                    && (isset($ajax[$i]['wp_room.booked_by_partner_name']) && !empty($ajax[$i]['wp_room.booked_by_partner_name']))) {
                    //this row is given -- add name of given unset the -1 column
                    $ajax[$i]['wp_room.partner_name'] = $ajax[$i]['wp_room.source_partner_name'];
                    //set the temp column
                    $ajax[$i]['wp_room.temp_credit_subtract'] = $ajax[$i]['wp_room.credit_subtract'];
                    $ajax[$i]['wp_room.credit_subtract'] = 0;
                    //make new row with the taken -- add name of taken for this column
                    $oldAjax = $ajax[$i];
                    $i++;
                    $ajax[$i] = $oldAjax;
                    $ajax[$i]['wp_room.partner_name'] = $ajax[$i]['wp_room.booked_by_partner_name'];
                    //unset the +1 column
                    $ajax[$i]['wp_room.credit_add'] = 0;
                    //add credit subtract back in and then remove temp
                    $ajax[$i]['wp_room.credit_subtract'] = $ajax[$i]['wp_room.temp_credit_subtract'];
                    unset($ajax[$i]['wp_room.temp_credit_subtract']);
                }
                $i++;
            }//end foreach result
        }//end foreach querydata

        //if this is a trade partner then we also need adjustments
        if (isset($ajax[0]['wp_room.credit_add']) || isset($ajax[0]['wp_room.credit_subtract'])) {
            //this is a very specific report with specific conditions -- we alsways know that it could have a date range
            $getWheres = explode(' AND ', $wheres);
            foreach ($getWheres as $gw) {
                if (strpos($gw, 'wp_room.check_in_date') !== false) {
                    //replace the wp_room with updated_at
                    $newWheres[] = str_replace('wp_room.check_in_date', 'updated_at', $gw);
                }
            }
            $sql = "SELECT a.name, b.credit_add, b.credit_subtract, b.comments FROM wp_partner a
                            INNER JOIN wp_partner_adjustments b on b.partner_id=a.user_id";
            if (isset($newWheres)) {
                $sql .= ' WHERE ' . implode(" AND ", $newWheres);
            }
            // @TODO this query is not escaped
            $partners = $wpdb->get_results($sql);
            foreach ($partners as $partner) {

                $ajax[$i] = [
                    'wp_room.record_id' => 'adj',
                    'wp_room.partner_name' => $partner->name,
                    'wp_room.credit_add' => $partner->credit_add,
                    'wp_room.credit_subtract' => $partner->credit_subtract,
                    'wp_room.type' => 'Adjustment',
                    'wp_room.GuestName' => $partner->comments,
                ];
                $i++;
            }
        }

        if ($isCancelled) {
            $dk = '';
            foreach ($ajax as $ak => $av) {

                if ($av['wp_gpxTransactions.id'] == $dk) {
//                             this is a duplicate -- remove the last one
                    unset($ajax[$lk]);
                }

                $dk = $av['wp_gpxTransactions.id'];
                $lk = $ak;
            }
            sort($ajax);
        }

        if (!empty($cron)) {
            $reportSend = '';

            $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $row->name);
            $upload_dir = wp_upload_dir();
            // @TODO hardcoded file path, probably broken on new server
            $fileLoc = '/var/www/reports/' . $filename . '.csv';
            $file = fopen($fileLoc, 'w');

            $heads = array_keys($ajax[0]);

            $list = [];
            $list[] = implode(',', $heads);
            $i = 1;

            foreach ($ajax as $k => $itm) {
                foreach ($itm as $value) {
                    foreach ($heads as $head) {
                        $ordered[$i][] = $value[$head];
                    }
                }
                $list[$i] = implode(',', $ordered[$i]);
                $i++;
            }

            foreach ($list as $line) {
                fputcsv($file, explode(",", $line));

            }

            fclose($file);

            $subject = $row->name;
            $message = get_option('gpx_crreportsemailMessage');
            $fromEmailName = 'GPX Vacations';
            $fromEmail = get_option('gpx_crreportsemailFrom');
            $toEmail = $row->emailrecipients;

            $headers[] = "From: " . $fromEmailName . " <" . $fromEmail . ">";
            $headers[] = "Content-Type: text/html; charset=UTF-8";

            $attachments = [$fileLoc];

//                     wp_mail($toEmail, $subject, $message, $headers, $attachments);
        }//end if cron
        //if this is the trade balance report then only trade balance
        return $ajax;
    }//end if ajax
    return $data;
}

function gpx_report_write_send() {
    global $wpdb;

    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $sql = "SELECT id, emailrepeat FROM wp_gpx_report_writer WHERE emailrecipients != ''";
    $results = $wpdb->get_results($sql);

    $weekday = date('N');
    $day = date('l');
    $month = date('j');

    $data = [];
    foreach ($results as $result) {
        if (strtolower($day) == strtolower($result->emailrepeat)) {
            $run = true;
        } else {
            switch ($result->emailrepeat) {
                case 'Daily':
                    $run = true;
                    break;

                case 'Weekdays':
                    if ($weekday < 6) {
                        $run = true;
                    }
                    break;

                case 'Monthly':
                    if ($month == '1') {
                        $run = true;
                    }
                    break;
            }
        }

        if (isset($run)) {
            $data[] = $gpx->reportwriter($result->id, true);
        }
    }

    wp_send_json($data);
}

add_action('hook_cron_gpx_report_write_send', 'gpx_report_write_send');
add_action('wp_ajax_cron_grws', 'gpx_report_write_send');

function gpx_report_writer_table() {
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->reportwriter($_GET['id']);

    wp_send_json($data);
}

add_action('wp_ajax_gpx_report_writer_table', 'gpx_report_writer_table');

function gpx_retarget_report() {
    $start = gpx_request('startDate');
    if (!$start) {
        wp_redirect(gpx_admin_route('reports_retarget'));
    }

    $table = 'wp_cart';
    if (isset($_GET['table'])) {
        $table = $_GET['table'];
    }
    $column = 'data';
    if (isset($_GET['column'])) {
        $column = $_GET['column'];
    }

    global $wpdb;

    $fileLoc = tempnam(sys_get_temp_dir(), 'retarget.csv');
    $file = fopen($fileLoc, 'w');

    $heads = [];
    $values = [];

    $end = $_POST['endDate'];
    $bookingComplete = $_POST['bookingComplete'];

    $startDate = date('Y-m-d 00:00:00', strtotime($_POST['startDate']));

    $endDate = '2025-01-01 00:00:00';
    if (isset($_POST['endDate']) && !empty($_POST['endDate'])) {
        $endDate = date('Y-m-d 23:59:59', strtotime($_POST['endDate']));
    }

    $n = 0;
    $heads = [
        'sessionID',
        'cartID',
        'user_type',
        'daeMemberNo',
        'guest_name',
        'email',
        'action',
        'id',
        'price',
        'ResortName',
        'WeekType',
        'bedrooms',
        'weekId',
        'checkIn',
        'refDomain',
        'currentPage',
        'search_location',
        'search_month',
        'search_year',
        'timestamp',
    ];
    if ($_POST['bookingComplete'] == 'Yes') {
        $sql = $wpdb->prepare("SELECT * FROM wp_gpxTransactions WHERE datetime BETWEEN %s AND %s", [
            $startDate,
            $endDate,
        ]);
        $transactions = $wpdb->get_results($sql);
        foreach ($transactions as $transaction) {
            $userID = $transaction->userID;
            $sql = $wpdb->prepare("SELECT * FROM wp_gpxMemberSearch WHERE userID=%s", $userID);
            $rows = $wpdb->get_results($sql);
            foreach ($rows as $row) {
                $data = json_decode($row->data);
                foreach ($data as $sKey => $sValue) {
                    $user = get_userdata($userID);
                    $usermeta = (object) array_map(function ($a) {
                        return $a[0];
                    }, get_user_meta($userID));
                    $name = $usermeta->first_name . " " . $usermeta->last_name;
                    $name = str_replace(",", "", $name);
                    $splitKey = explode('-', $sKey);
                    if ($splitKey[0] == 'select') {
                        $values[$n]['sessionID'] = $row->sessionID;
                        $values[$n]['cartID'] = $row->cartID;
                        $values[$n]['action'] = 'select';
                        $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                        $values[$n]['guest_name'] = html_entity_decode($name);
                        $values[$n]['email'] = $user->user_email;
                        $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                        $values[$n]['refDomain'] = $sValue->refDomain;
                        $values[$n]['currentPage'] = $sValue->currentPage;
                        $values[$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->price);
                        $values[$n]['WeekPrice'] = $sValue->WeekPrice;
                        $values[$n]['id'] = $sValue->property->id;
                        $values[$n]['ResortName'] = $sValue->property->ResortName;
                        $values[$n]['WeekType'] = $sValue->property->WeekType;
                        $values[$n]['bedrooms'] = $sValue->property->bedrooms;
                        $values[$n]['weekId'] = $sValue->property->weekId;
                        $values[$n]['checkIn'] = date("m/d/Y", strtotime($sValue->property->checkIn));
                        $values[$n]['user_type'] = $sValue->user_type;
                    }
                    if ($splitKey[0] == 'view') {
                        $values[$n]['sessionID'] = $row->sessionID;
                        $values[$n]['cartID'] = $row->cartID;
                        $values[$n]['action'] = 'view';
                        $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                        $values[$n]['guest_name'] = html_entity_decode($name);
                        $values[$n]['email'] = $user->user_email;
                        $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                        $values[$n]['refDomain'] = $sValue->refDomain;
                        $values[$n]['currentPage'] = $sValue->currentPage;
                        $values[$n]['WeekType'] = $sValue->week_type;
                        $values[$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->price);
                        $values[$n]['id'] = $sValue->id;
                        $values[$n]['ResortName'] = $sValue->name;
                        $values[$n]['checkIn'] = date("m/d/Y", strtotime($sValue->checkIn));
                        $values[$n]['bedrooms'] = $sValue->beds;
                        $values[$n]['search_location'] = $sValue->search_location;
                        $values[$n]['search_month'] = $sValue->search_month;
                        $values[$n]['search_year'] = $sValue->search_year;
                        $values[$n]['user_type'] = $sValue->user_type;
                    }
                    if ($splitKey[0] == 'bookattempt') {
                        $values[$n]['sessionID'] = $row->sessionID;
                        $values[$n]['cartID'] = $row->cartID;
                        $values[$n]['action'] = 'bookattempt';
                        $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                        $values[$n]['guest_name'] = html_entity_decode($name);
                        $values[$n]['email'] = $user->user_email;
                        $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                        $values[$n]['WeekType'] = $sValue->Booking->WeekType;
                        $values[$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->Booking->AmountPaid);
                        $values[$n]['id'] = $sValue->$splitKey[1];
                        $values[$n]['weekId'] = $sValue->Booking->WeekID;
                        $values[$n]['user_type'] = $sValue->user_agent;
                    }
                    if ($splitKey[0] == 'resort') {
                        $values[$n]['sessionID'] = $row->sessionID;
                        $values[$n]['cartID'] = $row->cartID;
                        $values[$n]['action'] = 'resortview';
                        $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                        $values[$n]['guest_name'] = html_entity_decode($name);
                        $values[$n]['email'] = $user->user_email;
                        $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                        $values[$n]['ResortName'] = $sValue->ResortName;
                        $values[$n]['id'] = $sValue->id;
                        $values[$n]['search_location'] = $sValue->search_location;
                        $values[$n]['search_month'] = $sValue->search_month;
                        $values[$n]['search_year'] = $sValue->search_year;
                        $values[$n]['user_type'] = $sValue->user_type;
                    }
                    $n++;
                }
            }
        };
    } else {
        $sql = $wpdb->prepare("SELECT * FROM wp_gpxMemberSearch WHERE datetime BETWEEN %s AND %s", [
            $startDate,
            $endDate,
        ]);
        $rows = $wpdb->get_results($sql);
        foreach ($rows as $row) {
            $userID = $row->userID;
            $data = json_decode($row->data);
            foreach ($data as $sKey => $sValue) {
                $transactionID = '';
                $user = get_userdata($userID);
                $usermeta = (object) array_map(function ($a) {
                    return $a[0];
                }, get_user_meta($userID));
                $name = $usermeta->first_name . " " . $usermeta->last_name;
                $name = str_replace(",", "", $name);
                $splitKey = explode('-', $sKey);

                if ($splitKey[0] == 'bookattempt') {
                    $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s", $sValue->Booking->WeekID);
                    $transactionID = $wpdb->get_row($sql);
                    if (!empty($transactionID)) {
                        continue;
                    }
                    $values[$n]['sessionID'] = $row->sessionID;
                    $values[$n]['cartID'] = $row->cartID;
                    $values[$n]['action'] = 'bookattempt';
                    $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                    $values[$n]['guest_name'] = html_entity_decode($name);
                    $values[$n]['email'] = $user->user_email;
                    $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                    $values[$n]['WeekType'] = $sValue->WeekType;
                    $values[$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->paid);
                    $values[$n]['id'] = $sValue->$splitKey[1];
                    $values[$n]['weekId'] = $sValue->WeekID;
                    $values[$n]['user_type'] = $sValue->user_agent;
                }
                if ($splitKey[0] == 'select') {
                    $values[$n]['sessionID'] = $row->sessionID;
                    $values[$n]['cartID'] = $row->cartID;
                    $values[$n]['action'] = 'select';
                    $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                    $values[$n]['guest_name'] = html_entity_decode($name);
                    $values[$n]['email'] = $user->user_email;
                    $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                    $values[$n]['refDomain'] = $sValue->refDomain;
                    $values[$n]['currentPage'] = $sValue->currentPage;
                    $values[$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->price);
                    $values[$n]['WeekPrice'] = $sValue->WeekPrice;
                    $values[$n]['id'] = $sValue->property->id;
                    $values[$n]['ResortName'] = $sValue->property->ResortName;
                    $values[$n]['WeekType'] = $sValue->property->WeekType;
                    $values[$n]['bedrooms'] = $sValue->property->bedrooms;
                    $values[$n]['weekId'] = $sValue->property->weekId;
                    $values[$n]['checkIn'] = date("m/d/Y", strtotime($sValue->property->checkIn));
                    $values[$n]['user_type'] = $sValue->user_type;
                }
                if ($splitKey[0] == 'view') {
                    $values[$n]['sessionID'] = $row->sessionID;
                    $values[$n]['cartID'] = $row->cartID;
                    $values[$n]['action'] = 'view';
                    $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                    $values[$n]['guest_name'] = html_entity_decode($name);
                    $values[$n]['email'] = $user->user_email;
                    $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                    $values[$n]['refDomain'] = $sValue->refDomain;
                    $values[$n]['currentPage'] = $sValue->currentPage;
                    $values[$n]['WeekType'] = $sValue->week_type;
                    $values[$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->price);
                    $values[$n]['id'] = $sValue->id;
                    $values[$n]['ResortName'] = $sValue->name;
                    $values[$n]['checkIn'] = date("m/d/Y", strtotime($sValue->checkIn));
                    $values[$n]['bedrooms'] = $sValue->beds;
                    $values[$n]['search_location'] = $sValue->search_location;
                    $values[$n]['search_month'] = $sValue->search_month;
                    $values[$n]['search_year'] = $sValue->search_year;
                    $values[$n]['user_type'] = $sValue->user_type;
                }
                if ($splitKey[0] == 'resort') {
                    $values[$n]['sessionID'] = $row->sessionID;
                    $values[$n]['cartID'] = $row->cartID;
                    $values[$n]['action'] = 'resortview';
                    $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                    $values[$n]['guest_name'] = html_entity_decode($name);
                    $values[$n]['email'] = $user->user_email;
                    $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                    $values[$n]['ResortName'] = $sValue->ResortName;
                    $values[$n]['id'] = $sValue->id;
                    $values[$n]['search_location'] = $sValue->search_location;
                    $values[$n]['search_month'] = $sValue->search_month;
                    $values[$n]['search_year'] = $sValue->search_year;
                    $values[$n]['user_type'] = $sValue->user_type;
                }
                $n++;
            }
        }
    }
    $list = [];
    $list[] = implode(',', $heads);
    $i = 1;
    foreach ($values as $value) {
        $value = str_replace(",", "", $value);
        foreach ($heads as $head) {
            $ordered[$i][] = $value[$head];
        }
        $list[$i] = implode(',', $ordered[$i]);
        $i++;
    }
    foreach ($list as $line) {
        fputcsv($file, explode(",", $line));

    }
    fseek($file, 0);
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="retarget.csv"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($fileLoc));
    fpassthru($file);
    fclose($file);
    unlink($fileLoc);

    exit;
}

add_action("wp_ajax_gpx_retarget_report", "gpx_retarget_report");

function gpx_json_reports() {
    $table = 'wp_cart';
    if (isset($_GET['table'])) {
        $table = $_GET['table'];
    }
    $days = '10';
    if (isset($_GET['days'])) {
        $days = $_GET['days'];
    }
    global $wpdb;
    $datevar = 'datetime';
    if ($table == 'wp_gpxFailedTransactions') $datevar = 'date';
    $today = date('Y-m-d');
    $date = date('Y-m-d 00:00:00', strtotime("-" . $days . " day", strtotime($today)));
    $sql = $wpdb->prepare("SELECT * FROM " . gpx_esc_table($table) . " WHERE " . gpx_esc_table($datevar) . " >= %s", $date);
    if ($table == 'wp_gpxMemberSearch') {
        $results = $wpdb->get_results($sql);
        foreach ($results as $row) {
            $userID = $row->userID;
            $data = json_decode($row->data);
            foreach ($data as $sKey => $sValue) {
                $transactionID = '';
                $user = get_userdata($userID);
                $usermeta = (object) array_map(function ($a) {
                    return $a[0];
                }, get_user_meta($userID));
                $name = $usermeta->first_name . " " . $usermeta->last_name;
                $name = str_replace(",", "", $name);
                $splitKey = explode('-', $sKey);

                if ($splitKey[0] == 'bookattempt') {
                    $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s", $sValue->Booking->WeekID);
                    $transactionID = $wpdb->get_row($sql);
                    if (!empty($transactionID)) {
                        continue;
                    }
                    $rows['bookattempt'][$n]['sessionID'] = $row->sessionID;
                    $rows['bookattempt'][$n]['cartID'] = $row->cartID;
                    $rows['bookattempt'][$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                    $rows['bookattempt'][$n]['guest_name'] = html_entity_decode($name);
                    $rows['bookattempt'][$n]['email'] = $user->user_email;
                    $rows['bookattempt'][$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                    $rows['bookattempt'][$n]['WeekType'] = $sValue->WeekType;
                    $rows['bookattempt'][$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->paid);
                    $rows['bookattempt'][$n]['id'] = $sValue->$splitKey[1];
                    $rowsv[$n]['weekId'] = $sValue->WeekID;
                }
                if ($splitKey[0] == 'select') {
                    $rows['select'][$n]['sessionID'] = $row->sessionID;
                    $rows['select'][$n]['cartID'] = $row->cartID;
                    $rows['select'][$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                    $rows['select'][$n]['guest_name'] = html_entity_decode($name);
                    $rows['select'][$n]['email'] = $user->user_email;
                    $rows['select'][$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                    $rows['select'][$n]['refDomain'] = $sValue->refDomain;
                    $rows['select'][$n]['currentPage'] = $sValue->currentPage;
                    $rows['select'][$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->price);
                    $rows['select'][$n]['WeekPrice'] = $sValue->WeekPrice;
                    $rows['select'][$n]['id'] = $sValue->property->id;
                    $rows['select'][$n]['ResortName'] = stripslashes($sValue->property->ResortName);
                    $rows['select'][$n]['WeekType'] = $sValue->property->WeekType;
                    $rows['select'][$n]['bedrooms'] = $sValue->property->bedrooms;
                    $rows['select'][$n]['weekId'] = $sValue->property->weekId;
                    $rows['select'][$n]['checkIn'] = date("m/d/Y", strtotime($sValue->property->checkIn));
                }
                if ($splitKey[0] == 'view') {
                    $rows['view'][$n]['sessionID'] = $row->sessionID;
                    $rows['view'][$n]['cartID'] = $row->cartID;
                    $rows['view'][$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                    $rows['view'][$n]['guest_name'] = html_entity_decode($name);
                    $rows['view'][$n]['email'] = $user->user_email;
                    $rows['view'][$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                    $rows['view'][$n]['refDomain'] = $sValue->refDomain;
                    $rows['view'][$n]['currentPage'] = $sValue->currentPage;
                    $rows['view'][$n]['WeekType'] = $sValue->week_type;
                    $rows['view'][$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->price);
                    $rows['view'][$n]['id'] = $sValue->id;
                    $rows['view'][$n]['ResortName'] = stripslashes($sValue->name);
                    $rows['view'][$n]['checkIn'] = date("m/d/Y", strtotime($sValue->checkIn));
                    $rows['view'][$n]['bedrooms'] = $sValue->beds;
                    $rows['view'][$n]['search_location'] = $sValue->search_location;
                    $rows['view'][$n]['search_month'] = $sValue->search_month;
                    $rows['view'][$n]['search_year'] = $sValue->search_year;
                }
                if ($splitKey[0] == 'resort') {
                    $rows['resortview'][$n]['sessionID'] = $row->sessionID;
                    $rows['resortview'][$n]['cartID'] = $row->cartID;
                    $rows['resortview'][$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                    $rows['resortview'][$n]['guest_name'] = html_entity_decode($name);
                    $rows['resortview'][$n]['email'] = $user->user_email;
                    $rows['resortview'][$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                    $rows['resortview'][$n]['ResortName'] = stripslashes($sValue->ResortName);
                    $rows['resortview'][$n]['id'] = $sValue->id;
                    $rows['resortview'][$n]['search_location'] = $sValue->search_location;
                    $rows['resortview'][$n]['search_month'] = $sValue->search_month;
                    $rows['resortview'][$n]['search_year'] = $sValue->search_year;
                }
                $n++;
            }
        }
    } else {
        $rows = $wpdb->get_results($sql);
        foreach ($rows as $row) {
            $data = json_decode($row->data);
            unset($row->data);
            $row->data = $data;
        }
    }

    wp_send_json($rows);
}

add_action("wp_ajax_gpx_json_reports", "gpx_json_reports");
add_action("wp_ajax_nopriv_gpx_json_reports", "gpx_json_reports");

function gpx_get_csv_download($table, $column, $days = '', $email = '', $dateFrom = '', $dateTo = '') {
    global $wpdb;
    $joinedTbl = [];
    $mapPropertiesToRooms = [
        'id' => 'record_id',
        'checkIn' => 'check_in_date',
        'checkOut' => 'check_out_date',
        'Price' => 'price',
        'weekID' => 'record_id',
        'weekId' => 'record_id',
        'StockDisplay' => 'availability',
        'WeekType' => 'type',
        'noNights' => 'DATEDIFF(a.check_out_date, a.check_in_date)',
        'active_rental_push_date' => 'active_rental_push_date',
    ];
    $mapPropertiesToUnit = [
        'bedrooms' => 'number_of_bedrooms',
        'sleeps' => 'sleeps_total',
        'Size' => 'name',
    ];
    $mapPropertiesToResort = [
        'country' => 'Country',
        'region' => 'Region',
        'locality' => 'Town',
        'resortName' => 'ResortName',
    ];
    $mapPropertiesToResort = [
        'Country' => 'Country',
        'Region' => 'Region',
        'Town' => 'Town',
        'ResortName' => 'ResortName',
        'ImagePath1' => 'ImagePath1',
        'AlertNote' => 'AlertNote',
        'AdditionalInfo' => 'AdditionalInfo',
        'HTMLAlertNotes' => 'HTMLAlertNotes',
        'ResortID' => 'ResortID',
        'taxMethod' => 'taxMethod',
        'taxID' => 'taxID',
        'gpxRegionID' => 'gpxRegionID',
    ];

    $joinedTbl['roomTable'] = [
        'alias' => 'a',
        'table' => 'wp_room',
    ];
    $joinedTbl['unitTable'] = [
        'alias' => 'c',
        'table' => 'wp_unit_type',
    ];
    $joinedTbl['resortTable'] = [
        'alias' => 'b',
        'table' => 'wp_resorts',
    ];
    foreach ($mapPropertiesToRooms as $key => $value) {
        if ($key == 'noNights') {
            $joinedTbl['joinRoom'][] = $value . ' as ' . $key;
        } else {
            $joinedTbl['joinRoom'][] = $joinedTbl['roomTable']['alias'] . '.' . $value . ' as ' . $key;
        }
    }
    foreach ($mapPropertiesToUnit as $key => $value) {
        $joinedTbl['joinUnit'][] = $joinedTbl['unitTable']['alias'] . '.' . $value . ' as ' . $key;
    }
    foreach ($mapPropertiesToResort as $key => $value) {
        $joinedTbl['joinResort'][] = $joinedTbl['resortTable']['alias'] . '.' . $value . ' as ' . $key;
    }

    $where = '';
    if ($table == 'wp_cart') {
        $where .= ' WHERE datetime > "2017-12-31 23:59:59"';
        $where .= ' AND cartID != ""';
    }

    if ($table == 'wp_gpxMemberSearch') {
        $today = date('Y-m-d');
        $datefrom = date('Y-m-d 23:59:59', strtotime("-" . $days . " day", strtotime($today)));
        $where .= $wpdb->prepare(' WHERE datetime > %s', $datefrom);
    }
    if ($table == 'wp_specials') {
        $ids = explode("_", $days);
        $indIds = explode(",", $ids[2]);
        $where .= $wpdb->prepare(' WHERE id BETWEEN %s AND %s', [$ids[0], $ids[1]]);
        if (!empty($indIds)) {
            $placeholders = gpx_db_placeholders($indIds, '%s');
            $where .= $wpdb->prepare(" OR id in ({$placeholders})", array_values($indIds));
        }
    }

    $sql = "SELECT * FROM " . gpx_esc_table($table) . ' ' . $where . " ORDER BY `id`";

    if ($table == 'wp_gpxTransactions') {
        $select = "SELECT t.id as transactionID, t.*,
                        " . implode(', ', $joinedTbl['joinRoom']) . ",
                        " . implode(', ', $joinedTbl['joinResort']) . ",
                        " . implode(', ', $joinedTbl['joinUnit']) . ",
                        " . $joinedTbl['roomTable']['alias'] . ".record_id as PID, " . $joinedTbl['resortTable']['alias'] . ".id as RID";
        $from = " FROM wp_gpxTransactions t";
        $joinedTable[] = " LEFT OUTER JOIN " . $joinedTbl['roomTable']['table'] . " " . $joinedTbl['roomTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".resort=t.weekId";
        $joinedTable[] = " LEFT OUTER JOIN " . $joinedTbl['resortTable']['table'] . " " . $joinedTbl['resortTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".resort=" . $joinedTbl['resortTable']['alias'] . " .id";
        $joinedTable[] = " LEFT OUTER JOIN " . $joinedTbl['unitTable']['table'] . " " . $joinedTbl['unitTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".unit_type=" . $joinedTbl['unitTable']['alias'] . ".record_id";


        $wheres[] = ' WHERE cartID != ""';

        if (!empty($dateFrom)) {
            $wheres[] = ' t.datetime BETWEEN "' . date('Y-m-d 00:00:00', strtotime($dateFrom)) . '" AND "' . date('Y-m-d 23:59:59', strtotime($dateTo)) . '"';
        }
        $where = implode(" AND ", $wheres);
        $join = implode(" ", $joinedTable);
        $sql = $select . $from . $join . $where . " GROUP BY t.id ORDER BY t.id";
    }


    if ($table == 'wpmeta') {
        $headStart = [
            'DAEMemberNo',
            'AccountName',
            'FirstName1',
            'FirstName2',
            'LastName1',
            'LastName2',
            'DayPhone',
            'HomePhone',
            'Mobile',
            'Mobile1',
            'Mobile2',
            'Email',
            'Email',
            'Address1',
            'Address2',
            'Address3',
            'Address4',
            'Address5',
            'PostCode',
            'ResortShareID',
            'ResortMemeberID',
            'ReferalID',
            'OwnershipWeekType',
        ];
        $heads = $headStart;
        $args = [
            'role' => 'gpx_member',
        ];
        $allOwners = get_users($args);
        $aoi = 0;
        foreach ($allOwners as $ao) {
            foreach ($headStart as $meta) {
                $rows[$ao->ID][$meta] = get_user_meta($ao->ID, $meta, true);
            }
        }
    } else {
        $rows = $wpdb->get_results($sql);
    }

    $upload_dir = wp_upload_dir();
    $fileLoc = '/var/www/reports/' . $table . '.csv';
    $file = fopen($fileLoc, 'w');

    $heads = [];
    $values = [];
    $n = 0;
    if ($table == 'wp_cart') {
        $headStart = ['id', 'datetime'];
        $heads = $headStart;
    }
    if ($table == 'wp_gpxTransactions') {
        $headStart = [
            'transactionID',
            'datetime',
            'resortName',
            'weekId',
            'check_in_date',
            'noNights',
            'Size',
            'WeekType',
            'MemberNumber',
            'MemberName',
            'GuestName',
            'Adults',
            'Children',
            'CPO',
            'cancelled',
            'actWeekPrice',
            'actupgradeFee',
            'actcpoFee',
            'acttax',
            'actguestFee',
            'actextensionFee',
            'lateDepositFee',
            'specialRequest',
            'coupon',
            'couponDiscount',
            'promoName',
            'discount',
            'ownerCreditCouponID',
            'ownerCreditCouponAmount',
            'Paid',
            'refundaction',
            'refundamount',
        ];
        $heads = $headStart;
    }
    if ($table == 'wp_gpxMemberSearch') {
        $headStart = [
            'id',
            'datetime',
            'userID',
            'action',
            'id',
            'price',
            'ResortName',
            'WeekType',
            'bedrooms',
            'weekId',
            'checkIn',
            'refDomain',
            'search_location',
            'search_month',
            'search_year',
        ];
        $heads = $headStart;
    }
    if ($table == 'wp_specials') {
        $headStart = ['id', 'Name', 'first_name', 'last_name', 'emsID'];
        $heads = $headStart;
    }
    foreach ($rows as $row) {
        if ($table == 'wpmeta') {
            $values[$n] = $row;
        } else {
            $data = json_decode($row->$column);
        }

        if ($table == 'wpmeta') {
            //nothing
        } elseif ($table == 'wp_specials') {
            $n++;
            $z = 1;
            $specificCustomers = json_decode($data->specificCustomer);
            foreach ($specificCustomers as $customer) {
                //get their usermeta
                $values[$row->id . $n . $z]['id'] = $row->id;
                $values[$row->id . $n . $z]['Name'] = $row->Name;
                $values[$row->id . $n . $z]['first_name'] = get_user_meta($customer, 'first_name', true);
                $values[$row->id . $n . $z]['last_name'] = get_user_meta($customer, 'last_name', true);
                $values[$row->id . $n . $z]['emsID'] = get_user_meta($customer, 'DAEMemberNo', true);
                $z++;
            }
        } elseif ($table == 'wp_gpxMemberSearch') {
            foreach ($data as $sKey => $sValue) {
                $splitKey = explode('-', $sKey);
                if ($splitKey[0] == 'select') {
                    $values[$n]['id'] = $row->id;
                    $values[$n]['datetime'] = $row->datetime;
                    $values[$n]['action'] = 'select';
                    $values[$n]['action'] = 'select';
                    $values[$n]['userID'] = $row->userID;
                    $values[$n]['refDomain'] = $sValue->refDomain;
                    $values[$n]['currentPage'] = $sValue->currentPage;
                    $values[$n]['price'] = $sValue->price;
                    $values[$n]['WeekPrice'] = $sValue->WeekPrice;
                    $values[$n]['propertyID'] = $sValue->property->id;
                    $values[$n]['ResortName'] = stripslashes($sValue->property->ResortName);
                    $values[$n]['WeekType'] = $sValue->property->WeekType;
                    $values[$n]['bedrooms'] = $sValue->property->bedrooms;
                    $values[$n]['weekId'] = $sValue->property->weekId;
                    $values[$n]['checkIn'] = date("m/d/Y", strtotime($sValue->property->checkIn));
                }
                if ($splitKey[0] == 'view') {
                    $values[$n]['id'] = $row->id;
                    $values[$n]['datetime'] = $row->datetime;
                    $values[$n]['action'] = 'view';
                    $values[$n]['userID'] = $row->userID;
                    $values[$n]['refDomain'] = $sValue->refDomain;
                    $values[$n]['currentPage'] = $sValue->currentPage;
                    $values[$n]['WeekType'] = $sValue->week_type;
                    $values[$n]['price'] = $sValue->price;
                    $values[$n]['propertyID'] = $sValue->id;
                    $values[$n]['ResortName'] = stripslashes($sValue->name);
                    $values[$n]['checkIn'] = date("m/d/Y", strtotime($sValue->checkIn));
                    $values[$n]['bedrooms'] = $sValue->beds;
                    $values[$n]['search_location'] = $sValue->search_location;
                    $values[$n]['search_month'] = $sValue->search_month;
                    $values[$n]['search_year'] = $sValue->search_year;
                }
                if ($splitKey[0] == 'bookattempt') {
                    $values[$n]['id'] = $row->id;
                    $values[$n]['datetime'] = $row->datetime;
                    $values[$n]['action'] = 'bookattempt';
                    $values[$n]['userID'] = $row->userID;
                    $values[$n]['WeekType'] = $sValue->WeekType;
                    $values[$n]['price'] = $sValue->AmountPaid;
                    $values[$n]['propertyID'] = $sValue->$splitKey[1];
                    $values[$n]['weekId'] = $sValue->WeekID;
                }
                if ($splitKey[0] == 'resort') {
                    $values[$n]['id'] = $row->id;
                    $values[$n]['datetime'] = $row->datetime;
                    $values[$n]['action'] = 'resortview';
                    $values[$n]['userID'] = $row->userID;
                    $values[$n]['ResortName'] = stripslashes($sValue->ResortName);
                    $values[$n]['resortID'] = $sValue->id;
                    $values[$n]['search_location'] = $sValue->search_location;
                    $values[$n]['search_month'] = $sValue->search_month;
                    $values[$n]['search_year'] = $sValue->search_year;
                }
                if (is_numeric($splitKey[0])) {

                    $values[$n]['id'] = $row->id;
                    $values[$n]['datetime'] = $row->datetime;
                    $values[$n]['action'] = 'search';
                    $values[$n]['userID'] = $row->userID;
                    $values[$n]['search_location'] = $sValue->search->locationSearched->location;
                    $values[$n]['search_month'] = $sValue->search->locationSearched->select_month;
                    $values[$n]['search_year'] = $sValue->search->locationSearched->select_year;
                }

            }
        } else {

            if ($row->transactionType != 'booking' && empty($row->WeekType)) {
                $row->WeekType = $row->transactionType;
            }

            if ($table == 'wp_gpxTransactions') {
                $cxData = [];
                $refunded = [];
                $refundAmt = '';
                if (!empty($row->cancelledData)) {
                    $cxData = json_decode($row->cancelledData);
                    foreach ($cxData as $cx) {
                        $refunded[] = $cx->amount;
                        if ($cx->action == 'refund') {
                            $refundTypes['credit_card'] = 'credit card';
                        } else {
                            $refundTypes['credit'] = 'credit';
                        }
                    }
                    $row->refundamount = array_sum($refunded);
                    $row->refundaction = implode(", ", $refundTypes);
                }
                //                     if((isset($data->couponCode) && $data->couponCode == 'NULL'))
                //                     {
                //is this an auto coupon?
                $sql = $wpdb->prepare("SELECT user_id FROM wp_gpxAutoCoupon WHERE transaction_id=%s", $row->id);
                $sql = $wpdb->prepare("SELECT b.Name from wp_gpxAutoCoupon a
                                INNER JOIN wp_specials b on b.id=a.coupon_id
                                WHERE user_id = (SELECT user_id FROM wp_gpxAutoCoupon WHERE transaction_id=%s)", $row->id);
                $cname = $wpdb->get_row($sql);
                if (!empty($cname)) {
                    $data->AutoCoupon = $cname->Name;
                }
                //                     }
            }
            $dcnt = count($data);
            $hcnt = count($heads);
            if (isset($headStart)) {
                $hcnt -= count($headStart);
            }
            foreach ($headStart as $h) {
                if (is_array($row->$h) || is_object($row->$h)) {
                    $values[$n][$h] = implode(", ", (array) $row->$h);
                } elseif (isset($row->$h)) {
                    $values[$n][$h] = $row->$h;
                } elseif (isset($data->$h)) {
                    if (is_array($data->$h) || is_object($data->$h)) {
                        $values[$n][$h] = implode(", ", (array) $data->$h);
                    } else {
                        $values[$n][$h] = $data->$h;
                    }
                }
            }
        }
        $n++;
    }
    $list = [];
    $list[] = implode(',', $heads);
    $i = 1;

    foreach ($values as $value) {
        if ($table != 'wpmeta') {
            $value = str_replace(",", "", $value);
            foreach ($heads as $head) {
                if (is_object($value[$head])) {
                    $ordered[$i][] = '';
                }
                $ordered[$i][] = $value[$head];
            }
        } else {
            foreach ($heads as $head) {
                if (is_object($value[$head])) {
                    $ordered[$i][] = '';
                } else {
                    $value[$head] = str_replace(",", "", $value[$head]);
                    $ordered[$i][] = $value[$head];
                }
            }
        }
        $list[$i] = implode(',', $ordered[$i]);
        $i++;
    }
    foreach ($list as $line) {
        fputcsv($file, explode(",", $line));

    }
    fclose($file);

    return $fileLoc;
}

function gpx_csv_download() {
    $table = 'wp_cart';
    if (isset($_GET['table'])) {
        $table = $_GET['table'];
    }
    $column = 'data';
    if (isset($_GET['column'])) {
        $column = $_GET['column'];
    }
    $days = '60';
    if (isset($_GET['days'])) {
        $days = $_GET['days'];
    }
    $dateFrom = date('Y-m-d', strtotime('-2 days'));
    if (isset($_GET['datefrom'])) {
        $dateFrom = $_GET['datefrom'];
    }

    $dateTo = date('Y-m-d');
    if (!empty($_GET['dateto'])) {
        $dateTo = $_GET['dateto'];
    }
    $return = gpx_get_csv_download($table, $column, $days, '', $dateFrom, $dateTo);

    if (file_exists($return)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($return) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($return));
        readfile($return);
        exit;
    }
}

add_action("wp_ajax_gpx_csv_download", "gpx_csv_download");
add_action("wp_ajax_nopriv_gpx_csv_download", "gpx_csv_download");

/**
 *   Master Availability
 */
function gpx_report_availability() {
    $ma = new MasterAvailability();
    $ma->filter->dates(gpx_request('date-start'), gpx_request('date-end'));
    $data = $ma->run();
    wp_send_json($data);
}

add_action("wp_ajax_gpx_get_report_availability", "gpx_report_availability");
add_action("wp_ajax_nopriv_gpx_get_report_availability", "gpx_report_availability");

