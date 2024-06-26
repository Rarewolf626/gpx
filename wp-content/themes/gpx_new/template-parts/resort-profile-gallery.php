<?php
/** @var ?stdClass $resort */
$resort = $resort ?? $args['resort'] ?? null;
if ( ! $resort ) {
    return;
}
$images = $images ?? $args['images'] ?? $resort->images ?? [];
?>

<div class="gallery-detail">
    <div id="gallery_resort" class="royalSlider rsDefault rsResort">
        <?php
        foreach($images as $img)
        {
            if(!empty($img))
            {
                $httpsimg = '';
                if(isset($img['id']))
                {
                    $attachImg = wp_get_attachment_image_src($img['id'], 'large');
                    $httpsimg = $attachImg[0] ?? '';
                }
                if(empty($httpsimg))
                {
                    $httpsimg = str_replace("http://", "https://", $img['src']);
                }
                ?>
                <a class="rsImg" data-rsw="594" data-rsh="395"  data-rsBigImg="<?=$httpsimg; ?>" href="<?=$httpsimg; ?>" data-rsImg="<?=$httpsimg; ?>"  data-rsVideo="<?=$img['imageVideo']?>">
                    <img width="120" height="90" class="rsTmb" src="<?=$httpsimg; ?>" alt="<?=$img['imageAlt']?>" title="<?=$img['imageTitle']?>"  data-rsImg="<?=$httpsimg; ?>"  data-rsVideo="<?=$img['imageVideo']?>" />
                </a>
                <?php
            }
        }
        ?>
    </div>
    <?php
    $shared_gallery = get_posts( array(
        'post_type' => 'owner-shared-media',
        'orderby'    => 'menu_order',
        'sort_order' => 'asc',
        'post_per_page' => '-1',
        'tax_query' => array(
            array(
                'taxonomy' => 'gpx_shared_media_resort',
                'field' => 'name',
                'terms' => $resort->ResortName,
            )
        )
    ) );
    $fullImages = array();
    $thumbImages = array();

    foreach ( $shared_gallery as $gallery )
    {
        $fullImages[] = rwmb_meta( 'gpx_shared_images', array('size'=>'large'), $gallery->ID );
        $thumbImages[] = rwmb_meta( 'gpx_shared_images', array('size'=>'thumbnail'), $gallery->ID );
    }
    if(!empty($fullImages))
    {
        ?>
        <div class="owner-shared-gallery-wrapper">
            <h3>Owner Shared Media</h3>

            <ul id="owner-shared-main-gallery" class="cg-gallery-main">
                <?php
                foreach($fullImages as $galleries)
                {
                    foreach($galleries as $image)
                    {
                        ?>
                        <li><img src="<?=$image['url']?>" alt="<?=$image['alt']?>" title="<?=$image['title']?>" /></li>
                        <?php
                    }
                }
                ?>
            </ul>
        </div>
        <?php
    }
    ?>
</div>
<dialog id="gallery" data-close-on-outside-click="false">
    <div class="gallery-image"></div>
</dialog>
