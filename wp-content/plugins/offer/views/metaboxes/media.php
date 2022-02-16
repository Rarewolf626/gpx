<?php

add_action('admin_enqueue_scripts', function () {
	$screen = get_current_screen();
	$post_types = array('offer');

	foreach($post_types as $post_type) {
		if ($screen->base == 'post' && $screen->post_type == $post_type) {
			wp_enqueue_script('jquery');
			wp_enqueue_script("jquery-ui-sortable");
			wp_enqueue_script("jquery-ui-draggable");
			wp_enqueue_script("jquery-ui-droppable");
//
//			wp_enqueue_style('dgt-media-upload-css', plugin_dir_url(__FILE__) . 'assets/css/style.css');
//			wp_enqueue_script('dgt-media-upload-js', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'));

			wp_localize_script('dgt-media-upload-js', 'dgt_media_upload_js', array (
				'ajaxUrl' => admin_url( 'admin-ajax.php' )
			));
		}
	}
});

// register metabox
add_action('admin_init', function () {
	$post_types = array('offer');
	$has_mixed_images = false;

	foreach($post_types as $post_type):
		add_meta_box('dgt-media-upload', 'Gallery, please upload an image 1600 x 366 pixels', function ($post) {
			$gpx_image_gallery_items = get_post_meta($post->ID, 'gpx_extra_gallery', false);
			?>
			<input id="upload_logo_button" type="button" value="Select Files" class="button-secondary" />
			<div class="dgt-images-gallery-container" id="dgt-images-gallery-container-droppable">
				<ul id="sortable">
					<?php foreach ($gpx_image_gallery_items as $image_gallery_item):
						if (intval($image_gallery_item) <= 0) {
							$main_thumb = urldecode($image_gallery_item);
							if ($has_mixed_images == false) { $has_mixed_images = true; }
						} else {
							list($main_thumb) = wp_get_attachment_image_src(intval($image_gallery_item), 'thumbnail');
						}
						?>
						<li class="ui-state-default">
							<?php if ($has_mixed_images == true): ?>
							<div class="dgt-image-gallery-item dgt-image-gallery-item-from-mls" style="background-image:url(<?php echo $main_thumb; ?>);">
								<?php else: ?>
								<div class="dgt-image-gallery-item">
									<?php endif; ?>
									<div class="dgt-image-gallery-item-close">&times;</div>
									<?php if ($has_mixed_images == false): ?>
										<img src="<?php echo $main_thumb; ?>">
									<?php endif; ?>
									<input type="hidden" name="gpx_image_gallery[]" value="<?php echo $image_gallery_item; ?>">
								</div>
						</li>
					<?php endforeach ?>
				</ul>
			</div>
			<?php
		}, $post_type, 'normal', 'high');

	endforeach;
});

add_action('save_post', function ($post_id) {
	// Autosave, do nothing
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	// AJAX? Not used here
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		return;
	// Check user permissions
	if ( ! current_user_can( 'edit_post', $post_id ) )
		return;
	// Return if it's a post revision
	if ( false !== wp_is_post_revision( $post_id ) )
		return;

	// save metaboxes
	global $wpdb;

	$wpdb->query('DELETE FROM ' . $wpdb->prefix . 'postmeta WHERE post_id = "' . $post_id . '" AND meta_key = "gpx_extra_gallery"');

//	var_dump($_POST); die;
	if (isset($_POST['gpx_image_gallery']) && is_array($_POST['gpx_image_gallery']) && !empty($_POST['gpx_image_gallery'])) {
		foreach($_POST['gpx_image_gallery'] as $image_gallery_item) {
			add_post_meta($post_id, 'gpx_extra_gallery', $image_gallery_item, false);
		}
	}
});