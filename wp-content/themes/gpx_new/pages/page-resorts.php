<?php
/**
 * Template Name: Resorts Page
 * Theme: GPX
 */

get_header();
while ( have_posts() ) : the_post();
?>
<section class="w-banner no-bottom-margin new-design-height">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen">
        <li class="slider-item rsContent"><img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/resorts.jpg" alt="" /></li>
    </ul>
    <div class="dgt-container w-box">
        <div class="gsub-title"><h1>Explore the GPX Resort Directory</h1></div>
        <?php //get_template_part( 'template-parts/form-resorts' ); ?>
    </div>
</section>
<?php include(locate_template( 'template-parts/universal-search-widget.php' )); ?>
<section class="w-featured bg-gray-light">
<?php 
/*
?> 
    <span class="tag">
        <img src="<?php echo get_template_directory_uri(); ?>/images/tag03.png" alt="Featured Resorts">
<?php
*/
?>
    <?php echo do_shortcode('[gpx_display_featured_resorts location="resorts" get="9"]'); ?>
</section>
<?php endwhile;
get_footer(); ?>