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
 
class JFWSTregistertype {
	
	public function __construct()  
    {	
		$this->mb_key_settings = '_jfwst_metabox_2';
		$this->mb_key_steps = '_jfwst_metabox_1';
		$this->post_type='websitetour';
		
		add_action( 'init', array($this, 'jfwbst_custom_type_menus'));
		add_action( 'add_meta_boxes', array($this, 'jfwbst_custom_meta_box_websitetour'));
		
		//Add custom post types colums
		add_filter('manage_websitetour_posts_columns', array($this,'jfwbst_columns_set_websitetour'), 10);
	    add_action('manage_websitetour_posts_custom_column', array($this,'jfwbst_columns_content_websitetour'), 10, 2);
		
		//Add css
		add_action('admin_enqueue_scripts', array( $this, 'jfwstb_backend_css'));
		
	}
	
	
	function jfwstb_backend_css() {
		wp_enqueue_style('jfwstb_backend_css', plugins_url('../assets/css/backend.css', __FILE__));
		//wp_enqueue_style('jquery-ui_css', plugins_url('../assets/css/jquery-ui.css', __FILE__));
        //wp_enqueue_script('jquery_ui_backend_js', plugins_url('../assets/js/jquery-ui.js', __FILE__));     
	}
	
		
	function jfwbst_columns_set_websitetour($old_columns)
	{
		$websitetour_col = array(
			'cb'     => '<input type="checkbox">',
			'title'  => __('Tour Title', 'websitetourbuilder'),
			'shortocode'   => __('Shortcode', 'websitetourbuilder'),
			'display' => __('Display As', 'websitetourbuilder'),
			'lightboxtheme'   => __('LightBox', 'websitetourbuilder'),
			'popuptheme'   => __('Popup', 'websitetourbuilder'),
			'date'   => __('Date', 'websitetourbuilder'),
			
			
		);
		return $websitetour_col;
	}
	
	// CREATE TWO FUNCTIONS TO HANDLE THE COLUMN
	function jfwbst_columns_content_websitetour($column_name, $post_id) {
		switch($column_name) {
			case 'shortocode':
				$shortocode = "<code>[websitetour id=".'"'.$post_id.'"'."]</code>";
				echo $shortocode ? $shortocode : __('Shortcode not present', 'websitetourbuilder');
			break;
			case 'display':
				$mbcol = get_post_meta( $post_id, $this->mb_key_settings, true );
				echo isset($mbcol['displayas']) ? ucfirst($mbcol['displayas']) : __('Post meta not present', 'websitetourbuilder');	
			break;	
			case 'lightboxtheme':
				$mbcol = get_post_meta( $post_id, $this->mb_key_settings, true );
				echo isset($mbcol['lightboxtheme']) ? ucfirst($mbcol['lightboxtheme']) : __('Post meta not present', 'websitetourbuilder');	
			break;	
			case 'popuptheme':
				$mbcol = get_post_meta( $post_id, $this->mb_key_settings, true );
				echo isset($mbcol['popuptheme']) ? ucfirst($mbcol['popuptheme']) : __('Post meta not present', 'websitetourbuilder');	
			break;	
			default:
		break;
		}
	}

	function jfwbst_custom_meta_box_websitetour() {
		//$post_types = get_post_types();
		$post_type="websitetour";
		//foreach ( $post_types as $post_type )
		add_meta_box( 'joomlaforce_follow', __('Follow Us', 'websitetourbuilder'),  array($this, 'jfwbst_custom_meta_box_follow_render'), $post_type, 'side', 'high' );
		add_meta_box( 'joomlaforce_using',  __('How to Use WebSiteTour Builder', 'websitetourbuilder'),  array($this, 'jfwbst_custom_meta_box_using_render'),  $post_type, 'side', 'high' );
		
	}
	
	
	
	function jfwbst_custom_meta_box_using_render() {
			
		
		global $post;
	   // Get the data
	   $webtourid = $post->ID;
		/*$info = "you can use this: <code>LenSlider::lenslider_output_slider(\'{$webtourid}\')</code>
					 <code>echo LenSlider::lenslider_output_slider(\'{$webtourid}\', false)</code>
					 <code>echo do_shortcode(\'[lenslider id={$webtourid}]\')</code></p>";*/
		
		$smb = sprintf(__('How to Use WebSiteTour Builder Description', 'websitetourbuilder'), $webtourid, $webtourid);
				
		echo $smb;
		

		
	}
	
	
	function jfwbst_custom_meta_box_follow_render() {
		
		
		// $fbi = '<img src="' . plugins_url( 'websitetourbuilder/assets/img/icon_facebook.png' ) . '" > ';
		//$yti = '<img src="' . plugins_url( 'websitetourbuilder/assets/img/icon_youtube_ma.png' ) . '" > ';
			
		 $fmb = __('Follow us details', 'websitetourbuilder');
		echo $fmb;
		
		
		
	}
			
