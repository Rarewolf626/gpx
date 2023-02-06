<?php

namespace GPX\Repository;

use GPX\Model\Week;

class WeekRepository {

    public static function instance(): WeekRepository {
        return gpx( WeekRepository::class );
    }

    public function get_week( $id ) {
        return Week::with( 'unit' )->find( $id );
    }

    public function get_weeks( array $week_ids = [] ): array {
        global $wpdb;
        $week_ids = array_filter( array_map( fn( $id ) => (int) $id, $week_ids ) );
        if ( empty( $week_ids ) ) {
            return [];
        }
        $placeholders = gpx_db_placeholders( $week_ids, '%d' );
        $sql          = $wpdb->prepare( "SELECT
                `a`.`record_id` AS `id`, `a`.`check_in_date` AS `checkIn`, `a`.`check_out_date` AS `checkOut`, `a`.`price` AS `Price`,
                `a`.`record_id` AS `weekID`, `a`.`record_id` AS `weekId`, `a`.`resort` AS `resortId`, `a`.`resort` AS `resortID`,
                `a`.`availability` AS `StockDisplay`, `a`.`type` AS `WeekType`, DATEDIFF(`a`.`check_out_date`, `a`.`check_in_date`) AS `noNights`,
                `a`.`active_rental_push_date` AS `active_rental_push_date`,
                `b`.`Country` AS `Country`, `b`.`Region` AS `Region`, `b`.`Town` AS `Town`, `b`.`ResortName` AS `ResortName`,
                `b`.`ImagePath1` AS `ImagePath1`, `b`.`AlertNote` AS `AlertNote`, `b`.`AdditionalInfo` AS `AdditionalInfo`,
                `b`.`HTMLAlertNotes` AS `HTMLAlertNotes`, `b`.`ResortID` AS `ResortID`, `b`.`taxMethod` AS `taxMethod`,
                `b`.`taxID` AS `taxID`, `b`.`gpxRegionID` AS `gpxRegionID`,
                `c`.`number_of_bedrooms` AS `bedrooms`, `c`.`sleeps_total` AS `sleeps`, `c`.`name` AS `Size`,
                `a`.`record_id` AS `PID`, `b`.`id` AS `RID`
            FROM `wp_room` AS `a`
            INNER JOIN `wp_resorts` AS `b` ON `a`.`resort` = `b`.`id`
            INNER JOIN `wp_unit_type` AS `c` ON `a`.`unit_type` = `c`.`record_id`
            WHERE a.record_id IN ({$placeholders}) AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1",
                                        $week_ids );

        return $wpdb->get_results( $sql );
    }

}
