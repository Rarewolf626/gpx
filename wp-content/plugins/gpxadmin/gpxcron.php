<?php
include('/var/www/html/wp-load.php');
define( 'HOMEDIR', '/var/www/html/');
define ('ROOTDIR', '/var/www/html/wp-content/plugins/gpxadmin');
define ('ROOTURI', '/wp-content/plugins/gpxadmin');

define( 'GPXADMIN_PLUGIN_DIR', ROOTDIR.'dashboard' );
define( 'GPXADMIN_API_DIR', ROOTDIR.'/api' );

define( 'GPXADMIN_PLUGIN_URI', ROOTURI.'/dashboard' );
define( 'GPXADMIN_API_URI', ROOTURI.'/api' );

$month = 1;
$year = '2018';
$country = "xxx";
$region = "3";

error_reporting(0);
@ini_set('display_errors', 0);

$cnt = count($argv);
for($i=1; $i < $cnt; $i++)
{
    parse_str($argv[$i], $params);
    extract($params);
}
parse_str($argv[1], $actionar);
$action = $actionar['action'];
if(isset($_GET['action']))
{
    $action = $_GET['action'];
}
if($action == 'get_bonus')
{
cron_get_bonus($country, $region, $month, $year);
}
if($action == 'get_add_bonus')
{
    cron_get_add_bonus($country, $region, $month, $year);
}
if($action == 'get_add_exchange')
{
    cron_get_add_exchange($country, $region, $month, $year);
}
if($action == 'cron_check_resort_table')
{
    cron_check_resort_table();
}
if($action == 'cron_check_custom_requests')
{
    cron_check_custom_requests();
}
if($action == 'cron_generate_custom_requests_reports')
{
    cron_generate_custom_requests_reports();
}
if($action == 'cron_generate_member_search_reports')
{
    cron_generate_member_search_reports();
}
if($action == 'cron_release_holds')
{
    cron_release_holds();
}
if($action == 'cron_import_owner_final')
{
    cron_import_owner_final();
}
if($action == 'cron_dae_transactions')
{
    cron_dae_transactions();
}
if($action == 'cron_rework_ids_r')
{
    cron_rework_ids_r();
}
if($action == 'cron_rework_ids')
{
    cron_rework_ids();
}
if($action == 'cron_import_credit') 
{
    cron_import_credit();
}
if($action == 'cron_import_transactions') 
{
    cron_import_transactions();
}
if($action == 'cron_import_transactions_two') 
{
    cron_import_transactions_two();
}
if($action == 'cron_gpx_owner_from_sf') 
{
    cron_gpx_owner_from_sf();
}
if($action == 'cron_inactive_coupons') 
{
    cron_inactive_coupons();
}
 
add_action('wp_ajax_cron_inactive_coupons', 'cron_inactive_coupons');

require_once GPXADMIN_PLUGIN_DIR.'/vendors/dompdf/lib/html5lib/Parser.php';
require_once GPXADMIN_PLUGIN_DIR.'/vendors/dompdf/lib/php-font-lib/src/FontLib/Autoloader.php';
require_once GPXADMIN_PLUGIN_DIR.'/vendors/dompdf/lib/php-svg-lib/src/autoload.php';
require_once GPXADMIN_PLUGIN_DIR.'/vendors/dompdf/src/Autoloader.php';
Dompdf\Autoloader::register();
                                
// reference the Dompdf namespace
use Dompdf\Dompdf;
   

function cron_import_transactions()
{
    global $wpdb;
    
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $where = 'imported=0';
    if(!empty($id))
    {
        $where = 'id='.$id;
    }
    $table='transactions_import';
    $tt = 'transaction1';
    if($_GET['table'] == 'two')
    {
        $table = 'transactions_import_two';
        $tt = 'transaction2';
    }
    $sql = "SELECT * FROM ".$table." WHERE ".$where." ORDER BY RAND() LIMIT 100";
    $rows = $wpdb->get_results($sql);
    
    foreach($rows as $row)
    {
        $wpdb->update($table, array('imported'=>2), array('id'=>$row->id));
        //was this one entered?
        //         $sql = "SELECT id FROM wp_gpxTransactions WHERE weekId='".$row->weekId."'";
        //         $isin = $wpdb->get_var($sql);
        //         if(!empty($isin))
            //         {
        //             continue;
        //         }
        //         echo '<pre>'.print_r($row, true).'</pre>';
        
        
        
        if($row->GuestName == '#N/A')
        {
            $exception = json_encode($row);
            $wpdb->insert("reimport_exceptions", array('type'=>$tt.' guest formula error', 'data'=>$exception));
            continue;
        }
        //         if(!empty($resort))
        
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
        if(array_key_exists($row->ResortName, $resortKeyOne))
        {
            $resortMissing = $resortKeyOne[$row->ResortName];
            if($resort == 'SKIP')
            {
                continue;
            }
        }
        if(array_key_exists($row->ResortName, $resortKeyTwo))
        {
            $resortMissing = $resortKeyTwo[$row->ResortName];
            if($resort == 'SKIP')
            {
                continue;
            }
        }
        if(!empty($resortMissing))
        {
            $sql = "SELECT id, resortID, ResortName FROM wp_resorts WHERE id='".$resortMissing."'";
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
            
            $sql = "SELECT id, resortID, ResortName FROM wp_resorts WHERE id='".$resort_ID."'";
            $resort = $wpdb->get_row($sql);
            $resortName = $resort->ResortName;
            
            if(empty($resort))
            {
                $exception = json_encode($row);
                $wpdb->insert("reimport_exceptions", array('type'=>$tt.' resort', 'data'=>$exception));
                continue;
            }
        }
        else
        {
            $resortID = $resort->id;
            $daeResortID = $resort->resortID;
        }
        
        $user = get_users(array(
            'meta_key' => 'GPX_Member_VEST__c',
            'meta_value' => $row->MemberNumber
        ));
        
        $sql = "SELECT user_id FROM wp_GPR_Owner_ID__c WHERE user_id='".$row->MemberNumber."'";
        $user = $wpdb->get_var($sql);
        
        if(empty($user))
        {
            $exception = json_encode($row);
            $wpdb->insert("reimport_exceptions", array('type'=>$tt.' user', 'data'=>$exception));
            continue;
        }
        else
        {
            $userID = $user;
            
            $sql = "SELECT name FROM wp_partner WHERE user_id='".$userID."'";
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
                    $wpdb->insert("reimport_exceptions", array('type'=>$tt.' member name', 'data'=>$exception));
                    continue;
                }
            }
        }
        
        $unitType = $row->Unit_Type;
        $sql = "SELECT record_id FROM wp_unit_type WHERE resort_id='".$resortID."' AND name='".$unitType."'";
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
        
        $sql = "SELECT record_id FROM wp_room WHERE record_id='".$row->weekId."'";
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
        $sql = "SELECT id FROM wp_gpxTransactions WHERE weekId='".$row->weekId."' AND userID='".$userID."'";
        $et = $wpdb->get_var($sql);
        if(!empty($et))
        {
            $wpdb->update('wp_gpxTransactions', $wp_gpxTransactions, array('id'=>$et));
            $transactionID = $et;
        }
        else
        {
            $sql = "SELECT id FROM wp_gpxTransactions WHERE weekId='".$row->weekId."'";
            $enut = $wpdb->get_var($sql);
            if(empty($enut))
            {
                $wpdb->insert('wp_gpxTransactions', $wp_gpxTransactions);
                $transactionID = $wpdb->insert_id;
            }
            else
            {
                $exception = json_encode($row);
                $wpdb->insert("reimport_exceptions", array('type'=>$tt.' duplicate week transaction not cancelled', 'data'=>$exception));
                continue;
            }
        }
        if(isset($transactionID) && !empty($transactionID))
        {
            $d = $gpx->transactiontosf($transactionID);
        }
    }
    $sql = "SELECT COUNT(id) as cnt FROM ".$table." WHERE imported=0";
    $remain = $wpdb->get_var($sql);
    if($remain > 0 && empty($id))
    {
        echo '<script>location.reload();</script>';
        exit;
    }
    
    wp_send_json(array('remaining'=>$remain));
    wp_die();
    return true;
}

