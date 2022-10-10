<?php

namespace GPX\Model;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use GPX\Repository\RegionRepository;
use GPX\Collection\MatchesCollection;

class CustomRequestMatch {

    private $filters = [
        'adults'     => 0,
        'children'   => 0,  // occupancy
        'checkIn'    => null,
        'checkIn2'   => null,  // check in and check out dates
        'roomType'   => 'Any',      // size of room requested
        'larger'     => 0,            // look for larger rooms
        'preference' => 'Any',  // exchange/rental/Both
        'nearby'     => null, // search nearby resorts
        'miles'      => 30,      // miles search radius
        'region'     => null,   // a city was selected
        'city'       => null,   // a city was selected
        'resort'     => null  // a specific resort was selected
    ];

    private $roomSizes = [];  // array of room sizes to search

    private ?MatchesCollection $results = null;    // the resulting rooms matching criteria


    /**
     * @todo what happens if they try to book in advance memorial day next year?
     *
     */
    public function __construct( $input = [] ) {
        $this->results = new MatchesCollection();
        $this->set_filters( $input );
    }

    public function has_restricted_date(): bool {
        $date  = Carbon::createFromFormat( 'm/d/Y', $this->filters['checkIn'] );
        $start = $date->clone()->setMonth( 6 )->setDay( 1 )->format( 'Y-m-d' );
        $end   = $date->clone()->setMonth( 9 )->setDay( 1 )->format( 'Y-m-d' );
        $date  = $date->format( 'Y-m-d' );

        return $date >= $start && $date < $end;
    }

    public function in_restricted_region( int $region_id ): bool {
        return in_array( $region_id, $this->gpx_get_restricted_regions() );
    }

    public function is_restricted( int $regionid ): bool {
        if ( ! $this->has_restricted_date() ) {
            return false;
        }

        return $this->in_restricted_region( $regionid );
    }

    public function is_fully_restricted(): bool {
        if ( empty( $this->filters['region'] ) && empty( $this->filters['resort'] ) && empty( $this->filters['city'] ) ) {
            return false;
        }
        if ( ! $this->has_restricted_date() ) {
            return false;
        }
        if ( $this->filters['resort'] ) {
            $resort = $this->find_resort( $this->filters['resort'] );
            if ( $resort ) {
                return $this->in_restricted_region( $resort['region_id'] );
            }
        }
        $name = $this->filters['city'] ?? $this->filters['region'];
        if ( ! $name ) {
            return false;
        }
        $region = $this->find_region( $name );
        if ( ! $region ) {
            return false;
        }

        return $this->in_restricted_region( $region['id'] );
    }

