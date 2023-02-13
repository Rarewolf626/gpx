<?php

use GPX\Model\Resort;
use GPX\Repository\ResortRepository;


/**
 *
 *
 *
 *
 */
function deleteUnittype()
{
    global $wpdb;
    $id =  $_POST['unit_id'];

    $wpdb->delete('wp_unit_type', array( 'record_id' => $id ) );

    wp_send_json("delete done");
}
add_action('wp_ajax_deleteUnittype', 'deleteUnittype');
add_action('wp_ajax_nopriv_deleteUnittype', 'deleteUnittype');

/**
 *
 *
 *
 *
 */
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
}
add_action('wp_ajax_unitType_Form', 'unitType_Form');
add_action('wp_ajax_nopriv_unitType_Form', 'unitType_Form');





/**
 *
 *
 *
 *
 */
function resort_confirmation_number(){
    global $wpdb;

    $resort = $_POST['resort'];
    $resortConfirmation = $_POST['resortConfirmation'];
    //  resort_confirmation_number
    $sql = $wpdb->prepare("SELECT *  FROM `wp_room` WHERE `resort_confirmation_number` = %s", $resortConfirmation);

    if(!empty($resort))
    {
        $sql .=  $wpdb->prepare(" AND resort=%s", $_POST['resort']);
    }
    $rows = $wpdb->get_results($sql);
    $response = array();

    wp_send_json($rows);
}
add_action('wp_ajax_resort_confirmation_number', 'resort_confirmation_number');
add_action('wp_ajax_nopriv_resort_confirmation_number', 'resort_confirmation_number');






/**
 *
 *
 *
 *
 */
function get_unit_type(){
    global $wpdb;

    $resort = $_POST['resort'];
    //  resort_confirmation_number
    $sql = $wpdb->prepare("SELECT * FROM `wp_unit_type` WHERE `resort_id` = %s", $resort);

    $rows = $wpdb->get_results($sql);
    $res = array();

    foreach ($rows as $row) {

        $res[$row->record_id] = $row->name;
    }

    wp_send_json($res);
}
add_action('wp_ajax_get_unit_type', 'get_unit_type');
add_action('wp_ajax_nopriv_get_unit_type', 'get_unit_type');

/**
 *
 *
 *
 *
 */
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
            'source_partner_id' =>  (isset($_POST['source_partner_id'])) ? intval($_POST['source_partner_id']) : 0,
            'resort_confirmation_number' => $_POST['resort_confirmation_number'],
            'active' => $active,
            'availability' => $_POST['availability'],
            'available_to_partner_id' => (isset($_POST['available_to_partner_id'])) ? intval($_POST['available_to_partner_id']) : 0,
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
        $sql = $wpdb->prepare("SELECT * FROM wp_room WHERE record_id=%d",$_POST['room_id']);
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
        $sql = $wpdb->prepare("UPDATE wp_partner
        SET no_of_rooms_given = no_of_rooms_given + %d, trade_balance = trade_balance + %d
        WHERE user_id = %s", [$count,$count, $rooms['source_partner_id']]);

        $wpdb->query($sql);
    }

    wp_send_json($msg);
}
add_action('wp_ajax_room_Form', 'room_Form');
add_action('wp_ajax_room_Form_edit', 'room_Form');


/**
 *
 *
 *
 *
 */
function get_addResorts()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    $data = $gpx->DAEGetResortProfile();

    wp_send_json($data);
}



/**
 *
 *
 *
 *
 */
function get_indResorts()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    $data = $gpx->DAEGetResortInd();

    wp_send_json($data);
}
add_action('wp_ajax_get_indResorts', 'get_indResorts');
add_action('wp_ajax_nopriv_get_indResorts', 'get_indResorts');


/**
 *
 *
 *
 *
 */
function get_missingResort()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
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
}

add_action('wp_ajax_get_missingResort', 'get_missingResort');
add_action('wp_ajax_nopriv_get_missingResort', 'get_missingResort');


/**
 *
 *
 *
 *
 */
function get_addResortDetails()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    $data = $gpx->addResortDetails();

    wp_send_json($data);
}

