<?php

use GPX\Repository\WeekRepository;
use GPX\Repository\TransactionRepository;

class GpxRetrieve {
    public string $uri;
    public string $dir;
    public array $daecred;
    public array $expectedMemberDetails;
    public array $expectedBookingDetails;
    public array $expectedPaymentDetails;
    public static ?GpxRetrieve $instance = null;

    public function __construct($uri = null, $dir = null) {
        $this->uri = plugins_url('', __FILE__) . '/api';
        $this->dir = trailingslashit(dirname(__FILE__));

        $this->daecred = [
            'action' => get_option('dae_ws_action'),
            'host' => get_option('dae_ws_host'),
            'AuthID' => get_option('dae_ws_authid'),
            'DAEMemberNo' => get_option('dae_ws_memberno'),
            'mode' => get_option('dae_ws_mode'),
        ];

        $this->expectedMemberDetails = [
            'MemberNo' => 'YES',
            'AccountName' => 'YES',
            'Address1' => 'YES',
            'Address2' => 'NO',
            'Address3' => 'YES',
            'Address4' => 'YES',
            'Address5' => 'YES',
            'BroadcastEmail' => 'NO',
            'DayPhone' => 'NO',
            'Email' => 'YES',
            'Email2' => 'YES',
            'Fax' => 'NO',
            'Salutation' => 'YES',
            'Title1' => 'YES',
            'Title2' => 'YES',
            'FirstName1' => 'YES',
            'FirstName2' => 'NO',
            'HomePhone' => 'YES',
            'LastName1' => 'YES',
            'LastName2' => 'NO',
            'MailName' => 'YES',
            'Mobile' => 'NO',
            'Mobile2' => 'NO',
            'NewsletterStatus' => 'YES',
            'PostCode' => 'YES',
            'Password' => 'NO',
            'ReferalID' => 'YES',
            'ExternalMemberNumber' => 'NO',
            'MailOut' => 'YES',
            'SMSStatus' => 'YES',
            'SMSNumber' => 'YES',
        ];

        $this->expectedBookingDetails = [
            'CreditWeekID',
            'WeekEndpointID',
            'WeekID',
            'GuestFirstName',
            'GuestLastName',
            'GuestEmailAddress',
            'Adults',
            'Children',
            'WeekType',
            'CPO',
            'AmountPaid',
            'DAEMemberNo',
            'ResortID',
            'CurrencyCode',
            'GuestAddress',
            'GuestTown',
            'GuestState',
            'GuestPostCode',
            'GuestPhone',
            'GuestMobile',
            'GuestCountry',
            'GuestTitle',
        ];
        $this->expectedPaymentDetails = [
            'DAEMemberNo',
            'Address',
            'PostCode',
            'Country',
            'Email',
            'CardHolder',
            'CardNo',
            'CCV',
            'ExpiryMonth',
            'ExpiryYear',
            'PaymentAmount',
            'CurrencyCode',
        ];
    }

    public static function instance(): GpxRetrieve {
        if (!self::$instance) {
            self::$instance = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
        }

        return self::$instance;
    }


