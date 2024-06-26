<?php
/**
 * @package WordPress DGT
 * @since   DGT Alliance 2.0
 */

use GPX\Model\Region;
use GPX\Model\Special;
use GPX\Model\PreHold;
use GPX\Model\UserMeta;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use GPX\Model\CustomRequest;
use Doctrine\DBAL\Connection;
use GPX\Model\Week;
use GPX\Repository\RegionRepository;
use GPX\Form\CustomRequestForm;
use GPX\Model\CustomRequestMatch;
use GPX\Repository\WeekRepository;
use GPX\Repository\OwnerRepository;
use GPX\Repository\IntervalRepository;
use GPX\Repository\CustomRequestRepository;
use Illuminate\Database\Eloquent\Collection;

date_default_timezone_set('America/Los_Angeles');

define('GPX_THEME_VERSION', '5.00');
if (!defined('GPXADMIN_THEME_DIR')) define('GPXADMIN_THEME_DIR', __DIR__);

require_once __DIR__ . '/models/gpxmodel.php';

if (!function_exists('gpx_theme_setup')) :
    function gpx_theme_setup() {
        load_theme_textdomain('gpx');

        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');
        // add_theme_support ( 'post-thumbnails', array* 'wpsl_stores' ) );
        add_theme_support('title-tag');

        add_theme_support('post-thumbnails');
        set_post_thumbnail_size(1200, 9999);

        /*
     * Switch default core markup for search form, comment form, and comments
     * to output valid HTML5.
     */
        add_theme_support('html5', [
            'search-form',
            'gallery',
            'caption',
        ]);

        add_theme_support('post-formats', [
            'aside',
            'image',
            'video',
            'quote',
            'link',
            'gallery',
            'status',
            'audio',
            'chat',
        ]);
    }
endif;
add_action('after_setup_theme', 'gpx_theme_setup');

// Add hook for admin <head></head>
add_action('admin_head', 'gpx_add_recaptcha_site_key');
// Add hook for front-end <head></head>
add_action('wp_head', 'gpx_add_recaptcha_site_key');

function gpx_add_recaptcha_site_key() {
    echo '<script>window.RECAPTCHA_SITE_KEY = ' . json_encode(GPX_RECAPTCHA_V3_SITE_KEY) . ';</script>';
}

add_action('wp_head', function () {
    get_template_part('template-parts/header', 'scripts');
});

add_action('wp_footer', function () {
    get_template_part('template-parts/footer', 'scripts');
});

add_action('wp_enqueue_scripts', function () {
    // enqueue Main styles
    $css_directory_uri = get_template_directory_uri() . '/css/';
    wp_register_style('jquery-ui', 'https://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css');
    wp_enqueue_style('jquery-ui');
    wp_register_style('sumoselect', $css_directory_uri . 'sumoselect.css', [], GPX_THEME_VERSION, 'all');
    wp_enqueue_style('sumoselect');
    wp_register_style('dialog', 'https://cdnjs.cloudflare.com/ajax/libs/dialog-polyfill/0.5.6/dialog-polyfill.min.css', [], '0.5.6', 'all');
    wp_register_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', [], '4.0.13', 'all');
    wp_register_style('main', gpx_asset('app.css'), ['dialog', 'select2'], GPX_THEME_VERSION, 'all');
    wp_enqueue_style('main');
    wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css');
    if (is_homepage()) :
        wp_register_style('home', gpx_asset('home.css'), ['main'], GPX_THEME_VERSION, 'all');
        wp_enqueue_style('home');
    else:
        wp_register_style('inner', gpx_asset('inner.css'), ['main'], GPX_THEME_VERSION, 'all');
        wp_enqueue_style('inner');
    endif;

    if (is_page(['view-profile'])):
        wp_register_style('data-table', $css_directory_uri . 'jquery.dataTables.min.css', [], GPX_THEME_VERSION, 'all');
        wp_enqueue_style('data-table');
        wp_register_style('data-table-responsive', $css_directory_uri . 'dataTables.responsive.css', [], GPX_THEME_VERSION, 'all');
        wp_enqueue_style('data-table-responsive');
    endif;

    if (is_singular(['offer'])):
        wp_register_style('pagex', $css_directory_uri . 'pagex.css', [], GPX_THEME_VERSION, 'all');
    endif;

    wp_enqueue_style('daterange-picker', $css_directory_uri . 'daterange-picker.css', [], GPX_THEME_VERSION, 'all');
    wp_enqueue_style('slick-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css', [], GPX_THEME_VERSION, 'all');
    wp_enqueue_style('ada', $css_directory_uri . 'ada.css', [], GPX_THEME_VERSION, 'all');
    wp_enqueue_style('ice', $css_directory_uri . 'ice.css', [], GPX_THEME_VERSION, 'all');
    wp_enqueue_style('custom', gpx_asset('custom.css'), ['main'], GPX_THEME_VERSION, 'all');
});