add_action('wp_ajax_get_addResortDetails', 'get_addResortDetails');
add_action('wp_ajax_nopriv_get_addResortDetails', 'get_addResortDetails');


/**
 *
 *
 *
 *
 */
function get_manualResortUpdate()
{
    global $wpdb;
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $sql = $wpdb->prepare("SELECT id, ResortID, EndpointID, gpxRegionID FROM wp_resorts WHERE ResortID=%s", $_POST['resort']);
    $row = $wpdb->get_row($sql);
    $inputMembers = array(
        'ResortID'=>$row->ResortID,
        'EndpointID'=>$row->EndpointID,
    );

    $data = $gpx->DAEGetResortProfile($row->id, $row->gpxRegionID, $inputMembers, '1');

    wp_send_json($data);
}

add_action('wp_ajax_get_manualResortUpdate', 'get_manualResortUpdate');
add_action('wp_ajax_nopriv_get_manualResortUpdate', 'get_manualResortUpdate');


/**
 *
 *
 *
 *
 */
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
}

add_action('wp_ajax_get_manualResortUpdateAll', 'get_manualResortUpdateAll');
add_action('wp_ajax_nopriv_get_manualResortUpdateAll', 'get_manualResortUpdateAll');



/**
 *
 *
 *
 *
 */
function gpx_resorts_list()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    return $gpx->return_gpx_properties();
}


/**
 *
 *
 *
 *
 */
function get_gpx_resorts()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_resorts();

    wp_send_json($data);
}

add_action('wp_ajax_get_gpx_resorts', 'get_gpx_resorts');
add_action('wp_ajax_nopriv_get_gpx_resorts', 'get_gpx_resorts');



/**
 *
 *
 *
 *
 */
function gpx_store_resort()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_store_resort();

    wp_send_json($data);
}

add_action('wp_ajax_gpx_store_resort', 'gpx_store_resort');
add_action('wp_ajax_nopriv_gpx_store_resort', 'gpx_store_resort');



/**
 *
 *
 *
 *
 */
function featured_gpx_resort()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_featured_gpx_resort();

    wp_send_json($data);
}

add_action('wp_ajax_featured_gpx_resort', 'featured_gpx_resort');
add_action('wp_ajax_nopriv_featured_gpx_resort', 'featured_gpx_resort');

/**
 *
 *
 *
 *
 */
function ai_gpx_resort()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_ai_gpx_resort();

    wp_send_json($data);
}

add_action('wp_ajax_ai_gpx_resort', 'ai_gpx_resort');
add_action('wp_ajax_nopriv_ai_gpx_resort', 'ai_gpx_resort');



/**
 *
 *
 *
 *
 */
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
}

add_action('wp_ajax_gpx_resort_image_update_attr', 'gpx_resort_image_update_attr');


/**
 *
 *
 *
 *
 */
function guest_fees_gpx_resort()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_guest_fees_gpx_resort();

    wp_send_json($data);
}

add_action('wp_ajax_guest_fees_gpx_resort', 'guest_fees_gpx_resort');
add_action('wp_ajax_nopriv_guest_fees_gpx_resort', 'guest_fees_gpx_resort');



/**
 *
 *
 *
 *
 */
function gpx_resort_attribute_new()
{
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
    if (in_array($post['type'], ['PostCode','Address1','Address2','Town','Region','Country'])) {
        DB::table('wp_resorts')
              ->where('ResortID', '=', $post['resortID'])
              ->update(['geocode_status' => null]);
    }

    wp_send_json($data);
}

add_action('wp_ajax_gpx_resort_attribute_new', 'gpx_resort_attribute_new');



/**
 *
 *
 *
 *
 */
function gpx_resort_attribute_remove()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $post['resortID'] = $_POST['resort'];
    $post['item'] = $_POST['item'];
    $post['type'] = $_POST['type'];

    $data = $gpx->return_resort_attribute_remove($post);

    wp_send_json($data);
}

add_action('wp_ajax_gpx_resort_attribute_remove', 'gpx_resort_attribute_remove');


