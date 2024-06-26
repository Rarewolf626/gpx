<?php

use GPX\Model\Week;
use GPX\Repository\WeekRepository;
use GPX\Repository\RegionRepository;
use GPX\Form\Admin\Resort\CopyResortFeesForm;
use GPX\Form\Admin\Resort\DeleteResortFeesForm;
use GPX\Form\Admin\Resort\EditAlertNoteForm;
use GPX\Form\Admin\Resort\EditDescriptionForm;
use GPX\Form\Admin\Resort\EditResortFeesForm;
use GPX\Form\Admin\Resort\RemoveAlertNoteForm;
use GPX\Model\ResortMeta;
use GPX\Model\Resort;
use GPX\Repository\ResortRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

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


function resort_confirmation_number()
{
    $rows = Week::query()
        ->where('resort_confirmation_number', '=', $_POST['resortConfirmation'])
        ->when($_POST['resort'] ?? null, fn($query) => $query->where('resort', '=', $_POST['resort']))
        ->get();

    wp_send_json($rows->toArray());
}

add_action('wp_ajax_resort_confirmation_number', 'resort_confirmation_number');
add_action('wp_ajax_nopriv_resort_confirmation_number', 'resort_confirmation_number');


function get_unit_type()
{
    $resort = $_POST['resort'] ?? null;
    if (empty($resort)) {
        wp_send_json([]);
    }
    $types = DB::table('wp_unit_type')
        ->where('resort_id', '=', $resort)
        ->pluck('name', 'record_id');

    wp_send_json($types);
}

add_action('wp_ajax_get_unit_type', 'get_unit_type');
add_action('wp_ajax_nopriv_get_unit_type', 'get_unit_type');

/**
 * Responsible for both the add room and the update room form
 */
function room_Form()
{
    $week_id = gpx_request()->request->getInt('room_id');
    if ($week_id) {
        $week = Week::find($week_id);
        if (!$week) {
            wp_send_json_error(['message' => 'Could not find week'], 404);
        }
        WeekRepository::instance()->update_week($week, $_POST);
        wp_send_json_success([
            'message' => 'Updated successful',
        ]);
    } else {
        $weeks = WeekRepository::instance()->add_weeks($_POST);
        wp_send_json_success([
            'message' => sprintf('%d week%s Added',
                $weeks->count(),
                $weeks->count() === 1 ? '' : 's'),
        ], 201);
    }
}

add_action('wp_ajax_room_Form', 'room_Form');
add_action('wp_ajax_room_Form_edit', 'room_Form');


/**
 *
 *
 *
 *
 */
function get_addResorts()
{
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    $data = $gpx->DAEGetResortProfile();

    wp_send_json($data);
}


/**
 *
 *
 *
 *
 */
function get_indResorts()
{
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    $data = $gpx->DAEGetResortInd();

    wp_send_json($data);
}

add_action('wp_ajax_get_indResorts', 'get_indResorts');
add_action('wp_ajax_nopriv_get_indResorts', 'get_indResorts');


/**
 *
 *
 *
 *
 */
function get_missingResort()
{
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    $resortID = '9491';
    if (isset($_GET['resortID'])) {
        $resortID = $_GET['resortID'];
    }

    $endpointID = 'EUR';
    if (isset($_GET['endpointID'])) {
        $endpointID = $_GET['endpointID'];
    }
    $data = $gpx->missingDAEGetResortProfile($resortID, $endpointID);

    wp_send_json($data);
}

add_action('wp_ajax_get_missingResort', 'get_missingResort');
add_action('wp_ajax_nopriv_get_missingResort', 'get_missingResort');


/**
 *
 *
 *
 *
 */
function get_addResortDetails()
{
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
    $data = $gpx->addResortDetails();

    wp_send_json($data);
}

add_action('wp_ajax_get_addResortDetails', 'get_addResortDetails');
add_action('wp_ajax_nopriv_get_addResortDetails', 'get_addResortDetails');


/**
 *
 *
 *
 *
 */
function get_manualResortUpdate()
{
    global $wpdb;
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $sql = $wpdb->prepare("SELECT id, ResortID, EndpointID, gpxRegionID FROM wp_resorts WHERE ResortID=%s",
        $_POST['resort']);
    $row = $wpdb->get_row($sql);
    $inputMembers = [
        'ResortID' => $row->ResortID,
        'EndpointID' => $row->EndpointID,
    ];

    $data = $gpx->DAEGetResortProfile($row->id, $row->gpxRegionID, $inputMembers, '1');

    wp_send_json($data);
}

add_action('wp_ajax_get_manualResortUpdate', 'get_manualResortUpdate');
add_action('wp_ajax_nopriv_get_manualResortUpdate', 'get_manualResortUpdate');


/**
 *
 *
 *
 *
 */
function get_manualResortUpdateAll()
{
    global $wpdb;
    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

    $sql = "SELECT id, ResortID, EndpointID, gpxRegionID FROM wp_resorts WHERE active='1'";
    $rows = $wpdb->get_results($sql);
    foreach ($rows as $row) {
        $inputMembers = [
            'ResortID' => $row->ResortID,
            'EndpointID' => $row->EndpointID,
        ];
        $data = $gpx->DAEGetResortProfile($row->id, $row->gpxRegionID, $inputMembers, '1');
    }

    wp_send_json($data);
}

add_action('wp_ajax_get_manualResortUpdateAll', 'get_manualResortUpdateAll');
add_action('wp_ajax_nopriv_get_manualResortUpdateAll', 'get_manualResortUpdateAll');


/**
 *
 *
 *
 *
 */
function gpx_resorts_list()
{
    global $wpdb;

    $sql = "SELECT a.id, a.price, a.WeekEndpointID, a.resortId, a.resortName, a.country, b.Description, b.ImagePath1  FROM wp_properties a INNER JOIN wp_resorts b ON b.ResortID=a.resortId";
    $props = $wpdb->get_results($sql);

    return $props;
}


