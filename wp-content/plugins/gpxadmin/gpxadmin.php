<?php
/*
* Plugin Name: GPX Admin
* Plugin URI: http://www.4eightyeast.com
* Version: 1.0
* Description: GPX custom dashboard and functionality -- VEST
* Author: Chris Goering
* Author URI: http://www.4eightyeast.com
* License: GPLv2 or later
*/

use GPX\GPXAdmin\Router\GpxAdminRouter;
use GPX\Exception\NoMatchingRouteException;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once __DIR__.'/dashboard/functions/class.gpxadmin.php';
date_default_timezone_set('America/Los_Angeles');
defined('GPXADMIN_VERSION') OR define( 'GPXADMIN_VERSION', '2.17');
defined('GPXADMIN_DIR') OR define( 'GPXADMIN_DIR', trailingslashit( __DIR__ ) );
defined('GPXADMIN_PLUGIN_DIR') OR define( 'GPXADMIN_PLUGIN_DIR', trailingslashit( __DIR__ ).'dashboard' );
defined('GPXADMIN_API_DIR') OR define( 'GPXADMIN_API_DIR', trailingslashit( __DIR__ ).'api' );
defined('GPXADMIN_PLUGIN_URI') OR define( 'GPXADMIN_PLUGIN_URI', plugins_url('', __FILE__).'/dashboard' );
defined('GPXADMIN_API_URI') OR define( 'GPXADMIN_API_URI', plugins_url('', __FILE__).'/api' );
defined('SOAP_CLIENT_BASEDIR') OR define( "SOAP_CLIENT_BASEDIR", GPXADMIN_API_DIR . "/lib/salesforce/soapclient" );

require_once __DIR__.'/autoloader.php';
require_once __DIR__.'/services.php';
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/api/lib/salesforce/soapclient/SObject.php';
require_once __DIR__.'/api/functions/class.gpxretrieve.php';
require_once __DIR__.'/dashboard/functions/class.gpxadmin.php';
require_once __DIR__.'/dashboard/models/gpxmodel.php';
require_once __DIR__.'/api/models/tripadvisormodel.php';
require_once __DIR__.'/api/functions/class.tripadvisor.php';
require_once __DIR__.'/dashboard/libraries/ssp.class.php';
require_once __DIR__.'/api/models/shiftfourmodel.php';
require_once __DIR__.'/api/functions/class.shiftfour.php';
require_once __DIR__.'/api/models/icemodel.php';
require_once __DIR__.'/api/functions/class.ice.php';
require_once __DIR__.'/api/functions/class.salesforce.php';
require_once( SOAP_CLIENT_BASEDIR . '/SforcePartnerClient.php' );
require_once( SOAP_CLIENT_BASEDIR . '/SforceHeaderOptions.php' );

// initialize service container
$container = gpx();
// initialize query builder
/** @var Illuminate\Database\Capsule\Manager $capsule */
$capsule = gpx(Illuminate\Database\Capsule\Manager::class);

