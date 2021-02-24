<?php
/*

 * Plugin Name: GPX Admin
 * Plugin URI: http://www.4eightyeast.com
 * Version: 1.0
 * Description: GPX custom dashboard and functionality
 * Author: Chris Goering
 * Author URI: http://www.4eightyeast.com
 * License: GPLv2 or later
 */
require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.salesforce.php';

date_default_timezone_set('America/Los_Angeles');
if(isset($_REQUEST['debug_more']))
{
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
if(isset($_REQUEST['debug']))
{
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE & ~E_NOTICE & ~E_WARNING);
}

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


define( 'GPXADMIN_PLUGIN_DIR', trailingslashit( dirname(__FILE__) ).'dashboard' );
define( 'GPXADMIN_API_DIR', trailingslashit( dirname(__FILE__) ).'/api' );

define( 'GPXADMIN_PLUGIN_URI', plugins_url('', __FILE__).'/dashboard' );
define( 'GPXADMIN_API_URI', plugins_url('', __FILE__).'/api' );


//include scripts/styles
if( !is_admin() )
{

}
else
{
    function load_custom_wp_admin_style() {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
        wp_register_style('bootstrap_css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
        wp_enqueue_style('bootstrap_css');
        wp_enqueue_style('bootrap_table_css', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/bootstrap-table.min.css');
        wp_enqueue_style('bootrap_table_filter_css', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/extensions/filter-control/bootstrap-table-filter-control.min.css');
        wp_enqueue_style('fontawesome_css', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css');
        wp_enqueue_style('nprogress_css', GPXADMIN_PLUGIN_URI.'/vendors/nprogress/nprogress.css');
        wp_enqueue_style('prettify_css', GPXADMIN_PLUGIN_URI.'/vendors/google-code-prettify/bin/prettify.min.css');
        wp_enqueue_style('jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css');
        wp_enqueue_style('timepicker_css', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css');
        wp_enqueue_style('daterangepicker_css', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.27/daterangepicker.css');
        wp_enqueue_style('fontawesome_iconpicker_css', GPXADMIN_PLUGIN_URI.'/vendors/fontawesome-iconpicker/css/fontawesome-iconpicker.min.css');           
        wp_enqueue_style('gpx_admin_custom_css', GPXADMIN_PLUGIN_URI.'/build/css/custom.css', '', '2.01');
        wp_enqueue_style('bootrap_select_css', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css');
        wp_enqueue_style('bootrap_multiselect_css', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css');
        wp_register_script('jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js', array('jquery'));
        wp_enqueue_script('jquery_ui');
        wp_enqueue_script("jquery-ui-draggable");
        wp_enqueue_script("jquery-ui-sortable");
        wp_enqueue_script('timepicker_js', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js', array('jquery_ui'));
        wp_register_script('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js', array('jquery'));
        wp_enqueue_script('bootstrap');
        wp_register_script('bootstrap_table_js', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/bootstrap-table.min.js', array('bootstrap'));
        wp_enqueue_script('bootstrap_table_js');
        wp_enqueue_script('bootsrap_table_fc_js', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/extensions/filter-control/bootstrap-table-filter-control.min.js', array('bootstrap_table_js'));
        wp_enqueue_script('bootsrap_table_export_js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/extensions/export/bootstrap-table-export.min.js', array('bootstrap_table_js'));
        wp_enqueue_script('bootsrap_tableexport_js', '//rawgit.com/hhurz/tableExport.jquery.plugin/master/tableExport.js', array('bootstrap_table_js'));
        wp_enqueue_script('fastclick_jquery', GPXADMIN_PLUGIN_URI.'/vendors/fastclick/lib/fastclick.js', array('bootstrap'));
        wp_enqueue_script('bootstrap_select_jquery', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js', array('bootstrap'));
        wp_enqueue_script('bootstrap_multiselect_jquery', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js', array('bootstrap'));
        wp_enqueue_script('javascript_cookie', '//cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.3/js.cookie.min.js', array('bootstrap'));
        wp_register_script('moment_js', '//cdn.jsdelivr.net/momentjs/latest/moment.min.js', array('bootstrap'));
        wp_enqueue_script('moment_js');
        wp_enqueue_script('daterangepicker_js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.27/daterangepicker.js', array('moment_js'));
        wp_enqueue_script('fontawesome_iconpicker_js', GPXADMIN_PLUGIN_URI.'/vendors/fontawesome-iconpicker/js/fontawesome-iconpicker.min.js', array('bootstrap'));
        wp_enqueue_script('nprogress_jquery', GPXADMIN_PLUGIN_URI.'/vendors/nprogress/nprogress.js', array('bootstrap'));
        wp_enqueue_script('wysiwyg_jquery', GPXADMIN_PLUGIN_URI.'/vendors/bootstrap-wysiwyg/js/bootstrap-wysiwyg.min.js', array('bootstrap'));
        wp_enqueue_script('hotkeys_jquery', GPXADMIN_PLUGIN_URI.'/vendors/jquery.hotkeys/jquery.hotkeys.js', array('bootstrap'));
        wp_enqueue_script('prettify_jquery', GPXADMIN_PLUGIN_URI.'/vendors/google-code-prettify/src/prettify.js', array('bootstrap'));
        wp_enqueue_script('custom_jquery', GPXADMIN_PLUGIN_URI.'/build/js/custom.js', array('bootstrap'), '2.01');
    }
    if(isset($_GET['page']) && $_GET['page'] == 'gpx-admin-page')
      add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );


    add_action( 'admin_menu', 'gpx_admin_menu' );
    
    function gpx_admin_menu()
    {
        add_menu_page( 'GPX Admin Page', 'GPX Admin', 'gpx_admin', 'gpx-admin-page', 'gpx_admin_page', 'dashicons-tickets', 6  );
    }
    
    function gpx_admin_page()
    {
        require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
        $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
        $page = '';
        if(isset($_GET['gpx-pg']))
            $page = $_GET['gpx-pg'];
        echo $gpx->getpage($page, 'admin');

    }

    //if on the aerc admin page then fold the default menu
    if(isset($_GET['page']) && $_GET['page'] == 'gpx-admin-page')
    {
        /**
         * fold the default wordpress admin menu
         * @param unknown $classes
         * @return unknown
         */
        function dashboard_menu_folded( $classes ) {
            
            $classes .= 'folded';
            
            return $classes;
            
        }
        add_filter( 'admin_body_class','dashboard_menu_folded' );
    }
}
//wp ajax being used for cron api
function get_addRegions()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    $data = $gpx->addRegions();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_addRegions', 'get_addRegions');
add_action('wp_ajax_nopriv_get_addRegions', 'get_addRegions');


function creditExtention()
{
    global $wpdb;
    
    echo '<pre>'.print_r($_REQUEST, true).'</pre>';
    
    $id = $_REQUEST['id'];
    $newdate = date('m/d/Y', strtotime($_REQUEST['dateExtension']));
    
    $sql = "SELECT credit_expiration_date FROM wp_credit WHERE id='".$id."'";
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
//     require_once GPXADMIN_API_DIR.'/functions/class.salesforce.php';
//     $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
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
    
    $return = array('success'=>true, 'message'=>$msg, 'date'=>$newdate, 'cid'=>$cid);
}

add_action('wp_ajax_creditExtention', 'creditExtention');
add_action('wp_ajax_nopriv_creditExtention', 'creditExtention');




//wp ajax being used for cron api
function get_countryList()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $ping = '';
    if(isset($_POST['ping']))
    {
        $ping = $_POST['ping'];
    }
    
    $data = $gpx->DAEGetCountryList($ping);

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_countryList', 'get_countryList');
add_action('wp_ajax_nopriv_get_countryList', 'get_countryList');

function deleteUnittype()
{
    global $wpdb;
    $id =  $_POST['unit_id'];
    
    $wpdb->delete('wp_unit_type', array( 'record_id' => $id ) );
    
    echo wp_send_json("delete done");
    exit();
}
add_action('wp_ajax_deleteUnittype', 'deleteUnittype');
add_action('wp_ajax_nopriv_deleteUnittype', 'deleteUnittype');

function unitType_Form(){
    global $wpdb;

    if(isset($_POST['unit_id']) && !empty($_POST['unit_id'])){

        $unitType = [
                'name'=>$_POST['name'],
                'resort_id'=>$_POST['resort_id'],
                'number_of_bedrooms'=>$_POST['number_of_bedrooms'],
                'sleeps_total'=>$_POST['sleeps_total']
            ];

        $wpdb->update('wp_unit_type', $unitType,array('record_id'=>$_POST['unit_id']));    

    }
    else{

        $unitType = [
                'name'=>$_POST['name'],
                'resort_id'=>$_POST['resort_id'],
                'number_of_bedrooms'=>$_POST['number_of_bedrooms'],
                'sleeps_total'=>$_POST['sleeps_total']
            ];
        $wpdb->insert('wp_unit_type', $unitType);    
        
    }
    
    wp_send_json("Done");
    wp_die();


}
add_action('wp_ajax_unitType_Form', 'unitType_Form');
add_action('wp_ajax_nopriv_unitType_Form', 'unitType_Form');


function csv_upload(){
    global $wpdb;
    $error_message       = '';
    $success_message     = '';
    $message_info_style  = '';
    $array = str_getcsv(file_get_contents($_POST['file_url']));   
    $file_upload_url = $_POST['file_url'];

    $db_cols = array('record_id','create_date','last_modified_date','check_in_date', 'check_out_date','unit_type_id', 'unit_type', 'source_num', 'source_partner_id' ,'sourced_by_partner_on', 'resort_confirmation_number' ,'active', 'availability','available_to_partner_id','type', 'price', 'points', 'note', 'given_to_partner_id');  // Array of db column names
       
      
        $numColumns = '19';

        
        $myCSV   = $file_upload_url;

        if ( ( $fh = @fopen( $myCSV, 'r' )) !== false ) {
            
            $values      = array();
            $too_many    = '';  // Used to alert users if columns do not match

            $row = fgetcsv( $fh );
            $no_of_clients = 0;
            $counter = 0;     
            $ids=array();

            while ( ( $line = fgetcsv( $fh )) !== false ) {
//                 echo '<pre>'.print_r($line, true).'</pre>';
                $counter++; 
                if ( $counter != 0 ) { 
                    $type = '';
                    if(strtolower($line[5]) == "rental"){
                        $type = 1;
                    }
                    elseif (strtolower($line[5]) == "exchange") {
                        $type = 2;   
                    }
                    elseif(strpos(strtolower($line[5]), 'rental') !== false && strpos(strtolower($line[5]), 'exchange') !== false) {
                        $type = 3;
                    }

                    $source_num = '';
                    if($line[6] == "Owner"){
                        $source_num = 0;
                    }
                    elseif ($line[6] == "GPR") {
                        $source_num = 1;   
                    }
                    else {
                        $source_num = 2;
                    }


                $record_id  = $line[0];
                $resort_confirmation_number  = $line[1];
                $check_in_date  = date('Y-m-d', strtotime($line[9]));
//                 if(empty($line[2]))
//                 {
                    $check_out_date = date('Y-m-d', strtotime($line[9].'+1 week'));
//                 }
//                 else
//                 {
//                     $check_out_date = date('Y-m-d', strtotime($line[2]));
//                 }

                //resort fetch
                $resort = $line[7];
                $resorts = "SELECT * FROM `wp_resorts` WHERE `ResortName` = '".$resort."' ORDER BY `id` ASC";
                $resorts_result = $wpdb->get_results($resorts); 

                $resort = $resorts_result[0]->id;

                //unit type by resort
                $name = $line[8];
                $unit_type = "SELECT record_id FROM `wp_unit_type` WHERE `name` = '".$name."' AND `resort_id` = '".$resorts_result[0]->id."' ORDER BY `record_id` ASC";
                $unit_type = $wpdb->get_var($unit_type); 


//                 $unit_type  = $unit->record_id;
                $type  = $type;
                $source_num  = $source_num;

                //fetch partners
                 $spid = "SELECT * FROM wp_users a INNER JOIN wp_usermeta b on a.ID=b.user_id WHERE b.meta_key='DAEMemberNo' AND (user_nicename = '".$line[7]."' OR display_name = '".$line[7]."')";
                 $spid_result = $wpdb->get_results($spid);

                $source_partner_id = $spid_result[0]->ID;
                $availability = $line[8];      
                $active  = $line[14];
                if(strtolower($active) == 'false')
                {
                    $active = '0';
                }
                else 
                {
                    $active = '1';
                }
                $price  = $line[9];
                $activeRental  = date('Y-m-d', strtotime($line[13]));

                //fetch available to partner
                $avltop = "SELECT * FROM `wp_partner` WHERE name = '".$line[10]."'";
                 $avltop_result = $wpdb->get_results($avltop);
                $available_to_partner_id   = $avltop_result[0]->record_id;

                $gvtop = "SELECT * FROM `wp_partner` WHERE name = '".$line[10]."'";
                 $gvtop_result = $wpdb->get_results($gvtop);

                $given_to_partner_id  = $gvtop_result[0]->record_id;
                $price = number_format(preg_replace("/[^0-9]/", "",str_replace('.00', '', $line[12])), 0);
                $note   = $line[13];
                
                $inserts = [
                    'record_id'=>$record_id,
                    'resort_confirmation_number'=>$resort_confirmation_number,
                    'resort'=>$resort,
                    'unit_type'=>$unit_type,
                    'check_in_date'=>$check_in_date,
                    'check_out_date'=>$check_out_date,
                    'price'=>$price,
                    'active'=>$active,
                    'active_rental_push_date'=>$activeRental,
                    'source_num'=>'2',
                    'type'=>'3',
                    'availability'=>'1',
                ];
                
                $inserts['create_by'] = get_current_user_id();
                
                $updateDets[strtotime('NOW')] = [
                    'update_by' => get_current_user_id(),
                    'details'=>base64_encode(json_encode($inserts)),
                ];
                $inserts['update_details'] = json_encode($updateDets);
                
                echo '<pre>'.print_r($inserts, true).'</pre>';

                $wpdb->insert('wp_room', $inserts);
                if($wpdb->last_error)
                {
                    echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                    exit;
                }
//                 $sql   = "INSERT INTO `wp_room` (`resort_confirmation_number`,`check_in_date`, `check_out_date`, `resort`,`unit_type`, `type`, `source_num`, `source_partner_id`, `availability`, `active`, `available_to_partner_id`, `given_to_partner_id`,`price`,`note`) VALUES ('".$resort_confirmation_number."', '".$check_in_date."', '".$check_out_date."', '".$resort."', '".$unit_type."', '".$type."', '".$source_num."', '".$source_partner_id."', '".$availability."', '".$active."', '".$available_to_partner_id."', '".$given_to_partner_id."', '".$price."', '".$note."')";

                
//                 $db_query_insert = $wpdb->query( $sql );


                // $updatesql = "UPDATE `wp_room` SET `import_id` = ".$wpdb->insert_id." WHERE `wp_room`.`record_id` = ".$wpdb->insert_id."";
                //  $updatedb_query = $wpdb->query($updatesql);
                    $ids[$counter] = $wpdb->insert_id;
                } 

              
            }
// exit;

if(isset($ids)){

foreach ($ids as $value) {
    
    $updatesql = "UPDATE `wp_room` SET `import_id` = ".$ids[1]." WHERE `wp_room`.`record_id` = ".$value."";

    $updatedb_query = $wpdb->query($updatesql);
}    
}



            
                // logic if data not right
        $sql_error = "SELECT count(record_id) as records FROM `wp_room` WHERE (`check_in_date` = '0000-00-00 00:00:00' or `check_out_date` = '0000-00-00 00:00:00' or resort ='0' or resort ='null' or unit_type ='null') AND (import_id IN (SELECT import_id FROM wp_room WHERE create_date = (SELECT MAX(create_date) FROM wp_room)))";
        $sqlerr = $wpdb->get_results($sql_error);

        
        if($sqlerr){
            if(isset($sqlerr[0]->records)){
            
             wp_send_json(array('error', "There were ".$sqlerr[0]->records." import errors. <a href='/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_error'>Click here</a> to edit them"));
            }
        }
        else{
            
            wp_send_json(array('success', "Csv uploded sucessfully"));

        }

        }
        else{
            wp_send_json(array('success', "Please upload a CSV file"));
            
        }

        
    // SELECT * FROM `wp_resorts` WHERE `ResortName` = 'The Ridge on Sedona Golf Resort' ORDER BY `id` ASC 



}
add_action('wp_ajax_csv_upload', 'csv_upload');
add_action('wp_ajax_nopriv_csv_upload', 'csv_upload');


function partner_autocomplete(){
    global $wpdb;

    $search = $_REQUEST['search'];
    $type = $_REQUEST['type'];
    $acType = $_REQUEST['actype'];
    
    
    if($_REQUEST['availability'])
    {
        //partners = 3
        if($type == '3')
        {
            $partnerSearch = true;
        }
        if($type == '2')
        {
            $ownerSearch = true;
        }
    }
    else 
    {
        if($type == '1')
        {
            $ownerSearch = true;
        }
        if($type == '3')
        {
            $partnerSearch = true;
        }
    }
//
    if($partnerSearch)
    {
        $sql = "SELECT user_id, name FROM `wp_partner` WHERE name like '%".$search."%' GROUP BY record_id ORDER BY `type` ASC";
    }
    else
    {
        $sql = "SELECT user_id, SPI_Owner_Name_1st__c as name  FROM `wp_GPR_Owner_ID__c` WHERE name like '%".$search."%' or `SPI_Owner_Name_1st__c` LIKE '%".$search."%' GROUP BY user_id ORDER BY `SPI_Owner_Name_1st__c` ASC";
    }

    $rows = $wpdb->get_results($sql);  
     $response = array();

    //  wp_send_json($rows);
    // wp_die();
    foreach ($rows as $row) {
        if($acType == 'select2')
        {
            $response['items'][] = array("id"=>$row->user_id,"text"=>$row->name);
        }
        else
        {
            $response[] = array("value"=>$row->user_id,"label"=>$row->name);
        }
    }

    wp_send_json($response);
    wp_die();


}
add_action('wp_ajax_partner_autocomplete', 'partner_autocomplete');
add_action('wp_ajax_nopriv_partner_autocomplete', 'partner_autocomplete');




function resort_confirmation_number(){
    global $wpdb;

    $resort = $_POST['resort'];
    $resortConfirmation = $_POST['resortConfirmation'];
    //  resort_confirmation_number
    $sql = "SELECT *  FROM `wp_room` WHERE `resort_confirmation_number` = '".$resortConfirmation."'";

     if(!empty($resort))
     {
         $sql .=  " AND resort='".$_POST['resort']."'";
     }
    $rows = $wpdb->get_results($sql);  
     $response = array();

     wp_send_json($rows);
    wp_die();
    


}
add_action('wp_ajax_resort_confirmation_number', 'resort_confirmation_number');
add_action('wp_ajax_nopriv_resort_confirmation_number', 'resort_confirmation_number');





function get_unit_type(){
    global $wpdb;

    $resort = $_POST['resort'];
    //  resort_confirmation_number
     $sql = "SELECT * FROM `wp_unit_type` WHERE `resort_id` = '".$resort."'";

    $rows = $wpdb->get_results($sql);  
    $res = array();
    
    foreach ($rows as $row) {
        
     $res[$row->record_id] = $row->name;
    }
   
    wp_send_json($res);
    wp_die();


}
add_action('wp_ajax_get_unit_type', 'get_unit_type');
add_action('wp_ajax_nopriv_get_unit_type', 'get_unit_type');




function room_Form(){
    global $wpdb;
    
    if(empty($_POST['check_out_date']))
    {
        $check_out_date = date("Y-m-d 00:00:00",strtotime($_POST['check_in_date'].' +1 week'));
    }
    else
    {
        $check_out_date = date("Y-m-d 00:00:00",strtotime($_POST['check_out_date']));
    }
    $displayDate = date("Y-m-d 00:00:00", strtotime($_POST['active_specific_date']));
    if(isset($_POST['active_week_month']) && !empty($_POST['active_week_month']))
    {
        
        if($_POST['active_type'] == 'weeks' || $_POST['active_type'] == 'months')
        {
            $displayDate = date('Y-m-01 00:00:00', strtotime($_POST['check_in_date'].' -'.$_POST['active_week_month'].$_POST['active_type']));
        }
    }
    
    $active = '0';
    if(isset($_POST['active']) && !empty($_POST['active']))
    {
        $active = $_POST['active'];
    }
    
    $rentalPush = date('Y-m-d', strtotime($_POST['check_in_date']."-6 months"));

    if(isset($_POST['rental_push']) && !empty($_POST['rental_push']))
    {
        $rentalPush = date('Y-m-d', strtotime($_POST['check_in_date']."-".$_POST['rental_push']." months"));
    }
    if(isset($_POST['rental_push_date']) && !empty($_POST['rental_push_date']))
    {
        $rentalPush = date('Y-m-d', strtotime($_POST['rental_push_date']));
    }
    if(strtotime($rentalPush) < strtotime($displayDate))
    {
        $rentalPush = date('Y-m-d', strtotime($displayDate));
    }

    
    if(strtotime($rentalPush) > strtolower($displayDate))
        
    
    
    $rooms = [
                'check_in_date' => date("Y-m-d 00:00:00",strtotime($_POST['check_in_date'])), 
                'check_out_date' => $check_out_date,
                'active_specific_date' => $displayDate,
                'resort' => $_POST['resort'],
                'unit_type' => $_POST['unit_type_id'],
                'source_num' => $_POST['source'],
                'source_partner_id' => $_POST['source_partner_id'],
                'resort_confirmation_number' => $_POST['resort_confirmation_number'],
                'active' => $active,
                'availability' => $_POST['availability'],
                'available_to_partner_id' => $_POST['available_to_partner_id'],
                'type' => $_POST['type'],
                'price' => floatval(str_replace(',', '', str_replace("$", "", $_POST['price']))),
                'note' => $_POST['note'],
                'active_week_month' => $_POST['active_week_month'],
                'active_type' => $_POST['active_type'],
                'active_rental_push_date' => $rentalPush,
            ];
    
    $count = 1;
    if($count > 0)
    {
        $count = $_POST['count'];
    }
    
    if(isset($_POST['room_id']) && !empty($_POST['room_id']))
    {
        $sql = "SELECT * FROM wp_room WHERE record_id=".$_POST['room_id'];
        $row = $wpdb->get_row($sql);
        
        foreach($rooms as $rk=>$rv)
        {
            if (DateTime::createFromFormat('Y-m-d H:i:s', $row->$rk) !== FALSE) {
                $row->$rv = date("Y-m-d", strtotime($row->$rk));
            }
            
            if($rv != $row->$rk)
            {
                $roomUpdate[$rk] = [
                    'old'=>$row->$rk,
                    'new'=>$rv,
                ];
            }
        }
        
        $updateDets = json_decode($row->update_details, ARRAY_A);
        
        $updateDets[strtotime('NOW')] = [
            'update_by' => get_current_user_id(),
            'details'=>base64_encode(json_encode($roomUpdate)),
        ];
        $rooms['update_details'] = json_encode($updateDets);
        
        $wpdb->update('wp_room', $rooms, array('record_id'=>$_POST['room_id']));
        $msg = "Updated successful";
    }
    else 
    {
        $rooms['create_by'] = get_current_user_id();
        
        $updateDets[strtotime('NOW')] = [
            'update_by' => get_current_user_id(),
            'details'=>base64_encode(json_encode($rooms)),
        ];
        $rooms['update_details'] = json_encode($updateDets);
        
        for($i=0;$i<$count;$i++)
        {
            $wpdb->insert('wp_room', $rooms);
        }
        
        $weeks = ' Week';
        if($i > 1)
        {
            $weeks .= 's';
        }
        
        $msg = $count.$weeks." Added";
    }
    
    if(isset($rooms['source_partner_id']) && !empty($rooms['source_partner_id']))
    {
        $sql = "UPDATE wp_partner 
        SET no_of_rooms_given = no_of_rooms_given + ".$count.", trade_balance = trade_balance + ".$count."
        WHERE user_id = '".$rooms['source_partner_id']."'";
        
        $wpdb->query($sql);
    }
    
    wp_send_json($msg);
    wp_die();
}
add_action('wp_ajax_room_Form', 'room_Form');
add_action('wp_ajax_room_Form_edit', 'room_Form');

// function room_Form_edit(){
//     global $wpdb;
    
//     if(empty($_POST['check_out_date']))
//     {
//         $check_out_date = date("Y-m-d",strtotime($_POST['check_in_date'].' +1 week'));
//     }
//     else 
//     {
//         $check_out_date = date("Y-m-d",strtotime($_POST['check_out_date']));
//     }
    
//     $displayDate = date("Y-m-d", strtotime($_POST['active_specific_date']));
//     if(isset($_POST['accredit resorttive_week_month']) && !empty($_POST['active_week_month']))
//     {
        
//         if($_POST['active_type'] == 'weeks' || $_POST['active_type'] == 'months')
//         {
//             $displayDate = date('Y-m-01', strtotime($_POST['check_in_date'].' -'.$_POST['active_week_month'].$_POST['active_type']));
//         }
//     }
    
//     $active = '0';
//     if(isset($_POST['active']) && !empty($_POST['active']))
//     {
//         $active = $_POST['active'];
//     }
    
//     $rooms = [
//                 'check_in_date' => date("Y-m-d",strtotime($_POST['check_in_date'])), 
//         'check_out_date' => $check_out_date,
//         'active_specific_date' => $displayDate,
//                 'resort' => $_POST['resort'],
//                 'unit_type' => $_POST['unit_type_id'],
//                 'source_num' => $_POST['source'],
//                 'source_partner_id' => $_POST['source_partner_id'],
//                 'resort_confirmation_number' => $_POST['resort_confirmation_number'],
//                 'active' => $active,
//                 'availability' => $_POST['availability'],
//                 'available_to_partner_id' => $_POST['available_to_partner_id'],
//                 'type' => $_POST['type'],
//                 'price' => floatval(str_replace(',', '', str_replace("$", "", $_POST['price']))),
//         'note' => $_POST['note'],
//         'active_week_month' => $_POST['active_week_month'],
//         'active_type' => $_POST['active_type'],
//             ];

    

//     wp_die();
// }
// }
// add_action('wp_ajax_room_Form_edit', 'room_Form_edit');

function getregionfromCountyList()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $ping = '';
    if(isset($_POST['ping']))
    {
        $ping = $_POST['ping'];
    }
    
    $data = $gpx->DAEGetCountryList($ping);

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_getregionfromCountyList', 'getregionfromCountyList');
add_action('wp_ajax_nopriv_getregionfromCountyList', 'getregionfromCountyList');

function get_regionList()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $CountryID = '14';
    
    if(isset($_GET['country']))
    {
        $CountryID = $_GET['country'];
    }
    
    $data = $gpx->DAEGetRegionList($CountryID);

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_regionList', 'get_regionList');
add_action('wp_ajax_nopriv_get_regionList', 'get_regionList');

function get_addResorts()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    echo '<pre>'.print_r("Start", true).'</pre>';
    $data = $gpx->DAEGetResortProfile();

    wp_send_json($data);
    wp_die();
}

function get_indResorts()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    echo '<pre>'.print_r("Start", true).'</pre>';
    $data = $gpx->DAEGetResortInd();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_indResorts', 'get_indResorts');
add_action('wp_ajax_nopriv_get_indResorts', 'get_indResorts');

function get_missingResort()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    echo '<pre>'.print_r("Start", true).'</pre>';
    $resortID = '9491';
    if(isset($_GET['resortID']))
    {
        $resortID = $_GET['resortID'];
    }
      
    $endpointID = 'EUR';
    if(isset($_GET['endpointID']))
    {
        $endpointID = $_GET['endpointID'];
    }
    $data = $gpx->missingDAEGetResortProfile($resortID, $endpointID);

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_missingResort', 'get_missingResort');
add_action('wp_ajax_nopriv_get_missingResort', 'get_missingResort');

function get_addResortDetails()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    echo '<pre>'.print_r("Start", true).'</pre>';
    $data = $gpx->addResortDetails();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_addResortDetails', 'get_addResortDetails');
add_action('wp_ajax_nopriv_get_addResortDetails', 'get_addResortDetails');

function get_manualResortUpdate()
{
    global $wpdb;
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $sql = "SELECT id, ResortID, EndpointID, gpxRegionID FROM wp_resorts WHERE ResortID='".$_POST['resort']."'";
    $row = $wpdb->get_row($sql);  
    $inputMembers = array(
        'ResortID'=>$row->ResortID,
        'EndpointID'=>$row->EndpointID,
   );
    
   $data = $gpx->DAEGetResortProfile($row->id, $row->gpxRegionID, $inputMembers, '1');

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_manualResortUpdate', 'get_manualResortUpdate');
add_action('wp_ajax_nopriv_get_manualResortUpdate', 'get_manualResortUpdate');

function get_manualResortUpdateAll()
{
    global $wpdb;
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $sql = "SELECT id, ResortID, EndpointID, gpxRegionID FROM wp_resorts WHERE active='1'";
    $rows = $wpdb->get_results($sql);  
    foreach($rows as $row)
    {
        $inputMembers = array(
            'ResortID'=>$row->ResortID,
            'EndpointID'=>$row->EndpointID,
       );
       $data = $gpx->DAEGetResortProfile($row->id, $row->gpxRegionID, $inputMembers, '1');
    }

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_manualResortUpdateAll', 'get_manualResortUpdateAll');
add_action('wp_ajax_nopriv_get_manualResortUpdateAll', 'get_manualResortUpdateAll');


function subregions_all()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    echo '<pre>'.print_r("Start", true).'</pre>';
    $data = $gpx->update_subregions_add_all_resorts();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_subregions_all', 'subregions_all');
add_action('wp_ajax_nopriv_get_addResorts', 'subregions_all');

function get_bonus()
{
    $function = 'DAEGetBonusRentalAvailability';
    if(isset($_GET['function']))
    {
        $function = 'NewAddDAEGetBonusRentalAvailability';
    }
    $month = 3;
    if(isset($_GET['month']))
        $month = $_GET['month'];
    $year = '2019';
    if(isset($_GET['year']))
        $year = $_GET['year'];
    $country = "14";
    if(isset($_GET['country']))
        $country = $_GET['country'];
    $region = "?";
    if(isset($_GET['region']))
        $region = $_GET['region'];
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
        if(isset($_GET['quick']))
        {
            $inputMembers['quick'] = true;
        }
        $data = $gpx->$function($inputMembers);
        echo '<pre>'.print_r($data, true).'</pre>';
//     }
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_bonus', 'get_bonus');
add_action('wp_ajax_nopriv_get_bonus', 'get_bonus');

function get_exchange()
{
    global $wpdb;
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
//     for($i=3;$i<13;$i++)
//     {
    $function = 'DAEGetExchangeAvailability';
    if(isset($_GET['function']))
    {
        $function = 'NewAddDAEGetExchangeAvailability';
    }
    $month = 3;
    if(isset($_GET['month']))
        $month = $_GET['month'];
    $year = '2019';
    if(isset($_GET['year']))
        $year = $_GET['year'];
    $country = "14";
    if(isset($_GET['country']))
        $country = $_GET['country'];
    $region = "?";
    
    if($country == '14' && $_GET['all'] == 'all')
    {
        $sql = "SELECT a.id FROM wp_properties a
                INNER JOIN wp_resorts  b on a.resortJoinID = b.id
                INNER JOIN wp_gpxRegion c on b.gpxRegionID = c.id
                WHERE c.lft BETWEEN 1939 AND 2924
                AND a.active='1'
                AND b.active='1'
                AND a.WeekType='ExchangeWeek'";
        $toCheck = $wpdb->get_results($sql);
        foreach($toCheck as $tc)
        {
            $allActive[$tc->id] = $tc->id;
        }
        $session = strtotime('NOW');
        $wpdb->insert('wp_refresh_to_remove', array('session'=>$session, 'weeks_all'=>json_encode($allActive)));
        $dbActiveRefresh = $wpdb->insert_id;
        
        //get all the regions
        $allRegions = [
            'SECA',
            'RMSE', 
            'NENG', 
            'MWES', 
            'MSOU', 
            'MATL', 
            'HAWA', 
            'GULF', 
            'PCOA', 
        ];
        $pullDates[] = date('n/Y');
        $date  = date('m/d/Y');
        for($i=1;$i<13;$i++)
        {
            $pullDates[] = date('n/Y', strtotime("+".$i." months", strtotime($date)));
        }

        foreach($allRegions as $region)
        {
            foreach($pullDates as $pd)
            {
                $pds = explode("/", $pd);
                $pullyear = $pds[1];
                $pullmonth = $pds[0];
                $inputMembers = array(
                    'DAEMemberNo'=>true,
                    'CountryID'=>$country,
                    'RegionID'=>$region,
                    'Month'=>$pullmonth,
                    'Year'=>$pullyear,
                    'ShowSplitWeeks'=>True,
                );
                if(isset($dbActiveRefresh))
                {
                    $inputMembers['dbActiveRefresh'] = $dbActiveRefresh;
                }
                $data = $gpx->$function($inputMembers);
                
                if(isset($data['weeks_added']))
                {
                    $addedArr = $data['weeks_added'];
                    echo '<pre>'.print_r("added: ", true).'</pre>';
                    echo '<pre>'.print_r($addedArr, true).'</pre>';
                }
                foreach($addedArr as $ar)
                {
                    unset($allActive[$ar]);
                }
            }
        }
        
        if(isset($session))
        {
            //get all the weeks that were added
            if(isset($data['weeks_added']))
            {
                $addedArr = $data['weeks_added'];
                echo '<pre>'.print_r("added: ", true).'</pre>';
                echo '<pre>'.print_r($addedArr, true).'</pre>';
            }
            else 
            {
//                 $sql = "SELECT weeks_added, weeks_all FROM wp_refresh_to_remove WHERE id='".$dbActiveRefresh."'";
//                 $added = $wpdb->get_row($sql);
//                 $addedArr = json_decode($added->weeks_added, true);
            }
            foreach($addedArr as $ar)
            {
                unset($allActive[$ar]);
            }
            echo '<pre>'.print_r($allActive, true).'</pre>';
            //now we have all the weeks that aren't active
            foreach($allActive as $aa)
            {
                $wpdb->update('wp_properties', array('active'=>'0'), array('id'=>$aa));
            }
        }
        wp_send_json($data);
        wp_die();
    }
    
    if(isset($_GET['region']))
    {
        $region = $_GET['region'];
    }
        $inputMembers = array(
            'DAEMemberNo'=>true,
            'CountryID'=>$country,
            'RegionID'=>$region,
            'Month'=>$month,
            'Year'=>$year,
            'ShowSplitWeeks'=>True,
        );
        $data = $gpx->$function($inputMembers);
//     }
    echo '<pre>'.print_r($data, true).'</pre>';
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_exchange', 'get_exchange');
add_action('wp_ajax_nopriv_get_exchange', 'get_exchange');

function get_add_bonus()
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
    
    
    //now we are going to pull all regions within a country
    if(isset($_GET['country']))
    {
        $countries[] = $_GET['country'];
        if(isset($_GET['region']))
        {
            $regions[$_GET['country']][] = $_GET['region'];
        }
        else
        {
            $regions[$_GET['country']][] = '?';
        }
    }
    else
    {
        $sql = "SELECT DISTINCT CountryID FROM wp_daeCountry WHERE (CountryID <> '14' OR CountryID <> '7' OR CountryID <> '26') AND active=1";
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
    
    $pullDates[] = date('n/Y');
    
    for($i=1;$i<13;$i++)
    {
        $pullDates[] = date('n/Y', strtotime("+".$i." months", strtotime($date)));
    }
    
    foreach($countries as $country)
    {
        foreach($regions[$country] as $region)
        {
            foreach($pullDates as $pd)
            {
                echo '<pre>'.print_r($pd, true).'</pre>';
                $pds = explode("/", $pd);
                $pullyear = $pds[1];
                $pullmonth = $pds[0];
                $subtime = microtime(true);
                
                
                $subtimediff = $starttime - $subtime;
                echo '<pre>'.print_r($subtimediff, true).'</pre>';
                $inputMembers = array(
                    'DAEMemberNo'=>true,
                    'CountryID'=>$country,
                    'RegionID'=>$region,
                    'Month'=>$pullmonth,
                    'Year'=>$pullyear,
                    'WeeksToShow'=>'ALL',
                    'Sort'=>'Default',
                );
                echo '<pre>'.print_r($inputMembers, true).'</pre>';
                $data = $gpxapi->NewAddDAEGetBonusRentalAvailability($inputMembers);
                echo '<pre>'.print_r($data, true).'</pre>';
                
                //update the most recent pulls with info...
                
                $wpdb->insert('wp_daeRefresh', array('called'=>'bonus', 'country'=>$country, 'pulled'=>$pullmonth."/".$pullyear));
                
            }
        }
    }
    
    
    $data = array('success'=>true);
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_add_bonus', 'get_add_bonus');
add_action('wp_ajax_nopriv_get_add_bonus', 'get_add_bonus');

function get_add_exchange()
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
    
    //now we are going to pull all regions within a country
    if(isset($_GET['country']))
    {
        $countries[] = $_GET['country'];
        if(isset($_GET['region']))
        {
            $regions[$_GET['country']][] = $_GET['region']; 
        }
        else
        {
            $regions[$_GET['country']][] = '?'; 
        }
    }
    else
    {
        $sql = "SELECT DISTINCT CountryID FROM wp_daeCountry WHERE (CountryID <> '14' OR CountryID <> '7' OR CountryID <> '26') AND active=1";
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
        '14'
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
    
    $pullDates[] = date('n/Y');
    $startDate = date('n');
    
    for($i=1;$i<13;$i++)
    {
        $pullDates[] = date('n/Y', strtotime("+".$i." months", strtotime($date)));
    }
    
    foreach($countries as $country)
    {
        foreach($regions[$country] as $region)
        {
            foreach($pullDates as $pd)
            {
                $pds = explode("/", $pd);
                $pullyear = $pds[1];
                $pullmonth = $pds[0];
                $subtime = microtime(true);
                $inputMembers = array(
                    'DAEMemberNo'=>true,
                    'CountryID'=>$country,
                    'RegionID'=>$region,
                    'Month'=>$pullmonth,
                    'Year'=>$pullyear,
                    'ShowSplitWeeks'=>True,
                );
                echo '<pre>'.print_r($inputMembers, true).'</pre>';
                $data = $gpxapi->NewAddDAEGetExchangeAvailability($inputMembers);
                echo '<pre>'.print_r($data, true).'</pre>';
                
                //update the most recent pulls with info...
                
                $wpdb->insert('wp_daeRefresh', array('called'=>'exchange', 'country'=>$country, 'pulled'=>$pullmonth."/".$pullyear));
            }
        }
        
    }
    
    $data = array('success'=>true);
    
    wp_send_json($data);
    wp_die();
    
}

add_action('wp_ajax_get_add_exchange', 'get_add_exchange');
add_action('wp_ajax_nopriv_get_add_exchange', 'get_add_exchange');

function get_dae_user_info()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    $userdata = $gpx->returnDAEGetMemberDetails($_GET['daememberno']);
    
    wp_send_json($userdata);
    wp_die();
    
}
add_action('wp_ajax_get_dae_user_info', 'get_dae_user_info');
add_action('wp_ajax_nopriv_get_dae_user_info', 'get_dae_user_info');

function get_dae_users()
{

    global $wpdb;
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    /*
    $daeMemebers = $wpdb->get_results(
        "SELECT AccountID
        FROM wp_daeMembers
        WHERE AccountStatus=''
        AND added='0'
        LIMIT 10"
        );
    if($daeMemebers)
    {
        foreach($daeMemebers as $daeMemeber)
        {
            echo '<pre>'.print_r($daeMemeber, true).'</pre>';
          $DAEMemeberNo = $daeMemeber->AccountID;
          $data = $gpx->DAEGetMemberDetails($DAEMemeberNo);
            
        }
    }

    
    $args1 = array(
        'role' => 'gpx_member',
    );
    $gpx_members = get_users($args1);
    
    foreach($gpx_members as $member)
    {
        if(is_numeric($member->data->user_login))
        {
            $userid = $member->data->ID;
            $DAEMemeberNo = $member->data->user_login;
            $data = $gpx->DAEGetMemberDetails($DAEMemeberNo, $userid);
        }
    }
    */ 
    $sql = "SELECT * FROM wp_users a 
            INNER JOIN wp_usermeta b on a.ID=b.user_id
            WHERE b.meta_key='DAEMemberNo'
            ORDER BY a.ID desc";
    //$sql = "SELECT * FROM wp_users WHERE user_registered > '2017-03-14 00:00:00'";
    $results = $wpdb->get_results($sql);
    foreach($results as $key=>$result)
    {
//         echo '<pre>'.print_r($result, true).'</pre>';
//         wp_delete_user($result->ID);
//         $data[] = array(
//           'ID'=>$result->ID,
//             'memberno'=>$result->meta_value,
//             'email'=>$result->user_email,
//         );
//         $data = $gpx->DAEGetMemberDetails($result->meta_value, $result->ID, $result->user_email);
        
//         if(substr($result->user_login, 0, 1) == "U")
//            unset($results[$key]); 
//         else   
//             $d[$result->ID] = $result->meta_value;
    }
//     $d = array('11'=>'233078');
//     echo '<pre>'.print_r($d, true).'</pre>';
//     foreach($d as $k=>$v)
//     {
//         $data = $gpx->DAEGetMemberDetails($v, $k, $email);
//     }

    //$data = $gpx->DAEGetMemberDetails('233078', '11', 'lmehl@gpresorts.com');
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_get_dae_users', 'get_dae_users');
add_action('wp_ajax_nopriv_get_dae_users', 'get_dae_users');

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
    echo '<pre>'.print_r($rows, true).'</pre>';
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


function gpx_check_active()
{
    global $wpdb;
    
    $sql = "SELECT record_id FROM wp_room WHERE active_specific_date <= '".date('Y-m-d')."' and active=0";
    $results = $wpdb->get_results($sql);
    
    foreach($results as $r)
    {
        
        $sql = "SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId='".$r->record_id."' AND cancelled IS NULL";
        $trow = $wpdb->get_var($sql);
        
        if($trow > 0)
        {
            //nothing to do
        }
        else
        {
            $sql = "SElECT id FROM wp_gpxPreHold WHERE weekId='".$r->record_id."' AND released=0";
            $held = $wpdb->get_var($sql);
            if(empty($held))
            {
                $wpdb->update('wp_room', array('active'=>1), array('record_id'=>$r->record_id));
            }
        }
    }
    
    $checkIN = date('Y-m-d', strtotime('+1 week'));
    $sql = "SELECT record_id FROM wp_room WHERE check_in_date <= '".$checkIN."' and active=1";
    $results = $wpdb->get_results($sql);
    foreach($results as $r)
    {
        $wpdb->update('wp_room', array('active'=>0), array('record_id'=>$r->record_id));
    }
 
}
add_action('hook_cron_gpx_check_active', 'gpx_check_active');
add_action('wp_ajax_cron_gca', 'gpx_check_active');
function function_Ownership_mapping() {
        global $wpdb;
        $check_wp_mapuser2oid = $wpdb->get_results("SELECT usr.ID as gpx_user_id, usr.user_nicename as gpx_username, Name as gpr_oid, oint.ownerID as gpr_oid_interval, resortID, user_status, Delinquent__c, unitweek  FROM wp_GPR_Owner_ID__c oid INNER JOIN wp_owner_interval oint ON oid.Name = oint.ownerID INNER JOIN wp_users usr ON usr.user_email = oid.SPI_Email__c");

      if(isset($check_wp_mapuser2oid)){
        
        if (count($check_wp_mapuser2oid) != 0){

       foreach ($check_wp_mapuser2oid as $value) {

        $check_available = $wpdb->get_results("SELECT *  FROM `wp_mapuser2oid` WHERE `gpx_user_id` = '".$value->gpx_user_id."' AND `gpx_username` LIKE '".$value->gpx_username."' AND `gpr_oid` = '".$value->gpr_oid."' AND `gpr_oid_interval` = '".$value->gpr_oid_interval."'"); 
//         print_r(count($check_available));
        
        if (count($check_available) == 0){

          $insert = $wpdb->get_results("INSERT INTO `wp_mapuser2oid` (`gpx_user_id`, `gpx_username`, `gpr_oid`, `gpr_oid_interval`, `resortID`, `user_status`, `Delinquent__c`, `unitweek`) VALUES ('".$value->gpx_user_id."', '".$value->gpx_username."', '".$value->gpr_oid."', '".$value->gpr_oid_interval."', '".$value->resortID."', '".$value->user_status."', '".$value->Delinquent__c."', '".$value->unitweek."')");

          
        }  
       
       }    
      }
    }

}

add_action('hook_cron_function_Ownership_mapping', 'function_Ownership_mapping');

function gpx_owner_reassign()
{
    global $wpdb;
    
    if(isset($_REQUEST['vestID']))
    {
        $wpdb->update('wp_credit', array('owner_id'=>$_REQUEST['vestID']), array('owner_id'=>$_REQUEST['legacyID']));
        if(get_current_user_id() == 5)
        {
            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
            echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
        }
        $sql = "SELECT id, data FROM wp_gpxTransactions WHERE userID='".$_REQUEST['legacyID']."'";
        $rows = $wpdb->get_results($sql);
        
        foreach($rows as $row)
        {
            $id = $row->id;
            $tData = json_decode($row->data, true);
            
            $tData['MemberNumber'] = $_REQUEST['vestID'];
            $wpdb->update('wp_gpxTransactions', array('userID'=>$_REQUEST['vestID'], 'data'=>json_encode($tData)), array('id'=>$id));
 
            if(get_current_user_id() == 5)
            {
                echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
            }
            
        }
        
        echo '<pre>'.print_r("UPDATED", true).'</pre>';
    }
    
}
add_action('wp_ajax_gpx_owner_reassign', 'gpx_owner_reassign');

function rework_missed_deposits()
{
    global $wpdb;
    
    $sql = "SELECT * FROM `deposit_rework` WHERE imported='33' LIMIT 1000";
    $rows = $wpdb->get_results($sql);
    foreach($rows as $row)
    {
        $wpdb->update('deposit_rework', array('imported'=>'5'), array('id'=>$row->id));
        $sql = "SELECT `deposit used` FROM transactions_import_two WHERE weekId=".$row->weekId." AND MemberNumber=".$row->userID;
        $odeposit = $wpdb->get_var($sql);
        if(!empty($odeposit))
        {
            
            $sql = "SELECT a.id FROM wp_credit a  
                    INNER JOIN import_credit_future_stay b ON 
                        b.Deposit_year=a.deposit_year AND
                        b.resort_name=a.resort_name AND
                        b.unit_type=a.unit_type AND
                        b.Member_Name=a.owner_id
                        WHERE b.ID=".$odeposit;
            $deposit = $wpdb->get_var($sql);
            
            if(!empty($deposit))
            {
                $sql =  "SELECT id, data FROM wp_gpxTransactions WHERE weekId=".$row->weekId." AND userID=".$row->userID;
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
        echo '<pre>'.print_r($tcnt, true).'</pre>';
        echo '<script>location.reload();</script>';
        exit;
    }
    
    wp_send_json(array('remaining'=>$tcnt));
    wp_die();
}
add_action('wp_ajax_rework_missed_deposits', 'rework_missed_deposits');
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
            $wheres[] = $rk." = '".$rv."'";
        }
        
        $sql = "SELECT id, owner_id, deposit_year, check_in_date, credit_amount, resort_name, unit_type
                FROM wp_credit WHERE ".implode(" AND ", $wheres)." ORDER BY id desc";
        
        $rows = $wpdb->get_results($sql);

        foreach($rows as $k=>$row)
        {
            $in[] = $row->id;
            $sql = "SELECT id FROM wp_gpxTransactions WHERE JSON_EXTRACT(data, '$.creditweekid') = '".$row->id."'";
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
    wp_die();
}
add_action('wp_ajax_rework_duplicate_credits', 'rework_duplicate_credits');

function rework_ids_r()
{
    global $wpdb;
    
    $sf = Salesforce::getInstance();
    
    $limit = 500;
    
//     $sql = "SELECT id, old_owner_id, new_owner_id FROM owner_rework_owners WHERE imported=0 AND old_owner_id !='2147483647' ORDER BY RAND() LIMIT ".$limit;
    
    
//     $sql = "SELECT id, Name, SPI_Owner_Name_1st__c, user_id, SPI_Email__c FROM `wp_GPR_Owner_ID__c` WHERE user_id < 99991 ORDER BY `user_id` ASC LIMIT ".$limit;
    $sql = "SELECT user_id FROM `wp_GPR_Owner_ID__c` WHERE meta_rework=0 LIMIT ".$limit;
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
//         $user = $u;
        //get the GPX_Member_VEST__c
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
        
//         update_user_meta($nu, 'DAEMemberNo', $nu);
//         update_user_meta($nu, 'GPX_Member_VEST__c', $nu);
        if($nu != $olduser->user_id)
        {
            $wpdb->update('wp_GPR_Owner_ID__c', array('user_id'=>$nu), array('id'=>$olduser->id));
            $wpdb->update('wp_mapuser2oid', array('gpx_user_id'=>$nu), array('gpr_oid'=>$olduser->Name));
            $wpdb->update('wp_owner_interval', array('userID'=>$nu), array('ownerID'=>$olduser->Name));
        }

//         //adjust the transactions
        if($ou != $nu)
        {
//             $wpdb->update('wp_gpxTransactions', array('userID'=>$nu), array('userID'=>$ou));
//             $wpdb->update('wp_gpxPreHold', array('user'=>$nu), array('user'=>$ou));
//             $wpdb->update('wp_cart', array('user'=>$nu), array('user'=>$ou));
//             $wpdb->update('wp_credit', array('owner_id'=>$nu), array('owner_id'=>$ou));
//             $wpdb->update('wp_gpxAutoCoupon', array('user_id'=>$nu), array('user_id'=>$ou));
//     //         $wpdb->update('wp_gpxMemberSearch', array('userID'=>$nu), array('userID'=>$ou));
//             $wpdb->update('wp_partner', array('user_id'=>$nu), array('user_id'=>$ou));
//             $wpdb->update('wp_users', array('ID'=>$nu), array('ID'=>$ou));
//             $wpdb->update('wp_usermeta', array('user_id'=>$nu), array('user_id'=>$ou));
        }
    }
    
//     $sql = "SELECT id, old_owner_id, new_owner_id FROM owner_rework_owners WHERE imported=0 AND old_owner_id !='2147483647'";
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

function rework_zero_ids()
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

function rework_username()
{
    global $wpdb;
    
    $sql = "SELECT ID FROM wp_users WHERE user_login LIKE '%NOT_A_VALID_EMAIL%' LIMIT 100";
    $rows = $wpdb->get_results($sql);
    
    foreach($rows as $row)
    {
        $wpdb->update('wp_users', array('user_login'=>$row->ID), array('ID'=>$row->ID));
    }
    
    
    $sql = "SELECT COUNT(ID) AS cnt FROM wp_users WHERE user_login LIKE '%NOT_A_VALID_EMAIL%'";
    $remain = $wpdb->get_var($sql);
    if($remain > 0)
    {
        echo '<pre>'.print_r($remain, true).'</pre>';
        sleep(1);
        echo '<script>location.reload();</script>';
        exit;
    }
    
    wp_send_json(array('remaining'=>$remain));
    wp_die();
}
add_action('wp_ajax_rework_username', 'rework_username');

function rework_ids()
{
    global $wpdb;
    
    $sql = "SELECT ID, user_login FROM 
            `wp_users` 
            WHERE `user_login` LIKE 'U%' ORDER BY ID DESC";    
    $users = $wpdb->get_results($sql);
    echo '<pre>'.print_r($sql, true).'</pre>';
    
    foreach($users as $user)
    {
        $userID = $user->ID;
        $ul = str_replace("U", "", $user->user_login);
        $ul = str_replace(" ", "", $ul);
        if($ul != $userID)
        {
//             $nines[] = $ul;
            //insert into the table
            
            //get the new id
//                 $wpdb->insert('owner_rework_owners', array('old_owner_id'=>$userID, 'new_owner_id'=>$ul, 'imported'=>'0'));
                if($wpdb->last_error)
                {
                    echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                }
        }
    }
//     $sql = "SELECT count(ID) as cnt FROM 
//             `wp_users` 
//             WHERE `user_login` LIKE 'U%'";
//     $tcnt = $wpdb->get_var($sql);
    
    $of = $offset+$limit;
    if($of < $tcnt)
    {
//         echo '<script>location.reload();</script>';
//         exit;
    }
    
    wp_send_json(array('remaining'=>$tcnt));
    wp_die();
}
add_action('wp_ajax_rework_ids', 'rework_ids');

function rework_interval()
{
    global $wpdb;
    
    $sf = Salesforce::getInstance();
    
    $sql = "SELECT * FROM wp_GPR_Owner_ID__c WHERE meta_rework=0 ORDER BY `user_id` DESC LIMIT 500";    
    $users = $wpdb->get_results($sql);
    
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
    
    foreach($users as $user)
    {
        $wpdb->update('wp_GPR_Owner_ID__c', array('meta_rework'=>1), array('id'=>$user->id));
        $gmvc = get_user_meta($user->user_id, 'GPX_Member_VEST__c', true);
        if($gmvc != $user->user_id)
        {
            $wpdb->update('wp_GPR_Owner_ID__c', array('meta_rework'=>2), array('id'=>$user->id));
            $userIDs[] = $user->Name;
            $oldUserIDs[$user->Name] = $user->user_id;
        }
    }
    
    if(!empty($userIDs))
    {
        $query = "SELECT ".implode(",", $sels)."  FROM GPR_Owner_ID__c where
                   Name IN ('".implode("','", $userIDs)."'";
        $results = $sf->query($query);
        
        foreach($results as $result)
        {
            $wpdb->update('wp_GPR_Owner_ID__c', array('meta_rework'=>3), array('id'=>$user->id));
            $value = $results[0]->fields;
        
            $wpdb->update('wp_GPR_Owner_ID__c', array('user_id'=>$value->GPX_Member_VEST__c), array('user_id'=>$oldUserIDs[$value->Name]));
            $wpdb->update('wp_mapuser2oid', array('gpx_user_id'=>$value->GPX_Member_VEST__c), array('gpx_user_id'=>$oldUserIDs[$value->Name]));
            $wpdb->update('wp_owner_interval', array('userID'=>$value->GPX_Member_VEST__c), array('userID'=>$oldUserIDs[$value->Name]));
//             foreach($selects as $sk=>$sv)
//             {
//                 if(isset($value->$sv))
//                 {
//                     update_user_meta($user->user_id, $sk, $value->$sv);
//                 }
//             }
        }
    }

    $sql = "SELECT * FROM wp_GPR_Owner_ID__c WHERE meta_rework=0";
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
add_action('wp_ajax_rework_interval', 'rework_interval');

function rework_coupon()
{
    global $wpdb;
    
    $sql = "SELECT id, Properties FROM `wp_specials` WHERE `Amount` = '100' and SpecUsage='customer' and reworked=1 and active=1 ORDER BY `id`  DESC LIMIT 1";
    $rows = $wpdb->get_results($sql);
    echo '<pre>'.print_r(count($rows), true).'</pre>';
    foreach($rows as $row)
    {
        $data = json_decode($row->Properties);
        $specificCustomer = json_decode($data->specificCustomer, true);
        $useExc = $data->useExc;
        
        foreach($specificCustomer as $sc)
        {
            $sql = "SELECT new_id FROM vest_rework_users WHERE old_id='".$sc."'";
            $newID = $wpdb->get_var($sql);
            if(!empty($newID))
            {
                if(!in_array($newID, $specificCustomer))
                {
                    $specificCustomer[] = $newID;
                }
                
                $data->useExc = str_replace('\"'.$sc.'\"', '\"'.$newID.'\"', $data->useExc);
            }
        }
        
        $upp = json_encode($specificCustomer);
        $data->specificCustomer = $upp;
        $wpdb->update('wp_specials', array('Properties'=>json_encode($data), 'reworked'=>'2'), array('id'=>$row->id));
    }
}
add_action('wp_ajax_gpx_rework_coupon', 'rework_coupon');

function rework_mc_expire()
{
    global $wpdb;
    
    $sql = "SELECT a.id, b.datetime FROM wp_gpxOwnerCreditCoupon a
            INNER JOIN wp_gpxOwnerCreditCoupon_activity b on b.couponID=a.id
            WHERE a.created_date is null
            AND b.activity='created'";
    $rows = $wpdb->get_results($sql);
    
    foreach($rows as $row)
    {
        $wpdb->update('wp_gpxOwnerCreditCoupon', array('expirationDate'=>date('Y-m-d', strtotime($row->datetime."+1 year")), 'created_date'=>date('Y-m-d', strtotime($row->datetime))), array('id'=>$row->id));
    }
    
    $sql = "SELECT count(id) as cnt FROM `wp_gpxOwnerCreditCoupon` WHERE `created_date` is null";
    $tcnt = $wpdb->get_var($sql);
    
    if($tcnt > 0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }
    wp_send_json(array('remaining'=>$tcnt));
    wp_die();
}
add_action('wp_ajax_gpx_rework_mc_expire', 'rework_mc_expire');

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
        $sql = "SELECT id FROM wp_gpxTransactions WHERE weekId='".$row->record_id."'";
        $t = $wpdb->get_var($sql);
        if(!empty($t))
        {
            echo '<pre>'.print_r("T".$t, true).'</pre>';
            continue;
        }
        $sql = "SELECT id FROM wp_gpxPreHold WHERE weekId='".$row->record_id."' AND released=0";
        $h = $wpdb->get_var($sql);
        if(!empty($h))
        {
            echo '<pre>'.print_r("H".$h, true).'</pre>';
            continue;
        }
        $wpdb->update('wp_room', array('active'=>1, 'active_specific_date'=>'2030-01-01', 'active_rental_push_date'=>'2030-01-01'), array('record_id'=>$row->record_id));
        $i++;
    }
    echo '<pre>'.print_r($i, true).'</pre>';
    $sql = "SELECT count(r.record_id) as cnt FROM  wp_room r
            INNER JOIN import_partner_credits p ON p.record_id=r.record_id
            WHERE r.active=0 AND p.Active=1";
    $tcnt = $wpdb->get_var($sql);
    
    wp_send_json(array('remaining'=>$tcnt));
    wp_die();
}
add_action('wp_ajax_gpx_rework_tp_inactive', 'rework_tp_inactive');

add_action('hook_cron_GPX_Owner', 'function_GPX_Owner');
function function_GPX_Owner($isException='', $byOwnerID='') {
    
        global $wpdb;
        
//         if(empty($isException))
//         {
//             echo '<pre>'.print_r("Temporarily Disabled", true).'</pre>';
//             exit;
//         }
        
//         $wpdb->insert('wp_owner_spi_error', array('owner_id'=>'9999991'));
//     require_once GPXADMIN_API_DIR.'/functions/class.restsaleforce.php';
//     $gpxRest = new RestSalesforce();
    
//     require_once GPXADMIN_API_DIR.'/functions/class.salesforce.php';
//     $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
    $sf = Salesforce::getInstance();
      
//     $sql = "SELECT owner, id FROM import_owner_no_vest WHERE imported < 5 ORDER BY RAND() LIMIT 10";
//     $owners = $wpdb->get_results($sql);

//     if(!empty($owners))
//     {
//         foreach($owners as $owner)
//         {
//             $impowner[] = $owner->owner;
//             $iowners[$owner->owner] = $owner->id;
//         }
//     }
    $queryDays = '1';
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
   
//     $sql = "SELECT id, dae FROM final_owner_import WHERE imported=0 ORDER BY RAND() LIMIT 500";
//     $allOwners = $wpdb->get_results($sql);
    
//     $sql = "SELECT id, Name FROM wp_GPR_Owner_ID__c WHERE SPI_Email__c='hrfetters@yahoo.com'";
//     $sql = "SELECT ID FROM `wp_users` WHERE `ID` LIKE '9999%' ORDER BY RAND() LIMIT 3000";
//     $allOwners = $wpdb->get_results($sql);
// //     echo '<pre>'.print_r($allOwners, true).'</pre>';
//     foreach($allOwners as $ao)
//     {
//         $newID = substr($ao->ID, 4);
//         $sql = "SELECT id FROM wp_GPR_Owner_ID__c WHERE user_id='".$newID."'";
//         $row = $wpdb->get_var($sql);
        
//         if(!empty($row))
//         {
//             $wpdb->update('wp_users', array('ID'=>$newID), array('ID'=>$ao->ID));
//             $wpdb->update('wp_usermeta', array('user_id'=>$newID), array('user_id'=>$ao->ID));
//         }

// //         $oid = $ao->Name;
// //         $id = $ao->id;

//     }
//     exit;
    /*
     * @TODO: check exclude developer/hoa from query
     */
//     $query = "SELECT ".implode(",", $sels)."  FROM GPR_Owner_ID__c where CreatedDate <= 2020-07-01T00:00:00Z AND HOA_Developer__c = false  ORDER BY CreatedDate desc";

    
    $query = "SELECT ".implode(",", $sels)."  FROM GPR_Owner_ID__c where 
                    SystemModStamp >= LAST_N_DAYS:".$queryDays." 
                            AND HOA_Developer__c = false  
                ORDER BY CreatedDate desc";

//     if(isset($impowner))
//     {
//         $query = "SELECT ".implode(",", $sels)."  FROM GPR_Owner_ID__c where 
//                        Name IN ('".implode("','", $impowner)."')";
//     }
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
if(get_current_user_id() == 5)
{
    echo '<pre>'.print_r($query, true).'</pre>';
}
    $results = $sf->query($query);
//     $query = "SELECT Name FROM GPR_Owner_ID__c where
//                 Name NOT IN ('".implode("','", $impowner)."')
//                   AND HOA_Developer__c = false AND GPX_Member_VEST__c='' AND Total_Active_Contracts__c > 0 ORDER BY Name";
//     $results = $sf->query($query);d
    

//     $i = 0 ;
//     foreach($results as $rk=>$result)
//     {
//         $value = $result->fields;
//         if(!in_array($value->Name, $impowner))
//         {
//             $toInsert[] = $result;
//         }
//     }
    
//     foreach($toInsert as $result)
//     {
//         $value = $result->fields;
//         $wpdb->insert('import_owner_no_vest', array('owner'=>$value->Name));
//         if($wpdb->last_error)
//         {
//             $i++;
//         }
        
//     }
// exit;
//     $results =  $gpxRest->httpGet($query);
    $selects['Email'] = 'SPI_Email__c';
    $selects['Email1'] = 'SPI_Email__c';
    
    $testaccs = [
        '112220440',
        '112220435',
        '112220432',
        '112220427',
        '112220439',
//         '9999633761',
//         '10000000000',
    ];
    if(empty($results))
    {
        echo '<pre>'.print_r('NOT FOUND IN SF!', true).'</pre>';
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
        
        if(in_array($value->Name, $testaccs))
        {
            $value->SPI_Email__c = $value->SPI_Email__c.".test";
            
            $sql = "SELECT user_id FROM wp_GPR_Owner_ID__c WHERE Name='".$value->Name."'";
            $ru = $wpdb->get_var($sql);
            
            $wpdb->delete('wp_GPR_Owner_ID__c', array('user_id'=>$ru));
            $wpdb->delete('wp_owner_interval', array('userID'=>$ru));
            $wpdb->delete('wp_mapuser2oid', array('gpx_user_id'=>$ru));
        }
        
        if(empty($value->GPX_Member_VEST__c))
        {
//             continue;
        }
        
//         $wpdb->update('final_owner_import', array('imported'=>1), array('dae'=>$value->GPX_Member_VEST__c));
        
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
            echo '<pre>'.print_r("NO OWNERSHIPS IN SF!", true).'</pre>';
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
            if(!empty($_GET['vestID']) && !empty($_GET['split']))
            {
                //change the vestID for the owner with the email that matches 'split'
                $updateUser = get_user_by('email', $_GET['split']);
                if(empty($updateUser))
                {
                    $sql = "SELECT user_id FROM wp_GPR_Owner_ID__c WHERE SPI_Email__c='".$_GET['split']."'";
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
                    echo '<pre>'.print_r("A user with that email could not be found.", true).'</pre>';
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
                echo '<pre>'.print_r("YOUR VESTATION ISN'T QUITE RIGHT -- OWNER NOT FOUND IN VEST", true).'</pre>';
//                 $user = get_user_by('email', $value->SPI_Email__c);
            }
            if(isset($_GET['test']))
            {
                echo '<pre>'.print_r($user, true).'</pre>';
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
                    $sql = "SELECT ID FROM wp_users WHERE ID='".$value->GPX_Member_VEST__c."'";
                    $isInWP = $wpdb->get_var($sql);
                }
                
                if(empty($isInWP))
                {
//                     $wpdb->insert( $wpdb->users, array( 'ID' => $value->GPX_Member_VEST__c ) );
                    
                    $user_login = wp_slash( $value->SPI_Email__c );
                    $user_email = wp_slash( $value->SPI_Email__c );
                    $user_pass = wp_generate_password();
                    
                    $userdata = [
                        'user_login'=>$user_login,
                        'user_email'=>$user_email,
                        'user_pass'=>$user_pass,
                    ];
                    
//                     $userdata = [
                        
//                     ];
                    
//                     $userdata = compact('user_login', 'user_email', 'user_pass');
                    $user_id = wp_insert_user($userdata);;
                    
                }
                else
                {
                    if($user_id = email_exists($value->SPI_Email__c))
                    {
                        //nothing needs to happen
                    }
                    else
                    {
                        $user_id = wp_create_user( $value->SPI_Email__c, wp_generate_password(), $value->SPI_Email__c );
                    }
                }

                
                if(empty($user_id))
                {
                    $errorID = '';
                    $sql = "SELECT id FROM wp_owner_spi_error WHERE owner_id='".$value->Owner_ID__c."'";
                    $errorID = $wpdb->get_var($sql);

                    if(!empty($errorID))
                    {
                        $wpdb->update('wp_owner_spi_error', array('data'=>json_encode($value), 'updated_at'=>date('Y-m-d H:i:s')), array('id'=>$errorID));
                    }
                    else 
                    {
                        $wpdb->insert('wp_owner_spi_error', array('owner_id'=>$value->Owner_ID__c, 'data'=>json_encode($value), 'updated_at'=>date('Y-m-d H:i:s')));
                    }
                    continue;
                }
                
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
            update_user_meta($user_id, 'DAEMemberNo', $user_id);
            
            $userrole = new WP_User( $user_id );
            
//             $userrole->set_role( 'gpx_member' );
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
            /*
             * Disable for now!
             */
            /*
            $result = $wpdb->update('wp_GPR_Owner_ID__c', 
                array('user_id'=>$user_id, 'SPI_Owner_Name_1st__c'=>$fullname, 'SPI_Email__c'=> $value->SPI_Email__c, 'SPI_Home_Phone__c'=> $value->SPI_Home_Phone__c, 'SPI_Work_Phone__c'=> $value->SPI_Work_Phone__c, 'SPI_Street__c'=> $value->SPI_Street__c, 'SPI_City__c'=> $value->SPI_City__c, 'SPI_State__c'=> $value->SPI_State__c, 'SPI_Zip_Code__c'=> $value->SPI_Zip_Code__c, 'SPI_Country__c'=> $value->SPI_Country__c), 
            
                array("Name" => $check_if_exist[0]->Name));
                */
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
            echo '<pre>'.print_r($sfFields, true).'</pre>';
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
        $imported['Import ID'][] = $user_id;
     }

    if(!empty($isException))
    {
        return $user_id;
    }

//     $sql = "SELECT count(id) FROM import_owner_no_vest WHERE imported < 5";
//     $remain = $wpdb->get_var($sql);
    
//     if($remain > 0 && !isset($_REQUEST['vestID']))
//     {

//         echo '<script>location.reload();</script>';
//         exit;
//     }
    
//     wp_send_json(array('remaining'=>$remain));
//     wp_die();


    wp_send_json($imported);
    wp_die();
    
}

add_action('hook_cron_GPX_Owner', 'function_GPX_Owner');
add_action('wp_ajax_cron_GPX_Owner', 'function_GPX_Owner');


// /**
//  * Import Credit
//  */

function gpx_import_credit_C()
{
    global $wpdb;
    
    $sql = "SElECT * FROM import_owner_credits WHERE imported=0 order by RAND() LIMIT 100";
//     $sql = "SELECT * FROM `import_exceptions` WHERE `type` LIKE 'credit user' AND validated=0 LIMIT 100";
    $imports = $wpdb->get_results($sql, ARRAY_A);
    if(empty($imports))
    {
        //try the other import function
        gpx_import_credit();
    }
//     echo '<pre>'.print_r(count($imports), true).'</pre>';
    //     $imports = [
    
    //         ['member_Name'=>'431369', 'credit_amount'=>'0', 'credit_expiration_date'=>'2018-01-29', 'resort_id'=>'23','resort_name'=>'Carlsbad Inn Beach Resort', 'deposit_year'=>'2017', 'unit_type'=>'1b/4', 'check_in_date'=>'2017-01-29','credit_used'=>'1', 'status'=>'Approved'],
    //         ['member_Name'=>'616038', 'credit_amount'=>'1', 'credit_expiration_date'=>'2019-12-30', 'resort_id'=>'3030','resort_name'=>'Kauai Beach Villas', 'deposit_year'=>'2017', 'unit_type'=>'1b/4', 'check_in_date'=>'2017-09-23','credit_used'=>'0', 'status'=>'Approved'],
    //         ['member_Name'=>'391405', 'credit_amount'=>'0', 'credit_expiration_date'=>'2019-03-23', 'resort_id'=>'','resort_name'=>'The Homestead', 'deposit_year'=>'2018', 'unit_type'=>'2b/6', 'check_in_date'=>'2018-03-23','credit_used'=>'1', 'status'=>'Approved'],
    //         ['member_Name'=>'3033031', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-11-08', 'resort_id'=>'2932','resort_name'=>'Coronado Beach Resort', 'deposit_year'=>'2019', 'unit_type'=>'2b/6', 'check_in_date'=>'2019-03-03','credit_used'=>'0', 'status'=>'Approved'],
    //         ['member_Name'=>'608072', 'credit_amount'=>'1', 'credit_expiration_date'=>'2020-05-04', 'resort_id'=>'2457','resort_name'=>'Four Seasons Residence Club Scottsdale@Troon North', 'deposit_year'=>'2018', 'unit_type'=>'2b/6', 'check_in_date'=>'2018-05-04','credit_used'=>'0', 'status'=>'Approved'],
        //         ['member_Name'=>'608072', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-03-01', 'resort_id'=>'26','resort_name'=>'Red Wolf Lodge at Squaw Valley', 'deposit_year'=>'2020', 'unit_type'=>'St/4', 'check_in_date'=>'2020-03-08','credit_used'=>'0', 'status'=>'Approved'],
        //         ['member_Name'=>'405760', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-04-04', 'resort_id'=>'','resort_name'=>'Deposit Credit', 'deposit_year'=>'2020', 'unit_type'=>'2b/6', 'check_in_date'=>'2020-04-04','credit_used'=>'0', 'status'=>'Approved'],
            //         ['member_Name'=>'312477', 'credit_amount'=>'0', 'credit_expiration_date'=>'2021-04-07', 'resort_id'=>'','resort_name'=>'Deposit Credit', 'deposit_year'=>'2020', 'unit_type'=>'1b/0', 'check_in_date'=>'2020-04-07','credit_used'=>'1', 'status'=>'Approved'],
            //         ['member_Name'=>'485460', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-10-18', 'resort_id'=>'','resort_name'=>'CRADJ - USA', 'deposit_year'=>'2020', 'unit_type'=>'2b/0', 'check_in_date'=>'2020-09-30','credit_used'=>'0', 'status'=>'Approved'],
                //         ['member_Name'=>'477831', 'credit_amount'=>'0', 'credit_expiration_date'=>'2021-10-26', 'resort_id'=>'','resort_name'=>'CRADJ - USA', 'deposit_year'=>'2020', 'unit_type'=>'1b/0', 'check_in_date'=>'2020-10-26','credit_used'=>'1', 'status'=>'Approved'],
                //         ['member_Name'=>'596133', 'credit_amount'=>'0', 'credit_expiration_date'=>'2021-01-05', 'resort_id'=>'31','resort_name'=>'Hilton Grand Vacations Club at MarBrisa', 'deposit_year'=>'2020', 'unit_type'=>'2b/6', 'check_in_date'=>'2020-01-05','credit_used'=>'1', 'status'=>'Approved'],
                    //         ['member_Name'=>'605242', 'credit_amount'=>'2', 'credit_expiration_date'=>'2022-09-20', 'resort_id'=>'1437','resort_name'=>'Olympic Village Inn', 'deposit_year'=>'2021', 'unit_type'=>'1b/4', 'check_in_date'=>'2021-03-14','credit_used'=>'0', 'status'=>'Approved'],
                    //         ['member_Name'=>'98766', 'credit_amount'=>'1', 'credit_expiration_date'=>'2023-08-29', 'resort_id'=>'4','resort_name'=>'Grand Pacific Palisades', 'deposit_year'=>'2020', 'unit_type'=>'2b/7', 'check_in_date'=>'2020-01-19','credit_used'=>'0', 'status'=>'Approved'],
                        //         ['member_Name'=>'434757', 'credit_amount'=>'1', 'credit_expiration_date'=>'2023-02-20', 'resort_id'=>'1426','resort_name'=>'Sand Pebbles', 'deposit_year'=>'2021', 'unit_type'=>'St/2', 'check_in_date'=>'2021-02-20','credit_used'=>'0', 'status'=>'Approved'],
                        //         ['member_Name'=>'619344', 'credit_amount'=>'1', 'credit_expiration_date'=>'2019-10-29', 'resort_id'=>'1334','resort_name'=>'Capistrano Surfside Inn', 'deposit_year'=>'2017', 'unit_type'=>'2b/6', 'check_in_date'=>'2017-10-29','credit_used'=>'0', 'status'=>'Approved'],
                            //         ['member_Name'=>'469511', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-11-27', 'resort_id'=>'2994','resort_name'=>'Channel Island Shores', 'deposit_year'=>'2018', 'unit_type'=>'2b/6', 'check_in_date'=>'2018-03-30','credit_used'=>'0', 'status'=>'Approved'],
                            //         ['member_Name'=>'469395', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-12-06', 'resort_id'=>'2994','resort_name'=>'Channel Island Shores', 'deposit_year'=>'2019', 'unit_type'=>'2BLOFT/8', 'check_in_date'=>'2019-01-09','credit_used'=>'0', 'status'=>'Approved'],
                                //         ['member_Name'=>'498162', 'credit_amount'=>'1', 'credit_expiration_date'=>'2019-12-01', 'resort_id'=>'3019','resort_name'=>'Tahoe Beach & Ski Club', 'deposit_year'=>'2017', 'unit_type'=>'1B DLX/4', 'check_in_date'=>'2017-12-30','credit_used'=>'0', 'status'=>'Approved'],
                                //         ['member_Name'=>'199747', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-03-14', 'resort_id'=>'23','resort_name'=>'Carlsbad Inn Beach Resort', 'deposit_year'=>'2020', 'unit_type'=>'1b/6', 'check_in_date'=>'2020-03-14','credit_used'=>'0', 'status'=>'Approved'],
                                    //         ['member_Name'=>'401669', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-09-25', 'resort_id'=>'1417','resort_name'=>'Nob Hill Inn', 'deposit_year'=>'2020', 'unit_type'=>'HR/2', 'check_in_date'=>'2020-09-25','credit_used'=>'0', 'status'=>'Approved'],
                                    //         ['member_Name'=>'605702', 'credit_amount'=>'1', 'credit_expiration_date'=>'2020-06-29', 'resort_id'=>'1733','resort_name'=>'The Kona Billfisher Resort', 'deposit_year'=>'2018', 'unit_type'=>'1b/', 'check_in_date'=>'2018-06-29','credit_used'=>'0', 'status'=>'Approved'],
                                        //     ];
                                    
//                                         $imports = [
//                                         //         ['member_Name'=>'498162', 'credit_amount'=>'1', 'credit_expiration_date'=>'2019-12-01', 'resort_id'=>'3019','resort_name'=>'Tahoe Beach & Ski Club', 'deposit_year'=>'2017', 'unit_type'=>'1B DLX/4', 'check_in_date'=>'2017-12-30','credit_used'=>'0', 'status'=>'Approved'],
//                                         ];

    foreach($imports as $import)
    {

//         $import = json_decode($result['data'], true);
        
        $wpdb->update('import_owner_credits', array('imported'=>1), array('ID'=>$import['ID']));
        $sfImport = $import;
//         $args  = array(
//             'meta_key' => 'GPX_Member_VEST__c', //any custom field name
//             'meta_value' => $import['Member_Name'] //the value to compare against
//         );
        
//         $user_query = new WP_User_Query( $args );
        
//         $users = $user_query->get_results();
        
//         if(empty($users))
//         {
//             $exception = json_encode($import);
//             $wpdb->insert("reimport_exceptions", array('type'=>'credit user', 'data'=>$exception));
// //             continue;
//         }
        
//         $cid =  $users[0]->ID;
//         $user = get_user_by('login', $import['Member_Name']);
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
//                 $wpdb->update('import_exceptions', array('validated'=>2), array('id'=>$result['id']));
                continue;
            }
//             $wpdb->update('import_exceptions', array('validated'=>1), array('id'=>$result['id']));
        }
        else
        {
//             $wpddb->update('import_exceptions', array('validated'=>1), array('id'=>$result['id']));
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
            'Costa Sur Resort & Spa'=>'46872',
        ];
        
        $sql = "SELECT gprID, ResortName FROM wp_resorts WHERE id='".$resortKeyOne[$import['resort_name']]."'";
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
            if(!empty($rid))
            {
//                 $sql = "SELECT unitweek FROM wp_mapuser2oid WHERE gpx_user_id='".$cid."' AND resortID='".substr($rid, 0, 15)."'";
//                 $unit_week = $wpdb->get_var($sql);
            }
            else
            {
                $exception = json_encode($import);
                $wpdb->update('import_exceptions', array('validated'=>2), array('id'=>$result['id']));
//                 $wpdb->insert("reimport_exceptions", array('type'=>'credit resort', 'data'=>$exception));
                $wpdb->insert("final_import_exceptions", array('type'=>'credit resort', 'data'=>$exception));
                continue;
            }
        
        $email = $user->Email;
//         $email = $users[0]->Email;
//         if(empty($email))
//         {
//             $email = $users[0]->user_email;
//         }
        
        $sfDepositData = [
//             'id'=>$import['ID'],
            'Check_In_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c'=>date('Y-m-d', strtotime($import['Credit_expiratio_date'])),
            'Deposit_Year__c'=>$import['Deposit_year'],
            'Account_Name__c'=>$user->Property_Owner__c,
//             'Account_Name__c'=>$users[0]->Property_Owner__c,
            'GPX_Member__c'=>$cid,
            //             'Deposit_Date__c'=>'',
            'Resort__c'=>$rid,
            'Resort_Name__c'=>str_replace("&", "&amp;", $import['resort_name']),
            'Resort_Unit_Week__c'=>$unit_week,
            'Unit_Type__c'=>$import['unit_type'],
            'Member_Email__c'=>$email,
//             'Member_First_Name__c'=>$users[0]->FirstName1,
//             'Member_Last_Name__c'=>$users[0]->LastName1,
            'Member_First_Name__c'=>$user->FirstName1,
            'Member_Last_Name__c'=>$user->LastName1,
            'Credits_Issued__c'=>$import['credit_amount'] + $import['credit_used'],
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
        
        foreach($timport as $k=>$v)
        {
            if($k == 'status' || $k == 'credit_expiration_date' || $k == 'credit_used')
            {
                continue;
            }
            $wheres[] = $k."='".$v."'";
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
        
        echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
        
        $sf = Salesforce::getInstance();
        
        $insertID  = $wpdb->insert_id;
        
        $sfDepositData['GPX_Deposit_ID__c'] = $insertID;
        
        //         $results =  $gpxRest->httpPost($sfDepositData, 'GPX_Deposit__c');
        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';
        
        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfDepositData;
        $sfFields[0]->type = $sfType;
        
        $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);

		$wpdb->update('import_owner_credits', array('sfError'=>json_encode($sfDepositAdd)), array('ID'=>$import['ID']));

        $record = $sfDepositAdd[0]->id;
        echo '<pre>'.print_r($sfDepositAdd, true).'</pre>';
        $wpdb->update('wp_credit', array('record_id'=>$record, 'sf_name'=>$sfDepositAdd[0]->Name), array('id'=>$insertID));
        
    }
    $sql = "SElECT count(id) as cnt  FROM import_owner_credits WHERE imported=0";
//     $sql = "SELECT count(id) as cnt FROM `import_exceptions` WHERE `type` LIKE 'credit user' AND validated=0";
    $remain = $wpdb->get_var($sql);
    
    if($remain > 0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }
    
    wp_send_json(array('remaining'=>$remain));
    wp_die();
                                        
}
add_action('wp_ajax_gpx_import_credit_C', 'gpx_import_credit_C');
add_action('wp_ajax_nopriv_gpx_import_credit_C', 'gpx_import_credit_C');


/**
 * Import Credit
 */
function gpx_import_closure_credit()
{
    global $wpdb;
    $sf = Salesforce::getInstance();
    
//     $sql = "SELECT * FROM `closure_credits_import` WHERE `Deposit_Resort` = 1787  LIMIT 100";
    $sql = "SElECT * FROM closure_credits_import WHERE imported=0 AND AccoutID != '7100227'  LIMIT 100";
    $imports = $wpdb->get_results($sql, ARRAY_A);
    echo '<pre>'.print_r($imports, true).'</pre>';
    $nctooc = [
        'Member_Name'=>'AccoutID',
        'check_in_date'=>'CheckIn',
        'unit_type'=>'UnitSize',
        'week_id'=>'Week_ID',
    ];
    
//     echo '<pre>'.print_r(count($imports), true).'</pre>';
    //     $imports = [
    
    //         ['member_Name'=>'431369', 'credit_amount'=>'0', 'credit_expiration_date'=>'2018-01-29', 'resort_id'=>'23','resort_name'=>'Carlsbad Inn Beach Resort', 'deposit_year'=>'2017', 'unit_type'=>'1b/4', 'check_in_date'=>'2017-01-29','credit_used'=>'1', 'status'=>'Approved'],
    //         ['member_Name'=>'616038', 'credit_amount'=>'1', 'credit_expiration_date'=>'2019-12-30', 'resort_id'=>'3030','resort_name'=>'Kauai Beach Villas', 'deposit_year'=>'2017', 'unit_type'=>'1b/4', 'check_in_date'=>'2017-09-23','credit_used'=>'0', 'status'=>'Approved'],
    //         ['member_Name'=>'391405', 'credit_amount'=>'0', 'credit_expiration_date'=>'2019-03-23', 'resort_id'=>'','resort_name'=>'The Homestead', 'deposit_year'=>'2018', 'unit_type'=>'2b/6', 'check_in_date'=>'2018-03-23','credit_used'=>'1', 'status'=>'Approved'],
    //         ['member_Name'=>'3033031', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-11-08', 'resort_id'=>'2932','resort_name'=>'Coronado Beach Resort', 'deposit_year'=>'2019', 'unit_type'=>'2b/6', 'check_in_date'=>'2019-03-03','credit_used'=>'0', 'status'=>'Approved'],
    //         ['member_Name'=>'608072', 'credit_amount'=>'1', 'credit_expiration_date'=>'2020-05-04', 'resort_id'=>'2457','resort_name'=>'Four Seasons Residence Club Scottsdale@Troon North', 'deposit_year'=>'2018', 'unit_type'=>'2b/6', 'check_in_date'=>'2018-05-04','credit_used'=>'0', 'status'=>'Approved'],
        //         ['member_Name'=>'608072', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-03-01', 'resort_id'=>'26','resort_name'=>'Red Wolf Lodge at Squaw Valley', 'deposit_year'=>'2020', 'unit_type'=>'St/4', 'check_in_date'=>'2020-03-08','credit_used'=>'0', 'status'=>'Approved'],
        //         ['member_Name'=>'405760', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-04-04', 'resort_id'=>'','resort_name'=>'Deposit Credit', 'deposit_year'=>'2020', 'unit_type'=>'2b/6', 'check_in_date'=>'2020-04-04','credit_used'=>'0', 'status'=>'Approved'],
            //         ['member_Name'=>'312477', 'credit_amount'=>'0', 'credit_expiration_date'=>'2021-04-07', 'resort_id'=>'','resort_name'=>'Deposit Credit', 'deposit_year'=>'2020', 'unit_type'=>'1b/0', 'check_in_date'=>'2020-04-07','credit_used'=>'1', 'status'=>'Approved'],
            //         ['member_Name'=>'485460', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-10-18', 'resort_id'=>'','resort_name'=>'CRADJ - USA', 'deposit_year'=>'2020', 'unit_type'=>'2b/0', 'check_in_date'=>'2020-09-30','credit_used'=>'0', 'status'=>'Approved'],
                //         ['member_Name'=>'477831', 'credit_amount'=>'0', 'credit_expiration_date'=>'2021-10-26', 'resort_id'=>'','resort_name'=>'CRADJ - USA', 'deposit_year'=>'2020', 'unit_type'=>'1b/0', 'check_in_date'=>'2020-10-26','credit_used'=>'1', 'status'=>'Approved'],
                //         ['member_Name'=>'596133', 'credit_amount'=>'0', 'credit_expiration_date'=>'2021-01-05', 'resort_id'=>'31','resort_name'=>'Hilton Grand Vacations Club at MarBrisa', 'deposit_year'=>'2020', 'unit_type'=>'2b/6', 'check_in_date'=>'2020-01-05','credit_used'=>'1', 'status'=>'Approved'],
                    //         ['member_Name'=>'605242', 'credit_amount'=>'2', 'credit_expiration_date'=>'2022-09-20', 'resort_id'=>'1437','resort_name'=>'Olympic Village Inn', 'deposit_year'=>'2021', 'unit_type'=>'1b/4', 'check_in_date'=>'2021-03-14','credit_used'=>'0', 'status'=>'Approved'],
                    //         ['member_Name'=>'98766', 'credit_amount'=>'1', 'credit_expiration_date'=>'2023-08-29', 'resort_id'=>'4','resort_name'=>'Grand Pacific Palisades', 'deposit_year'=>'2020', 'unit_type'=>'2b/7', 'check_in_date'=>'2020-01-19','credit_used'=>'0', 'status'=>'Approved'],
                        //         ['member_Name'=>'434757', 'credit_amount'=>'1', 'credit_expiration_date'=>'2023-02-20', 'resort_id'=>'1426','resort_name'=>'Sand Pebbles', 'deposit_year'=>'2021', 'unit_type'=>'St/2', 'check_in_date'=>'2021-02-20','credit_used'=>'0', 'status'=>'Approved'],
                        //         ['member_Name'=>'619344', 'credit_amount'=>'1', 'credit_expiration_date'=>'2019-10-29', 'resort_id'=>'1334','resort_name'=>'Capistrano Surfside Inn', 'deposit_year'=>'2017', 'unit_type'=>'2b/6', 'check_in_date'=>'2017-10-29','credit_used'=>'0', 'status'=>'Approved'],
                            //         ['member_Name'=>'469511', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-11-27', 'resort_id'=>'2994','resort_name'=>'Channel Island Shores', 'deposit_year'=>'2018', 'unit_type'=>'2b/6', 'check_in_date'=>'2018-03-30','credit_used'=>'0', 'status'=>'Approved'],
                            //         ['member_Name'=>'469395', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-12-06', 'resort_id'=>'2994','resort_name'=>'Channel Island Shores', 'deposit_year'=>'2019', 'unit_type'=>'2BLOFT/8', 'check_in_date'=>'2019-01-09','credit_used'=>'0', 'status'=>'Approved'],
                                //         ['member_Name'=>'498162', 'credit_amount'=>'1', 'credit_expiration_date'=>'2019-12-01', 'resort_id'=>'3019','resort_name'=>'Tahoe Beach & Ski Club', 'deposit_year'=>'2017', 'unit_type'=>'1B DLX/4', 'check_in_date'=>'2017-12-30','credit_used'=>'0', 'status'=>'Approved'],
                                //         ['member_Name'=>'199747', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-03-14', 'resort_id'=>'23','resort_name'=>'Carlsbad Inn Beach Resort', 'deposit_year'=>'2020', 'unit_type'=>'1b/6', 'check_in_date'=>'2020-03-14','credit_used'=>'0', 'status'=>'Approved'],
                                    //         ['member_Name'=>'401669', 'credit_amount'=>'1', 'credit_expiration_date'=>'2022-09-25', 'resort_id'=>'1417','resort_name'=>'Nob Hill Inn', 'deposit_year'=>'2020', 'unit_type'=>'HR/2', 'check_in_date'=>'2020-09-25','credit_used'=>'0', 'status'=>'Approved'],
                                    //         ['member_Name'=>'605702', 'credit_amount'=>'1', 'credit_expiration_date'=>'2020-06-29', 'resort_id'=>'1733','resort_name'=>'The Kona Billfisher Resort', 'deposit_year'=>'2018', 'unit_type'=>'1b/', 'check_in_date'=>'2018-06-29','credit_used'=>'0', 'status'=>'Approved'],
                                        //     ];
                                    
//                                         $imports = [
//                                         //         ['member_Name'=>'498162', 'credit_amount'=>'1', 'credit_expiration_date'=>'2019-12-01', 'resort_id'=>'3019','resort_name'=>'Tahoe Beach & Ski Club', 'deposit_year'=>'2017', 'unit_type'=>'1B DLX/4', 'check_in_date'=>'2017-12-30','credit_used'=>'0', 'status'=>'Approved'],
//                                         ];

    foreach($imports as $import)
    {
        /*
         * 19532
         */
        foreach($nctooc as $n=>$o)
        {
            $import[$n] = $import[$o];
        }
        
        
        
//         $wpdb->update('import_owner_credits', array('imported'=>1), array('ID'=>$import['ID']));
        $sfImport = $import;
        
//         $cid =  $users[0]->ID;
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
                    
//                     $wpdb->update('closure_credits_import', array('imported'=>2), array('id'=>$import['id']));
                    continue;
                }
               
            }
//             $args  = array(
//                 'meta_key' => 'GPX_Member_VEST__c', //any custom field name
//                 'meta_value' => $import['Member_Name'] //the value to compare against
//             );
            
//             $user_query = new WP_User_Query( $args );
            
//             $users = $user_query->get_results();
            
//             if(empty($users))
//             {
//                 $exception = json_encode($import);
// //                 $wpdb->insert("reimport_exceptions", array('type'=>'credit user', 'data'=>$exception));
//                             continue;
//             }
//             else 
//             {
//                 $user = $users[0];
//             }
        }
        else
        {
//            $wpdb->update('closure_credits_import', array('imported'=>1), array('id'=>$import['ID']));
        }
        $cid = $user->ID;
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
        
        $rid = $import['Deposit_Resort'];
        $sql = "SELECT ResortName, gprID FROM wp_resorts WHERE id='".$rid."'";
        $resortDets = $wpdb->get_row($sql);
        $resortName = $resortDets->ResortName;

        
        $rid = $resortDets->gprID;
        
            if(!empty($rid))
            {
//                 $sql = "SELECT unitweek FROM wp_mapuser2oid WHERE gpx_user_id='".$cid."' AND resortID='".substr($rid, 0, 15)."'";
//                 $unit_week = $wpdb->get_var($sql);
            }
            else
            {
                $exception = json_encode($import);
//                 $wpdb->insert("reimport_exceptions", array('type'=>'credit resort', 'data'=>$exception));
//                 $wpdb->update('import_owner_credits', array('imported'=>3), array('id'=>$import['ID']));
                continue;
            }
        
            
        $import['credit_amount'] = 1;
        $import['credit_used'] = $import['credit_amount'] - $import['CRBal'];
        $import['Deposit_year'] = '2020';
        $import['status'] = 'Approved';
        
        $email = $user->Email;
//         $email = $users[0]->Email;
//         if(empty($email))
//         {
//             $email = $users[0]->user_email;
//         }

        $accountSQL = "SELECT Name from GPR_Owner_ID__c WHERE GPX_Member_VEST__c='".$cid."'";
        
        $accountResults = $sf->query($accountSQL);
        
        foreach($accountResults as $acc)
        {
            $account = $acc->fields;
            $accountName = $account->Id;
        }

        $sfDepositData = [
//             'id'=>$import['ID'],
            'Check_In_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'].'+2 years')),
            'Deposit_Year__c'=>$import['Deposit_year'],
//             'Account_Name__c'=>$accountName,
//             'Account_Name__c'=>$users[0]->Property_Owner__c,
            'GPX_Member__c'=>$cid,
            //             'Deposit_Date__c'=>'',
            'Resort__c'=>$rid,
            'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;", $resortName)),
            'Resort_Unit_Week__c'=>$unit_week,
            'Unit_Type__c'=>$import['unit_type'],
            'Member_Email__c'=>$email,
//             'Member_First_Name__c'=>$users[0]->FirstName1,
//             'Member_Last_Name__c'=>$users[0]->LastName1,
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
        
        foreach($twheres as $tw)
        {
            $iwheres[] = $tw."='".$timport[$tw]."' ";
        }
        $twhere = implode(" AND ", $iwheres);
        $sql = "SELECT id FROM wp_credit WHERE ".$twhere;
        
        echo '<pre>'.print_r($sql, true).'</pre>';
        exit;
        
        $isCredit = $wpdb->get_row($sql);
        
        if(!empty($isCredit))
        {
//             $wpdb->update('wp_credit', $timport, array('id'=>$isCredit->id));
//             $insertID  = $isCredit->id;
        }
        else
        {
//             $wpdb->insert('wp_credit', $timport);
//             $insertID  = $wpdb->insert_id;
        }
        
        $sf = Salesforce::getInstance();
        
        
        
        $sfDepositData['GPX_Deposit_ID__c'] = $insertID;
        
        //         $results =  $gpxRest->httpPost($sfDepositData, 'GPX_Deposit__c');
        $sfType = 'GPX_Deposit__c';
        $sfObject = 'GPX_Deposit_ID__c';
        
        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfDepositData;
        $sfFields[0]->type = $sfType;
        
        $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);
        echo '<pre>'.print_r($sfDepositAdd, true).'</pre>';
        $record = $sfDepositAdd[0]->id;
      
        $wpdb->update('wp_credit', array('record_id'=>$record, 'sf_name'=>$sfDepositAdd[0]->Name), array('id'=>$insertID));
        $wpdb->update('closure_credits_import', array('new_id'=>$insertID, 'imported'=>'1'), array('id'=>$import['id']));
    }
    $sql = "SELECT count(id) as cnt FROM closure_credits_import WHERE imported=2";
    $remain = $wpdb->get_var($sql);
    
    if($remain > 0)
    {
//         echo '<script>location.reload();</script>';
//         exit;
    }
    
    wp_send_json(array('remaining'=>$remain));
    wp_die();
                                        
}
add_action('wp_ajax_gpx_import_closure_credit', 'gpx_import_closure_credit');


/**
 * Import Credit
 */
function gpx_import_credit_rework($single='')
{
    global $wpdb;
    $sf = Salesforce::getInstance();
    
//     $sql = "SElECT * FROM import_credit_future_stay WHERE imported=0 order by RAND() LIMIT 1";
//     $sql = "SElECT * FROM import_credit_future_stay WHERE imported=0 ORDER BY new_id DESC LIMIT 100";
// //     $sql = "SElECT * FROM import_credit_future_stay WHERE imported=0 LIMIT 1";
//     $imports = $wpdb->get_results($sql, ARRAY_A);
    
    
    $sql = "SELECT * FROM import_credit_future_stay WHERE ID NOT IN (SELECT a.ID FROM `import_credit_future_stay` a
            INNER JOIN wp_gpxTransactions b on b.weekId=a.week_id) LIMIT 50";
    
    $limit = 100;
    $sql = "SELECT b.* FROM wp_credit a 
    INNER JOIN `import_credit_future_stay` b ON b.Member_Name=a.owner_id AND a.deposit_year=b.Deposit_year
    WHERE a.record_id IS NULL and a.status != 'DOE' and a.created_date < '2021-01-01' AND b.imported=1 AND sfError=''
    LIMIT ".$limit;
    
    $imports = $wpdb->get_results($sql, ARRAY_A);

    foreach($imports as $import)
    {
        $weekID = $import['week_id'];
        $resortID = $import['missing_resort_id'];
        if($resortID == "NULL")
        {
            $resortID = '';
        }
//         $tables = [
//             'transactions_import_two',
//             'transactions_import',
            
//         ];
//         foreach($tables as $t)
//         {
//             $sql = "SELECT id FROM ".$t." WHERE weekId='".$weekID."'";
//             $id = $wpdb->get_var($sql);
//             if(!empty($id))
//             {
//                 gpx_import_transactions($t, $id, $resortID);
//             }
//         }
        /*
         * 19532
         */
        
//         $wpdb->update('import_credit_future_stay', array('imported'=>5), array('ID'=>$import['ID']));
        $sfImport = $import;
//         $args  = array(
//             'meta_key' => 'GPX_Member_VEST__c', //any custom field name
//             'meta_value' => $import['Member_Name'] //the value to compare against
//         );
        
//         $user_query = new WP_User_Query( $args );
        
//         $users = $user_query->get_results();
        
//         if(empty($users))
//         {
//             $exception = json_encode($import);
//             $wpdb->insert("reimport_exceptions", array('type'=>'credit user', 'data'=>$exception));
// //             continue;
//         }
        
//         $cid =  $users[0]->ID;
        $user = get_user_by('ID', $import['Member_Name']);
      
        if(empty($user))
        {
            $wpdb->update('import_owner_credits', array('imported'=>2), array('ID'=>$import['ID']));
            continue;
        }
        else
        {
//             $wpdb->update('import_credit_future_stay', array('imported'=>1), array('ID'=>$import['ID']));
        }
        $cid = $user->ID;
        $unit_week = '';
        $rid = '';
        
        if(!empty($import['missing_resort_id']))
        {
            $sql = "SELECT gprID, ResortName FROM wp_resorts WHERE id='".$import['missing_resort_id']."'";
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
//                 $unit_week = $wpdb->get_var($sql);
            }
            else
            {
                //pull from the transaction
                $sql = "SELECT b.gprID, b.ResortName FROM wp_gpxTransactions a 
                        INNER JOIN wp_resorts b on a.resortId=b.ResortID
                        WHERE a.weekId='".$import['week_id']."' AND a.userID='".$cid."'";
                $resortInfo = $wpdb->get_row($sql);
                
                $rid = $resortInfo->ResortName;
                $import['resort_name'] = $resortInfo->ResortName;
                if(empty($rid))
                {
                    $exception = json_encode($import);
    //                 $wpdb->insert("reimport_exceptions", array('type'=>'credit resort', 'data'=>$exception));
                    $wpdb->update('import_owner_credits', array('imported'=>3), array('ID'=>$import['ID']));
                    continue;
                }
            }
        }
        
        $email = $user->Email;
//         $email = $users[0]->Email;
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
//             'id'=>$import['ID'],
            'Check_In_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'].'+'.$plus.' year')),
            'Deposit_Year__c'=>$import['Deposit_year'],
//             'Account_Name__c'=>$accountName,
//             'Account_Name__c'=>$users[0]->Property_Owner__c,
            'GPX_Member__c'=>$cid,
            //             'Deposit_Date__c'=>'',
            'Resort__c'=>$rid,
            'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;", $import['resort_name'])),
            'Resort_Unit_Week__c'=>$unit_week,
            'Unit_Type__c'=>$import['unit_type'],
            'Member_Email__c'=>$email,
//             'Member_First_Name__c'=>$users[0]->FirstName1,
//             'Member_Last_Name__c'=>$users[0]->LastName1,
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
            $iwheres[] = $tw."='".$timport[$tw]."' ";
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
        
//         echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
//         echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
        
        $sfDepositData['GPX_Deposit_ID__c'] = $insertID;
        
        //         $results =  $gpxRest->httpPost($sfDepositData, 'GPX_Deposit__c');
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
//         echo '<pre>'.print_r($sfDepositAdd, true).'</pre>';
        $wpdb->update('import_credit_future_stay', array('sfError'=>json_encode($sfDepositAdd)), array('ID'=>$import['ID']));
        $record = $sfDepositAdd[0]->id;
        
        $wpdb->update('wp_credit', array('record_id'=>$record, 'sf_name'=>$sfDepositAdd[0]->Name), array('id'=>$insertID));
//         $wpdb->update('import_credit_future_stay', array('new_id'=>$wpdb->insert_id), array('id'=>$import['id']));
    }
//     $sql = "SELECT count(id) as cnt FROM import_credit_future_stay WHERE imported=0";
//     $remain = $wpdb->get_var($sql);
    
    
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
    wp_die();
                                        
}
add_action('wp_ajax_gpx_credit_to_sf', 'gpx_import_credit_rework');

function gpx_import_credit($single='')
{
    global $wpdb;
    $sf = Salesforce::getInstance();
    
//     $sql = "SElECT * FROM import_credit_future_stay WHERE imported=0 order by RAND() LIMIT 1";
//     $sql = "SElECT * FROM import_credit_future_stay WHERE imported=0 ORDER BY new_id DESC LIMIT 100";
// //     $sql = "SElECT * FROM import_credit_future_stay WHERE imported=0 LIMIT 1";
//     $imports = $wpdb->get_results($sql, ARRAY_A);
    
    
    $sql = "SELECT * FROM import_credit_future_stay WHERE ID NOT IN (SELECT a.ID FROM `import_credit_future_stay` a
            INNER JOIN wp_gpxTransactions b on b.weekId=a.week_id)";
    $imports = $wpdb->get_results($sql, ARRAY_A);

    foreach($imports as $import)
    {
        echo '<pre>'.print_r($import, true).'</pre>';
        $weekID = $import['week_id'];
        $resortID = $import['missing_resort_id'];
        if($resortID == "NULL")
        {
            $resortID = '';
        }
//         $tables = [
//             'transactions_import_two',
//             'transactions_import',
            
//         ];
//         foreach($tables as $t)
//         {
//             $sql = "SELECT id FROM ".$t." WHERE weekId='".$weekID."'";
//             $id = $wpdb->get_var($sql);
//             if(!empty($id))
//             {
//                 gpx_import_transactions($t, $id, $resortID);
//             }
//         }
        /*
         * 19532
         */
        
        $wpdb->update('import_credit_future_stay', array('imported'=>5), array('ID'=>$import['ID']));
        $sfImport = $import;
//         $args  = array(
//             'meta_key' => 'GPX_Member_VEST__c', //any custom field name
//             'meta_value' => $import['Member_Name'] //the value to compare against
//         );
        
//         $user_query = new WP_User_Query( $args );
        
//         $users = $user_query->get_results();
        
//         if(empty($users))
//         {
//             $exception = json_encode($import);
//             $wpdb->insert("reimport_exceptions", array('type'=>'credit user', 'data'=>$exception));
// //             continue;
//         }
        
//         $cid =  $users[0]->ID;
        $user = get_user_by('ID', $import['Member_Name']);
      
        if(empty($user))
        {
            $wpdb->update('import_credit_future_stay', array('imported'=>2), array('ID'=>$import['ID']));
            continue;
        }
        else
        {
//             $wpdb->update('import_credit_future_stay', array('imported'=>1), array('ID'=>$import['ID']));
        }
        $cid = $user->ID;
        $unit_week = '';
        $rid = '';
        
//         if(!empty($import['new_id']))
//         {
//             $sql = "SELECT gprID, ResortName FROM wp_resorts WHERE id='".$import['new_id']."'";
//             $resortInfo = $wpdb->get_row($sql);
//             $rid = $resortInfo->gprID;
//             $import['resort_name'] = $resortInfo->ResortName;
//         }
//         if(empty($rid))
//         {
//             $resortName = $import['resort_name'];
//             $resortName = str_replace("- VI", "", $resortName);
//             $resortName = trim($resortName);
//             $sql = $wpdb->prepare("SELECT gprID, ResortName FROM wp_resorts WHERE ResortName=%s", $resortName);
            
//             $resortInfo = $wpdb->get_row($sql);
//             $rid = $resortInfo->gprID;
//             $import['resort_name'] = $resortInfo->ResortName;
            
            
//             if(!empty($rid))
//             {
//                 $sql = "SELECT unitweek FROM wp_mapuser2oid WHERE gpx_user_id='".$cid."' AND resortID='".substr($rid, 0, 15)."'";
// //                 $unit_week = $wpdb->get_var($sql);
//             }
//             else
//             {
//                 //pull from the transaction
//                 $sql = "SELECT b.gprID, b.ResortName FROM wp_gpxTransactions a 
//                         INNER JOIN wp_resorts b on a.resortId=b.ResortID
//                         WHERE a.weekId='".$import['week_id']."' AND a.userID='".$cid."'";
//                 $resortInfo = $wpdb->get_row($sql);
                
//                 $rid = $resortInfo->ResortName;
//                 $import['resort_name'] = $resortInfo->ResortName;
//                 if(empty($rid))
//                 {
//                     $exception = json_encode($import);
//     //                 $wpdb->insert("reimport_exceptions", array('type'=>'credit resort', 'data'=>$exception));
//                     $wpdb->update('import_credit_future_stay', array('imported'=>3), array('ID'=>$import['ID']));
//                     continue;
//                 }
//             }
//         }
        
        $email = $user->Email;
//         $email = $users[0]->Email;
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
//             'id'=>$import['ID'],
            'Check_In_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'].'+'.$plus.' year')),
            'Deposit_Year__c'=>$import['Deposit_year'],
//             'Account_Name__c'=>$accountName,
//             'Account_Name__c'=>$users[0]->Property_Owner__c,
            'GPX_Member__c'=>$cid,
            //             'Deposit_Date__c'=>'',
            'Resort__c'=>$rid,
            'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;", $import['resort_name'])),
            'Resort_Unit_Week__c'=>$unit_week,
            'Unit_Type__c'=>$import['unit_type'],
            'Member_Email__c'=>$email,
//             'Member_First_Name__c'=>$users[0]->FirstName1,
//             'Member_Last_Name__c'=>$users[0]->LastName1,
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
            $iwheres[] = $tw."='".$timport[$tw]."' ";
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
            echo '<pre>'.print_r($timport, true).'</pre>';
        }
        
        echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
        echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
        
        $sfDepositData['GPX_Deposit_ID__c'] = $insertID;
        
        //         $results =  $gpxRest->httpPost($sfDepositData, 'GPX_Deposit__c');
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
        echo '<pre>'.print_r($sfDepositAdd, true).'</pre>';
        $record = $sfDepositAdd[0]->id;
        
        $wpdb->update('wp_credit', array('record_id'=>$record, 'sf_name'=>$sfDepositAdd[0]->Name), array('id'=>$insertID));
        $wpdb->update('import_credit_future_stay', array('new_id'=>$wpdb->insert_id), array('id'=>$import['id']));
        
        $sql = "SELECT id, data FROM wp_gpxTransactions WHERE weekId='".$import['week_id']."' AND userID='".$cid."'";
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
//     $sql = "SELECT count(id) as cnt FROM import_credit_future_stay WHERE imported=0";
//     $remain = $wpdb->get_var($sql);
    
    
    $sql = "SELECT COUNT(ID) as cnt FROM import_credit_future_stay WHERE ID NOT IN (SELECT a.ID FROM `import_credit_future_stay` a
            INNER JOIN wp_gpxTransactions b on b.weekId=a.week_id)";
    $remain = $wpdb->get_var($sql);
    
    if($remain > 0)
    {
//         echo '<script>location.reload();</script>';
//         exit;
    }
    wp_send_json(array('remaining'=>$remain));
    wp_die();
                                        
}
add_action('wp_ajax_gpx_import_credit', 'gpx_import_credit_C');

function gpx_import_credit_future_stay($single='')
{
    global $wpdb;
    $sf = Salesforce::getInstance();
    
//     $sql = "SElECT * FROM import_credit_future_stay WHERE imported=0 order by RAND() LIMIT 1";
//     $sql = "SElECT * FROM import_credit_future_stay WHERE imported=0 ORDER BY new_id DESC LIMIT 100";
// //     $sql = "SElECT * FROM import_credit_future_stay WHERE imported=0 LIMIT 1";
//     $imports = $wpdb->get_results($sql, ARRAY_A);
    
    
//     $sql = "SELECT * FROM import_credit_future_stay WHERE ID NOT IN (SELECT a.ID FROM `import_credit_future_stay` a
//             INNER JOIN wp_gpxTransactions b on b.weekId=a.week_id)";
    $sql = "SElECT * FROM import_credit_future_stay WHERE imported=0";
    $imports = $wpdb->get_results($sql, ARRAY_A);

    foreach($imports as $import)
    {
        echo '<pre>'.print_r($import, true).'</pre>';
        $weekID = $import['week_id'];
        $resortID = $import['missing_resort_id'];
        if($resortID == "NULL")
        {
            $resortID = '';
        }
//         $tables = [
//             'transactions_import_two',
//             'transactions_import',
            
//         ];
//         foreach($tables as $t)
//         {
//             $sql = "SELECT id FROM ".$t." WHERE weekId='".$weekID."'";
//             $id = $wpdb->get_var($sql);
//             if(!empty($id))
//             {
//                 gpx_import_transactions($t, $id, $resortID);
//             }
//         }
        /*
         * 19532
         */
        
        $wpdb->update('import_credit_future_stay', array('imported'=>5), array('ID'=>$import['ID']));
        $sfImport = $import;
//         $args  = array(
//             'meta_key' => 'GPX_Member_VEST__c', //any custom field name
//             'meta_value' => $import['Member_Name'] //the value to compare against
//         );
        
//         $user_query = new WP_User_Query( $args );
        
//         $users = $user_query->get_results();
        
//         if(empty($users))
//         {
//             $exception = json_encode($import);
//             $wpdb->insert("reimport_exceptions", array('type'=>'credit user', 'data'=>$exception));
// //             continue;
//         }
        
//         $cid =  $users[0]->ID;
        $user = get_user_by('ID', $import['Member_Name']);
      
        if(empty($user))
        {
            $wpdb->update('import_credit_future_stay', array('imported'=>2), array('ID'=>$import['ID']));
            continue;
        }
        else
        {
//             $wpdb->update('import_credit_future_stay', array('imported'=>1), array('ID'=>$import['ID']));
        }
        $cid = $user->ID;
        $unit_week = '';
        $rid = '';
        
//         if(!empty($import['new_id']))
//         {
//             $sql = "SELECT gprID, ResortName FROM wp_resorts WHERE id='".$import['new_id']."'";
//             $resortInfo = $wpdb->get_row($sql);
//             $rid = $resortInfo->gprID;
//             $import['resort_name'] = $resortInfo->ResortName;
//         }
//         if(empty($rid))
//         {
//             $resortName = $import['resort_name'];
//             $resortName = str_replace("- VI", "", $resortName);
//             $resortName = trim($resortName);
//             $sql = $wpdb->prepare("SELECT gprID, ResortName FROM wp_resorts WHERE ResortName=%s", $resortName);
            
//             $resortInfo = $wpdb->get_row($sql);
//             $rid = $resortInfo->gprID;
//             $import['resort_name'] = $resortInfo->ResortName;
            
            
//             if(!empty($rid))
//             {
//                 $sql = "SELECT unitweek FROM wp_mapuser2oid WHERE gpx_user_id='".$cid."' AND resortID='".substr($rid, 0, 15)."'";
// //                 $unit_week = $wpdb->get_var($sql);
//             }
//             else
//             {
//                 //pull from the transaction
//                 $sql = "SELECT b.gprID, b.ResortName FROM wp_gpxTransactions a 
//                         INNER JOIN wp_resorts b on a.resortId=b.ResortID
//                         WHERE a.weekId='".$import['week_id']."' AND a.userID='".$cid."'";
//                 $resortInfo = $wpdb->get_row($sql);
                
//                 $rid = $resortInfo->ResortName;
//                 $import['resort_name'] = $resortInfo->ResortName;
//                 if(empty($rid))
//                 {
//                     $exception = json_encode($import);
//     //                 $wpdb->insert("reimport_exceptions", array('type'=>'credit resort', 'data'=>$exception));
//                     $wpdb->update('import_credit_future_stay', array('imported'=>3), array('ID'=>$import['ID']));
//                     continue;
//                 }
//             }
//         }
        
        $email = $user->Email;
//         $email = $users[0]->Email;
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
//             'id'=>$import['ID'],
            'Check_In_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'])),
            'Expiration_Date__c'=>date('Y-m-d', strtotime($import['check_in_date'].'+'.$plus.' year')),
            'Deposit_Year__c'=>$import['Deposit_year'],
//             'Account_Name__c'=>$accountName,
//             'Account_Name__c'=>$users[0]->Property_Owner__c,
            'GPX_Member__c'=>$cid,
            //             'Deposit_Date__c'=>'',
            'Resort__c'=>$rid,
            'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;", $import['resort_name'])),
            'Resort_Unit_Week__c'=>$unit_week,
            'Unit_Type__c'=>$import['unit_type'],
            'Member_Email__c'=>$email,
//             'Member_First_Name__c'=>$users[0]->FirstName1,
//             'Member_Last_Name__c'=>$users[0]->LastName1,
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
            $iwheres[] = $tw."='".$timport[$tw]."' ";
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
            echo '<pre>'.print_r($timport, true).'</pre>';
        }
        
        echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
        echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
        
        $sfDepositData['GPX_Deposit_ID__c'] = $insertID;
        
        //         $results =  $gpxRest->httpPost($sfDepositData, 'GPX_Deposit__c');
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
        echo '<pre>'.print_r($sfDepositAdd, true).'</pre>';
        $record = $sfDepositAdd[0]->id;
        
        $wpdb->update('wp_credit', array('record_id'=>$record, 'sf_name'=>$sfDepositAdd[0]->Name), array('id'=>$insertID));
        $wpdb->update('import_credit_future_stay', array('new_id'=>$wpdb->insert_id), array('id'=>$import['id']));
        
        $sql = "SELECT id, data FROM wp_gpxTransactions WHERE weekId='".$import['week_id']."' AND userID='".$cid."'";
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
//     $sql = "SELECT count(id) as cnt FROM import_credit_future_stay WHERE imported=0";
//     $remain = $wpdb->get_var($sql);
    
    
    $sql = "SELECT COUNT(ID) as cnt FROM import_credit_future_stay WHERE imported=0";
    $remain = $wpdb->get_var($sql);
    
    if($remain > 0)
    {
//         echo '<script>location.reload();</script>';
//         exit;
    }
    wp_send_json(array('remaining'=>$remain));
    wp_die();
                                        
}
add_action('wp_ajax_gpx_import_credit_future_stay', 'gpx_import_credit_future_stay');
// add_action('wp_ajax_nopriv_gpx_import_credit', 'gpx_import_credit');

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
            
            echo '<pre>'.print_r($sfDepositAdd, true).'</pre>';
            
            $record = $sfDepositAdd[0]->id;
            
            $wpdb->update('wp_credit', array('record_id'=>$record, 'sf_name'=>$sfDepositAdd[0]->Name), array('id'=>$import['id']));
            
            $user = get_user_by('ID', $import['owner_id']);
            
            $sfDepositData = [];
            $sfDepositData['GPX_Deposit_ID__c '] = $import['id'];
            if(!empty($user))
            {
                $email = $user->Email;
                //         $email = $users[0]->Email;
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
                
                echo '<pre>'.print_r($sfDepositAdd, true).'</pre>';
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
                
                echo '<pre>'.print_r($sfDepositAdd, true).'</pre>';
            }
            
        }
    }
    
    wp_send_json(array('added'=>''));
    wp_die();
}
add_action('wp_ajax_gpx_missed_credit_to_sf', 'gpx_missed_credit_to_sf');
function gpx_impot_partners()
{
   global $wpdb;
   
   $sql = "SELECT * FROM wp_partner WHERE user_id IS NULL";
   $rows = $wpdb->get_results($sql);
   
   foreach($rows as $row)
   {
       $user = get_user_by('email', $row->email);
       
       if(!empty($user))
       {
           $userID = $user->ID;
       }
       else
       {
           
           $userID = wp_create_user( $row->email, wp_generate_password(), $row->email );
           
           //                 $to = 'chris@4eightyeast.com';
           //                 $subject = 'Cron updated wp_GPR_Owner_ID__c';
           //                 $body = 'New Owners Added';
           //                 $headers = array('Content-Type: text/html; charset=UTF-8');
           
           //                 wp_mail( $to, $subject, $body, $headers );
       }
       
       $userdets = [
           'ID'=>$userID,
           'first_name'=>$row->name,
       ];
       
       $up = wp_update_user($userdets);
       
       update_user_meta($userID, 'first_name', $row->name);
       update_user_meta($userID, 'DAEMemberNo', $row->username);
       
       $wpdb->update('wp_partner', array('user_id'=>$userID), array('record_id'=>$row->record_id));
   }
}

add_action('wp_ajax_gpx_impot_partners', 'gpx_impot_partners');

function gpx_partner_credits()
{
    global $wpdb;
   
    $sql = "SELECT * FROM wp_gpxTransactions";
    
    
    $sql = "SELECT record_id FROM import_partner_credits WHERE imported=0 LIMIT 1000";
    $rows = $wpdb->get_results($sql);

    
    foreach($rows as $row)
    {
        $wpdb->update('import_partner_credits', array('imported'=>1), array('id'=>$row->id));
        
        $sql = "SELECT id FROM wp_gpxTransactions WHERE weekId='".$row->record_id."'";
        $dweek = $wpdb->get_var($sql);
        
        $dp = [
            'transactionID'=>$dweek,
            'record_id'=>$row->record_id,
        ];
       
        $exception = json_encode($row);
        $wpdb->insert("reimport_exceptions", array('type'=>'delete trade partner', 'data'=>$exception));
        
        //temp delete
        $wpdb->delete('wp_gpxTransactions', array('weekId'=>$row->record_id));
        $wpdb->delete('wp_room', array('record_id'=>$row->record_id));
        
        
        $raw_spd = $row->source_partner_id;
        if($raw_spd == 'GPR')
        {
            $raw_spd = '';
        }
        else 
        {
            $sql = "SELECT user_id FROM wp_partner WHERE username='".$raw_spd."'";
            $spd = $wpdb->get_var($sql);
            
            if(empty($spd))
            {
                $exception = json_encode($row);
//                 $wpdb->insert("reimport_exceptions", array('type'=>'partner credit source partner id', 'data'=>$exception));
                continue;
            }
        }
        
        $gpd = '';
        
        if(!empty($row->Given_to_Partner_id))
        {
            $sql = "SELECT user_id, name FROM wp_partner WHERE username='".$row->Given_to_Partner_id."'";
            $gpdRow = $wpdb->get_row($sql);
            
            $gpd = $gpdRow->user_id;
            $gpName = $gpdRow->name;

            if(empty($gpd))
            {
                $exception = json_encode($row);
                $wpdb->insert("reimport_exceptions", array('type'=>'partner credit given partner id', 'data'=>$exception));
                continue;
            }

        }

        $resortID = $row->resort;
        $sql = "SELECT resortID, ResortName FROM wp_resorts WHERE id='".$resortID."'";
        $resort = $wpdb->get_row($sql);
        
        $daeResortID = $resort->resortID;
        $ResortName = $resort->ResortName;
        
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

        $sql = "SELECT record_id FROM wp_room WHERE record_id='".$row->record_id."'";
        $room = $wpdb->get_var($sql);
        
        $active = '0';
       
        if(trim($row->Active) == '1')
        {
            $active = '1';
        }
        
        $wp_room = [
            'record_id'=>$row->record_id,
            'active_specific_date' => date("Y-m-d 00:00:00", strtotime($row->active_specific_date)),
            'check_in_date' => date('Y-m-d 00:00:00', strtotime($row->check_in_date)),
            'check_out_date' => date('Y-m-d 00:00:00', strtotime($row->check_out_date)),
            'resort' => $resortID,
            'unit_type' => $unitID,
            'source_num' => $row->source_num,
            'source_partner_id' => $spd,
            'sourced_by_partner_on' => date('Y-m-d 00:00:00', strtotime($row->sourced_by_partner_on)),
            'resort_confirmation_number' => $row->resort_confirmation_number,
            'active' => $active,
            'availability' => $row->availability,
            'available_to_partner_id' => '0',
            'type' => $row->Type,
            'active_rental_push_date' => date('Y-m-d', strtotime($row->active_rental_push_date)),
            'price' => '0',
            'points' => NULL,
            'note' => '',
            'given_to_partner_id' => $row->Given_to_Partner_id,
            'import_id' => '0',
            'active_type' => '0',
            'active_week_month' => '0',
            'create_by' => '5',
            'archived' => '0',
            ];
        $sql = "SELECT record_id FROM wp_room WHERE record_id='".$row->record_id."'";
        $week = $wpdb->get_row($sql);
        
//         if(!empty($week))
//         {
//             $wpdb->update('wp_room', $wp_room, array('record_id'=>$row->record_id));
//         }
//         else 
//         {
//             $wpdb->insert('wp_room', $wp_room);
//         }
        
        if(empty($gpd))
        {
            //was this transaction added before?
            $sql = "SELECT id, weekId, data FROM wp_gpxTransactions WHERE weekId='".$row->record_id."'";
            $dups = $wpdb->get_results($sql);
            foreach($dups as $dup)
            {
                $dlt = false;
                $dupid = $dup->id;
                $dupJSON = json_decode($dup->data);
                if(empty($dupJSON->MemberNumber))
                {
//                     echo '<pre>'.print_r("DELETE: ".$dupid, true).'</pre>';
//                     exit;
                    $wpdb->delete('wp_gpxTransactions', array('id'=>$dupid));
                    $dlt = true;
                    break;
                }
                $sql = "SELECT weekId FROM transactions_import WHERE MemberNumber='".$dupJSON->MemberNumber."' AND weekId='".$row->record_id."'";
                $nd = $wpdb->get_var($sql);
                if(empty($nd))
                {
//                     echo '<pre>'.print_r($dupJSON, true).'</pre>';
//                     echo '<pre>'.print_r("DELETE: ".$dupid, true).'</pre>';
//                     exit;
                        $wpdb->delete('wp_gpxTransactions', array('id'=>$dupid));
                        $dlt = true;
                        break;
                }
            }
            //add to the exception report for traci
            if($dlt)
            {
                $exception = json_encode($dups);
                $wpdb->insert("reimport_exceptions", array('type'=>'Transaction Import Error Transaction Deleted', 'data'=>$exception));
            }
        }
        else
        {
            continue;
            $cpo = "NOT TAKEN";
            
            $data = [
                "MemberNumber"=>$row->Given_to_Partner_id,
                "MemberName"=>$gpName,
                "GuestName"=>'',
                "Adults"=>2,
                "Children"=>0,
                "UpgradeFee"=>'',
                "CPO"=>$cpo,
                "CPOFee"=>'',
                "Paid"=>'0',
                "Balance"=>"0",
                "ResortID"=>$daeResortID,
                "ResortName"=>$ResortName,
                "room_type"=>$row->Unit_Type,
                "WeekType"=>'Exchange',
                "sleeps"=>$sleeps,
                "bedrooms"=>$beds,
                "Size"=>$unitType,
                "noNights"=>"7",
                "checkIn"=>date('Y-m-d', strtotime($row->check_in_date)),
                "processedBy"=>5,
                'actWeekPrice' => '0',
                'actcpoFee' => '0',
                'actextensionFee' => '0',
                'actguestFee' => '0',
                'actupgradeFee' => '0',
                'acttax' => '0',
                'actlatedeposit' => '0',
            ];
            
            $wp_gpxTransactions = [
                'transactionType' => 'booking',
                'cartID' => $gpd.'-'.$row->record_id,
                'sessionID' => '',
                'userID' => $gpd,
                'resortID' => $daeResortID,
                'weekId' => $row->record_id,
                'check_in_date' => date('Y-m-d', strtotime($row->check_in_date)),
                'datetime' => date('Y-m-d', strtotime($row->sourced_by_partner_on)),
                'depositID' => NULL,
                'paymentGatewayID' => '',
                'transactionRequestId' => NULL,
                'transactionData' => '',
                'sfid' => '0',
                'sfData' => '',
                'data' => json_encode($data),
                ];
            
            $sql =  "SELECT id FROM wp_gpxTransactions WHERE weekId='".$row->record_id."'";
            $weekID = $wpdb->get_var($sql);
            if(get_current_user_id() == 5)
            {
                echo '<pre>'.print_r($wpd_gpxTransaction, true).'</pre>';
                exit;
            }
            if(!empty($weekID))
            {
                $exception = json_encode($row);
                $wpdb->insert("reimport_exceptions", array('type'=>'partner import duplicate week', 'data'=>$exception));
                continue;
            }
            
            $wpdb->insert('wp_gpxTransactions', $wp_gpxTransactions);
            if($wpdb->last_error)
            {
                $exception = json_encode($row);
                $wpdb->insert("reimport_exceptions", array('type'=>'partner credit insert transaction', 'data'=>$exception));
            }
        }
    }
    $sql = "SELECT count(id) as cnt FROM import_partner_credits WHERE imported=0 LIMIT 100";
    $remain = $wpdb->get_var($sql);
    
    if($remain > 0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }
    
    wp_send_json(array('remaining'=>$remain));
    wp_die();
}
add_action('wp_ajax_gpx_partner_credits', 'gpx_partner_credits');

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
            echo '<pre>'.print_r($import, true).'</pre>';
            echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
            exit;
        }
        $id = $wpdb->insert_id;
        
        $sql = "SELECT * FROM temp_usermeta WHERE user_id='".$row->user_id."'";
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
                echo '<pre>'.print_r($importMeta, true).'</pre>';
                echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
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

function gpx_import_rooms()
{
    global $wpdb;
    
    $sql = "SELECT * FROM import_rooms WHERE imported=0 LIMIT 200";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {

        $wpdb->update('import_rooms', array('imported'=>1), array('id'=>$row->id));
        
        $resortName = $row->ResortName;
        $resortName = str_replace("- VI", "", $resortName);
        $resortName = trim($resortName);
        $sql = $wpdb->prepare("SELECT id, resortID FROM wp_resorts WHERE ResortName=%s", $resortName);
        $resort = $wpdb->get_row($sql);

        if(empty($resort))
        {
            $exception = json_encode($row);
            $wpdb->insert("reimport_exceptions", array('type'=>'trade partner inventory resort', 'data'=>$exception));
            continue;
        }
        else
        {
            $resortID = $resort->id;
            $daeResortID = $resort->resortID;
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
        
        $active = '1';
        if($row->active == 'FALSE')
        {
            $active = '0';
        }
        $type ='3';
        if(trim($row->Type) == 'Exchange')
        {
            $type = '1';
        }
        $spi = '0';
        if(!empty($row->source_partner_id))
        {
            $spi = $row->source_partner_id;
        }
        $wpdb->delete('wp_room', array('record_id'=>$row->record_id));
        $record_id = trim($row->record_id);
        $wp_room = [
            'record_id'=>$record_id,
            'active_specific_date' => date("Y-m-d 00:00:00"),
            'check_in_date' => date('Y-m-d 00:00:00', strtotime($row->StartDate)),
            'check_out_date' => date('Y-m-d 00:00:00', strtotime($row->StartDate.' +7 days')),
            'resort' => $resortID,
            'unit_type' => $unitID,
            'source_num' => '2',
            'source_partner_id' => $spi,
            'sourced_by_partner_on' => '',
            'resort_confirmation_number' => '',
            'active' => $active,
            'availability' => '1',
            'available_to_partner_id' => '0',
            'type' => $type,
            'active_rental_push_date' => date('Y-m-d', strtotime($row->active_rental_push_date)),
            'price' => $row->Price,
            'points' => NULL,
            'note' => 'From: '.$row->note,
            'given_to_partner_id' => NULL,
            'import_id' => '0',
            'active_type' => '0',
            'active_week_month' => '0',
            'create_by' => '5',
            'archived' => '0',
            ];
        
        $sql = "SELECT record_id FROM wp_room WHERE record_id='".$record_id."'";
        $week = $wpdb->get_row($sql);
        if(!empty($week))
        {
            $wpdb->update('wp_room', $wp_room, array('record_id'=>$record_id));
        }
        else 
        {
            $wpdb->insert('wp_room', $wp_room);
        }
    }
    
    $sql = "SELECT COUNT(id) as cnt FROM import_rooms WHERE imported=0";
    $remain = $wpdb->get_var($sql);
    
    if($remain > 0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }
    
    wp_send_json(array('remaining'=>$remain));
    wp_die();
}
add_action('wp_ajax_gpx_import_rooms', 'gpx_import_rooms');

function gpx_import_transactions_manual($table='transactions_import_two', $id='', $resort='')
{
    global $wpdb;
    
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
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
    
//     $sql = "SELECT id FROM ".$table." where weekId NOT IN 
//                 ( SELECT a.weekId  FROM ".$table." a
//                 INNER JOIN wp_gpxTransactions b ON b.weekId=a.weekId)";
//     $r = $wpdb->get_results($sql);
    
//     foreach($r as $v)
//     {
//         $wpdb->update($table, array('imported'=>0), array('id'=>$v->id));
//     }
    
    $where = 'imported=0';
    if(!empty($id))
    {
        $where = 'id='.$id;
    }

    $sql = "SELECT * FROM ".$table." WHERE ".$where." ORDER BY RAND() LIMIT 40";
    $rows = $wpdb->get_results($sql);
    
    foreach($rows as $row)
    {
//         echo '<pre>'.print_r($row, true).'</pre>';
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
            $wpdb->insert("final_import_exceptions", array('type'=>$tt.' guest formula error', 'data'=>$exception));
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
            'El Dorado Casitas Royale by Karisma'=>'46905',
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
        
        $sql = "SELECT user_id FROM wp_GPR_Owner_ID__c WHERE user_id='".$row->MemberNumber."'";
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
                    $wpdb->insert("final_import_exceptions", array('type'=>$tt.' member name', 'data'=>$exception));
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
                $wpdb->insert("final_import_exceptions", array('type'=>$tt.' duplicate week transaction not cancelled', 'data'=>$exception));
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
            echo '<pre>'.print_r($remain, true).'</pre>';
            echo '<script>location.reload();</script>';
            exit;
        }
        
        wp_send_json(array('remaining'=>$remain));
        wp_die();
        return true;
}

function gpx_import_transactions($table='transactions_import_two', $id='', $resort='')
{
    global $wpdb;
    
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
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
    
//     $sql = "SELECT id FROM ".$table." where weekId NOT IN 
//                 ( SELECT a.weekId  FROM ".$table." a
//                 INNER JOIN wp_gpxTransactions b ON b.weekId=a.weekId)";
//     $r = $wpdb->get_results($sql);
    
//     foreach($r as $v)
//     {
//         $wpdb->update($table, array('imported'=>0), array('id'=>$v->id));
//     }
    
    $where = 'imported=0';
    if(!empty($id))
    {
        $where = 'id='.$id;
    }

    $sql = "SELECT * FROM ".$table." WHERE ".$where." ORDER BY RAND() LIMIT 40";
    $rows = $wpdb->get_results($sql);
    
    foreach($rows as $row)
    {
//         echo '<pre>'.print_r($row, true).'</pre>';
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
            $wpdb->insert("final_import_exceptions", array('type'=>$tt.' guest formula error', 'data'=>$exception));
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
            'El Dorado Casitas Royale by Karisma'=>'46905',
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
        
        $sql = "SELECT user_id FROM wp_GPR_Owner_ID__c WHERE user_id='".$row->MemberNumber."'";
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
                    $wpdb->insert("final_import_exceptions", array('type'=>$tt.' member name', 'data'=>$exception));
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
                $wpdb->insert("final_import_exceptions", array('type'=>$tt.' duplicate week transaction not cancelled', 'data'=>$exception));
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
            echo '<pre>'.print_r($remain, true).'</pre>';
            echo '<script>location.reload();</script>';
            exit;
        }
        
        wp_send_json(array('remaining'=>$remain));
        wp_die();
        return true;
}
add_action('wp_ajax_gpx_import_transactions', 'gpx_import_transactions');

function gpx_owner_monetary_credits()
{
    global $wpdb;
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
//     $sql = "SELECT * FROM owner_monetary_credits WHERE imported=1 LIMIT 100";
    $sql = "SELECT * FROM wp_gpxOwnerCreditCoupon_owner WHERE ";
    
    $sql = "SELECT new_id, old_id FROM vest_rework_users WHERE old_id IN (SELECT ownerID FROM wp_gpxOwnerCreditCoupon_owner)";
    
    $imports = $wpdb->get_results($sql);
    
    foreach($imports as $import)
    {
        $wpdb->update('wp_gpxOwnerCreditCoupon_owner', array('ownerID'=>$import->new_id), array('ownerID'=>$import->old_id));
        //         $wpdb->update('owner_monetary_credits', array('imported'=>2), array('id'=>$import->id));
//         $slug = 'PV'.$import->AccountID;
        
//         $sql = "SELECT ownerID FROM wp_gpxOwnerCreditCoupon_owner WHERE couponID='".$import->id."'";
//         $row = $wpdb->get_row($sql);
//         $accountID = str_replace("PV", "", $import->name);
        
//         if(empty($row))
//         {
//             $wpdb->update('wp_gpxOwnerCreditCoupon_owner', array('ownerID'=>$accountID), array('couponID'=>$import->id));
//             //add random string to the end and check again
// //             $rand = rand(1, 1000);
// //             $slug = $slug.$rand;
// //             $sql = "SELECT id FROM wp_gpxOwnerCreditCoupon WHERE couponcode='".$slug."'";
// //             $row = $wpdb->get_row($sql);
// //             if(!empty($row))
// //             {
// //                 //add random string to the end and check again
// //                 $rand = rand(1, 1000);
// //                 $slug = $slug.$rand;
// //             }
//         }
//         else
//         {
// //             $wpdb->update('owner_monetary_credits', array('imported'=>3), array('id'=>$import->id));
//         }
//         $occ = [
//             'Name'=>'PV'.$import->AccountID,
//             'Slug'=>$slug,
//             'Active'=>1,
//             'singleuse'=>0,
//             'created_date'=>$import->Business_Date,
//             'amount'=>$import->Amount,
//             'owners'=>[$import->AccountID],
//         ];
        
//         $coupon = $gpx->promodeccouponsadd($occ);
    }
    
    $sql = "SELECT count(id) as cnt FROM owner_monetary_credits WHERE imported=1 LIMIT 100";
    $remain = $wpdb->get_var($sql);
    
    if($remain > 0)
    {
        echo '<script>location.reload();</script>';
        exit;
    }
    
    wp_send_json(array('remaining'=>$remain));
    wp_die();
}
add_action('wp_ajax_gpx_owner_monetary_credits', 'gpx_owner_monetary_credits');

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
    wp_die();
}
add_action('wp_ajax_gpx_import_owner_credits', 'gpx_import_owner_credits');

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
    $fileLoc = '/var/www/reports/'.$type.'.csv';
    $file = fopen($fileLoc, 'w');
    
    $sql = "SELECT type, data FROM reimport_exceptions WHERE type='".$type."'";
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
//             foreach($v as $kk=>$vv)
//             {
//                 $tvv[$kk] = $vv;
//             }
           $ov[] = $v;
//             $ov = '"'.implode('","',$v).'"';
        }
        $ov[] = $tvv;
    }
    $list = array();
    $list[] = $th[$type];
    
//     $list[] = implode(',', $th);
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

function sf_import_resorts($resortid='')
{
    global $wpdb;

//     require_once GPXADMIN_API_DIR.'/functions/class.salesforce.php';
//     $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
    $sf = Salesforce::getInstance();
    
    $selects = [
        'Id',
        'Name',
//         'GPX_Resort_ID__c',
    ];
    $query =  "select ".implode(", ", $selects)." from Resort__c  where
                    SystemModStamp >= LAST_N_DAYS: 14";
    $results = $sf->query($query);
    $checked = [];
    
    foreach($results as $result)
    {
        $fields = $result->fields;
        $id = $result->Id;
        
//         $newcheck = str_replace(' Resort', '', $fields->Name);
        
        $sql = 'SELECT * FROM wp_resorts WHERE ResortName LIKE "'.$fields->Name.'%"';
        $row = $wpdb->get_row($sql);
        if(!empty($row))
        {
//             $an = [];
//             $updateResorts['resort'] = $row->id;
//             if(!empty($row->gprID))
//             {
//                 //This is set
//                 $dataset['already set'][] = $fields->Name;
//             }
//             else 
//             {
                $wpdb->update('wp_resorts', array('gprID'=>$id), array('id'=>$row->id));
                $dataset['just set'][] = $fields->Name;
//             }
        }
        else
        {
            $dataset['no match'][] = $sql;
        }
        
        $updateResorts['alertResult'] = json_encode($an);
        
        $wpdb->insert('resort_import', $updateResorts);
        
    }
    wp_send_json($dataset);
    wp_die();
}
add_action('wp_ajax_sf_import_resorts', 'sf_import_resorts');

function sf_update_resorts($resortid='')
{
    global $wpdb;

//     require_once GPXADMIN_API_DIR.'/functions/class.salesforce.php';
//     $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
    $sf = Salesforce::getInstance();
    
    $selects = [
        'Id',
        'Name',
        'GPX_Resort_ID__c',
    ];
//     $query =  "select ".implode(", ", $selects)." from Resort__c";
//     $results = $sf->query($query);
    
//     $checked = [];
    
    $wheres[] = 'active=1';
    
    if(isset($_REQUEST['id']))
    {
        $id = $_REQUEST['id'];
    }
    if(!empty($id))
    {
        
        $wheres[] = ' AND id='.$id;
    }
    if(!empty($resortid))
    {
        $wheres[] = " AND ResortID='".$resortid."'";
    }
    
//     $sql = "SELECT * FROM wp_resorts WHERE ".implode(" ", $wheres);
//     if(isset($_REQUEST['grouped']))
//     {
        $sql = "SELECT * FROM `wp_resorts` WHERE sf_GPX_Resort__c IS NULL and gprID != ''
                LIMIT 10";
//     }

	if(isset($id))
    {
    	$sql = "SELECT * FROM wp_resorts WHERE ".implode(" ", $wheres);
    }
    
    if(isset($_REQUEST['address_refresh']))
    {
        $sql = "SELECT * FROM wp_resorts WHERE Town='' AND Region='' ORDER BY `wp_resorts`.`lastUpdate` DESC";
    }

    $results = $wpdb->get_results($sql);
    
    if(!empty($id))
    {
        echo '<pre>'.print_r($results, true).'</pre>';
    }
    
    foreach($results as $row)
    {
        if(isset($_REQUEST['address_refresh']))
        {
            $thisResortID = $row->id;
            $refresh = [
                'Address1',
                'Town',
                'Region',
                'Country',
                'PostCode',
                'Phone',
                'WebLink',
            ];
            
            foreach($refresh as $rf)
            {
                $sql = "SELECT meta_value FROM wp_resorts_meta WHERE meta_key='".$rf."' AND ResortID='".$row->ResortID."'";
                $refreshMeta = $wpdb->get_var($sql);
                if(!empty($refreshMeta))
                {
                    $rmJson = json_decode($refreshMeta);
                    foreach($rmJson as $rmj)
                    {
                        $end = end($rmj);
                        $row->$rf = $end->desc;
                    }
                }
            }
            
            $update = $row;
            unset($update->id);
            echo '<pre>'.print_r($update, true).'</pre>';
            $refreshUPdate = $wpdb->update('wp_resorts',(array) $update, array('id'=>$thisResortID));
            
            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
            echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
        }
//         $fields = $result->fields;
//         $id = $result->Id;
        
//         $newcheck = str_replace(' Resort', '', $fields->Name);
        
//         $sql = 'SELECT * FROM wp_resorts WHERE ResortName LIKE "'.$newcheck.'%"';
//         $row = $wpdb->get_row($sql);
        if(!empty($row))
        {
            $an = [];
            $updateResorts['resort'] = $row->id;
            if(!empty($row->gprID))
            {
                //This is set
                $dataset['already set'][] = $fields->Name;
            }
            else 
            {
                $wpdb->update('wp_resorts', array('gprID'=>$row->gprID), array('id'=>thisResortID));
                $dataset['just set'][] = $fields->Name;
            }
            
            $toSend = [
                'Name'=>'ResortName',
                'GPX_Resort_ID__c'=>'id',
                'Additional_Info__c'=>'AdditionalInfo',
                'Address_Cont__c'=>'Address2',
                'Check_In_Days__c'=>'CheckInDays',
                'Check_In_Time__c'=>'CheckInEarliest',
                'Check_Out_Time__c'=>'CheckOutLatest',
                'City__c'=>'Town',
                'Closest_Airport__c'=>'Airport',
                'Country__c'=>'Country',
                'Directions__c'=>'Directions',
                'Fax__c'=>'Fax',
                'Phone__c'=>'Phone',
                'Resort_Description__c'=>'Description',
                'Resort_Website__c'=>'Website',
                //                     'RSF__c'=>'CheckInDays',
                'State_Region__c'=>'Region',
                'Street_Address__c'=>'Address1',
                'Zip_Postal_Code__c'=>'PostCode',
            ];
            
//             $sql = "SELECT '".implode("', '", $toSend)."' FROM wp_resorts WHERE id='".$row->id."'";
//             $resort = $wpdb->get_row($sql);
            
            foreach($toSend as $sk=>$sv)
            {
                $sfResortData[$sk] = str_replace("&", "and", $row->$sv);
                $breaks = array("<br />","<br>","<br/>");
                $sfResortData[$sk] = str_ireplace($breaks, "\r\n", $sfResortData[$sk]); 
            }
            
            $sfWeekAdd = '';
            $sfAdd = '';
            $sfType = 'GPX_Resort__c';
            $sfObject = 'GPX_Resort_ID__c';
            
            $sfFields = [];
            $sfFields[0] = new SObject();
            $sfFields[0]->fields = $sfResortData;
            $sfFields[0]->type = $sfType;
           
            $sfResortAdd = $sf->gpxUpsert($sfObject, $sfFields);
//             echo '<pre>'.print_r($sfResortAdd, true).'</pre>';
            
            $updateResorts['resortResult'] = json_encode($sfResortAdd);
            
            if(!empty($id))
            {
                echo '<pre>'.print_r($updateResorts, true).'</pre>';
            }
            
            $sfID = $sfResortAdd[0]->id;
          
            $wpdb->update('wp_resorts', array('sf_GPX_Resort__c'=>$sfID), array('id'=>$row->id));

            $sql = "SELECT id, meta_value FROM wp_resorts_meta WHERE ResortID='".$row->ResortID."' AND meta_key='AlertNote'";
            $meta = $wpdb->get_row($sql);
           
            if(!empty($meta))
            {
                $noted[$row->ResortID] = $row->ResortID;
                $alertNotes = json_decode($meta->meta_value, true);
               
                $notes = [];

                foreach($alertNotes as $rmdate=>$rmvalues)
                {
                    $rmdates = explode("_", $rmdate);
                 
                    if(isset($rmdates[1]))
                    {
                        if($rmdates[1] < strtotime('NOW'))
                        {
                            if(count($notes) == 0)
                            {
                                unset($noted[$row->ResortID]);
                            }
                            continue;
                        }
                    }
                    $sfnamevalues = $rmvalues;
                    $sfAlertNote = [];
                    $sfAlertNote['GPX_Resort__c'] = $sfID;
                    $sfAlertNote['Start_Date__c'] = date('Y-m-d', $rmdates[0]);
                    if(!empty($rmdates[1]))
                    {
                        $sfAlertNote['End_Date__c'] = date('Y-m-d', $rmdates[1]);
                    }
                    if(isset($rmvalues['desc']))
                    {
                        $sfAlertNote['Alert_Notice__c'] = $rmvalues['desc'];
                        
                    }
                    elseif(is_array($rmvalues))
                    {
                        $end = end($rmvalues);
                        foreach($rmvalues as $rmv)
                        {
                            if(isset($rmv['desc']))
                            {
                                $sfAlertNote['Alert_Notice__c'] = $rmv['desc'];
                            }
                        }
                    }
                    else 
                    {
                        $end = end($rmvalues);
                        $sfAlertNote['Alert_Notice__c'] = $end['desc'];
                    }
                    
                    $notes[] = $sfAlertNote;
                    
                    
                    $sfAlertNote['Alert_Notice__c'] = str_replace("&", "and", $sfAlertNote['Alert_Notice__c']);
                    $sfAlertNote['Alert_Notice__c'] = str_replace("<b>", "", $sfAlertNote['Alert_Notice__c']);
                    $breaks = array("<br />","<br>","<br/>");
                    $sfAlertNote['Alert_Notice__c'] = str_ireplace($breaks, "\r\n", $sfAlertNote['Alert_Notice__c']);
                    $sfAlertNote['Alert_Notice__c'] = strip_tags($sfAlertNote['Alert_Notice__c']);
                    
                    $sfType = 'Resort_Alert_Note__c';
                    
                    
                    $sfFields = [];
                    $sfFields[0] = new SObject();
                    
                    
                    $sfFields[0]->type = $sfType;
               
                    if(isset($sfnamevalues['sfname']))
                    {
                        $sfAlertNote['Name'] = $rmvalues['sfname'];
                        
                        $sfFields[0]->fields = $sfAlertNote;
                        
//                         $sfalertnoteEdit = $sf->gpxUpsert('Name', $sfFields);
                       
                        $an[] = $sfalertnoteEdit;
                    }
                    else
                    {
                        $sfFields[0]->fields = $sfAlertNote;
//                         $sfalertnoteAdd = $sf->gpxCreate($sfFields);
                       
                        $an[] = $sfalertnoteAdd;
                        
                        //we need to add the name back into this record and save it
                        $notesQuery = "SELECT Name FROM Resort_Alert_Note__c WHERE ID='".$sfalertnoteAdd[0]->id."'";
                        $notesResults = $sf->query($notesQuery);
                      
                        foreach($notesResults as $nr)
                        {
                            $noteFields = $nr->fields;
                            $alertNotes[$rmdate]['sfname'] = $noteFields->Name; 
                        }
                    }
                  
                }
//               echo '<pre>'.print_r($an, true).'</pre>';
                if(isset($noteFields))
                {
                    $wpdb->update('wp_resorts_meta', array('meta_value'=>json_encode($alertNotes)), array('id'=>$meta->id));
                    
                }
                
                
            }
            else
            {
                $sfAlertNote = [];
                $sfAlertNote['GPX_Resort__c'] = $sfID;
                $sfAlertNote['Start_Date__c'] = date('Y-m-d');
                
                $sfAlertNote['Alert_Notice__c'] = str_replace("&", "and", $row->AlertNote);
                $sfAlertNote['Alert_Notice__c'] = str_replace("<b>", "", $sfAlertNote['Alert_Notice__c']);
                $breaks = array("<br />","<br>","<br/>");
                $sfAlertNote['Alert_Notice__c'] = str_ireplace($breaks, "\r\n", $sfAlertNote['Alert_Notice__c']);
                $sfAlertNote['Alert_Notice__c'] = strip_tags($sfAlertNote['Alert_Notice__c']);
                
                
                $sfType = 'Resort_Alert_Note__c';
                
                
                
                $sfFields = [];
                $sfFields[0] = new SObject();
                
                $sfFields[0]->type = $sfType;
                
                $sfFields[0]->fields = $sfAlertNote;
                unset($sfFields[0]->any);
//                 $sfalertnoteAdd = $sf->gpxCreate($sfFields);

                $an[] = $sfalertnoteAdd;
            }
        }
        else
        {
            $dataset['no match'][] = $sql;
        }
        
        $updateResorts['alertResult'] = json_encode($an);
        
        $wpdb->insert('resort_import', $updateResorts);
        
    }
    
    $sql = "SELECT count(id) as cnt FROM `wp_resorts` WHERE sf_GPX_Resort__c IS NULL and gprID != ''";
    $remain = $wpdb->get_var($sql);
    
    if($remain > 0)
    {
//         echo '<script>location.reload();</script>';
//         exit;
    }
    
    wp_send_json(array('remaining'=>$remain));
    wp_die();
}
add_action('wp_ajax_sf_update_resorts', 'sf_update_resorts');

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

function hook_credit_import($atts = '')
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
//     require_once GPXADMIN_API_DIR.'/functions/class.restsaleforce.php';
//     $gpxRest = new RestSalesforce();
   
   require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
   $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
   
//    require_once GPXADMIN_API_DIR.'/functions/class.salesforce.php';
//    $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
   $sf = Salesforce::getInstance();
//     $results =  $gpxRest->httpGet("select Account_Name__c, Check_In_Date__c, Credit_Extension_Date__c, Credits_Issued__c, Credits_Used__c, Deposit_Date__c, Deposit_Year__c, Expiration_Date__c, GPX_Deposit_ID__c, GPX_Member__c,  GPX_Resort__c, Resort_Name__c, Resort_Unit_Week__c, Status__c, Unit_Type__c from GPX_Deposit__c");
 
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
   
//    $query =  "select ".implode(", ", $selects)." from GPX_Transaction__c WHERE Status__c='Denied' AND  SystemModStamp >= LAST_N_DAYS:".$queryDays." ";
   $query =  "select ".implode(", ", $selects)." from GPX_Transaction__c WHERE SystemModStamp > ".$d.'T'.$t."Z AND Status__c='Denied'";
   $results = $sf->query($query);

   if(!empty($results))
   {
       foreach($results as $result)
       {
           
           $value = $result->fields;
           
           $sql = "SELECT cancelledData FROM wp_gpxTransactions WHERE id='".$value->Name."'";
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
           
//            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
//            echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
//            echo '<pre>'.print_r($value, true).'</pre>';
           $sql = "SELECT id, credit_used, credit_action FROM wp_credit WHERE record_id='".$value->GPX_Deposit__c."'";
           $row = $wpdb->get_row($sql);
//            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
//            echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
//            echo '<pre>'.print_r($row, true).'</pre>';
         
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
//            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
//            echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
           
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
//            echo '<pre>'.print_r($sfDepositAdjust, true).'</pre>';
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
   
   
//    $query =  "select ".implode(", ", $selects)." from GPX_Deposit__c WHERE SystemModStamp >= LAST_N_DAYS:".$queryDays." ";
   $query =  "select ".implode(", ", $selects)." from GPX_Deposit__c WHERE SystemModStamp > ".$d.'T'.$t."Z";
   if(isset($gpxcreditid))
   {
       $query .= " AND GPX_Deposit_ID__c='".$gpxcreditid."'";
   }
    $results = $sf->query($query);
    
    if(empty($results))
    {
        echo '<pre>'.print_r("No Results", true).'</pre>';
        echo '<pre>'.print_r($query, true).'</pre>';
    }
    
    foreach ($results as $result) 
    {
        
        $value = $result->fields;

        if($value->Delete_this_Record__c == 'true')
        {
            $wpdb->delete('wp_credit', array('id'=>$value->GPX_Deposit_ID__c));
        }
        else
        {
            
        }
        
        $credit = [
            'record_id'=>$result->Id ,
            'sf_name'=>$result->Name,
            'credit_amount'=>$value->Credits_Issued__c, 
            'credit_expiration_date'=>$value->Expiration_Date__c, 
            'resort_name'=>stripslashes(str_replace("&", "&amp;", $value->Resort_Name__c)), 
            'deposit_year'=> $value->Deposit_Year__c, 
//             'week_type'=> $value->week_type, 
            'unit_type'=> $value->Unit_Type__c, 
            'check_in_date'=> $value->Check_In_Date__c, 
            'extension_date'=> $value->Credit_Extension_Date__c,
            'coupon'=> $value->Coupon__c,
            'status'=> $value->Deposit_Status__c,
            ];
        
//         if($credit['status'] == 'Approved')
//         {
//             $credit['status'] == 'Available';
//         }
        
            $sql = "SELECT status, owner_id, credit_used, credit_amount FROM wp_credit WHERE id='".$value->GPX_Deposit_ID__c."'";
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
                    $oSql = "SELECT id FROM wp_owner_interval WHERE userID = '".$ownerID."' AND  unitweek = '".$value->Resort_Unit_Week__c."' AND  (Year_Last_Banked__c IS NULL OR Year_Last_Banked__c < '".$credit['deposit_year']."')";
                    $oRow = $wpdb->get_var($oSql);
                    
                    if(!empty($oRow))
                    {
                        $wpdb->update('wp_owner_interval', array('Year_Last_Banked__c'=>$credit['deposit_year']), array('id'=>$oRow));
                    }
                }
                //get this transaction
                $sql = "SELECT a.id, a.weekId, a.cancelled, a.userID, a.data, b.data as excd FROM wp_gpxTransactions a
                        INNER JOIN wp_gpxDepostOnExchange b ON a.depositID=b.id
                        WHERE a.userID='".$row->owner_id."'";
                $trans = $wpdb->get_results($sql);
                foreach($trans as $tk=>$tv)
                {
                    $dexp = json_decode($tv->excd);
                    if($dexp->GPX_Deposit_ID__c == $value->GPX_Deposit_ID__c)
                    {
//                         echo '<pre>'.print_r($value, true).'</pre>';
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
                            $sfData['Reservation_Status__c'] = 'Confirmed';
                            
                            $sfType = 'GPX_Transaction__c';
                            $sfObject = 'GPXTransaction__c';
                            $sfFields = [];
                            $sfFields[0] = new SObject();
                            $sfFields[0]->fields = $sfData;
                            $sfFields[0]->type = $sfType;
                            
                            $sfObject = 'GPXTransaction__c';
                            
                            $sfAdd = $sf->gpxUpsert($sfObject, $sfFields);
                           
                        }
                        elseif($tv->cancelled == '' || $tv->cancelled == 'null')
                        {
                            if($value->Deposit_Status__c == 'Denied')
                            {
                                $jsonData = json_decode($tv->data);
                                $amount = $jsonData->Paid;
                                //create the coupon
                                //does this slug exist?
                                $slug = $tv->weekId.$tv->userID;
                                $sql = "SELECT id FROM wp_gpxOwnerCreditCoupon WHERE couponcode='".$slug."'";
                                $row = $wpdb->get_row($sql);
                                if(!empty($row))
                                {
                                    //add random string to the end and check again
                                    $rand = rand(1, 1000);
                                    $slug = $slug.$rand;
                                    $sql = "SELECT id FROM wp_gpxOwnerCreditCoupon WHERE couponcode='".$slug."'";
                                    $row = $wpdb->get_row($sql);
                                    if(!empty($row))
                                    {
                                        //add random string to the end and check again
                                        $rand = rand(1, 1000);
                                        $slug = $slug.$rand;
                                    }
                                }
                                
                                $occ = [
                                    'Name'=>$tv->weekId,
                                    'Slug'=>$slug,
                                    'Active'=>1,
                                    'singleuse'=>0,
                                    'amount'=>$amount,
                                    'owners'=>[$tv->userID],
                                ];
                                $coupon = $gpx->promodeccouponsadd($occ);
                                
                                $sql = "SELECT cancelledData FROM wp_gpxTransactions WHERE id='".$tv->id."'";
                                $cd = $wpdb->get_var($sql);
                                
                                $cupdate = json_decode($cd, true);
                                
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
                                
                                $sql = "SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId='".$tv->weekId."' AND cancelled IS NULL";
                                $trow = $wpdb->get_var($sql);
                                
                                if($trow > 0)
                                {
                                    //nothing to do
                                }
                                else
                                {
                                    $wpdb->update('wp_room', array('active'=>1), array('record_id'=>$tv->weekId));
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
                                
                                $wpdb->update('wp_credit', $creditUpdate, array('id'=>$value->GPX_Deposit_ID__c));
                                
                                //update week and transaction
                                $sfWeekData['GpxWeekRefId__c'] = $tv->weekId;
                                $sfWeekData['Status__c'] = 'Available';
                                
                                $sfFields = [];
                                $sfFields[0] = new SObject();
                                $sfFields[0]->fields = $sfWeekData;
                                $sfFields[0]->type = 'GPX_Week__c';
                                
                                $sfObject = 'GpxWeekRefId__c';
                                
                                $sfWeekAdd = $sf->gpxUpsert($sfObject, $sfFields);
                                
                                
                                $sfData['GPXTransaction__c'] = $tv->id;
                                $sfData['Reservation_Status__c'] = 'Cancelled';
                                
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
//         $check_if_exist = $wpdb->get_results("SELECT * FROM `wp_credit` where id = '".$value->GPX_Deposit_ID__c."'");
//         if(count($check_if_exist) == 0)
//         {
// //             $wpdb->insert('wp_credit', $credit);
//         }
//         else
    //         {
// if(get_current_user_id() == 5)
// {
//     echo '<pre>'.print_r($credit, true).'</pre>';
// }
            foreach($credit as $ck=>$cv)
            {
                if(empty($cv))
                {
                    unset($credit[$ck]);
                }
            }
//          if(get_current_user_id() == 5)
//          {
//              echo '<pre>'.print_r($credit, true).'</pre>';
//          }
            $wpdb->update('wp_credit', $credit, array('id'=>$value->GPX_Deposit_ID__c));
    }
}
add_action('hook_credit_import', 'hook_credit_import');
add_action('wp_ajax_gpx_credit_import', 'hook_credit_import');
add_shortcode('get_credit', 'hook_credit_import');

function cg_ttsf()
{
    global $wpdb;
    
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
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
    wp_die();
}

add_action('wp_ajax_cg_ttsf', 'cg_ttsf');
// function gpx_check_username()
// {
//     $data['exists'] = false;
//     if(username_exists($_REQUEST['username']))
//     {
//         $data['exists'] = true;
//     }
//     $data['success'] = true;
//     wp_send_json($data);
//     wp_die();
// }
// add_action("wp_ajax_gpx_check_username", "gpx_check_username");

function tp_claim_week()
{
    global $wpdb;
    
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
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

    $sql = "SELECT * FROM wp_partner WHERE user_id='".$tp."'";
    $row = $wpdb->get_row($sql);
   
    if($_POST['type'] == 'hold')
    {
        foreach($ids as $id)
        {
            
            $sql = "SELECT data from wp_gpxPreHold WHERE user='".$tp."' AND weekId='".$id."' AND released='0'";
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
            $sql = "SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId='".$id."' AND cancelled IS NULL";
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
            
            $sql = "SELECT data from wp_gpxPreHold WHERE user='".$tp."' AND weekId='".$id."' AND released='0'";
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
                $price = get_option('gpx_exchange_fee');
                $paid = 0;
                $balance = 0;
            }
            else
            {
                $sql = "SELECT a.price, b.taxID FROM wp_room a
                        INNER JOIN wp_resorts b ON a.resort=b.id
                        WHERE a.record_id='".$id."'";
                
                $prow = $wpdb->get_row($sql);
                $price = $prow->price;
                
                $taxAmount = 0;
                //add the tax
                $ttType = 'gpx_tax_transaction_bonus';
                if(get_option($ttType) == '1') //set the tax
                {
                    $sql = "SELECT * FROM wp_gpxTaxes WHERE ID='".$prow->taxID."'";
                    $tax = $wpdb->get_row($sql);
                    $taxPercent = '';
                    $flatTax = '';
                    for($t=1;$t<=3;$t++)
                    {
                        $tp = 'TaxPercent'.$t;
                        $ft = 'FlatTax'.$t;
                        if(!empty($tax->$tp))
                        {
                            $taxPercent += $tax->$tp;
                        }
                        if(!empty($tax->$ft))
                        {
                            $flatTax += $tax->$ft;
                        }
                    }
                    if(!empty($taxPercent))
                    {
                        $finalPrice = str_replace(",", "",$price);
                        $finalPriceForTax = $finalPrice;
                        $taxAmount = $finalPriceForTax*($taxPercent/100);
                    }
                    if(!empty($flatTax))
                    {
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
                $sql = "UPDATE wp_partner
                SET no_of_rooms_received_taken = no_of_rooms_received_taken + 1, trade_balance = trade_balance - 1 
                WHERE user_id = '".$tp."'";
                $wpdb->query($sql);
            }
            
            
        }
    }
    
}
add_action("wp_ajax_tp_claim_week", "tp_claim_week");

function tp_adjust_balance()
{
    global $wpdb;
    
    $sql = "SELECT no_of_rooms_given, no_of_rooms_received_taken, trade_balance, adjData FROM wp_partner WHERE user_id='".$_POST['user']."'";
    $credits = $wpdb->get_row($sql);

    $num = $_POST['num'];
    $note = [];
    if(!empty($credits->adjData));
    {
        $note = json_decode($credits->adjData, true);
    }
    $note[strtotime('now')] = $_POST['note'];
    if($_POST['type'] == 'plus')
    {
        $toUpdate = [
            'no_of_rooms_given' => $credits->no_of_rooms_given + $num,
            'trade_balance' => $credits->trade_balance + $num,
            'no_of_rooms_received_taken' => $credits->no_of_rooms_received_taken,
            'adjData'=>json_encode($note),
        ];
    }
    
    if($_POST['type'] == 'minus')
    {
        $toUpdate = [
            'no_of_rooms_received_taken' => $credits->no_of_rooms_received_taken + $num,
            'trade_balance' => $credits->trade_balance - $num,
            'no_of_rooms_given' => $credits->no_of_rooms_given,
            'adjData'=>json_encode($note),
        ];
    }
    $updae = $wpdb->update('wp_partner', $toUpdate, array('user_id'=>$_POST['user']));
    
    $data = [
        'success' => true,
        'html' => '<button class="btn btn-secondary" disabled>New Trade Balance: '.$toUpdate['trade_balance'].'</button>',
    ];
    
    wp_send_json($data);
    wp_die();
}
add_action("wp_ajax_tp_adjust_balance", "tp_adjust_balance");
function get_gpx_tradepartners()
{
    global $wpdb;
    
    $sql = "SELECT * FROM wp_partner";
    $partners = $wpdb->get_results($sql);
    
    $i = 0;
    
    foreach($partners as $partner)
    {
        $sql = "SELECT id FROM wp_gpxPreHold WHERE released='0' AND user='".$partner->user_id."'";
        $holds = $wpdb->get_results($sql);
        
        $data[$i]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=tradepartners_edit&id='.$partner->record_id.'" class="tp-in-modal data-user" data-user="'.$partner->user_id.'" data-type="edit" data-title="Edit '.$partner->name.'" data-select="admin-modal-content"><i class="fa fa-pencil"></i></a>';
        $data[$i]['edit'] .= '&nbsp;&nbsp;<a id="tp_id_'.$partner->user_id.'" href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=tradepartners_view&id='.$partner->user_id.'" class="tp-in-modal" data-type="activity" data-title="'.$partner->name.'" data-select="admin-modal-content"><i class="fa fa-eye"></i></a>';
        $data[$i]['edit'] .= '&nbsp;&nbsp;<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_add&tp='.$partner->user_id.'" class="tp-in-modal" data-type="add" data-title="Add Week for '.$partner->name.'" data-select="admin-modal-content"><i class="fa fa-plus"></i></a>';
        $data[$i]['edit'] .= '&nbsp;&nbsp;<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=tradepartners_inventory&tp='.$partner->user_id.'" class="tp-in-modal" data-type="inventory" data-title="'.$partner->name.'" data-select="admin-modal-content"><i class="fa fa-minus"></i></a>';
        $data[$i]['edit'] .= '&nbsp;&nbsp;<a href="#" class="debitModal" data-toggle="modal" data-target="#gpxModalBalance" data-name="'.$partner->name.'" data-balance="'.$partner->debit_balance.'" data-id="'.$partner->user_id.'"><i class="fa fa-usd"></i></a>';
        $data[$i]['name'] = $partner->name;
        $data[$i]['email']= $partner->email;
        $data[$i]['phone'] = $partner->phone;
        $data[$i]['address'] = $partner->address;
        $data[$i]['rooms_given'] = $partner->no_of_rooms_given;
        $data[$i]['rooms_received'] = $partner->no_of_rooms_received_taken;
        $data[$i]['trade_balance'] = $partner->trade_balance;
        $data[$i]['holds'] = count($holds);
        $i++;
    }
    
    wp_send_json($data);
    wp_die();
}
add_action("wp_ajax_get_gpx_tradepartners", "get_gpx_tradepartners");

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

function tp_debit()
{
    global $wpdb;
    
    $sql = "SELECT name, debit_id, debit_balance FROM wp_partner WHERE user_id='".$_POST['id']."'";
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
    wp_die();
}
add_action('wp_ajax_tp_debit', 'tp_debit');

function gpx_mass_update_owners()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpxadmin = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $offset = '';
    if(isset($_GET['offset']))
        $offset = $_GET['offset'];
//     echo '<pre>'.print_r(date('h:i:s'), true).'</pre>';
    $owners = $gpxadmin->return_mass_update_owners($_GET['orderby'], $_GET['order'], $offset);
//     echo '<pre>'.print_r(date('h:i:s'), true).'</pre>';
//     foreach($owners as $key=>$value)
//     {
//         $user = $gpx->DAEGetMemberDetails($value['DAEMemberNo'], $key, array('email'=>$value['email']));
//         echo '<pre>'.print_r($user, true).'</pre>';
//         $data = $user;
//     }
//     echo '<pre>'.print_r(date('h:i:s'), true).'</pre>';
    wp_send_json($data);
    wp_die();
}
add_action("wp_ajax_gpx_mass_update_owners","gpx_mass_update_owners");
add_action("wp_ajax_nopriv_gpx_mass_update_owners", "gpx_mass_update_owners");

function gpx_check_login()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    if(is_user_logged_in())
    {
        //check/update member credit for use in checkout
        $cid = get_current_user_id();
         
        if(isset($_COOKIE['switchuser']))
            $cid = $_COOKIE['switchuser'];
        
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
            
        $credit = $gpx->DAEGetMemberCredits($usermeta->DAEMemberNo, $cid);   
            
        $data = array('success'=>true);
    }
    else
        $data = array('login'=>true);

        wp_send_json($data);
        wp_die();
}
add_action("wp_ajax_gpx_check_login","gpx_check_login");
add_action("wp_ajax_nopriv_gpx_check_login", "gpx_check_login");

function gpx_hold_property()
{
    global $wpdb;
    
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpxadmin = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $cid = $_GET['cid'];
    $pid = $_GET['pid'];
    
    $liid = get_current_user_id();
    
    $agentOrOwner = 'owner';
    if($cid != $liid)
    {
        $agentOrOwner = 'agent';
    }
    
    $sql = "SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId='".$pid."' AND cancelled IS NULL";
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
        wp_die();
    }
    
    
    $activeUser = get_userdata($liid);
    
    $bookingrequest = '';
    if(!empty($_REQUEST['bookingrequest']))
    {
        $bookingrequest = 'true';
    }
    
    $sql = "SELECT gpr_oid FROM wp_mapuser2oid WHERE gpx_user_id='".$cid."' LIMIT 1";
    $oid4credit = $wpdb->get_row($sql);
    
    $holdcount = 0;
    $holdcount = count($gpx->DAEGetWeeksOnHold($cid));
    $credits = $gpxadmin->GetMemberCredits($oid4credit->gpr_oid);
    
    $sql = "SELECT id, release_on FROM wp_gpxPreHold
                        WHERE user='".$cid."' AND propertyID='".$pid."' AND released=0";
    $row = $wpdb->get_row($sql);
    
    //return true if credits+1 is greater than holds
    if($credits+1 > $holdcount || $agentOrOwner == 'agent')
    {
        //we're good we can continue holding this
        if(empty($row))
        {
            //does someone else have this on hold?
            $iSql = "SELECT id, release_on FROM wp_gpxPreHold
                        WHERE propertyID='".$pid."' AND released=0";
            $iRow = $wpdb->get_row($iSql);
            if(!empty($iRow))
            {
                $output = [
                    'error'=>'This week is no longer available.',
                    'msg'=>'This week is no longer available.',
                    'inactive'=>true,
                ];
                wp_send_json($output);
                wp_die();
            }
        }
    }
    else
    {
        $output = array('error'=>'too many holds', 'msg'=>get_option('gpx_hold_error_message'));
        
        
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
                    wp_die();
                }
                else
                {
                    return $output;
                }
            }
        }
    }
    
    
//     $release_on = strtotime('+24 hours');
    
    $timeLimit = get_option('gpx_hold_limt_time');
    if(isset($_REQUEST['button']))
    {
        $timeLimit = '24';
    }
    $release_on = strtotime("+".$timeLimit." hours");

    if(!isset($_GET['cid']) || $_GET['cid'] == 0)
        $hold = array('login'=>true);
//     else
//         $hold = $gpx->DAEHoldWeek($_GET['pid'], $_GET['cid'], '', $bookingrequest);
    
    if(empty($_GET['lpid']))
    {
        $_GET['lpid'] = '0';
    }

    $sql = "SELECT data FROM wp_gpxPreHold WHERE user='".$_GET['cid']."' AND weekId='".$_GET['pid']."'";
    $holds = $wpdb->get_row($sql);
    
    $holdDets = json_decode($holds->data, true);
    
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
    $update = $wpdb->update('wp_gpxPreHold', $data, array('user'=>$_GET['cid'], 'weekId'=>$_GET['pid']));
    if(empty($update))
    {
        $wpdb->insert('wp_gpxPreHold',$data);
        $update = $wpdb->insert_id;
    }

    $wpdb->update('wp_room', array('active'=>'0'), array('record_id'=>$_GET['wid']));
    
    $sql = "SELECT release_on FROM wp_gpxPreHold WHERE user='".$_GET['cid']."' AND weekId='".$_GET['pid']."'";
    $rel = $wpdb->get_row($sql);
    $data['msg'] = 'Success';
    
    $data['release_on'] = date('m/d/Y H:i:s', strtotime($rel->release_on));
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_hold_property', 'gpx_hold_property');
add_action('wp_ajax_nopriv_gpx_hold_property', 'gpx_hold_property');


function get_dae_weeks_hold()
{
    global $wpdb;
    
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
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
    wp_die();    
}
add_action('wp_ajax_get_dae_weeks_hold', 'get_dae_weeks_hold');
add_action('wp_ajax_nopriv_get_dae_weeks_hold', 'get_dae_weeks_hold');

function test_cron_release_holds()
{
    global $wpdb;
    
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $releasedate = date('Y-m-d H:i:s');

    $sql = "SELECT a.id, a.weekId, a.user, a.data, b.record_id FROM wp_gpxPreHold a
            INNER JOIN wp_room b on a.propertyID=b.record_id
            WHERE 
            a.released=0 and a.release_on IS NOT NULL and a.release_on <= '".$releasedate."'";
    $rows = $wpdb->get_results($sql);
    
//     $sql = "select record_id as weekId FROM wp_room";
//     $rows = $wpdb->get_results($sql);

    $i = 0;
    foreach($rows as $row)
    {
        $holdDets = json_decode($row->data, true);
        $holdDets[strtotime('now')] = [
            'action'=>'released',
            'by'=>'System',
        ];
        
        $sql = "SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId='".$row->weekId."' AND cancelled IS NULL";
        $trow = $wpdb->get_var($sql);
        
        if($trow > 0)
        {
            $wpdb->update('wp_room', array('active'=>'0'), array('record_id'=>$row->weekId));
//             echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
        }
        else
        {
            $wpdb->update('wp_room', array('active'=>1), array('record_id'=>$row->weekId));
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
        
        $sql = "SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId='".$row->weekId."' AND cancelled IS NULL";
        $trow = $wpdb->get_var($sql);
        
        if($trow > 0)
        {
//             echo '<pre>'.print_r($row, true).'</pre>';
            $wpdb->update('wp_room', array('active'=>'0'), array('record_id'=>$row->weekId));
//             echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
        }
        else
        {
//             $wpdb->update('wp_room', array('active'=>1), array('record_id'=>$row->weekId));
        }

            
//         $wpdb->update('wp_gpxPreHold', array('released'=>1, 'data'=>json_encode($holdDets)), array('id'=>$row->id));
//         $i++;
    }
    
    $data = array('success'=>$i.' held weeks removed.');
    wp_send_json($data);
    wp_die(); 
}
add_action('cron_gpx_release_weeks', 'test_cron_release_holds');
add_action('wp_ajax_gpx_release_weeks', 'test_cron_release_holds');
add_action('wp_ajax_nopriv_gpx_release_weeks', 'test_cron_release_holds');

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
    
    $sql = "SELECT id, data FROM wp_cart WHERE cartID='".$_POST['cartID']."' AND propertyID='".$_POST['propertyID']."'";
    $row = $wpdb->get_row($sql);
      
    if(!empty($row))
    {
        $jsonData = json_decode($row->data, true);
        foreach($jsonData as $jdK=>$jdV)
        {
            if(!isset($_POST[$jdK]))
            {
                $_POST[$jdK] = $jdV;
            }
        }
    }
//     if(get_current_user_id() == 5)
//     {
//         echo '<pre>'.print_r($_POST, true).'</pre>';
//     }
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
        wp_die();   
    }
    else
    {
        return $return;
    }
}
add_action('wp_ajax_gpx_save_guest', 'gpx_save_guest');
add_action('wp_ajax_nopriv_gpx_save_guest', 'gpx_save_guest');

function update_checkin()
{
    global $wpdb;
    
    $sql = "SELECT id, data from wp_gpxTransactions";
    $rows = $wpdb->get_results($sql);
    

    foreach($rows as $row)
    {
        $data = json_decode($row->data);
        
//         echo '<pre>'.print_r($data, true).'</pre>';
        
        $checkin['check_in_date'] = date('Y-m-d', strtotime($data->checkIn));
        $wpdb->update('wp_gpxTransactions', $checkin, array('id'=>$row->id));
        
//    echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';

    }
}
add_action('wp_ajax_update_checkin', 'update_checkin');

function gpx_deposit_on_exchange()
{
    global $wpdb;
//     //is this an agent
    $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $_POST['cid'] ) );
    
    $sql = "SELECT b.resortID FROM wp_room a
            INNER JOIN wp_resorts b ON b.id=a.resort 
            WHERE a.record_id='".$_POST['pid']."'";
    $row = $wpdb->get_row($sql);
   
    $sql = "SELECT * FROM wp_resorts_meta WHERE ResortID='".$row->resortID."'";
    
    $resortMetas = $wpdb->get_results($sql);

    $rmFees = [
        'LateDepositFeeOverride'=>[],
    ];
    foreach($resortMetas as $rm)
    {
        //reset the resort meta items
        $rmk = $rm->meta_key;
        if($rmArr = json_decode($rm->meta_value, true))
        {
            
            foreach($rmArr as $rmdate=>$rmvalues);
            {
                
                $thisVal = '';
                $rmdates = explode("_", $rmdate);
                
                if(count($rmdates) == 1 && $rmdates[0] == '0')
                {
                    //do nothing
                }
                else
                {
                    //check to see if the from date has started
                    if($rmdates[0] < strtotime($_POST['Check_In_Date__c']))
                    {
                        //this date has started we can keep working
                    }
                    else
                    {
                        //these meta items don't need to be used
                        continue;
                    }
                    //check to see if the to date has passed
                    if(isset($rmdates[1]) && ($rmdates[1] >= strtotime($_POST['Check_In_Date__c'])))
                    {
                        //these meta items don't need to be used
                        continue;
                    }
                    else
                    {
                        //this date is sooner than the end date we can keep working
                    }
                    foreach($rmvalues as $rmval)
                    {
                        //do we need to reset any of the fees?
                        if(array_key_exists($rmk, $rmFees))
                        {
                            
                            //set this fee
                            if($rmk == 'LateDepositFeeOverride')
                            {
                                if($rmval == '0')
                                {
                                    $skipOverride = '1';
                                }
                                else
                                {
                                    $skipOverride = $rmval;
                                }
                            }
                        }
                    }
                }
            }
        }
    } //end resort meta fees
     
//     if( $usermeta->GP_Preferred != 'Yes' && !isset($skipOverride))
    if( !isset($skipOverride))
    {
        if(isset($_POST['add_to_cart']) && $_POST['add_to_cart'] == '2')
        {
            //nothing to do here
        }
        elseif(date("Y-m-d H:i:s", strtotime('+15 days')) > date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])))
        {
            $ldFee = get_option('gpx_late_deposit_fee');
            
            if(date("Y-m-d H:i:s", strtotime('+7 days')) > date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])))
            {
                $ldFee = get_option('gpx_late_deposit_fee_within');
            }
            
            $agentReturn = [
                'paymentrequired'=>true,
                'amount'=>$ldFee,
                'type'=>'late_deposit',
                'html'=>'<h5>You will be required to pay a late deposit fee of $'.$ldFee.' to complete trasaction.</h5><br /><br /> <span class="usw-button"><button class="dgt-btn add-fee-to-cart" data-cart="'.$_POST['cartID'].'" data-skip="No">Add To Cart</button>',
            ];
            
            if(get_current_user_id() != $_POST['cid'])
            {
                $agentReturn['html'] .= '<br /><br /><button class="dgt-btn add-fee-to-cart af-agent-skip" data-cart="'.$_POST['cartID'].'" data-skip="Yes">Waive Fee</button>';
            }
            
//             $agentReturn['html'] .= '<br /><br /><button class="btn btn-secondary" class="close-modal">Cancel</a>';
            
            $_POST['cartID'] = $_COOKIE['gpx-cart'];
            if( !isset($_POST['add_to_cart']) || ( isset($_POST['add_to_cart']) && $_POST['add_to_cart'] != '1' ) )
            {
                $_POST['add_to_cart'] = true;
                $return = $agentReturn;
                $return['posted'] = $_POST;
                wp_send_json($return);
                wp_die();
            }
            if($_POST['add_to_cart'] == '1')
            {
                //add this to the cart
                $sql = "SELECT data FROM wp_cart WHERE cartID='".$_POST['cartID']."'";
                $row = $wpdb->get_row($sql);
                $cartData = json_decode($row->data, true);
                $cartData['late_deposit_fee'] = $agentReturn['amount'];
                $cd = [
                    'data'=>json_encode($cartData),
                ];
                $wpdb->update('wp_cart', $cd, array('cartID'=>$_POST['cartID']));
            }
//             echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
            //now this is in the cart we can unset $agentreturn
            unset($agentReturn);
        }
    }
//     }

    $credit = [
        'created_date'=>date('Y-m-d H:i:s'),
        'deposit_year'=>date('Y', strtotime($_POST['Check_In_Date__c'])),
        'resort_name'=>stripslashes(str_replace("&", "&amp;",$_POST['Resort_Name__c'])),
        'check_in_date'=>date('Y-m-d', strtotime($_POST['Check_In_Date__c'])),
        'owner_id'=>$_POST['cid'],
        'interval_number'=>$_POST['Contract_ID__c'],
        'unit_type'=>$_POST['unit_type'],
        'status'=>'DOE',
    ];
 
    $sql = "SELECT * FROM wp_owner_interval WHERE contractID='".$_POST['Contract_ID__c']."'";
    $interval = $wpdb->get_row($sql);
    
    foreach($interval as $intK=>$intV)
    {
        $_POST[$intK] = $intV;
    }
    
    $wpdb->insert('wp_credit', $credit);
    
    $creditID = $wpdb->insert_id;
    
    foreach($credit as $ck=>$cv)
    {
        if(empty($_POST[$ck]))
        {
            $_POST[$ck] = $cv;
        }
    }
    
    $_POST['GPX_Deposit_ID__c'] = $wpdb->insert_id;
    
    $json = json_encode($_POST);
    
    $wpdb->insert('wp_gpxDepostOnExchange', array('creditID'=>$creditID, 'data'=>$json));

    if(isset($agentReturn))
    {
        $return = $agentReturn;
        $return['success'] = true;
        $return['id'] = $wpdb->insert_id;
    }
    else
    {
        $return = array('id'=>$wpdb->insert_id);
    }

    wp_send_json($return);
    wp_die();
}
add_action('wp_ajax_gpx_deposit_on_exchange', 'gpx_deposit_on_exchange');
add_action('wp_ajax_nopriv_gpx_deposit_on_exchange', 'gpx_deposit_on_exchange');


function gpx_payment_submit()
{
    global $wpdb;
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $cid = get_current_user_id();
 
    if(isset($_COOKIE['switchuser']))
    {
        $cid = $_COOKIE['switchuser'];
    }
    
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
                
                $sql = "SELECT user, data FROM wp_cart WHERE cartID='".$post['cartID']."' ORDER BY id DESC LIMIT 1";
                $cart = $wpdb->get_row($sql);
                $cartData = json_decode($cart->data);
                
                if(isset($_POST['paid']) && $_POST['paid'] > 0)
                {
//                     require_once GPXADMIN_API_DIR.'/functions/class.shiftfour.php';
//                     $shift4 = new Shiftfour(GPXADMIN_API_URI, GPXADMIN_API_DIR);
                    
                    $sql = "SELECT item as type, data FROM wp_temp_cart WHERE id='".$cartData->tempID."'";
                    $temp = $wpdb->get_row($sql);
                    $tempData = json_decode($temp->data);
                    
                    //is this a duplicate transaction
                    $sql = "SELECT id FROM wp_gpxTransactions WHERE cartID='".$post['cartID']."' AND transactionType='".$temp->type."'";
                    $row = $wpdb->get_row($sql);
                    
                    if(!empty($row))
                    {
                        $data['error'] = 'Transaction processed.';
                        wp_send_json($data);
                        wp_die();
                    }
                    
                    $sf = Salesforce::getInstance();
                    
                    //charge the full amount
                    $sql = "SELECT i4go_responsecode, i4go_uniqueid FROM wp_payments WHERE id='".$_REQUEST['paymentID']."'";
                    $i4go = $wpdb->get_row($sql);
    //                 echo '<pre>'.print_r($i4go, true).'</pre>';
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
//                 echo '<pre>'.print_r("st", true).'</pre>';
                $fullPriceForPayment = $_REQUEST['amount'];
//                 echo '<pre>'.print_r($fullPriceForPayment, true).'</pre>';
                $paymentRef = $_REQUEST['paymentID'];
                $type = [
                    $_REQUEST['item'],
                ];
                
                if(isset($post['ownerCreditCoupon']))
                {
                    
                    $osql = "SELECT *, a.id as cid, b.id as aid, c.id as oid FROM wp_gpxOwnerCreditCoupon a
                                        INNER JOIN wp_gpxOwnerCreditCoupon_activity b ON b.couponID=a.id
                                        INNER JOIN wp_gpxOwnerCreditCoupon_owner c ON c.couponID=a.id
                                        WHERE a.id IN ('".implode("', '", $cartData->occoupon)."') AND a.active=1 and c.ownerID='".$cid."'";
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
                        if(get_current_user_id() == 5)
                        {
//                             echo '<pre>'.print_r($eachCouponActAmount, true).'</pre>';
                        }
                        if($distinctCoupon->single_use == 1 && array_sum($actredeemed) > 0)
                        {
                            $balance = 0;
                        }
                        else
                        {
                            $balance = array_sum($actamount) - array_sum($actredeemed);
                            //                             if(isset($indCartOCCreditUsed))
                                //                             {
                                //                                 $balance = $balance - array_sum($indCartOCCreditUsed);
                                //                             }
                         }
                        //if we have a balance at this point the the coupon is good
                        if($balance > 0)
                        {
                            //                                                 echo '<pre>'.print_r($indPrice[$book], true).'</pre>';
                            if($balance <= $fullPriceForPayment)
                            {
                                $fullPriceForPayment = $fullPriceForPayment - $balance;
                                //                                 $indPrice[$book] = $indPrice[$book] - $balance;
                                $indCartOCCreditUsed[] = $balance;
                                $couponDiscount = array_sum($indCartOCCreditUsed);
                            }
                            // use the coupon
                            //                             else
                                //                             {
                                //                                 $indCartOCCreditUsed[$book] = $checkoutAmount;
                                //                                 $indPrice[$book] = 0;
                                // //                                 $finalPrice = $finalPrice - $indCartOCCreditUsed[$book];
                                //                             }
                        }
                    }
                }
//                 if(get_current_user_id() == 5)
//                 {
//                     echo '<pre>'.print_r($fullPriceForPayment, true).'</pre>';
//                 }

                if(isset($_POST['paid']) && $_POST['paid'] > 0)
                {
                    require_once GPXADMIN_API_DIR.'/functions/class.shiftfour.php';
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
//                     $post['cartID'] = $_COOKIE['gpx-cart'];
                    
                    //what type of charge was this?
                   
                    
                    $sql = "SELECT item as type, data FROM wp_temp_cart WHERE id='".$cartData->tempID."'";
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
                        if(get_current_user_id() == 5)
                        {
//                             echo '<pre>'.print_r($occActivity, true).'</pre>';
                        }
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
        if(get_current_user_id() == 5)
        {
            
        }
        else 
        {

//      Until we launch we want general customers (any owner account) to be able to complete a booking without credit card details.
        if(get_current_user_id() != $cid) //only agents can post without a payment
            $book = $gpx->DAECompleteBooking($_POST);
        else
            $book = array('ReturnCode'=>'10001', 'ReturnMessage'=>'You must complete the payment details!');
        }
//         $book = $gpx->DAECompleteBooking($_POST);

    }
//     $bookingErrorCodes = array(
//         '-8',  
//         '-9',  
//         '100',  
//         '101',  
//         '102',  
//         '103',  
//         '104',  
//         '107',  
//     );
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
    wp_die();    
}
add_action('wp_ajax_gpx_payment_submit', 'gpx_payment_submit');
add_action('wp_ajax_nopriv_gpx_payment_submit', 'gpx_payment_submit');

function cg_payment_submit($id='')
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    if(!empty($_GET['id']))
    {
        $id = $_GET['id'];
    }
    $t = $gpx->transactiontosf($id);
}
add_action('wp_ajax_cg_payment_submit', 'cg_payment_submit');

function gpx_resend_confirmation()
{
    global $wpdb;
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $confID = $gpx->DAEReIssueConfirmation($_GET);
    $msg = '<a href="/confirmation-download?id='.$confID.'&week='.$_GET['weekid'].'&no='.$_GET['memberno'].'" target="_blank">Please click here to view your confirmation.</a>';
    if($confID == 0)
        $msg = 'There was an error process your request.  Please try again later.';
    $data = array('msg'=>$msg);
    
    wp_send_json($data);
    wp_die();    
}
add_action('wp_ajax_gpx_resend_confirmation', 'gpx_resend_confirmation');
add_action('wp_ajax_nopriv_gpx_resend_confirmation', 'gpx_resend_confirmation');



function gpx_save_confirmation()
{
    global $wpdb;
    if(substr($_SERVER['REQUEST_URI'], 0, 22) == '/confirmation-download')
    {
        $sql = "SELECT id, pdf FROM wp_gpxPDFConf WHERE daeMemberNo='".$_GET['no']."' AND id='".$_GET['id']."' AND weekid='".$_GET['week']."'";
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
// add_action('wp_ajax_gpx_save_confirmation', 'gpx_save_confirmation');
// add_action('wp_ajax_nopriv_gpx_save_confirmation', 'gpx_save_confirmation');
// function get_gpx_users()
// {
//     require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
//     $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

//     $data = $gpx->return_dae_members();
    
//     wp_send_json($data);
//     wp_die();
// }

// add_action('wp_ajax_get_gpx_users', 'get_gpx_users');
// add_action('wp_ajax_nopriv_get_gpx_users', 'get_gpx_users');

function send_welcome_email()
{
    global $wpdb;
    
    if(isset($_REQUEST['cid']))
    {
        $id = $_REQUEST['cid'];
        
        $sql = "SELECT SPI_Email__c, SPI_Owner_Name_1st__c FROM wp_GPR_Owner_ID__c WHERE user_id='".$id."'";
        $row = $wpdb->get_row($sql);
        
        $name = $row->SPI_Owner_Name_1st__c;
        $email = $row->SPI_Email__c;
        
//         $userdata = get_userdata($id);
        
//         $email = $userdata->Email;
//         if(empty($email))
//         {
//             $email = $userdata->user_email;
//         }
      
        //create the link
        /*
         * todo: create the link for the email
         */
        
        $hashKey = wp_generate_password(10, false);
        
        update_user_meta($id, 'gpx_upl_hash', $hashKey);
        $url = get_site_url().'?welcome='.$hashKey;
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $msg = 'Message TBD. <a href="'.$url.'">Click here to create account.</a>';
        $msg = '<body bgcolor="#FAFAFA" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; height: 100% !important; width: 100% !important; background-color: #FAFAFA; margin: 0; padding: 0;">
<style type="text/css">#outlook a {
              padding: 0;
          }
          .body{
              width: 100% !important;
              -webkit-text-size-adjust: 100%;
              -ms-text-size-adjust: 100%;
              margin: 0;
              padding: 0;
          }
          .ExternalClass {
              width:100%;
          }
          .ExternalClass,
          .ExternalClass p,
          .ExternalClass span,
          .ExternalClass font,
          .ExternalClass td,
          .ExternalClass div {
              line-height: 100%;
          }
          img {
              outline: none;
              text-decoration: none;
              -ms-interpolation-mode: bicubic;
          }
          a img {
              border: none;
          }
          p {
              margin: 1em 0;
          }
          table td {
              border-collapse: collapse;
          }
          /* hide unsubscribe from forwards*/
          blockquote .original-only, .WordSection1 .original-only {
            display: none !important;
          }

          @media only screen and (max-width: 480px){
            body, table, td, p, a, li, blockquote{-webkit-text-size-adjust:none !important;} /* Prevent Webkit platforms from changing default text sizes */
                    body{width:100% !important; min-width:100% !important;} /* Prevent iOS Mail from adding padding to the body */

            #bodyCell{padding:10px !important;}

            #templateContainer{
              max-width:600px !important;
              width:100% !important;
            }

            h1{
              font-size:24px !important;
              line-height:100% !important;
            }

            h2{
              font-size:20px !important;
              line-height:100% !important;
            }

            h3{
              font-size:18px !important;
              line-height:100% !important;
            }

            h4{
              font-size:16px !important;
              line-height:100% !important;
            }

            #templatePreheader{display:none !important;} /* Hide the template preheader to save space */

            #headerImage{
              height:auto !important;
              max-width:600px !important;
              width:100% !important;
            }

            .headerContent{
              font-size:20px !important;
              line-height:125% !important;
            }

            .bodyContent{
              font-size:18px !important;
              line-height:125% !important;
            }

            .templateColumnContainer{display:block !important; width:100% !important;}

            .columnImage{
              height:auto !important;
              max-width:480px !important;
              width:100% !important;
            }

            .leftColumnContent{
              font-size:16px !important;
              line-height:125% !important;
            }

            .rightColumnContent{
              font-size:16px !important;
              line-height:125% !important;
            }

            .footerContent{
              font-size:14px !important;
              line-height:115% !important;
            }

            .footerContent a{display:block !important;} /* Place footer social and utility links on their own lines, for easier access */
          }
</style>
<table align="center" border="0" cellpadding="0" cellspacing="0" id="bodyTable" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #FAFAFA; border-collapse: collapse !important; height: 100% !important; margin: 0; mso-table-lspace: 0pt; mso-table-rspace: 0pt; padding: 0; width: 100% !important" width="100%">
	<tbody>
		<tr>
			<td align="center" id="bodyCell" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; height: 100% !important; width: 100% !important; border-top-width: 4px; border-top-color: #dddddd; border-top-style: solid; margin: 0; padding: 20px;" valign="top">
			<p style="text-align:center; color: #808080; font-family: Helvetica; font-size: 10px; line-height: 12.5px;">To make sure this goes to your inbox, just add <a href="GPVSpecialist@gpresorts.com" style="color:#00adef;">GPVSpecialist@gpresorts.com</a> to your address book.</p>
			<!-- BEGIN TEMPLATE // -->

			<table border="0" cellpadding="0" cellspacing="0" id="templateContainer" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse !important; width: 600px; border: 1px solid #dddddd;">
				<tbody>
					<tr>
						<td align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top"><!-- BEGIN PREHEADER // -->
						<table border="0" cellpadding="0" cellspacing="0" id="templatePreheader" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #FFFFFF; border-bottom-color: #CCCCCC; border-bottom-style: solid; border-bottom-width: 1px; border-collapse: collapse !important; mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
							<tbody>
								<tr style="">
									<td align="left" class="preheaderContent" pardot-region="preheader_content00" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #808080; font-family: Helvetica; font-size: 10px; line-height: 12.5px; text-align: left; padding: 10px 20px;" valign="top"><a href="https://www.gpxvacations.com/"><img alt="Grand Pacific Exchange" border="0" height="45" src="http://www2.grandpacificresorts.com/l/130601/2016-08-23/hfbs5/130601/20220/GPX_logo_sans_125x44.png" style="width: 125px; height: 45px; border-width: 0px; border-style: solid;" width="125"></a></td>
									<td align="left" class="preheaderContent" pardot-data="line-height:20px;" pardot-region="preheader_content01" style="color: rgb(128, 128, 128); font-family: Helvetica; font-size: 10px; line-height: 20px; text-align: left; padding: 10px 20px 10px 0px; background: rgb(255, 255, 255);" valign="top" width="180">
									<h6 style="text-align: right;"><span style="font-size:18px;">Welcome to GPX</span></h6>
									</td>
								</tr>
							</tbody>
						</table>
						<!-- // END PREHEADER --></td>
					</tr>
					<tr>
						<td align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top"><!-- BEGIN HEADER // -->
						<table border="0" cellpadding="0" cellspacing="0" id="templateHeader" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #FFFFFF; border-bottom-color: #CCCCCC; border-bottom-style: solid; border-bottom-width: 1px; border-collapse: collapse !important; mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
							<tbody>
								<tr>
								</tr>
							</tbody>
						</table>
						<!-- // END HEADER --></td>
					</tr>
					<tr>
						<td align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top"><!-- BEGIN BODY // -->
						<table border="0" cellpadding="0" cellspacing="0" id="templateBody" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #FFFFFF; border-bottom-color: #CCCCCC; border-bottom-style: solid; border-bottom-width: 1px; border-collapse: collapse !important; border-top-color: #FFFFFF; border-top-style: solid; border-top-width: 1px; mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
							<tbody>
								<tr style="">
									<td align="left" class="bodyContent" pardot-data="" pardot-region="body_content" style="color: rgb(80, 80, 80); font-family: Helvetica; font-size: 16px; line-height: 24px; text-align: left; padding: 20px;" valign="top"><div style="display: block; font-family: Helvetica; font-size: 26px; font-style: normal; font-weight: bold; letter-spacing: normal; line-height: 26px; margin: 0px; padding-bottom: 10px; color: rgb(32, 32, 32) !important; text-align: center;"><span style="font-size:40px; line-height:50px;">Welcome to GPX</span><br>
&nbsp;</div>

<p>Dear&nbsp;'.$name.',</p>
We are excited to welcome you, a valued Owner with Grand Pacific Resorts, to your exclusive Owner Benefit program. Your GPX membership opens up more opportunities to vacation anytime throughout the year to some of top destinations. There are no annual membership fees or complicated point systems, which makes vacationing more often easier than ever.<br>
<br>
Vacation specialists are standing by at (866) 325-6295 to provide you with exceptional service. You may look and book online at anytime by visiting&nbsp;<a href="http://www.gpxvacations.com/" style="text-decoration:none;color:#00adef;" target="_blank">GPXvacations.com</a>.&nbsp;<br>
<br>
Let\'s get started! Simply click the button to walk through the steps of setting up your online account.&nbsp;<br>
&nbsp;
<div style="text-align: center;">
<table border="0" cellpadding="0" cellspacing="0" class="mobile-button-container" width="100%">
	<tbody>
		<tr>
			<td align="center" class="padding-copy" style="padding: 0;">
			<table border="0" cellpadding="0" cellspacing="0" class="responsive-table">
				<tbody>
					<tr>
						<td align="center"><a class="mobile-button" href="'.$url.'" style="font-size: 16px; font-family: Arial, sans-serif; font-weight: bold; color: #ffffff !important; text-decoration: none; background-color: #009ad6; border-top: 10px solid #009ad6; border-bottom: 10px solid #009ad6; border-left: 25px solid #009ad6; border-right: 25px solid #009ad6; border-radius: 5px; -webkit-border-radius: 5px; -moz-border-radius: 5px; display: inline-block; margin:10px 0;" target="_blank"><font color="#ffffff">Get Started Here!</font></a></td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
</div>
</td>
								</tr>
							</tbody>
						</table>
						<!-- // END BODY --></td>
					</tr>
					<tr>
						<td align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top"><!-- BEGIN FOOTER // -->
						<table border="0" cellpadding="0" cellspacing="0" id="templateFooter" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #FFFFFF; border-collapse: collapse !important; border-top-color: #FFFFFF; border-top-style: solid; border-top-width: 1px; mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
							<tbody>
								<tr style="">
									<td align="left" class="footerContent" pardot-data="" pardot-region="preheader_content01" style="color: rgb(128, 128, 128); font-family: Helvetica; font-size: 10px; line-height: 15px; text-align: left; padding: 0px 20px 20px; background: rgb(239, 239, 239);" valign="top"><div style="text-align: center;">Copyright© '.date('Y').'&nbsp;Grand Pacific Resorts, All rights reserved. You are an owner at a resort managed by Grand Pacific Resorts and may receive periodic communications from the company.<br>
&nbsp;<br>
You are receiving this email because you are an owner with Grand Pacific Resorts. If you believe an error has been made, please contact us at gpvspecialist@gpresorts.com.<br>
<br>';
									    
									    /*
<a href="%%view_online%%" style="color:#00adef;">View online</a>
                                        */
$msg .= '</div>
</td>
								</tr>
								<tr style="">
									<td align="left" class="footerContent original-only" pardot-region="preheader_content02" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #808080; font-family: Helvetica; font-size: 10px; line-height: 15px; text-align: left; padding: 0 20px 20px;" valign="top"><div style="text-align: center;"><a href="	https://www2.grandpacificresorts.com/emailPreference/e/epc/130601/VM1N5YXix4WEdxBb7rXsuE8ogp9HFelSYjBXnvhsLeY/263">Update My Preferences</a><br>
&nbsp;
<div style="text-align: center;"><a href="https://gpxvacations.com/privacy-policy/" style="color:#00adef;">Privacy Policy</a><br>
&nbsp;</div>
</div>
</td>
								</tr>
							</tbody>
						</table>
						<!-- // END FOOTER --></td>
					</tr>
				</tbody>
			</table>
			<!-- // END TEMPLATE --></td>
		</tr>
	</tbody>
</table>
<br>
<!--
          This email was originally designed by the wonderful folks at MailChimp and remixed by Pardot.
          It is licensed under CC BY-SA 3.0
        -->


</body>';
        if($emailresults = wp_mail($email, 'Welcome to GPX', $msg, $headers))
        {
            $data['success'] = true;
            $data['msg'] = 'Email Sent!';
        }
        else
        {
            $data['msg'] = "Email not sent.  Please verify email address in profile.";
        }
    }
    wp_send_json($data);
    wp_die();  
}
add_action('wp_ajax_send_welcome_email', 'send_welcome_email');
// define the wp_mail_failed callback
function action_wp_mail_failed($wp_error)
{

    return error_log(print_r($wp_error, true));
}

// add the action
add_action('wp_mail_failed', 'action_wp_mail_failed', 10, 1);
function gpx_Owner_id_c(){
    global $wpdb;
    
    $data = array();
    
    
    $map2db = [
        'Name' => 'Name',
        'SPI_Owner_Name_1st__c' => 'SPI_Owner_Name_1st__c',
        'SPI_Email__c' => 'SPI_Email__c',
        'id' => 'id'
    ];
    $orderBy;
    $limit;
    $offset;
    
    $wheres = '';
    if(isset($_REQUEST['filter']))
    {
        $wheres = '';
        $search = json_decode(stripslashes($_REQUEST['filter']));
        foreach($search as $sk=>$sv)
        {
            if($sk == 'id')
            {
                $wheres[] = "user_id LIKE '%".$sv."%'";
            }
            else
            {
                $wheres[] = $sk." LIKE '%".$sv."%'";
            }
        }
        $where = "AND ".implode(" OR ", $wheres)."";
    }
    
    
    
    if(isset($_REQUEST['sort']))
    {
        $orderBy = " ORDER BY ".$_REQUEST['sort']." ".$_REQUEST['order'];
    }
    if(isset($_REQUEST['limit']))
    {
        $limit = " LIMIT ".$_REQUEST['limit'];
        //                 $data['filtered'] = $_REQUEST['limit'];
    }
    if(isset($_REQUEST['offset']))
    {
        $offset = " OFFSET ".$_REQUEST['offset'];
    }
    $sql = "SELECT id, user_id, Name, SPI_Owner_Name_1st__c, SPI_Email__c, SPI_Home_Phone__c, SPI_Street__c, SPI_City__c, SPI_State__c  FROM `wp_GPR_Owner_ID__c` WHERE `user_id` IS NOT NULL and `Name` IN (SELECT `gpr_oid` FROM `wp_mapuser2oid`) "
        .$where
        ." GROUP BY user_id "
            .$orderBy
            .$limit
            .$offset;
            
            $tsql = "SELECT COUNT(distinct user_id) as cnt  FROM `wp_GPR_Owner_ID__c` WHERE `user_id` IS NOT NULL and `Name` IN (SELECT `gpr_oid` FROM `wp_mapuser2oid`)";
            $data['total'] = (int) $wpdb->get_var($tsql);
            $results = $wpdb->get_results($sql);
            //         echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
            $i = 0;
            $dups = [];
            foreach($results as $result)
            {
                if(in_array($result->Name, $dups))
                {
                    continue;
                }
                if($result->SPI_Owner_Name_1st__c == 'Jonathan Newby')
                {
                    //                 continue;
                }
                $dups[] = $result->Name;
                $welcomeEmailLink = '';
                if($result->welcome_email_sent == '0')
                {
                    //                 $welcomeEmailLink = '<sup><i class="fa fa-exclamation"></i></sup>';
                }
                
                $sql = "SELECT COUNT(id) as cnt FROM wp_owner_interval WHERE Contract_Status__c='Active' AND userID='".$result->user_id."'";
                $intervals = $wpdb->get_var($sql);
                
                $data['rows'][$i]['action'] = '<a href="#" class="switch_user" data-user="'.$result->user_id.'" title="Select Owner and Return"><i class="fa fa-refresh fa-rotate-90" aria-hidden="true"></i></a>  <a  href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_edit&amp;id='.$result->user_id.'" title="Edit Owner Account" ><i class="fa fa-pencil" aria-hidden="true"></i>'.$welcomeEmailLink.'</a>';
                //                  $data[$i]['action'] .= '&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/wp-admin/admin.php?page=gpx-admin-page&amp;gpx-pg=users_mapping&amp;id='.$result->user_id.'" class="view-mapping" title="View Owner Account"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                $data['rows'][$i]['id'] = $result->user_id;
                $data['rows'][$i]['Name'] = $result->Name;
                $data['rows'][$i]['SPI_Owner_Name_1st__c'] = $result->SPI_Owner_Name_1st__c;
                $data['rows'][$i]['SPI_Email__c'] = $result->SPI_Email__c;
                $data['rows'][$i]['SPI_Home_Phone__c'] = $result->SPI_Home_Phone__c;
                //                 $data[$i]['SPI_Work_Phone__c'] = $result->SPI_Work_Phone__c;
                $data['rows'][$i]['SPI_Street__c'] = $result->SPI_Street__c;
                $data['rows'][$i]['SPI_City__c'] = $result->SPI_City__c;
                $data['rows'][$i]['SPI_State__c'] = $result->SPI_State__c;
                //                 $data[$i]['SPI_Zip_Code__c'] = $result->SPI_Zip_Code__c;
                //                 $data[$i]['SPI_Country__c'] = $result->SPI_Country__c;
                $data['rows'][$i]['Intervals'] = $intervals;
                $i++;
            }
            
            //         $sql = "SELECT * FROM wp_partner";
            //         $results = $wpdb->get_results($sql);
            
            //         foreach($results as $result)
                //         {
                //             $data[$i]['action'] = '<a href="#" class="switch_user" data-user="'.$result->user_id.'" title="Select Owner and Return"><i class="fa fa-refresh fa-rotate-90" aria-hidden="true"></i></a> | <a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_edit&amp;id='.$result->user_id.'" title="Edit Owner Account"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/wp-admin/admin.php?page=gpx-admin-page&amp;gpx-pg=users_mapping&amp;id='.$result->user_id.'" class="view-mapping" title="View Owner Account"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                //             $data[$i]['id'] = $result->user_id;
                //             $data[$i]['Name'] = $result->username;
                //             $data[$i]['SPI_Owner_Name_1st__c'] = $result->name;
                //             $data[$i]['SPI_Email__c'] = $result->email;
                //             $data[$i]['SPI_Home_Phone__c'] = $result->phone;
                //             $data[$i]['SPI_Street__c'] = $result->address;
                //             $i++;
                //         }
                
                wp_send_json($data);
                wp_die();

    }

   add_action('wp_ajax_gpx_Owner_id_c', 'gpx_Owner_id_c');
   add_action('wp_ajax_nopriv_gpx_Owner_id_c', 'gpx_Owner_id_c');

function gpx_tp_inventory() { 

        global $wpdb;
        
        $data = array();
        
        $orderBy;
        $limit;
        $offset;
        
        $where = "(`check_in_date` != '0000-00-00 00:00:00' or `check_out_date` != '0000-00-00 00:00:00') and resort !='0' and resort !='null' and unit_type !='null' and archived=0";
        
        if(isset($_REQUEST['filter']))
        {
            $search = json_decode(stripslashes($_REQUEST['filter']));
            foreach($search as $sk=>$sv)
            {
                if($sk == 'record_id')
                {
                    $wheres[] = "CAST(record_id as CHAR) LIKE '".$sv."%'";
                }
                elseif($sk == 'check_in_date')
                {
                    $wheres[] = $sk ." BETWEEN '".date('Y-m-d 00:00:00', strtotime($sv))."' AND '".date('Y-m-d 23:59:59', strtotime($sv))."' ";
                }
                else
                {
                    $wheres[] = $sk." LIKE '%".$sv."%'";
                }
            }
            $where .= " AND ".implode(" OR ", $wheres)."";
        }
        
        
        if(get_current_user_id() == 5)
        {
//             echo '<pre>'.print_r($wheres, true).'</pre>';
        }
        
        if(isset($_REQUEST['sort']))
        {
            $orderBy = " ORDER BY ".$_REQUEST['sort']." ".$_REQUEST['order'];
        }
        if(isset($_REQUEST['limit']))
        {
            $limit = " LIMIT ".$_REQUEST['limit'];
            //                 $data['filtered'] = $_REQUEST['limit'];
        }
        if(isset($_REQUEST['offset']))
        {
            $offset = " OFFSET ".$_REQUEST['offset'];
        }
        $sql = "SELECT a.*, b.ResortName  FROM `wp_room` a 
                INNER JOIN wp_resorts b ON b.id=a.resort
                WHERE" 
            .$where
                .$orderBy
                .$limit
                .$offset;
                
        $results = $wpdb->get_results($sql);
        if(get_current_user_id() == 5)
        {
//             echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
//             echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
//             echo '<pre>'.print_r($results, true).'</pre>';
        }
        $tsql = "SELECT COUNT(record_id) as cnt  FROM `wp_room`
                WHERE"
            .$where;
        $data['total'] = (int) $wpdb->get_var($tsql);;
        //SELECT * FROM `wp_room` WHERE `unit_type` != '0' or `unit_type` IS NOT NULL or `resort` IS NOT NULL or `resort` != '0' ORDER BY `record_id` DESC
        $i = 0;
        
        foreach($results as $result)
        {
            
            
               if($result->active == 0)
               {
                   //was this held by this owner
                   $sql = "SELECT id FROM wp_gpxPreHold WHERE propertyID='".$result->record_id."' AND user='".$_REQUEST['user']."' AND released=0";
                   $held = $wpdb->get_row($sql);
                   if(empty($held))
                   {
                       //this was not held by this user so don't display it.
                       /*
                        * Ashley would like to see all inventory for now.
                        * TODO: Add continue back.
                        */
//                        continue;
                   }
                   else 
                   {
                       $data[$i]['active'] = 'Held';
                   }
                   
               }
//                 $data[$i]['record_id'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id='.$result->record_id.'"><i class="fa fa-pencil" aria-hidden="true"></i></a>';    
//                 $data[$i]['record_id'] .= '&nbsp;&nbsp;<a href="#" class="deleteWeek" data-id='.$result->record_id.'"><i class="fa fa-trash" aria-hidden="true" style="color: #d9534f;"></i></a>';    
               $data['rows'][$i]['record_id'] = $result->record_id;
               $data['rows'][$i]['create_date'] = $result->create_date;
               $data['rows'][$i]['last_modified_date'] = $result->last_modified_date;
               $data['rows'][$i]['create_date'] = '<span data-date="'.date('Y-m-d', strtotime($result->create_date)).'">'.date('m/d/Y', strtotime($result->create_date)).'</span>';
               $data['rows'][$i]['last_modified_date'] = '<span data-date="'.date('Y-m-d', strtotime($result->last_modified_date)).'">'.date('m/d/Y', strtotime($result->last_modified_date)).'</span>';
               $data['rows'][$i]['check_in_date'] = '<span data-date="'.date('Y-m-d', strtotime($result->check_in_date)).'">'.date('m/d/Y', strtotime($result->check_in_date)).'</span>';
               $data['rows'][$i]['check_out_date'] = '<span data-date="'.date('Y-m-d', strtotime($result->check_out_date)).'">'.date('m/d/Y', strtotime($result->check_out_date)).'</span>';
               $data['rows'][$i]['price'] = '';
                if($result->type != '1' && !empty($result->price))
                {
                    $data['rows'][$i]['price'] = '$'.$result->price;
                }

                $unit_type = "SELECT * FROM `wp_unit_type` WHERE `record_id` = '".$result->unit_type."'";
                $unit = $wpdb->get_results($unit_type); 
                $data['rows'][$i]['unit_type_id'] = $unit[0]->name;

                $spid = "SELECT * FROM wp_users a INNER JOIN wp_usermeta b on a.ID=b.user_id WHERE b.meta_key='DAEMemberNo' AND ID = '".$result->source_partner_id."'";
                $spid_result = $wpdb->get_results($spid);
                $data['rows'][$i]['source_partner_id'] = $spid_result[0]->display_name;

                //resort
                $data['rows'][$i]['ResortName'] = $result->ResortName;

//                 $data['rows'][$i]['sourced_by_partner_on'] = $result->sourced_by_partner_on;
//                 $data['rows'][$i]['resort_confirmation_number'] = $result->resort_confirmation_number;

                if(!isset($data[$i]['active']))
                {
                    $active = "";
                    if(isset($result->active)){
                        
                        if($result->active == 1){
                            $active = "Yes";
                        }
                        else{
                            $active = "No";
                        }
                    }
                    $data['rows'][$i]['active'] = $active;
                }
                
                
                $availability = "";

                if(isset($result->availability)){
                    
                    if($result->availability == 0){
                        $availability = "--";
                    }
                    elseif($result->availability == 1){
                        $availability = "All";
                    }
                    elseif($result->availability == 2){
                        $availability = "Owner Only";
                    }
                    else {
                        $availability = "Partner Only";
                    }
                    
                }

                $data['rows'][$i]['availability'] = $availability;

                $avltop = "SELECT * FROM `wp_partner` WHERE record_id = '".$result->available_to_partner_id."'";
                $avltop_result = $wpdb->get_results($avltop);

                $data['rows'][$i]['available_to_partner_id'] = $avltop_result[0]->name;

                $type = "";
                if(isset($result->type)){
                    if($result->type == 1){
                        $type = "Exchange";
                    }
                    elseif($result->type == 2){
                     $type = "Rental";   
                    }
                    elseif($result->type == 3){
                        $type = 'Exchange/Rental';
                    }
                    else{
                     $type = "--";      
                    }

                }

                $data['rows'][$i]['type'] = $type;
                // $data[$i]['points'] = $result->points;
                // $data[$i]['note'] = $result->note;
                
                $i++;
        }
        
        wp_send_json($data);
        wp_die();

    }

   add_action('wp_ajax_gpx_tp_inventory', 'gpx_tp_inventory');
   add_action('wp_ajax_nopriv_gpx_tp_inventory', 'gpx_tp_inventory');

   function gpx_tp_activity()
   {
       global $wpdb;
       
       $data = [];
       $table = [];
       
       $id = $_GET['id'];
       //get the rooms added
       $sql = "SELECT a.record_id, a.check_in_date, a.resort_confirmation_number, a.sourced_by_partner_on, b.ResortName, c.name AS unit_type  FROM wp_room a
              INNER JOIN wp_resorts b ON b.id=a.resort
              INNER JOIN wp_unit_type c ON c.record_id=a.unit_type
              WHERE source_partner_id='".$id."' ORDER BY sourced_by_partner_on";
       $results = $wpdb->get_results($sql);
       
       $i = 0;
       foreach($results as $rv)
       {
           $k = strtotime($rv->sourced_by_partner_on).$i;
           
           $checkin = '';
           if(!empty($rv->check_in_date))
           {
               $checkin = date('m/d/Y', strtotime($rv->check_in_date));
           }
           
           $table[$k]['edit'] = '<a data-back="#tp_id_'.$id.'" href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id='.$rv->record_id.'" target="_blank"><i class="fa fa-pencil"></i></a>';
           $table[$k]['ID'] = $rv->record_id;
           $table[$k]['activity'] = 'Deposit';
           $table[$k]['check_in_date'] = $checkin;
           $table[$k]['resort'] = $rv->ResortName;
           $table[$k]['unit_type'] = $rv->unit_type;
           $table[$k]['resort_confirmation_number'] = $rv->resort_confirmation_number;
           $table[$k]['debit'] = '';
           
           $i++;
       }
       //get the rooms booked
       $sql = "SELECT t.id, t.transactionType, t.data, t.datetime, a.record_id, a.price, a.check_in_date, a.resort_confirmation_number, b.ResortName, c.name AS unit_type  FROM 
              wp_gpxTransactions t
              LEFT OUTER JOIN wp_room a ON t.weekID=a.record_id
              LEFT OUTER JOIN wp_resorts b ON b.id=a.resort
              LEFT OUTER JOIN wp_unit_type c on c.record_id=a.unit_type
              WHERE t.userID='".$id."'
              AND t.cancelled IS NULL 
              ORDER BY t.datetime";
       $results = $wpdb->get_results($sql);

       foreach($results as $rv)
       {
           $k = strtotime($rv->datetime).$i;
           
           $data = json_decode($rv->data);
           
           $debit = '';
           if(strtolower($data->WeekType) == 'rental')
           {
               $debit = "-$".$data->Paid;
           }
           
           $activity = ucwords($rv->transactionType);
           if($rv->transactionType == 'pay_debit')
           {
               $activity = 'Pay Debit';
               $debit = "$".$data->Paid;
           }
           
           $checkin = '';
           if(!empty($rv->check_in_date))
           {
               $checkin = date('m/d/Y', strtotime($rv->check_in_date));
           }
           $table[$k]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=transactions_view&id='.$rv->id.'" class="in-modal"><i class="fa fa-eye" aria-hidden="true"></i></a>';
           $table[$k]['ID'] = $rv->record_id;
           $table[$k]['activity'] = $activity;
           $table[$k]['check_in_date'] = $checkin;
           $table[$k]['resort'] = $rv->ResortName;
           $table[$k]['unit_type'] = $rv->unit_type;
           $table[$k]['resort_confirmation_number'] = $rv->resort_confirmation_number;
           $table[$k]['guest_name'] = $data->GuestName;
           $table[$k]['debit'] = $debit;
           
           $i++;
       }
       
       $sql = "SELECT t.id, t.release_on, a.record_id, a.check_in_date, a.resort_confirmation_number, b.ResortName, c.name AS unit_type FROM wp_gpxPreHold t
              INNER JOIN wp_room a ON t.weekID=a.record_id
              INNER JOIN wp_resorts b ON b.id=a.resort
              INNER JOIN wp_unit_type c on c.record_id=a.unit_type
              WHERE t.user='".$id."' AND t.released=0 ORDER BY t.release_on";       
       $results = $wpdb->get_results($sql);

       foreach($results as $rv)
       {
           $k = strtotime($rv->release_on).$i;
           
           $table[$k]['edit'] = '<a href="#" data-id="'.$rv->id.'" class="release-week" title="release"><i class="fa fa-calendar-times-o" aria-hidden="true"></i></a>';
           $table[$k]['ID'] = $rv->record_id;
           $table[$k]['activity'] = 'Held';
           $table[$k]['check_in_date'] = date('m/d/Y', strtotime($rv->check_in_date));
           $table[$k]['resort'] = $rv->ResortName;
           $table[$k]['unit_type'] = $rv->unit_type;
           $table[$k]['resort_confirmation_number'] = $rv->resort_confirmation_number;
           $table[$k]['guest_name'] = '';
           $table[$k]['debit'] = '';
           
           $i++;
       }
       ksort($table);
       
       $table = array_values($table);
       
       wp_send_json($table);
       wp_die();
   }
   add_action('wp_ajax_gpx_tp_activity', 'gpx_tp_activity');
function gpx_Room() 
{
        global $wpdb;

        $data = array();
        
        $where = '';
        
        if(isset($_REQUEST['Archived']))
        {
            $andWheres[] = " r.archived='".$_REQUEST['Archived']."'";
        }
        
		
        if(isset($_REQUEST['future_dates']) && $_REQUEST['future_dates'] == '0')
        {
            
        }
		else
        {
        	$andWheres[] = "r.check_in_date >= '".date('Y-m-d')."'";
        }
        
        $orderBy;
        $limit;
        $offset;
        
//         if(isset($_REQUEST['from_date']) && isset($_REQUEST['to_date']))
//         {
//             $from_date = $_REQUEST['from_date'];
//             $to_date   = $_REQUEST['to_date'];

//             $where = "(`check_in_date` >= '".date($from_date)."' AND check_in_date <= '".date($to_date)."') and resort !='0' and resort !='null' and unit_type !='null' ".$archived;
//         }else
//         {
//             $where = "(`check_in_date` != '0000-00-00 00:00:00' or `check_out_date` != '0000-00-00 00:00:00') and resort !='0' and resort !='null' and unit_type !='null' ".$archived;
//         }
        if(isset($_REQUEST['filter']))
        {

            $search = json_decode(stripslashes($_REQUEST['filter']));
            foreach($search as $sk=>$sv)
            {
                if(isset($_GET['filter_debug']))
                {
                	echo '<pre>'.print_r($sk, true).'</pre>';
                	echo '<pre>'.print_r($sv, true).'</pre>';
                }
                if($sk == 'record_id')
                {
                    $andWheres[] = "CAST(r.record_id as CHAR) LIKE '".$sv."%'";
                }
                elseif($sk == 'check_in_date')
                {
                    $andWheres[] = $sk ." BETWEEN '".date('Y-m-d 00:00:00', strtotime($sv))."' AND '".date('Y-m-d 23:59:59', strtotime($sv))."' ";
                }
				elseif($sk == 'active')
                {
                	if(strtolower($sv) == 'yes')
                    {
                    	$sv = 1;
                    }
                	else
                    {
                    	$sv = 0;
                    }
					$andWheres[] = "r.active=".$sv;
                }
                else
                {
                    $andWheres[] = $sk." LIKE '%".$sv."%'";
                }
            }
        }

        if(!empty($andWheres))
        {
            $where = " WHERE ".implode(" AND ", $andWheres);
        }

		if(!empty($wheres))
        {

            if(empty($where))
            {
                $where = ' WHERE ';
            }
            else 
            {
                $where .= ' AND ';
            }
			$where .= "(".implode(" OR ", $wheres).")";
		}
        
        if(isset($_REQUEST['sort']))
        {
            $orderBy = " ORDER BY ".$_REQUEST['sort']." ".$_REQUEST['order'];
        }
        if(isset($_REQUEST['limit']))
        {
            $limit = " LIMIT ".$_REQUEST['limit'];
        }
        if(isset($_REQUEST['offset']))
        {
            $offset = " OFFSET ".$_REQUEST['offset'];
        }
        if(isset($_REQUEST['from_date']) && isset($_REQUEST['to_date']))
        {
            $limit = " LIMIT 20";
        }
        $sql = "SELECT  r.*,
                u.name as room_type,
                rs.ResortName,
                ps.name as source_name,
                pg.name as given_name
                FROM `wp_room` r
                    INNER JOIN wp_unit_type u
                    on u.record_id=r.unit_type
                    INNER JOIN wp_resorts rs
                    ON rs.id=r.resort
                    LEFT OUTER JOIN wp_partner ps
                    ON r.source_partner_id=ps.user_id
                    LEFT OUTER JOIN wp_partner pg
                    ON r.given_to_partner_id=ps.user_id";
        if(!empty($where))
        {
            $sql .= $where;
        }
        $sql .= $orderBy;
        $sql .=  $limit;
        $sql .=  $offset;

        $tsql = "SELECT COUNT(r.record_id) as cnt  FROM `wp_room` r

                    INNER JOIN wp_unit_type u
                    on u.record_id=r.unit_type
                    INNER JOIN wp_resorts rs
                    ON rs.id=r.resort
                    LEFT OUTER JOIN wp_partner ps
                    ON r.source_partner_id=ps.user_id
                    LEFT OUTER JOIN wp_partner pg
                    ON r.given_to_partner_id=ps.user_id";
        if(!empty($where))
        {
            $tsql .= $where;
        }
        $data['total'] = (int) $wpdb->get_var($tsql);
        
        $i = 0;
        $results = $wpdb->get_results($sql);
      
        if(isset($_GET['debug_room']))
        {
            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
            echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
            echo '<pre>'.print_r($wpdb->last_result, true).'</pre>';
            if(isset($_GET['exit']))
            {
                exit;
            }
        }
        
        foreach($results as $result)
        {
            //what is the status
            if($result->active == '1')
            {
                $result->status = 'Available';
            }
            else 
            {
                $sql = "select `gpx`.`wp_gpxTransactions`.`weekId`
						from `gpx`.`wp_gpxTransactions` where `gpx`.`wp_gpxTransactions`.`weekId` = '".$result->record_id."' AND `gpx`.`wp_gpxTransactions`.`cancelled` IS NULL";
                $booked = $wpdb->get_var($sql);
                
                if(!empty($booked))
                {
                    $result->status = 'Booked';
                }
                else
                {
                    $sql = "select `wp_gpxPreHold`.`weekId`
                        from `wp_gpxPreHold`
                        where (`wp_gpxPreHold`.`released` = 0) AND `wp_gpxPreHold`.`weekId`='".$result->record_id."'";
                    $held = $wpdb->get_var($sql);
                    if(!empty($held))
                    {
                        $result->status = 'Held';
                    }
//                     elseif(strtotime($result->check_in_date) > strtotime('NOW'))
//                     {
//                         $result->status = '';
//                     }
                    else
                    {
                        $result->status = 'Available';
                    }
                }
            }
            
                $data['rows'][$i]['action'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id='.$result->record_id.'"><i class="fa fa-pencil" aria-hidden="true"></i></a>';    
                $data['rows'][$i]['action'] .= '&nbsp;&nbsp;<a href="#" class="deleteWeek" data-id='.$result->record_id.'"><i class="fa fa-trash" aria-hidden="true" style="color: #d9534f;"></i></a>';    
                $data['rows'][$i]['record_id'] = $result->record_id;
                $data['rows'][$i]['create_date'] = $result->create_date;
                $data['rows'][$i]['last_modified_date'] = $result->last_modified_date;
                $data['rows'][$i]['check_in_date'] = date('m/d/Y', strtotime($result->check_in_date));
                $data['rows'][$i]['check_out_date'] = date('m/d/Y', strtotime($result->check_out_date));
                $data['rows'][$i]['price'] = '';
                $data['rows'][$i]['room_type'] = $result->room_type;
                $data['rows'][$i]['unit_type_id'] = $result->room_type;
                if($result->type != '1' && !empty($result->price))
                {
                    $data['rows'][$i]['price'] = '$'.$result->price;
                }
                $data['rows'][$i]['source_partner_id'] = $result->source_name;
                $data['rows'][$i]['ResortName'] = $result->ResortName;
                
                $data['rows'][$i]['sourced_by_partner_on'] = $result->sourced_by_partner_on;
                $data['rows'][$i]['resort_confirmation_number'] = $result->resort_confirmation_number;
                $data['rows'][$i]['active'] = $result->active;
                
                $data['rows'][$i]['available_to_partner_id'] = $result->given_name;
                $data['rows'][$i]['room_status'] = $result->status;
                

                $active = "";
                if(isset($result->active)){

                    if($result->active == 1){
                        $active = "Yes";
                    }
                    else{
                        $active = "No";
                        if(isset($result->Held) && $result->Held > 0)
                        {
                            $active = 'Held';
                        }
                    }   
                }
                
                $archive = "";
                if(isset($result->archived)){

                    if($result->archived == 1){
                        $archive = "Yes";
                    }
                    else{
                        $archive = "No";
                    }   
                }

                $data['rows'][$i]['active'] = $active;
                $data['rows'][$i]['archived'] = $archive;

                $type = "";
                if(isset($result->type)){
                    if($result->type == 1){
                        $type = "Exchange";
                    }
                    elseif($result->type == 2){
                     $type = "Rental";   
                    }
                    elseif($result->type == 3){
                        $type = 'Exchange/Rental';
                    }
                    else{
                     $type = "--";      
                    }

                }

                $data['rows'][$i]['type'] = $type;
                // $data[$i]['points'] = $result->points;
                // $data[$i]['note'] = $result->note;
                
                $i++;
        }
        
        wp_send_json($data);
        wp_die();

    }

   add_action('wp_ajax_gpx_Room', 'gpx_Room');
   add_action('wp_ajax_nopriv_gpx_Room', 'gpx_Room');


   function gpx_remove_room()
   {
       global $wpdb;
       
       $return['success'] = false;
       if(!empty($_REQUEST['id']))
       {
           $return['success'] = true;
           
           $sql = "SELECT source_partner_id, update_details FROM wp_room WHERE record_id='".$_REQUEST['id']."'";
           $roomRow = $wpdb->get_row($sql);

           $sql = "SELECT id FROM wp_gpxTransactions WHERE weekId='".$_REQUEST['id']."'";
           $row = $wpdb->get_row($sql);
           
           //Need to add capability to delete/archive weeks. If a week has had a booking on it, it should only be able to be archived (to keep the history intact). Weeks without a booking can be truly deleted from the database.
           if(empty($row))
           {
               $wpdb->delete('wp_room', array('record_id'=>$_REQUEST['id']));
               $return['deleted'] = true;
           }
           else 
           {
               $row = $roomRow; 
               
               $updateDets = json_decode($row->update_details, ARRAY_A);
               
               $updateDets[strtotime('NOW')] = [
                   'update_by' => get_current_user_id(),
                   'details'=>base64_encode(json_encode(array('room_archived'=>date('m/d/Y H:i:s')))),
               ];
               
               $data = [
                   'active'=>'0',
                   'archived'=>'1',
                   'update_details'=>json_encode($updateDets),
               ];
               
               $wpdb->update('wp_room', $data, array('record_id'=>$_REQUEST['id']));
               
               $return['success'] = true;
               $return['archived'] = true;
           }
           //if this was a trade partner then adjust their rooms given
           if($roomRow->source_partner_id != 0)
           {
               $sql = "UPDATE wp_partner set no_of_rooms_given = no_of_rooms_given - 1 WHERE user_id='".$roomRow->source_partner_id."'";
               $wpdb->query($sql);
           }
       }
       
       wp_send_json($return);
       wp_die();
   }
   add_action('wp_ajax_gpx_remove_room', 'gpx_remove_room');
   function gpx_Room_error_ajax() { 

        global $wpdb;
        $sql = "SELECT *  FROM `wp_room` WHERE `check_in_date` = '0000-00-00 00:00:00' or `check_out_date` = '0000-00-00 00:00:00' or resort ='0' or resort ='null' or unit_type ='null'";
        $results = $wpdb->get_results($sql);
        wp_send_json($results);
        wp_die();


   }

   add_action('wp_ajax_gpx_Room_error_ajax', 'gpx_Room_error_ajax');
   add_action('wp_ajax_nopriv_gpx_Room_error_ajax', 'gpx_Room_error_ajax');

function gpx_remove_report()
{
   global $wpdb;
   $data = [];
   
   if(isset($_POST['id']))
   {
       $wpdb->delete('wp_gpx_report_writer', array('id'=>$_POST['id']));
   }
   
   $data['success'] = true;
   
   wp_send_json($data);
   wp_die();
}
add_action('wp_ajax_gpx_remove_report', 'gpx_remove_report');

   function gpx_Room_error_page() { 


        global $wpdb;
        $sql = "SELECT *  FROM `wp_room` WHERE `check_in_date` = '0000-00-00 00:00:00' or `check_out_date` = '0000-00-00 00:00:00' or resort ='0' or resort ='null' or unit_type ='null'";
        $results = $wpdb->get_results($sql);
        $i = 0;
        $data = array();
        
        foreach($results as $result)
        {
                    
                $data[$i]['record_id'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id='.$result->record_id.'"><i class="fa fa-pencil" aria-hidden="true"></i><i class="fa fa-warning" aria-hidden="true" style="font-size:18px;color:red"></i></a>';  
                $data[$i]['ID'] = $result->record_id;
                $data[$i]['create_date'] = $result->create_date;
                $data[$i]['last_modified_date'] = $result->last_modified_date;
                $data[$i]['check_in_date'] = $result->check_in_date;
                $data[$i]['check_out_date'] = $result->check_out_date;

                $unit_type = "SELECT * FROM `wp_unit_type` WHERE `record_id` = '".$result->unit_type."'";
                $unit = $wpdb->get_results($unit_type); 
                $data[$i]['unit_type_id'] = $unit[0]->name;

                $spid = "SELECT * FROM wp_users a INNER JOIN wp_usermeta b on a.ID=b.user_id WHERE b.meta_key='DAEMemberNo' AND ID = '".$result->source_partner_id."'";
                $spid_result = $wpdb->get_results($spid);

                 $res = "SELECT *  FROM `wp_resorts` WHERE `id` = '".$result->resort."'";
                $res_result = $wpdb->get_results($res);

                $data[$i]['resort'] = $res_result[0]->ResortName;

                $data[$i]['source_partner_id'] = $spid_result[0]->display_name;


                $data[$i]['sourced_by_partner_on'] = $result->sourced_by_partner_on;
                $data[$i]['resort_confirmation_number'] = $result->resort_confirmation_number;
                $active = "";
                if(isset($result->active)){

                    if($result->active = 1){
                        $active = "Yes";
                    }
                    else{
                        $active = "No";
                    }   
                }

                $availability = "";

                if(isset($result->availability)){
                    
                    if($result->availability = 0){
                        $availability = "--";
                    }
                    elseif($result->availability = 1){
                        $availability = "All";
                    }
                    elseif($result->availability = 2){
                        $availability = "Owner Only";
                    }
                    else {
                        $availability = "Partner Only";
                    }
                    
                }


                $data[$i]['active'] = $active;
                $data[$i]['availability'] = $availability;

                $avltop = "SELECT * FROM `wp_partner` WHERE record_id = '".$result->available_to_partner_id."'";
                $avltop_result = $wpdb->get_results($avltop);

                $data[$i]['available_to_partner_id'] = $avltop_result[0]->name;

                $type = "";
                if(isset($result->type)){
                    
                    if($result->type == 1){
                        $type = "Exchange";
                    }
                    elseif($result->type == 2){
                     $type = "Rental";   
                    }
                    elseif($result->type == 3){
                     $type = "Exchange/Rental";   
                    }

                }

                $data[$i]['type'] = $type;
                // $data[$i]['points'] = $result->points;
                // $data[$i]['note'] = $result->note;
                
                $i++;
        }
        
        wp_send_json($data);
        wp_die();

    }

   add_action('wp_ajax_gpx_Room_error_page', 'gpx_Room_error_page');
   add_action('wp_ajax_nopriv_gpx_Room_error_page', 'gpx_Room_error_page');



function get_gpx_upgrade_fees()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
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
    wp_die();    
}
add_action('wp_ajax_get_gpx_upgrade_fees', 'get_gpx_upgrade_fees');
add_action('wp_ajax_nopriv_get_gpx_upgrade_fees', 'get_gpx_upgrade_fees');

function get_gpx_users_switch()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_get_gpx_users_switch();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_users_switch', 'get_gpx_users_switch');
add_action('wp_ajax_nopriv_get_gpx_users_switch', 'get_gpx_users_switch');

function create_dae_user()
{
    global $wpdb;
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $memberDetails = array(
        'AccountName'=>'Test, User',  
        'Address1'=>'209 S Walnut St',  
        'Address3'=>'McPherson',  
        'Address4'=>'KS',  
        'Address5'=>'USA',  
        'Email'=>'chris@gogowebdev.com',  
        'Email2'=>'chris@4eightyeast.com',  
        'Salutation'=>'Mr',  
        'Title1'=>'Title 1',  
        'Title2'=>'Title 2',  
        'FirstName1'=>'Chris',  
        'HomePhone'=>'6207556898',  
        'LastName1'=>'Goering',  
        'MailName'=>'Chris Goering',  
        'NewsletterStatus'=>'NOT_SUBSCRIBED',  
        'PostCode'=>'67460',  
        'ReferalID'=>'0',  
        'MailOut'=>True,  
        'SMSStatus'=>'NOT_SUBSCRIBED',  
        'SMSNumber'=>'6207556898',  
    );
    
    $data = $gpx->DAECreateMemeber($memberDetails);
    
    wp_send_json($data);
    wp_die();
}



add_action('wp_ajax_create_dae_user', 'create_dae_user');
add_action('wp_ajax_nopriv_create_dae_user', 'create_dae_user');

function salesforce_connect()
{

//     require_once GPXADMIN_API_DIR.'/functions/class.salesforce.php';
//     $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
    $sf = Salesforce::getInstance();
    /*  query test
     *
     * 
     *      */
    
    $query = "select owner_id__c, property_owner__c, id from ownership_interval__c where ROID_Key_480East__c ='R04351163321A14H08'";
    $data = $sf->query($query);
echo '<pre>'.print_r($data[0]->fields, true).'</pre>';
    /* insert test
     * 
     */
    
//     $fields = array (
//         'Ownership_Interval__c' => $data[0]->fields->ownership_interval__c,
//         'Deposit_Ref_No__c' => '654321',
//         'Deposit_Start_Date__c' => '2017-04-11',
//         'Deposit_End_Date__c' => '2017-01-11'
//     );
    
//     $sObject = new SObject();
//     $sObject->fields = $fields;
//     $sObject->type = 'GPX_Transaction__c';
    
//     $insert = array($sObject);
    
//     $data = $sf->insertTransaction($insert);
    
    //$data = $sf->setLoginScopeHeader();

    echo wp_send_json($data);
    exit();
}
add_action("wp_ajax_salesforce_connect","salesforce_connect");
add_action("wp_ajax_nopriv_salesforce_connect", "salesforce_connect");

function get_gpx_promos()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_promos();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_promos', 'get_gpx_promos');
add_action('wp_ajax_nopriv_get_gpx_promos', 'get_gpx_promos');

function get_gpx_desccoupons()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->return_gpx_desccoupons();
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_desccoupons', 'get_gpx_desccoupons');

function get_gpx_customrequests()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->return_gpx_customrequests();
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_get_gpx_customrequests', 'get_gpx_customrequests');
add_action('wp_ajax_nopriv_get_gpx_customrequests', 'get_gpx_customrequests');

function get_gpx_regions()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_regions();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_regions', 'get_gpx_regions');
add_action('wp_ajax_nopriv_get_gpx_regions', 'get_gpx_regions');

function get_gpx_region_list()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $country = '';
    $region = '';
    if(isset($_REQUEST['country']))
        $country = $_REQUEST['country'];
    if(isset($_REQUEST['region']))
        $region = $_REQUEST['region'];
    
    $data = $gpx->return_gpx_region_list($country,$region);

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_region_list', 'get_gpx_region_list');
add_action('wp_ajax_nopriv_get_gpx_region_list', 'get_gpx_region_list');

function add_gpx_region()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->return_gpx_add_edit_region();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_add_gpx_region', 'add_gpx_region');
add_action('wp_ajax_nopriv_add_gpx_region', 'add_gpx_region');

function get_gpx_regionsassignlist()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_regionsassignlist();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_regionsassignlist', 'get_gpx_regionsassignlist');
add_action('wp_ajax_nopriv_get_gpx_regionsassignlist', 'get_gpx_regionsassignlist');
function get_gpx_transactions()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $tradepartner = '';
    if(isset($_GET['tradepartner']))
    {
        $tradepartner = true;
    }
    $data = $gpx->return_gpx_transactions($tradepartner);
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_transactions', 'get_gpx_transactions');
function gpx_admin_owner_transactions()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
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
    wp_die();
}
add_action('wp_ajax_gpx_admin_owner_transactions', 'gpx_admin_owner_transactions');
function get_gpx_holds()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
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
    wp_die();
}

add_action('wp_ajax_get_gpx_holds', 'get_gpx_holds');
add_action('wp_ajax_nopriv_get_gpx_holds', 'get_gpx_holds');

function gpx_release_week()
{
    global $wpdb;
    
    $activeUser = get_userdata(get_current_user_id());
   
    $sql = "SELECT propertyID, data FROM wp_gpxPreHold WHERE id='".$_POST['id']."'";
    $row = $wpdb->get_row($sql);
    
    $holdDets = json_decode($row->data, true);
    $holdDets[strtotime('now')] = [
        'action'=>'released',
        'by'=>$activeUser->first_name." ".$activeUser->last_name,
    ];
    
    $wpdb->update('wp_gpxPreHold', array('released'=>'1', 'data'=>json_encode($holdDets)), array('id'=>$_POST['id']));

    
    $sql = "SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId='".$row->propertyID."' AND cancelled IS NULL";
    $trow = $wpdb->get_var($sql);
    
    if($trow > 0)
    {
        //nothing to do
    }
    else
    {
        $wpdb->update('wp_room', array('active'=>'1'), array('record_id'=>$row->propertyID));
    }
    

    
    $data['success'] = true;

    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_release_week', 'gpx_release_week');

function gpx_credit_action()
{
    global $wpdb;
    
    if(isset($_POST['id']))
    {
        
        $sf = Salesforce::getInstance();

        if(get_current_user_id() == 5)
        {
            echo '<pre>'.print_r($_POST['type'], true).'</pre>';
        }
        $pendingStatus = '';
        if($_POST['type'] == 'deposit_transferred')
        {
            $pendingStatus = 1;
            $sql = "SELECT creditID, data FROM wp_gpxDepostOnExchange WHERE id='".$_POST['id']."'";
            $doe = $wpdb->get_row($sql);
            
            $_POST['id'] = $doe->creditID;
            $_POST['type'] = 'transferred';
            
            $depositData = json_decode($doe->data);
            
//             $sql = "SELECT SPI_Owner_Name_1st__c FROM wp_GPR_Owner_ID__c WHERE user_id='".$depositData->owner_id."'";
//             $ownerName = $wpdb->get_var($sql);
            
            $sfCreditData = [
                'Account_Name__c'=>$depositData->Account_Name__c,
                'Check_In_Date__c'=>date('Y-m-d', strtotime($depositData->check_in_date)),
                'GPX_Member__c'=>$depositData->owner_id,
                'Deposit_Date__c'=>date('Y-m-d'),
                //             'GPX_Resort__c'=>$_POST['GPX_Resort__c'],
                'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;", $depositData->Resort_Name__c)),
                'GPX_Resort__c'=>$depositData->GPX_Resort__c,
                'Resort_Unit_Week__c'=>$depositData->Resort_Unit_Week__c,
            ];

            $tDeposit = [
                'status'=>'Pending',
                'unitinterval'=>$depositData->unitweek,
            ];
        }
        
        $sql = "SELECT * FROM wp_credit WHERE id='".$_POST['id']."'";
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
        
        if(get_current_user_id() == 5)
        {
            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
            echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
            echo '<pre>'.print_r($sfCreditData, true).'</pre>';
            echo '<pre>'.print_r($sfDepositAdjust, true).'</pre>';
        }
        
        //send the datails to SF as a transaction
        
        $sql = "SELECT record_id FROM wp_partner WHERE user_id=".$credit->owner_id;
        $partner = $wpdb->get_row($sql);
        
        $poro = 'USA GPX Member';
        if(!empty($partner))
        {
            $poro = 'USA GPX Trade Partner';
        }
        
        $pt = 'Donation';
        $transactionType = 'credit_donation';
        if($_POST['type'] == 'transferred')
        {
            $pt = 'Transfer to Perks';
            $transactionType = 'credit_transfer';
            $ice = post_IceMemeber($credit->owner_id, true);
            
            if(isset($_REQUEST['icedebug']))
            {
                echo '<pre>'.print_r($ice, true).'</pre>';
            }
            
            $data['redirect'] = $ice['redirect'];
        }
        
        $sql = "SELECT * FROM wp_GPR_Owner_ID__c WHERE user_id=".$credit->owner_id;
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
        //         $sfData['RecordTypeId'] = '0121k00000167ZE';
        $sfData['RecordTypeId'] = '0121W000000QQ75';
        
        if($pt == 'Transfer to Perks')
        {
            $sfData['ICE_Account_ID__c'] = $usermeta->ICEUserName;
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
        
        //         $sfAdd = $sf->gpxCreate($sfData, 'true');
        if(get_current_user_id() == 5)
        {
//             echo '<pre>'.print_r($sfData, true).'</pre>';
//             echo '<pre>'.print_r($sfAdd, true).'</pre>';
        }
        
        if(isset($sfAdd[0]->id))
        {
            $sfTransactionID = $sfAdd[0]->id;
            $sfDB = array(
                'sfid'=> $sfTransactionID,
                'sfData'=>json_encode(array('insert'=>$sfData)),
            );
            
            $wpdb->update('wp_gpxTransactions', $sfData, array('id'=>$transactionID));
        }
        
        $data['action'] = ucfirst($_POST['type']);
    }
    $data['success'] = true;
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_credit_action', 'gpx_credit_action');
add_action('wp_ajax_gpx_credit_action', 'gpx_credit_action');

function gpx_extend_week()
{
    global $wpdb;
    
    $sql = "SELECT user, weekId FROM wp_gpxPreHold WHERE id='".$_REQUEST['id']."'";
    $row = $wpdb->get_row($sql);
    
    $cid = $row->user;
    
    $sql = "SELECT id FROM wp_gpxPreHold WHERE user != '".$row->user."' AND weekId='".$row->weekId."' AND released=0";
    $dup = $wpdb->get_row($sql);
    
    if(!empty($dup))
    {
        //this is a duplicate return an error
        $data['error'] = 'Another owner has this week on hold.';
        wp_send_json($data);
        wp_die();
    }
    
    $newdate = date('Y-m-d 23:59:59', strtotime('+1 DAY'));
    
    if(isset($_REQUEST['newdate']) && !empty($_REQUEST['newdate']))
    {
        $newdate = date('Y-m-d 23:59:59', strtotime($_REQUEST['newdate']));
    }
    
    $wpdb->update('wp_gpxPreHold', array('release_on'=>$newdate, 'released'=>'0'), array('id'=>$_POST['id']));

    $sql = "SELECT propertyID FROM wp_gpxPreHold WHERE id='".$_POST['id']."'";
    $row = $wpdb->get_row($sql);
    
    $wpdb->update('wp_room', array('active'=>'0'), array('record_id'=>$row->propertyID));
    $data['success'] = true;
    $data['cid'] = $cid;
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_extend_week', 'gpx_extend_week');

function gpx_transaction_fees_adjust()
{
    global $wpdb;
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    require_once GPXADMIN_API_DIR.'/functions/class.shiftfour.php';
    $shift4 = new Shiftfour();
    
    //send the data to sf
//     require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.salesforce.php';
//     $sf = new Salesforce();
    $sf = Salesforce::getInstance();
    
    $data = [];
    
    if(isset($_POST['id']))
    {

        $id = $_POST['id'];
        $refundType = $_POST['refundType'];
        $type = $_POST['type'];
        $amount = $_POST['amount'];
        
        $sql = "SELECT * FROM wp_gpxTransactions WHERE id='".$id."'";
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
            
//             $sfData['Purchase_Price__c'] = $updateData['WeekPrice'] += $amount;
            $sfData['GPXTransaction__c'] = $transaction;
        }
        if($type == 'couponDiscount')
        {
            //update the coupon discount amount
            $couponAmount = number_format(str_replace("$", $updateData['couponDiscount']), 2);
            $newCouponAmount = $couponAmount + $amount;
            $updateData['couponDiscount'] = '$'.$newCouponAmount;
            $updateData['refunded'] += $amount;
            
//             $sfData['Purchase_Price__c'] = $updateData['WeekPrice'] += $amount;
            $sfData['Reservation_Status__c'] = 'Cancelled';
            $sfData['GPXTransaction__c'] = $transaction;
        }
        if($type == 'discount')
        {
            //update the discount amount
            $discount = number_format(str_replace("$", $updateData['discount']), 2);
            $newdiscount = $discount + $amount;
            $updateData['discount'] = $newdiscount;
            $updateData['refunded'] += $amount;
            
//             $sfData['Purchase_Price__c'] = $updateData['WeekPrice'] += $amount;
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
//             $updateData['actguestFee'] = $newguestfee;
            $updateData['refunded'] += $amount;
            
//             $sfData['Guest_Fee__c'] = $updateData['actguestFee'];
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
//             $updateData['actupgradeFee'] = $newupgradefee;
            $updateData['refunded'] += $amount;
            
//             $sfData['Upgrade_Fee__c'] = $updateData['actupgradeFee'];
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
//             $updateData['actupgradeFee'] = $newupgradefee;
            $updateData['refunded'] += $amount;
            
//             $sfData['Credit_Extension_Fee__c'] = $updateData['actupgradeFee'];
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
//             $updateData['actcpoFee'] = $newcpofee;
            $updateData['refunded'] = $amount;
            
//             $sfData['CPO_Fee__c'] = $updateData['actcpoFee'];
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
//             $updateData['actWeekPrice'] = $newweekpricefee;
            $updateData['refunded'] += $amount;
          
//             $sfData['Purchase_Price__c'] = $newweekpricefee;
            $sfData['GPXTransaction__c'] = $transaction;
        }
        if($type == 'cancel')
        {
            //update the coupon amount
            $updateData['refunded'] += $amount;
            $wpdbUpdate['cancelled'] = 1;
            
            $sfData['Reservation_Status__c'] = 'Cancelled';
            $sfData['GPXTransaction__c'] = $transaction;
        }
        
        //don't over refund!
        if($amount > 0)
        {
//             //did they use a coupon?
//             if(isset($transData->couponDiscount))
//             {
//                 $ac[] = str_replace("$", "", $transData->couponDiscount);
//             }
            
//             $dc = '0';
//             if(isset($ac))
//             {
//                 //all of the coupons
//                 $dc = array_sum($ac);
//                 $amount = $amount - $dc;
//             }

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
            }
            
            if(isset($ca))
            {
                //remove the total refunded amount from the amount paid
                $paid = $paid - array_sum($ca);
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
                    wp_die();
                }
            }
            else
            {
                //create a coupon for this amount
                //does this slug exist?
                $slug = $trans->weekId.$trans->userID;
                $sql = "SELECT id FROM wp_gpxOwnerCreditCoupon WHERE couponcode='".$slug."'";
                $row = $wpdb->get_row($sql);
                if(!empty($row))
                {
                    //add random string to the end and check again
                    $rand = rand(1, 1000);
                    $slug = $slug.$rand;
                    $sql = "SELECT id FROM wp_gpxOwnerCreditCoupon WHERE couponcode='".$slug."'";
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
            
//             $sfData[''] = $totalAmount;
            
            $sfFields = [];
            $sfFields[0] = new SObject();
            $sfFields[0]->fields = $sfData;
            $sfFields[0]->type = 'GPX_Transaction__c';
//             $sfAdd = $sf->gpxTransactions($sfFields);
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
//         $wpdbUpdate['cancelledDate'] = date('Y-m-d', strtotime("NOW"));
        
        $wpdb->update('wp_gpxTransactions', $wpdbUpdate, array('id'=>$id));
        
        $data['success'] = true;
    }
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_transaction_fees_adjust', 'gpx_transaction_fees_adjust');

function gpx_cancel_booking($transaction='')
{
    global $wpdb;
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
//     require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.salesforce.php';
//     $sf = new Salesforce();
    $sf = Salesforce::getInstance();
    
    if(isset($_POST['transaction']))
    {
        $transaction = $_POST['transaction'];
    }
    
    $sql = "SELECT * FROM wp_gpxTransactions WHERE id='".$transaction."'";
    $transRow = $wpdb->get_row($sql);
    
    $transData = json_decode($transRow->data);
    $sfTransData = json_decode($transRow->sfData);
    $canceledData = json_decode($transRow->cancelledData);
    
    $refunded = '0';
 
    //is this a trade partner
    $sql = "SELECT * FROM wp_partner WHERE user_id='".$transRow->userID."'";
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
                $sql = "SELECT Type, PromoType, Amount FROM wp_specials WHERE id='".$coupon."'";
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
    }
    
    if($refunded == 0 && isset($transData->GuestFeeAmount) && $transData->GuestFeeAmount > 0)
    {
        $refunded = $refunded + $transData->GuestFeeAmount;
    }
    
    if (strpos(strtolower($transData->WeekType), 'exchange') !== false )
    {
        //need to refresh the credit
        $sql = "SELECT credit_used FROM wp_credit WHERE id='".$transData->creditweekid."'";
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
//     elseif(isset($_REQUEST['amt']))
//     {
//         $refunded = $_REQUEST['amt'];
//     }

    
    //we need to refund this transaction
    if($refunded > 0)
    {
        //max cannot exceed the amount paid minus the amount already refunded
//         if(!empty($canceledData))
//         {
//             foreach($canceledData as $cK=>$cD)
//             {
//                 $alredyRefunded[$cK] = $cD->amount;
//             }
//             $available = $netPaid - array_sum($alredyRefunded);
//             if($refunded > $available)
//             {
//                 $refunded = $available;
//             }
//             $cupdate[$cK] = $cD;
//         }
        
        //credit card or coupon
        if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'refund')
        {
            $refundType = 'refund';
            require_once GPXADMIN_API_DIR.'/functions/class.shiftfour.php';
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
        else
        {
            $refundType = 'credit';
            $slug = $transRow->weekId.$transRow->userID;
            $sql = "SELECT id FROM wp_gpxOwnerCreditCoupon WHERE couponcode='".$slug."'";
            $row = $wpdb->get_row($sql);
            if(!empty($row))
            {
                //add random string to the end and check again
                $rand = rand(1, 1000);
                $slug = $slug.$rand;
                $sql = "SELECT id FROM wp_gpxOwnerCreditCoupon WHERE couponcode='".$slug."'";
                $row = $wpdb->get_row($sql);
                if(!empty($row))
                {
                    //add random string to the end and check again
                    $rand = rand(1, 1000);
                    $slug = $slug.$rand;
                }
            }
            
            $occ = [
                'Name'=>$transRow->weekId,
                'Slug'=>$slug,
                'Active'=>1,
                'singleuse'=>0,
                'amount'=>$refunded,
                'owners'=>[$transRow->userID],
            ];
            $coupon = $gpx->promodeccouponsadd($occ);

            $data['html'] = "<h4>A $".$refunded." coupon has been generated.</h4>";
        }
    }
    
    $sfData['Reservation_Status__c'] = 'Cancelled';
    $sfData['GPXTransaction__c'] = $sfTransData->insert->GPXTransaction__c;
//     $sfData['EMS_Account__c'] = $sfTransData->insert->EMS_Account__c;
//     $sfData['GPX_Ref__c'] = $sfTransData->insert->GPX_Ref__c;
    
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
    
//     if(get_current_user_id() == 5)
//     {
//         echo '<pre>'.print_r($sfWeekData, true).'</pre>';
//         echo '<pre>'.print_r($sfCancelTransaction, true).'</pre>';
//         echo '<pre>'.print_r($sfWeekAvailable, true).'</pre>';
//     }
//     echo '<pre>'.print_r($sfAdd, true).'</pre>';
    //update the databse
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

    $sql = "SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId='".$transRow->weekId."' AND cancelled IS NULL";
    $trow = $wpdb->get_var($sql);
    
    if($trow > 0)
    {
        //nothing to do
    }
    else
    {
        $wpdb->update('wp_room', array('active'=>1), array('record_id'=>$transRow->weekId));
    }
    
    
    
    $data['success'] = true;
    $data['cid'] = $transRow->userID;
    $data['amount'] = $refunded;
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_gpx_cancel_booking', 'gpx_cancel_booking');
add_action('wp_ajax_nopriv_gpx_cancel_booking', 'gpx_cancel_booking');

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
//             echo '<pre>'.print_r($data, true).'</pre>';
            $wpdb->update('wp_gpxTransactions', $data, array('id'=>$row->id));
        }
    }
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_rework_add_cancelled_date', 'gpx_rework_add_cancelled_date');


function gpx_remove_guest()
{
    global $wpdb;
    
    $return = [];
    
    if(isset($_POST['transactionID']) && !empty($_POST['transactionID']))
    {
        $sql = "SELECT userID, data FROM wp_gpxTransactions WHERE id='".$_POST['transactionID']."'";
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
    wp_die();
}
add_action('wp_ajax_gpx_remove_guest', 'gpx_remove_guest');

function gpx_reasign_guest_name($postdata = '', $addtocart = '')
{
    global $wpdb;
    
    if(!empty($postdata))
    {
        $_POST = (array) $postdata;
    }

    $transaction = $_POST['transactionID'];
    
    $sql = "SELECT sfid, sfData, data, weekId, userID FROM wp_gpxTransactions
                WHERE id=".$transaction;
    $row = $wpdb->get_row($sql);
    
    $cid = $row->userID;
    
    $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $memberID ) );
    
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
            
//             $data['html'] .= '<button class="btn btn-secondary" class="close-modal">Cancel</a>';
        }
    }
    
    if(!isset($data))
    {
        
//         require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.salesforce.php';
//         $sf = new Salesforce();
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
        
        if(get_current_user_id() == 5)
        {
            echo '<pre>'.print_r($sfWeekAdd, true).'</pre>';
        }
//         if(isset($dbUpdate['sfid'])) // if this is set then we need to add the new id to the database
//         {
//             $dbUpdate['sfid'] = $sfAdd[0]->id;
//         }
//         else
//         {
//             $key = 'updated_'.strtotime("now");
            
//             $sfDB[$key] = [
//                 'by'=>get_current_user_id(),
//                 'data'=>$sfData,
//             ];
            
// //             $dbUpdate['sfData'] = json_encode($sfDB);
//         }
        
        if(!isset($sfAdd[0]->id))
        {
            //add the error to the sf data
            $sfDB['error'] = $sfAdd;
            $key = 'updated_'.strtotime("now");
            
            $sfDB[$key] = [
                'by'=>get_current_user_id(),
                'data'=>$sfData,
            ];
//             $dbUpdate['sfData'] = json_encode($sfDB);
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
        echo wp_send_json($data);
        exit();
    }
}
add_action('wp_ajax_gpx_reasign_guest_name', 'gpx_reasign_guest_name');
add_action('wp_ajax_nopriv_gpx_reasign_guest_name', 'gpx_reasign_guest_name');

function gpx_mass_import_to_sf()
{
    global $wpdb;
    
//     require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.salesforce.php';
//     $sf = new Salesforce();
    $sf = Salesforce::getInstance();
    
    $sql = "SELECT * FROM wp_gpxTransactions WHERE sfid=''";
    $all = $wpdb->get_results($sql);
    
    foreach($all as $row)
    {
        // update the transaction table
        $transaction = $row->id;
        
        $tData = json_decode($row->data, true);
        
        $today = strtotime('NOW');
        // if this check in isn't in the future then we can skip it
        if(strtotime($tData['checkIn']) < $today)
        {
            $wpdb->update('wp_gpxTransactions', array('sfid'=>'N/A'), array('id'=>$transaction));
            continue;
        }
        else
        {
            echo '<pre>'.print_r("this one".$row->id, true).'</pre>';
        }
        $sfDB = json_decode($row->sfData, true);
        
        $name = $tData['GuestName'];
        $name = trim($name);
        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim( preg_replace('#'.$last_name.'#', '', $name ) );
        
        $sfData['Guest_First_Name__c'] = $first_name;
        $sfData['Guest_Last_Name__c'] = $last_name;
        $sfData['Guest_Email__c'] = $tData['Email'];
        $sfData['of_Adults__c'] = $tData['Adults'];
        $sfData['of_Children__c'] = $tData['Children'];
        $sfData['GPXTransaction__c'] = $transaction;
        
        $sql = 'SELECT resortName FROM wp_properties WHERE weekId="'.$row->weekId.'"';
        $resort = $wpdb->get_row($sql);
        $resortName = $resort->resortName;
        
        $sfTaxAmount = 0;
        if(isset($tData['taxCharged']) && !empty($tData['taxCharged']))
        {
            $sfTaxAmount = $tData['taxCharged'];
        }
        $purchasePrice = $tData['Paid'];
        if(isset($sfTaxAmount) && !empty($sfTaxAmount))
        {
            $purchasePrice = $purchasePrice - $sfTaxAmount;
        }
        if($tData['CPO'] = 'NotApplicable')
        {
            $CPOFee = '0';
            $sfCPO = 'False';
        }
        else
        {
            $CPOFee = $tdData['CPOFee'];
            $purchasePrice = $purchasePrice - $CPOFee;
            $sfCPO = 'True';
        }
        if(empty($tData['UpgradeFee']))
        {
            $upgradeFee = 0;
        }
        else
        {
            $upgradeFee = $tData['UpgradeFee'];
            $purchasePrice = $purchasePrice - $upgradeFee;
        }
        $weekType = str_replace("Week", "", $tData['WeekType']);
        if(strtolower($prop->WeekType) == 'bonus')
        {
            $weekType = 'Rental';
        }
        // default role would be gpx_member.
        $userRole = 'USA GPX Member';
        $user_info = get_userdata($row->userID);
        foreach($user_info->roles as $role)
        {
            //if this user has the gpx_trade_partner role then we can change $userRole
            if($role == 'gpx_trade_partner');
            {
                $userRole = 'USA GPX Trade Partner';
            }
        }
        
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $row->userID ) );
        
        $sfData['Guest_Home_Phone__c']=$tData['HomePhone']; //home phone
        $sfData['Guest_Cell_Phone__c']=$tData['Mobile']; //cell phone
        $sfData['EMS_Account__c']=$tData['MemberNumber']; //EMS Account No
        $sfData['Reservation_Reference__c']=$row->weekId; //EMS Ref ID
        $sfData['Reserved_Resort_Name__c']=$resortName; //Resort
        $sfData['Check_In_Date__c']=date('Y-m-d', strtotime($tData['checkIn'])); //Check-in Date
        
        $sfData['Check_Out_Date__c']=date('Y-m-d', strtotime('+'.$tData['noNights'].' days', strtotime($tData['checkIn']))); //Check-out Date
        $sfData['Unit_Type__c']=$tData['bedrooms']; //Unit Type
        $sfData['Purchase_Type__c']=$weekType; //Week Type
        $sfData['Special_Requests__c']=$tData['SpecialRequest']; //Special Request
        $sfData['CPO_Opt_in__c']=$sfCPO; //CPO
        $sfData['Upgrade_Fee__c']=$upgradeFee; //Upgrade Fee
        $sfData['Purchase_Price__c']=$purchasePrice; //Full Price
        $sfData['CPO_Fee__c']=$CPOFee;
        $sfData['Member_Home_Phone__c']=$usermeta->DayPhone;
        $sfData['Member_Cell_Phone__c']=$usermeta->Mobile;
        $sfData['Member_First_Name__c']=$usermeta->FirstName1;
        $sfData['Member_Last_Name__c']=$usermeta->LastName1;
        $sfData['Account_Type__c']=$userRole;
        $sfData['Tax_Paid__c']=$sfTaxAmount;
        
        $dbUpdate['sfData'] = json_encode(array('imported'.strtotime("now")=>$sfData));
        
        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfData;
        $sfFields[0]->type = 'GPX_Transaction__c';
        $sfAdd = $sf->gpxTransactions($sfFields);
        
        if(!isset($sfAdd[0]->id))
        {
            //add the error to the sf data
            $sfDB['error'] = $sfAdd;
            $key = 'imported_'.strtotime("now");
            
            $sfDB[$key] = [
                'by'=>get_current_user_id(),
                'data'=>$sfData,
            ];
            $dbUpdate['sfData'] = json_encode($sfDB);
        }
        else
        {
            $dbUpdate['sfid'] = $sfAdd[0]->id;
        }
        
        
        $wpdb->update('wp_gpxTransactions', $dbUpdate, array('id'=>$transaction));
    }
    $data['success'] = true;
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_mass_import_to_sf', 'gpx_mass_import_to_sf');
add_action('wp_ajax_nopriv_gpx_mass_import_to_sf', 'gpx_mass_import_to_sf');

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

function gpx_transactions_add()
{
    global $wpdb;
    
    require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $eachTrans = explode(PHP_EOL, $_POST['transactions']);
    
    foreach($eachTrans as $trans)
    {
        $dets = explode(",", preg_replace('/\s+/', '', $trans));
        $memberNO = $dets[0];
        $weekID = $dets[1];
        
        //put the week on hold
        
    }
    
    
    $sql = "SELECT data FROM wp_gpxTransactions
            WHERE id=".$transaction;
    $row = $wpdb->get_row($sql);
    $tData = (array) json_decode($row->data);
    
    $tData['GuestName'] = $name;
    
    $wpdb->update('wp_gpxTransactions', array('data'=>json_encode($tData)), array('id'=>$transaction));
    
    $data['success'] = true;
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_transactions_add', 'gpx_transactions_add');
add_action('wp_ajax_nopriv_gpx_transactions_add', 'gpx_transactions_add');

function gpx_resorts_list()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    return $gpx->return_gpx_properties();    
}

function assign_gpx_region()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_assign_region();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_assign_gpx_region', 'assign_gpx_region');
add_action('wp_ajax_nopriv_assign_gpx_region', 'assign_gpx_region');

function get_gpx_switchuage()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $usage = '';
    $type = '';
    
    if(isset($_GET['usage']))
        $usage = $_GET['usage'];
    if(isset($_GET['type']))
        $type = $_GET['type'];
    
    $data = $gpx->return_gpx_switchuage($usage, $type);

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_switchuage', 'get_gpx_switchuage');
add_action('wp_ajax_nopriv_get_gpx_switchuage', 'get_gpx_switchuage');

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

function get_gpx_resorts()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_resorts();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_resorts', 'get_gpx_resorts');
add_action('wp_ajax_nopriv_get_gpx_resorts', 'get_gpx_resorts');

function gpx_store_resort()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_store_resort();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_gpx_store_resort', 'gpx_store_resort');
add_action('wp_ajax_nopriv_gpx_store_resort', 'gpx_store_resort');

function get_gpx_reportsearches()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_reportsearches();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_reportsearches', 'get_gpx_reportsearches');
add_action('wp_ajax_nopriv_get_gpx_reportsearches', 'get_gpx_reportsearches');

function gpx_update_names()
{
    global $wpdb;
    
    $sql = "SELECT * FROM wp_users  
            WHERE user_email = ''
            LIMIT 1";
    echo '<pre>'.print_r($sql, true).'</pre>';
    $rows = $wpdb->get_rows($sql);
    echo '<pre>'.print_r($rows, true).'</pre>';
    foreach($rows as $row)
    {
        echo '<pre>'.print_r($row, true).'</pre>';
    }
    
    
}
add_action('wp_ajax_gpx_update_names', 'gpx_update_names');
add_action('wp_ajax_nopriv_gpx_update_names', 'gpx_update_names');
function gpx_geocode_all()
{
    
    require_once WP_CONTENT_DIR.'/plugins/wp-store-locator/admin/class-geocode.php';
    $geocode = new WPSL_Geocode();

    $args = array( 
        'post_type' => 'wpsl_stores',
        'nopaging' => true,
        'meta_query'=> array(
            array(
                'key'=>'wpsl_lat',
                'value'=>null,
            )
        )
    );
    
    $loop = new WP_Query( $args );
    while ( $loop->have_posts() ) : $loop->the_post();
    $id = get_the_ID();

    $meta = get_post_meta($id);
    $dataarr = array(
        'address'=>'wpsl_address',
        'city'=>'wpsl_city',
        'state'=>'wpsl_state',
        'zip'=>'wpsl_zip',
        'country'=>'wpsl_country',
        'lat'=>'wpsl_lat',
        'lng'=>'wpsl_lng',
    );
    foreach($dataarr as $k=>$v)
    {
        
       if(isset($meta[$v]))
       {
           foreach($meta[$v] as $mv)
           {
               $store_data[$k] = $mv;
           }
       }
    }
    echo '<pre>'.print_r($store_data, true).'</pre>';
    
    $geocode->check_geocode_data($id, $store_data);
    endwhile;
    

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_gpx_geocode_all', 'gpx_geocode_all');
add_action('wp_ajax_nopriv_gpx_geocode_all', 'gpx_geocode_all');

function edit_gpx_resort()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->return_gpx_edit_gpx_resort();
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_edit_gpx_resort', 'edit_gpx_resort');
add_action('wp_ajax_nopriv_edit_gpx_resort', 'edit_gpx_resort');

function featured_gpx_resort()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->return_gpx_featured_gpx_resort();
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_featured_gpx_resort', 'featured_gpx_resort');
add_action('wp_ajax_nopriv_featured_gpx_resort', 'featured_gpx_resort');

function ai_gpx_resort()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->return_gpx_ai_gpx_resort();
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_ai_gpx_resort', 'ai_gpx_resort');
add_action('wp_ajax_nopriv_ai_gpx_resort', 'ai_gpx_resort');


function guest_fees_gpx_resort()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->return_guest_fees_gpx_resort();
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_guest_fees_gpx_resort', 'guest_fees_gpx_resort');
add_action('wp_ajax_nopriv_guest_fees_gpx_resort', 'guest_fees_gpx_resort');



function gpx_resort_image_update_attr()
{
    if(!empty($_POST['id']))
    {
        $id = $_POST['id'];
        $image = array(
            'ID'           => $id,
            'post_title'   => $_POST['title'],
        );
        wp_update_post( $image );
        
        update_post_meta($id, '_wp_attachment_image_alt', $_POST['alt']);
        update_post_meta($id, 'gpx_image_video', $_POST['video']);
        
        //update the image url
    }
    $data = ['success'=>true];
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_gpx_resort_image_update_attr', 'gpx_resort_image_update_attr');

function gpx_resort_attribute_new()
{
    global $wpdb;
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $post['resortID'] = $_POST['resort'];
    $post['type'] = $_POST['type'];
    $post['val'] = $_POST['val'];
    $post['from'] = $_POST['from'];
    $post['oldfrom'] = $_POST['oldfrom'];
    $post['to'] = $_POST['to'];
    $post['oldto'] = $_POST['oldto'];
    $post['list'] = $_POST['list'];
    $post['oldorder'] = $_POST['oldorder'];
    if($_POST['descs'])
    {
        $post['bookingpathdesc'] = $_POST['bookingpathdesc'];
        $post['resortprofiledesc'] = $_POST['resortprofiledesc'];
        $post['descs'] = $_POST['descs'];
    }
    
    
    $data = $gpx->return_resort_attribute_new($post);

    //Custom code
//     if($data["success"]==true)
//     {
//         $tablesprefix = 	$wpdb->prefix;
//         $tablename = "wp_resorts";
        
//         $result = $wpdb->get_results( " SELECT * FROM  wp_resorts_meta WHERE meta_key =  '".$post[type]."' AND  ResortID='".$post['resortID']."'   " ,ARRAY_A  );
//         $insert = json_encode($result[0]['meta_value']);
        
//         if($post[type]=="AlertNote" || $post[type]=="AdditionalInfo" )
//         {
//             $wpdb->query("UPDATE ".$tablename."  SET ".$post['type']."='".$post['val']."'
//             WHERE ResortID='".$post['resortID']."'  ");
//         }
//         else
//         {
//             $wpdb->query("UPDATE ".$tablename."  SET ".$post['type']."='".$result[0]['meta_value']."'
//             WHERE ResortID='".$post['resortID']."'  ");
//         }
//     }
    
    //Custom code End
//     $sf = sf_update_resorts($post['resortID']);
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_gpx_resort_attribute_new', 'gpx_resort_attribute_new');

function gpx_resort_attribute_remove()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $post['resortID'] = $_POST['resort'];
    $post['item'] = $_POST['item'];
    $post['type'] = $_POST['type'];
    
    $data = $gpx->return_resort_attribute_remove($post);
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_gpx_resort_attribute_remove', 'gpx_resort_attribute_remove');

function gpx_resort_attribute_reorder()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    foreach($_POST as $postKey=>$post)
    {
        if(is_array($post))
        {
            $input['order'] = $post;
        }
        else
        {
            $input[$postKey] = $post;
        }
    }
    
    $data = $gpx->return_gpx_resort_attribute_reorder($input);
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_gpx_resort_attribute_reorder', 'gpx_resort_attribute_reorder');

function gpx_resort_image_reorder()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    foreach($_POST as $postKey=>$post)
    {
        if(is_array($post))
        {
            $input['order'] = $post;
        }
        else
        {
            $input[$postKey] = $post;
        }
    }
    $data = $gpx->return_gpx_resort_image_reorder($input);
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_gpx_resort_image_reorder', 'gpx_resort_image_reorder');

function gpx_resort_repeatable_remove()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $post['from'] = $_POST['from'];
    $post['to'] = $_POST['to'];
    $post['type'] = $_POST['type'];
    $post['resortID'] = $_POST['resortID'];
    $post['oldorder'] = $_POST['oldorder'];
    
    $data = $gpx->return_gpx_resort_repeatable_remove($post);
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_gpx_resort_repeatable_remove', 'gpx_resort_repeatable_remove');

function gpx_image_remove()
{
    global $wpdb;
    
    $sql = "SELECT id, meta_value FROM wp_resorts_meta WHERE meta_key='images' AND ResortID='".$_POST['resortID']."'";
    $row = $wpdb->get_row($sql);
    $images = json_decode($row->meta_value);
    
    unset($images[$_POST['image']]);
    
    $updateImages = array('meta_value'=>json_encode($images));
    
    $wpdb->update('wp_resorts_meta', $updateImages, array('id'=>$row->id));
    
    $data['success'] = true;
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_gpx_image_remove', 'gpx_image_remove');

function active_gpx_resort()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->return_gpx_active_gpx_resort();
    
    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_active_gpx_resort', 'active_gpx_resort');
add_action('wp_ajax_nopriv_active_gpx_resort', 'active_gpx_resort');

function featured_gpx_region()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_featured_gpx_region();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_featured_gpx_region', 'featured_gpx_region');
add_action('wp_ajax_nopriv_featured_gpx_region', 'featured_gpx_region');

function hidden_gpx_region()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_hidden_gpx_region();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_hidden_gpx_region', 'hidden_gpx_region');
add_action('wp_ajax_nopriv_hidden_gpx_region', 'hidden_gpx_region');

function get_gpx_list_resorts()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_get_gpx_list_resorts($_POST['value'], $_POST['type']);

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_list_resorts', 'get_gpx_list_resorts');
add_action('wp_ajax_nopriv_get_gpx_list_resorts', 'get_gpx_list_resorts');

function add_gpx_promo()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->return_add_gpx_promo($_POST);

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_add_gpx_promo', 'add_gpx_promo');
add_action('wp_ajax_nopriv_add_gpx_promo', 'add_gpx_promo');


function gpx_get_coupon_template()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $selected = '';
    if(isset($_POST['selected']) && !empty($_POST['selected']))
        $selected = $_POST['selected'];

        $templates = $gpx->gpx_retrieve_coupon_templates($selected);

       echo wp_send_json(array('html'=>$templates));
        exit();
}
add_action('wp_ajax_gpx_get_coupon_template', 'gpx_get_coupon_template');
add_action('wp_ajax_nopriv_gpx_get_coupon_template', 'gpx_get_coupon_template');

function gpx_autocomplete_resort_fn() {
    header('content-type: application/json; charset=utf-8');
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';
    $term = stripslashes($term);
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $resorts = $gpx->return_gpx_resorts_by_name($term);
    
    $resort_search = array();
    if(!empty($term)){
        foreach($resorts as $item){
            $pos = strpos(strtolower($item), strtolower($term));
            if ($pos !== false) {
                $resort_search[] = $item;
            }
        }
        $resorts = $resort_search;
    }
    echo wp_send_json($resorts);
    exit();
}
add_action("wp_ajax_gpx_autocomplete_resort","gpx_autocomplete_resort_fn");
add_action("wp_ajax_nopriv_gpx_autocomplete_resort", "gpx_autocomplete_resort_fn");

function gpx_countryregion_dd()  
{
    header('content-type: application/json; charset=utf-8');
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $country = '';
    if(isset($_GET['country']))
        $country = $_GET['country'];
    
    $resorts = $gpx->return_gpx_countryregion_dd($country);
    
    echo wp_send_json($resorts);
    exit();    
}
add_action("wp_ajax_gpx_countryregion_dd","gpx_countryregion_dd");
add_action("wp_ajax_nopriv_gpx_countryregion_dd", "gpx_countryregion_dd");


function gpx_newcountryregion_dd()
{
    header('content-type: application/json; charset=utf-8');
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $country = '';
    if(isset($_GET['country']))
        $country = $_GET['country'];

        $resorts = $gpx->return_gpx_newcountryregion_dd($country);

        echo wp_send_json($resorts);
        exit();
}
add_action("wp_ajax_gpx_newcountryregion_dd","gpx_newcountryregion_dd");
add_action("wp_ajax_nopriv_gpx_newcountryregion_dd", "gpx_newcountryregion_dd");


function gpx_newcountryregion_dd_sc($atts)
{

    $atts = shortcode_atts(array('country'=>''), $atts);
    extract($atts);
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $resorts = $gpx->return_gpx_newcountryregion_dd($country);
    return $resorts;
}
add_shortcode('sc_newcountryregion_dd', 'gpx_newcountryregion_dd_sc');

function gpx_countryregion_dd_sc($atts)
{
    
    $atts = shortcode_atts(array('country'=>''), $atts);
    extract($atts);
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $resorts = $gpx->return_gpx_countryregion_dd($country);
    return $resorts;
}
add_shortcode('sc_countryregion_dd', 'gpx_countryregion_dd_sc');

function gpx_subregion_dd()
{
    header('content-type: application/json; charset=utf-8');
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $region = '';
    if(isset($_GET['selected_region']))
        $region = $_GET['selected_region'];

    $resorts = $gpx->return_gpx_subregion_dd($region);

    echo wp_send_json($resorts);
    exit();
}
add_action("wp_ajax_gpx_subregion_dd","gpx_subregion_dd");
add_action("wp_ajax_nopriv_gpx_subregion_dd", "gpx_subregion_dd");

function gpx_subregion_dd_sc($atts)
{
    $atts = shortcode_atts(array('type'=>'', 'region'=>'', 'country'=>''), $atts);
    extract($atts);
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $resorts = $gpx->return_gpx_subregion_dd($type, $region, $country);
    return $resorts;
}
add_shortcode('sc_gpx_subregion_dd', 'gpx_subregion_dd_sc');

function gpx_monthyear_dd()
{
    header('content-type: application/json; charset=utf-8');
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $country = '';
    $region = '';
    
    if(isset($_GET['country']))
        $country = $_GET['country'];
    
    if(isset($_GET['region']))
        $region = $_GET['region'];
    
    $resorts = $gpx->return_gpx_monthyear_dd($country, $region);

    echo wp_send_json($resorts);
    exit();
}
add_action("wp_ajax_gpx_monthyear_dd","gpx_monthyear_dd");
add_action("wp_ajax_nopriv_gpx_monthyear_dd", "gpx_monthyear_dd");

function resort_availability_calendar()
{
    header('content-type: application/json; charset=utf-8');
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $resort = '';
    $beds = '';
    $weektype = '';
    if(isset($_GET['resort']))
        $resort = $_GET['resort'];
    if(isset($_GET['beds']))
        $beds = $_GET['beds'];
    if(isset($_GET['weektype']))
        $weektype = $_GET['weektype'];
    $events = $gpx->resort_availability_calendar($resort, $beds, $weektype);

    echo wp_send_json($events);
    exit();
}
add_action("wp_ajax_resort_availability_calendar","resort_availability_calendar");
add_action("wp_ajax_nopriv_resort_availability_calendar", "resort_availability_calendar");

function request_password_reset()
{
    header('content-type: application/json; charset=utf-8');
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    if(isset($_POST['user_email']))
    {
        $userlogin = $_POST['user_email'];
    }
    if(isset($_POST['user_login']))
    {
        $userlogin = $_POST['user_login'];
    }
    if(isset($_POST['user_login_pwreset']))
    {
        $userlogin = $_POST['user_login_pwreset'];
    }
    $pw = $gpx->retrieve_password($userlogin);

    echo wp_send_json($pw);
    exit();
}
add_action("wp_ajax_request_password_reset","request_password_reset");
add_action("wp_ajax_nopriv_request_password_reset", "request_password_reset");

function gpx_validate_email()
{
    header('content-type: application/json; charset=utf-8');

    if(isset($_REQUEST['tp']))
    {
        if($_REQUEST['tp'] == 'email')
        {
            $val = sanitize_email($_REQUEST['val']);
            $exists = email_exists($val);
        }
        if($_REQUEST['tp'] == 'username')
        {
            $val = sanitize_text_field($_REQUEST['val']);
            $exists = username_exists($val);
        }
        if($exists)
        {
            $return['used'] = 'exists';
            $user = get_user_by('ID', $exists);

            $username = $user->user_login;
            $email = $user->user_email;
            $id = $user->ID;
            
            $return['html'] = '<h4>That '.$_REQUEST['tp']. ' is already in use.  Would you like to use this account?</h4><h4><button class="btn btn-primary" id="tp-use" data-email="'.$email.'" data-username="'.$username.'" data-id="'.$id.'">Yes</button> <button class="btn btn-secondary" id="tp-no">No</button>';
        }
    }
    else 
    {
        $exists = email_exists($_REQUEST['email']);
        
        if($exists)
        {
            $return = array('error'=>'That email already exists for an account in our system.  Please use another email address.' );
        }
        else 
        {
            $return = array("sucess"=>true);
        }
    }
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_validate_email","gpx_validate_email");
add_action("wp_ajax_nopriv_gpx_validate_email", "gpx_validate_email");


function do_password_reset()
{
    header('content-type: application/json; charset=utf-8');
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $redirectTo = '';
    
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
        $rp_key = $_REQUEST['rp_key'];
        $rp_login = $_REQUEST['rp_login'];
 
        $user = check_password_reset_key( $rp_key, $rp_login );
        
        if ( ! $user || is_wp_error( $user ) ) 
        {
            if ( $user && $user->get_error_code() === 'expired_key' ) 
            {
                $action = 'pwreset';
                $msg = 'Your key has expired.  Please request a new reset.';
            }
            else 
            {
                $action = 'pwreset';
                $msg = 'You used an invalid login.  Please request a new reset.';
            }
        }
        elseif ( isset( $_POST['pass1'] ) ) 
        {
            if ( $_POST['pass1'] != $_POST['pass2'] ) 
            {
                // Passwords don't match
                $action = 'pwset';
                $msg = "Passwords don't match";
            }
        
            elseif ( empty( $_POST['pass1'] ) ) 
            {
                // Password is empty
                $action = 'pwset';
                $msg = 'Password is empty.';
            }
            else 
            {
                reset_password( $user, $_POST['pass1'] );                
                $action = 'login';
                $msg = 'Password update successful.  You may now login with the new password.';
                $redirectTo = home_url();
            }
            
        }
        $pw = array('action'=>$action, 'msg'=>$msg, 'redirect'=>$redirectTo);
    }
    else 
    {
        $pw = array('action'=>'pwset', 'msg'=>'Invalid Request');
    }
    echo wp_send_json($pw);
    exit();
}
add_action("wp_ajax_do_password_reset","do_password_reset");
add_action("wp_ajax_nopriv_do_password_reset", "do_password_reset");

function gpx_change_password()
{
    $cid = $_POST['cid'];
    $pw1 = $_POST['chPassword'];

    $data['msg'] = 'System unavailable. Please try again later.';

        $user = get_user_by('ID', $cid);
        
        if(isset($_POST['hash']))
        {
            $pass = $_POST['hash'];

            if ( $user && wp_check_password( $pass, $user->data->user_pass, $user->ID) )
            {
                $up = wp_set_password($pw1, $user->ID);
                $data['msg'] = 'Password Updated!';
            }
            else
                $data['msg'] = 'Wrong password!';
        }
        else
        {
            $up = wp_set_password($pw1, $user->ID);
            $data['msg'] = 'Password Updated!';
        }


        echo wp_send_json($data);
        exit();
}
add_action("wp_ajax_gpx_change_password","gpx_change_password");
add_action("wp_ajax_nopriv_gpx_change_password", "gpx_change_password");

function gpx_load_data()
{
    header('content-type: application/json; charset=utf-8');
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    if(isset($_GET['load']))
        $load = $_GET['load'];
    
    $return = $gpx->$load($_GET['cid']);
    
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_load_data","gpx_load_data");
add_action("wp_ajax_nopriv_gpx_load_data", "gpx_load_data");

function gpx_twoforone_validate()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $return = $gpx->get_twoforone_validate($_POST['coupon'], $_POST['setdate'], $_POST['resortID']);
    
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_twoforone_validate","gpx_twoforone_validate");
add_action("wp_ajax_nopriv_gpx_twoforone_validate", "gpx_twoforone_validate");
function gpx_credit_donation()
{
    global $wpdb;
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    
    if(isset($_POST['Check_In_Date__c']))
    {
        //send the details to SF
//         require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.salesforce.php';
//         $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
        $sf = Salesforce::getInstance();
//         require_once GPXADMIN_API_DIR.'/functions/class.restsaleforce.php';
//         $gpxRest = new RestSalesforce();
        
        
        $sfDepositData = [
            'Account_Name__c'=>$_POST['GPX_Member__c'],
            'Check_In_Date__c'=>date('Y-m-d', strtotime($_POST['Check_In_Date__c'])),
            'Account_Name__c'=>$_POST['Account_Name__c'],
            'GPX_Member__c'=>$cid,
            'Deposit_Date__c'=>date('Y-m-d'),
            //             'GPX_Resort__c'=>$_POST['GPX_Resort__c'],
            'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;", $_POST['Resort_Name__c'])),
            'Resort_Unit_Week__c'=>$_POST['Resort_Unit_Week__c'],
            'GPX_Deposit_ID__c'=>$_POST['GPX_Deposit_ID__c'],
        ];
        
//         $results =  $gpxRest->httpPost($sfDepositData, 'GPX_Deposit__c');
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
    
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_credit_donation", "gpx_credit_donation");

function gpx_post_will_bank($postdata='', $addtocart = '')
{
    global $wpdb;
    require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    if(!empty($postdata))
    {
        $_POST = (array) $postdata;
    }
    
    $cid = get_current_user_id();
    
    if(isset($_COOKIE['switchuser']))
    {
        $cid = $_COOKIE['switchuser'];
    }
    
    $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
    
    $depositBy = stripslashes(str_replace("&", "&amp;",$usermeta->FirstName1))." ".stripslashes(str_replace("&", "&amp;",$usermeta->LastName1));

    $agent = false;
    if($cid != get_current_user_id())
    {
        $agent = true;
        $agentmeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( get_current_user_id() ) );
        $depositBy = stripslashes(str_replace("&", "&amp;",$agentmeta->first_name))." ".stripslashes(str_replace("&", "&amp;",$agentmeta->last_name));
        
    }
    
    $weekTypeError = false;
    if(isset($_POST['OwnershipType']))
    {
        switch($_POST['Usage__c'])
        {
            case 'Odd':
                if(date('Y', strtotime($_POST['Check_In_Date__c'])) % 2 == 0)
                    $weekTypeError = true;
                    break;
                    
            case 'Even':
                if(date('Y', strtotime($_POST['Check_In_Date__c'])) % 2 != 0)
                    $weekTypeError = true;
                    break;
                    
            default:
                $weekTypeError = false;
                break;
                
        }
    }
    
    $sql = "SELECT deposit_year FROM wp_credit WHERE deposit_year='".date('Y', strtotime($_POST['Check_In_Date__c']))."' AND interval_number='".$_POST['Contract_ID__c']."'";
    $duplicateYear = $wpdb->get_row($sql);
    
    
    /*
     * Ok, new thing - I just logged in as Owner Talie Dwayne Scott and tried to do a late 
     * deposit, but the option to bank within 14 days isn't there. It's functioning like 
     * prod does now, so I can't deposit multiple weeks for the same year either (which 
     * is allowed in beta).
     */
    if($agent)
    {
        //if this is an agent then duplicate year doesn't matter and datechecks only need payment form
        if( date("Y-m-d H:i:s", strtotime('+15 days')) > date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])))
        {
            $ldFee = get_option('gpx_late_deposit_fee');
            
            if(date("Y-m-d H:i:s", strtotime('+7 days')) > date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])))
            {
                $ldFee = get_option('gpx_late_deposit_fee_within');
            }
        }
    }
//     elseif(!empty($duplicateYear))
//     {
//         $return = array('success'=>true, 'message'=>'You have already banked this year!');
//     }
    elseif(date("Y-m-d H:i:s") > date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])))
    {
        $return = array('success'=>true, 'message'=>'You are not allowed to bank a previous date!');
    }
    elseif(date("Y-m-d H:i:s", strtotime('+15 days')) > date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])))
    {
//         $return = array('success'=>true, 'message'=>'You are not allowed to bank within 15 days of today!  Please call us if you feel this is an error.');
        $ldFee = get_option('gpx_late_deposit_fee');
        
        if(date("Y-m-d H:i:s", strtotime('+7 days')) > date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])))
        {
            $ldFee = get_option('gpx_late_deposit_fee_within');
        }
    }
    elseif(date("Y-m-d H:i:s", strtotime($_POST['Check_In_Date__c'])) > date("Y-m-d H:i:s", strtotime('+2 years')))
    {
        $return = array('success'=>true, 'message'=>'You are allowed to bank up to two years from today!  Please call us if you feel this is an error.');
    }
    elseif(isset($weekTypeError) && $weekTypeError)
    {
        $return = array('success'=>true, 'message'=>'Your ownership includes '.strtolower($_POST['Usage__c']).' year entitlement.  Please select an applicable date.  Reservations for your owner use week should be made prior to depositing in GPX.');
    }

    
    if(empty($returntocart) && isset($return))
    {
        echo wp_send_json($return);
        exit();
    }

    $sql = "SELECT b.resortID FROM  wp_resorts b
            WHERE b.gprID='".$_POST['GPX_Resort__c']."'";
    $row = $wpdb->get_row($sql);
    
    $sql = "SELECT * FROM wp_resorts_meta WHERE ResortID='".$row->resortID."'";

    $resortMetas = $wpdb->get_results($sql);
    
    $rmFees = [
        'LateDepositFeeOverride'=>[],
    ];
    foreach($resortMetas as $rm)
    {
        //reset the resort meta items
        $rmk = $rm->meta_key;
        if($rmArr = json_decode($rm->meta_value, true))
        {
            
            foreach($rmArr as $rmdate=>$rmvalues);
            {
                
                $thisVal = '';
                $rmdates = explode("_", $rmdate);
                
                if(count($rmdates) == 1 && $rmdates[0] == '0')
                {
                    //do nothing
                }
                else
                {
                    //check to see if the from date has started
                    if($rmdates[0] < strtotime($_POST['Check_In_Date__c']))
                    {
                        //this date has started we can keep working
                    }
                    else
                    {
                        //these meta items don't need to be used
                        continue;
                    }
                    //check to see if the to date has passed
                    if(isset($rmdates[1]) && ($rmdates[1] >= strtotime($_POST['Check_In_Date__c'])))
                    {
                        //these meta items don't need to be used
                        continue;
                    }
                    else
                    {
                        //this date is sooner than the end date we can keep working
                    }
                    foreach($rmvalues as $rmval)
                    {
                        //do we need to reset any of the fees?
                        if(array_key_exists($rmk, $rmFees))
                        {
                            
                            //set this fee
                            if($rmk == 'LateDepositFeeOverride')
                            {
                                
                                if($rmval == '0')
                                {
                                    $ldFee = '';
                                }
                                else
                                {
                                    $ldFee = $rmval;
                                }
                            }
                        }
                    }
                }
            }
        }
    } //end resort meta fees
    
    if(!isset($return['succes']))
    {

        //add to database
        $db = [
            'created_date' => date('Y-m-d H:i:s'),
            'interval_number' => $_POST['Contract_ID__c'],
            'resort_name' => stripslashes(str_replace("&", "&amp;",$_POST['Resort_Name__c'])),
            'deposit_year' => date('Y', strtotime($_POST['Check_In_Date__c'])),
            'check_in_date' => date('Y-m-d', strtotime($_POST['Check_In_Date__c'])),
            'owner_id' => $cid,
            'unit_type' => $_POST['Unit_Type__c'],
            'unitinterval' => $_POST['Resort_Unit_Week__c'],
        ];

//         if($agent && !empty($ldFee) && empty($addtocart))
        if(!empty($ldFee) && empty($addtocart))
        {
            //add this to the temp table
            $wpdb->insert('wp_temp_cart', array('item'=>'deposit', 'user_id'=>$cid, 'data'=>json_encode($_POST)));
            $tempID = $wpdb->insert_id;
            $agentReturn = [
                'paymentrequired'=>true,
                'amount'=>get_option('gpx_late_deposit_fee'),
                'type'=>'late_deposit',
                'html'=>'<h5>You will be required to pay a late deposit fee of $'.$ldFee.' to complete trasaction.</h5><br /><br /><span class="usw-button"><button class="dgt-btn add-fee-to-cart-direct" data-type="late_deposit_fee" data-fee="'.$ldFee.'" data-tid="'.$tempID.'" data-cart="" data-skip="No">Add To Cart</button>',
            ];    
            
            if($cid != get_current_user_id())
            {
                $agentReturn['html'] .= '<br /><br /><button class="dgt-btn add-fee-to-cart-direct af-agent-skip" data-fee="'.$ldFee.'" data-tid="'.$tempID.'" data-type="late_deposit_fee" data-cart="" data-skip="Yes">Waive Fee</button>';
            }
        }
        else
        {
            $wpdb->insert('wp_credit', $db);
            
            $insertid = $wpdb->insert_id;
            $_POST['GPX_Deposit_ID__c'] = $wpdb->insert_id;
            
            //send the details to SF
    //         require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.salesforce.php';
    //         $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
            
//             require_once GPXADMIN_API_DIR.'/functions/class.salesforce.php';
//             $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
            $sf = Salesforce::getInstance();
    //         require_once GPXADMIN_API_DIR.'/functions/class.restsaleforce.php';
    //         $gpxRest = new RestSalesforce();
            $sql = "SELECT RIOD_Key_Full FROM wp_mapuser2oid WHERE gpx_user_id='".$cid."' AND unitweek='".$_POST['Resort_Unit_Week__c']."'";
            $roid = $wpdb->get_var($sql);
            //get the ownership interval id
            $query = "SELECT ID, Name FROM Ownership_Interval__c WHERE ROID_Key_Full__c = '".$roid."'";
            $results = $sf->query($query);
            
//             $sfDetail = $results[0]->fields;
            $interval = $results[0]->Id;
            
            $email = $usermeta->Email;
            if(empty($email))
            {
                $email = $usermeta->email;
            }
            
            $sfDepositData = [
                'Account_Name__c'=>$_POST['GPX_Member__c'],
                'Check_In_Date__c'=>date('Y-m-d', strtotime($_POST['Check_In_Date__c'])),
                'Deposit_Year__c'=>date('Y', strtotime($_POST['Check_In_Date__c'])),
                'Account_Name__c'=>$_POST['Account_Name__c'],
                'GPX_Member__c'=>$cid,
                'Deposit_Date__c'=>date('Y-m-d'),
                'Resort__c'=>$_POST['GPX_Resort__c'],
                'Resort_Name__c'=>stripslashes(str_replace("&", "&amp;",$_POST['Resort_Name__c'])),
                'Resort_Unit_Week__c'=>$_POST['Resort_Unit_Week__c'],
                'GPX_Deposit_ID__c'=>$_POST['GPX_Deposit_ID__c'],
                'Coupon__c'=>$_POST['twofer'],
                'Unit_Type__c'=>$_POST['Unit_Type__c'],
                'Member_Email__c'=>$email,
                'Member_First_Name__c'=>stripslashes(str_replace("&", "&amp;",$usermeta->FirstName1)),
                    'Member_Last_Name__c'=>stripslashes(str_replace("&", "&amp;",$usermeta->LastName1)),
                'Ownership_Interval__c'=>$interval,
                'Deposited_by__c'=>$depositBy,
            ];
            
    //         $results =  $gpxRest->httpPost($sfDepositData, 'GPX_Deposit__c');
            $sfType = 'GPX_Deposit__c';
            $sfObject = 'GPX_Deposit_ID__c';
            
            $sfFields = [];
            $sfFields[0] = new SObject();
            $sfFields[0]->fields = $sfDepositData;
            $sfFields[0]->type = $sfType;
            
            $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);
            
            $record = $sfDepositAdd[0]->id;
            
            $wpdb->update('wp_credit', array('record_id'=>$record), array('id'=>$insertid));
            
            $msg = "Your week has been banked. Please allow 48-72 hours for our system to verify the transaction.";
        }
        if(isset($agentReturn))
        {
            $return = $agentReturn;
            $return['credit'] = 1;
            $return['success'] = true;
            $return['message'] = $msg;
        }
        else
        {
            $return = array('credit'=>1, 'success'=>true, 'message'=>$msg, 'creditid'=>$insertid);
        }
    }
    
    if(!empty($addtocart))
    {
        return $return;
    }
    else 
    {
        echo wp_send_json($return);
        exit();
    }
}
add_action("wp_ajax_gpx_post_will_bank","gpx_post_will_bank");
add_action("wp_ajax_nopriv_gpx_post_will_bank", "gpx_post_will_bank");

function gpx_add_fee_to_cart()
{
    global $wpdb;
    
    $return = [];
    
    if(isset($_POST['tempID']))
    {
        $tempID = $_POST['tempID'];
        $skip = $_POST['skip'];
        $fee = $_POST['fee'];
        $type = $_POST['type'];
        
        //get the details that need to be added
        $sql = "SElECT * FROM wp_temp_cart WHERE id='".$tempID."'";
        $tempRow = $wpdb->get_row($sql);
        
        $cid = $tempRow->user_id;
        $tempData = json_decode($tempRow->data, true);
     
//         $bank = gpx_post_will_bank($tempData, $cid);
        if($skip == 'Yes')
        {
            
            //add the deposit
            if($tempRow->item == 'deposit')
            {
                $return = gpx_post_will_bank($tempData, $cid);
            }
            
//             if($tempRow->type == 'extension')
//             {
//                 $return = gpx_extend_credit($tempData, $cid);
//             }
            
            if($tempRow->item == 'guest')
            {
                $return = gpx_reasign_guest_name($tempData, $cid);
            }
            
            
        }
        else
        {
           //add to the cart
            
            $_POST['user_type'] = 'Owner';
            $loggedinuser =  get_current_user_id();
            if($loggedinuser != $cid)
            {
                $user_type = 'Agent';
            }
            
            $user = get_userdata($cid);
            if(isset($user) && !empty($user))
            {
                $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $_POST['user'] ) );
            }
                
            $searchSessionID = '';
            if(isset($usermeta->searchSessionID))
            {
                $searchSessionID = $usermeta->searchSessionID;
            }
            
            $cartID = $tempRow->id."_".$cid;
            if(!empty($_COOKIE['gpx-cart']))
            {
                $cartID = $_COOKIE['gpx-cart'];
            }
            
            $sql = "SELECT id, data FROM wp_cart WHERE cartID='".$cartID."'";
            $row = $wpdb->get_row($sql);
            
            $cart = [
                'user'=>$cid,
                $type=>$fee,
            ];
            
            if(!empty($row))
            {
                $jsonData = json_decode($row->data, true);
                foreach($jsonData as $jdK=>$jdV)
                {
                    if(!isset($cart[$jdK]))
                    {
                        $cart[$jdK] = $jdV;
                    }
                }
            }
            $json = json_encode($_POST);
            
            $data['data'] = $json;
            
            if(!empty($row))
            {
                $update = $wpdb->update('wp_cart', $data, array('id'=>$row->id));
            }
            else
            {
                $data['user'] = $cid;
                $data['cartID'] = $cartID;
                $data['sessionID'] = $searchSessionID;
                $data['propertyID'] = 'nobook';
                $data['weekId'] = '1';
                $insert = $wpdb->insert('wp_cart', $data);
            }
            
            $return['redirect'] = true;
            $return['cartid'] = $cartID;
        }
    }
    
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_add_fee_to_cart","gpx_add_fee_to_cart");

function gpx_extend_credit($postdata = '', $addtocart = '')
{
    global $wpdb;
    
    if(empty($postdata))
    {
        //insert into the temporary cart
        
        
        $cid = get_current_user_id();
        
        if(isset($_COOKIE['switchuser']))
        {
            $cid = $_COOKIE['switchuser'];
        }
        
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
            'html'=>'<h5>You will be required to pay a credit extension fee of $'.$_POST['fee'].' to complete trasaction.</h5><br /><br /> <span class="usw-button"><button class="dgt-btn add-fee-to-cart-direct" data-type="extension" data-fee="'.$_POST['fee'].'" data-tid="'.$tempID.'" data-cart="" data-skip="No">Add To Cart</button>'
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
        
        $sql = "SELECT credit_expiration_date FROM wp_credit WHERE id='".$id."'";
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
//         require_once GPXADMIN_API_DIR.'/functions/class.salesforce.php';
//         sforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
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
        echo wp_send_json($return);
        exit();
    }
}
add_action("wp_ajax_gpx_extend_credit","gpx_extend_credit");
function gpx_load_deposit_form()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $html = $gpx->get_deposit_form();

    $return = array('html'=>$html);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_load_deposit_form","gpx_load_deposit_form");
add_action("wp_ajax_nopriv_gpx_load_deposit_form", "gpx_load_deposit_form");



add_action("wp_ajax_deleteUnittype","deleteUnittype");
add_action("wp_ajax_nopriv_deleteUnittype", "deleteUnittype");

function gpx_load_exchange_form()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $return = $gpx->get_exchange_form();

    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_load_exchange_form","gpx_load_exchange_form");
add_action("wp_ajax_nopriv_gpx_load_exchange_form", "gpx_load_exchange_form");

function gpx_bonus_week_details()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $return = $gpx->get_bonus_week_details();

    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_bonus_week_details","gpx_bonus_week_details");
add_action("wp_ajax_nopriv_gpx_bonus_week_details", "gpx_bonus_week_details");

function gpx_alert_submit()
{

    $option = $_POST['msg'];
    
    update_option('gpx_alert_msg_msg', $option);
    
    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_alert_submit","gpx_alert_submit");

function gpx_switch_alert()
{

    $option = $_POST['active'];
    
    update_option('gpx_alert_msg_active', $option);
    
    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_switch_alert","gpx_switch_alert");

function gpx_switch_booking_disabled()
{
    
    $option = $_POST['active'];
    
    update_option('gpx_booking_disabled_active', $option);
    
    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_switch_booking_disabled","gpx_switch_booking_disabled");

function gpx_booking_disabled_submit()
{
    
    $option = $_POST['msg'];
    
    update_option('gpx_booking_disabled_msg', $option);
    
    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_booking_disabeled_submit","gpx_booking_disabled_submit");


function gpx_ExtensionFee_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_extension_fee', $option);

    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_ExtensionFee_submit","gpx_ExtensionFee_submit");

function gpx_lateDepositFee_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_late_deposit_fee', $option);

    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_lateDepositFee_submit","gpx_lateDepositFee_submit");

function gpx_lateDepositFee_submit_within()
{

    $option = $_POST['amt'];

    update_option('gpx_late_deposit_fee_within', $option);

    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_lateDepositFee_submit_within","gpx_lateDepositFee_submit_within");

function gpx_fbfee_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_fb_fee', $option);

    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_fbfee_submit","gpx_fbfee_submit");

function gpx_min_rental_fee()
{

    $option = $_POST['min_rental'];

    update_option('gpx_min_rental_fee', $option);

    $return = array('success'=>true);
    
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_min_rental_fee","gpx_min_rental_fee");

function gpx_exchangefee_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_exchange_fee', $option);

    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_exchangefee_submit","gpx_exchangefee_submit");

function gpx_gfamount_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_gf_amount', $option);

    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_gfamount_submit","gpx_gfamount_submit");

function gpx_hold_limit_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_hold_error_message', $option);

    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_hold_limit_submit","gpx_hold_limit_submit");

function gpx_hold_limit_time_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_hold_limt_time', $option);

    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_hold_limit_time_submit","gpx_hold_limit_time_submit");

function gpx_hold_limit_timer_submit()
{

    $option = $_POST['amt'];

    update_option('gpx_hold_limt_timer', $option);

    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_hold_limit_timer_submit","gpx_hold_limit_timer_submit");

function gpx_dae_ws_submit()
{
    
    $field = $_POST['field'];
    $val = $_POST['val'];
    
    update_option($field, $val);
    
    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_dae_ws_submit","gpx_dae_ws_submit");

function gpx_switch_crEmail()
{
    
    $option = $_POST['active'];
    
    update_option('gpx_global_cr_email_send', $option);
    
    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_switch_crEmail","gpx_switch_crEmail");

function gpx_switch_gf()
{

    $option = $_POST['active'];

    update_option('gpx_global_guest_fees', $option);

    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_switch_gf","gpx_switch_gf");

function gpx_update_displayname()
{
    global $wpdb;
    $sql = "SELECT ID FROM wp_users WHERE user_email=''";
    $rows = $wpdb->get_results($sql);
    
    foreach($rows as $row)
    {
        $name = '';
        $email = '';
        $sql = "SELECT meta_key, meta_value FROM wp_usermeta WHERE user_id='".$row->ID."'
            AND  meta_key = 'email'";
        $metas = $wpdb->get_results($sql);
        foreach($metas as $meta)
        {
            $usermeta[$row->ID][$meta->meta_key] = $meta->meta_value;
        }
        //$name = $usermeta[$row->ID]['FirstName1']." ".$usermeta[$row->ID]['LastName1'];
        $email = $usermeta[$row->ID]['email'];
        echo '<pre>'.print_r($email, true).'</pre>';
//         $nameempty = str_replace(" ", "", $name);
//         if(isset($nameempty) && !empty($nameempty))
//             $wpdb->update('wp_users', array('display_name'=>$name), array('ID'=>$row->ID));  
        if(isset($email) && !empty($email))
            $wpdb->update('wp_users', array('user_email'=>$email), array('ID'=>$row->ID));
        echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
    }
    
    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_ggpx_update_displayname","gpx_update_displayname");
add_action("wp_ajax_nopriv_gpx_update_displayname", "gpx_update_displayname");

function gpx_switchusers()
{
    $userid = $_POST['cid'];
    update_user_meta( $userid, 'last_login', time() );
    update_user_meta($userid, 'searchSessionID', $userid."-".time());
    
    //It looks like when the user is setup WordPress/code is defaulting the display name to be the owners 'member id' instead of the phonetic name.
    //Need to correct so it doesn't happen in the future and fix all accounts on file.
    $first_name = get_user_meta( $userid, 'first_name', true );
    $last_name = get_user_meta( $userid, 'last_name', true );
    $full_name = trim( $first_name . ' ' . $last_name );
    if ( ! empty( $full_name ) && ( $user->data->display_name != $full_name ) ) {
        $userdata = array(
            'ID' => $userid,
            'display_name' => $full_name,
        );
        
        wp_update_user( $userdata );
    }
    
    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_switchusers","gpx_switchusers");
add_action("wp_ajax_nopriv_gpx_switchusers", "gpx_switchusers");

function gpx_switchusers_hook()
{
    do_action('gpx_switchusers_hook');
}

function gpx_remove_from_cart_fn()
{
    global $wpdb;
    
    require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    if(empty($_GET['pid']))
    {
        $cart = $_COOKIE['gpx-cart'];
        $wpdb->delete('wp_cart', array('cartID'=>$cart));
        $output['rr'] = 'redirect';
    }
    else 
    {
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $_GET['cid'] ) );
        
        $activeUser = get_userdata(get_current_user_id());
        
        $sql = "SELECT propertyID, data FROM wp_gpxPreHold WHERE user='".$_GET['cid']."' AND weekId='".$_GET['pid']."'";
        $row = $wpdb->get_row($sql);

        $holdDets = json_decode($row->data, true);
        $holdDets[strtotime('now')] = [
            'action'=>'released',
            'by'=>$activeUser->first_name." ".$activeUser->last_name,
        ];
        
    //     $sql = "SELECT WeekId FROM wp_properties WHERE WeekType='".$row->WeekType."' AND WeekEndpointID='".$row->WeekEndpointID."' AND WeekID='".$row->WeekID."' AND active='0'";
    //     $activateRow = $wpdb->get_row($sql);
        $sql = "SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId='".$_GET['pid']."' AND cancelled IS NULL";
        $trow = $wpdb->get_var($sql);
        
        if($trow > 0)
        {
            //nothing to do
        }
        else
        {
            $wpdb->update('wp_room', array('active'=>1), array('record_id'=>$_GET['pid']));
        }
        
        $wpdb->update('wp_gpxPreHold', array('released'=>1, 'data'=>json_encode($holdDets)), array('user'=>$_GET['cid'], 'weekId'=>$_GET['pid']));
    
        $remove = array(
            'user'=>$_GET['cid'],
            'propertyID'=>$_GET['pid'],
        );
        $output = array();
        if(!isset($_GET['nocart']))
        {
            $wpdb->delete('wp_cart', $remove);
            
            $sql = "SELECT * FROM wp_cart WHERE cartID='".$_COOKIE['gpx-cart']."'";
            $cart = $wpdb->get_results($sql);
            if(!empty($cart))
            {
                //update coupons for any cart
                foreach($cart as $value)
                {
                    $data = json_decode($value->data);
                    if(isset($data->coupon))
                        unset($data->coupon);
                    
                    $update = json_encode($data);
                    $wpdb->update('wp_cart', array('data'=>$update), array('id'=>$value->id));
                }
                
                $output['rr'] = 'refresh';
            }
                else
            {
                $output['rr'] = 'redirect';
            }
        }
    }
    echo wp_send_json($output);
    exit();
}
add_action("wp_ajax_gpx_remove_from_cart","gpx_remove_from_cart_fn");
add_action("wp_ajax_nopriv_gpx_remove_from_cart", "gpx_remove_from_cart_fn");
function gpx_admin_toolbar_link( $wp_admin_bar ) {
    $args = array(
        'id'    => 'gpx_admin',
        'title' => 'GPX Admin',
        'href'  => '/wp-admin/admin.php?page=gpx-admin-page',
        'meta'  => array( 'class' => 'my-toolbar-gpx-page' )
    );
    $wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'gpx_admin_toolbar_link', 999 );

function gpx_userswitch_toolbar_link( $wp_admin_bar ) {
    $sutext = '';
    if(isset($_COOKIE['switchuser']))
    {
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $_COOKIE['switchuser'] ) );
        $fname = $usermeta->SPI_First_Name__c;
        if(empty($fname))
        {
            $fname = $usermeta->first_name;
        }
        $lname = $usermeta->SPI_Last_Name__c;
        if(empty($lname))
        {
            $lname = $usermeta->last_name;
        }
        $sutext = 'Logged In As: '.$fname.' '.$lname.' ';
    }
    $args = array(
        'id'    => 'gpx_switch',
        'title' => $sutext.'Switch Owners',
        'href'  => '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_all',
        'meta'  => array( 'class' => 'my-toolbar-switch' )
    );
    $wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'gpx_userswitch_toolbar_link', 999 );

function gpx_cc_fix()
{
    global $wpdb;

    $sql = "SELECT * FROM `wp_gpxMemberSearch` WHERE `data` LIKE '%CardNo%'";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {
        $data = json_decode($row->data);
        foreach($data as $mk=>$d)
        {
            echo '<pre>'.print_r($d, true).'</pre>';
            foreach($d as $k=>$v)
            {
                $oldNum = '';
                $newNum = '';
                if($k == 'Payment')
                {
                    $oldNum = $v->CardNo;
                    $newNum = substr($v->CardNo, -4);
                    echo '<pre>'.print_r($newNum, true).'</pre>';
                    echo '<pre>'.print_r($oldNum, true).'</pre>';
                    if($oldNum != $newNum)
                    {
                        echo '<pre>'.print_r($mk, true).'</pre>';
                        $data->$mk->Payment->CardNo = $newNum;
                        $updata = json_encode($data);
                        $wpdb->update('wp_gpxMemberSearch', array('data'=>$updata), array('id'=>$row->id));
                        echo '<pre>'.print_r("ID=".$row->id, true).'</pre>';
                        echo '<pre>'.print_r($updata, true).'</pre>';
                    }
                }
            }
            
        }

    }

}
add_action("wp_ajax_gpx_cc_fix","gpx_cc_fix");
add_action("wp_ajax_nopriv_gpx_cc_fix", "gpx_cc_fix");

function gpx_csv_download()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $table = 'wp_cart';
    if(isset($_GET['table']))
        $table = $_GET['table'];
    $column = 'data';
    if(isset($_GET['column']))
        $column = $_GET['column'];
    $days = '60';
    if(isset($_GET['days']))
        $days = $_GET['days'];
    $dateFrom = date('Y-m-d', strtotime('-2 days'));
    if(isset($_GET['datefrom']))
    {
        $dateFrom = $_GET['datefrom'];
    }
    
    $dateTo = date('Y-m-d');
    if(!empty($_GET['dateto']))
    {
        $dateTo = $_GET['dateto'];
    }
    $return = $gpx->get_csv_download($table, $column, $days, '', $dateFrom, $dateTo);

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
add_action("wp_ajax_gpx_csv_download","gpx_csv_download");
add_action("wp_ajax_nopriv_gpx_csv_download", "gpx_csv_download");

function gpx_json_reports()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $table = 'wp_cart';
    if(isset($_GET['table']))
        $table = $_GET['table'];
    $days = '10';
    if(isset($_GET['days']))
        $days = $_GET['days'];
    $return = $gpx->get_gpx_json_reports($table, $days);

    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_json_reports","gpx_json_reports");
add_action("wp_ajax_nopriv_gpx_json_reports", "gpx_json_reports");

function gpx_retarget_report()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $table = 'wp_cart';
    if(isset($_GET['table']))
        $table = $_GET['table'];
    $column = 'data';
    if(isset($_GET['column']))
        $column = $_GET['column'];
    $return = $gpx->reportretarget();
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
add_action("wp_ajax_gpx_retarget_report","gpx_retarget_report");
add_action("wp_ajax_nopriv_gpx_retarget_report", "gpx_retarget_report");

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
    echo '<pre>'.print_r(count($users), true).'</pre>';
    if (!empty($users)) {

        foreach ($users as $user)
        {
            add_user_meta( $user->id, 'ICEStore', 'Yes', true );
        }
        echo '<pre>'.print_r("updated", true).'</pre>';
    }
}

add_action('wp_ajax_add_ice_permission', 'add_ice_permission');
add_action('wp_ajax_nopriv_add_ice_permission', 'add_ice_permission');

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
    
    //                     if(get_current_user_id() == 5)
        //                     {
        //                         echo '<pre>'.print_r($usermeta->ICENameId, true).'</pre>';
        //                         echo '<pre>'.print_r($usermeta, true).'</pre>';
        //                     }
    if(isset($usermeta->ICENameId) && !empty($usermeta->ICENameId))
    {
        if(isset($_REQUEST['icedebug']))
        {
            echo '<pre>'.print_r('nameid', true).'</pre>';
            echo '<pre>'.print_r($redirect, true).'</pre>';
        }
        $data = $ice->newIceMember();
    }
    else
    {
        if(isset($_REQUEST['icedebug']))
        {
            echo '<pre>'.print_r('not nameid', true).'</pre>';
            echo '<pre>'.print_r($redirect, true).'</pre>';
        }
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

function add_ai()
{
    global $wpdb;
    echo '<pre>'.print_r("start", true).'</pre>';
    $sql = "SELECT * FROM wp_resorts";
    $props = $wpdb->get_results($sql);
    foreach($props as $prop)
    {
        $resortFacilities = json_decode($prop->ResortFacilities);
        if(in_array('All Inclusive', $resortFacilities) || strpos($prop->HTMLAlertNotes, 'IMPORTANT: All-Inclusive Information') || strpos($prop->AlertNote, 'IMPORTANT: This is an All Inclusive (AI) property.'))
        {
            echo '<pre>'.print_r($prop->id, true).'</pre>';
            $wpdb->update('wp_resorts', array('ai'=>1), array('id'=>$prop->id));
            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
        }
    }
}
add_action('wp_ajax_add_ai', 'add_ai');
add_action('wp_ajax_nopriv_add_ai', 'add_ai');

function add_gpx_resorttax()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->return_add_gpx_resorttax($_POST);
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_add_gpx_resorttax', 'add_gpx_resorttax');
add_action('wp_ajax_nopriv_add_gpx_resorttax', 'add_gpx_resorttax');

function edit_gpx_resorttax()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->return_edit_gpx_resorttax($_POST);
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_edit_gpx_resorttax', 'edit_gpx_resorttax');
add_action('wp_ajax_nopriv_edit_gpx_resorttax', 'edit_gpx_resorttax');

function update_gpx_resorttax_id()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->return_update_gpx_resorttax_id($_POST);
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_update_gpx_resorttax_id', 'update_gpx_resorttax_id');
add_action('wp_ajax_nopriv_update_gpx_resorttax_id', 'update_gpx_resorttax_id');

function edit_tax_method()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->return_edit_tax_method($_POST);
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_edit_tax_method', 'edit_tax_method');
add_action('wp_ajax_nopriv_edit_tax_method', 'edit_tax_method');

function get_gpx_resorttaxes()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->return_get_gpx_resorttaxes();
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_get_gpx_resorttaxes', 'get_gpx_resorttaxes');
add_action('wp_ajax_nopriv_get_gpx_resorttaxes', 'get_gpx_resorttaxes');

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
    echo wp_send_json($return);
    exit();
}
add_action('wp_ajax_update_gpx_tax_transaction_type', 'update_gpx_tax_transaction_type');
add_action('wp_ajax_nopriv_update_gpx_tax_transaction_type', 'update_gpx_tax_transaction_type');

function cron_check_custom_requests_ajax()
{
    global $wpdb;
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $gpx->return_cron_check_custom_requests();
    
    $data = array('success'=>true);
}
add_action('wp_ajax_cron_check_custom_requests_ajax', 'cron_check_custom_requests_ajax');
add_action('wp_ajax_nopriv_cron_check_custom_requests_ajax', 'cron_check_custom_requests_ajax');

function get_gpx_promoautocoupons()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_promoautocoupons();

    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_get_gpx_promoautocoupons', 'get_gpx_promoautocoupons');
add_action('wp_ajax_nopriv_get_gpx_promoautocoupons', 'get_gpx_promoautocoupons');

function get_gpx_tripadvisor_locations()
{
    global $wpdb;
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    require_once GPXADMIN_API_DIR.'/functions/class.tripadvisor.php';
    $ta = new TARetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $nn = 0;
    $zz = 0;
    
    $sql = "SELECT id, ResortName, LatitudeLongitude FROM wp_resorts WHERE taID='' and LatitudeLongitude != '' LIMIT 700";
    $rows = $wpdb->get_results($sql);
    foreach($rows as $row)
    {
        $latlng = $row->LatitudeLongitude;
        $name = $row->ResortName;
        $loc = $ta->location_mapper($latlng, $name);
        $data = $loc->data;
        if(!empty($data))
        {
            $wpdb->update('wp_resorts', array('taID'=>$data[0]->location_id), array('id'=>$row->id));
            $nn++;
        }
        else
        {
            $wpdb->update('wp_resorts', array('taID'=>'1'), array('id'=>$row->id));
            $zz++;
        }
    }
    
    $data['taID'] = $nn;
    $data['noTAID'] = $zz;
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_get_gpx_tripadvisor_locations', 'get_gpx_tripadvisor_locations');
add_action('wp_ajax_nopriv_get_gpx_tripadvisor_locations', 'get_gpx_tripadvisor_locations');

function get_gpx_tripadvisor_location($id)
{
    require_once GPXADMIN_API_DIR.'/functions/class.tripadvisor.php';
    $ta = new TARetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
    $coords = $_GET['coords'];
    $id = $_GET['rid'];
    
    $loc = $ta->location_mapper($coords);
    $rows = $loc->data;
    if(!empty($rows))
    {
        $data['html'] = '';
        foreach($rows as $row )
        {
            $data['html'] .= '<div class="well">';
            $data['html'] .= '<div class="row form-group">';
            $data['html'] .= '<div class="col-xs-9">';
            $data['html'] .= $row->name."<br>".$row->address_obj->address_string;
            $data['html'] .= '</div>';
            $data['html'] .= '<div class="col-xs-3">';
            $data['html'] .= '<button class="btn btn-success newTA" data-taid="'.$row->location_id.'" data-rid="'.$id.'" data-coords="'.$coords.'">Add</button>';
            $data['html'] .= '</div>';
            $data['html'] .= '</div>';
            $data['html'] .= '</div>';
        }
    }
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_get_gpx_tripadvisor_location', 'get_gpx_tripadvisor_location');
add_action('wp_ajax_nopriv_get_gpx_tripadvisor_location', 'get_gpx_tripadvisor_location');

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
        
        $sql = "SELECT SUM(credit_amount) AS total_credit_amount, SUM(credit_used) AS total_credit_used FROM wp_credit WHERE owner_id IN (SELECT gpx_user_id FROM wp_mapuser2oid WHERE gpx_user_id='".$cid."') AND (credit_expiration_date IS NULL OR credit_expiration_date >'".date('Y-m-d')."')";
        $credit = $wpdb->get_row($sql);
        
        $credits = $credit->total_credit_amount - $credit->total_credit_used;
        
        $sql = "SELECT *  FROM `wp_mapuser2oid` WHERE `gpx_user_id` = '".$cid."'";
        $wp_mapuser2oid = $gpx->GetMappedOwnerByCID($cid);
        
        $memberNumber = '';
        
        if(!empty($wp_mapuser2oid))
        {
            $memberNumber = $wp_mapuser2oid->gpr_oid;
        }
        
        $sql = "SELECT a.*, b.ResortName, c.deposit_year FROM wp_owner_interval a
                INNER JOIN wp_resorts b ON b.gprID LIKE CONCAT(BINARY a.resortID, '%')
                LEFT JOIN (SELECT MAX(deposit_year) as deposit_year, interval_number FROM wp_credit WHERE status != 'Pending' GROUP BY interval_number) c ON c.interval_number=a.contractID
                WHERE a.Contract_Status__c != 'Cancelled'
                    AND a.ownerID IN
                    (SELECT gpr_oid
                        FROM wp_mapuser2oid
                        WHERE gpx_user_id IN
                            (SELECT gpx_user_id
                            FROM wp_mapuser2oid
                            WHERE gpr_oid='".$memberNumber."'))";
        $ownerships = $wpdb->get_results($sql, ARRAY_A);
        
        //Rule is # of Ownerships  (i.e. – have 2 weeks, can have account go to negative 2, one per week)
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

function post_gpx_tripadvisor_locationid($id)
{
    global $wpdb;
    
    $id = $_POST['rid'];
    $taID = $_POST['taid'];
    
    $wpdb->update('wp_resorts', array('taID'=>$taID), array('id'=>$id));
    
    $data = array('success'=>true);
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_post_gpx_tripadvisor_locationid', 'post_gpx_tripadvisor_locationid');
add_action('wp_ajax_nopriv_post_gpx_tripadvisor_locationid', 'post_gpx_tripadvisor_locationid');

function gpx_promo_dup_check()
{
    global $wpdb;
    
    $data = array('success'=>true);
    
    if(isset($_POST['slug']))
    {
        $sql = "SELECT * FROM wp_specials WHERE slug LIKE '".$_POST['slug']."'";
        $row =  $wpdb->get_row($sql);
        
        if(!empty($row))
        {
            $data = array('error'=>'You already used this slug.');
        }
    }
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_promo_dup_check', 'gpx_promo_dup_check');

function gpx_trans_agent_fix()
{
    global $wpdb;
    

    $sql = "SELECT * FROM wp_gpxTransactions WHERE cartID <> '' and id > 8500";
    $toCheck = $wpdb->get_results($sql);
    $i = 0;
    foreach($toCheck as $dRow)
    {
        $djson = json_decode($dRow->data, true);
        $sql = "SELECT * FROM wp_gpxMemberSearch WHERE sessionID='".$dRow->sessionID."' AND data LIKE '%view-%'";
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
        
       
//         $wpdb->update('wp_gpxTransactions', array('data'=>$data), array('id'=>$tcK));
    }
    $data['processed'] = $i;
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_trans_agent_fix', 'gpx_trans_agent_fix');

function gpx_sf_test()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->gpx_get_sf_object_test();
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_sf_test', 'gpx_sf_test');
add_action('wp_ajax_nopriv_gpx_sf_test', 'gpx_sf_test');

//remove visual editor from custom request form form
add_filter( 'user_can_richedit', 'cr_form_remove_visual');


require_once GPXADMIN_PLUGIN_DIR.'/vendors/dompdf/lib/html5lib/Parser.php';
require_once GPXADMIN_PLUGIN_DIR.'/vendors/dompdf/lib/php-font-lib/src/FontLib/Autoloader.php';
require_once GPXADMIN_PLUGIN_DIR.'/vendors/dompdf/lib/php-svg-lib/src/autoload.php';
require_once GPXADMIN_PLUGIN_DIR.'/vendors/dompdf/src/Autoloader.php';
Dompdf\Autoloader::register();

// reference the Dompdf namespace
use Dompdf\Dompdf;

function gpx_cr_pdf_reports(){
    if (isset($_REQUEST['cr_pdf_reports'])){
        
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
        
        // Output the generated PDF to Browser
        $dompdf->stream();
    }
}

add_action('init', 'gpx_cr_pdf_reports');

function cr_form_remove_visual($c) {
    if (isset($_REQUEST['gpx-pg']) && $_REQUEST['gpx-pg'] == 'customrequests_form')
        return false;
        return $c;
}
//It looks like when the user is setup WordPress/code is defaulting the display name to be the owners 'member id' instead of the phonetic name.
//Need to correct so it doesn't happen in the future and fix all accounts on file.
function gpx_format_user_display_name_on_login( $username ) {
    $user = get_user_by( 'login', $username );
    
    $first_name = get_user_meta( $user->ID, 'first_name', true );
    $last_name = get_user_meta( $user->ID, 'last_name', true );
    
    $full_name = trim( $first_name . ' ' . $last_name );
    
    if ( ! empty( $full_name ) && ( $user->data->display_name != $full_name ) ) {
        $userdata = array(
            'ID' => $user->ID,
            'display_name' => $full_name,
        );
        
        wp_update_user( $userdata );
    }
}
add_action( 'wp_login', 'gpx_format_user_display_name_on_login' );
add_action( 'user_register', 'gpx_format_user_display_name_on_login' );
function get_username_modal()
{
    $data['html'] = '<ul class="gform_fields">
						<li class="message-box"><span>For security reasons, please update your username and password.</span></li>
						<li class="gfield">
							<label for="modal_username" class="gfield_label"></label>
							<div class="ginput_container">
								<input type="text" id="modal_username" name="modal_username" placeholder="Username" class="validate modal_reset_username" autocomplete="off" required="required"/>
							</div>
						</li>
						<li class="gfield">
							<label for="modal_password" class="gfield_label"></label>
							<div class="ginput_container">
								<input id="login_password" id="modal_password" name="user_pass" type="password" placeholder="Password" class="validate modal_reset_username" autocomplete="off" required="required"/>
							</div>
						</li>
						<li class="gfield">
							<label for="modal_repeat_password" class="gfield_label"></label>
							<div class="ginput_container">
								<input id="login_password" id="modal_repeat_password" name="user_pass_repeat" type="password" placeholder="Repeat Password" class="validate modal_reset_username" autocomplete="off" required="required"/>
							</div>
						</li>
						<li class="gfield">
							<a href="#" class="call-modal-pwreset">Forgot password?</a>
						</li>
					</ul>';
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_get_username_modal', 'get_username_modal');
add_action('wp_ajax_get_username_modal', 'get_username_modal');
add_action('wp_ajax_nopriv_get_username_modal', 'get_username_modal');

function gpx_import_test()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->transactionimport();
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_import_test', 'gpx_import_test');
add_action('wp_ajax_nopriv_gpx_import_test', 'gpx_import_test');

function gpx_import_owner_credit()
{
    global $wpdb;
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    //wp_gpx_import_account_credit 0 = not imported; 1 = imported; 2 = exception;
    
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
        ];
        
        $gpx->promodeccouponsadd($occ);
        
        $wpdb->update('wp_gpx_import_account_credit', array('is_added'=>1), array('id'=>$row->id));
    }
    
}
add_action('wp_ajax_gpx_import_owner_credit', 'gpx_import_owner_credit');
add_action('wp_ajax_nopriv_gpx_import_owner_credit', 'gpx_import_owner_credit');

function get_gpx_promoautocouponexceptions()
{
    global $wpdb;
    
    $data = [];
    
    $sql = "SELECT * FROM wp_gpx_import_account_credit WHERE is_added=2";
    $results = $wpdb->get_results($sql);
    
    $i = 0;
    foreach($results as $row)
    {
        $data[$i]['account'] = $row->account;
        $data[$i]['business_date'] = $row->business_date;
        $data[$i]['amount'] = $row->amount;
        $i++;
    }
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_get_gpx_promoautocouponexceptions', 'get_gpx_promoautocouponexceptions');

function gpx_user_id_by_daenumber($daeNumber)
{
    global $wpdb;
    
    $sql = "SELECT user_id FROM wp_usermeta WHERE meta_key='DAEMemberNo' AND meta_value='".$daeNumber."'";
    $user_id = $wpdb->get_var($sql);
    
    return $user_id;
}

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

function gpx_report_writer_table()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $data = $gpx->reportwriter($_GET['id']);
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_report_writer_table', 'gpx_report_writer_table');

function gpx_report_write_send()
{
    global $wpdb;
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $sql = "SELECT id, emailrepeat FROM wp_gpx_report_writer WHERE emailrecipients != ''";
    $results = $wpdb->get_results($sql);
    
    $weekday = date('N');
    $day = date('l');
    $month = date('j');
    
    
    foreach($results as $result)
    {
        if(strtolower($day) == strtolower($result->emailrepeat))
        {
            $run = true;
        }
        else
        {
            switch($result->emailrepeat)
            {
                case 'Daily':
                    $run = true;
                break;
                
                case 'Weekdays':
                    if($weekday < 6)
                    {
                        $run = true;
                    }
                break;
                
                case 'Monthly':
                    if($month == '1')
                    {
                        $run = true;
                    }
                break;
            }
        }
        
        if(isset($run))
        {
            $data[] = $gpx->reportwriter($result->id, true);
        }
    }
    
    wp_send_json($data);
    wp_die();
}
add_action('hook_cron_gpx_report_write_send', 'gpx_report_write_send');
add_action('wp_ajax_cron_grws', 'gpx_report_write_send');

/*
 * Report Writer Submit
 * Store details that were added to the form then open the table page
 */
function gpx_report_write()
{
    global $wpdb;
          
    if(isset($_POST['reportType']))
    {
        if($_POST['reportType'] == 'Group')
        {
            $role = implode(",", $_POST['role']);
        }
        if(!empty($_POST['condition']))
        {
            $cj = json_decode(stripslashes($_POST['condition']), true);
            $co = json_decode(stripslashes($_POST['operator']), true);
            $cod = json_decode(stripslashes($_POST['operand']), true);
            $cv = json_decode(stripslashes($_POST['conditionValue']), true);
            for($i=1;$i<=$_POST['gps'];$i++)
            {
                
                $conditions[] = [
                    'condition'=>$cj[$i],
                    'operator'=>$co[$i],
                    'operand'=>$cod[$i],
                    'conditionValue'=>$cv[$i],
                ];
            }
        }
//         echo '<pre>'.print_r($conditions, true).'</pre>';
        $insert = [
            'name'=>$_POST['name'],
            'data'=>json_encode($_POST['data']),
            'reportType'=>$_POST['reportType'],
            'role'=>$role,
            'emailrepeat'=>$_POST['emailrepeat'],
            'emailrecipients'=>$_POST['emailrecipients'],
            'conditions'=>json_encode($conditions),
            'formData'=>base64_encode($_POST['form']),
            'userID'=>get_current_user_id(),
            
        ];
        if(isset($_REQUEST['editid']))
        {
            $updateYes = false;
            $sql = "SELECT name, reportType FROM wp_gpx_report_writer WHERE id='".$_REQUEST['editid']."'";
            $thisReport = $wpdb->get_row($sql);
            
            if(!empty($thisReport) && $thisReport->name == $_REQUEST['name'] && $thisReport->reportType == $_REQUEST['reportType'])
            {
                $updateYes = true;
            }

            
            if($updateYes)
            {
                $wpdb->update('wp_gpx_report_writer', $insert, array('id'=>$_REQUEST['editid']));
                $data = [
                    'success' => true,
                    'refresh' => '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer&id='.$_REQUEST['editid'],
                ];
            }
        }
        
        if(!isset($data))
        {
            $wpdb->insert('wp_gpx_report_writer', $insert);
            $data = [
                'success' => true,
            ];
            if(empty($_REQUEST['name']))
            {
                $data['refresh'] = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer&id='.$wpdb->insert_id;
            }
            else
            {
                $data['link']='<li><a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer&id='.$wpdb->insert_id.'" target="_blank">'.$_POST['name'].'</a>&nbsp;&nbsp;<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer&editid='.$wpdb->insert_id.'"><i class="fa fa-pencil"></i></a></li>';
                $data['refresh'] = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer&id='.$wpdb->insert_id;
            }
        }

    }
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_report_write', 'gpx_report_write');

function gpx_shiftfour_sale_test()
{
    require_once GPXADMIN_API_DIR.'/functions/class.shiftfour.php';
    $shift4 = new Shiftfour();
    
    $data = $shift4->shift_auth();
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_shiftfour_sale_test', 'gpx_shiftfour_sale_test');
add_action('wp_ajax_nopriv_gpx_shiftfour_sale_test', 'gpx_shiftfour_sale_test');

function gpx_i4goauth()
{
    require_once GPXADMIN_API_DIR.'/functions/class.shiftfour.php';
    $shift4 = new Shiftfour();
    
    $i4go = $shift4->i_four_go_auth();
    
    $data = [
        'data' => json_decode($i4go['i4go']),
        'paymentID' => $i4go['paymentID'],
    ];
        
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_i4goauth', 'gpx_i4goauth');
add_action('wp_ajax_nopriv_gpx_i4goauth', 'gpx_i4goauth');

function gpx_14gostatus()
{
    global $wpdb;
    
    require_once GPXADMIN_API_DIR.'/functions/class.shiftfour.php';
    $shift4 = new Shiftfour();
    
    //we just need to update the database with the response
    $update = $_REQUEST['data'];
    
    $update['i4go_object'] = json_encode($update['otn']);
    unset($update['otn']);
    
    $wpdb->update('wp_payments', $update, array('id'=>$_REQUEST['paymentID']));
    $data['i4go_response'] = $update['i4go_response'];
    $data['i4go_responsecode'] = $update['i4go_responsecode'];
    if(isset($update['i4go_responsetext'])) 
    {
        //just the text
        $responsetext = explode(" (", $update['i4go_responsetext']);
        $data['i4go_responsetext'] = $responsetext[0];
    }
    $data['paymentID'] = $_REQUEST['paymentID'];
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_gpx_14gostatus', 'gpx_14gostatus');
add_action('wp_ajax_nopriv_gpx_14gostatus', 'gpx_14gostatus');
