 <?php
/**
 * Template Name: Full Screen Page
 * Theme: lite
 */
 get_header();
 ?>
<section class="full-content">
    <?php while ( have_posts() ) : the_post(); 
            get_template_part( 'template-parts/content', 'gpx' );
         endwhile; // end of the loop. ?>	
</section>
<?php get_footer(); ?>