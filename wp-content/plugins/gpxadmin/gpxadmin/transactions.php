<?php

use GPX\Command\Week\AdminHoldWeeks;
use GPX\Model\Week;
use GPX\Model\Owner;
use GPX\Model\Credit;
use GPX\Model\PreHold;
use GPX\Model\Partner;
use GPX\Model\Special;
use GPX\Model\UserMeta;
use GPX\Model\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use GPX\Output\StreamAndOutput;
use GPX\Model\OwnerCreditCoupon;
use GPX\Repository\WeekRepository;
use GPX\Repository\OwnerRepository;
use GPX\Api\Salesforce\Salesforce;
use GPX\Model\Checkout\Item\GuestFee;
use GPX\Repository\IntervalRepository;
use GPX\Model\OwnerCreditCouponActivity;
use GPX\Repository\TransactionRepository;
use GPX\Command\Transaction\UpdateGuestInfo;
use GPX\DataObject\Transaction\RefundRequest;
use GPX\Command\Partner\Week\PartnerHoldWeeks;
use GPX\Command\Partner\Week\PartnerBookWeeks;
use Symfony\Component\Console\Output\NullOutput;
use GPX\Form\Profile\Transaction\UpdateGuestForm;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpFoundation\StreamedResponse;
use GPX\Form\Admin\TradePartner\TradePartnerClaimWeekForm;

function creditExtention() {
    global $wpdb;


    $id = $_REQUEST['id'];
    $newdate = date('m/d/Y', strtotime($_REQUEST['dateExtension']));

    $sql = $wpdb->prepare("SELECT credit_expiration_date FROM wp_credit WHERE id=%s", $id);
    $row = $wpdb->get_row($sql);

    $moddata = [
        'type' => 'Credit Extension',
        'oldDate' => $row->credit_expiration_date,
        'newDate' => date('Y-m-d', strtotime($newdate)),
    ];

    $mod = [
        'credit_id' => $id,
        'recorded_by' => get_current_user_id(),
        'data' => json_encode($moddata),
    ];

    $wpdb->insert('wp_credit_modification', $mod);

    $modID = $wpdb->insert_id;

    $update = [
        'credit_expiration_date' => date("Y-m-d", strtotime($newdate)),
        'extension_date' => date('Y-m-d'),
        'modification_id' => $modID,
        'modified_date' => date('Y-m-d'),
    ];

    $wpdb->update('wp_credit', $update, ['id' => $id]);

    /*
     * TODO: Test after functionality is confirmed
     */

    //send to SF
    $sf = Salesforce::getInstance();

    $sfDepositData = [
        'GPX_Deposit_ID__c' => $id,
        'Credit_Extension_Date__c' => date('Y-m-d'),
        'Expiration_Date__c' => date('Y-m-d', strtotime($newdate)),
    ];

    $sfType = 'GPX_Deposit__c';
    $sfObject = 'GPX_Deposit_ID__c';

    $sfFields = [];
    $sfFields[0] = new SObject();
    $sfFields[0]->fields = $sfDepositData;
    $sfFields[0]->type = $sfType;

    $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);

    $msg = "Credit has been extended to " . $newdate;

    $cid = 0; // Undefined variable $cid
    $return = ['success' => true, 'message' => $msg, 'date' => $newdate, 'cid' => $cid];
}

add_action('wp_ajax_creditExtention', 'creditExtention');
add_action('wp_ajax_nopriv_creditExtention', 'creditExtention');


function rework_missed_deposits() {
    global $wpdb;

    $sql = "SELECT * FROM `deposit_rework` WHERE imported='33' LIMIT 1000";
    $rows = $wpdb->get_results($sql);
    foreach ($rows as $row) {
        $wpdb->update('deposit_rework', ['imported' => '5'], ['id' => $row->id]);
        $sql = $wpdb->prepare("SELECT `deposit used` FROM transactions_import_two WHERE weekId=%d AND MemberNumber=%d", [
            $row->weekId,
            $row->userID,
        ]);
        $odeposit = $wpdb->get_var($sql);
        if (!empty($odeposit)) {

            $sql = $wpdb->prepare("SELECT a.id FROM wp_credit a
                    INNER JOIN import_credit_future_stay b ON
                        b.Deposit_year=a.deposit_year AND
                        b.resort_name=a.resort_name AND
                        b.unit_type=a.unit_type AND
                        b.Member_Name=a.owner_id
                        WHERE b.ID=%d", $odeposit);
            $deposit = $wpdb->get_var($sql);

            if (!empty($deposit)) {
                $sql = $wpdb->prepare("SELECT id, data FROM wp_gpxTransactions WHERE weekId=%d AND userID=%d", [
                    $row->weekId,
                    $row->userID,
                ]);
                $d = $wpdb->get_row($sql);
                if (!empty($d)) {

                    $wpdb->update('deposit_rework', ['imported' => '1'], ['id' => $row->id]);
                    $data = json_decode($d->data, true);
                    $data['creditweekid'] = $deposit;
                    $wpdb->update('wp_gpxTransactions', [
                        'depositID' => $deposit,
                        'data' => json_encode($data),
                    ], ['id' => $d->id]);
                } else {
                    $wpdb->update('deposit_rework', ['imported' => '2'], ['id' => $row->id]);
                }
            } else {
                $wpdb->update('deposit_rework', ['imported' => '3'], ['id' => $row->id]);
            }
        } else {
            $wpdb->update('deposit_rework', ['imported' => '4'], ['id' => $row->id]);
        }
    }


    $sql = "SELECT COUNT(id) as cnt FROM `deposit_rework` WHERE imported='33'";
    $tcnt = $wpdb->get_var($sql);

    if ($tcnt > 0) {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(['remaining' => $tcnt]);
}

add_action('wp_ajax_rework_missed_deposits', 'rework_missed_deposits');


function rework_duplicate_credits() {
    global $wpdb;


    $sql = "SELECT checked, txid FROM credit_dup_checked";
    $ck = $wpdb->get_results($sql, ARRAY_A);
    foreach ($ck as $c) {
        $in[] = $c['checked'];
        $tx[] = $c['txid'];
    }


    if (count($in) > 0) {
        $sql = "select owner_id, deposit_year, unit_type, check_in_date, count(*) as NumDuplicates
                    from wp_credit
                    WHERE id NOT IN ('" . implode("','", $in) . "')
                    group by owner_id, deposit_year, unit_type, check_in_date
                    having NumDuplicates > 1 LIMIT 10";
    } else {
        $sql = "select owner_id, deposit_year, unit_type, check_in_date, count(*) as NumDuplicates
                    from wp_credit
                    group by owner_id, deposit_year, unit_type, check_in_date
                    having NumDuplicates > 1 LIMIT 10";
    }
    $results = $wpdb->get_results($sql);
    foreach ($results as $result) {

        $wheres = [];
        foreach ($result as $rk => $rv) {
            if ($rk == 'NumDuplicates') {
                continue;
            }
            $wheres[] = $wpdb->prepare(gpx_esc_table($rk) . " = %s", $rv);
        }

        $sql = "SELECT id, owner_id, deposit_year, check_in_date, credit_amount, resort_name, unit_type
                FROM wp_credit WHERE " . implode(" AND ", $wheres) . " ORDER BY id desc";

        $rows = $wpdb->get_results($sql);

        foreach ($rows as $k => $row) {
            $in[] = $row->id;
            $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE JSON_EXTRACT(data, '$.creditweekid') = %s", $row->id);
            $transaction = $wpdb->get_var($sql);

            $wpdb->insert('credit_dup_checked', ['checked' => $row->id, 'txid' => $transaction->id]);


            if (!empty($transaction)) {
                if (!in_array($transaction->id, $tx)) {
                    unset($rows[$k]);
                }
            }
        }
        usort($rows, function ($a, $b) {
            return $b->id - $a->id;
        });
        if (count($rows) > 1) {
            unset($rows[0]);
        }
        foreach ($rows as $row) {
            $toInsert[] = $row;
        }
    }
    foreach ($toInsert as $row) {
        $wpdb->insert('credit_dup_delete', (array) $row);
    }

    if (count($in) < 4233) {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(['remaining' => $tcnt]);
}

add_action('wp_ajax_rework_duplicate_credits', 'rework_duplicate_credits');


function rework_tp_inactive() {
    global $wpdb;

    $sql = "SELECT r.record_id FROM  wp_room r
            INNER JOIN import_partner_credits p ON p.record_id=r.record_id
            WHERE r.active=0 AND p.Active=1";
    $rows = $wpdb->get_results($sql);
    $i = 0;
    foreach ($rows as $row) {
        $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s", $row->record_id);
        $t = $wpdb->get_var($sql);
        if (!empty($t)) {
            continue;
        }
        $sql = $wpdb->prepare("SELECT id FROM wp_gpxPreHold WHERE weekId=%s AND released=0", $row->record_id);
        $h = $wpdb->get_var($sql);
        if (!empty($h)) {
            continue;
        }
        $wpdb->update('wp_room', [
            'active' => 1,
            'active_specific_date' => '2030-01-01',
            'active_rental_push_date' => '2030-01-01',
        ], ['record_id' => $row->record_id]);
        $i++;
    }
    $sql = "SELECT count(r.record_id) as cnt FROM  wp_room r
            INNER JOIN import_partner_credits p ON p.record_id=r.record_id
            WHERE r.active=0 AND p.Active=1";
    $tcnt = $wpdb->get_var($sql);

    wp_send_json(['remaining' => $tcnt]);
}

add_action('wp_ajax_gpx_rework_tp_inactive', 'rework_tp_inactive');


/**
 * Import Credit
 */
function gpx_import_credit_C() {
    global $wpdb;

    $sql = "SElECT * FROM import_owner_credits WHERE imported=0 order by RAND() LIMIT 100";
    $imports = $wpdb->get_results($sql, ARRAY_A);
    if (empty($imports)) {
        //try the other import function
        gpx_import_credit();
    }

    foreach ($imports as $import) {

        $wpdb->update('import_owner_credits', ['imported' => 1], ['ID' => $import['ID']]);
        $sfImport = $import;

        $user = reset(
            get_users(
                [
                    'meta_key' => 'DAEMemberNo',
                    'meta_value' => $import['Member_Name'],
                    'number' => 1,
                    'count_total' => false,
                ]
            )
        );
        if (empty($user)) {

            //let's try to import this owner
            $user = function_GPX_Owner($import['Member_Name']);

            if (empty($user)) {
                $exception = json_encode($import);
                $wpdb->insert("final_import_exceptions", ['type' => 'credit user', 'data' => $exception]);
                continue;
            }
        }

        $cid = $user->ID;
        $unit_week = '';
        $rid = '';


        $resortKeyOne = [
            'Butterfield Park - VI' => '2440',
            'Grand Palladium White Sand - AI' => '46895',
            'Grand Sirenis Riviera Maya Resort - AI' => '46896',
            'High Point World Resort - RHC' => '1549',
            'Los Abrigados Resort & Spa' => '2467',
            'Makai Club Cottages' => '1786',
            'Palm Canyon Resort & Spa' => '1397',
            'Sunset Marina Resort & Yacht Club - AI' => '46897',
            'Azul Beach Resort Negril by Karisma - AI' => '46898',
            'Bali Villas & Sports Club - Rentals Only' => '46899',
            'Blue Whale' => '46900',
            'Bluegreen Club 36' => '46901',
            'BreakFree Alexandra Beach' => '46902',
            'Classic @ Alpha Sovereign Hotel' => '46903',
            'Club Regina Los Cabos' => '46904',
            'Eagles Nest Resort - VI' => '1836',
            'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive' => '46905',
            'El Dorado Maroma by Karisma a Gourmet AI' => '46906',
            'El Dorado Royale by Karisma a Gourmet AI' => '46907',
            'Fiesta Ameri. Vac Club At Cabo Del Sol' => '46908',
            'Fort Brown Condo Shares' => '46909',
            'Four Seasons Residence Club Scottsdale@Troon North' => '2457',
            'Generations Riviera Maya by Karisma a Gourmet AI' => '46910',
            'GPX Cruise Exchange' => 'SKIP',
            'Grand Palladium Jamaica Resort & Spa - AI' => '46911',
            'Grand Palladium Vallarta Resort & Spa - AI' => '46912',
            'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive' => '46913',
            'High Sierra Condominiums' => '46914',
            'Kiltannon Home Farm' => '46915',
            'Knocktopher Abbey' => '46916',
            'Knocktopher Abbey (Shadowed)' => '46916',
            'Laguna Suites Golf and Spa - AI' => '46917',
            'Maison St. Charles - Rentals Only' => '46918',
            'Makai Club Resort' => '1787',
            'Marina Del Rey Beach Club - No Longer Accepting' => '46919',
            'Mantra Aqueous on Port' => '46920',
            'Maui Sunset - Rentals Only' => '1758',
            'Mayan Palace Mazatlan' => '3652',
            'Ocean Gate Resort' => '46921',
            'Ocean Spa Hotel - AI' => '46922',
            'Paradise' => '46923',
            'Park Royal Homestay Club Cala' => '338',
            'Park Royal Los Cabos - RHC' => '46924',
            'Peacock Suites Resort' => '46925',
            'Pounamu Apartments - Rental' => '46926',
            'Presidential Suites by LHVC - Punta Cana NON - AI' => '46927',
            'RHC - Park Royal - Los Tules' => '46928',
            'Royal Regency Paris (Shadowed)' => '479',
            'Royal Sunset - AI' => '46929',
            'Secrets Puerto Los Cabos Golf & Spa Resort - AI' => '46930',
            'Secrets Wild Orchid Montego Bay - AI' => '46931',
            'Solare Bahia Mar - Rentals Only' => '46932',
            'Tahoe Trail - VI' => '40',
            'The RePlay Residence' => '46933',
            'The Tropical at LHVC - AI' => '46934',
            'Vacation Village at Williamsburg' => '2432',
            'Wolf Run Manor At Treasure Lake' => '46935',
            'Wyndham Grand Desert - 3 Nights' => '46936',
            'Wyndham Royal Garden at Waikiki - Rental Only' => '1716',
        ];

        $resortKeyTwo = [
            'Royal Aloha Chandler - Butterfield Park' => '2440',
            'Grand Palladium White Sand - AI' => '46895',
            'Grand Sirenis Riviera Maya Resort - AI' => '46896',
            'High Point World Resort' => '1549',
            'Los Abrigados Resort and Spa' => '2467',
            'Makai Club Resort Cottages' => '1786',
            'Palm Canyon Resort and Spa' => '1397',
            'Sunset Marina Resort & Yacht Club - AI' => '46897',
            'Azul Beach Resort Negril by Karisma - AI' => '46898',
            'Bali Villas & Sports Club - Rentals Only' => '46899',
            'Blue Whale' => '46900',
            'Bluegreen Club 36' => '46901',
            'BreakFree Alexandra Beach' => '46902',
            'Classic @ Alpha Sovereign Hotel' => '46903',
            'Club Regina Los Cabos' => '46904',
            'Royal Aloha Branson - Eagles Nest Resort' => '1836',
            'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive' => '46905',
            'El Dorado Maroma by Karisma a Gourmet AI' => '46906',
            'El Dorado Royale by Karisma a Gourmet AI' => '46907',
            'Fiesta Ameri. Vac Club At Cabo Del Sol' => '46908',
            'Fort Brown Condo Shares' => '46909',
            'Four Seasons Residence Club Scottsdale at Troon North' => '2457',
            'Generations Riviera Maya by Karisma a Gourmet AI' => '46910',
            'SKIP' => 'SKIP',
            'Grand Palladium Jamaica Resort & Spa - AI' => '46911',
            'Grand Palladium Vallarta Resort & Spa - AI' => '46912',
            'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive' => '46913',
            'High Sierra Condominiums' => '46914',
            'Kiltannon Home Farm' => '46915',
            'Knocktopher Abbey' => '46916',
            'Laguna Suites Golf and Spa - AI' => '46917',
            'Maison St. Charles - Rentals Only' => '46918',
            'Makai Club Resort Condos' => '1787',
            'Marina Del Rey Beach Club - No Longer Accepting' => '46919',
            'Mantra Aqueous on Port' => '46920',
            'Maui Sunset' => '1758',
            'Mayan Palace Mazatlan by Grupo Vidanta' => '3652',
            'Ocean Gate Resort' => '46921',
            'Ocean Spa Hotel - AI' => '46922',
            'Paradise' => '46923',
            'Royal Holiday - Park Royal Club Cala' => '338',
            'Park Royal Los Cabos - RHC' => '46924',
            'Peacock Suites Resort' => '46925',
            'Pounamu Apartments - Rental' => '46926',
            'Presidential Suites by LHVC - Punta Cana NON - AI' => '46927',
            'RHC - Park Royal - Los Tules' => '46928',
            'Royal Regency By Diamond Resorts' => '479',
            'Royal Sunset - AI' => '46929',
            'Secrets Puerto Los Cabos Golf & Spa Resort - AI' => '46930',
            'Secrets Wild Orchid Montego Bay - AI' => '46931',
            'Solare Bahia Mar - Rentals Only' => '46932',
            'Royal Aloha Tahoe' => '40',
            'The RePlay Residence' => '46933',
            'The Tropical at LHVC - AI' => '46934',
            'Williamsburg Plantation Resort' => '2432',
            'Wolf Run Manor At Treasure Lake' => '46935',
            'Wyndham Grand Desert - 3 Nights' => '46936',
            'Royal Garden at Waikiki Resort' => '1716',
            'Costa Sur Resort & Spa' => '46872',
        ];

        $sql = $wpdb->prepare("SELECT gprID, ResortName FROM wp_resorts WHERE id=%s", $resortKeyOne[$import['resort_name']]);
        $resortDet = $wpdb->get_row($sql);

        $rid = $resortDet->gprID;
        $resortName = $resortDet->ResortName;

        if (empty($resortName)) {
            $resortName = $import['resort_name'];
            $resortName = str_replace("- VI", "", $resortName);
            $resortName = trim($resortName);
            $sql = $wpdb->prepare("SELECT gprID FROM wp_resorts WHERE ResortName=%s", $resortName);
            $rid = $wpdb->get_var($sql);
        }
        if (empty($rid)) {
            $exception = json_encode($import);
            $wpdb->update('import_exceptions', ['validated' => 2], ['id' => $result['id']]);
            $wpdb->insert("final_import_exceptions", ['type' => 'credit resort', 'data' => $exception]);
            continue;
        }

        $email = $user->Email;

        $sfDepositData = [

            'Check_In_Date__c' => date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c' => date('Y-m-d', strtotime($import['Credit_expiratio_date'])),
            'Deposit_Year__c' => $import['Deposit_year'],
            'Account_Name__c' => $user->Property_Owner__c,
            'GPX_Member__c' => $cid,
            'Resort__c' => $rid,
            'Resort_Name__c' => str_replace("&", "&amp;", $import['resort_name']),
            'Resort_Unit_Week__c' => $unit_week,
            'Unit_Type__c' => $import['unit_type'],
            'Member_Email__c' => $email,
            'Member_First_Name__c' => $user->FirstName1,
            'Member_Last_Name__c' => $user->LastName1,
            'Credits_Issued__c' => $import['credit_amount'] + $import['credit_used'],
            'Credits_Used__c' => $import['credit_used'],
            'Deposit_Status__c' => $import['status'],
        ];


        $timport['resort_name'] = $resortName;
        $timport['check_in_date'] = date('Y-m-d', strtotime($import['check_in_date']));
        $timport['credit_expiration_date'] = date('Y-m-d', strtotime($import['Credit_expiratio_date']));
        $timport['deposit_year'] = $import['Deposit_year'];
        $timport['unit_type'] = $import['unit_type'];
        $timport['credit_amount'] = $import['credit_amount'];
        $timport['credit_used'] = $import['credit_used'];
        $timport['owner_id'] = $cid;
        $timport['status'] = $import['status'];


        unset($import['resort_id']);
        unset($import['member_Name']);

        $wheres = [];
        foreach ($timport as $k => $v) {
            if ($k == 'status' || $k == 'credit_expiration_date' || $k == 'credit_used') {
                continue;
            }
            $wheres[] = $wpdb->prepare(gpx_esc_table($k) . " = %s", $v);
        }

        $sql = "SELECT id FROM wp_credit WHERE " . implode(" AND ", $wheres);
        $row = $wpdb->get_row($sql);

        if (empty($row)) {
            $wpdb->insert('wp_credit', $timport);
        } else {
            $wpdb->update('wp_credit', $timport, ['id' => $row->id]);
        }

        $sf = Salesforce::getInstance();

        $insertID = $wpdb->insert_id;

        $sfDepositData['GPX_Deposit_ID__c'] = $insertID;

        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';

        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfDepositData;
        $sfFields[0]->type = $sfType;

        $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);

        $wpdb->update('import_owner_credits', ['sfError' => json_encode($sfDepositAdd)], ['ID' => $import['ID']]);

        $record = $sfDepositAdd[0]->id;
        $wpdb->update('wp_credit', ['record_id' => $record, 'sf_name' => $sfDepositAdd[0]->Name], ['id' => $insertID]);

    }
    $sql = "SElECT count(id) as cnt  FROM import_owner_credits WHERE imported=0";
    $remain = $wpdb->get_var($sql);

    if ($remain > 0) {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(['remaining' => $remain]);
}

add_action('wp_ajax_gpx_import_credit_C', 'gpx_import_credit_C');
add_action('wp_ajax_nopriv_gpx_import_credit_C', 'gpx_import_credit_C');

/**
 * Import Credit
 */
function gpx_import_closure_credit() {
    global $wpdb;
    $sf = Salesforce::getInstance();

    $sql = "SElECT * FROM closure_credits_import WHERE imported=0 AND AccoutID != '7100227'  LIMIT 100";
    $imports = $wpdb->get_results($sql, ARRAY_A);

    $nctooc = [
        'Member_Name' => 'AccoutID',
        'check_in_date' => 'CheckIn',
        'unit_type' => 'UnitSize',
        'week_id' => 'Week_ID',
    ];

    foreach ($imports as $import) {

        foreach ($nctooc as $n => $o) {
            $import[$n] = $import[$o];
        }

        $sfImport = $import;

        $user = get_user_by('ID', $import['Member_Name']);


        if (empty($user)) {

            $user = reset(
                get_users(
                    [
                        'meta_key' => 'GPX_Member_VEST__c',
                        'meta_value' => $import['Member_Name'],
                        'number' => 1,
                        'count_total' => false,
                    ]
                )
            );
            if (!empty($user)) {
                $ou = $user->ID;
            } else {
                $user = reset(
                    get_users(
                        [
                            'meta_key' => 'DAEMemberNo',
                            'meta_value' => $import['Member_Name'],
                            'number' => 1,
                            'count_total' => false,
                        ]
                    )
                );

                if (empty($user)) {

                    continue;
                }

            }

        }

        $cid = $user->ID;
        $usermeta = (object) array_map(function ($a) { return $a[0]; }, get_user_meta($cid));

        $rid = $import['Deposit_Resort'];
        $sql = $wpdb->prepare("SELECT ResortName, gprID FROM wp_resorts WHERE id=%s", $rid);
        $resortDets = $wpdb->get_row($sql);
        $resortName = $resortDets->ResortName;


        $rid = $resortDets->gprID;

        if (empty($rid)) {
            $exception = json_encode($import);
            continue;
        }


        $import['credit_amount'] = 1;
        $import['credit_used'] = $import['credit_amount'] - $import['CRBal'];
        $import['Deposit_year'] = '2020';
        $import['status'] = 'Approved';

        $email = $user->Email;

        $accountSQL = "SELECT Name from GPR_Owner_ID__c WHERE GPX_Member_VEST__c='" . $cid . "'";

        $accountResults = $sf->query($accountSQL);

        foreach ($accountResults as $acc) {
            $account = $acc->fields;
            $accountName = $account->Id;
        }

        $sfDepositData = [

            'Check_In_Date__c' => date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c' => date('Y-m-d', strtotime($import['check_in_date'] . '+2 years')),
            'Deposit_Year__c' => $import['Deposit_year'],
            'GPX_Member__c' => $cid,
            'Resort__c' => $rid,
            'Resort_Name__c' => stripslashes(str_replace("&", "&amp;", $resortName)),
            'Resort_Unit_Week__c' => $unit_week,
            'Unit_Type__c' => $import['unit_type'],
            'Member_Email__c' => $email,
            'Member_First_Name__c' => str_replace("&", " AND ", $user->FirstName1),
            'Member_Last_Name__c' => $user->LastName1,
            'Credits_Issued__c' => $import['credit_amount'],
            'Credits_Used__c' => $import['credit_used'],
            'Deposit_Status__c' => $import['status'],
            'Coupon__c' => $import['Couponcode'],
        ];

        $timport['resort_name'] = $resortName;
        $timport['check_in_date'] = date('Y-m-d', strtotime($import['check_in_date']));
        $timport['credit_expiration_date'] = date('Y-m-d', strtotime($import['check_in_date'] . '+2 years'));
        $timport['deposit_year'] = $import['Deposit_year'];
        $timport['unit_type'] = $import['unit_type'];
        $timport['credit_amount'] = $import['credit_amount'];
        $timport['credit_used'] = $import['credit_used'];
        $timport['owner_id'] = $cid;
        $timport['status'] = $import['status'];


        unset($import['resort_id']);
        unset($import['member_Name']);

        $twheres = [
            'resort_name',
            'owner_id',
            'check_in_date',
            'deposit_year',
        ];
        $iwheres = [];
        foreach ($twheres as $tw) {
            $iwheres[] = $wpdb->prepare(gpx_esc_table($tw) . " = %s", $timport[$tw]);
        }
        $twhere = implode(" AND ", $iwheres);
        $sql = "SELECT id FROM wp_credit WHERE " . $twhere;
        // @TODO is this exit supposed to be here?
        exit;

        $isCredit = $wpdb->get_row($sql);

        $sf = Salesforce::getInstance();

        $sfDepositData['GPX_Deposit_ID__c'] = $insertID;

        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';

        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfDepositData;
        $sfFields[0]->type = $sfType;

        $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);

        $record = $sfDepositAdd[0]->id;

        $wpdb->update('wp_credit', ['record_id' => $record, 'sf_name' => $sfDepositAdd[0]->Name], ['id' => $insertID]);
        $wpdb->update('closure_credits_import', ['new_id' => $insertID, 'imported' => '1'], ['id' => $import['id']]);
    }
    $sql = "SELECT count(id) as cnt FROM closure_credits_import WHERE imported=2";
    $remain = $wpdb->get_var($sql);

    wp_send_json(['remaining' => $remain]);
}

add_action('wp_ajax_gpx_import_closure_credit', 'gpx_import_closure_credit');

/**
 * Import Credit
 */
function gpx_import_credit_rework($single = '') {
    global $wpdb;
    $sf = Salesforce::getInstance();

    $sql = "SELECT * FROM import_credit_future_stay WHERE ID NOT IN (SELECT a.ID FROM `import_credit_future_stay` a
            INNER JOIN wp_gpxTransactions b on b.weekId=a.week_id) LIMIT 50";

    $limit = 100;
    $sql = "SELECT b.* FROM wp_credit a
    INNER JOIN `import_credit_future_stay` b ON b.Member_Name=a.owner_id AND a.deposit_year=b.Deposit_year
    WHERE a.record_id IS NULL and a.status != 'DOE' and a.created_date < '2021-01-01' AND b.imported=1 AND sfError=''
    LIMIT 100";

    $imports = $wpdb->get_results($sql, ARRAY_A);

    foreach ($imports as $import) {
        $weekID = $import['week_id'];
        $resortID = $import['missing_resort_id'];
        if ($resortID == "NULL") {
            $resortID = '';
        }

        $sfImport = $import;

        $user = get_user_by('ID', $import['Member_Name']);

        if (empty($user)) {
            $wpdb->update('import_owner_credits', ['imported' => 2], ['ID' => $import['ID']]);
            continue;
        }

        $cid = $user->ID;
        $unit_week = '';
        $rid = '';

        if (!empty($import['missing_resort_id'])) {
            $sql = $wpdb->prepare("SELECT gprID, ResortName FROM wp_resorts WHERE id=%s", $import['missing_resort_id']);
            $resortInfo = $wpdb->get_row($sql);
            $rid = $resortInfo->gprID;
            $import['resort_name'] = $resortInfo->ResortName;
        }
        if (empty($rid)) {
            $resortName = $import['resort_name'];
            $resortName = str_replace("- VI", "", $resortName);
            $resortName = trim($resortName);
            $sql = $wpdb->prepare("SELECT gprID, ResortName FROM wp_resorts WHERE ResortName=%s", $resortName);

            $resortInfo = $wpdb->get_row($sql);
            $rid = $resortInfo->gprID;
            $import['resort_name'] = $resortInfo->ResortName;


            if (!empty($rid)) {
                $sql = "SELECT unitweek FROM wp_mapuser2oid WHERE gpx_user_id='" . $cid . "' AND resortID='" . substr($rid, 0, 15) . "'";
            } else {
                //pull from the transaction
                $sql = $wpdb->prepare("SELECT b.gprID, b.ResortName FROM wp_gpxTransactions a
                        INNER JOIN wp_resorts b on a.resortId=b.ResortID
                        WHERE a.weekId=%s AND a.userID=%s", [$import['week_id'], $cid]);
                $resortInfo = $wpdb->get_row($sql);

                $rid = $resortInfo->ResortName;
                $import['resort_name'] = $resortInfo->ResortName;
                if (empty($rid)) {
                    $exception = json_encode($import);
                    $wpdb->update('import_owner_credits', ['imported' => 3], ['ID' => $import['ID']]);
                    continue;
                }
            }
        }

        $email = $user->Email;
        if (empty($email)) {
            $email = $users->user_email;
        }

        $accountSQL = "SELECT Name from GPR_Owner_ID__c WHERE GPX_Member_VEST__c='" . $cid . "'";

        $accountResults = $sf->query($accountSQL);

        foreach ($accountResults as $acc) {
            $account = $acc->fields;
            $accountName = $account->Name;
            $accountID = $account->ID;
        }

        $plus = '2';
        if ($import['extended'] != '#N/A') {
            $plus = '3';
        }
        $sfDepositData = [

            'Check_In_Date__c' => date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c' => date('Y-m-d', strtotime($import['check_in_date'] . '+' . $plus . ' year')),
            'Deposit_Year__c' => $import['Deposit_year'],
            'GPX_Member__c' => $cid,
            'Resort__c' => $rid,
            'Resort_Name__c' => stripslashes(str_replace("&", "&amp;", $import['resort_name'])),
            'Resort_Unit_Week__c' => $unit_week,
            'Unit_Type__c' => $import['unit_type'],
            'Member_Email__c' => $email,
            'Member_First_Name__c' => stripslashes(str_replace("&", "&amp;", $user->FirstName1)),
            'Member_Last_Name__c' => stripslashes(str_replace("&", "&amp;", $user->LastName1)),
            'Credits_Issued__c' => $import['credit_amount'],
            'Credits_Used__c' => $import['credit_used'],
            'Deposit_Status__c' => $import['status'],
        ];
        $timport['resort_name'] = $resortName;
        $timport['check_in_date'] = date('Y-m-d', strtotime($import['check_in_date']));
        $timport['credit_expiration_date'] = date('Y-m-d', strtotime($import['check_in_date'] . '+1 year'));
        $timport['deposit_year'] = $import['Deposit_year'];
        $timport['unit_type'] = $import['unit_type'];
        $timport['credit_amount'] = $import['credit_amount'];
        $timport['credit_used'] = $import['credit_used'];
        $timport['owner_id'] = $cid;
        $timport['status'] = $import['status'];

        unset($import['resort_id']);
        unset($import['member_Name']);

        $updateCheck = [
            'check_in_date',
            'deposit_year',
            'unit_type',
            'credit_amount',
            'credit_used',
            'owner_id',
            'status',
        ];


        $iwheres = [];
        foreach ($updateCheck as $tw) {
            $iwheres[] = $wpdb->prepare(gpx_esc_table($tw) . " = %s", $timport[$tw]);
        }

        $twhere = implode(" AND ", $iwheres);
        $sql = "SELECT id FROM wp_credit WHERE " . $twhere;
        $isCredit = $wpdb->get_row($sql);

        $sfUpdate = '1';
        if (!empty($isCredit)) {
            $wpdb->update('wp_credit', $timport, ['id' => $isCredit->id]);
            $insertID = $isCredit->id;
        } else {
            $wpdb->insert('wp_credit', $timport);
            $insertID = $wpdb->insert_id;
            $sfUpdate = $insertID;
        }

        $sfDepositData['GPX_Deposit_ID__c'] = $insertID;

        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';

        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfDepositData;
        $sfFields[0]->type = $sfType;

        if (!empty($sfUpdate)) {
            $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);
        }

        $wpdb->update('import_credit_future_stay', ['sfError' => json_encode($sfDepositAdd)], ['ID' => $import['ID']]);
        $record = $sfDepositAdd[0]->id;

        $wpdb->update('wp_credit', ['record_id' => $record, 'sf_name' => $sfDepositAdd[0]->Name], ['id' => $insertID]);
    }


    $sql = "SELECT COUNT(a.ID) as cnt FROM `import_credit_future_stay` a
    INNER JOIN wp_credit b ON a.Member_Name=b.owner_id AND b.deposit_year=a.Deposit_year
    WHERE record_id IS NULL and b.status != 'DOE' and b.created_date < '2021-01-01' AND a.imported=1 AND sfError=''";
    $remain = $wpdb->get_var($sql);

    if ($remain > 0) {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(['credit' => $insertID, 'remaining' => $remain]);
}

add_action('wp_ajax_gpx_credit_to_sf', 'gpx_import_credit_rework');


function gpx_import_credit($single = '') {
    global $wpdb;
    $sf = Salesforce::getInstance();

    $sql = "SELECT * FROM import_credit_future_stay WHERE ID NOT IN (SELECT a.ID FROM `import_credit_future_stay` a
            INNER JOIN wp_gpxTransactions b on b.weekId=a.week_id)";
    $imports = $wpdb->get_results($sql, ARRAY_A);

    foreach ($imports as $import) {

        $weekID = $import['week_id'];
        $resortID = $import['missing_resort_id'];
        if ($resortID == "NULL") {
            $resortID = '';
        }


        $wpdb->update('import_credit_future_stay', ['imported' => 5], ['ID' => $import['ID']]);
        $sfImport = $import;

        $user = get_user_by('ID', $import['Member_Name']);

        if (empty($user)) {
            $wpdb->update('import_credit_future_stay', ['imported' => 2], ['ID' => $import['ID']]);
            continue;
        }

        $cid = $user->ID;
        $unit_week = '';
        $rid = '';

        $email = $user->Email;

        if (empty($email)) {
            $email = $users->user_email;
        }

        $accountSQL = "SELECT Name from GPR_Owner_ID__c WHERE GPX_Member_VEST__c='" . $cid . "'";

        $accountResults = $sf->query($accountSQL);

        foreach ($accountResults as $acc) {
            $account = $acc->fields;
            $accountName = $account->Name;
            $accountID = $account->ID;
        }

        $plus = '2';
        if ($import['extended'] != '#N/A') {
            $plus = '3';
        }
        $sfDepositData = [
            'Check_In_Date__c' => date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c' => date('Y-m-d', strtotime($import['check_in_date'] . '+' . $plus . ' year')),
            'Deposit_Year__c' => $import['Deposit_year'],
            'GPX_Member__c' => $cid,
            'Resort__c' => $rid,
            'Resort_Name__c' => stripslashes(str_replace("&", "&amp;", $import['resort_name'])),
            'Resort_Unit_Week__c' => $unit_week,
            'Unit_Type__c' => $import['unit_type'],
            'Member_Email__c' => $email,
            'Member_First_Name__c' => str_replace("&", "&amp;", $user->FirstName1),
            'Member_Last_Name__c' => $user->LastName1,
            'Credits_Issued__c' => $import['credit_amount'],
            'Credits_Used__c' => $import['credit_used'],
            'Deposit_Status__c' => $import['status'],
        ];
        $timport['resort_name'] = $resortName;
        $timport['check_in_date'] = date('Y-m-d', strtotime($import['check_in_date']));
        $timport['credit_expiration_date'] = date('Y-m-d', strtotime($import['check_in_date'] . '+1 year'));
        $timport['deposit_year'] = $import['Deposit_year'];
        $timport['unit_type'] = $import['unit_type'];
        $timport['credit_amount'] = $import['credit_amount'];
        $timport['credit_used'] = $import['credit_used'];
        $timport['owner_id'] = $cid;
        $timport['status'] = $import['status'];

        unset($import['resort_id']);
        unset($import['member_Name']);

        $updateCheck = [
            'check_in_date',
            'deposit_year',
            'unit_type',
            'credit_amount',
            'credit_used',
            'owner_id',
            'status',
        ];


        $iwheres = [];
        foreach ($updateCheck as $tw) {
            $iwheres[] = $wpdb->prepare(gpx_esc_table($tw) . " = %s", $timport[$tw]);
        }

        $twhere = implode(" AND ", $iwheres);
        $sql = "SELECT id FROM wp_credit WHERE " . $twhere;
        $isCredit = $wpdb->get_row($sql);

        $sfUpdate = '1';
        if (!empty($isCredit)) {
            $wpdb->update('wp_credit', $timport, ['id' => $isCredit->id]);
            $insertID = $isCredit->id;
        } else {
            $wpdb->insert('wp_credit', $timport);
            $insertID = $wpdb->insert_id;
            $sfUpdate = $insertID;
        }

        $sfDepositData['GPX_Deposit_ID__c'] = $insertID;

        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';

        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfDepositData;
        $sfFields[0]->type = $sfType;

        if (!empty($sfUpdate)) {
            $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);
        }

        $record = $sfDepositAdd[0]->id;

        $wpdb->update('wp_credit', ['record_id' => $record, 'sf_name' => $sfDepositAdd[0]->Name], ['id' => $insertID]);
        $wpdb->update('import_credit_future_stay', ['new_id' => $wpdb->insert_id], ['id' => $import['id']]);

        $sql = $wpdb->prepare("SELECT id, data FROM wp_gpxTransactions WHERE weekId=%s AND userID=%s", [
            $import['week_id'],
            $cid,
        ]);
        $trans = $wpdb->get_row($sql);

        if (!empty($trans)) {
            $transData = json_decode($trans->data, true);

            $sfData['GPXTransaction__c'] = $trans->id;
            $sfData['GPX_Deposit__c'] = $record;

            $sfWeekAdd = '';
            $sfAdd = '';
            $sfType = 'GPX_Transaction__c';
            $sfObject = 'GPXTransaction__c';

            $sfFields = [];
            $sfFields[0] = new SObject();
            $sfFields[0]->fields = $sfData;
            $sfFields[0]->type = $sfType;

            if (!empty($sfUpdate)) {
                $sfUpdateTransaction = $sf->gpxUpsert($sfObject, $sfFields);
            }

            $transData['creditweekid'] = $insertID;

            $wpdb->update('wp_gpxTransactions', [
                'depositID' => $insertID,
                'data' => json_encode($transData),
            ], ['id' => $trans->id]);
        }
    }


    $sql = "SELECT COUNT(ID) as cnt FROM import_credit_future_stay WHERE ID NOT IN (SELECT a.ID FROM `import_credit_future_stay` a
            INNER JOIN wp_gpxTransactions b on b.weekId=a.week_id)";
    $remain = $wpdb->get_var($sql);

    wp_send_json(['remaining' => $remain]);
}