add_action('wp_enqueue_scripts', function () {
    global $post;
    $js_directory_uri = get_template_directory_uri() . '/js/';
    wp_register_script('jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js', ['jquery']);
    wp_register_script('royalslider', $js_directory_uri . 'jquery.royalslider.custom.min.js', ['jquery'], '9.5.7', true);
    wp_register_script('sumoselect', $js_directory_uri . 'jquery.sumoselect.min.js', ['jquery'], '3.0.21', true);
    wp_register_script('material-form', $js_directory_uri . 'jquery.material.form.min.js', ['jquery'], '1.0', true);
    wp_register_script('polyfill', 'https://polyfill.io/v3/polyfill.min.js?features=Array.prototype.find%2CElement.prototype.classList%2CObject.assign%2CElement.prototype.dataset%2CNodeList.prototype.forEach%2CElement.prototype.closest%2CString.prototype.endsWith', [], time(), false);
    wp_register_script('dialog', 'https://cdnjs.cloudflare.com/ajax/libs/dialog-polyfill/0.5.6/dialog-polyfill.min.js', [], '0.5.6', true);
    wp_register_script('alpine', 'https://cdn.jsdelivr.net/npm/alpinejs@3.13.0/dist/cdn.min.js', [], '3.13.0', [
        'strategy' => 'defer',
        'in_footer' => true,
    ]);
    wp_register_script('axios', 'https://cdnjs.cloudflare.com/ajax/libs/axios/1.5.0/axios.min.js', [], '1.5.0', true);
    wp_register_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js', ['jquery'], '4.0.13', true);
    wp_register_script('cookie', 'https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.3/js.cookie.min.js', [], '2.1.3', true);
    wp_register_script('modal', $js_directory_uri . 'modal.js', ['dialog', 'polyfill',], GPX_THEME_VERSION, true);
    wp_register_script('custom-request', $js_directory_uri . 'custom-request.js', [
        'modal',
        'jquery',
        'axios',
        'wp-util',
    ], GPX_THEME_VERSION, true);
    wp_register_script('runtime', gpx_asset('runtime.js'), ['polyfill'], GPX_THEME_VERSION, true);
    wp_register_script('app', gpx_asset('app.js'), ['runtime'], GPX_THEME_VERSION, true);
    wp_register_script('main', $js_directory_uri . 'main.js', [
        'jquery',
        'modal',
        'select2',
        'custom-request',
        'app',
        'alpine',
        'axios',
        'cookie',
        'moment',
    ], GPX_THEME_VERSION, true);
    wp_register_script('profile', gpx_asset('profile.js'), ['main', 'polyfill', 'axios'], GPX_THEME_VERSION, true);
    wp_register_script('ice', $js_directory_uri . 'ice.js', ['jquery'], GPX_THEME_VERSION, true);
    wp_register_script('gpx-custom-select', $js_directory_uri . 'custom-select.js', [], GPX_THEME_VERSION, true);
    wp_enqueue_script('jquery');
    if (is_page(97)) {
        wp_enqueue_script('jquery_ui');
    }
    // 		else
    wp_register_script('gpx_cookies', $js_directory_uri . 'gpx_cookies.js', [
        'jquery',
        'cookie',
    ], GPX_THEME_VERSION, true);
    wp_enqueue_script('jquery_ui-core');
    wp_enqueue_script('jquery-ui-autocomplete');
    wp_enqueue_script('royalslider');
    wp_enqueue_script('sumoselect');
    wp_enqueue_script('material-form');

    wp_enqueue_script('jquery-tinysort', '//cdnjs.cloudflare.com/ajax/libs/tinysort/2.3.6/tinysort.min.js', ['material-form']);
    wp_enqueue_script('daterange-pickerjs', $js_directory_uri . 'jquery.daterange-picker.js', ['jquery_ui'], '1.0', true);
    wp_enqueue_script('slick-js', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js', ['jquery_ui'], '1.0', true);
    wp_enqueue_script('main');
    wp_enqueue_script('gpx_cookies');
    wp_enqueue_script('shift4');
    wp_enqueue_script('ice');

    $params = [
        'url_theme' => get_template_directory_uri(),
        'url_ajax' => admin_url("admin-ajax.php"),
    ];

    if (is_homepage()) :
        wp_register_script('scroll-magic', $js_directory_uri . 'ScrollMagic.min.js', ['jquery'], '1.0', true);
        wp_enqueue_script('scroll-magic');
        wp_enqueue_script('gpx-custom-select');
        $params['current'] = 'home';
    else:
        $params['current'] = $post->post_name;
    endif;

    wp_localize_script('main', 'gpx_base', $params);
    wp_enqueue_script('main');

    if (is_page(['view-profile'])):
        wp_register_script('data-tables', $js_directory_uri . 'jquery.dataTables.min.js', ['jquery'], '1.10.12', true);
        wp_register_script('data-tables-responsive', $js_directory_uri . 'dataTables.responsive.min.js', ['jquery'], '1.0.0', true);
        wp_enqueue_script('data-tables');
        wp_enqueue_script('data-tables-responsive');
        wp_enqueue_script('profile');
    endif;

    wp_enqueue_script('recaptchav3', 'https://www.google.com/recaptcha/api.js?render=' . GPX_RECAPTCHA_V3_SITE_KEY, ['jquery'], GPX_THEME_VERSION, true);
});

function gpx_asset(string $path = null): ?string {
    if (empty($path)) {
        return null;
    }
    static $manifest;
    if (!$manifest) {
        $file = file_exists(get_template_directory() . '/dist/manifest.json') ? file_get_contents(get_template_directory() . '/dist/manifest.json') : '{}';
        $manifest = json_decode($file, true);
    }
    if (!array_key_exists($path, $manifest)) {
        return null;
    }

    return $manifest[$path];
}

function gpx_theme_template(string $template, array $params = [], bool $echo = true): ?string {
    if (!Str::endsWith($template, '.php')) $template .= '.php';
    $__gpx_template = realpath(GPXADMIN_THEME_DIR . '/templates/' . $template);
    unset($template);
    if (!$__gpx_template || !file_exists($__gpx_template) || !Str::startsWith($__gpx_template, GPXADMIN_THEME_DIR . '/templates/')) {
        return null;
    }
    extract($params, EXTR_SKIP);
    if (array_key_exists('params', $params)) {
        $params = $params['params'];
    } else {
        unset($params);
    }
    if (!$echo) {
        ob_start();
    }
    require $__gpx_template;
    if (!$echo) {
        return ob_get_clean();
    }

    return null;
}

function gpx_theme_template_part(string $template, array $params = [], bool $echo = true): ?string {
    if (!Str::endsWith($template, '.php')) $template .= '.php';
    $__gpx_template = realpath(GPXADMIN_THEME_DIR . '/template-parts/' . $template);
    unset($template);
    if (!$__gpx_template || !file_exists($__gpx_template) || !Str::startsWith($__gpx_template, GPXADMIN_THEME_DIR . '/template-parts/')) {
        return null;
    }
    extract($params, EXTR_SKIP);
    if (array_key_exists('params', $params)) {
        $params = $params['params'];
    } else {
        unset($params);
    }
    if (!$echo) {
        ob_start();
    }
    require $__gpx_template;
    if (!$echo) {
        return ob_get_clean();
    }

    return null;
}

function onetrust_js_handle($tag, $handle, $source) {
    if ('gpx_cookies' === $handle) {
        $tag = '<script type="text/javascript" src="' . $source . '"></script>';
    }

    return $tag;
}

add_filter('script_loader_tag', 'onetrust_js_handle', 10, 3);


function gpr_onetrust_form($params = []) {
    $inputVars = [
        'data' => '',
    ];
    $atts = shortcode_atts($inputVars, $params);
    extract($atts);

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

add_shortcode('gpr_onetrust_form', 'gpr_onetrust_form');

function is_homepage() {
    return (is_front_page() || is_home()) ? true : false;
}


/**
 * Get the post type function for single.php
 */
function get_the_post_type() {
    $post = get_post();

    return !empty($post) ? $post->post_type : false;
}

/*
 * Excerpt lenght in homepage
 */
function dgt_excerpt_length($length) {
    return 15;
}

add_filter('excerpt_length', 'dgt_excerpt_length', 999);

/*
 * Ajax load
 */
add_action("wp_ajax_gpx_load_more", "gpx_load_more_fn");
add_action("wp_ajax_nopriv_gpx_load_more", "gpx_load_more_fn");

function gpx_load_more_fn() {
    switch ($_POST['type'] ?? null) {
        case 1:
            get_template_part('template-parts/featured-destinations-home');
            break;
        case 2:
            get_template_part('template-parts/result-listing-items');
            break;
        default:
            get_template_part('template-parts/resorts-listing-items');
            break;
    }
    exit();
}

function gpx_load_results_page_fn() {
    $country = '';
    $region = '';

    global $wpdb;

    $joinedTbl = map_dae_to_vest_properties();

    extract($_POST);
    $monthstart = date('Y-m-01', strtotime($select_monthyear));
    $monthend = date('Y-m-t', strtotime($select_monthyear));

    $html = '';

    $sql = $wpdb->prepare("SELECT lft, rght FROM wp_gpxRegion WHERE RegionID=%d", $select_location);
    $row = $wpdb->get_row($sql);
    $lft = $row->lft + 1;
    $sql = $wpdb->prepare("SELECT id, lft, rght FROM wp_gpxRegion WHERE lft BETWEEN %d AND %d ORDER BY lft ASC",
        [$lft, $row->rght]);
    $gpxRegions = $wpdb->get_results($sql);

    foreach ($gpxRegions as $gpxRegion) {
        $regionSet = false;
        $sql = $wpdb->prepare("SELECT
                        " . implode(', ', $joinedTbl['joinRoom']) . ",
                        " . implode(', ', $joinedTbl['joinResort']) . ",
                        " . implode(', ', $joinedTbl['joinUnit']) . ",
                        " . $joinedTbl['roomTable']['alias'] . ".record_id as PID, " . $joinedTbl['resortTable']['alias'] . ".id as RID
                            FROM " . $joinedTbl['roomTable']['table'] . " " . $joinedTbl['roomTable']['alias'] . "
                    INNER JOIN " . $joinedTbl['resortTable']['table'] . " " . $joinedTbl['resortTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".resort=" . $joinedTbl['resortTable']['alias'] . ".id
                    INNER JOIN " . $joinedTbl['unitTable']['table'] . " " . $joinedTbl['unitTable']['alias'] . " ON " . $joinedTbl['roomTable']['alias'] . ".unit_type=" . $joinedTbl['unitTable']['alias'] . ".record_id
                WHERE b.gpxRegionID=%d
                AND check_in_date BETWEEN %s AND %s
                ",
            [$gpxRegion->id, $monthstart, $monthend]);
        $rows = $wpdb->get_results($sql);

        if (!empty($rows)) {
            $cntResults = count($rows);
            $i = 1;
            foreach ($rows as $row) {
                $priceint = preg_replace("/[^0-9\.]/", "", $prop->WeekPrice);
                if ($priceint != $row->Price) {
                    $row->Price = $priceint;
                }
                $discount = '';
                $specialPrice = '';
                //are there specials?
                $sql = $wpdb->prepare("SELECT a.Properties, a.Amount, a.SpecUsage
			FROM wp_specials a
            LEFT JOIN wp_promo_meta b ON b.specialsID=a.id
            LEFT JOIN wp_resorts c ON c.id=b.foreignID
            LEFT JOIN wp_gpxRegion d ON d.id=b.foreignID
            WHERE ((c.ResortID=%s AND b.refTable='wp_resorts')
            OR d.id=%s
            OR SpecUsage='customer')
            AND DATE(NOW()) BETWEEN StartDate AND EndDate
            AND c.active=1",
                    [$row->ResortID, $row->gpxRegionID]);
                $specs = $wpdb->get_results($sql);
                if ($specs) {
                    foreach ($specs as $spec) {
                        $specialMeta = stripslashes_deep(json_decode($spec->Properties));
                        switch ($specialMeta->transactionType) {
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
                        if ($spec->Amount > $discount && $transactionType == $row->WeekType) {
                            $discount = $spec->Amount;
                            $discountType = $specialMeta->promoType;
                            if ($discountType == 'Pct Off') {
                                $specialPrice = number_format($row->Price * (1 - ($discount / 100)), 2);
                            } elseif ($discountType == 'Dollar Off') {
                                $specialPrice = $row->Price - $discount;
                            } elseif ($discount < $row->Price) {
                                $specialPrice = $discount;
                            }
                            if ($specialPrice < 0) {
                                $specialPrice = '0.00';
                            }
                        }
                    }
                }

                if (!$regionSet) {
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
                                				<span class="date-result" >' . date('F',
                            strtotime($monthstart)) . ' ' . date('Y',
                            strtotime($monthstart)) . '</span>
                                			</div>
                                		</div>
                                	</div>
                                </div>
                                <ul id="gpx-listing-result" class="w-list-result">';
                }
                $html .= '<li class="item-result';
                if (!empty($specialPrice)) {
                    $html .= ' active';
                }
                $html .= '">
                            	<div class="w-cnt-result">
                            		<div class="result-head">
                            			';
                $pricesplit = explode(" ", $row->WeekPrice);
                $nopriceint = str_replace($priceint, "", $prop->WeekPrice);
                if (empty($specialPrice)) {
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
                if ($i == $cntResults) {
                    $html .= '</ul></li>';
                }
                $i++;
                $regionSet = true;
            }
        }
    }

    $output = ['html' => $html];

    wp_send_json($output);
}

add_action("wp_ajax_gpx_load_results_page_fn", "gpx_load_results_page_fn");
add_action("wp_ajax_nopriv_gpx_load_results_page_fn", "gpx_load_results_page_fn");

function update_username() {
    global $wpdb;
    $data = [];

    if (isset($_POST['modal_username'])) {
        $pw1 = trim($_POST['user_pass']);
        $pw2 = trim($_POST['user_pass_repeat']);
        $username_raw = $_POST['modal_username'];
        $username_clean = sanitize_user($username_raw, true);
        $wh_cleaned = sanitize_text_field($_POST['wh']);

        if (isset($_POST['wh'])) {
            $userID = Arr::first(
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

            if (empty($userID)) {
                $data['msg'] = 'Invalid Request.  Please contact us to create your account.';
            }
        } else {
            $userID = get_current_user_id();
        }

        // don't allow emails
        if (is_email($username_raw)) {
            $data['msg'] = 'Please choose a unique username that is not an email address.';
        }

        // make sure passwords match
        if ($pw1 != $pw2) {
            $data['msg'] = 'Passwords do not match!';
        } elseif (username_exists($username_raw)) {
            //is this their account?
            $data['msg'] = 'That username is already in use.  Please choose a different username.';
        }

        // usernames only valid char
        if (preg_match("/[^A-Za-z0-9]/", $username_raw)) {
            $data['msg'] = 'Username can only contain upper and lower case characters or numbers.';
        }

        //  validate min 6 chars
        if (strlen($username_clean) < 6) {
            $data['msg'] = 'Username must be at least 6 characters.';
        }

        // valid password char
        $pw1_clean = sanitize_text_field($pw1);

        if ($pw1_clean != $pw1) {
            $data['msg'] = 'Password contains invalid characters. Please try again.';
        }
        //  validate min 6 chars
        if (strlen($pw1_clean) < 8) {
            $data['msg'] = 'Password must be at least 8 characters.';
        }

        if (empty($data)) {
            $up = wp_set_password($pw1, $userID);

            $wpdb->update('wp_users', ['user_login' => $username_clean], ['ID' => $userID]);
            // removed the user meta for the token after the updates is complete
            // security issue to leave this in the database
            // ticket #1925
            delete_user_meta($userID, 'gpx_upl_hash');
            update_user_meta($userID, 'gpx_upl', '1');

            $wpdb->update('wp_GPR_Owner_ID__c', ['welcome_email_sent' => 1], ['user_id' => $userID]);
            $data['success'] = true;
            $data['msg'] = 'Updated';
        }
    }

    wp_send_json($data);
}

add_action('wp_ajax_update_username', 'update_username');
add_action('wp_ajax_nopriv_update_username', 'update_username');

function gpx_pw_reset_fn() {
    header("access-control-allow-origin: *");
    $credentials = [];
    if (isset($_POST['user_email'])) {
        $userlogin = $_POST['user_email'];
    }
    if (isset($_POST['user_login'])) {
        $userlogin = $_POST['user_login'];
    }
    if (isset($_POST['user_login_pwreset'])) {
        $userlogin = $_POST['user_login_pwreset'];
    }
    $credentials['user_login'] = isset($userlogin) ? trim($userlogin) : '';
    $user_signon = wp_signon($credentials, true);
    $pwreset = retrieve_password();
    status_header(200);
    if (is_wp_error($pwreset)) {
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
    wp_send_json($user_signon_response);
}

add_action("wp_ajax_gpx_pw_reset", "gpx_pw_reset_fn");
add_action("wp_ajax_nopriv_gpx_pw_reset", "gpx_pw_reset_fn");

function gpx_autocomplete_location_sub_fn() {
    global $wpdb;

    $parent = gpx_request('region', '');
    $term = gpx_request('term');
    $filter = gpx_search_string($term);


    if (!empty($parent)) {
        $region = RegionRepository::instance()->findRegion($parent);

        $sql = $wpdb->prepare("SELECT DISTINCT IF(IFNULL(displayName, '') != '', displayName, IF(IFNULL(subName, '') != '', subName, IF(IFNULL(name, '') != '', name, ''))) as region from wp_gpxRegion WHERE lft > %d AND rght < %d and ddHidden = 0 AND search_name LIKE %s ORDER BY region",
            [$region->lft, $region->rght, '%' . $wpdb->esc_like($filter) . '%']);
        $locations = $wpdb->get_col($sql);

        wp_send_json($locations);
    }

    $locations = ['Mexico', 'Caribbean'];
    $sql = $wpdb->prepare("SELECT DISTINCT IF(IFNULL(displayName, '') != '', displayName, IF(IFNULL(subName, '') != '', subName, IF(IFNULL(name, '') != '', name, ''))) as region FROM wp_gpxRegion WHERE ddHidden = 0 AND name != 'All' AND search_name LIKE %s ORDER BY region", '%' . $wpdb->esc_like($filter) . '%');
    $regions = $wpdb->get_col($sql);
    $locations = array_merge($locations, $regions);

    $sql = $wpdb->prepare("SELECT country FROM wp_gpxCategory WHERE country != 'USA' AND search_name LIKE %s ORDER BY country", '%' . $wpdb->esc_like($filter) . '%');
    $countries = $wpdb->get_col($sql);
    $locations = array_merge($locations, $countries);

    sort($locations);
    wp_send_json($locations);
}

add_action("wp_ajax_gpx_autocomplete_location_sub", "gpx_autocomplete_location_sub_fn");
add_action("wp_ajax_nopriv_gpx_autocomplete_location_sub", "gpx_autocomplete_location_sub_fn");

function gpx_autocomplete_location_resort_fn() {
    global $wpdb;
    $term = gpx_request('term', '');
    $region = gpx_request('region', '');

    if (!empty($region)) {
        gpx_autocomplete_region();
    }

    $filter = gpx_search_string($term);
    $sql = $wpdb->prepare("SELECT ResortName FROM wp_resorts WHERE active=1 AND search_name LIKE %s ORDER BY ResortName", '%' . $wpdb->esc_like($filter) . '%');
    $resorts = $wpdb->get_col($sql);
    wp_send_json($resorts);
}

function gpx_autocomplete_region() {
    global $wpdb;
    $term = gpx_request('region', '');
    $filter = gpx_search_string($term);
    $regions = [];
    $sql = $wpdb->prepare("SELECT name FROM wp_gpxRegion WHERE search_name LIKE %s", '%' . $wpdb->esc_like($filter) . '%');
    $rows = $wpdb->get_results($sql);
    foreach ($rows as $row) {
        $sql = $wpdb->prepare("SELECT gpxRegionID, ResortName from wp_gpxRegion a
                    INNER JOIN wp_resorts b ON a.id=b.gpxRegionID
                    WHERE lft  BETWEEN %d AND %d and ddHidden = '0'",
            [$row->lft, $row->rght]);
        $cities = $wpdb->get_results($sql);

        foreach ($cities as $city) {
            $regions[] = $city->ResortName;
        }
    }
    $regions = array_unique($regions, SORT_STRING);
    sort($regions, SORT_STRING);
    wp_send_json($regions);
}

add_action("wp_ajax_gpx_autocomplete_location_resort", "gpx_autocomplete_location_resort_fn");
add_action("wp_ajax_nopriv_gpx_autocomplete_location_resort", "gpx_autocomplete_location_resort_fn");

function gpx_autocomplete_sr_location() {
    global $wpdb;
    $term = gpx_request('term', '');

    if (empty($term)) {
        $sql = "SELECT DISTINCT IF(IFNULL(displayName, '') != '', displayName, IF(IFNULL(subName, '') != '', subName, IF(IFNULL(name, '') != '', name, ''))) as region FROM wp_gpxRegion WHERE ddHidden = 0 AND featured = 1 ORDER BY region";
        $regions = array_map(fn($region) => [
            'category' => 'REGION',
            'label' => $region,
            'value' => $region,
        ], $wpdb->get_col($sql));
        wp_send_json($regions);
    }

    $filter = gpx_search_string($term);

    $sql = $wpdb->prepare("SELECT DISTINCT IF(IFNULL(displayName, '') != '', displayName, IF(IFNULL(subName, '') != '', subName, IF(IFNULL(name, '') != '', name, ''))) as region FROM wp_gpxRegion WHERE ddHidden = 0 AND name != 'All' AND search_name LIKE %s ORDER BY region", '%' . $wpdb->esc_like($filter) . '%');
    $regions = array_map(fn($region) => [
        'category' => 'REGION',
        'label' => $region,
        'value' => $region,
    ], $wpdb->get_col($sql));

    $sql = $wpdb->prepare("SELECT country FROM wp_gpxCategory WHERE country != 'USA' AND search_name LIKE %s ORDER BY country", '%' . $wpdb->esc_like($filter) . '%');
    $countries = array_map(fn($country) => [
        'category' => 'REGION',
        'label' => $country,
        'value' => $country,
    ], $wpdb->get_col($sql));

    $regions = array_merge($regions, $countries);
    usort($regions, fn($a, $b) => $a['value'] <=> $b['value']);

    wp_send_json($regions);
}

add_action("wp_ajax_gpx_autocomplete_sr_location", "gpx_autocomplete_sr_location");
add_action("wp_ajax_nopriv_gpx_autocomplete_sr_location", "gpx_autocomplete_sr_location");

function gpx_autocomplete_location_fn() {
    global $wpdb;
    $term = gpx_request('term', '');

    if (empty($term)) {
        $sql = "SELECT DISTINCT IF(IFNULL(displayName, '') != '', displayName, IF(IFNULL(subName, '') != '', subName, IF(IFNULL(name, '') != '', name, ''))) as region FROM wp_gpxRegion WHERE ddHidden = 0 AND featured = 1 ORDER BY region";
        $regions = array_map(fn($region) => [
            'category' => 'REGION',
            'label' => $region,
            'value' => $region,
        ], $wpdb->get_col($sql));
        wp_send_json($regions);
    }

    $filter = gpx_search_string($term);

    $sql = $wpdb->prepare("SELECT DISTINCT IF(IFNULL(displayName, '') != '', displayName, IF(IFNULL(subName, '') != '', subName, IF(IFNULL(name, '') != '', name, ''))) as region FROM wp_gpxRegion WHERE ddHidden = 0 AND name != 'All' AND search_name LIKE %s ORDER BY region", '%' . $wpdb->esc_like($filter) . '%');
    $regions = array_map(fn($region) => [
        'category' => 'REGION',
        'label' => $region,
        'value' => $region,
    ], $wpdb->get_col($sql));

    //get the regions...
    $sql = $wpdb->prepare("SELECT country FROM wp_gpxCategory WHERE country != 'USA' AND search_name LIKE %s ORDER BY country", '%' . $wpdb->esc_like($filter) . '%');
    $countries = array_map(fn($country) => [
        'category' => 'REGION',
        'label' => $country,
        'value' => $country,
    ], $wpdb->get_col($sql));

    $regions = array_merge($regions, $countries);
    usort($regions, fn($a, $b) => $a['value'] <=> $b['value']);

    //get the resorts...
    $sql = $wpdb->prepare("SELECT ResortName FROM wp_resorts WHERE active='1' AND search_name LIKE %s ORDER BY ResortName", '%' . $wpdb->esc_like($filter) . '%');
    $resorts = array_map(fn($resort) => [
        'category' => 'RESORT',
        'label' => $resort,
        'value' => $resort,
    ], $wpdb->get_col($sql));

    wp_send_json(array_merge($regions, $resorts));
}

add_action("wp_ajax_gpx_autocomplete_location", "gpx_autocomplete_location_fn");
add_action("wp_ajax_nopriv_gpx_autocomplete_location", "gpx_autocomplete_location_fn");
add_action("wp_ajax_gpx_autocomplete_usw", "gpx_autocomplete_location_fn");
add_action("wp_ajax_nopriv_gpx_autocomplete_usw", "gpx_autocomplete_location_fn");

function gpx_get_location_coordinates_fn() {
    global $wpdb;

    $return = [];

    $sql = $wpdb->prepare("SELECT lng, lat FROM wp_gpxRegion WHERE (name=%s OR displayName=%s)",
        [$_POST['region'], $_POST['region']]);
    $row = $wpdb->get_row($sql);

    if ($row->lng != '0' && $row->lat != '0') {
        $return['success'] = true;
    }

    wp_send_json($return);
}

add_action("wp_ajax_gpx_get_location_coordinates", "gpx_get_location_coordinates_fn");
add_action("wp_ajax_nopriv_gpx_get_location_coordinates", "gpx_get_location_coordinates_fn");

function gpx_view_profile_sc() {
    global $wpdb;

    $cid = gpx_get_switch_user_cookie();
    $user = get_userdata($cid);
    $usermeta = UserMeta::load($cid);

    if (empty($usermeta->first_name) && !empty($usermeta->FirstName1)) {
        $usermeta->first_name = $usermeta->FirstName1;
    }

    if (empty($usermeta->last_name) && !empty($usermeta->LastName1)) {
        $usermeta->last_name = $usermeta->LastName1;
    }

    $usermeta->Email = OwnerRepository::instance()->get_email($cid);

    $dayphone = $usermeta->DayPhone;

    //set the profile columns
    $profilecols[0] = [
        [
            'placeholder' => "First Name",
            'type' => 'text',
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'first_name'],
            'required' => 'required',
        ],
        [
            'placeholder' => "Last Name",
            'type' => 'text',
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'last_name'],
            'required' => 'required',
        ],
        [
            'placeholder' => "Email",
            'type' => 'email',
            'class' => 'validate emailvalidate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'Email'],
            'required' => 'required',
        ],
        [
            'placeholder' => "Home Phone",
            'type' => 'tel',
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'DayPhone'],
            'required' => 'required',
        ],
        [
            'placeholder' => "Mobile Phone",
            'type' => 'tel',
            'class' => '',
            'value' => ['from' => 'usermeta', 'retrieve' => 'Mobile1'],
            'required' => '',
        ],
    ];
    $profilecols[1] = [
        [
            'placeholder' => "Street Address",
            'type' => 'text',
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'Address1'],
            'required' => 'required',
        ],
        [
            'placeholder' => "City",
            'type' => 'text',
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'Address3'],
            'required' => 'required',
        ],
        [
            'placeholder' => "State",
            'type' => 'text',
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'Address4'],
            'required' => 'required',
        ],
        [
            'placeholder' => "Zip",
            'type' => 'text',
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'PostCode'],
            'required' => 'required',
        ],
        [
            'placeholder' => "Country",
            'type' => 'text',
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'Address5'],
            'required' => 'required',
        ],
    ];

    if (isset($_POST['cid']) && $cid = $_POST['cid']) {
        foreach ($profilecols as $col) {
            foreach ($col as $data) {
                if ($data['value']['retrieve'] == 'Email') //update user table
                {
                    $mainData = [
                        'ID' => $cid,
                        'user_email' => $_POST[$data['value']['retrieve']],
                    ];
                    wp_update_user($mainData);
                    update_user_meta($cid, $data['value']['retrieve'], $_POST[$data['value']['retrieve']]);
                } else //update user meta
                {
                    update_user_meta($cid, $data['value']['retrieve'], $_POST[$data['value']['retrieve']]);
                }
                if ($data['value']['retrieve'] == 'user_email') {
                    update_user_meta($cid, 'Email', $_POST[$data['value']['retrieve']]);
                }
            }
        }

    }

    $sql = $wpdb->prepare("SELECT * FROM wp_gpxMemberSearch WHERE userID = %d", $cid);
    $results = $wpdb->get_results($sql);

    foreach ($results as $result) {
        $history = json_decode($result->data);
        foreach ($history as $key => $value) {
            if (isset($value->week_type)) {
                $splitKey = explode('-', $key);
                if ($splitKey[0] == 'view') {
                    $weektype = $value->week_type;
                    if ($weektype == 'BonusWeek') {
                        $weektype = 'RentalWeek';
                    }
                    $histout[$weektype][] = [
                        'weekId' => '<a href="/booking-path?book=' . esc_attr($value->id) . '">' . esc_html($value->id) . '</a>',
                        'ResortName' => '<a href="resort-profile/?resortName=' . esc_attr($value->name) . '">' . esc_html($value->name) . '</a>',
                        'Price' => '<a href="/booking-path?book=' . esc_attr($value->id) . '">' . esc_html($value->price) . '</a>',
                        'checkIn' => '<a href="/booking-path?book=' . esc_attr($value->id) . '">' . esc_html($value->checkIn) . '</a>',
                        'Size' => '<a href="/booking-path?book=' . esc_attr($value->id) . '">' . esc_html($value->beds) . '</a>',
                    ];
                }
            }
            if (isset($value->ResortName)) {
                $searched = "N/A";
                if (isset($value->search_month)) {
                    $searched = $value->search_month;
                }
                if (isset($value->search_year)) {
                    $searched .= ' ' . $value->search_year;
                }
                $histoutresort[] = [
                    'ResortName' => '<a href="/resort-profile/?resort=' . esc_attr($value->id) . '">' . esc_html($value->ResortName) . '</a>',
                    'DateViewed' => '<a href="/resort-profile/?resort=' . esc_attr($value->id) . '">' . date("m/d/Y",
                            strtotime($value->DateViewed)) . '</a>',
                    'Searched' => '<a href="/resort-profile/?resort=' . esc_attr($value->id) . '">' . esc_html($searched) . '</a>',
                ];
            }
        }
    }

    $expireDate = date('Y-m-d H:i:s');
    //get my coupons
    $sql = $wpdb->prepare("SELECT a.coupon_hash, a.used, b.Name, b.Slug, b.Properties FROM wp_gpxAutoCoupon a
            INNER JOIN wp_specials b ON a.coupon_id=b.id
            WHERE user_id=%d
            AND EndDate > %s ORDER BY used",
        [$cid, $expireDate]);
    $acs = $wpdb->get_results($sql);
    foreach ($acs as $ac) {
        $redeemed = "No";
        $promoproperties = json_decode($ac->Properties);


        if ($ac->used == '1') {
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
    $sql = $wpdb->prepare("SELECT *, a.id as cid, b.id as oid, c.id as aid, c.datetime as activity_date FROM wp_gpxOwnerCreditCoupon a
                    INNER JOIN wp_gpxOwnerCreditCoupon_owner b ON b.couponID=a.id
                    INNER JOIN wp_gpxOwnerCreditCoupon_activity c ON c.couponID=a.id
                    WHERE b.ownerID=%d",
        $cid);
    $coupons = $wpdb->get_results($sql);
    $distinctCoupon = [];
    foreach ($coupons as $coupon) {
        $distinctCoupon[$coupon->cid]['coupon'] = $coupon;
        $distinctCoupon[$coupon->cid]['activity'][$coupon->aid] = $coupon;
    }
    foreach ($distinctCoupon as $dcKey => $dc) {
        $activityDate = '0';
        foreach ($dc['activity'] as $activity) {
            if ($activity->activity == 'transaction') {
                $redeemedAmount[$dcKey][] = $activity->amount;
            } else {
                $amount[$dcKey][] = $activity->amount;
                //get the greatest date
                if ($activity->activity == 'created') {
                    $activityDate = strtotime($activity->activity_date);
                }
            }
        }
        //if activitydate <> 0 and is more than 1 year ago then we shouldn't display this coupon
        if ($activityDate != 0 && $activityDate < strtotime('-1 year')) {
            continue;
        }

        if (isset($dc['coupon']->single_use) && $dc['coupon']->single_use == 1 && array_sum($redeemedAmount[$dcKey]) > 0) {
            $balance = 0;
        } else {
            $balance[$dcKey] = array_sum($amount[$dcKey] ?? []) - array_sum($redeemedAmount[$dcKey] ?? []);
        }

        $mycreditcoupons[] = [
            'name' => $dc['coupon']->name,
            'code' => $dc['coupon']->couponcode,
            'balance' => '$' . $balance[$dcKey],
            'redeemed' => '$' . array_sum($redeemedAmount[$dcKey] ?? []),
            'active' => $dc['coupon']->active,
            'expire' => date('m/d/Y', strtotime($dc['coupon']->expirationDate)),
        ];
    }

    //get my custom requests
    $crs = CustomRequest::where('userID', '=', $cid)
                        ->enabled()
                        ->open()
                        ->orderBy('active', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();
    $i = 0;
    $customRequests = [];
    foreach ($crs as $cr) {
        $location = '<a href="#" class="edit-custom-request" data-rid="' . esc_attr($cr->id) . '" aria-label="Edit Custom Request"><i class="fa fa-eye" aria-hidden="true"></i></a> ';
        if (!empty($cr->resort)) {
            $location .= 'Resort: ' . esc_html($cr->resort);
        } elseif (!empty($cr->city)) {
            $location .= 'City: ' . esc_html($cr->city);
        } elseif (!empty($cr->region)) {
            $location .= 'Region: ' . esc_html($cr->region);
        }

        $date = $cr->checkIn->format('m/d/Y');
        if ($cr->checkIn2) {
            $date .= ' - ' . $cr->checkIn2->format('m/d/Y');
        }
        $requesteddate = $cr->datetime->format('m/d/Y');
        $found = $cr->matched ? "Yes" : 'No';
        //Request to be kept ‘visible’ even if Inactive (remove option to ‘Delete’)
        $active = 'No <a href="#" class="crActivate btn btn-secondary" data-crid="' . esc_attr($cr->id) . '" data-action="activate">Enable</a>';
        //changing back to the previous version where we had a toggle option
        if ($found == "Yes") {
            $active = 'No';
        }
        if ($cr->active) {
            $active = 'Yes';
            //Request to be kept ‘visible’ even if Inactive (remove option to ‘Delete’)
            //adding this option back in
            $active = 'Yes <a href="#" class="crActivate btn btn-secondary" data-crid="' . esc_attr($cr->id) . '" data-action="deactivate">Disable</a>';
        }
        $crObject = new CustomRequestMatch($cr);
        $matches = $crObject->get_matches();
        $matched = $matches->notRestricted()->isNotEmpty() ? 'Yes' : 'No';
        if ($matches->notRestricted()->isNotEmpty()) {
            $matchLink = ' <a class="btn btn-secondary" href="/result?custom=' . urlencode($cr->id) . '">View Results</a>';
            if ($cr->week_on_hold) {
                $crWeekType = '&type=ExchangeWeek';
                if ($cr->preference == 'Rental') {
                    $crWeekType = str_replace('Exchange', 'Rental', $crWeekType);
                }
                $matchLink = ' <a class="btn btn-secondary" href="/booking-path/?book=' . urlencode($cr->week_on_hold) . $crWeekType . '">View Results</a>';
            }
            $matched = '';
            if ($cr->matchEmail) {
                $matched .= '<span title="Notification Sent: ' . $cr->matchEmail->format('m/d/Y') . '">';
            }
            $matched .= 'Yes';
            $matched .= $matchLink;
            if ($cr->matchEmail) {
                $matched .= '</span>';
            }
        }

        $customRequests[$i]['location'] = $location;
        $customRequests[$i]['traveldate'] = $date;
        $customRequests[$i]['requesteddate'] = $requesteddate;
        $customRequests[$i]['matched'] = $matched;
        $customRequests[$i]['active'] = $active;
        $i++;
    }

    $sql = $wpdb->prepare("SELECT *  FROM `wp_GPR_Owner_ID__c` WHERE `user_id` = %d", $cid);
    $gprOwner = $wpdb->get_row($sql);

    include(get_template_directory() . '/templates/sc-view-profile.php');
}

add_shortcode('gpx_view_profile', 'gpx_view_profile_sc');

function gpx_resort_result_page_sc() {
    global $wpdb;
    if (isset($_GET['select_region'])) {
        $sql = $wpdb->prepare("SELECT name, lft, rght, id  FROM wp_gpxRegion WHERE id=%d", $_GET['select_region']);
    } elseif (isset($_GET['select_country'])) {
        $sql = $wpdb->prepare(
            "SELECT MIN(lft) as lft, MAX(rght) as rght, a.id FROM wp_gpxRegion a
                INNER JOIN wp_daeRegion b ON b.id=a.RegionID
                WHERE b.CountryID=%s",
            $_GET['select_country']
        );
    } else {
        $sql = "SELECT MIN(lft) as lft, MAX(rght) as rght FROM wp_gpxRegion";
    }
    $row = $wpdb->get_row($sql);
    $left = $row->lft;
    $sql = $wpdb->prepare("SELECT a.*, b.lft, b.rght, name FROM wp_resorts a
        INNER JOIN wp_gpxRegion b ON b.id = a.gpxRegionID
        WHERE b.lft BETWEEN %d AND %d AND a.active=1",
        [$row->lft, $row->rght]);
    $results = $wpdb->get_results($sql);

    foreach ($results as $result) {
        $subregion = [];
        $weektypes = ['', 'null', 'All'];
        $parentLft = '';
        $filterCities[$result->gpxRegionID] = $result->name;
        $sql = $wpdb->prepare("SELECT type FROM wp_room WHERE resort=%s AND active='1'", $result->ResortID);
        $rows = $wpdb->get_results($sql);
        $result->propCount = count($rows);

        foreach ($rows as $row) {
            $weektypes[$row->WeekType] = $row->WeekType;
        }
        $result->WeekType = $weektypes;
        $result->ResortType = json_encode($weektypes);
        $subregion[] = $result->gpxRegionID;
        while ($parentLft > $left) {
            $sql = $wpdb->prepare("SELECT id, lft, name FROM wp_gpxRegion WHERE id=%d", $left);
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

    usort($resorts, fn($a, $b) => $a->propCount <=> $b->propCount);

    $cid = gpx_get_switch_user_cookie();

    include(get_template_directory() . '/templates/sc-resort-result.php');
}

add_shortcode('gpx_resort_result_page', 'gpx_resort_result_page_sc');

function gpx_email_confirmation($atts) {
    global $wpdb;

    $atts = shortcode_atts(
        [
            'terms' => '',
        ],
        $atts,
        'gpx_booking_path_confirmation');

    $cid = gpx_get_switch_user_cookie();
    if (isset($_POST['confirmation'])) {
        $cartID = $_GET['confirmation'];
    }
    $sql = $wpdb->prepare("SELECT * FROM wp_gpxTransactions WHERE cartID=%s", $cartID);
    $rows = $wpdb->get_results($sql);
    $i = 0;
    if (!empty($rows)) {
        foreach ($rows as $row) {
            $user = get_userdata($cid);
            if (isset($user) && !empty($user)) {
                $usermeta = (object) array_map(function ($a) {
                    return $a[0];
                }, get_user_meta($cid));
            }

            $transactions[$i] = json_decode($row->data);

            $sql = $wpdb->prepare("SELECT * FROM wp_resorts WHERE ResortID=%s", $transactions[$i]->ResortID);
            $resort[$i] = $wpdb->get_row($sql);

            $sql = $wpdb->prepare("SELECT * FROM wp_resorts_meta WHERE ResortID=%s", $transactions[$i]->ResortID);
            $rms = $wpdb->get_results($sql);

            die(); // @TODO Jonathan: Is this here on purpose?

            foreach ($rms as $rm) {
                $rmk = $rm->meta_key;
                $resort[$i]->$rmk = $rm->meta_value;
            }

            $sql = $wpdb->prepare("SELECT id FROM wp_properties WHERE weekID = %s", $row->weekId);
            $prow = $wpdb->get_row($sql);
            if (!empty($prow)) {
                $book = $prow->id;
            } else {
                $book = $row->weekId;
            }

            $property_details[$i] = get_property_details($book, $cid);
            $i++;
        }
    }
    include(get_template_directory() . '/templates/sc-booking-path-confirmation.php');
}

add_shortcode('gpx_email_confirmation', 'gpx_email_confirmation');

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
        'active' => 'active',
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
    foreach ($mapPropertiesToRooms as $key => $value) {
        if ($key == 'noNights') {
            $output['joinRoom'][] = $value . ' as ' . $key;
        } else {
            $output['joinRoom'][] = $output['roomTable']['alias'] . '.' . $value . ' as ' . $key;
        }
    }
    foreach ($mapPropertiesToUnit as $key => $value) {
        $output['joinUnit'][] = $output['unitTable']['alias'] . '.' . $value . ' as ' . $key;
    }
    foreach ($mapPropertiesToResort as $key => $value) {
        $output['joinResort'][] = $output['resortTable']['alias'] . '.' . $value . ' as ' . $key;
    }

    return $output;
}

function promo_retrieve_each($specialMeta, $props) {}

// REMOVE WP EMOJI
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');


function custom_request_status_change() {
    global $wpdb;
    if (isset($_POST['crid'])) {
        $id = $_POST['crid'];
        $udata['active'] = '1';
        if ($_POST['craction'] == 'deactivate') {
            $udata['active'] = 0;
        }
    }
    if (isset($_REQUEST['croid'])) {
        $udata['active'] = 0;
        $split = explode("221a2d2s33d564334ne3", $_REQUEST['croid']);
        $id = $split[0];
        $emsID = $split[1];
        $sql = $wpdb->prepare("SELECT active FROM wp_gpxCustomRequest where id=%d", $id);
        $row = $wpdb->get_row($sql);
        if ($row->active == '0') {
            $udata['active'] = 1;
        }
    }

    if (isset($id)) {
        $update = $wpdb->update('wp_gpxCustomRequest', $udata, ['id' => $id]);
        $data['success'] = true;
    }
    if (isset($_REQUEST['croid'])) {
        wp_redirect(get_site_url("", "/custom-request-status-updated"));
        die;
    }

    wp_send_json($data);
}

add_action("wp_ajax_custom_request_status_change", "custom_request_status_change");
add_action("wp_ajax_nopriv_custom_request_status_change", "custom_request_status_change");

function gpx_member_dashboard_sc() {
    global $wpdb;

    $cid = gpx_get_switch_user_cookie();

    //set the profile columns
    $profilecols[0] = [
        [
            'placeholder' => "First Name",
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'FirstName1'],
            'required' => 'required',
        ],
        [
            'placeholder' => "Last Name",
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'LastName1'],
            'required' => 'required',
        ],
        [
            'placeholder' => "Email",
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'Email'],
            'required' => 'required',
        ],
        [
            'placeholder' => "Home Phone",
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'HomePhone'],
            'required' => 'required',
        ],
        [
            'placeholder' => "Mobile Phone",
            'class' => '',
            'value' => ['from' => 'usermeta', 'retrieve' => 'Mobile'],
            'required' => '',
        ],
    ];
    $profilecols[1] = [
        [
            'placeholder' => "Street Address",
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'Address1'],
            'required' => 'required',
        ],
        [
            'placeholder' => "City",
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'Address3'],
            'required' => 'required',
        ],
        [
            'placeholder' => "State",
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'Address4'],
            'required' => 'required',
        ],
        [
            'placeholder' => "Zip",
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'PostCode'],
            'required' => 'required',
        ],
        [
            'placeholder' => "Country",
            'class' => 'validate',
            'value' => ['from' => 'usermeta', 'retrieve' => 'Address5'],
            'required' => 'required',
        ],
    ];
    if (isset($_POST['cid']) && $cid = $_POST['cid']) {
        foreach ($profilecols as $col) {
            foreach ($col as $data) {
                if ($data['value']['from'] == 'user') //update user table
                {
                    $mainData = [
                        'ID' => $cid,
                        'user_email' => $_POST[$data['value']['retrieve']],
                    ];
                    wp_update_user($mainData);
                } else //update user meta
                {
                    update_user_meta($cid, $data['value']['retrieve'], $_POST[$data['value']['retrieve']]);
                }
                if ($data['value']['retrieve'] == 'user_email') {
                    update_user_meta($cid, 'Email', $_POST[$data['value']['retrieve']]);
                }
            }
        }
    }
    $user = get_userdata($cid);
    $usermeta = UserMeta::load($cid);

    $sql = $wpdb->prepare("SELECT * FROM wp_gpxMemberSearch WHERE userID=%d", $cid);
    $results = $wpdb->get_results($sql);

    $histout = [];
    foreach ($results as $result) {
        $history = json_decode($result->data);
        foreach ($history as $key => $value) {
            if (isset($value->property->WeekType)) {
                $splitKey = explode('-', $key);
                if ($splitKey[0] == 'select') {
                    $weektype = $value->property->WeekType;
                    $histsetPrice = $value->price;
                    $histdaePrice = $value->property->Price;
                    $price = $value->property->WeekPrice;
                    if ($histsetPrice < $histdaePrice) {
                        $price = '<span style="text-decoration: line-through;">' . $value->property->WeekPrice . '</span> ' . str_replace($value->property->Price,
                                $histsetPrice,
                                $value->property->WeekPrice);
                    }
                    $histout[$weektype][] = [
                        'weekId' => $value->property->weekId ?? $value->property->weekID ?? $value->property->id ?? null,
                        'ResortName' => $value->property->ResortName ?? null,
                        'Price' => $price,
                        'checkIn' => $value->property->checkIn ?? null,
                        'Size' => $value->property->Size ?? null,
                    ];
                }
            }
        }
    }

    include(get_template_directory() . '/templates/sc-member-dashboard.php');
}