function gpx_return_resort($id = '') {
    global $wpdb;

    $sql = $wpdb->prepare("SELECT * FROM wp_resorts WHERE id=%s", $id);
    $row = $wpdb->get_row($sql);

    $nodates = [
        'ada',
        'attributes',
        'UnitFacilities',
        'ResortFacilities',
        'AreaFacilities',
        'UnitConfig',
        'CommonArea',
        'UponRequest',
        'GuestBathroom',
        'GuestRoom',
        'ResortFeeSettings',
    ];

    DB::table('wp_resorts_meta')
      ->select(['meta_key', 'meta_value'])
      ->where('ResortID', '=', $row->ResortID)
      ->whereIn('meta_key', $nodates)
      ->pluck('meta_value', 'meta_key')
      ->map(fn($value, $key) => $key === 'ResortFeeSettings' ? json_decode($value, true) : Arr::last((json_decode($value, true))))
      ->each(function ($value, $attribute) use ($row) {
          $row->$attribute = $attribute === 'ResortFeeSettings' ? $value : json_encode($value);
      });

    if (isset($_FILES['new_image'])) {
        $image = $_FILES['new_image'];

        // HANDLE THE FILE UPLOAD
        // If the upload field has a file in it
        if (isset($image) && ($image['size'] > 0)) {

            // Get the type of the uploaded file. This is returned as "type/extension"
            $arr_file_type = wp_check_filetype(basename($image['name']));
            $uploaded_file_type = $arr_file_type['type'];

            // Set an array containing a list of acceptable formats
            $allowed_file_types = ['image/jpg', 'image/jpeg', 'image/gif', 'image/png'];
            // If the uploaded file is the right format
            if (in_array($uploaded_file_type, $allowed_file_types)) {
                // Options array for the wp_handle_upload function. 'test_upload' => false
                $upload_overrides = ['test_form' => false];
                // Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array
                $uploaded_file = wp_handle_upload($image, $upload_overrides);

                // If the wp_handle_upload call returned a local path for the image
                if (isset($uploaded_file['file'])) {

                    //The new file URL
                    $new_file_url = $uploaded_file['url'];

                    // The wp_insert_attachment function needs the literal system path, which was passed back from wp_handle_upload
                    $file_name_and_location = $uploaded_file['file'];

                    // Generate a title for the image that'll be used in the media library
                    $file_title_for_media_library = $row->ResortName;

                    // Set up options array to add this file as an attachment

                    $imgTitle = addslashes($file_title_for_media_library);
                    if (isset($_POST['title']) && !empty($_POST['title'])) {
                        $imgTitle = $_POST['title'];
                    }

                    $attachment = [
                        'post_mime_type' => $uploaded_file_type,
                        'post_title' => $imgTitle,
                        'post_content' => '',
                        'post_status' => 'inherit',
                    ];

                    // Run the wp_insert_attachment function. This adds the file to the media library and generates the thumbnails. If you wanted to attch this image to a post, you could pass the post id as a third param and it'd magically happen.
                    $attach_id = wp_insert_attachment($attachment, $file_name_and_location);

                    if (isset($_POST['alt']) && !empty($_POST['alt'])) {
                        update_post_meta($attach_id, '_wp_attachment_image_alt', $_POST['alt']);
                        update_post_meta($attach_id, 'gpx_image_video', $_POST['video']);
                    }
                    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata($attach_id, $file_name_and_location);
                    wp_update_attachment_metadata($attach_id, $attach_data);


                    //update the resort_meta table


                    // Set the feedback flag to false, since the upload was successful
                    $upload_feedback = false;


                } else { // wp_handle_upload returned some kind of error. the return does contain error details, so you can use it here if you want.

                    $upload_feedback = 'There was a problem with your upload.';

                }

            } else { // wrong file type

                $upload_feedback = 'Please upload only image files (jpg, gif or png).';

            }

        } else { // No file was passed

            $upload_feedback = false;

        }

    }

    $rmGroups = [
        'AlertNote' => 'alertnotes',

        'CommonArea' => 'ada',
        'GuestRoom' => 'ada',
        'GuestBathroom' => 'ada',
        'UponRequest' => 'ada',

        'UnitFacilities' => 'attributes',
        'ResortFacilities' => 'attributes',
        'AreaFacilities' => 'attributes',
        'UnitConfig' => 'attributes',

        //             'resortConditions'=>'attributes',
        'GuestFeeAmount' => 'fees',
        'resortFees' => 'fees',
        'ExchangeFeeAmount' => 'fees',
        'RentalFeeAmount' => 'fees',
        'CPOFeeAmount' => 'fees',
        'LateDepositFeeOverride' => 'fees',
        'UpgradeFeeAmount' => 'fees',
        'SameResortExchangeFee' => 'fees',
    ];

    $dates = [
        'alertnotes' => [],
        'descriptions' => ['0'],
        'attributes' => ['0'],
        'ada' => ['0'],
        'fees' => ['0'],
    ];
    $defaultAttrs = [];
    $setAttribute = [];

    $resortMetas = DB::table('wp_resorts_meta')
                     ->select(['meta_key', 'meta_value'])
                     ->where('ResortID', '=', $row->ResortID)
                     ->where('meta_key', '!=', 'ResortFeeSettings')
                     ->whereNotIn('meta_key', Resort::descriptionFields()->pluck('name'))
                     ->get();

    //set the default attributes
    foreach ($rmGroups as $rmk => $rmg) {
        if ($rmg == 'attributes') {
            $setAttribute[$rmk] = $rmk;
        }
    }
    foreach ($setAttribute as $sa) {
        if (!empty($row->$sa)) {
            $defaultAttrs[$sa] = is_string($row->$sa) ? json_decode($row->$sa, true) : $row->$sa;
            $toSet[$sa] = $defaultAttrs[$sa];
        }
    }
    if (isset($defaultAttrs)) {
        $row->defaultAttrs = $defaultAttrs;

        if (!empty($resortMetas)) {
            foreach ($resortMetas as $meta) {
                unset($setAttribute[$meta->meta_key]);
                $dateorder = [];
                $key = $meta->meta_key;
                $rmDefaults[$key] = $row->$key ?? null;
                $rmGroups[$key] = $row->$key ?? null;
                $row->$key = $meta->meta_value;
                $metaValue = json_decode($row->$key, true);
                if (is_array($metaValue)) {
                    foreach ($metaValue as $mvKey => $mvVal) {
                        $dateorder[$mvKey] = $mvVal;
                        if (isset($rmGroups[$key])) unset($dates[$rmGroups[$key]][0]);
                    }
                }
                ksort($dateorder);
                foreach ($dateorder as $doK => $doV) {
                    $dates[$rmGroups[$key]][$doK][$key] = $doV;
                }
            }
        }

        //is this the first time this resort has been updated?
        if (!isset($row->images)) {
            $daeImages = [];
            //the image hasn't been updated -- let's get the ones set by DAE
            for ($i = 1; $i <= 3; $i++) {
                $daeImage = 'ImagePath' . $i;
                if (!empty($row->$daeImage)) {
                    $daeImages[] =
                        [
                            'type' => 'dae',
                            'src' => $row->$daeImage,
                        ];
                }
            }
            $row->images = json_encode($daeImages);
            $wpdb->insert('wp_resorts_meta', [
                'ResortID' => $row->ResortID,
                'meta_key' => 'images',
                'meta_value' => $row->images,
            ]);
        } elseif (isset($new_file_url)) {
            //add the new image to the end of the object
            $allImages = json_decode($row->images, true);
            $allImages[] = [
                'type' => 'uploaded',
                'id' => $attach_id,
                'src' => $new_file_url,
            ];
            $row->images = json_encode($allImages);
            $wpdb->update('wp_resorts_meta', ['meta_value' => $row->images], [
                'ResortID' => $row->ResortID,
                'meta_key' => 'images',
            ]);
            $row->newfile = true;
        }

    }
    if (!empty($rmDefaults)) {
        $row->rmdefaults = $rmDefaults;
    }
    //any resort meta attributes that aren't set should be set now...
    foreach ($setAttribute as $sa) {
        if (!empty($toSet[$sa])) {
            $insertMetaValue[strtotime('today midnight')] = $toSet[$sa];
            $insert = json_encode($insertMetaValue);
            $wpdb->insert('wp_resorts_meta', [
                'ResortID' => $row->ResortID,
                'meta_key' => $sa,
                'meta_value' => $insert,
            ]);
        }
    }

    $dates['alertnotes'] = json_decode($row->AlertNote ?? '[]', true) ?? [];
    if (empty($dates['alertnotes'])) {
        $dates['alertnotes'] = [
            '0' => [
                [
                    'desc' => '',
                    'path' => [
                        'booking' => '0',
                        'profile' => '0',
                    ],
                ],
            ],
        ];
    }
    $row->dates = $dates;

    $sql = "SELECT * FROM wp_gpxTaxes";
    $row->taxes = $wpdb->get_results($sql);

    $wp_unit_type = $wpdb->prepare("SELECT *  FROM `wp_unit_type` WHERE `resort_id` = %s", $row->id);
    $row->unit_types = $wpdb->get_results($wp_unit_type, OBJECT_K);

    //how many welcome emails?

    $resortID4Owner = substr($row->gprID, 0, 15);
    $sql = $wpdb->prepare("SELECT DISTINCT ownerID FROM wp_owner_interval WHERE resortID=%s", $resortID4Owner);
    $allOwners = $wpdb->get_results($sql);
    $owners4Count = [];
    foreach ($allOwners as $oneOwner) {
        $owners4Count[] = $oneOwner->ownerID;
    }
    if (!empty($owners4Count)) {
        $placeholders = gpx_db_placeholders($owners4Count);
        $sql = $wpdb->prepare("SELECT COUNT(meta_value) as cnt FROM wp_usermeta WHERE meta_key='welcome_email_sent' AND user_id IN ({$placeholders})",
            array_values($owners4Count));
        $ownerCnt = $wpdb->get_var($sql);
    } else {
        $ownerCnt = 0;
    }
    $row->mlOwners = count($owners4Count) - $ownerCnt;

    $feeFields = Resort::feeFields()->pluck('name')->toArray();
    $fees = [];
    foreach ($feeFields as $field) {
        $value = $row->$field ?? null;
        if (null === $value) continue;
        $value = json_decode($value, true);
        foreach ($value as $dates => $feeValue) {
            if (!array_key_exists($dates, $fees)) {
                $date = explode('_', $dates);
                $fees[$dates] = [
                    'dates' => [
                        'key' => $dates,
                        'start' => date('Y-m-d', $date[0]),
                        'end' => ($date[1] ?? null) ? date('Y-m-d', $date[1]) : null,
                    ],
                    'resortFees' => [],
                    'ExchangeFeeAmount' => null,
                    'RentalFeeAmount' => null,
                    'CPOFeeAmount' => null,
                    'GuestFeeAmount' => null,
                    'UpgradeFeeAmount' => null,
                    'SameResortExchangeFee' => null,
                ];
            }
            if ($field === 'resortFees') {
                $fees[$dates][$field] = $feeValue;
            } else {
                $fees[$dates][$field] = is_array($feeValue) ? end($feeValue) : $feeValue;
            }
        }
    }
    uasort($fees, function ($a, $b) {
        if ($a['dates']['start'] !== $b['dates']['start']) {
            return $a['dates']['start'] <=> $b['dates']['start'];
        }

        return $a['dates']['end'] <=> $b['dates']['end'];
    });
    $row->dates['fees'] = $fees;
    if (empty($fees)) {
        $fees[] = [
            'dates' => [
                'key' => null,
                'start' => date('Y-m-d'),
                'end' => null,
            ],
            'resortFees' => [],
            'ExchangeFeeAmount' => null,
            'RentalFeeAmount' => null,
            'CPOFeeAmount' => null,
            'GuestFeeAmount' => null,
            'UpgradeFeeAmount' => null,
            'SameResortExchangeFee' => null,
        ];
    }
    $row->fees = array_values($fees);

    $regions = RegionRepository::instance()->breadcrumbs($row->gpxRegionID);
    $row->show_resort_fees = !empty(array_filter($regions, fn($region) => $region['show_resort_fees']));

    return $row;
}

