<?php
/**
 * Theme: GPX
 */
get_header();
while ( have_posts() ) : the_post(); ?>
<section class="wbanner">
	<div id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen">
		<div class="slider-item rsContent"><img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/california.jpg" alt="" /></div>
	</div>
</section>
<section class="wcontent">
	<div class="dgt-container">
		<div class="single_content">
			<?php the_content() ?>
		</div>
		<div class="single_sidebar">
			<?php get_sidebar(); ?>
		</div>
	</div>
</section>
<?php endwhile;
get_footer(); ?>