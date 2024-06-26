<?php

use GPX\Model\UserMeta;
use GPX\DataObject\Resort\AvailabilityCalendarSearch;
use GPX\Model\Resort;
use GPX\Model\Special;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use GPX\Repository\WeekRepository;
use GPX\Repository\OwnerRepository;
use GPX\Repository\IntervalRepository;
use GPX\Repository\TransactionRepository;

class GpxAdmin {

    protected $uri;
    protected $dir;
    public $user;
    public GpxModel $gpx_model;

    public function __construct($uri, $dir) {
        $this->uri = $uri;
        $this->dir = $dir;
        $this->user = wp_get_current_user();
        $this->gpx_model = new GpxModel;
    }

    /**
     * getpage loads the page notice that it calls a separate function below which acts as a "controller"
     */
    public function getpage(string $slug = '', $type = 'admin') {
        $static = [
            'dashboard' => admin_url('admin.php?page=gpx-admin-page'),
            'user_data' => $this->user,
            'dir' => $this->dir,
        ];

        if ($slug === 'dashboard' || $slug === '') {
            echo gpx_render_blade('admin::dashboard', [], true);

            return;
        }

        $page = $this->gpx_model->parse_page($slug);
        $file = '/templates/admin/' . $page['parent'] . '/' . $page['child'] . '.php';
        $id = $_GET['id'] ?? '';
        if (file_exists($this->dir . $file)) {
            $data = $this->{$page['child']}($id);
            $data['active'] = $page['parent'];
            require $this->dir . $file;
        } else {
            $this->notfound();
        }
    }

    public function notfound(string $title = 'Page Not Found', string $message = null) {
        $dashboard = admin_url('admin.php?page=gpx-admin-page');
        $user_data = $this->user;
        $dir = $this->dir;
        $active = 'dashboard';
        gpx_admin_view('404', compact('title', 'message', 'dashboard', 'dir', 'active', 'user_data'), true);
    }

    public function customrequests() {
        return [];
    }

    public function customrequestemail() {
        if (!current_user_can('administrator')) {
            exit;
        }

        if (isset($_POST['email'])) {
            update_option('gpx_cremail', sanitize_email($_POST['email']));
            update_option('gpx_cremailName', sanitize_text_field($_POST['name']));
            update_option('gpx_cremailSubject', sanitize_text_field($_POST['subject']));
            update_option('gpx_cremailTitle', sanitize_text_field($_POST['title']));
            update_option('gpx_cremailButton', sanitize_text_field($_POST['button']));
            update_option('gpx_cremailMessage', $_POST['content']);
        }

        $data = [];

        $data['cremail'] = get_option('gpx_cremail');
        $data['cremailName'] = get_option('gpx_cremailName');
        $data['cremailSubject'] = get_option('gpx_cremailSubject');
        $data['cremailTitle'] = get_option('gpx_cremailTitle', 'Success!');
        $data['cremailButton'] = get_option('gpx_cremailButton', 'Review Your Match');
        $data['cremailMessage'] = get_option('gpx_cremailMessage');

        return $data;
    }

    public function customrequestemailresortmatch() {
        if (!current_user_can('administrator')) {
            exit;
        }

        if (isset($_POST['email'])) {
            update_option('gpx_crresortmatchemail', sanitize_email($_POST['email']));
            update_option('gpx_crresortmatchemailName', sanitize_text_field($_POST['name']));
            update_option('gpx_crresortmatchemailSubject', sanitize_text_field($_POST['subject']));
            update_option('gpx_crresortmatchemailTitle', sanitize_text_field($_POST['title']));
            update_option('gpx_crresortmatchemailButton', sanitize_text_field($_POST['button']));
            update_option('gpx_crresortmatchemailMessage', $_POST['content']);
        }

        $data = [];

        $data['cremail'] = get_option('gpx_crresortmatchemail', 'gpx@gpresorts.com');
        $data['cremailName'] = get_option('gpx_crresortmatchemailName', 'GPX Custom Requests');
        $data['cremailSubject'] = get_option('gpx_crresortmatchemailSubject', 'There is a Match! Confirm your Custom Search Request');
        $data['cremailTitle'] = get_option('gpx_crresortmatchemailTitle', 'Success!');
        $data['cremailButton'] = get_option('gpx_crresortmatchemailButton', 'Review & Confirm Reservation');
        $data['cremailMessage'] = get_option('gpx_crresortmatchemailMessage');

        return $data;
    }

    public function customrequestemailresortmissed() {
        if (!current_user_can('administrator')) {
            exit;
        }

        if (isset($_POST['email'])) {
            update_option('gpx_crresortmissedemail', sanitize_email($_POST['email']));
            update_option('gpx_crresortmissedemailName', sanitize_text_field($_POST['name']));
            update_option('gpx_crresortmissedemailSubject', sanitize_text_field($_POST['subject']));
            update_option('gpx_crresortmissedemailTitle', sanitize_text_field($_POST['title']));
            update_option('gpx_crresortmissedemailButton', sanitize_text_field($_POST['button']));
            update_option('gpx_crresortmissedemailMessage', $_POST['content']);
        }
        $data = [];

        $data['cremail'] = get_option('gpx_crresortmissedemail', 'gpx@gpresorts.com');
        $data['cremailName'] = get_option('gpx_crresortmissedemailName', 'GPX Custom Requests');
        $data['cremailSubject'] = get_option('gpx_crresortmissedemailSubject', 'Your Custom Search Request has been Released');
        $data['cremailTitle'] = get_option('gpx_crresortmissedemailTitle', 'Did You Find Another Vacation?');
        $data['cremailButton'] = get_option('gpx_crresortmissedemailButton', 'Submit a New Request');
        $data['cremailMessage'] = get_option('gpx_crresortmissedemailMessage');

        return $data;
    }

    public function customrequestemailsixtyday() {
        if (!current_user_can('administrator')) {
            exit;
        }
        if (isset($_POST['email'])) {
            update_option('gpx_crsixtydayemail', sanitize_email($_POST['email']));
            update_option('gpx_crsixtydayemailName', sanitize_text_field($_POST['name']));
            update_option('gpx_crsixtydayemailSubject', sanitize_text_field($_POST['subject']));
            update_option('gpx_crsixtydayemailTitle', sanitize_text_field($_POST['title']));
            update_option('gpx_crsixtydayemailButton', sanitize_text_field($_POST['button']));
            update_option('gpx_crsixtydayemailMessage', $_POST['content']);
        }
        $data = [];

        $data['cremail'] = get_option('gpx_crsixtydayemail', 'gpx@gpresorts.com');
        $data['cremailName'] = get_option('gpx_crsixtydayemailName', 'GPX Custom Requests');
        $data['cremailSubject'] = get_option('gpx_crsixtydayemailSubject', 'No Matches for your Custom Search Request');
        $data['cremailTitle'] = get_option('gpx_crsixtydayemailTitle', 'Should We Keep Searching?');
        $data['cremailButton'] = get_option('gpx_crsixtydayemailButton', 'Keep Searching');
        $data['cremailMessage'] = get_option('gpx_crsixtydayemailMessage');

        return $data;
    }

