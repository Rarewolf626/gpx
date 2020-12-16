<?php
/**
 * Template Name: Booking Path Exchange Page
 * Theme: GPX
 */

get_header();
while ( have_posts() ) : the_post();
?>
<section class="w-banner w-results w-results-home">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result rs-col-3 booking-path">
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg" alt="" />
        </li>
    </ul>
    <div class="dgt-container w-box">
        <div class="w-options w-results">
            
        </div>
        <div class="w-progress-line">
            <ul>
                <li>
                    <span>Select</span>
                    <span class="icon select"></span>
                </li>
                <li>
                    <span>Book</span>
                    <span class="icon book active"></span>
                </li>
                <li>
                    <span>Pay</span>
                    <span class="icon pay"></span>
                </li>
                <li>
                    <span>Confirm</span>
                    <span class="icon confirm"></span>
                </li>
            </ul>
            <div class="line">
                <div class="progress"></div>
            </div>
        </div>
    </div>
</section>
<section class="booking booking-exchange booking-active">
    <div class="w-filter dgt-container">
        <div class="left">
            <h3>Your Next Booking</h3>
        </div>
        <div class="right">
            
            <a href="<?php echo site_url(); ?>/result/">
                 <h3> <span>Cancel and Return to Search </span></h3>
            </a>
        </div>
    </div>
    <div class="w-featured bg-gray-light w-result-home">
        <div class="w-list-view dgt-container">
            <div class="w-item-view">
                <div class="view">
                    <div class="view-cnt">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/imgv1.jpg" alt="">
                    </div>
                    <div class="view-cnt">
                        <div class="descrip">
                            <hgroup>
                                <h2>Hilton Grand Vacations Club at MarBrisa</h2>
                                <span>USA / Carlsbad, California</span>
                            </hgroup>
                            <p>Check-In 05 Mar 2016</p>
                            <p>Check-Out 12 Mar 2016</p>
                        </div>
                        <div class="w-status">
                            <div class="close">
                                <i class="icon-close"></i>
                            </div>
                            <div class="result">
                                <span class="count-result" >26 Results for</span>
                                <span class="date-result" >January 2017</span>
                            </div>
                            <ul class="status">
                                <li>
                                    <div class="status-rental"></div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="exchange-credit">
                    <div class="exchange-result active-message">
                        <h2>Exchange Credit</h2>
                        <p>
                            Our records indicate that you do not have a current deposit with GPX.  This exchange will be performed provided you select the week you’d like to deposit below.  Your selected week for deposit will be reviewed by GPX and if approved and accepted no further action will be needed for this transaction. Please note: If the deposited week cannot be approved successfully within 5 days this transaction will be canceled.
                        </p>
                    </div>
                </div>
            </div>
            <div class="member-form">
                <hgroup>
                    <h2>Member / Guest Information</h2>
                    <h2>GPX Member: <strong>Wagner, Renee</strong></h2>
                </hgroup>
                <div class="w-form">
                    <form action="" class="material">
                        <div class="head-form">
                            <input type="checkbox" id="rdb-reservation">
                            <label for="rdb-reservation">Click here to assign this reservation to a guest</label>
                        </div>
                        <ul class="list-form">
                            <li>
                                <div class="ginput_container">
                                    <input type="text" placeholder="Title" class="validate"" required>
                                </div>
                            </li>
                            <li>
                                <div class="ginput_container">
                                    <input type="text" placeholder="First Name" class="validate"" required>
                                </div>
                            </li>
                            <li>
                                <div class="ginput_container">
                                    <input type="text" placeholder="Last Name" class="validate"" required>
                                </div>
                            </li>
                            <li>
                                <div class="ginput_container">
                                    <input type="text" placeholder="Email" class="validate"" required>
                                </div>
                            </li>
                            <li>
                                <div class="ginput_container">
                                    <input type="text" placeholder="Confirm Email" class="validate"">
                                </div>
                            </li>
                            <li>
                                <div class="ginput_container">
                                    <input type="text" placeholder="Home Phone" class="validate"">
                                </div>
                            </li>
                            <li>
                                <div class="ginput_container">
                                    <input type="text" placeholder="Mobile Phone" class="validate"">
                                </div>
                            </li>
                        </ul>
                        <ul class="list-form">
                            <li>
                                <div class="ginput_container">
                                    <input type="text" placeholder="Street Address" class="validate"" required>
                                </div>
                            </li>
                            <li>
                                <div class="ginput_container">
                                    <input type="text" placeholder="City" class="validate"" required>
                                </div>
                            </li>
                            <li>
                                <div class="ginput_container">
                                    <input type="text" placeholder="State" class="validate"" required>
                                </div>
                            </li>
                            <li>
                                <div class="ginput_container">
                                    <input type="text" placeholder="Post/Zip Code" class="validate"" required>
                                </div>
                            </li>
                            <li>
                                <div class="group">
                                    <div class="ginput_container">
                                        <select name="mySelect1" placeholder="Adults">
                                            <option value="1" select>Option 1</option>
                                            <option value="2">Option 2</option>
                                        </select>
                                        <select name="mySelect2" placeholder="Children">
                                            <option value="1" select>Option 1</option>
                                            <option value="2">Option 2</option>
                                        </select>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <div class="gform_footer">
                            <!--<input class=" dgt-btn" type="submit" value="Next">-->
                            <a href="<?php echo site_url(); ?>/booking-path-payment/" class="dgt-btn" id="next-2" data-id="booking-3">Next</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
endwhile;
get_footer(); ?>