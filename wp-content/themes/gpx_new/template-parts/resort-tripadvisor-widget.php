<?php
/**
 * @var ?string $resort
 * @var ?string $taURL
 * @var ?float $stars
 * @var ?int $reviews
 * @var array{resort: ?string, taURL: ?string, stars: ?float, reviews: ?int} $args
 */
$resort = $resort ?? $args['resort'] ?? null;
$taURL = $taURL ?? $args['taURL'] ?? '';
$stars = $stars ?? $args['stars'] ?? 0;
$reviews = $reviews ?? $args['reviews'] ?? 0;
$starsclass = str_replace(".", "_", $stars);

if(!$taURL) return;

?>
<div class="ta-badge">
    <?php if($resort):?>
        <p><a href="<?=esc_url($taURL)?>" class="ta-link" target="_blank"><strong><?=esc_html($resort)?></strong></a></p>
    <?php endif; ?>
    <p>TripAdvisor Traveler Rating</p>
    <p>
        <a href="<?=esc_url($taURL)?>" target="_blank">
            <img class="ta-star" src="/wp-content/themes/gpx_new/images/ta-stars<?=esc_attr($starsclass)?>.png" alt="<?=esc_attr($stars)?>">
            <br>
            <span style="text-decoration: underline;"><?=esc_html($reviews)?> Reviews</span>
        </a>
    </p>
    <p><a href="<?=esc_url($taURL)?>" target="_blank"><img src="/wp-content/themes/gpx_new/images/ta_logo.png" alt="TripAdvisor"></a></p>
</div>
