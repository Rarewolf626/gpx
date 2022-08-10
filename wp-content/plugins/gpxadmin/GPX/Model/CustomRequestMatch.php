<?php
use GPX\Model\Room;
use GPX\Repository\RegionRepository;

namespace GPX\Model;

class CustomRequestMatch
{

    private $filters = array(   'adults' => 0, 'children' => 0,  // occupancy
                                'CheckIn'=>null,'checkIn2'=>null,  // check in and check out dates
                                'roomType'=>'Any',      // size of room requested
                                'larger'=>0,            // look for larger rooms
                                'preference'=>'Any',  // exchange/rental/Both
                                'nearby'=>null, // search nearby resorts
                                'miles'=>0,      // miles search radius
                                'region'=>null,   // a city was selected
                                'resort'=>null  // a specific resort was selected
                                    ) ;

    private $roomSizes = array();  // array of room sizes to search

    private $results = array();    // the resulting rooms matching criteria


    /**
     * @todo what happens if they try to book in advance memorial day next year?
     *
     */
    public function __construct(){
        $this->filters['miles'] = 30;
    }


    /**
     * @param $regionid
     * @return bool
     */
    private function is_restricted($regionid) {
        // base the holiday year on the year of the vacation
        // check the year of the check in-date
        $year = date('Y',strtotime($this->filters['CheckIn']));

        $restrictionStart = strtotime("June 1, $year");
        $restrictionEnd = strtotime("September 1,  $year");

        // check if the checkIn and CheckIn2 (checkOut) dates are between
        $restrictedCheck = false;

        $restrictedRegions = $this->gpx_get_restricted_regions();

        //check if the data is within a restricted time and it's in a restricted region
        if(     ($restrictionStart <= strtotime($this->filters['checkIn'])
            AND strtotime($this->filters['checkIn']) <= $restrictionEnd)
            AND in_array($restrictedRegions,$regionid)
        ) {
            $restrictedCheck = true;
        }
        return  $restrictedCheck;
    }

