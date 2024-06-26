<?php

use GPX\Repository\OwnerRepository;
use GPX\Model\Category as Category;


/**
 *
 *
 *
 *
 */
function gpx_check_login() {
    if ( is_user_logged_in() ) {
        wp_send_json( [ 'success' => true ] );
    }

    wp_send_json( [ 'login' => true ] );
}

add_action( "wp_ajax_gpx_check_login", "gpx_check_login" );
add_action( "wp_ajax_nopriv_gpx_check_login", "gpx_check_login" );


function get_gpx_users_switch() {
    global $wpdb;

    if (isset($_REQUEST['filter']) && !empty($_REQUEST['filter'])) {
        $filters = json_decode(stripslashes($_REQUEST['filter']));
        foreach ($filters as $filterKey => $filterVal) {
            if ($filterKey == 'display_name') {
                $searchVals = explode(" ", $filterVal);
                foreach ($searchVals as $sv) {
                    $displayWheres[] = $wpdb->prepare(" " . gpx_esc_table($filterKey) . " LIKE %s", '%' . $wpdb->esc_like($sv) . '%');
                }
                $wheres[] = "(" . implode(" AND ", $displayWheres) . ")";
            } else {
                $wheres[] = $wpdb->prepare(" " . gpx_esc_table($filterKey) . " LIKE %s", '%' . $wpdb->esc_like($filterVal) . '%');
            }
        }
    } elseif (!empty($_REQUEST['search'])) {
        $searchVals = explode(" ", $_REQUEST['search']);
        foreach ($searchVals as $sv) {
            $wheres[] = $wpdb->prepare(" user_email LIKE %s", '%' . $wpdb->esc_like($sv) . '%');
            $wheres[] = $wpdb->prepare(" display_name LIKE %s", '%' . $wpdb->esc_like($sv) . '%');
            $wheres[] = $wpdb->prepare(" user_login LIKE %s", '%' . $wpdb->esc_like($sv) . '%');
        }
    }

    $where = "WHERE user_login LIKE 'U%' ";
    if (!empty($wheres)) {
        $where .= 'AND (';
        $where .= implode(" AND ", $wheres);
        $where .= ')';
    }
    $sql = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS ID, user_email, display_name, user_login FROM wp_users " . $where . " LIMIT %d OFFSET %d", [
        $_REQUEST['limit'] ?? 20,
        $_REQUEST['offset'] ?? 0,
    ]);
    $users = $wpdb->get_results($sql);

    $sql = "SELECT FOUND_ROWS()";
    $rowcount = $wpdb->get_var($sql);

    $i = 0;
    $data = [];
    foreach ($users as $user) {
        //filter -- only gpx_member
        $user_meta = get_userdata($user->ID);
        $user_roles = $user_meta->roles;
        if (!in_array('gpx_member', $user_roles)) {
            $rowcount--;
            continue;
        }
        //createe the array for the table
        $data[$i]['switch'] = '<a href="#" class="switch_user" data-user="' . esc_attr($user->ID) . '" title="Select Owner and Return"><i class="fa fa-refresh fa-rotate-90" aria-hidden="true"></i></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_edit&id=' . $user->ID . '" title="Edit Owner Account"><i class="fa fa-pencil" aria-hidden="true"></i></a>|&nbsp;&nbsp;<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_mapping&id=' . $user->ID . '" title="View Owner Account"><i class="fa fa-eye" aria-hidden="true"></i></a>';
        $data[$i]['display_name'] = $user->display_name;
        $data[$i]['last_name'] = '';
        $data[$i]['user_email'] = $user->user_email;
        $data[$i]['user_login'] = $user->user_login;
        $i++;
    }
    $fulldata['total'] = $rowcount;
    $fulldata['rows'] = $data;

    wp_send_json( $fulldata );
}
add_action( 'wp_ajax_get_gpx_users_switch', 'get_gpx_users_switch' );
add_action( 'wp_ajax_nopriv_get_gpx_users_switch', 'get_gpx_users_switch' );


