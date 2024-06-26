<?php
/**
 * @var ?string $book
 * @var ?string $returnLink
 */

?>
<?php gpx_theme_template_part( 'checkout-progress', [ 'step' => 'book' ] ) ?>
<?php gpx_theme_template_part( 'universal-search-widget' ) ?>

<section class="booking booking-payment booking-active" id="booking-1">
    <div class="w-filter dgt-container">
        <div class="left">
            <h1>Please Login</h1>
        </div>
        <div class="right">
            <a href="<?php echo site_url('/'); ?>">
                <h3> <span>Cancel and Start New Search </span> <i class="icon-close"></i></h3>
            </a>
        </div>
    </div>
    <div class="w-featured bg-gray-light w-result-home">
        <div class="w-list-view dgt-container">
            <div class="w-item-view filtered" id="signInError">
                <h3>You must be logged in to book a property.  Please <a href="#" class="call-modal-login">sign in</a> to continue.</h3>
            </div>
        </div>
    </div>
</section>
