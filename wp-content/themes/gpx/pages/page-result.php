<?php
/**
 * Template Name: Result Page
 * Theme: GPX
 */

get_header();
?> 
<!-- Indicaciones - ELiminar esta sección-->
<section class="w-banner w-results w-results-home">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result rs-col-3">
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg" alt="" />
        </li>
    </ul>
    <div class="dgt-container w-box">
        <form action="">
            <div class="w-options w-results">
                <h4>Displaying Resorts In:</h4>
                <div class="cnt col-3">
                    <select id="select_country" class="dgt-select" name="mySelect" placeholder="Country">
                        <option value="0" disabled selected value="foo" ></option>
                        <option value="1">Australia</option>
                        <option value="2">Canada</option>
                        <option value="3">Caribbean</option>
                        <option value="4">China</option>
                        <option value="5">Cypress</option>
                        <option value="6">Egyp & Surrounds</option>
                        <option value="7">Europe - Central</option>
                        <option value="8">Europe - Eastern</option>
                        <option value="9">Europe - Other</option>
                        <option value="10">France</option>
                        <option value="11">India</option>
                        <option value="12">Indonesia</option>
                        <option value="13">Ireland</option>
                        <option value="14">Italy</option>
                        <option value="15">Malaysia</option>
                        <option value="16">Malta</option>
                        <option value="17">Mexico</option>
                        <option value="18">New Zealand</option>
                        <option value="19">North Africa</option>
                        <option value="20">Pacific Islands</option>
                        <option value="21">Philippines</option>
                        <option value="22">Portugal</option>
                        <option value="23">Rest of Asia</option>
                        <option value="24">South Africa</option>
                        <option value="25">South America</option>
                        <option value="26">South Korea</option>
                        <option value="27">Spain</option>
                        <option value="28">Taiwan</option>
                        <option value="29">Thailand</option>
                        <option value="30">Turkey</option>
                        <option value="31">UK</option>
                        <option value="32">United Arab Emirates</option>
                        <option value="33">USA</option>
                        <option value="34">Vietnam</option>
                    </select>
                </div>
                <div class="cnt col-3">
                    <select id="select_location" class="dgt-select" name="mySelect" placeholder="Location">
                        <option value="0" disabled selected ></option>
                        <option value="1">All</option>
                        <option value="2">California</option>
                        <option value="3">Florida</option>
                        <option value="4">Hawaiian Islands</option>
                        <option value="5">Wid West</option>
                        <option value="6">Nevada</option>
                        <option value="7">North East</option>
                        <option value="8">Pacific Coast</option>
                        <option value="9">Rocky Mountains</option>
                        <option value="10">South East</option>
                        <option value="11">South West</option>
                    </select>
                </div>
                <div class="cnt col-3">
                    <select id="select_location" class="dgt-select" name="mySelect" placeholder="Month/Year">
                        <option value="0" disabled selected ></option>
                        <option value="1">January 2017</option>
                        <option value="2">February 2017</option>
                        <option value="3">March 2017</option>
                        <option value="4">April 2017</option>
                        <option value="5">May 2017</option>
                        <option value="6">June 2017</option>
                        <option value="7">July 2017</option>
                        <option value="8">August 2017</option>
                        <option value="9">September 2017</option>
                        <option value="10">October 2017</option>
                        <option value="11">November 2017</option>
                        <option value="12">December 2017</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</section>
<section class="w-filter dgt-container">
    <div class="left">
        <h3>417 Search Results</h3>
    </div>
    <div class="right">
        <ul class="status">
            <li>
                <div class="status-all">
                    <p>All-Inclusive</p>
                </div>
            </li>
            <li>
                <div class="status-exchange">
                    <p>Exchange</p>
                </div>
            </li>
            <li>
                <div class="status-rental">
                    <p>Rental</p>
                </div>
            </li>
        </ul>
        <a href="" class="dgt-btn call-modal-filter">Filter Results</a>
    </div>
