<!DOCTYPE html>
<html>
    <head>
<?php wp_head(); ?>
<style>
.maintenane-text p {
    color: #fff;
}
</style>
    </head>
    <body class="<?php echo $body_classes ? $body_classes : ''; ?>">
		<?php do_action('wpmm_after_body'); ?>
<div class="cnt-wrapper">
<div class="wrapper">
        <header class="header">
            <div class="top-nav">
                <div class="dgt-container">
                                            <h1>
                            <a href="https://gpxvacations.com" class="w-logo radius" aria-label="GPX">
                                <figure><img src="https://gpxvacations.com/wp-content/themes/gpx_new/images/logo.png" alt="Grand Pacific Exchange | GPX" width="" height=""></figure>
                            </a>
                        </h1>
                                        <a href="#" class="phone noclick" aria-label="phone" onclick="return false;>
                        <i class=" icon-phone"="">
                        <span>(866) 325-6295</span>
                    </a>
                    <div class="access">
                        <a href="tel:8663256295" class="mobile-phone" aria-label="phone">
                            <span>(866) 325-6295</span>
                        </a>
                                            </div>
                </div>
            </div>
            <div class="dgt-container">
			<ul id="top-main-nav-menu" class="nav-list">
</ul>			
</div>
        </header>
<div class="maintenance" style=" text-align: center;  color: #fff; background-image: url('/wp-content/themes/gpx_new/images/beach_bckgrd.jpg');background-position: center bottom;background-size: cover;width: 100%;min-height: calc(100vh - 128px);display: grid;grid-template-columns: 1fr;grid-template-rows: 1fr;">
	<div class="maintenance-text" style="align-self: center; padding: 0 15px;max-width: 1000px;margin: 0px auto;"><?php  echo stripslashes($text)?></div>
</div>
</div>
</div>
    </body>
</html>