/**
 *
 *
 *
 *
 */
function gpx_resort_attribute_reorder()
{
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
}

add_action('wp_ajax_gpx_resort_attribute_reorder', 'gpx_resort_attribute_reorder');


/**
 *
 *
 *
 *
 */
function gpx_resort_image_reorder()
{
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
}
add_action('wp_ajax_gpx_resort_image_reorder', 'gpx_resort_image_reorder');




/**
 *
 *
 *
 *
 */
function gpx_resort_repeatable_remove()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $post['from'] = $_POST['from'];
    $post['to'] = $_POST['to'];
    $post['type'] = $_POST['type'];
    $post['resortID'] = $_POST['resortID'];
    $post['oldorder'] = $_POST['oldorder'];

    $data = $gpx->return_gpx_resort_repeatable_remove($post);

    wp_send_json($data);
}

add_action('wp_ajax_gpx_resort_repeatable_remove', 'gpx_resort_repeatable_remove');




/**
 *
 *
 *
 *
 */
function gpx_image_remove()
{
    global $wpdb;

    $sql = $wpdb->prepare("SELECT id, meta_value FROM wp_resorts_meta WHERE meta_key='images' AND ResortID=%s", $_POST['resortID']);
    $row = $wpdb->get_row($sql);
    $images = json_decode($row->meta_value);

    unset($images[$_POST['image']]);

    $updateImages = array('meta_value'=>json_encode($images));

    $wpdb->update('wp_resorts_meta', $updateImages, array('id'=>$row->id));

    $data['success'] = true;

    wp_send_json($data);
}

add_action('wp_ajax_gpx_image_remove', 'gpx_image_remove');




/**
 *
 *
 *
 *
 */
function active_gpx_resort()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_active_gpx_resort();

    wp_send_json($data);
}

add_action('wp_ajax_active_gpx_resort', 'active_gpx_resort');
add_action('wp_ajax_nopriv_active_gpx_resort', 'active_gpx_resort');



/**
 *
 *
 *
 *
 */
function get_gpx_list_resorts()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_get_gpx_list_resorts($_POST['value'], $_POST['type']);

    wp_send_json($data);
}

add_action('wp_ajax_get_gpx_list_resorts', 'get_gpx_list_resorts');
add_action('wp_ajax_nopriv_get_gpx_list_resorts', 'get_gpx_list_resorts');

/**
 *
 *
 *
 *
 */
function gpx_autocomplete_resort_fn() {
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';
    $term = stripslashes($term);
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
    wp_send_json($resorts);
}
add_action("wp_ajax_gpx_autocomplete_resort","gpx_autocomplete_resort_fn");
add_action("wp_ajax_nopriv_gpx_autocomplete_resort", "gpx_autocomplete_resort_fn");


/**
 *
 *
 *
 *
 */
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
            $sql = $wpdb->prepare("SELECT name, reportType, userID FROM wp_gpx_report_writer WHERE id=%s", $_REQUEST['editid']);
            $thisReport = $wpdb->get_row($sql);

            if(!empty($thisReport) && $thisReport->name == $_REQUEST['name'] && $thisReport->reportType == $_REQUEST['reportType'])
            {
                $updateYes = true;
            }


            if($updateYes)
            {
                //only the owner can update a universal report. Change all others to single.
                if($_POST['reportType'] == 'Universal')
                {
                    //is this the original owner?
                    if($thisReport->userID != $insert['userID'])
                    {
                        //change to single
                        $insert['reportType'] = 'Single';
                        $wpdb->insert('wp_gpx_report_writer', $insert);
                        $data = [
                            'success' => true,
                            'refresh' => '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer&id='.$wpdb->insert_id,
                        ];
                    }
                }
                if(!isset($data))
                {
                    $wpdb->update('wp_gpx_report_writer', $insert, array('id'=>$_REQUEST['editid']));
                    $data = [
                        'success' => true,
                        'refresh' => '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer&id='.$_REQUEST['editid'],
                    ];
                }
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
}
add_action('wp_ajax_gpx_report_write', 'gpx_report_write');

