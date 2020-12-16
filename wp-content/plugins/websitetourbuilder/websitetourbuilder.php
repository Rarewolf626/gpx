<?php

/**
 * Plugin Name: WebSite Tour Builder
 * Plugin URI: http://wordpress.org/plugins/websitetourbuilder/
 * Description: Web Site Tour Builder for Wordpress is a powerfull Tour Plugin, which can be used as a site tour, helpers, guides or tooltips. Use the Web Site Tour plugin for your website, products, applications, landing pages or something else. The plugin is very easy to use and allow you to create a very cool tour in simple dinamics steps. Web Site Tour Builder gives you the ability to create amazing tour which easily arouse visitor interest, with a User Friendly Backend, highly customizable solution to build your tour into your site.
 * Version: 1.3
 * Author: JoomlaForce Team
 * Author URI: http://www.joomlaforce.com
 * Copyright   Copyright (C) 2014. All rights reserved.
 * License     GNU General Public License version 2 or later; see LICENSE.txt
 */
 

define('JFWST_PATH', plugin_dir_path(__FILE__) ); 
define('JFWST_ADMIN', plugin_dir_path(__FILE__).'backend/' ); 
define('JFWST_FRONT', plugin_dir_path(__FILE__).'frontend/' ); 

class JFWSTPlugin {  

	private static $instance;
	
    public function __construct()  
    {
        
		
		if(!is_admin()){

			/* Add Functions for Js and Css
			*  Se lo shortcode esiste allora richiama una funzione che controlla il post e richiama gli stili e gli script
			*  perform the check when the_posts() function is called */
			//require_once JFWST_PATH.'includes/class.shortcodesearch.php';
			//$jfwst_shortcodesearch = new JFWSTshortcodesearch();			
			
			require_once JFWST_PATH.'includes/class.shortcodehandler.php';
			$jfwst_shortcodehandler = new JFWSTshortcodehandler();			

		} 	
		// WORDPRESS CUSTOM MENU TYPE
		require_once JFWST_ADMIN.'class.jfwbst_registertype.php';
		$jfwbst_registertype = new JFWSTregistertype();
		
		// METABOX CODE AND CSS FOR custom meta boxes OF WPALCHEMY
		require_once JFWST_ADMIN.'class.jfwbst_framework.php';
		$jfwbst_framework = new JFWSTframework();

		// ADD EDITOR BUTTON
		include_once JFWST_PATH.'includes/editorbtn/class.tinymce.php';
		$jfwbst_tinymce = new JFWSTtinymce();
		

    }  
	
	//ADMIN AREA
	static function GetInstance()
      {
          
          if (!isset(self::$instance))
          {
              self::$instance = new self();
          }
          return self::$instance;
      }
	 
 
} // end class


$jfwstPlugin = JFWSTPlugin::GetInstance();
//$jfwstPlugin->jfwst_init_framework(); 
//$jfwstPlugin = new  JFWSTPlugin();


class load_language 
{
    public function __construct()
    {
    add_action('init', array($this, 'load_language'));
    }

     public function load_language()
    {
        load_plugin_textdomain('websitetourbuilder', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
    }
}

$load_language = new load_language;


?>
