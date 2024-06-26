<?php

/*
 * GPX Results Page Shortcode
 *
 * Displays results from params / request variables
 * Combined function to display results from other functions, posted search request
 * and custom requests.
 *
 * @param int|string $resortID
 * @param array|string $paginate
 * @param string $calendar
 * @return html|object returns an object when called from wp-ajax otherwise returns html
 */

use GPX\Model\Partner;
use GPX\Model\UserMeta;
use Illuminate\Support\Arr;
use GPX\Model\CustomRequest;
use GPX\Model\CustomRequestMatch;
use GPX\Repository\WeekRepository;
use GPX\Repository\RegionRepository;
use GPX\Repository\ResortRepository;

/*
 * GPX Results Page Shortcode
 *
 * Displays results from params / request variables
 * Combined function to display results from other functions, posted search request
 * and custom requests.
 *
 * @param int|string $resortID
 * @param array|string $paginate
 * @param string $calendar
 * @return html|object returns an object when called from wp-ajax otherwise returns html
 */
function gpx_result_page_sc($resortID = '', $paginate = [], $calendar = '') {
    global $wpdb;
    $ids = [];
    //     //update the join id

    if ($resortID) {
        $outputProps = true;
    }
    $paginate = [
        'limitstart' => $paginate['limitstart'] ?? 0,
        'limitcount' => $paginate['limitcount'] ?? 0,
    ];
    $limitStart = $paginate['limitstart'];
    $limitCount = $paginate['limitcount'];
    if ($paginate['limitcount'] > 0) {
        // some of the records might get filtered out, so we pull double what we need and will return the correct amount later.
        // this is to fix fewer than the requested amount of weeks being shown.
        $limit = $wpdb->prepare(" LIMIT %d, %d", [$paginate['limitstart'], $paginate['limitcount'] * 2]);
    }

    $cid = gpx_get_switch_user_cookie();

    if ($cid) {
        $user = get_userdata($cid);
        $usermeta = UserMeta::load($cid);
    }
    $request = wp_unslash($_REQUEST);
    extract($request, EXTR_SKIP);
    $select_month = gpx_search_month();
    $request['month'] = $request['select_month'] = $select_month;
    $select_year = gpx_search_year();
    $request['yr'] = $request['year'] = $request['select_year'] = $select_year;
    if (isset($request['destination'])) {
        $location = $request['location'] = $request['destination'];
        if ((int) $select_year <= 2018) {
            $alldates = true;
        }
    }
    $defaultExchangeFee = (int) get_option('gpx_exchange_fee', 0);

    //is this a previously matched result?
    if (isset($request['custom'])) {
        $props = [];
        $paginate['limitcount'] = 0;
        $paginate['limitstart'] = 0;
        $limitCount = 0;
        $limitStart = 0;
        $customRequest = CustomRequest::find($request['custom']);
        if ($customRequest) {
            $cdmObj = new CustomRequestMatch($customRequest);
            $matches = $cdmObj->get_matches();
            $week_ids = $cdmObj->has_restricted_date() ? $matches->notRestricted()->ids() : $matches->ids();
            $props = WeekRepository::instance()->get_weeks($week_ids);
        }
    } elseif (isset($request['matched'])) {
        $paginate['limitcount'] = 0;
        $paginate['limitstart'] = 0;
        $limitCount = 0;
        $limitStart = 0;
        $week_ids = explode(',', $request['matched']);
        $props = WeekRepository::instance()->get_weeks($week_ids);
    } else {
        if ((empty($select_month) && empty($select_year))) {
            $alldates = true;
        }
        if (mb_strtolower($select_month ?? 'any') == 'any') {
            $thisYear = date('Y');
            if (empty($select_year)) {
                $select_year = date('Y');
            }
            $monthstart = date($select_year . '-m-d');
            if ($thisYear != $select_year) {
                $monthstart = $select_year . '-01-01';
            }
            $monthend = $select_year . "-12-31";
        } else {
            $nextmonth = date('Y-m-d', strtotime('+1 month'));
            if (empty($select_year)) {
                $select_year = date('Y');
            }
            if (empty($select_month)) {
                $select_month = date('F');
            }
            $monthstart = date('Y-m-01', strtotime($select_month . "-" . $select_year));
            $today = date('Y-m-d');
            if ($monthstart < $today) {
                $monthstart = $today;
            }
            $monthend = date('Y-m-t', strtotime($select_month . "-" . $select_year));
        }

        $featuredresorts = gpx_get_featured_properties();


        if (isset($request['location']) && !empty($request['location'])) {
            $sql = $wpdb->prepare("SELECT id, lft, rght FROM wp_gpxRegion WHERE name=%s OR displayName=%s",
                [$location, $location]);
            $locs = $wpdb->get_results($sql);

            if (empty($locs)) {
                //if this location is a country
                $sql = $wpdb->prepare("SELECT a.lft, a.rght FROM wp_gpxRegion a
                        INNER JOIN wp_daeRegion b ON a.RegionID=b.id
                        INNER JOIN wp_gpxCategory c ON c.CountryID=b.CountryID
                            WHERE c.country = %s",
                    $location);
                $ranges = $wpdb->get_results($sql);
                if (!empty($ranges)) {
                    foreach ($ranges as $range) {
                        $sql = $wpdb->prepare("SELECT id, name FROM wp_gpxRegion
                                WHERE lft BETWEEN %d AND %d
                                    ORDER BY lft ASC",
                            [$range->lft, $range->rght]);
                        $rows = $wpdb->get_results($sql);
                        foreach ($rows as $row) {
                            $ids[] = $row->id;
                        }
                    }
                } else {
                    //see if this is a resort
                    $sql = $wpdb->prepare("SELECT id FROM wp_resorts WHERE ResortName=%s", $location);
                    $row = $wpdb->get_row($sql);
                    if (!empty($row)) {
                        //redirect to the resort
                        $redirectArr = [
                            'resortName' => $location,
                        ];
                        if (isset($select_month) && (!empty($select_month) || $select_month != 'f')) {
                            $redirectArr['month'] = $select_month;
                            if (isset($select_year) && !empty($select_year)) {
                                $redirectArr['yr'] = $select_year;
                            }
                        }
                        $redirectQS = http_build_query($redirectArr);
                        $redirectURL = home_url('/resort-profile/?' . $redirectQS);
                        echo "<script>window.location.href = '" . $redirectURL . "';</script>";
                        exit;
                    }
                }
            } else {
                foreach ($locs as $loc) {
                    $sql = $wpdb->prepare("SELECT id, name FROM wp_gpxRegion
                            WHERE lft BETWEEN %d AND %d
                                ORDER BY lft ASC",
                        [$loc->lft, $loc->rght]);
                    $rows = $wpdb->get_results($sql);
                    foreach ($rows as $row) {
                        $ids[] = $row->id;
                    }
                }
            }

            if (isset($request['destination'])) {
                $placeholders = empty($ids) ? '%s' : gpx_db_placeholders($ids, '%d');
                $sql = $wpdb->prepare("SELECT
                    `a`.`record_id` AS `id`, `a`.`check_in_date` AS `checkIn`, `a`.`check_out_date` AS `checkOut`, `a`.`price` AS `Price`,
                    `a`.`record_id` AS `weekID`, `a`.`record_id` AS `weekId`, `a`.`resort` AS `resortId`, `a`.`resort` AS `resortID`,
                    `a`.`availability` AS `StockDisplay`, `a`.`type` AS `WeekType`, DATEDIFF(`a`.`check_out_date`, `a`.`check_in_date`) AS `noNights`,
                    `a`.`active_rental_push_date` AS `active_rental_push_date`,
                    `b`.`Country` AS `Country`, `b`.`Region` AS `Region`, `b`.`Town` AS `Town`, `b`.`ResortName` AS `ResortName`,
                    `b`.`ImagePath1` AS `ImagePath1`, `b`.`AlertNote` AS `AlertNote`, `b`.`AdditionalInfo` AS `AdditionalInfo`,
                    `b`.`HTMLAlertNotes` AS `HTMLAlertNotes`, `b`.`ResortID` AS `ResortID`, `b`.`taxMethod` AS `taxMethod`,
                    `b`.`taxID` AS `taxID`, `b`.`gpxRegionID` AS `gpxRegionID`,
                    `c`.`number_of_bedrooms` AS `bedrooms`, `c`.`sleeps_total` AS `sleeps`, `c`.`name` AS `Size`,
                    `a`.`record_id` AS `PID`, `b`.`id` AS `RID`
                FROM `wp_room` AS `a`
                INNER JOIN `wp_resorts` AS `b` ON `a`.`resort` = `b`.`id`
                INNER JOIN `wp_unit_type` AS `c` ON `a`.`unit_type` = `c`.`record_id`
                WHERE b.GPXRegionID IN ({$placeholders}) AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1",
                    !empty($ids) ? $ids : ['na']
                );
            } else {
                $placeholders = empty($ids) ? '%s' : gpx_db_placeholders($ids, '%d');
                $values = empty($ids) ? ['na'] : $ids;
                $values[] = $monthstart;
                $values[] = $monthend;
                $sql = $wpdb->prepare("SELECT
                    `a`.`record_id` AS `id`, `a`.`check_in_date` AS `checkIn`, `a`.`check_out_date` AS `checkOut`, `a`.`price` AS `Price`,
                    `a`.`record_id` AS `weekID`, `a`.`record_id` AS `weekId`, `a`.`resort` AS `resortId`, `a`.`resort` AS `resortID`,
                    `a`.`availability` AS `StockDisplay`, `a`.`type` AS `WeekType`, DATEDIFF(`a`.`check_out_date`, `a`.`check_in_date`) AS `noNights`,
                    `a`.`active_rental_push_date` AS `active_rental_push_date`,
                    `b`.`Country` AS `Country`, `b`.`Region` AS `Region`, `b`.`Town` AS `Town`, `b`.`ResortName` AS `ResortName`, b.`ResortID` as `ResortID`,
                    `b`.`ImagePath1` AS `ImagePath1`, `b`.`AlertNote` AS `AlertNote`, `b`.`AdditionalInfo` AS `AdditionalInfo`,
                    `b`.`HTMLAlertNotes` AS `HTMLAlertNotes`, `b`.`ResortID` AS `ResortID`, `b`.`taxMethod` AS `taxMethod`,
                    `b`.`taxID` AS `taxID`, `b`.`gpxRegionID` AS `gpxRegionID`,
                    `c`.`number_of_bedrooms` AS `bedrooms`, `c`.`sleeps_total` AS `sleeps`, `c`.`name` AS `Size`,
                    `a`.`record_id` AS `PID`, `b`.`id` AS `RID`
                FROM `wp_room` AS `a`
                INNER JOIN `wp_resorts` AS `b` ON `a`.`resort` = `b`.`id`
                INNER JOIN `wp_unit_type` AS `c` ON `a`.`unit_type` = `c`.`record_id`
                WHERE b.GPXRegionID IN ({$placeholders}) AND a.check_in_date BETWEEN %s AND %s AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1",
                    $values
                );
            }
            $resortsSql = $wpdb->prepare("SELECT * FROM wp_resorts b WHERE GPXRegionID IN ({$placeholders}) AND active = 1",
                empty($ids) ? ['na'] : $ids);
        } elseif (isset($resortID)) {
            $values = [$resortID];
            if ($select_month != 'f') {
                $values[] = $monthstart;
                $values[] = $monthend;
                $destDateWhere = " AND (a.`check_in_date` BETWEEN %s AND %s) ";
            } else {
                $values[] = $today;
                $destDateWhere = " AND (a.`check_in_date` > %s) ";
            }

            $sql = $wpdb->prepare("SELECT
                `a`.`record_id` AS `id`, `a`.`check_in_date` AS `checkIn`, `a`.`check_out_date` AS `checkOut`, `a`.`price` AS `Price`,
                `a`.`record_id` AS `weekID`, `a`.`record_id` AS `weekId`, `a`.`resort` AS `resortId`, `a`.`resort` AS `resortID`,
                `a`.`availability` AS `StockDisplay`, `a`.`type` AS `WeekType`, DATEDIFF(`a`.`check_out_date`, `a`.`check_in_date`) AS `noNights`,
                `a`.`active_rental_push_date` AS `active_rental_push_date`,
                `b`.`Country` AS `Country`, `b`.`Region` AS `Region`, `b`.`Town` AS `Town`, `b`.`ResortName` AS `ResortName`, b.`ResortID` as `ResortID`,
                `b`.`ImagePath1` AS `ImagePath1`, `b`.`AlertNote` AS `AlertNote`, `b`.`AdditionalInfo` AS `AdditionalInfo`,
                `b`.`HTMLAlertNotes` AS `HTMLAlertNotes`, `b`.`ResortID` AS `ResortID`, `b`.`taxMethod` AS `taxMethod`,
                `b`.`taxID` AS `taxID`, `b`.`gpxRegionID` AS `gpxRegionID`,
                `c`.`number_of_bedrooms` AS `bedrooms`, `c`.`sleeps_total` AS `sleeps`, `c`.`name` AS `Size`,
                `a`.`record_id` AS `PID`, `b`.`id` AS `RID`
            FROM `wp_room` AS `a`
            INNER JOIN `wp_resorts` AS `b` ON `a`.`resort` = `b`.`id`
            INNER JOIN `wp_unit_type` AS `c` ON `a`.`unit_type` = `c`.`record_id`
            WHERE b.id = %d {$destDateWhere} AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1
                ORDER BY a.`check_in_date`",
                $values);
        } elseif (isset($alldates)) {
            $sql = $wpdb->prepare("SELECT
                `a`.`record_id` AS `id`, `a`.`check_in_date` AS `checkIn`, `a`.`check_out_date` AS `checkOut`, `a`.`price` AS `Price`,
                `a`.`record_id` AS `weekID`, `a`.`record_id` AS `weekId`, `a`.`resort` AS `resortId`, `a`.`resort` AS `resortID`,
                `a`.`availability` AS `StockDisplay`, `a`.`type` AS `WeekType`, DATEDIFF(`a`.`check_out_date`, `a`.`check_in_date`) AS `noNights`,
                `a`.`active_rental_push_date` AS `active_rental_push_date`,
                `b`.`Country` AS `Country`, `b`.`Region` AS `Region`, `b`.`Town` AS `Town`, `b`.`ResortName` AS `ResortName`, b.`ResortID` as `ResortID`,
                `b`.`ImagePath1` AS `ImagePath1`, `b`.`AlertNote` AS `AlertNote`, `b`.`AdditionalInfo` AS `AdditionalInfo`,
                `b`.`HTMLAlertNotes` AS `HTMLAlertNotes`, `b`.`ResortID` AS `ResortID`, `b`.`taxMethod` AS `taxMethod`,
                `b`.`taxID` AS `taxID`, `b`.`gpxRegionID` AS `gpxRegionID`,
                `c`.`number_of_bedrooms` AS `bedrooms`, `c`.`sleeps_total` AS `sleeps`, `c`.`name` AS `Size`,
                `a`.`record_id` AS `PID`, `b`.`id` AS `RID`
            FROM `wp_room` AS `a`
            INNER JOIN `wp_resorts` AS `b` ON `a`.`resort` = `b`.`id`
            INNER JOIN `wp_unit_type` AS `c` ON `a`.`unit_type` = `c`.`record_id`
            WHERE a.check_in_date > %s AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1
                ",
                $today);
        } else {
            $sql = $wpdb->prepare("SELECT
                `a`.`record_id` AS `id`, `a`.`check_in_date` AS `checkIn`, `a`.`check_out_date` AS `checkOut`, `a`.`price` AS `Price`,
                `a`.`record_id` AS `weekID`, `a`.`record_id` AS `weekId`, `a`.`resort` AS `resortId`, `a`.`resort` AS `resortID`,
                `a`.`availability` AS `StockDisplay`, `a`.`type` AS `WeekType`, DATEDIFF(`a`.`check_out_date`, `a`.`check_in_date`) AS `noNights`,
                `a`.`active_rental_push_date` AS `active_rental_push_date`,
                `b`.`Country` AS `Country`, `b`.`Region` AS `Region`, `b`.`Town` AS `Town`, `b`.`ResortName` AS `ResortName`, b.`ResortID` as `ResortID`,
                `b`.`ImagePath1` AS `ImagePath1`, `b`.`AlertNote` AS `AlertNote`, `b`.`AdditionalInfo` AS `AdditionalInfo`,
                `b`.`HTMLAlertNotes` AS `HTMLAlertNotes`, `b`.`ResortID` AS `ResortID`, `b`.`taxMethod` AS `taxMethod`,
                `b`.`taxID` AS `taxID`, `b`.`gpxRegionID` AS `gpxRegionID`,
                `c`.`number_of_bedrooms` AS `bedrooms`, `c`.`sleeps_total` AS `sleeps`, `c`.`name` AS `Size`,
                `a`.`record_id` AS `PID`, `b`.`id` AS `RID`
            FROM `wp_room` AS `a`
            INNER JOIN `wp_resorts` AS `b` ON `a`.`resort` = `b`.`id`
            INNER JOIN `wp_unit_type` AS `c` ON `a`.`unit_type` = `c`.`record_id`
            WHERE a.check_in_date BETWEEN %s AND %s AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1
                ",
                [$monthstart, $monthend]);
        }
        if (isset($limit) && !empty($limit)) {
            $sql .= $limit;
        }

        if ($resortID || !empty($ids)) {
            $props = $wpdb->get_results($sql);
        }
    }
    $_REQUEST['yr'] = $select_year;
    $_REQUEST['month'] = $select_month;

    $totalCnt = isset($props) ? count($props) : 0;
    $cntResults = $totalCnt;

    if (!empty($props) || isset($resortsSql)) {
        //let's first get query specials by the variables that are already set
        $todayDT = date("Y-m-d 00:00:00");
        $placeholders = gpx_db_placeholders($ids, '%d');
        $values = $ids;
        $values[] = $todayDT;
        $values[] = $todayDT;
        $resorts = [];
        $sql = $wpdb->prepare("SELECT a.id, a.Name, a.Properties, a.Amount, a.SpecUsage, a.TravelStartDate, a.TravelEndDate
        FROM wp_specials a
        LEFT JOIN wp_promo_meta b ON b.specialsID=a.id
        LEFT JOIN wp_resorts c ON c.id=b.foreignID
        LEFT JOIN wp_gpxRegion d ON d.id=b.foreignID
        WHERE
                (SpecUsage = 'any'
             OR   ((b.reftable = 'wp_gpxRegion' AND d.id IN ({$placeholders})))
                    OR SpecUsage LIKE '%%customer%%'
                    OR SpecUsage LIKE '%%dae%%')
        AND Type='promo'
        AND (StartDate <= %s AND EndDate >= %s)
        AND a.Active=1
            GROUP BY a.id",
            $values);
        $firstRows = $wpdb->get_results($sql);

        $prop_string = [];
        $new_props = [];
        foreach ($props as $p) {
            $week_date_size = $p->resortId . '=' . $p->WeekType . '=' . date('m/d/Y',
                    strtotime($p->checkIn)) . '=' . $p->Size;
            if (!in_array($week_date_size, $prop_string)) {
                $new_props[] = $p;
            }
            array_push($prop_string, $week_date_size);
        }


        $count_week_date_size = (array_count_values($prop_string));

        $props = $new_props;
        unset($new_props);

        $is_partner = Partner::where('user_id', $cid)->exists();
        $theseResorts = [];
        // Check availability
        $props = array_filter($props, function ($prop) use ($cid, $is_partner) {
            $availability = (int) ($prop->StockDisplay ?? null);

            return match ($availability) {
                2 => !$is_partner,
                3 => $is_partner,
                default => true,
            };
        });
        $props = array_map(function ($prop) use (&$count_week_date_size) {
            $string_week_date_size = $prop->resortId . '=' . $prop->WeekType . '=' . date('m/d/Y',
                    strtotime($prop->checkIn)) . '=' . $prop->Size;
            $prop->prop_count = $count_week_date_size[$string_week_date_size];

            return $prop;
        }, $props);

        $regionRepository = RegionRepository::instance();

        $theseResorts = array_column($props, 'ResortID');
        $theseResorts = array_combine($theseResorts, $theseResorts);

        //get all the regions that these properties belongs to
        $propRegionParentIDs = array_reduce($props, function (array $regions, $prop) use ($regionRepository) {
            if (array_key_exists($prop->ResortID, $regions)) {
                return $regions;
            }
            $regions[$prop->ResortID] = array_column($regionRepository->breadcrumbs($prop->gpxRegionID), 'id');

            return $regions;
        }, []);

        $resortDates = Arr::keyBy(array_map(function ($prop) use ($propRegionParentIDs) {
            $rdgp = $prop->ResortID . strtotime($prop->checkIn);

            return [
                'key' => $rdgp,
                'ResortID' => $prop->ResortID,
                'checkIn' => date('Y-m-d', strtotime($prop->checkIn)),
                'propRegionParentIDs' => $propRegionParentIDs[$prop->ResortID],
            ];
        }, $props), 'key');

        $specRows = [];

        // store $resortMetas as array
        if (!empty($theseResorts)) {
            $placeholders = gpx_db_placeholders($theseResorts, '%d');
            $sql = $wpdb->prepare("SELECT * FROM wp_resorts_meta WHERE ResortID IN ({$placeholders}) AND meta_key = 'images'",
                $theseResorts);
            $query = $wpdb->get_results($sql, ARRAY_A);
        } else {
            $query = [];
        }

        foreach ($query as $thisk => $thisrow) {
            $current['rmk'] = $thisrow['meta_key'];
            $current['rmv'] = json_decode($thisrow['meta_value'], true);
            $current['rid'] = $thisrow['ResortID'];

            $resortMetas[$current['rid']][$current['rmk']] = $current['rmv'];

            // image
            if (!empty($resortMetas[$current['rid']]['images'])) {
                $resortImages = $resortMetas[$current['rid']]['images'];
                $oneImage = Arr::first($resortImages);

                // store items for $prop in ['to_prop'] // extract in loop
                $resortMetas[$current['rid']]['ImagePath1'] = $oneImage['src'];
                unset($resortImages);
                unset($oneImage);
            }
        }

        $props = array_values($props);
        $propKeys = array_keys($props);
        $pi = 0;
        $ppi = 0;
        while ($pi < count($props)) {
            $prop = $props[$pi];

            //skip anything that has an error
            $allErrors = [
                'checkIn',
            ];
            //if this type is 3 then i't both exchange and rental. Run it as an exchange
            if ($prop->PID == '47071506') {
                $ppi++;
            }

            //first we need to set the week type
            //if this type is 3 then it's both exchange and rental. Run it as an exchange
            if ($prop->WeekType == '1') {
                $prop->WeekType = 'ExchangeWeek';
            } elseif ($prop->WeekType == '2') {
                $prop->WeekType = 'RentalWeek';
            } else {
                //a previous loop set this as a rental
                if (isset($prop->forRental) && $prop->forRental) {
                    $prop->WeekType = 'RentalWeek';
                } else {
                    //we know for sure this is an exchange week
                    $prop->WeekType = 'ExchangeWeek';
                    $rentalAvailable = false;
                    if (empty($prop->active_rental_push_date)) {
                        if (strtotime($prop->checkIn) < strtotime('+ 6 months')) {
                            $rentalAvailable = true;
                        }
                    } elseif (strtotime('NOW') > strtotime($prop->active_rental_push_date)) {
                        $rentalAvailable = true;
                    }
                    if ($rentalAvailable) {
                        $nextCnt = count($props);
                        $props[$nextCnt] = clone $prop;
                        $props[$nextCnt]->forRental = $nextCnt;
                        $props[$nextCnt]->Price = $prop->Price;
                        $randexPrice[$nextCnt] = $prop->Price;
                    }
                }
            }
            $alwaysWeekExchange = $prop->WeekType;
            $prop->WeekTypeDisplay = $prop->WeekType === 'ExchangeWeek' ? 'Exchange Week' : 'Rental Week';
            $prop->pricing = gpx_get_pricing($prop->weekId, $prop->WeekType, $cid);
            $prop->Price = $prop->pricing['special'];
            $prop->WeekPrice = $prop->pricing['price'];
            $prop->specialPrice = $prop->pricing['special'];
            $prop->discount = $prop->pricing['discount'];
            $prop->specialicon = $prop->pricing['promos']->first(fn($promo) => $promo->icon)?->icon ?? null;
            $prop->specialdesc = $prop->pricing['promos']->first(fn($promo) => $promo->desc)?->desc ?? null;
            $prop->slash = !$prop->pricing['promos']->every(fn($promo) => $promo->Properties?->slash && $promo->Properties?->slash == 'No Slash');
            $prop->preventhighlight = $prop->pricing['promos']->every(fn($promo) => !!($promo->Properties?->preventhighlight ?? false));
            $prop->images = $resortMetas[$prop->ResortID]['images'] ?? [];
            $prop->ImagePath1 = $resortMetas[$prop->ResortID]['ImagePath1'] ?? '';

            $nextRows = [];

            $pi++;

            $plural = '';
            $chechbr = strtolower(substr($prop->bedrooms, 0, 1));
            if (is_numeric($chechbr)) {
                $bedtype = $chechbr;
                if ($chechbr != 1) {
                    $plural = 's';
                }
                $bedname = $chechbr . " Bedroom" . $plural;
            } elseif ($chechbr == 's') {
                $bedtype = 'Studio';
                $bedname = 'Studio';
            } else {
                $bedtype = $prop->bedrooms;
                $bedname = $prop->bedrooms;
            }

            $allBedrooms[$bedtype] = $bedname;
            $prop->AllInclusive = '00';
            $resortFacilities = isset($prop->ResortFacilities) ? json_decode($prop->ResortFacilities) : null;
            if ((is_array($resortFacilities) && in_array('All Inclusive',
                        $resortFacilities)) || strpos($prop->HTMLAlertNotes,
                    'IMPORTANT: All-Inclusive Information') || strpos($prop->AlertNote,
                    'IMPORTANT: This is an All Inclusive (AI) property.')) {
                $prop->AllInclusive = '6';
            }

            $discount = '';
            $rdgp = $prop->ResortID . strtotime($prop->checkIn);

            $date = $prop->checkIn;

            //remove any exclusive weeks
            if (isset($rmExclusiveWeek[$prop->weekId]) && !empty($rmExclusiveWeek[$prop->weekId])) {
                unset($props[$propKey]);
                $pi++;
                continue;
            }

            //sort the results by date...
            $weekTypeKey = 'b';
            if ($prop->WeekType == 'ExchangeWeek') {
                $weekTypeKey = 'a';
            }
            $prop->propkeyset = strtotime($prop->checkIn) . '--' . $weekTypeKey . '--' . $prop->PID;
            $datasort = str_replace("--", "", $prop->propkeyset);

            $propsetspecialprice[$datasort] = $prop->specialPrice;
            $prefPropSetDets[$datasort]['specialPrice'] = $prop->specialPrice ?? 0.00;
            $prefPropSetDets[$datasort]['specialicon'] = $prop->specialicon ?? null;
            $prefPropSetDets[$datasort]['specialdesc'] = $prop->specialdesc ?? null;


            $checkFN[] = $prop->gpxRegionID;
            $regions[$prop->gpxRegionID] = $prop->gpxRegionID;
            $resorts[$prop->ResortID]['props'][$datasort] = $prop;
            $propPrice[$datasort] = $prop->WeekPrice;
            $propType[$datasort] = $prop->WeekType;
            $calendarRows[] = $prop;
        }
        //add all the extra resorts
        if (isset($resortsSql)) {
            if ($resorts) {
                $thisSetResorts = array_keys($resorts);
                $placeholders = gpx_db_placeholders($thisSetResorts, '%d');
                $moreWhere = $wpdb->prepare(" AND (ResortID NOT IN ({$placeholders}))", $thisSetResorts);
                $resortsSql .= $moreWhere;
            }
            $allResorts = $wpdb->get_results($resortsSql);
            foreach ($allResorts as $ar) {
                $resorts[$ar->ResortID]['resort'] = $ar;
            }
        }
        $newStyle = true;
        $filterNames = [];
        if (isset($checkFN) && !empty($checkFN)) {
            foreach ($checkFN as $fn) {
                $sql = $wpdb->prepare("SELECT id, name FROM wp_gpxRegion WHERE id=%d", $fn);
                $fnRows = $wpdb->get_results($sql);

                foreach ($fnRows as $fnRow) {
                    if ($fnRow->name != 'All') {
                        $filterNames[$fnRow->id] = $fnRow->name;
                    }
                }
            }
        }
        asort($filterNames);
    }
    if (!isset($resorts)) $resorts = [];
    //get a list of restricted gpxRegions
    $restrictIDs = gpx_db()->fetchAllKeyValue("SELECT r.id, r.id FROM wp_gpxRegion r INNER JOIN wp_gpxRegion ca ON (ca.name = 'Southern Coast (California)') WHERE r.lft BETWEEN ca.lft AND ca.rght");
    if ($limitCount > 0) {
        foreach ($resorts as $resort_id => $resort) {
            // because we pulled double the amount of records we needed earlier we need to limit it to the requested amount.
            $resorts[$resort_id]['props'] = array_slice($resort['props'], 0, $limitCount, true);
        }
    }

    // Pull resort details
    $resort_ids = array_unique(array_keys($resorts));
    if (!empty($resort_ids)) {
        $placeholders = gpx_db_placeholders($resort_ids, '%s');
        $sql = $wpdb->prepare("SELECT * FROM `wp_resorts` WHERE `ResortID` IN ({$placeholders}) ", $resort_ids);
        $rs = $wpdb->get_results($sql, ARRAY_A);
        foreach ($rs as $resort) {
            $resortMeta = ResortRepository::instance()->get_resort_meta($resort['ResortID'], [
                'ExchangeFeeAmount',
                'RentalFeeAmount',
                'images',
                'ImagePath1',
                'ResortFeeSettings',
            ], true);
            $resort['images'] = $resortMeta?->images ?? $resort['images'] ?? [];
            $resort['ImagePath1'] = $resortMeta?->ImagePath1 ?? $resort['ImagePath1'] ?? null;
            $resort['ExchangeFeeAmount'] = $resortMeta?->ExchangeFeeAmount ?? $defaultExchangeFee;
            $resort['RentalFeeAmount'] = $resortMeta?->RentalFeeAmount ?? null;
            $resort['ResortFeeSettings'] = $resortMeta->ResortFeeSettings ?? null;
            if ($resort['ResortFeeSettings']['enabled'] ?? false) {
                $resort['ResortFeeSettings']['total'] = $resort['ResortFeeSettings']['fee'];
                if ($resort['ResortFeeSettings']['frequency'] === 'daily') {
                    $resort['ResortFeeSettings']['total'] *= 7;
                }
                $regions = RegionRepository::instance()->breadcrumbs($resort['gpxRegionID']);
                if (empty(array_filter($regions, fn($region) => $region['show_resort_fees']))) {
                    $resort['ResortFeeSettings']['enabled'] = false;
                }
            }
            $resorts[$resort['ResortID']] = array_merge($resort, $resorts[$resort['ResortID']]);
            if (isset($resorts[$resort['ResortID']]['props'])) {
                ksort($resorts[$resort['ResortID']]['props']);
            }
        }
    }

    if (isset($outputProps) && $outputProps) {
        if ($resorts) {
            if (!empty($calendar)) {
                return $calendarRows;
            } else {
                $resortid = gpx_request('resort');
                $output = gpx_theme_template('resort-availability', compact('resortid', 'resorts', 'props', 'cid', 'restrictIDs', 'cntResults'), false);
            }
        } else {
            $output = '<div style="text-align:center; margin: 30px 20px 40px 20px; "><h3 style="color:#cc0000;">Your search didn\'t return any results</h3><p style="font-size:15px;">Please consider searching a different resort or try again later.</p></div>';
        }

        return $output;
    } else {
        include(get_template_directory() . '/templates/sc-result.php');
    }
}

add_shortcode('gpx_result_page', 'gpx_result_page_sc');
add_shortcode('gpx_insider_week_page', 'gpx_insider_week_page_sc');
