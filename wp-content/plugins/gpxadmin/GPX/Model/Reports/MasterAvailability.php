<?php

namespace GPX\Model\Reports;

use Illuminate\Support\Arr;
use GPX\Model\Reports\Filter;
use stdClass;

class MasterAvailability {
    public Filter $filter;

    public function __construct( ?Filter $filter = null ) {
        $this->filter = $filter ?? new Filter();
    }

    public function run(): array {
        // get the base inventory data
        $data = $this->basequery( $this->filter->start_date, $this->filter->end_date );

        return array_map( function ( array $row ) {
            if ( $row['is_booked'] ) {
                $row['status'] = 'Booked';
            } elseif ( $row['is_held'] ) {
                $weeksheld = $this->weekheld( $row['record_id'] );
                if ( $weeksheld ) {
                    $row['status']     = 'Held';
                    $row['held_for']   = $weeksheld['user'];
                    $row['release_on'] = $weeksheld['release_on'];
                }
            }

            return Arr::except( $row, [ 'is_booked', 'is_held' ] );
        }, $data );
    }

    private function basequery( string $start, string $end ): array {
        global $wpdb;

        $sql = $wpdb->prepare( "SELECT
                                        i.record_id,
                                        r.ResortName,
                                        IF(i.active = 1, 'Yes', 'No') as active,
                                        DATE_FORMAT(i.check_in_date,'%%m/%%d/%%Y') as check_in_date,
                                        r.Town as city,
                                        r.Region as state,
                                        r.Country as country,
                                        i.Price ,
                                        u.name as 'UnitType',
                                        CASE
                                            WHEN i.type = 1 THEN 'Exchange'
                                            WHEN i.type = 2 THEN 'Rental'
                                            ELSE 'Both'
                                        END as type,
                                        CASE
                                            WHEN i.source_num = 1 THEN 'Owner'
                                            WHEN i.source_num = 2 THEN 'GPR'
                                            ELSE 'Trade Partner'
                                        END as Source,
                                        pa.name as 'SourcePartnerName',
                                        'Available' as status,
                                        EXISTS(SELECT t.id FROM wp_gpxTransactions t WHERE t.`cancelled` != 1 AND t.transactionType = 'booking' AND t.weekId = i.record_id) as is_booked,
                                        EXISTS(SELECT h.id FROM wp_gpxPreHold h WHERE h.released = 0 AND h.weekId = i.record_id) as is_held,
                                        NULL as held_for,
                                        NULL as release_on
                                        FROM wp_room i
                                        JOIN wp_resorts r ON (i.resort = r.id)
                                        JOIN wp_unit_type u ON (i.unit_type = u.record_id)
                                        LEFT JOIN wp_partner pa ON (pa.user_id = i.source_partner_id)
                                        WHERE
                                            DATE(i.check_in_date) BETWEEN %s and %s",
                               $start,
                               $end );

        return $wpdb->get_results( $sql, ARRAY_A );
    }

    private function weekheld( int $weekId ): ?array {
        global $wpdb;

        $sql = $wpdb->prepare( "SELECT
                                        propertyID,
                                        weekId,
                                        `user`,
                                        weekType,
                                        released,
                                        release_on
                                    FROM  wp_gpxPreHold
                                    WHERE
                                        released = 0 AND
                                        weekId = %d
                                    LIMIT 1",
                               $weekId );

        return $wpdb->get_row( $sql, ARRAY_A );
    }
}
