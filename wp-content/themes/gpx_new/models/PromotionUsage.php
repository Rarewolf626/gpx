<?php

/**
 * Promotion Usage
 *
 * object contains the usage for the promotion
 */
class PromotionUsage
{

    public $usagetype;   // any, region, resort, customer

    public $country;
    // gpx admin uses ALL as the first region, we'll ignore that and just store the region
    public $region;

    // array of resorts. only used when usage type resort is selected.
    protected array $resort = array();

    public function __construct (){}

/*
 * adds a resort onto the usage
 */
    public function addResort(){

    }



}
