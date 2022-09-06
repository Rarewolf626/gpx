<?php

namespace GPX\Model;

use GPX\Repository\WeekRepository;

class Week
{

    public $record_id;
    public $create_date;
    public $active_specific_date;
    public $last_modified_date;
    public $check_in_date;
    public $check_out_date;
    public $sourced_by_partner;
    public $resort_id;
    public $unit_type;
    public $active_rental_push_date;
    public $active;
    public $availablity;
    public $available_to_partner;
    public $price;

    public $unit;  //array of units


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

        $week->unit = self::get_unit_type($week->resort_id);

        return $week;
    }

    private static function get_unit_type($id){
        return  UnitType::where('resort_id','=',$id)->first();
    }

    private static function get_resort($id) {



    }
}
