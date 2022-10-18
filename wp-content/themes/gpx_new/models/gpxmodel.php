<?php

use GPX\Model\Region;
use Illuminate\Support\Arr;
use GPX\Repository\RegionRepository;

function get_property_details($book, $cid)
{
    global $wpdb;
    $joinedTbl = map_dae_to_vest_properties();
    $results = [];
    $sql = $wpdb->prepare("SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinResort']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                    WHERE a.record_id=%s AND a.archived=0 AND a.active_rental_push_date != '2030-01-01'", $book);
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
            $sql = $wpdb->prepare("SELECT data FROM wp_cart WHERE cartID=%s AND weekID=%s ORDER BY id desc", [$_COOKIE['gpx-cart'],$prop->PID]);
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
        $sql = $wpdb->prepare("SELECT * FROM wp_resorts_meta WHERE ResortID=%s", $prop->ResortID);
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
            'SameResortExchangeFee'=>[],
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

                        if(isset($attributesList) && array_key_exists($rmk, $attributesList))
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

                                    if(!isset($thisset) || !in_array($rmval['desc'], $thisset))
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
        $sql = $wpdb->prepare("SELECT a.id, a.Name, a.Slug, a.Properties, a.Amount, a.SpecUsage, a.PromoType, a.master
    			FROM wp_specials a
                LEFT JOIN wp_promo_meta b ON b.specialsID=a.id
                LEFT JOIN wp_resorts c ON c.id=b.foreignID
                LEFT JOIN wp_gpxRegion d ON d.id=b.foreignID
                WHERE (SpecUsage = 'any'
                OR ((c.ResortID=%s AND b.refTable='wp_resorts')
                OR (b.reftable = 'wp_gpxRegion' AND d.id IN (%s)))
                OR SpecUsage LIKE '%%customer%%'
                OR SpecUsage LIKE '%%region%%')
                AND %s BETWEEN TravelStartDate AND TravelEndDate
                AND (StartDate <= %s AND EndDate >= %s)
                AND a.Type='promo'
                AND a.Active=1", [$prop->ResortID, $prop->gpxRegionID, $checkInDate, $todayDT, $todayDT]);
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
                        {
                            $skip = true;
                            $whySkip = 'minWeekPrice';
                        }
                        if($nostacking)
                        {
                            $sptPrice = $prop->Price;
                        }
                        else
                        {
                            $sptPrice = $specialDiscountPrice;
                        }
                        if($sptPrice < $specialMeta->minWeekPrice)
                        {
                            $skip = true;
                            $whySkip = 'minWeekPrice';
                        }
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
                            $sql = $wpdb->prepare("SELECT * FROM wp_cart WHERE cartID=%s",$boCartID);
                            $bocarts = $wpdb->get_results($sql);
                        }
                        if(isset($bocarts) && !empty($bocarts))
                        {
                            foreach($bocarts as $bocart)
                            {
                                $boPID = $bocart->propertyID;
                                $sql = $wpdb->prepare("SELECT price FROM wp_room WHERE id=%s", $boPID);
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
                            $whySkip = 'bogo';
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
                                            $sql = $wpdb->prepare("SELECT id FROM wp_specials WHERE master=%s", $row->master);
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
                                        $sql = $wpdb->prepare("SELECT lpid FROM wp_gpxPreHold WHERE weekId=%s AND user=%s AND lpid=%s", [$prop->weekId, $cid, $prop->weekId.$row->id]);
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
                                        $whySkip = 'nolpid';
                                        if(isset($_REQUEST['promo_debug']))
                                        {
                                            echo '<pre>'.print_r('skipped '.$row->id.': lpid', true).'</pre>';
                                        }
                                    }
                                }
//                                 if(isset($_REQUEST['debug_promo']))
//                                 {
//                                     echo '<pre>'.print_r("landing page id: ".$lpid, true).'</pre>';
//                                 }
                                //blackouts
                                if(isset($specialMeta->blackout) && !empty($specialMeta->blackout))
                                {
                                    foreach($specialMeta->blackout as $blackout)
                                    {
                                        if(strtotime($prop->checkIn) >= strtotime($blackout->start) && strtotime($prop->checkIn) <= strtotime($blackout->end))
                                        {
                                            $skip = true;
                                            $whySkip = 'blackout';
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
                                                $whySkip = 'resortBlackout';
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
                                                $whySkip = 'resortTravel';
                                            }
                                        }
                                    }
                                }

                                if(isset($bogominPID) && $bogominPID != $prop->id)
                                {
                                    $skip = true;
                                    $whySkip = 'bookEndDate';
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
                                                    $whySkip = 'customer';
                                                }
                                            }
                                            else
                                            {
                                                $skip = true;
                                                $whySkip = 'customer';
                                            }
                                        }

                                        if($skip)
                                        {
                                            $skippedBefore = true;
                                        }

                                        //usage resort
                                        if(isset($specialMeta->usage_region) && !empty($specialMeta->usage_region))
                                        {
                                            $usage_regions = json_decode($specialMeta->usage_region);
                                            $uregionsAr = Region::tree($usage_regions)->select('wp_gpxRegion.id')->pluck('id')->toArray();
                                            if(!in_array($prop->gpxRegionID, $uregionsAr))
                                            {
                                                $skip = true;
                                                $whySkip = 'usage_region';
                                                $maybeSkipRR[] = true;
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
                                                        $whySkip = 'customer';
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
                                                if(!in_array($_GET['book'], $specialMeta->usage_resort))
                                                {
                                                    if(isset($regionOK) && $regionOK == true)//if we set the region and it applies to this resort then the resort doesn't matter
                                                    {
                                                        //do nothing
                                                        $resortOK = true;
                                                    }
                                                    elseif(in_array($prop->resortId, $specialMeta->usage_resort))
                                                    {
                                                        //do nothing
                                                        $resortOK = true;
                                                    }
                                                    else
                                                    {
                                                        $skip = true;
                                                        $whySkip = 'usage_resort';
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

                                        //transaction type
                                        if(!empty($transactionType) && (in_array('ExchangeWeek', $transactionType) || !in_array('BonusWeek', $transactionType)))
                                        {
                                            if(!in_array($prop->WeekType, $transactionType))
                                            {
                                                $skip = true;
                                                $whySkip = 'transactionType';
                                            }
                                        }

                                        //useage DAE
                                        if(isset($specialMeta->useage_dae) && !empty($specialMeta->useage_dae))
                                        {
                                            //Only show if OwnerBusCatCode = DAE AND StockDisplay = ALL or GPX
                                            if((strtolower($prop->StockDisplay) == 'all' || (strtolower($prop->StockDisplay) == 'gpx' || strtolower($prop->StockDisplay) == 'usa gpx')) && (strtolower($prop->OwnerBusCatCode) == 'dae' || strtolower($prop->OwnerBusCatCode) == 'usa dae'))
                                            {
                                                // we're all good -- these are the only properties that should be displayed
                                            }
                                            else
                                            {
                                                $skip = true;
                                                $whySkip = 'useage_dae';
                                            }

                                        }
                                        //exclude resorts
                                        if(isset($specialMeta->exclude_resort) && !empty($specialMeta->exclude_resort))
                                        {
                                            foreach($specialMeta->exclude_resort as $exc_resort)
                                            {
                                                if($exc_resort == $prop->RID)
                                                {
                                                    $skip = true;
                                                    $whySkip = 'exclude_resort';
                                                    break;
                                                }
                                            }
                                        }
                                        //exclude regions
                                        if(isset($specialMeta->exclude_region) && !empty($specialMeta->exclude_region))
                                        {
                                            $exclude_regions = $specialMeta->exclude_region;
                                            $excregions = Region::tree($exclude_regions)->select('wp_gpxRegion.id')->pluck('id')->toArray();
                                            if(in_array($prop->gpxRegionID, $excregions))
                                            {
                                                $skip = true;
                                                $whySkip = 'exclude_region';
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
                                                    {
                                                        if($usermeta->$or == $prop->ResortName)
                                                        {
                                                            $skip = true;
                                                            $whySkip = 'home-resort';
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        //lead time
                                        $today = date('Y-m-d');
                                        if(isset($specialMeta->leadTimeMin) && !empty($specialMeta->leadTimeMin))
                                        {
                                            $ltdate = date('Y-m-d', strtotime($prop->checkIn." -".$specialMeta->leadTimeMin." days"));
                                            if($today > $ltdate)
                                            {
                                                $skip = true;
                                                $whySkip = 'leadTimeMin';
                                            }
                                        }

                                        if(isset($specialMeta->leadTimeMax) && !empty($specialMeta->leadTimeMax))
                                        {
                                            $ltdate = date('Y-m-d', strtotime($prop->checkIn." -".$specialMeta->leadTimeMax." days"));
                                            if($today < $ltdate)
                                            {
                                                $skip = true;
                                                $whySkip = 'leadTimeMax';
                                            }
                                        }
                                        if(isset($specialMeta->bookStartDate) && !empty($specialMeta->bookStartDate))
                                        {
                                            $bookStartDate = date('Y-m-d', strtotime($specialMeta->bookStartDate));
                                            if($today < $bookStartDate)
                                            {
                                                $skip = true;
                                                $whySkip = 'bookStartDate';
                                            }
                                        }

                                        if(isset($specialMeta->bookEndDate) && !empty($specialMeta->bookEndDate))
                                        {
                                            $bookEndDate = date('Y-m-d', strtotime($specialMeta->bookEndDate));
                                            if($today > $bookEndDate)
                                            {
                                                $skip = true;
                                                $whySkip = 'bookEndDate';
                                            }

                                        }

                                        if(!$skip)
                                        {

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
                                                    $thisSpecialPrice = str_replace(",", "", $specialDiscountPrice*(1-($discount/100)));
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
            $discountAmt = $stackPrice;
            if($discountType == 'Auto Create Coupon')
            {
                $discountAmt = '';
                $discount = '';
            }
            if($discountType == 'Set Amt')
            {
                $discountAmt = $prop->Price - $discount;
            }
            $data = array('prop'=>$prop,
                'discount'=>$discount,
                'discountAmt'=>$discountAmt,
                'specialPrice'=>$specialPrice,
                'promoTerms'=>$promoTerms,
            );
            if(isset($lpid) && !empty($lpid))
            {
                $data['lpid'] = $lpid;
            }
            if(!empty($promoName))
            {
                $data['promoName'] = $promoName;
            }
            if(!empty($activePromos))
            {
                $data['activePromos'] = $activePromos;
            }
            if(!empty($bogoSet))
            {
                $data['bogo'] = $bogoSet;
            }
            if(isset($autoCreateCoupons) && !empty($autoCreateCoupons))
            {
                $data['autoCoupons'] = $autoCreateCoupons;
            }
    }
    else
    {
        $data = array('error'=>'property');
    }

    return $data;
}

        function save_search($user='', $search = '', $type = '', $resorts='', $props='', $cid='')
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
                $sql = $wpdb->prepare("SELECT * FROM wp_gpxMemberSearch WHERE sessionID=%s", $user->searchSessionID);
                $sessionRow = $wpdb->get_row($sql);

                if(isset($sessionRow) && !empty($sessionRow)) {
                    $sessionMeta = json_decode($sessionRow->data);
                } else {
                    $sessionMeta = new stdClass();
                }
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
                                        if(isset($prop->specialPrice)) $summary[$searchTime]['resorts'][$key]['props']['specialPrice'] = $prop->specialPrice;
                                }
                        }
                $refpage = '';
                if(isset($_SERVER['HTTP_REFERER'])) $refpage = $_SERVER['HTTP_REFERER'];
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

                                $sql = $wpdb->prepare("SELECT * FROM wp_gpxMemberSearch WHERE sessionID=%s", $user->searchSessionID);
                                $sessionRow = $wpdb->get_row($sql);
                        }

                        $sessionMeta = new stdClass();

                        $prop = get_property_details($pid, $cid);

                        if(isset($select_month)) {
                            $month = $select_month;
                        }else {
                            $month = date( 'F' );
                        }
                        if(isset($select_year)) {
                            $year = $select_year;
                        }else {
                            $year = date( 'Y' );
                        }
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
                        if(isset($_COOKIE['gpx-cart'])) $searchCartID = $_COOKIE['gpx-cart'];
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

                        $sql = $wpdb->prepare("SELECT * FROM wp_gpxMemberSearch WHERE sessionID=%s", $user->searchSessionID);
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
                }  else {
                    $sessionMeta = new stdClass();
                }
                if(isset($select_month)) {
                    $month = $select_month;
                }else {
                    $month = date( 'F' );
                }
                if(isset($select_year)) {
                    $year = $select_year;
                }else {
                    $year = date( 'Y' );
                }
                $refpage = '';
                if(isset($_SERVER['HTTP_REFERER']))$refpage = $_SERVER['HTTP_REFERER'];

                $metaKey = 'resort-'.$resort->id;

                if(isset($rid) && !empty($rid))//set the data the first time
                {

                        $sql = $wpdb->prepare("SELECT * FROM wp_resorts WHERE id=%d", $rid);
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
                } else {
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
                if(isset($_COOKIE['gpx-cart'])) {
                    $searchCartID = $_COOKIE['gpx-cart'];
                }
                if(isset($sessionMeta->$metaKey)) {
                    $wpdb->update( 'wp_gpxMemberSearch',
                                   [
                                       'userID'    => $userID,
                                       'sessionID' => $user->searchSessionID,
                                       'cartID'    => $searchCartID,
                                       'data'      => $sessionMetaJson
                                   ],
                                   [ 'id' => $sessionRow->id ] );
                } else {
                    $wpdb->insert( 'wp_gpxMemberSearch',
                                   [
                                       'userID'    => $userID,
                                       'sessionID' => $user->searchSessionID,
                                       'cartID'    => $searchCartID,
                                       'data'      => $sessionMetaJson
                                   ] );
                }

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

            $sql = $wpdb->prepare("SELECT DISTINCT propertyID, data FROM wp_cart WHERE cartID=%s", $_COOKIE['gpx-cart']);
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
                                    $finalPrice = (float)$finalPrice + (float)$prop->Price;
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
                                    $sql = $wpdb->prepare("SELECT id, Properties, Amount FROM wp_specials WHERE id=%d", $activeCoupon);
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
                                        unset($couponDiscount);
                                        continue;
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

                                                            }
                                                                else
                                                                {
                                                                    $couponDiscount = $cpoFee-$active->Amount;

                                                                }

                                                                    if($couponDiscount > $cpoFee)
                                                                    {
                                                                        $couponDiscount = $cpoFee;
                                                                    }

                                                        }
                                                        break;

                                                    case 'Upgrade':
                                                        if(isset($upgradeFee) && !empty($upgradeFee))
                                                        {
                                                            if($activeProp->promoType == 'Pct Off')
                                                            {
                                                                $couponDiscount = number_format($upgradeFee*($active->Amount/100),2);

                                                            }
                                                                else
                                                                {
                                                                    $couponDiscount = $upgradeFee-$active->Amount;

                                                                }

                                                                    if($couponDiscount > $upgradeFee)
                                                                    {
                                                                        $couponDiscount = $upgradeFee;

                                                                    }
                                                        }
                                                        break;

                                                    case 'Guest Fees':
                                                        if(isset($gfAmt) && !empty($gfAmt))
                                                        {
                                                            if($activeProp->promoType == 'Pct Off')
                                                            {
                                                                $couponDiscount = number_format($gfAmt*($active->Amount/100),2);

                                                            }
                                                                else
                                                                {
                                                                    $couponDiscount = $active->Amount;

                                                                }

                                                                    if($couponDiscount > $gfAmt)
                                                                    {
                                                                        $couponDiscount = $gfAmt;

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
                                        $sql = $wpdb->prepare("SELECT * FROM wp_gpxTaxes WHERE ID=%d", $prop->taxID);
                                        $tax = $wpdb->get_row($sql);
                                        $taxPercent = 0;
                                        $flatTax = 0;
                                        for($t=1;$t<=3;$t++)
                                        {
                                            $tp = 'TaxPercent'.$t;
                                            $ft = 'FlatTax'.$t;
                                            if(!empty($tax->$tp))
                                                $taxPercent += (float)$tax->$tp;
                                                if(!empty($tax->$ft))
                                                    $flatTax += $tax->$ft;
                                        }
                                        if($taxPercent > 0)
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
                                        if($flatTax > 0)
                                            $taxAmount += (float)$flatTax;

                                            if($prop->taxMethod == 2)//deduct from price
                                            {
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
                                            $sql = $wpdb->prepare("SELECT *, a.id as cid, b.id as aid, c.id as oid FROM wp_gpxOwnerCreditCoupon a
                                            INNER JOIN wp_gpxOwnerCreditCoupon_activity b ON b.couponID=a.id
                                            INNER JOIN wp_gpxOwnerCreditCoupon_owner c ON c.couponID=a.id
                                            WHERE a.id=%d AND a.active=1 and c.ownerID=%d", [$thisOCC, $cid]);
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
                                                }
                                                //if we have a balance at this point the coupon is good
                                                if($balance > 0)
                                                {
                                                    unset($occUsed[$thisOCC]);
                                                    $avAmt = $indPrice[$book] - array_sum($occUsed);

                                                    if($balance < $avAmt)
                                                    {
                                                        $occUsed[$thisOCC] = $balance;
                                                        $occForActivity[$book][$thisOCC] = $balance;
                                                    }
                                                    else
                                                    {
                                                        $occUsed[$thisOCC] = $avAmt;
                                                        $occForActivity[$book][$thisOCC] = $avAmt;
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
            $sql = $wpdb->prepare("SELECT a.id, a.Name, a.Slug, a.Properties, a.Amount, a.SpecUsage, a.PromoType
    			FROM wp_specials a
                LEFT JOIN wp_promo_meta b ON b.specialsID=a.id
                LEFT JOIN wp_resorts c ON c.id=b.foreignID
                LEFT JOIN wp_gpxRegion d ON d.id=b.foreignID
                WHERE (SpecUsage = 'any'
                OR ((c.ResortID=%d AND b.refTable='wp_resorts')
                OR (b.reftable = 'wp_gpxRegion' AND d.id = %d))
                OR SpecUsage LIKE '%%customer%%'
                OR SpecUsage LIKE '%%region%%')
                AND %s BETWEEN TravelStartDate AND TravelEndDate
                AND (StartDate <= %s AND EndDate >= %s)
                AND a.Type='promo'
                AND a.Active=1", [$prop->ResortID, $prop->gpxRegionID, $checkInDate, $todayDT, $todayDT]);
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
                                $sql = $wpdb->prepare("SELECT * FROM wp_cart WHERE cartID=%s", $boCartID);
                                $bocarts = $wpdb->get_results($sql);
                            }
                            if(isset($bocarts) && !empty($bocarts))
                            {
                                foreach($bocarts as $bocart)
                                {
                                    $boPID = $bocart->propertyID;
                                    // @TODO Jonathan: there is no `id` column on the `wp_room` table, this should probably be `record_id`
                                    $sql = $wpdb->prepare("SELECT price FROM wp_room WHERE id=%d", $boPID);
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
                                        }
                                        else
                                        {
                                            //let's check to see if it was on hold
                                            $sql = $wpdb->prepare("SELECT lpid FROM wp_gpxPreHold
                                            WHERE weekId=%s AND user=%s AND lpid=%d", [$prop->weekId,$cid, $prop->weekId.$row->id]);
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
                                                $uregionsAr = Region::tree($usage_regions)->select('wp_gpxRegion.id')->pluck('id')->toArray();
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
                                                $excregions = Region::tree($exclude_regions)->select('wp_gpxRegion.id')->pluck('id')->toArray();
                                                if(in_array($prop->gpxRegionID, $excregions)){
                                                    $skip = true;
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

/**
 * @param $cid
 * @return bool
 */
function gpx_hold_check($cid){
    //query the database for this users' holds
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );

    $holds = $gpx->DAEGetWeeksOnHold($usermeta->DAEMemberNo);
    $credits = $gpx->DAEGetMemberCredits($usermeta->DAEMemberNo);

    //return true if credits+1 is greater than holds
    if($credits[0]+1 >= count($holds)) {
        return true;
    }
    else {
        return false;
    }

}

/**
 * @param $coupons
 * @return array|int[]
 */
function cart_coupon($coupons)  {
        global $wpdb;

        $couponDiscount = 0;
        foreach($coupons as $activeCoupon)
        {

            //verify that this coupon was only applied once -- if this is in the array then we already applied it
            if(isset($couponused[$activeCoupon]) && $couponused[$activeCoupon] == $book)
            {
                continue;
            }
            $couponused[$activeCoupon] = $book;
            $startFinalPrice = $finalPrice;
            $sql = $wpdb->prepare("SELECT id, Properties, Amount FROM wp_specials WHERE id=%d", $activeCoupon);
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
                unset($couponDiscount);
                continue;
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
