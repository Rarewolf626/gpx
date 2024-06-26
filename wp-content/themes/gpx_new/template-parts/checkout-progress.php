<?php
/** @var string $step */
$step = $step ?? 'book';
$hide_book = $hide_book ?? false;
?>
<section class="w-banner w-results w-results-home checklogin">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result rs-col-3 booking-path">
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg" alt=""/>
        </li>
    </ul>
    <div class="dgt-container w-box">
        <div class="w-options w-results">

        </div>
        <div class="w-progress-line">
            <ul id="checkout-progress">
                <li data-step="select">
                    <span>Select</span>
                    <span class="icon select"></span>
                </li>
                <?php if(!$hide_book): ?>
                <li data-step="book">
                    <span>Book</span>
                    <span class="icon book <?= in_array($step, ['review','exchange','book']) ? 'active' : ''?>"></span>
                </li>
                <?php endif; ?>
                <li data-step="pay">
                    <span>Pay</span>
                    <span class="icon pay <?= in_array($step, ['pay','payment']) ? 'active' : ''?>"></span>
                </li>
                <li data-step="confirm">
                    <span>Confirm</span>
                    <span class="icon confirm <?= in_array($step, ['confirm','complete']) ? 'active' : ''?>"></span>
                </li>
            </ul>
            <div class="line">
                <div class="progress"></div>
            </div>
        </div>
    </div>
</section>
