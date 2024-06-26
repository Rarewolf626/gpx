<?php

namespace GPX\Model;

use Carbon\CarbonPeriod;
use DB;
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

    public function __construct($input = [])
    {
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
        $name = $this->filters['city'] ?: $this->filters['region'];
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
    public function get_matches($input = []): MatchesCollection
    {
        if ($input) {
            $this->set_filters($input);
        }

        return $this->find_inventory();

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

    private function find_inventory()
    {
        global $wpdb;

        $resort = $this->find_resort($this->filters['resort']);
        if ($this->filters['resort'] && !$resort) {
            // if a resort was requested but it was not found, return empty result
            return new MatchesCollection();
        }

        $region = $this->find_region($this->filters['city'] ?: $this->filters['region']);
        if (!$this->filters['resort'] && !$region) {
            // if a resort was not requested and no region was found, return empty result
            return new MatchesCollection();
        }

        $resortTypeWhere = $this->build_resort_type_where();
        $roomTypeWhere = $this->build_room_type_where();
        $regionWhere = $this->build_region_where($region);
        $resortWhere = $this->build_resort_where($resort);
        $nearbyWhere = $this->build_nearby_where($resort);
        $locationWhere = implode(' OR ', array_filter([$regionWhere, $resortWhere, $nearbyWhere]));

        // ok, we found regions
        $sql = $wpdb->prepare("SELECT
                    a.record_id as weekId,
                    a.resort as resort_id,
                    b.GPXRegionID as region_id
                FROM wp_room a
                INNER JOIN wp_resorts b ON a.resort=b.id
                INNER JOIN wp_unit_type c ON a.unit_type=c.record_id
                WHERE
                    ($locationWhere)
                    AND (DATE(a.check_in_date) BETWEEN %s and %s)
                    $resortTypeWhere
                    $roomTypeWhere
                    AND a.active=1
                    AND b.active=1",
            date('Y-m-d', strtotime($this->filters['checkIn'])),
            date('Y-m-d', strtotime($this->filters['checkIn2']))
        );
        // get properties
        $results = collect(DB::connection()->select($sql))->map(fn($result) => (array)$result)->keyBy('weekId');
        return new MatchesCollection($results);
    }

    private function build_resort_where($resort = null): string
    {
        if (empty($resort)) return '';
        global $wpdb;
        return $wpdb->prepare("(a.resort = %d)", $resort['id']);
    }

    private function build_nearby_where(array $resort = null): string
    {
        if (empty($resort)) return '';
        if (!$this->filters['nearby']) return '';

        $latitude = $resort['latitude'];
        $longitude = $resort['longitude'];
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            if (empty($resort['LatitudeLongitude'])) {
                return '';
            }
            [$latitude, $longitude] = explode(',', $resort['LatitudeLongitude']);
        }

        global $wpdb;
        // find other resorts nearby
        $distance = $wpdb->prepare("ST_Distance_Sphere(ST_GeomFromText('POINT(%f %f)'), ST_GeomFromText(CONCAT('POINT(',`longitude`,' ',`latitude`,')')))",
            [$longitude, $latitude]
        );
        $sql = $wpdb->prepare("SELECT
            `id`, {$distance} as 'distance'
        FROM `wp_resorts`
        WHERE `id` != %d AND `latitude` IS NOT NULL AND `longitude` IS NOT NULL AND {$distance} <= %d
        ORDER BY distance ASC
        ",
            $resort['id'],
            static::MILES * 1609.344 /* miles to meters */
        );
        $resorts = $wpdb->get_col($sql);
        if (!$resorts) {
            return '';
        }
        $resorts = implode(',', array_filter(array_map('intval', Arr::wrap($resorts))));
        return "(a.resort IN ({$resorts}))";
    }

    private function build_region_where(array $region = null): string
    {
        global $wpdb;

        if (!$region) {
            return '';
        }

        //get all the regions
        $sql = $wpdb->prepare("SELECT id FROM wp_gpxRegion WHERE lft BETWEEN %d AND %d ORDER BY lft ASC",
            [$region['lft'], $region['rght']]
        );
        $ids = $wpdb->get_col($sql);
        if (!$ids) {
            return '';
        }
        return "(b.GPXRegionID IN (" . implode(',', array_map('intval', $ids)) . "))";
    }


    private function build_resort_type_where(): string
    {
        //  1  exchange
        //  2  rental
        //  3  both
        $rtWhere = "";
        // there is a resort type preference set and it's not "Any"
        if (!empty($this->filters['preference']) && $this->filters['preference'] != 'Any') {
            if ($this->filters['preference'] == 'Exchange') {
                $rtWhere = " AND a.`type` IN ('3','1') ";
            } else {
                $rtWhere = " AND a.`type` IN ('3','2') ";
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

        return match ( $this->filters['roomType'] ) {
            '3BR' => array_merge( $types['3BR'], $types['4BR'] ),
            '2BR' => array_merge( $types['2BR'], $types['3BR'], $types['4BR'] ),
            '1BR' => array_merge( $types['1BR'], $types['2BR'], $types['3BR'], $types['4BR'] ),
            'Studio' => array_merge( $types['Studio'], $types['1BR'], $types['2BR'], $types['3BR'], $types['4BR'] ),
            default => [],
        };
    }
}
