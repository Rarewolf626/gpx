<?php

namespace GPX\Repository;

class ResortRepository
{

    /**
     * @return RegionRepository
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function instance(): ResortRepository{
        return gpx(ResortRepository::class);
    }

    /**
     * @param int $id
     * @param Obj $location
     * @return void
     */
    public function save_geodata (int $id, $location) {
        global $wpdb;

        if (is_object($location) AND isset($location->lat) AND isset($location->lng)  ){
            $location_string = $location->lat . ','.$location->lng;
        } else  { return 'error'; }

        $sql = $wpdb->prepare("UPDATE wp_resorts
                                     SET `LatitudeLongitude` = %s, `latitude` = %f, `longitude` = %f, geocode_status = 1
                                     WHERE id  = %d ", $location_string, $location->lat, $location->lng, $id);
        $wpdb->query($sql);

    }


    public function save_geodata_error (int $id) {
        global $wpdb;

        $sql = $wpdb->prepare("UPDATE wp_resorts
                                     SET geocode_status = 0
                                     WHERE id  = %d ", $id);
        $wpdb->query($sql);

    }


}