function cron_import_transactions_two()
{
    global $wpdb;
    
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $where = 'imported=0';
    if(!empty($id))
    {
        $where = 'id='.$id;
    }
//     $table='transactions_import';
//     $tt = 'transaction1';
    $table = 'transactions_import_two';
    $tt = 'transaction2';
    $sql = "SELECT * FROM ".$table." WHERE ".$where." ORDER BY RAND() LIMIT 100";
    $rows = $wpdb->get_results($sql);
    
    foreach($rows as $row)
    {
        $wpdb->update($table, array('imported'=>2), array('id'=>$row->id));
        //was this one entered?
        //         $sql = "SELECT id FROM wp_gpxTransactions WHERE weekId='".$row->weekId."'";
        //         $isin = $wpdb->get_var($sql);
        //         if(!empty($isin))
            //         {
        //             continue;
        //         }
        //         echo '<pre>'.print_r($row, true).'</pre>';
        
        
        
        if($row->GuestName == '#N/A')
        {
            $exception = json_encode($row);
            $wpdb->insert("reimport_exceptions", array('type'=>$tt.' guest formula error', 'data'=>$exception));
            continue;
        }
        //         if(!empty($resort))
        
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
        if(array_key_exists($row->ResortName, $resortKeyOne))
        {
            $resortMissing = $resortKeyOne[$row->ResortName];
            if($resort == 'SKIP')
            {
                continue;
            }
        }
        if(array_key_exists($row->ResortName, $resortKeyTwo))
        {
            $resortMissing = $resortKeyTwo[$row->ResortName];
            if($resort == 'SKIP')
            {
                continue;
            }
        }
        if(!empty($resortMissing))
        {
            $sql = "SELECT id, resortID, ResortName FROM wp_resorts WHERE id='".$resortMissing."'";
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
            
            $sql = "SELECT id, resortID, ResortName FROM wp_resorts WHERE id='".$resort_ID."'";
            $resort = $wpdb->get_row($sql);
            $resortName = $resort->ResortName;
            
            if(empty($resort))
            {
                $exception = json_encode($row);
                $wpdb->insert("reimport_exceptions", array('type'=>$tt.' resort', 'data'=>$exception));
                continue;
            }
        }
        else
        {
            $resortID = $resort->id;
            $daeResortID = $resort->resortID;
        }
        
        $user = get_users(array(
            'meta_key' => 'GPX_Member_VEST__c',
            'meta_value' => $row->MemberNumber
        ));
        
        $sql = "SELECT user_id FROM wp_GPR_Owner_ID__c WHERE user_id='".$row->MemberNumber."'";
        $user = $wpdb->get_var($sql);
        
        if(empty($user))
        {
            $exception = json_encode($row);
            $wpdb->insert("reimport_exceptions", array('type'=>$tt.' user', 'data'=>$exception));
            continue;
        }
        else
        {
            $userID = $user;
            
            $sql = "SELECT name FROM wp_partner WHERE user_id='".$userID."'";
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
                    $wpdb->insert("reimport_exceptions", array('type'=>$tt.' member name', 'data'=>$exception));
                    continue;
                }
            }
        }
        
        $unitType = $row->Unit_Type;
        $sql = "SELECT record_id FROM wp_unit_type WHERE resort_id='".$resortID."' AND name='".$unitType."'";
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
        
        $sql = "SELECT record_id FROM wp_room WHERE record_id='".$row->weekId."'";
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
        $sql = "SELECT id FROM wp_gpxTransactions WHERE weekId='".$row->weekId."' AND userID='".$userID."'";
        $et = $wpdb->get_var($sql);
        if(!empty($et))
        {
            $wpdb->update('wp_gpxTransactions', $wp_gpxTransactions, array('id'=>$et));
            $transactionID = $et;
        }
        else
        {
            $sql = "SELECT id FROM wp_gpxTransactions WHERE weekId='".$row->weekId."'";
            $enut = $wpdb->get_var($sql);
            if(empty($enut))
            {
                $wpdb->insert('wp_gpxTransactions', $wp_gpxTransactions);
                $transactionID = $wpdb->insert_id;
            }
            else
            {
                $exception = json_encode($row);
                $wpdb->insert("reimport_exceptions", array('type'=>$tt.' duplicate week transaction not cancelled', 'data'=>$exception));
                continue;
            }
        }
        if(isset($transactionID) && !empty($transactionID))
        {
            $d = $gpx->transactiontosf($transactionID);
        }
    }
    $sql = "SELECT COUNT(id) as cnt FROM ".$table." WHERE imported=0";
    $remain = $wpdb->get_var($sql);
    if($remain > 0 && empty($id))
    {
        echo '<script>location.reload();</script>';
        exit;
    }
    
    wp_send_json(array('remaining'=>$remain));
    wp_die();
    return true;
}

