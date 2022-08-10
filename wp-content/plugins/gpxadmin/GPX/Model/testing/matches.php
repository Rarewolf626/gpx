<?php

require('../../../../../../wp-load.php');

use GPX\Model\CustomRequestMatch;


echo "start";
echo "<pre>";


$filters = array(   'adults' => 2, 'children' => 0,  // occupancy
    'CheckIn'=>'09/11/2022','checkIn2'=>null,  // check in and check out dates
    'roomType'=>'2',      // size of room requested
    'larger'=>0,            // look for larger rooms
    'preference'=>'Any',  // exchange/rental/Both
    'nearby'=>1, // search nearby resorts
    'miles'=>30,      // miles search radius
    'region'=>null,   // a city was selected
    'resort'=>'Atlantic Beach Resort'  // a specific resort was selected
) ;


$cdmObj = new CustomRequestMatch();
$data = $cdmObj->get_matches($filters);

print_r($data);


echo "</pre>";
echo "end";
