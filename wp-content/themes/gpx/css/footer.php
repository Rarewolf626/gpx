<?php
/**
 * @package WordPress
 * @subpackage GPX
 * @since GPX 1.0
 */
?>
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
                    <a href="tel:(866)325-6295" class="phone">(866) 325-6295</a>
                    <h3>Connect</h3>
                    <div class="w-social-list">
                        <?php wp_nav_menu(array('theme_location' => 'menu-social', 'menu_class' => 'nav-social', 'container' => false )); ?>
                    </div>
                    <div class="gpx">
                        <a class="privacy" href="#">Privacy Policy</a>
                        <a class="terms" href="#">Terms & Conditions</a>
                        <p><span>©</span> <span id="id_year"></span> <span>GPX</span></p>
                    </div>

                </li>
                <li class="w-footer-item w-form">
                    <div class="div">
                        <h2>Sign In</h2>
                        <div class="gform_wrapper">
                            <form action="" class="material">
                                <div class="gform_body">
                                    <ul class="gform_fields">
                                        <li class="gfield">
                                            <label for="" class="gfield_label"></label>
                                            <div class="ginput_container">
                                                <input type="text" placeholder="Email Address" class="validate"" required>
                                            </div>
                                        </li>
                                        <li class="gfield">
                                            <label for="" class="gfield_label"></label>
                                            <div class="ginput_container">
                                                <input id="footer_password" type="password" placeholder="Password" class="validate"">
                                            </div>
                                        </li>
                                        <li class="gfield">
                                            <a href="#">Forgot password?</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="gform_footer">
                                    <input class="btn-login" type="submit" value="Sign In">
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="div-login">
                        <div class="user">
                            <a href="">Wagner, Renee</a>
                        </div>
                        <a href="" class="dgt-btn">Sign Out</a>
                    </div>
                </li>
                <li class="w-footer-item w-menu">
                    <?php wp_nav_menu(array('theme_location' => 'menu-home', 'menu_class' => 'nav-footer', 'container' => false )); ?>
                    <div class="gpx">
                        <a class="privacy" href="#">Privacy Policy</a>
                        <a class="terms" href="#">Terms & Conditions</a>
                        <p><span>©</span> <span id="id_year"></span> <span>GPX</span></p>
                    </div>
                </li>
            </ul>
        </div>
	</footer>
<?php wp_footer(); ?>
    <!--<script src="js/jquery1.8.3.min.js" type="text/javascript"></script>-->
    <script src="<?php echo get_template_directory_uri(); ?>/js/jquery.min.js" type="text/javascript"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/js/jquery-ui.min.js" type="text/javascript"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/js/jquery.royalslider.custom.min.js" type="text/javascript"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/js/jquery.material.form.min.js" type="text/javascript"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/js/main.js" type="text/javascript"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/js/dataTables.responsive.min.js" type="text/javascript"></script>
</body>
</html>
