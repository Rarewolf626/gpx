<?php
    flush();
    if ( substr_count( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) ) { 
        ob_start( "ob_gzhandler" );
    }
    else { 
        ob_start(); 
    }
    $protocol = is_ssl() ? 'https://' : 'http://';
    global $class, $post, $current_url, $SeoWp,$is_blog, $idSearchText;
    $current_url = $protocol . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
?>
<!DOCTYPE html>
<!--[if IE 7]>
    <html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
    <html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
    <html <?php language_attributes(); ?> xmlns:fb="http://www.facebook.com/2008/fbml" xmlns:og="http://ogp.me/ns#" xmlns:addthis="http://www.addthis.com/help/api-spec" >
<!--<![endif]-->
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="google-translate-customization" content="1a94205f716d8901-799a04dc467a2bdb-gd4280bf08c1b260d-2b" />
    <meta name="description" content=" ">
    <meta name="google" content="notranslate">
    <link id="page_favicon" type="image/x-icon" rel="icon" href="<?php echo get_template_directory_uri(); ?>/images/favicon.png">
    <?php
        if(!is_array($SeoWp) ): ?>
            <title><?php wp_title('|', true, 'right'); ?></title>
        <?php endif; ?>
        <link rel="profile" href="http://gmpg.org/xfn/11" />
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
        <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
        <!--[if lt IE 9]>
            <script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
        <![endif]-->
        <script> var URL_SITE = '<?php bloginfo("url"); ?>/'; </script>
        <?php wp_head(); ?>
        <script type="text/javascript">
            var URL_SITE_THEME = '<?php echo get_template_directory_uri(); ?>';
            var URL_WEB_SITE = '<?php bloginfo('url'); ?>/';
        </script>
        <?php if(is_array($SeoWp) ): ?>
            <title><?php echo $SeoWp['title']; ?></title>
            <meta property="og:title" content="<?php echo $SeoWp['title']; ?>" />
            <meta property="og:type" content="website" />
            <meta property="og:url" content="<?php echo $SeoWp['url']; ?>" />
            <meta property="og:description" content="<?php echo $SeoWp['description']; ?>" />
            <meta property="og:image" content="<?php echo $SeoWp['image']; ?>" />
            <meta property="og:image:width" content="600" />
            <meta property="og:image:height" content="315" />
        <?php endif; ?>
        <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/plugins.css">
        <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/header-footer.css">
        <?php if( is_homepage() ) { ?>
            <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/home.css">
        <?php } else  {?>
            <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/inner.css">
            <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/custom.css">
    <?php }?>
