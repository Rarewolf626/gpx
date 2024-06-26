<?php
/**
 * @var ?int $cid
 * @var ?int $book
 * @var ?string $returnLink
 * @var string $errorMessage
 */

?>

<?php gpx_theme_template_part( 'checkout-progress', [ 'step' => 'book' ] ) ?>
<?php gpx_theme_template_part( 'universal-search-widget' ) ?>

<section class="booking booking-payment booking-active" id="booking-1">
    <div class="w-filter dgt-container">
        <div class="left">

        </div>
        <div class="right">
            <a href="<?= site_url('/'); ?>">
                <h3><span>Cancel and Start New Search </span> <i class="icon-close"></i></h3>
            </a>
        </div>
    </div>
    <div class="w-featured bg-gray-light w-result-home">
        <div class="w-list-view dgt-container">
            <div class="w-item-view filtered">
                <h3><?= esc_html($errorMessage) ?></h3>
            </div>
        </div>
    </div>
</section>
