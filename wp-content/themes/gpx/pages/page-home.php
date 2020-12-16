<?php
/**
 * Template Name: Home Page
 * Theme: lite
 */
get_header();
?> 
<!-- Indicaciones - ELiminar esta sección-->
<section class="w-banner">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen">
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/california.jpg" alt="" />
        </li>
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/couple.jpg" alt="" />
        </li>
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/florida.jpg" alt="" />
        </li>
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/hawai.jpg" alt="" />
        </li>
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/vegas.jpg" alt="" />
        </li>
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/mexico.jpg" alt="" />
        </li>
    </ul>
    <div class="dgt-container w-box">
        <div class="gsub-title">
            <h3>GPX Is your private exchange service</h3>
        </div>
        <h2 class="gtitle">
            Vacation Somewhere New
        </h2>
        <form class="" role="search" method="get" action="<?php echo home_url( '/result' ); ?>">
            <div class="w-options">
                <div class="cnt left">
                    <div class="component">
                        <input id="location_autocomplete" placeholder="Type a Location">
                    </div>
                </div>
                <div class="cnt right">
                    <select id="select_month" class="dgt-select" name="mySelect" placeholder="Any Month">
                        <option value="0" disabled selected value="foo" ></option>
                        <option value="1" >January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="4">May</option>
                        <option value="4">June</option>
                        <option value="4">July</option>
                        <option value="4">August</option>
                        <option value="4">September</option>
                        <option value="4">October</option>
                        <option value="4">November</option>
                        <option value="4">December</option>
                    </select>
                    <select id="select_year" class="dgt-select" name="mySelect" placeholder="Any Year">
                        <option value="0" disabled selected ></option>
                        <option value="1">2016</option>
                        <option value="2">2017</option>
                        <option value="3">2018</option>
                        <option value="4">2019</option>
                        <option value="4">2020</option>
                        <option value="4">2021</option>
                        <option value="4">2022</option>
                        <option value="4">2023</option>
                        <option value="4">2024</option>
                        <option value="4">2025</option>
                        <option value="4">2026</option>
                        <option value="4">2027</option>
                    </select>
                </div>
            </div>
            <input type="submit" class="dgt-btn" value="Search">
        </form>

        <div id="trigger1"></div>
        <div id="trigger2"></div>
        <div id="trigger3"></div>
    </div>
</section>
<section class="w-travel"> 
    <div class="w-travel-cnt">
        <div class="gtitle">
            <h2>Travel More For Less</h2>
            <p>GPX makes Exchange and Rentals Simple.</p>
        </div>
        <ul class="w-list">
            <li class="w-item" id="animate1">
               <img src="<?php echo get_template_directory_uri(); ?>/images/item01.png" alt="">
               <h3>Map Your Journey</h3>
               <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor r sit voluptatem accusantium doloremque</p>
            </li>
            <li class="w-item" id="animate2">
               <img src="<?php echo get_template_directory_uri(); ?>/images/item02.png" alt="">
               <h3>Look Before You Book</h3>
               <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor r sit voluptatem accusantium doloremque</p>
            </li>
            <li class="w-item" id="animate3">
               <img src="<?php echo get_template_directory_uri(); ?>/images/item03.png" alt="">
               <h3>The World Is yours to Explore</h3>
               <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor r sit voluptatem accusantium doloremque</p>
            </li>
        </ul>
    </div>
</section>
<section class="w-offers bg-gray">
    <span class="tag">
        <img src="<?php echo get_template_directory_uri(); ?>/images/tag01.png" alt="This Week's Offers">
    </span>
    <ul class="w-list">
        <?php
            $posts_args = array( 'post_type' => 'offer', 'posts_per_page' => 3, 'meta_key' => 'dgt_extra_order', 'orderby' => 'meta_value_num', 'order'     => 'ASC' );
            $posts_query = new WP_Query($posts_args);
            if ($posts_query->have_posts()){
              while ($posts_query->have_posts()) { 
                $posts_query->the_post();
        ?>
        <li class="w-item">
            <div class="cnt">
                <a href="<?php the_permalink(); ?>">
                    <figure>
                        <?php 
                            if ( has_post_thumbnail()) {
                                $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full');
                        ?>
                            <img src="<?php echo $large_image_url[0] ?>" alt="">
                        <?php } ?>
                    </figure>
                    <h3><?php the_title(); ?></h3>
                    <?php the_content(); ?>
                    <div class="dgt-btn">
                       Explore Offer 
                    </div>
                </a>
            </div>
        </li>
        <?php } } ?>
    </ul>
