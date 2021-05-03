<?php

if (!function_exists('gpr_smart_bar_post_type')) {
    
    function gpr_smart_bar_post_type() {
        
        $labels = array(
            'name' => _x('Alerts', 'post type general name'),
            'singular_name' => _x('Alert','post type singular name'),
            'add_new' => _x('Add New', 'alert item'),
            'add_new_item' => 'Add New Alert',
            'edit_item' => 'Edit Alert',
            'new_item' => 'New ALert',
            'view_item' => 'View ALert',
            'search_items' => 'Search Alert',
            'not_found' => 'Nothing found',
            'not_found_in_trash' => 'Nothing found in Trash',
            'parent_item_colon' => ''
        );
        
        $args = array(
            'labels' => $labels,
            'public' => true,      
            'show_in_rest' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'capability_type' => 'page',
            'hierarchical' => true,
            'menu_position' => null,
            'menu_icon' => 'dashicons-megaphone',
            'supports' => array('title', 'editor'),
            'rewrite' => array ( 'slug' => 'gprsa' , 'with_front' => false )
        );
        
        register_post_type(GPR_SA, $args);
        flush_rewrite_rules();
    }
}
add_action('init', 'gpr_smart_bar_post_type');

function gpr_smart_bar_meta_boxes( $meta_boxes ) {
    $prefix = 'gprsb-';
    
    $meta_boxes[] = array(
        'id' => 'smart_bar',
        'title' => esc_html__( 'Smart Bar Information', 'four8ightyeast' ),
        'post_types' => array( GPR_SA ),
        'context' => 'after_editor',
        'priority' => 'default',
        'autosave' => 'false',
        'fields' => array(
            array(
                'type' => 'heading',
                'name' => 'Date Range',
            ),
            array(
                'id' => $prefix . 'start_date',
                'type' => 'datetime',
                'name' => esc_html__( 'Start Date', 'four8ightyeast' ),
                'js_options' => array(),
            ),
            array(
                'id' => $prefix . 'end_date',
                'type' => 'datetime',
                'name' => esc_html__( 'End Date', 'four8ightyeast' ),
                'js_options' => array(),
            ),
            array(
                'type' => 'divider',
            ),
            array(
                'type' => 'heading',
                'name' => 'Alert Colors',
            ),
            array(
                'id' => $prefix . 'background_color',
                'name' => esc_html__( 'Background Color', 'four8ightyeast' ),
                'type' => 'color',
            ),
            array(
                'id' => $prefix . 'text_color',
                'name' => esc_html__( 'Text Color', 'four8ightyeast' ),
                'type' => 'color',
            ),
            array(
                'type' => 'divider',
            ),
            array(
                'id' => $prefix . 'priority',
                'type' => 'number',
                'name' => esc_html__( 'Priority', 'four8ightyeast' ),
                'step' => '1',
            ),
            array(
                'type' => 'divider',
            ),
            array(
                'type' => 'heading',
                'name' => 'Call To action',
            ),
            array(
                'id' => $prefix . 'cta_text',
                'type' => 'text',
                'name' => esc_html__( 'Text', 'four8ightyeast' ),
                'desc' => 'Limit 25 characters',
            ),
            array(
                'id' => $prefix . 'cta_action',
                'type' => 'text',
                'name' => esc_html__( 'URL', 'four8ightyeast' ),
            ),
            array(
                'id' => $prefix . 'cta_background',
                'name' => esc_html__( 'Background Color', 'four8ightyeast' ),
                'type' => 'color',
            ),
            array(
                'id' => $prefix . 'cta_text_color',
                'name' => esc_html__( 'Text Color', 'four8ightyeast' ),
                'type' => 'color',
            ),
            array(
                'type' => 'divider',
            ),
            array(
                'id' => $prefix . 'display_page',
                'type' => 'post',
                'post_type' => 'page',
                'name' => esc_html__( 'Select Pages', 'four8ightyeast' ),
                'desc' => esc_html__( 'Leave blank to display on entire website.', 'four8ightyeast' ),
                'field_type' => 'checkbox_tree',
            ),
            array(
                'id' => $prefix . 'custom_page',
                'type' => 'text',
                'name' => esc_html__( 'Custom Page (full url)', 'four8ightyeast' ),
            ),
            array(
                'type' => 'divider',
            ),
            array(
                'id' => $prefix . 'websites',
                'name' => esc_html__( 'Select Websites', 'four8ightyeast' ),
                'type' => 'checkbox_list',
                'options' => get_websites_option(),
            ),
        ),
    );
    
    return $meta_boxes;
}
add_filter( 'rwmb_meta_boxes', 'gpr_smart_bar_meta_boxes' );

function get_websites_option() {
    
    $children = get_option('gpr_smartbar_children');
    $thisurl = get_option('siteurl');
    $domain = explode("//", $thisurl);
    $thisname = get_option('blogname');
    
    $currentChildren = unserialize( base64_decode( $children ) );
    
    $forOptions[$domain[1]] = $thisname;
    
    foreach($currentChildren as $cc)
    {
        $forOptions[$cc['url']] = $cc['name'];
    }
    
    return $forOptions;
}

// function set_custom_gpr_smart_bar_columns($columns) {
//     unset($columns['date']);
//     unset($columns['wplms_id']);

//     $columns['fee'] = __( 'Fee', 'four8ightyeast' );
//     $columns['date'] = __( 'Date', 'four8ightyeast' );

