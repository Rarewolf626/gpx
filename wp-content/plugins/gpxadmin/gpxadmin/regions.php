<?php


/**
 *
 *
 *
 *
 */
//wp ajax being used for cron api
function get_addRegions()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    $data = $gpx->addRegions();

    wp_send_json($data);
}

add_action('wp_ajax_get_addRegions', 'get_addRegions');
add_action('wp_ajax_nopriv_get_addRegions', 'get_addRegions');


/**
 *
 *
 *
 *
 */
//wp ajax being used for cron api
function get_countryList()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $ping = '';
    if(isset($_POST['ping']))
    {
        $ping = $_POST['ping'];
    }

    $data = $gpx->DAEGetCountryList($ping);

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_countryList', 'get_countryList');
add_action('wp_ajax_nopriv_get_countryList', 'get_countryList');




/**
 *
 *
 *
 *
 */
function getregionfromCountyList()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $ping = '';
    if(isset($_POST['ping']))
    {
        $ping = $_POST['ping'];
    }

    $data = $gpx->DAEGetCountryList($ping);

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_getregionfromCountyList', 'getregionfromCountyList');
add_action('wp_ajax_nopriv_getregionfromCountyList', 'getregionfromCountyList');




/**
 *
 *
 *
 *
 */
function get_regionList()
{
    require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $CountryID = '14';

    if(isset($_GET['country']))
    {
        $CountryID = $_GET['country'];
    }

    $data = $gpx->DAEGetRegionList($CountryID);

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_regionList', 'get_regionList');
add_action('wp_ajax_nopriv_get_regionList', 'get_regionList');


/**
 *
 *
 *
 *
 */
function subregions_all()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $data = $gpx->update_subregions_add_all_resorts();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_subregions_all', 'subregions_all');
add_action('wp_ajax_nopriv_get_addResorts', 'subregions_all');



/**
 *
 *
 *
 *
 */
function get_gpx_regions()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_regions();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_regions', 'get_gpx_regions');
add_action('wp_ajax_nopriv_get_gpx_regions', 'get_gpx_regions');

/**
 *
 *
 *
 *
 */
function get_gpx_region_list()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $country = '';
    $region = '';
    if(isset($_REQUEST['country']))
        $country = $_REQUEST['country'];
    if(isset($_REQUEST['region']))
        $region = $_REQUEST['region'];

    $data = $gpx->return_gpx_region_list($country,$region);

    wp_send_json($data);
    wp_die();
}
add_action('wp_ajax_get_gpx_region_list', 'get_gpx_region_list');
add_action('wp_ajax_nopriv_get_gpx_region_list', 'get_gpx_region_list');

/**
 *
 *
 *
 *
 */
function add_gpx_region()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_add_edit_region();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_add_gpx_region', 'add_gpx_region');
add_action('wp_ajax_nopriv_add_gpx_region', 'add_gpx_region');


/**
 *
 *
 *
 *
 */
function get_gpx_regionsassignlist()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_regionsassignlist();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_get_gpx_regionsassignlist', 'get_gpx_regionsassignlist');
add_action('wp_ajax_nopriv_get_gpx_regionsassignlist', 'get_gpx_regionsassignlist');


/**
 *
 *
 *
 *
 */
function assign_gpx_region()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_assign_region();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_assign_gpx_region', 'assign_gpx_region');
add_action('wp_ajax_nopriv_assign_gpx_region', 'assign_gpx_region');


/**
 *
 *
 *
 *
 */
function featured_gpx_region()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_featured_gpx_region();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_featured_gpx_region', 'featured_gpx_region');
add_action('wp_ajax_nopriv_featured_gpx_region', 'featured_gpx_region');



/**
 *
 *
 *
 *
 */
function hidden_gpx_region()
{
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $data = $gpx->return_gpx_hidden_gpx_region();

    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_hidden_gpx_region', 'hidden_gpx_region');
add_action('wp_ajax_nopriv_hidden_gpx_region', 'hidden_gpx_region');



/**
 *
 *
 *
 *
 */
function gpx_countryregion_dd()
{
    header('content-type: application/json; charset=utf-8');
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $country = '';
    if(isset($_GET['country']))
        $country = $_GET['country'];

    $resorts = $gpx->return_gpx_countryregion_dd($country);

    echo wp_send_json($resorts);
    exit();
}
add_action("wp_ajax_gpx_countryregion_dd","gpx_countryregion_dd");
add_action("wp_ajax_nopriv_gpx_countryregion_dd", "gpx_countryregion_dd");



/**
 *
 *
 *
 *
 */
function gpx_newcountryregion_dd()
{
    header('content-type: application/json; charset=utf-8');
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $country = '';
    if(isset($_GET['country']))
        $country = $_GET['country'];

    $resorts = $gpx->return_gpx_newcountryregion_dd($country);

    echo wp_send_json($resorts);
    exit();
}
add_action("wp_ajax_gpx_newcountryregion_dd","gpx_newcountryregion_dd");
add_action("wp_ajax_nopriv_gpx_newcountryregion_dd", "gpx_newcountryregion_dd");





/**
 *
 *
 *
 *
 */
function gpx_newcountryregion_dd_sc($atts)
{

    $atts = shortcode_atts(array('country'=>''), $atts);
    extract($atts);
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $resorts = $gpx->return_gpx_newcountryregion_dd($country);
    return $resorts;
}
add_shortcode('sc_newcountryregion_dd', 'gpx_newcountryregion_dd_sc');



/**
 *
 *
 *
 *
 */
function gpx_countryregion_dd_sc($atts)
{

    $atts = shortcode_atts(array('country'=>''), $atts);
    extract($atts);
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $resorts = $gpx->return_gpx_countryregion_dd($country);
    return $resorts;
}
add_shortcode('sc_countryregion_dd', 'gpx_countryregion_dd_sc');



/**
 *
 *
 *
 *
 */
function gpx_subregion_dd()
{
    header('content-type: application/json; charset=utf-8');
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);

    $region = '';
    if(isset($_GET['selected_region']))
        $region = $_GET['selected_region'];

    $resorts = $gpx->return_gpx_subregion_dd($region);

    echo wp_send_json($resorts);
    exit();
}
add_action("wp_ajax_gpx_subregion_dd","gpx_subregion_dd");
add_action("wp_ajax_nopriv_gpx_subregion_dd", "gpx_subregion_dd");

/**
 *
 *
 *
 *
 */
function gpx_subregion_dd_sc($atts)
{
    $atts = shortcode_atts(array('type'=>'', 'region'=>'', 'country'=>''), $atts);
    extract($atts);
    require_once GPXADMIN_PLUGIN_DIR.'/functions/class.gpxadmin.php';
    $gpx = new GpxAdmin(GPXADMIN_PLUGIN_URI, GPXADMIN_PLUGIN_DIR);
    $resorts = $gpx->return_gpx_subregion_dd($type, $region, $country);
    return $resorts;
}
add_shortcode('sc_gpx_subregion_dd', 'gpx_subregion_dd_sc');

