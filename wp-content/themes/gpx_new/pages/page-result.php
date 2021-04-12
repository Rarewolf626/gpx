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
print_r($held);
?>      
 


<?php get_footer(); ?>