//     return $columns;
// }
// add_filter( 'manage_gpr_smart_bar_posts_columns', 'set_custom_gpr_smart_bar_columns' );

// function custom_gpr_smart_bar_column( $column, $post_id ) {
//     switch ( $column ) {
//         case 'fee' :
//             echo '$'.get_post_meta( $post_id , 'ezaz-court-fees-fee' , true );
//             break;
//     }
// }
// add_action( 'manage_gpr_smart_bar_custom_column' , 'custom_gpr_smart_bar_column', 10, 2 );

//show all the smart bars in the api
add_filter( 'rest_gpr_smart_bar_collection_params', function ( $params, WP_Post_Type $post_type ) {
    if ( GPR_SA === $post_type->name && isset( $params['per_page'] ) ) {
        $params['per_page']['maximum'] = 100;
    }
    
    return $params;
}, 10, 2 );

    
    add_action( 'rest_api_init', function () {
        register_rest_field( GPR_SA, 'meta_box', array(
            'get_callback' => function( $post_arr ) {
            $metabox['start_date'] = get_post_meta( $post_arr['id'], 'gprsb-start_date', true );
            $metabox['end_date'] = get_post_meta( $post_arr['id'], 'gprsb-end_date', true );
            $metabox['background_color'] = get_post_meta( $post_arr['id'], 'gprsb-background_color', true );
            $metabox['text_color'] = get_post_meta( $post_arr['id'], 'gprsb-text_color', true );
            $metabox['priority'] = get_post_meta( $post_arr['id'], 'gprsb-priority', true );
            $metabox['cta_text'] = get_post_meta( $post_arr['id'], 'gprsb-cta_text', true );
            $metabox['cta_action'] = get_post_meta( $post_arr['id'], 'gprsb-cta_action', true );
            $metabox['cta_background'] = get_post_meta( $post_arr['id'], 'gprsb-cta_background', true );
            $metabox['cta_text_color'] = get_post_meta( $post_arr['id'], 'gprsb-cta_text_color', true );
            $metabox['display_page'] = get_post_meta( $post_arr['id'], 'gprsb-display_page', true );
            $metabox['custom_page'] = get_post_meta( $post_arr['id'], 'gprsb-custom_page', true );
            $metabox['websites'] = get_post_meta( $post_arr['id'], 'gprsb-websites', true );
            return $metabox;
            },
            ) );
    } );

/**
 * Adds a submenu page under a custom post type parent.
 */
function gpr_register_ref_page() {
    add_submenu_page(
        'edit.php?post_type='.GPR_SA,
        __( 'Alert Settings', 'four8ightyeast' ),
        __( 'Alert Settings', 'four8ightyeast' ),
        'manage_options',
        'gpr-alert-settings',
        'gpr_smartbar_settings_page'
        );
}
add_action('admin_menu', 'gpr_register_ref_page');
/**
 * Display callback for the submenu page.
 */
function gpr_smartbar_settings_page() {
    ?>
<div class="wrap">
    <h1><?php _e( 'Alert Settings', 'four8ightyeast' ); ?></h1>
    
    <table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th scope="row"><label for="">Is Parent Website</label></th>
				<td>
    				<select name="is_parent_site" id="is_parent_site">
                    	<option value="no" data-show="parent_url_row">No</option>
                    	<option value="yes" data-show="parent_url_row">Yes</option>
    				</select>
				</td>
			</tr>
			<tr class="parent_url_row">
				<th scope="row"><label for="blogname">Parent URL</label></th>
				<td><input name="gpr_smartbar_parent_url" id="gpr_smartbar_parent_url" data-item="gpr_smartbar_parent_url" value="<?=get_option('gpr_smartbar_parent_url')?>" type="text" class="code gsa-settings" placeholder="https://" /></td>
			</tr>
            <tr class="child_sites_row">
            	<th scope="row"><label for="blogdescription">New Child Website</label></th>
            	<td>
            		<div style="display: inline-block;">
            			<label for="gpr_smartbar_new_website_name" style="font-weight: 500; display: block;">Website Name</label>
            			<input name="gpr_smartbar_new_website_name" type="text" id="gpr_smartbar_new_website_name" value="" placeholder="Website Name" class="regular-text">
            		</div>
            		<div style="display: inline-block;">
            			<label for="gpr_smartbar_new_website_url" style="font-weight: 500; display: block;">Website URL</label>
            			<input name="gpr_smartbar_new_website_url" type="text" id="gpr_smartbar_new_website_url" value="" placeholder="Website URL" class="regular-text">
            		</div>
            		<p class="description" id="tagline-description">Add a new child website.</p>
            		<p><button class="gpx_smartbar_new_child">Add</button></p>
        		</td>
            </tr>
            <tr class="child_sites_row">
            	<th>Child Websites</th>
            	<td id="gpr_smartbar_children">
            		<?php 
            		$children = get_option('gpr_smartbar_children');
            		$childrenArr = unserialize( base64_decode( $children ) );
            		foreach($childrenArr as $child)
            		{
                		echo '<div class="gpr_smartbar_child_row"><button class="remove_children" data-row="'.$child['name'].'">&times;</button>'.$child['name']." ".$child['url'].'</div>';
            		}
            		?>
            	</td>
            </tr>
		</tbody>
	</table>
</div>
<?php
}