    public function promoedit($id = '') {
        global $wpdb;

        if (isset($_POST['bookingFunnel'])) {
            $post = gpx_return_add_promo($_POST);
            echo '<script>window.location.href = "/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=promos_all";</script>';
            exit;
        }

        $data = ['usage' => '', 'exclusions' => ''];
        $sql = $wpdb->prepare("SELECT * FROM wp_specials WHERE id=%d", $id);
        $data['promo'] = $wpdb->get_row($sql);
        $meta = stripslashes_deep(json_decode($data['promo']->Properties));

        $data['promometa'] = $meta;

        $sql = "SELECT id, Name FROM wp_specials WHERE active=1 ORDER BY Name";
        $data['special_masters'] = $wpdb->get_results($sql);

        if (isset($data['promometa']->usage) && !empty($data['promometa']->usage)) {
            $data['usage'] = $meta->usage;
            switch ($data['promometa']->usage) {
                case 'region':
                    $jsonUsageRegion = json_decode($meta->usage_region);
                    if (json_last_error() !== 0) {
                        $reg = DB::table('wp_' . $meta->usage_regionType)
                                 ->select([
                                     'RegionID',
                                     $meta->usage_regionType == 'daeCountry' ? 'country as name' : 'name',
                                 ])
                                 ->where('id', '=', $meta->usage_region)
                                 ->first();
                        $data['usage_regionName'] = $reg->name;
                        if ($data['usage_regionName'] == 'All') {
                            $sql = $wpdb->prepare("SELECT country FROM wp_gpxCategory a INNER JOIN wp_daeRegion b ON b.countryID=a.id WHERE b.RegionID=%s", $reg->RegionID);
                            $par = $wpdb->get_row($sql);
                            $data['usage_parent'] = $par->country;
                        }
                    }
                    break;

                case 'resort':
                case 'customer':
                    $resorts = $meta->usage_resort ?? [];
                    $data['usage_resortNames'] = DB::table('wp_resorts')->select([
                        'id',
                        'ResortName',
                    ])->whereIn('id', $resorts)->get()->toArray();
                    if (isset($meta->specificCustomer)) {
                        $sc = json_decode($meta->specificCustomer);
                        $data['promometa']->specificCustomer = gpx_return_customers($sc);
                    }
                    break;
            }
        }
        if (isset($data['promometa']->exclusions) && !empty($data['promometa']->exclusions)) {
            $data['exclusions'] = $meta->exclusions;
            switch ($data['promometa']->exclusions) {
                case 'region':
                    break;
                case 'resort':
                case 'customer':
                    if (isset($meta->exclude_resort)) {
                        $resorts = $meta->exclude_resort ?? [];
                        $data['exclude_resortNames'] = DB::table('wp_resorts')->select([
                            'id',
                            'ResortName',
                        ])->whereIn('id', $resorts)->get()->toArray();
                    }
                    break;

                case 'home-resort':
                    $resorts = $meta->exclude_home_resort ?? [];
                    $data['exclude_resortNames'] = DB::table('wp_resorts')->select([
                        'id',
                        'ResortName',
                    ])->whereIn('id', $resorts)->get()->toArray();
                    break;
            }
        }

        return $data;
    }

    public function promoadd() {
        global $wpdb;
        $data = [];

        if (isset($_POST['bookingFunnel'])) {
            $post = gpx_return_add_promo($_POST);
            echo '<script>window.location.href = "/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=promos_all";</script>';
            exit;
        }

        $sql = "SELECT id, Name FROM wp_specials WHERE active=1 ORDER BY Name";
        $data['special_masters'] = $wpdb->get_results($sql);

        return $data;
    }

    public function promoautocoupons() {
        return [];
    }

    public function promodeccoupons() {
        return [];
    }

    public function promodeccouponsadd($post = []) {
        global $wpdb;

        $data = [];
        if (empty($post)) {
            $post = $_POST;
        }

        $occ = [
            'Name' => 'name',
            'Slug' => 'couponcode',
            'Active' => 'active',
            'singleuse' => 'singleuse',
            'expirationDate' => 'expirationDate',
            'comments' => 'comments',
        ];
        $oca = [
            'amount' => 'amount',
        ];
        $oco = [
            'owners' => 'ownerID',
        ];
        $allvars = array_merge($occ, $oca, $oco);
        foreach ($allvars as $key => $val) {
            $data['vars'][$key] = '';
        }
        if (isset($post['Name'])) {
            if (empty($post['expirationDate'])) {
                $post['expirationDate'] = date('Y-m-d', strtotime("+1 year"));
            }
            foreach ($allvars as $key => $val) {
                if ($post[$key] != '0' && empty($post[$key])) {
                    $error[$key] = true;
                }
            }
            if (!isset($error)) {
                foreach ($occ as $key => $val) {
                    $coupon[$val] = $post[$key];
                }
                if (empty($coupon['expirationDate'])) {
                    $coupon['expirationDate'] = date('Y-m-d', strtotime('+10 year'));
                }
                if (!empty($post['created_date'])) {
                    $coupon['created_date'] = date('Y-m-d', strtotime($post['created_date']));
                }

                $wpdb->insert('wp_gpxOwnerCreditCoupon', $coupon);

                $last_id = $wpdb->insert_id;
                $data['coupon'] = $last_id;
                if (isset($last_id)) {
                    //insert into the wp_gpxOwnerCreditCoupon_activity table
                    foreach ($oca as $key => $val) {
                        $activity[$val] = $post[$key];
                    }
                    if (!isset($error)) {
                        $activity['couponID'] = $last_id;
                        $activity['activity'] = 'created';
                        $activity['activity_comments'] = date('m/d/Y H:i') . ': ' . $post['comments'];
                        $activity['userID'] = get_current_user_id();
                    }
                    $wpdb->insert('wp_gpxOwnerCreditCoupon_activity', $activity);

                    //insert into the wp_gpxOwnerCreditCoupon_owner table
                    foreach ($post['owners'] as $owner) {
                        $insertOwner = [
                            'couponID' => $last_id,
                            'ownerID' => $owner,
                        ];
                        $wpdb->insert('wp_gpxOwnerCreditCoupon_owner', $insertOwner);
                    }
                }
            } else {
                $html = '';
                foreach ($allvars as $key => $val) {
                    if ($key == 'owners') {
                        foreach ($post['owners'] as $owner) {
                            $html .= gpx_return_findowner($owner, 'option', 'user_id');
                        }
                        $data['vars'][$key] = $html;
                    } else {
                        $data['vars'][$key] = $post[$key];
                    }
                }
            }
        }

        return $data;
    }

