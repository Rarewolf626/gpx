<?php

use Illuminate\Support\Arr;
use GPX\Repository\WeekRepository;

class GpxRetrieve {
    public $uri;
    public $dir;
    public $daecred;
    public $expectedMemberDetails;
    public $expectedBookingDetails;
    public $expectedPaymentDetails;
    public static $instance = null;

    public function __construct( $uri = null, $dir = null ) {
        $this->uri = plugins_url( '', __FILE__ ) . '/api';
        $this->dir = trailingslashit( dirname( __FILE__ ) );

        $this->daecred = [
            'action' => get_option( 'dae_ws_action' ),
            'host' => get_option( 'dae_ws_host' ),
            'AuthID' => get_option( 'dae_ws_authid' ),
            'DAEMemberNo' => get_option( 'dae_ws_memberno' ),
            'mode' => get_option( 'dae_ws_mode' ),
        ];

        $this->expectedMemberDetails = [
            'MemberNo' => 'YES',
            'AccountName' => 'YES',
            'Address1' => 'YES',
            'Address2' => 'NO',
            'Address3' => 'YES',
            'Address4' => 'YES',
            'Address5' => 'YES',
            'BroadcastEmail' => 'NO',
            'DayPhone' => 'NO',
            'Email' => 'YES',
            'Email2' => 'YES',
            'Fax' => 'NO',
            'Salutation' => 'YES',
            'Title1' => 'YES',
            'Title2' => 'YES',
            'FirstName1' => 'YES',
            'FirstName2' => 'NO',
            'HomePhone' => 'YES',
            'LastName1' => 'YES',
            'LastName2' => 'NO',
            'MailName' => 'YES',
            'Mobile' => 'NO',
            'Mobile2' => 'NO',
            'NewsletterStatus' => 'YES',
            'PostCode' => 'YES',
            'Password' => 'NO',
            'ReferalID' => 'YES',
            'ExternalMemberNumber' => 'NO',
            'MailOut' => 'YES',
            'SMSStatus' => 'YES',
            'SMSNumber' => 'YES',
        ];

        $this->expectedBookingDetails = [
            'CreditWeekID',
            'WeekEndpointID',
            'WeekID',
            'GuestFirstName',
            'GuestLastName',
            'GuestEmailAddress',
            'Adults',
            'Children',
            'WeekType',
            'CPO',
            'AmountPaid',
            'DAEMemberNo',
            'ResortID',
            'CurrencyCode',
            'GuestAddress',
            'GuestTown',
            'GuestState',
            'GuestPostCode',
            'GuestPhone',
            'GuestMobile',
            'GuestCountry',
            'GuestTitle',
        ];
        $this->expectedPaymentDetails = [
            'DAEMemberNo',
            'Address',
            'PostCode',
            'Country',
            'Email',
            'CardHolder',
            'CardNo',
            'CCV',
            'ExpiryMonth',
            'ExpiryYear',
            'PaymentAmount',
            'CurrencyCode',
        ];
    }

    public static function instance(): GpxRetrieve {
        if ( ! self::$instance ) {
            self::$instance = new GpxRetrieve( GPXADMIN_API_URI, GPXADMIN_API_DIR );
        }

        return self::$instance;
    }


    function addRegions() {
        global $wpdb;

        $sql = "SELECT * FROM wp_daeRegion";
        $existingRegions = $wpdb->get_results( $sql );
        foreach ( $existingRegions as $allRegions ) {
            $regionsCheck[ $allRegions->id ] = $allRegions->region;
        }
        $sql = "SELECT * FROM wp_daeCountry";
        $existingCountries = $wpdb->get_results( $sql );
        foreach ( $existingCountries as $allCountries ) {
            $countriesCheck[ $allCountries->id ] = $allCountries->country;
        }
        $countries = $this->DAEGetCountryList();
        foreach ( $countries as $countryXML ) {
            //insert each countries
            $country = json_decode( json_encode( $countryXML ) );
            $data = [
                'country' => $country->ItemDescription,
                'CountryID' => $country->ItemID,
            ];
            if ( in_array( $country->ItemDescription, $countriesCheck ) ) {
                $validatedCountry[] = $country->ItemDescription;
            }
            if ( $wpdb->update( 'wp_gpxCategory',
                [ 'newCountryID' => $country->ItemID ],
                [ 'country' => $country->ItemDescription ] ) ) {
                //updated
            } else {
                $wpdb->insert( 'wp_gpxCategory', [
                    'country' => $country->ItemDescription,
                    'newCountryID' => $country->ItemID,
                    'search_name' => gpx_search_string( $country->ItemDescription ),
                ] );
            }

            $regions = $this->DAEGetRegionList( $country->ItemID );

            foreach ( $regions as $regionXML ) {
                $region = json_decode( json_encode( $regionXML ) );
                $countryParsed = json_decode( json_encode( $country ) );
                $data = [
                    'region' => $region->ItemDescription,
                    'RegionID' => $region->ItemID,
                    'CountryID' => $countryParsed->ItemID,
                ];
                if ( in_array( $region->ItemDescription, $regionsCheck ) ) {
                    if ( $region->ItemDescription == 'All' ) {
                        continue;
                    }
                    $validatedRegion[] = $region->ItemDescription;
                }
            }
        }

        return [ 'success' => true ];
    }


    function DAEGetCountryList( $ping = '' ) {
        global $wpdb;
        $data = [
            'functionName' => 'DAEGetCountryList',
            'inputMembers' => [
                'FilterByParent' => 'false',
            ],
            'return' => 'GeneralListItem',
        ];

        $countries = [];

        if ( empty( $ping ) ) {
            $wpdb->update( 'wp_daeCountry', [ 'active' => '0' ], [ 'active' => '1' ] );

            foreach ( $countries as $countryXML ) {
                $country = json_decode( json_encode( $countryXML ) );
                $data = [
                    'country' => $country->ItemDescription,
                    'CountryID' => $country->ItemID,
                    'active' => 1,
                ];
                $wpdb->insert( 'wp_daeCountry', $data );
            }
        } else {
            $output = json_decode( json_encode( $countries ) );
            if ( isset( $output[0]->ItemID ) ) {
                $countries = [ 'success' => true ];
            }
        }

        return $countries;

    }

    /** @deprecated */
    function DAEGetRegionList( $CountryID ) {
        return [];
    }

    /** @deprecated */
    function DAEGetBonusRentalAvailability( $inputMembers ) {
        return [];
    }

    /** @deprecated */
    function DAEGetExchangeAvailability( $inputMembers ) {
        return [];
    }

    /** @deprecated */
    function AddDAEGetBonusRentalAvailability( $inputMembers ) {
        return [ 'success' => true ];
    }

    function NewAddDAEGetBonusRentalAvailability( $inputMembers ) {
        global $wpdb;

        extract( $inputMembers );

        $wheres = $wpdb->prepare( "CountryID=%s", $CountryID );
        if ( $RegionID != '?' ) {
            $wheres .= $wpdb->prepare( " AND RegionID=%s", $RegionID );
        }

        $sql = "SELECT id FROM wp_daeRegion WHERE " . $wheres;
        $rows = $wpdb->get_results( $sql );
        $removed = [];
        $toReturn = [];

        foreach ( $rows as $row ) {
            //set all weeks to inactive
            $sql = $wpdb->prepare( "SELECT lft, rght FROM wp_gpxRegion WHERE RegionID=%s", $row->id );
            $row = $wpdb->get_row( $sql );
            $lft = $row->lft;
            if ( ! empty( $lft ) ) {
                $sql = $wpdb->prepare( "SELECT id FROM wp_gpxRegion
                            WHERE lft BETWEEN %s AND %s
                             ORDER BY lft ASC",
                    [ $lft, $row->rght ] );
                $gpxRegions = $wpdb->get_col( $sql );
                if ( $gpxRegions ) {
                    $monthstart = date( 'Y-m-01', strtotime( $Year . "-" . $Month . "-01" ) );
                    $monthend = date( 'Y-m-t', strtotime( $Year . "-" . $Month . "-01" ) );

                    $placeholders = gpx_db_placeholders( $gpxRegions, '%d' );
                    $params = $gpxRegions;
                    $params[] = $monthstart;
                    $params[] = $monthend;
                    $sql = $wpdb->prepare( "SELECT a.id AS pid FROM wp_properties a
                        INNER JOIN wp_resorts b ON a.resortId=b.ResortID
                        WHERE b.gpxRegionID IN ({$placeholders})
                        AND (WeekType='BonusWeek' OR WeekType='RentalWeek')
                        AND STR_TO_DATE(checkIn, '%%d %%M %%Y') BETWEEN %s AND %s
                        AND a.active=1",
                        $params );
                    $rows = $wpdb->get_col( $sql );
                    $removed = array_merge( $removed, array_combine( $rows, $rows ) );
                }
            }
        }

        if ( ! empty( $removed ) ) {
            $wpdb->insert( 'wp_refresh_removed', [ 'removed' => json_encode( $removed ), 'type' => 'rental' ] );
            foreach ( $removed as $remove ) {
                $wpdb->update( 'wp_properties', [ 'active' => 0 ], [ 'id' => $remove ] );
            }
        }

        if ( isset( $propertiesAdded ) ) {
            $toReturn['weeks_added'] = $propertiesAdded;
        }
        $toReturn['success'] = true;

        return $toReturn;

    }

    /** @deprecated */
    function AddDAEGetExchangeAvailability( $inputMembers ) {
        return [ 'success' => true ];
    }

    function NewAddDAEGetExchangeAvailability( $inputMembers ) {
        global $wpdb;

        extract( $inputMembers );

        $data = [
            'functionName' => 'DAEGetExchangeAvailability',
            'inputMembers' => [
                'CountryID' => $CountryID,
                'RegionID' => $RegionID,
                'Month' => $Month,
                'Year' => $Year,
                'ShowSplitWeeks' => true,
                'DAEMemberNo' => $this->daecred[ $DAEMemberNo ],
            ],
            'return' => 'AvailabilityDetail',
        ];


        $removed = [];

        $wheres = $wpdb->prepare( "CountryID=%s", $CountryID );
        if ( $RegionID != '?' ) {
            $wheres .= $wpdb->prepare( " AND RegionID=%s", $RegionID );
        }

        $sql = "SELECT id FROM wp_daeRegion WHERE " . $wheres;
        $rows = $wpdb->get_results( $sql );
        foreach ( $rows as $r ) {
            //set all weeks to inactive
            $sql = $wpdb->prepare( "SELECT lft, rght FROM wp_gpxRegion WHERE RegionID=%s", $r->id );
            $row = $wpdb->get_row( $sql );
            $lft = $row->lft;
            if ( ! empty( $lft ) ) {
                $sql = $wpdb->prepare( "SELECT DISTINCT id, lft, rght FROM wp_gpxRegion
                            WHERE lft BETWEEN %d AND %d
                             ORDER BY lft ASC",
                    [ $lft, $row->rght ] );
                $gpxRegions = $wpdb->get_results( $sql );

                $monthstart = date( 'Y-m-01', strtotime( $Year . "-" . $Month . "-01" ) );
                $monthend = date( 'Y-m-t', strtotime( $Year . "-" . $Month . "-01" ) );

                foreach ( $gpxRegions as $gpxRegion ) {
                    $sql = $wpdb->prepare( "SELECT *, a.id AS pid FROM wp_properties a
                        INNER JOIN wp_resorts b ON a.resortId=b.ResortID
                        WHERE b.gpxRegionID=%s
                        AND (WeekType='ExchangeWeek')
                        AND STR_TO_DATE(checkIn, '%%d %%M %%Y') BETWEEN %s AND %s
                        AND a.active=1",
                        [ $gpxRegion->id, $monthstart, $monthend ] );

                    $rows = $wpdb->get_results( $sql );
                    foreach ( $rows as $row ) {
                        $removed[ $row->pid ] = $row->pid;
                        $allByWeekID[ $row->weekId ][ $row->WeekType ] = $row;
                    }
                }
            }
        }

        if ( isset( $propertiesAdded ) ) {
            $toReturn['weeks_added'] = $propertiesAdded;
        }
        if ( ! empty( $removed ) ) {
            $wpdb->insert( 'wp_refresh_removed', [ 'removed' => json_encode( $removed ), 'type' => 'exchange' ] );
            foreach ( $removed as $remove ) {
                $wpdb->update( 'wp_properties', [ 'active' => 0 ], [ 'id' => $remove ] );
            }
        }
        $toReturn['success'] = true;

        return $toReturn;
    }


    /**
     * @deprecated
     */
    function returnDAEGetMemberDetails( $DAEMemberNo ) {
        return [];
    }


    /** @deprecated */
    function DAEGetMemberDetails( $DAEMemberNo, $userID = '', $post = '', $password = '' ): array {
        return [ 'error' => 'EMS Error!' ];
    }

    /** @deprecated */
    function DAECreateMemeber( $memberDetails ) {
        return [];
    }

    /** @deprecated */
    function DAEGetMemberDetailsForUpdate( $DAEMemberNo ) {
        return json_decode( json_encode( [] ) );
    }

    function DAEUpdateMemberDetails( $DAEMemeberNo, $post ) {
        $post['MemberNo'] = $DAEMemeberNo;

        $currentData = [ new stdClass() ];
        $switchnames = [ 'first_name' => 'FirstName1', 'last_name' => 'LastName1', 'fax' => 'Fax' ];

        foreach ( $switchnames as $key => $value ) {
            if ( isset( $post[ $key ] ) ) {
                $post[ $value ] = $post[ $key ];
                unset( $post[ $key ] );
            }
        }
        unset( $post['updateProfile'] );
        unset( $post['profileSubmit'] );
        unset( $post['user_email'] );
        unset( $post['OwnershipWeekType'] );
        unset( $post['cid'] );
        $required = [
            'MemberNo',
            'Address1',
            'Address3',
            'Address4',
            'Address5',
            'Email',
            'Email2',
            'Title1',
            'Title2',
            'FirstName1',
            'LastName1',
            'PostCode',
            'Salutation',
            'Mobile',
        ];
        if ( isset( $post['DayPhone'] ) ) {
            $updateData['HomePhone'] = $post['DayPhone'];
        } elseif ( isset( $updateData ) ) {
            $updateData['DayPhone'] = $updateData['HomePhone']; // @phpstan-ignore-line
        }
        foreach ( $required as $require ) {
            if ( isset( $post[ $require ] ) ) {
                $updateData[ $require ] = str_replace( ' &', ', ', $post[ $require ] );
                unset( $post[ $require ] );
            } elseif ( isset( $currentData[0]->$require ) ) {
                $updateData[ $require ] = str_replace( ' &', ', ', $currentData[0]->$require );
            } else {
                $updateData[ $require ] = '';
            }
        }
        foreach ( $post as $key => $value ) {
            $updateData[ $key ] = str_replace( ' &', ', ', $value );
        }
        if ( ! isset( $updateData['AccountName'] ) || empty( $updateData['AccountName'] ) ) {
            if ( isset( $updateData['FirstName1'] ) && isset( $updateData['LastName1'] ) ) {
                $updateData['AccountName'] = $updateData['FirstName1'] . " " . $updateData['LastName1'];
            } else {
                $updateData['AccountName'] = $currentData[0]->FirstName1 . " " . $currentData[0]->LastName1;
            }
        }
        if ( ! isset( $updateData['NewsletterStatus'] ) || empty( $updateData['NewsletterStatus'] ) ) {
            $updateData['NewsletterStatus'] = $currentData[0]->NewsletterStatus;
        }
        if ( ! isset( $updateData['SMSStatus'] ) || empty( $updateData['SMSStatus'] ) ) {
            $updateData['SMSStatus'] = $currentData[0]->SMSStatus;
        }
        if ( ! isset( $updateData['ReferalID'] ) || empty( $updateData['ReferalID'] ) ) {
            $updateData['ReferalID'] = $currentData[0]->ReferalID;
        }

        if ( ! isset( $updateData['MailOut'] ) || empty( $updateData['MailOut'] ) ) {
            $updateData['MailOut'] = $currentData[0]->MailOut;
        }
        if ( ! isset( $updateData['MailName'] ) || empty( $updateData['MailName'] ) ) {
            if ( isset( $updateData['FirstName1'] ) && isset( $updateData['LastName1'] ) ) {
                $updateData['MailName'] = $updateData['FirstName1'] . " " . $updateData['LastName1'];
            } else {
                $updateData['MailName'] = $currentData[0]->FirstName1 . " " . $currentData[0]->LastName1;
            }
        }
        foreach ( $this->expectedMemberDetails as $emk => $emyn ) {
            $toUpdate[ $emk ] = $updateData[ $emk ];
        }
        $data = [
            'functionName' => 'DAEUpdateMemberDetails',
            'externalObject' => 'MemberDetails',
            'inputMembers' => $toUpdate,
            'return' => 'MemberDetails',
        ];
        $user = [];
        $user = json_decode( json_encode( $user[0] ) );
        if ( $user->ReturnCode == '0' ) {
            $class = 'success';
        } else {
            $class = 'warning';
        }
        $html = '<span class="label label-' . $class . '">' . $user->ReturnMessage . '</span>';

        return $html;
    }

