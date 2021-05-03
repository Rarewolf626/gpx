<?php
/**
 * Template Name: Booking Path Payment Page
 * Theme: GPX
 */

get_header();
?> 
<!-- Indicaciones - ELiminar esta sección-->
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
                    <span class="icon book "></span>
                </li>
                <li>
                    <span>Pay</span>
                    <span class="icon pay active"></span>
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
<section class="booking booking-payment booking-active" id="booking-3">
    <div class="w-filter dgt-container">
        <div class="left">
            <h3>Hilton Grand Vacations Club at MarBrisa</h3>
        </div>
        <div class="right">
            
            <a href="<?php echo site_url(); ?>/result/">
                 <h3> <span>Cancel and Return to Search </span> <i class="icon-close"></i></h3>
            </a>
        </div>
    </div>
    <div class="w-featured bg-gray-light w-result-home">
        <div class="w-list-view dgt-container">
            <div class="w-item-view">
                <div class="view-detail">
                    <ul class="list-result">
                        <li>
                            <p><strong>Select Week Number</strong></p>
                            <p>330418</p>
                        </li>
                        <li>
                            <p><strong>Week Type</strong></p>
                            <p>Exchange</p>
                        </li>
                        <li>
                            <p><strong>Price</strong></p>
                            <p>USD <span>$189</span> $129</p>
                        </li>
                        <li>
                            <p><strong>Check In</strong></p>
                            <p>05 Mar 2016</p>
                        </li>
                        <li>
                            <p><strong>Check Out</strong></p>
                            <p>12 Mar 2016</p>
                        </li>
                    </ul>
                    <ul class="list-result">
                        <li>
                            <p><strong>Nights</strong></p>
                            <p>7</p>
                        </li>
                        <li>
                            <p><strong>Bedrooms</strong></p>
                            <p>Studio</p>
                        </li>
                        <li>
                            <p><strong>Sleep</strong></p>
                            <p>2 + 1</p>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="promotional">
                <h3>Promotional Code</h3>
                <div class="w-cnt">
                    <form action="" class="material">
                        <div class="gwrapper">
                            <div class="ginput_container">
                                <input type="text" placeholder="Enter a Promotional Code or Coupon">
                            </div>
                            <div class="ginput_container">
                                <input type="submit" class="dgt-btn" value="Submit">
                            </div>
                        </div>
                        <div class="gwrapper">
                            <div class="ginput_container">
                                <p>You have a <span>$250</span> Credit.</p>
                            </div>
                            <div class="ginput_container">
                                <a href="" class="dgt-btn">Apply Discount</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="payment">
                <h3>Payment</h3>
                <div class="w-cnt">
                    <div class="w-list-cart">
                        <div class="carts">
                            <form action="" class="material">
                                <ul>
                                    <li><img src="<?php echo get_template_directory_uri(); ?>/images/payment.png" alt="logo" width="" height=""></li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Street Address *" required>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Post / Zip Code *" required>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <select name="mySelect3" placeholder="Country *">
                                                <option value="1" select>Option 1</option>
                                                <option value="2">Option 2</option>
                                                <option value="3">Option 3</option>
                                                <option value="4">Option 4</option>
                                            </select>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Email *" required >
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Cardholder Name" required>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Cardholder Number *" required>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="CVV *" required>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container ginput_date">
                                            <p>Expiration Date</p>
                                            <div class="selects">
                                                <select name="mySelect4" placeholder="Month">
                                                    <option value="1" select>Option 1</option>
                                                    <option value="2">Option 2</option>
                                                    <option value="3">Option 3</option>
                                                    <option value="4">Option 4</option>
                                                </select>
                                                <select name="mySelect5" placeholder="Year">
                                                    <option value="1" select>Option 1</option>
                                                    <option value="2">Option 2</option>
                                                    <option value="3">Option 3</option>
                                                    <option value="4">Option 4</option>
                                                </select>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </form>
                        </div>
                    </div>
                    <ul class="w-list-details">
                        <li>
                            <div class="gtitle">
                                <span>Payment Details</span>
                            </div>
                        </li>
                        <li>
                            <p>Booking <strong>Hiltron Grand Vacations Club at MarBrisa</strong></p>
                        </li>
                        <li>
                            <div class="result">
                                <p>USD <span>$189.00</span> $12900</p>
                            </div>
                        </li>
                        <li>
                            <div class="result">
                                <p>Account Credit $0.00</p>
                            </div>
                        </li>
                        <li>
                            <div class="result">
                                <p><strong>remove</strong> CPO $0.00</p>
                            </div>
                        </li>
                        <li>
                            <div class="result">
                                <p>Upgrade Fee $99.00</p>
                            </div>
                        </li>
                        <li>
                            <div class="result">
                                <p>Discount $0.00</p>
                            </div>
                        </li>
                        <li>
                            <div class="result noline">
                                <p> Taxes: Included</p>
                            </div>
                        </li>
                        <li>
                            <div class="result total">
                                <p>Total: $228.00</p>
                            </div>
                        </li>
                        <li>
                            <div class="message">
                                <p>This charge will show on your credit card statement as <strong>Grand Pacific Exchange</strong></p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="payconfirm">
                <div class="check">
                    <div class="cnt">
                        <input type="checkbox" id="chk_terms_2">
                        <label for="chk_terms_2">
                            I have reviewed and understand the terms and conditions below
                        </label>
                    </div>
                    <div class="cnt">
                        <a href="<?php echo site_url(); ?>/booking-path-confirmation/" class="dgt-btn btn-next" id="next-3" data-id="booking-4">Pay & Confirm</a>
                    </div>
                </div>
                <p> <strong>All Transactions are non-refundable</strong></p>
               <br>
                <p>
                   <strong>All transactions are non-refundable.</strong>  I understand, if confirming a larger unit size than what I deposited I am subject to an upgrade fee. Upgrade fees are as follows: studio deposit to a one (1) bedroom exchange is $85; studio deposit to a two (2) or three (3) bedroom exchange is $185; one (1) bedroom deposit to two (2) or three (3) bedroom exchange is $185; no upgrade fee is required when two (2) bedroom deposit is exchanged for a three (3) bedroom. This upgrade fee is in addition to the exchange fee. A GPX representative will call you the next business day to collect this upgrade fee. If GPX is unable to collect this fee within 48 hours, your exchange is subject to cancellation. I understand and agree that my credit card will be charged immediately for the exchange transaction amount indicated and that this transaction is bound by the terms and conditions of Grand Pacific Exchange for the confirmation of this vacation. THIS DOES NOT APPLY TO MEMBERS BOOKING RENTAL WEEKS AS THIS DOES NOT REQUIRE A CREDIT DEPOSIT.
                </p>
            </div>
        </div>
    </div>   
</section>
<!-- Fin de indicaciones-->
<?php get_footer(); ?>
