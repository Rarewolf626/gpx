<?php 
/*

 * Plugin Name: GPX Admin
 * Plugin URI: http://www.4eightyeast.com
 * Version: 1.0
 * Description: GPX custom password reset functionality
 * Author: Chris Goering
 * Author URI: http://www.4eightyeast.com
 * License: GPLv2 or later
 */


if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly

}

function redirect_to_custom_lostpassword() {
    if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
        if ( is_user_logged_in() ) {
            $this->redirect_logged_in_user();
            exit;
        }

        wp_redirect( home_url( 'member-password-lost' ) );
        exit;
    }
}
add_action( 'login_form_lostpassword', array( $this, 'redirect_to_custom_lostpassword' ) );

function render_password_lost_form( $attributes, $content = null ) {
    // Parse shortcode attributes
    $default_attributes = array( 'show_title' => false );
    $attributes = shortcode_atts( $default_attributes, $attributes );

    if ( is_user_logged_in() ) {
        return __( 'You are already signed in.', 'personalize-login' );
    } else {
        return $this->get_template_html( 'password_lost_form', $attributes );
    }
}
add_shortcode( 'custom-password-lost-form', array( $this, 'render_password_lost_form' ) );

function do_password_lost() {
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
        $errors = retrieve_password();
        if ( is_wp_error( $errors ) ) {
            // Errors found
            $redirect_url = home_url( 'member-password-lost' );
            $redirect_url = add_query_arg( 'errors', join( ',', $errors->get_error_codes() ), $redirect_url );
        } else {
            // Email sent
            $redirect_url = home_url( 'member-login' );
            $redirect_url = add_query_arg( 'checkemail', 'confirm', $redirect_url );
        }

        wp_redirect( $redirect_url );
        exit;
    }
}
add_action( 'login_form_lostpassword', array( $this, 'do_password_lost' ) );

function replace_retrieve_password_message( $message, $key, $user_login, $user_data ) {
    // Create new message
    $msg  = __( 'Hello!', 'personalize-login' ) . "\r\n\r\n";
    $msg .= sprintf( __( 'You asked us to reset your password for your account using the email address %s.', 'personalize-login' ), $user_login ) . "\r\n\r\n";
    $msg .= __( "If this was a mistake, or you didn't ask for a password reset, just ignore this email and nothing will happen.", 'personalize-login' ) . "\r\n\r\n";
    $msg .= __( 'To reset your password, visit the following address:', 'personalize-login' ) . "\r\n\r\n";
    $msg .= site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . "\r\n\r\n";
    $msg .= __( 'Thanks!', 'personalize-login' ) . "\r\n";

    return $msg;
}
add_filter( 'retrieve_password_message', array( $this, 'replace_retrieve_password_message' ), 10, 4 );

function redirect_to_custom_password_reset() {
    if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
        // Verify key / login combo
        $user = check_password_reset_key( $_REQUEST['key'], $_REQUEST['login'] );
        if ( ! $user || is_wp_error( $user ) ) {
            if ( $user && $user->get_error_code() === 'expired_key' ) {
                wp_redirect( home_url( 'member-login?login=expiredkey' ) );
            } else {
                wp_redirect( home_url( 'member-login?login=invalidkey' ) );
            }
            exit;
        }

        $redirect_url = home_url( 'member-password-reset' );
        $redirect_url = add_query_arg( 'login', esc_attr( $_REQUEST['login'] ), $redirect_url );
        $redirect_url = add_query_arg( 'key', esc_attr( $_REQUEST['key'] ), $redirect_url );

        wp_redirect( $redirect_url );
        exit;
    }
}
add_action( 'login_form_rp', array( $this, 'redirect_to_custom_password_reset' ) );
add_action( 'login_form_resetpass', array( $this, 'redirect_to_custom_password_reset' ) );

function render_password_reset_form( $attributes, $content = null ) {
    // Parse shortcode attributes
    $default_attributes = array( 'show_title' => false );
    $attributes = shortcode_atts( $default_attributes, $attributes );

    if ( is_user_logged_in() ) {
        return __( 'You are already signed in.', 'personalize-login' );
    } else {
        if ( isset( $_REQUEST['login'] ) && isset( $_REQUEST['key'] ) ) {
            $attributes['login'] = $_REQUEST['login'];
            $attributes['key'] = $_REQUEST['key'];

            // Error messages
            $errors = array();
            if ( isset( $_REQUEST['error'] ) ) {
                $error_codes = explode( ',', $_REQUEST['error'] );

                foreach ( $error_codes as $code ) {
                    $errors []= $this->get_error_message( $code );
                }
            }
            $attributes['errors'] = $errors;

            return $this->get_template_html( 'password_reset_form', $attributes );
        } else {
            return __( 'Invalid password reset link.', 'personalize-login' );
        }
    }
}
add_shortcode( 'custom-password-reset-form', array( $this, 'render_password_reset_form' ) );

function do_password_reset() {
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
        $rp_key = $_REQUEST['rp_key'];
        $rp_login = $_REQUEST['rp_login'];

        $user = check_password_reset_key( $rp_key, $rp_login );

        if ( ! $user || is_wp_error( $user ) ) {
            if ( $user && $user->get_error_code() === 'expired_key' ) {
                wp_redirect( home_url( 'member-login?login=expiredkey' ) );
            } else {
                wp_redirect( home_url( 'member-login?login=invalidkey' ) );
            }
            exit;
        }

        if ( isset( $_POST['pass1'] ) ) {
            if ( $_POST['pass1'] != $_POST['pass2'] ) {
                // Passwords don't match
                $redirect_url = home_url( 'member-password-reset' );

                $redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
                $redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
                $redirect_url = add_query_arg( 'error', 'password_reset_mismatch', $redirect_url );

                wp_redirect( $redirect_url );
                exit;
            }

            if ( empty( $_POST['pass1'] ) ) {
                // Password is empty
                $redirect_url = home_url( 'member-password-reset' );

                $redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
                $redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
                $redirect_url = add_query_arg( 'error', 'password_reset_empty', $redirect_url );

                wp_redirect( $redirect_url );
                exit;
            }

            // Parameter checks OK, reset password
            reset_password( $user, $_POST['pass1'] );
            wp_redirect( home_url( 'member-login?password=changed' ) );
        } else {
            echo "Invalid request.";
        }

        exit;
    }
}
add_action( 'login_form_rp', array( $this, 'do_password_reset' ) );
add_action( 'login_form_resetpass', array( $this, 'do_password_reset' ) );

