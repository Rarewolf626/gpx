<?php
/**
 * @package WordPress DGT
 * @since DGT Alliance 2.0
 */
date_default_timezone_set('America/Los_Angeles');


define( 'GPX_THEME_VERSION', '4.019582' );

require_once 'models/gpxmodel.php';
//$gpx_model = new GPXModel;

if ( ! function_exists( 'gpx_theme_setup' ) ) :
function gpx_theme_setup() {
    
    load_theme_textdomain( 'gpx' );
    
    // Add default posts and comments RSS feed links to head.
    add_theme_support( 'automatic-feed-links' );
    // add_theme_support ( 'post-thumbnails', array* 'wpsl_stores' ) );
    add_theme_support( 'title-tag' );
    
    add_theme_support( 'post-thumbnails' );
    set_post_thumbnail_size( 1200, 9999 );
    
    /*
     * Switch default core markup for search form, comment form, and comments
     * to output valid HTML5.
     */
    add_theme_support( 'html5', array(
        'search-form',
        'gallery',
        'caption',
    ) );
    
    add_theme_support( 'post-formats', array(
        'aside',
        'image',
        'video',
        'quote',
        'link',
        'gallery',
        'status',
        'audio',
        'chat',
    ) );
    
}
endif;
add_action( 'after_setup_theme', 'gpx_theme_setup' );

if ( ! function_exists( 'load_gpx_theme_styles' ) ) {
    /**
     * Load Required CSS Styles
     */
    function load_gpx_theme_styles() {
        // enqueue Main styles
        $css_directory_uri = get_template_directory_uri() . '/css/';
        wp_register_style('jquery-ui', '//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css');
        wp_enqueue_style( 'jquery-ui' );
        wp_register_style('sumoselect', $css_directory_uri. 'sumoselect.css', array(), GPX_THEME_VERSION, 'all' );
        wp_enqueue_style('sumoselect');
        wp_register_style('main', $css_directory_uri . 'main.css', array(), GPX_THEME_VERSION, 'all' );
        wp_enqueue_style('main');
        wp_enqueue_style('fontawesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css');
        if( is_homepage()) :
        wp_register_style('home', $css_directory_uri. 'home.css', array(), GPX_THEME_VERSION, 'all' );
        wp_enqueue_style('home');
        wp_register_style('home', $css_directory_uri. 'home.css', array(), GPX_THEME_VERSION, 'all' );
        wp_enqueue_style('home');
        else:
        wp_register_style('inner', $css_directory_uri. 'inner.css', array(), GPX_THEME_VERSION, 'all' );
        wp_enqueue_style('inner');
        endif;
        
        if( is_page( array( 'view-profile') ) ):
        wp_register_style('data-table', $css_directory_uri . 'jquery.dataTables.min.css', array(), GPX_THEME_VERSION, 'all' );
        wp_enqueue_style('data-table');
        wp_register_style('data-table-responsive', $css_directory_uri . 'dataTables.responsive.css', array(), GPX_THEME_VERSION, 'all' );
        wp_enqueue_style('data-table-responsive');
        endif;
        
        if( is_singular( array( 'offer') ) ):
        wp_register_style('pagex', $css_directory_uri . 'pagex.css', array(), GPX_THEME_VERSION, 'all' );
        endif;
        
        wp_enqueue_style('daterange-picker', $css_directory_uri.'daterange-picker.css', array(), GPX_THEME_VERSION, 'all');
        wp_enqueue_style('slick-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css', array(), GPX_THEME_VERSION, 'all');
        wp_enqueue_style('custom', $css_directory_uri.'custom.css', array(), GPX_THEME_VERSION, 'all');
        wp_enqueue_style('ada', $css_directory_uri.'ada.css', array(), '1.1', 'all');
        wp_enqueue_style('ice', $css_directory_uri.'ice.css', array(), GPX_THEME_VERSION, 'all');
        
        
        
    }
    add_action( 'wp_enqueue_scripts', 'load_gpx_theme_styles' );
}

if ( ! function_exists( 'load_gpx_theme_scripts' ) ) {
    /**
     * Enqueue JavaScripts required for this theme
     */
    function load_gpx_theme_scripts() {
        global $post;
        $js_directory_uri = get_template_directory_uri() . '/js/';
        wp_register_script('jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js', array('jquery'));
        wp_register_script('royalslider', $js_directory_uri . 'jquery.royalslider.custom.min.js', array('jquery'), '9.5.7', true );
        wp_register_script('sumoselect', $js_directory_uri . 'jquery.sumoselect.min.js', array('jquery'), '3.0.2', true );
        wp_register_script('material-form', $js_directory_uri . 'jquery.material.form.min.js', array('jquery'), '1.0', true );
        wp_register_script('main', $js_directory_uri . 'main.js', array( 'jquery' ), GPX_THEME_VERSION, true );
        
        wp_register_script('ada', $js_directory_uri . 'ada.js', array( 'jquery' ), GPX_THEME_VERSION, true );
        wp_register_script('shift4', $js_directory_uri . 'shift4.js', array( 'jquery' ), GPX_THEME_VERSION, true );
        wp_register_script('ice', $js_directory_uri . 'ice.js', array( 'jquery' ), GPX_THEME_VERSION, true );
        
        wp_enqueue_script( 'jquery' );
        if(is_page(97))
            wp_enqueue_script( 'jquery_ui' );
            // 		else
            wp_register_script('gpx_cookies', $js_directory_uri . 'gpx_cookies.js', array( 'jquery' ), GPX_THEME_VERSION, true );
            wp_enqueue_script( 'jquery_ui-core' );
            wp_enqueue_script( 'jquery-ui-autocomplete' );
            wp_enqueue_script( 'royalslider' );
            wp_enqueue_script( 'sumoselect');
            wp_enqueue_script( 'material-form' );
            wp_enqueue_script('javascript_cookie', '//cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.3/js.cookie.min.js', array('material-form'));
            wp_enqueue_script('jquery-tinysort', '//cdnjs.cloudflare.com/ajax/libs/tinysort/2.3.6/tinysort.min.js', array('material-form'));
            wp_enqueue_script('daterange-pickerjs',  $js_directory_uri . 'jquery.daterange-picker.js', array( 'jquery_ui' ), '1.0', true);
            wp_enqueue_script('slick-js',  'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js', array( 'jquery_ui' ), '1.0', true);
            
            wp_enqueue_script( 'main' );
            wp_enqueue_script( 'gpx_cookies' );
            wp_enqueue_script( 'ada' );
            wp_enqueue_script( 'shift4' );
            wp_enqueue_script( 'ice' );
            
            
            $params = array(
                'url_theme'  => get_template_directory_uri(),
                'url_ajax'   => admin_url("admin-ajax.php"),
            );
            
            if( is_homepage()) :
            wp_register_script('scroll-magic', $js_directory_uri . 'ScrollMagic.min.js', array( 'jquery' ), '1.0', true );
            wp_enqueue_script('scroll-magic');
            $params['current'] = 'home';
            else:
            $params['current'] = $post->post_name;
            endif;
            
            wp_localize_script ( 'main','gpx_base' , $params);
            wp_enqueue_script('main');
            
            if( is_page( array( 'view-profile') ) ):
            wp_register_script('data-tables', $js_directory_uri . 'jquery.dataTables.min.js', array( 'jquery' ), '1.10.12', true );
            wp_register_script('data-tables-responsive', $js_directory_uri . 'dataTables.responsive.min.js', array( 'jquery' ), '1.0.0', true);
            wp_enqueue_script( 'data-tables' );
            wp_enqueue_script( 'data-tables-responsive' );
            endif;
    }
    add_action('wp_enqueue_scripts', 'load_gpx_theme_scripts');
    
    
    function onetrust_js_handle( $tag, $handle, $source ) {
        if ( 'gpx_cookies' === $handle ) {
            $tag = '<script type="text/javascript" src="' . $source . '"></script>';
        }
        
        return $tag;
    }
    add_filter( 'script_loader_tag', 'onetrust_js_handle', 10, 3 );
}

function gpr_onetrust_form($params=[])
{
    $inputVars = [
        'data' => '',
    ];
    $atts = shortcode_atts($inputVars,$params);
    extract($atts);
    
    ob_start();
    ?>
		<!-- OneTrust Consent Receipt Start -->
		<script
				  src="https://privacyportal-cdn.onetrust.com/consent-receipt-scripts/scripts/otconsent-1.0.min.js"
				  type="text/javascript"
				  charset="UTF-8"
				  id="consent-receipt-script">
		  triggerId="trigger";
		  identifierId="inputEmail";
		  confirmationId="confirmation";
		  settingsUrl="https://privacyportal-cdn.onetrust.com/consentmanager-settings/408bd2ea-da6b-40bb-8f66-e2fe87cd91f9/<?=$data?>-active.json";
		</script><!-- OneTrust Consent Receipt End -->
		<div class="l-constrained l-padding-m">
             <div id="CcpaConsentPreferences">
        
            </div>
        
        </div>
    <?php
    $output = ob_get_contents();   
    ob_end_clean();   
    return $output;
}
add_shortcode('gpr_onetrust_form', 'gpr_onetrust_form');

function is_homepage() {
	return(is_front_page() || is_home())? true : false;
}


/**
 * Get the post type function for single.php
 */
function get_the_post_type() {
    $post = get_post();
    return ! empty( $post ) ? $post->post_type : false;
}

/*
 * Excerpt lenght in homepage
 */
function dgt_excerpt_length( $length ) {
	return 15;
}
add_filter( 'excerpt_length', 'dgt_excerpt_length', 999 );

/*
 * Ajax load
 */
add_action("wp_ajax_gpx_load_more","gpx_load_more_fn");
add_action("wp_ajax_nopriv_gpx_load_more", "gpx_load_more_fn");

function gpx_load_more_fn() {
	$type_data = $_POST['type'];
	$output = '';
	switch($type_data) {
		case 1:
			ob_start();
			get_template_part( 'template-parts/featured-destinations-home' );
			$output = ob_get_clean();
			break;
		case 2:
			ob_start();
			get_template_part( 'template-parts/result-listing-items' );
			$output = ob_get_clean();
			break;
		default:
			ob_start();
	    get_template_part( 'template-parts/resorts-listing-items' );
			$output = ob_get_clean();
			break;
	}
	echo $output;
	exit();
}

add_action("wp_ajax_gpx_user_login","gpx_user_login_fn");
add_action("wp_ajax_nopriv_gpx_user_login", "gpx_user_login_fn");

function gpx_load_results_page_fn()
{
    header('content-type: application/json; charset=utf-8');

    $country = '';
    $region = '';

    global $wpdb;
    
    $joinedTbl = map_dae_to_vest_properties();
    
    extract($_POST);
    $monthstart = date('Y-m-01', strtotime($select_monthyear));
    $monthend = date('Y-m-t', strtotime($select_monthyear));
    
    $html = '';
    
    $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE RegionID='".$select_location."'";
    $row = $wpdb->get_row($sql);
    $lft = $row->lft+1;
    $sql = "SELECT id, lft, rght FROM wp_gpxRegion
        WHERE lft BETWEEN ".$lft." AND ".$row->rght."
        ORDER BY lft ASC";
    $gpxRegions = $wpdb->get_results($sql);
    
    foreach($gpxRegions as $gpxRegion)
    {
        $regionSet = false;
        $sql = "SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinResort']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                WHERE b.gpxRegionID='".$gpxRegion->id."'
                AND check_in_date BETWEEN '".$monthstart."' AND '".$monthend."'
                ";
        $rows = $wpdb->get_results($sql);
 
        if(!empty($rows))
        {
           $cntResults = count($rows);
           $i = 1;
           foreach($rows as $row) 
           {
               $priceint = preg_replace("/[^0-9\.]/", "",$prop->WeekPrice);
               if($priceint != $row->Price)
                   $row->Price = $priceint;
               $discount = '';
               $specialPrice = '';
               //are there specials?
               $sql = "SELECT a.Properties, a.Amount, a.SpecUsage
			FROM wp_specials a
            LEFT JOIN wp_promo_meta b ON b.specialsID=a.id
            LEFT JOIN wp_resorts c ON c.id=b.foreignID
            LEFT JOIN wp_gpxRegion d ON d.id=b.foreignID
            WHERE ((c.ResortID='".$row->ResortID."' AND b.refTable='wp_resorts')
            OR d.id='".$row->gpxRegionID."'
            OR SpecUsage='customer')
            AND DATE(NOW()) BETWEEN StartDate AND EndDate
            AND c.active=1";
               $specs = $wpdb->get_results($sql);
               if($specs)
                   foreach($specs as $spec)
                   {
                       $specialMeta = stripslashes_deep( json_decode($spec->Properties) );
                       switch ($specialMeta->transactionType)
                       {
                           case 'upsell':
                               /*
                                * todo: add upsell conditions and change this variable
                                */
                               $transactionType = 'Upsell';
                               break;
               
                           case 'All':
                               $transactionType = $prop->WeekType;
                               break;
               
                           default:
                               $transactionType = 'BonusWeek';
                               break;
                       }
                       if($spec->Amount > $discount && $transactionType == $row->WeekType)
                       {
                           $discount = $spec->Amount;
                           $discountType = $specialMeta->promoType;
                           if($discountType == 'Pct Off')
                               $specialPrice = number_format($row->Price*(1-($discount/100)), 2);
                           elseif($discountType == 'Dollar Off')
                               $specialPrice = $row->Price-$discount;
                           elseif($discount < $row->Price)
                               $specialPrice = $discount;
                           if($specialPrice < 0)
                               $specialPrice = '0.00';
                       }
                   }
               
               if(!$regionSet)
               {
                   $html .= '<li class="w-item-view">
                                <div class="view">
                                	<div class="view-cnt">
                                		<img src="'.$row->ImagePath1.'" alt="'.$row->ResortName.'">
                                	</div>
                                	<div class="view-cnt">
                                		<div class="descrip">
                                			<hgroup>
                                				<h2>'.$row->ResortName.'</h2>
                                				<span>'.$row->Country.' / '.$row->Town.', '.$row->Region.'/span>
                                			</hgroup>
                                			<a href="" class="dgt-btn">View Resort</a>
                                		</div>
                                		<div class="w-status">
                                			<div class="close">
                                				<i class="icon-close"></i>
                                			</div>
                                			<div class="result">
                                				<span class="count-result" >'.$cntResults.' Results for</span>
                                				<span class="date-result" >'.date('F', strtotime($monthstart)).' '.date('Y', strtotime($monthstart)).'</span>
                                			</div>
                                				    <!--
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
                                				    -->
                                		</div>
                                	</div>
                                </div>
                                <ul id="gpx-listing-result" class="w-list-result">';
               }
               $html .= '<li class="item-result';
               if(!empty($specialPrice))
                   $html .= ' active';
               $html .= '">
                            	<div class="w-cnt-result">
                            		<div class="result-head">
                            			';
               $pricesplit = explode(" ", $row->WeekPrice);
               $nopriceint = str_replace($priceint, "", $prop->WeekPrice);
               if(empty($specialPrice))
                   $html .= '<p><strong>'.$pricesplit[1].'</strong></p>';
               else
               {
                   $html .= '<p class="mach"><strong>'.$pricesplit[1].'</strong></p>';
                   $html .= '<p class="now">Now <strong>'.$nopriceint.$specialPrice.'</strong></p>';
               }
               $html  .= '              <ul class="status">
                            				<li>
                            					<div class="status-'.$row->WeekType.'"></div>
                            				</li>
                            			</ul>
                            		</div>
                            		<div class="cnt">
                            			<p><strong>'.$row->WeekType.'</strong></p>
                            			<p>Check-In '.$row->checkIn.'</p>
                            			<p>'.$row->noNights.' Nights</p>
                            			<p>Size '.$row->Size.'</p>
                            		</div>
                            		<div class="list-button">
                            			<a href="" class="dgt-btn hold-btn" data-propertiesID="'.$row->id.'">Hold</a>
                            			<a href="" class="dgt-btn active book-btn" data-propertiesID="'.$row->id.'">Book</a>
                            		</div>
                            	</div>
                            </li>';
               if($i == $cntResults)
                   $html .= '</ul></li>';
               $i++;
               $regionSet = true;
           }
        }
    }
   
    $output = array('html'=>$html);

    echo wp_send_json($output);
    exit();
}
add_action("wp_ajax_gpx_load_results_page_fn","gpx_load_results_page_fn");
add_action("wp_ajax_nopriv_gpx_load_results_page_fn", "gpx_load_results_page_fn");

function update_username()
{
    global $wpdb;
    $data = [];
    
    if(isset($_POST['modal_username']))
    {
        $pw1 = $_POST['user_pass'];
        $pw2 = $_POST['user_pass_repeat'];
        $username = sanitize_text_field($_POST['modal_username']);
        if(isset($_POST['wh']))
        {
            $userID = reset(
                get_users(
                    array(
                        'meta_key' => 'gpx_upl_hash',
                        'meta_value' => $_POST['wh'],
                        'number' => 1,
                        'count_total' => false,
                        'fields' => 'ids',
                    )
                    )
                );
            
            if(empty($userID))
            {
                $data['msg'] = 'Invalid Request.  Please contact us to create your account.';
            }
           
        }
        else
        {
            $userID = get_current_user_id();
        }
        
        if(is_email($username))
        {
            $data['msg'] = 'Please choose a unique username that is not an email address.';
        }
        if($pw1 != $pw2)
        {
            $data['msg'] = 'Passwords do not match!';
        }
        elseif(username_exists($username))
        { 
            //is this their account?
            
            $data['msg'] = 'That username is already in use.  Please choose a different username.';
        }
        if(empty($data))
        {
            $up = wp_set_password($pw1, $userID);
            
            
            $wpdb->update('wp_users', array('user_login'=>$username), array('ID'=>$userID));
            update_user_meta($userID, 'gpx_upl', '1');
            
            $wpdb->update('wp_GPR_Owner_ID__c', array('welcome_email_sent'=>1), array('user_id'=>$userID));
            $data['success'] = true;
            $data['msg'] = 'Updated';
        }
    }
    
    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_update_username', 'update_username');
add_action('wp_ajax_nopriv_update_username', 'update_username');

function gpx_user_login_fn() {
	header('content-type: application/json; charset=utf-8');
	header("access-control-allow-origin: *");
	global $wpdb;
	$credentials = array();
	if(isset($_POST['user_email']))
	{
	    $userlogin = $_POST['user_email'];
	}
	elseif(isset($_POST['user_email_footer']))
	{
	    $userlogin = $_POST['user_email_footer'];
	}
	if(isset($_POST['user_pass']))
	{
	    $userpassword = $_POST['user_pass'];
	}
	elseif(isset($_POST['user_pass_footer']))
	{
	    $userpassword = $_POST['user_pass_footer'];
	}
	    
	$credentials['user_login'] = isset($userlogin) ? trim($userlogin) : '';
	$credentials['user_password'] = isset($userpassword) ? trim($userpassword) : '';
	$credentials['remember'] = "forever";
	
	
	$redirect = trim($_POST['redirect_to']);
	$user_signon = wp_signon($credentials, true);
	status_header(200);
	if (is_wp_error($user_signon)) {
		$user_signon_response = array(
			'loggedin' => false,
			'message' => 'Wrong username or password.'
		);
	} else {
	    $userid = $user_signon->ID;
	    $userroles = (array) $user_signon->roles;
	    
	    $changed = '1';
	   
	    if(in_array('gpx_member', $userroles))
	    {
	        //only owners with an interval should login
	        $sql = "SELECT id FROM wp_GPR_Owner_ID__c WHERE user_id='".$userid."'";
	        $interval = $wpdb->get_row($sql);
	        
	        if(empty($interval))
	        {
// 	            $msg = "This website is for testing purposes only.  You will be redirected to the production website.";
// 	            $redirect = 'https://gpxvacations.com';
	            
// 	            $user_signon_response = array(
// 	                'loggedin' => true,
// 	                'redirect_to' => $redirect,
// 	                'message' => $msg,
// 	            );
// 	            wp_destroy_current_session();
// 	            wp_clear_auth_cookie();
// 	            wp_set_current_user( 0 );
// 	            status_header(200);
	        }
	        else
	        {
	            if($userpassword != 'vesttest1')
	            {
// 	                $msg = "This website is for testing purposes only.  You will be redirected to the production website.";
// 	                $redirect = 'https://gpxvacations.com';
	                
// 	                $user_signon_response = array(
// 	                    'loggedin' => true,
// 	                    'redirect_to' => $redirect,
// 	                    'message' => $msg,
// 	                );
// 	                wp_destroy_current_session();
// 	                wp_clear_auth_cookie();
// 	                wp_set_current_user( 0 );
// 	                status_header(200);
	            }
	        }
	        
	        if(isset($user_signon_response))
	        {
	            echo wp_send_json($user_signon_response);
	            exit();
	        }
	        
	        $changed = 0;
	        
	        $changed = get_user_meta($userid, 'gpx_upl');
	        if(empty($changed))
	        {
	            $changed = '';
	        }
// 	        echo '<pre>'.print_r($changed, true).'</pre>';
	        
	    }
// 	    echo '<pre>'.print_r($changed, true).'</pre>';
	    if(!empty($changed))
	    {
	        $msg =  'Login sucessful, redirecting...';
	    }
	    else 
	    {
	        $msg = 'Update Username!';
	        $redirect = 'username_modal';
	    }
		$user_signon_response = array(
			'loggedin' => true,
			'redirect_to' => $redirect,
			'message' => $msg,
		);
	}
	echo wp_send_json($user_signon_response);
 exit();
}
function gpx_pw_reset_fn() {
	header('content-type: application/json; charset=utf-8');
	header("access-control-allow-origin: *");
	$credentials = array();
	if(isset($_POST['user_email']))
	{
	    $userlogin = $_POST['user_email'];
	}
	if(isset($_POST['user_login']))
	{
	    $userlogin = $_POST['user_login'];
	}
	if(isset($_POST['user_login_pwreset']))
	{
	    $userlogin = $_POST['user_login_pwreset'];
	}
	$credentials['user_login'] = isset($userlogin) ? trim($userlogin) : '';
	$user_signon = wp_signon($credentials, true);
	$pwreset = retrieve_password();
	status_header(200);
	if (is_wp_error($pwreset)) {
		$user_signon_response = array(
			'loggedin' => false,
			'message' => 'Wrong username or password.'
		);
	} else {
		$user_signon_response = array(
			'loggedin' => true,
			'message' => 'Please check your email for a password reset link.',
		);
	}
	echo wp_send_json($user_signon_response);
 exit();
}

add_action("wp_ajax_gpx_pw_reset","gpx_pw_reset_fn");
add_action("wp_ajax_nopriv_gpx_pw_reset", "gpx_pw_reset_fn");

function gpx_autocomplete_location_sub_fn() {
    global $wpdb;


    header('content-type: application/json; charset=utf-8');

    $term = '';
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    $region = '';
    $region = (!empty($_GET['region']))? sanitize_text_field($_GET['region']) : '';


    if(!empty($region))
    {
        $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE name = '".$region."'";
        $rows = $wpdb->get_results($sql);
        foreach($rows as $row)
        {
            $sql = "SELECT DISTINCT name, subName from wp_gpxRegion WHERE lft > '".$row->lft."' AND rght < '".$row->rght."' and ddHidden = '0'";
            $cities = $wpdb->get_results($sql);
            foreach($cities as $city)
            {
                if(!empty(trim($city->subName)))
                    $locations[] .= $city->subName;
                    else
                        $locations[] .= $city->name;
            }
        }
         
    }
    else
    {
        $locations = array( 'Mexico', 'Caribbean' );
        if(empty($term))
            $where = "featured = '1'";
            else
                $where = "name != 'All'";
                 
                $sql = "SELECT DISTINCT name, subName FROM wp_gpxRegion WHERE ddHidden = '0' AND ".$where;
                $regions = $wpdb->get_results($sql);
                foreach($regions as $region)
                {
                    $locations[] = $region->name;
                    if(isset($region->subName) && !empty(trim($region->subName)))
                        $locations[] .= $region->subName;
                }
                 
                if(!empty($term))
                {
                    $sql = "SELECT country as name FROM wp_gpxCategory";
                    $countries = $wpdb->get_results($sql);
                    foreach($countries as $country)
                    {
                        if($country->name == 'USA')
                            continue;
                            $locations[] = $country->name;
                    }
                }
    }
    sort($locations);

    // 	$toplocations = array( 'USA' );
    // 	foreach($toplocations as $tl)
        // 	{
        // 	    $key = array_search($tl, $locations);
        //         $temp = $locations[$key];
        //         unset($locations[$key]);
        //         array_unshift($locations, $temp);
        // 	}

        //$locations = array('USA', 'Aklan') + $locations;

        $location_search = array();
        if(!empty($term)){
            foreach($locations as $item){
                $pos = strpos(strtolower($item), strtolower($term));
                if ($pos !== false) {
                    $location_search[] = $item;
                }
            }
            $locations = $location_search;
        }
        echo wp_send_json($locations);
        exit();
}
add_action("wp_ajax_gpx_autocomplete_location_sub","gpx_autocomplete_location_sub_fn");
add_action("wp_ajax_nopriv_gpx_autocomplete_location_sub", "gpx_autocomplete_location_sub_fn");

function gpx_autocomplete_location_resort_fn() {
    global $wpdb;
    
    header('content-type: application/json; charset=utf-8');
    
    $resort = array();
    
    $locations = array( 'Mexico', 'Caribbean' );
    
    $term = '';
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';
    
    $region = '';
    $region = (!empty($_GET['region']))? sanitize_text_field($_GET['region']) : '';
    
    
    if(!empty($region))
    {
        $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE name = '".$region."'";
        $rows = $wpdb->get_results($sql);
        foreach($rows as $row)
        {
            $sql = "SELECT gpxRegionID, ResortName from wp_gpxRegion a
                    INNER JOIN wp_resorts b ON a.id=b.gpxRegionID
                    WHERE lft  BETWEEN '".$row->lft."' AND '".$row->rght."' and ddHidden = '0'";
            $cities = $wpdb->get_results($sql);
            
            foreach($cities as $city)
            {
                $resorts[$city->gpxRegionID] = $city->ResortName;
            }
        }
        
    }
    else
    {
        $sql = "SELECT ResortName FROM wp_resorts";
        $results = $wpdb->get_results($sql);
        
        foreach($results as $result)
        {
            $resorts[] = $result->ResortName;
        }
    }
    sort($resorts);
    
    // 	$toplocations = array( 'USA' );
    // 	foreach($toplocations as $tl)
    // 	{
    // 	    $key = array_search($tl, $locations);
    //         $temp = $locations[$key];
    //         unset($locations[$key]);
    //         array_unshift($locations, $temp);
    // 	}
    
    //$locations = array('USA', 'Aklan') + $locations;
    
    $resorts_search = array();
    if(!empty($term)){
        foreach($resorts as $item){
            $pos = strpos(strtolower($item), strtolower($term));
            if ($pos !== false) {
                $resorts_search[] = $item;
            }
        }
        $resorts = $resorts_search;
    }
    echo wp_send_json($resorts);
    exit();
}
add_action("wp_ajax_gpx_autocomplete_location_resort","gpx_autocomplete_location_resort_fn");
add_action("wp_ajax_nopriv_gpx_autocomplete_location_resort", "gpx_autocomplete_location_resort_fn");

function gpx_autocomplete_sr_location() {
    global $wpdb;
    
    
    header('content-type: application/json; charset=utf-8');
    
    //$locations = array( 'Mexico', 'Caribbean' );
    
    $term = '';
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';
    
    if(empty($term))
    {
        $where = "featured = '1'";
    }
    else
    {
        $where = "name != 'All'";
    }
    $sql = "SELECT DISTINCT name, subName, displayName FROM wp_gpxRegion WHERE ddHidden = '0' AND ".$where;
    
    $regions = $wpdb->get_results($sql);
    foreach($regions as $region)
    {
        
        
        if(isset($region->displayName) && !empty(trim($region->displayName)))
        {
            $regionLocations[] = $region->displayName;
        }
        elseif(isset($region->subName) && !empty(trim($region->subName)))
        {
            $regionLocations[] = $region->subName;
        }
        else
        {
            $regionLocations[] = $region->name;
        }
        
    }
    
    if(!empty($term))
    {
        //get the regions...
        $sql = "SELECT country as name FROM wp_gpxCategory";
        $countries = $wpdb->get_results($sql);
        foreach($countries as $country)
        {
            if($country->name == 'USA')
                continue;
                $regionLocations[] = $country->name;
        }
        
        //get the resorts...
        $sql = "SELECT ResortName FROM wp_resorts";
        $results = $wpdb->get_results($sql);
        
        foreach($results as $result)
        {
            $resortLocations[] = $result->ResortName;
        }
    }
    
    
    
    sort($regionLocations);
    sort($resortLocations);
    foreach($regionLocations as  $loc)
    {
        $locations[] = [
            'category' => 'REGION',
            'label' => $loc,
            'value' => $loc,
        ];
    }
    foreach($resortLocations as  $loc)
    {
        $locations[] = [
            'category' => 'RESORT',
            'label' => $loc,
            'value' => $loc,
        ];
    }
    
    // 	$toplocations = array( 'Mexico', 'USA' );
    // 	foreach($toplocations as $tl)
    // 	{
    // 	    $key = array_search($tl, $locations);
    //         $temp = $locations[$key];
    //         unset($locations[$key]);
    //         array_unshift($locations, $temp);
    // 	}
    
    //$locations = array('USA', 'Aklan') + $locations;
    
    $search = array();
    if(!empty($term)){
        
        foreach($regionLocations as $item){
            $pos = strpos(strtolower($item), strtolower($term));
            if ($pos !== false) {
                $search[] = [
                    'category' => 'REGION',
                    'label' => $item,
                    'value' => $item,
                ];
            }
        }
        
//         foreach($resortLocations as $item){
//             $pos = strpos(strtolower($item), strtolower($term));
//             if ($pos !== false) {
//                 $search[] = [
//                     'category' => 'RESORT',
//                     'label' => $item,
//                     'value' => $item,
//                 ];
//             }
//         }
        $locations = $search;
    }
    echo wp_send_json($locations);
    exit();
}
add_action("wp_ajax_gpx_autocomplete_sr_location","gpx_autocomplete_sr_location");
add_action("wp_ajax_nopriv_gpx_autocomplete_sr_location", "gpx_autocomplete_sr_location");

function gpx_autocomplete_location_fn() {
    global $wpdb;
    
    
    header('content-type: application/json; charset=utf-8');
    
    //$locations = array( 'Mexico', 'Caribbean' );
    
    $term = '';
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';
    
    if(empty($term))
    {
        $where = "featured = '1'";
    }
    else
    {
        $where = "name != 'All'";
    }
    $sql = "SELECT DISTINCT name, subName, displayName FROM wp_gpxRegion WHERE ddHidden = '0' AND ".$where;
    
    $regions = $wpdb->get_results($sql);
    foreach($regions as $region)
    {
        
        
        if(isset($region->displayName) && !empty(trim($region->displayName)))
        {
            $regionLocations[] = $region->displayName;
        }
        elseif(isset($region->subName) && !empty(trim($region->subName)))
        {
            $regionLocations[] = $region->subName;
        }
        else
        {
            $regionLocations[] = $region->name;
        }
        
    }
    
    if(!empty($term))
    {
        //get the regions...
        $sql = "SELECT country as name FROM wp_gpxCategory";
        $countries = $wpdb->get_results($sql);
        foreach($countries as $country)
        {
            if($country->name == 'USA')
                continue;
                $regionLocations[] = $country->name;
        }
        
        //get the resorts...
        $sql = "SELECT ResortName FROM wp_resorts WHERE active=1";
        $results = $wpdb->get_results($sql);
        
        foreach($results as $result)
        {
            $resortLocations[] = $result->ResortName;
        }
    }
    
    
    
    sort($regionLocations);
    sort($resortLocations);
    foreach($regionLocations as  $loc)
    {
        $locations[] = [
            'category' => 'REGION',
            'label' => $loc,
            'value' => $loc,
        ];
    }
    foreach($resortLocations as  $loc)
    {
        $locations[] = [
            'category' => 'RESORT',
            'label' => $loc,
            'value' => $loc,
        ];
    }
    
    // 	$toplocations = array( 'Mexico', 'USA' );
    // 	foreach($toplocations as $tl)
    // 	{
    // 	    $key = array_search($tl, $locations);
    //         $temp = $locations[$key];
    //         unset($locations[$key]);
    //         array_unshift($locations, $temp);
    // 	}
    
    //$locations = array('USA', 'Aklan') + $locations;
    
    $search = array();
    if(!empty($term)){
        
        foreach($regionLocations as $item){
            $pos = strpos(strtolower($item), strtolower($term));
            if ($pos !== false) {
                $search[] = [
                    'category' => 'REGION',
                    'label' => $item,
                    'value' => $item,
                ];
            }
        }
        
        foreach($resortLocations as $item){
            $pos = strpos(strtolower($item), strtolower($term));
            if ($pos !== false) {
                $search[] = [
                    'category' => 'RESORT',
                    'label' => $item,
                    'value' => $item,
                ];
            }
        }
        $locations = $search;
    }
    echo wp_send_json($locations);
    exit();
}
add_action("wp_ajax_gpx_autocomplete_location","gpx_autocomplete_location_fn");
add_action("wp_ajax_nopriv_gpx_autocomplete_location", "gpx_autocomplete_location_fn");

function gpx_autocomplete_usw_fn() {
    global $wpdb;
    
    
    header('content-type: application/json; charset=utf-8');
    
    //$locations = array( 'Mexico', 'Caribbean' );
    
    $term = '';
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';
    
    if(empty($term))
    {
        $where = "featured = '1'";
    }
    else
    {
        $where = "name != 'All'";
    }
    $sql = "SELECT DISTINCT name, subName, displayName FROM wp_gpxRegion WHERE ddHidden = '0' AND ".$where;
    
    $regions = $wpdb->get_results($sql);
    foreach($regions as $region)
    {
        
        
        if(isset($region->displayName) && !empty(trim($region->displayName)))
        {
            $regionLocations[] = $region->displayName;
        }
        elseif(isset($region->subName) && !empty(trim($region->subName)))
        {
            $regionLocations[] = $region->subName;
        }
        else
        {
            $regionLocations[] = $region->name;
        }
        
    }
    
    if(!empty($term))
    {
        //get the regions...
        $sql = "SELECT country as name FROM wp_gpxCategory";
        $countries = $wpdb->get_results($sql);
        foreach($countries as $country)
        {
            if($country->name == 'USA')
                continue;
                $regionLocations[] = $country->name;
        }
        
        //get the resorts...
        $sql = "SELECT ResortName FROM wp_resorts WHERE active='1'";
        $results = $wpdb->get_results($sql);
        
        foreach($results as $result)
        {
            $resortLocations[] = $result->ResortName;
        }
    }
    
    
    
    sort($regionLocations);
    sort($resortLocations);
    foreach($regionLocations as  $loc)
    {
        $locations[] = [
            'category' => 'REGION',
            'label' => $loc,
            'value' => $loc,
        ];
    }
    foreach($resortLocations as  $loc)
    {
        $locations[] = [
            'category' => 'RESORT',
            'label' => $loc,
            'value' => $loc,
        ];
    }
    
    // 	$toplocations = array( 'Mexico', 'USA' );
    // 	foreach($toplocations as $tl)
    // 	{
    // 	    $key = array_search($tl, $locations);
    //         $temp = $locations[$key];
    //         unset($locations[$key]);
    //         array_unshift($locations, $temp);
    // 	}
    
    //$locations = array('USA', 'Aklan') + $locations;
    
    $search = array();
    if(!empty($term)){
        
        foreach($regionLocations as $item){
            $pos = strpos(strtolower($item), strtolower($term));
            if ($pos !== false) {
                $search[] = [
                    'category' => 'REGION',
                    'label' => $item,
                    'value' => $item,
                ];
            }
        }
        
        foreach($resortLocations as $item){
            $pos = strpos(strtolower($item), strtolower($term));
            if ($pos !== false) {
                $search[] = [
                    'category' => 'RESORT',
                    'label' => $item,
                    'value' => $item,
                ];
            }
        }
        $locations = $search;
    }
    
    echo wp_send_json($locations);
    exit();
}
add_action("wp_ajax_gpx_autocomplete_usw","gpx_autocomplete_usw_fn");
add_action("wp_ajax_nopriv_gpx_autocomplete_usw", "gpx_autocomplete_usw_fn");
/*
 * page loading shortcodes
 * 
 * 
 */

function gpx_get_location_coordinates_fn(){
   global $wpdb;
   
   $return = array();
   
   $sql = "SELECT lng, lat FROM wp_gpxRegion WHERE (name='".$_POST['region']."' OR displayName='".$_POST['region']."')";
   $row = $wpdb->get_row($sql);
   
   if($row->lng != '0' && $row->lat != '0')
   {
       $return['success'] = true;
   }
   
   echo wp_send_json($return);
   exit();
}
add_action("wp_ajax_gpx_get_location_coordinates","gpx_get_location_coordinates_fn");
add_action("wp_ajax_nopriv_gpx_get_location_coordinates", "gpx_get_location_coordinates_fn");

function gpx_booking_path_sc($atts)
{
    global $wpdb;

    $atts = shortcode_atts(
        array(
            'terms' => '',
        ), $atts, 'gpx_booking_path' );
    
    $cid = get_current_user_id();
    
    
    if(isset($_COOKIE['switchuser']))
        $cid = $_COOKIE['switchuser'];
    
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    
    $sql = "SELECT SUM(credit_amount) AS total_credit_amount, SUM(credit_used) AS total_credit_used FROM wp_credit WHERE owner_id IN (SELECT gpx_user_id FROM wp_mapuser2oid WHERE gpx_user_id='".$cid."') AND (credit_expiration_date IS NULL OR credit_expiration_date >'".date('Y-m-d')."')";
    $credit = $wpdb->get_row($sql);

    $credits = $credit->total_credit_amount - $credit->total_credit_used;
    
    
    $sql = "SELECT *  FROM `wp_GPR_Owner_ID__c` WHERE `user_id` = '".$cid."'";
    $gprOwner = $wpdb->get_row($sql);
    
    $sql = "SELECT *  FROM `wp_mapuser2oid` WHERE `gpx_user_id` = '".$cid."'";
    $wp_mapuser2oid = $gpx->GetMappedOwnerByCID($cid);
    
    $memberNumber = '';
    
    if(!empty($wp_mapuser2oid))
    {
        $memberNumber = $wp_mapuser2oid->gpr_oid;
    }
    
    $sql = "SELECT a.*, b.ResortName, c.deposit_year FROM wp_owner_interval a
            INNER JOIN wp_resorts b ON b.gprID LIKE CONCAT(BINARY a.resortID, '%')
            LEFT JOIN (SELECT MAX(deposit_year) as deposit_year, interval_number FROM wp_credit WHERE status != 'Pending' GROUP BY interval_number) c ON c.interval_number=a.contractID
            WHERE a.Contract_Status__c != 'Cancelled'
                AND a.ownerID IN
                (SELECT gpr_oid
                    FROM wp_mapuser2oid
                    WHERE gpx_user_id IN
                        (SELECT gpx_user_id
                        FROM wp_mapuser2oid
                        WHERE gpr_oid='".$memberNumber."'))";
    $ownerships = $wpdb->get_results($sql, ARRAY_A);

    //Rule is # of Ownerships  (i.e. � have 2 weeks, can have account go to negative 2, one per week)
    $newcredit = (($credits) - 1) * -1;
    
    
    if($newcredit > count($ownerships))
    {
        $errorMessage = 'Please deposit a week to continue.';
        
    }
    
    $current_user_fr = wp_get_current_user();
    $roles = $current_user_fr->roles;
    $role = array_shift( $roles );
    
    if(isset($_COOKIE['switchuser']))
        $cid = $_COOKIE['switchuser'];
    
    if(isset($cid) && !empty($cid))
    {
        $user = get_userdata($cid);
        if(isset($user) && !empty($user))
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
        
        $book = $_GET['book'];
        //get the property and resort
        $property_details = get_property_details($book, $cid);
        
        if(isset($property_details['error']))
        {
            $property_error = true;
        }
        else 
        {
            extract($property_details);
        
            if(!isset($_COOKIE['gpx-cart']))
            {
                if(isset($prop->weekId))
                    $propWeekId = $prop->weekId;
                else 
                    $propWeekId = mt_rand(100000,999999);
                $cookie = array('name'=>'gpx-cart','value'=>$cid."-".$propWeekId, 'expires'=>'30',  'path'=>'/', 'site'=>site_url());
                include('templates/js-set-cookie.php');
                $_COOKIE['gpx-cart'] = $cid."-".$propWeekId;
            }
//             $profilecols[0] = array(
//                 array('placeholder'=>"Title", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>''), 'required'=>''),
//                 array('placeholder'=>"First Name", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'FirstName1'), 'required'=>'required'),
//                 array('placeholder'=>"Last Name", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'LastName1'), 'required'=>'required'),
//                 array('placeholder'=>"Email", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'email'), 'required'=>'required'),
//                 array('placeholder'=>"Home Phone", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'phone'), 'required'=>'required'),
//                 array('placeholder'=>"Mobile Phone", 'class'=>'', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Mobile'), 'required'=>''),
//                 array('placeholder'=>"Special Request", 'class'=>'', 'value'=>array('from'=>'usermeta', 'retrieve'=>''), 'required'=>'', 'textarea'=>true),
//             );
            $profilecols[0] = array(
                array('placeholder'=>"First Name", 'type'=>'text', 'class'=>'validate', 'value'=>array( 'name'=>'FirstName1', 'from'=>'usermeta', 'retrieve'=>'SPI_First_Name__c'), 'required'=>'required'),
                array('placeholder'=>"Last Name", 'type'=>'text', 'class'=>'validate', 'value'=>array('name'=>'LastName1', 'from'=>'usermeta', 'retrieve'=>'SPI_Last_Name__c'), 'required'=>'required'),
                array('placeholder'=>"Email", 'type'=>'email', 'class'=>'validate', 'value'=>array('name'=>'email', 'from'=>'usermeta', 'retrieve'=>'SPI_Email__c'), 'required'=>'required'),
                array('placeholder'=>"Phone", 'type'=>'tel', 'class'=>'validate', 'value'=>array('name'=>'phone', 'from'=>'usermeta', 'retrieve'=>'SPI_Home_Phone__c'), 'required'=>'required'),
//                 array('placeholder'=>"Mobile Phone", 'class'=>'', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Mobile'), 'required'=>''),
//                 array('placeholder'=>"Special Request", 'class'=>'', 'value'=>array('from'=>'usermeta', 'retrieve'=>''), 'required'=>'', 'textarea'=>true),
            );
            $profilecols[1] = array(
//                 array('placeholder'=>"Street Address", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Address1'), 'required'=>'required'),
//                 array('placeholder'=>"City", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Address3'), 'required'=>'required'),
//                 array('placeholder'=>"State", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Address4'), 'required'=>'required'),
//                 array('placeholder'=>"Zip", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'PostCode'), 'required'=>'required'),
//                 array('placeholder'=>"Country", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Address5'), 'required'=>'required'),
                array('placeholder'=>"Adults", 'type'=>'text', 'class'=>'validate validate-int', 'value'=>array('from'=>'usermeta', 'retrieve'=>'adults'), 'required'=>'required'),
                array('placeholder'=>"Children", 'type'=>'text', 'class'=>'validate validate-int', 'value'=>array('from'=>'usermeta', 'retrieve'=>'children'), 'required'=>'required'),
                array('placeholder'=>"Special Request", 'class'=>'', 'value'=>array('from'=>'usermeta', 'retrieve'=>''), 'required'=>'', 'textarea'=>true),
            );
            
            $user = get_userdata($cid);
            if(isset($user) && !empty($user))
                $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
            
                $mapMissing = [
                    'SPI_First_Name__c'=>'first_name',
                    'SPI_Last_Name__c'=>'last_name',
                    'SPI_Email__c'=>'Email',
                    'SPI_Home_Phone__c'=>'DayPhone',
                ];
               foreach($mapMissing as $mapKey=>$mapValue)
               {
                   if(!isset($usermeta->$mapKey) && isset($usermeta->$mapValue))
                   {
                       $usermeta->$mapKey = $usermeta->$mapValue;
                   }
               }
            $savesearch = save_search($usermeta, '', 'select', '', $property_details);
        } 
    }
    
    include('templates/sc-booking-path.php');
}
add_shortcode('gpx_booking_path', 'gpx_booking_path_sc');



function gpx_booking_path_payment_sc($atts)
{
    global $wpdb;
  
    $atts = shortcode_atts(
        array(
            'terms' => '',
        ), $atts, 'gpx_booking_path_payment' );
    
    $cid = get_current_user_id();
    
    if(isset($_COOKIE['switchuser']))
        $cid = $_COOKIE['switchuser'];    
    
    if(!isset($_COOKIE['gpx-cart']))
        include('templates/sc-booking-path-payment-empty.php');
    else 
    {
        $regularcheckout = true;
        //is this a simple checkout?
        $sql = "SELECT weekId, propertyID, data FROM wp_cart WHERE cartID='".$_COOKIE['gpx-cart']."'";
        $results = $wpdb->get_results($sql);
       
        if(!empty($results))
        {
            $scNotSkip = false;
            foreach($results as $result)
            {
                if($result->propertyID > 0)
                {
                    //this cart has actual properties in it -- skip simple checkout
                    $scNotSkip = false;
                    $regularcheckout = true;
                    break;
                }
                else 
                {
                    $scNotSkip = true;
                    $row = $result;
                }
            }
            if($scNotSkip && (empty($row->propertyID) || $row->propertyID == '0'))
            {
                $regularcheckout = false;
                
                $data = json_decode($row->data);

                if($data->type == 'late_deposit_fee')
                {
                    $LateDepositFeeAmount = $data->fee;
                }
                if($data->type == 'extension')
                {
                    $extensionFee = $data->fee;
                }
                if($data->type == 'guest')
                {
                    $GuestFeeAmount = $data->fee;
                }
 
                $checkoutItem = $data->type;
                
                $checkoutAmount = $data->fee;
                
                if(isset($data->occoupon))
                {
                    $sql = "SELECT *, a.id as cid, b.id as aid, c.id as oid FROM wp_gpxOwnerCreditCoupon a
                                        INNER JOIN wp_gpxOwnerCreditCoupon_activity b ON b.couponID=a.id
                                        INNER JOIN wp_gpxOwnerCreditCoupon_owner c ON c.couponID=a.id
                                        WHERE a.id IN ('".implode("', '", $data->occoupon)."') AND a.active=1 and c.ownerID='".$cid."'";
                    $occoupons = $wpdb->get_results($sql);
                    if(!empty($occoupons))
                    {
                        foreach($occoupons as $occoupon)
                        {
                            $distinctCoupon = $occoupon;
                            $distinctOwner[$occoupon->oid] = $occoupon;
                            $distinctActivity[$occoupon->aid] = $occoupon;
                        }
                        
                        //get the balance and activity for data
                        foreach($distinctActivity as $activity)
                        {
                            if($activity->activity == 'transaction')
                            {
                                $actredeemed[] = $activity->amount;
                            }
                            else
                            {
                                $actamount[] = $activity->amount;
                            }
                        }
                        if($distinctCoupon->single_use == 1 && array_sum($actredeemed) > 0)
                        {
                            $balance = 0;
                        }
                        else
                        {
                            $balance = array_sum($actamount) - array_sum($actredeemed);
//                             if(isset($indCartOCCreditUsed))
//                             {
//                                 $balance = $balance - array_sum($indCartOCCreditUsed);
//                             }
                        }
                        //if we have a balance at this point the the coupon is good
                        if($balance > 0)
                        {
//                             if(get_current_user_id() == 5)
//                             {
//                                 echo '<pre>'.print_r("balance:".$balance, true).'</pre>';
//                                 echo '<pre>'.print_r("checkout:".$checkoutAmount, true).'</pre>';
//                             }
                            //                                                 echo '<pre>'.print_r($indPrice[$book], true).'</pre>';
                            if($balance <= $checkoutAmount)
                            {
                                $checkoutAmount = $checkoutAmount - $balance;
//                                 $indPrice[$book] = $indPrice[$book] - $balance;
                                $indCartOCCreditUsed[$book] = $balance;
                                $couponDiscount = array_sum($indCartOCCreditUsed);
                            }
                            else
                            {
                                $indCartOCCreditUsed[$book] = $checkoutAmount;
                                $couponDiscount = $checkoutAmount;
                                $checkoutAmount = 0;
                            }
//                             else
//                             {
//                                 $indCartOCCreditUsed[$book] = $checkoutAmount;
//                                 $indPrice[$book] = 0;
// //                                 $finalPrice = $finalPrice - $indCartOCCreditUsed[$book];
//                             }
                        }
                    }
                }
            }
            
        }
        if($regularcheckout)
        {
            //get the details from gpxmodel    
            $checkoutData = get_property_details_checkout($cid);
            extract($checkoutData);
           
            include('templates/sc-booking-path-payment.php');
        }
        else 
        {
            include('templates/sc-booking-path-payment-simple-checkout.php');
        }
        
    }
}
add_shortcode('gpx_booking_path_payment', 'gpx_booking_path_payment_sc');

function gpx_booking_path_confirmation_cs()
{
    global $wpdb;  
    
    $cid = get_current_user_id();
    $cartID = '';
    if(isset($_COOKIE['switchuser']))
        $cid = $_COOKIE['switchuser'];
    if(isset($_GET['confirmation']))
        $cartID = $_GET['confirmation'];
    elseif(isset($_COOKIE['gpx-cart']))
        $cartID = $_COOKIE['gpx-cart'];
    $rows = '';
    if(!empty($cartID));
    {
        $sql = "SELECT * FROM wp_gpxTransactions WHERE cartID='".$cartID."' AND cancelled IS NULL";
        $rows = $wpdb->get_results($sql);
    }
    $i = 0;
    if(!empty($rows))
    {
        foreach($rows as $row)
        {
            if(empty($row->sessionID))
            {
                continue;
            }
            $user = get_userdata($cid);
            if(isset($user) && !empty($user))
                $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
            
            $transactions[$i] = json_decode($row->data);
            
            $sql = "SELECT * FROM wp_resorts WHERE ResortID='".$transactions[$i]->ResortID."'";
            $resort[$i] = $wpdb->get_row($sql);
        
            $sql = "SELECT * FROM wp_resorts_meta WHERE ResortID='".$transactions[$i]->ResortID."'";
            $rms = $wpdb->get_results($sql);
            
            foreach($rms as $rm)
            {
                $rmk = $rm->meta_key;
                
                if($rmArr = json_decode($rm->meta_value, true))
                {
                    foreach($rmArr as $rmdate=>$rmvalues)
                    {
                        // we need to display all of the applicaable alert notes
                        if(isset($lastValue) && !empty($lastValue))
                        {
                            $thisVal = $lastValue;
                        }
                        else
                        {
                            if(isset($resort->$rmk))
                            {
                                $thisVal = $resort->$rmk;
                            }
                        }
                        
                        $rmdates = explode("_", $rmdate);
                        if(count($rmdates) == 1 && $rmdates[0] == '0')
                        {
                            //do nothing
                        }
                        else
                        {
                            //check to see if the from date within the checkin date
                            if($rmdates[0] < strtotime($prop->checkIn))
                            {
                                //this date has started we can keep working
                            }
                            else
                            {
                                //these meta items don't need to be used -- except for alert notes -- we can show those in the future
                                if($rmk != 'AlertNote')
                                {
                                    continue;
                                }
                            }
                            //check to see if the to date has passed
                            if(isset($rmdates[1]) && ($rmdates[1] < strtotime($prop->checkIn)))
                            {
                                //these meta items don't need to be used
                                continue;
                            }
                            else
                            {
                                //this date is sooner than the end date we can keep working
                            }
                            if(array_key_exists($rmk, $attributesList))
                            {
                                // this is an attribute list Handle it now...
                                $thisVal = $resort->$rmk;
                                $thisVal = json_encode($rmvalues);
                            }
                            else
                            {
                                $rmval = end($rmvalues);
                                //set $thisVal = ''; if we should just leave this completely off when the profile button isn't selected
                                if(isset($resort->$rmk))
                                {
                                    $thisVal = $resort->$rmk;
                                }
                                //check to see if this should be displayed in the booking path
                                if(isset($rmval['path']) && $rmval['path']['booking'] == 0)
                                {
                                    //this isn't supposed to be part of the booking path
                                    continue;
                                }
                                if(isset($rmval['desc']))
                                {
                                    if($rmk == 'AlertNote')
                                    {
                                        
                                        if(!in_array($rmval['desc'], $thisset))
                                        {
                                            $thisValArr[] = [
                                                'desc' => $rmval['desc'],
                                                'date' => $rmdates,
                                            ];
                                        }
                                        $thisset[] = $rmval['desc'];
                                    }
                                    else
                                    {
                                        $thisVal = $rmVal['desc'];
                                        $thisValArr = [];
                                    }
                                }
                            }
                        }
                        $lastValue = $thisVal;
                    }
                    if($rmk == 'AlertNote' && isset($thisValArr) && !empty($thisValArr))
                    {
                        $thisVal = $thisValArr;
                    }
                    $resort[$i]->$rmk = $thisVal;
                }
                else
                {
                    if($meta->meta_value != '[]')
                    {
                        $resort[$i]->$rmk = $meta->meta_value;
                    }
                }
                
            }

            $sql = "SELECT id FROM wp_properties WHERE weekID='".$row->weekId."'";
            $prow = $wpdb->get_row($sql);
            if(!empty($prow))
            {
                $book = $prow->id;
            }
            else 
            {
                $book = $row->weekId;
            }
            
            
            
            
            $property_details[$i] = get_property_details($book, $cid);

            //check for auto coupons
            $sql = "SELECT a.coupon_hash, b.Name, b.Properties, b.Slug FROM wp_gpxAutoCoupon a
                    INNER JOIN wp_specials b ON a.coupon_id=b.id
                    WHERE transaction_id=".$row->id." AND user_id =".$row->userID;
            $acRow = $wpdb->get_row($sql);
            if(!empty($acRow))
            {
                $acProps = json_decode($acRow->Properties);
            
                $acCoupon[$i] = array(
                    'name'=>$acRow->Name,
                    'slug' => $acRow->Slug,
                    'code'=>$acRow->coupon_hash,
                    'tc' => $acProps->actc,
                );
            }
            
            if(isset($transactions[$i]->promoName) && !empty($transactions[$i]->promoName))
            {
                $sql = "SELECT * FROM wp_specials WHERE Name LIKE '%".$transactions[$i]->promoName."%'";
                $promos = $wpdb->get_results($sql);
                foreach($promos as $promo)
                {
                    $promoprops = json_decode($promo->Properties);
                    if(isset($promoprops->terms) && !empty($promoprops->terms))
                    {
                        $tcs[$promoprops->terms] = $promoprops->terms;
                    }
                }
            }
            if(isset($property_details[$i]['promoTerms']) && !empty($property_details[$i]['promoTerms']))
            {
                $tcs[$property_details[$i]['promoTerms']] = $property_details[$i]['promoTerms'];
            }
            $i++;
        }
        if(!isset($transactions))
        {
            foreach($rows as $row)
            {
                $transactions[$row->id] = json_decode($row->data);
            }
        }
    }
    else 
    {
        $sql = "SELECT * FROM wp_cart WHERE cartID='".$cartID."' ORDER BY id DESC LIMIT 1";
        $row = $wpdb->get_row($sql);
        
        $transactions[$row->id] = json_decode($row->data);
        if(empty($transactions[$row->id]->Paid))
        {
            $transactions[$row->id]->Paid = $transactions[$row->id]->fee;
        }
    }
    include('templates/sc-booking-path-confirmation.php');    
}
add_shortcode('gpx_booking_path_confirmation', 'gpx_booking_path_confirmation_cs');

function gpx_email_confirmation($atts)
{
    global $wpdb;  

    $atts = shortcode_atts(
        array(
            'terms' => '',
        ), $atts, 'gpx_booking_path_confirmation' );
    
    $cid = get_current_user_id();
    
    if(isset($_COOKIE['switchuser']))
        $cid = $_COOKIE['switchuser'];
    if(isset($_POST['confirmation']))
        $cartID = $_GET['confirmation'];
    $sql = "SELECT * FROM wp_gpxTransactions WHERE cartID='".$cartID."'";
    $rows = $wpdb->get_results($sql);
    $i = 0;
    if(!empty($rows))
        foreach($rows as $row)
        {
            $user = get_userdata($cid);
            if(isset($user) && !empty($user))
                $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
            
            $transactions[$i] = json_decode($row->data);
            
            $sql = "SELECT * FROM wp_resorts WHERE ResortID='".$transactions[$i]->ResortID."'";
            $resort[$i] = $wpdb->get_row($sql);
        
            $sql = "SELECT * FROM wp_resorts_meta WHERE ResortID='".$transactions[$i]->ResortID."'";
            $rms = $wpdb->get_results($sql);
//              print_r($rms);
             die();

            foreach($rms as $rm)
            {
                $rmk = $rm->meta_key;
                $resort[$i]->$rmk = $rm->meta_value;
            }

            $sql = "SELECT id FROM wp_properties WHERE weekID='".$row->weekId."'";
            $prow = $wpdb->get_row($sql);
            if(!empty($prow))
            {
                $book = $prow->id;
            }
            else
            {
                $book = $row->weekId;
            }
            
            $property_details[$i] = get_property_details($book, $cid);
            $i++;
        }
    include('templates/sc-booking-path-confirmation.php'); 
    
    
}
add_shortcode('gpx_email_confirmation', 'gpx_email_confirmation');

function map_dae_to_vest_properties()
{
    $mapPropertiesToRooms = [
        'id' => 'record_id',
        'checkIn'=>'check_in_date',
        'checkOut'=>'check_out_date',
        'Price'=>'price',
        'weekID'=>'record_id',
        'weekId'=>'record_id',
        'resortId'=>'resort',
        'resortID'=>'resort',
        'StockDisplay'=>'availability',
        'WeekType' => 'type',
        'noNights' => 'DATEDIFF(check_out_date, check_in_date)',
        'active_rental_push_date' => 'active_rental_push_date',
    ];
    $mapPropertiesToUnit = [
        'bedrooms' => 'number_of_bedrooms',
        'sleeps' => 'sleeps_total',
        'Size' => 'name',
    ];
    $mapPropertiesToResort = [
        'country'=>'Country',
        'region'=>'Region',
        'locality'=>'Town',
        'resortName'=>'ResortName',
    ];
    $mapPropertiesToResort = [
        'Country'=>'Country',
        'Region'=>'Region',
        'Town'=>'Town',
        'ResortName'=>'ResortName',
        'ImagePath1'=>'ImagePath1',
        'AlertNote'=>'AlertNote',
        'AdditionalInfo'=>'AdditionalInfo',
        'HTMLAlertNotes'=>'HTMLAlertNotes',
        'ResortID'=>'ResortID',
        'taxMethod'=>'taxMethod',
        'taxID'=>'taxID',
        'gpxRegionID'=>'gpxRegionID',
    ];
    
    $output['roomTable'] = [
        'alias'=>'a',
        'table'=>'wp_room',
    ];
    $output['unitTable'] = [
        'alias'=>'c',
        'table'=>'wp_unit_type',
    ];
    $output['resortTable'] = [
        'alias'=>'b',
        'table'=>'wp_resorts',
    ];
    foreach($mapPropertiesToRooms as $key=>$value)
    {
        if($key == 'noNights')
        {
            $output['joinRoom'][] = $value.' as '.$key;
        }
        else
        {
            $output['joinRoom'][] = $output['roomTable']['alias'].'.'.$value.' as '.$key;
        }
    }
    foreach($mapPropertiesToUnit as $key=>$value)
    {
        $output['joinUnit'][] =$output['unitTable']['alias'].'.'. $value.' as '.$key;
    }
    foreach($mapPropertiesToResort as $key=>$value)
    {
        $output['joinResort'][] = $output['resortTable']['alias'].'.'.$value.' as '.$key;
    }
    
    return $output;
}

/*
 * GPX Results Page Shortcode
 * 
 * Displays results from params / request variables
 * Combined function to display results from other functions, posted search request
 * and custom requests.
 * 
 * @param int $resortID
 * @param int $paginate
 * @param bool $calendar
 * @return html|object returns an object when called from wp-ajax otherwise returns html
 */
function gpx_result_page_sc($resortID='', $paginate='', $calendar='')
{
    global $wpdb;
    
    //     //update the join id
                   
    if(isset($resortID) && !empty($resortID))
        $outputProps = true;
        
        if(isset($paginate) && !empty($paginate))
        {
            extract($paginate);
            $limit = " LIMIT ".$limitStart.", ".$limitCount;
        }
        
        $cid = get_current_user_id();
        
        if(isset($_COOKIE['switchuser']))
            $cid = $_COOKIE['switchuser'];
            
            if(isset($cid) && !empty($cid))
            {
                $user = get_userdata($cid);
                $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
            }
            
            
            if(!get_user_meta($cid, 'DAEMemberNo', TRUE))
            {
                require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
                $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
                
                $DAEMemberNo = str_replace("U", "", $user->user_login);
                $user = $gpx->DAEGetMemberDetails($DAEMemberNo, $cid, array('email'=>$usermeta->email));
            }
            
            if(isset($_GET['destination']))
            {
                $_REQUEST['location'] = $_GET['destination'];
                if($_REQUEST['select_year'] > 2018)
                {
                    //we need to pull these dates
                }
                else
                {
                    $alldates = true;
                }
            }
            if(isset($_REQUEST))
            {
                extract($_REQUEST);
                
                //is this a previously matched result?
                if(isset($_REQUEST['matched']))
                {
                    $sql = "SELECT * FROM wp_gpxCustomRequest WHERE id='".$matched."'";
                    $matchedDB = (array) $wpdb->get_row($sql);
                    $props = custom_request_match($matchedDB, '1');
                    unset($props['restricted']);
                }
                else
                {
                    if((empty($select_month) && empty($select_year)))
                        $alldates = true;
                        if($select_month == 'any')
                        {
                            $thisYear = date('Y');
                            if(!isset($select_year))
                                $select_year = date('Y');
                                $monthstart = date($select_year.'-m-d');
                                if($thisYear != $select_year)
                                    $monthstart = $select_year.'-01-01';
                                    $monthend = $select_year."-12-31";
                        }
                        else
                        {
                            $nextmonth = date('Y-m-d', strtotime('+1 month'));
                            if(!isset($select_year))
                            {
//                                 $select_year = date('Y', strtotime($nextmonth));
                                $select_year = date('Y');
                            }
                            if(!isset($select_month))
                            {
                                $select_month = date('f', strtotime($nextmonth));
                            }
                            $monthstart = date('Y-m-01', strtotime($select_month."-".$select_year));
                            $today = date('Y-m-d');
                            if($monthstart < $today)
                            {
                                $monthstart = $today;
                            }
                            $monthend = date('Y-m-t', strtotime($select_month."-".$select_year));
                        }
                        
                        $joinedTbl = map_dae_to_vest_properties();
                        $sql = "SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinResort']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                    WHERE b.featured=1
                    AND a.active = 1 AND a.archived=0 AND a.active_rental_push_date != '2030-01-01'
                    AND b.active = 1";
                        $featuredprops = $wpdb->get_results($sql);

                        
                        foreach($featuredprops as $featuredprop)
                        {
                            $featuredresorts[$featuredprop->ResortID]['resort'] = $featuredprop;
                            $featuredresorts[$featuredprop->ResortID]['props'][] = $featuredprop;
                        }
                        

                        if(isset($_REQUEST['location']) && !empty($_REQUEST['location']))
                        {
                        
                            //                             echo '<pre>'.print_r("location", true).'</pre>';
                            $sql = "SELECT id, lft, rght FROM wp_gpxRegion WHERE name='".$location."' OR displayName='".$location."'";
                            $locs = $wpdb->get_results($sql);

                            
                            if(empty($locs))
                            {
                                //if this location is a country
                                $sql = "SELECT a.lft, a.rght FROM wp_gpxRegion a
                            INNER JOIN wp_daeRegion b ON a.RegionID=b.id
                            INNER JOIN wp_gpxCategory c ON c.CountryID=b.CategoryID
                            WHERE c.country='".$location."'";
                                $ranges = $wpdb->get_results($sql);
                                if(!empty($ranges))
                                {
                                    foreach($ranges as $range)
                                    {
                                        $sql = "SELECT id, name FROM wp_gpxRegion
                                    WHERE lft BETWEEN ".$range->lft." AND ".$range->rght."
                                    ORDER BY lft ASC";
                                        $rows = $wpdb->get_results($sql);
                                        foreach($rows as $row)
                                        {
                                            $ids[] = $row->id;
                                            
                                        }
                                    }
                                }
                                else
                                {
                                    //see if this is a resort
                                    $sql = "SELECT id FROM wp_resorts WHERE ResortName='".$location."'";
                                    $row = $wpdb->get_row($sql);
                                    if(!empty($row))
                                    {
                                        //redirect to the resort
                                        $redirectArr = [
                                            'resortName'=>$location,
                                        ];
                                        if(isset($select_month) && (!empty($select_month) || $select_month != 'f'))
                                        {
                                            $redirectArr['month'] = $select_month;
                                            if(isset($select_year) && !empty($select_year))
                                            {
                                                $redirectArr['yr'] = $select_year;
                                            }
                                        }
                                        $redirectQS = http_build_query($redirectArr);
                                        $redirectURL = home_url('/resort-profile/?'.$redirectQS);
                                        echo "<script>window.location.href = '".$redirectURL."';</script>";
                                        exit;
                                    }
                                }
                            }
                            else
                            {
                                foreach($locs as $loc)
                                {
                                    $sql = "SELECT id, name FROM wp_gpxRegion
                                WHERE lft BETWEEN ".$loc->lft." AND ".$loc->rght."
                                ORDER BY lft ASC";
                                    $rows = $wpdb->get_results($sql);
                                    foreach($rows as $row)
                                    {
                                        $ids[] = $row->id;
                                        
                                    }
                                }
                            }
                            if(isset($ids) && !empty($ids))
                            {
                                foreach($ids as $id)
                                {
                                    $wheres[] = "b.GPXREgionID='".$id."'";
                                }
                                $where = 'b.GPXRegionID IN ('.implode(",", $ids).')';
                                //                                 $where = implode(" OR ", $wheres);
                            }
                            else
                            {
                                $where = "b.GPXREgionID='na'";
                            }
                            if(isset($_GET['destination']))
                            {
                                
                                $sql = "SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                    WHERE (".$where.")"
                                    .$destDateWhere.
                                    "AND a.active = 1 AND  a.archived=0 AND a.active_rental_push_date != '2030-01-01'
                                AND b.active = 1";
                                    //             elseif(isset($alldates) && $select_month == 'any')
                                    //                 $sql = "SELECT *, a.id as PID, b.id as RID FROM wp_properties a
                                    //                     INNER JOIN wp_resorts b ON a.resortJoinID=b.id
                                    //                     WHERE (".$where.")
                                    //                     AND STR_TO_DATE(checkIn, '%d %M %Y') > '".$today."'
                                    //                     AND active = 1";
                            }
                            else
                            {
                                $sql = "SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinResort']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                    WHERE (".$where.")
                AND a.check_in_date BETWEEN '".$monthstart."' AND '".$monthend."'
                AND a.active = 1 AND  a.archived=0 AND a.active_rental_push_date != '2030-01-01'
                AND b.active = 1";
                            }
                            $resortSQLWhere = str_replace("b.", "", $where);
                            $resortsSql = "SELECT * FROM wp_resorts WHERE (".$resortSQLWhere.")
                                            AND active = 1";
                        }
                        elseif(isset($resortID))
                        {
                            
                            $destDateWhere = " WHERE check_in_date > '".$today."'";
                            if($select_month != 'f')
                            {
                                $destDateWhere = " AND check_in_date BETWEEN '".$monthstart."' AND '".$monthend."'";
                            }
                            $sql = "SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id"
                    
                                .$destDateWhere.
                                "AND b.id='".$resortID."'
                            AND a.active = 1 AND  a.archived=0 AND a.active_rental_push_date != '2030-01-01'
                            AND b.active = 1
                            ORDER BY check_in_date";
                        }
                        elseif(isset($alldates))
                        $sql = "SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                    WHERE a.check_in_date > '".$today."'
                        AND a.active = 1 AND  a.archived=0 AND a.active_rental_push_date != '2030-01-01'
                        AND b.active = 1";
                        else
                            $sql = "SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                    WHERE a.check_in_date BETWEEN '".$monthstart."' AND '".$monthend."'
                        AND a.active = 1 AND  a.archived=0 AND a.active_rental_push_date != '2030-01-01'
                        AND b.active = 1";
                            if(isset($limit) && !empty($limit))
                                $sql .= $limit;
                                if($where != "b.GPXREgionID='na'")
                                    $props = $wpdb->get_results($sql);
                }


                
                $totalCnt = count($props);

                if((isset($props) && !empty($props)) || isset($resortsSql))
                {
                    
                    
                    //let's first get query specials by the variables that are already set
                    $todayDT = date("Y-m-d 00:00:00");
                    $sql = "SELECT a.id, a.Name, a.Properties, a.Amount, a.SpecUsage, a.TravelStartDate, a.TravelEndDate
			FROM wp_specials a
            LEFT JOIN wp_promo_meta b ON b.specialsID=a.id
            LEFT JOIN wp_resorts c ON c.id=b.foreignID
            LEFT JOIN wp_gpxRegion d ON d.id=b.foreignID
            WHERE
                    (SpecUsage = 'any'
                 OR   ((b.reftable = 'wp_gpxRegion' AND d.id IN ('".implode("','", $ids)."')))
                        OR SpecUsage LIKE '%customer%'
                        OR SpecUsage LIKE '%dae%')
            AND Type='promo'
            AND (StartDate <= '".$todayDT."' AND EndDate >= '".$todayDT."')
            AND a.Active=1
            GROUP BY a.id";
                    $firstRows = $wpdb->get_results($sql);

                   

                    $prop_string = array();
                    $new_props = array();
                    foreach($props as $p){
                        $week_date_size = $p->resortId.'='.$p->WeekType.'='.date('m/d/Y', strtotime($p->checkIn)).'='.$p->Size;     
                        if(!in_array($week_date_size, $prop_string)){
                            $new_props[] = $p;
                        }
                        array_push($prop_string, $week_date_size);

                    }
 

                    $count_week_date_size = (array_count_values($prop_string));
                        
                    
                    $props = $new_props;

                  
                    $theseResorts = [];
                    foreach($props as $prop){

                        $string_week_date_size = $prop->resortId.'='.$prop->WeekType.'='.date('m/d/Y', strtotime($prop->checkIn)).'='.$prop->Size;     
                        $prop->prop_count = $count_week_date_size[$string_week_date_size];

                        //set all the resorts that are part of the results
                        if(!in_array($prop->ResortID, $theseResorts))
                        {
                            $theseResorts[$prop->ResortID] = $prop->ResortID;
                                                
                            //get all ther regions that this property belongs to
                            $propRegionParentIDs[$prop->ResortID] = [];
                            $sql = "SELECT parent FROM wp_gpxRegion WHERE id='".$prop->gpxRegionID."'";
                            $thisParent = $wpdb->get_var($sql);
                            $propRegionParentIDs[$prop->ResortID][] = $thisParent;
                            if(!empty($thisParent))
                            {
                                while(!empty($thisParent) && $thisParent != '1')
                                {
                                    $sql = "SELECT parent FROM wp_gpxRegion WHERE id='".$thisParent."'";
                                    $thisParent = $wpdb->get_var($sql);
                                    $propRegionParentIDs[$prop->ResortID][] = $thisParent;
                                }
                            }
                        }
                        
                        //date - resort groups
                        $rdgp = $prop->ResortID.strtotime($prop->checkIn);
                        $resortDates[$rdgp] = [
                            'ResortID'=>$prop->ResortID,
                            'checkIn'=>date('Y-m-d', strtotime($prop->checkIn)),
                            'propRegionParentIDs'=>$propRegionParentIDs[$prop->ResortID],
                        ];
                    }
                    
                    foreach($resortDates as $rdK=>$rdV)
                    {
                        $sql = "SELECT a.id, a.Name, a.Properties, a.Amount, a.SpecUsage, a.TravelStartDate, a.TravelEndDate
                    			FROM wp_specials a
                                LEFT JOIN wp_promo_meta b ON b.specialsID=a.id
                                LEFT JOIN wp_resorts c ON c.id=b.foreignID
                                LEFT JOIN wp_gpxRegion d ON d.id=b.foreignID
                                WHERE ((c.ResortID='".$rdV['ResortID']."' AND b.refTable='wp_resorts') OR(b.reftable = 'wp_gpxRegion' AND d.id IN ('".implode("','", $rdV['propRegionParentIDs'])."')))
                                AND Type='promo'
                                AND '".$rdV['checkIn']."' BETWEEN TravelStartDate AND TravelEndDate
                                AND (StartDate <= '".$todayDT."' AND EndDate >= '".$todayDT."')
                                AND a.Active=1
                                GROUP BY a.id";
                        $nextRows = $wpdb->get_results($sql);
                        $specRows[$rdK] = array_merge((array) $firstRows, (array) $nextRows);
                    }
                    
                    
        			//we only need to grab these resort metas				
					$whichMetas = [
					    'ExchangeFeeAmount',
					    'RentalFeeAmount',
					    'images',
					];
					
					// store $resortMetas as array
					$sql = "SELECT * FROM wp_resorts_meta WHERE ResortID IN ('".implode("','", $theseResorts)."') AND meta_key IN ('".implode("','", $whichMetas)."')";
                    $query = $wpdb->get_results($sql, ARRAY_A);
                    
                    foreach($query as $thisk=>$thisrow)
                    {                            
                    	$this['rmk'] = $thisrow['meta_key'];
                    	$this['rmv'] = $thisrow['meta_value'];
                    	$this['rid'] = $thisrow['ResortID'];
                    	
                    	$resortMetas[$this['rid']][$this['rmk']] = $this['rmv'];
                    	
                    	// image
                            if(!empty($resortMetas[$this['rid']]['images']))
                            {
                                $resortImages = json_decode($resortMetas[$this['rid']]['images'], true);
                                $oneImage = $resortImages[0];
                                
                                
                            // store items for $prop in ['to_prop'] // extract in loop
                                $resortMetas[$this['rid']]['ImagePath1'] = $oneImage['src'];
                                
                                
                                unset($resortImages);
                                unset($oneImage);
                            }
					}
                    
                    $propKeys = array_keys($props);
                    $pi = 0;
                    $ppi = 0;
                    while($pi < count($props))
                    {
                        
                        $propKey = $propKeys[$pi];
                        $prop = $props[$pi];
                        
                        
                                            
                        if(!isset($prop->ResortID))
                        {
                            $rSql = "SELECT ResortID FROM wp_resorts WHERE id='".$prop->RID."'";
                            $rRow = $wpdb->get_row($rSql);
                            $prop->ResortID = $rRow->ResortID;
                        }
                        
                        //skip anything that has an error
                        $allErrors = [
                            'checkIn',
                        ];
                        //validate availablity
                        if($prop->availablity == '2')
                        {
                            //partners shouldn't see this
                            //this should only be available to partners
                            $sql = "SELECT record_id FROM wp_partner WHERE user_id='".$cid."'";
                            $row = $wpdb->get_row($sql);
                            if(!empty($row))
                            {
                                continue;
                            }
                        }
                        if($prop->availablity == '3')
                        {
                            //only partners shouldn't see this
                            //this should only be available to partners
                            $sql = "SELECT record_id FROM wp_partner WHERE user_id='".$cid."'";
                            $row = $wpdb->get_row($sql);
                            if(empty($row))
                            {
                                continue;
                            }
                        }
                        foreach($allErrors as $ae)
                        {
                            if(empty($prop->$ae) || $prop->$ae == '0000-00-00 00:00:00')
                            {
                                continue;
                            }
                        }
                        //if this type is 3 then i't both exchange and rental. Run it as an exchange
                        if($prop->PID == '47071506')
                        {
                            $ppi++;
                        }
                        if($prop->WeekType == '1')
                        {
                            $prop->WeekType = 'ExchangeWeek';
                            $alwaysWeekExchange = 'ExchangeWeek';
                        }
                        elseif($prop->WeekType == '2')
                        {
                            $prop->WeekType = 'RentalWeek';
                            $alwaysWeekExchange = 'RentalWeek';
                        }
                        else 
                        {
                            if($prop->forRental)
                            {
                                $prop->WeekType = 'RentalWeek';
                                $alwaysWeekExchange = 'RentalWeek';
                                $prop->Price = $randexPrice[$prop->forRental];
                            }
                            else
                            {
                                $rentalAvailable = false;
                                
                                if(empty($prop->active_rental_push_date))
                                {
                                    if(strtotime($prop->checkIn) < strtotime('+ 6 months'))
                                    {
                                        $retalAvailable = true;
                                    }
                                }
                                elseif(strtotime('NOW') > strtotime($prop->active_rental_push_date))
                                {
                                    $rentalAvailable = true;
                                }
                               
                                if($rentalAvailable)
                                {
                                    $nextCnt = count($props);
                                    $props[$nextCnt] = $props[$propKey];
                                    $props[$nextCnt]->forRental = $nextCnt;
                                    $props[$nextCnt]->Price = $prop->Price;
                                    $randexPrice[$nextCnt] = $prop->Price;
                                    //                                     $propKeys[] = $rPropKey;
                                }
                                $prop->WeekType = 'ExchangeWeek';
                            }
                        }
                        $alwaysWeekExchange = $prop->WeekType;
                        
                        if($prop->WeekType == 'ExchangeWeek')
                        {
                            $prop->Price = get_option('gpx_exchange_fee');
                        }
                        $prop->Price = number_format($prop->Price, 0, '.', '');
                        $prop->WeekPrice = $prop->Price;
                       
                        $nextRows = array();
//                         if($prop->WeekType == 'RentalWeek' && $prop->OwnerBusCatCode == 'GPX' && $prop->StockDisplay == 'DAE')
                        if($prop->WeekType == 'RentalWeek' && ($prop->OwnerBusCatCode == 'GPX' || $prop->OwnerBusCatCode == 'USA GPX') && ($prop->StockDisplay == 'DAE' || $prop->StockDisplay == 'USA DAE'))
                        {
                            unset($prop);
                            continue;
                        }
                        
                    	// extract resort metas to prop -- in this case we are only concerned with the image and week price
                        if(!empty($resortMetas[$k]))
                        {
                        	foreach($resortMetas[$k] as $this['rmk']=>$this['rmv'])
                        	{
                        	    if($this['rmk'] == 'ImagePath1')
                        	    {
                        	        $prop->$this['rmk'] = $this['rmv'];
                        	    }
                        	    else 
                        	    {
                        	            //reset the resort meta items
                        	        $rmk = $this['rmk'];
                        	            if($rmArr = json_decode($this['rmv'], true))
                        	            {
                        	                foreach($rmArr as $rmdate=>$rmvalues)
                        	                {
                        	                    $thisVal = '';
                        	                    $rmdates = explode("_", $rmdate);
                        	                    if(count($rmdates) == 1 && $rmdates[0] == '0')
                        	                    {
                        	                        //do nothing
                        	                    }
                        	                    else
                        	                    {
                        	                        //changing this to go by checkIn instead of the active date
                        	                        $checkInForRM = strtotime($prop->checkIn);
                        	                        if(isset($_REQUEST['resortfeedebug']))
                        	                        {
                        	                            $showItems = [];
                        	                            $showItems[] = 'RID: '.$prop->RID;
                        	                            $showItems[] = 'PID: '.$prop->PID;
                        	                            $showItems[] = 'Check In: '.date('m/d/Y', $checkInForRM);
                        	                            $showItems[] = 'Override Start: '.date('m/d/Y', $rmdates[0]);
                        	                            $showItems[] = 'Override End: '.date('m/d/Y', $rmdates[1]);
                        	                            echo '<pre>'.print_r(implode(' -- ', $showItems), true).'</pre>';
                        	                        }
                        	                        //check to see if the from date has started
                        	                        //                                                 if($rmdates[0] < strtotime("now"))
                        	                        if($rmdates[0] <= $checkInForRM)
                        	                        {
                        	                            //this date has started we can keep working
                        	                        }
                        	                        else
                        	                        {
                        	                            //these meta items don't need to be used
                        	                            $pi++;
                        	                            continue;
                        	                        }
                        	                        //check to see if the to date has passed
                        	                        //                                                 if(isset($rmdates[1]) && ($rmdates[1] >= strtotime("now")))
                        	                        if(isset($rmdates[1]) && ($checkInForRM > $rmdates[1]))
                        	                        {
                        	                            //these meta items don't need to be used
                        	                            $pi++;
                        	                            continue;
                        	                        }
                        	                        else
                        	                        {
                        	                            //this date is sooner than the end date we can keep working
                        	                        }
                        	                        foreach($rmvalues as $rmval)
                        	                        {
                        	                            //do we need to reset any of the fees?
                        	                            if(array_key_exists($rmk, $rmFees))
                        	                            {
                        	                                //set this amount in the object
                        	                                $prop->$rmk = $rmval;
                        	                                if(!empty($rmFees[$rmk]))
                        	                                {
                        	                                    //if values exist then we need to overwrite
                        	                                    foreach($rmFees[$rmk] as $propRMK)
                        	                                    {
                        	                                        //if this is either week price or price then we only apply this to the correct week type...
                        	                                        if($rmk == 'ExchangeFeeAmount')
                        	                                        {
                        	                                            //$prop->WeekType cannot be RentalWeek or BonusWeek
                        	                                            if($prop->WeekType == 'BonusWeek' || $prop->WeekType == 'RentalWeek')
                        	                                            {
                        	                                                $pi++;
                        	                                                continue;
                        	                                            }
                        	                                        }
                        	                                        elseif($rmk == 'RentalFeeAmount')
                        	                                        {
                        	                                            //$prop->WeekType cannot be ExchangeWeek
                        	                                            if($prop->WeekType == 'ExchangeWeek')
                        	                                            {
                        	                                                $pi++;
                        	                                                continue;
                        	                                            }
                        	                                            
                        	                                        }
                        	                                        $prop->$propRMK = preg_replace("/\d+([\d,]?\d)*(\.\d+)?/", $rmval, $prop->$propRMK);
                        	                                    }
                        	                                }
                        	                            }
                        	                        }
                        	                    }
                        	                }
                        	            }
                        	            else
                        	            {
                        	                $prop->$this['rmk'] = $this['rmv'];
                        	            }
                        	    }
                        	}
                        }
                        
                        $plural = '';
                        $chechbr = strtolower(substr($prop->bedrooms, 0, 1));
                        if(is_numeric($chechbr))
                        {
                            $bedtype = $chechbr;
                            if($chechbr != 1)
                                $plural = 's';
                                $bedname = $chechbr." Bedroom".$plural;
                        }
                        elseif($chechbr == 's')
                        {
                            $bedtype = 'Studio';
                            $bedname = 'Studio';
                        }
                        else
                        {
                            $bedtype = $prop->bedrooms;
                            $bedname = $prop->bedrooms;
                        }
                        
                        $allBedrooms[$bedtype] = $bedname;
                        $prop->AllInclusive = '00';
                        $resortFacilities = json_decode($prop->ResortFacilities);
                        if((is_array($resortFacilities) && in_array('All Inclusive', $resortFacilities)) || strpos($prop->HTMLAlertNotes, 'IMPORTANT: All-Inclusive Information') || strpos($prop->AlertNote, 'IMPORTANT: This is an All Inclusive (AI) property.'))
                        {
                            $prop->AllInclusive = '6';
                        }
                        
                            $discount = '';
                            $prop->specialPrice = '';

                            if($specRows[$prop->ResortID])
                                foreach($specRows[$prop->ResortID] as $rowArr)
                                {
                                    
                                    $row = (object) $rowArr;
                                    if(get_current_user_id() != 5)
                                    {
                                        if($row->id == '438')
                                        {
                                            continue;
                                        }
                                    }
                                    //first remove any travel dates that slipped through on the first query
                                    if($date >= $row->TravelStartDate && $date <= $row->TravelEndDate )
                                    {
                                        //we are all good
                                    }
                                    else
                                    {
                                        continue;
                                    }
                                    
                                    $specialMeta = stripslashes_deep( json_decode($row->Properties));
                                    
                                    //if this is an exclusive week then we might need to remove this property
                                    if(isset($specialMeta->exclusiveWeeks) && !empty($specialMeta->exclusiveWeeks))
                                    {
                                        $exclusiveWeeks = explode(',', $specialMeta->exclusiveWeeks);
                                        if(in_array($prop->weekId, $exclusiveWeeks))
                                        {
                                            $rmExclusiveWeek[$prop->weekId] = $prop->weekId;
                                        }
                                        else
                                        {
                                            //this doesn't apply
                                            $skip = true;
                                            continue;
                                        }
                                    }
                                    // landing page only
                                    elseif(isset($specialMeta->availability) && $specialMeta->availability == 'Landing Page')
                                    {
                                        if(isset($_COOKIE['lp_promo']) && $_COOKIE['lp_promo'] == $row->Slug)
                                        {
                                            $returnLink = '<a href="/promotion/'.$row->Slug.'" class="return-link">View All '.$row->Name.' Weeks</a>';
                                        }
                                        //With regards to a 'Landing Page' promo setting...yes, if that is the setup then the discount is only to be presented on that page, otherwise we would set it up as site-wide.
                                        $skip = true;
                                        continue;
                                    }
                                    
                                    if(is_array($specialMeta->transactionType))
                                        $ttArr = $specialMeta->transactionType;
                                        else
                                            $ttArr = array($specialMeta->transactionType);
                                            $transactionTypes = array();
                                            foreach($ttArr as $tt)
                                            {
                                                switch ($tt)
                                                {
                                                    case 'Upsell':
                                                        $transactionTypes['upsell'] = 'Upsell';
                                                        break;
                                                        
                                                    case 'All':
                                                        $transactionTypes['any'] = $prop->WeekType;
                                                        break;
                                                        
                                                    case 'any':
                                                        $transactionTypes['any'] = $prop->WeekType;
                                                        break;
                                                    case 'ExchangeWeek':
                                                        $transactionTypes['exchange'] = 'ExchangeWeek';
                                                        break;
                                                    case 'BonusWeek':
                                                        $transactionTypes['bonus'] = 'BonusWeek';
                                                        $transactionTypes['rental'] = 'RentalWeek';
                                                        break;
                                                    case 'RentalWeek':
                                                        $transactionTypes['rental'] = 'RentalWeek';
                                                        $transactionTypes['bonus'] = 'Bonus';
                                                        break;
                                                }
                                            }
                                            
//                                             if(get_current_user_id() == 5)
//                                             {
//                                                 if($row->id == '438' && $prop->PID == '47071506')
//                                                 {
//                                                     echo '<pre>'.print_r($prop->PID.$prop->WeekType.$ppi, true).'</pre>';
//                                                 }
//                                             }
                                            $ttWeekType = $prop->WeekType;
                                                
                                            if($ttWeekType == 'RentalWeek' && !in_array('any', $transactionTypes) && !in_array($ttWeekType, $transactionTypes))
                                            {
                                                $ttWeekType = 'BonusWeek';
                                            }
                                            
                                            $prop->WeekType = $alwaysWeekExchange;
                                            
                                                if(in_array($ttWeekType, $transactionTypes))
                                                {
                                                   if(get_current_user_id() == 5)
                                                   {
                                                       if($row->id == 438)
                                                       {
                                                           echo '<pre>'.print_r("in promo x: ".$alwaysWeekExchange, true).'</pre>';
                                                           echo '<pre>'.print_r("in promo: ".$prop->WeekType, true).'</pre>';
                                                       }
                                                   }
                                                    $skip = false;
                                                    $regionOK = false;
                                                    /*
                                                     * filter out conditions
                                                     */
                                                    //upsell only
                                                    if(in_array('Upsell', $transactionTypes) && count($transactionTypes) == 1)
                                                    {
                                                        $skip = true;
                                                        continue;
                                                    }
                                                    
                                                    //blackouts
                                                    if(isset($specialMeta->blackout) && !empty($specialMeta->blackout))
                                                    {
                                                        foreach($specialMeta->blackout as $blackout)
                                                        {
                                                            if(strtotime($prop->checkIn) >= strtotime($blackout->start) && strtotime($prop->checkIn) <= strtotime($blackout->end))
                                                            {
                                                                $skip = true;
                                                                continue;
                                                            }
                                                        }
                                                    }
                                                    //resort blackout dates
                                                    if(isset($specialMeta->resortBlackout) && !empty($specialMeta->resortBlackout))
                                                    {
                                                        foreach($specialMeta->resortBlackout as $resortBlackout)
                                                        {
                                                            //if this resort is in the resort blackout array then continue looking for the date
                                                            if(in_array($prop->RID, $resortBlackout->resorts))
                                                            {
                                                                if(strtotime($prop->checkIn) >= strtotime($resortBlackout->start) && strtotime($prop->checkIn) <= strtotime($resortBlackout->end))
                                                                {
                                                                    $skip = true;
                                                                }
                                                            }
                                                        }
                                                        if($skip)
                                                        {
                                                            continue;
                                                        }
                                                    }//resort specific travel dates
                                                    if(isset($specialMeta->resortTravel) && !empty($specialMeta->resortTravel))
                                                    {
                                                        foreach($specialMeta->resortTravel as $resortTravel)
                                                        {
                                                            //if this resort is in the resort blackout array then continue looking for the date
                                                            if(in_array($prop->RID, $resortTravel->resorts))
                                                            {
                                                                if(strtotime($prop->checkIn) >= strtotime($resortTravel->start) && strtotime($prop->checkIn) <= strtotime($resortTravel->end))
                                                                {
                                                                    //all good
                                                                }
                                                                else
                                                                {
                                                                    $skip = true;
                                                                }
                                                            }
                                                        }
                                                        if($skip)
                                                        {
                                                            continue;
                                                        }
                                                    }
                                                    
                                                    
                                                    $prop->WeekType = $alwaysWeekExchange;
                                                    //week min cost
                                                    if(isset($specialMeta->minWeekPrice) && !empty($specialMeta->minWeekPrice))
                                                    {
                                                        if($prop->WeekType == 'ExchangeWeek')
                                                        {
                                                            $skip = true;
                                                        }
                                                        
                                                        if($prop->Price < $specialMeta->minWeekPrice)
                                                        {
                                                            $skip = true;
                                                        }
                                                    }
                                                    if((isset($specialMeta->beforeLogin) && $specialMeta->beforeLogin == 'Yes') && !is_user_logged_in())
                                                    {
                                                        $skip = true;
                                                    }
                                                    if(strpos($row->SpecUsage, 'customer') !== false)//customer specific
                                                    {
                                                        if(isset($cid))
                                                        {
                                                            $specCust = (array) json_decode($specialMeta->specificCustomer);
                                                            if(!in_array($cid, $specCust))
                                                            {
                                                                $skip = true;
                                                            }
                                                        }
                                                        else
                                                        {
                                                            $skip = true;
                                                        }
                                                        if($skip)
                                                        {
                                                            continue;
                                                        }
                                                    }
                                                    
                                                    
                                                    $prop->WeekType = $alwaysWeekExchange;
                                                    //transaction type
                                                    if(in_array('ExchangeWeek', $transactionType) || !in_array('BonusWeek', $transactionTypes))
                                                    {
                                                        if(!in_array($prop->WeekType, $transactionTypes))
                                                        {
                                                            $skip = true;
                                                            continue;
                                                        }
                                                    }
                                                    //usage region
                                                    if(isset($specialMeta->usage_region) && !empty($specialMeta->usage_region))
                                                    {
                                                        $usage_regions = json_decode($specialMeta->usage_region);
                                                        foreach($usage_regions as $usage_region)
                                                        {
                                                            $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE id='".$usage_region."'";
                                                            $excludeLftRght = $wpdb->get_row($sql);
                                                            $excleft = $excludeLftRght->lft;
                                                            $excright = $excludeLftRght->rght;
                                                            $sql = "SELECT id FROM wp_gpxRegion WHERE lft>=".$excleft." AND rght<=".$excright;
                                                            $usageregions = $wpdb->get_results($sql);
                                                            if(isset($usageregions) && !empty($usageregions))
                                                            {
                                                                foreach($usageregions as $usageregion)
                                                                {
                                                                    $uregionsAr[] = $usageregion->id;
                                                                }
                                                            }
                                                            
                                                        }
                                                        if(!in_array($prop->gpxRegionID, $uregionsAr))
                                                        {
                                                            $skip = true;
                                                            continue;
                                                        }
                                                        else
                                                        {
                                                            $regionOK = true;
                                                        }
                                                    }
                                                    
                                                    //usage resort
                                                    if(isset($specialMeta->usage_resort) && !empty($specialMeta->usage_resort))
                                                    {
                                                        if(!in_array($prop->RID, $specialMeta->usage_resort))
                                                        {
                                                            if(isset($regionOK) && $regionOK == true)//if we set the region and it applies to this resort then the resort doesn't matter
                                                            {
                                                                //do nothing
                                                            }
                                                            else
                                                            {
                                                                $skip = true;
                                                                continue;
                                                            }
                                                        }
                                                    }
                                                    //useage DAE
//                                                     if(isset($specialMeta->useage_dae) && !empty($specialMeta->useage_dae))
//                                                     {
//                                                         //Only show if OwnerBusCatCode = DAE AND StockDisplay = ALL or GPX
//                                                         //if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx') && strtolower($prop->OwnerBusCatCode) == 'dae')
//                                                         if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx' || strtolower($prop->StockDisplay) == 'usa gpx') && (strtolower($prop->OwnerBusCatCode) == 'dae' || strtolower($prop->OwnerBusCatCode) == 'usa dae'))
//                                                         {
//                                                             // we're all good -- these are the only properties that should be displayed
//                                                         }
//                                                         else
//                                                         {
//                                                             $skip = true;
//                                                             continue;
//                                                         }
//                                                     }
                                                    //exclusions
                                                    //exclude DAE
//                                                     if((isset($specialMeta->exclude_dae) && !empty($specialMeta->exclude_dae)) || (isset($specialMeta->exclusions) && $specialMeta->exclusions == 'dae'))
//                                                     {
//                                                         //If DAE selected as an exclusion:
//                                                         //- Do not show inventory to use unless
//                                                         //--- Stock Display = GPX or ALL
//                                                         //AND
//                                                         //---OwnerBusCatCode=GPX
//                                                         if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'usa gpx') && strtolower($prop->OwnerBusCatCode) == 'usa gpx')
// //                                                         if(
// //                                                             (
// //                                                                 strtolower($prop->StockDisplay) == 'all' 
// //                                                                 || strtolower($prop->StockDisplay) == 'gpx' 
// //                                                                 || strtolower($prop->StockDisplay) == 'usa gpx'
// //                                                             ) 
// //                                                             && 
// //                                                             (
// //                                                                 strtolower($prop->OwnerBusCatCode) == 'gpx' 
// //                                                                 || strtolower($prop->OwnerBusCatCode) == 'usa gpx'
// //                                                             )
// //                                                           )
//                                                         {
//                                                             //all good we can show these properties
//                                                         }
//                                                         else
//                                                         {
//                                                             $skip = true;
//                                                             continue;
//                                                         }
//                                                     }

                                                    //exclude resorts
                                                    if(isset($specialMeta->exclude_resort) && !empty($specialMeta->exclude_resort))
                                                    {
                                                        if(in_array($prop->RID, $specialMeta->exclude_resort))
                                                        {
                                                                $skip = true;
                                                                //break;
                                                        }
                                                        if($skip)
                                                        {
                                                            continue;
                                                        }
                                                    }
                                                    
                                                    //exclude regions
                                                    if(isset($specialMeta->exclude_region) && !empty($specialMeta->exclude_region))
                                                    {
                                                        $exclude_regions = json_decode($specialMeta->exclude_region);
                                                        foreach($exclude_regions as $exclude_region)
                                                        {
                                                            $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE id='".$exclude_region."'";
                                                            $excludeLftRght = $wpdb->get_row($sql);
                                                            $excleft = $excludeLftRght->lft;
                                                            $excright = $excludeLftRght->rght;
                                                            $sql = "SELECT * FROM wp_gpxRegion WHERE lft>=".$excleft." AND rght<=".$excright;
                                                            $excregions = $wpdb->get_results($sql);
                                                            if(isset($excregions) && !empty($excregions))
                                                            {
                                                                foreach($excregions as $excregion)
                                                                {
                                                                    if($excregion->id == $prop->gpxRegionID)
                                                                    {
                                                                        $skip = true;
                                                                    }
                                                                }
                                                                if($skip)
                                                                {
                                                                    continue;
                                                                }
                                                            }
                                                        }
                                                    }
                                                    
                                                    //exclude home resort
                                                    if(isset($specialMeta->exclusions) && $specialMeta->exclusions == 'home-resort')
                                                    {
                                                        if(isset($usermeta) && !empty($usermeta))
                                                        {
                                                            $ownresorts = array('OwnResort1', 'OwnResort2', 'OwnResort3');
                                                            foreach($ownresorts as $or)
                                                            {
                                                                if(isset($usermeta->$or))
                                                                    if($usermeta->$or == $prop->ResortName)
                                                                        $skip = true;
                                                            }
                                                            if($skip)
                                                            {
                                                                continue;
                                                            }
                                                        }
                                                    }
                                                    //                             if(isset($specialMeta->exclusions))
                                                        //                             {
                                                        //                                 switch ($specialMeta->exclusions)
                                                        //                                 {
                                                        //                                     case 'resort':
                                                        //                                         if(isset($specialMeta->exclude_resort))
                                                            //                                             foreach($specialMeta->exclude_resort as $exc_resort)
                                                                //                                             {
                                                                //                                                 if($exc_resort == $prop->RID)
                                                                    //                                                 {
                                                                    //                                                     $skip = true;
                                                                    //                                                     break 2;
                                                                    //                                                 }
                                                                //                                             }
                                                            //                                         break;
                                                            
                                                            //                                     case 'region':
                                                            //                                         if(isset($specialMeta->exclude_region))
                                                                //                                         {
                                                                //                                             //get all sub regions
                                                                //                                             $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE id='".$specialMeta->exclude_region."'";
                                                                //                                             $excludeLftRght = $wpdb->get_row($sql);
                                                                //                                             $excleft = $excludeLftRght->lft;
                                                                //                                             $excright = $excludeLftRght->rght;
                                                                //                                             $sql = "SELECT * FROM wp_gpxRegion WHERE lft>=".$excleft." AND rght<=".$excright;
                                                                //                                             $excregions = $wpdb->get_results($sql);
                                                                //                                             if(isset($excregions) && !empty($excregions))
                                                                    //                                             {
                                                                    //                                                 foreach($excregions as $excregion)
                                                                        //                                                 {
                                                                        //                                                     if($excregion->id == $prop->gpxRegionID)
                                                                            //                                                     {
                                                                            //                                                         $skip = true;
                                                                            //                                                     }
                                                                        //                                                 }
                                                                    //                                             }
                                                                //                                         }
                                                            //                                         break;
                                                            //                                 }
                                                            //                             }
                                                    
                                                    //lead time
                                                    $today = date('Y-m-d');
                                                    if(isset($specialMeta->leadTimeMin) && !empty($specialMeta->leadTimeMin))
                                                    {
                                                        $ltdate = date('Y-m-d', strtotime($prop->checkIn." -".$specialMeta->leadTimeMin." days"));
                                                        if($today > $ltdate)
                                                        {
                                                            $skip = true;
                                                            continue;
                                                        }
                                                    }
                                                    
                                                    if(isset($specialMeta->leadTimeMax) && !empty($specialMeta->leadTimeMax))
                                                    {
                                                        $ltdate = date('Y-m-d', strtotime($prop->checkIn." -".$specialMeta->leadTimeMax." days"));
                                                        if($today < $ltdate)
                                                        {
                                                            $skip = true;
                                                            continue;
                                                        }
                                                    }
                                                    if(!$skip)
                                                    {
                                                        $thisDiscounted = '';
                                                        if(isset($rmExclusiveWeek[$prop->weekId]) && !empty($rmExclusiveWeek[$prop->weekId]))
                                                        {
                                                            unset($rmExclusiveWeek[$prop->weekId]);
                                                        }
                                                        $discount = $row->Amount;
                                                        $discountType = $specialMeta->promoType;
                                                        if($discountType == 'Pct Off')
                                                        {
                                                            $thisSpecialPrice = number_format($prop->Price*(1-($discount/100)), 2, '.', '');
                                                            if( ( isset($prop->specialPrice) && ( $thisSpecialPrice < $prop->specialPrice || empty( $prop->specialPrice )  ) ) || empty($prop->specialPrice) )
                                                            {
                                                                $prop->specialPrice = $thisSpecialPrice;
                                                                $thisDiscounted = true;
                                                            }
                                                        }
                                                        elseif($discountType == 'Dollar Off')
                                                        {
                                                            $thisSpecialPrice = $prop->Price-$discount;
                                                            if( ( isset($prop->specialPrice) && ( $thisSpecialPrice < $prop->specialPrice || empty( $prop->specialPrice )  ) ) || !isset($prop->specialPrice) )
                                                            {
                                                                $prop->specialPrice = $thisSpecialPrice;
                                                                $thisDiscounted = true;
                                                            }
                                                        }
                                                        elseif($discount < $prop->Price)
                                                        {
                                                            $thisSpecialPrice = $discount;
                                                            if( ( isset($prop->specialPrice) && ( $thisSpecialPrice < $prop->specialPrice || empty( $prop->specialPrice )  ) ) || !isset($prop->specialPrice) )
                                                            {
                                                                $prop->specialPrice = $thisSpecialPrice;
                                                                $thisDiscounted = true;
                                                            }
                                                        }
                                                        
                                                        if($prop->specialPrice < 0)
                                                        {
                                                            $prop->specialPrice = '0.00';
                                                        }
                                                        if(isset($specialMeta->icon) && $thisDiscounted)
                                                        {
                                                            $prop->specialicon = $specialMeta->icon;
                                                        }
                                                        if(isset($specialMeta->desc) && $thisDiscounted)
                                                        {
                                                            $allDescs[] = $specialMeta->desc;
                                                            $prop->specialdesc  = $specialMeta->desc;
                                                            $prop->specialnum  = $row->id;
                                                        }
                                                            
                                                        if(isset($specialMeta->stacking) && $specialMeta->stacking == 'No' && $prop->specialPrice > 0)
                                                        {
                                                            //check if this amount is less than the other promos
                                                            if($discountType == 'Pct Off')
                                                            {
                                                                $thisStackPrice = number_format($prop->Price*(1-($discount/100)), 2, ".", "");
                                                                if( ( isset($prop->specialPrice) && $thisStackPrice < $prop->specialPrice ) || !isset($prop->specialPrice) )
                                                                {
                                                                    $stackPrice = $thisStackPrice;
                                                                }
                                                            }
                                                            elseif($discountType == 'Dollar Off')
                                                            {
                                                                $thisStackPrice = $prop->Price-$discount;
                                                                if( ( isset($prop->specialPrice) && $thisStackPrice < $prop->specialPrice ) || !isset($prop->specialPrice) )
                                                                {
                                                                    $stackPrice = $thisSpecialPrice;
                                                                }
                                                            }
                                                            elseif($discount < $prop->Price)
                                                            {
                                                                $thisStackPrice = $discount;
                                                                if( ( isset($prop->specialPrice) && $thisStackPrice < $prop->specialPrice ) || !isset($prop->specialPrice) )
                                                                {
                                                                    $stackPrice = $thisSpecialPrice;
                                                                }
                                                            }
                                                            
                                                            if($stackPrice != 0 && $stackPrice < $prop->specialPrice)
                                                            {
                                                                $allDescs = array($specialMeta->desc);
                                                                $prop->specialPrice = $stackPrice;
                                                            }
                                                            else
                                                            {
                                                            }
                                                        }
                                                        $prop->special = (object) array_merge((array) $special, (array) $specialMeta);
                                                    }
                                                }
                                                
                                }
                            
                            //remove any exclusive weeks
                            if(isset($rmExclusiveWeek[$prop->weekId]) && !empty($rmExclusiveWeek[$prop->weekId]))
                            {
                                unset($props[$propKey]);
                                continue;
                            }
                            
                            if(get_current_user_id() == 5)
                            {
                                //                             echo '<pre>'.print_r($allDescs, true).'</pre>';
                                //                             $allDescs = array_unique($allDescs);
                                //                             $prop->specialdesc = implode("; ", $allDescs);
                            }
                            
                            
                            $prop->WeekType = $alwaysWeekExchange;
                            //sort the results by date...
                            $weekTypeKey = 'b';
                            if($prop->WeekType == 'ExchangeWeek')
                            {
                                $weekTypeKey = 'a';
                            }
                            if($prop->WeekType == 'RentalWeek')
                            {
                                $weekTypeKey = 'c';
                            }
                            
                            
                            $prop->WeekType = $alwaysWeekExchange;
                            $datasort = strtotime($prop->checkIn).$weekTypeKey.$prop->PID;
                            $checkFN[] = $prop->gpxRegionID;
                            $regions[$prop->gpxRegionID] = $prop->gpxRegionID;
                            $resorts[$prop->ResortID]['resort'] = $prop;
                            $resorts[$prop->ResortID]['props'][$datasort] = $prop;
                            $propPrice[$datasort] = $prop->WeekPrice;
                            $propType[$datasort] = $prop->WeekType;
                            $calendarRows[] = $prop;
                            $pi++;
                            
                            if(get_current_user_id() == 5 && $prop->PID == '47334901')
                            {
//                                 echo '<pre>'.print_r($prop, true).'</pre>';
//                                 echo '<pre>'.print_r($propType[$datasort], true).'</pre>';
                            }
                    }
                    //add all the extra resorts
                    if(isset($resortsSql))
                    {
                        foreach($resorts as $thisResortID=>$resortDets)
                        {
                            $thisSetResorts[] = $thisResortID;
                        }
                        $moreWhere = ' AND (ResortID NOT IN (\''.implode("','", $thisSetResorts).'\'))';
                        $resortsSql .= $moreWhere;
                        $allResorts = $wpdb->get_results($resortsSql);
                        foreach($allResorts as $ar)
                        {
                            $resorts[$ar->ResortID]['resort'] = $ar;
                        }
                    }
                    $newStyle = true;
                    $filterNames = array();
                    if(isset($checkFN) && !empty($checkFN))
                    {
                        foreach($checkFN as $fn)
                        {
                            $sql = "SELECT id, name FROM wp_gpxRegion
                            WHERE id='".$fn."'";
                            $fnRows = $wpdb->get_results($sql);
                            
                            foreach($fnRows as $fnRow)
                            {
                                if($fnRow->name != 'All')
                                    $filterNames[$fnRow->id] = $fnRow->name;
                            }
                        }
                    }
                    asort($filterNames);
                }
                
                if(isset($resorts) && isset($_SESSION['searchSessionID']))
                {
                    $savesearch = save_search($usermeta, $_REQUEST, 'search', $resorts);
                }
                elseif(isset($usermeta) && isset($resorts))
                {
                    $savesearch = save_search($usermeta, $_REQUEST, 'search', $resorts);
                }
                elseif(isset($usermeta))
                {
                    $savesearch = save_search($usermeta, $_REQUEST, 'search');
                }
                else
                {
                    $savesearch = save_search('', $_REQUEST, 'search');
                }
            }
            //get a list of restricted gpxRegions
            $sql = "SELECT id, lft, rght FROM wp_gpxRegion WHERE name='Southern Coast (California)'";
            $restLRs = $wpdb->get_results($sql);
            foreach($restLRs as $restLR)
            {
                $sql = "SELECT id FROM wp_gpxRegion WHERE lft BETWEEN ".$restLR->lft." AND ".$restLR->rght;
                $restricted = $wpdb->get_results($sql);
                foreach($restricted as $restrict)
                {
                    $restrictIDs[$restrict->id] = $restrict->id;
                }
            }
            

            if(isset($outputProps) && $outputProps)
            {
                if(isset($resorts))
                {
                    if(!empty($calendar))
                    {
                        return $calendarRows;
                    }
                    else
                    {
                        include ('templates/resort-availability.php');
                    }
                }
                else
                {
                    $output = '<div style="text-align:center; margin: 30px 20px 40px 20px; "><h3 style="color:#cc0000;">Your search didn\'t return any results</h3><p style="font-size:15px;">Please consider searching a different resort or try again later.</p></div>';
                }
                
                return $output;
            }
            else
            {

                include('templates/sc-result.php');
            }
}

function gpx_insider_week_page_sc()
{
    global $wpdb;
    
    $joinedTbl = map_dae_to_vest_properties();
    
    // remove login requirement
    //     if(is_user_logged_in())
        //     {
    $cid = get_current_user_id();
    
    if(isset($_COOKIE['switchuser']))
        $cid = $_COOKIE['switchuser'];
        
        if(isset($cid) && !empty($cid))
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
            
            $monthstart = date('Y-m-t', strtotime("now"));
            $monthend = date('Y-m-t', strtotime("+90 days"));
            
            $sql = "SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinResort']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                        WHERE check_in_date BETWEEN '".$monthstart."' AND '".$monthend."'
                        AND type IN (1, 3)
                        AND price <= 349
						AND price > 199
                        AND a.active = 1 AND  a.archived=0 AND a.active_rental_push_date != '2030-01-01'
                AND b.active = 1";
            $props = $wpdb->get_results($sql);
            if(isset($_REQUEST['insider_debug']))
            {
                echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                echo '<pre>'.print_r($wpdb->last_result, true).'</pre>';
            }
            if(isset($props) && !empty($props))
            {
                $prop_string = array();
                $new_props = array();
                foreach($props as $p)
                {
                    $week_date_size = $p->resortId.'='.$p->WeekType.'='.date('m/d/Y', strtotime($p->checkIn)).'='.$p->Size;     
                    if(!in_array($week_date_size, $prop_string))
                    {
                        $new_props[] = $p;
                    }
                    array_push($prop_string, $week_date_size);

                }

                $count_week_date_size = (array_count_values($prop_string)); 
                
                $props = $new_props;

                foreach($props as $prop)
                {

                    $string_week_date_size = $prop->resortId.'='.$prop->WeekType.'='.date('m/d/Y', strtotime($prop->checkIn)).'='.$prop->Size;     
                    $prop->prop_count = $count_week_date_size[$string_week_date_size];

                    
                    
                }
							
				$whichMetas = [
				    'ExchangeFeeAmount',
				    'RentalFeeAmount',
				    'images',
				];
				
				// store $resortMetas as array
				$sql = "SELECT * FROM wp_resorts_meta WHERE ResortID IN ('".implode("','", $theseResorts)."') AND meta_key IN ('".implode("','", $whichMetas)."')";
                $query = $wpdb->get_results($sql, ARRAY_A);
                
                foreach($query as $thisk=>$thisrow)
                {                            
                	$this['rmk'] = $thisrow['meta_key'];
                	$this['rmv'] = $thisrow['meta_value'];
                	$this['rid'] = $thisrow['ResortID'];
                	
                	$resortMetas[$this['rid']][$this['rmk']] = $this['rmv'];
                	
                	// image
                    if(!empty($resortMetas[$this['rid']]['images']))
                    {
                        $resortImages = json_decode($resortMetas[$this['rid']]['images'], true);
                        $oneImage = $resortImages[0];
                        
                        // store items for $prop in ['to_prop'] // extract in loop
                        $resortMetas[$this['rid']]['ImagePath1'] = $oneImage['src'];
                        
                        
                        unset($resortImages);
                        unset($oneImage);
                    }
				} 
                
                $propKeys = array_keys($props);
                $pi = 0;
                $datasort = 0;
                while($pi < count($props))
                {
                    $propKey = $propKeys[$pi];
                    $prop = $props[$pi];
                    //skip anything that has an error
                    $allErrors = [
                        'checkIn',
                    ];
                    //validate availablity
                    if($prop->availablity == '2')
                    {
                        //partners shouldn't see this
                        //this should only be available to partners
                        $sql = "SELECT record_id FROM wp_partner WHERE user_id='".$cid."'";
                        $row = $wpdb->get_row($sql);
                        if(!empty($row))
                        {
                            continue;
                        }
                    }
                    if($prop->availablity == '3')
                    {
                        //only partners shouldn't see this
                        //this should only be available to partners
                        $sql = "SELECT record_id FROM wp_partner WHERE user_id='".$cid."'";
                        $row = $wpdb->get_row($sql);
                        if(empty($row))
                        {
                            continue;
                        }
                    }
                    
                    foreach($allErrors as $ae)
                    {
                        if(empty($prop->$ae) || $prop->$ae == '0000-00-00 00:00:00')
                        {
                            continue;
                        }
                    }
                    //if this type is 3 then i't both exchange and rental. Run it as an exchange
                    if($prop->WeekType == '1')
                    {
                        $prop->WeekType = 'ExchangeWeek';
                    }
                    elseif($prop->WeekType == '2')
                    {
                        $prop->WeekType = 'RentalWeek';
                    }
                    else
                    {
                        if($prop->forRental)
                        {
                            $prop->WeekType = 'RentalWeek';
                            $prop->Price = $randexPrice[$prop->forRental];
                        }
                        else
                        {
                            $rentalAvailable = false;
                            if(empty($prop->active_rental_push_date))
                            {
                                if(strtotime($prop->checkIn) < strtotime('+ 6 months'))
                                {
                                    $retalAvailable = true;
                                }
                            }
                            elseif(strtotime('NOW') > strtotime($prop->accive_rental_push_date))
                            {
                                $rentalAvailable = true;
                            }
                            if($rentalAvailable)
                            {
                                $nextCnt = count($props);
                                $props[$nextCnt] = $props[$propKey];
                                $props[$nextCnt]->forRental = $nextCnt;
                                $props[$nextCnt]->Price = $prop->Price;
                                $randexPrice[$nextCnt] = $prop->Price;
                                //                                     $propKeys[] = $rPropKey;
                            }
                            $prop->WeekType = 'ExchangeWeek';
                        }
                    }
                    
                    if($prop->WeekType == 'ExchangeWeek')
                    {
                        $prop->Price = get_option('gpx_exchange_fee');
                    }
                    
                    $prop->WeekPrice = $prop->Price;
                    //if($prop->WeekType == 'RentalWeek' && ($prop->OwnerBusCatCode == 'GPX' && $prop->StockDisplay == 'DAE') || ($prop-OwnerBusCatCode == 'USA GPX' && $prop->StockDisplay == 'USA DAE'))
                    if($prop->WeekType == 'RentalWeek' && (($prop->OwnerBusCatCode == 'GPX' || $prop->OwnerBusCatCode == 'USA GPX') && ($prop->StockDisplay == 'DAE' || $prop->StockDisplay == 'USA DAE')) || (($prop-OwnerBusCatCode == 'GPX' || $prop-OwnerBusCatCode == 'USA GPX') && ($prop->StockDisplay == 'DAE' || $prop->StockDisplay == 'USA DAE')))
                    {
                        unset($prop);
                        continue;
                    }
                    
                	// extract resort metas to prop -- in this case we are only concerned with the image and week price
                    if(!empty($resortMetas[$k]))
                    {
                    	foreach($resortMetas[$k] as $this['rmk']=>$this['rmv'])
                    	{
                    	    if($this['rmk'] == 'ImagePath1')
                    	    {
                    	        $prop->$this['rmk'] = $this['rmv'];
                    	    }
                    	    else 
                    	    {
                    	            //reset the resort meta items
                    	        $rmk = $this['rmk'];
                    	            if($rmArr = json_decode($this['rmv'], true))
                    	            {
                    	                foreach($rmArr as $rmdate=>$rmvalues)
                    	                {
                    	                    $thisVal = '';
                    	                    $rmdates = explode("_", $rmdate);
                    	                    if(count($rmdates) == 1 && $rmdates[0] == '0')
                    	                    {
                    	                        //do nothing
                    	                    }
                    	                    else
                    	                    {
                    	                        //changing this to go by checkIn instead of the active date
                    	                        $checkInForRM = strtotime($prop->checkIn);
                    	                        if(isset($_REQUEST['resortfeedebug']))
                    	                        {
                    	                            $showItems = [];
                    	                            $showItems[] = 'RID: '.$prop->RID;
                    	                            $showItems[] = 'PID: '.$prop->PID;
                    	                            $showItems[] = 'Check In: '.date('m/d/Y', $checkInForRM);
                    	                            $showItems[] = 'Override Start: '.date('m/d/Y', $rmdates[0]);
                    	                            $showItems[] = 'Override End: '.date('m/d/Y', $rmdates[1]);
                    	                            echo '<pre>'.print_r(implode(' -- ', $showItems), true).'</pre>';
                    	                        }
                    	                        //check to see if the from date has started
                    	                        //                                                 if($rmdates[0] < strtotime("now"))
                    	                        if($rmdates[0] <= $checkInForRM)
                    	                        {
                    	                            //this date has started we can keep working
                    	                        }
                    	                        else
                    	                        {
                    	                            //these meta items don't need to be used
                    	                            $pi++;
                    	                            continue;
                    	                        }
                    	                        //check to see if the to date has passed
                    	                        //                                                 if(isset($rmdates[1]) && ($rmdates[1] >= strtotime("now")))
                    	                        if(isset($rmdates[1]) && ($checkInForRM > $rmdates[1]))
                    	                        {
                    	                            //these meta items don't need to be used
                    	                            $pi++;
                    	                            continue;
                    	                        }
                    	                        else
                    	                        {
                    	                            //this date is sooner than the end date we can keep working
                    	                        }
                    	                        foreach($rmvalues as $rmval)
                    	                        {
                    	                            //do we need to reset any of the fees?
                    	                            if(array_key_exists($rmk, $rmFees))
                    	                            {
                    	                                //set this amount in the object
                    	                                $prop->$rmk = $rmval;
                    	                                if(!empty($rmFees[$rmk]))
                    	                                {
                    	                                    //if values exist then we need to overwrite
                    	                                    foreach($rmFees[$rmk] as $propRMK)
                    	                                    {
                    	                                        //if this is either week price or price then we only apply this to the correct week type...
                    	                                        if($rmk == 'ExchangeFeeAmount')
                    	                                        {
                    	                                            //$prop->WeekType cannot be RentalWeek or BonusWeek
                    	                                            if($prop->WeekType == 'BonusWeek' || $prop->WeekType == 'RentalWeek')
                    	                                            {
                    	                                                $pi++;
                    	                                                continue;
                    	                                            }
                    	                                        }
                    	                                        elseif($rmk == 'RentalFeeAmount')
                    	                                        {
                    	                                            //$prop->WeekType cannot be ExchangeWeek
                    	                                            if($prop->WeekType == 'ExchangeWeek')
                    	                                            {
                    	                                                $pi++;
                    	                                                continue;
                    	                                            }
                    	                                            
                    	                                        }
                    	                                        $prop->$propRMK = preg_replace("/\d+([\d,]?\d)*(\.\d+)?/", $rmval, $prop->$propRMK);
                    	                                    }
                    	                                }
                    	                            }
                    	                        }
                    	                    }
                    	                }
                    	            }
                    	            else
                    	            {
                    	                $prop->$this['rmk'] = $this['rmv'];
                    	            }
                    	    }
                    	}
                    }
                
                    $plural = '';
                    $chechbr = strtolower(substr($prop->bedrooms, 0, 1));
                    if(is_numeric($chechbr))
                    {
                        $bedtype = $chechbr;
                        if($chechbr != 1)
                            $plural = 's';
                            $bedname = $chechbr." Bedroom".$plural;
                    }
                    elseif($chechbr == 's')
                    {
                        $bedtype = 'Studio';
                        $bedname = 'Studio';
                    }
                    else
                    {
                        $bedtype = $prop->bedrooms;
                        $bedname = $prop->bedrooms;
                    }
                    
                    $allBedrooms[$bedtype] = $bedname;
                    $prop->AllInclusive = '';
                    $resortFacilities = json_decode($prop->ResortFacilities);
                    if(in_array('All Inclusive', $resortFacilities) || strpos($prop->HTMLAlertNotes, 'IMPORTANT: All-Inclusive Information') || strpos($prop->AlertNote, 'IMPORTANT: This is an All Inclusive (AI) property.'))
                    {
                        unset($prop);
                        continue;
                        $prop->AllInclusive = '6';
                    }
                    
                    $priceint = preg_replace("/[^0-9\.]/", "",$prop->WeekPrice);
                    if($priceint != $prop->Price)
                        $prop->Price = $priceint;
                        
                        $discount = '';
                        $prop->specialPrice = '';
                        $date = date('Y-m-d', strtotime($prop->checkIn));
                        $todayDT = date("Y-m-d 00:00:00");
                        $sql = "SELECT a.Properties, a.Amount, a.SpecUsage
    			FROM wp_specials a
                LEFT JOIN wp_promo_meta b ON b.specialsID=a.id
                LEFT JOIN wp_resorts c ON c.id=b.foreignID
                LEFT JOIN wp_gpxRegion d ON d.id=b.foreignID
                WHERE
                    (SpecUsage = 'any'
                        OR ((c.ResortID='".$prop->ResortID."' AND b.refTable='wp_resorts')
                        OR (b.reftable = 'wp_gpxRegion' AND d.id IN ('".implode("','", $ids)."')))
                        OR SpecUsage LIKE '%customer%'
                        OR SpecUsage LIKE '%dae%')
                AND Type='promo'
                AND '".$date."' BETWEEN TravelStartDate AND TravelEndDate
                AND (StartDate <= '".$todayDT."' AND EndDate >= '".$todayDT."')
                AND a.Active=1
                AND c.active=1";
                        $rows = $wpdb->get_results($sql);
                        if($rows)
                            foreach($rows as $row)
                            {
                                $specialMeta = stripslashes_deep( json_decode($row->Properties));
                                
                                //if this is an exclusive week then we might need to remove this property
                                if(isset($specialMeta->exclusiveWeeks) && !empty($specialMeta->exclusiveWeeks))
                                {
                                    $exclusiveWeeks = explode(',', $specialMeta->exclusiveWeeks);
                                    if(in_array($prop->weekId, $exclusiveWeeks))
                                    {
                                        $rmExclusiveWeek[$prop->weekId] = $prop->weekId;
                                    }
                                    else
                                    {
                                        //this doesn't apply
                                        $skip = true;
                                        continue;
                                    }
                                }
                                
                                if(is_array($specialMeta->transactionType))
                                    $ttArr = $specialMeta->transactionType;
                                    else
                                        $ttArr = array($specialMeta->transactionType);
                                        $transactionTypes = array();
                                        foreach($ttArr as $tt)
                                        {
                                            switch ($tt)
                                            {
                                                case 'Upsell':
                                                    $transactionTypes['upsell'] = 'Upsell';
                                                    break;
                                                    
                                                case 'All':
                                                    $transactionTypes['any'] = $prop->WeekType;
                                                    break;
                                                    
                                                case 'any':
                                                    $transactionTypes['any'] = $prop->WeekType;
                                                    break;
                                                case 'ExchangeWeek':
                                                    $transactionTypes['exchange'] = 'ExchangeWeek';
                                                    break;
                                                case 'BonusWeek':
                                                    $transactionTypes['bonus'] = 'BonusWeek';
                                                    break;
                                                case 'RentalWeek':
                                                    $transactionTypes['bonus'] = 'RentalWeek';
                                                    break;
                                            }
                                        }
                                        $ttWeekType = $prop->WeekType;
                                        if($ttWeekType == 'RentalWeek' && $transactionType != 'Upsell' && !in_array('all', $transactionTypes))
                                            $ttWeekType = 'BonusWeek';
                                            if($row->Amount > $discount && in_array($ttWeekType, $transactionTypes))
                                            {
                                                $skip = false;
                                                $regionOK = false;
                                                /*
                                                 * filter out conditions
                                                 */
                                                
                                                // landing page only
                                                if(isset($specialMeta->availability) && $specialMeta->availability == 'Landing Page')
                                                {
                                                    if(isset($_COOKIE['lp_promo']) && $_COOKIE['lp_promo'] == $row->Slug)
                                                    {
                                                        // all good
                                                        $returnLink = '<a href="/promotion/'.$row->Slug.'" class="return-link">View All '.$row->Name.' Weeks</a>';
                                                    }
                                                    //With regards to a 'Landing Page' promo setting...yes, if that is the setup then the discount is only to be presented on that page, otherwise we would set it up as site-wide.
                                                    $skip = true;
                                                }
                                                //blackouts
                                                if(isset($specialMeta->blackout) && !empty($specialMeta->blackout))
                                                {
                                                    foreach($specialMeta->blackout as $blackout)
                                                    {
                                                        if(strtotime($prop->checkIn) >= strtotime($blackout->start) && strtotime($prop->checkIn) <= strtotime($blackout->end))
                                                        {
                                                            $skip = true;
                                                        }
                                                    }
                                                }
                                                //resort blackout dates
                                                if(isset($specialMeta->resortBlackout) && !empty($specialMeta->resortBlackout))
                                                {
                                                    foreach($specialMeta->resortBlackout as $resortBlackout)
                                                    {
                                                        //if this resort is in the resort blackout array then continue looking for the date
                                                        if(in_array($prop->RID, $resortBlackout->resorts))
                                                        {
                                                            if(strtotime($prop->checkIn) >= strtotime($resortBlackout->start) && strtotime($prop->checkIn) <= strtotime($resortBlackout->end))
                                                            {
                                                                $skip = true;
                                                            }
                                                        }
                                                    }
                                                }
                                                //resort specific travel dates
                                                if(isset($specialMeta->resortTravel) && !empty($specialMeta->resortTravel))
                                                {
                                                    foreach($specialMeta->resortTravel as $resortTravel)
                                                    {
                                                        //if this resort is in the resort travel array then continue looking for the date
                                                        if(in_array($prop->RID, $resortTravel->resorts))
                                                        {
                                                            if(strtotime($prop->checkIn) >= strtotime($resortTravel->start) && strtotime($prop->checkIn) <= strtotime($resortTravel->end))
                                                            {
                                                                //all good
                                                            }
                                                            else
                                                            {
                                                                $skip = true;
                                                            }
                                                        }
                                                    }
                                                }
                                                
                                                if((isset($specialMeta->beforeLogin) && $specialMeta->beforeLogin == 'Yes') && !is_user_logged_in())
                                                    $skip = true;
                                                    if(strpos($row->SpecUsage, 'customer') !== false)//customer specific
                                                    {
                                                        if(isset($cid))
                                                        {
                                                            $specCust = (array) json_decode($specialMeta->specificCustomer);
                                                            if(!in_array($cid, $specCust))
                                                            {
                                                                $skip = true;
                                                            }
                                                        }
                                                        else
                                                            $skip = true;
                                                    }
                                                    //transaction type
                                                    if(in_array('ExchangeWeek', $transactionType) || !in_array('BonusWeek', $transactionTypes))
                                                    {
                                                        if(!in_array($prop->WeekType, $transactionTypes))
                                                        {
                                                            $skip = true;
                                                        }
                                                    }
                                                    
                                                    //usage region
                                                    if(isset($specialMeta->usage_region) && !empty($specialMeta->usage_region))
                                                    {
                                                        $usage_regions = json_decode($specialMeta->usage_region);
                                                        foreach($usage_regions as $usage_region)
                                                        {
                                                            $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE id='".$usage_region."'";
                                                            $excludeLftRght = $wpdb->get_row($sql);
                                                            $excleft = $excludeLftRght->lft;
                                                            $excright = $excludeLftRght->rght;
                                                            $sql = "SELECT id FROM wp_gpxRegion WHERE lft>=".$excleft." AND rght<=".$excright;
                                                            $usageregions = $wpdb->get_results($sql);
                                                            if(isset($usageregions) && !empty($usageregions))
                                                            {
                                                                foreach($usageregions as $usageregion)
                                                                {
                                                                    $uregionsAr[] = $usageregion->id;
                                                                }
                                                            }
                                                            
                                                        }
                                                        if(!in_array($prop->gpxRegionID, $uregionsAr))
                                                        {
                                                            $skip = true;
                                                        }
                                                        else
                                                        {
                                                            $regionOK = true;
                                                        }
                                                    }
                                                    
                                                    //usage resort
                                                    if(isset($specialMeta->usage_resort) && !empty($specialMeta->usage_resort))
                                                    {
                                                        if(!in_array($prop->RID, $specialMeta->usage_resort))
                                                        {
                                                            if(isset($regionOK) && $regionOK == true)//if we set the region and it applies to this resort then the resort doesn't matter
                                                            {
                                                                //do nothing
                                                            }
                                                            else
                                                            {
                                                                $skip = true;
                                                            }
                                                        }
                                                    }
                                                    
                                                    if(isset($specialMeta->minWeekPrice) && !empty($specialMeta->minWeekPrice))
                                                    {
                                                        if($prop->WeekType == 'ExchangeWeek')
                                                            $skip = true;
                                                            
                                                            if($prop->Price < $specialMeta->minWeekPrice)
                                                                $skip = true;
                                                    }
                                                    //useage DAE
                                                    if(isset($specialMeta->useage_dae) && !empty($specialMeta->useage_dae))
                                                    {
                                                        //Only show if OwnerBusCatCode = DAE AND StockDisplay = ALL or GPX
                                                        //if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx') && strtolower($prop->OwnerBusCatCode) == 'dae')
                                                        if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx' || strtolower($prop->StockDisplay) == 'usa gpx') && (strtolower($prop->OwnerBusCatCode) == 'dae' || strtolower($prop->OwnerBusCatCode) == 'usa dae'))
                                                        {
                                                            // we're all good -- these are the only properties that should be displayed
                                                        }
                                                        else
                                                        {
                                                            $skip = true;
                                                        }
                                                    }
                                                    
                                                    //exclusions
                                                    
                                                    //exclude DAE
//                                                     if((isset($specialMeta->exclude_dae) && !empty($specialMeta->exclude_dae)) || (isset($specialMeta->exclusions) && $specialMeta->exclusions == 'dae'))
//                                                     {
//                                                         //If DAE selected as an exclusion:
//                                                         //- Do not show inventory to use unless
//                                                         //--- Stock Display = GPX or ALL
//                                                         //AND
//                                                         //---OwnerBusCatCode=GPX
//                                                         //if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx') && strtolower($prop->OwnerBusCatCode) == 'gpx')
//                                                         if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx' || strtolower($prop->StockDisplay) == 'usa gpx') && (strtolower($prop->OwnerBusCatCode) == 'gpx' || strtolower($prop->OwnerBusCatCode) == 'usa gpx'))
//                                                         {
//                                                             //all good we can show these properties
//                                                         }
//                                                         else
//                                                         {
//                                                             $skip = true;
//                                                         }
//                                                     }
                                                    
                                                    //exclude resorts
                                                    if(isset($specialMeta->exclude_resort) && !empty($specialMeta->exclude_resort))
                                                    {
                                                        if(in_array($prop->RID, $specialMeta->exclude_resort))
                                                        {
                                                                $skip = true;
                                                                break;
                                                        }
                                                    }
                                                    
                                                    //exclude regions
                                                    if(isset($specialMeta->exclude_region) && !empty($specialMeta->exclude_region))
                                                    {
                                                        $exclude_regions = json_decode($specialMeta->exclude_region);
                                                        foreach($exclude_regions as $exclude_region)
                                                        {
                                                            $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE id='".$exclude_region."'";
                                                            $excludeLftRght = $wpdb->get_row($sql);
                                                            $excleft = $excludeLftRght->lft;
                                                            $excright = $excludeLftRght->rght;
                                                            $sql = "SELECT * FROM wp_gpxRegion WHERE lft>=".$excleft." AND rght<=".$excright;
                                                            $excregions = $wpdb->get_results($sql);
                                                            if(isset($excregions) && !empty($excregions))
                                                            {
                                                                foreach($excregions as $excregion)
                                                                {
                                                                    if($excregion->id == $prop->gpxRegionID)
                                                                    {
                                                                        $skip = true;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    
                                                    //exclude home resort
                                                    if(isset($specialMeta->exclusions) && $specialMeta->exclusions == 'home-resort')
                                                    {
                                                        if(isset($usermeta) && !empty($usermeta))
                                                        {
                                                            $ownresorts = array('OwnResort1', 'OwnResort2', 'OwnResort3');
                                                            foreach($ownresorts as $or)
                                                            {
                                                                if(isset($usermeta->$or))
                                                                    if($usermeta->$or == $prop->ResortName)
                                                                        $skip = true;
                                                            }
                                                        }
                                                    }
                                                    //                             if(isset($specialMeta->exclusions))
                                                    //                             {
                                                    //                                 switch ($specialMeta->exclusions)
                                                    //                                 {
                                                    //                                     case 'resort':
                                                    //                                         if(isset($specialMeta->exclude_resort))
                                                    //                                             foreach($specialMeta->exclude_resort as $exc_resort)
                                                    //                                             {
                                                    //                                                 if($exc_resort == $prop->RID)
                                                    //                                                 {
                                                    //                                                     $skip = true;
                                                    //                                                     break 2;
                                                    //                                                 }
                                                    //                                             }
                                                    //                                         break;
                                                    
                                                    //                                     case 'region':
                                                    //                                         if(isset($specialMeta->exclude_region))
                                                    //                                         {
                                                    //                                             //get all sub regions
                                                    //                                             $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE id='".$specialMeta->exclude_region."'";
                                                    //                                             $excludeLftRght = $wpdb->get_row($sql);
                                                    //                                             $excleft = $excludeLftRght->lft;
                                                    //                                             $excright = $excludeLftRght->rght;
                                                    //                                             $sql = "SELECT * FROM wp_gpxRegion WHERE lft>=".$excleft." AND rght<=".$excright;
                                                    //                                             $excregions = $wpdb->get_results($sql);
                                                    //                                             if(isset($excregions) && !empty($excregions))
                                                    //                                             {
                                                    //                                                 foreach($excregions as $excregion)
                                                    //                                                 {
                                                    //                                                     if($excregion->id == $prop->gpxRegionID)
                                                    //                                                     {
                                                    //                                                         $skip = true;
                                                    //                                                     }
                                                    //                                                 }
                                                    //                                             }
                                                    //                                         }
                                                    //                                         break;
                                                    //                                 }
                                                    //                             }
                                                    
                                                    //lead time
                                                    $today = date('Y-m-d');
                                                    if(isset($specialMeta->leadTimeMin) && !empty($specialMeta->leadTimeMin))
                                                    {
                                                        $ltdate = date('Y-m-d', strtotime($prop->checkIn." -".$specialMeta->leadTimeMin." days"));
                                                        if($today > $ltdate)
                                                            $skip = true;
                                                    }
                                                    
                                                    if(isset($specialMeta->leadTimeMax) && !empty($specialMeta->leadTimeMax))
                                                    {
                                                        $ltdate = date('Y-m-d', strtotime($prop->checkIn." -".$specialMeta->leadTimeMax." days"));
                                                        if($today < $ltdate)
                                                            $skip = true;
                                                    }
                                                    
                                                    
                                                    if(!$skip)
                                                    {
                                                        if(isset($rmExclusiveWeek[$prop->weekId]) && !empty($rmExclusiveWeek[$prop->weekId]))
                                                        {
                                                            unset($rmExclusiveWeek[$prop->weekId]);
                                                        }
                                                        $discount = $row->Amount;
                                                        $discountType = $specialMeta->promoType;
                                                        if($discountType == 'Pct Off')
                                                            $prop->specialPrice = number_format($prop->Price*(1-($discount/100)), 2);
                                                            elseif($discountType == 'Dollar Off')
                                                            $prop->specialPrice = $prop->Price-$discount;
                                                            elseif($discount < $prop->Price)
                                                            $prop->specialPrice = $discount;
                                                            if($prop->specialPrice < 0)
                                                                $prop->specialPrice = '0.00';
                                                                if(isset($specialMeta->icon))
                                                                    $prop->specialicon = $specialMeta->icon;
                                                                    if(isset($specialMeta->desc))
                                                                        $prop->specialdesc = $specialMeta->desc;
                                                                        $prop->special = (object) array_merge((array) $special, (array) $specialMeta);
                                                    }
                                                    
                                            }
                            }
                        //remove any exclusive weeks
                        if(isset($rmExclusiveWeek[$prop->weekId]) && !empty($rmExclusiveWeek[$prop->weekId]))
                        {
                            unset($props[$propKey]);
                            continue;
                        }
                        $checkFN[] = $prop->gpxRegionID;
                        $regions[$prop->gpxRegionID] = $prop->gpxRegionID;
                        $resorts[$prop->ResortID]['resort'] = $prop;
                        $resorts[$prop->ResortID]['props'][$datasort] = $prop;
                        $propPrice[$datasort] = $prop->WeekPrice;
                        $datasort++;
                }
                $filterNames = array();
                if(isset($checkFN) && !empty($checkFN))
                {
                    foreach($checkFN as $fn)
                    {
                        $sql = "SELECT id, name FROM wp_gpxRegion
                                WHERE id='".$fn."'";
                        $fnRows = $wpdb->get_results($sql);
                        
                        foreach($fnRows as $fnRow)
                        {
                            if($fnRow->name != 'All')
                                $filterNames[$fnRow->id] = $fnRow->name;
                        }
                    }
                }
                asort($filterNames);
            }
            
            if(isset($resorts) && isset($_SESSION['searchSessionID']))
                $savesearch = save_search($usermeta, $_POST, 'search', $resorts);
                //     }
    //     else
        //     {
        //         $loginalert = true;
        //         $resorts = array();
        //     }
    //get a list of restricted gpxRegions
    $sql = "SELECT id, lft, rght FROM wp_gpxRegion WHERE name='Southern Coast (California)'";
    $restLRs = $wpdb->get_results($sql);
    foreach($restLRs as $restLR)
    {
        $sql = "SELECT id FROM wp_gpxRegion WHERE lft BETWEEN ".$restLR->lft." AND ".$restLR->rght;
        $restricted = $wpdb->get_results($sql);
        foreach($restricted as $restrict)
        {
            $restrictIDs[$restrict->id] = $restrict->id;
        }
    }
    include('templates/sc-result.php');
}

add_shortcode('gpx_result_page', 'gpx_result_page_sc');

add_shortcode('gpx_insider_week_page', 'gpx_insider_week_page_sc');

function gpx_resort_result_page_sc()
{
    global $wpdb;
    if(isset($_GET['select_region']))
    {
        //         $sql = "SELECT name FROM wp_gpxRegion WHERE RegionID='".$_GET['select_region']."'";
        //         $row = $wpdb->get_row($sql);
        
        //         $regionName = $row->name;
        //         $sql = "SELECT name, lft, rght, id  FROM wp_gpxRegion WHERE name='".$regionName."'";
        $sql = "SELECT name, lft, rght, id  FROM wp_gpxRegion WHERE id='".$_GET['select_region']."'";
        $regionResults = $wpdb->get_results($sql);
        foreach($regionResults as $row)
        {
            $left = $row->lft;
            $sql = "SELECT a.*, b.lft, b.rght, name FROM wp_resorts a
            INNER JOIN wp_gpxRegion b ON b.id = a.gpxRegionID
            WHERE b.lft BETWEEN ".$row->lft." AND ".$row->rght." AND a.active=1";
            $rs = $wpdb->get_results($sql);
            foreach($rs as $r)
            {
                $results[] = $r;
            }
        }
    }
    elseif(isset($_GET['select_country']))
    {
        $sql = "SELECT MIN(lft) as lft, MAX(rght) as rght, a.id FROM wp_gpxRegion a
                INNER JOIN wp_daeRegion b ON b.id=a.RegionID
                WHERE b.CountryID='".$_GET['select_country']."'";
        $row = $wpdb->get_row($sql);
        $left = $row->lft;
        $sql = "SELECT a.*, b.lft, b.rght, name FROM wp_resorts a
        INNER JOIN wp_gpxRegion b ON b.id = a.gpxRegionID
        WHERE b.lft BETWEEN ".$row->lft." AND ".$row->rght." AND a.active=1";
        $results = $wpdb->get_results($sql);
    }
    else
    {
        $sql = "SELECT MIN(lft) as lft, MAX(rght) as rght FROM wp_gpxRegion";
        $row = $wpdb->get_row($sql);
        $left = $row->lft;
        $sql = "SELECT a.*, b.lft, b.rght, name FROM wp_resorts a
        INNER JOIN wp_gpxRegion b ON b.id = a.gpxRegionID
        WHERE b.lft BETWEEN ".$row->lft." AND ".$row->rght." AND a.active=1";
        $results = $wpdb->get_results($sql);
    }
    
    foreach($results as $result)
    {
        $subregion = array();
        $weektypes = array('', 'null', 'All');
        $parentLft = '';
        $filterCities[$result->gpxRegionID] = $result->name;
        $sql = "SELECT type FROM wp_room WHERE resort='".$result->ResortID."' AND active='1'";
        $rows = $wpdb->get_results($sql);
        $result->propCount = count($rows);
        //         echo '<pre>'.print_r($result->resortName." ".$result->propCount, true).'</pre>';
        foreach($rows as $row)
        {
            $weektypes[$row->WeekType] = $row->WeekType;
        }
        $result->WeekType = $weektypes;
        $result->ResortType = json_encode($weektypes);
        $subregion[] = $result->gpxRegionID;
        while($parentLft > $left)
        {
            $sql = "SELECT id, lft, name FROM wp_gpxRegion WHERE id='".$left."'";
            $srow = $wpdb->get_row($sql);
            $subregion[] = $srow->id;
            $parentLft = $row->lft;
            $filterCities[$srow->id] = $srow->name;
        }
        $result->SubRegion = json_encode($subregion);
        asort($filterCities);
        $result->filterCities = json_encode($filterCities);
    }
    $resorts = $results;
    
    function compareCount($a, $b) {
        if($a->propCount == $b->propCount) {
            return 0;
        }
        return ($a->propCount > $b->propCount) ? -1 : 1;
    }
    
    usort($resorts, 'compareCount');
    
    $cid = get_current_user_id();
    
    if(isset($_COOKIE['switchuser']))
        $cid = $_COOKIE['switchuser'];
        
        include('templates/sc-resort-result.php');
}
add_shortcode('gpx_resort_result_page', 'gpx_resort_result_page_sc');

function gpx_resort_availability()
{
    $destination = $_REQUEST['resortid'];
    $paginate = '';
    if(isset($_REQUEST['limitstart']))
    {
        $paginate['limitCount'] = '10000';
        $paginate['limitStart'] = $_REQUEST['limitstart'];
        if(isset($_REQUEST['limitcount']) && $_REQUEST['limitcount'] > 0)
            $paginate['limitCount'] = $_REQUEST['limitcount'];
    }
    $html = gpx_result_page_sc($destination, $paginate);

    $return = array('html'=>$html);
    
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_resort_availability","gpx_resort_availability");
add_action("wp_ajax_nopriv_gpx_resort_availability", "gpx_resort_availability");

/**
 * GPX Promo Page Shortcode
 * 
 * Displays promo page results
 * Uses url to create a varable. The variable is used to query the wp_specials table to retrieve the promo.
 * Then we retreive all of the inventory that could apply based on a basic inventory query followed by filtering the
 * results based on conditions established when the promo is created.
 * return html
 */
function gpx_promo_page_sc()
{
    global $wpdb;
    
    $tstart = time(true);
    
    $baseExchangePrice = get_option('gpx_exchange_fee');
    
    $joinedTbl = map_dae_to_vest_properties();
    
    //     $sql = "SELECT * FROM wp_properties a
    //                 INNER JOIN wp_resorts b ON a.resortJoinID=b.id
    //                 WHERE b.featured=1
    //                     AND a.active = 1
    //                 AND b.active = 1
    //                         AND IsAdvanceNotice = 'false'
    //                     AND a.WeekPrice != ' $'";
    //     $featuredprops = $wpdb->get_results($sql);
    
    //are there exlusive weeks that we need to take into account?
    $sql = "SELECT Properties FROM wp_specials WHERE active=1";
    $results = $wpdb->get_results($sql);
    foreach($results as $result)
    {
        $sm = stripslashes_deep( json_decode($result->Properties) );
        if(!empty($sm->exclusiveWeeks))
        {
            $exp = explode(",",$sm->exclusiveWeeks);
            foreach($exp as $ex)
            {
                $exrmExclusiveWeek[$ex] = $ex;
            }
        }
    }
    $cid = get_current_user_id();
    
    if(isset($_COOKIE['switchuser']))
        $cid = $_COOKIE['switchuser'];
        if(isset($cid) && !empty($cid))
        {
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
        }
        
        foreach($featuredprops as $featuredprop)
        {
            $featuredresorts[$featuredprop->ResortID]['resort'] = $featuredprop;
            $featuredresorts[$featuredprop->ResortID]['props'][] = $featuredprop;
        }
        $todayDT = date("Y-m-d 00:00:00");
        $promo = get_query_var('promo');
        if(!empty($promo))
        {    
            //check to see if this is a master promo
            $sql = "SELECT id FROM wp_specials WHERE Slug='".$promo."' AND active=1";
            $ismaster = $wpdb->get_row($sql);
            
            $sql = "SELECT * FROM wp_specials b WHERE master='".$ismaster->id."' and b.Active=1";
            $frommasters = $wpdb->get_results($sql);
//                     if(get_current_user_id() == 5)
//                     echo '<pre>'.print_r(count($frommasters), true).'</pre>';
            //if there is more than one result then this is a master promo
            if(count($frommasters) > 0)
            {
                $sql = "SELECT * FROM wp_specials b WHERE master='".$ismaster->id."' OR b.Slug='".$promo."' AND b.Active=1";
            }
            else
            {
                $sql = "SELECT * FROM wp_specials b
                WHERE b.Slug='".$promo."'
                AND b.Active=1";
            }
            
            
//                   if(get_current_user_id() == 5)
//                   echo '<pre>'.print_r($sql, true).'</pre>';
            
        }
        else
        {
            $sql = "SELECT * FROM wp_specials b
            WHERE b.showIndex='Yes'
            AND (StartDate <= '".$todayDT."' AND EndDate >= '".$todayDT."')
            AND b.Active=1";
        }
            
        $specials = $wpdb->get_results($sql);

        
        
        if(isset($_REQUEST['debug_special']))
        {
//             echo '<pre>'.print_r($specials, true).'</pre>';
        }
        
                $wheres = array();
                $datewheres = array();
                foreach($specials as $specialK=>$special)
                {
                    //if this is a coupon then we want to change the promo amount to $0
                    if(strtolower($special->Type) == 'coupon')
                    {
                        $special->Amount = 0;
                    }
                    
                    $specialMeta = stripslashes_deep( json_decode($special->Properties) );
                       
                    //are we reqired to be logged in in order to see this promo?
                    //         if($specialMeta->beforeLogin == 'Yes' && !is_user_logged_in())
                    //         {
                    //             unset($specials[$specialK]);
                    //             continue;
                    //         }
                    
                    //is this promo only available on the landing page?
                    if(isset($specialMeta->availability) && $specialMeta->availability == 'Landing Page')
                    {
                        $lpCookie = $special->Slug;
                        $lpSPID = $special->id;
                    }
                    
                    $today = date('Y-m-d');
                    $startpromo = date('Y-m-d', strtotime($special->StartDate));
                    $endpromo = date('Y-m-d', strtotime($special->EndDate));
                    if(($today <= $endpromo) && ($today >= $startpromo))
                    {
                        
                        if($specialMeta->usage == 'any')
                        {
//                             $wheres[$special->id][] = 'a.id > 0';
                        }
                        else
                        {
                            if(isset($specialMeta->usage_region) && !empty($specialMeta->usage_region))
                            {
                                $allRegions = implode(",", json_decode($specialMeta->usage_region));
                                
                                $sql = "SELECT name, lft, rght FROM wp_gpxRegion
                            WHERE id IN (".$allRegions.")";
                                $ranges = $wpdb->get_results($sql);
                                if(!empty($ranges))
                                {
                                        foreach($ranges as $range)
                                        {
                                            $sql = "SELECT id FROM wp_gpxRegion
                                                WHERE lft BETWEEN ".$range->lft." AND ".$range->rght."
                                                ORDER BY lft ASC";
                                            $rows = $wpdb->get_results($sql);
                                            foreach($rows as $row)
                                            {
                                                $wheres[$special->id][] = "b.GPXRegionID='".$row->id."'";
                                            }
                                        }
                                }
                            }
                            //usage resort
                            if(isset($specialMeta->usage_resort) && !empty($specialMeta->usage_resort))
                            {
                                $usageResorts = explode(json_decode($specialMeta->usage_resort));
                                if(empty($useageResorts))
                                    $usageResorts = $specialMeta->usage_resort;
                                    foreach($usageResorts as $usageResort)
                                    {
                                        $wheres[$special->id][] = "b.id='".$usageResort."'";
                                    }
                            }
                        }
                        
                        if(isset($specialMeta->travelStartDate) && !empty($specialMeta->travelStartDate))
                        {
                            $start = date('Y-m-d', strtotime($specialMeta->travelStartDate));
                            $end = date('Y-m-d', strtotime($specialMeta->travelEndDate));
                            $datewheres[$special->id] = " AND (check_in_date BETWEEN '".$start."' AND '".$end."')";
                            
                        }
                        
                        $discount[$special->id] = $special->Amount;
                        
                        //only pull the specific transaction type
                        
                        //swicth the transaction type and upsell options between the original text and new array
                        if(is_array($specialMeta->transactionType))
                            $ttArr = $specialMeta->transactionType;
                            else
                                $ttArr = array($specialMeta->transactionType);
                                if(is_array($specialMeta->upsellOptions))
                                    $uoArr = $specialMeta->upsellOptions;
                                    else
                                        $uoArr = array($specialMeta->upsellOptions);
                                        
                                        foreach($ttArr as $tt)
                                        {
                                            switch ($tt)
                                            {
                                                case 'upsell':
                                                    $ttWhere[$special->id] = '';
                                                    if(in_array('CPO', $uoArr) || in_array('Upgrade', $uoArr))
                                                        $ttWhereArr['exchange'] = " a.type = '1' OR a.type = '3'";
                                                    
                                                        
                                                    break;
                                                        
                                                case 'All':
                                                    $ttWhere[$special->id] = '';
                                                    break;
                                                    
                                                case 'any':
                                                    $ttWhere[$special->id] = '';
                                                    break;
                                                case 'ExchangeWeek':
                                                    $ttWhereArr['exchange'] = " a.type = '1' OR a.type = '3'";
                                                    break;
                                                    
                                                case 'BonusWeek':
                                                    $ttWhereArr['bonus'] = " a.type = '2' OR a.type = '3'";
                                                    break;
                                                    
                                            }
                                        }
                                        $ttWhere[$special->id] = ' ';
                                        //if(isset($ttWhereArr) && !empty($ttWhereArr))
                                            //$ttWhere[$special->id] = " AND (".implode(" OR ", $ttWhereArr).") ";
                    }
                    else
                    {
                        unset($specials[$specialK]);
                    }
                    
                    //add the exclude options to the query
                    //exclude region
                    if(isset($specialMeta->exclude_region) && !empty($specialMeta->exclude_region))
                    {
                        $allRegions = implode(",", json_decode($specialMeta->exclude_region));
                        $sql = "SELECT name, lft, rght FROM wp_gpxRegion
                        WHERE id IN (".$allRegions.")";
                        $ranges = $wpdb->get_results($sql);
                        if(!empty($ranges))
                        {
                            foreach($ranges as $range)
                            {
                                $sql = "SELECT id FROM wp_gpxRegion
                                            WHERE lft BETWEEN ".$range->lft." AND ".$range->rght."
                                            ORDER BY lft ASC";
                                $rows = $wpdb->get_results($sql);
                                foreach($rows as $row)
                                {
                                    $excludeRegion[$special->id][] = $row->id;
                                }
                            }
                        }
                        if(isset($excludeRegion[$special->id]) && !empty($excludeRegion[$special->id]))
                        {
                            $whereExcludeRegions[$special->id] = ' b.GPXRegionID NOT IN ("'.implode('","', $excludeRegion[$special->id]).'")';
                        }
                    }
                    //exclude resort
                    if(isset($specialMeta->eclude_resort) && !empty($specialMeta->eclude_resort))
                    {
                        $usageResorts = explode(json_decode($specialMeta->eclude_resort));
                        if(empty($useageResorts))
                        {
                            $excludeResorts[$special->id][] = $specialMeta->eclude_resort;
                        }
                        foreach($usageResorts as $usageResort)
                        {
                            $excludeResorts[$special->id][] = $usageResort;
                        }
                        if(isset($excludeResorts[$special->id]) && !empty($excludeResorts[$special->id]))
                        {
                            $whereExcludeResorts[$special->id] = ' b.id NOT IN ("'.implode('","', $excludeResorts[$special->id]).'")';
                        }
                        
                    }
                    //exclude dae
//                     if(isset($specialMeta->exclude_dae) && $specialMeta->exclude_dae == '1')
//                     {
// //                         $whereDAEExclude[$special->id] = " AND ((StockDisplay LIKE 'ALL' OR StockDisplay LIKE 'USA GPX') AND OwnerBusCatCode LIKE 'USA GPX')";
//                     }
                    
                }
// echo '<script>console.log("count_specials: '.count($specials).'");</script>';
                if(count($specials) > 0)
                {
                    
                    $special = $specials[0];

                    //has this been cached?
//                     $sql = "SELECT result_cache FROM wp_gpx_results_cache WHERE result_key='".$special->id."' and result_datetime > '".date('Y-m-d H:i:s', strtotime('-5 minutes'))."'";
//                     $cache = $wpdb->get_row($sql);
                    
                    if(isset($_REQUEST['cache_debug']))
                    {
                        echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                    }
                    $cache = '';
                    if(!empty($cache))
                    {
                        $cacheData = json_decode(base64_decode($cache));
                        extract($cacheData);
                    }
                    else
                    {
//                     foreach($specials as $special)
//                     {
                        $datewhere = '';
                        if(!empty($datewheres[$special->id]))
                        {
                            $datewhere = $datewheres[$special->id];
                        }
                        
                        // create $specialMeta from Properties
                        $specialMeta = stripslashes_deep( json_decode($special->Properties) );
                        $special->imploded_transtype = implode('|',$specialMeta->transactionType); // for matching   
    
                        if(!empty($wheres[$special->id]))
                        {
                            $where = "(".implode(" OR ", $wheres[$special->id]).") ". $datewhere;
                        }
                        else
                        {
                            $where = preg_replace('/AND/', "", $datewhere, 1);
                        }
                                //$where .= $ttWhere[$special->id]; 		// DOESN'T LIMIT WeekType !!
                                
                                if(isset($whereExcludeRegions[$special->id]) && !empty($whereExcludeRegions[$special->id]))
                                {
                                    $where .= " AND".$whereExcludeRegions[$special->id];
                                }
                                if(isset( $whereExcludeResorts[$special->id]) && !empty( $whereExcludeResorts[$special->id]))
                                {
                                    $where .= " AND".$whereExcludeResorts[$special->id];
                                }
                                if(isset( $whereDAEExclude[$special->id]) && !empty( $whereDAEExclude[$special->id]))
                                {
                                    $where .= $whereDAEExclude[$special->id];
                                }
                            $where .= "  AND a.active=1 AND b.active=1";
                            
                            //$where .= " AND (a.country != 'Australia')";
                            
                            $sql = "SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinResort']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias'].".id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                WHERE ".$where."
                AND a.active=1 and b.active=1 AND a.active_rental_push_date != '2030-01-01'
                GROUP BY PID
                ORDER BY featured DESC";
                            $props_rows = $wpdb->get_results($sql); 
                        $sanity_cnt = 0;
                            // MOD: first iteration, convert props_rows to props[$p->resortId] (for removals)
                            foreach($props_rows as $p)
                            {
                            	// lets clear the easy stuff
                            	
                            	  // REMOVE unmatched WeekType 
                            	  
                                $pwt = [];
                            	 	switch($p->WeekType)
                            	 	{
                            	 		case '1':
                            	 		    $pwt[] = 1;
                            	 			if(strpos(implode('|',$specialMeta->transactionType),'ExchangeWeek')===FALSE && strpos(implode('|',$specialMeta->transactionType),'any')===FALSE)
                            	 			{
                            	 			    continue;
                            	 			}
                            	 		break;
                            	 		
                            	 		case '2':
                            	 		    $pwt[] = 2;
                            	 			if(strpos(implode('|',$specialMeta->transactionType),'BonusWeek')===FALSE && strpos(implode('|',$specialMeta->transactionType),'any')===FALSE)
                            	 			{
                            	 			    continue;
                            	 			}
                            	 		break;
                            	 		
                            	 		default:
                            	 		    $pwt[] = 3;
//                             	 		    $pwt[] = 2;
                            	 		break;
                            	 	}
                            		
                            	foreach($pwt as $weekType)
                            	{
                            	    $p->week_date_size = $p->resortId.'='.strtotime($p->checkIn).'='.$weekType.'='.str_replace('/', '', $p->Size);
                                    $pCnt[$p->week_date_size][] = 1;
                                    $p->prop_count = array_sum($pCnt[$p->week_date_size]);
                                    $props[$p->ResortID][$p->week_date_size] = $p;
                                    $sanity_cnt++;
                            	}
                                $theseResorts[$p->ResortID] = $p->ResortID;
                            }
							
							$whichMetas = [
							    'ExchangeFeeAmount',
							    'RentalFeeAmount',
							    'images',
							];
							
							// store $resortMetas as array
							$sql = "SELECT * FROM wp_resorts_meta WHERE ResortID IN ('".implode("','", $theseResorts)."') AND meta_key IN ('".implode("','", $whichMetas)."')";
                            $query = $wpdb->get_results($sql, ARRAY_A);
                            
                            foreach($query as $thisk=>$thisrow)
                            {                            
                            	$this['rmk'] = $thisrow['meta_key'];
                            	$this['rmv'] = $thisrow['meta_value'];
                            	$this['rid'] = $thisrow['ResortID'];
                            	
                            	$resortMetas[$this['rid']][$this['rmk']] = $this['rmv'];
                            	
                            	// image
                                    if(!empty($resortMetas[$this['rid']]['images']))
                                    {
                                        $resortImages = json_decode($resortMetas[$this['rid']]['images'], true);
                                        $oneImage = $resortImages[0];
                                        
                                        //$prop->ImagePath1 = $oneImage['src'];
                                        
                                    // store items for $prop in ['to_prop'] // extract in loop
//                                         $resortMetas[$this['rid']]['to_prop']['ImagePath1'] = $oneImage['src'];
                                        $resortMetas[$this['rid']]['ImagePath1'] = $oneImage['src'];
                                        
                                        
                                        unset($resortImages);
                                        unset($oneImage);
                                    }
							}

                            $unsetFilterMost = true;

                            
            		// MAIN LOOP

                            foreach($props as $k=>$pv)
                            {
                                ksort($pv);
                                $npv = array_values($pv);
                                $propKeys = array_keys($npv);
                                $pi = 0;
                                
                                //if this is an ajax request then we need to loop through all of these
//                                 if(!wp_doing_ajax())
//                                 {
//                                     $pi = count($npv) - 1;
//                                 }
//                                 if(isset($_REQUEST['count_debug']))
//                                 {
//                                     echo '<pre>'.print_r($pi, true).'</pre>';
//                                 }
                                $ppi = 0;
                                
                                while($pi < count($npv))
                                {
                                    $propKey = $propKeys[$pi];
                                    $prop = $npv[$pi];
                                    //first we need to set the week type
                                    //if this type is 3 then it's both exchange and rental. Run it as an exchange
                                    if($prop->WeekType == '1')
                                    {
                                        $prop->WeekType = 'ExchangeWeek';
                                    }
                                    elseif($prop->WeekType == '2')
                                    {
                                        $prop->WeekType = 'RentalWeek';
                                    }
                                    else
                                    {
                                        if($prop->forRental)
                                        {
                                            $prop->WeekType = 'RentalWeek';
                                            $prop->Price = $randexPrice[$prop->forRental];
                                        }
                                        else
                                        {
                                            $rentalAvailable = false;
                                            if(empty($prop->active_rental_push_date))
                                            {
                                                if(strtotime($prop->checkIn) < strtotime('+ 6 months'))
                                                {
                                                    $retalAvailable = true;
                                                }
                                            }
                                            elseif(strtotime('NOW') > strtotime($prop->accive_rental_push_date))
                                            {
                                                $rentalAvailable = true;
                                            }
                                            if($rentalAvailable)
                                            {
                                                $nextCnt = count($npv);
                                                $npv[$nextCnt] = $prop;
                                                $npv[$nextCnt]->forRental = $nextCnt;
                                                $npv[$nextCnt]->Price = $prop->Price;
                                                $randexPrice[$nextCnt] = $prop->Price;
                                                //                                     $propKeys[] = $rPropKey;
                                            }
                                            $prop->WeekType = 'ExchangeWeek';
                                        }
                                    }
                                	// extract resort metas to prop -- in this case we are only concerned with the image and week price
                                    if(!empty($resortMetas[$k]))
                                    {
                                    	foreach($resortMetas[$k] as $this['rmk']=>$this['rmv'])
                                    	{
                                    	    if($this['rmk'] == 'ImagePath1')
                                    	    {
                                    	        $prop->$this['rmk'] = $this['rmv'];
                                    	    }
                                    	    else 
                                    	    {
                                    	            //reset the resort meta items
                                    	        $rmk = $this['rmk'];
                                    	            if($rmArr = json_decode($this['rmv'], true))
                                    	            {
                                    	                foreach($rmArr as $rmdate=>$rmvalues)
                                    	                {
                                    	                    $thisVal = '';
                                    	                    $rmdates = explode("_", $rmdate);
                                    	                    if(count($rmdates) == 1 && $rmdates[0] == '0')
                                    	                    {
                                    	                        //do nothing
                                    	                    }
                                    	                    else
                                    	                    {
                                    	                        //changing this to go by checkIn instead of the active date
                                    	                        $checkInForRM = strtotime($prop->checkIn);
                                    	                        if(isset($_REQUEST['resortfeedebug']))
                                    	                        {
                                    	                            $showItems = [];
                                    	                            $showItems[] = 'RID: '.$prop->RID;
                                    	                            $showItems[] = 'PID: '.$prop->PID;
                                    	                            $showItems[] = 'Check In: '.date('m/d/Y', $checkInForRM);
                                    	                            $showItems[] = 'Override Start: '.date('m/d/Y', $rmdates[0]);
                                    	                            $showItems[] = 'Override End: '.date('m/d/Y', $rmdates[1]);
                                    	                            echo '<pre>'.print_r(implode(' -- ', $showItems), true).'</pre>';
                                    	                        }
                                    	                        //check to see if the from date has started
                                    	                        //                                                 if($rmdates[0] < strtotime("now"))
                                    	                        if($rmdates[0] <= $checkInForRM)
                                    	                        {
                                    	                            //this date has started we can keep working
                                    	                        }
                                    	                        else
                                    	                        {
                                    	                            //these meta items don't need to be used
                                    	                            $pi++;
                                    	                            continue;
                                    	                        }
                                    	                        //check to see if the to date has passed
                                    	                        //                                                 if(isset($rmdates[1]) && ($rmdates[1] >= strtotime("now")))
                                    	                        if(isset($rmdates[1]) && ($checkInForRM > $rmdates[1]))
                                    	                        {
                                    	                            //these meta items don't need to be used
                                    	                            $pi++;
                                    	                            continue;
                                    	                        }
                                    	                        else
                                    	                        {
                                    	                            //this date is sooner than the end date we can keep working
                                    	                        }
                                    	                        foreach($rmvalues as $rmval)
                                    	                        {
                                    	                            //do we need to reset any of the fees?
                                    	                            if(array_key_exists($rmk, $rmFees))
                                    	                            {
                                    	                                //set this amount in the object
                                    	                                $prop->$rmk = $rmval;
                                    	                                if(!empty($rmFees[$rmk]))
                                    	                                {
                                    	                                    //if values exist then we need to overwrite
                                    	                                    foreach($rmFees[$rmk] as $propRMK)
                                    	                                    {
                                    	                                        //if this is either week price or price then we only apply this to the correct week type...
                                    	                                        if($rmk == 'ExchangeFeeAmount')
                                    	                                        {
                                    	                                            //$prop->WeekType cannot be RentalWeek or BonusWeek
                                    	                                            if($prop->WeekType == 'BonusWeek' || $prop->WeekType == 'RentalWeek')
                                    	                                            {
                                    	                                                $pi++;
                                    	                                                continue;
                                    	                                            }
                                    	                                        }
                                    	                                        elseif($rmk == 'RentalFeeAmount')
                                    	                                        {
                                    	                                            //$prop->WeekType cannot be ExchangeWeek
                                    	                                            if($prop->WeekType == 'ExchangeWeek')
                                    	                                            {
                                    	                                                $pi++;
                                    	                                                continue;
                                    	                                            }
                                    	                                            
                                    	                                        }
                                    	                                        $prop->$propRMK = preg_replace("/\d+([\d,]?\d)*(\.\d+)?/", $rmval, $prop->$propRMK);
                                    	                                    }
                                    	                                }
                                    	                            }
                                    	                        }
                                    	                    }
                                    	                }
                                    	            }
                                    	            else
                                    	            {
                                    	                $prop->$this['rmk'] = $this['rmv'];
                                    	            }
                                    	    }
                                    	}
                                    }
    
    
    
//                                     $dupsKey = $prop->resortID.strtotime($prop->checkIn).$prop->bedrooms.$prop->sleeps;
                                    
//                                     if(in_array($dupsKey, $isDups))
//                                     {
//                                         $dupCnt = array_count_values($isDups);
//                                         if($dupKnt[$dupsKey] == 2)
//                                         {
//                                             continue;
//                                         }
//                                     }
//                                     else 
//                                     {
//                                         $isDups[] = $dupsKey;
//                                     }
                                    
                                    //skip anything that has an error
                                    $allErrors = [
                                        'checkIn',
                                    ];
                                    
                                    
                                    //validate availablity
    //                                     if($prop->availablity == '2')
    //                                     {
    //                                         //partners shouldn't see this
    //                                         //this should only be available to partners
    //                                         $sql = "SELECT record_id FROM wp_partner WHERE user_id='".$cid."'";
    //                                         $row = $wpdb->get_row($sql);
    //                                         if(!empty($row))
    //                                         {
    //                                             continue;
    //                                         }
    //                                     }
    //                                     if($prop->availablity == '3')
    //                                     {
    //                                         //only partners shouldn't see this
    //                                         //this should only be available to partners
    //                                         $sql = "SELECT record_id FROM wp_partner WHERE user_id='".$cid."'";
    //                                         $row = $wpdb->get_row($sql);
    //                                         if(empty($row))
    //                                         {
    //                                             continue;
    //                                         }
    //                                     }
                                    
                                    foreach($allErrors as $ae)
                                    {
                                        if(empty($prop->$ae) || $prop->$ae == '0000-00-00 00:00:00')
                                        {
                                            $pi++;
                                            continue;
                                        }
                                    }
    //                                 if($prop->WeekType == '3' || $prop->forRental)
    //                                 {
    //                                     //if this checkin date is within 6 months then also run it as a rental
    //                                     if($prop->forRental)
    //                                     {
    //                                         $prop->WeekType = 'RentalWeek';
    //                                     }
    //                                     else
    //                                     {
    //                                         if(strtotime($prop->checkIn) < strtotime('+ 6 months'))
    //                                         {
    //                                             $nextCnt = count($props);
    //                                             $props[$nextCnt] = $props[$k];
    //                                             $props[$nextCnt]->forRental = true;
    //                                             //                                     $propKeys[] = $rPropKey;
    //                                         }
    //                                     }
    //                                 }
    //                                 if($prop->WeekType != 'RentalWeek' || $prop->WeekType == '2')
    //                                 {
    //                                     if($prop->WeekType == '1')
    //                                     {
    //                                         $prop->WeekType = 'ExchangeWeek';
    //                                     }
    //                                     else
    //                                     {
    //                                         $prop->WeekType = 'RentalWeek';
    //                                     }
    //                                 }
    //                                 else
    //                                 {
    //                                     $prop->WeekType = 'ExchangeWeek';
    //                                 }
                                   
                                    if($prop->WeekType == 'ExchangeWeek')
                                    {
                                        $prop->Price = $baseExchangePrice;
                                    }
                                    
                                    $prop->WeekPrice = $prop->Price;
                                    //if we have a featured resort then we don't want to filter by the number of units
                                    if($prop->featured == 1)
                                    {
                                        $unsetFilterMost = false;
                                    }
                                    
    
    
                         	// do something with $specialMeta 
                                    
                                    if(isset($specialMeta->exclusiveWeeks) && !empty($specialMeta->exclusiveWeeks))
                                    {
                                        //if this is an exclusive week then we might need to remove this property
                                        if(in_array($prop->weekId, $exrmExclusiveWeek))
                                        {
                                            $exclusiveWeeks = get_exclusive_weeks($prop, $cid);
                                            if(!empty($exclusiveWeeks))
                                            {
                                                //we returned a result on this week -- we don't need to do anything else becuase it shouldn't be displayed.
                                                //                                         unset($props[$k]);
                                                $pi++;
                                                continue;
                                            }
                                        }
                                        
                                        $exclusiveWeeks = explode(',', $specialMeta->exclusiveWeeks);
                                        if(in_array($prop->weekId, $exclusiveWeeks))
                                        {
                                            $rmExclusiveWeek[$prop->weekId] = $prop->weekId;
                                        }
                                        else
                                        {
                                            //this doesn't apply
                                            unset($prop);
                                            $pi++;
                                            continue;
                                        }
                                    }
    //                                 if($prop->WeekType == 'RentalWeek' && $prop->OwnerBusCatCode == 'GPX' && $prop->StockDisplay == 'DAE')
    //                                 if($prop->WeekType == 'RentalWeek' && ($prop->OwnerBusCatCode == 'GPX' || $prop->OwnerBusCatCode == 'USA GPX') && ($prop->StockDisplay == 'DAE' || $prop->StockDisplay == 'USA DAE'))
    //                                 {
    //                                     unset($prop);
    //                                     continue;
    //                                 }
                                    
                                    $priceint = preg_replace("/[^0-9\.]/", "",$prop->WeekPrice);
                                    if($priceint != $prop->Price)
                                    {
                                        $prop->Price = $priceint;
                                    }
                                    
                                    /*
                                     * filter out conditions
                                     */
                                    $continue = false;
                                    
                                    //blackouts
                                    if(isset($specialMeta->blackout) && !empty($specialMeta->blackout))
                                    {
                                        foreach($specialMeta->blackout as $blackout)
                                        {
                                            if(strtotime($prop->checkIn) >= strtotime($blackout->start) && strtotime($prop->checkIn) <= strtotime($blackout->end))
                                            {
//                                                 unset($props[$k]);
                                                $continue = true;			// ! this is ignored, why is it here?
                                                $pi++;
                                                continue;
                                            }
                                        }
                                    }
                                    //resort blackout dates
                                    if(isset($specialMeta->resortBlackout) && !empty($specialMeta->resortBlackout))
                                    {
                                        foreach($specialMeta->resortBlackout as $resortBlackout)
                                        {
                                            //if this resort is in the resort blackout array then continue looking for the date
                                            if(in_array($prop->RID, $resortBlackout->resorts))
                                            {
                                                if(strtotime($prop->checkIn) >= strtotime($resortBlackout->start) && strtotime($prop->checkIn) <= strtotime($resortBlackout->end))
                                                {
//                                                     unset($props[$k]);
                                                    $continue = true;
                                                    $pi++;
                                                    continue 2;
                                                }
                                            }
                                        }
                                    }
                                    
                                    //resort specific travel dates
                                    if(isset($specialMeta->resortTravel) && !empty($specialMeta->resortTravel))
                                    {
                                        foreach($specialMeta->resortTravel as $resortTravel)
                                        {
                                            //if this resort is in the resort blackout array then continue looking for the date
                                            if(in_array($prop->RID, $resortTravel->resorts))
                                            {
                                                if(strtotime($prop->checkIn) >= strtotime($resortTravel->start) && strtotime($prop->checkIn) <= strtotime($resortTravel->end))
                                                {
                                                    //all good
                                                }
                                                else
                                                {
//                                                     unset($props[$k]);
                                                    $continue = true;
                                                    $pi++;
                                                    continue 2;
                                                }
                                            }
                                        }
                                    }
                                    
                                    
                                    //transaction type
                                    if($specialMeta->transactionType != 'any' && $specialMeta->transactionType != 'upsell')
                                    {
                                        $apwt = $prop->WeekType;
                                        if($apwt == 'RentalWeek')
                                        {
                                            $apwt = 'BonusWeek';
                                        }
                                        if( (is_array($specialMeta->transactionType) && !in_array($apwt, $specialMeta->transactionType)) || (!is_array($specialMeta->transactionType) && $apwt != $specialMeta->transactionType) )
                                        {
//                                             unset($props[$k]);
                                            $pi++;
                                            continue;
                                        }
                                    }
                                    
                                    //week min cost
                                    if(isset($specialMeta->minWeekPrice) && !empty($specialMeta->minWeekPrice))
                                    {
                                        if($prop->WeekType == 'ExchangeWeek')
                                            continue;
                                            
                                            if($prop->Price < $specialMeta->minWeekPrice)
                                            {
                                                $pi++;
                                                continue;
                                            }
                                    }
                                    
                                    if(strpos($special->SpecUsage, 'customer') !== false)//customer specific
                                    {
                                        if(isset($cid))
                                        {
                                            $specCust = (array) json_decode($specialMeta->specificCustomer);
                                            if(!is_user_logged_in())
                                            {
                                                //if this user isn't logged in then we still want to display the results
                                            }
                                            elseif(!in_array($cid, $specCust))
                                            {
//                                                 unset($props[$k]);
                                                $pi++;
                                                continue;
                                            }
                                        }
                                        else
                                        {
//                                             unset($props[$k]);
                                            $pi++;
                                            continue;
                                        }
                                    }
                                    //useage DAE
                                    if(isset($specialMeta->useage_dae) && !empty($specialMeta->useage_dae))
                                    {
                                        //Only show if OwnerBusCatCode = DAE AND StockDisplay = ALL or GPX
//                                         if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx') && strtolower($prop->OwnerBusCatCode) == 'dae')
                                        if((strtolower($prop->StockDisplay) == 'all' || (strtolower($prop->StockDisplay) == 'gpx' || strtolower($prop->StockDisplay) == 'usa gpx')) && (strtolower($prop->OwnerBusCatCode) == 'dae' || strtolower($prop->OwnerBusCatCode) == 'usa dae'))
                                        {
                                            // we're all good -- these are the only properties that should be displayed
                                        }
                                        else
                                        {
//                                             unset($props[$k]);
                                            $continue = true;
                                        }
                                    }
                                    
                                    
                                    //exclusions
                                    
                                    //exclude DAE
//                                     if((isset($specialMeta->exclude_dae) && !empty($specialMeta->exclude_dae)) || (isset($specialMeta->exclusions) && $specialMeta->exclusions == 'dae'))
//                                     {
//                                         //If DAE selected as an exclusion:
//                                         //- Do not show inventory to use unless
//                                         //--- Stock Display = GPX or ALL
//                                         //AND
//                                         //---OwnerBusCatCode=GPX
//                                         //if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx') && strtolower($prop->OwnerBusCatCode) == 'gpx')
//                                         if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx' || strtolower($prop->StockDisplay) == 'usa gpx') && (strtolower($prop->OwnerBusCatCode) == 'gpx' || strtolower($prop->OwnerBusCatCode) == 'usa gpx'))
//                                         {
//                                             //all good we can show these properties
//                                         }
//                                         else
//                                         {
// //                                             unset($props[$k]);
//                                             $continue = true;
//                                         }
//                                     }
                                    
                                    //exclude resorts
                                    if(isset($specialMeta->exclude_resort) && !empty($specialMeta->exclude_resort))
                                    {
                                        if(in_array($prop->RID, $specialMeta->exclude_resort))
                                        {
//                                             unset($props[$k]);
                                            $continue = true;
                                        }
                                    }
                                    
        // !!!!! sql in LOOP !!!
                                    //exclude regions
                                    // we already added this so skip this
//                                     if(isset($specialMeta->exclude_region) && !empty($specialMeta->exclude_region))
//                                     {
//                                         $exclude_regions = json_decode($specialMeta->exclude_region, true);
                                        
//                                         if(array_intersect($exclude_regions, $these_regions))
//                                         {
//                                             continue;
//                                         }
//                                     }
                                    
                                    //exclude home resort
                                    if(isset($specialMeta->exclusions) && $specialMeta->exclusions == 'home-resort')
                                    {
                                        if(isset($usermeta) && !empty($usermeta))
                                        {
                                            $ownresorts = array('OwnResort1', 'OwnResort2', 'OwnResort3');
                                            foreach($ownresorts as $or)
                                            {
                                                if(isset($usermeta->$or))
                                                    if($usermeta->$or == $prop->ResortName)
                                                    {
//                                                         unset($props[$k]);
                                                        $continue = true;
                                                    }
                                            }
                                        }
                                    }
                                    //             if(isset($specialMeta->exclusions))
                                    //             {
                                    //                 switch ($specialMeta->exclusions)
                                    //                 {
                                    //                     case 'resort':
                                    //                         if(isset($specialMeta->exclude_resort))
                                    //                             foreach($specialMeta->exclude_resort as $exc_resort)
                                    //                             {
                                    //                                 if($exc_resort == $prop->ResortID)
                                    //                                 {
                                    //                                     unset($props[$k]);
                                    //                                     $continue = true;
                                    //                                     break 2;
                                    //                                 }
                                    //                             }
                                    //                     break;
                                    
                                    //                     case 'region':
                                    //                         if(isset($specialMeta->exclude_region))
                                    //                         {
                                    //                             //get all sub regions
                                    //                             $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE id='".$specialMeta->exclude_region."'";
                                    //                             $excludeLftRght = $wpdb->get_row($sql);
                                    //                             $excleft = $excludeLftRght->lft;
                                    //                             $excright = $excludeLftRght->rght;
                                    //                             $sql = "SELECT * FROM wp_gpxRegion WHERE lft>=".$excleft." AND rght<=".$excright;
                                    //                             $excregions = $wpdb->get_results($sql);
                                    //                             if(isset($excregions) && !empty($excregions))
                                    //                             {
                                    //                                 foreach($excregions as $excregion)
                                    //                                 {
                                    //                                     if($excregion->id == $prop->gpxRegionID)
                                    //                                     {
                                    //                                         unset($props[$k]);
                                    //                                         $continue = true;
                                    //                                     }
                                    //                                 }
                                    //                             }
                                    //                         }
                                    //                     break;
                                    //                 }
                                    //             }
                                    
                                    //lead time
                                    if(isset($specialMeta->leadTimeMin) && !empty($specialMeta->leadTimeMin))
                                    {
                                        $ltdate = date('Y-m-d', strtotime($prop->checkIn." -".$specialMeta->leadTimeMin." days"));
                                        if($today > $ltdate)
                                        {
//                                             unset($props[$k]);
                                            $pi++;
                                            continue;
                                        }
                                    }
                                    
                                    if(isset($specialMeta->leadTimeMax) && !empty($specialMeta->leadTimeMax))
                                    {
                                        $ltdate = date('Y-m-d', strtotime($prop->checkIn." -".$specialMeta->leadTimeMax." days"));
                                        if($today < $ltdate)
                                        {
//                                             unset($props[$k]);
                                            $pi++;
                                            continue;
                                        }
                                    }
                                    
                                    if(isset($specialMeta->bookStartDate) && !empty($specialMeta->bookStartDate))
                                    {
                                        $bookStartDate = date('Y-m-d', strtotime($specialMeta->bookStartDate));
                                        if($today < $bookStartDate)
                                        {
//                                             unset($props[$k]);
                                            $pi++;
                                            continue;
                                        }
                                    }
                                    
                                    if(isset($specialMeta->bookEndDate) && !empty($specialMeta->bookEndDate))
                                    {
                                        $bookEndDate = date('Y-m-d', strtotime($specialMeta->bookEndDate));
                                        if($today > $bookEndDate)
                                        {
//                                             unset($props[$k]);
                                            $pi++;
                                            continue;
                                        }
                                    }
                                    
                                    if($continue)    // get rid of all this 'continue' - remove prop from proplist
                                    {
//                                         unset($props[$k]);
                                        $pi++;
                                        continue;
                                    }
                                    
                                    else
                                    {
                                        //if exclusive weeks are set here then all conditions have been met we can display it as long as it isn't fenced off somewhere else
                                        if(isset($rmExclusiveWeek[$prop->weekId]) && !empty($rmExclusiveWeek[$prop->weekId]))
                                        {
                                            if(in_array($prop->weekId, $exrmExclusiveWeek))
                                            {
                                                unset($exrmExclusiveWeek[$prop->weekId]);
                                            }
                                            unset($rmExclusiveWeek[$prop->weekId]);
                                        }
                                    }
                                    
                                    //remove any exclusive weeks
                                    if((isset($rmExclusiveWeek[$prop->weekId]) && !empty($rmExclusiveWeek[$prop->weekId])))
                                    {
//                                         unset($props[$k]);
                                        $pi++;
                                        continue;
                                    }
                                    
                                    $prop->special = (object) array_merge((array) $special, (array) $specialMeta);
                                    $discountType = $specialMeta->promoType;
                                    if($specialMeta->transactionType == 'upsell' || in_array('upsell', $specialMeta->transactionType))
                                    {
                                        //we don't want any discount -- just display the results
                                    }
                                    elseif($discountType == 'Pct Off')
                                    {
                                        $prop->specialPrice = number_format($prop->Price*(1-($discount[$special->id]/100)), 2);
                                    }
                                    elseif($discountType == 'Dollar Off')
                                    {
                                        $prop->specialPrice = $prop->Price-$discount[$special->id];
                                    }
                                    elseif($discount[$special->id] < $prop->Price)
                                    {
                                        $prop->specialPrice = $discount[$special->id];
                                    }
                                    if($prop->specialPrice < 0)
                                    {
                                        $prop->specialPrice = '0.00';
                                    }

                                    if(isset($specialMeta->beforeLogin) && $specialMeta->beforeLogin == "Yes")
                                    {
                                        if(!is_user_logged_in())
                                        {
                                            $loginalert = true;
                                            $prop->specialPrice = '';
                                        }
                                    }
                                    //display the properties even when they aren't logged in...
                                    if(!is_user_logged_in())
                                    {
                                        if($specialMeta->beforeLogin == 'No')
                                        {
                                            //do nothing we want to show these prices
                                        }
                                        else
                                        {
                                            $loginalert = true;
                                            $prop->specialPrice = '';
                                        }
                                    }
                                    if(isset($specialMeta->icon))
                                    {
                                        $prop->specialicon = $specialMeta->icon;
                                    }
                                    if(isset($specialMeta->desc))
                                    {
                                        $prop->specialdesc = $specialMeta->desc;
                                    }
                                    
                                    $prop->AllInclusive = '';
                                    $resortFacilities = json_decode($prop->ResortFacilities);
                                    if(in_array('All Inclusive', $resortFacilities) || strpos($prop->HTMLAlertNotes, 'IMPORTANT: All-Inclusive Information') || strpos($prop->AlertNote, 'IMPORTANT: This is an All Inclusive (AI) property.'))
                                    {
                                        $prop->AllInclusive = '6';
                                    }
                                    
                                    $pwt = "b";
                                    if($prop->WeekType == 'ExchangeWeek')
                                    {
                                        $pwt = "a";
                                    }
                                    
                                    $propkeyset = strtotime($prop->checkIn).$pwt.$prop->weekId;
                                    $prop->propkeyset = $propkeyset;
                                    
                                    //if the prop was set already then we need to see if this price is less.
                                    if(array_key_exists($propkeyset, $resorts[$prop->ResortID]['props']))
                                    {
                                        //is this price more than the previous price?  If so we don't want to set the price.
                                        if(str_replace(",", "", str_replace(".00", "", $prop->specialPrice)) >= str_replace(",", "", str_replace(".00", "", $resorts[$prop->ResortID]['props'][$propkeyset]->specialPrice)))
                                        {
                                            $pi++;
                                            continue;
                                        }
                                    }
                                    
                                    //need to add the special back in if the previous propkeyset had a special but this one doesn't
                                    if(!isset($prop->specialPrice) || (isset($prop->SpecialPrice) && empty($prop->specialPrice)))
                                    {
                                        $prop->specialPrice = $prefPropSetDets[$propkeyset]['specialPrice'];
                                        $prop->specialicon = $prefPropSetDets[$propkeyset]['specialicon'];
                                        $prop->specialdesc = $prefPropSetDets[$propkeyset]['specialdesc'];
                                    }
                                    
                                    $checkFN[$prop->gpxRegionID] = $prop->gpxRegionID;
                                    $propsetspecialprice[$propkeyset] = $prop->specialPrice;
                                    $prefPropSetDets[$propkeyset]['specialPrice'] = $prop->specialPrice;
                                    $prefPropSetDets[$propkeyset]['specialicon'] = $prop->specialicon;
                                    $prefPropSetDets[$propkeyset]['specialdesc'] = $prop->specialdesc;
                                    
                                    $propPrice[$propkeyset] = $prop->WeekPrice;
                                    
                                    $resorts[$prop->ResortID]['resort'] = $prop;
                                    
                                    $resorts[$prop->ResortID]['props'][$propkeyset] = $prop;
                                    
                                    $rp[$propkeyset] = $prop;
                                    $resorts[$prop->ResortID]['propopts'][$propkeyset][] = $prop;
                                    
                                    if(isset($_REQUEST['prop_debug']))
                                    {
                                        echo '<pre>'.print_r($resorts[$prop->ResortID]['props'][$propkeyset], true).'</pre>';
                                    }
                                    
                                    // 
                                    $allProps[$prop->ResortID][] = $prop;
                                    $pi++;
                                }
                            }
//                     }
/*
 * Skip cache for now -- we can't load the pages to just anyone!
                       //let's cache this now
                       $toCache = [
                           'resorts'=>$resorts,
                           'checkFN'=>$checkFN,
                           'propsetspecialprice'=>$propsetspecialprice,
                           'prefPropSetDets'=>$prefPropSetDets,
                           'propPrice'=>$propPrice,
                           'propPrice'=>$propPrice,
                           'allProps'=>$allProps,
                       ];
                       
                       $cacheInsert = [
                           'cache_type'=>1, 
                           'result_key'=>$special->id, 
                           'result_datetime'=>date('Y-m-d H:i:s'),
                       ];
                       
                       foreach($cacheInsert as $cwk=>$cwv)
                       {
                           $cacheWheres[] = $cwk." = '".$cwv."'";
                       }
                       
                       $cacheInsert['result_cache'] = base64_encode(json_encode($toCache));
                       
                       $sql = "SELECT id FROM wp_gpx_results_cache WHERE ".implode(' AND ', $cacheWheres);
                       $isCache = $wpdb->get_var($sql);
                       
                       if(isset($_REQUEST['cache_debug']))
                       {
                           echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                           echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                       }
                       
                       if(!empty($isCache))
                       {
                           $wpdb->update('wp_gpx_results_cache', $cacheInsert, array('id'=>$isCache));
                       }
                       else
                       {
                           $wpdb->insert('wp_gpx_results_cache', $cacheInsert);
                       }
                       
                       if(isset($_REQUEST['cache_debug']))
                       {
                           echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                           echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                       }
                       
                       unset($toCache);
*/
                    }
                }

                $filterNames = array();
                if(isset($checkFN) && !empty($checkFN))
                {
                    foreach($checkFN as $fn)
                    {
                        $sql = "SELECT id, name FROM wp_gpxRegion
                            WHERE id='".$fn."'";
                        $fnRows = $wpdb->get_results($sql);
                        
                        foreach($fnRows as $fnRow)
                        {
                            if($fnRow->name != 'All')
                                $filterNames[$fnRow->id] = $fnRow->name;
                        }
                    }
                }
                asort($filterNames);
                //setting the display options...
                foreach($resorts as $rk=>$resort)
                {
                    foreach($resort['propopts'] as $key=>$value)
                    {
                        $propOpts = [
                            'slash'=>'',
                            'icon'=>'',
                            'desc'=>'',
                            'preventhighlight'=>'',
                        ];
                        $propDescs = [];
                        foreach($value as $prop)
                        {
                            if(isset($prop->special->slash) && $prop->special->slash == 'Force Slash')
                            {
                                $propOpts['slash'] = '1';
                            }
                            if(isset($prop->specialicon) && !empty($prop->specialicon))
                            {
                                $propOpts['icon'] = $prop->specialicon;
                            }
                            if(isset($prop->specialdesc))
                            {
                                $propDescs[] = $prop->specialdesc;
                            }
                            if(isset($prop->special->highlight) && $prop->special->highlight == 'Prevent Highlighting')
                            {
                                $propOpts['preventhighlight'] = '1';
                            }
                        }
                        if(!empty($propDescs))
                        {
                            $propOpts['desc'] = implode("\r\n", $propDescs);
                        }
                        foreach($propOpts as $opt=>$v)
                        {
                                $setPropDetails[$key][$opt] = $v;
                        }
                    }
                }
                //     if(isset($usermeta) && !empty($usermeta))
                //     {
                //         if(isset($resorts))
                //             $savesearch = save_search($usermeta, $_POST, 'search', $resorts);
                //     }
                //get a list of restricted gpxRegions
                $sql = "SELECT id, lft, rght FROM wp_gpxRegion WHERE name='Southern Coast (California)'";
                $restLRs = $wpdb->get_results($sql);
                foreach($restLRs as $restLR)
                {
                    $sql = "SELECT id FROM wp_gpxRegion WHERE lft BETWEEN ".$restLR->lft." AND ".$restLR->rght;
                    $restricted = $wpdb->get_results($sql);
                    foreach($restricted as $restrict)
                    {
                        $restrictIDs[$restrict->id] = $restrict->id;
                    }
                }
                include('templates/sc-result.php');
}
add_shortcode('gpx_promo_page', 'gpx_promo_page_sc');

function promo_retrieve_each($specialMeta, $props)
{
}

// REMOVE WP EMOJI
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );


function gpx_view_profile_sc()
{
    global $wpdb;
    
    $cid = get_current_user_id();
 
    if(isset($_COOKIE['switchuser']))
        $cid = $_COOKIE['switchuser'];
    
    $user = get_userdata($cid);
    $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );

    if(!get_user_meta($cid, 'DAEMemberNo', TRUE))
    {
        require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
        $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
        $DAEMemberNo = str_replace("U", "", $user->user_login);
        $user = $gpx->DAEGetMemberDetails($DAEMemberNo, $cid, array('email'=>$usermeta->email));
    }
    
    
    if(empty($usermeta->first_name) && !empty($usermeta->FirstName1))
    {
        $usermeta->first_name = $usermeta->FirstName1;
    }
    
    if(empty($usermeta->last_name) && !empty($usermeta->LastName1))
    {
        $usermeta->last_name = $usermeta->LastName1;
    }
    
    if(empty($usermeta->Email))
    {
        $usermeta->Email = $usermeta->email;
        if(empty($usermeta->Email))
        {
            $usermeta->Email = $usermeta->user_email;
        }
    }
    
    $dayphone = '';
    if(isset($usermeta->DayPhone) && !empty($usermeta->DayPhone) && !is_object($usermeta->DayPhone))
    {
        $dayphone = $user->DayPhone;
    }
    $usermeta->DayPhone = $dayphone;
    
    //set the profile columns
    $profilecols[0] = array(
        array('placeholder'=>"First Name", 'type'=>'text', 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'first_name'), 'required'=>'required'),
        array('placeholder'=>"Last Name", 'type'=>'text', 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'last_name'), 'required'=>'required'),
        array('placeholder'=>"Email", 'type'=>'email', 'type'=>'text', 'class'=>'validate emailvalidate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Email'), 'required'=>'required'),
        array('placeholder'=>"Home Phone", 'type'=>'tel', 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'DayPhone'), 'required'=>'required'),
        array('placeholder'=>"Mobile Phone", 'type'=>'tel', 'class'=>'', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Mobile1'), 'required'=>''),
    );
    $profilecols[1] = array(
        array('placeholder'=>"Street Address", 'type'=>'text', 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Address1'), 'required'=>'required'),
        array('placeholder'=>"City", 'type'=>'text', 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Address3'), 'required'=>'required'),
        array('placeholder'=>"State", 'type'=>'text', 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Address4'), 'required'=>'required'),
        array('placeholder'=>"Zip", 'type'=>'text', 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'PostCode'), 'required'=>'required'),
        array('placeholder'=>"Country", 'type'=>'text', 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Address5'), 'required'=>'required'),
    );
    
    if(isset($_POST['cid']) && $cid = $_POST['cid'])
    {
        foreach($profilecols as $col)
        {
            foreach($col as $data)
            {
                if($data['value']['retrieve'] == 'Email') //update user table
                {
                    $mainData = array(
                        'ID'=>$cid,
                        'user_email'=>$_POST[$data['value']['retrieve']],
                    );
                    wp_update_user($mainData);
                    update_user_meta($cid, $data['value']['retrieve'], $_POST[$data['value']['retrieve']]);
                }
                else //update user meta
                {
                    update_user_meta($cid, $data['value']['retrieve'], $_POST[$data['value']['retrieve']]);
                }
                if($data['value']['retrieve'] == 'user_email')
                {
                   update_user_meta($cid, 'Email', $_POST[$data['value']['retrieve']]); 
                }
            }
        }
        //send to DAE
        require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
        $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
        if(isset($usermeta->DAEMemberNo))
            $update = $gpx->DAEUpdateMemberDetails($usermeta->DAEMemberNo, $_POST);
    }

    $user = get_userdata($cid);
    $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
    
    $sql = "SELECT * FROM wp_gpxMemberSearch WHERE userID='".$cid."'";
    $results = $wpdb->get_results($sql);

    foreach($results as $result)
    {
        $history = json_decode($result->data);
        foreach($history as $key=>$value)
        {
            if(isset($value->week_type))
            {
                $splitKey = explode('-', $key);
                if($splitKey[0] == 'view')
                {
                    $weektype = $value->week_type;
                    if($weektype == 'BonusWeek')
                        $weektype = 'RentalWeek';
//                     $histsetPrice = $value->price;
//                     $histdaePrice = $value->property->Price;
//                     $price = $value->property->WeekPrice;
//                     if($histsetPrice < $histdaePrice)
//                         $price = '<span style="text-decoration: line-through;">'.$value->property->WeekPrice.'</span> '.str_replace($value->property->Price, $histsetPrice, $value->property->WeekPrice);
                    $histout[$weektype][] = array(
                        'weekId'=>'<a href="/booking-path?book='.$value->id.'">'.$value->id.'</a>',
                        'ResortName'=>'<a href="resort-profile/?resortName='.$value->name.'">'.$value->name.'</a>',
                        'Price'=>'<a href="/booking-path?book='.$value->id.'">'.$value->price.'</a>',
                        'checkIn'=>'<a href="/booking-path?book='.$value->id.'">'.$value->checkIn.'</a>',
                        'Size'=>'<a href="/booking-path?book='.$value->id.'">'.$value->beds.'</a>',
                    );
                }
            }
            if(isset($value->ResortName))
            {
                $searched = "N/A";
                if(isset($value->search_month))
                    $searched = $value->search_month;
                if(isset($value->search_year))
                    $searched .= ' '.$value->search_year;
                $histoutresort[] = array(
                    'ResortName'=>'<a href="/resort-profile/?resort='.$value->id.'">'.$value->ResortName.'</a>',
                    'DateViewed'=>'<a href="/resort-profile/?resort='.$value->id.'">'.date("m/d/Y", strtotime($value->DateViewed)).'</a>',
                    'Searched'=>'<a href="/resort-profile/?resort='.$value->id.'">'.$searched.'</a>',
                );
            }
        }
    }
    
    $expireDate = date('Y-m-d H:i:s');
    //get my coupons
    $sql = "SELECT a.coupon_hash, a.used, b.Name, b.Slug, b.Properties FROM wp_gpxAutoCoupon a
            INNER JOIN wp_specials b ON a.coupon_id=b.id
            WHERE user_id='".$cid."'
            AND EndDate > '".$expireDate."' ORDER BY used";
    $acs = $wpdb->get_results($sql);
    foreach($acs as $ac)
    {
        $redeemed = "No";
		$promoproperties=json_decode($ac->Properties);
// 		        if(get_current_user_id() == 5)
// 		        {
// 		            echo '<pre>'.print_r($promoproperties->actc, true).'</pre>';
// 		        }

        if($ac->used == '1')
            $redeemed = "Yes";
            $mycoupons[] = array(
                'name' => $ac->Name,
                'slug' => $ac->Slug,
                'code' => $ac->coupon_hash,
                'redeemed' => $redeemed,
                'details' => $promoproperties->actc,
            );
    }
    //get my owner credit coupons
    //get the coupon
    $sql = "SELECT *, a.id as cid, b.id as oid, c.id as aid, c.datetime as activity_date FROM wp_gpxOwnerCreditCoupon a
                    INNER JOIN wp_gpxOwnerCreditCoupon_owner b ON b.couponID=a.id
                    INNER JOIN wp_gpxOwnerCreditCoupon_activity c ON c.couponID=a.id
                    WHERE b.ownerID='".$cid."'";
    $coupons = $wpdb->get_results($sql);
    foreach($coupons as $coupon)
    {
        $distinctCoupon[$coupon->cid]['coupon'] = $coupon;
        $distinctCoupon[$coupon->cid]['activity'][$coupon->aid] = $coupon;
    }
    foreach($distinctCoupon as $dcKey=>$dc)
    {
        $activityDate = '0';
        foreach($dc['activity'] as $activity)
        {
           
            if($activity->activity == 'transaction')
            {
                $redeemedAmount[$dcKey][] = $activity->amount;
            }
            else
            {
                $amount[$dcKey][] = $activity->amount;
                //get the greatest date
//                 if(strtotime($activity->activity_date) > $activityDate)
//                 {
//                     $activityDate = strtotime($activity->activity_date);
//                 }
                if($activity->activity == 'created')
                {
                    $activityDate = strtotime($activity->activity_date);
                }
            }
        }
        //if activitydate <> 0 and is more than 1 year ago then we shouldn't display this coupon
        if($activityDate != 0 && $activityDate < strtotime('-1 year'))
        {
            continue;
        }
        
        if($dc['coupon']->single_use == 1 && array_sum($redeemedAmount[$dcKey]) > 0)
        {
            $balance = 0;
        }
        else
        {
            $balance[$dcKey] = array_sum($amount[$dcKey]) - array_sum($redeemedAmount[$dcKey]);
        }
        
        $mycreditcoupons[] = array(
            'name' => $dc['coupon']->name,
            'code' => $dc['coupon']->couponcode,
            'balance' => '$'.$balance[$dcKey],
            'redeemed' => '$'.array_sum($redeemedAmount[$dcKey]),
            'active' => $dc['coupon']->active,
            'expire' => date('m/d/Y', strtotime($dc['coupon']->expirationDate)),
        );

    }
    
    //get my custom requests
    $sql = "SELECT * FROM wp_gpxCustomRequest WHERE emsID='".$usermeta->DAEMemberNo."' ORDER BY active";
    $crs = $wpdb->get_results($sql);
    foreach($crs as $cr)
    {
        $location = '<a href="#" class="edit-custom-request" data-rid="'.$cr->id.'" aria-label="Edit Custom Request"><i class="fa fa-eye" aria-hidden="true"></i></a> ';
        if(!empty($cr->resort))
            $location .= 'Resort: '.stripslashes($cr->resort);
            elseif(!empty($cr->city))
            $location .= 'City: '.stripslashes($cr->city);
            elseif(!empty($cr->region))
            $location .= 'Region: '.stripslashes($cr->region);
            
            $date = $cr->checkIn;
            if(!empty($cr->checkIn2))
            {
                
                if(strtotime($cr->checkIn2) < strtotime("now"))
                    continue;
                    $date .= ' - '.$cr->checkIn2;
            }
            elseif(strtotime($cr->checkIn) < strtotime("now"))
            {
                continue;
            }
            $requesteddate = date('m/d/Y', strtotime($cr->datetime));
            $found = "Yes";
            if(empty($cr->matched))
                $found = "No";
                
                //Request to be kept ‘visible’ even if Inactive (remove option to ‘Delete’)
                        $active = 'No <a href="#" class="crActivate btn btn-secondary" data-crid="'.$cr->id.'" data-action="activate">Enable</a>';
                //changing back to the previous version where we had a toggle option    
                if($found == "Yes")
                {
                    $active = 'No';
                }
//                 $active = 'No';
                if($cr->active == '1')
                {
                    $active = 'Yes';
                    //Request to be kept ‘visible’ even if Inactive (remove option to ‘Delete’)
                    //adding this option back in
                            $active = 'Yes <a href="#" class="crActivate btn btn-secondary" data-crid="'.$cr->id.'" data-action="deactivate">Disable</a>';
                }
                    //         $matched = array();
                    //         $matches = array();
                    //         $matched = explode(",", $cr->matched);
                    //         $matchedResortName = '';
                    //         foreach($matched as $match)
                        //         {
                        //             if(!empty($match))
                            //             {
                            //                 $sql = "SELECT resortName FROM wp_properties WHERE id='".str_replace(" ", "", $match)."'";
                            //                 $matchedResortName = $wpdb->get_row($sql);
                            //                 $matches[] = '<a href="/booking-path/?book='.$match.'">'.$matchedResortName->resortName.'</a>';
                            //             }
                        //         }
                    $db = (array) $cr;
                    $matched = custom_request_match($db);
                    $matches = 'No';
                    if(!empty($matched))
                    {
                        $matchLink = ' <a class="btn btn-secondary" href="/result?matched='.$cr->id.'">View Results</a>';
                        if(!empty($cr->week_on_hold))
                        {
                            $crWeekType = '&type=ExchangeWeek';
                            if($cr->preference == 'Rental')
                            {
                                $crWeekType = str_replace('Exchange', 'Rental', $crWeekType);
                            }
                            $matchLink = ' <a class="btn btn-secondary" href="/booking-path/?book='.$cr->week_on_hold.$crWeekType.'">View Results</a>';
                        }
                        $matches = '';
                        if(!empty($cr->matchEmail))
                        {
                            $matches .= '<span title="Notification Sent: '.date('m/d/y', strtotime($cr->matchEmail)).'">';
                        }
                        $matches .= 'Yes';
                        $matches .= $matchLink;
                        if(!empty($cr->matchEmail))
                        {
                            $matches .= '</span>';
                        }
                        // if we are only returning the restricted key then this isn't a match
                        if(count($matched) == 1 && isset($matched['restricted']))
                        {
                            $matches = 'No';
                        }
                    }
                    
                    
                    $customRequests[$i]['location'] = $location;
                    $customRequests[$i]['traveldate'] = $date;
                    $customRequests[$i]['requesteddate'] = $requesteddate;
                    $customRequests[$i]['matched'] = $matches;
                    $customRequests[$i]['active'] = $active;
                    $i++;
    }
    
    
    $sql = "SELECT *  FROM `wp_GPR_Owner_ID__c` WHERE `user_id` = '".$cid."'";
    $gprOwner = $wpdb->get_row($sql);
    
    include('templates/sc-view-profile.php');
    
}
add_shortcode('gpx_view_profile', 'gpx_view_profile_sc');

function custom_request_status_change()
{
    global $wpdb;
    if(isset($_POST[crid]))
    {
        $id = $_POST['crid'];
        $udata['active'] = '1';
        if($_POST['craction'] ==  'deactivate')
            $udata['active'] = 0;
    }
    if(isset($_REQUEST['croid']))
    {
        $udata['active'] = 0;
        $split = explode("221a2d2s33d564334ne3", $_REQUEST['croid']);
        $id = $split[0];
        $emsID = $split[1];
        $sql = "SELECT active FROM wp_gpxCustomRequest where id='".$id."'";
        $row = $wpdb->get_row($sql);
        if($row->active == '0')
        {
            $udata['active'] = 1;
        }
    }
    
    if(isset($id))
    {
        $update = $wpdb->update('wp_gpxCustomRequest', $udata, array('id'=>$id));
        $data['success'] = true;
    }
    if(isset($_REQUEST['croid']))
    {
        header('Location: '.get_site_url("", "/custom-request-status-updated"));
        die;
    }
    
    wp_send_json($data);
    wp_die();
}
add_action("wp_ajax_custom_request_status_change","custom_request_status_change");
add_action("wp_ajax_nopriv_custom_request_status_change", "custom_request_status_change");

function custom_request_validate_restrictions()
{
    global $wpdb;
    
    $data = array('success'=>false);
    
    $forDB = array(
        '00N40000003S58X'=>'region',
        '00N40000003DG5S'=>'city',
        '00N40000003DG59'=>'resort',
        'miles'=>'miles'
    );
    foreach($forDB as $key=>$value)
    {
        if(!empty($_POST[$key]))
        {
            $db[$value] = $_POST[$key];
        }
    }
    
    
    $dateRanges = json_decode( stripslashes($_POST['00N40000003DG5P']));
    $db['checkIn'] = date('m/d/Y', strtotime($dateRanges->start));
    $db['checkIn2'] = date('m/d/Y', strtotime($dateRanges->end));
    if(isset($db['checkIn']) && (isset($db['region']) || isset($db['city']) || isset($db['resort'])))
    {
        $crd = custom_request_match($db);
        if($crd)
        {
            $data = $crd;
        }
    }
    
    wp_send_json($data);
    wp_die();
}
add_action("wp_ajax_custom_request_validate_restrictions","custom_request_validate_restrictions");
add_action("wp_ajax_nopriv_custom_request_validate_restrictions", "custom_request_validate_restrictions");

function gpx_member_dashboard_sc()
{
    global $wpdb;
    
    $cid = get_current_user_id();
 
    if(isset($_COOKIE['switchuser']))
        $cid = $_COOKIE['switchuser'];
    

    //set the profile columns
    $profilecols[0] = array(
        array('placeholder'=>"First Name", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'FirstName1'), 'required'=>'required'),
        array('placeholder'=>"Last Name", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'LastName1'), 'required'=>'required'),
        array('placeholder'=>"Email", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Email'), 'required'=>'required'),
        array('placeholder'=>"Home Phone", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'HomePhone'), 'required'=>'required'),
        array('placeholder'=>"Mobile Phone", 'class'=>'', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Mobile'), 'required'=>''),
    );
    $profilecols[1] = array(
        array('placeholder'=>"Street Address", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Address1'), 'required'=>'required'),
        array('placeholder'=>"City", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Address3'), 'required'=>'required'),
        array('placeholder'=>"State", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Address4'), 'required'=>'required'),
        array('placeholder'=>"Zip", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'PostCode'), 'required'=>'required'),
        array('placeholder'=>"Country", 'class'=>'validate', 'value'=>array('from'=>'usermeta', 'retrieve'=>'Address5'), 'required'=>'required'),
    );
    if(isset($_POST['cid']) && $cid = $_POST['cid'])
    {
        
        foreach($profilecols as $col)
        {
            foreach($col as $data)
            {
                if($data['value']['from'] == 'user') //update user table
                {
                    $mainData = array(
                        'ID'=>$cid,
                        'user_email'=>$_POST[$data['value']['retrieve']],
                    );
                    wp_update_user($mainData);
                }
                else //update user meta
                {
                    update_user_meta($cid, $data['value']['retrieve'], $_POST[$data['value']['retrieve']]);
                }
                if($data['value']['retrieve'] == 'user_email')
                {
                   update_user_meta($cid, 'Email', $_POST[$data['value']['retrieve']]); 
                }
            }
        }
    }
    
    $user = get_userdata($cid);
    $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );

    if(!get_user_meta($cid, 'DAEMemberNo', TRUE))
    {
        require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
        $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    
        $DAEMemberNo = str_replace("U", "", $user->user_login);
        $user = $gpx->DAEGetMemberDetails($DAEMemberNo, $cid, array('email'=>$usermeta->email));
    }
    
    $sql = "SELECT * FROM wp_gpxMemberSearch WHERE userID='".$cid."'";
    $results = $wpdb->get_results($sql);

    foreach($results as $result)
    {
        $history = json_decode($result->data);
        foreach($history as $key=>$value)
        {
            if(isset($value->property->WeekType))
            {
                $splitKey = explode('-', $key);
                if($splitKey[0] == 'select')
                {
                    $weektype = $value->property->WeekType;
                    $histsetPrice = $value->price;
                    $histdaePrice = $value->property->Price;
                    $price = $value->property->WeekPrice;
                    if($histsetPrice < $histdaePrice)
                        $price = '<span style="text-decoration: line-through;">'.$value->property->WeekPrice.'</span> '.str_replace($value->property->Price, $histsetPrice, $value->property->WeekPrice);
                    $histout[$weektype][] = array(
                        'weekId'=>$value->property->weekId,
                        'ResortName'=>$value->property->ResortName,
                        'Price'=>$price,
                        'checkIn'=>$value->property->checkIn,
                        'Size'=>$value->property->Size,
                    );
                }
            }
        }
    }
    
    include('templates/sc-member-dashboard.php');
    
}
add_shortcode('gpx_member_dashboard', 'gpx_member_dashboard_sc');

function vc_gpx_member_dashboard()
{
    vc_map( array(
        "name" => __("GPX Memeber Dashboard", "gpx-website"),
        "base" => "gpx_member_dashboard",
        "params" => array(
            // add params same as with any other content element
            array(
                "type" => "textfield",
                "heading" => __("Extra class name", "gpx-website"),
                "param_name" => "el_class",
                "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "my-text-domain")
            )
        )
    ) );
}
add_action('vc_before_init', 'vc_gpx_member_dashboard');

function vc_gpx_locations_page()
{
    vc_map( array(
        "name" => __("Locations Map", "gpx-website"),
        "base" => "wpsl",
        "params" => array(
            // add params same as with any other content element
            array(
              'type'=>"dropdown",
              'heading'=>__("Template", "gpx-website"),
                'param_name'=>'template',
                'description'=>__("Extra templates could be added here.  Right now we are just using Default."),
                'value'=>array(
                  'default', 
                ),
            ),
            array(
                "type" => "textfield",
                "heading" => __("Extra class name", "my-text-domain"),
                "param_name" => "el_class",
                "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "my-text-domain")
            )
        )
    ) );
}
add_action('vc_before_init', 'vc_gpx_locations_page');
//add/enter a new coupon
function gpx_enter_coupon()
{
    global $wpdb;
    
    extract($_POST);
    
    $user = get_userdata($cid);
    if(isset($user) && !empty($user))
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
    
    
    //check if it is an auto create coupon
    $couponParts = preg_split( "(-|\s+)", $coupon );
    $autoCouponHash = end($couponParts);
    
    //check database
    $sql = "SELECT coupon_id FROM wp_gpxAutoCoupon WHERE coupon_hash='".$autoCouponHash."' AND user_id='".$cid."' AND used=0";
    $ac = $wpdb->get_row($sql);
    
    if(!empty($ac)) //this is a hashed coupon
    {
        $acForCart = $autoCouponHash;
        $sql = "SELECT * FROM wp_specials WHERE Type='coupon' AND id='".$ac->coupon_id."' AND active=1";
    }
    else
        $sql = "SELECT * FROM wp_specials WHERE Type='coupon' AND (Name='".$coupon."' OR Slug='".$coupon."') AND active=1";
    $row = $wpdb->get_row($sql);
    
    $specialMeta = stripslashes_deep(json_decode($row->Properties));
    
    
    if(empty($row))
    {
        //check to see if this is a owner credit coupon
        $sql = "SELECT *, a.id as cid, b.id as aid, c.id as oid FROM wp_gpxOwnerCreditCoupon a
                INNER JOIN wp_gpxOwnerCreditCoupon_activity b ON b.couponID=a.id
                INNER JOIN wp_gpxOwnerCreditCoupon_owner c ON c.couponID=a.id
                WHERE (a.name='".$coupon."' OR a.couponcode='".$coupon."') AND a.active=1 and c.ownerID='".$cid."'";
        $occoupons = $wpdb->get_results($sql);
        if(!empty($occoupons))
        {
            foreach($occoupons as $occoupon)
            {
                $distinctCoupon = $occoupon;
                $distinctOwner[$occoupon->oid] = $occoupon;
                $distinctActivity[$occoupon->aid] = $occoupon;
            }
            
            //get the balance and activity for data
            foreach($distinctActivity as $activity)
            {
                if($activity->activity == 'transaction')
                {
                    $actredeemed[] = $activity->amount;
                }
                else
                {
                    $actamount[] = $activity->amount;
                }
            }
            if($distinctCoupon->single_use == 1 && array_sum($actredeemed) > 0)
            {
                $balance = 0;
            }
            else
            {
                $balance = array_sum($actamount) - array_sum($actredeemed);
            }
            
            //if we have a balance at this point the the coupon is good
            if($balance > 0)
            {
                $sql = "SELECT * FROM wp_cart WHERE cartID='".$cartID."'";
                $cartRows = $wpdb->get_results($sql);
                foreach($cartRows as $cartRow)
                {
                    $cart = json_decode($cartRow->data);
                    if(isset($cart->occoupon))
                    {
                        if(is_array($cart->occoupon))
                        {
                            
                            $ccs = $cart->occoupon;
                        }
                        else
                        {
                            $ccs[] = $cart->occoupon;
                        }
                    }
                    $ccs[] = $distinctCoupon->cid;
                    $cart->occoupon =$ccs;
                    
                    $update = json_encode($cart);
                    $wpdb->update('wp_cart', array('data'=>$update), array('cartID'=>$cartID));
                }
                $return['success'] = true;
                
                echo wp_send_json($return);
                exit();
            }
            else
            {
                //coupon isn't valid
                $return['error'] = "This coupon is invalid.";
            }
        }
        else
        {
            //coupon isn't valid
            $return['error'] = "This coupon is invalid.";
        }
    }
    else 
    {
        $bCouponID = $row->id;
        $now = date('Y-m-d h:i:s');
        if($now > $row->EndDate)
            $return['error'] = "This coupon has expired";
        if($now < $row->StartDate)
            $return['error'] = "This coupon is invalid.";
        
        if(isset($specialMeta->singleUse) && $specialMeta->singleUse == 'Yes')
        {
            $sql = "SELECT * FROM wp_redeemedCoupons WHERE userID='".$cid."' AND specialID='".$row->id."'";
            //             $cpDup = $wpdb->get_row($sql);
            
            $cpDup = $wpdb->get_results($sql);
            //now we can have more than one assigned so we need to see how many times this owner was added.
            $customersCount = array_count_values(json_decode($specialMeta->specificCustomer, true));
            
            //now we need to add the the people from the hard coded array
            $hcCustomers[453][]=197931;
            $hcCustomers[453][]=197931;
            $hcCustomers[453][]=197944;
            $hcCustomers[453][]=197948;
            $hcCustomers[453][]=197966;
            $hcCustomers[453][]=198001;
            $hcCustomers[453][]=198026;
            $hcCustomers[453][]=198045;
            $hcCustomers[453][]=198056;
            $hcCustomers[453][]=198072;
            $hcCustomers[453][]=198091;
            $hcCustomers[453][]=198095;
            $hcCustomers[453][]=198109;
            $hcCustomers[453][]=198145;
            $hcCustomers[454][]=198150;
            $hcCustomers[453][]=198180;
            $hcCustomers[446][]=198310;
            $hcCustomers[446][]=198315;
            $hcCustomers[446][]=198320;
            $hcCustomers[446][]=198320;
            $hcCustomers[446][]=198320;
            $hcCustomers[446][]=198320;
            $hcCustomers[446][]=198321;
            $hcCustomers[454][]=198377;
            $hcCustomers[440][]=198645;
            $hcCustomers[454][]=198663;
            $hcCustomers[454][]=198700;
            $hcCustomers[440][]=198719;
            $hcCustomers[445][]=198734;
            $hcCustomers[449][]=198760;
            $hcCustomers[440][]=198786;
            $hcCustomers[440][]=198892;
            $hcCustomers[440][]=198927;
            $hcCustomers[440][]=198935;
            $hcCustomers[443][]=198935;
            $hcCustomers[440][]=199066;
            $hcCustomers[451][]=199087;
            $hcCustomers[440][]=199091;
            $hcCustomers[454][]=199091;
            $hcCustomers[451][]=199092;
            $hcCustomers[451][]=199107;
            $hcCustomers[451][]=199142;
            $hcCustomers[451][]=199151;
            $hcCustomers[451][]=199151;
            $hcCustomers[451][]=199153;
            $hcCustomers[451][]=199160;
            $hcCustomers[451][]=199163;
            $hcCustomers[451][]=199166;
            $hcCustomers[451][]=199167;
            $hcCustomers[451][]=199192;
            $hcCustomers[451][]=199198;
            $hcCustomers[451][]=199221;
            $hcCustomers[451][]=199222;
            $hcCustomers[451][]=199236;
            $hcCustomers[451][]=199239;
            $hcCustomers[440][]=199336;
            $hcCustomers[444][]=199344;
            $hcCustomers[444][]=199384;
            $hcCustomers[444][]=199386;
            $hcCustomers[444][]=199405;
            $hcCustomers[444][]=199407;
            $hcCustomers[444][]=199409;
            $hcCustomers[440][]=199429;
            $hcCustomers[446][]=199537;
            $hcCustomers[446][]=199593;
            $hcCustomers[441][]=199741;
            $hcCustomers[445][]=199776;
            $hcCustomers[454][]=199781;
            $hcCustomers[445][]=199790;
            $hcCustomers[454][]=199803;
            $hcCustomers[440][]=199805;
            $hcCustomers[440][]=199810;
            $hcCustomers[440][]=199842;
            $hcCustomers[440][]=199865;
            $hcCustomers[445][]=199866;
            $hcCustomers[440][]=199873;
            $hcCustomers[445][]=199901;
            $hcCustomers[440][]=199905;
            $hcCustomers[440][]=199920;
            $hcCustomers[440][]=199938;
            $hcCustomers[440][]=199959;
            $hcCustomers[440][]=199966;
            $hcCustomers[440][]=199996;
            $hcCustomers[440][]=200000;
            $hcCustomers[440][]=200004;
            $hcCustomers[440][]=200021;
            $hcCustomers[440][]=200038;
            $hcCustomers[440][]=200038;
            $hcCustomers[440][]=200038;
            $hcCustomers[442][]=200055;
            $hcCustomers[442][]=200055;
            $hcCustomers[440][]=200059;
            $hcCustomers[445][]=200086;
            $hcCustomers[445][]=200092;
            $hcCustomers[440][]=200176;
            $hcCustomers[454][]=200245;
            $hcCustomers[453][]=200282;
            $hcCustomers[453][]=200344;
            $hcCustomers[440][]=200406;
            $hcCustomers[440][]=200489;
            $hcCustomers[440][]=200489;
            $hcCustomers[451][]=200551;
            $hcCustomers[440][]=200628;
            $hcCustomers[453][]=200629;
            $hcCustomers[440][]=200637;
            $hcCustomers[454][]=200717;
            $hcCustomers[451][]=200988;
            $hcCustomers[440][]=201070;
            $hcCustomers[440][]=201232;
            $hcCustomers[440][]=201241;
            $hcCustomers[442][]=201310;
            $hcCustomers[453][]=201365;
            $hcCustomers[440][]=201418;
            $hcCustomers[440][]=201435;
            $hcCustomers[440][]=201447;
            $hcCustomers[440][]=201447;
            $hcCustomers[442][]=201519;
            $hcCustomers[440][]=201657;
            $hcCustomers[451][]=201748;
            $hcCustomers[451][]=201748;
            $hcCustomers[451][]=201824;
            $hcCustomers[440][]=201863;
            $hcCustomers[454][]=202026;
            $hcCustomers[444][]=202393;
            $hcCustomers[440][]=202439;
            $hcCustomers[451][]=202463;
            $hcCustomers[445][]=202463;
            $hcCustomers[445][]=202464;
            $hcCustomers[444][]=202556;
            $hcCustomers[453][]=202588;
            $hcCustomers[440][]=203053;
            $hcCustomers[446][]=203113;
            $hcCustomers[453][]=203132;
            $hcCustomers[453][]=203214;
            $hcCustomers[451][]=203362;
            $hcCustomers[454][]=203398;
            $hcCustomers[442][]=203414;
            $hcCustomers[453][]=203605;
            $hcCustomers[440][]=203607;
            $hcCustomers[454][]=203649;
            $hcCustomers[440][]=203680;
            $hcCustomers[453][]=203692;
            $hcCustomers[440][]=203705;
            $hcCustomers[440][]=231992;
            $hcCustomers[454][]=232031;
            $hcCustomers[443][]=232148;
            $hcCustomers[440][]=232176;
            $hcCustomers[445][]=232176;
            $hcCustomers[440][]=232176;
            $hcCustomers[445][]=232176;
            $hcCustomers[454][]=232236;
            $hcCustomers[440][]=232321;
            $hcCustomers[440][]=232356;
            $hcCustomers[451][]=232386;
            $hcCustomers[440][]=232401;
            $hcCustomers[445][]=232426;
            $hcCustomers[445][]=232426;
            $hcCustomers[440][]=232611;
            $hcCustomers[451][]=232618;
            $hcCustomers[440][]=232630;
            $hcCustomers[446][]=233093;
            $hcCustomers[440][]=233284;
            $hcCustomers[444][]=233292;
            $hcCustomers[440][]=233292;
            $hcCustomers[446][]=233293;
            $hcCustomers[440][]=233410;
            $hcCustomers[451][]=233469;
            $hcCustomers[440][]=233584;
            $hcCustomers[454][]=233650;
            $hcCustomers[453][]=233953;
            $hcCustomers[451][]=234254;
            $hcCustomers[440][]=234328;
            $hcCustomers[444][]=234556;
            $hcCustomers[440][]=234727;
            $hcCustomers[440][]=234734;
            $hcCustomers[440][]=234754;
            $hcCustomers[440][]=234781;
            $hcCustomers[453][]=234940;
            $hcCustomers[440][]=234941;
            $hcCustomers[440][]=235092;
            $hcCustomers[453][]=235122;
            $hcCustomers[453][]=235125;
            $hcCustomers[440][]=235183;
            $hcCustomers[440][]=235212;
            $hcCustomers[440][]=235218;
            $hcCustomers[446][]=235247;
            $hcCustomers[450][]=235286;
            $hcCustomers[450][]=235286;
            $hcCustomers[445][]=235286;
            $hcCustomers[451][]=235316;
            $hcCustomers[440][]=312477;
            $hcCustomers[454][]=312498;
            $hcCustomers[444][]=389582;
            $hcCustomers[451][]=389594;
            $hcCustomers[444][]=389634;
            $hcCustomers[446][]=389679;
            $hcCustomers[451][]=389730;
            $hcCustomers[440][]=389758;
            $hcCustomers[451][]=389766;
            $hcCustomers[451][]=389766;
            $hcCustomers[442][]=389783;
            $hcCustomers[444][]=389792;
            $hcCustomers[453][]=389794;
            $hcCustomers[444][]=389884;
            $hcCustomers[445][]=389892;
            $hcCustomers[440][]=389924;
            $hcCustomers[451][]=390048;
            $hcCustomers[445][]=390058;
            $hcCustomers[440][]=390062;
            $hcCustomers[440][]=390245;
            $hcCustomers[453][]=390249;
            $hcCustomers[440][]=390294;
            $hcCustomers[454][]=390334;
            $hcCustomers[440][]=390359;
            $hcCustomers[454][]=390369;
            $hcCustomers[453][]=390404;
            $hcCustomers[440][]=390438;
            $hcCustomers[440][]=390513;
            $hcCustomers[451][]=390520;
            $hcCustomers[440][]=390548;
            $hcCustomers[440][]=390548;
            $hcCustomers[440][]=390553;
            $hcCustomers[454][]=390559;
            $hcCustomers[444][]=390564;
            $hcCustomers[445][]=390571;
            $hcCustomers[440][]=390647;
            $hcCustomers[440][]=390688;
            $hcCustomers[453][]=390779;
            $hcCustomers[440][]=390792;
            $hcCustomers[440][]=390796;
            $hcCustomers[440][]=390799;
            $hcCustomers[454][]=390871;
            $hcCustomers[453][]=390872;
            $hcCustomers[440][]=390901;
            $hcCustomers[446][]=390903;
            $hcCustomers[444][]=390906;
            $hcCustomers[440][]=390919;
            $hcCustomers[440][]=390920;
            $hcCustomers[453][]=390971;
            $hcCustomers[454][]=390977;
            $hcCustomers[444][]=391018;
            $hcCustomers[440][]=391036;
            $hcCustomers[451][]=391045;
            $hcCustomers[444][]=391106;
            $hcCustomers[444][]=391106;
            $hcCustomers[454][]=391209;
            $hcCustomers[453][]=391263;
            $hcCustomers[440][]=391267;
            $hcCustomers[440][]=391268;
            $hcCustomers[440][]=391268;
            $hcCustomers[451][]=391274;
            $hcCustomers[440][]=391328;
            $hcCustomers[440][]=391355;
            $hcCustomers[440][]=391365;
            $hcCustomers[440][]=391365;
            $hcCustomers[440][]=391373;
            $hcCustomers[446][]=391408;
            $hcCustomers[454][]=391415;
            $hcCustomers[441][]=391432;
            $hcCustomers[441][]=391432;
            $hcCustomers[446][]=391445;
            $hcCustomers[440][]=391458;
            $hcCustomers[440][]=391464;
            $hcCustomers[440][]=391465;
            $hcCustomers[440][]=391467;
            $hcCustomers[440][]=391468;
            $hcCustomers[444][]=391483;
            $hcCustomers[445][]=391517;
            $hcCustomers[440][]=391534;
            $hcCustomers[440][]=391534;
            $hcCustomers[440][]=391534;
            $hcCustomers[440][]=391534;
            $hcCustomers[440][]=391534;
            $hcCustomers[440][]=391535;
            $hcCustomers[451][]=391568;
            $hcCustomers[445][]=391591;
            $hcCustomers[440][]=391607;
            $hcCustomers[453][]=391658;
            $hcCustomers[453][]=391720;
            $hcCustomers[453][]=391757;
            $hcCustomers[453][]=391760;
            $hcCustomers[453][]=391812;
            $hcCustomers[453][]=391891;
            $hcCustomers[453][]=391899;
            $hcCustomers[453][]=391915;
            $hcCustomers[453][]=392005;
            $hcCustomers[451][]=392047;
            $hcCustomers[453][]=392083;
            $hcCustomers[453][]=392085;
            $hcCustomers[453][]=392089;
            $hcCustomers[453][]=392220;
            $hcCustomers[453][]=392222;
            $hcCustomers[453][]=392255;
            $hcCustomers[453][]=392347;
            $hcCustomers[453][]=392419;
            $hcCustomers[453][]=392440;
            $hcCustomers[453][]=392480;
            $hcCustomers[453][]=392508;
            $hcCustomers[453][]=392666;
            $hcCustomers[453][]=392683;
            $hcCustomers[453][]=392727;
            $hcCustomers[453][]=392761;
            $hcCustomers[453][]=392794;
            $hcCustomers[453][]=392804;
            $hcCustomers[453][]=392925;
            $hcCustomers[453][]=392928;
            $hcCustomers[453][]=392982;
            $hcCustomers[453][]=392992;
            $hcCustomers[453][]=393001;
            $hcCustomers[453][]=393025;
            $hcCustomers[453][]=393069;
            $hcCustomers[453][]=393123;
            $hcCustomers[451][]=393202;
            $hcCustomers[453][]=393283;
            $hcCustomers[453][]=393288;
            $hcCustomers[453][]=393295;
            $hcCustomers[451][]=393332;
            $hcCustomers[453][]=393436;
            $hcCustomers[453][]=393436;
            $hcCustomers[453][]=393526;
            $hcCustomers[453][]=393676;
            $hcCustomers[446][]=393698;
            $hcCustomers[446][]=393700;
            $hcCustomers[446][]=393786;
            $hcCustomers[446][]=393798;
            $hcCustomers[454][]=393871;
            $hcCustomers[454][]=394042;
            $hcCustomers[454][]=394106;
            $hcCustomers[454][]=394140;
            $hcCustomers[454][]=394443;
            $hcCustomers[454][]=394522;
            $hcCustomers[454][]=394887;
            $hcCustomers[454][]=395061;
            $hcCustomers[454][]=395209;
            $hcCustomers[454][]=395513;
            $hcCustomers[454][]=395575;
            $hcCustomers[454][]=395881;
            $hcCustomers[454][]=395970;
            $hcCustomers[454][]=396014;
            $hcCustomers[454][]=396095;
            $hcCustomers[454][]=396115;
            $hcCustomers[454][]=396121;
            $hcCustomers[454][]=396139;
            $hcCustomers[454][]=396674;
            $hcCustomers[454][]=397067;
            $hcCustomers[454][]=397337;
            $hcCustomers[454][]=397536;
            $hcCustomers[454][]=397553;
            $hcCustomers[454][]=397733;
            $hcCustomers[454][]=397817;
            $hcCustomers[454][]=398031;
            $hcCustomers[454][]=398056;
            $hcCustomers[454][]=398086;
            $hcCustomers[451][]=398209;
            $hcCustomers[445][]=398211;
            $hcCustomers[454][]=398232;
            $hcCustomers[454][]=398279;
            $hcCustomers[454][]=398370;
            $hcCustomers[454][]=398379;
            $hcCustomers[454][]=398393;
            $hcCustomers[454][]=398436;
            $hcCustomers[454][]=398837;
            $hcCustomers[440][]=399050;
            $hcCustomers[454][]=399095;
            $hcCustomers[454][]=399110;
            $hcCustomers[454][]=399121;
            $hcCustomers[445][]=399261;
            $hcCustomers[445][]=399261;
            $hcCustomers[440][]=399336;
            $hcCustomers[440][]=399457;
            $hcCustomers[454][]=399499;
            $hcCustomers[454][]=399593;
            $hcCustomers[454][]=399593;
            $hcCustomers[440][]=399730;
            $hcCustomers[454][]=399750;
            $hcCustomers[440][]=399793;
            $hcCustomers[454][]=399835;
            $hcCustomers[445][]=399885;
            $hcCustomers[440][]=400195;
            $hcCustomers[445][]=400213;
            $hcCustomers[451][]=400249;
            $hcCustomers[440][]=400280;
            $hcCustomers[443][]=400304;
            $hcCustomers[443][]=400414;
            $hcCustomers[445][]=400723;
            $hcCustomers[440][]=400799;
            $hcCustomers[440][]=400882;
            $hcCustomers[445][]=400913;
            $hcCustomers[440][]=400920;
            $hcCustomers[440][]=400939;
            $hcCustomers[440][]=400958;
            $hcCustomers[445][]=400988;
            $hcCustomers[440][]=400998;
            $hcCustomers[446][]=401009;
            $hcCustomers[440][]=401019;
            $hcCustomers[451][]=401022;
            $hcCustomers[440][]=401023;
            $hcCustomers[451][]=401062;
            $hcCustomers[451][]=401066;
            $hcCustomers[451][]=401067;
            $hcCustomers[451][]=401072;
            $hcCustomers[451][]=401076;
            $hcCustomers[451][]=401079;
            $hcCustomers[451][]=401086;
            $hcCustomers[451][]=401088;
            $hcCustomers[451][]=401089;
            $hcCustomers[451][]=401090;
            $hcCustomers[451][]=401119;
            $hcCustomers[451][]=401129;
            $hcCustomers[451][]=401153;
            $hcCustomers[451][]=401153;
            $hcCustomers[451][]=401158;
            $hcCustomers[451][]=401160;
            $hcCustomers[451][]=401166;
            $hcCustomers[451][]=401178;
            $hcCustomers[451][]=401201;
            $hcCustomers[451][]=401220;
            $hcCustomers[451][]=401238;
            $hcCustomers[451][]=401251;
            $hcCustomers[451][]=401312;
            $hcCustomers[451][]=401317;
            $hcCustomers[451][]=401352;
            $hcCustomers[451][]=401365;
            $hcCustomers[451][]=401375;
            $hcCustomers[451][]=401381;
            $hcCustomers[451][]=401394;
            $hcCustomers[440][]=401396;
            $hcCustomers[451][]=401423;
            $hcCustomers[451][]=401432;
            $hcCustomers[451][]=401444;
            $hcCustomers[451][]=401454;
            $hcCustomers[451][]=401468;
            $hcCustomers[451][]=401468;
            $hcCustomers[451][]=401498;
            $hcCustomers[445][]=401539;
            $hcCustomers[451][]=401540;
            $hcCustomers[451][]=401553;
            $hcCustomers[451][]=401578;
            $hcCustomers[451][]=401601;
            $hcCustomers[451][]=401603;
            $hcCustomers[444][]=401606;
            $hcCustomers[451][]=401634;
            $hcCustomers[451][]=401683;
            $hcCustomers[451][]=401709;
            $hcCustomers[451][]=401812;
            $hcCustomers[451][]=401843;
            $hcCustomers[451][]=401848;
            $hcCustomers[451][]=401848;
            $hcCustomers[451][]=401855;
            $hcCustomers[451][]=401863;
            $hcCustomers[451][]=401875;
            $hcCustomers[451][]=401875;
            $hcCustomers[451][]=401903;
            $hcCustomers[451][]=401907;
            $hcCustomers[445][]=401916;
            $hcCustomers[451][]=401922;
            $hcCustomers[451][]=401927;
            $hcCustomers[451][]=401928;
            $hcCustomers[451][]=401934;
            $hcCustomers[451][]=401937;
            $hcCustomers[451][]=401945;
            $hcCustomers[451][]=401953;
            $hcCustomers[451][]=401962;
            $hcCustomers[451][]=401994;
            $hcCustomers[451][]=402012;
            $hcCustomers[445][]=402036;
            $hcCustomers[451][]=402089;
            $hcCustomers[451][]=402093;
            $hcCustomers[451][]=402182;
            $hcCustomers[454][]=402238;
            $hcCustomers[451][]=402256;
            $hcCustomers[451][]=402262;
            $hcCustomers[451][]=402265;
            $hcCustomers[451][]=402277;
            $hcCustomers[440][]=402281;
            $hcCustomers[451][]=402298;
            $hcCustomers[451][]=402303;
            $hcCustomers[451][]=402312;
            $hcCustomers[451][]=402315;
            $hcCustomers[440][]=402351;
            $hcCustomers[444][]=402391;
            $hcCustomers[444][]=402395;
            $hcCustomers[444][]=402404;
            $hcCustomers[444][]=402405;
            $hcCustomers[445][]=402467;
            $hcCustomers[444][]=402500;
            $hcCustomers[444][]=402517;
            $hcCustomers[445][]=402562;
            $hcCustomers[444][]=402584;
            $hcCustomers[440][]=402586;
            $hcCustomers[440][]=402604;
            $hcCustomers[440][]=402635;
            $hcCustomers[440][]=402655;
            $hcCustomers[440][]=402660;
            $hcCustomers[440][]=402661;
            $hcCustomers[440][]=402678;
            $hcCustomers[445][]=402680;
            $hcCustomers[450][]=402784;
            $hcCustomers[450][]=402784;
            $hcCustomers[440][]=402938;
            $hcCustomers[445][]=402993;
            $hcCustomers[440][]=403072;
            $hcCustomers[450][]=403154;
            $hcCustomers[440][]=403308;
            $hcCustomers[445][]=403320;
            $hcCustomers[440][]=403332;
            $hcCustomers[440][]=403341;
            $hcCustomers[440][]=403341;
            $hcCustomers[440][]=403346;
            $hcCustomers[445][]=403347;
            $hcCustomers[440][]=403369;
            $hcCustomers[453][]=403416;
            $hcCustomers[446][]=403420;
            $hcCustomers[445][]=403458;
            $hcCustomers[440][]=403500;
            $hcCustomers[445][]=403514;
            $hcCustomers[445][]=403528;
            $hcCustomers[440][]=403537;
            $hcCustomers[440][]=403548;
            $hcCustomers[440][]=403606;
            $hcCustomers[440][]=403615;
            $hcCustomers[440][]=403630;
            $hcCustomers[440][]=403648;
            $hcCustomers[440][]=403650;
            $hcCustomers[445][]=403655;
            $hcCustomers[445][]=403679;
            $hcCustomers[440][]=403766;
            $hcCustomers[445][]=403801;
            $hcCustomers[440][]=403809;
            $hcCustomers[440][]=403813;
            $hcCustomers[440][]=403817;
            $hcCustomers[440][]=403838;
            $hcCustomers[445][]=403847;
            $hcCustomers[440][]=403884;
            $hcCustomers[440][]=403896;
            $hcCustomers[440][]=403899;
            $hcCustomers[445][]=403934;
            $hcCustomers[440][]=403941;
            $hcCustomers[440][]=403949;
            $hcCustomers[440][]=404099;
            $hcCustomers[445][]=404102;
            $hcCustomers[440][]=404110;
            $hcCustomers[440][]=404113;
            $hcCustomers[440][]=404114;
            $hcCustomers[440][]=404135;
            $hcCustomers[445][]=404162;
            $hcCustomers[446][]=404164;
            $hcCustomers[445][]=404191;
            $hcCustomers[445][]=404197;
            $hcCustomers[445][]=404197;
            $hcCustomers[445][]=404216;
            $hcCustomers[445][]=404216;
            $hcCustomers[440][]=404223;
            $hcCustomers[451][]=404223;
            $hcCustomers[445][]=404250;
            $hcCustomers[440][]=404271;
            $hcCustomers[445][]=404273;
            $hcCustomers[454][]=404279;
            $hcCustomers[445][]=404303;
            $hcCustomers[440][]=404311;
            $hcCustomers[440][]=404318;
            $hcCustomers[440][]=404341;
            $hcCustomers[440][]=404346;
            $hcCustomers[445][]=404352;
            $hcCustomers[440][]=404378;
            $hcCustomers[440][]=404385;
            $hcCustomers[445][]=404392;
            $hcCustomers[440][]=404406;
            $hcCustomers[440][]=404419;
            $hcCustomers[445][]=404438;
            $hcCustomers[440][]=404463;
            $hcCustomers[445][]=404468;
            $hcCustomers[445][]=404484;
            $hcCustomers[440][]=404544;
            $hcCustomers[440][]=404549;
            $hcCustomers[445][]=404575;
            $hcCustomers[445][]=404581;
            $hcCustomers[440][]=404586;
            $hcCustomers[440][]=404586;
            $hcCustomers[440][]=404604;
            $hcCustomers[440][]=404616;
            $hcCustomers[440][]=404628;
            $hcCustomers[440][]=404631;
            $hcCustomers[445][]=404634;
            $hcCustomers[440][]=404636;
            $hcCustomers[440][]=404654;
            $hcCustomers[440][]=404677;
            $hcCustomers[440][]=404692;
            $hcCustomers[440][]=404704;
            $hcCustomers[440][]=404704;
            $hcCustomers[440][]=404723;
            $hcCustomers[440][]=404728;
            $hcCustomers[440][]=404728;
            $hcCustomers[440][]=404745;
            $hcCustomers[440][]=404767;
            $hcCustomers[440][]=404826;
            $hcCustomers[445][]=404874;
            $hcCustomers[440][]=404877;
            $hcCustomers[445][]=404878;
            $hcCustomers[445][]=404888;
            $hcCustomers[440][]=404890;
            $hcCustomers[440][]=404900;
            $hcCustomers[440][]=404940;
            $hcCustomers[453][]=404962;
            $hcCustomers[440][]=404995;
            $hcCustomers[440][]=405015;
            $hcCustomers[440][]=405078;
            $hcCustomers[440][]=405107;
            $hcCustomers[440][]=405129;
            $hcCustomers[451][]=405131;
            $hcCustomers[440][]=405139;
            $hcCustomers[440][]=405141;
            $hcCustomers[440][]=405155;
            $hcCustomers[440][]=405184;
            $hcCustomers[440][]=405201;
            $hcCustomers[440][]=405279;
            $hcCustomers[444][]=405281;
            $hcCustomers[440][]=405283;
            $hcCustomers[440][]=405284;
            $hcCustomers[440][]=405311;
            $hcCustomers[444][]=405323;
            $hcCustomers[445][]=405351;
            $hcCustomers[445][]=405365;
            $hcCustomers[445][]=405403;
            $hcCustomers[445][]=405419;
            $hcCustomers[445][]=405423;
            $hcCustomers[445][]=405430;
            $hcCustomers[440][]=405434;
            $hcCustomers[440][]=405435;
            $hcCustomers[440][]=405435;
            $hcCustomers[445][]=405446;
            $hcCustomers[445][]=405446;
            $hcCustomers[440][]=405453;
            $hcCustomers[440][]=405458;
            $hcCustomers[440][]=405518;
            $hcCustomers[440][]=405543;
            $hcCustomers[440][]=405575;
            $hcCustomers[440][]=405584;
            $hcCustomers[440][]=405610;
            $hcCustomers[440][]=405631;
            $hcCustomers[445][]=405643;
            $hcCustomers[445][]=405658;
            $hcCustomers[440][]=405687;
            $hcCustomers[440][]=405696;
            $hcCustomers[445][]=405707;
            $hcCustomers[440][]=405760;
            $hcCustomers[440][]=405760;
            $hcCustomers[440][]=405761;
            $hcCustomers[445][]=405774;
            $hcCustomers[445][]=405774;
            $hcCustomers[453][]=405812;
            $hcCustomers[453][]=405842;
            $hcCustomers[453][]=405846;
            $hcCustomers[453][]=405880;
            $hcCustomers[453][]=405988;
            $hcCustomers[453][]=406003;
            $hcCustomers[453][]=406064;
            $hcCustomers[453][]=406104;
            $hcCustomers[453][]=406120;
            $hcCustomers[440][]=406189;
            $hcCustomers[453][]=406196;
            $hcCustomers[453][]=406221;
            $hcCustomers[454][]=406421;
            $hcCustomers[453][]=406638;
            $hcCustomers[453][]=406700;
            $hcCustomers[453][]=406815;
            $hcCustomers[453][]=406818;
            $hcCustomers[453][]=406875;
            $hcCustomers[453][]=407049;
            $hcCustomers[453][]=407076;
            $hcCustomers[453][]=407090;
            $hcCustomers[453][]=407094;
            $hcCustomers[453][]=407145;
            $hcCustomers[453][]=407161;
            $hcCustomers[453][]=407172;
            $hcCustomers[453][]=407261;
            $hcCustomers[453][]=407287;
            $hcCustomers[453][]=407329;
            $hcCustomers[453][]=407354;
            $hcCustomers[453][]=407378;
            $hcCustomers[453][]=407399;
            $hcCustomers[453][]=407400;
            $hcCustomers[444][]=407419;
            $hcCustomers[453][]=407428;
            $hcCustomers[453][]=407431;
            $hcCustomers[453][]=407451;
            $hcCustomers[453][]=407530;
            $hcCustomers[453][]=407550;
            $hcCustomers[453][]=407611;
            $hcCustomers[453][]=407655;
            $hcCustomers[440][]=407718;
            $hcCustomers[446][]=407734;
            $hcCustomers[446][]=407741;
            $hcCustomers[446][]=407746;
            $hcCustomers[446][]=407750;
            $hcCustomers[446][]=407755;
            $hcCustomers[446][]=407757;
            $hcCustomers[446][]=407783;
            $hcCustomers[446][]=407783;
            $hcCustomers[446][]=407784;
            $hcCustomers[446][]=407789;
            $hcCustomers[446][]=407804;
            $hcCustomers[446][]=407835;
            $hcCustomers[446][]=407846;
            $hcCustomers[446][]=407847;
            $hcCustomers[454][]=407885;
            $hcCustomers[451][]=407938;
            $hcCustomers[454][]=407946;
            $hcCustomers[454][]=407982;
            $hcCustomers[454][]=408125;
            $hcCustomers[440][]=408286;
            $hcCustomers[454][]=408320;
            $hcCustomers[440][]=408321;
            $hcCustomers[440][]=408342;
            $hcCustomers[440][]=408377;
            $hcCustomers[445][]=408412;
            $hcCustomers[440][]=408413;
            $hcCustomers[440][]=408428;
            $hcCustomers[445][]=408460;
            $hcCustomers[440][]=408483;
            $hcCustomers[440][]=408496;
            $hcCustomers[440][]=408505;
            $hcCustomers[443][]=408726;
            $hcCustomers[443][]=408803;
            $hcCustomers[440][]=408887;
            $hcCustomers[440][]=408949;
            $hcCustomers[443][]=409035;
            $hcCustomers[443][]=409088;
            $hcCustomers[443][]=409108;
            $hcCustomers[443][]=409277;
            $hcCustomers[440][]=409387;
            $hcCustomers[440][]=409392;
            $hcCustomers[443][]=409503;
            $hcCustomers[443][]=409737;
            $hcCustomers[440][]=409779;
            $hcCustomers[451][]=409784;
            $hcCustomers[442][]=409836;
            $hcCustomers[442][]=409869;
            $hcCustomers[445][]=409870;
            $hcCustomers[442][]=409874;
            $hcCustomers[442][]=409899;
            $hcCustomers[442][]=409904;
            $hcCustomers[442][]=409921;
            $hcCustomers[442][]=409979;
            $hcCustomers[442][]=409988;
            $hcCustomers[442][]=410200;
            $hcCustomers[440][]=410329;
            $hcCustomers[442][]=410674;
            $hcCustomers[442][]=410690;
            $hcCustomers[440][]=410755;
            $hcCustomers[440][]=411000;
            $hcCustomers[442][]=411028;
            $hcCustomers[440][]=411203;
            $hcCustomers[442][]=411544;
            $hcCustomers[442][]=411659;
            $hcCustomers[442][]=411660;
            $hcCustomers[453][]=411737;
            $hcCustomers[444][]=411802;
            $hcCustomers[440][]=411804;
            $hcCustomers[451][]=411809;
            $hcCustomers[451][]=411813;
            $hcCustomers[440][]=411814;
            $hcCustomers[451][]=411819;
            $hcCustomers[451][]=411842;
            $hcCustomers[451][]=411844;
            $hcCustomers[444][]=411866;
            $hcCustomers[451][]=411871;
            $hcCustomers[440][]=411890;
            $hcCustomers[451][]=411893;
            $hcCustomers[451][]=411910;
            $hcCustomers[440][]=411910;
            $hcCustomers[451][]=411913;
            $hcCustomers[451][]=411914;
            $hcCustomers[451][]=411948;
            $hcCustomers[451][]=411956;
            $hcCustomers[451][]=411978;
            $hcCustomers[451][]=411979;
            $hcCustomers[451][]=411979;
            $hcCustomers[451][]=411979;
            $hcCustomers[445][]=411981;
            $hcCustomers[451][]=411993;
            $hcCustomers[451][]=412028;
            $hcCustomers[451][]=412039;
            $hcCustomers[451][]=412051;
            $hcCustomers[451][]=412066;
            $hcCustomers[451][]=412074;
            $hcCustomers[440][]=412085;
            $hcCustomers[440][]=412099;
            $hcCustomers[440][]=412134;
            $hcCustomers[445][]=412138;
            $hcCustomers[451][]=412182;
            $hcCustomers[451][]=412185;
            $hcCustomers[451][]=412203;
            $hcCustomers[451][]=412213;
            $hcCustomers[451][]=412246;
            $hcCustomers[451][]=412257;
            $hcCustomers[451][]=412257;
            $hcCustomers[451][]=412260;
            $hcCustomers[451][]=412301;
            $hcCustomers[445][]=412344;
            $hcCustomers[440][]=412349;
            $hcCustomers[451][]=412354;
            $hcCustomers[451][]=412380;
            $hcCustomers[451][]=412389;
            $hcCustomers[451][]=412397;
            $hcCustomers[451][]=412413;
            $hcCustomers[451][]=412415;
            $hcCustomers[451][]=412430;
            $hcCustomers[451][]=412431;
            $hcCustomers[451][]=412446;
            $hcCustomers[451][]=412454;
            $hcCustomers[451][]=412462;
            $hcCustomers[451][]=412470;
            $hcCustomers[440][]=412475;
            $hcCustomers[444][]=412495;
            $hcCustomers[445][]=412523;
            $hcCustomers[444][]=412561;
            $hcCustomers[444][]=412564;
            $hcCustomers[444][]=412574;
            $hcCustomers[444][]=412596;
            $hcCustomers[444][]=412625;
            $hcCustomers[444][]=412630;
            $hcCustomers[444][]=412651;
            $hcCustomers[444][]=412689;
            $hcCustomers[445][]=412701;
            $hcCustomers[440][]=412729;
            $hcCustomers[444][]=412732;
            $hcCustomers[444][]=412768;
            $hcCustomers[445][]=412810;
            $hcCustomers[444][]=412865;
            $hcCustomers[445][]=412878;
            $hcCustomers[444][]=412880;
            $hcCustomers[444][]=412889;
            $hcCustomers[444][]=412902;
            $hcCustomers[444][]=412917;
            $hcCustomers[444][]=412925;
            $hcCustomers[444][]=412939;
            $hcCustomers[440][]=412972;
            $hcCustomers[440][]=412976;
            $hcCustomers[444][]=412991;
            $hcCustomers[440][]=412993;
            $hcCustomers[440][]=413000;
            $hcCustomers[453][]=413036;
            $hcCustomers[453][]=413039;
            $hcCustomers[454][]=413042;
            $hcCustomers[445][]=413071;
            $hcCustomers[440][]=413077;
            $hcCustomers[440][]=413085;
            $hcCustomers[445][]=413089;
            $hcCustomers[440][]=413097;
            $hcCustomers[440][]=413235;
            $hcCustomers[440][]=413325;
            $hcCustomers[440][]=413332;
            $hcCustomers[450][]=413375;
            $hcCustomers[450][]=413494;
            $hcCustomers[450][]=413578;
            $hcCustomers[440][]=413587;
            $hcCustomers[440][]=413608;
            $hcCustomers[440][]=413687;
            $hcCustomers[445][]=413706;
            $hcCustomers[440][]=413719;
            $hcCustomers[440][]=413723;
            $hcCustomers[445][]=413733;
            $hcCustomers[445][]=413733;
            $hcCustomers[440][]=413739;
            $hcCustomers[440][]=413743;
            $hcCustomers[440][]=413760;
            $hcCustomers[440][]=413795;
            $hcCustomers[445][]=413805;
            $hcCustomers[440][]=413811;
            $hcCustomers[440][]=413813;
            $hcCustomers[440][]=413814;
            $hcCustomers[440][]=413871;
            $hcCustomers[440][]=413875;
            $hcCustomers[440][]=413882;
            $hcCustomers[440][]=413882;
            $hcCustomers[440][]=413916;
            $hcCustomers[453][]=413928;
            $hcCustomers[444][]=413954;
            $hcCustomers[440][]=413965;
            $hcCustomers[440][]=413968;
            $hcCustomers[451][]=413979;
            $hcCustomers[453][]=413983;
            $hcCustomers[454][]=413985;
            $hcCustomers[454][]=414003;
            $hcCustomers[440][]=414043;
            $hcCustomers[440][]=414054;
            $hcCustomers[453][]=414060;
            $hcCustomers[440][]=414064;
            $hcCustomers[440][]=414125;
            $hcCustomers[440][]=414147;
            $hcCustomers[440][]=414147;
            $hcCustomers[445][]=414186;
            $hcCustomers[451][]=414230;
            $hcCustomers[442][]=414258;
            $hcCustomers[440][]=414299;
            $hcCustomers[451][]=414313;
            $hcCustomers[440][]=414325;
            $hcCustomers[454][]=414348;
            $hcCustomers[451][]=414380;
            $hcCustomers[440][]=414385;
            $hcCustomers[440][]=414388;
            $hcCustomers[444][]=414426;
            $hcCustomers[440][]=414428;
            $hcCustomers[440][]=414429;
            $hcCustomers[440][]=414433;
            $hcCustomers[445][]=414451;
            $hcCustomers[440][]=414483;
            $hcCustomers[440][]=414487;
            $hcCustomers[453][]=414503;
            $hcCustomers[440][]=414523;
            $hcCustomers[445][]=414523;
            $hcCustomers[440][]=414547;
            $hcCustomers[440][]=414575;
            $hcCustomers[440][]=414630;
            $hcCustomers[440][]=414714;
            $hcCustomers[454][]=414747;
            $hcCustomers[447][]=414753;
            $hcCustomers[440][]=414756;
            $hcCustomers[444][]=414799;
            $hcCustomers[440][]=414808;
            $hcCustomers[445][]=414813;
            $hcCustomers[445][]=414816;
            $hcCustomers[440][]=414893;
            $hcCustomers[445][]=414909;
            $hcCustomers[440][]=414913;
            $hcCustomers[445][]=414919;
            $hcCustomers[440][]=414922;
            $hcCustomers[440][]=414937;
            $hcCustomers[445][]=414946;
            $hcCustomers[445][]=414946;
            $hcCustomers[440][]=414954;
            $hcCustomers[440][]=415027;
            $hcCustomers[445][]=415045;
            $hcCustomers[440][]=415053;
            $hcCustomers[454][]=415063;
            $hcCustomers[454][]=415063;
            $hcCustomers[445][]=415070;
            $hcCustomers[440][]=415087;
            $hcCustomers[445][]=415125;
            $hcCustomers[440][]=415166;
            $hcCustomers[440][]=415175;
            $hcCustomers[440][]=415185;
            $hcCustomers[440][]=415196;
            $hcCustomers[440][]=415218;
            $hcCustomers[440][]=415234;
            $hcCustomers[440][]=415243;
            $hcCustomers[440][]=415245;
            $hcCustomers[440][]=415248;
            $hcCustomers[445][]=415249;
            $hcCustomers[440][]=415265;
            $hcCustomers[440][]=415297;
            $hcCustomers[440][]=415300;
            $hcCustomers[445][]=415317;
            $hcCustomers[445][]=415345;
            $hcCustomers[445][]=415380;
            $hcCustomers[440][]=415391;
            $hcCustomers[445][]=415416;
            $hcCustomers[440][]=415442;
            $hcCustomers[440][]=415450;
            $hcCustomers[440][]=415453;
            $hcCustomers[440][]=415463;
            $hcCustomers[440][]=415463;
            $hcCustomers[440][]=415464;
            $hcCustomers[440][]=415475;
            $hcCustomers[440][]=415477;
            $hcCustomers[440][]=415501;
            $hcCustomers[440][]=415505;
            $hcCustomers[440][]=415510;
            $hcCustomers[440][]=415511;
            $hcCustomers[440][]=415536;
            $hcCustomers[440][]=415537;
            $hcCustomers[440][]=415537;
            $hcCustomers[440][]=415599;
            $hcCustomers[440][]=415600;
            $hcCustomers[440][]=415607;
            $hcCustomers[440][]=415615;
            $hcCustomers[440][]=415630;
            $hcCustomers[440][]=415636;
            $hcCustomers[440][]=415642;
            $hcCustomers[440][]=415669;
            $hcCustomers[440][]=415680;
            $hcCustomers[440][]=415682;
            $hcCustomers[440][]=415689;
            $hcCustomers[445][]=415701;
            $hcCustomers[445][]=415702;
            $hcCustomers[445][]=415710;
            $hcCustomers[445][]=415715;
            $hcCustomers[445][]=415720;
            $hcCustomers[445][]=415730;
            $hcCustomers[445][]=415740;
            $hcCustomers[445][]=415749;
            $hcCustomers[445][]=415749;
            $hcCustomers[445][]=415766;
            $hcCustomers[445][]=415778;
            $hcCustomers[440][]=415787;
            $hcCustomers[440][]=415823;
            $hcCustomers[440][]=415827;
            $hcCustomers[440][]=415839;
            $hcCustomers[440][]=415840;
            $hcCustomers[440][]=415847;
            $hcCustomers[440][]=415849;
            $hcCustomers[440][]=415874;
            $hcCustomers[440][]=415895;
            $hcCustomers[440][]=415909;
            $hcCustomers[440][]=415914;
            $hcCustomers[445][]=415936;
            $hcCustomers[445][]=415937;
            $hcCustomers[440][]=415945;
            $hcCustomers[440][]=415957;
            $hcCustomers[440][]=415962;
            $hcCustomers[445][]=415964;
            $hcCustomers[440][]=415967;
            $hcCustomers[440][]=416303;
            $hcCustomers[440][]=416466;
            $hcCustomers[440][]=416477;
            $hcCustomers[451][]=416827;
            $hcCustomers[448][]=417362;
            $hcCustomers[444][]=417964;
            $hcCustomers[445][]=418066;
            $hcCustomers[454][]=418184;
            $hcCustomers[446][]=418969;
            $hcCustomers[440][]=419092;
            $hcCustomers[444][]=419167;
            $hcCustomers[441][]=419336;
            $hcCustomers[441][]=419336;
            $hcCustomers[451][]=419386;
            $hcCustomers[453][]=419521;
            $hcCustomers[445][]=419723;
            $hcCustomers[443][]=419772;
            $hcCustomers[444][]=419859;
            $hcCustomers[440][]=419891;
            $hcCustomers[454][]=420652;
            $hcCustomers[440][]=421989;
            $hcCustomers[440][]=422248;
            $hcCustomers[440][]=423082;
            $hcCustomers[446][]=423115;
            $hcCustomers[440][]=423204;
            $hcCustomers[444][]=423307;
            $hcCustomers[444][]=423493;
            $hcCustomers[451][]=423570;
            $hcCustomers[451][]=423985;
            $hcCustomers[440][]=424288;
            $hcCustomers[451][]=424296;
            $hcCustomers[440][]=424425;
            $hcCustomers[440][]=425339;
            $hcCustomers[453][]=425443;
            $hcCustomers[453][]=425678;
            $hcCustomers[451][]=425763;
            $hcCustomers[445][]=425975;
            $hcCustomers[445][]=426462;
            $hcCustomers[440][]=427082;
            $hcCustomers[453][]=427083;
            $hcCustomers[440][]=427285;
            $hcCustomers[445][]=427743;
            $hcCustomers[445][]=427743;
            $hcCustomers[454][]=428048;
            $hcCustomers[440][]=428143;
            $hcCustomers[440][]=428282;
            $hcCustomers[441][]=428339;
            $hcCustomers[446][]=429235;
            $hcCustomers[440][]=429974;
            $hcCustomers[446][]=430825;
            $hcCustomers[451][]=430846;
            $hcCustomers[440][]=431189;
            $hcCustomers[445][]=431772;
            $hcCustomers[440][]=432263;
            $hcCustomers[444][]=432513;
            $hcCustomers[451][]=432516;
            $hcCustomers[454][]=432541;
            $hcCustomers[453][]=433091;
            $hcCustomers[440][]=433733;
            $hcCustomers[440][]=434154;
            $hcCustomers[447][]=434568;
            $hcCustomers[440][]=435242;
            $hcCustomers[440][]=436018;
            $hcCustomers[451][]=436306;
            $hcCustomers[440][]=448002;
            $hcCustomers[440][]=448219;
            $hcCustomers[440][]=448398;
            $hcCustomers[451][]=448458;
            $hcCustomers[440][]=448687;
            $hcCustomers[440][]=448957;
            $hcCustomers[445][]=449234;
            $hcCustomers[440][]=449367;
            $hcCustomers[440][]=449670;
            $hcCustomers[453][]=449973;
            $hcCustomers[440][]=450126;
            $hcCustomers[440][]=450173;
            $hcCustomers[453][]=450354;
            $hcCustomers[454][]=450390;
            $hcCustomers[440][]=450488;
            $hcCustomers[445][]=450540;
            $hcCustomers[440][]=450559;
            $hcCustomers[445][]=450827;
            $hcCustomers[440][]=451098;
            $hcCustomers[440][]=452065;
            $hcCustomers[451][]=452919;
            $hcCustomers[440][]=453106;
            $hcCustomers[441][]=454236;
            $hcCustomers[441][]=454251;
            $hcCustomers[441][]=454768;
            $hcCustomers[441][]=455277;
            $hcCustomers[441][]=455930;
            $hcCustomers[441][]=455966;
            $hcCustomers[441][]=455989;
            $hcCustomers[441][]=456714;
            $hcCustomers[441][]=457949;
            $hcCustomers[453][]=458817;
            $hcCustomers[453][]=459418;
            $hcCustomers[445][]=462291;
            $hcCustomers[448][]=469207;
            $hcCustomers[448][]=469265;
            $hcCustomers[448][]=469480;
            $hcCustomers[448][]=469572;
            $hcCustomers[448][]=469865;
            $hcCustomers[448][]=469937;
            $hcCustomers[448][]=470222;
            $hcCustomers[440][]=471115;
            $hcCustomers[454][]=471727;
            $hcCustomers[440][]=474340;
            $hcCustomers[446][]=474551;
            $hcCustomers[451][]=474723;
            $hcCustomers[445][]=475160;
            $hcCustomers[440][]=476047;
            $hcCustomers[440][]=476226;
            $hcCustomers[444][]=476445;
            $hcCustomers[444][]=477189;
            $hcCustomers[444][]=477843;
            $hcCustomers[451][]=480763;
            $hcCustomers[442][]=480890;
            $hcCustomers[453][]=481276;
            $hcCustomers[442][]=481543;
            $hcCustomers[446][]=481603;
            $hcCustomers[440][]=481621;
            $hcCustomers[451][]=482085;
            $hcCustomers[445][]=482122;
            $hcCustomers[445][]=482122;
            $hcCustomers[445][]=482767;
            $hcCustomers[440][]=482860;
            $hcCustomers[445][]=483016;
            $hcCustomers[440][]=483186;
            $hcCustomers[454][]=483418;
            $hcCustomers[444][]=483434;
            $hcCustomers[444][]=483483;
            $hcCustomers[453][]=484006;
            $hcCustomers[440][]=484010;
            $hcCustomers[453][]=484296;
            $hcCustomers[440][]=484646;
            $hcCustomers[454][]=484743;
            $hcCustomers[445][]=484903;
            $hcCustomers[451][]=484933;
            $hcCustomers[451][]=484964;
            $hcCustomers[440][]=485074;
            $hcCustomers[451][]=485207;
            $hcCustomers[453][]=485472;
            $hcCustomers[445][]=485567;
            $hcCustomers[440][]=486280;
            $hcCustomers[453][]=486754;
            $hcCustomers[440][]=487187;
            $hcCustomers[453][]=488412;
            $hcCustomers[453][]=488862;
            $hcCustomers[445][]=488863;
            $hcCustomers[446][]=488902;
            $hcCustomers[440][]=489252;
            $hcCustomers[453][]=489346;
            $hcCustomers[451][]=489456;
            $hcCustomers[446][]=490128;
            $hcCustomers[445][]=490128;
            $hcCustomers[445][]=490128;
            $hcCustomers[454][]=490128;
            $hcCustomers[445][]=490415;
            $hcCustomers[445][]=490415;
            $hcCustomers[448][]=490930;
            $hcCustomers[445][]=492408;
            $hcCustomers[451][]=492551;
            $hcCustomers[446][]=492625;
            $hcCustomers[445][]=492867;
            $hcCustomers[440][]=492982;
            $hcCustomers[453][]=493421;
            $hcCustomers[445][]=494156;
            $hcCustomers[440][]=494217;
            $hcCustomers[440][]=494217;
            $hcCustomers[451][]=494552;
            $hcCustomers[440][]=494591;
            $hcCustomers[454][]=494853;
            $hcCustomers[440][]=495010;
            $hcCustomers[440][]=495053;
            $hcCustomers[454][]=495112;
            $hcCustomers[441][]=495198;
            $hcCustomers[451][]=495547;
            $hcCustomers[445][]=501672;
            $hcCustomers[440][]=502388;
            $hcCustomers[446][]=502559;
            $hcCustomers[445][]=502871;
            $hcCustomers[445][]=502871;
            $hcCustomers[454][]=502871;
            $hcCustomers[445][]=503317;
            $hcCustomers[440][]=503318;
            $hcCustomers[440][]=503477;
            $hcCustomers[446][]=503501;
            $hcCustomers[446][]=503501;
            $hcCustomers[446][]=503501;
            $hcCustomers[440][]=503511;
            $hcCustomers[440][]=503532;
            $hcCustomers[440][]=503883;
            $hcCustomers[440][]=504022;
            $hcCustomers[440][]=504105;
            $hcCustomers[445][]=504173;
            $hcCustomers[444][]=504370;
            $hcCustomers[451][]=504388;
            $hcCustomers[440][]=504411;
            $hcCustomers[440][]=504411;
            $hcCustomers[445][]=504471;
            $hcCustomers[445][]=504471;
            $hcCustomers[440][]=504538;
            $hcCustomers[445][]=504619;
            $hcCustomers[446][]=505166;
            $hcCustomers[447][]=505504;
            $hcCustomers[447][]=505513;
            $hcCustomers[447][]=505544;
            $hcCustomers[447][]=505552;
            $hcCustomers[447][]=505571;
            $hcCustomers[447][]=505580;
            $hcCustomers[447][]=505587;
            $hcCustomers[447][]=505608;
            $hcCustomers[447][]=505610;
            $hcCustomers[447][]=505623;
            $hcCustomers[447][]=505633;
            $hcCustomers[447][]=505633;
            $hcCustomers[447][]=505666;
            $hcCustomers[447][]=505681;
            $hcCustomers[447][]=505702;
            $hcCustomers[447][]=505708;
            $hcCustomers[447][]=505745;
            $hcCustomers[447][]=505783;
            $hcCustomers[447][]=505791;
            $hcCustomers[447][]=505795;
            $hcCustomers[447][]=505822;
            $hcCustomers[447][]=505832;
            $hcCustomers[447][]=505843;
            $hcCustomers[447][]=505845;
            $hcCustomers[444][]=505849;
            $hcCustomers[447][]=505852;
            $hcCustomers[447][]=505852;
            $hcCustomers[447][]=505858;
            $hcCustomers[447][]=505880;
            $hcCustomers[447][]=505885;
            $hcCustomers[447][]=505907;
            $hcCustomers[447][]=505910;
            $hcCustomers[447][]=505914;
            $hcCustomers[447][]=505915;
            $hcCustomers[447][]=505923;
            $hcCustomers[447][]=505937;
            $hcCustomers[447][]=505938;
            $hcCustomers[447][]=505982;
            $hcCustomers[447][]=506004;
            $hcCustomers[447][]=506019;
            $hcCustomers[447][]=506027;
            $hcCustomers[447][]=506030;
            $hcCustomers[447][]=506030;
            $hcCustomers[447][]=506037;
            $hcCustomers[447][]=506055;
            $hcCustomers[447][]=506065;
            $hcCustomers[447][]=506071;
            $hcCustomers[447][]=506079;
            $hcCustomers[447][]=506084;
            $hcCustomers[447][]=506088;
            $hcCustomers[447][]=506140;
            $hcCustomers[447][]=506160;
            $hcCustomers[447][]=506179;
            $hcCustomers[447][]=506181;
            $hcCustomers[447][]=506192;
            $hcCustomers[447][]=506227;
            $hcCustomers[447][]=506234;
            $hcCustomers[447][]=506259;
            $hcCustomers[447][]=506261;
            $hcCustomers[447][]=506264;
            $hcCustomers[447][]=506297;
            $hcCustomers[447][]=506317;
            $hcCustomers[445][]=506341;
            $hcCustomers[447][]=506345;
            $hcCustomers[447][]=506350;
            $hcCustomers[447][]=506362;
            $hcCustomers[447][]=506381;
            $hcCustomers[447][]=506426;
            $hcCustomers[447][]=506428;
            $hcCustomers[440][]=540027;
            $hcCustomers[445][]=540621;
            $hcCustomers[451][]=541073;
            $hcCustomers[451][]=541656;
            $hcCustomers[440][]=580485;
            $hcCustomers[445][]=581091;
            $hcCustomers[446][]=581132;
            $hcCustomers[451][]=581132;
            $hcCustomers[440][]=582056;
            $hcCustomers[440][]=582192;
            $hcCustomers[444][]=582243;
            $hcCustomers[451][]=582990;
            $hcCustomers[445][]=583303;
            $hcCustomers[454][]=583514;
            $hcCustomers[453][]=591051;
            $hcCustomers[454][]=591225;
            $hcCustomers[447][]=591284;
            $hcCustomers[445][]=591406;
            $hcCustomers[445][]=591406;
            $hcCustomers[445][]=591406;
            $hcCustomers[444][]=591611;
            $hcCustomers[444][]=591611;
            $hcCustomers[440][]=591837;
            $hcCustomers[451][]=592305;
            $hcCustomers[454][]=592455;
            $hcCustomers[445][]=592455;
            $hcCustomers[451][]=593006;
            $hcCustomers[440][]=593098;
            $hcCustomers[447][]=593155;
            $hcCustomers[440][]=593583;
            $hcCustomers[451][]=593673;
            $hcCustomers[447][]=593959;
            $hcCustomers[451][]=593962;
            $hcCustomers[451][]=594332;
            $hcCustomers[440][]=594904;
            $hcCustomers[446][]=594906;
            $hcCustomers[440][]=595096;
            $hcCustomers[440][]=595159;
            $hcCustomers[451][]=595681;
            $hcCustomers[445][]=596097;
            $hcCustomers[440][]=596104;
            $hcCustomers[440][]=596109;
            $hcCustomers[447][]=596295;
            $hcCustomers[447][]=596311;
            $hcCustomers[447][]=596311;
            $hcCustomers[447][]=596478;
            $hcCustomers[447][]=596527;
            $hcCustomers[451][]=596673;
            $hcCustomers[454][]=596758;
            $hcCustomers[440][]=596830;
            $hcCustomers[445][]=596865;
            $hcCustomers[440][]=596874;
            $hcCustomers[453][]=597062;
            $hcCustomers[445][]=597079;
            $hcCustomers[440][]=597122;
            $hcCustomers[445][]=597157;
            $hcCustomers[445][]=597157;
            $hcCustomers[454][]=597324;
            $hcCustomers[453][]=597442;
            $hcCustomers[447][]=597647;
            $hcCustomers[440][]=598216;
            $hcCustomers[440][]=598304;
            $hcCustomers[440][]=598541;
            $hcCustomers[440][]=598675;
            $hcCustomers[446][]=599430;
            $hcCustomers[441][]=599580;
            $hcCustomers[445][]=599867;
            $hcCustomers[445][]=599867;
            $hcCustomers[445][]=599867;
            $hcCustomers[454][]=600238;
            $hcCustomers[446][]=600241;
            $hcCustomers[440][]=604594;
            $hcCustomers[440][]=604952;
            $hcCustomers[440][]=605253;
            $hcCustomers[440][]=605288;
            $hcCustomers[440][]=605333;
            $hcCustomers[440][]=605361;
            $hcCustomers[440][]=605389;
            $hcCustomers[440][]=605403;
            $hcCustomers[440][]=605403;
            $hcCustomers[445][]=605405;
            $hcCustomers[445][]=606026;
            $hcCustomers[451][]=606038;
            $hcCustomers[445][]=606058;
            $hcCustomers[445][]=606058;
            $hcCustomers[445][]=606836;
            $hcCustomers[451][]=606841;
            $hcCustomers[453][]=607310;
            $hcCustomers[445][]=607332;
            $hcCustomers[453][]=607412;
            $hcCustomers[440][]=607449;
            $hcCustomers[447][]=608152;
            $hcCustomers[445][]=608332;
            $hcCustomers[440][]=608521;
            $hcCustomers[440][]=608615;
            $hcCustomers[447][]=609354;
            $hcCustomers[440][]=609362;
            $hcCustomers[444][]=609389;
            $hcCustomers[453][]=609497;
            $hcCustomers[440][]=609615;
            $hcCustomers[454][]=609841;
            $hcCustomers[440][]=610134;
            $hcCustomers[440][]=610136;
            $hcCustomers[440][]=610180;
            $hcCustomers[445][]=610512;
            $hcCustomers[444][]=610558;
            $hcCustomers[444][]=610838;
            $hcCustomers[440][]=611035;
            $hcCustomers[440][]=611240;
            $hcCustomers[440][]=611433;
            $hcCustomers[451][]=611683;
            $hcCustomers[454][]=611818;
            $hcCustomers[445][]=612000;
            $hcCustomers[445][]=612000;
            $hcCustomers[445][]=612000;
            $hcCustomers[442][]=612426;
            $hcCustomers[449][]=612465;
            $hcCustomers[440][]=613931;
            $hcCustomers[449][]=618057;
            $hcCustomers[449][]=618204;
            $hcCustomers[449][]=618260;
            $hcCustomers[449][]=618277;
            $hcCustomers[449][]=618287;
            $hcCustomers[449][]=618301;
            $hcCustomers[449][]=618314;
            $hcCustomers[449][]=618344;
            $hcCustomers[449][]=618403;
            $hcCustomers[449][]=618404;
            $hcCustomers[454][]=618488;
            $hcCustomers[449][]=618502;
            $hcCustomers[449][]=618540;
            $hcCustomers[449][]=618610;
            $hcCustomers[440][]=618610;
            $hcCustomers[449][]=618650;
            $hcCustomers[449][]=618758;
            $hcCustomers[449][]=618812;
            $hcCustomers[449][]=618819;
            $hcCustomers[449][]=618863;
            $hcCustomers[449][]=618983;
            $hcCustomers[449][]=618986;
            $hcCustomers[449][]=619062;
            $hcCustomers[449][]=619070;
            $hcCustomers[449][]=619099;
            $hcCustomers[449][]=619107;
            $hcCustomers[449][]=619109;
            $hcCustomers[449][]=619111;
            $hcCustomers[449][]=619221;
            $hcCustomers[449][]=619259;
            $hcCustomers[449][]=619262;
            $hcCustomers[449][]=619306;
            $hcCustomers[449][]=619335;
            $hcCustomers[449][]=619335;
            $hcCustomers[449][]=619341;
            $hcCustomers[449][]=619346;
            $hcCustomers[449][]=619380;
            $hcCustomers[449][]=619393;
            $hcCustomers[449][]=619410;
            $hcCustomers[449][]=619452;
            $hcCustomers[449][]=619474;
            $hcCustomers[449][]=619478;
            $hcCustomers[449][]=619478;
            $hcCustomers[449][]=619505;
            $hcCustomers[449][]=619505;
            $hcCustomers[449][]=619561;
            $hcCustomers[451][]=623097;
            $hcCustomers[451][]=623097;
            $hcCustomers[445][]=623729;
            $hcCustomers[453][]=623976;
            $hcCustomers[445][]=624363;
            $hcCustomers[440][]=624417;
            $hcCustomers[447][]=625181;
            $hcCustomers[440][]=625428;
            $hcCustomers[440][]=625969;
            $hcCustomers[440][]=625969;
            $hcCustomers[451][]=626045;
            $hcCustomers[444][]=626146;
            $hcCustomers[440][]=626680;
            $hcCustomers[451][]=626747;
            $hcCustomers[440][]=626782;
            $hcCustomers[444][]=626804;
            $hcCustomers[442][]=626984;
            $hcCustomers[454][]=626992;
            $hcCustomers[445][]=627148;
            $hcCustomers[445][]=627148;
            $hcCustomers[445][]=627148;
            $hcCustomers[445][]=627148;
            $hcCustomers[445][]=627195;
            $hcCustomers[451][]=627211;
            $hcCustomers[451][]=627211;
            $hcCustomers[444][]=627306;
            $hcCustomers[453][]=627332;
            $hcCustomers[444][]=627344;
            $hcCustomers[440][]=627365;
            $hcCustomers[442][]=627386;
            $hcCustomers[444][]=627534;
            $hcCustomers[453][]=627547;
            $hcCustomers[440][]=627688;
            $hcCustomers[454][]=627702;
            $hcCustomers[445][]=627891;
            $hcCustomers[445][]=627894;
            $hcCustomers[445][]=627922;
            $hcCustomers[445][]=627927;
            $hcCustomers[445][]=627950;
            $hcCustomers[445][]=627950;
            $hcCustomers[445][]=627962;
            $hcCustomers[445][]=627962;
            $hcCustomers[445][]=627962;
            $hcCustomers[445][]=627962;
            $hcCustomers[445][]=627968;
            $hcCustomers[446][]=628157;
            $hcCustomers[446][]=628157;
            $hcCustomers[446][]=628157;
            $hcCustomers[446][]=628168;
            $hcCustomers[444][]=628193;
            $hcCustomers[444][]=628255;
            $hcCustomers[443][]=628428;
            $hcCustomers[440][]=628511;
            $hcCustomers[446][]=630060;
            $hcCustomers[444][]=630183;
            $hcCustomers[440][]=630222;
            $hcCustomers[444][]=630509;
            $hcCustomers[454][]=630570;
            $hcCustomers[451][]=630641;
            $hcCustomers[445][]=630817;
            $hcCustomers[454][]=631258;
            $hcCustomers[453][]=631737;
            $hcCustomers[440][]=631964;
            $hcCustomers[451][]=631981;
            $hcCustomers[451][]=632205;
            $hcCustomers[453][]=633181;
            $hcCustomers[454][]=633430;
            $hcCustomers[451][]=633489;
            $hcCustomers[454][]=634330;
            $hcCustomers[451][]=634724;
            $hcCustomers[440][]=634802;
            $hcCustomers[451][]=634866;
            $hcCustomers[440][]=634897;
            $hcCustomers[440][]=634924;
            $hcCustomers[453][]=634966;
            $hcCustomers[440][]=634985;
            $hcCustomers[453][]=635011;
            $hcCustomers[453][]=635039;
            $hcCustomers[453][]=635064;
            $hcCustomers[440][]=635916;
            $hcCustomers[454][]=636213;
            $hcCustomers[446][]=636727;
            $hcCustomers[440][]=636738;
            $hcCustomers[443][]=636770;
            $hcCustomers[443][]=636770;
            $hcCustomers[442][]=636796;
            $hcCustomers[444][]=636835;
            $hcCustomers[444][]=636923;
            $hcCustomers[451][]=637159;
            $hcCustomers[445][]=637300;
            $hcCustomers[440][]=637315;
            $hcCustomers[444][]=637429;
            $hcCustomers[453][]=637478;
            $hcCustomers[454][]=638753;
            $hcCustomers[445][]=639252;
            $hcCustomers[451][]=639513;
            $hcCustomers[440][]=639565;
            $hcCustomers[440][]=639761;
            $hcCustomers[454][]=640039;
            $hcCustomers[440][]=640099;
            $hcCustomers[445][]=640202;
            $hcCustomers[440][]=640328;
            $hcCustomers[440][]=640328;
            $hcCustomers[445][]=640527;
            $hcCustomers[443][]=640544;
            $hcCustomers[444][]=640553;
            $hcCustomers[445][]=640601;
            $hcCustomers[440][]=640656;
            $hcCustomers[453][]=640770;
            $hcCustomers[440][]=640800;
            $hcCustomers[445][]=640801;
            $hcCustomers[453][]=641020;
            $hcCustomers[440][]=641190;
            $hcCustomers[440][]=641221;
            $hcCustomers[440][]=641290;
            $hcCustomers[440][]=641450;
            $hcCustomers[446][]=641451;
            $hcCustomers[440][]=641487;
            $hcCustomers[440][]=641497;
            $hcCustomers[451][]=641565;
            $hcCustomers[451][]=641565;
            $hcCustomers[444][]=641743;
            $hcCustomers[440][]=641770;
            $hcCustomers[447][]=641855;
            $hcCustomers[447][]=641930;
            $hcCustomers[447][]=641935;
            $hcCustomers[440][]=642177;
            $hcCustomers[445][]=642180;
            $hcCustomers[453][]=642185;
            $hcCustomers[444][]=642542;
            $hcCustomers[446][]=642572;
            $hcCustomers[454][]=642650;
            $hcCustomers[445][]=642690;
            $hcCustomers[453][]=642783;
            $hcCustomers[440][]=642873;
            $hcCustomers[440][]=642926;
            $hcCustomers[453][]=642950;
            $hcCustomers[442][]=643050;
            $hcCustomers[447][]=643210;
            $hcCustomers[445][]=643333;
            $hcCustomers[440][]=643390;
            $hcCustomers[445][]=643548;
            $hcCustomers[440][]=643555;
            $hcCustomers[440][]=643579;
            $hcCustomers[454][]=643682;
            $hcCustomers[440][]=643694;
            $hcCustomers[445][]=644374;
            $hcCustomers[440][]=644573;
            $hcCustomers[445][]=644989;
            $hcCustomers[445][]=644989;
            $hcCustomers[446][]=645338;
            $hcCustomers[440][]=646194;
            $hcCustomers[445][]=646832;
            $hcCustomers[451][]=647035;
            $hcCustomers[440][]=647344;
            $hcCustomers[440][]=647832;
            $hcCustomers[445][]=648047;
            $hcCustomers[445][]=648223;
            $hcCustomers[445][]=648223;
            $hcCustomers[445][]=648223;
            $hcCustomers[440][]=648531;
            $hcCustomers[444][]=648599;
            $hcCustomers[445][]=648614;
            $hcCustomers[451][]=648677;
            $hcCustomers[445][]=649345;
            $hcCustomers[451][]=649631;
            $hcCustomers[444][]=649907;
            $hcCustomers[444][]=649907;
            $hcCustomers[446][]=649978;
            $hcCustomers[440][]=650004;
            $hcCustomers[444][]=650060;
            $hcCustomers[445][]=650060;
            $hcCustomers[440][]=650060;
            $hcCustomers[445][]=650107;
            $hcCustomers[444][]=650726;
            $hcCustomers[442][]=650848;
            $hcCustomers[445][]=651682;
            $hcCustomers[440][]=652165;
            $hcCustomers[453][]=652783;
            $hcCustomers[440][]=652973;
            $hcCustomers[442][]=653362;
            $hcCustomers[441][]=653995;
            $hcCustomers[451][]=654684;
            $hcCustomers[445][]=654706;
            $hcCustomers[440][]=654758;
            $hcCustomers[451][]=654986;
            $hcCustomers[447][]=655148;
            $hcCustomers[440][]=655425;
            $hcCustomers[440][]=655543;
            $hcCustomers[444][]=655779;
            $hcCustomers[453][]=656726;
            $hcCustomers[451][]=657566;
            $hcCustomers[451][]=657566;
            $hcCustomers[451][]=657566;
            $hcCustomers[446][]=657566;
            $hcCustomers[440][]=658026;
            $hcCustomers[445][]=658464;
            $hcCustomers[442][]=658547;
            $hcCustomers[440][]=658554;
            $hcCustomers[445][]=658667;
            $hcCustomers[451][]=659022;
            $hcCustomers[440][]=659022;
            $hcCustomers[440][]=659158;
            $hcCustomers[440][]=659189;
            $hcCustomers[440][]=659189;
            $hcCustomers[451][]=659326;
            $hcCustomers[445][]=659465;
            $hcCustomers[440][]=659473;
            $hcCustomers[440][]=659474;
            $hcCustomers[454][]=659888;
            $hcCustomers[440][]=660211;
            $hcCustomers[440][]=660446;
            $hcCustomers[453][]=660546;
            $hcCustomers[453][]=660594;
            $hcCustomers[454][]=660706;
            $hcCustomers[440][]=660738;
            $hcCustomers[440][]=661698;
            $hcCustomers[451][]=662527;
            $hcCustomers[451][]=662696;
            $hcCustomers[446][]=662696;
            $hcCustomers[445][]=662972;
            $hcCustomers[442][]=663100;
            $hcCustomers[440][]=663372;
            $hcCustomers[446][]=663374;
            $hcCustomers[442][]=7030043;
            $hcCustomers[442][]=7030043;
            $hcCustomers[440][]=7030053;
            $hcCustomers[440][]=7030554;
            $hcCustomers[440][]=7030554;
            $hcCustomers[445][]=7032350;
            $hcCustomers[451][]=7032773;
            $hcCustomers[453][]=7034225;
            $hcCustomers[440][]=7036686;
            $hcCustomers[440][]=7037117;
            $hcCustomers[445][]=7037138;
            $hcCustomers[454][]=7037239;
            $hcCustomers[440][]=7039228;
            $hcCustomers[449][]=7039365;
            $hcCustomers[449][]=7039365;
            $hcCustomers[440][]=7040536;
            $hcCustomers[453][]=7041312;
            $hcCustomers[446][]=7043270;
            $hcCustomers[447][]=7043456;
            $hcCustomers[445][]=7043602;
            $hcCustomers[447][]=7043632;
            $hcCustomers[440][]=7044132;
            $hcCustomers[440][]=7044587;
            $hcCustomers[446][]=7044646;
            $hcCustomers[447][]=7045770;
            $hcCustomers[440][]=7045771;
            $hcCustomers[444][]=7045775;
            $hcCustomers[445][]=7047310;
            $hcCustomers[451][]=7048606;
            $hcCustomers[440][]=7049173;
            $hcCustomers[453][]=7049731;
            $hcCustomers[440][]=7050149;
            $hcCustomers[440][]=7050149;
            $hcCustomers[451][]=7051404;
            $hcCustomers[442][]=7051565;
            $hcCustomers[447][]=7052151;
            $hcCustomers[440][]=7055917;
            $hcCustomers[445][]=7057548;
            $hcCustomers[440][]=7057560;
            $hcCustomers[445][]=7057565;
            $hcCustomers[445][]=7058050;
            $hcCustomers[440][]=7060281;
            $hcCustomers[451][]=7068472;
            $hcCustomers[451][]=7068831;
            $hcCustomers[440][]=7070187;
            $hcCustomers[440][]=7070385;
            $hcCustomers[445][]=7070387;
            $hcCustomers[445][]=7070429;
            $hcCustomers[444][]=7071588;
            $hcCustomers[451][]=7074675;
            $hcCustomers[445][]=7075072;
            $hcCustomers[453][]=7078528;
            $hcCustomers[440][]=7079316;
            $hcCustomers[446][]=7081837;
            $hcCustomers[451][]=7082127;
            $hcCustomers[440][]=7084854;
            $hcCustomers[444][]=7085686;
            $hcCustomers[449][]=7087605;
            $hcCustomers[454][]=7087762;
            $hcCustomers[451][]=7088777;
            $hcCustomers[451][]=7089152;
            $hcCustomers[440][]=7093160;
            $hcCustomers[440][]=7093325;
            $hcCustomers[440][]=7093427;
            $hcCustomers[445][]=7094687;
            $hcCustomers[454][]=7095149;
            $hcCustomers[451][]=7095242;
            $hcCustomers[445][]=7098268;
            $hcCustomers[451][]=7098305;
            $hcCustomers[453][]=7098307;
            $hcCustomers[444][]=7098433;
            $hcCustomers[445][]=7098458;
            $hcCustomers[444][]=7098481;
            $hcCustomers[442][]=7098487;
            $hcCustomers[444][]=7098564;
            $hcCustomers[451][]=7098566;
            $hcCustomers[444][]=7098581;
            $hcCustomers[444][]=7098584;
            $hcCustomers[445][]=7098611;
            $hcCustomers[446][]=7098615;
            $hcCustomers[453][]=7098683;
            $hcCustomers[445][]=7098685;
            $hcCustomers[454][]=7098768;
            $hcCustomers[445][]=7098771;
            $hcCustomers[444][]=7098795;
            $hcCustomers[451][]=7098808;
            $hcCustomers[453][]=7098810;
            $hcCustomers[440][]=7098819;
            $hcCustomers[440][]=7098820;
            $hcCustomers[440][]=7098822;
            $hcCustomers[453][]=7098823;
            $hcCustomers[454][]=7098825;
            $hcCustomers[454][]=7098826;
            $hcCustomers[453][]=7098827;
            $hcCustomers[453][]=7098828;
            $hcCustomers[454][]=7098829;
            $hcCustomers[453][]=7098831;
            $hcCustomers[454][]=7098832;
            $hcCustomers[454][]=7098835;
            $hcCustomers[458][]=7060227;
            $hcCustomers[458][]=7058574;
            $hcCustomers[458][]=7059407;
            $hcCustomers[458][]=7058504;
            $hcCustomers[458][]=7058865;
            $hcCustomers[458][]=7058504;
            $hcCustomers[458][]=7058156;
            $hcCustomers[458][]=7058463;
            $hcCustomers[458][]=7059610;
            $hcCustomers[458][]=7060308;
            $hcCustomers[458][]=7058334;
            $hcCustomers[458][]=7058096;
            $hcCustomers[458][]=7060411;
            $hcCustomers[458][]=7059224;
            $hcCustomers[458][]=7059977;
            $hcCustomers[458][]=7059293;
            $hcCustomers[458][]=7059790;
            $hcCustomers[458][]=7059677;
            $hcCustomers[458][]=7058520;
            $hcCustomers[458][]=7058520;
            $hcCustomers[458][]=7058572;
            $hcCustomers[458][]=7059738;
            $hcCustomers[458][]=7060501;
            $hcCustomers[458][]=7059738;
            $hcCustomers[458][]=7059779;
            $hcCustomers[458][]=7059632;
            $hcCustomers[458][]=7060617;
            $hcCustomers[458][]=7059389;
            $hcCustomers[458][]=7060311;
            $hcCustomers[458][]=7060084;
            $hcCustomers[458][]=7060378;
            $hcCustomers[458][]=7058009;
            $hcCustomers[458][]=7060309;
            $hcCustomers[458][]=7058124;
            $hcCustomers[458][]=7058918;
            $hcCustomers[458][]=7060229;
            $hcCustomers[458][]=7059637;
            $hcCustomers[458][]=7060217;
            $hcCustomers[458][]=7059346;
            $hcCustomers[458][]=7059010;
            $hcCustomers[458][]=7059676;
            $hcCustomers[458][]=7058681;
            $hcCustomers[458][]=7058963;
            $hcCustomers[458][]=7059380;
            $hcCustomers[458][]=7058867;
            $hcCustomers[458][]=7060383;
            $hcCustomers[458][]=7058251;
            $hcCustomers[458][]=7058199;
            $hcCustomers[458][]=7058310;
            $hcCustomers[458][]=7060004;
            $hcCustomers[458][]=7059182;
            $hcCustomers[458][]=7060565;
            $hcCustomers[458][]=7058350;
            $hcCustomers[458][]=7060770;
            $hcCustomers[458][]=7058738;
            $hcCustomers[458][]=7058320;
            $hcCustomers[458][]=7059488;
            $hcCustomers[458][]=7059180;
            $hcCustomers[458][]=7060803;
            $hcCustomers[458][]=7060530;
            $hcCustomers[458][]=7059168;
            $hcCustomers[458][]=7060622;
            $hcCustomers[458][]=7059654;
            $hcCustomers[458][]=7059260;
            $hcCustomers[458][]=7058243;
            $hcCustomers[458][]=7058243;
            $hcCustomers[458][]=7058738;
            $hcCustomers[458][]=7058863;
            $hcCustomers[458][]=7058863;
            $hcCustomers[458][]=7059055;
            $hcCustomers[458][]=7059573;
            $hcCustomers[458][]=7060480;
            $hcCustomers[458][]=7060223;
            $hcCustomers[458][]=7058541;
            $hcCustomers[458][]=7060334;
            $hcCustomers[458][]=7058814;
            $hcCustomers[458][]=7059205;
            $hcCustomers[458][]=7059016;
            $hcCustomers[458][]=7058675;
            $hcCustomers[458][]=7059377;
            $hcCustomers[458][]=7058001;
            $hcCustomers[458][]=7058043;
            $hcCustomers[458][]=7060372;
            $hcCustomers[458][]=7060112;
            $hcCustomers[458][]=7058611;
            $hcCustomers[458][]=7058981;
            $hcCustomers[458][]=7058628;
            $hcCustomers[458][]=7060475;
            $hcCustomers[458][]=7059784;
            $hcCustomers[458][]=7060361;
            $hcCustomers[458][]=7060609;
            $hcCustomers[458][]=7059861;
            $hcCustomers[458][]=7059324;
            $hcCustomers[458][]=7060440;
            $hcCustomers[458][]=7058693;
            $hcCustomers[458][]=7058057;
            $hcCustomers[458][]=7060334;
            $hcCustomers[458][]=7060421;
            $hcCustomers[458][]=612147;
            $hcCustomers[458][]=7098930;
            $hcCustomers[458][]=7058999;
            $hcCustomers[458][]=7058694;
            $hcCustomers[458][]=7059909;
            $hcCustomers[458][]=7059503;
            $hcCustomers[458][]=7058765;
            $hcCustomers[458][]=7060586;
            $hcCustomers[458][]=7058985;
            $hcCustomers[458][]=7059528;
            $hcCustomers[458][]=7060574;
            $hcCustomers[458][]=7060382;
            $hcCustomers[458][]=7059553;
            $hcCustomers[458][]=7059553;
            $hcCustomers[458][]=7059553;
            $hcCustomers[458][]=7059372;
            $hcCustomers[458][]=7059387;
            $hcCustomers[458][]=7059806;
            $hcCustomers[458][]=7058244;
            $hcCustomers[458][]=7076161;
            $hcCustomers[458][]=7058452;
            $hcCustomers[458][]=7051150;
            $hcCustomers[458][]=7059769;
            $hcCustomers[458][]=7060332;
            $hcCustomers[458][]=7060332;
            $hcCustomers[458][]=7059637;
            $hcCustomers[458][]=7060054;
            $hcCustomers[458][]=7060066;
            $hcCustomers[458][]=7059294;
            $hcCustomers[458][]=7058074;
            $hcCustomers[458][]=7060018;
            $hcCustomers[458][]=7060314;
            $hcCustomers[458][]=7060168;
            $hcCustomers[458][]=7058908;
            $hcCustomers[458][]=7058136;
            $hcCustomers[458][]=7059432;
            $hcCustomers[458][]=7059103;
            $hcCustomers[458][]=7060355;
            $hcCustomers[458][]=7058776;
            $hcCustomers[458][]=7058107;
            $hcCustomers[458][]=409944;
            $hcCustomers[458][]=7084857;
            $hcCustomers[458][]=7060291;
            $hcCustomers[458][]=7058297;
            $hcCustomers[458][]=7059632;
            $hcCustomers[458][]=7058904;
            $hcCustomers[458][]=7059257;
            $hcCustomers[458][]=7058150;
            $hcCustomers[458][]=7058517;
            $hcCustomers[458][]=7059600;
            $hcCustomers[458][]=7058790;
            $hcCustomers[458][]=7058599;
            $hcCustomers[458][]=7058599;
            $hcCustomers[458][]=7059563;
            $hcCustomers[458][]=7058848;
            $hcCustomers[458][]=7059968;
            $hcCustomers[458][]=7059984;
            $hcCustomers[458][]=7058333;
            $hcCustomers[458][]=7060495;
            $hcCustomers[458][]=7060444;
            $hcCustomers[458][]=7058743;
            $hcCustomers[458][]=7060444;
            $hcCustomers[458][]=7059084;
            $hcCustomers[458][]=7060444;
            $hcCustomers[458][]=7059210;
            $hcCustomers[458][]=7058124;
            $hcCustomers[458][]=7060094;
            $hcCustomers[458][]=7059426;
            $hcCustomers[458][]=7058411;
            $hcCustomers[458][]=7060330;
            $hcCustomers[458][]=7059563;
            $hcCustomers[458][]=7060571;
            $hcCustomers[458][]=7058005;
            $hcCustomers[458][]=7060370;
            $hcCustomers[458][]=7059049;
            $hcCustomers[458][]=7059371;
            $hcCustomers[458][]=7051150;
            $hcCustomers[458][]=7059274;
            $hcCustomers[458][]=7058122;
            $hcCustomers[458][]=7060541;
            $hcCustomers[458][]=7060229;
            $hcCustomers[458][]=7058504;
            $hcCustomers[458][]=199152;
            $hcCustomers[458][]=474385;
            $hcCustomers[458][]=7059257;
            $hcCustomers[458][]=7058792;
            $hcCustomers[458][]=7060477;
            $hcCustomers[458][]=7059790;
            $hcCustomers[458][]=7060493;
            $hcCustomers[458][]=7059433;
            $hcCustomers[458][]=7059525;
            $hcCustomers[458][]=7059582;
            $hcCustomers[458][]=7060393;
            $hcCustomers[458][]=7058400;
            $hcCustomers[458][]=7059435;
            $hcCustomers[458][]=7060513;
            $hcCustomers[458][]=648655;
            $hcCustomers[458][]=7059241;
            $hcCustomers[458][]=7059005;
            $hcCustomers[458][]=7058696;
            $hcCustomers[458][]=7060820;
            $hcCustomers[458][]=7060582;
            $hcCustomers[458][]=7058510;
            $hcCustomers[458][]=7058572;
            $hcCustomers[458][]=7091507;
            
            foreach($hcCustomers as $hccK=>$hccVs)
            {
                foreach($hccVs as $hccV)
                {
                    if($hccV == $daeMemberNo)
                    {
                        $hcConverted[] = $cid;
                        //was this owner already added?
                        if(isset($customersCount[$cid]) && $customersCount[$cid] > 0)
                        {
                            //this is a duplicate record -- we need to reduce the original amount
                            $customersCount[$cid]--;
                        }
                    }
                }
            }
            
            $hcCustomercount = count($hcConverted);
            
            $customersCount[$cid] += $hcCustomercount;
            //             echo '<pre>'.print_r($customersCount[$cid], true).'</pre>';
            
            if(!empty($cpDup) && count($cpDup) >= $customersCount[$cid])
            {
                $return['error'] = "You have already used this coupon!";
            }
            //             if(!empty($cpDup))
                //                 $return['error'] = "You have already used this coupon!";
        }
    }
    
    if(!isset($return['error']))
    {
          
        

        
        $bogo = '';
        $bogomax = '';
        $bogomin = '';
        
        $sql = "SELECT * FROM wp_cart WHERE cartID='".$cartID."'";
        $cartRows = $wpdb->get_results($sql);
        foreach($cartRows as $cartRow)
        {
            
            $cart = json_decode($cartRow->data);
            
            
            //make sure this wasn't added
            if(isset($cart->coupon) && !empty($cart->coupon))
            {
                if(in_array($row->id,(array) $cart->coupon))
                {
                    $return['error'] = 'This coupon has already been applied!';
                    continue;
                }
            }
            
            if(isset($acForCart))
                $cart->acHash = $acForCart;
            $thisPropID = $cartRow->propertyID;
            $property_details = get_property_details($thisPropID, $cid);
            
            extract($property_details);
            
            $thiscart = $cartRow->id;
            $bogoCarts[$thiscart] = $cart;
            
            $addCoupon = $row->id;
            $discount = $row->Amount;
            
            $discountTypes = array(
                    'Pct Off',
                    'Dollar Off',
                    'Set Amt',
                    'BOGO',
                    'BOGOH',
                    'Auto Create Coupon'
            );
            $discountType = $specialMeta->promoType;
            foreach($discountTypes as $dt)
            {
                if(strpos($specialMeta->promoType, $dt))
                    $discountType = $dt;
            }
            
            
            if($discountType == 'Pct Off')
                $activePrice= number_format($currentPrice*(1-($discount/100)), 2);
            elseif($discountType == 'BOGO' || $discountType == 'BOGOH')
            {
                $bogo = $currentPrice;
                $bogos[$thiscart] = $prop->Price;
                if($bogo > $bogomax)
                {
                    $bogomax = $bogo;
                    $cartBogo['max'] = $cart;
                }
                if(empty($bogomin))
                {
                    $bogomin = $bogo;
                    $bogoMinCartID = $cartID;
                    $cartBogo['min'] = $cart;
                }
                elseif($bogo < $bogomin)
                {
                    $bogomin = $bogo;
                    $bogoMinCartID = $cartID;
                    $cartBogo['min'] = $cart;
                }
            }
            elseif($discountType == 'Dollar Off')
                $activePrice = $currentPrice-$discount;
            elseif($discount < $currentPrice)
                $activePrice = $discount;
            
                
            $skip = false;
            /*
             * filter out conditions
             */
                //blackouts
                if(isset($specialMeta->blackout) && !empty($specialMeta->blackout))
                {
                    foreach($specialMeta->blackout as $blackout)
                    {
                        if(strtotime($prop->checkIn) >= strtotime($blackout->start) && strtotime($prop->checkIn) <= strtotime($blackout->end))
                        {
                            $skip = true;
                        }
                    }
                }
                //resort blackout dates
                if(isset($specialMeta->resortBlackout) && !empty($specialMeta->resortBlackout))
                {
                    foreach($specialMeta->resortBlackout as $resortBlackout)
                    {
                        //if this resort is in the resort blackout array then continue looking for the date
                        if(in_array($prop->resortID, $resortBlackout->resorts))
                        {
                            if(strtotime($prop->checkIn) >= strtotime($resortBlackout->start) && strtotime($prop->checkIn) <= strtotime($resortBlackout->end))
                            {
                                $skip = true;
                            }
                        }
                    }
                }
                if(get_current_user_id() == 5)
                {
//                     echo '<pre>'.print_r("5589: ".$skip, true).'</pre>';
                }
                //resort specific travel dates
                if(isset($specialMeta->resortTravel) && !empty($specialMeta->resortTravel))
                {
                    foreach($specialMeta->resortTravel as $resortTravel)
                    {
                        //if this resort is in the resort blackout array then continue looking for the date
                        if(in_array($prop->resortID, $resortTravel->resorts))
                        {
                            if(strtotime($prop->checkIn) >= strtotime($resortTravel->start) && strtotime($prop->checkIn) <= strtotime($resortTravel->end))
                            {
                                //all good
                            }
                            else
                            {
                                $skip = true;
                            }
                        }
                    }
                }
                
                //week min cost
                if(isset($specialMeta->minWeekPrice) && !empty($specialMeta->minWeekPrice))
                {
                    if($prop->WeekType == 'ExchangeWeek')
                        $skip = true;
                    
                                if(get_current_user_id() == 5)
                                {
                                    $currentPrice = 351;
                                }
                    if($currentPrice < $specialMeta->minWeekPrice)
                        $skip = true;
                }
                
                //usage upsell
                if(isset($specialMeta->upsellOptions) && !empty($specialMeta->upsellOptions))
                {
                    $activePrice = $currentPrice;
                    $skip = true;
                    if(is_array($specialMeta->upsellOptions))
                    {
                        $sma = $specialMeta->upsellOptions;
                        $specialMeta->upsellOptions = $sma[0];
                    }
                    
                    switch($specialMeta->upsellOptions)
                    {
                        case 'CPO':
                            $datech = date('m/d/Y', strtotime($prop->checkIn.' -45 days'));
                            if(date('m/d/Y') <=  $datech)
                            {
                                if(isset($cart->CPOPrice) && !empty($cart->CPOPrice))
                                {
                                    if($discountType == 'Pct Off')
                                        $activePrice= number_format($cart->CPOPrice*($discount/100), 2);
                                        else
                                            $activePrice = $cart->CPOPrice-$discount;
                                            $skip = false;
                                }
                            }
                            break;
                
                        case 'Upgrade':
                            if(isset($cart->creditvalue) && !empty($cart->creditvalue))
                            {
                                if($discountType == 'Pct Off')
                                    $activePrice= number_format($cart->creditvalue*($discount/100), 2);
                                    else
                                        $activePrice = $cart->creditvalue-$discount;
                                        $skip = false;
                            }
                            break;
                
                        case 'Guest Fees': 
                            if(isset($cart->GuestFeeAmount) && $cart->GuestFeeAmount == '1')
                            {
                                $checkoutPropDetails = get_property_details_checkout($thisPropID, $cid);
                                if(isset($checkoutPropDetails['indGuestFeeAmount'][$thisPropID]) && !empty($checkoutPropDetails['indGuestFeeAmount'][$thisPropID]))
                                {
                                    if($discountType == 'Pct Off')
                                       $activePrice = $activePrice + number_format($checkoutPropDetails['indGuestFeeAmount'][$thisPropID]*($discount/100), 2);
                                    else
                                       $activePrice = $activePrice + ($checkoutPropDetails['indGuestFeeAmount'][$thisPropID] - $discount); 
                                    
                                    $skip = false;
                                }
                            }
                
                        case 'Extension Fees':
                
                            break;
                    }
                } 
                
                //specific customer
                if(isset($specialMeta->specificCustomer) && !empty($specialMeta->specificCustomer))
                {
                    $specificCustomer = json_decode($specialMeta->specificCustomer);
                    if(!in_array($cid, $specificCustomer))
                        $skip = true;
                }
                //usage resort
                if(isset($specialMeta->usage_region) && !empty($specialMeta->usage_region))
                {
                    $usage_regions = json_decode($specialMeta->usage_region);
                    foreach($usage_regions as $usage_region)
                    {
                        $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE id='".$usage_region."'";
                        $excludeLftRght = $wpdb->get_row($sql);
                        $excleft = $excludeLftRght->lft;
                        $excright = $excludeLftRght->rght;
                        $sql = "SELECT id FROM wp_gpxRegion WHERE lft>=".$excleft." AND rght<=".$excright;
                        $usageregions = $wpdb->get_results($sql);
                        if(isset($usageregions) && !empty($usageregions))
                        {
                            foreach($usageregions as $usageregion)
                            {
                                $uregionsAr[] = $usageregion->id;
                            }
                        }  
                        
                    }
                    if(!in_array($prop->gpxRegionID, $uregionsAr))
                    {
                        $skipRegion = true;
                        $skip = true;
                    }
                }
                
                //usage resort
                if(isset($specialMeta->usage_resort) && !empty($specialMeta->usage_resort))
                {
                    if(!in_array($property_details['prop']->resortID, $specialMeta->usage_resort))
                    {
                        //if the useage_region is set and we didn't skip it then we already know that this is OK -- We shouldn't check this
                        if(isset($skipRegion))
                        {
                            //everything is good here.
                        }
                        else 
                        {
                            $skip = true;
                        }   
                    }
                }
                
                $smt = [
                    'ExchangeWeek',
                    'BonusWeek'
                ];
                if(get_current_user_id() == 5)
                {
//                     echo '<pre>'.print_r($amt, true).'</pre>';
//                     echo '<pre>'.print_r($specialMeta->transactionType, true).'</pre>';
                }
                //transaction type
                if((is_array($specialMeta->transactionType) && array_intersect($specialMeta->transactionType, $smt)) || $specialMeta->transactionType == 'ExchangeWeek' || $specialMeta->transactionType == 'BonusWeek')
                {
                    $propWeekType = $prop->WeekType;
                    $smtt = $specialMeta->transactionType;
                    if($propWeekType == 'RentalWeek')
                    {
                        $propWeekType = 'BonusWeek';
                    }
                    if(!is_array($smtt) && ($propWeekType != $specialMeta->transactionType) || (is_array($smtt) && !in_array($propWeekType, $smtt)))
                    {
                        $skip = true;
                    }
                }
                  if(get_current_user_id() == 5)
                  {
//                       echo '<pre>'.print_r($skip, true).'</pre>';
//                       echo '<pre>'.print_r($prop->WeekType, true).'</pre>';
                  }    
                //exclusions
                
                //exclude DAE
//                 if((isset($specialMeta->exclude_dae) && !empty($specialMeta->exclude_dae)) || (isset($specialMeta->exclusions) && $specialMeta->exclusions == 'dae'))
//                 {
//                     //If DAE selected as an exclusion:
//                     //- Do not show inventory to use unless
//                     //--- Stock Display = GPX or ALL
//                     //AND
//                     //---OwnerBusCatCode=GPX
//                     //if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx') && strtolower($prop->OwnerBusCatCode) == 'gpx')
//                     if((strtolower($prop->StockDisplay) == 'all' || strtolower($prop->StockDisplay) == 'gpx' || strtolower($prop->StockDisplay) == 'usa gpx') && (strtolower($prop->OwnerBusCatCode) == 'gpx' || strtolower($prop->OwnerBusCatCode) == 'usa gpx'))
//                     {
//                         //all good we can show these properties
//                     }
//                     else
//                     {
//                         $skip = true;
//                     }
//                 }
                
                //exclude resorts
                if(isset($specialMeta->exclude_resort) && !empty($specialMeta->exclude_resort))
                {
                    if(in_array($prop->resortJoinID, $specialMeta->exclude_resort))
                    {
                            $skip = true;
                    }
                }
                
                if(get_current_user_id() == 5)
                {
//                     echo '<pre>'.print_r("6097: ".$skip, true).'</pre>';
                }
                //exclude regions
                if(isset($specialMeta->exclude_region) && !empty($specialMeta->exclude_region))
                {
                    $exclude_regions = json_decode($specialMeta->exclude_region);
                    foreach($exclude_regions as $exclude_region)
                    {
                        $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE id='".$exclude_region."'";
                        $excludeLftRght = $wpdb->get_row($sql);
                        $excleft = $excludeLftRght->lft;
                        $excright = $excludeLftRght->rght;
                        $sql = "SELECT * FROM wp_gpxRegion WHERE lft>=".$excleft." AND rght<=".$excright;
                        $excregions = $wpdb->get_results($sql);
                        if(isset($excregions) && !empty($excregions))
                        {
                            foreach($excregions as $excregion)
                            {
                                if($excregion->id == $prop->gpxRegionID)
                                {
                                    $skip = true;
                                }
                            }
                        }                                   
                    }
                }
                
                //lead time
                $today = date('Y-m-d');
                if(isset($specialMeta->leadTimeMin) && !empty($specialMeta->leadTimeMin))
                {
                    $ltdate = date('Y-m-d', strtotime($prop->checkIn." -".$specialMeta->leadTimeMin." days"));
                    if($today > $ltdate)
                        $skip = true;
                }
                if(isset($specialMeta->leadTimeMax) && !empty($specialMeta->leadTimeMax))
                {
                    $ltdate = date('Y-m-d', strtotime($prop->checkIn." -".$specialMeta->leadTimeMax." days"));
                    if($today < $ltdate)
                        $skip = true;
                }
                if(isset($specialMeta->bookStartDate) && !empty($specialMeta->bookStartDate))
                {
                    $bookStartDate = date('Y-m-d', strtotime($specialMeta->bookStartDate));
                    if($today < $bookStartDate)
                        $skip = true;
                }
                
                if(isset($specialMeta->bookEndDate) && !empty($specialMeta->bookEndDate))
                {
                    $bookEndDate = date('Y-m-d', strtotime($specialMeta->bookEndDate));
                    if($today > $bookEndDate)
                        $skip = true;
                }
                //stacking
                if($specialMeta->stacking == 'No')
                {
                    if(!empty($property_details['specialPrice']))
                    {
                        $return['error'] = 'A promotional price has been applied to your transaction.  This coupon is not allowed.';
                        continue;
                    }
                    else 
                    {
                        if(isset($cart->coupon))
                        {
                            foreach($cart->coupon as $activeCoupon)
                            {
                                $sql = "SELECT Properties, Amount FROM wp_specials WHERE id='".$activeCoupon."'";
                                $active = $wpdb->get_row($sql);
                                $activeProp = stripslashes_deep(json_decode($active->Properties));
                                if($activeProp->promoType == 'Pct Off')
                                    $thisPrice = number_format($currentPrice*(1-($active->Amount/100)),2);
                                elseif($activeProp->promoType == 'Dollar Off') 
                                    $thisPrice = $currentPrice-$active->Amount; 
                                elseif($Amount < $currentPrice)
                                    $thisPrice = $active->Amount;
                                
                                if((isset($thisPrice) && !empty($thisPrice)) && $thisPrice < $activePrice)
                                {
                                    $addCoupon = $active->id;
                                    $activePrice = $thisPrice; 
                                    unset($cart->coupon);
                                }
                            }
                        }
                    }
                }
                
                if(!$skip && !isset($return['error']))
                {
                    if(empty($bogomin))
                    {
                        if(isset($cart->coupon))
                        {
                            
                                $ccCart = (array) $cart->coupon;
                                $ccCart[$thisPropID] = $addCoupon;
//                             else
//                               array_push($cart->coupon, $addCoupon); 
                        }
                        else 
                        {
                            
                                $ccCart[$thisPropID] = $addCoupon;
//                             else
//                                 $cart->coupon = array($addCoupon);
                        }
                        
                            $cart->coupon = $ccCart;
//                         else
//                             $cart->coupon = array_unique($cart->coupon);
                        
                        
                        $update = json_encode($cart);
                        $wpdb->update('wp_cart', array('data'=>$update), array('cartID'=>$cartID));
                        $return['success'] = true;
                    }
                }
                else 
                    $return['error'] = "This coupon isn't available for this transaction.";
        }
        if(isset($bogos) && !empty($bogos))
        {
            $cnt = count($bogos)/2;
            asort($bogos, 1);
            $i = 0;
            if($discountType == 'BOGOH')
            {
                foreach($bogos as $bogoK=>$bogop)
                {
                    $bogoCarts[$bogoK]->coupon = array($bCouponID);
                    if($i < $cnt)
                    {
                        if (strpos($cnt, '.5') !== false)
                        {
                            $return['error'] = "This coupon isn't available for this transaction.";
                            continue;
                        }
                        $bogoCarts[$bogoK]->couponbogo = $bogop/2;
                
                        $update = json_encode($bogoCarts[$bogoK]);
                        $wpdb->update('wp_cart', array('data'=>$update), array('id'=>$bogoK));
                    }
                    else 
                    {
                        $bogoCarts[$bogoK]->couponbogo = $bogop;
                
                        $update = json_encode($bogoCarts[$bogoK]);
                        $wpdb->update('wp_cart', array('data'=>$update), array('id'=>$bogoK));
                    }
                    $i++;
                }
            }
            else 
            {
                foreach($bogos as $bogoK=>$bogop)
                {
                    $bogoCarts[$bogoK]->coupon = array($bCouponID);
                    if($i < $cnt)
                    {
                        if (strpos($cnt, '.5') !== false)
                        {
                            $return['error'] = "This coupon isn't available for this transaction.";
                            continue;
                        }
                        $bogoCarts[$bogoK]->couponbogo = '0.00';
                
                        $update = json_encode($bogoCarts[$bogoK]);
                        $wpdb->update('wp_cart', array('data'=>$update), array('id'=>$bogoK));
                    }
                    else 
                    {
                        $bogoCarts[$bogoK]->couponbogo = $bogop;
                
                        $update = json_encode($bogoCarts[$bogoK]);
                        $wpdb->update('wp_cart', array('data'=>$update), array('id'=>$bogoK));
                    }
                    $i++;
                }                
            }
            $return['success'] = true;
        }
    }
    

    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_enter_coupon","gpx_enter_coupon");
add_action("wp_ajax_nopriv_gpx_enter_coupon", "gpx_enter_coupon");

function gpx_remove_coupon()
{
    global $wpdb;
    
    extract($_POST);
        
    $sql = "SELECT * FROM wp_cart WHERE cartID='".$cartID."'";
    $cartRows = $wpdb->get_results($sql);
    foreach($cartRows as $cartRow)
    {
        
        $cart = json_decode($cartRow->data);
        unset($cart->coupon);

        $update = json_encode($cart);
        $wpdb->update('wp_cart', array('data'=>$update), array('cartID'=>$cartID));
        $return['success'] = true;
    }
    
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_remove_coupon","gpx_remove_coupon");
add_action("wp_ajax_nopriv_gpx_remove_coupon", "gpx_remove_coupon");

function gpx_remove_owner_credit_coupon()
{
    global $wpdb;
    
    extract($_POST);
        
    $sql = "SELECT * FROM wp_cart WHERE cartID='".$cartID."'";
    $cartRows = $wpdb->get_results($sql);
    foreach($cartRows as $cartRow)
    {
        
        $cart = json_decode($cartRow->data);
        unset($cart->occoupon);

        $update = json_encode($cart);
        $wpdb->update('wp_cart', array('data'=>$update), array('cartID'=>$cartID));
        $return['success'] = true;
    }
    
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_remove_owner_credit_coupon","gpx_remove_owner_credit_coupon");
add_action("wp_ajax_nopriv_gpx_remove_owner_credit_coupon", "gpx_remove_owner_credit_coupon");

function gpx_cpo_adjust()
{
    global $wpdb;
    
    extract($_POST);
    $propWhere = '';
    if(isset($propertyID)) 
        $propWhere = " AND propertyID='".$propertyID."'";
    $sql = "SELECT * FROM wp_cart WHERE cartID='".$cartID."'".$propWhere;
    $cartRows = $wpdb->get_results($sql);

    foreach($cartRows as $cartRow)
    {
        
        $cart = json_decode($cartRow->data);
        
        $cart->CPOPrice = '0';

        if(isset($add) && $add == 'add cpo')
            $cart->CPOPrice = get_option('gpx_fb_fee');
        
        $update = json_encode($cart);
        $wpdb->update('wp_cart', array('data'=>$update), array('id'=>$cartRow->id));
        $return['success'] = true;
    }
    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_cpo_adjust","gpx_cpo_adjust");
add_action("wp_ajax_nopriv_gpx_cpo_adjust", "gpx_cpo_adjust");

function gpx_get_custom_request()
{
    global $wpdb;
    
    $joinedTbl = map_dae_to_vest_properties();
    
    $return = array();
    
    if(isset($_REQUEST['cid']) && !empty($_REQUEST['cid']))
    {
        $user = get_userdata($_REQUEST['cid']);
        if(isset($user) && !empty($user))
        {
            require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
            $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
            
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $_REQUEST['cid'] ) );
            
            //             $credit = $gpx->DAEGetMemberCredits($usermeta->DAEMemberNo, $_REQUEST['cid']);
            
            //             if($credit[0] <= 0)
                //                 $return['error'] = 'You must have a deposit on file to complete a custom request.  Please deposit a week or contact us for assistance.';
            
            $return['fname'] = $usermeta->FirstName1;
            $return['lname'] = $usermeta->LastName1;
            $return['daememberno'] = $usermeta->DAEMemberNo;
            $return['phone'] = $usermeta->DayPhone;
            $return['mobile'] = $usermeta->Mobile1;
            $return['email'] = $usermeta->user_email;
            if(empty($return['email']))
                $return['email'] = $usermeta->Email;
        }
    }
    
    $getdate = '';
    
    if(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']))
    {
        if(substr($_REQUEST['pid'], 0, 1) == "R")
            $sql = "SELECT Country, Region, Town, ResortName
                    FROM wp_resorts
                    WHERE ResortID='".$_REQUEST['pid']."' AND active=1";
            else
            {
                $sql = "SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinResort']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
                WHERE a.request_id='".$_REQUEST['pid']."' AND b.active=1";
                $getdate = '1';
            }
            $row = $wpdb->get_row($sql);
            
            if(!empty($row))
            {
                $return['country'] = $row->Country;
                $return['region'] = $row->Region;
                $return['town'] = $row->Town;
                $return['town'] = $row->ResortName;
            }
            
    }
    
    if(isset($_REQUEST['rid']) && !empty($_REQUEST['rid']))
    {
        $sql = "SELECT * FROM wp_gpxCustomRequest WHERE id='".$_REQUEST['rid']."'";
        $row = $wpdb->get_row($sql);
        
        if(!empty($row))
        {
            $date = date('m/d/Y', strtotime($row->checkIn));
            $return['startdate'] = date('m/d/Y 00:00', strtotime($row->checkIn));
            if(!empty($row->checkIn2))
            {
                $date .= " - ".date('m/d/Y', strtotime($row->checkIn2));
                $return['enddate'] = date('m/d/Y 00:00', strtotime($row->checkIn2));
            }
            $return['date'] = $date;
            $return['id'] = $row->id;
            $return['country'] = $row->country;
            $return['state'] = $row->region;
            $return['city'] = $row->city;
            $return['resort'] = $row->resort;
            $return['miles'] = $row->miles;
            $return['daememberno'] = $row->emsID;
            $return['fname'] = $row->firstName;
            $return['lname'] = $row->lastName;
            $return['email'] = $row->email;
            $return['phone'] = $row->phone;
            $return['mobile'] = $row->mobile;
            $return['adults'] = $row->adults;
            $return['child'] = $row->children;
            $return['roomtype'] = $row->roomType;
            $return['roompref'] = $row->preference;
            $return['nearby'] = $row->nearby;
            $return['or_larger'] = $row->larger;
        }
        
    }
    
    
    
    if(!empty($getdate) && isset($row->checkIn))
        $return['dateFrom'] = date('m/d/Y', strtotime($row->checkIn));
        
        echo wp_send_json($return);
        exit();
}
add_action("wp_ajax_gpx_get_custom_request","gpx_get_custom_request");
add_action("wp_ajax_nopriv_gpx_get_custom_request", "gpx_get_custom_request");

function gpx_apply_discount()
{
    global $wpdb;
    extract($_POST);
    
    $sql = "SELECT * FROM wp_cart WHERE cartID='".$cartID."'";
    $cartRow = $wpdb->get_row($sql);
    $cart = json_decode($cartRow->data);
    
    $cid = $cart->user;
    
    $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
    
    $credit = $usermeta->daeCredit*.01;
    
    $cart->credit = $credit;
    
    $update = json_encode($cart);
    $wpdb->update('wp_cart', array('data'=>$update), array('cartID'=>$cartID));
    $return['success'] = true;

    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_apply_discount","gpx_apply_discount");
add_action("wp_ajax_nopriv_gpx_apply_discount", "gpx_apply_discount");

function gpx_post_custom_request()
{
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
    
    global $wpdb;
    
    $dateRanges = json_decode( stripslashes($_POST['00N40000003DG5P']));
    $_POST['00N40000003DG5P'] = date('m/d/Y', strtotime($dateRanges->start));
    $_POST['00N40000003DG5Q'] = date('m/d/Y', strtotime($dateRanges->end));
    
    //      $dates = array('00N40000003DG5P', '00N40000003DG5Q', '00N40000003DG5R');
    
    //     foreach($dates as $date)
        //     {
        //         if(!empty($_POST[$date]))
            //             $sortDates[] = strtotime($_POST[$date]);
            //     }
    
    //     $dateRanges = explode(" - ", $_POST['00N40000003DG5P']);
    //     foreach($dateRanges as $dr)
        //     {
        //         $sortDates[] = strtotime($dr);
        //     }
    //     sort($sortDates);
    //     $i = 0;
    //     echo '<pre>'.print_r( $_POST['00N40000003DG5P'], true).'</pre>';
    
    //     foreach($dates as $date)
        //     {
        //         if(isset($sortDates[$i]))
            //             $_POST[$date] = date('m/d/Y', $sortDates[$i]);
            //         else
                //             $_POST[$date] = '';
                //         $i++;
                //     }
    
    //     $formUrl =  'https://webto.salesforce.com/servlet/servlet.WebToCase?encoding=UTF-8';
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $formUrl);
    //     curl_setopt($ch, CURLOPT_POST, 1);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_FAILONERROR, true);
    //     $response = curl_exec($ch);
    //     curl_close($ch);
    
    //add to database
    $dbFields = array(
    //         '00N40000003DG5T'=>'country',
    //         '00N40000003DG5Y'=>'state',
        '00N40000003S58X'=>'region',
        '00N40000003DG5S'=>'city',
        '00N40000003DG59'=>'resort',
        '00N40000003DG5P'=>'checkIn',
        '00N40000003DG5Q'=>'checkIn2',
        '00N40000003DG5R'=>'checkIn3',
        '00N40000003DG4w'=>'emsID',
        '00N40000003DGST'=>'firstName',
        '00N40000003DGSO'=>'lastName',
        '00N40000003DG50'=>'email',
        '00N40000002yyD8'=>'phone',
        '00N40000002yyDD'=>'mobile',
        '00N40000003DG5X'=>'ada',
        '00N40000003DG56'=>'adults',
        '00N40000003DG57'=>'children',
        '00N40000003DG54'=>'roomType',
        '00N40000003DG51'=>'comments',
        'miles'=>'miles',
        'preference'=>'preference',
        'larger'=>'larger',
        'nearby'=>'nearby',
    );
    
    foreach($_POST as $pk=>$pv)
    {
        if(empty($pk) || !array_key_exists($pk, $dbFields))
            continue;
            $db[$dbFields[$pk]] = $pv;
    }
    $userType = 'Owner';
    $loggedinuser =  get_current_user_id();
    $cid = $loggedinuser;
    
    if(isset($_COOKIE['switchuser']))
        $cid = $_COOKIE['switchuser'];
        
        if($loggedinuser != $cid)
            $userType = 'Agent';
            
            $db['who'] = $userType;
            
            $user = get_userdata($cid);
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
            
            if(isset($usermeta->GP_Preferred) && $usermeta->GP_Preferred == 'Yes')
            {
                $db['BOD'] = 1;
            }

            $sql = "SELECT COUNT(id) as holds FROM wp_gpxPreHold WHERE user='".$cid."' AND released='0'";
            $holdcount = $wpdb->get_var($sql);
           
            $sql = "SELECT SUM(credit_amount) AS total_credit_amount, SUM(credit_used) AS total_credit_used FROM wp_credit WHERE owner_id IN (SELECT gpx_user_id FROM wp_mapuser2oid WHERE gpx_user_id='".$cid."') AND (credit_expiration_date IS NULL OR credit_expiration_date >'".date('Y-m-d')."')";
            $credit = $wpdb->get_row($sql);
            
            $credits = $credit->total_credit_amount - $credit->total_credit_used - $crs;
            
            $sql = "SELECT * FROM wp_gpxCustomRequest 
                    WHERE active=1 AND (emsID='".$usermeta->DAEMemberNo."' OR userID='".$cid."')
                    AND who='Owner'";
            $checkCustomRequests = $wpdb->get_results($sql);
            
            if(!empty($checkCustomRequests))
            {
                $holdcount += count($checkCustomRequests);
            }
            
            //return true if credits+1 is greater than holds
            if(isset($credits) && ($credits+1 > $holdcount))
            {
                //we're good we can continue holding this
            }
            else
            {
                $holderror = array('success'=>true, 'holderror'=>get_option('gpx_hold_error_message'));
                /*
                 * todo:  turn this on
                 */
                echo wp_send_json($holderror);
                exit();
            }
            
            $matches = custom_request_match($db);
            
            if(!empty($matches))
            {
                foreach($matches as $matchKey=>$match)
                {
                    if($matchKey == 'restricted')
                    {
                        if($match == 'All Restricted')
                        {
                            $db['active'] = '0';
                            $db['forCron'] = '0';
                        }
                        continue;
                    }
                    $matchedID[] = $match->PID;
                }
                
                if(isset($matchedID) && !empty($matchedID))
                {
                    $db['matched'] = implode(",", $matchedID);
                    $db['active'] = '0';
                    if(!isset($_POST['crID']) || (isset($_POST['crID']) && empty($_POST['crID'])))
                    {
                        $db['matchedOnSubmission'] = '1';
                    }
                }
                elseif(!isset($db['forCron']))
                {
                    $db['forCron'] = 1;
                }
            }
            elseif(!isset($db['forCron']))
            {
                $db['forCron'] = 1;
            }
            
            
            
            $dbCheck = $db;
            unset($dbCheck['who']);
            
            //adjust the query based on what they selected resort doesn't need region or city
            if(isset($dbCheck['resort']))
            {
                unset($dbCheck['city']);
                unset($dbCheck['region']);
            }
            elseif(isset($dbCheck['city']))
            {
                unset($dbCheck['region']);
            }
            
            foreach($dbCheck as $key=>$value)
            {
                $dbCheckWhere[] = $key."='".$value."'";
            }
            
            $db['userID'] = $cid;
            
            if(isset($_POST['crID']) && !empty($_POST['crID']))
            {
                $lastID = $_POST['crID'];
                unset($_POST['crID']);
                $wpdb->update('wp_gpxCustomRequest', $db, array('id'=>$lastID));
            }
            else
            {
                $sql = "SELECT id FROM wp_gpxCustomRequest WHERE ".implode(' AND ', $dbCheckWhere);
                $exist = $wpdb->get_row($sql);
                
                if($wpdb->num_rows > 0)
                    $lastID = $exist->id;
                    
                    if(!empty($db) && !isset($lastID))
                    {
                        $wpdb->insert('wp_gpxCustomRequest', $db);
                        $lastID = $wpdb->insert_id;
                    }
            }
            if(get_current_user_id() == 5)
            {
                echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
            }
            if(isset($matches[0]) && !empty($matches[0]))
            {
                $matches['matched'] = $lastID;
            }
            
            $matches['success'] = true;
            echo wp_send_json($matches);
            exit();
}
add_action("wp_ajax_gpx_post_custom_request","gpx_post_custom_request");
add_action("wp_ajax_nopriv_gpx_post_custom_request", "gpx_post_custom_request");

function gpx_fast_populate()
{

    $cid =  get_current_user_id();

    if(isset($_COOKIE['switchuser']))
        $cid = $_COOKIE['switchuser'];

        $user = get_userdata($cid);

        if(isset($user) && !empty($user))
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );

            $return = array(
                'billing_address'=>$usermeta->Address1,
                'billing_city'=>$usermeta->Address3,
                'billing_state'=>$usermeta->Address4,
                'billing_zip'=>$usermeta->PostCode,
                'biling_country'=>$usermeta->Address5,
                'billing_email'=>$usermeta->email,
                'billing_cardholder'=>$usermeta->FirstName1." ".$usermeta->LastName1,
            );

            echo wp_send_json($return);
            exit();
}
add_action("wp_ajax_gpx_fast_populate","gpx_fast_populate");
add_action("wp_ajax_gpx_fast_populate", "gpx_fast_populate");

function gpx_book_link_savesearch()
{
    
    if(is_user_logged_in())
    {
        $save = save_search_book($_POST);
    }

    $return['success'] = true;

    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_book_link_savesearch","gpx_book_link_savesearch");
add_action("wp_ajax_nopriv_gpx_book_link_savesearch", "gpx_book_link_savesearch");

function gpx_resort_link_savesearch()
{

    $save = save_search_resort('', $_POST);

    $return['success'] = true;

    echo wp_send_json($return);
    exit();
}
add_action("wp_ajax_gpx_resort_link_savesearch","gpx_resort_link_savesearch");
add_action("wp_ajax_nopriv_gpx_resort_link_savesearch", "gpx_resort_link_savesearch");

function gpx_display_featured_resorts_sc($atts='')
{
    global $wpdb;
    
    $atts = shortcode_atts(array('location'=>'home', 'start'=>'0', 'get'=>'6'), $atts);
    extract($atts);
 
    $return = $get+1;
    $sql = "SELECT * FROM wp_resorts WHERE featured='1' AND active=1 LIMIT ".$start.", ".$return;
    $props = $wpdb->get_results($sql);
    
    echo '<ul class="w-list w-list-items">';
    $i = 0;
    foreach($props as $prop)
    {
        if($i < $get) // only return $get
            include('templates/sc-featrued-destination-'.$location.'.php');
            $i++;
    }
    
    echo '</ul>';
    $getplus = $get+1;
    if(count($props) == $getplus)
    {
        $start = $start+$get;
        echo '<a href="#" class="sbt-seemore" id="seemore-home" data-location="'.$location.'" data-start="'.$start.'" data-get="'.$get.'"> <span>See more</span> <i class="icon-arrow-down"></i></a>';
        echo '<div class="sbt-seemore-box"></div>';
    }
    
}
add_shortcode('gpx_display_featured_resorts', 'gpx_display_featured_resorts_sc');

function gpx_display_featured_func($location='', $start='', $get='')
{
    global $wpdb;

    if(empty($location))
        extract($_POST);
    
    $return = $get+1;
    $sql = "SELECT * FROM wp_resorts WHERE featured='1' AND active=1 LIMIT ".$start.", ".$return;
    $props = $wpdb->get_results($sql);

    $html = '<ul class="w-list w-list-items">';
    $i = 0;
    foreach($props as $prop)
    {
        if($i < $get) // only return $get
        {
            $html .= '<li class="w-item">';
            $html .= '<div class="cnt">';
            $html .= '<a href="/resort-profile/?resort=<'.$prop->id.'">';
            $html .= '<figure><img src="<'.$prop->ImagePath1.'" alt="<'.$prop->ResortName.'"></figure>';
            if($lcoation == 'resorts')
                $html .= '<div calss="text">';
            $html .= '<h3><'.$prop->Town.', <'.$prop->Region.'</h3>';
            if($lcoation == 'resorts')
                $html .= '<h4>'.$prop->Country.'</h4>';
            $html .= '<p><'.$prop->ResortName.'</p>';
            if($lcoation == 'resorts')
                $html .= '</div>';
            if($lcoation == 'resorts')
                $html .= '<a href="/resort-profile/?resort='.$prop->id.'" class="dgt-btn">Explore</a>';
            else
                $html .= '<div data-link="/resort-profile/?resort=<'.$prop->id.'" class="dgt-btn sbt-btn">Explore Offer </div>';
            $html .= '</a>';
            $html .= '</div>';
            $html .= '</li>';
        }
        $i++;
    }
    
    $html .= '</ul>';
    $getplus = $get+1;
    if(count($props) == $getplus)
    {
        $start = $start+$get;
        $html .= '<a href="#" class="sbt-seemore" id="seemore-home" data-location="'.$location.'" data-start="'.$start.'" data-get="'.$get.'"> <span>See more</span> <i class="icon-arrow-down"></i></a>';
        $html .= '<div class="sbt-seemore-box"></div>';
    }

}
add_action("wp_ajax_gpx_display_featured_func","gpx_display_featured_func");
add_action("wp_ajax_nopriv_gpx_display_featured_func", "gpx_display_featured_func");
function gpx_change_password_with_hash_func()
{
    $cid = $_POST['cid'];
    $pw1 = $_POST['chPassword'];
    $pw2 = $_POST['chPasswordConfirm'];
    
    $data['msg'] = 'System unavailable. Please try again later.';
    
    if($pw1 != $pw2)
        $data['msg'] = 'Passwords do not match!';
    
    $user = get_user_by('ID', $cid);    
     
    if(isset($_POST['hash']))
    {
        $pass = $_POST['hash'];
    
        if ( $user && wp_check_password( $pass, $user->data->user_pass, $user->ID) ) 
        {
            $up = wp_set_password($pw1, $user->ID);
            $data['msg'] = 'Password Updated!';
        }
        else 
            $data['msg'] = 'Wrong password!';
    }
    else
    {
        $up = wp_set_password($pw1, $user->ID);
        $data['msg'] = 'Password Updated!';
    }
        

        echo wp_send_json($data);
        exit();    
}
add_action("wp_ajax_gpx_change_password_with_hash","gpx_change_password_with_hash_func");
add_action("wp_ajax_nopriv_gpx_change_password_with_hash", "gpx_change_password_with_hash_func");


/**
 * Force Visual Composer to initialize as "built into the theme". This will hide certain tabs under the Settings->Visual Composer page
 */
add_action( 'vc_before_init', 'your_prefix_vcSetAsTheme' );
function your_prefix_vcSetAsTheme() {
    vc_set_as_theme();
}
function add_promo_var($vars)
{
    $vars[] = 'promo';
    return $vars;
}
add_filter('query_vars', 'add_promo_var', 0, 1);
add_rewrite_rule('^promotion/([^/]*)/?','index.php?page_id=229&promo=$matches[1]','top');

function user_last_login( $user_login, $user ) {
    update_user_meta( $user->ID, 'last_login', time() );
    update_user_meta($user->ID, 'searchSessionID', $user->ID."-".time());
}
add_action( 'wp_login', 'user_last_login', 10, 2 );

add_action('after_setup_theme', 'remove_admin_bar');

function remove_admin_bar() {
    if (!current_user_can('gpx_admin')) {
        show_admin_bar(false);
    }
}

function my_login_redirect( $redirect_to, $request, $user ) {
    //is there a user to check?
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        //check for admins
        if ( in_array( 'administrator', $user->roles ) || in_array( 'gpx_call_center', $user->roles ) ) {
            // redirect them to the default place
//             return $redirect_to;
            return $redirect_to;
        } else {
            return home_url();
        }
    } else {
//         return $redirect_to;
        return $redirect_to;
    }
}

add_filter( 'login_redirect', 'my_login_redirect', 10, 3 );
function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/logo.png);
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );
function my_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'my_login_logo_url' );

function my_login_logo_url_title() {
    return 'GPX';
}
add_filter( 'login_headertitle', 'my_login_logo_url_title' );
function logout_redirect_home(){
    wp_safe_redirect(home_url());
    exit;
}
add_action('wp_logout', 'logout_redirect_home');

function my_redirect_home( $lostpassword_redirect ) {
    return home_url();
}
add_filter( 'lostpassword_redirect', 'my_redirect_home' );
add_filter( 'wpsl_meta_box_fields', 'custom_meta_box_fields' );

function custom_meta_box_fields( $meta_fields ) {

    $meta_fields[__( 'Additional Information', 'wpsl' )] = array(
        'phone' => array(
            'label' => __( 'Tel', 'wpsl' )
        ),
        'fax' => array(
            'label' => __( 'Fax', 'wpsl' )
        ),
        'email' => array(
            'label' => __( 'Email', 'wpsl' )
        ),
        'url' => array(
            'label' => __( 'Url', 'wpsl' )
        ),
        'resortid' => array(
            'label' => __( 'Resort ID', 'wpsl' )
        ),
        'thumbnail' => array(
            'label' => __( 'Thnumbnail URI', 'wpsl' )
        )
    );

    return $meta_fields;
}

function custom_templates( $templates ) {

    /**
     * The 'id' is for internal use and must be unique ( since 2.0 ).
     * The 'name' is used in the template dropdown on the settings page.
     * The 'path' points to the location of the custom template,
     * in this case the folder of your active theme.
     */
    $templates[] = array (
        'id'   => 'custom',
        'name' => 'Custom template',
        'path' => get_stylesheet_directory() . '/' . 'wpsl-templates/custom.php',
    );

    return $templates;
}
add_filter( 'wpsl_templates', 'custom_templates' );


function custom_frontend_meta_fields( $store_fields ) {
    
    $store_fields['wpsl_thumbnail'] = array(
        'name' => 'thumbnail',
        'type' => 'url'
    );
    
    return $store_fields;
}
add_filter( 'wpsl_frontend_meta_fields', 'custom_frontend_meta_fields' );

function custom_info_window_template() {
     
    $info_window_template = '<div data-store-id="<%= id %>" class="wpsl-info-window">';
    $info_window_template .= '<div>';
    $info_window_template .=  wpsl_store_header_template();
    $info_window_template .= '</div>';
    $info_window_template .= '<div>';
    $info_window_template .= '<div style="overflow:hidden;">';
    
    $info_window_template .= '<% if( thumbnail ) { %>';
    $info_window_template .= '<div style="float:left;width:42%; margin-right: 10px; height: 60px;">';
    $info_window_template .= '<img style="width:100%;height:auto;margin-right: 10px;" src="<%= thumbnail %>" width="250" height="250" alt="">';
    $info_window_template .= '</div>';
    $info_window_template .= '<% } %>';

    $info_window_template .= '<div>';
    $info_window_template .= '<span><%= address %></span>';
    $info_window_template .= '<span>' . wpsl_address_format_placeholders() . '</span>';
    $info_window_template .= '<small><a href="<%= url %>" class="btn btn-maps" target="_blank">Book Resort</a></small>';
    $info_window_template .= '</div>';
    $info_window_template .= '</div>';
    $info_window_template .= '</div>';
    $info_window_template .= '</div>';

    return $info_window_template;
}
add_filter( 'wpsl_info_window_template', 'custom_info_window_template' );

function custom_store_header_template() {

    $header_template = '<% if ( wpslSettings.storeUrl == 1 && url ) { %>';
    $header_template .= '<strong><a href="<%= url %>"><%= store %></a></strong>';
    $header_template .= '<% } else { %>';
    $header_template .= '<strong><%= store %></strong>';
    $header_template .= '<% } %>';

    return $header_template;
}
add_filter( 'wpsl_store_header_template', 'custom_store_header_template' );

function custom_listing_template() {

    global $wpsl, $wpsl_settings;

    $listing_template = '<li data-store-id="<%= id %>">';
    $listing_template .= '<div class="wpsl-store-location">';
    $listing_template .= '<div class="wpsl-listings-wrapper">';
    $listing_template .= '<div class="wpsl-main-info" style="float: left; margin-right: 20px;">';
    $listing_template .= '<p>';
    $listing_template .= wpsl_store_header_template( 'listing' ); // Check which header format we use
    $listing_template .= '<span class="wpsl-street"><%= address %></span>';
    $listing_template .= '<% if ( address2 ) { %>';
    $listing_template .= '<span class="wpsl-street"><%= address2 %></span>';
    $listing_template .= '<% } %>';
    $listing_template .= '<span>' . wpsl_address_format_placeholders() . '</span>'; // Use the correct address format
    
    if ( !$wpsl_settings['hide_country'] ) {
        $listing_template .= '<span class="wpsl-country"><%= country %></span>';
    }
    $listing_template .= '<a href="<%= url %>" class="btn btn-maps" target="_blank">Book Resort</a>';
    $listing_template .= '</p>';
    $listing_template .= '</div>';
    $listing_template .= '<% if( thumbnail ) { %>';
    $listing_template .= '<div class="wpsl-thumb" style="float: left; width: 100%;  max-width: 200px;">';
    $listing_template .= '<img style="width:100%;height:auto;margin-right: 10px;" src="<%= thumbnail %>" width="250" height="250" alt="">';
    $listing_template .= '</div>';
    $listing_template .= '<% } %>';
    $listing_template .= '</div>';
    $listing_template .= '</div>';
    $listing_template .= '</li>';

    return $listing_template;
}
add_filter( 'wpsl_listing_template', 'custom_listing_template' );

// Function that will return our WordPress menu
function gpx_list_menu($atts, $content = null) {
    extract(shortcode_atts(array(
        'menu'            => '',
        'container'       => 'div',
        'container_class' => '',
        'container_id'    => '',
        'menu_class'      => 'menu',
        'menu_id'         => '',
        'echo'            => true,
        'fallback_cb'     => 'wp_page_menu',
        'before'          => '',
        'after'           => '',
        'link_before'     => '',
        'link_after'      => '',
        'depth'           => 0,
        'walker'          => '',
        'theme_location'  => ''),
        $atts));

    return wp_nav_menu( array(
        'menu'            => $menu,
        'container'       => $container,
        'container_class' => $container_class,
        'container_id'    => $container_id,
        'menu_class'      => $menu_class,
        'menu_id'         => $menu_id,
        'echo'            => false,
        'fallback_cb'     => $fallback_cb,
        'before'          => $before,
        'after'           => $after,
        'link_before'     => $link_before,
        'link_after'      => $link_after,
        'depth'           => $depth,
        'walker'          => $walker,
        'theme_location'  => $theme_location));
}
//Create the shortcode
add_shortcode("gpx_listmenu", "gpx_list_menu");

function vc_gpx_custom_menu()
{
    vc_map( array(
        "name" => __("GPX Custom Menu", "gpx-website"),
        "base" => "gpx_listmenu",
        "params" => array(
            // add params same as with any other content element
            array(
                "type" => "textfield",
                "heading" => __("Menu", "gpx-website"),
                "param_name" => "menu",
                "description" => __("The menu slug.", "gp-website")
            ),
            array(
                "type" => "textfield",
                "heading" => __("Container Class", "gpx-website"),
                "param_name" => "container_class",
                "description" => __("Class of the container.", "gp-website")
            ),
            array(
                "type" => "textfield",
                "heading" => __("Container ID", "gpx-website"),
                "param_name" => "container_id",
                "description" => __("ID of the container.", "gp-website")
            ),
            array(
                "type" => "textfield",
                "heading" => __("Menu Class", "gpx-website"),
                "param_name" => "menu_class",
                "description" => __("Class of the menu.", "gp-website")
            ),
            array(
                "type" => "textfield",
                "heading" => __("Menu", "gpx-website"),
                "param_name" => "menu_id",
                "description" => __("ID of the menu.", "gp-website")
            ),
        )
    ) );
}
add_action('vc_before_init', 'vc_gpx_custom_menu');
function vc_gpx_bp_terms()
{
    vc_map( array(
        "name" => __("Booking Path Terms", "gpx-website"),
        "base" => "gpx_booking_path",
        "params" => array(
            // add params same as with any other content element
            array(
                "type" => "textarea",
                "heading" => __("Terms", "gpx-website"),
                "param_name" => "terms",
                "description" => __("Additional Terms & Conditions for all weeks.", "gpx-website")
            ),
        )
    ) );
}
add_action('vc_before_init', 'vc_gpx_bp_terms');

function vc_gpx_bpp_terms()
{
    vc_map( array(
        "name" => __("Booking Path Payment Terms", "gpx-website"),
        "base" => "gpx_booking_path_payment",
        "params" => array(
            // add params same as with any other content element
            array(
                "type" => "textarea",
                "heading" => __("Terms", "gpx-website"),
                "param_name" => "terms",
                "description" => __("Additional Terms & Conditions for all weeks.", "gpx-website")
            ),
        )
    ) );
}
add_action('vc_before_init', 'vc_gpx_bpp_terms');

function vc_gpx_bpc_terms()
{
    vc_map( array(
        "name" => __("Booking Path Payment Terms", "gpx-website"),
        "base" => "gpx_booking_path_confirmation",
        "params" => array(
            // add params same as with any other content element
            array(
                "type" => "textarea",
                "heading" => __("Terms", "gpx-website"),
                "param_name" => "terms",
                "description" => __("Additional Terms & Conditions for all weeks.", "gpx-website")
            ),
        )
    ) );
}
add_action('vc_before_init', 'vc_gpx_bpc_terms');


function desitnations_custom_post_type() {

    // Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Destinations', 'Post Type General Name', 'gpx-dst' ),
        'singular_name'       => _x( 'Destination', 'Post Type Singular Name', 'gpx-dst' ),
        'menu_name'           => __( 'Destinations', 'gpx-dst' ),
        'parent_item_colon'   => __( 'Parent Destination', 'gpx-dst' ),
        'all_items'           => __( 'All Destinations', 'gpx-dst' ),
        'view_item'           => __( 'View Destination', 'gpx-dst' ),
        'add_new_item'        => __( 'Add New Destination', 'gpx-dst' ),
        'add_new'             => __( 'Add New', 'gpx-dst' ),
        'edit_item'           => __( 'Edit Destination', 'gpx-dst' ),
        'update_item'         => __( 'Update Destination', 'gpx-dst' ),
        'search_items'        => __( 'Search Destination', 'gpx-dst' ),
        'not_found'           => __( 'Not Found', 'gpx-dst' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'gpx-dst' ),
    );

    // Set other options for Custom Post Type

    $args = array(
        'label'               => __( 'Destinations', 'gpx-dst' ),
        'description'         => __( 'Destinations', 'gpx-dst' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
        /* A hierarchical CPT is like Pages and can have
         * Parent and child items. A non-hierarchical CPT
    * is like Posts.
    */
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 12,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'page',
        'map_meta_cap' => true,
        'capability_type' => 'destinations',
    );

    // Registering your Custom Post Type
    register_post_type( 'destinations', $args );

}

add_action( 'init', 'desitnations_custom_post_type', 0 );

function destinations_meta_boxes( $meta_boxes ) {

    $meta_boxes[] = array(
        'title'      => __( 'Destination', 'gpx-dst' ),
        'post_types' => 'destinations',
        'fields'     => array(
            array(
                'id'   => 'gpx-destination-link',
                'name' => __( 'Destination (Region Name )', 'gpx-dst' ),
                'type' => 'text',
            ),
            array(
                'id'   => 'gpx-destination-blog-link',
                'name' => __( 'Other Link (link to a page other than a destination page -- leave blank to link to destination)', 'gpx-dst' ),
                'type' => 'post',
                'post_type' => [
                    'post',
                    'page'
                ],
                //                 'field_type' => 'select',
            ),
            array(
                'id'   => 'gpx-destination-link-text',
                'name' => __( 'Button Text', 'gpx-dst' ),
                'type' => 'text',
            ),
        ),
    );
    return $meta_boxes;
}
add_filter( 'rwmb_meta_boxes', 'destinations_meta_boxes' );

function gpx_shared_media_custom_post_type() {
    
    // Set UI labels for Custom Post Type
    $labels = array(
    'name'                => _x( 'Shared Media', 'Post Type General Name', 'gpx-dst' ),
    'singular_name'       => _x( 'Shared Media', 'Post Type Singular Name', 'gpx-dst' ),
    'menu_name'           => __( 'Shared Media', 'gpx-dst' ),
    'parent_item_colon'   => __( 'Parent Media Galery', 'gpx-dst' ),
    'all_items'           => __( 'All Media Galleries', 'gpx-dst' ),
    'view_item'           => __( 'View Media Gallery', 'gpx-dst' ),
    'add_new_item'        => __( 'Add New Media Gallery', 'gpx-dst' ),
    'add_new'             => __( 'Add New Gallery', 'gpx-dst' ),
    'edit_item'           => __( 'Edit Media Gallery', 'gpx-dst' ),
    'update_item'         => __( 'Update Media Gallery', 'gpx-dst' ),
    'search_items'        => __( 'Search Media Gallery', 'gpx-dst' ),
    'not_found'           => __( 'Not Found', 'gpx-dst' ),
    'not_found_in_trash'  => __( 'Not found in Trash', 'gpx-dst' ),
    );
    
    // Set other options for Custom Post Type
    
    $args = array(
    'label'               => __( 'Media', 'gpx-dst' ),
    'description'         => __( 'Media', 'gpx-dst' ),
    'labels'              => $labels,
    // Features this CPT supports in Post Editor
    'supports'            => array( 'title', 'page-attributes' ),
    /* A hierarchical CPT is like Pages and can have
     * Parent and child items. A non-hierarchical CPT
     * is like Posts.
     */
    'hierarchical'        => false,
    'public'              => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'show_in_nav_menus'   => true,
    'show_in_admin_bar'   => true,
    'menu_position'       => 12,
    'can_export'          => true,
    'has_archive'         => true,
    'exclude_from_search' => false,
    'publicly_queryable'  => true,
    'capability_type'     => 'page',
    'map_meta_cap' => true,
    'capability_type' => 'owner-shared-media',
    'menu_icon' => 'dashicons-images-alt',
    );
    
    // Registering your Custom Post Type
    register_post_type( 'owner-shared-media', $args );
    
}

add_action( 'init', 'gpx_shared_media_custom_post_type', 0 );

function gpx_shared_media_taxonomies_cat() {
    $labels = array(
        'name'              => _x( 'Resorts', 'taxonomy general name' ),
        'singular_name'     => _x( 'Resort', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Resorts' ),
        'all_items'         => __( 'All Resorts' ),
        'parent_item'       => __( 'Parent Resort' ),
        'parent_item_colon' => __( 'Parent Resort:' ),
        'edit_item'         => __( 'Edit Resort' ),
        'update_item'       => __( 'Update Resort' ),
        'add_new_item'      => __( 'Add New Resort' ),
        'new_item_name'     => __( 'New Resort' ),
        'menu_name'         => __( 'Resorts' ),
    );
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'show_admin_column'   => true,
        //         'capabilities'=>array(
            //             'manage_terms' => 'manage_courts',
            //             'edit_terms' => 'edit_courts',
            //             'delete_terms' => 'delete_courts',
            //             'assign_terms' => 'assign_courts'),
    );
    register_taxonomy( 'gpx_shared_media_resort', 'owner-shared-media', $args );
}
add_action( 'init', 'gpx_shared_media_taxonomies_cat', 0 );

function gpx_shared_media_meta_boxes( $meta_boxes ) {
    
    $meta_boxes[] = array(
        'title'      => __( 'Gallery', 'gpx-dst' ),
        'post_types' => 'owner-shared-media',
        'fields'     => array(
            array(
                'id'               => 'gpx_shared_images',
                'name'             => 'Image Gallery',
                'type'             => 'image_advanced',
                // Maximum image uploads.
    //                 'max_file_uploads' => 2,
                
                // Do not show how many images uploaded/remaining.
                'max_status'       => 'false',
                
                // Image size that displays in the edit page.
                'image_size'       => 'thumbnail',
            ),
            
        ),
    );
    return $meta_boxes;
}
add_filter( 'rwmb_meta_boxes', 'gpx_shared_media_meta_boxes' );

function gpx_shared_media_remove_wp_seo_meta_box() {
    remove_meta_box('wpseo_meta', 'owner-shared-media', 'normal');
}
add_action('add_meta_boxes', 'gpx_shared_media_remove_wp_seo_meta_box', 100);

// function set_shared_media_resorts()
// {
//     global $wpdb;
//     $sql = "SELECT ResortName FROM wp_resorts";
//     echo '<pre>'.print_r($sql, true).'</pre>';
//     $resorts = $wpdb->get_results($sql);
    
//     foreach($resorts as $resort)
//     {
//         $resortName = $resort->ResortName;
//         echo '<pre>'.print_r($resortName, true).'</pre>';
//         if(!term_exists( $resortName, 'gpx_shared_media_resort' ))
//         {
//             wp_insert_term( $resortName, 'gpx_shared_media_resort');
//         }
//     }
//     echo wp_send_json($resorts);
//     exit();
// }
// add_action("wp_ajax_set_shared_media_resorts","set_shared_media_resorts");
// add_action("wp_ajax_nopriv_set_shared_media_resorts","set_shared_media_resorts");

function set_default_display_name( $user_id ) {
    $user = get_userdata( $user_id );
    $name = sprintf( '%s %s', $user->first_name, $user->last_name );
    $args = array(
        'ID' => $user_id,
        'display_name' => $name,
        'nickname' => $name
    );
    wp_update_user( $args );
}
add_action( 'user_register', 'set_default_display_name' );

function ice_shortcode($atts)
{
    $at = shortcode_atts(
        array(
            'class' => '',
            'loggedintext' => 'Just Cruising Along...',
            'nologgedintext' => 'Login',
        ), $atts );
    
    $html = '';
    
    $cid = get_current_user_id();
    
    if(isset($_COOKIE['switchuser']))
    {
        $cid = $_COOKIE['switchuser'];
    }
    
    if(isset($cid) && !empty($cid))
    {
        $user = get_userdata($cid);
        if(isset($user) && !empty($user))
        {
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
            if((isset($usermeta->ICEStore) && $usermeta->ICEStore != 'No') || !isset($usermeta->ICEStore))
            {
                $html = '<a href="#" class="ice-link '.esc_attr($at['class']).'" data-cid="'.$cid.'">'.esc_attr($at['loggedintext']).'</a>';
                
            }
        }
    }
    if(empty($html))
    {
        $html = '<a href="#" class="ice-link '.esc_attr($at['class']).'" data-cid="">'.esc_attr($at['nologgedintext']).'</a>';
    }
    return $html;
}
add_filter( 'gform_tabindex', '__return_false' );add_shortcode('ice_shortcode', 'ice_shortcode');

function universal_search_widget_shortcode()
{
    ob_start();
    include(locate_template( 'template-parts/universal-search-widget.php' ));
    return ob_get_clean();
}
add_shortcode('gpx_universal_search_widget', 'universal_search_widget_shortcode');

function perks_choose_credit()
{
    ob_start();
    
    echo '<div class="exchange-credit"><div id="exchangeList"><div style="text-align: center;"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div></div></div>';
    
    return ob_get_clean();
}
add_shortcode('perks_choose_credit', 'perks_choose_credit');

function gpx_lpid_cookie()
{
    if(isset($_POST['lpid']) && isset($_POST['cid']))
    {
        update_user_meta($_POST['cid'], 'lppromoid'.$_POST['lpid'], $_POST['lpid']);
    }
    $data = ['success'=>true];
    echo wp_send_json($data);
    exit();
}
add_action("wp_ajax_gpx_lpid_cookie","gpx_lpid_cookie");
add_action("wp_ajax_nopriv_gpx_lpid_cookie", "gpx_lpid_cookie");

function gpx_show_hold_button()
{
    if(empty($_GET['cid']))
    {
        $data['hide'] = true;
    }
    else
    {
        if( gpx_hold_check($_GET['cid']) )
        {
            $data['show'] = true;
        }
        else
        {
            $data['hide'] = true;
        }
    }
    
    echo wp_send_json($data);
    exit();
}
add_action("wp_ajax_gpx_show_hold_button","gpx_show_hold_button");
add_action("wp_ajax_nopriv_gpx_show_hold_button", "gpx_show_hold_button");