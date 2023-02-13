<?php

namespace GPX\Repository;

use GPX\Model\CustomRequest;
class OwnerRepository {

    public static function instance(): OwnerRepository {
        return gpx( OwnerRepository::class );
    }

    /**
     * do not use wp_users table at all for owners. The email address sorted here is not reliable.
     * [x] SELECT `user_email` FROM `wp_users` WHERE `ID` = ?
     *
     *
     * [1] SELECT `meta_value` FROM `wp_usermeta` WHERE `meta_key` = 'Email' AND `user_id` = ?
     * [2] SELECT `meta_value` FROM `wp_usermeta` WHERE `meta_key` = 'user_email' AND `user_id` = ?
     * [3] SELECT `meta_value` FROM `wp_usermeta` WHERE `meta_key` = 'Email1' AND `user_id` = ?
     * [x] SELECT `meta_value` FROM `wp_usermeta` WHERE `meta_key` = 'Email2' AND `user_id` = ?
     * [4] SELECT `SPI_Email__c` FROM `wp_GPR_Owner_ID__c` WHERE `user_id` = ?
     * [5] SELECT `meta_value` FROM `wp_usermeta` WHERE `meta_key` = 'SPI_Email__c' AND `user_id` = ?
     *
     * @param int $userid
     *
     * @return ?string
     */
    public function get_email( int $userid = null ) {
        if(!$userid) return null;
        global $wpdb;

        $user_info  = get_userdata( $userid );
        $user_email = $user_info->user_email;

        $meta = get_user_meta( $userid );

        $sql  = $wpdb->prepare( "SELECT `SPI_Email__c` FROM `wp_GPR_Owner_ID__c` WHERE `user_id` = %d", $userid );
        $data = $wpdb->get_var( $sql );

        // order of fallback
        $use_email   = [];
        $use_email[] = $meta['Email'][0] ?? null;
        $use_email[] = $meta['email'][0] ?? null;
        $use_email[] = $meta['user_email'][0] ?? null;
        $use_email[] = $meta['Email1'][0] ?? null;
        $use_email[] = $data;
        $use_email[] = $meta['SPI_Email__c'][0] ?? null;
        $use_email[] = $user_email;

        $the_email = collect( $use_email )->first( function ( $email ) {
            return ! empty( $email );
        } );

        return $the_email;
    }


    public function get_hold_count( int $userid ): int {
        global $wpdb;

        $sql = $wpdb->prepare( "SELECT COUNT(id) as holds FROM wp_gpxPreHold WHERE user=%d AND released='0'", $userid );

        return (int) $wpdb->get_var( $sql );
    }

    public function get_credits( int $userid ): int {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT  IFNULL(SUM(credit_amount),0) - IFNULL(SUM(credit_used),0) AS credits
                        FROM wp_credit
                        WHERE owner_id IN (SELECT gpx_user_id FROM wp_mapuser2oid WHERE gpx_user_id = %d)
                        AND (credit_expiration_date IS NULL OR credit_expiration_date > %s)",
            [ $userid, date( 'Y-m-d' ) ]
        );

        return (int) $wpdb->get_var( $sql );
    }

    public function has_requests_remaining( int $cid, int $emsid ): bool {
        // count active ownership weeks
        $interval_count = IntervalRepository::instance()->count_intervals($emsid, true);

        // credit amount - credit used
        $credits = $this->get_credits( $cid );

        // get existing custom requests
        $checkCustomRequests = CustomRequestRepository::instance()->count_open_requests( $emsid, $cid );

        return $checkCustomRequests < $credits + $interval_count;
    }

    public function get_unique_email( string $email, string $name ): string {
        $i = 0;
        while ( email_exists( $email ) ) {
            $email = $i ? $name . '+' . $i . '@example.com' : $name . '@example.com';
            $i ++;
        }

        return $email;
    }

    public function get_unique_username( string $email, string $name ): string {
        $i = 0;
        while ( username_exists( $email ) ) {
            $email = $i ? $name . $i . '@example.com' : $name . '@example.com';
            $i ++;
        }

        return $email;
    }


}
