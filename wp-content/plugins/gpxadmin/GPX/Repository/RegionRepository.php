<?php

namespace GPX\Repository;

class RegionRepository {
    public static function instance(): RegionRepository{
        return gpx(RegionRepository::class);
    }

    public function tree( int $region_id ) {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT a.*
        FROM `wp_gpxRegion` a
        INNER JOIN wp_gpxRegion p ON (p.id = %d)
        WHERE (a.name != 'All') AND (a.`lft` >= p.`lft`) AND (a.`rght` <= p.`rght`)
        ORDER BY a.`lft` ASC", $region_id);
        return $wpdb->get_results($sql);
    }

    public function restricted(): array {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT a.id
        FROM `wp_gpxRegion` a
        INNER JOIN wp_gpxRegion p ON (p.name = 'Southern Coast (California)')
        WHERE a.`lft` BETWEEN p.`lft` AND p.`rght`
        ORDER BY a.`lft` ASC");
        $regions = array_column($wpdb->get_results($sql, ARRAY_A), 'id');
        return array_combine($regions, $regions);
    }

    /**
     * @param $regionname
     * @return mixed
     */
    public function get_region_id($regionname) {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT id
                                     FROM `wp_gpxRegion`
                                     WHERE `name` = '%s'", $regionname);
        $region = $wpdb->get_results($sql);
        return $region[0]->id;
    }

    /**
     * @param $regionid
     * @return mixed
     */
    public function get_region_name($regionid) {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT name
                                     FROM `wp_gpxRegion`
                                     WHERE id = %d", $regionid);
        $region = $wpdb->get_results($sql);
        return $region[0]->name;
    }

}
