<?php

namespace GPX\Model;

class Room {

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
            '3',
            '4',
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
            '4',
            '3b',
            '4b',
            '3B VIL'
        ),
    );

    public static function get_room_types (){

        return self::$roomTypes;

    }


}
