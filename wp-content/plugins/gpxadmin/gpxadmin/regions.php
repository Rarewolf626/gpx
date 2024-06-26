<?php



function gpx_add_region($parent, $newregion, $oldid = '', $reassign = '', $displayName = '') {
    global $wpdb;

    $sql = $wpdb->prepare("SELECT lft,rght FROM wp_gpxRegion WHERE id = %d", $parent);
    $plr = $wpdb->get_row($sql);

    $right = $plr->rght;

    $sql = $wpdb->prepare("UPDATE wp_gpxRegion SET lft=lft+2 WHERE lft > %d", $right);
    $wpdb->query($sql);
    $sql = $wpdb->prepare("UPDATE wp_gpxRegion SET rght=rght+2 WHERE rght >= %d", $right);
    $wpdb->query($sql);

    $update = [
        'name' => $newregion,
        'parent' => $parent,
        'lft' => $right,
        'rght' => $right + 1,
        'displayName' => $displayName,
        'search_name' => gpx_search_string($displayName ?: $newregion),
    ];
    $wpdb->insert('wp_gpxRegion', $update);
    $newid = $wpdb->insert_id;
    if (!empty($oldid)) {
        $update = ['parent' => $newid];
        $wpdb->update('wp_gpxRegion', $update, ['parent' => $oldid]);
        $wpdb->update('wp_resorts', ['gpxRegionID' => $newid], ['gpxRegionID' => $oldid]);
    }

    if (isset($reassign) && !empty($reassign)) {
        $wpdb->update('wp_resorts', ['gpxRegionID' => $newid], ['gpxRegionID' => $parent]);
    }

    //$this->gpx_model->rebuild_tree(1, 0);
}


function gpx_subregions_all() {
    global $wpdb;
    $sql = "SELECT id, Town, Region, Country, gpxRegionID FROM wp_resorts WHERE Region='PUERTO PLATA' AND gpxRegionID='551'";
    $resorts = $wpdb->get_results($sql);
    foreach ($resorts as $resort) {
        $sql = $wpdb->prepare("SELECT id, name FROM wp_gpxRegion WHERE name=%s", $resort->Region);
        $gpxRegion = $wpdb->get_row($sql);
        if (!empty($gpxRegion)) {
            $subRegion = $gpxRegion->id;
        } else {
            $query = $wpdb->prepare("SELECT id, lft, rght FROM wp_gpxRegion WHERE id=%s", $resort->gpxRegionID);
            $plr = $wpdb->get_row($query);
            //if region exists then add the child
            if (!empty($plr)) {
                echo '<pre>' . print_r("get to it", true) . '</pre>';
            } //otherwise we need to pull the parent region from the daeRegion table and add both the region and locality as sub region
            else {
                echo '*********';
                echo '*********';
                echo '*********';
                echo '*********';
                echo '<pre>' . print_r("check id: " . $resort->id, true) . '</pre>';
                echo '*********';
                echo '*********';
                echo '*********';
                echo '*********';

            }
        }
        if (isset($subRegion) && $subRegion != $resort->gpxRegionID) {
            $wpdb->update('wp_resorts', ['gpxRegionID' => $subRegion], ['id' => $resort->id]);
        }
    }
    wp_send_json([]);
}
add_action('wp_ajax_subregions_all', 'gpx_subregions_all');
add_action('wp_ajax_nopriv_get_addResorts', 'gpx_subregions_all');

function gpx_return_region_list($country = '', $region = '') {
    global $wpdb;
    if (!empty($country) && empty($region)) {
        $sql = $wpdb->prepare("SELECT a.id as rid, b.id, a.region FROM wp_daeRegion a
                    INNER JOIN wp_gpxRegion b ON b.RegionID=a.id
                    WHERE a.CountryID=%s",
            $country);

        return $wpdb->get_results($sql);
    }
    if (!empty($region) && empty($country)) {
        $sql = $wpdb->prepare("SELECT id, name as region FROM wp_gpxRegion WHERE parent=%s ORDER BY name",
            $region);

        return $wpdb->get_results($sql);
    }

    return [];
}

