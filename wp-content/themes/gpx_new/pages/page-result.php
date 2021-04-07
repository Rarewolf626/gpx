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
print_r($_REQUEST);

echo json_encode($_REQUEST);


endwhile;
get_footer(); ?>
<script type="text/javascript">
    $.ajax({
        url: "<?php echo site_url() ?>/wp-admin/admin-ajax.php?action=getresult",
        type: "POST",
        data:{'action': 'getresult'},
            success:function(res){

                // $('#sc-result').html(res);
        }
        
    })
</script>