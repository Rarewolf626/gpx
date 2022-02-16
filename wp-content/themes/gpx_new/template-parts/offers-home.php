<?php
$posts_args = array(  'post_type' => 'offer',
    'posts_per_page' => -1,
    'meta_key'  => 'gpx_extra_order',
    'orderby'   => 'meta_value_num',
    'order'     => 'ASC',
    'meta_query' => array(
        'key'=>'gpx_show',
        'value'=>'1',
    ),
);
$posts_query = new WP_Query($posts_args);
?>
<ul class="w-list carousel-slider">
	<?php
	if ($posts_query->have_posts()){
	    while ($posts_query->have_posts()) {
	        $posts_query->the_post();
	        $meta_data = get_post_meta( get_the_ID());
	        $title_for_home = get_post_meta(get_the_ID(), GPX_PREFIX.'_extra_title', true);
	        $desc_for_home = get_post_meta(get_the_ID(), GPX_PREFIX.'_extra_desc', true);
	        $gpx_box_button_text    = (isset($meta_data['gpx_box_button_text'][0]) && !empty($meta_data['gpx_box_button_text'][0])) ? $meta_data['gpx_box_button_text'][0] : 'Explore Offer';
	        $gpx_box_button_url    = (isset($meta_data['gpx_box_button_url'][0]) && !empty($meta_data['gpx_box_button_url'][0])) ? $meta_data['gpx_box_button_url'][0] : get_the_permalink();
	        $gpx_offer_start_date  = (isset($meta_data['gpx_offer_start_date'][0]) && !empty($meta_data['gpx_offer_start_date'][0])) ? strtotime($meta_data['gpx_offer_start_date'][0].'00:00  America/Los_Angeles') : '';
	        $gpx_offer_end_date    = (isset($meta_data['gpx_offer_end_date'][0]) && !empty($meta_data['gpx_offer_end_date'][0])) ? strtotime($meta_data['gpx_offer_end_date'][0].'23:59  America/Los_Angeles') : '';
	        
	        $startofday = strtotime("today 00:00 America/Los_Angeles");
	        $endofday = strtotime("today 23:59 America/Los_Angeles");
	        if(!empty($gpx_offer_start_date))
	        {
	            if($startofday < $gpx_offer_start_date)
	            {
	                continue;
	            }
	        }
	        if(!empty($gpx_offer_end_date))
	        {
	            if($endofday > $gpx_offer_end_date)
	            {
	                continue;
	            }
	        }
			?>
			<li class="w-item">
				<div class="cnt">
					<a href="<?=$gpx_box_button_url; ?>">
						<figure>
							<?php
							if ( has_post_thumbnail()) {
								$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full');
								?>
								<img src="<?php echo $large_image_url[0] ?>" alt="<?php the_title(); ?>" />
							<?php } ?>
						</figure>
						<h3><?php echo (!empty($title_for_home))? $title_for_home : get_the_title(); ?></h3>
						<p><?php echo $desc_for_home; ?></p>
						<div class="dgt-btn"> <?=$gpx_box_button_text?> </div>
					</a>
				</div>
			</li>
		<?php } } ?>
</ul>