/**
 * Is this called anywhere?
 * If it is, it should be replaced.
 * @deprecated
 */
function gpx_store_resort()
{
    global $wpdb;

    require_once WP_CONTENT_DIR . '/plugins/wp-store-locator/admin/class-geocode.php';
    $geocode = new WPSL_Geocode();

    $sql = "SELECT * FROM wp_resorts WHERE store_d=0";
    $results = $wpdb->get_results($sql);

    foreach ($results as $result) {

        $ResortName = $result->ResortName;
        $Description = $result->Description;
        $Address1 = $result->Address1;
        $Address2 = $result->Address2;
        $Town = $result->Town;
        $Region = $result->Region;
        $Country = $result->Country;
        $ImagePath1 = $result->ImagePath1;
        $ResortID = $result->ResortID;
        $URL = home_url() . "/resort-profile/?resort=" . $result->id;
        $ll = $result->LatitudeLongitude;
        $llSplit = explode(',', $ll);

        $post_id = wp_insert_post([
            'post_type' => 'wpsl_stores',
            'post_title' => $ResortName,
            'post_content' => $Description,
            'post_status' => 'publish',
            'comment_status' => 'closed',   // if you prefer
            'ping_status' => 'closed',      // if you prefer
        ]);

        if ($post_id) {
            // insert post meta
            add_post_meta($post_id, 'wpsl_address', $Address1);
            add_post_meta($post_id, 'wpsl_address2', $Address2);
            add_post_meta($post_id, 'wpsl_city', $Town);
            add_post_meta($post_id, 'wpsl_state', $Region);
            add_post_meta($post_id, 'wpsl_country', $Country);
            add_post_meta($post_id, 'wpsl_lat', $llSplit[0]);
            add_post_meta($post_id, 'wpsl_lng', $llSplit[1]);
            add_post_meta($post_id, 'wpsl_url', $URL);
            //add_post_meta($post_id, 'wpsl_hours', $custom3);
            add_post_meta($post_id, 'wpsl_resortid', $ResortID);
            add_post_meta($post_id, 'wpsl_thumbnail', $ImagePath1);
        }
        $wpdb->update('wp_resorts', ['store_d' => 1], ['id' => $result->id]);

        $geocode->check_geocode_data($post_id);

    }

    wp_send_json([]);
}

add_action('wp_ajax_gpx_store_resort', 'gpx_store_resort');
add_action('wp_ajax_nopriv_gpx_store_resort', 'gpx_store_resort');


/**
 *
 *
 *
 *
 */
function featured_gpx_resort()
{
    global $wpdb;

    $featured = $_POST['featured'];

    if ($featured == 0) {
        $newstatus = 1;
        $msg = "Resort is featured!";
        $fa = "fa-check-square";
    } else {
        $newstatus = 0;
        $msg = "Resort is not featured!";
        $fa = "fa-square";
    }

    $wpdb->update('wp_resorts', ['featured' => $newstatus], ['ResortID' => $_POST['resort']]);

    $data = ['success' => true, 'msg' => $msg, 'fastatus' => $fa, 'status' => $newstatus];

    wp_send_json($data);
}

add_action('wp_ajax_featured_gpx_resort', 'featured_gpx_resort');
add_action('wp_ajax_nopriv_featured_gpx_resort', 'featured_gpx_resort');

/**
 *
 *
 *
 *
 */
function ai_gpx_resort()
{
    global $wpdb;

    $ai = $_POST['ai'];

    if ($ai == 0) {
        $newstatus = 1;
        $msg = "Resort is AI!";
        $fa = "fa-check-square";
    } else {
        $newstatus = 0;
        $msg = "Resort is not AI!";
        $fa = "fa-square";
    }

    $wpdb->update('wp_resorts', ['ai' => $newstatus], ['ResortID' => $_POST['resort']]);

    $data = ['success' => true, 'msg' => $msg, 'fastatus' => $fa, 'status' => $newstatus];

    wp_send_json($data);
}

add_action('wp_ajax_ai_gpx_resort', 'ai_gpx_resort');
add_action('wp_ajax_nopriv_ai_gpx_resort', 'ai_gpx_resort');


/**
 *
 *
 *
 *
 */
function gpx_resort_image_update_attr()
{
    if (!empty($_POST['id'])) {
        $id = $_POST['id'];
        $image = [
            'ID' => $id,
            'post_title' => $_POST['title'],
        ];
        wp_update_post($image);

        update_post_meta($id, '_wp_attachment_image_alt', $_POST['alt']);
        update_post_meta($id, 'gpx_image_video', $_POST['video']);
        //update the image url
    }
    $data = ['success' => true];

    wp_send_json($data);
}

add_action('wp_ajax_gpx_resort_image_update_attr', 'gpx_resort_image_update_attr');


/**
 *
 *
 *
 *
 */
function guest_fees_gpx_resort()
{
    global $wpdb;

    $enabled = $_POST['enabled'];

    if ($enabled == 0) {
        $newstatus = 1;
        $msg = "Guest fees for this resort are enabled!";
        $fa = "fa-check-square";
    } else {
        $newstatus = 0;
        $msg = "Guest fees for this resort are not enabled!";
        $fa = "fa-square";
    }

    $wpdb->update('wp_resorts', ['guestFeesEnabled' => $newstatus], ['ResortID' => $_POST['resort']]);

    $data = ['success' => true, 'msg' => $msg, 'gfstatus' => $fa, 'status' => $newstatus];

    wp_send_json($data);
}

add_action('wp_ajax_guest_fees_gpx_resort', 'guest_fees_gpx_resort');
add_action('wp_ajax_nopriv_guest_fees_gpx_resort', 'guest_fees_gpx_resort');

function gpx_resort_third_party_deposit_fees()
{
    global $wpdb;
    $resort_id = gpx_request('resort');
    if(!$resort_id){
        wp_send_json_error(['success' => false, 'msg' => 'Resort not found'], 404);
    }
    $sql = $wpdb->prepare("SELECT id, third_party_deposit_fee_enabled FROM wp_resorts WHERE ResortID=%s", $resort_id);
    $resort = $wpdb->get_row($sql);
    if(!$resort){
        wp_send_json_error(['success' => false, 'msg' => 'Resort not found'], 404);
    }
    $enabled = !!$resort->third_party_deposit_fee_enabled;

    if ($enabled) {
        $newstatus = 0;
        $msg = "Third party deposit fees for this resort are not enabled!";
        $fa = "fa-square";
    } else {
        $newstatus = 1;
        $msg = "Third party deposit fees for this resort are enabled!";
        $fa = "fa-check-square";
    }

    $wpdb->update('wp_resorts', ['third_party_deposit_fee_enabled' => $newstatus], ['ResortID' => $resort_id]);

    $data = ['success' => true, 'msg' => $msg, 'gfstatus' => $fa, 'status' => $newstatus];

    wp_send_json($data);
}

add_action('wp_ajax_gpx_resort_third_party_deposit_fees', 'gpx_resort_third_party_deposit_fees');


