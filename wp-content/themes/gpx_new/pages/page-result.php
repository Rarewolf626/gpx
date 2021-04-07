<?php
/**
 * Template Name: Result Page
 * Theme: GPX
 */

get_header();
while ( have_posts() ) : the_post();  

the_content();?>

<div id="sc-result"></div>
<?php

$_REQUEST['action'] = 'getresult';

endwhile;
get_footer(); ?>
<script type="text/javascript">
    $.ajax({
        url: "<?php echo site_url() ?>/wp-admin/admin-ajax.php?action=getresult",
        type: "POST",
        data: '<?php echo json_encode($_REQUEST); ?>',
            success:function(res){

                // $('#sc-result').html(res);
        }
        
    })
</script>