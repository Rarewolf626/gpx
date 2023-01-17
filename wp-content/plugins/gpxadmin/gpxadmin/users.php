<?php
use GPX\Repository\OwnerRepository;



/**
 *
 *
 *
 *
 */
function gpx_check_login()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    if(is_user_logged_in())
    {
        //check/update member credit for use in checkout
        $cid = gpx_get_switch_user_cookie();

        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );

        $credit = $gpx->DAEGetMemberCredits($usermeta->DAEMemberNo, $cid);

        $data = array('success'=>true);
    }
    else
        $data = array('login'=>true);

    wp_send_json($data);
    wp_die();
}
add_action("wp_ajax_gpx_check_login","gpx_check_login");
add_action("wp_ajax_nopriv_gpx_check_login", "gpx_check_login");


/**
 *
 *
 *
 *
 */
function get_gpx_users_switch()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_get_gpx_users_switch();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_users_switch', 'get_gpx_users_switch');
add_action('wp_ajax_nopriv_get_gpx_users_switch', 'get_gpx_users_switch');


/**
 *
 *
 *
 *
 */
function get_gpx_switchuage()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $usage = '';
    $type = '';

    if(isset($_GET['usage']))
        $usage = $_GET['usage'];
    if(isset($_GET['type']))
        $type = $_GET['type'];

    $data = $gpx->return_gpx_switchuage($usage, $type);

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_switchuage', 'get_gpx_switchuage');
add_action('wp_ajax_nopriv_get_gpx_switchuage', 'get_gpx_switchuage');


/**
 *
 *
 *
 *
 */
function gpx_update_names()
{
    global $wpdb;
    $sql = "SELECT * FROM wp_users WHERE user_email = '' LIMIT 1";
    $rows = $wpdb->get_rows($sql);
}
add_action('wp_ajax_gpx_update_names', 'gpx_update_names');
add_action('wp_ajax_nopriv_gpx_update_names', 'gpx_update_names');




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
        // recaptcha code
        require_once GPXADMIN_PLUGIN_DIR . '/libraries/recaptcha-master/src/autoload.php';


        $recaptcha = new \ReCaptcha\ReCaptcha( GPX_RECAPTCHA_V3_SECRET_KEY );
        $resp      = $recaptcha->setExpectedAction( 'password_reset' )
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

    require_once GPXADMIN_PLUGIN_DIR . '/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin( GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR );
    $pw  = $gpx->retrieve_password( $userlogin );
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
    $email = OwnerRepository::instance()->get_email($user_data->ID);

    if ( ! $email ) {
        // the user does npt have a custom email address
        return $message;
    }

    if ( $email && mb_strtolower($email) === mb_strtolower($user_data->user_email) ) {
        // the email address in the profile matches the one in the user table
        // return the message content to be sent to the default address
        return $message;
    }
    $blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
    $title    = sprintf( __( '[%s] Password Reset' ), $blogname );
    $title    = apply_filters( 'retrieve_password_title', $title );

    if ( ! wp_mail( $email, wp_specialchars_decode( $title ), $message ) ) {
        // there was an error sending to the email address in the user profile.
        // return the message content so the default user email is used
        return $message;
    }
    // return false so it doesn't try to send to the user's default email address
    return false;
}, 10, 4 );





/**
 *
 *
 *
 *
 */