function gpx_get_usage($type): string {
    $countries = Category::select(['CountryID','country'])->where('CountryID', '!=', 14)->pluck('country', 'CountryID');

    $output = '<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-name">Country</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select name="' . esc_attr($type) . 'country" id="country_1" class="form-control col-md-7 col-xs-12">
                          	  <option></option>
                              <option value="14">USA</option>
                              ';
    foreach ($countries as $CountryID => $country) {
        $output .= '<option value="' . esc_attr($CountryID) . '">' . esc_html($country) . '</option>';
    }
    $output .= '          </select>
                        </div>
                      </div>
                      ';

    return $output;
}


function gpx_retrieve_password($user_login) {
    $user_login = sanitize_text_field($user_login);
    if (empty($user_login)) return false;
    $user = get_user_by('login', $user_login);
    if (!$user) return false;

    $errors = retrieve_password($user->user_login);
    if (is_wp_error($errors)) {
        return false;
    }

    return ['success' => 'Please check your email for the link to reset your password.'];
}

/**
 *
 *
 *
 *
 */
function request_password_reset() {
    // if the user is logged in bypass the recaptcha
    // don't need it if they are already logged in
    // this will prevent the breakage admin password reset links
    // and members reset links
    // ticket#1925
    if ( ! is_user_logged_in() ) {
        $recaptcha = new \ReCaptcha\ReCaptcha( GPX_RECAPTCHA_V3_SECRET_KEY );
        $resp = $recaptcha->setExpectedAction( 'password_reset' )
                          ->setScoreThreshold( 0.5 )
                          ->verify( $_POST['rec_token'], $_SERVER['REMOTE_ADDR'] );
        if ( ! $resp->isSuccess() ) {
            wp_send_json( [ 'error' => $resp->getErrorCodes() ] );
        }
    }

    $userlogin = $_POST['user_login_pwreset'] ?? $_POST['user_login'] ?? $_POST['user_email'] ?? null;
    if ( ! $userlogin ) {
        wp_send_json( false );
    }

    $pw = gpx_retrieve_password( $userlogin );
    wp_send_json( $pw );
}

add_action( "wp_ajax_request_password_reset", "request_password_reset" );
add_action( "wp_ajax_nopriv_request_password_reset", "request_password_reset" );


/**
 *
 *
 *
 *
 */
add_filter( 'retrieve_password_message', function ( $message, $key, $user_login, $user_data ) {
    // Customize password reset email content
    $message = __( 'It looks like you have forgotten your GPX password.  If this is correct, please follow this link to complete your request for a new password.' ) . "\r\n\r\n";
    $message .= network_site_url( "?action=rp&key=$key&login=" . rawurlencode( $user_data->user_login ),
            'login' ) . "\r\n";
    // $email   = get_user_meta( $user_data->ID, 'Email', true );
    $email = OwnerRepository::instance()->get_email( $user_data->ID );

    if ( ! $email ) {
        // the user does npt have a custom email address
        return $message;
    }

    if ( $email && mb_strtolower( $email ) === mb_strtolower( $user_data->user_email ) ) {
        // the email address in the profile matches the one in the user table
        // return the message content to be sent to the default address
        return $message;
    }
    $blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
    $title = sprintf( __( '[%s] Password Reset' ), $blogname );
    $title = apply_filters( 'retrieve_password_title', $title );

    if ( ! wp_mail( $email, wp_specialchars_decode( $title ), $message ) ) {
        // there was an error sending to the email address in the user profile.
        // return the message content so the default user email is used
        return $message;
    }

    // return false so it doesn't try to send to the user's default email address
    return false;
}, 10, 4 );


function gpx_validate_email() {
    $exists = email_exists( gpx_request( 'email' ) );

    if ( ! $exists ) {
        wp_send_json_success();
    }

    wp_send_json( [ 'error' => 'That email already exists for an account in our system.  Please use another email address.' ] );
}

