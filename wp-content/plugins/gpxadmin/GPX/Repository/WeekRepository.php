<?php

namespace GPX\Repository;

use GPX\Model\Week;

class WeekRepository
{

    public static function instance(): WeekRepository{
        return gpx(WeekRepository::class);
    }

    public function get_week($id){
        return Week::with('unit')->find($id);
    }

    private static function get_resort($id) {



    }


    /*
    *  I don't understand the purpose of base64 encoding the entire row
     * and sticking it into another array.
     * Also, don't know the significance of the key
    */
    public function get_details() {
       $key =  array_key_first($this->update_details);
       return  base64_decode($this->update_details[$key]['details']);
    }


}