add_shortcode('gpx_member_dashboard', 'gpx_member_dashboard_sc');

function vc_gpx_member_dashboard() {
    vc_map([
        "name" => __("GPX Memeber Dashboard", "gpx-website"),
        "base" => "gpx_member_dashboard",
        "params" => [
            // add params same as with any other content element
            [
                "type" => "textfield",
                "heading" => __("Extra class name", "gpx-website"),
                "param_name" => "el_class",
                "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.",
                    "my-text-domain"),
            ],
        ],
    ]);
}

add_action('vc_before_init', 'vc_gpx_member_dashboard');

function vc_gpx_locations_page() {
    vc_map([
        "name" => __("Locations Map", "gpx-website"),
        "base" => "wpsl",
        "params" => [
            // add params same as with any other content element
            [
                'type' => "dropdown",
                'heading' => __("Template", "gpx-website"),
                'param_name' => 'template',
                'description' => __("Extra templates could be added here.  Right now we are just using Default."),
                'value' => [
                    'default',
                ],
            ],
            [
                "type" => "textfield",
                "heading" => __("Extra class name", "my-text-domain"),
                "param_name" => "el_class",
                "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.",
                    "my-text-domain"),
            ],
        ],
    ]);
}

add_action('vc_before_init', 'vc_gpx_locations_page');