add_action('wp_ajax_gpx_import_credit', 'gpx_import_credit_C');


function gpx_import_credit_future_stay($single = '') {
    global $wpdb;
    $sf = Salesforce::getInstance();

    $sql = "SElECT * FROM import_credit_future_stay WHERE imported=0";
    $imports = $wpdb->get_results($sql, ARRAY_A);

    foreach ($imports as $import) {

        $weekID = $import['week_id'];
        $resortID = $import['missing_resort_id'];
        if ($resortID == "NULL") {
            $resortID = '';
        }

        $wpdb->update('import_credit_future_stay', ['imported' => 5], ['ID' => $import['ID']]);
        $sfImport = $import;

        $user = get_user_by('ID', $import['Member_Name']);

        if (empty($user)) {
            $wpdb->update('import_credit_future_stay', ['imported' => 2], ['ID' => $import['ID']]);
            continue;
        }

        $cid = $user->ID;
        $unit_week = '';
        $rid = '';


        $email = $user->Email;
        if (empty($email)) {
            $email = $users->user_email;
        }

        $accountSQL = "SELECT Name from GPR_Owner_ID__c WHERE GPX_Member_VEST__c='" . $cid . "'";

        $accountResults = $sf->query($accountSQL);

        foreach ($accountResults as $acc) {
            $account = $acc->fields;
            $accountName = $account->Name;
            $accountID = $account->ID;
        }

        $plus = '2';
        if ($import['extended'] != '#N/A') {
            $plus = '3';
        }
        $sfDepositData = [
            'Check_In_Date__c' => date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c' => date('Y-m-d', strtotime($import['check_in_date'] . '+' . $plus . ' year')),
            'Deposit_Year__c' => $import['Deposit_year'],
            'GPX_Member__c' => $cid,
            'Resort__c' => $rid,
            'Resort_Name__c' => stripslashes(str_replace("&", "&amp;", $import['resort_name'])),
            'Resort_Unit_Week__c' => $unit_week,
            'Unit_Type__c' => $import['unit_type'],
            'Member_Email__c' => $email,
            'Member_First_Name__c' => str_replace("&", "&amp;", $user->FirstName1),
            'Member_Last_Name__c' => $user->LastName1,
            'Credits_Issued__c' => $import['credit_amount'],
            'Credits_Used__c' => $import['credit_used'],
            'Deposit_Status__c' => $import['status'],
        ];
        $timport['resort_name'] = $resortName;
        $timport['check_in_date'] = date('Y-m-d', strtotime($import['check_in_date']));
        $timport['credit_expiration_date'] = date('Y-m-d', strtotime($import['check_in_date'] . '+1 year'));
        $timport['deposit_year'] = $import['Deposit_year'];
        $timport['unit_type'] = $import['unit_type'];
        $timport['credit_amount'] = $import['credit_amount'];
        $timport['credit_used'] = $import['credit_used'];
        $timport['owner_id'] = $cid;
        $timport['status'] = $import['status'];

        unset($import['resort_id']);
        unset($import['member_Name']);

        $updateCheck = [
            'check_in_date',
            'deposit_year',
            'unit_type',
            'credit_amount',
            'credit_used',
            'owner_id',
            'status',
        ];


        $iwheres = [];
        foreach ($updateCheck as $tw) {
            $iwheres[] = $wpdb->prepare(gpx_esc_table($tw) . "= %s", $timport[$tw]);
        }

        $twhere = implode(" AND ", $iwheres);
        $sql = "SELECT id FROM wp_credit WHERE " . $twhere;
        $isCredit = $wpdb->get_row($sql);

        $sfUpdate = '1';
        if (!empty($isCredit)) {
            $wpdb->update('wp_credit', $timport, ['id' => $isCredit->id]);
            $insertID = $isCredit->id;
        } else {
            $wpdb->insert('wp_credit', $timport);
            $insertID = $wpdb->insert_id;
            $sfUpdate = $insertID;
        }

        $sfDepositData['GPX_Deposit_ID__c'] = $insertID;

        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';

        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfDepositData;
        $sfFields[0]->type = $sfType;

        if (!empty($sfUpdate)) {
            $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);
        }

        $record = $sfDepositAdd[0]->id;

        $wpdb->update('wp_credit', ['record_id' => $record, 'sf_name' => $sfDepositAdd[0]->Name], ['id' => $insertID]);
        $wpdb->update('import_credit_future_stay', ['new_id' => $wpdb->insert_id], ['id' => $import['id']]);

        $sql = $wpdb->prepare("SELECT id, data FROM wp_gpxTransactions WHERE weekId=%s AND userID=%s", [
            $import['week_id'],
            $cid,
        ]);
        $trans = $wpdb->get_row($sql);

        if (!empty($trans)) {
            $transData = json_decode($trans->data, true);

            $sfData['GPXTransaction__c'] = $trans->id;
            $sfData['GPX_Deposit__c'] = $record;

            $sfWeekAdd = '';
            $sfAdd = '';
            $sfType = 'GPX_Transaction__c';
            $sfObject = 'GPXTransaction__c';

            $sfFields = [];
            $sfFields[0] = new SObject();
            $sfFields[0]->fields = $sfData;
            $sfFields[0]->type = $sfType;

            if (!empty($sfUpdate)) {
                $sfUpdateTransaction = $sf->gpxUpsert($sfObject, $sfFields);
            }

            $transData['creditweekid'] = $insertID;

            $wpdb->update('wp_gpxTransactions', [
                'depositID' => $insertID,
                'data' => json_encode($transData),
            ], ['id' => $trans->id]);
        }
    }

    $sql = "SELECT COUNT(ID) as cnt FROM import_credit_future_stay WHERE imported=0";
    $remain = $wpdb->get_var($sql);

    wp_send_json(['remaining' => $remain]);
}

