<?php

use GPX\Model\Credit;
use GPX\Model\Partner;
use GPX\Model\UserMeta;
use GPX\Model\Interval;
use GPX\Model\UnitType;
use GPX\Model\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use GPX\Model\DepositOnExchange;
use GPX\Api\Salesforce\Salesforce;
use GPX\Repository\OwnerRepository;
use GPX\Model\Checkout\ShoppingCart;
use GPX\Repository\CreditRepository;
use GPX\Repository\IntervalRepository;
use GPX\Form\Checkout\DepositWeekForm;
use GPX\Form\Perks\PerksTransferDepositForm;

function gpx_load_deposit_form() {
    $fees = gpx_get_late_fee_settings();
    $tp = gpx_get_third_party_fee_settings();
    $fees['tp_fee'] = $tp['fee'];
    $fees['tp_days'] = $tp['days'];

    if (!is_user_logged_in()) {
        wp_send_json(['credit' => 0, 'ownerships' => [], 'fees' => $fees, 'is_agent' => false]);
    }

    $cid = gpx_get_switch_user_cookie();
    $credit = OwnerRepository::instance()->get_credits($cid);
    $ownerships = IntervalRepository::instance()->get_member_ownerships($cid, true);

    wp_send_json([
        'credit' => $credit,
        'ownerships' => $ownerships,
        'fees' => $fees,
        'is_agent' => gpx_is_agent(),
    ]);
}

add_action("wp_ajax_gpx_load_deposit_form", "gpx_load_deposit_form");
add_action("wp_ajax_nopriv_gpx_load_deposit_form", "gpx_load_deposit_form");

function gpx_deposit_week(ShoppingCart $cart): ?Credit {
    if (!is_user_logged_in()) {
        wp_send_json(['success' => false, 'message' => 'Must be logged in'], 403);
    }

    if (!$cart->isDeposit()) {
        wp_send_json(['success' => false, 'message' => 'Must be a deposit'], 422);
    }

    if (!$cart->getTotals()->total > 0 && !$cart->item()->getDeposit()->fee && !$cart->isAgent()) {
        wp_send_json(['success' => false, 'message' => 'A payment has not been made'], 422);
    }

    $ownership = $cart->item()->getOwnership();
    $deposit = $cart->item()->getDeposit();
    $interval = IntervalRepository::instance()->getIntervalFromSalesforce($ownership->contractID);

    $credit = new Credit([
        'created_date' => Carbon::now(),
        'interval_number' => $ownership->contractID,
        'sf_name' => null,
        'resort_name' => $ownership->ResortName,
        'deposit_year' => $deposit->getCheckinDate()?->format('Y'),
        'check_in_date' => $deposit->getCheckinDate()?->format('Y-m-d'),
        'owner_id' => $cart->cid,
        'unit_type' => $deposit->unit_type ?? $interval?->Room_Type__c ?? $ownership->Room_Type__c,
        'unitinterval' => $interval?->UnitWeek__c ?? $ownership->unitweek,
        'reservation_number' => $deposit->reservation_number,
    ]);
    $credit->save();

    //send the details to SF
    $sf = Salesforce::getInstance();
    $owner = UserMeta::load($cart->cid);
    $agent = UserMeta::load(get_current_user_id());

    $sfDepositData = [
        'GPX_Deposit_ID__c' => $credit->id,
        'Check_In_Date__c' => $deposit->getCheckinDate()?->format('Y-m-d'),
        'Deposit_Year__c' => $deposit->getCheckinDate()?->format('Y'),
        'Account_Name__c' => $interval?->Property_Owner__c ?? '',
        'GPX_Member__c' => $cart->cid,
        'Deposit_Date__c' => date('Y-m-d H:i:s'),
        'Resort__c' => $ownership->resortID ?? '',
        'Resort_Name__c' => $ownership->ResortName ?? '',
        'Resort_Unit_Week__c' => $credit->unitinterval ?? '',
        'Coupon__c' => mb_substr($deposit->coupon ?? '', 0, 20),
        'Unit_Type__c' => $credit->unit_type,
        'Member_Email__c' => $owner->getEmailAddress(),
        'Member_First_Name__c' => stripslashes(str_replace("&", "&amp;", $owner->getFirstName())),
        'Member_Last_Name__c' => stripslashes(str_replace("&", "&amp;", $owner->getLastName())),
        'Deposited_by__c' => $agent->getName(),
    ];
    if ($interval?->ID) {
        $sfDepositData['Ownership_Interval__c'] = $interval->ID;
    }
    if (!empty($deposit->reservation_number)) {
        $sfDepositData['Reservation__c'] = $deposit->reservation_number;
    }
    $sfFields = new SObject();
    $sfFields->fields = $sfDepositData;
    $sfFields->type = 'GPX_Deposit__c';
    $sfDepositAdd = $sf->gpxUpsert('GPX_Deposit_ID__c', [$sfFields]);

    if (isset($sfDepositAdd[0]->id)) {
        $credit->update(['record_id' => $sfDepositAdd[0]->id]);
    } else {
        gpx_logger()->error('Failed to add deposit to Salesforce', [
            'result' => $sfDepositAdd,
            'data' => $sfDepositData,
            'credit' => $credit,
        ]);
    }

    return $credit;
}

