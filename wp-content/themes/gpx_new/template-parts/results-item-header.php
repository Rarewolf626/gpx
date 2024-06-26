<?php
/**
 * @var stdClass $prop
 * @var array $resort
 * @var ?bool $highlight
 */

$highlight = $highlight ?? false;
$finalPrice = $prop->Price;
if ($prop->discount > 0) {
    $finalPrice = $prop->WeekPrice;
    if ($prop->specialPrice - $prop->WeekPrice != 0) {
        $finalPrice = $prop->specialPrice;
    }
}
?>
<div class="result-header <?= esc_attr($highlight ? 'result-header--highlight' : '')?>">
    <div class="result-header-details">
        <div class="result-header-pricing">
            <div
                class="result-header-price <?= esc_attr($prop->slash ? 'result-header-price--strike' : '') ?>">
                <?= gpx_currency($prop->slash ? $prop->WeekPrice : $finalPrice, true) ?>
            </div>
            <?php if ($prop->slash): ?>
                <div class="result-header-price result-header-price--now">
                    <span>Now</span>
                    <strong><?= gpx_currency($finalPrice, true) ?></strong>
                </div>
            <?php endif; ?>
            <?php if ($prop->specialdesc && $prop->specialicon): ?>
                <?php $dialogID = bin2hex(random_bytes(8)); ?>
                <div class="result-header-special">
                    <a href="#dialog-special-<?php esc_attr_e($dialogID) ?>"
                       class="special-link" aria-label="promo info"><i
                            class="fa <?= esc_attr($prop->specialicon) ?>"></i></a>
                    <dialog id="dialog-special-<?php esc_attr_e($dialogID) ?>"
                            class="modal-special">
                        <div class="w-modal">
                            <p><?= nl2p(esc_html($prop->specialdesc)) ?></p>
                        </div>
                    </dialog>
                </div>
            <?php endif; ?>

        </div>
        <?php if ($resort['ResortFeeSettings']['enabled'] ?? false): ?>
            <div class="result-header-fees">
                <?= gpx_currency($finalPrice + $resort['ResortFeeSettings']['total'], true) ?>
                including resort fees
            </div>
        <?php endif; ?>
    </div>
    <div class="result-header-status">
        <?php if ($prop->WeekType === 'Exchange Week'): ?>
            <div class="status-icon status-icon--ExchangeWeek"></div>
        <?php else: ?>
            <div class="status-icon status-icon--RentalWeek"></div>
        <?php endif; ?>
    </div>
</div>
