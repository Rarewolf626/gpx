<?php
/**
 * Template Name: Result Page
 * Theme: GPX
 */

get_header();
while ( have_posts() ) : the_post();

endwhile;
?>
<?php echo do_shortcode('[websitetour id="18535"]'); ?>
<section class="w-banner">
	<!-- Banner home -->
	<?php get_template_part( 'template-parts/banner-home' ); ?>
	<!-- Search home -->
	<?php get_template_part( 'template-parts/search-home' ); ?>
</section>
<?php 
/*
?>
<section class="w-travel">
	<!-- travel home -->
	<?php get_template_part( 'template-parts/travel-home' ); ?>
</section>
<?php
*/
?>
<section class="w-offers" id="offers">
	<?php 
	/*
	?>
	<span class="tag"><img src="<?php echo get_template_directory_uri(); ?>/images/tag01.png" alt="This Week's Offers"></span>
	<?php
	*/
	?>
	<!-- Offers home -->
	<div class="gtitle">
		<h2>Offers & Spotlights</h2>
	</div>
	<?php get_template_part( 'template-parts/offers-home' ); ?>
</section>
<section class="w-featured bg-gray">
	<span class="tag"><img src="<?php echo get_template_directory_uri(); ?>/images/tag02.png" alt="Featured Destinations"></span>
	<!-- featured destination home -->
	<?php get_template_part( 'template-parts/featured-destinations-home' ); ?>
		<?php //echo do_shortcode('[gpx_display_featured_resorts]'); ?>
</section>
<?php 
get_footer(); ?>
