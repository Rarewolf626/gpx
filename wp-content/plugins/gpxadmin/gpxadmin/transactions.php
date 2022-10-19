<?php



/**
 *
 *
 *
 *
 */
function creditExtention()
{
    global $wpdb;


    $id = $_REQUEST['id'];
    $newdate = date('m/d/Y', strtotime($_REQUEST['dateExtension']));

    $sql = $wpdb->prepare("SELECT credit_expiration_date FROM wp_credit WHERE id=%s", $id);
    $row = $wpdb->get_row($sql);

    $moddata = [
        'type'=>'Credit Extension',
        'oldDate'=>$row->credit_expiration_date,
        'newDate'=>date('Y-m-d', strtotime($newdate)),
    ];

    $mod = [
        'credit_id'=>$id,
        'recorded_by'=>get_current_user_id(),
        'data'=>json_encode($moddata),
    ];

    $wpdb->insert('wp_credit_modification', $mod);

    $modID = $wpdb->insert_id;

    $update = [
        'credit_expiration_date' => date("Y-m-d", strtotime($newdate)),
        'extension_date' => date('Y-m-d'),
        'modification_id'=>$modID,
        'modified_date'=>date('Y-m-d'),
    ];

    $wpdb->update('wp_credit', $update, array('id'=>$id));

    /*
     * TODO: Test after functionality is confirmed
     */

    //send to SF
    $sf = Salesforce::getInstance();

    $sfDepositData = [
        'GPX_Deposit_ID__c'=>$id,
        'Credit_Extension_Date__c'=>date('Y-m-d'),
        'Expiration_Date__c'=>date('Y-m-d', strtotime($newdate)),
    ];

    $sfType = 'GPX_Deposit__c';
    $sfObject = 'GPX_Deposit_ID__c';

    $sfFields = [];
    $sfFields[0] = new SObject();
    $sfFields[0]->fields = $sfDepositData;
    $sfFields[0]->type = $sfType;

    $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);

    $msg = "Credit has been extended to ".$newdate;

    $cid=0; // Undefined variable $cid
    $return = array('success'=>true, 'message'=>$msg, 'date'=>$newdate, 'cid'=>$cid);
}

add_action('wp_ajax_creditExtention', 'creditExtention');
add_action('wp_ajax_nopriv_creditExtention', 'creditExtention');




/**
 *
 *
 *
 *
 */
function rework_missed_deposits()
{
    global $wpdb;

    $sql = "SELECT * FROM `deposit_rework` WHERE imported='33' LIMIT 1000";
    $rows = $wpdb->get_results($sql);
    foreach($rows as $row)
    {
        $wpdb->update('deposit_rework', array('imported'=>'5'), array('id'=>$row->id));
        $sql = $wpdb->prepare("SELECT `deposit used` FROM transactions_import_two WHERE weekId=%d AND MemberNumber=%d", [$row->weekId,$row->userID]);
        $odeposit = $wpdb->get_var($sql);
        if(!empty($odeposit))
        {

            $sql = $wpdb->prepare("SELECT a.id FROM wp_credit a
                    INNER JOIN import_credit_future_stay b ON
                        b.Deposit_year=a.deposit_year AND
                        b.resort_name=a.resort_name AND
                        b.unit_type=a.unit_type AND
                        b.Member_Name=a.owner_id
                        WHERE b.ID=%d",$odeposit);
            $deposit = $wpdb->get_var($sql);

            if(!empty($deposit))
            {
                $sql =  $wpdb->prepare("SELECT id, data FROM wp_gpxTransactions WHERE weekId=%d AND userID=%d",[$row->weekId,$row->userID]);
                $d = $wpdb->get_row($sql);
                if(!empty($d))
                {

                    $wpdb->update('deposit_rework', array('imported'=>'1'), array('id'=>$row->id));
                    $data = json_decode($d->data, true);
                    $data['creditweekid'] = $deposit;
                    $wpdb->update('wp_gpxTransactions', array('depositID'=>$deposit, 'data'=>json_encode($data)), array('id'=>$d->id));
                }
                else
                {
                    $wpdb->update('deposit_rework', array('imported'=>'2'), array('id'=>$row->id));
                }
            }
            else
            {
                $wpdb->update('deposit_rework', array('imported'=>'3'), array('id'=>$row->id));
            }
        }
        else
        {
            $wpdb->update('deposit_rework', array('imported'=>'4'), array('id'=>$row->id));
        }
    }


    $sql = "SELECT COUNT(id) as cnt FROM `deposit_rework` WHERE imported='33'";
    $tcnt = $wpdb->get_var($sql);

    if($tcnt>0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('remaining'=>$tcnt));
}
add_action('wp_ajax_rework_missed_deposits', 'rework_missed_deposits');








/**
 *
 *
 *
 *
 */
function rework_duplicate_credits()
{
    global $wpdb;


    $sql = "SELECT checked, txid FROM credit_dup_checked";
    $ck = $wpdb->get_results($sql, ARRAY_A);
    foreach($ck as $c)
    {
        $in[] = $c['checked'];
        $tx[] = $c['txid'];
    }


    if(count($in) > 0)
    {
        $sql = "select owner_id, deposit_year, unit_type, check_in_date, count(*) as NumDuplicates
                    from wp_credit
                    WHERE id NOT IN ('".implode("','", $in)."')
                    group by owner_id, deposit_year, unit_type, check_in_date
                    having NumDuplicates > 1 LIMIT 10";
    }
    else
    {
        $sql = "select owner_id, deposit_year, unit_type, check_in_date, count(*) as NumDuplicates
                    from wp_credit
                    group by owner_id, deposit_year, unit_type, check_in_date
                    having NumDuplicates > 1 LIMIT 10";
    }
    $results = $wpdb->get_results($sql);
    foreach($results as $result)
    {

        $wheres = [];
        foreach($result as $rk=>$rv)
        {
            if($rk == 'NumDuplicates')
            {
                continue;
            }
            $wheres[] = $wpdb->prepare(gpx_esc_table($rk)." = %s",$rv);
        }

        $sql = "SELECT id, owner_id, deposit_year, check_in_date, credit_amount, resort_name, unit_type
                FROM wp_credit WHERE ".implode(" AND ", $wheres)." ORDER BY id desc";

        $rows = $wpdb->get_results($sql);

        foreach($rows as $k=>$row)
        {
            $in[] = $row->id;
            $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE JSON_EXTRACT(data, '$.creditweekid') = %s", $row->id);
            $transaction = $wpdb->get_var($sql);

            $wpdb->insert('credit_dup_checked', array('checked'=>$row->id, 'txid'=>$transaction->id));


            if(!empty($transaction))
            {
                if(!in_array($transaction->id, $tx))
                {
                    unset($rows[$k]);
                }
            }
        }
        usort($rows, function($a, $b) {
            return $b->id - $a->id;
        });
        if(count($rows) > 1)
        {
            unset($rows[0]);
        }
        foreach($rows as $row)
        {
            $toInsert[] = $row;
        }
    }
    foreach($toInsert as $row)
    {
        $wpdb->insert('credit_dup_delete', (array) $row);
    }

    if(count($in) < 4233)
    {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('remaining'=>$tcnt));
}
add_action('wp_ajax_rework_duplicate_credits', 'rework_duplicate_credits');





/**
 *
 *
 *
 *
 */
function rework_tp_inactive()
{
    global $wpdb;

    $sql = "SELECT r.record_id FROM  wp_room r
            INNER JOIN import_partner_credits p ON p.record_id=r.record_id
            WHERE r.active=0 AND p.Active=1";
    $rows = $wpdb->get_results($sql);
    $i= 0;
    foreach($rows as $row)
    {
        $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s", $row->record_id);
        $t = $wpdb->get_var($sql);
        if(!empty($t))
        {
            continue;
        }
        $sql = $wpdb->prepare("SELECT id FROM wp_gpxPreHold WHERE weekId=%s AND released=0", $row->record_id);
        $h = $wpdb->get_var($sql);
        if(!empty($h))
        {
            continue;
        }
        $wpdb->update('wp_room', array('active'=>1, 'active_specific_date'=>'2030-01-01', 'active_rental_push_date'=>'2030-01-01'), array('record_id'=>$row->record_id));
        $i++;
    }
    $sql = "SELECT count(r.record_id) as cnt FROM  wp_room r
            INNER JOIN import_partner_credits p ON p.record_id=r.record_id
            WHERE r.active=0 AND p.Active=1";
    $tcnt = $wpdb->get_var($sql);

    wp_send_json(array('remaining'=>$tcnt));
}
add_action('wp_ajax_gpx_rework_tp_inactive', 'rework_tp_inactive');






/**
 *
 *
 *
 *
 */
// /**
//  * Import Credit
//  */

function gpx_import_credit_C()
{
    global $wpdb;

    $sql = "SElECT * FROM import_owner_credits WHERE imported=0 order by RAND() LIMIT 100";
    $imports = $wpdb->get_results($sql, ARRAY_A);
    if(empty($imports))
    {
        //try the other import function
        gpx_import_credit();
    }

    foreach($imports as $import)
    {

        $wpdb->update('import_owner_credits', array('imported'=>1), array('ID'=>$import['ID']));
        $sfImport = $import;

        $user = reset(
            get_users(
                array(
                    'meta_key' => 'DAEMemberNo',
                    'meta_value' => $import['Member_Name'],
                    'number' => 1,
                    'count_total' => false
                )
            )
        );
        if(empty($user))
        {

            //let's try to import this owner
            $user = function_GPX_Owner($import['Member_Name']);

            if(empty($user))
            {
                $exception = json_encode($import);
                $wpdb->insert("final_import_exceptions", array('type'=>'credit user', 'data'=>$exception));
                continue;
            }
        }

        $cid = $user->ID;
        $unit_week = '';
        $rid = '';


        $resortKeyOne = [
            'Butterfield Park - VI'=>'2440',
            'Grand Palladium White Sand - AI'=>'46895',
            'Grand Sirenis Riviera Maya Resort - AI'=>'46896',
            'High Point World Resort - RHC'=>'1549',
            'Los Abrigados Resort & Spa'=>'2467',
            'Makai Club Cottages'=>'1786',
            'Palm Canyon Resort & Spa'=>'1397',
            'Sunset Marina Resort & Yacht Club - AI'=>'46897',
            'Azul Beach Resort Negril by Karisma - AI'=>'46898',
            'Bali Villas & Sports Club - Rentals Only'=>'46899',
            'Blue Whale'=>'46900',
            'Bluegreen Club 36'=>'46901',
            'BreakFree Alexandra Beach'=>'46902',
            'Classic @ Alpha Sovereign Hotel'=>'46903',
            'Club Regina Los Cabos'=>'46904',
            'Eagles Nest Resort - VI'=>'1836',
            'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive'=>'46905',
            'El Dorado Maroma by Karisma a Gourmet AI'=>'46906',
            'El Dorado Royale by Karisma a Gourmet AI'=>'46907',
            'Fiesta Ameri. Vac Club At Cabo Del Sol'=>'46908',
            'Fort Brown Condo Shares'=>'46909',
            'Four Seasons Residence Club Scottsdale@Troon North'=>'2457',
            'Generations Riviera Maya by Karisma a Gourmet AI'=>'46910',
            'GPX Cruise Exchange'=>'SKIP',
            'Grand Palladium Jamaica Resort & Spa - AI'=>'46911',
            'Grand Palladium Vallarta Resort & Spa - AI'=>'46912',
            'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive'=>'46913',
            'High Sierra Condominiums'=>'46914',
            'Kiltannon Home Farm'=>'46915',
            'Knocktopher Abbey'=>'46916',
            'Knocktopher Abbey (Shadowed)'=>'46916',
            'Laguna Suites Golf and Spa - AI'=>'46917',
            'Maison St. Charles - Rentals Only'=>'46918',
            'Makai Club Resort'=>'1787',
            'Marina Del Rey Beach Club - No Longer Accepting'=>'46919',
            'Mantra Aqueous on Port'=>'46920',
            'Maui Sunset - Rentals Only'=>'1758',
            'Mayan Palace Mazatlan'=>'3652',
            'Ocean Gate Resort'=>'46921',
            'Ocean Spa Hotel - AI'=>'46922',
            'Paradise'=>'46923',
            'Park Royal Homestay Club Cala'=>'338',
            'Park Royal Los Cabos - RHC'=>'46924',
            'Peacock Suites Resort'=>'46925',
            'Pounamu Apartments - Rental'=>'46926',
            'Presidential Suites by LHVC - Punta Cana NON - AI'=>'46927',
            'RHC - Park Royal - Los Tules'=>'46928',
            'Royal Regency Paris (Shadowed)'=>'479',
            'Royal Sunset - AI'=>'46929',
            'Secrets Puerto Los Cabos Golf & Spa Resort - AI'=>'46930',
            'Secrets Wild Orchid Montego Bay - AI'=>'46931',
            'Solare Bahia Mar - Rentals Only'=>'46932',
            'Tahoe Trail - VI'=>'40',
            'The RePlay Residence'=>'46933',
            'The Tropical at LHVC - AI'=>'46934',
            'Vacation Village at Williamsburg'=>'2432',
            'Wolf Run Manor At Treasure Lake'=>'46935',
            'Wyndham Grand Desert - 3 Nights'=>'46936',
            'Wyndham Royal Garden at Waikiki - Rental Only'=>'1716',
        ];

        $resortKeyTwo = [
            'Royal Aloha Chandler - Butterfield Park'=>'2440',
            'Grand Palladium White Sand - AI'=>'46895',
            'Grand Sirenis Riviera Maya Resort - AI'=>'46896',
            'High Point World Resort'=>'1549',
            'Los Abrigados Resort and Spa'=>'2467',
            'Makai Club Resort Cottages'=>'1786',
            'Palm Canyon Resort and Spa'=>'1397',
            'Sunset Marina Resort & Yacht Club - AI'=>'46897',
            'Azul Beach Resort Negril by Karisma - AI'=>'46898',
            'Bali Villas & Sports Club - Rentals Only'=>'46899',
            'Blue Whale'=>'46900',
            'Bluegreen Club 36'=>'46901',
            'BreakFree Alexandra Beach'=>'46902',
            'Classic @ Alpha Sovereign Hotel'=>'46903',
            'Club Regina Los Cabos'=>'46904',
            'Royal Aloha Branson - Eagles Nest Resort'=>'1836',
            'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive'=>'46905',
            'El Dorado Maroma by Karisma a Gourmet AI'=>'46906',
            'El Dorado Royale by Karisma a Gourmet AI'=>'46907',
            'Fiesta Ameri. Vac Club At Cabo Del Sol'=>'46908',
            'Fort Brown Condo Shares'=>'46909',
            'Four Seasons Residence Club Scottsdale at Troon North'=>'2457',
            'Generations Riviera Maya by Karisma a Gourmet AI'=>'46910',
            'SKIP'=>'SKIP',
            'Grand Palladium Jamaica Resort & Spa - AI'=>'46911',
            'Grand Palladium Vallarta Resort & Spa - AI'=>'46912',
            'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive'=>'46913',
            'High Sierra Condominiums'=>'46914',
            'Kiltannon Home Farm'=>'46915',
            'Knocktopher Abbey'=>'46916',
            'Laguna Suites Golf and Spa - AI'=>'46917',
            'Maison St. Charles - Rentals Only'=>'46918',
            'Makai Club Resort Condos'=>'1787',
            'Marina Del Rey Beach Club - No Longer Accepting'=>'46919',
            'Mantra Aqueous on Port'=>'46920',
            'Maui Sunset'=>'1758',
            'Mayan Palace Mazatlan by Grupo Vidanta'=>'3652',
            'Ocean Gate Resort'=>'46921',
            'Ocean Spa Hotel - AI'=>'46922',
            'Paradise'=>'46923',
            'Royal Holiday - Park Royal Club Cala'=>'338',
            'Park Royal Los Cabos - RHC'=>'46924',
            'Peacock Suites Resort'=>'46925',
            'Pounamu Apartments - Rental'=>'46926',
            'Presidential Suites by LHVC - Punta Cana NON - AI'=>'46927',
            'RHC - Park Royal - Los Tules'=>'46928',
            'Royal Regency By Diamond Resorts'=>'479',
            'Royal Sunset - AI'=>'46929',
            'Secrets Puerto Los Cabos Golf & Spa Resort - AI'=>'46930',
            'Secrets Wild Orchid Montego Bay - AI'=>'46931',
            'Solare Bahia Mar - Rentals Only'=>'46932',
            'Royal Aloha Tahoe'=>'40',
            'The RePlay Residence'=>'46933',
            'The Tropical at LHVC - AI'=>'46934',
            'Williamsburg Plantation Resort'=>'2432',
            'Wolf Run Manor At Treasure Lake'=>'46935',
            'Wyndham Grand Desert - 3 Nights'=>'46936',
            'Royal Garden at Waikiki Resort'=>'1716',
            'Costa Sur Resort & Spa'=>'46872',
        ];

        $sql = $wpdb->prepare("SELECT gprID, ResortName FROM wp_resorts WHERE id=%s", $resortKeyOne[$import['resort_name']]);
        $resortDet = $wpdb->get_row($sql);

        $rid = $resortDet->gprID;
        $resortName = $resortDet->ResortName;

        if(empty($resortName))
        {
            $resortName = $import['resort_name'];
            $resortName = str_replace("- VI", "", $resortName);
            $resortName = trim($resortName);
            $sql = $wpdb->prepare("SELECT gprID FROM wp_resorts WHERE ResortName=%s", $resortName);
            $rid = $wpdb->get_var($sql);
        }
        if(empty($rid))
        {
            $exception = json_encode($import);
            $wpdb->update('import_exceptions', array('validated'=>2), array('id'=>$result['id']));
            $wpdb->insert("final_import_exceptions", array('type'=>'credit resort', 'data'=>$exception));
            continue;
        }

        $email = $user->Email;

        $sfDepositData = [

            'Check_In_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c'=>date('Y-m-d', strtotime($import['Credit_expiratio_date'])),
            'Deposit_Year__c'=>$import['Deposit_year'],
            'Account_Name__c'=>$user->Property_Owner__c,
            'GPX_Member__c'=>$cid,
            'Resort__c'=>$rid,
            'Resort_Name__c'=>str_replace("&", "&amp;", $import['resort_name']),
            'Resort_Unit_Week__c'=>$unit_week,
            'Unit_Type__c'=>$import['unit_type'],
            'Member_Email__c'=>$email,
            'Member_First_Name__c'=>$user->FirstName1,
            'Member_Last_Name__c'=>$user->LastName1,
            'Credits_Issued__c'=>$import['credit_amount'] + $import['credit_used'],
            'Credits_Used__c'=>$import['credit_used'],
            'Deposit_Status__c'=>$import['status'],
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
        foreach($timport as $k=>$v)
        {
            if($k == 'status' || $k == 'credit_expiration_date' || $k == 'credit_used')
            {
                continue;
            }
            $wheres[] = $wpdb->prepare(gpx_esc_table($k) . " = %s",$v);
        }

        $sql = "SELECT id FROM wp_credit WHERE ".implode(" AND ", $wheres);
        $row = $wpdb->get_row($sql);

        if(empty($row))
        {
            $wpdb->insert('wp_credit', $timport);
        }
        else
        {
            $wpdb->update('wp_credit', $timport, array('id'=>$row->id));
        }

        $sf = Salesforce::getInstance();

        $insertID  = $wpdb->insert_id;

        $sfDepositData['GPX_Deposit_ID__c'] = $insertID;

        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';

        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfDepositData;
        $sfFields[0]->type = $sfType;

        $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);

        $wpdb->update('import_owner_credits', array('sfError'=>json_encode($sfDepositAdd)), array('ID'=>$import['ID']));

        $record = $sfDepositAdd[0]->id;
        $wpdb->update('wp_credit', array('record_id'=>$record, 'sf_name'=>$sfDepositAdd[0]->Name), array('id'=>$insertID));

    }
    $sql = "SElECT count(id) as cnt  FROM import_owner_credits WHERE imported=0";
    $remain = $wpdb->get_var($sql);

    if($remain > 0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('remaining'=>$remain));
}
add_action('wp_ajax_gpx_import_credit_C', 'gpx_import_credit_C');
add_action('wp_ajax_nopriv_gpx_import_credit_C', 'gpx_import_credit_C');

/**
 *
 *
 *
 *
 */
/**
 * Import Credit
 */
function gpx_import_closure_credit()
{
    global $wpdb;
    $sf = Salesforce::getInstance();

    $sql = "SElECT * FROM closure_credits_import WHERE imported=0 AND AccoutID != '7100227'  LIMIT 100";
    $imports = $wpdb->get_results($sql, ARRAY_A);

    $nctooc = [
        'Member_Name'=>'AccoutID',
        'check_in_date'=>'CheckIn',
        'unit_type'=>'UnitSize',
        'week_id'=>'Week_ID',
    ];

    foreach($imports as $import)
    {

        foreach($nctooc as $n=>$o)
        {
            $import[$n] = $import[$o];
        }

        $sfImport = $import;

        $user = get_user_by('ID', $import['Member_Name']);


        if(empty($user))
        {

            $user = reset(
                get_users(
                    array(
                        'meta_key' => 'GPX_Member_VEST__c',
                        'meta_value' => $import['Member_Name'],
                        'number' => 1,
                        'count_total' => false
                    )
                )
            );
            if(!empty($user))
            {
                $ou = $user->ID;
            }
            else
            {
                $user = reset(
                    get_users(
                        array(
                            'meta_key' => 'DAEMemberNo',
                            'meta_value' => $import['Member_Name'],
                            'number' => 1,
                            'count_total' => false
                        )
                    )
                );

                if(empty($user))
                {

                    continue;
                }

            }

        }

        $cid = $user->ID;
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );

        $rid = $import['Deposit_Resort'];
        $sql = $wpdb->prepare("SELECT ResortName, gprID FROM wp_resorts WHERE id=%s",$rid );
        $resortDets = $wpdb->get_row($sql);
        $resortName = $resortDets->ResortName;


        $rid = $resortDets->gprID;

        if(empty($rid))
        {
            $exception = json_encode($import);
            continue;
        }


        $import['credit_amount'] = 1;
        $import['credit_used'] = $import['credit_amount'] - $import['CRBal'];
        $import['Deposit_year'] = '2020';
        $import['status'] = 'Approved';

        $email = $user->Email;

        $accountSQL = "SELECT Name from GPR_Owner_ID__c WHERE GPX_Member_VEST__c='".$cid."'";

        $accountResults = $sf->query($accountSQL);

        foreach($accountResults as $acc)
        {
            $account = $acc->fields;
            $accountName = $account->Id;
        }

        $sfDepositData = [

            'Check_In_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'].'+2 years')),
            'Deposit_Year__c'=>$import['Deposit_year'],
            'GPX_Member__c'=>$cid,
            'Resort__c'=>$rid,
            'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;", $resortName)),
            'Resort_Unit_Week__c'=>$unit_week,
            'Unit_Type__c'=>$import['unit_type'],
            'Member_Email__c'=>$email,
            'Member_First_Name__c'=>str_replace("&", " AND ", $user->FirstName1),
            'Member_Last_Name__c'=>$user->LastName1,
            'Credits_Issued__c'=>$import['credit_amount'],
            'Credits_Used__c'=>$import['credit_used'],
            'Deposit_Status__c'=>$import['status'],
            'Coupon__c'=>$import['Couponcode'],
        ];

        $timport['resort_name'] = $resortName;
        $timport['check_in_date'] = date('Y-m-d', strtotime($import['check_in_date']));
        $timport['credit_expiration_date'] = date('Y-m-d', strtotime($import['check_in_date'].'+2 years'));
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
        foreach($twheres as $tw)
        {
            $iwheres[] = $wpdb->prepare(gpx_esc_table($tw)." = %s",$timport[$tw]);
        }
        $twhere = implode(" AND ", $iwheres);
        $sql = "SELECT id FROM wp_credit WHERE ".$twhere;
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

        $wpdb->update('wp_credit', array('record_id'=>$record, 'sf_name'=>$sfDepositAdd[0]->Name), array('id'=>$insertID));
        $wpdb->update('closure_credits_import', array('new_id'=>$insertID, 'imported'=>'1'), array('id'=>$import['id']));
    }
    $sql = "SELECT count(id) as cnt FROM closure_credits_import WHERE imported=2";
    $remain = $wpdb->get_var($sql);

    wp_send_json(array('remaining'=>$remain));
}
add_action('wp_ajax_gpx_import_closure_credit', 'gpx_import_closure_credit');