    private function gpx_get_restricted_regions(): array {
        static $regions;
        if ( ! $regions ) {
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
    public function get_matches( $input = [] ) {
        if ( $input ) {
            $this->set_filters( $input );
        }

        // build an array of $this->roomSizes to search
        $this->determine_room_sizes_to_search();
        // if resort property selected, then find inventory in that property
        if ( ! empty( $this->filters['resort'] ) ) {  //search by resort
            $resortid = $this->get_resort_id_from_name( $this->filters['resort'] );
            $this->find_inventory_by_resort( $resortid );

            // if nearby selected, then use miles from preferred property and add to result properties
            // nearby requires -  nearby, miles, and resort
            if ( $this->filters['miles'] && $this->filters['nearby'] ) {
                $this->find_inventory_nearby();
            }
        } elseif ( ! empty( $this->filters['region'] ) ) {
            // if region/city selected, then find properties in that location and add to result properties
            $this->find_inventory_by_region();
        }


        // return the result set
        return $this->results;
    }


    /**
     * @param int $weekId
     *
     * @return false
     *
     * checks the single int weekId to see if it is a match for the filters
     * requires filters to be set, otherwise will result in false positive
     *
     * @todo complete the rest of the cheks
     */
    public function is_match( int $weekId ) {
        $match = true;

        // assume filter has been set
        // @todo check filter is set

        // get week
        $week = Week::active()->with('unit')->find( $weekId );
        if ( ! $week ) {
            return false;
        }

        // START MATCHING

        // make sure week is available
        // @todo make sure week is available

        // check date range - make sure the check-in date hasn't passed
        // @todo check date range

        // room size
        if ( $this->filters['roomType'] != 'Any' ) {  // not any, room size matters
            if ( $this->filters['larger'] ) {   // allow rooms larger than filter
                // check if the room is at least the right size
                if ( $week->unit->number_of_bedrooms < $this->filters['roomType'] ) {
                    return false;
                }
            } else {  // no larger rooms
                // check if the room is the exact size
                if ( $week->unit->number_of_bedrooms != $this->filters['roomType'] ) {
                    return false;
                }
            }
        }

        // location correct
        // @todo check location

        return $match;
    }


    /**
     * @param array|CustomRequest $input
     *
     * @return void
     */
    public function set_filters( $input = [] ) {
        if ( $input instanceof CustomRequest ) {
            $input = $input->toFilters();
        }
        if ( ! is_array( $input ) ) {
            throw new \InvalidArgumentException( 'Filters must be array or CustomRequest' );
        }
        $this->validate_filters( $input );
    }

    private function get_resort_id_from_name( string $resortname ): ?int {
        $resort = $this->find_resort( $resortname );

        return $resort ? (int) $resort['id'] : null;
    }

    public function find_resort( string $resortname ): ?array {
        global $wpdb;

        $sql = $wpdb->prepare( "SELECT id,gpxRegionID,LatitudeLongitude, latitude, longitude as region_id FROM wp_resorts WHERE ResortName = %s",
                               $resortname );

        return $wpdb->get_row( $sql, ARRAY_A );
    }


    public function find_region( string $regionname ): ?array {
        global $wpdb;

        // region as country?
        $sql      = $wpdb->prepare( "SELECT countryID from wp_gpxCategory WHERE country='%s' && CountryID < 1000",
                                    $regionname );
        $category = $wpdb->get_row( $sql );
        if ( $category ) {
            $sql = $wpdb->prepare( "SELECT a.id, a.lft, a.rght FROM wp_gpxRegion a
                    INNER JOIN wp_daeRegion b ON a.RegionID=b.id
                    WHERE b.CategoryID=%s",
                                   $category->countryID );
        } else {
            $sql = $wpdb->prepare( "SELECT id, lft, rght FROM wp_gpxRegion WHERE name='%s' OR subName='%s' OR displayName='%s'",
                                   [ $regionname, $regionname, $regionname ] );
        }

        return $wpdb->get_row( $sql, ARRAY_A );
    }

    /**
     * @param int[]|int $resortid
     *
     * @return void
     */
    private function find_inventory_by_resort( $resortid ) {
        global $wpdb;
        $resorts = implode( ',', array_filter( array_map( 'intval', Arr::wrap( $resortid ) ) ) );

        $resortTypeWhere = $this->build_resort_type_where();
        $roomTypeWhere   = $this->build_room_type_where();

        $sql = $wpdb->prepare( "SELECT
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
                               date( 'Y-m-d', strtotime( $this->filters['checkIn'] ) ),
                               date( 'Y-m-d', strtotime( $this->filters['checkIn2'] ) )
        );
        $weeks         = collect( $wpdb->get_results( $sql, ARRAY_A ) )->keyBy( 'weekId' );
        $this->results = $this->results->merge( $weeks );
    }

    /**
     * @return string
     */
    private function build_resort_type_where() {
        //  1  exchange
        //  2  rental
        //  3  both
        $rtWhere = "";
        // there is a resort type preference set and it's not "Any"
        if ( isset( $this->filters['preference'] ) && ! empty( $this->filters['preference'] ) && $this->filters['preference'] != 'Any' ) {
            if ( $this->filters['preference'] == 'Exchange' ) {
                $rtWhere = " AND type IN ('3','1') ";
            } else {
                $rtWhere = " AND type IN ('3','2') ";
            }
        }

        return $rtWhere;
    }


    /**
     * @return string
     *
     *   AND a.unit_type IN (".implode(',', '".$this->roomSizes."').")
     */
    private function build_room_type_where() {
        global $wpdb;
        if ( isset( $this->filters['roomType'] ) and $this->filters['roomType'] != 'Any' ) {
            $placeholders = gpx_db_placeholders( $this->roomSizes );

            return $wpdb->prepare( " AND c.number_of_bedrooms IN {$placeholders} ", $this->roomSizes );
        }

        return '';
    }


    /**
     * find inventory nearby a resort
     *
     * @return void
     */
    private function find_inventory_nearby() {
        global $wpdb;

        if ( empty( $this->filters['resort'] ) ) {
            return;
        }

        $resort = $this->find_resort( $this->filters['resort'] );
        if ( ! $resort ) {
            // requested resort was not found
            return;
        }
        $latitude  = $resort['latitude'];
        $longitude = $resort['longitude'];
        if ( null === $latitude || null === $longitude ) {
            if ( empty( $resort['LatitudeLongitude'] ) ) {
                return;
            }
            [ $latitude, $longitude ] = explode( ',', $resort['LatitudeLongitude'] );
        }

        // find other resorts nearby
        $distance = $wpdb->prepare( "ST_Distance_Sphere(ST_GeomFromText('POINT(%f %f)'), ST_GeomFromText(CONCAT('POINT(',`longitude`,' ',`latitude`,')')))",
                                    [ $longitude, $latitude ] );
        $sql      = $wpdb->prepare( "SELECT
            `id`, {$distance} as 'distance'
        FROM `wp_resorts`
        WHERE `id` != %d AND `latitude` IS NOT NULL AND `longitude` IS NOT NULL AND {$distance} <= %d
        ORDER BY distance asc
        ",
                                    $resort['id'],
                                    $this->filters['miles'] * 1609.344 /* miles to meters */
        );
        $resorts  = $wpdb->get_col( $sql );
        if ( ! $resorts ) {
            return;
        }
        $this->find_inventory_by_resort( $resorts );
    }

    /**
     * @param $regionid
     *
     * @return void
     */
    private function find_inventory_by_region( $regionname = null ) {
        global $wpdb;

        $resortTypeWhere = $this->build_resort_type_where();
        $roomTypeWhere   = $this->build_room_type_where();

        // use param or filter?
        $theregion = $regionname ?? $this->filters['region'];
        if ( $theregion == null ) {
            return;
        }

        $region = $this->find_region( $theregion );
        if ( ! $region ) {
            return;
        }

        //get all the regions
        $sql = $wpdb->prepare( "SELECT id FROM wp_gpxRegion WHERE lft BETWEEN %d AND %d ORDER BY lft ASC",
                               [ $region['lft'], $region['rght'] ] );
        $ids = $wpdb->get_col( $sql );
        if ( ! $ids ) {
            return;
        }

        // ok, we found regions
        $sql = $wpdb->prepare( "SELECT
                    a.record_id as weekId,
                    a.resort as resort_id,
                    b.GPXRegionID as region_id
                FROM wp_room a
                INNER JOIN wp_resorts b ON a.resort=b.id
                INNER JOIN wp_unit_type c ON a.unit_type=c.record_id
                    WHERE b.GPXRegionID IN (" . implode( ',', array_map( 'intval', $ids ) ) . ")
                    AND check_in_date BETWEEN %s AND %s
                    $resortTypeWhere
                    $roomTypeWhere
                    AND a.active=1
                    AND b.active=1",
                               date( 'Y-m-d', strtotime( $this->filters['checkIn'] ) ),
                               date( 'Y-m-d', strtotime( $this->filters['checkIn2'] ) )
        );
        // get properties
        $props = collect( $wpdb->get_results( $sql, ARRAY_A ) )->keyBy( 'weekId' );
        $this->results = $this->results->merge( $props );
    }


    /**
     * @todo remove this method after the database is normalized
     *
     *  this is a temporary method until the database is normalized, then it can be removed
     *  use this to map the bad data to the actual values.
     *
     * we only need to do this if the roomsize is outside of the Room::roomSizes
     */
    private function normalize_room_size( $size ) {
        if ( Room::get_room_sizes() != $size ) {
            switch ( $size ) {
                case 'HR':
                case 'HDLX':
                    $size = 'STD';
                    break;
                case '1BMINI':
                case '1B DLX':
                case '1BTWN':
                case '1B OCN':
                    $size = '1';
                    break;
                case '2r':
                case '2BLOFT':
                case '2B VIL':
                case '2B':
                case '2BCAB':
                    $size = '2';
                    break;
                case '4':
                    $size = '3';
                    break;
            }
        }

        return $size;
    }


    /**
     * @return void
     *
     * takes the existing room sizes and adds the rest of the potential sizes to the filter array.
     * This function is only needed while the data is not normalized.
     *
     */
    private function expand_room_sizes() {
        $additional_sizes = [];
        foreach ( $this->roomSizes as $size ) {
            switch ( $size ) {
                case 'STD':
                    $additional_sizes[] = 'HR';
                    $additional_sizes[] = 'HDLX';
                    break;
                case '1':
                    $additional_sizes[] = '1BMINI';
                    $additional_sizes[] = '1B DLX';
                    $additional_sizes[] = '1BTWN';
                    $additional_sizes[] = '1B OCN';
                    $additional_sizes[] = '1BR';
                    break;
                case '2':
                    $additional_sizes[] = '2r';
                    $additional_sizes[] = '2BLOFT';
                    $additional_sizes[] = '2B';
                    $additional_sizes[] = '2B VIL';
                    $additional_sizes[] = '2BCAB';
                    $additional_sizes[] = '2BR';
                    break;
                case '3':
                    $additional_sizes[] = '4';
                    break;
            }
        }
        // add the additional room sizes onto the existing $this->>roomSizes
        $this->roomSizes = $this->roomSizes + $additional_sizes;
    }

    /**
     * checks for valid input and stores the inputs in $this->filters
     */
    private function validate_filters( array $input = [] ) {
        $input = array_merge( $this->filters, array_intersect_key( $input, $this->filters ) );
        $input = filter_var_array( $input, [
            'adults'     => [
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => [ 'default' => 0, 'min_range' => 0 ],
            ],
            'children'   => [
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => [ 'default' => 0, 'min_range' => 0 ],
            ],
            'checkIn'    => [
                'filter'  => FILTER_VALIDATE_REGEXP,
                'flags'   => FILTER_REQUIRE_SCALAR | FILTER_NULL_ON_FAILURE,
                'options' => [ 'regexp' => "/^\d{2}\/\d{2}\/\d{4}$/" ],
            ],
            'checkIn2'   => [
                'filter'  => FILTER_VALIDATE_REGEXP,
                'flags'   => FILTER_REQUIRE_SCALAR | FILTER_NULL_ON_FAILURE,
                'options' => [ 'regexp' => "/^\d{2}\/\d{2}\/\d{4}$/" ],
            ],
            'roomType'   => [
                'filter'  => FILTER_VALIDATE_REGEXP,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => [ 'default' => 'Any', 'regexp' => "/^Any|Studio|1BR|2BR|3BR$/" ],
            ],
            'larger'     => [
                'filter'  => FILTER_VALIDATE_BOOLEAN,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => [ 'default' => false ],
            ],
            'preference' => [
                'filter'  => FILTER_VALIDATE_REGEXP,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => [ 'default' => 'Any', 'regexp' => "/^Any|Rental|Exchange$/" ],
            ],
            'nearby'     => [
                'filter'  => FILTER_VALIDATE_BOOLEAN,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => [ 'default' => false ],
            ],
            'miles'      => [
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => [ 'default' => 30, 'min_range' => 0 ],
            ],
            'region'     => [
                'filter' => FILTER_DEFAULT,
                'flags'  => FILTER_REQUIRE_SCALAR,
            ],
            'city'       => [
                'filter' => FILTER_DEFAULT,
                'flags'  => FILTER_REQUIRE_SCALAR,
            ],
            'resort'     => [
                'filter' => FILTER_DEFAULT,
                'flags'  => FILTER_REQUIRE_SCALAR,
            ],
        ],                         true );

        // @todo confirm there are at least 1 adult
        // @todo confirm there is at least a checkin date
        // @todo confirm there is either a region or a resort selected
        // check and set the region / city
        if ( ! empty( $input['city'] ) ) {
            $input['region'] = $input['city'];
        }

        // if the check-out date is not set, then set as check in date + 6 days (1 week)
        if ( ! $input['checkIn2'] ) {
            $input['checkIn2'] = date( 'm/d/Y', strtotime( $input['checkIn'] . ' + 6 days' ) );
        }

        if ( ! $input['miles'] ) {
            $input['miles'] = 30;
        }
        $this->filters = $input;
    }

    /**
     * @return void
     *
     *   STD, 1, 2, 3
     *
     *   build a list of rooms size that will be used in the search based on user input
     */
    private function determine_room_sizes_to_search() {
        //if the roomtype is 'any' than just include all
        $this->roomSizes = Room::get_room_sizes();

        // a room size was specified
        // build the array of rooms to include in search
        if ( $this->filters['roomType'] != 'Any' ) {
            $minimum_room_size_found = false;
            foreach ( Room::get_room_sizes() as $key => $value ) {
                if ( $this->filters['roomType'] != $value ) {
                    // if smaller than min room size OR larger than room size, but they don't want larger rooms - remove it from the search list
                    if ( ! $minimum_room_size_found || ( $minimum_room_size_found && ! $this->filters['larger'] ) ) {
                        unset( $this->roomSizes[ $key ] );
                    }
                } else {
                    $minimum_room_size_found = true;
                }
            }
        }
        // add the rest of unnormalized data to the array
        $this->expand_room_sizes();
    }
}
