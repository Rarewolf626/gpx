<?php

use GPX\Model\UserMeta;
use GPX\Model\CustomRequest;
use GPX\Output\StreamAndOutput;
use GPX\Form\CustomRequestForm;
use GPX\Model\CustomRequestMatch;
use GPX\Repository\OwnerRepository;
use GPX\Repository\IntervalRepository;
use GPX\Repository\CustomRequestRepository;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * This function just pulls the cid user data.
 * No idea why there is resort data in here, it's skipped
 *
 * @return void
 */
function gpx_get_custom_request() {
    global $wpdb;

    $joinedTbl = map_dae_to_vest_properties();

    $return = [];
    $cid = $_REQUEST['cid'] ?? null;
    $pid = $_REQUEST['pid'] ?? null;
    if ( ! empty( $cid ) ) {
        $owner = get_userdata( $cid );
        if ( isset( $owner ) && ! empty( $owner ) ) {
            $usermeta = UserMeta::load( $cid );
            $memberNumber = (int) gpx_get_member_number( $cid );
            $return['fname'] = $usermeta->getFirstName();
            $return['lname'] = $usermeta->getLastName();
            $return['daememberno'] = $memberNumber;
            $return['phone'] = $usermeta->getPhone() ?: null;
            $return['mobile'] = $usermeta->getMobile() ?: null;
            $return['email'] = OwnerRepository::instance()->get_email( $owner->ID );
            $return['credits'] = OwnerRepository::instance()->get_credits( $owner->ID );
            $return['requests'] = CustomRequestRepository::instance()->count_open_requests( $memberNumber, $owner->ID );
            $return['intervals'] = IntervalRepository::instance()->count_intervals( $memberNumber, true );
            $return['unavailable'] = ! OwnerRepository::instance()->has_requests_remaining( $owner->ID, $memberNumber );
            $return['show_availability'] = false;
            if ( gpx_show_debug( true ) ) {
                $return['show_availability'] = true;
            }
        }
    }

    $getdate = '';

    if ( ! empty( $pid ) ) {
        if ( substr( $pid, 0, 1 ) == "R" ) {
            $sql = $wpdb->prepare( "SELECT Country, Region, Town, ResortName
                    FROM wp_resorts
                    WHERE ResortID=%s AND active=1",
                $pid );
        } else {

            $sql = $wpdb->prepare("SELECT
                    b.Country, b.Region, b.Town, b.ResortName
                FROM wp_room a
                INNER JOIN wp_resorts b ON a.resort=b .id
                INNER JOIN wp_unit_type c ON a.unit_type=c.record_id
                WHERE a.record_id=%s AND b.active=1", $pid);
            $getdate = '1';
        }
        $row = $wpdb->get_row( $sql );

        if ( ! empty( $row ) ) {
            $return['country'] = $row->Country;
            $return['region'] = $row->Region;
            $return['town'] = $row->Town;
            $return['resort'] = $row->ResortName;
        }
    }

    if ( ! empty( $_REQUEST['rid'] ) ) {
        $request = CustomRequest::findOrNew( $_REQUEST['rid'] );
        wp_send_json( [
            'id' => $request->id ?: null,
            'daememberno' => $request->emsID ?: null,
            'checkIn' => optional( $request->checkIn )->format( 'm/d/Y' ),
            'checkIn2' => optional( $request->checkIn2 )->format( 'm/d/Y' ),
            'country' => $request->country ?: null,
            'resort' => $request->resort ?: null,
            'region' => $request->region ?: null,
            'city' => $request->city ?: null,
            'miles' => CustomRequestMatch::MILES,
            'nearby' => $request->nearby,
            'adults' => $request->adults ?: 0,
            'children' => $request->children ?: 0,
            'roomType' => $request->roomType ?: 'Any',
            'preference' => $request->preference ?: 'Any',
            'larger' => $request->larger,
            'email' => $request->email ?: null,
            'phone' => $request->phone ?: null,
            'mobile' => $request->mobile ?: null,
            'firstName' => $request->firstName ?: null,
            'lastName' => $request->lastName ?: null,
        ] );
    }


    if ( ! empty( $getdate ) && isset( $row->checkIn ) ) {
        $return['dateFrom'] = date( 'm/d/Y', strtotime( $row->checkIn ) );
    }

    wp_send_json( $return );
}

add_action( "wp_ajax_gpx_get_custom_request", "gpx_get_custom_request" );
add_action( "wp_ajax_nopriv_gpx_get_custom_request", "gpx_get_custom_request" );

function gpx_post_special_request() {
    $form = CustomRequestForm::instance();
    $data = $form->validate( $_POST );

    $cdmObj = new CustomRequestMatch( $data );

    if ( $cdmObj->is_fully_restricted() ) {
        // the requested region / resort is restricted
        wp_send_json(
            [
                'success' => true,
                'restricted' => true,
                'matched' => null,
                'hold' => false,
                'matches' => [],
                'message' => 'No requests will be taken for Southern California between June 1 and September 1.',
            ]
        );
    }

    $cid = gpx_get_switch_user_cookie();
    $usermeta = UserMeta::load( $cid );
    $emsid = gpx_get_member_number( $cid );
    $BOD = $usermeta->GP_Preferred;

    if ( $cid == get_current_user_id() && ! OwnerRepository::instance()->has_requests_remaining( $cid, $emsid ) ) {
        wp_send_json( [
                'success' => true,
                'restricted' => false,
                'matched' => null,
                'hold' => true,
                'matches' => [],
                'message' => get_option( 'gpx_hold_error_message' ),
            ]
        );
    }

    $matches = $cdmObj->get_matches();
    $date_restricted = $cdmObj->has_restricted_date();

    $request = new CustomRequest();
    $request->who = $cid == get_current_user_id() ? 'Owner' : 'Agent';
    $request->BOD = $BOD == 'Yes';

    $request->userID = $cid;
    $request->emsID = $emsid;
    $request->firstName = $usermeta->FirstName1 ?? '';
    $request->lastName = $usermeta->LastName1 ?? '';
    $request->phone = $usermeta->DayPhone ?? '';
    $request->mobile = $usermeta->Mobile ?? '';
    $request->resort = $data['resort'] ?? '';
    $request->nearby = $data['nearby'];
    $request->region = $data['region'] ?? '';
    $request->city = $data['city'] ?? '';
    $request->adults = $data['adults'];
    $request->children = $data['children'];
    $request->email = $data['email'] ?? '';
    $request->roomType = $data['roomType'];
    $request->larger = $data['larger'];
    $request->preference = $data['preference'];
    $request->miles = CustomRequestMatch::MILES;
    $request->checkIn = $data['checkIn'];
    $request->checkIn2 = $data['checkIn2'];

    if ( $date_restricted ) {
        // if the date is restricted, filter out the weeks in restricted regions
        $not_restricted = $matches->notRestricted();
        $request->active = $not_restricted->isEmpty();
        $request->forCron = $not_restricted->isEmpty();
        $request->matchedOnSubmission = $not_restricted->isNotEmpty();
        $request->matched = $not_restricted->ids();
    } else {
        $request->active = $matches->isEmpty();
        $request->forCron = $matches->isEmpty();
        $request->matchedOnSubmission = $matches->isNotEmpty();
        $request->matched = $matches->ids();
    }
    // check if this request is already submitted
    $previous = $request->findLikeThis();
    if ( ! $previous ) {
        // if the request is already submitted do not save it again
        $request->save();
    }
    if ( $date_restricted && $matches->anyRestricted() ) {
        $message = 'Note: Your special request included weeks that are restricted. These weeks have been removed from the results.';
        $restricted = true;
    } elseif ( empty( $request->matched ) ) {
        $message = "Your request has been received. You'll receive an email when a match is found.";
        $restricted = false;
    } else {
        $message = 'Matching Travel Found';
        $restricted = false;
    }

    gpx_logger()->info( 'Custom request submitted', [
        'request' => $data,
        'matches' => $matches->toArray(),
        'result' => $request->toArray(),
        'message' => $message,
        'restricted' => $restricted,
    ] );

    wp_send_json(
        [
            'success' => true,
            'hold' => false,
            'matched' => $previous ? $previous->id : $request->id,
            'matches' => $request->matched,
            'restricted' => $restricted,
            'message' => $message,
        ]
    );
}

add_action( "wp_ajax_gpx_post_special_request", "gpx_post_special_request" );
add_action( "wp_ajax_nopriv_gpx_post_special_request", "gpx_post_special_request" );

function get_gpx_customrequests() {
    global $wpdb;

    $data = [];

    $sql = "SELECT * FROM wp_gpxCustomRequest";
    $wheres = [];

    if (isset($_REQUEST['filtertype'])) {
        $dates = explode(" - ", $_REQUEST['dates']);
        if ($_REQUEST['filtertype'] == 'travel') {
            if (count($dates) == 1) {
                $wheres[] = $wpdb->prepare('str_to_date(checkIn, \'%%m/%%d/%%Y\') = %s', date('Y-m-d', strtotime($dates[0])));
            } else {
                $wheres[] = $wpdb->prepare('str_to_date(checkIn, \'%%m/%%d/%%Y\') BETWEEN %s AND %s', [
                    date('Y-m-d', strtotime($dates[0])),
                    date('Y-m-d', strtotime($dates[1])),
                ]);
                $wheres[] = $wpdb->prepare('str_to_date(checkIn2, \'%%m/%%d/%%Y\') BETWEEN %s AND %s', [
                    date('Y-m-d', strtotime($dates[0])),
                    date('Y-m-d', strtotime($dates[1])),
                ]);
            }
        } elseif ($_REQUEST['filtertype'] == 'email') {
            $_REQUEST['found'] = 'yes';
            if (count($dates) == 1) {
                $wheres[] = $wpdb->prepare('matchEmail BETWEEN %s AND %s', [
                    date('Y-m-d 00:00:00', strtotime($dates[0])),
                    date('Y-m-d 23:59:59', strtotime($dates[0])),
                ]);
            } else {
                $wheres[] = $wpdb->prepare('matchEmail BETWEEN %s AND %s', [
                    date('Y-m-d 00:00:00', strtotime($dates[0])),
                    date('Y-m-d 23:59:59', strtotime($dates[1])),
                ]);
            }
        } else {
            if (count($dates) == 1) {
                $wheres[] = $wpdb->prepare('datetime LIKE %s', $wpdb->esc_like(date('Y-m-d', strtotime($dates[0]))) . '%');
            } else {
                $wheres[] = $wpdb->prepare('datetime BETWEEN %s AND %s', [
                    date('Y-m-d 00:00:00', strtotime($dates[0])),
                    date('Y-m-d 23:59:59', strtotime($dates[1])),
                ]);
            }
        }
    }

    if (isset($_REQUEST['found'])) {
        if ($_REQUEST['found'] == 'yes') {
            $wheres[] = "matched != ''";
        }
        if ($_REQUEST['found'] == 'no') {
            $wheres[] = "matched = ''";
        }
    }

    if (isset($wheres)) {
        $sql .= " WHERE " . implode(" AND ", $wheres);
    }
    $crs = $wpdb->get_results($sql);

    $i = 0;
    foreach ($crs as $cr) {
        $location = '';
        if (!empty($cr->resort)) {
            $location = 'Resort: ' . $cr->resort;

        } elseif (!empty($cr->city)) {
            $location = 'City: ' . $cr->city;
        } elseif (!empty($cr->region)) {
            $location = 'Region: ' . $cr->region;
        }

        $date = $cr->checkIn;
        if (!empty($cr->checkIn2)) {
            $date .= ' - ' . $cr->checkIn2;
        }

        $converted = "No";
        $revenue = '';
        if ($cr->matchConverted != '0') {
            $converted = "Yes";
            $sql = $wpdb->prepare("SELECT data from wp_gpxTransactions WHERE id=%d", $cr->matchConverted);
            $transData = $wpdb->get_row($sql);
            $transDecode = json_decode($transData->data);
            $revenue = '$' . number_format($transDecode->Paid, 2);
        }

        $matchEmail = '';
        if (!empty($cr->matchEmail)) {
            $matchEmail = date('m/d/Y', strtotime($cr->matchEmail));
        }

        $nearby = "No";
        if ($cr->nearby == 1) {
            $nearby = "Yes";
        }

        $larger = "No";
        if ($cr->larger == 1) {
            $larger = "Yes";
        }

        $active = "Yes";
        if ($cr->active == '0') $active = "No";

        $found = "Yes";
        if (empty($cr->matched)) $found = "No";
        $data[$i]['userID'] = $cr->userID;
        $data[$i]['emsID'] = $cr->emsID;
        $data[$i]['owner'] = $cr->firstName . " " . $cr->lastName;
        $data[$i]['location'] = $location;
        $data[$i]['region'] = $cr->region;
        $data[$i]['city'] = $cr->city;
        $data[$i]['resort'] = $cr->resort;
        $data[$i]['traveldate'] = $date;
        $data[$i]['found'] = $found;
        $data[$i]['matched'] = $cr->matched;
        $data[$i]['converted'] = $converted;
        $data[$i]['revenue'] = $revenue;
        $data[$i]['roomType'] = $cr->roomType;
        $data[$i]['who'] = $cr->who;
        $data[$i]['travelers'] = intval($cr->adults) + intval($cr->children);
        $data[$i]['entrydate'] = date('m/d/Y', strtotime($cr->datetime));
        $data[$i]['matchEmail'] = $matchEmail;
        $data[$i]['nearby'] = $nearby;
        $data[$i]['larger'] = $larger;
        $data[$i]['type'] = $cr->preference;
        $data[$i]['active'] = $active;
        $i++;
    }

    wp_send_json( $data );
}
add_action( 'wp_ajax_get_gpx_customrequests', 'get_gpx_customrequests' );
add_action( 'wp_ajax_nopriv_get_gpx_customrequests', 'get_gpx_customrequests' );



/**
 *
 *
 *
 *
 */
function cr_form_remove_visual( $c ) {
    if ( isset( $_REQUEST['gpx-pg'] ) && $_REQUEST['gpx-pg'] == 'customrequests_form' ) {
        return false;
    }

    return $c;
}

//remove visual editor from custom request form form
add_filter( 'user_can_richedit', 'cr_form_remove_visual' );

function gpx_check_custom_requests() {
    if ( ! check_user_role( [ 'gpx_admin', 'gpx_call_center', 'administrator', 'administrator_plus' ] ) ) {
        gpx_response( 'You do not have permission to run command', 403 );
    }
    $params = ['command' => 'request:checker'];
    if (gpx_request('debug')) {
        $params['--debug'] = true;
    }
    $response = new StreamedResponse(function () use ($params) {
        $path = WP_CONTENT_DIR . '/logs/custom-request-checker.log';
        $stream = fopen($path, 'w+');
        $output = new StreamAndOutput($stream);
        gpx_run_command($params, $output, false);
        fclose($stream);
    }, 200, ['Content-Type' => 'text/plain']);
    gpx_send_response($response);
}
add_action( 'wp_ajax_gpx_check_custom_requests', 'gpx_check_custom_requests' );
add_action( 'wp_ajax_nopriv_gpx_check_custom_requests', 'gpx_check_custom_requests' );

function gpx_review_custom_requests()
{
    if (!check_user_role(['gpx_admin', 'gpx_call_center', 'administrator', 'administrator_plus'])) {
        gpx_response('You do not have permission to run command', 403);
    }
    $path = WP_CONTENT_DIR . '/logs/custom-request-checker.log';
    if (!is_file($path)) {
        gpx_response('You do not have permission to run command', 404);
    }

    $response = new StreamedResponse(function () use ($path) {
        readfile($path);
    }, 200, ['Content-Type' => 'text/plain']);

    gpx_send_response($response);
}

add_action( 'wp_ajax_gpx_review_custom_requests', 'gpx_review_custom_requests' );
add_action( 'wp_ajax_nopriv_gpx_review_custom_requests', 'gpx_review_custom_requests' );
