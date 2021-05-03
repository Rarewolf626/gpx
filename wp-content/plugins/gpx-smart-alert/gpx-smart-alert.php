<?php 
/*
 * Plugin Name: GPR Smart Alert 
 * Plugin URI: http://www.4eightyeast.com
 * Version: 1.0
 * Description: Alert bar for GPR branded websites.
 * Author: Chris Goering
 * Author URI: http://www.4eightyeast.com
 * License: GPLv2 or later
 */

define( 'GPR_SA_PLUGIN_DIR', __FILE__ );
define( 'GPR_SA_DIR', trailingslashit( dirname(__FILE__) ).'/includes' );
define( 'GPR_SA_URI', plugins_url('', __FILE__).'/includes' );

define('GPR_SA', 'gprsmartbar');
define( 'GPR_PREFIX', 'gpx' );

define( 'GPR_SA_VERS', '1.3');

$directoryIncludes = [
    'cpt',
    'filters',
//     'shortcodes',
    'setup',
    'functions',
//     'actions',
    'ajax',
];
foreach($directoryIncludes as $di)
{
    foreach(glob(GPR_SA_DIR. "/".$di."/*.php") as $file){
        if($file == GPR_SA_DIR."/".$di."/index.php")
        {
            continue;
        }
        require $file;
    }
}