function gpx_validate_email()
{
    header('content-type: application/json; charset=utf-8');

    if(isset($_REQUEST['tp']))
    {
        if($_REQUEST['tp'] == 'email')
        {
            $val = sanitize_email($_REQUEST['val']);
            $exists = email_exists($val);
        }
        if($_REQUEST['tp'] == 'username')
        {
            $val = sanitize_text_field($_REQUEST['val']);
            $exists = username_exists($val);
        }
        if($exists)
        {
            $return['used'] = 'exists';
            $user = get_user_by('ID', $exists);

            $username = $user->user_login;
            $email = $user->user_email;
            $id = $user->ID;

            $return['html'] = '<h4>That '.$_REQUEST['tp']. ' is already in use.  Would you like to use this account?</h4><h4><button class="btn btn-primary" id="tp-use" data-email="'.$email.'" data-username="'.$username.'" data-id="'.$id.'">Yes</button> <button class="btn btn-secondary" id="tp-no">No</button>';
        }
    }
    else
    {
        $exists = email_exists($_REQUEST['email']);

        if($exists)
        {
            $return = array('error'=>'That email already exists for an account in our system.  Please use another email address.' );
        }
        else
        {
            $return = array("sucess"=>true);
        }
    }
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_validate_email","gpx_validate_email");
add_action("wp_ajax_nopriv_gpx_validate_email", "gpx_validate_email");





/**
 *
 *
 *
 *
 */
function gpx_user_login_fn() {
    require_once GPXADMIN_PLUGIN_DIR.'/libraries/recaptcha-master/src/autoload.php';
    header("access-control-allow-origin: *");
    global $wpdb;

    $credentials = array();

    if(defined('GPX_RECAPTCHA_V3_DISABLED') && !GPX_RECAPTCHA_V3_DISABLED) {
        $rec_token  = $_POST['rec_token'];
        $rec_action = $_POST['rec_action'];

        $recaptcha = new \ReCaptcha\ReCaptcha( GPX_RECAPTCHA_V3_SECRET_KEY );
        $resp      = $recaptcha->setExpectedAction( $rec_action )
            ->setScoreThreshold( 0.5 )
            ->verify( $rec_token, $_SERVER['REMOTE_ADDR'] );

        if ( ! $resp->isSuccess() ) {
            $errors = $resp->getErrorCodes();

            $pw = [ 'error' => $errors ];

            wp_send_json( $pw );
        }
    }

    if(isset($_POST['user_email']))
    {
        $userlogin = $_POST['user_email'];
    }
    elseif(isset($_POST['user_email_footer']))
    {
        $userlogin = $_POST['user_email_footer'];
    }
    if(isset($_POST['user_pass']))
    {
        $userpassword = $_POST['user_pass'];
    }
    elseif(isset($_POST['user_pass_footer']))
    {
        $userpassword = $_POST['user_pass_footer'];
    }

    $credentials['user_login'] = isset($userlogin) ? trim($userlogin) : '';
    $credentials['user_password'] = isset($userpassword) ? trim($userpassword) : '';
    $credentials['remember'] = "forever";

    $redirect = $_POST['redirect_to'] ?? '';
    $user_signon = wp_signon($credentials, true);
    if (is_wp_error($user_signon)) {
        $user_signon_response = array(
            'loggedin' => false,
            'message' => 'Wrong username or password.'
        );
        wp_send_json($user_signon_response);
    }
    $userid = $user_signon->ID;
    $changed = true;
    if (in_array('gpx_member', $user_signon->roles)) {
        $disabled = (bool)get_user_meta($userid, 'GPXOwnerAccountDisabled', true);
        $sql = $wpdb->prepare("SELECT count(*) FROM wp_GPR_Owner_ID__c WHERE user_id=%s", $userid);
        $interval = (int)$wpdb->get_var($sql);

        if ($disabled || !$interval) {
            $msg = "Please contact us for help with your account.";
            $redirect = site_url();

            $user_signon_response = array(
                'loggedin' => false,
                'redirect_to' => $redirect,
                'message' => $msg,
            );
            wp_destroy_current_session();
            wp_clear_auth_cookie();
            wp_set_current_user( 0 );
            wp_send_json($user_signon_response);
        }

        $changed = (bool)get_user_meta($userid, 'gpx_upl', true);
    }
    if ($changed) {
        $msg =  'Login sucessful, redirecting...';
    } else {
        $msg = 'Update Username!';
        $redirect = 'username_modal';
    }
    $user_signon_response = array(
        'loggedin' => true,
        'redirect_to' => $redirect,
        'message' => $msg,
    );

    wp_send_json($user_signon_response);
}
add_action("wp_ajax_gpx_user_login","gpx_user_login_fn");
add_action("wp_ajax_nopriv_gpx_user_login", "gpx_user_login_fn");







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

    require_once GPXADMIN_PLUGIN_DIR . '/libraries/recaptcha-master/src/autoload.php';
    $recaptcha = new \ReCaptcha\ReCaptcha( GPX_RECAPTCHA_V3_SECRET_KEY );
    $resp      = $recaptcha->setExpectedAction( 'set_password' )
        ->setScoreThreshold( 0.5 )
        ->verify( $_POST['rec_token'], $_SERVER['REMOTE_ADDR'] );
    if ( ! $resp->isSuccess() ) {
        wp_send_json_error( [ 'error' => $resp->getErrorCodes() ] );
    }

    if(!isset($_POST['rp_key'], $_POST['rp_login'])){
        wp_send_json_error(
            [
                'action' => 'pwreset',
                'msg'    => 'You used an invalid login.  Please request a new reset.',
            ]
        );
    }

    $user = check_password_reset_key( $_POST['rp_key'], $_POST['rp_login'] );
    if ( is_wp_error( $user ) && $user->get_error_code() === 'expired_key' ) {
        wp_send_json_error(
            [
                'action' => 'pwreset',
                'msg'    => 'Your key has expired.  Please request a new reset.',
            ]
        );
    } elseif ( ! $user || is_wp_error( $user ) ) {
        wp_send_json_error(
            [
                'action' => 'pwreset',
                'msg'    => 'You used an invalid login.  Please request a new reset.',
            ]
        );
    }
    if ( empty( $_POST['pass1'] ) ) {
        wp_send_json_error(
            [
                'action' => 'pwset',
                'msg'    => 'Password is empty.',
            ]
        );
    }
    if ( $_POST['pass1'] != $_POST['pass2'] ) {
        wp_send_json_error(
            [
                'action' => 'pwset',
                'msg'    => "Passwords don't match",
            ]
        );
    }

    reset_password( $user, $_POST['pass1'] );
    wp_send_json_success(
        [
            'action'   => 'login',
            'msg'      => 'Password update successful.  You may now login with the new password.',
            'redirect' => home_url(),
        ]
    );
}
add_action("wp_ajax_do_password_reset","do_password_reset");
add_action("wp_ajax_nopriv_do_password_reset", "do_password_reset");



