<?php
get_header();
?> 
<!-- Indicaciones - ELiminar esta sección-->
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/pagex.css">
<?php
    // Start the loop.
    while ( have_posts() ) : the_post(); 
        $images = rwmb_meta( 'dgt_extra_image'); 
?>
    <section class="w-banner">
        <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen">
            <li class="slider-item rsContent">
                <?php if ( !empty( $images ) ) {
                    foreach ( $images as $image ) { ?>
                <img class="rsImg" src="<?php echo $image['full_url']; ?>" alt="Offers" />
                <?php } } ?>
            </li>
        </ul>
        <div class="dgt-container w-box">
            <div class="offer_title">
                <h2 class="gtitle"><?php the_title(); ?></h2>
                <h3 class="w-options"><?php echo rwmb_meta( 'dgt_extra_subtitle', $args=array(), get_the_id() ); ?></h3>
            </div>
        </div>
    </section>
    <div class="dgt-container PageRes">
        <p><?php echo the_content(); ?> </p>
        <?php if( !empty( rwmb_meta('dgt_extra_code') ) ) { ?>
            <div class="phone_add">Offer Code: <span><b><?php echo rwmb_meta('dgt_extra_code'); ?></b></span></div>
        <?php } ?>
        <div class="btn_phom">
            <a href="#" class="btn left">Search More Offers</a>
            <a href="#" class="btn right">Search Availability</a>
        </div>
        <div class="clear"></div>
        <div class="term_text">
            <?php if( !empty( rwmb_meta('dgt_extra_code') ) ) echo 'TERMS OF OFFER'.rwmb_meta('dgt_extra_terms_conditions'); ?>
        </div>
    </div>
<?php endwhile; ?>
<!-- Fin de indicaciones-->
<?php get_footer(); ?>
<script>
  $('body').addClass('active-session');
</script>