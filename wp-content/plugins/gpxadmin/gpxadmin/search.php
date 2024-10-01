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
use GPX\Repository\SpecialsRepository;
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


    /** =====================================
     *  SENTRY
     *  =====================================
     */
    if (SENTRY_ENABLED) {
        // Setup context for the full transaction
        $transactionContext = \Sentry\Tracing\TransactionContext::make()->setName('gpx_result_page_sc')->setOp('http.server');
        // Start the transaction
        $GLOBALS['sentryTransaction'] = \Sentry\startTransaction($transactionContext);
        \Sentry\SentrySdk::getCurrentHub()->setSpan($GLOBALS['sentryTransaction']);
        $spanContext = \Sentry\Tracing\SpanContext::make()->setOp('variable initialization');
        $span1 = $GLOBALS['sentryTransaction']->startChild($spanContext);
        \Sentry\SentrySdk::getCurrentHub()->setSpan($span1);
    }


    $ids = [];
    //  update the join id

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

    /** =====================================
     *  SENTRY
     *  =====================================
     */
    if (SENTRY_ENABLED) {
        $span1->finish();

        $spanContext = \Sentry\Tracing\SpanContext::make()->setOp('select properties');
        $span2 = $GLOBALS['sentryTransaction']->startChild($spanContext);
        \Sentry\SentrySdk::getCurrentHub()->setSpan($span2);
    }



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

    /** =====================================
     *  SENTRY
     *  =====================================
     */
    if (SENTRY_ENABLED) {
        $span2->finish();
    }

    if (!empty($props) || isset($resortsSql)) {
        //let's first get query specials by the variables that are already set
        /** =====================================
         *  SENTRY
         *  =====================================
         */
        if (SENTRY_ENABLED) {
            $spanContext = \Sentry\Tracing\SpanContext::make()->setOp('sql- wp_specials, no props');
            $span3 = $GLOBALS['sentryTransaction']->startChild($spanContext);
            \Sentry\SentrySdk::getCurrentHub()->setSpan($span3);
        }

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

        /** =====================================
         *  SENTRY
         *  =====================================
         */
        if (SENTRY_ENABLED) {
            $span3->finish();

            $spanContext = \Sentry\Tracing\SpanContext::make()->setOp('foreach props');
            $span4 = $GLOBALS['sentryTransaction']->startChild($spanContext);
            \Sentry\SentrySdk::getCurrentHub()->setSpan($span4);
        }

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



        /** =====================================
         *  SENTRY
         *  =====================================
         */
        if (SENTRY_ENABLED) {
            $span4->finish();

            $spanContext = \Sentry\Tracing\SpanContext::make()->setOp('week properties- resort meta');
            $span5 = $GLOBALS['sentryTransaction']->startChild($spanContext);
            \Sentry\SentrySdk::getCurrentHub()->setSpan($span5);
        }

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


        // create a list to send to the list_get_pricing
        // so we don't have to make a DB call for every property
        $props_list = [];

        foreach ($props as $prop) {
            $input_prop = new stdClass();
            $input_prop->ResortID = $prop->ResortID;
            $input_prop->resortId = $prop->resortId;
            $input_prop->weekId = $prop->weekId;
            $input_prop->WeekType = $prop->WeekType;
            $props_list[] = $input_prop;
        }

        $weekPrices = list_get_pricing($props_list,$cid);

        /** =====================================
         *  SENTRY
         *  =====================================
         */
        if (SENTRY_ENABLED) {
            $span5->setData(['props'=>$props,'$props_list'=>$props_list])->finish();
        }


        while ($pi < count($props)) {

            $prop = $props[$pi];
            /** =====================================
             *  SENTRY
             *  =====================================
             */
            if(SENTRY_ENABLED) {
                $spanContext = \Sentry\Tracing\SpanContext::make()->setOp('week properties - while loop start')->setData(['prop' => $prop]);
                $span10 = $GLOBALS['sentryTransaction']->startChild($spanContext);
                \Sentry\SentrySdk::getCurrentHub()->setSpan($span10);
            }
            //skip anything that has an error
            $allErrors = [
                'checkIn',
            ];
            //if this type is 3 then it's both exchange and rental. Run it as an exchange
            if ($prop->PID == '47071506') {
                $ppi++;
            }

            /** =====================================
             *  SENTRY
             *  =====================================
             */
            if (SENTRY_ENABLED) {
                $span10->finish();

                $spanContext = \Sentry\Tracing\SpanContext::make()->setOp('week properties - week type');
                $span11 = $GLOBALS['sentryTransaction']->startChild($spanContext);
                \Sentry\SentrySdk::getCurrentHub()->setSpan($span11);
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

            /** =====================================
             *  SENTRY
             *  =====================================
             */
            if (SENTRY_ENABLED) {
                $span11->setData(['props'=>$props])->finish();

                $spanContext = \Sentry\Tracing\SpanContext::make()->setOp('week properties - set prices');
                $span12 = $GLOBALS['sentryTransaction']->startChild($spanContext);
                \Sentry\SentrySdk::getCurrentHub()->setSpan($span12);
            }

            $alwaysWeekExchange = $prop->WeekType;
            $prop->WeekTypeDisplay = $prop->WeekType === 'ExchangeWeek' ? 'Exchange Week' : 'Rental Week';
   //         $prop->pricing = gpx_get_pricing($prop->weekId, $prop->WeekType, $cid);
            $prop->pricing = $weekPrices[$prop->id];

            // set price
            if ($prop->WeekType === "ExchangeWeek") {
                $prop->Price = $prop->pricing['exchange'];
                $prop->pricing['price'] = $prop->pricing['exchange'];
            } else {
                $prop->Price = $prop->pricing['rental'];
                $prop->pricing['price'] = $prop->pricing['rental'];
            }
            $prop->pricing['special'] = $prop->Price;

            $prop->WeekPrice = $prop->pricing['price'];
            $prop->specialPrice = $prop->pricing['special'];
            $prop->discount = $prop->pricing['discount'];
            $prop->images = $resortMetas[$prop->ResortID]['images'] ?? [];
            $prop->ImagePath1 = $resortMetas[$prop->ResortID]['ImagePath1'] ?? '';

            // no longer used...
            $prop->specialicon = $prop->pricing['promos']->first(fn($promo) => $promo->icon)?->icon ?? null;
            $prop->specialdesc = $prop->pricing['promos']->first(fn($promo) => $promo->desc)?->desc ?? null;
            $prop->slash = !$prop->pricing['promos']->every(fn($promo) => $promo->Properties?->slash && $promo->Properties?->slash == 'No Slash');
            $prop->preventhighlight = $prop->pricing['promos']->every(fn($promo) => !!($promo->Properties?->preventhighlight ?? false));

            /** =====================================
             *  SENTRY
             *  =====================================
             */
            if (SENTRY_ENABLED) {
                $span12->setData(['prop'=>$prop])->finish();

                $spanContext = \Sentry\Tracing\SpanContext::make()->setOp('week properties - more');
                $span13 = $GLOBALS['sentryTransaction']->startChild($spanContext);
                \Sentry\SentrySdk::getCurrentHub()->setSpan($span13);

            }


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
            /** =====================================
             *  SENTRY
             *  =====================================
             */
            if (SENTRY_ENABLED) {
                $span13->finish();
            }
        }


        /** =====================================
         *  SENTRY
         *  =====================================
         */
        if (SENTRY_ENABLED) {

            $spanContext = \Sentry\Tracing\SpanContext::make()->setOp('add extra resorts');
            $span6 = $GLOBALS['sentryTransaction']->startChild($spanContext);
            \Sentry\SentrySdk::getCurrentHub()->setSpan($span6);
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
        if (SENTRY_ENABLED) {$span6->finish();}
    }

    /** =====================================
     *  SENTRY
     *  =====================================
     */
    if (SENTRY_ENABLED) {

        $spanContext = \Sentry\Tracing\SpanContext::make()->setOp('southern coast check');
        $span7 = $GLOBALS['sentryTransaction']->startChild($spanContext);
        \Sentry\SentrySdk::getCurrentHub()->setSpan($span7);
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

    /** =====================================
     *  SENTRY
     *  =====================================
     */
    if (SENTRY_ENABLED) {
        $span7->finish();
    }

    if (!empty($resort_ids)) {

        /** =====================================
         *  SENTRY
         *  =====================================
         */
        if (SENTRY_ENABLED) {
            $spanContext = \Sentry\Tracing\SpanContext::make()->setOp('empty resort ids');
            $span8 = $GLOBALS['sentryTransaction']->startChild($spanContext);
            \Sentry\SentrySdk::getCurrentHub()->setSpan($span8);
        }



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


            // exchange fee based on resort fee schedule
            $resort['ExchangeFeeAmount'] = get_option('gpx_exchange_fee') ?? 199.00;
            // if the resort has a custom exchange fee, use that instead
            if (is_array($resortMeta?->ExchangeFeeAmount)) {
                // split the key into 2 timestamps\
                foreach ($resortMeta->ExchangeFeeAmount as $key => $value) {
                    $key = explode('_', $key);
// @todo Set the exchange fee based on the date range here...
                }
            }
            $resort['CustomFees']['ExchangeFeeAmount'] = $resortMeta?->ExchangeFeeAmount ?? null;


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

        // run this function to check all the custom prices for the week and set the correct prices.

        $resorts = setCustomPrices($resorts);

        /** =====================================
         *  SENTRY
         *  =====================================
         */
        if (SENTRY_ENABLED) {
            $span8->finish();
        }
    }



    /** =====================================
     *  SENTRY
     *  =====================================
     */
    if (SENTRY_ENABLED) {
        $spanContext = \Sentry\Tracing\SpanContext::make()->setOp('display & templates');
        $span9 = $GLOBALS['sentryTransaction']->startChild($spanContext);
        \Sentry\SentrySdk::getCurrentHub()->setSpan($span9);
    }


    if (isset($outputProps) && $outputProps) {
        if ($resorts) {
            if (!empty($calendar)) {

                if (SENTRY_ENABLED) {$span9->finish();}

                return $calendarRows;
            } else {
                $resortid = gpx_request('resort');
                $output = gpx_theme_template('resort-availability', compact('resortid', 'resorts', 'props', 'cid', 'restrictIDs', 'cntResults'), false);
            }
        } else {
            $output = '<div style="text-align:center; margin: 30px 20px 40px 20px; "><h3 style="color:#cc0000;">Your search didn\'t return any results</h3><p style="font-size:15px;">Please consider searching a different resort or try again later.</p></div>';
        }

        if (SENTRY_ENABLED) {$span9->finish();}

        return $output;
    } else {
        include(get_template_directory() . '/templates/sc-result.php');
    }

    if (SENTRY_ENABLED) {$span9->finish();}
}

add_shortcode('gpx_result_page', 'gpx_result_page_sc');
add_shortcode('gpx_insider_week_page', 'gpx_insider_week_page_sc');


/**
 *  get the pricing for an array of properties
 *
 *  band-aid replacement for use of gpx_get_pricing  cart.php line 517
 *  due to performance problems.
 *
 *  Utilize on line ~611
 *
 *  props: array of properties
 *      props[n][weekId]  = $prop->weekId
 *      props[n][WeekType]   = $prop->WeekType
 *      $cid
 */
function list_get_pricing(array $props, $cid) {
    global $wpdb;

    /** =====================================
     *  SENTRY
     *  =====================================
     */
    if (SENTRY_ENABLED) {
        $spanContext = \Sentry\Tracing\SpanContext::make()->setOp('list_get_pricing');
        $span = $GLOBALS['sentryTransaction']->startChild($spanContext);
        \Sentry\SentrySdk::getCurrentHub()->setSpan($span);
    }

    /**
     * Default Fees
     */
    $defaultFees = [];
    $defaultFees['gpx_gf_amount'] =  (get_option('gpx_global_guest_fees') && get_option('gpx_gf_amount')) ? get_option('gpx_gf_amount') : 69.00;
    $defaultFees['gpx_min_rental_fee'] = (get_option('gpx_min_rental_fee')) ? get_option('gpx_min_rental_fee') : 99.00;
    $defaultFees['gpx_late_deposit_fee'] = (get_option('gpx_late_deposit_fee')) ? get_option('gpx_late_deposit_fee') : 149.00;
    $defaultFees['gpx_late_deposit_fee_within'] = (get_option('gpx_late_deposit_fee_within') ) ? get_option('gpx_late_deposit_fee_within') : 99.00;
    $defaultFees['gpx_exchange_fee'] = (get_option('gpx_exchange_fee')) ? get_option('gpx_exchange_fee') : 199.00;
    $defaultFees['gpx_extension_fee'] =  (get_option('gpx_extension_fee')) ? get_option('gpx_extension_fee') : 129.00;
    $defaultFees['gpx_third_party_fee'] = (get_option('gpx_third_party_fee')) ? get_option('gpx_third_party_fee') : 50.00;
    $defaultFees['gpx_legacy_owner_exchange_fee'] = (get_option('gpx_legacy_owner_exchange_fee')) ? get_option('gpx_legacy_owner_exchange_fee') : 50.00;
    $defaultFees['flex_fee'] = (get_option('gpx_fb_fee')) ? get_option('gpx_fb_fee') : 39.00;


    // write an SQL to pull the week prices from the database for a list of weeks
    $weekIds = array_column($props, 'weekId');
    $placeholders = gpx_db_placeholders($weekIds, '%d');
    $sql = $wpdb->prepare("SELECT record_id, price, resort, type
                FROM `wp_room` WHERE `record_id` IN ({$placeholders}) ", $weekIds);

    $weeks = $wpdb->get_results($sql, ARRAY_A);

    //write an SQL to pull the resort fees from the database for a list of resorts


    $resortIds = array_unique(array_column($props, 'ResortID'));


    $placeholders = gpx_db_placeholders($resortIds, '%s');
    $sql = $wpdb->prepare("SELECT ResortID,meta_key,meta_value FROM `wp_resorts_meta` WHERE
                `meta_key` IN ( 'ResortFeeSettings','ExchangeFeeAmount','RentalFeeAmount','CPOFeeAmount','GuestFeeAmount','UpgradeFeeAmount','SameResortExchangeFee')
                 AND  `ResortID` IN ({$placeholders}) ", $resortIds);
    $resortFees = $wpdb->get_results($sql, ARRAY_A);

    $remappedArray = array_reduce($resortFees, function($carry, $item) {
        $resortID = $item['ResortID'];
        if (!isset($carry[$resortID])) {
            $carry[$resortID] = [];
        }
        $carry[$resortID][$item['meta_key']] = json_decode($item['meta_value'], true);
        return $carry;
    }, []);

/**
 *    closure function to calculate the resort fee
 */
    //write a closure function to calculate the resort fee
    $get_the_resort_function = function($resortID) use ($remappedArray) {
        if (isset($remappedArray[$resortID]['ResortFeeSettings'])) {
            $resortFeeSettings = $remappedArray[$resortID]['ResortFeeSettings'];
            if ($resortFeeSettings['enabled']) {
                $resortFee = $resortFeeSettings['fee'];
                if ($resortFeeSettings['frequency'] === 'daily') {
                    $resortFee *= 7;
                }
                return number_format($resortFee,2);
            } else {
                return number_format(0,2);
            }
        } else {
            return number_format(0,2);
        }
    };

    /**
     *  closure function to calculate the exchange fee for the resort
     */
    $get_the_exchange_fee_function = function($resortID) use ($remappedArray, $defaultFees, $cid) {
        $user_data = get_user_meta($cid);
        $legacy = isset($user_data['GP_Preferred']) && (end($user_data['GP_Preferred']) == 'Yes');
        if ($legacy) {
            $the_fee = $defaultFees['gpx_legacy_owner_exchange_fee'];
        } else {
            $the_fee = $defaultFees['gpx_exchange_fee'];
            if (isset($remappedArray[$resortID]['ExchangeFeeAmount'])) {
                $currentTimestamp = time();
                // write a check to see current date is within the range of the fee the range is
                // represented by the start and end date in timestamp format for example 1718780400_1730271600
                foreach ($remappedArray[$resortID]['ExchangeFeeAmount'] as $dateRange => $fee) {
                    list($startTimestamp, $endTimestamp) = explode('_', $dateRange);
                    if ($currentTimestamp >= (int)$startTimestamp && $currentTimestamp <= (int)$endTimestamp) {
                        $the_fee = end($fee);
                    }
                }
            }
        }
        return number_format($the_fee,2);
    };

    $get_the_guest_fee_function = function($resortID) use ($remappedArray, $defaultFees) {
        $the_fee = $defaultFees['gpx_gf_amount'];
        if (isset($remappedArray[$resortID]['GuestFeeAmount'])) {
            $currentTimestamp = time();
            // write a check to see current date is within the range of the fee the range is
            // represented by the start and end date in timestamp format for example 1718780400_1730271600
            foreach ($remappedArray[$resortID]['GuestFeeAmount'] as $dateRange => $fee) {
                list($startTimestamp, $endTimestamp) = explode('_', $dateRange);
                if ($currentTimestamp >= (int)$startTimestamp && $currentTimestamp <= (int)$endTimestamp) {
                    $the_fee =  end($fee);
                }
            }
        }
        return number_format($the_fee,2);
    };

    $get_the_upgrade_fee_function = function($resortID) use ($remappedArray) {
        $the_fee = 0;
        if (isset($remappedArray[$resortID]['UpgradeFeeAmount'])) {
            $currentTimestamp = time();
            // write a check to see current date is within the range of the fee the range is
            // represented by the start and end date in timestamp format for example 1718780400_1730271600
            foreach ($remappedArray[$resortID]['UpgradeFeeAmount'] as $dateRange => $fee) {
                list($startTimestamp, $endTimestamp) = explode('_', $dateRange);
                if ($currentTimestamp >= (int)$startTimestamp && $currentTimestamp <= (int)$endTimestamp) {
                    $the_fee =  end($fee);
                }
            }
        }
        return number_format($the_fee,2);
    };
    /**
     * @param $resortID
     * @return string
     */
    $get_the_flex_fee_function = function($resortID) use ($remappedArray, $defaultFees) {
        $the_fee = $defaultFees['flex_fee'];
        if (isset($remappedArray[$resortID]['CPOFeeAmount'])) {
            $currentTimestamp = time();
            // write a check to see current date is within the range of the fee the range is
            // represented by the start and end date in timestamp format for example 1718780400_1730271600
            foreach ($remappedArray[$resortID]['CPOFeeAmount'] as $dateRange => $fee) {
                list($startTimestamp, $endTimestamp) = explode('_', $dateRange);
                if ($currentTimestamp >= (int)$startTimestamp && $currentTimestamp <= (int)$endTimestamp) {
                    $the_fee =  end($fee);
                }
            }
        }
        return number_format($the_fee,2);
    };

    $get_the_same_resort_fee_function = function($resortID) use ($remappedArray) {
        $the_fee = 0;
        if (isset($remappedArray[$resortID]['SameResortExchangeFee'])) {
            $currentTimestamp = time();
            // write a check to see current date is within the range of the fee the range is
            // represented by the start and end date in timestamp format for example 1718780400_1730271600
            foreach ($remappedArray[$resortID]['SameResortExchangeFee'] as $dateRange => $fee) {
                list($startTimestamp, $endTimestamp) = explode('_', $dateRange);
                if ($currentTimestamp >= (int)$startTimestamp && $currentTimestamp <= (int)$endTimestamp) {
                    $the_fee =  end($fee);
                }
            }
        }
        return number_format($the_fee,2);
    };

    $weekPrices = [];
    foreach ($weeks as $week) {

        $ResortID =  $props[array_search($week['record_id'], array_column($props, 'weekId'))]->ResortID;
        $weekPrices[$week['record_id']]['record_id'] = $week['record_id'];
        $weekPrices[$week['record_id']]['resort'] = $week['resort'];
        $weekPrices[$week['record_id']]['ResortID'] = $ResortID;
        $weekPrices[$week['record_id']]['type'] = $week['type'];

        $weekPrices[$week['record_id']]['price'] =  0.00;
        $weekPrices[$week['record_id']]['exchange'] = $get_the_exchange_fee_function($ResortID);
        $weekPrices[$week['record_id']]['exchange_same_resort'] = $get_the_same_resort_fee_function($ResortID);
        $weekPrices[$week['record_id']]['extension'] = number_format($defaultFees['gpx_extension_fee'],2);
        $weekPrices[$week['record_id']]['rental'] = number_format($week['price'],2);
        $weekPrices[$week['record_id']]['flex'] = $get_the_flex_fee_function($ResortID);
        $weekPrices[$week['record_id']]['guest'] = $get_the_guest_fee_function($ResortID);
        $weekPrices[$week['record_id']]['upgrade'] = $get_the_upgrade_fee_function($ResortID);
        $weekPrices[$week['record_id']]['sevenDays'] = number_format($defaultFees['gpx_late_deposit_fee_within'],2);
        $weekPrices[$week['record_id']]['fifteenDays'] = number_format($defaultFees['gpx_late_deposit_fee'],2);
        $weekPrices[$week['record_id']]['tp_deposit'] = number_format($defaultFees['gpx_third_party_fee'],2);
        $weekPrices[$week['record_id']]['discount'] = 0;
        $weekPrices[$week['record_id']]['promo'] = null;
        $weekPrices[$week['record_id']]['special'] = 0;  // what is this?
        $weekPrices[$week['record_id']]['promos'] = collect();
        $weekPrices[$week['record_id']]['resort_fee'] = $get_the_resort_function ($ResortID);

        /*
         * array:15 [▼
          "price" => 402.0
          "exchange" => 199.0
          "exchange_same_resort" => 0.0
          "extension" => 129.0
          "rental" => 402.0
          "flex" => 39.0
          "guest" => 69.0
          "upgrade" => 0.0
          "sevenDays" => 149
          "fifteenDays" => 99
          "tp_deposit" => 50
          "discount" => 0.0
          "promo" => null
          "special" => 402.0
          "promos" => Illuminate\Support\Collection {#11557 ▶}
         *
         */
    }

    /** =====================================
     *  SENTRY
     *  =====================================
     */
    if (SENTRY_ENABLED) {  $span->setData(['$weekPrices'=>$weekPrices])->finish();}

    return $weekPrices;
}

function setCustomPrices($resorts) {

    foreach ($resorts as $resort) { // loop through each resort

        // get the custom fees for the resort
        $customFees = $resort['CustomFees'];
        // Exchange Fees
        $custom_exchange_fees = [];
        if (is_array($customFees['ExchangeFeeAmount'])) {
            foreach ($customFees['ExchangeFeeAmount'] as $dates => $fee) {
                list($start, $end) = explode('_', $dates);
                $custom_exchange_fees[] = [
                    'start' => $start,
                    'end' => $end,
                    'fee' => $fee
                ];
            }
        } else {
            continue;
        }

        foreach ($resort['props'] as $key => $week) { // loop through each property in each resort
         // check the checkIn date of the week and see if it matches any of the customFees
            $weekCheckIn = strtotime($week->checkIn);

            foreach ($custom_exchange_fees as $fee) {

                if ($weekCheckIn >= $fee['start'] && $weekCheckIn < $fee['end']) {

                    if ($week->WeekType === 'ExchangeWeek') {
                        $week->Price = $fee['fee'];
                        $week->WeekPrice = $fee['fee'];
                        $week->specialPrice = $fee['fee'];
                        $resorts[$resort['ResortID']]['props'][$key]  =   $week;
                    }
                }
            }
        }
    }

    return $resorts;
}
