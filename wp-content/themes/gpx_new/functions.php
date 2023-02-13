<?php
/**
 * @package WordPress DGT
 * @since   DGT Alliance 2.0
 */

use Doctrine\DBAL\Connection;
use GPX\Model\CustomRequestMatch;
use GPX\Repository\OwnerRepository;

date_default_timezone_set( 'America/Los_Angeles' );

define( 'GPX_THEME_VERSION', '4.33' );

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
        add_theme_support( 'html5', [
            'search-form',
            'gallery',
            'caption',
        ] );

        add_theme_support( 'post-formats', [
            'aside',
            'image',
            'video',
            'quote',
            'link',
            'gallery',
            'status',
            'audio',
            'chat',
        ] );
    }
endif;
add_action( 'after_setup_theme', 'gpx_theme_setup' );

// Add hook for admin <head></head>
add_action( 'admin_head', 'gpx_add_recaptcha_site_key' );
// Add hook for front-end <head></head>
add_action( 'wp_head', 'gpx_add_recaptcha_site_key' );

function gpx_add_recaptcha_site_key() {
    echo '<script>window.RECAPTCHA_SITE_KEY = ' . json_encode( GPX_RECAPTCHA_V3_SITE_KEY ) . ';</script>';
}

add_action( 'wp_head', function () {
    echo file_get_contents(__DIR__ . '/template-parts/header-scripts.html');
} );

add_action( 'wp_footer', function(){
    echo file_get_contents(__DIR__ . '/template-parts/footer-scripts.html');
} );


if ( ! function_exists( 'load_gpx_theme_styles' ) ) {
    /**
     * Load Required CSS Styles
     */
    function load_gpx_theme_styles() {
        // enqueue Main styles
        $css_directory_uri = get_template_directory_uri() . '/css/';
        wp_register_style( 'jquery-ui', '//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui' );
        wp_register_style( 'sumoselect', $css_directory_uri . 'sumoselect.css', [], GPX_THEME_VERSION, 'all' );
        wp_enqueue_style( 'sumoselect' );
        wp_register_style( 'dialog',
                           'https://cdnjs.cloudflare.com/ajax/libs/dialog-polyfill/0.5.6/dialog-polyfill.min.css',
                           [],
                           '0.5.6',
                           'all' );
        wp_register_style( 'header-footer', $css_directory_uri . 'header-footer.css', [], GPX_THEME_VERSION, 'all' );
        wp_register_style( 'main',
                           $css_directory_uri . 'main.css',
                           [ 'dialog', 'header-footer' ],
                           GPX_THEME_VERSION,
                           'all' );
        wp_enqueue_style( 'main' );
        wp_enqueue_style( 'fontawesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css' );
        if ( is_homepage() ) :
            wp_register_style( 'home', $css_directory_uri . 'home.css', [], GPX_THEME_VERSION, 'all' );
            wp_enqueue_style( 'home' );
            wp_register_style( 'home', $css_directory_uri . 'home.css', [], GPX_THEME_VERSION, 'all' );
            wp_enqueue_style( 'home' );
        else:
            wp_register_style( 'inner', $css_directory_uri . 'inner.css', [], GPX_THEME_VERSION, 'all' );
            wp_enqueue_style( 'inner' );
        endif;

        if ( is_page( [ 'view-profile' ] ) ):
            wp_register_style( 'data-table',
                               $css_directory_uri . 'jquery.dataTables.min.css',
                               [],
                               GPX_THEME_VERSION,
                               'all' );
            wp_enqueue_style( 'data-table' );
            wp_register_style( 'data-table-responsive',
                               $css_directory_uri . 'dataTables.responsive.css',
                               [],
                               GPX_THEME_VERSION,
                               'all' );
            wp_enqueue_style( 'data-table-responsive' );
        endif;

        if ( is_singular( [ 'offer' ] ) ):
            wp_register_style( 'pagex', $css_directory_uri . 'pagex.css', [], GPX_THEME_VERSION, 'all' );
        endif;

        wp_enqueue_style( 'daterange-picker',
                          $css_directory_uri . 'daterange-picker.css',
                          [],
                          GPX_THEME_VERSION,
                          'all' );
        wp_enqueue_style( 'slick-css',
                          'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css',
                          [],
                          GPX_THEME_VERSION,
                          'all' );
        wp_enqueue_style( 'custom', $css_directory_uri . 'custom.css', [], GPX_THEME_VERSION, 'all' );
        wp_enqueue_style( 'ada', $css_directory_uri . 'ada.css', [], '1.1', 'all' );
        wp_enqueue_style( 'ice', $css_directory_uri . 'ice.css', [], GPX_THEME_VERSION, 'all' );
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
        wp_register_script( 'jquery_ui',
                            'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js',
                            [ 'jquery' ] );
        wp_register_script( 'royalslider',
                            $js_directory_uri . 'jquery.royalslider.custom.min.js',
                            [ 'jquery' ],
                            '9.5.7',
                            true );
        wp_register_script( 'sumoselect',
                            $js_directory_uri . 'jquery.sumoselect.min.js',
                            [ 'jquery' ],
                            '3.0.21',
                            true );
        wp_register_script( 'material-form',
                            $js_directory_uri . 'jquery.material.form.min.js',
                            [ 'jquery' ],
                            '1.0',
                            true );
        wp_register_script( 'polyfill',
                            'https://polyfill.io/v3/polyfill.min.js?features=Element.prototype.classList%2CObject.assign%2CElement.prototype.dataset%2CNodeList.prototype.forEach%2CElement.prototype.closest%2CString.prototype.endsWith',
                            [],
                            time(),
                            false );
        wp_register_script( 'dialog',
                            'https://cdnjs.cloudflare.com/ajax/libs/dialog-polyfill/0.5.6/dialog-polyfill.min.js',
                            [],
                            '0.5.6',
                            true );
        wp_register_script( 'modal',
                            $js_directory_uri . 'modal.js',
                            [ 'dialog', 'polyfill' ],
                            GPX_THEME_VERSION,
                            true );
        wp_register_script( 'alert', $js_directory_uri . 'alert.js', [ 'modal' ], GPX_THEME_VERSION, true );
        wp_register_script( 'main',
                            $js_directory_uri . 'main.js',
                            [ 'jquery', 'modal', 'alert' ],
                            GPX_THEME_VERSION,
                            true );
        wp_register_script( 'ada', $js_directory_uri . 'ada.js', [ 'jquery' ], GPX_THEME_VERSION, true );
        wp_register_script( 'shift4', $js_directory_uri . 'shift4.js', [ 'jquery' ], GPX_THEME_VERSION, true );
        wp_register_script( 'ice', $js_directory_uri . 'ice.js', [ 'jquery' ], GPX_THEME_VERSION, true );

        wp_enqueue_script( 'jquery' );
        if ( is_page( 97 ) ) {
            wp_enqueue_script( 'jquery_ui' );
        }
        // 		else
        wp_register_script( 'gpx_cookies',
                            $js_directory_uri . 'gpx_cookies.js',
                            [ 'jquery' ],
                            GPX_THEME_VERSION,
                            true );
        wp_enqueue_script( 'jquery_ui-core' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'royalslider' );
        wp_enqueue_script( 'sumoselect' );
        wp_enqueue_script( 'material-form' );
        wp_enqueue_script( 'javascript_cookie',
                           '//cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.3/js.cookie.min.js',
                           [ 'material-form' ] );
        wp_enqueue_script( 'jquery-tinysort',
                           '//cdnjs.cloudflare.com/ajax/libs/tinysort/2.3.6/tinysort.min.js',
                           [ 'material-form' ] );
        wp_enqueue_script( 'daterange-pickerjs',
                           $js_directory_uri . 'jquery.daterange-picker.js',
                           [ 'jquery_ui' ],
                           '1.0',
                           true );
        wp_enqueue_script( 'slick-js',
                           'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js',
                           [ 'jquery_ui' ],
                           '1.0',
                           true );

        wp_enqueue_script( 'main' );
        wp_enqueue_script( 'gpx_cookies' );
        wp_enqueue_script( 'ada' );
        wp_enqueue_script( 'shift4' );
        wp_enqueue_script( 'ice' );


        $params = [
            'url_theme' => get_template_directory_uri(),
            'url_ajax' => admin_url( "admin-ajax.php" ),
        ];

        if ( is_homepage() ) :
            wp_register_script( 'scroll-magic', $js_directory_uri . 'ScrollMagic.min.js', [ 'jquery' ], '1.0', true );
            wp_enqueue_script( 'scroll-magic' );
            $params['current'] = 'home';
        else:
            $params['current'] = $post->post_name;
        endif;

        wp_localize_script( 'main', 'gpx_base', $params );
        wp_enqueue_script( 'main' );

        if ( is_page( [ 'view-profile' ] ) ):
            wp_register_script( 'data-tables',
                                $js_directory_uri . 'jquery.dataTables.min.js',
                                [ 'jquery' ],
                                '1.10.12',
                                true );
            wp_register_script( 'data-tables-responsive',
                                $js_directory_uri . 'dataTables.responsive.min.js',
                                [ 'jquery' ],
                                '1.0.0',
                                true );
            wp_enqueue_script( 'data-tables' );
            wp_enqueue_script( 'data-tables-responsive' );
        endif;

        wp_enqueue_script( 'recaptchav3',
                           'https://www.google.com/recaptcha/api.js?render=' . GPX_RECAPTCHA_V3_SITE_KEY,
                           [ 'jquery' ],
                           GPX_THEME_VERSION,
                           true );
    }

    add_action( 'wp_enqueue_scripts', 'load_gpx_theme_scripts' );
}

function onetrust_js_handle( $tag, $handle, $source ) {
    if ( 'gpx_cookies' === $handle ) {
        $tag = '<script type="text/javascript" src="' . $source . '"></script>';
    }

    return $tag;
}

add_filter( 'script_loader_tag', 'onetrust_js_handle', 10, 3 );


