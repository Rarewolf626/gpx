<?php




/**
 *
 *  sf_import_resorts
 *
 * https://www.gpxvacations.com/wp-admin/admin-ajax.php?action=sf_import_resorts
 * import/sync resorts
 * ran manually with URL only
 *
 * finds resorts in SF that have been changed in last 14 days,
 * attempts to match them to resort names in GPX then updates GPX (wp_resorts) with the
 * SF id for that resort
 *
 */
function sf_import_resorts($resortid='')
{
    global $wpdb;

    $sf = Salesforce::getInstance();

    $selects = [
        'Id',
        'Name',
    ];
    $query =  "select ".implode(", ", $selects)." from Resort__c  where
                    SystemModStamp >= LAST_N_DAYS: 14";
    $results = $sf->query($query);
    $checked = [];

    foreach($results as $result)
    {
        $fields = $result->fields;
        $id = $result->Id;

        $sql = $wpdb->prepare('SELECT * FROM wp_resorts WHERE ResortName LIKE %s', $wpdb->esc_like($fields->Name).'%');
        $row = $wpdb->get_row($sql);
        if(!empty($row))
        {
            $wpdb->update('wp_resorts', array('gprID'=>$id), array('id'=>$row->id));
            $dataset['just set'][] = $fields->Name;
        }
        else
        {
            $dataset['no match'][] = $sql;
        }

        $updateResorts['alertResult'] = json_encode($an);

        $wpdb->insert('resort_import', $updateResorts);

    }
    wp_send_json($dataset);
}
add_action('wp_ajax_sf_import_resorts', 'sf_import_resorts');






/**
 *
 * @depecated
 *
 *  this appears to not be used and is broken
 */
function sf_update_resorts($resortid='')
{
    global $wpdb;

    $sf = Salesforce::getInstance();

    $selects = [
        'Id',
        'Name',
        'GPX_Resort_ID__c',
    ];

    $id = $_REQUEST['id'] ?? null;
    $query = DB::table('wp_resorts');
    if (isset($_REQUEST['address_refresh'])) {
        $query->where('Town', '=', '');
        $query->where('Region', '=', '');
        $query->orderBy('lastUpdate', 'desc');
    } elseif($id) {
        $query->where('active', '=', 1);
        $query->where('id', '=', $id);
        $query->when($resortid, fn($query) => $query->where('ResortID', '=', $resortid));
    } else {
        $query->whereNull('sf_GPX_Resort__c');
        $query->where('gprID', '!=', '');
        $query->take(10);
    }
    $results = $query->get()->toArray();

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
                $sql = $wpdb->prepare("SELECT meta_value FROM wp_resorts_meta WHERE meta_key=%s AND ResortID=%s", [$rf,$row->ResortID]);
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

            $refreshUPdate = $wpdb->update('wp_resorts',(array) $update, array('id'=>$thisResortID));

        }

        if(!empty($row))
        {
            $an = [];
            $updateResorts['resort'] = $row->id;
            if(!empty($row->gprID))
            {
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
                'State_Region__c'=>'Region',
                'Street_Address__c'=>'Address1',
                'Zip_Postal_Code__c'=>'PostCode',
            ];

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


            $updateResorts['resortResult'] = json_encode($sfResortAdd);

            $sfID = $sfResortAdd[0]->id;

            $wpdb->update('wp_resorts', array('sf_GPX_Resort__c'=>$sfID), array('id'=>$row->id));

            $sql = $wpdb->prepare("SELECT id, meta_value FROM wp_resorts_meta WHERE ResortID=%s AND meta_key='AlertNote'", $row->ResortID);
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

                        $an[] = $sfalertnoteEdit;
                    }
                    else
                    {
                        $sfFields[0]->fields = $sfAlertNote;
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

    wp_send_json(array('remaining'=>$remain));
}
add_action('wp_ajax_sf_update_resorts', 'sf_update_resorts');



/**
 *
 *
 *
 *
 */
function salesforce_connect()
{


    $sf = Salesforce::getInstance();
    /*  query test
     *
     *
     *      */

    $query = "select owner_id__c, property_owner__c, id from ownership_interval__c where ROID_Key_480East__c ='R04351163321A14H08'";
    $data = $sf->query($query);

    wp_send_json($data);
}
add_action("wp_ajax_salesforce_connect","salesforce_connect");
add_action("wp_ajax_nopriv_salesforce_connect", "salesforce_connect");



/**
 *
 *
 *
 *
 */
function gpx_mass_import_to_sf()
{
    global $wpdb;

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

        $sql = $wpdb->prepare('SELECT resortName FROM wp_properties WHERE weekId=%s', $row->weekId);
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
}
add_action('wp_ajax_gpx_mass_import_to_sf', 'gpx_mass_import_to_sf');
add_action('wp_ajax_nopriv_gpx_mass_import_to_sf', 'gpx_mass_import_to_sf');



/**
 *
 *
 *
 *
 */
function gpx_sf_test()
{
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->gpx_get_sf_object_test();

    wp_send_json($data);
}
add_action('wp_ajax_gpx_sf_test', 'gpx_sf_test');
add_action('wp_ajax_nopriv_gpx_sf_test', 'gpx_sf_test');