function gpx_get_resort_attributes($post) {
    global $wpdb;

    extract($post);

    //these don't need a date anymore
    $nodates = [
        'ada',
        'attributes',
        'UnitFacilities',
        'ResortFacilities',
        'AreaFacilities',
        'UnitConfig',
        'CommonArea',
        'UponRequest',
        'UponRequest',
        'GuestBathroom',
    ];
    $sql = $wpdb->prepare("SELECT * FROM wp_resorts WHERE ResortID=%s", [$resortID]);
    $resort = $wpdb->get_row($sql, ARRAY_A);
    if (in_array($type, $nodates)) {
        $newValue = json_decode($resort[$type] ?? '[]', true);
        $newValue[] = $val;
        $newValue = json_encode(array_values($newValue));
        $wpdb->update('wp_resorts', [$type => $newValue], ['ResortID' => $resortID]);
    } else {
        $wpdb->update('wp_resorts', [$type => $val], ['ResortID' => $resortID]);
    }

    $sql = $wpdb->prepare("SELECT id, meta_value FROM wp_resorts_meta WHERE ResortID=%s AND meta_key=%s",
        [$resortID, $type]);
    $rm = $wpdb->get_row($sql);
    //$attributeKey is the old date range
    $attributeKey = gpx_get_attribute_key($oldfrom, $oldto, $oldorder);

    //updateAttributeKey is the new date range
    $newAttributeKey = gpx_get_attribute_key($from, $to, $oldorder);

    if (empty($rm)) {
        $toSet = json_decode($resort->$type ?? '[]');
        $metaValue[$newAttributeKey] = $toSet;
        $insert = json_encode($metaValue);
        $wpdb->insert('wp_resorts_meta',
            ['ResortID' => $resortID, 'meta_key' => $type, 'meta_value' => $insert]);
        $updateID = $wpdb->insert_id;
        $sql = $wpdb->prepare("SELECT id, meta_value FROM wp_resorts_meta WHERE id=%s", $updateID);
        $rm = $wpdb->get_row($sql);
    }


    if (!empty($rm)) {
        $metaValue = json_decode($rm->meta_value, true);

        if (in_array($type, $nodates)) {
            $ark = array_keys($metaValue);
            $newAttributeKey = $attributeKey = $ark[0];
        }

        if (isset($metaValue[$attributeKey])) {
            foreach ($metaValue[$attributeKey] as $v) {
                $attributes[] = $v;
            }
            //if the' $attributeKey != $newAttibuteKey then this is an update -- remove the original one
            unset($metaValue[$attributeKey]);

            if (isset($descs)) {
                $insertVal[] = [
                    'path' => [
                        'booking' => $bookingpathdesc,
                        'profile' => $resortprofiledesc,
                    ],
                    'desc' => $val,
                ];
            } else {
                $insertVal[] = $val;
            }
            //                 }
            if (!empty($list)) {
                foreach ($list as $l) {
                    $insertVal[] = $l;
                }
            }
            foreach ($insertVal as $newVal) {
                if (!empty($newVal)) {
                    $attributes[] = $newVal;
                }
            }
            $count = count($attributes);

            $metaValue[$newAttributeKey] = $attributes;
        } else {
            if (!empty($val)) {
                if (isset($descs)) {
                    $insertVal[] = [
                        'path' => [
                            'booking' => $bookingpathdesc,
                            'profile' => $resortprofiledesc,
                        ],
                        'desc' => $val,
                    ];
                } else {
                    $insertVal[] = $val;
                }
            } elseif ($bookingpathdesc || $resortprofiledesc) {
                $insertVal[] = [
                    'path' => [
                        'booking' => $bookingpathdesc,
                        'profile' => $resortprofiledesc,
                    ],
                    'desc' => $val,
                ];
            } elseif ($descs) {
                $insertVal[] = [
                    'path' => [
                        'booking' => $bookingpathdesc,
                        'profile' => $resortprofiledesc,
                    ],
                    'desc' => $val,
                ];
            }
            if (!empty($list)) {
                foreach ($list as $l) {
                    $insertVal[] = $l;
                }
                foreach ($insertVal as $newVal) {
                    if (!empty($newVal)) {
                        $metaValue[$newAttributeKey] = $newVal;
                    }
                }
            } else {
                $metaValue[$newAttributeKey] = $insertVal;
            }

            $count = count($metaValue[$newAttributeKey]);
        }
        if ($val == 'remove' || $val == 'delete') {
            //this should be removed...
            unset($metaValue[$newAttributeKey]);
        }
        $wpdb->update('wp_resorts_meta', ['meta_value' => json_encode($metaValue)], ['id' => $rm->id]);
    } else {
        $attributes[] = $val;
        $count = count($attributes);

        if (isset($descs)) {
            $insert[$newAttributeKey][] = [
                'path' => [
                    'booking' => $bookingpathdesc,
                    'profile' => $resortprofiledesc,
                ],
                'desc' => $val,
            ];
        } else {
            $insert = [
                $newAttributeKey => $attributes,
            ];
        }

        $wpdb->insert('wp_resorts_meta',
            ['ResortID' => $resortID, 'meta_key' => $type, 'meta_value' => json_encode($insert)]);
    }


    $msg = 'Insert Successful';

    $data = ['success' => true, 'msg' => $msg, 'count' => $count];

    return $data;
}

/**
 *
 *
 *
 *
 */
function gpx_resort_attribute_new()
{
    $post['resortID'] = $_POST['resort'];
    $post['type'] = $_POST['type'];
    $post['val'] = $_POST['val'];
    $post['from'] = $_POST['from'];
    $post['oldfrom'] = $_POST['oldfrom'];
    $post['to'] = $_POST['to'];
    $post['oldto'] = $_POST['oldto'];
    $post['list'] = $_POST['list'] ?? null;
    $post['oldorder'] = $_POST['oldorder'] ?? null;
    if (!empty($_POST['descs'])) {
        $post['bookingpathdesc'] = $_POST['bookingpathdesc'];
        $post['resortprofiledesc'] = $_POST['resortprofiledesc'];
        $post['descs'] = $_POST['descs'];
    }
    $data = gpx_get_resort_attributes($post);
    if (in_array($post['type'], ['PostCode', 'Address1', 'Address2', 'Town', 'Region', 'Country'])) {
        DB::table('wp_resorts')
            ->where('ResortID', '=', $post['resortID'])
            ->update(['geocode_status' => null]);
    }

    wp_send_json($data);
}

add_action('wp_ajax_gpx_resort_attribute_new', 'gpx_resort_attribute_new');


function gpx_admin_resort_description_edit()
{
    /** @var EditDescriptionForm $form */
    $form = gpx(EditDescriptionForm::class);
    $values = $form->validate();

    $resort = Resort::findByResortId($values['resort']);
    $resort->fill([$values['field'] => $values['value']]);

    if (in_array($values['field'], ['PostCode', 'Address1', 'Address2', 'Town', 'Region', 'Country'])) {
        $resort->geocode_status = null;
    }

    $resort->save();
    gpx_logger()->info(sprintf('Admin %s updated %s for resort %s', get_current_user_id(), $values['field'], $values['resort']));

    wp_send_json(['success' => true, 'message' => sprintf('Field %s changed', $values['field']), 'data' => $values]);
}

add_action('wp_ajax_gpx_admin_resort_description_edit', 'gpx_admin_resort_description_edit');

function gpx_admin_resort_alert_save()
{
    global $wpdb;
    /** @var EditAlertNoteForm $form */
    $form = gpx(EditAlertNoteForm::class);
    $values = $form->validate();
    $key = (string)Carbon::parse($values['from'])->startOfDay()->timestamp;
    if ($values['to']) {
        $key .= '_' . Carbon::parse($values['to'])->startOfDay()->timestamp;
    }
    $value = [
        'path' => [
            'booking' => $values['booking'] ? "1" : "0",
            'profile' => $values['profile'] ? "1" : "0",
        ],
        'desc' => $values['value'],
    ];
    $sql = $wpdb->prepare("SELECT * FROM wp_resorts_meta WHERE ResortID = %s AND meta_key = 'AlertNote' LIMIT 1", [$values['resort']]);
    $record = $wpdb->get_row($sql);
    if ($record) {
        $data = json_decode($record->meta_value, true);
        $data = array_map(fn($row) => array_values($row), $data);
    } else {
        $data = [];
    }
    if (array_key_exists($key, $data)) {
        if ($key != $values['oldDates']) {
            wp_send_json(['success' => false, 'alert' => 'Another alert note is already using this active date range.', 'data' => $values], 422);
        }
        $current = Arr::last($data[$key]);
        if ($current['desc'] === $value['desc'] && $current['path']['booking'] == $value['path']['booking'] && $current['path']['profile'] == $value['path']['profile']) {
            wp_send_json(['success' => true, 'message' => 'Current data not changed.', 'key' => $key, 'data' => $values]);
        }
    }
    if (array_key_exists($values['oldDates'], $data)) {
        unset($data[$values['oldDates']]);
    }
    $data[$key] = [$value];
    ksort($data);
    if ($record) {
        $wpdb->update('wp_resorts_meta', [
            'meta_value' => json_encode($data),
        ], ['id' => $record->id]);
    } else {
        $wpdb->insert('wp_resorts_meta', [
            'ResortID' => $values['resort'],
            'meta_key' => 'AlertNote',
            'meta_value' => json_encode($data),
        ]);
    }

    wp_send_json([
        'success' => true,
        'message' => 'Alert note saved.',
        'key' => $key,
        'data' => $values
    ]);
}

