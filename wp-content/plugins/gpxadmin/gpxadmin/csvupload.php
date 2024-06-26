<?php




/**
 *
 *
 *
 *
 */
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

                $check_out_date = date('Y-m-d', strtotime($line[9].'+1 week'));


                //resort fetch
                $resort = $line[7];
                $resorts = $wpdb->prepare("SELECT * FROM `wp_resorts` WHERE `ResortName` = %s ORDER BY `id` ASC", $resort);
                $resorts_result = $wpdb->get_results($resorts);

                $resort = $resorts_result[0]->id;

                //unit type by resort
                $name = $line[8];
                $unit_type = $wpdb->prepare("SELECT record_id FROM `wp_unit_type` WHERE `name` = %s AND `resort_id` = %s ORDER BY `record_id` ASC", [$name, $resorts_result[0]->id]);
                $unit_type = $wpdb->get_var($unit_type);

                $type  = $type;
                $source_num  = $source_num;

                //fetch partners
                $spid = $wpdb->prepare("SELECT * FROM wp_users a INNER JOIN wp_usermeta b on a.ID=b.user_id WHERE b.meta_key='DAEMemberNo' AND (user_nicename = %s OR display_name = %s)", [$line[7],$line[7]]);
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
                $avltop = $wpdb->prepare("SELECT * FROM `wp_partner` WHERE name = %s", $line[10]);
                $avltop_result = $wpdb->get_results($avltop);
                $available_to_partner_id   = $avltop_result[0]->record_id;

                $gvtop = $wpdb->prepare("SELECT * FROM `wp_partner` WHERE name = %s",$line[10] );
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


                $wpdb->insert('wp_room', $inserts);
                if($wpdb->last_error)
                {
                    exit;
                }

                $ids[$counter] = $wpdb->insert_id;
            }


        }

        foreach ($ids as $value) {
            $updatedb_query = $wpdb->update('wp_room', ['import_id' => $ids[1]], ['record_id' => $value]);
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

}
add_action('wp_ajax_csv_upload', 'csv_upload');
add_action('wp_ajax_nopriv_csv_upload', 'csv_upload');