</section>
<section class="w-featured bg-gray-light w-result-home">
    <ul class="w-list-view dgt-container">
        <li class="w-item-view">
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
                        <a href="" class="dgt-btn">View Resort</a>
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
                                <div class="status-all">
                                    <p>All-Inclusive</p>
                                </div>
                            </li>
                            <li>
                                <div class="status-exchange"></div>
                            </li>
                            <li>
                                <div class="status-rental"></div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <ul class="w-list-result">
                <li class="item-result active">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p class="mach"><strong>$248</strong></p>
                            <p>Now <strong>$159</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-exchange"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn">Hold</a>
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$189</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-exchange"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn">Hold</a>
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$349</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-rental"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$189</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-exchange"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn">Hold</a>
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$189</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-exchange"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn">Hold</a>
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$189</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-exchange"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn">Hold</a>
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
                <li class="item-result active">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p class="mach"><strong>$149</strong></p>
                            <p>Now <strong>$99</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-exchange"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn">Hold</a>
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$189</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-exchange"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn">Hold</a>
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
            </ul>
            <a href="" class="seemore"> 
                <span>View more inventory for this resort</span> 
                <i class="icon-arrow-down"></i>
            </a>
        </li>
        <li class="w-item-view">
            <div class="view">
                <div class="view-cnt">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/imgv2.jpg" alt="">
                </div>
                <div class="view-cnt">
                    <div class="descrip">
                        <hgroup>
                            <h2>Channel Island Shores</h2>
                            <span>USA / Oxnard, California</span>
                        </hgroup>
                        <a href="" class="dgt-btn">View Resort</a>
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
                                <div class="status-all">
                                    <p>All-Inclusive</p>
                                </div>
                            </li>
                            <li>
                                <div class="status-exchange"></div>
                            </li>
                            <li>
                                <div class="status-rental"></div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <ul class="w-list-result">
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$189</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-exchange"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn">Hold</a>
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$189</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-exchange"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn">Hold</a>
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$349</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-rental"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
            </ul>
        </li>
        <li class="w-item-view">
            <div class="view">
                <div class="view-cnt">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/imgv3.jpg" alt="">
                </div>
                <div class="view-cnt">
                    <div class="descrip">
                        <hgroup>
                            <h2>Tahoe Beach and Ski Club</h2>
                            <span>USA / South Lake Tahoe, California</span>
                        </hgroup>
                        <a href="" class="dgt-btn">View Resort</a>
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
                                <div class="status-all">
                                    <p>All-Inclusive</p>
                                </div>
                            </li>
                            <li>
                                <div class="status-exchange"></div>
                            </li>
                            <li>
                                <div class="status-rental"></div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <ul class="w-list-result">
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$189</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-exchange"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn">Hold</a>
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$349</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-rental"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$189</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-exchange"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn">Hold</a>
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
                <li class="item-result active">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p class="mach"><strong>$149</strong></p>
                            <p>Now <strong>$99</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-exchange"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn">Hold</a>
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
            </ul>
        </li>
        <li class="w-item-view">
            <div class="view">
                <div class="view-cnt">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/imgv4.jpg" alt="">
                </div>
                <div class="view-cnt">
                    <div class="descrip">
                        <hgroup>
                            <h2>Mountain Retreat Resort</h2>
                            <span>USA / South Lake Tahoe, California</span>
                        </hgroup>
                        <a href="" class="dgt-btn">View Resort</a>
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
                                <div class="status-exchange"></div>
                            </li>
                            <li>
                                <div class="status-rental"></div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <ul class="w-list-result">
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$189</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-exchange"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn">Hold</a>
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$189</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-exchange"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn">Hold</a>
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
                <li class="item-result">
                    <div class="w-cnt-result">
                        <div class="result-head">
                            <p><strong>$349</strong></p>
                            <ul class="status">
                                <li>
                                    <div class="status-rental"></div>
                                </li>
                            </ul>
                        </div>
                        <div class="cnt">
                            <p><strong>Exchange</strong></p>
                            <p>Check-In 05 Mar 2016</p>
                            <p>7 Nights</p>
                            <p>Studio/Sleeps 2</p>
                        </div>
                        <div class="list-button">
                            <a href="" class="dgt-btn active">Book</a>
                        </div>
                    </div>
                </li>
            </ul>
        </li>
    </ul>
    <div class="dgt-container">
        <div class="w-list-actions">
            <a href="" class="dgt-btn">Submit a Custom Request</a>
            <a href="" class="dgt-btn">Start a New Search</a>
        </div>
    </div>
</section>
<!-- Fin de indicaciones-->
<?php get_footer(); ?>