add_action('wp_ajax_gpx_import_credit_future_stay', 'gpx_import_credit_future_stay');


function gpx_missed_credit_to_sf() {
    global $wpdb;
    $sf = Salesforce::getInstance();

    $sql = "SELECT * FROM `wp_credit` WHERE `record_id` IS NULL AND `status` != 'DOE'";
    $rows = $wpdb->get_results($sql, ARRAY_A);

    if (!empty($rows)) {
        foreach ($rows as $import) {

            $sfDepositData = [
                'GPX_Deposit_ID__c ' => $import['id'],
                'Check_In_Date__c' => date('Y-m-d', strtotime($import['check_in_date'])),
                'Expiration_Date__c' => date('Y-m-d', strtotime($import['check_in_date'] . '+' . $plus . ' year')),
                'Deposit_Year__c' => $import['Deposit_year'],

                'GPX_Member__c' => $import['owner_id'],

                'Resort_Name__c' => stripslashes(str_replace("&", "&amp;", $import['resort_name'])),
                'Unit_Type__c' => $import['unit_type'],

                'Credits_Used__c' => $import['credit_used'],
                'Deposit_Status__c' => $import['status'],
            ];

            if (!empty($import['credit_amount'])) {
                $sfDepositData['Credits_Issued__c'] = $import['credit_amount'];
            }

            $sfType = 'GPX_Deposit__c';
            $sfObject = 'GPX_Deposit_ID__c';

            $sfFields = [];
            $sfFields[0] = new SObject();
            $sfFields[0]->fields = $sfDepositData;
            $sfFields[0]->type = $sfType;

            //add minimal details just to get it in there
            $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);

            $record = $sfDepositAdd[0]->id;

            $wpdb->update('wp_credit', [
                'record_id' => $record,
                'sf_name' => $sfDepositAdd[0]->Name,
            ], ['id' => $import['id']]);

            $user = get_user_by('ID', $import['owner_id']);

            $sfDepositData = [];
            $sfDepositData['GPX_Deposit_ID__c '] = $import['id'];
            if (!empty($user)) {
                $email = $user->Email;
                if (empty($email)) {
                    $email = $users->user_email;
                }
                $sfDepositData['Member_First_Name__c'] = stripslashes(str_replace("&", "&amp;", $user->FirstName1));
                $sfDepositData['Member_Last_Name__c'] = stripslashes(str_replace("&", "&amp;", $user->LastName1));
                $sfDepositData['Member_Email__c'] = $email;

                $sfFields = [];
                $sfFields[0] = new SObject();
                $sfFields[0]->fields = $sfDepositData;
                $sfFields[0]->type = $sfType;

                //add the name
                $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);

            }
            unset($sfDepositData['Member_First_Name__c']);
            unset($sfDepositData['Member_Last_Name__c']);
            unset($sfDepositData['Member_Email__c']);

            $resortName = $import['resort_name'];
            $resortName = str_replace("- VI", "", $resortName);
            $resortName = trim($resortName);
            $sql = $wpdb->prepare("SELECT gprID, ResortName FROM wp_resorts WHERE ResortName=%s", $resortName);

            $resortInfo = $wpdb->get_row($sql);

            if (!empty($resortInfo->gprID)) {
                $sfDepositData['Resort__c'] = $resortInfo->gprID;

                $sfFields = [];
                $sfFields[0] = new SObject();
                $sfFields[0]->fields = $sfDepositData;
                $sfFields[0]->type = $sfType;

                //add the resortid
                $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);

            }

        }
    }

    wp_send_json(['added' => '']);
}

add_action('wp_ajax_gpx_missed_credit_to_sf', 'gpx_missed_credit_to_sf');


function gpx_import_transactions_manual($table = 'transactions_import_two', $id = '', $resort = '') {
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $table = 'transactions_import';
    $tt = 'transaction1';
    if ($_GET['table'] == 'two') {
        $table = 'transactions_import_two';
        $tt = 'transaction2';
    }
    if ($_GET['table'] == 'owner') {
        $table = 'transactions_import_owner';
        $tt = 'transactionOwner';
    }

    if ($_GET['table'] == 'two') {
        $table = 'transactions_import_two';
        $tt = 'transaction2';
    }
    if ($_GET['table'] == 'owner') {
        $table = 'transactions_import_owner';
        $tt = 'transactionOwner';
    }

    $where = 'imported=0';
    if (!empty($id)) {
        $where = $wpdb->prepare('id=%d', $id);
    }

    $sql = "SELECT * FROM " . gpx_esc_table($table) . " WHERE " . $where . " ORDER BY RAND() LIMIT 40";
    $rows = $wpdb->get_results($sql);

    foreach ($rows as $row) {

        $wpdb->update($table, ['imported' => 2], ['id' => $row->id]);


        if ($row->GuestName == '#N/A') {
            $exception = json_encode($row);
            $wpdb->insert("final_import_exceptions", ['type' => $tt . ' guest formula error', 'data' => $exception]);
            continue;
        }

        $resortKeyOne = [
            'Butterfield Park - VI' => '2440',
            'Grand Palladium White Sand - AI' => '46895',
            'Grand Sirenis Riviera Maya Resort - AI' => '46896',
            'High Point World Resort - RHC' => '1549',
            'Los Abrigados Resort & Spa' => '2467',
            'Makai Club Cottages' => '1786',
            'Palm Canyon Resort & Spa' => '1397',
            'Sunset Marina Resort & Yacht Club - AI' => '46897',
            'Azul Beach Resort Negril by Karisma - AI' => '46898',
            'Bali Villas & Sports Club - Rentals Only' => '46899',
            'Blue Whale' => '46900',
            'Bluegreen Club 36' => '46901',
            'BreakFree Alexandra Beach' => '46902',
            'Classic @ Alpha Sovereign Hotel' => '46903',
            'Club Regina Los Cabos' => '46904',
            'Eagles Nest Resort - VI' => '1836',
            'El Dorado Casitas Royale by Karisma' => '46905',
            'El Dorado Casitas Royale by Karisma, a Gourmet AIl Inclusive' => '46905',
            'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive' => '46905',
            'El Dorado Maroma by Karisma, a Gourmet AI' => '46906',
            'El Dorado Maroma by Karisma a Gourmet AI' => '46906',
            'El Dorado Royale by Karisma a Gourmet AI' => '46907',
            'El Dorado Royale by Karisma, a Gourmet AI' => '46907',
            'Fiesta Ameri. Vac Club At Cabo Del Sol' => '46908',
            'Fort Brown Condo Shares' => '46909',
            'Four Seasons Residence Club Scottsdale@Troon North' => '2457',
            'Generations Riviera Maya by Karisma a Gourmet AI' => '46910',
            'GPX Cruise Exchange' => 'SKIP',
            'Grand Palladium Jamaica Resort & Spa - AI' => '46911',
            'Grand Palladium Vallarta Resort & Spa - AI' => '46912',
            'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive' => '46913',
            'High Sierra Condominiums' => '46914',
            'Kiltannon Home Farm' => '46915',
            'Knocktopher Abbey' => '46916',
            'Knocktopher Abbey (Shadowed)' => '46916',
            'Laguna Suites Golf and Spa - AI' => '46917',
            'Maison St. Charles - Rentals Only' => '46918',
            'Makai Club Resort' => '1787',
            'Marina Del Rey Beach Club - No Longer Accepting' => '46919',
            'Mantra Aqueous on Port' => '46920',
            'Maui Sunset - Rentals Only' => '1758',
            'Mayan Palace Mazatlan' => '3652',
            'Ocean Gate Resort' => '46921',
            'Ocean Spa Hotel - AI' => '46922',
            'Paradise' => '46923',
            'Park Royal Homestay Club Cala' => '338',
            'Park Royal Los Cabos - RHC' => '46924',
            'Peacock Suites Resort' => '46925',
            'Pounamu Apartments - Rental' => '46926',
            'Presidential Suites by LHVC - Punta Cana NON - AI' => '46927',
            'RHC - Park Royal - Los Tules' => '46928',
            'Royal Regency Paris (Shadowed)' => '479',
            'Royal Sunset - AI' => '46929',
            'Secrets Puerto Los Cabos Golf & Spa Resort - AI' => '46930',
            'Secrets Wild Orchid Montego Bay - AI' => '46931',
            'Solare Bahia Mar - Rentals Only' => '46932',
            'Tahoe Trail - VI' => '40',
            'The RePlay Residence' => '46933',
            'The Tropical at LHVC - AI' => '46934',
            'Vacation Village at Williamsburg' => '2432',
            'Wolf Run Manor At Treasure Lake' => '46935',
            'Wyndham Grand Desert - 3 Nights' => '46936',
            'Wyndham Royal Garden at Waikiki - Rental Only' => '1716',
        ];

        $resortKeyTwo = [
            'Royal Aloha Chandler - Butterfield Park' => '2440',
            'Grand Palladium White Sand - AI' => '46895',
            'Grand Sirenis Riviera Maya Resort - AI' => '46896',
            'High Point World Resort' => '1549',
            'Los Abrigados Resort and Spa' => '2467',
            'Makai Club Resort Cottages' => '1786',
            'Palm Canyon Resort and Spa' => '1397',
            'Sunset Marina Resort & Yacht Club - AI' => '46897',
            'Azul Beach Resort Negril by Karisma - AI' => '46898',
            'Bali Villas & Sports Club - Rentals Only' => '46899',
            'Blue Whale' => '46900',
            'Bluegreen Club 36' => '46901',
            'BreakFree Alexandra Beach' => '46902',
            'Classic @ Alpha Sovereign Hotel' => '46903',
            'Club Regina Los Cabos' => '46904',
            'Royal Aloha Branson - Eagles Nest Resort' => '1836',
            'El Dorado Casitas Royale by Karisma, a Gourmet AIl Inclusive' => '46905',
            'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive' => '46905',
            'El Dorado Maroma by Karisma a Gourmet AI' => '46906',
            'El Dorado Royale by Karisma a Gourmet AI' => '46907',
            'Fiesta Ameri. Vac Club At Cabo Del Sol' => '46908',
            'Fort Brown Condo Shares' => '46909',
            'Four Seasons Residence Club Scottsdale at Troon North' => '2457',
            'Generations Riviera Maya by Karisma a Gourmet AI' => '46910',
            'SKIP' => 'SKIP',
            'Grand Palladium Jamaica Resort & Spa - AI' => '46911',
            'Grand Palladium Vallarta Resort & Spa - AI' => '46912',
            'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive' => '46913',
            'High Sierra Condominiums' => '46914',
            'Kiltannon Home Farm' => '46915',
            'Knocktopher Abbey' => '46916',
            'Laguna Suites Golf and Spa - AI' => '46917',
            'Maison St. Charles - Rentals Only' => '46918',
            'Makai Club Resort Condos' => '1787',
            'Marina Del Rey Beach Club - No Longer Accepting' => '46919',
            'Mantra Aqueous on Port' => '46920',
            'Maui Sunset' => '1758',
            'Mayan Palace Mazatlan by Grupo Vidanta' => '3652',
            'Ocean Gate Resort' => '46921',
            'Ocean Spa Hotel - AI' => '46922',
            'Paradise' => '46923',
            'Royal Holiday - Park Royal Club Cala' => '338',
            'Park Royal Los Cabos - RHC' => '46924',
            'Peacock Suites Resort' => '46925',
            'Pounamu Apartments - Rental' => '46926',
            'Presidential Suites by LHVC - Punta Cana NON - AI' => '46927',
            'RHC - Park Royal - Los Tules' => '46928',
            'Royal Regency By Diamond Resorts' => '479',
            'Royal Sunset - AI' => '46929',
            'Secrets Puerto Los Cabos Golf & Spa Resort - AI' => '46930',
            'Secrets Wild Orchid Montego Bay - AI' => '46931',
            'Solare Bahia Mar - Rentals Only' => '46932',
            'Royal Aloha Tahoe' => '40',
            'The RePlay Residence' => '46933',
            'The Tropical at LHVC - AI' => '46934',
            'Williamsburg Plantation Resort' => '2432',
            'Wolf Run Manor At Treasure Lake' => '46935',
            'Wyndham Grand Desert - 3 Nights' => '46936',
            'Royal Garden at Waikiki Resort' => '1716',
        ];
        $resortMissing = '';
        if (array_key_exists($row->Resort_Name, $resortKeyOne)) {
            $resortMissing = $resortKeyOne[$row->Resort_Name];
            if ($resort == 'SKIP') {
                continue;
            }
        }
        if (array_key_exists($row->Resort_Name, $resortKeyTwo)) {
            $resortMissing = $resortKeyTwo[$row->Resort_Name];
            if ($resort == 'SKIP') {
                continue;
            }
        }
        if (!empty($resortMissing)) {
            $sql = $wpdb->prepare("SELECT id, resortID, ResortName FROM wp_resorts WHERE id=%s", $resortMissing);
            $resort = $wpdb->get_row($sql);
            $resortName = $resort->ResortName;
        } else {
            $resortName = $row->Resort_Name;
            $resortName = str_replace("- VI", "", $resortName);
            $resortName = trim($resortName);
            $sql = $wpdb->prepare("SELECT id, resortID FROM wp_resorts WHERE ResortName=%s", $resortName);
            $resort = $wpdb->get_row($sql);
        }

        if (empty($resort)) {
            $sql = $wpdb->prepare("SELECT missing_resort_id FROM import_credit_future_stay WHERE resort_name=%s", $resortName);
            $resort_ID = $wpdb->get_var($sql);

            $sql = $wpdb->prepare("SELECT id, resortID, ResortName FROM wp_resorts WHERE id=%s", $resort_ID);
            $resort = $wpdb->get_row($sql);
            $resortID = $resort->resortID;
            $resortName = $resort->ResortName;


        } else {
            $resortID = $resort->id;
            $daeResortID = $resort->resortID;
        }

        if (empty($resort)) {
            $exception = json_encode($row);
            $wpdb->insert("final_import_exceptions", ['type' => $tt . ' resort', 'data' => $exception]);
            continue;
        }

        $sql = $wpdb->prepare("SELECT user_id FROM wp_GPR_Owner_ID__c WHERE user_id=%s", $row->MemberNumber);
        $user = $wpdb->get_var($sql);

        if (empty($user)) {
            //let's try to import this owner
            $user = function_GPX_Owner($row->MemberNumber);

            if (empty($user)) {
                $exception = json_encode($row);
                $wpdb->insert("final_import_exceptions", ['type' => $tt . ' user', 'data' => $exception]);
                continue;
            }
        } else {
            $userID = $user;

            $sql = $wpdb->prepare("SELECT name FROM wp_partner WHERE user_id=%s", $userID);
            $memberName = $wpdb->get_var($sql);

            if (empty($memberName)) {
                $fn = get_user_meta($userID, 'first_name', true);

                if (empty($fn)) {
                    $fn = get_user_meta($userID, 'FirstName1', true);
                }
                $ln = get_user_meta($userID, 'last_name', true);
                if (empty($ln)) {
                    $ln = get_user_meta($userID, 'LastName1', true);
                }
                if (!empty($fn) || !empty($ln)) {
                    $memberName = $fn . " " . $ln;
                } else {
                    $exception = json_encode($row);
                    $wpdb->insert("final_import_exceptions", ['type' => $tt . ' member name', 'data' => $exception]);
                    continue;
                }
            }
        }

        $unitType = $row->Unit_Type;
        $sql = $wpdb->prepare("SELECT record_id FROM wp_unit_type WHERE resort_id=%s AND name=%s", [
            $resortID,
            $unitType,
        ]);
        $unitID = $wpdb->get_var($sql);

        $bs = explode("/", $unitType);
        $beds = $bs[0];
        $beds = str_replace("b", "", $beds);
        if ($beds == 'St') {
            $beds = 'STD';
        }
        $sleeps = $bs[1];
        if (empty($unitID)) {
            $insert = [
                'name' => $unitType,
                'create_date' => date('Y-m-d'),
                'number_of_bedrooms' => $beds,
                'sleeps_total' => $sleeps,
                'resort_id' => $resortID,
            ];
            $wpdb->insert('wp_unit_type', $insert);
            $unitID = $wpdb->insert_id;
        }


        $wp_room = [
            'record_id' => $row->weekId,
            'active_specific_date' => date("Y-m-d 00:00:00", strtotime($row->Rental_Opening_Date)),
            'check_in_date' => date('Y-m-d 00:00:00', strtotime($row->Check_In_Date)),
            'check_out_date' => date('Y-m-d 00:00:00', strtotime($row->Check_In_Date . ' +7 days')),
            'resort' => $resortID,
            'unit_type' => $unitID,
            'source_num' => '1',
            'source_partner_id' => '0',
            'sourced_by_partner_on' => '',
            'resort_confirmation_number' => '',
            'active' => '0',
            'availability' => '1',
            'available_to_partner_id' => '0',
            'type' => '1',
            'active_rental_push_date' => date('Y-m-d', strtotime($row->Rental_Opening_Date)),
            'price' => '0',
            'points' => null,
            'note' => '',
            'given_to_partner_id' => null,
            'import_id' => '0',
            'active_type' => '0',
            'active_week_month' => '0',
            'create_by' => '5',
            'archived' => '0',
        ];

        $sql = $wpdb->prepare("SELECT record_id FROM wp_room WHERE record_id=%s", $row->weekId);
        $week = $wpdb->get_row($sql);
        if (!empty($week)) {
            $wpdb->update('wp_room', $wp_room, ['record_id' => $week]);
        } else {
            $wpdb->insert('wp_room', $wp_room);
        }

        $cpo = "TAKEN";
        if ($row->CPO == 'No') {
            $cpo = "NOT TAKEN";
        }

        $data = [
            "MemberNumber" => $row->MemberNumber,
            "MemberName" => $memberName,
            "GuestName" => $row->GuestName,
            "Adults" => $row->Adults,
            "Children" => $row->Children,
            "UpgradeFee" => $row->actupgradeFee,
            "CPO" => $cpo,
            "CPOFee" => $row->actcpoFee,
            "Paid" => $row->Paid,
            "Balance" => "0",
            "ResortID" => $daeResortID,
            "ResortName" => $row->Resort_Name,
            "room_type" => $row->Unit_Type,
            "WeekType" => $row->WeekTransactionType,
            "sleeps" => $sleeps,
            "bedrooms" => $beds,
            "Size" => $row->Unit_Type,
            "noNights" => "7",
            "checkIn" => date('Y-m-d', strtotime($row->Check_In_Date)),
            "processedBy" => 5,
            'actWeekPrice' => $row->actWeekPrice,
            'actcpoFee' => $row->actcpoFee,
            'actextensionFee' => $row->actextensionFee,
            'actguestFee' => $row->actguestFee,
            'actupgradeFee' => $row->actupgradeFee,
            'acttax' => $row->acttax,
            'actlatedeposit' => $row->actlatedeposit,
        ];

        $wp_gpxTransactions = [
            'transactionType' => 'booking',
            'cartID' => $userID . '-' . $row->weekId,
            'sessionID' => '',
            'userID' => $userID,
            'resortID' => $daeResortID,
            'weekId' => $row->weekId,
            'check_in_date' => date('Y-m-d', strtotime($row->Check_In_Date)),
            'datetime' => date('Y-m-d', strtotime($row->transaction_date)),
            'depositID' => null,
            'paymentGatewayID' => '',
            'transactionRequestId' => null,
            'transactionData' => '',
            'sfid' => '0',
            'sfData' => '',
            'data' => json_encode($data),
        ];

        $transactionID = '';
        $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s AND userID=%s", [
            $row->weekId,
            $userID,
        ]);
        $et = $wpdb->get_var($sql);
        if (!empty($et)) {
            $wpdb->update('wp_gpxTransactions', $wp_gpxTransactions, ['id' => $et]);
            $transactionID = $et;
        } else {
            $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s", $row->weekId);
            $enut = $wpdb->get_var($sql);
            if (empty($enut)) {
                $wpdb->insert('wp_gpxTransactions', $wp_gpxTransactions);
                $transactionID = $wpdb->insert_id;
            } else {
                $exception = json_encode($row);
                $wpdb->insert("final_import_exceptions", [
                    'type' => $tt . ' duplicate week transaction not cancelled',
                    'data' => $exception,
                ]);
                continue;
            }
        }
        if (isset($transactionID) && !empty($transactionID)) {
            TransactionRepository::instance()->send_to_salesforce((int) $transactionID);
        }
    }
    $sql = "SELECT COUNT(id) as cnt FROM " . gpx_esc_table($table) . " WHERE imported=0";
    $remain = $wpdb->get_var($sql);
    if ($remain > 0 && empty($id)) {

        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(['remaining' => $remain]);
}


