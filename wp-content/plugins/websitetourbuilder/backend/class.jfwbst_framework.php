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

class JFWSTframework {
	
	//public $test = 'accesso variabile';
	
	
	public function __construct()  
    {	
	

		// Class WpAlchemy including
		include_once JFWST_ADMIN.'framework/MetaBox.php';
		include_once JFWST_ADMIN.'framework/MediaAccess.php';
		$wpalchemy_media_access = new WPAlchemy_MediaAccess();
		
		//ADD Metabox Style
		add_action( 'init', array($this, 'jfwst_metabox_styles' ));
		//ADD Metabox n° 1 - Spec.
		add_action( 'init',  array($this, 'jfwst_metabox_2_spec'));
		//ADD Metabox n° 2 - Spec.
		add_action( 'init',  array($this, 'jfwst_metabox_1_spec'));
		//add_action( 'init',  array($this, 'jfwst_metabox_right1_spec'));
			
		/* 
		 * Recreate the default filters on the_content
		 * this will make it much easier to output the meta content with proper/expected formatting
		*/
		add_filter( 'meta_content', 'wptexturize'        );
		add_filter( 'meta_content', 'convert_smilies'    );
		add_filter( 'meta_content', 'convert_chars'      );
		add_filter( 'meta_content', 'wpautop'            );
		add_filter( 'meta_content', 'shortcode_unautop'  );
		add_filter( 'meta_content', 'prepend_attachment' );
		add_filter( 'meta_content', 'do_shortcode');
        
        
        // added by evatix - 25 Nov 2015
        add_filter( 'wp_default_editor', create_function( '', 'return "html";' ) );
        
		//$this->myglobalvar = & $GLOBALS['full1_mb'];
		
		
	

	}
	
	
	
	/*function prova () {
	$uffa="<br>accesso alla funzione <br>";
	return $uffa;
	}*/
		
	function jfwst_metabox_styles()
	{
		if ( is_admin() )
		{			
			//wp_enqueue_style( 'jfwst-metabox', JFWST_ADMIN.'/metaboxes/meta.css', array(), '1.0', 'all' );
			wp_enqueue_style( 'jfwst-metabox', plugins_url('/metaboxes/meta.css', __FILE__), array(), '1.0', 'all' );
			
		}
	}
	
	function jfwst_metabox_right1_spec()
	{
		
		
	    $mbright1title = '<h2><span class="wp-menu-image dashicons-before dashicons-images-alt2">'.__("Like this Plugin", 'websitetourbuilder').'</span></h2>';
		
		global $right1_mb;
		$right1_mb = new WPAlchemy_MetaBox(
		array
		(
			'id' => '_jfwst_metabox_right1',
			'title' => $mbright1title,
			//'types' => array('page', 'events'), // added only for pages and to custom post type "events"
			'types' => array('page', 'websitetour'), // added only for pages and to custom post type "events"
			'context' => 'normal', // same as above, defaults to "normal"
			'priority' => 'high', // same as above, defaults to "high"
			//'template' => get_stylesheet_directory() . '/metaboxes/full-meta.php'
			//'template' => JFWST_ADMIN.'metaboxes/websitetour_metaboxes_steps.php'
			'template' => JFWST_ADMIN.'metaboxes/websitetour_metaboxes_right.php',
			'hide_screen_option' => TRUE
		));
		
		//return $right1_mb;

	}
	
	function jfwst_metabox_1_spec()
	{
		
		
	    $mb1title = '<h2><span class="wp-menu-image dashicons-before dashicons-images-alt2">'.__("Steps Settings", 'websitetourbuilder').'</span></h2>';
		global $full1_mb;
		$full1_mb = new WPAlchemy_MetaBox(
		array
		(
			'id' => '_jfwst_metabox_1',
			'title' => $mb1title,
			//'types' => array('page', 'events'), // added only for pages and to custom post type "events"
			//'types' => array('post', 'websitetour'), // added only for pages and to custom post type "events"
			'types' => array('postxxx', 'websitetour'), // added only for pages and to custom post type "events"		
			'context' => 'normal', // same as above, defaults to "normal"
			'priority' => 'high', // same as above, defaults to "high"
			//'template' => get_stylesheet_directory() . '/metaboxes/full-meta.php'
			//'template' => JFWST_ADMIN.'metaboxes/websitetour_metaboxes_steps.php'
			'template' => JFWST_ADMIN.'metaboxes/websitetour_metaboxes_steps.php',
			'init_action' => array($this, 'jfwst_kia_metabox_init'), 
			'save_filter' => array($this, 'jfwst_kia_repeating_save_filter'),
			'view' => 'closed',
			'hide_screen_option' => FALSE
		));
		return $full1_mb;

	}
	
