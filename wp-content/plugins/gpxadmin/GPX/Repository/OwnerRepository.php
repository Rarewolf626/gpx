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


    /**
     * @param int $userid
     * @return string|null
     */
    public function get_hold_count(int $userid) {
        global $wpdb;

        $sql       = $wpdb->prepare("SELECT COUNT(id) as holds FROM wp_gpxPreHold WHERE user=%d AND released='0'", $userid);
        $holdcount = $wpdb->get_var( $sql );
        return $holdcount;
    }

    /**
     * @param int $userid
     * @return mixed
     */
    public function get_credits(int $userid) {
        global $wpdb;

        $sql    = $wpdb->prepare(
                "SELECT  SUM(credit_amount) AS total_credit_amount,
                                SUM(credit_used) AS total_credit_used
                        FROM wp_credit
                        WHERE owner_id IN (SELECT gpx_user_id FROM wp_mapuser2oid WHERE gpx_user_id = %d)
                        AND (credit_expiration_date IS NULL OR credit_expiration_date > %s)",
                        [$userid, date( 'Y-m-d' )]
                    );
        $credit = $wpdb->get_row( $sql );

        return $credit->total_credit_amount - $credit->total_credit_used;
    }

    /**
     * @param $owner
     * @return int
     */
    public function insert_owner($owner){
        global $wpdb;

        // @todo insert in to users
        //

        // @todo insert into users_meta


        // @todo finish insert to wp_GPR_Owner_ID__c

        $fullname =  $owner->SPI_First_Name__c." ".$owner->SPI_Last_Name__c;

        $wpdb->insert('wp_GPR_Owner_ID__c',
            array('Name'=>$owner->Name,
                'user_id'=>$owner,
                'SPI_Owner_Name_1st__c'=>$fullname,
                'SPI_Email__c'=> $owner->SPI_Email__c,
                'SPI_Home_Phone__c'=> $owner->SPI_Home_Phone__c,
                'SPI_Work_Phone__c'=> $owner->SPI_Work_Phone__c,
                'SPI_Street__c'=> $owner->SPI_Street__c,
                'SPI_City__c'=> $owner->SPI_City__c,
                'SPI_State__c'=> $owner->SPI_State__c,
                'SPI_Zip_Code__c'=> $owner->SPI_Zip_Code__c,
                'SPI_Country__c'=> $owner->SPI_Country__c));


        return $wpdb->insert_id;
    }




}
