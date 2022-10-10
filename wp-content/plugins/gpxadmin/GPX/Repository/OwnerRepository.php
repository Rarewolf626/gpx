<?php

namespace GPX\Repository;

use DB;
use SObject;
use WP_User;
use GPX\Model\User;
use GPX\Model\Owner;
use GPX\Model\Resort;
use GPX\Model\Interval;
use Illuminate\Support\Arr;
use GPX\Model\MappedInterval;
use Illuminate\Support\Carbon;
use GPX\Model\CustomRequest;
use GPX\Api\Salesforce\Salesforce;

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


    public function get_hold_count( int $userid ): int {
        global $wpdb;

        $sql = $wpdb->prepare( "SELECT COUNT(id) as holds FROM wp_gpxPreHold WHERE user=%d AND released='0'", $userid );
        return (int)$wpdb->get_var( $sql );

    }

    public function get_credits( int $userid ): int {
        global $wpdb;

        $sql    = $wpdb->prepare(
            "SELECT  SUM(credit_amount) - SUM(credit_used) AS credits
                        FROM wp_credit
                        WHERE owner_id IN (SELECT gpx_user_id FROM wp_mapuser2oid WHERE gpx_user_id = %d)
                        AND (credit_expiration_date IS NULL OR credit_expiration_date > %s)",
            [ $userid, date( 'Y-m-d' ) ]
        );
        return (int)$wpdb->get_var( $sql );
    }

    public function has_holds_remaining( int $cid, int $emsid ): bool
    {
        $holdcount = $this->get_hold_count( $cid) ;

        // credit amount + credit used
        $credits = $this->get_credits( $cid);

        // get existing custom requests
        $checkCustomRequests = CustomRequest::active()
                                            ->owner()
                                            ->byUser( $emsid, $cid )
                                            ->count();

        return $holdcount + $checkCustomRequests < $credits + 1;
    }

    public function import_from_sf( SObject $ownerObj ) {
        // set the imported status to 5
        // not sure what this is for
        DB::table( 'import_owner_no_vest' )->where( 'id', '=', $ownerObj->Name )->update( [ 'imported' => 5 ] );
        if ( empty( $ownerObj->SPI_Email__c ) ) {
            // do not import owners without an email
            return null;
        }
        $user_id = $this->find_existing_user( $ownerObj );
        if ( $user_id ) {
            $owner = $this->update_existing_owner( $user_id, $ownerObj );
            gpx_logger()->debug( 'Existing owner was updated from salesforce',
                                 [
                                     'user'     => $user_id,
                                     'owner'    => $owner->toArray(),
                                     'sfObject' => $ownerObj->fields,
                                 ] );
        } else {
            $owner = $this->insert_new_owner( $ownerObj );
            gpx_logger()->debug( 'New owner was imported from salesforce',
                                 [
                                     'user'     => $owner->user_id,
                                     'owner'    => $owner->toArray(),
                                     'sfObject' => $ownerObj->fields,
                                 ] );
        }
        $this->update_user_meta( $owner->user_id, $ownerObj );

        $this->insert_new_intervals( $owner->user_id, $ownerObj );
        // Send the WordPress user id back to salesforce to connect the account.
        $sf               = Salesforce::getInstance();
        $sfObject         = new SObject();
        $sfObject->type   = 'GPR_Owner_ID__c';
        $sfObject->fields = [
            'Name' => $ownerObj->Name,
            'GPX_Member_VEST__c' => $owner->user_id,
        ];
        $sf->gpxUpsert( 'Name', [ $sfObject ] );
        update_user_meta( $owner->user_id, 'GPX_Member_VEST__c', $owner->user_id );

        return $owner;
    }

    public function find_existing_user( SObject $ownerObj ): ?int {
        global $wpdb;
        // See if there is already an entry in the owners table
        $sql     = $wpdb->prepare( "SELECT user_id FROM `wp_GPR_Owner_ID__c` WHERE Name = %s LIMIT 1",
                                   [ $ownerObj->Name ] );
        $user_id = $wpdb->get_var( $sql );
        if ( $user_id ) {
            return (int) $user_id;
        }
        // see if there is an existing user connected with this member number.
        $user = Arr::first( get_users(
                                [
                                    'fields'      => 'ID',
                                    'meta_key'    => 'DAEMemberNo',
                                    'meta_value'  => $ownerObj->Name,
                                    'number'      => 1,
                                    'count_total' => false,
                                ]
                            ) );

        return $user ? (int) $user : null;
    }

    private function insert_new_owner( SObject $ownerObj ): Owner {
        $login   = $this->get_unique_username( $ownerObj->SPI_Email__c, $ownerObj->Name );
        $email   = $this->get_unique_email( $ownerObj->SPI_Email__c, $ownerObj->Name );
        $user_id = wp_insert_user(
            [
                'user_login' => $login,
                'user_email' => $email,
                'user_pass'  => wp_generate_password(),
                'first_name' => $ownerObj->SPI_First_Name__c,
                'last_name'  => $ownerObj->SPI_Last_Name__c,
            ]
        );
        if ( is_wp_error( $user_id ) ) {
            DB::table( 'wp_owner_spi_error' )
              ->insert(
                  [
                      'owner_id'   => $ownerObj->Owner_ID__c,
                      'updated_at' => Carbon::now(),
                      'data'       => json_encode( [
                                                       'error_message' => $user_id->get_error_message(),
                                                       'sfDetails'     => json_encode( $ownerObj ),
                                                   ] ),
                  ]
              );
            throw new \Exception( $user_id->get_error_message(), $user_id->get_error_code() );
        }

        $owner                        = new Owner();
        $owner->Name                  = $ownerObj->Name;
        $owner->created_date          = $ownerObj->CreatedDate ? Carbon::parse( $ownerObj->CreatedDate ) : Carbon::now();
        $owner->updated_date          = Carbon::now();
        $owner->user_id               = $user_id;
        $owner->SPI_Owner_Name_1st__c = trim( $ownerObj->fields->SPI_First_Name__c . " " . $ownerObj->fields->SPI_Last_Name__c );
        $owner->SPI_Email__c          = $ownerObj->fields->SPI_Email__c;
        $owner->SPI_Home_Phone__c     = $ownerObj->fields->SPI_Home_Phone__c;
        $owner->SPI_Work_Phone__c     = $ownerObj->fields->SPI_Work_Phone__c;
        $owner->SPI_Street__c         = $ownerObj->fields->SPI_Street__c;
        $owner->SPI_City__c           = $ownerObj->fields->SPI_City__c;
        $owner->SPI_State__c          = $ownerObj->fields->SPI_State__c;
        $owner->SPI_Zip_Code__c       = $ownerObj->fields->SPI_Zip_Code__c;
        $owner->SPI_Country__c        = $ownerObj->fields->SPI_Country__c;
        $owner->save();

        return $owner;
    }

    private function update_existing_owner( int $user_id, SObject $ownerObj ): Owner {
        wp_update_user(
            [
                'ID'         => $user_id,
                'user_email' => $ownerObj->SPI_Email__c,
                'first_name' => $ownerObj->SPI_First_Name__c,
                'last_name'  => $ownerObj->SPI_Last_Name__c,
            ]
        );

        $owner          = Owner::where( 'user_id', '=', $user_id )->firstOrNew();
        $owner->Name    = $ownerObj->Name;
        $owner->user_id = $user_id;
        if ( $ownerObj->CreatedDate ) {
            $owner->created_date = Carbon::parse( $ownerObj->CreatedDate );
        }
        $owner->updated_date          = Carbon::now();
        $owner->SPI_Owner_Name_1st__c = trim( $ownerObj->fields->SPI_First_Name__c . " " . $ownerObj->fields->SPI_Last_Name__c );
        $owner->SPI_Email__c          = $ownerObj->fields->SPI_Email__c;
        $owner->SPI_Home_Phone__c     = $ownerObj->fields->SPI_Home_Phone__c;
        $owner->SPI_Work_Phone__c     = $ownerObj->fields->SPI_Work_Phone__c;
        $owner->SPI_Street__c         = $ownerObj->fields->SPI_Street__c;
        $owner->SPI_City__c           = $ownerObj->fields->SPI_City__c;
        $owner->SPI_State__c          = $ownerObj->fields->SPI_State__c;
        $owner->SPI_Zip_Code__c       = $ownerObj->fields->SPI_Zip_Code__c;
        $owner->SPI_Country__c        = $ownerObj->fields->SPI_Country__c;
        $owner->save();

        return $owner;
    }

    public function insert_new_intervals( int $user_id, SObject $ownerObj ) {
        $intervals = $ownerObj->intervals ?? [];
        if ( ! $intervals ) {
            return;
        }
        $user = get_user_by( 'id', $user_id );
        foreach ( $intervals as $row ) {
            $data = [
                'userID'                   => $user_id,
                'ownerID'                  => $row->fields->Owner_ID__c,
                'resortID'                 => mb_substr( $row->fields->GPR_Resort__c, 0, 15 ),
                'contractID'               => $row->Contract_ID__c ?: '',
                'unitweek'                 => $row->UnitWeek__c ?: null,
                'Contract_Status__c'       => $row->Contract_Status__c ?: null,
                'Delinquent__c'            => $row->Delinquent__c ?: null,
                'Days_past_due__c'         => $row->Days_Past_Due__c ?: null,
                'Total_Amount_Past_Due__c' => $row->Total_Amount_Past_Due__c ?: null,
                'Room_type__c'             => $row->Room_Type__c ?: null,
                'Year_Last_Banked__c'      => $row->Year_Last_Banked__c ?: null,
                'RIOD_Key_Full'            => $row->ROID_Key_Full__c ?: '',
            ];

            $interval = Interval::where( 'RIOD_Key_Full', '=', $row->ROID_Key_Full__c )->firstorNew();
            $interval->fill( $data );
            $interval->save();
            gpx_logger()->debug( 'Interval imported from salesforce',
                                 [
                                     'user'     => $user_id,
                                     'owner'    => $row->fields->Owner_ID__c,
                                     'interval' => $row->fields,
                                 ] );

            if ( $row->Resort_Name ) {
                $updated = DB::table( 'wp_resorts' )
                             ->where( 'ResortName', '=', $row->Resort_Name )
                             ->where( fn( $query ) => $query
                                 ->whereNull( 'gprID' )
                                 ->orWhere( 'gprID', '!=', $interval->resortID )
                             )
                             ->update( [ 'gprID' => $interval->resortID ] );
                if ( $updated ) {
                    gpx_logger()->debug( 'Connected resort',
                                         [ 'gprID' => $interval->resortID, 'resort' => $row->Resort_Name ] );
                }
            }
            $map = MappedInterval::where( 'RIOD_Key_Full', '=', $row->ROID_Key_Full__c )->firstorNew();
            $map->fill( [
                            'gpx_user_id'      => $user_id,
                            'gpx_username'     => $user->user_login,
                            'gpr_oid'          => $interval->ownerID,
                            'gpr_oid_interval' => $interval->ownerID,
                            'resortID'         => $interval->resortID,
                            'user_status'      => 0,
                            'Delinquent__c'    => $interval->Delinquent__c,
                            'unitweek'         => $interval->unitweek,
                            'RIOD_Key_Full'    => $interval->RIOD_Key_Full,
                        ] );
            $map->save();
            gpx_logger()->debug( 'Saved wp_mapuser2oid', [ 'user' => $user_id, 'mapping' => $map->toArray() ] );
        }
    }

    private function update_user_meta( int $user_id, SObject $ownerObj ) {
        update_user_meta( $user_id, 'DAEMemberNo', $ownerObj->Name );
        if ( $ownerObj->Legacy_Preferred_Program_Member__c ) {
            $preferred = $ownerObj->Legacy_Preferred_Program_Member__c;
            if ( mb_strtolower( $preferred ) === 'true' ) {
                $preferred = 'Yes';
            }
            if ( mb_strtolower( $preferred ) === 'false' ) {
                $preferred = 'No';
            }
            update_user_meta( $user_id, 'GP_Preferred', $preferred );
        }
        $fields = [
            'first_name'      => 'SPI_First_Name__c',
            'last_name'       => 'SPI_Last_Name__c',
            'FirstName1'      => 'SPI_First_Name__c',
            'FirstName2'      => 'SPI_First_Name2__c',
            'LastName1'       => 'SPI_Last_Name__c',
            'LastName2'       => 'SPI_Last_Name2__c',
            'email'           => 'SPI_Email__c',
            'phone'           => 'SPI_Home_Phone__c',
            'DayPhone'        => 'SPI_Home_Phone__c',
            'work_phone'      => 'SPI_Work_Phone__c',
            'address'         => 'SPI_Street__c',
            'Address1'        => 'SPI_Street__c',
            'city'            => 'SPI_City__c',
            'Address3'        => 'SPI_City__c',
            'state'           => 'SPI_State__c',
            'Address4'        => 'SPI_State__c',
            'zip'             => 'SPI_Zip_Code__c',
            'Address5'        => 'SPI_Zip_Code__c',
            'PostCode'        => 'SPI_Zip_Code__c',
            'country'         => 'SPI_Country__c',
            'ExternalPartyID' => 'SpiOwnerId__c',
            'Property_Owner'  => 'Property_Owner__c',
        ];
        foreach ( $fields as $meta_key => $field ) {
            if ( $ownerObj->$field ) {
                update_user_meta( $user_id, $meta_key, $ownerObj->$field );
            }
        }
        $user = new WP_User( $user_id );
        if ( ! in_array( 'gpx_member', $user->roles ) ) {
            if ( $user->roles == [ 'subscriber' ] ) {
                $user->set_role( 'gpx_member' );
            } else {
                $user->add_role( 'gpx_member' );
            }
        }
    }

    private function get_unique_email( string $email, string $name ): string {
        $i = 0;
        while ( email_exists( $email ) ) {
            $email = $i ? $name . '+' . $i . '@example.com' : $name . '@example.com';
            $i ++;
        }

        return $email;
    }

    private function get_unique_username( string $email, string $name ): string {
        $i = 0;
        while ( username_exists( $email ) ) {
            $email = $i ? $name . '+' . $i . '@example.com' : $name . '@example.com';
            $i ++;
        }

        return $email;
    }
}