add_action('wp_ajax_gpx_admin_resort_alert_save', 'gpx_admin_resort_alert_save');

function gpx_resort_remove_alert()
{
    /** @var RemoveAlertNoteForm $form */
    $form = gpx(RemoveAlertNoteForm::class);
    $values = $form->validate();
    global $wpdb;
    $resort = $values['resort'];
    $key = $values['oldDates'];

    $sql = $wpdb->prepare("SELECT * FROM wp_resorts_meta WHERE ResortID = %s AND meta_key = 'AlertNote' LIMIT 1", [$resort]);
    $record = $wpdb->get_row($sql);
    if (!$record) {
        wp_send_json([
            'success' => true,
            'message' => 'No alert notes to remove.',
        ]);
    }
    $data = json_decode($record->meta_value, true);
    $data = array_map(fn($row) => array_values($row), $data);

    if (!array_key_exists($key, $data)) {
        wp_send_json([
            'success' => true,
            'message' => 'Requested alert note not found.',
        ]);
    }

    unset($data[$key]);
    ksort($data);

    $wpdb->update('wp_resorts_meta', [
        'meta_value' => json_encode($data),
    ], ['id' => $record->id]);

    wp_send_json([
        'success' => true,
        'message' => 'Alert note removed.',
    ]);
}

add_action('wp_ajax_gpx_resort_remove_alert', 'gpx_resort_remove_alert');

/**
 *
 *
 *
 *
 */
function gpx_resort_attribute_remove()
{
    global $wpdb;

    $resortID = $_POST['resort'];
    $item = $_POST['item'];
    $type = $_POST['type'];
    $attributeKey = '0';

    if (!empty($from)) {
        $from = date('Y-m-d 00:00:00', strtotime($from));
        $attributeKey = strtotime($from);
    }
    if (!empty($to)) {
        $to = date('Y-m-d 00:00:00', strtotime($to));
        $attributeKey .= "_" . strtotime($to);
    }
    $sql = $wpdb->prepare("SELECT * FROM wp_resorts WHERE ResortID=%s", [$resortID]);
    $resort = $wpdb->get_row($sql);

    $sql = $wpdb->prepare("SELECT id, meta_value FROM wp_resorts_meta WHERE ResortID=%s AND meta_key=%s", [
        $resortID,
        $type,
    ]);
    $rm = $wpdb->get_row($sql);

    $nodates = [
        'ada',
        'attributes',
        'UnitFacilities',
        'ResortFacilities',
        'AreaFacilities',
        'UnitConfig',
        'CommonArea',
        'UponRequest',
        'UponRequest',
        'GuestBathroom',
    ];

    if (!empty($rm)) {
        $metaValue = json_decode($rm->meta_value, true);
        if (!isset($metaValue[$attributeKey])) {
            end($metaValue);
            $attributeKey = key($metaValue);
            reset($metaValue);
        }

        $attributes = $metaValue[$attributeKey];
        unset($attributes[$item]);
        $metaValue[$attributeKey] = $attributes;

        $wpdb->update('wp_resorts_meta', ['meta_value' => json_encode($metaValue)], ['id' => $rm->id]);

        if (in_array($type, $nodates)) {
            $wpdb->update('wp_resorts', [$type => json_encode(array_values(Arr::last($metaValue)))], ['id' => $resort->id]);
        }
    }

    $msg = 'Remove Successful';

    $data = ['success' => true, 'msg' => $msg];

    wp_send_json($data);
}

add_action('wp_ajax_gpx_resort_attribute_remove', 'gpx_resort_attribute_remove');


/**
 *
 *
 *
 *
 */
function gpx_resort_attribute_reorder()
{
    global $wpdb;

    $from = gpx_input('from');
    $to = gpx_input('to');
    $resortID = gpx_input('resortID');
    $type = gpx_input('type');
    $order = [];
    foreach ($_POST as $postKey => $post) {
        if (is_array($post)) {
            $order = array_values($post);
        }
    }


    $attributeKey = '0';
    if (!empty($from)) {
        $attributeKey = strtotime($from);
    }
    if (!empty($to)) {
        $attributeKey .= "_" . strtotime($to);
    }

    $sql = $wpdb->prepare("SELECT id, meta_value FROM wp_resorts_meta WHERE ResortID=%s AND meta_key=%s", [
        $resortID,
        $type,
    ]);
    $rm = $wpdb->get_row($sql);

    if (!empty($rm)) {
        $metaValue = json_decode($rm->meta_value, true);
        $updateID = $rm->id;
    } else {
        $sql = $wpdb->prepare('SELECT ' . gpx_esc_table($type) . ' FROM wp_resorts WHERE ResortID=%s', $resortID);
        $res = $wpdb->get_row($sql);

        if (!empty($res)) {
            $toSet = json_decode($res->$type);
            $metaValue[$attributeKey] = $toSet;
            $insert = json_encode($metaValue);
            $wpdb->insert('wp_resorts_meta', [
                'ResortID' => $resortID,
                'meta_key' => $type,
                'meta_value' => $insert,
            ]);
            $updateID = $wpdb->insert_id;
        }
    }
    if (!empty($metaValue)) {
        if (!isset($metaValue[$attributeKey])) {
            end($metaValue);
            $attributeKey = key($metaValue);
            reset($metaValue);
        }

        $attributes = $metaValue[$attributeKey];

        foreach ($order as $o) {
            $newOrder[] = $attributes[$o];
        }
        $metaValue[$attributeKey] = $newOrder;

        $wpdb->update('wp_resorts_meta', ['meta_value' => json_encode($metaValue)], ['id' => $updateID]);
    }

    $msg = 'Reorder Successful';

    $data = ['success' => true, 'msg' => $msg];

    wp_send_json($data);
}

add_action('wp_ajax_gpx_resort_attribute_reorder', 'gpx_resort_attribute_reorder');

function gpxadmin_resort_edit_fees()
{
    global $wpdb;
    /** @var EditResortFeesForm $form */
    $form = gpx(EditResortFeesForm::class);
    $values = $form->validate();

    $resort = Resort::find($values['resort']);
    $sql = $wpdb->prepare("SELECT `meta_key`,`id`,`meta_value` FROM wp_resorts_meta WHERE ResortID = %s AND meta_key IN ('resortFees', 'ExchangeFeeAmount', 'RentalFeeAmount', 'CPOFeeAmount', 'GuestFeeAmount', 'UpgradeFeeAmount', 'SameResortExchangeFee')", [$resort->ResortID]);
    $meta = $wpdb->get_results($sql, OBJECT_K);
    $meta = array_map(fn($row) => ['id' => $row->id, 'meta_value' => json_decode($row->meta_value, true)], (array)$meta);
    $meta = [
        'resortFees' => $meta['resortFees'] ?? ['id' => null, 'meta_value' => []],
        'ExchangeFeeAmount' => $meta['ExchangeFeeAmount'] ?? ['id' => null, 'meta_value' => []],
        'RentalFeeAmount' => $meta['RentalFeeAmount'] ?? ['id' => null, 'meta_value' => []],
        'CPOFeeAmount' => $meta['CPOFeeAmount'] ?? ['id' => null, 'meta_value' => []],
        'GuestFeeAmount' => $meta['GuestFeeAmount'] ?? ['id' => null, 'meta_value' => []],
        'UpgradeFeeAmount' => $meta['UpgradeFeeAmount'] ?? ['id' => null, 'meta_value' => []],
        'SameResortExchangeFee' => $meta['SameResortExchangeFee'] ?? ['id' => null, 'meta_value' => []],
    ];
    $start = Carbon::parse($values['start'])->startOfDay();
    $end = $values['end'] ? Carbon::parse($values['end'])->startOfDay() : null;
    $key = (string)$start->timestamp;
    if ($end) $key .= '_' . $end->timestamp;
    if ($values['key'] && $values['key'] != $key) {
        foreach ($meta as $field => $value) {
            if (array_key_exists($key, $meta[$field]['meta_value'])) {
                wp_send_json([
                    'success' => false,
                    'message' => 'The date range you entered already exists.',
                    'errors' => ['start' => ['The date range you entered already exists.']],
                ], 422);
            }
        }
    }

    foreach ($meta as $field => $value) {
        if ($values['key'] && array_key_exists($values['key'], $meta[$field]['meta_value']) && $values['key'] != $key) {
            $meta[$field]['meta_value'][$key] = $meta[$field]['meta_value'][$values['key']];
            unset($meta[$field]['meta_value'][$values['key']]);
        }
        if ($field == 'resortFees') {
            $meta[$field]['meta_value'][$key] = array_values(array_map(fn($v) => (float)$v == (int)$v ? (int)$v : round((float)$v, 2), $values[$field] ?? []));
            if ($values['resortFee'] > 0) {
                $meta[$field]['meta_value'][$key][] = (float)$values['resortFee'] == (int)$values['resortFee'] ? (int)$values['resortFee'] : round((float)$values['resortFee'], 2);
            }
            if (empty($meta[$field]['meta_value'][$key])) {
                unset($meta[$field]['meta_value'][$key]);
            }
        } elseif (array_key_exists($field, $values)) {
            if ($values[$field] > 0) {
                $meta[$field]['meta_value'][$key] = [(float)$values[$field] == (int)$values[$field] ? (int)$values[$field] : round((float)$values[$field], 2)];
            } else {
                unset($meta[$field]['meta_value'][$key]);
            }
        }
        if (empty($meta[$field]['meta_value'])) {
            $wpdb->delete('wp_resorts_meta', [
                'ResortID' => $resort->ResortID,
                'meta_key' => $field,
            ]);
        } elseif ($meta[$field]['id']) {
            $wpdb->update('wp_resorts_meta', ['meta_value' => json_encode($meta[$field]['meta_value'])], ['id' => $meta[$field]['id']]);
        } else {
            $wpdb->insert('wp_resorts_meta', [
                'ResortID' => $resort->ResortID,
                'meta_key' => $field,
                'meta_value' => json_encode($meta[$field]['meta_value']),
            ]);
        }
    }

    wp_send_json(['success' => true], 200);
}