function gpx_import_transactions($table = 'transactions_import_two', $id = '', $resort = '') {
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $table = 'transactions_import';
    $tt = 'transaction1';
    if ($_GET['table'] == 'two') {
        $table = 'transactions_import_two';
        $tt = 'transaction2';
    }
    if ($_GET['table'] == 'owner') {
        $table = 'transactions_import_owner';
        $tt = 'transactionOwner';
    }

    if ($_GET['table'] == 'two') {
        $table = 'transactions_import_two';
        $tt = 'transaction2';
    }
    if ($_GET['table'] == 'owner') {
        $table = 'transactions_import_owner';
        $tt = 'transactionOwner';
    }
    if ($_GET['id']) {
        $id = $_GET['id'];
    }

    $where = 'imported=0';
    if (!empty($id)) {
        $where = $wpdb->prepare('id=%d', $id);
    }

    $sql = "SELECT * FROM " . gpx_esc_table($table) . " WHERE " . $where . " ORDER BY RAND() LIMIT 40";
    $rows = $wpdb->get_results($sql);

    foreach ($rows as $row) {

        $wpdb->update($table, ['imported' => 2], ['id' => $row->id]);

        if ($row->GuestName == '#N/A') {
            $exception = json_encode($row);
            $wpdb->insert("final_import_exceptions", ['type' => $tt . ' guest formula error', 'data' => $exception]);
            continue;
        }

        $resortKeyOne = [
            'Butterfield Park - VI' => '2440',
            'Grand Palladium White Sand - AI' => '46895',
            'Grand Sirenis Riviera Maya Resort - AI' => '46896',
            'High Point World Resort - RHC' => '1549',
            'Los Abrigados Resort & Spa' => '2467',
            'Makai Club Cottages' => '1786',
            'Palm Canyon Resort & Spa' => '1397',
            'Sunset Marina Resort & Yacht Club - AI' => '46897',
            'Azul Beach Resort Negril by Karisma - AI' => '46898',
            'Bali Villas & Sports Club - Rentals Only' => '46899',
            'Blue Whale' => '46900',
            'Bluegreen Club 36' => '46901',
            'BreakFree Alexandra Beach' => '46902',
            'Classic @ Alpha Sovereign Hotel' => '46903',
            'Club Regina Los Cabos' => '46904',
            'Eagles Nest Resort - VI' => '1836',
            'El Dorado Casitas Royale by Karisma' => '46905',
            'El Dorado Casitas Royale by Karisma, a Gourmet AIl Inclusive' => '46905',
            'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive' => '46905',
            'El Dorado Maroma by Karisma, a Gourmet AI' => '46906',
            'El Dorado Maroma by Karisma a Gourmet AI' => '46906',
            'El Dorado Royale by Karisma a Gourmet AI' => '46907',
            'El Dorado Royale by Karisma, a Gourmet AI' => '46907',
            'Fiesta Ameri. Vac Club At Cabo Del Sol' => '46908',
            'Fort Brown Condo Shares' => '46909',
            'Four Seasons Residence Club Scottsdale@Troon North' => '2457',
            'Generations Riviera Maya by Karisma a Gourmet AI' => '46910',
            'GPX Cruise Exchange' => 'SKIP',
            'Grand Palladium Jamaica Resort & Spa - AI' => '46911',
            'Grand Palladium Vallarta Resort & Spa - AI' => '46912',
            'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive' => '46913',
            'High Sierra Condominiums' => '46914',
            'Kiltannon Home Farm' => '46915',
            'Knocktopher Abbey' => '46916',
            'Knocktopher Abbey (Shadowed)' => '46916',
            'Laguna Suites Golf and Spa - AI' => '46917',
            'Maison St. Charles - Rentals Only' => '46918',
            'Makai Club Resort' => '1787',
            'Marina Del Rey Beach Club - No Longer Accepting' => '46919',
            'Mantra Aqueous on Port' => '46920',
            'Maui Sunset - Rentals Only' => '1758',
            'Mayan Palace Mazatlan' => '3652',
            'Ocean Gate Resort' => '46921',
            'Ocean Spa Hotel - AI' => '46922',
            'Paradise' => '46923',
            'Park Royal Homestay Club Cala' => '338',
            'Park Royal Los Cabos - RHC' => '46924',
            'Peacock Suites Resort' => '46925',
            'Pounamu Apartments - Rental' => '46926',
            'Presidential Suites by LHVC - Punta Cana NON - AI' => '46927',
            'RHC - Park Royal - Los Tules' => '46928',
            'Royal Regency Paris (Shadowed)' => '479',
            'Royal Sunset - AI' => '46929',
            'Secrets Puerto Los Cabos Golf & Spa Resort - AI' => '46930',
            'Secrets Wild Orchid Montego Bay - AI' => '46931',
            'Solare Bahia Mar - Rentals Only' => '46932',
            'Tahoe Trail - VI' => '40',
            'The RePlay Residence' => '46933',
            'The Tropical at LHVC - AI' => '46934',
            'Vacation Village at Williamsburg' => '2432',
            'Wolf Run Manor At Treasure Lake' => '46935',
            'Wyndham Grand Desert - 3 Nights' => '46936',
            'Wyndham Royal Garden at Waikiki - Rental Only' => '1716',
        ];

        $resortKeyTwo = [
            'Royal Aloha Chandler - Butterfield Park' => '2440',
            'Grand Palladium White Sand - AI' => '46895',
            'Grand Sirenis Riviera Maya Resort - AI' => '46896',
            'High Point World Resort' => '1549',
            'Los Abrigados Resort and Spa' => '2467',
            'Makai Club Resort Cottages' => '1786',
            'Palm Canyon Resort and Spa' => '1397',
            'Sunset Marina Resort & Yacht Club - AI' => '46897',
            'Azul Beach Resort Negril by Karisma - AI' => '46898',
            'Bali Villas & Sports Club - Rentals Only' => '46899',
            'Blue Whale' => '46900',
            'Bluegreen Club 36' => '46901',
            'BreakFree Alexandra Beach' => '46902',
            'Classic @ Alpha Sovereign Hotel' => '46903',
            'Club Regina Los Cabos' => '46904',
            'Royal Aloha Branson - Eagles Nest Resort' => '1836',
            'El Dorado Casitas Royale by Karisma, a Gourmet AIl Inclusive' => '46905',
            'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive' => '46905',
            'El Dorado Maroma by Karisma a Gourmet AI' => '46906',
            'El Dorado Royale by Karisma a Gourmet AI' => '46907',
            'Fiesta Ameri. Vac Club At Cabo Del Sol' => '46908',
            'Fort Brown Condo Shares' => '46909',
            'Four Seasons Residence Club Scottsdale at Troon North' => '2457',
            'Generations Riviera Maya by Karisma a Gourmet AI' => '46910',
            'SKIP' => 'SKIP',
            'Grand Palladium Jamaica Resort & Spa - AI' => '46911',
            'Grand Palladium Vallarta Resort & Spa - AI' => '46912',
            'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive' => '46913',
            'High Sierra Condominiums' => '46914',
            'Kiltannon Home Farm' => '46915',
            'Knocktopher Abbey' => '46916',
            'Laguna Suites Golf and Spa - AI' => '46917',
            'Maison St. Charles - Rentals Only' => '46918',
            'Makai Club Resort Condos' => '1787',
            'Marina Del Rey Beach Club - No Longer Accepting' => '46919',
            'Mantra Aqueous on Port' => '46920',
            'Maui Sunset' => '1758',
            'Mayan Palace Mazatlan by Grupo Vidanta' => '3652',
            'Ocean Gate Resort' => '46921',
            'Ocean Spa Hotel - AI' => '46922',
            'Paradise' => '46923',
            'Royal Holiday - Park Royal Club Cala' => '338',
            'Park Royal Los Cabos - RHC' => '46924',
            'Peacock Suites Resort' => '46925',
            'Pounamu Apartments - Rental' => '46926',
            'Presidential Suites by LHVC - Punta Cana NON - AI' => '46927',
            'RHC - Park Royal - Los Tules' => '46928',
            'Royal Regency By Diamond Resorts' => '479',
            'Royal Sunset - AI' => '46929',
            'Secrets Puerto Los Cabos Golf & Spa Resort - AI' => '46930',
            'Secrets Wild Orchid Montego Bay - AI' => '46931',
            'Solare Bahia Mar - Rentals Only' => '46932',
            'Royal Aloha Tahoe' => '40',
            'The RePlay Residence' => '46933',
            'The Tropical at LHVC - AI' => '46934',
            'Williamsburg Plantation Resort' => '2432',
            'Wolf Run Manor At Treasure Lake' => '46935',
            'Wyndham Grand Desert - 3 Nights' => '46936',
            'Royal Garden at Waikiki Resort' => '1716',
        ];
        $resortMissing = '';
        if (array_key_exists($row->Resort_Name, $resortKeyOne)) {
            $resortMissing = $resortKeyOne[$row->Resort_Name];
            if ($resort == 'SKIP') {
                continue;
            }
        }
        if (array_key_exists($row->Resort_Name, $resortKeyTwo)) {
            $resortMissing = $resortKeyTwo[$row->Resort_Name];
            if ($resort == 'SKIP') {
                continue;
            }
        }
        if (!empty($resortMissing)) {
            $sql = $wpdb->prepare("SELECT id, resortID, ResortName FROM wp_resorts WHERE id=%s", $resortMissing);
            $resort = $wpdb->get_row($sql);
            $resortName = $resort->ResortName;
        } else {
            $resortName = $row->Resort_Name;
            $resortName = str_replace("- VI", "", $resortName);
            $resortName = trim($resortName);
            $sql = $wpdb->prepare("SELECT id, resortID FROM wp_resorts WHERE ResortName=%s", $resortName);
            $resort = $wpdb->get_row($sql);
        }

        if (empty($resort)) {
            $sql = $wpdb->prepare("SELECT missing_resort_id FROM import_credit_future_stay WHERE resort_name=%s", $resortName);
            $resort_ID = $wpdb->get_var($sql);

            $sql = $wpdb->prepare("SELECT id, resortID, ResortName FROM wp_resorts WHERE id=%s", $resort_ID);
            $resort = $wpdb->get_row($sql);
            $resortID = $resort->resortID;
            $resortName = $resort->ResortName;


        } else {
            $resortID = $resort->id;
            $daeResortID = $resort->resortID;
        }

        if (empty($resort)) {
            $exception = json_encode($row);
            $wpdb->insert("final_import_exceptions", ['type' => $tt . ' resort', 'data' => $exception]);
            continue;
        }

        $sql = $wpdb->prepare("SELECT user_id FROM wp_GPR_Owner_ID__c WHERE user_id=%s", $row->MemberNumber);
        $user = $wpdb->get_var($sql);

        if (empty($user)) {
            //let's try to import this owner
            $user = function_GPX_Owner($row->MemberNumber);

            if (empty($user)) {
                $exception = json_encode($row);
                $wpdb->insert("final_import_exceptions", ['type' => $tt . ' user', 'data' => $exception]);
                continue;
            }
        } else {
            $userID = $user;

            $sql = $wpdb->prepare("SELECT name FROM wp_partner WHERE user_id=%s", $userID);
            $memberName = $wpdb->get_var($sql);

            if (empty($memberName)) {
                $fn = get_user_meta($userID, 'first_name', true);

                if (empty($fn)) {
                    $fn = get_user_meta($userID, 'FirstName1', true);
                }
                $ln = get_user_meta($userID, 'last_name', true);
                if (empty($ln)) {
                    $ln = get_user_meta($userID, 'LastName1', true);
                }
                if (!empty($fn) || !empty($ln)) {
                    $memberName = $fn . " " . $ln;
                } else {
                    $exception = json_encode($row);
                    $wpdb->insert("final_import_exceptions", ['type' => $tt . ' member name', 'data' => $exception]);
                    continue;
                }
            }
        }

        $unitType = $row->Unit_Type;
        $sql = $wpdb->prepare("SELECT record_id FROM wp_unit_type WHERE resort_id=%s AND name=%s", [
            $resortID,
            $unitType,
        ]);
        $unitID = $wpdb->get_var($sql);

        $bs = explode("/", $unitType);
        $beds = $bs[0];
        $beds = str_replace("b", "", $beds);
        if ($beds == 'St') {
            $beds = 'STD';
        }
        $sleeps = $bs[1];
        if (empty($unitID)) {
            $insert = [
                'name' => $unitType,
                'create_date' => date('Y-m-d'),
                'number_of_bedrooms' => $beds,
                'sleeps_total' => $sleeps,
                'resort_id' => $resortID,
            ];
            $wpdb->insert('wp_unit_type', $insert);
            $unitID = $wpdb->insert_id;
        }


        $wp_room = [
            'record_id' => $row->weekId,
            'active_specific_date' => date("Y-m-d 00:00:00", strtotime($row->Rental_Opening_Date)),
            'check_in_date' => date('Y-m-d 00:00:00', strtotime($row->Check_In_Date)),
            'check_out_date' => date('Y-m-d 00:00:00', strtotime($row->Check_In_Date . ' +7 days')),
            'resort' => $resortID,
            'unit_type' => $unitID,
            'source_num' => '1',
            'source_partner_id' => '0',
            'sourced_by_partner_on' => '',
            'resort_confirmation_number' => '',
            'active' => '0',
            'availability' => '1',
            'available_to_partner_id' => '0',
            'type' => '1',
            'active_rental_push_date' => date('Y-m-d', strtotime($row->Rental_Opening_Date)),
            'price' => '0',
            'points' => null,
            'note' => '',
            'given_to_partner_id' => null,
            'import_id' => '0',
            'active_type' => '0',
            'active_week_month' => '0',
            'create_by' => '5',
            'archived' => '0',
        ];

        $sql = $wpdb->prepare("SELECT record_id FROM wp_room WHERE record_id=%s", $row->weekId);
        $week = $wpdb->get_row($sql);
        if (!empty($week)) {
            $wpdb->update('wp_room', $wp_room, ['record_id' => $week]);
        } else {
            $wpdb->insert('wp_room', $wp_room);
        }

        $cpo = "TAKEN";
        if ($row->CPO == 'No') {
            $cpo = "NOT TAKEN";
        }

        $data = [
            "MemberNumber" => $row->MemberNumber,
            "MemberName" => $memberName,
            "GuestName" => $row->GuestName,
            "Adults" => $row->Adults,
            "Children" => $row->Children,
            "UpgradeFee" => $row->actupgradeFee,
            "CPO" => $cpo,
            "CPOFee" => $row->actcpoFee,
            "Paid" => $row->Paid,
            "Balance" => "0",
            "ResortID" => $daeResortID,
            "ResortName" => $row->Resort_Name,
            "room_type" => $row->Unit_Type,
            "WeekType" => $row->WeekTransactionType,
            "sleeps" => $sleeps,
            "bedrooms" => $beds,
            "Size" => $row->Unit_Type,
            "noNights" => "7",
            "checkIn" => date('Y-m-d', strtotime($row->Check_In_Date)),
            "processedBy" => 5,
            'actWeekPrice' => $row->actWeekPrice,
            'actcpoFee' => $row->actcpoFee,
            'actextensionFee' => $row->actextensionFee,
            'actguestFee' => $row->actguestFee,
            'actupgradeFee' => $row->actupgradeFee,
            'acttax' => $row->acttax,
            'actlatedeposit' => $row->actlatedeposit,
        ];

        $wp_gpxTransactions = [
            'transactionType' => 'booking',
            'cartID' => $userID . '-' . $row->weekId,
            'sessionID' => '',
            'userID' => $userID,
            'resortID' => $daeResortID,
            'weekId' => $row->weekId,
            'check_in_date' => date('Y-m-d', strtotime($row->Check_In_Date)),
            'datetime' => date('Y-m-d', strtotime($row->transaction_date)),
            'depositID' => null,
            'paymentGatewayID' => '',
            'transactionRequestId' => null,
            'transactionData' => '',
            'sfid' => '0',
            'sfData' => '',
            'data' => json_encode($data),
        ];

        $transactionID = '';
        $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s AND userID=%s", [
            $row->weekId,
            $userID,
        ]);
        $et = $wpdb->get_var($sql);
        if (!empty($et)) {
            $wpdb->update('wp_gpxTransactions', $wp_gpxTransactions, ['id' => $et]);
            $transactionID = $et;
        } else {
            $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s", $row->weekId);
            $enut = $wpdb->get_var($sql);
            if (empty($enut) || isset($_GET['force_new_transaction'])) {
                $wpdb->insert('wp_gpxTransactions', $wp_gpxTransactions);
                $transactionID = $wpdb->insert_id;
            } else {
                $exception = json_encode($row);
                $wpdb->insert("final_import_exceptions", [
                    'type' => $tt . ' duplicate week transaction not cancelled',
                    'data' => $exception,
                ]);
                continue;
            }
        }
        if (isset($transactionID) && !empty($transactionID)) {
            TransactionRepository::instance()->send_to_salesforce((int) $transactionID);
        }
    }
    $sql = "SELECT COUNT(id) as cnt FROM " . gpx_esc_table($table) . " WHERE imported=0";
    $remain = $wpdb->get_var($sql);
    if ($remain > 0 && empty($id)) {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(['remaining' => $remain]);
}

add_action('wp_ajax_gpx_import_transactions', 'gpx_import_transactions');


function gpx_import_owner_credits() {
    global $wpdb;

    $sql = "SELECT count(id) as cnt FROM import_pwner_deposits WHERE imported=0 LIMIT 1";

    $rows = $wpdb->get_results($sql);

    foreach ($rows as $row) {

        $import_pwner_deposits = [
            [
                'ID' => '10384447',
                'Member_Name' => '464536',
                'credit_amount' => '1',
                'Credit_expiration date' => '1/1/2020',
                'resort_name' => 'Hanalei Bay Resort',
                'Deposit_year' => '2018',
                'unit_type' => '1b/4',
                'check_in_date' => '1/1/2018',
                'credit_used' => '0',
                'status' => 'Approved',
                'imported' => '0',
            ],
        ];
    }

    $import_pwner_deposits = [
        [
            'ID' => '10384447',
            'Member_Name' => '464536',
            'credit_amount' => '1',
            'Credit_expiration date' => '1/1/2020',
            'resort_name' => 'Hanalei Bay Resort',
            'Deposit_year' => '2018',
            'unit_type' => '1b/4',
            'check_in_date' => '1/1/2018',
            'credit_used' => '0',
            'status' => 'Approved',
            'imported' => '0',
        ],
    ];


    $sql = "SELECT count(id) as cnt FROM import_pwner_deposits WHERE imported=0";
    $remain = $wpdb->get_var($sql);

    if ($remain > 0) {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(['remaining' => $remain]);
}

add_action('wp_ajax_gpx_import_owner_credits', 'gpx_import_owner_credits');


function gpx_owner_monetary_credits() {
    global $wpdb;

    $sql = "SELECT new_id, old_id FROM vest_rework_users WHERE old_id IN (SELECT ownerID FROM wp_gpxOwnerCreditCoupon_owner)";
    $imports = $wpdb->get_results($sql);

    foreach ($imports as $import) {
        $wpdb->update('wp_gpxOwnerCreditCoupon_owner', ['ownerID' => $import->new_id], ['ownerID' => $import->old_id]);

    }

    $sql = "SELECT count(id) as cnt FROM owner_monetary_credits WHERE imported=1 LIMIT 100";
    $remain = $wpdb->get_var($sql);

    if ($remain > 0) {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(['remaining' => $remain]);
}

add_action('wp_ajax_gpx_owner_monetary_credits', 'gpx_owner_monetary_credits');


function reimport_exceptions() {
    global $wpdb;
    $reimport_exceptions = [
        'cu' => 'credit user',
        'cr' => 'credit resort',
        'pcg' => 'partner credit given partner id',
        'pcs' => 'partner credit source partner id',
        'pid' => 'partner import duplicate week',
        'pd' => 'delete trade partner',
        'pci' => 'partner credit insert transaction',
        'tu1' => 'transaction1 user',
        'tr1' => 'transaction1 resort',
        'tu2' => 'transaction2 user',
        'tr2' => 'transaction2 resort',
        'td' => 'transaction duplicate week transaction not cancelled',
        'tmn' => 'transaction member name',
        'tgf' => 'transaction guest formula error',
    ];

    $type = $reimport_exceptions[$_GET['type']];

    $upload_dir = wp_upload_dir();
    // @TODO hardcoded path is likely broken on new server
    $fileLoc = '/var/www/reports/' . $type . '.csv';
    $file = fopen($fileLoc, 'w');

    $sql = $wpdb->prepare("SELECT type, data FROM reimport_exceptions WHERE type=%s", $type);
    $results = $wpdb->get_results($sql, ARRAY_A);
    foreach ($results as $r) {
        $rd = json_decode($r['data'], true);
        $types[$r['type']][] = str_replace(",", "", $rd);
    }

    foreach ($types as $tk => $tv) {
        foreach ($tv as $v) {
            if (!isset($th[$tk])) {
                $heads = array_keys($v);
                $th[$tk] = implode(',', array_keys($v));
            }

            $ov[] = $v;

        }
        $ov[] = $tvv;
    }
    $list = [];
    $list[] = $th[$type];

    $i = 1;
    foreach ($ov as $value) {
        foreach ($heads as $head) {
            $ordered[$i][] = $value[$head];
        }
        $list[$i] = implode(',', $ordered[$i]);
        $i++;
    }
    foreach ($list as $line) {
        fputcsv($file, explode(",", $line));
    }
    fclose($file);

    if (file_exists($fileLoc)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileLoc) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fileLoc));
        readfile($fileLoc);
        exit;
    }
}

add_action('wp_ajax_gpx_reimport_exceptions', 'reimport_exceptions');


function hook_credit_import(int $gpxcreditid) {
    // Check deposits
    gpx_run_command([
        'command' => 'sf:transaction:check-deposits',
        '--deposit' => $gpxcreditid,
    ], false);
}

