<?php

/* customs columns listing post type */
add_filter(sprintf('manage_%s_posts_columns', GPX_OFFER), sprintf('dgt_idx_manage_%s_columns', GPX_OFFER) );
add_filter(sprintf('manage_%s_posts_custom_column', GPX_OFFER), sprintf('dgt_idx_manage_post_%s_columns', GPX_OFFER), 10, 2);

function dgt_idx_manage_offer_columns($posts_columns = array()) {
  $default = array('cb', 'image', 'title', 'subtitle', 'code', 'show', 'order', 'date');
  $new_posts_columns = array();
  foreach ($default as $k) {
    switch($k){
      case 'image':           $new_posts_columns[$k] = 'Image'; break;
      case 'subtitle':        $new_posts_columns[$k] = 'Sub Title'; break;
      case 'code':            $new_posts_columns[$k] = 'Promo Code'; break;
      case 'show':            $new_posts_columns[$k] = 'Show Home Page'; break;
      case 'order':           $new_posts_columns[$k] = 'Order Home Page'; break;
      default:                $new_posts_columns[$k] = isset($posts_columns[$k]) ? $posts_columns[$k] : ''; break;
    }
  }
  return $new_posts_columns;
}

function dgt_idx_manage_post_offer_columns($column_name, $ID) {
  switch ($column_name) {
    case 'image':
      $attr = get_post_meta($ID, '_thumbnail_id');
      $att_id = $attr[0];
      if ($att_id > 0) {
        $src = wp_get_attachment_image_src($att_id, 'thumbnail');
        if (!empty($src)) {
          $image = $src[0];
          echo sprintf('<img src="%s" width="60" height="60" />', $image);
        }
      }
      break;
    case 'subtitle':
      echo get_post_meta($ID, GPX_PREFIX.'_subtitle', true);
      break;
    case 'code':
      echo get_post_meta($ID, GPX_PREFIX.'_promo_code', true);
      break;
    case 'show':
      $checkbox = (int)get_post_meta($ID, GPX_PREFIX.'_show', true);
      echo ($checkbox)? 'Yes' : 'No';
      break;
    case 'order':
      echo get_post_meta($ID, GPX_PREFIX.'_extra_order', true);
      break;
  }
}
