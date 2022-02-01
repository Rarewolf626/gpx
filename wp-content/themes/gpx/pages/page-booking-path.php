<?php
/**
 * Template Name: Booking Path Page
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
<section class="booking booking-path booking-active" id="booking-1">
    <div class="w-filter dgt-container">
        <div class="left">
            <h3>Your Next Vacation</h3>
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
                            <div class="result">
                            </div>
                            <ul class="status">
                                <li>
                                    <div class="status-rental"></div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
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
                            <p>USD $189</p>
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
            <div class="check">
                <div class="cnt">
                    <input type="checkbox" id="chk_terms">
                    <label for="chk_terms">
                        I have reviewed and understand the terms and conditions below
                    </label>
                </div>
                <div class="cnt">
                    <a href="" class="dgt-btn btn-next" id="next-1" data-id="booking-2" >Next</a>
                </div>
            </div>
            <div class="tabs">
                <h2>Please Review Booking Policies Before Proceeding</h2>
                <div class="head-tab">
                    <ul>
                        <li> 
                            <a href="" data-id="tab-1" class="head-active">Know Before You Go</a>
                        </li>
                        <li>
                            <a href="" data-id="tab-2" >Terms & Conditions</a>
                        </li>
                    </ul>
                </div>
                <div class="content-tabs">
                    <div id="tab-1" class="item-tab tab-active">
                        <div class="item-tab-cnt">
                            <p>PLEASE CHECK THE DAY OF CHECK IN. A cash/credit card security of deposit of $250 is required at check in. Tangalooma has various check in dates.The date we have provided on your confirmation cannot be changed for any different check in date. *** ALL GUESTS MUST contact Tangalooma Reservations on 07 3637 2119 to book and pre pay the launch transfers to Tangalooma Island. The resort also requests that guests call and reconfirm their booking 48 hours prior to arrival. Current launch fees - are as follows; $70 per adult return and $36 per child return (3 years - 14 years) and travel either 12.30pm or 5pm from Brisbane to Tangalooma and 8.30am or 2pm from Tangalooma to Brisbane. (Journey time is 75 minutes). This price is valid for travel on Saturdays only - if you wish to travel on any other launch times the rate is increased - please contact Tangalooma Reservations for details. Guests must be at launch 45mins prior to departure time. PARKING AT HOLT STREET- Lock up Parking at a cost of $15.00 per day or $60 per week. Please Note: Dolphin feeding is not available for Timeshare Owners or Exchangee. (Viewing only). Nor do the Timeshare units have airconditioning. Tangalooma is a
                            licensed premises therefore only Alcohol that is purchased at the resort is permitted- Resort staff reserve the right to decline luggage transportation if inspection is refused. If taking 4WD contact MI CAT 07 3909 3333 to book. Please note if booking the MI CAT and not taking a 4WD it is approximately a 30 minute walk from the drop off point to the resort on sand and unsealed paths. Tangalooma's check in day's are Monday - Saturday - Please check your arrival and departure dates carefully as once booked these dates are not flexible and cannot be changed with the resort. No Pets Allowed</p>
                        </div>
                        <div class="item-seemore">
                            <a href="#" class="seemore"> <span>See more</span> <i class="icon-arrow-down"></i></a>
                        </div>
                    </div>
                    <div id="tab-2" class="item-tab" >
                        <div class="item-tab-cnt">
                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quos rem laudantium perspiciatis, facere distinctio unde vel quis tempora quae eius nobis earum doloribus, deleniti at. Beatae quia harum, laborum optio. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab tempore minima, velit eius. Excepturi voluptatem blanditiis beatae repellat distinctio, incidunt illo itaque adipisci temporibus quos laudantium aperiam dolores, at voluptate.</p>
                        </div>
                        <div class="item-seemore">
                            <a href="#" class="seemore"> <span>See more</span> <i class="icon-arrow-down"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="booking booking-exchange" id="booking-2">
    <div class="w-filter dgt-container">
        <div class="left">
            <h3>Your Next Booking</h3>
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
                            <div class="result">
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
                    <hgroup>
                        <h2>Exchange Credit</h2>
                        <p>Choose and exchange credit to use for this exchange booking</p>
                    </hgroup>
                    <div class="exchange-result">
                        <h2>Exchange Credit</h2>
                        <p>
                            Our records indicate that you do not have a current deposit with GPX; however this exchange will be performed, in good faith, and in-lieu of a deposit/banking of a week. Please select Deposit A Week from your Dashboard after your booking is complete. Should GPX have questions we will contact you within 24 business hours. Please note: if a deposit cannot be completed in 5 business days this exchange transaction will be cancelled.
                        </p>
                    </div>
                    <ul class="exchange-list">
                        <li class="exchange-item">
                            <div class="w-credit">
                                <div class="head-credit">
                                    <input type="checkbox" id="rdb-credit-1" value="1" name="radio[1][]">
                                    <label for="rdb-credit-1">Apply Credit</label>
                                </div>
                                <div class="cnt-credit">
                                    <ul>    
                                        <li>
                                            <p><strong>Grand Pacific Palasades Resort and Hotel</strong></p> 
                                            <p>2587658</p>
                                        </li>
                                        <li>
                                            <p><strong>Expires:</strong></p>
                                            <span>07 Jan 2017</span>
                                        </li>
                                        <li>
                                            <p><strong>Entitlement Year:</strong></p>
                                            <span>2018</span>
                                            <p>Size: 2bdr./sleeps 7</p>
                                        </li>
                                        <li>
                                            <p>Please note: This booking requires and upgrade fee</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <li class="exchange-item">
                            <div class="w-credit">
                                <div class="head-credit">
                                    <input type="checkbox" id="rdb-credit-2" value="1" name="radio[2][]">
                                    <label for="rdb-credit-2">Apply Credit</label>
                                </div>
                                <div class="cnt-credit">
                                    <ul>    
                                        <li>
                                            <p><strong>Grand Pacific Palasades Resort and Hotel</strong></p> 
                                            <p>2587658</p>
                                        </li>
                                        <li>
                                            <p><strong>Expires:</strong></p>
                                            <span>07 Jan 2017</span>
                                        </li>
                                        <li>
                                            <p><strong>Entitlement Year:</strong></p>
                                            <span>2018</span>
                                            <p>Size: 2bdr./sleeps 7</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <li class="exchange-item">
                            <div class="w-credit">
                                <div class="head-credit">
                                    <input type="checkbox" id="rdb-credit-3" value="1" name="radio[3][]">
                                    <label for="rdb-credit-3">Apply Credit</label>
                                </div>
                                <div class="cnt-credit">
                                    <ul>    
                                        <li>
                                            <p><strong>Grand Pacific Palasades Resort and Hotel</strong></p> 
                                            <p>2587658</p>
                                        </li>
                                        <li>
                                            <p><strong>Expires:</strong></p>
                                            <span>07 Jan 2017</span>
                                        </li>
                                        <li>
                                            <p><strong>Entitlement Year:</strong></p>
                                            <span>2018 </span>
                                            <p>Size: 2bdr./sleeps 7</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
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
<section class="booking booking-payment" id="booking-3">
    <div class="w-filter dgt-container">
        <div class="left">
            <h3>Hilton Grand Vacations Club at MarBrisa</h3>
        </div>
        <div class="right">
            <a href="<?php echo site_url(); ?>/result/">
                <h3>Cancel and Return to Search</h3>
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
                            <p>USD $189</p>
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
                <div class="exchange-credit">
                    <hgroup>
                        <h2>Exchange Credit</h2>
                        <p>Choose and exchange credit to use for this exchange booking</p>
                    </hgroup>
                    <ul class="exchange-list">
                        <li class="exchange-item">
                            <div class="w-credit">
                                <div class="head-credit">
                                    <input type="checkbox" id="rdb-credit-1" value="1" name="radio[1][]">
                                    <label for="rdb-credit-1">Apply Credit</label>
                                </div>
                                <div class="cnt-credit">
                                    <ul>    
                                        <li>
                                            <p><strong>Grand Pacific Palasades Resort and Hotel</strong></p> 
                                            <p>2587658</p>
                                        </li>
                                        <li>
                                            <p><strong>Expires:</strong></p>
                                            <span>07 Jan 2017</span>
                                        </li>
                                        <li>
                                            <p><strong>Entitlement Year:</strong></p>
                                            <span>2018</span>
                                            <p>Size: 2bdr./sleeps 7</p>
                                        </li>
                                        <li>
                                            <p>Please note: This booking requires and upgrade fee</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <li class="exchange-item">
                            <div class="w-credit">
                                <div class="head-credit">
                                    <input type="checkbox" id="rdb-credit-2" value="1" name="radio[2][]">
                                    <label for="rdb-credit-2">Apply Credit</label>
                                </div>
                                <div class="cnt-credit">
                                    <ul>    
                                        <li>
                                            <p><strong>Grand Pacific Palasades Resort and Hotel</strong></p> 
                                            <p>2587658</p>
                                        </li>
                                        <li>
                                            <p><strong>Expires:</strong></p>
                                            <span>07 Jan 2017</span>
                                        </li>
                                        <li>
                                            <p><strong>Entitlement Year:</strong></p>
                                            <span>2018</span>
                                            <p>Size: 2bdr./sleeps 7</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <li class="exchange-item">
                            <div class="w-credit">
                                <div class="head-credit">
                                    <input type="checkbox" id="rdb-credit-3" value="1" name="radio[3][]">
                                    <label for="rdb-credit-3">Apply Credit</label>
                                </div>
                                <div class="cnt-credit">
                                    <ul>    
                                        <li>
                                            <p><strong>Grand Pacific Palasades Resort and Hotel</strong></p> 
                                            <p>2587658</p>
                                        </li>
                                        <li>
                                            <p><strong>Expires:</strong></p>
                                            <span>07 Jan 2017</span>
                                        </li>
                                        <li>
                                            <p><strong>Entitlement Year:</strong></p>
                                            <span>2018 </span>
                                            <p>Size: 2bdr./sleeps 7</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
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
                                            <input type="text" placeholder="Street Address">
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Post/Zip Code">
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <select name="mySelect3" placeholder="Country">
                                                <option value="1" select>Option 1</option>
                                                <option value="2">Option 2</option>
                                                <option value="3">Option 3</option>
                                                <option value="4">Option 4</option>
                                            </select>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Email">
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Cardholder Name">
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Cardholder Number">
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="SVV">
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
                                <p>USD <span>$189.00</span> $129.00</p>
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
                                <p>This charge will on your credit card statement as <strong>Grand Pacific Exchange</strong></p>
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
                        <a href="" class="dgt-btn btn-next" id="next-3" data-id="booking-4">Pay & Confirm</a>
                    </div>
                </div>
                <p>
                    I understand, if confirming a larger unit size than what I deposited I am subject to an upgrade fee. Upgrade fees are as follows: studio deposit to a one (1) bedroom exchange is $85; studio deposit to a two (2) or three (3) bedroom exchange is $185; one (1) bedroom deposit to two (2) or three (3) bedroom exchange is $185; no upgrade fee is required when two (2) bedroom deposit is exchanged for a three (3) bedroom. This upgrade fee is in addition to the exchange fee. A GPX representative will call you the next business day to collect this upgrade fee. If GPX is unable to collect this fee within 48 hours, your exchange is subject to cancellation. I understand and agree that my credit card will be charged immediately for the exchange transaction amount indicated and that this transaction is bound by the terms and conditions of Grand Pacific Exchange for the confirmation of this vacation. THIS DOES NOT APPLY TO MEMBERS BOOKING RENTAL WEEKS AS THIS DOES NOT REQUIRE A CREDIT DEPOSIT.
                </p>
            </div>
        </div>
    </div>   
</section>

<!-- Fin de indicaciones-->
<?php get_footer(); ?>