function gpx_extend_credit(ShoppingCart $cart): ?Credit {
    $credit = $cart->item()->getCredit();
    $newdate = $cart->item()->getExtensionDate();
    $today = date('Y-m-d');

    $modID = DB::table('wp_credit_modification')->insertGetId([
        'credit_id' => $credit->id,
        'recorded_by' => get_current_user_id(),
        'data' => json_encode([
            [
                'type' => 'Credit Extension',
                'oldDate' => $credit->credit_expiration_date,
                'newDate' => $newdate,
            ],
        ]),
    ]);

    $credit->update([
        'credit_expiration_date' => $newdate,
        'extension_date' => $today,
        'modification_id' => $modID,
        'modified_date' => $today,
    ]);

    //send to SF
    $sf = Salesforce::getInstance();
    $sfFields = new SObject();
    $sfFields->fields = [
        'GPX_Deposit_ID__c' => $credit->id,
        'Credit_Extension_Date__c' => $today,
        'Expiration_Date__c' => $newdate,
    ];
    $sfFields->type = 'GPX_Deposit__c';

    $sfDepositAdd = $sf->gpxUpsert('GPX_Deposit_ID__c', [$sfFields]);

    return $credit;
}

function gpx_perks_choose_credit() {
    if (!is_user_logged_in()) {
        return '<h2 style="text-align: center;background-color:#e7e8eb;padding:35px;margin-bottom:100px;"><a class="call-modal-log login call-modal-login signin" href="#">Please Sign In to Continue</a></h2>';
    }

    $cid = gpx_get_switch_user_cookie();
    $is_agent = $cid != get_current_user_id();
    $ownerships = IntervalRepository::instance()->get_member_ownerships($cid, true)->map(function (Interval $ownership) use ($is_agent) {
        return [
            'id' => (int) $ownership->id,
            'is_delinquent' => (bool) $ownership->is_delinquent,
            'resort_id' => $ownership->resort_id,
            'ResortName' => $ownership->ResortName,
            'Room_Type__c' => $ownership?->interval->Room_Type__c ?? $ownership->Room_Type__c ?? null,
            'Week_Type__c' => $ownership->interval?->Week_Type__c ?? null,
            'Contract_ID__c' => $ownership->interval?->Contract_ID__c ?? null,
            'Year_Last_Banked__c' => $interval->Year_Last_Banked__c ?? $ownership->Year_Last_Banked__c ?? $ownership->deposit_year ?? null,
            'next_year' => $is_agent ? date('Y-m-d', strtotime("-2 years")) : $ownership->nextyear,
            'gpr' => (bool) $ownership->gpr,
            'defaultUpgrade' => null,
            'upgradeFee' => 0,
        ];
    });
    $credits = CreditRepository::instance()->getOwnerCreditWeeks($cid)->get()->map(fn(Credit $creditWeek) => [
        'id' => (int) $creditWeek->id,
        'resort' => $creditWeek->resort_name,
        'expires' => gpx_format_date($creditWeek->credit_expiration_date, 'm/d/Y'),
        'year' => $creditWeek->deposit_year,
        'size' => $creditWeek->unit_type,
        'upgradeFee' => $creditWeek->upgradeFee,
        'expired' => $creditWeek->isExpired(),
        'delinquent' => $creditWeek->Delinquent__c === 'Yes',
    ]);

    return '<div id="perks-exchange-credit" data-props="' . esc_attr(json_encode([
            'credits' => $credits,
            'ownerships' => $ownerships,
        ])) . '"><div style="text-align: center;"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div></div>';
}

