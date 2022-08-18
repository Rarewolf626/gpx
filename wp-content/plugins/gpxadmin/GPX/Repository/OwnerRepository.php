<?php

namespace GPX\Repository;

class OwnerRepository

{

    /*
     *
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
     */
    public static function instance(): OwnerRepository{
        return gpx(OwnerRepository::class);
    }

    /**
     * @param int $userid
     * @return string
     */
    public function get_email(int $userid) {
        global $wpdb;

        $user_info = get_userdata($userid);
        $user_email = $user_info->user_email;

        $meta = get_user_meta( $userid);

        $sql = $wpdb->prepare("SELECT `SPI_Email__c` FROM `wp_GPR_Owner_ID__c` WHERE `user_id` = %d", $userid );
        $data = $wpdb->get_var($sql);

        // order of fallback
        $use_email = array();
        $use_email[] = $meta['Email'][0] ?? null;
        $use_email[] = $meta['email'][0] ?? null;
        $use_email[] = $meta['user_email'][0] ?? null;
        $use_email[] = $meta['Email1'][0] ?? null;
        $use_email[] = $data;
        $use_email[] = $meta['SPI_Email__c'][0] ?? null;
        $use_email[] = $user_email;

        $the_email = collect($use_email)->first(function($email){ return !empty($email); });

        return $the_email ;
    }

    /**
     * @param int $userid
     * @param string $email
     * @return void
     */
    public function save_email(int $userid, $email) {
        global $wpdb;

    }

}