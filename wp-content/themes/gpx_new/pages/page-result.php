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
        url: "<?php echo site_url() ?>/wp-admin/admin-ajax.php?action=getresult",
        type: "POST",
        data:{'action': 'getresult'},
            success:function(res){
                console.log(res);
        }
        
    })
</script>