<?php
/**
 * Template Name: Resorts Result Page
 * Theme: GPX
 */

get_header();

while ( have_posts() ) : the_post();  

the_content();

endwhile; // end of the loop.
get_footer(); ?>