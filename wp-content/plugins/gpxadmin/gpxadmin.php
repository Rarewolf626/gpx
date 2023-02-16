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
use Dompdf\Dompdf;
use Symfony\Component\HttpFoundation\Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once __DIR__.'/api/functions/class.gpxretrieve.php';
require_once __DIR__.'/dashboard/functions/class.gpxadmin.php';
date_default_timezone_set('America/Los_Angeles');
defined('GPXADMIN_VERSION') OR define( 'GPXADMIN_VERSION', '2.16');
defined('GPXADMIN_DIR') OR define( 'GPXADMIN_DIR', trailingslashit( __DIR__ ) );
defined('GPXADMIN_PLUGIN_DIR') OR define( 'GPXADMIN_PLUGIN_DIR', trailingslashit( __DIR__ ).'/dashboard' );
defined('GPXADMIN_API_DIR') OR define( 'GPXADMIN_API_DIR', trailingslashit( __DIR__ ).'/api' );
defined('GPXADMIN_PLUGIN_URI') OR define( 'GPXADMIN_PLUGIN_URI', plugins_url('', __FILE__).'/dashboard' );
defined('GPXADMIN_API_URI') OR define( 'GPXADMIN_API_URI', plugins_url('', __FILE__).'/api' );
defined('SOAP_CLIENT_BASEDIR') OR define( "SOAP_CLIENT_BASEDIR", GPXADMIN_API_DIR . "/lib/salesforce/soapclient" );

require_once __DIR__.'/autoloader.php';
require_once __DIR__.'/services.php';
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/api/lib/salesforce/soapclient/SObject.php';
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
        wp_register_style('bootstrap_css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
        wp_enqueue_style('bootstrap_css');
        wp_enqueue_style('bootrap_table_css', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/bootstrap-table.min.css');
        wp_enqueue_style('bootrap_table_filter_css', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/extensions/filter-control/bootstrap-table-filter-control.min.css');
        wp_enqueue_style('fontawesome_css', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css');
        wp_enqueue_style('nprogress_css', GPXADMIN_PLUGIN_URI.'/vendors/nprogress/nprogress.css');
        wp_enqueue_style('prettify_css', GPXADMIN_PLUGIN_URI.'/vendors/google-code-prettify/bin/prettify.min.css');
        wp_enqueue_style('jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css');
        wp_enqueue_style('timepicker_css', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css');
        wp_enqueue_style('daterangepicker_css', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.27/daterangepicker.css');
        wp_enqueue_style('fontawesome_iconpicker_css', GPXADMIN_PLUGIN_URI.'/vendors/fontawesome-iconpicker/css/fontawesome-iconpicker.min.css');
        wp_enqueue_style('gpx_admin_custom_css', GPXADMIN_PLUGIN_URI.'/build/css/custom.css', '', GPXADMIN_VERSION);
        wp_enqueue_style('bootrap_select_css', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css');
        wp_enqueue_style('bootrap_multiselect_css', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css');
        wp_register_script('jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js', array('jquery'));
        wp_enqueue_script('jquery_ui');
        wp_enqueue_script("jquery-ui-draggable");
        wp_enqueue_script("jquery-ui-sortable");
        wp_enqueue_script('timepicker_js', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js', array('jquery_ui'));
        wp_register_script('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'));
        wp_enqueue_script('bootstrap');
        wp_register_script('bootstrap_table_js', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/bootstrap-table.min.js', array('bootstrap'));
        wp_enqueue_script('bootstrap_table_js');
        wp_enqueue_script('bootsrap_table_fc_js', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/extensions/filter-control/bootstrap-table-filter-control.min.js', array('bootstrap_table_js'));
        wp_enqueue_script('bootsrap_table_export_js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.18.0/extensions/export/bootstrap-table-export.min.js', array('bootstrap_table_js'));
        wp_enqueue_script('bootsrap_tableexport_js', '//rawgit.com/hhurz/tableExport.jquery.plugin/master/tableExport.js', array('bootstrap_table_js'));
        wp_enqueue_script('fastclick_jquery', GPXADMIN_PLUGIN_URI.'/vendors/fastclick/lib/fastclick.js', array('bootstrap'));
        wp_enqueue_script('bootstrap_select_jquery', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js', array('bootstrap'));
        wp_enqueue_script('bootstrap_multiselect_jquery', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js', array('bootstrap'));
        wp_enqueue_script('javascript_cookie', '//cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.3/js.cookie.min.js', array('bootstrap'));
        wp_register_script('moment_js', '//cdn.jsdelivr.net/momentjs/latest/moment.min.js', array('bootstrap'));
        wp_enqueue_script('moment_js');
        wp_enqueue_script('daterangepicker_js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.27/daterangepicker.js', array('moment_js'));
        wp_enqueue_script('fontawesome_iconpicker_js', GPXADMIN_PLUGIN_URI.'/vendors/fontawesome-iconpicker/js/fontawesome-iconpicker.min.js', array('bootstrap'));
        wp_enqueue_script('nprogress_jquery', GPXADMIN_PLUGIN_URI.'/vendors/nprogress/nprogress.js', array('bootstrap'));
        wp_enqueue_script('wysiwyg_jquery', GPXADMIN_PLUGIN_URI.'/vendors/bootstrap-wysiwyg/js/bootstrap-wysiwyg.min.js', array('bootstrap'));
        wp_enqueue_script('hotkeys_jquery', GPXADMIN_PLUGIN_URI.'/vendors/jquery.hotkeys/jquery.hotkeys.js', array('bootstrap'));
        wp_enqueue_script('prettify_jquery', GPXADMIN_PLUGIN_URI.'/vendors/google-code-prettify/src/prettify.js', array('bootstrap'));
        wp_enqueue_script('lzstring', GPXADMIN_PLUGIN_URI.'/vendors/lzstring/lz-string.min.js', array('bootstrap'), GPXADMIN_VERSION);
        wp_enqueue_script('custom_jquery', GPXADMIN_PLUGIN_URI.'/build/js/custom.js', array('bootstrap'), GPXADMIN_VERSION);
    }
    if(isset($_GET['page']) && $_GET['page'] == 'gpx-admin-page')
        add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );

    add_action( 'admin_menu', 'gpx_admin_menu' );

    function gpx_admin_menu()
    {
        add_menu_page( 'GPX Admin Page', 'GPX Admin', 'gpx_admin', 'gpx-admin-page', 'gpx_admin_page', 'dashicons-tickets', 6  );
    }

    function gpx_admin_page()
    {
        $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
        $page = '';
        if(isset($_GET['gpx-pg']))
            $page = $_GET['gpx-pg'];
        echo $gpx->getpage($page, 'admin');
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

require_once GPXADMIN_PLUGIN_DIR.'/vendors/dompdf/lib/html5lib/Parser.php';
require_once GPXADMIN_PLUGIN_DIR.'/vendors/dompdf/lib/php-font-lib/src/FontLib/Autoloader.php';
require_once GPXADMIN_PLUGIN_DIR.'/vendors/dompdf/lib/php-svg-lib/src/autoload.php';
require_once GPXADMIN_PLUGIN_DIR.'/vendors/dompdf/src/Autoloader.php';
\Dompdf\Autoloader::register();

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


