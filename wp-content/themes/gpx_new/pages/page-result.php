<?php
/**
 * Template Name: Result Page
 * Theme: GPX
 */

get_header();
// while ( have_posts() ) : the_post();  

// the_content();
// endwhile;
echo '<pre>';
print_r($_REQUEST);

?>
 <?php get_footer(); ?>
 <script type="text/javascript">
    $.ajax({
            url: "<?php echo site_url() ?>/wp-admin/admin-ajax.php?action=result_page",
            type: "POST",
            data:{'action': 'result_page','location':'<?php echo $_REQUEST['location']; ?>','select_month':'<?php echo $_REQUEST['select_month']; ?>','select_year':'<?php echo $_REQUEST['select_month']; ?>'},
            success:function(res){
                console.log(res);
            }
        })
 </script>