add_action( "wp_ajax_gpx_validate_email", "gpx_validate_email" );
add_action( "wp_ajax_nopriv_gpx_validate_email", "gpx_validate_email" );


/**
 *
 *
 *
 *
 */
function gpx_user_login_fn() {
    global $wpdb;

    $credentials = [];

    if ( defined( 'GPX_RECAPTCHA_V3_DISABLED' ) && ! GPX_RECAPTCHA_V3_DISABLED ) {
        $rec_token = $_POST['rec_token'];
        $rec_action = $_POST['rec_action'];

        $recaptcha = new \ReCaptcha\ReCaptcha( GPX_RECAPTCHA_V3_SECRET_KEY );
        $resp = $recaptcha->setExpectedAction( $rec_action )
                          ->setScoreThreshold( 0.5 )
                          ->verify( $rec_token, $_SERVER['REMOTE_ADDR'] );

        if ( ! $resp->isSuccess() ) {
            $errors = $resp->getErrorCodes();

            $pw = [ 'error' => $errors ];

            wp_send_json( $pw );
        }
    }

    if ( isset( $_POST['user_email'] ) ) {
        $userlogin = $_POST['user_email'];
    } elseif ( isset( $_POST['user_email_footer'] ) ) {
        $userlogin = $_POST['user_email_footer'];
    }
    if ( isset( $_POST['user_pass'] ) ) {
        $userpassword = $_POST['user_pass'];
    } elseif ( isset( $_POST['user_pass_footer'] ) ) {
        $userpassword = $_POST['user_pass_footer'];
    }

    $credentials['user_login'] = isset( $userlogin ) ? trim( $userlogin ) : '';
    $credentials['user_password'] = isset( $userpassword ) ? trim( $userpassword ) : '';
    $credentials['remember'] = "forever";

    $redirect = $_POST['redirect_to'] ?? '';
    $user_signon = wp_signon( $credentials, true );
    if ( is_wp_error( $user_signon ) ) {
        $user_signon_response = [
            'loggedin' => false,
            'message' => 'Wrong username or password.',
        ];
        wp_send_json( $user_signon_response );
    }
    $userid = $user_signon->ID;
    $changed = true;
    if ( in_array( 'gpx_member', $user_signon->roles ) ) {
        $disabled = (bool) get_user_meta( $userid, 'GPXOwnerAccountDisabled', true );
        $sql = $wpdb->prepare( "SELECT count(*) FROM wp_GPR_Owner_ID__c WHERE user_id=%s", $userid );
        $intervals = (int) $wpdb->get_var( $sql );
        if ( $disabled || ! $intervals ) {
            $msg = "Please contact us for help with your account.";
            $redirect = site_url();

            $user_signon_response = [
                'loggedin' => false,
                'redirect_to' => $redirect,
                'message' => $msg,
            ];
            wp_destroy_current_session();
            wp_clear_auth_cookie();
            wp_set_current_user( 0 );
            wp_send_json( $user_signon_response );
        }

        $changed = (bool) get_user_meta( $userid, 'gpx_upl', true );
    }
    if ( $changed ) {
        $msg = 'Login sucessful, redirecting...';
    } else {
        $msg = 'Update Username!';
        $redirect = 'username_modal';
    }
    $user_signon_response = [
        'loggedin' => true,
        'redirect_to' => $redirect,
        'message' => $msg,
    ];

    wp_send_json( $user_signon_response );
}

add_action( "wp_ajax_gpx_user_login", "gpx_user_login_fn" );
add_action( "wp_ajax_nopriv_gpx_user_login", "gpx_user_login_fn" );


/**
 *
 *
 *
 *
 */
