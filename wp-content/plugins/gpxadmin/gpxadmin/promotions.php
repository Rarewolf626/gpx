<?php

use GPX\Model\Region;
use GPX\Model\Special;
use GPX\Model\UserMeta;
use Illuminate\Support\Arr;
use Doctrine\DBAL\Connection;
use Illuminate\Support\Carbon;
use GPX\Repository\ResortRepository;
use GPX\Repository\RegionRepository;
use Doctrine\DBAL\ArrayParameterType;
use GPX\Repository\SpecialsRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * GPX Promo Page Shortcode
 *
 * Displays promo page results
 * Uses url to create a varable. The variable is used to query the wp_specials table to retrieve the promo.
 * Then we retreive all of the inventory that could apply based on a basic inventory query followed by filtering the
 * results based on conditions established when the promo is created.
 * return html
 */
function gpx_promo_page_sc() {
    global $wpdb;
    $cid = gpx_get_switch_user_cookie();
    $usermeta = $cid ? UserMeta::load($cid) : null;
    $legacy = gpx_is_legacy_preferred_member($cid);
    $code = get_query_var('promo');
    $promo = Special::active()->current()->slug($code)->first();
    if (!$promo || !$promo->isCustomerAllowedToUse($cid) || !$promo->isLandingPage()) {
        $cntResults = 0;
        $filterNames = [];
        // promo was not found, return empty results
        include(get_template_directory() . '/templates/sc-result.php');

        return;
    }
    $specials = SpecialsRepository::instance()->get_specials_for_promo($code)
                                  ->filter(fn(Special $special) => $special->isCustomerAllowedToUse($cid));
    $lpSPID = $promo->id;
    $lpCookie = null;
    $weeks = [];
    $exchangeFees = [];
    $resorts = [];
    $defaultExchangeFee = (int) get_option('gpx_exchange_fee', 0);
    $legacyExchangeFee = (int) get_option('gpx_legacy_owner_exchange_fee', $defaultExchangeFee);
    foreach ($specials as $special) {
        $specialMeta = $special->Properties;
        $wheres = [
            "a.`active` = 1",
            "a.`archived` = 0",
            "a.`active_rental_push_date` != '2030-01-01'",
            "b.`active` = 1",
            "DATE(a.`check_in_date`) > CURRENT_DATE()",
        ];
        if (!empty($specialMeta->exclusiveWeeks)) {
            $exclusiveWeeks = array_values(array_filter(array_map('intval', explode(',', $specialMeta->exclusiveWeeks))));
            $placeholders = gpx_db_placeholders($exclusiveWeeks, '%d');
            $wheres[] = $wpdb->prepare("a.week_id NOT IN ({$placeholders})", $exclusiveWeeks);
        }
        if (!$special->isForAnyTransactionType()) {
            if ($special->isForExchanges()) {
                $wheres[] = "a.type IN ('1','3')";
            } elseif ($special->isForRentals()) {
                $wheres[] = "a.type IN ('2','3')";
            } elseif ($special->isUpsell(true)) {
                if ($special->isCpoUpsell() || $special->isUpgradeUpsell()) {
                    $wheres[] = "a.type IN ('1','3')";
                }
            }
        }
        if (!empty($specialMeta->travelStartDate)) {
            $start = date('Y-m-d', strtotime($specialMeta->travelStartDate));
            $wheres[] = $wpdb->prepare("a.check_in_date >= %s", $start);
        }
        if (!empty($specialMeta->travelEndDate)) {
            $end = date('Y-m-d', strtotime($specialMeta->travelEndDate));
            $wheres[] = $wpdb->prepare("a.check_in_date <= %s", $end);
        }
        if (!empty($specialMeta->resortTravel)) {
            $resortTravel = [];
            foreach ($specialMeta->resortTravel as $travel) {
                $allowed = array_values(array_filter($travel->resorts, 'intval'));
                if ($travel->start && $travel->end && !empty($allowed)) {
                    $start = date('Y-m-d', strtotime($travel->start));
                    $end = date('Y-m-d', strtotime($travel->end));
                    $placeholders = gpx_db_placeholders($allowed, '%d');
                    $params = $allowed;
                    $params[] = $start;
                    $params[] = $end;
                    $resortTravel[] = $wpdb->prepare("(a.resort IN ({$placeholders}) AND a.check_in_date BETWEEN %s AND %s)", $params);
                }
            }
            if (!empty($resortTravel)) {
                $wheres[] = '(' . implode(' OR ', $resortTravel) . ')';
            }
        }
        if (!empty($specialMeta->blackout)) {
            foreach ($specialMeta->blackout as $blackout) {
                $start = date('Y-m-d', strtotime($blackout->start));
                $end = date('Y-m-d', strtotime($blackout->end));
                $wheres[] = $wpdb->prepare("a.check_in_date NOT BETWEEN %s AND %s", $start, $end);
            }
        }
        if (!empty($specialMeta->resortBlackout)) {
            $resortBlackout = [];
            foreach ($specialMeta->resortBlackout as $blackout) {
                $blocked = array_values(array_filter($blackout->resorts, 'intval'));
                if ($blackout->start && $blackout->end && !empty($blocked)) {
                    $start = date('Y-m-d', strtotime($blackout->start));
                    $end = date('Y-m-d', strtotime($blackout->end));
                    $placeholders = gpx_db_placeholders($blocked, '%d');
                    $params = $blocked;
                    $params[] = $start;
                    $params[] = $end;
                    $resortBlackout[] = $wpdb->prepare("(a.resort NOT IN ({$placeholders}) OR a.check_in_date BETWEEN %s AND %s)", $params);
                }
            }
            if (!empty($resortBlackout)) {
                $wheres[] = '(' . implode(' AND ', $resortBlackout) . ')';
            }
        }
        if (!empty($specialMeta->leadTimeMin)) {
            $min = Carbon::now()->addDays($specialMeta->leadTimeMin);
            $wheres[] = $wpdb->prepare("a.check_in_date >= %s", $min->format('Y-m-d'));
        }
        if (!empty($specialMeta->leadTimeMax)) {
            $max = Carbon::now()->addDays($specialMeta->leadTimeMax);
            $wheres[] = $wpdb->prepare("a.check_in_date <= %s", $max->format('Y-m-d'));
        }
        if ($specialMeta->usage != 'any') {
            $usage = Arr::wrap(explode(',', $specialMeta->usage));
            if (in_array('region', $usage)) {
                $allowed = $specialMeta->usage_region ?? [];
                if (is_string($allowed)) $allowed = json_decode(stripslashes($allowed), true);
                if (!empty($allowed)) {
                    $regions = Region::tree($allowed)->select('wp_gpxRegion.id')->pluck('id')->toArray();
                    $placeholders = gpx_db_placeholders($regions, '%d');
                    if (!empty($regions)) {
                        $wheres[] = $wpdb->prepare("b.GPXRegionID IN ({$placeholders})", $regions);
                    }
                };
            }
            if (in_array('resort', $usage)) {
                $allowed = $specialMeta->usage_resort ?? [];
                if (is_string($allowed)) $allowed = json_decode(stripslashes($allowed), true);
                $allowed = array_filter(array_map('intval', $allowed));
                if (!empty($allowed)) {
                    $placeholders = gpx_db_placeholders($allowed, '%d');
                    $wheres[] = $wpdb->prepare("a.resort IN ({$placeholders})", $allowed);
                };
            }
            if (in_array('dae', $usage)) {
//                $wheres[] = "`a`.`availability` IN ()";
//                if (isset($specialMeta->useage_dae) && !empty($specialMeta->useage_dae)) {
//                    if ((strtolower($week->StockDisplay) == 'all' || (strtolower($week->StockDisplay) == 'gpx' || strtolower($week->StockDisplay) == 'usa gpx')) && (strtolower($week->OwnerBusCatCode) == 'dae' || strtolower($week->OwnerBusCatCode) == 'usa dae')) {
//                        // we're all good -- these are the only properties that should be displayed
//                    } else {
//                        continue;
//                    }
//                }
            }
        }
        $exclusions = Arr::wrap(explode(',', $specialMeta->exclusions));
        if (in_array('region', $exclusions)) {
            $excluded = $specialMeta->exclude_region ?? [];
            if (is_string($excluded)) $excluded = json_decode(stripslashes($excluded), true);
            if (!empty($excluded)) {
                $regions = Region::tree($excluded)->select('wp_gpxRegion.id')->pluck('id')->toArray();
                $placeholders = gpx_db_placeholders($regions, '%d');
                if (!empty($regions)) {
                    $wheres[] = $wpdb->prepare("b.GPXRegionID NOT IN ({$placeholders})", $regions);
                }
            };
        }
        if (in_array('resort', $exclusions)) {
            $excluded = $specialMeta->exclude_resort ?? [];
            if (is_string($excluded)) $excluded = json_decode(stripslashes($excluded), true);
            $excluded = array_filter(array_map('intval', $excluded));
            if (!empty($excluded)) {
                $placeholders = gpx_db_placeholders($excluded, '%d');
                $wheres[] = $wpdb->prepare("a.resort NOT IN ({$placeholders})", $excluded);
            };
        }
        if (in_array('home-resort', $exclusions)) {
            $resorts = array_filter([
                $usermeta->OwnResort1,
                $usermeta->OwnResort2,
                $usermeta->OwnResort3,
            ]);
            if (!empty($resorts)) {
                $placeholders = gpx_db_placeholders($resorts, '%s');
                $wheres[] = $wpdb->prepare("b.ResortName NOT IN ({$placeholders})", $resorts);
            }
        }
        if (!empty($weeks)) {
            // exclude weeks already pulled
            $ids = array_column($weeks, 'id');
            $placeholders = gpx_db_placeholders($ids, '%d');
            $wheres[] = $wpdb->prepare("a.record_id NOT IN ({$placeholders})", $ids);
        }
        $where = implode(' AND ', array_map(fn($where) => "({$where})", $wheres));
        $sql = "SELECT
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
            WHERE {$where}
            ORDER BY b.featured DESC, a.`resort` ASC,  a.`check_in_date`";
        $results = $wpdb->get_results($sql);
        $resort_ids = array_unique(array_column($results, 'resortId'));

        if (!empty($resort_ids)) {
            $placeholders = gpx_db_placeholders($resort_ids, '%d');
            $sql = $wpdb->prepare("SELECT *
                FROM `wp_resorts`
                WHERE `id` IN ({$placeholders})
        ", $resort_ids);
            if (!empty($resorts)) {
                $rids = array_keys($resorts);
                $placeholders = gpx_db_placeholders($rids, '%s');
                $sql .= $wpdb->prepare(" AND `ResortID` NOT IN ({$resorts})", $rids);
            }
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
                $resort['ResortFeeSettings'] = $resortMeta?->ResortFeeSettings ?? null;
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
                $resorts[$resort['ResortID']] = $resort;
            }
        }

        foreach ($results as $week) {
            switch ($week->WeekType) {
                case '1':
                    $week->types = ['ExchangeWeek'];
                    break;
                case '2':
                    $week->types = ['RentalWeek'];
                    break;
                default:
                    $week->types = ['ExchangeWeek', 'RentalWeek'];
                    if (!$special->isForAnyTransactionType()) {
                        if ($special->isForExchanges()) {
                            $week->types = ['ExchangeWeek'];
                        } elseif ($special->isForRentals()) {
                            $week->types = ['RentalWeek'];
                        }
                    }
                    break;
            }

            $week->images = $resorts[$week->ResortID]['images'] ?? [];
            $week->ImagePath1 = $resorts[$week->ResortID]['ImagePath1'] ?? '';
            if ($special->isPromo()) {
                $week->specialicon = $specialMeta->icon ?? $week->specialicon ?? null;
                $week->specialdesc = $specialMeta->desc ?? $week->specialicon ?? null;
                $week->slash = $specialMeta?->slash !== 'No Slash';
                $week->preventhighlight = $specialMeta->preventhighlight ?? false;
            } else {
                $week->specialicon = $week->specialicon ?? null;
                $week->specialdesc = $week->specialdesc ?? null;
                $week->slash = $week->slash ?? false;
                $week->preventhighlight = $week->preventhighlight ?? false;
            }
            $resortFacilities = json_decode($week->ResortFacilities ?? '[]');
            if ((is_array($resortFacilities) && in_array('All Inclusive',
                        $resortFacilities)) || strpos($week->HTMLAlertNotes,
                    'IMPORTANT: All-Inclusive Information') || strpos($week->AlertNote,
                    'IMPORTANT: This is an All Inclusive (AI) property.')) {
                $week->AllInclusive = '6';
            }

            foreach ($week->types as $type) {
                if (!empty($specialMeta->minWeekPrice) && $type === 'RentalWeek') {
                    if ($week->Price < $specialMeta->minWeekPrice) {
                        continue;
                    }
                }
                $key = $week->resortId . '=' . date('Ymd', strtotime($week->checkIn)) . '=' . $type . '=' . str_replace('/', '', $week->Size);
                if (array_key_exists($key, $weeks)) {
                    $weeks[$key]->prop_count++;
                    continue;
                }
                if ($type === 'RentalWeek') {
                    $available_date = $week->active_rental_push_date ? Carbon::parse($week->active_rental_push_date) : Carbon::parse($week->checkIn)->subMonths(6);
                    if ($available_date->isFuture()) {
                        continue;
                    }
                }
                $weekTypeKey = $type === 'ExchangeWeek' ? 'a' : 'b';
                $prop = clone $week;
                $prop->propkeyset = date('Ymd', strtotime($week->checkIn)) . '--' . $weekTypeKey . '--' . $prop->PID;
                $prop->datasort = str_replace("--", "", $prop->propkeyset);
                $prop->week_date_size = $key;
                $prop->prop_count = 1;
                if ($legacy) {
                    $exchangeFee = $legacyExchangeFee;
                } else {
                    $exchangeFee = $resorts[$week->ResortID]['ExchangeFeeAmount'] ?? $defaultExchangeFee;
                }
                // hack to clean up array type. Price must be a float
                // if the price is an array take the last value in the array and convert it to a float
                $week->Price = is_array($week->Price) ? (float) end($week->Price) : (float) $week->Price;

                // hack to clean up array type. Price must be a float
                // if the $exchangeFee is an array take the last value in the array and convert it to a float
                $exchangeFee = is_array($exchangeFee) ? (float) end($exchangeFee) : (float) $exchangeFee;

                $price = $type === 'ExchangeWeek' ? $exchangeFee : $week->Price;
                if ($special->isPromo()) {
                    // only automatically apply promos, not coupons
                    $pricing = gpx_apply_promos(Arr::wrap($special), $price);
                } else {
                    $pricing = ['special' => $price, 'applied' => collect([])];
                }
                $prop->WeekType = $type;
                $prop->WeekTypeDisplay = $prop->WeekType === 'ExchangeWeek' ? 'Exchange Week' : 'Rental Week';
                $prop->WeekPrice = $price;
                $prop->Price = $pricing['special'];
                $prop->specialPrice = $pricing['special'];
                if (isset($specialMeta->beforeLogin) && $specialMeta->beforeLogin == "Yes") {
                    if (!is_user_logged_in()) {
                        $loginalert = true;
                        $prop->Price = $price;
                        $prop->specialPrice = $price;
                    }
                }
                $prop->promo = $special->Slug;
                $prop->discount = round($price - $pricing['special'], 2);
                $prop->AllInclusive = '';

                $weeks[$key] = $prop;
            }
        }
    }
    $cntResults = count($weeks);
    $weeks = collect(array_values($weeks))->sortBy('datasort')->groupBy('ResortID')->toArray();
    foreach ($resorts as $key => $resort) {
        $resorts[$key]['props'] = $weeks[$key];
    }

    $filterNames = !empty($checkFN) ? gpx_db()->fetchAllKeyValue("SELECT id, name FROM wp_gpxRegion WHERE id IN (?) AND name != 'All' ORDER BY name ASC",
        [$checkFN],
        [ArrayParameterType::INTEGER]) : [];

    //get a list of restricted gpxRegions
    $restrictIDs = gpx_db()->fetchAllKeyValue("SELECT r.id, r.id FROM wp_gpxRegion r INNER JOIN wp_gpxRegion ca ON (ca.name = 'Southern Coast (California)') WHERE r.lft BETWEEN ca.lft AND ca.rght");
    include(get_template_directory() . '/templates/sc-result.php');
}

