<?php

namespace GPX\Model;

use GPX\Repository\WeekRepository;
use GPX\Repository\UnitTypeRepository;

class Week
{

    protected $record_id;
    protected $create_date;
    protected $active_specific_date;
    protected $last_modified_date;
    protected $check_in_date;
    protected $check_out_date;
    protected $sourced_by_partner;
    protected $resort_id;
    protected $unit_type;
    protected $active_rental_push_date;
    protected $active;
    protected $availablity;
    protected $available_to_partner;
    protected $price;

    protected $units;  //array of units


    public static function get_week($id){

        $a_week = WeekRepository::where('record_id','=',$id)->first();
        if(!$a_week) return null;
        $week = new static();
        $week->record_id = $a_week->record_id;
        $week->create_date = $a_week->create_date;
        $week->active_specific_date = $a_week->active_specific_date;
        $week->last_modified_date = $a_week->last_modified_date;
        $week->check_in_date = $a_week->check_in_date;
        $week->check_out_date = $a_week->check_out_date;
        $week->sourced_by_partner = $a_week->sourced_by_partner;
        $week->active_rental_push_date = $a_week->active_rental_push_date;
        $week->resort_id = $a_week->resort;
        $week->active = $a_week->active;
        $week->availablity = $a_week->availablity;
        $week->available_to_partner = $a_week->available_to_partner;
        $week->price = $a_week->price;

        $week->units = self::get_unit_types($week->resort_id);

        return $week;
    }

    private static function get_unit_types($id){
        return  UnitTypeRepository::where('resort_id','=',$id)->get();
    }

    private static function get_resort($id) {



    }
}
