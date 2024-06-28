<?php

use GPX\Model\Credit;
use GPX\Model\UserMeta;
use GPX\Model\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use GPX\Api\Salesforce\Salesforce;
use GPX\Repository\OwnerRepository;
use GPX\Model\Checkout\ShoppingCart;
use GPX\Repository\IntervalRepository;
use GPX\Form\Checkout\DepositWeekForm;

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

    wp_send_json(['credit' => $credit, 'ownerships' => $ownerships, 'fees' => $fees, 'is_agent' => gpx_is_agent()]);
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