    /** @deprecated */
    function DAEHoldWeek($pid, $cid, $emsid = "", $bookingrequest = "") {
        global $wpdb;


        $releasetime = date('Y-m-d H:i:s', strtotime('+24 hours'));

        //make sure that this owner can make a hold request
        if (empty($emsid)) {
            $emsid = gpx_get_member_number($cid);
        }
        $holds = WeekRepository::instance()->get_weeks_on_hold($emsid);

        $holdcount = 0;
        if (isset($holds[0])) {
            $holdcount = count($holds);
        } elseif (isset($holds['country'])) {
            //dae just returns an associative array when there is only one
            $holdcount = 1;
        }

        $sql = $wpdb->prepare("SELECT id, release_on FROM wp_gpxPreHold
                        WHERE user=%s AND propertyID=%s AND released=0", [$cid, $pid]);
        $row = $wpdb->get_row($sql);

        //return true if credits+1 is greater than holds
        if (1 > $holdcount) {
            //we're good we can continue holding this
        } else {
            $output = ['error' => 'too many holds', 'msg' => get_option('gpx_hold_error_message')];


            if (!empty($bookingrequest)) {
                //is this a new hold request
                //we dont' need to do anything here right now but let's leave it just in case
            } else {
                //since this isn't a booking request we need to return the error and prevent anything else from happeneing.
                if (empty($row)) {
                    return $output;
                }
            }
        }

        if (!empty($bookingrequest)) {
            //is this a new hold request
            $releasetime = date('Y-m-d H:i:s', strtotime('+' . get_option('gpx_hold_limt_time') . ' hours'));
            if (!empty($row)) {
                if ($row->release_on > $releasetime) {
                    if (strtotime($releasetime) > strtotime($row->release_on)) {
                        $releasetime = $row->release_on;
                    }
                }
                $wpdb->delete('wp_gpxPreHold', ['id' => $row->id]);
            }
        }

        $sql = $wpdb->prepare("SELECT WeekType, WeekEndpointID, weekId, WeekType FROM wp_properties WHERE id=%s", $pid);
        $row = $wpdb->get_row($sql);


        //As discussed on yesterday's call and again internally here amongst ourselves
        // we think the best number to show in the 'Exchange Summary' slot on the member dashboard
        //  be a formula that takes the total non-pending deposits and subtract out the Exchange weeks booked.
        //This will bypass the erroneous number being sent by DAE and not confuse the owner.


        $credit = 0;

        if (!isset($emsid)) {
            $msg = "Please login to continue;";
            $output = ['error' => 'memberno', 'msg' => $msg];

            return $output;
        } elseif (isset($row) && $row->WeekType == 'ExchangeWeek' && (!empty($credit) && $credit <= -1)) {
            $msg = 'You have already booked an exchange with a negative deposit.  All deposits must be processed prior to completing this booking.  Please wait 48-72 hours for our system to verify the transactions.';
        } else {
            // @TODO old custom request form
            // uses pid so it might work differently
            $msg = 'This property is no longer available! <a href="#" class="dgt-btn active book-btn custom-request" data-pid="' . $pid . '" data-cid="' . $cid . '">Submit Custom Request</a>';
        }
        $output = ['msg' => $msg, 'release' => $releasetime];

        return $output;
    }

    /** @deprecated */
    function retreive_map_dae_to_vest() {
        $mapPropertiesToRooms = [
            'id' => 'record_id',
            'checkIn' => 'check_in_date',
            'checkOut' => 'check_out_date',
            'Price' => 'price',
            'weekID' => 'record_id',
            'weekId' => 'record_id',
            'StockDisplay' => 'availability',
            'resort_confirmation_number' => 'resort_confirmation_number',
            'source_partner_id' => 'source_partner_id',
            'WeekType' => 'type',
            'noNights' => 'DATEDIFF(check_out_date, check_in_date)',
            'active' => 'active',
            'source_num' => 'source_num',
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
            'gprID' => 'gpxRegionID',

        ];
        $mapRoomToPartner = [
            '',
        ];


        $output['roomTable'] = [
            'alias' => 'a',
            'table' => 'wp_room',
        ];
        $output['unitTable'] = [
            'alias' => 'c',
            'table' => 'wp_unit_type',
        ];
        $output['resortTable'] = [
            'alias' => 'b',
            'table' => 'wp_resorts',
        ];
        foreach ($mapPropertiesToRooms as $key => $value) {
            if ($key == 'noNights') {
                $output['joinRoom'][] = $value . ' as ' . $key;
            } else {
                $output['joinRoom'][] = $output['roomTable']['alias'] . '.' . $value . ' as ' . $key;
            }
        }
        foreach ($mapPropertiesToUnit as $key => $value) {
            $output['joinUnit'][] = $output['unitTable']['alias'] . '.' . $value . ' as ' . $key;
        }
        foreach ($mapPropertiesToResort as $key => $value) {
            $output['joinResort'][] = $output['resortTable']['alias'] . '.' . $value . ' as ' . $key;
        }

        return $output;
    }

    /** @deprecated */
    function DAEReleaseWeek($inputMembers) {
        global $wpdb;

        $wpdb->update('wp_gpxPreHold', ['released' => 1], ['weekId' => $inputMembers['WeekID']]);
    }

    /**
     * @param $DAEMemberNo
     * @param $ExtMemberNo
     *
     * @return array
     * @deprecated
     */
    function DAEGetAccountDetails($DAEMemberNo = '', $ExtMemberNo = '') {
        return [];
    }

    /**
     * @param $MemberTypeID
     * @param $BusCatID
     *
     * @return array
     * @deprecated
     */
    function DAEGetUnitUpgradeFees($MemberTypeID, $BusCatID) {
        return [];
    }

    public function DAEGetWeekDetails(int $WeekID): ?stdClass {
        return WeekRepository::instance()->get_week_data($WeekID);
    }

    /** @deprecated */
    function DAEReIssueConfirmation($post) {
        return null;
    }

    /**
     * @deprecated
     *
     * entire function returns on 2nd line and not used.
     *
     */
    function DAEGetResortProfile($id, $gpxRegionID, $inputMembers, $update = '') {
        global $wpdb;

        $return = ['success' => 'Resort Updated!'];

        $data = [
            'functionName' => 'DAEGetResortProfile',
            'inputMembers' => $inputMembers,
            'return' => 'ResortProfile',
        ];


        $output['gpxRegionID'] = $gpxRegionID;
        if (empty($gpxRegionID)) {
            $output['gpxRegionID'] = 'NA';
        }

        $sql = $wpdb->prepare("SELECT * FROM wp_resorts WHERE ResortID=%s", $output['ResortID']);
        $updateorinsert = $wpdb->get_row($sql);

        if ((!empty($update) && $update == 1) || !empty($updateorinsert)) {
            $wpdb->update('wp_resorts', $output, ['id' => $id]);
        } elseif (!empty($update) && $update == 'insert') {
            $wpdb->insert('wp_resorts', $output);
            $return['id'] = $wpdb->insert_id;
        }

        return $return;
    }

    /** @deprecated */
    function missingDAEGetResortProfile($resortID, $endpointID) {
        return ['successs' => "there was an error!"];
    }

    /** @deprecated */
    function DAEGetResortInd() {
        return ['success' => true];
    }

    /** @deprecated */
    function addResortDetails() {
        return ['success' => 'Resort updated.'];
    }

    /** @deprecated */
    function microtime_float() {
        [$usec, $sec] = explode(" ", microtime());

        return ((float) $usec + (float) $sec);
    }
}
