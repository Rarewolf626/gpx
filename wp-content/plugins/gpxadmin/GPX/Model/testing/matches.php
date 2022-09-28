<?php

require( '../../../../../../wp-load.php' );

use GPX\Model\CustomRequestMatch;
use GPX\Repository\RegionRepository;


echo "start";
echo "<pre>";

/*
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



$filters = array(   'adults' => 2, 'children' => 0,  // occupancy
    'CheckIn'=>'09/11/2022','checkIn2'=>null,  // check in and check out dates
    'roomType'=>'2',      // size of room requested
    'larger'=>1,            // look for larger rooms
    'preference'=>'Any',  // exchange/rental/Both
    'nearby'=>1, // search nearby resorts
    'miles'=>70,      // miles search radius
    'region'=>null,   // a city was selected
    'resort'=>'Atlantic Beach Resort'  // a specific resort was selected
) ;

*/
$filters = [
    'adults'     => 2,
    'children'   => 0,  // occupancy
    'checkIn'    => '07/29/2022',
    'checkIn2'   => null,  // check in and check out dates
    'roomType'   => '2',      // size of room requested
    'larger'     => 1,            // look for larger rooms
    'preference' => 'Any',  // exchange/rental/Both
    'nearby'     => 1, // search nearby resorts
    'miles'      => CustomRequestMatch::MILES,      // miles search radius
    'city'     => 'Las Vegas',   // a city was selected
    'resort'     => 'Jockey Club'  // a specific resort was selected
];


$cdmObj = new CustomRequestMatch($filters);
$data = $cdmObj->get_matches();

print_r($data->toArray());

/*
$regionid = RegionRepository::instance()->get_region_id('Atlantic Beach');
echo $regionid;
*/

echo "</pre>";
echo "end";