	function jfwbst_custom_type_menus() {
	   
       
       
		
    	// creazione (registrazione) del custom post type
		$ctname = __('Tour Builder', 'websitetourbuilder');
		
    	register_post_type( 'websitetour', /* nome del custom post type */
        // aggiungiamo ora tutte le impostazioni necessarie, in primis definiamo le varie etichette mostrate nei menù
       		 array('labels' => array(
				'name' => $ctname, /* Nome, al plurale, dell'etichetta del post type. */
				'singular_name' => __('Tour', 'websitetourbuilder'), /* Nome, al singolare, dell'etichetta del post type. */
				'all_items' => __('All Tours', 'websitetourbuilder'), /* Testo mostrato nei menu che indica tutti i contenuti del post type */
				'add_new' => __('Add New Tour', 'websitetourbuilder'), /* Il testo per il pulsante Aggiungi. */
				'add_new_item' => __('Add New Tour', 'websitetourbuilder'), /* Testo per il pulsante Aggiungi nuovo post type */
				'edit_item' => __('Edit Tour', 'websitetourbuilder'), /*  Testo per modifica */
				'new_item' => __('New Tour', 'websitetourbuilder'), /* Testo per nuovo oggetto */
				'view_item' => __('View Tour', 'websitetourbuilder'), /* Testo per visualizzare */
				'search_items' => __('Find Tour', 'websitetourbuilder'), /* Testo per la ricerca*/
				'not_found' =>  __('No Tour Found', 'websitetourbuilder'), /* Testo per risultato non trovato */
				'not_found_in_trash' => __('No Tour Found in the Trash', 'websitetourbuilder'), /* Testo per risultato non trovato nel cestino */
				'parent_item_colon' => ''
				), /* Fine dell'array delle etichette */   
				'description' => __('WebSite Tour Builder for Wordpress', 'websitetourbuilder'), /* Una breve descrizione del post type */
				'public' => false, /* Definisce se il post type sia visibile sia da front-end che da back-end */
				'publicly_queryable' => false, /* Definisce se possono essere fatte query da front-end */
				'exclude_from_search' => true, /* Definisce se questo post type è escluso dai risultati di ricerca */
				'show_ui' => true, /* Definisce se deve essere visualizzata l'interfaccia di default nel pannello di amministrazione */
				
				//Nuovi
				'show_in_nav_menus' => true,
				'show_in_menu' => true,
				'show_in_admin_bar ' => false,
				//
				
				'query_var' => false,
				'menu_position' => 60, /* Definisce l'ordine in cui comparire nel menù di amministrazione a sinistra */
				//'menu_icon' => get_stylesheet_directory_uri() . '/img/custom-post-icon.png', /* Scegli l'icona da usare nel menù per il posty type */
				//'menu_icon' => 'dashicons-admin-generic', /* Scegli l'icona da usare nel menù per il posty type */
			
				'menu_icon' => plugins_url('../assets/img/menu-icon.png',  __FILE__), // 16px16
				
				'rewrite'   => array( 'slug' => 'websitetour', 'with_front' => false ), /* Puoi specificare uno slug per gli URL */
				'has_archive' => 'false', /* Definisci se abilitare la generazione di un archivio (equivalente di archive-libri.php) */
				'capability_type' => 'page', /* Definisci se si comporterà come un post o come una pagina */
				'hierarchical' => false, /* Definisci se potranno essere definiti elementi padri di altri */
				/* la riga successiva definisce quali elementi verranno visualizzati nella schermata di creazione del post */
			   // 'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'sticky')
				'supports' => array( 'title')
				//'register_meta_box_cb' => array($this,'display_movie_review_meta_box')
			) /* fine delle opzioni */
    	); /* fine della registrazione */
	    
		//Add Help Page
		add_action('admin_menu' , array($this, 'jfwbst_helpdesk_spec')); 
		//add_action('admin_menu' , array($this, 'jfwbst_docs_spec')); 
		
		
		//add_action('admin_menu',  array($this, 'add_configuration'));

	
		
		
		

	}
    
    // adds menu page
/*function add_configuration() {
                
	add_submenu_page( 'edit.php?post_type=websitetour', "Configuration", "Configuration", "manage_options", 'admin_settings.php', array($this, 'slider_settings' ) );
}*/


function slider_settings() 
{
    include('admin_settings.php');
}        
	
	
	function jfwbst_helpdesk_spec() {
		add_submenu_page('edit.php?post_type=websitetour', 
						'Custom Post Type Admin 1', 
						__('Support', 'websitetourbuilder'), 
						'manage_options', 
						basename(__FILE__), 
						array($this, 'jfwbst_helpdesk_html')
						);		

	}
	
	function jfwbst_helpdesk_html () { 
	   
        echo __('Support Description', 'websitetourbuilder');
       
	}
	



	
} // EOC

?>