    function DAEHoldWeek( $pid, $cid, $emsid = "", $bookingrequest = "" ) {
        global $wpdb;


        $releasetime = date( 'Y-m-d H:i:s', strtotime( '+24 hours' ) );

        //make sure that this owner can make a hold request
        if ( empty( $emsid ) ) {
            $emsid = gpx_get_member_number( $cid );
        }
        $holds = WeekRepository::instance()->get_weeks_on_hold( $emsid );

        $holdcount = 0;
        if ( isset( $holds[0] ) ) {
            $holdcount = count( $holds );
        } elseif ( isset( $holds['country'] ) ) {
            //dae just returns an associative array when there is only one
            $holdcount = 1;
        }

        $sql = $wpdb->prepare( "SELECT id, release_on FROM wp_gpxPreHold
                        WHERE user=%s AND propertyID=%s AND released=0", [ $cid, $pid ] );
        $row = $wpdb->get_row( $sql );

        //return true if credits+1 is greater than holds
        if ( 1 > $holdcount ) {
            //we're good we can continue holding this
        } else {
            $output = [ 'error' => 'too many holds', 'msg' => get_option( 'gpx_hold_error_message' ) ];


            if ( ! empty( $bookingrequest ) ) {
                //is this a new hold request
                //we dont' need to do anything here right now but let's leave it just in case
            } else {
                //since this isn't a booking request we need to return the error and prevent anything else from happeneing.
                if ( empty( $row ) ) {
                    return $output;
                }
            }
        }

        if ( ! empty( $bookingrequest ) ) {
            //is this a new hold request
            $releasetime = date( 'Y-m-d H:i:s', strtotime( '+' . get_option( 'gpx_hold_limt_time' ) . ' hours' ) );
            if ( ! empty( $row ) ) {
                if ( $row->release_on > $releasetime ) {
                    if ( strtotime( $releasetime ) > strtotime( $row->release_on ) ) {
                        $releasetime = $row->release_on;
                    }
                }
                $wpdb->delete( 'wp_gpxPreHold', [ 'id' => $row->id ] );
            }
        }

        $sql = $wpdb->prepare( "SELECT WeekType, WeekEndpointID, weekId, WeekType FROM wp_properties WHERE id=%s", $pid );
        $row = $wpdb->get_row( $sql );


        //As discussed on yesterday's call and again internally here amongst ourselves
        // we think the best number to show in the 'Exchange Summary' slot on the member dashboard
        //  be a formula that takes the total non-pending deposits and subtract out the Exchange weeks booked.
        //This will bypass the erroneous number being sent by DAE and not confuse the owner.


        $credit = 0;

        if ( ! isset( $emsid ) ) {
            $msg = "Please login to continue;";
            $output = [ 'error' => 'memberno', 'msg' => $msg ];

            return $output;
        } elseif ( isset($row) && $row->WeekType == 'ExchangeWeek' && ( ! empty( $credit ) && $credit <= - 1 ) ) {
            $msg = 'You have already booked an exchange with a negative deposit.  All deposits must be processed prior to completing this booking.  Please wait 48-72 hours for our system to verify the transactions.';
        } else {
            // @TODO old custom request form
            // uses pid so it might work differently
            $msg = 'This property is no longer available! <a href="#" class="dgt-btn active book-btn custom-request" data-pid="' . $pid . '" data-cid="' . $cid . '">Submit Custom Request</a>';
        }
        $output = [ 'msg' => $msg, 'release' => $releasetime ];

        return $output;
    }

    function retreive_map_dae_to_vest() {
        $mapPropertiesToRooms = [
            'id' => 'record_id',
            'checkIn' => 'check_in_date',
            'checkOut' => 'check_out_date',
            'Price' => 'price',
            'weekID' => 'record_id',
            'weekId' => 'record_id',
            'StockDisplay' => 'availability',
            'resort_confirmation_number' => 'resort_confirmation_number',
            'source_partner_id' => 'source_partner_id',
            'WeekType' => 'type',
            'noNights' => 'DATEDIFF(check_out_date, check_in_date)',
            'active' => 'active',
            'source_num' => 'source_num',
        ];
        $mapPropertiesToUnit = [
            'bedrooms' => 'number_of_bedrooms',
            'sleeps' => 'sleeps_total',
            'Size' => 'name',
        ];
        $mapPropertiesToResort = [
            'country' => 'Country',
            'region' => 'Region',
            'locality' => 'Town',
            'resortName' => 'ResortName',
        ];
        $mapPropertiesToResort = [
            'Country' => 'Country',
            'Region' => 'Region',
            'Town' => 'Town',
            'ResortName' => 'ResortName',
            'ImagePath1' => 'ImagePath1',
            'AlertNote' => 'AlertNote',
            'AdditionalInfo' => 'AdditionalInfo',
            'HTMLAlertNotes' => 'HTMLAlertNotes',
            'ResortID' => 'ResortID',
            'gprID' => 'gpxRegionID',

        ];
        $mapRoomToPartner = [
            '',
        ];


        $output['roomTable'] = [
            'alias' => 'a',
            'table' => 'wp_room',
        ];
        $output['unitTable'] = [
            'alias' => 'c',
            'table' => 'wp_unit_type',
        ];
        $output['resortTable'] = [
            'alias' => 'b',
            'table' => 'wp_resorts',
        ];
        foreach ( $mapPropertiesToRooms as $key => $value ) {
            if ( $key == 'noNights' ) {
                $output['joinRoom'][] = $value . ' as ' . $key;
            } else {
                $output['joinRoom'][] = $output['roomTable']['alias'] . '.' . $value . ' as ' . $key;
            }
        }
        foreach ( $mapPropertiesToUnit as $key => $value ) {
            $output['joinUnit'][] = $output['unitTable']['alias'] . '.' . $value . ' as ' . $key;
        }
        foreach ( $mapPropertiesToResort as $key => $value ) {
            $output['joinResort'][] = $output['resortTable']['alias'] . '.' . $value . ' as ' . $key;
        }

        return $output;
    }

    function DAEReleaseWeek( $inputMembers ) {
        global $wpdb;

        $wpdb->update( 'wp_gpxPreHold', [ 'released' => 1 ], [ 'weekId' => $inputMembers['WeekID'] ] );
    }

    function DAECompleteBooking( $post ) {
        global $wpdb;

        $sf = Salesforce::getInstance();

        $sql = $wpdb->prepare( "SELECT DISTINCT propertyID, data FROM wp_cart WHERE cartID=%s", $post['cartID'] );
        $carts = $wpdb->get_results( $sql );

        foreach ( $carts as $cart ) {
            $cartData = json_decode( $cart->data );

            $sql = $wpdb->prepare( "SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId=%s AND cancelled IS NULL",
                $cartData->propertyID );
            $trow = $wpdb->get_var( $sql );

            if ( $trow > 0 ) {
                $wpdb->update( 'wp_room', [ 'active' => '0' ], [ 'record_id' => $cartData->propertyID ] );
                $output = [
                    'error' => 'This week is no longer available.',
                ];

                return $output;
            }

            $upgradeFee = '';
            if ( isset( $post['UpgradeFee'] ) ) {
                $upgradeFee = $post['UpgradeFee'];
            }
            $totalPerPrice = 0;
            $CPOFee = '';
            $CPO = "NotApplicable";
            $CPODAE = "NotApplicable";
            if ( ( isset( $cartData->CPOPrice ) && $cartData->CPOPrice > 0 ) || ( $post['CPO'][ $cartData->propertyID ] && ( $post['CPO'][ $cartData->propertyID ] == 'NotTaken' || $post['CPO'][ $cartData->propertyID ] == 'Taken' ) ) ) {
                $CPO = "NotTaken";
                $CPODAE = $post['CPO'][ $cartData->propertyID ];

                if ( isset( $cartData->CPOPrice ) && $cartData->CPOPrice > 0 ) {
                    $CPOFee = $cartData->CPOPrice;
                    $CPO = 'Taken';
                } else {
                    if ( isset( $post['CPOFee'] ) ) {
                        $CPOFee = $post['CPOFee'][ $cartData->propertyID ];
                    }
                    if ( isset( $post['CPO'] ) ) {
                        $CPO = $post['CPO'];
                        $CPODAE = $post['CPO'][ $cartData->propertyID ];
                    }
                }
            }
            $joinedTbl = $this->retreive_map_dae_to_vest();

            /*
  * @TODO replace the SQL
  * SELECT
                a.record_id as id, a.check_in_date as checkIn, a.check_out_date as checkOut, a.price as Price, a.record_id as weekID, a.record_id as weekId, a.availability as StockDisplay, a.resort_confirmation_number as resort_confirmation_number, a.source_partner_id as source_partner_id, a.type as WeekType, DATEDIFF(check_out_date, check_in_date) as noNights, a.active as active, a.source_num as source_num,
                b.Country as Country, b.Region as Region, b.Town as Town, b.ResortName as ResortName, b.ImagePath1 as ImagePath1, b.AlertNote as AlertNote, b.AdditionalInfo as AdditionalInfo, b.HTMLAlertNotes as HTMLAlertNotes, b.ResortID as ResortID, b.gpxRegionID as gprID,
                c.number_of_bedrooms as bedrooms, c.sleeps_total as sleeps, c.name as Size,
                a.record_id as PID, b.id as RID
                            FROM wp_room a
                        INNER JOIN wp_resorts b ON a.resort=b .id
                        INNER JOIN wp_unit_type c ON a.unit_type=c.record_id
                            WHERE a.record_id='xxxxx'
  * */


            $sql = $wpdb->prepare( "SELECT
                " . implode( ', ', $joinedTbl['joinRoom'] ) . ",
                " . implode( ', ', $joinedTbl['joinResort'] ) . ",
                " . implode( ', ', $joinedTbl['joinUnit'] ) . ",
                " . $joinedTbl['roomTable']['alias'] . ".record_id as PID, " . $joinedTbl['resortTable']['alias'] . ".id as RID
                            FROM " . $joinedTbl['roomTable']['table'] . " " . $joinedTbl['roomTable']['alias'] . "
                        INNER JOIN " . $joinedTbl['resortTable']['table'] . " " . $joinedTbl['resortTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".resort=" . $joinedTbl['resortTable']['alias'] . " .id
                        INNER JOIN " . $joinedTbl['unitTable']['table'] . " " . $joinedTbl['unitTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".unit_type=" . $joinedTbl['unitTable']['alias'] . ".record_id
                            WHERE a.record_id=%s",
                $cartData->propertyID );
            $prop = $wpdb->get_row( $sql );
            $prop->WeekType = $cartData->weekType;

            $usermeta = (object) array_map( function ( $a ) {
                return $a[0];
            }, get_user_meta( $cartData->user ) );

            $userType = 'Owner';
            $loggedinuser = get_current_user_id();
            if ( $loggedinuser != $cartData->user ) {
                $userType = 'Agent';
            }

            if ( isset( $cartData->user_email ) ) {
                $email = $cartData->user_email;
            } elseif ( isset( $cartData->Email ) ) {
                $email = $cartData->Email;
            } elseif ( isset( $usermeta->Email ) ) {
                $email = $usermeta->Email;
            } elseif ( isset( $use ) ) {
                $mobile = 'NA';
            }
            if ( isset( $cartData->mobile ) && ! empty( $cartData->Mobile ) ) {
                $mobile = $cartData->Mobile;
            }
            $adults = '';
            $children = '';
            if ( isset( $cartData->adults ) ) {
                $adults = $cartData->adults;
            }
            if ( isset( $cartData->children ) ) {
                $children = $cartData->children;
            }

            $creditweekID = '0';
            if ( isset( $cartData->creditweekid ) && $cartData->creditweekid != 'deposit' ) {
                $creditweekID = $cartData->creditweekid;
            }

            $sProps = get_property_details_checkout( $cartData->propertyID, $cartData->user );

            if ( isset( $_POST['taxes'] ) ) {
                $sProps['taxes'][ $cartData->propertyID ] = $_POST['taxes'];
            }

            $data = [
                'functionName' => 'DAECompleteBooking',
                'externalObject' => 'Booking',
                'inputMembers' => [
                    'CreditWeekID' => $creditweekID,
                    'WeekEndpointID' => $prop->WeekEndpointID,
                    'WeekID' => $prop->weekId,
                    'GuestFirstName' => str_replace( ' &', ', ', $cartData->FirstName1 ),
                    'GuestLastName' => str_replace( ' &', ', ', $cartData->LastName1 ),
                    'GuestEmailAddress' => $email,
                    'Adults' => $adults,
                    'Children' => $children,
                    'WeekType' => $prop->WeekType,
                    'CPO' => $CPODAE,
                    'AmountPaid' => str_replace( ",", "", $post['paid'] ),
                    'DAEMemberNo' => $usermeta->DAEMemberNo,
                    'ResortID' => $prop->resortId,
                    'CurrencyCode' => $prop->Currency,
                    'GuestAddress' => str_replace( ' &', ', ', $cartData->Address1 ),
                    'GuestTown' => $cartData->Address3,
                    'GuestState' => $cartData->Address4,
                    'GuestPostCode' => $cartData->PostCode,
                    'GuestPhone' => $cartData->HomePhone,
                    //'GuestMobile'=>$mobile,
                    'GuestCountry' => $cartData->Address5,
                ],
                //                 'ExtMemberNo'=>true,
                'return' => 'BookingReceipt',
            ];

            //save the results to gpxMemberSearch database
            $sql = $wpdb->prepare( "SELECT * FROM wp_gpxMemberSearch WHERE sessionID=%s",
                $usermeta->searchSessionID );
            $sessionRow = $wpdb->get_row( $sql );
            if ( isset( $sessionRow ) ) {
                $sessionMeta = json_decode( $sessionRow->data );
            } else {
                $sessionMeta = new stdClass();
            }

            $data['inputMembers']['paid'] = $post['paid'];
            $data['inputMembers']['user_agent'] = $userType;
            $metaKey = 'bookattempt-' . $prop->id;

            $sessionMeta->$metaKey = $data['inputMembers'];
            $sessionMetaJson = json_encode( $sessionMeta );

            unset( $data['inputMembers']['user_agent'] );
            $searchCartID = '';
            if ( isset( $_COOKIE['gpx-cart'] ) ) {
                $searchCartID = $_COOKIE['gpx-cart'];
            }
            if ( isset( $sessionRow ) ) {
                $wpdb->update( 'wp_gpxMemberSearch',
                    [
                        'userID' => $cartData->user,
                        'sessionID' => $usermeta->searchSessionID,
                        'cartID' => $searchCartID,
                        'data' => $sessionMetaJson,
                    ],
                    [ 'id' => $sessionRow->id ] );
            } else {
                $wpdb->insert( 'wp_gpxMemberSearch',
                    [
                        'userID' => $cartData->user,
                        'sessionID' => $usermeta->searchSessionID,
                        'cartID' => $searchCartID,
                        'data' => $sessionMetaJson,
                    ] );
            }

            unset( $data['inputMembers']['paid'] );

            foreach ( $this->expectedBookingDetails as $ebd ) {
                $daeSend['Booking'][ $ebd ] = $data[ $ebd ];
            }


            $mtstart = $this->microtime_float();
            $mtend = $this->microtime_float();
            $seconds = $mtend - $mtstart;
            $data['inputMembers']['paid'] = $post['paid'];

            $sfCPO = '';
            if ( ( isset( $CPO ) && $CPO == 'Taken' ) || $CPOFee > 0 ) {
                $sfCPO = 1;
            }

            $discount = str_replace( ",", "", $post['pp'][ $cart->propertyID ] );


            if ( isset( $CPOFee ) && $CPOFee > 0 ) {
                $discount = $discount - $CPOFee;
            }
            if ( isset( $upgradeFee ) && $upgradeFee > 0 ) {
                $discount = $discount - $upgradeFee;
            }

            $discount = $post['fullPrice'][ $cart->propertyID ] - $discount;

            $resonType = str_replace( "Week", "", $prop->WeekType );

            $checkInDate = date( "m/d/Y", strtotime( $prop->checkIn ) );

            $sfdata = [
                'orgid' => '00D40000000MzoY',
                'recordType' => '01240000000QMdz',
                'origin' => 'Web',
                'reason' => 'GPX: Exchange Request',
                'status' => 'Open',
                'priority' => 'Standard',
                'subject' => 'New GPX Exchange Request Submission',
                'description' => 'Please validate request and complete exchange workflow in SPI and EMS',
                '00N40000002yyD8' => $cartData->HomePhone,
                //home phone
                '00N40000002yyDD' => $cartData->Mobile,
                //cell phone
                '00N40000003S0Qr' => $usermeta->DAEMemberNo,
                //EMS Account No
                '00N40000003S0Qv' => $prop->weekId,
                //EMS Ref ID

                '00N40000003S0Qt' => $cartData->FirstName1,
                //Guest First Name
                '00N40000003S0Qu' => $cartData->LastName1,
                //Guest Last Name
                '00N40000003S0Qs' => $email,
                //Guest Email
                '00N40000003S0Qw' => $prop->resortName,
                //Resort
                '00N40000003S0Qp' => $checkInDate,
                //Check-in Date
                '00N40000003S0Qq' => date( 'm/d/Y',
                    strtotime( '+' . $prop->noNights . ' days', strtotime( $checkInDate ) ) ),
                //Check-out Date
                //'00N40000002zOQB'=>$prop->noNights." nights", //Number of nights
                '00N40000003S0Qx' => $prop->bedrooms,
                //Unit Type
                '00N40000003S0Qy' => $prop->WeekType,
                //Week Type
                '00N40000003DG56' => $adults,
                //Adults
                '00N40000003DG57' => $children,
                //Children
                '00N40000003DG51' => $cartData->SpecialRequest,
                //Special Request
                '00N40000003DG4v' => $sfCPO,
                //CPO
                '00N40000003DG5A' => $$upgradeFee,
                //Upgrade Fee
                '00N40000003DG4z' => $post['fullPrice'][ $cart->propertyID ],
                //Full Price
                '00N40000003DG4y' => $discount,
                //Discount Price
                '00N40000003DG52' => $post['pp'][ $cart->propertyID ],
                //Total Price
            ];

            //if exchange without banked week
            if ( $prop->WeekType == 'ExchangeWeek' || $prop->WeekType == 'Exchange Week' ) {
                if ( $creditweekID == 0 || $creditweekID == 'undefined' ) {
                    $sfdata['00N40000003DG53'] = 1;
                } else {
                    $depositID = $creditweekID;
                }

                if ( $creditweekID == 0 || $creditweekID == 'undefined' ) {
                    $sfdata['00N40000003DG53'] = 1;
                }
                if ( isset( $cartData->deposit ) && ! empty( $cartData->deposit ) ) {
                    $sql = $wpdb->prepare( "SELECT data FROM wp_gpxDepostOnExchange WHERE id=%s",
                        $cartData->deposit );
                    $dRow = $wpdb->get_row( $sql );
                    $deposit = json_decode( $dRow->data );
                    $depositpost = (array) $deposit;
                    $depositID = $depositpost['GPX_Deposit_ID__c'];

                    $sfDepositData = [
                        'Check_In_Date__c' => date( 'Y-m-d', strtotime( $depositpost['Check_In_Date__c'] ) ),
                        'Deposit_Year__c' => date( 'Y', strtotime( $depositpost['Check_In_Date__c'] ) ),
                        'Account_Name__c' => $depositpost['Account_Name__c'],
                        'GPX_Member__c' => $cartData->user,
                        'Deposit_Date__c' => date( 'Y-m-d' ),
                        'Resort__c' => $depositpost['GPX_Resort__c'],
                        'Resort_Name__c' => $depositpost['Resort_Name__c'],
                        'Resort_Unit_Week__c' => $depositpost['Resort_Unit_Week__c'],
                        'GPX_Deposit_ID__c' => $depositpost['GPX_Deposit_ID__c'],
                        'Unit_Type__c' => $depositpost['Room_Type__c'],
                        'Member_Email__c' => $usermeta->Email,
                        'Member_First_Name__c' => $usermeta->FirstName1,
                        'Member_Last_Name__c' => $usermeta->LastName1,
                    ];


                    //does this have a coupon and is it a 2for1
                    $coupArr = (array) $cartData->coupon;
                    $thisCoupon = $coupArr[0];

                    $sql = $wpdb->prepare( "SELECT id, Name, PromoType FROM wp_specials WHERE id=%s",
                        $thisCoupon );
                    $istwofer = $wpdb->get_row( $sql );
                    if ( $istwofer->PromoType == '2 for 1 Deposit' ) {
                        $sfDepositData['Coupon__c'] == $istwofer->Name . " (" . $istwofer->id . ")";
                    }

                    $sfType = 'GPX_Deposit__c';
                    $sfObject = 'GPX_Deposit_ID__c';

                    $sfFields = [];
                    $sfFields[0] = new SObject();
                    $sfFields[0]->fields = $sfDepositData;
                    $sfFields[0]->type = $sfType;

                    $sfDepositAdd = $sf->gpxUpsert( $sfObject, $sfFields );

                    $sfdata['reason'] = 'GPX: Deposit & Exchange';
                    $sfdata['subject'] = 'New GPX Deposit & Exchange Request Submission';
                    $sfdata['description'] = 'Please validate request and complete deposit/exchange workflow in SPI and Salesforce';

                    $sfdata['00N40000002yyD8'] = $usermeta->HomePhone; //home phone
                    $sfdata['00N40000002yyDD'] = $usermeta->Mobile; //cell phone
                    $sfdata['00N40000003S0Qh'] = $usermeta->DAEMemberNo; //EMS Account No
                    $sfdata['00N40000003S0Qi'] = $deposit->Contract_ID__c; //EMS Ref ID
                    $sfdata['00N40000003S0Qj'] = $usermeta->Email; //Email
                    $sfdata['00N40000003S0Qm'] = $deposit->GPX_Resort__c; //Resort
                    $sfdata['00N40000002yqhF'] = $deposit->Resort_Unit_Week__c; //Unit Week
                    $sfdata['00N40000003S0Qg'] = date( "m/d/Y",
                        strtotime( $deposit->Check_In_Date__c ) ); //Check-in Date
                    $sfdata['00N40000003S0Qo'] = $deposit->Week_Type; //Week Type
                    $sfdata['00N40000003S0Qk'] = $usermeta->FirstName1; //Guest First Name
                    $sfdata['00N40000003S0Ql'] = $usermeta->LastName1; //Guest Last Name
                }

                //add the credit used
                $sql = $wpdb->prepare( "UPDATE wp_credit SET credit_used = credit_used + 1 WHERE id=%s", $depositID );
                $wpdb->query( $sql );

                $sql = $wpdb->prepare( "SELECT credit_used FROM wp_credit WHERE id=%s", $depositID );
                $creditsUsed = $wpdb->get_var( $sql );

                //credits_used cannot be less than 1
                if ( $creditsUsed < 1 ) {
                    $wpdb->update( 'wp_credit', [ 'credit_used' => 1 ], [ 'id' => $depositID ] );
                    $creditsUsed = 1;
                }

                //update credit in sf
                $sfCreditData['GPX_Deposit_ID__c'] = $depositID;
                $sfCreditData['Credits_Used__c'] = $creditsUsed;

                $sfWeekAdd = '';
                $sfAdd = '';
                $sfType = 'GPX_Deposit__c';
                $sfObject = 'GPX_Deposit_ID__c';


                $sfFields = [];
                $sfFields[0] = new SObject();
                $sfFields[0]->fields = $sfCreditData;
                $sfFields[0]->type = $sfType;

                $sfResortAdd = $sf->gpxUpsert( $sfObject, $sfFields );
            }

            if ( $CPO == 'NotTaken' ) {
                $CPO = '';
            }

            if ( empty( $usermeta->DAEMemberNo ) ) {
                $usermeta->DAEMemberNo = $_POST['user'];
                $usermeta->FirstName1 = $cartData->FirstName1;
                $usermeta->LastName1 = $cartData->LastName1;
            }

            $tsData = [
                'MemberNumber' => $usermeta->DAEMemberNo,
                'MemberName' => $usermeta->FirstName1 . " " . $usermeta->LastName1,
                'GuestName' => $cartData->FirstName1 . " " . $cartData->LastName1,
                'Adults' => $adults,
                'Children' => $children,
                'UpgradeFee' => $upgradeFee,
                'CPO' => $CPODAE,
                'CPOFee' => $CPOFee,
                'Paid' => $post['pp'][ $cartData->propertyID ],
                'WeekType' => ucfirst( str_replace( "week", "", strtolower( $prop->WeekType ) ) ),
                'resortName' => $prop->ResortName,
                'WeekPrice' => $sProps['propWeekPrice'],
                'Balance' => $post['balance'],
                'ResortID' => $prop->ResortID,
                'sleeps' => $prop->sleeps,
                'bedrooms' => $prop->bedrooms,
                'Size' => $prop->Size,
                'noNights' => $prop->noNights,
                'checkIn' => $prop->checkIn,
                'processedBy' => get_current_user_id(),
                'specialRequest' => $cartData->SpecialRequest,
                'actWeekPrice' => $sProps['actIndPrice'][ $prop->weekId ]['WeekPrice'],
                'actcpoFee' => $sProps['actIndPrice'][ $prop->weekId ]['cpoFee'],
                'actextensionFee' => $sProps['actIndPrice'][ $prop->weekId ]['extensionFee'],
                'actguestFee' => $sProps['actIndPrice'][ $prop->weekId ]['guestFee'],
                'actupgradeFee' => $sProps['actIndPrice'][ $prop->weekId ]['upgradeFee'],
                'acttax' => $sProps['actIndPrice'][ $prop->weekId ]['tax'],
            ];
            if ( isset( $_POST['WeekPrice'] ) ) {
                $tsData['actWeekPrice'] = $_POST['WeekPrice'];
            }
            if ( isset( $_POST['taxes'] ) ) {
                $tsData['acttax'] = $_POST['taxes']['taxAmount'];
            }
            if ( ( isset( $cartData->type ) && $cartData->type > 'extension' ) || isset( $cartData->creditextensionfee ) ) {
                $tsData['actextensionFee'] = $cartData->fee;
                $tsData['creditextensionfee'] = $cartData->fee;
            }
            if ( isset( $cartData->late_deposit_fee ) && $cartData->late_deposit_fee > 0 ) {
                $tsData['lateDepositFee'] = $cartData->late_deposit_fee;
                $tsData['actlatedepositFee'] = $cartData->late_deposit_fee;
            }
            if ( isset( $cartData->promoName ) && ! empty( $cartData->promoName ) ) {
                $tsData['promoName'] = $cartData->promoName;
            }
            if ( isset( $cartData->discount ) && ! empty( $cartData->discount ) ) {
                $tsData['discount'] = str_replace( ",", "", $cartData->discount );
            }
            if ( isset( $cartData->creditweekid ) && ! empty( $cartData->creditweekid ) ) {
                if ( $cartData->creditweekid == 'deposit' ) {
                    $cartData->creditweekid = $depositpost['GPX_Deposit_ID__c'];
                }
                $tsData['creditweekid'] = $cartData->creditweekid;
            }

            if ( isset( $cartData->coupon ) && ! empty( $cartData->coupon ) ) {
                $tsData['coupon'] = $cartData->coupon;
            }
            if ( isset( $post['couponDiscount'] ) && ! empty( $post['couponDiscount'] ) ) {
                $tsData['couponDiscount'] = $post['couponDiscount'];
            }

            if ( isset( $cartData->GuestFeeAmount ) && $cartData->GuestFeeAmount == 1 ) {
                if ( isset( $sProps['indGuestFeeAmount'][ $cartData->propertyID ] ) && ! empty( $sProps['indGuestFeeAmount'][ $cartData->propertyID ] ) ) {
                    $tsData['GuestFeeAmount'] = $sProps['indGuestFeeAmount'][ $cartData->propertyID ];
                }
            }
            if ( isset( $sProps['taxes'][ $cartData->propertyID ] ) && ! empty( $sProps['taxes'][ $cartData->propertyID ] ) ) {
                $tsData['taxCharged'] = $sProps['taxes'][ $cartData->propertyID ]['taxAmount'];
                $tsData['acttax'] = $tsData['taxCharged'];
            }

            if ( isset( $sProps['occForActivity'][ $cartData->propertyID ] ) && isset( $_POST['ownerCreditCoupon'] ) ) {
                foreach ( $sProps['occForActivity'][ $cartData->propertyID ] as $occK => $occV ) {
                    $occAmt[] = $occV;
                    $occID[] = $occK;
                    $occActivities[] = [
                        'couponID' => $occK,
                        'activity' => 'transaction',
                        'amount' => $occV,
                        'userID' => $cartData->user,
                    ];
                }

                $tsData['ownerCreditCouponID'] = implode( ",", $occID );
                $tsData['ownerCreditCouponAmount'] = array_sum( $occAmt );
            }
            if ( empty( $prop->resortId ) ) {
                $prop->resortId = $prop->RID;
            }
            $ts = [
                'cartID' => $post['cartID'],
                'transactionType' => 'booking',
                'userID' => $cartData->user,
                'resortID' => $prop->ResortID,
                'weekId' => $prop->weekId,
                'check_in_date' => $prop->checkIn,
                'depositID' => $cartData->deposit,
                'paymentGatewayID' => '',
                'data' => json_encode( $tsData ),
                'transactionData' => json_encode( $tsData ),
                'returnTime' => $seconds,
            ];

            if ( isset( $depositID ) && ! empty( $depositID ) ) {
                $ts['depositID'] = $depositID;
            }

            $wpdb->insert( 'wp_gpxTransactions', $ts );
            $tranactionID = $wpdb->insert_id;

            $wpdb->update( 'wp_room', [ 'active' => 0 ], [ 'record_id' => $prop->weekId ] );


            //is this a trade partner
            $tpSQL = $wpdb->prepare( "SELECT record_id, debit_id, debit_balance FROM wp_partner WHERE user_id=%s",
                $cartData->user );
            $tp = $wpdb->get_row( $tpSQL );

            if ( ! empty( $tp ) ) {
                //debit the partner
                $debit = $ts;
                unset( $debit['data'] );
                $tpDebit = $tsData['Paid'];
                $debit['transactionID'] = $tranactionID;
                $pdb = [
                    'user' => $cartData->user,
                    'data' => json_encode( $debit ),
                    'amount' => $tpDebit,
                ];

                $wpdb->insert( 'wp_partner_debit_balance', $pdb );

                $debitID = json_decode( $tp->debit_id, true );
                $debitID[] = $wpdb->insert_id;

                $debitBalance = $tp->debit_balance + $tsData['Paid'];

                $debited = [
                    'debit_id' => json_encode( $debitID ),
                    'debit_balance' => $debitBalance,
                ];
                $wpdb->update( 'wp_partner', $debited, [ 'record_id' => $tp->record_id ] );
            }

            $wpdb->update( 'wp_gpxDepostOnExchange',
                [ 'transactionID' => $tranactionID ],
                [ 'id' => $cartData->deposit ] );

            //send to SF
            $sftid = $this->transactiontosf( $tranactionID );

            //if owner credit coupon used then add transaction detail to database
            if ( isset( $sProps['indCartOCCreditUsed'][ $cartData->propertyID ] ) && isset( $_POST['ownerCreditCoupon'] ) ) {
                foreach ( $occActivities as $occActivity ) {
                    $occActivity['xref'] = $tranactionID;
                    $wpdb->insert( 'wp_gpxOwnerCreditCoupon_activity', $occActivity );
                }
            }
            //if auto coupon was used let's set that now
            if ( isset( $sProps['couponTemplate'] ) && ! empty( $sProps['couponTemplate'] ) ) {
                $sql = "SELECT id FROM wp_gpxAutoCoupon ORDER BY id desc";
                $nc = $wpdb->get_row( $sql );
                $nextNum = 0;
                if ( ! empty( $nc ) ) {
                    $nextNum = $nc->id;
                }
                //userid + last 5 alpha numberic characters of a hash of the next ID.
                $couponHash = $cartData->user . substr( preg_replace( "/[^a-zA-Z0-9]+/",
                        "",
                        wp_hash_password( $nextNum + 1 ) ),
                        - 5 );

                $ac = [
                    'user_id' => $cartData->user,
                    'transaction_id' => $tranactionID,
                    'coupon_id' => $sProps['couponTemplate'],
                    'coupon_hash' => $couponHash,
                ];
                $wpdb->insert( 'wp_gpxAutoCoupon', $ac );
            }
            if ( isset( $sProps['taxes'][ $cartData->propertyID ] ) && ! empty( $sProps['taxes'][ $cartData->propertyID ] ) ) {
                $wp_gpxTaxAudit = [
                    'transactionDate' => date( 'Y-m-d h:i:s' ),
                    'emsID' => $usermeta->DAEMemberNo,
                    'resortID' => $prop->resortId,
                    'arrivalDate' => date( 'Y-m-d', strtotime( $prop->checkIn ) ),
                    'unitType' => $prop->WeekType,
                    'transactionType' => 'DAECompleteBooking',
                    'baseAmount' => $totalPerPrice,
                    'taxAmount' => $sProps['taxes'][ $cartData->propertyID ]['taxAmount'],
                    'gpxTaxID' => $sProps['taxes'][ $cartData->propertyID ]['taxID'],
                ];
                $wpdb->insert( 'wp_gpxTaxAudit', $wp_gpxTaxAudit );
            }

            //update the coresponding custom request (if applicable)
            $sql = $wpdb->prepare( "SELECT id FROM wp_gpxCustomRequest WHERE userID=%d AND matched LIKE %s",
                [ $cartData->user, '%' . $wpdb->esc_like( $prop->id ) . '%' ] );
            $results = $wpdb->get_results( $sql );
            if ( ! empty( $results ) ) {
                foreach ( $results as $result ) {
                    $wpdb->update( 'wp_gpxCustomRequest',
                        [ 'matchConverted' => $tranactionID ],
                        [ 'id' => $result->id ] );
                }
            }

            if ( isset( $cartData->coupon ) && ! empty( $cartData->coupon ) ) {
                foreach ( $cartData->coupon as $coupon ) {
                    $sql = $wpdb->prepare( "UPDATE wp_specials SET redeemed=redeemed + 1 WHERE id=%s", $coupon );
                    $wpdb->query( $sql );

                    $wpdb->insert( 'wp_redeemedCoupons', [ 'userID' => $cartData->user, 'specialID' => $coupon ] );


                    //auto coupon used
                    if ( isset( $cartData->acHash ) && ! empty( $cartData->acHash ) ) {
                        $wpdb->update( 'wp_gpxAutoCoupon',
                            [ 'used' => 1 ],
                            [ 'user_id' => $cartData->user, 'coupon_hash' => $cartData->acHash ] );
                    }
                }
            }
        }

        $output['ReturnCode'] = 'A';

        return $output;
    }

    /**
     * @param array{
     *     cartID: string,
     *     paid: float,
     *     UpgradeFee?: float|int,
     *     CPO?: array<int, string>,
     *     CPOFee?: array<int, int|float>,
     *     paymentID: int,
     *     pp: array<int, int|float>,
     *     fullPrice: array<int, int|float>,
     *     balance: mixed,
     *     ownerCreditCoupon: string
     * } $post
     *
     * @return array{ReturnMessage: string, error: string}
     */
    function DAEPayAndCompleteBooking( array $post ): array {
        global $wpdb;

        $sf = Salesforce::getInstance();
        $charged = false;

        $bookingDisabledActive = (bool) get_option( 'gpx_booking_disabled_active' );
        if ( $bookingDisabledActive ) {
            // this is disabled then don't do anything else
            if ( is_user_logged_in() ) {
                if ( gpx_user_has_role( 'gpx_member' ) ) {
                    return [ 'ReturnMessage' => get_option( 'gpx_booking_disabled_msg' ) ];
                }
            }
        }

        $sql = $wpdb->prepare( "SELECT DISTINCT propertyID,weekID, data FROM wp_cart WHERE cartID=%s", $post['cartID'] );
        $carts = $wpdb->get_results( $sql );
        foreach ( $carts as $cart ) {
            $cartData = json_decode( $cart->data );
            $propertyID = $cart->propertyID ?? $cart->weekID ?? $cartData->propertyID ?? null;
            $cartData->propertyID = $propertyID;

            $sql = $wpdb->prepare( "SELECT COUNT(id) as tcnt FROM wp_gpxTransactions WHERE weekId=%s AND cancelled IS NULL", $propertyID );
            $transactions_count = (int) $wpdb->get_var( $sql );

            if ( $transactions_count > 0 ) {
                $wpdb->update( 'wp_room', [ 'active' => '0' ], [ 'record_id' => $propertyID ] );

                return [
                    'error' => 'This week is no longer available.',
                ];
            }

            $upgradeFee = $post['UpgradeFee'] ?? 0;
            $CPOFee = 0;
            $CPO = "NotApplicable";
            $CPODAE = "NotApplicable";
            if ( ( isset( $cartData->CPOPrice ) && $cartData->CPOPrice > 0 ) || ( $post['CPO'][ $cartData->propertyID ] && ( $post['CPO'][ $cartData->propertyID ] == 'NotTaken' || $post['CPO'][ $cartData->propertyID ] == 'Taken' ) ) ) {
                $CPO = "NotTaken";
                $CPODAE = $post['CPO'][ $cartData->propertyID ];

                if ( isset( $cartData->CPOPrice ) && $cartData->CPOPrice > 0 ) {
                    $CPOFee = $cartData->CPOPrice;
                    $CPO = 'Taken';
                } else {
                    if ( isset( $post['CPOFee'] ) ) {
                        $CPOFee = $post['CPOFee'][ $cartData->propertyID ];
                    }
                    if ( isset( $post['CPO'] ) ) {
                        $CPO = $post['CPO'];
                        $CPODAE = $post['CPO'][ $cartData->propertyID ];
                    }
                }
            }

            $prop = WeekRepository::instance()->get_property($cartData->propertyID);
            $prop->WeekType = $cartData->weekType;
            $usermeta = gpx_get_usermeta($cartData->user);

            $userType = get_current_user_id() === $cartData->user ? 'Owner' : 'Agent';
            $email = $cartData->user_email ?? $cartData->Email ?? gpx_get_user_email( $cartData->user );
            $mobile = $cartData->Mobile ?? 'NA';
            $adults = $cartData->adults ?? 0;
            $children = $cartData->children ?? 0;
            $creditweekID = ( isset( $cartData->creditweekid ) && $cartData->creditweekid != 'undefined' && $cartData->creditweekid != 'deposit' ) ? $cartData->creditweekid : '0';
            $currencyCode = $prop->Currency ?? 'USD';

            $sProps = get_property_details_checkout(
                $cartData->user,
                $cartData->propertyID,
                $cartData->user,
                $cartData->user
            );

            //charge the full amount but only charge it once
            if ( ! $charged ) {
                $shift4 = new Shiftfour( $this->uri, $this->dir );

                $paymentID = is_array( $post['paymentID'] ) ? Arr::first( array_filter( $post['paymentID'] ) ) : $post['paymentID'];
                //charge the full amount
                $sql = $wpdb->prepare( "SELECT i4go_responsecode, i4go_uniqueid FROM wp_payments WHERE id=%d", $paymentID );
                $i4go = $wpdb->get_row( $sql );
                if ( $i4go->i4go_responsecode != 1 ) {
                    return ['error' => 'Invalid Credit Card'];
                }

                $i4goToken = $i4go->i4go_uniqueid;
                //add this token data to this user
                $sft = unserialize( $usermeta->shiftfourtoken );
                if ( ! empty( $sft ) && is_array( $sft ) ) {
                    $sft[] = [
                        'token' => $i4goToken,
                    ];
                } else {
                    $sft = [
                        'token' => $i4goToken,
                    ];
                }
                update_user_meta( $cartData->user, 'shiftfourtoken', serialize( $sft ) );

                $fullPriceForPayment = number_format( array_sum( $sProps['indPrice'] ?? [0] ), 2, '.', '' );
                $totalTaxCharged = 0.00;

                if ( ! empty( $sProps['taxes'] ) ) {
                    $totalTaxCharged += array_sum(array_column($sProps['taxes'], 'taxAmount'));
                }
                $paymentRef = $post['paymentID'];

                $start = microtime(true);
                $paymentDetails = $shift4->shift_sale(
                    $i4goToken,
                    $fullPriceForPayment,
                    $totalTaxCharged,
                    $paymentRef,
                    $cartData->user
                );
                $end = microtime(true);
                $seconds = $end - $start;

                $paymentDetailsArr = json_decode( $paymentDetails, true );

                if ( defined( 'SHIFT4_FAKE_SUCCESS' ) && SHIFT4_FAKE_SUCCESS && isset( $paymentDetailsArr['result'][0]['transaction']['responseCode'] ) && $paymentDetailsArr['result'][0]['transaction']['responseCode'] === 'D' ) {
                    // the sandbox account is returning declined responses for test credit card numbers.
                    // defining a SHIFT4_FAKE_SUCCESS constant to true value will allow a declined response to be treated as approved.
                    $paymentDetailsArr['result'][0]['transaction']['responseCode'] = 'A';
                }

                if ( isset( $paymentDetailsArr['result'][0]['error'] ) ) {
                    //this is an error how should we proccess
                    if ( $paymentDetailsArr['result'][0]['error']['primaryCode'] == 9961 ) {
                        sleep( 5 );
                        $start = microtime(true);
                        $failedPayment = $shift4->shift_invioce( $post['paymentID'] );
                        $end = microtime(true);
                        $seconds = $end - $start;
                        $failedPaymentDetailsArr = json_decode( $failedPayment, true );
                        //do we have an invoice?
                        if ( $failedPaymentDetailsArr['result'][0]['error']['primaryCode'] == 9815 ) {
                            //we don't have an invoice -- log this error
                            $wpdb->update( 'wp_payments',
                                [ 'i4go_responsetext' => json_encode( $failedPaymentDetailsArr['result'][0]['error'] ) ],
                                [ 'id' => $post['paymentID'] ] );
                            $wpdb->insert( 'wp_gpxFailedTransactions', [
                                'cartID' => $post['cartID'],
                                'data' => json_encode( $failedPaymentDetailsArr['result'][0]['error'] ),
                                'returnTime' => $seconds,
                            ] );

                            return [ 'error' => 'Please try again later.' ];
                        }
                        $wpdb->update( 'wp_payments',
                            [ 'i4go_responsetext' => json_encode( $failedPaymentDetailsArr['result'][0]['error'] ) ],
                            [ 'id' => $post['paymentID'] ] );
                        $wpdb->insert( 'wp_gpxFailedTransactions', [
                            'cartID' => $post['cartID'],
                            'data' => json_encode( $failedPayment ),
                            'returnTime' => $seconds ?? 0,
                        ] );

                        return [ 'ReturnMessage' => 'Please try again later.' ];
                    }
                }

                $output['ReturnCode'] = $paymentDetailsArr['result'][0]['transaction']['responseCode'];
                $output['PaymentReg'] = ltrim( $paymentDetailsArr['result'][0]['transaction']['invoice'], '0' );
                $charged = true;
            }

            $totalPerPrice = number_format( $sProps['indPrice'][ $cartData->propertyID ], 2, '.', '' );
            $Address = $post['billing_address'] . ", " . $post['billing_city'] . ", " . $post['billing_state'] . ", " . ( $post['billing_country'] ?? 'US' );

            //save the results to gpxMemberSearch database
            $sql = $wpdb->prepare( "SELECT * FROM wp_gpxMemberSearch WHERE sessionID=%s",
                $usermeta->searchSessionID );
            $sessionRow = $wpdb->get_row( $sql );
            $sessionMeta = $sessionRow ? json_decode( $sessionRow->data ) : new stdClass();

            $data['inputMembers']['paid'] = $post['paid'];
            $data['inputMembers']['user_agent'] = $userType;
            $metaKey = 'bookattempt-' . $prop->id;
            $truncateCC = substr( $data['inputMembers']['Payment']['CardNo'] ?? '', - 4 );
            $dbIM = $data['inputMembers'] ?? [];
            $dbIM['Payment']['CardNo'] = $truncateCC;
            $sessionMeta->$metaKey = $dbIM;

            $sessionMetaJson = json_encode( $sessionMeta );
            unset( $data['inputMembers']['user_agent'] );

            $searchCartID = $_COOKIE['gpx-cart'] ?? '';
            if ( $sessionRow ) {
                $wpdb->update( 'wp_gpxMemberSearch',
                    [
                        'userID' => $cartData->user,
                        'sessionID' => $usermeta->searchSessionID,
                        'cartID' => $searchCartID,
                        'data' => $sessionMetaJson,
                    ],
                    [ 'id' => $sessionRow->id ] );
            } else {
                $wpdb->insert( 'wp_gpxMemberSearch',
                    [
                        'userID' => $cartData->user,
                        'sessionID' => $usermeta->searchSessionID,
                        'cartID' => $searchCartID,
                        'data' => $sessionMetaJson,
                    ] );
            }

            unset( $data['inputMembers']['paid'] );

            foreach ( $this->expectedPaymentDetails as $epd ) {
                $daeSend['Payment'][ $epd ] = $data[ $epd ] ?? null;
            }
            foreach ( $this->expectedBookingDetails as $ebd ) {
                $daeSend['Booking'][ $ebd ] = $data[ $ebd ] ?? null;
            }

            $data['inputMembers']['paid'] = $post['paid'];

            //save the transaction
            if ( in_array( $output['ReturnCode'] ?? '', [ '0', '105', '106', 'A', 'a', ] ) ) {
                //release the hold
                $this->DAEReleaseWeek( [ 'WeekID' => $prop->weekId ] );

                $sfCPO =  ( ( isset( $CPODAE ) && $CPODAE == 'Taken' ) || $CPOFee > 0 ) ? 1 : '';
                $discount = $post['pp'][ $cart->propertyID ];

                if ( $CPOFee > 0 ) {
                    $discount -= $CPOFee;
                }
                if ( $upgradeFee > 0 ) {
                    $discount -= $upgradeFee;
                }

                $discount = $post['fullPrice'][ $cart->propertyID ] - $discount;
                $checkInDate = date( "m/d/Y", strtotime( $prop->checkIn ) );

                $sfdata = [
                    'orgid' => '00D40000000MzoY',
                    'recordType' => '01240000000QMdz',
                    'origin' => 'Web',
                    'reason' => 'GPX: Exchange Request',
                    'status' => 'Open',
                    'priority' => 'Standard',
                    'subject' => 'New GPX Exchange Request Submission',
                    'description' => 'Please validate request and complete exchange workflow in SPI and EMS',
                    '00N40000002yyD8' => $cartData->HomePhone ?? null, //home phone
                    '00N40000002yyDD' => $cartData->Mobile ?? null, //cell phone
                    '00N40000003S0Qr' => $usermeta->DAEMemberNo, //EMS Account No
                    '00N40000003S0Qv' => $prop->weekId, //EMS Ref ID
                    '00N40000003S0Qt' => $cartData->FirstName1, //Guest First Name
                    '00N40000003S0Qu' => $cartData->LastName1, //Guest Last Name
                    '00N40000003S0Qs' => $email, //Guest Email
                    '00N40000003S0Qw' => $prop->ResortName ?? null, //Resort
                    '00N40000003S0Qp' => $checkInDate, //Check-in Date
                    '00N40000003S0Qq' => date( 'm/d/Y',
                        strtotime( '+' . $prop->noNights . ' days',
                            strtotime( $checkInDate ) ) ), //Check-out Date

                    '00N40000003S0Qx' => $prop->bedrooms, //Unit Type
                    '00N40000003S0Qy' => $prop->WeekType, //Week Type
                    '00N40000003DG56' => $adults, //Adults
                    '00N40000003DG57' => $children, //Children
                    '00N40000003DG51' => $cartData->SpecialRequest, //Special Request
                    '00N40000003DG4v' => $sfCPO, //CPO
                    '00N40000003DG5A' => $upgradeFee, //Upgrade Fee
                    '00N40000003DG4z' => $post['fullPrice'][ $cart->propertyID ], //Full Price
                    '00N40000003DG4y' => $discount, //Discount Price
                    '00N40000003DG52' => $post['pp'][ $cart->propertyID ], //Total Price
                ];

                //if exchange without banked week
                if ( $prop->WeekType == 'ExchangeWeek' || $prop->WeekType == 'Exchange Week' ) {
                    if ( $creditweekID == 0 || $creditweekID == 'undefined' ) {
                        $sfdata['00N40000003DG53'] = 1;
                    } else {
                        $depositID = $creditweekID;
                    }

                    if ( ! empty( $cartData->deposit ) ) {
                        $sql = $wpdb->prepare( "SELECT data FROM wp_gpxDepostOnExchange WHERE id=%s", $cartData->deposit );
                        $depositpost = json_decode( $wpdb->get_var( $sql ), true );
                        $depositID = $depositpost['GPX_Deposit_ID__c'];

                        $sfDepositData = [
                            'Check_In_Date__c' => date( 'Y-m-d', strtotime( $depositpost['Check_In_Date__c'] ) ),
                            'Deposit_Year__c' => date( 'Y', strtotime( $depositpost['Check_In_Date__c'] ) ),
                            'Account_Name__c' => $depositpost['Account_Name__c'],
                            'GPX_Member__c' => $cartData->user,
                            'Deposit_Date__c' => date( 'Y-m-d' ),
                            'Resort__c' => $depositpost['GPX_Resort__c'],
                            'Resort_Name__c' => $depositpost['Resort_Name__c'],
                            'Resort_Unit_Week__c' => $depositpost['Resort_Unit_Week__c'],
                            'GPX_Deposit_ID__c' => $depositpost['GPX_Deposit_ID__c'],
                            'Unit_Type__c' => $depositpost['Room_Type__c'],
                            'Member_Email__c' => $email,
                            'Member_First_Name__c' => $usermeta->FirstName1,
                            'Member_Last_Name__c' => $usermeta->LastName1,
                        ];
                        $thisCoupon = Arr::first((array) $cartData->coupon);

                        $sql = $wpdb->prepare( "SELECT id, Name, PromoType FROM wp_specials WHERE id=%s", $thisCoupon );
                        $istwofer = $wpdb->get_row( $sql );
                        if ( $istwofer->PromoType == '2 for 1 Deposit' ) {
                            $sfDepositData['Coupon__c'] = $istwofer->Name . " (" . $istwofer->id . ")";
                        }

                        $sfFields = new SObject();
                        $sfFields->fields = $sfDepositData;
                        $sfFields->type = 'GPX_Deposit__c';
                        $sf->gpxUpsert( 'GPX_Deposit_ID__c', [$sfFields] );

                        $sfdata['reason'] = 'GPX: Deposit & Exchange';
                        $sfdata['subject'] = 'New GPX Deposit & Exchange Request Submission';
                        $sfdata['description'] = 'Please validate request and complete deposit/exchange workflow in SPI and Salesforce';

                        $sfdata['00N40000002yyD8'] = $usermeta->HomePhone; //home phone
                        $sfdata['00N40000002yyDD'] = $usermeta->Mobile; //cell phone
                        $sfdata['00N40000003S0Qh'] = $usermeta->DAEMemberNo; //EMS Account No
                        $sfdata['00N40000003S0Qi'] = $depositpost['Contract_ID__c']; //EMS Ref ID
                        $sfdata['00N40000003S0Qj'] = $email; //Email
                        $sfdata['00N40000003S0Qm'] = $depositpost['GPX_Resort__c']; //Resort
                        $sfdata['00N40000002yqhF'] = $depositpost['Resort_Unit_Week__c']; //Unit Week
                        $sfdata['00N40000003S0Qg'] = date( "m/d/Y", strtotime( $depositpost['Check_In_Date__c'] ) ); //Check-in Date
                        $sfdata['00N40000003S0Qk'] = $usermeta->FirstName1; //Guest First Name
                        $sfdata['00N40000003S0Ql'] = $usermeta->LastName1; //Guest Last Name
                    }

                    //add the credit used
                    $sql = $wpdb->prepare( "UPDATE wp_credit SET credit_used = credit_used + 1 WHERE id=%s", $depositID );
                    $wpdb->query( $sql );

                    $sql = $wpdb->prepare( "SELECT credit_used FROM wp_credit WHERE id=%s", $depositID );
                    $creditsUsed = (int)$wpdb->get_var( $sql );

                    // credits_used cannot be less than 1
                    if ( $creditsUsed < 1 ) {
                        $wpdb->update( 'wp_credit', [ 'credit_used' => 1 ], [ 'id' => $depositID ] );
                        $creditsUsed = 1;
                    }

                    //update credit in sf
                    $sfCreditData['GPX_Deposit_ID__c'] = $depositID;
                    $sfCreditData['Credits_Used__c'] = $creditsUsed;

                    if ( isset( $cartData->creditextensionfee ) && $cartData->creditextensionfee > 0 ) {
                        $sfCreditData['Credit_Extension_Date__c'] = date( 'Y-m-d' );
                        $sfCreditData['Expiration_Date__c'] = date( 'Y-m-d', strtotime( $prop->checkIn ) );
                    }

                    $sfFields = new SObject();
                    $sfFields->fields = $sfCreditData;
                    $sfFields->type = 'GPX_Deposit__c';
                    $sf->gpxUpsert( 'GPX_Deposit_ID__c', [$sfFields] );
                    $sf->gpxLogout();
                }

                if ( $CPO == 'NotTaken' ) {
                    $CPO = '';
                }
                $tsData = [
                    'MemberNumber' => $usermeta->DAEMemberNo,
                    'MemberName' => $usermeta->FirstName1 . " " . $usermeta->LastName1,
                    'GuestName' => $cartData->FirstName1 . " " . $cartData->LastName1,
                    'Email' => $email,
                    'Adults' => $adults,
                    'Children' => $children,
                    'UpgradeFee' => $upgradeFee,
                    'CPO' => $CPODAE,
                    'CPOFee' => $CPOFee,
                    'PaymentID' => $post['paymentID'],
                    'Paid' => $post['pp'][ $cartData->propertyID ],
                    'WeekType' => ucfirst( str_replace( "week", "", strtolower( $prop->WeekType ) ) ),
                    'resortName' => $prop->ResortName,
                    'WeekPrice' => $sProps['propWeekPrice'],
                    'Balance' => $post['balance'],
                    'ResortID' => $prop->ResortID,
                    'sleeps' => $prop->sleeps,
                    'bedrooms' => $prop->bedrooms,
                    'Size' => $prop->Size,
                    'noNights' => $prop->noNights,
                    'checkIn' => $prop->checkIn,
                    'processedBy' => get_current_user_id(),
                    'specialRequest' => $cartData->SpecialRequest,
                    'actWeekPrice' => $sProps['actIndPrice'][ $prop->weekId ]['WeekPrice'] ?? 0.00,
                    'actcpoFee' => $sProps['actIndPrice'][ $prop->weekId ]['cpoFee'] ?? 0.00,
                    'actextensionFee' => $sProps['actIndPrice'][ $prop->weekId ]['extensionFee'] ?? 0.00,
                    'actguestFee' => $sProps['actIndPrice'][ $prop->weekId ]['guestFee'] ?? 0.00,
                    'actupgradeFee' => $sProps['actIndPrice'][ $prop->weekId ]['upgradeFee'] ?? 0.00,
                    'acttax' => $sProps['actIndPrice'][ $prop->weekId ]['tax'] ?? 0.00,
                ];

                if ( $creditweekID != 0 ) {
                    $tdData['creditweekID'] = $creditweekID;
                }

                if ( isset( $cartData->late_deposit_fee ) && $cartData->late_deposit_fee > 0 ) {
                    $tsData['lateDepositFee'] = $cartData->late_deposit_fee;
                    $tsData['actlatedepositFee'] = $cartData->late_deposit_fee;
                }

                if ( ! empty( $cartData->promoName ) ) {
                    $tsData['promoName'] = $cartData->promoName;
                }
                if ( ! empty( $cartData->creditweekid ) ) {
                    if ( $cartData->creditweekid == 'deposit' && isset($depositpost['GPX_Deposit_ID__c']) ) {
                        $cartData->creditweekid = $depositpost['GPX_Deposit_ID__c'];
                    }
                    $tsData['creditweekid'] = $cartData->creditweekid;
                }
                if ( ! empty( $cartData->discount ) ) {
                    $tsData['discount'] = str_replace( ",", "", $cartData->discount );
                }
                if ( ! empty( $cartData->coupon ) ) {
                    $tsData['coupon'] = $cartData->coupon;
                }
                if ( ! empty( $post['couponDiscount'] ) ) {
                    $tsData['couponDiscount'] = str_replace( ",", "", $post['couponDiscount'] );
                }
                if ( isset( $cartData->GuestFeeAmount ) && $cartData->GuestFeeAmount == 1 ) {
                    if ( isset( $sProps['discGuestFee'][ $cartData->propertyID ] ) && ! empty( $sProps['discGuestFee'][ $cartData->propertyID ] ) ) {
                        $tsData['GuestFeeAmount'] = $sProps['discGuestFee'][ $cartData->propertyID ];
                    }
                }
                if ( ! empty( $sProps['taxes'][ $cartData->propertyID ] ) ) {
                    $tsData['taxCharged'] = $sProps['taxes'][ $cartData->propertyID ]['taxAmount'];
                    $tsData['acttax'] = $tsData['taxCharged'];
                }

                if ( isset( $sProps['occForActivity'][ $cartData->propertyID ] ) && isset( $_POST['ownerCreditCoupon'] ) ) {
                    foreach ( $sProps['occForActivity'][ $cartData->propertyID ] as $occK => $occV ) {
                        $occAmt[] = $occV;
                        $occID[] = $occK;
                        $occActivities[] = [
                            'couponID' => $occK,
                            'activity' => 'transaction',
                            'amount' => $occV,
                            'userID' => $cartData->user,
                        ];
                    }

                    $tsData['ownerCreditCouponID'] = implode( ",", $occID );
                    $tsData['ownerCreditCouponAmount'] = array_sum( $occAmt );
                }
                if ( isset( $cartData->creditextensionfee ) ) {
                    $tsData['creditextensionfee'] = $cartData->creditextensionfee;
                }

                $ts = [
                    'cartID' => $post['cartID'],
                    'transactionType' => 'booking',
                    'sessionID' => $usermeta->searchSessionID,
                    'userID' => $cartData->user,
                    'resortID' => $prop->ResortID,
                    'weekId' => $prop->weekId,
                    'check_in_date' => $prop->checkIn,
                    'depositID' => $cartData->deposit ?? null,
                    'paymentGatewayID' => $paymentRef,
                    'data' => json_encode( $tsData ),
                    'transactionData' => json_encode( $tsData ),
                    'returnTime' => $seconds,
                ];
                $wpdb->insert( 'wp_gpxTransactions', $ts );

                $tranactionID = $wpdb->insert_id;

                if(isset($cartData->deposit)) {
                    $wpdb->update( 'wp_gpxDepostOnExchange',
                        [ 'transactionID' => $tranactionID ],
                        [ 'id' => $cartData->deposit ] );
                }

                //send to SF
                $this->transactiontosf( $tranactionID );

                //if owner credit coupon used then add transaction detail to database
                if ( isset( $sProps['indCartOCCreditUsed'][ $cartData->propertyID ] ) && isset( $post['ownerCreditCoupon'] ) ) {
                    foreach ( $occActivities as $occActivity ) {
                        $occActivity['xref'] = $tranactionID;
                        $wpdb->insert( 'wp_gpxOwnerCreditCoupon_activity', $occActivity );
                    }
                }
                //if auto coupon was used let's set that now
                if ( ! empty( $sProps['couponTemplate'] ) ) {
                    $sql = "SELECT id FROM wp_gpxAutoCoupon ORDER BY id desc";
                    $nc = $wpdb->get_row( $sql );
                    $nextNum = 0;
                    if ( ! empty( $nc ) ) {
                        $nextNum = $nc->id;
                    }
                    //userid + last 5 alpha numberic characters of a hash of the next ID.
                    $couponHash = $cartData->user . substr( preg_replace( "/[^a-zA-Z0-9]+/",
                            "",
                            wp_hash_password( $nextNum + 1 ) ),
                            - 5 );

                    $ac = [
                        'user_id' => $cartData->user,
                        'transaction_id' => $tranactionID,
                        'coupon_id' => $sProps['couponTemplate'],
                        'coupon_hash' => $couponHash,
                    ];
                    $wpdb->insert( 'wp_gpxAutoCoupon', $ac );
                }
                if ( isset( $sProps['taxes'][ $cartData->propertyID ] ) && ! empty( $sProps['taxes'][ $cartData->propertyID ] ) ) {
                    $wp_gpxTaxAudit = [
                        'transactionDate' => date( 'Y-m-d h:i:s' ),
                        'emsID' => $usermeta->DAEMemberNo,
                        'resortID' => $prop->ResortID,
                        'arrivalDate' => date( 'Y-m-d', strtotime( $prop->checkIn ) ),
                        'unitType' => $prop->WeekType,
                        'transactionType' => 'DAECompleteBooking',
                        'baseAmount' => $totalPerPrice,
                        'taxAmount' => $sProps['taxes'][ $cartData->propertyID ]['taxAmount'],
                        'gpxTaxID' => $sProps['taxes'][ $cartData->propertyID ]['taxID'],
                    ];
                    $wpdb->insert( 'wp_gpxTaxAudit', $wp_gpxTaxAudit );
                }

                //update the coresponding custom request (if applicable)
                $sql = $wpdb->prepare( "SELECT id FROM wp_gpxCustomRequest WHERE userID=%d AND matched LIKE %s",
                    [ $cartData->user, '%' . $wpdb->esc_like( $prop->id ) . '%' ] );
                $results = $wpdb->get_results( $sql );
                if ( $results ) {
                    foreach ( $results as $result ) {
                        $wpdb->update( 'wp_gpxCustomRequest',
                            [ 'matchConverted' => $tranactionID ],
                            [ 'id' => $result->id ] );
                    }
                }
            } else {
                $wpdb->insert( 'wp_gpxFailedTransactions', [
                    'cartID' => $post['cartID'],
                    'userID' => $cartData->user,
                    'data' => json_encode( $paymentDetailsArr['result'][0] ),
                    'returnTime' => $seconds ?? 0,
                ] );
                $output['ReturnMessage'] = 'Your credit card could not be processed.';
            }
            if ( ! empty( $cartData->coupon ) ) {
                foreach ( $cartData->coupon as $coupon ) {
                    $sql = $wpdb->prepare("UPDATE wp_specials SET redeemed=redeemed + 1 WHERE id=%s", $coupon);
                    $wpdb->query( $sql );

                    $wpdb->insert( 'wp_redeemedCoupons', [ 'userID' => $cartData->user, 'specialID' => $coupon ] );

                    //auto coupon used
                    if ( ! empty( $cartData->acHash ) ) {
                        $wpdb->update( 'wp_gpxAutoCoupon',
                            [ 'used' => 1 ],
                            [ 'user_id' => $cartData->user, 'coupon_hash' => $cartData->acHash ] );
                    }
                }
            }
        }

        return $output ?? [];
    }

    /**
     * @param $DAEMemberNo
     * @param $cid
     *
     * @return int[][]
     * @deprecated
     */
    function DAEGetMemberCredits( $DAEMemberNo, $cid = '' ) {
        return [ [ 0 ] ];
    }

    /**
     * @param $DAEMemberNo
     *
     * @return string[]
     * @deprecated
     */
    function DAEGetMemberOwnership( $DAEMemberNo ) {
        return [ 'Error' => 'No Record' ];
    }

    /** @deprecated */
    function DAECreateWillBank( $inputMembers ) {
        return json_decode( json_encode( [] ) );
    }

    function DAEGetMemberHistory( $DAEMemberNo, $TransactionType = 'All' ) {
        global $wpdb;

        $cid = gpx_get_switch_user_cookie();
        $joinedTbl = $this->retreive_map_dae_to_vest();

        $sql = $wpdb->prepare( "SELECT
                t.data, t.datetime, t.cancelled,
                " . implode( ', ', $joinedTbl['joinRoom'] ) . ",
                " . implode( ', ', $joinedTbl['joinResort'] ) . ",
                " . implode( ', ', $joinedTbl['joinUnit'] ) . ",
                " . $joinedTbl['roomTable']['alias'] . ".record_id as PID, " . $joinedTbl['resortTable']['alias'] . ".id as RID
                    FROM wp_gpxTransactions t
                        INNER JOIN " . $joinedTbl['roomTable']['table'] . " " . $joinedTbl['roomTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".record_id=t.weekId
                        INNER JOIN " . $joinedTbl['resortTable']['table'] . " " . $joinedTbl['resortTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".resort=" . $joinedTbl['resortTable']['alias'] . " .id
                        INNER JOIN " . $joinedTbl['unitTable']['table'] . " " . $joinedTbl['unitTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".unit_type=" . $joinedTbl['unitTable']['alias'] . ".record_id
                            WHERE t.userID=%s",
            $cid );
        $rows = $wpdb->get_results( $sql, ARRAY_A );

        foreach ( $rows as $row ) {
            $data = json_decode( $row['data'] );
            $transactions[] = array_merge( $row, $data );
        }

        return $transactions;
    }

    /**
     * @param $DAEMemberNo
     * @param $ExtMemberNo
     *
     * @return array
     * @deprecated
     */
    function DAEGetAccountDetails( $DAEMemberNo = '', $ExtMemberNo = '' ) {
        return [];
    }

    /**
     * @param $MemberTypeID
     * @param $BusCatID
     *
     * @return array
     * @deprecated
     */
    function DAEGetUnitUpgradeFees( $MemberTypeID, $BusCatID ) {
        return [];
    }

    function DAEGetWeekDetails( $WeekID ) {
        global $wpdb;

        $joinedTbl = $this->retreive_map_dae_to_vest();

        $sql = $wpdb->prepare( "SELECT
                " . implode( ', ', $joinedTbl['joinRoom'] ) . ",
                " . implode( ', ', $joinedTbl['joinResort'] ) . ",
                " . implode( ', ', $joinedTbl['joinUnit'] ) . ",
                " . $joinedTbl['roomTable']['alias'] . ".record_id as PID, " . $joinedTbl['resortTable']['alias'] . ".id as RID
                    FROM " . $joinedTbl['roomTable']['table'] . " " . $joinedTbl['roomTable']['alias'] . "
                        INNER JOIN " . $joinedTbl['resortTable']['table'] . " " . $joinedTbl['resortTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".resort=" . $joinedTbl['resortTable']['alias'] . " .id
                        INNER JOIN " . $joinedTbl['unitTable']['table'] . " " . $joinedTbl['unitTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".unit_type=" . $joinedTbl['unitTable']['alias'] . ".record_id
                            WHERE " . $joinedTbl['roomTable']['alias'] . ".record_id=%s",
            $WeekID );
        $retrieve = $wpdb->get_row( $sql );


        if ( ($retrieve->source_partner_id ?? 0) > 0 ) {
            if ( $retrieve->source_num == '1' ) {
                $usermeta = gpx_get_usermeta( $retrieve->source_partner_id );
                $retrieve->source_name = $usermeta->FirstName1 . " " . $usermeta->LastName1;
                $retrieve->source_account = $usermeta->Property_Owner__c;
            } elseif ( $retrieve->source_num == '3' ) {
                $sql = $wpdb->prepare( "SELECT name, sf_account_id FROM wp_partner WHERE user_id=%s",
                    $retrieve->source_partner_id );
                $row = $wpdb->get_row( $sql );
                $retrieve->source_name = $row->name;
                $retrieve->source_account = $row->sf_account_id;
            }
        }


        return $retrieve;
    }

    /** @deprecated */
    function DAEReIssueConfirmation( $post ) {
        return null;
    }


    /**
     * @deprecated
     *
     * entire function returns on 2nd line and not used.
     *
     */
    function DAEGetResortProfile( $id, $gpxRegionID, $inputMembers, $update = '' ) {
        global $wpdb;

        $return = [ 'success' => 'Resort Updated!' ];

        $data = [
            'functionName' => 'DAEGetResortProfile',
            'inputMembers' => $inputMembers,
            'return' => 'ResortProfile',
        ];


        $output['gpxRegionID'] = $gpxRegionID;
        if ( empty( $gpxRegionID ) ) {
            $output['gpxRegionID'] = 'NA';
        }

        $sql = $wpdb->prepare( "SELECT * FROM wp_resorts WHERE ResortID=%s", $output['ResortID'] );
        $updateorinsert = $wpdb->get_row( $sql );

        if ( ( ! empty( $update ) && $update == 1 ) || ! empty( $updateorinsert ) ) {
            $wpdb->update( 'wp_resorts', $output, [ 'id' => $id ] );
        } elseif ( ! empty( $update ) && $update == 'insert' ) {
            $wpdb->insert( 'wp_resorts', $output );
            $return['id'] = $wpdb->insert_id;
        }

        return $return;
    }

    /** @deprecated */
    function missingDAEGetResortProfile( $resortID, $endpointID ) {
        return [ 'successs' => "there was an error!" ];
    }

    /** @deprecated */
    function DAEGetResortInd() {
        return [ 'success' => true ];
    }

    /** @deprecated */
    function addResortDetails() {
        return [ 'success' => 'Resort updated.' ];
    }

    function xml2array( $xmlObject, $out = [] ) {
        foreach ( (array) $xmlObject as $index => $node ) {
            $out[ $index ] = ( is_object( $node ) ) ? $this->xml2array( $node ) : $node;
        }

        return $out;
    }

    function microtime_float() {
        [ $usec, $sec ] = explode( " ", microtime() );

        return ( (float) $usec + (float) $sec );
    }

    function transactiontosf( $transactionID, $transactionType = 'transactions' ) {
        global $wpdb;

        $data = [];

        //pull from database
        $sql = $wpdb->prepare( "SELECT * FROM wp_gpxTransactions WHERE id=%s", $transactionID );
        $transactionRow = $wpdb->get_row( $sql );


        if ( empty( $transactionRow->sessionID ) ) {
            $thisisimported = true;
        }
        $weekId = $transactionRow->weekId;

        $row = json_decode( $transactionRow->data, true );

        if ( isset( $row['actextensionFee'] ) || isset( $row['creditweekid'] ) || isset( $row['creditid'] ) || ! empty( $transactionRow->depositID ) ) {
            if ( ! empty( $transactionRow->depositID ) ) {
                $sql = $wpdb->prepare( "SELECT creditID FROM wp_gpxDepostOnExchange WHERE id=%s",
                    $transactionRow->depositID );
                $crid = $wpdb->get_var( $sql );
            }
            if ( !empty($row['creditweekid']) ) {
                $crid = $row['creditweekid'];
            } elseif ( !empty($row['creditid']) ) {
                $crid = $row['creditid'];
            } elseif ( !empty($row['actextensionFee']) && !empty($row['id']) ) {
                $crid = $row['id'];
            }

            do_shortcode( '[get_credit gpxcreditid="' . $crid . '"]' );

            //get the status
            $sql = $wpdb->prepare( "SELECT sf_name, record_id, status FROM wp_credit WHERE id =%s",
                $crid );
            $cq = $wpdb->get_row( $sql );
            $transactionRow->CreditStatus = $cq->status;
            $transactionRow->CreditSFID = $cq->record_id;
            $transactionRow->CreditSFName = $cq->sf_name;
        }
        $sfRow = json_decode( $transactionRow->sfData );

        //get details from the cart
        $sql = $wpdb->prepare( "SELECT data FROM wp_cart WHERE cartID=%s", $transactionRow->cartID );
        $cRow = $wpdb->get_row( $sql );
        $cjson = json_decode( $cRow->data );

        $weekDetails = $this->DAEGetWeekDetails( $weekId );

        $skipTrans = [
            'transactionData',
            'data',
            'sfid',
            'sfData',
            'returnTime',
            'billing_address',
            'inCard_name',
            'CVV',
            'expiry_date',
            'card_number',
            'cancelledData',
        ];
        foreach ( $transactionRow as $tk => $td ) {
            if ( in_array( $tk, $skipTrans ) ) {
                continue;
            }
            $row[ $tk ] = trim( $td );
        }

        $row['source_num'] = $weekDetails->source_num ?? null;
        $row['source_name'] = $weekDetails->source_name ?? null;
        $row['source_account'] = $weekDetails->source_account ?? null;

        $mappedTransTypes['actWeekPrice'] = 'Exchange';
        $mappedTransTypes['EXCH240'] = 'Exchange';
        $mappedTransTypes['EXCH241'] = 'Exchange';
        $mappedTransTypes['TRADE250'] = 'Exchange';
        $mappedTransTypes['EXCH'] = 'Exchange';
        $mappedTransTypes['TRADEINT'] = 'Exchange';
        $mappedTransTypes['GPXPPEXCH'] = 'Exchange';
        $mappedTransTypes['EXPROMO'] = 'Exchange';
        $mappedTransTypes['INTERNALI'] = 'Exchange';
        $mappedTransTypes['IEXCH'] = 'Exchange';
        $mappedTransTypes['TRADE260'] = 'Exchange';
        $mappedTransTypes['TRADE262'] = 'Exchange';
        $mappedTransTypes['INTERNALD'] = 'Exchange';
        $mappedTransTypes['EXCHPROMO'] = 'Exchange';
        $mappedTransTypes['RC_62'] = 'Exchange';
        $mappedTransTypes['TRADE261'] = 'Exchange';
        $mappedTransTypes['TRADE251'] = 'Exchange';
        $mappedTransTypes['CPO242'] = 'CPO';
        $mappedTransTypes['CPO240'] = 'CPO';
        $mappedTransTypes['CPO241'] = 'CPO';
        $mappedTransTypes['CPOINT'] = 'CPO';
        $mappedTransTypes['CPS'] = 'CPO';
        $mappedTransTypes['ICPOINT'] = 'CPO';
        $mappedTransTypes['UNITUPG'] = 'Upgrade';
        $mappedTransTypes['Unitupg24'] = 'Upgrade';
        $mappedTransTypes['UPSELLPROMO'] = 'Upgrade';
        $mappedTransTypes['EXTEN'] = 'Extension';
        $mappedTransTypes['EXTEN24'] = 'Extension';
        $mappedTransTypes['LATEDEPGPX'] = 'Late Deposit';
        $mappedTransTypes['LATEDEP'] = 'Late Deposit';
        $mappedTransTypes['BONUS'] = 'Rental';
        $mappedTransTypes['BONUS24'] = 'Rental';
        $mappedTransTypes['INTRENTAL'] = 'Rental';
        $mappedTransTypes['BONUS26'] = 'Rental';
        $mappedTransTypes['RENTAL'] = 'Rental';
        $mappedTransTypes['RENTPROMO'] = 'Rental';
        $mappedTransTypes['TAXCODEGPX'] = 'Tax';
        $mappedTransTypes['ST10'] = 'Tax';
        $mappedTransTypes['GPXTAX'] = 'Tax';
        $mappedTransTypes['CLEARDEB'] = 'Adjustments';
        $mappedTransTypes['MISCNGST'] = 'Misc';
        $mappedTransTypes['GUEST CERT'] = 'Guest Certificate';
        $mappedTransTypes['GUEST NAME CHANGE'] = 'GUEST NAME CHANGE';

        foreach ( $mappedTransTypes as $mttK => $mtt ) {
            $tts[ $mtt ][] = $mttK;
        }
        //these need to be included in the cancelled to confirmed conditional statement
        $includeConfirmed = [
            'Exchange',
            'Rental',
        ];
        foreach ( $includeConfirmed as $ic ) {
            foreach ( $tts[ $ic ] as $tt ) {
                $allIncludeConfirmed[] = $tt;
            }
        }
        //these need to be excluded from the function that changes the status from Canceled to Confirmed
        $excludeConfirmed = [
            'Extension',
            'Late Deposit',
            'Adjustments',
            'Misc',
        ];
        foreach ( $excludeConfirmed as $exc ) {
            foreach ( $tts[ $exc ] as $tt ) {
                $cancelledCheckExcludeConfirmed[] = $tt;
            }
        }

        $sf = Salesforce::getInstance();


        /*
                     * the following are part of the file but not added to sf
                     * DepositingOwnerMemberID
                     * TravellingGuestContactID
                     * CreditBalance
                     */

        //the header items that we need to pull from the csv
        $header['transactions'] = [
            'datetime' => 'TransactionDate',
            'Purchase_Type__c' => 'RecordType',
            'status' => 'RecordStatus',
            'resortname' => 'ExchResortName',
            'userID' => 'DepositingOwnersAccount',
            'accounttype' => 'DepositingOwnerAccountType',
            //                         'member_first_name'=>'TravellerFirstName',
            //                         'member_last_name'=>'TravellerLastName',
            'memberAccountName' => 'TravellingMemberAccountName',
            'member_email' => 'TravellingGuestEmail',
            'guest' => 'TravellingMemberAccountName',
            'guestFirstName' => 'TravellerFirstName',
            'guestLastName' => 'TravellerLastName',
            'checkIn' => 'Arrival',
            'checkOut' => 'CheckoutDate',
            'Size' => 'UnitType',
            'deposit' => 'DepositRefNo',
            'MemberNumber' => 'TravellingMemberID',

            'resort_reservation_number' => 'ResortReservationNo',
            /*
                         * these need to be changed because they report one charge per line
                         *
                         */


            /*
                         * end per line change
                         * add amount from new file
                         */
            'PaymentID' => 'PaymentID',
            'Paid' => 'Paid',
            'transactionType' => 'TransactionType',
            'adults' => 'adults',
            'children' => 'children',
            'specialRequest' => 'GuestComments',
            'resort_confirmation_number' => 'Resort_Reservation__c',
            'guestEmailAddress' => 'TravellingGuestEmail',
            'processedBy' => 'BookedBy',
            'WeekType' => 'RecordType',
            /*
                         * used before but not now
                         */
            //                         'weektype'=>'Week Type',
            //                         'phoneCell'=>'Cell Phone',
            //                         'phoneHome'=>'Home Phone',
            //                         'coupon'=>'Coupon',
        ];

        //                     DAAuthoritydate
        //                     LeviesCheckDate
        //                     LeviesPaidDate
        //                     DepositExpiryDate
        //                     creditBalance
        //                     Inventory Source
        //                     AB_DepositId


        $header['deposit'] = [
            'datetime' => 'DepositCreatedDate',
            'Purchase_Type__c' => 'RecordType',
            'status' => 'RecordStatus',
            'resortname' => 'DepositResortName',
            'owner' => 'DepositOwnersAccount',
            'accounttype' => 'DepositOwnerAccountType',
            'checkIn' => 'CheckInDate',
            'checkOut' => 'CheckOutDate',
            'Size' => 'UnitType',
            'deposit' => 'DepositRefNo',
            'memberNo' => 'DepositOwnerMemberID',
            'resortMemberNo' => 'Resort Member #',
            'resortReservationNum' => 'ResortReservationNo',
            'bookedBy' => 'BookedBy',
        ];

        //now we are sending the data to two object -- let's define them
        $objects = [
            'week' => [
                //booking details
                'of_Adults__c' => 'of_Adults__c',
                'of_Children__c' => 'of_Children__c',
                'Guest_First_Name__c' => 'Guest_First_Name__c',
                'Guest_Last_Name__c' => 'Guest_Last_Name__c',
                'Guest_Email__c' => 'Guest_Email__c',
                'Guest_Phone__c' => 'Guest_Phone__c',
                'Special_Requests__c' => 'Special_Requests__c',
                //week details
                'sourse_num' => 'Inventory_Source__c',
                'source_account' => 'Inventory_Owned_by__c',
                'gprID' => 'GPX_Resort__c',
                'GpxWeekRefId__c' => 'GpxWeekRefId__c',
                'GPX_Resort__c' => 'GPX_Resort__c',
                'Check_in_Date__c' => 'Check_in_Date__c',
                'Check_out_Date__c' => 'Check_out_Date__c',
                'country' => 'Country__c',
                'region' => 'Region__c',
                'resortId' => 'Resort_ID__c',
                'resort_confirmation_number' => 'Resort_Reservation__c',
                'resrotName' => 'Resort_Name__c',
                'StockDisplay' => 'Stock_Display__c',
                'sleeps' => 'Unit_Sleeps__c',
                'Unit_Type__c' => 'Unit_Type__c',
                'weekNo' => 'Week_Number__c',
                'WeekType' => 'Week_Type__c',
                'TPSend' => 'TP_Guest_Assigned__c',
                'Booked_by_TP__c' => 'Booked_by_TP__c',
                'Flex_Booking__c' => 'Flex_Booking__c',
            ],
            'transaction' => [
                // reference ID's
                'GPX_Ref__c',
                'GPX_Deposit__c',
                'GPXTransaction__c',
                'Transaction_Book_Date__c',
                'Booked_By__c',
                'Account_Type__c',
                'Account_Name__c',
                'Shift4_Invoice_ID__c',
                // price / fees
                'Purchase_Price__c',
                'CPO_Fee__c',
                'Tax_Paid__c',
                'Upgrade_Fee__c',
                'Guest_Fee__c',
                'Credit_Extension_Fee__c',
                'Late_Deposit_Fee__c',
                'Coupon_Discount__c',
                '',
                // transaction details
                'CPO_Opt_in__c',
                'EMS_Account__c',
                'Purchase_Type__c',
                'Reservation_Status__c',
                'Transaction_On_hold__c',
                'GPX_Coupon_Code__c',
                'GPX_Promo_Code__c',
                //guest details
                'Guest_Cell_Phone__c',
                //don't fill in here either???
                'Guest_Email__c',
                'Guest_Cell_Phone__c',
                'Guest_First_Name__c',
                'Guest_Last_Name__c',
                'Guest_Home_Phone__c',
                'Mobile' => 'Member_Cell_Phone__c',
                'Email' => 'Member_Email__c',
                'HomePhone' => 'Member_Home_Phone__c',
                'first_name' => 'Member_First_Name__c',
                'last_name' => 'Member_Last_Name__c',
                'Special_Requests__c',
                'RecordTypeId',
                'Name',
            ],
        ];

        $extraTransactionTypes = [
            'creditextension' => '0121W000000E02nQAC',
            'guestfee' => '0121W000000E02oQAC',
            'latedepositfee' => '0121W000000E02pQAC',
            'upgradefee' => '0121W000000E02qQAC',
            'booking' => '0121W0000005jWTQAY',
            'first_name' => 'Member_First_Name__c',
            'last_name' => 'Member_Last_Name__c',
        ];

        $extraTransactionsMapped = [
            'CPO' => 'booking',
            'Extension' => 'creditextension',
            'Exchange' => 'booking',
            'Guest Certificate' => 'guestfee',
            'Late Deposit' => 'latedepositfee',
            'Rental' => 'booking',
            'Tax' => 'booking',
            'Upgrade' => 'upgradefee',
            'Adjustments' => 'booking',
            'Misc' => 'booking',
        ];

        $extraTransactionTypesObjects = [

        ];

        $removeSpecialChar = [
            'Special_Requests__c',
            'Member_First_Name__c',
            'Member_Last_Name__c',
            'Guest_First_Name__c',
            'Guest_Last_Name__c',
            'Resort_Name__c',
            'Inventory_Source__c',
        ];


        $sfWeekAdd = '';
        $sfData = [];
        $sfWeekData = [];
        $sfTransData = [];
        $dbTable = [];
        $dateTime = '';
        $paid = 0.00;
        $isDeposit = false;
        $name = '';
        $first_name = '';
        $last_name = '';
        $ownerDetails = '';
        $users = '';
        $user = '';
        $userID = '';
        $resort = '';
        $resortID = '';
        $resortCountry = '';
        $resortRegionName = '';
        $CPO = '';
        $resNum = $sfData['Reservation_Reference__c'] ?? null;
        $transRow = '';
        $dbTableToUpdate = [];
        $neNoNights = '';
        $neMemberName = '';
        $neGuestName = '';
        $neCheckIn = '';

        $userMemberNo = $row['memberNo'] ?? null;

        $dbTable['transactionType'] = 'booking';

        if ( $transactionType == 'deposit' ) {
            $isDeposit = true;
            $sfData['Deposit_Status__c'] = 'Confirmed';
            //add the deposit record type
            $sfData['RecordTypeId'] = '0121W0000005jWY';
            $dbTable['transactionType'] = 'deposit';
        } elseif(isset($row['Purchase_Type__c']) && isset($extraTransactionTypes[ $extraTransactionsMapped[ $mappedTransTypes[ $row['Purchase_Type__c'] ] ] ])) {
            $sfData['RecordTypeId'] = $extraTransactionTypes[ $extraTransactionsMapped[ $mappedTransTypes[ $row['Purchase_Type__c'] ] ] ];
        }
        //is this part of the extra trasactions table?
        if ( isset( $sfData['RecordTypeId'] ) && in_array( $sfData['RecordTypeId'], $extraTransactionTypes ) ) {
            foreach ( $extraTransactionTypes as $ettKey => $ett ) {
                if ( $sfData['RecordTypeId'] == $ett ) {
                    $dbTable['transactionType'] = $ettKey;
                }
            }
        }
        //get the userID
        $userID = $row['userID'];
        $sfData['EMS_Account__c'] = $row['userID'];
        $weekId = $row['weekId'];
        /*
         * Filter the transactions based on the following
         * If multiple line item charges exist within a single purchase of a week (Exchange Fee, Guest Fee & Upgrade), We’d like them to be on a single transaction record.
         * Ideally, edits to costs (adjustments, refunds, etc) would be able to be tied to the day that the adjustment of refund happened, but that wasn’t doable during the v1 rollout so we settled on the values in the original transaction being edited/cleared when an adjustment (cancel and refund/modification) occurs.
         * If an upgrade or guest fee is paid separately, after the date the exchange transaction is processed, it would create a separate transaction so that the revenue could be tied to the day the purchase was made.
         * Credit extnesion and late deposit is always a standalone transaction
         * Guest Fee could be any time
         */

        //if this file is deposit
        foreach ( $row as $rKey => $rValue ) {
            // begin the apply the processing requirements
            if ( $rKey == 'datetime' ) {
                $dateTime = date( 'Y-m-d 00:00:00', strtotime( $rValue ) );
                $sfTransactionDate = date( 'Y-m-d', strtotime( $rValue ) );
                $sfData['Transaction_Book_Date__c'] = $dateTime;
            }
            if ( $rKey == 'transactionType' ) {
                if ( $rValue == 'deposit' ) {
                    $sfData['RecordTypeId'] = $extraTransactionTypes['latedepositfee'];

                    $amount = (float) $row['Paid'] ?? 0.00;

                    $sfData['Late_Deposit_Fee__c'] = $amount;

                    if ( isset( $row['creditid'] ) ) {
                        $creditID = $row['creditid'];
                    }
                    if ( isset( $row['creditweekid'] ) ) {
                        $creditID = $row['creditweekid'];
                    }
                    if ( ! empty( $creditID ) ) {
                        $sql = $wpdb->prepare( "SELECT sf_name, record_id FROM wp_credit WHERE id=%s",
                            $creditID );
                        $creditWeekID = $wpdb->get_row( $sql );
                        $sfData['GPX_Deposit__c'] = $creditWeekID->record_id;
                    }
                }
                if ( $rValue == 'guest' ) {
                    $sfData['RecordTypeId'] = $extraTransactionTypes['guestfee'];

                    $amount = (float) $row['Paid'];

                    $sfData['Guest_Fee__c'] = $amount;
                }
                if ( $rValue == 'extension' ) {
                    $sfData['RecordTypeId'] = $extraTransactionTypes['creditextension'];

                    $amount = (float) $row['Paid'];

                    if ( isset( $row['creditid'] ) ) {
                        $creditID = $row['creditid'];
                    }
                    if ( isset( $row['creditweekid'] ) ) {
                        $creditID = $row['creditweekid'];
                    }
                    if ( ! empty( $creditID ) ) {
                        $sql = $wpdb->prepare( "SELECT sf_name, record_id FROM wp_credit WHERE id=%s",
                            $creditID );
                        $creditWeekID = $wpdb->get_row( $sql );
                        $sfData['GPX_Deposit__c'] = $creditWeekID->record_id;
                    }
                }
            }
            if ( $rKey == 'resortID' ) {
                $sql = $wpdb->prepare( "SELECT ResortName, gprID, sf_GPX_Resort__c from wp_resorts WHERE ResortID=%s",
                    $rValue );
                $resortRow = $wpdb->get_row( $sql );
                if ( ! empty( $resortRow ) && ( ! empty( $resortRow->gprID ) || ! empty( $resortRow->sf_GPX_Resort__c ) ) ) {
                    $sfData['Resort_ID__c'] = $resortRow->sf_GPX_Resort__c;
                    $sfWeekData['Resort_ID__c'] = $resortRow->sf_GPX_Resort__c;
                    $sfData['GPX_Resort__c'] = substr( $resortRow->sf_GPX_Resort__c, 0, 15 );
                    $sfWeekData['GPX_Resort__c'] = substr( $resortRow->sf_GPX_Resort__c, 0, 15 );
                }
            }

            if ( $rKey == 'coupon' ) {
                $placeholders = gpx_db_placeholders( $rValue );
                $sql = $wpdb->prepare( "SELECT Name from wp_specials WHERE id IN ({$placeholders})",
                    array_values( $rValue ) );
                $codes = $wpdb->get_results( $sql );
                //
                //add the owner credit coupons
                foreach ( $codes as $code ) {
                    $ccs[] = $code->Name;
                }
                $sfData['GPX_Coupon_Code__c'] = implode( ",", $ccs );
            }

            if ( $rKey == 'ownerCreditCouponID' ) {
                $ccs[] = 'Monetary Coupon';
                $sfData['GPX_Coupon_Code__c'] = implode( ",", $ccs );
            }
            if ( $rKey == 'promoName' ) {
                $sfData['GPX_Promo_Code__c'] = $rValue;
            }

            if ( $rKey == 'couponDiscount' ) {
                $amt[] = str_replace( ",", "", str_replace( '$', '', $rValue ) );
                $sfData['Coupon_Discount__c'] = array_sum( $amt );
            }
            if ( $rKey == 'ownerCreditCouponAmount' ) {
                $amt[] = str_replace( ",", "", $rValue );
                $sfData['Coupon_Discount__c'] = array_sum( $amt );
            }
            if ( $rKey == 'Paid' && ! $isDeposit ) {
                $ptSet = false;
                $amount = (float) $row['Paid'];
            }
            if ( $rKey == 'transactionType' ) {
                if ( $rValue == 'deposit' || $rValue == 'extension' ) {
                    if ( isset( $row['creditid'] ) ) {
                        $creditID = $row['creditid'];
                    }
                    if ( isset( $row['creditweekid'] ) ) {
                        $creditID = $row['creditweekid'];
                    }

                    if ( ! empty( $creditID ) ) {
                        $sql = $wpdb->prepare( "SELECT sf_name, record_id FROM wp_credit WHERE id=%s",
                            $creditID );
                        $creditWeekID = $wpdb->get_row( $sql );
                        $sfData['GPX_Deposit__c'] = $creditWeekID->record_id;
                    }
                }
            }

            if ( $rKey == 'actcpoFee' ) {
                $amount = (float) $row['actcpoFee'];
                $ptSet = true;
                $paid += $amount;
                $sfData['CPO_Fee__c'] = $amount;
            }


            if ( $rKey == 'actextensionFee' ) {
                $amount = (float) $row['actextensionFee'];
                $ptSet = true;
                if ( $row['actWeekPrice'] > 0 || $weekId != 0 ) {
                    //do not change the record type
                } else {
                    $sfData['RecordTypeId'] = $extraTransactionTypes['creditextension'];
                }
                $paid += $amount;
                $sfData['Credit_Extension_Fee__c'] = $amount ?? 0;
                $creditWeekID = $cjson->creditweekid;
                $sql = $wpdb->prepare( "SELECT record_id FROM wp_credit WHERE id=%s",
                    $creditWeekID );
                $dRow = $wpdb->get_row( $sql );

                if ( isset( $row['creditid'] ) ) {
                    $creditID = $row['creditid'];
                }
                if ( isset( $row['creditweekid'] ) ) {
                    $creditID = $row['creditweekid'];
                }
                if ( ! empty( $creditID ) ) {
                    $sql = $wpdb->prepare( "SELECT sf_name, record_id FROM wp_credit WHERE id=%s",
                        $creditID );
                    $creditWeekID = $wpdb->get_row( $sql );
                    $sfData['GPX_Deposit__c'] = $creditWeekID->record_id;
                }
            }

            if ( $rKey == 'CreditSFID' ) {
                $sfData['GPX_Deposit__c'] = $rValue;
            }
            if ( $rKey == 'actWeekPrice' ) {
                $amount = (float) $row['actWeekPrice'];
                $ptSet = true;
                $paid += $amount;
                $sfData['Purchase_Price__c'] = $amount;
                $objects['transaction'][] = 'GPX_Deposit__c';
            }

            if ( $rKey == 'actguestFee' ) {
                $amount = (float) $row['actguestFee'];
                $ptSet = true;

                //we can't just look at the week price we also need to see if this has a week associated with it
                if ( $row['actWeekPrice'] > 0 || $weekId != 0 ) {
                    //do not change the record type because this is week
                } else {
                    $sfData['RecordTypeId'] = $extraTransactionTypes['guestfee'];
                }
                $paid += $amount;
                $sfData['Guest_Fee__c'] = $amount;
            }

            if ( $rKey == 'actlatedepositFee' ) {
                if ( isset( $cjson->type ) && $cjson->type == 'late_deposit_fee' ) {
                    $amount = (float) $cjson->fee;
                } else {
                    $amount = (float) $row['actlatedepositFee'];
                }
                $ptSet = true;
                if ( $row['actWeekPrice'] > 0 || $weekId != 0 ) {
                    //do not change the record type
                } else {
                    $sfData['RecordTypeId'] = $extraTransactionTypes['latedepositfee'];
                }
                $paid += $amount;
                $sfData['Late_Deposit_Fee__c'] = $amount;

                if ( isset( $row['creditid'] ) ) {
                    $creditID = $row['creditid'];
                }
                if ( isset( $row['creditweekid'] ) ) {
                    $creditID = $row['creditweekid'];
                }
                if ( ! empty( $creditID ) ) {
                    $sql = $wpdb->prepare( "SELECT sf_name, record_id FROM wp_credit WHERE id=%s",
                        $creditID );
                    $creditWeekID = $wpdb->get_row( $sql );
                    $sfData['GPX_Deposit__c'] = $creditWeekID->record_id;
                }
            }
            if ( $rKey == 'creditweekid' || $rKey == 'creditid' ) {
                $creditID = $rValue;

                $sql = $wpdb->prepare( "SELECT sf_name, record_id FROM wp_credit WHERE id=%s", $creditID );
                $creditWeekID = $wpdb->get_row( $sql );

                $sfData['GPX_Deposit__c'] = $creditWeekID->record_id;
            }

            if ( $rKey == 'acttax' ) {
                $amount = (float) $row['acttax'];
                $ptSet = true;
                $paid += $amount;
                $sfData['Tax_Paid__c'] = $amount;
            }

            if ( $rKey == 'actcpoFee' ) {
                {
                }
                $amount = (float) $row['actupgradeFee'];
                $ptSet = true;
                $paid += $amount;
                $sfData['Upgrade_Fee__c'] = $amount;
            }
            if ( $rKey == 'WeekType' ) {
                if ( trim( strtolower( $rValue ) ) == 'exchange' ) {
                    $sfData['RecordTypeId'] = $extraTransactionTypes['booking'];
                    $sfData['Purchase_Type__c'] = 'Exchange';
                } elseif ( trim( strtolower( $rValue ) ) == 'rental' ) {
                    $sfData['RecordTypeId'] = $extraTransactionTypes['booking'];
                    $sfData['Purchase_Type__c'] = 'Rental';
                } else {
                    //this needs to be the transaaction type
                }
                //add additional transaction types
                if ( array_key_exists( strtolower( str_replace( " ", "", $rValue ) ), $extraTransactionTypes ) ) {
                    $sfData['RecordTypeId'] = $extraTransactionTypes[ strtolower( str_replace( " ", "", $rValue ) ) ];
                }
            }


            if ( $rKey == 'cancelled' ) {
                if ( $isDeposit ) {
                    if ( $rValue == 'Active' ) {
                        $rValue = 'Approved';
                    }
                    $sfData['Deposit_Status__c'] = $rValue;
                    //                                     $sfData['Reservation_Status__c'] = $rValue;
                } else {
                    if ( $rValue == '1' && ( ! in_array( $rValue, $cancelledCheckExcludeConfirmed ) ) ) {
                        //                                         $rValue = 'Confirmed';
                        $sfData['Reservation_Status__c'] = 'Cancelled';
                    } else {
                        $sfData['Reservation_Status__c'] = 'Confirmed';
                        if ( ($row['WeekType'] ?? '') == 'Exchange' && $row['depositID'] > 0 ) {
                            //is this status pending?
                            $sfData['Reservation_Status__c'] = 'Pending Deposit';
                        }
                    }
                }
            }
            if ( $rKey == 'resortName' || $rKey == 'ResortName' ) {
                if ( $isDeposit ) {
                    $sfData['Deposit_Resort_Name__c'] = $rValue;
                    $sfData['Resort_Name__c'] = $rValue;
                    $xResortName = esc_sql( $rValue );
                } else {
                    $xResortName = esc_sql( $rValue );
                    $sfData['Resort_Name__c'] = $rValue;
                }
            }


            if ( $rKey == 'userID' ) {
                //get the name
                $user_info = get_userdata( $rValue );
                $first_name = $user_info->first_name;
                $last_name = $user_info->last_name;
                $email = $user_info->user_email;
                $Property_Owner = $user_info->Property_Owner;
                $sfData['Member_First_Name__c'] = $first_name;
                $sfData['Member_Last_Name__c'] = $last_name;
                $sfData['Member_Email__c'] = $email;
                $sfData['Account_Type__c'] = 'USA GPX Member';
                $sfData['Account_Name__c'] = $Property_Owner;
            }

            if ( $rKey == 'source_num' ) {
                if ( $rValue == '1' ) {
                    $rValue = 'Owner';
                }
                if ( $rValue == '2' ) {
                    $rValue = 'GPR';
                }
                if ( $rValue == '3' ) {
                    $rValue = 'Trade Partner';
                }
                $sfData['Inventory_Source__c'] = $rValue;
                if ( isset( $thisisimported ) ) {
                    $sfData['Inventory_Source__c'] = 'GPR';
                }
            }
            if ( $rKey == 'source_account' ) {
                if ( $row['source_num'] == '3' ) {
                    $sfData['Inventory_Owned_by__c'] = $rValue;
                }
            }
            if ( $rKey == 'GuestName' ) {
                if ( empty( $cjson ) ) {
                    $name = trim( $rValue );
                    [ $first_name, $last_name ] = explode( ' ', $name, 2 );
                    $sfData['Guest_First_Name__c'] = $first_name;
                    $sfData['Guest_Last_Name__c'] = $last_name;
                } else {
                    $sfData['Guest_First_Name__c'] = $cjson->FirstName1 ?? null;
                    $sfData['Guest_Last_Name__c'] = $cjson->LastName1 ?? null;
                    $sfData['Guest_Cell_Phone__c'] = $cjson->Mobile ?? null;
                    $sfData['Guest_Home_Phone__c'] = $cjson->HomePhone ?? null;
                    $sfData['Guest_Email__c'] = $cjson->email ?? null;
                    if ( isset( $cjson->phone ) ) {
                        $sfData['Guest_Phone__c'] = substr( preg_replace( '/[^0-9]/', '', $cjson->phone ), 0, 18 );
                    }
                }
            }

            if ( $rKey == 'PaymentID' ) {
                $sfData['Shift4_Invoice_ID__c'] = $rValue;
            }
            if ( $rKey == 'Adults' ) {
                $sfData['of_Adults__c'] = $rValue;
            }
            if ( $rKey == 'Children' ) {
                $sfData['of_Children__c'] = $rValue;
            }
            if ( $rKey == 'specialRequest' ) {
                $sfData['Special_Requests__c'] = $rValue;
            }
            if ( $rKey == 'creditweekID' ) {
                $sfData['Resort_Reservation__c'] = $rValue;
            }
            if ( $rKey == 'checkIn' ) {
                if ( $isDeposit ) {
                    $sfData['Check_in_Date__c'] = date( 'Y-m-d', strtotime( $rValue ) );
                    $sfData['Deposit_Check_In_Date__c'] = date( 'Y-m-d', strtotime( $rValue ) );
                    $sfData['Deposit_Entitlement_Year__c'] = date( 'Y', strtotime( $rValue ) );
                } else {
                    $sfData['Check_in_Date__c'] = date( 'Y-m-d', strtotime( $rValue ) );
                }
                $sfData['Check_out_Date__c'] = date( 'Y-m-d', strtotime( $rValue . '+7 days' ) );
            }
            if ( $rKey == 'Size' ) {
                if ( $isDeposit ) {
                    $sfData['Deposit_Unit_Type__c'] = $rValue;
                } else {
                    //split the bedrooms
                    $bSplit = explode( '/', $rValue );
                    $sfData['Unit_Type__c'] = $bSplit[0];
                }
            }
            if ( $rKey == 'WeekType' ) {
                if ( trim( $row['WeekType'] ) == 'Exchange' || trim( $row['WeekType'] ) == 'Rental' ) {
                    $sfData['Week_Type__c'] = $row['WeekType'];
                } else {
                    $sfData['Week_Type__c'] = $mappedTransTypes[ $rValue ] ?? null;
                }
            }
            if ( $rKey == 'deposit' ) {
                if ( $isDeposit ) {
                    $sfData['Deposit_Reference__c'] = $rValue;
                    $sfData['Reservation_Reference__c'] = $rValue;
                } else {
                    $sfData['Reservation_Reference__c'] = $rValue;
                }
            }
            if ( $rKey == 'processedBy' ) {
                //should be the name of the supervisor or owner
                $sfData['Booked_By__c'] = $rValue;
            }
        }
        //if any details are missing then don't pass the field to SF
        foreach ( $sfData as $sfK => $sfD ) {
            if ( empty( $sfD ) ) {
                unset( $sfData[ $sfK ] );
            }
        }

        if ( ! empty( $sfData['CPO_Fee__c'] ) ) {
            $CPO = "True";
        } else {
            $CPO = "";
        }


        //make sure these items aren't empty
        if ( ! empty( $sfData['Check_in_Date__c'] ) ) {
            $neCheckIn = date( 'd M Y', strtotime( $sfData['Check_in_Date__c'] ) );
        }
        if ( ! empty( $sfData['Check_out_Date__c'] ) && ! empty( $sfData['Check_in_Date__c'] ) ) {
            $neNoNights = ( ( strtotime( $sfData['Check_out_Date__c'] ) - strtotime( $sfData['Check_in_Date__c'] ) ) / ( 60 * 60 * 24 ) );
        }
        if ( ! empty( $sfData['Member_First_Name__c'] ) || ! empty( $sfData['Member_Last_Name__c'] ) ) {
            $neMemberName = $sfData['Member_First_Name__c'] . " " . $sfData['Member_Last_Name__c'];
        }
        if ( ! empty( $sfData['Guest_First_Name__c'] ) || ! empty( $sfData['Guest_Last_Name__c'] ) ) {
            $neGuestName = $sfData['Guest_First_Name__c'] . " " . $sfData['Guest_Last_Name__c'];
        }

        //if this is a reversal and travellers first name is blank then we need to make this a confirmed transaction and make sure that the travellers first and last name remain.
        if ( $row['transactionType'] == 'REVERSAL' ) {
            //this is a reversal is the traveller blank
            if ( ! empty( $row['guestFirstName'] ) ) {
                //still going is this an exchange or rental?
                if ( in_array( $row['Purchase_Type__c'], $allIncludeConfirmed ) ) {
                    $sfData['Reservation_Status__c'] = 'Confirmed';
                    //                                     $sfData['Deposit_Status__c'] = 'Confirmed';
                    unset( $sfData['Guest_First_Name__c'] );
                    unset( $sfData['Guest_Last_Name__c'] );
                }
            }
        }

        $dbTableData = [
            'MemberNumber' => $sfData['EMS_Account__c'],
            'MemberName' => $neMemberName,
            'Owner' => $sfData['Trade_Partner__c'] ?? null,
            'GuestName' => $neGuestName,
            'Adults' => $sfData['of_Adults__c'] ?? null,
            'Children' => $sfData['Children'] ?? 0,
            'UpgradeFee' => $sfData['Upgrade_Fee__c'] ?? 0.00,
            'CPOFee' => $sfData['CPO_Fee__c'] ?? 0.00,
            'CPO' => $CPO,
            'Paid' => $paid,
            'Balance' => '0',
            'ResortID' => $resortID,
            'sleeps' => $bSplit[1] ?? null,
            'bedrooms' => $bSplit[0] ?? null,
            'Size' => $bSplit ? implode( "/", $bSplit ) : null,
            'noNights' => $neNoNights,
            'checkIn' => $neCheckIn,
            'specialRequest' => $sfData['Special_Requests__c'] ?? null,
            'Email' => $sfData['Member_Email__c'],
            'Uploaded' => date( 'Y-m-d H:i:s' ),
        ];


        //if this is a late deposit then we need traveling memeber
        /*
                                                 * Specifically for the charge codes that refer to late deposit fees,
                                                 * we don't really "care" that there is a travelling member ID.
                                                 * Ashley 12/5/2019
                                                 */
        $ldCheck = $row['WeekType'];
        if ( ($mappedTransTypes[ $ldCheck ] ?? null) == 'Late Deposit' ) {
            $sfData['EMS_Account__c'] = '';
            $sfData['Member_First_Name__c'] = '';
            $sfData['Member_Last_Name__c'] = '';
        }

        $dbTable = [
            'transactionType' => 'booking',
            'resortID' => $resortID,
            'sfEMSID' => $userMemberNo,
            'sfTransactionDate' => $sfTransactionDate,
            'data' => json_encode( $dbTableData ),
            'datetime' => $dateTime,
        ];

        $dbTable['userID'] = $userID;
        $dbTable['weekId'] = $weekId;
        $dbTable['sfData'] = json_encode( [
            'insert' => $sfData,
        ] );
        if ( $isDeposit ) {
            $dbTable['transactionType'] = 'deposit';
        }
        //is this part of the extra trasactions table?
        if ( isset( $sfData['RecordTypeId'] ) && in_array( $sfData['RecordTypeId'], $extraTransactionTypes ) ) {
            foreach ( $extraTransactionTypes as $ettKey => $ett ) {
                if ( $sfData['RecordTypeId'] == $ett ) {
                    $dbTable['transactionType'] = $ettKey;
                }
            }
        }
        if ( strtolower( $sfData['GpxWeekRefId__c'] ?? '' ) == 'cancelled' || strtolower( $sfData['GpxWeekRefId__c'] ?? '' ) == 'canceled' ) {
            $cancelled = [
                'userid' => $userID,
                'date' => date( 'Y-m-d H:i:s' ),
            ];
            $dbTable['cancelled'] = json_encode( $cancelled );
        }
        //has this record been added in our database?
        $resNum = $sfData['Reservation_Reference__c'] ?? null;

        if ( ! empty( $transRow ) ) {
            $dbTableToUpdate = json_decode( $transRow->data, true );
        }

        //was the cpo set for this transaction?
        if ( empty( $CPO ) ) {
            if ( isset( $row['CPO'] ) && $row['CPO'] == "Taken" ) {
                $CPO = "True";
                $sfData['CPO_Opt_in__c'] = true;
                $sfData['Flex_Booking__c'] = true;
            }
        }
        if ( isset( $row['CPO'] ) && $row['CPO'] == "Taken" ) {
            $CPO = "True";
            $sfData['CPO_Opt_in__c'] = true;
            $sfData['Flex_Booking__c'] = true;
        }

        //handle booked by
        if ( isset( $row['processedBy'] ) ) {
            if ( $row['userID'] == $row['processedBy'] ) {
                $sfData['Booked_By__c'] = 'Owner';
            } else {
                //get the name of the person that booked this
                $bookedby_user_info = get_userdata( $row['processedBy'] );
                $sfData['Booked_By__c'] = $bookedby_user_info->first_name . " " . $bookedby_user_info->last_name;
            }
        }

        foreach ( $dbTableData as $dbtdKey => $dbtd ) {
            if ( ! empty( $dbtd ) ) {
                $dbTableToUpdate[ $dbtdKey ] = $dbtd;
            }
        }
        $dbTable['data'] = json_encode( $dbTableToUpdate );

        $dbError = '';
        $sfError = [];

        $sfData['GPXTransaction__c'] = $transactionID;

        //we need to get week details and add update the database if necessary
//
        //create the week object
        foreach ( $objects['week'] as $oWeekKey => $oWeek ) {
            if ( isset( $sfData[ $oWeek ] ) && ! empty( $sfData[ $oWeek ] ) ) {
                if ( $oWeek == 'Unit_Sleeps__c' && strrpos( $sfData[ $oWeek ], "+" ) ) {
                    $sfData[ $oWeek ] = substr( $sfData[ $oWeek ], 0, strrpos( $sfData[ $oWeek ], "+" ) );
                }
                $sfWeekData[ $oWeek ] = str_replace( "&", "&amp;", $sfData[ $oWeek ] );
            } elseif ( isset( $weekDetails->$oWeekKey ) ) {
                if ( $oWeek == 'Unit_Sleeps__c' && strrpos( $weekDetails->$oWeekKey, "+" ) ) {
                    $weekDetails->$oWeekKey = substr( $weekDetails->$oWeekKey,
                        0,
                        strrpos( $weekDetails->$oWeekKey, "+" ) );
                }
                $sfWeekData[ $oWeek ] = str_replace( "&", "&amp;", $weekDetails->$oWeekKey );
            }
        }

        //adjust the status
        if ( ! $isDeposit ) {
            $sfWeekData['Status__c'] = 'Booked';
            if ( $sfData['Reservation_Status__c'] == 'Pending Deposit' ) {
                $sfWeekData['Status__c'] = 'Pending';
            }
            if ( $sfData['Reservation_Status__c'] == 'Cancelled' ) {
                $sfWeekData['Status__c'] = 'Available';
            }
        }

        $sfWeekData['GpxWeekRefId__c'] = $sfWeekData['Name'] = $weekId;
        //add the date/time that this is bing synced
        $sfWeekData['Date_Last_Synced_with_GPX__c'] = $sfTransData['Date_Last_Synced_with_GPX__c'] = date( 'Y-m-d' );

        //is this a trade partner booking?
        $sql = $wpdb->prepare( "SELECT record_id, name, sf_account_id FROM wp_partner WHERE user_id=%s",
            $row['userID'] );
        $istp = $wpdb->get_row( $sql );

        $sfData['Account_Type__c'] = 'USA GPX Member';
        if ( ! empty( $istp ) ) {
            $sfData['Booked_by_TP__c'] = 1;
            $sfWeekData['Booked_by_TP__c'] = 1;
            $sfData['Account_Type__c'] = 'USA GPX Trade Partner';
            $sfData['Account_Name__c'] = $istp->sf_account_id;
            $sfData['Member_Last_Name__c'] = $istp->name;
            $sfData['Purchase_Price__c'] = 0;
            $sfData['CPO_Fee__c'] = 0;
            $sfData['Tax_Paid__c'] = 0;
            $sfData['Upgrade_Fee__c'] = 0;
            $sfData['Guest_Fee__c'] = 0;
            $sfData['Credit_Extension_Fee__c'] = 0;
            $sfData['Late_Deposit_Fee__c'] = 0;
            $sfData['Coupon_Discount__c'] = 0;

            $sfData['Guest_First_Name__c'] = 'Partner';
            $sfData['Guest_Last_Name__c'] = 'Hold';
        }

        $approvedWeeks = [
            'Available',
            'Approved',
            'Booked',
        ];

        //is this a deposit on exchange
        if ( ( isset( $row['CreditStatus'] ) && ! in_array( $row['CreditStatus'], $approvedWeeks ) ) ||
             ( isset( $transRow->CreditStatus ) && ! in_array( $transRow->CreditStatus, $approvedWeeks ) ) ) {
            $sfData['Reservation_Status__c'] == 'Pending Deposit';
            $sfWeekData['Status__c'] = 'Pending';
        }
        if ( isset( $thisisimported ) ) {
            $sfWeekData['Status__c'] = 'Booked';
        }

        foreach ( $removeSpecialChar as $rsc ) {
            if ( isset( $sfWeekData[ $rsc ] ) ) {
                $sfWeekData[ $rsc ] = str_replace( "&amp;", " and ", $sfWeekData[ $rsc ] );
                $sfWeekData[ $rsc ] = preg_replace( '/[^ \w\-\.,]/', '', $sfWeekData[ $rsc ] );
            }
        }
        if ( empty( $sfData['Booked_By__c'] ) ) {
            $agentInfo = wp_get_current_user();
            $sfData['Booked_By__c'] = $agentInfo->first_name . ' ' . $agentInfo->last_name;
        }
        $sfWeekAdd = '';
        $sfAdd = '';
        $sfType = 'GPX_Week__c';
        $sfObject = 'GpxWeekRefId__c';


        $sfFields = [];
        $sfFields[0] = new SObject();
        $sfFields[0]->fields = $sfWeekData;
        $sfFields[0]->type = $sfType;

        //add GPX_Ref__c for guest fee one-off
        if ( $sfData['RecordTypeId'] == '0121W000000E02oQAC' ) {
            $sql = $wpdb->prepare( "SELECT sfData from wp_gpxTransactions WHERE id=%s",
                $row['transactionID'] );
            $sfDataRow = $wpdb->get_var( $sql );
            $sfRow = json_decode( $sfDataRow, true );
            $sfData['GPX_Ref__c'] = $sfRow['insert']['GPX_Ref__c'];
        }

        if ( $sfData['RecordTypeId'] == '0121W0000005jWTQAY' || isset( $_GET['send_week'] ) ) {
            $sfWeekAdd = $sf->gpxUpsert( $sfObject, $sfFields );
        }
        if ( isset( $_GET['sf_week_debug'] ) ) {
            echo '<pre>' . print_r( $sfWeekAdd, true ) . '</pre>';
        }

        if ( isset( $sfWeekAdd[0]->id ) ) {
            $sfData['GPX_Ref__c'] = $sfWeekAdd[0]->id;
        } else {
            $sfAdd = $sfWeekAdd;
        }

        if ( ! isset( $sfAdd[0]->errors ) ) {
            //did this transaction have an extension fee
            if ( ( isset( $cjson->creditextensionfee ) && $cjson->creditextensionfee > 0 ) ) {
                $sfData['Credit_Extension_Fee__c'] = $cjson->creditextensionfee ?? 0;
                if ( empty( $sfData['Credit_Extension_Fee__c'] ) ) {
                    $sfData['Credit_Extension_Fee__c'] = $cjson->actextensionFee ?? 0;
                }

                $creditWeekID = $cjson->creditweekid;
                $sql = $wpdb->prepare( "SELECT record_id FROM wp_credit WHERE id=%s",
                    $creditWeekID );
                $dRow = $wpdb->get_row( $sql );
                $objects['transaction'][] = 'GPX_Deposit__c';
            }

            if ( ( isset( $cjson->lateDepositFee ) && $cjson->lateDepositFee > 0 ) ) {
                $sfData['Late_Deposit_Fee__c'] = $cjson->lateDepositFee;
            }

            foreach ( $objects['transaction'] as $oTransKey => $oTrans ) {
                if ( isset( $sfData[ $oTrans ] ) ) {
                    $sfTransData[ $oTrans ] = $sfData[ $oTrans ];
                } elseif ( isset( $ownerDetails->$oTransKey ) ) {
                    $sfTransData[ $oTrans ] = $ownerDetails->$oTransKey;
                }
            }

            $sfTransData['Name'] = $sfTransData['GPXTransaction__c'];

            foreach ( $removeSpecialChar as $rsc ) {
                if ( isset( $sfTransData[ $rsc ] ) && ! empty( $sfTransData[ $rsc ] ) ) {
                    $sfWeekData[ $rsc ] = str_replace( "&amp;", " and ", $sfWeekData[ $rsc ] ?? '' );
                    $sfTransData[ $rsc ] = preg_replace( '/[^ \w\-\.,]/', '', $sfTransData[ $rsc ] ?? '' );
                }
            }

            $sfType = 'GPX_Transaction__c';
            $sfObject = 'GPXTransaction__c';
            $sfFields = [];
            $sfFields[0] = new SObject();

            if (isset($sfTransData['Credit_Extension_Fee__c'])) {
                if ($sfTransData['Credit_Extension_Fee__c']=='undefined') {
                    $sfTransData['Credit_Extension_Fee__c'] = 0;
                }
            }

            $sfFields[0]->fields = $sfTransData;
            $sfFields[0]->type = $sfType;
            $sfAdd = $sf->gpxUpsert( $sfObject, $sfFields );
        }
        if ( isset( $sfAdd[0]->id ) ) {
            $sfDB = [
                'sfid' => $sfAdd[0]->id,
                'sfData' => json_encode( [ 'insert' => $sfData ] ),
            ];

            $wpdb->update( 'wp_gpxTransactions', $sfDB, [ 'id' => $transactionID ] );
            $insertSuccess[] = 'Record ' . $weekId . ' added.';
        } else {
            $errorData = [
                'error' => $sfAdd,
            ];
            if ( isset( $sfTransData ) ) {
                $errorData['upsert'] = $sfTransData;
            } else {
                $errorData['upsert'] = $sfWeekData;
            }
            $sfDB = [
                'sfid' => $sfAdd[0]->id,
                'sfData' => json_encode( $errorData ),
            ];

            if ( ! isset( $sfAdd[0]->id ) || ( isset( $sfAdd[0]->id ) && empty( $sfAdd[0]->id ) ) ) {
                // use the provided email list if defined, otherwise just use the blog admin
                $to = defined( 'GPX_NOTIFICATION_EMAILS' ) ? GPX_NOTIFICATION_EMAILS : get_option( 'admin_email' );

                $subject = 'GPX Transaction to SF error on ' . get_site_url();

                $body = '<h2>Transaction: ' . $transactionID . '</h2><h2>Error</h2><pre>' . print_r( $errorData,
                        true ) . '</pre>';
                $headers = [ 'Content-Type: text/html; charset=UTF-8' ];

                wp_mail( $to, $subject, $body, $headers );
            }

            $wpdb->update( 'wp_gpxTransactions', $sfDB, [ 'id' => $transactionID ] );

            $insertSuccess[] = 'Record ' . $weekId . ' added.';

            foreach ( $sfAdd as $sfAddError ) {
                foreach ( $sfAddError->errors as $err ) {
                    $sfError[] = $err->message;
                }
            }
            if ( ! empty( $dbError ) ) {
                $sfError[] = $dbError;
            }
            $insertError[] = 'Record ' . ($sfData['Reservation_Reference__c'] ?? '') . " couldn't be added: " . implode( " & ",
                    $sfError );
        }
        if ( isset( $insertError ) ) {
            $data['message'][] = [
                'type' => 'nag-fail',
                'text' => implode( '<br /><br />', $insertError ),
            ];
        }
        if ( isset( $insertSuccess ) ) {
            $data['message'][] = [
                'type' => 'nag-success',
                'text' => implode( '<br /><br />', $insertSuccess ),
            ];
        }

        return $data;
    }

}
