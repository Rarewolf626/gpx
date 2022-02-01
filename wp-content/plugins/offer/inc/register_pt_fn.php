<?php

if (!function_exists('gpx_offer_post_type_fn')) {

	function gpx_offer_post_type_fn() {

		$labels = array(
			'name' => _x('Offers', 'post type general name'),
			'singular_name' => _x('Offer','post type singular name'),
			'add_new' => _x('Add New', 'Offer item'),
			'add_new_item' => 'Add New Offer',
			'edit_item' => 'Edit Offer',
			'new_item' => 'New Offer',
			'view_item' => 'View Offer',
			'search_items' => 'Search Offer',
			'not_found' => 'Nothing found',
			'not_found_in_trash' => 'Nothing found in Trash',
			'parent_item_colon' => ''
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'query_var' => true,
			'capability_type' => 'page',
			'hierarchical' => true,
			'menu_position' => null,
			'menu_icon' => 'dashicons-products',
			'supports' => array('title', 'editor', 'thumbnail'),
			'rewrite' => array ( 'slug' => 'offer' , 'with_front' => false )
		);

		register_post_type(GPX_OFFER, $args);
		flush_rewrite_rules();
	}
}

add_action('init', 'gpx_offer_post_type_fn');