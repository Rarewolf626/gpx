<?php
/**
 * @var ?stdClass $resort
 * @var ?string $taURL
 * @var ?string $starsclass
 * @var ?int $reviews
 */
$resort = $resort ?? $args['resort'] ?? null;

if (!$resort) {
    return;
}

?>

<div class="info-detail">
    <ul class="details">
        <li>
            <p><strong>Address:</strong></p>
            <p>
                <?php if (!empty($resort->maplink)): ?>
                    <a href="<?= $resort->maplink ?>" target="_blank">
                        <?php get_template_part('template-parts/resort-address', null, compact('resort')) ?>
                    </a>
                <?php else: ?>
                    <?php get_template_part('template-parts/resort-address', null, compact('resort')) ?>
                <?php endif; ?>
            </p>
        </li>
        <?php if ($resort->Website): ?>
            <li>
                <p><strong>Website:</strong></p>
                <p><a href="<?= esc_attr($resort->url) ?>" target="_blank"><?= esc_html($resort->link) ?></a></p>
            </li>
        <?php endif; ?>
        <?php if ($resort->Phone): ?>
            <li>
                <p><strong>Phone:</strong></p>
                <p><a href="tel:<?= esc_attr($resort->Phone) ?>"><?= esc_html($resort->Phone) ?></a></p>
            </li>
        <?php endif; ?>
        <?php if ($resort->Fax): ?>
            <li>
                <p><strong>Fax:</strong></p>
                <p><?= esc_html($resort->Fax) ?></p>
            </li>
        <?php endif; ?>
        <?php if ($resort->Airport): ?>
            <li>
                <p><strong>Closest Airport:</strong></p>
                <p><?= esc_html($resort->Airport) ?></p>
            </li>
        <?php endif; ?>
        <?php if ($resort->CheckInEarliest): ?>
            <li>
                <p><strong>Check In: </strong></p>
                <p>Earliest: <?= esc_html($resort->CheckInEarliest) ?></p>
            </li>
        <?php endif; ?>
        <?php if ($resort->CheckOutLatest): ?>
            <li>
                <p><strong>Check Out:</strong></p>
                <p>Latest: <?= esc_html($resort->CheckOutLatest) ?></p>
            </li>
        <?php endif; ?>
    </ul>
    <?php if ($resort->taID && $resort->taID != 1): ?>
        <?php echo do_shortcode('[tripadvisor-widget id="' . esc_attr($resort->taID) . '" resort="' . esc_attr($resort->ResortName) . '"]'); ?>
    <?php endif; ?>
</div>
