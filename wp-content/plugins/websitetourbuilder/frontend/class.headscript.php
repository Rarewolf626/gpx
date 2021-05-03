<?php
 
 /**
 * Web Site Tour Builder for Wordpress
 *
 * @package   websitetourbuilder
 * @author    JoomlaForce Team [joomlaforce.com]
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @link      http://joomlaforce.com
 * @copyright Copyright © 2014 JoomlaForce
 */

class JFWSTheadscript {
	
	public function __construct() {
		
		//check to see if we are in the front-end or back-end
		if(is_admin())
		{
			return; 
			//leave since we are in the back-end. bye.
		}
		
        
        add_action('wp_head','hook_custom_javascript');
		

	}
    

    function hook_custom_javascript() {
    
    	$output="<script> var click_to_close = '".__("Click to close", 'websitetourbuilder')."'; </script>";
    
    	echo $output;
    }
    
	
	function jfwstb_addhead_scripts() {
        
		wp_register_script('jfwstb_gquery', plugins_url('/assets/js/gquery-1.7.2.js', __FILE__));
		wp_register_script('jfwstb_pagetour',  plugins_url('/assets/js/jquery.wp_websitetour.js', __FILE__));
		wp_register_script('jfwstb_chrony',  plugins_url('/assets/js/jquery.gotour.chrony.js', __FILE__));

		wp_enqueue_script('jfwstb_gquery');
		wp_enqueue_script('jfwstb_pagetour');
		wp_enqueue_script('jfwstb_chrony');
		
		/*if($params->get('show_timer_controls',0))
            $document->addScript($uri.'modules/mod_websitetourbuilder/assets/js/jquery.gotour.chrony.js');*/
		
	}
	function jfwstb_addhead_styles() {
		
		//$metabox1 =  new JFWSTshortcodehandler;
		//echo $mb = $metabox1->jfwbst_getmetabox_settings_values($metabox1);
		
		//wp_register_style( 'jfwstb_popup', plugins_url('/theme/popup/default.css', __FILE__), array(), '1.0', 'all' );
		//Generic Style
		wp_register_style( 'jfwstb_tourstyle', plugins_url('/assets/css/style.css', __FILE__), array(), '1.0', 'all' );
		wp_enqueue_style( 'jfwstb_tourstyle' );
		
		//wp_enqueue_style( 'jfwstb_popup' );
		
		
	}

	
}
?>