//include scripts/styles
if( is_admin() ) {
    function load_custom_wp_admin_style() {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
        wp_register_style('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', [], '3.3.7');
        wp_enqueue_style('bootstrap_table', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/bootstrap-table.min.css', ['bootstrap'], '1.18.0');
        wp_enqueue_style('bootstrap_table_filter', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/extensions/filter-control/bootstrap-table-filter-control.min.css', ['bootstrap_table'], '1.18.0');
        wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', [], '4.7.0');
        wp_enqueue_style('material_icons', 'https://fonts.googleapis.com/icon?family=Material+Icons', []);
        wp_enqueue_style('nprogress', GPXADMIN_PLUGIN_URI.'/vendors/nprogress/nprogress.css');
        wp_enqueue_style('prettify', GPXADMIN_PLUGIN_URI.'/vendors/google-code-prettify/bin/prettify.min.css');
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css', [], '4.0.13');
        wp_enqueue_style('jquery_ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css', [], '1.12.1');
        wp_enqueue_style('timepicker', 'https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css', [], '1.3.5');
        wp_enqueue_style('daterangepicker', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.27/daterangepicker.css', ['bootstrap'], '2.1.27');
        wp_enqueue_style('fontawesome_iconpicker', GPXADMIN_PLUGIN_URI.'/vendors/fontawesome-iconpicker/css/fontawesome-iconpicker.min.css', ['fontawesome']);
        wp_enqueue_style('gpx_admin_custom_css', GPXADMIN_PLUGIN_URI.'/build/css/custom.css', [], GPXADMIN_VERSION);
        wp_enqueue_style('bootstrap_select', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css', ['bootstrap'], '1.12.4');
        wp_enqueue_style('bootstrap_multiselect', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css', ['bootstrap'], '0.9.13');
        wp_enqueue_script('parsley', 'https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js', array(), '2.9.2');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js', array('jquery'), '4.0.13');
        wp_register_script('jquery_ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', array('jquery'), '1.12.1');
        wp_enqueue_script("jquery-ui-draggable");
        wp_enqueue_script("jquery-ui-sortable");
        wp_enqueue_script('timepicker', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js', array('jquery_ui'), '1.3.5');
        wp_enqueue_script('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'), '3.3.7');
        wp_register_script('bootstrap_table', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/bootstrap-table.min.js', array('bootstrap'), '1.18.0');
        wp_enqueue_script('bootstrap_table');
        wp_enqueue_script('bootstrap_table_filter', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/extensions/filter-control/bootstrap-table-filter-control.min.js', array('bootstrap_table'), '1.18.0');
        wp_enqueue_script('bootstrap_table_export', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/extensions/export/bootstrap-table-export.min.js', array('bootstrap_table'), '1.18.0');
        wp_enqueue_script('bootstrap_tableexport', '//rawgit.com/hhurz/tableExport.jquery.plugin/master/tableExport.js', array('bootstrap_table_export'));
        wp_enqueue_script('fastclick_jquery', GPXADMIN_PLUGIN_URI.'/vendors/fastclick/lib/fastclick.js', array('bootstrap'));
        wp_enqueue_script('bootstrap_select', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js', array('bootstrap'), '1.12.4');
        wp_enqueue_script('bootstrap_multiselect', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js', array('bootstrap'), '0.9.13');
        wp_enqueue_script('javascript_cookie', '//cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.3/js.cookie.min.js', array('bootstrap'), '2.1.3');
        wp_enqueue_script('moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js', array('bootstrap'), '2.29.4');
        wp_enqueue_script('daterangepicker', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.27/daterangepicker.js', array('moment', 'jquery', 'bootstrap'), '2.1.27');
        wp_enqueue_script('fontawesome_iconpicker', GPXADMIN_PLUGIN_URI.'/vendors/fontawesome-iconpicker/js/fontawesome-iconpicker.min.js', array('bootstrap'));
        wp_enqueue_script('nprogress', GPXADMIN_PLUGIN_URI.'/vendors/nprogress/nprogress.js', array('bootstrap', 'jquery'));
        wp_enqueue_script('wysiwyg_jquery', GPXADMIN_PLUGIN_URI.'/vendors/bootstrap-wysiwyg/js/bootstrap-wysiwyg.min.js', array('bootstrap'));
        wp_enqueue_script('hotkeys_jquery', GPXADMIN_PLUGIN_URI.'/vendors/jquery.hotkeys/jquery.hotkeys.js', array('jquery'));
        wp_enqueue_script('prettify', GPXADMIN_PLUGIN_URI.'/vendors/google-code-prettify/src/prettify.js', array('bootstrap', 'jquery'));
        wp_enqueue_script('lzstring', GPXADMIN_PLUGIN_URI.'/vendors/lzstring/lz-string.min.js', array('bootstrap'), GPXADMIN_VERSION);
        wp_enqueue_script('custom_jquery', GPXADMIN_PLUGIN_URI.'/build/js/custom.js', array('bootstrap','jquery'), GPXADMIN_VERSION);
    }
    if(isset($_GET['page']) && $_GET['page'] == 'gpx-admin-page') {
        add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );
    }
    add_action( 'admin_menu', 'gpx_admin_menu' );

    function gpx_admin_menu()
    {
        add_menu_page( 'GPX Admin Page', 'GPX Admin', 'gpx_admin', 'gpx-admin-page', 'gpx_admin_page', 'dashicons-tickets', 6  );
    }

    function gpx_admin_page()
    {
        try {
            /** @var GpxAdminRouter $router */
            $router = gpx(GpxAdminRouter::class);
            $router->dispatch();
        } catch (NoMatchingRouteException $exception){
            $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
            $page = $_GET['gpx-pg'] ?? '';
            echo $gpx->getpage($page, 'admin');
        }
    }

    //if on the aerc admin page then fold the default menu
    if(isset($_GET['page']) && $_GET['page'] == 'gpx-admin-page')
    {
        /**
         * fold the default wordpress admin menu
         * @param unknown $classes
         * @return unknown
         */
        function dashboard_menu_folded( $classes ) {
            $classes .= 'folded';
            return $classes;
        }
        add_filter( 'admin_body_class','dashboard_menu_folded' );
    }
}

/*
 *      organizing functions
 */
require_once __DIR__ . '/gpxadmin/csvupload.php';
require_once __DIR__ . '/gpxadmin/regions.php';
require_once __DIR__ . '/gpxadmin/resorts.php';
require_once __DIR__ . '/gpxadmin/partners.php';
require_once __DIR__ . '/gpxadmin/exchanges.php';
require_once __DIR__ . '/gpxadmin/dae.php';
require_once __DIR__ . '/gpxadmin/owners.php';
require_once __DIR__ . '/gpxadmin/inventory.php';
require_once __DIR__ . '/gpxadmin/storelocator.php';
require_once __DIR__ . '/gpxadmin/transactions.php';
require_once __DIR__ . '/gpxadmin/promotions.php';
require_once __DIR__ . '/gpxadmin/customrequests.php';
require_once __DIR__ . '/gpxadmin/salesforce.php';
require_once __DIR__ . '/gpxadmin/users.php';
require_once __DIR__ . '/gpxadmin/emails.php';
require_once __DIR__ . '/gpxadmin/reports.php';
require_once __DIR__ . '/gpxadmin/cart.php';
require_once __DIR__ . '/gpxadmin/shift4.php';
require_once __DIR__ . '/gpxadmin/deposits.php';
require_once __DIR__ . '/gpxadmin/utils.php';
require_once __DIR__ . '/gpxadmin/email.php';