add_shortcode('perks_choose_credit', 'gpx_perks_choose_credit');

function gpx_credit_transfer() {
    if (!is_user_logged_in()) {
        wp_send_json(['success' => false, 'message' => 'Must be logged in'], 403);
    }

    $form = gpx(PerksTransferDepositForm::class);
    $values = $form->validate();

    $sf = Salesforce::getInstance();
    $cid = gpx_get_switch_user_cookie();
    $usermeta = UserMeta::load($cid);
    $agent = false;
    if ($cid != get_current_user_id()) {
        $agent = true;
        $agentmeta = UserMeta::load(get_current_user_id());
        $depositBy = stripslashes(str_replace("&", "and", $agentmeta->getName()));
    }

    $sfData = [];
    $sfCreditData = [];

    if ($values['type'] == 'deposit') {
        // a new credit needs to be created
        $interval = IntervalRepository::instance()->get_member_interval($cid, $values['deposit'], true);
        if (!$interval) {
            wp_send_json([
                'success' => false,
                'message' => 'Invalid Deposit',
                'errors' => ['deposit' => ['Deposit was not found']],
            ], 422);
        }

        $credit = Credit::create([
            'created_date' => Carbon::now(),
            'deposit_year' => date('Y', strtotime($values['checkin'])),
            'resort_name' => $interval->ResortName,
            'check_in_date' => $values['checkin'],
            'owner_id' => $cid,
            'interval_number' => $interval->Contract_ID__c,
            'unit_type' => $values['unit_type'],
            'status' => 'DOE',
            'coupon' => $values['coupon'],
            'reservation_number' => $values['reservation'],
        ]);

        $deposit = DepositOnExchange::create([
            'creditID' => $credit->id,
            'data' => [
                'created_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'OwnershipID' => $interval->ResortName,
                'resort_name' => $interval->ResortName,
                'GPX_Resort__c' => $interval->resortID,
                'Resort_Name__c' => $interval->ResortName,
                'Check_In_Date__c' => $values['checkin'],
                'check_in_date' => $values['checkin'],
                'unit_type' => $values['unit_type'],
                'Resort_Unit_Week__c' => $interval->unitweek,
                'Reservation__c' => $values['reservation'] ?? '',
                'unitweek' => $interval->unitweek,
                'Delinquent__c' => $interval->Delinquent__c,
                'GPX_Deposit_ID__c' => $credit->id,
                'Contract_ID__c' => $interval->Contract_ID__c,
                'contractID' => $interval->Contract_ID__c,
                'interval_number' => $interval->Contract_ID__c,
                'RIOD_Key_Full' => $interval->RIOD_Key_Full,
                'Account_Name__c' => $interval->Property_Owner__c ?? '',
                'resortID' => $interval->resortID,
                'creditID' => $credit->id,
                'cid' => $cid,
                'userID' => $cid,
                'owner_id' => $cid,
                'Room_Type__c' => $interval->Room_Type__c,
                'status' => 'DOE',
                'deposit_year' => date('Y', strtotime($values['checkin'])),
            ],
        ]);

        $sfCreditData = [
            'GPX_Deposit_ID__c' => $credit->id,
            'Check_In_Date__c' => $values['checkin'],
            'Deposit_Year__c' => date('Y', strtotime($values['checkin'])),
            'Account_Name__c' => $interval->Property_Owner__c ?? '',
            'GPX_Member__c' => $cid,
            'Deposit_Date__c' => date('Y-m-d'),
            'Resort__c' => $interval->gprID,
            'Resort_Name__c' => $interval->ResortName,
            'Resort_Unit_Week__c' => $interval->unitweek,
            'Unit_Type__c' => $values['unit_type'] ?: $interval->Room_Type__c,
            'Member_Email__c' => $usermeta->getEmailAddress(),
            'Member_First_Name__c' => $usermeta->getFirstName(),
            'Member_Last_Name__c' => $usermeta->getLastName(),
        ];
        if ($values['reservation']) {
            $sfCreditData['Reservation__c'] = $values['reservation'];
        }
        if ($interval->interval_id) {
            $sfCreditData['Ownership_Interval__c'] = $interval->interval_id;
        }
        if ($agent) {
            $sfCreditData['Deposited_by__c'] = $depositBy;
        }

        $credit->fill([
            'credit_action' => 'transferred',
            'status' => 'Pending',
            'unitinterval' => $interval->unitweek,
        ]);
        $sfData['Status__c'] = 'Pending';
        $sfData['Request_Type__c'] = 'Transfer to Perks';
    }

    if ($values['type'] == 'credit') {
        $credit = CreditRepository::instance()->getOwnerCredit($cid, $values['credit']);
        if (!$credit) {
            wp_send_json([
                'success' => false,
                'message' => 'Invalid Credit',
                'errors' => ['credit' => ['Credit was not found']],
            ], 422);
        }
        $credit->fill([
            'credit_action' => 'transferred',
        ]);
        $sfCreditData['GPX_Deposit_ID__c'] = $credit->id;
        $sfData['Status__c'] = 'Approved';
        $sfData['Request_Type__c'] = 'Transfer to Perks';
    }

    $credit->fill([
        'credit_used' => $credit->credit_used + 1,
    ]);

    $sfCreditData['Credits_Used__c'] = $credit->credit_used;

    $sfFields = new SObject();
    $sfFields->fields = $sfCreditData;
    $sfFields->type = 'GPX_Deposit__c';

    $sfDeposit = $sf->gpxUpsert('GPX_Deposit_ID__c', [$sfFields]);
    if (isset($sfDeposit[0]->id)) {
        $credit->record_id = $sfDeposit[0]->id;
    } else {
        // failed to create deposit in Salesforce
        gpx_logger()->error('Failed to add deposit to Salesforce', [
            'result' => $sfDeposit,
            'data' => $sfFields->fields,
            'credit' => $credit,
        ]);
    }
    $credit->save();

    //send the datails to SF as a transaction
    $partner = Partner::find($cid);

    if ($_POST['type'] == 'transferred') {
        $pt = 'Transfer to Perks';
        $transactionType = 'credit_transfer';

    }

    //explode the name
    $sfData['GPX_Deposit__c'] = $credit->record_id;
    $sfData['Member_First_Name__c'] = $usermeta->getFirstName();
    $sfData['Member_Last_Name__c'] = $usermeta->getLastName();
    $sfData['Member_Email__c'] = $usermeta->getEmailAddress();
    $sfData['EMS_Account__c'] = $credit->owner_id;
    $sfData['Account_Name__c'] = $usermeta->Property_Owner;
    $sfData['Account_Type__c'] = $partner ? 'USA GPX Trade Partner' : 'USA GPX Member';
    $sfData['Purchase_Type__c'] = '0';
    $sfData['Request_Type__c'] = $pt;
    $sfData['Transaction_Book_Date__c'] = date('Y-m-d');
    $sfData['Date_Last_Synced_with_GPX__c'] = date('Y-m-d');
    $bookedby_user_info = get_userdata(get_current_user_id());
    $sfData['Booked_By__c'] = $bookedby_user_info->first_name . " " . $bookedby_user_info->last_name;
    $sfData['RecordTypeId'] = '0121W000000QQ75';

    if ($pt == 'Transfer to Perks') {
        $sfData['ICE_Account_ID__c'] = $usermeta->ICENameId;
    }


    $tx = [
        'transactionType' => 'credit_transfer',
        'cartID' => 'na',
        'userID' => $credit->owner_id,
        'resortID' => 0,
        'weekId' => 0,
        'paymentGatewayID' => '0',
        'transactionData' => $sfData,
        'data' => $sfData,
        'depositID' => $credit->id,
    ];

    $transaction = Transaction::create($tx);

    $sfData['GPXTransaction__c'] = $transaction->id;
    $sfData['Name'] = $transaction->id;

    $sfFields = new SObject();
    $sfFields->fields = $sfData;
    $sfFields->type = 'GPX_Transaction__c';
    $sfAdd = $sf->gpxUpsert('GPXTransaction__c', [$sfFields]);

    if (isset($sfAdd[0]->id)) {
        $sfTransactionID = $sfAdd[0]->id;
        $sfDB = [
            'sfid' => $sfTransactionID,
            'sfData' => ['insert' => $sfData],
        ];

        $transaction->update($sfDB);
    } else {
        // failed to create transaction in Salesforce
        gpx_logger()->error('Failed to add transaction to Salesforce', [
            'result' => $sfAdd,
            'data' => $sfFields->fields,
            'transaction' => $transaction,
        ]);
    }

    // send to ice
    post_IceMemeberJWT($cid);


    wp_send_json(['success' => true]);

}