function cron_import_owner_final()
{
    global $wpdb;
    //     require_once GPXADMIN_API_DIR.'/functions/class.restsaleforce.php';
    //     $gpxRest = new RestSalesforce();
    
    //     require_once GPXADMIN_API_DIR.'/functions/class.salesforce.php';
    //     $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
    $sf = Salesforce::getInstance();
    
    //     $queryDays = '2';
    
    $selects = [
        'CreatedDate'=>'CreatedDate',
        'DAEMemberNo'=>'Name',
        //         'GPX_Member_No__c'=>'GPX_Member_No__c',
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
    
    //20 at a time
    //     $sql = "SELECT min(last_date) as md FROM owner_import order by id desc";
    //     $md = $wpdb->get_var($sql);
    
    //     $nextDate = date('Y-m-d', strtotime($md.'-1 day'));
    
    //     if(strtotime($nextDate) < strtotime($minDate))
        //     {
        // //         exit;
        //     }
            
        //     $wpdb->insert('owner_import', array('last_date'=>$nextDate));
        
        $sql = "SELECT id, dae FROM final_owner_import WHERE imported=0 ORDER BY RAND() LIMIT 500";
        $allOwners = $wpdb->get_results($sql);
        
        foreach($allOwners as $ao)
        {
            $oids[] = $ao->dae;
        }
        
        /*
         * @TODO: check exclude developer/hoa from query
         */
        //     $query = "SELECT ".implode(",", $sels)."  FROM GPR_Owner_ID__c where CreatedDate <= 2020-07-01T00:00:00Z AND HOA_Developer__c = false  ORDER BY CreatedDate desc";
        //     $query = "SELECT ".implode(",", $sels)."  FROM GPR_Owner_ID__c where
        //                     SystemModStamp >= LAST_N_DAYS:".$queryDays."
        //                             AND HOA_Developer__c = false
        //                 ORDER BY CreatedDate desc;
        
        
        //     $query = "SELECT ".implode(",", $sels)."  FROM GPR_Owner_ID__c
        //                 where  HOA_Developer__c = false
        //                 AND CreatedDate <= ".$ld."T23:59:59Z AND GPX_Member_VEST__c != ''
        //                 ORDER BY CreatedDate desc
        //                 LIMIT ".$limit."
        //                 OFFSET ".$offset;
        $query = "SELECT ".implode(",", $sels)."  FROM GPR_Owner_ID__c
                where  GPX_Member_VEST__c IN ('".implode("','", $oids)."')";
        //     $query = "SELECT ".implode(",", $sels)."  FROM GPR_Owner_ID__c
        //                 where  HOA_Developer__c = false
        //                 AND CreatedDate > ".$nextDate."T00:00:00Z
        //                 AND CreatedDate < ".$nextDate."T23:59:59Z AND GPX_Member_VEST__c != ''";
        //     $query = "SELECT ".implode(",", $sels)."  FROM GPR_Owner_ID__c where Name = '10000' ORDER BY CreatedDate desc";
        
        $results = $sf->query($query);
        
        //     $results =  $gpxRest->httpGet($query);
        $selects['Email'] = 'SPI_Email__c';
        $selects['Email1'] = 'SPI_Email__c';
        
        $testaccs = [
            'G112220440',
            'G112220435',
            'G112220432',
            'G112220427',
            'G112220439',
        ];
        
        if(empty($results))
        {
            exit;
        }
        
        foreach ($results as $result)
        {
            $value = $result->fields;
            $ocd = explode("T", $value->CreatedDate);
            
            $fq = false;
            $cd = $value->CreatedDate;
            $lo++;
            
            //         if(in_array($value->Name, $testaccs))
                //         {
                //             $value->SPI_Email__c = $value->SPI_Email__c.".test";
                    
                //             $sql = "SELECT user_id FROM wp_GPR_Owner_ID__c WHERE Name='".$value->Name."'";
                //             $ru = $wpdb->get_var($sql);
                    
                //             $wpdb->delete('wp_GPR_Owner_ID__c', array('user_id'=>$ru));
                //             $wpdb->delete('wp_owner_interval', array('userID'=>$ru));
                //             $wpdb->delete('wp_mapuser2oid', array('gpx_user_id'=>$ru));
                //         }
                
                if(empty($value->GPX_Member_VEST__c))
                {
                    continue;
                }
                
                $wpdb->update('final_owner_import', array('imported'=>1), array('dae'=>$value->GPX_Member_VEST__c));
                
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
                    //             'Year_Last_Banked__c',
                    'ROID_Key_Full__c',
                ];
                
                //update the ownership intervals
                $query2 = "SELECT ".implode(", ", $selects2)."
                    FROM Ownership_Interval__c
                       WHERE Owner_ID__c='".$value->Name."'";
                
                $results2 =  $sf->query($query2);
                
                if(empty($results2))
                {
                    continue;
                }
                //         if(strtotime($value->CreatedDate) < strtotime('-7 months'))
                    //         {
                    //             continue;
                    //         }
                    //         if(empty($value->GPX_Member_No__c))
                        //         {
                        //             if($value->Name != '100020003001')
                            //             {
                            //                 continue;
                            //             }
                                $user = '';
                                
                                $user = reset(
                                    get_users(
                                        array(
                                            'meta_key' => 'DAEMemberNo',
                                            'meta_value' => $value->GPX_Member_VEST__c,
                                        )
                                        )
                                    );
                                if(empty($user))
                                {
                                    
                                    //                 $user = get_user_by('email', $value->SPI_Email__c);
                                }
                                //             echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                                //             exit;
                                if(!empty($user))
                                {
                                    $value->GPX_Member_No__c = $user->ID;
                                    $user_id = $user->ID;
                                    
                                    
                                }
                                else
                                {
                                    
                                    $user_id = wp_create_user( $value->SPI_Email__c, wp_generate_password(), $value->SPI_Email__c );
                                    
                                    //                 $to = 'chris@4eightyeast.com';
                                    //                 $subject = 'Cron updated wp_GPR_Owner_ID__c';
                                    //                 $body = 'New Owners Added';
                                    //                 $headers = array('Content-Type: text/html; charset=UTF-8');
                                    
                                    //                 wp_mail( $to, $subject, $body, $headers );
                                }
                                
                                $userdets = [
                                    'ID'=>$user_id,
                                    'first_name'=>$value->SPI_First_Name__c,
                                    'last_name'=>$value->SPI_Last_Name__c,
                                ];
                                $up = wp_update_user($userdets);
                                update_user_meta($user_id, 'first_name', $value->SPI_First_Name__c);
                                update_user_meta($user_id, 'last_name', $value->SPI_Last_Name__c);
                                
                                $userrole = new WP_User( $user_id );
                                
                                //             $userrole->set_role( 'gpx_member' );
                                $userrole->set_role('gpx_member');
                                
                                foreach($selects as $sk=>$sv)
                                {
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
                                
                                //             foreach($sels as $selK=>$selV)
                                    //             {
                                    //                 if($selK == 'GP_Preferred')
                                        //                 {
                                        //                     if($value->$selV == "true")
                                            //                     {
                                            //                         $value->$selV = "Yes";
                                            //                     }
                                            //                     if($value->$selV == "false")
                                                //                     {
                                                //                         $value->$selV = "No";
                                                //                     }
                                                //                 }
                                                // //                 if($user_id == 84415)
                                                    // //                 {
                                                    // // echo '<pre>'.print_r($selK." ".$value->$selV, true).'</pre>';
                                                    // //                 }
                                                
                                                    //                $um = update_user_meta($user_id, $selK,  $value->$selV);
                                                    //             }
                                //             foreach($selects as $sk=>$sv)
                                    //             {
                                    //                 $um = update_user_meta($user_id, $sk,  $value->$sv);
                                    //                 echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                                    //             }
                                //         }
                    
                                $sql = "SELECT * FROM wp_GPR_Owner_ID__c WHERE Name LIKE '".$value->Name."'";
                                $check_if_exist = $wpdb->get_results($sql);
                                
                                if(count($check_if_exist) <= 0){
                                    $fullname = $value->SPI_First_Name__c." ".$value->SPI_Last_Name__c;
                                    $wpdb->insert('wp_GPR_Owner_ID__c', array('Name'=>$value->Name, 'user_id'=>$user_id, 'SPI_Owner_Name_1st__c'=>$fullname, 'SPI_Email__c'=> $value->SPI_Email__c, 'SPI_Home_Phone__c'=> $value->SPI_Home_Phone__c, 'SPI_Work_Phone__c'=> $value->SPI_Work_Phone__c, 'SPI_Street__c'=> $value->SPI_Street__c, 'SPI_City__c'=> $value->SPI_City__c, 'SPI_State__c'=> $value->SPI_State__c, 'SPI_Zip_Code__c'=> $value->SPI_Zip_Code__c, 'SPI_Country__c'=> $value->SPI_Country__c));
                                    
                                    //does this user have an id?
                                    
                                }
                                else
                                {
                                    $fullname = $value->SPI_First_Name__c." ".$value->SPI_Last_Name__c;
                                    $result = $wpdb->update('wp_GPR_Owner_ID__c',
                                        array('user_id'=>$user_id, 'SPI_Owner_Name_1st__c'=>$fullname, 'SPI_Email__c'=> $value->SPI_Email__c, 'SPI_Home_Phone__c'=> $value->SPI_Home_Phone__c, 'SPI_Work_Phone__c'=> $value->SPI_Work_Phone__c, 'SPI_Street__c'=> $value->SPI_Street__c, 'SPI_City__c'=> $value->SPI_City__c, 'SPI_State__c'=> $value->SPI_State__c, 'SPI_Zip_Code__c'=> $value->SPI_Zip_Code__c, 'SPI_Country__c'=> $value->SPI_Country__c),
                                        
                                        array("Name" => $check_if_exist[0]->Name));
                                    
                                }
                                //         if(isset($newuser))
                                    //         {
                                    //             $sfOwnerData['GPX_Member_VEST__c'] = $user_id;
                                    //             $sfOwnerData['Name'] = $value->Name;
                                        
                                    //             $sfType = 'GPR_Owner_ID__c';
                                    //             $sfObject = 'Name';
                                    //             $sfFields = [];
                                    //             $sfFields[0] = new SObject();
                                    //             $sfFields[0]->fields = $sfOwnerData;
                                    //             $sfFields[0]->type = $sfType;
                                    //             $sfAdd = $sf->gpxUpsert($sfObject, $sfFields);
                                    //         }
                    
                    
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
                                        
                                        $sql = "SELECT id FROM wp_owner_interval WHERE RIOD_Key_Full='".$r2->ROID_Key_Full__c."'";
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
                                        $sql = "SELECT id FROM wp_resorts WHERE gprID='".$r2->GPR_Resort__c."'";
                                        $row = $wpdb->get_row($sql);
                                        
                                        if(empty($row))
                                        {
                                            //can we update this resort?
                                            $selects = [
                                                'Name',
                                                //                     'GPX_Resort_ID__c'
                                            ];
                                            
                                            $resortQ = "SELECT ".implode(", ", $selects)."
                    FROM Resort__c
                       WHERE ID='".$interval['resortID']."'";
                                            $resortResults = $sf->query($resortQ);
                                            
                                            foreach($resortResults as $rr)
                                            {
                                                $resort = $rr->fields;
                                                $resortName = $resort->Name;
                                                
                                                $rsql = "SELECT id FROM wp_resorts WHERE ResortName LIKE '".$resortName."'";
                                                $rRow = $wpdb->get_var($id);
                                                
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
                                        $sql = "SELECT id FROM wp_mapuser2oid WHERE RIOD_Key_Full='".$r2->ROID_Key_Full__c."'";
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
}
//      if(isset($resortNotAvailable))
    //      {
    //          $to = [
    //              'chris@4eightyeast.com',
    //              'tscott@gpresorts.com'
    //          ];
    //          $message = 'The following resorts were not found in the GPX database: '.impolode(", ".$resortNotAvailable);
    //          echo '<pre>'.print_r($message, true).'</pre>';
    // //          wp_mail($to, 'Resorts Not Found', $message);
    //      }

// $sql = "SELECT count(id) as cnt FROM final_owner_import WHERE imported=0";
// $remain = $wpdb->get_var($sql);

// if($remain > 0)
// {
    
    
//     echo '<script>location.reload();</script>';
//     exit;
// }

wp_send_json(array('remaining'=>$remain));
wp_die();
}

function cron_rework_ids_r()
{
    global $wpdb;
    
    $limit = 10;
    
    $sql = "SELECT last_offset FROM owner_rework_r ORDER BY id desc LIMIT 1";
    $offset = $wpdb->get_var($sql);
    
    $wpdb->update('owner_rework_r', array('last_offset'=>$offset+$limit), array('last_offset'=>$offset));
    
    $sql = "SELECT ID, user_login FROM
            `wp_users`
            WHERE `user_login` LIKE 'U%' ORDER BY ID DESC LIMIT ".$limit." OFFSET ".$offset;
    $users = $wpdb->get_results($sql);
    
    foreach($users as $user)
    {
        //does this user exist?
        
        $ou = $user->ID;
        $nu = str_replace("U", "", $user->user_login);
        
        $sql = "SELECT ID FROM wp_users WHERE ID=".$nu;
        $isu = $wpdb->get_row($sql);
        
        if(!empty($isu))
        {
            $oou = $ou;
            $onu = $nu;
            //we need to reasign this user to a much higher number
            $nu = '9999'.$nu;
            $ou = $isu->ID;
            
            //adjust the transactions
            $wpdb->update('wp_gpxTransactions', array('userID'=>$nu), array('userID'=>$ou));
            $wpdb->update('wp_gpxPreHold', array('user'=>$nu), array('user'=>$ou));
            $wpdb->update('wp_cart', array('user'=>$nu), array('user'=>$ou));
            $wpdb->update('wp_credit', array('owner_id'=>$nu), array('owner_id'=>$ou));
            $wpdb->update('wp_GPR_Owner_ID__c', array('user_id'=>$nu), array('user_id'=>$ou));
            $wpdb->update('wp_gpxAutoCoupon', array('user_id'=>$nu), array('user_id'=>$ou));
            $wpdb->update('wp_gpxMemberSearch', array('userID'=>$nu), array('userID'=>$ou));
            $wpdb->update('wp_mapuser2oid', array('gpx_user_id'=>$nu), array('gpx_user_id'=>$ou));
            $wpdb->update('wp_owner_interval', array('userID'=>$nu), array('userID'=>$ou));
            $wpdb->update('wp_partner', array('user_id'=>$nu), array('user_id'=>$ou));
            $wpdb->update('wp_users', array('ID'=>$nu), array('ID'=>$ou));
            $wpdb->update('wp_usermeta', array('user_id'=>$nu), array('user_id'=>$ou));
            
            $nu = $onu;
            $ou = $oou;
        }
        
        //adjust the transactions
        $wpdb->update('wp_gpxTransactions', array('userID'=>$nu), array('userID'=>$ou));
        $wpdb->update('wp_gpxPreHold', array('user'=>$nu), array('user'=>$ou));
        $wpdb->update('wp_cart', array('user'=>$nu), array('user'=>$ou));
        $wpdb->update('wp_credit', array('owner_id'=>$nu), array('owner_id'=>$ou));
        $wpdb->update('wp_GPR_Owner_ID__c', array('user_id'=>$nu), array('user_id'=>$ou));
        $wpdb->update('wp_gpxAutoCoupon', array('user_id'=>$nu), array('user_id'=>$ou));
        $wpdb->update('wp_gpxMemberSearch', array('userID'=>$nu), array('userID'=>$ou));
        $wpdb->update('wp_mapuser2oid', array('gpx_user_id'=>$nu), array('gpx_user_id'=>$ou));
        $wpdb->update('wp_owner_interval', array('userID'=>$nu), array('userID'=>$ou));
        $wpdb->update('wp_partner', array('user_id'=>$nu), array('user_id'=>$ou));
        $wpdb->update('wp_users', array('ID'=>$nu), array('ID'=>$ou));
        $wpdb->update('wp_usermeta', array('user_id'=>$nu), array('user_id'=>$ou));
    }
    
    $sql = "SELECT count(ID) as cnt FROM
            `wp_users`
            WHERE `user_login` LIKE 'U%'";
    $tcnt = $wpdb->get_var($sql);
    
    $of = $offset+$limit;
    if($of < $tcnt)
    {
        echo '<script>location.reload();</script>';
        exit;
    }
    
    wp_send_json(array('remaining'=>$tcnt));
    wp_die();
}

function cron_rework_ids()
{
    global $wpdb;
    
    $limit = 500;
    
    $sql = "SELECT count(id) as cnt FROM owner_rework_owners WHERE imported=0";
    $tcnt = $wpdb->get_var($sql);
    
    if($tcnt = 0)
    {
        exit;
    }
    
    $sql = "SELECT id, old_owner_id, new_owner_id FROM owner_rework_owners WHERE imported=0 ORDER BY RAND() LIMIT ".$limit;
    $users = $wpdb->get_results($sql);
    
    foreach($users as $user)
    {
        $wpdb->update('owner_rework_owners', array('imported'=>2), array('id'=>$user->id));
        //does this user exist?
        
        $ou = $user->old_owner_id;
        $nu = $user->new_owner_id;
        
        $sql = "SELECT ID FROM wp_users WHERE ID=".$nu;
        $isu = $wpdb->get_row($sql);
        
        if(!empty($isu))
        {
            $oou = $ou;
            $onu = $nu;
            //we need to reasign this user to a much higher number
            $nu = '9999'.$nu;
            $ou = $isu->ID;
            
            //adjust the transactions
            $wpdb->update('wp_gpxTransactions', array('userID'=>$nu), array('userID'=>$ou));
            $wpdb->update('wp_gpxPreHold', array('user'=>$nu), array('user'=>$ou));
            $wpdb->update('wp_cart', array('user'=>$nu), array('user'=>$ou));
            $wpdb->update('wp_credit', array('owner_id'=>$nu), array('owner_id'=>$ou));
            $wpdb->update('wp_GPR_Owner_ID__c', array('user_id'=>$nu), array('user_id'=>$ou));
            $wpdb->update('wp_gpxAutoCoupon', array('user_id'=>$nu), array('user_id'=>$ou));
//             $wpdb->update('wp_gpxMemberSearch', array('userID'=>$nu), array('userID'=>$ou));
            $wpdb->update('wp_mapuser2oid', array('gpx_user_id'=>$nu), array('gpx_user_id'=>$ou));
            $wpdb->update('wp_owner_interval', array('userID'=>$nu), array('userID'=>$ou));
            $wpdb->update('wp_partner', array('user_id'=>$nu), array('user_id'=>$ou));
            $wpdb->update('wp_users', array('ID'=>$nu), array('ID'=>$ou));
            $wpdb->update('wp_usermeta', array('user_id'=>$nu), array('user_id'=>$ou));
            
            $nu = $onu;
            $ou = $oou;
        }
        
        //adjust the transactions
        $wpdb->update('wp_gpxTransactions', array('userID'=>$nu), array('userID'=>$ou));
        $wpdb->update('wp_gpxPreHold', array('user'=>$nu), array('user'=>$ou));
        $wpdb->update('wp_cart', array('user'=>$nu), array('user'=>$ou));
        $wpdb->update('wp_credit', array('owner_id'=>$nu), array('owner_id'=>$ou));
        $wpdb->update('wp_GPR_Owner_ID__c', array('user_id'=>$nu), array('user_id'=>$ou));
        $wpdb->update('wp_gpxAutoCoupon', array('user_id'=>$nu), array('user_id'=>$ou));
//         $wpdb->update('wp_gpxMemberSearch', array('userID'=>$nu), array('userID'=>$ou));
        $wpdb->update('wp_mapuser2oid', array('gpx_user_id'=>$nu), array('gpx_user_id'=>$ou));
        $wpdb->update('wp_owner_interval', array('userID'=>$nu), array('userID'=>$ou));
        $wpdb->update('wp_partner', array('user_id'=>$nu), array('user_id'=>$ou));
        $wpdb->update('wp_users', array('ID'=>$nu), array('ID'=>$ou));
        $wpdb->update('wp_usermeta', array('user_id'=>$nu), array('user_id'=>$ou));
    }

}

function cron_dae_transactions()
{
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $gpx->transactionimport();
    
    return true;
}

function cron_gpx_owner_from_sf()
{
    global $wpdb;
    
    require_once ROOTDIR.'/gpxadmin.php';
    
    function_GPX_Owner();
}

function cron_inactive_coupons()
{
    global $wpdb;
    
    $sql = "SELECT id FROM  wp_gpxOwnerCreditCoupon WHERE expirationDate < '".date('Y-m-d')."' AND active=1";
    $results = $wpdb->get_results($sql);
    echo '<pre>'.print_r($results, true).'</pre>';
    foreach($results as $row)
    {
        $wpdb->update('wp_gpxOwnerCreditCoupon', array('active'=>0), array('id'=>$row->id));
        echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
    }
    
    return true;
}
function cron_import_credit()
{
    require_once ROOTDIR.'/gpxadmin.php';
    
    hook_credit_import();
}

function cron_release_holds()
{
    require_once ROOTDIR.'/gpxadmin.php';
    
    test_cron_release_holds();
}
function cron_get_bonus($country, $region, $month, $year)
{
    
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    //     for($i=3;$i<6;$i++)
    //     {
    $inputMembers = array(
        'DAEMemberNo'=>true,
        'DAEMemberNo'=>true,
        'CountryID'=>$country,
        'RegionID'=>$region,
        'Month'=>$month,
        'Year'=>$year,
        'WeeksToShow'=>'ALL',
        'Sort'=>'Default',
    );
    $data = $gpx->DAEGetBonusRentalAvailability($inputMembers);
    echo '<pre>'.print_r($data, true).'</pre>';
    //     }
    
}
function cron_get_add_bonus($country, $region, $month, $year)
{
    
    global $wpdb;
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpxapi = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $starttime = microtime(true);
    
    //         if(isset($_GET['country']))
    //             $dateFrom = date('Y-m-d h:i:s', strtotime('-1 hour'));
    //         else
    //             $dateFrom = date('Y-m-d h:i:s', strtotime('-1 day'));
    //         echo '<pre>'.print_r($dateFrom, true).'</pre>';
    $date = date('Y-m-d H:i:s', strtotime('-7 hours'));
    $dateMinus = strtotime($date) - 82800;
    
    $dateFrom = date('Y-m-d H:i:s',$dateMinus);
    
    if(isset($country) && $country != 'xxx')
    {
        $countries[] = $country;
        $regions[$country][] = '?';
    }
    else
    {
        $sql = "SELECT DISTINCT CountryID FROM wp_daeCountry WHERE (CountryID <> '14' AND CountryID <> '26' AND CountryID <> '7') AND active=1";
        $allCountries = $wpdb->get_results($sql);
        foreach($allCountries as $oneCountry)
        {
            $countries[] = $oneCountry->CountryID;
            $regions[$oneCountry->CountryID][] = '?';
        }
    }
    
    $allRegionsNA = [
        '4',
        '5',
        '6',
        '13',
        '23',
        '25',
        '14',
    ];
    
    // europe cannot be all regions
    foreach($allRegionsNA as $ana)
    {
        if(in_array($ana, $countries))
        {
            unset($regions[$ana]);
            $sql = "SELECT DISTINCT RegionID FROM wp_daeRegion WHERE CountryID='".$ana."' AND active=1 and RegionID<>'?'";
            $allRegions = $wpdb->get_results($sql);
            foreach($allRegions as $oneRegion)
            {
                $regions[$ana][] = $oneRegion->RegionID;
                
            }
        }
    }
    foreach ($countries as $aCountry)
    {
        $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE RegionID IN (SELECT id FROM wp_daeRegion WHERE CountryID='".$aCountry."' and active=1)";
        $lrs = $wpdb->get_results($sql);
        $allLRs = [];
        foreach($lrs as $lr)
        {
            $allLRs[] = "c.lft BETWEEN ".$lr->lft." AND ".$lr->rght;
        }
        if(!empty($allLRs))
        {
            $sql = "SELECT a.id FROM wp_properties a
                                INNER JOIN wp_resorts  b on a.resortJoinID = b.id
                                INNER JOIN wp_gpxRegion c on b.gpxRegionID = c.id
                                WHERE (".implode(" OR ", $allLRs).")
                                AND a.active='1'
                                AND b.active='1'
                                AND a.WeekType IN ('RentalWeek', 'BonusWeek')";
            $toCheck = $wpdb->get_results($sql);
            foreach($toCheck as $tc)
            {
                $allActive[$tc->id] = $tc->id;
            }
            $allActiveCount = count($allActive);
            $session = strtotime('NOW');
            $wpdb->insert('wp_refresh_to_remove', array('session'=>$session, 'weeks_all'=>json_encode($allActive)));
            $dbActiveRefresh = $wpdb->insert_id;
            echo '<pre>'.print_r("dbactiverefresh: ".$dbActiveRefresh, true).'</pre>';
        }
    }
    echo '<pre>'.print_r($allActive, true).'</pre>';
    $pullDates[] = date('n/Y');
    
    for($i=0;$i<13;$i++)
    {
        $pullDates[] = date('n/Y', strtotime("+".$i." months", strtotime('first day of')));
    }
    $regionsDone = [];
    foreach($countries as $thisCountry)
    {
        $regionsDone = [];
        foreach($regions[$thisCountry] as $region)
        {
            sleep(5);
            if(in_array($region, $regionsDone))
            {
                continue;
            }
            $regionsDone[] = $region;
            foreach($pullDates as $pd)
            {
                $pds = explode("/", $pd);
                $pullyear = $pds[1];
                $pullmonth = $pds[0];
                $subtime = microtime(true);
                
                
                
                
                $subtimediff = $starttime - $subtime;
                echo '<pre>'.print_r($subtimediff, true).'</pre>';
                $inputMembers = array(
                    'DAEMemberNo'=>true,
                    'CountryID'=>$thisCountry,
                    'RegionID'=>$region,
                    'Month'=>$pullmonth,
                    'Year'=>$pullyear,
                    'WeeksToShow'=>'ALL',
                    'Sort'=>'Default',
                );
                if(isset($dbActiveRefresh))
                {
                    $inputMembers['dbActiveRefresh'] = $dbActiveRefresh;
                }
                echo '<pre>'.print_r($inputMembers, true).'</pre>';
                $data = $gpxapi->NewAddDAEGetBonusRentalAvailability($inputMembers);
                echo '<pre>'.print_r($data, true).'</pre>';
                
                //update the most recent pulls with info...
                
                $wpdb->insert('wp_daeRefresh', array('called'=>'bonus', 'country'=>$thisCountry, 'pulled'=>$pullmonth."/".$pullyear));
                if(isset($data['weeks_added']))
                {
                    $addedArr = $data['weeks_added'];
                    foreach($addedArr as $ar)
                    {
                        unset($allActive[$ar]);
                    }
                }
            }
        }
        if(isset($session) && isset($addedArr))
        {
            if(count($allActive) != $allActiveCount)
            {
                //now we have all the weeks that aren't active
                foreach($allActive as $aa)
                {
                    $wpdb->update('wp_properties', array('active'=>'0'), array('id'=>$aa));
                }
                echo '<pre>'.print_r("active: ", true).'</pre>';
                echo '<pre>'.print_r($allActive, true).'</pre>';
            }
        }
    }
    
}
function cron_get_add_exchange($country, $region, $month, $year)
{
    
    global $wpdb;
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpxapi = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $starttime = microtime(true);
    
    
    $date = date('Y-m-d H:i:s', strtotime('-7 hours'));
    $dateMinus = strtotime($date) - 82800;
    
    $dateFrom = date('Y-m-d H:i:s',$dateMinus);
    $minute = date('i');
    
    //     if(isset($country) && $country != 'xxx')
    //         $sql = "SELECT id, RegionID, CountryID FROM wp_daeRegion WHERE CountryID='".$country."' AND active=1 AND RegionID <> '?' ORDER BY exchange";
    //         else
    //             $sql = "SELECT id, RegionID, CountryID FROM wp_daeRegion WHERE (CountryID <> '14' OR CountryID <> '35' OR CountryID <> '30') AND active=1 AND exchange <= '".$dateFrom."' AND RegionID <> '?' ORDER BY exchange LIMIT 21";
    
    //             $pullRegions = $wpdb->get_results($sql);
    
    //             echo '<pre>'.print_r($sql, true).'</pre>';
    
    if(isset($country) && $country != 'xxx')
    {
        $countries[] = $country;
        $regions[$country][] = '?';
    }
    else
    {
        $sql = "SELECT DISTINCT CountryID FROM wp_daeCountry WHERE (CountryID <> '14' AND CountryID <> '26' AND CountryID <> '7') AND active=1";
        $allCountries = $wpdb->get_results($sql);
        foreach($allCountries as $oneCountry)
        {
            $countries[] = $oneCountry->CountryID;
            $regions[$oneCountry->CountryID][] = '?';
        }
    }
    echo '<pre>'.print_r($countries, true).'</pre>';
    echo '<pre>'.print_r($regions, true).'</pre>';
    $allRegionsNA = [
        '4',
        '5',
        '6',
        '13',
        '23',
        '25',
        '14',
    ];
    // europe cannot be all regions
    foreach($allRegionsNA as $ana)
    {
        if(in_array($ana, $countries))
        {
            unset($regions[$ana]);
            $sql = "SELECT DISTINCT RegionID FROM wp_daeRegion WHERE CountryID='".$ana."' AND active=1 and RegionID<>'?'";
            $allRegions = $wpdb->get_results($sql);
            foreach($allRegions as $oneRegion)
            {
                $regions[$ana][] = $oneRegion->RegionID;
                
            }
        }
    }
    foreach ($countries as $aCountry)
    {
        $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE RegionID IN (SELECT id FROM wp_daeRegion WHERE CountryID='".$aCountry."' and active=1)";
        $lrs = $wpdb->get_results($sql);
        $allLRs = [];
        foreach($lrs as $lr)
        {
            $allLRs[] = "c.lft BETWEEN ".$lr->lft." AND ".$lr->rght;
        }
        if(!empty($allLRs))
        {
            $sql = "SELECT a.id FROM wp_properties a
                                INNER JOIN wp_resorts  b on a.resortJoinID = b.id
                                INNER JOIN wp_gpxRegion c on b.gpxRegionID = c.id
                                WHERE (".implode(" OR ", $allLRs).")
                                AND a.active='1'
                                AND b.active='1'
                                AND a.WeekType='ExchangeWeek'";
            $toCheck = $wpdb->get_results($sql);
            echo '<pre>'.print_r($sql, true).'</pre>';
            foreach($toCheck as $tc)
            {
                $allActive[$tc->id] = $tc->id;
            }
            $allActiveCount = count($allActive);
            $session = strtotime('NOW');
            $wpdb->insert('wp_refresh_to_remove', array('session'=>$session, 'weeks_all'=>json_encode($allActive)));
            $dbActiveRefresh = $wpdb->insert_id;
        }
    }
    $pullDates[] = date('n/Y');
    $startDate = date('n');
    
    for($i=0;$i<13;$i++)
    {
        $pullDates[] = date('n/Y', strtotime("+".$i." months", strtotime('first day of')));
    }
    $regionsDone = [];
    foreach($countries as $thisCountry)
    {
        echo '<pre>'.print_r("country: ".$thisCountry, true).'</pre>';
        $regionsDone = [];
        foreach($regions[$thisCountry] as $region)
        {
            echo '<pre>'.print_r("this region: ".$region, true).'</pre>';
            sleep(5);
            if(in_array($region, $regionsDone))
            {
//                 continue;
            }
            $regionsDone[] = $region;
            foreach($pullDates as $pd)
            {
                //                 echo '<pre>'.print_r($pd, true).'</pre>';
                $pds = explode("/", $pd);
                $pullyear = $pds[1];
                $pullmonth = $pds[0];
                $subtime = microtime(true);
                $inputMembers = array(
                    'DAEMemberNo'=>true,
                    'CountryID'=>$thisCountry,
                    'RegionID'=>$region,
                    'Month'=>$pullmonth,
                    'Year'=>$pullyear,
                    'ShowSplitWeeks'=>True,
                );
                if(isset($dbActiveRefresh))
                {
                    $inputMembers['dbActiveRefresh'] = $dbActiveRefresh;
                }
                echo '<pre>'.print_r($inputMembers, true).'</pre>';
                $data = $gpxapi->NewAddDAEGetExchangeAvailability($inputMembers);
                echo '<pre>'.print_r($data, true).'</pre>';
                
                //update the most recent pulls with info...
                
                $wpdb->insert('wp_daeRefresh', array('called'=>'exchange', 'country'=>$thisCountry, 'pulled'=>$pullmonth."/".$pullyear));
                if(isset($data['weeks_added']))
                {
                    $addedArr = $data['weeks_added'];
                    foreach($addedArr as $ar)
                    {
                        unset($allActive[$ar]);
                    }
                }
            }
        }
        if(isset($session) && isset($addedArr))
        {
            if(count($allActive) != $allActiveCount)
            {
                //now we have all the weeks that aren't active
                foreach($allActive as $aa)
                {
                  $wpdb->update('wp_properties', array('active'=>'0'), array('id'=>$aa));
                }
                echo '<pre>'.print_r("active: ", true).'</pre>';
                echo '<pre>'.print_r($allActive, true).'</pre>';
            }
        }
    }
    
    
    $data = array('success'=>true);
}
function cron_check_resort_table()
{
    
    global $wpdb;
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpxapi = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $sql = "SELECT DISTINCT a.resortId, a.weekEndpointID  FROM wp_properties a WHERE a.resortId NOT IN (select ResortID FROM wp_resorts b)";
    
    $rows = $wpdb->get_results($sql);
    echo '<pre>'.print_r($rows, true).'</pre>';
    foreach($rows as $row)
    {
        $gpxapi->missingDAEGetResortProfile($row->resortId, $row->weekEndpointID);
    }
    
    //     if(date('N') == '7')
    //     {
    $sql = "SELECT id, ResortID, EndpointID, gpxRegionID FROM wp_resorts";
    $rows = $wpdb->get_results($sql);
    
    foreach($rows as $row)
    {
        $inputMembers = array(
            'ResortID'=>$row->ResortID,
            'EndpointID'=>$row->EndpointID,
        );
        
        $profile = $gpxapi->DAEGetResortProfile($row->id, $row->gpxRegionID, $inputMembers, '1');
    }
    //     }
    $data = array('success'=>true);
    }
function cron_check_custom_requests()
{
    global $wpdb;
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    //disable while testing today
//     $gpx->return_cron_check_custom_requests();
    
    $data = array('success'=>true);
}
function cron_generate_custom_requests_reports()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $html = $gpx->return_custom_request_report();
    
    // instantiate and use the dompdf class
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    
    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'portrait');
    
    // Render the HTML as PDF
    $dompdf->render();
    
    $output = $dompdf->output();
    
    $filename = str_replace('html/', '', HOMEDIR).'reports/Custom_Request_Report '.date('m_d_Y').'.pdf';
    
    file_put_contents($filename, $output);
    
    //send the message
    
    $subject = get_option('gpx_crreportsemailSubject');
    $message = get_option('gpx_crreportsemailMessage');
    $fromEmailName = get_option('gpx_crreportsemailName');
    $fromEmail = get_option('gpx_crreportsemailFrom');
    $toEmail = get_option('gpx_crreportsemailTo');
    
    $headers[]= "From: ".$fromEmailName." <".$fromEmail.">";
    $headers[] = "Content-Type: text/html; charset=UTF-8";
    
    $attachments = array($filename);
    
    wp_mail($toEmail, $subject, $message, $headers, $attachments);
    
}
function cron_generate_member_search_reports()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $days = get_option('gpx_msemailDays');
    if(empty($days))
    {
        $days = '18';
    }
    
    $filename = $gpx->get_csv_download('wp_gpxMemberSearch', 'data', $days);
    //send the message
    
    $subject = get_option('gpx_msemailSubject');
    $message = get_option('gpx_msemailMessage');
    $fromEmailName = get_option('gpx_msemailName');
    $fromEmail = get_option('gpx_msemail');
    $toEmail = get_option('gpx_msemailTo');
    
    $headers[]= "From: ".$fromEmailName." <".$fromEmail.">";
    $headers[] = "Content-Type: text/html; charset=UTF-8";
    
    $attachments = array($filename);
    
    wp_mail($toEmail, $subject, $message, $headers, $attachments);
    
}
                                        