    public function promodeccouponsedit($id = '') {
        /*
         * @todo: create the code to display details --
         * add activity form to bottom of activity
         *
         */
        global $wpdb;

        $data = [];

        $occ = [
            'Name' => 'name',
            'Slug' => 'couponcode',
            'Active' => 'active',
            'singleuse' => 'singleuse',
            'expirationDate' => 'expirationDate',
            'comments' => 'comments',
        ];
        $oca = [
            'amount' => 'amount',
        ];
        $oco = [
            'owners' => 'ownerID',
        ];
        $allvars = array_merge($occ, $oco);
        foreach ($allvars as $key => $val) {
            $data['vars'][$key] = '';
        }
        if (isset($_POST['Name'])) {
            foreach ($allvars as $key => $val) {
                if ($_POST[$key] != '0' && empty($_POST[$key])) {
                    $error[$key] = true;
                }
            }
            if (!isset($error)) {
                foreach ($occ as $key => $val) {
                    if ($key == 'comments') {
                        $sql = $wpdb->prepare("SELECT comments FROM wp_gpxOwnerCreditCoupon WHERE id=%d", $id);
                        $newComment = $_POST[$key];
                        $_POST[$key] = $wpdb->get_var($sql);

                        $_POST[$key] .= ' ' . date('m/d/Y H:i') . ': ' . $newComment;
                    }
                    $coupon[$val] = $_POST[$key];
                }
                $coupon['expirationDate'] = date('Y-m-d', strtotime($_POST['expirationDate']));
                $wpdb->update('wp_gpxOwnerCreditCoupon', $coupon, ['id' => $id]);

                //remove all owners becase we will add them back in next
                $wpdb->delete('wp_gpxOwnerCreditCoupon_owner', ['couponID' => $id]);

                //insert into the wp_gpxOwnerCreditCoupon_owner table
                foreach ($_POST['owners'] as $owner) {
                    $insertOwner = [
                        'couponID' => $id,
                        'ownerID' => $owner,
                    ];
                    $wpdb->insert('wp_gpxOwnerCreditCoupon_owner', $insertOwner);
                }
            } else {
                $html = '';
                foreach ($allvars as $key => $val) {
                    if ($key == 'owners') {
                        foreach ($_POST['owners'] as $owner) {
                            $html .= gpx_return_findowner($owner, 'option', 'user_id');
                        }
                        $data['vars'][$key] = $html;
                    } else {
                        $data['vars'][$key] = $_POST[$key];
                    }
                }
            }
        }
        if (isset($_POST['newActivity'])) {
            $newActivity = [
                'couponID' => $id,
                'activity' => 'adjustment',
                'amount' => $_POST['newActivity'],
                'userID' => get_current_user_id(),
                'activity_comments' => date('m/d/Y H:i') . ': ' . $_POST['newActivityComment'],
            ];
            $wpdb->insert('wp_gpxOwnerCreditCoupon_activity', $newActivity);
        }
        //if $data['vars']['Name'] is previously set when the form is invalid.  When set, don't pull the results from the database.t
        if (!isset($error)) {
            //get the coupon
            $sql = $wpdb->prepare("SELECT *, a.id as cid, b.id as oid, c.id as aid FROM wp_gpxOwnerCreditCoupon a
                    INNER JOIN wp_gpxOwnerCreditCoupon_owner b ON b.couponID=a.id
                    INNER JOIN wp_gpxOwnerCreditCoupon_activity c ON c.couponID=a.id
                    WHERE a.id=%d", $id);
            $coupons = $wpdb->get_results($sql);

            foreach ($coupons as $coupon) {
                $distinctCoupon = $coupon;
                $distinctOwner[$coupon->oid] = $coupon;
                $distinctActivity[$coupon->aid] = $coupon;
            }
            //get the balance and activity for data
            $amount = [];
            $redeemed = [];
            foreach ($distinctActivity as $activity) {
                if ($activity->activity == 'transaction') {
                    $redeemed[] = $activity->amount;
                } else {
                    $amount[] = $activity->amount;
                }
            }
            if (($distinctCoupon->single_use ?? false) == 1 && array_sum($redeemed) > 0) {
                $balance = 0;
            } else {
                $balance = array_sum($amount) - array_sum($redeemed);
            }
            $data['vars']['amount'] = $balance;
            $data['activity'] = $distinctActivity;

            // gneral coupon info for data
            foreach ($occ as $key => $val) {
                $data['vars'][$key] = $distinctCoupon->$val;
            }
            // owners for data
            $html = '';
            foreach ($distinctOwner as $do) {
                $html .= gpx_return_findowner($do->ownerID, 'option', 'user_id');
            }
            $data['vars']['owners'] = $html;

        }

        return $data;
    }

    public function regionadd() {
        global $wpdb;
        $data = [];

        $sql = "SELECT country, CountryID FROM wp_gpxCategory WHERE newCountryID > 0 ORDER BY CountryID";
        $data['countries'] = $wpdb->get_results($sql);

        return $data;
    }

    public function regionassignlist() {
        return [];
    }

    public function regionassign($id = '') {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT ResortName, gpxRegionID FROM wp_resorts WHERE id=%d", $id);
        $resort = $wpdb->get_row($sql);
        $data = gpx_return_region($resort->gpxRegionID);

        $data['resort'] = $resort;
        $sql = "SELECT country, CountryID FROM wp_gpxCategory";
        $data['countries'] = $wpdb->get_results($sql);
        $data['selected'] = $resort->gpxRegionID;

        return $data;
    }

    public function resortedit($id = '') {
        $data = [];

        $data['resort'] = gpx_return_resort($id);

        return $data;
    }

    public function resorttaxes() {
        return [];
    }

    public function resorttaxesedit($id = '') {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT * FROM wp_gpxTaxes WHERE id=%s", $id);
        $tax = $wpdb->get_row($sql);

        return [
            'tax' => $tax,
        ];
    }

    public function room() {
        return [];
    }

    public function roomadd() {
        global $wpdb;
        $resort = "SELECT id, ResortName FROM `wp_resorts` WHERE `active` = 1 ORDER BY ResortName";
        $resorts = $wpdb->get_results($resort);
        $data = [];
        $data['resort'] = $resorts;
        //SELECT record_id,name FROM `wp_partner`
        $partner = "SELECT record_id,name FROM `wp_partner`";
        $part = $wpdb->get_results($partner);

        $data['partner'] = $part;


        return $data;
    }

    /**
     * This is not linked to anywhere, is it used?
     * @deprecated
     */
    public function roomimport() {
        return [];
    }

    /**
     * If import is not used this isn't either
     * @deprecated
     */
    public function roomerror() {
        return [];
    }

    /**
     * This is not linked to anywhere, is it used?
     * @deprecated
     */
    public function unitTypeadd() {
        global $wpdb;
        $sql = "SELECT id , ResortName FROM `wp_resorts`";
        $result = $wpdb->get_results($sql);
        $data = [];
        $data['resorts'] = $result;

        return $data;
    }

    public function tradepartners() {
        return [];
    }

    public function tradepartneradd() {
        return [];
    }

    public function tradepartneredit($id = '') {
        global $wpdb;

        $data = [];

        if (isset($_REQUEST['email'])) {

            //add the details to the wp_partners table
            $update = [
                'name' => $_REQUEST['name'],
                'email' => $_REQUEST['email'],
                'phone' => $_REQUEST['phone'],
                'address' => $_REQUEST['address'],
                'sf_account_id' => $_REQUEST['sf_account_id'],
            ];
            $wpdb->update('wp_partner', $update, ['record_id' => $id]);
        }

        $sql = $wpdb->prepare("SELECT * FROM wp_partner WHERE record_id=%d", $id);
        $data['tp'] = $wpdb->get_row($sql);

        return $data;
    }

    public function tradepartnerinventory(): array {
        return [];
    }

    public function tradepartnerview(): array {
        return [];
    }

    public function transactionadd(): array {
        return [];
    }

    public function transactionholds(): array {
        return [];
    }

    public function transactionimport() {
        global $wpdb;

        $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);

        $data = [];
        $where = [];

        if (isset($_POST['weekId']) && check_admin_referer('gpx_admin', 'gpx_import_transaction')) {
            $required = [
                'weekId' => "Week ID",
                'ownerID' => "Owner ID",
            ];

            foreach ($required as $req => $val) {
                if (empty($_POST[$req])) {
                    $data['msg']['type'] = 'error';
                    $data['msg']['text'] = $val . " is required!";
                } else {
                    $where[$req] = $wpdb->prepare(gpx_esc_table($req) . " = %s", $_POST[$req]);
                }
            }

            if (!isset($data['msg'])) {
                $vars = $required;
                $vars['resortID'] = 'Resort ID';
                $vars['depositID'] = 'Deposit ID';
            }

            foreach ($vars as $key => $var) {
                $data[$key] = $_POST[$key];
            }
            //pull from each file

            $tables = [
                'transactions_import',
                'transactions_import_two',
                'transactions_import_owner',
            ];

            foreach ($tables as $table) {
                $sql = "SELECT * FROM " . $table . " WHERE " . implode(" AND ", $where) . "";
                $row = $wpdb->get_results($sql);
                if (!empty($row)) {
                    break;
                }
            }

            if (empty($row)) {
                $table = 'wp_gpxTransactions';
                if ($_POST['overwrite'] == 'Yes') {
                    unset($where['ownerID']);
                } else {
                    $where['ownerID'] = $wpdb->prepare('userID = %s', $_POST['ownerID']);
                }

                $sql = "SELECT * FROM wp_gpxTransactions WHERE " . implode(" AND ", $where) . " ORDER BY id DESC LIMIT 1";
                $row = $wpdb->get_row($sql);

                if (!empty($row)) {
                    $rowdata = json_decode($row->data);
                    $row = (object) array_merge((array) $row, (array) $rowdata);
                } else {
                    $data['msg'] = [
                        'type' => 'error',
                        'text' => 'Transaction not found!',
                    ];
                }
            }

            if (!empty($data['msg'])) {
                return $data;
            }

            if ($row->GuestName == '#N/A') {

                $data['msg'] = [
                    'type' => 'error',
                    'text' => 'Guest Name Error!',
                ];
            }

            $resortKeyOne = [
                'Butterfield Park - VI' => '2440',
                'Grand Palladium White Sand - AI' => '46895',
                'Grand Sirenis Riviera Maya Resort - AI' => '46896',
                'High Point World Resort - RHC' => '1549',
                'Los Abrigados Resort & Spa' => '2467',
                'Makai Club Cottages' => '1786',
                'Palm Canyon Resort & Spa' => '1397',
                'Sunset Marina Resort & Yacht Club - AI' => '46897',
                'Azul Beach Resort Negril by Karisma - AI' => '46898',
                'Bali Villas & Sports Club - Rentals Only' => '46899',
                'Blue Whale' => '46900',
                'Bluegreen Club 36' => '46901',
                'BreakFree Alexandra Beach' => '46902',
                'Classic @ Alpha Sovereign Hotel' => '46903',
                'Club Regina Los Cabos' => '46904',
                'Eagles Nest Resort - VI' => '1836',
                'El Dorado Casitas Royale by Karisma' => '46905',
                'El Dorado Casitas Royale by Karisma, a Gourmet AIl Inclusive' => '46905',
                'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive' => '46905',
                'El Dorado Maroma by Karisma, a Gourmet AI' => '46906',
                'El Dorado Royale by Karisma a Gourmet AI' => '46907',
                'El Dorado Royale by Karisma, a Gourmet AI' => '46907',
                'Fiesta Ameri. Vac Club At Cabo Del Sol' => '46908',
                'Fort Brown Condo Shares' => '46909',
                'Four Seasons Residence Club Scottsdale@Troon North' => '2457',
                'Generations Riviera Maya by Karisma a Gourmet AI' => '46910',
                'GPX Cruise Exchange' => 'SKIP',
                'Grand Palladium Jamaica Resort & Spa - AI' => '46911',
                'Grand Palladium Vallarta Resort & Spa - AI' => '46912',
                'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive' => '46913',
                'High Sierra Condominiums' => '46914',
                'Kiltannon Home Farm' => '46915',
                'Knocktopher Abbey' => '46916',
                'Knocktopher Abbey (Shadowed)' => '46916',
                'Laguna Suites Golf and Spa - AI' => '46917',
                'Maison St. Charles - Rentals Only' => '46918',
                'Makai Club Resort' => '1787',
                'Marina Del Rey Beach Club - No Longer Accepting' => '46919',
                'Mantra Aqueous on Port' => '46920',
                'Maui Sunset - Rentals Only' => '1758',
                'Mayan Palace Mazatlan' => '3652',
                'Ocean Gate Resort' => '46921',
                'Ocean Spa Hotel - AI' => '46922',
                'Paradise' => '46923',
                'Park Royal Homestay Club Cala' => '338',
                'Park Royal Los Cabos - RHC' => '46924',
                'Peacock Suites Resort' => '46925',
                'Pounamu Apartments - Rental' => '46926',
                'Presidential Suites by LHVC - Punta Cana NON - AI' => '46927',
                'RHC - Park Royal - Los Tules' => '46928',
                'Royal Regency Paris (Shadowed)' => '479',
                'Royal Sunset - AI' => '46929',
                'Secrets Puerto Los Cabos Golf & Spa Resort - AI' => '46930',
                'Secrets Wild Orchid Montego Bay - AI' => '46931',
                'Solare Bahia Mar - Rentals Only' => '46932',
                'Tahoe Trail - VI' => '40',
                'The RePlay Residence' => '46933',
                'The Tropical at LHVC - AI' => '46934',
                'Vacation Village at Williamsburg' => '2432',
                'Wolf Run Manor At Treasure Lake' => '46935',
                'Wyndham Grand Desert - 3 Nights' => '46936',
                'Wyndham Royal Garden at Waikiki - Rental Only' => '1716',
            ];

            $resortKeyTwo = [
                'Royal Aloha Chandler - Butterfield Park' => '2440',
                'Grand Palladium White Sand - AI' => '46895',
                'Grand Sirenis Riviera Maya Resort - AI' => '46896',
                'High Point World Resort' => '1549',
                'Los Abrigados Resort and Spa' => '2467',
                'Makai Club Resort Cottages' => '1786',
                'Palm Canyon Resort and Spa' => '1397',
                'Sunset Marina Resort & Yacht Club - AI' => '46897',
                'Azul Beach Resort Negril by Karisma - AI' => '46898',
                'Bali Villas & Sports Club - Rentals Only' => '46899',
                'Blue Whale' => '46900',
                'Bluegreen Club 36' => '46901',
                'BreakFree Alexandra Beach' => '46902',
                'Classic @ Alpha Sovereign Hotel' => '46903',
                'Club Regina Los Cabos' => '46904',
                'Royal Aloha Branson - Eagles Nest Resort' => '1836',
                'El Dorado Casitas Royale by Karisma, a Gourmet AIl Inclusive' => '46905',
                'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive' => '46905',
                'El Dorado Maroma by Karisma a Gourmet AI' => '46906',
                'El Dorado Royale by Karisma a Gourmet AI' => '46907',
                'Fiesta Ameri. Vac Club At Cabo Del Sol' => '46908',
                'Fort Brown Condo Shares' => '46909',
                'Four Seasons Residence Club Scottsdale at Troon North' => '2457',
                'Generations Riviera Maya by Karisma a Gourmet AI' => '46910',
                'SKIP' => 'SKIP',
                'Grand Palladium Jamaica Resort & Spa - AI' => '46911',
                'Grand Palladium Vallarta Resort & Spa - AI' => '46912',
                'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive' => '46913',
                'High Sierra Condominiums' => '46914',
                'Kiltannon Home Farm' => '46915',
                'Knocktopher Abbey' => '46916',
                'Laguna Suites Golf and Spa - AI' => '46917',
                'Maison St. Charles - Rentals Only' => '46918',
                'Makai Club Resort Condos' => '1787',
                'Marina Del Rey Beach Club - No Longer Accepting' => '46919',
                'Mantra Aqueous on Port' => '46920',
                'Maui Sunset' => '1758',
                'Mayan Palace Mazatlan by Grupo Vidanta' => '3652',
                'Ocean Gate Resort' => '46921',
                'Ocean Spa Hotel - AI' => '46922',
                'Paradise' => '46923',
                'Royal Holiday - Park Royal Club Cala' => '338',
                'Park Royal Los Cabos - RHC' => '46924',
                'Peacock Suites Resort' => '46925',
                'Pounamu Apartments - Rental' => '46926',
                'Presidential Suites by LHVC - Punta Cana NON - AI' => '46927',
                'RHC - Park Royal - Los Tules' => '46928',
                'Royal Regency By Diamond Resorts' => '479',
                'Royal Sunset - AI' => '46929',
                'Secrets Puerto Los Cabos Golf & Spa Resort - AI' => '46930',
                'Secrets Wild Orchid Montego Bay - AI' => '46931',
                'Solare Bahia Mar - Rentals Only' => '46932',
                'Royal Aloha Tahoe' => '40',
                'The RePlay Residence' => '46933',
                'The Tropical at LHVC - AI' => '46934',
                'Williamsburg Plantation Resort' => '2432',
                'Wolf Run Manor At Treasure Lake' => '46935',
                'Wyndham Grand Desert - 3 Nights' => '46936',
                'Royal Garden at Waikiki Resort' => '1716',
            ];
            $resortMissing = '';
            if (isset($_POST['resortID'])) {
                $resortMissing = $_POST['resortID'];
            } else {
                if (array_key_exists($row->Resort_Name, $resortKeyOne)) {
                    $resortMissing = $resortKeyOne[$row->Resort_Name];
                }
                if (array_key_exists($row->Resort_Name, $resortKeyTwo)) {
                    $resortMissing = $resortKeyTwo[$row->Resort_Name];
                }
            }
            if (!empty($resortMissing)) {
                $sql = $wpdb->prepare("SELECT id, resortID, ResortName FROM wp_resorts WHERE id=%s", $resortMissing);
                $resort = $wpdb->get_row($sql);
                $resortName = $resort->ResortName;
            } else {
                $resortName = $row->Resort_Name;
                $resortName = str_replace("- VI", "", $resortName);
                $resortName = trim($resortName);
                $sql = $wpdb->prepare("SELECT id, resortID FROM wp_resorts WHERE ResortName=%s", $resortName);
                $resort = $wpdb->get_row($sql);
            }

            if (empty($resort)) {
                $sql = $wpdb->prepare("SELECT missing_resort_id FROM import_credit_future_stay WHERE resort_name=%s", $resortName);
                $resort_ID = $wpdb->get_var($sql);

                $sql = $wpdb->prepare("SELECT id, resortID, ResortName FROM wp_resorts WHERE id=%s", $resort_ID);
                $resort = $wpdb->get_row($sql);
                $resortID = $resort->resortID;
                $resortName = $resort->ResortName;


            } else {
                $resortID = $resort->id;
                $daeResortID = $resort->resortID;
            }

            if (empty($resort)) {

                $data['msg'] = [
                    'type' => 'error',
                    'text' => 'Resort not found!',
                ];
            }

            $sql = $wpdb->prepare("SELECT user_id FROM wp_GPR_Owner_ID__c WHERE user_id=%s", $row->MemberNumber);
            $user = $wpdb->get_var($sql);

            if (empty($user)) {
                //let's try to import this owner
                $user = function_GPX_Owner($row->MemberNumber);

                if (empty($user)) {

                    $data['msg'] = [
                        'type' => 'error',
                        'text' => 'Owner not found!',
                    ];
                }
            } else {
                $userID = $user;

                $sql = $wpdb->prepare("SELECT name FROM wp_partner WHERE user_id=%s", $userID);
                $memberName = $wpdb->get_var($sql);

                if (empty($memberName)) {
                    $fn = get_user_meta($userID, 'first_name', true);

                    if (empty($fn)) {
                        $fn = get_user_meta($userID, 'FirstName1', true);
                    }
                    $ln = get_user_meta($userID, 'last_name', true);
                    if (empty($ln)) {
                        $ln = get_user_meta($userID, 'LastName1', true);
                    }
                    if (!empty($fn) || !empty($ln)) {
                        $memberName = $fn . " " . $ln;
                    } else {

                        $data['msg'] = [
                            'type' => 'error',
                            'text' => 'Owner not found!',
                        ];
                    }
                }
            }

            $unitType = $row->Unit_Type;
            $sql = $wpdb->prepare("SELECT record_id FROM wp_unit_type WHERE resort_id=%s AND name=%s", [
                $resortID,
                $unitType,
            ]);
            $unitID = $wpdb->get_var($sql);

            $bs = explode("/", $unitType);
            $beds = $bs[0];
            $beds = str_replace("b", "", $beds);
            if ($beds == 'St') {
                $beds = 'STD';
            }
            $sleeps = $bs[1];
            if (empty($unitID)) {
                $insert = [
                    'name' => $unitType,
                    'create_date' => date('Y-m-d'),
                    'number_of_bedrooms' => $beds,
                    'sleeps_total' => $sleeps,
                    'resort_id' => $resortID,
                ];
                $wpdb->insert('wp_unit_type', $insert);
                $unitID = $wpdb->insert_id;
            }

            if (isset($_POST['depositID'])) {
                $sql = $wpdb->prepare("SELECT id FROM wp_credit WHERE id=%s", $_POST['depositID']);
                $deposit = $wpdb->get_var($sql);

                if (empty($deposit)) {
                    $sql = $wpdb->prepare("SELECT a.id FROM wp_credit a
                            INNER JOIN import_credit_future_stay b ON
                            b.Deposit_year=a.deposit_year AND
                            b.resort_name=a.resort_name AND
                            b.unit_type=a.unit_type AND
                            b.Member_Name=a.owner_id
                            WHERE b.ID=%s", $_POST['depositID']);
                    $deposit = $wpdb->get_var($sql);
                }
                if (empty($deposit)) {
                    $data['msg'] = [
                        'type' => 'error',
                        'text' => 'Deposit not found!',
                    ];
                }
            } elseif ($row->WeekTransactionType == 'Exchange') {
                $sql = $wpdb->prepare("SELECT a.id FROM wp_credit a
                            INNER JOIN import_credit_future_stay b ON
                            b.Deposit_year=a.deposit_year AND
                            b.resort_name=a.resort_name AND
                            b.unit_type=a.unit_type AND
                            b.Member_Name=a.owner_id
                            WHERE b.ID=%s", $_POST['depositID']);
                $deposit = $wpdb->get_var($sql);
                if (empty($deposit)) {
                    $data['msg'] = [
                        'type' => 'error',
                        'text' => 'Deposit not found!',
                    ];
                }
            }

            if (!empty($data['msg'])) {
                return $data;
            }

            if (!isset($row->Check_In_Date) || empty($row->Check_In_Date)) {

                $row->Check_In_Date = $row->check_in_date;

            }

            $wp_room = [
                'record_id' => $row->weekId,
                'active_specific_date' => date("Y-m-d 00:00:00", strtotime($row->Rental_Opening_Date)),
                'check_in_date' => date('Y-m-d 00:00:00', strtotime($row->Check_In_Date)),
                'check_out_date' => date('Y-m-d 00:00:00', strtotime($row->Check_In_Date . ' +7 days')),
                'resort' => $resortID,
                'unit_type' => $unitID,
                'source_num' => '1',
                'source_partner_id' => '0',
                'sourced_by_partner_on' => '',
                'resort_confirmation_number' => '',
                'active' => '0',
                'availability' => '1',
                'available_to_partner_id' => '0',
                'type' => '1',
                'active_rental_push_date' => date('Y-m-d', strtotime($row->Rental_Opening_Date)),
                'price' => '0',
                'points' => null,
                'note' => '',
                'given_to_partner_id' => null,
                'import_id' => '0',
                'active_type' => '0',
                'active_week_month' => '0',
                'create_by' => '5',
                'archived' => '0',
            ];

            $sql = $wpdb->prepare("SELECT record_id FROM wp_room WHERE record_id=%s", $row->weekId);
            $week = $wpdb->get_row($sql);
            if (!empty($week)) {
                $wpdb->update('wp_room', $wp_room, ['record_id' => $week]);
            } else {
                $wpdb->insert('wp_room', $wp_room);
            }

            $cpo = "TAKEN";
            if ($row->CPO == 'No') {
                $cpo = "NOT TAKEN";
            }

            $data = [
                "MemberNumber" => $row->MemberNumber,
                "MemberName" => $memberName,
                "GuestName" => $row->GuestName,
                "Adults" => $row->Adults,
                "Children" => $row->Children,
                "UpgradeFee" => $row->actupgradeFee,
                "CPO" => $cpo,
                "CPOFee" => $row->actcpoFee,
                "Paid" => $row->Paid,
                "Balance" => "0",
                "ResortID" => $daeResortID,
                "ResortName" => $row->Resort_Name,
                "room_type" => $row->Unit_Type,
                "WeekType" => $row->WeekTransactionType,
                "sleeps" => $sleeps,
                "bedrooms" => $beds,
                "Size" => $row->Unit_Type,
                "noNights" => "7",
                "checkIn" => date('Y-m-d', strtotime($row->Check_In_Date)),
                "processedBy" => 5,
                'actWeekPrice' => $row->actWeekPrice,
                'actcpoFee' => $row->actcpoFee,
                'actextensionFee' => $row->actextensionFee,
                'actguestFee' => $row->actguestFee,
                'actupgradeFee' => $row->actupgradeFee,
                'acttax' => $row->acttax,
                'actlatedeposit' => $row->actlatedeposit,
            ];

            $wp_gpxTransactions = [
                'transactionType' => 'booking',
                'cartID' => $userID . '-' . $row->weekId,
                'sessionID' => '',
                'userID' => $userID,
                'resortID' => $daeResortID,
                'weekId' => $row->weekId,
                'check_in_date' => date('Y-m-d', strtotime($row->Check_In_Date)),
                'datetime' => date('Y-m-d', strtotime($row->transaction_date)),
                'depositID' => null,
                'paymentGatewayID' => '',
                'transactionRequestId' => null,
                'transactionData' => '',
                'sfid' => '0',
                'sfData' => '',
                'data' => json_encode($data),
            ];

            if (isset($deposit)) {
                $data['creditweekid'] = $deposit;
                $wp_gpxTransactions['depositID'] = $deposit;
                $wp_gpxTransactions['data'] = json_encode($data);
            }

            $transactionID = '';
            $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s AND userID=%s", [
                $row->weekId,
                $userID,
            ]);
            $et = $wpdb->get_var($sql);
            if (!empty($et)) {
                $wpdb->update('wp_gpxTransactions', $wp_gpxTransactions, ['id' => $et]);
                $transactionID = $et;

            } else {
                $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s", $row->weekId);
                $enut = $wpdb->get_var($sql);
                if (empty($enut)) {
                    $wpdb->insert('wp_gpxTransactions', $wp_gpxTransactions);
                    $transactionID = $wpdb->insert_id;
                } else {
                    if ($_POST['overwrite'] == 'Yes') {
                        $wpdb->update('wp_gpxTransactions', $wp_gpxTransactions, ['id' => $enut]);
                    } else {
                        $data['msg'] = [
                            'type' => 'error',
                            'text' => 'Transaction exists with a different owner!',
                        ];
                    }
                }
            }
            if (!empty($transactionID)) {
                TransactionRepository::instance()->send_to_salesforce((int)$transactionID);
            }
            $data = Arr::except($data, $vars);
            $data['msg'] = [
                'type' => 'success',
                'text' => 'Transaction updated!',
            ];
        }

        return $data;
    }

