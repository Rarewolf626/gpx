<?php

class GpxRetrieve
{
    public $uri;
    public $dir;
    public $daecred;
    public $expectedMemberDetails;
    public $expectedBookingDetails;
    public $expectedPaymentDetails;
    public static $instance = null;


    public function __construct($uri = null, $dir = null)
    {
        $this->uri = plugins_url('', __FILE__).'/api';
        $this->dir = trailingslashit( dirname(__FILE__) );

        $this->daecred = array(
            'action' => get_option('dae_ws_action'),
            'host' => get_option('dae_ws_host'),
            'AuthID' => get_option('dae_ws_authid'),
            'DAEMemberNo' => get_option('dae_ws_memberno'),
            'mode' => get_option('dae_ws_mode'),
        );

        $this->expectedMemberDetails = [
            'MemberNo'=>'YES',
            'AccountName'=>'YES',
            'Address1'=>'YES',
            'Address2'=>'NO',
            'Address3'=>'YES',
            'Address4'=>'YES',
            'Address5'=>'YES',
            'BroadcastEmail'=>'NO',
            'DayPhone'=>'NO',
            'Email'=>'YES',
            'Email2'=>'YES',
            'Fax'=>'NO',
            'Salutation'=>'YES',
            'Title1'=>'YES',
            'Title2'=>'YES',
            'FirstName1'=>'YES',
            'FirstName2'=>'NO',
            'HomePhone'=>'YES',
            'LastName1'=>'YES',
            'LastName2'=>'NO',
            'MailName'=>'YES',
            'Mobile'=>'NO',
            'Mobile2'=>'NO',
            'NewsletterStatus'=>'YES',
            'PostCode'=>'YES',
            'Password'=>'NO',
            'ReferalID'=>'YES',
            'ExternalMemberNumber'=>'NO',
            'MailOut'=>'YES',
            'SMSStatus'=>'YES',
            'SMSNumber'=>'YES',
        ];

        $this->expectedBookingDetails = [
            'CreditWeekID',
            'WeekEndpointID',
            'WeekID',
            'GuestFirstName',
            'GuestLastName',
            'GuestEmailAddress',
            'Adults',
            'Children',
            'WeekType',
            'CPO',
            'AmountPaid',
            'DAEMemberNo',
            'ResortID',
            'CurrencyCode',
            'GuestAddress',
            'GuestTown',
            'GuestState',
            'GuestPostCode',
            'GuestPhone',
            'GuestMobile',
            'GuestCountry',
            'GuestTitle',
        ];
        $this->expectedPaymentDetails = [
            'DAEMemberNo',
            'Address',
            'PostCode',
            'Country',
            'Email',
            'CardHolder',
            'CardNo',
            'CCV',
            'ExpiryMonth',
            'ExpiryYear',
            'PaymentAmount',
            'CurrencyCode',
        ];
    }

    public static function instance(): GpxRetrieve
    {
        if (!self::$instance) {
            self::$instance = new GpxRetrieve( GPXADMIN_API_URI, GPXADMIN_API_DIR );
        }
        return self::$instance;
    }

    function addRegions()
    {
        global $wpdb;

        $sql = "SELECT * FROM wp_daeRegion";
        $existingRegions = $wpdb->get_results($sql);
        foreach($existingRegions as $allRegions)
        {
            $regionsCheck[$allRegions->id] = $allRegions->region;
        }
        $sql = "SELECT * FROM wp_daeCountry";
        $existingCountries = $wpdb->get_results($sql);
        foreach($existingCountries as $allCountries)
        {
            $countriesCheck[$allCountries->id] = $allCountries->country;
        }
        $countries = $this->DAEGetCountryList();
        foreach($countries as $countryXML)
        {
            //insert each countries
            $country = json_decode(json_encode($countryXML));
            $data = array('country'=>$country->ItemDescription,
                'CountryID'=>$country->ItemID);
            if(in_array($country->ItemDescription, $countriesCheck))
            {
                $validatedCountry[] = $country->ItemDescription;
            }
            if($wpdb->update('wp_gpxCategory', array('newCountryID'=>$country->ItemID), array('country'=>$country->ItemDescription, 'search_name' => $country->ItemDescription)))
            {
                //updated
            }
            else
            {
                $wpdb->insert('wp_gpxCategory', array('country'=>$country->ItemDescription, 'newCountryID'=>$country->ItemID, 'search_name' => $country->ItemDescription));
            }

            $regions = $this->DAEGetRegionList($country->ItemID);

            foreach($regions as $regionXML)
            {
                $region = json_decode(json_encode($regionXML));
                $countryParsed = json_decode(json_encode($country));
                $data = array('region'=>$region->ItemDescription,
                    'RegionID'=>$region->ItemID,
                    'CountryID'=>$countryParsed->ItemID,
                );
                if(in_array($region->ItemDescription, $regionsCheck))
                {
                    if($region->ItemDescription == 'All')
                    {
                        continue;
                    }
                    $validatedRegion[] = $region->ItemDescription;
                }

            }
        }

        return array('success'=>true);
    }


    function DAEGetCountryList($ping = '')
    {
        global $wpdb;
        $data = array(
            'functionName'=>'DAEGetCountryList',
            'inputMembers'=>array(
                'FilterByParent'=>'false',
            ),
            'return'=>'GeneralListItem',
        );

        $countries = [];

        if(empty($ping))
        {
            $wpdb->update('wp_daeCountry', array('active'=>'0'), array('active'=>'1'));

            foreach($countries as $countryXML)
            {
                $country = json_decode(json_encode($countryXML));
                $data = [
                    'country'=>$country->ItemDescription,
                    'CountryID'=>$country->ItemID,
                    'active'=>1,
                ];
                $wpdb->insert('wp_daeCountry', $data);
            }
        }
        else
        {
            $output = json_decode(json_encode($countries));
            if(isset($output[0]->ItemID))
            {
                $countries = ['success'=>true];
            }
        }
        return $countries;
    }
    function DAEGetRegionList($CountryID)
    {
        $data = array(
            'functionName'=>'DAEGetRegionList',
            'inputMembers'=>array(
                'CountryID'=>$CountryID,
            ),
            'return'=>'GeneralListItem',
        );

        $regions = [];

        return $regions;
    }
    function DAEGetBonusRentalAvailability($inputMembers)
    {
        extract($inputMembers);
        $data = array(
            'functionName'=>'DAEGetBonusRentalAvailability',
            'inputMembers'=>array(
                'CountryID'=>$CountryID,
                'RegionID'=>$RegionID,
                'Month'=>$Month,
                'Year'=>$Year,
                'WeeksToShow'=>$WeeksToShow,
                'Sort'=>$Sort,
                'DAEMemberNo'=>$this->daecred[DAEMemberNo],
            ),
            'return'=>'AvailabilityDetail',
        );

        $rentals = [];

        return $rentals;
    }