add_shortcode('gpx_promo_page', 'gpx_promo_page_sc');

function gpx_get_specials_for_promo(string $code = null): Collection {
    if (empty($code)) {
        return new Collection();
    }
    //check to see if this is a master promo
    global $wpdb;
    $sql = $wpdb->prepare("SELECT id FROM wp_specials WHERE Slug=%s AND active=1", $code);
    $ismaster = $wpdb->get_var($sql);
    $frommasters = [];
    if ($ismaster) {
        $sql = $wpdb->prepare("SELECT * FROM wp_specials b WHERE b.master=%d and b.Active=1",
            $ismaster);
        $frommasters = $wpdb->get_results($sql);
    }
    $query = Special::promo()->active()->current();
    if (count($frommasters) > 0) {
        $query->where(fn($query) => $query->orWhere('master', '=', $ismaster)->orWhere('Slug', '=', $code));
    } else {
        $query->where('Slug', '=', $code);
    }

    return $query->get();

}

/**
 * Used to populate usage section on the promo form in gpxadmin
 * @return void
 */
function gpx_get_switchusage() {

    $usage = gpx_request('usage');
    $type = gpx_request('type');
    if (!empty($type)) {
        $type = $type . "_";
    }

    $data = ['html' => ''];

    switch ($usage) {
        case "region":
            $data['html'] = gpx_get_usage($type);
            $data['html'] .= '<div class="insert-above"></div>';
            break;

        case "resort":
        case "home-resort":
            $data['html'] = gpx_get_usage($type);
            $data['html'] .= '<div class="insert-above row">
                        <div class="col-sm-6 col-sm-offset-3 col-xs-12 text-right">
                          <a href="#" class="btn btn-primary resort-list">Load Resorts</a>
                        </div>
                        <div class="insert-resorts"></div>
                      ';

            break;

        case "trace":
            $data['html'] = '';
            break;

        case "customer":
            $data['html'] = '<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaCustomerResortSpecific">Resort Specific
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select name="metaCustomerResortSpecific" class="metaCustomerResortSpecific" class="form-control col-md-7 col-xs-12">
                          	  <option></option>
                          	  <option>Yes</option>
                          	  <option>No</option>
                          </select>
                        </div>
                    </div>
                    <div class="rs-add"></div>';
            break;
    }

    wp_send_json($data);
}