</head>
    <body <?php body_class($class); ?>>
        <!-- html solo para el menu responsive -->
        <div class="w-nav">
            <div class="menu-mobile icon-menu"></div>
            <div class="menu-mobile-close icon-close"></div>
        </div>
        <div class="r-overlay"></div>
        <!-- html solo para el menu responsive -->
        <div class="modal dgt-modal modal-alert" id="modal-alert">
            <div class="close-modal"><i class="icon-close"></i></div>
            <div class="w-modal">
                <div class="icon-alert"></div>
                <p>Our call center is temporarilly out of service.</p>
            </div>
        </div>
    <div class="cnt-wrapper">
        <div class="wrapper">
            <header class="header">
                <div class="top-nav">
                    <div class="dgt-container">
                        <a href="<?php bloginfo('url'); ?>" class="w-logo radius">
                            <figure>
                                <img src="<?php echo get_template_directory_uri(); ?>/images/logo.png" alt="logo" width="" height="">
                            </figure>
                        </a>
                        <a href="" class="phone">
                            <i class="icon-phone"></i>
                            <span>(866) 325-6295</span>
                        </a>
                        <div class="access">
                            <div class="user">
                                <a href="">Wagner, Renee</a>
                            </div>
                            <a href="#" class="dgt-btn deposit">Deposit Week</a>
                            <a href="#" class="dgt-btn call-modal-login" >Sign In</a>
                        </div>
                    </div>
                </div>
                <?php wp_nav_menu(array('theme_location' => 'menu-home', 'menu_class' => 'nav-list', 'container' => false )); ?>

                <div class="modal dgt-modal" id="modal-login">
                    <div class="close-modal"><i class="icon-close"></i></div>
                    <div class="w-login">
                        <h2>GPX Owner Sign In</h2>
                        <div class="gform_wrapper">
                            <form action="" class="material">
                                <div class="gform_body">
                                    <ul class="gform_fields">
                                        <li class="gfield">
                                            <label for="" class="gfield_label"></label>
                                            <div class="ginput_container">
                                                <input type="text" placeholder="Email Address" class="validate"">
                                            </div>
                                        </li>
                                        <li class="gfield">
                                            <label for="" class="gfield_label"></label>
                                            <div class="ginput_container">
                                                <input id="login_password" type="password" placeholder="Password" class="validate"">
                                            </div>
                                        </li>
                                        <li class="gfield">
                                            <a href="#">Forgot password?</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="gform_footer">
                                    <input type="submit" value="Sign In">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
            <div class="dgt-container g-w-modal">
                <div class="modal modal-filter dgt-modal" id="modal-filter">
                    <div class="close-modal"><i class="icon-close"></i></div>
                    <div class="w-modal">
                        <form action="">
                            <div class="block">
                                <h2>Sort Results</h2>
                                <select id="select_cities" class="dgt-select" name="mySelect" placeholder="All Cities">
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
                                <select id="select_soonest" class="dgt-select" name="mySelect" placeholder="Date/Soonest to Latest">
                                    <option value="0" disabled selected ></option>
                                    <option value="1">Date/Soonest to Latest</option>
                                    <option value="2">Date/Latest to Soonest</option>
                                    <option value="3">Price/Lowest to Hightest</option>
                                    <option value="4">Price/Highest to Lowest</option>
                                </select>
                                <h3>- Date Range</h3>
                                <a href="" class="dgt-btn">Check-In <span class="icon-date"></span></a>
                            </div>
                            <div class="block">
                                <h2>Filter Results</h2>
                                <h3>- Unit Size</h3>
                                <ul class="list-check">
                                    <li>
                                        <input type="checkbox" id="chk-studio" name="check[]" value="1" placeholder="Studio">
                                        <label for="chk-studio">Studio</label>
                                    </li>
                                    <li>
                                        <input type="checkbox" id="chk-1-bedroom" name="check[]" value="2" placeholder="1 Bedroom">
                                        <label for="chk-1-bedroom">1 Bedroom</label>
                                    </li>
                                    <li>
                                        <input type="checkbox" id="chk-2-bedroom" name="check[]" value="3" placeholder=" 2 Bedroom +">
                                        <label for="chk-2-bedroom">2 Bedroom +</label>
                                    </li>
                                </ul>
                                <h3>- Type of Week</h3>
                                <ul class="list-check">
                                    <li>
                                        <input type="checkbox" id="chk-rental" name="check[]" value="4" placeholder="Rental">
                                        <label for="chk-rental">Rental</label>
                                    </li>
                                    <li>
                                        <input type="checkbox" id="chk-exchange" name="check[]" value="5" placeholder="Exchange">
                                        <label for="chk-exchange">Exchange</label>
                                    </li>
                                </ul>
                                <h3>- Resort Type</h3>
                                <ul class="list-check">
                                    <li>
                                        <input type="checkbox" id="chk-all-inclusive" name="check[]" value="6" placeholder="All-Inclusive Resorts Only">
                                        <label for="chk-all-inclusive">All-Inclusive Resorts Only</label>
                                    </li>
                                </ul>
                            </div>
                        </form>
                    </div>
                </div>       
            </div>
            <div class="dgt-container g-w-modal">
                <div class="modal modal-filter dgt-modal" id="modal-filter-resort">
                    <div class="close-modal"><i class="icon-close"></i></div>
                    <div class="w-modal">
                        <form action="">
                            <div class="block">
                                <h2>Filter Results</h2>
                                <select id="select_cities" class="dgt-select" name="mySelect" placeholder="All Cities">
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
                                <select id="select_soonest" class="dgt-select" name="mySelect" placeholder="Resort /Inventory Type">
                                    <option value="0" disabled selected ></option>
                                    <option value="1">All</option>
                                    <option value="2">All-Inclusive</option>
                                    <option value="3">Exchange</option>
                                    <option value="4">Rental</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>       
            </div>