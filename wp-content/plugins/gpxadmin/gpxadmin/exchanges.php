<?php

use GPX\Model\Credit;
use GPX\Model\PreHold;
use GPX\Model\Property;
use GPX\Model\UnitType;
use GPX\Model\Interval;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use GPX\Api\Salesforce\Salesforce;
use GPX\Repository\OwnerRepository;
use GPX\Repository\ResortRepository;
use GPX\Repository\CreditRepository;
use GPX\Repository\IntervalRepository;

function gpx_get_exchange_details( stdClass $week ) {
    $data = [
        'CPOFee' => $week->cpoFee ?? 0.00,
        'CPOPrice' => ! empty( $week->cpoFee ) ? $week->cpoFee : get_option( 'gpx_fb_fee' ),
        'error' => '',
        'alert' => '',
        'resortName' => $week->ResortName,
        'creditWeeks' => collect(),
        'ownerships' => [],
    ];
    if ( ! is_user_logged_in() ) {
        return $data;
    }
    $sf = Salesforce::getInstance();
    $cid = gpx_get_switch_user_cookie();
    $property = Property::where( 'weekId', '=', $week->weekId )->first();
    $agent = $cid != get_current_user_id();

    $credit = OwnerRepository::instance()->get_credits( $cid );

    if ( $property && $property->WeekType === 'ExchangeWeek' && $credit <= - 1 ) {
        $data['alert'] = 'You have already booked an exchange with a negative deposit.  All deposits must be processed prior to completing this booking.  Please wait 48-72 hours for our team to verify the transactions.';
        $data['error'] = 'nocredit';

        return $data;
    }
    if ( $week->WeekType === 'donation' && ! $credit ) {
        $data['error'] = 'deposit';

        return $data;
    }

    if ( ! $week->active ) {
        // is the week inactive because the current user put it on hold?
        if ( PreHold::forUser( $cid )->forWeek( $week->id )->released( false )->doesntExist() ) {
            //$data['alert'] = 'This week is no longer available!<br><a href="#" class="dgt-btn active book-btn custom-request" data-pid="' . $week->id . '" data-cid="' . $cid . '">Submit Custom Request</a>';
            $data['error'] = 'notavailable';
            return $data;
        }
    }
    $creditWeeks = CreditRepository::instance()->getOwnerCreditWeeks( $cid, $week->checkIn )->get();
    $creditWeeks = $creditWeeks->map(function(Credit $creditWeek) use ($week) {
        $creditWeek->upgradeFee = $creditWeek->calculateUpgradeFee( $week->bedrooms, $creditWeek->getCreditBedrooms(), $week->resortId );

        return $creditWeek;
    });
    $ownerships = IntervalRepository::instance()->get_member_ownerships( $cid );
    if ( $ownerships->isNotEmpty() ) {
        $query = sprintf(/** @lang text */ "SELECT
        Name, Property_Owner__c, Room_Type__c, Week_Type__c, Owner_ID__c, Contract_ID__c, GPR_Owner_ID__c, GPR_Resort__c,
        GPR_Resort_Name__c, Owner_Status__c, Resort_ID_v2__c, UnitWeek__c, Usage__c, Year_Last_Banked__c, Days_Past_Due__c, Delinquent__c
        FROM Ownership_Interval__c
        WHERE Contract_ID__c IN (%s) AND Contract_Status__c = 'Active'", $ownerships->map(fn( $ownership ) => "'{$ownership->contractID}'")->join(',') );
        $intervals = $sf->query( $query );
        $ownerships = $ownerships->map(function (Interval $ownership) use ( $agent, $week, $intervals ) {
            $interval = Arr::first( $intervals, fn( $interval ) => $interval->Contract_ID__c == $ownership->contractID );
            $ownership->creditbed = UnitType::getNumberOfBedrooms( $ownership->Room_Type__c );
            $ownership->Week_Type__c = null;
            $ownership->Contract_ID__c = null;
            if ( $interval ) {
                $ownership->Room_Type__c = $interval->Room_Type__c;
                $ownership->Delinquent__c = $interval->Delinquent__c;
                $ownership->Week_Type__c = $interval->Week_Type__c;
                $ownership->Contract_ID__c = $interval->Contract_ID__c;
                $ownership->Year_Last_Banked__c = $interval->Year_Last_Banked__c;
                $ownership->creditbed = UnitType::getNumberOfBedrooms( $interval->Room_Type__c );
            }
            $ownership->is_delinquent = $ownership->Delinquent__c === 'Yes';
            $ownership->defaultUpgrade = null;
            $ownership->creditWeek = $interval;
            $beds = UnitType::getNumberOfBedrooms( $week->bedrooms );
            if ( in_array( $week->ResortName, [
                    'Channel Island Shores',
                    'Hilton Grand Vacations Club at MarBrisa',
                    'RiverPointe Napa Valley',
                ] ) || empty( $ownership->creditbed ) ) {
                $ownership->defaultUpgrade = match ( $beds ) {
                    'st','studio' => [ 'studio' => 0, '1' => 0, '2' => 0, '3' => 0, ],
                    '1' => [ 'studio' => 85, '1' => 0, '2' => 0, '3' => 0, ],
                    '2' => [ 'studio' => 185, '1' => 185, '2' => 0, '3' => 0, ],
                    default => [ 'studio' => 0, '1' => 0, '2' => 0, '3' => 0, ],
                };
                $ownership->upgradeFee = 0;
            } else {
                $ownership->upgradeFee = Credit::calculateUpgradeFee( $beds, $ownership->creditbed );
            }
            $ownership->Year_Last_Banked__c = $ownership->Year_Last_Banked__c ?: $ownership->deposit_year ?? null;

            //if this is an agent then the minimum date can be up to a year ago
            $ownership->next_year = $agent ? date( 'Y-m-d', strtotime( "-2 years" ) ) : $ownership->nextyear;

            return $ownership;
        });
    }

    $data['creditWeeks'] = $creditWeeks;
    $data['ownerships'] = $ownerships;

    return $data;
}


/**
 * @deprecated
 * @return void
 */
function gpx_load_exchange_form() {
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $sf = Salesforce::getInstance();

    $data = ['html' => '', 'CPOFee' => ''];

    $time_start = microtime(true);

    if (is_user_logged_in()) {
        $exchangebooking = ' to use for this exchange booking';

        if ((empty($_GET['id']) || $_GET['id'] == 'undefined')) {
            $exchangebooking = '';
        }

        $sql = $wpdb->prepare("SELECT WeekType, WeekEndpointID, weekId, WeekType, checkIn, resortId  FROM wp_properties WHERE id=%s", $_GET['id']);
        $row = $wpdb->get_row($sql);

        $cid = gpx_get_switch_user_cookie();


        $wp_mapuser2oid = gpx_get_mapped_owner_by_cid($cid);

        $memberNumber = '';

        if (!empty($wp_mapuser2oid)) {
            $memberNumber = $wp_mapuser2oid->gpr_oid;
        }

        $agent = false;
        if ($cid != get_current_user_id()) {
            $agent = true;
        }


        //set the resort meta fees
        $rmFees = [
            'UpgradeFeeAmount' => [],
            'CPOFeeAmount' => [],
        ];
        if ($row) {
            $sql = $wpdb->prepare("SELECT * FROM wp_resorts_meta WHERE ResortID=%s", $row->resortId);

            $resortMetas = $wpdb->get_results($sql);


            foreach ($resortMetas as $rm) {
                //reset the resort meta items
                $rmk = $rm->meta_key;
                if ($rmArr = json_decode($rm->meta_value, true)) {
                    foreach ($rmArr as $rmdate => $rmvalues) {
                        $thisVal = '';
                        $rmdates = explode("_", $rmdate);
                        if (count($rmdates) == 1 && $rmdates[0] == '0') {
                            //do nothing
                        } else {
                            //check to see if the from date has started
                            if ($rmdates[0] < strtotime("now")) {
                                //this date has started we can keep working
                            } else {
                                //these meta items don't need to be used
                                continue;
                            }
                            //check to see if the to date has passed
                            if (isset($rmdates[1]) && ($rmdates[1] >= strtotime("now"))) {
                                //these meta items don't need to be used
                                continue;
                            } else {
                                //this date is sooner than the end date we can keep working
                            }
                            foreach ($rmvalues as $rmval) {
                                //do we need to reset any of the fees?
                                if (array_key_exists($rmk, $rmFees)) {
                                    //set this fee
                                    if ($rmk == 'UpgradeFeeAmount') {
                                        $upgradeAmount = $rmval;
                                    }
                                    if ($rmk == 'CPOFeeAmount') {
                                        $cpoFee = $rmval;
                                    }
                                }
                            }
                        }
                    }
                }
            } //end resort meta fees
        }

        $credit = OwnerRepository::instance()->get_credits($cid);
        $hidenext = '';

        $creditWeeks = gpx_get_member_deposits($cid);

        foreach ($creditWeeks as $cwK => $cw) {
            if ($cw->status == 'Approved' && $cw->credit_action == 'transferred') {
                unset($creditWeeks[$cwK]);
            }
        }

        if (isset($row) && $row->WeekType == 'ExchangeWeek' && (isset($credit) && !empty($credit) && $credit[0] <= -1)) {
            $data['error'] = 'You have already booked an exchange with a negative deposit.  All deposits must be processed prior to completing this booking.  Please wait 48-72 hours for our team to verify the transactions.';
            $html = "<h2>Exchange weeks are not available.</h2>";
        } elseif ($_GET['type'] === 'donation' && empty($credit)) {
            $html = '<div class="exchange-result exchangeNotOK">';
            $html .= '<h2>Ready to donate? <a href="#modal-deposit" class="dgt-btn deposit-modal" aria-label="Deposit Week">Deposit a week now</a> to get started</h2>';
            $html .= '</div>';
        } else {

            $html = '<div class="exchange-result exchangeOK">';
            $html .= '<h2>Exchange Credit</h2><p>';
            $html .= 'Our records indicate that you do not have a current deposit with GPX; however this exchange will be performed, in good faith, and in-lieu of a deposit/banking of a week. Please select Deposit A Week from your Dashboard after your booking is complete. If you have already deposited your week it can take up to 48-72 hours for our team to verify the transaction. Should GPX have questions we will contact you within 24 business hours. Please note: if a deposit cannot be completed in 5 business days this exchange transaction will be cancelled.';
            $html .= '</p></div>';

            $weekType = str_replace(" ", "", $_GET['weektype']);

            $weekDetails = $gpx->DAEGetWeekDetails($_GET['weekid']);
            $data['resortName'] = $weekDetails->ResortName ?? '';

            if ($weekDetails->active == '0')   //this is broken, $weekDetails[0]->active
            {
                //did this user put it on hold?
                $sql = $wpdb->prepare("SELECT id FROM wp_gpxPreHold WHERE user=%s and weekId=%s AND released=0", [
                    $cid,
                    $_GET['weekid'],
                ]);
                $row = $wpdb->get_row($sql);
                if (empty($row)) {
                    $data['error'] = 'This week is no longer available!<br><a href="#" class="dgt-btn active book-btn custom-request" data-pid="' . $_GET['id'] . '" data-cid="' . $cid . '">Submit Custom Request</a>';
                    $data['html'] = "<h2>This week is no longer available.</h2>";

                    wp_send_json( $data );
                }
            }

            if (!empty($creditWeeks)) {

                $html = '<hgroup>';
                $html .= '<h2>Exchange Credit</h2>';
                $html .= '<p>Choose an exchange credit' . $exchangebooking . '.</p>';
                $html .= '</hgroup>';
                $html .= '<ul id="exchangeList" class="exchange-list">';

                $beds = $weekDetails->bedrooms;

                $resortName = $weekDetails->ResortName;

                $i = 1;
                foreach ($creditWeeks as $creditWeek) {
                    $creditWeek->Room_Type__c = $creditWeek->unit_type;
                    $checkindate = strtotime($weekDetails->checkIn);
                    $bankexpiredate = strtotime($creditWeek->credit_expiration_date);
                    //if this expired and can't be extended
                    if ($checkindate > $bankexpiredate && !empty($creditWeek->extension_date)) {
                        continue;
                    }
                    $selects = [
                        'Name',
                        'Property_Owner__c',
                        'Room_Type__c',
                        'Week_Type__c',
                        'Owner_ID__c',
                        'Contract_ID__c',
                        'GPR_Owner_ID__c',
                        'GPR_Resort__c',
                        'GPR_Resort_Name__c',
                        'Owner_Status__c',
                        'Resort_ID_v2__c',
                        'UnitWeek__c',
                        'Usage__c',
                        'Year_Last_Banked__c',
                        'Days_Past_Due__c',
                        'Delinquent__c',
                        'ROID_Key_Full__c',
                    ];


                    //If an owner is booking an exchange that has an arrival date after the expiration date of the exchange they are booking against we need to prevent the booking and present verbiage to the owner that in order to complete the transaction they must pay a credit extension fee or deposit/select a different week to book against.
                    $expired = '';
                    $expiredclass = '';
                    $expireddisabled = '';
                    $expiredFee = '';
                    if (isset($weekDetails->checkIn)) {
                        //$bankingYear = date('m/d/'.$creditWeek->BankingYear);
                        //$bankexpiredate = strtotime($bankingYear. '+ 2 years');
                        //$missingExpiryMessage = 'Please note:  A credit extension may be required for this booking.  An representative will advise if necessary.';
                        if ($checkindate > $bankexpiredate && (!empty($_GET['id']) && $_GET['id'] != 'undefined')) {
                            $expired = 'In order to complete the transaction you must pay a credit extension fee or deposit/select a different week to book against.<br><br><button class="btn btn-primary pay-extension" data-tocart="no-redirect">Add Fee To Cart</button>';
                            $expiredclass = 'expired';
                            $expireddisabled = 'disabled';
                            $expiredFee = get_option('gpx_extension_fee');
                        } elseif ($checkindate > $bankexpiredate && empty($exchangebooking)) {
                            continue;
                        } else {
                            if ($creditWeek->Delinquent__c != 'No') {
                                $expired = 'Please contact us at <a href=\"tel:+18775667519\">(877) 566-7519</a> to use this deposit.';
                                $expiredclass = 'expired';
                                $expireddisabled = 'disabled';
                            }
                        }
                    }


                    if (empty($creditWeek->Room_Type__c)) {
                        $utcb = explode("/", $creditWeek->unit_type);
                        //	$creditWeek->Room_Type__c = str_replace("b", "", $utcb);
                        $creditWeek->Room_Type__c = str_replace("b", "", $utcb[0]);
                    }

                    if (strpos(strtolower($creditWeek->Room_Type__c), '2br') !== false) {
                        $creditbed = '2';
                    } elseif (strpos(strtolower($creditWeek->Room_Type__c), '1br') !== false) {
                        $creditbed = '1';
                    } elseif (strpos(strtolower($creditWeek->Room_Type__c), '2') !== false) {
                        $creditbed = '2';
                    } elseif (strpos(strtolower($creditWeek->Room_Type__c), 'std') !== false) {
                        $creditbed = 'studio';
                    } elseif (strpos(strtolower($creditWeek->Room_Type__c), 'st') !== false) {
                        $creditbed = 'studio';
                    } elseif (strpos(strtolower($creditWeek->Room_Type__c), '1') !== false) {
                        $creditbed = '1';
                    } else {
                        $creditbed = $creditWeek->Room_Type__c;
                    }
                    //from 2 - 3 upgrade fee is 185
                    switch (strtolower($creditbed)) {
                        case 'studio':
                            if (strpos(strtolower($beds), 'std') !== false) {
                                $upgradeFee = '0';
                            } elseif (strpos(strtolower($beds), 'htl') !== false) {
                                $upgradeFee = '0';
                            } elseif (strpos(strtolower($beds), '1') !== false) {
                                $upgradeFee = '85';
                            } else {
                                $upgradeFee = '185';
                            }
                            break;

                        case '1':
                            //This is only the case at Carlsbad Inn Beach Resort.  Owners who have a 1 Bedroom Sleeps 6 unit type can upgrade to a 2 bedroom with no upgrade fee.
                            if (strpos(strtolower($beds), 'st') !== false
                                || strpos(strtolower($beds), '1') !== false
                                || (($creditWeek->Resort_ID_v2__c ?? null) == 'CBI' && strpos(strtolower($beds), '2') !== false)) {
                                $upgradeFee = '0';
                            } else {
                                $upgradeFee = '185';
                            }
                            break;

                        case '2':
                            //This is only the case at Carlsbad Inn Beach Resort.  Owners who have a 1 Bedroom Sleeps 6 unit type can upgrade to a 2 bedroom with no upgrade fee.
                            if (strpos(strtolower($beds), 'std') !== false
                                || strpos(strtolower($beds), 'htl') !== false
                                || strpos(strtolower($beds), '1') !== false
                                || strpos(strtolower($beds), '2') !== false
                                || ($creditWeek->Resort_ID_v2__c == 'CBI'
                                    && strpos(strtolower($beds), '3') !== false)) {
                                $upgradeFee = '0';
                            } else {
                                $upgradeFee = '185';
                            }
                            break;

                        default:
                            $upgradeFee = '0';
                            break;
                    }
                    if ($upgradeFee > 0 && isset($upgradeAmount)) {
                        $upgradeFee = $upgradeAmount;
                    }
                    $html .= '<li class="exchange-item">';
                    $html .= '<div class="w-credit">';
                    $html .= '<div class="head-credit ' . $expireddisabled . '">';
                    $html .= '<input type="checkbox" class="exchange-credit-check if-perks-credit" id="rdb-credit-' . $i . '" value="' . $upgradeFee . '" name="radio[' . $i . '][]" data-creditexpiredfee="' . $expiredFee . '" data-creditweekid="' . $creditWeek->id . '" ' . $expireddisabled . '>';
                    $html .= '<label for="rdb-credit-' . $i . '">Apply Credit</label>';
                    $html .= '</div>';
                    $html .= '<div class="cnt-credit">';
                    $html .= '<ul>';
                    $html .= '<li>';
                    $html .= '<p><strong>' . $creditWeek->resort_name . '</strong></p>';
                    if (!empty($creditWeek->CreditWeekID)) {
                        $html .= '<p>' . $creditWeek->CreditWeekID . '</p>';
                    }
                    $html .= '</li>';
                    $html .= '<li>';
                    $html .= '<p><strong>Expires:</strong></p>';
                    $html .= '<span> ';
                    if (isset($pendingReview)) {
                        $html .= $pendingReview;
                    } elseif (isset($creditWeek->credit_expiration_date)) {
                        $html .= $creditWeek->credit_expiration_date;
                    }
                    $html .= '</span>';
                    $html .= '</li>';
                    $html .= '<li>';
                    $html .= '<p><strong>Entitlement Year:</strong> ' . $creditWeek->deposit_year . '</p>';
                    $html .= '</li>';
                    $html .= '<li>';
                    $html .= '<p><strong>Size:</strong> ' . $creditWeek->unit_type . '</p>';
                    $html .= '</li>';
                    if ($upgradeFee > 0 && !empty($exchangebooking)) {
                        $html .= '<li>';
                        $html .= '<p>Please note: This booking requires an upgrade fee</p>';
                        $html .= '</li>';
                    }
                    if (isset($expired) && !empty($expired)) {
                        $html .= '<li>';
                        $html .= '<p>' . $expired . '</p>';
                        $html .= '<input type="hidden" name="expired-fee" class="expired-fee" value="' . $expiredFee . '" />';
                        $html .= '</li>';
                    } elseif (isset($missingExpiryMessage)) {
                        $html .= '<li>';
                        $html .= '<p>' . $missingExpiryMessage . '</p>';
                        $html .= '</li>';
                    }
                    $html .= '</ul>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</li>';
                    $i++;
                }
                $html .= '</ul>';
                $html .= '<p style="font-size: 18px; margin-top: 35px;">Don\'t see the credit you want to use?  <a href="#useDeposit" class="toggleElement use-deposit" style="color: #009ad6;">Click here</a> to <span id="showhidetext">show</span> additional weeks to deposit and use for this booking.</p>';
                $hidenext = 'style = "display: none; margin-top: 35px;"';

            }
            $ownerships = IntervalRepository::instance()->get_member_ownerships($cid);
//                         echo '<pre>'.print_r($ownerships, true).'</pre>';
            $html .= '<div id="useDeposit" ' . $hidenext . '>';
            $html .= '<hgroup>';
            $html .= '<h2>Use New Deposit</h2>';
            $html .= '<p>Select the week you would like to deposit as credit for this exchange.</p>';
            $html .= '</hgroup>';
            $html .= '<form name="exchangendeposit" id="exchangendeposit">';
            $html .= '<ul id="exchangeList" class="exchange-list deposit-bank-boxes" style="text-align: center;">';

            $beds = $weekDetails->bedrooms;

            $resortName = $weekDetails->ResortName;


            $i = 1;
            if (!empty($ownerships)) {

                foreach ($ownerships as $ownership) {
                    $query = $wpdb->prepare(/** @lang sfquery */ "SELECT
                                    Name,Property_Owner__c,Room_Type__c,Week_Type__c,Owner_ID__c, Contract_ID__c,
                                    GPR_Owner_ID__c, GPR_Resort__c, GPR_Resort_Name__c, Owner_Status__c,
                                    Resort_ID_v2__c, UnitWeek__c, Usage__c, Year_Last_Banked__c, Days_Past_Due__c,
                                    Delinquent__c, ROID_Key_Full__c
                            FROM Ownership_Interval__c
                            WHERE Contract_ID__c = %s AND Contract_Status__c='Active'", $ownership['contractID']);

                    $creditWeeks = $sf->query($query);
                    if (!$creditWeeks) {
                        // the ownership was in the database but not in salesforce, skip it
                        continue;
                    }
                    $creditWeek = $creditWeeks[0]->fields;

                    /*
                             * todo:  add exceptions for room_type
                             */
                    if (strpos(strtolower($creditWeek->Room_Type__c), '2br') !== false) {
                        $creditbed = '2';
                    } elseif (strpos(strtolower($creditWeek->Room_Type__c), '1br') !== false) {
                        $creditbed = '1';
                    } elseif (strpos(strtolower($creditWeek->Room_Type__c), '2') !== false) {
                        $creditbed = '2';
                    } elseif (strpos(strtolower($creditWeek->Room_Type__c), 'std') !== false) {
                        $creditbed = 'studio';
                    } elseif (strpos(strtolower($creditWeek->Room_Type__c), '1') !== false) {
                        $creditbed = '1';
                    } else {
                        $creditbed = $creditWeek->Room_Type__c;
                    }

                    $selectUnit = [
                        'Channel Island Shores',
                        'Hilton Grand Vacations Club at MarBrisa',
                        'RiverPointe Napa Valley',
                    ];

                    if ((isset($result) && in_array($result->ResortName, $selectUnit)) || empty($creditbed)) {

                        $defaultUpgrade = [
                            'st' => '0',
                            '1' => '0',
                            '2' => '0',
                            '3' => '0',
                        ];
                        switch (strtolower($beds)) {
                            case 'st':
                                $defaultUpgrade = [
                                    'st' => '0',
                                    '1' => '0',
                                    '2' => '0',
                                    '3' => '0',
                                ];
                                break;

                            case '1':
                                $defaultUpgrade = [
                                    'st' => '85',
                                    '1' => '0',
                                    '2' => '0',
                                    '3' => '0',
                                ];
                                break;

                            case '2':
                                $defaultUpgrade = [
                                    'st' => '185',
                                    '1' => '185',
                                    '2' => '0',
                                    '3' => '0',
                                ];
                                break;
                        }
                    } else {
                        switch (strtolower($creditbed)) {
                            case 'studio':
                                if (strpos(strtolower($beds), 'st') !== false) {
                                    $upgradeFee = '0';
                                } elseif (strpos(strtolower($beds), '1') !== false) {
                                    $upgradeFee = '85';
                                } else {
                                    $upgradeFee = '185';
                                }
                                break;

                            case 'hotel':
                                if (strpos(strtolower($beds), 'st') !== false) {
                                    $upgradeFee = '0';
                                } elseif (strpos(strtolower($beds), '1') !== false) {
                                    $upgradeFee = '85';
                                } else {
                                    $upgradeFee = '185';
                                }
                                break;

                            case '1':
                                //This is only the case at Carlsbad Inn Beach Resort.  Owners who have a 1 Bedroom Sleeps 6 unit type can upgrade to a 2 bedroom with no upgrade fee.

                                /*
This code is completely broken

                                        if(strpos(strtolower($beds), 'st') !== false
                                        || strpos(strtolower($beds), '1') !== false
                                        || ($creditWeek->Resort_ID_v2__c == 'CBI' && strpos(strtolower($beds), '2') !== false))
*/
                                //                                         || ($creditWeek->Resort_ID_v2__c == 'Carlsbad Inn Beach Resort' && strpos(strtolower($beds), '2') !== false && $resortName == 'Carlsbad Inn Beach Resort'))


                                // allow users that have are upgrading from  a 1 br  / 6 to a 2br don't pay upgrade

                                // if carlsbad && 1 credit && 6 beds
                                if ($creditWeek->ROID_Key_Full__c == "CBI99472142994721425223110A" and
                                    $creditWeek->credit_amount == '1' and
                                    $beds = '6' and
                                    $weekType = "ExchangeWeek" and
                                    $weekDetails->sleeps > 5
                                ) {
                                    $upgradeFee = '0';
                                } else {
                                    $upgradeFee = 185;
                                }
                                break;

                            default:
                                $upgradeFee = '0';
                                break;
                        }
                    }
                    if ($upgradeFee > 0 && isset($upgradeAmount)) {
                        $upgradeFee = $upgradeAmount;
                    }

                    $yearbankded = '';
                    $ownershipType = '';

                    if (!empty($ownership['Year_Last_Banked__c'])) {
                        $yearbankded = $ownership['Year_Last_Banked__c'] + 1;
                        $nextyear = '1/1/' . $yearbankded;
                    } elseif (!empty($ownership['deposit_year'])) {
                        $yearbankded = $ownership['deposit_year'] + 1;
                        $nextyear = '1/1/' . $yearbankded;
                        $ownership['Year_Last_Banked__c'] = $ownership['deposit_year'];
                    } else {
                        $nextyear = date('m/d/Y', strtotime('+14 days'));
                    }

                    //if this is an agent then the minimum date can be up to a year ago
                    if ($agent) {
                        $nextyear = date('m/d/Y', strtotime("-2 years"));
                    }


                    //if this is delinquent then don't allow the deposit
                    $delinquent = '';

                    if ($creditWeek->Delinquent__c == 'Yes') {
                        $delinquent = "<strong>Please contact us at <a href=\"tel:+18775667519\">(877) 566-7519</a> to use this deposit.</strong>";
                    }

                    $html .= '<li>';
                    $html .= '<div class="bank-row">';
                    $html .= '<input type="checkbox" class="exchange-credit-check if-perks-ownership" id="rdb-credit-' . $i . '" value="' . $upgradeFee . '" name="radio[' . $i . '][]" data-creditweekid="deposit">';
                    $html .= '</div>';
                    $html .= '<div class="bank-row">';
                    $html .= '<h3>' . $ownership['ResortName'] . '</h3>';
                    $html .= '</div>';

                    if (!empty($delinquent)) {
                        $html .= '<div class="bank-row" style="margin: margin-bottom: 20px;">' . $delinquent . '</div>';
                    } else {
                        $html .= '<div class="bank-row">';
                        $html .= '<span class="dgt-btn bank-select">Select</span>';
                        $html .= '</div>';
                        $html .= '<input type="hidden" name="Year" class="disswitch" disabled="disabled">';

                        $html .= '<div class="bank-row">';
                        $html .= '<input type="radio" name="OwnershipID" class="switch-deposit" value="' . $ownership['ResortName'] . '" style="text-align: center;">';
                        $html .= '</div>';
                    }

                    /*
                             * todo: add dropdown when room type is blank
                             */
                    $unitType = $creditWeek->Room_Type__c;
                    $hiddenUnitType = '<input type="hidden" name="unit_type" value="' . $unitType . '" class="disswitch" disabled="disabled">';

                    $upgradeMessage = '';
                    if ((isset($result) && in_array($result->ResortName, $selectUnit)) || empty($unitType)) {
                        $unitType = '<select name="Unit_Type__c" class="sel_unit_type doe">';
                        $unitType .= '<option data-upgradefee="' . $defaultUpgrade['st'] . '">Studio</option>';
                        $unitType .= '<option data-upgradefee="' . $defaultUpgrade['1'] . '">1br</option>';
                        $unitType .= '<option data-upgradefee="' . $defaultUpgrade['2'] . '">2br</option>';
                        $unitType .= '<option data-upgradefee="' . $defaultUpgrade['3'] . '">3br</option>';
                        $unitType .= '</select>';
                        $upgradeMessage = 'style="display: none;"';
                        $hiddenUnitType = '';
                    }

                    $html .= '<div class="bank-row">Unit Type: ' . $unitType . '</div>';
                    $html .= '<div class="bank-row">Week Type: ' . $creditWeek->Week_Type__c . '</div>';
                    $html .= '<div class="bank-row">Ownership Type:' . $ownershipType . '</div>';
                    $html .= '<div class="bank-row">Resort Member Number: ' . $creditWeek->Contract_ID__c . '</div>';
                    if (isset($ownership['Year_Last_Banked__c'])) {
                        $html .= '<div class="bank-row">Last Year Banked: ' . $ownership['Year_Last_Banked__c'] . '</div>';
                    }
                    $html .= '<div class="bank-row" style="height: 40px; position: relative;">';

                    if (empty($delinquent)) {
                        $html .= '<input type="text" placeholder="Check In Date" name="Check_In_Date__c" class="validate mindatepicker disswitch" data-mindate="' . $nextyear . '" value="" disabled="disabled" required>';
                    }
                    $html .= $hiddenUnitType;
                    $html .= '<input type="hidden" name="Contract_ID__c" value="' . $creditWeek->Contract_ID__c . '" class="disswitch" disabled="disabled">';
                    $html .= '<input type="hidden" name="Usage__c" value="' . $creditWeek->Usage__c . '" class="disswitch" disabled="disabled">';
                    $html .= '<input type="hidden" name="Account_Name__c" value="' . $creditWeek->Property_Owner__c . '" class="disswitch" disabled="disabled">';
                    $html .= '<input type="hidden" name="GPX_Member__c" value="' . $creditWeek->Owner_ID__c . '" class="disswitch" disabled="disabled">';
                    $html .= '<input type="hidden" name="GPX_Resort__c" value="' . $creditWeek->GPR_Resort__c . '" class="disswitch" disabled="disabled">';
                    $html .= '<input type="hidden" name="Resort_Name__c" value="' . $ownership['ResortName'] . '" class="disswitch" disabled="disabled">';
                    $html .= '<input type="hidden" name="Resort_Unit_Week__c" value="' . $creditWeek->UnitWeek__c . '" class="disswitch" disabled="disabled">';
                    $html .= '<input type="hidden" name="cid" value="' . $cid . '" class="disswitch" disabled="disabled">';
                    $html .= '</div>';

                    $resRequired = '';
                    if ($ownership['gpr'] == '0') {
                        $resRequired = ' required="required"';
                    }
                    $html .= '<div class="reswrap"><input type="text" name="Reservation__c" placeholder="Reservation Number" class="resdisswitch" disabled="disabled" ' . $resRequired . ' /></div>';

                    if (($upgradeFee > 0 || !empty($upgradeMessage)) && !empty($exchangebooking)) {
                        $html .= '<div class="bank-row doe_upgrade_msg" ' . $upgradeMessage . '>';
                        $html .= 'Please note: This booking requires an upgrade fee';
                        $html .= '</div>';
                    }
                    $html .= '</li>';
                    $i++;
                }
            }
            $html .= '</ul>';
            $html .= '</form>';
            $html .= '<p id="floatDisc" style="font-size: 18px; margin-top: 35px;">*Float reservations must be made with your home resort prior to deposit. Deposit transactions will automatically be system verified. Unverified deposits may result in the canellation of exchange reservations.</p>';
            $html .= '</div>';
        }
        $data['CPOPrice'] = get_option('gpx_fb_fee');
        if (isset($cpoFee) && !empty($cpoFee)) {
            $data['CPOPrice'] = $cpoFee;
        }
        $data['html'] = $html;
        $data['resortName'] = $resortName;
    }

    wp_send_json( $data );
}
add_action( "wp_ajax_gpx_load_exchange_form", "gpx_load_exchange_form" );
add_action( "wp_ajax_nopriv_gpx_load_exchange_form", "gpx_load_exchange_form" );

/**
 *
 *
 *
 *
 */
function gpx_admin_toolbar_link( $wp_admin_bar ) {
    $args = [
        'id' => 'gpx_admin',
        'title' => 'GPX Admin',
        'href' => '/wp-admin/admin.php?page=gpx-admin-page',
        'meta' => [ 'class' => 'my-toolbar-gpx-page' ],
    ];
    $wp_admin_bar->add_node( $args );
}

add_action( 'admin_bar_menu', 'gpx_admin_toolbar_link', 999 );