add_action('wp_ajax_gpxadmin_resort_edit_fees', 'gpxadmin_resort_edit_fees');

function gpxadmin_resort_copy_fees()
{
    global $wpdb;
    /** @var CopyResortFeesForm $form */
    $form = gpx(CopyResortFeesForm::class);
    $values = $form->validate();

    $resort = Resort::find($values['resort']);
    $current = $values['key'];

    $start = Carbon::parse($values['start'])->startOfDay();
    $end = $values['end'] ? Carbon::parse($values['end'])->startOfDay() : null;
    $key = (string)$start->timestamp;
    if ($end) $key .= '_' . $end->timestamp;

    $sql = $wpdb->prepare("SELECT `meta_key`,`id`,`meta_value` FROM wp_resorts_meta WHERE ResortID = %s AND meta_key IN ('resortFees', 'ExchangeFeeAmount', 'RentalFeeAmount', 'CPOFeeAmount', 'GuestFeeAmount', 'UpgradeFeeAmount', 'SameResortExchangeFee')", [$resort->ResortID]);
    $meta = $wpdb->get_results($sql, OBJECT_K);
    $meta = array_map(fn($row) => ['id' => $row->id, 'meta_value' => json_decode($row->meta_value, true)], (array)$meta);
    foreach ($meta as $field => $value) {
        if (array_key_exists($key, $meta[$field]['meta_value'])) {
            wp_send_json([
                'success' => false,
                'message' => 'The date range you entered already exists.',
                'errors' => ['start' => ['The date range you entered already exists.']],
            ], 422);
        }
    }

    foreach ($meta as $field => $data) {
        if (!array_key_exists($current, $meta[$field]['meta_value'])) {
            continue;
        }
        $meta[$field]['meta_value'][$key] = $meta[$field]['meta_value'][$current];
        $wpdb->update('wp_resorts_meta', ['meta_value' => json_encode($meta[$field]['meta_value'])], ['id' => $meta[$field]['id']]);
    }

    wp_send_json(['success' => true], 200);
}

add_action('wp_ajax_gpxadmin_resort_copy_fees', 'gpxadmin_resort_copy_fees');

function gpxadmin_resort_delete_fees()
{
    global $wpdb;
    /** @var DeleteResortFeesForm $form */
    $form = gpx(DeleteResortFeesForm::class);
    $values = $form->validate();

    $resort = Resort::find($values['resort']);
    $key = $values['key'];

    $sql = $wpdb->prepare("SELECT `meta_key`,`id`,`meta_value` FROM wp_resorts_meta WHERE ResortID = %s AND meta_key IN ('resortFees', 'ExchangeFeeAmount', 'RentalFeeAmount', 'CPOFeeAmount', 'GuestFeeAmount', 'UpgradeFeeAmount', 'SameResortExchangeFee')", [$resort->ResortID]);
    $meta = $wpdb->get_results($sql, OBJECT_K);
    $meta = array_map(fn($row) => ['id' => $row->id, 'meta_value' => json_decode($row->meta_value, true)], (array)$meta);
    foreach ($meta as $field => $data) {
        if (!array_key_exists($key, $meta[$field]['meta_value'])) {
            continue;
        }
        unset($meta[$field]['meta_value'][$key]);
        if (empty($meta[$field]['meta_value'])) {
            $wpdb->delete('wp_resorts_meta', [
                'ResortID' => $resort->ResortID,
                'meta_key' => $field,
            ]);
        } else {
            $wpdb->update('wp_resorts_meta', ['meta_value' => json_encode($meta[$field]['meta_value'])], ['id' => $meta[$field]['id']]);
        }
    }

    wp_send_json(['success' => true], 200);
}

add_action('wp_ajax_gpxadmin_resort_delete_fees', 'gpxadmin_resort_delete_fees');


/**
 *
 *
 *
 *
 */
function gpx_resort_image_reorder()
{
    global $wpdb;

    foreach ($_POST as $postKey => $post) {
        if (is_array($post)) {
            $input['order'] = $post;
        } else {
            $input[$postKey] = $post;
        }
    }

    $resortID = $input['resortID'] ?? null;
    $type = $input['type'] ?? null;
    $order = $input['order'] ?? [];
    $attributes = $input['attributes'] ?? [];
    $newOrder = [];


    $sql = $wpdb->prepare("SELECT id, meta_value FROM wp_resorts_meta WHERE ResortID=%s AND meta_key=%s", [
        $resortID,
        $type,
    ]);
    $rm = $wpdb->get_row($sql);

    if (!empty($rm)) {
        $attributes = json_decode($rm->meta_value, true);
        foreach ($order as $o) {
            $newOrder[] = $attributes[$o];
        }


        $wpdb->update('wp_resorts_meta', ['meta_value' => json_encode($newOrder)], ['id' => $rm->id]);
    } else {
        foreach ($order as $o) {
            $newOrder[] = $attributes[$o];
        }
        $wpdb->insert('wp_resorts_meta', ['meta_value' => json_encode($newOrder), 'meta_key' => 'images']);
    }
    $msg = 'Reorder Successful';

    $data = ['success' => true, 'msg' => $msg];

    wp_send_json($data);
}

add_action('wp_ajax_gpx_resort_image_reorder', 'gpx_resort_image_reorder');


/**
 *
 *
 *
 *
 */