function gpx_fast_populate() {
    $default = [
        'billing_address' => null,
        'billing_city' => null,
        'billing_state' => null,
        'billing_zip' => null,
        'billing_country' => null,
        'billing_email' => null,
        'billing_cardholder' => null,
    ];
    $cid = gpx_get_switch_user_cookie();
    if (!$cid) {
        wp_send_json($default);
    }

    $usermeta = UserMeta::load($cid);
    wp_send_json([
        'billing_address' => $usermeta->getAddress(),
        'billing_city' => $usermeta->getCity(),
        'billing_state' => $usermeta->getState(),
        'billing_zip' => $usermeta->getPostalCode(),
        'biling_country' => $usermeta->getCountry(),
        'billing_email' => $usermeta->getEmailAddress(),
        'billing_cardholder' => $usermeta->getName(),
    ]);
}

add_action("wp_ajax_gpx_fast_populate", "gpx_fast_populate");
add_action("wp_ajax_gpx_fast_populate", "gpx_fast_populate");

function gpx_resort_link_savesearch() {
    $save = save_search_resort('', $_POST);

    $return['success'] = true;

    wp_send_json($return);
}

add_action("wp_ajax_gpx_resort_link_savesearch", "gpx_resort_link_savesearch");
add_action("wp_ajax_nopriv_gpx_resort_link_savesearch", "gpx_resort_link_savesearch");

