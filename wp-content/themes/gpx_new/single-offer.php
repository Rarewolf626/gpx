<?php
/**
 * Theme: GPX, for displaying all single offer
 */
get_header();
while ( have_posts() ) : the_post();
    $meta_data = get_post_meta( get_the_ID());
    $images = '';
    if(isset($meta_data['gpx_extra_gallery']))
        $images = $meta_data['gpx_extra_gallery'];
    $subtitle = $meta_data['gpx_subtitle'][0];
    $promo_code = $meta_data['gpx_promo_code'][0];
    $term_conditions = $meta_data['gpx_term_condition'][0];
    $gpx_offer_button_text    = (isset($meta_data['gpx_offer_button_text'][0]) && !empty($meta_data['gpx_offer_button_text'][0])) ? $meta_data['gpx_offer_button_text'][0] : 'Search More Offers';
    $gpx_offer_button_url    = (isset($meta_data['gpx_offer_button_url'][0]) && !empty($meta_data['gpx_offer_button_url'][0])) ? $meta_data['gpx_offer_button_url'][0] : '/#offers';
    $gpx_offer_start_date  = (isset($meta_data['gpx_offer_start_date'][0]) && !empty($meta_data['gpx_offer_start_date'][0])) ? strtotime($meta_data['gpx_offer_start_date'][0].'00:00  America/Los_Angeles') : '';
    $gpx_offer_end_date    = (isset($meta_data['gpx_offer_end_date'][0]) && !empty($meta_data['gpx_offer_end_date'][0])) ? strtotime($meta_data['gpx_offer_end_date'][0].'23:59  America/Los_Angeles') : '';
    
    $startofday = strtotime("today 00:00 America/Los_Angeles");
    $endofday = strtotime("today 23:59 America/Los_Angeles");
    if(!empty($gpx_offer_start_date))
    {
        if($startofday < $gpx_offer_start_date)
        {
            continue;
        }
    }
    if(!empty($gpx_offer_end_date))
    {
        if($endofday > $gpx_offer_end_date)
        {
            continue;
        }
    }
    ?>
    <section class="w-banner">
        <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen">
            <li class="slider-item rsContent">
                <?php if ( !empty( $images ) ) {
                foreach ( $images as $image ) {
                    $src = wp_get_attachment_image_src($image, 'full');
                    ?>
                    <img class="rsImg" src="<?php echo $src[0]; ?>" alt="Offers" />
                <?php } } ?>
            </li>
        </ul>
        <div class="dgt-container w-box">
            <div class="offer_title">
                <h2 class="gtitle"><?php the_title(); ?></h2>
                <h3 class="w-options"><?php echo $subtitle; ?></h3>
            </div>
        </div>
    </section>
    <div class="dgt-container PageRes">
        <p><?php echo the_content(); ?> </p>
        <?php if( !empty( $promo_code ) ) { ?>
            <div class="phone_add">Offer Code: <span><b><?php echo $promo_code; ?></b></span></div>
        <?php } ?>
        <div class="btn_phom">
            <a href="<?=$gpx_offer_button_url?>" class="btn left"><?=$gpx_offer_button_text?></a>
        </div>
        <div class="clear"></div>
        <div class="term_text">
            <?php if( !empty( $term_conditions ) ) echo 'TERMS OF OFFER '.$term_conditions; ?>
        </div>
    </div>
<?php endwhile; ?>
<?php get_footer(); ?>