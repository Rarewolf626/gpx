<?php
/**
 * Template Name: Result Page
 * Theme: GPX
 */

get_header();
// while ( have_posts() ) : the_post();  

// the_content();
// endwhile;?>

<?php 
if(is_user_logged_in())
{
    $roleCheck = wp_get_current_user();
    if ( in_array( 'gpx_member_-_expired', (array) $roleCheck->roles ) ) {
       ?>
       <script type="text/javascript">
			location.href="/404";
       </script>
       <?php 
       exit;
    }
}
if(isset($lpCookie) && !empty($lpCookie))
{
    $expires = time() + (86400 * 30);
?>
<div class="cookieset" data-name="lp_promo" data-value="<?=$lpCookie?>" data-expires="<?=$expires?>"></div>
<?php 
}
if(isset($savesearch) && is_array($savesearch) && !empty($savesearch['guest-searchSessionID']))
{
    $expires = time() + (86400 * 30);
?>
    <div class="cookieset" data-name="guest-searchSessionID" data-value="<?=$savesearch['guest-searchSessionID']?>" data-expires="<?=$expires?>"></div>
<?php 
}
//check to see if booking is disabled
$bookingDisabeledClass = '';
$bookingDisabledActive = get_option('gpx_booking_disabled_active');
if($bookingDisabledActive == '1') // this is disabled let's get the message and set the class
{
    if(is_user_logged_in())
    {
        $bdUser = wp_get_current_user();
        $role = (array) $bdUser->roles;
        if($role[0] == 'gpx_member')
        {
            $bookingDisabledMessage = get_option('gpx_booking_disabled_msg');
            ?>
            <div id="bookingDisabledMessage" class="booking-disabled-check" data-msg="<?=$bookingDisabledMessage;?>"></div>
            <?php 
            $bookingDisabeledClass = 'booking-disabled';
        }
    }
}
//get the held weeks for this user
$sql = "SELECT * FROM wp_gpxPreHold WHERE user='".$cid."' and released=0";
$holds = $wpdb->get_results($sql);
foreach($holds as $theld)
{
    $held[$theld->weekId] = $theld->weekId;
}
?>       <div class="dgt-container g-w-modal">
            <div class="modal modal-filter dgt-modal" id="modal-filter">
            	<div class="close-modal"><i class="icon-close"></i></div>
            	<div class="w-modal">
            		<form action="">
            			<div class="block">
            				<h2>Sort Results</h2>
            				<?php 
            				?>
            				<label for="select_cities" class="ada-text">Filter City</label>
            				<select id="select_cities" class="dgt-select filter_city" multiple="multiple" data-filter="subregions" name="mySelect" placeholder="All Cities">
            					<?php
            					foreach($filterNames as $filterNameKey => $filterNameValue)
            					{
            					?>
            					<option value="<?=$filterNameKey?>"><?=$filterNameValue?></option>
            					<?php 
            					}
            					?>
            				</select>
            				<label for="select_soonest" class="ada-text">Filter Soonest</label>
            				<select id="select_soonest" class="dgt-select" name="mySelect" placeholder="Date/Soonest to Latest">
            					<option value="1">Date/Soonest to Latest</option>
            					<option value="2">Date/Latest to Soonest</option>
            					<option value="3">Price/Lowest to Hightest</option>
            					<option value="4">Price/Highest to Lowest</option>
            				</select>
            				<h3>- Date Range</h3>
            				<a href="#" class="dgt-btn" id="checkin-btn">Check-In <span class="icon-date"></span></a>
            				<input id="rangepicker" style="display: none">
            			</div>
            			<div class="block">
            				<h2>Filter Results</h2>
            				<h3>- Unit Size (note: currently working on this)</h3>
            				<ul class="list-check">
            				<?php 
            				foreach($allBedrooms as $bkey=>$bval)
            				{
            				?>
            				   <li>
            						<input type="checkbox" class="filter_size" id="chk-<?=$bkey?>" name="addsize[]" value="<?=$bkey?>" data-type="size" data-filter="bedtype" placeholder="Studio">
            						<label for="chk-<?=$bkey?>"><?=$bval?></label>
            					</li>
            				<?php 
            				}
            				?>
            				</ul>
            				<h3>- Type of Week</h3>
            				<ul class="list-check">
            					<li>
            						<input type="checkbox" class="filter_resorttype" id="chk-rental" class="filter_data filter_group" data-type="type" data-filter="resorttype" name="addname[]" value="Rental Week" placeholder="Rental" checked>
            						<label for="chk-rental">Rental</label>
            					</li>
            					<li>
            						<input type="checkbox" class="filter_resorttype" id="chk-exchange" class="filter_data filter_group" data-type="type" data-filter="resorttype" name="addname[]" value="Exchange Week" placeholder="Exchange" checked>
            						<label for="chk-exchange">Exchange</label>
            					</li>
            				</ul>
            				<h3>- Resort Type</h3>
            				<h5 class="aiActive">All Inclusive Resorts - <span id="aiNot"></span>Included in Results</h5>
            				<ul class="list-check">
            					<li>
            						<input type="checkbox" class="filter_ai" id="chk-all-inclusive" name="addai[]" data-type="type" data-filter="resorttype" value="00" placeholder="All-Inclusive Resorts Not Included">
            						<label for="chk-all-inclusive">All-Inclusive Resorts Not Included</label>
            					</li>
            					<li>
            						<input type="checkbox" id="filter_ai_dummy" name="filter_ai_dummy" checked>
            						<label for="filter_ai_dummy">All Inclusive Resorts Included</label>
            					</li>
            				</ul>
            			</div>
            		</form>
            	</div>
            </div>
        </div>
<?php 
if(isset($loginalert))
{
    //$resorts = $featuredresorts;
?>
    <div class="modal dgt-modal modal-alert active-modal" id="modal-alert">
    	<div class="close-modal"><i class="icon-close"></i></div>
    	<div class="w-modal">
    		<div class="icon-alert"></div>
    		<p>These specials are only available to logged in users.  Please <a class="dgt-btn call-modal-login signin" href="#">Sign In</a> to see the promo price.</p>
    	</div>
    </div>
<?php     
}
?> 
    <section class="w-banner w-results w-resulst-reset w-results-home new-style-result-banner">
        <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result rs-col-3">
            <li class="slider-item rsContent"><img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg" alt="" /></li>
        </ul>
        <div class="dgt-container w-box result-page-form">
        <h2 class="hero-main-text">
        	Search Results
        </h2>
             
        </div>
    </section>
    <?php include(locate_template( 'template-parts/universal-search-widget.php' )); ?>
    <section class="w-filter dgt-container">
        <div class="left">
        <?php
        $cntResults = 0;
        if(!empty($rp))
        {
            $cntResults = count($rp);
        }
        elseif(!empty($props))
        {
            $cntResults = count($props);
        }
        ?>
            <h3><?=$cntResults?> Search Results</h3>
            <?php 
            if(isset($returnLink) && !empty($returnLink))
                echo $returnLink;
            ?>
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
			<div id="sticky"><a href="" class="dgt-btn call-modal-filter">Filter Results</a></div>
        </div>
    </section>


<?php get_footer(); ?>