function do_password_reset() {
    if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
        wp_send_json_error( [ 'action' => 'pwset', 'msg' => 'Invalid Request' ] );
    }

    $recaptcha = new \ReCaptcha\ReCaptcha( GPX_RECAPTCHA_V3_SECRET_KEY );
    $resp = $recaptcha->setExpectedAction( 'set_password' )
                      ->setScoreThreshold( 0.5 )
                      ->verify( $_POST['rec_token'], $_SERVER['REMOTE_ADDR'] );
    if ( ! $resp->isSuccess() ) {
        wp_send_json_error( [ 'error' => $resp->getErrorCodes() ] );
    }

    if ( ! isset( $_POST['rp_key'], $_POST['rp_login'] ) ) {
        wp_send_json_error(
            [
                'action' => 'pwreset',
                'msg' => 'You used an invalid login.  Please request a new reset.',
            ]
        );
    }

    $user = check_password_reset_key( $_POST['rp_key'], $_POST['rp_login'] );
    if ( is_wp_error( $user ) && $user->get_error_code() === 'expired_key' ) {
        wp_send_json_error(
            [
                'action' => 'pwreset',
                'msg' => 'Your key has expired.  Please request a new reset.',
            ]
        );
    } elseif ( ! $user || is_wp_error( $user ) ) {
        wp_send_json_error(
            [
                'action' => 'pwreset',
                'msg' => 'You used an invalid login.  Please request a new reset.',
            ]
        );
    }
    if ( empty( $_POST['pass1'] ) ) {
        wp_send_json_error(
            [
                'action' => 'pwset',
                'msg' => 'Password is empty.',
            ]
        );
    }
    if ( $_POST['pass1'] != $_POST['pass2'] ) {
        wp_send_json_error(
            [
                'action' => 'pwset',
                'msg' => "Passwords don't match",
            ]
        );
    }

    reset_password( $user, $_POST['pass1'] );
    wp_send_json_success(
        [
            'action' => 'login',
            'msg' => 'Password update successful.  You may now login with the new password.',
            'redirect' => home_url(),
        ]
    );
}

add_action( "wp_ajax_do_password_reset", "do_password_reset" );
add_action( "wp_ajax_nopriv_do_password_reset", "do_password_reset" );


/**
 *
 *
 *
 *
 */
function gpx_change_password() {
    $cid = $_POST['cid'];
    $pw1 = $_POST['chPassword'];

    $data['msg'] = 'System unavailable. Please try again later.';

    $user = get_user_by( 'ID', $cid );

    if ( isset( $_POST['hash'] ) ) {
        $pass = $_POST['hash'];

        if ( $user && wp_check_password( $pass, $user->data->user_pass, $user->ID ) ) {
            $up = wp_set_password( $pw1, $user->ID );
            $data['msg'] = 'Password Updated!';
        } else {
            $data['msg'] = 'Wrong password!';
        }
    } else {
        $up = wp_set_password( $pw1, $user->ID );
        $data['msg'] = 'Password Updated!';
    }


    wp_send_json( $data );
}

add_action( "wp_ajax_gpx_change_password", "gpx_change_password" );
add_action( "wp_ajax_nopriv_gpx_change_password", "gpx_change_password" );


/**
 *
 *
 *
 *
 */
function gpx_load_data() {
    $load = $_GET['load'] ?? null;
    if($load !== 'load_transactions'){
        // @deprecated
        // @TODO this should never be called except with load_transactions
        gpx_show_404();
    }

    $term = ( ! empty( $_GET['term'] ) ) ? sanitize_text_field( $_GET['term'] ) : '';
    $return = gpx_load_transactions( $_GET['cid'] );
    wp_send_json( $return );
}

add_action( "wp_ajax_gpx_load_data", "gpx_load_data" );
add_action( "wp_ajax_nopriv_gpx_load_data", "gpx_load_data" );


/**
 *
 *
 *
 *
 */