function gpx_display_featured_resorts_sc($atts = '') {
    global $wpdb;

    $atts = shortcode_atts(['location' => 'home', 'start' => '0', 'get' => '6'], $atts);
    extract($atts);

    $return = $get + 1;
    $sql = $wpdb->prepare("SELECT * FROM wp_resorts WHERE featured='1' AND active=1 LIMIT %d, %d",
        [$start, $return]);
    $props = $wpdb->get_results($sql);

    echo '<ul class="w-list w-list-items">';
    $i = 0;
    foreach ($props as $prop) {
        if ($i < $get) // only return $get
        {
            include(get_template_directory() . '/templates/sc-featrued-destination-' . $location . '.php');
        }
        $i++;
    }

    echo '</ul>';
    $getplus = $get + 1;
    if (count($props) == $getplus) {
        $start = $start + $get;
        echo '<a href="#" class="sbt-seemore" id="seemore-home" data-location="' . $location . '" data-start="' . $start . '" data-get="' . $get . '"> <span>See more</span> <i class="icon-arrow-down"></i></a>';
        echo '<div class="sbt-seemore-box"></div>';
    }
}

add_shortcode('gpx_display_featured_resorts', 'gpx_display_featured_resorts_sc');

function gpx_display_featured_func($location = '', $start = '', $get = '') {
    global $wpdb;

    if (empty($location)) {
        extract($_POST);
    }

    $return = $get + 1;
    $sql = $wpdb->prepare("SELECT * FROM wp_resorts WHERE featured='1' AND active=1 LIMIT %d, %d",
        [$start, $return]);
    $props = $wpdb->get_results($sql);

    $html = '<ul class="w-list w-list-items">';
    $i = 0;
    foreach ($props as $prop) {
        if ($i < $get) // only return $get
        {
            $html .= '<li class="w-item">';
            $html .= '<div class="cnt">';
            $html .= '<a href="/resort-profile/?resort=<' . $prop->id . '">';
            $html .= '<figure><img src="<' . $prop->ImagePath1 . '" alt="<' . $prop->ResortName . '"></figure>';
            if ($lcoation == 'resorts') {
                $html .= '<div calss="text">';
            }
            $html .= '<h3><' . $prop->Town . ', <' . $prop->Region . '</h3>';
            if ($lcoation == 'resorts') {
                $html .= '<h4>' . $prop->Country . '</h4>';
            }
            $html .= '<p><' . $prop->ResortName . '</p>';
            if ($lcoation == 'resorts') {
                $html .= '</div>';
            }
            if ($lcoation == 'resorts') {
                $html .= '<a href="/resort-profile/?resort=' . $prop->id . '" class="dgt-btn">Explore</a>';
            } else {
                $html .= '<div data-link="/resort-profile/?resort=<' . $prop->id . '" class="dgt-btn sbt-btn">Explore Offer </div>';
            }
            $html .= '</a>';
            $html .= '</div>';
            $html .= '</li>';
        }
        $i++;
    }

    $html .= '</ul>';
    $getplus = $get + 1;
    if (count($props) == $getplus) {
        $start = $start + $get;
        $html .= '<a href="#" class="sbt-seemore" id="seemore-home" data-location="' . $location . '" data-start="' . $start . '" data-get="' . $get . '"> <span>See more</span> <i class="icon-arrow-down"></i></a>';
        $html .= '<div class="sbt-seemore-box"></div>';
    }
}

