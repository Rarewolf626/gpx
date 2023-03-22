<?php

namespace GPX\Model;

use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use GPX\Repository\RegionRepository;
use GPX\Collection\MatchesCollection;

class CustomRequestMatch
{
    const MILES = 30;

    private $filters = [
        'adults' => 0,
        'children' => 0,
        'checkIn' => null,
        'checkIn2' => null,
        'roomType' => 'Any',
        'larger' => 0,
        'preference' => 'Any',
        'nearby' => null,
        'region' => null,
        'city' => null,
        'resort' => null,
    ];

    private $roomSizes = [];  // array of room sizes to search

    private ?MatchesCollection $results = null;    // the resulting rooms matching criteria

    public function __construct($input = [])
    {
        $this->results = new MatchesCollection();
        $this->set_filters($input);
    }

    /**
     * Restricted between Jun 1 and Sep 1
     */
    public function has_restricted_date(): bool
    {
        $checkin = Carbon::createFromFormat('m/d/Y', $this->filters['checkIn'])->startOfDay();
        $checkout = Carbon::createFromFormat('m/d/Y', $this->filters['checkIn2'])->endOfDay();
        $period = CarbonPeriod::create($checkin, $checkout);
        $start = $checkin->clone()->setMonth(6)->setDay(1)->startOfDay();
        $end = $checkin->clone()->setMonth(9)->setDay(1)->endOfDay();
        $blocked = CarbonPeriod::create($start, $end);

        return $period->overlaps($blocked);
    }

    public function in_restricted_region(int $region_id): bool
    {
        return in_array($region_id, $this->gpx_get_restricted_regions());
    }

    public function is_restricted(int $regionid): bool
    {
        if (!$this->has_restricted_date()) {
            return false;
        }

        return $this->in_restricted_region($regionid);
    }

    public function is_fully_restricted(): bool
    {
        if (empty($this->filters['region']) && empty($this->filters['resort']) && empty($this->filters['city'])) {
            return false;
        }
        if (!$this->has_restricted_date()) {
            return false;
        }
        if ($this->filters['resort']) {
            $resort = $this->find_resort($this->filters['resort']);
            if ($resort) {
                return $this->in_restricted_region($resort['region_id']);
            }
        }
        $name = $this->filters['city'] ?? $this->filters['region'];
        if (!$name) {
            return false;
        }
        $region = $this->find_region($name);
        if (!$region) {
            return false;
        }

        return $this->in_restricted_region($region['id']);
    }

    private function gpx_get_restricted_regions(): array
    {
        static $regions;
        if (!$regions) {
            $regions = RegionRepository::instance()->restricted();
        }

        return $regions;
    }

    /**
     *  model to build a custom request match
     *
     *  0. Validate
     *  1. check the dates are not restricted
     *  2. check the region is not restricted
     *  3. determine the array of room sizes to use in the filter
     *  3. find rooms at the resort that are available in the date range
     *  4. see if they submitted a region and check that region for additional rooms
     *  5. check if they clicked will accept nearby resort matches?
     *
     * @param array|CustomRequest $input
     *
     * @return MatchesCollection
     */
    public function get_matches($input = [])
    {
        if ($input) {
            $this->set_filters($input);
        }
        $this->results = new MatchesCollection();

        // build an array of $this->roomSizes to search
        $this->determine_room_sizes_to_search();
        // if resort property selected, then find inventory in that property
        if (!empty($this->filters['resort'])) {  //search by resort
            $resortid = $this->get_resort_id_from_name($this->filters['resort']);
            $this->find_inventory_by_resort($resortid);

            // if nearby selected, then use miles from preferred property and add to result properties
            // nearby requires -  nearby, miles, and resort
            if ($this->filters['nearby']) {
                $this->find_inventory_nearby();
            }
        } elseif (!empty($this->filters['region'])) {
            // if region/city selected, then find properties in that location and add to result properties
            $this->find_inventory_by_region();
        }


        // return the result set
        return $this->results;
    }

    /**
     * @param array|CustomRequest $input
     *
     * @return void
     */
    public function set_filters($input = [])
    {
        if ($input instanceof CustomRequest) {
            $input = $input->toFilters();
        }
        if (!is_array($input)) {
            throw new \InvalidArgumentException('Filters must be array or CustomRequest');
        }
        $this->validate_filters($input);
        $this->results = new MatchesCollection();
    }

    private function get_resort_id_from_name(string $resortname = null): ?int
    {
        $resort = $this->find_resort($resortname);

        return $resort ? (int)$resort['id'] : null;
    }

    public function find_resort(string $resortname = null): ?array
    {
        global $wpdb;
        if (empty($resortname)) return null;

        $sql = $wpdb->prepare("SELECT id,gpxRegionID as region_id,LatitudeLongitude, latitude, longitude FROM wp_resorts WHERE ResortName = %s",
            $resortname
        );

        return $wpdb->get_row($sql, ARRAY_A);
    }