    /**
     * @return mixed
     */
    private function gpx_get_restricted_regions()
    {
        static $regions;
        if(!$regions){
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
    */
    public function get_matches($input) {

        // validate input
        $this->validate_filters($input);

        // build an array of $this->roomSizes to search
        $this->determine_room_sizes_to_search();

        // if resort property selected, then find inventory in that property
        if(isset($this->filters['resort']) && !empty($this->filters['resort'])) {  //search by resort
           $resortid = $this->get_resort_id_from_name($this->filters['resort']);
           $this->find_inventory_by_resort($resortid);
        }

        // if nearby selected, then use miles from preferred property and add to result properties
        // nearby requires -  nearby, miles, and resort
        if((isset($this->filters['miles']) && $this->filters['miles'] != 0) || ( (isset($this->filters['nearby']) && $this->filters['nearby'] == '1') && (isset($this->filters['resort']) && !empty($this->filters['resort'])) ) ) {
           $this->find_inventory_nearby();
        }

        // if region/city selected, then find properties in that location and add to result properties
        if ((isset($this->filters['region']) && $this->filters['region'] != '')) {
            $this->find_inventory_by_region();
        }

        // return the result set
        return $this->results;
    }

    /**
     * @param int $id
     * @return int|null
     */
    private function get_resort_id_from_name($resortname){
        global $wpdb;

        $sql = $wpdb->prepare("SELECT id FROM wp_resorts WHERE ResortName LIKE %s ",$wpdb->esc_like($resortname));
        $row = $wpdb->get_row($sql);
        return $row->id ?? null;
    }

    /**
     * @return void
     *
     *
     */
    private function find_inventory_by_resort(int $resortid) {

        global $wpdb;

        $resortTypeWhere = $this->build_resort_type_where();
        $roomTypeWhere = $this->build_room_type_where();

        $sql = $wpdb->prepare("SELECT
                a.record_id as weekId
            FROM wp_room a
            INNER JOIN wp_resorts b ON a.resort=b.id
            INNER JOIN wp_unit_type c ON a.unit_type=c.record_id
                WHERE a.resort = %d
                AND check_in_date BETWEEN %s AND %s
                $resortTypeWhere
                $roomTypeWhere
                AND a.active=1
                AND b.active=1",$resortid,$this->filters['CheckIn'],$this->filters['checkIn2']);

          // get properties
          $props = $wpdb->get_results($sql);
          if (count($props) > 0) {
              array_push ($this->results, $props);
          }
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
        if (isset($this->filters['preference']) && !empty($this->filters['preference']) && $this->filters['preference'] != 'Any'){

            if($this->filters['preference'] == 'Exchange') {
                $rtWhere = " AND type IN ('3','1') ";
            } else {
                $rtWhere = " AND type IN ('3','2') ";
            }
        }
        return $rtWhere;
    }


    /**
     * @return void
     *
     *   AND a.unit_type IN (".implode(',', '".$this->roomSizes."').")
     */
    private function build_room_type_where() {
        $rtWhere = "";
        if (isset($this->filters['roomType']) AND  $this->filters['roomType'] != 'Any') {
           $rtWhere = " AND a.unit_type IN ('".implode("','", $this->roomSizes)."') ";
        }
        return $rtWhere;
    }


    /**
     * find inventory nearby a resort
     *
     * @return void
     */
    private function find_inventory_nearby() {

        global $wpdb;

        // get the name of the region for the filter selected resort
        $sql = $wpdb->prepare("SELECT a.name FROM wp_gpxRegion a
                                        INNER JOIN wp_resorts b on b.gpxRegionID=a.id
                                        WHERE ResortName LIKE %s", $wpdb->esc_like($this->filters['resort']));
        $nearby = $wpdb->get_row($sql);
        $region = $nearby->name;

        //get the lat/long coordinates of the selected city/region
        $sql = $wpdb->prepare("SELECT lng, lat FROM wp_gpxRegion WHERE (`name`=%s OR `displayName`=%s)", [$region, $region]);
        $row = $wpdb->get_row($sql);

        $ids = array();
        // found the lat for the $region
        // now find all the regions within a radius of those co-ords
        if(!empty($row) && $row->lat != '0') {
            $sql = $wpdb->prepare("SELECT
                    `id`,
                    (
                        3959 *
                        acos(
                            cos( radians( %d ) ) *
                            cos( radians( `lat` ) ) *
                            cos(
                                radians( `lng` ) - radians( %d )
                            ) +
                            sin(radians(%d)) *
                            sin(radians(`lat`))
                        )
                    ) `distance`
                FROM
                    `wp_gpxRegion`
                HAVING
                    `distance` < %d",[$row->lat, $row->lng, $row->lat, $this->filters['miles']]);
            $regions = $wpdb->get_results($sql);

            foreach($regions as $region) {
                $ids[] = $region->id;
            }
        }
        // loop through the regions and add to the result
        foreach ($ids as $id) {
            $this->find_inventory_by_region($id);
        }

    }

    /**
     * @param $regionid
     * @return void
     */
    private function find_inventory_by_region($regionid = null ) {

        global $wpdb;

        $resortTypeWhere = $this->build_resort_type_where();
        $roomTypeWhere = $this->build_room_type_where();
        $props = array();

        // use param or filter?
        $theregion = $regionid ?? $this->filters['region'];
        if ($theregion = null ) return;

        // region as country?
        $sql = $wpdb->prepare("SELECT countryID from wp_gpxCategory WHERE country=%s && CountryID < 1000", $theregion);
        $category = $wpdb->get_row($sql);
        if(!empty($category)) {
            $sql = $wpdb->prepare("SELECT a.id, a.lft, a.rght FROM wp_gpxRegion a
                    INNER JOIN wp_daeRegion b ON a.RegionID=b.id
                    WHERE b.CategoryID=%s", $category->id);
        } else {
            $sql = $wpdb->prepare("SELECT id, lft, rght FROM wp_gpxRegion WHERE name=%s OR subName=%s OR displayName=%s", [$theregion,$theregion,$theregion]);
        }
        $gpxRegions = $wpdb->get_results($sql);

        if(!empty($gpxRegions)) {
            // if gpxRegions are not empty then, ...
            foreach ($gpxRegions as $gpxRegion) {
                //get all the regions
                $sql = $wpdb->prepare("SELECT id FROM wp_gpxRegion
                    WHERE lft BETWEEN %d AND %d
                    ORDER BY lft ASC", [$gpxRegion->lft, $gpxRegion->rght]);
                $rows = $wpdb->get_results($sql);
            }

            foreach($rows as $row){
                if (!$this->is_restricted($row->id)){
                    $ids[] = $row->id;
                }
            }

            if((isset($ids) && !empty($ids)) ) {
                // ok, we found regions

                $sql = $wpdb->prepare("SELECT   a.record_id as weekId
                FROM wp_room a
                INNER JOIN wp_resorts b ON a.resort=b .id
                INNER JOIN wp_unit_type c ON a.unit_type=c.record_id
                    WHERE b.GPXRegionID IN (".implode(',', array_map('intval', $ids)).")
                    AND check_in_date BETWEEN %s AND %s
                    $resortTypeWhere
                    $roomTypeWhere
                    AND a.active=1
                    AND b.active=1",$this->filters['CheckIn'],$this->filters['checkIn2']);
                // get properties
                $props = $wpdb->get_results($sql);
            }
        }
        if (count($props) > 0) {
            array_merge ($this->results, $props);
        }

    }


    /**
     * @todo remove this method after the database is normalized
     *
     *  this is a temporary method until the database is normalized, then it can be removed
     *  use this to map the bad data to the actual values.
     *
     * we only need to do this if the roomsize is outside of the Room::roomSizes
     */
    private function normalize_room_size ($size) {

        if (Room::get_room_sizes() != $size ) {
            switch ($size) {
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

        $additional_sizes = array();
        foreach ($this->roomSizes as $size) {
            switch ($size){
                case 'STD':
                    $additional_sizes[]='HR';
                    $additional_sizes[]='HDLX';
                    break;
                case '1':
                    $additional_sizes[]='1BMINI';
                    $additional_sizes[]='1B DLX';
                    $additional_sizes[]='1BTWN';
                    $additional_sizes[]='1B OCN';
                    break;
                case '2':
                    $additional_sizes[]='2r';
                    $additional_sizes[]='2BLOFT';
                    $additional_sizes[]='2B';
                    $additional_sizes[]='2B VIL';
                    $additional_sizes[]='2BCAB';
                    break;
                case '3':
                    $additional_sizes[]='4';
                    break;
            }
        }
        // add the additional room sizes onto the existing $this->>roomSizes
        $this->roomSizes = $this->roomSizes + $additional_sizes;
    }

    /**
     * @param $input
     * @return void
     */
    public function set_filters($input){
        $this->validate_filters($input);
    }

    /**
     * @param $input
     * @return mixed
     *
     * checks for valid input and stores the inputs in $this->filters
     */
    private function validate_filters($input){

        // make sure the roomType is submitted
        if(!isset($input['roomType']) || empty($input['roomType']))  $input['roomType'] = 'Any';

        // @todo confirm there are at least 1 adult
        // @todo confirm there is at least a checkin date
        // @todo confirm there is either a region or a resort selected

        // place each of the input values into $this->filters
        // loose everything else in the input not included in $this->filters
        foreach ($this->filters as $key => $value) {
            $this->filters[$key] = $input[$key];
        }

        // check and set the region / city
        if (isset($input['ciy']) && $input['city'] != '') $this->filters['region'] = $input['city'];

        // if the check-out date is not set, then set as check in date + 6 days (1 week)
        if ($this->filters['checkIn2'] == null) {
            $this->filters['checkIn2'] = date('m/d/Y',strtotime($this->filters['CheckIn'] .' + 6 days'));
        }

    }

    /**
     * @return void]
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
        if ($this->filters['roomType'] != 'Any') {

            $minimum_room_size_found = false;
            foreach (Room::get_room_sizes() as $key => $value) {

                if ($this->filters['roomType'] != $value) {
                    // if smaller than min room size OR larger than room size, but they don't want larger rooms - remove it from the search list
                    if (!$minimum_room_size_found || ($minimum_room_size_found && !$this->filters['larger'] ) )  unset($this->roomSizes[$key]);
                } else {
                    $minimum_room_size_found = true;
                }
            }
        }
        // add the rest of unnormalized data to the array
        $this->expand_room_sizes();
    }

}
