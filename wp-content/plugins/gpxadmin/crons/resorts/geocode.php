<?php

require_once( dirname( __DIR__, 5 ) . DIRECTORY_SEPARATOR . 'wp-load.php' );

use GPX\Api\GoogleMap;
use GPX\Repository\ResortRepository;

// select resorts where no lat/lng
// SELECT * FROM wp_resorts where LatitudeLongitude='';

// ALTER TABLE `wp_resorts` ADD `latitude` double(11,7) NULL AFTER `LatitudeLongitude`, ADD `longitude` double(11,7) NULL AFTER `latitude`;

$sql = "SELECT id,Address1,Address2,Town,Region,Country, PostCode, LatitudeLongitude
        FROM wp_resorts
        WHERE active = 1 AND geocode_status IS NULL
        ";

$rows = $wpdb->get_results( $sql );

$mapObj     = GoogleMap::instance();
$repository = ResortRepository::instance();

foreach ( $rows as $row ) {
    $address = $row->Address1 . ' ' . $row->Address2 . ' ' . $row->Town . ', ' . $row->Region . ' ' . $row->PostCode . ', ' . $row->Country;
    echo 'RESORT: ' . $row->id . PHP_EOL;
    echo 'GEOCODE ADDRESS: ' . $address . PHP_EOL;
    try {
        $location = $mapObj->geocode( $address );
        echo 'RESULTS: ' . $location->lat . ', ' . $location->lng . PHP_EOL;
        $repository->save_geodata( $row->id, $location );
    } catch ( Exception $e ) {
        $repository->save_geodata_error( $row->id );
        echo 'ERROR: ' . PHP_EOL;
        echo $e->getMessage() . PHP_EOL;
    }
    echo PHP_EOL. PHP_EOL;
}

//  ADD COLUMN `geocode_status` INT NULL AFTER `active`;