function gpx_resort_repeatable_remove()
{
    global $wpdb;

    $from = $_POST['from'];
    $to = $_POST['to'];
    $type = $_POST['type'];
    $resortID = $_POST['resortID'];
    $oldorder = $_POST['oldorder'];
    $attributeKey = '0';

    if (!empty($from)) {
        $from = date('Y-m-d H:i:s', strtotime($from . '-12 hours'));
        $attributeKey = strtotime($from);
        if (isset($oldorder) && !empty($oldorder) && strtolower($oldorder) != 'undefined') {
            $attributeKey += $oldorder;
        }
    }
    if (!empty($to)) {
        $to = date('Y-m-d 23:59:59', strtotime($to . '+1 day'));
        $attributeKey .= "_" . strtotime($to);
    }

    $rmGroups = [
        'AlertNote' => 'descriptions',
        'AreaDescription' => 'descriptions',
        'UnitDescription' => 'descriptions',
        'AdditionalInfo' => 'descriptions',
        'Description' => 'descriptions',
        'Website' => 'descriptions',
        'CheckInDays' => 'descriptions',
        'CheckInEarliest' => 'descriptions',
        'CheckInLatest' => 'descriptions',
        'CheckOutEarliest' => 'descriptions',
        'CheckOutLatest' => 'descriptions',
        'Address1' => 'descriptions',
        'Address2' => 'descriptions',
        'Town' => 'descriptions',
        'Region' => 'descriptions',
        'Country' => 'descriptions',
        'PostCode' => 'descriptions',
        'Phone' => 'descriptions',
        'Fax' => 'descriptions',
        'Airport' => 'descriptions',
        'Directions' => 'descriptions',
        'unitFacilities' => 'attributes',
        'resortFacilities' => 'attributes',
        'areaFacilities' => 'attributes',
        'UnitConfig' => 'attributes',
        'GuestFeeAmount' => 'fees',
        'resortFees' => 'fees',
        'RentalFeeAmount' => 'fees',
        'ExchangeFeeAmount' => 'fees',
        'CPOFeeAmount' => 'fees',
        'UpgradeFeeAmount' => 'fees',
        'SameResortExchangeFee' => 'fees',
    ];
    $ins = [];
    foreach ($rmGroups as $rmK => $rmV) {
        if ($type == $rmV) {
            $ins[] = $rmK;
        }
    }

    $mkIns = '';
    if (!empty($ins)) {
        $placeholders = gpx_db_placeholders($ins, '%s');
        $mkIns = $wpdb->prepare(" AND meta_key IN ({$placeholders})", array_values($ins));
    }

    $sql = $wpdb->prepare("SELECT id, meta_value FROM wp_resorts_meta WHERE ResortID=%s ", $resortID) . $mkIns;
    $rms = $wpdb->get_results($sql);


    if (!empty($rms)) {
        foreach ($rms as $rm) {
            $metaValue = json_decode($rm->meta_value, true);
            foreach ($metaValue as $mk => $mv) {
                $splitAttribute = explode("_", $mk);

                if (!empty($from)) {

                    $fromR1 = strtotime($from . ' -36 hours');
                    $fromR2 = strtotime($from . ' +43 hours');
                    if (substr($splitAttribute[0], 0, 10) >= $fromR1 && substr($splitAttribute[0], 0, 10) <= $fromR2) {
                        $attributeKey = $mk;
                    }
                    if (!empty($to)) {
                        $attributeKey = $attributeKey;
                        $toR1 = strtotime($to . ' -36 hours');
                        $toR2 = strtotime($to . ' +36 hours');

                        if (substr($splitAttribute[1], 0, 10) >= $toR1 && substr($splitAttribute[1], 0, 10) <= $toR2) {
                            $attributeKey = $mk;
                        }
                    }
                }
            }

            unset($metaValue[$attributeKey]);

            $wpdb->update('wp_resorts_meta', ['meta_value' => json_encode($metaValue)], ['id' => $rm->id]);
        }
    }

    $msg = 'Remove Successful';

    $data = ['success' => true, 'msg' => $msg];

    wp_send_json($data);
}

add_action('wp_ajax_gpx_resort_repeatable_remove', 'gpx_resort_repeatable_remove');


/**
 *
 *
 *
 *
 */
function gpx_image_remove()
{
    global $wpdb;

    $sql = $wpdb->prepare("SELECT id, meta_value FROM wp_resorts_meta WHERE meta_key='images' AND ResortID=%s",
        $_POST['resortID']);
    $row = $wpdb->get_row($sql);
    $images = json_decode($row->meta_value);

    unset($images[$_POST['image']]);

    $updateImages = ['meta_value' => json_encode($images)];

    $wpdb->update('wp_resorts_meta', $updateImages, ['id' => $row->id]);

    $data['success'] = true;

    wp_send_json($data);
}

add_action('wp_ajax_gpx_image_remove', 'gpx_image_remove');


/**
 *
 *
 *
 *
 */
function active_gpx_resort()
{
    global $wpdb;

    $active = $_POST['active'];

    if ($active == 0) {
        $newstatus = 1;
        $msg = "Resort is Active!";
        $fa = "fa-check-square";
    } else {
        $newstatus = 0;
        $msg = "Resort is not active!";
        $fa = "fa-square";
    }

    $wpdb->update('wp_resorts', ['active' => $newstatus], ['ResortID' => $_POST['resort']]);

    $data = ['success' => true, 'msg' => $msg, 'fastatus' => $fa, 'status' => $newstatus];

    wp_send_json($data);
}

add_action('wp_ajax_active_gpx_resort', 'active_gpx_resort');
add_action('wp_ajax_nopriv_active_gpx_resort', 'active_gpx_resort');

function get_gpx_list_resorts()
{
    $value = gpx_request('value');
    $type = gpx_request('type', '');

    global $wpdb;
    $data = [];

    if (!empty($type)) {
        $opType = $type . "_";
    }

    $sql = $wpdb->prepare("SELECT lft, rght FROM wp_gpxRegion WHERE id=%d", $value);
    $row = $wpdb->get_row($sql);
    $sql = $wpdb->prepare("SELECT id FROM wp_gpxRegion WHERE lft >= %d AND rght <= %d", [$row->lft, $row->rght]);
    $results = $wpdb->get_results($sql);
    foreach ($results as $result) {
        $gpxRegionID = $result->id;
        $sql = $wpdb->prepare("SELECT id, ResortName from wp_resorts WHERE gpxRegionID=%s", $gpxRegionID);
        $resortResult = $wpdb->get_results($sql);
        if (!empty($resortResult)) {
            $resortslist[] = $resortResult;
        }
    }


    if (isset($resortslist) && !empty($resortslist)) {
        foreach ($resortslist as $resorts) {
            foreach ($resorts as $resort) {
                $ops[$resort->id] = $resort->ResortName;
            }
        }

        asort($ops);
        $data = '<div class="form-group parent-delete">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-name">Resort
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-11">
                      <select name="' . $opType . 'resort[]" class="form-control col-md-7 col-xs-12">
                      	  <option></option>';
        foreach ($ops as $rkey => $rval) {
            $data .= '<option value="' . esc_attr($rkey) . '">' . esc_html($rval) . '</option>';
        }
        $data .= '
                      </select>
                    </div>
                    <div class="col-xs-1 remove-element">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                    </div>
                  </div>';
    } else {
        $data = '<div class="flash-msg">There aren\'t any resorts in the selected region';
    }

    wp_send_json(['html' => $data]);
}

add_action('wp_ajax_get_gpx_list_resorts', 'get_gpx_list_resorts');
add_action('wp_ajax_nopriv_get_gpx_list_resorts', 'get_gpx_list_resorts');

/**
 *
 *
 *
 *
 */
function gpx_autocomplete_resort_fn()
{
    $term = (!empty($_GET['term'])) ? sanitize_text_field($_GET['term']) : '';
    $term = stripslashes($term);

    $resorts = Resort::select(['id', 'ResortName'])
                     ->active()
                     ->when(empty($term), fn($query) => $query->featured())
                     ->orderBy('ResortName')
                     ->get()
    ->pluck('ResortName')
    ->values();


    $resort_search = [];
    if (!empty($term)) {
        foreach ($resorts as $item) {
            $pos = strpos(strtolower($item), strtolower($term));
            if ($pos !== false) {
                $resort_search[] = $item;
            }
        }
        $resorts = $resort_search;
    }
    wp_send_json($resorts);
}

add_action("wp_ajax_gpx_autocomplete_resort", "gpx_autocomplete_resort_fn");
add_action("wp_ajax_nopriv_gpx_autocomplete_resort", "gpx_autocomplete_resort_fn");


/**
 *
 *
 *
 *
 */
/*
 * Report Writer Submit
 * Store details that were added to the form then open the table page
 */