/**
 *
 *
 *
 *
 */
function gpx_change_password()
{
    $cid = $_POST['cid'];
    $pw1 = $_POST['chPassword'];

    $data['msg'] = 'System unavailable. Please try again later.';

    $user = get_user_by('ID', $cid);

    if(isset($_POST['hash']))
    {
        $pass = $_POST['hash'];

        if ( $user && wp_check_password( $pass, $user->data->user_pass, $user->ID) )
        {
            $up = wp_set_password($pw1, $user->ID);
            $data['msg'] = 'Password Updated!';
        }
        else
            $data['msg'] = 'Wrong password!';
    }
    else
    {
        $up = wp_set_password($pw1, $user->ID);
        $data['msg'] = 'Password Updated!';
    }


    echo wp_send_json($data);
    exit();
}
add_action("wp_ajax_gpx_change_password","gpx_change_password");
add_action("wp_ajax_nopriv_gpx_change_password", "gpx_change_password");



/**
 *
 *
 *
 *
 */
function gpx_load_data()
{
    header('content-type: application/json; charset=utf-8');
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    if(isset($_GET['load']))
        $load = $_GET['load'];

    $return = $gpx->$load($_GET['cid']);

    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_load_data","gpx_load_data");
add_action("wp_ajax_nopriv_gpx_load_data", "gpx_load_data");



/**
 *
 *
 *
 *
 */
function get_username_modal()
{
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
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_get_username_modal', 'get_username_modal');
add_action('wp_ajax_get_username_modal', 'get_username_modal');
add_action('wp_ajax_nopriv_get_username_modal', 'get_username_modal');




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
    if(!$user) return;

    $first_name = get_user_meta( $user->ID, 'first_name', true );
    $last_name = get_user_meta( $user->ID, 'last_name', true );

    $full_name = trim( $first_name . ' ' . $last_name );

    if ( ! empty( $full_name ) && ( $user->data->display_name != $full_name ) ) {
        $userdata = array(
            'ID' => $user->ID,
            'display_name' => $full_name,
        );

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
    if(isset($_COOKIE['switchuser']))
    {
        $cid = gpx_get_switch_user_cookie();
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta($cid) );
        $fname = $usermeta->SPI_First_Name__c ?? null;
        if(empty($fname)) {
            $fname = $usermeta->first_name;
        }
        $lname = $usermeta->SPI_Last_Name__c ?? null;
        if(empty($lname)) {
            $lname = $usermeta->last_name;
        }
        $sutext = 'Logged In As: '.$fname.' '.$lname.' ';
    }
    $args = array(
        'id'    => 'gpx_switch',
        'title' => $sutext.'Switch Owners',
        'href'  => '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_all',
        'meta'  => array( 'class' => 'my-toolbar-switch' )
    );
    $wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'gpx_userswitch_toolbar_link', 999 );


/**
 * @param $roles
 * @param $user_id
 * @return bool
 */
function check_user_role($roles, $user_id = null) {
    if ($user_id) $user = get_userdata($user_id);
    else $user = wp_get_current_user();
    if (empty($user)) return false;
    foreach ($user->roles as $role) {
        if (in_array($role, $roles)) {
            return true;
        }
    }
    return false;
}

function gpx_switchusers()
{
    if (!check_user_role( [ 'gpx_admin', 'gpx_call_center', 'administrator', 'administrator_plus' ] ) ) {
        wp_send_json_error( [ 'message' => 'You do not have permission to switch users' ], 403 );
    }
    $userid = $_POST['cid'] ?? null;
    if ( ! $userid ) {
        wp_send_json_error( [ 'message' => 'No userid provided' ], 404 );
    }
    $user   = get_userdata( $userid );
    if ( ! $user ) {
        wp_send_json_error( [ 'message' => 'User not found' ], 404 );
    }
    $disabled = (bool) get_user_meta( $userid, 'GPXOwnerAccountDisabled', true );
    if ( $disabled ) {
        wp_send_json_error( [ 'message' => 'Account was disabled' ], 403 );
    }
    setcookie('switchuser', (int)$userid, 0, '/', '', true, false);
    setcookie('gpx-cart', null, -1, '/', parse_url(site_url(), PHP_URL_HOST), true, false);

    update_user_meta( $userid, 'last_login', time() );
    update_user_meta( $userid, 'searchSessionID', $userid . "-" . time() );

    //It looks like when the user is setup WordPress/code is defaulting the display name to be the owners 'member id' instead of the phonetic name.
    //Need to correct so it doesn't happen in the future and fix all accounts on file.

    $first_name = get_user_meta( $userid, 'first_name', true );
    $last_name  = get_user_meta( $userid, 'last_name', true );
    $full_name  = trim( $first_name . ' ' . $last_name );
    if ( ! empty( $full_name ) && ( $user->display_name != $full_name ) ) {
        $userdata = [
            'ID'           => $userid,
            'display_name' => $full_name,
        ];
        wp_update_user( $userdata );
    }
    $return = [ 'success' => true ];
    wp_send_json( $return );
}

add_action( "wp_ajax_gpx_switchusers","gpx_switchusers");
add_action("wp_ajax_nopriv_gpx_switchusers", "gpx_switchusers");


/**
 *
function gpx_get_user_id(){
$user = wp_get_current_user();
if(!$user) return null;
// @TODO check for permissions
if(!$is_allowed){
return $user->ID;
}
return $_COOKIE['switch_user'] ?? $user->ID;
}
 *
 */
function gpx_get_switch_user_cookie () {

    if (check_user_role(array('gpx_admin','gpx_call_center','administrator','administrator_plus'))) {
        return $_COOKIE['switchuser'] ?? get_current_user_id();
    }
    return get_current_user_id();
}

/**
 *
 *
 *
 *
 */
function gpx_switchusers_hook()
{
    do_action('gpx_switchusers_hook');
}



/**
 *
 *
 *
 *
 */
function gpx_update_displayname()
{
    global $wpdb;
    $sql = "SELECT ID FROM wp_users WHERE user_email=''";
    $rows = $wpdb->get_results($sql);

    foreach($rows as $row)
    {
        $email = get_user_meta($row->ID, 'Email', true);
        if($email){
            $wpdb->update('wp_users', array('user_email'=>$email), array('ID'=>$row->ID));
        }
    }

    $return = array('success'=>true);
    wp_send_json($return);
}
add_action("wp_ajax_ggpx_update_displayname","gpx_update_displayname");
add_action("wp_ajax_nopriv_gpx_update_displayname", "gpx_update_displayname");


/**
 *
 *
 *
 *
 */
function gpx_switch_gf()
{

    $option = $_POST['active'];

    update_option('gpx_global_guest_fees', $option);

    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_switch_gf","gpx_switch_gf");


/**
 *
 *
 *
 *
 */
function gpx_switch_crEmail()
{

    $option = $_POST['active'];

    update_option('gpx_global_cr_email_send', $option);

    $return = array('success'=>true);
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_switch_crEmail","gpx_switch_crEmail");

