<?php

namespace GPX\Repository;

use DB;
use SObject;
use GPX\Model\Owner;
use GPX\Model\Interval;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use GPX\Api\Salesforce\Salesforce;

class OwnerRepository {

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
    public static function instance(): OwnerRepository {
        return gpx( OwnerRepository::class );
    }

    /**
     * @param int $userid
     *
     * @return string
     */
    public function get_email( int $userid ) {
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

    /**
     * @param int    $userid
     * @param string $email
     *
     * @return void
     */
    public function save_email( int $userid, $email ) {
        global $wpdb;
    }


    /**
     * @param int $userid
     *
     * @return string|null
     */
    public function get_hold_count( int $userid ) {
        global $wpdb;

        $sql       = $wpdb->prepare( "SELECT COUNT(id) as holds FROM wp_gpxPreHold WHERE user=%d AND released='0'",
                                     $userid );
        $holdcount = $wpdb->get_var( $sql );

        return $holdcount;
    }

    /**
     * @param int $userid
     *
     * @return mixed
     */
    public function get_credits( int $userid ) {
        global $wpdb;

        $sql    = $wpdb->prepare(
            "SELECT  SUM(credit_amount) AS total_credit_amount,
                                SUM(credit_used) AS total_credit_used
                        FROM wp_credit
                        WHERE owner_id IN (SELECT gpx_user_id FROM wp_mapuser2oid WHERE gpx_user_id = %d)
                        AND (credit_expiration_date IS NULL OR credit_expiration_date > %s)",
            [ $userid, date( 'Y-m-d' ) ]
        );
        $credit = $wpdb->get_row( $sql );

        return $credit->total_credit_amount - $credit->total_credit_used;
    }

    /**
     * @param $owner
     *
     * @return int
     */
    public function insert_owner( $owner ) {
        global $wpdb;

        // @todo insert in to users
        //

        // @todo insert into users_meta


        // @todo finish insert to wp_GPR_Owner_ID__c

        $fullname = $owner->SPI_First_Name__c . " " . $owner->SPI_Last_Name__c;

        $wpdb->insert( 'wp_GPR_Owner_ID__c',
                       [
                           'Name'                  => $owner->Name,
                           'user_id'               => $owner,
                           'SPI_Owner_Name_1st__c' => $fullname,
                           'SPI_Email__c'          => $owner->SPI_Email__c,
                           'SPI_Home_Phone__c'     => $owner->SPI_Home_Phone__c,
                           'SPI_Work_Phone__c'     => $owner->SPI_Work_Phone__c,
                           'SPI_Street__c'         => $owner->SPI_Street__c,
                           'SPI_City__c'           => $owner->SPI_City__c,
                           'SPI_State__c'          => $owner->SPI_State__c,
                           'SPI_Zip_Code__c'       => $owner->SPI_Zip_Code__c,
                           'SPI_Country__c'        => $owner->SPI_Country__c,
                       ] );


        return $wpdb->insert_id;
    }


    public function import_from_sf( SObject $ownerObj ) {
        // set the imported status to 5
        // not sure what this is for
        DB::table( 'import_owner_no_vest' )->where( 'id', '=', $ownerObj->Name )->update( [ 'imported' => 5 ] );
        if ( empty( $ownerObj->SPI_Email__c ) ) {
            // do not import owners without an email
            return null;
        }
        // check if the user already exists
        // check the wp_user_meta for the 'Name'/GPX_Member_VEST__c to see if the owner has been added
        $user = get_user_by( 'login', $ownerObj->SPI_Email__c );
        if ( ! $user ) {
            $user = get_user_by( 'email', $ownerObj->SPI_Email__c );
        }
        if ( ! $user ) {
            $user = Arr::first( get_users(
                                    [
                                        'fields'      => 'ID',
                                        'meta_query'  => [
                                            'relation' => 'OR',
                                            [
                                                'key'   => 'GPX_Member_VEST__c',
                                                'value' => $ownerObj->Name,
                                            ],
                                            [
                                                'key'   => 'DAEMemberNo',
                                                'value' => $ownerObj->Name,
                                            ],
                                        ],
                                        'number'      => 1,
                                        'count_total' => false,
                                    ]
                                ) );
        }

        if ( $user ) {
            $owner = $this->update_existing_owner( $user->ID, $ownerObj );
        } else {
            $owner = $this->insert_new_owner( $ownerObj );
        }

        $this->insert_new_intervals( $owner->user_id, $ownerObj->intervals );
        // @TODO temporarilly comment out
//        $sf = Salesforce::getInstance();
//        $sfObject         = new SObject();
//        $sfObject->type   = 'GPR_Owner_ID__c';
//        $sfObject->fields = [
//            'GPX_Member_VEST__c' => $owner->user_id,
//        ];
//        $sf->gpxUpsert( 'Name', [ $sfObject ] );
        update_user_meta( $owner->user_id, 'GPX_Member_VEST__c', $owner->user_id );

        return $owner;
    }

    /**
     * @param $ownerObj
     *
     * @return void
     */
    private function insert_new_owner( SObject $ownerObj ): Owner {
        $user_id = wp_insert_user(
            [
                'user_login' => $ownerObj->SPI_Email__c,
                'user_email' => $ownerObj->SPI_Email__c,
                'user_pass'  => wp_generate_password(),
                'first_name' => $ownerObj->SPI_First_Name__c,
                'last_name'  => $ownerObj->SPI_Last_Name__c,
            ]
        );
        if ( is_wp_error( $user_id ) ) {
            throw new \Exception( $user_id->get_error_message(), $user_id->get_error_code() );
        }

        $owner                        = new Owner();
        $owner->Name                  = $ownerObj->Name;
        $owner->created_date          = Carbon::parse( $ownerObj->CreatedDate );
        $owner->updated_date          = Carbon::now();
        $owner->user_id               = $user_id;
        $owner->SPI_Owner_Name_1st__c = trim( $ownerObj->SPI_First_Name__c . " " . $ownerObj->SPI_Last_Name__c );
        $owner->SPI_Email__c          = $ownerObj->SPI_Email__c;
        $owner->SPI_Home_Phone__c     = $ownerObj->SPI_Home_Phone__c;
        $owner->SPI_Work_Phone__c     = $ownerObj->SPI_Work_Phone__c;
        $owner->SPI_Street__c         = $ownerObj->SPI_Street__c;
        $owner->SPI_City__c           = $ownerObj->SPI_City__c;
        $owner->SPI_State__c          = $ownerObj->SPI_State__c;
        $owner->SPI_Zip_Code__c       = $ownerObj->SPI_Zip_Code__c;
        $owner->SPI_Country__c        = $ownerObj->SPI_Country__c;
//        $owner->imported              = true;
        $owner->save();

        // insert into user_meta
        update_user_meta( $user_id, 'SfImported', 1 );
        update_user_meta( $user_id, 'DAEMemberNo', $ownerObj->Name );

        return $owner;
    }


    private function update_existing_owner( int $user_id, SObject $ownerObj ): Owner {
        wp_update_user(
            [
                'ID'         => $user_id,
                'first_name' => $ownerObj->SPI_First_Name__c,
                'last_name'  => $ownerObj->SPI_Last_Name__c,
            ]
        );

        $owner                        = Owner::where( 'user_id', '=', $user_id )->firstOrNew();
        $owner->Name                  = $ownerObj->Name;
        $owner->user_id               = $user_id;
        $owner->created_date          = Carbon::parse( $ownerObj->CreatedDate );
        $owner->updated_date          = Carbon::now();
        $owner->SPI_Owner_Name_1st__c = trim( $ownerObj->SPI_First_Name__c . " " . $ownerObj->SPI_Last_Name__c );
        $owner->SPI_Email__c          = $ownerObj->SPI_Email__c;
        $owner->SPI_Home_Phone__c     = $ownerObj->SPI_Home_Phone__c;
        $owner->SPI_Work_Phone__c     = $ownerObj->SPI_Work_Phone__c;
        $owner->SPI_Street__c         = $ownerObj->SPI_Street__c;
        $owner->SPI_City__c           = $ownerObj->SPI_City__c;
        $owner->SPI_State__c          = $ownerObj->SPI_State__c;
        $owner->SPI_Zip_Code__c       = $ownerObj->SPI_Zip_Code__c;
        $owner->SPI_Country__c        = $ownerObj->SPI_Country__c;
        $owner->imported              = true;
        $owner->save();

        // insert into user_meta
        update_user_meta( $user_id, 'SfImported', 1 );
        update_user_meta( $user_id, 'DAEMemberNo', $ownerObj->Name );

        return $owner;
    }

    public function insert_new_intervals( int $user_id, array $intervals = [] ) {
        foreach ( $intervals as $row ) {
            $data = [
                'userID'                   => $user_id,
                'ownerID'                  => $row->Owner_ID__c,
                'resortID'                 => substr( $row->GPR_Resort__c, 0, 15 ),
                'contractID'               => $row->Contract_ID__c,
                'unitweek'                 => $row->UnitWeek__c,
                'Contract_Status__c'       => $row->Contract_Status__c,
                'Delinquent__c'            => $row->Delinquent__c,
                'Days_past_due__c'         => $row->Days_Past_Due__c,
                'Total_Amount_Past_Due__c' => $row->Total_Amount_Past_Due__c,
                'Room_type__c'             => $row->Room_Type__c,
                'Year_Last_Banked__c'      => $row->Year_Last_Banked__c,
                'RIOD_Key_Full'            => $row->ROID_Key_Full__c,
            ];

            $interval = Interval::where( 'RIOD_Key_Full', '=', $row->ROID_Key_Full__c )->firstorNew();
            $interval->fill( $data );
            $interval->save();
        }
    }
}