add_action('wp_ajax_gpx_get_switchusage', 'gpx_get_switchusage');
add_action('wp_ajax_nopriv_gpx_get_switchusage', 'gpx_get_switchusage');


function rework_coupon() {
    global $wpdb;

    $sql = "SELECT id, Properties FROM `wp_specials` WHERE `Amount` = '100' and SpecUsage='customer' and reworked=1 and active=1 ORDER BY `id`  DESC LIMIT 1";
    $rows = $wpdb->get_results($sql);

    foreach ($rows as $row) {
        $data = json_decode($row->Properties);
        $specificCustomer = json_decode($data->specificCustomer, true);
        $useExc = $data->useExc;

        foreach ($specificCustomer as $sc) {
            $sql = $wpdb->prepare("SELECT new_id FROM vest_rework_users WHERE old_id=%s", $sc);
            $newID = $wpdb->get_var($sql);
            if (!empty($newID)) {
                if (!in_array($newID, $specificCustomer)) {
                    $specificCustomer[] = $newID;
                }

                $data->useExc = str_replace('\"' . $sc . '\"', '\"' . $newID . '\"', $data->useExc);
            }
        }

        $upp = json_encode($specificCustomer);
        $data->specificCustomer = $upp;
        $wpdb->update('wp_specials', ['Properties' => json_encode($data), 'reworked' => '2'], ['id' => $row->id]);
    }
}

