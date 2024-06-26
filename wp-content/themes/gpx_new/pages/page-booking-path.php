<?php
/**
 * Template Name: Booking Path Page
 * Theme: GPX
 */

get_header();

while ( have_posts() ) : the_post();

the_content();

endwhile;

get_footer(); ?>