function get_username_modal() {
    $data['html'] = '<ul class="gform_fields">
						<li class="message-box"><span>For security reasons, please update your username and password.</span></li>
						<li class="gfield">
							<label for="modal_username" class="gfield_label"></label>
							<div class="ginput_container">
								<input type="text" id="modal_username" name="modal_username" placeholder="Username" class="validate modal_reset_username" autocomplete="off" required="required"/>
							</div>
						</li>
						<li class="gfield">
							<label for="modal_password" class="gfield_label"></label>
							<div class="ginput_container">
								<input id="login_password" id="modal_password" name="user_pass" type="password" placeholder="Password" class="validate modal_reset_username" autocomplete="off" required="required"/>
							</div>
						</li>
						<li class="gfield">
							<label for="modal_repeat_password" class="gfield_label"></label>
							<div class="ginput_container">
								<input id="login_password" id="modal_repeat_password" name="user_pass_repeat" type="password" placeholder="Repeat Password" class="validate modal_reset_username" autocomplete="off" required="required"/>
							</div>
						</li>
						<li class="gfield">
							<a href="#" class="call-modal-pwreset">Forgot password?</a>
						</li>
					</ul>';
    wp_send_json( $data );
}

add_action( 'wp_ajax_get_username_modal', 'get_username_modal' );
add_action( 'wp_ajax_get_username_modal', 'get_username_modal' );
add_action( 'wp_ajax_nopriv_get_username_modal', 'get_username_modal' );


/**
 *
 *
 *
 *
 */
//It looks like when the user is setup WordPress/code is defaulting the display name to be the owners 'member id' instead of the phonetic name.
//Need to correct so it doesn't happen in the future and fix all accounts on file.
function gpx_format_user_display_name_on_login( $username ) {
    $user = get_user_by( 'login', $username );
    if ( ! $user ) {
        return;
    }

    $first_name = get_user_meta( $user->ID, 'first_name', true );
    $last_name = get_user_meta( $user->ID, 'last_name', true );

    $full_name = trim( $first_name . ' ' . $last_name );

    if ( ! empty( $full_name ) && ( $user->data->display_name != $full_name ) ) {
        $userdata = [
            'ID' => $user->ID,
            'display_name' => $full_name,
        ];

        wp_update_user( $userdata );
    }
}

add_action( 'wp_login', 'gpx_format_user_display_name_on_login' );
add_action( 'user_register', 'gpx_format_user_display_name_on_login' );

/**
 *
 *
 *
 *
 */
function gpx_userswitch_toolbar_link( $wp_admin_bar ) {
    $sutext = '';
    if ( isset( $_COOKIE['switchuser'] ) ) {
        $cid = gpx_get_switch_user_cookie();
        $usermeta = (object) array_map( function ( $a ) {
            return $a[0];
        }, get_user_meta( $cid ) );
        $fname = $usermeta->SPI_First_Name__c ?? null;
        if ( empty( $fname ) ) {
            $fname = $usermeta->first_name;
        }
        $lname = $usermeta->SPI_Last_Name__c ?? null;
        if ( empty( $lname ) ) {
            $lname = $usermeta->last_name;
        }
        $sutext = 'Logged In As: ' . $fname . ' ' . $lname . ' ';
    }
    $args = [
        'id' => 'gpx_switch',
        'title' => $sutext . 'Switch Owners',
        'href' => '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_all',
        'meta' => [ 'class' => 'my-toolbar-switch' ],
    ];
    $wp_admin_bar->add_node( $args );
}

add_action( 'admin_bar_menu', 'gpx_userswitch_toolbar_link', 999 );


/**
 * @param $roles
 * @param $user_id
 *
 * @return bool
 */
function check_user_role( $roles, $user_id = null ) {
    if ( $user_id ) {
        $user = get_userdata( $user_id );
    } else {
        $user = wp_get_current_user();
    }
    if ( empty( $user ) ) {
        return false;
    }
    foreach ( $user->roles as $role ) {
        if ( in_array( $role, $roles ) ) {
            return true;
        }
    }

    return false;
}