</section>
<section class="w-featured bg-gray-light">
    <span class="tag">
        <img src="<?php echo get_template_directory_uri(); ?>/images/tag02.png" alt="Featured Destinations">
    </span>
    <ul class="w-list w-list-items">
        <li class="w-item">
            <div class="cnt">
                <a href="">
                    <figure>
                        <img src="<?php echo get_template_directory_uri(); ?>/images/img04.jpg" alt="Carlsbad, CA">
                    </figure>
                    <h3>Carlsbad, CA</h3>
                    <p>Os eriandis il quosama ero omnimin ctorempor quosa susa lorem ipsum quosament.</p>
                    <div class="dgt-btn">
                       Explore Offer 
                    </div>
                </a>
            </div>
        </li>
        <li class="w-item">
            <div class="cnt">
                <a href="">
                    <figure>
                        <img src="<?php echo get_template_directory_uri(); ?>/images/img05.jpg" alt="Las Vegas, NV">
                    </figure>
                    <h3>Las Vegas, NV</h3>
                    <p>Os eriandis il quosama ero omnimin ctorempor quosa susa lorem ipsum quosament.</p>
                    <div class="dgt-btn">
                       Explore Offer 
                    </div>
                </a>
            </div>
        </li>
        <li class="w-item">
            <div class="cnt">
                <a href="">
                    <figure>
                        <img src="<?php echo get_template_directory_uri(); ?>/images/img06.jpg" alt="Princeville, HI">
                    </figure>
                    <h3>Princeville, HI</h3>
                    <p>Os eriandis il quosama ero omnimin ctorempor quosa susa lorem ipsum quosament.</p>
                    <div class="dgt-btn">
                       Explore Offer 
                    </div>
                </a>
            </div>
        </li>
        <li class="w-item">
            <div class="cnt">
                <a href="">
                    <figure>
                        <img src="<?php echo get_template_directory_uri(); ?>/images/img04.jpg" alt="Carlsbad, CA">
                    </figure>
                    <h3>Carlsbad, CA</h3>
                    <p>Os eriandis il quosama ero omnimin ctorempor quosa susa lorem ipsum quosament.</p>
                    <div class="dgt-btn">
                       Explore Offer 
                    </div>
                </a>
            </div>
        </li>
        <li class="w-item">
            <div class="cnt">
                <a href="">
                    <figure>
                        <img src="<?php echo get_template_directory_uri(); ?>/images/img05.jpg" alt="Las Vegas, NV">
                    </figure>
                    <h3>Las Vegas, NV</h3>
                    <p>Os eriandis il quosama ero omnimin ctorempor quosa susa lorem ipsum quosament.</p>
                    <div class="dgt-btn">
                       Explore Offer 
                    </div>
                </a>
            </div>
        </li>
        <li class="w-item">
            <div class="cnt">
                <a href="">
                    <figure>
                        <img src="<?php echo get_template_directory_uri(); ?>/images/img06.jpg" alt="Princeville, HI">
                    </figure>
                    <h3>Princeville, HI</h3>
                    <p>Os eriandis il quosama ero omnimin ctorempor quosa susa lorem ipsum quosament.</p>
                    <div class="dgt-btn">
                       Explore Offer 
                    </div>
                </a>
            </div>
        </li>
    </ul>
    <a href="" class="seemore seemoreitems"> <span>See more</span> <i class="icon-arrow-down"></i></a>
</section>
<!-- Fin de indicaciones-->

<script src="<?php echo get_template_directory_uri(); ?>/js/ScrollMagic.js" type="text/javascript"></script>
<script>
    var controller = new ScrollMagic.Controller();

    // build scene
    var scene1 = new ScrollMagic.Scene({triggerElement: "#trigger1"})
                    // trigger animation by adding a css class
                    .setClassToggle("#animate1", "show1")
                    //.addIndicators({name: "1 - add a class"}) // add indicators (requires plugin)
                    .addTo(controller);
    // build scene
    var scene2 = new ScrollMagic.Scene({triggerElement: "#trigger2"})
                    // trigger animation by adding a css class
                    .setClassToggle("#animate2", "show2")
                    //.addIndicators({name: "1 - add a class"}) // add indicators (requires plugin)
                    .addTo(controller);

    // build scene
    var scene3 = new ScrollMagic.Scene({triggerElement: "#trigger3"})
                    // trigger animation by adding a css class
                    .setClassToggle("#animate3", "show3")
                    //.addIndicators({name: "1 - add a class"}) // add indicators (requires plugin)
                    .addTo(controller);
</script>
<?php get_footer(); ?>