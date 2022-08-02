<?php

namespace GPX\Model\Reports;

class Filter
{

    public $resort = null;
    public $active = null;

    public $start_date = null;
    public $end_date = null;

    public $check_in = null;
    public $state = null;
    public $available = null;
    public $city = null;
    public $country = null;
    public $price = null;
    public $source = null;
    public $type = null;
    public $partnerid = null;
    public $release_on = null;

    public function __construct(){}

    public function dates($start, $end = null){

        // @todo implement checkdate()
        // if the start_date is not set, then start today
        $this->start_date = (isset($start)) ? $start : date('Y-m-d');
        // if the end date is not set, use today + 1 year
        $this->end_date = (isset($end)) ? $end : date('Y-m-d',strtotime($this->start_date.'+ 12 months'));

        // if the range is greater than 1 year limit the range to 1 year only
        $origin = date_create($this->start_date);
        $target = date_create($this->end_date);
        $interval = date_diff($origin, $target);
        if ( intval($interval->format('%a')) > 366) {
            $this->end_date = date('Y-m-d', strtotime($this->start_date.'+ 12 months'));
        }
    }
}
