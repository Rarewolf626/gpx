<?php
/**
 * Template Name: Resorts Page
 * Theme: GPX
 */

get_header();
?> 
<!-- Indicaciones - ELiminar esta sección-->
<section class="w-banner">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen">
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/resorts.jpg" alt="" />
        </li>
    </ul>
    <div class="dgt-container w-box">
        <div class="gsub-title">
            <h2>Explore the GPX Resort Directory</h2>
        </div>
        <form class="" role="search" method="get" action="<?php echo home_url( '/resorts-result' ); ?>">
            <div class="w-options">
                <div class="cnt left">
                    <h4>Find a Resort by Name</h4>
                    <div class="component">
                        <input id="resort_autocomplete" placeholder="Type a Resort Name">
                    </div>
                </div>
                <div class="center">
                    <span>Or</span>
                </div>
                <div class="cnt right">
                    <h4>Find Resort by Location</h4>
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
            <input type="submit" class="dgt-btn" value="Explore">
        </form>
    </div>
</section>

<section class="w-featured bg-gray-light">
    <span class="tag">
        <img src="<?php echo get_template_directory_uri(); ?>/images/tag03.png" alt="Featured Resorts">
    </span>
    <?php 
        $props = gpx_resorts_list();

        if($props)
        {
        ?>
     <ul class="w-list w-list-items">       
        <?php 
            foreach($props as $prop)
            {
                include( (locate_template('template-parts/resort-featureditem.php')));
            }
        ?>    
    </ul>    
        <?php 
        }
    
    
    ?>
    
    

    <a href="" class="seemore seemoreitems"> <span>See more</span> <i class="icon-arrow-down"></i></a>
</section>
<!-- Fin de indicaciones-->
<?php get_footer(); ?>