/**
 *
 *
 *
 *
 */
/**
 * Import Credit
 */
function gpx_import_credit_rework($single='')
{
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

    foreach($imports as $import)
    {
        $weekID = $import['week_id'];
        $resortID = $import['missing_resort_id'];
        if($resortID == "NULL")
        {
            $resortID = '';
        }

        $sfImport = $import;

        $user = get_user_by('ID', $import['Member_Name']);

        if(empty($user))
        {
            $wpdb->update('import_owner_credits', array('imported'=>2), array('ID'=>$import['ID']));
            continue;
        }

        $cid = $user->ID;
        $unit_week = '';
        $rid = '';

        if(!empty($import['missing_resort_id']))
        {
            $sql = $wpdb->prepare("SELECT gprID, ResortName FROM wp_resorts WHERE id=%s", $import['missing_resort_id']);
            $resortInfo = $wpdb->get_row($sql);
            $rid = $resortInfo->gprID;
            $import['resort_name'] = $resortInfo->ResortName;
        }
        if(empty($rid))
        {
            $resortName = $import['resort_name'];
            $resortName = str_replace("- VI", "", $resortName);
            $resortName = trim($resortName);
            $sql = $wpdb->prepare("SELECT gprID, ResortName FROM wp_resorts WHERE ResortName=%s", $resortName);

            $resortInfo = $wpdb->get_row($sql);
            $rid = $resortInfo->gprID;
            $import['resort_name'] = $resortInfo->ResortName;


            if(!empty($rid))
            {
                $sql = "SELECT unitweek FROM wp_mapuser2oid WHERE gpx_user_id='".$cid."' AND resortID='".substr($rid, 0, 15)."'";
            }
            else
            {
                //pull from the transaction
                $sql = $wpdb->prepare("SELECT b.gprID, b.ResortName FROM wp_gpxTransactions a
                        INNER JOIN wp_resorts b on a.resortId=b.ResortID
                        WHERE a.weekId=%s AND a.userID=%s", [$import['week_id'], $cid]);
                $resortInfo = $wpdb->get_row($sql);

                $rid = $resortInfo->ResortName;
                $import['resort_name'] = $resortInfo->ResortName;
                if(empty($rid))
                {
                    $exception = json_encode($import);
                    $wpdb->update('import_owner_credits', array('imported'=>3), array('ID'=>$import['ID']));
                    continue;
                }
            }
        }

        $email = $user->Email;
        if(empty($email))
        {
            $email = $users->user_email;
        }

        $accountSQL = "SELECT Name from GPR_Owner_ID__c WHERE GPX_Member_VEST__c='".$cid."'";

        $accountResults = $sf->query($accountSQL);

        foreach($accountResults as $acc)
        {
            $account = $acc->fields;
            $accountName = $account->Name;
            $accountID = $account->ID;
        }

        $plus = '2';
        if($import['extended'] !='#N/A')
        {
            $plus = '3';
        }
        $sfDepositData = [

            'Check_In_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'].'+'.$plus.' year')),
            'Deposit_Year__c'=>$import['Deposit_year'],
            'GPX_Member__c'=>$cid,
            'Resort__c'=>$rid,
            'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;", $import['resort_name'])),
            'Resort_Unit_Week__c'=>$unit_week,
            'Unit_Type__c'=>$import['unit_type'],
            'Member_Email__c'=>$email,
            'Member_First_Name__c'=>stripslashes(str_replace("&", "&amp;", $user->FirstName1)),
            'Member_Last_Name__c'=>stripslashes(str_replace("&", "&amp;", $user->LastName1)),
            'Credits_Issued__c'=>$import['credit_amount'],
            'Credits_Used__c'=>$import['credit_used'],
            'Deposit_Status__c'=>$import['status'],
        ];
        $timport['resort_name'] = $resortName;
        $timport['check_in_date'] = date('Y-m-d', strtotime($import['check_in_date']));
        $timport['credit_expiration_date'] = date('Y-m-d', strtotime($import['check_in_date'].'+1 year'));
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
        foreach($updateCheck as $tw)
        {
            $iwheres[] = $wpdb->prepare(gpx_esc_table($tw)." = %s",$timport[$tw]);
        }

        $twhere = implode(" AND ", $iwheres);
        $sql = "SELECT id FROM wp_credit WHERE ".$twhere;
        $isCredit = $wpdb->get_row($sql);

        $sfUpdate = '1';
        if(!empty($isCredit))
        {
            $wpdb->update('wp_credit', $timport, array('id'=>$isCredit->id));
            $insertID  = $isCredit->id;
        }
        else
        {
            $wpdb->insert('wp_credit', $timport);
            $insertID  = $wpdb->insert_id;
            $sfUpdate = $insertID;
        }

        $sfDepositData['GPX_Deposit_ID__c'] = $insertID;

        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';

        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfDepositData;
        $sfFields[0]->type = $sfType;

        if(!empty($sfUpdate))
        {
            $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);
        }

        $wpdb->update('import_credit_future_stay', array('sfError'=>json_encode($sfDepositAdd)), array('ID'=>$import['ID']));
        $record = $sfDepositAdd[0]->id;

        $wpdb->update('wp_credit', array('record_id'=>$record, 'sf_name'=>$sfDepositAdd[0]->Name), array('id'=>$insertID));
    }



    $sql = "SELECT COUNT(a.ID) as cnt FROM `import_credit_future_stay` a
    INNER JOIN wp_credit b ON a.Member_Name=b.owner_id AND b.deposit_year=a.Deposit_year
    WHERE record_id IS NULL and b.status != 'DOE' and b.created_date < '2021-01-01' AND a.imported=1 AND sfError=''";
    $remain = $wpdb->get_var($sql);

    if($remain > 0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('credit'=>$insertID,'remaining'=>$remain));
}
add_action('wp_ajax_gpx_credit_to_sf', 'gpx_import_credit_rework');


/**
 *
 *
 *
 *
 */
function gpx_import_credit($single='')
{
    global $wpdb;
    $sf = Salesforce::getInstance();

    $sql = "SELECT * FROM import_credit_future_stay WHERE ID NOT IN (SELECT a.ID FROM `import_credit_future_stay` a
            INNER JOIN wp_gpxTransactions b on b.weekId=a.week_id)";
    $imports = $wpdb->get_results($sql, ARRAY_A);

    foreach($imports as $import)
    {

        $weekID = $import['week_id'];
        $resortID = $import['missing_resort_id'];
        if($resortID == "NULL")
        {
            $resortID = '';
        }


        $wpdb->update('import_credit_future_stay', array('imported'=>5), array('ID'=>$import['ID']));
        $sfImport = $import;

        $user = get_user_by('ID', $import['Member_Name']);

        if(empty($user))
        {
            $wpdb->update('import_credit_future_stay', array('imported'=>2), array('ID'=>$import['ID']));
            continue;
        }

        $cid = $user->ID;
        $unit_week = '';
        $rid = '';

        $email = $user->Email;

        if(empty($email))
        {
            $email = $users->user_email;
        }

        $accountSQL = "SELECT Name from GPR_Owner_ID__c WHERE GPX_Member_VEST__c='".$cid."'";

        $accountResults = $sf->query($accountSQL);

        foreach($accountResults as $acc)
        {
            $account = $acc->fields;
            $accountName = $account->Name;
            $accountID = $account->ID;
        }

        $plus = '2';
        if($import['extended'] !='#N/A')
        {
            $plus = '3';
        }
        $sfDepositData = [
            'Check_In_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'].'+'.$plus.' year')),
            'Deposit_Year__c'=>$import['Deposit_year'],
            'GPX_Member__c'=>$cid,
            'Resort__c'=>$rid,
            'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;", $import['resort_name'])),
            'Resort_Unit_Week__c'=>$unit_week,
            'Unit_Type__c'=>$import['unit_type'],
            'Member_Email__c'=>$email,
            'Member_First_Name__c'=>str_replace("&", "&amp;", $user->FirstName1),
            'Member_Last_Name__c'=>$user->LastName1,
            'Credits_Issued__c'=>$import['credit_amount'],
            'Credits_Used__c'=>$import['credit_used'],
            'Deposit_Status__c'=>$import['status'],
        ];
        $timport['resort_name'] = $resortName;
        $timport['check_in_date'] = date('Y-m-d', strtotime($import['check_in_date']));
        $timport['credit_expiration_date'] = date('Y-m-d', strtotime($import['check_in_date'].'+1 year'));
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
        foreach($updateCheck as $tw)
        {
            $iwheres[] = $wpdb->prepare(gpx_esc_table($tw)." = %s",$timport[$tw]);
        }

        $twhere = implode(" AND ", $iwheres);
        $sql = "SELECT id FROM wp_credit WHERE ".$twhere;
        $isCredit = $wpdb->get_row($sql);

        $sfUpdate = '1';
        if(!empty($isCredit))
        {
            $wpdb->update('wp_credit', $timport, array('id'=>$isCredit->id));
            $insertID  = $isCredit->id;
        }
        else
        {
            $wpdb->insert('wp_credit', $timport);
            $insertID  = $wpdb->insert_id;
            $sfUpdate = $insertID;
        }

        $sfDepositData['GPX_Deposit_ID__c'] = $insertID;

        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';

        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfDepositData;
        $sfFields[0]->type = $sfType;

        if(!empty($sfUpdate))
        {
            $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);
        }

        $record = $sfDepositAdd[0]->id;

        $wpdb->update('wp_credit', array('record_id'=>$record, 'sf_name'=>$sfDepositAdd[0]->Name), array('id'=>$insertID));
        $wpdb->update('import_credit_future_stay', array('new_id'=>$wpdb->insert_id), array('id'=>$import['id']));

        $sql = $wpdb->prepare("SELECT id, data FROM wp_gpxTransactions WHERE weekId=%s AND userID=%s", [$import['week_id'], $cid]);
        $trans = $wpdb->get_row($sql);

        if(!empty($trans))
        {
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

            if(!empty($sfUpdate))
            {
                $sfUpdateTransaction = $sf->gpxUpsert($sfObject, $sfFields);
            }

            $transData['creditweekid'] = $insertID;

            $wpdb->update('wp_gpxTransactions', array('depositID'=>$insertID, 'data'=>json_encode($transData)), array('id'=>$trans->id));
        }
    }


    $sql = "SELECT COUNT(ID) as cnt FROM import_credit_future_stay WHERE ID NOT IN (SELECT a.ID FROM `import_credit_future_stay` a
            INNER JOIN wp_gpxTransactions b on b.weekId=a.week_id)";
    $remain = $wpdb->get_var($sql);

    wp_send_json(array('remaining'=>$remain));
}
add_action('wp_ajax_gpx_import_credit', 'gpx_import_credit_C');



/**
 *
 *
 *
 *
 */
function gpx_import_credit_future_stay($single='')
{
    global $wpdb;
    $sf = Salesforce::getInstance();

    $sql = "SElECT * FROM import_credit_future_stay WHERE imported=0";
    $imports = $wpdb->get_results($sql, ARRAY_A);

    foreach($imports as $import)
    {

        $weekID = $import['week_id'];
        $resortID = $import['missing_resort_id'];
        if($resortID == "NULL")
        {
            $resortID = '';
        }

        $wpdb->update('import_credit_future_stay', array('imported'=>5), array('ID'=>$import['ID']));
        $sfImport = $import;

        $user = get_user_by('ID', $import['Member_Name']);

        if(empty($user))
        {
            $wpdb->update('import_credit_future_stay', array('imported'=>2), array('ID'=>$import['ID']));
            continue;
        }

        $cid = $user->ID;
        $unit_week = '';
        $rid = '';


        $email = $user->Email;
        if(empty($email))
        {
            $email = $users->user_email;
        }

        $accountSQL = "SELECT Name from GPR_Owner_ID__c WHERE GPX_Member_VEST__c='".$cid."'";

        $accountResults = $sf->query($accountSQL);

        foreach($accountResults as $acc)
        {
            $account = $acc->fields;
            $accountName = $account->Name;
            $accountID = $account->ID;
        }

        $plus = '2';
        if($import['extended'] !='#N/A')
        {
            $plus = '3';
        }
        $sfDepositData = [
            'Check_In_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'].'+'.$plus.' year')),
            'Deposit_Year__c'=>$import['Deposit_year'],
            'GPX_Member__c'=>$cid,
            'Resort__c'=>$rid,
            'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;", $import['resort_name'])),
            'Resort_Unit_Week__c'=>$unit_week,
            'Unit_Type__c'=>$import['unit_type'],
            'Member_Email__c'=>$email,
            'Member_First_Name__c'=>str_replace("&", "&amp;", $user->FirstName1),
            'Member_Last_Name__c'=>$user->LastName1,
            'Credits_Issued__c'=>$import['credit_amount'],
            'Credits_Used__c'=>$import['credit_used'],
            'Deposit_Status__c'=>$import['status'],
        ];
        $timport['resort_name'] = $resortName;
        $timport['check_in_date'] = date('Y-m-d', strtotime($import['check_in_date']));
        $timport['credit_expiration_date'] = date('Y-m-d', strtotime($import['check_in_date'].'+1 year'));
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
        foreach($updateCheck as $tw)
        {
            $iwheres[] = $wpdb->prepare(gpx_esc_table($tw)."= %s",$timport[$tw]);
        }

        $twhere = implode(" AND ", $iwheres);
        $sql = "SELECT id FROM wp_credit WHERE ".$twhere;
        $isCredit = $wpdb->get_row($sql);

        $sfUpdate = '1';
        if(!empty($isCredit))
        {
            $wpdb->update('wp_credit', $timport, array('id'=>$isCredit->id));
            $insertID  = $isCredit->id;
        }
        else
        {
            $wpdb->insert('wp_credit', $timport);
            $insertID  = $wpdb->insert_id;
            $sfUpdate = $insertID;
        }

        $sfDepositData['GPX_Deposit_ID__c'] = $insertID;

        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';

        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfDepositData;
        $sfFields[0]->type = $sfType;

        if(!empty($sfUpdate))
        {
            $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);
        }

        $record = $sfDepositAdd[0]->id;

        $wpdb->update('wp_credit', array('record_id'=>$record, 'sf_name'=>$sfDepositAdd[0]->Name), array('id'=>$insertID));
        $wpdb->update('import_credit_future_stay', array('new_id'=>$wpdb->insert_id), array('id'=>$import['id']));

        $sql = $wpdb->prepare("SELECT id, data FROM wp_gpxTransactions WHERE weekId=%s AND userID=%s", [$import['week_id'], $cid]);
        $trans = $wpdb->get_row($sql);

        if(!empty($trans))
        {
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

            if(!empty($sfUpdate))
            {
                $sfUpdateTransaction = $sf->gpxUpsert($sfObject, $sfFields);
            }

            $transData['creditweekid'] = $insertID;

            $wpdb->update('wp_gpxTransactions', array('depositID'=>$insertID, 'data'=>json_encode($transData)), array('id'=>$trans->id));
        }
    }

    $sql = "SELECT COUNT(ID) as cnt FROM import_credit_future_stay WHERE imported=0";
    $remain = $wpdb->get_var($sql);

    wp_send_json(array('remaining'=>$remain));
}
add_action('wp_ajax_gpx_import_credit_future_stay', 'gpx_import_credit_future_stay');


/**
 *
 *
 *
 *
 */
