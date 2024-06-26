<?php
/**
 * @var ?int $cid
 * @var ?int $book
 * @var ?string $returnLink
 */

?>

<?php gpx_theme_template_part('checkout-progress', ['step' => 'book']) ?>
<?php gpx_theme_template_part('universal-search-widget') ?>

<section class="booking booking-payment booking-active" id="booking-1">
    <div class="w-filter dgt-container">
        <div class="left">
            <h1>Invalid Property</h1>
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
                <h3>This property isn't available.</h3>
            </div>
            <div>
                <button type="button" class="btn btn-blue custom-request"
                        data-pid="<?= esc_attr($book) ?>"
                        data-cid="<?= esc_attr($cid) ?>"
                >
                    Submit Custom Request
                </button>
            </div>
        </div>
    </div>
</section>
