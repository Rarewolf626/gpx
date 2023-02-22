<?php

namespace GPX\Repository;

class IntervalRepository {

    public static function instance(): IntervalRepository {
        return gpx( IntervalRepository::class );
    }

    public function count_intervals( int $member_number, bool $active_only = true ): int {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT
                    COUNT(*) as num_intervals
                FROM wp_owner_interval a
                INNER JOIN wp_resorts b ON b.gprID LIKE CONCAT(BINARY a.resortID, '%%')
                WHERE a.ownerID IN
                    (SELECT DISTINCT gpr_oid
                        FROM wp_mapuser2oid
                        WHERE gpx_user_id IN
                            (SELECT DISTINCT gpx_user_id
                            FROM wp_mapuser2oid
                            WHERE gpr_oid=%d))", $member_number);
        if($active_only){
            $sql .= " AND a.Contract_Status__c = 'Active'";
        }
        return (int) $wpdb->get_var($sql);
    }

    public function get_member_ownerships($cid)
    {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT a.*, b.ResortName, b.gpr, c.deposit_year
                FROM wp_owner_interval a
                INNER JOIN wp_resorts b ON (a.resortID != '' AND a.resortID IS NOT NULL AND b.gprID = a.resortID)
                LEFT JOIN (SELECT MAX(deposit_year) as deposit_year, interval_number FROM wp_credit WHERE status != 'Pending' GROUP BY interval_number) c ON c.interval_number=a.contractID
                WHERE a.userID = %d", $cid);
        return $wpdb->get_results($sql, ARRAY_A);
    }
}