add_action("wp_ajax_gpx_display_featured_func", "gpx_display_featured_func");
add_action("wp_ajax_nopriv_gpx_display_featured_func", "gpx_display_featured_func");
function gpx_change_password_with_hash_func() {
    $cid = $_POST['cid'];
    $pw1 = $_POST['chPassword'];
    $pw2 = $_POST['chPasswordConfirm'];

    $data['msg'] = 'System unavailable. Please try again later.';

    if ($pw1 != $pw2) {
        $data['msg'] = 'Passwords do not match!';
    }

    $user = get_user_by('ID', $cid);

    if (isset($_POST['hash'])) {
        $pass = $_POST['hash'];

        if ($user && wp_check_password($pass, $user->data->user_pass, $user->ID)) {
            $up = wp_set_password($pw1, $user->ID);
            $data['msg'] = 'Password Updated!';
        } else {
            $data['msg'] = 'Wrong password!';
        }
    } else {
        $up = wp_set_password($pw1, $user->ID);
        $data['msg'] = 'Password Updated!';
    }


    wp_send_json($data);
}

add_action("wp_ajax_gpx_change_password_with_hash", "gpx_change_password_with_hash_func");
add_action("wp_ajax_nopriv_gpx_change_password_with_hash", "gpx_change_password_with_hash_func");


