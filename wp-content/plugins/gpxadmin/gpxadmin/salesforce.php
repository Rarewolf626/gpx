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
 * @depecated Use `php console sf:resort:fix-ids` instead
 *
 */
function sf_import_resorts($resortid = null): void
{
    global $wpdb;
    $resortid = $resortid ?? $_REQUEST['id'] ?? null;
    $sf = Salesforce::getInstance();
    $query = /** @lang sfquery */ "select Id,Name from Resort__c  where SystemModStamp >= LAST_N_DAYS: 14";
    $results = $sf->query($query);
    $dataset = ['just set' => [], 'no match' => [],];
    foreach ($results as $result) {
        $fields = $result->fields;
        $id = $result->Id;
        $sql = $wpdb->prepare('SELECT * FROM wp_resorts WHERE ResortName LIKE %s', $wpdb->esc_like($fields->Name) . '%');
        $row = $wpdb->get_row($sql);
        if (!empty($row)) {
            $wpdb->update('wp_resorts', array('gprID' => $id), array('id' => $row->id));
            $dataset['just set'][] = $fields->Name;
        } else {
            $dataset['no match'][] = $sql;
        }
    }
    wp_send_json($dataset);
}
add_action('wp_ajax_sf_import_resorts', 'sf_import_resorts');

/**
 *
 * @depecated Use `php console sf:resort:push` instead
 *
 *  this appears to not be used and is broken
 *
 */
function sf_update_resorts($resortid=''): void
{
    global $wpdb;

    $sf = Salesforce::getInstance();
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

    foreach($results as $row) {

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

        $sfFields = new SObject();
        $sfFields->fields = $sfResortData;
        $sfFields->type = 'GPX_Resort__c';
        $sfResortAdd = $sf->gpxUpsert('GPX_Resort_ID__c', [$sfFields]);

        $sfID = $sfResortAdd[0]?->id ?? null;
        if(!$sfID) {
            $wpdb->update('wp_resorts', array('sf_GPX_Resort__c'=>$sfID), array('id'=>$row->id));
        }
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
        if(!empty($sfTaxAmount))
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
