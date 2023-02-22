<?php

namespace GPX\Model;

class Room {

    /**
     * @var \string[][]
     *
     * This is way more complex that needed.
     *
     * @todo normalize the database so we don't need to use this and can just use the self::roomSizes
     */
    private static $roomTypes = array(
        'Studio' => array(
            'St',
            'STD',
            'HR',
            'Spa',
            'HSUP',
            'HDLX',
            'STSO',
            'STTENT',
            'YACT',
        ),
        '1BR' => array(
            '1',
            '1b',
            '1B VIL',
            '1B OCN',
            '1BDLX',
            '1B DLX',
            '1BTWN',
            '1B GDN',
            '1BMINI',
        ),
        '2BR' => array(
            '2',
            '2r',
            '2B',
            '2b',
            '2B VIL',
            '2BLOFT',
            '2B DLX',
            '2BCAB',
            '2B OCN',
        ),
        '3BR' => array(
            '3',
            '3b',
            '3B VIL'
        ),
        '4BR' => array(
            '4',
            '4b',
        ),
    );

    private static $roomSizes = array('STD','1','2','3');


    private static $roomOccupancy = array(
        'Studio'=>array(
            'min'=>'1',
            'max'=>'2'
        ),
        '1BR'=>array(
            'min'=>'4',
            'max'=>'15'
        ),
        '2BR'=>array(
            'min'=>'6',
            'max'=>'15'
        ),
        '3BR'=>array(
            'min'=>'6',
            'max'=>'15'
        ),
        'Any'=>array(
            'min'=>'1',
            'max'=>'15'
        ),
    );

    /**
     * @return \string[][]
     */
    public static function get_room_types (){
        return self::$roomTypes;
    }

    /**
     * @return \string[][]
     */
    public static function get_room_occupancy () {
        return self::$roomOccupancy;
    }

    /**
     * @return string[]
     */
    public static function get_room_sizes() {
        return self::$roomSizes;
    }
}