    public function find_region(string $regionname = null): ?array
    {
        global $wpdb;
        if (empty($regionname)) return null;

        // region as country?
        $sql = $wpdb->prepare("SELECT countryID from wp_gpxCategory WHERE country=%s && CountryID < 1000",
            $regionname
        );
        $category = $wpdb->get_row($sql);
        if ($category) {
            $sql = $wpdb->prepare("SELECT a.id, a.lft, a.rght FROM wp_gpxRegion a
                    INNER JOIN wp_daeRegion b ON a.RegionID=b.id
                    WHERE b.CategoryID=%s",
                $category->countryID
            );
        } else {
            $sql = $wpdb->prepare("SELECT id, lft, rght FROM wp_gpxRegion WHERE name=%s OR subName=%s OR displayName=%s",
                [$regionname, $regionname, $regionname]
            );
        }

        return $wpdb->get_row($sql, ARRAY_A);
    }

    /**
     * @param int[]|int $resortid
     *
     * @return void
     */
    private function find_inventory_by_resort($resortid = null)
    {
        global $wpdb;
        if (empty($resortid)) return;
        $resorts = implode(',', array_filter(array_map('intval', Arr::wrap($resortid))));
        if (empty($resorts)) return;

        $resortTypeWhere = $this->build_resort_type_where();
        $roomTypeWhere = $this->build_room_type_where();
        $sql = $wpdb->prepare("SELECT
                a.record_id as weekId,
                a.resort as resort_id,
                b.GPXRegionID as region_id
            FROM wp_room a
            INNER JOIN wp_resorts b ON a.resort=b.id
            INNER JOIN wp_unit_type c ON a.unit_type=c.record_id
                WHERE a.resort IN ({$resorts})
                AND check_in_date BETWEEN %s AND %s
                $resortTypeWhere
                $roomTypeWhere
                AND a.active=1
                AND b.active=1",
            date('Y-m-d', strtotime($this->filters['checkIn'])),
            date('Y-m-d', strtotime($this->filters['checkIn2']))
        );
        $weeks = collect($wpdb->get_results($sql, ARRAY_A))->keyBy('weekId');
        $this->results = $this->results->merge($weeks);
    }

    /**
     * @return string
     */
    private function build_resort_type_where()
    {
        //  1  exchange
        //  2  rental
        //  3  both
        $rtWhere = "";
        // there is a resort type preference set and it's not "Any"
        if (isset($this->filters['preference']) && !empty($this->filters['preference']) && $this->filters['preference'] != 'Any') {
            if ($this->filters['preference'] == 'Exchange') {
                $rtWhere = " AND type IN ('3','1') ";
            } else {
                $rtWhere = " AND type IN ('3','2') ";
            }
        }

        return $rtWhere;
    }

    private function build_room_type_where(): string
    {
        global $wpdb;
        if (!$this->filters['roomType'] || $this->filters['roomType'] === 'Any') {
            return '';
        }
        $sizes = $this->determine_room_sizes_to_search();
        if (!$sizes) {
            return '';
        }

        $placeholders = gpx_db_placeholders($sizes);

        return $wpdb->prepare(" AND c.number_of_bedrooms IN ({$placeholders}) ", $sizes);
    }


    /**
     * find inventory nearby a resort
     *
     * @return void
     */
    private function find_inventory_nearby()
    {
        global $wpdb;

        if (empty($this->filters['resort'])) {
            return;
        }

        $resort = $this->find_resort($this->filters['resort']);
        if (!$resort) {
            // requested resort was not found
            return;
        }
        $latitude = $resort['latitude'];
        $longitude = $resort['longitude'];
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            if (empty($resort['LatitudeLongitude'])) {
                return;
            }
            [$latitude, $longitude] = explode(',', $resort['LatitudeLongitude']);
        }