function gpx_switchusers() {
    if ( ! check_user_role( [ 'gpx_admin', 'gpx_call_center', 'administrator', 'administrator_plus' ] ) ) {
        wp_send_json_error( [ 'message' => 'You do not have permission to switch users' ], 403 );
    }
    $userid = $_POST['cid'] ?? null;
    if ( ! $userid ) {
        wp_send_json_error( [ 'message' => 'No userid provided' ], 404 );
    }
    $user = get_userdata( $userid );
    if ( ! $user ) {
        wp_send_json_error( [ 'message' => 'User not found' ], 404 );
    }

    setcookie( 'switchuser', (int) $userid, 0, '/', '', true, false );
    update_user_meta( $userid, 'last_login', time() );
    update_user_meta( $userid, 'searchSessionID', $userid . "-" . time() );
    //It looks like when the user is setup WordPress/code is defaulting the display name to be the owners 'member id' instead of the phonetic name.
    //Need to correct so it doesn't happen in the future and fix all accounts on file.

    $first_name = get_user_meta( $userid, 'first_name', true );
    $last_name = get_user_meta( $userid, 'last_name', true );
    $full_name = trim( $first_name . ' ' . $last_name );
    if ( ! empty( $full_name ) && ( $user->display_name != $full_name ) ) {
        $userdata = [
            'ID' => $userid,
            'display_name' => $full_name,
        ];
        wp_update_user( $userdata );
    }
    $return = [ 'success' => true ];
    wp_send_json( $return );
}


add_action( "wp_ajax_gpx_switchusers", "gpx_switchusers" );
add_action( "wp_ajax_nopriv_gpx_switchusers", "gpx_switchusers" );

function gpx_get_switch_user_cookie(): ?int {
    if ( ! is_user_logged_in() ) return null;
    $cid = get_current_user_id();
    if ( check_user_role( [ 'gpx_admin', 'gpx_call_center', 'administrator', 'administrator_plus' ], $cid ) ) {
        return (int)($_COOKIE['switchuser'] ?? $cid);
    }

    return $cid;
}

function gpx_is_agent(): bool {
    if ( ! is_user_logged_in() ) return false;
    $cid = gpx_get_switch_user_cookie();

    return $cid !== get_current_user_id();
}

function gpx_is_owner(): bool {
    if ( ! is_user_logged_in() ) return false;
    $cid = gpx_get_switch_user_cookie();

    return $cid === get_current_user_id();
}


/**
 * @param ?int $id
 *
 * @return ?stdClass
 */
function gpx_get_usermeta( int $id = null ) {
    if ( ! $id ) $id = get_current_user_id();
    $meta = get_user_meta( $id );
    if ( ! $meta ) {
        return null;
    }

    return (object) array_map( function ( $a ) { return $a[0]; }, $meta );
}

/**
 *
 *
 *
 *
 */
function gpx_switchusers_hook() {
    do_action( 'gpx_switchusers_hook' );
}


/**
 *
 *
 *
 *
 */
function gpx_update_displayname() {
    global $wpdb;
    $sql = "SELECT ID FROM wp_users WHERE user_email=''";
    $rows = $wpdb->get_results( $sql );

    foreach ( $rows as $row ) {
        $email = get_user_meta( $row->ID, 'Email', true );
        if ( $email ) {
            $wpdb->update( 'wp_users', [ 'user_email' => $email ], [ 'ID' => $row->ID ] );
        }
    }

    $return = [ 'success' => true ];
    wp_send_json( $return );
}

add_action( "wp_ajax_ggpx_update_displayname", "gpx_update_displayname" );
add_action( "wp_ajax_nopriv_gpx_update_displayname", "gpx_update_displayname" );


/**
 *
 *
 *
 *
 */
function gpx_switch_crEmail() {
    $option = $_POST['active'];

    update_option( 'gpx_global_cr_email_send', $option );

    $return = [ 'success' => true ];
    wp_send_json( $return );
}

add_action( "wp_ajax_gpx_switch_crEmail", "gpx_switch_crEmail" );

