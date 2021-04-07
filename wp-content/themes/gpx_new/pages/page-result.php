<?php
/**
 * Template Name: Result Page
 * Theme: GPX
 */

get_header();
while ( have_posts() ) : the_post();  

the_content();
print_r($_REQUEST);

endwhile;
get_footer(); ?>
<script type="text/javascript">
    $.ajax({
        url: "<?php echo site_url() ?>/wp-admin/admin-ajax.php?action=gpx_ajax_result",
        type: "POST",
        data: {'action':'gpx_ajax_result','location:califronia'},
            success:function(res){

                // $('#sc-result').html(res);
        }
        
    })
</script>