/**
 * Force Visual Composer to initialize as "built into the theme". This will hide certain tabs under the
 * Settings->Visual Composer page
 */
add_action('vc_before_init', 'your_prefix_vcSetAsTheme');
function your_prefix_vcSetAsTheme() {
    vc_set_as_theme();
}

function add_promo_var($vars) {
    $vars[] = 'promo';

    return $vars;
}

add_filter('query_vars', 'add_promo_var', 0, 1);
add_rewrite_rule('^promotion/([^/]*)/?', 'index.php?page_id=229&promo=$matches[1]', 'top');

function user_last_login($user_login, $user) {
    update_user_meta($user->ID, 'last_login', time());
    update_user_meta($user->ID, 'searchSessionID', $user->ID . "-" . time());
}

add_action('wp_login', 'user_last_login', 10, 2);

add_action('after_setup_theme', 'remove_admin_bar');

function remove_admin_bar() {
    if (!current_user_can('gpx_admin')) {
        show_admin_bar(false);
    }
}

function my_login_redirect($redirect_to, $request, $user) {
    //is there a user to check?
    if (isset($user->roles) && is_array($user->roles)) {
        //check for admins
        if (in_array('administrator', $user->roles) || in_array('gpx_call_center', $user->roles)) {
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

add_filter('login_redirect', 'my_login_redirect', 10, 3);
function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/logo.png);
        }
    </style>
<?php }

add_action('login_enqueue_scripts', 'my_login_logo');
function my_login_logo_url() {
    return home_url();
}

add_filter('login_headerurl', 'my_login_logo_url');

function my_login_logo_url_title() {
    return 'GPX';
}

add_filter('login_headertitle', 'my_login_logo_url_title');
function logout_redirect_home() {
    wp_safe_redirect(home_url());
    exit;
}

add_action('wp_logout', 'logout_redirect_home');

function my_redirect_home($lostpassword_redirect) {
    return home_url();
}

add_filter('lostpassword_redirect', 'my_redirect_home');
add_filter('wpsl_meta_box_fields', 'custom_meta_box_fields');

function custom_meta_box_fields($meta_fields) {
    $meta_fields[__('Additional Information', 'wpsl')] = [
        'phone' => [
            'label' => __('Tel', 'wpsl'),
        ],
        'fax' => [
            'label' => __('Fax', 'wpsl'),
        ],
        'email' => [
            'label' => __('Email', 'wpsl'),
        ],
        'url' => [
            'label' => __('Url', 'wpsl'),
        ],
        'resortid' => [
            'label' => __('Resort ID', 'wpsl'),
        ],
        'thumbnail' => [
            'label' => __('Thnumbnail URI', 'wpsl'),
        ],
    ];

    return $meta_fields;
}

