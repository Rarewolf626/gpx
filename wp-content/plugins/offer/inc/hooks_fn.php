<?php

add_action('admin_init', 'dgt_offer_register_metaboxes');

// register metaboxes
function dgt_offer_register_metaboxes() {
	add_meta_box('gpx-offer-information', 'Offer Information','gpx_offer_information_metabox_render', GPX_OFFER);
}

function gpx_offer_information_metabox_render($post) {
	$gpx_subtitle       = get_post_meta($post->ID, GPX_PREFIX.'_subtitle', true);
	$gpx_promo_code     = get_post_meta($post->ID, GPX_PREFIX.'_promo_code', true);
	$gpx_term_condition = get_post_meta($post->ID, GPX_PREFIX.'_term_condition', true);
	$gpx_show           = get_post_meta($post->ID, GPX_PREFIX.'_show', true);
	$pgx_extra_title    = get_post_meta($post->ID, GPX_PREFIX.'_extra_title', true);
	$pgx_extra_desc    = get_post_meta($post->ID, GPX_PREFIX.'_extra_desc', true);
	$gpx_extra_order    = get_post_meta($post->ID, GPX_PREFIX.'_extra_order', true);
	$gpx_box_button_text    = get_post_meta($post->ID, GPX_PREFIX.'_box_button_text', true);
	$gpx_box_button_url    = get_post_meta($post->ID, GPX_PREFIX.'_box_button_url', true);
	$gpx_offer_button_text    = get_post_meta($post->ID, GPX_PREFIX.'_offer_button_text', true);
	$gpx_offer_button_url    = get_post_meta($post->ID, GPX_PREFIX.'_offer_button_url', true);
	$gpx_offer_start_date    = get_post_meta($post->ID, GPX_PREFIX.'_offer_start_date', true);
	$gpx_offer_end_date    = get_post_meta($post->ID, GPX_PREFIX.'_offer_end_date', true);

	include GPX_BASE_PATH.'/views/metaboxes/metabox-information.php';
}


function add_script_metabox(){
	echo '<script>
				var $ = jQuery.noConflict();
				$(document).ready(function(){
					if (!$("#gpx_show").is(":checked")) {
						$("#gpx_extra_title").parent().parent().fadeOut("fast");
						$("#gpx_extra_title").val(null);
						$("#gpx_extra_order").parent().parent().fadeOut("fast");
						$("#gpx_extra_order").val(null);
					}

					$("#gpx_show").change(function(){
						if (this.checked) {
							$("#gpx_extra_title").parent().parent().fadeIn("fast");
							$("#gpx_extra_order").parent().parent().fadeIn("fast");
						}	else {
							$("#gpx_extra_title").parent().parent().fadeOut("fast");
							$("#gpx_extra_title").val(null);

							$("#gpx_extra_order").parent().parent().fadeOut("fast");
							$("#gpx_extra_order").val(null);
						}});
				});
			</script>';
}

add_action( 'rwmb_after_offer_additional', 'add_script_metabox' );
