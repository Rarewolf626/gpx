<?php
/**
 * Template Name: Resorts Result Page
 * Theme: GPX
 */

get_header();
?> 
<!-- Indicaciones - ELiminar esta sección-->
<section class="w-banner w-results">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result">
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg" alt="" />
        </li>
    </ul>
    <div class="dgt-container w-box">
        <form action="">
            <div class="w-options w-results">
                <h4>Displaying Resorts In:</h4>
                <div class="cnt left resort">
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
                <div class="cnt right resort">
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
            </div>
        </form>
    </div>
</section>
<section class="w-filter dgt-container">
    <div class="left">
        <h3>36 Resorts in USA/California</h3>
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
        <a href="" class="dgt-btn call-modal-filter-resort">Filter Result</a>
    </div>
</section>
<section class="w-featured bg-gray-light">
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
        </li>
        <li class="w-item-view">
            <div class="view">
                <div class="view-cnt">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/imgv5.jpg" alt="">
                </div>
                <div class="view-cnt">
                    <div class="descrip">
                        <hgroup>
                            <h2>Indian Palms Vacation Club</h2>
                            <span>USA / Indio, California</span>
                        </hgroup>
                        <a href="" class="dgt-btn">View Resort</a>
                    </div>
                    <div class="w-status">
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
        </li>
    </ul>
</section>
<!-- Fin de indicaciones-->
<?php get_footer(); ?>