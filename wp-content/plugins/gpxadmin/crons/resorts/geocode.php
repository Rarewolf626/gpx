<?php

require('../../../../../wp-load.php');
Use GPX\Api\GoogleMap;
Use GPX\Repository\ResortRepository;

// select resorts where no lat/lng
// SELECT * FROM wp_resorts where LatitudeLongitude='';

$sql = "SELECT id,Address1,Address2,Town,Region,Country, PostCode,LatitudeLongitude
        FROM wp_resorts
        WHERE LatitudeLongitude=''
        AND active =1
        AND geocode_status IS NULL
        LIMIT 10";

$rows = $wpdb->get_results($sql);

$mapObj = new GoogleMap();

foreach ($rows as $row) {
    $address = $row->Address1.' '.$row->Address2.' '.$row->Town. ' ' . $row->Region. ' ' . $row->PostCode. ' ' . $row->Country;

    $location = GoogleMap::instance()->geocode($address);
/*
    echo $address;
    echo '<br / >';
    echo 'location : '. $location->lat.','.$location->lng;
    echo '<hr / >';
*/

    if ($location) {
        ResortRepository::instance()->save_geodata($row->id, $location);
    } else {
        ResortRepository::instance()->save_geodata_error($row->id);
    }



}


//  ADD COLUMN `geocode_status` INT NULL AFTER `active`;