add_action("wp_ajax_gpx_credit_transfer", "gpx_credit_transfer");

function gpx_credit_action() {
    global $wpdb;

    if (isset($_POST['id'])) {

        $sf = Salesforce::getInstance();

        $pendingStatus = '';
        if ($_POST['type'] == 'deposit_transferred') {
            $pendingStatus = 1;
            $sql = $wpdb->prepare("SELECT creditID, data FROM wp_gpxDepostOnExchange WHERE id=%s", $_POST['id']);
            $doe = $wpdb->get_row($sql);

            $_POST['id'] = $doe->creditID;
            $_POST['type'] = 'transferred';

            $depositData = json_decode($doe->data);

            $usermeta = (object) array_map(function ($a) { return $a[0]; }, get_user_meta($depositData->cid));

            if ($depositData->owner_id != get_current_user_id()) {
                $agent = true;
                $agentmeta = (object) array_map(function ($a) { return $a[0]; }, get_user_meta(get_current_user_id()));
                $depositBy = stripslashes(str_replace("&", "&amp;", $agentmeta->first_name)) . " " . stripslashes(str_replace("&", "&amp;", $agentmeta->last_name));
            }

            $email = $usermeta->Email;
            if (empty($email)) {
                $email = $usermeta->email;
            }

            $query = $wpdb->prepare("SELECT ID, Name FROM Ownership_Interval__c WHERE ROID_Key_Full__c = %s", $depositData->RIOD_Key_Full);
            $results = $sf->query($query);

            $interval = $results[0]->Id;

            $sfCreditData = [
                'Account_Name__c' => $depositData->Account_Name__c,
                'Check_In_Date__c' => date('Y-m-d', strtotime($depositData->check_in_date)),
                'Deposit_Year__c' => date('Y', strtotime($depositData->check_in_date)),
                'GPX_Member__c' => $depositData->owner_id,
                'Deposit_Date__c' => date('Y-m-d'),
                'Resort__c' => $depositData->resortID,
                'Resort_Name__c' => stripslashes(str_replace("&", "&amp;", $depositData->Resort_Name__c)),
                'Resort_Unit_Week__c' => $depositData->Resort_Unit_Week__c,
                'Member_Email__c' => $email,
                'Member_First_Name__c' => stripslashes(str_replace("&", "&amp;", $usermeta->first_name)),
                'Member_Last_Name__c' => stripslashes(str_replace("&", "&amp;", $usermeta->last_name)),
                'Deposited_by__c' => $depositBy,
                'Unit_Type__c' => $depositData->unit_type,
                'Ownership_Interval__c' => $interval,
            ];

            if (!empty($depositData->Reservation__c)) {
                $sfCreditData['Reservation__c'] = $depositData->Reservation__c;
            }
            $tDeposit = [
                'status' => 'Pending',
                'unitinterval' => $depositData->unitweek,
            ];
        }

        $sql = $wpdb->prepare("SELECT * FROM wp_credit WHERE id=%s", $_POST['id']);
        $credit = $wpdb->get_row($sql);

        $update = [
            'credit_action' => $_POST['type'],
            'credit_used' => $credit->credit_used + 1,
        ];

        if (!empty($tDeposit)) {
            $update = array_merge($update, $tDeposit);
        }

        $sfCreditData['GPX_Deposit_ID__c'] = $credit->id;
        $sfCreditData['Credits_Used__c'] = $update['credit_used'];

        $sfFields = new SObject();
        $sfFields->fields = $sfCreditData;
        $sfFields->type = 'GPX_Deposit__c';

        $sfDepositAdjust = $sf->gpxUpsert('GPX_Deposit_ID__c', [$sfFields], 'true');

        $sfDepostID = $sfDepositAdjust[0]->id;

        //if this is ICE then we need to do the ICE shortcode

        $wpdb->update('wp_credit', $update, ['id' => $_POST['id']]);

        //send the datails to SF as a transaction

        $sql = $wpdb->prepare("SELECT record_id FROM wp_partner WHERE user_id=%d", $credit->owner_id);
        $partner = $wpdb->get_row($sql);

        $poro = 'USA GPX Member';
        if (!empty($partner)) {
            $poro = 'USA GPX Trade Partner';
        }

        if ($_POST['type'] == 'donated') {
            $pt = 'Donation';
            $transactionType = 'credit_donation';

            $data['redirect'] = true;
        }

        if ($_POST['type'] == 'transferred') {
            $pt = 'Transfer to Perks';
            $transactionType = 'credit_transfer';

            $data['redirect'] = true;
        }

        $sql = $wpdb->prepare("SELECT * FROM wp_GPR_Owner_ID__c WHERE user_id=%d", $credit->owner_id);
        $ownerData = $wpdb->get_row($sql);

        $user_info = get_userdata($credit->owner_id);


        $usermeta = (object) array_map(function ($a) { return $a[0]; }, get_user_meta($credit->owner_id));

        if (empty($usermeta->Email)) {
            $usermeta->Email = $usermeta->email;
            if (empty($usermeta->Email)) {
                $usermeta->Email = $usermeta->user_email;
            }
        }


        $user_info = get_userdata($rValue);
        $first_name = $usermeta->first_name;
        $last_name = $usermeta->last_name;
        $email = $usermeta->Email;
        $Property_Owner = $usermeta->Property_Owner;

        //explode the name
        $sfData['GPX_Deposit__c'] = $sfDepostID;
        $sfData['Member_First_Name__c'] = $first_name;
        $sfData['Member_Last_Name__c'] = $last_name;
        $sfData['Member_Email__c'] = $email;
        $sfData['EMS_Account__c'] = $credit->owner_id;
        $sfData['Account_Name__c'] = $Property_Owner;
        $sfData['Account_Type__c'] = $poro;
        if ($pt == 'Donation' || $pendingStatus == 1) {
            $sfData['Status__c'] = 'Pending';
        } elseif ($pt == 'Transfer to Perks') {
            $sfData['Status__c'] = 'Approved';
        }
        $sfData['Purchase_Type__c'] = '0';
        $sfData['Request_Type__c'] = $pt;
        $sfData['Transaction_Book_Date__c'] = date('Y-m-d');
        $sfData['Date_Last_Synced_with_GPX__c'] = date('Y-m-d');
        $bookedby_user_info = get_userdata(get_current_user_id());
        $sfData['Booked_By__c'] = $bookedby_user_info->first_name . " " . $bookedby_user_info->last_name;
        $sfData['RecordTypeId'] = '0121W000000QQ75';

        if ($pt == 'Transfer to Perks') {
            $sfData['ICE_Account_ID__c'] = $usermeta->ICENameId;
        }


        $txData = json_encode($sfData);

        $tx = [
            'transactionType' => $transactionType,
            'cartID' => 'na',
            'userID' => $credit->owner_id,
            'resortID' => 0,
            'weekId' => 0,
            'paymentGatewayID' => '0',
            'transactionData' => $txData,
            'data' => $txData,
            'depositID' => $credit->id,
        ];

        $wpdb->insert('wp_gpxTransactions', $tx);

        $transactionID = $wpdb->insert_id;

        $sfData['GPXTransaction__c'] = $transactionID;
        $sfData['Name'] = $transactionID;

        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfData;
        $sfFields[0]->type = 'GPX_Transaction__c';

        $sfObject = 'GPXTransaction__c';

        $sfAdd = $sf->gpxUpsert($sfObject, $sfFields, 'true');

        if (isset($sfAdd[0]->id)) {
            $sfTransactionID = $sfAdd[0]->id;
            $sfDB = [
                'sfid' => $sfTransactionID,
                'sfData' => json_encode(['insert' => $sfData]),
            ];

            $wpdb->update('wp_gpxTransactions', $sfDB, ['id' => $transactionID]);
        }

        $data['action'] = ucfirst($_POST['type']);
    }
    $data['success'] = true;

    wp_send_json($data);
}

add_action('wp_ajax_gpx_credit_action', 'gpx_credit_action');


function gpx_perks_choose_donation() {
    return '<div class="exchange-donate"><div id="exchangeList" data-type="donation"><div style="text-align: center;"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div></div></div>';
}

add_shortcode('perks_choose_donation', 'gpx_perks_choose_donation');





