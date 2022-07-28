<?php

namespace GPX\Repository;

class OwnerRepository
{
    public static function instance(): OwnerRepository{
        return gpx(OwnerRepository::class);
    }

    /*
     *
     *  do not use, the wp_users table should not contain the email address of the owner
     *  [x] SELECT `user_email` FROM `wp_users` WHERE ID = ?;
     *
     *
     *  SELECT `meta_value` FROM `wp_usermeta` WHERE `meta_key` = 'Email' AND `user_id` = ?
     *  SELECT `meta_value` FROM `wp_usermeta` WHERE `meta_key` = 'user_email' AND `user_id` = ?
     *  SELECT `meta_value` FROM `wp_usermeta` WHERE `meta_key` = 'Email1' AND `user_id` = ?
     *  SELECT `meta_value` FROM `wp_usermeta` WHERE `meta_key` = 'Email2' AND `user_id` = ?
     *  SELECT `SPI_Email__c` FROM `wp_GPR_Owner_ID__c` WHERE `user_id` = ?
     *  SELECT `meta_value` FROM `wp_usermeta` WHERE `meta_key` = 'SPI_Email__c' AND `user_id` = ?
     *
     */


    /*
     * gets email address for owner to multiple tables / fields
     */
    public function get_email (int $ownerid) {
        global $wpdb;
        // 1.
        $sql = $wpdb->prepare("SELECT `meta_value` FROM `wp_usermeta` WHERE `meta_key` = 'Email' AND `user_id` = ?", $ownerid);
        $data = $wpdb->get_results($sql);
        // 2.



    }
    /*
     *  saves  email address for owner to multiple tables / fields
     */
    public function save_email (int $ownerid, $email){
        global $wpdb;

    }

}
