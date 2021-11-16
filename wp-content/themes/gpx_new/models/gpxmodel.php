<?php

function get_property_details($book, $cid)
{
    global $wpdb;
    $joinedTbl = map_dae_to_vest_properties();
    $results = [];
    $sql = "SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinResort']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                    WHERE a.record_id='".$book."' AND a.archived=0 AND a.active_rental_push_date != '2030-01-01'";
    $prop = $wpdb->get_row($sql);
    if(isset($prop) && !empty($prop))
    {
        //validate availablity
        if($prop->availablity > 1)
        {
            if($prop->availablity == '2')
            {
                //this should only be available to ownersa
            }
            if($prop->availablity == '3')
            {
                //this should only be available to partners
            }
        }
        //add the prop type 
        
        if(isset($_REQUEST['type']))
        {
            $prop->WeekType = $_REQUEST['type'];
        }
        elseif(isset($_COOKIE['gpx-cart']))
        {
            $sql = "SELECT data FROM wp_cart WHERE cartID='".$_COOKIE['gpx-cart']."' AND weekID='".$prop->PID."' ORDER BY id desc";
            $prow = $wpdb->get_row($sql);
            
            $pdata = json_decode($prow->data);

            $prop->WeekType = str_replace(" ", "", $pdata->weekType);
                   
//             $prop->WeekType = $_COOKIE['exchange_bonus'];
        }

        //use the exchange fee for the price?
        if(  $prop->WeekType == 'ExchangeWeek' || $prop->WeekType == 'Exchange Week')
        {
            $prop->Price = get_option('gpx_exchange_fee');
        }
        $prop->WeekPrice = $prop->Price;
        $sql = "SELECT * FROM wp_resorts_meta WHERE ResortID='".$prop->ResortID."'";
        $resortMetas = $wpdb->get_results($sql);
        
        $rmFees = [
            'ExchangeFeeAmount'=>[
                'WeekPrice',
                'Price'
            ],
            'RentalFeeAmount'=>[
                'WeekPrice',
                'Price'
            ],
            'UpgradeFeeAmount'=>[],
            'CPOFeeAmount'=>[],
            'GuestFeeAmount'=>[],
        ];
        
        foreach($resortMetas as $rm)
        {
            $rmk = $rm->meta_key;
            if($rmArr = json_decode($rm->meta_value, true))
            {
                ksort($rmArr);
                        
                foreach($rmArr as $rmdate=>$rmvalues)
                {
                    // we need to display all of the applicaable alert notes
                    if(isset($lastValue) && !empty($lastValue))
                    {
                        $thisVal = $lastValue;
                    }
                    else
                    {
                        if(isset($resort->$rmk))
                        {
                            $thisVal = $resort->$rmk;
                        }
                    }
                    $rmdates = explode("_", $rmdate);
                    if(count($rmdates) == 1 && $rmdates[0] == '0')
                    {
                        //do nothing
                    }
                    else
                    {
                        //changing this to go by checkIn instead of the active date
                        $checkInForRM = strtotime($prop->checkIn);
                        //check to see if the from date has started
                        //                                         if($rmdates[0] < strtotime("now"))
                        if($rmdates[0] <= $checkInForRM)
                        {
                            //this date has started we can keep working
                        }
                        else
                        {
                            //these meta items don't need to be used
                            continue;
                        }
                        //check to see if the to date has passed
                        //                                         if(isset($rmdates[1]) && ($rmdates[1] >= strtotime("now")))
                        if(isset($rmdates[1]) && ($checkInForRM > $rmdates[1]))
                        {
                            //these meta items don't need to be used
                            continue;
                        }
                        else
                        {
                            //this date is sooner than the end date we can keep working
                        }
                        
                        //check to see if the from date within the checkin date
//                         if($rmdates[0] > strtotime($prop->checkIn))
//                         {
//                             //the set date is greater than the checkin date 
//                             if($rmk == 'AlertNote')
//                             {
//                                 continue;
//                             }
//                         }
//                         else
//                         {
//                             //are these resort fees?
//                             if(array_key_exists($rmk, $rmFees))
//                             {
//                                 //we don't need to do anything
//                             }
//                             else 
//                             {
//                                 //these meta items don't need to be used -- except for alert notes -- we can show those in the future
//                                 if($rmk != 'AlertNote')
//                                 {
//                                     continue;
//                                 }
//                             }
//                         }
//                         //check to see if the to date has passed
//                         if(isset($rmdates[1]) && ($rmdates[1] < strtotime($prop->checkIn)))
//                         {
//                             //these meta items don't need to be used
//                             continue;
//                         }
//                         else
//                         {
//                             //this date is sooner than the end date we can keep working
//                         }
                        if(array_key_exists($rmk, $attributesList))
                        {
                            // this is an attribute list Handle it now...
                            $thisVal = $resort->$rmk;
                            $thisVal = json_encode($rmvalues);
                        }
                        //do we need to reset any of the fees?
                        elseif(array_key_exists($rmk, $rmFees))
                        {
                            
                            //set this amount in the object
                            $prop->$rmk = $rmvalues;
                            if(!empty($rmFees[$rmk]))
                            {
                                //if values exist then we need to overwrite
                                foreach($rmFees[$rmk] as $propRMK)
                                {
                                    //if this is either week price or price then we only apply this to the correct week type...
                                    if($rmk == 'ExchangeFeeAmount')
                                    {
                                        //$prop->WeekType cannot be RentalWeek or BonusWeek
                                        if($prop->WeekType == 'BonusWeek' || $prop->WeekType == 'RentalWeek')
                                        {
                                            continue;
                                        }
                                    }
                                    elseif($rmk == 'RentalFeeAmount')
                                    {
                                        //$prop->WeekType cannot be ExchangeWeek
                                        if($prop->WeekType == 'ExchangeWeek')
                                        {
                                            continue;
                                        }
                                        
                                    }
                                    $fee = end($rmvalues);
                                    $prop->$propRMK = preg_replace("/\d+([\d,]?\d)*(\.\d+)?/", $fee, $prop->$propRMK);
                                }
                            }
                        }
                        else
                        {
                            $rmval = end($rmvalues);
                            //set $thisVal = ''; if we should just leave this completely off when the profile button isn't selected
                            if(isset($resort->$rmk))
                            {
                                $thisVal = $resort->$rmk;
                            }
                            //check to see if this should be displayed in the booking path
                            if(isset($rmval['path']) && $rmval['path']['booking'] == 0)
                            {
                                //this isn't supposed to be part of the booking path
                                continue;
                            }
                            if(isset($rmval['desc']))
                            {
                                if($rmk == 'AlertNote')
                                {
                                    
                                    if(!in_array($rmval['desc'], $thisset))
                                    {
                                        $thisValArr[] = [
                                            'desc' => $rmval['desc'],
                                            'date' => $rmdates,
                                        ];
                                    }
                                    $thisset[] = $rmval['desc'];
                                }
                                else
                                {
                                    $thisVal = $rmVal['desc'];
                                    $thisValArr = [];
                                }
                            }
                        }
                    }
                    $lastValue = $thisVal;
                }
                if($rmk == 'AlertNote' && isset($thisValArr) && !empty($thisValArr))
                {
                    $thisVal = $thisValArr;
                }
                $prop->$rmk = $thisVal;
            }
            else
            {
                if($meta->meta_value != '[]')
                {
                    $prop->$rmk = $meta->meta_value;
                }
            }
        }
        $checkInDate = date('Y-m-d', strtotime($prop->checkIn));
        $discount = '';
        $specialPrice = '';
        $todayDT = date("Y-m-d 00:00:00");
        $thisPromo = [];
        //are there specials?

        $sql = "SELECT a.id, a.Name, a.Slug, a.Properties, a.Amount, a.SpecUsage, a.PromoType, a.master
    			FROM wp_specials a
                LEFT JOIN wp_promo_meta b ON b.specialsID=a.id
                LEFT JOIN wp_resorts c ON c.id=b.foreignID
                LEFT JOIN wp_gpxRegion d ON d.id=b.foreignID
                WHERE (SpecUsage = 'any'
                OR ((c.ResortID='".$prop->ResortID."' AND b.refTable='wp_resorts')
                OR (b.reftable = 'wp_gpxRegion' AND d.id IN ('".$prop->gpxRegionID."')))
                OR SpecUsage LIKE '%customer%'
                OR SpecUsage LIKE '%region%')
                AND '".$checkInDate."' BETWEEN TravelStartDate AND TravelEndDate
                AND (StartDate <= '".$todayDT."' AND EndDate >= '".$todayDT."')
                AND a.Type='promo'
                AND a.Active=1";
        $rows = $wpdb->get_results($sql);
        $promoTerms = '';
        $priceint = preg_replace("/[^0-9\.]/", "",$prop->WeekPrice);
        if($priceint != $prop->Price)
            $prop->Price = $priceint;
            if($rows)
            {
                $specialDiscountPrice = $prop->Price;
                $i = 0;
                $promoTerms = array();
                $prop->specialDesc = '';
                foreach($rows as $row)
                {
                    
                    if(isset($_REQUEST['promo_debug']))
                    {
                        echo '<pre>'.print_r($row->Name, true).'</pre>';
                    }
                    $uregionsAr = array();
                    $skip = false;
                    $regionOK = false;
                    $resortOK = false;
                    $skippedBefore = false;
                    $specialMeta = stripslashes_deep( json_decode($row->Properties) );
                    
                    $nostacking = false;
                    if(isset($specialMeta->stacking) && $specialMeta->stacking == 'No')
                    {
                        //we don't want to stack this promo
                        $nostacking = true;
                    }
                    //week min cost
                    if(isset($specialMeta->minWeekPrice) && !empty($specialMeta->minWeekPrice))
                    {
                        if($prop->WeekType == 'ExchangeWeek')
                            $skip = true;
                        if($nostacking)
                        {
                            $sptPrice = $prop->Price;
                        }
                        else 
                        {
                            $sptPrice = $specialDiscountPrice;
                        }
                        if($sptPrice < $specialMeta->minWeekPrice)
                                $skip = true;
                    }
                    //usage upsell
                    $upsell = array();
                    if(isset($specialMeta->upsellOptions) && !empty($specialMeta->upsellOptions))
                    {
                        
                        $upsell[] = array(
                            'option'=>$specialMeta->upsellOptions,
                            'type' => $specialMeta->promoType,
                            'amount' => $row->Amount,
                        );
                    }
                    if($specialMeta->promoType == 'BOGO' || $specialMeta->promoType == 'BOGOH')
                    {
                        $bogomax = '';
                        $bogomin = '';
                        $bogoSet = $row->Slug;
                        if(isset($_COOKIE['gpx-cart']))
                        {
                            $boCartID = $_COOKIE['gpx-cart'];
                            $sql = "SELECT * FROM wp_cart WHERE cartID='".$boCartID."'";
                            $bocarts = $wpdb->get_results($sql);
                        }
                        if(isset($bocarts) && !empty($bocarts))
                        {
                            foreach($bocarts as $bocart)
                            {
                                $boPID = $bocart->propertyID;
                                $sql = "SELECT price FROM wp_room WHERE id='".$boPID."'";
                                $boPriceQ = $wpdb->get_row($sql);
                                $bogo = $boPriceQ->Price;
                                if($bogo > $bogomax)
                                {
                                    $bogomax = $bogo;
                                }
                                if(empty($bogomin))
                                {
                                    $bogomin = $bogo;
                                    $bogominPID = $boPID;
                                }
                                elseif($bogo <= $bogomin)
                                {
                                    $bogomin = $bogo;
                                    $bogominPID = $boPID;
                                }
                            }
                            if($bogominPID == $prop->id)
                            {
                                if($specialMeta->promoType == 'BOGOH')
                                    $bogoPrice = number_format($prop->Price/2, 2);
                                    else
                                        $bogoPrice = '0';
                            }
                        }
                        if(count($bocarts) < 2)
                        {
                            unset($bogoPrice);
                            $skip = true;
                        }
                    }
                    if(is_array($specialMeta->transactionType))
                        $ttArr = $specialMeta->transactionType;
                        else
                            $ttArr = array($specialMeta->transactionType);
                            $transactionType = array();
                            foreach($ttArr as $tt)
                            {
                                switch ($tt)
                                {
                                    case 'Upsell':
                                        $transactionType['upsell'] = 'Upsell';
                                        break;
                                        
                                    case 'All':
                                        $transactionType['any'] = $prop->WeekType;
                                        break;
                                        
                                    case 'any':
                                        $transactionType['any'] = $prop->WeekType;
                                        break;
                                    case 'ExchangeWeek':
                                        $transactionType['exchange'] = 'ExchangeWeek';
                                        break;
                                    case 'BonusWeek':
                                        $transactionType['bonus'] = 'BonusWeek';
                                        $transactionType['rental'] = 'RentalWeek';
                                        break;
                                    case 'RentalWeek':
                                        $transactionType['rental'] = 'RentalWeek';
                                        $transactionType['bonus'] = 'BonusWeek';
                                        break;
                                }
                            }
                            if(
                                !empty($upsell) || 
                                ($specialMeta->stacking == 'No' && $row->Amount > $discount) ||
                                ($transactionType == $prop->WeekType || in_array($prop->WeekType, $transactionType)) || 
                                (isset($bogominPID) && $bogominPID == $prop->id) || 
                                ((isset($specialMeta->acCoupon) && $specialMeta->acCoupon == 1) &&  ($transactionType == $prop->WeekType || in_array($prop->WeekType, $transactionType)))
                              )
                            {
                                
                                /*
                                 * filter out conditions
                                 */
                                // landing page only
                                //does this user have the cookie set in the database?
                                if($thisCookieID = get_user_meta($cid, 'lppromoid'.$prop->weekId.$row->id) && !isset($_COOKIE['lppromoid'.$prop->weekId.$row->id]))
                                {
                                    $_COOKIE['lppromoid'.$prop->weekId.$row->id] = $thisCookieID;
                                }
                                
                                if(isset($specialMeta->availability) && $specialMeta->availability == 'Landing Page')
                                {
                                    $lpid = '';
                                    if(isset($_COOKIE['lppromoid'.$prop->weekId.$row->id]))
                                    {
                                        // all good
                                        $lpid = $prop->weekId.$row->id;
                                    }
                                    elseif($row->master != 0)
                                    {
                                        if(isset($_COOKIE['lppromoid'.$prop->weekId.$row->master]))
                                        {
                                            $lpid = $prop->weekId.$row->master;
                                        }
                                        else 
                                        {
                                            //are there other children of this parent
                                            $sql = "SELECT id FROM wp_specials WHERE master=".$row->master;
                                            $lpmasters = $wpdb->get_results($sql);
                                            foreach($lpmasters as $lpm)
                                            {
                                                if(isset($_COOKIE['lppromoid'.$prop->weekId.$lpm->id]))
                                                {
                                                    $lpid = $prop->weekId.$lpm->id;
                                                }
                                            }
                                        }
                                    }
                                    else
                                    {
                                        //let's check to see if it was on hold
                                        $sql = "SELECT lpid FROM wp_gpxPreHold WHERE weekId='".$prop->weekId."' AND user='".$cid."' AND lpid='".$prop->weekId.$row->id."'";
                                        $lpidRows = $wpdb->get_results($sql);
                                        foreach($lpidRows as $lpidRow)
                                        {
                                            if(!empty($lpidRow->lpid))
                                                $lpid = $lpidRow->lpid;
                                        }
                                        
                                    }
                                    //if the lpid is empty then we can skip
                                    if(empty($lpid))
                                    {
                                        $skip = true;
                                        if(isset($_REQUEST['promo_debug']))
                                        {
                                            echo '<pre>'.print_r('skipped '.$row->id.': lpid', true).'</pre>';
                                        }
                                    }
                                }
                                //blackouts
                                if(isset($specialMeta->blackout) && !empty($specialMeta->blackout))
                                {
                                    foreach($specialMeta->blackout as $blackout)
                                    {
                                        if(strtotime($prop->checkIn) >= strtotime($blackout->start) && strtotime($prop->checkIn) <= strtotime($blackout->end))
                                        {
                                            $skip = true;
                                        if(isset($_REQUEST['promo_debug']))
                                        {
                                            echo '<pre>'.print_r('skipped '.$row->id.': blackout', true).'</pre>';
                                        }
                                        }
                                    }
                                }
                                //resort blackout dates
                                if(isset($specialMeta->resortBlackout) && !empty($specialMeta->resortBlackout))
                                {
                                    foreach($specialMeta->resortBlackout as $resortBlackout)
                                    {
                                        //if this resort is in the resort blackout array then continue looking for the date
                                        if(in_array($prop->resortID, $resortBlackout->resorts))
                                        {
                                            if(strtotime($prop->checkIn) > strtotime($resortBlackout->start) && strtotime($prop->checkIn) < strtotime($resortBlackout->end))
                                            {
                                                $skip = true;
                                                if(isset($_REQUEST['promo_debug']))
                                                {
                                                    echo '<pre>'.print_r('skipped '.$row->id.': resort blackout', true).'</pre>';
                                                }
                                            }
                                        }
                                    }
                                }
                                //resort specific travel dates
                                if(isset($specialMeta->resortTravel) && !empty($specialMeta->resortTravel))
                                {
                                    foreach($specialMeta->resortTravel as $resortTravel)
                                    {
                                        //if this resort is in the resort blackout array then continue looking for the date
                                        if(in_array($prop->resortID, $resortTravel->resorts))
                                        {
                                            if(strtotime($prop->checkIn) > strtotime($resortTravel->start) && strtotime($prop->checkIn) < strtotime($resortTravel->end))
                                            {
                                                //all good
                                            }
                                            else
                                            {
                                                $skip = true;
                                                if(isset($_REQUEST['promo_debug']))
                                                {
                                                    echo '<pre>'.print_r('skipped '.$row->id.': travel specific', true).'</pre>';
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                if(isset($bogominPID) && $bogominPID != $prop->id)
                                {
                                    $skip = true;
                                        if(isset($_REQUEST['promo_debug']))
                                        {
                                            echo '<pre>'.print_r('skipped '.$row->id.': bogo', true).'</pre>';
                                        }
                                }
                                    if($specialMeta->beforeLogin == 'Yes' && !is_user_logged_in())
                                        $skip = true;
                                        
                                        if(strpos($row->SpecUsage, 'customer') !== false)//customer specific
                                        {
                                            if(isset($cid))
                                            {
                                                $specCust = (array) json_decode($specialMeta->specificCustomer);
                                                if(!in_array($cid, $specCust))
                                                {
                                                    $skip = true;
                                                    if(isset($_REQUEST['promo_debug']))
                                                    {
                                                        echo '<pre>'.print_r('skipped '.$row->id.': customer', true).'</pre>';
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                $skip = true;
                                                if(isset($_REQUEST['promo_debug']))
                                                {
                                                    echo '<pre>'.print_r('skipped '.$row->id.': customer', true).'</pre>';
                                                }
                                            }
                                        }
                                        
                                        
                                        if(isset($_REQUEST['promo_debug']))
                                        {
                                            echo '<pre>'.print_r($row->name.' '.$row->id.' before region '.$skip, true).'</pre>';
                                        }
                                        
                                        if($skip)
                                        {
                                            $skippedBefore = true;
                                        }
                                        
//usage resort
                                        if(isset($specialMeta->usage_region) && !empty($specialMeta->usage_region))
                                        {
                                            $usage_regions = json_decode($specialMeta->usage_region);
                                            foreach($usage_regions as $usage_region)
                                            {
                                                $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE id='".$usage_region."'";
                                                $excludeLftRght = $wpdb->get_row($sql);
                                                $excleft = $excludeLftRght->lft;
                                                $excright = $excludeLftRght->rght;
                                                $sql = "SELECT id FROM wp_gpxRegion WHERE lft>=".$excleft." AND rght<=".$excright;
                                                $usageregions = $wpdb->get_results($sql);
                                                if(isset($usageregions) && !empty($usageregions))
                                                {
                                                    foreach($usageregions as $usageregion)
                                                    {
                                                        $uregionsAr[] = $usageregion->id;
                                                    }
                                                }
                                                
                                            }
                                            if(!in_array($prop->gpxRegionID, $uregionsAr))
                                            {
                                                $skip = true;
                                                $maybeSkipRR[] = true;
                                                $regionOK = 'no';
                                            }
                                            else
                                            {
                                                $regionOK = 'yes';
                                            }
                                        }
                                        
                                        if(isset($_REQUEST['promo_debug']))
                                        {
                                            echo '<pre>'.print_r($row->name.' '.$row->id.' region '.$skip, true).'</pre>';
                                        }
                                        
                                        //usage resort
                                        if(isset($specialMeta->usage_resort) && !empty($specialMeta->usage_resort))
                                        {
                                            if(isset($cart))
                                            {
                                                if(!in_array($cart->propertyID, $specialMeta->usage_resort))
                                                {
                                                    if(isset($regionOK) && $regionOK == true)//if we set the region and it applies to this resort then the resort doesn't matter
                                                    {
                                                        //do nothing
                                                        $resortOK = true;
                                                    }
                                                    else
                                                    {
                                                        $skip = true;
                                                        $maybeSkipRR[] = true;
                                                    }
                                                }
                                                else
                                                {
                                                    $resortOK = true;
                                                }
                                            }
                                            elseif(isset($_GET['book']))
                                            {
                                                if(isset($_REQUEST['promo_debug']))
                                                {
                                                    echo '<pre>'.print_r($specialMeta->usage_resort, true).'</pre>';
                                                }
                                                if(!in_array($_GET['book'], $specialMeta->usage_resort))
                                                {
                                                    if(isset($regionOK) && $regionOK == true)//if we set the region and it applies to this resort then the resort doesn't matter
                                                    {
                                                        //do nothing
                                                        $resortOK = true;
                                                    }
                                                    else
                                                    {
                                                        $skip = true;
                                                        $maybeSkipRR[] = true;
                                                    }
                                                }
                                                else 
                                                {
                                                    $resortOK = true;
                                                }
                                            }
                                            
                                            if($resortOK && !$skippedBefore)
                                            {
                                                $skip = false;
                                            }
                                            
                                        }
                                        
                                        if(isset($_REQUEST['promo_debug']))
                                        {
                                            echo '<pre>'.print_r($row->name.' '.$row->id.' resort '.$skip, true).'</pre>';
                                        }
                                        
                                        //transaction type
                                        if(!empty($transactionType) && (in_array('ExchangeWeek', $transactionType) || !in_array('BonusWeek', $transactionType)))
                                        {
                                            if(!in_array($prop->WeekType, $transactionType))
                                            {
                                                $skip = true;
                                                if(isset($_REQUEST['promo_debug']))
                                                {
                                                    echo '<pre>'.print_r('skipped '.$row->id.': transaction type', true).'</pre>';
                                                }
                                            }
                                        }
                                        
                                        //useage DAE
                                        if(isset($specialMeta->useage_dae) && !empty($specialMeta->useage_dae))
                                        {
                                            //Only show if OwnerBusCatCode = DAE AND StockDisplay = ALL or GPX
                                            //if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx') && strtolower($prop->OwnerBusCatCode) == 'dae')
                                            if((strtolower($prop->StockDisplay) == 'all' || (strtolower($prop->StockDisplay) == 'gpx' || strtolower($prop->StockDisplay) == 'usa gpx')) && (strtolower($prop->OwnerBusCatCode) == 'dae' || strtolower($prop->OwnerBusCatCode) == 'usa dae'))
                                            {
                                                // we're all good -- these are the only properties that should be displayed
                                            }
                                            else
                                            {
                                                $skip = true;
                                            }
                                            
                                        }
                                        //exclusions
                                        //exclude DAE inventory
//                                         if((isset($specialMeta->exclude_dae) && !empty($specialMeta->exclude_dae)) || (isset($specialMeta->exclusions) && $specialMeta->exclusions == 'dae'))
//                                         {
//                                             //If DAE selected as an exclusion:
//                                             //- Do not show inventory to use unless
//                                             //--- Stock Display = GPX or ALL
//                                             //AND
//                                             //---OwnerBusCatCode=GPX
//                                             //if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx') && strtolower($prop->OwnerBusCatCode) == 'gpx')
//                                             if((strtolower($prop->StockDisplay) == 'all' || (strtolower($prop->StockDisplay) == 'gpx' || strtolower($prop->StockDisplay) == 'usa gpx')) && (strtolower($prop->OwnerBusCatCode) == 'gpx' || strtolower($prop->OwnerBusCatCode) == 'usa gpx'))
//                                             {
//                                                 //all good we can show these properties
//                                             }
//                                             else
//                                             {
//                                                 $skip = true;
//                                             }
//                                         }
                                        //exclude resorts
                                        if(isset($specialMeta->exclude_resort) && !empty($specialMeta->exclude_resort))
                                        {
                                            foreach($specialMeta->exclude_resort as $exc_resort)
                                            {
                                                if($exc_resort == $prop->RID)
                                                {
                                                    $skip = true;
                                                    break;
                                                }
                                            }
                                        }
                                        //exclude regions
                                        if(isset($specialMeta->exclude_region) && !empty($specialMeta->exclude_region))
                                        {
                                            $exclude_regions = json_decode($specialMeta->exclude_region);
                                            foreach($exclude_regions as $exclude_region)
                                            {
                                                $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE id='".$exclude_region."'";
                                                $excludeLftRght = $wpdb->get_row($sql);
                                                $excleft = $excludeLftRght->lft;
                                                $excright = $excludeLftRght->rght;
                                                $sql = "SELECT * FROM wp_gpxRegion WHERE lft>=".$excleft." AND rght<=".$excright;
                                                $excregions = $wpdb->get_results($sql);
                                                if(isset($excregions) && !empty($excregions))
                                                {
                                                    foreach($excregions as $excregion)
                                                    {
                                                        if($excregion->id == $prop->gpxRegionID)
                                                        {
                                                            $skip = true;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        //exclude home resort
                                        if(isset($specialMeta->exclusions) && $specialMeta->exclusions == 'home-resort')
                                        {
                                            if(isset($usermeta) && !empty($usermeta))
                                            {
                                                $ownresorts = array('OwnResort1', 'OwnResort2', 'OwnResort3');
                                                foreach($ownresorts as $or)
                                                {
                                                    if(isset($usermeta->$or))
                                                        if($usermeta->$or == $prop->ResortName)
                                                            $skip = true;
                                                }
                                            }
                                        }
                                        
                                        //lead time
                                        $today = date('Y-m-d');
                                        if(isset($specialMeta->leadTimeMin) && !empty($specialMeta->leadTimeMin))
                                        {
                                            $ltdate = date('Y-m-d', strtotime($prop->checkIn." -".$specialMeta->leadTimeMin." days"));
                                            if($today > $ltdate)
                                                $skip = true;
                                        }
                                        
                                        if(isset($specialMeta->leadTimeMax) && !empty($specialMeta->leadTimeMax))
                                        {
                                            $ltdate = date('Y-m-d', strtotime($prop->checkIn." -".$specialMeta->leadTimeMax." days"));
                                            if($today < $ltdate)
                                                $skip = true;
                                        }
                                        if(isset($specialMeta->bookStartDate) && !empty($specialMeta->bookStartDate))
                                        {
                                            $bookStartDate = date('Y-m-d', strtotime($specialMeta->bookStartDate));
                                            if($today < $bookStartDate)
                                                $skip = true;
                                        }
                                        
                                        if(isset($specialMeta->bookEndDate) && !empty($specialMeta->bookEndDate))
                                        {
                                            $bookEndDate = date('Y-m-d', strtotime($specialMeta->bookEndDate));
                                            if($today > $bookEndDate)
                                                $skip = true;
                                        }
                                        
                                        if(!$skip)
                                        {
                                                if(isset($_REQUEST['promo_debug']))
                                                {
                                                    echo '<pre>'.print_r($row->id, true).'</pre>';
                                                }
                                            //was this promo already applied?
                                            if(in_array($row->id, $thisPromo))
                                            {
                                                continue;
                                            }
                                            $thisPromo[] = $row->id;
                                            
                                            if(isset($specialMeta->terms))
                                            {
                                                if(!in_array($specialMeta->terms, $promoTerms))
                                                {
                                                    $promoTerms[$i] = $specialMeta->terms;
                                                }
                                            }
                                            if(isset($specialMeta->icon) && isset($specialMeta->desc))
                                            {
                                                $prop->specialIcon = $specialMeta->icon;
                                                $prop->specialDesc = $specialMeta->desc;
                                            }
                                            /*
                                             * slashes not working -- adding the specialmeta slash option
                                             */
                                            if(isset($specialMeta->slash))
                                            {
                                                $prop->slash = $specialMeta->slash;
                                            }
                                            if(isset($specialMeta->acCoupon) && $specialMeta->acCoupon == 1)
                                            {
                                                $autoCreateCoupons[] = $row->id;
                                            }
                                            if($specialMeta->promoType != 'Set Amt' && $row->Amount == 0) //if the amount isn't set then no discounts apply skip the rest
                                            {
                                                continue;
                                            }
                                            $singleUpsellDiscount = false;
                                            if(!empty($upsell))
                                            {
                                                if(count($specialMeta->transactionType) == 1)
                                                {
                                                    $singleUpsellDiscount = true;
                                                }
                                                $prop->upsellDisc = $upsell;
                                            }
                                            if(!$singleUpsellDiscount)
                                            {
                                                $promoName = $row->Name;
                                                $discountType = $specialMeta->promoType;
                                                $discount = $row->Amount;
                                                
                                                if($discountType == 'Pct Off')
                                                {
                                                    $thisSpecialPrice = number_format($specialDiscountPrice*(1-($discount/100)), 2);
                                                    if( ( isset($specialPrice) && ( $thisSpecialPrice < $specialPrice || empty( $specialPrice )  ) ) || empty($specialPrice) )
                                                    {
                                                        $specialPrice = $thisSpecialPrice;
                                                        $thisDiscounted = true;
                                                    }
                                                }
                                                elseif($discountType == 'Dollar Off')
                                                {
                                                    $thisSpecialPrice = $specialDiscountPrice-$discount;
                                                    if( ( isset($specialPrice) && ( $thisSpecialPrice < $specialPrice || empty( $specialPrice )  ) ) || empty($specialPrice) )
                                                    {
                                                        $specialPrice = $thisSpecialPrice;
                                                        $thisDiscounted = true;
                                                    }
                                                }
                                                elseif($discountType == 'Set Amt')
                                                {
                                                    if($discount < $prop->Price)
                                                    {
                                                        $thisSpecialPrice = $discount;
                                                        if( ( isset($specialPrice) && ( $thisSpecialPrice < $specialPrice || empty( $specialPrice )  ) ) || empty($specialPrice) )
                                                        {
                                                            $specialPrice = $thisSpecialPrice;
                                                            $thisDiscounted = true;
                                                        }
                                                    }
                                                    else
                                                    {
                                                        continue;
                                                    }
                                                }
                                                elseif($discount < $prop->Price)
                                                {
                                                    $specialPrice = $discount;
                                                    if( ( isset($specialPrice) && ( $thisSpecialPrice < $specialPrice || empty( $specialPrice )  ) ) || empty($specialPrice) )
                                                    {
                                                        $specialPrice = $thisSpecialPrice;
                                                        $thisDiscounted = true;
                                                    }
                                                    
                                                }
                                                if(isset($bogoPrice))
                                                {
                                                    $specialPrice = $bogoPrice;
                                                    if( ( isset($specialPrice) && ( $thisSpecialPrice < $specialPrice || empty( $specialPrice )  ) ) || empty($specialPrice) )
                                                    {
                                                        $specialPrice = $thisSpecialPrice;
                                                        $thisDiscounted = true;
                                                    }
                                                }
                                                if($specialPrice <= 0)
                                                {
                                                    $specialPrice = '0.00';
                                                }
                                                if(isset($specialMeta->stacking) && $specialMeta->stacking == 'No' && $specialPrice > 0)
                                                {
                                                    //check if this amount is less than the other promos
                                                    if($discountType == 'Pct Off')
                                                    {
                                                        $thisStackSpecialPrice = number_format($prop->Price*(1-($discount/100)), 2);
                                                        if( ( isset($stackPrice) && ( $thisStackSpecialPrice < $stackPrice || empty( $stackPrice )  ) ) || empty($stackPrice) )
                                                        {
                                                            $stackPrice = $thisStackSpecialPrice;
                                                            $thisDiscounted = true;
                                                        }
                                                    }
                                                    elseif($discountType == 'Dollar Off')
                                                    {
                                                        $thisStackSpecialPrice = $prop->Price-$discount;
                                                        if( ( isset($stackPrice) && ( $thisStackSpecialPrice < $stackPrice || empty( $stackPrice )  ) ) || empty($stackPrice) )
                                                        {
                                                            $stackPrice = $thisStackSpecialPrice;
                                                            $thisDiscounted = true;
                                                        }
                                                    }
                                                    elseif($discount < $prop->Price)
                                                    {
                                                        $thisStackSpecialPrice = $discount;
                                                        if( ( isset($stackPrice) && ( $thisStackSpecialPrice < $stackPrice || empty( $stackPrice )  ) ) || empty($stackPrice) )
                                                        {
                                                            $stackPrice = $thisStackSpecialPrice;
                                                            $thisDiscounted = true;
                                                        }
                                                    }
                                                    if($stackPrice != 0 && $stackPrice < $specialDiscountPrice)
                                                    {
                                                        $thisSpecialPrice = $stackPrice;
                                                        if( ( isset($specialPrice) && ( $thisSpecialPrice > $specialPrice || empty( $specialPrice )  ) ) || empty($specialPrice) )
                                                        {
                                                            $specialPrice = $thisSpecialPrice;
                                                            $thisDiscounted = true;
                                                        }
                                                    }
                                                    else
                                                    {
                                                        unset($promoTerms[$i]);
                                                        $thisSpecialPrice = $specialDiscountPrice;
                                                        if( ( isset($specialPrice) && ( $thisSpecialPrice > $specialPrice || empty( $specialPrice )  ) ) || empty($specialPrice) )
                                                        {
                                                            $specialPrice = $thisSpecialPrice;
                                                            $thisDiscounted = true;
                                                        }
                                                    }
                                                }
                                                if(isset($promoTerms[$i]))
                                                {
                                                    if(isset($specialMeta->icon) && isset($specialMeta->desc))
                                                    {
                                                        $prop->specialIcon = $specialMeta->icon;
                                                        $prop->specialDesc .= $specialMeta->desc;
                                                    }
                                                }
                                                            
                                                $specialDiscountPrice = $specialPrice;
                                            }
                                            $activePromos[] = $row->id;
                                        }
                            }
                            $i++;
                }
            }
//             $discountAmt = $discount;
            $discountAmt = $stackPrice;
            if($discountType == 'Auto Create Coupon')
            {
                $discountAmt = '';
                $discount = '';
            }
            if($discountType == 'Set Amt')
                $discountAmt = $prop->Price - $discount;
                $data = array('prop'=>$prop,
                    'discount'=>$discount,
                    'discountAmt'=>$discountAmt,
                    'specialPrice'=>$specialPrice,
                    'promoTerms'=>$promoTerms,
                );
                if(isset($lpid) && !empty($lpid))
                    $data['lpid'] = $lpid;
                    if(!empty($promoName))
                        $data['promoName'] = $promoName;
                        if(!empty($activePromos))
                            $data['activePromos'] = $activePromos;
                            if(!empty($bogoSet))
                                $data['bogo'] = $bogoSet;
                                if(isset($autoCreateCoupons) && !empty($autoCreateCoupons))
                                    $data['autoCoupons'] = $autoCreateCoupons;
//                 $exclusiveWeeks = get_exclusive_weeks($prop, $cid);
//                 if(!empty($exclusiveWeeks))
//                 {
//                     $data['error'] = 'Exclusive Week';
//                 }
    }
    else
    {
        $data = array('error'=>'property');
    }
    
    if(isset($_REQUEST['promo_debug']))
    {
        echo '<pre>'.print_r($data['discount'], true).'</pre>';
        echo '<pre>'.print_r($data['discountAmt'], true).'</pre>';
        echo '<pre>'.print_r($thisPromo, true).'</pre>';
        echo '<pre>'.print_r($activePromos, true).'</pre>';
        echo '<pre>'.print_r($data['prop'], true).'</pre>';
    }
    
    return $data;
}
    
        function save_search($user='', $search, $type, $resorts='', $props='', $cid='')
        {
                global $wpdb;
                $propselects = array('id', 'WeekType', 'WeekPrice', 'Price', 'resortName', 'resortId', 'weekId');
                if(isset($props) && !empty($props))
                {
                    foreach($props as $key=>$val)
                    {
                        if(!in_array($key, $propselects))
                            unset($props->$key);
                    }
                }
                $searchTime = time();
                $sessionRow = '';
                if(isset($user->searchSessionID))
                {
                $sesExp = explode('-', $user->searchSessionID);
                $userID = $sesExp[0];
                $dt = new DateTime("@$sesExp[1]");
                $last_login = $dt->format('m/d/y h:i:s');


                $userType = 'Owner';
                $loggedinuser =  get_current_user_id();
                if($loggedinuser != $cid)
                        $userType = 'Agent';

                
                }
                if(!is_user_logged_in())
                {
                    
                    if(isset($_COOKIE['guest-searchSessionID']))
                    {
                        $user->seachSessionID = $_COOKIE['guest-searchSessionID'];
                        $cid = '84521';
                        $userID = '84521';
                    }
                    else 
                    {
                        if(!isset($user))
                        {
                            $user = new stdClass();
                        }
                        ob_start();
                        //set the cookie
                        $user->searchSessionID = time().rand(1,9).'-'.time();
                        ob_start();
                        setcookie('guest-searchSessionID', $user->searchSessionID);
                        ob_end_flush();
                        $cid = '84521';
                        $userID = '84521';
                    }
                    $userType = 'Guest';
                }
                if(!isset($user->searchSessionID))
                {
                    return true;
                }
                $sql = "SELECT * FROM wp_gpxMemberSearch WHERE sessionID='".$user->searchSessionID."'";
                $sessionRow = $wpdb->get_row($sql);
                
                if(isset($sessionRow) && !empty($sessionRow))
                        $sessionMeta = json_decode($sessionRow->data);
                else
                        $sessionMeta = new stdClass();
                if(!empty($resorts))
                        foreach($resorts as $key=>$value)
                        {
                                $summary[$searchTime]['resorts'][$key]['name'] = $value['resort']->resortName;

                                foreach($value['props'] as $prop)
                                {
                                        $summary[$searchTime]['resorts'][$key]['props'] = array(
                                                'price'=>$prop->Price,
                                                'checkIn'=>$prop->checkIn,
                                        );
                                        if(isset($prop->specialPrice))
                                                $summary[$searchTime]['resorts'][$key]['props']['specialPrice'] = $prop->specialPrice;
                                }
                        }
                $refpage = '';
                if(isset($_SERVER['HTTP_REFERER']))
                        $refpage = $_SERVER['HTTP_REFERER'];
                switch($type)
                {
                        case 'search':
                                $data = array('search'=>
                                    array(
                                        'last_login'=>$last_login,
                                        'refDomain'=>$refpage,
                                        'currentPage'=>$_SERVER['REQUEST_URI'],
                                        'refPage'=>get_permalink(),
                                        'locationSearched'=>$search,
                                        'searchSummary'=>$summary,
                                        'user_type'=>$userType,
                                        'search_by_id'=>$cid,
                                        ),
                                );
                                $metaKey = $searchTime;
                        break;

                        case 'select':
                                $data = array(
                                        'last_login'=>$last_login,
                                        'refDomain'=>$refpage,
                                        'currentPage'=>$_SERVER['REQUEST_URI'],
                                        'property'=>$props['prop'],
                                        'price'=>$props['prop']->Price,
                                        'user_type'=>$userType,
                                        'search_by_id'=>$cid,
                                );
                                if(isset($prop->specialPrice))
                                        $data['select']['specialPrice'] = $prop->specialPrice;
                                $metaKey = 'select-'.$props['prop']->resortID;
                        break;

                        case 'book':

                        case 'ICE':
                            $data = array('ICE'=>
                            array(
                            'last_login'=>$last_login,
                            'refDomain'=>$refpage,
                            'currentPage'=>$_SERVER['REQUEST_URI'],
                            'user_type'=>$userType,
                            'search_by_id'=>$cid,
                            ));
                            $metaKey = 'ICE';
                            break;
                }
                
                $sessionMeta->$metaKey = $data;
                $sessionMetaJson = json_encode($sessionMeta);
                $searchCartID = '';
                if(isset($_COOKIE['gpx-cart']))
                    $searchCartID = $_COOKIE['gpx-cart'];
                    if(isset($sessionRow))
                        $wpdb->update('wp_gpxMemberSearch', array('userID'=>$userID, 'sessionID'=>$user->searchSessionID, 'cartID'=>$searchCartID, 'data'=>$sessionMetaJson), array('id'=>$sessionRow->id));
                        else
                            $wpdb->insert('wp_gpxMemberSearch', array('userID'=>$userID, 'sessionID'=>$user->searchSessionID, 'cartID'=>$searchCartID, 'data'=>$sessionMetaJson));
                            
                            if($userType == 'Guest')
                            {
                                return array('guest-searchSessionID'=>$user->searchSessionID);
                            }
                            else 
                            {
                                return $searchTime;
                            }
        }

        function save_search_book($post)
        {
                global $wpdb;

                extract($post);

                $user = get_userdata($cid);
                if(isset($user) && !empty($user))
                        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );

                $userType = 'Owner';
                $loggedinuser =  get_current_user_id();
                if($loggedinuser != $cid)
                        $userType = 'Agent';

                        $searchTime = time();
                        $sessionRow = '';
                        if(isset($user->searchSessionID))
                        {
                                $sesExp = explode('-', $user->searchSessionID);
                                $userID = $sesExp[0];
                                $dt = new DateTime("@$sesExp[1]");
                                $last_login = $dt->format('m/d/y h:i:s');

                                $sql = "SELECT * FROM wp_gpxMemberSearch WHERE sessionID='".$user->searchSessionID."'";
                                $sessionRow = $wpdb->get_row($sql);
                        }
//             if(isset($sessionRow) && !empty($sessionRow))
//                 $sessionMeta = json_decode($sessionRow->data);
//             else

                        $sessionMeta = new stdClass();

                        $prop = get_property_details($pid, $cid);

                        if(isset($select_month))
                                $month = $select_month;
                        else
                                $month = date('F');

                        if(isset($select_year))
                                $year = $select_year;
                        else
                                $year = date('Y');

                        $refpage = '';
                        if(isset($_SERVER['HTTP_REFERER']))
                                $refpage = $_SERVER['HTTP_REFERER'];
                        $data = array(
                                                'refDomain'=>$refpage,
                                                'currentPage'=>$_SERVER['REQUEST_URI'],
                                                'week_type'=>$prop['prop']->WeekType,
                                                'price'=>$prop['prop']->WeekPrice,
                                                'id'=>$pid,
                                                'name'=>$prop['prop']->resortName,
                                                'checkIn'=>date('m/d/Y', strtotime($prop['prop']->checkIn)),
                                                'beds'=>$prop['prop']->bedrooms." / ".$prop['prop']->sleeps,
                                                'search_location'=>$location,
                                                'search_month'=>$month,
                                                'search_year'=>$year,
                            'user_type'=>$userType,
                            'search_by_id'=>$cid,
                                                );
                        if(isset($prop['specialPrice']))
                                $data['select']['specialPrice'] = $prop['specialPrice'];

                        $metaKey = 'view-'.$pid;

                        $sessionMeta->$metaKey = $data;
                        $sessionMetaJson = json_encode($sessionMeta);
                        $searchCartID = '';
                        if(isset($_COOKIE['gpx-cart']))
                                $searchCartID = $_COOKIE['gpx-cart'];
//             if(isset($sessionRow))
//                 $wpdb->update('wp_gpxMemberSearch', array('userID'=>$userID, 'sessionID'=>$user->searchSessionID, 'data'=>$sessionMetaJson), array('id'=>$sessionRow->id));
//             else
                        $wpdb->insert('wp_gpxMemberSearch', array('userID'=>$userID, 'sessionID'=>$user->searchSessionID, 'cartID'=>$searchCartID, 'data'=>$sessionMetaJson));

                        return $searchTime;
        }

        function save_search_resort($resort='', $post='')
        {
                global $wpdb;

                extract($post);

                $user = get_userdata($cid);
                if(isset($user) && !empty($user))
                        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );

                $userType = 'Owner';
                $loggedinuser =  get_current_user_id();
                if($loggedinuser != $cid)
                        $userType = 'Agent';

                $searchTime = time();
                $sessionRows = '';
                if(isset($user->searchSessionID))
                {
                        $sesExp = explode('-', $user->searchSessionID);
                        $userID = $sesExp[0];
                        $dt = new DateTime("@$sesExp[1]");
                        $last_login = $dt->format('m/d/y h:i:s');

                        $sql = "SELECT * FROM wp_gpxMemberSearch WHERE sessionID='".$user->searchSessionID."'";
                        $sessionRows = $wpdb->get_results($sql);
                }
                if(isset($sessionRows) && !empty($sessionRows))
                {
                        foreach($sessionRows as $sessionRow)
                        {
                                $sessionMeta = json_decode($sessionRow->data);
                                $sessionMetaArray = (array) $sessionMeta;
                                $sessionKeys[] = key($sessionMetaArray);
                                $sessionMetas[key($sessionMetaArray)] = $sessionMeta;
                        }
                }
                else
                        $sessionMeta = new stdClass();

                if(isset($select_month))
                        $month = $select_month;
                else
                        $month = date('F');

                if(isset($select_year))
                        $year = $select_year;
                else
                        $year = date('Y');

                $refpage = '';
                if(isset($_SERVER['HTTP_REFERER']))
                        $refpage = $_SERVER['HTTP_REFERER'];

                $metaKey = 'resort-'.$resort->id;

                if(isset($rid) && !empty($rid))//set the data the first time
                {

                        $sql = "SELECT * FROM wp_resorts WHERE id='".$rid."'";
                        $resort = $wpdb->get_row($sql);

                        $metaKey = 'resort-'.$resort->id;

                        if(isset($sessionKeys) && in_array($metaKey, $sessionKeys))
                        {
                                $data = (array) $sessionMetas[$metaKey];
                                $data[$metaKey]->DateViewed = date('m/d/Y h:i:s');
                                $data[$metaKey]->search_location = $location;
                                $data[$metaKey]->search_month = $month;
                                $data[$metaKey]->search_year =$year;
                                $data[$metaKey]->user_type = $userType;

                        }
                        else
                                $data[$metaKey] = array(
                                        'refDomain'=>$refpage,
                                        'currentPage'=>$_SERVER['REQUEST_URI'],
                                        'ResortName'=>$resort->ResortName,
                                        'DateViewed'=>date('m/d/Y h:i:s'),
                                        'id'=>$resort->id,
                                        'search_location'=>$location,
                                        'search_month'=>$month,
                                        'search_year'=>$year,
                                    'user_type'=>$userType,
                                    'search_by_id'=>$cid,
                                );
                }
                else //viewed directly
                {
                        if(isset($sessionKeys) && in_array($metaKey, $sessionKeys))
                        {
                                $data = (array) $sessionMetas[$metaKey];
                                $data[$metaKey]->user_type = $userType;
                                $data[$metaKey]->DateViewed = date('m/d/Y h:i:s');
                        }
                        else
                                $data[$metaKey] = array(
                                        'refDomain'=>$refpage,
                                        'currentPage'=>$_SERVER['REQUEST_URI'],
                                        'ResortName'=>$resort->ResortName,
                                        'DateViewed'=>date('m/d/Y h:i:s'),
                                        'id'=>$resort->id,
                                        'user_type'=>$userType,
                                        'search_by_id'=>$cid,
                                );
                }
                $sessionMetaJson = json_encode($data);
                $searchCartID = '';
                if(isset($_COOKIE['gpx-cart']))
                        $searchCartID = $_COOKIE['gpx-cart'];
                if(isset($sessionMeta->$metaKey))
                        $wpdb->update('wp_gpxMemberSearch', array('userID'=>$userID, 'sessionID'=>$user->searchSessionID, 'cartID'=>$searchCartID, 'data'=>$sessionMetaJson), array('id'=>$sessionRow->id));
                else
                        $wpdb->insert('wp_gpxMemberSearch', array('userID'=>$userID, 'sessionID'=>$user->searchSessionID, 'cartID'=>$searchCartID, 'data'=>$sessionMetaJson));

                return $searchTime;
        }

        function get_property_details_checkout($cid, $ccid='', $ocid='', $checkoutcid='')
        {
            global $wpdb;
            
            if(!empty($ccid) && $ccid != $cid)
            {
                $cid = $ccid;
            }
            if(!empty($checkoutcid))
            {
                $cid = $checkoutcid;
            }
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
            
            $sql = "SELECT DISTINCT propertyID, data FROM wp_cart WHERE cartID='".$_COOKIE['gpx-cart']."'";
            $rows = $wpdb->get_results($sql);
            
            $couponDiscount = '';
            $cartCredit = '';
            $indCartOCCreditUsed = [];
            if(!empty($rows))
            {
                $finalPrice = '';
                $rn = array();
                $spOut = array();
                $indPrice = array();
                $actIndPrice = array();
                $fullPrice = array();
                $fees = array();
                $cpoFee = '';
                $CPO = '';
                $upgradeFee = '';
                $extensionFee = '';
                $spSum = '';
                $gfSlash = '';
                $couponDiscount = 0;
                $zzz = 0;
                foreach($rows as $row)
                {
                    $zzz++;

                    $cart = json_decode($row->data);

                    $specialPrice = '';
                    $discount = '';
                    
                    $book = $row->propertyID;
                    
                    $property_details = get_property_details($book, $cid);
                    extract($property_details);
                    
                    //get guest fees
                    if(isset($cart->GuestFeeAmount) && $cart->GuestFeeAmount == 1)
                    {
                        if(isset($prop->GuestFeeAmount) && !empty($prop->GuestFeeAmount))
                            $gfAmt = $prop->GuestFeeAmount;
                            elseif(get_option('gpx_global_guest_fees') == '1' && (get_option('gpx_gf_amount') && get_option('gpx_gf_amount') > $gfAmount))
                            $gfAmt = get_option('gpx_gf_amount');
                            
                    }
                    
                    //get late Deposit fees
                    if(isset($cart->late_deposit_fee) && $cart->late_deposit_fee > 1)
                    {
                        $ldFeeAmt = $cart->late_deposit_fee;
                    }

                    if($prop->WeekType == 'ExchangeWeek')
                        $CPO[$book] = 'NotTaken';
                        
                        if(isset($cart->CPOPrice) && !empty($cart->CPOPrice))
                        {
                            //cpo shouldn't be available within 45 days and never on bonus/rental
                            if($prop->WeekType == 'ExchangeWeek')
                            {
                                if(strtotime($prop->checkIn) > strtotime('+45 days'))
                                {
                                    $CPO[$book] = 'Taken';
                                }
                                else
                                {
                                    $cart->CPOPrice = 0;
                                }
                            }
                        }
                        
                        
                        $props[$book] = $prop;
                        
                        $pp[] = str_replace(",", "", $prop->Price);
                        
                        if(empty($prop->Price))
                            $prop->Price = preg_replace("/[^0-9\.]/", "",$prop->WeekPrice);
                            
                            $indPrice[$book] = str_replace(",", "", $prop->Price);
                            $actIndPrice[$book]['WeekPrice'] = $prop->Price;
                            $fullPrice[$book] = $indPrice[$book];
                            
                            
                            
                            if(isset($specialPrice) && !empty($specialPrice))
                            {
                                $cmpSP = preg_replace("/[^0-9\.]/", "",$specialPrice);
                                if(empty($prop->Price))
                                    $cmpP = preg_replace("/[^0-9\.]/", "",$prop->WeekPrice);
                                    else
                                        $cmpP = preg_replace("/[^0-9\.]/", "",$prop->Price);
                                        if($cmpP - $cmpSP != 0)
                                        {
                                            $specialPrice = str_replace(",", "", $specialPrice);
                                            $spOut[$book] = $specialPrice;
                                            $spDiscount[$book] = $prop->Price - $specialPrice;
                                            $indPrice[$book] = $specialPrice;
                                            
                                            $actIndPrice[$book]['WeekPrice'] = $specialPrice;
                                            $finalPrice = $finalPrice + $specialPrice;
                                        }
                                        else
                                        {
                                            if(empty($prop->Price))
                                                $prop->Price = preg_replace("/[^0-9\.]/", "",$prop->WeekPrice);
                                                
                                                $indPrice[$book] = str_replace(",", "", $prop->Price);
                                                
                                                
                                                $actIndPrice[$book]['WeekPrice'] = $indPrice[$book];
                                                $finalPrice = $finalPrice + $prop->Price;
                                        }
                            }
                            else
                            {
                                if(empty($prop->Price))
                                    $prop->Price = preg_replace("/[^0-9\.]/", "",$prop->WeekPrice);
                                    
                                    $indPrice[$book] = str_replace(",", "", $prop->Price);
                                    
                                    
                                    $actIndPrice[$book]['WeekPrice'] = $indPrice[$book];
                                    $finalPrice = $finalPrice + $prop->Price;
                            }
                            
                            
                            if(isset($cart->credit) && empty($cartCredit))
                            {
                                $finalPrice = $finalPrice - $cart->credit;
                                $indPrice[$book] = $indPrice[$book] - $cart->credit;
                                
                                
                                $actIndPrice[$book]['WeekPrice'] = $indPrice[$book];
                                $cartCredit = $cart->credit;
                            }
                            if(isset($property_details['prop']->upsellDisc) && !empty($property_details['prop']->upsellDisc))
                            {
                                $upsellDisc = $property_details['prop']->upsellDisc;
                            }
                            $datech = date('m/d/Y', strtotime($prop->checkIn.' -45 days'));
                            if(strtotime(date('m/d/Y')) <=  strtotime($datech))
                            {
                                $cpoFee = $cart->CPOPrice;
                                if(isset($cart->CPOPrice) && !empty($cart->CPOPrice))
                                {
                                    if(isset($upsellDisc))
                                    {
                                        foreach($upsellDisc as $usd)
                                        {
                                            if($usd['option'] == 'CPO' || in_array('CPO', $usd['option']))
                                            {
                                                if($usd['type'] == 'Pct Off')
                                                    $cpoDisc = $cpoFee*($usd['amount']/100);
                                                    else
                                                        $cpoDisc = $usd['amount'];
                                                        if($cpoDisc > $cpoFee)
                                                            $cpoDisc = $cpoFee;
                                                            
                                                            $cpoSlash = $cpoFee;
                                                            $indCPOSlash[$book] = $cpoFee;
                                                            $totalCPOSlash += $cpoSlash;
                                                            $cpoFee = $cpoFee - $cpoDisc;
                                            }
                                        }
                                    }
                                    $indCPOFee[$book] = $cpoFee;
                                    $totalCPOFee += $cpoFee;
                                    $finalPrice = $finalPrice + $cpoFee;
                                    $indPrice[$book] = $indPrice[$book] + $cpoFee;
                                    $fees[] = $cpoFee;
                                    $indFees[$book][] = $cpoFee;
                                    
                                    
                                    $actIndPrice[$book]['cpoFee'] = $cpoFee;
                                }
                            }
                            if(isset($cart->creditextensionfee) && $cart->creditextensionfee > 0)
                            {
                                $extensionFee = $cart->creditextensionfee;
                                $indExtFee[$book] = $extensionFee;
                                $indExtFee[$book] = $extensionFee;
                                $totalExtFee += $extensionFee;
                                $finalPrice = $finalPrice + $extensionFee;
                                $indPrice[$book] = $indPrice[$book] + $extensionFee;
                                
                                
                                $actIndPrice[$book]['extensionFee'] = $extensionFee;
                                $fees[] = $extensionFee;
                                $indFees[$book][] = $extensionFee;
                            }
                            if(isset($cart->creditvalue) && $cart->creditvalue > 0)
                            {
                                $indUpgrade[$book] = $upgradeFee;
                                $upgradeFee = $upgradeFee + $cart->creditvalue;
                                if(empty($indUpgrade[$book]))
                                {
                                    $indUpgrade[$book] = $upgradeFee;
                                }
                                if(isset($upsellDisc) && $upsellDisc['option'] == 'Upgrade')
                                {
                                    foreach($upsellDisc as $usd)
                                    {
                                        if($usd['option'] == 'Upgrade' || in_array('Upgrade',$usd['option']))
                                        {
                                            
                                            if($usd['type'] == 'Pct Off')
                                                $upgradeDisc = number_format($upgradeFee*($usd['amount']/100),2);
                                                else
                                                    $upgradeDisc = $upgradeFee-$usd['amount'];
                                                    
                                                    if($upgradeDisc > $upgradeFee)
                                                        $upgradeDisc = $upgradeFee;
                                                        $upgradeSlash = $upgradeFee;
                                                        $indUpgradeFeeSlash[$book] = $upgradeFee;
                                                        $upgradeFee = $upgradeFee - $upgradeDisc;
                                                        $indUpgrade[$book] = $indUpgrade[$book] - $upgradeDisc;
                                        }
                                    }
                                }
                                $fees[] = $upgradeFee;
                                $indFees[$book][] = $upgradeFee;
                                $indPrice[$book] = $indUpgrade[$book] + $indPrice[$book];
                                
                                $actIndPrice[$book]['upgradeFee'] = $upgradeFee;
                                $finalPrice = $finalPrice + $indUpgrade[$book];
                            }
                            if(isset($gfAmt) && !empty($gfAmt))
                            {
                                $gfAdd = true;
                                $indPrice[$book] += $gfAmt;
                                
                                
                                $actIndPrice[$book]['guestFee'] = $gfAmt;
                                $upsellDisc = $property_details['prop']->upsellDisc;
                                if(isset($property_details['prop']->upsellDisc))
                                {
                                    $upsellDisc = $property_details['prop']->upsellDisc;
                                }
                                
                                if(isset($upsellDisc))
                                {
                                    foreach($upsellDisc as $usd)
                                    {
                                        if($usd['option'] == 'Guest Fees' ||  in_array('Guest Fees', $usd['option']))
                                        {
                                            if($usd['type'] == 'Pct Off')
                                            {
                                                $gfDisc = number_format($gfAmt*($usd['amount']/100),2);
                                            }
                                            elseif($usd['type'] == 'Set Amt')
                                            {
                                                $gfDisc = $gfAmt;
                                            }
                                            else
                                            {
                                                $gfDisc = $usd['amount'];
                                            }
                                            if($gfDisc > $gfAmt)
                                            {
                                                $gfDisc = $gfAmt;
                                            }
                                                
                                            $indGFSlash[$book] = $gfAmt;
                                            $gfSlash = $gfSlash + $gfAmt;
                                            
                                            if($indPrice[$book] < $gfDisc)
                                            {
                                                $gfDisc = $indPrice[$book];
                                            }
                                            
                                            $gfAmt = $gfAmt - $gfDisc;
                                            
                                            $indPrice[$book] = $indPrice[$book] - $gfDisc;
                                            
                                            
                                            $actIndPrice[$book]['guestFee'] = $gfAmt;
                                            
                                        }
                                    }
                                }
                                $indFees[$book][] = $gfAmt;
                                $discGuestFee[$book] = $gfAmt;
                            }
                            if(isset($cart->coupon))
                            {
                                
                                $couponDiscount = 0;
                                foreach($cart->coupon as $activeCoupon)
                                {
                                    
                                    //verify that this coupon was only applied once -- if this is in the array then we already applied it
                                    if(isset($couponused[$activeCoupon]) && $couponused[$activeCoupon] == $book)
                                    {
                                        continue;
                                    }
                                    $couponused[$activeCoupon] = $book;
                                    //                         echo '<pre>'.print_r($couponDiscount, true).'</pre>';
                                    $startFinalPrice = $finalPrice;
                                    $sql = "SELECT id, Properties, Amount FROM wp_specials WHERE id='".$activeCoupon."'";
                                    $active = $wpdb->get_row($sql);
                                    $activeProp = stripslashes_deep( json_decode($active->Properties) );
                                    
                                    if(($couponkey > 20 && $book != $couponkey) && ($activeProp->promoType != 'BOGO' || $activeProp->promoType != 'BOGOH'))
                                    {
                                        continue;
                                    }
                                    
                                    $discountTypes = array(
                                        'Pct Off',
                                        'Dollar Off',
                                        'Set Amt',
                                        'BOGO',
                                        'BOGOH',
                                    );
                                    foreach($discountTypes as $dt)
                                    {
                                        if(strpos($activeProp->promoType, $dt))
                                            $activeProp->promoType = $dt;
                                    }
                                    
                                    if(isset($activeProp->acCoupon) && $activeProp->acCoupon == 1)
                                    {
                                        $couponTemplate = $activeProp->couponTemplate;
//                                         if(in_array($active->id, $activePromos)) // skip all the pricing when it is an auto create coupon and the promo exists
//                                         {
                                            unset($couponDiscount);
                                            continue;
//                                         }
                                    }
                                        
                                        $singleUpsellOption = false;
                                        if(isset($activeProp->upsellOptions) && !empty($activeProp->upsellOptions))
                                        {
                                            if(count($activeProp->transactionType) == 1)
                                            {
                                                $singleUpsellOption = true;
                                            }
                                            foreach($activeProp->upsellOptions as $upsellOption)
                                            {
                                                switch($upsellOption)
                                                {
                                                    case 'CPO':
                                                        if(isset($cpoFee) && !empty($cpoFee))
                                                        {
                                                            if($activeProp->promoType == 'Pct Off')
                                                            {
                                                                $couponDiscount = number_format($cpoFee*($active->Amount/100),2);
                                                                
//                                                                 $actIndPrice[$book]['cpoFee'] = $actIndPrice[$book]['cpoFee'] - $couponDiscount;
                                                            }
                                                                else
                                                                {
                                                                    $couponDiscount = $cpoFee-$active->Amount;
                                                                    
//                                                                     $actIndPrice[$book]['cpoFee'] = $actIndPrice[$book]['cpoFee'] - $couponDiscount;
                                                                }
                                                                    
                                                                    if($couponDiscount > $cpoFee)
                                                                    {
                                                                        $couponDiscount = $cpoFee;
                                                                        
//                                                                         $actIndPrice[$book]['cpoFee'] =$couponDiscount;
                                                                    }
                                                                        
                                                        }
                                                        break;
                                                        
                                                    case 'Upgrade':
                                                        if(isset($upgradeFee) && !empty($upgradeFee))
                                                        {
                                                            if($activeProp->promoType == 'Pct Off')
                                                            {
                                                                $couponDiscount = number_format($upgradeFee*($active->Amount/100),2);
                                                                
                                                                
//                                                                 $actIndPrice[$book]['upgradeFee'] = $actIndPrice[$book]['upgradeFee'] - $couponDiscount;
                                                            }
                                                                else
                                                                {
                                                                    $couponDiscount = $upgradeFee-$active->Amount;
                                                                    
//                                                                     $actIndPrice[$book]['upgradeFee'] = $actIndPrice[$book]['upgradeFee'] - $couponDiscount;
                                                                }
                                                                    
                                                                    if($couponDiscount > $upgradeFee)
                                                                    {
                                                                        $couponDiscount = $upgradeFee;
                                                                        
//                                                                         $actIndPrice[$book]['upgradeFee'] = $actIndPrice[$book]['upgradeFee'] - $couponDiscount;
                                                                    }
                                                        }
                                                        break;
                                                        
                                                    case 'Guest Fees':
                                                        if(isset($gfAmt) && !empty($gfAmt))
                                                        {
                                                            if($activeProp->promoType == 'Pct Off')
                                                            {
                                                                $couponDiscount = number_format($gfAmt*($active->Amount/100),2);
                                                                
//                                                                 $actIndPrice[$book]['guestFee'] = $actIndPrice[$book]['guestFee'] - $couponDiscount;
                                                            }
                                                                else
                                                                {
                                                                    $couponDiscount = $active->Amount;
                                                                    
//                                                                     $actIndPrice[$book]['guestFee'] = $actIndPrice[$book]['guestFee'] - $couponDiscount;
                                                                }
                                                                    
                                                                    if($couponDiscount > $gfAmt)
                                                                    {
                                                                        $couponDiscount = $gfAmt;
                                                                        
//                                                                         $actIndPrice[$book]['guestFee'] = $actIndPrice[$book]['guestFee'] - $couponDiscount;
                                                                    }
                                                            }
                                                            $discGuestFee[$book] = $discGuestFee[$book] - $couponDiscount;
                                                                break;
                                                                
                                                    case 'Extension Fees':
                                                        
                                                        break;
                                                }
                                                $upselldisc[$book][$upsellOption] = $couponDiscount;
                                            }
                                            $indCouponDisc[$book] = array_sum($upselldisc[$book]);
                                            if(!empty($couponDiscount))
                                            {
                                                $finalPrice = $finalPrice-$couponDiscount;
                                            }
                                        }
                                        if(!$singleUpsellOption)
                                        {
                                            $pricewofees = ($indPrice[$book] - array_sum($indFees[$book]));
                                            $tt = $activeProp->transactionType;
                                            $bonusExchange = [
                                                'ExchangeWeek',
                                                'BonusWeek'
                                            ];
                                            $any = [
                                                'any'
                                            ];
                                            if($activeProp->promoType == 'Pct Off')
                                            {
                                                if( (!is_array($bonusExchange) && in_array($tt, $bonusExchange)) || (is_array($bonusExchange) && array_intersect($tt, $bonusExchange)))
                                                {
                                                    $poDisc = $pricewofees*(($active->Amount/100));
                                                    $allCoupon[$book] = $poDisc;
                                                    $finalPrice = number_format($finalPrice - $poDisc,2);
                                                }
                                                elseif($tt == 'any' ||  array_intersect($tt, $any))
                                                {
                                                    $poDisc = $indPrice[$book]*(($active->Amount/100));
                                                    $allCoupon[$book] = $poDisc;
                                                    $finalPrice = number_format($finalPrice - $poDisc,2);
                                                }
// //                                                 $actIndPrice[$book]['WeekPrice'] = $actIndPrice[$book]['WeekPrice'] - ($actIndPrice[$book]['WeekPrice'] * ($active->Amount/100));
//                                                 //if an extension fee is set then we need to add it back in to the set amount
//                                                 if(isset($extensionFee))
//                                                 {
//                                                     $allCoupon[$book]  -= $extensionFee * ($active->Amount/100);
//                                                     $finalPrice += $extensionFee * ($active->Amount/100);
                                                    
                                                    
// //                                                     $actIndPrice[$book]['extensionFee'] = $actIndPrice[$book]['extensionFee'] - ( $actIndPrice[$book]['extensionFee'] * ($active->Amount/100));
//                                                 }
                                            
//                                                 //if an guest fee is set then we need to add it back in to the set amount
//                                                 if(isset($gfAmt))
//                                                 {
//                                                     $allCoupon[$book]  -= $gfAmt * ($active->Amount/100);
//                                                     $finalPrice += $gfAmt * ($active->Amount/100);
                                                    
                                                    
// //                                                     $actIndPrice[$book]['guestFee'] = $actIndPrice[$book]['guestFee'] - ($actIndPrice[$book]['guestFee'] * ($active->Amount/100));
//                                                 }
                                                
//                                                 //if an upgrade fee is set then we need to add it back in to the set amount
//                                                 if(isset($upgradeFee))
//                                                 {
//                                                     $allCoupon[$book]  -= $upgradeFee * ($active->Amount/100);
                                                    
// //                                                     $actIndPrice[$book]['upgradeFee'] = $actIndPrice[$book]['upgradeFee'] -  ($actIndPrice[$book]['upgradeFee'] * ($active->Amount/100));
//                                                     $finalPrice += $upgradeFee * ($active->Amount/100);
//                                                 }
//                                                 //if a CPO fee is set then we need to add it back in to the set amount
//                                                 if(isset($cpoFee))
//                                                 {
//                                                     $allCoupon[$book] -= $cpoFee * ($active->Amount/100);
                                                    
// //                                                     $actIndPrice[$book]['cpoFee'] = $actIndPrice[$book]['cpoFee'] - ($actIndPrice[$book]['cpoFee'] * ($active->Amount/100));
//                                                     $finalPrice += $cpoFee * ($active->Amount/100);
//                                                 }
                                                
                                            }
                                            elseif($activeProp->promoType == 'BOGO' || $activeProp->promoType == 'BOGOH')
                                            {
                                                if(isset($cart->couponbogo))
                                                {
                                                    $couponDiscountPrice = $indPrice[$book] - $cart->couponbogo;
                                                    $indBOGOCoupon[$book] = $indPrice[$book] - $cart->couponbogo;
                                                    if($couponDiscountPrice < 0)
                                                    {
                                                        if($activeProp->promoType == 'BOGO')
                                                        {
                                                            $couponDiscountPrice = 0;
                                                            $indBOGOCoupon[$book] = 0;
                                                            
//                                                             $actIndPrice[$book]['WeekPrice'] = 0;
//                                                             $actIndPrice[$book]['cpoFee'] = 0;
//                                                             $actIndPrice[$book]['extensionFee'] = 0;
//                                                             $actIndPrice[$book]['guestFee'] = 0;
//                                                             $actIndPrice[$book]['upgradeFee'] = 0;
                                                        }
                                                        else 
                                                        {
                                                            $couponDiscountPrice = $indFees[$book]/2;
                                                            $indBOGOCoupon[$book] = $indFees[$book]/2;
                                                            
                                                            
//                                                             $actIndPrice[$book]['WeekPrice'] = $actIndPrice[$book]['WeekPrice']/2;
//                                                             $actIndPrice[$book]['cpoFee'] = $actIndPrice[$book]['cpoFee']/2;
//                                                             $actIndPrice[$book]['extensionFee'] = $actIndPrice[$book]['extensionFee']/2;
//                                                             $actIndPrice[$book]['guestFee'] = $actIndPrice[$book]['guestFee']/2;
//                                                             $actIndPrice[$book]['upgradeFee'] = $actIndPrice[$book]['upgradeFee']/2;
                                                        }
                                                    }
                                                    $allCoupon[$book] = $indBOGOCoupon[$book];
                                                    $finalPrice = $finalPrice-$couponDiscountPrice;
                                                }
                                            }
                                            elseif($activeProp->promoType == 'Dollar Off')
                                            {
                                                $finalPrice = $finalPrice-$active->Amount;
                                                $allCoupon[$book]= $active->Amount;
                                                
                                                
//                                                 $actIndPrice[$book]['WeekPrice'] = $actIndPrice[$book]['WeekPrice'] - $active->Amount;
                                                
                                            }
                                            elseif(( in_array($tt, $bonusExchange) || array_intersect($tt, $bonusExchange)) && $active->Amount < $actIndPrice[$book]['WeekPrice'] )
                                            {
                                                $poDisc = $actIndPrice[$book]['WeekPrice']  - $active->Amount;
                                                $allCoupon[$book] = $poDisc;
                                                $finalPrice = number_format($finalPrice - $poDisc,2);
                                            }
                                            elseif(($tt == 'any' ||  array_intersect($tt, $any)) && $active->Amount < $finalPrice)
                                            {
                                                $poDisc = $finalPrice-$active->Amount;
                                                $allCoupon[$book] = $poDisc;
                                                $finalPrice = number_format($active->Amount,2);
                                            }
//                                             $couponDiscount = ($startFinalPrice -$finalPrice);
                                            $couponDiscount = array_sum($allCoupon);
                                            $indCouponDisc[$book] = $allCoupon[$book];
                                            
                                        }
                                        
                                        //is the coupon more than the max value?
                                        if(isset($activeProp->maxValue) && !empty($activeProp->maxValue) && $activeProp->maxValue < $couponDiscount)
                                        {
                                            $maxDiff = $couponDiscount-$activeProp->maxValue;
                                            $couponDiscount = $activeProp->maxValue;
                                            $finalPrice = $finalPrice + $maxDiff;
                                        }
                                        if(isset($couponDiscount) && !empty($couponDiscount))
                                        {
                                            $indPrice[$book] = $indPrice[$book] - $indCouponDisc[$book];
                                            
                                        }
                                }
                            }
                            //add guest fees
                            if(isset($gfAmt))
                            {
                                $finalPrice = $finalPrice + $gfAmt;
                                $GuestFeeAmount += $gfAmt;
                                $indGuestFeeAmount[$book] = $gfAmt;
                                //$indPrice[$book] += $gfAmt;
                            }
                            
                            //add late deposit fee
                            if(isset($ldFeeAmt))
                            {
                                $finalPrice = $finalPrice + $ldFeeAmt;
                                $LateDepositFeeAmount += $ldFeeAmt;
                                $indLateDepositFeeAmount[$book] = $ldFeeAmt;
                                $indPrice[$book] += $ldFeeAmt;
                            }
                            // add/deduct tax from price
                            if($prop->WeekType == 'ExchangeWeek')
                                $ttType = 'gpx_tax_transaction_exchange';
                                else
                                    $ttType = 'gpx_tax_transaction_bonus';
                                    if(get_option($ttType) == '1') //set the tax
                                    {
                                        $sql = "SELECT * FROM wp_gpxTaxes WHERE ID='".$prop->taxID."'";
                                        $tax = $wpdb->get_row($sql);
                                        $taxPercent = '';
                                        $flatTax = '';
                                        for($t=1;$t<=3;$t++)
                                        {
                                            $tp = 'TaxPercent'.$t;
                                            $ft = 'FlatTax'.$t;
                                            if(!empty($tax->$tp))
                                                $taxPercent += $tax->$tp;
                                                if(!empty($tax->$ft))
                                                    $flatTax += $tax->$ft;
                                        }
                                        if(!empty($taxPercent))
                                        {
                                            $finalPrice = str_replace(",", "",$finalPrice);
                                            $finalPriceForTax = $finalPrice;
                                            $preFinalPriceForTax = $finalPrice;
                                            if(isset($GuestFeeAmount) && !empty($GuestFeeAmount))
                                            {
                                                $finalPriceForTax = $finalPriceForTax - $GuestFeeAmount;
                                                if($finalPriceForTax < 0)
                                                {
                                                    $finalPriceForTax = $preFinalPriceForTax;
                                                }
                                                $preFinalPriceForTax = $finalPriceForTax;
                                            }
                                            if(isset($upgradeFee) && !empty($upgradeFee))
                                            {
                                                $finalPriceForTax = $finalPriceForTax - $upgradeFee;
                                                if($finalPriceForTax < 0)
                                                {
                                                    $finalPriceForTax = $preFinalPriceForTax;
                                                }
                                                $preFinalPriceForTax = $finalPriceForTax;
                                            }
                                            if(isset($totalCPOFee) && !empty($totalCPOFee))
                                            {
                                                $finalPriceForTax = $finalPriceForTax - $totalCPOFee;
                                                if($finalPriceForTax < 0)
                                                {
                                                    $finalPriceForTax = $preFinalPriceForTax;
                                                }
                                                $preFinalPriceForTax = $finalPriceForTax;
                                            }
                                            $taxAmount = $finalPriceForTax*($taxPercent/100);
                                        }
                                        if(!empty($flatTax))
                                            $taxAmount += $flatTax;
                                            
                                            if($prop->taxMethod == 2)//deduct from price
                                            {
                                                $taxes[$book]['type'] = 'deduct';
                                                $taxes[$book] = array(
                                                    'taxID'=>$tax->ID,
                                                    'type'=>'deduct',
                                                    'taxPercent'=>$taxPercent,
                                                    'flatTax'=>$flatTax,
                                                    'taxAmount'=>$taxAmount,
                                                );
                                                $prop->tax = $taxAmount*-1;
                                                if(isset($taxPercent) && !empty($taxPercent))
                                                    $prop->taxPercent = $taxPercent*-1;
                                                    if(isset($flatTax) && !empty($flatTax))
                                                        $prop->taxFlat = $flatTax*-1;
                                            }
                                            else
                                            {
                                                $taxes[$book] = array(
                                                    'taxID'=>$tax->ID,
                                                    'type'=>'add',
                                                    'taxPercent'=>$taxPercent,
                                                    'flatTax'=>$flatTax,
                                                    'taxAmount'=>$taxAmount,
                                                );
                                                $prop->tax = $taxAmount;
                                                if(isset($taxPercent) && !empty($taxPercent))
                                                    $prop->taxPercent = $taxPercent;
                                                    if(isset($flatTax) && !empty($flatTax))
                                                        $prop->taxFlat = $flatTax;
                                                        $finalPrice += $taxAmount;
                                            }
                                            $taxTotal = $taxTotal+$taxAmount;
                                            $indPrice[$book] += $taxes[$book]['taxAmount'];
                                            $actIndPrice[$book]['tax'] = $taxTotal;
                                            
                                    }//end tax
                                    $indPrice[$book] = number_format($indPrice[$book], 2, '.', '');
                                    $finalPrice = str_replace(",", "", $finalPrice);
                                   
                                    
                                    //owner credit coupon
                                    if(isset($cart->occoupon))
                                    {
                                        $occArr = $cart->occoupon;
                                        $occUsed = [];
                                        foreach($occArr as $thisOCC)
                                        {
                                            $sql = "SELECT *, a.id as cid, b.id as aid, c.id as oid FROM wp_gpxOwnerCreditCoupon a
                                            INNER JOIN wp_gpxOwnerCreditCoupon_activity b ON b.couponID=a.id
                                            INNER JOIN wp_gpxOwnerCreditCoupon_owner c ON c.couponID=a.id
                                            WHERE a.id='".$thisOCC."' AND a.active=1 and c.ownerID='".$cid."'";
                                            $occoupons = $wpdb->get_results($sql);
                                       
                                            if(!empty($occoupons))
                                            {
                                                $distinctCoupon = '';
                                                $distinctOwner = [];
                                                $distinctActivity = [];
                                                foreach($occoupons as $occoupon)
                                                {
                                                    $distinctCoupon = $occoupon;
                                                    $distinctOwner[$occoupon->oid] = $occoupon;
                                                    $distinctActivity[$occoupon->aid] = $occoupon;
                                                }
                                                
                                                $actredeemed = [];
                                                $actamount = [];
                                                //get the balance and activity for data
                                                foreach($distinctActivity as $activity)
                                                {
                                                    if($activity->activity == 'transaction')
                                                    {
                                                        $actredeemed[] = $activity->amount;
                                                    }
                                                    else
                                                    {
                                                        $actamount[] = $activity->amount;
                                                    }
                                                }
                                                if($distinctCoupon->single_use == 1 && array_sum($actredeemed) > 0)
                                                {
                                                    $balance = 0;
                                                }
                                                else
                                                {
                                                    $balance = array_sum($actamount) - array_sum($actredeemed);
//                                                     if(isset($occUsed))
//                                                     {
//                                                         $balance = $balance - array_sum($occUsed);
//                                                     }
                                                }
                                                //if we have a balance at this point the the coupon is good
                                                if($balance > 0)
                                                {
    //                                                 echo '<pre>'.print_r($indPrice[$book], true).'</pre>';
                                                    $avAmt = $indPrice[$book] - array_sum($occUsed);
                                                    if($balance < $avAmt)
                                                    {
                                                        $occUsed[$thisOCC] = $balance;
                                                        $occForActivity[$book][$thisOCC] = $balance;
//                                                         $finalPrice = $finalPrice - $balance;
//                                                         $indPrice[$book] = $indPrice[$book] - $balance;
//                                                         $indCartOCCreditUsed[$book] = $balance;
    //                                                     $actIndPrice[$book]['WeekPrice'] = $actIndPrice[$book]['WeekPrice'] - $balance;
                                                    }
                                                    else
                                                    {
                                                        $occUsed[$thisOCC] = $avAmt;
                                                        $occForActivity[$book][$thisOCC] = $avAmt;
                                                        
//                                                         $indCartOCCreditUsed[$book] = $indPrice[$book];
//                                                         $indPrice[$book] = 0;
//                                                         $finalPrice = $finalPrice - $indCartOCCreditUsed[$book];
                                                        
    //                                                     $actIndPrice[$book]['WeekPrice'] = 0;
    //                                                     $actIndPrice[$book]['cpoFee'] = 0;
    //                                                     $actIndPrice[$book]['extensionFee'] = 0;
    //                                                     $actIndPrice[$book]['guestFee'] = 0;
    //                                                     $actIndPrice[$book]['upgradeFee'] = 0;
    //                                                     $actIndPrice[$book]['tax'] = 0;
                                                    }
                                                }
                                            }
                                        }
                                        if(!empty($occUsed))
                                        {
                                            $indPrice[$book] = $indPrice[$book] - array_sum($occUsed);
                                            $finalPrice = $finalPrice - array_sum($occUsed);
                                            $indCartOCCreditUsed[$book] = array_sum($occUsed);
                                        }
                                    }
                }//end each property in cart
               if(isset($_GET['debug_price']))
               {
                   echo '<pre>'.print_r($indPrice, true).'</pre>';
                   echo '<pre>'.print_r($indCartOCCreditUsed, true).'</pre>';
               }
               $finalPrice = number_format($finalPrice, 2);
                if($finalPrice <= 0)
                {
                    $finalPrice = 0;
                }
                    $ppSum = array_sum($pp);
                    
                    if(count($spOut) > 0)
                    {
                        $spFullDiscount = array_sum($spDiscount);
                        $spSum = $ppSum - $spFullDiscount;
//                         $spSum = array_sum($spOut);
                    }
                        if(isset($couponDiscount) && $couponDiscount > 0)
                            $couponDiscount = '$'.number_format($couponDiscount, 2);
                            
                            $priceint = number_format(preg_replace("/[^0-9\.]/", "",$prop->WeekPrice), 0);
                            $propWeekPrice = str_replace(".00", "", $prop->WeekPrice);
                            $nopriceint = str_replace($priceint, "", $propWeekPrice);
                            $displayPrice = $finalPrice;
            }
            else
            {
                $carterror = true;
            }
            $data = get_defined_vars();
            return $data;
        }
        
        
        function get_exclusive_weeks($prop, $cid)
        {
            global $wpdb;
            $checkInDate = date('Y-m-d', strtotime($prop->checkIn));
            $discount = '';
            $specialPrice = '';
            $todayDT = date("Y-m-d 00:00:00");
            //are there specials?
            $sql = "SELECT a.id, a.Name, a.Slug, a.Properties, a.Amount, a.SpecUsage, a.PromoType
    			FROM wp_specials a
                LEFT JOIN wp_promo_meta b ON b.specialsID=a.id
                LEFT JOIN wp_resorts c ON c.id=b.foreignID
                LEFT JOIN wp_gpxRegion d ON d.id=b.foreignID
                WHERE (SpecUsage = 'any'
                OR ((c.ResortID='".$prop->ResortID."' AND b.refTable='wp_resorts')
                OR (b.reftable = 'wp_gpxRegion' AND d.id IN ('".$prop->gpxRegionID."')))
                OR SpecUsage LIKE '%customer%'
                OR SpecUsage LIKE '%region%')
                AND '".$checkInDate."' BETWEEN TravelStartDate AND TravelEndDate
                AND (StartDate <= '".$todayDT."' AND EndDate >= '".$todayDT."')
                AND a.Type='promo'
                AND a.Active=1";
            $rows = $wpdb->get_results($sql);
                if($rows)
                {
                    $i = 0;
                    foreach($rows as $row)
                    {
                        $uregionsAr = array();
                        $skip = false;
                        $regionOK = false;
                        $resortOK = false;
                        $specialMeta = stripslashes_deep( json_decode($row->Properties) );
                        
                        //if this is an exclusive week then we might need to remove this property
                        if(isset($specialMeta->exclusiveWeeks) && !empty($specialMeta->exclusiveWeeks))
                        {
                            $exclusiveWeeks = explode(',', $specialMeta->exclusiveWeeks);
                            if(in_array($prop->weekId, $exclusiveWeeks))
                            {
                                $rmExclusiveWeek[$prop->weekId] = $prop->weekId;
                            }
                            else
                            {
                                //this doesn't apply
                                $skip = true;
                                continue;
                            }
                        }
                        
                        //week min cost
                        if(isset($specialMeta->minWeekPrice) && !empty($specialMeta->minWeekPrice))
                        {
                            if($prop->WeekType == 'ExchangeWeek')
                                $skip = true;
                                
                                if($specialDiscountPrice < $specialMeta->minWeekPrice)
                                    $skip = true;
                        }
                        //usage upsell
                        $upsell = array();
                        if(isset($specialMeta->upsellOptions) && !empty($specialMeta->upsellOptions))
                        {
                            
                            $upsell[] = array(
                                'option'=>$specialMeta->upsellOptions,
                                'type' => $specialMeta->promoType,
                                'amount' => $row->Amount,
                            );
                        }
                        if($specialMeta->promoType == 'BOGO' || $specialMeta->promoType == 'BOGOH')
                        {
                            $bogomax = '';
                            $bogomin = '';
                            $bogoSet = $row->Slug;
                            if(isset($_COOKIE['gpx-cart']))
                            {
                                $boCartID = $_COOKIE['gpx-cart'];
                                $sql = "SELECT * FROM wp_cart WHERE cartID='".$boCartID."'";
                                $bocarts = $wpdb->get_results($sql);
                            }
                            if(isset($bocarts) && !empty($bocarts))
                            {
                                foreach($bocarts as $bocart)
                                {
                                    $boPID = $bocart->propertyID;
                                    $sql = "SELECT price FROM wp_room WHERE id='".$boPID."'";
                                    $boPriceQ = $wpdb->get_row($sql);
                                    $bogo = $boPriceQ->Price;
                                    if($bogo > $bogomax)
                                    {
                                        $bogomax = $bogo;
                                    }
                                    if(empty($bogomin))
                                    {
                                        $bogomin = $bogo;
                                        $bogominPID = $boPID;
                                    }
                                    elseif($bogo <= $bogomin)
                                    {
                                        $bogomin = $bogo;
                                        $bogominPID = $boPID;
                                    }
                                }
                                if($bogominPID == $prop->id)
                                {
                                    if($specialMeta->promoType == 'BOGOH')
                                        $bogoPrice = number_format($prop->Price/2, 2);
                                        else
                                            $bogoPrice = '0';
                                }
                            }
                            if(count($bocarts) < 2)
                            {
                                unset($bogoPrice);
                                $skip = true;
                            }
                        }
                        if(is_array($specialMeta->transactionType))
                            $ttArr = $specialMeta->transactionType;
                            else
                                $ttArr = array($specialMeta->transactionType);
                                $transactionType = array();
                                foreach($ttArr as $tt)
                                {
                                    switch ($tt)
                                    {
                                        case 'Upsell':
                                            $transactionType['upsell'] = 'Upsell';
                                            break;
                                            
                                        case 'All':
                                            $transactionType['any'] = $prop->WeekType;
                                            break;
                                            
                                        case 'any':
                                            $transactionType['any'] = $prop->WeekType;
                                            break;
                                        case 'ExchangeWeek':
                                            $transactionType['exchange'] = 'ExchangeWeek';
                                            break;
                                        case 'BonusWeek':
                                            $transactionType['bonus'] = 'BonusWeek';
                                            break;
                                        case 'RentalWeek':
                                            $transactionType['bonus'] = 'RentalWeek';
                                            break;
                                    }
                                }
                                if(!empty($upsell) || ($row->Amount > $discount  && ($transactionType == $prop->WeekType || in_array($prop->WeekType, $transactionType))) || (isset($bogominPID) && $bogominPID == $prop->id) || ((isset($specialMeta->acCoupon) && $specialMeta->acCoupon == 1) &&  ($transactionType == $prop->WeekType || in_array($prop->WeekType, $transactionType))))
                                {
                                    /*
                                     * filter out conditions
                                     */
                                    // landing page only
                                    if(isset($specialMeta->availability) && $specialMeta->availability == 'Landing Page')
                                    {
                                        $lpid = '';
                                        if(isset($_COOKIE['lppromoid'.$prop->weekId.$row->id]))
                                        {
                                            // all good
                                            $lpid = $prop->weekId.$row->id;
//                                             echo '<pre>'.print_r("", true).'</pre>';
                                        }
                                        else
                                        {
                                            //let's check to see if it was on hold
                                            $sql = "SELECT lpid FROM wp_gpxPreHold WHERE weekId='".$prop->weekId."' AND user='".$cid."' AND lpid='".$prop->weekId.$row->id."'";
                                            $lpidRows = $wpdb->get_results($sql);
                                            foreach($lpidRows as $lpidRow)
                                            {
                                                if(!empty($lpidRow->lpid))
                                                    $lpid = $lpidRow->lpid;
                                            }
                                            
                                        }
                                        //if the lpid is empty then we can skip
                                        if(empty($lpid))
                                        {
                                            $skip = true;
                                        }
                                    }
                                    //blackouts
                                    if(isset($specialMeta->blackout) && !empty($specialMeta->blackout))
                                    {
                                        foreach($specialMeta->blackout as $blackout)
                                        {
                                            if(strtotime($prop->checkIn) >= strtotime($blackout->start) && strtotime($prop->checkIn) <= strtotime($blackout->end))
                                            {
                                                $skip = true;
                                            }
                                        }
                                    }
                                    //resort blackout dates
                                    if(isset($specialMeta->resortBlackout) && !empty($specialMeta->resortBlackout))
                                    {
                                        foreach($specialMeta->resortBlackout as $resortBlackout)
                                        {
                                            //if this resort is in the resort blackout array then continue looking for the date
                                            if(in_array($prop->resortID, $resortBlackout->resorts))
                                            {
                                                if(strtotime($prop->checkIn) > strtotime($resortBlackout->start) && strtotime($prop->checkIn) < strtotime($resortBlackout->end))
                                                {
                                                    $skip = true;
                                                }
                                            }
                                        }
                                    }
                                    //resort specific travel dates
                                    if(isset($specialMeta->resortTravel) && !empty($specialMeta->resortTravel))
                                    {
                                        foreach($specialMeta->resortTravel as $resortTravel)
                                        {
                                            //if this resort is in the resort blackout array then continue looking for the date
                                            if(in_array($prop->resortID, $resortTravel->resorts))
                                            {
                                                if(strtotime($prop->checkIn) > strtotime($resortTravel->start) && strtotime($prop->checkIn) < strtotime($resortTravel->end))
                                                {
                                                    //all good
                                                }
                                                else
                                                {
                                                    $skip = true;
                                                }
                                            }
                                        }
                                    }
                                    
                                    if(isset($bogominPID) && $bogominPID != $prop->id)
                                        $skip = true;
                                        if($specialMeta->beforeLogin == 'Yes' && !is_user_logged_in())
                                            $skip = true;
                                            
                                            if(strpos($row->SpecUsage, 'customer') !== false)//customer specific
                                            {
                                                if(isset($cid))
                                                {
                                                    $specCust = (array) json_decode($specialMeta->specificCustomer);
                                                    if(!in_array($cid, $specCust))
                                                    {
                                                        $skip = true;
                                                    }
                                                }
                                                else
                                                    $skip = true;
                                            }
                                            
                                            if($skip)
                                            {
                                                $skippedBefore = true;
                                            }
                                            
                                            //usage resort
                                            if(isset($specialMeta->usage_region) && !empty($specialMeta->usage_region))
                                            {
                                                $usage_regions = json_decode($specialMeta->usage_region);
                                                foreach($usage_regions as $usage_region)
                                                {
                                                    $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE id='".$usage_region."'";
                                                    $excludeLftRght = $wpdb->get_row($sql);
                                                    $excleft = $excludeLftRght->lft;
                                                    $excright = $excludeLftRght->rght;
                                                    $sql = "SELECT id FROM wp_gpxRegion WHERE lft>=".$excleft." AND rght<=".$excright;
                                                    $usageregions = $wpdb->get_results($sql);
                                                    if(isset($usageregions) && !empty($usageregions))
                                                    {
                                                        foreach($usageregions as $usageregion)
                                                        {
                                                            $uregionsAr[] = $usageregion->id;
                                                        }
                                                    }
                                                    
                                                }
                                                if(!in_array($prop->gpxRegionID, $uregionsAr))
                                                {
                                                    $skip = true;
                                                    $regionOK = 'no';
                                                }
                                                else
                                                {
                                                    $regionOK = 'yes';
                                                }
                                            }
                                            //usage resort
                                            if(isset($specialMeta->usage_resort) && !empty($specialMeta->usage_resort))
                                            {
                                                if(isset($cart))
                                                    if(!in_array($cart->propertyID, $specialMeta->usage_resort))
                                                        if(isset($regionOK) && $regionOK == true)//if we set the region and it applies to this resort then the resort doesn't matter
                                                        {
                                                            //do nothing
                                                            $resortOK = true;
                                                        }
                                                    else
                                                    {
                                                        $skip = true;
                                                    }
                                                    elseif(isset($_GET['book']))
                                                    if(!in_array($_GET['book'], $specialMeta->usage_resort))
                                                        if(isset($regionOK) && $regionOK == true)//if we set the region and it applies to this resort then the resort doesn't matter
                                                        {
                                                            //do nothing
                                                            $resortOK = true;
                                                        }
                                                    else
                                                    {
                                                        $skip = true;
                                                    }
                                            }
                                            
                                            if(get_current_user_id() == 5)
                                            {
                                                if($resortOK && !$skippedBefore)
                                                {
                                                    $skip = false;
                                                }
                                            }
                                            
                                            //transaction type
                                            if(!empty($transactionType) && (in_array('ExchangeWeek', $transactionType) || !in_array('BonusWeek', $transactionType)))
                                            {
                                                if(!in_array($prop->WeekType, $transactionType))
                                                {
                                                    $skip = true;
                                                }
                                            }
                                            
                                            //useage DAE
                                            if(isset($specialMeta->useage_dae) && !empty($specialMeta->useage_dae))
                                            
                                            {
                                                //Only show if OwnerBusCatCode = DAE AND StockDisplay = ALL or GPX
//                                                 if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx') && strtolower($prop->OwnerBusCatCode) == 'dae')
                                                if((strtolower($prop->StockDisplay) == 'all' || (strtolower($prop->StockDisplay) == 'gpx' || strtolower($prop->StockDisplay) == 'usa gpx')) && (strtolower($prop->OwnerBusCatCode) == 'dae' || strtolower($prop->OwnerBusCatCode) == 'usa dae'))
                                                {
                                                    // we're all good -- these are the only properties that should be displayed
                                                }
                                                else
                                                {
                                                    $skip = true;
                                                }
                                                
                                            }
                                            //exclusions
                                            
                                            //exclude DAE inventory
//                                             if((isset($specialMeta->exclude_dae) && !empty($specialMeta->exclude_dae)) || (isset($specialMeta->exclusions) && $specialMeta->exclusions == 'dae'))
//                                             {
//                                                 //If DAE selected as an exclusion:
//                                                 //- Do not show inventory to use unless
//                                                 //--- Stock Display = GPX or ALL
//                                                 //AND
//                                                 //---OwnerBusCatCode=GPX
// //                                                 if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx') && strtolower($prop->OwnerBusCatCode) == 'gpx')
//                                                 if((strtolower($prop->StockDisplay) == 'all' || (strtolower($prop->StockDisplay) == 'gpx' || strtolower($prop->StockDisplay) == 'gpx')) && (strtolower($prop->OwnerBusCatCode) == 'gpx' || strtolower($prop->OwnerBusCatCode) == 'usa gpx'))
//                                                 {
//                                                     //all good we can show these properties
//                                                 }
//                                                 else
//                                                 {
//                                                     $skip = true;
//                                                 }
//                                             }
                                            
                                            //exclude resorts
                                            if(isset($specialMeta->exclude_resort) && !empty($specialMeta->exclude_resort))
                                            {
                                                foreach($specialMeta->exclude_resort as $exc_resort)
                                                {
                                                    if($exc_resort == $prop->resortID)
                                                    {
                                                        $skip = true;
                                                        break;
                                                    }
                                                }
                                            }
                                            //exclude regions
                                            if(isset($specialMeta->exclude_region) && !empty($specialMeta->exclude_region))
                                            {
                                                $exclude_regions = json_decode($specialMeta->exclude_region);
                                                foreach($exclude_regions as $exclude_region)
                                                {
                                                    $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE id='".$exclude_region."'";
                                                    $excludeLftRght = $wpdb->get_row($sql);
                                                    $excleft = $excludeLftRght->lft;
                                                    $excright = $excludeLftRght->rght;
                                                    $sql = "SELECT * FROM wp_gpxRegion WHERE lft>=".$excleft." AND rght<=".$excright;
                                                    $excregions = $wpdb->get_results($sql);
                                                    if(isset($excregions) && !empty($excregions))
                                                    {
                                                        foreach($excregions as $excregion)
                                                        {
                                                            if($excregion->id == $prop->gpxRegionID)
                                                            {
                                                                $skip = true;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            //exclude home resort
                                            if(isset($specialMeta->exclusions) && $specialMeta->exclusions == 'home-resort')
                                            {
                                                if(isset($usermeta) && !empty($usermeta))
                                                {
                                                    $ownresorts = array('OwnResort1', 'OwnResort2', 'OwnResort3');
                                                    foreach($ownresorts as $or)
                                                    {
                                                        if(isset($usermeta->$or))
                                                            if($usermeta->$or == $prop->ResortName)
                                                                $skip = true;
                                                    }
                                                }
                                            }
                                            
                                            //lead time
                                            $today = date('Y-m-d');
                                            if(isset($specialMeta->leadTimeMin) && !empty($specialMeta->leadTimeMin))
                                            {
                                                $ltdate = date('Y-m-d', strtotime($prop->checkIn." -".$specialMeta->leadTimeMin." days"));
                                                if($today > $ltdate)
                                                    $skip = true;
                                            }
                                            
                                            if(isset($specialMeta->leadTimeMax) && !empty($specialMeta->leadTimeMax))
                                            {
                                                $ltdate = date('Y-m-d', strtotime($prop->checkIn." -".$specialMeta->leadTimeMax." days"));
                                                if($today < $ltdate)
                                                    $skip = true;
                                            }
                                            if(isset($specialMeta->bookStartDate) && !empty($specialMeta->bookStartDate))
                                            {
                                                $bookStartDate = date('Y-m-d', strtotime($specialMeta->bookStartDate));
                                                if($today < $bookStartDate)
                                                    $skip = true;
                                            }
                                            
                                            if(isset($specialMeta->bookEndDate) && !empty($specialMeta->bookEndDate))
                                            {
                                                $bookEndDate = date('Y-m-d', strtotime($specialMeta->bookEndDate));
                                                if($today > $bookEndDate)
                                                    $skip = true;
                                            }
                                            if(!$skip)
                                            {
                                                if(isset($rmExclusiveWeek[$prop->weekId]) && !empty($rmExclusiveWeek[$prop->weekId]))
                                                {
                                                    unset($rmExclusiveWeek[$prop->weekId]);
                                                }
                                            }
                                }
                                $i++;
                    }
                }
                $data = array();
                if(isset($rmExclusiveWeek) && !empty($rmExclusiveWeek))
                {
                    $data = $rmExclusiveWeek;
                }
                return $data;
        }
        
        function custom_request_match($db, $resultPage='')
        {
            global $wpdb;
            
            $rtWhere = '';
            $checkIN = strtotime($db['checkIn']);
            $thisYear = date('Y', $checkIN);
            $memorialDay = strtotime("-6 days last monday of may $thisYear");
            $laborDay = strtotime("-6 days first monday of september $thisYear");
            
            $joinedTbl = map_dae_to_vest_properties();
            
            if(isset($db['adults']))
            {
                
                //number of guests can't exceed maximum occupancy
                $occupants = $db['adults'] + $db['children'];
                
                if(empty($db['roomType']))
                {
                    $db['roomType'] = 'Any';
                }
                if($db['roomType'] != 'Any')
                {
                    $minRoomType = $db['roomType'];
                    $roomTypes = array(
                        'Studio' => array(
                            'St',
                            'STD',
                            'HR',
                            'Spa',
                            'HSUP',
                            'HDLX',
                            'STSO',
                            'STTENT',
                            'YACT',
                        ),
                        '1BR' => array(
                            '1',
                            '1b',
                            '1B VIL',
                            '1B OCN',
                            '1BDLX',
                            '1B DLX',
                            '1BTWN',
                            '1B GDN',
                            '1BMINI',
                        ),
                        '2BR' => array(
                            '2',
                            '3',
                            '4',
                            '2r',
                            '2B',
                            '2b',
                            '2B VIL',
                            '2BLOFT',
                            '2B DLX',
                            '2BCAB',
                            '2B OCN',
                        ),
                        '3BR' => array(
                            '3',
                            '4',
                            '3b',
                            '4b',
                            '3B VIL'
                        ),
                    );
                    
                    foreach($roomTypes as $rtKey=>$rtVal)
                    {
                        if($rtKey == $minRoomType)
                        {
                            break;
                        }
                        else
                        {
                            unset($roomTypes[$rtKey]);
                        }
                    }
                    foreach($roomTypes as $rtVal)
                    {
                        foreach($rtVal as $rtv)
                        {
                            $minRTs[] = $rtv;
                        }
                        if(isset($db['larger']) && $db['larger'] == 1)
                        {
                            //continue the loop
                        }
                        else
                        {
                            //break becuase we don't want any larger units to be added.
                            break;
                        }
                    }
                    $rtWhere .= " AND number_of_bedrooms IN ('".implode("','", $minRTs)."')";
                }
                
                $roomOccupancy = array(
                    'Studio'=>array(
                        'min'=>'1',
                        'max'=>'2'
                    ),
                    '1BR'=>array(
                        'min'=>'4',
                        'max'=>'15'
                    ),
                    '2BR'=>array(
                        'min'=>'6',
                        'max'=>'15'
                    ),
                    '3BR'=>array(
                        'min'=>'6',
                        'max'=>'15'
                    ),
                    'Any'=>array(
                        'min'=>'1',
                        'max'=>'15'
                    ),
                );
                //if there are too many occupants for the selected room then select the approriate room size
                if($occupants > $roomOccupancy[$db['roomType']]['max'])
                {
//                     foreach($roomOccupancy as $key=>$value)
//                     {
//                         if($occupants < $value['max'])
//                         {
//                             $rt = $key;
//                             break;
//                         }
//                     }
                }
                else
                {
                    $rt = $db['roomType'];
                }
                
                //not using the roomType field any more.  only pull based on the occupancy
                // $rtWhere = "AND gpxSleepsTotal between'".$roomOccupancy[$rt]['min']."' AND '".$roomOccupancy[$rt]['max']."'";
//                 $rtWhere .= " AND sleeps_total between'".$occupants."' AND '20'";
                
            }
            //if week preference is set then we need to either pull exchange week or everything but exchange week
            if(isset($db['preference']) && !empty($db['preference']) && $db['preference'] != 'Any')
            {
                if($db['preference'] == 'Exchange')
                {
                    $rtWhere .= " AND type IN  ('3', '1')";
                }
                else
                {
                    $rtWhere .= " AND type IN ('3', '2')";
                }
            }
            
            //check if the data is within a restricted time
            if(($memorialDay <= strtotime($db['checkIn']) AND strtotime($db['checkIn']) <= $laborDay)) //the first date in the range is between memorial day and labor day
            {
                if((isset($db['checkIn2']) AND ($memorialDay <= strtotime($db['checkIn2']) AND strtotime($db['checkIn2']) <= $laborDay)) || !isset($db['checkIn2'])) //the second date in the range either isn't set or it is set and it is between memorial day and labor day
                {
                    $restrictedCheck = "Fully";
                }
                else //the second date is not between memorial day and labor day
                {
                    $restrictedCheck = "Partial";
                }
            }
            elseif((isset($db['checkIn2']) AND ($memorialDay <= strtotime($db['checkIn2']) AND strtotime($db['checkIn2']) <= $laborDay))) // first date isn't between memorial day and labor day but second date is
            {
                $restrictedCheck = "Partial";
            }
            else //neither date is between memorial day and labor day
            {
                //do nothing
            }
            
            //get a list of restricted gpxRegions
            $sql = "SELECT id, lft, rght FROM wp_gpxRegion WHERE name='Southern Coast (California)'";
            $restLRs = $wpdb->get_results($sql);
            foreach($restLRs as $restLR)
            {
                $sql = "SELECT id FROM wp_gpxRegion WHERE lft BETWEEN ".$restLR->lft." AND ".$restLR->rght;
                $restricted = $wpdb->get_results($sql);
                
                
                if(isset($_GET['customrequest_debug']))
                {
                    echo '<pre>'.print_r("restricted", true).'</pre>';
                    echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                    echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                    echo '<pre>'.print_r("end restricted", true).'</pre>';
                }
                
                foreach($restricted as $restrict)
                {
                    
                    $restrictIDs[$restrict->id] = $restrict->id;
                }
            }
            //begin the search process
            
            if((isset($db['miles']) && $db['miles'] != 0) || ( (isset($db['nearby']) && $db['nearby'] == '1') && (isset($db['resort']) && !empty($db['resort'])) ) ) //search by miles
            {
                //if nearby is checked then get the "city" and search within 30 miles
                if(isset($db['nearby']) && $db['nearby'] == '1')
                {
                    $sql = "SELECT a.name FROM wp_gpxRegion a
                            INNER JOIN wp_resorts b on b.gpxRegionID=a.id
                            WHERE ResortName LIKE '".addslashes($db['resort'])."'";
                    $nearby = $wpdb->get_row($sql);
                    
                    
                    if(isset($_GET['customrequest_debug']))
                    {
                        echo '<pre>'.print_r("nearby", true).'</pre>';
                        echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                        echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                        echo '<pre>'.print_r("end nearby", true).'</pre>';
                    }
                    
                    $db['city'] = $nearby->name;
                    $db['miles'] = 30;
                }
                if(empty($db['city']))
                {
                    //we don't have anything set so there isn't anything to search -- return no results
                    return array();
                }
                
                //get the coordinates of the selected city
                $sql = "SELECT lng, lat FROM wp_gpxRegion WHERE (name='".$db['city']."' OR displayName='".$db['city']."')";
                $row = $wpdb->get_row($sql);
                if(!empty($row) && $row->lat != '0')
                {
                    $sql = "SELECT
                            `id`,
                            (
                                3959 *
                                acos(
                                    cos( radians( ".$row->lat." ) ) *
                                    cos( radians( `lat` ) ) *
                                    cos(
                                        radians( `lng` ) - radians( ".$row->lng." )
                                    ) +
                                    sin(radians(".$row->lat.")) *
                                    sin(radians(`lat`))
                                )
                            ) `distance`
                        FROM
                            `wp_gpxRegion`
                        HAVING
                            `distance` < ".$db['miles'];
                    $regions = $wpdb->get_results($sql);
                    
                    
                    if(isset($_GET['customrequest_debug']))
                    {
                        echo '<pre>'.print_r("regions", true).'</pre>';
                        echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                        echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                        echo '<pre>'.print_r("end regions", true).'</pre>';
                    }
                    
                    foreach($regions as $region)
                    {
                        $ids[] = $region->id;
                    }
                }
            }
            elseif(isset($db['resort']) && !empty($db['resort'])) //search by resort
            {
                if(empty($db['checkIn2']) || $db['checkIn2'] < $db['checkIn'])
                {
                    $db['checkIn2'] = $db['checkIn'];
                }
                //get the resorts that match
                $sql = "SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".implode(', ', $joinedTbl['joinResort']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                        WHERE b.ResortName LIKE '".addslashes($db['resort'])."'
                        AND check_in_date BETWEEN '".date("Y-m-d 00:00:00", strtotime($db['checkIn']))."' AND '".date("Y-m-d 23:59:59", strtotime($db['checkIn2']))."'
                        $rtWhere
                        AND a.active=1
                        AND b.active=1";
                        $props = $wpdb->get_results($sql);
                        
                        if(isset($_GET['customrequest_debug']))
                        {
                            echo '<pre>'.print_r("resorts", true).'</pre>';
                            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                            echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                            echo '<pre>'.print_r("end resorts", true).'</pre>';
                        }
                        //check if the gpxRegionID is restricted
                        if(isset($restrictedCheck))
                        {
                            $sql = "SELECT b.gpxRegionID  FROM wp_room a
                        INNER JOIN wp_resorts b ON b.id = a.resort
                        WHERE b.ResortName LIKE '".addslashes($db['resort'])."' group by b.gpxRegionID";
                            $regionIDs = $wpdb->get_results($sql);

                            if(!empty($regionIDs))
                            {
                                foreach($regionIDs as $regionID)
                                {
                                    if(in_array($regionID->gpxRegionID, $restrictIDs))
                                    {
                                        $allRestricted[] = 'Restricted';
                                        
                                    }
                                    else
                                    {
                                        $allRestricted[] = 'Not Restricted';
                                    }
                                }
                            }
                            else
                            {
                                $allRestricted[] = 'Not Restricted';
                            }
                        }
            }
            else //search by region
            {
                $region = $db['region'];
                if(isset($db['city']) && !empty($db['city'])) //search by city/sub-region
                    $region = $db['city'];
                    
                    if(empty($region))
                    {
                        //we don't have anything set so there isn't anything to search -- return no results
                        return array();
                    }
                    
                    //is this a cateogry?
                    $sql = "SELECT countryID from wp_gpxCategory WHERE country='".$db['region']."' && CountryID < 1000";
                    $category = $wpdb->get_row($sql);
                    
                    if(!empty($category) && ($category->id == '14' && $db['region'] == 'Italy'))
                    {
                        $category = '';
                    }
                    
                    if(!empty($category))
                    {
                        $sql = "SELECT a.id, a.lft, a.rght FROM wp_gpxRegion a
                            INNER JOIN wp_daeRegion b ON a.RegionID=b.id
                            WHERE b.CategoryID='".$category->id."'";
                    }
                    else
                    {
                        $sql = "SELECT id, lft, rght FROM wp_gpxRegion
                            WHERE name='".$region."'
                            OR subName='".$region."'
                            OR displayName='".$region."'";
                    }
                    $gpxRegions = $wpdb->get_results($sql);                
                    
                    if(isset($_GET['customrequest_debug']))
                    {
                        echo '<pre>'.print_r("regions", true).'</pre>';
                        echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                        echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                    }
                    
                    if(!empty($gpxRegions))
                    {
                        $results = array();
                        foreach($gpxRegions as $gpxRegion)
                        {
                            //get all the regions
                            $sql = "SELECT id, name FROM wp_gpxRegion
                            WHERE lft BETWEEN ".$gpxRegion->lft." AND ".$gpxRegion->rght."
                            ORDER BY lft ASC";
                            $rows = $wpdb->get_results($sql);
                            foreach($rows as $row)
                            {
                                $ids[] = $row->id;
                                
                            }
                            
                            
                            if(isset($_GET['customrequest_debug']))
                            {
                                echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                                echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                                echo '<pre>'.print_r("end regions", true).'</pre>';
                            }
                        }
                    }
            }
            //if we only set the $ids array above then we still need to get the results (search by region and search by miles)
            if((isset($ids) && !empty($ids)) && (empty($results) || isset($results['retricted'])))
            {
                
                foreach($ids as $id)
                {
                    if(isset($restrictedCheck))
                    {
                        //check if the id is restricted
                        if(in_array($id, $restrictIDs))
                        {
                            $allRestricted[] = 'Restricted';
                        }
                        else
                        {
                            $allRestricted[] = 'Not Restricted';
                        }
                    }
                    else
                    {
                        $allRestricted[] = 'Not Restricted';
                    }
                    
                    $wheres[] = "b.GPXRegionID='".$id."'";
                }
                $where = implode(" OR ", $wheres);
                
                if(empty($db['checkIn2']) || $db['checkIn2'] < $db['checkIn'])
                {
                    $db['checkIn2'] = $db['checkIn'];
                }
                
                $sql = "SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".implode(', ', $joinedTbl['joinResort']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                        WHERE (".$where.")
                        AND check_in_date BETWEEN '".date("Y-m-d", strtotime($db['checkIn']))."' AND '".date("Y-m-d", strtotime($db['checkIn2']))."'
                        $rtWhere
                        AND a.active=1
                        AND b.active=1";
                        $props = $wpdb->get_results($sql);
                        
                        if(isset($_GET['customrequest_debug']))
                        {
                           echo '<pre>'.print_r($sql, true).'</pre>';
                           echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                        }
            }
            //add the restricted value
            if(isset($restrictedCheck) && in_array('Restricted', $allRestricted) && in_array('Not Restricted', $allRestricted))
            {
                $results['restricted'] = 'Some Restricted';
            }
            elseif(isset($restrictedCheck) && in_array('Restricted', $allRestricted) && !in_array('Not Restricted', $allRestricted))
            {
                if($restrictedCheck == 'Fully')
                {
                    $results['restricted'] = 'All Restricted';
                }
                else
                {
                    $results['restricted'] = 'Some Restricted';
                }
            }
            
            if(isset($_GET['customrequest_debug']))
            {
                echo '<pre>'.print_r($props, true).'</pre>';
            }
            //check the results for
            foreach($props as $prop)
            {
                $results[] = $prop;
            }
            
            return $results;
        }
        
        function gpx_hold_check($cid)
        {
            //query the database for this users' holds
            require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
            $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
            
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
            
            $holds = $gpx->DAEGetWeeksOnHold($usermeta->DAEMemberNo);
            $credits = $gpx->DAEGetMemberCredits($usermeta->DAEMemberNo);
            
            //return true if credits+1 is greater than holds
            if($credits[0]+1 >= count($holds))
            {
                return true;
            }
            else
            {
                return false;
            }
            
        }
        
        function cart_coupon($coupons)
        {
            
                
                $couponDiscount = 0;
                foreach($coupons as $activeCoupon)
                {
                    
                    //verify that this coupon was only applied once -- if this is in the array then we already applied it
                    if(isset($couponused[$activeCoupon]) && $couponused[$activeCoupon] == $book)
                    {
                        continue;
                    }
                    $couponused[$activeCoupon] = $book;
                    //                         echo '<pre>'.print_r($couponDiscount, true).'</pre>';
                    $startFinalPrice = $finalPrice;
                    $sql = "SELECT id, Properties, Amount FROM wp_specials WHERE id='".$activeCoupon."'";
                    $active = $wpdb->get_row($sql);
                    $activeProp = stripslashes_deep( json_decode($active->Properties) );
                    
                    if(($couponkey > 20 && $book != $couponkey) && ($activeProp->promoType != 'BOGO' || $activeProp->promoType != 'BOGOH'))
                    {
                        continue;
                    }
                    
                    $discountTypes = array(
                        'Pct Off',
                        'Dollar Off',
                        'Set Amt',
                        'BOGO',
                        'BOGOH',
                    );
                    foreach($discountTypes as $dt)
                    {
                        if(strpos($activeProp->promoType, $dt))
                            $activeProp->promoType = $dt;
                    }
                    
                    if(isset($activeProp->acCoupon) && $activeProp->acCoupon == 1)
                    {
                        $couponTemplate = $activeProp->couponTemplate;
                        //                                         if(in_array($active->id, $activePromos)) // skip all the pricing when it is an auto create coupon and the promo exists
                        //                                         {
                        unset($couponDiscount);
                        continue;
                        //                                         }
                        }
                        
                        $singleUpsellOption = false;
                        if(isset($activeProp->upsellOptions) && !empty($activeProp->upsellOptions))
                        {
                            if(count($activeProp->transactionType) == 1)
                            {
                                $singleUpsellOption = true;
                            }
                            foreach($activeProp->upsellOptions as $upsellOption)
                            {
                                switch($upsellOption)
                                {
                                    case 'CPO':
                                        if(isset($cpoFee) && !empty($cpoFee))
                                        {
                                            if($activeProp->promoType == 'Pct Off')
                                                $couponDiscount = number_format($cpoFee*($active->Amount/100),2);
                                                else
                                                    $couponDiscount = $cpoFee-$active->Amount;
                                                    
                                                    if($couponDiscount > $cpoFee)
                                                        $couponDiscount = $cpoFee;
                                                        
                                        }
                                        break;
                                        
                                    case 'Upgrade':
                                        if(isset($upgradeFee) && !empty($upgradeFee))
                                        {
                                            if($activeProp->promoType == 'Pct Off')
                                                $couponDiscount = number_format($upgradeFee*($active->Amount/100),2);
                                                else
                                                    $couponDiscount = $upgradeFee-$active->Amount;
                                                    
                                                    if($couponDiscount > $upgradeFee)
                                                        $couponDiscount = $upgradeFee;
                                        }
                                        break;
                                        
                                    case 'Guest Fees':
                                        if(isset($gfAmt) && !empty($gfAmt))
                                        {
                                            if($activeProp->promoType == 'Pct Off')
                                                $couponDiscount = number_format($gfAmt*($active->Amount/100),2);
                                                else
                                                    $couponDiscount = $active->Amount;
                                                    
                                                    if($couponDiscount > $gfAmt)
                                                        $couponDiscount = $gfAmt;                                                    }
                                                        break;
                                                        
                                    case 'Extension Fees':
                                        
                                        break;
                                }
                                $upselldisc[$book][$upsellOption] = $couponDiscount;
                            }
                            $indCouponDisc[$book] = array_sum($upselldisc[$book]);
                            if(!empty($couponDiscount))
                            {
                                $finalPrice = $finalPrice-$couponDiscount;
                            }
                        }
                        if(!$singleUpsellOption)
                        {
                            $pricewofees = ($indPrice[$book] - array_sum($indFees[$book]));
                            if($activeProp->promoType == 'Pct Off')
                            {
                                //first let's calculate the discount
                                $poDisc = ($indPrice[$book] - array_sum($indFees[$book]))*(($active->Amount/100));
                                $allCoupon[$book] = $poDisc;
                                $finalPrice = number_format($finalPrice - $poDisc,2);
                            }
                            elseif($activeProp->promoType == 'BOGO' || $activeProp->promoType == 'BOGOH')
                            {
                                if(isset($cart->couponbogo))
                                {
                                    $couponDiscountPrice = $indPrice[$book] - $cart->couponbogo;
                                    $indBOGOCoupon[$book] = $indPrice[$book] - $cart->couponbogo;
                                    if($couponDiscountPrice < 0)
                                    {
                                        if($activeProp->promoType == 'BOGO')
                                        {
                                            $couponDiscountPrice = 0;
                                            $indBOGOCoupon[$book] = 0;
                                        }
                                        else
                                        {
                                            $couponDiscountPrice = $indFees[$book]/2;
                                            $indBOGOCoupon[$book] = $indFees[$book]/2;
                                        }
                                    }
                                    $allCoupon[$book] = $indBOGOCoupon[$book];
                                    $finalPrice = $finalPrice-$couponDiscountPrice;
                                }
                            }
                            elseif($activeProp->promoType == 'Dollar Off')
                            {
                                $finalPrice = $finalPrice-$active->Amount;
                                $allCoupon[$book]= $active->Amount;
                            }
                            elseif($active->Amount < $indPrice[$book])
                            {
                                $allCoupon[$book] = $indPrice[$book] - $active->Amount;
                                
                                $finalPrice = $active->Amount;
                                //if an upgrade fee is set then we need to add it back in to the set amount
                                if(isset($upgradeFee))
                                {
                                    $allCoupon[$book]  -= $upgradeFee;
                                    $finalPrice += $upgradeFee;
                                }
                                //if a CPO fee is set then we need to add it back in to the set amount
                                if(isset($cpoFee))
                                {
                                    $allCoupon[$book] -= $cpoFee;
                                    $finalPrice += $cpoFee;
                                }
                            }
                            //                                             $couponDiscount = ($startFinalPrice -$finalPrice);
                            $couponDiscount = array_sum($allCoupon);
                        }
                        
                        //is the coupon more than the max value?
                        if(isset($activeProp->maxValue) && !empty($activeProp->maxValue) && $activeProp->maxValue < $couponDiscount)
                        {
                            $maxDiff = $couponDiscount-$activeProp->maxValue;
                            $couponDiscount = $activeProp->maxValue;
                        }
                    }
                    return array('coupon'=>$couponDiscount);                   
        }