add_action('wp_ajax_gpx_rework_coupon', 'rework_coupon');


function rework_mc_expire() {
    global $wpdb;

    $sql = "SELECT a.id, b.datetime FROM wp_gpxOwnerCreditCoupon a
            INNER JOIN wp_gpxOwnerCreditCoupon_activity b on b.couponID=a.id
            WHERE a.created_date is null
            AND b.activity='created'";
    $rows = $wpdb->get_results($sql);

    foreach ($rows as $row) {
        $wpdb->update('wp_gpxOwnerCreditCoupon', [
            'expirationDate' => date('Y-m-d', strtotime($row->datetime . "+1 year")),
            'created_date' => date('Y-m-d', strtotime($row->datetime)),
        ], ['id' => $row->id]);
    }

    $sql = "SELECT count(id) as cnt FROM `wp_gpxOwnerCreditCoupon` WHERE `created_date` is null";
    $tcnt = $wpdb->get_var($sql);

    if ($tcnt > 0) {
        echo '<script>location.reload();</script>';
        exit;
    }
    wp_send_json(['remaining' => $tcnt]);
}

add_action('wp_ajax_gpx_rework_mc_expire', 'rework_mc_expire');


function get_gpx_desccoupons() {
    global $wpdb;
    $active = '';
    $where = '';
    $expiryStatus = '';
    if (isset($_REQUEST['limit'])) {
        $limit = $wpdb->prepare(" LIMIT %d", $_REQUEST['limit']);
    }
    if (isset($_REQUEST['offset'])) {
        $offset = $wpdb->prepare(" OFFSET %d", $_REQUEST['offset']);
    }
    if (isset($_REQUEST['Active'])) {
        if ($_REQUEST['Active'] == '1') {
            $expiryStatus = "a.active = 1";
        } elseif ($_REQUEST['Active'] == 'no') {
            $expiryStatus = "a.active = 0";
        }
    }
    $wheres = [];
    if (isset($_REQUEST['filter'])) {
        $search = json_decode(stripslashes($_REQUEST['filter']));
        foreach ($search as $sk => $sv) {

            if ($sk == 'id') {
                $wheres[] = $wpdb->prepare("a.id = %s", $sv);
            }

            if ($sk == 'Slug') {
                $wheres[] = $wpdb->prepare("a.couponcode LIKE %s", $wpdb->esc_like($sv) . '%');
            }

            if ($sk == 'EMSOwnerID') {
                $wheres[] = $wpdb->prepare("co.ownerID = %s", $sv);
            }

            if ($sk == 'ExpiryDate') {
                $wheres[] = $wpdb->prepare("expirationDate BETWEEN %s AND %s ", [
                    date('Y-m-d 00:00:00', strtotime($sv)),
                    date('Y-m-d 23:59:59', strtotime($sv)),
                ]);
            }

        }

    }

    if ($expiryStatus != '') {
        $wheres[] = $expiryStatus;
    }
    if (!empty($wheres)) {
        $where .= " WHERE " . implode(" AND ", $wheres);
    }

    if (isset($_REQUEST['sort'])) {
        if ($_REQUEST['sort'] == 'id') {
            $orderBy = " ORDER BY a.id " . gpx_esc_orderby($_REQUEST['order']);
        }

        if ($_REQUEST['sort'] == 'Name') {
            $orderBy = " ORDER BY a.name " . gpx_esc_orderby($_REQUEST['order']);
        }

        if ($_REQUEST['sort'] == 'ExpiryStatus') {
            $orderBy = " ORDER BY active " . gpx_esc_orderby($_REQUEST['order']);
        }

        if ($_REQUEST['sort'] == 'ExpiryDate') {
            $orderBy = " ORDER BY a.expirationDate " . gpx_esc_orderby($_REQUEST['order']);
        }
    }

    $joins = " INNER JOIN wp_gpxOwnerCreditCoupon_activity ca ON ca.couponID = a.id INNER JOIN wp_gpxOwnerCreditCoupon_owner co ON co.couponID = a.id ";
    $tsql = "SELECT COUNT(*) FROM (SELECT a.* FROM wp_gpxOwnerCreditCoupon a " . $joins . $where . " GROUP BY a.id) as aaa";
    $res['total'] = (int) $wpdb->get_var($tsql);
    //added a cron to switch active status daily
    $sql = "SELECT a.* FROM wp_gpxOwnerCreditCoupon a " . $joins . $where . ' GROUP BY a.id ' . $orderBy . $limit . $offset;
    $coupons = $wpdb->get_results($sql);


    $i = 0;
    $data = [];
    foreach ($coupons as $coupon) {
        $redeemed = [];
        $amount = [];
        $redeemed[] = 0;
        $amount[] = 0;
        $sql = $wpdb->prepare("SELECT * FROM wp_gpxOwnerCreditCoupon_activity WHERE couponID=%s", $coupon->id);
        $activities = $wpdb->get_results($sql);

        $coupon->activity = '';
        $allActivity = [];
        $activityAgents = [];
        foreach ($activities as $activity) {
            if (!isset($agents[$activity->userID])) {
                $agentmeta = (object) array_map(fn($a) => $a[0], get_user_meta($activity->userID) ?: []);
                $agents[$activity->userID] = ($agentmeta->first_name ?? '') . " " . ($agentmeta->last_name ?? '');
            }

            $activityAgents[] = $agents[$activity->userID];
            $allActivity[] = 'Activity: ' . $activity->activity . ' Amount: ' . $activity->amount . ' By: ' . $agents[$activity->userID] . ' ' . stripslashes($activity->activity_comments);

            if ($activity->activity == 'transaction') {
                $redeemed[] = $activity->amount;
            } else {
                $amount[] = $activity->amount;
            }
        }

        $firstAgent = $activityAgents[0];

        if (($coupon->single_use ?? false) == 1 && array_sum($redeemed) > 0) {
            $balance = 0;
        } else {
            $balance = array_sum($amount) - array_sum($redeemed);
        }

        $sql = $wpdb->prepare("SELECT * FROM wp_gpxOwnerCreditCoupon_owner WHERE couponID=%s", $coupon->id);
        $owners = $wpdb->get_results($sql);

        $membernos = [];
        foreach ($owners as $owner) {
            $usermeta = (object) array_map(function ($a) { return $a[0]; }, get_user_meta($owner->ownerID));

            if (isset($usermeta->DAEMemberNo)) {
                $membernos[] = $usermeta->DAEMemberNo;
            }
        }

        switch ($coupon->active) {
            case 0:
                $active = "No";
                break;

            case 1:
                $active = "Yes";
                break;
        }

        switch ($coupon->singleuse) {
            case 0:
                $singleuse = "No";
                break;

            case 1:
                $singleuse = "Yes";
                break;
        }
        $expirationDate = '';
        if ($coupon->expirationDate != '') {
            $expirationDate = '<div data-date="' . strtotime($coupon->expirationDate) . '">' . date('m/d/Y', strtotime($coupon->expirationDate)) . '</div>';
        }
        $data[$i]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=promos_deccouponsedit&id=' . $coupon->id . '"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
        $data[$i]['id'] = $coupon->id;
        $data[$i]['Name'] = stripslashes($coupon->name);
        $data[$i]['Slug'] = $coupon->couponcode;
        $data[$i]['EMSOwnerID'] = implode(",", $membernos);
        $data[$i]['Balance'] = $balance;
        $data[$i]['Redeemed'] = array_sum($redeemed);
        $data[$i]['SingleUse'] = $singleuse;
        $data[$i]['ExpiryDate'] = $expirationDate;
        $data[$i]['ExpiryStatus'] = $active;
        $data[$i]['comments'] = $coupon->comments;
        $data[$i]['IssuedOn'] = date('m/d/Y H:i', strtotime($coupon->created_date));
        $data[$i]['IssuedBy'] = $firstAgent;
        $data[$i]['Activity'] = implode("; ", $allActivity);
        $i++;
    }
    $res['rows'] = $data;

    wp_send_json($res);
}

