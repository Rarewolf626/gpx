<!doctype html>
<!--[if lt IE 7]> <html class="lt-ie9 lt-ie8 lt-ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7]> <html class="lt-ie9 lt-ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8]> <html class="lt-ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 8]><!--> <html <?php language_attributes(); ?>> <!--<![endif]-->
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5" />
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <meta name="format-detection" content="telephone=no">
    <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/images/favicon.ico" />
    <!--[if lt IE 9]>
    <script src="<?php echo get_template_directory_uri(); ?>/js/html5.js"></script>
    <![endif]-->
    <?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <?php endif; ?>
    <?php wp_head(); ?>
</head>
<?php $class = ''?>
<body <?php body_class($class); ?> data-b="a">
<?php  gpr_smartbar_load(); ?>
<div class="w-nav">
    <div class="menu-mobile icon-menu"></div>
    <div class="menu-mobile-close icon-close"></div>
</div>
<div class="r-overlay"></div>
<?php get_template_part( 'template-parts/modal-alert' ); ?>
<div class="cnt-wrapper">
    <div class="wrapper">
        <header class="header">
            <div class="top-nav">
                <div class="dgt-container">
                    <?php if( is_homepage() || is_page('result') || is_page('resorts-result') ) { ?>
                        <h1>
                            <a href="<?php bloginfo('url'); ?>" class="w-logo radius" aria-label="GPX">
                                <figure><img src="<?php echo get_template_directory_uri(); ?>/images/logo.png" alt="<?php bloginfo('name'); ?>" width="" height="" /></figure>
                            </a>
                        </h1>
                    <?php } else  {?>
                        <a href="<?php bloginfo('url'); ?>" class="w-logo radius" aria-label="GPX">
                            <figure>
                                <img src="<?php echo get_template_directory_uri(); ?>/images/logo.png" alt="<?php bloginfo('name'); ?>" width="" height="" />
                            </figure>
                        </a>
                    <?php }?>
                    <a href="#" class="phone noclick" aria-label="phone" onclick="return false;>
                        <i class="icon-phone"></i>
                        <span>(866) 325-6295</span>
                    </a>
                    <div class="access">
                        <a href="tel:8663256295" class="mobile-phone" aria-label="phone">
                            <span>(866) 325-6295</span>
                        </a>
                        <?php
                        if (is_user_logged_in()):
                            $current_user = wp_get_current_user();
                            if(isset($_COOKIE['switchuser']))
                            {
                                $soid = $_COOKIE['switchuser'];
                                $current_user = get_userdata($soid);
                            }
                            /*
                            ?>
                            <div class="user">
                                <a href="<?php echo site_url(); ?>/member-dashboard" class="owner-name"><?php echo $current_user->user_login ; ?></a>
                            </div>
                            */
                            ?>
                        <?php
                        if(isset($_COOKIE['gpx-cart']) && !empty($_COOKIE['gpx-cart']))
                        {
                            global $wpdb;
                            $sql = "SELECT * FROM wp_cart WHERE cartID='".$_COOKIE['gpx-cart']."'";
                            if($row = $wpdb->get_row($sql))
                            {
                        ?>
                        &nbsp;&nbsp;<a href="<?=site_url()?>/booking-path-payment/" aria-label="cart"><i class="fa fa-shopping-cart" aria-hidden="true" style="font-size: 25px;"></i></a>&nbsp;&nbsp;
                        <?php
                            }
                        }
                        ?>
                        <a href="#modal-deposit" id="main-deposit-link" class="dgt-btn deposit better-modal-link" aria-label="Deposit Week">Deposit Week</a>
                        <?php
                        //check to see if the deposit cookie is set and is 1
                        if(isset($_COOKIE['deposit-login']) && $_COOKIE['deposit-login'] == '1')
                        {
                            $_COOKIE['deposit-login'] = 0;
                            unset($_COOKIE['deposit-login']);
                            $depuser = wp_get_current_user();
                            $depowner = 0;
                            if ( in_array( 'gpx_member', (array) $depuser->roles ) ) {
                                //The user has the "author" role
                                $depowner = 1;
                            }
                            ?>
                            <div class="deposit-login" style="display: none;" data-owner="<?=$depowner?>"></div>
                            <?php
                        }
                        ?>
                        <?php endif; ?>

                        <?php if (is_user_logged_in()): ?>
                            <a href="<?php echo wp_logout_url(); ?>" class="dgt-btn signout" >Sign Out</a>
                        <?php else: ?>
                        <a href="#modal-deposit" class="dgt-btn call-modal-login signin deposit-cookie" aria-label="Deposit Week">Deposit Week</a>
                        <a href="#" class="dgt-btn call-modal-login signin" >Sign In</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="dgt-container">
			<?php wp_nav_menu(array('theme_location' => 'menu-home', 'menu_class' => 'nav-list', 'menu_id'=>'top-main-nav-menu', 'container' => false )); ?>
			</div>
        </header>
            <?php get_template_part( 'template-parts/modal-form-login' ); ?>
            <?php get_template_part( 'template-parts/modal-form-pw-reset' ); ?>
            <?php get_template_part( 'template-parts/modal-form-deposit' ); ?>
            <?php get_template_part( 'template-parts/modal-form-custom-request' ); ?>
            <?php get_template_part( 'template-parts/modal-form-checkout' ); ?>
        <!--
        <div class="dgt-container g-w-modal">
            <?php //get_template_part( 'template-parts/modal-form-filter-resort' ); ?>
        </div>
        -->
        <div class="modal dgt-modal modal-alert" id="modal-hold-alert">
        	<div class="close-modal"><i class="icon-close"></i></div>
        	<div class="w-modal">
        		<?php //<div class="icon-alert"></div>?>
        		<p id="alertMsg"></p>
        	</div>
        </div>
