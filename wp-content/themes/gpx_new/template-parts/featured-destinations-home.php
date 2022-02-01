<?php
$posts_args = array(  'post_type' => 'destinations',
    'meta_key'  => 'gpx_extra_order',
    'orderby'   => 'menu_order',
    'order'     => 'ASC',
);
$posts_query = new WP_Query($posts_args);
?>
<ul class="w-list w-list-items carousel-slider">
<?php
	if ($posts_query->have_posts()){
		while ($posts_query->have_posts()) {
			$posts_query->the_post();
			$meta_data = get_post_meta( get_the_ID());
			?>
			<li class="w-item">
            	<div class="cnt">
            	<?php 
            	$link = '/result/?destination='.$meta_data['gpx-destination-link'][0];
            	if(isset($meta_data['gpx-destination-blog-link'][0]))
            	{
            	    
            	    $link = get_the_permalink($meta_data['gpx-destination-blog-link'][0]);
            	}
            	?>
            		<a href="<?=$link?>">
            			<figure><?=the_post_thumbnail('full')?></figure>
            			<h3><?=the_title()?></h3>
            			<?=the_content()?>
            			<div class="dgt-btn"><?=$meta_data['gpx-destination-link-text'][0]?> </div>
            		</a>
            	</div>
            </li>
		<?php } } ?>
</ul>