        // find other resorts nearby
        $distance = $wpdb->prepare("ST_Distance_Sphere(ST_GeomFromText('POINT(%f %f)'), ST_GeomFromText(CONCAT('POINT(',`longitude`,' ',`latitude`,')')))",
            [$longitude, $latitude]
        );
        $sql = $wpdb->prepare("SELECT
            `id`, {$distance} as 'distance'
        FROM `wp_resorts`
        WHERE `id` != %d AND `latitude` IS NOT NULL AND `longitude` IS NOT NULL AND {$distance} <= %d
        ORDER BY distance asc
        ",
            $resort['id'],
            static::MILES * 1609.344 /* miles to meters */
        );
        $resorts = $wpdb->get_col($sql);
        if (!$resorts) {
            return;
        }
        $this->find_inventory_by_resort($resorts);
    }

    private function find_inventory_by_region(string $regionname = null)
    {
        global $wpdb;

        // use param or filter?
        $regionname = $regionname ?? $this->filters['region'];
        if ($regionname == null) {
            return;
        }

        $region = $this->find_region($regionname);
        if (!$region) {
            return;
        }

        //get all the regions
        $sql = $wpdb->prepare("SELECT id FROM wp_gpxRegion WHERE lft BETWEEN %d AND %d ORDER BY lft ASC",
            [$region['lft'], $region['rght']]
        );
        $ids = $wpdb->get_col($sql);
        if (!$ids) {
            return;
        }

        $resortTypeWhere = $this->build_resort_type_where();
        $roomTypeWhere = $this->build_room_type_where();

        // ok, we found regions
        $sql = $wpdb->prepare("SELECT
                    a.record_id as weekId,
                    a.resort as resort_id,
                    b.GPXRegionID as region_id
                FROM wp_room a
                INNER JOIN wp_resorts b ON a.resort=b.id
                INNER JOIN wp_unit_type c ON a.unit_type=c.record_id
                    WHERE b.GPXRegionID IN (" . implode(',', array_map('intval', $ids)) . ")
                    AND check_in_date BETWEEN %s AND %s
                    $resortTypeWhere
                    $roomTypeWhere
                    AND a.active=1
                    AND b.active=1",
            date('Y-m-d', strtotime($this->filters['checkIn'])),
            date('Y-m-d', strtotime($this->filters['checkIn2']))
        );
        // get properties
        $props = collect($wpdb->get_results($sql, ARRAY_A))->keyBy('weekId');
        $this->results = $this->results->merge($props);
    }

    /**
     * checks for valid input and stores the inputs in $this->filters
     */
    private function validate_filters(array $input = [])
    {
        $input = array_merge($this->filters, array_intersect_key($input, $this->filters));
        $input = filter_var_array($input, [
            'adults' => [
                'filter' => FILTER_VALIDATE_INT,
                'flags' => FILTER_REQUIRE_SCALAR,
                'options' => ['default' => 0, 'min_range' => 0],
            ],
            'children' => [
                'filter' => FILTER_VALIDATE_INT,
                'flags' => FILTER_REQUIRE_SCALAR,
                'options' => ['default' => 0, 'min_range' => 0],
            ],
            'checkIn' => [
                'filter' => FILTER_VALIDATE_REGEXP,
                'flags' => FILTER_REQUIRE_SCALAR | FILTER_NULL_ON_FAILURE,
                'options' => ['regexp' => "/^\d{2}\/\d{2}\/\d{4}$/"],
            ],
            'checkIn2' => [
                'filter' => FILTER_VALIDATE_REGEXP,
                'flags' => FILTER_REQUIRE_SCALAR | FILTER_NULL_ON_FAILURE,
                'options' => ['regexp' => "/^\d{2}\/\d{2}\/\d{4}$/"],
            ],
            'roomType' => [
                'filter' => FILTER_VALIDATE_REGEXP,
                'flags' => FILTER_REQUIRE_SCALAR,
                'options' => ['default' => 'Any', 'regexp' => "/^Any|Studio|1BR|2BR|3BR$/"],
            ],
            'larger' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'flags' => FILTER_REQUIRE_SCALAR,
                'options' => ['default' => false],
            ],
            'preference' => [
                'filter' => FILTER_VALIDATE_REGEXP,
                'flags' => FILTER_REQUIRE_SCALAR,
                'options' => ['default' => 'Any', 'regexp' => "/^Any|Rental|Exchange$/"],
            ],
            'nearby' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'flags' => FILTER_REQUIRE_SCALAR,
                'options' => ['default' => false],
            ],
            'region' => [
                'filter' => FILTER_DEFAULT,
                'flags' => FILTER_REQUIRE_SCALAR,
            ],
            'city' => [
                'filter' => FILTER_DEFAULT,
                'flags' => FILTER_REQUIRE_SCALAR,
            ],
            'resort' => [
                'filter' => FILTER_DEFAULT,
                'flags' => FILTER_REQUIRE_SCALAR,
            ],
        ], true);

        // @todo confirm there are at least 1 adult
        // @todo confirm there is at least a checkin date
        // @todo confirm there is either a region or a resort selected
        // check and set the region / city
        if (!empty($input['city'])) {
            $input['region'] = $input['city'];
        }

        // if the check-out date is not set, then set as check in date + 6 days (1 week)
        if (!$input['checkIn2']) {
            $input['checkIn2'] = date('m/d/Y', strtotime($input['checkIn'] . ' + 6 days'));
        }

        $this->filters = $input;
    }

    private function determine_room_sizes_to_search(): array
    {
        $types = Room::get_room_types();
        if ($this->filters['roomType'] === 'Any') {
            return [];
        }
        if (!$this->filters['larger'] && isset($types[$this->filters['roomType']])) {
            return $types[$this->filters['roomType']];
        }
        switch ($this->filters['roomType']) {
            case '3BR':
                return array_merge($types['3BR'], $types['4BR']);
            case '2BR':
                return array_merge($types['2BR'], $types['3BR'], $types['4BR']);
            case '1BR':
                return array_merge($types['1BR'], $types['2BR'], $types['3BR'], $types['4BR']);
            case 'Studio':
                return array_merge($types['Studio'], $types['1BR'], $types['2BR'], $types['3BR'], $types['4BR']);
            case 'Any':
            default:
                return [];
        }
    }
}
