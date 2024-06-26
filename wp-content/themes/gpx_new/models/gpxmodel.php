<?php

use GPX\Model\Region;
use GPX\Model\UserMeta;
use Illuminate\Support\Arr;
use GPX\Repository\WeekRepository;
use GPX\Repository\RegionRepository;

/**
 * @param int $book Week ID
 * @param int $cid User ID
 *
 * @return array{error: string}|array{prop: stdClass, discount: string, discountAmt: string, specialPrice: string, promoTerms: string, lpid?: string, promoName?: string, activePromos?: int[], bogo?: string, autoCoupons?: int[]}
 */
function get_property_details( $book, $cid ) {
    global $wpdb;
    $results = [];
    $prop = WeekRepository::instance()->get_week_for_checkout( $book );
    if ( ! $prop ) {
        return [ 'error' => 'property' ];
    }
    $cart = gpx_get_cart();

    if ( isset( $_REQUEST['type'] ) ) {
        $prop->WeekType = $_REQUEST['type'];
    } elseif ( $cart->isSaved() ) {
        $sql = $wpdb->prepare( "SELECT data FROM wp_cart WHERE cartID=%s AND weekID=%s ORDER BY id desc", [
            $cart->cartid,
            $prop->PID,
        ] );
        $pdata = $wpdb->get_Var( $sql );

        if ( $pdata ) {
            $pdata = json_decode( $pdata );
            if ( ! empty( $prop->WeekType ) ) {
                $prop->WeekType = str_replace( " ", "", $pdata->weekType );
            }
        }
    }
    //use the exchange fee for the price?
    if ( $prop->WeekType == 'ExchangeWeek' || $prop->WeekType == 'Exchange Week' ) {
        $prop->Price = gpx_get_exchange_fee( $cid, $prop->id );
    }
    $prop->WeekPrice = $prop->Price;
    $sql = $wpdb->prepare( "SELECT * FROM wp_resorts_meta WHERE ResortID=%s", $prop->ResortID );
    $resortMetas = $wpdb->get_results( $sql );

    $rmFees = [
        'RentalFeeAmount' => [
            'WeekPrice',
            'Price',
        ],
        'UpgradeFeeAmount' => [],
        'CPOFeeAmount' => [],
        'GuestFeeAmount' => [],
        'SameResortExchangeFee' => [],
    ];

    foreach ( $resortMetas as $rm ) {
        $rmk = $rm->meta_key;
        if ( $rmArr = json_decode( $rm->meta_value, true ) ) {
            ksort( $rmArr );
            if ($rmk === 'ResortFeeSettings') {
                $settings = json_decode($rm->meta_value, true);
                if ($settings['enabled'] ?? false) {
                    $settings['total'] = $settings['fee'];
                    if ($settings['frequency'] === 'daily') {
                        $settings['total'] *= 7;
                    }
                    $regions = RegionRepository::instance()->breadcrumbs($prop->gpxRegionID);
                    if (empty(array_filter($regions, fn($region) => $region['show_resort_fees']))) {
                        $settings['enabled'] = false;
                    }
                }
                $prop->$rmk = $settings;
                continue;
            }

            foreach ( $rmArr as $rmdate => $rmvalues ) {
                // we need to display all of the applicaable alert notes
                if ( isset( $lastValue ) && ! empty( $lastValue ) ) {
                    $thisVal = $lastValue;
                } else {
                    if ( isset( $resort->$rmk ) ) {
                        $thisVal = $resort->$rmk;
                    }
                }
                $rmdates = explode( "_", $rmdate );
                if ( count( $rmdates ) == 1 && $rmdates[0] == '0' ) {
                    //do nothing
                } else {
                    //changing this to go by checkIn instead of the active date
                    $checkInForRM = strtotime( $prop->checkIn );
                    //check to see if the from date has started
                    //                                         if($rmdates[0] < strtotime("now"))
                    if ( $rmdates[0] <= $checkInForRM ) {
                        //this date has started we can keep working
                    } else {
                        //these meta items don't need to be used
                        continue;
                    }
                    //check to see if the to date has passed
                    //                                         if(isset($rmdates[1]) && ($rmdates[1] >= strtotime("now")))
                    if ( isset( $rmdates[1] ) && ( $checkInForRM > $rmdates[1] ) ) {
                        //these meta items don't need to be used
                        continue;
                    } else {
                        //this date is sooner than the end date we can keep working
                    }

                    if ( isset( $attributesList ) && array_key_exists( $rmk, $attributesList ) ) {
                        // this is an attribute list Handle it now...
                        $thisVal = $resort->$rmk;
                        $thisVal = json_encode( $rmvalues );
                    } elseif ( array_key_exists( $rmk, $rmFees ) ) {
                        //do we need to reset any of the fees?

                        //set this amount in the object
                        $prop->$rmk = $rmvalues;
                        if ( ! empty( $rmFees[ $rmk ] ) ) {
                            //if values exist then we need to overwrite
                            foreach ( $rmFees[ $rmk ] as $propRMK ) {
                                //if this is either week price or price then we only apply this to the correct week type...
                                if ( $rmk == 'ExchangeFeeAmount' ) {
                                    //$prop->WeekType cannot be RentalWeek or BonusWeek
                                    if ( $prop->WeekType == 'BonusWeek' || $prop->WeekType == 'RentalWeek' ) {
                                        continue;
                                    }
                                } elseif ( $rmk == 'RentalFeeAmount' ) {
                                    //$prop->WeekType cannot be ExchangeWeek
                                    if ( $prop->WeekType == 'ExchangeWeek' ) {
                                        continue;
                                    }

                                }
                                $fee = end( $rmvalues );
                                $prop->$propRMK = preg_replace( "/\d+([\d,]?\d)*(\.\d+)?/", $fee, $prop->$propRMK );
                            }
                        }
                    } else {
                        $rmval = is_array( $rmvalues ) ? end( $rmvalues ) : $rmvalues;
                        //set $thisVal = ''; if we should just leave this completely off when the profile button isn't selected
                        if ( isset( $resort->$rmk ) ) {
                            $thisVal = $resort->$rmk;
                        }
                        //check to see if this should be displayed in the booking path
                        if ( isset( $rmval['path'] ) && $rmval['path']['booking'] == 0 ) {
                            //this isn't supposed to be part of the booking path
                            continue;
                        }
                        if ( isset( $rmval['desc'] ) ) {
                            if ( $rmk == 'AlertNote' ) {

                                if ( ! isset( $thisset ) || ! in_array( $rmval['desc'], $thisset ) ) {
                                    $thisValArr[] = [
                                        'desc' => $rmval['desc'],
                                        'date' => $rmdates,
                                    ];
                                }
                                $thisset[] = $rmval['desc'];
                            } else {
                                $thisVal = $rmval['desc'];
                                $thisValArr = [];
                            }
                        }
                    }
                }
                if ( isset( $thisVal ) ) $lastValue = $thisVal;
            }
            if ( $rmk == 'AlertNote' && ! empty( $thisValArr ) ) {
                $thisVal = $thisValArr;
            }
            if ( isset( $thisVal ) ) {
                $prop->$rmk = $thisVal;
            }
        } else {
            if ( $rm->meta_value != '[]' ) {
                $prop->$rmk = $rm->meta_value;
            }
        }
    }

    $prop->image = [
        'thumbnail' => $prop->ImagePath1,
        'title' => mb_strtolower( $prop->ResortName ),
        'alt' => $prop->ResortName,
    ];
    $sql = $wpdb->prepare( "SELECT meta_value FROM wp_resorts_meta WHERE meta_key='images' AND ResortID=%s", $prop->ResortID );
    $rawResortImages = $wpdb->get_row( $sql );
    if ( $rawResortImages && ! empty( $rawResortImages->meta_value ) ) {
        $resortImages = json_decode( $rawResortImages->meta_value, true );
        $oneImage = Arr::first( $resortImages );
        $prop->image['thumbnail'] = $oneImage['src'];

        if ( $oneImage['type'] == 'uploaded' ) {
            $id = $oneImage['id'];
            $prop->image['alt'] = get_post_meta( $id, '_wp_attachment_image_alt', true );
            $prop->image['title'] = get_the_title( $id );
        }
    }

    $checkInDate = date( 'Y-m-d', strtotime( $prop->checkIn ) );
    $discount = '';
    $specialPrice = '';
    $todayDT = date( "Y-m-d 00:00:00" );
    $thisPromo = [];
    //are there specials?
    $sql = $wpdb->prepare( "SELECT a.id, a.Name, a.Slug, a.Properties, a.Amount, a.SpecUsage, a.PromoType, a.master
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
                AND a.Active=1", [ $prop->ResortID, $prop->gpxRegionID, $checkInDate, $todayDT, $todayDT ] );
    $rows = $wpdb->get_results( $sql );
    $promoTerms = '';
    $priceint = preg_replace( "/[^0-9\.]/", "", $prop->WeekPrice );
    if ( $priceint != $prop->Price ) $prop->Price = $priceint;
    if ( $rows ) {
        $specialDiscountPrice = $prop->Price;
        $i = 0;
        $promoTerms = [];
        $prop->specialDesc = '';
        foreach ( $rows as $row ) {
            $uregionsAr = [];
            $skip = false;
            $regionOK = false;
            $resortOK = false;
            $skippedBefore = false;
            $specialMeta = stripslashes_deep( json_decode( $row->Properties ) );

            $nostacking = false;
            if ( isset( $specialMeta->stacking ) && $specialMeta->stacking == 'No' ) {
                //we don't want to stack this promo
                $nostacking = true;
            }
            //week min cost
            if ( isset( $specialMeta->minWeekPrice ) && ! empty( $specialMeta->minWeekPrice ) ) {
                if ( $prop->WeekType == 'ExchangeWeek' ) {
                    $skip = true;
                    $whySkip = 'minWeekPrice';
                }
                if ( $nostacking ) {
                    $sptPrice = $prop->Price;
                } else {
                    $sptPrice = $specialDiscountPrice;
                }
                if ( $sptPrice < $specialMeta->minWeekPrice ) {
                    $skip = true;
                    $whySkip = 'minWeekPrice';
                }
            }
            //usage upsell
            $upsell = [];
            if ( isset( $specialMeta->upsellOptions ) && ! empty( $specialMeta->upsellOptions ) ) {

                $upsell[] = [
                    'option' => $specialMeta->upsellOptions,
                    'type' => $specialMeta->promoType,
                    'amount' => $row->Amount,
                ];
            }
            if ( $specialMeta->promoType == 'BOGO' || $specialMeta->promoType == 'BOGOH' ) {
                $bogomax = '';
                $bogomin = '';
                $bogoSet = $row->Slug;
                $sql = $wpdb->prepare( "SELECT * FROM wp_cart WHERE user=%s", $cid );
                $bocarts = $wpdb->get_results( $sql );
                $boCartID = Arr::first( $bocarts )?->cartID;
                if ( ! empty( $bocarts ) ) {
                    foreach ( $bocarts as $bocart ) {
                        $boPID = $bocart->propertyID;
                        $sql = $wpdb->prepare( "SELECT price FROM wp_room WHERE record_id=%s", $boPID );
                        $boPriceQ = $wpdb->get_row( $sql );
                        $bogo = $boPriceQ->Price;
                        if ( $bogo > $bogomax ) {
                            $bogomax = $bogo;
                        }
                        if ( empty( $bogomin ) ) {
                            $bogomin = $bogo;
                            $bogominPID = $boPID;
                        } elseif ( $bogo <= $bogomin ) {
                            $bogomin = $bogo;
                            $bogominPID = $boPID;
                        }
                    }
                    if ( $bogominPID == $prop->id ) {
                        if ( $specialMeta->promoType == 'BOGOH' ) {
                            $bogoPrice = number_format( $prop->Price / 2, 2 );
                        } else {
                            $bogoPrice = '0';
                        }
                    }
                }
                if ( count( $bocarts ) < 2 ) {
                    unset( $bogoPrice );
                    $skip = true;
                    $whySkip = 'bogo';
                }
            }
            $ttArr = Arr::wrap( $specialMeta->transactionType );
            $transactionType = [];
            foreach ( $ttArr as $tt ) {
                switch ( $tt ) {
                    case 'Upsell':
                        $transactionType['upsell'] = 'Upsell';
                        break;
                    case 'All':
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
            if (
                ! empty( $upsell ) ||
                ( $specialMeta->stacking == 'No' && $row->Amount > $discount ) ||
                ( $transactionType == $prop->WeekType || in_array( $prop->WeekType, $transactionType ) ) ||
                ( isset( $bogominPID ) && $bogominPID == $prop->id ) ||
                ( ( isset( $specialMeta->acCoupon ) && $specialMeta->acCoupon == 1 ) && ( $transactionType == $prop->WeekType || in_array( $prop->WeekType, $transactionType ) ) )
            ) {

                /*
                 * filter out conditions
                 */
                // landing page only
                //does this user have the cookie set in the database?
                if ( get_user_meta( $cid, "lppromoid|{$prop->weekId}|$row->id") ) {
                    $lpid = $prop->weekId . '|' . $row->id;
                }

                if ( isset( $specialMeta->availability ) && $specialMeta->availability == 'Landing Page' ) {
                    $lpid = '';
                    if ( isset( $_COOKIE[ "lppromoid|{$prop->weekId}|$row->id" ] ) ) {
                        // all good
                        $lpid = $prop->weekId . '|' . $row->id;
                    } elseif ( $row->master != 0 ) {
                        if ( isset( $_COOKIE[ "lppromoid|{$prop->weekId}|$row->master" ] ) ) {
                            $lpid = $prop->weekId . '|' . $row->master;
                        } else {
                            //are there other children of this parent
                            $sql = $wpdb->prepare( "SELECT id FROM wp_specials WHERE master=%s", $row->master );
                            $lpmasters = $wpdb->get_results( $sql );
                            foreach ( $lpmasters as $lpm ) {
                                if ( isset( $_COOKIE[ "lppromoid|{$prop->weekId}|$lpm->id" ] ) ) {
                                    $lpid = $prop->weekId . '|' . $lpm->id;
                                }
                            }
                        }
                    } else {
                        //let's check to see if it was on hold
                        $sql = $wpdb->prepare( "SELECT lpid FROM wp_gpxPreHold WHERE weekId=%d AND user=%d AND lpid IN (%d,%s)", [
                            $prop->weekId,
                            $cid,
                            $prop->weekId . $row->id,
                            $prop->weekId . '|' . $row->id,
                        ] );
                        $lpidRows = $wpdb->get_results( $sql );
                        foreach ( $lpidRows as $lpidRow ) {
                            if ( ! empty( $lpidRow->lpid ) ) {
                                $lpid = $lpidRow->lpid;
                            }
                        }
                    }

                    //if the lpid is empty then we can skip
                    if ( empty( $lpid ) ) {
                        $skip = true;
                        $whySkip = 'nolpid';
                    }
                }

                //blackouts
                if ( isset( $specialMeta->blackout ) && ! empty( $specialMeta->blackout ) ) {
                    foreach ( $specialMeta->blackout as $blackout ) {
                        if ( strtotime( $prop->checkIn ) >= strtotime( $blackout->start ) && strtotime( $prop->checkIn ) <= strtotime( $blackout->end ) ) {
                            $skip = true;
                            $whySkip = 'blackout';
                        }
                    }
                }
                //resort blackout dates
                if ( isset( $specialMeta->resortBlackout ) && ! empty( $specialMeta->resortBlackout ) ) {
                    foreach ( $specialMeta->resortBlackout as $resortBlackout ) {
                        //if this resort is in the resort blackout array then continue looking for the date
                        if ( in_array( $prop->resortID, $resortBlackout->resorts ) ) {
                            if ( strtotime( $prop->checkIn ) > strtotime( $resortBlackout->start ) && strtotime( $prop->checkIn ) < strtotime( $resortBlackout->end ) ) {
                                $skip = true;
                                $whySkip = 'resortBlackout';
                            }
                        }
                    }
                }
                //resort specific travel dates
                if ( isset( $specialMeta->resortTravel ) && ! empty( $specialMeta->resortTravel ) ) {
                    foreach ( $specialMeta->resortTravel as $resortTravel ) {
                        //if this resort is in the resort blackout array then continue looking for the date
                        if ( in_array( $prop->resortID, $resortTravel->resorts ) ) {
                            if ( strtotime( $prop->checkIn ) > strtotime( $resortTravel->start ) && strtotime( $prop->checkIn ) < strtotime( $resortTravel->end ) ) {
                                //all good
                            } else {
                                $skip = true;
                                $whySkip = 'resortTravel';
                            }
                        }
                    }
                }

                if ( isset( $bogominPID ) && $bogominPID != $prop->id ) {
                    $skip = true;
                    $whySkip = 'bookEndDate';
                }
                if ( $specialMeta->beforeLogin == 'Yes' && ! is_user_logged_in() ) {
                    $skip = true;
                }

                if ( strpos( $row->SpecUsage, 'customer' ) !== false )//customer specific
                {
                    if ( isset( $cid ) ) {
                        $specCust = (array) json_decode( $specialMeta->specificCustomer );
                        if ( ! in_array( $cid, $specCust ) ) {
                            $skip = true;
                            $whySkip = 'customer';
                        }
                    } else {
                        $skip = true;
                        $whySkip = 'customer';
                    }
                }

                if ( $skip ) {
                    $skippedBefore = true;
                }

                //usage resort
                if ( isset( $specialMeta->usage_region ) && ! empty( $specialMeta->usage_region ) ) {
                    $usage_regions = json_decode( $specialMeta->usage_region );
                    $uregionsAr = Region::tree( $usage_regions )->select( 'wp_gpxRegion.id' )->pluck( 'id' )->toArray();
                    if ( ! in_array( $prop->gpxRegionID, $uregionsAr ) ) {
                        $skip = true;
                        $whySkip = 'usage_region';
                        $maybeSkipRR[] = true;
                        $regionOK = 'no';
                    } else {
                        $regionOK = 'yes';
                    }
                }

                //usage resort
                if ( isset( $specialMeta->usage_resort ) && ! empty( $specialMeta->usage_resort ) ) {
                    if ( isset( $cart ) ) {
                        if ( ! in_array( $cart->propertyID, $specialMeta->usage_resort ) ) {
                            if ( isset( $regionOK ) && $regionOK == true )//if we set the region and it applies to this resort then the resort doesn't matter
                            {
                                //do nothing
                                $resortOK = true;
                            } else {
                                $skip = true;
                                $whySkip = 'customer';
                                $maybeSkipRR[] = true;
                            }
                        } else {
                            $resortOK = true;
                        }
                    } elseif ( isset( $book ) ) {
                        if ( ! in_array( $book, $specialMeta->usage_resort ) ) {
                            if ( isset( $regionOK ) && $regionOK == true )//if we set the region and it applies to this resort then the resort doesn't matter
                            {
                                //do nothing
                                $resortOK = true;
                            } elseif ( in_array( $prop->resortId, $specialMeta->usage_resort ) ) {
                                //do nothing
                                $resortOK = true;
                            } else {
                                $skip = true;
                                $whySkip = 'usage_resort';
                                $maybeSkipRR[] = true;
                            }
                        } else {
                            $resortOK = true;
                        }
                    }

                    if ( $resortOK && ! $skippedBefore ) {
                        $skip = false;
                    }

                }

                //transaction type
                if ( ! empty( $transactionType ) && ( in_array( 'ExchangeWeek', $transactionType ) || ! in_array( 'BonusWeek', $transactionType ) ) ) {
                    if ( ! in_array( $prop->WeekType, $transactionType ) ) {
                        $skip = true;
                        $whySkip = 'transactionType';
                    }
                }

                //useage DAE
                if ( isset( $specialMeta->useage_dae ) && ! empty( $specialMeta->useage_dae ) ) {
                    //Only show if OwnerBusCatCode = DAE AND StockDisplay = ALL or GPX
                    if ( ( strtolower( $prop->StockDisplay ) == 'all' || ( strtolower( $prop->StockDisplay ) == 'gpx' || strtolower( $prop->StockDisplay ) == 'usa gpx' ) ) && ( strtolower( $prop->OwnerBusCatCode ) == 'dae' || strtolower( $prop->OwnerBusCatCode ) == 'usa dae' ) ) {
                        // we're all good -- these are the only properties that should be displayed
                    } else {
                        $skip = true;
                        $whySkip = 'useage_dae';
                    }

                }
                //exclude resorts
                if ( isset( $specialMeta->exclude_resort ) && ! empty( $specialMeta->exclude_resort ) ) {
                    foreach ( $specialMeta->exclude_resort as $exc_resort ) {
                        if ( $exc_resort == $prop->RID ) {
                            $skip = true;
                            $whySkip = 'exclude_resort';
                            break;
                        }
                    }
                }
                //exclude regions
                if ( isset( $specialMeta->exclude_region ) && ! empty( $specialMeta->exclude_region ) ) {
                    $exclude_regions = $specialMeta->exclude_region;
                    $excregions = Region::tree( $exclude_regions )->select( 'wp_gpxRegion.id' )->pluck( 'id' )->toArray();
                    if ( in_array( $prop->gpxRegionID, $excregions ) ) {
                        $skip = true;
                        $whySkip = 'exclude_region';
                    }
                }
                //exclude home resort
                if ( isset( $specialMeta->exclusions ) && $specialMeta->exclusions == 'home-resort' ) {
                    if ( isset( $usermeta ) && ! empty( $usermeta ) ) {
                        $ownresorts = [ 'OwnResort1', 'OwnResort2', 'OwnResort3' ];
                        foreach ( $ownresorts as $or ) {
                            if ( isset( $usermeta->$or ) ) {
                                if ( $usermeta->$or == $prop->ResortName ) {
                                    $skip = true;
                                    $whySkip = 'home-resort';
                                }
                            }
                        }
                    }
                }

                //lead time
                $today = date( 'Y-m-d' );
                if ( isset( $specialMeta->leadTimeMin ) && ! empty( $specialMeta->leadTimeMin ) ) {
                    $ltdate = date( 'Y-m-d', strtotime( $prop->checkIn . " -" . $specialMeta->leadTimeMin . " days" ) );
                    if ( $today > $ltdate ) {
                        $skip = true;
                        $whySkip = 'leadTimeMin';
                    }
                }

                if ( isset( $specialMeta->leadTimeMax ) && ! empty( $specialMeta->leadTimeMax ) ) {
                    $ltdate = date( 'Y-m-d', strtotime( $prop->checkIn . " -" . $specialMeta->leadTimeMax . " days" ) );
                    if ( $today < $ltdate ) {
                        $skip = true;
                        $whySkip = 'leadTimeMax';
                    }
                }
                if ( isset( $specialMeta->bookStartDate ) && ! empty( $specialMeta->bookStartDate ) ) {
                    $bookStartDate = date( 'Y-m-d', strtotime( $specialMeta->bookStartDate ) );
                    if ( $today < $bookStartDate ) {
                        $skip = true;
                        $whySkip = 'bookStartDate';
                    }
                }

                if ( isset( $specialMeta->bookEndDate ) && ! empty( $specialMeta->bookEndDate ) ) {
                    $bookEndDate = date( 'Y-m-d', strtotime( $specialMeta->bookEndDate ) );
                    if ( $today > $bookEndDate ) {
                        $skip = true;
                        $whySkip = 'bookEndDate';
                    }

                }

                if ( ! $skip ) {

                    //was this promo already applied?
                    if ( in_array( $row->id, $thisPromo ) ) {
                        continue;
                    }
                    $thisPromo[] = $row->id;

                    if ( isset( $specialMeta->terms ) ) {
                        if ( ! in_array( $specialMeta->terms, $promoTerms ) ) {
                            $promoTerms[ $i ] = $specialMeta->terms;
                        }
                    }
                    if ( isset( $specialMeta->icon ) && isset( $specialMeta->desc ) ) {
                        $prop->specialIcon = $specialMeta->icon;
                        $prop->specialDesc = $specialMeta->desc;
                    }
                    /*
                     * slashes not working -- adding the specialmeta slash option
                     */
                    if ( isset( $specialMeta->slash ) ) {
                        $prop->slash = $specialMeta->slash;
                    }
                    if ( isset( $specialMeta->acCoupon ) && $specialMeta->acCoupon == 1 ) {
                        $autoCreateCoupons[] = $row->id;
                    }
                    if ( $specialMeta->promoType != 'Set Amt' && $row->Amount == 0 ) //if the amount isn't set then no discounts apply skip the rest
                    {
                        continue;
                    }
                    $singleUpsellDiscount = false;
                    if ( ! empty( $upsell ) ) {
                        if ( count( $specialMeta->transactionType ) == 1 ) {
                            $singleUpsellDiscount = true;
                        }
                        $prop->upsellDisc = $upsell;
                    }

                    if ( ! $singleUpsellDiscount ) {
                        $promoName = $row->Name;
                        $discountType = $specialMeta->promoType;
                        $discount = $row->Amount;
                        if ( $discountType == 'Pct Off' ) {
                            $thisSpecialPrice = str_replace( ",", "", $specialDiscountPrice * ( 1 - ( $discount / 100 ) ) );
                            if ( ( isset( $specialPrice ) && ( $thisSpecialPrice < $specialPrice || empty( $specialPrice ) ) ) || empty( $specialPrice ) ) {
                                $specialPrice = $thisSpecialPrice;
                                $thisDiscounted = true;
                            }
                        } elseif ( $discountType == 'Dollar Off' ) {
                            $thisSpecialPrice = $specialDiscountPrice - $discount;
                            if ( ( isset( $specialPrice ) && ( $thisSpecialPrice < $specialPrice || empty( $specialPrice ) ) ) || empty( $specialPrice ) ) {
                                $specialPrice = $thisSpecialPrice;
                                $thisDiscounted = true;
                            }
                        } elseif ( $discountType == 'Set Amt' ) {
                            if ( $discount < $prop->Price ) {
                                $thisSpecialPrice = $discount;
                                if ( ( isset( $specialPrice ) && ( $thisSpecialPrice < $specialPrice || empty( $specialPrice ) ) ) || empty( $specialPrice ) ) {
                                    $specialPrice = $thisSpecialPrice;
                                    $thisDiscounted = true;
                                }
                            } else {
                                continue;
                            }
                        } elseif ( $discount < $prop->Price ) {
                            $specialPrice = $discount;
                            if ( ( isset( $specialPrice ) && ( $thisSpecialPrice < $specialPrice || empty( $specialPrice ) ) ) || empty( $specialPrice ) ) {
                                $specialPrice = $thisSpecialPrice;
                                $thisDiscounted = true;
                            }

                        }
                        if ( isset( $bogoPrice ) ) {
                            $specialPrice = $bogoPrice;
                            if ( ( isset( $specialPrice ) && ( $thisSpecialPrice < $specialPrice || empty( $specialPrice ) ) ) || empty( $specialPrice ) ) {
                                $specialPrice = $thisSpecialPrice;
                                $thisDiscounted = true;
                            }
                        }
                        if ( $specialPrice <= 0 ) {
                            $specialPrice = '0.00';
                        }
                        if ( isset( $specialMeta->stacking ) && $specialMeta->stacking == 'No' && $specialPrice > 0 ) {
                            //check if this amount is less than the other promos
                            if ( $discountType == 'Pct Off' ) {
                                $thisStackSpecialPrice = number_format( $prop->Price * ( 1 - ( $discount / 100 ) ), 2 );
                                if ( ( isset( $stackPrice ) && ( $thisStackSpecialPrice < $stackPrice || empty( $stackPrice ) ) ) || empty( $stackPrice ) ) {
                                    $stackPrice = $thisStackSpecialPrice;
                                    $thisDiscounted = true;
                                }
                            } elseif ( $discountType == 'Dollar Off' ) {
                                $thisStackSpecialPrice = $prop->Price - $discount;
                                if ( ( isset( $stackPrice ) && ( $thisStackSpecialPrice < $stackPrice || empty( $stackPrice ) ) ) || empty( $stackPrice ) ) {
                                    $stackPrice = $thisStackSpecialPrice;
                                    $thisDiscounted = true;
                                }
                            } elseif ( $discount < $prop->Price ) {
                                $thisStackSpecialPrice = $discount;
                                if ( ( isset( $stackPrice ) && ( $thisStackSpecialPrice < $stackPrice || empty( $stackPrice ) ) ) || empty( $stackPrice ) ) {
                                    $stackPrice = $thisStackSpecialPrice;
                                    $thisDiscounted = true;
                                }
                            }
                            if ( $stackPrice != 0 && $stackPrice < $specialDiscountPrice ) {
                                $thisSpecialPrice = $stackPrice;
                                if ( ( isset( $specialPrice ) && ( $thisSpecialPrice > $specialPrice || empty( $specialPrice ) ) ) || empty( $specialPrice ) ) {
                                    $specialPrice = $thisSpecialPrice;
                                    $thisDiscounted = true;
                                }
                            } else {
                                unset( $promoTerms[ $i ] );
                                $thisSpecialPrice = $specialDiscountPrice;
                                if ( ( isset( $specialPrice ) && ( $thisSpecialPrice > $specialPrice || empty( $specialPrice ) ) ) || empty( $specialPrice ) ) {
                                    $specialPrice = $thisSpecialPrice;
                                    $thisDiscounted = true;
                                }
                            }
                        }
                        if ( isset( $promoTerms[ $i ] ) ) {
                            if ( isset( $specialMeta->icon ) && isset( $specialMeta->desc ) ) {
                                $prop->specialIcon = $specialMeta->icon;
                                $prop->specialDesc .= $specialMeta->desc;
                            }
                        }

                        $specialDiscountPrice = $specialPrice;
                    }
                    $activePromos[] = $row->id;
                }
            }
            $i ++;
        }
    }
    $discountAmt = $stackPrice ?? '';
    $discountType = $discountType ?? '';
    if ( $discountType == 'Auto Create Coupon' ) {
        $discountAmt = '';
        $discount = '';
    }
    if ( $discountType == 'Set Amt' ) {
        $discountAmt = $prop->Price - $discount;
    }
    $prop->DisplayWeekType = $prop->WeekType;
    if ( $prop->WeekType == 'ExchangeWeek' ) {
        $prop->priceorfee = "Exchange Fee";
        $prop->DisplayWeekType = 'Exchange Week';
    } else {
        $prop->priceorfee = 'Price';
        $prop->DisplayWeekType = 'Rental Week';
    }

    $prop->displayPrice = '$' . round( $prop->WeekPrice, 0 );
    if ( ! empty( $specialPrice ) && $specialPrice != $prop->Price ) {
        $numformat = 0;
        if ( ! empty( $prop->specialIcon ) || ( isset( $prop->slash ) && $prop->slash == 'Force Slash' ) ) {
            $prop->displayPrice = '<span style="text-deocoration: line-through;">$' . round( $prop->WeekPrice, 0 ) . '</span>';
        }
        //let's get the price into the correct format...
        $reformatWeekPrice = str_replace( ",", "", $prop->WeekPrice );
        $reformatSpecialPrice = str_replace( ",", "", $specialPrice );
        $reformatSpecialPrice = number_format( $reformatSpecialPrice, $numformat, ".", "" );
        $reformatPrice = str_replace( ",", "", $prop->Price );
        $reformatPrice = number_format( $reformatPrice, $numformat, ".", "" );
        $prop->displayPrice .= '$' . str_replace( $reformatPrice, $reformatSpecialPrice, $reformatWeekPrice );
    }

    $gfAmount = $gfAmount ?? 0;
    $gfAmt = 0;
    $prop->guestFeesEnabled = ( isset( $prop->guestFeesEnabled ) && $prop->guestFeesEnabled ) || ( get_option( 'gpx_global_guest_fees' ) == '1' && ( get_option( 'gpx_gf_amount' ) && get_option( 'gpx_gf_amount' ) > $gfAmount ) );
    if ( $prop->guestFeesEnabled ) {
        if ( get_option( 'gpx_global_guest_fees' ) == '1' && ( get_option( 'gpx_gf_amount' ) && get_option( 'gpx_gf_amount' ) > $gfAmount ) ) {
            $gfAmt = get_option( 'gpx_gf_amount' );
        }
        if ( isset( $prop->GuestFeeAmount ) && ! empty( $prop->GuestFeeAmount ) ) {
            $gfAmt = $prop->GuestFeeAmount;
        }
        if ( isset( $prop->upsellDisc ) ) {
            $upsellDisc = $prop->upsellDisc;
            if ( is_array( $upsellDisc ) ) {
                foreach ( $upsellDisc as $ud ) {
                    if ( ( $ud['option'] == 'Guest Fees' || in_array( 'Guest Fees', $ud['option'] ) ) ) {
                        $guestfeesenabled = true;
                    }
                }
            } else {
                if ( ( $upsellDisc['option'] == 'Guest Fees' || in_array( 'Guest Fees', $upsellDisc['option'] ) ) ) {
                    $guestfeesenabled = true;
                }
            }
        }

        if ( isset( $guestfeesenabled ) ) {
            foreach ( $upsellDisc as $usd ) {
                if ( $usd['type'] == 'Pct Off' ) {
                    $gfDisc = number_format( $gfAmt * ( $usd['amount'] / 100 ), 0 );
                } elseif ( $usd['type'] == 'Set Amt' ) {
                    $gfDisc = $gfAmt;
                } else {
                    $gfDisc = $usd['amount'];
                }

                if ( $gfDisc > $gfAmt ) {
                    $gfDisc = $gfAmt;
                }

                $gfSlash = $gfSlash + $gfAmt;

                $gfAmt = $gfAmt - $gfDisc;
            }
        }
    }
    $prop->gfAmt = $gfAmt;

    $data = [
        'prop' => $prop,
        'discount' => $discount,
        'discountAmt' => $discountAmt,
        'specialPrice' => $specialPrice,
        'promoTerms' => $promoTerms,
    ];
    if ( isset( $lpid ) && ! empty( $lpid ) ) {
        $data['lpid'] = $lpid;
    }
    if ( ! empty( $promoName ) ) {
        $data['promoName'] = $promoName;
    }
    if ( ! empty( $activePromos ) ) {
        $data['activePromos'] = $activePromos;
    }
    if ( ! empty( $bogoSet ) ) {
        $data['bogo'] = $bogoSet;
    }
    if ( isset( $autoCreateCoupons ) && ! empty( $autoCreateCoupons ) ) {
        $data['autoCoupons'] = $autoCreateCoupons;
    }


    return $data;
}

function save_search( $user = '', $search = '', $type = '', $resorts = '', $props = '', $cid = '' ) {
    global $wpdb;
    $propselects = [ 'id', 'WeekType', 'WeekPrice', 'Price', 'resortName', 'resortId', 'weekId' ];
    if ( isset( $props ) && ! empty( $props ) ) {
        foreach ( $props as $key => $val ) {
            if ( ! in_array( $key, $propselects ) ) {
                unset( $props->$key );
            }
        }
    }
    $searchTime = time();
    $sessionRow = '';
    if ( isset( $user->searchSessionID ) ) {
        $sesExp = explode( '-', $user->searchSessionID );
        $userID = $sesExp[0];
        $dt = new DateTime( "@$sesExp[1]" );
        $last_login = $dt->format( 'm/d/y h:i:s' );


        $userType = 'Owner';
        $loggedinuser = get_current_user_id();
        if ( $loggedinuser != $cid ) {
            $userType = 'Agent';
        }


    }
    if ( ! is_user_logged_in() ) {

        if ( isset( $_COOKIE['guest-searchSessionID'] ) ) {
            $user->seachSessionID = $_COOKIE['guest-searchSessionID'];
            $cid = '84521';
            $userID = '84521';
        } else {
            if ( ! isset( $user ) ) {
                $user = new stdClass();
            }
            ob_start();
            //set the cookie
            $user->searchSessionID = time() . rand( 1, 9 ) . '-' . time();
            ob_start();
            setcookie( 'guest-searchSessionID', $user->searchSessionID );
            ob_end_flush();
            $cid = '84521';
            $userID = '84521';
        }
        $userType = 'Guest';
    }
    if ( ! isset( $user->searchSessionID ) ) {
        return true;
    }
    $sql = $wpdb->prepare( "SELECT * FROM wp_gpxMemberSearch WHERE sessionID=%s", $user->searchSessionID );
    $sessionRow = $wpdb->get_row( $sql );

    if ( isset( $sessionRow ) && ! empty( $sessionRow ) ) {
        $sessionMeta = json_decode( $sessionRow->data );
    } else {
        $sessionMeta = new stdClass();
    }
    if ( ! empty( $resorts ) ) {
        foreach ( $resorts as $key => $value ) {
            $summary[ $searchTime ]['resorts'][ $key ]['name'] = $value['resort']->resortName;

            foreach ( $value['props'] as $prop ) {
                $summary[ $searchTime ]['resorts'][ $key ]['props'] = [
                    'price' => $prop->Price,
                    'checkIn' => $prop->checkIn,
                ];
                if ( isset( $prop->specialPrice ) ) $summary[ $searchTime ]['resorts'][ $key ]['props']['specialPrice'] = $prop->specialPrice;
            }
        }
    }
    $refpage = '';
    if ( isset( $_SERVER['HTTP_REFERER'] ) ) $refpage = $_SERVER['HTTP_REFERER'];
    switch ( $type ) {
        case 'search':
            $data = [
                'search' =>
                    [
                        'last_login' => $last_login,
                        'refDomain' => $refpage,
                        'currentPage' => $_SERVER['REQUEST_URI'],
                        'refPage' => get_permalink(),
                        'locationSearched' => $search,
                        'searchSummary' => $summary,
                        'user_type' => $userType,
                        'search_by_id' => $cid,
                    ],
            ];
            $metaKey = $searchTime;
            break;

        case 'select':
            $data = [
                'last_login' => $last_login,
                'refDomain' => $refpage,
                'currentPage' => $_SERVER['REQUEST_URI'],
                'property' => $props['prop'],
                'price' => $props['prop']->Price,
                'user_type' => $userType,
                'search_by_id' => $cid,
            ];
            if ( isset( $prop->specialPrice ) ) {
                $data['select']['specialPrice'] = $prop->specialPrice;
            }
            $metaKey = 'select-' . $props['prop']->resortID;
            break;

        case 'book':

        case 'ICE':
            $data = [
                'ICE' =>
                    [
                        'last_login' => $last_login,
                        'refDomain' => $refpage,
                        'currentPage' => $_SERVER['REQUEST_URI'],
                        'user_type' => $userType,
                        'search_by_id' => $cid,
                    ],
            ];
            $metaKey = 'ICE';
            break;
    }

    $sessionMeta->$metaKey = $data;
    $sessionMetaJson = json_encode( $sessionMeta );
    $searchCartID = '';
    if ( isset( $_COOKIE['gpx-cart'] ) ) {
        $searchCartID = $_COOKIE['gpx-cart'];
    }
    if ( isset( $sessionRow ) ) {
        $wpdb->update( 'wp_gpxMemberSearch', [
            'userID' => $userID,
            'sessionID' => $user->searchSessionID,
            'cartID' => $searchCartID,
            'data' => $sessionMetaJson,
        ], [ 'id' => $sessionRow->id ] );
    } else {
        $wpdb->insert( 'wp_gpxMemberSearch', [
            'userID' => $userID,
            'sessionID' => $user->searchSessionID,
            'cartID' => $searchCartID,
            'data' => $sessionMetaJson,
        ] );
    }

    if ( $userType == 'Guest' ) {
        return [ 'guest-searchSessionID' => $user->searchSessionID ];
    } else {
        return $searchTime;
    }
}

function save_search_book( int $pid, array $search ) {
    global $wpdb;

    $cid = gpx_get_switch_user_cookie();
    $user = get_userdata( $cid );
    $loggedinuser = get_current_user_id();
    $userType = $loggedinuser === $cid ? 'Owner' : 'Agent';

    $searchTime = time();
    $sessionRow = '';
    if ( isset( $user->searchSessionID ) ) {
        $sesExp = explode( '-', $user->searchSessionID );
        $userID = $sesExp[0];
        $dt = new DateTime( "@$sesExp[1]" );
        $last_login = $dt->format( 'm/d/y h:i:s' );

        $sql = $wpdb->prepare( "SELECT * FROM wp_gpxMemberSearch WHERE sessionID=%s", $user->searchSessionID );
        $sessionRow = $wpdb->get_row( $sql );
    }

    $sessionMeta = [];

    $prop = get_property_details( $pid, $cid );

    $month = $search['select_month'] ?? date( 'F' );
    $year = $search['select_year'] ?? date( 'Y' );
    $refpage = $_SERVER['HTTP_REFERER'] ?? '';

    $data = [
        'refDomain' => $refpage,
        'currentPage' => $_SERVER['REQUEST_URI'],
        'week_type' => $search['type'],
        'price' => $prop['prop']->WeekPrice,
        'id' => $pid,
        'name' => $prop['prop']->ResortName,
        'checkIn' => date( 'm/d/Y', strtotime( $prop['prop']->checkIn ) ),
        'beds' => $prop['prop']->bedrooms . " / " . $prop['prop']->sleeps,
        'search_location' => $search['location'] ?? null,
        'search_month' => $month,
        'search_year' => $year,
        'user_type' => $userType,
        'search_by_id' => $cid,
        'lpid' => $search['lpid'] ?? null,
    ];
    if ( isset( $prop['specialPrice'] ) ) {
        $data['select']['specialPrice'] = $prop['specialPrice'];
    }

    $metaKey = 'view-' . $pid;
    $sessionMeta[$metaKey] = $data;
    $cart = gpx_get_cart( $cid );

    $wpdb->insert( 'wp_gpxMemberSearch', [
        'userID' => $userID,
        'sessionID' => $user->searchSessionID,
        'cartID' => $cart->cartid,
        'data' => json_encode($sessionMeta),
    ] );

    return $searchTime;
}

function save_search_resort( $resort = null, $post = [] ) {
    global $wpdb;
    $searchTime = time();
    $cid = $post['cid'] ?? gpx_get_switch_user_cookie();
    if ( ! $cid ) return $searchTime;

    //extract($post);
    $user = get_userdata( $cid );
    $usermeta = UserMeta::load( $cid );
    $userType = $cid != get_current_user_id() ? 'Agent' : 'Owner';
    $userID = $user->ID;
    $data = [];
    $sessionMetas = [];
    if ( $usermeta->searchSessionID ) {
        $sql = $wpdb->prepare( "SELECT * FROM wp_gpxMemberSearch WHERE sessionID=%s", $usermeta->searchSessionID );
        $sessionRows = $wpdb->get_results( $sql, ARRAY_A );
        foreach ( $sessionRows as $sessionRow ) {
            $sessionMeta = json_decode( $sessionRow['data'] );
            foreach ( $sessionMeta as $sessionKey => $sessionValue ) {
                $sessionValue->sessionRowID = $sessionRow['id'];
                $sessionValue->sessionID = $sessionRow['sessionID'];
                $sessionMetas[ $sessionKey ] = $sessionValue;
            }
        }
    }
    $location = $post['location'] ?? null;
    $month = $post['select_month'] ?? date( 'F' );
    $year = $post['select_year'] ?? date( 'Y' );
    $refpage = $_SERVER['HTTP_REFERER'] ?? '';
    $metaKey = $resort ? 'resort-' . $resort->id : 'resort-';
    $rid = $post['rid'] ?? null;
    if ( $rid ) {
        //set the data the first time
        $sql = $wpdb->prepare( "SELECT * FROM wp_resorts WHERE id=%d", $rid );
        $resort = $wpdb->get_row( $sql );
        if ( $resort ) $metaKey = 'resort-' . $resort->id;

        if ( array_key_exists( $metaKey, $sessionMetas ) ) {
            $data = Arr::only( $sessionMetas, $metaKey );
            $data[ $metaKey ]->DateViewed = date( 'm/d/Y h:i:s' );
            $data[ $metaKey ]->search_location = $location;
            $data[ $metaKey ]->search_month = $month;
            $data[ $metaKey ]->search_year = $year;
            $data[ $metaKey ]->user_type = $userType;

        } else {
            $data = [
                $metaKey => [
                    'refDomain' => $refpage,
                    'currentPage' => $_SERVER['REQUEST_URI'] ?? '',
                    'ResortName' => $resort->ResortName ?? '',
                    'DateViewed' => date( 'm/d/Y h:i:s' ),
                    'id' => $resort->id ?? '',
                    'search_location' => $location,
                    'search_month' => $month,
                    'search_year' => $year,
                    'user_type' => $userType,
                    'search_by_id' => $cid,
                ],
            ];
        }
    } else {
        if ( array_key_exists( $metaKey, $sessionMetas ) ) {
            $data = Arr::only( $sessionMetas, $metaKey );
            $data[ $metaKey ]->user_type = $userType;
            $data[ $metaKey ]->DateViewed = date( 'm/d/Y h:i:s' );
        } else {
            $data[ $metaKey ] = [
                'refDomain' => $refpage,
                'currentPage' => $_SERVER['REQUEST_URI'] ?? '',
                'ResortName' => $resort->ResortName ?? '',
                'DateViewed' => date( 'm/d/Y h:i:s' ),
                'id' => $resort->id ?? '',
                'user_type' => $userType,
                'search_by_id' => $cid,
            ];
        }
    }
    $sessionMetaJson = json_encode( $data );
    $searchCartID = $_COOKIE['gpx-cart'] ?? '';
    if ( array_key_exists( $metaKey, $sessionMetas ) ) {
        $wpdb->update( 'wp_gpxMemberSearch',
            [
                'userID' => $userID,
                'sessionID' => $user->searchSessionID,
                'cartID' => $searchCartID,
                'data' => $sessionMetaJson,
            ],
            [ 'id' => $sessionMetas[ $metaKey ]->sessionRowID ] );
    } else {
        $wpdb->insert( 'wp_gpxMemberSearch',
            [
                'userID' => $userID,
                'sessionID' => $user->searchSessionID,
                'cartID' => $searchCartID,
                'data' => $sessionMetaJson,
            ] );
    }

    return $searchTime;
}

/**
 * @param $cid
 *
 * @return bool
 */
function gpx_hold_check( $cid ) {
    //query the database for this users' holds
    $gpx = new GpxRetrieve( GPXADMIN_API_URI, GPXADMIN_API_DIR );

    $usermeta = (object) array_map( function ( $a ) { return $a[0]; }, get_user_meta( $cid ) );

    $holds = WeekRepository::instance()->get_weeks_on_hold( $usermeta->DAEMemberNo );
    $credits = [ [ 0 ] ];

    //return true if credits+1 is greater than holds
    if ( $credits[0] + 1 >= count( $holds ) ) {
        return true;
    } else {
        return false;
    }

}

/**
 * @param $coupons
 *
 * @return array|int[]
 */
function cart_coupon( $coupons ) {
    global $wpdb;

    $couponDiscount = 0;
    foreach ( $coupons as $activeCoupon ) {

        //verify that this coupon was only applied once -- if this is in the array then we already applied it
        if ( isset( $couponused[ $activeCoupon ] ) && $couponused[ $activeCoupon ] == $book ) {
            continue;
        }
        $couponused[ $activeCoupon ] = $book;
        $startFinalPrice = $finalPrice;
        $sql = $wpdb->prepare( "SELECT id, Properties, Amount FROM wp_specials WHERE id=%d", $activeCoupon );
        $active = $wpdb->get_row( $sql );
        $activeProp = stripslashes_deep( json_decode( $active->Properties ) );

        if ( ( $couponkey > 20 && $book != $couponkey ) && ( $activeProp->promoType != 'BOGO' || $activeProp->promoType != 'BOGOH' ) ) {
            continue;
        }

        $discountTypes = [
            'Pct Off',
            'Dollar Off',
            'Set Amt',
            'BOGO',
            'BOGOH',
        ];
        foreach ( $discountTypes as $dt ) {
            if ( strpos( $activeProp->promoType, $dt ) ) {
                $activeProp->promoType = $dt;
            }
        }

        if ( isset( $activeProp->acCoupon ) && $activeProp->acCoupon == 1 ) {
            $couponTemplate = $activeProp->couponTemplate;
            unset( $couponDiscount );
            continue;
        }

        $singleUpsellOption = false;
        if ( isset( $activeProp->upsellOptions ) && ! empty( $activeProp->upsellOptions ) ) {
            if ( count( $activeProp->transactionType ) == 1 ) {
                $singleUpsellOption = true;
            }
            foreach ( $activeProp->upsellOptions as $upsellOption ) {
                switch ( $upsellOption ) {
                    case 'CPO':
                        if ( isset( $cpoFee ) && ! empty( $cpoFee ) ) {
                            if ( $activeProp->promoType == 'Pct Off' ) {
                                $couponDiscount = number_format( $cpoFee * ( $active->Amount / 100 ), 2 );
                            } else {
                                $couponDiscount = $cpoFee - $active->Amount;
                            }

                            if ( $couponDiscount > $cpoFee ) {
                                $couponDiscount = $cpoFee;
                            }

                        }
                        break;

                    case 'Upgrade':
                        if ( isset( $upgradeFee ) && ! empty( $upgradeFee ) ) {
                            if ( $activeProp->promoType == 'Pct Off' ) {
                                $couponDiscount = number_format( $upgradeFee * ( $active->Amount / 100 ), 2 );
                            } else {
                                $couponDiscount = $upgradeFee - $active->Amount;
                            }

                            if ( $couponDiscount > $upgradeFee ) {
                                $couponDiscount = $upgradeFee;
                            }
                        }
                        break;

                    case 'Guest Fees':
                        if ( isset( $gfAmt ) && ! empty( $gfAmt ) ) {
                            if ( $activeProp->promoType == 'Pct Off' ) {
                                $couponDiscount = number_format( $gfAmt * ( $active->Amount / 100 ), 2 );
                            } else {
                                $couponDiscount = $active->Amount;
                            }

                            if ( $couponDiscount > $gfAmt ) {
                                $couponDiscount = $gfAmt;
                            }
                        }
                        break;

                    case 'Extension Fees':

                        break;
                }
                $upselldisc[ $book ][ $upsellOption ] = $couponDiscount;
            }
            $indCouponDisc[ $book ] = array_sum( $upselldisc[ $book ] );
            if ( ! empty( $couponDiscount ) ) {
                $finalPrice = $finalPrice - $couponDiscount;
            }
        }
        if ( ! $singleUpsellOption ) {
            $pricewofees = ( $indPrice[ $book ] - array_sum( $indFees[ $book ] ) );
            if ( $activeProp->promoType == 'Pct Off' ) {
                //first let's calculate the discount
                $poDisc = ( $indPrice[ $book ] - array_sum( $indFees[ $book ] ) ) * ( ( $active->Amount / 100 ) );
                $allCoupon[ $book ] = $poDisc;
                $finalPrice = number_format( $finalPrice - $poDisc, 2 );
            } elseif ( $activeProp->promoType == 'BOGO' || $activeProp->promoType == 'BOGOH' ) {
                if ( isset( $cart->couponbogo ) ) {
                    $couponDiscountPrice = $indPrice[ $book ] - $cart->couponbogo;
                    $indBOGOCoupon[ $book ] = $indPrice[ $book ] - $cart->couponbogo;
                    if ( $couponDiscountPrice < 0 ) {
                        if ( $activeProp->promoType == 'BOGO' ) {
                            $couponDiscountPrice = 0;
                            $indBOGOCoupon[ $book ] = 0;
                        } else {
                            $couponDiscountPrice = $indFees[ $book ] / 2;
                            $indBOGOCoupon[ $book ] = $indFees[ $book ] / 2;
                        }
                    }
                    $allCoupon[ $book ] = $indBOGOCoupon[ $book ];
                    $finalPrice = $finalPrice - $couponDiscountPrice;
                }
            } elseif ( $activeProp->promoType == 'Dollar Off' ) {
                $finalPrice = $finalPrice - $active->Amount;
                $allCoupon[ $book ] = $active->Amount;
            } elseif ( $active->Amount < $indPrice[ $book ] ) {
                $allCoupon[ $book ] = $indPrice[ $book ] - $active->Amount;

                $finalPrice = $active->Amount;
                //if an upgrade fee is set then we need to add it back in to the set amount
                if ( isset( $upgradeFee ) ) {
                    $allCoupon[ $book ] -= $upgradeFee;
                    $finalPrice += $upgradeFee;
                }
                //if a CPO fee is set then we need to add it back in to the set amount
                if ( isset( $cpoFee ) ) {
                    $allCoupon[ $book ] -= $cpoFee;
                    $finalPrice += $cpoFee;
                }
            }
            $couponDiscount = array_sum( $allCoupon );
        }

        //is the coupon more than the max value?
        if ( isset( $activeProp->maxValue ) && ! empty( $activeProp->maxValue ) && $activeProp->maxValue < $couponDiscount ) {
            $maxDiff = $couponDiscount - $activeProp->maxValue;
            $couponDiscount = $activeProp->maxValue;
        }
    }

    return array( 'coupon' => $couponDiscount );
}
