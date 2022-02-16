<?php
/**
 * Plugin Name: Offer
 * Description: Create custom Post Type Offers
 * Author: DGT Alliance
 */

define('GPX_OFFER', 'offer');
define( 'GPX_PREFIX', 'gpx' );

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

define('GPX_BASE_PATH', plugin_dir_path(__FILE__));
define('GPX_BASE_URI', plugin_dir_url(__FILE__));

/* Init */
require 'inc/register_pt_fn.php';
require 'inc/hooks_fn.php';
require 'inc/filter_fn.php';
require 'inc/actions_fn.php';

require 'views/metaboxes/media.php';