function gpr_onetrust_form( $params = [] ) {
    $inputVars = [
        'data' => '',
    ];
    $atts = shortcode_atts( $inputVars, $params );
    extract( $atts );

    ob_start();
    ?>
    <!-- OneTrust Consent Receipt Start -->
    <script
        src="https://privacyportal-cdn.onetrust.com/consent-receipt-scripts/scripts/otconsent-1.0.min.js"
        type="text/javascript"
        charset="UTF-8"
        id="consent-receipt-script">
        triggerId = "trigger";
        identifierId = "inputEmail";
        confirmationId = "confirmation";
        settingsUrl = "https://privacyportal-cdn.onetrust.com/consentmanager-settings/408bd2ea-da6b-40bb-8f66-e2fe87cd91f9/<?=$data?>-active.json";
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

add_shortcode( 'gpr_onetrust_form', 'gpr_onetrust_form' );

function is_homepage() {
    return ( is_front_page() || is_home() ) ? true : false;
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
add_action( "wp_ajax_gpx_load_more", "gpx_load_more_fn" );
add_action( "wp_ajax_nopriv_gpx_load_more", "gpx_load_more_fn" );

function gpx_load_more_fn() {
    $type_data = $_POST['type'];
    $output = '';
    switch ( $type_data ) {
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

function gpx_load_results_page_fn() {
    header( 'content-type: application/json; charset=utf-8' );

    $country = '';
    $region = '';

    global $wpdb;

    $joinedTbl = map_dae_to_vest_properties();

    extract( $_POST );
    $monthstart = date( 'Y-m-01', strtotime( $select_monthyear ) );
    $monthend = date( 'Y-m-t', strtotime( $select_monthyear ) );

    $html = '';

    $sql = $wpdb->prepare( "SELECT lft, rght FROM wp_gpxRegion WHERE RegionID=%d", $select_location );
    $row = $wpdb->get_row( $sql );
    $lft = $row->lft + 1;
    $sql = $wpdb->prepare( "SELECT id, lft, rght FROM wp_gpxRegion WHERE lft BETWEEN %d AND %d ORDER BY lft ASC",
                           [ $lft, $row->rght ] );
    $gpxRegions = $wpdb->get_results( $sql );

    foreach ( $gpxRegions as $gpxRegion ) {
        $regionSet = false;
        $sql = $wpdb->prepare( "SELECT
                        " . implode( ', ', $joinedTbl['joinRoom'] ) . ",
                        " . implode( ', ', $joinedTbl['joinResort'] ) . ",
                        " . implode( ', ', $joinedTbl['joinUnit'] ) . ",
                        " . $joinedTbl['roomTable']['alias'] . ".record_id as PID, " . $joinedTbl['resortTable']['alias'] . ".id as RID
                            FROM " . $joinedTbl['roomTable']['table'] . " " . $joinedTbl['roomTable']['alias'] . "
                    INNER JOIN " . $joinedTbl['resortTable']['table'] . " " . $joinedTbl['resortTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".resort=" . $joinedTbl['resortTable']['alias'] . ".id
                    INNER JOIN " . $joinedTbl['unitTable']['table'] . " " . $joinedTbl['unitTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".unit_type=" . $joinedTbl['unitTable']['alias'] . ".record_id
                WHERE b.gpxRegionID=%d
                AND check_in_date BETWEEN %s AND %s
                ",
                               [ $gpxRegion->id, $monthstart, $monthend ] );
        $rows = $wpdb->get_results( $sql );

        if ( ! empty( $rows ) ) {
            $cntResults = count( $rows );
            $i = 1;
            foreach ( $rows as $row ) {
                $priceint = preg_replace( "/[^0-9\.]/", "", $prop->WeekPrice );
                if ( $priceint != $row->Price ) {
                    $row->Price = $priceint;
                }
                $discount = '';
                $specialPrice = '';
                //are there specials?
                $sql = $wpdb->prepare( "SELECT a.Properties, a.Amount, a.SpecUsage
			FROM wp_specials a
            LEFT JOIN wp_promo_meta b ON b.specialsID=a.id
            LEFT JOIN wp_resorts c ON c.id=b.foreignID
            LEFT JOIN wp_gpxRegion d ON d.id=b.foreignID
            WHERE ((c.ResortID=%s AND b.refTable='wp_resorts')
            OR d.id=%s
            OR SpecUsage='customer')
            AND DATE(NOW()) BETWEEN StartDate AND EndDate
            AND c.active=1",
                                       [ $row->ResortID, $row->gpxRegionID ] );
                $specs = $wpdb->get_results( $sql );
                if ( $specs ) {
                    foreach ( $specs as $spec ) {
                        $specialMeta = stripslashes_deep( json_decode( $spec->Properties ) );
                        switch ( $specialMeta->transactionType ) {
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
                        if ( $spec->Amount > $discount && $transactionType == $row->WeekType ) {
                            $discount = $spec->Amount;
                            $discountType = $specialMeta->promoType;
                            if ( $discountType == 'Pct Off' ) {
                                $specialPrice = number_format( $row->Price * ( 1 - ( $discount / 100 ) ), 2 );
                            } elseif ( $discountType == 'Dollar Off' ) {
                                $specialPrice = $row->Price - $discount;
                            } elseif ( $discount < $row->Price ) {
                                $specialPrice = $discount;
                            }
                            if ( $specialPrice < 0 ) {
                                $specialPrice = '0.00';
                            }
                        }
                    }
                }

                if ( ! $regionSet ) {
                    $html .= '<li class="w-item-view">
                                <div class="view">
                                	<div class="view-cnt">
                                		<img src="' . $row->ImagePath1 . '" alt="' . $row->ResortName . '">
                                	</div>
                                	<div class="view-cnt">
                                		<div class="descrip">
                                			<hgroup>
                                				<h2>' . $row->ResortName . '</h2>
                                				<span>' . $row->Country . ' / ' . $row->Town . ', ' . $row->Region . '/span>
                                			</hgroup>
                                			<a href="" class="dgt-btn">View Resort</a>
                                		</div>
                                		<div class="w-status">
                                			<div class="close">
                                				<i class="icon-close"></i>
                                			</div>
                                			<div class="result">
                                				<span class="count-result" >' . $cntResults . ' Results for</span>
                                				<span class="date-result" >' . date( 'F',
                                                                                     strtotime( $monthstart ) ) . ' ' . date( 'Y',
                                                                                                                              strtotime( $monthstart ) ) . '</span>
                                			</div>
                                		</div>
                                	</div>
                                </div>
                                <ul id="gpx-listing-result" class="w-list-result">';
                }
                $html .= '<li class="item-result';
                if ( ! empty( $specialPrice ) ) {
                    $html .= ' active';
                }
                $html .= '">
                            	<div class="w-cnt-result">
                            		<div class="result-head">
                            			';
                $pricesplit = explode( " ", $row->WeekPrice );
                $nopriceint = str_replace( $priceint, "", $prop->WeekPrice );
                if ( empty( $specialPrice ) ) {
                    $html .= '<p><strong>' . $pricesplit[1] . '</strong></p>';
                } else {
                    $html .= '<p class="mach"><strong>' . $pricesplit[1] . '</strong></p>';
                    $html .= '<p class="now">Now <strong>' . $nopriceint . $specialPrice . '</strong></p>';
                }
                $html .= '              <ul class="status">
                            				<li>
                            					<div class="status-' . $row->WeekType . '"></div>
                            				</li>
                            			</ul>
                            		</div>
                            		<div class="cnt">
                            			<p><strong>' . $row->WeekType . '</strong></p>
                            			<p>Check-In ' . $row->checkIn . '</p>
                            			<p>' . $row->noNights . ' Nights</p>
                            			<p>Size ' . $row->Size . '</p>
                            		</div>
                            		<div class="list-button">
                            			<a href="" class="dgt-btn hold-btn" data-propertiesID="' . $row->id . '">Hold</a>
                            			<a href="" class="dgt-btn active book-btn" data-propertiesID="' . $row->id . '">Book</a>
                            		</div>
                            	</div>
                            </li>';
                if ( $i == $cntResults ) {
                    $html .= '</ul></li>';
                }
                $i ++;
                $regionSet = true;
            }
        }
    }

    $output = [ 'html' => $html ];

    wp_send_json( $output );
}

add_action( "wp_ajax_gpx_load_results_page_fn", "gpx_load_results_page_fn" );
add_action( "wp_ajax_nopriv_gpx_load_results_page_fn", "gpx_load_results_page_fn" );

function update_username() {
    global $wpdb;
    $data = [];

    if ( isset( $_POST['modal_username'] ) ) {
        $pw1 = trim( $_POST['user_pass'] );
        $pw2 = trim( $_POST['user_pass_repeat'] );
        $username_raw = $_POST['modal_username'];
        $username_clean = sanitize_user( $username_raw, true );
        $wh_cleaned = sanitize_text_field( $_POST['wh'] );

        if ( isset( $_POST['wh'] ) ) {
            $userID = reset(
                get_users(
                    [
                        'meta_key' => 'gpx_upl_hash',
                        'meta_value' => $wh_cleaned,
                        'number' => 1,
                        'count_total' => false,
                        'fields' => 'ids',
                    ]
                )
            );

            if ( empty( $userID ) ) {
                $data['msg'] = 'Invalid Request.  Please contact us to create your account.';
            }
        } else {
            $userID = get_current_user_id();
        }

        // don't allow emails
        if ( is_email( $username_raw ) ) {
            $data['msg'] = 'Please choose a unique username that is not an email address.';
        }

        // make sure passwords match
        if ( $pw1 != $pw2 ) {
            $data['msg'] = 'Passwords do not match!';
        } elseif ( username_exists( $username_raw ) ) {
            //is this their account?

            $data['msg'] = 'That username is already in use.  Please choose a different username.';
        }

        // usernames only valid char
        if ( preg_match( "/[^A-Za-z0-9]/", $username_raw ) ) {
            $data['msg'] = 'Username can only contain upper and lower case characters or numbers.';
        }

        //  validate min 6 chars
        if ( strlen( $username_clean ) < 6 ) {
            $data['msg'] = 'Username must be at least 6 characters.';
        }

        // valid password char
        $pw1_clean = sanitize_text_field( $pw1 );

        if ( $pw1_clean != $pw1 ) {
            $data['msg'] = 'Password contains invalid characters. Please try again.';
        }
        //  validate min 6 chars
        if ( strlen( $pw1_clean ) < 8 ) {
            $data['msg'] = 'Password must be at least 8 characters.';
        }

        if ( empty( $data ) ) {
            $up = wp_set_password( $pw1, $userID );

            $wpdb->update( 'wp_users', [ 'user_login' => $username_clean ], [ 'ID' => $userID ] );
            // removed the user meta for the token after the updates is complete
            // security issue to leave this in the database
            // ticket #1925
            delete_user_meta( $userID, 'gpx_upl_hash' );
            update_user_meta( $userID, 'gpx_upl', '1' );

            $wpdb->update( 'wp_GPR_Owner_ID__c', [ 'welcome_email_sent' => 1 ], [ 'user_id' => $userID ] );
            $data['success'] = true;
            $data['msg'] = 'Updated';
        }
    }

    wp_send_json( $data );
    wp_die();
}

add_action( 'wp_ajax_update_username', 'update_username' );
add_action( 'wp_ajax_nopriv_update_username', 'update_username' );

function gpx_pw_reset_fn() {
    header( "access-control-allow-origin: *" );
    $credentials = [];
    if ( isset( $_POST['user_email'] ) ) {
        $userlogin = $_POST['user_email'];
    }
    if ( isset( $_POST['user_login'] ) ) {
        $userlogin = $_POST['user_login'];
    }
    if ( isset( $_POST['user_login_pwreset'] ) ) {
        $userlogin = $_POST['user_login_pwreset'];
    }
    $credentials['user_login'] = isset( $userlogin ) ? trim( $userlogin ) : '';
    $user_signon = wp_signon( $credentials, true );
    $pwreset = retrieve_password();
    status_header( 200 );
    if ( is_wp_error( $pwreset ) ) {
        $user_signon_response = [
            'loggedin' => false,
            'message' => 'Wrong username or password.',
        ];
    } else {
        $user_signon_response = [
            'loggedin' => true,
            'message' => 'Please check your email for the link to reset your password.',
        ];
    }
    wp_send_json( $user_signon_response );
}

add_action( "wp_ajax_gpx_pw_reset", "gpx_pw_reset_fn" );
add_action( "wp_ajax_nopriv_gpx_pw_reset", "gpx_pw_reset_fn" );

function gpx_autocomplete_location_sub_fn() {
    global $wpdb;

    $term = ( ! empty( $_GET['term'] ) ) ? sanitize_text_field( $_GET['term'] ) : '';
    $region = ( ! empty( $_GET['region'] ) ) ? sanitize_text_field( $_GET['region'] ) : '';

    if ( ! empty( $region ) ) {
        $sql = $wpdb->prepare( "SELECT lft, rght FROM wp_gpxRegion WHERE name = %s", $region );
        $rows = $wpdb->get_results( $sql );
        $locations = [];
        foreach ( $rows as $row ) {
            $sql = $wpdb->prepare( "SELECT DISTINCT name, subName from wp_gpxRegion WHERE lft > %d AND rght < %d and ddHidden = 0",
                                   [ $row->lft, $row->rght ] );
            $cities = $wpdb->get_results( $sql );
            foreach ( $cities as $city ) {
                if ( ! empty( trim( $city->subName ) ) ) {
                    $locations[] = $city->subName;
                } else {
                    $locations[] = $city->name;
                }
            }
        }
    } else {
        $locations = [ 'Mexico', 'Caribbean' ];
        $sql = sprintf( "SELECT DISTINCT name, subName FROM wp_gpxRegion WHERE ddHidden = 0 AND %s",
                        empty( $term ) ? 'featured = 1' : "name != 'All'" );
        $regions = $wpdb->get_results( $sql );
        foreach ( $regions as $region ) {
            $location = $region->name;
            if ( isset( $region->subName ) && ! empty( trim( $region->subName ) ) ) {
                $location .= $region->subName;
            }
            $locations[] = $location;
        }

        if ( ! empty( $term ) ) {
            $sql = "SELECT country as name FROM wp_gpxCategory";
            $countries = $wpdb->get_results( $sql );
            foreach ( $countries as $country ) {
                if ( $country->name == 'USA' ) {
                    continue;
                }
                $locations[] = $country->name;
            }
        }
    }
    sort( $locations );

    $location_search = [];
    if ( ! empty( $term ) ) {
        foreach ( $locations as $item ) {
            $pos = strpos( strtolower( $item ), strtolower( $term ) );
            if ( $pos !== false ) {
                $location_search[] = $item;
            }
        }
        $locations = $location_search;
    }
    wp_send_json( $locations );
}

add_action( "wp_ajax_gpx_autocomplete_location_sub", "gpx_autocomplete_location_sub_fn" );
add_action( "wp_ajax_nopriv_gpx_autocomplete_location_sub", "gpx_autocomplete_location_sub_fn" );

function gpx_autocomplete_location_resort_fn() {
    global $wpdb;

    $resort = [];
    $locations = [ 'Mexico', 'Caribbean' ];
    $term = '';
    $term = ( ! empty( $_GET['term'] ) ) ? sanitize_text_field( $_GET['term'] ) : '';
    $region = '';
    $region = ( ! empty( $_GET['region'] ) ) ? sanitize_text_field( $_GET['region'] ) : '';

    if ( ! empty( $region ) ) {
        $sql = $wpdb->prepare( "SELECT lft, rght FROM wp_gpxRegion WHERE name = %s", $region );
        $rows = $wpdb->get_results( $sql );
        foreach ( $rows as $row ) {
            $sql = $wpdb->prepare( "SELECT gpxRegionID, ResortName from wp_gpxRegion a
                    INNER JOIN wp_resorts b ON a.id=b.gpxRegionID
                    WHERE lft  BETWEEN %d AND %d and ddHidden = '0'",
                                   [ $row->lft, $row->rght ] );
            $cities = $wpdb->get_results( $sql );

            foreach ( $cities as $city ) {
                $resorts[ $city->gpxRegionID ] = $city->ResortName;
            }
        }
    } else {
        $sql = "SELECT ResortName FROM wp_resorts where active = 1";
        $results = $wpdb->get_results( $sql );

        foreach ( $results as $result ) {
            $resorts[] = $result->ResortName;
        }
    }
    sort( $resorts );

    $resorts_search = [];
    if ( ! empty( $term ) ) {
        foreach ( $resorts as $item ) {
            $pos = strpos( strtolower( $item ), strtolower( $term ) );
            if ( $pos !== false ) {
                $resorts_search[] = $item;
            }
        }
        $resorts = $resorts_search;
    }
    wp_send_json( $resorts );
}

add_action( "wp_ajax_gpx_autocomplete_location_resort", "gpx_autocomplete_location_resort_fn" );
add_action( "wp_ajax_nopriv_gpx_autocomplete_location_resort", "gpx_autocomplete_location_resort_fn" );

function gpx_autocomplete_sr_location() {
    global $wpdb;
    $term = ( ! empty( $_GET['term'] ) ) ? sanitize_text_field( $_GET['term'] ) : '';
    $sql = sprintf( "SELECT DISTINCT name, subName, displayName FROM wp_gpxRegion WHERE ddHidden = 0 AND %s",
                    empty( $term ) ? 'featured = 1' : "name != 'All'" );
    $regions = $wpdb->get_results( $sql );
    foreach ( $regions as $region ) {
        if ( isset( $region->displayName ) && ! empty( trim( $region->displayName ) ) ) {
            $regionLocations[] = $region->displayName;
        } elseif ( isset( $region->subName ) && ! empty( trim( $region->subName ) ) ) {
            $regionLocations[] = $region->subName;
        } else {
            $regionLocations[] = $region->name;
        }
    }

    if ( ! empty( $term ) ) {
        //get the regions...
        $sql = "SELECT country as name FROM wp_gpxCategory";
        $countries = $wpdb->get_results( $sql );
        foreach ( $countries as $country ) {
            if ( $country->name == 'USA' ) {
                continue;
            }
            $regionLocations[] = $country->name;
        }

        //get the resorts...
        $sql = "SELECT ResortName FROM wp_resorts";
        $results = $wpdb->get_results( $sql );

        foreach ( $results as $result ) {
            $resortLocations[] = $result->ResortName;
        }
    }


    sort( $regionLocations );
    sort( $resortLocations );
    foreach ( $regionLocations as $loc ) {
        $locations[] = [
            'category' => 'REGION',
            'label' => $loc,
            'value' => $loc,
        ];
    }
    foreach ( $resortLocations as $loc ) {
        $locations[] = [
            'category' => 'RESORT',
            'label' => $loc,
            'value' => $loc,
        ];
    }

    $search = [];
    if ( ! empty( $term ) ) {
        foreach ( $regionLocations as $item ) {
            $pos = strpos( strtolower( $item ), strtolower( $term ) );
            if ( $pos !== false ) {
                $search[] = [
                    'category' => 'REGION',
                    'label' => $item,
                    'value' => $item,
                ];
            }
        }

        $locations = $search;
    }
    wp_send_json( $locations );
}

add_action( "wp_ajax_gpx_autocomplete_sr_location", "gpx_autocomplete_sr_location" );
add_action( "wp_ajax_nopriv_gpx_autocomplete_sr_location", "gpx_autocomplete_sr_location" );

function gpx_autocomplete_location_fn() {
    global $wpdb;
    $term = gpx_request()->query->get( 'term', '' );
    $sql = sprintf( "SELECT DISTINCT name, subName, displayName FROM wp_gpxRegion WHERE ddHidden = 0 AND %s",
                    empty( $term ) ? 'featured = 1' : "name != 'All'" );

    $regions = $wpdb->get_results( $sql );
    foreach ( $regions as $region ) {
        if ( isset( $region->displayName ) && ! empty( trim( $region->displayName ) ) ) {
            $regionLocations[] = $region->displayName;
        } elseif ( isset( $region->subName ) && ! empty( trim( $region->subName ) ) ) {
            $regionLocations[] = $region->subName;
        } else {
            $regionLocations[] = $region->name;
        }
    }

    if ( ! empty( $term ) ) {
        //get the regions...
        $sql = "SELECT country as name FROM wp_gpxCategory";
        $countries = $wpdb->get_results( $sql );
        foreach ( $countries as $country ) {
            if ( $country->name == 'USA' ) {
                continue;
            }
            $regionLocations[] = $country->name;
        }

        //get the resorts...
        $sql = "SELECT ResortName FROM wp_resorts WHERE active=1";
        $results = $wpdb->get_results( $sql );

        foreach ( $results as $result ) {
            $resortLocations[] = $result->ResortName;
        }
    }


    sort( $regionLocations );
    sort( $resortLocations );
    foreach ( $regionLocations as $loc ) {
        $locations[] = [
            'category' => 'REGION',
            'label' => $loc,
            'value' => $loc,
        ];
    }
    foreach ( $resortLocations as $loc ) {
        $locations[] = [
            'category' => 'RESORT',
            'label' => $loc,
            'value' => $loc,
        ];
    }


    $search = [];
    if ( ! empty( $term ) ) {
        foreach ( $regionLocations as $item ) {
            $pos = strpos( strtolower( $item ), strtolower( $term ) );
            if ( $pos !== false ) {
                $search[] = [
                    'category' => 'REGION',
                    'label' => $item,
                    'value' => $item,
                ];
            }
        }

        foreach ( $resortLocations as $item ) {
            $pos = strpos( strtolower( $item ), strtolower( $term ) );
            if ( $pos !== false ) {
                $search[] = [
                    'category' => 'RESORT',
                    'label' => $item,
                    'value' => $item,
                ];
            }
        }
        $locations = $search;
    }
    wp_send_json( $locations );
}

add_action( "wp_ajax_gpx_autocomplete_location", "gpx_autocomplete_location_fn" );
add_action( "wp_ajax_nopriv_gpx_autocomplete_location", "gpx_autocomplete_location_fn" );

function gpx_autocomplete_usw_fn() {
    global $wpdb;

    $term = ( ! empty( $_GET['term'] ) ) ? sanitize_text_field( $_GET['term'] ) : '';

    $sql = "SELECT DISTINCT name, subName, displayName FROM wp_gpxRegion WHERE ddHidden = 0 AND ";
    $sql .= empty( $term ) ? 'featured = 1' : "name != 'All'";

    $regions = $wpdb->get_results( $sql );
    foreach ( $regions as $region ) {
        if ( isset( $region->displayName ) && ! empty( trim( $region->displayName ) ) ) {
            $regionLocations[] = $region->displayName;
        } elseif ( isset( $region->subName ) && ! empty( trim( $region->subName ) ) ) {
            $regionLocations[] = $region->subName;
        } else {
            $regionLocations[] = $region->name;
        }
    }

    if ( ! empty( $term ) ) {
        //get the regions...
        $sql = "SELECT country as name FROM wp_gpxCategory";
        $countries = $wpdb->get_results( $sql );
        foreach ( $countries as $country ) {
            if ( $country->name == 'USA' ) {
                continue;
            }
            $regionLocations[] = $country->name;
        }

        //get the resorts...
        $sql = "SELECT ResortName FROM wp_resorts WHERE active='1'";
        $results = $wpdb->get_results( $sql );

        foreach ( $results as $result ) {
            $resortLocations[] = $result->ResortName;
        }
    }


    sort( $regionLocations );
    sort( $resortLocations );
    foreach ( $regionLocations as $loc ) {
        $locations[] = [
            'category' => 'REGION',
            'label' => $loc,
            'value' => $loc,
        ];
    }
    foreach ( $resortLocations as $loc ) {
        $locations[] = [
            'category' => 'RESORT',
            'label' => $loc,
            'value' => $loc,
        ];
    }

    $search = [];
    if ( ! empty( $term ) ) {
        foreach ( $regionLocations as $item ) {
            $pos = strpos( strtolower( $item ), strtolower( $term ) );
            if ( $pos !== false ) {
                $search[] = [
                    'category' => 'REGION',
                    'label' => $item,
                    'value' => $item,
                ];
            }
        }

        foreach ( $resortLocations as $item ) {
            $pos = strpos( strtolower( $item ), strtolower( $term ) );
            if ( $pos !== false ) {
                $search[] = [
                    'category' => 'RESORT',
                    'label' => $item,
                    'value' => $item,
                ];
            }
        }
        $locations = $search;
    }

    wp_send_json( $locations );
}

add_action( "wp_ajax_gpx_autocomplete_usw", "gpx_autocomplete_usw_fn" );
add_action( "wp_ajax_nopriv_gpx_autocomplete_usw", "gpx_autocomplete_usw_fn" );
/*
 * page loading shortcodes
 *
 *
 */

function gpx_get_location_coordinates_fn() {
    global $wpdb;

    $return = [];

    $sql = $wpdb->prepare( "SELECT lng, lat FROM wp_gpxRegion WHERE (name=%s OR displayName=%s)",
                           [ $_POST['region'], $_POST['region'] ] );
    $row = $wpdb->get_row( $sql );

    if ( $row->lng != '0' && $row->lat != '0' ) {
        $return['success'] = true;
    }

    wp_send_json( $return );
}

add_action( "wp_ajax_gpx_get_location_coordinates", "gpx_get_location_coordinates_fn" );
add_action( "wp_ajax_nopriv_gpx_get_location_coordinates", "gpx_get_location_coordinates_fn" );

function gpx_booking_path_sc( $atts ) {
    global $wpdb;

    $atts = shortcode_atts(
        [
            'terms' => '',
        ],
        $atts,
        'gpx_booking_path' );

    $cid = gpx_get_switch_user_cookie();

    require_once GPXADMIN_PLUGIN_DIR . '/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin( GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR );

    $sql = $wpdb->prepare( "SELECT SUM(credit_amount) AS total_credit_amount, SUM(credit_used) AS total_credit_used FROM wp_credit WHERE owner_id IN (SELECT gpx_user_id FROM wp_mapuser2oid WHERE gpx_user_id=%d) AND (credit_expiration_date IS NULL OR credit_expiration_date > %s)",
                           [ $cid, date( 'Y-m-d' ) ] );
    $credit = $wpdb->get_row( $sql );

    $credits = $credit->total_credit_amount - $credit->total_credit_used;


    $sql = $wpdb->prepare( "SELECT *  FROM `wp_GPR_Owner_ID__c` WHERE `user_id` = %d", $cid );
    $gprOwner = $wpdb->get_row( $sql );

    $sql = $wpdb->prepare( "SELECT *  FROM `wp_mapuser2oid` WHERE `gpx_user_id` = %d", $cid );
    $wp_mapuser2oid = $gpx->GetMappedOwnerByCID( $cid );

    $memberNumber = '';

    if ( ! empty( $wp_mapuser2oid ) ) {
        $memberNumber = $wp_mapuser2oid->gpr_oid;
    }

    $sql = $wpdb->prepare( "SELECT a.*, b.ResortName, c.deposit_year FROM wp_owner_interval a
            INNER JOIN wp_resorts b ON b.gprID LIKE CONCAT(BINARY a.resortID, '%%')
            LEFT JOIN (SELECT MAX(deposit_year) as deposit_year, interval_number FROM wp_credit WHERE status != 'Pending' GROUP BY interval_number) c ON c.interval_number=a.contractID
            WHERE a.Contract_Status__c != 'Cancelled'
                AND a.ownerID IN
                (SELECT gpr_oid
                    FROM wp_mapuser2oid
                    WHERE gpx_user_id IN
                        (SELECT gpx_user_id
                        FROM wp_mapuser2oid
                        WHERE gpr_oid=%d))",
                           $memberNumber );
    $ownerships = $wpdb->get_results( $sql, ARRAY_A );

    //Rule is # of Ownerships  (i.e. � have 2 weeks, can have account go to negative 2, one per week)
    $newcredit = ( ( $credits ) - 1 ) * - 1;


    if ( $newcredit > count( $ownerships ) ) {
        $errorMessage = 'Please deposit a week to continue.';
    }

    $current_user_fr = wp_get_current_user();
    $roles = $current_user_fr->roles;
    $role = array_shift( $roles );

    $cid = gpx_get_switch_user_cookie();

    if ( isset( $cid ) && ! empty( $cid ) ) {
        $user = get_userdata( $cid );
        if ( isset( $user ) && ! empty( $user ) ) {
            $usermeta = (object) array_map( function ( $a ) {
                return $a[0];
            }, get_user_meta( $cid ) );
        }

        $book = $_GET['book'];
        //get the property and resort
        $property_details = get_property_details( $book, $cid );

        if ( isset( $property_details['error'] ) ) {
            $property_error = true;
        } else {
            extract( $property_details );

            if ( ! isset( $_COOKIE['gpx-cart'] ) ) {
                if ( isset( $prop->weekId ) ) {
                    $propWeekId = $prop->weekId;
                } else {
                    $propWeekId = mt_rand( 100000, 999999 );
                }
                $cookie = [
                    'name' => 'gpx-cart',
                    'value' => $cid . "-" . $propWeekId,
                    'expires' => '30',
                    'path' => '/',
                    'site' => site_url(),
                ];
                include( 'templates/js-set-cookie.php' );
                $_COOKIE['gpx-cart'] = $cid . "-" . $propWeekId;
            }
            $profilecols[0] = [
                [
                    'placeholder' => "First Name",
                    'type' => 'text',
                    'class' => 'validate',
                    'value' => [
                        'name' => 'FirstName1',
                        'from' => 'usermeta',
                        'retrieve' => 'SPI_First_Name__c',
                    ],
                    'required' => 'required',
                ],
                [
                    'placeholder' => "Last Name",
                    'type' => 'text',
                    'class' => 'validate',
                    'value' => [ 'name' => 'LastName1', 'from' => 'usermeta', 'retrieve' => 'SPI_Last_Name__c' ],
                    'required' => 'required',
                ],
                [
                    'placeholder' => "Email",
                    'type' => 'email',
                    'class' => 'validate',
                    'value' => [ 'name' => 'email', 'from' => 'usermeta', 'retrieve' => 'SPI_Email__c' ],
                    'required' => 'required',
                ],
                [
                    'placeholder' => "Phone",
                    'type' => 'tel',
                    'class' => 'validate',
                    'value' => [ 'name' => 'phone', 'from' => 'usermeta', 'retrieve' => 'SPI_Home_Phone__c' ],
                    'required' => 'required',
                ],
                [
                    'placeholder' => "Adults",
                    'type' => 'text',
                    'class' => 'validate validate-int',
                    'value' => [ 'from' => 'usermeta', 'retrieve' => 'adults' ],
                    'required' => 'required',
                ],
                [
                    'placeholder' => "Children",
                    'type' => 'text',
                    'class' => 'validate validate-int',
                    'value' => [ 'from' => 'usermeta', 'retrieve' => 'children' ],
                    'required' => 'required',
                ],
                [
                    'placeholder' => "Special Request",
                    'class' => '',
                    'value' => [ 'from' => 'usermeta', 'retrieve' => '' ],
                    'required' => '',
                    'textarea' => true,
                ],
            ];

            $user = get_userdata( $cid );
            if ( isset( $user ) && ! empty( $user ) ) {
                $usermeta = (object) array_map( function ( $a ) {
                    return $a[0];
                }, get_user_meta( $cid ) );
            }

            $mapMissing = [
                'SPI_First_Name__c' => 'first_name',
                'SPI_Last_Name__c' => 'last_name',
                'SPI_Email__c' => 'Email',
                'SPI_Home_Phone__c' => 'DayPhone',
            ];
            foreach ( $mapMissing as $mapKey => $mapValue ) {
                if ( ! isset( $usermeta->$mapKey ) && isset( $usermeta->$mapValue ) ) {
                    $usermeta->$mapKey = $usermeta->$mapValue;
                }
            }
            $savesearch = save_search( $usermeta, '', 'select', '', $property_details );
        }
    }

    include( 'templates/sc-booking-path.php' );
}

add_shortcode( 'gpx_booking_path', 'gpx_booking_path_sc' );

function gpx_booking_path_payment_sc( $atts ) {
    global $wpdb;

    $atts = shortcode_atts(
        [
            'terms' => '',
        ],
        $atts,
        'gpx_booking_path_payment' );

    $cid = gpx_get_switch_user_cookie();

    if ( ! isset( $_COOKIE['gpx-cart'] ) ) {
        include( 'templates/sc-booking-path-payment-empty.php' );
        return;
    }
    $regularcheckout = true;
    //is this a simple checkout?
    $sql = $wpdb->prepare( "SELECT weekId, propertyID, data FROM wp_cart WHERE cartID=%s", $_COOKIE['gpx-cart'] );
    $results = $wpdb->get_results( $sql );

    if ( ! empty( $results ) ) {
        $scNotSkip = false;
        $extensionFee = 0.00;
        $GuestFeeAmount = 0.00;
        $LateDepositFeeAmount = 0.00;
        $checkoutAmount = 0.00;
        $distinctOwner = [];
        $distinctActivity = [];
        foreach ( $results as $result ) {
            if ( $result->propertyID > 0 ) {
                //this cart has actual properties in it -- skip simple checkout
                $scNotSkip = false;
                $regularcheckout = true;
                break;
            } else {
                $scNotSkip = true;
                $row = $result;
            }
        }
        if ( $scNotSkip && ( empty( $row->propertyID ) || $row->propertyID == '0' ) ) {
            $regularcheckout = false;

            $data = json_decode( $row->data );

            if ( $data->type == 'late_deposit_fee' ) {
                $LateDepositFeeAmount = (float)$data->fee;
            }
            if ( $data->type == 'extension' ) {
                $extensionFee = (float)$data->fee;
            }
            if ( $data->type == 'guest' ) {
                $GuestFeeAmount = (float)$data->fee;
            }
            $checkoutItem = $data->type;

            $checkoutAmount = (float)$data->fee;

            if ( isset( $data->occoupon ) ) {
                $occoupons = DB::table( 'wp_gpxOwnerCreditCoupon', 'a' )
                               ->selectRaw( "*, a.id as cid, b.id as aid, c.id as oid" )
                               ->join( 'wp_gpxOwnerCreditCoupon_activity as b', 'b.couponID', '=', 'a.id' )
                               ->join( 'wp_gpxOwnerCreditCoupon_owner as c', 'c.couponID', '=', 'a.id' )
                               ->whereIn( 'a.id', $data->occoupon )
                               ->where( 'a.active', '=', 1 )
                               ->where( 'c.ownerID', '=', $cid )
                               ->get()->toArray();
                if ( ! empty( $occoupons ) ) {
                    foreach ( $occoupons as $occoupon ) {
                        $distinctCoupon = $occoupon;
                        $distinctOwner[ $occoupon->oid ] = $occoupon;
                        $distinctActivity[ $occoupon->aid ] = $occoupon;
                    }

                    $actredeemed = [];
                    $actamount = [];
                    //get the balance and activity for data
                    foreach ( $distinctActivity as $activity ) {
                        if ( $activity->activity == 'transaction' ) {
                            $actredeemed[] = (float)$activity->amount;
                        } else {
                            $actamount[] = (float)$activity->amount;
                        }
                    }
                    if ( $distinctCoupon->single_use && array_sum( $actredeemed ) > 0 ) {
                        $balance = 0;
                    } else {
                        $balance = array_sum( $actamount ) - array_sum( $actredeemed );
                    }
                    //if we have a balance at this point the coupon is good
                    if ( $balance > 0 ) {
                        if ( $balance <= $checkoutAmount ) {
                            $checkoutAmount = $checkoutAmount - $balance;
                            $indCartOCCreditUsed[] = $balance;
                            $couponDiscount = array_sum( $indCartOCCreditUsed );
                        } else {
                            $indCartOCCreditUsed[] = $checkoutAmount;
                            $couponDiscount = $checkoutAmount;
                            $checkoutAmount = 0;
                        }
                    }
                }
            }
        }
    }
    if ( $regularcheckout ) {
        //get the details from gpxmodel
        $checkoutData = get_property_details_checkout( $cid );
        extract( $checkoutData );

        include( 'templates/sc-booking-path-payment.php' );
    } else {
        include( 'templates/sc-booking-path-payment-simple-checkout.php' );
    }
}


add_shortcode( 'gpx_booking_path_payment', 'gpx_booking_path_payment_sc' );

function gpx_booking_path_confirmation_cs() {
    global $wpdb;

    $cid = gpx_get_switch_user_cookie();
    $cartID = $_GET['confirmation'] ?? $_COOKIE['gpx-cart'] ?? '';
    $rows = [];
    if ( ! empty( $cartID ) ) {
        $sql = $wpdb->prepare( "SELECT * FROM wp_gpxTransactions WHERE cartID=%s AND cancelled IS NULL", $cartID );
        $rows = $wpdb->get_results( $sql );
    }
    $i = 0;
    if ( ! empty( $rows ) ) {
        foreach ( $rows as $row ) {
            if ( empty( $row->sessionID ) ) {
                continue;
            }
            $user = get_userdata( $cid );
            if ( isset( $user ) && ! empty( $user ) ) {
                $usermeta = (object) array_map( function ( $a ) {
                    return $a[0];
                }, get_user_meta( $cid ) );
            }

            $transactions[ $i ] = json_decode( $row->data );

            $sql = $wpdb->prepare( "SELECT * FROM wp_resorts WHERE ResortID=%s",
                                   $transactions[ $i ]->ResortID );
            $resort[ $i ] = $wpdb->get_row( $sql );

            $sql = $wpdb->prepare( "SELECT * FROM wp_resorts_meta WHERE ResortID=%s", $transactions[ $i ]->ResortID );
            $rms = $wpdb->get_results( $sql );

            $attributesList = [
                'UnitFacilities' => 'Unit Facilities',
                'ResortFacilities' => 'Resort Facilities',
                'AreaFacilities' => 'Area Facilities',
                'resortConditions' => 'Resort Conditions',
                'configuration' => 'Conditions',
                'CommonArea' => 'Common Area Accessibility Features',
                'GuestRoom' => 'Guest Room Accessibility Features',
                'GuestBathroom' => 'Guest Bathroom Accessibility Features',
                'UponRequest' => 'The following can be added to any Guest Room upon request',
                'UnitConfig' => 'Unit Config',
            ];

            foreach ( $rms as $rm ) {
                $rmk = $rm->meta_key;

                if ( $rmArr = json_decode( $rm->meta_value, true ) ) {
                    foreach ( $rmArr as $rmdate => $rmvalues ) {
                        // we need to display all of the applicaable alert notes
                        if ( isset( $lastValue ) && ! empty( $lastValue ) ) {
                            $thisVal = $lastValue;
                        } elseif ( isset( $resort->{$rmk} ) ) {
                            $thisVal = $resort->{$rmk};
                        }

                        $rmdates = explode( "_", $rmdate );
                        $from = (int) $rmdates[0];
                        $to = isset( $rmdates[1] ) ? (int) $rmdates[1] : null;
                        $checkin = isset( $transactions[ $i ]->checkIn ) ? strtotime( $transactions[ $i ]->checkIn ) : null;
                        if ( $from ) {
                            //check to see if the from date within the checkin date
                            if ( $from >= $checkin ) {
                                //these meta items don't need to be used -- except for alert notes -- we can show those in the future
                                if ( $rmk != 'AlertNote' ) {
                                    continue;
                                }
                            }
                            //check to see if the to date has passed
                            if ( $to && ( $to < $checkin ) ) {
                                //these meta items don't need to be used
                                continue;
                            }
                            if ( array_key_exists( $rmk, $attributesList ) ) {
                                // this is an attribute list Handle it now...
                                $thisVal = json_encode( $rmvalues );
                            } else {
                                $rmval = end( $rmvalues );
                                //check to see if this should be displayed in the booking path
                                if ( isset( $rmval['path'] ) && $rmval['path']['booking'] == 0 ) {
                                    //this isn't supposed to be part of the booking path
                                    continue;
                                }
                                if ( isset( $rmval['desc'] ) ) {
                                    if ( $rmk == 'AlertNote' ) {
                                        if ( ! isset( $thisset ) || ! in_array( $rmval['desc'], $thisset ) ) {
                                            $thisValArr[] = [
                                                'desc' => $rmval['desc'],
                                                'date' => $rmdates,
                                            ];
                                        }
                                        $thisset[] = $rmval['desc'];
                                    } else {
                                        $thisVal = $rmval['desc'];
                                        $thisValArr = [];
                                    }
                                }
                            }
                        }
                        $lastValue = $thisVal;
                    }
                    if ( $rmk == 'AlertNote' && isset( $thisValArr ) && ! empty( $thisValArr ) ) {
                        $thisVal = $thisValArr;
                    }
                    $resort[ $i ]->$rmk = $thisVal;
                } else {
                    if ( $rm->meta_value != '[]' ) {
                        $resort[ $i ]->$rmk = $rm->meta_value;
                    }
                }
            }

            $sql = $wpdb->prepare( "SELECT id FROM wp_properties WHERE weekID=%s", $row->weekId );
            $prow = $wpdb->get_row( $sql );
            if ( ! empty( $prow ) ) {
                $book = $prow->id;
            } else {
                $book = $row->weekId;
            }


            $property_details[ $i ] = get_property_details( $book, $cid );

            //check for auto coupons
            $sql = $wpdb->prepare( "SELECT a.coupon_hash, b.Name, b.Properties, b.Slug FROM wp_gpxAutoCoupon a
                    INNER JOIN wp_specials b ON a.coupon_id=b.id
                    WHERE transaction_id=%d AND user_id = %d",
                                   [ $row->id, $row->userID ] );
            $acRow = $wpdb->get_row( $sql );
            if ( ! empty( $acRow ) ) {
                $acProps = json_decode( $acRow->Properties );

                $acCoupon[ $i ] = [
                    'name' => $acRow->Name,
                    'slug' => $acRow->Slug,
                    'code' => $acRow->coupon_hash,
                    'tc' => $acProps->actc,
                ];
            }

            if ( isset( $transactions[ $i ]->promoName ) && ! empty( $transactions[ $i ]->promoName ) ) {
                $sql = $wpdb->prepare( "SELECT * FROM wp_specials WHERE Name LIKE %s",
                                       '%' . $wpdb->esc_like( $transactions[ $i ]->promoName ) . '%' );
                $promos = $wpdb->get_results( $sql );
                foreach ( $promos as $promo ) {
                    $promoprops = json_decode( $promo->Properties );
                    if ( isset( $promoprops->terms ) && ! empty( $promoprops->terms ) ) {
                        $tcs[ $promoprops->terms ] = $promoprops->terms;
                    }
                }
            }
            if ( isset( $property_details[ $i ]['promoTerms'] ) && ! empty( $property_details[ $i ]['promoTerms'] ) ) {
                $tcs[ $property_details[ $i ]['promoTerms'] ] = $property_details[ $i ]['promoTerms'];
            }
            $i ++;
        }
        if ( ! isset( $transactions ) ) {
            foreach ( $rows as $row ) {
                $transactions[ $row->id ] = json_decode( $row->data );
            }
        }
    } else {
        $sql = $wpdb->prepare( "SELECT * FROM wp_cart WHERE cartID=%s ORDER BY id DESC LIMIT 1", $cartID );
        $row = $wpdb->get_row( $sql );

        $transactions[ $row->id ] = json_decode( $row->data );
        if ( empty( $transactions[ $row->id ]->Paid ) ) {
            $transactions[ $row->id ]->Paid = $transactions[ $row->id ]->fee;
        }
    }
    include( 'templates/sc-booking-path-confirmation.php' );
}

add_shortcode( 'gpx_booking_path_confirmation', 'gpx_booking_path_confirmation_cs' );

function gpx_email_confirmation( $atts ) {
    global $wpdb;

    $atts = shortcode_atts(
        [
            'terms' => '',
        ],
        $atts,
        'gpx_booking_path_confirmation' );

    $cid = gpx_get_switch_user_cookie();
    if ( isset( $_POST['confirmation'] ) ) {
        $cartID = $_GET['confirmation'];
    }
    $sql = $wpdb->prepare( "SELECT * FROM wp_gpxTransactions WHERE cartID=%s", $cartID );
    $rows = $wpdb->get_results( $sql );
    $i = 0;
    if ( ! empty( $rows ) ) {
        foreach ( $rows as $row ) {
            $user = get_userdata( $cid );
            if ( isset( $user ) && ! empty( $user ) ) {
                $usermeta = (object) array_map( function ( $a ) {
                    return $a[0];
                }, get_user_meta( $cid ) );
            }

            $transactions[ $i ] = json_decode( $row->data );

            $sql = $wpdb->prepare( "SELECT * FROM wp_resorts WHERE ResortID=%s", $transactions[ $i ]->ResortID );
            $resort[ $i ] = $wpdb->get_row( $sql );

            $sql = $wpdb->prepare( "SELECT * FROM wp_resorts_meta WHERE ResortID=%s", $transactions[ $i ]->ResortID );
            $rms = $wpdb->get_results( $sql );

            die(); // @TODO Jonathan: Is this here on purpose?

            foreach ( $rms as $rm ) {
                $rmk = $rm->meta_key;
                $resort[ $i ]->$rmk = $rm->meta_value;
            }

            $sql = $wpdb->prepare( "SELECT id FROM wp_properties WHERE weekID = %s", $row->weekId );
            $prow = $wpdb->get_row( $sql );
            if ( ! empty( $prow ) ) {
                $book = $prow->id;
            } else {
                $book = $row->weekId;
            }

            $property_details[ $i ] = get_property_details( $book, $cid );
            $i ++;
        }
    }
    include( 'templates/sc-booking-path-confirmation.php' );
}

add_shortcode( 'gpx_email_confirmation', 'gpx_email_confirmation' );

function map_dae_to_vest_properties() {
    $mapPropertiesToRooms = [
        'id' => 'record_id',
        'checkIn' => 'check_in_date',
        'checkOut' => 'check_out_date',
        'Price' => 'price',
        'weekID' => 'record_id',
        'weekId' => 'record_id',
        'resortId' => 'resort',
        'resortID' => 'resort',
        'StockDisplay' => 'availability',
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
        'country' => 'Country',
        'region' => 'Region',
        'locality' => 'Town',
        'resortName' => 'ResortName',
    ];
    $mapPropertiesToResort = [
        'Country' => 'Country',
        'Region' => 'Region',
        'Town' => 'Town',
        'ResortName' => 'ResortName',
        'ImagePath1' => 'ImagePath1',
        'AlertNote' => 'AlertNote',
        'AdditionalInfo' => 'AdditionalInfo',
        'HTMLAlertNotes' => 'HTMLAlertNotes',
        'ResortID' => 'ResortID',
        'taxMethod' => 'taxMethod',
        'taxID' => 'taxID',
        'gpxRegionID' => 'gpxRegionID',
    ];

    $output['roomTable'] = [
        'alias' => 'a',
        'table' => 'wp_room',
    ];
    $output['unitTable'] = [
        'alias' => 'c',
        'table' => 'wp_unit_type',
    ];
    $output['resortTable'] = [
        'alias' => 'b',
        'table' => 'wp_resorts',
    ];
    foreach ( $mapPropertiesToRooms as $key => $value ) {
        if ( $key == 'noNights' ) {
            $output['joinRoom'][] = $value . ' as ' . $key;
        } else {
            $output['joinRoom'][] = $output['roomTable']['alias'] . '.' . $value . ' as ' . $key;
        }
    }
    foreach ( $mapPropertiesToUnit as $key => $value ) {
        $output['joinUnit'][] = $output['unitTable']['alias'] . '.' . $value . ' as ' . $key;
    }
    foreach ( $mapPropertiesToResort as $key => $value ) {
        $output['joinResort'][] = $output['resortTable']['alias'] . '.' . $value . ' as ' . $key;
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
function gpx_result_page_sc( $resortID = '', $paginate = [], $calendar = '' ) {
    global $wpdb;
    $ids = [];
    //     //update the join id

    if ( isset( $resortID ) && ! empty( $resortID ) ) {
        $outputProps = true;
    }
    $paginate = [
        'limitstart' => $paginate['limitstart'] ?? 0,
        'limitcount' => $paginate['limitcount'] ?? 0,
    ];
    $limitStart = $paginate['limitstart'];
    $limitCount = $paginate['limitcount'];
    if ( $paginate['limitcount'] > 0 ) {
        // some of the records might get filtered out so we pull double what we need and will return the correct amount later.
        // this is to fix fewer than the requested amount of weeks being shown.
        $limit = $wpdb->prepare( " LIMIT %d, %d", [ $paginate['limitstart'], $paginate['limitcount'] * 2 ] );
    }

    $cid = gpx_get_switch_user_cookie();

    if ( isset( $cid ) && ! empty( $cid ) ) {
        $user = get_userdata( $cid );
        $usermeta = (object) array_map( function ( $a ) {
            return $a[0];
        }, get_user_meta( $cid ) );
    }


    if ( ! get_user_meta( $cid, 'DAEMemberNo', true ) ) {
        require_once GPXADMIN_API_DIR . '/functions/class.gpxretrieve.php';
        $gpx = new GpxRetrieve( GPXADMIN_API_URI, GPXADMIN_API_DIR );

        $DAEMemberNo = str_replace( "U", "", $user->user_login );
        $user = $gpx->DAEGetMemberDetails( $DAEMemberNo, $cid, [ 'email' => $usermeta->email ] );
    }

    if ( isset( $_GET['destination'] ) ) {
        $_REQUEST['location'] = $_GET['destination'];
        if ( $_REQUEST['select_year'] > 2018 ) {
            //we need to pull these dates
        } else {
            $alldates = true;
        }
    }
    if ( isset( $_REQUEST ) ) {
        extract( $_REQUEST, EXTR_SKIP );

        //is this a previously matched result?
        if ( isset( $_REQUEST['matched'] ) ) {
            $sql = $wpdb->prepare( "SELECT * FROM wp_gpxCustomRequest WHERE id=%d", $matched );
            $matchedDB = (array) $wpdb->get_row( $sql );
            $props = custom_request_match( $matchedDB, '1' );
            unset( $props['restricted'] );
        } else {
            if ( ( empty( $select_month ) && empty( $select_year ) ) ) {
                $alldates = true;
            }
            if ( mb_strtolower( $select_month ) == 'any' ) {
                $thisYear = date( 'Y' );
                if ( ! isset( $select_year ) ) {
                    $select_year = date( 'Y' );
                }
                $monthstart = date( $select_year . '-m-d' );
                if ( $thisYear != $select_year ) {
                    $monthstart = $select_year . '-01-01';
                }
                $monthend = $select_year . "-12-31";
            } else {
                $nextmonth = date( 'Y-m-d', strtotime( '+1 month' ) );
                if ( ! isset( $select_year ) ) {
                    $select_year = date( 'Y' );
                }
                if ( ! isset( $select_month ) ) {
                    $select_month = date( 'f', strtotime( $nextmonth ) );
                }
                $monthstart = date( 'Y-m-01', strtotime( $select_month . "-" . $select_year ) );
                $today = date( 'Y-m-d' );
                if ( $monthstart < $today ) {
                    $monthstart = $today;
                }
                $monthend = date( 'Y-m-t', strtotime( $select_month . "-" . $select_year ) );
            }

            $sql = "SELECT
                    `a`.`record_id` AS `id`, `a`.`check_in_date` AS `checkIn`, `a`.`check_out_date` AS `checkOut`, `a`.`price` AS `Price`,
                    `a`.`record_id` AS `weekID`, `a`.`record_id` AS `weekId`, `a`.`resort` AS `resortId`, `a`.`resort` AS `resortID`,
                    `a`.`availability` AS `StockDisplay`, `a`.`type` AS `WeekType`, DATEDIFF(`a`.`check_out_date`, `a`.`check_in_date`) AS `noNights`,
                    `a`.`active_rental_push_date` AS `active_rental_push_date`,
                    `b`.`Country` AS `Country`, `b`.`Region` AS `Region`, `b`.`Town` AS `Town`, `b`.`ResortName` AS `ResortName`,
                    `b`.`ImagePath1` AS `ImagePath1`, `b`.`AlertNote` AS `AlertNote`, `b`.`AdditionalInfo` AS `AdditionalInfo`,
                    `b`.`HTMLAlertNotes` AS `HTMLAlertNotes`, `b`.`ResortID` AS `ResortID`, `b`.`taxMethod` AS `taxMethod`,
                    `b`.`taxID` AS `taxID`, `b`.`gpxRegionID` AS `gpxRegionID`,
                    `c`.`number_of_bedrooms` AS `bedrooms`, `c`.`sleeps_total` AS `sleeps`, `c`.`name` AS `Size`,
                    `a`.`record_id` AS `PID`, `b`.`id` AS `RID`
                FROM `wp_room` AS `a`
                INNER JOIN `wp_resorts` AS `b` ON `a`.`resort` = `b`.`id`
                INNER JOIN `wp_unit_type` AS `c` ON `a`.`unit_type` = `c`.`record_id`
                WHERE `b`.`featured` = 1 AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1";

            $featuredprops = $wpdb->get_results( $sql );


            foreach ( $featuredprops as $featuredprop ) {
                $featuredresorts[ $featuredprop->ResortID ]['resort'] = $featuredprop;
                $featuredresorts[ $featuredprop->ResortID ]['props'][] = $featuredprop;
            }


            if ( isset( $_REQUEST['location'] ) && ! empty( $_REQUEST['location'] ) ) {
                $sql = $wpdb->prepare( "SELECT id, lft, rght FROM wp_gpxRegion WHERE name=%s OR displayName=%s",
                                       [ $location, $location ] );
                $locs = $wpdb->get_results( $sql );

                if ( empty( $locs ) ) {
                    //if this location is a country
                    $sql = $wpdb->prepare( "SELECT a.lft, a.rght FROM wp_gpxRegion a
                            INNER JOIN wp_daeRegion b ON a.RegionID=b.id
                            INNER JOIN wp_gpxCategory c ON c.CountryID=b.CategoryID
                            WHERE c.country = %s",
                                           $location );
                    $ranges = $wpdb->get_results( $sql );
                    if ( ! empty( $ranges ) ) {
                        foreach ( $ranges as $range ) {
                            $sql = $wpdb->prepare( "SELECT id, name FROM wp_gpxRegion
                                    WHERE lft BETWEEN %d AND %d
                                    ORDER BY lft ASC",
                                                   [ $range->lft, $range->rght ] );
                            $rows = $wpdb->get_results( $sql );
                            foreach ( $rows as $row ) {
                                $ids[] = $row->id;
                            }
                        }
                    } else {
                        //see if this is a resort
                        $sql = $wpdb->prepare( "SELECT id FROM wp_resorts WHERE ResortName=%s", $location );
                        $row = $wpdb->get_row( $sql );
                        if ( ! empty( $row ) ) {
                            //redirect to the resort
                            $redirectArr = [
                                'resortName' => $location,
                            ];
                            if ( isset( $select_month ) && ( ! empty( $select_month ) || $select_month != 'f' ) ) {
                                $redirectArr['month'] = $select_month;
                                if ( isset( $select_year ) && ! empty( $select_year ) ) {
                                    $redirectArr['yr'] = $select_year;
                                }
                            }
                            $redirectQS = http_build_query( $redirectArr );
                            $redirectURL = home_url( '/resort-profile/?' . $redirectQS );
                            echo "<script>window.location.href = '" . $redirectURL . "';</script>";
                            exit;
                        }
                    }
                } else {
                    foreach ( $locs as $loc ) {
                        $sql = $wpdb->prepare( "SELECT id, name FROM wp_gpxRegion
                                WHERE lft BETWEEN %d AND %d
                                ORDER BY lft ASC",
                                               [ $loc->lft, $loc->rght ] );
                        $rows = $wpdb->get_results( $sql );
                        foreach ( $rows as $row ) {
                            $ids[] = $row->id;
                        }
                    }
                }

                if ( isset( $_GET['destination'] ) ) {
                    $placeholders = empty( $ids ) ? '%s' : gpx_db_placeholders( $ids, '%d' );
                    $sql = $wpdb->prepare( "SELECT
                        `a`.`record_id` AS `id`, `a`.`check_in_date` AS `checkIn`, `a`.`check_out_date` AS `checkOut`, `a`.`price` AS `Price`,
                        `a`.`record_id` AS `weekID`, `a`.`record_id` AS `weekId`, `a`.`resort` AS `resortId`, `a`.`resort` AS `resortID`,
                        `a`.`availability` AS `StockDisplay`, `a`.`type` AS `WeekType`, DATEDIFF(`a`.`check_out_date`, `a`.`check_in_date`) AS `noNights`,
                        `a`.`active_rental_push_date` AS `active_rental_push_date`,
                        `b`.`Country` AS `Country`, `b`.`Region` AS `Region`, `b`.`Town` AS `Town`, `b`.`ResortName` AS `ResortName`,
                        `b`.`ImagePath1` AS `ImagePath1`, `b`.`AlertNote` AS `AlertNote`, `b`.`AdditionalInfo` AS `AdditionalInfo`,
                        `b`.`HTMLAlertNotes` AS `HTMLAlertNotes`, `b`.`ResortID` AS `ResortID`, `b`.`taxMethod` AS `taxMethod`,
                        `b`.`taxID` AS `taxID`, `b`.`gpxRegionID` AS `gpxRegionID`,
                        `c`.`number_of_bedrooms` AS `bedrooms`, `c`.`sleeps_total` AS `sleeps`, `c`.`name` AS `Size`,
                        `a`.`record_id` AS `PID`, `b`.`id` AS `RID`
                    FROM `wp_room` AS `a`
                    INNER JOIN `wp_resorts` AS `b` ON `a`.`resort` = `b`.`id`
                    INNER JOIN `wp_unit_type` AS `c` ON `a`.`unit_type` = `c`.`record_id`
                    WHERE b.GPXRegionID IN ({$placeholders}) AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1",
                                           ! empty( $ids ) ? $ids : [ 'na' ]
                    );
                } else {
                    $placeholders = empty( $ids ) ? '%s' : gpx_db_placeholders( $ids, '%d' );
                    $values = empty( $ids ) ? [ 'na' ] : $ids;
                    $values[] = $monthstart;
                    $values[] = $monthend;
                    $sql = $wpdb->prepare( "SELECT
                        `a`.`record_id` AS `id`, `a`.`check_in_date` AS `checkIn`, `a`.`check_out_date` AS `checkOut`, `a`.`price` AS `Price`,
                        `a`.`record_id` AS `weekID`, `a`.`record_id` AS `weekId`, `a`.`resort` AS `resortId`, `a`.`resort` AS `resortID`,
                        `a`.`availability` AS `StockDisplay`, `a`.`type` AS `WeekType`, DATEDIFF(`a`.`check_out_date`, `a`.`check_in_date`) AS `noNights`,
                        `a`.`active_rental_push_date` AS `active_rental_push_date`,
                        `b`.`Country` AS `Country`, `b`.`Region` AS `Region`, `b`.`Town` AS `Town`, `b`.`ResortName` AS `ResortName`,
                        `b`.`ImagePath1` AS `ImagePath1`, `b`.`AlertNote` AS `AlertNote`, `b`.`AdditionalInfo` AS `AdditionalInfo`,
                        `b`.`HTMLAlertNotes` AS `HTMLAlertNotes`, `b`.`ResortID` AS `ResortID`, `b`.`taxMethod` AS `taxMethod`,
                        `b`.`taxID` AS `taxID`, `b`.`gpxRegionID` AS `gpxRegionID`,
                        `c`.`number_of_bedrooms` AS `bedrooms`, `c`.`sleeps_total` AS `sleeps`, `c`.`name` AS `Size`,
                        `a`.`record_id` AS `PID`, `b`.`id` AS `RID`
                    FROM `wp_room` AS `a`
                    INNER JOIN `wp_resorts` AS `b` ON `a`.`resort` = `b`.`id`
                    INNER JOIN `wp_unit_type` AS `c` ON `a`.`unit_type` = `c`.`record_id`
                    WHERE b.GPXRegionID IN ({$placeholders}) AND a.check_in_date BETWEEN %s AND %s AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1",
                                           $values
                    );
                }
                $resortsSql = $wpdb->prepare( "SELECT * FROM wp_resorts b WHERE GPXRegionID IN ({$placeholders}) AND active = 1",
                                              empty( $ids ) ? [ 'na' ] : $ids );
            } elseif ( isset( $resortID ) ) {
                $values = [ $resortID ];
                if ( $select_month != 'f' ) {
                    $values[] = $monthstart;
                    $values[] = $monthend;
                    $destDateWhere = " AND (a.`check_in_date` BETWEEN %s AND %s) ";
                } else {
                    $values[] = $today;
                    $destDateWhere = " AND (a.`check_in_date` > %s) ";
                }

                $sql = $wpdb->prepare( "SELECT
                    `a`.`record_id` AS `id`, `a`.`check_in_date` AS `checkIn`, `a`.`check_out_date` AS `checkOut`, `a`.`price` AS `Price`,
                    `a`.`record_id` AS `weekID`, `a`.`record_id` AS `weekId`, `a`.`resort` AS `resortId`, `a`.`resort` AS `resortID`,
                    `a`.`availability` AS `StockDisplay`, `a`.`type` AS `WeekType`, DATEDIFF(`a`.`check_out_date`, `a`.`check_in_date`) AS `noNights`,
                    `a`.`active_rental_push_date` AS `active_rental_push_date`,
                    `b`.`Country` AS `Country`, `b`.`Region` AS `Region`, `b`.`Town` AS `Town`, `b`.`ResortName` AS `ResortName`,
                    `b`.`ImagePath1` AS `ImagePath1`, `b`.`AlertNote` AS `AlertNote`, `b`.`AdditionalInfo` AS `AdditionalInfo`,
                    `b`.`HTMLAlertNotes` AS `HTMLAlertNotes`, `b`.`ResortID` AS `ResortID`, `b`.`taxMethod` AS `taxMethod`,
                    `b`.`taxID` AS `taxID`, `b`.`gpxRegionID` AS `gpxRegionID`,
                    `c`.`number_of_bedrooms` AS `bedrooms`, `c`.`sleeps_total` AS `sleeps`, `c`.`name` AS `Size`,
                    `a`.`record_id` AS `PID`, `b`.`id` AS `RID`
                FROM `wp_room` AS `a`
                INNER JOIN `wp_resorts` AS `b` ON `a`.`resort` = `b`.`id`
                INNER JOIN `wp_unit_type` AS `c` ON `a`.`unit_type` = `c`.`record_id`
                WHERE b.id = %d {$destDateWhere} AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1
                ORDER BY a.`check_in_date`",
                                       $values );
            } elseif ( isset( $alldates ) ) {
                $sql = $wpdb->prepare( "SELECT
                    `a`.`record_id` AS `id`, `a`.`check_in_date` AS `checkIn`, `a`.`check_out_date` AS `checkOut`, `a`.`price` AS `Price`,
                    `a`.`record_id` AS `weekID`, `a`.`record_id` AS `weekId`, `a`.`resort` AS `resortId`, `a`.`resort` AS `resortID`,
                    `a`.`availability` AS `StockDisplay`, `a`.`type` AS `WeekType`, DATEDIFF(`a`.`check_out_date`, `a`.`check_in_date`) AS `noNights`,
                    `a`.`active_rental_push_date` AS `active_rental_push_date`,
                    `b`.`Country` AS `Country`, `b`.`Region` AS `Region`, `b`.`Town` AS `Town`, `b`.`ResortName` AS `ResortName`,
                    `b`.`ImagePath1` AS `ImagePath1`, `b`.`AlertNote` AS `AlertNote`, `b`.`AdditionalInfo` AS `AdditionalInfo`,
                    `b`.`HTMLAlertNotes` AS `HTMLAlertNotes`, `b`.`ResortID` AS `ResortID`, `b`.`taxMethod` AS `taxMethod`,
                    `b`.`taxID` AS `taxID`, `b`.`gpxRegionID` AS `gpxRegionID`,
                    `c`.`number_of_bedrooms` AS `bedrooms`, `c`.`sleeps_total` AS `sleeps`, `c`.`name` AS `Size`,
                    `a`.`record_id` AS `PID`, `b`.`id` AS `RID`
                FROM `wp_room` AS `a`
                INNER JOIN `wp_resorts` AS `b` ON `a`.`resort` = `b`.`id`
                INNER JOIN `wp_unit_type` AS `c` ON `a`.`unit_type` = `c`.`record_id`
                WHERE a.check_in_date > %s AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1
                ",
                                       $today );
            } else {
                $sql = $wpdb->prepare( "SELECT
                    `a`.`record_id` AS `id`, `a`.`check_in_date` AS `checkIn`, `a`.`check_out_date` AS `checkOut`, `a`.`price` AS `Price`,
                    `a`.`record_id` AS `weekID`, `a`.`record_id` AS `weekId`, `a`.`resort` AS `resortId`, `a`.`resort` AS `resortID`,
                    `a`.`availability` AS `StockDisplay`, `a`.`type` AS `WeekType`, DATEDIFF(`a`.`check_out_date`, `a`.`check_in_date`) AS `noNights`,
                    `a`.`active_rental_push_date` AS `active_rental_push_date`,
                    `b`.`Country` AS `Country`, `b`.`Region` AS `Region`, `b`.`Town` AS `Town`, `b`.`ResortName` AS `ResortName`,
                    `b`.`ImagePath1` AS `ImagePath1`, `b`.`AlertNote` AS `AlertNote`, `b`.`AdditionalInfo` AS `AdditionalInfo`,
                    `b`.`HTMLAlertNotes` AS `HTMLAlertNotes`, `b`.`ResortID` AS `ResortID`, `b`.`taxMethod` AS `taxMethod`,
                    `b`.`taxID` AS `taxID`, `b`.`gpxRegionID` AS `gpxRegionID`,
                    `c`.`number_of_bedrooms` AS `bedrooms`, `c`.`sleeps_total` AS `sleeps`, `c`.`name` AS `Size`,
                    `a`.`record_id` AS `PID`, `b`.`id` AS `RID`
                FROM `wp_room` AS `a`
                INNER JOIN `wp_resorts` AS `b` ON `a`.`resort` = `b`.`id`
                INNER JOIN `wp_unit_type` AS `c` ON `a`.`unit_type` = `c`.`record_id`
                WHERE a.check_in_date BETWEEN %s AND %s AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1
                ",
                                       [ $monthstart, $monthend ] );
            }
            if ( isset( $limit ) && ! empty( $limit ) ) {
                $sql .= $limit;
            }


            if ( $resortID || ! empty( $ids ) ) {
                $props = $wpdb->get_results( $sql );
            }
        }


        $totalCnt = count( $props );

        if ( ( isset( $props ) && ! empty( $props ) ) || isset( $resortsSql ) ) {
            //let's first get query specials by the variables that are already set
            $todayDT = date( "Y-m-d 00:00:00" );
            $placeholders = gpx_db_placeholders( $ids, '%d' );
            $values = $ids;
            $values[] = $todayDT;
            $values[] = $todayDT;
            $sql = $wpdb->prepare( "SELECT a.id, a.Name, a.Properties, a.Amount, a.SpecUsage, a.TravelStartDate, a.TravelEndDate
			FROM wp_specials a
            LEFT JOIN wp_promo_meta b ON b.specialsID=a.id
            LEFT JOIN wp_resorts c ON c.id=b.foreignID
            LEFT JOIN wp_gpxRegion d ON d.id=b.foreignID
            WHERE
                    (SpecUsage = 'any'
                 OR   ((b.reftable = 'wp_gpxRegion' AND d.id IN ({$placeholders})))
                        OR SpecUsage LIKE '%%customer%%'
                        OR SpecUsage LIKE '%%dae%%')
            AND Type='promo'
            AND (StartDate <= %s AND EndDate >= %s)
            AND a.Active=1
            GROUP BY a.id",
                                   $values );
            $firstRows = $wpdb->get_results( $sql );

            $prop_string = [];
            $new_props = [];
            foreach ( $props as $p ) {
                $week_date_size = $p->resortId . '=' . $p->WeekType . '=' . date( 'm/d/Y',
                                                                                  strtotime( $p->checkIn ) ) . '=' . $p->Size;
                if ( ! in_array( $week_date_size, $prop_string ) ) {
                    $new_props[] = $p;
                }
                array_push( $prop_string, $week_date_size );
            }


            $count_week_date_size = ( array_count_values( $prop_string ) );


            $props = $new_props;


            $theseResorts = [];
            foreach ( $props as $propK => $prop ) {
                //validate availablity
                if ( $prop->availablity == '2' ) {
                    //partners shouldn't see this
                    //this should only be available to partners
                    $sql = $wpdb->prepare( "SELECT record_id FROM wp_partner WHERE user_id=%d", $cid );
                    $row = $wpdb->get_row( $sql );
                    if ( ! empty( $row ) ) {
                        unset( $props[ $propK ] );
                        continue;
                    }
                }
                if ( $prop->availablity == '3' ) {
                    //only partners shouldn't see this
                    //this should only be available to partners
                    $sql = $wpdb->prepare( "SELECT record_id FROM wp_partner WHERE user_id=%d", $cid );
                    $row = $wpdb->get_row( $sql );
                    if ( empty( $row ) ) {
                        unset( $props[ $propK ] );
                        continue;
                    }
                }

                if ( ! isset( $prop->ResortID ) ) {
                    $rSql = $wpdb->prepare( "SELECT ResortID FROM wp_resorts WHERE id=%d", $prop->RID );
                    $rRow = $wpdb->get_row( $rSql );
                    $prop->ResortID = $rRow->ResortID;
                }

                $string_week_date_size = $prop->resortId . '=' . $prop->WeekType . '=' . date( 'm/d/Y',
                                                                                               strtotime( $prop->checkIn ) ) . '=' . $prop->Size;
                $prop->prop_count = $count_week_date_size[ $string_week_date_size ];

                //set all the resorts that are part of the results
                if ( ! in_array( $prop->ResortID, $theseResorts ) ) {
                    $theseResorts[ $prop->ResortID ] = $prop->ResortID;

                    //get all ther regions that this property belongs to
                    $propRegionParentIDs[ $prop->ResortID ] = [];
                    $sql = $wpdb->prepare( "SELECT parent FROM wp_gpxRegion WHERE id=%d", $prop->gpxRegionID );
                    $thisParent = $wpdb->get_var( $sql );
                    $propRegionParentIDs[ $prop->ResortID ][] = $thisParent;
                    if ( ! empty( $thisParent ) ) {
                        while ( ! empty( $thisParent ) && $thisParent != '1' ) {
                            $sql = $wpdb->prepare( "SELECT parent FROM wp_gpxRegion WHERE id=%d", $thisParent );
                            $thisParent = $wpdb->get_var( $sql );
                            $propRegionParentIDs[ $prop->ResortID ][] = $thisParent;
                        }
                    }
                }

                //date - resort groups
                $rdgp = $prop->ResortID . strtotime( $prop->checkIn );
                $resortDates[ $rdgp ] = [
                    'ResortID' => $prop->ResortID,
                    'checkIn' => date( 'Y-m-d', strtotime( $prop->checkIn ) ),
                    'propRegionParentIDs' => $propRegionParentIDs[ $prop->ResortID ],
                ];
            }

            foreach ( $resortDates as $rdK => $rdV ) {
                $placeholders = gpx_db_placeholders( $rdV['propRegionParentIDs'], '%d' );
                $values = $rdV['propRegionParentIDs'];
                array_unshift( $values, $rdV['ResortID'] );
                $values[] = $rdV['checkIn'];
                $values[] = $todayDT;
                $values[] = $todayDT;

                $sql = $wpdb->prepare( "SELECT a.id, a.Name, a.Properties, a.Amount, a.SpecUsage, a.TravelStartDate, a.TravelEndDate
                    FROM wp_specials a
                    LEFT JOIN wp_promo_meta b ON b.specialsID=a.id
                    LEFT JOIN wp_resorts c ON c.id=b.foreignID
                    LEFT JOIN wp_gpxRegion d ON d.id=b.foreignID
                    WHERE ((c.ResortID=%s AND b.refTable='wp_resorts') OR (b.reftable = 'wp_gpxRegion' AND d.id IN ({$placeholders})))
                    AND Type='promo'
                    AND %s BETWEEN TravelStartDate AND TravelEndDate
                    AND (StartDate <= %s AND EndDate >= %s)
                    AND a.Active=1
                    GROUP BY a.id",
                                       $values );
                $nextRows = $wpdb->get_results( $sql );
                $specRows[ $rdK ] = array_merge( (array) $firstRows, (array) $nextRows );
            }

            foreach ( $specRows as $spK => $spV ) {
                $row = (object) $spV;

                $specialMeta = stripslashes_deep( json_decode( $row->Properties ) );

                if ( isset( $specialMeta->usage_region ) && ! empty( $specialMeta->usage_region ) ) {
                    $usage_regions = json_decode( $specialMeta->usage_region );

                    foreach ( $usage_regions as $usage_region ) {
                        $sql = $wpdb->prepare( "SELECT lft, rght FROM wp_gpxRegion WHERE id=%d", $usage_region );
                        $excludeLftRght = $wpdb->get_row( $sql );
                        $excleft = $excludeLftRght->lft;
                        $excright = $excludeLftRght->rght;
                        $sql = $wpdb->prepare( "SELECT id FROM wp_gpxRegion WHERE lft >= %d AND rght <= %d",
                                               [ $excleft, $excright ] );
                        $usageregions = $wpdb->get_results( $sql );
                        if ( ! empty( $usageregions ) ) {
                            foreach ( $usageregions as $usageregion ) {
                                $uregionsAr[ $spK ][] = $usageregion->id;
                            }
                        }
                    }
                }

                if ( isset( $specialMeta->exclude_region ) && ! empty( $specialMeta->exclude_region ) ) {
                    $exclude_regions = json_decode( $specialMeta->exclude_region );
                    foreach ( $exclude_regions as $exclude_region ) {
                        $sql = $wpdb->prepare( "SELECT lft, rght FROM wp_gpxRegion WHERE id=%d", $exclude_region );
                        $excludeLftRght = $wpdb->get_row( $sql );
                        $excleft = $excludeLftRght->lft;
                        $excright = $excludeLftRght->rght;
                        $sql = $wpdb->prepare( "SELECT * FROM wp_gpxRegion WHERE lft >= %d AND rght <= %d",
                                               [ $excleft, $excright ] );
                        $excregions[ $spK ] = $wpdb->get_results( $sql );
                    }
                }
            }

            //we only need to grab these resort metas
            $whichMetas = [
                'ExchangeFeeAmount',
                'RentalFeeAmount',
                'images',
            ];
            $rmFees = [
                'ExchangeFeeAmount',
                'RentalFeeAmount',
            ];

            // store $resortMetas as array
            $placeholders = gpx_db_placeholders( $theseResorts, '%d' );
            $sql = $wpdb->prepare( "SELECT * FROM wp_resorts_meta WHERE ResortID IN ({$placeholders}) AND meta_key IN ('ExchangeFeeAmount', 'RentalFeeAmount', 'images')",
                                   $theseResorts );
            $query = $wpdb->get_results( $sql, ARRAY_A );

            foreach ( $query as $thisk => $thisrow ) {
                $current['rmk'] = $thisrow['meta_key'];
                $current['rmv'] = json_decode( $thisrow['meta_value'], true );
                $current['rid'] = $thisrow['ResortID'];

                $resortMetas[ $current['rid'] ][ $current['rmk'] ] = $current['rmv'];

                //fees
                if ( in_array( $current['rmk'], $rmFees ) ) {
                    $rmFeeData = $current['rmv'];
                    $thisRMFees = [];
                    foreach ( $rmFeeData as $rmDate => $rmFee ) {
                        switch ( $current['rmk'] ) {
                            case 'ExchangeFeeAmount':
                                $thisFeeType = 'ExchangeWeek';
                                break;

                            case 'RentalFeeAmount':
                                $thisFeeType = 'RentalWeek';
                                break;

                            default:
                                $thisFeeType = 'ExchangeWeek';
                                break;
                        }
                        $thisRMFees[] = [
                            'date' => $rmDate,
                            'type' => $thisFeeType,
                            'fee' => $rmFee,
                        ];
                    }
                    $resortMetas[ $current['rid'] ][ $current['rmk'] ] = $thisRMFees;
                }
                // image
                if ( ! empty( $resortMetas[ $current['rid'] ]['images'] ) ) {
                    $resortImages = $resortMetas[ $current['rid'] ]['images'];
                    $oneImage = $resortImages[0];


                    // store items for $prop in ['to_prop'] // extract in loop
                    $resortMetas[ $current['rid'] ]['ImagePath1'] = $oneImage['src'];


                    unset( $resortImages );
                    unset( $oneImage );
                }
            }

            $propKeys = array_keys( $props );
            $pi = 0;
            $ppi = 0;
            while ( $pi < count( $props ) ) {
                $propKey = $propKeys[ $pi ];
                $k = $propKey;
                $prop = $props[ $pi ];

                //skip anything that has an error
                $allErrors = [
                    'checkIn',
                ];
                //if this type is 3 then i't both exchange and rental. Run it as an exchange
                if ( $prop->PID == '47071506' ) {
                    $ppi ++;
                }

                //first we need to set the week type
                //if this type is 3 then it's both exchange and rental. Run it as an exchange
                if ( $prop->WeekType == '1' ) {
                    $prop->WeekType = 'ExchangeWeek';
                } elseif ( $prop->WeekType == '2' ) {
                    $prop->WeekType = 'RentalWeek';
                } else {
                    //a previous loop set this as a rental
                    if ( $prop->forRental ) {
                        $prop->WeekType = 'RentalWeek';
                        $prop->Price = $randexPrice[ $prop->forRental ];
                    } else {
                        //we know for sure this is an exchange week
                        $prop->WeekType = 'ExchangeWeek';
                        $rentalAvailable = false;
                        if ( empty( $prop->active_rental_push_date ) ) {
                            if ( strtotime( $prop->checkIn ) < strtotime( '+ 6 months' ) ) {
                                $retalAvailable = true;
                            }
                        } elseif ( strtotime( 'NOW' ) > strtotime( $prop->active_rental_push_date ) ) {
                            $rentalAvailable = true;
                        }
                        if ( $rentalAvailable ) {
                            $nextCnt = count( $props );
                            $props[ $nextCnt ] = $prop;
                            $props[ $nextCnt ]->forRental = $nextCnt;
                            $props[ $nextCnt ]->Price = $prop->Price;
                            $randexPrice[ $nextCnt ] = $prop->Price;
                        }
                    }
                }
                $alwaysWeekExchange = $prop->WeekType;

                if ( $prop->WeekType == 'ExchangeWeek' ) {
                    $prop->Price = get_option( 'gpx_exchange_fee' );
                }
                $prop->Price = number_format( $prop->Price, 0, '.', '' );
                $prop->WeekPrice = $prop->Price;

                $nextRows = [];

                // extract resort metas to prop -- in this case we are only concerned with the image and week price
                if ( ! empty( $resortMetas[ $prop->ResortID ] ) ) {
                    foreach ( $resortMetas[ $prop->ResortID ] as $current['rmk'] => $current['rmv'] ) {
                        if ( $current['rmk'] == 'ImagePath1' ) {
                            $prop->{$current['rmk']} = $current['rmv'];
                        } else {
                            //reset the resort meta items
                            foreach ( $current['rmv'] as $rmv ) {
                                if ( isset( $rmv['type'] ) && $rmv['type'] == $prop->WeekType ) {
                                    $rmk = $rmv['type'];

                                    $rmdate = $rmv['date'];

                                    $rmvalues = $rmv['fee'];
                                    $thisVal = '';
                                    $rmdates = explode( "_", $rmdate );
                                    if ( count( $rmdates ) == 1 && $rmdates[0] == '0' ) {
                                        //do nothing
                                    } else {
                                        //changing this to go by checkIn instead of the active date
                                        $checkInForRM = strtotime( $prop->checkIn );

                                        //check to see if the from date has started
                                        if ( $rmdates[0] <= $checkInForRM ) {
                                            //this date has started we can keep working
                                        } else {
                                            //these meta items don't need to be used
                                            continue;
                                        }
                                        //check to see if the to date has passed
                                        //                                                 if(isset($rmdates[1]) && ($rmdates[1] >= strtotime("now")))
                                        if ( isset( $rmdates[1] ) && ( $checkInForRM > $rmdates[1] ) ) {
                                            //these meta items don't need to be used
                                            continue;
                                        } else {
                                            //this date is sooner than the end date we can keep working
                                        }
                                        foreach ( $rmvalues as $rmval ) {
                                            //set this amount in the object
                                            $prop->Price = $rmval;
                                            $prop->WeekPrice = $rmval;
                                        }
                                    }
                                } else {
                                    $prop->{$current['rmk']} = $current['rmv'];
                                }
                            }
                        }
                    }
                }

                $pi ++;

                $plural = '';
                $chechbr = strtolower( substr( $prop->bedrooms, 0, 1 ) );
                if ( is_numeric( $chechbr ) ) {
                    $bedtype = $chechbr;
                    if ( $chechbr != 1 ) {
                        $plural = 's';
                    }
                    $bedname = $chechbr . " Bedroom" . $plural;
                } elseif ( $chechbr == 's' ) {
                    $bedtype = 'Studio';
                    $bedname = 'Studio';
                } else {
                    $bedtype = $prop->bedrooms;
                    $bedname = $prop->bedrooms;
                }

                $allBedrooms[ $bedtype ] = $bedname;
                $prop->AllInclusive = '00';
                $resortFacilities = json_decode( $prop->ResortFacilities );
                if ( ( is_array( $resortFacilities ) && in_array( 'All Inclusive',
                                                                  $resortFacilities ) ) || strpos( $prop->HTMLAlertNotes,
                                                                                                   'IMPORTANT: All-Inclusive Information' ) || strpos( $prop->AlertNote,
                                                                                                                                                       'IMPORTANT: This is an All Inclusive (AI) property.' ) ) {
                    $prop->AllInclusive = '6';
                }

                $discount = '';
                $prop->specialPrice = '';
                $rdgp = $prop->ResortID . strtotime( $prop->checkIn );

                $date = $prop->checkIn;

                if ( $specRows[ $rdgp ] ) {
                    foreach ( $specRows[ $rdgp ] as $rowArr ) {
                        $row = (object) $rowArr;

                        //first remove any travel dates that slipped through on the first query
                        if ( $date >= $row->TravelStartDate && $date <= $row->TravelEndDate ) {
                            //we are all good
                        } else {
                            continue;
                        }

                        $specialMeta = stripslashes_deep( json_decode( $row->Properties ) );

                        //if this is an exclusive week then we might need to remove this property
                        if ( isset( $specialMeta->exclusiveWeeks ) && ! empty( $specialMeta->exclusiveWeeks ) ) {
                            $exclusiveWeeks = explode( ',', $specialMeta->exclusiveWeeks );
                            if ( in_array( $prop->weekId, $exclusiveWeeks ) ) {
                                $rmExclusiveWeek[ $prop->weekId ] = $prop->weekId;
                            } else {
                                //this doesn't apply
                                $skip = true;
                                continue;
                            }
                        } // landing page only
                        elseif ( isset( $specialMeta->availability ) && $specialMeta->availability == 'Landing Page' ) {
                            if ( isset( $_COOKIE['lp_promo'] ) && $_COOKIE['lp_promo'] == $row->Slug ) {
                                $returnLink = '<a href="/promotion/' . $row->Slug . '" class="return-link">View All ' . $row->Name . ' Weeks</a>';
                            }
                            //With regards to a 'Landing Page' promo setting...yes, if that is the setup then the discount is only to be presented on that page, otherwise we would set it up as site-wide.
                            $skip = true;
                            continue;
                        }

                        if ( is_array( $specialMeta->transactionType ) ) {
                            $ttArr = $specialMeta->transactionType;
                        } else {
                            $ttArr = [ $specialMeta->transactionType ];
                        }
                        $transactionTypes = [];
                        foreach ( $ttArr as $tt ) {
                            switch ( $tt ) {
                                case 'Upsell':
                                    $transactionTypes['upsell'] = 'Upsell';
                                    break;

                                case 'upsell':
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
                        $ttWeekType = $prop->WeekType;

                        if ( $ttWeekType == 'RentalWeek' && ! in_array( 'any',
                                                                        $transactionTypes ) && ! in_array( $ttWeekType,
                                                                                                           $transactionTypes ) ) {
                            $ttWeekType = 'BonusWeek';
                        }
                        if ( in_array( $ttWeekType, $transactionTypes ) ) {
                            $skip = false;
                            $regionOK = false;
                            /*
                                                     * filter out conditions
                                                     */
                            //upsell only
                            if ( in_array( 'Upsell', $transactionTypes ) && count( $transactionTypes ) == 1 ) {
                                $skip = true;
                                continue;
                            }

                            //blackouts
                            if ( isset( $specialMeta->blackout ) && ! empty( $specialMeta->blackout ) ) {
                                foreach ( $specialMeta->blackout as $blackout ) {
                                    if ( strtotime( $prop->checkIn ) >= strtotime( $blackout->start ) && strtotime( $prop->checkIn ) <= strtotime( $blackout->end ) ) {
                                        $skip = true;
                                        continue;
                                    }
                                }
                            }
                            //resort blackout dates
                            if ( isset( $specialMeta->resortBlackout ) && ! empty( $specialMeta->resortBlackout ) ) {
                                foreach ( $specialMeta->resortBlackout as $resortBlackout ) {
                                    //if this resort is in the resort blackout array then continue looking for the date
                                    if ( in_array( $prop->RID, $resortBlackout->resorts ) ) {
                                        if ( strtotime( $prop->checkIn ) >= strtotime( $resortBlackout->start ) && strtotime( $prop->checkIn ) <= strtotime( $resortBlackout->end ) ) {
                                            $skip = true;
                                        }
                                    }
                                }
                                if ( $skip ) {
                                    continue;
                                }
                            }//resort specific travel dates
                            if ( isset( $specialMeta->resortTravel ) && ! empty( $specialMeta->resortTravel ) ) {
                                foreach ( $specialMeta->resortTravel as $resortTravel ) {
                                    //if this resort is in the resort blackout array then continue looking for the date
                                    if ( in_array( $prop->RID, $resortTravel->resorts ) ) {
                                        if ( strtotime( $prop->checkIn ) >= strtotime( $resortTravel->start ) && strtotime( $prop->checkIn ) <= strtotime( $resortTravel->end ) ) {
                                            //all good
                                        } else {
                                            $skip = true;
                                        }
                                    }
                                }
                                if ( $skip ) {
                                    continue;
                                }
                            }


//                                                     $prop->WeekType = $alwaysWeekExchange;
                            //week min cost
                            if ( isset( $specialMeta->minWeekPrice ) && ! empty( $specialMeta->minWeekPrice ) ) {
                                if ( $prop->WeekType == 'ExchangeWeek' ) {
                                    $skip = true;
                                }

                                if ( $prop->Price < $specialMeta->minWeekPrice ) {
                                    $skip = true;
                                }
                            }
                            if ( ( isset( $specialMeta->beforeLogin ) && $specialMeta->beforeLogin == 'Yes' ) && ! is_user_logged_in() ) {
                                $skip = true;
                            }
                            if ( strpos( $row->SpecUsage, 'customer' ) !== false )//customer specific
                            {
                                if ( isset( $cid ) ) {
                                    $specCust = (array) json_decode( $specialMeta->specificCustomer );
                                    if ( ! in_array( $cid, $specCust ) ) {
                                        $skip = true;
                                    }
                                } else {
                                    $skip = true;
                                }
                                if ( $skip ) {
                                    continue;
                                }
                            }


                            //transaction type
                            if ( in_array( 'ExchangeWeek', $transactionTypes ) || ! in_array( 'BonusWeek',
                                                                                              $transactionTypes ) ) {
                                if ( ! in_array( $prop->WeekType, $transactionTypes ) ) {
                                    $skip = true;
                                    continue;
                                }
                            }
                            //usage region
                            if ( isset( $specialMeta->usage_region ) && ! empty( $specialMeta->usage_region ) && isset( $uregionsAr[ $rdgp ] ) ) {
                                if ( ! in_array( $prop->gpxRegionID, $uregionsAr[ $rdgp ] ) ) {
                                    $skip = true;
                                    continue;
                                } else {
                                    $regionOK = true;
                                }
                            }

                            //usage resort
                            if ( isset( $specialMeta->usage_resort ) && ! empty( $specialMeta->usage_resort ) ) {
                                if ( ! in_array( $prop->RID, $specialMeta->usage_resort ) ) {
                                    if ( isset( $regionOK ) && $regionOK == true )//if we set the region and it applies to this resort then the resort doesn't matter
                                    {
                                        //do nothing
                                    } else {
                                        $skip = true;
                                        continue;
                                    }
                                }
                            }
                            //exclusions

                            //exclude resorts
                            if ( isset( $specialMeta->exclude_resort ) && ! empty( $specialMeta->exclude_resort ) ) {
                                if ( in_array( $prop->RID, $specialMeta->exclude_resort ) ) {
                                    $skip = true;
                                    //break;
                                }
                                if ( $skip ) {
                                    continue;
                                }
                            }

                            //exclude regions
                            if ( isset( $specialMeta->exclude_region ) && ! empty( $specialMeta->exclude_region ) ) {
                                if ( isset( $excregions[ $rdgp ] ) && ! empty( $excregions[ $rdgp ] ) ) {
                                    if ( in_array( $prop->gpxRegionID, $excregions[ $rdgp ] ) ) {
                                        continue;
                                    }
                                }
                            }

                            //exclude home resort
                            if ( isset( $specialMeta->exclusions ) && $specialMeta->exclusions == 'home-resort' ) {
                                if ( isset( $usermeta ) && ! empty( $usermeta ) ) {
                                    $ownresorts = [ 'OwnResort1', 'OwnResort2', 'OwnResort3' ];
                                    foreach ( $ownresorts as $or ) {
                                        if ( isset( $usermeta->$or ) ) {
                                            if ( $usermeta->$or == $prop->ResortName ) {
                                                $skip = true;
                                            }
                                        }
                                    }
                                    if ( $skip ) {
                                        continue;
                                    }
                                }
                            }

                            //lead time
                            $today = date( 'Y-m-d' );
                            if ( isset( $specialMeta->leadTimeMin ) && ! empty( $specialMeta->leadTimeMin ) ) {
                                $ltdate = date( 'Y-m-d',
                                                strtotime( $prop->checkIn . " -" . $specialMeta->leadTimeMin . " days" ) );
                                if ( $today > $ltdate ) {
                                    $skip = true;
                                    continue;
                                }
                            }

                            if ( isset( $specialMeta->leadTimeMax ) && ! empty( $specialMeta->leadTimeMax ) ) {
                                $ltdate = date( 'Y-m-d',
                                                strtotime( $prop->checkIn . " -" . $specialMeta->leadTimeMax . " days" ) );
                                if ( $today < $ltdate ) {
                                    $skip = true;
                                    continue;
                                }
                            }
                            if ( ! $skip ) {
                                $thisDiscounted = '';
                                if ( isset( $rmExclusiveWeek[ $prop->weekId ] ) && ! empty( $rmExclusiveWeek[ $prop->weekId ] ) ) {
                                    unset( $rmExclusiveWeek[ $prop->weekId ] );
                                }
                                $discount = $row->Amount;
                                $discountType = $specialMeta->promoType;
                                if ( $discountType == 'Pct Off' ) {
                                    $thisSpecialPrice = number_format( $prop->Price * ( 1 - ( $discount / 100 ) ),
                                                                       2,
                                                                       '.',
                                                                       '' );
                                    if ( ( isset( $prop->specialPrice ) && ( $thisSpecialPrice < $prop->specialPrice || empty( $prop->specialPrice ) ) ) || empty( $prop->specialPrice ) ) {
                                        $prop->specialPrice = $thisSpecialPrice;
                                        $thisDiscounted = true;
                                    }
                                } elseif ( $discountType == 'Dollar Off' ) {
                                    $thisSpecialPrice = $prop->Price - $discount;
                                    if ( ( isset( $prop->specialPrice ) && ( $thisSpecialPrice < $prop->specialPrice || empty( $prop->specialPrice ) ) ) || ! isset( $prop->specialPrice ) ) {
                                        $prop->specialPrice = $thisSpecialPrice;
                                        $thisDiscounted = true;
                                    }
                                } elseif ( $discount < $prop->Price ) {
                                    $thisSpecialPrice = $discount;
                                    if ( ( isset( $prop->specialPrice ) && ( $thisSpecialPrice < $prop->specialPrice || empty( $prop->specialPrice ) ) ) || ! isset( $prop->specialPrice ) ) {
                                        $prop->specialPrice = $thisSpecialPrice;
                                        $thisDiscounted = true;
                                    }
                                }

                                if ( $prop->specialPrice < 0 ) {
                                    $prop->specialPrice = '0.00';
                                }
                                if ( isset( $specialMeta->icon ) && $thisDiscounted ) {
                                    $prop->specialicon = $specialMeta->icon;
                                }
                                if ( isset( $specialMeta->desc ) && $thisDiscounted ) {
                                    $allDescs[] = $specialMeta->desc;
                                    $prop->specialdesc = $specialMeta->desc;
                                    $prop->specialnum = $row->id;
                                }

                                if ( isset( $specialMeta->stacking ) && $specialMeta->stacking == 'No' && $prop->specialPrice > 0 ) {
                                    //check if this amount is less than the other promos
                                    if ( $discountType == 'Pct Off' ) {
                                        $thisStackPrice = number_format( $prop->Price * ( 1 - ( $discount / 100 ) ),
                                                                         2,
                                                                         ".",
                                                                         "" );
                                        if ( ( isset( $prop->specialPrice ) && $thisStackPrice < $prop->specialPrice ) || ! isset( $prop->specialPrice ) ) {
                                            $stackPrice = $thisStackPrice;
                                        }
                                    } elseif ( $discountType == 'Dollar Off' ) {
                                        $thisStackPrice = $prop->Price - $discount;
                                        if ( ( isset( $prop->specialPrice ) && $thisStackPrice < $prop->specialPrice ) || ! isset( $prop->specialPrice ) ) {
                                            $stackPrice = $thisSpecialPrice;
                                        }
                                    } elseif ( $discount < $prop->Price ) {
                                        $thisStackPrice = $discount;
                                        if ( ( isset( $prop->specialPrice ) && $thisStackPrice < $prop->specialPrice ) || ! isset( $prop->specialPrice ) ) {
                                            $stackPrice = $thisSpecialPrice;
                                        }
                                    }

                                    if ( $stackPrice != 0 && $stackPrice < $prop->specialPrice ) {
                                        $allDescs = [ $specialMeta->desc ];
                                        $prop->specialPrice = $stackPrice;
                                    } else {
                                    }
                                }
                                $prop->special = (object) array_merge( (array) $special, (array) $specialMeta );
                            }
                        }
                    }
                }

                //remove any exclusive weeks
                if ( isset( $rmExclusiveWeek[ $prop->weekId ] ) && ! empty( $rmExclusiveWeek[ $prop->weekId ] ) ) {
                    unset( $props[ $propKey ] );
                    $pi ++;
                    continue;
                }

                //sort the results by date...
                $weekTypeKey = 'b';
                if ( $prop->WeekType == 'ExchangeWeek' ) {
                    $weekTypeKey = 'a';
                }
                $datasort = strtotime( $prop->checkIn ) . '--' . $weekTypeKey . '--' . $prop->PID;

                $prop->propkeyset = $datasort;
                $datasort = str_replace( "--", "", $datasort );

                //need to add the special back in if the previous propkeyset had a special but this one doesn't
                if ( ! isset( $prop->specialPrice ) || ( isset( $prop->SpecialPrice ) && empty( $prop->specialPrice ) ) ) {
                    $prop->specialPrice = $prefPropSetDets[ $datasort ]['specialPrice'];
                    $prop->specialicon = $prefPropSetDets[ $datasort ]['specialicon'];
                    $prop->specialdesc = $prefPropSetDets[ $datasort ]['specialdesc'];
                }

                $propsetspecialprice[ $datasort ] = $prop->specialPrice;
                $prefPropSetDets[ $datasort ]['specialPrice'] = $prop->specialPrice;
                $prefPropSetDets[ $datasort ]['specialicon'] = $prop->specialicon;
                $prefPropSetDets[ $datasort ]['specialdesc'] = $prop->specialdesc;


                $checkFN[] = $prop->gpxRegionID;
                $regions[ $prop->gpxRegionID ] = $prop->gpxRegionID;
                $resorts[ $prop->ResortID ]['resort'] = $prop;
                $resorts[ $prop->ResortID ]['props'][ $datasort ] = $prop;
                $propPrice[ $datasort ] = $prop->WeekPrice;
                $propType[ $datasort ] = $prop->WeekType;
                $calendarRows[] = $prop;
            }
            //add all the extra resorts
            if ( isset( $resortsSql ) ) {
                if ( $resorts ) {
                    $thisSetResorts = array_keys( $resorts );
                    $placeholders = gpx_db_placeholders( $thisSetResorts, '%d' );
                    $moreWhere = $wpdb->prepare( " AND (ResortID NOT IN ({$placeholders})", $thisSetResorts );
                    $resortsSql .= $moreWhere;
                }
                $allResorts = $wpdb->get_results( $resortsSql );
                foreach ( $allResorts as $ar ) {
                    $resorts[ $ar->ResortID ]['resort'] = $ar;
                }
            }
            $newStyle = true;
            $filterNames = [];
            if ( isset( $checkFN ) && ! empty( $checkFN ) ) {
                foreach ( $checkFN as $fn ) {
                    $sql = $wpdb->prepare( "SELECT id, name FROM wp_gpxRegion WHERE id=%d", $fn );
                    $fnRows = $wpdb->get_results( $sql );

                    foreach ( $fnRows as $fnRow ) {
                        if ( $fnRow->name != 'All' ) {
                            $filterNames[ $fnRow->id ] = $fnRow->name;
                        }
                    }
                }
            }
            asort( $filterNames );
        }
    }
    //get a list of restricted gpxRegions
    $restrictIDs = gpx_db()->fetchAllKeyValue( "SELECT r.id, r.id FROM wp_gpxRegion r INNER JOIN wp_gpxRegion ca ON (ca.name = 'Southern Coast (California)') WHERE r.lft BETWEEN ca.lft AND ca.rght" );
    if ( $limitCount > 0 ) {
        foreach ( $resorts as $resort_id => $resort ) {
            // because we pulled double the amount of records we needed earlier we need to limit it to the requested amount.
            $resorts[ $resort_id ]['props'] = array_slice( $resort['props'], 0, $limitCount, true );
        }
    }

    if ( isset( $outputProps ) && $outputProps ) {
        if ( isset( $resorts ) ) {
            if ( ! empty( $calendar ) ) {
                return $calendarRows;
            } else {
                include( 'templates/resort-availability.php' );
            }
        } else {
            $output = '<div style="text-align:center; margin: 30px 20px 40px 20px; "><h3 style="color:#cc0000;">Your search didn\'t return any results</h3><p style="font-size:15px;">Please consider searching a different resort or try again later.</p></div>';
        }

        return $output;
    } else {
        include( 'templates/sc-result.php' );
    }
}

function gpx_insider_week_page_sc() {
    global $wpdb;

    $joinedTbl = map_dae_to_vest_properties();

    $cid = gpx_get_switch_user_cookie();

    if ( isset( $cid ) && ! empty( $cid ) ) {
        $usermeta = (object) array_map( function ( $a ) {
            return $a[0];
        }, get_user_meta( $cid ) );
    }

    $monthstart = date( 'Y-m-t', strtotime( "now" ) );
    $monthend = date( 'Y-m-t', strtotime( "+90 days" ) );
    //temporarily set week price to 399 so that we get results

    $sql = $wpdb->prepare( "SELECT
                    `a`.`record_id` AS `id`, `a`.`check_in_date` AS `checkIn`, `a`.`check_out_date` AS `checkOut`, `a`.`price` AS `Price`,
                    `a`.`record_id` AS `weekID`, `a`.`record_id` AS `weekId`, `a`.`resort` AS `resortId`, `a`.`resort` AS `resortID`,
                    `a`.`availability` AS `StockDisplay`, `a`.`type` AS `WeekType`, DATEDIFF(`a`.`check_out_date`, `a`.`check_in_date`) AS `noNights`,
                    `a`.`active_rental_push_date` AS `active_rental_push_date`,
                    `b`.`Country` AS `Country`, `b`.`Region` AS `Region`, `b`.`Town` AS `Town`, `b`.`ResortName` AS `ResortName`,
                    `b`.`ImagePath1` AS `ImagePath1`, `b`.`AlertNote` AS `AlertNote`, `b`.`AdditionalInfo` AS `AdditionalInfo`,
                    `b`.`HTMLAlertNotes` AS `HTMLAlertNotes`, `b`.`ResortID` AS `ResortID`, `b`.`taxMethod` AS `taxMethod`,
                    `b`.`taxID` AS `taxID`, `b`.`gpxRegionID` AS `gpxRegionID`,
                    `c`.`number_of_bedrooms` AS `bedrooms`, `c`.`sleeps_total` AS `sleeps`, `c`.`name` AS `Size`,
                    `a`.`record_id` AS `PID`, `b`.`id` AS `RID`
                FROM `wp_room` AS `a`
                INNER JOIN `wp_resorts` AS `b` ON `a`.`resort` = `b`.`id`
                INNER JOIN `wp_unit_type` AS `c` ON `a`.`unit_type` = `c`.`record_id`
                WHERE
                    (check_in_date BETWEEN %s AND %s)
                    AND (a.active_rental_push_date < %s)
                    AND type IN (1, 3)
                    AND price BETWEEN 199 and 399
                    AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1",
                           [ $monthstart, $monthend, $monthstart ]
    );
    $props = $wpdb->get_results( $sql );

    if ( isset( $props ) && ! empty( $props ) ) {
        $prop_string = [];
        $new_props = [];
        foreach ( $props as $p ) {
            $week_date_size = $p->resortId . '=' . $p->WeekType . '=' . date( 'm/d/Y',
                                                                              strtotime( $p->checkIn ) ) . '=' . $p->Size;
            if ( ! in_array( $week_date_size, $prop_string ) ) {
                $new_props[] = $p;
            }
            array_push( $prop_string, $week_date_size );
        }

        $count_week_date_size = ( array_count_values( $prop_string ) );

        $props = $new_props;

        $theseResorts = [];
        foreach ( $props as $propK => $prop ) {
            //validate availablity
            if ( $prop->availablity == '2' ) {
                //partners shouldn't see this
                //this should only be available to partners
                $sql = $wpdb->prepare( "SELECT record_id FROM wp_partner WHERE user_id=%d", $cid );
                $row = $wpdb->get_row( $sql );
                if ( ! empty( $row ) ) {
                    unset( $props[ $propK ] );
                    continue;
                }
            }
            if ( $prop->availablity == '3' ) {
                //only partners shouldn't see this
                //this should only be available to partners
                $sql = $wpdb->prepare( "SELECT record_id FROM wp_partner WHERE user_id=%d", $cid );
                $row = $wpdb->get_row( $sql );
                if ( empty( $row ) ) {
                    unset( $props[ $propK ] );
                    continue;
                }
            }

            if ( ! isset( $prop->ResortID ) ) {
                $rSql = $wpdb->prepare( "SELECT ResortID FROM wp_resorts WHERE id=%d", $prop->RID );
                $rRow = $wpdb->get_row( $rSql );
                $prop->ResortID = $rRow->ResortID;
            }

            $string_week_date_size = $prop->resortId . '=' . $prop->WeekType . '=' . date( 'm/d/Y',
                                                                                           strtotime( $prop->checkIn ) ) . '=' . $prop->Size;
            $prop->prop_count = $count_week_date_size[ $string_week_date_size ];

            //set all the resorts that are part of the results
            if ( ! in_array( $prop->ResortID, $theseResorts ) ) {
                $theseResorts[ $prop->ResortID ] = $prop->ResortID;

                //get all ther regions that this property belongs to
                $propRegionParentIDs[ $prop->ResortID ] = [];
                $sql = $wpdb->prepare( "SELECT parent FROM wp_gpxRegion WHERE id=%d", $prop->gpxRegionID );
                $thisParent = $wpdb->get_var( $sql );
                $propRegionParentIDs[ $prop->ResortID ][] = $thisParent;
                if ( ! empty( $thisParent ) ) {
                    while ( ! empty( $thisParent ) && $thisParent != '1' ) {
                        $sql = $wpdb->prepare( "SELECT parent FROM wp_gpxRegion WHERE id=%d", $thisParent );
                        $thisParent = $wpdb->get_var( $sql );
                        $propRegionParentIDs[ $prop->ResortID ][] = $thisParent;
                    }
                }
            }

            //date - resort groups
            $rdgp = $prop->ResortID . strtotime( $prop->checkIn );
            $resortDates[ $rdgp ] = [
                'ResortID' => $prop->ResortID,
                'checkIn' => date( 'Y-m-d', strtotime( $prop->checkIn ) ),
                'propRegionParentIDs' => $propRegionParentIDs[ $prop->ResortID ],
            ];
        }

        foreach ( $resortDates as $rdK => $rdV ) {
            $placeholders = gpx_db_placeholders( $rdV['propRegionParentIDs'], '%d' );
            $values = array_values( $rdV['propRegionParentIDs'] );
            array_unshift( $values, $rdV['ResortID'] );
            $values[] = $rdV['checkIn'];
            $values[] = $todayDT;
            $values[] = $todayDT;
            $sql = $wpdb->prepare( "SELECT a.id, a.Name, a.Properties, a.Amount, a.SpecUsage, a.TravelStartDate, a.TravelEndDate
                			FROM wp_specials a
                            LEFT JOIN wp_promo_meta b ON b.specialsID=a.id
                            LEFT JOIN wp_resorts c ON c.id=b.foreignID
                            LEFT JOIN wp_gpxRegion d ON d.id=b.foreignID
                            WHERE ((c.ResortID=%s AND b.refTable='wp_resorts') OR(b.reftable = 'wp_gpxRegion' AND d.id IN ({$placeholders})))
                            AND Type='promo'
                            AND %s BETWEEN TravelStartDate AND TravelEndDate
                            AND (StartDate <= %s AND EndDate >= %s)
                            AND a.Active=1
                            GROUP BY a.id",
                                   $values );
            $nextRows = $wpdb->get_results( $sql );
            $specRows[ $rdK ] = array_merge( (array) $firstRows, (array) $nextRows );
        }

        foreach ( $specRows as $spK => $spV ) {
            $row = (object) $spV;

            $specialMeta = stripslashes_deep( json_decode( $row->Properties ) );

            if ( isset( $specialMeta->usage_region ) && ! empty( $specialMeta->usage_region ) ) {
                $usage_regions = json_decode( $specialMeta->usage_region );

                foreach ( $usage_regions as $usage_region ) {
                    $sql = $wpdb->prepare( "SELECT lft, rght FROM wp_gpxRegion WHERE id=%d", $usage_region );
                    $excludeLftRght = $wpdb->get_row( $sql );
                    $excleft = $excludeLftRght->lft;
                    $excright = $excludeLftRght->rght;
                    $sql = $wpdb->prepare( "SELECT id FROM wp_gpxRegion WHERE lft >= %d AND rght <= %d",
                                           [ $excleft, $excright ] );
                    $usageregions = $wpdb->get_results( $sql );
                    if ( ! empty( $usageregions ) ) {
                        foreach ( $usageregions as $usageregion ) {
                            $uregionsAr[ $spK ][] = $usageregion->id;
                        }
                    }
                }
            }

            if ( isset( $specialMeta->exclude_region ) && ! empty( $specialMeta->exclude_region ) ) {
                $exclude_regions = json_decode( $specialMeta->exclude_region );
                foreach ( $exclude_regions as $exclude_region ) {
                    $sql = $wpdb->prepare( "SELECT lft, rght FROM wp_gpxRegion WHERE id=%d", $exclude_region );
                    $excludeLftRght = $wpdb->get_row( $sql );
                    $excleft = $excludeLftRght->lft;
                    $excright = $excludeLftRght->rght;
                    $sql = $wpdb->prepare( "SELECT * FROM wp_gpxRegion WHERE lft >= %d AND rght <= %d",
                                           [ $excleft, $excright ] );
                    $excregions[ $spK ] = $wpdb->get_results( $sql );
                }
            }
        }

        //we only need to grab these resort metas
        $whichMetas = [
            'ExchangeFeeAmount',
            'RentalFeeAmount',
            'images',
        ];
        $rmFees = [
            'ExchangeFeeAmount',
            'RentalFeeAmount',
        ];

        // store $resortMetas as array
        $placeholders = gpx_db_placeholders( $theseResorts, '%d' );
        $sql = $wpdb->prepare( "SELECT * FROM wp_resorts_meta WHERE ResortID IN ({$placeholders}) AND meta_key IN ('ExchangeFeeAmount', 'RentalFeeAmount', 'images')",
                               $theseResorts );
        $query = $wpdb->get_results( $sql, ARRAY_A );

        foreach ( $query as $thisk => $thisrow ) {
            $current['rmk'] = $thisrow['meta_key'];
            $current['rmv'] = $thisrow['meta_value'];
            $current['rid'] = $thisrow['ResortID'];

            $resortMetas[ $current['rid'] ][ $current['rmk'] ] = $current['rmv'];

            //fees
            if ( in_array( $current['rmk'], $rmFees ) ) {
                $rmFeeData = json_decode( $current['rmv'], true );
                $thisRMFees = [];
                foreach ( $rmFeeData as $rmDate => $rmFee ) {
                    switch ( $current['rmk'] ) {
                        case 'ExchangeFeeAmount':
                            $thisFeeType = 'ExchangeWeek';
                            break;

                        case 'RentalFeeAmount':
                            $thisFeeType = 'RentalWeek';
                            break;

                        default:
                            $thisFeeType = 'ExchangeWeek';
                            break;
                    }
                    $thisRMFees[] = [
                        'date' => $rmDate,
                        'type' => $thisFeeType,
                        'fee' => $rmFee,
                    ];
                }
                $resortMetas[ $current['rid'] ][ $current['rmk'] ] = $thisRMFees;
            }
            // image
            if ( ! empty( $resortMetas[ $current['rid'] ]['images'] ) ) {
                $resortImages = json_decode( $resortMetas[ $current['rid'] ]['images'], true );
                $oneImage = $resortImages[0];


                // store items for $prop in ['to_prop'] // extract in loop
                $resortMetas[ $current['rid'] ]['ImagePath1'] = $oneImage['src'];


                unset( $resortImages );
                unset( $oneImage );
            }
        }
        $propKeys = array_keys( $props );
        $pi = 0;
        $datasort = 0;
        while ( $pi < count( $props ) ) {
            $propKey = $propKeys[ $pi ];
            $prop = $props[ $pi ];
            //skip anything that has an error
            $allErrors = [
                'checkIn',
            ];

            foreach ( $allErrors as $ae ) {
                if ( empty( $prop->$ae ) || $prop->$ae == '0000-00-00 00:00:00' ) {
                    continue;
                }
            }
            //if this type is 3 then i't both exchange and rental. Run it as an exchange
            if ( $prop->WeekType == '1' ) {
                $prop->WeekType = 'ExchangeWeek';
            } elseif ( $prop->WeekType == '2' ) {
                $prop->WeekType = 'RentalWeek';
            } else {
                if ( $prop->forRental ) {
                    $prop->WeekType = 'RentalWeek';
                    $prop->Price = $randexPrice[ $prop->forRental ];
                } else {
                    $rentalAvailable = false;
                    if ( empty( $prop->active_rental_push_date ) ) {
                        if ( strtotime( $prop->checkIn ) < strtotime( '+ 6 months' ) ) {
                            $retalAvailable = true;
                        }
                    } elseif ( strtotime( 'NOW' ) > strtotime( $prop->active_rental_push_date ) ) {
                        $rentalAvailable = true;
                    }
                    if ( $rentalAvailable ) {
                        $nextCnt = count( $props );
                        $props[ $nextCnt ] = $props[ $propKey ];
                        $props[ $nextCnt ]->forRental = $nextCnt;
                        $props[ $nextCnt ]->Price = $prop->Price;
                        $randexPrice[ $nextCnt ] = $prop->Price;
                        //                                     $propKeys[] = $rPropKey;
                    }
                    $prop->WeekType = 'ExchangeWeek';
                }
            }

            if ( $prop->WeekType == 'ExchangeWeek' ) {
//                         $prop->Price = get_option('gpx_exchange_fee');
                //we can't have exchange weeks for insider weeks
                $pi ++;
                continue;
            }

            $prop->WeekPrice = $prop->Price;
            //if($prop->WeekType == 'RentalWeek' && ($prop->OwnerBusCatCode == 'GPX' && $prop->StockDisplay == 'DAE') || ($prop-OwnerBusCatCode == 'USA GPX' && $prop->StockDisplay == 'USA DAE'))
            if ( $prop->WeekType == 'RentalWeek' && ( ( $prop->OwnerBusCatCode == 'GPX' || $prop->OwnerBusCatCode == 'USA GPX' ) && ( $prop->StockDisplay == 'DAE' || $prop->StockDisplay == 'USA DAE' ) ) || ( ( $prop - OwnerBusCatCode == 'GPX' || $prop - OwnerBusCatCode == 'USA GPX' ) && ( $prop->StockDisplay == 'DAE' || $prop->StockDisplay == 'USA DAE' ) ) ) {
                unset( $prop );
                continue;
            }

            // extract resort metas to prop -- in this case we are only concerned with the image and week price
            if ( ! empty( $resortMetas[ $prop->ResortID ] ) ) {
                foreach ( $resortMetas[ $prop->ResortID ] as $current['rmk'] => $current['rmv'] ) {
                    if ( $current['rmk'] == 'ImagePath1' ) {
                        $prop->{$current['rmk']} = $current['rmv'];
                    } else {
                        //reset the resort meta items

                        foreach ( $current['rmv'] as $rmv ) {
                            if ( isset( $rmv['type'] ) && $rmv['type'] == $prop->WeekType ) {
                                $rmk = $rmv['type'];

                                $rmdate = $rmv['date'];

                                $rmvalues = $rmv['fee'];
                                $thisVal = '';
                                $rmdates = explode( "_", $rmdate );
                                if ( count( $rmdates ) == 1 && $rmdates[0] == '0' ) {
                                    //do nothing
                                } else {
                                    //changing this to go by checkIn instead of the active date
                                    $checkInForRM = strtotime( $prop->checkIn );

                                    //check to see if the from date has started
                                    if ( $rmdates[0] <= $checkInForRM ) {
                                        //this date has started we can keep working
                                    } else {
                                        //these meta items don't need to be used
                                        continue;
                                    }
                                    //check to see if the to date has passed
                                    //                                                 if(isset($rmdates[1]) && ($rmdates[1] >= strtotime("now")))
                                    if ( isset( $rmdates[1] ) && ( $checkInForRM > $rmdates[1] ) ) {
                                        //these meta items don't need to be used
                                        continue;
                                    } else {
                                        //this date is sooner than the end date we can keep working
                                    }
                                    foreach ( $rmvalues as $rmval ) {
                                        //set this amount in the object
                                        $prop->Price = $rmval;
                                        $prop->WeekPrice = $rmval;
                                    }
                                }
                            } else {
                                $prop->{$current['rmk']} = $current['rmv'];
                            }
                        }
                    }
                }
            }

            $pi ++;

            $plural = '';
            $chechbr = strtolower( substr( $prop->bedrooms, 0, 1 ) );
            if ( is_numeric( $chechbr ) ) {
                $bedtype = $chechbr;
                if ( $chechbr != 1 ) {
                    $plural = 's';
                }
                $bedname = $chechbr . " Bedroom" . $plural;
            } elseif ( $chechbr == 's' ) {
                $bedtype = 'Studio';
                $bedname = 'Studio';
            } else {
                $bedtype = $prop->bedrooms;
                $bedname = $prop->bedrooms;
            }

            $allBedrooms[ $bedtype ] = $bedname;
            $prop->AllInclusive = '';
            $resortFacilities = json_decode( $prop->ResortFacilities );
            if ( in_array( 'All Inclusive', $resortFacilities ) || strpos( $prop->HTMLAlertNotes,
                                                                           'IMPORTANT: All-Inclusive Information' ) || strpos( $prop->AlertNote,
                                                                                                                               'IMPORTANT: This is an All Inclusive (AI) property.' ) ) {
                unset( $prop );
                continue;
                $prop->AllInclusive = '6';
            }

            $priceint = preg_replace( "/[^0-9\.]/", "", $prop->WeekPrice );
            if ( $priceint != $prop->Price ) {
                $prop->Price = $priceint;
            }

            $discount = '';
            $prop->specialPrice = '';
            $rdgp = $prop->ResortID . strtotime( $prop->checkIn );

            $date = $prop->checkIn;

            if ( $specRows[ $rdgp ] ) {
                foreach ( $specRows[ $rdgp ] as $rowArr ) {
                    $row = (object) $rowArr;
                    $specialMeta = stripslashes_deep( json_decode( $row->Properties ) );

                    //if this is an exclusive week then we might need to remove this property
                    if ( isset( $specialMeta->exclusiveWeeks ) && ! empty( $specialMeta->exclusiveWeeks ) ) {
                        $exclusiveWeeks = explode( ',', $specialMeta->exclusiveWeeks );
                        if ( in_array( $prop->weekId, $exclusiveWeeks ) ) {
                            $rmExclusiveWeek[ $prop->weekId ] = $prop->weekId;
                        } else {
                            //this doesn't apply
                            $skip = true;
                            continue;
                        }
                    }

                    if ( is_array( $specialMeta->transactionType ) ) {
                        $ttArr = $specialMeta->transactionType;
                    } else {
                        $ttArr = [ $specialMeta->transactionType ];
                    }
                    $transactionTypes = [];
                    foreach ( $ttArr as $tt ) {
                        switch ( $tt ) {
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
                    if ( $ttWeekType == 'RentalWeek' && $transactionType != 'Upsell' && ! in_array( 'all',
                                                                                                    $transactionTypes ) ) {
                        $ttWeekType = 'BonusWeek';
                    }
                    if ( $row->Amount > $discount && in_array( $ttWeekType, $transactionTypes ) ) {
                        $skip = false;
                        $regionOK = false;
                        /*
                                                 * filter out conditions
                                                 */

                        // landing page only
                        if ( isset( $specialMeta->availability ) && $specialMeta->availability == 'Landing Page' ) {
                            if ( isset( $_COOKIE['lp_promo'] ) && $_COOKIE['lp_promo'] == $row->Slug ) {
                                // all good
                                $returnLink = '<a href="/promotion/' . $row->Slug . '" class="return-link">View All ' . $row->Name . ' Weeks</a>';
                            }
                            //With regards to a 'Landing Page' promo setting...yes, if that is the setup then the discount is only to be presented on that page, otherwise we would set it up as site-wide.
                            $skip = true;
                        }
                        //blackouts
                        if ( isset( $specialMeta->blackout ) && ! empty( $specialMeta->blackout ) ) {
                            foreach ( $specialMeta->blackout as $blackout ) {
                                if ( strtotime( $prop->checkIn ) >= strtotime( $blackout->start ) && strtotime( $prop->checkIn ) <= strtotime( $blackout->end ) ) {
                                    $skip = true;
                                }
                            }
                        }
                        //resort blackout dates
                        if ( isset( $specialMeta->resortBlackout ) && ! empty( $specialMeta->resortBlackout ) ) {
                            foreach ( $specialMeta->resortBlackout as $resortBlackout ) {
                                //if this resort is in the resort blackout array then continue looking for the date
                                if ( in_array( $prop->RID, $resortBlackout->resorts ) ) {
                                    if ( strtotime( $prop->checkIn ) >= strtotime( $resortBlackout->start ) && strtotime( $prop->checkIn ) <= strtotime( $resortBlackout->end ) ) {
                                        $skip = true;
                                    }
                                }
                            }
                        }
                        //resort specific travel dates
                        if ( isset( $specialMeta->resortTravel ) && ! empty( $specialMeta->resortTravel ) ) {
                            foreach ( $specialMeta->resortTravel as $resortTravel ) {
                                //if this resort is in the resort travel array then continue looking for the date
                                if ( in_array( $prop->RID, $resortTravel->resorts ) ) {
                                    if ( strtotime( $prop->checkIn ) >= strtotime( $resortTravel->start ) && strtotime( $prop->checkIn ) <= strtotime( $resortTravel->end ) ) {
                                        //all good
                                    } else {
                                        $skip = true;
                                    }
                                }
                            }
                        }

                        if ( ( isset( $specialMeta->beforeLogin ) && $specialMeta->beforeLogin == 'Yes' ) && ! is_user_logged_in() ) {
                            $skip = true;
                        }
                        if ( strpos( $row->SpecUsage, 'customer' ) !== false )//customer specific
                        {
                            if ( isset( $cid ) ) {
                                $specCust = (array) json_decode( $specialMeta->specificCustomer );
                                if ( ! in_array( $cid, $specCust ) ) {
                                    $skip = true;
                                }
                            } else {
                                $skip = true;
                            }
                        }
                        //transaction type
                        if ( in_array( 'ExchangeWeek', $transactionType ) || ! in_array( 'BonusWeek',
                                                                                         $transactionTypes ) ) {
                            if ( ! in_array( $prop->WeekType, $transactionTypes ) ) {
                                $skip = true;
                            }
                        }

                        //usage region

                        //usage region
                        if ( isset( $specialMeta->usage_region ) && ! empty( $specialMeta->usage_region ) && isset( $uregionsAr[ $rdgp ] ) ) {
                            if ( ! in_array( $prop->gpxRegionID, $uregionsAr[ $rdgp ] ) ) {
                                $skip = true;
                                continue;
                            } else {
                                $regionOK = true;
                            }
                        }

                        //usage resort
                        if ( isset( $specialMeta->usage_resort ) && ! empty( $specialMeta->usage_resort ) ) {
                            if ( ! in_array( $prop->RID, $specialMeta->usage_resort ) ) {
                                if ( isset( $regionOK ) && $regionOK == true )//if we set the region and it applies to this resort then the resort doesn't matter
                                {
                                    //do nothing
                                } else {
                                    $skip = true;
                                }
                            }
                        }

                        if ( isset( $specialMeta->minWeekPrice ) && ! empty( $specialMeta->minWeekPrice ) ) {
                            if ( $prop->WeekType == 'ExchangeWeek' ) {
                                $skip = true;
                            }

                            if ( $prop->Price < $specialMeta->minWeekPrice ) {
                                $skip = true;
                            }
                        }

                        //exclusions

                        //exclude resorts
                        if ( isset( $specialMeta->exclude_resort ) && ! empty( $specialMeta->exclude_resort ) ) {
                            if ( in_array( $prop->RID, $specialMeta->exclude_resort ) ) {
                                $skip = true;
                                break;
                            }
                        }

                        //exclude regions
                        if ( isset( $specialMeta->exclude_region ) && ! empty( $specialMeta->exclude_region ) ) {
                            if ( isset( $excregions[ $rdgp ] ) && ! empty( $excregions[ $rdgp ] ) ) {
                                if ( in_array( $prop->gpxRegionID, $excregions[ $rdgp ] ) ) {
                                    continue;
                                }
                            }
                        }

                        //exclude home resort
                        if ( isset( $specialMeta->exclusions ) && $specialMeta->exclusions == 'home-resort' ) {
                            if ( isset( $usermeta ) && ! empty( $usermeta ) ) {
                                $ownresorts = [ 'OwnResort1', 'OwnResort2', 'OwnResort3' ];
                                foreach ( $ownresorts as $or ) {
                                    if ( isset( $usermeta->$or ) ) {
                                        if ( $usermeta->$or == $prop->ResortName ) {
                                            $skip = true;
                                        }
                                    }
                                }
                            }
                        }

                        //lead time
                        $today = date( 'Y-m-d' );
                        if ( isset( $specialMeta->leadTimeMin ) && ! empty( $specialMeta->leadTimeMin ) ) {
                            $ltdate = date( 'Y-m-d',
                                            strtotime( $prop->checkIn . " -" . $specialMeta->leadTimeMin . " days" ) );
                            if ( $today > $ltdate ) {
                                $skip = true;
                            }
                        }

                        if ( isset( $specialMeta->leadTimeMax ) && ! empty( $specialMeta->leadTimeMax ) ) {
                            $ltdate = date( 'Y-m-d',
                                            strtotime( $prop->checkIn . " -" . $specialMeta->leadTimeMax . " days" ) );
                            if ( $today < $ltdate ) {
                                $skip = true;
                            }
                        }


                        if ( ! $skip ) {
                            if ( isset( $rmExclusiveWeek[ $prop->weekId ] ) && ! empty( $rmExclusiveWeek[ $prop->weekId ] ) ) {
                                unset( $rmExclusiveWeek[ $prop->weekId ] );
                            }
                            $discount = $row->Amount;
                            $discountType = $specialMeta->promoType;
                            if ( $discountType == 'Pct Off' ) {
                                $prop->specialPrice = number_format( $prop->Price * ( 1 - ( $discount / 100 ) ), 2 );
                            } elseif ( $discountType == 'Dollar Off' ) {
                                $prop->specialPrice = $prop->Price - $discount;
                            } elseif ( $discount < $prop->Price ) {
                                $prop->specialPrice = $discount;
                            }
                            if ( $prop->specialPrice < 0 ) {
                                $prop->specialPrice = '0.00';
                            }
                            if ( isset( $specialMeta->icon ) ) {
                                $prop->specialicon = $specialMeta->icon;
                            }
                            if ( isset( $specialMeta->desc ) ) {
                                $prop->specialdesc = $specialMeta->desc;
                            }
                            $prop->special = (object) array_merge( (array) $special, (array) $specialMeta );
                        }
                    }
                }
            }
            //remove any exclusive weeks
            if ( isset( $rmExclusiveWeek[ $prop->weekId ] ) && ! empty( $rmExclusiveWeek[ $prop->weekId ] ) ) {
                unset( $props[ $propKey ] );
                continue;
            }

            $datasort = strtotime( $prop->checkIn ) . '--' . $weekTypeKey . '--' . $prop->PID;

            $prop->propkeyset = $datasort;
            $datasort = str_replace( "--", "", $datasort );

            //need to add the special back in if the previous propkeyset had a special but this one doesn't
            if ( ! isset( $prop->specialPrice ) || ( isset( $prop->SpecialPrice ) && empty( $prop->specialPrice ) ) ) {
                $prop->specialPrice = $prefPropSetDets[ $datasort ]['specialPrice'];
                $prop->specialicon = $prefPropSetDets[ $datasort ]['specialicon'];
                $prop->specialdesc = $prefPropSetDets[ $datasort ]['specialdesc'];
            }

            $propsetspecialprice[ $datasort ] = $prop->specialPrice;
            $prefPropSetDets[ $datasort ]['specialPrice'] = $prop->specialPrice;
            $prefPropSetDets[ $datasort ]['specialicon'] = $prop->specialicon;
            $prefPropSetDets[ $datasort ]['specialdesc'] = $prop->specialdesc;
            $checkFN[] = $prop->gpxRegionID;
            $regions[ $prop->gpxRegionID ] = $prop->gpxRegionID;
            $resorts[ $prop->ResortID ]['resort'] = $prop;
            $resorts[ $prop->ResortID ]['props'][ $datasort ] = $prop;
            $propPrice[ $datasort ] = $prop->WeekPrice;
        }
        $filterNames = ! empty( $checkFN ) ? gpx_db()->fetchAllKeyValue( "SELECT id, name FROM wp_gpxRegion WHERE id IN (?) AND name != 'All' ORDER BY name",
                                                                         [ $checkFN ],
                                                                         [ Connection::PARAM_INT_ARRAY ] ) : [];
    }

    if ( isset( $resorts ) && isset( $_SESSION['searchSessionID'] ) ) {
        $savesearch = save_search( $usermeta, $_POST, 'search', $resorts );
    }

    //get a list of restricted gpxRegions
    $restrictIDs = gpx_db()->fetchAllKeyValue( "SELECT r.id, r.id FROM wp_gpxRegion r INNER JOIN wp_gpxRegion ca ON (ca.name = 'Southern Coast (California)') WHERE r.lft BETWEEN ca.lft AND ca.rght" );
    include( 'templates/sc-result.php' );
}

add_shortcode( 'gpx_result_page', 'gpx_result_page_sc' );

add_shortcode( 'gpx_insider_week_page', 'gpx_insider_week_page_sc' );

function gpx_resort_result_page_sc() {
    global $wpdb;
    if ( isset( $_GET['select_region'] ) ) {
        $sql = $wpdb->prepare( "SELECT name, lft, rght, id  FROM wp_gpxRegion WHERE id=%d", $_GET['select_region'] );
    } elseif ( isset( $_GET['select_country'] ) ) {
        $sql = $wpdb->prepare(
            "SELECT MIN(lft) as lft, MAX(rght) as rght, a.id FROM wp_gpxRegion a
                INNER JOIN wp_daeRegion b ON b.id=a.RegionID
                WHERE b.CountryID=%s",
            $_GET['select_country']
        );
    } else {
        $sql = "SELECT MIN(lft) as lft, MAX(rght) as rght FROM wp_gpxRegion";
    }
    $row = $wpdb->get_row( $sql );
    $left = $row->lft;
    $sql = $wpdb->prepare( "SELECT a.*, b.lft, b.rght, name FROM wp_resorts a
        INNER JOIN wp_gpxRegion b ON b.id = a.gpxRegionID
        WHERE b.lft BETWEEN %d AND %d AND a.active=1",
                           [ $row->lft, $row->rght ] );
    $results = $wpdb->get_results( $sql );

    foreach ( $results as $result ) {
        $subregion = [];
        $weektypes = [ '', 'null', 'All' ];
        $parentLft = '';
        $filterCities[ $result->gpxRegionID ] = $result->name;
        $sql = $wpdb->prepare( "SELECT type FROM wp_room WHERE resort=%s AND active='1'", $result->ResortID );
        $rows = $wpdb->get_results( $sql );
        $result->propCount = count( $rows );

        foreach ( $rows as $row ) {
            $weektypes[ $row->WeekType ] = $row->WeekType;
        }
        $result->WeekType = $weektypes;
        $result->ResortType = json_encode( $weektypes );
        $subregion[] = $result->gpxRegionID;
        while ( $parentLft > $left ) {
            $sql = $wpdb->prepare( "SELECT id, lft, name FROM wp_gpxRegion WHERE id=%d", $left );
            $srow = $wpdb->get_row( $sql );
            $subregion[] = $srow->id;
            $parentLft = $row->lft;
            $filterCities[ $srow->id ] = $srow->name;
        }
        $result->SubRegion = json_encode( $subregion );
        asort( $filterCities );
        $result->filterCities = json_encode( $filterCities );
    }
    $resorts = $results;

    usort( $resorts, fn( $a, $b ) => $a->propCount <=> $b->propCount );

    $cid = gpx_get_switch_user_cookie();

    include( 'templates/sc-resort-result.php' );
}

add_shortcode( 'gpx_resort_result_page', 'gpx_resort_result_page_sc' );

function gpx_resort_availability() {
    $destination = $_REQUEST['resortid'];
    $paginate = [
        'limitstart' => $_REQUEST['limitstart'] ?? 0,
        'limitcount' => $_REQUEST['limitcount'] ?? 10000,
    ];
    $html = gpx_result_page_sc( $destination, $paginate );

    $return = [ 'html' => $html ];

    wp_send_json( $return );
}

add_action( "wp_ajax_gpx_resort_availability", "gpx_resort_availability" );
add_action( "wp_ajax_nopriv_gpx_resort_availability", "gpx_resort_availability" );

/**
 * GPX Promo Page Shortcode
 *
 * Displays promo page results
 * Uses url to create a varable. The variable is used to query the wp_specials table to retrieve the promo.
 * Then we retreive all of the inventory that could apply based on a basic inventory query followed by filtering the
 * results based on conditions established when the promo is created.
 * return html
 */
function gpx_promo_page_sc() {
    global $wpdb;

    $tstart = time();


    $baseExchangePrice = get_option( 'gpx_exchange_fee' );

    $joinedTbl = map_dae_to_vest_properties();

    //are there exlusive weeks that we need to take into account?
    $sql = "SELECT Properties FROM wp_specials WHERE active=1";
    $results = $wpdb->get_results( $sql );
    foreach ( $results as $result ) {
        $sm = stripslashes_deep( json_decode( $result->Properties ) );
        if ( ! empty( $sm->exclusiveWeeks ) ) {
            $exp = explode( ",", $sm->exclusiveWeeks );
            foreach ( $exp as $ex ) {
                $exrmExclusiveWeek[ $ex ] = $ex;
            }
        }
    }
    $cid = gpx_get_switch_user_cookie();
    if ( isset( $cid ) && ! empty( $cid ) ) {
        $usermeta = (object) array_map( function ( $a ) {
            return $a[0];
        }, get_user_meta( $cid ) );
    }

    if ( ! empty( $featuredprops ) ) {
        foreach ( $featuredprops as $featuredprop ) {
            $featuredresorts[ $featuredprop->ResortID ]['resort'] = $featuredprop;
            $featuredresorts[ $featuredprop->ResortID ]['props'][] = $featuredprop;
        }
    }
    $todayDT = date( "Y-m-d 00:00:00" );
    $promo = get_query_var( 'promo' );
    if ( ! empty( $promo ) ) {
        //check to see if this is a master promo
        $sql = $wpdb->prepare( "SELECT id FROM wp_specials WHERE Slug=%s AND active=1", $promo );
        $ismaster = $wpdb->get_row( $sql );
        $frommasters = [];
        if ( $ismaster ) {
            $sql = $wpdb->prepare( "SELECT * FROM wp_specials b WHERE master=%d and b.Active=1",
                                   $ismaster->id );
            $frommasters = $wpdb->get_results( $sql );
        }
        if ( count( $frommasters ) > 0 ) {
            $sql = $wpdb->prepare( "SELECT * FROM wp_specials b WHERE master=%d OR b.Slug=%s AND b.Active=1",
                                   [ $ismaster->id, $promo ] );
        } else {
            $sql = $wpdb->prepare( "SELECT * FROM wp_specials b WHERE b.Slug=%s AND b.Active=1", $promo );
        }
    } else {
        //let set the date so far in the past that no promo will apply
        $todayDT = '1899-01-01';
        $sql = $wpdb->prepare( "SELECT * FROM wp_specials b
            WHERE b.showIndex='Yes'
            AND (StartDate <= %s AND EndDate >= %s)
            AND b.Active=1",
                               [ $todayDT, $todayDT ] );
    }
    $specials = $wpdb->get_results( $sql );

    $wheres = [];
    $datewheres = [];
    $resorts = [];
    foreach ( $specials as $specialK => $special ) {
        //if this is a coupon then we want to change the promo amount to $0
        if ( strtolower( $special->Type ) == 'coupon' ) {
            $special->Amount = 0;
        }

        $specialMeta = stripslashes_deep( json_decode( $special->Properties ) );


        //is this promo only available on the landing page?
        if ( isset( $specialMeta->availability ) && $specialMeta->availability == 'Landing Page' ) {
            $lpCookie = $special->Slug;
            $lpSPID = $special->id;
        }

        $today = date( 'Y-m-d' );
        $startpromo = date( 'Y-m-d', strtotime( $special->StartDate ) );
        $endpromo = date( 'Y-m-d', strtotime( $special->EndDate ) );
        if ( ( $today <= $endpromo ) && ( $today >= $startpromo ) ) {
            if ( $specialMeta->usage != 'any' ) {
                if ( isset( $specialMeta->usage_region ) && ! empty( $specialMeta->usage_region ) ) {
                    $allRegions = array_values( json_decode( $specialMeta->usage_region ) );
                    $placeholders = gpx_db_placeholders( $allRegions, '%d' );
                    $sql = $wpdb->prepare( "SELECT name, lft, rght FROM wp_gpxRegion WHERE id IN ($placeholders)",
                                           $allRegions );
                    $ranges = $wpdb->get_results( $sql );
                    if ( ! empty( $ranges ) ) {
                        foreach ( $ranges as $range ) {
                            $sql = $wpdb->prepare( "SELECT id FROM wp_gpxRegion
                                                WHERE lft BETWEEN %d AND %d
                                                ORDER BY lft ASC",
                                                   [ $range->lft, $range->rght ] );
                            $rows = $wpdb->get_results( $sql );
                            foreach ( $rows as $row ) {
                                $wheres[ $special->id ][] = $wpdb->prepare( "b.GPXRegionID=%s", $row->id );
                            }
                        }
                    }
                }
                //usage resort
                if ( isset( $specialMeta->usage_resort ) && ! empty( $specialMeta->usage_resort ) ) {
                    if ( $specialMeta->usage_resort && is_string( $specialMeta->usage_resort ) ) {
                        $usageResorts = json_decode( $specialMeta->usage_resort );
                    } else {
                        $usageResorts = $specialMeta->usage_resort;
                    }
                    if ( empty( $useageResorts ) ) {
                        $usageResorts = $specialMeta->usage_resort;
                    }
                    foreach ( $usageResorts as $usageResort ) {
                        $wheres[ $special->id ][] = $wpdb->prepare( "b.id=%s", $usageResort );
                    }
                }
            }

            if ( isset( $specialMeta->travelStartDate ) && ! empty( $specialMeta->travelStartDate ) ) {
                $start = date( 'Y-m-d', strtotime( $specialMeta->travelStartDate ) );
                $end = date( 'Y-m-d', strtotime( $specialMeta->travelEndDate ) );
                $datewheres[ $special->id ] = $wpdb->prepare( " AND (check_in_date BETWEEN %s AND %s)",
                                                              [ $start, $end ] );
            }

            $discount[ $special->id ] = $special->Amount;

            //only pull the specific transaction type

            //swicth the transaction type and upsell options between the original text and new array
            if ( is_array( $specialMeta->transactionType ) ) {
                $ttArr = $specialMeta->transactionType;
            } else {
                $ttArr = [ $specialMeta->transactionType ];
            }
            if ( is_array( $specialMeta->upsellOptions ) ) {
                $uoArr = $specialMeta->upsellOptions;
            } else {
                $uoArr = [ $specialMeta->upsellOptions ];
            }

            foreach ( $ttArr as $tt ) {
                switch ( $tt ) {
                    case 'upsell':
                        $ttWhere[ $special->id ] = '';
                        if ( in_array( 'CPO', $uoArr ) || in_array( 'Upgrade', $uoArr ) ) {
                            $ttWhereArr['exchange'] = " a.type = '1' OR a.type = '3'";
                        }
                        break;
                    case 'All':
                        $ttWhere[ $special->id ] = '';
                        break;
                    case 'any':
                        $ttWhere[ $special->id ] = '';
                        break;
                    case 'ExchangeWeek':
                        $ttWhereArr['exchange'] = " a.type = '1' OR a.type = '3'";
                        break;
                    case 'BonusWeek':
                        $ttWhereArr['bonus'] = " a.type = '2' OR a.type = '3'";
                        break;
                }
            }
            $ttWhere[ $special->id ] = ' ';
        }

        //add the exclude options to the query
        //exclude region
        if ( isset( $specialMeta->exclude_region ) && ! empty( $specialMeta->exclude_region ) ) {
            $allRegions = array_values( json_decode( $specialMeta->exclude_region ) );
            $placeholders = gpx_db_placeholders( $allRegions, '%d' );
            $sql = $wpdb->prepare( "SELECT name, lft, rght FROM wp_gpxRegion WHERE id IN ($placeholders)",
                                   $allRegions );
            $ranges = $wpdb->get_results( $sql );
            if ( ! empty( $ranges ) ) {
                foreach ( $ranges as $range ) {
                    $sql = $wpdb->prepare( "SELECT id FROM wp_gpxRegion WHERE lft BETWEEN %d AND %d ORDER BY lft ASC",
                                           [ $range->lft, $range->rght ] );
                    $rows = $wpdb->get_results( $sql );
                    foreach ( $rows as $row ) {
                        $excludeRegion[ $special->id ][] = $row->id;
                    }
                }
            }
            if ( isset( $excludeRegion[ $special->id ] ) && ! empty( $excludeRegion[ $special->id ] ) ) {
                $placeholders = gpx_db_placeholders( $excludeRegion[ $special->id ], '%s' );
                $whereExcludeRegions[ $special->id ] = $wpdb->prepare( " b.GPXRegionID NOT IN ($placeholders)",
                                                                       array_values( $excludeRegion[ $special->id ] ) );
            }
        }
        //exclude resort
        if ( isset( $specialMeta->exclude_resort ) && ! empty( $specialMeta->exclude_resort ) ) {
            $usageResorts = $specialMeta->exclude_resort;
            foreach ( $usageResorts as $usageResort ) {
                $excludeResorts[ $special->id ][] = $usageResort;
            }
            if ( isset( $excludeResorts[ $special->id ] ) && ! empty( $excludeResorts[ $special->id ] ) ) {
                $placeholders = gpx_db_placeholders( $excludeRegion[ $special->id ], '%s' );
                $whereExcludeResorts[ $special->id ] = $wpdb->prepare( " b.id NOT IN ({$placeholders})",
                                                                       array_values( $excludeRegion[ $special->id ] ) );
            }
        }

        $datewhere = '';
        if ( ! empty( $datewheres[ $special->id ] ) ) {
            $datewhere = $datewheres[ $special->id ];
        }

        // create $specialMeta from Properties
        $specialMeta = stripslashes_deep( json_decode( $special->Properties ) );
        $special->imploded_transtype = implode( '|', $specialMeta->transactionType ); // for matching

        if ( ! empty( $wheres[ $special->id ] ) ) {
            $where = "(" . implode( " OR ", $wheres[ $special->id ] ) . ") " . $datewhere;
        } else {
            $where = preg_replace( '/AND/', "", $datewhere, 1 );
        }

        if ( isset( $whereExcludeRegions[ $special->id ] ) && ! empty( $whereExcludeRegions[ $special->id ] ) ) {
            $where .= " AND" . $whereExcludeRegions[ $special->id ];
        }
        if ( isset( $whereExcludeResorts[ $special->id ] ) && ! empty( $whereExcludeResorts[ $special->id ] ) ) {
            $where .= " AND" . $whereExcludeResorts[ $special->id ];
        }
        if ( isset( $whereDAEExclude[ $special->id ] ) && ! empty( $whereDAEExclude[ $special->id ] ) ) {
            $where .= $whereDAEExclude[ $special->id ];
        }
        $where .= "  AND a.active=1 AND b.active=1";

        $sql = "SELECT
                    " . implode( ', ', $joinedTbl['joinRoom'] ) . ",
                    " . implode( ', ', $joinedTbl['joinResort'] ) . ",
                    " . implode( ', ', $joinedTbl['joinUnit'] ) . ",
                    " . $joinedTbl['roomTable']['alias'] . ".record_id as PID, " . $joinedTbl['resortTable']['alias'] . ".id as RID
                        FROM " . $joinedTbl['roomTable']['table'] . " " . $joinedTbl['roomTable']['alias'] . "
                INNER JOIN " . $joinedTbl['resortTable']['table'] . " " . $joinedTbl['resortTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".resort=" . $joinedTbl['resortTable']['alias'] . ".id
                INNER JOIN " . $joinedTbl['unitTable']['table'] . " " . $joinedTbl['unitTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".unit_type=" . $joinedTbl['unitTable']['alias'] . ".record_id
            WHERE " . $where . "
            AND a.active=1 and b.active=1 AND a.active_rental_push_date != '2030-01-01'
            GROUP BY PID
            ORDER BY featured DESC";
        $props_rows = $wpdb->get_results( $sql );
        $sanity_cnt = 0;
        // MOD: first iteration, convert props_rows to props[$p->resortId] (for removals)
        foreach ( $props_rows as $p ) {
            // lets clear the easy stuff

            // REMOVE unmatched WeekType

            $pwt = [];
            switch ( $p->WeekType ) {
                case '1':
                    $pwt[] = 1;
                    if ( strpos( implode( '|', $specialMeta->transactionType ),
                                 'ExchangeWeek' ) === false && strpos( implode( '|',
                                                                                $specialMeta->transactionType ),
                                                                       'any' ) === false ) {
                        continue 2;
                    }
                    break;

                case '2':
                    $pwt[] = 2;
                    if ( strpos( implode( '|', $specialMeta->transactionType ),
                                 'BonusWeek' ) === false && strpos( implode( '|', $specialMeta->transactionType ),
                                                                    'any' ) === false ) {
                        continue 2;
                    }
                    break;

                default:
                    $pwt[] = 3;
                    break;
            }

            foreach ( $pwt as $weekType ) {
                $p->week_date_size = $p->resortId . '=' . strtotime( $p->checkIn ) . '=' . $weekType . '=' . str_replace( '/',
                                                                                                                          '',
                                                                                                                          $p->Size );
                $pCnt[ $p->week_date_size ][] = 1;
                $p->prop_count = array_sum( $pCnt[ $p->week_date_size ] );
                $props[ $p->ResortID ][ $p->week_date_size ] = $p;
                $sanity_cnt ++;
            }
            $theseResorts[ $p->ResortID ] = $p->ResortID;
        }

        $whichMetas = [
            'ExchangeFeeAmount',
            'RentalFeeAmount',
            'images',
        ];
        $rmFees = [
            'ExchangeFeeAmount',
            'RentalFeeAmount',
        ];

        // store $resortMetas as array
        if ( ! empty( $theseResorts ) ) {
            $placeholders = gpx_db_placeholders( $theseResorts, '%s' );
            $sql = $wpdb->prepare( "SELECT * FROM wp_resorts_meta WHERE ResortID IN ($placeholders) AND meta_key IN ('ExchangeFeeAmount', 'RentalFeeAmount', 'images')",
                                   array_values( $theseResorts ) );
            $query = $wpdb->get_results( $sql, ARRAY_A );
        } else {
            $query = [];
        }

        foreach ( $query as $thisk => $thisrow ) {
            $current['rmk'] = $thisrow['meta_key'];
            $current['rmv'] = json_decode( $thisrow['meta_value'], true );
            $current['rid'] = $thisrow['ResortID'];

            $resortMetas[ $current['rid'] ][ $current['rmk'] ] = $current['rmv'];

            //fees
            if ( in_array( $current['rmk'], $rmFees ) ) {
                $rmFeeData = $current['rmv'];
                $thisRMFees = [];
                foreach ( $rmFeeData as $rmDate => $rmFee ) {
                    switch ( $current['rmk'] ) {
                        case 'ExchangeFeeAmount':
                            $thisFeeType = 'ExchangeWeek';
                            break;

                        case 'RentalFeeAmount':
                            $thisFeeType = 'RentalWeek';
                            break;

                        default:
                            $thisFeeType = 'ExchangeWeek';
                            break;
                    }
                    $thisRMFees[] = [
                        'date' => $rmDate,
                        'type' => $thisFeeType,
                        'fee' => $rmFee,
                    ];
                }
                $resortMetas[ $current['rid'] ][ $current['rmk'] ] = $thisRMFees;
            }
            // image
            if ( ! empty( $resortMetas[ $current['rid'] ]['images'] ) ) {
                $resortImages = $resortMetas[ $current['rid'] ]['images'];
                $oneImage = $resortImages[0];


                // store items for $prop in ['to_prop'] // extract in loop
                $resortMetas[ $current['rid'] ]['ImagePath1'] = $oneImage['src'];


                unset( $resortImages );
                unset( $oneImage );
            }
        }

        $unsetFilterMost = true;


        // MAIN LOOP

        foreach ( $props as $k => $pv ) {
            ksort( $pv );
            $npv = array_values( $pv );
            $propKeys = array_keys( $npv );

            $pi = 0;
            $ppi = 0;
            $ni = 0;

            while ( $pi < count( $npv ) ) {
                $ni ++;
                $propKey = $propKeys[ $pi ];
                $prop = $npv[ $pi ];
                //first we need to set the week type
                //if this type is 3 then it's both exchange and rental. Run it as an exchange
                if ( $prop->WeekType == '1' ) {
                    $prop->WeekType = 'ExchangeWeek';
                } elseif ( $prop->WeekType == '2' ) {
                    $prop->WeekType = 'RentalWeek';
                } else {
                    if ( $prop->forRental ) {
                        $prop->WeekType = 'RentalWeek';
                        $prop->Price = $randexPrice[ $prop->forRental ];
                    } else {
                        $rentalAvailable = false;
                        if ( empty( $prop->active_rental_push_date ) ) {
                            if ( strtotime( $prop->checkIn ) < strtotime( '+ 6 months' ) ) {
                                $retalAvailable = true;
                            }
                        } elseif ( strtotime( 'NOW' ) > strtotime( $prop->active_rental_push_date ) ) {
                            $rentalAvailable = true;
                        }
                        if ( $rentalAvailable ) {
                            $nextCnt = count( $npv );
                            $npv[ $nextCnt ] = $prop;
                            $npv[ $nextCnt ]->forRental = $nextCnt;
                            $npv[ $nextCnt ]->Price = $prop->Price;
                            $randexPrice[ $nextCnt ] = $prop->Price;
                        }
                        $prop->WeekType = 'ExchangeWeek';
                    }
                }
                // extract resort metas to prop -- in this case we are only concerned with the image and week price
                if ( ! empty( $resortMetas[ $prop->ResortID ] ) ) {
                    foreach ( $resortMetas[ $prop->ResortID ] as $current['rmk'] => $current['rmv'] ) {
                        if ( $current['rmk'] == 'ImagePath1' ) {
                            $prop->{$current['rmk']} = $current['rmv'];
                        } else {
                            //reset the resort meta items
                            if ( isset( $current['rmv'] ) ) {
                                foreach ( $current['rmv'] as $rmv ) {
                                    if ( isset( $rmv['type'] ) && $rmv['type'] == $prop->WeekType ) {
                                        $rmk = $rmv['type'];

                                        $rmdate = $rmv['date'];

                                        $rmvalues = $rmv['fee'];
                                        $thisVal = '';
                                        $rmdates = explode( "_", $rmdate );
                                        if ( count( $rmdates ) == 1 && $rmdates[0] == '0' ) {
                                            //do nothing
                                        } else {
                                            //changing this to go by checkIn instead of the active date
                                            $checkInForRM = strtotime( $prop->checkIn );

                                            //check to see if the from date has started
                                            if ( $rmdates[0] <= $checkInForRM ) {
                                                //this date has started we can keep working
                                            } else {
                                                //these meta items don't need to be used
                                                continue;
                                            }
                                            //check to see if the to date has passed
                                            if ( isset( $rmdates[1] ) && ( $checkInForRM > $rmdates[1] ) ) {
                                                //these meta items don't need to be used
                                                continue;
                                            } else {
                                                //this date is sooner than the end date we can keep working
                                            }
                                            foreach ( $rmvalues as $rmval ) {
                                                //set this amount in the object
                                                $prop->Price = $rmval;
                                                $prop->WeekPrice = $rmval;
                                            }
                                        }
                                    } else {
                                        $prop->{$current['rmk']} = $current['rmv'];
                                    }
                                }
                            }
                        }
                    }
                }


                //skip anything that has an error
                $allErrors = [
                    'checkIn',
                ];


                foreach ( $allErrors as $ae ) {
                    if ( empty( $prop->$ae ) || $prop->$ae == '0000-00-00 00:00:00' ) {
                        $pi ++;
                        continue;
                    }
                }

                if ( $prop->WeekType == 'ExchangeWeek' ) {
                    $prop->Price = $baseExchangePrice;
                }

                $prop->WeekPrice = $prop->Price;
                //if we have a featured resort then we don't want to filter by the number of units
                if ( $prop->featured == 1 ) {
                    $unsetFilterMost = false;
                }


                // do something with $specialMeta

                if ( isset( $specialMeta->exclusiveWeeks ) && ! empty( $specialMeta->exclusiveWeeks ) ) {
                    //if this is an exclusive week then we might need to remove this property
                    if ( in_array( $prop->weekId, $exrmExclusiveWeek ) ) {
                        $exclusiveWeeks = get_exclusive_weeks( $prop, $cid );
                        if ( ! empty( $exclusiveWeeks ) ) {
                            //we returned a result on this week -- we don't need to do anything else becuase it shouldn't be displayed.
                            //                                         unset($props[$k]);
                            $pi ++;
                            continue;
                        }
                    }

                    $exclusiveWeeks = explode( ',', $specialMeta->exclusiveWeeks );
                    if ( in_array( $prop->weekId, $exclusiveWeeks ) ) {
                        $rmExclusiveWeek[ $prop->weekId ] = $prop->weekId;
                    } else {
                        //this doesn't apply
                        unset( $prop );
                        $pi ++;
                        continue;
                    }
                }

                $priceint = preg_replace( "/[^0-9\.]/", "", $prop->WeekPrice );
                if ( $priceint != $prop->Price ) {
                    $prop->Price = $priceint;
                }

                // filter out conditions
                $continue = false;

                //blackouts
                if ( isset( $specialMeta->blackout ) && ! empty( $specialMeta->blackout ) ) {
                    foreach ( $specialMeta->blackout as $blackout ) {
                        if ( strtotime( $prop->checkIn ) >= strtotime( $blackout->start ) && strtotime( $prop->checkIn ) <= strtotime( $blackout->end ) ) {
                            $continue = true;            // ! this is ignored, why is it here?
                            $pi ++;
                            continue;
                        }
                    }
                }
                //resort blackout dates
                if ( isset( $specialMeta->resortBlackout ) && ! empty( $specialMeta->resortBlackout ) ) {
                    foreach ( $specialMeta->resortBlackout as $resortBlackout ) {
                        //if this resort is in the resort blackout array then continue looking for the date
                        if ( in_array( $prop->RID, $resortBlackout->resorts ) ) {
                            if ( strtotime( $prop->checkIn ) >= strtotime( $resortBlackout->start ) && strtotime( $prop->checkIn ) <= strtotime( $resortBlackout->end ) ) {
                                $continue = true;
                                $pi ++;
                                continue 2;
                            }
                        }
                    }
                }

                //resort specific travel dates
                if ( isset( $specialMeta->resortTravel ) && ! empty( $specialMeta->resortTravel ) ) {
                    foreach ( $specialMeta->resortTravel as $resortTravel ) {
                        //if this resort is in the resort blackout array then continue looking for the date
                        if ( in_array( $prop->RID, $resortTravel->resorts ) ) {
                            if ( strtotime( $prop->checkIn ) >= strtotime( $resortTravel->start ) && strtotime( $prop->checkIn ) <= strtotime( $resortTravel->end ) ) {
                                //all good
                            } else {
                                $continue = true;
                                $pi ++;
                                continue 2;
                            }
                        }
                    }
                }


                //transaction type
                if ( ( ( is_array( $specialMeta->transactionType ) && ! in_array( 'any',
                                                                                  $specialMeta->transactionType ) ) || ( ! is_array( $specialMeta->transactionType ) && $specialMeta->transactionType != 'any' ) ) && $specialMeta->transactionType != 'upsell' ) {
                    $apwt = $prop->WeekType;
                    if ( $apwt == 'RentalWeek' ) {
                        $apwt = 'BonusWeek';
                    }
                    if ( ( is_array( $specialMeta->transactionType ) && ! in_array( $apwt,
                                                                                    $specialMeta->transactionType ) ) || ( ! is_array( $specialMeta->transactionType ) && $apwt != $specialMeta->transactionType ) ) {
                        $pi ++;
                        continue;
                    }
                }

                //week min cost
                if ( isset( $specialMeta->minWeekPrice ) && ! empty( $specialMeta->minWeekPrice ) ) {
                    if ( $prop->WeekType == 'ExchangeWeek' ) {
                        continue;
                    }

                    if ( $prop->Price < $specialMeta->minWeekPrice ) {
                        $pi ++;
                        continue;
                    }
                }

                if ( strpos( $special->SpecUsage, 'customer' ) !== false )//customer specific
                {
                    if ( isset( $cid ) ) {
                        $specCust = (array) json_decode( $specialMeta->specificCustomer );
                        if ( ! is_user_logged_in() ) {
                            //if this user isn't logged in then we still want to display the results
                        } elseif ( ! in_array( $cid, $specCust ) ) {
                            $pi ++;
                            continue;
                        }
                    } else {
                        $pi ++;
                        continue;
                    }
                }
                //useage DAE
                if ( isset( $specialMeta->useage_dae ) && ! empty( $specialMeta->useage_dae ) ) {
                    if ( ( strtolower( $prop->StockDisplay ) == 'all' || ( strtolower( $prop->StockDisplay ) == 'gpx' || strtolower( $prop->StockDisplay ) == 'usa gpx' ) ) && ( strtolower( $prop->OwnerBusCatCode ) == 'dae' || strtolower( $prop->OwnerBusCatCode ) == 'usa dae' ) ) {
                        // we're all good -- these are the only properties that should be displayed
                    } else {
                        $continue = true;
                    }
                }


                //exclusions

                //exclude resorts
                if ( isset( $specialMeta->exclude_resort ) && ! empty( $specialMeta->exclude_resort ) ) {
                    if ( in_array( $prop->RID, $specialMeta->exclude_resort ) ) {
                        $continue = true;
                    }
                }

                // !!!!! sql in LOOP !!!
                //exclude regions

                //exclude home resort
                if ( isset( $specialMeta->exclusions ) && $specialMeta->exclusions == 'home-resort' ) {
                    if ( isset( $usermeta ) && ! empty( $usermeta ) ) {
                        $ownresorts = [ 'OwnResort1', 'OwnResort2', 'OwnResort3' ];
                        foreach ( $ownresorts as $or ) {
                            if ( isset( $usermeta->$or ) ) {
                                if ( $usermeta->$or == $prop->ResortName ) {
                                    $continue = true;
                                }
                            }
                        }
                    }
                }

                //lead time
                if ( isset( $specialMeta->leadTimeMin ) && ! empty( $specialMeta->leadTimeMin ) ) {
                    $ltdate = date( 'Y-m-d',
                                    strtotime( $prop->checkIn . " -" . $specialMeta->leadTimeMin . " days" ) );
                    if ( $today > $ltdate ) {
                        $pi ++;
                        continue;
                    }
                }

                if ( isset( $specialMeta->leadTimeMax ) && ! empty( $specialMeta->leadTimeMax ) ) {
                    $ltdate = date( 'Y-m-d',
                                    strtotime( $prop->checkIn . " -" . $specialMeta->leadTimeMax . " days" ) );
                    if ( $today < $ltdate ) {
                        $pi ++;
                        continue;
                    }
                }

                if ( isset( $specialMeta->bookStartDate ) && ! empty( $specialMeta->bookStartDate ) ) {
                    $bookStartDate = date( 'Y-m-d', strtotime( $specialMeta->bookStartDate ) );
                    if ( $today < $bookStartDate ) {
                        $pi ++;
                        continue;
                    }
                }

                if ( isset( $specialMeta->bookEndDate ) && ! empty( $specialMeta->bookEndDate ) ) {
                    $bookEndDate = date( 'Y-m-d', strtotime( $specialMeta->bookEndDate ) );
                    if ( $today > $bookEndDate ) {
                        $pi ++;
                        continue;
                    }
                }

                if ( $continue )    // get rid of all this 'continue' - remove prop from proplist
                {
                    $pi ++;
                    continue;
                } else {
                    //if exclusive weeks are set here then all conditions have been met we can display it as long as it isn't fenced off somewhere else
                    if ( isset( $rmExclusiveWeek[ $prop->weekId ] ) && ! empty( $rmExclusiveWeek[ $prop->weekId ] ) ) {
                        if ( in_array( $prop->weekId, $exrmExclusiveWeek ) ) {
                            unset( $exrmExclusiveWeek[ $prop->weekId ] );
                        }
                        unset( $rmExclusiveWeek[ $prop->weekId ] );
                    }
                }

                //remove any exclusive weeks
                if ( ( isset( $rmExclusiveWeek[ $prop->weekId ] ) && ! empty( $rmExclusiveWeek[ $prop->weekId ] ) ) ) {
                    $pi ++;
                    continue;
                }

                $prop->special = (object) array_merge( (array) $special, (array) $specialMeta );
                $discountType = $specialMeta->promoType;
                if ( $specialMeta->transactionType == 'upsell' || in_array( 'upsell',
                                                                            $specialMeta->transactionType ) ) {
                    //we don't want any discount -- just display the results
                } elseif ( $discountType == 'Pct Off' ) {
                    $prop->specialPrice = number_format( $prop->Price * ( 1 - ( $discount[ $special->id ] / 100 ) ),
                                                         2 );
                } elseif ( $discountType == 'Dollar Off' ) {
                    $prop->specialPrice = $prop->Price - $discount[ $special->id ];
                } elseif ( $discount[ $special->id ] < $prop->Price ) {
                    $prop->specialPrice = $discount[ $special->id ];
                }
                if ( $prop->specialPrice < 0 ) {
                    $prop->specialPrice = '0.00';
                }

                if ( isset( $specialMeta->beforeLogin ) && $specialMeta->beforeLogin == "Yes" ) {
                    if ( ! is_user_logged_in() ) {
                        $loginalert = true;
                        $prop->specialPrice = '';
                    }
                }
                //display the properties even when they aren't logged in...
                if ( ! is_user_logged_in() ) {
                    if ( $specialMeta->beforeLogin == 'No' ) {
                        //do nothing we want to show these prices
                    } else {
                        $loginalert = true;
                        $prop->specialPrice = '';
                    }
                }
                if ( isset( $specialMeta->icon ) ) {
                    $prop->specialicon = $specialMeta->icon;
                }
                if ( isset( $specialMeta->desc ) ) {
                    $prop->specialdesc = $specialMeta->desc;
                }

                $prop->AllInclusive = '';
                $resortFacilities = json_decode( $prop->ResortFacilities );
                if ( ( is_array( $resortFacilities ) && in_array( 'All Inclusive',
                                                                  $resortFacilities ) ) || strpos( $prop->HTMLAlertNotes,
                                                                                                   'IMPORTANT: All-Inclusive Information' ) || strpos( $prop->AlertNote,
                                                                                                                                                       'IMPORTANT: This is an All Inclusive (AI) property.' ) ) {
                    $prop->AllInclusive = '6';
                }

                $pwt = "b";
                if ( $prop->WeekType == 'ExchangeWeek' ) {
                    $pwt = "a";
                }

                $propkeyset = strtotime( $prop->checkIn ) . $pwt . $prop->weekId;
                $prop->propkeyset = $propkeyset;

                //if the prop was set already then we need to see if this price is less.
                if ( isset( $resorts[ $prop->ResortID ]['props'] ) && array_key_exists( $propkeyset,
                                                                                        $resorts[ $prop->ResortID ]['props'] ) ) {
                    //is this price more than the previous price?  If so we don't want to set the price.
                    if ( str_replace( ",", "", str_replace( ".00", "", $prop->specialPrice ) ) >= str_replace( ",",
                                                                                                               "",
                                                                                                               str_replace( ".00",
                                                                                                                            "",
                                                                                                                            $resorts[ $prop->ResortID ]['props'][ $propkeyset ]->specialPrice ) ) ) {
                        $pi ++;
                        continue;
                    }
                }

                //need to add the special back in if the previous propkeyset had a special but this one doesn't
                if ( ! isset( $prop->specialPrice ) || ( isset( $prop->SpecialPrice ) && empty( $prop->specialPrice ) ) ) {
                    $prop->specialPrice = $prefPropSetDets[ $propkeyset ]['specialPrice'];
                    $prop->specialicon = $prefPropSetDets[ $propkeyset ]['specialicon'];
                    $prop->specialdesc = $prefPropSetDets[ $propkeyset ]['specialdesc'];
                }

                $checkFN[ $prop->gpxRegionID ] = $prop->gpxRegionID;
                $propsetspecialprice[ $propkeyset ] = $prop->specialPrice;
                $prefPropSetDets[ $propkeyset ]['specialPrice'] = $prop->specialPrice;
                $prefPropSetDets[ $propkeyset ]['specialicon'] = $prop->specialicon;
                $prefPropSetDets[ $propkeyset ]['specialdesc'] = $prop->specialdesc;

                $propPrice[ $propkeyset ] = $prop->WeekPrice;

                $resorts[ $prop->ResortID ]['resort'] = $prop;

                $resorts[ $prop->ResortID ]['props'][ $propkeyset ] = $prop;

                $rp[ $propkeyset ] = $prop;
                $resorts[ $prop->ResortID ]['propopts'][ $propkeyset ][] = $prop;

                $allProps[ $prop->ResortID ][] = $prop;
                $pi ++;
            }
        }
    }

    $filterNames = ! empty( $checkFN ) ? gpx_db()->fetchAllKeyValue( "SELECT id, name FROM wp_gpxRegion WHERE id IN (?) AND name != 'All' ORDER BY name ASC",
                                                                     [ $checkFN ],
                                                                     [ Connection::PARAM_INT_ARRAY ] ) : [];
    //setting the display options...
    foreach ( $resorts as $rk => $resort ) {
        foreach ( $resort['propopts'] as $key => $value ) {
            $propOpts = [
                'slash' => '',
                'icon' => '',
                'desc' => '',
                'preventhighlight' => '',
            ];
            $propDescs = [];
            foreach ( $value as $prop ) {
                if ( isset( $prop->special->slash ) && $prop->special->slash == 'Force Slash' ) {
                    $propOpts['slash'] = '1';
                }
                if ( isset( $prop->specialicon ) && ! empty( $prop->specialicon ) ) {
                    $propOpts['icon'] = $prop->specialicon;
                }
                if ( isset( $prop->specialdesc ) ) {
                    $propDescs[] = $prop->specialdesc;
                }
                if ( isset( $prop->special->highlight ) && $prop->special->highlight == 'Prevent Highlighting' ) {
                    $propOpts['preventhighlight'] = '1';
                }
            }
            if ( ! empty( $propDescs ) ) {
                $propOpts['desc'] = implode( "\r\n", $propDescs );
            }
            foreach ( $propOpts as $opt => $v ) {
                $setPropDetails[ $key ][ $opt ] = $v;
            }
        }
    }

    //get a list of restricted gpxRegions
    $restrictIDs = gpx_db()->fetchAllKeyValue( "SELECT r.id, r.id FROM wp_gpxRegion r INNER JOIN wp_gpxRegion ca ON (ca.name = 'Southern Coast (California)') WHERE r.lft BETWEEN ca.lft AND ca.rght" );
    include( 'templates/sc-result.php' );
}

add_shortcode( 'gpx_promo_page', 'gpx_promo_page_sc' );

function promo_retrieve_each( $specialMeta, $props ) {
}

// REMOVE WP EMOJI
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );


function gpx_view_profile_sc() {
    global $wpdb;

    $cid = gpx_get_switch_user_cookie();

    $user = get_userdata( $cid );
    $usermeta = (object) array_map( function ( $a ) {
        return $a[0];
    }, get_user_meta( $cid ) );

    if ( empty( $usermeta->first_name ) && ! empty( $usermeta->FirstName1 ) ) {
        $usermeta->first_name = $usermeta->FirstName1;
    }

    if ( empty( $usermeta->last_name ) && ! empty( $usermeta->LastName1 ) ) {
        $usermeta->last_name = $usermeta->LastName1;
    }

    $usermeta->Email = OwnerRepository::instance()->get_email( $cid );

    $dayphone = '';
    if ( isset( $usermeta->DayPhone ) && ! empty( $usermeta->DayPhone ) && ! is_object( $usermeta->DayPhone ) ) {
        $dayphone = $usermeta->DayPhone;
        if ( is_object( unserialize( $usermeta->DayPhone ) ) ) {
            $dayphone = '';
        }
    }
    $usermeta->DayPhone = $dayphone;

    //set the profile columns
    $profilecols[0] = [
        [
            'placeholder' => "First Name",
            'type' => 'text',
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'first_name' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "Last Name",
            'type' => 'text',
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'last_name' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "Email",
            'type' => 'email',
            'class' => 'validate emailvalidate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'Email' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "Home Phone",
            'type' => 'tel',
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'DayPhone' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "Mobile Phone",
            'type' => 'tel',
            'class' => '',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'Mobile1' ],
            'required' => '',
        ],
    ];
    $profilecols[1] = [
        [
            'placeholder' => "Street Address",
            'type' => 'text',
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'Address1' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "City",
            'type' => 'text',
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'Address3' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "State",
            'type' => 'text',
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'Address4' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "Zip",
            'type' => 'text',
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'PostCode' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "Country",
            'type' => 'text',
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'Address5' ],
            'required' => 'required',
        ],
    ];

    if ( isset( $_POST['cid'] ) && $cid = $_POST['cid'] ) {
        foreach ( $profilecols as $col ) {
            foreach ( $col as $data ) {
                if ( $data['value']['retrieve'] == 'Email' ) //update user table
                {
                    $mainData = [
                        'ID' => $cid,
                        'user_email' => $_POST[ $data['value']['retrieve'] ],
                    ];
                    wp_update_user( $mainData );
                    update_user_meta( $cid, $data['value']['retrieve'], $_POST[ $data['value']['retrieve'] ] );
                } else //update user meta
                {
                    update_user_meta( $cid, $data['value']['retrieve'], $_POST[ $data['value']['retrieve'] ] );
                }
                if ( $data['value']['retrieve'] == 'user_email' ) {
                    update_user_meta( $cid, 'Email', $_POST[ $data['value']['retrieve'] ] );
                }
            }
        }
        //send to DAE
        require_once ABSPATH . '/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
        $gpx = new GpxRetrieve( GPXADMIN_API_URI, GPXADMIN_API_DIR );
        if ( isset( $usermeta->DAEMemberNo ) ) {
            $update = $gpx->DAEUpdateMemberDetails( $usermeta->DAEMemberNo, $_POST );
        }
    }

    $sql = $wpdb->prepare( "SELECT * FROM wp_gpxMemberSearch WHERE userID = %d", $cid );
    $results = $wpdb->get_results( $sql );

    foreach ( $results as $result ) {
        $history = json_decode( $result->data );
        foreach ( $history as $key => $value ) {
            if ( isset( $value->week_type ) ) {
                $splitKey = explode( '-', $key );
                if ( $splitKey[0] == 'view' ) {
                    $weektype = $value->week_type;
                    if ( $weektype == 'BonusWeek' ) {
                        $weektype = 'RentalWeek';
                    }
                    $histout[ $weektype ][] = [
                        'weekId' => '<a href="/booking-path?book=' . esc_attr( $value->id ) . '">' . esc_html( $value->id ) . '</a>',
                        'ResortName' => '<a href="resort-profile/?resortName=' . esc_attr( $value->name ) . '">' . esc_html( $value->name ) . '</a>',
                        'Price' => '<a href="/booking-path?book=' . esc_attr( $value->id ) . '">' . esc_html( $value->price ) . '</a>',
                        'checkIn' => '<a href="/booking-path?book=' . esc_attr( $value->id ) . '">' . esc_html( $value->checkIn ) . '</a>',
                        'Size' => '<a href="/booking-path?book=' . esc_attr( $value->id ) . '">' . esc_html( $value->beds ) . '</a>',
                    ];
                }
            }
            if ( isset( $value->ResortName ) ) {
                $searched = "N/A";
                if ( isset( $value->search_month ) ) {
                    $searched = $value->search_month;
                }
                if ( isset( $value->search_year ) ) {
                    $searched .= ' ' . $value->search_year;
                }
                $histoutresort[] = [
                    'ResortName' => '<a href="/resort-profile/?resort=' . esc_attr( $value->id ) . '">' . esc_html( $value->ResortName ) . '</a>',
                    'DateViewed' => '<a href="/resort-profile/?resort=' . esc_attr( $value->id ) . '">' . date( "m/d/Y",
                                                                                                                strtotime( $value->DateViewed ) ) . '</a>',
                    'Searched' => '<a href="/resort-profile/?resort=' . esc_attr( $value->id ) . '">' . esc_html( $searched ) . '</a>',
                ];
            }
        }
    }

    $expireDate = date( 'Y-m-d H:i:s' );
    //get my coupons
    $sql = $wpdb->prepare( "SELECT a.coupon_hash, a.used, b.Name, b.Slug, b.Properties FROM wp_gpxAutoCoupon a
            INNER JOIN wp_specials b ON a.coupon_id=b.id
            WHERE user_id=%d
            AND EndDate > %s ORDER BY used",
                           [ $cid, $expireDate ] );
    $acs = $wpdb->get_results( $sql );
    foreach ( $acs as $ac ) {
        $redeemed = "No";
        $promoproperties = json_decode( $ac->Properties );


        if ( $ac->used == '1' ) {
            $redeemed = "Yes";
        }
        $mycoupons[] = [
            'name' => $ac->Name,
            'slug' => $ac->Slug,
            'code' => $ac->coupon_hash,
            'redeemed' => $redeemed,
            'details' => $promoproperties->actc,
        ];
    }
    //get my owner credit coupons
    //get the coupon
    $sql = $wpdb->prepare( "SELECT *, a.id as cid, b.id as oid, c.id as aid, c.datetime as activity_date FROM wp_gpxOwnerCreditCoupon a
                    INNER JOIN wp_gpxOwnerCreditCoupon_owner b ON b.couponID=a.id
                    INNER JOIN wp_gpxOwnerCreditCoupon_activity c ON c.couponID=a.id
                    WHERE b.ownerID=%d",
                           $cid );
    $coupons = $wpdb->get_results( $sql );
    $distinctCoupon = [];
    foreach ( $coupons as $coupon ) {
        $distinctCoupon[ $coupon->cid ]['coupon'] = $coupon;
        $distinctCoupon[ $coupon->cid ]['activity'][ $coupon->aid ] = $coupon;
    }
    foreach ( $distinctCoupon as $dcKey => $dc ) {
        $activityDate = '0';
        foreach ( $dc['activity'] as $activity ) {
            if ( $activity->activity == 'transaction' ) {
                $redeemedAmount[ $dcKey ][] = $activity->amount;
            } else {
                $amount[ $dcKey ][] = $activity->amount;
                //get the greatest date
                if ( $activity->activity == 'created' ) {
                    $activityDate = strtotime( $activity->activity_date );
                }
            }
        }
        //if activitydate <> 0 and is more than 1 year ago then we shouldn't display this coupon
        if ( $activityDate != 0 && $activityDate < strtotime( '-1 year' ) ) {
            continue;
        }

        if ( $dc['coupon']->single_use == 1 && array_sum( $redeemedAmount[ $dcKey ] ) > 0 ) {
            $balance = 0;
        } else {
            $balance[ $dcKey ] = array_sum( $amount[ $dcKey ] ) - array_sum( $redeemedAmount[ $dcKey ] );
        }

        $mycreditcoupons[] = [
            'name' => $dc['coupon']->name,
            'code' => $dc['coupon']->couponcode,
            'balance' => '$' . $balance[ $dcKey ],
            'redeemed' => '$' . array_sum( $redeemedAmount[ $dcKey ] ),
            'active' => $dc['coupon']->active,
            'expire' => date( 'm/d/Y', strtotime( $dc['coupon']->expirationDate ) ),
        ];
    }

    //get my custom requests
    $sql = $wpdb->prepare( "SELECT * FROM wp_gpxCustomRequest WHERE emsID=%s ORDER BY active", $usermeta->DAEMemberNo );
    $crs = $wpdb->get_results( $sql );
    foreach ( $crs as $cr ) {
        $i = 0;
        $location = '<a href="#" class="edit-custom-request" data-rid="' . esc_attr( $cr->id ) . '" aria-label="Edit Custom Request"><i class="fa fa-eye" aria-hidden="true"></i></a> ';
        if ( ! empty( $cr->resort ) ) {
            $location .= 'Resort: ' . esc_html( $cr->resort );
        } elseif ( ! empty( $cr->city ) ) {
            $location .= 'City: ' . esc_html( $cr->city );
        } elseif ( ! empty( $cr->region ) ) {
            $location .= 'Region: ' . esc_html( $cr->region );
        }

        $date = $cr->checkIn;
        if ( ! empty( $cr->checkIn2 ) ) {
            if ( strtotime( $cr->checkIn2 ) < strtotime( "now" ) ) {
                continue;
            }
            $date .= ' - ' . $cr->checkIn2;
        } elseif ( strtotime( $cr->checkIn ) < strtotime( "now" ) ) {
            continue;
        }
        $requesteddate = date( 'm/d/Y', strtotime( $cr->datetime ) );
        $found = "Yes";
        if ( empty( $cr->matched ) ) {
            $found = "No";
        }

        //Request to be kept ‘visible’ even if Inactive (remove option to ‘Delete’)
        $active = 'No <a href="#" class="crActivate btn btn-secondary" data-crid="' . esc_attr( $cr->id ) . '" data-action="activate">Enable</a>';
        //changing back to the previous version where we had a toggle option
        if ( $found == "Yes" ) {
            $active = 'No';
        }
//                 $active = 'No';
        if ( $cr->active == '1' ) {
            $active = 'Yes';
            //Request to be kept ‘visible’ even if Inactive (remove option to ‘Delete’)
            //adding this option back in
            $active = 'Yes <a href="#" class="crActivate btn btn-secondary" data-crid="' . esc_attr( $cr->id ) . '" data-action="deactivate">Disable</a>';
        }
        $db = (array) $cr;
        $matched = custom_request_match( $db );
        $matches = 'No';
        if ( ! empty( $matched ) ) {
            $matchLink = ' <a class="btn btn-secondary" href="/result?matched=' . urlencode( $cr->id ) . '">View Results</a>';
            if ( ! empty( $cr->week_on_hold ) ) {
                $crWeekType = '&type=ExchangeWeek';
                if ( $cr->preference == 'Rental' ) {
                    $crWeekType = str_replace( 'Exchange', 'Rental', $crWeekType );
                }
                $matchLink = ' <a class="btn btn-secondary" href="/booking-path/?book=' . urlencode( $cr->week_on_hold ) . $crWeekType . '">View Results</a>';
            }
            $matches = '';
            if ( ! empty( $cr->matchEmail ) ) {
                $matches .= '<span title="Notification Sent: ' . date( 'm/d/y', strtotime( $cr->matchEmail ) ) . '">';
            }
            $matches .= 'Yes';
            $matches .= $matchLink;
            if ( ! empty( $cr->matchEmail ) ) {
                $matches .= '</span>';
            }
            // if we are only returning the restricted key then this isn't a match
            if ( count( $matched ) == 1 && isset( $matched['restricted'] ) ) {
                $matches = 'No';
            }
        }

        $customRequests[ $i ]['location'] = $location;
        $customRequests[ $i ]['traveldate'] = $date;
        $customRequests[ $i ]['requesteddate'] = $requesteddate;
        $customRequests[ $i ]['matched'] = $matches;
        $customRequests[ $i ]['active'] = $active;
        $i ++;
    }


    $sql = $wpdb->prepare( "SELECT *  FROM `wp_GPR_Owner_ID__c` WHERE `user_id` = %d", $cid );
    $gprOwner = $wpdb->get_row( $sql );

    include( 'templates/sc-view-profile.php' );
}

add_shortcode( 'gpx_view_profile', 'gpx_view_profile_sc' );

function custom_request_status_change() {
    global $wpdb;
    if ( isset( $_POST['crid'] ) ) {
        $id = $_POST['crid'];
        $udata['active'] = '1';
        if ( $_POST['craction'] == 'deactivate' ) {
            $udata['active'] = 0;
        }
    }
    if ( isset( $_REQUEST['croid'] ) ) {
        $udata['active'] = 0;
        $split = explode( "221a2d2s33d564334ne3", $_REQUEST['croid'] );
        $id = $split[0];
        $emsID = $split[1];
        $sql = $wpdb->prepare( "SELECT active FROM wp_gpxCustomRequest where id=%d", $id );
        $row = $wpdb->get_row( $sql );
        if ( $row->active == '0' ) {
            $udata['active'] = 1;
        }
    }

    if ( isset( $id ) ) {
        $update = $wpdb->update( 'wp_gpxCustomRequest', $udata, [ 'id' => $id ] );
        $data['success'] = true;
    }
    if ( isset( $_REQUEST['croid'] ) ) {
        wp_redirect( get_site_url( "", "/custom-request-status-updated" ) );
        die;
    }

    wp_send_json( $data );
}

add_action( "wp_ajax_custom_request_status_change", "custom_request_status_change" );
add_action( "wp_ajax_nopriv_custom_request_status_change", "custom_request_status_change" );

function custom_request_validate_restrictions() {
    global $wpdb;

    $data = [ 'success' => false ];

    $forDB = [
        '00N40000003S58X' => 'region',
        '00N40000003DG5S' => 'city',
        '00N40000003DG59' => 'resort',
        'miles' => 'miles',
    ];
    foreach ( $forDB as $key => $value ) {
        if ( ! empty( $_POST[ $key ] ) ) {
            $db[ $value ] = $_POST[ $key ];
        }
    }


    $dateRanges = json_decode( stripslashes( $_POST['00N40000003DG5P'] ) );
    $db['checkIn'] = date( 'm/d/Y', strtotime( $dateRanges->start ) );
    $db['checkIn2'] = date( 'm/d/Y', strtotime( $dateRanges->end ) );
    if ( isset( $db['checkIn'] ) && ( isset( $db['region'] ) || isset( $db['city'] ) || isset( $db['resort'] ) ) ) {
        $crd = custom_request_match( $db );
        if ( $crd ) {
            $data = $crd;
        }
    }

    wp_send_json( $data );
}

add_action( "wp_ajax_custom_request_validate_restrictions", "custom_request_validate_restrictions" );
add_action( "wp_ajax_nopriv_custom_request_validate_restrictions", "custom_request_validate_restrictions" );

function gpx_member_dashboard_sc() {
    global $wpdb;

    $cid = gpx_get_switch_user_cookie();


    //set the profile columns
    $profilecols[0] = [
        [
            'placeholder' => "First Name",
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'FirstName1' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "Last Name",
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'LastName1' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "Email",
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'Email' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "Home Phone",
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'HomePhone' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "Mobile Phone",
            'class' => '',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'Mobile' ],
            'required' => '',
        ],
    ];
    $profilecols[1] = [
        [
            'placeholder' => "Street Address",
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'Address1' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "City",
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'Address3' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "State",
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'Address4' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "Zip",
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'PostCode' ],
            'required' => 'required',
        ],
        [
            'placeholder' => "Country",
            'class' => 'validate',
            'value' => [ 'from' => 'usermeta', 'retrieve' => 'Address5' ],
            'required' => 'required',
        ],
    ];
    if ( isset( $_POST['cid'] ) && $cid = $_POST['cid'] ) {
        foreach ( $profilecols as $col ) {
            foreach ( $col as $data ) {
                if ( $data['value']['from'] == 'user' ) //update user table
                {
                    $mainData = [
                        'ID' => $cid,
                        'user_email' => $_POST[ $data['value']['retrieve'] ],
                    ];
                    wp_update_user( $mainData );
                } else //update user meta
                {
                    update_user_meta( $cid, $data['value']['retrieve'], $_POST[ $data['value']['retrieve'] ] );
                }
                if ( $data['value']['retrieve'] == 'user_email' ) {
                    update_user_meta( $cid, 'Email', $_POST[ $data['value']['retrieve'] ] );
                }
            }
        }
    }

    $user = get_userdata( $cid );
    $usermeta = (object) array_map( function ( $a ) {
        return $a[0];
    }, get_user_meta( $cid ) );

    if ( ! get_user_meta( $cid, 'DAEMemberNo', true ) ) {
        require_once GPXADMIN_API_DIR . '/functions/class.gpxretrieve.php';
        $gpx = new GpxRetrieve( GPXADMIN_API_URI, GPXADMIN_API_DIR );

        $DAEMemberNo = str_replace( "U", "", $user->user_login );
        $user = $gpx->DAEGetMemberDetails( $DAEMemberNo, $cid, [ 'email' => $usermeta->email ] );
    }

    $sql = $wpdb->prepare( "SELECT * FROM wp_gpxMemberSearch WHERE userID=%d", $cid );
    $results = $wpdb->get_results( $sql );

    foreach ( $results as $result ) {
        $history = json_decode( $result->data );
        foreach ( $history as $key => $value ) {
            if ( isset( $value->property->WeekType ) ) {
                $splitKey = explode( '-', $key );
                if ( $splitKey[0] == 'select' ) {
                    $weektype = $value->property->WeekType;
                    $histsetPrice = $value->price;
                    $histdaePrice = $value->property->Price;
                    $price = $value->property->WeekPrice;
                    if ( $histsetPrice < $histdaePrice ) {
                        $price = '<span style="text-decoration: line-through;">' . $value->property->WeekPrice . '</span> ' . str_replace( $value->property->Price,
                                                                                                                                           $histsetPrice,
                                                                                                                                           $value->property->WeekPrice );
                    }
                    $histout[ $weektype ][] = [
                        'weekId' => $value->property->weekId,
                        'ResortName' => $value->property->ResortName,
                        'Price' => $price,
                        'checkIn' => $value->property->checkIn,
                        'Size' => $value->property->Size,
                    ];
                }
            }
        }
    }

    include( 'templates/sc-member-dashboard.php' );
}

add_shortcode( 'gpx_member_dashboard', 'gpx_member_dashboard_sc' );

function vc_gpx_member_dashboard() {
    vc_map( [
                "name" => __( "GPX Memeber Dashboard", "gpx-website" ),
                "base" => "gpx_member_dashboard",
                "params" => [
                    // add params same as with any other content element
                    [
                        "type" => "textfield",
                        "heading" => __( "Extra class name", "gpx-website" ),
                        "param_name" => "el_class",
                        "description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.",
                                             "my-text-domain" ),
                    ],
                ],
            ] );
}

add_action( 'vc_before_init', 'vc_gpx_member_dashboard' );

function vc_gpx_locations_page() {
    vc_map( [
                "name" => __( "Locations Map", "gpx-website" ),
                "base" => "wpsl",
                "params" => [
                    // add params same as with any other content element
                    [
                        'type' => "dropdown",
                        'heading' => __( "Template", "gpx-website" ),
                        'param_name' => 'template',
                        'description' => __( "Extra templates could be added here.  Right now we are just using Default." ),
                        'value' => [
                            'default',
                        ],
                    ],
                    [
                        "type" => "textfield",
                        "heading" => __( "Extra class name", "my-text-domain" ),
                        "param_name" => "el_class",
                        "description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.",
                                             "my-text-domain" ),
                    ],
                ],
            ] );
}

add_action( 'vc_before_init', 'vc_gpx_locations_page' );

//add/enter a new coupon
function gpx_enter_coupon() {
    global $wpdb;
    $cid = gpx_get_switch_user_cookie();
    $coupon = $_POST['coupon'] ?? null;
    $cartID = $_POST['cartID'] ?? null;
    $book = $_POST['book'] ?? null;
    $currentPrice = $_POST['currentPrice'] ?? null;
    $return = [];

    $user = get_userdata( $cid );

    //check if it is an auto create coupon
    $couponParts    = preg_split( "(-|\s+)", $coupon );
    $autoCouponHash = end( $couponParts );

    //check database
    $sql = $wpdb->prepare("SELECT coupon_id FROM wp_gpxAutoCoupon WHERE coupon_hash=%s AND user_id=%d AND used=0", [$autoCouponHash, $cid]);
    $ac  = $wpdb->get_row( $sql );

    if ( ! empty( $ac ) ) //this is a hashed coupon
    {
        $acForCart = $autoCouponHash;
        $sql       = $wpdb->prepare("SELECT * FROM wp_specials WHERE Type='coupon' AND id=%d AND active=1", $ac->coupon_id);
    } else {
        $sql = $wpdb->prepare("SELECT * FROM wp_specials WHERE Type='coupon' AND (Name=%s OR Slug=%s) AND active=1", [$coupon,$coupon]);
    }
    $row = $wpdb->get_row( $sql );


    if ( empty( $row ) ) {
        //check to see if this is a owner credit coupon
        $sql       = $wpdb->prepare("SELECT *, a.id as cid, b.id as aid, c.id as oid FROM wp_gpxOwnerCreditCoupon a
                INNER JOIN wp_gpxOwnerCreditCoupon_activity b ON b.couponID=a.id
                INNER JOIN wp_gpxOwnerCreditCoupon_owner c ON c.couponID=a.id
                WHERE (a.name=%s OR a.couponcode=%s) AND a.active=1 and c.ownerID=%d", [$coupon, $coupon, $cid]);
        $occoupons = $wpdb->get_results( $sql );
        if ( ! empty( $occoupons ) ) {
            $distinctOwner = [];
            $distinctActivity = [];
            $actredeemed = 0.00;
            $actamount = 0.00;
            foreach ( $occoupons as $occoupon ) {
                $distinctCoupon                     = $occoupon;
                $distinctOwner[ $occoupon->oid ]    = $occoupon;
                $distinctActivity[ $occoupon->aid ] = $occoupon;
            }

            //get the balance and activity for data
            foreach ( $distinctActivity as $activity ) {
                if ( $activity->activity == 'transaction' ) {
                    $actredeemed += (float)$activity->amount;
                } else {
                    $actamount += (float)$activity->amount;
                }
            }
            if ( $distinctCoupon->single_use && $actredeemed > 0 ) {
                $balance = 0;
            } else {
                $balance = $actamount - $actredeemed;
            }
            // if we have a balance at this point, the coupon is good
            if ( $balance > 0 ) {
                $sql      = $wpdb->prepare("SELECT * FROM wp_cart WHERE cartID=%s", $cartID);
                $cartRows = $wpdb->get_results( $sql );
                $ccs = [];
                foreach ( $cartRows as $cartRow ) {
                    $cart = json_decode( $cartRow->data );
                    if ( isset( $cart->occoupon ) ) {
                        if ( is_array( $cart->occoupon ) ) {
                            $ccs = $cart->occoupon;
                        } else {
                            $ccs[] = $cart->occoupon;
                        }
                    }
                    $ccs[]          = $distinctCoupon->cid;
                    $ccs = array_unique($ccs);
                    $cart->occoupon = $ccs;

                    $update = json_encode( $cart );
                    $wpdb->update( 'wp_cart', [ 'data' => $update ], [ 'id' => $cartRow->id ] );
                }
                $return['success'] = true;

                wp_send_json( $return );
            } else {
                //coupon isn't valid
                $return['error'] = "This coupon is invalid.";
            }
        } else {
            //coupon isn't valid
            $return['error'] = "This coupon is invalid.";
        }
    } else {
        $specialMeta = json_decode( $row->Properties, false );
        $bCouponID = $row->id;
        $now       = date( 'Y-m-d h:i:s' );
        if ( $now > $row->EndDate ) {
            $return['error'] = "This coupon has expired";
        }
        if ( $now < $row->StartDate ) {
            $return['error'] = "This coupon is invalid.";
        }

        if ( isset( $specialMeta->singleUse ) && $specialMeta->singleUse == 'Yes' ) {
            $sql = $wpdb->prepare("SELECT * FROM wp_redeemedCoupons WHERE userID=%d AND specialID=%d", [$cid, $row->id]);
            $cpDup = $wpdb->get_results( $sql );
            //now we can have more than one assigned so we need to see how many times this owner was added.
            $customersCount = array_count_values( json_decode( $specialMeta->specificCustomer, true ) );

            //now we need to add the people from the hard coded array
            $hcCustomers = include __DIR__ . '/data/hc-customers.php';

            foreach ( $hcCustomers as $hccK => $hccVs ) {
                foreach ( $hccVs as $hccV ) {
                    if ( $hccV == $daeMemberNo ) {
                        $hcConverted[] = $cid;
                        //was this owner already added?
                        if ( isset( $customersCount[ $cid ] ) && $customersCount[ $cid ] > 0 ) {
                            //this is a duplicate record -- we need to reduce the original amount
                            $customersCount[ $cid ] --;
                        }
                    }
                }
            }

            $hcCustomercount = count( $hcConverted );

            $customersCount[ $cid ] += $hcCustomercount;

            if ( ! empty( $cpDup ) && count( $cpDup ) >= $customersCount[ $cid ] ) {
                $return['error'] = "You have already used this coupon!";
            }
        }
    }

    if ( ! isset( $return['error'] ) ) {
        $bogo    = '';
        $bogomax = '';
        $bogomin = '';

        $sql      = $wpdb->prepare("SELECT * FROM wp_cart WHERE cartID=%s", $cartID);
        $cartRows = $wpdb->get_results( $sql );
        foreach ( $cartRows as $cartRow ) {
            $cart = json_decode( $cartRow->data );


            //make sure this wasn't added
            if ( isset( $cart->coupon ) && ! empty( $cart->coupon ) ) {
                if ( in_array( $row->id, (array) $cart->coupon ) ) {
                    $return['error'] = 'This coupon has already been applied!';
                    continue;
                }
            }

            if ( isset( $acForCart ) ) {
                $cart->acHash = $acForCart;
            }
            $thisPropID       = $cartRow->propertyID;
            $property_details = get_property_details( $thisPropID, $cid );

            extract( $property_details );

            $thiscart               = $cartRow->id;
            $bogoCarts[ $thiscart ] = $cart;

            $addCoupon = $row->id;
            $discount  = $row->Amount;

            $discountTypes = [
                'Pct Off',
                'Dollar Off',
                'Set Amt',
                'BOGO',
                'BOGOH',
                'Auto Create Coupon',
            ];
            $discountType  = $specialMeta->promoType;
            foreach ( $discountTypes as $dt ) {
                if ( strpos( $specialMeta->promoType, $dt ) ) {
                    $discountType = $dt;
                }
            }


            if ( $discountType == 'Pct Off' ) {
                $activePrice = number_format( $currentPrice * ( 1 - ( $discount / 100 ) ), 2 );
            } elseif ( $discountType == 'BOGO' || $discountType == 'BOGOH' ) {
                $bogo               = $currentPrice;
                $bogos[ $thiscart ] = $prop->Price;
                if ( $bogo > $bogomax ) {
                    $bogomax         = $bogo;
                    $cartBogo['max'] = $cart;
                }
                if ( empty( $bogomin ) ) {
                    $bogomin         = $bogo;
                    $bogoMinCartID   = $cartID;
                    $cartBogo['min'] = $cart;
                } elseif ( $bogo < $bogomin ) {
                    $bogomin         = $bogo;
                    $bogoMinCartID   = $cartID;
                    $cartBogo['min'] = $cart;
                }
            } elseif ( $discountType == 'Dollar Off' ) {
                $activePrice = $currentPrice - $discount;
            } elseif ( $discount < $currentPrice ) {
                $activePrice = $discount;
            }


            $skip = false;
            /*
             * filter out conditions
             */
            //blackouts
            if ( isset( $specialMeta->blackout ) && ! empty( $specialMeta->blackout ) ) {
                foreach ( $specialMeta->blackout as $blackout ) {
                    if ( strtotime( $prop->checkIn ) >= strtotime( $blackout->start ) && strtotime( $prop->checkIn ) <= strtotime( $blackout->end ) ) {
                        $skip = true;
                    }
                }
            }
            //resort blackout dates
            if ( isset( $specialMeta->resortBlackout ) && ! empty( $specialMeta->resortBlackout ) ) {
                foreach ( $specialMeta->resortBlackout as $resortBlackout ) {
                    //if this resort is in the resort blackout array then continue looking for the date
                    if ( in_array( $prop->resortID, $resortBlackout->resorts ) ) {
                        if ( strtotime( $prop->checkIn ) >= strtotime( $resortBlackout->start ) && strtotime( $prop->checkIn ) <= strtotime( $resortBlackout->end ) ) {
                            $skip = true;
                        }
                    }
                }
            }

            //resort specific travel dates
            if ( isset( $specialMeta->resortTravel ) && ! empty( $specialMeta->resortTravel ) ) {
                foreach ( $specialMeta->resortTravel as $resortTravel ) {
                    //if this resort is in the resort blackout array then continue looking for the date
                    if ( in_array( $prop->resortID, $resortTravel->resorts ) ) {
                        if ( strtotime( $prop->checkIn ) >= strtotime( $resortTravel->start ) && strtotime( $prop->checkIn ) <= strtotime( $resortTravel->end ) ) {
                            //all good
                        } else {
                            $skip = true;
                        }
                    }
                }
            }

            //week min cost
            if ( isset( $specialMeta->minWeekPrice ) && ! empty( $specialMeta->minWeekPrice ) ) {
                if ( $prop->WeekType == 'ExchangeWeek' ) {
                    $skip = true;
                }

                if ( get_current_user_id() == 5 ) {
                    $currentPrice = 351;
                }
                if ( $currentPrice < $specialMeta->minWeekPrice ) {
                    $skip = true;
                }
            }

            //usage upsell
            if ( isset( $specialMeta->upsellOptions ) && ! empty( $specialMeta->upsellOptions ) ) {
                $activePrice = $currentPrice;
                $skip        = true;
                if ( is_array( $specialMeta->upsellOptions ) ) {
                    $sma                        = $specialMeta->upsellOptions;
                    $specialMeta->upsellOptions = $sma[0];
                }

                switch ( $specialMeta->upsellOptions ) {
                    case 'CPO':
                        $datech = date( 'm/d/Y', strtotime( $prop->checkIn . ' -45 days' ) );
                        if ( date( 'm/d/Y' ) <= $datech ) {
                            if ( isset( $cart->CPOPrice ) && ! empty( $cart->CPOPrice ) ) {
                                if ( $discountType == 'Pct Off' ) {
                                    $activePrice = number_format( $cart->CPOPrice * ( $discount / 100 ), 2 );
                                } else {
                                    $activePrice = $cart->CPOPrice - $discount;
                                }
                                $skip = false;
                            }
                        }
                        break;

                    case 'Upgrade':
                        if ( isset( $cart->creditvalue ) && ! empty( $cart->creditvalue ) ) {
                            if ( $discountType == 'Pct Off' ) {
                                $activePrice = number_format( $cart->creditvalue * ( $discount / 100 ), 2 );
                            } else {
                                $activePrice = $cart->creditvalue - $discount;
                            }
                            $skip = false;
                        }
                        break;

                    case 'Guest Fees':
                        if ( isset( $cart->GuestFeeAmount ) && $cart->GuestFeeAmount == '1' ) {
                            $checkoutPropDetails = get_property_details_checkout( $thisPropID, $cid );
                            if ( isset( $checkoutPropDetails['indGuestFeeAmount'][ $thisPropID ] ) && ! empty( $checkoutPropDetails['indGuestFeeAmount'][ $thisPropID ] ) ) {
                                if ( $discountType == 'Pct Off' ) {
                                    $activePrice = $activePrice + number_format( $checkoutPropDetails['indGuestFeeAmount'][ $thisPropID ] * ( $discount / 100 ),
                                                                                 2 );
                                } else {
                                    $activePrice = $activePrice + ( $checkoutPropDetails['indGuestFeeAmount'][ $thisPropID ] - $discount );
                                }

                                $skip = false;
                            }
                        }

                    case 'Extension Fees':

                        break;
                }
            }

            //specific customer
            if ( isset( $specialMeta->specificCustomer ) && ! empty( $specialMeta->specificCustomer ) ) {
                $specificCustomer = json_decode( $specialMeta->specificCustomer );
                if ( ! in_array( $cid, $specificCustomer ) ) {
                    $skip = true;
                }
            }
            //usage resort
            if ( isset( $specialMeta->usage_region ) && ! empty( $specialMeta->usage_region ) ) {
                $usage_regions = json_decode( $specialMeta->usage_region );
                foreach ( $usage_regions as $usage_region ) {
                    $sql            = $wpdb->prepare("SELECT lft, rght FROM wp_gpxRegion WHERE id=%d", $usage_region);
                    $excludeLftRght = $wpdb->get_row( $sql );
                    $excleft        = $excludeLftRght->lft;
                    $excright       = $excludeLftRght->rght;
                    $sql            = $wpdb->prepare("SELECT id FROM wp_gpxRegion WHERE lft>= %d AND rght<= %d", [$excleft, $excright]);
                    $usageregions   = $wpdb->get_results( $sql );
                    if ( isset( $usageregions ) && ! empty( $usageregions ) ) {
                        foreach ( $usageregions as $usageregion ) {
                            $uregionsAr[] = $usageregion->id;
                        }
                    }
                }
                if ( ! in_array( $prop->gpxRegionID, $uregionsAr ) ) {
                    $skipRegion = true;
                    $skip       = true;
                }
            }

            //usage resort
            if ( isset( $specialMeta->usage_resort ) && ! empty( $specialMeta->usage_resort ) ) {
                if ( ! in_array( $property_details['prop']->resortID, $specialMeta->usage_resort ) ) {
                    //if the useage_region is set and we didn't skip it then we already know that this is OK -- We shouldn't check this
                    if ( isset( $skipRegion ) ) {
                        //everything is good here.
                    } else {
                        $skip = true;
                    }
                }
            }

            $smt = [
                'ExchangeWeek',
                'BonusWeek',
            ];
            if ( get_current_user_id() == 5 ) {

            }
            //transaction type
            if ( ( is_array( $specialMeta->transactionType ) && array_intersect( $specialMeta->transactionType,
                                                                                 $smt ) ) || $specialMeta->transactionType == 'ExchangeWeek' || $specialMeta->transactionType == 'BonusWeek' ) {
                $propWeekType = $prop->WeekType;
                $smtt         = $specialMeta->transactionType;
                if ( $propWeekType == 'RentalWeek' ) {
                    $propWeekType = 'BonusWeek';
                }
                if ( ! is_array( $smtt ) && ( $propWeekType != $specialMeta->transactionType ) || ( is_array( $smtt ) && ! in_array( $propWeekType,
                                                                                                                                     $smtt ) ) ) {
                    $skip = true;
                }
            }

            //exclusions

            //exclude resorts
            if ( isset( $specialMeta->exclude_resort ) && ! empty( $specialMeta->exclude_resort ) ) {
                if ( in_array( $prop->resortJoinID, $specialMeta->exclude_resort ) ) {
                    $skip = true;
                }
            }

            //exclude regions
            if ( isset( $specialMeta->exclude_region ) && ! empty( $specialMeta->exclude_region ) ) {
                $exclude_regions = json_decode( $specialMeta->exclude_region );
                foreach ( $exclude_regions as $exclude_region ) {
                    $sql            = $wpdb->prepare("SELECT lft, rght FROM wp_gpxRegion WHERE id=%d", $exclude_region);
                    $excludeLftRght = $wpdb->get_row( $sql );
                    $excleft        = $excludeLftRght->lft;
                    $excright       = $excludeLftRght->rght;
                    $sql            = $wpdb->prepare("SELECT * FROM wp_gpxRegion WHERE lft >= %d AND rght <= %d", [$excleft, $excright]);
                    $excregions     = $wpdb->get_results( $sql );
                    if ( isset( $excregions ) && ! empty( $excregions ) ) {
                        foreach ( $excregions as $excregion ) {
                            if ( $excregion->id == $prop->gpxRegionID ) {
                                $skip = true;
                            }
                        }
                    }
                }
            }

            //lead time
            $today = date( 'Y-m-d' );
            if ( isset( $specialMeta->leadTimeMin ) && ! empty( $specialMeta->leadTimeMin ) ) {
                $ltdate = date( 'Y-m-d', strtotime( $prop->checkIn . " -" . $specialMeta->leadTimeMin . " days" ) );
                if ( $today > $ltdate ) {
                    $skip = true;
                }
            }
            if ( isset( $specialMeta->leadTimeMax ) && ! empty( $specialMeta->leadTimeMax ) ) {
                $ltdate = date( 'Y-m-d', strtotime( $prop->checkIn . " -" . $specialMeta->leadTimeMax . " days" ) );
                if ( $today < $ltdate ) {
                    $skip = true;
                }
            }
            if ( isset( $specialMeta->bookStartDate ) && ! empty( $specialMeta->bookStartDate ) ) {
                $bookStartDate = date( 'Y-m-d', strtotime( $specialMeta->bookStartDate ) );
                if ( $today < $bookStartDate ) {
                    $skip = true;
                }
            }

            if ( isset( $specialMeta->bookEndDate ) && ! empty( $specialMeta->bookEndDate ) ) {
                $bookEndDate = date( 'Y-m-d', strtotime( $specialMeta->bookEndDate ) );
                if ( $today > $bookEndDate ) {
                    $skip = true;
                }
            }
            //stacking
            if ( $specialMeta->stacking == 'No' ) {
                if ( ! empty( $property_details['specialPrice'] ) ) {
                    $return['error'] = 'A promotional price has been applied to your transaction.  This coupon is not allowed.';
                    continue;
                } else {
                    if ( isset( $cart->coupon ) ) {
                        foreach ( $cart->coupon as $activeCoupon ) {
                            $sql        = $wpdb->prepare("SELECT Properties, Amount FROM wp_specials WHERE id=%d", $activeCoupon);
                            $active     = $wpdb->get_row( $sql );
                            $activeProp = stripslashes_deep( json_decode( $active->Properties ) );
                            if ( $activeProp->promoType == 'Pct Off' ) {
                                $thisPrice = number_format( $currentPrice * ( 1 - ( $active->Amount / 100 ) ), 2 );
                            } elseif ( $activeProp->promoType == 'Dollar Off' ) {
                                $thisPrice = $currentPrice - $active->Amount;
                            } elseif ( $Amount < $currentPrice ) {
                                $thisPrice = $active->Amount;
                            }

                            if ( ( isset( $thisPrice ) && ! empty( $thisPrice ) ) && $thisPrice < $activePrice ) {
                                $addCoupon   = $active->id;
                                $activePrice = $thisPrice;
                                unset( $cart->coupon );
                            }
                        }
                    }
                }
            }

            if ( ! $skip && ! isset( $return['error'] ) ) {
                if ( empty( $bogomin ) ) {
                    if ( isset( $cart->coupon ) ) {
                        $ccCart                = (array) $cart->coupon;
                        $ccCart[ $thisPropID ] = $addCoupon;
                    } else {
                        $ccCart[ $thisPropID ] = $addCoupon;
                    }

                    $cart->coupon = $ccCart;
                    $update = json_encode( $cart );
                    $wpdb->update( 'wp_cart', [ 'data' => $update ], [ 'cartID' => $cartID ] );
                    $return['success'] = true;
                }
            } else {
                $return['error'] = "This coupon isn't available for this transaction.";
            }
        }
        if ( isset( $bogos ) && ! empty( $bogos ) ) {
            $cnt = count( $bogos ) / 2;
            asort( $bogos, 1 );
            $i = 0;
            if ( $discountType == 'BOGOH' ) {
                foreach ( $bogos as $bogoK => $bogop ) {
                    $bogoCarts[ $bogoK ]->coupon = [ $bCouponID ];
                    if ( $i < $cnt ) {
                        if ( strpos( $cnt, '.5' ) !== false ) {
                            $return['error'] = "This coupon isn't available for this transaction.";
                            continue;
                        }
                        $bogoCarts[ $bogoK ]->couponbogo = $bogop / 2;

                        $update = json_encode( $bogoCarts[ $bogoK ] );
                        $wpdb->update( 'wp_cart', [ 'data' => $update ], [ 'id' => $bogoK ] );
                    } else {
                        $bogoCarts[ $bogoK ]->couponbogo = $bogop;

                        $update = json_encode( $bogoCarts[ $bogoK ] );
                        $wpdb->update( 'wp_cart', [ 'data' => $update ], [ 'id' => $bogoK ] );
                    }
                    $i ++;
                }
            } else {
                foreach ( $bogos as $bogoK => $bogop ) {
                    $bogoCarts[ $bogoK ]->coupon = [ $bCouponID ];
                    if ( $i < $cnt ) {
                        if ( strpos( $cnt, '.5' ) !== false ) {
                            $return['error'] = "This coupon isn't available for this transaction.";
                            continue;
                        }
                        $bogoCarts[ $bogoK ]->couponbogo = '0.00';

                        $update = json_encode( $bogoCarts[ $bogoK ] );
                        $wpdb->update( 'wp_cart', [ 'data' => $update ], [ 'id' => $bogoK ] );
                    } else {
                        $bogoCarts[ $bogoK ]->couponbogo = $bogop;

                        $update = json_encode( $bogoCarts[ $bogoK ] );
                        $wpdb->update( 'wp_cart', [ 'data' => $update ], [ 'id' => $bogoK ] );
                    }
                    $i ++;
                }
            }
            $return['success'] = true;
        }
    }
    wp_send_json( $return );
}

add_action( "wp_ajax_gpx_enter_coupon", "gpx_enter_coupon" );
add_action( "wp_ajax_nopriv_gpx_enter_coupon", "gpx_enter_coupon" );

function gpx_remove_coupon() {
    global $wpdb;

    $type = $_POST['type'] ?? 'coupon';
    $cartID = $_COOKIE['gpx-cart'] ?? '';
    if (!$cartID) {
        wp_send_json_error(['message' => 'No cart found']);
    }

    $sql = $wpdb->prepare( "SELECT cartID,data FROM wp_cart WHERE cartID=%s LIMIT 1", $cartID );
    $cartRow = $wpdb->get_row( $sql );
    $cart = json_decode( $cartRow->data );
    if ($type === 'occoupon') {
        unset( $cart->occoupon );
    } else {
        unset( $cart->coupon );
    }

    $update = json_encode( $cart );
    $wpdb->update( 'wp_cart', [ 'data' => $update ], [ 'cartID' => $cartID ] );

    wp_send_json_success( ['message' => 'Coupon removed'] );
}

add_action( "wp_ajax_gpx_remove_coupon", "gpx_remove_coupon" );
add_action( "wp_ajax_nopriv_gpx_remove_coupon", "gpx_remove_coupon" );

function gpx_remove_owner_credit_coupon() {
    global $wpdb;

    extract( $_POST );

    $sql = $wpdb->prepare( "SELECT * FROM wp_cart WHERE cartID=%s", $cartID );
    $cartRows = $wpdb->get_results( $sql );
    foreach ( $cartRows as $cartRow ) {
        $cart = json_decode( $cartRow->data );
        unset( $cart->occoupon );

        $update = json_encode( $cart );
        $wpdb->update( 'wp_cart', [ 'data' => $update ], [ 'cartID' => $cartID ] );
        $return['success'] = true;
    }

    wp_send_json( $return );
}

add_action( "wp_ajax_gpx_remove_owner_credit_coupon", "gpx_remove_owner_credit_coupon" );
add_action( "wp_ajax_nopriv_gpx_remove_owner_credit_coupon", "gpx_remove_owner_credit_coupon" );

function gpx_cpo_adjust() {
    global $wpdb;

    $propertyID = gpx_request()->request->getInt( 'propertyID' ) ?: null;
    $add = gpx_request()->request->get( 'add' );
    $cartID = gpx_request()->request->get( 'cartID' );
    $return = [ 'success' => false ];

    $cartRows = DB::table( 'wp_cart' )
                  ->where( 'cartID', '=', $cartID )
                  ->when( $propertyID, fn( $query ) => $query->where( 'propertyID', '=', $propertyID ) )
                  ->get()
                  ->toArray();

    foreach ( $cartRows as $cartRow ) {
        $cart = json_decode( $cartRow->data );

        $cart->CPOPrice = '0';

        if ( $add == 'add cpo' ) {
            $cart->CPOPrice = get_option( 'gpx_fb_fee' );
        }

        $update = json_encode( $cart );
        $wpdb->update( 'wp_cart', [ 'data' => $update ], [ 'id' => $cartRow->id ] );
        $return['success'] = true;
    }
    wp_send_json( $return );
}

add_action( "wp_ajax_gpx_cpo_adjust", "gpx_cpo_adjust" );
add_action( "wp_ajax_nopriv_gpx_cpo_adjust", "gpx_cpo_adjust" );


/**
 * This function just pulls the cid user data.
 * No idea why there is resort data in here, it's skipped
 *
 * @return void
 */
function gpx_get_custom_request() {
    global $wpdb;

    $joinedTbl = map_dae_to_vest_properties();

    $return = [];

    if ( isset( $_REQUEST['cid'] ) && ! empty( $_REQUEST['cid'] ) ) {
        $user = get_userdata( $_REQUEST['cid'] );
        if ( isset( $user ) && ! empty( $user ) ) {
            require_once ABSPATH . '/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
            $gpx = new GpxRetrieve( GPXADMIN_API_URI, GPXADMIN_API_DIR );

            $usermeta = (object) array_map( function ( $a ) {
                return $a[0];
            }, get_user_meta( $_REQUEST['cid'] ) );

            $return['fname'] = $usermeta->FirstName1;
            $return['lname'] = $usermeta->LastName1;
            $return['daememberno'] = $usermeta->DAEMemberNo;
            $return['phone'] = $usermeta->DayPhone;
            $return['mobile'] = $usermeta->Mobile1;
            $return['email'] = $usermeta->user_email;
            if ( empty( $return['email'] ) ) {
                $return['email'] = $usermeta->Email;
            }
        }
    }

    $getdate = '';

    if ( isset( $_REQUEST['pid'] ) && ! empty( $_REQUEST['pid'] ) ) {
        if ( substr( $_REQUEST['pid'], 0, 1 ) == "R" ) {
            $sql = $wpdb->prepare( "SELECT Country, Region, Town, ResortName
                    FROM wp_resorts
                    WHERE ResortID=%s AND active=1",
                                   $_REQUEST['pid'] );
        } else {
            $sql = $wpdb->prepare( "SELECT
                        " . implode( ', ', $joinedTbl['joinRoom'] ) . ",
                        " . implode( ', ', $joinedTbl['joinResort'] ) . ",
                        " . implode( ', ', $joinedTbl['joinUnit'] ) . ",
                        " . $joinedTbl['roomTable']['alias'] . ".record_id as PID, " . $joinedTbl['resortTable']['alias'] . ".id as RID
                            FROM " . $joinedTbl['roomTable']['table'] . " " . $joinedTbl['roomTable']['alias'] . "
                    INNER JOIN " . $joinedTbl['resortTable']['table'] . " " . $joinedTbl['resortTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".resort=" . $joinedTbl['resortTable']['alias'] . " .id
                    INNER JOIN " . $joinedTbl['unitTable']['table'] . " " . $joinedTbl['unitTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".unit_type=" . $joinedTbl['unitTable']['alias'] . ".record_id
                WHERE a.request_id=%s AND b.active=1",
                                   $_REQUEST['pid'] );
            $getdate = '1';
        }
        $row = $wpdb->get_row( $sql );

        if ( ! empty( $row ) ) {
            $return['country'] = $row->Country;
            $return['region'] = $row->Region;
            $return['town'] = $row->Town;
            $return['town'] = $row->ResortName; // @TODO do these keys match on purpose or is this a bug?
        }
    }

    if ( isset( $_REQUEST['rid'] ) && ! empty( $_REQUEST['rid'] ) ) {
        $sql = $wpdb->prepare( "SELECT * FROM wp_gpxCustomRequest WHERE id=%d", $_REQUEST['rid'] );
        $row = $wpdb->get_row( $sql );

        if ( ! empty( $row ) ) {
            $date = date( 'm/d/Y', strtotime( $row->checkIn ) );
            $return['startdate'] = date( 'm/d/Y 00:00', strtotime( $row->checkIn ) );
            if ( ! empty( $row->checkIn2 ) ) {
                $date .= " - " . date( 'm/d/Y', strtotime( $row->checkIn2 ) );
                $return['enddate'] = date( 'm/d/Y 00:00', strtotime( $row->checkIn2 ) );
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


    if ( ! empty( $getdate ) && isset( $row->checkIn ) ) {
        $return['dateFrom'] = date( 'm/d/Y', strtotime( $row->checkIn ) );
    }

    wp_send_json( $return );
}

add_action( "wp_ajax_gpx_get_custom_request", "gpx_get_custom_request" );
add_action( "wp_ajax_nopriv_gpx_get_custom_request", "gpx_get_custom_request" );

/**
 *
 *
 */
function gpx_apply_discount() {
    global $wpdb;

    $cartID = gpx_request()->request->get( 'cartID' );
    $sql = $wpdb->prepare( "SELECT * FROM wp_cart WHERE cartID=%s", $cartID );
    $cartRow = $wpdb->get_row( $sql );
    $cart = json_decode( $cartRow->data );

    $cid = $cart->user;

    $usermeta = (object) array_map( function ( $a ) {
        return $a[0];
    }, get_user_meta( $cid ) );

    $credit = $usermeta->daeCredit * .01;

    $cart->credit = $credit;

    $update = json_encode( $cart );
    $wpdb->update( 'wp_cart', [ 'data' => $update ], [ 'cartID' => $cartID ] );
    $return = [ 'success' => true ];

    wp_send_json( $return );
}

add_action( "wp_ajax_gpx_apply_discount", "gpx_apply_discount" );
add_action( "wp_ajax_nopriv_gpx_apply_discount", "gpx_apply_discount" );

/**
 *  Accepts the post request for the custom request form
 *
 */
function gpx_post_custom_request() {
    global $wpdb;

    $dateRanges = json_decode( stripslashes( $_POST['00N40000003DG5P'] ) );
    $_POST['00N40000003DG5P'] = date( 'm/d/Y', strtotime( $dateRanges->start ) );
    $_POST['00N40000003DG5Q'] = date( 'm/d/Y', strtotime( $dateRanges->end ) );


    //add to database
    $dbFields = [
        //         '00N40000003DG5T'=>'country',
        //         '00N40000003DG5Y'=>'state',
        '00N40000003S58X' => 'region',
        '00N40000003DG5S' => 'city',
        '00N40000003DG59' => 'resort',
        '00N40000003DG5P' => 'checkIn',
        '00N40000003DG5Q' => 'checkIn2',
        '00N40000003DG5R' => 'checkIn3',
        '00N40000003DG4w' => 'emsID',
        '00N40000003DGST' => 'firstName',
        '00N40000003DGSO' => 'lastName',
        '00N40000003DG50' => 'email',
        '00N40000002yyD8' => 'phone',
        '00N40000002yyDD' => 'mobile',
        '00N40000003DG5X' => 'ada',
        '00N40000003DG56' => 'adults',
        '00N40000003DG57' => 'children',
        '00N40000003DG54' => 'roomType',
        '00N40000003DG51' => 'comments',
        'miles' => 'miles',
        'preference' => 'preference',
        'larger' => 'larger',
        'nearby' => 'nearby',
    ];

    foreach ( $_POST as $pk => $pv ) {
        if ( empty( $pk ) || ! array_key_exists( $pk, $dbFields ) ) {
            continue;
        }
        $db[ $dbFields[ $pk ] ] = $pv;
    }
    $userType = 'Owner';

    $loggedinuser = get_current_user_id();

    $cid = gpx_get_switch_user_cookie();

    if ( $loggedinuser != $cid ) {
        $userType = 'Agent';
    }

    $db['who'] = $userType;

    $user = get_userdata( $cid );
    $usermeta = (object) array_map( function ( $a ) {
        return $a[0];
    }, get_user_meta( $cid ) );

    if ( isset( $usermeta->GP_Preferred ) && $usermeta->GP_Preferred == 'Yes' ) {
        $db['BOD'] = 1;
    }

    $sql = $wpdb->prepare( "SELECT COUNT(id) as holds FROM wp_gpxPreHold WHERE user=%d AND released='0'", $cid );
    $holdcount = $wpdb->get_var( $sql );

    $sql = $wpdb->prepare(
        "SELECT SUM(credit_amount) AS total_credit_amount, SUM(credit_used) AS total_credit_used
        FROM wp_credit
        WHERE owner_id IN (SELECT gpx_user_id FROM wp_mapuser2oid WHERE gpx_user_id = %d)
        AND (credit_expiration_date IS NULL OR credit_expiration_date > %s)",
        [ $cid, date( 'Y-m-d' ) ]
    );
    $credit = $wpdb->get_row( $sql );

    $credits = $credit->total_credit_amount - $credit->total_credit_used - $crs;

    $sql = $wpdb->prepare( "SELECT * FROM wp_gpxCustomRequest
                    WHERE active=1 AND (emsID=%s OR userID=%d)
                    AND who='Owner'",
                           [ $usermeta->DAEMemberNo, $cid ] );
    $checkCustomRequests = $wpdb->get_results( $sql );

    if ( ! empty( $checkCustomRequests ) ) {
        $holdcount += count( $checkCustomRequests );
    }

    //return true if credits+1 is greater than holds
    if ( isset( $credits ) && ( $credits + 1 > $holdcount ) ) {
        //we're good we can continue holding this
    } else {
        $holderror = [ 'success' => true, 'holderror' => get_option( 'gpx_hold_error_message' ) ];
        /*
                 * todo:  turn this on
                 */
        wp_send_json( $holderror );
    }

    $matches = custom_request_match( $db );

    if ( ! empty( $matches ) ) {
        foreach ( $matches as $matchKey => $match ) {
            if ( $matchKey == 'restricted' ) {
                if ( $match == 'All Restricted' ) {
                    $db['active'] = '0';
                    $db['forCron'] = '0';
                }
                continue;
            }
            $matchedID[] = $match->PID;
        }

        if ( isset( $matchedID ) && ! empty( $matchedID ) ) {
            $db['matched'] = implode( ",", $matchedID );
            $db['active'] = '0';
            if ( ! isset( $_POST['crID'] ) || ( isset( $_POST['crID'] ) && empty( $_POST['crID'] ) ) ) {
                $db['matchedOnSubmission'] = '1';
            }
        } elseif ( ! isset( $db['forCron'] ) ) {
            $db['forCron'] = 1;
        }
    } elseif ( ! isset( $db['forCron'] ) ) {
        $db['forCron'] = 1;
    }


    $dbCheck = $db;
    unset( $dbCheck['who'] );

    //adjust the query based on what they selected resort doesn't need region or city
    if ( isset( $dbCheck['resort'] ) ) {
        unset( $dbCheck['city'] );
        unset( $dbCheck['region'] );
    } elseif ( isset( $dbCheck['city'] ) ) {
        unset( $dbCheck['region'] );
    }
    $db['userID'] = $cid;

    if ( isset( $_POST['crID'] ) && ! empty( $_POST['crID'] ) ) {
        $lastID = $_POST['crID'];
        unset( $_POST['crID'] );
        $wpdb->update( 'wp_gpxCustomRequest', $db, [ 'id' => $lastID ] );
    } else {
        $query = DB::table( 'wp_gpxCustomRequest' )->select( 'id' );
        foreach ( $dbCheck as $key => $value ) {
            $query->where( $key, "=", $value );
        }
        $exist = $query->first();

        if ( $exist ) {
            $lastID = $exist->id;
        }

        if ( ! empty( $db ) && ! isset( $lastID ) ) {
            $wpdb->insert( 'wp_gpxCustomRequest', $db );
            $lastID = $wpdb->insert_id;
        }
    }

    if ( isset( $matches[0] ) && ! empty( $matches[0] ) ) {
        $matches['matched'] = $lastID;
    }

    $matches['success'] = true;
    wp_send_json( $matches );
}

add_action( "wp_ajax_gpx_post_custom_request", "gpx_post_custom_request" );
add_action( "wp_ajax_nopriv_gpx_post_custom_request", "gpx_post_custom_request" );

function gpx_fast_populate() {
    $cid = gpx_get_switch_user_cookie();

    $user = get_userdata( $cid );

    if ( isset( $user ) && ! empty( $user ) ) {
        $usermeta = (object) array_map( function ( $a ) {
            return $a[0];
        }, get_user_meta( $cid ) );
    }

    $return = [
        'billing_address' => $usermeta->Address1,
        'billing_city' => $usermeta->Address3,
        'billing_state' => $usermeta->Address4,
        'billing_zip' => $usermeta->PostCode,
        'biling_country' => $usermeta->Address5,
        'billing_email' => $usermeta->email,
        'billing_cardholder' => $usermeta->FirstName1 . " " . $usermeta->LastName1,
    ];

    wp_send_json( $return );
}

add_action( "wp_ajax_gpx_fast_populate", "gpx_fast_populate" );
add_action( "wp_ajax_gpx_fast_populate", "gpx_fast_populate" );

function gpx_book_link_savesearch() {
    if ( is_user_logged_in() ) {
        $save = save_search_book( $_POST );
    }

    $return['success'] = true;

    wp_send_json( $return );
}

add_action( "wp_ajax_gpx_book_link_savesearch", "gpx_book_link_savesearch" );
add_action( "wp_ajax_nopriv_gpx_book_link_savesearch", "gpx_book_link_savesearch" );

function gpx_resort_link_savesearch() {
    $save = save_search_resort( '', $_POST );

    $return['success'] = true;

    wp_send_json( $return );
}

add_action( "wp_ajax_gpx_resort_link_savesearch", "gpx_resort_link_savesearch" );
add_action( "wp_ajax_nopriv_gpx_resort_link_savesearch", "gpx_resort_link_savesearch" );

function gpx_display_featured_resorts_sc( $atts = '' ) {
    global $wpdb;

    $atts = shortcode_atts( [ 'location' => 'home', 'start' => '0', 'get' => '6' ], $atts );
    extract( $atts );

    $return = $get + 1;
    $sql = $wpdb->prepare( "SELECT * FROM wp_resorts WHERE featured='1' AND active=1 LIMIT %d, %d",
                           [ $start, $return ] );
    $props = $wpdb->get_results( $sql );

    echo '<ul class="w-list w-list-items">';
    $i = 0;
    foreach ( $props as $prop ) {
        if ( $i < $get ) // only return $get
        {
            include( 'templates/sc-featrued-destination-' . $location . '.php' );
        }
        $i ++;
    }

    echo '</ul>';
    $getplus = $get + 1;
    if ( count( $props ) == $getplus ) {
        $start = $start + $get;
        echo '<a href="#" class="sbt-seemore" id="seemore-home" data-location="' . $location . '" data-start="' . $start . '" data-get="' . $get . '"> <span>See more</span> <i class="icon-arrow-down"></i></a>';
        echo '<div class="sbt-seemore-box"></div>';
    }
}

add_shortcode( 'gpx_display_featured_resorts', 'gpx_display_featured_resorts_sc' );

function gpx_display_featured_func( $location = '', $start = '', $get = '' ) {
    global $wpdb;

    if ( empty( $location ) ) {
        extract( $_POST );
    }

    $return = $get + 1;
    $sql = $wpdb->prepare( "SELECT * FROM wp_resorts WHERE featured='1' AND active=1 LIMIT %d, %d",
                           [ $start, $return ] );
    $props = $wpdb->get_results( $sql );

    $html = '<ul class="w-list w-list-items">';
    $i = 0;
    foreach ( $props as $prop ) {
        if ( $i < $get ) // only return $get
        {
            $html .= '<li class="w-item">';
            $html .= '<div class="cnt">';
            $html .= '<a href="/resort-profile/?resort=<' . $prop->id . '">';
            $html .= '<figure><img src="<' . $prop->ImagePath1 . '" alt="<' . $prop->ResortName . '"></figure>';
            if ( $lcoation == 'resorts' ) {
                $html .= '<div calss="text">';
            }
            $html .= '<h3><' . $prop->Town . ', <' . $prop->Region . '</h3>';
            if ( $lcoation == 'resorts' ) {
                $html .= '<h4>' . $prop->Country . '</h4>';
            }
            $html .= '<p><' . $prop->ResortName . '</p>';
            if ( $lcoation == 'resorts' ) {
                $html .= '</div>';
            }
            if ( $lcoation == 'resorts' ) {
                $html .= '<a href="/resort-profile/?resort=' . $prop->id . '" class="dgt-btn">Explore</a>';
            } else {
                $html .= '<div data-link="/resort-profile/?resort=<' . $prop->id . '" class="dgt-btn sbt-btn">Explore Offer </div>';
            }
            $html .= '</a>';
            $html .= '</div>';
            $html .= '</li>';
        }
        $i ++;
    }

    $html .= '</ul>';
    $getplus = $get + 1;
    if ( count( $props ) == $getplus ) {
        $start = $start + $get;
        $html .= '<a href="#" class="sbt-seemore" id="seemore-home" data-location="' . $location . '" data-start="' . $start . '" data-get="' . $get . '"> <span>See more</span> <i class="icon-arrow-down"></i></a>';
        $html .= '<div class="sbt-seemore-box"></div>';
    }
}

add_action( "wp_ajax_gpx_display_featured_func", "gpx_display_featured_func" );
add_action( "wp_ajax_nopriv_gpx_display_featured_func", "gpx_display_featured_func" );
function gpx_change_password_with_hash_func() {
    $cid = $_POST['cid'];
    $pw1 = $_POST['chPassword'];
    $pw2 = $_POST['chPasswordConfirm'];

    $data['msg'] = 'System unavailable. Please try again later.';

    if ( $pw1 != $pw2 ) {
        $data['msg'] = 'Passwords do not match!';
    }

    $user = get_user_by( 'ID', $cid );

    if ( isset( $_POST['hash'] ) ) {
        $pass = $_POST['hash'];

        if ( $user && wp_check_password( $pass, $user->data->user_pass, $user->ID ) ) {
            $up = wp_set_password( $pw1, $user->ID );
            $data['msg'] = 'Password Updated!';
        } else {
            $data['msg'] = 'Wrong password!';
        }
    } else {
        $up = wp_set_password( $pw1, $user->ID );
        $data['msg'] = 'Password Updated!';
    }


    wp_send_json( $data );
}

add_action( "wp_ajax_gpx_change_password_with_hash", "gpx_change_password_with_hash_func" );
add_action( "wp_ajax_nopriv_gpx_change_password_with_hash", "gpx_change_password_with_hash_func" );


/**
 * Force Visual Composer to initialize as "built into the theme". This will hide certain tabs under the
 * Settings->Visual Composer page
 */
add_action( 'vc_before_init', 'your_prefix_vcSetAsTheme' );
function your_prefix_vcSetAsTheme() {
    vc_set_as_theme();
}

function add_promo_var( $vars ) {
    $vars[] = 'promo';

    return $vars;
}

add_filter( 'query_vars', 'add_promo_var', 0, 1 );
add_rewrite_rule( '^promotion/([^/]*)/?', 'index.php?page_id=229&promo=$matches[1]', 'top' );

function user_last_login( $user_login, $user ) {
    update_user_meta( $user->ID, 'last_login', time() );
    update_user_meta( $user->ID, 'searchSessionID', $user->ID . "-" . time() );
}

add_action( 'wp_login', 'user_last_login', 10, 2 );

add_action( 'after_setup_theme', 'remove_admin_bar' );

function remove_admin_bar() {
    if ( ! current_user_can( 'gpx_admin' ) ) {
        show_admin_bar( false );
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
function logout_redirect_home() {
    wp_safe_redirect( home_url() );
    exit;
}

add_action( 'wp_logout', 'logout_redirect_home' );

function my_redirect_home( $lostpassword_redirect ) {
    return home_url();
}

add_filter( 'lostpassword_redirect', 'my_redirect_home' );
add_filter( 'wpsl_meta_box_fields', 'custom_meta_box_fields' );

function custom_meta_box_fields( $meta_fields ) {
    $meta_fields[ __( 'Additional Information', 'wpsl' ) ] = [
        'phone' => [
            'label' => __( 'Tel', 'wpsl' ),
        ],
        'fax' => [
            'label' => __( 'Fax', 'wpsl' ),
        ],
        'email' => [
            'label' => __( 'Email', 'wpsl' ),
        ],
        'url' => [
            'label' => __( 'Url', 'wpsl' ),
        ],
        'resortid' => [
            'label' => __( 'Resort ID', 'wpsl' ),
        ],
        'thumbnail' => [
            'label' => __( 'Thnumbnail URI', 'wpsl' ),
        ],
    ];

    return $meta_fields;
}

function custom_templates( $templates ) {
    /**
     * The 'id' is for internal use and must be unique ( since 2.0 ).
     * The 'name' is used in the template dropdown on the settings page.
     * The 'path' points to the location of the custom template,
     * in this case the folder of your active theme.
     */
    $templates[] = [
        'id' => 'custom',
        'name' => 'Custom template',
        'path' => get_stylesheet_directory() . '/' . 'wpsl-templates/custom.php',
    ];

    return $templates;
}

add_filter( 'wpsl_templates', 'custom_templates' );


function custom_frontend_meta_fields( $store_fields ) {
    $store_fields['wpsl_thumbnail'] = [
        'name' => 'thumbnail',
        'type' => 'url',
    ];

    return $store_fields;
}

add_filter( 'wpsl_frontend_meta_fields', 'custom_frontend_meta_fields' );

function custom_info_window_template() {
    $info_window_template = '<div data-store-id="<%= id %>" class="wpsl-info-window">';
    $info_window_template .= '<div>';
    $info_window_template .= wpsl_store_header_template();
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

    if ( ! $wpsl_settings['hide_country'] ) {
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
function gpx_list_menu( $atts, $content = null ) {
    extract( shortcode_atts( [
                                 'menu' => '',
                                 'container' => 'div',
                                 'container_class' => '',
                                 'container_id' => '',
                                 'menu_class' => 'menu',
                                 'menu_id' => '',
                                 'echo' => true,
                                 'fallback_cb' => 'wp_page_menu',
                                 'before' => '',
                                 'after' => '',
                                 'link_before' => '',
                                 'link_after' => '',
                                 'depth' => 0,
                                 'walker' => '',
                                 'theme_location' => '',
                             ],
                             $atts ) );

    return wp_nav_menu( [
                            'menu' => $menu,
                            'container' => $container,
                            'container_class' => $container_class,
                            'container_id' => $container_id,
                            'menu_class' => $menu_class,
                            'menu_id' => $menu_id,
                            'echo' => false,
                            'fallback_cb' => $fallback_cb,
                            'before' => $before,
                            'after' => $after,
                            'link_before' => $link_before,
                            'link_after' => $link_after,
                            'depth' => $depth,
                            'walker' => $walker,
                            'theme_location' => $theme_location,
                        ] );
}

//Create the shortcode
add_shortcode( "gpx_listmenu", "gpx_list_menu" );

function vc_gpx_custom_menu() {
    vc_map( [
                "name" => __( "GPX Custom Menu", "gpx-website" ),
                "base" => "gpx_listmenu",
                "params" => [
                    // add params same as with any other content element
                    [
                        "type" => "textfield",
                        "heading" => __( "Menu", "gpx-website" ),
                        "param_name" => "menu",
                        "description" => __( "The menu slug.", "gp-website" ),
                    ],
                    [
                        "type" => "textfield",
                        "heading" => __( "Container Class", "gpx-website" ),
                        "param_name" => "container_class",
                        "description" => __( "Class of the container.", "gp-website" ),
                    ],
                    [
                        "type" => "textfield",
                        "heading" => __( "Container ID", "gpx-website" ),
                        "param_name" => "container_id",
                        "description" => __( "ID of the container.", "gp-website" ),
                    ],
                    [
                        "type" => "textfield",
                        "heading" => __( "Menu Class", "gpx-website" ),
                        "param_name" => "menu_class",
                        "description" => __( "Class of the menu.", "gp-website" ),
                    ],
                    [
                        "type" => "textfield",
                        "heading" => __( "Menu", "gpx-website" ),
                        "param_name" => "menu_id",
                        "description" => __( "ID of the menu.", "gp-website" ),
                    ],
                ],
            ] );
}

add_action( 'vc_before_init', 'vc_gpx_custom_menu' );
function vc_gpx_bp_terms() {
    vc_map( [
                "name" => __( "Booking Path Terms", "gpx-website" ),
                "base" => "gpx_booking_path",
                "params" => [
                    // add params same as with any other content element
                    [
                        "type" => "textarea",
                        "heading" => __( "Terms", "gpx-website" ),
                        "param_name" => "terms",
                        "description" => __( "Additional Terms & Conditions for all weeks.", "gpx-website" ),
                    ],
                ],
            ] );
}

add_action( 'vc_before_init', 'vc_gpx_bp_terms' );

function vc_gpx_bpp_terms() {
    vc_map( [
                "name" => __( "Booking Path Payment Terms", "gpx-website" ),
                "base" => "gpx_booking_path_payment",
                "params" => [
                    // add params same as with any other content element
                    [
                        "type" => "textarea",
                        "heading" => __( "Terms", "gpx-website" ),
                        "param_name" => "terms",
                        "description" => __( "Additional Terms & Conditions for all weeks.", "gpx-website" ),
                    ],
                ],
            ] );
}

add_action( 'vc_before_init', 'vc_gpx_bpp_terms' );

function vc_gpx_bpc_terms() {
    vc_map( [
                "name" => __( "Booking Path Payment Terms", "gpx-website" ),
                "base" => "gpx_booking_path_confirmation",
                "params" => [
                    // add params same as with any other content element
                    [
                        "type" => "textarea",
                        "heading" => __( "Terms", "gpx-website" ),
                        "param_name" => "terms",
                        "description" => __( "Additional Terms & Conditions for all weeks.", "gpx-website" ),
                    ],
                ],
            ] );
}

add_action( 'vc_before_init', 'vc_gpx_bpc_terms' );


function desitnations_custom_post_type() {
    // Set UI labels for Custom Post Type
    $labels = [
        'name' => _x( 'Destinations', 'Post Type General Name', 'gpx-dst' ),
        'singular_name' => _x( 'Destination', 'Post Type Singular Name', 'gpx-dst' ),
        'menu_name' => __( 'Destinations', 'gpx-dst' ),
        'parent_item_colon' => __( 'Parent Destination', 'gpx-dst' ),
        'all_items' => __( 'All Destinations', 'gpx-dst' ),
        'view_item' => __( 'View Destination', 'gpx-dst' ),
        'add_new_item' => __( 'Add New Destination', 'gpx-dst' ),
        'add_new' => __( 'Add New', 'gpx-dst' ),
        'edit_item' => __( 'Edit Destination', 'gpx-dst' ),
        'update_item' => __( 'Update Destination', 'gpx-dst' ),
        'search_items' => __( 'Search Destination', 'gpx-dst' ),
        'not_found' => __( 'Not Found', 'gpx-dst' ),
        'not_found_in_trash' => __( 'Not found in Trash', 'gpx-dst' ),
    ];

    // Set other options for Custom Post Type

    $args = [
        'label' => __( 'Destinations', 'gpx-dst' ),
        'description' => __( 'Destinations', 'gpx-dst' ),
        'labels' => $labels,
        // Features this CPT supports in Post Editor
        'supports' => [ 'title', 'editor', 'thumbnail', 'page-attributes' ],
        /* A hierarchical CPT is like Pages and can have
         * Parent and child items. A non-hierarchical CPT
    * is like Posts.
    */
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 12,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'map_meta_cap' => true,
        'capability_type' => 'destinations',
    ];

    // Registering your Custom Post Type
    register_post_type( 'destinations', $args );
}

add_action( 'init', 'desitnations_custom_post_type', 0 );

function destinations_meta_boxes( $meta_boxes ) {
    $meta_boxes[] = [
        'title' => __( 'Destination', 'gpx-dst' ),
        'post_types' => 'destinations',
        'fields' => [
            [
                'id' => 'gpx-destination-link',
                'name' => __( 'Destination (Region Name )', 'gpx-dst' ),
                'type' => 'text',
            ],
            [
                'id' => 'gpx-destination-blog-link',
                'name' => __( 'Other Link (link to a page other than a destination page -- leave blank to link to destination)',
                              'gpx-dst' ),
                'type' => 'post',
                'post_type' => [
                    'post',
                    'page',
                ],
                //                 'field_type' => 'select',
            ],
            [
                'id' => 'gpx-destination-link-text',
                'name' => __( 'Button Text', 'gpx-dst' ),
                'type' => 'text',
            ],
        ],
    ];

    return $meta_boxes;
}

add_filter( 'rwmb_meta_boxes', 'destinations_meta_boxes' );

function gpx_shared_media_custom_post_type() {
    // Set UI labels for Custom Post Type
    $labels = [
        'name' => _x( 'Shared Media', 'Post Type General Name', 'gpx-dst' ),
        'singular_name' => _x( 'Shared Media', 'Post Type Singular Name', 'gpx-dst' ),
        'menu_name' => __( 'Shared Media', 'gpx-dst' ),
        'parent_item_colon' => __( 'Parent Media Galery', 'gpx-dst' ),
        'all_items' => __( 'All Media Galleries', 'gpx-dst' ),
        'view_item' => __( 'View Media Gallery', 'gpx-dst' ),
        'add_new_item' => __( 'Add New Media Gallery', 'gpx-dst' ),
        'add_new' => __( 'Add New Gallery', 'gpx-dst' ),
        'edit_item' => __( 'Edit Media Gallery', 'gpx-dst' ),
        'update_item' => __( 'Update Media Gallery', 'gpx-dst' ),
        'search_items' => __( 'Search Media Gallery', 'gpx-dst' ),
        'not_found' => __( 'Not Found', 'gpx-dst' ),
        'not_found_in_trash' => __( 'Not found in Trash', 'gpx-dst' ),
    ];

    // Set other options for Custom Post Type

    $args = [
        'label' => __( 'Media', 'gpx-dst' ),
        'description' => __( 'Media', 'gpx-dst' ),
        'labels' => $labels,
        // Features this CPT supports in Post Editor
        'supports' => [ 'title', 'page-attributes' ],
        /* A hierarchical CPT is like Pages and can have
     * Parent and child items. A non-hierarchical CPT
     * is like Posts.
     */
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 12,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'map_meta_cap' => true,
        'capability_type' => 'owner-shared-media',
        'menu_icon' => 'dashicons-images-alt',
    ];

    // Registering your Custom Post Type
    register_post_type( 'owner-shared-media', $args );
}

add_action( 'init', 'gpx_shared_media_custom_post_type', 0 );

function gpx_shared_media_taxonomies_cat() {
    $labels = [
        'name' => _x( 'Resorts', 'taxonomy general name' ),
        'singular_name' => _x( 'Resort', 'taxonomy singular name' ),
        'search_items' => __( 'Search Resorts' ),
        'all_items' => __( 'All Resorts' ),
        'parent_item' => __( 'Parent Resort' ),
        'parent_item_colon' => __( 'Parent Resort:' ),
        'edit_item' => __( 'Edit Resort' ),
        'update_item' => __( 'Update Resort' ),
        'add_new_item' => __( 'Add New Resort' ),
        'new_item_name' => __( 'New Resort' ),
        'menu_name' => __( 'Resorts' ),
    ];
    $args = [
        'labels' => $labels,
        'hierarchical' => true,
        'show_admin_column' => true,
    ];
    register_taxonomy( 'gpx_shared_media_resort', 'owner-shared-media', $args );
}

add_action( 'init', 'gpx_shared_media_taxonomies_cat', 0 );

function gpx_shared_media_meta_boxes( $meta_boxes ) {
    $meta_boxes[] = [
        'title' => __( 'Gallery', 'gpx-dst' ),
        'post_types' => 'owner-shared-media',
        'fields' => [
            [
                'id' => 'gpx_shared_images',
                'name' => 'Image Gallery',
                'type' => 'image_advanced',
                // Maximum image uploads.
                //                 'max_file_uploads' => 2,

                // Do not show how many images uploaded/remaining.
                'max_status' => 'false',

                // Image size that displays in the edit page.
                'image_size' => 'thumbnail',
            ],

        ],
    ];

    return $meta_boxes;
}

add_filter( 'rwmb_meta_boxes', 'gpx_shared_media_meta_boxes' );

function gpx_shared_media_remove_wp_seo_meta_box() {
    remove_meta_box( 'wpseo_meta', 'owner-shared-media', 'normal' );
}

add_action( 'add_meta_boxes', 'gpx_shared_media_remove_wp_seo_meta_box', 100 );


function set_default_display_name( $user_id ) {
    $user = get_userdata( $user_id );
    $name = sprintf( '%s %s', $user->first_name, $user->last_name );
    $args = [
        'ID' => $user_id,
        'display_name' => $name,
        'nickname' => $name,
    ];
    wp_update_user( $args );
}

add_action( 'user_register', 'set_default_display_name' );

function ice_shortcode( $atts ) {
    $at = shortcode_atts(
        [
            'class' => '',
            'loggedintext' => 'Just Cruising Along...',
            'nologgedintext' => 'Login',
        ],
        $atts );

    $html = '';

    $cid = gpx_get_switch_user_cookie();

    if ( isset( $cid ) && ! empty( $cid ) ) {
        $user = get_userdata( $cid );
        if ( isset( $user ) && ! empty( $user ) ) {
            $usermeta = (object) array_map( function ( $a ) {
                return $a[0];
            }, get_user_meta( $cid ) );
            if ( ( isset( $usermeta->ICEStore ) && $usermeta->ICEStore != 'No' ) || ! isset( $usermeta->ICEStore ) ) {
                $html = '<a href="#" class="ice-link ' . esc_attr( $at['class'] ) . '" data-cid="' . $cid . '">' . esc_attr( $at['loggedintext'] ) . '</a>';
            }
        }
    }
    if ( empty( $html ) ) {
        $html = '<a href="#" class="ice-link ' . esc_attr( $at['class'] ) . '" data-cid="">' . esc_attr( $at['nologgedintext'] ) . '</a>';
    }

    return $html;
}

add_filter( 'gform_tabindex', '__return_false' );
add_shortcode( 'ice_shortcode', 'ice_shortcode' );

function universal_search_widget_shortcode() {
    ob_start();
    include( locate_template( 'template-parts/universal-search-widget.php' ) );

    return ob_get_clean();
}

add_shortcode( 'gpx_universal_search_widget', 'universal_search_widget_shortcode' );

function perks_choose_credit() {
    return '<div class="exchange-credit"><div id="exchangeList"><div style="text-align: center;"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div></div></div>';
}

add_shortcode( 'perks_choose_credit', 'perks_choose_credit' );

function perks_choose_donation() {
    return '<div class="exchange-donate"><div id="exchangeList" data-type="donation"><div style="text-align: center;"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div></div></div>';
}

add_shortcode( 'perks_choose_donation', 'perks_choose_donation' );

function gpx_lpid_cookie() {
    if ( isset( $_POST['lpid'] ) && isset( $_POST['cid'] ) ) {
        update_user_meta( $_POST['cid'], 'lppromoid' . $_POST['lpid'], $_POST['lpid'] );
    }

    $data = [ 'success' => true ];
    wp_send_json( $data );
}

add_action( "wp_ajax_gpx_lpid_cookie", "gpx_lpid_cookie" );
add_action( "wp_ajax_nopriv_gpx_lpid_cookie", "gpx_lpid_cookie" );

function gpx_show_hold_button() {
    if ( empty( $_GET['cid'] ) ) {
        $data['hide'] = true;
    } else {
        if ( gpx_hold_check( $_GET['cid'] ) ) {
            $data['show'] = true;
        } else {
            $data['hide'] = true;
        }
    }

    wp_send_json( $data );
}

add_action( "wp_ajax_gpx_show_hold_button", "gpx_show_hold_button" );
add_action( "wp_ajax_nopriv_gpx_show_hold_button", "gpx_show_hold_button" );

add_action( 'init', function () {
    // this is a plugin that is no longer available
    // register a shortcode that returns an empty string
    // so the shortcode does not show up in page content
    // if the plugin is not active
    if ( ! is_plugin_active( 'websitetourbuilder' ) ) {
        add_shortcode( 'websitetour', function () {
            return '';
        } );
    }
} );
