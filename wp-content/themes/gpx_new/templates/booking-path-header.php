<?php
/**
 * @var string $active
 */

$active = $active ?? 'select';
?>
<section class="w-banner w-results w-results-home">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result rs-col-3 booking-path">
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg" alt=""/>
        </li>
    </ul>
    <div class="dgt-container w-box">
        <div class="w-options w-results">

        </div>
        <div class="w-progress-line">
            <ul>
                <li>
                    <span>Select</span>
                    <span class="icon select <?= $active === 'select' ? 'active' : '';?>"></span>
                </li>
                <li>
                    <span>Book</span>
                    <span class="icon book <?= $active === 'book' ? 'active' : '';?>"></span>
                </li>
                <li>
                    <span>Pay</span>
                    <span class="icon pay <?= $active === 'pay' ? 'active' : '';?>"></span>
                </li>
                <li>
                    <span>Confirm</span>
                    <span class="icon confirm <?= $active === 'confirm' ? 'active' : '';?>"></span>
                </li>
            </ul>
            <div class="line">
                <div class="progress"></div>
            </div>
        </div>
    </div>
</section>
