<?php
/**
 * @var ?stdClass $resort
 * @var ?string $taURL
 * @var ?string $starsclass
 * @var ?int $reviews
 */

$resort = $resort ?? $args['resort'] ?? null;
$taUR = $taUR ?? $args['taUR'] ?? '';
$starsclass = $starsclass ?? $args['starsclass'] ?? '';
$reviews = $reviews ?? $args['reviews'] ?? 0;
if ( ! $resort ) {
    return;
}
?>

<div class="info-detail">
    <ul class="details">
        <li>
            <p><strong>Address:</strong></p>
            <p>
              <?php if(!empty($resort->maplink)): ?>
                <a href="<?=$resort->maplink?>" target="_blank">
                    <?php get_template_part('template-parts/resort-address', null, compact('resort')) ?>
                </a>
              <?php else: ?>
                  <?php get_template_part('template-parts/resort-address', null, compact('resort')) ?>
              <?php endif; ?>
            </p>
        </li>
        <?php if($resort->Website):?>
        <li>
            <p><strong>Website:</strong></p>
            <p><a href="<?= esc_attr($resort->url) ?>" target="_blank"><?= esc_html($resort->link)?></a></p>
        </li>
        <?php endif; ?>
        <li>
            <p><strong>Phone:</strong></p>
            <p><a href="tel:<?= esc_attr($resort->Phone)?>"><?= esc_html($resort->Phone)?></a></p>
        </li>
        <li>
            <p><strong>Fax:</strong></p>
            <p><?=esc_html($resort->Fax)?></p>
        </li>
        <li>
            <p><strong>Closest Airport:</strong></p>
            <p><?=esc_html($resort->Airport)?></p>
        </li>
        <li>
            <p><strong>Check In: <?=esc_html($resort->CheckInDays)?></strong></p>
            <p>Earliest: <?=esc_html($resort->CheckInEarliest)?></p>
            <p>Latest: <?=esc_html($resort->CheckInLatest)?></p>
        </li>
        <li>
            <p><strong>Check Out:</strong></p>
            <p>Earliest: <?=esc_html($resort->CheckOutEarliest)?></p>
            <p>Latest: <?=esc_html($resort->CheckOutLatest)?></p>
        </li>
    </ul>
<?php if(isset($taURL)): ?>
    <div class="ta-badge">
    	<p><a href="<?=esc_attr($taURL)?>" class="ta-link" target="_blank"><strong><?=esc_html($resort->ResortName)?></strong></a></p>
    	<p>TripAdvisor Traveler Rating</p>
    	<p><a href="<?=esc_attr($taURL)?>" target="_blank"><img class="ta-star" src="/wp-content/themes/gpx_new/images/ta-stars<?=esc_attr($starsclass)?>.png" alt="<?=esc_attr($starsclass)?>"><br><span style="text-decoration: underline;"><?=esc_html($reviews)?> Reviews</span></a></p>
    	<p><a href="<?=esc_attr($taURL)?>" target="_blank"><img src="/wp-content/themes/gpx_new/images/ta_logo.png" alt="TripAdvisor"></a></p>
    </div>
<?php endif; ?>
</div>
