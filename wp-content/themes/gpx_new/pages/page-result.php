<?php
/**
 * Template Name: Result Page
 * Theme: GPX
 */

get_header();
// while ( have_posts() ) : the_post();  

// the_content();
// endwhile;
?>
<div id="main-result"></div>
 <?php get_footer(); ?>
 <script type="text/javascript">
    $.post('/wp-admin/admin-ajax.php?action=result_page',{location:'<?php echo $_REQUEST['location']; ?>',select_month:'<?php echo $_REQUEST['select_month']; ?>',select_year:'<?php echo $_REQUEST['select_year']; ?>'}, function(data){
            $('#main-result').html(data);
    });		
    
 </script>