function gpx_report_write()
{
    global $wpdb;

    if (isset($_POST['reportType'])) {
        if ($_POST['reportType'] == 'Group') {
            $role = implode(",", $_POST['role']);
        }
        if (!empty($_POST['condition'])) {
            $cj = json_decode(stripslashes($_POST['condition']), true);
            $co = json_decode(stripslashes($_POST['operator']), true);
            $cod = json_decode(stripslashes($_POST['operand']), true);
            $cv = json_decode(stripslashes($_POST['conditionValue']), true);
            for ($i = 1; $i <= $_POST['gps']; $i++) {
                $conditions[] = [
                    'condition' => $cj[$i],
                    'operator' => $co[$i],
                    'operand' => $cod[$i],
                    'conditionValue' => $cv[$i],
                ];
            }
        }

        $insert = [
            'name' => $_POST['name'],
            'data' => json_encode($_POST['data']),
            'reportType' => $_POST['reportType'],
            'role' => $role,
            'emailrepeat' => $_POST['emailrepeat'],
            'emailrecipients' => $_POST['emailrecipients'],
            'conditions' => json_encode($conditions),
            'formData' => base64_encode($_POST['form']),
            'userID' => get_current_user_id(),

        ];
        if (isset($_REQUEST['editid'])) {
            $updateYes = false;
            $sql = $wpdb->prepare("SELECT name, reportType, userID FROM wp_gpx_report_writer WHERE id=%s",
                $_REQUEST['editid']);
            $thisReport = $wpdb->get_row($sql);

            if (!empty($thisReport) && $thisReport->name == $_REQUEST['name'] && $thisReport->reportType == $_REQUEST['reportType']) {
                $updateYes = true;
            }


            if ($updateYes) {
                //only the owner can update a universal report. Change all others to single.
                if ($_POST['reportType'] == 'Universal') {
                    //is this the original owner?
                    if ($thisReport->userID != $insert['userID']) {
                        //change to single
                        $insert['reportType'] = 'Single';
                        $wpdb->insert('wp_gpx_report_writer', $insert);
                        $data = [
                            'success' => true,
                            'refresh' => '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer&id=' . $wpdb->insert_id,
                        ];
                    }
                }
                if (!isset($data)) {
                    $wpdb->update('wp_gpx_report_writer', $insert, ['id' => $_REQUEST['editid']]);
                    $data = [
                        'success' => true,
                        'refresh' => '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer&id=' . $_REQUEST['editid'],
                    ];
                }
            }
        }

        if (!isset($data)) {
            $wpdb->insert('wp_gpx_report_writer', $insert);
            $data = [
                'success' => true,
            ];
            if (empty($_REQUEST['name'])) {
                $data['refresh'] = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer&id=' . $wpdb->insert_id;
            } else {
                $data['link'] = '<li><a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer&id=' . $wpdb->insert_id . '" target="_blank">' . $_POST['name'] . '</a>&nbsp;&nbsp;<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer&editid=' . $wpdb->insert_id . '"><i class="fa fa-pencil"></i></a></li>';
                $data['refresh'] = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer&id=' . $wpdb->insert_id;
            }
        }
    }

    wp_send_json($data);
}

add_action('wp_ajax_gpx_report_write', 'gpx_report_write');

/**
 *
 *
 *
 *
 */
function get_gpx_resorttaxes()
{
    global $wpdb;

    $sql = "SELECT ID,TaxAuthority,City,State,Country FROM wp_gpxTaxes";
    $taxes = $wpdb->get_results($sql);
    $data = array_map(fn($tax) => [
        'edit' => '<a href="'.gpx_admin_route('resorts_taxesedit', ['id' => $tax->ID]) . '"><i class="fa fa-pencil" aria-hidden="true"></i></a>',
        'authority' => $tax->TaxAuthority,
        'city' => $tax->City,
        'state' => $tax->State,
        'country' => $tax->Country,
    ], $taxes);

    wp_send_json($data);
}

add_action('wp_ajax_get_gpx_resorttaxes', 'get_gpx_resorttaxes');
add_action('wp_ajax_nopriv_get_gpx_resorttaxes', 'get_gpx_resorttaxes');

/**
 *
 *
 *
 *
 */
function add_gpx_resorttax()
{
    global $wpdb;

    $output = ['error' => true, 'msg' => 'You must submit something'];

    if (!empty($_POST)) {
        $add = [
            'TaxAuthority' => $_POST['TaxAuthority'],
            'City' => $_POST['City'],
            'State' => $_POST['State'],
            'Country' => $_POST['Country'],
        ];
        for ($i = 1; $i <= 3; $i++) {
            if (isset($_POST['TaxPercent' . $i]) && !empty($_POST['TaxPercent'] . $i)) {
                $add['TaxPercent' . $i] = $_POST['TaxPercent' . $i];
            }
            if (isset($_POST['FlatTax' . $i]) && !empty($_POST['FlatTax'] . $i)) {
                $add['FlatTax' . $i] = $_POST['FlatTax' . $i];
            }
        }
        if ($wpdb->insert('wp_gpxTaxes', $add)) {
            $msg = 'Tax Added';
            $insertID = $wpdb->insert_id;
            if (isset($_POST['resortID']) && !empty($_POST['resortID']) && !empty($insertID)) {
                if ($wpdb->update('wp_resorts', ['taxID' => $insertID], ['ResortID' => $_POST['resortID']])) {
                    $msg .= ' and Resort Updated';
                }
            }
            $output = ['success' => true, 'msg' => $msg];
        } else {
            $output['msg'] = 'There was an error adding the tax';
        }
    }

    wp_send_json($output);
}

add_action('wp_ajax_add_gpx_resorttax', 'add_gpx_resorttax');
add_action('wp_ajax_nopriv_add_gpx_resorttax', 'add_gpx_resorttax');


/**
 *
 *
 *
 *
 */
function edit_gpx_resorttax()
{
    global $wpdb;

    $data = ['error' => true, 'msg' => 'There was an error updating'];
    if (!empty($_POST['taxID'])) {
        $update = [
            'TaxAuthority' => $_POST['TaxAuthority'],
            'City' => $_POST['City'],
            'State' => $_POST['State'],
            'Country' => $_POST['Country'],
        ];
        for ($i = 1; $i <= 3; $i++) {
            if (isset($_POST['TaxPercent' . $i]) && !empty($_POST['TaxPercent'] . $i)) {
                $update['TaxPercent' . $i] = $_POST['TaxPercent' . $i];
            }
            if (isset($_POST['FlatTax' . $i]) && !empty($_POST['FlatTax'] . $i)) {
                $update['FlatTax' . $i] = $_POST['FlatTax' . $i];
            }
        }
        $wpdb->update('wp_gpxTaxes', $update, ['ID' => $_POST['taxID']]);
        $data = ['success' => true, 'msg' => 'Tax Updated'];
    }

    wp_send_json($data);
}

add_action('wp_ajax_edit_gpx_resorttax', 'edit_gpx_resorttax');
add_action('wp_ajax_nopriv_edit_gpx_resorttax', 'edit_gpx_resorttax');


/**
 *
 *
 *
 *
 */
function update_gpx_resorttax_id()
{
    global $wpdb;

    $data = ['error' => true, 'msg' => 'There was an error'];
    if (!empty($_POST['resortID'])) {
        if ($wpdb->update('wp_resorts', ['taxID' => $_POST['taxID']], ['ResortID' => $_POST['resortID']])) {
            $data = ['success' => true, 'msg' => 'Resort Tax Updated'];
        } else {
            $data['msg'] = 'Nothing to update';
        }
    }

    wp_send_json($data);
}

add_action('wp_ajax_update_gpx_resorttax_id', 'update_gpx_resorttax_id');
add_action('wp_ajax_nopriv_update_gpx_resorttax_id', 'update_gpx_resorttax_id');


/**
 *
 *
 *
 *
 */
function edit_tax_method()
{
    global $wpdb;

    $data = ['error' => true, 'mgs' => 'Tax Method Not Updated'];
    if (!empty($_POST['ResortID']) && !empty($_POST['taxMethod'])) {
        if ($wpdb->update('wp_resorts', ['taxMethod' => $_POST['taxMethod']], ['ResortID' => $_POST['ResortID']])) {
            $data = ['success' => true, 'msg' => 'Tax Method Updated'];
        }
    }

    wp_send_json($data);
}

add_action('wp_ajax_edit_tax_method', 'edit_tax_method');
add_action('wp_ajax_nopriv_edit_tax_method', 'edit_tax_method');

/**
 *
 *
 *
 *
 */
function add_ai()
{
    global $wpdb;

    $sql = "SELECT id FROM wp_resorts WHERE
        (ResortFacilities = 'All Inclusive')
        OR (JSON_VALID(ResortFacilities) AND JSON_CONTAINS(JSON_EXTRACT(ResortFacilities, '$[*]'), '\"All Inclusive\"', '$') )
        OR (HTMLAlertNotes LIKE '%IMPORTANT: All-Inclusive Information%')
        OR (AlertNote LIKE '%IMPORTANT: This is an All Inclusive (AI) property.%')";
    $props = $wpdb->get_col($sql);
    $placeholders = gpx_db_placeholders($props, '%d');
    $sql = $wpdb->prepare("UPDATE wp_resorts SET ai = 1 WHERE id IN ({$placeholders})", $props);
    $wpdb->query($sql);
}

add_action('wp_ajax_add_ai', 'add_ai');
add_action('wp_ajax_nopriv_add_ai', 'add_ai');
