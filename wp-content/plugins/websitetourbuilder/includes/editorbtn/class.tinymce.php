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

class JFWSTtinymce {
	
	public function __construct()  
     {
		
		//Get Tours titles, ids
		add_action('admin_head', array( $this, 'jfwstb_post_formvalue'));
		//Funzioni per aggiungere l'editor
		add_action('admin_head', array( $this, 'jfwstb_addtinymce_button'));
		add_action('admin_enqueue_scripts', array( $this, 'jfwstb_tinymce_css'));
				
		/*//FOR FULL SCREENMODE - DA IMPLEMENTARE !!!!
		add_action('admin_head',  array( $this, 'wordup_add_fullscreen_button'));
		add_action('admin_enqueue_scripts',  array( $this, 'wordup_add_css'));*/
	
    }  

	function jfwstb_post_formvalue() {
	
	  global $wpdb;
	  $querystr = "
		SELECT $wpdb->posts.post_title , $wpdb->posts.id
		FROM $wpdb->posts
		WHERE $wpdb->posts.post_status = 'publish' 
		AND $wpdb->posts.post_type = 'websitetour'
		ORDER BY $wpdb->posts.post_date DESC
	  ";
		$pageposts = $wpdb->get_results($querystr, OBJECT);
		$output="";
		foreach ($pageposts as $tourpost){
			//$output .= '{text: "'.$tourpost->post_title.'", value: "'.$tourpost->post_title.'"},';
			$output[] = array( 'text' => $tourpost->post_title, 'value' => $tourpost->post_title, 'id' => $tourpost->id );
		}
		//print_r($output);
		?>
		<script type="text/javascript">
		<?php 
		//Passo l'array nel javascript
		//var post_id = '<?php global $post; echo $post->ID; 
		?>
		var mce_options = '<?php echo json_encode($output); ?>';
		</script>
		<?php
	  
	}

	function jfwstb_addtinymce_button() {
		global $typenow;
		// sprawdzamy czy user ma uprawnienia do edycji postów/podstron
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
		return;
		}
		// weryfikujemy typ wpisu
		if( ! in_array( $typenow, array( 'post', 'page' ) ) )
			return;
		// sprawdzamy czy user ma włączony edytor WYSIWYG
		if ( get_user_option('rich_editing') == 'true') {
			add_filter('mce_external_plugins', array( $this, 'jfwstb_add_tinymce_plugin'));
			add_filter('mce_buttons', array( $this, 'jfwstb_register_tinymce_button'));
		}
	}

	function jfwstb_add_tinymce_plugin($plugin_array) {
		$plugin_array['jfwstb_btn'] = plugins_url( '/popuptour.js', __FILE__ ); // CHANGE THE BUTTON SCRIPT HERE
		return $plugin_array;
	}

	function jfwstb_register_tinymce_button($buttons) {
	   array_push($buttons, "jfwstb_btn");
	   return $buttons;
	}
	
	function jfwstb_tinymce_css() {
		wp_enqueue_style('jfwstb_tinymce', plugins_url('/style.css', __FILE__));
	}
	
	
	//FUNZIONI PER BOTTONE IN FULL SCREEN MODE
	function wordup_add_fullscreen_button() {
	  global $typenow;
	  // sprawdzamy czy user ma uprawnienia do edycji postów/podstron
	  if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') )
		return;
	  // weryfikujemy typ wpisu
	  if( ! in_array( $typenow, array( 'post', 'page' ) ) )
		return;
	  // sprawdzamy czy user ma włączony edytor WYSIWYG
	  if ( get_user_option('rich_editing') == 'true') {
		 add_filter( 'wp_fullscreen_buttons', array( $this,'wordup_add_button_to_fullscreen_editor'));
	  }
	}
	
	function wordup_add_button_to_fullscreen_editor( $buttons ){
    $buttons['icon dashicons-wordpress'] = array(
        'title' => 'WordPress button',
        'onclick' => "if(tinyMCE.activeEditor) {
          tinyMCE.activeEditor.insertContent('Hello World!');
        } else {
          var cursor = jQuery('#content').prop('selectionStart');
          if(!cursor) cursor = 0;
          var content = jQuery('#content').val();
          var textBefore = content.substring(0,  cursor );
          var textAfter  = content.substring( cursor, content.length );
          jQuery('#content').val( textBefore + 'Hello World!' + textAfter );
        }",
        'both' => true
    );
    return $buttons;
}

	function wordup_add_css() {
		wp_enqueue_style('wordup-tinymce', plugins_url('/style.css', __FILE__));
	}

}//end class