add_action('wp_ajax_get_gpx_desccoupons', 'get_gpx_desccoupons');


function add_gpx_promo() {
    $data = gpx_return_add_promo($_POST);

    wp_send_json($data);
}

add_action('wp_ajax_add_gpx_promo', 'add_gpx_promo');
add_action('wp_ajax_nopriv_add_gpx_promo', 'add_gpx_promo');


function gpx_get_coupon_template() {
    $selected = '';
    if (isset($_POST['selected']) && !empty($_POST['selected'])) {
        $selected = $_POST['selected'];
    }

    $templates = gpx_retrieve_coupon_templates($selected);

    wp_send_json(['html' => $templates]);
}

add_action('wp_ajax_gpx_get_coupon_template', 'gpx_get_coupon_template');
add_action('wp_ajax_nopriv_gpx_get_coupon_template', 'gpx_get_coupon_template');

function gpx_retrieve_coupon_templates($selected = '') {
    global $wpdb;

    $sql = "SELECT id, name FROM wp_specials WHERE PromoType IN ('Auto Create Coupon Template -- Pct Off', 'Auto Create Coupon Template -- Dollar Off', 'Auto Create Coupon Template -- Set Amt')";
    $rows = $wpdb->get_results($sql);
    $html = '<option>Select Template</option>';
    foreach ($rows as $row) {
        $sel = '';
        if ($selected == $row->id) {
            $sel = 'selected';
        }
        $html .= '<option value="' . $row->id . '" ' . $sel . '>' . $row->name . '</option>';
    }

    return $html;
}