function custom_templates($templates) {
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

add_filter('wpsl_templates', 'custom_templates');


function custom_frontend_meta_fields($store_fields) {
    $store_fields['wpsl_thumbnail'] = [
        'name' => 'thumbnail',
        'type' => 'url',
    ];

    return $store_fields;
}

add_filter('wpsl_frontend_meta_fields', 'custom_frontend_meta_fields');

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

add_filter('wpsl_info_window_template', 'custom_info_window_template');

function custom_store_header_template() {
    $header_template = '<% if ( wpslSettings.storeUrl == 1 && url ) { %>';
    $header_template .= '<strong><a href="<%= url %>"><%= store %></a></strong>';
    $header_template .= '<% } else { %>';
    $header_template .= '<strong><%= store %></strong>';
    $header_template .= '<% } %>';

    return $header_template;
}

add_filter('wpsl_store_header_template', 'custom_store_header_template');

function custom_listing_template() {
    global $wpsl, $wpsl_settings;

    $listing_template = '<li data-store-id="<%= id %>">';
    $listing_template .= '<div class="wpsl-store-location">';
    $listing_template .= '<div class="wpsl-listings-wrapper">';
    $listing_template .= '<div class="wpsl-main-info" style="float: left; margin-right: 20px;">';
    $listing_template .= '<p>';
    $listing_template .= wpsl_store_header_template('listing'); // Check which header format we use
    $listing_template .= '<span class="wpsl-street"><%= address %></span>';
    $listing_template .= '<% if ( address2 ) { %>';
    $listing_template .= '<span class="wpsl-street"><%= address2 %></span>';
    $listing_template .= '<% } %>';
    $listing_template .= '<span>' . wpsl_address_format_placeholders() . '</span>'; // Use the correct address format

    if (!$wpsl_settings['hide_country']) {
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

add_filter('wpsl_listing_template', 'custom_listing_template');

// Function that will return our WordPress menu
function gpx_list_menu($atts, $content = null) {
    extract(shortcode_atts([
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
        $atts));

    return wp_nav_menu([
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
    ]);
}

//Create the shortcode
add_shortcode("gpx_listmenu", "gpx_list_menu");

function vc_gpx_custom_menu() {
    vc_map([
        "name" => __("GPX Custom Menu", "gpx-website"),
        "base" => "gpx_listmenu",
        "params" => [
            // add params same as with any other content element
            [
                "type" => "textfield",
                "heading" => __("Menu", "gpx-website"),
                "param_name" => "menu",
                "description" => __("The menu slug.", "gp-website"),
            ],
            [
                "type" => "textfield",
                "heading" => __("Container Class", "gpx-website"),
                "param_name" => "container_class",
                "description" => __("Class of the container.", "gp-website"),
            ],
            [
                "type" => "textfield",
                "heading" => __("Container ID", "gpx-website"),
                "param_name" => "container_id",
                "description" => __("ID of the container.", "gp-website"),
            ],
            [
                "type" => "textfield",
                "heading" => __("Menu Class", "gpx-website"),
                "param_name" => "menu_class",
                "description" => __("Class of the menu.", "gp-website"),
            ],
            [
                "type" => "textfield",
                "heading" => __("Menu", "gpx-website"),
                "param_name" => "menu_id",
                "description" => __("ID of the menu.", "gp-website"),
            ],
        ],
    ]);
}

add_action('vc_before_init', 'vc_gpx_custom_menu');
function vc_gpx_bp_terms() {
    vc_map([
        "name" => __("Booking Path Terms", "gpx-website"),
        "base" => "gpx_booking_path",
        "params" => [
            // add params same as with any other content element
            [
                "type" => "textarea",
                "heading" => __("Terms", "gpx-website"),
                "param_name" => "terms",
                "description" => __("Additional Terms & Conditions for all weeks.", "gpx-website"),
            ],
        ],
    ]);
}

add_action('vc_before_init', 'vc_gpx_bp_terms');

function vc_gpx_bpp_terms() {
    vc_map([
        "name" => __("Booking Path Payment Terms", "gpx-website"),
        "base" => "gpx_booking_path_payment",
        "params" => [
            // add params same as with any other content element
            [
                "type" => "textarea",
                "heading" => __("Terms", "gpx-website"),
                "param_name" => "terms",
                "description" => __("Additional Terms & Conditions for all weeks.", "gpx-website"),
            ],
        ],
    ]);
}

add_action('vc_before_init', 'vc_gpx_bpp_terms');

function vc_gpx_bpc_terms() {
    vc_map([
        "name" => __("Booking Path Payment Terms", "gpx-website"),
        "base" => "gpx_booking_path_confirmation",
        "params" => [
            // add params same as with any other content element
            [
                "type" => "textarea",
                "heading" => __("Terms", "gpx-website"),
                "param_name" => "terms",
                "description" => __("Additional Terms & Conditions for all weeks.", "gpx-website"),
            ],
        ],
    ]);
}

add_action('vc_before_init', 'vc_gpx_bpc_terms');


function desitnations_custom_post_type() {
    // Set UI labels for Custom Post Type
    $labels = [
        'name' => _x('Destinations', 'Post Type General Name', 'gpx-dst'),
        'singular_name' => _x('Destination', 'Post Type Singular Name', 'gpx-dst'),
        'menu_name' => __('Destinations', 'gpx-dst'),
        'parent_item_colon' => __('Parent Destination', 'gpx-dst'),
        'all_items' => __('All Destinations', 'gpx-dst'),
        'view_item' => __('View Destination', 'gpx-dst'),
        'add_new_item' => __('Add New Destination', 'gpx-dst'),
        'add_new' => __('Add New', 'gpx-dst'),
        'edit_item' => __('Edit Destination', 'gpx-dst'),
        'update_item' => __('Update Destination', 'gpx-dst'),
        'search_items' => __('Search Destination', 'gpx-dst'),
        'not_found' => __('Not Found', 'gpx-dst'),
        'not_found_in_trash' => __('Not found in Trash', 'gpx-dst'),
    ];

    // Set other options for Custom Post Type

    $args = [
        'label' => __('Destinations', 'gpx-dst'),
        'description' => __('Destinations', 'gpx-dst'),
        'labels' => $labels,
        // Features this CPT supports in Post Editor
        'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'],
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
    register_post_type('destinations', $args);
}

add_action('init', 'desitnations_custom_post_type', 0);

function destinations_meta_boxes($meta_boxes) {
    $meta_boxes[] = [
        'title' => __('Destination', 'gpx-dst'),
        'post_types' => 'destinations',
        'fields' => [
            [
                'id' => 'gpx-destination-link',
                'name' => __('Destination (Region Name )', 'gpx-dst'),
                'type' => 'text',
            ],
            [
                'id' => 'gpx-destination-blog-link',
                'name' => __('Other Link (link to a page other than a destination page -- leave blank to link to destination)',
                    'gpx-dst'),
                'type' => 'post',
                'post_type' => [
                    'post',
                    'page',
                ],
                //                 'field_type' => 'select',
            ],
            [
                'id' => 'gpx-destination-link-text',
                'name' => __('Button Text', 'gpx-dst'),
                'type' => 'text',
            ],
        ],
    ];

    return $meta_boxes;
}

add_filter('rwmb_meta_boxes', 'destinations_meta_boxes');

function gpx_shared_media_custom_post_type() {
    // Set UI labels for Custom Post Type
    $labels = [
        'name' => _x('Shared Media', 'Post Type General Name', 'gpx-dst'),
        'singular_name' => _x('Shared Media', 'Post Type Singular Name', 'gpx-dst'),
        'menu_name' => __('Shared Media', 'gpx-dst'),
        'parent_item_colon' => __('Parent Media Galery', 'gpx-dst'),
        'all_items' => __('All Media Galleries', 'gpx-dst'),
        'view_item' => __('View Media Gallery', 'gpx-dst'),
        'add_new_item' => __('Add New Media Gallery', 'gpx-dst'),
        'add_new' => __('Add New Gallery', 'gpx-dst'),
        'edit_item' => __('Edit Media Gallery', 'gpx-dst'),
        'update_item' => __('Update Media Gallery', 'gpx-dst'),
        'search_items' => __('Search Media Gallery', 'gpx-dst'),
        'not_found' => __('Not Found', 'gpx-dst'),
        'not_found_in_trash' => __('Not found in Trash', 'gpx-dst'),
    ];

    // Set other options for Custom Post Type

    $args = [
        'label' => __('Media', 'gpx-dst'),
        'description' => __('Media', 'gpx-dst'),
        'labels' => $labels,
        // Features this CPT supports in Post Editor
        'supports' => ['title', 'page-attributes'],
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
    register_post_type('owner-shared-media', $args);
}

add_action('init', 'gpx_shared_media_custom_post_type', 0);

function gpx_shared_media_taxonomies_cat() {
    $labels = [
        'name' => _x('Resorts', 'taxonomy general name'),
        'singular_name' => _x('Resort', 'taxonomy singular name'),
        'search_items' => __('Search Resorts'),
        'all_items' => __('All Resorts'),
        'parent_item' => __('Parent Resort'),
        'parent_item_colon' => __('Parent Resort:'),
        'edit_item' => __('Edit Resort'),
        'update_item' => __('Update Resort'),
        'add_new_item' => __('Add New Resort'),
        'new_item_name' => __('New Resort'),
        'menu_name' => __('Resorts'),
    ];
    $args = [
        'labels' => $labels,
        'hierarchical' => true,
        'show_admin_column' => true,
    ];
    register_taxonomy('gpx_shared_media_resort', 'owner-shared-media', $args);
}

add_action('init', 'gpx_shared_media_taxonomies_cat', 0);

function gpx_shared_media_meta_boxes($meta_boxes) {
    $meta_boxes[] = [
        'title' => __('Gallery', 'gpx-dst'),
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

add_filter('rwmb_meta_boxes', 'gpx_shared_media_meta_boxes');

function gpx_shared_media_remove_wp_seo_meta_box() {
    remove_meta_box('wpseo_meta', 'owner-shared-media', 'normal');
}

add_action('add_meta_boxes', 'gpx_shared_media_remove_wp_seo_meta_box', 100);


function set_default_display_name($user_id) {
    $user = get_userdata($user_id);
    $name = sprintf('%s %s', $user->first_name, $user->last_name);
    $args = [
        'ID' => $user_id,
        'display_name' => $name,
        'nickname' => $name,
    ];
    wp_update_user($args);
}

add_action('user_register', 'set_default_display_name');

function ice_shortcode($atts) {
    $at = shortcode_atts(
        [
            'class' => '',
            'loggedintext' => 'Just Cruising Along...',
            'nologgedintext' => 'Login',
        ],
        $atts);

    $html = '';

    $cid = gpx_get_switch_user_cookie();

    if (isset($cid) && !empty($cid)) {
        $user = get_userdata($cid);
        if (isset($user) && !empty($user)) {
            $usermeta = (object) array_map(function ($a) {
                return $a[0];
            }, get_user_meta($cid));
            if ((isset($usermeta->ICEStore) && $usermeta->ICEStore != 'No') || !isset($usermeta->ICEStore)) {
                $html = '<a href="#" class="ice-link ' . esc_attr($at['class']) . '" data-cid="' . $cid . '">' . esc_attr($at['loggedintext']) . '</a>';
            }
        }
    }
    if (empty($html)) {
        $html = '<a href="#" class="ice-link ' . esc_attr($at['class']) . '" data-cid="">' . esc_attr($at['nologgedintext']) . '</a>';
    }

    return $html;
}

add_filter('gform_tabindex', '__return_false');
add_shortcode('ice_shortcode', 'ice_shortcode');

function universal_search_widget_shortcode() {
    ob_start();
    include(locate_template('template-parts/universal-search-widget.php'));

    return ob_get_clean();
}

add_shortcode('gpx_universal_search_widget', 'universal_search_widget_shortcode');

function perks_choose_credit() {
    return '<div class="exchange-credit"><div id="exchangeList"><div style="text-align: center;"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div></div></div>';
}

add_shortcode('perks_choose_credit', 'perks_choose_credit');

function perks_choose_donation() {
    return '<div class="exchange-donate"><div id="exchangeList" data-type="donation"><div style="text-align: center;"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div></div></div>';
}

add_shortcode('perks_choose_donation', 'perks_choose_donation');

function gpx_lpid_cookie() {
    if (isset($_POST['lpid']) && isset($_POST['cid'])) {
        update_user_meta($_POST['cid'], 'lppromoid', $_POST['lpid']);
    }

    $data = ['success' => true];
    wp_send_json($data);
}

add_action("wp_ajax_gpx_lpid_cookie", "gpx_lpid_cookie");
add_action("wp_ajax_nopriv_gpx_lpid_cookie", "gpx_lpid_cookie");

function gpx_show_hold_button() {
    if (empty($_GET['cid'])) {
        $data['hide'] = true;
    } else {
        if (gpx_hold_check($_GET['cid'])) {
            $data['show'] = true;
        } else {
            $data['hide'] = true;
        }
    }

    wp_send_json($data);
}

add_action("wp_ajax_gpx_show_hold_button", "gpx_show_hold_button");
add_action("wp_ajax_nopriv_gpx_show_hold_button", "gpx_show_hold_button");

add_action('init', function () {

    // strip slashes from superglobals
    $_GET = wp_unslash($_GET);
    $_POST = wp_unslash($_POST);
    $_COOKIE = wp_unslash($_COOKIE);
    $_REQUEST = wp_unslash($_REQUEST);

    // this is a plugin that is no longer available
    // register a shortcode that returns an empty string
    // so the shortcode does not show up in page content
    // if the plugin is not active
    if (!is_plugin_active('websitetourbuilder')) {
        add_shortcode('websitetour', function () {
            return '';
        });
    }
});
