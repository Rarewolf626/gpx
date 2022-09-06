<?php

require_once( dirname( __DIR__, 5 ) . DIRECTORY_SEPARATOR . 'wp-load.php' );

use GPX\Api\GoogleMap;
use GPX\Repository\ResortRepository;

// select resorts where no lat/lng
// SELECT * FROM wp_resorts where LatitudeLongitude='';

// ALTER TABLE `wp_resorts` ADD `latitude` double(11,7) NULL AFTER `LatitudeLongitude`, ADD `longitude` double(11,7) NULL AFTER `latitude`;

$sql = "SELECT id,Address1,Address2,Town,Region,Country, PostCode,LatitudeLongitude
        FROM wp_resorts
        WHERE (LatitudeLongitude='' OR `latitude` IS NULL OR `longitude` IS NULL)
        AND active =1
        AND geocode_status IS NULL
        LIMIT 10";

$rows = $wpdb->get_results( $sql );

$mapObj = new GoogleMap();

foreach ( $rows as $row ) {
    if ( $row->LatitudeLongitude ) {
        $cords         = explode( ',', $row->LatitudeLongitude );
        $location      = new stdClass();
        $location->lat = $cords[0];
        $location->lng = $cords[1];
    } else {
        $address = $row->Address1 . ' ' . $row->Address2 . ' ' . $row->Town . ' ' . $row->Region . ' ' . $row->PostCode . ' ' . $row->Country;

        $location = GoogleMap::instance()->geocode( $address );
    }

    if ( $location ) {
        ResortRepository::instance()->save_geodata( $row->id, $location );
    } else {
        ResortRepository::instance()->save_geodata_error( $row->id );
    }
}

//  ADD COLUMN `geocode_status` INT NULL AFTER `active`;