    public function users(): array {
        return [];
    }

    public function useredit(string $id = '') {
        global $wpdb;
        if (isset($_POST['Email'])) {
            $redirect = $_POST['returnurl'];
            unset($_POST['returnurl']);

            $gpxOwner = [
                'SPI_Email__c' => $_POST['Email'],
            ];
            $wpdb->update('wp_GPR_Owner_ID__c', $gpxOwner, ['user_id' => $id]);

            $wptodae = [
                'first_name' => 'FirstName1',
                'last_name' => 'LastName1',
                'user_email' => 'Email',
                'Mobile1' => 'Mobile',
            ];

            foreach ($wptodae as $wdKey => $wdValue) {
                $_POST[$wdKey] = str_replace(" &", ",", $_POST[$wdKey]);
            }

            foreach ($_POST as $key => $value) {
                if ($key == 'OwnershipWeekType') {
                    $value = json_encode($_POST[$key]);
                }
                update_user_meta($id, $key, $value);
            }

            echo '<script type="text/javascript">window.location.href="' . $redirect . '"</script>';

        }
        if (!$id) {
            $this->notfound('User Not Found', 'The user you requested could not be found.');
        }
        $user = get_userdata($id);
        $data = ['user' => $user];

        $sql = $wpdb->prepare("SELECT *  FROM `wp_GPR_Owner_ID__c` WHERE user_id IN
(SELECT userID FROM wp_owner_interval a WHERE a.Contract_Status__c = 'Active' AND
 a.ownerID IN
                    (SELECT DISTINCT gpr_oid
                        FROM wp_mapuser2oid
                        WHERE gpx_user_id IN
                            (SELECT DISTINCT gpx_user_id
                            FROM wp_mapuser2oid
                            WHERE gpx_user_id=%s))) AND user_id=%s", [$id, $id]);
        $data['umap'] = $wpdb->get_row($sql, ARRAY_A);

        return $data;
    }

    public function usersplit() {
        global $wpdb;

        $data = [];

        if (!empty($_POST['owner_id'])) {
            if (!empty($_POST['vestID'])) {
                $originalOwnerID = $_POST['owner_id'];
                $newVestID = $_POST['vestID'];

                $data['ownerIDs'] = $wpdb->update('wp_GPR_Owner_ID__c', ['user_id' => $newVestID], ['Name' => $originalOwnerID]);
                $data['mapIDs'] = $wpdb->update('wp_mapuser2oid', ['gpx_user_id' => $newVestID], ['gpr_oid' => $originalOwnerID]);
                $data['intervalIDs'] = $wpdb->update('wp_owner_interval', ['userID' => $newVestID], ['ownerID' => $originalOwnerID]);

                $data['msgType'] = 'success';
            } elseif (!empty($_POST['email'])) {
                $originalOwnerID = $_POST['owner_id'];

                $user = get_user_by('email', $_POST['email']);

                if (!empty($user)) {
                    $userId = $user->ID;

                    update_user_meta($userId, 'GPX_Member_VEST__c', '');
                }

                $ownerAdd = function_GPX_Owner($_POST['owner_id'], true);

                $data['msgType'] = 'success';
            } else {
                $originalOwnerID = $_POST['owner_id'];

                $user = Arr::first(
                    get_users(
                        [
                            'meta_key' => 'owner_id',
                            'meta_value' => $originalOwnerID,
                            'number' => 1,
                            'count_total' => false,
                        ]
                    )
                );

                update_user_meta($user->ID, 'GPX_Member_VEST__c', '');

                $wpdb->delete('wp_GPR_Owner_ID__c', ['Name' => $originalOwnerID]);
                $wpdb->delete('wp_mapuser2oid', ['gpr_oid' => $originalOwnerID]);
                $wpdb->delete('wp_owner_interval', ['ownerID' => $originalOwnerID]);

                $ownerAdd = function_GPX_Owner($_POST['owner_id'], true);

                $data['msgType'] = 'success';
            }
        } else {
            $data['vestID'] = $_POST['vestID'];
            $data['msgType'] = 'error';
            $data['msg'] = 'Owner ID is required.';
        }

        return $data;
    }

    /**
     * @TODO
     * Broken on both staging and live server
     * Should this be fixed or removed?
     * @deprecated
     */
    public function reportsearches(): array {
        return [];
    }

    public function reportretarget() {
        return [];
    }

    public function reportcustomrequest() {
        $data = [];

        global $wpdb;

        $sql = "SELECT count(*) as total FROM wp_gpxCustomRequest";
        $all = $wpdb->get_row($sql);
        $return['alltotal'] = $all->total;

        $sql = "SELECT count(*) as total FROM wp_gpxCustomRequest WHERE matchedOnSubmission='1'";
        $activeMatched = $wpdb->get_row($sql);
        $return['activeMatchedtotal'] = $activeMatched->total;

        $sql = "SELECT count(*) as total FROM wp_gpxCustomRequest WHERE matchConverted <> '0'";
        $convertedMatched = $wpdb->get_row($sql);
        $return['convertedMatchedtotal'] = $convertedMatched->total;

        $data['totals'] = $return;

        return $data;
    }

    public function reportavailability() {
        return [];
    }

    public function reportemailmembersearch() {
        if (!current_user_can('administrator')) {
            exit;
        }
        if (isset($_POST['msEmail'])) {
            update_option('gpx_msemailTo', $_POST['msEmailTo']);
            update_option('gpx_msemail', $_POST['msEmail']);
            update_option('gpx_msemailName', $_POST['msEmailName']);
            update_option('gpx_msemailSubject', $_POST['msEmailSubject']);
            update_option('gpx_msemailMessage', $_POST['msEmailMessage']);
            update_option('gpx_msemailDays', $_POST['msEmailDays']);
        }

        $data = [];
        $data['msemailTo'] = get_option('gpx_msemailTo');
        $data['msemail'] = get_option('gpx_msemail');
        $data['msemailName'] = get_option('gpx_msemailName');
        $data['msemailSubject'] = get_option('gpx_msemailSubject');
        $data['msemailMessage'] = get_option('gpx_msemailMessage');
        $data['msemailDays'] = get_option('gpx_msemailDays');

        return $data;
    }

    public function reportwriter($id = null, $cron = null) {
        global $wpdb;

        $data = [
            'available_roles' => gpx_report_roles(),
        ];
        $editid = gpx_request('editid');
        if ($editid) {
            $sql = $wpdb->prepare("SELECT * FROM wp_gpx_report_writer WHERE id=%s", $editid);
            $data['editreport'] = $wpdb->get_row($sql);
        }
        if ($id) {
            return gpx_run_report($id, $cron);
        }

        $skipWheres = [
            'wp_room.record_id',
            'wp_credit.id',
            'wp_partner.record_id',
            'wp_partner.no_of_rooms_given',
            'wp_partner.no_of_rooms_received_take',
            'wp_partner.trade_balanc',
            'wp_partner.debit_balance',
            'wp_gpxTransactions.id',
            'wp_gpxTransactions.cartID',
            'wp_gpxTransactions.sessionID',
            'wp_gpxTransactions.userID',
            'wp_gpxTransactions.paymentGatewayID',
            'wp_gpxTransactions.sfData',
        ];


        $tables = gpx_report_writer('tables');

        foreach ($tables as $table) {
            foreach ($table['fields'] as $tk => $tf) {
                $type = $tf['type'] ?? null;
                $fieldData = $tf['data'] ?? null;
                $cancelledData = $tf['cancelledData'] ?? null;
                if (is_array($tf)) {
                    $tf['column'] = $tf['column'] ?? '';
                    $tf['xref'] = $tf['xref'] ?? '';
                }

                if ($type == 'join') {
                    $data['fields'][$table['table']][$tf['column'] . $tf['xref']] = [
                        'name' => $tf['name'],
                        'field' => $tf['xref'],
                    ];
                    if (in_array($table['table'] . "." . $tk, $skipWheres)) {
                        //we don't want to set this one
                    } else {
                        $whereField = $tf['xref'];
                        if (isset($tf['where'])) {
                            $whereField = $tf['where'];
                        }
                        $data['wheres'][$table['name']][] = [
                            'name' => $tf['name'],
                            'field' => $whereField,
                        ];
                    }
                } elseif (in_array($type, ['join_case', 'join_json', 'case'])) {
                    $data['fields'][$table['table']][$tf['column'] . $tf['xref']] = [
                        'name' => $tf['name'],
                        'field' => $tf['xref'],
                    ];
                } elseif ($type == 'qjson') {
                    $data['fields'][$table['table']][$table['table'] . "." . $tk . "." . $tf['xref']] = [
                        'name' => $tf['name'],
                        'field' => $tf['xref'],
                    ];
                } elseif ($type == 'agentname') {
                    $data['fields'][$table['table']][$table['table'] . "." . $tk . "." . $tf['xref']] = [
                        'name' => $tf['name'],
                        'field' => $tf['xref'],
                    ];
                } elseif ($type == 'usermeta') {
                    $data['fields'][$table['table']][$table['table'] . "." . $tk . "." . $tf['xref']] = [
                        'name' => $tf['name'],
                        'field' => $table['table'] . "." . $tf['xref'] . "." . $tk,
                    ];
                } elseif (is_array($fieldData)) {

                    foreach ($fieldData as $tdk => $tdf) {
                        $data['fields'][$table['table']][$table['table'] . "." . $tk . "." . $tdk] = [
                            'name' => $tdf,
                            'field' => $table['table'] . "." . $tk . "." . $tdk,
                        ];
                    }
                } elseif (is_array($cancelledData)) {

                    foreach ($cancelledData as $tdk => $tdf) {
                        $data['fields'][$table['table']][$table['table'] . "." . $tk . "." . $tdk] = [
                            'name' => $tdf,
                            'field' => $table['table'] . "." . $tk . "." . $tdk,
                        ];
                    }
                } elseif ($type == 'json' || $type == 'json_split') {

                    foreach ($fieldData as $tdk => $tdf) {
                        $data['fields'][$table['table']][$table['table'] . "." . $tk . "." . $tdk] = [
                            'name' => $tdf,
                            'field' => $table['table'] . "." . $tk . "." . $tdk,
                        ];
                    }
                } else {
                    $data['fields'][$table['table']][] = [
                        'name' => $tf,
                        'field' => $table['table'] . "." . $tk,
                    ];
                    if (in_array($table['table'] . "." . $tk, $skipWheres)) {
                        //we don't want to set this one
                    } else {
                        $data['wheres'][$table['name']][] = [
                            'name' => $tf,
                            'field' => $table['table'] . "." . $tk,
                        ];
                    }
                }
            }
            $data['tables'][$table['table']] = $table['name'];
        }

        $sql = "SELECT id, name, reportType, role, userID FROM wp_gpx_report_writer";
        $reports = $wpdb->get_results($sql);

        foreach ($reports as $k => $report) {
            //report types
            $reportType = explode(",", $report->reportType);
            if (in_array('Individual', $reportType)) {
                //this report must have been created by the current user
                if (get_current_user_id() != $report->userID) {
                    unset($reports[$k]);
                }
            }

            if (in_array('Group', $reportType)) {
                //this user must have a role that was assigned to the report
                $setRoles = explode(",", $report->role);
                $user_meta = get_userdata(get_current_user_id());
                $user_roles = $user_meta->roles;
                if (!gpx_in_array_any($user_roles, $setRoles)) {
                    //if this isn't part of the array then we don't need to continue.
                    unset($reports[$k]);
                }
            }

        }
        $data['reports'] = $reports;

        return $data;
    }
}