	function jfwst_metabox_2_spec()
	{
	    $mb2title = '<h2><span class="wp-menu-image dashicons-before dashicons-admin-generic"> '.__("Web Site Tour Builder - General Settings", 'websitetourbuilder').'</span></h2>';
		global $full2_mb;
		$full2_mb = new WPAlchemy_MetaBox(
		array
		(
			'id' => '_jfwst_metabox_2',
			'title' => $mb2title,
			//'types' => array('page', 'events'), // added only for pages and to custom post type "events"
			//'types' => array('post', 'websitetour'), // added only for pages and to custom post type "events"
			//vedere questo parametro
			//inseristo un type casuale per non farlo attivare in nessun tipo di pagina eccetto quella del website tour
			'types' => array('postxxx', 'websitetour'), // added only for pages and to custom post type "events"
			'context' => 'normal', // same as above, defaults to "normal"
			'priority' => 'high', // same as above, defaults to "high"
			//'template' => get_stylesheet_directory() . '/metaboxes/full-meta.php'
			'template' => JFWST_ADMIN.'metaboxes/websitetour_metaboxes_settings.php',
			'save_filter' => array($this, 'jfwst_kia_single_save_filter'),
			'init_action' => array($this, 'jfwst_kia_metabox_init'),
			'view' => 'opened',
			'hide_screen_option' => FALSE
		));
		return $full2_mb;
	}

	
		/* 
	 * Sanitize the input similar to post_content
	 * @param array $meta - all data from metabox
	 * @param int $post_id
	 * @return array
	 */
	public function jfwst_kia_single_save_filter( $meta, $post_id ){
		if( isset( $meta['test_editor'] ) ){
			$meta['test_editor'] = sanitize_post_field( 'post_content', $meta['test_editor'], $post_id, 'db' );
		}
		return $meta;
	
	}
	
		/* 
	 * Sanitize the input similar to post_content
	 * @param array $meta - all data from metabox
	 * @param int $post_id
	 * @return array
	 */
	function jfwst_kia_repeating_save_filter( $meta, $post_id ){
	
		if ( is_array( $meta ) && ! empty( $meta ) ){
	
			array_walk ( $meta, function ( &$item, $key ) { 
				if( isset( $item['textarea'] ) ){
					$item['textarea'] = sanitize_post_field( 'post_content', $item['textarea'], $post_id, 'db' );
				}
	
			} );
	
		}
		
		
	
		return $meta;
	
	}
	
		/* 
	 * Enqueue styles and scripts specific to metaboxs
	 */
	function jfwst_kia_metabox_init(){
		
		//make sure we enqueue some scripts just in case ( only needed for repeating metaboxes )
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'jquery-ui-mouse' );
		wp_enqueue_script( 'jquery-ui-sortable' );
	
		$suffix = defined( SCRIPT_DEBUG ) && SCRIPT_DEBUG ? '' : '';

        global $wp_scripts;
        //echo plugin_dir_path( '/wp-content/plugins/websitetourbuilder/backend/metaboxes/quicktags.min.js');
        //exit;

        $wp_scripts->registered['quicktags']->src = plugins_url( '/metaboxes/quicktags.min.js', __FILE__);
        
        
        
      /*  
echo '<pre>';
		print_r($wp_scripts->registered['quicktags']->src);
        exit;*/
        
        //wp_dequeue_script( 'quicktags' );
        
        //wp_register_script( 'quicktags', plugins_url( '/metaboxes/quicktags.min.js', __FILE__));
        
		//wp_enqueue_script( 'quicktags');

		
        wp_register_script( 'kia-metabox', plugins_url( '/metaboxes/kia-metabox' . $suffix . '.js', __FILE__));
        
		// special script for dealing with repeating textareas- needs to run AFTER all the tinyMCE init scripts, so make 'editor' a requirement
		wp_enqueue_script( 'kia-metabox', plugins_url( '/metaboxes/kia-metabox' . $suffix . '.js', __FILE__), array( 'jquery', 'word-count', 'editor', 'quicktags', 'wplink', 'wp-fullscreen', 'media-upload' ), '1.3', true );
		
		
		
	}

	
} // EOC



?>