function gpx_missed_credit_to_sf()
{
    global $wpdb;
    $sf = Salesforce::getInstance();

    $sql = "SELECT * FROM `wp_credit` WHERE `record_id` IS NULL AND `status` != 'DOE'";
    $rows = $wpdb->get_results($sql, ARRAY_A);

    if(!empty($rows))
    {
        foreach($rows as $import)
        {

            $sfDepositData = [
                'GPX_Deposit_ID__c '=>$import['id'],
                'Check_In_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'])),
                'Expiration_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'].'+'.$plus.' year')),
                'Deposit_Year__c'=>$import['Deposit_year'],

                'GPX_Member__c'=>$import['owner_id'],

                'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;", $import['resort_name'])),
                'Unit_Type__c'=>$import['unit_type'],

                'Credits_Used__c'=>$import['credit_used'],
                'Deposit_Status__c'=>$import['status'],
            ];

            if(!empty($import['credit_amount']))
            {
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

            $wpdb->update('wp_credit', array('record_id'=>$record, 'sf_name'=>$sfDepositAdd[0]->Name), array('id'=>$import['id']));

            $user = get_user_by('ID', $import['owner_id']);

            $sfDepositData = [];
            $sfDepositData['GPX_Deposit_ID__c '] = $import['id'];
            if(!empty($user))
            {
                $email = $user->Email;
                if(empty($email))
                {
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

            if(!empty($resortInfo->gprID))
            {
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

    wp_send_json(array('added'=>''));
}
add_action('wp_ajax_gpx_missed_credit_to_sf', 'gpx_missed_credit_to_sf');



/**
 *
 *
 *
 *
 */
function gpx_import_transactions_manual($table='transactions_import_two', $id='', $resort='')
{
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $table='transactions_import';
    $tt = 'transaction1';
    if($_GET['table'] == 'two')
    {
        $table = 'transactions_import_two';
        $tt = 'transaction2';
    }
    if($_GET['table'] == 'owner')
    {
        $table = 'transactions_import_owner';
        $tt = 'transactionOwner';
    }

    if($_GET['table'] == 'two')
    {
        $table = 'transactions_import_two';
        $tt = 'transaction2';
    }
    if($_GET['table'] == 'owner')
    {
        $table = 'transactions_import_owner';
        $tt = 'transactionOwner';
    }

    $where = 'imported=0';
    if(!empty($id))
    {
        $where = $wpdb->prepare('id=%d',$id);
    }

    $sql = "SELECT * FROM ".gpx_esc_table($table)." WHERE ".$where." ORDER BY RAND() LIMIT 40";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {

        $wpdb->update($table, array('imported'=>2), array('id'=>$row->id));


        if($row->GuestName == '#N/A')
        {
            $exception = json_encode($row);
            $wpdb->insert("final_import_exceptions", array('type'=>$tt.' guest formula error', 'data'=>$exception));
            continue;
        }

        $resortKeyOne = [
            'Butterfield Park - VI'=>'2440',
            'Grand Palladium White Sand - AI'=>'46895',
            'Grand Sirenis Riviera Maya Resort - AI'=>'46896',
            'High Point World Resort - RHC'=>'1549',
            'Los Abrigados Resort & Spa'=>'2467',
            'Makai Club Cottages'=>'1786',
            'Palm Canyon Resort & Spa'=>'1397',
            'Sunset Marina Resort & Yacht Club - AI'=>'46897',
            'Azul Beach Resort Negril by Karisma - AI'=>'46898',
            'Bali Villas & Sports Club - Rentals Only'=>'46899',
            'Blue Whale'=>'46900',
            'Bluegreen Club 36'=>'46901',
            'BreakFree Alexandra Beach'=>'46902',
            'Classic @ Alpha Sovereign Hotel'=>'46903',
            'Club Regina Los Cabos'=>'46904',
            'Eagles Nest Resort - VI'=>'1836',
            'El Dorado Casitas Royale by Karisma'=>'46905',
            'El Dorado Casitas Royale by Karisma, a Gourmet AIl Inclusive'=>'46905',
            'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive'=>'46905',
            'El Dorado Maroma by Karisma, a Gourmet AI'=>'46906',
            'El Dorado Maroma by Karisma a Gourmet AI'=>'46906',
            'El Dorado Royale by Karisma a Gourmet AI'=>'46907',
            'El Dorado Royale by Karisma, a Gourmet AI'=>'46907',
            'Fiesta Ameri. Vac Club At Cabo Del Sol'=>'46908',
            'Fort Brown Condo Shares'=>'46909',
            'Four Seasons Residence Club Scottsdale@Troon North'=>'2457',
            'Generations Riviera Maya by Karisma a Gourmet AI'=>'46910',
            'GPX Cruise Exchange'=>'SKIP',
            'Grand Palladium Jamaica Resort & Spa - AI'=>'46911',
            'Grand Palladium Vallarta Resort & Spa - AI'=>'46912',
            'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive'=>'46913',
            'High Sierra Condominiums'=>'46914',
            'Kiltannon Home Farm'=>'46915',
            'Knocktopher Abbey'=>'46916',
            'Knocktopher Abbey (Shadowed)'=>'46916',
            'Laguna Suites Golf and Spa - AI'=>'46917',
            'Maison St. Charles - Rentals Only'=>'46918',
            'Makai Club Resort'=>'1787',
            'Marina Del Rey Beach Club - No Longer Accepting'=>'46919',
            'Mantra Aqueous on Port'=>'46920',
            'Maui Sunset - Rentals Only'=>'1758',
            'Mayan Palace Mazatlan'=>'3652',
            'Ocean Gate Resort'=>'46921',
            'Ocean Spa Hotel - AI'=>'46922',
            'Paradise'=>'46923',
            'Park Royal Homestay Club Cala'=>'338',
            'Park Royal Los Cabos - RHC'=>'46924',
            'Peacock Suites Resort'=>'46925',
            'Pounamu Apartments - Rental'=>'46926',
            'Presidential Suites by LHVC - Punta Cana NON - AI'=>'46927',
            'RHC - Park Royal - Los Tules'=>'46928',
            'Royal Regency Paris (Shadowed)'=>'479',
            'Royal Sunset - AI'=>'46929',
            'Secrets Puerto Los Cabos Golf & Spa Resort - AI'=>'46930',
            'Secrets Wild Orchid Montego Bay - AI'=>'46931',
            'Solare Bahia Mar - Rentals Only'=>'46932',
            'Tahoe Trail - VI'=>'40',
            'The RePlay Residence'=>'46933',
            'The Tropical at LHVC - AI'=>'46934',
            'Vacation Village at Williamsburg'=>'2432',
            'Wolf Run Manor At Treasure Lake'=>'46935',
            'Wyndham Grand Desert - 3 Nights'=>'46936',
            'Wyndham Royal Garden at Waikiki - Rental Only'=>'1716',
        ];

        $resortKeyTwo = [
            'Royal Aloha Chandler - Butterfield Park'=>'2440',
            'Grand Palladium White Sand - AI'=>'46895',
            'Grand Sirenis Riviera Maya Resort - AI'=>'46896',
            'High Point World Resort'=>'1549',
            'Los Abrigados Resort and Spa'=>'2467',
            'Makai Club Resort Cottages'=>'1786',
            'Palm Canyon Resort and Spa'=>'1397',
            'Sunset Marina Resort & Yacht Club - AI'=>'46897',
            'Azul Beach Resort Negril by Karisma - AI'=>'46898',
            'Bali Villas & Sports Club - Rentals Only'=>'46899',
            'Blue Whale'=>'46900',
            'Bluegreen Club 36'=>'46901',
            'BreakFree Alexandra Beach'=>'46902',
            'Classic @ Alpha Sovereign Hotel'=>'46903',
            'Club Regina Los Cabos'=>'46904',
            'Royal Aloha Branson - Eagles Nest Resort'=>'1836',
            'El Dorado Casitas Royale by Karisma, a Gourmet AIl Inclusive' => '46905',
            'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive'=>'46905',
            'El Dorado Maroma by Karisma a Gourmet AI'=>'46906',
            'El Dorado Royale by Karisma a Gourmet AI'=>'46907',
            'Fiesta Ameri. Vac Club At Cabo Del Sol'=>'46908',
            'Fort Brown Condo Shares'=>'46909',
            'Four Seasons Residence Club Scottsdale at Troon North'=>'2457',
            'Generations Riviera Maya by Karisma a Gourmet AI'=>'46910',
            'SKIP'=>'SKIP',
            'Grand Palladium Jamaica Resort & Spa - AI'=>'46911',
            'Grand Palladium Vallarta Resort & Spa - AI'=>'46912',
            'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive'=>'46913',
            'High Sierra Condominiums'=>'46914',
            'Kiltannon Home Farm'=>'46915',
            'Knocktopher Abbey'=>'46916',
            'Laguna Suites Golf and Spa - AI'=>'46917',
            'Maison St. Charles - Rentals Only'=>'46918',
            'Makai Club Resort Condos'=>'1787',
            'Marina Del Rey Beach Club - No Longer Accepting'=>'46919',
            'Mantra Aqueous on Port'=>'46920',
            'Maui Sunset'=>'1758',
            'Mayan Palace Mazatlan by Grupo Vidanta'=>'3652',
            'Ocean Gate Resort'=>'46921',
            'Ocean Spa Hotel - AI'=>'46922',
            'Paradise'=>'46923',
            'Royal Holiday - Park Royal Club Cala'=>'338',
            'Park Royal Los Cabos - RHC'=>'46924',
            'Peacock Suites Resort'=>'46925',
            'Pounamu Apartments - Rental'=>'46926',
            'Presidential Suites by LHVC - Punta Cana NON - AI'=>'46927',
            'RHC - Park Royal - Los Tules'=>'46928',
            'Royal Regency By Diamond Resorts'=>'479',
            'Royal Sunset - AI'=>'46929',
            'Secrets Puerto Los Cabos Golf & Spa Resort - AI'=>'46930',
            'Secrets Wild Orchid Montego Bay - AI'=>'46931',
            'Solare Bahia Mar - Rentals Only'=>'46932',
            'Royal Aloha Tahoe'=>'40',
            'The RePlay Residence'=>'46933',
            'The Tropical at LHVC - AI'=>'46934',
            'Williamsburg Plantation Resort'=>'2432',
            'Wolf Run Manor At Treasure Lake'=>'46935',
            'Wyndham Grand Desert - 3 Nights'=>'46936',
            'Royal Garden at Waikiki Resort'=>'1716',
        ];
        $resortMissing = '';
        if(array_key_exists($row->Resort_Name, $resortKeyOne))
        {
            $resortMissing = $resortKeyOne[$row->Resort_Name];
            if($resort == 'SKIP')
            {
                continue;
            }
        }
        if(array_key_exists($row->Resort_Name, $resortKeyTwo))
        {
            $resortMissing = $resortKeyTwo[$row->Resort_Name];
            if($resort == 'SKIP')
            {
                continue;
            }
        }
        if(!empty($resortMissing))
        {
            $sql = $wpdb->prepare("SELECT id, resortID, ResortName FROM wp_resorts WHERE id=%s", $resortMissing);
            $resort = $wpdb->get_row($sql);
            $resortName = $resort->ResortName;
        }
        else
        {
            $resortName = $row->Resort_Name;
            $resortName = str_replace("- VI", "", $resortName);
            $resortName = trim($resortName);
            $sql = $wpdb->prepare("SELECT id, resortID FROM wp_resorts WHERE ResortName=%s", $resortName);
            $resort = $wpdb->get_row($sql);
        }

        if(empty($resort))
        {
            $sql = $wpdb->prepare("SELECT missing_resort_id FROM import_credit_future_stay WHERE resort_name=%s", $resortName);
            $resort_ID = $wpdb->get_var($sql);

            $sql = $wpdb->prepare("SELECT id, resortID, ResortName FROM wp_resorts WHERE id=%s", $resort_ID);
            $resort = $wpdb->get_row($sql);
            $resortID = $resort->resortID;
            $resortName = $resort->ResortName;


        }
        else
        {
            $resortID = $resort->id;
            $daeResortID = $resort->resortID;
        }

        if(empty($resort))
        {
            $exception = json_encode($row);
            $wpdb->insert("final_import_exceptions", array('type'=>$tt.' resort', 'data'=>$exception));
            continue;
        }

        $sql = $wpdb->prepare("SELECT user_id FROM wp_GPR_Owner_ID__c WHERE user_id=%s", $row->MemberNumber);
        $user = $wpdb->get_var($sql);

        if(empty($user))
        {
            //let's try to import this owner
            $user = function_GPX_Owner($row->MemberNumber);

            if(empty($user))
            {
                $exception = json_encode($row);
                $wpdb->insert("final_import_exceptions", array('type'=>$tt.' user', 'data'=>$exception));
                continue;
            }
        }
        else
        {
            $userID = $user;

            $sql = $wpdb->prepare("SELECT name FROM wp_partner WHERE user_id=%s", $userID);
            $memberName = $wpdb->get_var($sql);

            if(empty($memberName))
            {
                $fn = get_user_meta($userID,'first_name', true);

                if(empty($fn))
                {
                    $fn = get_user_meta($userID,'FirstName1', true);
                }
                $ln = get_user_meta($userID,'last_name', true);
                if(empty($ln))
                {
                    $ln = get_user_meta($userID,'LastName1', true);
                }
                if(!empty($fn) || !empty($ln))
                {
                    $memberName = $fn." ".$ln;
                }
                else
                {
                    $exception = json_encode($row);
                    $wpdb->insert("final_import_exceptions", array('type'=>$tt.' member name', 'data'=>$exception));
                    continue;
                }
            }
        }

        $unitType = $row->Unit_Type;
        $sql = $wpdb->prepare("SELECT record_id FROM wp_unit_type WHERE resort_id=%s AND name=%s", [$resortID,$unitType]);
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



        $wp_room = [
            'record_id'=>$row->weekId,
            'active_specific_date' => date("Y-m-d 00:00:00", strtotime($row->Rental_Opening_Date)),
            'check_in_date' => date('Y-m-d 00:00:00', strtotime($row->Check_In_Date)),
            'check_out_date' => date('Y-m-d 00:00:00', strtotime($row->Check_In_Date.' +7 days')),
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
            'points' => NULL,
            'note' => '',
            'given_to_partner_id' => NULL,
            'import_id' => '0',
            'active_type' => '0',
            'active_week_month' => '0',
            'create_by' => '5',
            'archived' => '0',
        ];

        $sql = $wpdb->prepare("SELECT record_id FROM wp_room WHERE record_id=%s", $row->weekId);
        $week = $wpdb->get_row($sql);
        if(!empty($week))
        {
            $wpdb->update('wp_room', $wp_room, array('record_id'=>$week));
        }
        else
        {
            $wpdb->insert('wp_room', $wp_room);
        }

        $cpo = "TAKEN";
        if($row->CPO == 'No')
        {
            $cpo = "NOT TAKEN";
        }

        $data = [
            "MemberNumber"=>$row->MemberNumber,
            "MemberName"=>$memberName,
            "GuestName"=>$row->GuestName,
            "Adults"=>$row->Adults,
            "Children"=>$row->Children,
            "UpgradeFee"=>$row->actupgradeFee,
            "CPO"=>$cpo,
            "CPOFee"=>$row->actcpoFee,
            "Paid"=>$row->Paid,
            "Balance"=>"0",
            "ResortID"=>$daeResortID,
            "ResortName"=>$row->Resort_Name,
            "room_type"=>$row->Unit_Type,
            "WeekType"=>$row->WeekTransactionType,
            "sleeps"=>$sleeps,
            "bedrooms"=>$beds,
            "Size"=>$row->Unit_Type,
            "noNights"=>"7",
            "checkIn"=>date('Y-m-d', strtotime($row->Check_In_Date)),
            "processedBy"=>5,
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
            'cartID' => $userID.'-'.$row->weekId,
            'sessionID' => '',
            'userID' => $userID,
            'resortID' => $daeResortID,
            'weekId' => $row->weekId,
            'check_in_date' => date('Y-m-d', strtotime($row->Check_In_Date)),
            'datetime' => date('Y-m-d', strtotime($row->transaction_date)),
            'depositID' => NULL,
            'paymentGatewayID' => '',
            'transactionRequestId' => NULL,
            'transactionData' => '',
            'sfid' => '0',
            'sfData' => '',
            'data' => json_encode($data),
        ];

        $transactionID = '';
        $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s AND userID=%s", [$row->weekId,$userID]);
        $et = $wpdb->get_var($sql);
        if(!empty($et))
        {
            $wpdb->update('wp_gpxTransactions', $wp_gpxTransactions, array('id'=>$et));
            $transactionID = $et;
        }
        else
        {
            $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s", $row->weekId);
            $enut = $wpdb->get_var($sql);
            if(empty($enut))
            {
                $wpdb->insert('wp_gpxTransactions', $wp_gpxTransactions);
                $transactionID = $wpdb->insert_id;
            }
            else
            {
                $exception = json_encode($row);
                $wpdb->insert("final_import_exceptions", array('type'=>$tt.' duplicate week transaction not cancelled', 'data'=>$exception));
                continue;
            }
        }
        if(isset($transactionID) && !empty($transactionID))
        {
            $d = $gpx->transactiontosf($transactionID);
        }
    }
    $sql = "SELECT COUNT(id) as cnt FROM ".gpx_esc_table($table)." WHERE imported=0";
    $remain = $wpdb->get_var($sql);
    if($remain > 0 && empty($id))
    {

        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('remaining'=>$remain));
}






/**
 *
 *
 *
 *
 */
function gpx_import_transactions($table='transactions_import_two', $id='', $resort='')
{
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $table='transactions_import';
    $tt = 'transaction1';
    if($_GET['table'] == 'two')
    {
        $table = 'transactions_import_two';
        $tt = 'transaction2';
    }
    if($_GET['table'] == 'owner')
    {
        $table = 'transactions_import_owner';
        $tt = 'transactionOwner';
    }

    if($_GET['table'] == 'two')
    {
        $table = 'transactions_import_two';
        $tt = 'transaction2';
    }
    if($_GET['table'] == 'owner')
    {
        $table = 'transactions_import_owner';
        $tt = 'transactionOwner';
    }
    if($_GET['id'])
    {
        $id = $_GET['id'];
    }

    $where = 'imported=0';
    if(!empty($id))
    {
        $where = $wpdb->prepare('id=%d',$id);
    }

    $sql = "SELECT * FROM ".gpx_esc_table($table)." WHERE ".$where." ORDER BY RAND() LIMIT 40";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {

        $wpdb->update($table, array('imported'=>2), array('id'=>$row->id));

        if($row->GuestName == '#N/A')
        {
            $exception = json_encode($row);
            $wpdb->insert("final_import_exceptions", array('type'=>$tt.' guest formula error', 'data'=>$exception));
            continue;
        }

        $resortKeyOne = [
            'Butterfield Park - VI'=>'2440',
            'Grand Palladium White Sand - AI'=>'46895',
            'Grand Sirenis Riviera Maya Resort - AI'=>'46896',
            'High Point World Resort - RHC'=>'1549',
            'Los Abrigados Resort & Spa'=>'2467',
            'Makai Club Cottages'=>'1786',
            'Palm Canyon Resort & Spa'=>'1397',
            'Sunset Marina Resort & Yacht Club - AI'=>'46897',
            'Azul Beach Resort Negril by Karisma - AI'=>'46898',
            'Bali Villas & Sports Club - Rentals Only'=>'46899',
            'Blue Whale'=>'46900',
            'Bluegreen Club 36'=>'46901',
            'BreakFree Alexandra Beach'=>'46902',
            'Classic @ Alpha Sovereign Hotel'=>'46903',
            'Club Regina Los Cabos'=>'46904',
            'Eagles Nest Resort - VI'=>'1836',
            'El Dorado Casitas Royale by Karisma'=>'46905',
            'El Dorado Casitas Royale by Karisma, a Gourmet AIl Inclusive'=>'46905',
            'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive'=>'46905',
            'El Dorado Maroma by Karisma, a Gourmet AI'=>'46906',
            'El Dorado Maroma by Karisma a Gourmet AI'=>'46906',
            'El Dorado Royale by Karisma a Gourmet AI'=>'46907',
            'El Dorado Royale by Karisma, a Gourmet AI'=>'46907',
            'Fiesta Ameri. Vac Club At Cabo Del Sol'=>'46908',
            'Fort Brown Condo Shares'=>'46909',
            'Four Seasons Residence Club Scottsdale@Troon North'=>'2457',
            'Generations Riviera Maya by Karisma a Gourmet AI'=>'46910',
            'GPX Cruise Exchange'=>'SKIP',
            'Grand Palladium Jamaica Resort & Spa - AI'=>'46911',
            'Grand Palladium Vallarta Resort & Spa - AI'=>'46912',
            'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive'=>'46913',
            'High Sierra Condominiums'=>'46914',
            'Kiltannon Home Farm'=>'46915',
            'Knocktopher Abbey'=>'46916',
            'Knocktopher Abbey (Shadowed)'=>'46916',
            'Laguna Suites Golf and Spa - AI'=>'46917',
            'Maison St. Charles - Rentals Only'=>'46918',
            'Makai Club Resort'=>'1787',
            'Marina Del Rey Beach Club - No Longer Accepting'=>'46919',
            'Mantra Aqueous on Port'=>'46920',
            'Maui Sunset - Rentals Only'=>'1758',
            'Mayan Palace Mazatlan'=>'3652',
            'Ocean Gate Resort'=>'46921',
            'Ocean Spa Hotel - AI'=>'46922',
            'Paradise'=>'46923',
            'Park Royal Homestay Club Cala'=>'338',
            'Park Royal Los Cabos - RHC'=>'46924',
            'Peacock Suites Resort'=>'46925',
            'Pounamu Apartments - Rental'=>'46926',
            'Presidential Suites by LHVC - Punta Cana NON - AI'=>'46927',
            'RHC - Park Royal - Los Tules'=>'46928',
            'Royal Regency Paris (Shadowed)'=>'479',
            'Royal Sunset - AI'=>'46929',
            'Secrets Puerto Los Cabos Golf & Spa Resort - AI'=>'46930',
            'Secrets Wild Orchid Montego Bay - AI'=>'46931',
            'Solare Bahia Mar - Rentals Only'=>'46932',
            'Tahoe Trail - VI'=>'40',
            'The RePlay Residence'=>'46933',
            'The Tropical at LHVC - AI'=>'46934',
            'Vacation Village at Williamsburg'=>'2432',
            'Wolf Run Manor At Treasure Lake'=>'46935',
            'Wyndham Grand Desert - 3 Nights'=>'46936',
            'Wyndham Royal Garden at Waikiki - Rental Only'=>'1716',
        ];

        $resortKeyTwo = [
            'Royal Aloha Chandler - Butterfield Park'=>'2440',
            'Grand Palladium White Sand - AI'=>'46895',
            'Grand Sirenis Riviera Maya Resort - AI'=>'46896',
            'High Point World Resort'=>'1549',
            'Los Abrigados Resort and Spa'=>'2467',
            'Makai Club Resort Cottages'=>'1786',
            'Palm Canyon Resort and Spa'=>'1397',
            'Sunset Marina Resort & Yacht Club - AI'=>'46897',
            'Azul Beach Resort Negril by Karisma - AI'=>'46898',
            'Bali Villas & Sports Club - Rentals Only'=>'46899',
            'Blue Whale'=>'46900',
            'Bluegreen Club 36'=>'46901',
            'BreakFree Alexandra Beach'=>'46902',
            'Classic @ Alpha Sovereign Hotel'=>'46903',
            'Club Regina Los Cabos'=>'46904',
            'Royal Aloha Branson - Eagles Nest Resort'=>'1836',
            'El Dorado Casitas Royale by Karisma, a Gourmet AIl Inclusive' => '46905',
            'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive'=>'46905',
            'El Dorado Maroma by Karisma a Gourmet AI'=>'46906',
            'El Dorado Royale by Karisma a Gourmet AI'=>'46907',
            'Fiesta Ameri. Vac Club At Cabo Del Sol'=>'46908',
            'Fort Brown Condo Shares'=>'46909',
            'Four Seasons Residence Club Scottsdale at Troon North'=>'2457',
            'Generations Riviera Maya by Karisma a Gourmet AI'=>'46910',
            'SKIP'=>'SKIP',
            'Grand Palladium Jamaica Resort & Spa - AI'=>'46911',
            'Grand Palladium Vallarta Resort & Spa - AI'=>'46912',
            'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive'=>'46913',
            'High Sierra Condominiums'=>'46914',
            'Kiltannon Home Farm'=>'46915',
            'Knocktopher Abbey'=>'46916',
            'Laguna Suites Golf and Spa - AI'=>'46917',
            'Maison St. Charles - Rentals Only'=>'46918',
            'Makai Club Resort Condos'=>'1787',
            'Marina Del Rey Beach Club - No Longer Accepting'=>'46919',
            'Mantra Aqueous on Port'=>'46920',
            'Maui Sunset'=>'1758',
            'Mayan Palace Mazatlan by Grupo Vidanta'=>'3652',
            'Ocean Gate Resort'=>'46921',
            'Ocean Spa Hotel - AI'=>'46922',
            'Paradise'=>'46923',
            'Royal Holiday - Park Royal Club Cala'=>'338',
            'Park Royal Los Cabos - RHC'=>'46924',
            'Peacock Suites Resort'=>'46925',
            'Pounamu Apartments - Rental'=>'46926',
            'Presidential Suites by LHVC - Punta Cana NON - AI'=>'46927',
            'RHC - Park Royal - Los Tules'=>'46928',
            'Royal Regency By Diamond Resorts'=>'479',
            'Royal Sunset - AI'=>'46929',
            'Secrets Puerto Los Cabos Golf & Spa Resort - AI'=>'46930',
            'Secrets Wild Orchid Montego Bay - AI'=>'46931',
            'Solare Bahia Mar - Rentals Only'=>'46932',
            'Royal Aloha Tahoe'=>'40',
            'The RePlay Residence'=>'46933',
            'The Tropical at LHVC - AI'=>'46934',
            'Williamsburg Plantation Resort'=>'2432',
            'Wolf Run Manor At Treasure Lake'=>'46935',
            'Wyndham Grand Desert - 3 Nights'=>'46936',
            'Royal Garden at Waikiki Resort'=>'1716',
        ];
        $resortMissing = '';
        if(array_key_exists($row->Resort_Name, $resortKeyOne))
        {
            $resortMissing = $resortKeyOne[$row->Resort_Name];
            if($resort == 'SKIP')
            {
                continue;
            }
        }
        if(array_key_exists($row->Resort_Name, $resortKeyTwo))
        {
            $resortMissing = $resortKeyTwo[$row->Resort_Name];
            if($resort == 'SKIP')
            {
                continue;
            }
        }
        if(!empty($resortMissing))
        {
            $sql = $wpdb->prepare("SELECT id, resortID, ResortName FROM wp_resorts WHERE id=%s", $resortMissing);
            $resort = $wpdb->get_row($sql);
            $resortName = $resort->ResortName;
        }
        else
        {
            $resortName = $row->Resort_Name;
            $resortName = str_replace("- VI", "", $resortName);
            $resortName = trim($resortName);
            $sql = $wpdb->prepare("SELECT id, resortID FROM wp_resorts WHERE ResortName=%s", $resortName);
            $resort = $wpdb->get_row($sql);
        }

        if(empty($resort))
        {
            $sql = $wpdb->prepare("SELECT missing_resort_id FROM import_credit_future_stay WHERE resort_name=%s", $resortName);
            $resort_ID = $wpdb->get_var($sql);

            $sql = $wpdb->prepare("SELECT id, resortID, ResortName FROM wp_resorts WHERE id=%s", $resort_ID);
            $resort = $wpdb->get_row($sql);
            $resortID = $resort->resortID;
            $resortName = $resort->ResortName;


        }
        else
        {
            $resortID = $resort->id;
            $daeResortID = $resort->resortID;
        }

        if(empty($resort))
        {
            $exception = json_encode($row);
            $wpdb->insert("final_import_exceptions", array('type'=>$tt.' resort', 'data'=>$exception));
            continue;
        }

        $sql = $wpdb->prepare("SELECT user_id FROM wp_GPR_Owner_ID__c WHERE user_id=%s", $row->MemberNumber);
        $user = $wpdb->get_var($sql);

        if(empty($user))
        {
            //let's try to import this owner
            $user = function_GPX_Owner($row->MemberNumber);

            if(empty($user))
            {
                $exception = json_encode($row);
                $wpdb->insert("final_import_exceptions", array('type'=>$tt.' user', 'data'=>$exception));
                continue;
            }
        }
        else
        {
            $userID = $user;

            $sql = $wpdb->prepare("SELECT name FROM wp_partner WHERE user_id=%s", $userID);
            $memberName = $wpdb->get_var($sql);

            if(empty($memberName))
            {
                $fn = get_user_meta($userID,'first_name', true);

                if(empty($fn))
                {
                    $fn = get_user_meta($userID,'FirstName1', true);
                }
                $ln = get_user_meta($userID,'last_name', true);
                if(empty($ln))
                {
                    $ln = get_user_meta($userID,'LastName1', true);
                }
                if(!empty($fn) || !empty($ln))
                {
                    $memberName = $fn." ".$ln;
                }
                else
                {
                    $exception = json_encode($row);
                    $wpdb->insert("final_import_exceptions", array('type'=>$tt.' member name', 'data'=>$exception));
                    continue;
                }
            }
        }

        $unitType = $row->Unit_Type;
        $sql = $wpdb->prepare("SELECT record_id FROM wp_unit_type WHERE resort_id=%s AND name=%s", [$resortID,$unitType]);
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



        $wp_room = [
            'record_id'=>$row->weekId,
            'active_specific_date' => date("Y-m-d 00:00:00", strtotime($row->Rental_Opening_Date)),
            'check_in_date' => date('Y-m-d 00:00:00', strtotime($row->Check_In_Date)),
            'check_out_date' => date('Y-m-d 00:00:00', strtotime($row->Check_In_Date.' +7 days')),
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
            'points' => NULL,
            'note' => '',
            'given_to_partner_id' => NULL,
            'import_id' => '0',
            'active_type' => '0',
            'active_week_month' => '0',
            'create_by' => '5',
            'archived' => '0',
        ];

        $sql = $wpdb->prepare("SELECT record_id FROM wp_room WHERE record_id=%s", $row->weekId);
        $week = $wpdb->get_row($sql);
        if(!empty($week))
        {
            $wpdb->update('wp_room', $wp_room, array('record_id'=>$week));
        }
        else
        {
            $wpdb->insert('wp_room', $wp_room);
        }

        $cpo = "TAKEN";
        if($row->CPO == 'No')
        {
            $cpo = "NOT TAKEN";
        }

        $data = [
            "MemberNumber"=>$row->MemberNumber,
            "MemberName"=>$memberName,
            "GuestName"=>$row->GuestName,
            "Adults"=>$row->Adults,
            "Children"=>$row->Children,
            "UpgradeFee"=>$row->actupgradeFee,
            "CPO"=>$cpo,
            "CPOFee"=>$row->actcpoFee,
            "Paid"=>$row->Paid,
            "Balance"=>"0",
            "ResortID"=>$daeResortID,
            "ResortName"=>$row->Resort_Name,
            "room_type"=>$row->Unit_Type,
            "WeekType"=>$row->WeekTransactionType,
            "sleeps"=>$sleeps,
            "bedrooms"=>$beds,
            "Size"=>$row->Unit_Type,
            "noNights"=>"7",
            "checkIn"=>date('Y-m-d', strtotime($row->Check_In_Date)),
            "processedBy"=>5,
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
            'cartID' => $userID.'-'.$row->weekId,
            'sessionID' => '',
            'userID' => $userID,
            'resortID' => $daeResortID,
            'weekId' => $row->weekId,
            'check_in_date' => date('Y-m-d', strtotime($row->Check_In_Date)),
            'datetime' => date('Y-m-d', strtotime($row->transaction_date)),
            'depositID' => NULL,
            'paymentGatewayID' => '',
            'transactionRequestId' => NULL,
            'transactionData' => '',
            'sfid' => '0',
            'sfData' => '',
            'data' => json_encode($data),
        ];

        $transactionID = '';
        $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s AND userID=%s", [$row->weekId,$userID]);
        $et = $wpdb->get_var($sql);
        if(!empty($et))
        {
            $wpdb->update('wp_gpxTransactions', $wp_gpxTransactions, array('id'=>$et));
            $transactionID = $et;
        }
        else
        {
            $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s", $row->weekId);
            $enut = $wpdb->get_var($sql);
            if(empty($enut) || isset($_GET['force_new_transaction']))
            {
                $wpdb->insert('wp_gpxTransactions', $wp_gpxTransactions);
                $transactionID = $wpdb->insert_id;
            }
            else
            {
                $exception = json_encode($row);
                $wpdb->insert("final_import_exceptions", array('type'=>$tt.' duplicate week transaction not cancelled', 'data'=>$exception));
                continue;
            }
        }
        if(isset($transactionID) && !empty($transactionID))
        {
            $d = $gpx->transactiontosf($transactionID);
        }
    }
    $sql = "SELECT COUNT(id) as cnt FROM ".gpx_esc_table($table)." WHERE imported=0";
    $remain = $wpdb->get_var($sql);
    if($remain > 0 && empty($id))
    {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('remaining'=>$remain));
}
add_action('wp_ajax_gpx_import_transactions', 'gpx_import_transactions');


/**
 *
 *
 *
 *
 */
function gpx_import_owner_credits()
{
    global $wpdb;

    $sql = "SELECT count(id) as cnt FROM import_pwner_deposits WHERE imported=0 LIMIT 1";

    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {

        $import_pwner_deposits = array(
            array('ID' => '10384447',
                'Member_Name' => '464536',
                'credit_amount' => '1',
                'Credit_expiration date' => '1/1/2020',
                'resort_name' => 'Hanalei Bay Resort',
                'Deposit_year' => '2018',
                'unit_type' => '1b/4',
                'check_in_date' => '1/1/2018',
                'credit_used' => '0',
                'status' => 'Approved',
                'imported' => '0')
        );
    }

    $import_pwner_deposits = array(
        array('ID' => '10384447','Member_Name' => '464536','credit_amount' => '1','Credit_expiration date' => '1/1/2020','resort_name' => 'Hanalei Bay Resort','Deposit_year' => '2018','unit_type' => '1b/4','check_in_date' => '1/1/2018','credit_used' => '0','status' => 'Approved','imported' => '0')
    );


    $sql = "SELECT count(id) as cnt FROM import_pwner_deposits WHERE imported=0";
    $remain = $wpdb->get_var($sql);

    if($remain > 0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('remaining'=>$remain));
}
add_action('wp_ajax_gpx_import_owner_credits', 'gpx_import_owner_credits');





/**
 *
 *
 *
 *
 */
function gpx_owner_monetary_credits()
{
    global $wpdb;

    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $sql = "SELECT new_id, old_id FROM vest_rework_users WHERE old_id IN (SELECT ownerID FROM wp_gpxOwnerCreditCoupon_owner)";
    $imports = $wpdb->get_results($sql);

    foreach($imports as $import)
    {
        $wpdb->update('wp_gpxOwnerCreditCoupon_owner', array('ownerID'=>$import->new_id), array('ownerID'=>$import->old_id));

    }

    $sql = "SELECT count(id) as cnt FROM owner_monetary_credits WHERE imported=1 LIMIT 100";
    $remain = $wpdb->get_var($sql);

    if($remain > 0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('remaining'=>$remain));
}
add_action('wp_ajax_gpx_owner_monetary_credits', 'gpx_owner_monetary_credits');





/**
 *
 *
 *
 *
 */
function reimport_exceptions()
{
    global $wpdb;
    $reimport_exceptions = array(
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
        'tgf' => 'transaction guest formula error'
    );

    $type = $reimport_exceptions[$_GET['type']];

    $upload_dir = wp_upload_dir();
    // @TODO hardcoded path is likely broken on new server
    $fileLoc = '/var/www/reports/'.$type.'.csv';
    $file = fopen($fileLoc, 'w');

    $sql = $wpdb->prepare("SELECT type, data FROM reimport_exceptions WHERE type=%s", $type);
    $results = $wpdb->get_results($sql, ARRAY_A);
    foreach($results as $r)
    {
        $rd = json_decode($r['data'], true);
        $types[$r['type']][] = str_replace(",", "", $rd);
    }

    foreach($types as $tk=>$tv)
    {
        foreach($tv as $v)
        {
            if(!isset($th[$tk]))
            {
                $heads = array_keys($v);
                $th[$tk] = implode(',',array_keys($v));
            }

            $ov[] = $v;

        }
        $ov[] = $tvv;
    }
    $list = array();
    $list[] = $th[$type];

    $i = 1;
    foreach($ov as $value)
    {
        foreach($heads as $head)
        {
            $ordered[$i][] = $value[$head];
        }
        $list[$i] = implode(',', $ordered[$i]);
        $i++;
    }
    foreach($list as $line)
    {
        fputcsv($file,explode(",", $line));
    }
    fclose($file);

    if (file_exists($fileLoc)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($fileLoc).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fileLoc));
        readfile($fileLoc);
        exit;
    }
}
add_action('wp_ajax_gpx_reimport_exceptions', 'reimport_exceptions');



/**
 *
 *
 *
 *
 */
function hook_credit_import($atts=array())
{

    global $wpdb;

    if(!empty($atts))
    {
        $atts = shortcode_atts(
            array(
                'gpxcreditid' => '',
            ), $atts );
        extract($atts);
    }

    // if creditid provided use it...
    if(isset($_GET['creditid']))
    {
        $gpxcreditid = $_GET['creditid'];
    }

    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $sf = Salesforce::getInstance();

    $queryDays = 3;

    //query alt use
    $selects = [
        'Id',
        'Name',
        'Status__c',
        'GPX_Deposit__c',
    ];

    $d = date('Y-m-d', strtotime("-1 hour"));
    $t = date('H:i:s', strtotime("-1 hour"));

    $query =  "select ".implode(", ", $selects)." from GPX_Transaction__c WHERE SystemModStamp > ".$d.'T'.$t."Z AND Status__c='Denied'";
    $results = $sf->query($query);

    if(!empty($results))
    {
        foreach($results as $result)
        {

            $value = $result->fields;


            $sql = $wpdb->prepare("SELECT cancelledData FROM wp_gpxTransactions WHERE id=%s", $value->Name);
            $cd = $wpdb->get_var($sql);

            if(!empty($cd))
            {
                continue;
            }
            $cupdate = json_decode($cd, true);

            $cupdate[strtotime('NOW')] = [
                'userid'=> 'system',
                'name'=> 'system',
                'date'=> date('Y-m-d H:i:s'),
                'refunded'=>'',
                'coupon' => '',
                'action'=>'refund',
                'amount'=>'',
                'by'=>'system',
            ];

            $transactionUpdate = [
                'cancelled'=>1,
                'cancelledDate'=>date('Y-m-d'),
                'cancelledData'=>json_encode($cupdate),
            ];
            $wpdb->update('wp_gpxTransactions', $transactionUpdate, array('id'=>$value->Name));

            $sql = $wpdb->prepare("SELECT id, credit_used, credit_action FROM wp_credit WHERE record_id=%s", $value->GPX_Deposit__c);
            $row = $wpdb->get_row($sql);

            $newCreditUsed = $row->credit_used - 1;
            $creditID = $row->id;

            $creditModData = [
                'type'=>'Deposit Denied',
                'oldAmount'=>$row->credit_used,
                'newAmount'=>$newCreditUsed,
                'date'=>date('Y-m-d'),
            ];
            $creditMod = [
                'credit_id'=>$value->GPX_Deposit__c,
                'recorded_by'=>'9999999',
                'data'=>json_encode($creditModData),
            ];

            $wpdb->insert('wp_credit_modification', $creditMod);

            $creditUpdate = [
                'credit_used'=>$newCreditUsed,
                'status' => 'Available',
                'modification_id'=>$wpdb->insert_id,
                'modified_date'=>date('Y-m-d'),
                'credit_action'=>'',
            ];

            $credit['credit_used'] = $newCreditUsed;
            $credit['status'] = 'Available';
            $credit['modification_id'] = $wpdb->insert_id;
            $credit['modified_date'] = date('Y-m-d');
            $credit['credit_action'] = '';

            $wpdb->update('wp_credit', $creditUpdate, array('id'=>$creditID));

            $sfCreditData['GPX_Deposit_ID__c'] = $creditID;
            $sfCreditData['Credits_Used__c'] = $newCreditUsed;
            $sfCreditData['Credits_Used__c'] = $newCreditUsed;
            $sfCreditData['Deposit_Status__c'] = 'Approved';

            $sfWeekAdd = '';
            $sfAdd = '';
            $sfType = 'GPX_Deposit__c';
            $sfObject = 'GPX_Deposit_ID__c';


            $sfFields = [];
            $sfFields[0] = new SObject();
            $sfFields[0]->fields = $sfCreditData;
            $sfFields[0]->type = $sfType;

            $sfDepositAdjust = $sf->gpxUpsert($sfObject, $sfFields);
        }
    }

    $selects = [
        'Id',
        'Name',
        'Account_Name__c',
        'Check_In_Date__c',
        'Credit_Extension_Date__c',
        'Credits_Issued__c',
        'Credits_Used__c',
        'Deposit_Date__c',
        'Deposit_Year__c',
        'Expiration_Date__c',
        'GPX_Deposit_ID__c',
        'GPX_Member__c',
        'Resort_Name__c',
        'Resort_Unit_Week__c',
        'Deposit_Status__c',
        'Unit_Type__c',
        'Coupon__c',
        'Delete_this_Record__c',
    ];


    $query =  "select ".implode(", ", $selects)." from GPX_Deposit__c WHERE SystemModStamp > ".$d.'T'.$t."Z";
    if(isset($gpxcreditid))
    {
        $query .= " AND GPX_Deposit_ID__c='".$gpxcreditid."'";
    }

    $results = $sf->query($query);


    foreach ($results as $result)
    {

        $value = $result->fields;

        if($value->Delete_this_Record__c == 'true')
        {
            $wpdb->delete('wp_credit', array('id'=>$value->GPX_Deposit_ID__c));
        }

        $credit = [
            'record_id'=>$result->Id ,
            'sf_name'=>$result->Name,
            'credit_amount'=>$value->Credits_Issued__c,
            'credit_expiration_date'=>$value->Expiration_Date__c,
            'resort_name'=>stripslashes(str_replace("&", "&amp;", $value->Resort_Name__c)),
            'deposit_year'=> $value->Deposit_Year__c,
            'unit_type'=> $value->Unit_Type__c,
            'check_in_date'=> $value->Check_In_Date__c,
            'extension_date'=> $value->Credit_Extension_Date__c,
            'coupon'=> $value->Coupon__c,
            'status'=> $value->Deposit_Status__c,
        ];

        if(!empty($value->Reservation__c))
        {
            $credit['reservation_number'] = $value->Reservation__c;
        }

        $sql = $wpdb->prepare("SELECT status, owner_id, credit_used, credit_amount FROM wp_credit WHERE id=%s", $value->GPX_Deposit_ID__c);
        $row = $wpdb->get_row($sql);

        $ownerID = $row->owner_id;

        $nv = [
            '-1'
        ];
        $nv[] = $row->credit_used;
        $newCreditUsed = array_sum($nv);

        if($row->credit_used != $value->Credits_Used__c || $row->credit_amount != $value->Credits_Issued__c)
        {
            $wpdb->update('wp_credit', array('credit_amount'=>$value->Credits_Issued__c, 'credit_used'=>$value->Credits_Used__c), array('id'=>$value->GPX_Deposit_ID__c));
        }

        if($row->status != $value->Deposit_Status__c)
        {
            //add the last year banked
            if($value->Deposit_Status__c == 'Approved')
            {
                $oSql = $wpdb->prepare("SELECT id FROM wp_owner_interval WHERE userID = %s AND  unitweek = %s AND  (Year_Last_Banked__c IS NULL OR Year_Last_Banked__c < %s)", [$ownerID, $value->Resort_Unit_Week__c, $credit['deposit_year']]);
                $oRow = $wpdb->get_var($oSql);

                if(!empty($oRow))
                {
                    $wpdb->update('wp_owner_interval', array('Year_Last_Banked__c'=>$credit['deposit_year']), array('id'=>$oRow));
                }
            }
            //get this transaction
            // @TODO this query is never run, why is it here?
            $sql = $wpdb->prepare("SELECT a.id, a.weekId, a.cancelled, a.userID, a.data, b.data as excd FROM wp_gpxTransactions a
                        INNER JOIN wp_gpxDepostOnExchange b ON a.depositID=b.id
                        WHERE a.userID=%s", $row->owner_id);

            $sql = $wpdb->prepare("SELECT a.id, a.transactionType, a.weekId, a.cancelled, a.cancelledData, a.userID, a.data, b.data as excd FROM wp_gpxTransactions a
                        INNER JOIN wp_credit c ON c.id=a.depositID
						INNER JOIN wp_gpxDepostOnExchange b ON c.id=b.creditID
						WHERE a.depositID=%s", $value->GPX_Deposit_ID__c);

            $trans = $wpdb->get_results($sql);

            if(empty($trans))
            {
                //this id comes from the wp_gpxDepostOnExchange table
                $sql = $wpdb->prepare("SELECT a.id, a.transactionType, a.weekId, a.cancelled, a.cancelledData, a.userID, a.data, b.data as excd FROM wp_gpxTransactions a
    						INNER JOIN wp_gpxDepostOnExchange b ON b.id=a.depositID
    						WHERE b.creditID=%s", $value->GPX_Deposit_ID__c);

                $trans = $wpdb->get_results($sql);
            }


            foreach($trans as $tk=>$tv)
            {
                $sfData = [];
                $sfWeekData = [];

                $dexp = json_decode($tv->excd);

                if($dexp->GPX_Deposit_ID__c == $value->GPX_Deposit_ID__c)
                {
                    if($value->Deposit_Status__c == 'Approved')
                    {
                        //update week and transaction
                        $sfWeekData['GpxWeekRefId__c'] = $tv->weekId;
                        $sfWeekData['Status__c'] = 'Booked';

                        $sfFields = [];
                        $sfFields[0] = new SObject();
                        $sfFields[0]->fields = $sfWeekData;
                        $sfFields[0]->type = 'GPX_Week__c';

                        $sfObject = 'GpxWeekRefId__c';

                        $sfWeekAdd = $sf->gpxUpsert($sfObject, $sfFields);


                        $sfData['GPXTransaction__c'] = $tv->id;

                        if($tv->transactionType == 'credit_transfer')
                        {
                            $sfData['Status__c'] = 'Approved';
                        }

                        $sfData['Reservation_Status__c'] = 'Confirmed';

                        $sfType = 'GPX_Transaction__c';
                        $sfObject = 'GPXTransaction__c';
                        $sfFields = [];
                        $sfFields[0] = new SObject();
                        $sfFields[0]->fields = $sfData;
                        $sfFields[0]->type = $sfType;

                        $sfObject = 'GPXTransaction__c';

                        $sfAdd = $sf->gpxUpsert($sfObject, $sfFields);

                        $wpdb->update('wp_gpxTransactioons', array('cancelled'=>'0'), array('id'=>$tv->id));

                    }
                    elseif($tv->cancelled != '1')
                    {
                        if($value->Deposit_Status__c == 'Denied')
                        {

                            $jsonData = json_decode($tv->data);
                            $amount = $jsonData->Paid;


                            //create the coupon
                            if($tv->transactionType != 'credit_transfer')
                            {
                                //does this slug exist?
                                $slug = $tv->weekId.$tv->userID;
                                do {
                                    $sql = $wpdb->prepare("SELECT id FROM wp_gpxOwnerCreditCoupon WHERE couponcode=%s", $slug);
                                    $exists = $wpdb->get_var($sql);
                                    if($exists) $slug = $tv->weekId.$tv->userID.rand(1, 1000);
                                } while($exists);

                                $occ = [
                                    'Name'=>$tv->weekId,
                                    'Slug'=>$slug,
                                    'Active'=>1,
                                    'singleuse'=>0,
                                    'amount'=>$amount,
                                    'owners'=>[$tv->userID],
                                    'comments'=>'Denied deposit -- system generated credit',
                                ];
                                $coupon = $gpx->promodeccouponsadd($occ);
                            }

                            $cupdate = json_decode($tv->cancelledData, true);

                            $cupdate[strtotime('NOW')] = [
                                'userid'=> 'system',
                                'name'=> 'system',
                                'date'=> date('Y-m-d H:i:s'),
                                'refunded'=>'',
                                'coupon' => $coupon['coupon'],

                                'action'=>'system',
                                'amount'=>$amount,
                                'by'=>'system',
                            ];

                            $transactionUpdate = [
                                'cancelled'=>1,
                                'cancelledDate'=>date('Y-m-d'),
                                'cancelledData'=>json_encode($cupdate),
                            ];
                            $wpdb->update('wp_gpxTransactions', $transactionUpdate, array('id'=>$tv->id));

                            $sql = $wpdb->prepare("SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId=%s AND cancelled IS NULL", $tv->weekId);
                            $trow = $wpdb->get_var($sql);


                            // TODO refactor - this is silly
                            if($trow > 0)
                            {
                                //nothing to do
                            }
                            else
                            {

                                //we always need to check the "display date" prior to making it active. Only make this active when the sell date is in the future.
                                $sql = $wpdb->prepare("SELECT active_specific_date FROM wp_room WHERE record_id=%d",$tv->weekId);
                                $activeDate = $wpdb->get_var($sql);

                                if(strtotime('NOW') >  strtotime($activeDate))
                                {
                                    $wpdb->update('wp_room', array('active'=>1), array('record_id'=>$tv->weekId));
                                }
                            }


                            $creditModData = [
                                'type'=>'Deposit Denied',
                                'oldAmount'=>$row->credit_used,
                                'newAmount'=>$newCreditUsed,
                                'date'=>date('Y-m-d'),
                            ];
                            $creditMod = [
                                'credit_id'=>$value->GPX_Deposit_ID__c,
                                'recorded_by'=>'9999999',
                                'data'=>json_encode($creditModData),
                            ];

                            $wpdb->insert('wp_credit_modification', $creditMod);

                            $creditUpdate = [
                                'credit_used'=>$newCreditUsed,
                                'modification_id'=>$wpdb->insert_id,
                                'modified_date'=>date('Y-m-d'),
                            ];

                            $credit['credit_used'] = $newCreditUsed;
                            $credit['modification_id'] = $wpdb->insert_id;
                            $credit['modified_date'] = date('Y-m-d');

                            $wpdb->update('wp_credit', $credit, array('id'=>$value->GPX_Deposit_ID__c));

                            //update week and transaction
                            if($tv->transactionType != 'credit_transfer')
                            {
                                $sfWeekData['GpxWeekRefId__c'] = $tv->weekId;
                                $sfWeekData['Status__c'] = 'Available';

                                $sfFields = [];
                                $sfFields[0] = new SObject();
                                $sfFields[0]->fields = $sfWeekData;
                                $sfFields[0]->type = 'GPX_Week__c';

                                $sfObject = 'GpxWeekRefId__c';

                                $sfWeekAdd = $sf->gpxUpsert($sfObject, $sfFields);
                            }

                            $sfData['GPXTransaction__c'] = $tv->id;

                            if($tv->transactionType == 'credit_transfer')
                            {
                                $sfData['Status__c'] = 'Denied';
                            }
                            $sfData['Reservation_Status__c'] = 'Cancelled';
                            $sfData['Cancel_Date__c'] = date('Y-m-d');

                            $sfType = 'GPX_Transaction__c';
                            $sfObject = 'GPXTransaction__c';
                            $sfFields = [];
                            $sfFields[0] = new SObject();
                            $sfFields[0]->fields = $sfData;
                            $sfFields[0]->type = $sfType;

                            $sfObject = 'GPXTransaction__c';

                            $sfAdd = $sf->gpxUpsert($sfObject, $sfFields);

                            $sfCreditData['GPX_Deposit_ID__c'] = $value->GPX_Deposit_ID__c;
                            $sfCreditData['Credits_Used__c'] = $newCreditUsed;

                            $sfObject = 'GPX_Deposit_ID__c';

                            $sfFields = [];
                            $sfFields[0] = new SObject();
                            $sfFields[0]->fields = $sfCreditData;
                            $sfFields[0]->type = 'GPX_Deposit__c';

                            $sfDepositAdjust = $sf->gpxUpsert($sfObject, $sfFields);

                        }
                    }
                }
            }
        }

        foreach($credit as $ck=>$cv)
        {
            if(empty($cv))
            {
                unset($credit[$ck]);
            }
        }

        $wpdb->update('wp_credit', $credit, array('id'=>$value->GPX_Deposit_ID__c));
    }
}
add_action('hook_credit_import', 'hook_credit_import');
add_action('wp_ajax_gpx_credit_import', 'hook_credit_import');
add_shortcode('get_credit', 'hook_credit_import');


/**
 *
 *
 *
 *
 */
function cg_ttsf()
{
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $sql = "SELECT id FROM wp_gpxTransactions WHERE sfData='' AND check_in_date > '2020-11-24' ORDER BY RAND() LIMIT 30";
    $trans = $wpdb->get_results($sql);

    foreach($trans as $r)
    {
        $id = $r->id;

        $d = $gpx->transactiontosf($id);
    }

    $sql = "SELECT COUNT(id) as cnt FROM wp_gpxTransactions WHERE sfData='' AND check_in_date > '2020-11-24'";
    $remain = $wpdb->get_var($sql);

    if($remain > 0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(array('remaining'=>$remain));
}

add_action('wp_ajax_cg_ttsf', 'cg_ttsf');




/**
 *
 *
 *
 *
 */
function tp_claim_week()
{
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $liid = get_current_user_id();

    $agentOrOwner = 'owner';
    if($cid != $liid)
    {
        $agentOrOwner = 'agent';
    }

    $activeUser = get_userdata($liid);
    $tp = $liid;
    if(!empty($_REQUEST['tp']))
    {
        $tp = $_REQUEST['tp'];
    }
    $ids = $_REQUEST['ids'];

    $sql = $wpdb->prepare("SELECT * FROM wp_partner WHERE user_id=%s", $tp);
    $row = $wpdb->get_row($sql);

    if($_POST['type'] == 'hold')
    {
        foreach($ids as $id)
        {

            $sql = $wpdb->prepare("SELECT data from wp_gpxPreHold WHERE user=%s AND weekId=%s AND released='0'", [$tp,$id]);
            $row = $wpdb->get_row($sql);

            if(!empty($row))
            {
                $holdDets = json_decode($row->data, true);
            }
            $holdDets[strtotime('now')] = [
                'action'=>'held',
                'by'=>$activeUser->first_name." ".$activeUser->last_name,
            ];
            $releaseOn = date('Y-m-d', strtotime('+1 year'));
            if(!empty($_REQUEST['date']))
            {
                $releaseOn = date('Y-m-d', strtotime($_REQUEST['date']));
            }
            $data = array(
                'propertyID'=>$id,
                'weekId'=>$id,
                'user'=>$tp,
                'data'=>json_encode($holdDets),
                'released'=>0,
                'release_on'=>$releaseOn,

            );

            $update = $wpdb->update('wp_gpxPreHold', $data, array('user'=>$tp, 'weekId'=>$id));
            if(empty($update))
            {
                $wpdb->insert('wp_gpxPreHold',$data);
                $update = $wpdb->insert_id;
            }


            $wpdb->update('wp_room', array('active'=>'0'), array('record_id'=>$id));
        }
    }
    else
    {
        $_POST['adults'] = 1;
        $_POST['children'] = 0;
        $_POST['user_type'] = 'Agent';
        $_POST['user'] = $tp;
        $_POST['FirstName1'] = $row->name;
        $_POST['Email'] = $row->email;
        $_POST['HomePhone'] = $row->phone;
        //add this to a cart and book the week

        foreach($ids as $id)
        {

            //is this available?
            $sql = $wpdb->prepare("SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId=%s AND cancelled IS NULL", $id);
            $trow = $wpdb->get_var($sql);

            if($trow > 0)
            {
                $wpdb->update('wp_room', array('active'=>'0'), array('record_id'=>$id));
                $output = [
                    'error'=>'This week is no longer available.'
                ];
                continue;
            }


            $_POST['propertyID'] = $id;
            $_POST['weekId'] = $id;
            $_POST['cartID'] = $tp."_".$id;
            $_POST['weekType'] = $_POST['type'];

            $sql = $wpdb->prepare("SELECT data from wp_gpxPreHold WHERE user=%s AND weekId=%s AND released='0'", [$tp,$id]);
            $row = $wpdb->get_row($sql);

            if(!empty($row))
            {
                $holdDets = json_decode($row->data, true);

                $holdDets[strtotime('now')] = [
                    'action'=>'released',
                    'by'=>$activeUser->first_name." ".$activeUser->last_name,
                ];
                $data = array(
                    'propertyID'=>$id,
                    'weekId'=>$id,
                    'user'=>$tp,
                    'data'=>json_encode($holdDets),
                    'released'=>1,
                );

                $update = $wpdb->update('wp_gpxPreHold', $data, array('user'=>$tp, 'weekId'=>$id));
            }

            if($_POST['type'] == 'ExchangeWeek')
            {

                $_POST['taxes'] = array();
                $price = get_option('gpx_exchange_fee');
                $paid = 0;
                $balance = 0;

            }
            else
            {
                $sql = $wpdb->prepare("SELECT a.price, b.taxID FROM wp_room a
                        INNER JOIN wp_resorts b ON a.resort=b.id
                        WHERE a.record_id=%s", $id);

                $prow = $wpdb->get_row($sql);
                $price = $prow->price;

                $taxAmount = 0;
                //add the tax
                $ttType = 'gpx_tax_transaction_bonus';
                if(get_option($ttType) == '1') //set the tax
                {
                    $sql = $wpdb->prepare("SELECT * FROM wp_gpxTaxes WHERE ID=%s", $prow->taxID);
                    $tax = $wpdb->get_row($sql);
                    $taxPercent =  (float)$tax->TaxPercent1 + (float)$tax->TaxPercent2 + (float)$tax->TaxPercent3;
                    $flatTax =   (float)$tax->FlatTax1 + (float)$tax->FlatTax2 + (float)$tax->FlatTax3;
                    if(!empty($taxPercent)) {
                        $finalPrice = str_replace(",", "",$price);
                        $finalPriceForTax = $finalPrice;
                        $taxAmount = $finalPriceForTax*($taxPercent/100);
                    }
                    if(!empty($flatTax)) {
                        $taxAmount += $flatTax;
                    }

                    $_POST['taxes'] = array(
                        'taxID'=>$prow->taxID,
                        'type'=>'add',
                        'taxPercent'=>$taxPercent,
                        'flatTax'=>$flatTax,
                        'taxAmount'=>$taxAmount,
                    );

                }//end tax

                $paid = $price + $taxAmount;
                $balance = $paid;
            }


            $save = gpx_save_guest($tp);

            $_POST['paid'] = $paid;
            $_POST['pp'][$id] = $paid;
            $_POST['fullPrice'][$id] = $price;
            $_POST['balance'] = $balance;
            $_POST['WeekPrice'] = $price;

            $book = $gpx->DAECompleteBooking($_POST);

            if($_POST['type'] == 'ExchangeWeek')
            {
                $sql = $wpdb->prepare("UPDATE wp_partner
                SET no_of_rooms_received_taken = no_of_rooms_received_taken + 1, trade_balance = trade_balance - 1
                WHERE user_id = %s", $tp);
                $wpdb->query($sql);
            }


        }
    }

}
add_action("wp_ajax_tp_claim_week", "tp_claim_week");






/**
 *
 *
 *
 *
 */
function tp_adjust_balance()
{
    global $wpdb;

    $sql = $wpdb->prepare("SELECT no_of_rooms_given, no_of_rooms_received_taken, trade_balance, adjData FROM wp_partner WHERE user_id=%s", $_POST['user']);
    $credits = $wpdb->get_row($sql);

    $num = $_POST['num'];
    $note = [];
    if(!empty($credits->adjData));
    {
        $note = json_decode($credits->adjData, true);
    }
    $note[strtotime('now')] = $_POST['note'];

    $tpAdjust = [
        'partner_id'=>$_POST['user'],
        'comments'=>$_POST['note'],
        'updated_at'=>date('Y-m-d H:i:s'),
    ];

    if($_POST['type'] == 'plus')
    {
        $toUpdate = [
            'no_of_rooms_given' => $credits->no_of_rooms_given + $num,
            'trade_balance' => $credits->trade_balance + $num,
            'no_of_rooms_received_taken' => $credits->no_of_rooms_received_taken,
            'adjData'=>json_encode($note),
        ];
        $tpAdjust['credit_add'] = $num;
    }

    if($_POST['type'] == 'minus')
    {
        $toUpdate = [
            'no_of_rooms_received_taken' => $credits->no_of_rooms_received_taken + $num,
            'trade_balance' => $credits->trade_balance - $num,
            'no_of_rooms_given' => $credits->no_of_rooms_given,
            'adjData'=>json_encode($note),
        ];
        $tpAdjust['credit_subtract'] = $num;
    }
    $updae = $wpdb->update('wp_partner', $toUpdate, array('user_id'=>$_POST['user']));
    $insert = $wpdb->insert('wp_partner_adjustments', $tpAdjust);
    $data = [
        'success' => true,
        'html' => '<button class="btn btn-secondary" disabled>New Trade Balance: '.$toUpdate['trade_balance'].'</button>',
    ];

    wp_send_json($data);
}
add_action("wp_ajax_tp_adjust_balance", "tp_adjust_balance");



/**
 *
 *
 *
 *
 */
function tp_debit()
{
    global $wpdb;

    $sql = $wpdb->prepare("SELECT name, debit_id, debit_balance FROM wp_partner WHERE user_id=%s", $_POST['id']);
    $row = $wpdb->get_row($sql);

    $cartID = $_POST['id']."_".strtotime('now');
    $transactionType = 'pay_debit';

    $dbData = [
        'MemberNumber'=>$_POST['id'],
        'MemberName'=>$row->name,
        'cartID' => $cartID,
        'transactionType' => $transactionType,
        'Paid'=>$_POST['amt'],
    ];


    $transaction = [
        'cartID'=>$cartID,
        'transactionType'=>$transactionType,
        'userID'=>$_POST['id'],
        'data'=>json_encode($dbData)
    ];
    $wpdb->insert('wp_gpxTransactions', $transaction);

    $dbData['transactionID'] = $wpdb->insert_id;

    $amount = $_POST['amt'] * -1;

    $debit = [
        'user'=>$_POST['id'],
        'data'=>json_encode($dbData),
        'amount'=>$amount,
    ];

    $wpdb->insert('wp_partner_debit_balance', $debit);

    $ids = [];
    $ids = json_decode($row->debit_id, true);
    $ids[] = $wpdb->insert_id;

    $newbalance = $row->debit_balance + $amount;

    $wpdb->update('wp_partner', array('debit_balance'=>$newbalance, 'debit_id'=>json_encode($ids)), array('user_id'=>$_POST['id']));

    $data['success'] = true;
    $data['balance'] = $newbalance;

    wp_send_json($data);
}
add_action('wp_ajax_tp_debit', 'tp_debit');




/**
 *
 *
 *
 *
 */
function gpx_hold_property()
{
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    $gpxadmin = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $cid = $_GET['cid'];
    $pid = $_GET['pid'];

    if(isset($_GET['wid']) && (empty($pid) || $pid == ''))
    {
        $pid = $_GET['wid'];
    }

    $liid = get_current_user_id();

    $agentOrOwner = 'owner';
    if($cid != $liid)
    {
        $agentOrOwner = 'agent';
    }

    $sql = $wpdb->prepare("SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId=%s AND cancelled IS NULL", $pid);
    $trow = $wpdb->get_var($sql);

    if($trow > 0)
    {
        $wpdb->update('wp_room', array('active'=>'0'), array('record_id'=>$pid));
        $output = [
            'error'=>'This week is no longer available.',
            'msg'=>'This week is no longer available.',
            'inactive'=>true,
        ];
        wp_send_json($output);
    }


    $activeUser = get_userdata($liid);

    $bookingrequest = '';
    if(!empty($_REQUEST['bookingrequest']))
    {
        $bookingrequest = 'true';
    }

    $sql = $wpdb->prepare("SELECT gpr_oid FROM wp_mapuser2oid WHERE gpx_user_id=%s LIMIT 1", $cid);
    $oid4credit = $wpdb->get_row($sql);

    $holdcount = 0;
    $holdcount = count($gpx->DAEGetWeeksOnHold($cid));
    $credits = $gpxadmin->GetMemberCredits($oid4credit->gpr_oid);

    $sql = $wpdb->prepare("SELECT id, release_on FROM wp_gpxPreHold WHERE user=%s AND propertyID=%s AND released=0", [$cid,$pid]);
    $row = $wpdb->get_row($sql);

    //return true if credits+1 is greater than holds
    if($credits+1 > $holdcount || $agentOrOwner == 'agent')
    {
        //we're good we can continue holding this
        if(empty($row))
        {
            //does someone else have this on hold?
            $iSql = $wpdb->prepare("SELECT id, release_on FROM wp_gpxPreHold WHERE propertyID=%s AND released=0", $pid);
            $iRow = $wpdb->get_row($iSql);
            if(!empty($iRow))
            {
                $output = [
                    'error'=>'This week is no longer available.',
                    'msg'=>'This week is no longer available.',
                    'inactive'=>true,
                ];

                wp_send_json($output);
            }
        }
    }
    else
    {
        $output = array('error'=>'too many holds', 'msg'=>get_option('gpx_hold_error_message'));

// TODO  another ifdonothing - silly FIX
        if(!empty($bookingrequest))
        {
            //is this a new hold request
            //we dont' need to do anything here right now but let's leave it just in case
        }
        else
        {
            //since this isn't a booking request we need to return the error and prevent anything else from happeneing.
            if(empty($row))
            {
                if(wp_doing_ajax())
                {
                    wp_send_json($output);
                }
                else
                {
                    return $output;
                }
            }
        }
    }

    $timeLimit = get_option('gpx_hold_limt_time');
    if(!$timeLimit || isset($_REQUEST['button']))
    {
        $timeLimit = '24';
    }

    $release_on = strtotime("+".$timeLimit." hours");

    if(!isset($_GET['cid']) || $_GET['cid'] == 0)
        $hold = array('login'=>true);

    if(empty($_GET['lpid']))
    {
        $_GET['lpid'] = '0';
    }


    $sql = $wpdb->prepare("SELECT id, data FROM wp_gpxPreHold WHERE user=%s AND weekId=%s", [$_GET['cid'],$_GET['pid']]);
    $holds = $wpdb->get_row($sql);

    $holdDets[strtotime('now')] = [
        'action'=>'held',
        'by'=>$activeUser->first_name." ".$activeUser->last_name,
    ];

    $data = array(
        'propertyID'=>$_GET['pid'],
        'weekId'=>$_GET['pid'],
        'user'=>$_GET['cid'],
        'lpid'=>$_GET['lpid'],
        'released'=>0,
        'release_on'=>date('Y-m-d H:i:s', $release_on),
        'data'=>json_encode($holdDets),
    );
    if(isset($_GET['weekType']))
    {
        $data['weekType'] = str_replace(" ", "", $_GET['weekType']);
    }

    if(isset($holds->id))
    {
        $wpdb->delete('wp_gpxPreHold', array('user'=>$cid, 'propertyID'=>$pid));
    }

    $wpdb->insert('wp_gpxPreHold',$data);
    $update = $wpdb->insert_id;

    $wpdb->update('wp_room', array('active'=>'0'), array('record_id'=>$pid));

    $sql = $wpdb->prepare("SELECT release_on FROM wp_gpxPreHold WHERE user=%s AND weekId=%s", [$_GET['cid'],$pid]);
    $rel = $wpdb->get_row($sql);
    $data['msg'] = 'Success';

    $data['release_on'] = date('m/d/Y H:i:s', strtotime($rel->release_on));

    wp_send_json($data);
}
add_action('wp_ajax_gpx_hold_property', 'gpx_hold_property');
add_action('wp_ajax_nopriv_gpx_hold_property', 'gpx_hold_property');





/**
 *
 *
 *
 *
 */
function get_dae_weeks_hold()
{
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $sql = "SELECT DISTINCT meta_value FROM wp_usermeta WHERE meta_key='DAEMemberNo'";
    $results = $wpdb->get_results($sql);
    $i = 0;
    foreach($results as $row)
    {
        $DAEMemberNo = $row->meta_value;

        $hold = $gpx->DAEGetWeeksOnHold($DAEMemberNo);
        if(!empty($hold))
        {
            //release weeks
            if(isset($hold['country']))
                $hold = array($hold);
            foreach($hold as $h)
            {
                $inputMembers = array(
                    'WeekEndpointID' => $h['WeekEndpointID'],
                    'WeekID' => $h['weekId'],
                    'DAEMemberNo' => $DAEMemberNo,
                    'ForImmediateSale' => true,
                );
                $gpx->DAEReleaseWeek($inputMembers);
                $i++;
            }
        }
    }

    $data = array('success'=>$i.' held weeks removed.');
    wp_send_json($data);
}
add_action('wp_ajax_get_dae_weeks_hold', 'get_dae_weeks_hold');
add_action('wp_ajax_nopriv_get_dae_weeks_hold', 'get_dae_weeks_hold');




/**
 *
 *
 *
 *
 */
function test_cron_release_holds()
{
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $releasedate = date('Y-m-d H:i:s');

    $sql = $wpdb->prepare("SELECT a.id, a.weekId, a.user, a.data, b.record_id FROM wp_gpxPreHold a
            INNER JOIN wp_room b on a.propertyID=b.record_id
            WHERE a.released=0 and a.release_on IS NOT NULL and a.release_on <= %s", $releasedate);
    $rows = $wpdb->get_results($sql);

    $i = 0;
    foreach($rows as $row)
    {
        $holdDets = json_decode($row->data, true);
        $holdDets[strtotime('now')] = [
            'action'=>'released',
            'by'=>'System',
        ];

        $sql = $wpdb->prepare("SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId=%s AND cancelled IS NULL", $row->weekId);
        $trow = $wpdb->get_var($sql);

        if($trow > 0)
        {
            $wpdb->update('wp_room', array('active'=>'0'), array('record_id'=>$row->weekId));
        }
        else
        {

            //we always need to check the "display date" prior to making it active. Only make this active when the sell date is in the future.
            $sql = $wpdb->prepare("SELECT active_specific_date FROM wp_room WHERE record_id=%d", $row->weekId);
            $activeDate = $wpdb->get_var($sql);

            if(strtotime('NOW') >  strtotime($activeDate))
            {
                $wpdb->update('wp_room', array('active'=>1), array('record_id'=>$row->weekId));
            }
        }


        $wpdb->update('wp_gpxPreHold', array('released'=>1, 'data'=>json_encode($holdDets)), array('id'=>$row->id));
        $i++;
    }

    $sql = "SELECT b.record_id as weekId, a.released FROM wp_room b
            INNER JOIN wp_gpxTransactions t ON t.weekId = b.record_id
            LEFT OUTER JOIN wp_gpxPreHold a on a.propertyID=b.record_id
            WHERE b.active=1 AND t.cancelled IS NULL";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {
        if($row->released == '0')
        {
            continue;
        }

        $sql = $wpdb->prepare("SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId=%s AND cancelled IS NULL", $row->weekId);
        $trow = $wpdb->get_var($sql);

        if($trow > 0)
        {

            $wpdb->update('wp_room', array('active'=>'0'), array('record_id'=>$row->weekId));
        }
    }

    $data = array('success'=>$i.' held weeks removed.');
    wp_send_json($data);
}
add_action('cron_gpx_release_weeks', 'test_cron_release_holds');
add_action('wp_ajax_gpx_release_weeks', 'test_cron_release_holds');
add_action('wp_ajax_nopriv_gpx_release_weeks', 'test_cron_release_holds');




/**
 *
 *
 *
 *
 */
function gpx_save_guest($tp='')
{
    global $wpdb;

    if(!isset($_POST['adults']))
        $_POST['adults'] = '1';
    if(!isset($_POST['children']))
        $_POST['children'] = '0';

    $_POST['user_type'] = 'Owner';
    $loggedinuser =  get_current_user_id();
    if($loggedinuser != $_POST['user']);
    $_POST['user_type'] = 'Agent';


    $user = get_userdata($_POST['user']);
    if(isset($user) && !empty($user))
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $_POST['user'] ) );

    $searchSessionID = '';
    if(isset($usermeta->searchSessionID))
    {
        $searchSessionID = $usermeta->searchSessionID;
    }

    // pull old cart id record
    $sql = $wpdb->prepare("SELECT id, data FROM wp_cart WHERE cartID=%s AND propertyID=%s", [$_POST['cartID'],$_POST['propertyID']]);
    $row = $wpdb->get_row($sql);
    //funky merge
    if(!empty($row))
    {
        $jsonData = json_decode($row->data, true);
        // loop through old data
        foreach($jsonData as $jdK=>$jdV)
        {
            if(!isset($_POST[$jdK]))
            {
                $_POST[$jdK] = $jdV;
            }
        }
    }
    /*  band-aid to not use old cart tax data when a taxed transaction of the same user/weekid has multiple carts

    example : a partner books a rental week and cancels then books the same exchange week, the tax is used from the
    previous week. This fix stops it from populating the old values.
     *
     */
    if(isset($_POST['taxes']) && $_POST['taxes'] === []) unset($_POST['taxes']);

    $json = json_encode($_POST);

    $data['user'] = $_POST['user'];
    $data['cartID'] = $_POST['cartID'];
    $data['sessionID'] = $searchSessionID;
    $data['propertyID'] = $_POST['propertyID'];
    $data['weekId'] = $_POST['weekId'];
    $data['data'] = $json;

    if(!empty($row))
        $update = $wpdb->update('wp_cart', $data, array('id'=>$row->id));
    else
        $insert = $wpdb->insert('wp_cart', $data);
    $return = array('success'=>true, 'id'=>$wpdb->insert_id);
    if(empty($tp))
    {
        wp_send_json($return);
    }
    else
    {
        return $return;
    }
}
add_action('wp_ajax_gpx_save_guest', 'gpx_save_guest');
add_action('wp_ajax_nopriv_gpx_save_guest', 'gpx_save_guest');


/**
 *
 *
 *
 *
 */
function update_checkin()
{
    global $wpdb;

    $sql = "SELECT id, data from wp_gpxTransactions";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {
        $data = json_decode($row->data);
        $checkin['check_in_date'] = date('Y-m-d', strtotime($data->checkIn));
        $wpdb->update('wp_gpxTransactions', $checkin, array('id'=>$row->id));
    }
}
add_action('wp_ajax_update_checkin', 'update_checkin');






/**
 *
 *
 *
 *
 */
function gpx_payment_submit()
{
    global $wpdb;
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $cid = gpx_get_switch_user_cookie();

    if(isset($_POST['ownerCreditCoupon']) && $_POST['paid'] == 0 && !isset($_POST['simpleCheckout']))
    {

        $book = $gpx->DAECompleteBooking($_POST);
    }
    elseif(isset($_POST['paid']) && $_POST['paid'] == 0 && !isset($_POST['simpleCheckout']))
    {
        //adding an elseis is a little overkill -- we could just use if paid == 0 but I want to leave it here in case they change their mind
        //When the paid amount is zero then we can just process this with DAECompleteBooking instead of going through the payment process.
        $book = $gpx->DAECompleteBooking($_POST);
    }
    elseif((isset($_POST['billing_number']) && !empty($_POST['billing_number'])) || isset($_POST['simpleCheckout']))
    {
        if(isset($_POST['paid']) && $_POST['paid'] > 0)
        {
            $paymentRequired = array(
                'Address'=>'billing_address',
                'City'=>'billing_city',
                'State'=>'billing_state',
                'Post Code'=>'billing_zip',
                'Country'=>'biling_country',
                'Email'=>'billing_email',
                'Card Holder'=>'billing_cardholder',
                'Card Number'=>'billing_number',
                'CCV'=>'billing_ccv',
                'Expiry Month'=>'billing_month',
                'Expiry Year'=>'billing_year',
            );
            $reqError = array();
            foreach($paymentRequired as $pKey=>$pValue)
            {
                if(!isset($_POST[$pValue]) || (isset($_POST[$pValue]) && empty($_POST[$pValue])))
                    $reqError[] = $pKey;
            }
        }
        if(isset($reqError) && !empty($reqError))
        {
            $isorare = 'is';
            if(count($reqError) > 1)
                $isorare = 'are';
            $book = array('ReturnCode'=>'10001', 'ReturnMessage'=>'You must complete the payment details! '.implode(", ", $reqError).' '.$isorare.' required.');
        }
        else
        {
            if(isset($_POST['simpleCheckout']))
            {

                $post = $_POST;

                $sql = $wpdb->prepare("SELECT user, data FROM wp_cart WHERE cartID=%s ORDER BY id DESC LIMIT 1", $post['cartID']);
                $cart = $wpdb->get_row($sql);
                $cartData = json_decode($cart->data);

                if(isset($_POST['paid']) && $_POST['paid'] > 0)
                {

                    $sql = $wpdb->prepare("SELECT item as type, data FROM wp_temp_cart WHERE id=%s", $cartData->tempID);
                    $temp = $wpdb->get_row($sql);
                    $tempData = json_decode($temp->data);

                    //is this a duplicate transaction
                    $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE cartID=%s AND transactionType=%s", [$post['cartID'],$temp->type]);
                    $row = $wpdb->get_row($sql);

                    if(!empty($row))
                    {
                        $data['error'] = 'Transaction processed.';
                        wp_send_json($data);
                    }

                    $sf = Salesforce::getInstance();

                    //charge the full amount
                    $sql = $wpdb->prepare("SELECT i4go_responsecode, i4go_uniqueid FROM wp_payments WHERE id=%s", $_REQUEST['paymentID']);
                    $i4go = $wpdb->get_row($sql);

                    if($i4go->i4go_responsecode != 1)
                    {
                        $output['error'] = 'Invalid Credit Card';
                        return $output;
                    }

                    $i4goToken = $i4go->i4go_uniqueid;
                    //add this token data to this user
                    $shift4TokenData = $usermeta->shiftfourtoken;
                    $sft = unserialize($shift4TokenData);
                    if( !empty($sft) && is_array($sft))
                    {
                        $sft[] = [
                            'token' => $i4goToken,
                        ];
                    }
                    else
                    {
                        $sft = [
                            'token' =>$i4goToken,
                        ];
                    }
                    update_user_meta($cartData->user, 'shiftfourtoken', serialize($sft));
                }

                $fullPriceForPayment = $_REQUEST['amount'];

                $paymentRef = $_REQUEST['paymentID'];
                $type = [
                    $_REQUEST['item'],
                ];

                if(isset($post['ownerCreditCoupon']))
                {
                    $placeholders = gpx_db_placeholders($cartData->occoupon);
                    $values = array_values($cartData->occoupon);
                    $values[] = $cid;
                    $osql = $wpdb->prepare("SELECT *, a.id as cid, b.id as aid, c.id as oid FROM wp_gpxOwnerCreditCoupon a
                                        INNER JOIN wp_gpxOwnerCreditCoupon_activity b ON b.couponID=a.id
                                        INNER JOIN wp_gpxOwnerCreditCoupon_owner c ON c.couponID=a.id
                                        WHERE a.id IN ({$placeholders}) AND a.active=1 and c.ownerID=%s", $values);
                    $occoupons = $wpdb->get_results($osql);
                    if(!empty($occoupons))
                    {
                        foreach($occoupons as $occoupon)
                        {
                            $distinctCoupon = $occoupon;
                            $distinctOwner[$occoupon->oid] = $occoupon;
                            $distinctActivity[$occoupon->cid."_".$occoupon->aid] = $occoupon;
                        }

                        //get the balance and activity for data
                        foreach($distinctActivity as $fid=>$activity)
                        {
                            $eid = explode("_", $fid);
                            $ocid = $eid[0];
                            if($activity->activity == 'transaction')
                            {
                                $actredeemed[] = $activity->amount;
                                $eachCouponRedeemed[$ocid][] = $activity->amount;
                            }
                            else
                            {
                                $actamount[] = $activity->amount;
                                $eachCouponActAmount[$ocid][] = $activity->amount;
                            }
                        }

                        if($distinctCoupon->single_use == 1 && array_sum($actredeemed) > 0)
                        {
                            $balance = 0;
                        }
                        else
                        {
                            $balance = array_sum($actamount) - array_sum($actredeemed);
                        }
                        //if we have a balance at this point the the coupon is good
                        if($balance > 0)
                        {
                            if($balance <= $fullPriceForPayment)
                            {
                                $fullPriceForPayment = $fullPriceForPayment - $balance;
                                $indCartOCCreditUsed[] = $balance;
                                $couponDiscount = array_sum($indCartOCCreditUsed);
                            }
                        }
                    }
                }


                if(isset($_POST['paid']) && $_POST['paid'] > 0)
                {
                    $shift4 = new Shiftfour();

                    $paymentDetails = $shift4->shift_sale($i4goToken, $fullPriceForPayment, $totalTaxCharged, $paymentRef, $usermeta->DAEMemberNo);

                    $paymentDetailsArr = json_decode($paymentDetails, true);

                    if($paymentDetailsArr['result'][0]['error'])
                    {
                        //this is an error how should we proccess
                        if($paymentDetailsArr['result'][0]['error']['primaryCode'] == 9961)
                        {
                            sleep(5);
                            $failedPayment = $shift4->shift_invioce($_REQUEST['paymentID']);
                            $paymentDetailsArr = json_decode($failedPayment, true);
                            //do we have an invoice?
                            if($paymentDetailsArr['result'][0]['error']['primaryCode'] == 9815)
                            {
                                //we don't have an invoice -- log this error
                                $wpdb->update('wp_payments', array('i4go_responsetext'=>json_encode($paymentDetailsArr['result'][0]['error'])), array('id'=>$_REQUEST['paymentID']));
                                $jsonBook = json_encode($paymentDetailsArr['result'][0]['error']);
                                $dbbook = array(
                                    'cartID'=>$post['cartID'],
                                    'data'=>$jsonBook,
                                    'returnTime'=>$seconds,
                                );
                                $wpdb->insert('wp_gpxFailedTransactions', $dbbook);

                                return array('error'=>'Please try again later.');
                            }
                            $wpdb->update('wp_payments', array('i4go_responsetext'=>json_encode($paymentDetailsArr['result'][0]['error'])), array('id'=>$_REQUEST['paymentID']));
                            $jsonBook = json_encode($failedPayment);
                            $dbbook = array(
                                'cartID'=>$post['cartID'],
                                'data'=>$jsonBook,
                                'returnTime'=>$seconds,
                            );
                            $wpdb->insert('wp_gpxFailedTransactions', $dbbook);

                            return array('ReturnMessage'=>'Please try again later.');
                        }
                    }

                    $book['ReturnCode'] = $paymentDetailsArr['result'][0]['transaction']['responseCode'];
                    $output['PaymentReg'] = ltrim($paymentDetailsArr['result'][0]['transaction']['invoice'], '0');
                }
                else
                {
                    $book['ReturnCode'] = 'A';
                }

                if($book['ReturnCode'] == 'A')
                {
                    $charged = true;

                    //what type of charge was this?


                    $sql = $wpdb->prepare("SELECT item as type, data FROM wp_temp_cart WHERE id=%s", $cartData->tempID);
                    $temp = $wpdb->get_row($sql);
                    $tempData = json_decode($temp->data);

                    $tempData->PaymentID = $_REQUEST['paymentID'];
                    $tempData->Paid = $fullPriceForPayment;

                    if($temp->type == 'extension')
                    {
                        $tempData->actextensionFee = $tempData->fee;
                    }
                    if($temp->type == 'guest')
                    {
                        $tempData->actguestFee = $tempData->fee;
                    }
                    if($temp->type == 'deposit')
                    {
                        $pd = $tempData->Paid;
                        if(isset($tempData->ownerCreditCouponAmount))
                        {
                            $pd += $tempData->ownerCreditCouponAmount;
                        }
                        $tempData->lateDepositFee = $pd;
                    }
                    //add the transaction
                    $transdata = [
                        'transactionType'=>$temp->type,
                        'cartID'=>$post['cartID'],
                        'userID'=>$cart->user,
                        'paymentGatewayID'=>$_REQUEST['paymentID'],
                        'transactionData'=>json_encode($tempData),
                        'data'=>json_encode($tempData),
                    ];

                    if($temp->type == 'late_deposit_fee' || $temp->type == 'deposit')
                    {
                        $bank = gpx_post_will_bank($tempData, $cid);
                        $tempData->creditid = $bank['creditid'];
                        $transdata['data'] = json_encode($tempData);

                        $import = hook_credit_import();
                    }

                    if($temp->type == 'extension')
                    {
                        $extend = gpx_extend_credit($tempData, $cid);

                        $import = hook_credit_import();
                    }

                    if($temp->type == 'guest')
                    {
                        $guest = gpx_reasign_guest_name($tempData, $cid);
                    }

                    if(isset($post['ownerCreditCoupon']))
                    {
                        $occUsedBalance = $post['ownerCreditCoupon'];

                        foreach($cartData->occoupon as $occ)
                        {
                            $eachBalance[$occ] = array_sum($eachCouponActAmount[$occ]) - array_sum($eachCouponRedeemed[$occ]);

                            if($occUsedBalance == $eachBalance[$occ] || $eachBalance[$occ] > $occUsedBalance)
                            {
                                $occUsed = $occUsedBalance;
                            }
                            else
                            {
                                $occUsed = $eachBalance[$occ];
                                $occUsedBalance = $occUsedBalance - $eachBalance[$occ];
                            }

                            $occActivity[$occ] = [
                                'couponID'=>$occ,
                                'activity'=>'transaction',
                                'amount'=>$occUsed,
                                'userID'=>$cart->user,
                            ];

                        }
                        $tempData->ownerCreditCouponID = $cartData->occoupon;
                        $tempData->ownerCreditCouponAmount = $post['ownerCreditCoupon'];

                        $transdata['data'] = json_encode($tempData);
                    }

                    $wpdb->insert('wp_gpxTransactions', $transdata);

                    $transactionID = $wpdb->insert_id;

                    if(isset($post['ownerCreditCoupon']))
                    {

                        foreach($occActivity as $oa)
                        {
                            $oa['xref'] = $transactionID;

                            $wpdb->insert('wp_gpxOwnerCreditCoupon_activity', $oa);
                        }
                    }

                    $gpx->transactiontosf($transactionID);

                }

            }
            else
            {
                $book = $gpx->DAEPayAndCompleteBooking($_POST);
            }
        }
    }
    else
    {
        //      Until we launch we want general customers (any owner account) to be able to complete a booking without credit card details.
        if(get_current_user_id() != $cid) //only agents can post without a payment
            $book = $gpx->DAECompleteBooking($_POST);
        else
            $book = array('ReturnCode'=>'10001', 'ReturnMessage'=>'You must complete the payment details!');
    }

    $bookingErrorCodes = array(
        '0',
        '105',
        '106',
        'A',
        'a',
    );
    if(isset($book['ReturnCode']) && in_array($book['ReturnCode'], $bookingErrorCodes))
    {
        $data = array('success'=>true);
        if(isset($_REQUEST['item']))
        {
            $data['type'] = $_REQUEST['item'];
            $data['msg'] = 'Success!';
        }
    }
    else
    {
        if(isset($book['error']))
        {
            $book['ReturnMessage'] = $book['error'];
        }
        else
        {
            $book['ReturnMessage'] = 'Unable to process your request at this time.  Please try again later.';
        }
        $data = array('error'=>$book['ReturnMessage']);
    }

    wp_send_json($data);
}
add_action('wp_ajax_gpx_payment_submit', 'gpx_payment_submit');
add_action('wp_ajax_nopriv_gpx_payment_submit', 'gpx_payment_submit');




/**
 *
 *
 *
 *
 */
function cg_payment_submit($id='')
{
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    if(!empty($_GET['id']))
    {
        $id = $_GET['id'];
    }
    $t = $gpx->transactiontosf($id);
}
add_action('wp_ajax_cg_payment_submit', 'cg_payment_submit');



/**
 *
 *
 *
 *
 */
function function_missed_transactions($id='')
{
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $sql = "SELECT id FROM `wp_gpxTransactions` WHERE `sfid` IS NULL AND datetime > '2021-10-01 00:00:00'";
    $txs = $wpdb->get_results($sql);

    foreach($txs as $tx)
    {
        $t = $gpx->transactiontosf($tx->id);
    }

    return true;
}
add_action('hook_cron_function_missed_transactions', 'function_missed_transactions');




/**
 *
 *
 *
 *
 */
function gpx_resend_confirmation()
{
    global $wpdb;
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $confID = $gpx->DAEReIssueConfirmation($_GET);
    $msg = '<a href="/confirmation-download?id='.$confID.'&week='.$_GET['weekid'].'&no='.$_GET['memberno'].'" target="_blank">Please click here to view your confirmation.</a>';
    if($confID == 0)
        $msg = 'There was an error process your request.  Please try again later.';
    $data = array('msg'=>$msg);

    wp_send_json($data);
}
add_action('wp_ajax_gpx_resend_confirmation', 'gpx_resend_confirmation');
add_action('wp_ajax_nopriv_gpx_resend_confirmation', 'gpx_resend_confirmation');



/**
 *
 *
 *
 *
 */
function gpx_save_confirmation()
{
    global $wpdb;
    if(substr($_SERVER['REQUEST_URI'], 0, 22) == '/confirmation-download')
    {
        $sql = $wpdb->prepare("SELECT id, pdf FROM wp_gpxPDFConf WHERE daeMemberNo=%s AND id=%s AND weekid=%s", [$_GET['no'],$_GET['id'],$_GET['week']]);
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




/**
 *
 *
 *
 *
 */
function get_gpx_upgrade_fees()
{
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


/**
 *
 *
 *
 *
 */
function get_gpx_transactions()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $tradepartner = '';
    if(isset($_GET['tradepartner']))
    {
        $tradepartner = true;
    }
    $data = $gpx->return_gpx_transactions($tradepartner);

    wp_send_json($data);
}

add_action('wp_ajax_get_gpx_transactions', 'get_gpx_transactions');


/**
 *
 *
 *
 *
 */
function gpx_admin_owner_transactions()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $tradepartner = '';
    if(isset($_GET['tradepartner']))
    {
        $tradepartner = true;
    }
    $group = " WHERE userID='".$_GET['userID']."'";
    if(isset($_GET['weekID']))
    {
        $group = " WHERE weekId='".$_GET['weekID']."'";
    }
    $data = $gpx->return_gpx_transactions($tradepartner, $group);

    wp_send_json($data);
}
add_action('wp_ajax_gpx_admin_owner_transactions', 'gpx_admin_owner_transactions');

/**
 *
 *
 *
 *
 */
function get_gpx_holds()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $group = '';

    if(!empty($_GET['userID']))
    {
        $group = " WHERE a.user='".$_GET['userID']."'";
    }

    if(!empty($_GET['weedID']))
    {
        $group = " WHERE a.weekId='".$_GET['weedID']."'";
    }

    $data = $gpx->return_get_gpx_holds($group);

    wp_send_json($data);
}

add_action('wp_ajax_get_gpx_holds', 'get_gpx_holds');
add_action('wp_ajax_nopriv_get_gpx_holds', 'get_gpx_holds');



/**
 *
 *
 *
 *
 */
function gpx_credit_action()
{
    global $wpdb;

    if(isset($_POST['id']))
    {

        $sf = Salesforce::getInstance();

        $pendingStatus = '';
        if($_POST['type'] == 'deposit_transferred')
        {
            $pendingStatus = 1;
            $sql = $wpdb->prepare("SELECT creditID, data FROM wp_gpxDepostOnExchange WHERE id=%s", $_POST['id']);
            $doe = $wpdb->get_row($sql);

            $_POST['id'] = $doe->creditID;
            $_POST['type'] = 'transferred';

            $depositData = json_decode($doe->data);

            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $depositData->cid ) );

            if($depositData->owner_id != get_current_user_id())
            {
                $agent = true;
                $agentmeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( get_current_user_id() ) );
                $depositBy = stripslashes(str_replace("&", "&amp;",$agentmeta->first_name))." ".stripslashes(str_replace("&", "&amp;",$agentmeta->last_name));
            }

            $email = $usermeta->Email;
            if(empty($email))
            {
                $email = $usermeta->email;
            }

            $query = "SELECT ID, Name FROM Ownership_Interval__c WHERE ROID_Key_Full__c = '".$depositData->RIOD_Key_Full."'";
            $results = $sf->query($query);

            $interval = $results[0]->Id;

            $sfCreditData = [
                'Account_Name__c'=>$depositData->Account_Name__c,
                'Check_In_Date__c'=>date('Y-m-d', strtotime($depositData->check_in_date)),
                'Deposit_Year__c'=>date('Y', strtotime($depositData->check_in_date)),
                'GPX_Member__c'=>$depositData->owner_id,
                'Deposit_Date__c'=>date('Y-m-d'),
                'Resort__c'=>$depositData->resortID,
                'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;", $depositData->Resort_Name__c)),
                'Resort_Unit_Week__c'=>$depositData->Resort_Unit_Week__c,
                'Member_Email__c'=>$email,
                'Member_First_Name__c'=>stripslashes(str_replace("&", "&amp;",$usermeta->first_name)),
                'Member_Last_Name__c'=>stripslashes(str_replace("&", "&amp;",$usermeta->last_name)),
                'Deposited_by__c'=>$depositBy,
                'Unit_Type__c'=>$depositData->unit_type,
                'Ownership_Interval__c'=>$interval,
            ];

            if(!empty($depositData->Reservation__c))
            {
                $sfCreditData['Reservation__c'] = $depositData->Reservation__c;
            }
            $tDeposit = [
                'status'=>'Pending',
                'unitinterval'=>$depositData->unitweek,
            ];
        }

        $sql = $wpdb->prepare("SELECT * FROM wp_credit WHERE id=%s", $_POST['id']);
        $credit = $wpdb->get_row($sql);

        $update = [
            'credit_action'=>$_POST['type'],
            'credit_used'=>$credit->credit_used + 1,
        ];

        if(!empty($tDeposit))
        {
            $update = array_merge($update, $tDeposit);
        }

        $sfCreditData['GPX_Deposit_ID__c'] = $credit->id;
        $sfCreditData['Credits_Used__c'] = $update['credit_used'];

        $sfWeekAdd = '';
        $sfAdd = '';
        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';


        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfCreditData;
        $sfFields[0]->type = $sfType;

        $sfDepositAdjust = $sf->gpxUpsert($sfObject, $sfFields, 'true');

        $sfDepostID = $sfDepositAdjust[0]->id;

        //if this is ICE then we need to do the ICE shortcode

        $wpdb->update('wp_credit', $update, array('id'=>$_POST['id']));

        //send the datails to SF as a transaction

        $sql = $wpdb->prepare("SELECT record_id FROM wp_partner WHERE user_id=%d",$credit->owner_id);
        $partner = $wpdb->get_row($sql);

        $poro = 'USA GPX Member';
        if(!empty($partner))
        {
            $poro = 'USA GPX Trade Partner';
        }

        if($_POST['type'] == 'donated'){
            $pt = 'Donation';
            $transactionType = 'credit_donation';

            $data['redirect'] = true;
        }

        if($_POST['type'] == 'transferred')
        {
            $pt = 'Transfer to Perks';
            $transactionType = 'credit_transfer';

            $data['redirect'] = true;
        }

        $sql = $wpdb->prepare("SELECT * FROM wp_GPR_Owner_ID__c WHERE user_id=%d",$credit->owner_id);
        $ownerData = $wpdb->get_row($sql);

        $user_info = get_userdata($credit->owner_id);


        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $credit->owner_id ) );

        if(empty($usermeta->Email))
        {
            $usermeta->Email = $usermeta->email;
            if(empty($usermeta->Email))
            {
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
        if($pt == 'Donation' || $pendingStatus == 1)
        {
            $sfData['Status__c'] = 'Pending';
        }
        elseif($pt ==  'Transfer to Perks')
        {
            $sfData['Status__c'] = 'Approved';
        }
        $sfData['Purchase_Type__c'] = '0';
        $sfData['Request_Type__c'] = $pt;
        $sfData['Transaction_Book_Date__c'] = date('Y-m-d');
        $sfData['Date_Last_Synced_with_GPX__c'] = date('Y-m-d');
        $bookedby_user_info = get_userdata(get_current_user_id());
        $sfData['Booked_By__c'] = $bookedby_user_info->first_name." ".$bookedby_user_info->last_name;
        $sfData['RecordTypeId'] = '0121W000000QQ75';

        if($pt == 'Transfer to Perks')
        {
            $sfData['ICE_Account_ID__c'] = $usermeta->ICENameId;
        }


        $txData = json_encode($sfData);

        $tx = [
            'transactionType'=>$transactionType,
            'cartID'=>'na',
            'userID'=>$credit->owner_id,
            'resortID'=>0,
            'weekId'=>0,
            'paymentGatewayID'=>'0',
            'transactionData'=>$txData,
            'data'=>$txData,
            'depositID'=>$credit->id,
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

        if(isset($sfAdd[0]->id))
        {
            $sfTransactionID = $sfAdd[0]->id;
            $sfDB = array(
                'sfid'=> $sfTransactionID,
                'sfData'=>json_encode(array('insert'=>$sfData)),
            );

            $wpdb->update('wp_gpxTransactions', $sfDB, array('id'=>$transactionID));
        }

        $data['action'] = ucfirst($_POST['type']);
    }
    $data['success'] = true;

    wp_send_json($data);
}
add_action('wp_ajax_gpx_credit_action', 'gpx_credit_action');
add_action('wp_ajax_gpx_credit_action', 'gpx_credit_action');



/**
 *
 *
 *
 *
 */
function gpx_credit_manual()
{
    global $wpdb;

    $data = ['success'=>false];
    if(isset($_REQUEST['id']))
    {
        $sf = Salesforce::getInstance();

        $sql = $wpdb->prepare("SELECT * FROM wp_credit WHERE id=%s", $_REQUEST['id']);
        $row = $wpdb->get_row($sql, ARRAY_A);

        $sql = $wpdb->prepare("SELECT * FROM wp_GPR_Owner_ID__c WHERE user_id=%s",$row['owner_id']);
        $ownerData = $wpdb->get_row($sql);

        $user_info = get_userdata($row['owner_id']);

        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $row['owner_id']) );

        if(empty($usermeta->Email))
        {
            $usermeta->Email = $usermeta->email;
            if(empty($usermeta->Email))
            {
                $usermeta->Email = $usermeta->user_email;
            }
        }

        $row['first_name'] = $usermeta->first_name;
        $row['last_name'] = $usermeta->last_name;
        $row['email'] = $usermeta->Email;
        $row['Property_Owner'] = $usermeta->Property_Owner;

        $sql = $wpdb->prepare("SELECT ROID_Key_Full FROM wp_owner_interval WHERE unitweek=%s AND userID=%s", [$row['unitinterval'],$row['owner_id']]);
        $depositData = $wpdb->get_row($sql);

        $query = $wpdb->prepare("SELECT ID, Name FROM Ownership_Interval__c WHERE ROID_Key_Full__c = %s", $depositData->RIOD_Key_Full);
        $results = $sf->query($query);

        $row['interval'] = $results[0]->Id;

        if($pt == 'Donation' || $pendingStatus == 1)
        {
            $sfData['Status__c'] = 'Pending';
        }

        $forSF = [
            'status'=>'Deposit_Status__c',
            'Property_Owner'=>'Account_Name__c',
            'check_in_date'=>'Check_In_Date__c',
            'deposit_year'=>'Deposit_Year__c',
            'owner_id'=>'GPX_Member__c',
            'created_date'=>'Deposit_Date__c',
            'resort_name'=>'Resort_Name__c',
            'unitinterval'=>'Resort_Unit_Week__c',
            'email'=>'Member_Email__c',
            'first_name'=>'Member_First_Name__c',
            'last_name'=>'Member_Last_Name__c',
            'unit_type'=>'Unit_Type__c',
            'interval'=>'Ownership_Interval__c',
            'id'=>'GPX_Deposit_ID__c',
            'credit_used'=>'Credits_Used__c',
            'credit_amount'=>'Credits_Issued__c',
        ];

        foreach($forSF as $sfK=>$sfV)
        {
            if(!empty($row[$sfK]))
            {
                $sfCreditData[$sfV] = $row[$sfK];
            }
        }

        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';

        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfCreditData;
        $sfFields[0]->type = $sfType;

        $sfDepositAdjust = $sf->gpxUpsert($sfObject, $sfFields, 'true');

        $data['success'] = true;
    }

    wp_send_json($data);
}
add_action('wp_ajax_gpx_credit_manual', 'gpx_credit_manual');



/**
 *
 *
 *
 *
 */
function gpx_transaction_fees_adjust()
{
    global $wpdb;

    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $shift4 = new Shiftfour();

    //send the data to sf
    $sf = Salesforce::getInstance();

    $data = [];

    if(isset($_POST['id']))
    {

        $id = $_POST['id'];
        $refundType = $_POST['refundType'];
        $type = $_POST['type'];
        $amount = $_POST['amount'];

        $sql = $wpdb->prepare("SELECT * FROM wp_gpxTransactions WHERE id=%s", $id);
        $trans = $wpdb->get_row($sql);

        $transaction = $trans->id;

        $transData = json_decode($trans->data);

        $origSFData = json_decode($trans->sfData, true);

        $cid = $trans->userID;

        $updateData = json_decode($trans->data, true);

        $updateDets = json_decode($trans->cancelledData, true);


        //what type of this change is this?
        if($type == 'full')
        {
            //just add the refunded amount to the data
            $updateData['refunded'] += $amount;
            $sfData['GPXTransaction__c'] = $transaction;
        }
        if($type == 'couponDiscount')
        {
            //update the coupon discount amount
            $couponAmount = number_format(str_replace("$", '', $updateData['couponDiscount']), 2, '.', '');
            $newCouponAmount = $couponAmount + $amount;
            $updateData['couponDiscount'] = '$'.$newCouponAmount;
            $updateData['refunded'] += $amount;
            $sfData['Reservation_Status__c'] = 'Cancelled';
            $sfData['Cancel_Date__c'] = date('Y-m-d');
            $sfData['GPXTransaction__c'] = $transaction;
        }
        if($type == 'discount')
        {
            //update the discount amount
            $discount = number_format(str_replace("$", '', $updateData['discount']), 2, '.', '');
            $newdiscount = $discount + $amount;
            $updateData['discount'] = $newdiscount;
            $updateData['refunded'] += $amount;
            $sfData['Reservation_Status__c	'] = 'Cancelled';
            $sfData['GPXTransaction__c'] = $transaction;
        }
        if($type == 'guestfeeamount')
        {
            //update the upgrade fee amount
            $guestfee = $updateData['actguestFee'];
            $newguestfee = $guestfee - $amount;
            if($newguestfee < 0)
            {
                $amount = $newguestfee + $amount;
                $newguestfee = 0;
                if($amount < 0)
                {
                    $amount = 0;
                }
            }
            $updateData['refunded'] += $amount;
            $sfData['GPXTransaction__c'] = $transaction;
        }
        if($type == 'upgradefee')
        {
            //update the upgrade fee amount
            $upgradefee = $updateData['actupgradeFee'];
            $newupgradefee = $upgradefee - $amount;
            if($newupgradefee < 0)
            {
                $amount = $newupgradefee + $amount;
                $newupgradefee = 0;
                if($amount < 0)
                {
                    $amount = 0;
                }
            }
            $updateData['refunded'] += $amount;
            $sfData['GPXTransaction__c'] = $transaction;
        }
        if($type == 'creditextensionfee')
        {
            //update the upgrade fee amount
            $extFee = $updateData['actextensionFee'];
            $newextFee = $extFee - $amount;
            if($newextFee < 0)
            {
                $amount = $newextFee + $amount;
                $newupgradefee = 0;
                if($amount < 0)
                {
                    $amount = 0;
                }
            }

            $updateData['refunded'] += $amount;
            $sfData['GPXTransaction__c'] = $transaction;
        }
        if($type == 'cpofee')
        {
            //update the cpo amount
            $cpofee = $updateData['actcpoFee'];
            $newcpofee = $cpofee - $amount;
            if($newcpofee < 0)
            {
                $amount = $newcpofee + $amount;
                $newcpofee = 0;
                if($amount < 0)
                {
                    $amount = 0;
                }
            }

            $updateData['refunded'] = $amount;
            $sfData['GPXTransaction__c'] = $transaction;
        }
        if($type == 'erFee')
        {
            //update the week price amount
            $weekpricefee = $updateData['actWeekPrice'];
            $newweekpricefee = $weekpricefee - $amount;

            if($newweekpricefee < 0)
            {
                $amount = $newweekpricefee + $amount;
                $newweekpricefee = '0';
                if($amount < 0)
                {
                    $amount = 0;
                }
            }

            $updateData['refunded'] += $amount;
            $sfData['GPXTransaction__c'] = $transaction;
        }
        if($type == 'cancel')
        {
            //update the coupon amount
            $updateData['refunded'] += $amount;
            $wpdbUpdate['cancelled'] = 1;

            $sfData['Reservation_Status__c'] = 'Cancelled';
            $sfData['Cancel_Date__c'] = date('Y-m-d');
            $sfData['GPXTransaction__c'] = $transaction;
        }

        //don't over refund!
        if($amount > 0)
        {

            //was this already cancelled?
            $cancelledData = json_decode($trans->cancelledData);

            foreach($cancelledData as $cd)
            {
                $ca[] = $cd->amount;
            }

            $paid = $transData->Paid;

            //paid includes coupon amounts -- let's add the monetary coupon
            if(isset($transData->ownerCreditCouponAmount))
            {

                $paid = $paid + $transData->ownerCreditCouponAmount;
                //the refund amount may need to be split -- only when refunding to credit card.
                $occRefund = $transData->ownerCreditCouponAmount;
            }

            if(isset($ca))
            {
                //remove the total refunded amount from the amount paid
                $paid = $paid - array_sum($ca);
            }

            /*
             * Closure Coupons
             * One that I specifically asked for progress on was the issue with coupons not being refunded, even when
             * processing an admin refund all request. I tried processing a refund for the entire amount to a coupon and
             * only the portion paid above and beyond the coupon value was refunded (see below).
             */
            if(isset($transData->coupon))
            {
                $tcoupon = (array) $transData->coupon;
                $coupon = reset( $tcoupon );
                $sql = $wpdb->prepare("SELECT Type, PromoType, Amount FROM wp_specials WHERE id=%s", $coupon);
                $promo = $wpdb->get_row($sql);

                if($promo->Type == 'coupon' && $promo->PromoType == 'Pct Off' && $promo->Amount == '100')
                {
                    $couponAmt = str_replace("$", "", $transData->couponDiscount);
                    $paid = $paid + $couponAmt;
                }
            }

            //if the refund amount is greater than the the
            if($amount > $paid)
            {
                $amount = $paid;
            }

        }

        //do we need to credit the credit card? -- note only admin can do this
        if($amount > 0)
        {

            if($refundType == 'refund')
            {
                if(isset($occRefund))
                {
                    $amount = $amount - $occRefund;
                }

                $user = wp_get_current_user();
                //is this user an admin or admin plus?
                if ( in_array( 'gpx_admin', (array) $user->roles ) || in_array( 'gpx_supervisor', (array) $user->roles ) )
                {
                    //refund the amount to the credit card
                    $cancel = $shift4->shift_refund($id, $amount);
                    $data['html'] = '<h4>A refund to the credit card on file has been generated.</h4>';

                    //send the data to SF
                    $refundAmt = $amount;

                    if(!empty($updateDets))
                    {
                        foreach($updateDets as $cd)
                        {
                            $refundAmt += $cd['amount'];
                        }
                    }
                    $sfData['Credit_Card_Refund__c'] = $refundAmt;
                }
                else
                {
                    $data['error'] = true;
                    $data['html'] = "<h3>You must be an administrator to refund a transaction</h3>";

                    wp_send_json($data);
                }
            }

            if(isset($occRefund) || $refundType != 'refund')
            {
                if(isset($occRefund))
                {
                    $amount = $occRefund;
                }

                //create a coupon for this amount
                //does this slug exist?
                $slug = $trans->weekId.$trans->userID;
                $sql = $wpdb->prepare("SELECT id FROM wp_gpxOwnerCreditCoupon WHERE couponcode=%s", $slug);
                $row = $wpdb->get_row($sql);
                if(!empty($row))
                {
                    //add random string to the end and check again
                    $rand = rand(1, 1000);
                    $slug = $slug.$rand;
                    $sql = $wpdb->prepare("SELECT id FROM wp_gpxOwnerCreditCoupon WHERE couponcode=%s", $slug);
                    $row = $wpdb->get_row($sql);
                    if(!empty($row))
                    {
                        //add random string to the end and check again
                        $rand = rand(1, 1000);
                        $slug = $slug.$rand;
                    }
                }

                $occ = [
                    'Name'=>$trans->weekId,
                    'Slug'=>$slug,
                    'Active'=>1,
                    'singleuse'=>0,
                    'amount'=>$amount,
                    'owners'=>[$trans->userID],
                    'comments'=>'Line Item Refund issued on transaction '.$trans->weekId,
                ];

                $cadd = $gpx->promodeccouponsadd($occ);

                $data['html'] = '<h4>An owner credit coupon named '.$occ['Name'].' has been generated.</h4>';
            }
        }

        if(isset($sfData))
        {
            $query = "SELECT GPX_Ref__c FROM GPX_Transaction__c WHERE GPXTransaction__c=".$transaction;

            $sfRef = $sf->query($query);

            $sfData['EMS_Account__c'] = $cid;
            $sfData['GPX_Ref__c'] = $sfRef[0]->fields->GPX_Ref__c;

            $totalAmount = '0';
            foreach($updateDets as $upd)
            {
                $totalAmount += $upd['acount'];
            }

            $sfFields = [];
            $sfFields[0] = new SObject();
            $sfFields[0]->fields = $sfData;
            $sfFields[0]->type = 'GPX_Transaction__c';
            $sfObject = 'GPXTransaction__c';
            $sfAdd = $sf->gpxUpsert($sfObject, $sfFields);

        }

        $agentInfo = wp_get_current_user();
        $agent = $agentInfo->first_name.' '.$agentInfo->last_name;

        $updateDets[strtotime("NOW")] = [
            'type'=>$type,
            'action'=>$refundType,
            'amount'=>$amount,
            'coupon'=>$cadd['coupon'],
            'by'=>get_current_user_id(),
            'agent_name'=> $agent,
        ];

        $wpdbUpdate['data'] = json_encode($updateData);
        $wpdbUpdate['cancelledData'] = json_encode($updateDets);
        $wpdb->update('wp_gpxTransactions', $wpdbUpdate, array('id'=>$id));

        $data['success'] = true;
    }

    wp_send_json($data);
}
add_action('wp_ajax_gpx_transaction_fees_adjust', 'gpx_transaction_fees_adjust');




/**
 *
 *
 *
 *
 */
function gpx_cancel_booking($transaction='')
{
    global $wpdb;

    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $sf = Salesforce::getInstance();

    if(isset($_POST['transaction']))
    {
        $transaction = $_POST['transaction'];
    }

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

    if(!empty($partner))
    {
        if (strpos(strtolower($transData->WeekType), 'exchange') !== false )
        {
            //adjust the credit
            $updateAmount = [
                'no_of_rooms_received_taken'=>$partner->no_of_rooms_received_taken - 1,
                'trade_balance'=>$partner->trade_balance + 1,
            ];
            $wpdb->update('wp_partner', $updateAmount, array('record_id'=>$partner->record_id));
        }
        else
        {
            //adjust the balance
            $tpTransData = $transData;
            $tpTransData->cancelled = date('m/d/Y');
            $debit = [
                'user'=>$partner->user_id,
                'data'=>json_encode($tpTransData),
                'amount'=>$tpTransData->Paid,
            ];

            $wpdb->insert('wp_partner_debit_balance', $debit);
            $pdid = $wpdb->insert_id;

            $debit_id = json_decode($partner->debit_id, true);
            $adjData = json_decode($partner->adjData, true);

            $debit_id[] = $pdid;
            $adjData[strtotime('now')] = 'cancelled';
            $debit_balance = $partner->debit_balance - $tpTransData->Paid;

            $updateAmount = [
                'adjData'=>json_encode($adjData),
                'debit_id'=>json_encode($debit_id),
                'debit_balance'=>$debit_balance,
            ];

            $wpdb->update('wp_partner', $updateAmount, array('record_id'=>$partner->record_id));
        }
    }
    elseif(isset($_REQUEST['admin_amt']) && trim(strpos(strtolower($transData->WeekType), 'rental')) !== false)
    {
        //this is an admin refunding from GPX Admin
        $refunded = $_REQUEST['admin_amt'];
    }
    elseif (trim(strpos(strtolower($transData->WeekType), 'exchange')) !== false )
    {

        if(!empty($canceledData))
        {
            foreach($canceledData as $cK=>$cD)
            {
                $alredyRefunded[$cK] = $cD->amount;
                $amt = $cd->amount;
                $refunds[$cD->type][] = array_sum($amt);
            }
        }

        //within 45 days or without flex booking
        if((strtotime($transRow->check_in_date) < strtotime('+45 days')))
        {
            //no refund
        }
        elseif($transData->CPO == 'Taken')
        {
            //refund everything but the CPO
            $cpoFee = get_option('gpx_fb_fee');
            if(empty($transData->CPOFee))
            {
                $cpoFee = $transData->CPOFee;
                if(isset($refunds['cpofee']))
                {
                    $cpoRefund = array_sum($refunds['cpofee']);
                    $cpoFee = $cpoFee - $cpoRefund;
                    unset($refunds['cpofee']);
                }

            }
            $extFee = 0;
            if(!empty($transData->actextensionFee) && $transData->actextensionFee != 'null')
            {

                $extFee = $transData->actextensionFee;
                if(isset($refunds['extensionfee']))
                {
                    $extRefund = array_sum($refunds['extensionfee']);
                    $extFee = $extFee - $extRefund;
                    unset($refunds['extensionfee']);
                }
            }
            /*
             * todo: check the object name
             */
            $lateDeposit = 0;
            if(!empty($transData->actlatedepositFee) && $transData->actlatedepositFee != 'null')
            {
                $lateDeposit = $transData->actlatedepositFee;
                if(isset($refunds['latedepositfee']))
                {
                    $lateDepositRefund = array_sum($refunds['latedepositfee']);
                    $lateDeposit = $lateDeposit - $lateDepositRefund;
                    unset($refunds['latedepositfee']);
                }
            }

            $paid = $transData->Paid;
            $refunded = $paid - $cpoFee-$extFee-$lateDeposit;
            //remove any other refunds
            if(isset($refunds))
            {
                $refunded = $refunded - array_sum($refunds);
            }

            /*
             * Closure Coupons
             * One that I specifically asked for progress on was the issue with coupons not being refunded, even when
             * processing an admin refund all request. I tried processing a refund for the entire amount to a coupon and
             * only the portion paid above and beyond the coupon value was refunded (see below).
             */
            if(isset($transData->coupon))
            {
                $tcoupon = (array) $transData->coupon;
                $coupon = reset( $tcoupon );
                $sql = $wpdb->prepare("SELECT Type, PromoType, Amount FROM wp_specials WHERE id=%s",  $coupon);
                $promo = $wpdb->get_row($sql);

                if($promo->Type == 'coupon' && $promo->PromoType == 'Pct Off' && $promo->Amount == '100')
                {
                    $couponAmt = str_replace("$", "", $transData->couponDiscount);
                    $refunded = $refunded + $couponAmt;
                }
            }
        }

    }


    /*
     * if there is a monetary coupon add that amount in
     */
    if($transData->ownerCreditCouponAmount && $transData->ownerCreditCouponAmount > 0)
    {
        $refunded = $refunded + $transData->ownerCreditCouponAmount;
        //the refund amount may need to be split -- only when refunding to credit card.
        $occRefund = $transData->ownerCreditCouponAmount;
    }

    if($refunded == 0 && isset($transData->GuestFeeAmount) && $transData->GuestFeeAmount > 0)
    {
        $refunded = $refunded + $transData->GuestFeeAmount;
    }

    if (strpos(strtolower($transData->WeekType), 'exchange') !== false )
    {
        //need to refresh the credit
        $sql = $wpdb->prepare("SELECT credit_used FROM wp_credit WHERE id=%s", $transData->creditweekid);
        $cr = (int) $wpdb->get_var($sql);
        $newcr = $cr - 1;

        $wpdb->update('wp_credit', array('credit_used'=>$newcr), array('id'=>$transData->creditweekid));

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
    if($refunded > 0)
    {

        //credit card or coupon

        //never ever allow anyone but admin to issue credit card refunds
        if(!$is_admin)
        {
            $_REQUEST['type'] = 'credit';
        }

        if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'refund')
        {

            if(isset($occRefund))
            {
                $refunded = $refunded - $occRefund;

            }

            $refundType = 'refund';
            $shift4 = new Shiftfour();

            //refund the amount to the credit card
            $cancel = $shift4->shift_refund($transaction, $refunded);
            $data['html'] = '<h4>A refund to the credit card on file has been generated.</h4>';

            $refundAmt = $refunded;
            foreach($canceledData as $cd)
            {
                $refundAmt += $cd->amount;
            }
            $sfData['Credit_Card_Refund__c'] = $refundAmt;
        }

        if(isset($occRefund) || (isset($_REQUEST['type']) && $_REQUEST['type'] != 'refund'))
        {

            if(isset($occRefund))
            {
                $refunded = $occRefund;
            }

            $refundType = 'credit';
            $slug = $transRow->weekId.$transRow->userID;
            do {
                $sql = $wpdb->prepare("SELECT id FROM wp_gpxOwnerCreditCoupon WHERE couponcode=%s", $slug);
                $exists = $wpdb->get_var($sql);
                if($exists) $slug = $transRow->weekId.$transRow->userID.rand(1, 1000);
            } while($exists);

            $occ = [
                'Name'=>$transRow->weekId,
                'Slug'=>$slug,
                'Active'=>1,
                'singleuse'=>0,
                'amount'=>$refunded,
                'owners'=>[$transRow->userID],
                'comments'=>'Reservation Cancelled -- Refund issued on transaction '.$transRow->weekId.($is_admin?' (Refund Exception)':''),
            ];
            $coupon = $gpx->promodeccouponsadd($occ);

            $data['html'] = "<h4>A $".$refunded." coupon has been generated.</h4>";
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
    $agent = $agentInfo->first_name.' '.$agentInfo->last_name;

    $update = [
        'userid'=> get_current_user_id(),
        'name'=> $agent,
        'date'=> date('Y-m-d H:i:s'),
        'refunded'=>$refunded,
        'coupon' => $coupon['coupon'],
        'agent_name'=> $agent,
    ];

    $canceledData = (array) $canceledData;

    $canceledData[strtotime('NOW')] = [
        'userid'=> get_current_user_id(),
        'name'=> $agent,
        'date'=> date('Y-m-d H:i:s'),
        'refunded'=>$refunded,
        'coupon' => $coupon['coupon'],

        'action'=>$refundType,
        'amount'=>$refunded,
        'by'=>get_current_user_id(),
        'agent_name'=> $agent,
    ];

    $wpdb->update('wp_gpxTransactions', array('cancelled'=>'1', 'cancelledData'=>json_encode($canceledData), 'cancelledDate'=>date('Y-m-d', strtotime("NOW"))), array('id'=>$transaction));

    $sql = $wpdb->prepare("SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId=%s AND cancelled IS NULL", $transRow->weekId);
    $trow = $wpdb->get_var($sql);


    // TODO if nothing to do again... fix
    if($trow > 0)
    {
        //nothing to do
    }
    else
    {

        //we always need to check the "display date" prior to making it active. Only make this active when the sell date is in the future.
        $sql = $wpdb->prepare("SELECT active_specific_date FROM wp_room WHERE record_id=%d",$transRow->weekId);
        $activeDate = $wpdb->get_var($sql);

        if(strtotime('NOW') >  strtotime($activeDate))
        {
            $wpdb->update('wp_room', array('active'=>1), array('record_id'=>$transRow->weekId));
        }
    }



    $data['success'] = true;
    $data['cid'] = $transRow->userID;
    $data['amount'] = $refunded;
    wp_send_json($data);
}

add_action('wp_ajax_gpx_cancel_booking', 'gpx_cancel_booking');
add_action('wp_ajax_nopriv_gpx_cancel_booking', 'gpx_cancel_booking');



/**
 *
 *
 *
 *
 */
function gpx_rework_add_cancelled_date()
{
    global $wpdb;

    $sql = "SELECT id, cancelledData FROM wp_gpxTransactions WHERE cancelled IS NOT NULL";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {
        $ddata = json_decode($row->cancelledData, true);
        end($ddata);
        $date = date('Y-m-d', key($ddata));

        if(strtotime($date) > strtotime('2020-11-01'))
        {
            $data['cancelledDate'] = $date;
            $wpdb->update('wp_gpxTransactions', $data, array('id'=>$row->id));
        }
    }

    wp_send_json($data);
}
add_action('wp_ajax_gpx_rework_add_cancelled_date', 'gpx_rework_add_cancelled_date');

/**
 *
 *
 *
 *
 */
function gpx_remove_guest()
{
    global $wpdb;

    $return = [];

    if(isset($_POST['transactionID']) && !empty($_POST['transactionID']))
    {
        $sql = $wpdb->prepare("SELECT userID, data FROM wp_gpxTransactions WHERE id=%s", $_POST['transactionID']);
        $row = $wpdb->get_row($sql);

        $data = json_decode($row->data);

        $memberID = $row->userID;

        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $memberID ) );

        $_POST['FirstName1'] = $usermeta->FirstName1;
        $_POST['LastName1'] = $usermeta->LastName1;

        $guest = gpx_reasign_guest_name($_POST);

        $data->GuestName = $_POST['FirstName1']." ".$_POST['LastName1'];

        $wpdb->update('wp_gpxTransactions', array('data'=>json_encode($data)), array('id'=>$_POST['transactionID']));


        $return['success'] = true;
    }


    wp_send_json($return);
}
add_action('wp_ajax_gpx_remove_guest', 'gpx_remove_guest');



/**
 *
 *
 *
 *
 */
function gpx_reasign_guest_name($postdata = '', $addtocart = '')
{
    global $wpdb;

    if(!empty($postdata))
    {
        $_POST = (array) $postdata;
    }

    $transaction = $_POST['transactionID'];

    $sql = $wpdb->prepare("SELECT sfid, sfData, data, weekId, userID FROM wp_gpxTransactions WHERE id=%d",$transaction);
    $row = $wpdb->get_row($sql);

    $cid = $row->userID;

    $usermeta = gpx_get_usermeta($cid);

    $memberName = $usermeta->FirstName1.' '.$usermeta->LastName1;

    $tData = json_decode($row->data, true);


    if(empty($postdata))
    {

        if( (!isset($_POST['adminTransaction'])) && $tData['GuestName'] != $_POST['FirstName1']." ".$_POST['LastName1'] && $_POST['FirstName1'].' '.$_POST['LastName1'] != $memberName && (!isset($tData['GuestFeeAmount']) || (isset($tData['GuestFeeAmount']) && $tData['GuestFeeAmount'] <= 0)))
        {

            $_POST['fee'] = get_option('gpx_gf_amount');

            $tempcart = [
                'item'=>'guest',
                'user_id'=>$cid,
                'data'=>json_encode($_POST),
            ];

            $wpdb->insert('wp_temp_cart', $tempcart);

            $tempID = $wpdb->insert_id;
            $data = [
                'paymentrequired'=>true,
                'amount'=>$_POST['fee'],
                'type'=>'guest',
                'html'=>'<h5>You will be required to pay a guest fee of $'.$_POST['fee'].' to complete change.</h5><br /><br /> <span class="usw-button"><button class="dgt-btn add-fee-to-cart-direct" data-type="guest" data-fee="'.$_POST['fee'].'" data-tid="'.$tempID.'" data-cart="" data-skip="No">Add To Cart</button><br /><br />',
            ];

            if($cid != get_current_user_id())
            {
                $data['html'] .= '<button class="dgt-btn add-fee-to-cart-direct af-agent-skip" data-fee="'.$_POST['fee'].'" data-tid="'.$tempID.'" data-type="guest" data-cart="" data-skip="Yes">Waive Fee</button><br /><br />';
            }

        }
    }

    if(!isset($data) || (isset($_POST['transactionID'])))
    {

        $sf = Salesforce::getInstance();

        $sfDB = json_decode($row->sfData, true);

        if(isset($_POST['LastName1']))
        {
            $tData['GuestName'] = $_POST['FirstName1']." ".$_POST['LastName1'];
            $sfData['Guest_First_Name__c'] = $sfWeekData['Guest_First_Name__c'] = htmlentities($_POST['FirstName1']);
            $sfData['Guest_Last_Name__c'] = $sfWeekData['Guest_Last_Name__c'] = htmlentities($_POST['LastName1']);
        }
        if(isset($_POST['Email']))
        {
            $sfData['Guest_Email__c'] = $sfWeekData['Guest_Email__c'] = $tData['Email'] = $_POST['Email'];
        }
        if(isset($_POST['Phone']))
        {
            $sfData['Guest_Home_Phone__c'] = $sfWeekData['Guest_Phone__c'] = $tData['Phone'] = substr(preg_replace( '/[^0-9]/', '', $_POST['Phone']), 0, 18);
        }
        if(isset($_POST['Adults']))
        {
            $sfWeekData['of_Adults__c'] = $tData['Adults'] = $_POST['Adults'];
        }
        if(isset($_POST['Children']))
        {
            $sfWeekData['of_Children__c'] = $tData['Children'] = $_POST['Children'];
        }
        if(isset($_POST['Owner']) && !empty($_POST['Owner']))
        {
            $sfData['Trade_Partner__c'] = $tData['Owner'] = htmlentities($_POST['Owner']);
        }
        if(isset($_POST['fee']))
        {
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

        if(!isset($sfAdd[0]->id))
        {
            //add the error to the sf data
            $sfDB['error'] = $sfAdd;
            $key = 'updated_'.strtotime("now");

            $sfDB[$key] = [
                'by'=>get_current_user_id(),
                'data'=>$sfData,
            ];
        }

        $dbUpdate['data'] = json_encode($tData);

        $wpdb->update('wp_gpxTransactions', $dbUpdate, array('id'=>$transaction));

        $data['success'] = true;
        $data['cid'] = $cid;
        $data['message'] = 'Guest has been changed';
    }

    if(!empty($addtocart))
    {
        return $data;
    }
    else
    {
        wp_send_json($data);
    }
}
add_action('wp_ajax_gpx_reasign_guest_name', 'gpx_reasign_guest_name');
add_action('wp_ajax_nopriv_gpx_reasign_guest_name', 'gpx_reasign_guest_name');




/**
 *
 *
 *
 *
 */
function gpx_transactions_add()
{
    global $wpdb;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $eachTrans = explode(PHP_EOL, $_POST['transactions']);

    foreach($eachTrans as $trans)
    {
        $dets = explode(",", preg_replace('/\s+/', '', $trans));
        $memberNO = $dets[0];
        $weekID = $dets[1];

        //put the week on hold

    }


    $sql = $wpdb->prepare("SELECT data FROM wp_gpxTransactions WHERE id=%d",$transaction);
    $row = $wpdb->get_row($sql);
    $tData = (array) json_decode($row->data, true);

    $tData['GuestName'] = $name;

    $wpdb->update('wp_gpxTransactions', array('data'=>json_encode($tData)), array('id'=>$transaction));

    $data['success'] = true;

    wp_send_json($data);
}
add_action('wp_ajax_gpx_transactions_add', 'gpx_transactions_add');
add_action('wp_ajax_nopriv_gpx_transactions_add', 'gpx_transactions_add');


/**
 *
 *
 *
 *
 */
function gpx_credit_donation()
{
    global $wpdb;

    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);


    if(isset($_POST['Check_In_Date__c']))
    {
        //send the details to SF
        $sf = Salesforce::getInstance();


        $sfDepositData = [
            'Check_In_Date__c'=>date('Y-m-d', strtotime($_POST['Check_In_Date__c'])),
            'Account_Name__c'=>$_POST['Account_Name__c'],
            'GPX_Member__c'=>$cid,
            'Deposit_Date__c'=>date('Y-m-d'),
            'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;", $_POST['Resort_Name__c'])),
            'Resort_Unit_Week__c'=>$_POST['Resort_Unit_Week__c'],
            'GPX_Deposit_ID__c'=>$_POST['GPX_Deposit_ID__c'],
        ];

        if(!empty($_POST['Reservation__c']))
        {
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
    }
    else
    {
        $cid = $_GET['id'];
        $return = $gpx->get_deposit_form($cid);
    }

    wp_send_json($return);
}
add_action("wp_ajax_gpx_credit_donation", "gpx_credit_donation");



/**
 *
 *
 *
 *
 */
function gpx_extend_credit($postdata = '', $addtocart = '')
{
    global $wpdb;

    if(empty($postdata))
    {
        //insert into the temporary cart


        $cid = gpx_get_switch_user_cookie();

        $_POST['fee'] = get_option('gpx_extension_fee');

        $tempcart = [
            'item'=>'extension',
            'user_id'=>$cid,
            'data'=>json_encode($_POST),
        ];

        $wpdb->insert('wp_temp_cart', $tempcart);

        $tempID = $wpdb->insert_id;
        $return = [
            'paymentrequired'=>true,
            'amount'=>$_POST['fee'],
            'type'=>'extension',
            'html'=>'<h5>You will be required to pay a credit extension fee of $'.$_POST['fee'].' to complete this transaction.</h5><br /><br /> <span class="usw-button"><button class="dgt-btn add-fee-to-cart-direct" data-type="extension" data-fee="'.$_POST['fee'].'" data-tid="'.$tempID.'" data-cart="" data-skip="No">Add To Cart</button>'
        ];

        unset($_POST['id']);
    }
    else
    {
        $_POST = (array) $postdata;
    }

    if(!empty($_POST['id']) && !empty($_POST['newdate']))
    {
        $id = $_POST['id'];
        $newdate = date('m/d/Y', strtotime($_POST['newdate']));

        $sql = $wpdb->prepare("SELECT credit_expiration_date FROM wp_credit WHERE id=%s", $id);
        $row = $wpdb->get_row($sql);

        $moddata = [
            'type'=>'Credit Extension',
            'oldDate'=>$row->credit_expiration_date,
            'newDate'=>date('Y-m-d', strtotime($_POST['newdate'])),
        ];

        $mod = [
            'credit_id'=>$id,
            'recorded_by'=>get_current_user_id(),
            'data'=>json_encode($moddata),
        ];

        $wpdb->insert('wp_credit_modification', $mod);

        $modID = $wpdb->insert_id;

        $update = [
            'credit_expiration_date' => date("Y-m-d", strtotime($_POST['newdate'])),
            'extension_date' => date('Y-m-d'),
            'modification_id'=>$modID,
            'modified_date'=>date('Y-m-d'),
        ];


        $wpdb->update('wp_credit', $update, array('id'=>$id));


        /*
         * TODO: Test after functionality is confirmed
         */

        //send to SF
        $sf = Salesforce::getInstance();

        $sfDepositData = [
            'GPX_Deposit_ID__c'=>$id,
            'Credit_Extension_Date__c'=>date('Y-m-d'),
            'Expiration_Date__c'=>date('Y-m-d', strtotime($_POST['newdate'])),
        ];

        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';

        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfDepositData;
        $sfFields[0]->type = $sfType;

        $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);

        $msg = "Credit has been extended to ".$newdate;

        $return = array('success'=>true, 'message'=>$msg, 'date'=>$newdate, 'cid'=>$cid);
    }

    if(!empty($addtocart))
    {
        return $return;
    }
    else
    {
        wp_send_json($return);
    }
}
add_action("wp_ajax_gpx_extend_credit","gpx_extend_credit");

/**
 *
 *
 *
 *
 */
function gpx_load_deposit_form()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $html = $gpx->get_deposit_form();

    $return = array('html'=>$html);
    wp_send_json($return);
}
add_action("wp_ajax_gpx_load_deposit_form","gpx_load_deposit_form");
add_action("wp_ajax_nopriv_gpx_load_deposit_form", "gpx_load_deposit_form");



function gpx_import_test()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->transactionimport();

    wp_send_json($data);
}
add_action('wp_ajax_gpx_import_test', 'gpx_import_test');
add_action('wp_ajax_nopriv_gpx_import_test', 'gpx_import_test');