/**
 *
 *
 *
 *
 */
function post_gpx_tripadvisor_locationid($id)
{
    global $wpdb;

    $id = $_POST['rid'];
    $taID = $_POST['taid'];

    $wpdb->update('wp_resorts', array('taID'=>$taID), array('id'=>$id));

    $data = array('success'=>true);

    wp_send_json($data);
}
add_action('wp_ajax_post_gpx_tripadvisor_locationid', 'post_gpx_tripadvisor_locationid');
add_action('wp_ajax_nopriv_post_gpx_tripadvisor_locationid', 'post_gpx_tripadvisor_locationid');

/**
 *
 *
 *
 *
 */
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
}
add_action('wp_ajax_get_gpx_tripadvisor_location', 'get_gpx_tripadvisor_location');
add_action('wp_ajax_nopriv_get_gpx_tripadvisor_location', 'get_gpx_tripadvisor_location');


/**
 *
 *
 *
 *
 */
function get_gpx_tripadvisor_locations()
{
    global $wpdb;

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
}
add_action('wp_ajax_get_gpx_tripadvisor_locations', 'get_gpx_tripadvisor_locations');
add_action('wp_ajax_nopriv_get_gpx_tripadvisor_locations', 'get_gpx_tripadvisor_locations');


/**
 *
 *
 *
 *
 */
function get_gpx_resorttaxes()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_get_gpx_resorttaxes();

    wp_send_json($data);
}
add_action('wp_ajax_get_gpx_resorttaxes', 'get_gpx_resorttaxes');
add_action('wp_ajax_nopriv_get_gpx_resorttaxes', 'get_gpx_resorttaxes');

/**
 *
 *
 *
 *
 */
function add_gpx_resorttax()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_add_gpx_resorttax($_POST);

    wp_send_json($data);
}
add_action('wp_ajax_add_gpx_resorttax', 'add_gpx_resorttax');
add_action('wp_ajax_nopriv_add_gpx_resorttax', 'add_gpx_resorttax');


/**
 *
 *
 *
 *
 */
function edit_gpx_resorttax()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_edit_gpx_resorttax($_POST);

    wp_send_json($data);
}
add_action('wp_ajax_edit_gpx_resorttax', 'edit_gpx_resorttax');
add_action('wp_ajax_nopriv_edit_gpx_resorttax', 'edit_gpx_resorttax');


/**
 *
 *
 *
 *
 */
function update_gpx_resorttax_id()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_update_gpx_resorttax_id($_POST);

    wp_send_json($data);
}
add_action('wp_ajax_update_gpx_resorttax_id', 'update_gpx_resorttax_id');
add_action('wp_ajax_nopriv_update_gpx_resorttax_id', 'update_gpx_resorttax_id');


/**
 *
 *
 *
 *
 */
function edit_tax_method()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_edit_tax_method($_POST);

    wp_send_json($data);
}
add_action('wp_ajax_edit_tax_method', 'edit_tax_method');
add_action('wp_ajax_nopriv_edit_tax_method', 'edit_tax_method');

/**
 *
 *
 *
 *
 */
function add_ai()
{
    global $wpdb;

    $sql = "SELECT id FROM wp_resorts WHERE
        (ResortFacilities = 'All Inclusive')
        OR (JSON_VALID(ResortFacilities) AND JSON_CONTAINS(JSON_EXTRACT(ResortFacilities, '$[*]'), '\"All Inclusive\"', '$') )
        OR (HTMLAlertNotes LIKE '%IMPORTANT: All-Inclusive Information%')
        OR (AlertNote LIKE '%IMPORTANT: This is an All Inclusive (AI) property.%')";
    $props = $wpdb->get_col($sql);
    $placeholders = gpx_db_placeholders($props, '%d');
    $sql = $wpdb->prepare("UPDATE wp_resorts SET ai = 1 WHERE id IN ({$placeholders})", $props);
    $wpdb->query($sql);
}
add_action('wp_ajax_add_ai', 'add_ai');
add_action('wp_ajax_nopriv_add_ai', 'add_ai');
