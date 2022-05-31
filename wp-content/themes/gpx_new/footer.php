</div>
</div>
<div id="11"></div>
<footer class="footer" id="footer">
    <div class="footer-cnt">
        <div class="scrolltop">
            <i class="icon-upload"></i>
        </div>
        <ul class="w-footer-list">
            <li class="w-footer-item w-social">
                <a href="tel:+18663256295" class="phone">(866) 325-6295</a>
                <h3>Connect</h3>
                <div class="w-social-list">
                    <?php wp_nav_menu(array('theme_location' => 'menu-social', 'menu_class' => 'nav-social', 'container' => false )); ?>
                </div>
                <div class="gpx">
                    <?php wp_nav_menu(array('menu' => '6', 'menu_class'=>'nav-footer-terms', 'container'=>false))?>
                    <p><span>&copy;</span> <span id="id_year"><?php echo date('Y'); ?></span> <span>GPX</span></p>
                </div>
            </li>
            <li class="w-footer-item w-form">
                <?php if (!is_user_logged_in()): ?>
                <div class="div">
                    <h2>Sign In</h2>
                    <div class="gform_wrapper">
                        <?php get_template_part( 'template-parts/footer-form-login' ); ?>
                        <a href="#" class="dgt-btn dgt-login call-modal-login btn-sign-in">Sign In</a>
                        <?php if (is_user_logged_in()): ?>
                            <a href="<?php echo wp_logout_url(); ?>" class="dgt-btn dgt-login signout" >Sign Out</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else:
                    $current_user = wp_get_current_user();
                    if(isset($_COOKIE['switchuser']))
                    {
                        $soid = $_COOKIE['switchuser'];
                        $current_user = get_userdata($soid);
                    }
                ?>
                <div class="div-login">
                    <div class="user">
                        <a href="<?php echo site_url(); ?>/member-dashboard" class="owner-name"><?php echo $current_user->user_login ; ?></a>
                    </div>
                    <a href="<?php echo wp_logout_url(); ?>" class="dgt-btn">Sign Out</a>
                </div>
                <?php endif; ?>
                <a href="https://grandpacificresorts.com/" target="_blank"><img src="/wp-content/uploads/2017/03/logo-gpr-white.png" alt="Grand Pacifc Resorts" title="Grand Pacific Resorts" style="margin-top:20px;" /></a>
                <?php
                /*
                ?>
                <a href="http://resortime.com/" target="_blank"><img src="https://gpxvacations.com/wp-content/uploads/2017/05/logo-rt-white.png" alt="ResorTime" title="ResorTime" style="margin-top:20px;" /></a>
                <?php
                */
                ?>
            </li>
            <li class="w-footer-item w-menu">
                <?php wp_nav_menu(array('menu' => '2', 'theme_location' => 'menu-home-footer', 'menu_class' => 'nav-footer', 'container' => false )); ?>
                <div class="gpx terms-nav">
                	<?php wp_nav_menu(array('menu' => '6', 'menu_class'=>'nav-footer-terms', 'container'=>false))?>
                    <p><span>©</span> <span id="id_year2"><?php echo date('Y'); ?></span> <span>GPX</span></p>
                </div>
            </li>
        </ul>
    </div>
</footer>
<?php
if(isset($_REQUEST['gpxc']) && !empty($_REQUEST['gpxc']))
{
?>
<div id="acRequest" data-coupon="<?=$_REQUEST['gpxc']?>" style="display:none;"></div>
<?php
}
?>
<?php wp_footer(); ?>
<?php get_template_part( 'template-parts/modal-form-login' ); ?>
<?php get_template_part( 'template-parts/modal-form-pw-reset' ); ?>
<?php get_template_part( 'template-parts/modal-form-custom-request' ); ?>
<?php get_template_part( 'template-parts/modal-form-deposit' ); ?>
<?php get_template_part( 'template-parts/modal-alert' ); ?>
<?php get_template_part( 'template-parts/modal-hold-alert' ); ?>
</body>
</html>
