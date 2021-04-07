<?php
/**
 * Template Name: Result Page
 * Theme: GPX
 */

get_header();
while ( have_posts() ) : the_post();  

the_content();
endwhile;?>
<div id="sc-result"></div>

<?php get_footer(); ?>
<script type="text/javascript">
    $.ajax({
        url: "<?php echo site_url() ?>/wp-admin/admin-ajax.php?action=gpx_ajax_result",
        type: "POST",
        data: {'action':'gpx_ajax_result','location':'<?php echo $_REQUEST['location'] ?>','select_month':'<?php echo $_REQUEST['select_month'] ?>','select_year':'<?php echo $_REQUEST['select_year']; ?>'},
            success:function(res){

                $('#sc-result').html(res);
        }
        
    })
</script>