function gpx_return_region($id) {
    global $wpdb;

    $sql = $wpdb->prepare("SELECT * FROM wp_gpxRegion WHERE id=%s", $id);
    $region = $wpdb->get_row($sql);

    $data['name'] = $region->name;
    if (isset($region->parent) && $region->parent == 1) {
        $sql = "SELECT  a.id as tid, a.name, a.RegionID, a.parent, b.CountryID, country
                FROM `wp_gpxRegion` a
                LEFT JOIN wp_daeRegion b ON a.RegionID = b.id
                LEFT JOIN wp_gpxCategory c ON b.CountryID = c.CountryID
                WHERE b.id='" . $region->RegionID . "'
                ORDER BY lft ASC";
    } else {
        $sql = $wpdb->prepare("SELECT  a.id as tid, a.name, a.RegionID, a.parent, b.CountryID, country
                FROM `wp_gpxRegion` a
                LEFT JOIN wp_daeRegion b ON a.RegionID = b.id
                LEFT JOIN wp_gpxCategory c ON b.CountryID = c.CountryID
                WHERE lft < %d AND rght > %d
                ORDER BY lft ASC", [$region->lft, $region->rght]);
    }
    $parents = $wpdb->get_results($sql);
    $i = 0;
    $pp = [];
    foreach ($parents as $parent) {

        if (in_array($parent->name, $pp)) {
            continue;
        }

        $pp[] = $parent->name;

        if ($parent->parent == 1) {
            $data['country']['id'] = (int)$parent->CountryID;
            $data['country']['name'] = $parent->country;
            $data['listr'][$i + 1] = gpx_return_region_list($parent->CountryID, '');
            $i++;
            $data['parent'][$i]['id'] = (int)$parent->RegionID;
            $data['parent'][$i]['parent'] = null;
            $data['parent'][$i]['name'] = $parent->name;
            $data['parent'][$i]['tid'] = (int)$parent->tid;
            $data['listr'][$i + 1] = gpx_return_region_list('', $parent->tid);
        } else {
            $data['parent'][$i]['id'] = $parent->tid;
            $data['parent'][$i]['parent'] = $parent->parent;
            $data['parent'][$i]['name'] = $parent->name;
            $data['parent'][$i]['tid'] = $parent->tid;
            $data['listr'][$i + 1] = gpx_return_region_list('', $parent->tid);
        }

        $i++;
    }

    return $data;
}

/**
 *
 *
 *
 *
 */
function get_gpx_region_list()
{
    $country = '';
    $region = '';
    if(isset($_REQUEST['country']))
        $country = $_REQUEST['country'];
    if(isset($_REQUEST['region']))
        $region = $_REQUEST['region'];

    $data = gpx_return_region_list($country,$region);

    wp_send_json($data);
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
    global $wpdb;

    $gpx_model = new GpxModel();
    $output = ['success' => false];
    if (isset($_POST['usage_parent']) && !empty($_POST['usage_parent'])) {
        $up = $_POST['usage_parent'];
    } elseif (isset($_POST['parent']) && !empty($_POST['parent'])) {
        $up = $_POST['parent'];
    }
    if (isset($up) && !empty($up)) {
        foreach ($up as $key => $value) {
            if (empty($value)) {
                unset($up[$key]);
            }
        }

        $parent = end($up);
        //edit region?
        if ((isset($_POST['edit-region']) && !empty($_POST['edit-region'])) && (isset($_POST['id']) && !empty($_POST['id']))) {
            // we don't need to make a lot of changes if all we are doing is editing a name...
            $sql = $wpdb->prepare("SELECT parent FROM wp_gpxRegion WHERE id=%s", $_POST['id']);
            $oldRegion = $wpdb->get_row($sql);

            if ($parent == $oldRegion->parent)//it's the same
            {
                $update = [
                    'name' => $_POST['edit-region'],
                    'displayName' => $_POST['display-name'],
                    'search_name' => gpx_search_string($_POST['display-name'] ?? $_POST['edit-region'] ?? ''),

                ];
                $wpdb->update('wp_gpxRegion', $update, ['id' => $_POST['id']]);
            } else {
                //remove the existing record
                $wpdb->delete('wp_gpxRegion', ['id' => $_POST['id']]);

                $gpx_model->rebuild_tree(1, 0);

                sleep(2);

                gpx_add_region($parent, $_POST['edit-region'], $_POST['id'], $_POST['reassign'], $_POST['display-name']);
            }
            $output = ['success' => true, 'msg' => 'Successfully edited region!', 'type' => 'edit'];

        } //add new region?
        elseif (isset($_POST['new-region']) && !empty($_POST['new-region'])) {
            gpx_add_region($parent, $_POST['new-region'], '', $_POST['reassign'], $_POST['display-name']);
            $output = ['success' => true, 'msg' => 'Succesfully added region!'];
        } else {
            $output['msg'] = 'Error! Please check your information and try again.';
        }
    } elseif (isset($_POST['remove']) && !empty($_POST['remove'])) {
        //get the parent of this region
        $sql = $wpdb->prepare("SELECT parent FROM wp_gpxRegion WHERE id=%s", $_POST['remove']);
        $row = $wpdb->get_row($sql);
        $parent = $row->parent;

        //reassign all resorts to parent
        $wpdb->update('wp_resorts', ['gpxRegionID' => $parent], ['gpxRegionID' => $_POST['remove']]);

        //also reasign all direct children to the parent
        $wpdb->update('wp_gpxRegion', ['parent' => $parent], ['parent' => $_POST['remove']]);

        //remove the existing record
        $wpdb->delete('wp_gpxRegion', ['id' => $_POST['remove']]);

        $gpx_model->rebuild_tree(1, 0);

        $output = ['success' => true, 'msg' => 'Successfully removed region!'];
    } else {
        $output['mgs'] = 'Error! Please check your information and try again.';
    }

    wp_send_json($output);
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
    global $wpdb;
    $data = [];
    $sql = "SELECT a.id, a.ResortName, a.Address1, a.Town, a.Region, a.Country, b.name as regionName FROM wp_resorts a
                INNER JOIN wp_gpxRegion b ON a.gpxRegionID = b.id";
    $regions = $wpdb->get_results($sql);
    $i = 0;
    foreach ($regions as $region) {
        $data[$i]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=regions_assign&id=' . $region->id . '"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
        $data[$i]['resort'] = $region->ResortName;
        $data[$i]['address1'] = $region->Address1;
        $data[$i]['city'] = $region->Town;
        $data[$i]['state'] = $region->Region;
        $data[$i]['country'] = $region->Country;
        $data[$i]['region'] = $region->regionName;
        $i++;
    }

    $sql = "SELECT a.id, a.ResortName, a.Address1, a.Town, a.Region, a.Country FROM wp_resorts a
                WHERE gpxRegionID < 526";
    $regions = $wpdb->get_results($sql);
    foreach ($regions as $region) {
        $data[$i]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=regions_assign&id=' . $region->id . '"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
        $data[$i]['resort'] = $region->ResortName;
        $data[$i]['address1'] = $region->Address1;
        $data[$i]['city'] = $region->Town;
        $data[$i]['state'] = $region->Region;
        $data[$i]['country'] = $region->Country;
        $data[$i]['region'] = 'Unassigned';
        $i++;
    }

    wp_send_json($data);
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
    global $wpdb;
    $output = ['success' => false];

    if (isset($_POST['hidden-region']) && $_POST['hidden-region'] == "Yes") {
        $wpdb->update('wp_gpxRegion', ['ddHidden' => 1], ['id' => $_POST['orginalRegion']]);
    }

    if (isset($_POST['usage_parent']) && !empty($_POST['usage_parent'])) {
        while (empty(end($_POST['usage_parent']))) {
            array_pop($_POST['usage_parent']);
        }
        $newregion = end($_POST['usage_parent']);
        if (isset($_POST['resortid']) && !empty($_POST['resortid'])) {
            $id = $_POST['resortid'];

            $wpdb->update('wp_resorts', ['gpxRegionID' => $newregion], ['id' => $id]);
            $output = ['success' => true, 'msg' => 'Successfully updated region!'];
        } else {
            $output['msg'] = 'Error -- ID Not Set! Please check your information and try again.';
        }
    } else {
        $output['mgs'] = 'Error -- No Region Selected! Please check your information and try again.';
    }

    wp_send_json($output);
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
    global $wpdb;

    $featured = $_POST['featured'];

    if ($featured == 0) {
        $newstatus = 1;
        $msg = "Region is featured!";
        $fa = "fa-check-square";
    } else {
        $newstatus = 0;
        $msg = "Region is not featured!";
        $fa = "fa-square";
    }

    $wpdb->update('wp_gpxRegion', ['featured' => $newstatus], ['id' => $_POST['region']]);
    $data = ['success' => true, 'msg' => $msg, 'fastatus' => $fa, 'status' => $newstatus];

    wp_send_json($data);
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
    global $wpdb;

    $hidden = $_POST['hidden'];

    if ($hidden == 0) {
        $newstatus = 1;
        $msg = "Region is hidden!";
        $fa = "fa-check-square";
    } else {
        $newstatus = 0;
        $msg = "Region is not hidden!";
        $fa = "fa-square";
    }

    $wpdb->update('wp_gpxRegion', ['ddHidden' => $newstatus], ['id' => $_POST['region']]);
    $data = ['success' => true, 'msg' => $msg, 'fastatus' => $fa, 'status' => $newstatus];

    wp_send_json($data);
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
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    $country = '';
    if(isset($_GET['country']))
        $country = $_GET['country'];

    $resorts = gpx_return_countryregion_dd($country);

    wp_send_json($resorts);
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
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    $country = '';
    if(isset($_GET['country']))
        $country = $_GET['country'];

    global $wpdb;
    $output = '<option value="0" disabled selected ></option>';
    if (empty($country))//get the country
    {
        //select usa first
        $sql = "SELECT CountryID, country FROM wp_gpxCategory WHERE CountryID = '45'";
        $usa = $wpdb->get_results($sql);
        $sql = "SELECT CountryID, country FROM wp_gpxCategory WHERE CountryID <> '45' ORDER BY country";
        $all = $wpdb->get_results($sql);
        $countries = array_merge($usa, $all);
        foreach ($countries as $country) {
            if ($country->CountryID > '1000') {
                continue;
            }
            $output .= '<option value="' . $country->CountryID . '"';
            if (isset($_GET['select_country']) && $_GET['select_country'] == $country->CountryID) {
                $output .= ' selected';
            }
            $output .= '>' . $country->country . '</option>';
        }

    } else//get a region
    {
        $sql = $wpdb->prepare("SELECT id, region FROM wp_daeRegion WHERE CountryID=%s", $country);
        $regions = $wpdb->get_results($sql);
        $onlyhigh = false;
        foreach ($regions as $region) {
            //onlyhigh is set so we'll skip adding any other regions for this country
            if ($onlyhigh) {
                continue;
            }
            $sql = $wpdb->prepare("SELECT id, name, lft, rght FROM wp_gpxRegion WHERE RegionID=%s", $region->id);
            $gpxRegions = $wpdb->get_results($sql);
            foreach ($gpxRegions as $gpxRegion) {
                //onlyhigh is set so we'll skip adding any other regions for this country
                if ($onlyhigh) {
                    continue;
                }
                //first set DAE region
                $datas[$gpxRegion->id] = $gpxRegion->name;
                //check if the name is all and if they have any children -- if children exist then we assume that we want to get them
                if (strpos(strtolower($gpxRegion->name), " all") !== false && $gpxRegion->rght - $gpxRegion->lft != 1) {
                    //We only wnat the high level regions that are set by GPX remove all other options
                    $datas = [];
                    $onlyhigh = true;

                    //find the first children of all
                    $nextleft = $gpxRegion->lft + 1;
                    $right = $gpxRegion->rght;
                    $sql = $wpdb->prepare("SELECT id, name, lft, rght FROM wp_gpxRegion WHERE lft = %d", $nextleft);
                    $children = $wpdb->get_row($sql);
                    $childright = $children->rght;
                    $datas[$children->id] = $children->name;

                    while ($childright < $right) {
                        $nextleft = $childright + 1;
                        $sql = $wpdb->prepare("SELECT id, name, lft, rght FROM wp_gpxRegion WHERE lft = %d", $nextleft);
                        $children = $wpdb->get_row($sql);
                        if (empty($children)) {
                            $childright = 10000000000000;
                            continue;
                        }
                        $childright = $children->rght;
                        $datas[$children->id] = $children->name;
                    }
                }
            }
        }
        asort($datas);

        foreach ($datas as $key => $value) {
            $output .= '<option value="' . $key . '"';
            if (isset($_GET['select_region']) && $_GET['select_region'] == $key) {
                $output .= ' selected';
            }
            $output .= '>' . $value . '</option>';
        }
    }

    wp_send_json($output);
}
add_action("wp_ajax_gpx_newcountryregion_dd","gpx_newcountryregion_dd");
add_action("wp_ajax_nopriv_gpx_newcountryregion_dd", "gpx_newcountryregion_dd");


function gpx_return_countryregion_dd($country = '') {
    global $wpdb;
    $output = '<option value="0" disabled selected ></option>';
    if (empty($country))//get the country
    {
        //select usa first
        $sql = "SELECT CountryID, country FROM wp_gpxCategory WHERE CountryID = '45'";
        $usa = $wpdb->get_results($sql);
        $sql = "SELECT CountryID, country FROM wp_gpxCategory WHERE CountryID <> '45' ORDER BY country";
        $all = $wpdb->get_results($sql);
        $countries = array_merge($usa, $all);
        foreach ($countries as $country) {
            if ($country->CountryID > '1000') {
                continue;
            }
            $output .= '<option value="' . $country->CountryID . '"';
            if (isset($_GET['select_country']) && $_GET['select_country'] == $country->CountryID) {
                $output .= ' selected';
            }
            $output .= '>' . $country->country . '</option>';
        }

    } else//get a region
    {
        $sql = $wpdb->prepare("SELECT id, region FROM wp_daeRegion WHERE CountryID=%s", $country);
        $regions = $wpdb->get_results($sql);
        foreach ($regions as $region) {
            if ($region->region == 'All') {
                continue;
            }
            $output .= '<option value="' . $region->id . '"';
            if (isset($_GET['select_region']) && $_GET['select_region'] == $region->id) {
                $output .= ' selected';
            }
            $output .= '>' . $region->region . '</option>';
        }
    }

    return $output;
}


/**
 *
 *
 *
 *
 */
function gpx_newcountryregion_dd_sc($atts)
{

    $atts = shortcode_atts(array('country'=>''), $atts);
    $resorts = gpx_return_countryregion_dd($atts['country']);
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
    return gpx_return_countryregion_dd($atts['country']);
}
add_shortcode('sc_countryregion_dd', 'gpx_countryregion_dd_sc');

function gpx_return_subregion_dd($type, $jsonregion, $country) {
    global $wpdb;
    $output = '<option value="0" disabled selected ></option>';
    if (empty($regions) && !empty($country)) {
        $sql = $wpdb->prepare("SELECT a.id FROM wp_daeRegion a
                    INNER JOIN wp_gpxRegion b ON a.id=b.RegionID
                    WHERE a.CountryID=%s", $country);
        $rows = $wpdb->get_results($sql);
        foreach ($rows as $row) {
            $regions[] = $row->id;
        }
    } else {
        if (is_array($jsonregion)) {
            $regions = explode(",", $jsonregion);
        }
    }
    if (isset($regions)) {
        foreach ($regions as $region) {
            $sql = $wpdb->prepare("SELECT lft, rght, id, name FROM wp_gpxRegion WHERE " . gpx_esc_table($type) . "=%s", $region);
            if ($type <> 'id') {
                $row = $wpdb->get_row($sql);
                $lft = $row->lft + 1;
                $sql = $wpdb->prepare("SELECT id, name, lft, rght FROM wp_gpxRegion
                        WHERE lft BETWEEN %d AND %d
                        ORDER BY lft ASC", [$lft, $row->rght]);
            }
            $resorts = $wpdb->get_results($sql);
            $right = 0;
            $indent = '';
            foreach ($resorts as $resort) {

                if ($resort->rght < $right) {
                    $indent .= ' - ';
                    $right = $resort->rght;
                } else {
                    $right = $resort->rght;
                    $indent = '';
                }
                $output .= '<option value="' . $resort->id . '">' . $indent . $resort->name . '</option>';


            }
        }
    }

    return $output;
}


/**
 *
 *
 *
 *
 */
function gpx_subregion_dd()
{
    $term = (!empty($_GET['term']))? sanitize_text_field($_GET['term']) : '';

    $region = '';
    if(isset($_GET['selected_region']))
        $region = $_GET['selected_region'];

    $resorts = gpx_return_subregion_dd($region);

    wp_send_json($resorts);
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
    return gpx_return_subregion_dd($atts['type'], $atts['region'], $atts['country']);
}
add_shortcode('sc_gpx_subregion_dd', 'gpx_subregion_dd_sc');