/**
 *
 *
 *
 *
 */
function gpx_trans_agent_fix()
{
    global $wpdb;


    $sql = "SELECT * FROM wp_gpxTransactions WHERE cartID <> '' and id > 8500";
    $toCheck = $wpdb->get_results($sql);
    $i = 0;
    foreach($toCheck as $dRow)
    {
        $djson = json_decode($dRow->data, true);
        $sql = $wpdb->prepare("SELECT * FROM wp_gpxMemberSearch WHERE sessionID=%s AND data LIKE %s", [$dRow->sessionID, '%view-%']);
        $views = $wpdb->get_results($sql);
        foreach($views as $v)
        {
            $jv = json_decode($v->data, true);
            foreach($jv as $pv)
            {
                foreach($pv as $pk=>$p)
                {
                    if(isset($pk) && $pk == 'search_by_id')
                    {
                        if(!empty($p))
                        {
                            if($djson['processedBy'] != $p)
                            {
                                $djson['processedBy'] = $p;
                                $wpdb->update('wp_gpxTransactions', array('data'=>json_encode($djson)), array('id'=>$dRow->id));
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

/**
 *
 *
 *
 *
 */
function update_gpx_tax_transaction_type()
{
    $tts = array('bonus', 'exchange');
    foreach($tts as $value)
    {
        $option = 'gpx_tax_transaction_'.$value;
        if(in_array($value, $_POST['ttType']))
        {
            update_option($option, '1');
        }
        else
        {
            update_option($option, '0');
        }
    }
    $return = array('success'=>true);
    wp_send_json($return);
}
add_action('wp_ajax_update_gpx_tax_transaction_type', 'update_gpx_tax_transaction_type');
add_action('wp_ajax_nopriv_update_gpx_tax_transaction_type', 'update_gpx_tax_transaction_type');