    function DAEGetExchangeAvailability($inputMembers)
    {
        extract($inputMembers);
        $data = array(
            'functionName'=>'DAEGetExchangeAvailability',
            'inputMembers'=>array(
                'CountryID'=>$CountryID,
                'RegionID'=>$RegionID,
                'Month'=>$Month,
                'Year'=>$Year,
                'ShowSplitWeeks'=>True,
                'DAEMemberNo'=>$this->daecred[DAEMemberNo],
            ),
            'return'=>'AvailabilityDetail',
        );

        $rentals = [];

        return $rentals;
    }
    function AddDAEGetBonusRentalAvailability($inputMembers)
    {
        global $wpdb;

        extract($inputMembers);
        if($RegionID == '?')
        {
            echo '<pre>'.print_r("ERROR: You must select a region -- All Not allowed!", true).'</pre>';
            return;
        }
        $data = array(
            'functionName'=>'DAEGetBonusRentalAvailability',
            'inputMembers'=>array(
                'CountryID'=>$CountryID,
                'RegionID'=>$RegionID,
                'Month'=>$Month,
                'Year'=>$Year,
                'WeeksToShow'=>$WeeksToShow,
                'Sort'=>$Sort,
            ),
            'return'=>'AvailabilityDetail',
        );
        $rentals = [];
        echo '<pre>'.print_r($rentals, true).'</pre>';
        if(!empty($rentals))
        foreach($rentals as $value)
        {
            $out = array();
            foreach ( (array) $value as $index => $node )
                $out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;


            $gpxRegion='';
            $plr = '';
            $regionsTableRegion = '';

            if($out['region'] == "Hawaiian Islands")
                $out['region'] = "Hawaii";

                //check/get locality are already set in gpxRegions
                $sql = $wpdb->prepare("SELECT id, name FROM wp_gpxRegion WHERE name=%s", $out['locality']);
                $gpxRegion = $wpdb->get_row($sql);
                if(!empty($gpxRegion))
                    $subRegion = $gpxRegion->id;
                else
                {
                    $query = $wpdb->prepare("SELECT id, lft, rght FROM wp_gpxRegion WHERE name=%s", $out['region']);
                    $plr = $wpdb->get_row($query);
                    //if region exists then add the child
                    if(!empty($plr))
                    {
                            $right = $plr->rght;

                            $sql1 = $wpdb->prepare("UPDATE wp_gpxRegion SET lft=lft+2 WHERE lft>%d", $right);
                            $wpdb->query($sql1);
                            $sql2 = $wpdb->prepare("UPDATE wp_gpxRegion SET rght=rght+2 WHERE rght>=%d", $right);
                            $wpdb->query($sql2);

                            $update = array('name'=>$out['locality'],
                                            'search_name' => gpx_search_string($out['locality']),
                                            'parent'=>$plr->id,
                                            'lft'=>$right,
                                            'rght'=>$right+1
                            );
                            $wpdb->insert('wp_gpxRegion', $update);
                            $subRegion = $wpdb->insert_id;
                    }
                    //otherwise we need to pull the parent region from the daeRegion table and add both the region and locality as sub region
                    else
                    {
                        $query2 = $wpdb->prepare("SELECT a.id, a.lft, a.rght FROM wp_gpxRegion a
                                    INNER JOIN wp_daeRegion b ON a.RegionID=b.id
                                    WHERE b.RegionID=%s
                                    AND b.CountryID=%s", [$RegionID,$CountryID]);

                        $parent = $wpdb->get_row($query2);

                        $right = $parent->rght;

                        $sql3 = $wpdb->prepare("UPDATE wp_gpxRegion SET lft=lft+4 WHERE lft>%d", $right);
                        $wpdb->query($sql3);
                        $sql4 = $wpdb->prepare("UPDATE wp_gpxRegion SET rght=rght+4 WHERE rght>=%d", $right);
                        $wpdb->query($sql4);

                        $updateRegion = array(
                            'name'=>$out['region'],
                            'search_name'=>gpx_search_string($out['region']),
                            'parent'=>$parent->id,
                            'lft'=>$right,
                            'rght'=>$right+3
                        );
                        $wpdb->insert('wp_gpxRegion', $updateRegion);
                        $newid = $wpdb->insert_id;

                        $updateLocality = array(
                            'name'=>$out['locality'],
                            'search_name'=>gpx_search_string($out['locality']),
                            'parent'=>$newid,
                            'lft'=>$right+1,
                            'rght'=>$right+2
                        );
                        $wpdb->insert('wp_gpxRegion', $updateLocality);
                        $subRegion = $wpdb->insert_id;

                    }
                }
                $wkv = array();
                foreach($out as $k=>$v)
                {
                    $wkv[] = $wpdb->prepare(gpx_esc_table($k)." = %s", $v);
                }
                $wheres = implode(" AND ", $wkv);
                $sql = "SELECT id FROM wp_properties WHERE ".$wheres;
                $roi = $wpdb->get_row($sql);

                $out['active'] = 1;

                if(!empty($roi))//this record already exist we are going to update it
                    $wpdb->update('wp_properties', $out, array('id'=>$roi->id));
                else //we need to add this record
                    $wpdb->insert('wp_properties', $out);


                //pull the resort; if it's new or more than 1 month old then replace the resort information
                $sql = $wpdb->prepare("SELECT * FROM wp_resorts WHERE ResortID=%s", $out['resortId']);
                echo '<pre>'.print_r($sql, true).'</pre>';
                $resort = $wpdb->get_row($sql);
                if(empty($resort) || strtotime($resort->lastUpdate) < strtotime("-4 month"))
                {
                    $data2 = array(
                        'functionName'=>'DAEGetResortProfile',
                        'inputMembers'=>array(
                            'EndpointID'=>$out['WeekEndpointID'],
                            'ResortID'=>$out['resortId'],
                        ),
                        'return'=>'ResortProfile',
                    );
                    $propDetails = [];
                    foreach($propDetails as $prop)
                    {
                        foreach((array) $prop as $ind => $no)
                        {
                            $keyskip = array('ReturnCode', 'ReturnMessage');
                            if(in_array($ind, $keyskip))
                                continue;
                            if(is_object($no))
                            {
                                $op = $this->xml2array($no);
                                $no = json_encode($op['string']);
                            }

                            $output[$ind] = $no;

                        }
                    }
                    if(empty($resort))
                        $output['gpxRegionID'] = $subRegion;
                    else
                        $output['gpxRegionID'] = $resort->gpxRegionID;
                    $rkv = array();
                    foreach($output as $k=>$v)
                    {
                        $rkv[] = $wpdb->prepare(gpx_esc_table($k)." = %s", $v);
                    }
                    $rwheres = implode(" AND ", $rkv);
                    $sql = "SELECT id FROM wp_properties WHERE ".$rwheres;
                    $reoi = $wpdb->get_row($sql);

                    $out['active'] = 1;

                    if(!empty($roi))//this record already exist we are going to update it
                        $wpdb->update('wp_resorts', $output, array('id'=>$roi->id));
                    elseif(!empty($output['ResortName'])) //we need to add this record but only if we got good data from DAE
                        $wpdb->insert('wp_resorts', $output);

                }

        }

        return array('success'=>true);
    }
    function NewAddDAEGetBonusRentalAvailability($inputMembers)
    {
        global $wpdb;

        extract($inputMembers);
        $ftstart = $this->microtime_float();
        $data = array(
            'functionName'=>'DAEGetBonusRentalAvailability',
            'inputMembers'=>array(
                'CountryID'=>$CountryID,
                'RegionID'=>$RegionID,
                'Month'=>$Month,
                'Year'=>$Year,
                'WeeksToShow'=>$WeeksToShow,
                'Sort'=>$Sort,
                'DAEMemberNo'=>$this->daecred['DAEMemberNo'],
            ),
            'return'=>'AvailabilityDetail',
        );

        echo '<pre>'.print_r($data, true).'</pre>';
        $wheres = $wpdb->prepare("CountryID=%s", $CountryID);
        if($RegionID != '?')
        {
            $wheres .= $wpdb->prepare(" AND RegionID=%s", $RegionID);
        }

        $sql = "SELECT id FROM wp_daeRegion WHERE ".$wheres;
        $rows = $wpdb->get_results($sql);

        $mtstart = $this->microtime_float();
        $rentals = [];

        $mtend = $this->microtime_float();
        $seconds = $mtend - $mtstart;
        echo '<pre>'.print_r("Response Time: ".$seconds, true).'</pre>';

        //check if successful

        if(isset($rentals[0]->ReturnCode) && $rentals[0]->ReturnCode != 0)
        {
            //exit because we didn't return success;
            echo '<pre>'.print_r("error: ".$success['ReturnMessage'], true).'</pre>';
            return array('error', $success['ReturnMessage']);
        }

        foreach($rows as $row)
        {
            //set all weeks to inactive
            $sql = $wpdb->prepare("SELECT lft, rght FROM wp_gpxRegion WHERE RegionID=%s", $row->id);
            $row = $wpdb->get_row($sql);
            $lft = $row->lft;
            if(!empty($lft))
            {
                $sql = $wpdb->prepare("SELECT id, lft, rght FROM wp_gpxRegion
                            WHERE lft BETWEEN %s AND %s
                             ORDER BY lft ASC", [$lft,$row->rght]);
                $gpxRegions = $wpdb->get_results($sql);

                $monthstart = date('Y-m-01', strtotime($Year."-".$Month."-01"));
                $monthend = date('Y-m-t', strtotime($Year."-".$Month."-01"));

                foreach($gpxRegions as $gpxRegion)
                {
                    $regionSet = false;
                    $sql = $wpdb->prepare("SELECT *, a.id AS pid FROM wp_properties a
                        INNER JOIN wp_resorts b ON a.resortId=b.ResortID
                        WHERE b.gpxRegionID=%s
                        AND (WeekType='BonusWeek' OR WeekType='RentalWeek')
                        AND STR_TO_DATE(checkIn, '%%d %%M %%Y') BETWEEN %s AND %s
                        AND a.active=1", [$gpxRegion->id, $monthstart, $monthend]);
                    $rows = $wpdb->get_results($sql);
                    foreach($rows as $row)
                    {
                        $removed[$row->pid] = $row->pid;
                        $allByWeekID[$row->weekId][$row->WeekType] = $row;
                    }
                }
            }
        }
        $rtend = $this->microtime_float();
        $regionTime = $rtend - $mtend;
        echo '<pre>'.print_r("regions: ".$regionTime, true).'</pre>';
        if(!empty($rentals))
        {
            $vars = get_object_vars($rentals[0]);

            //update each week
            foreach($rentals as $value)
            {

                $out2 = json_decode(json_encode($value));

                if(isset($quick) && array_key_exists($out2->WeekType, $allByWeekID[$out2->weekId]))
                {
                    if($out2->WeekPrice == $allByWeekID[$out2->weekId][$out2->WeekType]->WeekPrice)
                    {
                        unset($removed[$allByWeekID[$out2->weekId][$out2->WeekType]->pid]);
                        echo '<pre>'.print_r("Skipped: ".$out2->weekId, true).'</pre>';
                        continue;
                    }
                }

                $wheres2 = $wpdb->prepare("weekId=%s AND WeekType=%s", [$out2->weekId,$out2->WeekType]);

                $sql = $wpdb->prepare("SELECT id FROM wp_properties WHERE weekId=%s AND WeekType=%s", [$out2->weekId,$out2->WeekType]);
                $roi = $wpdb->get_row($sql);

                $out2->active = 1;
                foreach($out2 as $outKey=>$outVal)
                {
                    if(is_object($outVal) && !empty( (array) $outVal))
                        $qout[$outKey] = json_encode($$outVal);
                        else
                            $qout[$outKey] = $outVal;
                }

                //get the resort id so that we can enter it...
                $sql = $wpdb->prepare("SELECT id FROM wp_resorts WHERE ResortID=%s", $qout['resortId']);
                $row = $wpdb->get_row($sql);
                if(!empty($row))
                {
                    $qout['resortJoinID'] = $row->id;
                }
                else //we need to add
                {
                    $newResort = $this->missingDAEGetResortProfile($qout['resortId'], $qout['WeekEndpointID']);
                    $qout['resortJoinID'] = $newResort['id'];
                }

                if(!empty($roi)) //this record already exist we are going to update it
                {
                    $wpdb->update('wp_properties', $qout, array('id'=>$roi->id));
                    unset($removed[$roi->id]);
                    if(isset($dbActiveRefresh))
                    {
                        $propertiesAdded[$roi->id] = $roi->id;
                    }

                }
                    else //we need to add this record
                        $wpdb->insert('wp_properties', $qout);

                        $gpxRegion='';
                        $plr = '';
                        $regionsTableRegion = '';

            }
        }

        if(!empty($removed))
        {
            $wpdb->insert('wp_refresh_removed', array('removed'=>json_encode($removed), 'type'=>'rental'));
            foreach($removed as $remove)
            {
                $wpdb->update('wp_properties', array('active'=>0), array('id'=>$remove));
            }
        }
        $ftend = $this->microtime_float();
        $utime = $ftend - $rtend;
        echo '<pre>'.print_r("Update: ".$utime, true).'</pre>';
        $seconds = $ftend - $ftstart;
        echo '<pre>'.print_r("total: ".$seconds, true).'</pre>';
        if(isset($propertiesAdded))
        {
            $toReturn['weeks_added'] = $propertiesAdded;
        }
        echo '<pre>'.print_r($toReturn, true).'</pre>';
        $toReturn['success'] = true;
        return $toReturn;
    }
    function AddDAEGetExchangeAvailability($inputMembers)
    {
        global $wpdb;

        extract($inputMembers);
        if($RegionID == '?')
        {
            echo '<pre>'.print_r("ERROR: You must select a region -- All Not allowed!", true).'</pre>';
            return;
        }
        $data = array(
            'functionName'=>'DAEGetExchangeAvailability',
            'inputMembers'=>array(
                'CountryID'=>$CountryID,
                'RegionID'=>$RegionID,
                'Month'=>$Month,
                'Year'=>$Year,
                'ShowSplitWeeks'=>True,
                'DAEMemberNo'=>$this->daecred[DAEMemberNo],
            ),
            'return'=>'AvailabilityDetail',
        );
        $rentals = [];



        if(!empty($rentals))
        foreach($rentals as $rental)
        {
            $out = array();
            $out = (array) $rental;



            $gpxRegion='';
            $plr = '';
            $regionsTableRegion = '';

            if($out['region'] == "Hawaiian Islands")
                $out['region'] = "Hawaii";

                //check/get locality are already set in gpxRegions
                $sql = $wpdb->prepare("SELECT id, name FROM wp_gpxRegion WHERE name=%s", $out['locality']);
                $gpxRegion = $wpdb->get_row($sql);
                if(!empty($gpxRegion))
                    $subRegion = $gpxRegion->id;
                else
                {
                    $query = $wpdb->prepare("SELECT id, lft, rght FROM wp_gpxRegion WHERE name=%s", $out['region']);
                    $plr = $wpdb->get_row($query);
                    //if region exists then add the child
                    if(!empty($plr))
                    {
                            $right = $plr->rght;

                            $sql1 = $wpdb->prepare("UPDATE wp_gpxRegion SET lft=lft+2 WHERE lft>%d", $right);
                            $wpdb->query($sql1);
                            $sql2 = $wpdb->prepare("UPDATE wp_gpxRegion SET rght=rght+2 WHERE rght>=%d", $right);
                            $wpdb->query($sql2);

                            $update = array(
                                'name'=>$out['locality'],
                                'search_name'=>gpx_search_string($out['locality']),
                                'parent'=>$plr->id,
                                'lft'=>$right,
                                'rght'=>$right+1
                            );
                            $wpdb->insert('wp_gpxRegion', $update);
                            $subRegion = $wpdb->insert_id;
                    }
                    //otherwise we need to pull the parent region from the daeRegion table and add both the region and locality as sub region
                    else
                    {
                        $query2 = $wpdb->prepare("SELECT a.id, a.lft, a.rght FROM wp_gpxRegion a
                                    INNER JOIN wp_daeRegion b ON a.RegionID=b.id
                                    WHERE b.RegionID=%s
                                    AND b.CountryID=%s", [$RegionID,$CountryID]);

                        $parent = $wpdb->get_row($query2);

                        $right = $parent->rght;

                        $sql3 = $wpdb->prepare("UPDATE wp_gpxRegion SET lft=lft+4 WHERE lft>%d", $right);
                        $wpdb->query($sql3);
                        $sql4 = $wpdb->prepare("UPDATE wp_gpxRegion SET rght=rght+4 WHERE rght>=%d", $right);
                        $wpdb->query($sql4);

                        $updateRegion = array(
                            'name'=>$out['region'],
                            'search_name'=>gpx_search_string($out['region']),
                            'parent'=>$parent->id,
                            'lft'=>$right,
                            'rght'=>$right+3
                        );
                        $wpdb->insert('wp_gpxRegion', $updateRegion);
                        $newid = $wpdb->insert_id;

                        $updateLocality = array(
                            'name'=>$out['locality'],
                            'search_name'=>gpx_search_string($out['locality']),
                            'parent'=>$newid,
                            'lft'=>$right+1,
                            'rght'=>$right+2
                        );
                        $wpdb->insert('wp_gpxRegion', $updateLocality);
                        $subRegion = $wpdb->insert_id;

                    }
                }
                $wkv = array();
                foreach($out as $k=>$v)
                {
                    $wkv[] = $wpdb->prepare(gpx_esc_table($k)." = '%s", $v);
                }
                $wheres = implode(" AND ", $wkv);
                $sql = "SELECT id FROM wp_properties WHERE ".$wheres;
                $roi = $wpdb->get_row($sql);

                $out['active'] = 1;

                if(!empty($roi))//this record already exist we are going to update it
                    $wpdb->update('wp_properties', $out, array('id'=>$roi->id));
                else //we need to add this record
                    $wpdb->insert('wp_properties', $out);

                //pull the resort if it's new or more than 1 month old then replace the resort information
                $sql = $wpdb->prepare("SELECT * FROM wp_resorts WHERE ResortID=%s", $out['resortId']);
                $resort = $wpdb->get_row($sql);
                if(empty($resort) || strtotime($resort->lastUpdate) < strtotime("-4 month"))
                {
                    $data2 = array(
                        'functionName'=>'DAEGetResortProfile',
                        'inputMembers'=>array(
                            'EndpointID'=>$out['WeekEndpointID'],
                            'ResortID'=>$out['resortId'],
                        ),
                        'return'=>'ResortProfile',
                    );
                    $propDetails = [];
                    foreach($propDetails as $prop)
                    {
                        foreach((array) $prop as $ind => $no)
                        {
                            $keyskip = array('ReturnCode', 'ReturnMessage');
                            if(in_array($ind, $keyskip))
                                continue;
                            if(is_object($no))
                            {
                                $op = $this->xml2array($no);
                                $no = json_encode($op['string']);
                            }

                            $output[$ind] = $no;

                        }
                    }
                    if(empty($resort))
                        $output['gpxRegionID'] = $subRegion;
                    else
                        $output['gpxRegionID'] = $resort->gpxRegionID;
                    // @TODO this $rkv variable is not set
                    $rwheres = implode(" AND ", $rkv);
                    $sql = "SELECT id FROM wp_properties WHERE ".$rwheres;
                    $reoi = $wpdb->get_row($sql);

                    $out['active'] = 1;

                    if(!empty($roi))//this record already exist we are going to update it
                        $wpdb->update('wp_resorts', $output, array('id'=>$roi->id));
                    elseif(!empty($output['ResortName'])) //we need to add this record but only if we got good data from DAE
                        $wpdb->insert('wp_resorts', $output);
                }

        }
        return array('success'=>true);
    }
    function NewAddDAEGetExchangeAvailability($inputMembers)
    {
        global $wpdb;

        extract($inputMembers);

        $data = array(
            'functionName'=>'DAEGetExchangeAvailability',
            'inputMembers'=>array(
                'CountryID'=>$CountryID,
                'RegionID'=>$RegionID,
                'Month'=>$Month,
                'Year'=>$Year,
                'ShowSplitWeeks'=>True,
                'DAEMemberNo'=>$this->daecred[DAEMemberNo],
            ),
            'return'=>'AvailabilityDetail',
        );
        echo '<pre>'.print_r($data, true).'</pre>';


        $mtstart = $this->microtime_float();
        $rentals =[];
        echo '<pre>'.print_r($rentals, true).'</pre>';
        $mtend = $this->microtime_float();
        $seconds = $mtend - $mtstart;
        echo '<pre>'.print_r("Response Time: ".$seconds, true).'</pre>';
        //check if successful

        if(isset($rentals[0]->ReturnCode) && $rentals[0]->ReturnCode != 0)
        {
            //exit because we didn't return success;
            echo '<pre>'.print_r("error: ".$success['ReturnMessage'], true).'</pre>';
            return array('error', $success['ReturnMessage']);
        }

        $wheres = $wpdb->prepare("CountryID=%s", $CountryID);
        if($RegionID != '?')
        {
            $wheres .= $wpdb->prepare(" AND RegionID=%s", $RegionID);
        }

        $sql = "SELECT id FROM wp_daeRegion WHERE ".$wheres;
        $rows = $wpdb->get_results($sql);
        echo '<pre>'.print_r($sql, true).'</pre>';
        foreach($rows as $r)
        {
            //set all weeks to inactive
            $sql = $wpdb->prepare("SELECT lft, rght FROM wp_gpxRegion WHERE RegionID=%s", $r->id);
            echo '<pre>'.print_r("Inactive Weeks", true).'</pre>';
            echo '<pre>'.print_r($sql, true).'</pre>';
            $row = $wpdb->get_row($sql);
            $lft = $row->lft;
            if(!empty($lft))
            {
                $sql = $wpdb->prepare("SELECT DISTINCT id, lft, rght FROM wp_gpxRegion
                            WHERE lft BETWEEN %d AND %d
                             ORDER BY lft ASC", [$lft,$row->rght]);
                $gpxRegions = $wpdb->get_results($sql);
                echo '<pre>'.print_r("GPX Regions", true).'</pre>';
                echo '<pre>'.print_r($gpxRegions, true).'</pre>';

                $monthstart = date('Y-m-01', strtotime($Year."-".$Month."-01"));
                $monthend = date('Y-m-t', strtotime($Year."-".$Month."-01"));

                foreach($gpxRegions as $gpxRegion)
                {
                    $regionSet = false;
                    $sql = $wpdb->prepare("SELECT *, a.id AS pid FROM wp_properties a
                        INNER JOIN wp_resorts b ON a.resortId=b.ResortID
                        WHERE b.gpxRegionID=%s
                        AND (WeekType='ExchangeWeek')
                        AND STR_TO_DATE(checkIn, '%%d %%M %%Y') BETWEEN %s AND %s
                        AND a.active=1", [$gpxRegion->id,$monthstart,$monthend]);
                    echo '<pre>'.print_r($sql, true).'</pre>';
                    $rows = $wpdb->get_results($sql);
                    foreach($rows as $row)
                    {
                        $removed[$row->pid] = $row->pid;
                        $allByWeekID[$row->weekId][$row->WeekType] = $row;

                    }
                }
            }
        }

        if(!empty($rentals))
        {
            echo '<pre>'.print_r($rentals, true).'</pre>';
            foreach($rentals as $rental)
            {

                $out = array();
                $out = (array) $rental;


                if(isset($quick) && array_key_exists($out2->WeekType, $allByWeekID[$out2->weekId]))
                {
                    if($out['WeekPrice'] == $allByWeekID[$out['weekId']][$out['WeekType']]->WeekPrice)
                    {
                        unset($removed[$allByWeekID[$out['weekId']][$out['WeekType']]->pid]);
                        echo '<pre>'.print_r("Skipped: ".$out2->weekId, true).'</pre>';
                        continue;
                    }
                }


                if(array_key_exists('ReturnCode', $out))
                    continue;
                    $gpxRegion='';
                    $plr = '';
                    $regionsTableRegion = '';


                    $wkv = array();
                    $wheres = $wpdb->prepare("weekId=%s AND WeekType=%s", [$out['weekId'],$out['WeekType']]);
                    $sql = "SELECT id FROM wp_properties WHERE ".$wheres;
                    $roi = $wpdb->get_row($sql);

                    echo '<pre>'.print_r($sql, true).'</pre>';
                    echo '<pre>'.print_r($roi, true).'</pre>';

                    $out['active'] = 1;

                    $out['resortJoinID'] = 0;
                    //get the resort id so that we can enter it...
                    $sql = $wpdb->prepare("SELECT id FROM wp_resorts WHERE ResortID=%s", $out['resortId']);
                    $row = $wpdb->get_row($sql);
                    if(!empty($row))
                    {
                        $qout['resortJoinID'] = $row->id;
                    }
                    else //we need to add
                    {
                        $newResort = $this->missingDAEGetResortProfile($qout['resortId'], $qout['WeekEndpointID']);
                        echo '<pre>'.print_r("new resort: ".$newResort, true).'</pre>';
                        $qout['resortJoinID'] = $newResort['id'];
                    }

                    if(!empty($roi))//this record already exist we are going to update it
                    {
                        $wpdb->update('wp_properties', $out, array('id'=>$roi->id));
                        unset($removed[$roi->id]);
                        if(isset($dbActiveRefresh))
                        {
                            $propertiesAdded[$roi->id] = $roi->id;
                        }
                    }
                        else //we need to add this record
                            $wpdb->insert('wp_properties', $out);
                            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
            }
        }
        if(isset($propertiesAdded))
        {
            $toReturn['weeks_added'] = $propertiesAdded;
        }
        if(!empty($removed))
        {
            echo '<pre>'.print_r("removed", true).'</pre>';
            echo '<pre>'.print_r($removed, true).'</pre>';
            $wpdb->insert('wp_refresh_removed', array('removed'=>json_encode($removed), 'type'=>'exchange'));
            foreach($removed as $remove)
            {
                $wpdb->update('wp_properties', array('active'=>0), array('id'=>$remove));
            }
        }
        $toReturn['success'] = true;
        echo '<pre>'.print_r('this return: ', true).'</pre>';
        echo '<pre>'.print_r($toReturn, true).'</pre>';
        return $toReturn;
    }


    /**
     * @deprecated
     */
    function returnDAEGetMemberDetails($DAEMemberNo)
    {
        $data = array(
            'functionName'=>'DAEGetMemberDetails',
            'inputMembers'=>array(
                'DAEMemberNo'=>$DAEMemberNo,
            ),
            'return'=>'MemberDetails'
        );
        $user =  [];
        return $user;
    }




    function DAEGetMemberDetails($DAEMemberNo, $userID='', $post='', $password=''): array
    {
       global $wpdb;
       if(isset($DAEMemberNo) && !empty($DAEMemberNo) && strtolower($DAEMemberNo) != 'null')
       {

       $json = json_encode($user);  // @phpstan-ignore-line
       $decode = json_decode($json);
       $details = $decode[0];
       }

       if(isset($details) && $details->ReturnCode == '0')
       {
           $mainData = array('ID'=>$userID,
                'first_name'=>$details->FirstName1,
               'last_name'=>$details->LastName1,
           );

           unset($details->ReturnCode);
           unset($details->ReturnMessage);
           /*
            *
            * Add members to the system?
            */
           if(empty($details->Email))
               $details->Email = $email;   // @phpstan-ignore-line

           $details->first_name = $details->FirstName1;
           $details->last_name = $details->LastName1;
           if(!isset($userID) && !empty($userID))
                $user_id = $userID;
           else
           {
               $user_id = username_exists( 'U'.$DAEMemberNo );

               if ( !$user_id and email_exists($details->Email) == false )
               {
                   if(empty($details->Email))
                   {
                       $details->Email = 'gpx-reports'.$DAEMemberNo.'@gpresorts.com';
                   }

                   $user_id = wp_create_user('U'.$DAEMemberNo, $password, $details->Email);
               }
               elseif(username_exists( 'U'.$DAEMemberNo ))
                   $user_id = username_exists( 'U'.$DAEMemberNo );
           }
           if(isset($post['RMN']) && !empty($post['RMN']))
           {
               $owt[$post['RMN']] = $post['OwnershipWeekType'];
               $details->OwnershipWeekType = json_encode($owt);
           }

           foreach($details as $key=>$value)
           {
                update_user_meta($user_id, $key, $value);
           }


           //add the DAEMemberNo
           update_user_meta($user_id, 'DAEMemberNo', $DAEMemberNo);

           $userdata = array(
             'ID'=>$user_id,
               'role'=>'gpx_member'
           );

           wp_update_user($userdata);
           $data = array('success'=>true);
       }
       else
       {
           $data = array('error'=>'EMS Error!');
       }
       return $data;
    }







    function DAECreateMemeber($memberDetails)
    {
        $data = array(
            'functionName'=>'DAECreateMember',
            'inputMembers'=>array(
                'AccountTypeID'=>0,
                'MemberDetails'=>$memberDetails,
                'SendEmail'=>True,
                'OfficeCode'=>'AU',
            ),
            'return'=>'GeneralList'
        );

        $types = [];

        return $types;
    }
    function DAEGetMemberDetailsForUpdate($DAEMemberNo)
    {
        $data = array(
            'functionName'=>'DAEGetMemberDetails',
            'inputMembers'=>array(
                'DAEMemberNo'=>$DAEMemberNo,
            ),
            'return'=>'MemberDetails'
        );
        $user =  [];

        $output = json_decode(json_encode($user));

        return $output;
    }

    function DAEUpdateMemberDetails($DAEMemeberNo, $post)
    {
        $post['MemberNo'] = $DAEMemeberNo;

        $currentData = $this->DAEGetMemberDetailsForUpdate($DAEMemeberNo);
        $switchnames = array('first_name'=>'FirstName1', 'last_name'=>'LastName1', 'fax'=>'Fax');

        foreach($switchnames as $key=>$value)
        {
            if(isset($post[$key]))
            {
                $post[$value] = $post[$key];
                unset($post[$key]);
            }
        }
        unset($post['updateProfile']);
        unset($post['profileSubmit']);
        unset($post['user_email']);
        unset($post['OwnershipWeekType']);
        unset($post['cid']);
        $required = array(
            'MemberNo',
            'Address1',
            'Address3',
            'Address4',
            'Address5',
            'Email',
            'Email2',
            'Title1',
            'Title2',
            'FirstName1',
            'LastName1',
            'PostCode',
            'Salutation',
            'Mobile',
        );
        if(isset($post['DayPhone']))
        {
            $updateData['HomePhone'] = $post['DayPhone'];
        }
        elseif(isset($updateData))
        {
            $updateData['DayPhone'] = $updateData['HomePhone']; // @phpstan-ignore-line
        }
        foreach($required as $require)
        {
            if(isset($post[$require]))
            {
                $updateData[$require] = str_replace(' &', ', ', $post[$require]);
                unset($post[$require]);
            }
            elseif(isset($currentData[0]->$require))
            $updateData[$require] = str_replace(' &', ', ', $currentData[0]->$require);
            else
                $updateData[$require] = '';
        }
        foreach($post as $key=>$value)
        {
            $updateData[$key] = str_replace(' &', ', ', $value);
        }
        if(!isset($updateData['AccountName']) || empty($updateData['AccountName']))
        {
            if(isset($updateData['FirstName1']) && isset($updateData['LastName1']))
            {
                $updateData['AccountName'] = $updateData['FirstName1']." ".$updateData['LastName1'];
            }
            else
            {
                $updateData['AccountName'] = $currentData[0]->FirstName1." ".$currentData[0]->LastName1;
            }
        }
        if(!isset($updateData['NewsletterStatus']) || empty($updateData['NewsletterStatus']))
        {
            $updateData['NewsletterStatus'] = $currentData[0]->NewsletterStatus;
        }
        if(!isset($updateData['SMSStatus']) || empty($updateData['SMSStatus']))
        {
            $updateData['SMSStatus'] = $currentData[0]->SMSStatus;
        }
        if(!isset($updateData['ReferalID']) || empty($updateData['ReferalID']))
        {
            $updateData['ReferalID'] = $currentData[0]->ReferalID;
        }

        if(!isset($updateData['MailOut']) || empty($updateData['MailOut']))
        {
            $updateData['MailOut'] = $currentData[0]->MailOut;
        }
        if(!isset($updateData['MailName']) || empty($updateData['MailName']))
        {
            if(isset($updateData['FirstName1']) && isset($updateData['LastName1']))
            {
                $updateData['MailName'] = $updateData['FirstName1']." ".$updateData['LastName1'];
            }
            else
            {
                $updateData['MailName'] = $currentData[0]->FirstName1." ".$currentData[0]->LastName1;
            }
        }
        foreach($this->expectedMemberDetails as $emk=>$emyn)
        {
            $toUpdate[$emk] = $updateData[$emk];

        }
        $data = array(
            'functionName'=>'DAEUpdateMemberDetails',
            'externalObject' => 'MemberDetails',
            'inputMembers'=>$toUpdate,
            'return'=>'MemberDetails'
        );
        $user =  [];
        $user = json_decode(json_encode($user[0]));
        if($user->ReturnCode == '0')
        {
            $class = 'success';
        }
        else
        {
            $class = 'warning';
        }
        $html = '<span class="label label-'.$class.'">'.$user->ReturnMessage.'</span>';

        return $html;
    }

    function DAEHoldWeek($pid, $cid, $emsid="", $bookingrequest="")
    {
        global $wpdb;


        $releasetime = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

        $gpxapi = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
        //make sure that this owner can make a hold request
        if(empty($emsid))
        {
            $emsid = gpx_get_member_number($cid);
        }
        $holds = $gpxapi->DAEGetWeeksOnHold($emsid);
        $credits = $gpxapi->DAEGetMemberCredits($emsid);

        $holdcount = 0;
        if(isset($holds[0]))
        {
            $holdcount = count($holds);
        }
        elseif(isset($holds['country']))
        {
            //dae just returns an associative array when there is only one
            $holdcount = 1;
        }

        $sql = $wpdb->prepare("SELECT id, release_on FROM wp_gpxPreHold
                        WHERE user=%s AND propertyID=%s AND released=0", [$cid,$pid]);
        $row = $wpdb->get_row($sql);

        //return true if credits+1 is greater than holds
        if($credits[0]+1 > $holdcount)
        {
           //we're good we can continue holding this
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
                    return $output;
                }
            }
        }

        if(!empty($bookingrequest))
        {
            //is this a new hold request
            $releasetime = date('Y-m-d H:i:s', strtotime('+'.get_option('gpx_hold_limt_time').' hours'));
            if(!empty($row))
            {
                if($row->release_on > $releasetime)
                    if(strtotime($releasetime) > strtotime($row->release_on))
                    {
                        $releasetime = $row->release_on;
                    }
                $wpdb->delete('wp_gpxPreHold', array('id'=>$row->id));
            }
        }

        $sql = $wpdb->prepare("SELECT WeekType, WeekEndpointID, weekId, WeekType FROM wp_properties WHERE id=%s", $pid);
        $row = $wpdb->get_row($sql);


        //As discussed on yesterday's call and again internally here amongst ourselves
        // we think the best number to show in the 'Exchange Summary' slot on the member dashboard
        //  be a formula that takes the total non-pending deposits and subtract out the Exchange weeks booked.
        //This will bypass the erroneous number being sent by DAE and not confuse the owner.


        $credit = $this->DAEGetMemberCredits($emsid);

        if(!isset($emsid))
        {
            $msg = "Please login to continue;";
            $output = array('error'=>'memberno', 'msg'=>$msg);
            return $output;
        }

        elseif($row->WeekType == 'ExchangeWeek' && (isset($credit) && !empty($credit) && $credit <= -1))

        {
            $msg = 'You have already booked an exchange with a negative deposit.  All deposits must be processed prior to completing this booking.  Please wait 48-72 hours for our system to verify the transactions.';
        }
        else
        {
            // @TODO old custom request form
            // uses pid so it might work differently
            $msg = 'This property is no longer available! <a href="#" class="dgt-btn active book-btn custom-request" data-pid="'.$pid.'" data-cid="'.$cid.'">Submit Custom Request</a>';

            $daeMemberNumber = preg_replace("/[^0-9]/","",$emsid);

            $data = array(
                'functionName'=>'DAEHoldWeek',
                'inputMembers'=>array(
                    'WeekEndpointID'=>$row->WeekEndpointID,
                    'WeekID'=>$row->weekId,
                    'WeekType'=>$row->WeekType,
                    'DAEMemberNo'=>$daeMemberNumber,
                ),

                'return'=>'GeneeralList'
            );

            $hold = [];
            $data = json_decode(json_encode($hold[0]));
            if($data->ReturnMessage == 'Success')
            {
                $msg = $data->ReturnMessage;
                $wpdb->update('wp_properties', array('active'=>'0'), array('weekId'=>$row->weekId));
            }
        }
        $output = array('msg'=>$msg, 'release'=>$releasetime);

        return $output;
    }

    function retreive_map_dae_to_vest()
    {

        $mapPropertiesToRooms = [
            'id' => 'record_id',
            'checkIn'=>'check_in_date',
            'checkOut'=>'check_out_date',
            'Price'=>'price',
            'weekID'=>'record_id',
            'weekId'=>'record_id',
            'StockDisplay'=>'availability',
            'resort_confirmation_number' => 'resort_confirmation_number',
            'source_partner_id' => 'source_partner_id',
            'WeekType' => 'type',
            'noNights' => 'DATEDIFF(check_out_date, check_in_date)',
            'active' => 'active',
            'source_num' => 'source_num',
        ];
        $mapPropertiesToUnit = [
            'bedrooms' => 'number_of_bedrooms',
            'sleeps' => 'sleeps_total',
            'Size' => 'name',
        ];
        $mapPropertiesToResort = [
            'country'=>'Country',
            'region'=>'Region',
            'locality'=>'Town',
            'resortName'=>'ResortName',
        ];
        $mapPropertiesToResort = [
            'Country'=>'Country',
            'Region'=>'Region',
            'Town'=>'Town',
            'ResortName'=>'ResortName',
            'ImagePath1'=>'ImagePath1',
            'AlertNote'=>'AlertNote',
            'AdditionalInfo'=>'AdditionalInfo',
            'HTMLAlertNotes'=>'HTMLAlertNotes',
            'ResortID'=>'ResortID',
            'gprID'=>'gpxRegionID'

        ];
        $mapRoomToPartner = [
            ''
        ];


        $output['roomTable'] = [
            'alias'=>'a',
            'table'=>'wp_room',
        ];
        $output['unitTable'] = [
            'alias'=>'c',
            'table'=>'wp_unit_type',
        ];
        $output['resortTable'] = [
            'alias'=>'b',
            'table'=>'wp_resorts',
        ];
        foreach($mapPropertiesToRooms as $key=>$value)
        {
            if($key == 'noNights')
            {
                $output['joinRoom'][] = $value.' as '.$key;
            }
            else
            {
                $output['joinRoom'][] = $output['roomTable']['alias'].'.'.$value.' as '.$key;
            }
        }
        foreach($mapPropertiesToUnit as $key=>$value)
        {
            $output['joinUnit'][] =$output['unitTable']['alias'].'.'. $value.' as '.$key;
        }
        foreach($mapPropertiesToResort as $key=>$value)
        {
            $output['joinResort'][] = $output['resortTable']['alias'].'.'.$value.' as '.$key;
        }
        return $output;
    }

    function DAEGetWeeksOnHold($cid)
    {

        global $wpdb;

        $joinedTbl = $this->retreive_map_dae_to_vest();


        $sql = $wpdb->prepare("SELECT
                h.weekType,
                h.id as holdid,
                h.release_on,
                h.released,
                ".implode(', ', $joinedTbl['joinRoom']).",
                ".implode(', ', $joinedTbl['joinResort']).",
                ".implode(', ', $joinedTbl['joinUnit']).",
                ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                    FROM wp_gpxPreHold h
                        INNER JOIN ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".record_id=h.propertyID
                        INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias'].".id
                        INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                            WHERE h.user=%s
                            AND h.released=0", $cid);
        $holdDetails = $wpdb->get_results($sql);

        return $holdDetails;
    }

    function DAEReleaseWeek($inputMembers)
    {
        global $wpdb;

        $wpdb->update('wp_gpxPreHold', array('released'=>1), array('weekId'=>$inputMembers['WeekID']));

    }

    function DAECompleteBooking($post)
    {
        global $wpdb;

        $sf = Salesforce::getInstance();

        $sql = $wpdb->prepare("SELECT DISTINCT propertyID, data FROM wp_cart WHERE cartID=%s", $post['cartID']);
        $carts = $wpdb->get_results($sql);

        foreach($carts as $cart)
        {
            $cartData = json_decode($cart->data);

            $sql = $wpdb->prepare("SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId=%s AND cancelled IS NULL", $cartData->propertyID);
            $trow = $wpdb->get_var($sql);

            if($trow > 0)
            {
                $wpdb->update('wp_room', array('active'=>'0'), array('record_id'=>$cartData->propertyID));
                $output = [
                    'error'=>'This week is no longer available.'
                ];
                return $output;
            }

            $upgradeFee = '';
            if(isset($post['UpgradeFee']))
                $upgradeFee = $post['UpgradeFee'];

                $CPOFee = '';
                $CPO = "NotApplicable";
                $CPODAE = "NotApplicable";
                if( (isset($cartData->CPOPrice) && $cartData->CPOPrice > 0) || ($post['CPO'][$cartData->propertyID] && ($post['CPO'][$cartData->propertyID] == 'NotTaken' || $post['CPO'][$cartData->propertyID] == 'Taken')) )
                {
                    $CPO = "NotTaken";
                    $CPODAE = $post['CPO'][$cartData->propertyID];

                    if(isset($cartData->CPOPrice) && $cartData->CPOPrice > 0)
                    {
                        $CPOFee = $cartData->CPOPrice;
                        $CPO = 'Taken';
                    }
                    else
                    {
                        if(isset($post['CPOFee']))
                        {
                            $CPOFee = $post['CPOFee'][$cartData->propertyID];
                        }
                        if(isset($post['CPO']))
                        {
                            $CPO = $post['CPO'];
                            $CPODAE = $post['CPO'][$cartData->propertyID];
                        }
                    }
                }
                $joinedTbl = $this->retreive_map_dae_to_vest();

 /*
  * @TODO replace the SQL
  * SELECT
                a.record_id as id, a.check_in_date as checkIn, a.check_out_date as checkOut, a.price as Price, a.record_id as weekID, a.record_id as weekId, a.availability as StockDisplay, a.resort_confirmation_number as resort_confirmation_number, a.source_partner_id as source_partner_id, a.type as WeekType, DATEDIFF(check_out_date, check_in_date) as noNights, a.active as active, a.source_num as source_num,
                b.Country as Country, b.Region as Region, b.Town as Town, b.ResortName as ResortName, b.ImagePath1 as ImagePath1, b.AlertNote as AlertNote, b.AdditionalInfo as AdditionalInfo, b.HTMLAlertNotes as HTMLAlertNotes, b.ResortID as ResortID, b.gpxRegionID as gprID,
                c.number_of_bedrooms as bedrooms, c.sleeps_total as sleeps, c.name as Size,
                a.record_id as PID, b.id as RID
                            FROM wp_room a
                        INNER JOIN wp_resorts b ON a.resort=b .id
                        INNER JOIN wp_unit_type c ON a.unit_type=c.record_id
                            WHERE a.record_id='xxxxx'
  * */





                $sql = $wpdb->prepare("SELECT
                ".implode(', ', $joinedTbl['joinRoom']).",
                ".implode(', ', $joinedTbl['joinResort']).",
                ".implode(', ', $joinedTbl['joinUnit']).",
                ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                        INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                        INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                            WHERE a.record_id=%s", $cartData->propertyID);
                $prop = $wpdb->get_row($sql);
                $prop->WeekType = $cartData->weekType;

                $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cartData->user ) );

                $userType = 'Owner';
                $loggedinuser =  get_current_user_id();
                if($loggedinuser != $cartData->user)
                    $userType = 'Agent';

                    if(isset($cartData->user_email))
                        $email = $cartData->user_email;
                        elseif(isset($cartData->Email))
                        $email = $cartData->Email;
                        elseif(isset($usermeta->Email))
                        $email = $usermeta->Email;
                        elseif(isset($use))


                        $mobile = 'NA';
                        if(isset($cartData->mobile) && !empty($cartData->Mobile))
                            $mobile = $cartData->Mobile;
                            $adults = '';
                            $children = '';
                            if(isset($cartData->adults))
                                $adults = $cartData->adults;
                                if(isset($cartData->children))
                                    $children = $cartData->children;

                                    $creditweekID = '0';
                                    if(isset($cartData->creditweekid) && $cartData->creditweekid != 'deposit')
                                        $creditweekID = $cartData->creditweekid;

                                        $daeMemberNumber = preg_replace("/[^0-9]/","",$usermeta->DAEMemberNo);


                                        $sProps = get_property_details_checkout($cartData->propertyID, $cartData->user);

                                        if(isset($_POST['taxes']))
                                        {
                                            $sProps['taxes'][$cartData->propertyID] = $_POST['taxes'];
                                        }

                                        $data = array(
                                            'functionName'=>'DAECompleteBooking',
                                            'externalObject' => 'Booking',
                                            'inputMembers'=>array(
                                                'CreditWeekID'=>$creditweekID,
                                                'WeekEndpointID'=>$prop->WeekEndpointID,
                                                'WeekID'=>$prop->weekId,
                                                'GuestFirstName'=>str_replace(' &', ', ', $cartData->FirstName1),
                                                'GuestLastName'=>str_replace(' &', ', ', $cartData->LastName1),
                                                'GuestEmailAddress'=>$email,
                                                'Adults'=>$adults,
                                                'Children'=>$children,
                                                'WeekType'=>$prop->WeekType,
                                                'CPO'=>$CPODAE,
                                                'AmountPaid'=>str_replace(",", "", $post['paid']),
                                                'DAEMemberNo'=>$usermeta->DAEMemberNo,
                                                'ResortID'=>$prop->resortId,
                                                'CurrencyCode'=>$prop->Currency,
                                                'GuestAddress'=>str_replace(' &', ', ', $cartData->Address1),
                                                'GuestTown'=>$cartData->Address3,
                                                'GuestState'=>$cartData->Address4,
                                                'GuestPostCode'=>$cartData->PostCode,
                                                'GuestPhone'=>$cartData->HomePhone,
                                                //'GuestMobile'=>$mobile,
                                                'GuestCountry'=>$cartData->Address5,
                                            ),
                                            //                 'ExtMemberNo'=>true,
                                            'return'=>'BookingReceipt'
                                        );

                                        //save the results to gpxMemberSearch database
                                        $sql = $wpdb->prepare("SELECT * FROM wp_gpxMemberSearch WHERE sessionID=%s", $usermeta->searchSessionID);
                                        $sessionRow = $wpdb->get_row($sql);
                                        if(isset($sessionRow))
                                            $sessionMeta = json_decode($sessionRow->data);
                                            else
                                                $sessionMeta = new stdClass();

                                                $data['inputMembers']['paid'] = $post['paid'];
                                                $data['inputMembers']['user_agent'] = $userType;
                                                $metaKey = 'bookattempt-'.$prop->id;

                                                $sessionMeta->$metaKey = $data['inputMembers'];
                                                $sessionMetaJson = json_encode($sessionMeta);

                                                unset($data['inputMembers']['user_agent']);
                                                $searchCartID = '';
                                                if(isset($_COOKIE['gpx-cart']))
                                                    $searchCartID = $_COOKIE['gpx-cart'];
                                                    if(isset($sessionRow))
                                                        $wpdb->update('wp_gpxMemberSearch', array('userID'=>$cartData->user, 'sessionID'=>$usermeta->searchSessionID, 'cartID'=>$searchCartID, 'data'=>$sessionMetaJson), array('id'=>$sessionRow->id));
                                                        else
                                                            $wpdb->insert('wp_gpxMemberSearch', array('userID'=>$cartData->user, 'sessionID'=>$usermeta->searchSessionID, 'cartID'=>$searchCartID, 'data'=>$sessionMetaJson));

                                                            unset($data['inputMembers']['paid']);

                                                            foreach($this->expectedBookingDetails as $ebd)
                                                            {
                                                                $daeSend['Booking'][$ebd] = $data[$ebd];
                                                            }


                                                            $mtstart = $this->microtime_float();
                                                            $mtend = $this->microtime_float();
                                                            $seconds = $mtend - $mtstart;
                                                            $data['inputMembers']['paid'] = $post['paid'];

                                                            $sfCPO = '';
                                                            if((isset($CPO) && $CPO == 'Taken') || $CPOFee > 0)
                                                                $sfCPO = 1;

                                                                $discount = str_replace(",", "", $post['pp'][$cart->propertyID]);


                                                                if(isset($CPOFee) && $CPOFee > 0)
                                                                    $discount = $discount - $CPOFee;
                                                                    if(isset($upgradeFee) && $upgradeFee > 0)
                                                                        $discount = $discount - $upgradeFee;

                                                                        $discount = $post['fullPrice'][$cart->propertyID] - $discount;

                                                                        $resonType = str_replace("Week", "", $prop->WeekType);

                                                                        $checkInDate = date("m/d/Y", strtotime($prop->checkIn));

                                                                        $sfdata = array(
                                                                            'orgid'=>'00D40000000MzoY',
                                                                            'recordType'=>'01240000000QMdz',
                                                                            'origin'=>'Web',
                                                                            'reason'=>'GPX: Exchange Request',
                                                                            'status'=>'Open',
                                                                            'priority'=>'Standard',
                                                                            'subject'=>'New GPX Exchange Request Submission',
                                                                            'description'=>'Please validate request and complete exchange workflow in SPI and EMS',
                                                                            '00N40000002yyD8'=>$cartData->HomePhone, //home phone
                                                                            '00N40000002yyDD'=>$cartData->Mobile, //cell phone
                                                                            '00N40000003S0Qr'=>$usermeta->DAEMemberNo, //EMS Account No
                                                                            '00N40000003S0Qv'=>$prop->weekId, //EMS Ref ID

                                                                            '00N40000003S0Qt'=>$cartData->FirstName1, //Guest First Name
                                                                            '00N40000003S0Qu'=>$cartData->LastName1, //Guest Last Name
                                                                            '00N40000003S0Qs'=>$email, //Guest Email
                                                                            '00N40000003S0Qw'=>$prop->resortName, //Resort
                                                                            '00N40000003S0Qp'=>$checkInDate, //Check-in Date
                                                                            '00N40000003S0Qq'=>date('m/d/Y', strtotime('+'.$prop->noNights.' days', strtotime($checkInDate))), //Check-out Date
                                                                            //'00N40000002zOQB'=>$prop->noNights." nights", //Number of nights
                                                                            '00N40000003S0Qx'=>$prop->bedrooms, //Unit Type
                                                                            '00N40000003S0Qy'=>$prop->WeekType, //Week Type
                                                                            '00N40000003DG56'=>$adults, //Adults
                                                                            '00N40000003DG57'=>$children, //Children
                                                                            '00N40000003DG51'=>$cartData->SpecialRequest, //Special Request
                                                                            '00N40000003DG4v'=>$sfCPO, //CPO
                                                                            '00N40000003DG5A'=>$$upgradeFee, //Upgrade Fee
                                                                            '00N40000003DG4z'=>$post['fullPrice'][$cart->propertyID], //Full Price
                                                                            '00N40000003DG4y'=>$discount, //Discount Price
                                                                            '00N40000003DG52'=>$post['pp'][$cart->propertyID], //Total Price
                                                                        );

                                                                        //if exchange without banked week
                                                                        if($prop->WeekType == 'ExchangeWeek' || $prop->WeekType == 'Exchange Week')
                                                                        {
                                                                            if($creditweekID == 0 || $creditweekID == 'undefined')
                                                                            {
                                                                                $sfdata['00N40000003DG53'] = 1;
                                                                            }
                                                                            else
                                                                            {
                                                                                $depositID = $creditweekID;
                                                                            }

                                                                            if($creditweekID == 0 || $creditweekID == 'undefined')
                                                                            {
                                                                                $sfdata['00N40000003DG53'] = 1;
                                                                            }
                                                                            if(isset($cartData->deposit) && !empty($cartData->deposit))
                                                                            {
                                                                                $sql = $wpdb->prepare("SELECT data FROM wp_gpxDepostOnExchange WHERE id=%s", $cartData->deposit);
                                                                                $dRow = $wpdb->get_row($sql);
                                                                                $deposit = json_decode($dRow->data);
                                                                                $depositpost = (array) $deposit;
                                                                                $depositID = $depositpost['GPX_Deposit_ID__c'];

                                                                                 $sfDepositData = [
                                                                                    'Check_In_Date__c'=>date('Y-m-d', strtotime($depositpost['Check_In_Date__c'])),
                                                                                    'Deposit_Year__c'=>date('Y', strtotime($depositpost['Check_In_Date__c'])),
                                                                                    'Account_Name__c'=>$depositpost['Account_Name__c'],
                                                                                    'GPX_Member__c'=>$cartData->user,
                                                                                    'Deposit_Date__c'=>date('Y-m-d'),
                                                                                    'Resort__c'=>$depositpost['GPX_Resort__c'],
                                                                                    'Resort_Name__c'=>$depositpost['Resort_Name__c'],
                                                                                    'Resort_Unit_Week__c'=>$depositpost['Resort_Unit_Week__c'],
                                                                                    'GPX_Deposit_ID__c'=>$depositpost['GPX_Deposit_ID__c'],
                                                                                    'Unit_Type__c'=>$depositpost['Room_Type__c'],
                                                                                    'Member_Email__c'=>$usermeta->Email,
                                                                                    'Member_First_Name__c'=>$usermeta->FirstName1,
                                                                                    'Member_Last_Name__c'=>$usermeta->LastName1,
                                                                                ];


                                                                                //does this have a coupon and is it a 2for1
                                                                                $coupArr = (array) $cartData->coupon;
                                                                                $thisCoupon = $coupArr[0];

                                                                                $sql = $wpdb->prepare("SELECT id, Name, PromoType FROM wp_specials WHERE id=%s", $thisCoupon);
                                                                                $istwofer = $wpdb->get_row($sql);
                                                                                if($istwofer->PromoType == '2 for 1 Deposit')
                                                                                {
                                                                                    $sfDepositData['Coupon__c'] == $istwofer->Name." (".$istwofer->id.")";
                                                                                }

                                                                                $sfType = 'GPX_Deposit__c';
                                                                                $sfObject = 'GPX_Deposit_ID__c';

                                                                                $sfFields = [];
                                                                                $sfFields[0] = new SObject();
                                                                                $sfFields[0]->fields = $sfDepositData;
                                                                                $sfFields[0]->type = $sfType;

                                                                                $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);

                                                                                $bankar = $bank;
                                                                                foreach($bank[0] as $b)
                                                                                {
                                                                                    $bankid = $b;
                                                                                }

                                                                                $sfdata['reason'] = 'GPX: Deposit & Exchange';
                                                                                $sfdata['subject'] = 'New GPX Deposit & Exchange Request Submission';
                                                                                $sfdata['description'] = 'Please validate request and complete deposit/exchange workflow in SPI and Salesforce';

                                                                                $sfdata['00N40000002yyD8'] = $usermeta->HomePhone; //home phone
                                                                                $sfdata['00N40000002yyDD'] = $usermeta->Mobile; //cell phone
                                                                                $sfdata['00N40000003S0Qh'] = $usermeta->DAEMemberNo; //EMS Account No
                                                                                $sfdata['00N40000003S0Qi'] = $deposit->Contract_ID__c; //EMS Ref ID
                                                                                $sfdata['00N40000003S0Qj'] = $usermeta->Email; //Email
                                                                                $sfdata['00N40000003S0Qm'] = $deposit->GPX_Resort__c; //Resort
                                                                                $sfdata['00N40000002yqhF'] = $deposit->Resort_Unit_Week__c; //Unit Week
                                                                                $sfdata['00N40000003S0Qg'] = date("m/d/Y", strtotime($deposit->Check_In_Date__c)); //Check-in Date
                                                                                $sfdata['00N40000003S0Qo'] = $deposit->Week_Type; //Week Type
                                                                                $sfdata['00N40000003S0Qk'] = $usermeta->FirstName1; //Guest First Name
                                                                                $sfdata['00N40000003S0Ql'] = $usermeta->LastName1; //Guest Last Name
                                                                            }

                                                                            //add the credit used
                                                                            $sql = $wpdb->prepare("UPDATE wp_credit SET credit_used = credit_used + 1 WHERE id=%s", $depositID);
                                                                            $wpdb->query($sql);

                                                                            $sql = $wpdb->prepare("SELECT credit_used FROM wp_credit WHERE id=%s", $depositID);
                                                                            $creditsUsed = $wpdb->get_var($sql);

                                                                            //credits_used cannot be less than 1
                                                                            if($creditsUsed < 1)
                                                                            {
                                                                                $wpdb->update('wp_credit', array('credit_used'=>1), array('id'=>$depositID));
                                                                                $creditsUsed = 1;
                                                                            }

                                                                            //update credit in sf
                                                                            $sfCreditData['GPX_Deposit_ID__c'] = $depositID;
                                                                            $sfCreditData['Credits_Used__c'] = $creditsUsed;

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

                                                                        if($CPO == 'NotTaken')
                                                                            $CPO = '';

                                                                            if(empty($usermeta->DAEMemberNo))
                                                                            {
                                                                                $usermeta->DAEMemberNo =  $_POST['user'];
                                                                                $usermeta->FirstName1 = $cartData->FirstName1;
                                                                                $usermeta->LastName1 = $cartData->LastName1;
                                                                            }

                                                                            $tsData = array(
                                                                                'MemberNumber'=>$usermeta->DAEMemberNo,
                                                                                'MemberName'=>$usermeta->FirstName1." ".$usermeta->LastName1,
                                                                                'GuestName'=>$cartData->FirstName1." ".$cartData->LastName1,
                                                                                'Adults'=>$adults,
                                                                                'Children'=>$children,
                                                                                'UpgradeFee'=>$upgradeFee,
                                                                                'CPO'=>$CPODAE,
                                                                                'CPOFee'=>$CPOFee,
                                                                                'Paid'=>$post['pp'][$cartData->propertyID],
                                                                                'WeekType'=>ucfirst(str_replace("week", "", strtolower($prop->WeekType))),
                                                                                'resortName'=>$prop->ResortName,
                                                                                'WeekPrice'=>$sProps['propWeekPrice'],
                                                                                'Balance'=>$post['balance'],
                                                                                'ResortID'=>$prop->ResortID,
                                                                                'sleeps'=>$prop->sleeps,
                                                                                'bedrooms'=>$prop->bedrooms,
                                                                                'Size'=>$prop->Size,
                                                                                'noNights'=>$prop->noNights,
                                                                                'checkIn'=>$prop->checkIn,
                                                                                'processedBy'=>get_current_user_id(),
                                                                                'specialRequest'=>$cartData->SpecialRequest,
                                                                                'actWeekPrice'=> $sProps['actIndPrice'][$prop->weekId]['WeekPrice'],
                                                                                'actcpoFee'=> $sProps['actIndPrice'][$prop->weekId]['cpoFee'],
                                                                                'actextensionFee'=> $sProps['actIndPrice'][$prop->weekId]['extensionFee'],
                                                                                'actguestFee'=> $sProps['actIndPrice'][$prop->weekId]['guestFee'],
                                                                                'actupgradeFee'=> $sProps['actIndPrice'][$prop->weekId]['upgradeFee'],
                                                                                'acttax'=> $sProps['actIndPrice'][$prop->weekId]['tax'],
                                                                            );
                                                                            if(isset($_POST['WeekPrice']))
                                                                            {
                                                                                $tsData['actWeekPrice'] = $_POST['WeekPrice'];
                                                                            }
                                                                            if(isset($_POST['taxes']))
                                                                            {
                                                                                $tsData['acttax'] = $_POST['taxes']['taxAmount'];
                                                                            }
                                                                            if((isset($cartData->type) && $cartData->type > 'extension') || isset($cartData->creditextensionfee))
                                                                            {
                                                                                $tsData['actextensionFee'] = $cartData->fee;
                                                                                $tsData['creditextensionfee'] = $cartData->fee;
                                                                            }
                                                                            if(isset($cartData->late_deposit_fee) && $cartData->late_deposit_fee > 0)
                                                                            {
                                                                                $tsData['lateDepositFee'] = $cartData->late_deposit_fee;
                                                                                $tsData['actlatedepositFee'] = $cartData->late_deposit_fee;
                                                                            }
                                                                            if(isset($cartData->promoName) && !empty($cartData->promoName))
                                                                            {
                                                                                $tsData['promoName'] = $cartData->promoName;
                                                                            }
                                                                            if(isset($cartData->discount) && !empty($cartData->discount))
                                                                            {
                                                                                $tsData['discount'] = str_replace(",", "", $cartData->discount);
                                                                            }
                                                                            if(isset($cartData->creditweekid) && !empty($cartData->creditweekid))
                                                                            {
                                                                                if($cartData->creditweekid == 'deposit')
                                                                                {
                                                                                    $cartData->creditweekid = $depositpost['GPX_Deposit_ID__c'];
                                                                                }
                                                                                $tsData['creditweekid'] = $cartData->creditweekid;
                                                                            }

                                                            if(isset($cartData->coupon) && !empty($cartData->coupon))
                                                                $tsData['coupon'] = $cartData->coupon;
                                                                if(isset($post['couponDiscount']) && !empty($post['couponDiscount']))
                                                                {
                                                                    $tsData['couponDiscount'] = $post['couponDiscount'];

                                                                }

                                                                    if(isset($cartData->GuestFeeAmount) && $cartData->GuestFeeAmount == 1)
                                                                    {
                                                                        if(isset($sProps['indGuestFeeAmount'][$cartData->propertyID]) && !empty($sProps['indGuestFeeAmount'][$cartData->propertyID]))
                                                                        {
                                                                            $tsData['GuestFeeAmount'] = $sProps['indGuestFeeAmount'][$cartData->propertyID];
                                                                        }
                                                                    }
                                                                    if(isset($sProps['taxes'][$cartData->propertyID]) && !empty($sProps['taxes'][$cartData->propertyID]))
                                                                    {
                                                                        $tsData['taxCharged'] = $sProps['taxes'][$cartData->propertyID]['taxAmount'];
                                                                        $tsData['acttax'] = $tsData['taxCharged'];
                                                                    }

                                                                    if(isset($sProps['occForActivity'][$cartData->propertyID]) && isset($_POST['ownerCreditCoupon']))
                                                                    {
                                                                        foreach($sProps['occForActivity'][$cartData->propertyID] as $occK=>$occV)
                                                                        {
                                                                            $occAmt[] = $occV;
                                                                            $occID[] = $occK;
                                                                            $occActivities[] = [
                                                                                'couponID'=>$occK,
                                                                                'activity'=>'transaction',
                                                                                'amount'=>$occV,
                                                                                'userID'=>$cartData->user,
                                                                            ];
                                                                        }

                                                                        $tsData['ownerCreditCouponID'] = implode(",", $occID);
                                                                        $tsData['ownerCreditCouponAmount'] = array_sum($occAmt);
                                                                            }
                                                                    if(empty($prop->resortId))
                                                                    {
                                                                        $prop->resortId = $prop->RID;
                                                                    }
                                                                    $ts = array(
                                                                        'cartID'=>$post['cartID'],
                                                                        'transactionType'=>'booking',
                                                                        'userID'=>$cartData->user,
                                                                        'resortID'=>$prop->ResortID,
                                                                        'weekId'=>$prop->weekId,
                                                                        'check_in_date'=>$prop->checkIn,
                                                                        'depositID'=>$cartData->deposit,
                                                                        'paymentGatewayID'=>'',
                                                                        'data'=>json_encode($tsData),
                                                                        'transactionData'=>json_encode($tsData),
                                                                        'returnTime'=>$seconds,
                                                                    );

                                                                    if(isset($depositID) && !empty($depositID))
                                                                    {
                                                                        $ts['depositID'] = $depositID;
                                                                    }

                                                                    $wpdb->insert('wp_gpxTransactions', $ts);
                                                                    $tranactionID = $wpdb->insert_id;

                                                                    $wpdb->update('wp_room', array('active'=>0), array('record_id'=>$prop->weekId));


                                                                    //is this a trade partner
                                                                    $tpSQL = $wpdb->prepare("SELECT record_id, debit_id, debit_balance FROM wp_partner WHERE user_id=%s", $cartData->user);
                                                                    $tp = $wpdb->get_row($tpSQL);

                                                                    if(!empty($tp))
                                                                    {
                                                                        //debit the partner
                                                                        $debit = $ts;
                                                                        unset($debit['data']);
                                                                        $tpDebit = $tsData['Paid'];
                                                                        $debit['transactionID'] = $tranactionID;
                                                                        $pdb = [
                                                                            'user'=>$cartData->user,
                                                                            'data'=>json_encode($debit),
                                                                            'amount'=>$tpDebit,
                                                                        ];

                                                                        $wpdb->insert('wp_partner_debit_balance', $pdb);

                                                                        $debitID = json_decode($tp->debit_id, true);
                                                                        $debitID[] = $wpdb->insert_id;

                                                                        $debitBalance = $tp->debit_balance + $tsData['Paid'];

                                                                        $debited = [
                                                                            'debit_id' => json_encode($debitID),
                                                                            'debit_balance' => $debitBalance,
                                                                        ];
                                                                        $wpdb->update('wp_partner', $debited, array('record_id'=>$tp->record_id));
                                                                    }

                                                                    $wpdb->update('wp_gpxDepostOnExchange', array('transactionID'=>$tranactionID), array('id'=>$cartData->deposit));

                                                                    //send to SF
                                                                    $sftid = $this->transactiontosf($tranactionID);

                                                                    //if owner credit coupon used then add transaction detail to database
                                                                    if(isset($sProps['indCartOCCreditUsed'][$cartData->propertyID]) && isset($_POST['ownerCreditCoupon']))
                                                                    {
                                                                        foreach($occActivities as $occActivity)
                                                                        {
                                                                            $occActivity['xref'] = $tranactionID;
                                                                            $wpdb->insert('wp_gpxOwnerCreditCoupon_activity', $occActivity);
                                                                        }
                                                                    }
                                                                    //if auto coupon was used let's set that now
                                                                    if(isset($sProps['couponTemplate']) && !empty($sProps['couponTemplate']))
                                                                    {
                                                                        $sql = "SELECT id FROM wp_gpxAutoCoupon ORDER BY id desc";
                                                                        $nc = $wpdb->get_row($sql);
                                                                        $nextNum = 0;
                                                                        if(!empty($nc))
                                                                            $nextNum = $nc->id;
                                                                            //userid + last 5 alpha numberic characters of a hash of the next ID.
                                                                            $couponHash = $cartData->user.substr(preg_replace("/[^a-zA-Z0-9]+/", "", wp_hash_password($nextNum+1)), -5);

                                                                            $ac = array(
                                                                                'user_id'=>$cartData->user,
                                                                                'transaction_id'=>$tranactionID,
                                                                                'coupon_id'=>$sProps['couponTemplate'],
                                                                                'coupon_hash'=>$couponHash,
                                                                            );
                                                                            $wpdb->insert('wp_gpxAutoCoupon', $ac);
                                                                    }
                                                                    if(isset($sProps['taxes'][$cartData->propertyID]) && !empty($sProps['taxes'][$cartData->propertyID]))
                                                                    {
                                                                        $wp_gpxTaxAudit = array(
                                                                            'transactionDate' => date('Y-m-d h:i:s'),
                                                                            'emsID' => $usermeta->DAEMemberNo,
                                                                            'resortID' => $prop->resortId,
                                                                            'arrivalDate' => date('Y-m-d', strtotime($prop->checkIn)),
                                                                            'unitType' => $prop->WeekType,
                                                                            'transactionType' => 'DAECompleteBooking',
                                                                            'baseAmount' => $totalPerPrice,
                                                                            'taxAmount' => $sProps['taxes'][$cartData->propertyID]['taxAmount'],
                                                                            'gpxTaxID' => $sProps['taxes'][$cartData->propertyID]['taxID']);
                                                                        $wpdb->insert('wp_gpxTaxAudit', $wp_gpxTaxAudit);
                                                                    }

                                                                    //update the coresponding custom request (if applicable)
                                                                    $sql = $wpdb->prepare("SELECT id FROM wp_gpxCustomRequest WHERE emsID=%s AND matched LIKE %s", [$usermeta->DAEMemberNo, '%'.$wpdb->esc_like($prop->id).'%']);
                                                                    $results = $wpdb->get_results($sql);
                                                                    if(!empty($results))
                                                                    {
                                                                        foreach($results as $result)
                                                                        {
                                                                            $wpdb->update('wp_gpxCustomRequest', array('matchConverted'=>$tranactionID), array('id'=>$result->id));
                                                                        }
                                                                    }

                                                                    if(isset($cartData->coupon) && !empty($cartData->coupon))
                                                                    {
                                                                        foreach($cartData->coupon as $coupon)
                                                                        {
                                                                            $sql = $wpdb->prepare("UPDATE wp_specials SET redeemed=redeemed + 1 WHERE id=%s", $coupon);
                                                                            $wpdb->query($sql);

                                                                            $wpdb->insert('wp_redeemedCoupons', array('userID'=>$cartData->user, 'specialID'=>$coupon));


                                                                            //auto coupon used
                                                                            if(isset($cartData->acHash) && !empty($cartData->acHash))
                                                                            {
                                                                                $wpdb->update('wp_gpxAutoCoupon', array('used'=>1), array('user_id'=>$cartData->user, 'coupon_hash'=>$cartData->acHash));
                                                                            }
                                                                        }
                                                                    }

        }

        $output['ReturnCode'] = 'A';

        return $output;

    }

    function DAEPayAndCompleteBooking($post)
    {
        global $wpdb;

        $sf = Salesforce::getInstance();

        $bookingDisabledActive = get_option('gpx_booking_disabled_active');
        if($bookingDisabledActive == '1') // this is disabled then don't do anything else
        {
            if(is_user_logged_in())
            {
                $bdUser = wp_get_current_user();
                $role = (array) $bdUser->roles;
                if($role[0] == 'gpx_member')
                {
                    $bookingDisabledMessage = get_option('gpx_booking_disabled_msg');
                    $output = array('ReturnMessage'=>$bookingDisabledMessage);

                    return $output;
                }
            }
        }

        $sql = $wpdb->prepare("SELECT DISTINCT propertyID, data FROM wp_cart WHERE cartID=%s", $post['cartID']);
        $carts = $wpdb->get_results($sql);
        foreach($carts as $cart)
        {
            $cartData = json_decode($cart->data);

            $sql = $wpdb->prepare("SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId=%s AND cancelled IS NULL", $cartData->propertyID);
            $trow = $wpdb->get_var($sql);

            if($trow > 0)
            {
                $wpdb->update('wp_room', array('active'=>'0'), array('record_id'=>$cartData->propertyID));
                $output = [
                    'error'=>'This week is no longer available.'
                ];
                return $output;
            }

            $upgradeFee = '';
            if(isset($post['UpgradeFee']))
                $upgradeFee = $post['UpgradeFee'];

                $CPOFee = '';
                $CPO = "NotApplicable";
                $CPODAE = "NotApplicable";
                if( (isset($cartData->CPOPrice) && $cartData->CPOPrice > 0) || ($post['CPO'][$cartData->propertyID] && ($post['CPO'][$cartData->propertyID] == 'NotTaken' || $post['CPO'][$cartData->propertyID] == 'Taken')) )
                {
                    $CPO = "NotTaken";
                    $CPODAE = $post['CPO'][$cartData->propertyID];

                    if(isset($cartData->CPOPrice) && $cartData->CPOPrice > 0)
                    {
                        $CPOFee = $cartData->CPOPrice;
                        $CPO = 'Taken';
                    }
                    else
                    {
                        if(isset($post['CPOFee']))
                        {
                            $CPOFee = $post['CPOFee'][$cartData->propertyID];
                        }
                        if(isset($post['CPO']))
                        {
                            $CPO = $post['CPO'];
                            $CPODAE = $post['CPO'][$cartData->propertyID];
                        }
                    }
                }

                $joinedTbl = $this->retreive_map_dae_to_vest();

                $sql = $wpdb->prepare("SELECT
                ".implode(', ', $joinedTbl['joinRoom']).",
                ".implode(', ', $joinedTbl['joinResort']).",
                ".implode(', ', $joinedTbl['joinUnit']).",
                ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                        INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                        INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                            WHERE a.record_id=%s", $cartData->propertyID);
                $prop = $wpdb->get_row($sql);
                $prop->WeekType = $cartData->weekType;
                $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cartData->user ) );

                $userType = 'Owner';
                $loggedinuser =  get_current_user_id();
                if($loggedinuser != $cartData->user)
                    $userType = 'Agent';

                    if(isset($cartData->user_email))
                    {
                        $email = $cartData->user_email;
                    }
                    elseif(isset($cartData->Email))
                    {
                        $email = $cartData->Email;
                    }
                    elseif(isset($usermeta->Email))
                    {
                        $email = $usermeta->Email;
                    }
                    elseif(isset($usermeta->email))
                    {
                        $email = $usermeta->email;
                    }



                        $mobile = 'NA';
                        if(isset($cartData->mobile) && !empty($cartData->Mobile))
                            $mobile = $cartData->Mobile;
                            $adults = '';
                            $children = '';
                            if(isset($cartData->adults))
                                $adults = $cartData->adults;
                                if(isset($cartData->children))
                                    $children = $cartData->children;

                                    $creditweekID = '0';
                                    if(isset($cartData->creditweekid) && $cartData->creditweekid != 'undefined' && $cartData->creditweekid != 'deposit') {
	                                    $creditweekID = $cartData->creditweekid;
                                    }

                                        $daeMemberNumber = preg_replace("/[^0-9]/","",$usermeta->DAEMemberNo);

                                        $currencyCode = 'USD';
                                        if(isset($prop->Currency) && !empty($prop->Currency)) {
	                                        $currencyCode = $prop->Currency;
                                        }elseif(isset($prop->WeekPrice) && !empty($prop->WeekPrice)) {
                                            $expPrice = explode(' ', $prop->WeekPrice);
                                            if(strlen($expPrice[0]) == 3)
                                                $currencyCode = $expPrice[0];
                                        }
                                            $sProps = get_property_details_checkout($cartData->user, $cartData->propertyID, $cartData->user, $cartData->user);
                                            //charge the full amount but only charge it once
                                            if(!isset($charged))
                                            {

                                                $shift4 = new Shiftfour($this->uri, $this->dir);

                                                if(is_array($_REQUEST['paymentID']))
                                                {
                                                    $paymentID = $_REQUEST['paymentID'][0];
                                                }
                                                else
                                                {
                                                    $paymentID = $_REQUEST['paymentID'];
                                                }
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

                                                $fullPriceForPayment = number_format(array_sum($sProps['indPrice']), 2, '.', '');
                                                $totalTaxCharged = '0';

                                                if(isset($sProps['taxes']) && !empty($sProps['taxes']))
                                                {
                                                    foreach($sProps['taxes'] as $paymentTax)
                                                    {
                                                        $totalTaxCharged += $paymentTax['taxAmount'];
                                                    }
                                                }
                                                $paymentRef = $_REQUEST['paymentID'];


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

                                                $output['ReturnCode'] = $paymentDetailsArr['result'][0]['transaction']['responseCode'];
                                                $output['PaymentReg'] = ltrim($paymentDetailsArr['result'][0]['transaction']['invoice'], '0');

                                                $charged = true;
    }

    $totalPerPrice = number_format($sProps['indPrice'][$cartData->propertyID], 2, '.', '');

    $Address = $post['billing_address'].", ".$post['billing_city'].", ".$post['billing_state'].", ".$post['billing_country'];


    $ims = array(
        'Payment'=>array(
            'DAEMemberNo'=>$usermeta->DAEMemberNo,
            'Address'=>$Address,
            'PostCode'=>$post['billing_zip'],
            'Country'=>$post['biling_country'],
            'Email'=>$post['billing_email'],
            'CardHolder'=>str_replace(' &', ', ', $post['billing_cardholder']),
            'CardNo'=>$post['billing_number'],
            'CCV'=>$post['billing_ccv'],
            'ExpiryMonth'=>$post['billing_month'],
            'ExpiryYear'=>$post['billing_year'],
            'PaymentAmount'=>$totalPerPrice,
            'CurrencyCode'=>$currencyCode,
        ),
        'Booking'=>array(
            'CreditWeekID'=>$creditweekID,
            'WeekEndpointID'=>$prop->WeekEndpointID,
            'WeekID'=>$prop->weekId,
            'GuestFirstName'=>str_replace(" &", ",", $cartData->FirstName1),
            'GuestLastName'=>str_replace(' &', ',', $cartData->LastName1),
            'GuestEmailAddress'=>$email,
            'Adults'=>$adults,
            'Children'=>$children,
            'WeekType'=>$prop->WeekType,
            'CPO'=>$CPODAE,
            'AmountPaid'=>str_replace(",", "", $totalPerPrice),
            'DAEMemberNo'=>$usermeta->DAEMemberNo,
            'ResortID'=>$prop->ResortID,
            'CurrencyCode'=>$prop->Currency,
            'GuestAddress'=>$cartData->Address1,
            'GuestTown'=>$cartData->Address3,
            'GuestState'=>$cartData->Address4,
            'GuestPostCode'=>$cartData->PostCode,
            'GuestPhone'=>$cartData->HomePhone,
            'GuestCountry'=>$cartData->Address5,
        ),
    );

    //save the results to gpxMemberSearch database
    $sql = $wpdb->prepare("SELECT * FROM wp_gpxMemberSearch WHERE sessionID=%s", $usermeta->searchSessionID);
    $sessionRow = $wpdb->get_row($sql);
    if(isset($sessionRow))
        $sessionMeta = json_decode($sessionRow->data);
        else
            $sessionMeta = new stdClass();

            $data['inputMembers']['paid'] = $post['paid'];
            $data['inputMembers']['user_agent'] = $userType;
            $metaKey = 'bookattempt-'.$prop->id;
            $truncateCC = substr($data['inputMembers']['Payment']['CardNo'], -4);
            $dbIM = $data['inputMembers'];
            $dbIM['Payment']['CardNo'] = $truncateCC;
            $sessionMeta->$metaKey = $dbIM;

            $sessionMetaJson = json_encode($sessionMeta);
            unset($data['inputMembers']['user_agent']);

            $searchCartID = '';
            if(isset($_COOKIE['gpx-cart']))
                $searchCartID = $_COOKIE['gpx-cart'];
                if(isset($sessionRow))
                    $wpdb->update('wp_gpxMemberSearch', array('userID'=>$cartData->user, 'sessionID'=>$usermeta->searchSessionID, 'cartID'=>$searchCartID, 'data'=>$sessionMetaJson), array('id'=>$sessionRow->id));
                    else
                        $wpdb->insert('wp_gpxMemberSearch', array('userID'=>$cartData->user, 'sessionID'=>$usermeta->searchSessionID, 'cartID'=>$searchCartID, 'data'=>$sessionMetaJson));

                        unset($data['inputMembers']['paid']);

                        foreach($this->expectedPaymentDetails as $epd)
                        {
                            $daeSend['Payment'][$epd] = $data[$epd];
                        }
                        foreach($this->expectedBookingDetails as $ebd)
                        {
                            $daeSend['Booking'][$ebd] = $data[$ebd];
                        }

                        $mtstart = $this->microtime_float();

                        $seconds = $mtend - $mtstart;

                        $data['inputMembers']['paid'] = $post['paid'];

                        //save the transaction
                        $bookingSuccessCodes = array(
                            '0',
                            '105',
                            '106',
                            'A',
                            'a',
                        );
                        //             $bookingErrorCodes = array(
                        //                 '-8',
                        //                 '-9',
                        //                 '100',
                        //                 '101',
                        //                 '102',
                        //                 '103',
                        //                 '104',
                        //                 '107',
                        //             );
                        if(isset($output['ReturnCode']) && in_array($output['ReturnCode'], $bookingSuccessCodes))
                        {
                            //release the hold
                            $this-> DAEReleaseWeek(array('WeekID'=>$prop->weekId));

                            $sfCPO = '';
                            if((isset($CPODAE) && $CPODAE == 'Taken') || $CPOFee > 0)
                                $sfCPO = 1;

                                $discount = $post['pp'][$cart->propertyID];


                                if(isset($CPOFee) && $CPOFee > 0)
                                    $discount = $discount - $CPOFee;
                                    if(isset($upgradeFee) && $upgradeFee > 0)
                                        $discount = $discount - $upgradeFee;

                                        $discount = $post['fullPrice'][$cart->propertyID] - $discount;

                                        $resonType = str_replace("Week", "", $prop->WeekType);

                                        $checkInDate = date("m/d/Y", strtotime($prop->checkIn));


                                        $sfdata = array(
                                            'orgid'=>'00D40000000MzoY',
                                            'recordType'=>'01240000000QMdz',
                                            'origin'=>'Web',
                                            'reason'=>'GPX: Exchange Request',
                                            'status'=>'Open',
                                            'priority'=>'Standard',
                                            'subject'=>'New GPX Exchange Request Submission',
                                            'description'=>'Please validate request and complete exchange workflow in SPI and EMS',
                                            '00N40000002yyD8'=>$cartData->HomePhone, //home phone
                                            '00N40000002yyDD'=>$cartData->Mobile, //cell phone
                                            '00N40000003S0Qr'=>$usermeta->DAEMemberNo, //EMS Account No
                                            '00N40000003S0Qv'=>$prop->weekId, //EMS Ref ID
                                            '00N40000003S0Qt'=>$cartData->FirstName1, //Guest First Name
                                            '00N40000003S0Qu'=>$cartData->LastName1, //Guest Last Name
                                            '00N40000003S0Qs'=>$email, //Guest Email
                                            '00N40000003S0Qw'=>$prop->resortName, //Resort
                                            '00N40000003S0Qp'=>$checkInDate, //Check-in Date
                                            '00N40000003S0Qq'=>date('m/d/Y', strtotime('+'.$prop->noNights.' days', strtotime($checkInDate))), //Check-out Date

                                            '00N40000003S0Qx'=>$prop->bedrooms, //Unit Type
                                            '00N40000003S0Qy'=>$prop->WeekType, //Week Type
                                            '00N40000003DG56'=>$adults, //Adults
                                            '00N40000003DG57'=>$children, //Children
                                            '00N40000003DG51'=>$cartData->SpecialRequest, //Special Request
                                            '00N40000003DG4v'=>$sfCPO, //CPO
                                            '00N40000003DG5A'=>$upgradeFee, //Upgrade Fee
                                            '00N40000003DG4z'=>$post['fullPrice'][$cart->propertyID], //Full Price
                                            '00N40000003DG4y'=>$discount, //Discount Price
                                            '00N40000003DG52'=>$post['pp'][$cart->propertyID], //Total Price
                                        );

                                        //if extension fee
                                        if($cartData->creditextensionfee && $cartData->creditextensionfee > 0)
                                        {


                                        }
                                        //if exchange without banked week
                                        if($prop->WeekType == 'ExchangeWeek' || $prop->WeekType == 'Exchange Week')
                                        {

                                            if($creditweekID == 0 || $creditweekID == 'undefined')
                                            {
                                                $sfdata['00N40000003DG53'] = 1;
                                            }
                                            else
                                            {
                                                $depositID = $creditweekID;
                                            }


                                                if($creditweekID == 0 || $creditweekID == 'undefined')
                                                {
                                                    $sfdata['00N40000003DG53'] = 1;
                                                }
                                                    if(isset($cartData->deposit) && !empty($cartData->deposit))
                                                    {
                                                        $sql = $wpdb->prepare("SELECT data FROM wp_gpxDepostOnExchange WHERE id=%s", $cartData->deposit);
                                                        $dRow = $wpdb->get_row($sql);
                                                        $deposit = json_decode($dRow->data);
                                                        $depositpost = (array) $deposit;
                                                        $depositID = $depositpost['GPX_Deposit_ID__c'];

                                                        $sfDepositData = [
                                                        //    'Account_Name__c'=>$depositpost['GPX_Member__c'],
                                                            'Check_In_Date__c'=>date('Y-m-d', strtotime($depositpost['Check_In_Date__c'])),
                                                            'Deposit_Year__c'=>date('Y', strtotime($depositpost['Check_In_Date__c'])),
                                                            'Account_Name__c'=>$depositpost['Account_Name__c'],
                                                            'GPX_Member__c'=>$cartData->user,
                                                            'Deposit_Date__c'=>date('Y-m-d'),
                                                            'Resort__c'=>$depositpost['GPX_Resort__c'],
                                                            'Resort_Name__c'=>$depositpost['Resort_Name__c'],
                                                            'Resort_Unit_Week__c'=>$depositpost['Resort_Unit_Week__c'],
                                                            'GPX_Deposit_ID__c'=>$depositpost['GPX_Deposit_ID__c'],
                                                            'Unit_Type__c'=>$depositpost['Room_Type__c'],
                                                            'Member_Email__c'=>$usermeta->Email,
                                                            'Member_First_Name__c'=>$usermeta->FirstName1,
                                                            'Member_Last_Name__c'=>$usermeta->LastName1,
                                                        ];

                                                            $coupArr = (array) $cartData->coupon;

                                                            $thisCoupon = $coupArr[0];


                                                        $sql = $wpdb->prepare("SELECT id, Name, PromoType FROM wp_specials WHERE id=%s", $thisCoupon);
                                                        $istwofer = $wpdb->get_row($sql);
                                                        if($istwofer->PromoType == '2 for 1 Deposit')
                                                        {
                                                            $sfDepositData['Coupon__c'] == $istwofer->Name." (".$istwofer->id.")";
                                                        }

                                                        $sfType = 'GPX_Deposit__c';
                                                        $sfObject = 'GPX_Deposit_ID__c';

                                                        $sfFields = [];
                                                        $sfFields[0] = new SObject();
                                                        $sfFields[0]->fields = $sfDepositData;
                                                        $sfFields[0]->type = $sfType;

                                                        $sfDepositAdd = $sf->gpxUpsert($sfObject, $sfFields);

                                                        $bankar = $bank;
                                                        foreach($bank[0] as $b)
                                                        {
                                                            $bankid = $b;
                                                        }

                                                        $sfdata['reason'] = 'GPX: Deposit & Exchange';
                                                        $sfdata['subject'] = 'New GPX Deposit & Exchange Request Submission';
                                                        $sfdata['description'] = 'Please validate request and complete deposit/exchange workflow in SPI and Salesforce';

                                                        $sfdata['00N40000002yyD8'] = $usermeta->HomePhone; //home phone
                                                        $sfdata['00N40000002yyDD'] = $usermeta->Mobile; //cell phone
                                                        $sfdata['00N40000003S0Qh'] = $usermeta->DAEMemberNo; //EMS Account No
                                                        $sfdata['00N40000003S0Qi'] = $deposit->Contract_ID__c; //EMS Ref ID
                                                        $sfdata['00N40000003S0Qj'] = $usermeta->Email; //Email
                                                        $sfdata['00N40000003S0Qm'] = $deposit->GPX_Resort__c; //Resort
                                                        $sfdata['00N40000002yqhF'] = $deposit->Resort_Unit_Week__c; //Unit Week
                                                        $sfdata['00N40000003S0Qg'] = date("m/d/Y", strtotime($deposit->Check_In_Date__c)); //Check-in Date
                                                        //                                                                                     $sfdata['00N40000003S0Qn'] = $deposit->Resort_Unit_Week__c; //Unit Type
                                                        //                                                                                     $sfdata['00N40000003S0Qo'] = $deposit->Week_Type; //Week Type
                                                        $sfdata['00N40000003S0Qk'] = $usermeta->FirstName1; //Guest First Name
                                                        $sfdata['00N40000003S0Ql'] = $usermeta->LastName1; //Guest Last Name
                                                    }

                                                    //add the credit used
                                                    $sql = $wpdb->prepare("UPDATE wp_credit SET credit_used = credit_used + 1 WHERE id=%s", $depositID);
                                                    $wpdb->query($sql);

                                                    $sql = $wpdb->prepare("SELECT credit_used FROM wp_credit WHERE id=%s", $depositID);
                                                    $creditsUsed = $wpdb->get_var($sql);

                                                    //credits_used cannot be less than 1
                                                    if($creditsUsed < 1)
                                                    {
                                                        $wpdb->update('wp_credit', array('credit_used'=>1), array('id'=>$depositID));
                                                        $creditsUsed = 1;
                                                    }

                                                    //update credit in sf
                                                    $sfCreditData['GPX_Deposit_ID__c'] = $depositID;
                                                    $sfCreditData['Credits_Used__c'] = $creditsUsed;

                                                    if(isset($cartData->creditextensionfee) && $cartData->creditextensionfee > 0)
                                                    {
                                                        $sfCreditData['Credit_Extension_Date__c'] = date('Y-m-d');
                                                        $sfCreditData['Expiration_Date__c'] = date('Y-m-d', strtotime($prop->checkIn));
                                                    }

                                                    $sfWeekAdd = '';
                                                    $sfAdd = '';
                                                    $sfType = 'GPX_Deposit__c';
                                                    $sfObject = 'GPX_Deposit_ID__c';


                                                    $sfFields = [];
                                                    $sfFields[0] = new SObject();
                                                    $sfFields[0]->fields = $sfCreditData;
                                                    $sfFields[0]->type = $sfType;

                                                    $sfResortAdd = $sf->gpxUpsert($sfObject, $sfFields);

                                                    $sfLogout = $sf->gpxLogout();

                                        }


                                        if($CPO == 'NotTaken')
                                        {
                                            $CPO = '';
                                        }
                                            $tsData = array(
                                                'MemberNumber'=>$usermeta->DAEMemberNo,
                                                'MemberName'=>$usermeta->FirstName1." ".$usermeta->LastName1,
                                                'GuestName'=>$cartData->FirstName1." ".$cartData->LastName1,
                                                'Email'=>$email,
                                                'Adults'=>$adults,
                                                'Children'=>$children,
                                                'UpgradeFee'=>$upgradeFee,
                                                'CPO'=>$CPODAE,
                                                'CPOFee'=>$CPOFee,
                                                'PaymentID'=>$paymentID,
                                                'Paid'=>$post['pp'][$cartData->propertyID],
                                                'WeekType'=>ucfirst(str_replace("week", "", strtolower($prop->WeekType))),
                                                'resortName'=>$prop->ResortName,
                                                'WeekPrice'=>$sProps['propWeekPrice'],
                                                'Balance'=>$post['balance'],
                                                'ResortID'=>$prop->ResortID,
                                                'sleeps'=>$prop->sleeps,
                                                'bedrooms'=>$prop->bedrooms,
                                                'Size'=>$prop->Size,
                                                'noNights'=>$prop->noNights,
                                                'checkIn'=>$prop->checkIn,
                                                'processedBy'=>get_current_user_id(),
                                                'specialRequest'=>$cartData->SpecialRequest,
                                                'actWeekPrice'=> $sProps['actIndPrice'][$prop->weekId]['WeekPrice'],
                                                'actcpoFee'=> $sProps['actIndPrice'][$prop->weekId]['cpoFee'],
                                                'actextensionFee'=> $sProps['actIndPrice'][$prop->weekId]['extensionFee'],
                                                'actguestFee'=> $sProps['actIndPrice'][$prop->weekId]['guestFee'],
                                                'actupgradeFee'=> $sProps['actIndPrice'][$prop->weekId]['upgradeFee'],
                                                'acttax'=> $sProps['actIndPrice'][$prop->weekId]['tax'],
                                            );

                                            if(isset($creditweekID) && $creditweekID != 0)
                                            {
                                                $tdData['creditweekID'] = $creditweekID;
                                            }

                                            if(isset($cartData->late_deposit_fee) && $cartData->late_deposit_fee > 0)
                                            {
                                                $tsData['lateDepositFee'] = $cartData->late_deposit_fee;
                                                $tsData['actlatedepositFee'] = $cartData->late_deposit_fee;
                                            }

                                            if(isset($cartData->promoName) && !empty($cartData->promoName))
                                            {
                                                $tsData['promoName'] = $cartData->promoName;
                                            }
                                            if(isset($cartData->creditweekid) && !empty($cartData->creditweekid))
                                            {
                                                if($cartData->creditweekid == 'deposit')
                                                {
                                                    $cartData->creditweekid = $depositpost['GPX_Deposit_ID__c'];
                                                }
                                                $tsData['creditweekid'] = $cartData->creditweekid;
                                            }
                                                if(isset($cartData->discount) && !empty($cartData->discount))
                                                {
                                                    $tsData['discount'] = str_replace(",", "", $cartData->discount);

                                                }
                                                    if(isset($cartData->coupon) && !empty($cartData->coupon))
                                                    {
                                                        $tsData['coupon'] = $cartData->coupon;
                                                    }
                                                        if(isset($post['couponDiscount']) && !empty($post['couponDiscount']))
                                                        {
                                                            $tsData['couponDiscount'] = str_replace(",", "", $post['couponDiscount']);

                                                        }
                                                            if(isset($cartData->GuestFeeAmount) && $cartData->GuestFeeAmount == 1)
                                                            {
                                                                if(isset($sProps['discGuestFee'][$cartData->propertyID]) && !empty($sProps['discGuestFee'][$cartData->propertyID]))
                                                                {
                                                                    $tsData['GuestFeeAmount'] = $sProps['discGuestFee'][$cartData->propertyID];
                                                                }
                                                            }
                                                            if(isset($sProps['taxes'][$cartData->propertyID]) && !empty($sProps['taxes'][$cartData->propertyID]))
                                                            {
                                                                $tsData['taxCharged'] = $sProps['taxes'][$cartData->propertyID]['taxAmount'];
                                                                $tsData['acttax'] = $tsData['taxCharged'];
                                                            }

                                                            if(isset($sProps['occForActivity'][$cartData->propertyID]) && isset($_POST['ownerCreditCoupon']))
                                                            {
                                                                foreach($sProps['occForActivity'][$cartData->propertyID] as $occK=>$occV)
                                                                {
                                                                    $occAmt[] = $occV;
                                                                    $occID[] = $occK;
                                                                    $occActivities[] = [
                                                                        'couponID'=>$occK,
                                                                        'activity'=>'transaction',
                                                                        'amount'=>$occV,
                                                                        'userID'=>$cartData->user,
                                                                    ];
                                                                }

                                                                $tsData['ownerCreditCouponID'] = implode(",", $occID);
                                                                $tsData['ownerCreditCouponAmount'] = array_sum($occAmt);
                                                            }
                                                            if(isset($cartData->creditextensionfee))
                                                            {
                                                                $tsData['creditextensionfee'] = $cartData->creditextensionfee;
                                                            }

                                                            $ts = array(
                                                                'cartID'=>$post['cartID'],
                                                                'transactionType'=>'booking',
                                                                'sessionID'=>$usermeta->searchSessionID,
                                                                'userID'=>$cartData->user,
                                                                'resortID'=>$prop->ResortID,
                                                                'weekId'=>$prop->weekId,
                                                                'check_in_date'=>$prop->checkIn,
                                                                'depositID'=>$cartData->deposit,
                                                                'paymentGatewayID'=>$paymentRef,
                                                                'data'=>json_encode($tsData),
                                                                'transactionData'=>json_encode($tsData),
                                                                'returnTime'=>$seconds,
                                                            );
                                                            $wpdb->insert('wp_gpxTransactions', $ts);

                                                            $tranactionID = $wpdb->insert_id;



                                                            $wpdb->update('wp_gpxDepostOnExchange', array('transactionID'=>$tranactionID), array('id'=>$cartData->deposit));

                                                            //send to SF
                                                            $sftid = $this->transactiontosf($tranactionID);

                                                            //if owner credit coupon used then add transaction detail to database
                                                            if(isset($sProps['indCartOCCreditUsed'][$cartData->propertyID]) && isset($_POST['ownerCreditCoupon']))
                                                            {
                                                                foreach($occActivities as $occActivity)
                                                                {
                                                                    $occActivity['xref'] = $tranactionID;
                                                                    $wpdb->insert('wp_gpxOwnerCreditCoupon_activity', $occActivity);
                                                                }
                                                            }
                                                            //if auto coupon was used let's set that now
                                                            if(isset($sProps['couponTemplate']) && !empty($sProps['couponTemplate']))
                                                            {
                                                                $sql = "SELECT id FROM wp_gpxAutoCoupon ORDER BY id desc";
                                                                $nc = $wpdb->get_row($sql);
                                                                $nextNum = 0;
                                                                if(!empty($nc))
                                                                    $nextNum = $nc->id;
                                                                    //userid + last 5 alpha numberic characters of a hash of the next ID.
                                                                    $couponHash = $cartData->user.substr(preg_replace("/[^a-zA-Z0-9]+/", "", wp_hash_password($nextNum+1)), -5);

                                                                    $ac = array(
                                                                        'user_id'=>$cartData->user,
                                                                        'transaction_id'=>$tranactionID,
                                                                        'coupon_id'=>$sProps['couponTemplate'],
                                                                        'coupon_hash'=>$couponHash,
                                                                    );
                                                                    $wpdb->insert('wp_gpxAutoCoupon', $ac);
                                                            }
                                                            if(isset($sProps['taxes'][$cartData->propertyID]) && !empty($sProps['taxes'][$cartData->propertyID]))
                                                            {
                                                                $wp_gpxTaxAudit = array(
                                                                    'transactionDate' => date('Y-m-d h:i:s'),
                                                                    'emsID' => $usermeta->DAEMemberNo,
                                                                    'resortID' => $prop->ResortID,
                                                                    'arrivalDate' => date('Y-m-d', strtotime($prop->checkIn)),
                                                                    'unitType' => $prop->WeekType,
                                                                    'transactionType' => 'DAECompleteBooking',
                                                                    'baseAmount' => $totalPerPrice,
                                                                    'taxAmount' => $sProps['taxes'][$cartData->propertyID]['taxAmount'],
                                                                    'gpxTaxID' => $sProps['taxes'][$cartData->propertyID]['taxID']);
                                                                $wpdb->insert('wp_gpxTaxAudit', $wp_gpxTaxAudit);
                                                            }

                                                            //update the coresponding custom request (if applicable)
                                                            $sql = $wpdb->prepare("SELECT id FROM wp_gpxCustomRequest WHERE emsID=%s AND matched LIKE %s", [$usermeta->DAEMemberNo, '%'.$wpdb->esc_like($prop->id).'%']);
                                                            $results = $wpdb->get_results($sql);
                                                            if(!empty($results))
                                                            {
                                                                foreach($results as $result)
                                                                {
                                                                    $wpdb->update('wp_gpxCustomRequest', array('matchConverted'=>$tranactionID), array('id'=>$result->id));
                                                                }
                                                            }

                        }
                        else
                        {
                              $jsonBook = json_encode($paymentDetailsArr['result'][0]);
                              $dbbook = array(
                                    'cartID'=>$post['cartID'],
                                    'userID'=>$cartData->user,
                                    'data'=>$jsonBook,
                                    'returnTime'=>$seconds,
                                );
                                $wpdb->insert('wp_gpxFailedTransactions', $dbbook);
                                $output['ReturnMessage'] = 'Your credit card could not be processed.';
                        }
                        if(isset($cartData->coupon) && !empty($cartData->coupon))
                        {
                            foreach($cartData->coupon as $coupon)
                            {
                                $sql = "UPDATE wp_specials SET redeemed=redeemed + 1 WHERE id='".$coupon."'";
                                $wpdb->query($sql);

                                $wpdb->insert('wp_redeemedCoupons', array('userID'=>$cartData->user, 'specialID'=>$coupon));

                                //auto coupon used
                                if(isset($cartData->acHash) && !empty($cartData->acHash))
                                {
                                    $wpdb->update('wp_gpxAutoCoupon', array('used'=>1), array('user_id'=>$cartData->user, 'coupon_hash'=>$cartData->acHash));
                                }
                            }
                        }

        }

        return $output;

    }

    function DAEGetMemberCredits($DAEMemberNo, $cid='')
    {
        global $wpdb;

        $data = array(
            'functionName'=>'DAEGetMemberCredits',
            'inputMembers'=>array(
                'DAEMemberNo'=>$DAEMemberNo,
            ),
            'return'=>'GeneralResultInteger'
        );

        $retrieve = [];

        $credits = $this->xml2array($retrieve[0]);

        if(!empty($cid)) // if cid is set then we'll update the credit
            update_user_meta($cid, 'daeCredit', $credits[0]);

        return $credits;
    }

    function DAEGetMemberOwnership($DAEMemberNo)
    {
        $data = array(
            'functionName'=>'DAEGetMemberOwnership',
            'inputMembers'=>array(
                'DAEMemberNo'=>$DAEMemberNo,
            ),
            'return'=>'OwnershipDetail'
        );

        $retrieve = [];
        if(empty($retrieve[0]))
            $ownership = array('Error'=>'No Record');
        else
            foreach($retrieve as $ret)
            {
                $ownership[] = $this->xml2array($ret);
            }
        return $ownership;
    }

    function DAECreateWillBank($inputMembers)
    {
        $data = array(
            'functionName'=>'DAECreateWillBank',
            'inputMembers'=>array(
                'DAEMemberNo'=>$inputMembers['DAEMemberNo'],
                'OwnershipID'=>$inputMembers['OwnershipID'],
                'Year'=>$inputMembers['Year'],
                'CheckINDate'=>date("d/m/Y", strtotime($inputMembers['CheckINDate'])),
                'BankNBook'=>0,
            ),
            'return'=>'GeneralResultInteger'
        );

        if(isset($inputMembers['Week_Type']) && $inputMembers['Week_Type'] == 'Points')
        {
            if(isset($inputMembers['UnitTypeID']))
                $data['inputMembers']['UnitTypeID'] = 1;

            if(isset($inputMembers['Sleeps']))
                $data['inputMembers']['Sleeps'] = 1;
        }

        $retrieve = [];

        $output = json_decode(json_encode($retrieve));

        return $output;
    }

    function DAEGetMemberHistory($DAEMemberNo, $TransactionType='All')
    {

        global $wpdb;

        $joinedTbl = $this->retreive_map_dae_to_vest();

        $sql = $wpdb->prepare("SELECT
                t.data, t.datetime, t.cancelled,
                ".implode(', ', $joinedTbl['joinRoom']).",
                ".implode(', ', $joinedTbl['joinResort']).",
                ".implode(', ', $joinedTbl['joinUnit']).",
                ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                    FROM wp_gpxTransactions t
                        INNER JOIN ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".record_id=t.weekId
                        INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                        INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                            WHERE t.userID=%s", $cid);
        $rows = $wpdb->get_results($sql, ARRAY_A);

        foreach($rows as $row)
        {
            $data = json_decode($row['data']);
            $transactions[] = array_merge($row,$data);
        }

        return $transactions;

    }

    function DAEGetAccountDetails($DAEMemberNo='', $ExtMemberNo='')
    {
        return [];

    }
    function DAEGetUnitUpgradeFees($MemberTypeID, $BusCatID)
    {
        $data = array(
            'functionName'=>'DAEGetUnitUpgradeFees',
            'inputMembers'=>array(
                //'OfficeCode'=>'?',
                'MemberTypeID'=>$MemberTypeID,
                'BusCatID'=>$BusCatID,
            ),
            'return'=>'UpgradeFeeList'
        );

        $retrieve = [];
        $fees = json_decode(json_encode($retrieve[0]));

        return $fees;

    }
    function DAEGetWeekDetails($WeekID)
    {
        global $wpdb;

        $joinedTbl = $this->retreive_map_dae_to_vest();

        $sql = $wpdb->prepare("SELECT
                ".implode(', ', $joinedTbl['joinRoom']).",
                ".implode(', ', $joinedTbl['joinResort']).",
                ".implode(', ', $joinedTbl['joinUnit']).",
                ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                    FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                        INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                        INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                            WHERE ".$joinedTbl['roomTable']['alias'].".record_id=%s", $WeekID);
        $retrieve = $wpdb->get_results($sql);

        foreach($retrieve as $k=>$v)
        {
            if($v->source_partner_id > 0)
            {
                if($v->source_num == '1')
                {
                    $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $v->source_partner_id ) );
                    $retrieve[$k]->source_name = $usermeta->FirstName1." ".$usermeta->LastName1;
                    $retrieve[$k]->source_account = $usermeta->Property_Owner__c;
                }
                elseif($v->source_num == '3')
                {
                    $sql = $wpdb->prepare("SELECT name, sf_account_id FROM wp_partner WHERE user_id=%s", $v->source_partner_id);
                    $row = $wpdb->get_row($sql);
                    $retrieve[$k]->source_name = $row->name;
                    $retrieve[$k]->source_account = $row->sf_account_id;
                }
            }
        }

        return $retrieve;
    }
    function DAEReIssueConfirmation($post)
    {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT WeekEndpointID FROM wp_properties
                WHERE resortName=%s", $post['resortname']);
        $endpoint = $wpdb->get_var($sql);

        $data = array(
            'functionName'=>'DAEReIssueConfirmation',
            'inputMembers'=>array(
                'WeekID'=>$post['weekid'],
                'DAEMemberNo'=>$post['memberno'],

            ),
            'return'=>'BookingReceipt'
        );


        $retrieve = [];
        $conf = json_decode(json_encode($retrieve[0]));
        $data = array(
          'daeMemberNo'=>$post['memberno'],
            'weekId'=>$post['weekid'],
            'pdf'=>$conf->PDFConfirmation
        );
        $wpdb->insert('wp_gpxPDFConf', $data);

        $confid = $wpdb->insert_id;
        return $confid;

    }


/**
 * @deprecated
 *
 * entire function returns on 2nd line and not used.
 *
 */
    function DAEGetResortProfile($id, $gpxRegionID, $inputMembers, $update='')
    {
        global $wpdb;

        $return = array('success'=>'Resort Updated!');

        $data = array(
            'functionName'=>'DAEGetResortProfile',
            'inputMembers'=>$inputMembers,
            'return'=>'ResortProfile',
        );
        $propDetails = [];

        foreach($propDetails as $prop)
        {
            foreach((array) $prop as $ind => $no)
            {
                $keyskip = array('ReturnCode', 'ReturnMessage');
                if(in_array($ind, $keyskip))
                    continue;
                    if(is_object($no))
                    {

                        $op = json_decode(json_encode($no));
                        if(!empty($op->string))
                            $no = json_encode($op->string);
                            else
                                $no = '';
                    }

                    $output[$ind] = $no;

            }
        }

        $output['gpxRegionID'] = $gpxRegionID;
        if(empty($gpxRegionID))
        {
            $output['gpxRegionID'] = 'NA';
        }

        $sql = $wpdb->prepare("SELECT * FROM wp_resorts WHERE ResortID=%s", $output['ResortID']);
        $updateorinsert = $wpdb->get_row($sql);

        if((!empty($update) && $update == 1) || !empty($updateorinsert))
            $wpdb->update('wp_resorts', $output, array('id'=>$id));
        elseif(!empty($update) && $update == 'insert')
        {
            $wpdb->insert('wp_resorts', $output);
            $return['id'] = $wpdb->insert_id;
        }


        return $return;

    }





    function missingDAEGetResortProfile($resortID, $endpointID)
    {
        global $wpdb;

                $data = array(
            'functionName'=>'DAEGetResortProfile',
            'inputMembers'=>array('EndpointID'=>$endpointID, 'ResortID'=>$resortID),
            'return'=>'ResortProfile',
        );
        echo '<pre>'.print_r($data, true).'</pre>';
        $propDetails = [];
        echo '<pre>'.print_r($propDetails, true).'</pre>';
        foreach($propDetails as $prop)
        {
            foreach((array) $prop as $ind => $no)
            {
                $keyskip = array('ReturnCode', 'ReturnMessage');
                if(in_array($ind, $keyskip))
                    continue;
                    if(is_object($no))
                    {

                        $op = json_decode(json_encode($no));
                        if(!empty($op->string))
                            $no = json_encode($op->string);
                            else
                                $no = '';
                    }

                    $output[$ind] = $no;

            }
        }

        if(empty($output['Town']) || empty($output['Region']))
        {
            return array('successs'=>"there was an error!");
        }
        //get the region by city
        $sql = $wpdb->prepare("SELECT id FROM wp_gpxRegion WHERE name=%s", $output['Town']);
        $row = $wpdb->get_row($sql);
        if(empty($row)) // get the region by region
            $sql = $wpdb->prepare("SELECT id FROM wp_gpxRegion WHERE name=%s",$output['Region'] );
            $row = $wpdb->get_row($sql);
            if(empty($row)) // get the country by region from Region Table
                $sql = $wpdb->prepare("SELECT c.id FROM wp_daeRegion b
                        INNER JOIN wp_gpxRegion c ON b.id=c.RegionID
                    WHERE b.region=%s", $output['Country']);
                $row = $wpdb->get_row($sql);
                if(empty($row)) // get the region by region
                    $sql = $wpdb->prepare("SELECT c.id FROM wp_daeCountry a
                            INNER JOIN wp_daeRegion b ON a.CountryID=b.CountryID
                            INNER JOIN wp_gpxRegion c ON b.id=c.RegionID
                        WHERE a.country=%s
                        AND b.region='All'", $output['Country']);
                    $row = $wpdb->get_row($sql);

        $output['gpxRegionID'] = $row->id;
        if(empty($output['gpxRegionID']))
            $output['gpxRegionID'] = 'NA';
        if(!empty($output['ResortName']))
        {
            $wpdb->insert('wp_resorts', $output);
            return array('succes'=>true, 'id'=>$wpdb->insert_id);
        }
        else
        {
            return array('success'=>"there was an error!");
        }
    }
    function DAEGetResortInd()
    {
        global $wpdb;
        echo '<pre>'.print_r("More", true).'</pre>';
        $sql = "SELECT a.id as gpxRegionID, b.RegionID, b.CountryID, b.id as daeRegionID
                FROM wp_gpxRegion a INNER JOIN wp_daeRegion b ON a.RegionID = b.id
                WHERE b.resortPull=0 ";
        echo '<pre>'.print_r($sql, true).'</pre>';
                $results = $wpdb->get_results($sql);

        foreach($results as $result)
        {
            $countyrID = $result->CountryID;
            $regionID = $result->RegionID;
            $gpxRegionID = $result->gpxRegionID;
            $daeRegionID = $result->daeRegionID;

            $data = array(
                'functionName'=>'DAEGetResortList',
                'inputMembers'=>array(
                    'CountryID'=>$countyrID,
                    'RegionID'=>$regionID,
                    'ResortType'=>'ALL',
                ),
                'return'=>'ResortList'
            );
            $retrieve = [];
            echo '<pre>'.print_r($retrieve, true).'</pre>';
            $resorts = json_decode(json_encode($retrieve));

            foreach($resorts as $resort)
            {
                $data = array('ResortID'=>$resort->ResortID, 'EndpointID'=>$resort->EndpointID, 'gpxRegionID'=>$gpxRegionID);
                $wpdb->insert('wp_resortList', $data);
            }
        }
            return array('success'=>true);
    }


    /*
     *  TODO
     *
     * there is an exit statement in the middle of the method
     *
     * There is also a specfic resort ID hard coded in the select..
     *
     * This function is either not used or doesn't work
     *
     * This method makes no sense
     */

    function addResortDetails()
    {
        global $wpdb;

            $sql = "SELECT * FROM wp_resorts WHERE ResortID='R3119'";
            $resorts = $wpdb->get_results($sql);
            echo '<pre>'.print_r($sql, true).'</pre>';
            foreach($resorts as $resort)
            {
                $output = array();
                $resortID = $resort->ResortID;
                $endpointID = $resort->EndpointID;
                $gpxRegionID = $resort->gpxRegionID;
                $data2 = array(
                    'functionName'=>'DAEGetResortProfile',
                    'inputMembers'=>array(
                        'ResortID'=>$resortID,
                        'EndpointID'=>$endpointID,
                    ),
                    'return'=>'ResortProfile',
                );
                $propDetails = [];
                echo '<pre>'.print_r($propDetails, true).'</pre>';
                exit;
                foreach($propDetails as $prop)
                {
                    foreach((array) $prop as $ind => $no)
                    {
                        $keyskip = array('ReturnCode', 'ReturnMessage');
                        if(in_array($ind, $keyskip))
                            continue;
                            if(is_object($no))
                            {

                                $op = json_decode(json_encode($no));
                                if(!empty($op->string))
                                    $no = json_encode($op->string);
                                else
                                    $no = '';
                            }

                            $output[$ind] = $no;

                    }
                }
                $output['gpxRegionID'] = $gpxRegionID;
                $sql = $wpdb->prepare("SELECT * FROM wp_resorts WHERE ResortID=%s", $resortID);
                $row = $wpdb->get_row($sql);
                if(!empty($row))
                    $wpdb->update('wp_resorts', $output, array('ResortID'=>$resortID));
                elseif(!empty($output['ResortName']))

                 $wpdb->insert('wp_resorts', $output);
                $upregion = array('id'=>$resort->id);
                $wpdb->update('wp_resortList', array('added'=>1), $upregion);
            }

        $output = array('success'=>'Resort updated.');

        return $output;

    }

    function xml2array ( $xmlObject, $out = array () )
    {
        foreach ( (array) $xmlObject as $index => $node )
            $out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;

            return $out;
    }

    function microtime_float()
    {
        [$usec, $sec] = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    function transactiontosf($transactionID, $transactionType = 'transactions')
    {
        global $wpdb;

        $data = array();

        //pull from database
        $sql = $wpdb->prepare("SELECT * FROM wp_gpxTransactions WHERE id=%s", $transactionID);
        $transactionRow = $wpdb->get_row($sql);


        if(empty($transactionRow->sessionID))
        {
               $thisisimported = true;
        }
        $weekId = $transactionRow->weekId;

        $row = json_decode($transactionRow->data, true);

        if(isset($row['actextensionFee']) || isset($row['creditweekid']) || isset($row['creditid']) || !empty($transactionRow->depositID))
        {

            if(!empty($transactionRow->depositID))
            {
                $sql = $wpdb->prepare("SELECT creditID FROM wp_gpxDepostOnExchange WHERE id=%s", $transactionRow->depositID);
                $crid = $wpdb->get_var($sql);
            }
            if($row['creditweekid'])
            {
                $crid = $row['creditweekid'];
            }
            elseif($row['creditid'])
            {
                $crid = $row['creditid'];
            }
            elseif($row['actextensionFee'] && $row['id'])
            {
                $crid = $row['id'];
            }

            do_shortcode('[get_credit gpxcreditid="'.$crid.'"]');

            //get the status
            $sql = $wpdb->prepare("SELECT sf_name, record_id, status FROM wp_credit WHERE id =%s", $crid);
            $cq = $wpdb->get_row($sql);
            $transactionRow->CreditStatus = $cq->status;
            $transactionRow->CreditSFID = $cq->record_id;
            $transactionRow->CreditSFName = $cq->sf_name;
        }
        $sfRow = json_decode($transactionRow->sfData);

        //get details from the cart
        $sql = $wpdb->prepare("SELECT data FROM wp_cart WHERE cartID=%s", $transactionRow->cartID);
        $cRow = $wpdb->get_row($sql);
        $cjson = json_decode($cRow->data);

        $getWeekDetails = $this->DAEGetWeekDetails($weekId);

        $weekDetails = $getWeekDetails[0];

        $skipTrans = [
            'transactionData',
            'data',
            'sfid',
            'sfData',
            'returnTime',
            'billing_address',
            'inCard_name',
            'CVV',
            'expiry_date',
            'card_number',
            'cancelledData',
        ];
        foreach($transactionRow as $tk=>$td)
        {
            if(in_array($tk, $skipTrans))
            {
                continue;
            }
            $row[$tk] = trim($td);
        }

        $row['source_num'] = $weekDetails->source_num;
        $row['source_name'] = $weekDetails->source_name;
        $row['source_account'] = $weekDetails->source_account;

        $mappedTransTypes['actWeekPrice'] = 'Exchange';
        $mappedTransTypes['EXCH240'] = 'Exchange';
        $mappedTransTypes['EXCH241'] = 'Exchange';
        $mappedTransTypes['TRADE250'] = 'Exchange';
        $mappedTransTypes['EXCH'] = 'Exchange';
        $mappedTransTypes['TRADEINT'] = 'Exchange';
        $mappedTransTypes['GPXPPEXCH'] = 'Exchange';
        $mappedTransTypes['EXPROMO'] = 'Exchange';
        $mappedTransTypes['INTERNALI'] = 'Exchange';
        $mappedTransTypes['IEXCH'] = 'Exchange';
        $mappedTransTypes['TRADE260'] = 'Exchange';
        $mappedTransTypes['TRADE262'] = 'Exchange';
        $mappedTransTypes['INTERNALD'] = 'Exchange';
        $mappedTransTypes['EXCHPROMO'] = 'Exchange';
        $mappedTransTypes['RC_62'] = 'Exchange';
        $mappedTransTypes['TRADE261'] = 'Exchange';
        $mappedTransTypes['TRADE251'] = 'Exchange';
        $mappedTransTypes['CPO242'] = 'CPO';
        $mappedTransTypes['CPO240'] = 'CPO';
        $mappedTransTypes['CPO241'] = 'CPO';
        $mappedTransTypes['CPOINT'] = 'CPO';
        $mappedTransTypes['CPS'] = 'CPO';
        $mappedTransTypes['ICPOINT'] = 'CPO';
        $mappedTransTypes['UNITUPG'] = 'Upgrade';
        $mappedTransTypes['Unitupg24'] = 'Upgrade';
        $mappedTransTypes['UPSELLPROMO'] = 'Upgrade';
        $mappedTransTypes['EXTEN'] = 'Extension';
        $mappedTransTypes['EXTEN24'] = 'Extension';
        $mappedTransTypes['LATEDEPGPX'] = 'Late Deposit';
        $mappedTransTypes['LATEDEP'] = 'Late Deposit';
        $mappedTransTypes['BONUS'] = 'Rental';
        $mappedTransTypes['BONUS24'] = 'Rental';
        $mappedTransTypes['INTRENTAL'] = 'Rental';
        $mappedTransTypes['BONUS26'] = 'Rental';
        $mappedTransTypes['RENTAL'] = 'Rental';
        $mappedTransTypes['RENTPROMO'] = 'Rental';
        $mappedTransTypes['TAXCODEGPX'] = 'Tax';
        $mappedTransTypes['ST10'] = 'Tax';
        $mappedTransTypes['GPXTAX'] = 'Tax';
        $mappedTransTypes['CLEARDEB'] = 'Adjustments';
        $mappedTransTypes['MISCNGST'] = 'Misc';
        $mappedTransTypes['GUEST CERT'] = 'Guest Certificate';
        $mappedTransTypes['GUEST NAME CHANGE'] = 'GUEST NAME CHANGE';

        foreach($mappedTransTypes as $mttK=>$mtt)
        {
            $tts[$mtt][] = $mttK;
        }
        //these need to be included in the cancelled to confirmed conditional statement
        $includeConfirmed = [
            'Exchange',
            'Rental',
        ];
        foreach($includeConfirmed as $ic)
        {

            foreach($tts[$ic] as $tt)
            {
                $allIncludeConfirmed[] = $tt;
            }
        }
        //these need to be excluded from the function that changes the status from Canceled to Confirmed
        $excludeConfirmed = [
            'Extension',
            'Late Deposit',
            'Adjustments',
            'Misc',
        ];
        foreach($excludeConfirmed as $exc)
        {
            foreach($tts[$exc] as $tt)
            {
                $cancelledCheckExcludeConfirmed[] = $tt;
            }
        }

                $sf = Salesforce::getInstance();


                    /*
                     * the following are part of the file but not added to sf
                     * DepositingOwnerMemberID
                     * TravellingGuestContactID
                     * CreditBalance
                     */

                    //the header items that we need to pull from the csv
                    $header['transactions'] = [
                        'datetime'=>'TransactionDate',
                        'Purchase_Type__c'=>'RecordType',
                        'status'=>'RecordStatus',
                        'resortname'=>'ExchResortName',
                        'userID'=>'DepositingOwnersAccount',
                        'accounttype'=>'DepositingOwnerAccountType',
                        //                         'member_first_name'=>'TravellerFirstName',
                        //                         'member_last_name'=>'TravellerLastName',
                        'memberAccountName'=>'TravellingMemberAccountName',
                        'member_email' => 'TravellingGuestEmail',
                        'guest'=>'TravellingMemberAccountName',
                        'guestFirstName'=>'TravellerFirstName',
                        'guestLastName'=>'TravellerLastName',
                        'checkIn'=>'Arrival',
                        'checkOut'=>'CheckoutDate',
                        'Size'=>'UnitType',
                        'deposit'=>'DepositRefNo',
                        'MemberNumber'=>'TravellingMemberID',

                        'resort_reservation_number'=>'ResortReservationNo',
                        /*
                         * these need to be changed because they report one charge per line
                         *
                         */


                        /*
                         * end per line change
                         * add amount from new file
                         */
                        'PaymentID'=>'PaymentID',
                        'Paid'=>'Paid',
                        'transactionType'=>'TransactionType',
                        'adults'=>'adults',
                        'children'=>'children',
                        'specialRequest'=>'GuestComments',
                        'resort_confirmation_number'=>'Resort_Reservation__c',
                        'guestEmailAddress'=>'TravellingGuestEmail',
                        'processedBy'=>'BookedBy',
                        'WeekType'=>'RecordType',
                        /*
                         * used before but not now
                         */
                        //                         'weektype'=>'Week Type',
                        //                         'phoneCell'=>'Cell Phone',
                        //                         'phoneHome'=>'Home Phone',
                        //                         'coupon'=>'Coupon',
                    ];

                    //                     DAAuthoritydate
                    //                     LeviesCheckDate
                    //                     LeviesPaidDate
                    //                     DepositExpiryDate
                    //                     creditBalance
                    //                     Inventory Source
                    //                     AB_DepositId


                    $header['deposit'] = [
                        'datetime'=>'DepositCreatedDate',
                        'Purchase_Type__c'=>'RecordType',
                        'status'=>'RecordStatus',
                        'resortname'=>'DepositResortName',
                        'owner'=>'DepositOwnersAccount',
                        'accounttype'=>'DepositOwnerAccountType',
                        'checkIn'=>'CheckInDate',
                        'checkOut'=>'CheckOutDate',
                        'Size'=>'UnitType',
                        'deposit'=>'DepositRefNo',
                        'memberNo'=>'DepositOwnerMemberID',
                        'resortMemberNo'=>'Resort Member #',
                        'resortReservationNum'=>'ResortReservationNo',
                        'bookedBy'=>'BookedBy',
                    ];

                    //now we are sending the data to two object -- let's define them
                    $objects = [
                        'week' => [
                            //booking details
                            'of_Adults__c'=>'of_Adults__c',
                            'of_Children__c'=>'of_Children__c',
                            'Guest_First_Name__c'=>'Guest_First_Name__c',
                            'Guest_Last_Name__c'=>'Guest_Last_Name__c',
                            'Guest_Email__c'=>'Guest_Email__c',
                            'Guest_Phone__c'=>'Guest_Phone__c',
                            'Special_Requests__c'=>'Special_Requests__c',
                            //week details
                            'sourse_num'=>'Inventory_Source__c',
                            'source_account'=>'Inventory_Owned_by__c',
                            'gprID'=>'GPX_Resort__c',
                            'GpxWeekRefId__c'=>'GpxWeekRefId__c',
                            'GPX_Resort__c'=>'GPX_Resort__c',
                            'Check_in_Date__c'=>'Check_in_Date__c',
                            'Check_out_Date__c'=>'Check_out_Date__c',
                            'country'=>'Country__c',
                            'region'=>'Region__c',
                            'resortId'=>'Resort_ID__c',
                            'resort_confirmation_number'=>'Resort_Reservation__c',
                            'resrotName'=>'Resort_Name__c',
                            'StockDisplay'=>'Stock_Display__c',
                            'sleeps'=>'Unit_Sleeps__c',
                            'Unit_Type__c'=>'Unit_Type__c',
                            'weekNo'=>'Week_Number__c',
                            'WeekType'=>'Week_Type__c',
                            'TPSend'=>'TP_Guest_Assigned__c',
                            'Booked_by_TP__c'=>'Booked_by_TP__c',
                            'Flex_Booking__c'=>'Flex_Booking__c',
                        ],
                        'transaction' => [
                            // reference ID's
                            'GPX_Ref__c',
                            'GPX_Deposit__c',
                            'GPXTransaction__c',
                            'Transaction_Book_Date__c',
                            'Booked_By__c',
                            'Account_Type__c',
                            'Account_Name__c',
                            'Shift4_Invoice_ID__c',
                            // price / fees
                            'Purchase_Price__c',
                            'CPO_Fee__c',
                            'Tax_Paid__c',
                            'Upgrade_Fee__c',
                            'Guest_Fee__c',
                            'Credit_Extension_Fee__c',
                            'Late_Deposit_Fee__c',
                            'Coupon_Discount__c',
                            '',
                            // transaction details
                            'CPO_Opt_in__c',
                            'EMS_Account__c',
                            'Purchase_Type__c',
                            'Reservation_Status__c',
                            'Transaction_On_hold__c',
                            'GPX_Coupon_Code__c',
                            'GPX_Promo_Code__c',
                            //guest details
                            'Guest_Cell_Phone__c',
                            //don't fill in here either???
                            'Guest_Email__c',
                            'Guest_Cell_Phone__c',
                            'Guest_First_Name__c',
                            'Guest_Last_Name__c',
                            'Guest_Home_Phone__c',
                            'Mobile'=>'Member_Cell_Phone__c',
                            'Email'=>'Member_Email__c',
                            'HomePhone'=>'Member_Home_Phone__c',
                            'first_name'=>'Member_First_Name__c',
                            'last_name'=>'Member_Last_Name__c',
                            'Special_Requests__c',
                            'RecordTypeId',
                            'Name',
                        ]
                    ];

                    $extraTransactionTypes = [
                        'creditextension' => '0121W000000E02nQAC',
                        'guestfee' => '0121W000000E02oQAC',
                        'latedepositfee' => '0121W000000E02pQAC',
                        'upgradefee' => '0121W000000E02qQAC',
                        'booking' => '0121W0000005jWTQAY',
                        'first_name'=>'Member_First_Name__c',
                        'last_name'=>'Member_Last_Name__c',
                    ];

                    $extraTransactionsMapped = [
                        'CPO' => 'booking',
                        'Extension' => 'creditextension',
                        'Exchange' => 'booking',
                        'Guest Certificate' => 'guestfee',
                        'Late Deposit' => 'latedepositfee',
                        'Rental' => 'booking',
                        'Tax' => 'booking',
                        'Upgrade' => 'upgradefee',
                        'Adjustments' => 'booking',
                        'Misc' => 'booking',
                    ];

                    $extraTransactionTypesObjects = [

                    ];

                    $removeSpecialChar = [
                        'Special_Requests__c',
                        'Member_First_Name__c',
                        'Member_Last_Name__c',
                        'Guest_First_Name__c',
                        'Guest_Last_Name__c',
                        'Resort_Name__c',
                        'Inventory_Source__c',
                    ];


                        $sfWeekAdd = '';
                        $sfData = [];
                        $sfWeekData = [];
                        $sfTransData = [];
                        $dbTable = [];
                        $dateTime = '';
                        $paid = '';
                        $isDeposit = false;
                        $name = '';
                        $first_name = '';
                        $last_name = '';
                        $ownerDetails = '';
                        $users = '';
                        $user = '';
                        $userID = '';
                        $resort = '';
                        $resortID ='';
                        $resortCountry = '';
                        $resortRegionName = '';
                        $CPO = '';
                        $resNum = $sfData['Reservation_Reference__c'];
                        $transRow = '';
                        $dbTableToUpdate = [];
                        $neNoNights = '';
                        $neMemberName = '';
                        $neGuestName = '';
                        $neCheckIn = '';

                        $userMemberNo = $row['memberNo'];

                        $dbTable['transactionType'] = 'booking';

                        if($transactionType == 'deposit')
                        {
                            $isDeposit = true;
                            $sfData['Deposit_Status__c'] = 'Confirmed';
                            //add the deposit record type
                            $sfData['RecordTypeId'] = '0121W0000005jWY';
                            $dbTable['transactionType'] = 'deposit';
                        }
                        else
                        {
                            $sfData['RecordTypeId'] = $extraTransactionTypes[$extraTransactionsMapped[$mappedTransTypes[$row['Purchase_Type__c']]]];
                        }
                        //is this part of the extra trasactions table?
                        if(isset($sfData['RecordTypeId']) && in_array($sfData['RecordTypeId'], $extraTransactionTypes))
                        {
                            foreach($extraTransactionTypes as $ettKey=>$ett)
                            {
                                if($sfData['RecordTypeId'] == $ett)
                                {
                                    $dbTable['transactionType'] = $ettKey;
                                }
                            }
                        }
                        //get the userID
                        $userID = $row['userID'];
                        $sfData['EMS_Account__c'] = $row['userID'];
                        $weekId = $row['weekId'];
                        /*
                         * Filter the transactions based on the following
                         * If multiple line item charges exist within a single purchase of a week (Exchange Fee, Guest Fee & Upgrade), We’d like them to be on a single transaction record.
                         * Ideally, edits to costs (adjustments, refunds, etc) would be able to be tied to the day that the adjustment of refund happened, but that wasn’t doable during the v1 rollout so we settled on the values in the original transaction being edited/cleared when an adjustment (cancel and refund/modification) occurs.
                         * If an upgrade or guest fee is paid separately, after the date the exchange transaction is processed, it would create a separate transaction so that the revenue could be tied to the day the purchase was made.
                         * Credit extnesion and late deposit is always a standalone transaction
                         * Guest Fee could be any time
                         */

                                    //if this file is deposit
                                        foreach($row as $rKey=>$rValue)
                                        {
                                            // begin the apply the processing requirements
                                            if($rKey == 'datetime')
                                            {
                                                $dbData['datetime'] = date('Y-m-d 00:00:00', strtotime($rValue));
                                                $dbData['sfTransactionDate'] = date('Y-m-d', strtotime($rValue));
                                                $dateTime = date('Y-m-d 00:00:00', strtotime($rValue));
                                                $sfTransactionDate = date('Y-m-d', strtotime($rowValue));
                                                $sfData['Transaction_Book_Date__c'] = $dateTime;
                                            }
                                            if($rKey == 'transactionType')
                                            {
                                                if($rValue == 'deposit')
                                                {
                                                    $sfData['RecordTypeId'] = $extraTransactionTypes['latedepositfee'];

                                                    $amount = $row['Paid'];

                                                    $sfData['Late_Deposit_Fee__c'] = $amount;

                                                    if(isset($row['creditid']))
                                                    {
                                                        $creditID = $row['creditid'];
                                                    }
                                                    if(isset($row['creditweekid']))
                                                    {
                                                        $creditID = $row['creditweekid'];
                                                    }
                                                    if(!empty($creditID))
                                                    {
                                                        $sql = $wpdb->prepare("SELECT sf_name, record_id FROM wp_credit WHERE id=%s", $creditID);
                                                        $creditWeekID = $wpdb->get_row($sql);
                                                        $sfData['GPX_Deposit__c'] = $creditWeekID->record_id;
                                                    }

                                                }
                                                if($rValue == 'guest')
                                                {
                                                    $sfData['RecordTypeId'] = $extraTransactionTypes['guestfee'];

                                                    $amount = $row['Paid'];

                                                    $sfData['Guest_Fee__c'] = $amount;

                                                }
                                                if($rValue == 'extension')
                                                {
                                                    $sfData['RecordTypeId'] = $extraTransactionTypes['creditextension'];

                                                    $amount = $row['Paid'];

                                                    if(isset($row['creditid']))
                                                    {
                                                        $creditID = $row['creditid'];
                                                    }
                                                    if(isset($row['creditweekid']))
                                                    {
                                                        $creditID = $row['creditweekid'];
                                                    }
                                                    if(!empty($creditID))
                                                    {
                                                        $sql = $wpdb->prepare("SELECT sf_name, record_id FROM wp_credit WHERE id=%s", $creditID);
                                                        $creditWeekID = $wpdb->get_row($sql);
                                                        $sfData['GPX_Deposit__c'] = $creditWeekID->record_id;
                                                    }
                                                }
                                            }
                                            if($rKey == 'resortID')
                                            {
                                                $sql = $wpdb->prepare("SELECT ResortName, gprID, sf_GPX_Resort__c from wp_resorts WHERE ResortID=%s", $rValue);
                                                $resortRow = $wpdb->get_row($sql);
                                                if(!empty($resortRow) && (!empty($resortRow->gprID) || !empty($resortRow->sf_GPX_Resort__c)))
                                                {
                                                    $sfData['Resort_ID__c'] = $resortRow->sf_GPX_Resort__c;
                                                    $sfWeekData['Resort_ID__c'] = $resortRow->sf_GPX_Resort__c;
                                                    $sfData['GPX_Resort__c'] = substr($resortRow->sf_GPX_Resort__c, 0, 15);
                                                    $sfWeekData['GPX_Resort__c'] = substr($resortRow->sf_GPX_Resort__c, 0, 15);
                                                }
                                            }

                                            if($rKey == 'coupon')
                                            {
                                                $placeholders = gpx_db_placeholders($rValue);
                                                $sql = $wpdb->prepare("SELECT Name from wp_specials WHERE id IN ({$placeholders})", array_values($rValue));
                                                $codes = $wpdb->get_results($sql);
                                                //
                                                //add the owner credit coupons
                                                foreach($codes as $code)
                                                {
                                                    $ccs[]  = $code->Name;
                                                }
                                                $sfData['GPX_Coupon_Code__c'] = implode(",", $ccs);
                                            }

                                            if($rKey == 'ownerCreditCouponID')
                                            {
                                                $ccs[] = 'Monetary Coupon';
                                                $sfData['GPX_Coupon_Code__c'] = implode(",", $ccs);
                                            }
                                            if($rKey == 'promoName')
                                            {
                                                $sfData['GPX_Promo_Code__c'] = $rValue;
                                            }

                                            if($rKey == 'couponDiscount')
                                            {
                                                $amt[] = str_replace(",", "", str_replace('$', '', $rValue));
                                                $sfData['Coupon_Discount__c'] = array_sum($amt);
                                            }
                                            if($rKey == 'ownerCreditCouponAmount')
                                            {
                                                $amt[] = str_replace(",", "", $rValue);
                                                $sfData['Coupon_Discount__c'] = array_sum($amt);
                                            }
                                            if($rKey == 'Paid' && !$isDeposit)
                                            {
                                                $ptSet = false;
                                                $amount = $row['Paid'];
                                            }
                                            if($rKey == 'transactionType')
                                            {
                                                if($rValue == 'deposit' || $rValue == 'extension')
                                                {
                                                    if(isset($row['creditid']))
                                                    {
                                                        $creditID = $row['creditid'];
                                                    }
                                                    if(isset($row['creditweekid']))
                                                    {
                                                        $creditID = $row['creditweekid'];
                                                    }

                                                    if(!empty($creditID))
                                                    {
                                                        $sql = $wpdb->prepare("SELECT sf_name, record_id FROM wp_credit WHERE id=%s", $creditID);
                                                        $creditWeekID = $wpdb->get_row($sql);
                                                        $sfData['GPX_Deposit__c'] = $creditWeekID->record_id;
                                                    }
                                                }
                                            }

                                                if($rKey == 'actcpoFee')
                                                {
                                                        $amount = $row['actcpoFee'];
                                                        $ptSet = true;
                                                        $paid += $amount;
                                                        $sfData['CPO_Fee__c'] = $amount;
                                                }


                                                if($rKey == 'actextensionFee')
                                                {
                                                        $amount = $row['actextensionFee'];
                                                        $ptSet = true;
                                                        if($row['actWeekPrice'] > 0 || $weekId != 0)
                                                        {
                                                            //do not change the record type
                                                        }
                                                        else
                                                        {
                                                            $sfData['RecordTypeId'] = $extraTransactionTypes['creditextension'];
                                                        }
                                                        $paid += $amount;
                                                        $sfData['Credit_Extension_Fee__c'] = $amount;
                                                        $creditWeekID = $cjson->creditweekid;
                                                        $sql = $wpdb->prepare("SELECT record_id FROM wp_credit WHERE id=%s", $creditWeekID);
                                                        $dRow = $wpdb->get_row($sql);

                                                        if(isset($row['creditid']))
                                                        {
                                                            $creditID = $row['creditid'];
                                                        }
                                                        if(isset($row['creditweekid']))
                                                        {
                                                            $creditID = $row['creditweekid'];
                                                        }
                                                        if(!empty($creditID))
                                                        {
                                                            $sql = $wpdb->prepare("SELECT sf_name, record_id FROM wp_credit WHERE id=%s", $creditID);
                                                            $creditWeekID = $wpdb->get_row($sql);
                                                            $sfData['GPX_Deposit__c'] = $creditWeekID->record_id;
                                                        }

                                                }

                                                if($rKey == 'CreditSFID')
                                                {
                                                    $sfData['GPX_Deposit__c'] = $rValue;
                                                }
                                                if($rKey == 'actWeekPrice')
                                                {
                                                        $amount = $row['actWeekPrice'];
                                                        $ptSet = true;
                                                        $paid += $amount;
                                                        $sfData['Purchase_Price__c'] = $amount;
                                                        $objects['transaction'][] = 'GPX_Deposit__c';

                                                }

                                                if($rKey == 'actguestFee')
                                                {
                                                        $amount = $row['actguestFee'];
                                                        $ptSet = true;

                                                        //we can't just look at the week price we also need to see if this has a week associated with it
                                                        if($row['actWeekPrice'] > 0 || $weekId != 0)
                                                        {
                                                            //do not change the record type because this is week
                                                        }
                                                        else
                                                        {
                                                            $sfData['RecordTypeId'] = $extraTransactionTypes['guestfee'];
                                                        }
                                                        $paid += $amount;
                                                        $sfData['Guest_Fee__c'] = $amount;
                                                }

                                                if($rKey == 'actlatedepositFee')
                                                {
                                                    if(isset($cjson->type) && $cjson->type == 'late_deposit_fee')
                                                    {
                                                        $amount = $cjson->fee;
                                                    }
                                                    else
                                                    {
                                                        $amount = $row['actlatedepositFee'];
                                                    }
                                                        $ptSet = true;
                                                        if($row['actWeekPrice'] > 0 || $weekId != 0)
                                                        {
                                                            //do not change the record type
                                                        }
                                                        else
                                                        {
                                                            $sfData['RecordTypeId'] = $extraTransactionTypes['latedepositfee'];
                                                        }
                                                        $paid += $amount;
                                                        $sfData['Late_Deposit_Fee__c'] = $amount;

                                                        if(isset($row['creditid']))
                                                        {
                                                            $creditID = $row['creditid'];
                                                        }
                                                        if(isset($row['creditweekid']))
                                                        {
                                                            $creditID = $row['creditweekid'];
                                                        }
                                                        if(!empty($creditID))
                                                        {
                                                            $sql = $wpdb->prepare("SELECT sf_name, record_id FROM wp_credit WHERE id=%s", $creditID);
                                                            $creditWeekID = $wpdb->get_row($sql);
                                                            $sfData['GPX_Deposit__c'] = $creditWeekID->record_id;
                                                        }
                                                }
                                                if($rKey == 'creditweekid' || $rKey == 'creditid')
                                                {
                                                    $creditID = $rValue;

                                                    $sql = $wpdb->prepare("SELECT sf_name, record_id FROM wp_credit WHERE id=%s", $creditID);
                                                    $creditWeekID = $wpdb->get_row($sql);

                                                    $sfData['GPX_Deposit__c'] = $creditWeekID->record_id;
                                                }

                                                if($rKey == 'acttax')
                                                {
                                                        $amount = $row['acttax'];
                                                        $ptSet = true;
                                                        $paid += $amount;
                                                        $sfData['Tax_Paid__c'] = $amount;
                                                }

                                                if($rKey == 'actcpoFee')
                                                {
                                                        $amount = $row['actupgradeFee'];
                                                        $ptSet = true;
                                                        $paid += $amount;
                                                        $sfData['Upgrade_Fee__c'] = $amount;
                                                }
                                                if($rKey == 'WeekType')
                                                {
                                                    if(trim(strtolower($rValue)) == 'exchange')
                                                    {
                                                        $sfData['RecordTypeId'] = $extraTransactionTypes['booking'];
                                                        $sfData['Purchase_Type__c'] = 'Exchange';
                                                    }
                                                    elseif(trim(strtolower($rValue)) == 'rental')
                                                    {
                                                        $sfData['RecordTypeId'] = $extraTransactionTypes['booking'];
                                                        $sfData['Purchase_Type__c'] = 'Rental';
                                                    }
                                                    else
                                                    {
                                                        //this needs to be the transaaction type
                                                    }
                                                    //add additional transaction types
                                                    if(array_key_exists(strtolower(str_replace(" ", "", $rValue)), $extraTransactionTypes))
                                                    {
                                                        $sfData['RecordTypeId'] = $extraTransactionTypes[strtolower(str_replace(" ", "", $rValue))];
                                                    }
                                                }


                                            if($rKey == 'cancelled')
                                            {
                                                if($isDeposit)
                                                {
                                                    if($rValue == 'Active')
                                                    {
                                                        $rValue = 'Approved';
                                                    }
                                                    $sfData['Deposit_Status__c'] = $rValue;
                                                    //                                     $sfData['Reservation_Status__c'] = $rValue;
                                                }
                                                else
                                                {
                                                    if($rValue == '1' && (!in_array($rValue, $cancelledCheckExcludeConfirmed)))
                                                    {
                                                        //                                         $rValue = 'Confirmed';
                                                        $sfData['Reservation_Status__c'] = 'Cancelled';
                                                    }
                                                    else
                                                    {
                                                    $sfData['Reservation_Status__c'] = 'Confirmed';
                                                    if($row['WeekType'] == 'Exchange' && $row['depositID'] > 0)
                                                    {
                                                        //is this status pending?
                                                        $sfData['Reservation_Status__c'] = 'Pending Deposit';
                                                    }
                                                    }
                                                }
                                            }
                                            if($rKey == 'resortName' || $rKey == 'ResortName')
                                            {
                                                if($isDeposit)
                                                {
                                                    $sfData['Deposit_Resort_Name__c'] = $rValue;
                                                    $sfData['Resort_Name__c'] = $rValue;
                                                    $xResortName = esc_sql($rValue);
                                                }
                                                else
                                                {
                                                    $xResortName = esc_sql($rValue);
                                                    $sfData['Resort_Name__c'] = $rValue;
                                                }
                                            }


                                            if($rKey == 'userID')
                                            {
                                                //get the name
                                                $user_info = get_userdata($rValue);
                                                $first_name = $user_info->first_name;
                                                $last_name = $user_info->last_name;
                                                $email = $user_info->user_email;
                                                $Property_Owner = $user_info->Property_Owner;

                                                //explode the name
                                                $onlyOneName = explode("&", $rowValue);
                                                $splitFirstLast = explode(",", $onlyOneName);
                                                $sfData['Member_First_Name__c'] = $first_name;
                                                $sfData['Member_Last_Name__c'] = $last_name;
                                                $sfData['Member_Email__c'] = $email;
                                                $sfData['Account_Type__c'] = 'USA GPX Member';
                                                $sfData['Account_Name__c'] = $Property_Owner;

                                            }

                                            if($rKey == 'source_num')
                                            {
                                                if($rValue == '1')
                                                {
                                                    $rValue = 'Owner';
                                                }
                                                if($rValue == '2')
                                                {
                                                    $rValue = 'GPR';
                                                }
                                                if($rValue == '3')
                                                {
                                                    $rValue = 'Trade Partner';
                                                }
                                                $sfData['Inventory_Source__c'] = $rValue;
                                                if(isset($thisisimported))
                                                {
                                                    $sfData['Inventory_Source__c'] = 'GPR';
                                                }

                                            }
                                            if($rKey == 'source_account')
                                            {
                                                if($row['source_num'] == '3')
                                                {
                                                    $sfData['Inventory_Owned_by__c'] = $rValue;
                                                }
                                            }
                                            if($rKey == 'GuestName')
                                            {
                                                if(empty($cjson))
                                                {
                                                    $name = trim($rValue);
                                                    [$first_name, $last_name] = explode(' ',$name,2);
                                                    $sfData['Guest_First_Name__c'] = $first_name;
                                                    $sfData['Guest_Last_Name__c'] = $last_name;
                                                }
                                                else
                                                {

                                                    $sfData['Guest_First_Name__c'] = $cjson->FirstName1;
                                                    $sfData['Guest_Last_Name__c'] = $cjson->LastName1;
                                                    $sfData['Guest_Cell_Phone__c'] = $cjson->Mobile;
                                                    $sfData['Guest_Home_Phone__c'] = $cjson->HomePhone;
                                                    $sfData['Guest_Email__c'] = $cjson->email;
                                                    if(isset($cjson->phone))
                                                    {
                                                        $sfData['Guest_Phone__c'] = substr(preg_replace( '/[^0-9]/', '', $cjson->phone), 0, 18);
                                                    }
                                                }
                                            }

                                            if($rKey == 'PaymentID')
                                            {
                                                $sfData['Shift4_Invoice_ID__c'] = $rValue;
                                            }
                                            if($rKey == 'Adults')
                                            {
                                                $sfData['of_Adults__c'] = $rValue;
                                            }
                                            if($rKey == 'Children')
                                            {
                                                $sfData['of_Children__c'] = $rValue;
                                            }
                                            if($rKey == 'specialRequest')
                                            {
                                                $sfData['Special_Requests__c'] = $rValue;
                                            }
                                            if($rKey == 'creditweekID')
                                            {
                                                $sfData['Resort_Reservation__c'] = $rValue;
                                            }
                                            if($rKey == 'checkIn')
                                            {
                                                if($isDeposit)
                                                {
                                                    $sfData['Check_in_Date__c'] = date('Y-m-d', strtotime($rValue));
                                                    $sfData['Deposit_Check_In_Date__c'] = date('Y-m-d', strtotime($rValue));
                                                    $sfData['Deposit_Entitlement_Year__c'] = date('Y', strtotime($rValue));
                                                }
                                                else
                                                {
                                                    $sfData['Check_in_Date__c'] = date('Y-m-d', strtotime($rValue));
                                                }
                                                $sfData['Check_out_Date__c'] = date('Y-m-d', strtotime($rValue.'+7 days'));
                                            }
                                            if($rKey == 'Size')
                                            {
                                                if($isDeposit)
                                                {
                                                    $sfData['Deposit_Unit_Type__c'] = $rValue;
                                                }
                                                else
                                                {
                                                    //split the bedrooms
                                                    $bSplit = explode('/', $rValue);
                                                    $sfData['Unit_Type__c'] = $bSplit[0];
                                                }
                                            }
                                            if($rKey == 'WeekType')
                                            {
                                                $sfData['Week_Type__c'] = $mappedTransTypes[$rValue];
                                                if(trim($row['WeekType']) == 'Exchange' || trim($row['WeekType']) == 'Rental')
                                                {
                                                    $sfData['Week_Type__c'] = $row['WeekType'];
                                                }

                                            }
                                            if($rKey == 'deposit')
                                            {
                                                if($isDeposit)
                                                {
                                                    $sfData['Deposit_Reference__c'] = $rValue;
                                                    $sfData['Reservation_Reference__c'] = $rValue;
                                                }
                                                else
                                                {
                                                    $sfData['Reservation_Reference__c'] = $rValue;
                                                }
                                            }
                                                    if($rKey == 'processedBy')
                                                    {
                                                        //should be the name of the supervisor or owner
                                                        $sfData['Booked_By__c'] = $rValue;
                                                    }
                                                }
                                                //if any details are missing then don't pass the field to SF
                                                foreach($sfData as $sfK=>$sfD)
                                                {
                                                    if(empty($sfD))
                                                    {
                                                        unset($sfData[$sfK]);
                                                    }
                                                }

                                                if(!empty($sfData['CPO_Fee__c']))
                                                {
                                                    $CPO = "True";
                                                }
                                                else
                                                {
                                                    $CPO = "";
                                                }




                                                //make sure these items aren't empty
                                                if(!empty($sfData['Check_in_Date__c']))
                                                {
                                                    $neCheckIn = date('d M Y', strtotime($sfData['Check_in_Date__c']));
                                                }
                                                if(!empty($sfData['Check_out_Date__c']) && !empty($sfData['Check_in_Date__c']))
                                                {
                                                    $neNoNights = ((strtotime($sfData['Check_out_Date__c']) - strtotime($sfData['Check_in_Date__c'])) / (60*60*24));
                                                }
                                                if(!empty($sfData['Member_First_Name__c']) || !empty($sfData['Member_Last_Name__c']))
                                                {
                                                    $neMemberName = $sfData['Member_First_Name__c']." ".$sfData['Member_Last_Name__c'];

                                                }
                                                if(!empty($sfData['Guest_First_Name__c']) || !empty($sfData['Guest_Last_Name__c']))
                                                {
                                                    $neGuestName = $sfData['Guest_First_Name__c']." ".$sfData['Guest_Last_Name__c'];
                                                }

                                                //if this is a reversal and travellers first name is blank then we need to make this a confirmed transaction and make sure that the travellers first and last name remain.
                                                if($row['transactionType'] == 'REVERSAL')
                                                {
                                                    //this is a reversal is the traveller blank
                                                    if(!empty($row['guestFirstName']))
                                                    {
                                                        //still going is this an exchange or rental?
                                                        if(in_array($row['Purchase_Type__c'], $allIncludeConfirmed))
                                                        {
                                                            $sfData['Reservation_Status__c'] = 'Confirmed';
                                                            //                                     $sfData['Deposit_Status__c'] = 'Confirmed';
                                                            unset($sfData['Guest_First_Name__c']);
                                                            unset($sfData['Guest_Last_Name__c']);
                                                        }
                                                    }
                                                }


                                                $dbTableData = [
                                                    'MemberNumber'=>$sfData['EMS_Account__c'],
                                                    'MemberName'=>$neMemberName,
                                                    'Owner'=>$sfData['Trade_Partner__c'],
                                                    'GuestName'=>$neGuestName,
                                                    'Adults'=>$sfData['of_Adults__c'],
                                                    'Children'=>$sfData['Children'],
                                                    'UpgradeFee'=>$sfData['Upgrade_Fee__c'],
                                                    'CPOFee'=>$sfData['CPO_Fee__c'],
                                                    'CPO'=>$CPO,
                                                    'Paid'=>$paid,
                                                    'Balance'=>'0',
                                                    'ResortID'=>$resortID,
                                                    'sleeps'=>$bSplit[1],
                                                    'bedrooms'=>$bSplit[0],
                                                    'Size'=>implode("/", $bSplit),
                                                    'noNights'=>$neNoNights,
                                                    'checkIn'=>$neCheckIn,
                                                    'specialRequest'=>$sfData['Special_Requests__c'],
                                                    'Email'=>$sfData['Member_Email__c'],
                                                    'Uploaded'=>date('Y-m-d H:i:s'),
                                                ];


                                                //if this is a late deposit then we need traveling memeber
                                                /*
                                                 * Specifically for the charge codes that refer to late deposit fees,
                                                 * we don't really "care" that there is a travelling member ID.
                                                 * Ashley 12/5/2019
                                                 */
                                                $ldCheck = $row['WeekType'];
                                                if($mappedTransTypes[$ldCheck] == 'Late Deposit')
                                                {
                                                    $sfData['EMS_Account__c'] = '';
                                                    $sfData['Member_First_Name__c'] = '';
                                                    $sfData['Member_Last_Name__c'] = '';
                                                }

                                                $dbTable = [
                                                    'transactionType'=>'booking',
                                                    'resortID'=>$resortID,
                                                    'sfEMSID'=>$userMemberNo,
                                                    'sfTransactionDate'=>$sfTransactionDate,
                                                    'data'=>json_encode($dbTableData),
                                                    'datetime'=>$dateTime,
                                                ];

                                                $dbTable['userID'] = $userID;
                                                $dbTable['weekId'] = $weekId;
                                                $dbTable['sfData'] = json_encode(array(
                                                    'insert'=>$sfData,
                                                ));
                                                if($isDeposit)
                                                {
                                                    $dbTable['transactionType'] = 'deposit';
                                                }
                                                //is this part of the extra trasactions table?
                                                if(isset($sfData['RecordTypeId']) && in_array($sfData['RecordTypeId'], $extraTransactionTypes))
                                                {
                                                    foreach($extraTransactionTypes as $ettKey=>$ett)
                                                    {
                                                        if($sfData['RecordTypeId'] == $ett)
                                                        {
                                                            $dbTable['transactionType'] = $ettKey;
                                                        }
                                                    }
                                                }
                                                if(strtolower($sfData['GpxWeekRefId__c']) == 'cancelled' || strtolower($sfData['GpxWeekRefId__c']) == 'canceled')
                                                {
                                                    $cancelled = [
                                                        'userid'=>$userID,
                                                        'date'=>date('Y-m-d H:i:s'),
                                                    ];
                                                    $dbTable['cancelled'] = json_encode($cancelled);
                                                }
                                                //has this record been added in our database?
                                                $resNum = $sfData['Reservation_Reference__c'];

                                                if(!empty($transRow))
                                                {
                                                    $dbTableToUpdate = json_decode($transRow->data, true);
                                                }

                                                //was the cpo set for this transaction?
                                                if(empty($CPO))
                                                {
                                                    if(isset($row['CPO']) && $row['CPO'] == "Taken")
                                                    {
                                                        $CPO = "True";
                                                        $sfData['CPO_Opt_in__c'] = true;
                                                        $sfData['Flex_Booking__c'] = true;
                                                    }
                                                }
                                                if(isset($row['CPO']) && $row['CPO'] == "Taken")
                                                {
                                                    $CPO = "True";
                                                    $sfData['CPO_Opt_in__c'] = true;
                                                    $sfData['Flex_Booking__c'] = true;
                                                }

                                                //handle booked by
                                                if(isset($row['processedBy']))
                                                {
                                                    if($row['userID'] == $row['processedBy'])
                                                    {
                                                        $sfData['Booked_By__c'] = 'Owner';
                                                    }
                                                    else
                                                    {
                                                        //get the name of the person that booked this
                                                        $bookedby_user_info = get_userdata($row['processedBy']);
                                                        $sfData['Booked_By__c'] = $bookedby_user_info->first_name." ".$bookedby_user_info->last_name;
                                                    }
                                                }

                                                foreach($dbTableData as $dbtdKey=>$dbtd)
                                                {
                                                    if(!empty($dbtd))
                                                    {
                                                        $dbTableToUpdate[$dbtdKey] = $dbtd;
                                                    }
                                                }
                                                $dbTable['data'] = json_encode($dbTableToUpdate);

                                                $dbError = '';
                                                $sfError = [];

                                                    $sfData['GPXTransaction__c'] = $transactionID;

                                                    //we need to get week details and add update the database if necessary
//
                                                    //create the week object
                                                    foreach($objects['week'] as $oWeekKey=>$oWeek)
                                                    {
                                                        if(isset($sfData[$oWeek]) && !empty($sfData[$oWeek]))
                                                        {
                                                            if($oWeek == 'Unit_Sleeps__c' && strrpos($sfData[$oWeek], "+"))
                                                            {
                                                                $sfData[$oWeek] = substr($sfData[$oWeek], 0, strrpos($sfData[$oWeek], "+"));
                                                            }
                                                            $sfWeekData[$oWeek] = str_replace("&", "&amp;", $sfData[$oWeek]);
                                                        }
                                                        elseif(isset($weekDetails->$oWeekKey))
                                                        {
                                                            if($oWeek == 'Unit_Sleeps__c' && strrpos($weekDetails->$oWeekKey, "+"))
                                                            {
                                                                $weekDetails->$oWeekKey = substr($weekDetails->$oWeekKey, 0, strrpos($weekDetails->$oWeekKey, "+"));
                                                            }
                                                            $sfWeekData[$oWeek] = str_replace("&", "&amp;", $weekDetails->$oWeekKey);
                                                        }
                                                    }

                                                    //adjust the status
                                                    if(!$isDeposit)
                                                    {
                                                        $sfWeekData['Status__c'] = 'Booked';
                                                        if($sfData['Reservation_Status__c'] == 'Pending Deposit')
                                                        {
                                                            $sfWeekData['Status__c'] = 'Pending';
                                                        }
                                                        if($sfData['Reservation_Status__c'] == 'Cancelled')
                                                        {
                                                            $sfWeekData['Status__c'] = 'Available';
                                                        }
                                                    }

                                                                $sfWeekData['GpxWeekRefId__c'] = $sfWeekData['Name'] = $weekId;
                                                                //add the date/time that this is bing synced
                                                                $sfWeekData['Date_Last_Synced_with_GPX__c'] = $sfTransData['Date_Last_Synced_with_GPX__c'] = date('Y-m-d');

                                                                //is this a trade partner booking?
                                                                $sql = $wpdb->prepare("SELECT record_id, name, sf_account_id FROM wp_partner WHERE user_id=%s", $row['userID']);
                                                                $istp = $wpdb->get_row($sql);

                                                                $sfData['Account_Type__c'] = 'USA GPX Member';
                                                                if(!empty($istp))
                                                                {
                                                                    $sfData['Booked_by_TP__c'] = 1;
                                                                    $sfWeekData['Booked_by_TP__c'] = 1;
                                                                    $sfData['Account_Type__c'] = 'USA GPX Trade Partner';
                                                                    $sfData['Account_Name__c'] = $istp->sf_account_id;
                                                                    $sfData['Member_Last_Name__c'] = $istp->name;
                                                                    $sfData['Purchase_Price__c'] = 0;
                                                                    $sfData['CPO_Fee__c'] = 0;
                                                                    $sfData['Tax_Paid__c'] = 0;
                                                                    $sfData['Upgrade_Fee__c'] = 0;
                                                                    $sfData['Guest_Fee__c'] = 0;
                                                                    $sfData['Credit_Extension_Fee__c'] = 0;
                                                                    $sfData['Late_Deposit_Fee__c'] = 0;
                                                                    $sfData['Coupon_Discount__c'] = 0;

                                                                    $sfData['Guest_First_Name__c'] = 'Partner';
                                                                    $sfData['Guest_Last_Name__c'] = 'Hold';

                                                                }

                                                                $approvedWeeks = [
                                                                    'Available',
                                                                    'Approved',
                                                                    'Booked',
                                                                ];

                                                                //is this a deposit on exchange
                                                                if((isset($row['CreditStatus']) && !in_array($row['CreditStatus'], $approvedWeeks)) ||
                                                                    (isset($transRow->CreditStatus) && !in_array($transRow->CreditStatus, $approvedWeeks)))
                                                                {

                                                                    $sfData['Reservation_Status__c'] == 'Pending Deposit';
                                                                    $sfWeekData['Status__c'] = 'Pending';
                                                                }
                                                                if(isset($thisisimported))
                                                                {
                                                                    $sfWeekData['Status__c'] = 'Booked';
                                                                }

                                                                foreach($removeSpecialChar as $rsc)
                                                                {
                                                                    if(isset($sfWeekData[$rsc]))
                                                                    {
                                                                        $sfWeekData[$rsc] = str_replace("&amp;", " and ", $sfWeekData[$rsc]);
                                                                        $sfWeekData[$rsc] = preg_replace('/[^ \w\-\.,]/', '', $sfWeekData[$rsc]);
                                                                    }
                                                                }
                                                                if(empty($sfData['Booked_By__c']))
                                                                {
                                                                    $agentInfo = wp_get_current_user();
                                                                    $sfData['Booked_By__c'] = $agentInfo->first_name.' '.$agentInfo->last_name;
                                                                }
                                                                    $sfWeekAdd = '';
                                                                    $sfAdd = '';
                                                                    $sfType = 'GPX_Week__c';
                                                                    $sfObject = 'GpxWeekRefId__c';


                                                                    $sfFields = [];
                                                                    $sfFields[0] = new SObject();
                                                                    $sfFields[0]->fields = $sfWeekData;
                                                                    $sfFields[0]->type = $sfType;

                                                                    //add GPX_Ref__c for guest fee one-off
                                                                    if($sfData['RecordTypeId'] == '0121W000000E02oQAC')
                                                                    {
                                                                        $sql = $wpdb->prepare("SELECT sfData from wp_gpxTransactions WHERE id=%s", $row['transactionID']);
                                                                        $sfDataRow = $wpdb->get_var($sql);
                                                                        $sfRow = json_decode($sfDataRow, true);
                                                                        $sfData['GPX_Ref__c'] = $sfRow['insert']['GPX_Ref__c'];
                                                                    }

                                                                    if($sfData['RecordTypeId'] == '0121W0000005jWTQAY' || isset($_GET['send_week']))
                                                                    {
                                                                        $sfWeekAdd = $sf->gpxUpsert($sfObject, $sfFields);
                                                                    }
                                                                    if(isset($_GET['sf_week_debug']))
                                                                    {
                                                                        echo '<pre>'.print_r($sfWeekAdd, true).'</pre>';
                                                                    }

                                                                    if(isset($sfWeekAdd[0]->id))
                                                                    {
                                                                        $sfData['GPX_Ref__c'] = $sfWeekAdd[0]->id;
                                                                    }
                                                                    else
                                                                    {
                                                                        $sfAdd = $sfWeekAdd;
                                                                    }

                                                                 if(!isset($sfAdd[0]->errors))
                                                                {
                                                                    //did this transaction have an extension fee
                                                                    if((isset($cjson->creditextensionfee) && $cjson->creditextensionfee > 0))
                                                                    {
                                                                        $sfData['Credit_Extension_Fee__c'] = $cjson->creditextensionfee;
                                                                        if(empty($sfData['Credit_Extension_Fee__c']))
                                                                        {
                                                                            $sfData['Credit_Extension_Fee__c'] = $cjson->actextensionFee;
                                                                        }

                                                                        $creditWeekID = $cjson->creditweekid;
                                                                        $sql = $wpdb->prepare("SELECT record_id FROM wp_credit WHERE id=%s", $creditWeekID);
                                                                        $dRow = $wpdb->get_row($sql);
                                                                        $objects['transaction'][] = 'GPX_Deposit__c';
                                                                    }

                                                                    if((isset($cjson->lateDepositFee) && $cjson->lateDepositFee > 0))
                                                                    {
                                                                        $sfData['Late_Deposit_Fee__c'] = $cjson->lateDepositFee;
                                                                    }

                                                                    foreach($objects['transaction'] as $oTransKey=>$oTrans)
                                                                    {
                                                                        if(isset($sfData[$oTrans]))
                                                                        {
                                                                            $sfTransData[$oTrans]  = $sfData[$oTrans];
                                                                        }
                                                                        elseif(isset($ownerDetails->$oTransKey))
                                                                        {
                                                                            $sfTransData[$oTrans] = $ownerDetails->$oTransKey;
                                                                        }
                                                                    }

                                                                    $sfTransData['Name'] = $sfTransData['GPXTransaction__c'];

                                                                    foreach($removeSpecialChar as $rsc)
                                                                    {
                                                                        if(isset($sfTransData[$rsc]) && !empty($sfTransData[$rsc]))
                                                                        {
                                                                            $sfWeekData[$rsc] = str_replace("&amp;", " and ", $sfWeekData[$rsc]);
                                                                            $sfTransData[$rsc] = preg_replace('/[^ \w\-\.,]/', '', $sfTransData[$rsc]);
                                                                        }
                                                                    }

                                                                    $sfType = 'GPX_Transaction__c';
                                                                    $sfObject = 'GPXTransaction__c';
                                                                    $sfFields = [];
                                                                    $sfFields[0] = new SObject();
                                                                    $sfFields[0]->fields = $sfTransData;
                                                                    $sfFields[0]->type = $sfType;
                                                                    $sfAdd = $sf->gpxUpsert($sfObject, $sfFields);

                                                                }
                                                if(isset($sfAdd[0]->id))
                                                {
                                                    $sfDB = array(
                                                        'sfid'=> $sfAdd[0]->id,
                                                        'sfData'=>json_encode(array('insert'=>$sfData)),
                                                    );

                                                    $wpdb->update('wp_gpxTransactions', $sfDB, array('id'=>$transactionID));
                                                    $insertSuccess[] = 'Record '.$weekId.' added.';
                                                }
                                                else
                                                {
                                                    $errorData = [
                                                        'error'=>$sfAdd,
                                                    ];
                                                    if(isset($sfTransData))
                                                    {
                                                        $errorData['upsert'] = $sfTransData;
                                                    }
                                                    else
                                                    {
                                                        $errorData['upsert'] = $sfWeekData;
                                                    }
                                                    $sfDB = array(
                                                        'sfid'=> $sfAdd[0]->id,
                                                        'sfData'=>json_encode($errorData),
                                                    );

                                                    if(!isset($sfAdd[0]->id) || (isset($sfAdd[0]->id) && empty($sfAdd[0]->id)))
                                                    {
                                                            // use the provided email list if defined, otherwise just use the blog admin
                                                            $to = (defined('GPX_NOTIFICATION_EMAILS')) ? GPX_NOTIFICATION_EMAILS : get_option('admin_email');

                                                            $subject = 'GPX Transaction to SF error on '.get_site_url();

                                                            $body = '<h2>Transaction: '.$transactionID.'</h2><h2>Error</h2><pre>'.print_r($errorData, true).'</pre>';
                                                            $headers = array('Content-Type: text/html; charset=UTF-8');

                                                            wp_mail( $to, $subject, $body, $headers );
                                                    }

                                                    $wpdb->update('wp_gpxTransactions', $sfDB, array('id'=>$transactionID));

                                                    $insertSuccess[] = 'Record '.$weekId.' added.';

                                                    foreach($sfAdd as $sfAddError)
                                                    {
                                                        foreach($sfAddError->errors as $err)
                                                        {
                                                            $sfError[] = $err->message;
                                                        }
                                                    }
                                                    if(!empty($dbError))
                                                    {
                                                        $sfError[] = $dbError;
                                                    }
                                                    $insertError[] = 'Record '.$sfData['Reservation_Reference__c']." couldn't be added: ".implode(" & ", $sfError);

                                                }
                if(isset($insertError))
                {
                    $data['message'][] = [
                        'type'=>'nag-fail',
                        'text'=>implode('<br /><br />', $insertError),
                    ];
                }
                if(isset($insertSuccess))
                {
                    $data['message'][] = [
                        'type'=>'nag-success',
                        'text'=>implode('<br /><br />', $insertSuccess),
                    ];
                }

        return $data;
    }

}