function gpx_cron_credit_import($atts = ''): void {
    if (!gpx_is_administrator(false)) {
        wp_send_json(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    $atts = shortcode_atts(['gpxcreditid' => null], $atts ?: []);

    $response = new StreamedResponse(function () use ($atts) {
        $gpxcreditid = (int) ($atts['gpxcreditid'] ?? $_GET['creditid'] ?? null);
        $path = WP_CONTENT_DIR . '/logs/check-transactions.log';
        $stream = fopen($path, 'w+');
        $output = new StreamAndOutput($stream);

        // Check deposits
        $params = ['command' => 'sf:transaction:check-deposits'];
        if ($gpxcreditid) {
            $params['--deposit'] = $gpxcreditid;
        }
        gpx_run_command($params, $output);

        fclose($stream);
    }, 200, ['Content-Type' => 'text/plain']);
    gpx_send_response($response);
}

add_action('wp_ajax_gpx_credit_import', 'gpx_cron_credit_import');


function cg_ttsf() {
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $sql = "SELECT id FROM wp_gpxTransactions WHERE sfData='' AND check_in_date > '2020-11-24' ORDER BY RAND() LIMIT 30";
    $trans = $wpdb->get_results($sql);

    foreach ($trans as $r) {
        $id = $r->id;

        TransactionRepository::instance()->send_to_salesforce((int) $id);
    }

    $sql = "SELECT COUNT(id) as cnt FROM wp_gpxTransactions WHERE sfData='' AND check_in_date > '2020-11-24'";
    $remain = $wpdb->get_var($sql);

    if ($remain > 0) {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(['remaining' => $remain]);
}

add_action('wp_ajax_cg_ttsf', 'cg_ttsf');
function admin_claim_week() {
    $liid = get_current_user_id();
    $ids = Arr::wrap(gpx_request('ids', []));
    $action = gpx_request('type');
    if (!in_array($action, ['hold', 'RentalWeek', 'ExchangeWeek'])) {
        wp_send_json(['success' => false, 'message' => 'Invalid action']);
    }
    if (count($ids) == 0) {
        wp_send_json(['success' => false, 'message' => 'Must select at least one week']);
    }

    if ($action == 'hold') {
        try{
            gpx_dispatch(new AdminHoldWeeks($ids, $liid, gpx_request('date')));
        }catch(Exception $e){
            return $e->getMessage();
        }
        wp_send_json(['success' => true, 'message' => 'Weeks held', 'weeks' => $ids]);
    }
    // only "hold" action is implemented for now | and may never be needed
    if ($action == 'RentalWeek' || $action == 'ExchangeWeek') {
        wp_send_json(['success' => false, 'message' => 'Action not implemented']);
        // gpx_dispatch(new PartnerBookWeeks($tp, $ids, $action, $liid));
        //  wp_send_json(['success' => true, 'message' => "{$action}s booked", 'weeks' => $ids]);
    }
}
add_action("wp_ajax_admin_claim_week", "admin_claim_week");

function tp_claim_week() {
    $liid = get_current_user_id();
    $tp = (int) gpx_request('tp', $liid);
    $ids = Arr::wrap(gpx_request('ids', []));
    $action = gpx_request('type');
    if (!in_array($action, ['hold', 'RentalWeek', 'ExchangeWeek'])) {
        wp_send_json(['success' => false, 'message' => 'Invalid action']);
    }
    if (count($ids) == 0) {
        wp_send_json(['success' => false, 'message' => 'Must select at least one week']);
    }

    if ($action == 'hold') {
        try{
            gpx_dispatch(new PartnerHoldWeeks($tp, $ids, $liid, gpx_request('date')));
        }catch(Exception $e){
             return $e->getMessage();
        }
        wp_send_json(['success' => true, 'message' => 'Weeks held', 'weeks' => $ids]);
    }
    gpx_dispatch(new PartnerBookWeeks($tp, $ids, $action, $liid));
    wp_send_json(['success' => true, 'message' => "{$action}s booked", 'weeks' => $ids]);
}

add_action("wp_ajax_tp_claim_week", "tp_claim_week");


function tp_adjust_balance() {
    global $wpdb;

    $sql = $wpdb->prepare("SELECT no_of_rooms_given, no_of_rooms_received_taken, trade_balance, adjData FROM wp_partner WHERE user_id=%s", $_POST['user']);
    $credits = $wpdb->get_row($sql);

    $num = $_POST['num'];
    $note = [];
    if (!empty($credits->adjData)) ;
    {
        $note = json_decode($credits->adjData, true);
    }
    $note[strtotime('now')] = $_POST['note'];

    $tpAdjust = [
        'partner_id' => $_POST['user'],
        'comments' => $_POST['note'],
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    if ($_POST['type'] == 'plus') {
        $toUpdate = [
            'no_of_rooms_given' => $credits->no_of_rooms_given + $num,
            'trade_balance' => $credits->trade_balance + $num,
            'no_of_rooms_received_taken' => $credits->no_of_rooms_received_taken,
            'adjData' => json_encode($note),
        ];
        $tpAdjust['credit_add'] = $num;
    }

    if ($_POST['type'] == 'minus') {
        $toUpdate = [
            'no_of_rooms_received_taken' => $credits->no_of_rooms_received_taken + $num,
            'trade_balance' => $credits->trade_balance - $num,
            'no_of_rooms_given' => $credits->no_of_rooms_given,
            'adjData' => json_encode($note),
        ];
        $tpAdjust['credit_subtract'] = $num;
    }
    $updae = $wpdb->update('wp_partner', $toUpdate, ['user_id' => $_POST['user']]);
    $insert = $wpdb->insert('wp_partner_adjustments', $tpAdjust);
    $data = [
        'success' => true,
        'html' => '<button class="btn btn-secondary" disabled>New Trade Balance: ' . $toUpdate['trade_balance'] . '</button>',
    ];

    wp_send_json($data);
}

add_action("wp_ajax_tp_adjust_balance", "tp_adjust_balance");

function tp_debit() {
    global $wpdb;

    $sql = $wpdb->prepare("SELECT name, debit_id, debit_balance FROM wp_partner WHERE user_id=%s", $_POST['id']);
    $row = $wpdb->get_row($sql);

    $cartID = $_POST['id'] . "_" . strtotime('now');
    $transactionType = 'pay_debit';

    $dbData = [
        'MemberNumber' => $_POST['id'],
        'MemberName' => $row->name,
        'cartID' => $cartID,
        'transactionType' => $transactionType,
        'Paid' => $_POST['amt'],
    ];


    $transaction = [
        'cartID' => $cartID,
        'transactionType' => $transactionType,
        'userID' => $_POST['id'],
        'data' => json_encode($dbData),
    ];
    $wpdb->insert('wp_gpxTransactions', $transaction);

    $dbData['transactionID'] = $wpdb->insert_id;

    $amount = floatval($_POST['amt']) * -1;

    $debit = [
        'user' => $_POST['id'],
        'data' => json_encode($dbData),
        'amount' => $amount,
    ];

    $wpdb->insert('wp_partner_debit_balance', $debit);

    $ids = [];
    $ids = json_decode($row->debit_id, true);
    $ids[] = $wpdb->insert_id;

    $newbalance = $row->debit_balance + $amount;

    $wpdb->update('wp_partner', [
        'debit_balance' => $newbalance,
        'debit_id' => json_encode($ids),
    ], ['user_id' => $_POST['id']]);

    $data['success'] = true;
    $data['balance'] = $newbalance;

    wp_send_json($data);
}

add_action('wp_ajax_tp_debit', 'tp_debit');


function gpx_hold_property() {
    global $wpdb;

    $cid = $_GET['cid'];
    $pid = $_GET['pid'];

    if (isset($_GET['wid']) && (empty($pid) || $pid == '')) {
        $pid = $_GET['wid'];
    }

    $liid = get_current_user_id();

    $agentOrOwner = 'owner';
    if ($cid != $liid) {
        $agentOrOwner = 'agent';
    }

    $sql = $wpdb->prepare("SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId=%s AND cancelled = 0", $pid);
    $trow = $wpdb->get_var($sql);

    if ($trow > 0) {
        $wpdb->update('wp_room', ['active' => '0'], ['record_id' => $pid]);
        $output = [
            'error' => 'This week is no longer available.',
            'msg' => 'This week is no longer available.',
            'inactive' => true,
        ];
        wp_send_json($output);
    }


    $activeUser = get_userdata($liid);

    $bookingrequest = '';
    if (!empty($_REQUEST['bookingrequest'])) {
        $bookingrequest = 'true';
    }

    $sql = $wpdb->prepare("SELECT gpr_oid FROM wp_mapuser2oid WHERE gpx_user_id=%s LIMIT 1", $cid);
    $oid4credit = $wpdb->get_row($sql);

    $holdcount = count(WeekRepository::instance()->get_weeks_on_hold($cid));
    $credits = OwnerRepository::instance()->get_credits($cid);

    $sql = $wpdb->prepare("SELECT id, release_on FROM wp_gpxPreHold WHERE user=%s AND propertyID=%s AND released=0", [
        $cid,
        $pid,
    ]);
    $row = $wpdb->get_row($sql);

    //return true if credits+1 is greater than holds
    if ($credits + 1 > $holdcount || $agentOrOwner == 'agent') {
        //we're good we can continue holding this
        if (empty($row)) {
            //does someone else have this on hold?
            $iSql = $wpdb->prepare("SELECT id, release_on FROM wp_gpxPreHold WHERE propertyID=%s AND released=0", $pid);
            $iRow = $wpdb->get_row($iSql);
            if (!empty($iRow)) {
                $output = [
                    'error' => 'This week is no longer available.',
                    'msg' => 'This week is no longer available.',
                    'inactive' => true,
                ];

                wp_send_json($output);
            }
        }
    } else {
        $output = ['error' => 'too many holds', 'msg' => get_option('gpx_hold_error_message')];

        if (empty($bookingrequest) && empty($row)) {
            //is this a new hold request
            //we don't need to do anything here right now but let's leave it just in case
            //since this isn't a booking request we need to return the error and prevent anything else from happeneing.
            if (wp_doing_ajax()) {
                wp_send_json($output);
            } else {
                return $output;
            }
        }
    }

    $timeLimit = get_option('gpx_hold_limt_time');
    if (!$timeLimit || isset($_REQUEST['button'])) {
        $timeLimit = '24';
    }

    $release_on = strtotime("+" . $timeLimit . " hours");

    if (!isset($_GET['cid']) || $_GET['cid'] == 0) {
        $hold = ['login' => true];
    }

    if (empty($_GET['lpid'])) {
        $_GET['lpid'] = '0';
    }


    $sql = $wpdb->prepare("SELECT id, data FROM wp_gpxPreHold WHERE user=%s AND weekId=%s", [
        $_GET['cid'],
        $_GET['pid'],
    ]);
    $holds = $wpdb->get_row($sql);

    $holdDets[strtotime('now')] = [
        'action' => 'held',
        'by' => $activeUser->first_name . " " . $activeUser->last_name,
    ];

    $data = [
        'propertyID' => $_GET['pid'],
        'weekId' => $_GET['pid'],
        'user' => $_GET['cid'],
        'lpid' => $_GET['lpid'],
        'released' => 0,
        'release_on' => date('Y-m-d H:i:s', $release_on),
        'data' => json_encode($holdDets),
    ];
    if (isset($_GET['weekType'])) {
        $data['weekType'] = str_replace(" ", "", $_GET['weekType']);
    }

    if (isset($holds->id)) {
        $wpdb->delete('wp_gpxPreHold', ['user' => $cid, 'propertyID' => $pid]);
    }

    $wpdb->insert('wp_gpxPreHold', $data);
    $update = $wpdb->insert_id;

    $wpdb->update('wp_room', ['active' => '0'], ['record_id' => $pid]);

    $sql = $wpdb->prepare("SELECT release_on FROM wp_gpxPreHold WHERE user=%s AND weekId=%s", [
        $_GET['cid'],
        $pid,
    ]);
    $rel = $wpdb->get_row($sql);
    $data['msg'] = 'Success';

    $data['release_on'] = date('m/d/Y H:i:s', strtotime($rel->release_on));

    wp_send_json($data);
}

add_action('wp_ajax_gpx_hold_property', 'gpx_hold_property');
add_action('wp_ajax_nopriv_gpx_hold_property', 'gpx_hold_property');


function get_dae_weeks_hold() {
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $sql = "SELECT DISTINCT meta_value FROM wp_usermeta WHERE meta_key='DAEMemberNo'";
    $results = $wpdb->get_results($sql);
    $i = 0;
    foreach ($results as $row) {
        $DAEMemberNo = $row->meta_value;

        $hold = WeekRepository::instance()->get_weeks_on_hold($DAEMemberNo);
        if (!empty($hold)) {
            //release weeks
            if (isset($hold['country'])) {
                $hold = [$hold];
            }
            foreach ($hold as $h) {
                $inputMembers = [
                    'WeekEndpointID' => $h['WeekEndpointID'],
                    'WeekID' => $h['weekId'],
                    'DAEMemberNo' => $DAEMemberNo,
                    'ForImmediateSale' => true,
                ];
                $gpx->DAEReleaseWeek($inputMembers);
                $i++;
            }
        }
    }

    $data = ['success' => $i . ' held weeks removed.'];
    wp_send_json($data);
}

add_action('wp_ajax_get_dae_weeks_hold', 'get_dae_weeks_hold');
add_action('wp_ajax_nopriv_get_dae_weeks_hold', 'get_dae_weeks_hold');


function gpx_cron_release_holds() {
    if (!gpx_is_administrator(false)) {
        wp_send_json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $response = new StreamedResponse(function () {
        $path = WP_CONTENT_DIR . '/logs/check-transactions.log';
        $stream = fopen($path, 'w+');
        $output = new StreamAndOutput($stream);
        gpx_run_command(['command' => 'hold:release'], $output);
        fclose($stream);
    }, 200, ['Content-Type' => 'text/plain']);
    gpx_send_response($response);
}

add_action('wp_ajax_gpx_release_weeks', 'gpx_cron_release_holds');


function update_checkin() {
    global $wpdb;

    $sql = "SELECT id, data from wp_gpxTransactions";
    $rows = $wpdb->get_results($sql);

    foreach ($rows as $row) {
        $data = json_decode($row->data);
        $checkin['check_in_date'] = date('Y-m-d', strtotime($data->checkIn));
        $wpdb->update('wp_gpxTransactions', $checkin, ['id' => $row->id]);
    }
}

add_action('wp_ajax_update_checkin', 'update_checkin');

function function_missed_transactions($id = '') {
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $sql = "SELECT id FROM `wp_gpxTransactions` WHERE `sfid` IS NULL AND datetime > '2021-10-01 00:00:00'";
    $txs = $wpdb->get_results($sql);

    foreach ($txs as $tx) {
        TransactionRepository::instance()->send_to_salesforce((int) $tx->id);
    }

    return true;
}

add_action('hook_cron_function_missed_transactions', 'function_missed_transactions');


function gpx_resend_confirmation() {
    global $wpdb;
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $confID = $gpx->DAEReIssueConfirmation($_GET);
    $msg = '<a href="/confirmation-download?id=' . $confID . '&week=' . $_GET['weekid'] . '&no=' . $_GET['memberno'] . '" target="_blank">Please click here to view your confirmation.</a>';
    if ($confID == 0) {
        $msg = 'There was an error process your request.  Please try again later.';
    }
    $data = ['msg' => $msg];

    wp_send_json($data);
}

add_action('wp_ajax_gpx_resend_confirmation', 'gpx_resend_confirmation');
add_action('wp_ajax_nopriv_gpx_resend_confirmation', 'gpx_resend_confirmation');


function gpx_save_confirmation() {
    global $wpdb;
    if (substr($_SERVER['REQUEST_URI'], 0, 22) == '/confirmation-download') {
        $sql = $wpdb->prepare("SELECT id, pdf FROM wp_gpxPDFConf WHERE daeMemberNo=%s AND id=%s AND weekid=%s", [
            $_GET['no'],
            $_GET['id'],
            $_GET['week'],
        ]);
        $row = $wpdb->get_row($sql);
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="confirmation.pdf"');
        header('HTTP/1.0 200 OK');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header("Content-Transfer-Encoding: binary");

        echo base64_decode($row->pdf);
        exit();
    }
}

add_action('template_redirect', 'gpx_save_confirmation');


function get_gpx_upgrade_fees() {
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    /*
     * ToDo: change $DAEMemberNo to a variable
     */

    $DAEMemberNo = 'U617897';

    $owner = $gpx->DAEGetAccountDetails($DAEMemberNo);


    $MemberTypeID = $owner->MemberTypeID;
    $BusCatID = $owner->BusCatID;


    $data = $gpx->DAEGetUnitUpgradeFees($MemberTypeID, $BusCatID);

    wp_send_json($data);
}

add_action('wp_ajax_get_gpx_upgrade_fees', 'get_gpx_upgrade_fees');
add_action('wp_ajax_nopriv_get_gpx_upgrade_fees', 'get_gpx_upgrade_fees');

function gpx_return_transactions($tradepartner = '', $gp = '') {
    global $wpdb;
    $output = [];
    $data = [];
    $where = '';

    if (isset($_REQUEST['filter'])) {
        $search = json_decode(stripslashes($_REQUEST['filter']));
        foreach ($search as $sk => $sv) {
            if ($sk == 'id') {
                $wheres[] = $wpdb->prepare("a.id LIKE %s", $wpdb->esc_like($sv) . '%');
            } elseif ($sk == 'memberNo') {
                $wheres[] = $wpdb->prepare("JSON_EXTRACT(data, '$.MemberNumber') LIKE %s", '%' . $wpdb->esc_like($sv) . '%');
            } elseif ($sk == 'Resort') {
                $wheres[] = $wpdb->prepare("b.ResortName LIKE %s", '%' . $wpdb->esc_like($sv) . '%');
            } elseif ($sk == 'room_type') {
                $wheres[] = $wpdb->prepare("u.name LIKE %s", '%' . $wpdb->esc_like($sv) . '%');
            } elseif ($sk == 'weekType') {
                $wheres[] = $wpdb->prepare("JSON_EXTRACT(data, '$.WeekType') LIKE %s", '%' . $wpdb->esc_like($sv) . '%');
            } elseif ($sk == 'weekID') {
                $wheres[] = $wpdb->prepare("a.weekId LIKE %s", '%' . $wpdb->esc_like($sv) . '%');
            } elseif ($sk == 'checkIn') {
                $wheres[] = $wpdb->prepare("a.check_in_date BETWEEN %s AND %s ", [
                    date('Y-m-d 00:00:00', strtotime($sv)),
                    date('Y-m-d 23:59:59', strtotime($sv)),
                ]);
            } elseif ($sk == 'paid') {
                $wheres[] = $wpdb->prepare("JSON_EXTRACT(data, '$.Paid') LIKE %s", '%' . $wpdb->esc_like($sv) . '%');
            } elseif ($sk == 'transactionDate') {
                $wheres[] = $wpdb->prepare("a.datetime BETWEEN %s AND %s ", [
                    date('Y-m-d 00:00:00', strtotime($sv)),
                    date('Y-m-d 23:59:59', strtotime($sv)),
                ]);
            } elseif ($sk == 'cancelled') {
                if (strpos($sv, 'y') !== false || strpos($sv, 'ye') !== false || strpos($sv, 'yes') !== false) {
                    $wheres[] = "a.cancelled = 1";
                } elseif (strpos($sv, 'n') !== false || strpos($sv, 'no') !== false) {
                    $wheres[] = "a.cancelled = 0";
                }
            } elseif ($sk == 'check_in_date') {
                $wheres[] = $wpdb->prepare(gpx_esc_table($sk) . " BETWEEN %s AND %s ", [
                    date('Y-m-d 00:00:00', strtotime($sv)),
                    date('Y-m-d 23:59:59', strtotime($sv)),
                ]);
            } else {
                $wheres[] = $wpdb->prepare(gpx_esc_table($sk) . " LIKE %s", '%' . $wpdb->esc_like($sv) . '%');
            }
        }
        $where .= " " . implode(" OR ", $wheres);
    }
    if (isset($_REQUEST['sort'])) {
        $orderBy = " ORDER BY " . gpx_esc_table($_REQUEST['sort']) . " " . gpx_esc_orderby($_REQUEST['order']);
    }
    if (isset($_REQUEST['limit'])) {
        $limit = $wpdb->prepare(" LIMIT %d", $_REQUEST['limit']);
    }
    if (isset($_REQUEST['offset'])) {
        $offset = $wpdb->prepare(" OFFSET %d", $_REQUEST['offset']);
    }

    $sql = "SELECT a.*, b.ResortName, u.name as room_type FROM wp_gpxTransactions a
                LEFT OUTER JOIN wp_room r ON r.record_id=a.weekId
                LEFT OUTER JOIN wp_resorts b ON r.resort=b.id

                LEFT OUTER JOIN wp_unit_type u on u.record_id=r.unit_type";


    if (!empty($gp)) {
        $sql .= $gp;
    } else {
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        $sql .= $orderBy;
        $sql .= $limit;
        $sql .= $offset;
    }

    $tsql = "SELECT a.id, a.data, a.cancelledData  FROM wp_gpxTransactions a
                LEFT OUTER JOIN wp_room r ON r.record_id=a.weekId
                LEFT OUTER JOIN wp_resorts b ON r.resort=b.id
                LEFT OUTER JOIN wp_unit_type u on u.record_id=r.unit_type";
    if (!empty($gp)) {
        $tsql .= $gp;
    } else {
        if (!empty($where)) {
            $tsql .= " WHERE " . $where;
        }
    }
    $output['total'] = count($wpdb->get_results($tsql));

    $rows = $wpdb->get_results($sql);
    $output['rows'] = [];
    $i = 0;

    foreach ($rows as $row) {
        if (!empty($tradepartner)) {
            //if this is a trade partner search then we need to find those with that role
            $user_meta = get_userdata($row->userID);

            $user_roles = $user_meta->roles;
            if (!in_array('gpx_trade_partner', $user_roles)) {
                //if this isn't part of the array then we don't need to continue.
                continue;
            }
        }
        $cancelled = $row->cancelled ? 'Yes' : 'No';

        $data = json_decode($row->data);
        $view = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=transactions_view&id=' . $row->id . '" target="_blank"><i class="fa fa-external-link" aria-hidden="true"></i></a>';

        $data->GuestName = $data->GuestName ?? '';
        $name = !empty($data->GuestName) ? explode(" ", $data->GuestName) : ['', ''];
        $email = '';
        if (isset($data->Email)) {
            $email = $data->Email;
        }
        $phone = '';
        if (isset($data->Phone)) {
            $phone = $data->Phone;
        }
        $checkin = '';
        if (!empty($data->checkIn)) {
            $checkin = '<div data-date="' . strtotime($data->checkIn) . '">' . date('m/d/Y', strtotime($data->checkIn)) . '</div>';
        }
        $transactionDate = '';
        if ($row->datetime != '') {
            $transactionDate = '<div data-date="' . strtotime($row->datetime) . '">' . date('m/d/Y', strtotime($row->datetime)) . '</div>';
        }
        $data->Adults = $data->Adults ?? 0;
        $data->Children = $data->Children ?? 0;
        $data->Paid = $data->Paid ?? 0;
        $guestName = '<div data-name="' . $data->GuestName . '" class="updateGuestName"';
        $guestName .= ' data-transaction="' . $row->id . '"';
        $guestName .= ' data-fname="' . $name[0] . '"';
        $guestName .= ' data-lname="' . $name[1] . '"';
        $guestName .= ' data-email="' . $email . '"';
        $guestName .= ' data-phone="' . $phone . '"';
        $guestName .= ' data-adults="' . $data->Adults . '"';
        $guestName .= ' data-children="' . $data->Children . '"';
        $guestName .= ' data-owner="' . ($data->Owner ?? '') . '"';
        $guestName .= '>';
        $guestName .= '<i class="fa fa-edit"></i> <span class="guestName guestName' . $row->id . '">' . $data->GuestName . '</span>';
        $guestName .= '</div>';

        $output['rows'][$i]['view'] = $view;
        $output['rows'][$i]['transactionType'] = ucwords(str_replace("_", " ", $row->transactionType));
        $output['rows'][$i]['id'] = $row->id;
        $output['rows'][$i]['memberNo'] = $data->MemberNumber ?? '';
        $output['rows'][$i]['memberName'] = $data->MemberName ?? '';
        $output['rows'][$i]['ownedBy'] = $data->Owner ?? '';
        $output['rows'][$i]['guest'] = $guestName;
        $output['rows'][$i]['Resort'] = $row->ResortName ?? '';
        $output['rows'][$i]['resrotID'] = $row->ResortID ?? '';
        $output['rows'][$i]['room_type'] = $row->room_type ?? '';
        $output['rows'][$i]['depositID'] = $row->depositID ?? '';
        $output['rows'][$i]['weekID'] = $row->weekId ?? '';
        $output['rows'][$i]['size'] = $data->Size ?? '';
        $output['rows'][$i]['checkIn'] = $checkin;
        $output['rows'][$i]['paid'] = '<div data-price="' . $data->Paid . '">' . gpx_currency($data->Paid) . '</div>';
        $output['rows'][$i]['weekType'] = $data->WeekType ?? '';

        $output['rows'][$i]['date'] = '<div data-date="' . strtotime($row->datetime) . '">' . date('m/d/Y', strtotime($row->datetime)) . '</div>';
        $output['rows'][$i]['adults'] = $data->Adults ?? '';
        $output['rows'][$i]['children'] = $data->Children ?? '';
        $output['rows'][$i]['upgradefee'] = $data->UpgradeFee ?? '';
        $output['rows'][$i]['cpo'] = $data->CPO ?? '';
        $output['rows'][$i]['cpofee'] = $data->CPOFee ?? '';
        $output['rows'][$i]['weekPrice'] = $data->WeekPrice ?? '';
        $output['rows'][$i]['balance'] = $data->Balance ?? '';
        $output['rows'][$i]['sleeps'] = $data->sleeps ?? '';
        $output['rows'][$i]['bedrooms'] = $data->bedrooms ?? '';
        $output['rows'][$i]['nights'] = $data->noNights ?? '';
        $output['rows'][$i]['processedBy'] = $data->processedBy ?? '';
        $output['rows'][$i]['promoName'] = $data->promoName ?? '';
        $output['rows'][$i]['discount'] = $data->discount ?? 0.00;
        $output['rows'][$i]['coupon'] = $data->coupon ?? '';
        $output['rows'][$i]['ownerCreditCouponAmount'] = $data->ownerCreditCouponAmount ?? '';
        $output['rows'][$i]['transactionDate'] = $transactionDate;
        $output['rows'][$i]['uploadedDate'] = $data->Uploaded ?? '';
        $output['rows'][$i]['cancelled'] = $cancelled;
        $i++;
    }

    return $output;
}

function get_gpx_transactions() {
    $tradepartner = '';
    if (isset($_GET['tradepartner'])) {
        $tradepartner = true;
    }
    $data = gpx_return_transactions($tradepartner);

    wp_send_json($data);
}

add_action('wp_ajax_get_gpx_transactions', 'get_gpx_transactions');

function gpx_endpoint_profile_transaction_modal(): string {
    if (!is_user_logged_in()) {
        status_header(403);

        return 'Must be logged in to view';
    }
    $id = gpx_request('transaction');
    if (empty($id)) {
        status_header(404);

        return 'No transaction provided';
    }
    $cid = gpx_get_switch_user_cookie();
    $transaction = Transaction::forUser($cid)->find($id);
    if (!$transaction) {
        status_header(404);

        return 'Transaction not found';
    }
    $transaction->user = UserMeta::load($transaction->userID);
    $creditID = $transaction->data['creditweekID'] ?? $transaction->data['creditweekid'] ?? null;
    if ($creditID) {
        $transaction->deposit = Credit::with('interval')->find($creditID);
    } else {
        $transaction->deposit = null;
    }
    $agent = UserMeta::load(get_current_user_id());

    return gpx_theme_template_part('profile-transaction-modal', compact('transaction', 'agent'), false);
}

function gpx_endpoint_profile_transaction_guest_modal(): void {
    $cid = gpx_get_switch_user_cookie();
    $id = gpx_request('transaction');
    if (!$id) {
        wp_send_json(['success' => false, 'message' => 'Transaction not found'], 404);
    }
    $transaction = Transaction::forUser($cid)->find($id);
    if (!$transaction) {
        wp_send_json(['success' => false, 'message' => 'Transaction not found'], 404);
    }
    $data = $transaction->data;
    $name = explode(" ", $data['GuestName'] ?? ' ', 2);
    if (empty($data['GuestFirstName'])) {
        $data['GuestFirstName'] = $name[0] ?? null;
    }
    if (empty($data['GuestLastName'])) {
        $data['GuestLastName'] = $name[1] ?? null;
    }
    $user = UserMeta::load($transaction->userID);

    $prop = null;
    if ($transaction->weekId) {
        $property_details = get_property_details($transaction->weekId, $cid);
        $prop = $property_details['prop'] ?? null;
    }


    $guest = [
        'id' => -1,
        'first_name' => $data['GuestFirstName'] ?? '',
        'last_name' => $data['GuestLastName'] ?? '',
        'name' => $data['GuestName'] ?? trim(($data['GuestFirstName'] ?? '') . ' ' . ($data['GuestLastName'] ?? '')),
        'email' => $data['GuestEmail'] ?? $data['Email'] ?? $user->getEmailAddress(),
        'phone' => gpx_format_phone($data['GuestPhone'] ?? $data['Phone'] ?? $user->getPhone()) ?? '',
        'adults' => (int) ($data['Adults'] ?? 1),
        'children' => (int) ($data['Children'] ?? 0),
        'owner' => $data['OwnerName'] ?? $data['Owner'] ?? '',
    ];

    $owners = collect([
        [
            'id' => -1,
            'first_name' => $guest['first_name'],
            'last_name' => $guest['last_name'],
            'name' => $guest['name'],
            'email' => $guest['email'],
            'phone' => $guest['phone'],
        ],
    ]);
    if (!$owners->contains('name', $user->getName())) {
        $owners->push([
            'id' => 0,
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'name' => $user->getName(),
            'email' => $user->getEmailAddress(),
            'phone' => $user->getPhone(),
        ]);
    }
    Owner::select(['id', 'user_id', 'SPI_Owner_Name_1st__c', 'SPI_Email__c', 'SPI_Home_Phone__c'])
         ->where('user_id', '=', $cid)
         ->where('SPI_Owner_Name_1st__c', '!=', $guest['name'])
         ->where('SPI_Owner_Name_1st__c', '!=', $user->getName())
         ->each(function ($owner) use ($owners) {
             if (!$owners->contains('name', $owner->SPI_Owner_Name_1st__c)) {
                 $name = explode(' ', $owner->SPI_Owner_Name_1st__c, 2);
                 $owners->push([
                     'id' => $owner->id,
                     'first_name' => trim($name[0] ?? ''),
                     'last_name' => trim($name[1] ?? ''),
                     'name' => $owner->SPI_Owner_Name_1st__c,
                     'email' => $owner->SPI_Email__c,
                     'phone' => $owner->SPI_Home_Phone__c,
                 ]);
             }
         });

    if (!empty($user->getSecondaryName()) && !str_contains(mb_strtolower($user->getSecondaryName()), 'c/o') && !$owners->contains('name', $user->getSecondaryName())) {
        $owners->push([
            'id' => -3,
            'first_name' => $user->FirstName2,
            'last_name' => $user->LastName2,
            'name' => $user->getSecondaryName(),
            'email' => mb_strtolower($user->Email2 ?? ''),
            'phone' => gpx_format_phone($user->Mobile2),
        ]);
    }

    wp_send_json([
        'success' => true,
        'transaction' => [
            'id' => $transaction->id,
            'has_guest_fee' => ((int) ($prop?->gfAmt ?? 0) > 0),
            'paid_guest_fee' => $transaction->hasGuestFee(),
            'checkIn' => $transaction->check_in_date->format('m/d/Y'),
            'cancelled' => $transaction->cancelled,
            'fee' => $prop?->gfAmt ?? 0,
            'fee_slash' => $prop?->gfSlash ?? '',
        ],
        'guest' => $guest,
        'owners' => $owners,
    ]);
}

function gpx_endpoint_profile_transaction_guest_update() {
    $cid = gpx_get_switch_user_cookie();
    if (!gpx_is_agent()) {
        wp_send_json(['success' => false, 'message' => 'Cannot update guest info'], 403);
    }
    $id = gpx_request('transaction');
    if (!$id) {
        wp_send_json(['success' => false, 'message' => 'Transaction not found'], 404);
    }
    $transaction = Transaction::forUser($cid)->find($id);
    if (!$transaction) {
        wp_send_json(['success' => false, 'message' => 'Transaction not found'], 404);
    }
    if ($transaction->cancelled) {
        wp_send_json(['success' => false, 'message' => 'Transaction is cancelled.']);
    }
    if (!$transaction->isBooking()) {
        wp_send_json(['success' => false, 'message' => 'This is not a booking transaction.']);
    }

    /** @var UpdateGuestForm $form */
    $form = gpx(UpdateGuestForm::class);
    $values = $form->validate(gpx_request('guest', []));
    $values['owner'] = $transaction->data['OwnerName'] ?? $transaction->data['Owner'] ?? '';

    // Does a guest fee need to be paid?
    if (!gpx_request('fee', false)) {
        // The fee was waived
        gpx_dispatch(new UpdateGuestInfo($transaction, $values));
        wp_send_json(['success' => true, 'message' => 'Transaction updated.']);
    }
    if ($transaction->hasGuestFee()) {
        // guest fee was already paid
        gpx_dispatch(new UpdateGuestInfo($transaction, $values));
        wp_send_json(['success' => true, 'message' => 'Transaction updated.']);
    }

    $property_details = get_property_details($transaction->weekId, $cid);
    $prop = $property_details['prop'] ?? null;
    $has_guest_fee = ((int) ($prop?->gfAmt ?? 0) > 0);
    if (!$has_guest_fee) {
        // no guest fee is required
        gpx_dispatch(new UpdateGuestInfo($transaction, $values));
        wp_send_json(['success' => true, 'message' => 'Transaction updated.']);
    }

    // add guest fee to cart
    $cart = gpx_create_cart();
    $cart->setItem(new GuestFee($cid, $transaction));
    $cart->item->setGuestInfo([
        'has_guest' => true,
        'fee' => true,
        'owner' => $values['owner'],
        'adults' => $values['adults'],
        'children' => $values['children'],
        'email' => $values['email'],
        'first_name' => $values['first_name'],
        'last_name' => $values['last_name'],
        'phone' => $values['phone'],
        'special_request' => $transaction->data['specialRequest'] ?? null,
    ]);
    gpx_save_cart($cart);

    wp_send_json([
        'success' => true,
        'redirect' => site_url('booking-path-payment'),
        'message' => 'Added guest fee to cart.',
    ]);
}

function gpx_agent_cancel_booking() {

    if (!is_user_logged_in()) {
        wp_send_json(['success' => false, 'message' => 'Must be logged in'], 403);
    }
    $id = gpx_request('transaction');
    if (empty($id)) {
        wp_send_json(['success' => false, 'message' => 'No transaction provided'], 404);
    }
    $cid = gpx_get_switch_user_cookie();
    $transaction = Transaction::forUser($cid)->with(['partner', 'week'])->find($id);
    if (!$transaction) {
        wp_send_json(['success' => false, 'message' => 'Transaction not found'], 404);
    }
    if ($transaction->transactionType !== 'booking') {
        wp_send_json(['success' => false, 'message' => 'Cannot cancel this transaction'], 422);
    }
    if ($transaction->cancelled) {
        wp_send_json(['success' => false, 'message' => 'Transaction already cancelled'], 422);
    }

    $message = null;
    $agent = UserMeta::load(get_current_user_id());
    $cancellations = collect(array_values($transaction->cancelledData ?? []));
    $has_flex = $transaction->canBeRefunded();
    $origin = gpx_request('origin') ?? 'system';
    $transData = $transaction->data;
    $refund = new RefundRequest([
        'cancel' => true,
        'amount' => 0,
        'booking' => $has_flex,
        'origin' => $origin,
        'booking_amount' => $has_flex ? round(($transData['actWeekPrice'] ?? 0.00) - ($cancellations->where('type', '==', 'erFee')->sum('amount')), 2) : 0.00,
        'cpo' => false,
        'cpo_amount' => 0.00,
        'upgrade' => $has_flex,
        'upgrade_amount' => $has_flex ? round(($transData['actupgradeFee'] ?? 0.00) - ($cancellations->where('type', '==', 'upgradefee')->sum('amount')), 2) : 0.00,
        'guest' => $has_flex,
        'guest_amount' => $has_flex ? round(($transData['actguestFee'] ?? $transData['GuestFeeAmount'] ?? 0.00) - ($cancellations->where('type', '==', 'guestfeeamount')->sum('amount')), 2) : 0.00,
        'late' => false,
        'late_amount' => 0.00,
        'third_party' => false,
        'third_party_amount' => 0.00,
        'extension' => false,
        'extension_amount' => 0.00,
        'tax' => false,
        'tax_amount' => 0.00,
    ]);

    $repository = TransactionRepository::instance();
    $refunded = $repository->refundTransaction($transaction, $refund, $agent);
    $repository->cancelTransaction($transaction, $origin);

    wp_send_json([
        'success' => true,
        'message' => $message,
        'cid' => $transaction->userID,
        'date' => $transaction->cancelledDate->format('m/d/Y'),
        'agent' => $agent->getName(),
        'amount' => $refunded,
    ]);
}

add_action('wp_ajax_gpx_agent_cancel_booking', 'gpx_agent_cancel_booking');

function gpx_cancel_booking($transaction = '') {
    global $wpdb;

    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $sf = Salesforce::getInstance();

    $transaction = $_POST['transaction'] ?? $transaction;
    $sql = $wpdb->prepare("SELECT * FROM wp_gpxTransactions WHERE id=%s", $transaction);
    $transRow = $wpdb->get_row($sql);

    $transData = json_decode($transRow->data);
    $sfTransData = json_decode($transRow->sfData);
    $canceledData = json_decode($transRow->cancelledData);

    $is_admin = isset($_POST['requester']) && $_POST['requester'] === 'admin';

    $refunded = '0';

    //is this a trade partner
    $sql = $wpdb->prepare("SELECT * FROM wp_partner WHERE user_id=%s", $transRow->userID);
    $partner = $wpdb->get_row($sql);

    if (!empty($partner)) {
        if (strpos(strtolower($transData->WeekType), 'exchange') !== false) {
            //adjust the credit
            $updateAmount = [
                'no_of_rooms_received_taken' => $partner->no_of_rooms_received_taken - 1,
                'trade_balance' => $partner->trade_balance + 1,
            ];
            $wpdb->update('wp_partner', $updateAmount, ['record_id' => $partner->record_id]);
        } else {
            //adjust the balance
            $tpTransData = $transData;
            $tpTransData->cancelled = date('m/d/Y');
            $debit = [
                'user' => $partner->user_id,
                'data' => json_encode($tpTransData),
                'amount' => $tpTransData->Paid,
            ];

            $wpdb->insert('wp_partner_debit_balance', $debit);
            $pdid = $wpdb->insert_id;

            $debit_id = json_decode($partner->debit_id, true);
            $adjData = json_decode($partner->adjData, true);

            $debit_id[] = $pdid;
            $adjData[strtotime('now')] = 'cancelled';
            $debit_balance = (float) $partner->debit_balance - (float) $tpTransData->Paid;

            $updateAmount = [
                'adjData' => json_encode($adjData),
                'debit_id' => json_encode($debit_id),
                'debit_balance' => $debit_balance,
            ];

            $wpdb->update('wp_partner', $updateAmount, ['record_id' => $partner->record_id]);
        }
    } elseif (isset($_REQUEST['admin_amt']) && trim(strpos(strtolower($transData->WeekType), 'rental')) !== false) {
        //this is an admin refunding from GPX Admin
        $refunded = $_REQUEST['admin_amt'];
    } elseif (trim(strpos(strtolower($transData->WeekType), 'exchange')) !== false) {

        if (!empty($canceledData)) {
            foreach ($canceledData as $cK => $cD) {
                $alredyRefunded[$cK] = $cD->amount;
                $amt = $cD->amount;
                $refunds[$cD->type][] = array_sum(Arr::wrap($amt ?? []));
            }
        }

        //within 45 days or without flex booking
        if ((strtotime($transRow->check_in_date) < strtotime('+45 days'))) {
            //no refund
        } elseif ($transData->CPO == 'Taken') {
            //refund everything but the CPO
            $cpoFee = get_option('gpx_fb_fee');
            if (empty($transData->CPOFee)) {
                $cpoFee = $transData->CPOFee;
                if (isset($refunds['cpofee'])) {
                    $cpoRefund = array_sum($refunds['cpofee']);
                    $cpoFee = $cpoFee - $cpoRefund;
                    unset($refunds['cpofee']);
                }

            }
            $extFee = 0;
            if (!empty($transData->actextensionFee) && $transData->actextensionFee != 'null') {

                $extFee = $transData->actextensionFee;
                if (isset($refunds['extensionfee'])) {
                    $extRefund = array_sum($refunds['extensionfee']);
                    $extFee = $extFee - $extRefund;
                    unset($refunds['extensionfee']);
                }
            }
            /*
             * todo: check the object name
             */
            $lateDeposit = 0;
            if (!empty($transData->actlatedepositFee) && $transData->actlatedepositFee != 'null') {
                $lateDeposit = $transData->actlatedepositFee;
                if (isset($refunds['latedepositfee'])) {
                    $lateDepositRefund = array_sum($refunds['latedepositfee']);
                    $lateDeposit = $lateDeposit - $lateDepositRefund;
                    unset($refunds['latedepositfee']);
                }
            }

            $paid = $transData->Paid;
            $refunded = $paid - $cpoFee - $extFee - $lateDeposit;
            //remove any other refunds
            if (isset($refunds)) {
                $refunded = $refunded - array_sum($refunds);
            }

            /*
             * Closure Coupons
             * One that I specifically asked for progress on was the issue with coupons not being refunded, even when
             * processing an admin refund all request. I tried processing a refund for the entire amount to a coupon and
             * only the portion paid above and beyond the coupon value was refunded (see below).
             */
            if (isset($transData->coupon)) {
                $tcoupon = (array) $transData->coupon;
                $coupon = reset($tcoupon);
                $sql = $wpdb->prepare("SELECT Type, PromoType, Amount FROM wp_specials WHERE id=%s", $coupon);
                $promo = $wpdb->get_row($sql);

                if ($promo->Type == 'coupon' && $promo->PromoType == 'Pct Off' && $promo->Amount == '100') {
                    $couponAmt = str_replace("$", "", $transData->couponDiscount);
                    $refunded = $refunded + $couponAmt;
                }
            }
        }

    }


    /*
     * if there is a monetary coupon add that amount in
     */
    if ($transData->ownerCreditCouponAmount && $transData->ownerCreditCouponAmount > 0) {
        $refunded = $refunded + $transData->ownerCreditCouponAmount;
        //the refund amount may need to be split -- only when refunding to credit card.
        $occRefund = $transData->ownerCreditCouponAmount;
    }

    if ($refunded == 0 && isset($transData->GuestFeeAmount) && $transData->GuestFeeAmount > 0) {
        $refunded = $refunded + $transData->GuestFeeAmount;
    }

    if (strpos(strtolower($transData->WeekType), 'exchange') !== false) {
        //need to refresh the credit
        $sql = $wpdb->prepare("SELECT credit_used FROM wp_credit WHERE id=%s", $transData->creditweekid);
        $cr = (int) $wpdb->get_var($sql);
        $newcr = $cr - 1;

        $wpdb->update('wp_credit', ['credit_used' => $newcr], ['id' => $transData->creditweekid]);

        $sfCreditData['GPX_Deposit_ID__c'] = $transData->creditweekid;
        $sfCreditData['Credits_Used__c'] = $newcr;

        $sfWeekAdd = '';
        $sfAdd = '';
        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';


        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfCreditData;
        $sfFields[0]->type = $sfType;

        $sfResortAdd = $sf->gpxUpsert($sfObject, $sfFields);
    }


    //we need to refund this transaction
    if ($refunded > 0) {

        //credit card or coupon

        //never ever allow anyone but admin to issue credit card refunds
        if (!$is_admin) {
            $_REQUEST['type'] = 'credit';
        }

        if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'refund') {

            if (isset($occRefund)) {
                $refunded = $refunded - $occRefund;

            }

            $refundType = 'refund';
            $shift4 = new Shiftfour();

            //refund the amount to the credit card
            $cancel = $shift4->shift_refund($transaction, $refunded);
            $data['html'] = '<h4>A refund to the credit card on file has been generated.</h4>';

            $refundAmt = $refunded;
            foreach ($canceledData as $cd) {
                $refundAmt += $cd->amount;
            }
            $sfData['Credit_Card_Refund__c'] = $refundAmt;
        }

        if (isset($occRefund) || (isset($_REQUEST['type']) && $_REQUEST['type'] != 'refund')) {

            if (isset($occRefund)) {
                $refunded = $occRefund;
            }

            $refundType = 'credit';
            $slug = $transRow->weekId . $transRow->userID;
            do {
                $sql = $wpdb->prepare("SELECT id FROM wp_gpxOwnerCreditCoupon WHERE couponcode=%s", $slug);
                $exists = $wpdb->get_var($sql);
                if ($exists) $slug = $transRow->weekId . $transRow->userID . rand(1, 1000);
            } while ($exists);

            $occ = [
                'Name' => $transRow->weekId,
                'Slug' => $slug,
                'Active' => 1,
                'singleuse' => 0,
                'amount' => $refunded,
                'owners' => [$transRow->userID],
                'comments' => 'Reservation Cancelled -- Refund issued on transaction ' . $transRow->weekId . ($is_admin ? ' (Refund Exception)' : ''),
            ];
            $coupon = $gpx->promodeccouponsadd($occ);

            $data['html'] = "<h4>A $" . $refunded . " coupon has been generated.</h4>";
        }
    }

    $sfData['Reservation_Status__c'] = 'Cancelled';
    $sfData['GPXTransaction__c'] = $sfTransData->insert->GPXTransaction__c;
    $sfData['Cancel_Date__c'] = date('Y-m-d');


    $sfWeekAdd = '';
    $sfAdd = '';
    $sfType = 'GPX_Transaction__c';
    $sfObject = 'GPXTransaction__c';

    $sfFields = [];
    $sfFields[0] = new SObject();
    $sfFields[0]->fields = $sfData;
    $sfFields[0]->type = $sfType;

    $sfCancelTransaction = $sf->gpxUpsert($sfObject, $sfFields);


    $sfWeekData['Status__c'] = 'Available';
    $sfWeekData['Name'] = $transRow->weekId;
    $sfWeekData['Booked_by_TP__c'] = 0;
    $sfWeekData['of_Children__c'] = '0';
    $sfWeekData['Special_Requests__c'] = ' ';


    $sfWeekAdd = '';
    $sfAdd = '';
    $sfType = 'GPX_Week__c';
    $sfObject = 'Name';

    $sfFields = [];
    $sfFields[0] = new SObject();
    $sfFields[0]->fields = $sfWeekData;
    $sfFields[0]->type = $sfType;

    $sfWeekAvailable = $sf->gpxUpsert($sfObject, $sfFields);

    //update the database
    $agentInfo = wp_get_current_user();
    $agent = $agentInfo->first_name . ' ' . $agentInfo->last_name;

    $update = [
        'userid' => get_current_user_id(),
        'name' => $agent,
        'date' => date('Y-m-d H:i:s'),
        'refunded' => $refunded,
        'coupon' => $coupon['coupon'],
        'agent_name' => $agent,
    ];

    $canceledData = (array) $canceledData;

    $canceledData[strtotime('NOW')] = [
        'userid' => get_current_user_id(),
        'name' => $agent,
        'origin' => $origin,  // @todo define $origin
        'date' => date('Y-m-d H:i:s'),
        'refunded' => $refunded,
        'coupon' => $coupon['coupon'],

        'action' => $refundType,
        'amount' => $refunded,
        'by' => get_current_user_id(),
        'agent_name' => $agent,
    ];

    $wpdb->update('wp_gpxTransactions', [
        'cancelled' => 1,
        'cancelledData' => json_encode($canceledData),
        'cancelledDate' => date('Y-m-d', strtotime("NOW")),
    ], ['id' => $transaction]);

    $sql = $wpdb->prepare("SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId=%s AND cancelled = 0", $transRow->weekId);
    $trow = $wpdb->get_var($sql);


    // TODO if nothing to do again... fix
    if ($trow > 0) {
        //nothing to do
    } else {

        //we always need to check the "display date" prior to making it active. Only make this active when the sell date is in the future.
        $sql = $wpdb->prepare("SELECT active_specific_date FROM wp_room WHERE record_id=%d", $transRow->weekId);
        $activeDate = $wpdb->get_var($sql);

        if (strtotime('NOW') > strtotime($activeDate)) {
            $wpdb->update('wp_room', ['active' => 1], ['record_id' => $transRow->weekId]);
        }
    }


    $data['success'] = true;
    $data['cid'] = $transRow->userID;
    $data['amount'] = $refunded;
    wp_send_json($data);
}

add_action('wp_ajax_gpx_cancel_booking', 'gpx_cancel_booking');
add_action('wp_ajax_nopriv_gpx_cancel_booking', 'gpx_cancel_booking');

function gpx_admin_owner_transactions() {
    $tradepartner = '';
    if (isset($_GET['tradepartner'])) {
        $tradepartner = true;
    }
    $group = " WHERE userID='" . ($_GET['userID'] ?? '') . "'";
    if (isset($_GET['weekID'])) {
        $group = " WHERE weekId='" . $_GET['weekID'] . "'";
    }
    $data = gpx_return_transactions($tradepartner, $group);

    wp_send_json($data);
}

add_action('wp_ajax_gpx_admin_owner_transactions', 'gpx_admin_owner_transactions');

function get_gpx_holds() {
    global $wpdb;
    $group = '';

    if (!empty($_GET['userID'])) {
        $group = $wpdb->prepare(" WHERE a.user = %s", $_GET['userID']);
    }

    if (!empty($_GET['weedID'])) {
        $group = $wpdb->prepare(" WHERE a.weekId = %s", $_GET['weedID']);
    }

    $output = [];
    $sql = "SELECT
                t.id as txid,
                a.id as holdID, a.user, a.release_on, a.released, a.data,
                b.record_id as id, b.check_in_date, b.active,
                c.ResortName,
                d.name, d.name as roomSize
            FROM wp_gpxPreHold a
            INNER JOIN wp_room b on b.record_id = a.weekId
            INNER JOIN wp_resorts c on c.id = b.resort
            INNER JOIN wp_unit_type d ON d.record_id = b.unit_type
            LEFT OUTER JOIN wp_gpxTransactions t ON t.weekId = b.record_id";
    if (!empty($group)) {
        $sql .= $group;
    }
    $sql .= ' GROUP BY a.id';
    $rows = $wpdb->get_results($sql);

    $i = 0;
    foreach ($rows as $row) {
        $canextend = false;

        $user = UserMeta::load($row->user);
        $released = 'Yes';
        if (!$row->released) {
            $released = 'No';
        } elseif ($row->active) {
            //was this booked?
            $isbooked = gpx_is_week_booked($row->id);

            if ($isbooked) {
                $released = 'Booked';
            }
        }

        if ($released == 'No') {
            $canextend = true;
        }
        $ownerName = $user->getName();
        $action = '<a href="#" class="more-hold-details" data-toggle="modal" data-target="#holdModal' . esc_attr($row->holdID) . '"><i class="fa fa-eye"></i></a>&nbsp;';
        $action .= '<div id="holdModal' . esc_attr($row->holdID) . '" class="modal fade" role="dialog">';
        $action .= '<div class="modal-dialog">';
        $action .= '<div class="modal-content">';
        $action .= '<div class="modal-header">';
        $action .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
        $action .= '<h4 class="modal-title">Hold Details</h4>';
        $action .= '</div>';
        $action .= '<div class="modal-body">';
        $action .= '<ul>';
        $action .= '<li><strong>Owner:</strong> ' . esc_html($ownerName) . '</li>';
        $action .= '<li><strong>Week:</strong> ' . esc_html($row->id) . '</li>';
        $action .= '<li><strong>Resort:</strong> ' . esc_html($row->ResortName) . '</li>';
        $action .= '<li><strong>Room:</strong> ' . esc_html($row->name) . '</li>';
        $action .= '<li><strong>Check In:</strong> ' . date('m/d/Y', strtotime($row->check_in_date)) . '</li>';
        $action .= '<li><strong>Activity:</strong></li><ul style="margin-left: 20px;">';
        $holdDets = json_decode($row->data);
        foreach ($holdDets as $hk => $hd) {
            $action .= '<li><strong>' . date('m/d/Y h:i a', $hk) . '</strong> ' . esc_html($hd->action) . ' by ' . esc_html($hd->by) . '</li>';
        }
        $action .= '</ul>';
        $action .= '</ul>';
        $action .= '</div>';
        $action .= '<div class="modal-footer">';
        $action .= '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
        $action .= '</div>';
        $action .= '</div>';
        $action .= '</div>';
        $action .= '</div>';
        if ($canextend) {
            $action .= '<span class="extend-box">';
            $action .= '<a href="#" class="extend-week"title="Extend Week"><i class="fa fa-calendar-plus-o"></i></a>';
            $action .= '<span class="extend-input" style="display: none;">';
            $action .= '<a href="#" class="close_box">&times;</a>';
            $action .= '<input type="date" class="form-control extend-date" name="extend-date" />';
            $action .= '<a href="#" class="btn btn-primary extend-btn" data-id="' . esc_attr($row->holdID) . '" >Extend Hold</a>';
            $action .= '</span>';
            $action .= '</span>';
            if ($released == 'No') {
                $action .= '&nbsp;&nbsp;&nbsp;<a href="#" class="release-week" data-id="' . esc_attr($row->holdID) . '" title="release"><i class="fa fa-calendar-times-o"></i></a>';
            }
        }
        $output[$i]['action'] = $action;
        $output[$i]['name'] = $ownerName;
        $output[$i]['memberNo'] = $row->user;
        $output[$i]['week'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id=' . rawurlencode($row->id) . '" target="_blank">' . esc_html($row->id) . '</a>';
        $output[$i]['resort'] = $row->ResortName;
        $output[$i]['roomSize'] = $row->roomSize;
        $output[$i]['checkIn'] = date('m/d/Y', strtotime($row->check_in_date));
        $output[$i]['releaseOn'] = date('m/d/Y H:i:s', strtotime($row->release_on));
        $output[$i]['release'] = $released;

        $i++;
    }

    wp_send_json($output);
}

add_action('wp_ajax_get_gpx_holds', 'get_gpx_holds');
add_action('wp_ajax_nopriv_get_gpx_holds', 'get_gpx_holds');

function gpx_credit_manual() {
    global $wpdb;
    $credit_id = (int) gpx_request('id');
    if (!$credit_id) {
        wp_send_json(['success' => false, 'message' => 'No credit provided'], 404);
    }
    $sql = $wpdb->prepare("SELECT * FROM wp_credit WHERE id=%d", $credit_id);
    $credit = $wpdb->get_row($sql, ARRAY_A);
    if (!$credit) {
        wp_send_json(['success' => false, 'message' => "Credit with id {$credit_id} not found"], 404);
    }
    $usermeta = UserMeta::load($credit['owner_id']);
    $sf = Salesforce::getInstance();
    $sql = $wpdb->prepare("SELECT RIOD_Key_Full FROM wp_owner_interval WHERE unitweek=%s AND userID=%d", [
        $credit['unitinterval'],
        $credit['owner_id'],
    ]);
    $interval_name = $wpdb->get_var($sql);
    $interval = null;
    if ($interval_name) {
        $query = $wpdb->prepare("SELECT ID, Name FROM Ownership_Interval__c WHERE ROID_Key_Full__c = %s", $interval_name);
        $results = $sf->query($query);
        $interval = $results[0]->Id;
    }

    $sfCreditData = [
        'Deposit_Status__c' => $credit['status'],
        'Account_Name__c' => $usermeta->Property_Owner ?? null,
        'Check_In_Date__c' => $credit['check_in_date'],
        'Deposit_Year__c' => $credit['deposit_year'],
        'GPX_Member__c' => $credit['owner_id'],
        'Deposit_Date__c' => $credit['created_date'],
        'Resort_Name__c' => $credit['resort_name'],
        'Resort_Unit_Week__c' => $credit['unitinterval'],
        'Member_Email__c' => OwnerRepository::instance()->get_email($credit['owner_id']),
        'Member_First_Name__c' => $usermeta->first_name ?? null,
        'Member_Last_Name__c' => $usermeta->last_name ?? null,
        'Unit_Type__c' => $credit['unit_type'],
        'GPX_Deposit_ID__c' => $credit['id'],
        'Credits_Used__c' => $credit['credit_used'],
        'Credits_Issued__c' => $credit['credit_amount'],
        'Ownership_Interval__c' => $interval,
    ];
    foreach ($sfCreditData as $key => $value) {
        if (empty($value)) {
            unset($sfCreditData[$key]);
        }
    }

    try {
        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfCreditData;
        $sfFields[0]->type = 'GPX_Deposit__c';
        $sfDepositAdjust = $sf->gpxUpsert('GPX_Deposit_ID__c', $sfFields, 'true');
        $sfid = $sfDepositAdjust[0]->id;
    } catch (\Exception $e) {
        wp_send_json(['success' => false, 'message' => 'Failed to push credit to salesforce', 'error' => $e], 500);
    }
    if (!$credit['record_id'] && !empty($sfid)) {

        $wpdb->update('wp_credit', ['record_id' => $sfid], ['id' => $credit['id']]);
    }

    wp_send_json(['success' => true, 'message' => 'Pushed credit to salesforce', 'data' => $sfCreditData]);
}

add_action('wp_ajax_gpx_credit_manual', 'gpx_credit_manual');

function gpx_rework_add_cancelled_date() {
    global $wpdb;

    $sql = "SELECT id, cancelledData FROM wp_gpxTransactions WHERE cancelled = 1";
    $rows = $wpdb->get_results($sql);

    foreach ($rows as $row) {
        $ddata = json_decode($row->cancelledData, true);
        end($ddata);
        $date = date('Y-m-d', key($ddata));

        if (strtotime($date) > strtotime('2020-11-01')) {
            $data['cancelledDate'] = $date;
            $wpdb->update('wp_gpxTransactions', $data, ['id' => $row->id]);
        }
    }

    wp_send_json($data);
}

add_action('wp_ajax_gpx_rework_add_cancelled_date', 'gpx_rework_add_cancelled_date');

function gpx_remove_guest() {
    global $wpdb;

    $return = [];

    if (isset($_POST['transactionID']) && !empty($_POST['transactionID'])) {
        $sql = $wpdb->prepare("SELECT userID, data FROM wp_gpxTransactions WHERE id=%s", $_POST['transactionID']);
        $row = $wpdb->get_row($sql);

        $data = json_decode($row->data);

        $memberID = $row->userID;

        $usermeta = (object) array_map(function ($a) { return $a[0]; }, get_user_meta($memberID));

        $_POST['FirstName1'] = $usermeta->FirstName1;
        $_POST['LastName1'] = $usermeta->LastName1;

        $guest = gpx_reasign_guest_name($_POST);

        $data->GuestName = $_POST['FirstName1'] . " " . $_POST['LastName1'];

        $wpdb->update('wp_gpxTransactions', ['data' => json_encode($data)], ['id' => $_POST['transactionID']]);


        $return['success'] = true;
    }


    wp_send_json($return);
}

add_action('wp_ajax_gpx_remove_guest', 'gpx_remove_guest');


function gpx_reasign_guest_name($postdata = [], $addtocart = '') {
    global $wpdb;

    if (!empty($postdata)) {
        $_POST = (array) $postdata;
    }

    $transaction = $_POST['transactionID'];

    $sql = $wpdb->prepare("SELECT sfid, sfData, data, weekId, userID FROM wp_gpxTransactions WHERE id=%d", $transaction);
    $row = $wpdb->get_row($sql);

    $cid = $row->userID;

    $usermeta = UserMeta::load($cid);

    $memberName = $usermeta->FirstName1 . ' ' . $usermeta->LastName1;

    $tData = json_decode($row->data, true);


    if (empty($postdata)) {

        if ((!isset($_POST['adminTransaction'])) && ($tData['GuestName'] ?? null) != $_POST['FirstName1'] . " " . $_POST['LastName1'] && $_POST['FirstName1'] . ' ' . $_POST['LastName1'] != $memberName && (!isset($tData['GuestFeeAmount']) || (isset($tData['GuestFeeAmount']) && $tData['GuestFeeAmount'] <= 0))) {

            $_POST['fee'] = get_option('gpx_gf_amount');

            $tempcart = [
                'item' => 'guest',
                'user_id' => $cid,
                'data' => json_encode($_POST),
            ];

            $wpdb->insert('wp_temp_cart', $tempcart);

            $tempID = $wpdb->insert_id;
            $data = [
                'paymentrequired' => true,
                'amount' => $_POST['fee'],
                'type' => 'guest',
                'html' => '<h5>You will be required to pay a guest fee of $' . $_POST['fee'] . ' to complete change.</h5><br /><br /> <span class="usw-button"><button class="dgt-btn add-fee-to-cart-direct" data-type="guest" data-fee="' . $_POST['fee'] . '" data-tid="' . $tempID . '" data-cart="" data-skip="No">Add To Cart</button><br /><br />',
            ];

            if ($cid != get_current_user_id()) {
                $data['html'] .= '<button class="dgt-btn add-fee-to-cart-direct af-agent-skip" data-fee="' . $_POST['fee'] . '" data-tid="' . $tempID . '" data-type="guest" data-cart="" data-skip="Yes">Waive Fee</button><br /><br />';
            }

        }
    }

    if (!isset($data) || (isset($_POST['transactionID']))) {

        $sf = Salesforce::getInstance();

        $sfDB = json_decode($row->sfData, true);

        if (isset($_POST['LastName1'])) {
            $tData['GuestName'] = $_POST['FirstName1'] . " " . $_POST['LastName1'];
            $sfData['Guest_First_Name__c'] = $sfWeekData['Guest_First_Name__c'] = htmlentities($_POST['FirstName1']);
            $sfData['Guest_Last_Name__c'] = $sfWeekData['Guest_Last_Name__c'] = htmlentities($_POST['LastName1']);
        }
        if (isset($_POST['Email'])) {
            $sfData['Guest_Email__c'] = $sfWeekData['Guest_Email__c'] = $tData['Email'] = $_POST['Email'];
        }
        if (isset($_POST['Phone'])) {
            $sfData['Guest_Home_Phone__c'] = $sfWeekData['Guest_Phone__c'] = $tData['Phone'] = substr(preg_replace('/[^0-9]/', '', $_POST['Phone']), 0, 18);
        }
        if (isset($_POST['Adults'])) {
            $sfWeekData['of_Adults__c'] = $tData['Adults'] = $_POST['Adults'];
        }
        if (isset($_POST['Children'])) {
            $sfWeekData['of_Children__c'] = $tData['Children'] = $_POST['Children'];
        }
        if (isset($_POST['Owner']) && !empty($_POST['Owner'])) {
            $sfData['Trade_Partner__c'] = $tData['Owner'] = htmlentities($_POST['Owner']);
        }
        if (isset($_POST['fee'])) {
            $tData['GuestFeeAmount'] = $_POST['fee'];
        }
        $sfData['GPXTransaction__c'] = $transaction;
        $sfWeekData['GpxWeekRefId__c'] = $row->weekId;

        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfData;
        $sfFields[0]->type = 'GPX_Transaction__c';
        $sfAdd = $sf->gpxTransactions($sfFields);

        $sfWeekAdd = '';
        $sfAdd = '';
        $sfType = 'GPX_Week__c';
        $sfObject = 'GpxWeekRefId__c';


        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfWeekData;
        $sfFields[0]->type = $sfType;
        $sfWeekAdd = $sf->gpxUpsert($sfObject, $sfFields);

        if (!isset($sfAdd[0]->id)) {
            //add the error to the sf data
            $sfDB['error'] = $sfAdd;
            $key = 'updated_' . strtotime("now");

            $sfDB[$key] = [
                'by' => get_current_user_id(),
                'data' => $sfData,
            ];
        }

        $dbUpdate['data'] = json_encode($tData);

        $wpdb->update('wp_gpxTransactions', $dbUpdate, ['id' => $transaction]);

        $data['success'] = true;
        $data['cid'] = $cid;
        $data['message'] = 'Guest has been changed';
    }

    if (!empty($addtocart)) {
        return $data;
    } else {
        wp_send_json($data);
    }
}

add_action('wp_ajax_gpx_reasign_guest_name', 'gpx_reasign_guest_name');
add_action('wp_ajax_nopriv_gpx_reasign_guest_name', 'gpx_reasign_guest_name');


function gpx_transactions_add() {
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $eachTrans = explode(PHP_EOL, $_POST['transactions']);

    foreach ($eachTrans as $trans) {
        $dets = explode(",", preg_replace('/\s+/', '', $trans));
        $memberNO = $dets[0];
        $weekID = $dets[1];

        //put the week on hold

    }


    $sql = $wpdb->prepare("SELECT data FROM wp_gpxTransactions WHERE id=%d", $transaction);
    $row = $wpdb->get_row($sql);
    $tData = (array) json_decode($row->data, true);

    $tData['GuestName'] = $name;

    $wpdb->update('wp_gpxTransactions', ['data' => json_encode($tData)], ['id' => $transaction]);

    $data['success'] = true;

    wp_send_json($data);
}

add_action('wp_ajax_gpx_transactions_add', 'gpx_transactions_add');
add_action('wp_ajax_nopriv_gpx_transactions_add', 'gpx_transactions_add');


function gpx_credit_donation() {

    if (isset($_POST['Check_In_Date__c'])) {
        //send the details to SF
        $sf = Salesforce::getInstance();
        $cid = gpx_get_switch_user_cookie();

        $sfDepositData = [
            'Check_In_Date__c' => date('Y-m-d', strtotime($_POST['Check_In_Date__c'])),
            'Account_Name__c' => $_POST['Account_Name__c'],
            'GPX_Member__c' => $cid,
            'Deposit_Date__c' => date('Y-m-d'),
            'Resort_Name__c' => stripslashes(str_replace("&", "&amp;", $_POST['Resort_Name__c'])),
            'Resort_Unit_Week__c' => $_POST['Resort_Unit_Week__c'],
            'GPX_Deposit_ID__c' => $_POST['GPX_Deposit_ID__c'],
        ];

        if (!empty($_POST['Reservation__c'])) {
            $sfDepositData['Reservation__c'] = $_POST['Reservation__c'];
        }

        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';

        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfDepositData;
        $sfFields[0]->type = $sfType;

        $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);

        $return['success'] = true;
    } else {
        $cid = $_GET['id'];
        $return = gpx_get_deposit_form($cid);
    }

    wp_send_json($return);
}

add_action("wp_ajax_gpx_credit_donation", "gpx_credit_donation");

function gpx_get_deposit_form($cid = '') {
    global $wpdb;

    if (!is_user_logged_in()) {
        $html = '';

        return $html;
    }
    if (empty($cid)) {
        $cid = gpx_get_switch_user_cookie();
    }
    $agent = false;
    if ($cid != get_current_user_id()) {
        $agent = true;
    }
    $usermeta = (object) array_map(function ($a) {
        return $a[0];
    }, get_user_meta($cid));

    $credit = OwnerRepository::instance()->get_credits($cid);
    $results = IntervalRepository::instance()->get_member_ownerships($cid);

    if (empty($results)) {
        return '<h2>Your ownership ID is not valid.</h2>';
    }

    $sf = Salesforce::getInstance();

    $html = '<h2>Deposit Week</h2>';
    $html .= '<h5>Current Credit: <span class="interval-credit">' . $credit . '</span></h5>';
    $html .= '<p>Float reservations must be made with your home resort prior to deposit.</p>';
    $html .= '<div id="depositMsg"></div>';
    $html .= '<form name="CreateWillBank" class="material" method="post">';
    $html .= '<input type="hidden" name="DAEMemberNo" value="' . $memberNumber . '">';
    $html .= '<ul class="deposit-bank-boxes">';
    foreach ($results as $result) {
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
            'ROID_Key_Full__c',
        ];
        $query = "SELECT " . implode(", ", $selects) . " FROM Ownership_Interval__c where Contract_ID__c = '" . $result->contractID . "'";
        $ownerships = $sf->query($query);
        $ownership = $ownerships ? $ownerships[0]->fields : null;

        //check for a 2 for 1 special
        $sql = "SELECT * FROM wp_specials WHERE PromoType='2 for 1 Deposit' and Active=1";
        $specials = $wpdb->get_results($sql);
        $type = '';
        $type = '';
        foreach ($specials as $special) {
            if (isset($twofer) && $twofer['type'] == 'Promo') {
                $promocode = $twofer['code'];
                $type = $twofer['type'];
            } else {
                $promocode = '';
            }

            if (strtolower($special->Type) == 'promo') {
                $promocode = $special->Slug;
            }
            $specialMeta = json_decode($special->Properties);
            //if useage_resort is set then we need to make sure that this resort should apply to this special
            if (isset($specialMeta->usage_resort)) {
                foreach ($specialMeta->usage_resort as $resortList) {
                    $sql = $wpdb->prepare("SELECT ResortID FROM wp_resorts WHERE id=%s", $resortList);
                    $resortRow = $wpdb->get_row($sql);
                    if ($resortRow->ResortID == $ownership?->Resort_ID_v2__c) {
                        if (isset($twofer['startDate'])) {
                            if ($twofer['startDate'] < $special->StartDate) {
                                $special->StartDate = $twofer['startDate'];
                            }
                            if ($twofer['endDate'] > $special->EndDate) {
                                $special->EndDate = $twofer['endDate'];
                            }
                        }
                        if ($type != 'Promo') {
                            $type = $special->Type;
                        }
                        $twofer = [
                            'startDate' => $special->StartDate,
                            'endDate' => $special->EndDate,
                            'type' => $type,
                            'code' => $promocode,
                        ];
                    }
                }
            } else // this isn't dependant on a set resort 8775667519
            {
                if (isset($specialMeta->specificCustomer) && in_array($cid, json_decode($specialMeta->specificCustomer))) {
                    if (isset($twofer['startDate'])) {
                        if ($twofer['startDate'] < $special->StartDate) {
                            $special->StartDate = $twofer['startDate'];
                        }
                        if ($twofer['endDate'] > $special->EndDate) {
                            $special->EndDate = $twofer['endDate'];
                        }
                    }
                    if ($type != 'Promo') {
                        $type = $special->Type;
                    }
                    $twofer = [
                        'startDate' => $special->StartDate,
                        'endDate' => $special->EndDate,
                        'type' => $type,
                        'code' => $promocode,
                    ];
                } else {
                    if (isset($twofer['startDate'])) {
                        if ($twofer['startDate'] < $special->StartDate) {
                            $special->StartDate = $twofer['startDate'];
                        }
                        if ($twofer['endDate'] > $special->EndDate) {
                            $special->EndDate = $twofer['endDate'];
                        }
                    }
                    if ($type != 'Promo') {
                        $type = $special->Type;
                    }
                    $twofer = [
                        'startDate' => $special->StartDate,
                        'endDate' => $special->EndDate,
                        'type' => $type,
                        'code' => $promocode,
                    ];
                }
            }
        }
        $admin = wp_get_current_user();
        if (in_array('administrator_plus', (array) $admin->roles) || in_array('administrator', (array) $admin->roles) || in_array('gpx_admin', (array) $admin->roles)) {
            $isadmin = 'style="display: block !important;"';
        }

        if (!isset($twofer) || (isset($twofer) && empty($twofer)) && isset($isadmin)) {
            $twofer = [
                'startDate' => 'null',
                'endDate' => 'null',
                'type' => 'cradj',
                'code' => '',
            ];
        }

        $yearbankded = '';
        $ownershipType = '';
        if (!empty($ownership?->Usage__c)) {
            $ownershipType = $ownership?->Usage__c;
        }
        if (!empty($result->deposit_year)) {
//                                 $yearbankded = $result->deposit_year+1;
//                                 $nextyear = '1/1/'.$yearbankded;
            //@Traci -- we asked to remove the minimum date becasue owners can depoist multiple times in one year
            $nextyear = date('m/d/Y', strtotime('+14 days'));
        } else {
            $nextyear = date('m/d/Y', strtotime('+14 days'));
        }
        //if this is an agent then the minimum date can be up to a year ago
        if ($agent) {
            $nextyear = date('m/d/Y', strtotime("-2 years"));
        }
        //if this is delinquent then don't allow the deposit
        $delinquent = '';
        if ($result->Delinquent__c != 'No') {
            $delinquent = "<strong>Please contact us at <a href=\"tel:+18775667519\">(877) 566-7519</a> for assistance.</strong>";
        }
        $html .= '<li>';
        $html .= '<div class="bank-row">';
        $html .= '<h3>' . $result->ResortName . '</h3>';
        $html .= '</div>';
        if (!empty($delinquent)) {
            $html .= '<div class="bank-row" style="margin: margin-bottom: 20px;">' . $delinquent . '</div>';
        } else {
            $html .= '<div class="bank-row">';
            $html .= '<span class="dgt-btn bank-select">Select</span>';
            $html .= '</div>';
            $html .= '<div class="bank-row">';
            $html .= '<input type="radio" name="OwnershipID" class="switch-deposit" value="' . $ownership?->Name . '" style="text-align: center;">';
            $html .= '</div>';
        }
        $selectUnit = [
            'Channel Island Shores',
            'Hilton Grand Vacations Club at MarBrisa',
            'RiverPointe Napa Valley',
        ];
        if (in_array($result->ResortName, $selectUnit) || empty($ownership?->Room_Type__c)) {
            $html .= '<div class="reswrap">';
            $html .= 'Unit Type: <select name="Unit_Type__c" class="sel_unit_type ">';
            $html .= '<option value="">Please Select</option>';
            $html .= '<option>Studio</option>';
            $html .= '<option>1br</option>';
            $html .= '<option>2br</option>';
            $html .= '<option>3br</option>';
            $html .= '</select>';
            $html .= '</div>';
        } else {
            $html .= '<input type="hidden" name="Unit_Type__c" value="' . $ownership?->Room_Type__c . '" class="disswitch" disabled="disabled">';
            $html .= '<div class="bank-row">Unit Type: ' . $ownership?->Room_Type__c . '</div>';
        }
        if (!empty($ownershipType)) {
            $html .= '<div class="bank-row">Ownership Type:' . $ownershipType . '</div>';
        }
        $html .= '<div class="bank-row">Resort Member Number: ' . $ownership?->UnitWeek__c . '</div>';
        if (isset($result->deposit_year)) {
            $html .= '<div class="bank-row">Last Year Banked: ' . $result->deposit_year . '</div>';
        }
        $html .= '<div class="bank-row" style="height: 40px; position: relative;">';

        if (!$delinquent) {
            $html .= '<input type="text" placeholder="Check In Date" name="Check_In_Date__c" class="validate mindatepicker disswitch" value="" disabled="disabled" required>';
        }
        $html .= '<input type="hidden" name="Contract_ID__c" value="' . $ownership?->Contract_ID__c . '" class="disswitch" disabled="disabled">';
        $html .= '<input type="hidden" name="Usage__c" value="' . $ownership?->Usage__c . '" class="disswitch" disabled="disabled">';
        $html .= '<input type="hidden" name="Account_Name__c" value="' . $ownership?->Property_Owner__c . '" class="disswitch" disabled="disabled">';
        $html .= '<input type="hidden" name="GPX_Member__c" value="' . $ownership?->Owner_ID__c . '" class="disswitch" disabled="disabled">';
        $html .= '<input type="hidden" name="GPX_Resort__c" value="' . $ownership?->GPR_Resort__c . '" class="disswitch" disabled="disabled">';
        $html .= '<input type="hidden" name="Resort_Name__c" value="' . $result->ResortName . '" class="disswitch" disabled="disabled">';
        $html .= '<input type="hidden" name="Resort_Unit_Week__c" value="' . $ownership?->UnitWeek__c . '" class="disswitch" disabled="disabled">';
        $html .= '</div>';

        $resRequired = '';
        if ($result->gpr == '0') {
            $resRequired = ' required="required"';
        }
        $html .= '<div class="reswrap"><input type="text" name="Reservation__c" placeholder="Reservation Number" class="resdisswitch" disabled="disabled" ' . $resRequired . ' /></div>';

        if (isset($twofer) && !empty($twofer)) {
            $html .= '<div ' . $isadmin . ' class="twoforone twoforone-' . $twofer['type'] . '" data-start="' . date('m/d/Y', strtotime($twofer['startDate'])) . '" data-end="' . date('m/d/Y', strtotime($twofer['endDate'])) . '">';
            $html .= '<input placeholder="Coupon Code" type="text" name="twofer" value="' . $twofer['code'] . '"><br>';
            $html .= '</div>';
        }
        $html .= '</li>';
    }
    $html .= '</ul>';
    $html .= '<li><a href="#" class="btn-will-bank dgt-btn">Submit<i class="fa fa-refresh fa-spin fa-fw" style="display: none;"></i></a></li>';
    $html .= '<li class="success-message"></li>';
    $html .= '</ul>';
    $html .= '</form>';


    return $html;
}


function gpx_import_test() {
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->transactionimport();

    wp_send_json($data);
}

add_action('wp_ajax_gpx_import_test', 'gpx_import_test');
add_action('wp_ajax_nopriv_gpx_import_test', 'gpx_import_test');


function gpx_trans_agent_fix() {
    global $wpdb;


    $sql = "SELECT * FROM wp_gpxTransactions WHERE cartID <> '' and id > 8500";
    $toCheck = $wpdb->get_results($sql);
    $i = 0;
    foreach ($toCheck as $dRow) {
        $djson = json_decode($dRow->data, true);
        $sql = $wpdb->prepare("SELECT * FROM wp_gpxMemberSearch WHERE sessionID=%s AND data LIKE %s", [
            $dRow->sessionID,
            '%view-%',
        ]);
        $views = $wpdb->get_results($sql);
        foreach ($views as $v) {
            $jv = json_decode($v->data, true);
            foreach ($jv as $pv) {
                foreach ($pv as $pk => $p) {
                    if (isset($pk) && $pk == 'search_by_id') {
                        if (!empty($p)) {
                            if ($djson['processedBy'] != $p) {
                                $djson['processedBy'] = $p;
                                $wpdb->update('wp_gpxTransactions', ['data' => json_encode($djson)], ['id' => $dRow->id]);
                                $i++;
                            }
                        }
                    }
                }
            }
        }
    }
    $data['processed'] = $i;
    wp_send_json($data);
}

add_action('wp_ajax_gpx_trans_agent_fix', 'gpx_trans_agent_fix');

function update_gpx_tax_transaction_type() {
    $tts = ['bonus', 'exchange'];
    foreach ($tts as $value) {
        $option = 'gpx_tax_transaction_' . $value;
        if (in_array($value, $_POST['ttType'])) {
            update_option($option, '1');
        } else {
            update_option($option, '0');
        }
    }
    $return = ['success' => true];
    wp_send_json($return);
}

add_action('wp_ajax_update_gpx_tax_transaction_type', 'update_gpx_tax_transaction_type');
add_action('wp_ajax_nopriv_update_gpx_tax_transaction_type', 'update_gpx_tax_transaction_type');


/**
 * Used to load transactions for use on member profile page
 */
function gpx_load_transactions($id = '') {
    $cid = gpx_get_switch_user_cookie();
    $agent = null;
    if ($cid !== get_current_user_id()) {
        $agentInfo = wp_get_current_user();
        $agent = $agentInfo->first_name . ' ' . $agentInfo->last_name;
    }
    $holds = WeekRepository::instance()->get_weeks_on_hold($cid);
    $output['hold'] = '';
    if (!empty($holds)) {
        $output['hold'] = '<thead><tr>';
        $output['hold'] .= '<td>ID</td><td>Resort Name</td><td>Bedrooms</td><td>Check In</td><td>Week Type</td><td>Release On</td><td></td><td></td>';
        $output['hold'] .= '</tr></thead><tbody>';
        foreach ($holds as $hold) {
            $holdWeekType = $hold->weekType == 'RentalWeek' ? 'Rental Week' : 'Exchange Week';
            $weekTypeForBook = str_replace(" ", "", $holdWeekType);
            $changeweek = gpx_theme_template_part('profile-held-weeks-type', [
                'hold' => $hold,
                'type' => $holdWeekType,
            ], false);

            $output['hold'] .= '<tr>';
            $output['hold'] .= '<td>' . esc_html($hold->PID) . '</td>';
            $output['hold'] .= '<td><a class="hold-confirm" href="/booking-path/?book=' . urlencode($hold->id) . '&type=' . urlencode($weekTypeForBook) . '">' . esc_html($hold->ResortName) . '</a></td>';
            $output['hold'] .= '<td>' . esc_html($hold->bedrooms) . '</td>';
            $output['hold'] .= '<td class="whitespace-nowrap">' . esc_html(date('m/d/Y', strtotime($hold->checkIn))) . '</td>';
            $output['hold'] .= '<td style="width:250px;">' . $changeweek . '</td>';
            $output['hold'] .= '<td class="whitespace-nowrap">' . esc_html(date('m/d/Y h:i a', strtotime($hold->release_on))) . '</td>';
            $output['hold'] .= '<td><a class="hold-confirm btn btn-sm btn-blue whitespace-nowrap" href="/booking-path/?book=' . urlencode($hold->id) . '&type=' . urlencode($weekTypeForBook) . '">Book Week</a></td>';
            $output['hold'] .= '<td>';
            if ($agent) {
                $action = '<span class="extend-box">';
                $action .= '<a href="#" class="extend-week" title="Extend Week"><i class="fa fa-calendar-plus-o"></i></a>';
                $action .= '<span class="extend-input" style="display: none;">';
                $action .= '<input type="date" class="form-control extend-date" name="extend-date" />';
                $action .= '<a href="#" class="btn btn-primary extend-btn" data-id="' . esc_attr($hold->holdid) . '" >Extend Hold</a>';
                $action .= '</span>';
                $action .= '</span>';
                $output['hold'] .= $action;
            }
            $output['hold'] .= '<a href="#" class="remove-hold" data-pid="' . esc_attr($hold->id) . '" data-cid="' . esc_attr($cid) . '" aria-label="remove hold"><i class="fa fa-trash" aria-hidden="true"></i></a>';
            $output['hold'] .= '</td>';
            $output['hold'] .= '</tr>';
        }
        $output['hold'] .= '</tbody>';
    }

    $credit = OwnerRepository::instance()->get_credits($cid);
    $ownerships = IntervalRepository::instance()->get_member_ownerships($cid);

    $output['credit'] = $credit;

    $transactions = TransactionRepository::instance()->get_member_transactions($cid);

    $html = '<thead><tr>';
    $html .= '<td>Membership#</td><td>Resort Name</td><td>Size</td><td>Last Year Banked</td><td>Deposit My Week<td></td>';
    $html .= '</tr></thead><tbody>';

    foreach ($ownerships as $ownership) {
        $html .= '<tr>';
        $html .= '<td>' . esc_html($ownership['unitweek'] ?? '') . '</td>';
        $html .= '<td>' . esc_html($ownership['ResortName'] ?? '') . '</td>';
        $html .= '<td>' . esc_html($ownership['Room_Type__c'] ?? '') . '</td>';
        $html .= '<td>' . esc_html($ownership['deposit_year'] ?? '') . '</td>';
        if ($ownership["Contract_Status__c"] == 'Active') {
            $dy = $ownership['Year_Last_Banked__c'] ?: date('Y');
            $html .= '<td><button class="btn btn-blue btn-sm deposit-modal">Deposit Now</button></td>';
        } else {
            $html .= '<td>' . esc_html($ownership['Contract_Status__c'] ?? '') . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody>';
    $output['ownership'] = $html;
    $types = [
        'Deposit' => [
            'id' => 'Ref No.',
            'unitinterval' => 'Interval',
            'resort_name' => 'Resort Name',
            'deposit_year' => 'Entitlement Year',
            'unit_type' => 'Unit Size/Occupancy',
            'status' => 'Status',
            'credit' => 'Credit Balance',
            'credit_expiration_date' => 'Expiration Date',
            'ice' => 'Use or Extend My Deposit',
        ],
        'Depositused' => [
            'id' => 'Ref No.',
            'unitinterval' => 'Interval',
            'resort_name' => 'Resort Name',
            'deposit_year' => 'Entitlement Year',
            'unit_type' => 'Unit Size/Occupancy',
            'status' => 'Status',
            'credit' => 'Credit Balance',
            'credit_expiration_date' => 'Expiration Date',
            'ice' => '',
        ],
        'Rental' => [
            'weekId' => 'Ref No.',
            'ResortName' => 'Resort Name',
            'room_type' => 'Room Type',
            'GuestName' => 'Guest Name',
            'checkIn' => 'Check In',
            'Paid' => 'Paid',
        ],
        'Exchange' => [
            'weekId' => 'Ref No.',
            'ResortName' => 'Resort Name',
            'room_type' => 'Room Type',
            'GuestName' => 'Guest Name',
            'checkIn' => 'Check In',
            'Paid' => 'Paid',
        ],
        'Misc' => [
            'id' => 'Ref No.',
            'type' => 'Type',
            'Paid' => 'Paid',
        ],
    ];

    foreach ($types as $key => $type) {
        $key = strtolower($key);

        $output[$key] = '<thead><tr>';
        foreach ($type as $th) {
            $output[$key] .= '<td>' . $th . '</td>';
        }
        $output[$key] .= '</tr></thead><tbody>';
        if (isset($transactions[$key])) {
            foreach ($transactions[$key] as $transaction) {
                $transaction['Paid'] = $transaction['Paid'] ?? 0.00;
                $transaction['Paid'] = ($transaction['Paid'] != 0) ? gpx_currency($transaction['Paid']) : '-';
                $cancelledClass = ($transaction['cancelled'] ?? 0) > 0 ? 'cancelled-week' : '';
                $output[$key] .= '<tr>';
                foreach ($type as $tk => $td) {
                    if ($tk == 'status' && $transaction['status'] != 'Denied') {
                        if ($transaction['credit_amount'] == 0 && $transaction['status'] != 'Cancelled') {
                            $transaction['status'] = 'PENDING';
                        } elseif ($transaction['status'] == 'Cancelled') {
                            $transaction['status'] = 'REMOVED';
                        } elseif ($transaction['credit_action'] == 'donated') {
                            $transaction['status'] = 'DONATED';
                        } elseif ($transaction['credit_action'] == 'transferred') {
                            $transaction['status'] = 'TRANSFERRED';
                        } elseif ($transaction['credit'] <= 0) {
                            $transaction['status'] = 'USED';
                        } elseif (strtotime($transaction['credit_expiration_date'] . ' 23:59:59') < strtotime('now')) {
                            $transaction['status'] = 'EXPIRED';
                        } else {
                            $transaction['status'] = 'ACTIVE';
                        }
                    } elseif ($tk == 'status') {
                        $transaction['status'] = 'DENIED';
                    }
                    if ($tk == 'type') {
                        if ($transaction[$tk] == 'Deposit') {
                            $transaction[$tk] = 'Late Deposit Fee';
                        }
                        if ($transaction[$tk] == 'Extension') {
                            $transaction[$tk] = 'Credit Extension Fee';
                        }
                        if ($transaction[$tk] == 'Guest') {
                            $transaction[$tk] = 'Guest Fee';
                        }
                        if ($transaction[$tk] == 'Credit_donation') {
                            $transaction[$tk] = 'Credit Donation';
                        }
                        if ($transaction[$tk] == 'Credit_transfer') {
                            $transaction[$tk] = 'Credit Transfer';
                        }
                    }
                    if (($tk == 'credit_expiration_date' || $tk == 'checkIn') && !empty($transaction[$tk])) {
                        $transaction[$tk] = date('m/d/Y', strtotime($transaction[$tk]));
                    }
                    if ($tk == 'ice') {
                        $transaction[$tk] = '';
                        if ($transaction['status'] == 'PENDING') {
                            $transaction[$tk] = '<span class="credit-pending">Credit Pending</span>';
                        } elseif (($key == 'deposit' && !empty($transaction['credit_action'])) || $transaction['status'] == 'INACTIVE' || date('m/d/Y',
                                strtotime($transaction['credit_expiration_date'])) == '01/01/1970' || date('m/d/Y',
                                strtotime($transaction['credit_expiration_date'])) == '12/31/1969') {
                        } else {
                            $expires = Carbon::createFromFormat('m/d/Y', $transaction['credit_expiration_date']);
                            $fromDate = $expires->format('Y-m-d');
                            $endDate = $expires->clone()->addYear()->format('Y-m-d');
                            $iceOptions = [];
                            $iceExtendBox = '';
                            if (isset($transaction['extension_valid']) && $transaction['extension_valid'] == 1 && $transaction['credit'] > 0) {
                                $iceOptions[] = '<option class="credit-extension"
                                     data-id="' . esc_attr($transaction['id']) . '"
                                     data-interval="' . esc_attr($transaction['unitinterval']) . '"
                                     data-datefrom="' . esc_attr($fromDate) . '"
                                     data-dateto="' . esc_attr($endDate) . '"
                                     data-amt="' . esc_attr(get_option('gpx_extension_fee', 0)) . '"
                                     >Extend</option>';
                            }
                            if (empty($transaction['credit_action']) && $key == 'deposit' && $transaction['credit'] > 0 && strtolower($transaction['status']) == 'active') {
                                $iceOptions[] = '<option class="credit-donate-btn" data-type="donated" data-id="' . $transaction['id'] . '">Donate</option>';
                                $iceOptions[] = '<option class="perks-link" data-type="perks" data-id="' . $transaction['id'] . '">Perks</option>';
                            }
                            if (!empty($iceOptions)) {
                                $transaction[$tk] = '<span class="extend-box">';
                                $transaction[$tk] .= '<select class="ice-select" style="max-width: 100px;">';
                                $transaction[$tk] .= '<option value="">Select</option>';
                                $transaction[$tk] .= implode('', $iceOptions);
                                $transaction[$tk] .= '</select>';
                                $transaction[$tk] .= $iceExtendBox;
                                $transaction[$tk] .= '</span>';
                            }
                        }
                    }
                    $output[$key] .= '<td class="' . $cancelledClass . '">';
                    $output[$key] .= $transaction[$tk];
                    if ($key != 'deposit' && $tk == 'weekId') {
                        if (isset($transaction['pending'])) {
                            $output[$key] .= ' -- Pending Deposit';
                        } else {
                            $output[$key] .= ' <a class="hide-slash" href="' . esc_url(site_url('/booking-path-confirmation') . "?confirmation={$transaction['cartID']}") . '" title="View Confirmation" target="_blank"><i class="fa fa-file-text" aria-hidden="true"></i></a>';
                            //is this a logged in agent?
                            if ($agent && $key != 'misc') {
                                $output[$key] .= ' | <a href="' . gpx_url('profile_transaction_modal', ['transaction' => $transaction['id']]) . '" class="agent-cancel-booking" data-agent="' . esc_attr($agent) . '" data-transaction="' . esc_attr($transaction['id']) . '" title="Edit Transaction"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                            }
                        }
                    }
                    if ($tk === 'GuestName' && !($transaction['cancelled'] ?? 0) && $agent) {
                        // add edit guest button for agents
                        $output[$key] .= ' <a href="' . gpx_url('profile_transaction_guest_modal', ['transaction' => $transaction['id']]) . '" class="profile-guest-edit" title="Edit Guest" data-id="' . esc_attr($transaction['id']) . '"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                    }
                    $output[$key] .= '</td>';
                }
                $output[$key] .= '</tr>';
            }
        }
        $output[$key] .= '</tbody>';
    }

    return $output;
}
