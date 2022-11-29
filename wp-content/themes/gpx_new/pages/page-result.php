<?php
/**
 * Template Name: Result Page
 * Theme: GPX
 */

get_header();
while ( have_posts() ) : the_post();

the_content();
endwhile;
?>

 <?php get_footer(); ?>


