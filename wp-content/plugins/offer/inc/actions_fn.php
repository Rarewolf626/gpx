<?php
add_action( 'admin_enqueue_scripts', 'safely_add_stylesheet_to_admin' );
function safely_add_stylesheet_to_admin() {
	wp_enqueue_style( 'dashboard-style', GPX_BASE_URI.'assets/css/style.css' );
	wp_enqueue_script( 'dashboard-offer', GPX_BASE_URI.'assets/js/script.js', array(), '1.0' );
}

function dgt_offer_metaboxes_save($post_id) {
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );

	if ( $is_autosave || $is_revision ) { return; }

	$gpx_subtitle       = isset($_POST[GPX_PREFIX.'_subtitle']) ? sanitize_text_field($_POST[GPX_PREFIX.'_subtitle']) : '';
	$gpx_promo_code     = isset($_POST[GPX_PREFIX.'_promo_code']) ? sanitize_text_field($_POST[GPX_PREFIX.'_promo_code']) : '';
	$gpx_term_condition = isset($_POST[GPX_PREFIX.'_term_condition']) ? sanitize_text_field($_POST[GPX_PREFIX.'_term_condition']) : '';
	$gpx_show           = isset($_POST[GPX_PREFIX.'_show']) ? (int)$_POST[GPX_PREFIX.'_show'] : 0;
	$pgx_extra_title    = isset($_POST[GPX_PREFIX.'_extra_title']) ? sanitize_text_field($_POST[GPX_PREFIX.'_extra_title']) : '';
	$pgx_extra_desc    = isset($_POST[GPX_PREFIX.'_extra_title']) ? sanitize_text_field($_POST[GPX_PREFIX.'_extra_desc']) : '';
	$gpx_extra_order    = isset($_POST[GPX_PREFIX.'_extra_order']) ? sanitize_text_field($_POST[GPX_PREFIX.'_extra_order']) : '';
	$gpx_box_button_text    = isset($_POST[GPX_PREFIX.'_box_button_text']) ? sanitize_text_field($_POST[GPX_PREFIX.'_box_button_text']) : '';
	$gpx_box_button_url    = isset($_POST[GPX_PREFIX.'_box_button_url']) ? sanitize_text_field($_POST[GPX_PREFIX.'_box_button_url']) : '';
	$gpx_offer_button_text    = isset($_POST[GPX_PREFIX.'_offer_button_text']) ? sanitize_text_field($_POST[GPX_PREFIX.'_offer_button_text']) : '';
	$gpx_offer_button_url    = isset($_POST[GPX_PREFIX.'_offer_button_url']) ? sanitize_text_field($_POST[GPX_PREFIX.'_offer_button_url']) : '';
	$gpx_offer_start_date    = isset($_POST[GPX_PREFIX.'_offer_start_date']) ? date("Y-m-d", strtotime(sanitize_text_field($_POST[GPX_PREFIX.'_offer_start_date']))) : '';
	$gpx_offer_end_date    = isset($_POST[GPX_PREFIX.'_offer_end_date']) ? date("Y-m-d", strtotime(sanitize_text_field($_POST[GPX_PREFIX.'_offer_end_date']))) : '';

	update_post_meta($post_id, GPX_PREFIX.'_subtitle', $gpx_subtitle);
	update_post_meta($post_id, GPX_PREFIX.'_promo_code', $gpx_promo_code);
	update_post_meta($post_id, GPX_PREFIX.'_term_condition', $gpx_term_condition);
	update_post_meta($post_id, GPX_PREFIX.'_show', $gpx_show);
	update_post_meta($post_id, GPX_PREFIX.'_extra_title', $pgx_extra_title);
	update_post_meta($post_id, GPX_PREFIX.'_extra_desc', $pgx_extra_desc);
	update_post_meta($post_id, GPX_PREFIX.'_extra_order', $gpx_extra_order);
	update_post_meta($post_id, GPX_PREFIX.'_box_button_text', $gpx_box_button_text);
	update_post_meta($post_id, GPX_PREFIX.'_box_button_url', $gpx_box_button_url);
	update_post_meta($post_id, GPX_PREFIX.'_offer_button_text', $gpx_offer_button_text);
	update_post_meta($post_id, GPX_PREFIX.'_offer_button_url', $gpx_offer_button_url);
	update_post_meta($post_id, GPX_PREFIX.'_offer_start_date', $gpx_offer_start_date);
	update_post_meta($post_id, GPX_PREFIX.'_offer_end_date', $gpx_offer_end_date);
}

add_action('save_post', 'dgt_offer_metaboxes_save');