function gpx_twoforone_validate() {
    $coupon = $_POST['coupon'];
    $date = $_POST['setdate'];
    $resort = $_POST['resortID'];
    global $wpdb;
    $data = ['success' => false, 'message' => 'That coupon is not valid'];

    $sql = $wpdb->prepare("SELECT * FROM wp_specials WHERE (Slug=%s OR Name=%s) AND PromoType='2 for 1 Deposit' AND Active='1'", [
        $coupon,
        $coupon,
    ]);
    $special = $wpdb->get_row($sql);

    if (!empty($special)) {
        if (strtotime($date) >= strtotime($special->StartDate) && strtotime($date) <= strtotime($special->EndDate)) {
            $specialMeta = json_decode($special->Properties);
            if (isset($specialMeta->usage_resort) && !empty($specialMeta->usage_resort)) {
                foreach ($specialMeta->usage_resort as $ur) {
                    $sql = $wpdb->prepare("SELECT ResortID FROM wp_resorts WHERE id=%s", $ur);
                    $resortRow = $wpdb->get_row($sql);

                    $resortIDs[] = $resortRow->ResortID;
                }
                if (in_array($resort, $resortIDs)) {
                    $data = ['success' => true, 'name' => $special->Name];
                }
            } else {
                $data = ['success' => true, 'name' => $special->Name];
            }
        }
    }


    return $data;

    wp_send_json($data);
}

add_action("wp_ajax_gpx_twoforone_validate", "gpx_twoforone_validate");
add_action("wp_ajax_nopriv_gpx_twoforone_validate", "gpx_twoforone_validate");


function get_gpx_promoautocouponexceptions() {
    global $wpdb;

    $data = [];

    $sql = "SELECT * FROM wp_gpx_import_account_credit WHERE is_added=2";
    $results = $wpdb->get_results($sql);

    $i = 0;
    foreach ($results as $row) {
        $data[$i]['account'] = $row->account;
        $data[$i]['business_date'] = $row->business_date;
        $data[$i]['amount'] = $row->amount;
        $i++;
    }

    wp_send_json($data);
}

add_action('wp_ajax_get_gpx_promoautocouponexceptions', 'get_gpx_promoautocouponexceptions');


function gpx_promo_dup_check() {
    global $wpdb;

    $data = ['success' => true];

    if (isset($_POST['slug'])) {
        $sql = $wpdb->prepare("SELECT * FROM wp_specials WHERE slug LIKE %s", $wpdb->esc_like($_POST['slug']));
        $row = $wpdb->get_row($sql);

        if (!empty($row)) {
            $data = ['error' => 'You already used this slug.'];
        }
    }

    wp_send_json($data);
}

add_action('wp_ajax_gpx_promo_dup_check', 'gpx_promo_dup_check');


