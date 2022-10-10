<?php
namespace GPX\Model;

/**
 * can't use Eloquent model here, because resort has a meta table.
 */
//use Illuminate\Database\Eloquent\Model;

use GPX\Model\Address;

/**
 * Resort class
 *
 * data from this object comes from the tables - wp_resorts, wp_resorts_meta
 *
 *  requires classes - Address,ResortFee
 *
 */
class Resort
{
    /*
    protected $table = 'wp_xxx';
    protected $primaryKey = 'record_id';
    protected $guarded = [];

    protected $casts = [

        'user' => 'integer',
        'resort_id' => 'integer',
        'weekId' => 'integer',
        'create_date' => 'datetime',
        'last_modified_date' => 'datetime',
    ];

    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'last_modified_date';
*/
    /**
     * @var mixed
     */
    public int $rowID;
    public string $resortID;
    public bool $active;
    public string $gprID;
    public string $sfID;
    public string $endPointID;
    public string $resortName;
    public string $webLink;
    public string $alertNote;
    public Address $address;
    public string $email;
    public array $images = array();

    // resort meta
    protected array $facilities = array('unit'=>array(),'area'=>array(),'resort'=>array());
    public string $website;
    protected array $description = array('general'=>'','unit'=>'','area'=>'');
    public string $airport;
    public string $directions;
    protected array $checkInDays = array(); // array of days checkin allowed
    protected array $checkTime = array(
        'in-earliest' => '',
        'in-latest' => '',
        'out-earliest' => '',
        'out-latest' => '',
    );
    public string $additionalInfo;
    protected array $unitConfig = array();
    protected array $location = array('latitude' => '','longitude'=>'');
    public string $videoURL;
    public string $disabledNotes;
    public string $htmlAlert;
    public int $gpxRegionID;
    public int $taxMethod;
    public int $taxID;
    public $taID; // don't know what this is

    //flags
    public bool $featured = false;
    public bool $gpr = false;
    public bool $guestEnabled = true;
    public bool $store_d; // no idea what this is?
    public bool $ai; // again, no clue

    public string $holidayPropertyMessage; // must be a better way

    // Resort Fees
    // these resortFeeTypes are important. Must be submitted by key or will be rejected by the obj
    private array $resortFeeTypes = array('resortFees','RentalFeeAmount','CPOFeeAmount','GuestFeeAmount','UpgradeFeeAmount','SameResortExchangeFee');
    public ResortFee $resortFees;
    public ResortFee $rentalFee;
    public ResortFee $cpoFee;
    public ResortFee $guestFee;
    public ResortFee $upgradeFee;
    public ResortFee $sameResortExchange;

    // @deprecated
    // these are no longer used, but _may_ be needed
    protected array $imagePath = array();

/*
 * constructor
 * setup model data structure
 */
    public function __construct(){}

    /*
     * add an image to the image list
     */
    public function add_image($image):bool {
        $this->images[] = $image;
        // @todo make sure the image exists before adding
        // don't add any broken or missing images
        return true;
    }
/*
 * setters
 */

    public function set_address(array $address):bool {
        $this->address = Address::create($address);
        return true;
    }

    // only set the allow keys in - check property above
    public function set_facilities(string $type,array $facilities){
        if (array_key_exists($type,$this->facilities)){
            $this->facilities[$type]=$facilities;
        }
    }
    // only set the allow keys in - check property above
    public function set_description(string $type,array $description){
        if (array_key_exists($type,$this->description)){
            $this->description[$type]=$description;
        }
    }
    // only accept long/lat pair
    // assume latitude before longitude
    public function set_location(string $location){
      list($this->location['latitude'], $this->location['longitude']) = explode(',',$location,2);
    }

    public function set_checkInDays(string $days){
        $this->checkInDays = explode(" ",$days);
    }

    public function set_checkTime(string $key, string $time){
        // call function for each key, only accept allowed keys
        if (array_key_exists($key,$this->checkTime)){
            $this->description[$key]=$time;
        }
    }

    // param unitconfig string array from the DB
    public function set_unitConfig(array $unitConfig){
        $this->unitConfig = $unitConfig;
    }

    public function add_imagePath (string $image){
        $this->imagePath[] = $image;
    }
    /*
     *  adds a resort fee object to the resort
     */
    public function add_fee(string $feetype, ResortFee $fee ) {

        $success = true;
        // check the $feetype key is allowed
        switch ($feetype) {
            case 'resortFees':
                $this->resortFees = $fee;
                break;
            case 'RentalFeeAmount':
                $this->rentalFee = $fee;
                break;
            case 'CPOFeeAmount':
                $this->cpoFee = $fee;
                break;
            case 'GuestFeeAmount':
                $this->guestFee = $fee;
                break;
            case 'UpgradeFeeAmount':
                $this->upgradeFee = $fee;
                break;
            case 'SameResortExchangeFee':
                $this->sameResortExchange = $fee;
                break;
            default:
                $success = false;
            // invalid key - reject
        }
        return $success;
    }
/*
 * getters
 */

    public function get_location():string{
        // return as a string list, comma separated only if fully pair exist
        if ($this->location['latitude']!="" and $this->location['longitude']!="") {
            return $this->location['latitude'] . ',' . $this->location['longitude'];
        } else {
            return false;
        }
    }

    // @param json true if get as a string to store in DB
    // model locally stored as an array for interface
    // json = true json encoded
    // json = false returns array
    public function get_unitConfig(bool $json=false){
        if ($json) {
            return json_encode($this->unitConfig);
        } else {  // just give me the array
            return $this->unitConfig;
        }
    }
    // returns a string if $key set, otherwise returned full array
    public function get_description($key=null){
        if (array_key_exists($key,$this->description)) {
            return $this->description[$key];
        } else {
            return $this->description;
        }
    }
    // returns a string if $key set, otherwise returned full array
    public function get_facilities($key=null)
    {
        if (array_key_exists($key,$this->facilities)) {
            return $this->facilities[$key];
        } else {
            return $this->facilities;
        }
    }
    // returns a string if $key set, otherwise returned full array
    public function get_checkTime($key=null)
    {
        if (array_key_exists($key,$this->checkTime)) {
            return $this->checkTime[$key];
        } else {
            return $this->checkTime;
        }
    }

    public function get_by_sf_id () {

    }



}