function get_gpx_promoautocoupons() {
    global $wpdb;

    $sql = "SELECT * FROM wp_gpxAutoCoupon";
    $acs = $wpdb->get_results($sql);
    $i = 0;
    $data = [];
    foreach ($acs as $ac) {
        $user = UserMeta::load($ac->user_id);

        $sql = $wpdb->prepare("SELECT b.resortName, b.checkIn FROM wp_gpxTransactions a
                    INNER JOIN wp_properties b ON  a.weekId = b.weekId
                    WHERE a.id=%s", $ac->transaction_id);
        $transaction = $wpdb->get_row($sql);

        $sql = $wpdb->prepare("SELECT Slug FROM wp_specials WHERE id=%s", $ac->coupon_id);
        $special = $wpdb->get_row($sql);

        $data[$i]['Name'] = $user->getName();
        $data[$i]['Transaction'] = $transaction->resortName . "<br>" . $transaction->checkIn;
        $data[$i]['Coupon'] = $special->Slug . "-" . $ac->coupon_hash;
        $data[$i]['Used'] = $ac->used ? 'Yes' : 'No';
        $i++;
    }

    wp_send_json($data);
}

add_action('wp_ajax_get_gpx_promoautocoupons', 'get_gpx_promoautocoupons');
add_action('wp_ajax_nopriv_get_gpx_promoautocoupons', 'get_gpx_promoautocoupons');

function gpx_return_add_promo($post = []) {
    global $wpdb;

    $post = stripslashes_deep(empty($post) ? $_POST : $post);

    if (isset($post['metaUseExc'])) {
        $post['metaUseExc'] = base64_decode($post['metaUseExc']);
    }

    if (isset($post['remove'])) {
        $wpdb->delete('wp_specials', ['id' => $post['remove']]);
        $wpdb->delete('wp_promo_meta', ['specialsID' => $post['remove']]);

        $output = ['success' => true, 'msg' => 'Successfully removed Promotion!'];

        return $output;
    }
    $meta = [
        'promoType' => $post['metaType'],
        'transactionType' => $post['metaTransactionType'] ?? 'any',
        'upsellOptions' => $post['metaUpsellOptions'] ?? [],
        'usage' => implode(",", array_unique($post['metaUsage'])),
        'exclusions' => implode(",", array_unique($post['metaExclusions'])),
        'exclusiveWeeks' => $post['exclusiveWeeks'],
        'stacking' => $post['metaStacking'],
        'bookStartDate' => $post['metaBookStartDate'],
        'bookEndDate' => date('Y-m-d 23:59:59', strtotime($post['metaBookEndDate'])),
        'travelStartDate' => $post['metaTravelStartDate'],
        'travelEndDate' => date('Y-m-d 23:59:59', strtotime($post['metaTravelEndDate'])),
        'leadTimeMin' => $post['metaLeadTimeMin'],
        'leadTimeMax' => $post['metaLeadTimeMax'],
        'terms' => $post['metaTerms'],
        'minWeekPrice' => $post['metaMinWeekPrice'],
        'maxValue' => $post['metaMaxValue'],
        'useExc' => $post['metaUseExc'],
        'availability' => $post['availability'],
        'slash' => $post['metaSlash'],
        'highlight' => $post['metaHighlight'],
    ];

    //blackout dates
    if (isset($post['metaBlackoutStart']) && !empty($post['metaBlackoutStart'])) {
        foreach ($post['metaBlackoutStart'] as $mbosKey => $mbosVal) {
            if (strtotime($mbosVal) > '1511362833') {
                $meta['blackout'][] = [
                    'start' => date('Y-m-d 00:00:00', strtotime($mbosVal)),
                    'end' => date('Y-m-d 23:59:59', strtotime($post['metaBlackoutEnd'][$mbosKey])),
                ];
            }
        }
    }
    //resort specific travel dates
    if (isset($post['metaResortBlackoutResorts']) && !empty($post['metaResortBlackoutResorts'])) {
        foreach ($post['metaResortBlackoutResorts'] as $metaResortBlackoutResort) {
            $metaResortBlackoutResorts = explode(",", $metaResortBlackoutResort);
            if (isset($post['metaResortBlackoutStart']) && !empty($post['metaResortBlackoutStart']) && isset($post['metaResortBlackoutEnd']) && !empty($post['metaResortBlackoutEnd'])) {
                foreach ($post['metaResortBlackoutStart'] as $mrbsKey => $mrbsValue) {
                    if (strtotime($mrbsValue) > '1511362833') {
                        $meta['resortBlackout'][] = [
                            'resorts' => $metaResortBlackoutResorts,
                            'start' => date('Y-m-d 00:00:00', strtotime($mrbsValue)),
                            'end' => date('Y-m-d 23:59:59', strtotime($post['metaResortBlackoutEnd'][$mrbsKey])),
                        ];
                    }
                }
            }
        }
    }
    //resort specific travel dates
    if (isset($post['metaResortTravelResorts']) && !empty($post['metaResortTravelResorts'])) {
        foreach ($post['metaResortTravelResorts'] as $metaResortTravelResort) {
            $metaResortTravelResorts = explode(",", $metaResortTravelResort);
            if (isset($post['metaResortTravelStart']) && !empty($post['metaResortTravelStart']) && isset($post['metaResortTravelEnd']) && !empty($post['metaResortTravelEnd'])) {
                foreach ($post['metaResortTravelStart'] as $mrtsKey => $mrtsValue) {
                    if (strtotime($mrtsValue) > '1511362833') {
                        $meta['resortTravel'][] = [
                            'resorts' => $metaResortTravelResorts,
                            'start' => date('Y-m-d 00:00:00', strtotime($mrtsValue)),
                            'end' => date('Y-m-d 23:59:59', strtotime($post['metaResortTravelEnd'][$mrtsKey])),
                        ];
                    }
                }
            }
        }
    }

    if (!empty($post['metaFlashStart'])) {
        $meta['flashStart'] = $post['metaFlashStart'];
    }

    if (!empty($post['metaFlashEnd'])) {
        $meta['flashEnd'] = $post['metaFlashEnd'];
    }

    $couponorpromo = 'promo';
    if ($post['bookingFunnel'] == 'No') //this is a coupon
    {
        $couponorpromo = 'coupon';
        $meta['maxCoupon'] = $post['metaMaxCoupon'];
        $meta['singleUse'] = $post['metaSingleUse'];
    } else {
        $meta['beforeLogin'] = $post['metaBeforeLogin'];
        $meta['GACode'] = $post['metaGACode'];
    }
    $meta['icon'] = $post['metaIcon'];
    $meta['desc'] = $post['metaDesc'];
    if (isset($post['metaSpecificCustomer'])) {
        $meta['specificCustomer'] = json_encode($post['metaSpecificCustomer']);
    }

    $extraupdate = [];  // initialize array correctly
    if (!empty($post['metaUsage'])) {
        foreach ($post['metaUsage'] as $museage) {
            switch ($museage) {
                case 'region':
                    if (!empty($post['metaSetRegion'])) {
                        $meta['usage_regionType'] = 'gpxRegion';
                        $meta['usage_region'] = json_encode($post['metaSetRegion']);
                        foreach ($post['metaSetRegion'] as $msr) {
                            $extraupdate[] = [$msr => 'gpxRegion'];
                        }
                    }
                    break;

                case 'resort':
                    if (!empty($post['usage_resort'])) {
                        foreach ($post['usage_resort'] as $resort) {
                            $meta['usage_resort'][] = $resort;
                            $extraupdate[] = [$resort => 'resorts'];
                            $extraforeign[] = $resort;
                        }
                    }
                    break;

                case 'trace':

                    break;

                case 'customer':
                    $meta['metaCustomerResortSpecific'] = $post['metaCustomerResortSpecific'];
                    if ($post['metaCustomerResortSpecific'] == 'Yes') {
                        if (!empty($post['usage_resort'])) {
                            foreach ($post['usage_resort'] as $resort) {
                                $meta['usage_resort'][] = $resort;
                                $extraupdate[] = [$resort => 'resorts'];
                            }
                        }
                    }
                    break;

                case 'dae':
                    $meta['useage_dae'] = 1;
                    break;

            }
        }
    }
    if (!empty($post['metaExclusions'])) {
        foreach ($post['metaExclusions'] as $mexc) {
            switch ($mexc) {
                case 'region':
                    if (!empty($post['metaSetRegionExclude'])) {
                        $meta['exclude_regionType'] = 'gpxRegion';
                        $meta['exclude_region'] = json_encode($post['metaSetRegionExclude']);
                    }
                    break;

                case 'resort':
                    if (!empty($post['exclude_resort'])) {
                        foreach ($post['exclude_resort'] as $resort) {
                            $meta['exclude_resort'][] = $resort;
                        }
                    }
                    break;

                case 'trace':

                    break;

                case 'home-resort':
                    if (!empty($post['exclude_resort'])) {
                        foreach ($post['exclude_resort'] as $resort) {
                            $meta['exclude_home_resort'][] = $resort;
                        }
                    }
                    break;

                case 'customer':
                    $meta['metaCustomerResortSpecificExclusions'] = $post['metaCustomerResortSpecificExclusions'];
                    if ($post['metaCustomerResortSpecificExclusions'] == 'Yes') {
                        if (!empty($post['exclude_resort'])) {
                            foreach ($post['exclude_resort'] as $resort) {
                                $meta['exclude_resort'][] = $resort;
                            }
                        }
                    }
                    break;

                case 'dae':
                    $meta['exclude_dae'] = 1;
                    break;

            }
        }
    }
    if (isset($post['actc']) && !empty($post['actc'])) {
        $meta['actc'] = $post['actc'];
    }
    if (isset($post['couponTemplate']) && !empty($post['couponTemplate'])) {
        $meta['couponTemplate'] = $post['couponTemplate'];
    }
    if (isset($post['acCoupon']) && $post['acCoupon'] == 1) {
        $meta['acCoupon'] = $post['acCoupon'];
    }
    $Amount = '';
    if (isset($post['Amount'])) {
        $Amount = $post['Amount'];
    }
    $update = [
        'Properties' => json_encode($meta),
        'Name' => $post['Name'],
        'Slug' => $post['Slug'],
        'PromoType' => $post['metaType'],
        'Type' => $couponorpromo,
        'Amount' => $Amount,
        'StartDate' => date('Y-m-d 00:00:00', strtotime($post['StartDate'])),
        'EndDate' => date('Y-m-d 23:59:59', strtotime($post['EndDate'])),
        'TravelStartDate' => date('Y-m-d 00:00:00', strtotime($post['metaTravelStartDate'])),
        'TravelEndDate' => date('Y-m-d 23:59:59', strtotime($post['metaTravelEndDate'])),
        'Active' => $post['Active'],
        'SpecUsage' => implode(",", array_unique($post['metaUsage'])),
        'showIndex' => $post['showIndex'],
    ];
    $update['master'] = $post['master'];
    $datetime = date('Y-m-d H:i:s');
    $current_user = wp_get_current_user();

    if (empty($post['specialID'])) {
        $rev[$datetime] = $current_user->display_name;

        $update['revisedBy'] = json_encode($rev);

        $wpdb->insert('wp_specials', $update);

        $sid = $wpdb->insert_id;
        $output = ['success' => true, 'msg' => 'Promotion Added!'];
    } else {
        $sql = $wpdb->prepare("SELECT revisedBy FROM wp_specials WHERE id=%s", $post['specialID']);
        $revRow = $wpdb->get_row($sql);
        if (isset($revRow) && !empty($revRow->revisedBy)) {
            $rev = json_decode($revRow->revisedBy);
        }

        $current_user = wp_get_current_user();

        $rev->$datetime = $current_user->display_name;

        $update['revisedBy'] = json_encode($rev);

        $wpdb->update('wp_specials', $update, ['id' => $post['specialID']]);
        $sid = $post['specialID'];
        $output = ['success' => true, 'msg' => 'Promotion Updated!'];
    }
    if (!empty($extraupdate)) {
        $wpdb->delete('wp_promo_meta', ['specialsID' => $sid]);
        foreach ($extraupdate as $eus) {
            foreach ($eus as $euk => $euv) {
                $table = 'wp_' . $euv;
                $updateExtra = [
                    'specialsID' => $sid,
                    'refTable' => $table,
                    'foreignID' => $euk,
                ];
                $wpdb->insert('wp_promo_meta', $updateExtra);
            }
        }
    }

    if (wp_doing_ajax()) {
        return $output;
    } else {
        if (isset($output['success'])) {
            return true;
        } else {
            return false;
        }
    }
}
