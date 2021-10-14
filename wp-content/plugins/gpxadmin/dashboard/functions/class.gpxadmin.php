<?php

class GpxAdmin {
    
    protected $uri;
    protected $dir;
    
    
    public function __construct($uri, $dir)
    {
        $this->uri = $uri;
        $this->dir = $dir;
        $this->user = wp_get_current_user();
        require_once $dir.'/models/gpxmodel.php';
        $this->gpx_model = new GpxModel;
    }
    
    //getpage loads the page notice that it calls a separat function below which acts as a "controller"
    public function getpage($page = 'dashboard',$type='')
    {
        $page = $this->gpx_model->parse_page($page);
        if($type == 'admin')
            $templates = '/templates/admin/';
            $file = $templates.$page['parent'].'/'.$page['child'].'.php';
            $static['dashboard'] = admin_url('admin.php?page=gpx-admin-page');
            $static['user_data'] = $this->user;
            $static['dir'] = $this->dir;
            $id = '';
            if(isset($_GET['id']))
                $id = $_GET['id'];
                if(file_exists($this->dir.$file))
                {
                    $data = $this->{$page['child']}($id);
                    $data['active'] = $page['parent'];
                    require_once $this->dir.$file;
                }
                else
                {
                    $data = $this->dashboard();
                    $data['active'] = 'dashboard';
                    require_once $this->dir.'/templates/'.$type.'/dashboard.php';
                }
                
    }
    
    //"controlers"  or at least a really bad attempt to create a controller
    public function dashboard()
    {
        $data['payit'] = '';
        $data['suckit'] = '';
        
        return $data;
    }
    public function coupons()
    {
        $data = array();
        
        return $data;
    }
    public function couponview()
    {
        $data = array();
        
        return $data;
    }
    public function couponedit()
    {
        $data = array();
        
        return $data;
    }
    public function couponadd()
    {
        $data = array();
        
        return $data;
    }
    public function customrequests()
    {
        $data = array();
        
        return $data;
    }
    public function customrequestform()
    {
        if(!current_user_can('administrator'))
            exit;
            if(isset($_POST['crForm']))
            {
                update_option('gpx_crform', $_POST['crForm']);
            }
            
            $data = array();
            
            $data['crform'] = get_option('gpx_crform');
            
            return $data;
    }
    public function customrequestemail()
    {
        if(!current_user_can('administrator'))
            exit;
            if(isset($_POST['crEmail']))
            {
                update_option('gpx_cremail', $_POST['crEmail']);
                update_option('gpx_cremailName', $_POST['crEmailName']);
                update_option('gpx_cremailSubject', $_POST['crEmailSubject']);
                update_option('gpx_cremailMessage', $_POST['crEmailMessage']);
            }
            
            $data = array();
            
            $data['cremail'] = get_option('gpx_cremail');
            $data['cremailName'] = get_option('gpx_cremailName');
            $data['cremailSubject'] = get_option('gpx_cremailSubject');
            $data['cremailMessage'] = get_option('gpx_cremailMessage');
            
            return $data;
    }
    public function customrequestemailresortmatch()
    {
        if(!current_user_can('administrator'))
            exit;
            if(isset($_POST['crEmail']))
            {
                update_option('gpx_crresortmatchemail', $_POST['crEmail']);
                update_option('gpx_crresortmatchemailName', $_POST['crEmailName']);
                update_option('gpx_crresortmatchemailSubject', $_POST['crEmailSubject']);
                update_option('gpx_crresortmatchemailMessage', $_POST['crEmailMessage']);
            }
            
            $data = array();
            
            $data['cremail'] = get_option('gpx_crresortmatchemail');
            $data['cremailName'] = get_option('gpx_crresortmatchemailName');
            $data['cremailSubject'] = get_option('gpx_crresortmatchemailSubject');
            $data['cremailMessage'] = get_option('gpx_crresortmatchemailMessage');
            
            return $data;
    }
    public function customrequestemailresortmissed()
    {
        if(!current_user_can('administrator'))
            exit;
            if(isset($_POST['crEmail']))
            {
                update_option('gpx_crresortmissedemail', $_POST['crEmail']);
                update_option('gpx_crresortmissedemailName', $_POST['crEmailName']);
                update_option('gpx_crresortmissedemailSubject', $_POST['crEmailSubject']);
                update_option('gpx_crresortmissedemailMessage', $_POST['crEmailMessage']);
            }
            
            $data = array();
            
            $data['cremail'] = get_option('gpx_crresortmissedemail');
            $data['cremailName'] = get_option('gpx_crresortmissedemailName');
            $data['cremailSubject'] = get_option('gpx_crresortmissedemailSubject');
            $data['cremailMessage'] = get_option('gpx_crresortmissedemailMessage');
            
            return $data;
    }
    public function customrequestemailsixtyday()
    {
        if(!current_user_can('administrator'))
            exit;
            if(isset($_POST['crEmail']))
            {
                update_option('gpx_crsixtydayemail', $_POST['crEmail']);
                update_option('gpx_crsixtydayemailName', $_POST['crEmailName']);
                update_option('gpx_crsixtydayemailSubject', $_POST['crEmailSubject']);
                update_option('gpx_crsixtydayemailMessage', $_POST['crEmailMessage']);
            }
            
            $data = array();
            
            $data['cremail'] = get_option('gpx_crsixtydayemail');
            $data['cremailName'] = get_option('gpx_crsixtydayemailName');
            $data['cremailSubject'] = get_option('gpx_crsixtydayemailSubject');
            $data['cremailMessage'] = get_option('gpx_crsixtydayemailMessage');
            
            return $data;
    }
    public function customrequestemailreports()
    {
        if(!current_user_can('administrator'))
            exit;
            if(isset($_POST['crEmail']))
            {
                update_option('gpx_crreportsemailTo', $_POST['crEmailTo']);
                update_option('gpx_crreportsemailFrom', $_POST['crEmail']);
                update_option('gpx_crreportsemailName', $_POST['crEmailName']);
                update_option('gpx_crreportsemailSubject', $_POST['crEmailSubject']);
                update_option('gpx_crreportsemailMessage', $_POST['crEmailMessage']);
            }
            
            $data = array();
            
            $data['cremailTo'] = get_option('gpx_crreportsemailTo');
            $data['cremail'] = get_option('gpx_crreportsemailFrom');
            $data['cremailName'] = get_option('gpx_crreportsemailName');
            $data['cremailSubject'] = get_option('gpx_crreportsemailSubject');
            $data['cremailMessage'] = get_option('gpx_crreportsemailMessage');
            
            return $data;
    }
    public function promos()
    {
        $data = array();
        
        return $data;
    }
    public function promoview()
    {
        $data = array();
        
        return $data;
    }
    public function promoedit($id='')
    {
        global $wpdb;
        
        if(isset($_POST['bookingFunnel']))
        {
            $post = $this->return_add_gpx_promo($_POST);
            echo '<script>window.location.href = "/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=promos_all";</script>';
            exit;
        }
        
        $data = array('usage'=>'', 'exclusions'=>'');
        $sql = "SELECT * FROM wp_specials WHERE id='".$id."'";
        $data['promo'] = $wpdb->get_row($sql);
        $meta = stripslashes_deep( json_decode($data['promo']->Properties));

        $data['promometa'] = $meta;

        $sql = "SELECT id, Name FROM wp_specials WHERE active=1 ORDER BY Name";
        $data['special_masters'] = $wpdb->get_results($sql);
        
        if(isset($data['promometa']->usage) && !empty($data['promometa']->usage))
        {
            $data['usage'] = $meta->usage;
            switch($data['promometa']->usage)
            {
                case 'region':
                    if($meta->usage_regionType == 'daeCountry')
                    {
                        $name = 'country';
                        $select = $name;
                    }
                    else
                    {
                        $name = 'name';
                        $select = $name.', RegionID';
                    }
                    $jsonUsageRegion = json_decode($meta->usage_region);
                    if(json_last_error() !== 0)
                    {
                        $sql = 'SELECT '.$select. ' FROM wp_'.$meta->usage_regionType.' WHERE id="'.$meta->usage_region.'"';
                        $reg = $wpdb->get_row($sql);
                        $data['usage_regionName'] = $reg->$name;
                        if($data['usage_regionName'] == 'All')
                        {
                            $sql = "SELECT country FROM wp_gpxCategory a INNER JOIN wp_daeRegion b ON b.countryID=a.id WHERE b.RegionID='".$reg->RegionID."'";
                            $par = $wpdb->get_row($sql);
                            $data['usage_parent'] = $par->country;
                        }
                    }
                    break;
                    
                case 'resort':
                case 'customer':
                    $resorts = '';
                    if(isset($meta->usage_resort))
                    {
                        $resorts = implode('","', $meta->usage_resort);
                    }
                    $sql = 'SELECT id, ResortName FROM wp_resorts WHERE id IN("'.$resorts.'")';
                    $data['usage_resortNames'] = $wpdb->get_results($sql);
                    if(isset($meta->specificCustomer))
                    {
                        $sc = json_decode($meta->specificCustomer);
                        $data['promometa']->specificCustomer = $this->return_get_gpx_customers($sc);
                    }
                    break;
            }
        }
        if(isset($data['promometa']->exclusions) && !empty($data['promometa']->exclusions))
        {
            $data['exclusions'] = $meta->exclusions;
            switch($data['promometa']->exclusions)
            {
                case 'region':
                    //                     if($meta->exclude_regionType == 'daeCountry')
                        //                     {
                        //                         $name = 'country';
                        //                         $select = $name;
                        //                     }
                    //                     else
                        //                     {
                        //                         $name = 'name';
                        //                         $select = $name.', RegionID';
                        //                     }
                    //                     $sql = 'SELE0CT '.$select. ' FROM wp_'.$meta->exclude_regionType.' WHERE id="'.$meta->exclude_region.'"';
                    //                     $reg = $wpdb->get_row($sql);
                    //                     $data['exclude_regionName'] = $reg->$name;
                    //                     if($data['exclude_regionName'] == 'All')
                        //                     {
                        //                         $sql = "SELECT country FROM wp_gpxCategory a INNER JOIN wp_daeRegion b ON b.countryID=a.id WHERE b.RegionID='".$reg->RegionID."'";
                        //                         $par = $wpdb->get_row($sql);
                        //                         $data['exclude_parent'] = $par->country;
                        //                     }
                    break;
                    
                case 'resort':
                case 'customer':
                    if(isset($meta->exclude_resort))
                    {
                        $resorts = implode($meta->exclude_resort, '","');
                        $sql = 'SELECT id, ResortName FROM wp_resorts WHERE id IN("'.$resorts.'")';
                        $data['exclude_resortNames'] = $wpdb->get_results($sql);
                    }
                    break;
                    
                case 'home-resort':
                    $resorts = implode($meta->exclude_home_resort, '","');
                    $sql = 'SELECT id, ResortName FROM wp_resorts WHERE id IN("'.$resorts.'")';
                    $data['exclude_resortNames'] = $wpdb->get_results($sql);
                    break;
            }
        }

        return $data;
    }
    public function promoadd()
    {
        global $wpdb;
        $data = array();
        
        if(isset($_POST['bookingFunnel']))
        {
            $post = $this->return_add_gpx_promo($_POST);
            echo '<script>window.location.href = "/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=promos_all";</script>';
            exit;
        }
        
        $sql = "SELECT id, Name FROM wp_specials WHERE active=1 ORDER BY Name";
        $data['special_masters'] = $wpdb->get_results($sql);
        
        return $data;
    }
    public function promoautocoupons()
    {
        $data = array();
        
        return $data;
    }
    public function promodeccoupons()
    {
        $data = array();
        
        return $data;
    }
    public function promodeccouponexceptions()
    {
        $data = array();
        
        return $data;
    }
    public function promodeccouponsadd($post=[])
    {
        global $wpdb;
        
        $data = array();
        
        if(!empty($post))
        {
            $_POST = $post;
        }
        
        $occ = [
            'Name'=>'name',
            'Slug'=>'couponcode',
            'Active'=>'active',
            'singleuse'=>'singleuse',
            'expirationDate'=>'expirationDate',
            'comments'=>'comments',
        ];
        $oca = [
            'amount'=>'amount',
        ];
        $oco = [
            'owners'=>'ownerID',
        ];
        $allvars = array_merge($occ, $oca, $oco);
        foreach($allvars as $key=>$val)
        {
            $data['vars'][$key] = '';
        }
        if(isset($_POST['Name']))
        {
            if(empty($_POST['expirationDate']))
            {
                $_POST['expirationDate'] = date('Y-m-d', strtotime("+1 year"));
            }
            foreach($allvars as $key=>$val)
            {
                if($_POST[$key] != '0' && empty($_POST[$key]))
                {
                    $error[$key] = true;
                }
            }
            if(!isset($error))
            {
                foreach($occ as $key=>$val)
                {
                    $coupon[$val] = $_POST[$key];
                }
                if(empty($coupon['expirationDate']))
                {
                    $coupon['expirationDate'] = date('Y-m-d', strtotime('+10 year'));
                }
                if(!empty($_POST['created_date']))
                {
                    $coupon['created_date'] = date('Y-m-d', strtotime($_POST['created_date']));
                }
                
                $wpdb->insert('wp_gpxOwnerCreditCoupon', $coupon);
                
                if(isset($_REQUEST['occ_debug']))
                {
                    echo '<pre>'.print_r($coupon, true).'</pre>';
                    echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                    echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                }
                
                $last_id = $wpdb->insert_id;
                $data['coupon'] = $last_id;
                if(isset($last_id))
                {
                    //insert into the wp_gpxOwnerCreditCoupon_activity table
                    foreach($oca as $key=>$val)
                    {
                        $activity[$val] = $_POST[$key];
                    }
                    if(!isset($error))
                    {
                        $activity['couponID'] = $last_id;
                        $activity['activity'] = 'created';
                        $activity['activity_comments'] = date('m/d/Y H:i').': '.$_POST['comments'];
                        $activity['userID'] = get_current_user_id();
                    }
                    $wpdb->insert('wp_gpxOwnerCreditCoupon_activity', $activity);
                    
                    //insert into the wp_gpxOwnerCreditCoupon_owner table
                    foreach($_POST['owners'] as $owner)
                    {
                        $insertOwner = [
                            'couponID'=> $last_id,
                            'ownerID'=>$owner,
                        ];
                        $wpdb->insert('wp_gpxOwnerCreditCoupon_owner', $insertOwner);
                    }
                }
            }
            else
            {
                $html = '';
                foreach($allvars as $key=>$val)
                {
                    if($key == 'owners')
                    {
                        foreach($_POST['owners'] as $owner)
                        {
                            $html .= $this->return_get_gpx_findowner($owner, 'option', 'user_id');
                        }
                        $data['vars'][$key] = $html;
                    }
                    else
                    {
                        $data['vars'][$key] = $_POST[$key];
                    }
                }
            }
        }
        
        return $data;
    }
    public function promodeccouponsedit($id='')
    {
        /*
         * @todo: create the code to display details --
         * add activity form to bottom of activity
         *
         */
        global $wpdb;
        
        $data = array();
        
        $occ = [
            'Name'=>'name',
            'Slug'=>'couponcode',
            'Active'=>'active',
            'singleuse'=>'singleuse',
            'expirationDate'=>'expirationDate',
            'comments' => 'comments'
        ];
        $oca = [
            'amount'=>'amount',
        ];
        $oco = [
            'owners'=>'ownerID',
        ];
        $allvars = array_merge($occ, $oco);
        foreach($allvars as $key=>$val)
        {
            $data['vars'][$key] = '';
        }
        if(isset($_POST['Name']))
        {
            foreach($allvars as $key=>$val)
            {
                if($_POST[$key] != '0' && empty($_POST[$key]))
                {
                    $error[$key] = true;
                }
            }
            if(!isset($error))
            {
                foreach($occ as $key=>$val)
                {
                    if($key == 'comments')
                    {
                        $sql = "SELECT comments FROM wp_gpxOwnerCreditCoupon WHERE id='".$id."'";
                        $newComment = $_POST[$key];
                        $_POST[$key] = $wpdb->get_var($sql);
                        
                        $_POST[$key] .= ' '.date('m/d/Y H:i').': '.$newComment;
                    }
                    $coupon[$val] = $_POST[$key];
                }
                $coupon['expirationDate'] = date('Y-m-d', strtotime($_POST['expirationDate']));
                $wpdb->update('wp_gpxOwnerCreditCoupon', $coupon, array('id'=>$id));
                
                //remove all owners becase we will add them back in next
                $wpdb->delete('wp_gpxOwnerCreditCoupon_owner', array('couponID'=>$id));
                
                //insert into the wp_gpxOwnerCreditCoupon_owner table
                foreach($_POST['owners'] as $owner)
                {
                    $insertOwner = [
                        'couponID'=> $id,
                        'ownerID'=>$owner,
                    ];
                    $wpdb->insert('wp_gpxOwnerCreditCoupon_owner', $insertOwner);
                }
            }
            else
            {
                $html = '';
                foreach($allvars as $key=>$val)
                {
                    if($key == 'owners')
                    {
                        foreach($_POST['owners'] as $owner)
                        {
                            $html .= $this->return_get_gpx_findowner($owner, 'option', 'user_id');
                        }
                        $data['vars'][$key] = $html;
                    }
                    else
                    {
                        $data['vars'][$key] = $_POST[$key];
                    }
                }
            }
        }
        if(isset($_POST['newActivity']))
        {
            $newActivity = [
                'couponID'=>$id,
                'activity'=>'adjustment',
                'amount'=>$_POST['newActivity'],
                'userID'=>get_current_user_id(),
                'activity_comments'=>date('m/d/Y H:i').': '.$_POST['newActivityComment'],
            ];
            $wpdb->insert('wp_gpxOwnerCreditCoupon_activity', $newActivity);
            
            if(isset($_REQUEST['occ_debug']))
            {
                echo '<pre>'.print_r($newActivity, true).'</pre>';
                echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
            }
            
        }
        //if $data['vars']['Name'] is previously set when the form is invalid.  When set, don't pull the results from the database.t
        if(!isset($error))
        {
            //get the coupon
            $sql = "SELECT *, a.id as cid, b.id as oid, c.id as aid FROM wp_gpxOwnerCreditCoupon a
                    INNER JOIN wp_gpxOwnerCreditCoupon_owner b ON b.couponID=a.id
                    INNER JOIN wp_gpxOwnerCreditCoupon_activity c ON c.couponID=a.id
                    WHERE a.id='".$id."'";
            $coupons = $wpdb->get_results($sql);

            foreach($coupons as $coupon)
            {
                $distinctCoupon = $coupon;
                $distinctOwner[$coupon->oid] = $coupon;
                $distinctActivity[$coupon->aid] = $coupon;
            }
            //get the balance and activity for data
            foreach($distinctActivity as $activity)
            {
                if($activity->activity == 'transaction')
                {
                    $redeemed[] = $activity->amount;
                }
                else
                {
                    $amount[] = $activity->amount;
                }
            }
            if($distinctCoupon->single_use == 1 && array_sum($redeemed) > 0)
            {
                $balance = 0;
            }
            else
            {
                $balance = array_sum($amount) - array_sum($redeemed);
            }
            $data['vars']['amount'] = $balance;
            $data['activity'] = $distinctActivity;
            
            // gneral coupon info for data
            foreach($occ as $key=>$val)
            {
                $data['vars'][$key] = $distinctCoupon->$val;
            }
            // owners for data
            $html = '';
            foreach($distinctOwner as $do)
            {
                $html .= $this->return_get_gpx_findowner($do->ownerID, 'option', 'user_id');
            }
            $data['vars']['owners'] = $html;
            
        }
        
        return $data;
    }
    public function regions()
    {
        $data = array();
        
        return $data;
    }
    public function inventory()
    {
        $data = array();
        
        return $data;
    }
    public function regionview()
    {
        $data = array();
        
        return $data;
    }
    public function regionedit($id='')
    {
        global $wpdb;
        
        $data = $this->return_gpx_region($id);
        
        $sql = "SELECT country, CountryID FROM wp_gpxCategory";
        $data['countries'] = $wpdb->get_results($sql);
        
        $sql = "SELECT name, RegionID, featured, ddHidden, displayName FROM wp_gpxRegion WHERE id='".$id."'";
        $row = $wpdb->get_row($sql);
        $data['RegionID'] = $row->RegionID;
        $data['featured'] = $row->featured;
        $data['name'] = $row->name;
        $data['displayName'] = $row->displayName;
        $data['selected'] = $id;
        $data['hidden'] = $row->ddHidden;
        return $data;
    }
    public function regionadd()
    {
        global $wpdb;
        $data = array();
        
         $sql = "SELECT country, CountryID FROM wp_gpxCategory WHERE newCountryID > 0 ORDER BY CountryID";
         $data['countries'] = $wpdb->get_results($sql);
        
        return $data;
    }
    public function regionassignlist()
    {
        $data = array();
        
        return $data;
    }
    public function regionassign($id='')
    {
        global $wpdb;
        $data = array();
        
        $sql = "SELECT ResortName, gpxRegionID FROM wp_resorts WHERE id='".$id."'";
        $resort = $wpdb->get_row($sql);
        $data = $this->return_gpx_region($resort->gpxRegionID);
        
        $data['resort'] = $resort;
        $sql = "SELECT country, CountryID FROM wp_gpxCategory";
        $data['countries'] = $wpdb->get_results($sql);
        $data['selected'] = $resort->gpxRegionID;
        return $data;
    }
    public function resorts()
    {
        $data = array();
        
        return $data;
    }

    public function unitTypeadd()
    {
        global $wpdb;
        $sql = "SELECT id , ResortName FROM `wp_resorts`";
        $result = $wpdb->get_results($sql);    
        $data = array();
        $data['resorts'] = $result;
        return $data;
    }

    public function room()
    {
        $data = array();
        
        return $data;
    }
    public function roomerror()
    {
        $data = array();
        
        return $data;
    }

    public function roomadd()
    {
        global $wpdb;
        $resort = "SELECT id, ResortName FROM `wp_resorts` WHERE `active` = 1 ORDER BY ResortName";
        $resorts = $wpdb->get_results($resort);    
        $data = array();
        $data['resort'] = $resorts;
        //SELECT record_id,name FROM `wp_partner`
        $partner = "SELECT record_id,name FROM `wp_partner`";
        $part = $wpdb->get_results($partner);    
        
        $data['partner'] = $part;

        
        
        return $data;
    }
    // room edit function

    public function roomedit($id='') 
    {
        global $wpdb;
        $data = array();
        $resort = "SELECT id, ResortName FROM `wp_resorts` WHERE `active` = 1";
        $resorts = $wpdb->get_results($resort);       
        $data = array();
         $data['resort'] = $resorts;
        $data['unit'] = $unit;

        $partner = "SELECT record_id,name FROM `wp_partner`";
        $part = $wpdb->get_results($partner);    
        $data['partner'] = $part;
        $rooms =  "SELECT * FROM wp_room WHERE record_id='".$id."'";
        $room = $wpdb->get_results($rooms);
        
        //get the users that touched this
        $data['updateDets'] = json_decode($room[0]->update_details);
        if(isset($_REQUEST['room_debug']))
        {
            echo '<pre>'.print_r($data['updateDets'], true).'</pre>';
        }
        foreach($data['updateDets'] as $det)
        {
            $usrs = $det->update_by;
            if(isset($_REQUEST['room_debug']))
            {
                echo '<pre>'.print_r($usrs, true).'</pre>';
            }
            $user = get_user_by('ID', $usrs);
            if(isset($_REQUEST['room_debug']))
            {
                echo '<pre>'.print_r($user, true).'</pre>';
            }            
            $data['update_users'][$usrs] = $user->first_name." ".$user->last_name;
        }
        if(isset($_REQUEST['room_debug']))
        {
            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
            echo '<pre>'.print_r($data['update_users'], true).'</pre>';
        }
        //SELECT *  FROM `wp_unit_type` WHERE `resort_id` = 1 ORDER BY `record_id`  DESC
        $wp_unit_type =  "SELECT *  FROM `wp_unit_type` WHERE `resort_id` ='".$room[0]->resort."'";
        $unit_type = $wpdb->get_results($wp_unit_type, OBJECT_K);

        $data['unit_type'] = $unit_type;


        $rooms =  "SELECT a.*, b.id as txid FROM wp_room a
                   LEFT OUTER JOIN wp_gpxTransactions b ON a.record_id=b.weekId WHERE record_id='".$id."'";
        $room = $wpdb->get_results($rooms);   

        if($room[0]->archived == 1)
        {
             $room[0]->status = 'Archived';
        }
        else 
        {
            //Method to extract Booked/Held State
            if ($room[0]->active != 1){
                
                $sql = "select `gpx`.`wp_gpxTransactions`.`weekId`
    				from `gpx`.`wp_gpxTransactions` where `gpx`.`wp_gpxTransactions`.`weekId` = '".$room[0]->record_id."' AND `gpx`.`wp_gpxTransactions`.`cancelled` IS NULL";
                $booked = $wpdb->get_var($sql);
                    
                if(!empty($booked)) {
                    $room[0]->status = 'Booked';
                } else {
                    $sql = "select `wp_gpxPreHold`.`weekId` from `wp_gpxPreHold`
                        where (`wp_gpxPreHold`.`released` = 0) AND `wp_gpxPreHold`.`propertyID`='".$room[0]->record_id."'";
                    
                    $held = $wpdb->get_var($sql);
                        
                    if(!empty($held)) {
                        $room[0]->status = 'Held';
                    } else
                    {
                        $room[0]->status = 'Available';
                    }
                }
            } else {
                $room[0]->status = "Available";
            }
        }

        $user = "SELECT *  FROM `wp_partner` WHERE `user_id` = '".$room[0]->source_partner_id."'";
        $user_result = $wpdb->get_results($user);  
        $data['user'] = $user_result;

        $data['room'] = $room;
        $data['id'] = $id;
        
        
        $data['disabled'] = '';
        
        if($this->weekisbooked($id))
        {
            $data['disabled'] = 'disabled';
        }
        return $data;
    }

    public function roomimport() 
    {
        global $wpdb;
        $data = array();
        return $data;
    }

    public function unitTypeForm()
    {   
        global $wpdb;
        $data = array();
        return $data;
    }

    public function resortadd()
    {
        global $wpdb;
        // $sql = "SELECT CountryID, country FROM `wp_daeCountry` ORDER BY `CountryID` ASC";
        // $country = $wpdb->get_results($sql);
        
//         require_once GPXADMIN_API_DIR.'/functions/class.restsaleforce.php';
//         $gpxRest = new RestSalesforce();

        
//         require_once GPXADMIN_API_DIR.'/functions/class.salesforce.php';
//         $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
        $sf = Salesforce::getInstance();
        
        $data = array();

        $data['message'] = '';
        // $data['country'] = $country;
        
//         if(isset($_POST['ResortID']) && !empty($_POST['ResortID']) && !empty($_POST['EndpointID']))
//         {
            //make sure this resort hasn't already been added
        if(isset($_POST['ResortID']))  
        {
            $sql = "SELECT * FROM wp_resorts WHERE ResortID='".$_POST['ResortID']."'";
            $result = $wpdb->get_row($sql);
            
            if(empty($result))
            {
                $toSend = [
                    'ResortID'=>'ResortID',
                    'Name'=>'ResortName',
                    'GPX_Resort_ID__c'=>'id',
                    'Additional_Info__c'=>'AdditionalInfo',
                    'Address_Cont__c'=>'Address2',
                    'Check_In_Days__c'=>'CheckInDays',
                    'Check_In_Time__c'=>'CheckInEarliest',
                    'Check_Out_Time__c'=>'CheckOutLatest',
                    'City__c'=>'Town',
                    'Closest_Airport__c'=>'Airport',
                    'Country__c'=>'Country',
                    'Directions__c'=>'Directions',
                    'Fax__c'=>'Fax',
                    'Phone__c'=>'Phone',
                    'Resort_Description__c'=>'Description',
                    'Resort_Website__c'=>'Website',
                    //                     'RSF__c'=>'CheckInDays',
                    'State_Region__c'=>'Region',
                    'Street_Address__c'=>'Address1',
                    'Zip_Postal_Code__c'=>'PostCode',
                ];
                
                foreacH($toSend as $ts)
                {
                    if($ts == 'id')
                    {
                        continue;
                    }
                    $inputMembers[$ts] = $_POST[$ts];
                }
                
//                 $inputMembers = array(
//                     'ResortID'=>$_POST['ResortID'],
// //                     'EndpointID'=>$_POST['EndpointID'],
//                     'ResortName'=>$_POST['ResortName'],
//                     'WebLink'=>$_POST['WebLink'],
//                     'AlertNote'=>$_POST['AlertNote'],
//                     'Address1'=>$_POST['Address1'],
//                     'Address2'=>$_POST['Address2'],
//                     'Town'=>$_POST['Town'],
//                     'PostCode'=>$_POST['PostCode'],
//                     'Phone'=>$_POST['Phone'],
//                     'Fax'=>$_POST['Fax'],
//                     'Email'=>$_POST['Email'],
//                 );
                
                 
                $wpdb->insert('wp_resorts', $inputMembers);

                unset($toSend['ResortID']);
                
                $inputMembers['id'] = $wpdb->insert_id;
                
                foreach($toSend as $sk=>$sv)
                {
                    $sfResortData[$sk] = str_replace("&", "and", $inputMembers[$sv]);
                    $breaks = array("<br />","<br>","<br/>");
                    $sfResortData[$sk] = str_ireplace($breaks, "\r\n", $sfResortData[$sk]);
                }
                
                $sfWeekAdd = '';
                $sfAdd = '';
                $sfType = 'GPX_Resort__c';
                $sfObject = 'GPX_Resort_ID__c';
                
                $sfFields = [];
                $sfFields[0] = new SObject();
                $sfFields[0]->fields = $sfResortData;
                $sfFields[0]->type = $sfType;
                
                $sfResortAdd = $sf->gpxUpsert($sfObject, $sfFields);
                
                $sfID = $sfResortAdd[0]->id;
                
                $wpdb->update('wp_resorts', array('sf_GPX_Resort__c'=>$sfID), array('id'=>$row->id));
                
               // $profile = $gpxRest->DAEGetResortProfile('', 'NA', $inputMembers, 'insert');
                
                $data['message'] = 'Resort Added!';
            }
            else
            {
                $data['message'] = 'That resort is already in the system!';
            }
        }
        
        return $data;
    }
    public function hoteladd()
    {
        global $wpdb;
        // $sql = "SELECT CountryID, country FROM `wp_daeCountry` ORDER BY `CountryID` ASC";
        // $country = $wpdb->get_results($sql);
        
//         require_once GPXADMIN_API_DIR.'/functions/class.restsaleforce.php';
//         $gpxRest = new RestSalesforce();

        
//         require_once GPXADMIN_API_DIR.'/functions/class.salesforce.php';
//         $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
        $sf = Salesforce::getInstance();
        
        $data = array();

        $data['message'] = '';
        // $data['country'] = $country;
        
        
        require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
        $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
        
        if(isset($_POST['ResortID']) && !empty($_POST['ResortID']) && !empty($_POST['EndpointID']))
        {
            //make sure this resort hasn't already been added
            
            $sql = "SELECT * FROM wp_resorts WHERE ResortID='".$_POST['ResortID']."'";
            $result = $wpdb->get_row($sql);
            
            if(empty($result))
            {
                $inputMembers = array(
                    'ResortID'=>$_POST['ResortID'],
                    'EndpointID'=>$_POST['EndpointID'],
                    'ResortName'=>$_POST['ResortName'],
                    'WebLink'=>$_POST['WebLink'],
                    'AlertNote'=>$_POST['AlertNote'],
                    'Address1'=>$_POST['Address1'],
                    'Address2'=>$_POST['Address2'],
                    'Town'=>$_POST['Town'],
                    'PostCode'=>$_POST['PostCode'],
                    'Phone'=>$_POST['Phone'],
                    'Fax'=>$_POST['Fax'],
                    'Email'=>$_POST['Email'],
                );
                 
                $wpdb->insert('wp_resorts', $inputMembers);
                

               // $profile = $gpxRest->DAEGetResortProfile('', 'NA', $inputMembers, 'insert');
                
                $data['message'] = 'Resort Added!';
            }
            else
            {
                $data['message'] = 'That resort is already in the system!';
            }
        }
        
        return $data;
    }
    public function resorttaxes()
    {
        $data = array();
        
        return $data;
    }
    public function resorttaxesedit($id='')
    {
        global $wpdb;
        $data = array();
        
        $data['tax'] = $this->return_taxesedit($id);
        
        return $data;
    }
    public function resortedit($id='')
    {
        global $wpdb;
        $data = array();
        
        $data['resort'] = $this->return_gpx_resort($id);
        
        return $data;
    }
    public function tradepartners()
    {
        $data = array();
        
        return $data;
    }
    public function tradepartneradd()
    {
        $data = array();
        
        if(isset($_REQUEST['username']))
        {
            global $wpdb;
    
            //creatre the account
            $user = [
               'user_pass'=>wp_generate_password(),
               'user_login'=>$_REQUEST['username'],
               'user_email'=>$_REQUEST['email'],
               'first_name'=>$_REQUEST['name'],
               'nickname'=>$_REQUEST['name'],
               'role'=>'gpx_trade_partner' 
            ];
            
            //does this email exist?
            $cuser = get_user_by('user_email', $_REQUEST['email']);
            if(!empty($cuser))
            {
                $user['ID'] = $cuser->ID;
            }
            $user_id = wp_insert_user($user);
            
            $userrole = new WP_User( $user_id );
            
            $userrole->set_role('gpx_member');
            
            //add the details to the wp_partners table
            $insert = [
                'user_id'=>$user_id,
                'create_date'=>date('Y-m-d H:i:s'),
                'username'=>$_REQUEST['username'],
                'name'=>$_REQUEST['name'],
                'email'=>$_REQUEST['email'],
                'phone'=>$_REQUEST['phone'],
                'address'=>$_REQUEST['address'],
                'sf_account_id'=>$_REQUEST['sf_account_id'],
            ];
            $wpdb->insert('wp_partner', $insert);
        }
        return $data;
    }
    public function tradepartneredit($id='')
    {
        global $wpdb;
        
        $data = array();
        
        if(isset($_REQUEST['email']))
        {
            
    
//             //creatre the account
//             $user = [
//                'user_pass'=>wp_generate_password(),
//                'user_login'=>$_REQUEST['username'],
//                'user_email'=>$_REQUEST['email'],
//                'first_name'=>$_REQUEST['name'],
//                'nickname'=>$_REQUEST['name'],
//                'role'=>'gpx_trade_partner' 
//             ];
            
//             //does this email exist?
//             $cuser = get_user_by('user_email', $_REQUEST['email']);
//             if(!empty($cuser))
//             {
//                 $user['ID'] = $cuser->ID;
//             }
//             $user_id = wp_insert_user($user);
            
            //add the details to the wp_partners table
            $update = [
                'name'=>$_REQUEST['name'],
                'email'=>$_REQUEST['email'],
                'phone'=>$_REQUEST['phone'],
                'address'=>$_REQUEST['address'],
                'sf_account_id'=>$_REQUEST['sf_account_id'],
            ];
            $wpdb->update('wp_partner', $update, array('record_id'=>$id));
        }
        
        $sql = "SELECT * FROM wp_partner WHERE record_id='".$id."'";
        $data['tp'] = $wpdb->get_row($sql);

        return $data;
    }
    public function tradepartnerinventory()
    {
        $data = array();
        
        return $data;
    }
    public function tradepartnerview()
    {
        $data = array();
        
        return $data;
    }
    public function tradepartnernew()
    {
        $data = array();
        
        return $data;
    }
    public function tradepartneraddowner()
    {
        global $wpdb;
        $data = array();
        
        //get all users with trade partner role
        $data['tradepartners'] = get_users( 'role=gpx_trade_partner' );
        
        if(isset($_POST['tradepartner']) && (!empty($_POST['tradepartner']) || !empty($_POST['weekIDs'])))
        {
            if(!empty($_POST['weekIDs']) && !empty($_POST['tradepartner']))
            {
                $weekIDs = preg_split('/\r\n|[\r\n]/', $_POST['weekIDs']);
                foreach($weekIDs as $weekID)
                {
                    $poData = array(
                        'weekID'=>$weekID,
                        'ownedBy'=>$_POST['tradepartner']
                    );
                    $sql = "SELECT * FROM wp_propertyOwners WHERE weekID='".$weekID."'";
                    $row = $wpdb->get_row($sql);
                    
                    if(!empty($row))
                    {
                        $wpdb->update('wp_propertyOwners', $poData, array('id'=>$row->id));
                    }
                    else
                    {
                        $wpdb->insert('wp_propertyOwners', $poData);
                    }
                    $error[] = $wpdb->last_error;
                    
                }
            }
            else
            {
                $error[] = "You must select a trade partner and add at least one week ID!";
            }
            if(empty(str_replace(" ", $error)))
            {
                $data['message'][] = [
                    'type'=>'success',
                    'text'=>'Update Success!'
                ];
            }
            else
            {
                $data['message'][] = [
                    'type'=>'error',
                    'text'=>implode("; ", $error)
                ];
            }
        }
        return $data;
    }
    public function transactions()
    {
        $data = array();
        
        return $data;
    }
    public function transactionview($id='')
    {
        global $wpdb;
        
        $data = array();
        
        //is this user an admin?
        $data['isadmin'] = '';
        $user = wp_get_current_user();
        if ( in_array( 'administrator_plus', (array) $user->roles ) || in_array( 'administrator', (array) $user->roles ) || in_array( 'gpx_admin', (array) $user->roles ) ) {
            $data['isadmin'] = 'admin';
        }
        
        
        $sql = "SELECT a.id, a.id as transactionID, a.userID, a.data, a.datetime, a.weekId, b.ResortName, cancelled as fullcancel, cancelledData FROM wp_gpxTransactions a
                LEFT OUTER JOIN wp_resorts b ON a.resortID=b.ResortID
                WHERE a.id='".$id."'";
        $transaction = $wpdb->get_row($sql);

        $transactionData = json_decode($transaction->data);
        $cData['cancelled'] = json_decode($transaction->cancelledData);

        if(isset($transactionData->creditweekid))
        {
            
            //get the deposit details
            $sql = "SELECT a.*, b.unitweek FROM wp_credit a
                    LEFT OUTER JOIN wp_owner_interval b ON b.contractID=a.interval_number
                    WHERE a.id='".$transactionData->creditweekid."'";
            $transactionData->depositDetails = $wpdb->get_row($sql);
            
        }

        //transaction refund line item
        $isRefunded = [
            'guestfeeamount'=>'actguestFee',
            'erFee'=>'actWeekPrice',
            'cpofee'=>'actcpoFee',
            'latedepositfee'=>'actlatedepositfee',
            'creditextensionfee'=>'actextensionFee',
            'upgradefee'=>'actupgradeFee',
        ];
        //get each cancelled by line item
        foreach($cData['cancelled'] as $cK=>$cD)
        {
            $refunded[$cD->type][$cK] = $cD->amount;
            $refundAction[$cD->action][$cK] = $cD->amount;
            $data['coupons'][] = $cD->copuon;
        }
        foreach($refundAction as $raK=>$raV)
        {
            $data['refunded'][$raK] = array_sum($raV);
        }
       
        foreach($isRefunded as $irK=>$irV)
        {
            if(isset($refunded[$irK]))
            {
                $transactionData->$irV = $transactionData->$irV - array_sum($refunded[$irK]);
            }
        }
        unset($transaction->data);
        unset($transaction->cancelledData);
        unset($transactionData->processedBy);
        
        $data['transaction'] = (object) array_merge((array) $transaction, (array) $transactionData, $cData);

        $sql = "SELECT SUM(credit) as credit, SUM(debit) as debit FROM wp_owner_credit WHERE ownerID='".$data['transaction']->userID."'";
        $credit = $wpdb->get_row($sql);
        
        $data['balance'] = $credit->credit - $credit->debit;
       
        //is this a trade partner?
        $sql = "SELECT record_id FROM wp_partner WHERE user_id='".$transaction->userID."'";
        $data['partner'] = $wpdb->get_row($sql);
        
        return $data;
    }
    public function transactionadd()
    {
        $data = array();
        
        return $data;
    }
    public function transactionholds()
    {
        $data = array();
        
        return $data;
    }
    
    public function transactionimport()
    {
        global $wpdb;
        
        require_once GPXADMIN_API_DIR.'/functions/class.gpxretrieve.php';
        $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
        
        $data = array();

        
        if(isset($_POST['weekId']) && check_admin_referer('gpx_admin', 'gpx_import_transaction'))
        {
            $required = [
                'weekId'=>"Week ID",
                'ownerID'=>"Owner ID",
            ];
            
            foreach($required as $req=>$val)
            {
                if(empty($_POST[$req]))
                {
                    $data['msg']['type'] = 'error';
                    $data['msg']['text'] = $val." is required!";
                }
                else
                {
                    $where[$req] = $req." = ".$_POST[$req];
                }
            }
            
            if(!isset($data['msg']))
            {
                $vars = $required;
                $vars['resortID'] = 'Resort ID';
                $vars['depositID'] = 'Deposit ID';
            }
            
            foreach($vars as $key=>$var)
            {
                $data[$key] = $_POST[$key];
            }
            //pull from each file
            
            $tables = [
                'transactions_import',
                'transactions_import_two',
                'transactions_import_owner',
            ];
            
            foreach($tables as $table)
            {
                $sql = "SELECT * FROM ".$table." WHERE ".implode(" AND ", $where)."";
                $row = $wpdb->get_results($sql);
                if(!empty($row))
                {
                    break;
                }
            }
            
            if(empty($row))
            {
                $table = 'wp_gpxTransactions';
                if($_POST['overwrite'] == 'Yes')
                {
                    unset($where['ownerID']);
                }
                else
                {
                    $where['ownerID'] = str_replace("ownerID", "userID", $where['ownerID']);
                }
                
                $sql = "SELECT * FROM ".$table." WHERE ".implode(" AND ", $where)." ORDER BY id DESC LIMIT 1";
                $row = $wpdb->get_row($sql);

                if(!empty($row))
                {
                    $rowdata = json_decode($row->data);
                    $row = (object) array_merge((array) $row, (array) $rowdata);
                }
                else
                {
                    $data['msg'] = [
                        'type'=>'error',
                        'text'=>'Transaction not found!',
                    ];
                }
            }
           
            if(!empty($data['msg']))
            {
                return $data;
            }
            
            if($row->GuestName == '#N/A')
            {
                
                $data['msg'] = [
                    'type'=>'error',
                    'text'=>'Guest Name Error!',
                ];
            }
            //         if(!empty($resort))
            
            $resortKeyOne = [
                'Butterfield Park - VI'=>'2440',
                'Grand Palladium White Sand - AI'=>'46895',
                'Grand Sirenis Riviera Maya Resort - AI'=>'46896',
                'High Point World Resort - RHC'=>'1549',
                'Los Abrigados Resort & Spa'=>'2467',
                'Makai Club Cottages'=>'1786',
                'Palm Canyon Resort & Spa'=>'1397',
                'Sunset Marina Resort & Yacht Club - AI'=>'46897',
                'Azul Beach Resort Negril by Karisma - AI'=>'46898',
                'Bali Villas & Sports Club - Rentals Only'=>'46899',
                'Blue Whale'=>'46900',
                'Bluegreen Club 36'=>'46901',
                'BreakFree Alexandra Beach'=>'46902',
                'Classic @ Alpha Sovereign Hotel'=>'46903',
                'Club Regina Los Cabos'=>'46904',
                'Eagles Nest Resort - VI'=>'1836',
                'El Dorado Casitas Royale by Karisma'=>'46905',
                'El Dorado Casitas Royale by Karisma'=>'46905',
                'El Dorado Casitas Royale by Karisma, a Gourmet AIl Inclusive'=>'46905',
                'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive'=>'46905',
                'El Dorado Maroma by Karisma, a Gourmet AI'=>'46906',
                'El Dorado Maroma by Karisma a Gourmet AI'=>'46906',
                'El Dorado Royale by Karisma a Gourmet AI'=>'46907',
                'El Dorado Royale by Karisma, a Gourmet AI'=>'46907',
                'Fiesta Ameri. Vac Club At Cabo Del Sol'=>'46908',
                'Fort Brown Condo Shares'=>'46909',
                'Four Seasons Residence Club Scottsdale@Troon North'=>'2457',
                'Generations Riviera Maya by Karisma a Gourmet AI'=>'46910',
                'GPX Cruise Exchange'=>'SKIP',
                'Grand Palladium Jamaica Resort & Spa - AI'=>'46911',
                'Grand Palladium Vallarta Resort & Spa - AI'=>'46912',
                'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive'=>'46913',
                'High Sierra Condominiums'=>'46914',
                'Kiltannon Home Farm'=>'46915',
                'Knocktopher Abbey'=>'46916',
                'Knocktopher Abbey (Shadowed)'=>'46916',
                'Laguna Suites Golf and Spa - AI'=>'46917',
                'Maison St. Charles - Rentals Only'=>'46918',
                'Makai Club Resort'=>'1787',
                'Marina Del Rey Beach Club - No Longer Accepting'=>'46919',
                'Mantra Aqueous on Port'=>'46920',
                'Maui Sunset - Rentals Only'=>'1758',
                'Mayan Palace Mazatlan'=>'3652',
                'Ocean Gate Resort'=>'46921',
                'Ocean Spa Hotel - AI'=>'46922',
                'Paradise'=>'46923',
                'Park Royal Homestay Club Cala'=>'338',
                'Park Royal Los Cabos - RHC'=>'46924',
                'Peacock Suites Resort'=>'46925',
                'Pounamu Apartments - Rental'=>'46926',
                'Presidential Suites by LHVC - Punta Cana NON - AI'=>'46927',
                'RHC - Park Royal - Los Tules'=>'46928',
                'Royal Regency Paris (Shadowed)'=>'479',
                'Royal Sunset - AI'=>'46929',
                'Secrets Puerto Los Cabos Golf & Spa Resort - AI'=>'46930',
                'Secrets Wild Orchid Montego Bay - AI'=>'46931',
                'Solare Bahia Mar - Rentals Only'=>'46932',
                'Tahoe Trail - VI'=>'40',
                'The RePlay Residence'=>'46933',
                'The Tropical at LHVC - AI'=>'46934',
                'Vacation Village at Williamsburg'=>'2432',
                'Wolf Run Manor At Treasure Lake'=>'46935',
                'Wyndham Grand Desert - 3 Nights'=>'46936',
                'Wyndham Royal Garden at Waikiki - Rental Only'=>'1716',
            ];
            
            $resortKeyTwo = [
                'Royal Aloha Chandler - Butterfield Park'=>'2440',
                'Grand Palladium White Sand - AI'=>'46895',
                'Grand Sirenis Riviera Maya Resort - AI'=>'46896',
                'High Point World Resort'=>'1549',
                'Los Abrigados Resort and Spa'=>'2467',
                'Makai Club Resort Cottages'=>'1786',
                'Palm Canyon Resort and Spa'=>'1397',
                'Sunset Marina Resort & Yacht Club - AI'=>'46897',
                'Azul Beach Resort Negril by Karisma - AI'=>'46898',
                'Bali Villas & Sports Club - Rentals Only'=>'46899',
                'Blue Whale'=>'46900',
                'Bluegreen Club 36'=>'46901',
                'BreakFree Alexandra Beach'=>'46902',
                'Classic @ Alpha Sovereign Hotel'=>'46903',
                'Club Regina Los Cabos'=>'46904',
                'Royal Aloha Branson - Eagles Nest Resort'=>'1836',
                'El Dorado Casitas Royale by Karisma, a Gourmet AIl Inclusive' => '46905',
                'El Dorado Casitas Royale by Karisma a Gourmet AIl Inclusive'=>'46905',
                'El Dorado Maroma by Karisma a Gourmet AI'=>'46906',
                'El Dorado Royale by Karisma a Gourmet AI'=>'46907',
                'Fiesta Ameri. Vac Club At Cabo Del Sol'=>'46908',
                'Fort Brown Condo Shares'=>'46909',
                'Four Seasons Residence Club Scottsdale at Troon North'=>'2457',
                'Generations Riviera Maya by Karisma a Gourmet AI'=>'46910',
                'SKIP'=>'SKIP',
                'Grand Palladium Jamaica Resort & Spa - AI'=>'46911',
                'Grand Palladium Vallarta Resort & Spa - AI'=>'46912',
                'Grand Sirenis Matlali Hills Resort & Spa - All Inclusive'=>'46913',
                'High Sierra Condominiums'=>'46914',
                'Kiltannon Home Farm'=>'46915',
                'Knocktopher Abbey'=>'46916',
                'Knocktopher Abbey'=>'46916',
                'Laguna Suites Golf and Spa - AI'=>'46917',
                'Maison St. Charles - Rentals Only'=>'46918',
                'Makai Club Resort Condos'=>'1787',
                'Marina Del Rey Beach Club - No Longer Accepting'=>'46919',
                'Mantra Aqueous on Port'=>'46920',
                'Maui Sunset'=>'1758',
                'Mayan Palace Mazatlan by Grupo Vidanta'=>'3652',
                'Ocean Gate Resort'=>'46921',
                'Ocean Spa Hotel - AI'=>'46922',
                'Paradise'=>'46923',
                'Royal Holiday - Park Royal Club Cala'=>'338',
                'Park Royal Los Cabos - RHC'=>'46924',
                'Peacock Suites Resort'=>'46925',
                'Pounamu Apartments - Rental'=>'46926',
                'Presidential Suites by LHVC - Punta Cana NON - AI'=>'46927',
                'RHC - Park Royal - Los Tules'=>'46928',
                'Royal Regency By Diamond Resorts'=>'479',
                'Royal Sunset - AI'=>'46929',
                'Secrets Puerto Los Cabos Golf & Spa Resort - AI'=>'46930',
                'Secrets Wild Orchid Montego Bay - AI'=>'46931',
                'Solare Bahia Mar - Rentals Only'=>'46932',
                'Royal Aloha Tahoe'=>'40',
                'The RePlay Residence'=>'46933',
                'The Tropical at LHVC - AI'=>'46934',
                'Williamsburg Plantation Resort'=>'2432',
                'Wolf Run Manor At Treasure Lake'=>'46935',
                'Wyndham Grand Desert - 3 Nights'=>'46936',
                'Royal Garden at Waikiki Resort'=>'1716',
            ];
            $resortMissing = '';
            if(isset($_POST['resortID']))
            {
                $resortMissing = $_POST['resortID'];
            }
            else
            {
                if(array_key_exists($row->Resort_Name, $resortKeyOne))
                {
                    $resortMissing = $resortKeyOne[$row->Resort_Name];
                    if($resort == 'SKIP')
                    {
                    }
                }
                if(array_key_exists($row->Resort_Name, $resortKeyTwo))
                {
                    $resortMissing = $resortKeyTwo[$row->Resort_Name];
                    if($resort == 'SKIP')
                    {
                    }
                }
            }
            if(!empty($resortMissing))
            {
                $sql = "SELECT id, resortID, ResortName FROM wp_resorts WHERE id='".$resortMissing."'";
                $resort = $wpdb->get_row($sql);
                $resortName = $resort->ResortName;
            }
            else
            {
                $resortName = $row->Resort_Name;
                $resortName = str_replace("- VI", "", $resortName);
                $resortName = trim($resortName);
                $sql = $wpdb->prepare("SELECT id, resortID FROM wp_resorts WHERE ResortName=%s", $resortName);
                $resort = $wpdb->get_row($sql);
            }
            
            if(empty($resort))
            {
                $sql = $wpdb->prepare("SELECT missing_resort_id FROM import_credit_future_stay WHERE resort_name=%s", $resortName);
                $resort_ID = $wpdb->get_var($sql);
                
                $sql = "SELECT id, resortID, ResortName FROM wp_resorts WHERE id='".$resort_ID."'";
                $resort = $wpdb->get_row($sql);
                $resortID = $resort->resortID;
                $resortName = $resort->ResortName;
                
                
            }
            else
            {
                $resortID = $resort->id;
                $daeResortID = $resort->resortID;
            }
            
            if(empty($resort))
            {
                
                $data['msg'] = [
                    'type'=>'error',
                    'text'=>'Resort not found!',
                ];
            }
            
            $sql = "SELECT user_id FROM wp_GPR_Owner_ID__c WHERE user_id='".$row->MemberNumber."'";
            $user = $wpdb->get_var($sql);
            
            if(empty($user))
            {
                //let's try to import this owner
                $user = function_GPX_Owner($row->MemberNumber);
                
                if(empty($user))
                {
                    
                    $data['msg'] = [
                        'type'=>'error',
                        'text'=>'Owner not found!',
                    ];
                }
            }
            else
            {
                $userID = $user;
                
                $sql = "SELECT name FROM wp_partner WHERE user_id='".$userID."'";
                $memberName = $wpdb->get_var($sql);
                
                if(empty($memberName))
                {
                    $fn = get_user_meta($userID,'first_name', true);
                    
                    if(empty($fn))
                    {
                        $fn = get_user_meta($userID,'FirstName1', true);
                    }
                    $ln = get_user_meta($userID,'last_name', true);
                    if(empty($ln))
                    {
                        $ln = get_user_meta($userID,'LastName1', true);
                    }
                    if(!empty($fn) || !empty($ln))
                    {
                        $memberName = $fn." ".$ln;
                    }
                    else
                    {
                        
                        $data['msg'] = [
                            'type'=>'error',
                            'text'=>'Owner not found!',
                        ];
                    }
                }
            }
            
            $unitType = $row->Unit_Type;
            $sql = "SELECT record_id FROM wp_unit_type WHERE resort_id='".$resortID."' AND name='".$unitType."'";
            $unitID = $wpdb->get_var($sql);
            
            $bs = explode("/", $unitType);
            $beds = $bs[0];
            $beds = str_replace("b", "", $beds);
            if($beds == 'St')
            {
                $beds = 'STD';
            }
            $sleeps = $bs[1];
            if(empty($unitID))
            {
                $insert = [
                    'name'=>$unitType,
                    'create_date'=>date('Y-m-d'),
                    'number_of_bedrooms'=>$beds,
                    'sleeps_total'=>$sleeps,
                    'resort_id'=>$resortID,
                ];
                $wpdb->insert('wp_unit_type', $insert);
                $unitID = $wpdb->insert_id;
            }
            
            if(isset($_POST['depositID']))
            {
                $sql = "SELECT id FROM wp_credit WHERE id='".$_POST['depositID']."'";
                $deposit = $wpdb->get_var($sql);
                
                if(empty($deposit))
                {
                    $sql = "SELECT a.id FROM wp_credit a
                            INNER JOIN import_credit_future_stay b ON
                            b.Deposit_year=a.deposit_year AND
                            b.resort_name=a.resort_name AND
                            b.unit_type=a.unit_type AND
                            b.Member_Name=a.owner_id
                            WHERE b.ID=".$_POST['depositID'];
                    $deposit = $wpdb->get_var($sql);
                }
                if(empty($deposit))
                {
                    $data['msg'] = [
                        'type'=>'error',
                        'text'=>'Deposit not found!',
                    ];
                }
            }
            elseif($row->WeekTransactionType == 'Exchange')
            {
                $sql = "SELECT a.id FROM wp_credit a
                            INNER JOIN import_credit_future_stay b ON
                            b.Deposit_year=a.deposit_year AND
                            b.resort_name=a.resort_name AND
                            b.unit_type=a.unit_type AND
                            b.Member_Name=a.owner_id
                            WHERE b.ID=".$_POST['depositID'];
                $deposit = $wpdb->get_var($sql);
                if(empty($deposit))
                {
                    $data['msg'] = [
                        'type'=>'error',
                        'text'=>'Deposit not found!',
                    ];
                }
            }
            
            if(!empty($data['msg']))
            {
                return $data;
            }
            
            if(!isset($row->Check_In_Date) || empty($row->Check_In_Date))
            {
                
                $row->Check_In_Date = $row->check_in_date;
                
            }
            
            $wp_room = [
                'record_id'=>$row->weekId,
                'active_specific_date' => date("Y-m-d 00:00:00", strtotime($row->Rental_Opening_Date)),
                'check_in_date' => date('Y-m-d 00:00:00', strtotime($row->Check_In_Date)),
                'check_out_date' => date('Y-m-d 00:00:00', strtotime($row->Check_In_Date.' +7 days')),
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
                'points' => NULL,
                'note' => '',
                'given_to_partner_id' => NULL,
                'import_id' => '0',
                'active_type' => '0',
                'active_week_month' => '0',
                'create_by' => '5',
                'archived' => '0',
            ];
            
            $sql = "SELECT record_id FROM wp_room WHERE record_id='".$row->weekId."'";
            $week = $wpdb->get_row($sql);
            if(!empty($week))
            {
                $wpdb->update('wp_room', $wp_room, array('record_id'=>$week));
            }
            else
            {
                $wpdb->insert('wp_room', $wp_room);
            }
            
            $cpo = "TAKEN";
            if($row->CPO == 'No')
            {
                $cpo = "NOT TAKEN";
            }
            
            $data = [
                "MemberNumber"=>$row->MemberNumber,
                "MemberName"=>$memberName,
                "GuestName"=>$row->GuestName,
                "Adults"=>$row->Adults,
                "Children"=>$row->Children,
                "UpgradeFee"=>$row->actupgradeFee,
                "CPO"=>$cpo,
                "CPOFee"=>$row->actcpoFee,
                "Paid"=>$row->Paid,
                "Balance"=>"0",
                "ResortID"=>$daeResortID,
                "ResortName"=>$row->Resort_Name,
                "room_type"=>$row->Unit_Type,
                "WeekType"=>$row->WeekTransactionType,
                "sleeps"=>$sleeps,
                "bedrooms"=>$beds,
                "Size"=>$row->Unit_Type,
                "noNights"=>"7",
                "checkIn"=>date('Y-m-d', strtotime($row->Check_In_Date)),
                "processedBy"=>5,
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
                'cartID' => $userID.'-'.$row->weekId,
                'sessionID' => '',
                'userID' => $userID,
                'resortID' => $daeResortID,
                'weekId' => $row->weekId,
                'check_in_date' => date('Y-m-d', strtotime($row->Check_In_Date)),
                'datetime' => date('Y-m-d', strtotime($row->transaction_date)),
                'depositID' => NULL,
                'paymentGatewayID' => '',
                'transactionRequestId' => NULL,
                'transactionData' => '',
                'sfid' => '0',
                'sfData' => '',
                'data' => json_encode($data),
            ];
            
            if(isset($deposit))
            {
                $data['creditweekid'] = $deposit;
                $wp_gpxTransactions['depositID'] = $deposit;
                $wp_gpxTransactions['data'] = json_encode($data);
            }
            
            $transactionID = '';
            $sql = "SELECT id FROM wp_gpxTransactions WHERE weekId='".$row->weekId."' AND userID='".$userID."'";
            $et = $wpdb->get_var($sql);
            if(!empty($et))
            {
                $wpdb->update('wp_gpxTransactions', $wp_gpxTransactions, array('id'=>$et));
                $transactionID = $et;
                if(get_current_user_id() == 5)
                {
                    echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                    echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                }
            }
            else
            {
                $sql = "SELECT id FROM wp_gpxTransactions WHERE weekId='".$row->weekId."'";
                $enut = $wpdb->get_var($sql);
                if(empty($enut))
                {
                    $wpdb->insert('wp_gpxTransactions', $wp_gpxTransactions);
                    $transactionID = $wpdb->insert_id;
                }
                else
                {
                    if($_POST['overwrite'] == 'Yes')
                    {
                        $wpdb->update('wp_gpxTransactions', $wp_gpxTransactions, array('id'=>$enut));
                    }
                    else
                    {
                        $data['msg'] = [
                            'type'=>'error',
                            'text'=>'Transaction exists with a different owner!',
                        ];
                    }
                }
            }
            if(isset($transactionID) && !empty($transactionID))
            {
                $d = $gpx->transactiontosf($transactionID);
            }
            foreach($vars as $var)
            {
                unset($data[$var]);
            }
            $data['msg'] = [
                'type'=>'success',
                'text'=>'Transaction updated!',
            ];
        }
        
        return $data;
    }
    
    public function users()
    {
        $data = array();
        
        return $data;
    }
    public function userview()
    {
        $data = array();
        
        return $data;
    }
    public function useredit($id='')
    {

        global $wpdb;
        if(isset($_POST['Email']))
        {
            $redirect = $_POST['returnurl'];
            unset($_POST['returnurl']);
           
            $gpxOwner = [
                'SPI_Email__c'=>$_POST['Email'],
            ];
            $wpdb->update('wp_GPR_Owner_ID__c', $gpxOwner, array('user_id'=>$id));
            
            $wptodae = array(
                'first_name'=>'FirstName1',
                'last_name'=>'LastName1',
                'user_email'=>'Email',
                'Mobile1'=>'Mobile',
            );
            
//             $mainData = array(
//                 'ID'=>$id,
//                 'user_email'=>$_POST['user_email'],
//             );
//             wp_update_user($mainData);
            
            foreach($wptodae as $wdKey=>$wdValue)
            {
                $_POST[$wdKey] = str_replace(" &", ",", $_POST[$wdKey]);
//                 $_POST[$wdValue] = $_POST[$wdKey];
            }
            
            foreach($_POST as $key=>$value)
            {
                if($key == 'OwnershipWeekType')
                    $value = json_encode($_POST[$key]);
                    //if(!empty($value))
                    update_user_meta($id, $key, $value);
            }
           
            
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $id ) );
            require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
            $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
            if(isset($usermeta->DAEMemberNo))
                $update = $gpx->DAEUpdateMemberDetails($usermeta->DAEMemberNo, $_POST);
                echo '<script type="text/javascript">window.location.href="'.$redirect.'"</script>';
                
        }
        
        
        $data = array('user'=>get_userdata($id));
        
//         $sql = "SELECT *  FROM `wp_GPR_Owner_ID__c` WHERE `user_id`=".$id;
//         $data['umap'] = $wpdb->get_row($sql, 'ARRAY_A');
        
        
        $sql = "SELECT *  FROM `wp_GPR_Owner_ID__c` WHERE user_id IN 
(SELECT userID FROM wp_owner_interval a WHERE a.Contract_Status__c = 'Active' AND
 a.ownerID IN 
                    (SELECT DISTINCT gpr_oid 
                        FROM wp_mapuser2oid 
                        WHERE gpx_user_id IN 
                            (SELECT DISTINCT gpx_user_id 
                            FROM wp_mapuser2oid 
                            WHERE gpx_user_id='".$id."'))) AND user_id=".$id;
        $data['umap'] = $wpdb->get_row($sql, 'ARRAY_A');
        
        return $data;
    }
    public function useradd()
    {
        $data = array();
        
        return $data;
    }
    public function usermassdelete()
    {
        global $wpdb;
        
        $data = array();
        
        if(isset($_POST['emsnums']) && !empty($_POST['emsnums']))
        {
            $emss = explode(",", $_POST['emsnums']);
            $i = 0;
            foreach($emss as $ems)
            {
                $num = str_replace(" ", "", $ems);
                
                $wheres = [
                    "user_login='U".$num."'",
                    "user_login='U ".$num."'",
                    "user_login='".$num."'",
                    "user_nicename='U".$num."'",
                    "user_nicename='U ".$num."'",
                    "user_nicename='".$num."'",
                ];
                $where = implode(" OR ", $wheres);
                $sql = "SELECT ID FROM wp_users WHERE $where";
                $var = $wpdb->get_var($sql);
                
                if(empty($var))
                {
                    $sql = "SELECT user_id FROM wp_usermeta WHERE meta_key='DAEMemberNo' and meta_value='".$num."'";
                    $var = $wpdb->get_var($sql);
                }
                
                if(!empty($var))
                {
                    $u = new WP_User( $var );
                    
                    // Remove role
                    $u->remove_role( 'gpx_member' );
                    
                    // Add role
                    $u->add_role( 'gpx_member_-_expired' );
                    $i++;
                }
                else
                {
                    $data['notFound'][] = $num;
                }
                
            }
            $data['updated'] = $i;
        }
        return $data;
    }
    public function userswitch()
    {
        $data = array();
        
        return $data;
    }
    public function usersplit()
    {
        global $wpdb;
        
        $data = array();
        
        if(!empty($_POST['owner_id']))
        {
            if(!empty($_POST['vestID']))
            {
                $originalOwnerID = $_POST['owner_id'];
                $newVestID = $_POST['vestID'];
                
                $data['ownerIDs'] = $wpdb->update('wp_GPR_Owner_ID__c', array('user_id'=>$newVestID), array('Name'=>$originalOwnerID));
                $data['mapIDs'] = $wpdb->update('wp_mapuser2oid', array('gpx_user_id'=>$newVestID), array('gpr_oid'=>$originalOwnerID));
                $data['intervalIDs'] = $wpdb->update('wp_owner_interval', array('userID'=>$newVestID), array('ownerID'=>$originalOwnerID));
            
                $data['msgType'] = 'success';
            }
            elseif(!empty($_POST['email']))
            {
                $originalOwnerID = $_POST['owner_id'];

				$user = get_user_by( 'email', $_POST['email'] );

				if(!empty($user))
				{
    				$userId = $user->ID;
    
    				update_user_meta($userId, 'GPX_Member_VEST__c', '');
				}

				$ownerAdd = function_GPX_Owner($_POST['owner_id'], true);

				$data['msgType'] = 'success';
            }
            else 
            {
                $originalOwnerID = $_POST['owner_id'];
                
                $user = reset(
                     get_users(
                          array(
                           'meta_key' => 'owner_id',
                           'meta_value' => $originalOwnerID,
                           'number' => 1,
                           'count_total' => false
                          )
                     )
                );

				update_user_meta($user->ID, 'GPX_Member_VEST__c', '');

                $wpdb->delete('wp_GPR_Owner_ID__c', array('Name'=>$originalOwnerID));
                $wpdb->delete('wp_mapuser2oid', array('gpr_oid'=>$originalOwnerID));
                $wpdb->delete('wp_owner_interval', array('ownerID'=>$originalOwnerID));
                
                $ownerAdd = function_GPX_Owner($_POST['owner_id'], true);
                
                $data['msgType'] = 'success';
            }
        }
        else
        {
            $data['vestID'] = $_POST['vestID'];
//             $data['email'] = $_POST['email'];
            
            $data['msgType'] = 'error';
            
            $data['msg'] = 'Owner ID is required.';
            
        }
        
        return $data;
    }
    public function usermapping()
    {
        $data = array();

        return $data;
    }
    public function userreassign()
    {
        $data = array();
        
        if(isset($_POST['"legacyID"']) && check_admin_referer('gpx_admin', 'gpx_reassign'))
        {
            $required = [
                '"legacyID"'=>"Legacy ID",
                'vestID'=>"VEST ID",
            ];
            
            foreach($required as $req=>$val)
            {
                if(empty($_POST[$req]))
                {
                    $data['msg']['type'] = 'error';
                    $data['msg']['text'] = $val." is required!";
                }
                else
                {
                    $where[$req] = $req." = ".$_POST[$req];
                }
            }
            
            if(!isset($data['msg']))
            {
                $vars = $required;
                $vars['resortID'] = 'Resort ID';
                $vars['depositID'] = 'Deposit ID';
            }
            
            foreach($vars as $key=>$var)
            {
                $data[$key] = $_POST[$key];
            }
            
            if(!empty($data['msg']))
            {
                return $data;
            }
            
            //now just update the transactions and deposits
            $wpdb->update('wp_credit', array('owner_id'=>$_POST['vestID']), array('owner_id'=>$_POST['legacyID']));
            
            $sql = "SELECT id, data FROM wp_gpxTransactions WHERE userID='".$_POST['legacyID']."'";
            $rows = $wpdb->get_results($sql);
            
            foreach($rows as $row)
            {
                $id = $row->id;
                $tData = json_decode($row->data, true);
                
                $tData['MemberNumber'] = $_POST['vestID'];
                $wpdb->update('wp_gpxTransactions', array('userID'=>$_POST['vestID'], 'data'=>json_encode($tData)), array('id'=>$id));
            }
            
            $data['msg'] = [
                'type'=>'success',
                'text'=>'Owner updated!',
            ];
            
        }
        return $data;
    }
    public function reportsearches()
    {
        $data = array();
        
        return $data;
    }
    public function reportownercreditcoupon()
    {
        global $wpdb;
        $return = array();
        $upload_dir = wp_upload_dir();
        $fileLoc = '/var/www/reports/reportownercreditcoupon.csv';
        $file = fopen($fileLoc, 'w');
        
        $select = [
            'a.id as CouponID',
            'a.name as CouponName',
            'a.singleuse as SingleUse',
            'b.activity as Activity',
            'b.amount as Amount',
            'b.userID as AddedByID',
            'b.datetime as AddedOn',
            'c.ownerID as OwnerDatabaseID',
            'b.xref as TransactionID',
        ];
        $sql = "SELECT  ".implode(",", $select)." FROM wp_gpxOwnerCreditCoupon a
                INNER JOIN wp_gpxOwnerCreditCoupon_activity b on b.couponID=a.id
                INNER JOIN wp_gpxOwnerCreditCoupon_owner c ON c.couponID=a.id
                ORDER BY b.couponID";
        $coupons = $wpdb->get_results($sql);
        foreach($coupons as $coupon)
        {
            $extraData = array();
            $transactionID = array();
            if(!empty($coupon->TransactionID) && $coupon->xref < 1000000)
            {
                //get the transaction
                $sql = "SELECT * FROM wp_gpxTransactions WHERE id='".$coupon->TransactionID."'";
                $transaction = $wpdb->get_row($sql);
                $extraData = (array) json_decode($transaction->data);
                $transactionID = [
                    'TransactionID' => $coupon->TransactionID,
                ];
            }
            else
            {
                $firstName = get_user_meta($coupon->OwnerDatabaseID, 'first_name', true);
                $lastName = get_user_meta($coupon->OwnerDatabaseID, 'last_name', true);
                $memberNo = get_user_meta($coupon->OwnerDatabaseID, 'DAEMemberNo', true);
                $extraData = [
                    'MemberNumber' => $memberNo,
                    'MemberName' => $firstName." ".$lastName,
                ];
            }
            $couponArray = (array) $coupon;
            if($couponArray['Activity'] == 'transaction')
            {
                $couponArray['Amount'] = $couponArray['Amount']*-1;
            }
            $addedbyfirstName = get_user_meta($coupon->AddedByID, 'first_name', true);
            $addedbylastName = get_user_meta($coupon->AddedByID, 'last_name', true);
            $couponAddedBy = [
                'AddedByName' => $addedbyfirstName." ".$addedbylastName,
            ];
            unset($couponArray['OwnerDatabaseID']);
            unset($couponArray['TransactionID']);
            $couponData[] = array_merge($couponArray, $couponAddedBy, $transactionID, $extraData);
        }
        foreach($couponData as $cdk=>$cdv)
        {
            foreach($cdv as $key=>$value)
            {
                $heads[$key] = $key;
                $csvs[$cdk][$key] = $value;
            }
        }
        $i = 0;
        foreach($heads as $head)
        {
            $csvData[$i][] = $head;
        }
        foreach($csvs as $csv)
        {
            $i++;
            foreach($heads as $head)
            {
                $csvData[$i][] = $csv[$head];
            }
        }
        foreach($csvData as $line)
        {
            fputcsv($file,$line);
        }
        fclose($file);
        return $fileLoc;
        
    }
    
    public function reportretarget()
    {
        $return = array();
        
        if(isset($_POST['startDate']))
        {
            global $wpdb;
            
            $upload_dir = wp_upload_dir();
            $fileLoc = '/var/www/reports/retarget.csv';
            $file = fopen($fileLoc, 'w');
            
            $heads = array();
            $values = array();
            
            $start = $_POST['startDate'];
            $end = $_POST['endDate'];
            $bookingComplete = $_POST['bookingComplete'];
            
            $startDate = date('Y-m-d 00:00:00', strtotime($_POST['startDate']));
            
            $endDate = '2025-01-01 00:00:00';
            if(isset($_POST['endDate']) && !empty($_POST['endDate']))
                $endDate = date('Y-m-d 23:59:59', strtotime($_POST['endDate']));
                
                $n = 0;
                $heads = array('sessionID','cartID', 'user_type', 'daeMemberNo','guest_name','email','action','id','price','ResortName','WeekType','bedrooms','weekId','checkIn','refDomain','currentPage','search_location','search_month','search_year', 'timestamp');
                if($_POST['bookingComplete'] == 'Yes')
                {
                    $sql = "SELECT * FROM wp_gpxTransactions WHERE datetime BETWEEN '".$startDate."' AND '".$endDate."'";
                    $transactions = $wpdb->get_results($sql);
                    foreach($transactions as $transaction)
                    {
                        $userID = $transaction->userID;
                        $sql = "SELECT * FROM wp_gpxMemberSearch WHERE userID='".$userID."'";
                        $rows = $wpdb->get_results($sql);
                        foreach($rows as $row)
                        {
                            $data = json_decode($row->data);
                            foreach($data as $sKey=>$sValue)
                            {
                                $user = get_userdata($userID);
                                $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $userID ) );
                                $name = $usermeta->first_name." ".$usermeta->last_name;
                                $name = str_replace(",", "", $name);
                                $splitKey = explode('-', $sKey);
                                //                             echo '<pre>'.print_r($splitKey, true).'</pre>';
                                //                             echo '<pre>'.print_r($sValue, true).'</pre>';
                                if($splitKey[0] == 'select')
                                {
                                    $values[$n]['sessionID'] = $row->sessionID;
                                    $values[$n]['cartID'] = $row->cartID;
                                    $values[$n]['action'] = 'select';
                                    $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                                    $values[$n]['guest_name'] = html_entity_decode($name);
                                    $values[$n]['email'] = $user->user_email;
                                    $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                                    $values[$n]['refDomain'] = $sValue->refDomain;
                                    $values[$n]['currentPage'] = $sValue->currentPage;
                                    $values[$n]['price'] =  preg_replace("/[^0-9,.]/", "", $sValue->price);
                                    $values[$n]['WeekPrice'] = $sValue->WeekPrice;
                                    $values[$n]['id'] = $sValue->property->id;
                                    $values[$n]['ResortName'] = $sValue->property->ResortName;
                                    $values[$n]['WeekType'] = $sValue->property->WeekType;
                                    $values[$n]['bedrooms'] = $sValue->property->bedrooms;
                                    $values[$n]['weekId'] = $sValue->property->weekId;
                                    $values[$n]['checkIn'] = date("m/d/Y", strtotime($sValue->property->checkIn));
                                    $values[$n]['user_type'] = $sValue->user_type;
                                }
                                if($splitKey[0] == 'view')
                                {
                                    $values[$n]['sessionID'] = $row->sessionID;
                                    $values[$n]['cartID'] = $row->cartID;
                                    $values[$n]['action'] = 'view';
                                    $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                                    $values[$n]['guest_name'] = html_entity_decode($name);
                                    $values[$n]['email'] = $user->user_email;
                                    $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                                    $values[$n]['refDomain'] = $sValue->refDomain;
                                    $values[$n]['currentPage'] = $sValue->currentPage;
                                    $values[$n]['WeekType'] = $sValue->week_type;
                                    $values[$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->price);
                                    $values[$n]['id'] = $sValue->id;
                                    $values[$n]['ResortName'] = $sValue->name;
                                    $values[$n]['checkIn'] = date("m/d/Y", strtotime($sValue->checkIn));
                                    $values[$n]['bedrooms'] = $sValue->beds;
                                    $values[$n]['search_location'] = $sValue->search_location;
                                    $values[$n]['search_month'] = $sValue->search_month;
                                    $values[$n]['search_year'] = $sValue->search_year;
                                    $values[$n]['user_type'] = $sValue->user_type;
                                }
                                if($splitKey[0] == 'bookattempt')
                                {
                                    $values[$n]['sessionID'] = $row->sessionID;
                                    $values[$n]['cartID'] = $row->cartID;
                                    $values[$n]['action'] = 'bookattempt';
                                    $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                                    $values[$n]['guest_name'] = html_entity_decode($name);
                                    $values[$n]['email'] = $user->user_email;
                                    $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                                    $values[$n]['WeekType'] = $sValue->Booking->WeekType;
                                    $values[$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->Booking->AmountPaid);
                                    $values[$n]['id'] = $sValue->$splitKey[1];
                                    $values[$n]['weekId'] = $sValue->Booking->WeekID;
                                    $values[$n]['user_type'] = $sValue->user_agent;
                                }
                                if($splitKey[0] == 'resort')
                                {
                                    $values[$n]['sessionID'] = $row->sessionID;
                                    $values[$n]['cartID'] = $row->cartID;
                                    $values[$n]['action'] = 'resortview';
                                    $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                                    $values[$n]['guest_name'] = html_entity_decode($name);
                                    $values[$n]['email'] = $user->user_email;
                                    $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                                    $values[$n]['ResortName'] = $sValue->ResortName;
                                    $values[$n]['id'] = $sValue->id;
                                    $values[$n]['search_location'] = $sValue->search_location;
                                    $values[$n]['search_month'] = $sValue->search_month;
                                    $values[$n]['search_year'] = $sValue->search_year;
                                    $values[$n]['user_type'] = $sValue->user_type;
                                }
                                $n++;
                            }
                        }
                    };
                }
                else
                {
                    $sql = "SELECT * FROM wp_gpxMemberSearch WHERE datetime BETWEEN '".$startDate."' AND '".$endDate."'";
                    $rows = $wpdb->get_results($sql);
                    foreach($rows as $row)
                    {
                        $userID = $row->userID;
                        $data = json_decode($row->data);
                        foreach($data as $sKey=>$sValue)
                        {
                            $transactionID = '';
                            $user = get_userdata($userID);
                            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $userID ) );
                            $name = $usermeta->first_name." ".$usermeta->last_name;
                            $name = str_replace(",", "", $name);
                            $splitKey = explode('-', $sKey);
                            
                            if($splitKey[0] == 'bookattempt')
                            {
                                $sql = "SELECT id FROM wp_gpxTransactions WHERE weekId='".$sValue->Booking->WeekID."'";
                                $transactionID = $wpdb->get_row($sql);
                                if(!empty($transactionID))
                                    continue;
                                    $values[$n]['sessionID'] = $row->sessionID;
                                    $values[$n]['cartID'] = $row->cartID;
                                    $values[$n]['action'] = 'bookattempt';
                                    $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                                    $values[$n]['guest_name'] = html_entity_decode($name);
                                    $values[$n]['email'] = $user->user_email;
                                    $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                                    $values[$n]['WeekType'] = $sValue->WeekType;
                                    $values[$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->paid);
                                    $values[$n]['id'] = $sValue->$splitKey[1];
                                    $values[$n]['weekId'] = $sValue->WeekID;
                                    $values[$n]['user_type'] = $sValue->user_agent;
                            }
                            if($splitKey[0] == 'select')
                            {
                                $values[$n]['sessionID'] = $row->sessionID;
                                $values[$n]['cartID'] = $row->cartID;
                                $values[$n]['action'] = 'select';
                                $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                                $values[$n]['guest_name'] = html_entity_decode($name);
                                $values[$n]['email'] = $user->user_email;
                                $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                                $values[$n]['refDomain'] = $sValue->refDomain;
                                $values[$n]['currentPage'] = $sValue->currentPage;
                                $values[$n]['price'] =  preg_replace("/[^0-9,.]/", "", $sValue->price);
                                $values[$n]['WeekPrice'] = $sValue->WeekPrice;
                                $values[$n]['id'] = $sValue->property->id;
                                $values[$n]['ResortName'] = $sValue->property->ResortName;
                                $values[$n]['WeekType'] = $sValue->property->WeekType;
                                $values[$n]['bedrooms'] = $sValue->property->bedrooms;
                                $values[$n]['weekId'] = $sValue->property->weekId;
                                $values[$n]['checkIn'] = date("m/d/Y", strtotime($sValue->property->checkIn));
                                $values[$n]['user_type'] = $sValue->user_type;
                            }
                            if($splitKey[0] == 'view')
                            {
                                $values[$n]['sessionID'] = $row->sessionID;
                                $values[$n]['cartID'] = $row->cartID;
                                $values[$n]['action'] = 'view';
                                $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                                $values[$n]['guest_name'] = html_entity_decode($name);
                                $values[$n]['email'] = $user->user_email;
                                $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                                $values[$n]['refDomain'] = $sValue->refDomain;
                                $values[$n]['currentPage'] = $sValue->currentPage;
                                $values[$n]['WeekType'] = $sValue->week_type;
                                $values[$n]['price'] =  preg_replace("/[^0-9,.]/", "", $sValue->price);
                                $values[$n]['id'] = $sValue->id;
                                $values[$n]['ResortName'] = $sValue->name;
                                $values[$n]['checkIn'] = date("m/d/Y", strtotime($sValue->checkIn));
                                $values[$n]['bedrooms'] = $sValue->beds;
                                $values[$n]['search_location'] = $sValue->search_location;
                                $values[$n]['search_month'] = $sValue->search_month;
                                $values[$n]['search_year'] = $sValue->search_year;
                                $values[$n]['user_type'] = $sValue->user_type;
                            }
                            if($splitKey[0] == 'resort')
                            {
                                $values[$n]['sessionID'] = $row->sessionID;
                                $values[$n]['cartID'] = $row->cartID;
                                $values[$n]['action'] = 'resortview';
                                $values[$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                                $values[$n]['guest_name'] = html_entity_decode($name);
                                $values[$n]['email'] = $user->user_email;
                                $values[$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                                $values[$n]['ResortName'] = $sValue->ResortName;
                                $values[$n]['id'] = $sValue->id;
                                $values[$n]['search_location'] = $sValue->search_location;
                                $values[$n]['search_month'] = $sValue->search_month;
                                $values[$n]['search_year'] = $sValue->search_year;
                                $values[$n]['user_type'] = $sValue->user_type;
                            }
                            $n++;
                        }
                    }
                }
                $list = array();
                $list[] = implode(',', $heads);
                $i = 1;
                foreach($values as $value)
                {
                    $value = str_replace(",", "", $value);
                    foreach($heads as $head)
                    {
                        $ordered[$i][] = $value[$head];
                    }
                    $list[$i] = implode(',', $ordered[$i]);
                    $i++;
                }
                foreach($list as $line)
                {
                    fputcsv($file,explode(",", $line));
                    
                }
                return $fileLoc;
                fclose($file);
        }
        
        return $return;
    }
    public function reportscsv()
    {
        $data = array();
        
        return $data;
    }
    public function reportcustomrequest()
    {
        $data = array();
        
        $data['totals'] = $this->return_gpx_customrequeststats();
        
        return $data;
    }
    public function reportemailmembersearch()
    {
        if(!current_user_can('administrator'))
            exit;
            if(isset($_POST['msEmail']))
            {
                update_option('gpx_msemailTo', $_POST['msEmailTo']);
                update_option('gpx_msemail', $_POST['msEmail']);
                update_option('gpx_msemailName', $_POST['msEmailName']);
                update_option('gpx_msemailSubject', $_POST['msEmailSubject']);
                update_option('gpx_msemailMessage', $_POST['msEmailMessage']);
                update_option('gpx_msemailDays', $_POST['msEmailDays']);
            }
            
            $data = array();
            $data['msemailTo'] = get_option('gpx_msemailTo');
            $data['msemail'] = get_option('gpx_msemail');
            $data['msemailName'] = get_option('gpx_msemailName');
            $data['msemailSubject'] = get_option('gpx_msemailSubject');
            $data['msemailMessage'] = get_option('gpx_msemailMessage');
            $data['msemailDays'] = get_option('gpx_msemailDays');
            
            return $data;
    }
    public function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    
    /*
     * report writer
     * used to both display the form as well as the table
     */
    public function reportwriter($id='', $cron='')
    {
        global $wpdb;
        global $wp_roles;
        
        $data = array();
  
        $data['available_roles'] = $wp_roles->roles;
        
        $skip_roles = [
            'editor',
            'author',
            'contributor',
            'subscriber',
            'wpsl_store_locator_manager',
            'gpx_member',
            'wpseo_manager',
            'wpseo_editor',
        ];
        
        foreach($skip_roles as $skip)
        {
            unset($data['available_roles'][$skip]);
        }
        
        /*
         * editid is for editing an ID
         */
        if(isset($_REQUEST['editid']) && !empty($_REQUEST['editid']))
        {
            $sql = "SELECT * FROM wp_gpx_report_writer WHERE id='".$_REQUEST['editid']."'";
            $data['editreport'] = $wpdb->get_row($sql);
        }
        
        /*
         * when an $id is set  
         */
        if(!empty($id))
        {
			$groupBy = [];
            $data['reportid'] = $id;
            
            /*
             * 'rw' is an array that is used to identify how to handle each item that can be selected
             */
            $data['rw'] = $this->gpx_report_writer('tables');
            
            $sql = "SELECT * FROM wp_gpx_report_writer WHERE id='".$id."'";
            $row = $wpdb->get_row($sql);
            
            $data['reportHeadName'] = $row->name;
            
            $tds = json_decode($row->data);
            
            /*
             * get the details from the database and then build the query and tables
             */
            foreach($tds as $td)
            {
                //does this table have a groupBy
                if($data['rw'][$extracted[0]]['groupBy'])
                {
                    $groupBy[] = $data['rw'][$extracted[0]]['groupBy'];
                }

                $data['th'][$td] = $td;
                $extracted = explode('.', $td);

                //do we have an "as" overwrite?
                if(isset($data['rw'][$extracted[0]]['fields'][$extracted[1]]['as']))
                {
                    $queryAs[$extracted[1]] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['as'];
                }
              
                //is this a joined table?
                $type_query = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['type'];
                if(isset($type_query) && ($type_query == 'join_json' || $type_query == 'join' || $type_query == 'join_case' || $type_query == 'join_usermeta'))
                {
                    foreach( $data['rw'][$extracted[0]]['fields'][$extracted[1]]['on'] as $jk=>$joins)
                    {
                        /*
                         * $qj = query joins
                         */
                        $qj[$joins] =  $joins;
                    }

                    /*
                     * $case = cases
                     */
                    $case[$td] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['case'];
                    $case_special[$td] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['case_special'];
                    $case_special_column[$td] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['column_special'];
//                     $data['fields'] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['column'];
//                     $data['case'][$extracted[0]][$extracted[1]] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['case'];
                    $tables[$extracted[0]][$extracted[1]] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['column'];
                    if(isset($data['rw'][$extracted[0]]['fields'][$extracted[1]]['column_override']))
                    {
                        $tables[$extracted[0]][$extracted[1]] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['column_override'];
                    }
                    $queryData[$extracted[0]][$extracted[1]] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['column'];
//                     if($data['rw'][$extracted[0]]['fields'][$extracted[1]]['column'] == 'data.WeekType')
//                     {
//                         $queryData[$extracted[0]][$extracted[1]] = 'data';
//                     }
                }
                elseif($data['rw'][$extracted[0]]['fields'][$extracted[2]]['type'] == 'post_merge')
                {
                    $tables[$extracted[0]][$data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref']] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['xref'];
                                        
                    foreach( $data['rw'][$extracted[0]]['fields'][$extracted[1]]['on'] as $jk=>$joins)
                    {
                        /*
                         * $qj = query joins
                         */
                        $qj[$joins] =  $joins;
                    }
                    
                }
                elseif($data['rw'][$extracted[0]]['fields'][$extracted[2]]['type'] == 'agentname')
                {
                    $tables[$extracted[0]][$data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref']] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['xref'];
                    $queryData[$extracted[0]][$data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref']] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['xref'];
                    $data['agentname'][$extracted[1]][$extracted[1]] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['from'];
                }
                elseif($data['rw'][$extracted[0]]['fields'][$extracted[1]]['type'] == 'usermeta')
                {
                    foreach( $data['rw'][$extracted[0]]['fields'][$extracted[1]]['on'] as $jk=>$joins)
                    {
                        /*
                         * $qj = query joins
                         */
                        $qj[$joins] =  $joins;
                    }
                    $tables[$extracted[0]][$extracted[1]] = $extracted[1];
                    $queryData[$extracted[0]][$extracted[1]] = $extracted[0].".".$extracted[1];
                    $data['usermeta'][$extracted[1]] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['column'];
                    $data['usermetaxref'][$extracted[1]] = $data['rw'][$extracted[0]]['fields'][$extracted[1]]['xref'];
                    $data['usermetakey'][$extracted[1]] = $extracted[0].".".$extracted[1].".".$data['rw'][$extracted[0]]['fields'][$extracted[1]]['key'];
                }
                elseif($data['rw'][$extracted[0]]['fields'][$extracted[2]]['type'] == 'usermeta')
                {
                    foreach( $data['rw'][$extracted[0]]['fields'][$extracted[2]]['on'] as $jk=>$joins)
                    {
                        /*
                         * $qj = query joins
                         */
                        $qj[$joins] =  $joins;
                    }
                    
                    
                    $tables[$extracted[0]][$data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref']] = $data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref'];
                    $queryData[$extracted[0]][$data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref']] = $data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref'];
                    $data['usermeta'][$extracted[1]][$extracted[2]] = $data['rw'][$extracted[0]]['fields'][$extracted[2]]['column'];
                    $data['usermetaxref'][$extracted[1]][$extracted[2]] = $data['rw'][$extracted[0]]['fields'][$extracted[2]]['xref'];
                    $data['usermetakey'][$extracted[1]][$extracted[2]] = $extracted[0].".".$extracted[1].".".$data['rw'][$extracted[0]]['fields'][$extracted[2]]['key'];
                }
                else 
                {
                    
                    $tables[$extracted[0]][$extracted[1]] = $extracted[1];
                    $queryData[$extracted[0]][$extracted[1]] = $extracted[0].".".$extracted[1];
//                     $data['fields'] = $extracted[1];
                    if(isset($extracted[2]))
                    {
                        $data['subfields'][$extracted[1]][$extracted[2]] = $extracted[2];
                    }
                }
            }//end foreach
// echo '<pre>'.print_r($queryData, true).'</pre>';
            //add the conditions
            $conditions = json_decode($row->conditions);
//             echo '<pre>'.print_r($conditions, true).'</pre>';
            foreach($conditions as $condition)
            {
//                 echo '<pre>'.print_r($condition, true).'</pre>';
                switch($condition->operator)
                {
                    case "equals":
                        $operator = "=";
                        if(empty($condition->conditionValue))
                        {
                            $operator = 'IS';
                            $condition->conditionValue = 'NULL';
                        }
                        else
                        {
                            $dt = date_parse($condition->conditionValue);
                            if($dt['year'] > 0)
                            {
                                $condition->conditionValue = $dt['year']."-".$dt['month']."-".$dt['day'];
                            }
                        }
                    break;
                    case "not_equals":
                        $operator = "!=";
                    break;
                    
                    case "greater":
                        $operator = ">";
                        if($dt = date_parse($condition->conditionValue))
                        {
                            $condition->conditionValue = $dt['year']."-".$dt['month']."-".$dt['day'];
                        }
                    break;
                    
                    case "less":
                        $operator = "<";
                        if($dt = date_parse($condition->conditionValue))
                        {
                            $condition->conditionValue = $dt['year']."-".$dt['month']."-".$dt['day'];
                        }
                    break;
                    
                    case "like":
                        $operator = "LIKE ";
                    break;
                    
                    case 'yesterday':
                        $operator = "BETWEEN ";
                        $condition->conditionValue = date('Y-m-d 00:00:00', strtotime('yesterday'))."' AND '".date('Y-m-d 23:59:59', strtotime('yesterday'));
                    break;
                    
                    case 'today':
                        $operator = "BETWEEN ";
                        $condition->conditionValue = date('Y-m-d 00:00:00')."' AND '".date('Y-m-d 23:59:59');
                    break;
                    
                    case 'this_year':
                        $operator = "BETWEEN";
                        $condition->conditionValue = date('Y-01-01 00:00:00', strtotime('today'))."' AND '".date('Y-12-t 23:59:59', strtotime('today'));
                        break;
                        
                    case 'last_year':
                        $operator = "BETWEEN";
                        $month_ini = new DateTime("first day of last year");
                        $month_end = new DateTime("last day of last year");
                        $condition->conditionValue = $month_ini->format('Y-m-d 00:00:00')."' AND '".$month_end->format('Y-m-d 23:59:59');
                    break; 
                    
                    case 'this_month':
                        $operator = "BETWEEN";
                        $condition->conditionValue = date('Y-m-1 00:00:00', strtotime('today'))."' AND '".date('Y-m-t 23:59:59', strtotime('today'));
                        break;
                        
                    case 'last_month':
                        $operator = "BETWEEN";
                        $month_ini = new DateTime("first day of last month");
                        $month_end = new DateTime("last day of last month");
                        $condition->conditionValue = $month_ini->format('Y-m-d 00:00:00')."' AND '".$month_end->format('Y-m-d 23:59:59');
                    break; 
                    
                    case 'this_week':
                        $operator = "BETWEEN";
                        $condition->conditionValue =  date('Y-m-d 00:00:00', strtotime('today 00:00:00'))."' AND '".date('Y-m-d 23:59:59', strtotime('+6 days 23:59:59'));
                    break; 
                    
                    case 'last_week':
                        $operator = "BETWEEN";
                        $condition->conditionValue =  date('Y-m-d 00:00:00', strtotime('-6 days 00:00:00'))."' AND '".date('Y-m-d 23:59:59', strtotime('today 23:59:59'));
                    break; 
                    
                    default:
                        $operator = "=";
                    break;
                }
                $operand = '';
                if(isset($condition->operand))
                {
                    $operand = $condition->operand;
                }
//                 if(isset($operand) && !empty($operand))
//                 {
//                     $wheres[] = $operand." ".$condition->condition." ".$operator." ".$condition->conditionValue;
//                 }
                if($operator == 'IS')
                {
                    $wheres[] = $operand." ".$condition->condition." ".$operator." ".$condition->conditionValue."";
                }
                else
                {
                    $wheres[] = $operand." ".$condition->condition." ".$operator." '".$condition->conditionValue."'";
                }
            	//if this is cancelled date then we also need to only show cancelled transactions
				if($condition->condition == 'wp_gpxTransactions.cancelledDate')
				{
				    if($operator != 'IS')
				    {
				        $wheres['cancelledNotNull'] = " AND wp_gpxTransactions.cancelled IS NOT NULL";
				    }
// 				    else
// 				    {
// 				        $wheres['cancelledNotNull'] = " AND wp_gpxTransactions.cancelled IS NOT NULL";
// 				    }
				}
			}//end foreach conditions
            if(wp_doing_ajax() || !empty($cron))
            {
                $i = 0;
                /*
                 * $ajax = column labels and results
                 */
                $ajax = [];
                
                foreach($queryData as $tk=>$td)
                {
                    foreach($td as $tdk=>$tdv)
                    {
                        $colSelect = $tdv;
                        
                        $qq = explode('|', $tdv);
                        if (count($qq) == 3) {
//                             $tdas[] = $qq[2]." AS '".$qq[1]."'";
                            $td[$tdk] = $qq[1];
                            $colSelect = $qq[2];
                        }
                        
                        $texp = explode('.', $tdv);
                        if(count($texp) == 2)
                        {
                            if($texp[0] == 'data')
                            {
                                $colSelect = $texp[0];
                            }
                            $td[$tdk] = $texp[1];
                        }
                        
                        $as = $td[$tdk];
                        if(isset($queryAs[$tdk]))
                        {
                            $as = $queryAs[$tdk];
                            if(isset($_REQUEST['as']))
                            {
                                echo '<pre>'.print_r($as, true).'</pre>';
                            }
                        }
//                         if($colSelect == 'data.WeekType' || $colSelect == 'wp_room.WeekType')
//                         {
//                             $colSelect = 'data';
//                         }
                        $tdas[] = $colSelect." AS ".$as;
                    }
                    
//                     foreach($td as $tdk=>$tdv)
//                     {
//                         $colSelect = $tdv;
                        
//                         $qq = explode('|', $tdv);
//                         if (count($qq) == 3) {
// //                             $tdas[] = $qq[2]." AS '".$qq[1]."'";
//                             $td[$tdk] = $qq[1];
//                             $colSelect = $qq[2];
//                         } else {
//                             $texp = explode('.', $tdv);
//                             if(count($texp) == 2)
//                             {
//                                 if ($texp[0] == 'data') {

// //                                     $tdas[] = $texp[0]." AS '".$texp[1]."'";
//                                     $colSelect = $texp[0];
//                                     $as = $texp[1];
//                                 } else {
//                                     $td[$tdk] = $tdv;
// //                                     $tdas[] = $tdv." AS '".$tdv."'";
//                                 }
//                             } else {
// //                                 $tdas[] = $tdv." AS '".$td[$tdk]."'";
                                
//                             }
//                         }
                         
//                         $as = $td[$tdk];
                        
//                         if(isset($queryAs[$tdk]))
//                         {
//                             $as = $queryAs[$tdk];
//                             if(isset($_REQUEST['as']))
//                             {
//                                 echo '<pre>'.print_r($as, true).'</pre>';
//                             }
//                         }
                        
//                         $tdas[] = $colSelect." AS ".$as;
//                     }
                    
                    $sql = "SELECT ".implode(", ", $tdas)." FROM ".$tk." ";
                   
                    if(isset($qj))
                    {
                        $sql .= " LEFT OUTER JOIN ";
                        $sql .= implode(" LEFT OUTER JOIN ", $qj);
                    }
                    
                    if(isset($wheres))
                    {
                        $sql .= " WHERE ".implode(" ", $wheres);
                    }
                    if($tk == 'wp_room' || $tk == 'wp_gpxTransactions')
                    {
                        if(isset($wheres))
                        {
                            $sql .= " AND wp_room.archived=0";
                        }
                        else 
                        {
                            $sql .= "WHERE wp_room.archived=0";
                        }
                    }

                    if(!empty($groupBy))
                    {
                        $sql .= ' GROUP BY '.implode(", ", $groupBy);
                    }

                    //                     echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                    if(isset($_REQUEST['sql_exit']))
                    {
                        echo '<pre>'.print_r($sql, true).'</pre>';
                        exit;
                    }
                    $results = $wpdb->get_results($sql);
                    if(isset($_REQUEST['report_debug']))
                    {
                        echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                        echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                        echo '<pre>'.print_r($results, true).'</pre>';
                    }
                    if(isset($_REQUEST['sql_exit']))
                    {
                        exit;
                    }
                    foreach($results as $result)
                    {
                        foreach($td as $tdK=>$t)
                        {
                            $ajax[$i][$tk.".".$t] = $result->$t;
                            
                                if($tdK == 'source_partner_name')
                                {
                                    $ajax[$i]['wp_room.source_partner_name'] = $result->source_partner_name;
                                }
								elseif(isset($data['subfields'][$t]))//is this a regular field or is it json?
							    {
                                if(isset($data['rw'][$tk][$t]['type']) && $data['rw'][$tk][$t]['type'] == 'join')
                                {
                                    $co = $data['rw'][$tk][$t]['column'];
                                    $ajax[$i][$tk.".".$t] = $result->$co;
                                    if(is_array($result->$co) || is_object($result->$co))
                                    {
                                        $ajax[$i][$tk.".".$t] = implode(", ", (array) $result->$co);
                                    }
                                }
                                //this is json the result is a json
                                elseif(!isset($json[$t]))
                                {
                                    $json[$t] = json_decode($result->$t);
                                }
                                foreach($data['subfields'][$t] as $st)
                                {
                                    
                                    if($this->validateDate($json[$t]->$st))
                                    {
                                        $json[$t]->$st = date('m/d/Y', strtotime($json[$t]->$st));
                                    }
                                    if($this->validateDate($json[$t]->$st, 'Y-m-d'))
                                    {
                                        $json[$t]->$st = date('m/d/Y', strtotime($json[$t]->$st));
                                    }
                                    
                                    $ajax[$i][$tk.".".$t.".".$st] = $json[$t]->$st;
                                    
                                    if($t == 'cancelledData')
                                    {
                                        $isCancelled = true;
                                        $ti = 0;
                                        $cdMark = $i;
                                        $amountSum[$cdMark][] = 0;
                                        $totJsonT = count( (array) $json[$t]);
                                       
										foreach($json[$t] as $jsnt)
										{
// 											$allValues[$i][$tk.".".$t.".".$st][] = $jsnt->$st;
										    
										    if($this->validateDate($jsnt->$st))
										    {
										        $jsnt->$st = date('m/d/Y', strtotime($jsnt->$st));
										    }
										    if($this->validateDate($json[$t]->$st, 'Y-m-d'))
										    {
										        $jsnt->$st = date('m/d/Y', strtotime($jsnt->$st));
										    }
										    
										    $zti = '';
										    if($ti > 0)
										    {
										        if($st == 'amount')
// 										        if(!empty($jsnt->$st))
										        {
    										        $lastAjax = $ajax[$i];
    										        if(isset($lastAjax['wp_gpxTransactions.cancelledDate']))
    										        {
    										            $lastAjax['wp_gpxTransactions.cancelledDate'] = date('m/d/Y', strtotime($lastAjax['wp_gpxTransactions.cancelledDate']));
    										        }
    										        $i++;
    										        $ajax[$i] = $lastAjax;
										        }
										    }
										    $ti++;
										    
										    if(isset($_GET['dup_debug']))
										    {
										        echo '<pre>'.print_r("ti: ".$ti."; toJsonT: ".$totJsonT, true).'</pre>';
										    }
										    
										    if($jsnt->st != 0 && empty($jsnt->$st))
										    {
										        continue;
										    }
										    
										    if($st == 'amount')
										    {
										        $ajax[$i][$tk.".".$t.".amount_sub"] = number_format($jsnt->$st, 2);
										        
										        $showAmount = '';
										        $amountSum[$cdMark][] = $jsnt->$st;
										        if($ti === $totJsonT)
										        {
										            $showAmount = array_sum($amountSum[$cdMark]);
										        }
										        
										        $jsnt->$st = number_format($showAmount, 2);
										    }
										    
// 										    echo '<pre>'.print_r($st, true).'</pre>';
										    
// 										    if($st == 'amount_sub')
// 										    {
// 										        echo '<pre>'.print_r("sub", true).'</pre>';
// 										        $jsnt->$st = $json[$t]->amount;
// 										    }
										    
										    
										    $ajax[$i][$tk.".".$t.".".$st] = $jsnt->$st;
											
										}
//                                     	$ajax[$i][$tk.".".$t.".".$st] =  implode(" & ", $allValues[$i][$tk.".".$t.".".$st]);
                                    }
                                    elseif(is_array($json[$t]->$st) || is_object($json[$t]->$st))
                                    {
                                        $ajax[$i][$tk.".".$t.".".$st] = implode(", ", (array) $json[$t]->$st);
                                    }
                                }
                                
                            }
                            elseif(isset($data['rw'][$tk]['fields'][$tdK]['type']) && $data['rw'][$tk]['fields'][$tdK]['type'] == 'agentname')
                            {
                                $from = $data['agentname'][$tk][$tdK];
                                $expFrom = explode('.', $from);
                                
                                if(count($expFrom) == 1)
                                {
                                    $agentNum = $result->$expFrom[0];
                                }
                                else 
                                {
                                    $agentNum = $json[$expFrom[0]]->$expFrom[1];
                                }
                                
                                $agentName = [];
                                $agentName['first'] = get_user_meta($agentNum,'first_name', true);
                                $agentName['last'] = get_user_meta($agentNum,'last_name', true);
                                
                                $ajax[$i][$tk.".".$t] = implode(" ", $agentName);
                            }
                            elseif(isset($data['rw'][$tk]['fields'][$tdK]['type']) && $data['rw'][$tk]['fields'][$tdK]['type'] == 'case')
                            {
                                $ajax[$i][$tk.".".$t] = $data['rw'][$tk]['fields'][$tdK]['case'][$result->$t];
                                if(is_array($data['rw'][$tk]['fields'][$tdK]['case'][$result->$t]) || is_object($data['rw'][$tk]['fields'][$tdK]['case'][$result->$t]))
                                {
                                    $ajax[$i][$tk.".".$t] = implode(", ", (array) $data['rw'][$tk]['fields'][$tdK]['case'][$result->$t]);
                                }
                            }
                            elseif(isset($data['rw'][$tk]['fields'][$tdK]['type']) && $data['rw'][$tk]['fields'][$tdK]['type'] == 'join_json')
                            {
                                $ajaxJson = json_decode($result->$t);
                                $ajax[$i][$tk.".".$t] = stripslashes($ajaxJson->$t);
                            }
                            elseif(isset($case[$tk.".".$tdK]))
                            {
                                $ajax[$i][$tk.".".$t] = $case[$tk.".".$tdK][$result->$t];
                            }
                            elseif(isset($data['usermeta'][$t]))
                            {
                                //this is usermeta -- get the results 
                                foreach($data['usermeta'][$t] as $ut)
                                {
                                    
//                                     $ak = $tk.'.'.$data['usermetaxref'][$t][$ut].'.'.$data['usermetakey'][$t][$ut];
                                    if(isset($_REQUEST['report_debug2']))
                                    {
//                                         echo '<pre>'.print_r($data['usermetaxref'], true).'</pre>';
                                        echo '<pre>'.print_r($t, true).'</pre>';
                                        echo '<pre>'.print_r($ut, true).'</pre>';
                                        echo '<pre>'.print_r($data, true).'</pre>';
//                                         echo '<pre>'.print_r($ak, true).'</pre>';
                                    }
                                    if($t == 'userID' || $t == 'ownerID')
                                    {
                                        foreach($data['usermeta'][$t] as $umK=>$umT)
                                        {
                                            if($umT == $ut)
                                            {
                                                $akK = $umK;
                                                break;
                                            }
                                        }
                                        $ak = $data['usermetakey'][$t][$akK];
                                        if(isset($_REQUEST['report_debug2']))
                                        {
                                            //                                         echo '<pre>'.print_r($data['usermetaxref'], true).'</pre>';
                                            echo '<pre>'.print_r($ak, true).'</pre>';
                                        }
                                    }
                                    else 
                                    {
                                        switch($ut)
                                        {
                                            case 'first_name':
                                                $ak = 'wp_credit.owner_id.memberFirstName';
                                            break;
                                            case 'last_name':
                                                $ak = 'wp_credit.owner_id.memberLastName';
                                            break;
                                            case 'user_email':
                                                $ak = 'wp_credit.owner_id.memberEmail';
                                            break;
                                            case 'Email':
                                                $ak = 'wp_gpxTransactions.userID.Email';
                                            break;
                                            case 'DayPhone':
                                                $ak = 'wp_gpxTransactions.userID.DayPhone';
                                            break;
                                            case 'address':
                                                $ak = 'wp_gpxTransactions.userID.address';
                                            break;
                                            case 'city':
                                                $ak = 'wp_gpxTransactions.userID.city';
                                            break;
                                            case 'state':
                                                $ak = 'wp_gpxTransactions.userID.state';
                                            break;
                                            case 'country':
                                                $ak = 'wp_gpxTransactions.userID.country';
                                            break;
                                            default:
                                                $ak = '';
                                            break;
                                        }
                                    }
                                    $ajax[$i][$ak] = get_user_meta($result->$t,$ut, true);
                                    if(isset($_REQUEST['report_debug2']))
                                    {
                                        //                                         echo '<pre>'.print_r($data['usermetaxref'], true).'</pre>';
                                        echo '<pre>'.print_r($ajax[$i][$ak], true).'</pre>';
                                        //                                         echo '<pre>'.print_r($ak, true).'</pre>';
                                    }
                                    if(empty( $ajax[$i][$ak] ))
                                    {
                                        //maybe this is the user object
                                        $user_info = get_userdata($result->$t);
                                        $ajax[$i][$ak]  = $user_info->$ut;
                                    }
                                    if(empty($ajax[$i][$ak]))
                                    {
//                                         unset($ajax[$i][$ak]);
                                    }
                                }
                            }
                            elseif(isset($data['usermeta_hold'][$t]))
                            {
                                //this is usermeta -- get the results 
                                $um = [];
                                foreach($data['usermeta_hold'][$t] as $ut)
                                {
                                    $um[] =  get_user_meta($result->$t,$ut, true);
                                }
                                if(!empty($um))
                                {
                                    $ajax[$i][$ak] = impolode(' ', $um);
                                }
                            }                            
                            elseif(isset($case_special[$tk.".".$tdK]))
                            {
                                if($data['rw'][$tk]['fields'][$tdK]['as'])
                                {
                                    $t = $data['rw'][$tk]['fields'][$tdK]['as'];
                                }
                                if (isset($case_special[$tk.".".$tdK]['NULL']) && isset($case_special[$tk.".".$tdK]['NOT NULL'])) {

                                    if (is_null($result->$t)) {
                                        $ajax[$i][$tk.".".$t] = $case_special[$tk.".".$tdK]['NULL'];
                                    } else {
                                        $ajax[$i][$tk.".".$t] = $case_special[$tk.".".$tdK]['NOT NULL'];
                                    }
                                } else {
                                    $ajax[$i][$tk.".".$t] = $result->$t;
                                }
                            }
                            else
                            {
//                                 $ajax[$i][$tk.".".$t] = $result->$t;
                                
                                   /* this doesn't work */
//                                 $tts = explode('.', $t);
//                                 if(isset($_REQUEST['json_debug']))
//                                 {
//                                     echo '<pre>'.print_r($tts, true).'</pre>';
//                                 }
//                                 if (count($tts) == 2) {
//                                     $ttss = $tts[1];
//                                     $json1 = $result->$ttss;
//                                     $json2 = json_decode($json1);
//                                 if(isset($_REQUEST['json_debug']))
//                                 {
//                                     echo '<pre>'.print_r($json2, true).'</pre>';
//                                 }
//                                     if (json_last_error() === JSON_ERROR_NONE) {
//                                         $ajax[$i][$tk.".".$ttss] = stripslashes($json2->$ttss);
//                                     } else {
//                                         $ajax[$i][$tk.".".$t] = stripslashes($result->$t);
//                                     }
                                    
                                        
//                                     if(isset($_REQUEST['json_debug']))
//                                     {
//                                         echo '<pre>'.print_r($ajax[$i], true).'</pre>';
//                                     }
                                    
//                                 } else {
//                                     $ajax[$i][$tk.".".$t] = stripslashes($result->$t);
//                                 }     
                                

                                //is this an as
                                if(isset($queryAs[$tdK]))
                                {
                                    $t = $queryAs[$tdK];
                                }

                                $ajax[$i][$tk.".".$t] = stripslashes($result->$t);

                                //is this JSON?
//                                 $json2 = json_decode($result->$t);
//                                 if(json_last_error() !== JSON_ERROR_NONE)
//                                 {
//                                     $ajax[$i][$tk.".".$t] = stripslashes($json2->$t);
//                                 }
//                                 unset($json2);
                                
                                if(is_array( $result->$t) || is_object( $result->$t))
                                {
                                    $ajax[$i][$tk.".".$t] = implode(", ", (array)  $result->$t);
                                }
                            }
                            unset($json[$t]);
                            
                            if($data['rw'][$tk]['fields'][$tdK]['columns'])
                            {
                                $columnsCount = count($data['rw'][$tk]['fields'][$tdK]['columns']['cols']);
                                foreach($data['rw'][$tk]['fields'][$tdK]['columns']['cols'] as $col)
                                {
                                    if(isset($ajax[$i][$col]))
                                    {
                                        $maybeRemoveAjax[$i][] = $ajax[$i][$col];
                                    }
                                }
                                
                                if(isset($_REQUEST['debug_cols']))
                                {
                                    echo '<pre>'.print_r($maybeRemoveAjax, true).'</pre>';
                                }
                                
                                for($di=0;$di<$columnsCount;$di++)
                                {
                                    if($di > 0)
                                    {
                                        $i++;
                                    }
                                    
                                    $ajax[$i][$data['rw'][$tk]['fields'][$tdK]['columns']['name']] = $maybeRemoveAjax[$i][$di];
                                                                
                                    if(isset($_REQUEST['debug_cols']))
                                    {
                                        echo '<pre>'.print_r($data['rw'][$tk]['fields'][$tdK]['columns']['name'], true).'</pre>';
                                        echo '<pre>'.print_r($ajax[$i][$data['rw'][$tk]['fields'][$tdK]['columns']['name']], true).'</pre>';
                                    }
    //                                 if(isset($ajax[$i][$maybeRemoveAjax[$i][$di]]))
    //                                 {
    //                                     unset($ajax[$i][$maybeRemoveAjax[$i][$di]]);
    //                                 }
                                }
                                unset($maybeRemoveAjax);
                            }                          
                        }//end foreach columns
                        foreach($ajax[$i] as $ak=>$av)
                        {
                            if($this->validateDate($av))
                            {
                                $ajax[$i][$ak] = date('m/d/Y', strtotime($av));
                            }
                            if($this->validateDate($av, 'Y-m-d'))
                            {
                                $ajax[$i][$ak] = date('m/d/Y', strtotime($av));
                            }
                        }
                        
                        //rental weeks don't need credits
                        if(isset($ajax[$i]['wp_room.WeekType']) 
                            && strtolower(substr( $ajax[$i]['wp_room.WeekType'], 0, 1 ))  == 'r' 
//                             && ( isset($ajax[$i]['wp_room.credit_subtract']) || isset($ajax[$i]['wp_room.credit_add']) )
                            )
                        {
						    //credit add and credit subtract need to be 0
						    $ajax[$i]['wp_room.credit_subtract'] = 0;
// 						    $ajax[$i]['wp_room.credit_add'] = 0;

                        }
                        
                        //if isset partner name and isset both given and taken
                        if(isset($ajax[$i]['wp_room.partner_name']) 
							&& (isset($ajax[$i]['wp_room.source_partner_name']) && !empty($ajax[$i]['wp_room.source_partner_name'])) 
							&& (isset($ajax[$i]['wp_room.booked_by_partner_name']) && !empty($ajax[$i]['wp_room.booked_by_partner_name']))) 
						{
                        	//this row is given -- add name of given unset the -1 column
							$ajax[$i]['wp_room.partner_name'] = $ajax[$i]['wp_room.source_partner_name'];
							//set the temp column
							$ajax[$i]['wp_room.temp_credit_subtract'] = $ajax[$i]['wp_room.credit_subtract'];
							$ajax[$i]['wp_room.credit_subtract'] = 0;
                        	//make new row with the taken -- add name of taken for this column
							$oldAjax = $ajax[$i];
                        	$i++;
							$ajax[$i] = $oldAjax;
							$ajax[$i]['wp_room.partner_name'] = $ajax[$i]['wp_room.booked_by_partner_name'];
                        	//unset the +1 column
							$ajax[$i]['wp_room.credit_add'] = 0;
							//add credit subtract back in and then remove temp
							$ajax[$i]['wp_room.credit_subtract'] = $ajax[$i]['wp_room.temp_credit_subtract'];
							unset($ajax[$i]['wp_room.temp_credit_subtract']);
						}  
                        $i++;
                    }//end foreach result
                }//end foreach querydata
                
                //if this is a trade partner then we also need adjustments
                if(isset($ajax[0]['wp_room.credit_add']) || isset($ajax[0]['wp_room.credit_subtract']))
                {
                    //this is a very specific report with specific conditions -- we alsways know that it could have a date range
                    $getWheres = explode(' AND ', $wheres);
					foreach($getWheres as $gw)
                    {
                        if( strpos( $gw,  'wp_room.check_in_date') !== false ) 
						{
                            //replace the wp_room with updated_at
                            $newWheres[] = str_replace('wp_room.check_in_date', 'updated_at', $gw);
                        }  
                    }
                    $sql = "SELECT a.name, b.credit_add, b.credit_subtract, b.comments FROM wp_partner a 
                            INNER JOIN wp_partner_adjustments b on b.partner_id=a.user_id";
                    if(isset($newWheres))
                    {
                        $sql .= ' WHERE '.implode(" AND ", $newWheres);
                    }
                    $partners = $wpdb->get_results($sql);
                    foreach($partners as $partner)
                    {
                        $ajax[$i] = [
                            'wp_room.record_id'=>'adj',
                            'wp_room.partner_name'=>$partner->name,
                            'wp_room.credit_add'->$partner->credit_add,
                            'wp_room.credit_subtract'->$partner->credit_subtract,
                            'wp_room.type'=>'Adjustment',
                            'wp_room.GuestName'=>$partner->comments,
                        ];
                        $i++;
                    }
                }
                
                if($isCancelled)
                {
                    $dk = '';
                    foreach($ajax as $ak=>$av)
                    {
                        
                        if($av['wp_gpxTransactions.id'] == $dk)
                        {
//                             this is a duplicate -- remove the last one
                            unset($ajax[$lk]);
                        }
                          
                        $dk = $av['wp_gpxTransactions.id'];
                        $lk = $ak;
                    }
                    sort($ajax);
                }
                
                if(isset($_REQUEST['report_debug']))
                {
					echo '<pre>'.print_r($ajax, true).'</pre>';
				}

                if(!empty($cron))
                {
                    $reportSend = '';
                    
                    $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $row->name);
                    $upload_dir = wp_upload_dir();
                    $fileLoc = '/var/www/reports/'.$filename.'.csv';
                    $file = fopen($fileLoc, 'w');
                    
                    $heads = array_keys($ajax[0]);
                    
                    $list = array();
                    $list[] = implode(',', $heads);
                    $i = 1;
                    
                    foreach($ajax as $k=>$itm)
                    {
                        foreach($itm as $value)
                        {
                            foreach($heads as $head)
                            {
                                $ordered[$i][] = $value[$head];
                            }
                        }
                        $list[$i] = implode(',', $ordered[$i]);
                        $i++;
                    }
                    
                    foreach($list as $line)
                    {
                        fputcsv($file,explode(",", $line));
                        
                    }
                    
                    fclose($file);
                    
                    $subject = $row->name;
                    $message = get_option('gpx_crreportsemailMessage');
                    $fromEmailName = 'GPX Vacations';
                    $fromEmail = get_option('gpx_crreportsemailFrom');
                    $toEmail = $row->emailrecipients;
                    
                    $headers[]= "From: ".$fromEmailName." <".$fromEmail.">";
                    $headers[] = "Content-Type: text/html; charset=UTF-8";
                    
                    $attachments = array($fileLoc);
                    
//                     wp_mail($toEmail, $subject, $message, $headers, $attachments);
                }//end if cron
                //if this is the trade balance report then only trade balance 
                return $ajax;
            }//end if ajax
        }//end if id
        else 
        {
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
            
            
            $tables = $this->gpx_report_writer('tables');
            foreach($tables as $table)
            {
                foreach($table['fields'] as $tk=>$tf)
                {
                    if($tf['type'] == 'join')
                    {
//                         $data['fields'][$table['table']][$tf['column']] = [
//                             'name'=>$tf['name'],
//                             'field'=>$tf['column'],
//                         ];
//                         $data['wheres'][$table['name']][] = [
//                             'name'=>$tf['name'],
//                             'field'=>$table['table'].".".$table['column'],
//                         ];
                        
                        $data['fields'][$table['table']][$tf['column'].$tf['xref']] = [
                            'name'=>$tf['name'],
                            'field'=>$tf['xref'],
                        ];
                        if(in_array($table['table'].".".$tk, $skipWheres))
                        {
                            //we don't want to set this one
                        }
                        else
                        {
                            $whereField = $tf['xref'];
                            if(isset($tf['where']))
                            {
                                $whereField = $tf['where'];
                            }
                            $data['wheres'][$table['name']][] = [
                                'name'=>$tf['name'],
                                'field'=>$whereField,
                            ];
                        }
                    }
                    elseif($tf['type'] == 'join_case' || $tf['type'] == 'join_json' || $tf['type'] == 'case')
                    {
                        $data['fields'][$table['table']][$tf['column'].$tf['xref']] = [
                            'name'=>$tf['name'],
                            'field'=>$tf['xref'],
                        ];
                    }
                    elseif($tf['type'] == 'qjson')
                    {
                        $data['fields'][$table['table']][$table['table'].".".$tk.".".$tf['xref']] = [
                            'name'=>$tf['name'],
                            'field'=>$tf['xref'],
                        ];
                    }
                    elseif($tf['type'] == 'agentname')
                    {
                        $data['fields'][$table['table']][$table['table'].".".$tk.".".$tf['xref']] = [
                            'name'=>$tf['name'],
                            'field'=>$tf['xref'],
                        ];
                    }
                    elseif($tf['type'] == 'usermeta')
                    {
                        $data['fields'][$table['table']][$table['table'].".".$tk.".".$tf['xref']] = [
                            'name'=>$tf['name'],
                            'field'=>$table['table'].".".$tf['xref'].".".$tk,
                        ];
                    }
                    elseif(is_array($tf['data']))
                    {
                        
                        foreach($tf['data'] as $tdk=>$tdf)
                        {
                            $data['fields'][$table['table']][$table['table'].".".$tk.".".$tdk] = [
                                'name'=>$tdf,
                                'field'=>$table['table'].".".$tk.".".$tdk,
                            ];
                        }
                    }
                    elseif(is_array($tf['cancelledData']))
                    {
                        
                        foreach($tf['cancelledData'] as $tdk=>$tdf)
                        {
                            $data['fields'][$table['table']][$table['table'].".".$tk.".".$tdk] = [
                                'name'=>$tdf,
                                'field'=>$table['table'].".".$tk.".".$tdk,
                            ];
                        }
                    }
                    elseif($tf['type'] == 'json' || $tf['type'] == 'json_split')
                    {
                        
                        foreach($tf['data'] as $tdk=>$tdf)
                        {
                            $data['fields'][$table['table']][$table['table'].".".$tk.".".$tdk] = [
                                'name'=>$tdf,
                                'field'=>$table['table'].".".$tk.".".$tdk,
                            ];
                        }
                    }
                    else
                    {
                        $data['fields'][$table['table']][] = [
                            'name'=>$tf,
                            'field'=>$table['table'].".".$tk,
                            ];
                        if(in_array($table['table'].".".$tk, $skipWheres))
                        {
                            //we don't want to set this one
                        }
                        else 
                        {
                            $data['wheres'][$table['name']][] = [
                                'name'=>$tf,
                                'field'=>$table['table'].".".$tk,
                                ];
                        }
                    }
                }
                $data['tables'][$table['table']] = $table['name'];
            }
            
            
            if(isset($_REQUEST['fields']))
            {
                echo '<pre>'.print_r($data['fields'], true).'</pre>';
            }
                        
            $sql = "SELECT id, name, reportType, role, userID FROM wp_gpx_report_writer";
            $reports = $wpdb->get_results($sql);
            
            foreach($reports as $k=>$report)
            {
                //report types 
                $reportType = explode(",", $report->reportType);
                if(in_array('Individual', $reportType))
                {
                    //this report must have been created by the current user
                    if(get_current_user_id() != $report->userID)
                    {
                        unset($reports[$k]);
                    }
                }
                
                if(in_array('Group', $reportType))
                {
                    //this user must have a role that was assigned to the report
                    $setRoles = explode(",", $report->role);
                    $user_meta=get_userdata(get_current_user_id());
                    $user_roles=$user_meta->roles;
                    if(!$this->in_array_any($user_roles,$setRoles))
                    {
                        //if this isn't part of the array then we don't need to continue.
                        unset($reports[$k]);
                    }
                }
                
            }
            $data['reports'] = $reports;
        }
        return $data;
    }
    
    private function in_array_any($needles, $haystack) {
        return (bool)array_intersect($needles, $haystack);
    }
    
    public function return_gpx_switchuage($usage, $type = '')
    {
        global $wpdb;
        if(!empty($type))
            $type = $type."_";
            $data = array();
            switch($usage)
            {
                case "region":
                    $sql = "SELECT country, CountryID FROM wp_gpxCategory";
                    $countries = $wpdb->get_results($sql);
                    
                    $data['html'] = '<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-name">Country
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select name="'.$type.'country" id="country_1" class="form-control col-md-7 col-xs-12">
                          	  <option></option>
                              <option value="14">USA</option>
                              ';
                    foreach($countries as $country)
                    {
                        if($country->CountryID == '14')
                            continue;
                            $data['html'] .= '<option value="'.$country->CountryID.'">'.$country->country.'</option>';
                    }
                    $data['html'] .= '
                          </select>
                        </div>
                      </div>
                      <div class="insert-above"></div>';
                    break;
                    
                case "resort":
                    $sql = "SELECT country, CountryID FROM wp_gpxCategory";
                    $countries = $wpdb->get_results($sql);
                    $data['html'] = '<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-name">Country
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select name="'.$type.'country" id="country_1" class="form-control col-md-7 col-xs-12">
                          	  <option></option>
                              <option value="14">USA</option>';
                    foreach($countries as $country)
                    {
                        if($country->CountryID == '14')
                            continue;
                            $data['html'] .= '<option value="'.$country->CountryID.'">'.$country->country.'</option>';
                    }
                    $data['html'] .= '
                          </select>
                        </div>
                      </div>
                      <div class="insert-above row">
                      <div class="col-sm-6 col-sm-offset-3 col-xs-12 text-right">
                        <a href="#" class="btn btn-primary resort-list">Load Resorts</a
                      </div>
                      <div class="insert-resorts"></div>';
                    break;
                    
                case "home-resort":
                    $sql = "SELECT country, CountryID FROM wp_gpxCategory";
                    $countries = $wpdb->get_results($sql);
                    $data['html'] = '<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-name">Country
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select name="'.$type.'country" id="country_1" class="form-control col-md-7 col-xs-12">
                          	  <option></option>
                              <option value="14">USA</option>';
                    foreach($countries as $country)
                    {
                        if($country->CountryID == '14')
                            continue;
                            $data['html'] .= '<option value="'.$country->CountryID.'">'.$country->country.'</option>';
                    }
                    $data['html'] .= '
                          </select>
                        </div>
                      </div>
                      <div class="insert-above row">
                      <div class="col-sm-6 col-sm-offset-3 col-xs-12 text-right">
                        <a href="#" class="btn btn-primary resort-list">Load Resorts</a
                      </div>
                      <div class="insert-resorts"></div>';
                    break;
                    
                case "trace":
                    $data['html'] = '';
                    break;
                    
                case "customer":
                    $data['html'] = '<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaCustomerResortSpecific">Resort Specific
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select name="metaCustomerResortSpecific" class="metaCustomerResortSpecific" class="form-control col-md-7 col-xs-12">
                          	  <option></option>
                          	  <option>Yes</option>
                          	  <option>No</option>
                          </select>
                        </div>
                    </div>
                    <div class="rs-add"></div>';
                    break;
            }
            return $data;
    }
    
    public function return_gpx_owner_search()
    {
        global $wpdb;
        
        $html = '<div class="form-group">';
        $html .= '<label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-name">Customer</label>';
        $html .= '<div class="col-xs-12 col-md-6">';
        $html .= '<input id="userSearch" class="form-control" placeholder="Name or Owner ID"><a href="#" id="userSearchBtn" class="btn btn-primary">Search</a>';
        $html .= '<div class="row"><div class="col-xs-12 col-sm-6 sflReset"><label class="label-above">Available</label><ul id="selectFromList" class="userSelect">';
        $html .= '</ul></div>';
        $html .= '<div class="col-xs-12 col-sm-6 sflReset"><label class="label-above">Selected</label><select id="selectToList" name="metaSpecificCustomer[]" class="userSelect" multiple=multiple></div></div>';
        $html .= '</select>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    public function return_get_gpx_customers($selectedVals='')
    {
        global $wpdb;
        $html = '<div class="form-group">';
        $html .= '<label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-name">Customer</label>';
        $html .= '<div class="col-md-6 col-sm-6 col-xs-12">';
        $html .= '<select class="owner-list" name="metaSpecificCustomer[]" multiple="multiple" class="form-control col-md-7 col-xs-12">';
//         $sql = "SELECT ID, display_name, user_login FROM wp_users WHERE user_login LIKE 'U%'";
        $sql = "SELECT user_id as ID, SPI_Owner_Name_1st__c as display_name, user_id as user_login FROM wp_GPR_Owner_ID__c";
        
        $getOwners = $wpdb->get_results($sql);
        
        //         $getOwners = get_users(array('role'=>'gpx_member'));
        $option = array();
        foreach($getOwners as $owner)
        {
            //            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $owner->ID ) );
            $option[$owner->ID] = $owner->display_name." ".$owner->user_login;
        }
        asort($option);
        foreach($option as $opK=>$opV)
        {
            $selected = '';
            if(!empty($selectedVals))
            {
                if(in_array(intval($opK), $selectedVals))
                    $selected = 'selected="selected"';
            }
            $html .= '<option value="'.$opK.'" '.$selected.'>'.$opV.'</option>';
        }
        $html .= '</select></div></div>';
        
        return $html;
    }
    
    public function return_get_gpx_findowner($search, $return='',$by='')
    {
        global $wpdb;
        
        $html = '';
        
//         if($by == 'user_id')
//         {
            $sql = "SELECT ID, display_name, user_login FROM wp_users WHERE ID=".$search;
//         }
        
        $rows = $wpdb->get_results($sql);
        
        if(empty($rows))
        {
                $searchVals = explode(" ", $search);
                foreach($searchVals as $sv)
                {
                    $displayNameWheres[] = " display_name LIKE '%".$sv."%'";
                }
                
                $displayNameWhere = "(".implode(" AND ", $displayNameWheres).")";
                $sql = "SELECT ID, display_name, user_login FROM wp_users WHERE user_login LIKE '%".$search."%' OR ".$displayNameWhere;
                $rows = $wpdb->get_results($sql);
        }
        
        foreach($rows as $row)
        {
            if($return == 'option')
            {
                $html .= '<option value="'.$row->ID.'" selected="selected">'.$row->user_login.' '.$row->display_name.'</option>';
            }
            else
            {
                $html .= '<li><a href="#" class="ownerSelectFrom" data-id="'.$row->ID.'" data-login="'.$row->user_login.'" data-name="'.$row->display_name.'">'.$row->user_login.' '.$row->display_name.' Select</a></li>';
            }
        }
        
        return $html;
    }
    
    public function return_get_gpx_list_resorts($value,$type='')
    {
        global $wpdb;
        $data = '';
        
        if(!empty($type))
            $opType = $type."_";
            
            $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE id='".$value."'";
            $row = $wpdb->get_row($sql);
            $sql = "SELECT id FROM wp_gpxRegion WHERE lft >= '".$row->lft."' AND rght <= '".$row->rght."'";
            $results = $wpdb->get_results($sql);
            foreach($results as $result)
            {
                $gpxRegionID = $result->id;
                $sql = "SELECT id, ResortName from wp_resorts WHERE gpxRegionID='".$gpxRegionID."'";
                $resortResult = $wpdb->get_results($sql);
                if(!empty($resortResult))
                    $resortslist[] = $resortResult;
            }
            
            
            if(isset($resortslist) && !empty($resortslist))
            {
                foreach($resortslist as $resorts)
                    foreach($resorts as $resort)
                        $ops[$resort->id] = $resort->ResortName;
                        
                        asort($ops);
                        $data = '<div class="form-group parent-delete">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-name">Resort
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-11">
                      <select name="'.$opType.'resort[]" class="form-control col-md-7 col-xs-12">
                      	  <option></option>';
                        foreach($ops as $rkey=>$rval)
                        {
                            $data .= '<option value="'.$rkey.'">'.$rval.'</option>';
                        }
                        $data .= '
                      </select>
                    </div>
                    <div class="col-xs-1 remove-element">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                    </div>
                  </div>';
            }
            else
            {
                $data = '<div class="flash-msg">There aren\'t any resorts in the selected region';
            }
            return $data;
    }
    
    public function return_dae_members()
    {
        $all_users = count_users();
        $total_users = $all_users['avail_roles']['gpx_member'];
        
        $args = array( 'role' => 'gpx_member', 'number'=>$_REQUEST['limit'], 'offset'=>$_REQUEST['offset']  );
        
        if(isset($_REQUEST['search']) && !empty($_REQUEST['search']))
        {
            if(substr($_REQUEST['search'], 0, 1) == U && strlen($_REQUEST['search']) == '7')
                $_REQUEST['search'] = str_replace("U", "", $_REQUEST['search']);
                $args['meta_query'] = array(
                    'relation' => 'OR',
                    array(
                        'key'=>'DAEMemberNo',
                        'value'=>$_REQUEST['search'],
                        'compare'=>'LIKE'
                    ),
                    array(
                        'key'=>'ResortMemberID',
                        'value'=>$_REQUEST['search'],
                        'compare'=>'LIKE'
                    ),
                    array(
                        'key'=>'ResortShareID',
                        'value'=>$_REQUEST['search'],
                        'compare'=>'LIKE'
                    ),
                    array(
                        'key'=>'LastName1',
                        'value'=>$_REQUEST['search'],
                        'compare'=>'LIKE'
                    ),
                    array(
                        'key'=>'FirstName1',
                        'value'=>$_REQUEST['search'],
                        'compare'=>'LIKE'
                    ),
                    array(
                        'key'=>'Email',
                        'value'=>$_REQUEST['search'],
                        'compare'=>'LIKE'
                    ),
                );
        }
        $users = get_users($args);
        $i = 0;
        foreach($users as $user)
        {
            $userInfo = get_userdata($user->data->ID);
            
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $user->data->ID ) );
            
            $credit = '<span class="creditAmt" data-user="'.$user->data->ID.'">';
            if(isset($usermeta->credit))
                $credit .= '$'.$usermeta->credit;
                
                $credit .= '</span><input type="text" name="newcredit" id="adjCredit">';
                $memberno = '';
                $salesid = '';
                $resortMemberID = '';
                if(isset($usermeta->DAEMemberNo))
                    $memberno = $usermeta->DAEMemberNo;
                    if(isset($usermeta->ResortShareID))
                        $salesid = $usermeta->ResortShareID;
                        if(isset($usermeta->ResortMemeberID))
                            $resortMemberID = $usermeta->ResortMemeberID;
                            
                            $required = array(
                                'FirstName1',
                                'LastName1',
                                'Email'
                            );
                            
                            foreach($required as $require)
                            {
                                if(!isset($usermeta->$require))
                                    $usermeta->$require = '';
                            }
                            
                            $data[$i]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_edit&id='.$user->data->ID.'"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                            $data[$i]['first_name'] = $usermeta->FirstName1;
                            $data[$i]['last_name'] = $usermeta->LastName1;
                            $data[$i]['user_email'] = $usermeta->Email;
                            $data[$i]['user_login'] = $userInfo->user_login;
                            $data[$i]['EMSAccountID'] = $memberno;
                            $data[$i]['SalesContractID'] = $salesid;
                            $data[$i]['ResrotMemberID'] = $resortMemberID;
                            $i++;
        }
        
        $fulldata['total'] = $total_users;
        $fulldata['rows'] = $data;
        
        return $fulldata;
    }
    public function return_add_gpx_credit($amount, $user)
    {
        
    }
    public function return_get_gpx_users_switch()
    {
        global $wpdb;
        /*
         * UPDATE wp_users SET display_name = CONCAT((SELECT meta_value FROM wp_usermeta WHERE meta_key = 'FirstName1' AND user_id = ID), ' ', (SELECT meta_value FROM wp_usermeta WHERE meta_key = 'LastName1' AND user_id = ID))  WHERE display_name=''
         */
        // $all_users = count_users();
        //$total_users = $all_users['avail_roles']['gpx_member'];

        $args = array( 'role' => 'gpx_member', 'number'=>$_REQUEST['limit'], 'offset'=>$_REQUEST['offset']  );
        $args = array( 'role' => 'gpx_member' );
        if(isset($_REQUEST['filter']) && !empty($_REQUEST['filter']))
        {
            $filters = json_decode(stripslashes($_REQUEST['filter']));
            foreach($filters as $filterKey=>$filterVal)
            {
                if($filterKey == 'display_name')
                {
                    $searchVals = explode(" ", $filterVal);
                    foreach($searchVals as $sv)
                    {
                        $displayWheres[] = " ".$filterKey." LIKE '%".$sv."%'";
                    }
                    $wheres[] = "(".implode(" AND ", $displayWheres).")";
                }
                else
                    $wheres[] = " ".$filterKey." LIKE '%".$filterVal."%'";
            }
        }
        elseif(isset($_REQUEST['search']) && !empty($_REQUEST['search']))
        {
            $searchVals = explode(" ", $_REQUEST['search']);
            $searchAgainst = array('user_email', 'display_name', 'user_login');
            foreach($searchVals as $sv)
            {
                foreach($searchAgainst as $sa)
                {
                    $wheres[] = " ".$sa." LIKE '%".$sv."%'";
                }
            }
        }
        
        $where = "WHERE user_login LIKE 'U%' ";
        if(isset($wheres) && !empty($wheres))
        {
            $where .= 'AND (';
            $where .= implode(" AND ", $wheres);
            $where .= ')';
        }
        $sql = "SELECT SQL_CALC_FOUND_ROWS ID, user_email, display_name, user_login FROM wp_users ".$where." LIMIT ".$_REQUEST['limit']." OFFSET ".$_REQUEST['offset'];
        $users = $wpdb->get_results($sql);
        
        $sql = "SELECT FOUND_ROWS()";
        $rowcount = $wpdb->get_var($sql);
        
        $i = 0;
        foreach($users as $user)
        {
            //filter -- only gpx_member
            $user_meta=get_userdata($user->ID);
            $user_roles=$user_meta->roles;
            if(!in_array('gpx_member', $user_roles))
            {
                $rowcount--;
                continue;
            }   
            //createe the array for the table
            $data[$i]['switch'] = '<a href="#" class="switch_user" data-user="'.$user->ID.'" title="Select Owner and Return"><i class="fa fa-refresh fa-rotate-90" aria-hidden="true"></i></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_edit&id='.$user->ID.'" title="Edit Owner Account"><i class="fa fa-pencil" aria-hidden="true"></i></a>|&nbsp;&nbsp;<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_mapping&id='.$user->ID.'" title="View Owner Account"><i class="fa fa-eye" aria-hidden="true"></i></a>';
            $data[$i]['display_name'] = $user->display_name;
            $data[$i]['last_name'] = '';
            $data[$i]['user_email'] = $user->user_email;
            $data[$i]['user_login'] = $user->user_login;
            $i++;
        }
        //         $sql = "SELECT FOUND_ROWS()";
        //         $rowcount = $wpdb->get_var($sql);
        $fulldata['total'] = $rowcount;
        $fulldata['rows'] = $data;
        return $fulldata;
    }
    
    public function return_gpx_customrequests()
    {
        global $wpdb;
        
        $data = array();
        
        $sql = "SELECT * FROM wp_gpxCustomRequest";
        
        if(isset($_REQUEST['filtertype']))
        {
            $dates = explode(" - ", $_REQUEST['dates']);
            if($_REQUEST['filtertype'] == 'travel')
            {
                if(count($dates) == 1)
                {
                    //                     $sql .= ' WHERE checkIn="'.date('m/d/Y', strtotime($dates[0])).'"';
                    $wheres[] = 'checkIn="'.date('m/d/Y', strtotime($dates[0])).'"';
                }
                else
                {
                    //                     $sql .= ' WHERE checkIn BETWEEN "'.date('m/d/Y', strtotime($dates[0])).'" AND "'.date('m/d/Y', strtotime($dates[1])).'"';
                    $wheres[] = 'checkIn BETWEEN "'.date('m/d/Y', strtotime($dates[0])).'" AND "'.date('m/d/Y', strtotime($dates[1])).'"';
                    //                     $sql .= ' WHERE checkIn BETWEEN "'.date('m/d/Y', strtotime($dates[0])).'" AND "'.date('m/d/Y', strtotime($dates[1])).'"';
                    $wheres[] = 'checkIn2 BETWEEN "'.date('m/d/Y', strtotime($dates[0])).'" AND "'.date('m/d/Y', strtotime($dates[1])).'"';
                }
            }
            elseif($_REQUEST['filtertype'] == 'email')
            {
                $_REQUEST['found'] = 'yes';
                if(count($dates) == 1)
                {
                    //                     $sql .= ' WHERE checkIn="'.date('m/d/Y', strtotime($dates[0])).'"';
                    $wheres[] = 'matchEmail BETWEEN "'.date('Y-m-d 00:00:00', strtotime($dates[0])).'" AND "'.date('Y-m-d 23:59:59', strtotime($dates[0])).'"';
                }
                else
                {
                    //                     $sql .= ' WHERE checkIn BETWEEN "'.date('m/d/Y', strtotime($dates[0])).'" AND "'.date('m/d/Y', strtotime($dates[1])).'"';
                    $wheres[] = 'matchEmail BETWEEN "'.date('Y-m-d 00:00:00', strtotime($dates[0])).'" AND "'.date('Y-m-d 23:59:59', strtotime($dates[1])).'"';
                }
            }
            else
            {
                if(count($dates) == 1)
                {
                    //                     $sql .= ' WHERE datetime="'.date('Y-m-d', strtotime($dates[0])).'%"';
                    $wheres[] = 'datetime="'.date('Y-m-d', strtotime($dates[0])).'%"';
                }
                else
                {
                    //                     $sql .= ' WHERE datetime BETWEEN "'.date('Y-m-d 00:00:00', strtotime($dates[0])).'" AND "'.date('Y-m-d 23:59:59', strtotime($dates[1])).'"';
                    $wheres[] .= 'datetime BETWEEN "'.date('Y-m-d 00:00:00', strtotime($dates[0])).'" AND "'.date('Y-m-d 23:59:59', strtotime($dates[1])).'"';
                }
            }
        }
        
        if(isset($_REQUEST['found']))
        {
            if($_REQUEST['found'] == 'yes')
                $wheres[] = "matched<>''";
                if($_REQUEST['found'] == 'no')
                    $wheres[] = "matched=''";
        }
        
        if(isset($wheres))
        {
            $sql .= " WHERE ".implode(" AND ", $wheres);
        }
        $crs = $wpdb->get_results($sql);
        
        $i = 0;
        foreach($crs as $cr)
        {
            $location = '';
            if(!empty($cr->resort))
            {
                $location = 'Resort: '.$cr->resort;
                
            }
            elseif(!empty($cr->city))
            {
                $location = 'City: '.$cr->city;
            }
            elseif(!empty($cr->region))
            {
                $location = 'Region: '.$cr->region;
            }
            
            $date = $cr->checkIn;
            if(!empty($cr->checkIn2))
                $date .= ' - '.$cr->checkIn2;
                
                $converted = "No";
                $revenue = '';
                if($cr->matchConverted != '0')
                {
                    $converted = "Yes";
                    $sql = "SELECT data from wp_gpxTransactions WHERE id='".$cr->matchConverted."'";
                    $transData = $wpdb->get_row($sql);
                    $transDecode = json_decode($transData->data);
                    $revenue = '$'.number_format($transDecode->Paid, 2);
                }
                
                $matchEmail = '';
                if(!empty($cr->matchEmail))
                {
                    $matchEmail = date('m/d/Y', strtotime($cr->matchEmail));
                }
                
                $nearby = "No";
                if($cr->nearby == 1)
                {
                    $nearby = "Yes";
                }
                
                $larger = "No";
                if($cr->larger == 1)
                {
                    $larger = "Yes";
                }
                
                $active = "Yes";
                if($cr->active == '0')
                    $active = "No";
                    
                    $found = "Yes";
                    if(empty($cr->matched))
                        $found = "No";
                        
                        $data[$i]['emsID'] = $cr->emsID;
                        $data[$i]['owner'] = $cr->firstName." ".$cr->lastName;
                        $data[$i]['location'] = $location;
                        $data[$i]['region'] = $cr->region;
                        $data[$i]['city'] = $cr->city;
                        $data[$i]['resort'] = $cr->resort;
                        $data[$i]['traveldate'] = $date;
                        $data[$i]['found'] = $found;
                        $data[$i]['matched'] = $cr->matched;
                        $data[$i]['converted'] = $converted;
                        $data[$i]['revenue'] = $revenue;
                        $data[$i]['roomType'] = $cr->roomType;
                        $data[$i]['who'] = $cr->who;
                        $data[$i]['travelers'] = ($cr->adults + $cr->children);
                        $data[$i]['entrydate'] = date('m/d/Y', strtotime($cr->datetime));
                        $data[$i]['matchEmail'] = $matchEmail;
                        $data[$i]['nearby'] = $nearby;
                        $data[$i]['larger'] = $larger;
                        $data[$i]['type'] = $cr->preference;
                        $data[$i]['active'] = $active;
                        $i++;
        }
        
        return $data;
    }
    
    public function return_gpx_customrequeststats()
    {
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
        
        return $return;
    }
    
    public function return_gpx_regions()
    {
        global $wpdb;
        
        $data = array();
        
        $sql = "SELECT a.id, b.country FROM wp_daeRegion a
                INNER JOIN wp_gpxCategory b on b.CountryID=a.CountryID";
        $countries = $wpdb->get_results($sql);
        foreach($countries as $country)
        {
            $clist[$country->id] = $country->country;
        }
        $sql = "SELECT * FROM wp_gpxRegion
                ORDER BY lft ASC";
        $regions = $wpdb->get_results($sql);
        foreach($regions as $region)
        {
            $rlist[$region->id] = $region->name;
        }
        $i = 0;
        foreach($regions as $region)
        {
            $edit = '';
            $gpx = '';
            $edit = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=regions_edit&id='.$region->id.'"><i class="fa fa-pencil" aria-hidden="true"></i></a></a>';
            if($region->RegionID == 'NULL' || empty($region->RegionID))
            {
                $parent = '';
                if(isset($rlist[$region->parent]))
                    $parent = $rlist[$region->parent];
                    $gpx = 'Yes';
            }
            else
                $parent = $clist[$region->RegionID];
                
                
                $data[$i]['edit'] = $edit;
                $data[$i]['gpx'] = $gpx;
                $data[$i]['region'] = $region->name;
                $data[$i]['displayName'] = $region->displayName;
                $data[$i]['parent'] = $parent;
                $i++;
        }
        
        return $data;
        
    }
    
    public function return_gpx_transactions_dt($tradepartner = '')
    {
        /*
         *
         * not using this right now.  need to reconsider since there is json data to be searched
         */
        $table = 'wp_gpxTransactions';
        
        $primaryKey = 'id';
        
        $columns = array(
            array( 'db' => 'id', 'dt' => 0 ),
            array( 'db' => 'last_name',  'dt' => 1 ),
            array( 'db' => 'position',   'dt' => 2 ),
            array( 'db' => 'office',     'dt' => 3 ),
            array(
                'db'        => 'start_date',
                'dt'        => 4,
                'formatter' => function( $d, $row ) {
                return date( 'jS M y', strtotime($d));
                }
                ),
                array(
                    'db'        => 'salary',
                    'dt'        => 5,
                    'formatter' => function( $d, $row ) {
                    return '$'.number_format($d);
                    }
                    )
                );
        $columns = [
            [
                'db'=>'id',
                'dt'=>0,
                'formatter'=>function( $d, $row ) {
                $items[] = $row['id'];
                $items[] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=transactions_view&id='.$row['id'].'" target="_blank"><i class="fa fa-external-link" aria-hidden="true"></i></a>';
                $items[] = ' <a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=transactions_view&id='.$row['id'].'" class="in-modal"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                
                return implode(" ", $items);
                }
                ],
                [
                    'db'=>'memberNo',
                    'dt'=>1,
                ],
                [
                    'db'=>'memberName',
                    'dt'=>2,
                ],
                [
                    'db'=>'ownedBy',
                    'dt'=>3,
                ],
                /*
                 * @todo update guest name javascript
                 */
                [
                    'db'=>'guest',
                    'dt'=>4,
                    'formatter'=>function( $d, $row ) {
                    $guestName[] = '<div data-name="'.$row['GuestName'].'" class="updateGuestName">';
                    $guestName[] = '>';
                    //$guestName .= '<input type="text" class="form-control guestNameInput'.$transaction->id.'" name="updateGuest" data-transaction="'.$row->id.'" value="'.$data->GuestName.'" style="display: none" />';
                    $guestName[] = '<i class="fa fa-edit"></i> <span class="guestName">'.$row['GuestName'].'</span>';
                    $guestName[] = '</div>';
                    
                    return implode(" ", $guestName);
                    }
                    ],
                    [
                        'db'=>'ownedBy',
                        'dt'=>5,
                    ],
                    ];
        $output[$i]['guest'] = $guestNaeme;
        $output[$i]['transactionType'] = $row->transactionType;
        $output[$i]['Resort'] = $row->ResortName;
        $output[$i]['resrotID'] = $row->ResortID;
        $output[$i]['weekID'] = $row->weekId;
        $output[$i]['size'] = $data->Size;
        $output[$i]['checkIn'] = '<div data-date="'.strtotime($data->checkIn).'">'.date('m/d/Y', strtotime($data->checkIn)).'</div>';
        $output[$i]['paid'] = '<div data-price="'.$data->Paid.'">$'.$data->Paid.'</div>';
        $output[$i]['weekType'] = $data->WeekType;
        
        $output[$i]['date'] = '<div data-date="'.strtotime($row->datetime).'">'.date('m/d/Y', strtotime($row->datetime)).'</div>';
        $output[$i]['adults'] = $data->Adults;
        $output[$i]['children'] = $data->Children;
        $output[$i]['upgradefee'] = $data->UpgradeFee;
        $output[$i]['cpo'] = $data->CPO;
        $output[$i]['cpofee'] = $data->CPOFee;
//         $output[$i]['weekPrice'] = $data->WeekPrice;
        $output[$i]['balance'] = $data->Balance;
        $output[$i]['sleeps'] = $data->sleeps;
        $output[$i]['bedrooms'] = $data->bedrooms;
        $output[$i]['nights'] = $data->noNights;
        $output[$i]['processedBy'] = $data->processedBy;
        $output[$i]['promoName'] = $data->promoName;
        $output[$i]['discount'] = $data->discount;
        $output[$i]['coupon'] = ($data->coupon != null) ? $data->coupon : "";
        $output[$i]['ownerCreditCouponAmount'] = $data->ownerCreditCouponAmount;
        $output[$i]['transactionDate'] = $row->datetime;
        $output[$i]['uploadedDate'] = $data->Uploaded;
        require( AERCADMIN_PLUGIN_DIR.'/libraries/ssp.class.php' );
        
        /**
         * below is an example for a join
         */
        /*
         $joinQuery = "FROM `{$table}` AS `c` LEFT JOIN `currency_names` AS `cn` ON (`cn`.`id` = `c`.`id_currency`)";
         $extraCondition = "`id_client`=".$ID_CLIENT_VALUE;
         
         $data = SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraCondition);
         */
        
        $joinQuery = "FROM wp_gpxTransactions a";
        $joinQuery .= "INNER JOIN wp_resorts b ON a.resortID=b.ResortID";
        $extraCondition = "cancelled IS null";
        
        $data = SSP::simple( $_GET, $this->ssp_sql_details, $table, $primaryKey, $columns, $joinQuery, $extraCondition);
    }
    public function return_gpx_transactions($tradepartner = '', $gp = '')
    {
        global $wpdb;
        
        $output = array();
        
        $data = array();

        $where;
        $orderBy;
        $limit;
        $offset;
        if(isset($_REQUEST['filter']))
        {
            $search = json_decode(stripslashes($_REQUEST['filter']));
            //error_log(print_r($search, TRUE));
            foreach($search as $sk=>$sv)
            {
                if($sk == 'id')
                {
                    $wheres[] = "a.id LIKE '".$sv."%'";
                }
                elseif($sk == 'memberNo')
                {
                    $wheres[] = "JSON_EXTRACT(data, '$.MemberNumber') LIKE '%".$sv."%'";
                }
                elseif($sk == 'Resort')
                {
                    $wheres[] = "b.ResortName LIKE '%".$sv."%'";
                }
                elseif($sk == 'room_type')
                {
                    $wheres[] = "u.name LIKE '%".$sv."%'";
                }
                elseif($sk == 'weekType')
                {
                    $wheres[] = "JSON_EXTRACT(data, '$.WeekType') LIKE '%".$sv."%'";
                }
                elseif($sk == 'weekID')
                {
                    $wheres[] = "a.weekId LIKE '%".$sv."%'";
                }
                elseif($sk == 'checkIn')
                {
                    $wheres[] = "a.check_in_date BETWEEN '".date('Y-m-d 00:00:00', strtotime($sv))."' AND '".date('Y-m-d 23:59:59', strtotime($sv))."' ";
                }
                elseif($sk == 'paid')
                {
                    $wheres[] = "JSON_EXTRACT(data, '$.Paid') LIKE '%".$sv."%'";
                }
                elseif($sk == 'transactionDate')
                {
                    $wheres[] = "a.datetime BETWEEN '".date('Y-m-d 00:00:00', strtotime($sv))."' AND '".date('Y-m-d 23:59:59', strtotime($sv))."' ";
                }
                elseif($sk == 'cancelled')
                {
                    if(strpos($sv, 'y') !== false || strpos($sv, 'ye') !== false || strpos($sv, 'yes') !== false)
                        $wheres[] = "a.cancelled LIKE '%1%'";
                    elseif(strpos($sv, 'n') !== false || strpos($sv, 'no') !== false )
                        $wheres[] = "a.cancelled IS NULL";
                }
                elseif($sk == 'check_in_date')
                {
                    $wheres[] = $sk ." BETWEEN '".date('Y-m-d 00:00:00', strtotime($sv))."' AND '".date('Y-m-d 23:59:59', strtotime($sv))."' ";
                }
                else
                {
                    $wheres[] = $sk." LIKE '%".$sv."%'";
                }
            }
            $where .= " ".implode(" OR ", $wheres)."";
        }
        if(isset($_REQUEST['sort']))
        {
            $orderBy = " ORDER BY ".$_REQUEST['sort']." ".$_REQUEST['order'];
        }
        if(isset($_REQUEST['limit']))
        {
            $limit = " LIMIT ".$_REQUEST['limit'];
        }
        if(isset($_REQUEST['offset']))
        {
            $offset = " OFFSET ".$_REQUEST['offset'];
        }

        $sql = "SELECT a.*, b.ResortName, u.name as room_type FROM wp_gpxTransactions a
                LEFT OUTER JOIN wp_room r ON r.record_id=a.weekId
                LEFT OUTER JOIN wp_resorts b ON r.resort=b.id
                
                LEFT OUTER JOIN wp_unit_type u on u.record_id=r.unit_type";

        
        if(!empty($gp))
        {
            $sql .= $gp;
        }
        else
        {
            if(!empty($where))
            {
                $sql .= " WHERE ".$where;
            }
            $sql .= $orderBy;
            $sql .= $limit;
            $sql .= $offset;
        }
        
        $tsql = "SELECT a.id, a.data  FROM wp_gpxTransactions a
                LEFT OUTER JOIN wp_room r ON r.record_id=a.weekId
                LEFT OUTER JOIN wp_resorts b ON r.resort=b.id
                LEFT OUTER JOIN wp_unit_type u on u.record_id=r.unit_type";
        if(!empty($gp))
        {
            $tsql .= $gp;
        }
        else
        {
            if(!empty($where))
            {
                $tsql .= " WHERE ".$where;
            }
        }
        $output['total'] = count($wpdb->get_results($tsql));
        
        
        if(isset($_GET['transactions_debug']))
        {
            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
            echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
            echo '<pre>'.print_r($wpdb->last_result, true).'</pre>';
        }
        
        $rows = $wpdb->get_results($sql);
        $output['rows'] = array();
        $i = 0;
        
        if(isset($_GET['transactions_debug']))
        {
            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
            echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
            echo '<pre>'.print_r($wpdb->last_result, true).'</pre>';
        }
        
        foreach($rows as $row)
        {
            if(!empty($tradepartner))
            {
                //if this is a trade partner search then we need to find those with that role
                $user_meta=get_userdata($row->userID);
                
                $user_roles=$user_meta->roles;
                if(!in_array('gpx_trade_partner', $user_roles))
                {
                    //if this isn't part of the array then we don't need to continue.
                    continue;
                }
            }
            if($row->cancelled == 1)
            {
                $cdat = json_decode($row->cancelledData);
                $cancelled = '<div class="viewCancelledTransaction" data-name="'.$cdat->name.'"';
                $cancelled .= ' data-date="'.$cdat->date.'"';
                $cancelled .= ' data-refunded="$'.$cdat->refunded.'"';
                $cancelled .= '>';
                $cancelled .= '<i class="fa fa-eye"></i><span class="cancelledTransaction cancelledTransaction"'.$row->id.'">Yes</span>';
            }
            else 
            {
                $cancelled = 'No';
            }
            
            $data = json_decode($row->data);
            $view = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=transactions_view&id='.$row->id.'" target="_blank"><i class="fa fa-external-link" aria-hidden="true"></i></a>';
            $view .= ' <a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=transactions_view&id='.$row->id.'" class="in-modal"><i class="fa fa-eye" aria-hidden="true"></i></a>';
            
            $name = explode(" ", $data->GuestName);
            $email = '';
            if(isset($data->Email))
            {
                $email = $data->Email;
            }
            $checkin = '';
            if($data->checkIn != '')
            {
                $checkin = '<div data-date="'.strtotime($data->checkIn).'">'.date('m/d/Y', strtotime($data->checkIn)).'</div>';
            }
            $transactionDate = '';
            if($row->datetime != '')
            {
                $transactionDate = '<div data-date="'.strtotime($row->datetime).'">'.date('m/d/Y', strtotime($row->datetime)).'</div>';
            }
            $guestName = '<div data-name="'.$data->GuestName.'" class="updateGuestName"';
            $guestName .= ' data-transaction="'.$row->id.'"';
            $guestName .= ' data-fname="'.$name[0].'"';
            $guestName .= ' data-lname="'.$name[1].'"';
            $guestName .= ' data-email="'.$email.'"';
            $guestName .= ' data-adults="'.$data->Adults.'"';
            $guestName .= ' data-children="'.$data->Children.'"';
            $guestName .= ' data-owner="'.$data->Owner.'"';
            $guestName .= '>';
            //$guestName .= '<input type="text" class="form-control guestNameInput'.$transaction->id.'" name="updateGuest" data-transaction="'.$row->id.'" value="'.$data->GuestName.'" style="display: none" />';
            $guestName .= '<i class="fa fa-edit"></i> <span class="guestName guestName'.$row->id.'">'.$data->GuestName.'</span>';
            $guestName .= '</div>';
            
            $output['rows'][$i]['view'] = $view;
            $output['rows'][$i]['transactionType'] = ucwords(str_replace("_", " ", $row->transactionType));
            $output['rows'][$i]['id'] = $row->id;
            $output['rows'][$i]['memberNo'] = $data->MemberNumber;
            $output['rows'][$i]['memberName'] = $data->MemberName;
            $output['rows'][$i]['ownedBy'] = $data->Owner;
            $output['rows'][$i]['guest'] = $guestName;
            $output['rows'][$i]['Resort'] = $row->ResortName;
            $output['rows'][$i]['resrotID'] = $row->ResortID;
            $output['rows'][$i]['room_type'] = $row->room_type;
            $output['rows'][$i]['depositID'] = $row->depositID;
            $output['rows'][$i]['weekID'] = $row->weekId;
            $output['rows'][$i]['size'] = $data->Size;
            $output['rows'][$i]['checkIn'] = $checkin;
            $output['rows'][$i]['paid'] = '<div data-price="'.$data->Paid.'">$'.$data->Paid.'</div>';
            $output['rows'][$i]['weekType'] = $data->WeekType;
            
            $output['rows'][$i]['date'] = '<div data-date="'.strtotime($row->datetime).'">'.date('m/d/Y', strtotime($row->datetime)).'</div>';
            $output['rows'][$i]['adults'] = $data->Adults;
            $output['rows'][$i]['children'] = $data->Children;
            $output['rows'][$i]['upgradefee'] = $data->UpgradeFee;
            $output['rows'][$i]['cpo'] = $data->CPO;
            $output['rows'][$i]['cpofee'] = $data->CPOFee;
            $output['rows'][$i]['weekPrice'] = $data->WeekPrice;
            $output['rows'][$i]['balance'] = $data->Balance;
            $output['rows'][$i]['sleeps'] = $data->sleeps;
            $output['rows'][$i]['bedrooms'] = $data->bedrooms;
            $output['rows'][$i]['nights'] = $data->noNights;
            $output['rows'][$i]['processedBy'] = $data->processedBy;
            $output['rows'][$i]['promoName'] = $data->promoName;
            $output['rows'][$i]['discount'] = $data->discount;
            $output['rows'][$i]['coupon'] = ($data->coupon != null) ? $data->coupon : "";
            $output['rows'][$i]['ownerCreditCouponAmount'] = $data->ownerCreditCouponAmount;
            $output['rows'][$i]['transactionDate'] = $transactionDate;
            $output['rows'][$i]['uploadedDate'] = $data->Uploaded;
            $output['rows'][$i]['cancelled'] = $cancelled;
            $i++;
        }
        return $output;
    }
    
    public function return_get_gpx_holds($gp='')
    {
        global $wpdb;
        
        $output =  [];
        
        $sql = "SELECT t.id as txid, a.id as holdID, a.user, a.release_on, a.released, a.data, b.record_id as id, b.check_in_date, b.active, c.ResortName, d.name, d.name as roomSize FROM wp_gpxPreHold a
                INNER JOIN wp_room b on b.record_id=a.weekid
                INNER JOIN wp_resorts c on c.id=b.resort
                INNER JOIN wp_unit_type d ON d.record_id=b.unit_type
                LEFT OUTER JOIN wp_gpxTransactions t ON t.weekId=b.record_id";
        if(!empty($gp))
        {
            $sql .= $gp;
        }
        $sql .= ' GROUP BY a.id';
        $rows = $wpdb->get_results($sql);

        $i = 0;
        foreach($rows as $row)
        {
            $canextend = false;

            $user = get_user_meta($row->user);
            $released = 'Yes';
            if($row->released == '0')
            {
                $released = 'No';
            }
            elseif($row->active == '0')//is this active?
            {
                //was this booked?
                $isbooked =  $this->weekisbooked($row->id);
                
                if($isbooked)
                {
                    $released = 'Booked';
                }
            }
            
//             if($released == 'No' && !$isbooked)
            if($released == 'No')
            {
                $canextend = true;
            }
            
            $action = '<a href="#" class="more-hold-details" data-toggle="modal" data-target="#holdModal'.$row->holdID.'"><i class="fa fa-eye"></i></a>&nbsp;';
            $action .= '<div id="holdModal'.$row->holdID.'" class="modal fade" role="dialog">';
            $action .= '<div class="modal-dialog">';
            $action .= '<div class="modal-content">';
            $action .= '<div class="modal-header">';
            $action .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
            $action .= '<h4 class="modal-title">Hold Details</h4>';
            $action .= '</div>';
            $action .= '<div class="modal-body">';
            $action .= '<ul>';
            $action .= '<li><strong>Owner:</strong> '.$user['first_name'][0].' '.$user['last_name'][0].'</li>';
            $action .= '<li><strong>Week:</strong> '.$row->id.'</li>';
            $action .= '<li><strong>Resort:</strong> '.$row->ResortName.'</li>';
            $action .= '<li><strong>Room:</strong> '.$row->name.'</li>';
            $action .= '<li><strong>Check In:</strong> '.date('m/d/Y', strtotime($row->check_in_date)).'</li>';
            $action .= '<li><strong>Activity:</strong></li><ul style="margin-left: 20px;">';
            $holdDets = json_decode($row->data);
            foreach($holdDets as $hk=>$hd)
            {
                $action .= '<li><strong>'.date('m/d/Y h:i a', $hk).'</strong> '.$hd->action.' by '.$hd->by.'</li>';
            }
            $action .= '</ul>';
            $action .= '</ul>';
            $action .= '</div>';
            $action .= '<div class="modal-footer">';
            $action .= '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
            $action .= '</div>';
            $action .= '</div>';
            $action .= '</div>';
            $action .= '</div>';
            if($canextend)
            {
            $action .= '<span class="extend-box">';
            $action .= '<a href="#" class="extend-week"title="Extend Week"><i class="fa fa-calendar-plus-o"></i></a>';
            $action .= '<span class="extend-input" style="display: none;">';
            $action .= '<a href="#" class="close_box">&times;</a>';
            $action .= '<input type="date" class="form-control extend-date" name="extend-date" />';
            $action .= '<a href="#" class="btn btn-primary extend-btn" data-id="'.$row->holdID.'" >Extend Hold</a>';
            $action .= '</span>';
            $action .= '</span>';
            if($released == 'No')
            {
                $action .= '&nbsp;&nbsp;&nbsp;<a href="#" class="release-week" data-id="'.$row->holdID.'" title="release"><i class="fa fa-calendar-times-o"></i></a>';
            }
            }
            $output[$i]['action'] = $action;
            $output[$i]['name'] = $user['first_name'][0].' '.$user['last_name'][0];
            $output[$i]['memberNo'] = $row->user;
            $output[$i]['week'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id='.$row->id.'" target="_blank">'.$row->id.'</a>';
            $output[$i]['resort'] = $row->ResortName;
            $output[$i]['roomSize'] = $row->roomSize;
            $output[$i]['checkIn'] = date('m/d/Y', strtotime($row->check_in_date));
            $output[$i]['releaseOn'] = date('m/d/Y H:i:s', strtotime($row->release_on));
            $output[$i]['release'] = $released;
            
            $i++;
        }
        
        
        return $output;
    }
 
    public function return_gpx_get_owner_credits()
    {
        global $wpdb;
        $sql = "SELECT a.*, b.recorded_by  FROM `wp_credit` a
                LEFT OUTER JOIN wp_credit_modification b ON b.credit_id=a.id
                WHERE `owner_id` = '".$_REQUEST['userID']."'";
        $rows = $wpdb->get_results($sql);
        
        foreach($rows as $row)
        {
            $creditAmt = 0;
            if(!empty($row->credit_amount))
            {
                $creditAmt = $row->credit_amount;
            }
            $creditUsed = 0;
            if(!empty($row->credit_used))
            {
                $creditUsed = $row->credit_used;
            }
            
            $ced = '';
            if(!empty($row->credit_expiration_date))
            {
                $ced = date('m/d/Y', strtotime($row->credit_expiration_date));
            }
            
            $ea = [];
            if(!empty($row->extension_date))
            {
                $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $row->recorded_by ) );
                $ea[] = $usermeta->first_name.' '.$usermeta->last_name.' on '.date('m/d/Y', strtotime($row->extension_date));
            }
            
            $data[$row->id]['action'] ='';
            if($row->credit_amount > 0)
            {
                $data[$row->id]['action'] = '<a href="#" data-id="'.$row->id.'" class="credit-extend" data-toggle="modal" data-target="#creModal" title="Credit Extension"><i class="fa fa-calendar-plus-o"></i></a>';
            }
            $data[$row->id]['id'] = $row->id;
            $data[$row->id]['resort'] = $row->resort_name;
            $data[$row->id]['checkIn'] = date('m/d/Y', strtotime($row->check_in_date));
            $data[$row->id]['depositDate'] = date('m/d/Y', strtotime($row->created_date));
            $data[$row->id]['depositYear'] = $row->deposit_year;
            $data[$row->id]['weekType'] = $row->week_type;
            $data[$row->id]['unitType'] = $row->unit_type;
            $data[$row->id]['coupon'] = $row->coupon;
            $data[$row->id]['creditAmt'] = $creditAmt;
            $data[$row->id]['creditUsed'] = $creditUsed;
            $data[$row->id]['expirationDate'] = $ced;
            $data[$row->id]['extensionActivity'] = implode('<br />', $ea);
            $data[$row->id]['status'] = $row->status;
        }
        
        sort($data);
        
        return $data;
    }
    
    public function return_gpx_desccoupons($active='')
    {
        global $wpdb;
        $where = '';
        $orderBy;
        $limit;
        $offset;
        $expiryStatus = '';
        if(isset($_REQUEST['limit']))
        {
            $limit = " LIMIT ".$_REQUEST['limit'];
        }
        if(isset($_REQUEST['offset']))
        {
            $offset = " OFFSET ".$_REQUEST['offset'];
        }
        if(isset($_REQUEST['Active']))
        {
            if($_REQUEST['Active'] == '1'){
                $expiryStatus = "a.active = 1";
             }elseif($_REQUEST['Active'] == 'no'){
                $expiryStatus = "a.active = 0";
             }
        }
        //error_log(print_r($_REQUEST['Active'], TRUE));
        $wheres;
        if(isset($_REQUEST['filter'])){
            $search = json_decode(stripslashes($_REQUEST['filter']));
            // print_r($search);
            $wheres = array();
            foreach($search as $sk=>$sv){
                
                if($sk == 'id'){
                    $wheres[] = "a.id = ".$sv;
                }

                if($sk == 'Slug'){
                    $wheres[] = "a.couponcode LIKE '".$sv."%'";
                } 

                if($sk == 'EMSOwnerID'){
                    $wheres[] = "co.ownerID = ".$sv;
                }

                if($sk == 'ExpiryDate')
                {
                    $wheres[] = "expirationDate BETWEEN '".date('Y-m-d 00:00:00', strtotime($sv))."' AND '".date('Y-m-d 23:59:59', strtotime($sv))."' ";
                }
                
            } 
                  
        }
        
        if($expiryStatus != '')
            $wheres[] = $expiryStatus;   
        if(!empty($wheres))
            $where .= " WHERE ".implode(" AND ", $wheres)."";

        if(isset($_REQUEST['sort'])){
            if($_REQUEST['sort'] == 'id'){
                $orderBy = " ORDER BY a.id ".$_REQUEST['order'];    
            }

            if($_REQUEST['sort'] == 'Name'){
                $orderBy = " ORDER BY a.name ".$_REQUEST['order'];    
            }

            if($_REQUEST['sort'] == 'ExpiryStatus'){
                $orderBy = " ORDER BY active ".$_REQUEST['order'];    
            }

            if($_REQUEST['sort'] == 'ExpiryDate'){
                $orderBy = " ORDER BY a.expirationDate ".$_REQUEST['order'];    
            }  
        }

        $joins = " INNER JOIN wp_gpxOwnerCreditCoupon_activity ca ON ca.couponID = a.id INNER JOIN wp_gpxOwnerCreditCoupon_owner co ON co.couponID = a.id ";
        $tsql = "SELECT COUNT(*) FROM (SELECT a.* FROM wp_gpxOwnerCreditCoupon a ".$joins.$where." GROUP BY a.id) as aaa";
        $res['total'] = (int) $wpdb->get_var($tsql);
        //added a cron to switch active status daily
//         $sql = "SELECT a.*, CASE WHEN expirationDate >= ".date('Y-m-d')." THEN 'Active' ELSE 'Inactive' END AS ExpiryStatus FROM wp_gpxOwnerCreditCoupon a ".$joins.$where.' GROUP BY a.id '.$orderBy.$limit.$offset;
        $sql = "SELECT a.* FROM wp_gpxOwnerCreditCoupon a ".$joins.$where.' GROUP BY a.id '.$orderBy.$limit.$offset;
        //error_log($sql);
        $coupons = $wpdb->get_results($sql);

        
        $i = 0;
        $data = array();
        foreach($coupons as $coupon)
        {
            $redeemed = [];
            $amount = [];
            $redeemed[] = 0;
            $amount[] = 0;
            $sql = "SELECT * FROM wp_gpxOwnerCreditCoupon_activity WHERE couponID=".$coupon->id;
            $activities = $wpdb->get_results($sql);
            
            $coupon->activity = '';
            $allActivity = [];
            $activityAgents = [];
            foreach($activities as $activity)
            {
                if(!isset($agents[$activity->userID]))
                {
                    $agentmeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $activity->userID ) );
                    $agents[$activity->userID] = $agentmeta->first_name." ".$agentmeta->last_name;
                }
                
                $activityAgents[] = $agents[$activity->userID];
                $allActivity[] = 'Activity: '.$activity->activity.' Amount: '.$activity->amount.' By: '.$agents[$activity->userID].' '.stripslashes($activity->activity_comments);
                
                if($activity->activity == 'transaction')
                {
                    $redeemed[] = $activity->amount;
                }
                else
                {
                    $amount[] = $activity->amount;
                }
            }
            
            $firstAgent = $activityAgents[0];
            
            if($coupon->single_use == 1 && array_sum($redeemed) > 0)
            {
                $balance = 0;
            }
            else
            {
                $balance = array_sum($amount) - array_sum($redeemed);
            }
            
            $sql = "SELECT * FROM wp_gpxOwnerCreditCoupon_owner WHERE couponID=".$coupon->id;
            $owners = $wpdb->get_results($sql);
            
            $membernos = [];
            foreach($owners as $owner)
            {
                $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $owner->ownerID ) );
                
                if(isset($usermeta->DAEMemberNo))
                {
                    $membernos[] = $usermeta->DAEMemberNo;
                }
            }
            
            switch($coupon->active)
            {
                case 0:
                    $active = "No";
                    break;
                    
                case 1:
                    $active = "Yes";
                    break;
            }
            
            switch($coupon->singleuse)
            {
                case 0:
                    $singleuse = "No";
                    break;
                    
                case 1:
                    $singleuse = "Yes";
                    break;
            }
            $expirationDate = '';
            if($coupon->expirationDate != '')
            {
                $expirationDate = '<div data-date="'.strtotime($coupon->expirationDate).'">'.date('m/d/Y', strtotime($coupon->expirationDate)).'</div>';
            }
            $data[$i]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=promos_deccouponsedit&id='.$coupon->id.'"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
            $data[$i]['id'] = $coupon->id;
            $data[$i]['Name'] = stripslashes($coupon->name);
            $data[$i]['Slug'] = $coupon->couponcode;
            $data[$i]['EMSOwnerID'] = implode(",", $membernos);
            $data[$i]['Balance'] = $balance;
            $data[$i]['Redeemed'] = array_sum($redeemed);
            $data[$i]['SingleUse'] = $singleuse;
            $data[$i]['ExpiryDate'] = $expirationDate;
            $data[$i]['ExpiryStatus'] = $active;
            $data[$i]['comments'] = $coupon->comments;
            $data[$i]['IssuedOn'] = date('m/d/Y H:i', strtotime($coupon->created_date));
            $data[$i]['IssuedBy'] = $firstAgent;
            $data[$i]['Activity'] = implode("; ", $allActivity);
            $i++;
        }
        $res['rows'] = $data;
        return $res;
    }
    /*
     * Return GPX Promos
     * Retrieve all promos
     * @boolean $active
     */
    public function return_gpx_promos($active='')
    {
        global $wpdb;
        $where = '';
        if(!empty($_REQUEST['Active']))
        {
            if($_REQUEST['Active'] == 'no')
            {
                $_REQUEST['Active'] = '0';
            }
            $where = "WHERE Active='".$_REQUEST['Active']."'";
        }
        $sql = "SELECT * FROM wp_specials ".$where;
        $promos = $wpdb->get_results($sql);
        $i = 0;
        $data = array();
        
        foreach($promos as $promo)
        {
            
            $properties = json_decode($promo->Properties);
            $redeemed = 'NA';
            if($promo->Type == 'coupon')
                $redeemed = $promo->redeemed;
                $data[$i]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=promos_edit&id='.$promo->id.'"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                $data[$i]['Type'] = ucfirst($promo->Type);
                $data[$i]['id'] = $promo->id;
                $data[$i]['Name'] = stripslashes($promo->Name);
                $data[$i]['Slug'] = '<a href="'.get_permalink('229').$promo->Slug.'" target="_blank">'.$promo->Slug.'</a>';
                $data[$i]['TransactionType'] = ucfirst($properties->transactionType);
                $data[$i]['Availability'] = ucfirst($properties->availability);
                $data[$i]['TravelStartDate'] = date("m/d/y", strtotime($promo->TravelStartDate));
                $data[$i]['TravelEndDate'] = date("m/d/y", strtotime($promo->TravelEndDate));
                $data[$i]['Redeemed'] = $redeemed;
                switch($promo->Active)
                {
                    case 0:
                        $active = "No";
                        break;
                        
                    case 1:
                        $active = "Yes";
                        break;
                }
                $data[$i]['Active'] = $active;
                $i++;
        }
        return $data;
    }
    /*
     * Return GPX Promo Auto Coupons
     * Retrieve Auto Coupons
     *
     */
    public function return_gpx_promoautocoupons()
    {
        global $wpdb;
        
        $sql = "SELECT * FROM wp_gpxAutoCoupon";
        $acs = $wpdb->get_results($sql);
        $i = 0;
        $data = array();
        foreach($acs as $ac)
        {
            $user = get_userdata($ac->user_id);
            if(isset($user) && !empty($user))
                $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $ac->user_id ) );
                $sql = "SELECT b.resortName, b.checkIn FROM wp_gpxTransactions a
                    INNER JOIN wp_properties b ON  a.weekId = b.weekId
                    WHERE a.id='".$ac->transaction_id."'";
                $transaction = $wpdb->get_row($sql);
                
                $sql = "SELECT Slug FROM wp_specials WHERE id='".$ac->coupon_id."'";
                $special = $wpdb->get_row($sql);
                
                $data[$i]['Name'] = $usermeta->FirstName1." ".$usermeta->LastName1;
                $data[$i]['Transaction'] = $transaction->resortName."<br>".$transaction->checkIn;
                $data[$i]['Coupon'] = $special->Slug."-".$ac->coupon_hash;
                switch($ac->used)
                {
                    case 0:
                        $used = "No";
                        break;
                        
                    case 1:
                        $used = "Yes";
                        break;
                }
                $data[$i]['Used'] = $used;
                $i++;
        }
        return $data;
    }
    
    public function return_gpx_properties($filters='')
    {
        global $wpdb;
        $props = '';
        
        $sql = "SELECT a.id, a.price, a.WeekEndpointID, a.resortId, a.resortName, a.country, b.Description, b.ImagePath1  FROM wp_properties a INNER JOIN wp_resorts b ON b.ResortID=a.resortId";
        $props = $wpdb->get_results($sql);
        
        return $props;
    }
    
    public function return_gpx_resorts_by_name($filters='')
    {
        global $wpdb;
        $wheres = 'WHERE active = 1';
        if(empty($filters))
            $wheres .= " AND featured = '1'";
            
            $sql = "SELECT ResortName FROM wp_resorts ".$wheres." ORDER BY ResortName";
            $rows = $wpdb->get_results($sql);
            
            foreach($rows as $row)
            {
                $output[] = $row->ResortName;
            }
            
            return $output;
            
    }
    
    public function return_get_gpx_resorttaxes()
    {
        global $wpdb;
        
        $sql = "SELECT * FROM wp_gpxTaxes";
        $taxes = $wpdb->get_results($sql);
        
        $i = 0;
        $data = array();
        foreach($taxes as $tax)
        {
            $user = get_userdata($ac->user_id);
            if(isset($user) && !empty($user))
                $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $ac->user_id ) );
                $sql = "SELECT b.resortName, b.checkIn FROM wp_gpxTransactions a
                    INNER JOIN wp_properties b ON  a.weekId = b.weekId
                    WHERE a.id='".$ac->transaction_id."'";
                $transaction = $wpdb->get_row($sql);
                
                $sql = "SELECT Slug FROM wp_specials WHERE id='".$ac->coupon_id."'";
                $special = $wpdb->get_row($sql);
                
                $data[$i]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=resorts_taxesedit&id='.$tax->ID.'"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                $data[$i]['authority'] = $tax->TaxAuthority;
                $data[$i]['city'] = $tax->City;
                $data[$i]['state'] = $tax->State;
                $data[$i]['country'] = $tax->Country;
                $i++;
        }
        return $data;
    }
    
    public function return_taxesedit($id)
    {
        global $wpdb;
        
        $sql = "SELECT * FROM wp_gpxTaxes WHERE id='".$id."'";
        $result = $wpdb->get_row($sql);
        
        return $result;
    }
    /*new data*/
    public function return_gpx_edit_gpx_resort()
    {
        global $wpdb;
        
        $output = array('success'=>false);
        if(isset($_POST['ResortID']))
        {
            $ResortID = $_POST['ResortID'];
            unset($_POST['ResortID']);
            foreach($_POST as $key=>$value)
            {
                $where = array('ResortID'=>$ResortID, 'meta_key'=>$key);
                $wpdb->delete('wp_resorts_meta', $where);
                $id = '1';
                if(!empty($value))
                {
                    $value = stripslashes($value);
                    $data = array('ResortID'=>$ResortID, 'meta_key'=>$key, 'meta_value'=>$value);
                    $wpdb->replace('wp_resorts_meta', $data);
                    $id = $wpdb->insert_id;
                }
                
            }
            
            if(!empty($id))
                $output = array('success'=>true, 'msg'=>'Edit Successful!');
                else
                    $output['msg'] = 'Nothing to update!';
        }
        else
            $output['msg'] = 'Resort not updated!';
            return $output;
    }
    
    public function return_gpx_featured_gpx_resort()
    {
        global $wpdb;
        
        $featured = $_POST['featured'];
        
        if($featured == 0)
        {
            $newstatus = 1;
            $msg = "Resort is featured!";
            $fa = "fa-check-square";
        }
        else
        {
            $newstatus = 0;
            $msg = "Resort is not featured!";
            $fa = "fa-square";
        }
        
        $wpdb->update('wp_resorts', array('featured'=>$newstatus), array('ResortID'=>$_POST['resort']));
        
        $data = array('success'=>true, 'msg'=>$msg, 'fastatus'=>$fa, 'status'=>$newstatus);
        
        return $data;
    }
    
    public function return_gpx_ai_gpx_resort()
    {
        global $wpdb;
        
        $ai = $_POST['ai'];
        
        if($ai == 0)
        {
            $newstatus = 1;
            $msg = "Resort is AI!";
            $fa = "fa-check-square";
        }
        else
        {
            $newstatus = 0;
            $msg = "Resort is not AI!";
            $fa = "fa-square";
        }
        
        $wpdb->update('wp_resorts', array('ai'=>$newstatus), array('ResortID'=>$_POST['resort']));
        
        $data = array('success'=>true, 'msg'=>$msg, 'fastatus'=>$fa, 'status'=>$newstatus);
        
        return $data;
    }
    
    public function return_guest_fees_gpx_resort()
    {
        global $wpdb;
        
        $enabled = $_POST['enabled'];
        
        if($enabled == 0)
        {
            $newstatus = 1;
            $msg = "Guest fees for this resort are enabled!";
            $fa = "fa-check-square";
        }
        else
        {
            $newstatus = 0;
            $msg = "Guest fees for this resort are not enabled!";
            $fa = "fa-square";
        }
        
        $wpdb->update('wp_resorts', array('guestFeesEnabled'=>$newstatus), array('ResortID'=>$_POST['resort']));
        
        $data = array('success'=>true, 'msg'=>$msg, 'gfstatus'=>$fa, 'status'=>$newstatus);
        
        return $data;
    }
    
    public function return_resort_attribute_new($post)
    {
        global $wpdb;
        
        extract($post);
        
        $wpdb->update('wp_resorts', array($type=>$val), array('ResortID'=>$resortID));
        if(get_current_user_id() == 5)
        {
//             echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
//             echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
        }
        $sql = "SELECT id, meta_value FROM wp_resorts_meta WHERE ResortID='".$resortID."' AND meta_key='".$type."'";
        $rm = $wpdb->get_row($sql);
        if(get_current_user_id() == 5)
        {
//             echo '<pre>'.print_r($rm, true).'</pre>';
        }
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
        
        
        //$attributeKey is the old date range
        $attributeKey = '0';
        $deleteVal = [];
        if(!empty($oldfrom))
        {
            $oldfrom = date('Y-m-d 00:00:00', strtotime($oldfrom));
//             $oldfrom = DateTime::createFromFormat('Y-m-d H:i:s', (new DateTime())->setTimestamp($oldfrom)->format('Y-m-d 00:00:00'))->getTimestamp();
            $attributeKey = strtotime($oldfrom);
            if(!empty($oldorder))
            {
                $attributeKey += $oldorder;
            }
        }
        if(!empty($oldto))
        {
            $oldto = date('Y-m-d 00:00:00', strtotime($oldto));
//             $oldto = DateTime::createFromFormat('Y-m-d H:i:s', (new DateTime())->setTimestamp($oldto)->format('Y-m-d 00:00:00'))->getTimestamp();
            $attributeKey .= "_".strtotime($oldto);
        }
        //updateAttributeKey is the new date range
        $newAttributeKey = 0;
        if(!empty($from))
        {
            $from = date('Y-m-d 00:00:00', strtotime($from));
//             $from = DateTime::createFromFormat('Y-m-d H:i:s', (new DateTime())->setTimestamp($from)->format('Y-m-d 00:00:00'))->getTimestamp();
            $newAttributeKey = strtotime($from);
            if(!empty($oldorder))
            {
                $newAttributeKey += $oldorder;
            }
        }
        if(!empty($to))
        {
            $to = date('Y-m-d 00:00:00', strtotime($to));
//             $to = DateTime::createFromFormat('Y-m-d H:i:s', (new DateTime())->setTimestamp($to)->format('Y-m-d 00:00:00'))->getTimestamp();
            $newAttributeKey .= "_".strtotime($to);
        }
        
        if(empty($rm))
        {
            $sql = 'SELECT '.$type.' FROM wp_resorts WHERE ResortID="'.$resortID.'"';
            $res = $wpdb->get_row($sql);
            
            if(!empty($res))
            {
                $toSet = json_decode($res->$type);
                $metaValue[$newAttributeKey] = $toSet;
                $insert = json_encode($metaValue);
                $wpdb->insert('wp_resorts_meta', array('ResortID'=>$resortID, 'meta_key'=>$type, 'meta_value'=>$insert));
                $updateID = $wpdb->insert_id;
                $sql = "SELECT id, meta_value FROM wp_resorts_meta WHERE id='".$updateID."'";
                $rm = $wpdb->get_row($sql);
            }
        }
        

        
        if(!empty($rm))
        {
            $metaValue = json_decode($rm->meta_value, true);
            
            if(in_array($type, $nodates))
            {
                $ark = array_keys($metaValue);
                $newAttributeKey = $attributeKey = $ark[0];
            }
            
            if(isset($metaValue[$attributeKey]))
            {
                //                 $attributes[] = $metaValue[$attributeKey];
                foreach($metaValue[$attributeKey] as $v)
                {
                    $attributes[] = $v;
                }
                //if the' $attributeKey != $newAttibuteKey then this is an update -- remove the original one
                unset($metaValue[$attributeKey]);
                //                 if(!empty($val))
                //                 {
                if(isset($descs))
                {
                    $insertVal[] = [
                        'path' => [
                            'booking' => $bookingpathdesc,
                            'profile' => $resortprofiledesc,
                        ],
                        'desc' => $val,
                    ];
                }
                else
                {
                    $insertVal[] = $val;
                }
                //                 }
                if(!empty($list))
                {
                    foreach($list as $l)
                    {
                        $insertVal[] = $l;
                    }
                }
                foreach($insertVal as $newVal)
                {
                    if(!empty($newVal))
                    {
                        $attributes[] = $newVal;
                    }
                }
                $count = count($attributes);
                
                $metaValue[$newAttributeKey] = $attributes;
            }
            else
            {
                if(!empty($val))
                {
                    if(isset($descs))
                    {
                        $insertVal[] = [
                            'path' => [
                                'booking' => $bookingpathdesc,
                                'profile' => $resortprofiledesc,
                            ],
                            'desc' => $val,
                        ];
                    }
                    else
                    {
                        $insertVal[] = $val;
                    }
                }
                elseif($bookingpathdesc || $resortprofiledesc)
                {
                    $insertVal[] = [
                        'path' => [
                            'booking' => $bookingpathdesc,
                            'profile' => $resortprofiledesc,
                        ],
                        'desc' => $val,
                    ];
                }
                elseif($descs)
                {
                    $insertVal[] = [
                        'path' => [
                            'booking' => $bookingpathdesc,
                            'profile' => $resortprofiledesc,
                        ],
                        'desc' => $val,
                    ];
                }
                if(!empty($list))
                {
                    foreach($list as $l)
                    {
                        $insertVal[] = $l;
                    }
                    foreach($insertVal as $newVal)
                    {
                        if(!empty($newVal))
                        {
                            $metaValue[$newAttributeKey] = $newVal;
                        }
                    }
                }
                else
                {
                    $metaValue[$newAttributeKey] = $insertVal;
                }
                
                $count = count($metaValue[$newAttributeKey]);
            }
            if($val == 'remove' || $val == 'delete')
            {
                //this should be removed...
                unset($metaValue[$newAttributeKey]);
            }
            $wpdb->update('wp_resorts_meta', array('meta_value'=>json_encode($metaValue)), array('id'=>$rm->id));
        }
        else
        {
            $attributes[] = $val;
            $count = count($attributes);
            
            if(isset($descs))
            {
                $insert[$newAttributeKey][] = [
                    'path' => [
                        'booking' => $bookingpathdesc,
                        'profile' => $resortprofiledesc,
                    ],
                    'desc' => $val,
                ];
            }
            else
            {
                $insert = [
                    $newAttributeKey=>$attributes
                ];
            }
            
            $wpdb->insert('wp_resorts_meta', array('ResortID'=>$resortID, 'meta_key'=>$type, 'meta_value'=>json_encode($insert)));
        }
        if(get_current_user_id() == 5)
        {
//             echo '<pre>'.print_r($resortID, true).'</pre>';
        }
        
        $msg = 'Insert Successful';
        
        $data = array('success'=>true, 'msg'=>$msg, 'count'=>$count);
        
        return $data;
    }
    
    public function return_gpx_resort_repeatable_remove($post)
    {
        global $wpdb;
        
        extract($post);
        
        $attributeKey = '0';
        if(!empty($from))
        {
//             $from = DateTime::createFromFormat('Y-m-d H:i:s', (new DateTime())->setTimestamp($from)->format('Y-m-d 00:00:00'))->getTimestamp();
            $from = date('Y-m-d H:i:s', strtotime($from.'-12 hours'));
            $attributeKey = strtotime($from);
            if(isset($oldorder) && !empty($oldorder) && strtolower($oldorder) != 'undefined')
            {
                $attributeKey += $oldorder;
            }
        }
        if(!empty($to))
        {
//             $to = DateTime::createFromFormat('Y-m-d H:i:s', (new DateTime())->setTimestamp($to)->format('Y-m-d 00:00:00'))->getTimestamp();
            $to = date('Y-m-d 23:59:59', strtotime($to.'+1 day'));
            $attributeKey .= "_".strtotime($to);
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
            
            'unitFacilities'=>'attributes',
            'resortFacilities'=>'attributes',
            'areaFacilities'=>'attributes',
            'UnitConfig'=>'attributes',
            //             'configuration'=>'attributes',
        //             'resortConditions'=>'attributes',
            'GuestFeeAmount' => 'fees',
            'resortFees' => 'fees',
            'RentalFeeAmount' => 'fees',
            'ExchangeFeeAmount' => 'fees',
            'CPOFeeAmount' => 'fees',
            'GuestFeeAmount' => 'fees',
            'UpgradeFeeAmount' => 'fees',
        ];
        $ins = [];
        foreach($rmGroups as $rmK=>$rmV)
        {
            if($type == $rmV)
            {
                $ins[] = $rmK;
            }
        }
        
        $mkIns = '';
        if(!empty($ins))
        {
            $mkIns = " AND meta_key IN ('".implode("', '", $ins)."')";
        }
        
        $sql = "SELECT id, meta_value FROM wp_resorts_meta WHERE ResortID='".$resortID."'".$mkIns;
        $rms = $wpdb->get_results($sql);
     
        
        if(!empty($rms))
        {
            foreach($rms as $rm)
            {
                $metaValue = json_decode($rm->meta_value, true);
                foreach($metaValue as $mk=>$mv)
                {
                    $splitAttribute = explode("_", $mk);
                    
                    if(!empty($from))
                    {
                        
                        $fromR1 = strtotime($from.' -36 hours');
                        $fromR2 = strtotime($from.' +43 hours');
                        if(substr($splitAttribute[0], 0, 10) >= $fromR1 && substr($splitAttribute[0], 0, 10) <= $fromR2)
                        {
                            $attributeKey = $mk;
                        }
                        if(!empty($to))
                        {
                            $attributeKey = $attributeKey;
                            $toR1 = strtotime($to.' -36 hours');
                            $toR2 = strtotime($to.' +36 hours');
                            
                            if(substr($splitAttribute[1], 0, 10) >= $toR1 && substr($splitAttribute[1], 0, 10) <= $toR2)
                            {
                                $attributeKey = $mk;
                            }
                        }
                    }
                }
                
                unset($metaValue[$attributeKey]);
                
                $wpdb->update('wp_resorts_meta', array('meta_value'=>json_encode($metaValue)), array('id'=>$rm->id));
            }
        }
        
        $msg = 'Remove Successful';
        
        $data = array('success'=>true, 'msg'=>$msg);
        
        return $data;
    }
    
    public function return_resort_attribute_remove($post)
    {
        global $wpdb;
        
        extract($post);
        
        $attributeKey = '0';
        if(!empty($from))
        {
            $from = date('Y-m-d 00:00:00', strtotime($from));
            $attributeKey = strtotime($from);
        }
        if(!empty($to))
        {
            $to = date('Y-m-d 00:00:00', strtotime($to));
            $attributeKey .= "_".strtotime($to);
        }
        
        $sql = "SELECT id, meta_value FROM wp_resorts_meta WHERE ResortID='".$resortID."' AND meta_key='".$type."'";
        $rm = $wpdb->get_row($sql);
        
        if(!empty($rm))
        {
            $metaValue = json_decode($rm->meta_value, true);
            if(!isset($metaValue[$attributeKey]))
            {
                end($metaValue);
                $attributeKey = key($metaValue);
                reset($metaValue);
            }
            
            $attributes = $metaValue[$attributeKey];
            unset($attributes[$item]);
            $metaValue[$attributeKey] = $attributes;
            
            $wpdb->update('wp_resorts_meta', array('meta_value'=>json_encode($metaValue)), array('id'=>$rm->id));
        }
        
        $msg = 'Remove Successful';
        
        $data = array('success'=>true, 'msg'=>$msg);
        
        return $data;
    }
    
    public function return_gpx_resort_attribute_reorder($post)
    {
        global $wpdb;
        
        extract($post);
        
        $attributeKey = '0';
        if(!empty($from))
        {
            $attributeKey = strtotime($from);
        }
        if(!empty($to))
        {
            $attributeKey .= "_".strtotime($to);
        }
        
        $sql = "SELECT id, meta_value FROM wp_resorts_meta WHERE ResortID='".$resortID."' AND meta_key='".$type."'";
        $rm = $wpdb->get_row($sql);
        
        if(!empty($rm))
        {
            $metaValue = json_decode($rm->meta_value, true);
            $updateID = $rm->id;
        }
        else
        {
            $sql = 'SELECT '.$type.' FROM wp_resorts WHERE ResortID="'.$resortID.'"';
            $res = $wpdb->get_row($sql);
            
            if(!empty($res))
            {
                $toSet = json_decode($res->$type);
                $metaValue[$attributeKey] = $toSet;
                $insert = json_encode($metaValue);
                $wpdb->insert('wp_resorts_meta', array('ResortID'=>$resortID, 'meta_key'=>$type, 'meta_value'=>$insert));
                $updateID = $wpdb->insert_id;
            }
        }
        if(!empty($metaValue))
        {
            if(!isset($metaValue[$attributeKey]))
            {
                end($metaValue);
                $attributeKey = key($metaValue);
                reset($metaValue);
            }
            
            $attributes = $metaValue[$attributeKey];
            
            foreach($order as $o)
            {
                $newOrder[] = $attributes[$o];
            }
            $metaValue[$attributeKey] = $newOrder;
            
            $wpdb->update('wp_resorts_meta', array('meta_value'=>json_encode($metaValue)), array('id'=>$updateID));
        }
        
        $msg = 'Reorder Successful';
        
        $data = array('success'=>true, 'msg'=>$msg);
        
        return $data;
    }
    
    public function return_gpx_resort_image_reorder($post)
    {
        global $wpdb;
        
        extract($post);
        
        $sql = "SELECT id, meta_value FROM wp_resorts_meta WHERE ResortID='".$resortID."' AND meta_key='".$type."'";
        $rm = $wpdb->get_row($sql);
        
        if(!empty($rm))
        {
            $attributes = json_decode($rm->meta_value, true);
            foreach($order as $o)
            {
                $newOrder[] = $attributes[$o];
            }
            
            
            $wpdb->update('wp_resorts_meta', array('meta_value'=>json_encode($newOrder)), array('id'=>$rm->id));
        }
        else
        {
            foreach($order as $o)
            {
                $newOrder[] = $attributes[$o];
            }
            $wpdb->insert('wp_resorts_meta', array('meta_value'=>json_encode($newOrder), 'meta_key'=>'images'));
        }
        $msg = 'Reorder Successful';
        
        $data = array('success'=>true, 'msg'=>$msg);
        
        return $data;
    }
    
    /*
     * old dataat
     */
    
    public function return_update_gpx_resorttax_id($post)
    {
        global $wpdb;
        
        $data = array('error'=>true, 'msg'=>'There was an error');
        if(isset($post) && !empty($post['resortID']))
        {
            if($wpdb->update('wp_resorts', array('taxID'=>$post['taxID']), array('ResortID'=>$post['resortID'])))
                $data = array('success'=>true, 'msg'=>'Resort Tax Updated');
                else
                    $data['msg'] = 'Nothing to update';
        }
        
        return $data;
    }
    public function return_add_gpx_resorttax($post)
    {
        global $wpdb;
        
        $output = array('error'=>true, 'msg'=>'You must submit something');
        
        if(isset($post) && !empty($post))
        {
            $add = array(
                'TaxAuthority' => $post['TaxAuthority'],
                'City' => $post['City'],
                'State' => $post['State'],
                'Country' => $post['Country'],
            );
            for($i=1;$i<=3;$i++)
            {
                if(isset($post['TaxPercent'.$i]) && !empty($post['TaxPercent'].$i))
                    $add['TaxPercent'.$i] = $post['TaxPercent'.$i];
                    if(isset($post['FlatTax'.$i]) && !empty($post['FlatTax'].$i))
                        $add['FlatTax'.$i] = $post['FlatTax'.$i];
            }
            if($wpdb->insert('wp_gpxTaxes', $add))
            {
                $msg = 'Tax Added';
                $insertID = $wpdb->insert_id;
                if(isset($post['resortID']) && !empty($post['resortID']) && !empty($insertID))
                {
                    if($wpdb->update('wp_resorts', array('taxID'=>$insertID), array('ResortID'=>$post['resortID'])))
                        $msg .= ' and Resort Updated';
                }
                $output = array('success'=>true, 'msg'=>$msg);
            }
            else
                $output['msg'] = 'There was an error adding the tax';
        }
        
        return $output;
    }
    
    public function return_edit_gpx_resorttax($post)
    {
        global $wpdb;
        
        $data = array('error'=>true, 'msg'=>'There was an error updating');
        if(isset($post) && !empty($post['taxID']))
        {
            $update = array(
                'TaxAuthority' => $post['TaxAuthority'],
                'City' => $post['City'],
                'State' => $post['State'],
                'Country' => $post['Country'],
            );
            for($i=1;$i<=3;$i++)
            {
                if(isset($post['TaxPercent'.$i]) && !empty($post['TaxPercent'].$i))
                    $update['TaxPercent'.$i] = $post['TaxPercent'.$i];
                    if(isset($post['FlatTax'.$i]) && !empty($post['FlatTax'].$i))
                        $update['FlatTax'.$i] = $post['FlatTax'.$i];
            }
            $wpdb->update('wp_gpxTaxes', $update, array('ID'=>$post['taxID']));
            $data = array('success'=>true, 'msg'=>'Tax Updated');
        }
        
        return $data;
    }
    
    public function return_edit_tax_method($post)
    {
        global $wpdb;
        
        $data = array('error'=>true, 'mgs'=>'Tax Method Not Updated');
        if(isset($post) && !empty($post['ResortID']) && !empty($post['taxMethod']))
        {
            if($wpdb->update('wp_resorts', array('taxMethod'=>$post['taxMethod']), array('ResortID'=>$post['ResortID'])))
                $data = array('success'=>true, 'msg'=>'Tax Method Updated');
        }
        return $data;
    }
    
    public function return_gpx_active_gpx_resort()
    {
        global $wpdb;
        
        $active = $_POST['active'];
        
        if($active == 0)
        {
            $newstatus = 1;
            $msg = "Resort is Active!";
            $fa = "fa-check-square";
        }
        else
        {
            $newstatus = 0;
            $msg = "Resort is not active!";
            $fa = "fa-square";
        }
        
        $wpdb->update('wp_resorts', array('active'=>$newstatus), array('ResortID'=>$_POST['resort']));
        
        $data = array('success'=>true, 'msg'=>$msg, 'fastatus'=>$fa, 'status'=>$newstatus);
        
        return $data;
    }
    public function return_gpx_featured_gpx_region()
    {
        global $wpdb;
        
        $featured = $_POST['featured'];
        
        if($featured == 0)
        {
            $newstatus = 1;
            $msg = "Region is featured!";
            $fa = "fa-check-square";
        }
        else
        {
            $newstatus = 0;
            $msg = "Region is not featured!";
            $fa = "fa-square";
        }
        
        $wpdb->update('wp_gpxRegion', array('featured'=>$newstatus), array('id'=>$_POST['region']));
        $data = array('success'=>true, 'msg'=>$msg, 'fastatus'=>$fa, 'status'=>$newstatus);
        
        return $data;
    }
    public function return_is_gpr()
    {
        global $wpdb;
        
        $gpr = $_POST['gpr'];
        
        if($gpr == 0)
        {
            $newstatus = 1;
            $msg = "GPR Resort!";
            $fa = "fa-check-square";
        }
        else
        {
            $newstatus = 0;
            $msg = "Not GPR Resort!";
            $fa = "fa-square";
        }
        
        $wpdb->update('wp_resorts', array('gpr'=>$newstatus), array('ResortID'=>$_POST['resort']));
        if(isset($_GET['resort_debug']))
        {
            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
            echo '<pre>'.print_r($wpdb->last_update, true).'</pre>';
        }
        $data = array('success'=>true, 'msg'=>$msg, 'fastatus'=>$fa, 'status'=>$newstatus);
        
        return $data;
    }
    
    public function return_gpx_hidden_gpx_region()
    {
        global $wpdb;
        
        $hidden = $_POST['hidden'];
        
        if($hidden == 0)
        {
            $newstatus = 1;
            $msg = "Region is hidden!";
            $fa = "fa-check-square";
        }
        else
        {
            $newstatus = 0;
            $msg = "Region is not hidden!";
            $fa = "fa-square";
        }
        
        $wpdb->update('wp_gpxRegion', array('ddHidden'=>$newstatus), array('id'=>$_POST['region']));
        $data = array('success'=>true, 'msg'=>$msg, 'fastatus'=>$fa, 'status'=>$newstatus);
        
        return $data;
    }
    
    public function return_gpx_region_list($country='', $region='')
    {
        global $wpdb;
        if(!empty($country) && empty($region))
            $sql = "SELECT a.id as rid, b.id, a.region FROM wp_daeRegion a
                    INNER JOIN wp_gpxRegion b ON b.RegionID=a.id
                    WHERE a.CountryID='".$country."'";
            elseif(!empty($region) && empty($country))
            $sql = "SELECT id, name as region FROM wp_gpxRegion WHERE parent='".$region."' ORDER BY name";
            $regions = $wpdb->get_results($sql);
            
            return $regions;
            
    }
    
    public function return_gpx_add_edit_region()
    {
        global $wpdb;
        
        
        $output = array('success'=>false);
        if(isset($_POST['usage_parent']) && !empty($_POST['usage_parent']))
        {
            $up = $_POST['usage_parent'];
        }
        elseif(isset($_POST['parent']) && !empty($_POST['parent']))
        {
            $up = $_POST['parent'];
        }
        if(isset($up) && !empty($up))
        {
            foreach($up as $key=>$value)
            {
                if(empty($value))
                    unset($up[$key]);
            }
            
            $parent = end($up);
            //edit region?
            if((isset($_POST['edit-region']) && !empty($_POST['edit-region'])) && (isset($_POST['id']) && !empty($_POST['id'])))
            {
                // we don't need to make a lot of changes if all we are doing is editing a name...
                $sql = "SELECT parent FROM wp_gpxRegion WHERE id='".$_POST['id']."'";
                $oldRegion = $wpdb->get_row($sql);
                
                if($parent == $oldRegion->parent)//it's the same
                {
                    $update = array(
                        'name'=>$_POST['edit-region'],
                        'displayName'=>$_POST['display-name']
                    );
                    $wpdb->update('wp_gpxRegion', $update, array('id'=>$_POST['id']));
                }
                else
                {
                    //remove the existing record
                    $wpdb->delete('wp_gpxRegion', array('id'=>$_POST['id']));
                    
                    $this->gpx_model->rebuild_tree(1, 0);
                    
                    sleep(2);
                    
                    $this->gpx_add_region($parent, $_POST['edit-region'], $_POST['id'], $_POST['reassign'], $_POST['display-name']);
                }
                $output = array('success'=>true, 'msg'=>'Successfully edited region!', 'type'=>'edit');
                
            }
            //add new region?
            elseif(isset($_POST['new-region']) && !empty($_POST['new-region']))
            {
                $this->gpx_add_region($parent, $_POST['new-region'], '', $_POST['reassign'], $_POST['display-name']);
                $output = array('success'=>true, 'msg'=>'Succesfully added region!');
            }
            else
                $output['msg'] = 'Error! Please check your information and try again.';
        }
        elseif(isset($_POST['remove']) && !empty($_POST['remove']))
        {
            //get the parent of this region
            $sql = "SELECT parent FROM wp_gpxRegion WHERE id='".$_POST['remove']."'";
            $row = $wpdb->get_row($sql);
            $parent = $row->parent;
            
            //reassign all resorts to parent
            $wpdb->update('wp_resorts', array('gpxRegionID'=>$parent), array('gpxRegionID'=>$_POST['remove']));
            
            //also reasign all direct children to the parent
            $wpdb->update('wp_gpxRegion', array('parent'=>$parent), array('parent'=>$_POST['remove']));
            
            //remove the existing record
            $wpdb->delete('wp_gpxRegion', array('id'=>$_POST['remove']));
            
            $this->gpx_model->rebuild_tree(1, 0);
            
            $output = array('success'=>true, 'msg'=>'Successfully removed region!');
        }
        else
            $output['mgs'] = 'Error! Please check your information and try again.';
            
            return $output;
            
    }
    
    public function gpx_add_region($parent, $newregion, $oldid='', $reassign = '', $displayName='')
    {
        global $wpdb;
        
        $sql = "SELECT lft,rght FROM wp_gpxRegion WHERE id='".$parent."'";
        $plr = $wpdb->get_row($sql);
        
        $right = $plr->rght;
        
        $sql = "UPDATE wp_gpxRegion SET lft=lft+2 WHERE lft>'".$right."'";
        $wpdb->query($sql);
        $sql = "UPDATE wp_gpxRegion SET rght=rght+2 WHERE rght>='".$right."'";
        $wpdb->query($sql);
        
        $update = array('name'=>$newregion,
            'parent'=>$parent,
            'lft'=>$right,
            'rght'=>$right+1,
            'displayName'=>$displayName
        );
        $wpdb->insert('wp_gpxRegion', $update);
        $newid = $wpdb->insert_id;
        if(!empty($oldid))
        {
            $update = array('parent'=>$newid);
            $wpdb->update('wp_gpxRegion', $update, array('parent'=>$oldid));
            $wpdb->update('wp_resorts', array('gpxRegionID'=>$newid), array('gpxRegionID'=>$oldid));
        }
        
        if(isset($reassign) && !empty($reassign))
        {
            $wpdb->update('wp_resorts', array('gpxRegionID'=>$newid), array('gpxRegionID'=>$parent));
        }
        
        //$this->gpx_model->rebuild_tree(1, 0);
    }
    
    public function return_get_dae_region($country)
    {
        global $wpdb;
        
        $sql = "SELECT RegionID, CountryID from wp_daeRegion WHERE CountryID='".$country."' AND region <> 'All'";
        $regions = $wpdb->get_results($sql);
        
        return $regions;
    }
    
    public function return_gpx_region($id)
    {
        global $wpdb;
        
        $sql = "SELECT * FROM wp_gpxRegion WHERE id='".$id."'";
        $region = $wpdb->get_row($sql);
        
        $data['name'] = $region->name;
        if(isset($region->parent) && $region->parent == 1)
            $sql = "SELECT  a.id as tid, a.name, a.RegionID, a.parent, b.CountryID, country FROM `wp_gpxRegion` a
                LEFT JOIN wp_daeRegion b ON a.RegionID = b.id
                LEFT JOIN wp_gpxCategory c ON b.CountryID = c.CountryID
                WHERE b.id='".$region->RegionID."'
                ORDER BY lft ASC";
            else
                $sql = "SELECT  a.id as tid, a.name, a.RegionID, a.parent, b.CountryID, country FROM `wp_gpxRegion` a
                LEFT JOIN wp_daeRegion b ON a.RegionID = b.id
                LEFT JOIN wp_gpxCategory c ON b.CountryID = c.CountryID
                WHERE lft < ".$region->lft." AND rght > ".$region->rght."
                ORDER BY lft ASC";
                $parents = $wpdb->get_results($sql);
                $i = 0;
                $pp = array();
                foreach($parents as $parent)
                {
                    
                    if(in_array($parent->name, $pp))
                        continue;
                        
                        $pp[] = $parent->name;
                        
                        if($parent->parent == 1)
                        {
                            $data['country']['id'] = $parent->CountryID;
                            $data['country']['name'] = $parent->country;
                            $data['listr'][$i+1] = $this->return_gpx_region_list($parent->CountryID, '');
                            $i++;
                            $data['parent'][$i]['id'] = $parent->RegionID;
                            $data['parent'][$i]['name']= $parent->name;
                            $data['parent'][$i]['tid']= $parent->tid;
                            $data['listr'][$i+1] = $this->return_gpx_region_list('', $parent->tid);
                        }
                        else
                        {
                            $data['parent'][$i]['id'] = $parent->tid;
                            $data['parent'][$i]['name'] = $parent->name;
                            $data['parent'][$i]['tid'] = $parent->tid;
                            $data['listr'][$i+1] = $this->return_gpx_region_list('', $parent->tid);
                        }
                        
                        $i++;
                }
                
                return $data;
    }
    public function return_gpx_regionsassignlist()
    {
        global $wpdb;
        $sql = "SELECT a.id, a.ResortName, a.Address1, a.Town, a.Region, a.Country, b.name as regionName FROM wp_resorts a
                INNER JOIN wp_gpxRegion b ON a.gpxRegionID = b.id";
        $regions = $wpdb->get_results($sql);
        $i = 0;
        foreach($regions as $region)
        {
            $data[$i]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=regions_assign&id='.$region->id.'"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
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
        foreach($regions as $region)
        {
            $data[$i]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=regions_assign&id='.$region->id.'"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
            $data[$i]['resort'] = $region->ResortName;
            $data[$i]['address1'] = $region->Address1;
            $data[$i]['city'] = $region->Town;
            $data[$i]['state'] = $region->Region;
            $data[$i]['country'] = $region->Country;
            $data[$i]['region'] = 'Unassigned';
            $i++;
        }
        return $data;
    }
    
    public function return_gpx_assign_region()
    {
        global $wpdb;
        $output = array('success'=>false);
        
        if(isset($_POST['hidden-region']) && $_POST['hidden-region'] == "Yes")
        {
            $wpdb->update('wp_gpxRegion', array('ddHidden'=>1), array('id'=>$_POST['orginalRegion']));
        }
        
        if(isset($_POST['usage_parent']) && !empty($_POST['usage_parent']))
        {
            while(empty(end($_POST['usage_parent'])))
            {
                array_pop($_POST['usage_parent']);
            }
            $newregion = end($_POST['usage_parent']);
            if(isset($_POST['resortid']) && !empty($_POST['resortid']))
            {
                $id = $_POST['resortid'];
                
                $wpdb->update('wp_resorts', array('gpxRegionID'=>$newregion), array('id'=>$id));
                $output = array('success'=>true, 'msg'=>'Successfully updated region!');
            }
            else
                $output['msg'] = 'Error -- ID Not Set! Please check your information and try again.';
        }
        else
            $output['mgs'] = 'Error -- No Region Selected! Please check your information and try again.';
            
            return $output;
    }
    
    public function return_gpx_resorts()
    {
        global $wpdb;
        
        $sql = "SELECT id, ResortName, Town, Region, Country, ai, active FROM wp_resorts";
        $resorts = $wpdb->get_results($sql);
        $i = 0;
        foreach($resorts as $resort)
        {
            $active = "Yes";
            $ai = "No";
            if($resort->ai == '1')
                $ai = "Yes";
                if($resort->active == '0')
                    $active = "No";
                    $data[$i]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=resorts_edit&id='.$resort->id.'"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                    $data[$i]['resort'] = $resort->ResortName;
                    $data[$i]['city'] = $resort->Town;
                    $data[$i]['state'] = $resort->Region;
                    $data[$i]['country'] = $resort->Country;
                    $data[$i]['ai'] = $ai;
                    $data[$i]['taID'] = $resort->taID;
                    $data[$i]['active'] = $active;
                    $i++;
        }
        
        return $data;
    }
    
    /*
     * gpx resort edit -- edit resort
     * @int $id
     * return (object) $row
     */
    public function return_gpx_resort($id='')
    {
        global $wpdb;
        
        $sql = "SELECT * FROM wp_resorts WHERE id='".$id."'";
        $row = $wpdb->get_row($sql);
        
        if(isset($_FILES['new_image'])){
            $image = $_FILES['new_image'];
            
            // HANDLE THE FILE UPLOAD
            // If the upload field has a file in it
            if(isset($image) && ($image['size'] > 0)) {
                
                // Get the type of the uploaded file. This is returned as "type/extension"
                $arr_file_type = wp_check_filetype(basename($image['name']));
                $uploaded_file_type = $arr_file_type['type'];
                
                // Set an array containing a list of acceptable formats
                $allowed_file_types = array('image/jpg','image/jpeg','image/gif','image/png');
                // If the uploaded file is the right format
                if(in_array($uploaded_file_type, $allowed_file_types)) {
                    // Options array for the wp_handle_upload function. 'test_upload' => false
                    $upload_overrides = array( 'test_form' => false );
                    // Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array
                    $uploaded_file = wp_handle_upload($image, $upload_overrides);
                    
                    // If the wp_handle_upload call returned a local path for the image
                    if(isset($uploaded_file['file'])) {
                        
                        //The new file URL
                        $new_file_url = $uploaded_file['url'];
                        
                        // The wp_insert_attachment function needs the literal system path, which was passed back from wp_handle_upload
                        $file_name_and_location = $uploaded_file['file'];
                        
                        // Generate a title for the image that'll be used in the media library
                        $file_title_for_media_library = $row->ResortName;
                        
                        // Set up options array to add this file as an attachment
                        
                        $imgTitle = addslashes($file_title_for_media_library);
                        if(isset($_POST['title']) && !empty($_POST['title']))
                        {
                            $imgTitle = $_POST['title'];
                        }
                        
                        $attachment = array(
                            'post_mime_type' => $uploaded_file_type,
                            'post_title' => $imgTitle,
                            'post_content' => '',
                            'post_status' => 'inherit'
                        );
                        
                        // Run the wp_insert_attachment function. This adds the file to the media library and generates the thumbnails. If you wanted to attch this image to a post, you could pass the post id as a third param and it'd magically happen.
                        $attach_id = wp_insert_attachment( $attachment, $file_name_and_location );
                        
                        if(isset($_POST['alt']) && !empty($_POST['alt']))
                        {
                            update_post_meta($attach_id, '_wp_attachment_image_alt', $_POST['alt']);
                            update_post_meta($attach_id, 'gpx_image_video', $_POST['video']);
                        }
                        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_name_and_location );
                        wp_update_attachment_metadata($attach_id,  $attach_data);
                        
                        
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
            
            'CommonArea'=>'ada',
            'GuestRoom'=>'ada',
            'GuestBathroom'=>'ada',
            'UponRequest'=>'ada',
            
            'UnitFacilities'=>'attributes',
            'ResortFacilities'=>'attributes',
            'AreaFacilities'=>'attributes',
            'UnitConfig'=>'attributes',
            //             'configuration'=>'attributes',
        //             'resortConditions'=>'attributes',
            'GuestFeeAmount' => 'fees',
            'resortFees' => 'fees',
            'ExchangeFeeAmount' => 'fees',
            'RentalFeeAmount' => 'fees',
            'CPOFeeAmount' => 'fees',
            'LateDepositFeeOverride' => 'fees',
            'UpgradeFeeAmount' => 'fees',
        ];
        
        $dates = [
            'alertnotes'=>['0'],
            'descriptions'=>['0'],
            'attributes'=>['0'],
            'ada'=>['0'],
            'fees'=>['0'],
        ];
        
        //set the default attributes
        foreach($rmGroups as $rmk=>$rmg)
        {
            if($rmg == 'attributes')
            {
                $setAttribute[$rmk] = $rmk;
            }
        }
        
        foreach($setAttribute as $sa)
        {
            if(isset($row->$sa) && !empty($row->$sa));
            {
                $defaultAttrs[$sa] = json_decode($row->$sa, true);
                $toSet[$sa] = $defaultAttrs[$sa];
            }
        }
        if(isset($defaultAttrs))
        {
            $row->defaultAttrs = $defaultAttrs;
        }
        
        $sql = "SELECT * FROM wp_resorts_meta WHERE ResortID='".$row->ResortID."'";
        $resortMetas = $wpdb->get_results($sql);
        if(!empty($resortMetas))
        {
            foreach($resortMetas as $meta)
            {
                unset($setAttribute[$meta->meta_key]);
                $dateorder = [];
                $key = $meta->meta_key;
                $rmDefaults[$key] = $row->$key;
                $row->$key = $meta->meta_value;
                $metaValue = json_decode($row->$key, true);
                if(is_array($metaValue))
                    foreach($metaValue as $mvKey=>$mvVal)
                    {
                        $dateorder[$mvKey] = $mvVal;
                        unset($dates[$rmGroups[$key]][0]);
                    }
                ksort($dateorder);
                foreach($dateorder as $doK=>$doV)
                {
                    
                    $dates[$rmGroups[$key]][$doK][$key] = $doV;
                }
            }
        }
        if(!empty($rmDefaults))
        {
            $row->rmdefaults = $rmDefaults;
        }
        //any resort meta attributes that aren't set should be set now...
        foreach($setAttribute as $sa)
        {
            if(!empty($toSet[$sa]))
            {
                $insertMetaValue[strtotime('today midnight')] = $toSet[$sa];
                $insert = json_encode($insertMetaValue);
                $wpdb->insert('wp_resorts_meta', array('ResortID'=>$row->ResortID, 'meta_key'=>$sa, 'meta_value'=>$insert));
            }
        }
        $row->dates = $dates;
        //is this the first time this resort has been updated?
        if(!isset($row->images))
        {
            //the image hasn't been updated -- let's get the ones set by DAE
            for($i=1;$i<=3;$i++)
            {
                $daeImage = 'ImagePath'.$i;
                if(!empty($row->$daeImage))
                {
                    $daeImages[] =
                    [
                        'type'=>'dae',
                        'src'=>$row->$daeImage
                    ];
                }
            }
            $row->images = json_encode($daeImages);
            $wpdb->insert('wp_resorts_meta', array('ResortID'=>$row->ResortID, 'meta_key'=>'images', 'meta_value'=>$row->images));
        }
        elseif(isset($new_file_url))
        {
            //add the new image to the end of the object
            $allImages = json_decode($row->images, true);
            $allImages[] = [
                'type'=>'uploaded',
                'id'=>$attach_id,
                'src'=>$new_file_url
            ];
            $row->images = json_encode($allImages);
            $wpdb->update('wp_resorts_meta', array('meta_value'=>$row->images), array('ResortID'=>$row->ResortID, 'meta_key'=>'images'));
            $row->newfile = true;
        }
        $sql = "SELECT * FROM wp_gpxTaxes";
        $row->taxes = $wpdb->get_results($sql);
        
        $wp_unit_type =  "SELECT *  FROM `wp_unit_type` WHERE `resort_id` ='".$row->id."'";
        $row->unit_types = $wpdb->get_results($wp_unit_type, OBJECT_K);
                
        //how many welcome emails?
        
        $resortID4Owner = substr($row->gprID, 0, 15);
        $sql = "SELECT DISTINCT ownerID FROM wp_owner_interval WHERE resortID='".$resortID4Owner."'";
        $allOwners = $wpdb->get_results($sql);

        foreach($allOwners as $oneOwner)
        {
            $owners4Count[] = $oneOwner->ownerID;
        }

        $sql = "SELECT COUNT(meta_value) as cnt FROM wp_usermeta WHERE meta_key='welcome_email_sent' AND user_id IN ('".implode("','", $owners4Count)."')";
        $ownerCnt = $wpdb->get_var($sql);
        $row->mlOwners = count($owners4Count) - $ownerCnt;
        
        return $row;
    }
    
    public function return_gpx_resorts_edit()
    {
        
    }
    
    public function return_gpx_store_resort()
    {
        global $wpdb;
        
        require_once WP_CONTENT_DIR.'/plugins/wp-store-locator/admin/class-geocode.php';
        $geocode = new WPSL_Geocode();
        
        $sql = "SELECT * FROM wp_resorts WHERE store_d=0";
        $results = $wpdb->get_results($sql);
        
        foreach($results as $result)
        {
            
            $ResortName = $result->ResortName;
            $Description = $result->Description;
            $Address1 = $result->Address1;
            $Address2 = $result->Address2;
            $Town = $result->Town;
            $Region = $result->Region;
            $Country = $result->Country;
            $ImagePath1 = $result->ImagePath1;
            $ResortID = $result->ResortID;
            $URL = home_url()."/resort-profile/?resort=".$result->id;
            $ll = $result->LatitudeLongitude;
            $llSplit = explode(',', $ll);
            
            $post_id = wp_insert_post(array (
                'post_type' => 'wpsl_stores',
                'post_title' => $ResortName,
                'post_content' => $Description,
                'post_status' => 'publish',
                'comment_status' => 'closed',   // if you prefer
                'ping_status' => 'closed',      // if you prefer
            ));
//             echo '<pre>'.print_r($post_id, true).'</pre>';
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
            $wpdb->update('wp_resorts', array('store_d'=>1), array('id'=>$result->id));
            
            $geocode->check_geocode_data($post_id);
            
        }
    }
    
    
    public function return_gpx_reportsearches()
    {
        global $wpdb;
        
        $sql = "SELECT a.*, b.user_login, b.display_name FROM wp_gpxMemberSearch a
                INNER JOIN wp_users b ON a.userID = b.ID
                where a.datetime between '2018-04-02' and '2018-04-30'";
        $searches = $wpdb->get_results($sql);
        $i = 0;
        foreach($searches as $search)
        {
            $searchVal = '';
            $jsondata = json_decode($search->data);
            foreach($jsondata as $key=>$value)
            {
                if(substr($key, 0,6) == 'resort')
                {
                    $searchVal = $value;
                }
            }
            if(empty($searchVal))
                continue;
                $data[$i]['resort'] = stripslashes($searchVal->ResortName);
                $data[$i]['ref'] = $searchVal->refDomain;
                $data[$i]['date'] = $searchVal->DateViewed;
                $data[$i]['resortID'] = $searchVal->id;
                $data[$i]['userID'] = $search->user_login;
                $data[$i]['user_name'] = $search->display_name;
                $data[$i]['search_location'] = stripslashes($searchVal->search_location);
                $data[$i]['search_month'] = $searchVal->search_month;
                $data[$i]['search_year'] = $searchVal->search_year;
                $i++;
        }
        
        return $data;
    }
    
    public function return_gpx_report_retarget()
    {
        
    }
    
    public function return_gpx_reportcsv()
    {
        $data = array();
        
        return $data;
        
    }
    
    public function return_mass_update_owners($orderby, $order, $offset='')
    {
        global $wpdb;
        
        //         $args = array(
        //             'role'=>'gpx_member',
        //             'meta_query'=>array(
        //                 array(
        //                     'key'=>'DAEMemberNo',
        //                     'compare'=>'NOT EXISTS',
        //                 ),
        //             ),
        //             'number' => 1,
        //             'orderby'=>$orderby,
        //             'order'=>$order
        //         );
        //         if(isset($offset) && !empty($offset))
            //             $args['offset'] = $offset;
        
            //         $users = get_users($args);
        
        $sql = "SELECT ID, user_login, user_email FROM wp_users
                WHERE ID NOT IN
                (SELECT b.user_id FROM wp_users a
                INNER JOIN wp_usermeta b ON a.ID=b.user_id
                WHERE user_registered > '2017-04-03 00:00:00'
                AND b.meta_key = 'DAEMemberNo')
                AND user_registered > '2017-04-03 00:00:00'
                LIMIT 50";
        //         $sql = "SELECT ID, user_login, user_email FROM wp_users WHERE display_name=''";
        $users = $wpdb->get_results($sql);
        
        foreach($users as $user)
        {
            //             $sql = "SELECT meta_value FROM wp_usermeta WHERE meta_key='MailName' AND user_id=".$user->ID;
            //             $names = $wpdb->get_row($sql);
            //             $expnames = explode(",", $names->meta_value);
            //             $display_name = $names->meta_value;
            
            //             $lastName = $expnames[0];
            //             $expFirst = explode(" & ", $expnames[1]);
            //             $firstName = $expFirst[0];
            
            //             if(!empty($display_name))
                //             {
                //                 echo '<pre>'.print_r($user->ID." ".$display_name, true).'</pre>';
                //                 $wpdb->update('wp_users', array('display_name'=>$display_name), array('ID'=>$user->ID));
                //             }
            
            
            $userInfo[$user->ID]['email'] = $user->user_email;
            $userInfo[$user->ID]['DAEMemberNo'] = str_replace("U", "", $user->user_login);
        }
        return $userInfo;
    }
    
    /*
     * Add GPX Promo/Coupon
     * Save the promo
     * Add
     * Edit
     * Remove
     * @array $post
     * Return Array
     */
    public function return_add_gpx_promo($post=[])
    {
        global $wpdb;
        
        if(empty($post))
        {
//             $post = $_POST;
            $post = stripslashes_deep($post);
        }
        else
        {
            $post = stripslashes_deep( $_POST );
        }
        
        $post = stripslashes_deep( $post );
        
        if(isset($post['metaUseExc']))
        {
            $post['metaUseExc'] = base64_decode($post['metaUseExc']);
        }
        
        if(isset($post['remove']))
        {
            $wpdb->delete('wp_specials', array('id'=>$post['remove']));
            $wpdb->delete('wp_promo_meta', array('specialsID'=>$post['remove']));
            
            $output = array('success'=>true, 'msg'=>'Successfully removed Promotion!');
            return $output;
        }
        $meta = array(
            'promoType'=>$post['metaType'],
            'transactionType'=>$post['metaTransactionType'],
            'usage'=> implode(",", array_unique($post['metaUsage'])),
            'exclusions'=> implode(",", array_unique($post['metaExclusions'])),
            'exclusiveWeeks' => $post['exclusiveWeeks'],
            'stacking' => $post['metaStacking'],
            //                 'flashStart' => $post['metaFlashStart'],
            //                 'flashEnd' => $post['metaFlashEnd'],
            'bookStartDate' => $post['metaBookStartDate'],
            'bookEndDate' => date('Y-m-d 23:59:59', strtotime($post['metaBookEndDate'])),
            'travelStartDate' => $post['metaTravelStartDate'],
            'travelEndDate' => date('Y-m-d 23:59:59', strtotime($post['metaTravelEndDate'])),
            'leadTimeMin' => $post['metaLeadTimeMin'],
            'leadTimeMax' => $post['metaLeadTimeMax'],
            'terms' => $post['metaTerms'],
            'minWeekPrice' => $post['metaMinWeekPrice'],
            'maxValue' => $post['metaMaxValue'],
            'useExc' => $post['metaUseExc'],
            'availability' => $post['availability'],
            'availability' => $post['availability'],
            'slash' => $post['metaSlash'],
            'highlight' => $post['metaHighlight'],
        );
        
        //blackout dates
        if(isset($post['metaBlackoutStart']) && !empty($post['metaBlackoutStart']))
        {
            foreach($post['metaBlackoutStart'] as $mbosKey=>$mbosVal)
            {
                if(strtotime($mbosVal) > '1511362833')
                {
                    $meta['blackout'][] = array(
                        'start'=>date('Y-m-d 00:00:00', strtotime($mbosVal)),
                        'end'=>date('Y-m-d 23:59:59', strtotime($post['metaBlackoutEnd'][$mbosKey])),
                    );
                }
            }
        }
        //resort specific travel dates
        if(isset($post['metaResortBlackoutResorts']) && !empty($post['metaResortBlackoutResorts']))
        {
            foreach($post['metaResortBlackoutResorts'] as $metaResortBlackoutResort)
            {
                $metaResortBlackoutResorts = explode(",", $metaResortBlackoutResort);
                if(isset($post['metaResortBlackoutStart']) && !empty($post['metaResortBlackoutStart']) && isset($post['metaResortBlackoutEnd']) && !empty($post['metaResortBlackoutEnd']))
                {
                    foreach($post['metaResortBlackoutStart'] as $mrbsKey=>$mrbsValue)
                    {
                        if(strtotime($mrbsValue) > '1511362833')
                        {
                            $meta['resortBlackout'][] = array(
                                'resorts' => $metaResortBlackoutResorts,
                                'start'=>date('Y-m-d 00:00:00', strtotime($mrbsValue)),
                                'end'=>date('Y-m-d 23:59:59', strtotime($post['metaResortBlackoutEnd'][$mrbsKey])),
                            );
                        }
                    }
                }
            }
        }
        //resort specific travel dates
        if(isset($post['metaResortTravelResorts']) && !empty($post['metaResortTravelResorts']))
        {
            foreach($post['metaResortTravelResorts'] as $metaResortTravelResort)
            {
                $metaResortTravelResorts = explode(",", $metaResortTravelResort);
                if(isset($post['metaResortTravelStart']) && !empty($post['metaResortTravelStart']) && isset($post['metaResortTravelEnd']) && !empty($post['metaResortTravelEnd']))
                {
                    foreach($post['metaResortTravelStart'] as $mrtsKey=>$mrtsValue)
                    {
                        if(strtotime($mrtsValue) > '1511362833')
                        {
                            $meta['resortTravel'][] = array(
                                'resorts' => $metaResortTravelResorts,
                                'start'=>date('Y-m-d 00:00:00', strtotime($mrtsValue)),
                                'end'=>date('Y-m-d 23:59:59', strtotime($post['metaResortTravelEnd'][$mrtsKey])),
                            );
                        }
                    }
                }
            }
        }
        
        if(!empty($post['metaFlashStart']))
            $meta['flashStart'] = $post['metaFlashStart'];
            
            if(!empty($post['metaFlashEnd']))
                $meta['flashEnd'] = $post['metaFlashEnd'];
                
                $couponorpromo = 'promo';
                if($post['bookingFunnel'] == 'No') //this is a coupon
                {
                    $couponorpromo = 'coupon';
                    $meta['maxCoupon'] = $post['metaMaxCoupon'];
                    $meta['singleUse'] = $post['metaSingleUse'];
                }
                else
                {
                    $meta['beforeLogin'] = $post['metaBeforeLogin'];
                    $meta['GACode'] = $post['metaGACode'];
                }
                $meta['icon'] = $post['metaIcon'];
                $meta['desc'] = $post['metaDesc'];
                if(isset($post['metaSpecificCustomer']))
                    $meta['specificCustomer'] = json_encode($post['metaSpecificCustomer']);
                    if(isset($post['metaTransactionType']) && in_array('upsell', $post['metaTransactionType']))
                        $meta['upsellOptions'] = $post['metaUpsellOptions'];
                        $extraupdate = '';
                        if(!empty($post['metaUsage']))
                            foreach($post['metaUsage'] as $museage)
                            {
                                switch($museage)
                                {
                                    case 'region':
                                        if(!empty($post['metaSetRegion']))
                                        {
                                            $meta['usage_regionType'] = 'gpxRegion';
                                            $meta['usage_region'] = json_encode($post['metaSetRegion']);
                                            foreach($post['metaSetRegion'] as $msr)
                                            {
                                                $extraupdate[] = array($msr=>'gpxRegion');
                                            }
                                        }
                                        break;
                                        
                                    case 'resort':
                                        if(!empty($post['usage_resort']))
                                            foreach($post['usage_resort'] as $resort)
                                            {
                                                $meta['usage_resort'][] = $resort;
                                                $extraupdate[] = array($resort=>'resorts');
                                                $extraforeign[] = $resort;
                                            }
                                        break;
                                        
                                    case 'trace':
                                        
                                        break;
                                        
                                    case 'customer':
                                        $meta['metaCustomerResortSpecific'] = $post['metaCustomerResortSpecific'];
                                        if($post['metaCustomerResortSpecific'] == 'Yes')
                                        {
                                            if(!empty($post['usage_resort']))
                                                foreach($post['usage_resort'] as $resort)
                                                {
                                                    $meta['usage_resort'][] = $resort;
                                                    $extraupdate[] = array($resort=>'resorts');
                                                }
                                        }
                                        break;
                                        
                                    case 'dae':
                                        $meta['useage_dae'] = 1;
                                        break;
                                        
                                }
                            }
                        if(!empty($post['metaExclusions']))
                            foreach($post['metaExclusions'] as $mexc)
                            {
                                switch($mexc)
                                {
                                    case 'region':
                                        if(!empty($post['metaSetRegionExclude']))
                                        {
                                            $meta['exclude_regionType'] = 'gpxRegion';
                                            $meta['exclude_region'] = json_encode($post['metaSetRegionExclude']);
                                        }
                                        break;
                                        
                                    case 'resort':
                                        if(!empty($post['exclude_resort']))
                                            foreach($post['exclude_resort'] as $resort)
                                            {
                                                $meta['exclude_resort'][] = $resort;
                                            }
                                        break;
                                        
                                    case 'trace':
                                        
                                        break;
                                        
                                    case 'home-resort':
                                        if(!empty($post['exclude_resort']))
                                            foreach($post['exclude_resort'] as $resort)
                                            {
                                                $meta['exclude_home_resort'][] = $resort;
                                            }
                                        break;
                                        
                                    case 'customer':
                                        $meta['metaCustomerResortSpecificExclusions'] = $post['metaCustomerResortSpecificExclusions'];
                                        if($post['metaCustomerResortSpecificExclusions'] == 'Yes')
                                        {
                                            if(!empty($post['exclude_resort']))
                                                foreach($post['exclude_resort'] as $resort)
                                                {
                                                    $meta['exclude_resort'][] = $resort;
                                                }
                                        }
                                        break;
                                        
                                    case 'dae':
                                        $meta['exclude_dae'] = 1;
                                        break;
                                        
                                }
                            }
                        if(isset($post['actc']) && !empty($post['actc']))
                            $meta['actc'] = $post['actc'];
                            if(isset($post['couponTemplate']) && !empty($post['couponTemplate']))
                                $meta['couponTemplate'] = $post['couponTemplate'];
                                if(isset($post['acCoupon']) && $post['acCoupon'] == 1)
                                    $meta['acCoupon'] = $post['acCoupon'];
                                    $Amount = '';
                                    if(isset($post['Amount']))
                                        $Amount = $post['Amount'];
                                        $update = array(
                                            'Properties'=>json_encode($meta),
                                            'Name'=>$post['Name'],
                                            'Slug'=>$post['Slug'],
                                            'PromoType'=>$post['metaType'],
                                            'Type'=>$couponorpromo,
                                            'Amount' =>$Amount,
                                            'StartDate' => date('Y-m-d 00:00:00', strtotime($post['StartDate'])),
                                            'EndDate' => date('Y-m-d 23:59:59', strtotime($post['EndDate'])),
                                            'TravelStartDate' => date('Y-m-d 00:00:00', strtotime($post['metaTravelStartDate'])),
                                            'TravelEndDate' => date('Y-m-d 23:59:59', strtotime($post['metaTravelEndDate'])),
                                            'Active' => $post['Active'],
                                            'SpecUsage' => implode(",", array_unique($post['metaUsage'])),
                                            'showIndex' => $post['showIndex'],
                                        );
                                        $update['master'] = $post['master'];
                                        $datetime = date('Y-m-d H:i:s');
                                        $current_user = wp_get_current_user();
                                       
                                        if(empty($post['specialID']))
                                        {
                                            $rev[$datetime] = $current_user->display_name;
                                            
                                            $update['revisedBy'] = json_encode($rev);
                                            
                                            $wpdb->insert('wp_specials', $update);
                                           
                                            $sid = $wpdb->insert_id;
                                            $output = array('success'=>true, 'msg'=>'Promotion Added!');
                                        }
                                        else
                                        {
                                            $sql = "SELECT revisedBy FROM wp_specials WHERE id='".$post['specialID']."'";
                                            $revRow = $wpdb->get_row($sql);
                                            if(isset($revRow) && !empty($revRow->revisedBy))
                                                $rev = json_decode($revRow->revisedBy);
                                                
                                                $current_user = wp_get_current_user();
                                                
                                                $rev->$datetime = $current_user->display_name;
                                                
                                                $update['revisedBy'] = json_encode($rev);
                                                
                                                $wpdb->update('wp_specials', $update, array('id'=>$post['specialID']));
                                                $sid = $post['specialID'];
                                                $output = array('success'=>true, 'msg'=>'Promotion Updated!');
                                        }
                                        if(!empty($extraupdate))
                                        {
                                            $wpdb->delete('wp_promo_meta', array('specialsID'=>$sid));
                                            foreach($extraupdate as $eus)
                                            {
                                                foreach($eus as $euk=>$euv)
                                                {
                                                    $table = 'wp_'.$euv;
                                                    $updateExtra = array(
                                                        'specialsID'=>$sid,
                                                        'refTable'=>$table,
                                                        'foreignID'=>$euk,
                                                    );
                                                    $wpdb->insert('wp_promo_meta', $updateExtra);
                                                }
                                            }
                                        }
                                        
                                        if(wp_doing_ajax())
                                        {
                                            return $output;
                                        }
                                        else 
                                        {
                                            if(isset($output['success']))
                                            {
                                                return true;
                                            }
                                            else 
                                            {
                                                return false;
                                            }
                                        }
    }
    
    public function gpx_retrieve_coupon_templates($selected = '')
    {
        global $wpdb;
        
        $sql = "SELECT id, name FROM wp_specials WHERE PromoType IN ('Auto Create Coupon Template -- Pct Off', 'Auto Create Coupon Template -- Dollar Off', 'Auto Create Coupon Template -- Set Amt')";
        $rows = $wpdb->get_results($sql);
        $html = '<option>Select Template</option>';
        foreach($rows as $row)
        {
            $sel = '';
            if($selected == $row->id)
                $sel = 'selected';
                $html .= '<option value="'.$row->id.'" '.$sel.'>'.$row->name.'</option>';
        }
        return $html;
    }
    
    public function return_gpx_countryregion_dd($country='')
    {
        global $wpdb;
        $output = '<option value="0" disabled selected ></option>';
        if(empty($country))//get the country
        {
            //select usa first
            $sql = "SELECT CountryID, country FROM wp_gpxCategory WHERE CountryID = '45'";
            $usa = $wpdb->get_results($sql);
            $sql = "SELECT CountryID, country FROM wp_gpxCategory WHERE CountryID <> '45' ORDER BY country";
            $all = $wpdb->get_results($sql);
            $countries = array_merge($usa, $all);
            foreach($countries as $country)
            {
                if($country->CountryID > '1000')
                    continue;
                    $output .= '<option value="'.$country->CountryID.'"';
                    if(isset($_GET['select_country']) && $_GET['select_country'] == $country->CountryID)
                        $output .= ' selected';
                        $output .= '>'.$country->country.'</option>';
            }
            
        }
        else//get a region
        {
            $sql = "SELECT id, region FROM wp_daeRegion WHERE CountryID='".$country."'";
            $regions = $wpdb->get_results($sql);
            foreach($regions as $region)
            {
                if($region->region == 'All')
                    continue;
                    $output .= '<option value="'.$region->id.'"';
                    if(isset($_GET['select_region']) && $_GET['select_region'] == $region->id)
                        $output .= ' selected';
                        $output .= '>'.$region->region.'</option>';
                        // $output .= '<option value="'.$region->id.'">'.$region->region.'</option>';
            }
        }
        return $output;
    }
    
    /*
     * Custom request generate report
     */
    
    public function return_cron_check_custom_requests($testing='')
    {
        $_REQUEST['match_debugging'] = true;
//         $testIDs = [
//             '646169',
//             '478171',
//             '594414',
//             '7104777',
//         ];
        
        global $wpdb;
        
        $sfSent = [];
        $sentEmailMissed = [];
        $sentEmailSixty = [];
        $sfLoginSet = '';
        $sfFields = [];
		$matchedID = array();
        $cremail = stripslashes(get_option('gpx_cremailMessage'));
        $crresortmatchemail = stripslashes(get_option('gpx_crresortmatchemailMessage'));
        $crresortmissedemail = stripslashes(get_option('gpx_crresortmissedemailMessage'));
        
        //create the link
        $link = get_site_url("", "/result/?matched=".$result->id, "https");
        
        require_once get_template_directory().'/models/gpxmodel.php';
        
        require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
        $dae = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
        
//         require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.salesforce.php';
//         $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
        $sf = Salesforce::getInstance();
        
        $twentyfourhours = date('Y-m-d H:i:s', strtotime('-24 hours'));
        //start by seeing if held properties need to be released
        $sql = "SELECT * FROM wp_gpxCustomRequest
            WHERE matched != ''
            AND match_release_date_time IS NULL
            AND match_date_time IS NOT NULL
            AND match_date_time < '".$twentyfourhours."'";
        //suppress for now
//         $rows = $wpdb->get_results($sql);
        foreach($rows as $row)
        {
            //cron testing
//             if(!in_array($row->userID, $testIDs))
//             {
//                 continue;
//             }
            
            //first release the match date time
            $update['match_release_date_time'] = date("Y-m-d H:i:s");
            $update['week_on_hold'] = 0;
            $wpdb->update('wp_gpxCustomRequest', $update, array('id'=>$row->id));
            $rowmatched = explode(",", $row->matched);
            //was this week booked?
            foreach($rowmatched as $holdMatch)
            {
                $sql = "SELECT * FROM wp_gpxTransactions a
                        INNER JOIN wp_properties b on a.weekId=b.weekId
                        WHERE b.id='".$holdMatch."'";
                $trans = $wpdb->get_row($sql);
                if(isset($trans) && !empty($trans))
                {
                    // this week has been booked we don't need to do anything else
                }
                else
                {
                    //realease the week
                    
                    //get the week details
                    $sql = "SELECT * FROM wp_properties WHERE id='".$holdMatch."'";
                    $propDets = $wpdb->get_row($sql);
                    $wpdb->update('wp_room', array('active'=>'1'), array('record_id'=>$propDets->weekId));
                    
                    $inputVars = array(
                        'WeekEndpointID' => $propDets->WeekEndpointID,
                        'WeekID' => $propDets->weekId,
                        'DAEMemberNo' => $row->emsID,
                        'ForImmediateSale' => true,
                    );
                    //release it from dae
//                     $dae->DAEReleaseWeek($inputVars);
                    
                    $message = $crresortmissedemail;
                    $fromEmailName = get_option('gpx_crresortmissedemailName');
                    $fromEmail = get_option('gpx_crresortmissedemail');
                    $subject = get_option('gpx_crresortmissedemailSubject');
                    //we aren't using the link on this message.  This will need to be adjusted if it is ever enabeld.
                    //                     $link = get_site_url("", "/wp-admin/admin-ajax.php?action=custom_request_status_change&croid=".$toemail->id."221a2d2s33d564334ne3".$toemail->emsID, "https");
                    
                    //add additional details
                    $replaceExtra['[weekID]'] = $holdMatch->matched;
                    $replaceExtra['[submitted]'] = $holdMatch->datetime;
                    $replaceExtra['[matcheddate]'] = $holdMatch->match_date_time;
                    $replaceExtra['[releaseddate]'] = $holdMatch->match_release_date_time;
                    $replaceExtra['[who]'] = $holdMatch->who;
                    
                    foreach($replaceExtra as $reK=>$reV)
                    {
                        $message = str_replace($reK, $reV, $message);
                    }
                    
                    $message = str_replace("[FORM]", $form, $message);
                    //                     $message = str_replace("HTTP://[URL]", $link, $message);
                    //                     $message = str_replace("[URL]", $link, $message);
                    
                    $headers[]= "From: ".$fromEmailName." <".$fromEmail.">";
                    //$headers[]= "Cc: GPX <gpx@gpxvacations.com>";
                    $headers[] = "Content-Type: text/html; charset=UTF-8";
                    //suppress email notification
//                     if(!in_array($row->email, $sentEmailMissed))
//                     {
//                         $sentEmailMissed[] = $row->email;
//                         if(wp_mail($row->email, $subject, $message, $headers))
//                         {
//                             $insertData = [
//                                 'cr_id'=>$row->id,
//                                 'email'=>'missed',
//                             ];
//                             $wpdb->insert('wp_gpxCREmails',$insertData);
//                         }
//                         else
//                         {
//                             $insertData = [
//                                 'cr_id'=>$row->id,
//                                 'email'=>'missed_email_error',
//                             ];
//                             $wpdb->insert('wp_gpxCREmails',$insertData);
//                         }
//                     }
                }
            }
        }
        
        //do any of these need to be inactive?
        $sql = "SELECT id, datetime, active FROM wp_gpxCustomRequest
                WHERE active='1'
                AND STR_TO_DATE(checkIn, '%m/%d/%Y') < '".date('Y-m-d')."'";
        $inactiveResults = $wpdb->get_results($sql);
        
        foreach($inactiveResults as $ir)
        {
            $wpdb->update('wp_gpxCustomRequest', array('active'=>'0'), array('id'=>$ir->id));
        }
        
        //check for new ones
        $sql = "SELECT * FROM wp_gpxCustomRequest
                WHERE matched=''
                AND active='1'
                AND STR_TO_DATE(checkIn, '%m/%d/%Y') > '".date('m/d/Y')."'
                ORDER BY BOD  DESC, datetime ASC";
        
        $results = $wpdb->get_results($sql);

        $noMatch = '';
        $sfSent = [];
        $i = 0;
        $matchedByResult = [];
        foreach($results as $result)
        {
            $matchedID = [];
            $matchesbypid = [];
            $doMatch = '';
    		if(isset($_REQUEST['cr_debug']))
            {
            	echo '<pre>'.print_r($result->id." -- ".$result->firstName." ".$result->lastName." -- ".$result->active, true).'</pre>';
            }
            //cron testing
//             if(!in_array($result->userID, $testIDs))
//             {
//                 continue;
//             }
            
            if(empty($result->userID) || $result->userID == '0')
            {
                $result->userID = $result->emsID;
            }

            $mrSet = [];
            //update the link
            $link = get_site_url("", "/result/?matched=".$result->id, "https");
            
            $db = (array) $result;
            $matches = custom_request_match($db);
            
            if(!empty($matches))
            {
//                 foreach($matches as $mmm)
//                 {
//                     $matchesbypid[$mmm->PID] = $matches;
//                 }
//                 $matchedID = array();
                foreach($matches as $matchKey=>$match)
                {
                    $matchesbypid[$match->PID] = $matches;
                    if($matchKey === 'restricted')
                    {
                        $i++;
                        continue;
                    }
                    if(isset($match->PID) && !empty($match->PID))
                    {
                        $matchedByResult[$result->id][] = $match->PID;
                        //only the first match should be added to the $matchedID array
                        if(!in_array($match->PID, $matchedID))
                        {
                            $matchedID[] = $match->PID;
                            $doMatch = $match->PID;
                        }
                        //if the request is resort based then first-in-first-out
                        if(isset($result->resort) && !empty($result->resort))
                        {
                            //set the match result order...
                            if(!isset($mrOrder[$result->id]))
                            {
                                //only set it when it hasn't been used by another owner
                                if(!in_array($i, $mrOrderUsed[$result->resort]))
                                {
                                    $update = array(
                                        'match_duplicate_order' => $i,
                                        'match_date_time' => date('Y-m-d H:i:s'),
                                    );
                                    $wpdb->update('wp_gpxCustomRequest', $update, array('id'=>$result->id));
                                    //put the week on hold
                                    $mrOrder[$result->id] = $match->PID;
                                    $mrSet[$result->id] = $match->PID;
                                    $mrOrderUsed[$match->PID][] = $i;
                                }
                                else
                                {
                                    
                                }
                            }
                            
                            $matchedResort[$result->resort][$i] = $match->PID;
                            $matchedResortDetails[$match->PID] = $match;
                        }
                    }
                    $i++;
                }
                //was this a resort specific request?
//                 if(isset($result->resort) && !empty($result->resort))
//                 {
                    
//                     //was the order set?
//                     if(!isset($mrOrder[$result->id]))
//                     {
//                         //if not then we need to set the order
//                         $max = $mrOrderUsed[$match->PID];
//                         $max++;
//                         $update = array(
//                             'match_duplicate_order' => $max,
//                             'match_date_time' => date('Y-m-d H:i:s'),
//                         );
//                         if(!isset($_REQUEST['match_debugging']))
//                         {
//                             $wpdb->update('wp_gpxCustomRequest', $update, array('id'=>$result->id));
//                         }
                        
                        
//                         //put the week on hold
//                         $mrOrderUsed[$result->resort][] = $max;
//                         $noMatch = 1;
//                     }
//                     //if this is resort specific and this isn't the first resort matched then 
//                     if($doMatch != $mrSet[$result->id])
//                     {
//                         $doMatch = '';
//                     }
//                 }
                
                if(isset($doMatch) && !empty($doMatch))
                {
                    if(isset($mrOrderUsed[$doMatch]))
                    {
                        if($doMatch != $matchedByResult[$result->id][0])
                        {
                            continue;
                        }
                    }
                    $mid = $doMatch;
                    //if this is a resort request then put it on hold
                    if(isset($result->resort) && !empty($result->resort))
                    {
                        $hold = true;
                        
//                         $thisMatchID = $mrSet[$result->id];
                        $thisMatchID = $mid;
                        
                        if(!isset($_REQUEST['match_debugging']))
                        {
                            $dae->DAEHoldWeek($thisMatchID, '', $result->userID);
                        }
                        
                        $holdData[strtotime('NOW')] = [
                            'action'=>'held',
                            'by'=>'Custom Request',
                        ];
                        
                        if($result->preference == 'Exchange')
                        {
                            $weekType = 'ExchangeWeek';
                            $weekTypeURI = '/booking-path/?book='.$thisMatchID.'&type=ExchangeWeek';
                            
                        }
                        elseif($result->preference == 'Rental')
                        {
                            $weekType = 'RentalWeek';
                            $weekTypeURI = '/booking-path/?book='.$thisMatchID.'&type=RentalWeek';
                        }
                        else
                        {
                            $weekType = 'N/A';
                            $weekTypeURI = '/result/?matched='.$result->id;
                        }
                        
                        $hold = [
                            'weekId'=>$thisMatchID,
                            'propertyID'=>$thisMatchID,
                            'weekType'=>$weekType,
                            'user'=>$result->userID,
                            'data'=>json_encode($holdData),
                            'released'=>'0',
                            'release_on'=>date('Y-m-d H:i:s', strtotime("+1 day")),
                        ];
                        
                        if(!isset($_REQUEST['match_debugging']))
                        {
                            $hold = $wpdb->insert('wp_gpxPreHold', $hold);
                        
                            $wpdb->update('wp_room', array('active'=>'0'), array('record_id'=>$thisMatchID));
                            
                            $link = get_site_url("", $weekTypeURI, "https");
                            $wpdb->update('wp_gpxCustomRequest', array('week_on_hold'=>$thisMatchID), array('id'=>$result->id));
                    
                        }
                    }
                    
                    $update['matched'] = implode(",",  $matchedByResult[$result->id]);
                    $update['active'] = 0;
                    $update['forCron'] = 0;
                    
                    
                    if(!isset($_REQUEST['match_debugging']))
                    {
                        $wpdb->update('wp_gpxCustomRequest', $update, array('id'=>$result->id));
                    }
                    
                    //send the details to SF
                    $sfExpectedFields = [
                        //                         'orgid' => [
                        //                             'default'=>[
                        //                                 'area'=>'00D40000000MzoY',
                        //                                 'resort'=>'00D40000000MzoY',
                        //                             ],
                        //                             'from'=>null
                        //                         ],
                        //                         'recordType' => [
                        //                             'default'=>[
                        //                                 'area'=>'01240000000MJdI',
                        //                                 'resort'=>'01240000000MJdI',
                        //                             ],
                        //                             'from'=>null
                        //                         ],
                        'Reason' => [
                            'default'=>[
                                'area'=>'GPX: Area Matched',
                                'resort'=>'GPX: Resort Matched',
                            ],
                            'from'=>null
                        ],
                        'Origin' => [
                            'default'=>[
                                'area'=>'Web',
                                'resort'=>'Web',
                            ],
                            'from'=>null,
                        ],
                        'RecordTypeID' => [
                            'default'=>[
                                'area'=>'01240000000MJdI',
                                'resort'=>'01240000000MJdI',
                            ],
                            'from'=>null,
                        ],
                        'Priority' => [
                            'default'=>[
                                'area'=>'Standard',
                                'resort'=>'Standard',
                            ],
                            'from'=>null,
                        ],
                        'Status' => [
                            'default'=>[
                                'area'=>'Open',
                                'resort'=>'Open',
                            ],
                            'from'=>null,
                        ],
                        'Subject' => [
                            'default'=>[
                                'area'=>'GPX Search Request – Area Match',
                                'resort'=>'GPX Search Request – Resort Match',
                            ],
                            'from'=>null,
                        ],
                        'Description' => [
                            'default'=>[
                                'area'=>'',
                                'resort'=>'',
                            ],
                            'description'=>'description',
                        ],
                        'Resort__c' => [
                            'default'=>[
                                'area'=>'',
                                'resort'=>'',
                            ],
                            'matched'=>'ResortName',
                        ],
                        'GPX_Unit_Type__c' => [
                            'default'=>[
                                'area'=>'',
                                'resort'=>'',
                            ],
                            'matched'=>Size,
                        ],
                        'Check_In_Date1__c' => [
                            'default'=>[
                                'area'=>'',
                                'resort'=>'',
                            ],
                            'matched'=>checkIn,
                        ],
                        'City__c' => [
                            'default'=>[
                                'area'=>'',
                                'resort'=>'',
                            ],
                            'matched'=>locality,
                        ],
                        'State__c' => [
                            'default'=>[
                                'area'=>'',
                                'resort'=>'',
                            ],
                            'matched'=>region,
                        ],
                        'Country__c' => [
                            'default'=>[
                                'area'=>'',
                                'resort'=>'',
                            ],
                            'matched'=>country,
                        ],
                        'EMS_Account_No__c' => [
                            'default'=>[
                                'area'=>'',
                                'resort'=>'',
                            ],
                            'request'=>emsID,
                        ],
                        'AccountId' => [
                            'default'=>[
                                'area'=>'',
                                'resort'=>'',
                            ],
                            'request'=>AccountId,
                        ],
                        'Inventory_Found_On__c' => [
                            'default'=>[
                                'area'=>'',
                                'resort'=>'',
                            ],
                            'match_date'=>match_date,
                        ],
                        'Inventory_Hold_Expires_On__c' => [
                            'default'=>[
                                'area'=>'',
                                'resort'=>'',
                            ],
                            'release_date'=>release_date,
                        ],
                        'Request_Submission_Date__c' => [
                            'default'=>[
                                'area'=>'',
                                'resort'=>'',
                            ],
                            'request'=>datetime,
                        ],
                        'SuppliedEmail' => [
                            'default'=>[
                                'area'=>'',
                                'resort'=>'',
                            ],
                            'request'=>email,
                        ],
                    ];
                    
//                     foreach($matchedID as $mid)
//                     {
                        $sfData = [];
                        foreach($sfExpectedFields as $fieldKey=>$field)
                        {
                            if(!empty($result->resort))
                            {
                                $fieldDefaultType = 'resort';
                            }
                            else
                            {
                                $fieldDefaultType = 'area';
                            }
                            
                            if(!empty($field['default'][$fieldDefaultType]))
                            {
                                $sfData[$fieldKey] = $field['default'][$fieldDefaultType];
                            }
                            
                            if(!empty($field['request']))
                            {
                                //pull from the $reults object
                                $pull = $field['request'];
                                $sfData[$fieldKey] = $result->$pull;
                            }
                            
                            if(!empty($field['matched']))
                            {
                                //pull from the $matchedResortDetails object
                                $pull = $field['matched'];
                                $sfData[$fieldKey] = $matchesbypid[$mid]->$pull;
                                if(empty($sfData[$fieldKey]))
                                {
                                    //this must be a region with an array
                                    $sfData[$fieldKey] = $matchesbypid[$mid][0]->$pull;
                                }
                                if($pull == 'checkIn')
                                {
                                    $sfData[$fieldKey] = date('Y-m-d', strtotime($sfData[$fieldKey]));
                                }
                            }
                            
                            if(!empty($field['match_date']))
                            {
                                $sfData[$fieldKey] = date('Y-m-d')."T".date('H:i:s').".000Z";
                            }
                            
                            if(!empty($field['release_date']) && $fieldDefaultType == 'resort')
                            {
                                $sfData[$fieldKey] = date('Y-m-d', strtotime('+24 hours'))."T".date('H:i:s', strtotime('+24 hours')).".000Z";
                            }
                            
                            if(!empty($field['description']))
                            {
                                //create the description
                                $description = 'Special Request Details:
                                ';
                                $description .= '
                                    
                                ';
                                if(!empty($result->resort))
                                {
                                    $description .= 'Resort: '.$result->resort.'
                                    ';
                                }
                                elseif(!empty($result->region))
                                {
                                    $description .= 'Region: '.$result->region.'
                                    ';
                                }
                                elseif(!empty($result->city))
                                {
                                    $description .= 'City: '.$result->city.'
                                    ';
                                    if(!empty($result->state))
                                    {
                                        $description .= 'State: '.$result->state.'
                                        ';
                                    }
                                    if(!empty($result->country))
                                    {
                                        $description .= 'Country: '.$result->country.'
                                        ';
                                    }
                                }
                                elseif(!empty($result->state))
                                {
                                    $description .= 'State: '.$result->state.'
                                    ';
                                    if(!empty($result->country))
                                    {
                                        $description .= 'Country: '.$result->country.'
                                        ';
                                    }
                                }
                                elseif(!empty($result->country))
                                {
                                    $description .= 'Country: '.$result->country.'
                                    ';
                                }
                                
                                $description .= 'Adults: '.$result->adults.'
                                ';
                                
                                if($result->children != 0)
                                {
                                    $description .= 'Children: '.$result->children.'
                                    ';
                                }
                                
                                $description .= 'Date: '.date('m/d/Y', strtotime($result->checkIn));
                                if(!empty($result->checkIn2))
                                {
                                    $description .= ' - '.date('m/d/Y', strtotime($result->checkIn2));
                                }
                                $sfData['Description'] = $description;
                            }
                            
                            if($fieldKey == 'Request_Submission_Date__c')
                            {
                                $sfData[$fieldKey] = date('Y-m-d', strtotime($sfData[$fieldKey]))."T".date('H:i:s', strtotime($sfData[$fieldKey])).".000Z";
                            }
                            
                            if($fieldKey == 'AccountId')
                            {
                                $sql = "SELECT Name FROM wp_GPR_Owner_ID__c WHERE user_id='".$result->userID."'";
                                $account = $wpdb->get_var($sql);
                                
                                $query = "SELECT Property_Owner__c FROM GPR_Owner_ID__c WHERE Name='".$account."'";
                                $aResults = $sf->query($query);
                                
                                foreach($aResults as $aResult)
                                {
                                    $fields = $aResult->fields;
                                    $id = $fields->Property_Owner__c;
                                    
                                    if(!empty($id))
                                    {
                                        $sfData[$fieldKey] = $id;
                                    }
                                }
                            }
                        }
                        
                        if(isset($matchesbypid[$mid]->PID))
                        {
                            $sfweekowner = $matchesbypid[$mid]->PID.$sfData['EMS_Account_No__c'];
                        }
                        else
                        {
                            //this must be a region with an array
                            $sfweekowner = $matchesbypid[$mid][0]->PID.$sfData['EMS_Account_No__c'];
                        }
                    	$matchFromLoop[$result->id] = [
                            'sfData'=>$sfData,
                            'sfweekowner'=>$sfweekowner,
                            'result'=>$result,
                            'link'=>$link,
                            'thisMatchID'=>$mid,
                        ];
//                     }
                }// if matched id
            }
        }
        
        
        if(!isset($_REQUEST['match_debugging']))
        {
            //move the email and sf call out of the loop which should correct duplicate issue
            $sfSent = [];
            $mrSet = [];
            $dupEmailCheck = [];
            foreach($matchFromLoop as $toSend)
            {
                extract($toSend);
                //send details to SF
                if(!in_array($sfSent, $sfweekowner))
                {
                    $sfFields = [];
                    $sfFields[$sfweekowner] = new SObject();
                    $sfFields[$sfweekowner]->fields = $sfData;
                    $sfFields[$sfweekowner]->type = 'Case';
                    
                    $sfSent[] = $sfweekowner;
                    
                    //                             if($sfAdd == 'INVALID_LOGIN: Invalid username, password, security token; or user locked out.')
                    //                             {
                    //                                 sleep(60);
                    //                                 $sfAdd = $sf->gpxCustomRequestMatch($sfFields);
                    //                                 if($sfAdd == 'INVALID_LOGIN: Invalid username, password, security token; or user locked out.')
                    //                                 {
                    //                                     sleep(60);
                    //                                     $sfAdd = $sf->gpxCustomRequestMatch($sfFields);
                    //                                     if($sfAdd == 'INVALID_LOGIN: Invalid username, password, security token; or user locked out.')
                    //                                     {
                    //                                         sleep(60);
                    //                                         $sfAdd = $sf->gpxCustomRequestMatch($sfFields);
                    //                                     }
                    //                                 }
                    //                             }
                    
                    //add the results to sf
                    foreach($sfFields as $sff)
                    {
                        $allSFFields[] = $sff;
                    }
                    
                    $sfAdd = $sf->gpxCustomRequestMatch($allSFFields, '');
                    //                             $sfAdd = $sf->gpxUpsert('GPX_External_ID__c', $sfFields, true);
                    //         echo '<pre>'.print_r($sfAdd, true).'</pre>';
                    if(isset($sfAdd['sessionId']))
                    {
                        $sfLoginSet = $sfAdd['sessionId'];
                    }
                    
                    //                             $sfTest = $sf->gpxLoginTesting($data, $sfLoginSet, true);
                    //                             echo '<pre>'.print_r($sfTest, true).'</pre>';
                    
                    //                             exit;
                    $sfResponse = $sfAdd;
                    $sfFieldsData = $sfFields;
                }
                
                //send email
                        if(get_option(gpx_global_cr_email_send) == 1)
                        {
                            
                            //parse the details for the form
                            $checkIn = $result->checkIn;
                            if(isset($result->checkIn2) && !empty($result->checkIn2))
                                $checkIn .= ' - '.$result->checkIn2;
                                $formData = array(
                                    'Region'=>$result->region,
                                    'City/Sub Region'=>$result->city,
                                    'Resort'=>$result->resort,
                                    'Nearby'=>$result->nearby,
                                    'Adults'=>$result->adults,
                                    'Children'=>$result->children,
                                    'Date'=>$checkIn,
                                );
                                
                                $form = '<ul>';
                                foreach($formData as $key=>$value)
                                {
                                    if($key == 'Nearby')
                                    {
                                        if($value == '1')
                                        {
                                            $form .= '<li><strong>Include Nearby Resort Matches</strong></li>';
                                        }
                                    }
                                    elseif(!empty($value))
                                    {
                                        $form .= '<li><strong>'.$key.':</strong> '.$value.'</li>';
                                    }
                                }
                                $form .= '</ul>';
                                
                                
                                if(isset($mrSet[$result->id])) // if $mrSet for this result id is set then we need to send the resort matched email
                                {
                                    $message = $crresortmatchemail;
                                    $fromEmailName = get_option('gpx_crresortmatchemailName');
                                    $fromEmail = get_option('gpx_crresortmatchemail');
                                    $subject = get_option('gpx_crresortmatchemailSubject');
                                    $recordMatch = 'matchEmail';
                                }
                                else // send the general matched email because this isn't a resort specific request
                                {
                                    $message = $cremail;
                                    $fromEmailName = get_option('gpx_cremailName');
                                    $fromEmail = get_option('gpx_cremail');
                                    $subject = get_option('gpx_cremailSubject');
                                }
                                
                                
                                $message = str_replace("[FORM]", $form, $message);
                                $message = str_replace("HTTP://[URL]", $link, $message);
                                $message = str_replace("[URL]", $link, $message);
                                
                                //add additional details
                                $replaceExtra['[weekID]'] = $thisMatchID;
                                $replaceExtra['[submitted]'] = $result->datetime;
                                $replaceExtra['[matcheddate]'] = $result->match_date_time;
                                $replaceExtra['[releaseddate]'] = $result->match_release_date_time;
                                $replaceExtra['[who]'] = $result->who;
                                
                                foreach($replaceExtra as $reK=>$reV)
                                {
                                    $message = str_replace($reK, $reV, $message);
                                }
                                
                                $headers[]= "From: ".$fromEmailName." <".$fromEmail.">";
    //                             $headers[]= "Bcc: GPX <gpxcustomrequest@4eightyeast.com>";
                                $headers[] = "Content-Type: text/html; charset=UTF-8";
                                
                                
    //                             echo '<pre>'.print_r("email: ".$result->email, true).'</pre>';
                                //keep from sending duplicate emails
                                if(!in_array($result->email, $dupEmailCheck[$subject]))
                                {
    //                                 echo '<pre>'.print_r("emailed: ".$result->email, true).'</pre>';
                                    if(wp_mail($result->email, $subject, $message, $headers))
                                    {
                                        $dupEmailCheck[$subject] = $result->email;
                                        //record the date that the email was sent
                                        $wpdb->update('wp_gpxCustomRequest', array('matchEmail'=>date('Y-m-d H:i:s')), array('id'=>$result->id));
                                        {
                                            $insertData = [
                                                'cr_id'=>$result->id,
                                                'email'=>'match',
                                                'sfData'=>json_encode($sfFieldsData),                                          'sf_response'=>$sfResponse,
                                                'sf_response'=>json_encode($sfResponse),                                          'sf_response'=>$sfResponse,
                                            ];
                                            $wpdb->insert('wp_gpxCREmails',$insertData);
                                        }
                                    }
                                    else
                                    {
                                        $insertData = [
                                            'cr_id'=>$result->id,
                                            'email'=>'match_email_error',
                                            //                                         'sf_response'=>$sfResponse,
                                            'sfData'=>json_encode($sfFieldsData),
                                        ];
                                        $wpdb->insert('wp_gpxCREmails',$insertData);
                                    }
                                }
                                
                        }
            }
        }
        else 
        {
            echo '<pre>'.print_r($matchFromLoop, true).'</pre>';
        }
        //check for requests that are over 60 days old
        $sql = "SELECT * FROM wp_gpxCustomRequest
                WHERE active=1
                AND matched = ''
                and sixtydayemail <> '1'
                AND UNIX_TIMESTAMP(datetime) < UNIX_TIMESTAMP( NOW() - INTERVAL 60 DAY )";
        //turn off sixty day trigger per Ashley/
//         $sixty = $wpdb->get_results($sql);
        
        foreach($sixty as $toemail)
        {
            
//             if(!in_array($result->userID, $testIDs))
//             {
//                 continue;
//             }
            $wpdb->update('wp_gpxCustomRequest', array('sixtydayemail'=>'1', 'active'=>'0', 'forCron'=>'0'), array('id'=>$toemail->id));
            
            $message =stripslashes(get_option('gpx_crsixtydayemailMessage'));
            
            $checkIn = $toemail->checkIn;
            if(isset($toemail->checkIn2) && !empty($toemail->checkIn2))
                $checkIn .= ' - '.$toemail->checkIn2;
                $formData = array(
                    'Region'=>$toemail->region,
                    'City/Sub Region'=>$toemail->city,
                    'Resort'=>$toemail->resort,
                    'Adults'=>$toemail->adults,
                    'Children'=>$toemail->children,
                    'Date'=>$checkIn,
                );
                
                $form = '<ul>';
                foreach($formData as $key=>$value)
                {
                    if(!empty($value))
                    {
                        $form .= '<li><strong>'.$key.':</strong> '.$value.'</li>';
                    }
                }
                $form .= '</ul>';
                
                $fromEmailName = get_option('gpx_crsixtydayemailName');
                $fromEmail = get_option('gpx_crsixtydayemail');
                $subject = get_option('gpx_crsixtydayemailSubject');
                
                $link = get_site_url("", "/wp-admin/admin-ajax.php?action=custom_request_status_change&croid=".$toemail->id."221a2d2s33d564334ne3".$toemail->emsID, "https");
                
                $message = str_replace("[FORM]", $form, $message);
                $message = str_replace("HTTP://[URL]", $link, $message);
                $message = str_replace("[URL]", $link, $message);
                
                //add additional details
                $replaceExtra['[weekID]'] = $toemail->matched;
                $replaceExtra['[submitted]'] = $toemail->datetime;
                $replaceExtra['[matcheddate]'] = $toemail->match_date_time;
                $replaceExtra['[releaseddate]'] = $toemail->match_release_date_time;
                $replaceExtra['[who]'] = $toemail->who;
                
                foreach($replaceExtra as $reK=>$reV)
                {
                    $message = str_replace($reK, $reV, $message);
                }
                
                $headers[]= "From: ".$fromEmailName." <".$fromEmail.">";
                //$headers[]= "Cc: GPX <gpx@gpxvacations.com>";
                $headers[] = "Content-Type: text/html; charset=UTF-8";
                
                if(!in_array($toemail->email, $sentEmailSixty))
                {
                    $sentEmailSixty[] = $toemail->email;
                    if(wp_mail($toemail->email, $subject, $message, $headers))
                    {
                        $insertData = [
                            'cr_id'=>$row->id,
                            'email'=>'sixtyday',
                        ];
                        $wpdb->insert('wp_gpxCREmails',$insertData);
                    }
                    else
                    {
                        //do nothing right now but maybe in the future
                        $insertData = [
                            'cr_id'=>$row->id,
                            'email'=>'sixtyday_email_error',
                        ];
                        $wpdb->insert('wp_gpxCREmails',$insertData);
                    }
                }
        }
        
        return $data;
    }
    
    public function return_gpx_newcountryregion_dd($country='')
    {
        global $wpdb;
        $output = '<option value="0" disabled selected ></option>';
        if(empty($country))//get the country
        {
            //select usa first
            $sql = "SELECT CountryID, country FROM wp_gpxCategory WHERE CountryID = '45'";
            $usa = $wpdb->get_results($sql);
            $sql = "SELECT CountryID, country FROM wp_gpxCategory WHERE CountryID <> '45' ORDER BY country";
            $all = $wpdb->get_results($sql);
            $countries = array_merge($usa, $all);
            foreach($countries as $country)
            {
                if($country->CountryID > '1000')
                    continue;
                    $output .= '<option value="'.$country->CountryID.'"';
                    if(isset($_GET['select_country']) && $_GET['select_country'] == $country->CountryID)
                        $output .= ' selected';
                        $output .= '>'.$country->country.'</option>';
            }
            
        }
        else//get a region
        {
            $sql = "SELECT id, region FROM wp_daeRegion WHERE CountryID='".$country."'";
            $regions = $wpdb->get_results($sql);
            $onlyhigh = false;
            foreach($regions as $region)
            {
                //onlyhigh is set so we'll skip adding any other regions for this country
                if($onlyhigh)
                    continue;
                    $sql = "SELECT id, name, lft, rght FROM wp_gpxRegion WHERE RegionID='".$region->id."'";
                    $gpxRegions = $wpdb->get_results($sql);
                    foreach($gpxRegions as $gpxRegion)
                    {
                        //onlyhigh is set so we'll skip adding any other regions for this country
                        if($onlyhigh)
                            continue;
                            //first set DAE region
                            $datas[$gpxRegion->id] = $gpxRegion->name;
                            //check if the name is all and if they have any children -- if children exist then we assume that we want to get them
                            if(strpos(strtolower($gpxRegion->name), " all") !== false && $gpxRegion->rght-$gpxRegion-lft != 1)
                            {
                                //We only wnat the high level regions that are set by GPX remove all other options
                                $datas = array();
                                $onlyhigh = true;
                                
                                //find the first children of all
                                $nextleft = $gpxRegion->lft + 1;
                                $right = $gpxRegion->rght;
                                $sql = "SELECT id, name, lft, rght FROM wp_gpxRegion WHERE lft = '".$nextleft."'";
                                $children = $wpdb->get_row($sql);
                                $childright = $children->rght;
                                $datas[$children->id] = $children->name;
                                
                                while($childright < $right)
                                {
                                    $nextleft = $childright + 1;
                                    $sql = "SELECT id, name, lft, rght FROM wp_gpxRegion WHERE lft = '".$nextleft."'";
                                    $children = $wpdb->get_row($sql);
                                    if(empty($children))
                                    {
                                        $childright = 10000000000000;
                                        continue;
                                    }
                                    $childright = $children->rght;
                                    $datas[$children->id] = $children->name;
                                }
                                
                                //                         $sql = "SELECT id, name FROM wp_gpxRegion WHERE lft >='".$gpxRegion->lft."' and rght <='".$gpxRegion->rght."'";
                                //                         $rows = $wpdb->get_results($sql);
                                //                         foreach($rows as $row)
                                    //                         {
                                    //     //                      if($row->name == 'All' || $row->name == 'All - USA')
                                        //                             if($row->name == 'All')
                                            //                                 continue;
                                            //                             $datas[$row->id] = $row->name;
                                            //                         }
                            }
                            
                    }
                    //                 if($region->region == 'All')
                        //                     continue;
                        //                 $output .= '<option value="'.$region->id.'"';
                        //                 if(isset($_GET['select_region']) && $_GET['select_region'] == $region->id)
                            //                     $output .= ' selected';
                            //                 $output .= '>'.$region->region.'</option>';
                        // $output .= '<option value="'.$region->id.'">'.$region->region.'</option>';
            }
            asort($datas);
            
            foreach($datas as $key=>$value)
            {
                $output .= '<option value="'.$key.'"';
                if(isset($_GET['select_region']) && $_GET['select_region'] == $key)
                    $output .= ' selected';
                    $output .= '>'.$value.'</option>';
            }
        }
        return $output;
    }
    
    public function return_gpx_monthyear_dd($country, $region)
    {
        global $wpdb;
        $dates = array();
        $output = '<option value="0" disabled selected ></option>';
        $sql = "SELECT lft, rght FROM wp_gpxRegion WHERE RegionID='".$region."'";
        $row = $wpdb->get_row($sql);
        $lft = $row->lft+1;
        $sql = "SELECT id, lft, rght FROM wp_gpxRegion
            WHERE lft BETWEEN ".$lft." AND ".$row->rght."
            ORDER BY lft ASC";
        $gpxRegions = $wpdb->get_results($sql);
        foreach($gpxRegions as $gpxRegion)
        {
            $sql = "SELECT DISTINCT a.checkIn FROM wp_properties a
                    INNER JOIN wp_resorts b ON a.resortId=b.ResortID
                    WHERE b.gpxRegionID='".$gpxRegion->id."'";
            $rows = $wpdb->get_results($sql);
            foreach($rows as $row)
            {
                $my = date('M Y', strtotime($row->checkIn));
                if(!in_array($my, $dates))
                    $dates[] = $my;
            }
        }
        foreach($dates as $date)
        {
            $output .= '<option>'.$date.'</option>';
        }
        return $output;
    }
    
    public function return_gpx_subregion_dd($type, $jsonregion, $country)
    {
        //                 if(get_current_user_id() == 5)
            //                 echo '<pre>'.print_r($type, true).'</pre>';
        global $wpdb;
        $output = '<option value="0" disabled selected ></option>';
        if(empty($regions) && !empty($country))
        {
            $sql = "SELECT a.id FROM wp_daeRegion a
                    INNER JOIN wp_gpxRegion b ON a.id=b.RegionID
                    WHERE a.CountryID=".$country;
            $rows = $wpdb->get_results($sql);
            foreach($rows as $row)
            {
                $regions[] = $row->id;
            }
        }
        else
        {
            if(is_array($jsonregion))
            {
                $regions = explode(",", $jsonregion);
            }
        }
        if(isset($regions))
        {
            foreach($regions as $region)
            {
                $sql = "SELECT lft, rght, id, name FROM wp_gpxRegion WHERE ".$type."='".$region."'";
                if($type <> 'id')
                {
                    $row = $wpdb->get_row($sql);
                    $lft = $row->lft+1;
                    $sql = "SELECT id, name, lft, rght FROM wp_gpxRegion
                        WHERE lft BETWEEN ".$lft." AND ".$row->rght."
                        ORDER BY lft ASC";
                }
                $resorts = $wpdb->get_results($sql);
                $right = 0;
                $indent = '';
                foreach($resorts as $resort)
                {
                    
                    if($resort->rght < $right)
                    {
                        $indent .= ' - ';
                        $right = $resort->rght;
                    }
                    else
                    {
                        $right = $resort->rght;
                        $indent = '';
                    }
                    $output .= '<option value="'.$resort->id.'">'.$indent.$resort->name.'</option>';
                    
                    
                }
            }
        }
        return $output;
    }
    
    public function resort_availability_calendar($resort, $beds, $weektype)
    {
        global $wpdb;
        
        $wheres = " WHERE check_in_date > now() AND b.ResortID='".$resort."' AND a.active='1' AND a.archived=0 and b.active=1 AND a.active_rental_push_date != '2030-01-01'";
        if(!empty($beds))
        {
            $wheres .= " AND c.bedrooms='".$beds."'";
        }
        if(!empty($weektype))
        {
            if($weektype == 'BonusWeek')
            {
                $plusSixMonths = date('Y-m-d', strtotime('+6 months'));
                $wheres .= " AND (a.type='2' OR (a.type='3' AND check_in_date <='".$plusSixMonths."'))";
            }
            else
            {
                $wheres .= " AND (a.type='1' OR a.type='3')";
            }
        }
        
        $mapPropertiesToRooms = [
            'id'=>'record_id',
            'checkIn'=>'check_in_date',
            'checkOut'=>'check_out_date',
            'Price'=>'price',
            'weekID'=>'record_id',
            'StockDisplay'=>'availability',
            'WeekType' => 'type',
            'noNights' => 'DATEDIFF(check_out_date, check_in_date)',
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
        ];
        
        $joinedTbl['roomTable'] = [
            'alias'=>'a',
            'table'=>'wp_room',
        ];
        $joinedTbl['unitTable'] = [
            'alias'=>'c',
            'table'=>'wp_unit_type',
        ];
        $joinedTbl['resortTable'] = [
            'alias'=>'b',
            'table'=>'wp_resorts',
        ];
        
        foreach($mapPropertiesToRooms as $key=>$value)
        {
            if($key == 'noNights')
            {
                $joinedTbl['joinRoom'][] = $value.' as '.$key;
            }
            else
            {
                $joinedTbl['joinRoom'][] = $joinedTbl['roomTable']['alias'].'.'.$value.' as '.$key;
            }
        }
        foreach($mapPropertiesToUnit as $key=>$value)
        {
            $joinedTbl['joinUnit'][] =$joinedTbl['unitTable']['alias'].'.'. $value.' as '.$key;
        }
        foreach($mapPropertiesToResort as $key=>$value)
        {
            $joinedTbl['joinResort'][] = $joinedTbl['resortTable']['alias'].'.'.$value.' as '.$key;
        }
        
        $sql = "SELECT
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinResort']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID
                            FROM ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']."
                    INNER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id
                    INNER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id
            ".$wheres." ORDER BY a.check_in_date, a.type, c.number_of_bedrooms";
        $props = $wpdb->get_results($sql);

        $i = 0;
        $lastCheckin = '';
        $lastBeds = '';
        $propKeys = array_keys($props);
        $pi = 0;
        while($pi < count($props))
        {
            $propKey = $propKeys[$pi];
            $row = $props[$pi];
            $pi++;
            //skip anything that has an error
            $allErrors = [
                'checkIn',
            ];
            
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
//             if($prop->WeekType == '3' || $prop->forRental)
//             {
//                 //if this checkin date is within 6 months then also run it as a rental
//                 if($prop->forRental)
//                 {
//                     $prop->WeekType = 'RentalWeek';
//                 }
//                 else
//                 {
//                     if(strtotime($prop->checkIn) < strtotime('+ 6 months'))
//                     {
//                         $nextCnt = count($props);
//                         $props[$nextCnt] = $props[$propKey];
//                         $props[$nextCnt]->forRental = true;
//                         //                                     $propKeys[] = $rPropKey;
//                     }
//                 }
//             }
//             if($prop->WeekType != 'RentalWeek' || $prop->WeekType == '2')
//             {
//                 $prop->WeekType = 'RentalWeek';
//             }
//             else
//             {
//                 $prop->WeekType = 'ExchangeWeek';
//             }
            
            if($prop->WeekType == 'ExchangeWeek')
            {
                $prop->Price = get_option('gpx_exchange_fee');
            }
            
            $prop->WeekPrice = $prop->Price;
            if($row->checkIn == $lastCheckin && $row->bedrooms == $lastBeds)
            {
                continue;
            }
                $data[$i]['start'] = date('Y-m-d', strtotime($row->checkIn));
                $data[$i]['end'] = date('Y-m-d', strtotime(str_replace('-', '/', $row->checkIn)."+1 week"));
                $data[$i]['bedrooms'] = $row->bedrooms;
                $data[$i]['weektype'] = $prop->WeekType;
                if($row->bedrooms == 'St')
                {
                    $beds = 'Studio';
                }
                else
                {
                    $beds = str_replace("b", ' Beds', $row->bedrooms);
                }
                $data[$i]['title'] = $row->ResortName." - ".$row->Size;
                if(isset($row->CheckInDays) && !empty($row->CheckInDays))
                {
//                     $data[$i]['title'] .= " - Check In Days: ".date('l', strtotime($row->checkIn));
                }
                if(isset($_REQUEST['weektype']))
                {
                    $prop->WeekType = $_REQUEST['weektype'];
                }
                if($prop->WeekType == 'BonusWeek')
                {
                    $prop->WeekType = 'RentalWeek';
                }
                $data[$i]['allDay'] = true;
                $data[$i]['url'] = '/booking-path/?book='.$row->id.'&type='.$prop->WeekType;
                
                $lastCheckin = $row->checkIn;
                $lastBeds = $row->bedrooms;
                
                $i++;
        }
        
        return $data;
    }
    
    public function GetMemberCredits($memberNumber)
    {
        global $wpdb;
        
        // the sub-query will grab all of this owners' ID that are mapped in the mapuser2oid table
        $sql = "SELECT SUM(credit_amount) AS total_credit_amount, 
                SUM(credit_used) AS total_credit_used 
                FROM wp_credit 
                WHERE owner_id IN 
                    (SELECT gpx_user_id FROM wp_mapuser2oid 
                    WHERE gpr_oid='".$memberNumber."') 
                    AND (credit_expiration_date IS NULL 
                        OR credit_expiration_date >'".date('Y-m-d')."')";
        $credit = $wpdb->get_row($sql);

        $total_credit = $credit->total_credit_amount - $credit->total_credit_used;

        return $total_credit;
    }
    private function GetMemberDeposits($memberNumber)
    {
        global $wpdb;
        
        $today = date('Y-m-d');
        
        $sql = "SELECT a.*, b.*, a.id as id FROM wp_credit a
INNER JOIN wp_mapuser2oid b ON b.gpx_user_id=a.owner_id
WHERE
                a.owner_id IN 
                (SELECT gpx_user_id FROM wp_mapuser2oid WHERE gpr_oid='".$memberNumber."') 
                AND (credit_expiration_date IS NOT NULL AND credit_expiration_date > '".$today."')
                    GROUP BY a.id";
        $credit_weeks = $wpdb->get_results($sql);
        if(get_current_user_id() == 5)
        {
//             echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
//             echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
//             echo '<pre>'.print_r($wpdb->last_result, true).'</pre>';
        }
        foreach($credit_weeks as $ck=>$cv)
        {
            if(empty($cv->credit_used))
            {
                $cv->credit_used = '0';
            }
            $total_credit = $cv->credit_amount - $cv->credit_used;
            if($total_credit <= 0)
            {
                unset($credit_weeks[$ck]);
            }
        }
        
        return $credit_weeks;
    }
    private function GetMemberOwnerships($memberNumber)
    {
        global $wpdb;
        
        $sql = "SELECT a.*, b.ResortName, b.gpr, c.deposit_year FROM wp_owner_interval a 
                INNER JOIN wp_resorts b ON b.gprID LIKE CONCAT(BINARY a.resortID, '%')
                LEFT JOIN (SELECT MAX(deposit_year) as deposit_year, interval_number FROM wp_credit WHERE status != 'Pending' GROUP BY interval_number) c ON c.interval_number=a.contractID
                WHERE";
//         $sql .= "
// a.Contract_Status__c != 'Cancelled' 
//                     AND ";
        $sql .= "
                    a.ownerID IN 
                    (SELECT DISTINCT gpr_oid 
                        FROM wp_mapuser2oid 
                        WHERE gpx_user_id IN 
                            (SELECT DISTINCT gpx_user_id 
                            FROM wp_mapuser2oid 
                            WHERE gpr_oid='".$memberNumber."'))";
//         $sql .= " OR
//                     a.userID IN 
//                             (SELECT DISTINCT gpx_user_id 
//                             FROM wp_mapuser2oid 
//                             WHERE gpr_oid='".$memberNumber."')";
// if(get_current_user_id() == 5)
// {
//     echo '<pre>'.print_r($sql, true).'</pre>';
// }
        $nextTime = microtime(true);
        $diffTime = $nextTime - $startTime;
       
        $ownerships = $wpdb->get_results($sql, ARRAY_A);
        
        if(isset($_REQUEST['ownership_debug']))
        {
            echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
            echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
        }
        
        return $ownerships;
    }
    private function GetMemberTransactions($cid, $memberNumber='')
    {
        global $wpdb;
        
        $sf = Salesforce::getInstance();
        
        //get the booking transactions
        $sql = "SELECT t.id, t.transactionType, t.depositID, t.cartID, t.weekId, t.paymentGatewayID, t.data, t.cancelled, u.name as room_type FROM wp_gpxTransactions t
                LEFT OUTER JOIN wp_room r on r.record_id=t.weekId   
                LEFT OUTER JOIN wp_unit_type u on u.record_id=r.unit_type
                WHERE t.userID='".$cid."'";
        $results = $wpdb->get_results($sql, ARRAY_A);

        foreach($results as $k=>$result)
        {
            if(!empty($result['depositID']))
            {
                $sql = "SELECT * FROM wp_gpxDepostOnExchange WHERE id='".$result['depositID']."'";
                $row = $wpdb->get_row($sql);
                $dd = json_decode($row->data);
                $depositIDs[$result['id']] = $dd->GPX_Deposit_ID__c;
            }
            $data = json_decode($result['data'], true);
            unset($results[$k]['data']);

            if(isset($data['creditweekid']))
            {
                
                //get the deposit details
                $sql = "SELECT * FROM wp_credit WHERE id='".$data['creditweekid']."'";
                $data['depositDetails'] = $wpdb->get_row($sql);
            }
            if(isset($data['resortName']))
            {
                $data['ResortName'] = $data['resortName'];
            }
            $wktype = trim(strtolower($data['WeekType']));
            if($result['transactionType'] != 'booking')
            {
                $wktype = 'misc';
                $data['type'] = ucwords($result['transactionType']);
                
                //if this is a guest then we need the id of the transaction
                if($data['type'] == 'Guest')
                {
                    $sql = "SELECT weekId, cancelled FROM wp_gpxTransactions WHERE id='".$data['transactionID']."'";
                    $week = $wpdb->get_row($sql);
                    $results[$k]['id'] = $week->weekId;
                    $results[$k]['cancelled'] = $week->cancelled;
                }
                if($data['type'] == 'Deposit')
                {
                    $results[$k]['id'] = $data['Resort_Unit_Week__c'];
                    if(isset($data['creditid']))
                    {
                        
                        $results[$k]['id'] = $data['creditid'];
                    }
//                     $results[$k]['id'] = $data['id'];
                }
                if($data['type'] == 'Extension')
                {
//                     echo '<pre>'.print_r($data['interval'], true).'</pre>';
                    $interval = $data['interval'];
                    $creditid = $data['id'];
                    $results[$k]['id'] = $creditid;
                    $data['id'] = $creditid;
                }
            }
            $transactions[$wktype][$result['id']] = array_merge($results[$k], $data);
            
        }
       //get the deposits
        $today = date("Y-m-d 00:00:00");
       $sql = "SELECT a.*, b.unitweek as unitinterval FROM wp_credit a 
                INNER JOIN wp_owner_interval b on b.userID=a.owner_id
                WHERE a.owner_id='".$cid."' GROUP BY a.id";
       $sql = "SELECT a.*, b.unitweek, a.id as id, a.record_id as sfid FROM wp_credit a
        INNER JOIN wp_mapuser2oid b ON b.gpx_user_id=a.owner_id
        WHERE
          a.status != 'DOE'
        AND a.owner_id IN
        (SELECT gpx_user_id FROM wp_mapuser2oid WHERE gpr_oid='".$memberNumber."')
        AND ( (a.status != 'Approved') OR (credit_expiration_date IS NOT NULL) )
        GROUP BY a.id        
        ORDER BY a.status, a.id";
       $results = $wpdb->get_results($sql, ARRAY_A);
       if(isset($_REQUEST['debugdeposit']))
       {
           echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
           echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
           echo '<pre>'.print_r($results, true).'</pre>';
       }
 
       foreach($results as $k=>$result)
       {
           if($result['extension_date'] == '' && strtotime('NOW') < strtotime($result['credit_expiration_date'].' 23:59:59'))
           {
               $results[$k]['extension_valid'] = 1;
           }
           $results[$k]['credit'] = $result['credit_amount'] - $result['credit_used'];
           
           if(empty($result['unitinterval']))
           {
               //get the unitweek from SF
               $query = "SELECT Resort_Unit_Week__c FROM GPX_Deposit__c where ID = '".$result['sfid']."'";
               $sfUnitWeek =  $sf->query($query);
               
               $UnitWeek = $sfUnitWeek[0]->fields;
               if(!empty($UnitWeek))
               {
                   $results[$k]['unitinterval'] = $UnitWeek->Resort_Unit_Week__c;
                   $wpdb->update('wp_credit', array('unitinterval'=>$UnitWeek->Resort_Unit_Week__c), array('id'=>$result->id));
               }
               
               
               if(isset($_REQUEST['debugdeposit']))
               {
                   echo '<pre>'.print_r($query, true).'</pre>';
                   echo '<pre>'.print_r($sfUnitWeek, true).'</pre>';
                   echo '<pre>'.print_r($UnitWeek, true).'</pre>';
                   echo '<pre>'.print_r( $result, true).'</pre>';
               }
           }
           
           $depositType = 'depositused';
           if($result['status'] == 'Pending' || ($result['status'] == 'Approved' && $results[$k]['credit'] > 0 && strtotime('NOW') < strtotime($result['credit_expiration_date'].' 23:59:59')))
           {
               $depositType = 'deposit';
           }
           
           if(!empty($result['credit_action']))
           {
               $results[$k]['status'] = ucwords($result['credit_action']);
           }
           
           $transactions[$depositType][$k] = $results[$k];
           
        
           //if this is a deposit on exchange and it's still pending then don't display the transaction
           if($result['status'] == 'Pending')
           {
               if(in_array($result['id'], $depositIDs))
               {
                   foreach($depositIDs as $ddK=>$ddv)
                   {
                       
                       if($result['id'] == $ddv)
                       {
                           $transactions['exchange'][$ddK]['pending'] = $ddv;
                       }
                   }
               }
           }
       }
//        if(get_current_user_id() == 5)
//        {
//            echo '<pre>'.print_r($transactions, true).'</pre>';
//        }
       return $transactions;
    }
    /**
     * 
     * @param int $cid -- the cid of the owner
     * @param array $return [id, gpx_username, gpr_oid, gpr_oid_interval, resortID, user_status, Delinquent__c, unitweek]
     */
    public function GetMappedOwnerByCID($cid, $return = [])
    {
        global $wpdb;
        
        if(empty($return))
        {
            $return = [
                'gpr_oid'
            ];
        }
        
        if(!empty($return))
        {
            $selects = implode(', ', $return);
        }
        
        $sql = "SELECT ".$selects." FROM wp_mapuser2oid WHERE gpx_user_id=".$cid."";
        $return = $wpdb->get_row($sql);
        return $return;
    }
    
    public function weekisbooked($weekID)
    {
        global $wpdb;
        
        $booked = false;
        
        $sql = "SELECT cancelled FROM wp_gpxTransactions WHERE weekId='".$weekID."' AND cancelled IS NULL";
        $rows = $wpdb->get_results($sql);
        //if we have any rows the this transaction is booked
        if(count($rows) > 0)
        {
            $booked = true;
        }
        return $booked;
    }
    
    public function get_deposit_form($cid = '')
    {
        global $wpdb;
        
        if(!is_user_logged_in())
        {
            $html = '';
            return $html;
        }
        if(empty($cid))
        {
            $cid = get_current_user_id();
            
            if(isset($_COOKIE['switchuser']))
                $cid = $_COOKIE['switchuser'];
        }
            $agent = false;
            if($cid != get_current_user_id())
            {
                $agent = true;
            }
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
            $sql = "SELECT *  FROM `wp_mapuser2oid` WHERE `gpx_user_id` = ".$cid."";
//             if(get_current_user_id() == 5)
//             {
//                 echo '<pre>'.print_r($cid, true).'</pre>';
//             }
            $wp_mapuser2oid = $this->GetMappedOwnerByCID($cid);

            $memberNumber = '';
//             if(get_current_user_id() == 5)
//             {
//                 echo '<pre>'.print_r($sql, true).'</pre>';
//                 echo '<pre>'.print_r($wp_mapuser2oid, true).'</pre>';
//             }
            if(!empty($wp_mapuser2oid))
            {
                $memberNumber = $wp_mapuser2oid->gpr_oid;
            }
            if(empty($memberNumber))
            {
                $html = '';
                return $html;
            }
            
            if(isset($usermeta->OwnershipWeekType) && !empty($usermeta->OwnershipWeekType))
                $ownershipWeekType = (array) json_decode($usermeta->OwnershipWeekType);
                
                require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
                $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
                
                //As discussed on yesterday's call and again internally here amongst ourselves we think the best number to show in the 'Exchange Summary' slot on the member dashboard  be a formula that takes the total non-pending deposits and subtract out the Exchange weeks booked.
                //This will bypass the erroneous number being sent by DAE and not confuse the owner.
                //         $transactions = $this->load_transactions($cid);
                //         $credit = $transactions['credit'];

                /*
                 * TODO: change DAEMemberNo to the correct number then make sure to use that variable in the $credit and $ownerships variables
                 *     The GetMemberCredit and GetMemberOwnerships variable assumes that the gpr_oid will be used
                 */
                $credit = $this->GetMemberCredits($memberNumber);
                $ownerships = $this->GetMemberOwnerships($memberNumber);

//                 require_once GPXADMIN_API_DIR.'/functions/class.restsaleforce.php';
//                 $gpxRest = new RestSalesforce();
                
//                 require_once GPXADMIN_API_DIR.'/functions/class.salesforce.php';
//                 $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
                $sf = Salesforce::getInstance();
                
                
//                 $query = "SELECT Name, Property_Owner__c, Room_Type__c, Week_Type__c, Owner_ID__c, Contract_ID__c, GPR_Owner_ID__c, GPR_Resort__c, GPR_Resort_Name__c, Owner_Status__c, Resort_ID_v2__c, UnitWeek__c, Usage__c, Year_Last_Banked__c, Days_Past_Due__c FROM Ownership_Interval__c where Owner_ID__c = '".$memberNumber."'";
//                 $results = $sf->query($query);
//                 $results =  $gpxRest->httpGet($query);
                
                //get the details from the database
                $sql = "SELECT a.*, b.ResortName, b.gpr, c.deposit_year FROM wp_owner_interval a 
                left outer JOIN wp_resorts b ON b.gprID  LIKE CONCAT(BINARY a.resortID, '%')
                LEFT OUTER JOIN (SELECT MAX(deposit_year) as deposit_year, interval_number FROM wp_credit WHERE status != 'Pending' GROUP BY interval_number) c ON c.interval_number=a.contractID
                WHERE a.Contract_Status__c != 'Cancelled' AND a.ownerID IN 
                    (SELECT gpr_oid 
                        FROM wp_mapuser2oid 
                        WHERE gpx_user_id IN 
                            (SELECT gpx_user_id 
                            FROM wp_mapuser2oid WHERE gpr_oid='".$memberNumber."'))";
                $results = $wpdb->get_results($sql);
                
                if(empty($results))
                {
                    $html = '<h2>Your ownership ID is not valid.</h2>';
                }
                    else
                    {
                        $html = '<h2>Deposit Week</h2>';
                        $html .= '<h5>Current Credit: <span class="interval-credit">'.$credit.'</span></h5>';
                        $html .= '<p>Float reservations must be made with your home resort prior to deposit.</p>';
                        $html .= '<div id="depositMsg"></div>';
                        $html .= '<form name="CreateWillBank" class="material" method="post">';
                        $html .= '<input type="hidden" name="DAEMemberNo" value="'.$memberNumber.'">';
                        $html .= '<ul class="deposit-bank-boxes">';
                        foreach($results as $result)
                        {    
                            $selects = [
                                'Name',
                                'Property_Owner__c',
                                'Room_Type__c',
                                'Week_Type__c',
                                'Owner_ID__c',
                                'Contract_ID__c', 
                                'GPR_Owner_ID__c', 
                                'GPR_Resort__c', 
                                'GPR_Resort_Name__c', 
                                'Owner_Status__c', 
                                'Resort_ID_v2__c', 
                                'UnitWeek__c', 
                                'Usage__c', 
                                'Year_Last_Banked__c', 
                                'Days_Past_Due__c',
                            ];
                            $query = "SELECT ".implode(", ", $selects)." FROM Ownership_Interval__c where Contract_ID__c = '".$result->contractID."'";
                            $ownerships =  $sf->query($query);
                            $ownership = $ownerships[0]->fields;
//                             $ownership =  $gpxRest->httpGet($query);
                            if($ownership->Days_Past_Due__c > 0)
                            {
//                                 continue;
                            }
                            
//                             if(empty($ownership->GPR_Resort_Name__c))
//                             {
//                                 $sql = "SELECT * FROM wp_resorts WHERE ResortID='".$ownership->Resort_ID_v2__c."'";
//                                 $resortDets = $wpdb->get_row($sql);
//                                 $ownership->GPR_Resort_Name__c = $resortDets->ResortName;
//                             }
                            //check for a 2 for 1 special
                            $sql = "SELECT * FROM wp_specials WHERE PromoType='2 for 1 Deposit' and Active=1";
                            $specials = $wpdb->get_results($sql);
                            $type = '';
                            $type = '';
                            foreach($specials as $special)
                            {
                                if(isset($twofer) && $twofer['type'] == 'Promo')
                                {
                                    $promocode = $twofer['code'];
                                    $type = $twofer['type'];
                                }
                                else
                                    $promocode = '';
                                    
                                    if(strtolower($special->Type) == 'promo')
                                        $promocode = $special->Slug;
                                        $specialMeta = json_decode($special->Properties);
                                        //if useage_resort is set then we need to make sure that this resort should apply to this special
                                        if(isset($specialMeta->usage_resort))
                                        {
                                            foreach($specialMeta->usage_resort as $resortList)
                                            {
                                                $sql = "SELECT ResortID FROM wp_resorts WHERE id='".$resortList."'";
                                                $resortRow = $wpdb->get_row($sql);
                                                if($resortRow->ResortID == $ownership->Resort_ID_v2__c)
                                                {
                                                    if(isset($twofer['startDate']))
                                                    {
                                                        if($twofer['startDate'] < $special->StartDate)
                                                            $special->StartDate = $twofer['startDate'];
                                                            if($twofer['endDate'] > $special->EndDate)
                                                                $special->EndDate = $twofer['endDate'];
                                                    }
                                                    if($type != 'Promo')
                                                        $type = $special->Type;
                                                        $twofer = array(
                                                            'startDate'=>$special->StartDate,
                                                            'endDate'=>$special->EndDate,
                                                            'type'=>$type,
                                                            'code'=>$promocode,
                                                        );
                                                }
                                            }
                                        }
                                        else // this isn't dependant on a set resort 8775667519
                                        {
                                            if(isset($specialMeta->specificCustomer) && in_array($cid, json_decode($specialMeta->specificCustomer)))
                                            {
                                                if(isset($twofer['startDate']))
                                                {
                                                    if($twofer['startDate'] < $special->StartDate)
                                                        $special->StartDate = $twofer['startDate'];
                                                        if($twofer['endDate'] > $special->EndDate)
                                                            $special->EndDate = $twofer['endDate'];
                                                }
                                                if($type != 'Promo')
                                                    $type = $special->Type;
                                                    $twofer = array(
                                                        'startDate'=>$special->StartDate,
                                                        'endDate'=>$special->EndDate,
                                                        'type'=>$type,
                                                        'code'=>$promocode,
                                                    );
                                            }
                                            else
                                            {
                                                if(isset($twofer['startDate']))
                                                {
                                                    if($twofer['startDate'] < $special->StartDate)
                                                        $special->StartDate = $twofer['startDate'];
                                                        if($twofer['endDate'] > $special->EndDate)
                                                            $special->EndDate = $twofer['endDate'];
                                                }
                                                if($type != 'Promo')
                                                    $type = $special->Type;
                                                    $twofer = array(
                                                        'startDate'=>$special->StartDate,
                                                        'endDate'=>$special->EndDate,
                                                        'type'=>$type,
                                                        'code'=>$promocode,
                                                    );
                                            }
                                        }
                            }
                            $admin = wp_get_current_user();
                            if ( in_array( 'administrator_plus', (array) $admin->roles ) || in_array( 'administrator', (array) $admin->roles ) || in_array( 'gpx_admin', (array) $admin->roles ) ) {
                                $isadmin = 'style="display: block !important;"';
                            }
                            
                            if(!isset($twofer) || (isset($twofer) && empty($twofer)) && isset($isadmin))
                            {
                                $twofer = [
                                    'startDate'=>'null',
                                    'endDate'=>'null',
                                    'type'=>'cradj',
                                    'code'=>'',
                                ];
                            }
                           
                            $yearbankded = '';
                            $ownershipType = '';
                            if(!empty($ownership->Usage__c))
                            {
                                $ownershipType = $ownership->Usage__c;
                            }
                            if(!empty($result->deposit_year))
                            {
//                                 $yearbankded = $result->deposit_year+1;
//                                 $nextyear = '1/1/'.$yearbankded;
                                //@Traci -- we asked to remove the minimum date becasue owners can depoist multiple times in one year
                                $nextyear = date('m/d/Y', strtotime('+14 days'));
                            }
                            else
                            {
                                $nextyear = date('m/d/Y', strtotime('+14 days'));
                            }
                            //if this is an agent then the minimum date can be up to a year ago
                            if($agent)
                            {
                                $nextyear = date('m/d/Y', strtotime("-2 years"));
                            }
                            //if this is delinquent then don't allow the deposit
                            $delinquent = '';
                            if($result->Delinquent__c != 'No')
                            {
                                $delinquent = "<strong>Please contact us at <a href=\"tel:+18775667519\">(877) 566-7519</a> for assistance.</strong>";
                            }
                                    $html .= '<li>';
                                    $html .= '<div class="bank-row">';
                                    $html .= '<h3>'.$result->ResortName.'</h3>';
                                    $html .= '</div>';
                                    if(!empty($delinquent))
                                    {
                                        $html .= '<div class="bank-row" style="margin: margin-bottom: 20px;">'.$delinquent.'</div>';
                                    }
                                    else 
                                    {
                                        $html .= '<div class="bank-row">';
                                        $html .= '<span class="dgt-btn bank-select">Select</span>';
                                        $html .= '</div>';
                                        $html .= '<div class="bank-row">';
                                        $html .= '<input type="radio" name="OwnershipID" class="switch-deposit" value="'.$ownership->Name.'" style="text-align: center;">';
                                        $html .= '</div>';
                                    }
                                    $selectUnit = [
                                        'Channel Island Shores',
                                        'Hilton Grand Vacations Club at MarBrisa',
                                        'RiverPointe Napa Valley',
                                    ];
                                    if(in_array($result->ResortName, $selectUnit) || empty($ownership->Room_Type__c))
                                    {
                                        $html .= '<div class="reswrap">';
                                        $html .= 'Unit Type: <select name="Unit_Type__c" class="sel_unit_type ">';
                                        $html .= '<option value="">Please Select</option>';
                                        $html .= '<option>Studio</option>';
                                        $html .= '<option>1br</option>';
                                        $html .= '<option>2br</option>';
                                        $html .= '<option>3br</option>';
                                        $html .= '</select>';
                                        $html .= '</div>';
                                    }
                                    else
                                    {
                                        $html .= '<input type="hidden" name="Unit_Type__c" value="'.$ownership->Room_Type__c.'" class="disswitch" disabled="disabled">';
                                        $html .= '<div class="bank-row">Unit Type: '.$ownership->Room_Type__c.'</div>';
                                    }
//                                     $html .= '<div class="bank-row">Week Type: '.$ownership->Week_Type__c.'</div>';
                                    if(!empty($ownershipType))
                                    {
                                        $html .= '<div class="bank-row">Ownership Type:'.$ownershipType.'</div>';
                                    }
                                    $html .= '<div class="bank-row">Resort Member Number: '.$ownership->UnitWeek__c.'</div>';
                                    if(isset($result->deposit_year))
                                    {
                                        $html .= '<div class="bank-row">Last Year Banked: '.$result->deposit_year.'</div>';
                                    }
                                        $html .= '<div class="bank-row" style="height: 40px; position: relative;">';
                                    
//                                         $html .= '<input type="text" placeholder="Check In Date" name="Check_In_Date__c" class="validate mindatepicker disswitch" data-mindate="'.$nextyear.'" value="" disabled="disabled" required>';

                                        if(!$delinquent)
                                        {
                                            $html .= '<input type="text" placeholder="Check In Date" name="Check_In_Date__c" class="validate mindatepicker disswitch" value="" disabled="disabled" required>';
                                        }
                                        $html .= '<input type="hidden" name="Contract_ID__c" value="'.$ownership->Contract_ID__c.'" class="disswitch" disabled="disabled">';
                                        $html .= '<input type="hidden" name="Usage__c" value="'.$ownership->Usage__c.'" class="disswitch" disabled="disabled">';
                                        $html .= '<input type="hidden" name="Account_Name__c" value="'.$ownership->Property_Owner__c.'" class="disswitch" disabled="disabled">';
                                        $html .= '<input type="hidden" name="GPX_Member__c" value="'.$ownership->Owner_ID__c.'" class="disswitch" disabled="disabled">';
                                        $html .= '<input type="hidden" name="GPX_Resort__c" value="'.$ownership->GPR_Resort__c.'" class="disswitch" disabled="disabled">';
                                        $html .= '<input type="hidden" name="Resort_Name__c" value="'.$result->ResortName.'" class="disswitch" disabled="disabled">';
                                        $html .= '<input type="hidden" name="Resort_Unit_Week__c" value="'.$ownership->UnitWeek__c.'" class="disswitch" disabled="disabled">';
                                        $html .= '</div>';
                                        
                                        $resRequired = '';
                                        if($result->gpr == '0')
                                        {
                                            $resRequired = ' required="required"';
                                        }
                                		$html .= '<div class="reswrap"><input type="text" name="Reservation__c" placeholder="Reservation Number" class="resdisswitch" disabled="disabled" '.$resRequired.' /></div>';

                                        if(isset($twofer) && !empty($twofer))
                                        {
                                            $html .= '<div '.$isadmin.' class="twoforone twoforone-'.$twofer['type'].'" data-start="'.date('m/d/Y', strtotime($twofer['startDate'])).'" data-end="'.date('m/d/Y', strtotime($twofer['endDate'])).'">';
                                            $html .= '<input placeholder="Coupon Code" type="text" name="twofer" value="'.$twofer['code'].'"><br>';
                                            $html .= '</div>';
                                        }
                                        $html .= '</li>';
                        }
                        $html .= '</ul>';
                        $html .= '<li><a href="#" class="btn-will-bank dgt-btn">Submit<i class="fa fa-refresh fa-spin fa-fw" style="display: none;"></i></a></li>';
                        $html .= '<li class="success-message"></li>';
                        $html .= '</ul>';
                        $html .= '</form>';
                    }
                    
                    return $html;
    }
    
    public function get_twoforone_validate($coupon, $date, $resort)
    {
        global $wpdb;
        $data = array('success'=>false, 'message'=>'That coupon is not valid');
        
        $sql = "SELECT * FROM wp_specials WHERE (Slug='".$coupon."' OR Name='".$coupon."') AND PromoType='2 for 1 Deposit' AND Active='1'";
        $special = $wpdb->get_row($sql);
        
        if(!empty($special))
        {
            if(strtotime($date) >= strtotime($special->StartDate) && strtotime($date) <= strtotime($special->EndDate))
            {
                $specialMeta = json_decode($special->Properties);
                if(isset($specialMeta->usage_resort) && !empty($specialMeta->usage_resort))
                {
                    foreach($specialMeta->usage_resort as $ur)
                    {
                        $sql = "SELECT ResortID FROM wp_resorts WHERE id='".$ur."'";
                        $resortRow = $wpdb->get_row($sql);
                        
                        $resortIDs[] = $resortRow->ResortID;
                    }
                    if(in_array($resort, $resortIDs))
                        $data = array('success'=>true, 'name'=>$special->Name);
                }
                else
                    $data = array('success'=>true, 'name'=>$special->Name);
            }
        }
        
        return $data;
    }
    
    public function get_exchange_form()
    {
        global $wpdb;
        
        require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
        $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
        
//         require_once GPXADMIN_API_DIR.'/functions/class.salesforce.php';
//         $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
        $sf = Salesforce::getInstance();
        
        $data = array('html'=>'', 'CPOFee'=>'');
        
        $time_start = microtime(true); 
        
        if(is_user_logged_in())
        {
            $exchangebooking = ' to use for this exchange booking';

            if((empty($_GET['id']) || $_GET['id'] == 'undefined'))
            {
                $exchangebooking = '';
            }
            
            $sql = "SELECT WeekType, WeekEndpointID, weekId, WeekType, checkIn, resortId  FROM wp_properties WHERE id='".$_GET['id']."'";
            $row = $wpdb->get_row($sql);
            
            $cid = get_current_user_id();
            
            if(isset($_COOKIE['switchuser']))
                $cid = $_COOKIE['switchuser'];
                
                $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
                
                $sql = "SELECT *  FROM `wp_mapuser2oid` WHERE `gpx_user_id` = '".$cid."'";
                $wp_mapuser2oid = $this->GetMappedOwnerByCID($cid);
                
                $memberNumber = '';
                
                if(!empty($wp_mapuser2oid))
                {
                    $memberNumber = $wp_mapuser2oid->gpr_oid;
                }
                
                $agent = false;
                if($cid != get_current_user_id())
                {
                    $agent = true;
                }
                //set the resort meta fees
                $sql = "SELECT * FROM wp_resorts_meta WHERE ResortID='".$row->resortId."'";
                
                $resortMetas = $wpdb->get_results($sql);
                
                $rmFees = [
                    'UpgradeFeeAmount'=>[],
                    'CPOFeeAmount'=>[],
                ];
                foreach($resortMetas as $rm)
                {
                    //reset the resort meta items
                    $rmk = $rm->meta_key;
                    if($rmArr = json_decode($rm->meta_value, true))
                    {
                        
                        foreach($rmArr as $rmdate=>$rmvalues);
                        {
                            
                            $thisVal = '';
                            $rmdates = explode("_", $rmdate);
                            if(count($rmdates) == 1 && $rmdates[0] == '0')
                            {
                                //do nothing
                            }
                            else
                            {
                                //check to see if the from date has started
                                if($rmdates[0] < strtotime("now"))
                                {
                                    //this date has started we can keep working
                                }
                                else
                                {
                                    //these meta items don't need to be used
                                    continue;
                                }
                                //check to see if the to date has passed
                                if(isset($rmdates[1]) && ($rmdates[1] >= strtotime("now")))
                                {
                                    //these meta items don't need to be used
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
                                        
                                        //set this fee
                                        if($rmk == 'UpgradeFeeAmount')
                                        {
                                            $upgradeAmount = $rmval;
                                        }
                                        if($rmk == 'CPOFeeAmount')
                                        {
                                            $cpoFee = $rmval;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } //end resort meta fees
                
                
                $credit = $this->GetMemberCredits($memberNumber);
                //                 echo '<pre>'.print_r($credit, true).'</pre>';
                $hidenext = '';
                
                $creditWeeks = $this->GetMemberDeposits($memberNumber);
                
                foreach($creditWeeks as $cwK=>$cw)
                {
                    if($cw->status == 'Approved' && $cw->credit_action == 'transferred')
                    {
                        unset($creditWeeks[$cwK]);
                    }
                }
                
                //             if($row->WeekType == 'ExchangeWeek' && (isset($credit) && !empty($credit) && $credit <= -1))
                if($row->WeekType == 'ExchangeWeek' && (isset($credit) && !empty($credit) && $credit[0] <= -1))
                {
                    $data['error'] = 'You have already booked an exchange with a negative deposit.  All deposits must be processed prior to completing this booking.  Please wait 48-72 hours for our team to verify the transactions.';
                    $html = "<h2>Exchange weeks are not available.</h2>";
                }
                else if($_GET['type'] === 'donation' && empty($credit)){
                     $html = '<div class="exchange-result exchangeNotOK">';
                     $html .= '<h2>Ready to donate? <a href="#modal-deposit" class="dgt-btn deposit better-modal-link" aria-label="Deposit Week">Deposit a week now</a> to get started</h2>';
                     $html .= '</div>';
                }
                else
                {
                    
                    $html = '<div class="exchange-result exchangeOK">';
                    $html .= '<h2>Exchange Credit</h2><p>';
                    $html .= 'Our records indicate that you do not have a current deposit with GPX; however this exchange will be performed, in good faith, and in-lieu of a deposit/banking of a week. Please select Deposit A Week from your Dashboard after your booking is complete. If you have already deposited your week it can take up to 48-72 hours for our team to verify the transaction. Should GPX have questions we will contact you within 24 business hours. Please note: if a deposit cannot be completed in 5 business days this exchange transaction will be cancelled.';
                    $html .= '</p></div>';
                    
                    $weekType = str_replace(" ", "", $_GET['weektype']);
                    
                    $weekDetails = $gpx->DAEGetWeekDetails($_GET['weekid']);
                    
                        if($weekDetails->active == '0')
                        {
                            //did this user put it on hold?
                            $sql = "SELECT id FROM wp_gpxPreHold WHERE user='".$cid."' and weekId='".$_GET['weekid']."' AND released=0";
                            $row = $wpdb->get_row($sql);
                            if(empty($row))
                            {
                                $data['error'] = 'This week is no longer available!<br><a href="#" class="dgt-btn active book-btn custom-request" data-pid="'.$_GET['id'].'" data-cid="'.$cid.'">Submit Custom Request</a>';
                                $html = "<h2>This week is no longer available.</h2>";
                            }
                        }
                        elseif(isset($creditWeeks) && !empty($creditWeeks))
                        {
                            
                            $html = '<hgroup>';
                            $html .= '<h2>Exchange Credit</h2>';
                            $html .= '<p>Choose an exchange credit'.$exchangebooking.'.</p>';
                            $html .= '</hgroup>';
                            $html .= '<ul id="exchangeList" class="exchange-list">';
                            
                            $beds = $weekDetails[0]->bedrooms;
                                   
                            $resortName = $weekDetails[0]->ResortName;
                                
                                $i = 1;
                                foreach($creditWeeks as $creditWeek)
                                {
                                    $creditWeek->Room_Type__c = $creditWeek->unit_type;
                                    $checkindate = strtotime($weekDetails[0]->checkIn);
                                    $bankexpiredate = strtotime($creditWeek->credit_expiration_date);
                                    //if this expired and can't be extended
                                    if($checkindate > $bankexpiredate && !empty($creditWeek->extension_date))
                                    {
                                        continue;
                                    }
                                    $selects = [
                                        'Name',
                                        'Property_Owner__c',
                                        'Room_Type__c',
                                        'Week_Type__c',
                                        'Owner_ID__c',
                                        'Contract_ID__c',
                                        'GPR_Owner_ID__c',
                                        'GPR_Resort__c',
                                        'GPR_Resort_Name__c',
                                        'Owner_Status__c',
                                        'Resort_ID_v2__c',
                                        'UnitWeek__c',
                                        'Usage__c',
                                        'Year_Last_Banked__c',
                                        'Days_Past_Due__c',
                                        'Delinquent__c',
                                    ];
                                    
//                                     $query = "SELECT ".implode(", ", $selects)." FROM Ownership_Interval__c where ROID_Key_Full__c = '".$creditWeek->ROID_Key_Full."'";
//                                     $sfDetails =  $sf->query($query);
                                    
//                                     if(get_current_user_id() == 5)
//                                     {
// //                                         echo '<pre>'.print_r($query, true).'</pre>';
// //                                         echo '<pre>'.print_r($sfDetails, true).'</pre>';
//                                     }
                                    
//                                     $sfDetail = $sfDetails[0]->fields;
                                    
                                    //If an owner is booking an exchange that has an arrival date after the expiration date of the exchange they are booking against we need to prevent the booking and present verbiage to the owner that in order to complete the transaction they must pay a credit extension fee or deposit/select a different week to book against.
                                    $expired = '';
                                    $expiredclass = '';
                                    $expireddisabled = '';
                                    $expiredFee = '';
                                    if(isset($weekDetails[0]->checkIn))
                                    {
                                        //$bankingYear = date('m/d/'.$creditWeek->BankingYear);
                                        //$bankexpiredate = strtotime($bankingYear. '+ 2 years');
                                        //$missingExpiryMessage = 'Please note:  A credit extension may be required for this booking.  An representative will advise if necessary.';
                                        if($checkindate > $bankexpiredate && (!empty($_GET['id']) && $_GET['id'] != 'undefined'))
                                        {
                                            $expired = 'In order to complete the transaction you must pay a credit extension fee or deposit/select a different week to book against.<br><br><button class="btn btn-primary pay-extension" data-tocart="no-redirect">Add Fee To Cart</button>';
                                            $expiredclass = 'expired';
                                            $expireddisabled = 'disabeled';
                                            $expiredFee = get_option('gpx_extension_fee');
                                        }
                                        elseif($checkindate > $bankexpiredate && empty($exchangebooking))
                                        {
                                            continue;
                                        }
                                        else
                                        {
                                            if($creditWeek->Delinquent__c != 'No')
                                            {
                                                $expired = 'Please contact us at <a href=\"tel:+18775667519\">(877) 566-7519</a> to use this deposit.';
                                                $expiredclass = 'expired';
                                                $expireddisabled = 'disabeled';
                                            }
                                            //$pendingReview = 'Pending Review';
                                        }
                                    }
                                    
//                                     $creditbed = substr($sfDetail->Room_Type__c, 0, 1);
                                    //echo '<pre>'.print_r('credit beds '.$creditbed, true).'</pre>';
                                    
									if(empty($creditWeek->Room_Type__c))
                                    {
                                    	$utcb = explode("/", $creditWeek->unit_type);
										$creditWeek->Room_Type__c = str_replace("b", "", $utcb);
                                    }

                                    if (strpos(strtolower($creditWeek->Room_Type__c), '2br') !== false) {
                                        $creditbed = '2';
                                    }
                                    elseif (strpos(strtolower($creditWeek->Room_Type__c), '1br') !== false) {
                                        $creditbed = '1';
                                    }
                                    elseif (strpos(strtolower($creditWeek->Room_Type__c), '2') !== false) {
                                        $creditbed = '2';
                                    }
                                    elseif (strpos(strtolower($creditWeek->Room_Type__c), 'std') !== false) {
                                        $creditbed = 'studio';
                                    }
                                    elseif (strpos(strtolower($creditWeek->Room_Type__c), 'st') !== false) {
                                        $creditbed = 'studio';
                                    }
                                    elseif (strpos(strtolower($creditWeek->Room_Type__c), '1') !== false) {
                                        $creditbed = '1';
                                    }
                                    else 
                                    {
                                        $creditbed = $creditWeek->Room_Type__c;
                                    }
                                    //from 2 - 3 upgrade fee is 185
                                    switch (strtolower($creditbed))
                                    {
                                        case 'studio':
                                            if( strpos(strtolower($beds), 'std') !== false)
                                            {
                                                $upgradeFee = '0';
                                            }
                                            elseif( strpos(strtolower($beds), 'htl') !== false)
                                            {
                                                $upgradeFee = '0';
                                            }
                                            elseif(strpos(strtolower($beds), '1') !== false)
                                            {
                                                $upgradeFee = '85';
                                            }
                                            else
                                            {
                                                $upgradeFee = '185';
                                            }
                                            break;
                                            
                                        case '1':                                                           //This is only the case at Carlsbad Inn Beach Resort.  Owners who have a 1 Bedroom Sleeps 6 unit type can upgrade to a 2 bedroom with no upgrade fee.
                                            if(strpos(strtolower($beds), 'st') !== false
                                            || strpos(strtolower($beds), '1') !== false
                                            || ($creditWeek->Resort_ID_v2__c == 'CBI' && strpos(strtolower($beds), '2') !== false))
//                                         || ($creditWeek->Resort_ID_v2__c == 'Carlsbad Inn Beach Resort' && strpos(strtolower($beds), '2') !== false && $resortName == 'Carlsbad Inn Beach Resort'))
                                            {
                                                $upgradeFee = '0';
                                            }
                                            else
                                            {
                                                $upgradeFee = '185';
                                            }
                                            break;
                                            
                                        case '2':                                                           //This is only the case at Carlsbad Inn Beach Resort.  Owners who have a 1 Bedroom Sleeps 6 unit type can upgrade to a 2 bedroom with no upgrade fee.
                                            if( strpos(strtolower($beds), 'std') !== false 
                                                || strpos(strtolower($beds), 'htl') !== false 
                                                || strpos(strtolower($beds), '1') !== false 
                                                || strpos(strtolower($beds), '2') !== false 
                                                || ($creditWeek->Resort_ID_v2__c == 'CBI'
                                                    && strpos(strtolower($beds), '3') !== false) )
                                            {
                                                $upgradeFee = '0';
                                            }
                                            else
                                            {
                                                $upgradeFee = '185';
                                            }
                                            break;
                                            
                                        default:
                                            $upgradeFee = '0';
                                            break;
                                    }
                                    if($upgradeFee > 0 && isset($upgradeAmount))
                                    {
                                        $upgradeFee = $upgradeAmount;
                                    }
                                        $html .= '<li class="exchange-item">';
                                        $html .= '<div class="w-credit">';
                                        $html .= '<div class="head-credit '.$expireddisabled.'">';
                                        $html .= '<input type="checkbox" class="exchange-credit-check if-perks-credit" id="rdb-credit-'.$i.'" value="'.$upgradeFee.'" name="radio['.$i.'][]" data-creditexpiredfee="'.$expiredFee.'" data-creditweekid="'.$creditWeek->id.'" '.$expireddisabled.'>';
                                        $html .= '<label for="rdb-credit-'.$i.'">Apply Credit</label>';
                                        $html .= '</div>';
                                        $html .= '<div class="cnt-credit">';
                                        $html .= '<ul>';
                                        $html .= '<li>';
                                        $html .= '<p><strong>'.$creditWeek->resort_name.'</strong></p>';
                                        $html .= '<p>'.$creditWeek->CreditWeekID.'</p>';
                                        $html .= '</li>';
                                        $html .= '<li>';
                                        $html .= '<p><strong>Expires:</strong></p>';
                                        $html .= '<span> ';
                                        if(isset($pendingReview))
                                        {
                                            $html .= $pendingReview;
                                        }
                                        elseif(isset($creditWeek->credit_expiration_date))
                                        {
                                            $html .= $creditWeek->credit_expiration_date;
                                        }
                                        $html .= '</span>';
                                        $html .= '</li>';
                                        $html .= '<li>';
                                        $html .= '<p><strong>Entitlement Year:</strong> '.$creditWeek->deposit_year.'</p>';
                                        $html .= '</li>';
                                        $html .= '<li>';
                                        $html .= '<p><strong>Size:</strong> '.$creditWeek->unit_type.'</p>';
                                        $html .= '</li>';
                                        if($upgradeFee > 0 && !empty($exchangebooking))
                                        {
                                            $html .= '<li>';
                                            $html .= '<p>Please note: This booking requires an upgrade fee</p>';
                                            $html .= '</li>';
                                        }
                                        if(isset($expired) && !empty($expired))
                                        {
                                            $html .= '<li>';
                                            $html .= '<p>'.$expired.'</p>';
                                            $html .= '<input type="hidden" name="expired-fee" class="expired-fee" value="'.$expiredFee.'" />';
                                            $html .= '</li>';
                                        }
                                        elseif(isset($missingExpiryMessage))
                                        {
                                            $html .= '<li>';
                                            $html .= '<p>'.$missingExpiryMessage.'</p>';
                                            $html .= '</li>';
                                        }
                                        $html .= '</ul>';
                                        $html .= '</div>';
                                        $html .= '</div>';
                                        $html .= '</li>';
                                        $i++;
                                }
                                $html .= '</ul>';
                                $html .= '<p style="font-size: 18px; margin-top: 35px;">Don\'t see the credit you want to use?  <a href="#useDeposit" class="toggleElement use-deposit" style="color: #009ad6;">Click here</a> to <span id="showhidetext">show</span> additional weeks to deposit and use for this booking.</p>';
                                $hidenext = 'style = "display: none; margin-top: 35px;"';
                                
                        }
                        $ownerships = $this->GetMemberOwnerships($memberNumber);
//                         echo '<pre>'.print_r($ownerships, true).'</pre>';
                        $html .= '<div id="useDeposit" '.$hidenext.'>';
                        $html .= '<hgroup>';
                        $html .= '<h2>Use New Deposit</h2>';
                        $html .= '<p>Select the week you would like to deposit as credit for this exchange.</p>';
                        $html .= '</hgroup>';
                        $html .= '<form name="exchangendeposit" id="exchangendeposit">';
                        $html .= '<ul id="exchangeList" class="exchange-list deposit-bank-boxes" style="text-align: center;">';
                        
                        $beds = $weekDetails[0]->bedrooms;
                        
                        $resortName = $weekDetails[0]->ResortName;
                        
                        
                        $i = 1;
                        if(!empty($ownerships))
                        {
                          
                            foreach($ownerships as $ownership)
                            {
                                //                             if(date('m/d/y', strtotime($ownership['ExpiryDate'])) < date('m/d/y', strtotime($row->checkIn)))
                                    //                                 continue;
                                $selects = [
                                    'Name',
                                    'Property_Owner__c',
                                    'Room_Type__c',
                                    'Week_Type__c',
                                    'Owner_ID__c',
                                    'Contract_ID__c',
                                    'GPR_Owner_ID__c',
                                    'GPR_Resort__c',
                                    'GPR_Resort_Name__c',
                                    'Owner_Status__c',
                                    'Resort_ID_v2__c',
                                    'UnitWeek__c',
                                    'Usage__c',
                                    'Year_Last_Banked__c',
                                    'Days_Past_Due__c',
                                    'Delinquent__c'
                                ];
                                
                                $query = "SELECT ".implode(", ", $selects)." FROM Ownership_Interval__c where Contract_ID__c = '".$ownership['contractID']."' AND Contract_Status__c='Active'";
                                $creditWeeks =  $sf->query($query);
//                                 echo '<pre>'.print_r($creditWeeks, true).'</pre>';
                                $creditWeek = $creditWeeks[0]->fields;
                                if(get_current_user_id() == 5)
                                {
//                                     echo '<pre>'.print_r($creditWeek, true).'</pre>';
                                }
                                /*
                                 * todo:  add exceptions for room_type
                                 */
                                if (strpos(strtolower($creditWeek->Room_Type__c), '2br') !== false) {
                                    $creditbed = '2';
                                }
                                elseif (strpos(strtolower($creditWeek->Room_Type__c), '1br') !== false) {
                                    $creditbed = '1';
                                }
                                elseif (strpos(strtolower($creditWeek->Room_Type__c), '2') !== false) {
                                    $creditbed = '2';
                                }
                                elseif (strpos(strtolower($creditWeek->Room_Type__c), 'std') !== false) {
                                    $creditbed = 'studio';
                                }
                                elseif (strpos(strtolower($creditWeek->Room_Type__c), '1') !== false) {
                                    $creditbed = '1';
                                }
                                else
                                {
                                    $creditbed = $creditWeek->Room_Type__c;
                                }
                                
//                                 if(get_current_user_id() == 5)
//                                 {
//                                     echo '<pre>'.print_r($creditWeek, true).'</pre>';
//                                     echo '<pre>'.print_r($creditbed, true).'</pre>';
//                                 }
                                
                                $selectUnit = [
                                    'Channel Island Shores',
                                    'Hilton Grand Vacations Club at MarBrisa',
                                    'RiverPointe Napa Valley',
                                ];
                                
                                if(in_array($result->ResortName, $selectUnit) || empty($creditbed))
                                {
                                    
                                    $defaultUpgrade = [
                                        'st' => '0',
                                        '1' => '0',
                                        '2' => '0',
                                        '3' => '0',
                                    ];
                                    switch(strtolower($beds))
                                    {
                                        case 'st':
                                            $defaultUpgrade = [
                                                'st' => '0',
                                                '1' => '0',
                                                '2' => '0',
                                                '3' => '0',
                                            ];
                                        break;
                                        
                                        case '1':
                                            $defaultUpgrade = [
                                                'st' => '85',
                                                '1' => '0',
                                                '2' => '0',
                                                '3' => '0',
                                            ];
                                        break;
                                        
                                        case '2':
                                            $defaultUpgrade = [
                                                'st' => '185',
                                                '1' => '185',
                                                '2' => '0',
                                                '3' => '0',
                                            ];
                                        break;
                                    }
                                }
                                else 
                                {
                                    switch (strtolower($creditbed))
                                    {
                                        case 'studio':
                                            if(strpos(strtolower($beds), 'st') !== false)
                                            {
                                                $upgradeFee = '0';
                                            }
                                            elseif(strpos(strtolower($beds), '1') !== false)
                                            {
                                                $upgradeFee = '85';
                                            }
                                            else
                                            {
                                                $upgradeFee = '185';
                                            }
                                            break;
                                            
                                        case 'hotel':
                                            if(strpos(strtolower($beds), 'st') !== false)
                                            {
                                                $upgradeFee = '0';
                                            }
                                            elseif(strpos(strtolower($beds), '1') !== false)
                                            {
                                                $upgradeFee = '85';
                                            }
                                            else
                                            {
                                                $upgradeFee = '185';
                                            }
                                            break;
                                            
                                        case '1':   
                                            //This is only the case at Carlsbad Inn Beach Resort.  Owners who have a 1 Bedroom Sleeps 6 unit type can upgrade to a 2 bedroom with no upgrade fee.
                                            if(strpos(strtolower($beds), 'st') !== false 
                                            || strpos(strtolower($beds), '1') !== false 
                                            || ($creditWeek->Resort_ID_v2__c == 'CBI' && strpos(strtolower($beds), '2') !== false))
    //                                         || ($creditWeek->Resort_ID_v2__c == 'Carlsbad Inn Beach Resort' && strpos(strtolower($beds), '2') !== false && $resortName == 'Carlsbad Inn Beach Resort'))
                                            {
                                                $upgradeFee = '0';
                                            }
                                            else
                                            {
                                                $upgradeFee = '185';
                                            }
                                            break;
                                            
                                        default:
                                            $upgradeFee = '0';
                                            break;
                                    }
                                }
                                if($upgradeFee > 0 && isset($upgradeAmount))
                                {
                                    $upgradeFee = $upgradeAmount;
                                }
                                
                                $yearbankded = '';
                                $ownershipType = '';
                                
                                if(!empty($ownership['Year_Last_Banked__c']))
                                {
                                    $yearbankded = $ownership['Year_Last_Banked__c']+1;
                                    $nextyear = '1/1/'.$yearbankded;
                                }
                                elseif(!empty($ownership['deposit_year']))
                                {
                                    $yearbankded = $ownership['deposit_year']+1;
                                    $nextyear = '1/1/'.$yearbankded;
                                    $ownership['Year_Last_Banked__c'] = $ownership['deposit_year'];
                                }
                                else
                                {
                                    $nextyear = date('m/d/Y', strtotime('+14 days'));
                                }
                                
                                //if this is an agent then the minimum date can be up to a year ago
                                if($agent)
                                {
                                    $nextyear = date('m/d/Y', strtotime("-2 years"));
                                }
                                
                                
                                //if this is delinquent then don't allow the deposit
                                $delinquent = '';
                                if(get_current_user_id() == 5)
                                {
//                                     echo '<pre>'.print_r($creditWeek, true).'</pre>';
                                }
                                if($creditWeek->Delinquent__c == 'Yes')
                                {
                                    $delinquent = "<strong>Please contact us at <a href=\"tel:+18775667519\">(877) 566-7519</a> to use this deposit.</strong>";
                                }
                                
                                $html .= '<li>';
                                $html .= '<div class="bank-row">';
                                $html .= '<input type="checkbox" class="exchange-credit-check if-perks-ownership" id="rdb-credit-'.$i.'" value="'.$upgradeFee.'" name="radio['.$i.'][]" data-creditweekid="deposit">';
                                $html .= '</div>';
                                $html .= '<div class="bank-row">';
                                $html .= '<h3>'.$ownership['ResortName'].'</h3>';
                                $html .= '</div>';
                                
                                if(!empty($delinquent))
                                {
                                    $html .= '<div class="bank-row" style="margin: margin-bottom: 20px;">'.$delinquent.'</div>';
                                }
                                else
                                {
                                    $html .= '<div class="bank-row">';
                                    $html .= '<span class="dgt-btn bank-select">Select</span>';
                                    $html .= '</div>';
                                    $html .= '<input type="hidden" name="Year" class="disswitch" disabled="disabled">';
                                    
                                    $html .= '<div class="bank-row">';
                                    $html .= '<input type="radio" name="OwnershipID" class="switch-deposit" value="'.$ownership['ResortName'].'" style="text-align: center;">';
                                    $html .= '</div>';
                                }
                                
                                /*
                                 * todo: add dropdown when room type is blank
                                 */
                                $unitType = $creditWeek->Room_Type__c;
                                $hiddenUnitType= '<input type="hidden" name="unit_type" value="'.$unitType.'" class="disswitch" disabled="disabled">';
                                
                                $upgradeMessage = '';
                                if(in_array($result->ResortName, $selectUnit) || empty($unitType))
                                {
                                    $unitType = '<select name="Unit_Type__c" class="sel_unit_type doe">';
                                    $unitType .= '<option data-upgradefee="'.$defaultUpgrade['st'].'">Studio</option>';
                                    $unitType .= '<option data-upgradefee="'.$defaultUpgrade['1'].'">1br</option>';
                                    $unitType .= '<option data-upgradefee="'.$defaultUpgrade['2'].'">2br</option>';
                                    $unitType .= '<option data-upgradefee="'.$defaultUpgrade['3'].'">3br</option>';
                                    $unitType .= '</select>';
                                    $upgradeMessage = 'style="display: none;"';
                                    $hiddenUnitType = '';
                                }
                                
                                $html .= '<div class="bank-row">Unit Type: '.$unitType.'</div>';
                                $html .= '<div class="bank-row">Week Type: '.$creditWeek->Week_Type__c.'</div>';
                                $html .= '<div class="bank-row">Ownership Type:'.$ownershipType.'</div>';
                                $html .= '<div class="bank-row">Resort Member Number: '.$creditWeek->Contract_ID__c.'</div>';
                                if(isset($ownership['Year_Last_Banked__c']))
                                {
                                    $html .= '<div class="bank-row">Last Year Banked: '.$ownership['Year_Last_Banked__c'].'</div>';
                                }
                                $html .= '<div class="bank-row" style="height: 40px; position: relative;">';
                               
                                if(empty($delinquent))
                                {
                                    $html .= '<input type="text" placeholder="Check In Date" name="Check_In_Date__c" class="validate mindatepicker disswitch" data-mindate="'.$nextyear.'" value="" disabled="disabled" required>';
                                }
                                $html .= $hiddenUnitType;
                                $html .= '<input type="hidden" name="Contract_ID__c" value="'.$creditWeek->Contract_ID__c.'" class="disswitch" disabled="disabled">';
                                $html .= '<input type="hidden" name="Usage__c" value="'.$creditWeek->Usage__c.'" class="disswitch" disabled="disabled">';
                                $html .= '<input type="hidden" name="Account_Name__c" value="'.$creditWeek->Property_Owner__c.'" class="disswitch" disabled="disabled">';
                                $html .= '<input type="hidden" name="GPX_Member__c" value="'.$creditWeek->Owner_ID__c.'" class="disswitch" disabled="disabled">';
                                $html .= '<input type="hidden" name="GPX_Resort__c" value="'.$creditWeek->GPR_Resort__c.'" class="disswitch" disabled="disabled">';
                                $html .= '<input type="hidden" name="Resort_Name__c" value="'.$ownership['ResortName'].'" class="disswitch" disabled="disabled">';
                                $html .= '<input type="hidden" name="Resort_Unit_Week__c" value="'.$creditWeek->UnitWeek__c.'" class="disswitch" disabled="disabled">';
                                $html .= '<input type="hidden" name="cid" value="'.$cid.'" class="disswitch" disabled="disabled">';
                                $html .= '</div>';

                                $resRequired = '';
                                if($ownership['gpr'] == '0')
                                {
                                    $resRequired = ' required="required"';
                                }
                                $html .= '<div class="reswrap"><input type="text" name="Reservation__c" placeholder="Reservation Number" class="resdisswitch" disabled="disabled" '.$resRequired.' /></div>';

                                if(($upgradeFee > 0 || !empty($upgradeMessage)) && !empty($exchangebooking))
                                {
                                    $html .= '<div class="bank-row doe_upgrade_msg" '.$upgradeMessage.'>';
                                    $html .= 'Please note: This booking requires an upgrade fee';
                                    $html .= '</div>';
                                }
                                $html .= '</li>';
                                $i++;
                            }
                        }
                        $html .= '</ul>';
                        $html .= '</form>';
                        $html .= '<p id="floatDisc" style="font-size: 18px; margin-top: 35px;">*Float reservations must be made with your home resort prior to deposit. Deposit transactions will automatically be system verified. Unverified deposits may result in the canellation of exchange reservations.</p>';
                        $html .= '</div>';
                }
                $data['CPOPrice'] = get_option('gpx_fb_fee');
                if(isset($cpoFee) && !empty($cpoFee))
                {
                    $data['CPOPrice'] = $cpoFee;
                }
                $data['html'] = $html;
                $data['resortName'] = $resortName;
        }
        
       return $data;
    }
    public function get_bonus_week_details()
    {
        global $wpdb;
        
        $sql = "SELECT WeekType, WeekEndpointID, weekId, WeekType, checkIn, WeekPrice, Price  FROM wp_properties WHERE id='".$_GET['id']."'";
        $row = $wpdb->get_row($sql);
        
        $WeekEndpointID = $_GET['weekendpointid'];
        $weekId = $_GET['weekid'];
        $weekType = str_replace(" ", "", $_GET['weektype']);
        
        if(!empty($row))
        {
            $WeekEndpointID = $row->WeekEndpointID;
            $weekId = $row->weekId;
            $weekType = $row->WeekType;
        }
        
        $data = array('success'=>true);
        
        $cid = get_current_user_id();
        
        if(isset($_COOKIE['switchuser']))
            $cid = $_COOKIE['switchuser'];
            
            $DAEMemberNo = '646169';
            
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
            $DAEMemberNo = $usermeta->DAEMemberNo;
            
            require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
            $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
            
            
            $weekDetails = $gpx->DAEGetWeekDetails($DAEMemberNo, $WeekEndpointID, $weekId, $weekType);
            if(isset($weekDetails->ReturnCode) && $weekDetails->ReturnCode != 0)
            {
                $data['Unavailable'] = "This week is no longer available.  Please select another week.";
                $wpdb->update('wp_properties', array('active'=>0), array('id'=>$_GET['id']));
            }
            if(isset($weekDetails->WeekPrice) && $weekDetails->WeekPrice != $row->Price)
            {
                $data['PriceChange'] = $weekDetails->WeekPrice;
                $weekPrice = $weekDetails->Currency.$weekDetails->WeekPrice;
                $updatedPrice = array('WeekPrice'=>$weekPrice, 'Price'=>$weekDetails->WeekPrice);
                if($weekPrice == ' $')
                    $updatedPrice['active'] = 0;
                    $wpdb->update('wp_properties', $updatedPrice, array('id'=>$_GET['id']));
            }
            
            return $data;
            
    }
    
    public function load_intervals($id)
    {
        global $wpdb;
        
        $cid = get_current_user_id();
        
        if(isset($_COOKIE['switchuser']))
            $cid = $_COOKIE['switchuser'];
            
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
            $DAEMemberNo = $usermeta->DAEMemberNo;
            
            
            $sql = "SELECT *  FROM `wp_mapuser2oid` WHERE `gpx_user_id` = '".$cid."'";
            $wp_mapuser2oid = $this->GetMappedOwnerByCID($cid);
            
            $memberNumber = '';
            
            if(!empty($wp_mapuser2oid))
            {
                $memberNumber = $wp_mapuser2oid->gpr_oid;
            }
            
            require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
            $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
            
            $credit = $this->GetMemberCredits($DAEMemberNo);
            
            $ownership = $this->GetMemberOwnerships($DAEMemberNo);
            
            
//             require_once GPXADMIN_API_DIR.'/functions/class.salesforce.php';
//             $sf = new Salesforce(GPXADMIN_API_DIR, GPXADMIN_API_DIR);
            $sf = Salesforce::getInstance();
            
//             $results =  $gpxRest->httpGet("SELECT Name, Property_Owner__c, Room_Type__c, Week_Type__c, Owner_ID__c, Contract_ID__c, GPR_Owner_ID__c, GPR_Resort__c, GPR_Resort_Name__c, Owner_Status__c, Resort_ID_v2__c, UnitWeek__c, Usage__c, Year_Last_Banked__c, Days_Past_Due__c FROM Ownership_Interval__c where Owner_ID__c = '".$memberNumber."'");
            $query =  "SELECT Name, Property_Owner__c, Room_Type__c, Week_Type__c, Owner_ID__c, Contract_ID__c, GPR_Owner_ID__c, GPR_Resort__c, GPR_Resort_Name__c, Owner_Status__c, Resort_ID_v2__c, UnitWeek__c, Usage__c, Year_Last_Banked__c, Days_Past_Due__c FROM Ownership_Interval__c where Owner_ID__c = '".$memberNumber."'";
            
            $results = $sf->query($query);
            
            if(isset($ownership[0]))
                $ownerships = $ownership;
                else
                    $ownerships = array($ownership);
                    
                    $html = '<div class="w-list-view dgt-container">';
                    
                    foreach($ownerships as $value)
                    {
                        $owner = (object) $value;
                        
                        //update the property for testing
                        if($owner->ResortID == 'CRADJ' && $DAEMemberNo == '610792')
                            $owner->ResortID = 'R1872';
                            $sql = "SELECT * FROM wp_resorts WHERE ResortID='".$owner->ResortID."'";
                            $prop = $wpdb->get_row($sql);
                            $html .= '<div class="w-item-view filtered">';
                            $html .= '<div class="view">';
                            $html .= '<div class="view-cnt">';
                            $html .= '<img src="'.$prop->ImagePath1.'" alt="'.$prop->ResortName.'">';
                            $html .= '</div>';
                            $html .= '<div class="view-cnt">';
                            $html .= '<div class="descrip">';
                            $html .= '<hgroup>';
                            $html .= '<h2>'.$prop->ResortName.'</h2>';
                            $html .= '<span>'.$prop->Town.', '.$prop->Region.'</span>';
                            $html .= '</hgroup>';
                            $html .= '<p>Anniversary Date: '.date('d M Y', strtotime($owner->AnniversaryDate)).'</p>';
                            if(isset($owner->ExpiryDate))
                                $html .= '<p>Expiry Date: '.date('d M Y', strtotime($owner->ExpiryDate)).'</p>';
                                if(isset($owner->LastYearBooked) && !empty($owner->LastYearBooked))
                                    $html .= '<p>Last Year Banked '.date('d M Y', strtotime($owner->LastYearBooked)).'</p>';
                                    $html .= '</div>';
                                    $html .= '<div class="w-status">';
                                    $html .= '<div class="result">';
                                    $html .= '</div>';
                                    $html .= '</div>';
                                    $html .= '</div>';
                                    $html .= '</div>';
                                    $html .= ' <div class="view-detail">';
                                    $html .= '<ul class="list-result">';
                                    $html .= '<li>';
                                    $html .= '<p><strong>Resort Member Number</strong></p>';
                                    $html .= '<p>'.$owner->ResortMemberNo.'</p>';
                                    $html .= '</li>';
                                    $html .= '<li>';
                                    $html .= '<p><strong>ResortShareID</strong></p>';
                                    $html .= '<p>'.$owner->ResortShareID.'</p>';
                                    $html .= '</li>';
                                    $html .= '<li>';
                                    $html .= '<p><strong>Week Number</strong></p>';
                                    $html .= '<p>'.$owner->WeekNo.'</p>';
                                    $html .= '</li>';
                                    $html .= '<li>';
                                    $html .= '<p><strong>Points Value</strong></p>';
                                    $html .= '<p>'.$owner->PointsValue.'</p>';
                                    $html .= '</li>';
                                    $html .= '</ul>';
                                    $html .= '<ul class="list-result">';
                                    $html .= '<li>';
                                    $html .= '<p><strong>Unit Type</strong></p>';
                                    $html .= '<p>'.$owner->UnitTypeDesc.'</p>';
                                    $html .= '</li>';
                                    $html .= '<li>';
                                    $html .= '<p><strong>Ownership Type</strong></p>';
                                    $html .= '<p>'.$owner->OwnershipType.'</p>';
                                    $html .= '</li>';
                                    $html .= '<li>';
                                    $html .= '<p><strong>Sleeps</strong></p>';
                                    $html .= '<p>'.$owner->UnitSleeps.'</p>';
                                    $html .= '</li>';
                                    if(isset($owner->Comments) && !empty($owner->Comments))
                                    {
                                        $html .= '<li>';
                                        $html .= '<p><strong>Comments</strong></p>';
                                        $html .= '<p>'.$owner->Comments.'</p>';
                                        $html .= '</li>';
                                    }
                                    $html .= '</ul>';
                                    $html .= '</div>';
                                    $html .= '</div>';
                    }
                    
                    $html .= '</div>';
                    
                    $html .= '<h2>Deposit</h2>';
                    $html .= '<h5>Current Credit: <span class="interval-credit">'.$credit[0].'</span></h5>';
                    
                    $html .= '<form name="CreateWillBank" class="material" method="post">';
                    $html .= '<input type="hidden" name="DAEMemberNo" value="'.$DAEMemberNo.'">';
                    $html .= '<input type="hidden" name="OwnershipID" value="'.$ownership['OwnershipID'].'">';
                    $html .= '<ul>';
                    $html .= '<li><div class="ginput_container"><div class="material-input input"><input type="text" placeholder="Year" name="Year" class="validate" value="" required></div></div></li>';
                    $html .= '<li><div class="ginput_container"><div class="material-input input"><input type="text" placeholder="Check In Date" name="CheckINDate" class="validate" value="" required></div></div></li>';
                    $html .= '<li><div class="ginput_container"><div class="material-input input"><input type="text" placeholder="Resort Booking Number" name="ResortBookingNo" class="validate" value="" required></div></div></li>';
                    $html .= '</ul>';
                    $html .= '<div class="gform_footer"><a href="#" class="btn-will-bank dgt-btn">Submit<i class="fa fa-refresh fa-spin fa-fw" style="display: none;"></i></a></div>';
                    $html .= '</form>';
                    
                    $data = array('html'=>$html);
                    
                    return $data;
                    
    }
    public function load_ownership($id)
    {
        global $wpdb;
        
        $cid = $_POST['cid'];
        
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
        $DAEMemberNo = str_replace("U", "", $usermeta->DAEMemberNo);
        
        
        
        require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
        $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
        
        $credit = $this->GetMemberCredits($DAEMemberNo);
        
        $ownership = $this->GetMemberOwnerships($DAEMemberNo);
        if(isset($ownership[0]))
            $ownerships = $ownership;
            else
                $ownerships = array($ownership);
                
                $html = '<div class="w-list-view dgt-container">';
                $html .= '<table><thead><tr>';
                $html .= '<th>Resort Member Number</th><th>Resort Name</th><th>Size</th><th>Anniversary Date</th><th>Last Year Banked</th>';
                $html .= '</tr></thead>';
                
                foreach($ownerships as $ownership)
                {
                    $html .= '<tr>';
                    $html .= '<td>'.$ownership['ResortMemberNo'].'</td>';
                    $html .= '<td>'.$ownership['ResortName'].'</td>';
                    $html .= '<td>'.$ownership['AnniversaryDate'].'</td>';
                    $html .= '<td>'.$ownership['LastYearBanked'].'</td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';
                
                $output = array('html'=>$html);
                
                return $output;
    }
    public function load_transactions($id)
    {
        global $wpdb;
        
        $startTime = microtime(true);
        
        //        ini_set('display_errors', 1);
        //        ini_set('display_startup_errors', 1);
        //        error_reporting(E_ALL);
        $cid = get_current_user_id();
        
        if(isset($_COOKIE['switchuser']))
        {
            $cid = $_COOKIE['switchuser'];
            $agentInfo = wp_get_current_user();
            $agent = $agentInfo->first_name.' '.$agentInfo->last_name;
        }
            
            global $wpdb;
            
            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
            $DAEMemberNo = str_replace("U", "", $usermeta->DAEMemberNo);
            
            $sql = "SELECT *  FROM `wp_mapuser2oid` WHERE `gpx_user_id` = '".$cid."'";
            $wp_mapuser2oid = $this->GetMappedOwnerByCID($cid);
            
            $memberNumber = '';
            
            if(!empty($wp_mapuser2oid))
            {
                $memberNumber = $wp_mapuser2oid->gpr_oid;
            }
            
            if(empty($memberNumber))
            {
                $memberNumber = $DAEMemberNo;
            }
            
            require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.gpxretrieve.php';
            $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
            
            $accountDetails = $gpx->DAEGetAccountDetails('U'.$DAEMemberNo);
            if(isset($accountDetails->OnAccountAmount))
                update_user_meta($cid, 'OnAccountAmount', $accountDetails->OnAccountAmount);
                
                $holds = $gpx->DAEGetWeeksOnHold($cid); 
                
                $output['hold'] = '';
                
                if(!empty($holds))
                {
                    
                    //dae doesn't return an array when only one record is left
                    if(isset($holds['country']))
                        $holds = array(0=>$holds);
                        $output['hold'] = '<thead><tr>';
                        $output['hold'] .= '<td>ID</td><td>Resort Name</td><td>Bedrooms</td><td>Check In</td><td>Week Type</td><td>Release On</td><td></td>';
                        $output['hold'] .= '</tr></thead><tbody>';
                        $nz = 1;
                        foreach($holds as $hold)
                        {
                            if($hold->released == 1)
                            {
                                continue;
                            }
                            $holdWeekType = $hold->weekType;
                            if($hold->weekType == 'RentalWeek')
                            {
                                $holdWeekType = 'Rental Week';
                            }
                            if($hold->weekType == 'ExchangeWeek')
                            {
                                $holdWeekType = 'Exchange Week';
                            }
                            if($hold->weekType == 'BonusWeek')
                            {
                                $holdWeekType = 'Rental Week';
                            }
                            
                            $weekTypeForBook = str_replace(" ", "", $holdWeekType);
                            
                            $output['hold'] .= '<tr>';
                            $output['hold'] .= '<td>'.$hold->PID.'</td>';
                            $output['hold'] .= '<td><a class="hold-confirm" href="/booking-path/?book='.$hold->id.'&type='.$weekTypeForBook.'">'.$hold->ResortName.'</a></td>';
                            $output['hold'] .= '<td>'.$hold->bedrooms.'</td>';
                            $output['hold'] .= '<td>'.date('m/d/Y', strtotime($hold->checkIn)).'</td>';
                            $output['hold'] .= '<td>'.$holdWeekType.'</td>';
                            $output['hold'] .= '<td>'.date('m/d/Y h:i a', strtotime($hold->release_on)).'</td>';
                            $output['hold'] .= '<td>';
                            if($agent)
                            {
                                $action = '<span class="extend-box">';
                                $action .= '<a href="#" class="extend-week"title="Extend Week"><i class="fa fa-calendar-plus-o"></i></a>';
                                $action .= '<span class="extend-input" style="display: none;">';
                                $action .= '<input type="date" class="form-control extend-date" name="extend-date" />';
                                $action .= '<a href="#" class="btn btn-primary extend-btn" data-id="'.$hold->holdid.'" >Extend Hold</a>';
                                $action .= '</span>';
                                $action .= '</span>';
                                $output['hold'] .= $action;
                            }
                            $output['hold'] .= '<a href="#" class="remove-hold" data-pid="'.$hold->id.'" data-cid="'.$cid.'" aria-label="remove hold"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                            $output['hold'] .= '</td>';
                            $output['hold'] .= '</tr>';
                            $nz++;
                        }
                        $output['hold'] .= '</tbody>';
                }
                
                $credit = $this->GetMemberCredits($memberNumber);
                $ownerships = $this->GetMemberOwnerships($memberNumber);
           
                $output['credit'] = $credit;
                
//                 $transactions = $gpx->DAEGetMemberHistory($DAEMemberNo);
                $transactions = $this->GetMemberTransactions($cid, $memberNumber);
                //         echo '<pre>'.print_r($transactions, true).'</pre>';
                $html = '<div class="w-list-view dgt-container">';
                
                        
                        $html = '<thead><tr>';
                        $html .= '<td>Membership#</td><td>Resort Name</td><td>Size</td><td>Last Year Banked</td><td>Deposit My Week<td></td>';
                        $html .= '</tr></thead><tbody>';
                        
                        $ownershipTDs = array(
                            'unitweek',
                            'ResortName',
                            'Room_Type__c',
                            //'AnniversaryDate',
                            'deposit_year',
                        );
                        
                        foreach($ownerships as $ownership)
                        {
                            $html .= '<tr>';
                            foreach($ownershipTDs as $td)
                            {
                                if($td == 'ResortMemberNo')
                                {
                                    //Loren's account was showing "array" for this value on Mayan Plalace.  Added this to account for that.
                                    //In Loren's case the array was empty!  This is coming directly from DAE.
                                    if(is_array($ownership[$td]))
                                    {
                                        $ownership[$td] = implode(", ", $ownership[$td]);
                                    }
                                }
                                if(!isset($ownership[$td]))
                                {
                                    $ownership[$td] = '';
                                }
                                $html .= '<td>'.$ownership[$td].'</td>';
                                    
                                if($td == 'resortID' && !empty($ownership[$td]))
                                {
                                    $resortNameForDeposit = $ownership[$td];
                                }
                                if($td == 'Year_Last_Banked__c' && !empty($ownership[$td]) && isset($resortNameForDeposit) && !empty($resortNameForDeposit))
                                {
                                    $resortForDeposit = $resortNameForDeposit;
                                }
                            }
                            $dy = date('Y');
                            if(!empty($ownership['Year_Last_Banked__c']))
                            {
                                $dy = $ownership['Year_Last_Banked__c']+1;
                            }
                            $dye = $dy + 1;
                            $html .= '<td>';
                            if($ownership["Contract_Status__c"] == 'Active')
                            {
                                $html .= '<select class="ownership-deposit">';
                                $html .= '<option> SELECT A YEAR</option>';
                                for($i=$dy; $i <= $dye; $i++)
                                {
                                    $html .= '<option>'.$i.'</option>';
                                }
                                
                                $html .= '</select>';
                            }
                            else
                            {
                                $html .= $ownership["Contract_Status__c"];
                            }
                            $html .= '</td>';
                            $html .= '</tr>';
                        }
                        $html .= '</tbody>';
                        $output['ownership'] = $html;
                        $types = array(
                            'Deposit'=>array(
                                'id'=>'Ref No.',
                                'unitinterval'=>'Interval',
                                'resort_name'=>'Resort Name',
                                'deposit_year'=>'Entitlement Year',
                                'unit_type'=>'Unit Size/Occupancy',
                                'status'=>'Status',
                                'credit'=>'Credit Balance',
                                'credit_expiration_date'=>'Expiration Date',
                                'ice'=>'Use or Extend My Deposit',
                            ),
                            'Depositused'=>array(
                                'id'=>'Ref No.',
                                'unitinterval'=>'Interval',
                                'resort_name'=>'Resort Name',
                                'deposit_year'=>'Entitlement Year',
                                'unit_type'=>'Unit Size/Occupancy',
                                'status'=>'Status',
                                'credit'=>'Credit Balance',
                                'credit_expiration_date'=>'Expiration Date',
                                'ice'=>'',
                            ),
                            'Rental'=>array(
                                'weekId'=>'Ref No.',
                                'ResortName'=>'Resort Name',
                                'room_type'=>'Room Type',
                                'GuestName'=>'Guest Name',
                                'checkIn'=>'Check In',
                                'Paid'=>'Paid',
                            ),
                            'Exchange'=>array(
                                'weekId'=>'Ref No.',
                                'ResortName'=>'Resort Name',
                                'room_type'=>'Room Type',
                                'GuestName'=>'Guest Name',
                                'checkIn'=>'Check In',
                                'Paid'=>'Paid',
                            ),
                            'Misc'=>array(
                                'id'=>'Ref No.',
                                'type'=>'Type',
                                'Paid'=>'Paid',
                            ),
                        );
                        //         $output['credit'] = 0;
//                         usort($transactions->ExchnageTransactions->TransactionDetail, function($a, $b)
//                         {
//                             return strcmp(strtotime($b->Checkin), strtotime($a->Checkin));
//                         });
//                         usort($transactions->BonusRentalTransactions->TransactionDetail, function($a, $b)
//                         {
//                             return strcmp(strtotime(strtotime($b->Checkin), $a->Checkin));
//                         });
//                         usort($transactions->DepositTransactions->TransactionDetail, function($a, $b)
//                         {
//                             return strcmp($b->entYear, $a->entYear);
//                         });
                        foreach($types as $key=>$type)
                        {
                            $key = strtolower($key);
                            
                            $output[$key] = '<thead><tr>';
                            foreach($type as $th)
                            {
                                $output[$key] .= '<td>'.$th.'</td>';
                            }
                            $output[$key] .= '</tr></thead><tbody>';
                            foreach($transactions[$key] as $transaction)
                            {
                                $cancelledClass = '';
                                if($transaction['cancelled'] > 0)
                                {
                                    $cancelledClass = 'cancelled-week';
                                }
                                $output[$key] .= '<tr>';
                                foreach($type as $tk=>$td)
                                {
                                    if($tk == 'Paid')
                                    {
                                        $transaction['Paid'] = '$'.$transaction['Paid'];
                                        if($transaction['Paid'] == '$')
                                        {
                                            $transaction['Paid'] = '-';
                                        }
                                    }
                                    if($tk == 'status' && $transaction['status'] != 'Denied')
                                    {
//                                         if(get_current_user_id() == 5)
//                                         {
//                                             echo '<pre>'.print_r($transaction, true).'</pre>';
//                                         }
                                        if($transaction['credit_amount'] == 0 && $transaction['status'] != 'Cancelled')
                                        {
                                            $transaction['status'] = 'PENDING';
                                        }
                                        elseif($transaction['status'] == 'Cancelled')
                                        {
                                            $transaction['status'] = 'REMOVED';
                                        }
                                        elseif($transaction['credit_action'] == 'donated')
                                        {
                                            $transaction['status'] = 'DONATED';
                                        }
                                        elseif($transaction['credit_action'] == 'transferred')
                                        {
                                            $transaction['status'] = 'TRANSFERRED';
                                        }
                                        elseif($transaction['credit'] <= 0)
                                        {
                                            $transaction['status'] = 'USED';
                                        }
                                        elseif(strtotime($transaction['credit_expiration_date'].' 23:59:59') < strtotime('now'))
                                        {
                                            $transaction['status'] = 'EXPIRED';
                                        }
                                        else 
                                        {
                                            $transaction['status'] = 'ACTIVE';
                                        }
                                        
                                    }
                                    elseif($tk == 'status')
                                    {
                                        $transaction['status'] = 'DENIED';
                                    }
                                    if($tk == 'type')
                                    {
                                        if($transaction[$tk] == 'Deposit')
                                        {
                                            $transaction[$tk] = 'Late Deposit Fee';
                                        }
                                        if($transaction[$tk] == 'Extension')
                                        {
                                            $transaction[$tk] = 'Credit Extension Fee';
                                        }
                                        if($transaction[$tk] == 'Guest')
                                        {
                                            $transaction[$tk] = 'Guest Fee';
                                        }
                                        if($transaction[$tk] == 'Credit_donation')
                                        {
                                            $transaction[$tk] = 'Credit Donation';
                                        }
                                        if($transaction[$tk] == 'Credit_transfer')
                                        {
                                            $transaction[$tk] = 'Credit Transfer';
                                        }
                                    }
                                    if(($tk == 'credit_expiration_date' || $tk == 'checkIn') && !empty($transaction[$tk]))
                                    {
                                        $transaction[$tk] = date('m/d/Y', strtotime($transaction[$tk]));
                                    }
                                    if($tk == 'ice')
                                    {
                                        $transaction[$tk] = '';
                                        if($transaction['status'] == 'PENDING')
                                        {
                                            $transaction[$tk] = '<span class="credit-pending">Credit Pending</span>';
                                        }
                                        elseif(($key == 'deposit' && !empty($transaction['credit_action'])) || $transaction['status'] == 'INACTIVE' || date('m/d/Y', strtotime($transaction['credit_expiration_date'])) == '01/01/1970' || date('m/d/Y', strtotime($transaction['credit_expiration_date'])) == '12/31/1969')
                                        {
                                            
                                        }
                                        else
                                        {
                                            $fromDate = date('Y, m, d', strtotime($transaction['credit_expiration_date']));
                                            $endDate = date('Y, m, d', strtotime($transaction['credit_expiration_date'].' +1 year'));
                                            $iceOptions = [];
                                            $iceExtendBox = '';
//                                             if($agent && $transaction['extension_valid'] == 1 && $transaction['credit'] > 0)
                                            if($transaction['extension_valid'] == 1 && $transaction['credit'] > 0)
                                            {
                                                $iceOptions[] = '<option class="extension_date_can_change credit-extension">Extend</option>';
                                                $iceExtendBox .= '<span class="extend-input" style="display: none;">';
                                                $iceExtendBox .= '<a href="#" class="close-box"><i class="fa fa-close"></i></a>';
                                                $iceExtendBox .= '<input type="hidden" class="form-control credit-extension-date" name="extend-date" data-interval="'.$transaction['unitinterval'].'" data-id="'.$transaction['id'].'"  data-datefrom="'.$fromDate.'" data-dateto="'.$endDate.'" data-amt="'.get_option('gpx_extension_fee').'" />';
                                                $iceExtendBox .= '<p>Are you sure you want to extend this deposit?<br /><br /><a href="#" class="btn btn-primary credit-extension-btn" data-interval="'.$transaction['unitinterval'].'" data-id="'.$transaction['id'].'" >Yes</a></p>';
                                                $iceExtendBox .= '</span>';
                                            }
                                            if(empty($transaction['credit_action']) && $key == 'deposit' && $transaction['credit'] > 0 && strtolower($transaction['status']) == 'active')
                                            {
                                                $iceOptions[] .= '<option class="credit-donate-btn" data-type="donated" data-id="'.$transaction['id'].'">Donate</option>';
                                                $iceOptions[] .= '<option class="perks-link" data-type="perks" data-id="'.$transaction['id'].'">Perks</option>';
                                                $iceExtendBox .= '<span class="donate-input" style="display: none;">';
                                                $iceExtendBox .= '<a href="#" class="close-box"><i class="fa fa-close"></i></a>';
                                                $iceExtendBox .= '<p>Are you sure you want to donate this deposit?<br /><br /><a href="#" class="btn btn-primary credit-donate-transfer" data-interval="'.$transaction['unitinterval'].'" data-id="'.$transaction['id'].'" >Yes</a></p>';
                                                $iceExtendBox .= '</span>';
//                                                 $iceOptions[] .= '<option class="credit-donate-transfer" data-type="transferred" data-id="'.$transaction['id'].'">Perks</option>';
                                            }
                                            if(!empty($iceOptions))
                                            {
                                                $transaction[$tk] = '<span class="extend-box">';
                                                $transaction[$tk] .= '<select class="ice-select" style="max-width: 100px;">';
                                                $transaction[$tk] .= '<option>Select</option>';
                                                $transaction[$tk] .= implode('', $iceOptions);
                                                $transaction[$tk] .= '</select>';
                                                $transaction[$tk] .= $iceExtendBox;
                                                $transaction[$tk] .= '</span>';
                                            }
                                        }
                                    }
                                    $output[$key] .= '<td class="'.$cancelledClass.'">';
                                    $output[$key] .= $transaction[$tk];
                                    if($key != 'deposit' && $tk == 'weekId')
                                    {
                                        if(isset($transaction['pending']))
                                        {
                                            $output[$key] .= ' -- Pending Deposit';
                                        }
                                        else
                                        {
                                            $output[$key] .= ' <a class="hide-slash" href="/booking-path-confirmation?confirmation='.$transaction['cartID'].'" title="View Confirmation" target="_blank"><i class="fa fa-file-text" aria-hidden="true"></i></a>';
                                            //is this a logged in agent?
                                            if($agent && $key != 'misc')
                                            {
                                                $output[$key] .= ' | <a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=transactions_view&id='.$transaction['id'].'" class="agent-cancel-booking" data-agent="'.$agent.'" data-transaction="'.$transaction['id'].'" title="Edit Transaction"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                                            }
                                        }
                                    }
                                    $output[$key] .= '</td>';
                                    
                                }
                                $output[$key] .= '</tr>';
                            }
                            $output[$key] .= '</tbody>';
                        }
                        
                        return $output;
    }
    
    public function get_gpx_json_reports($table="wp_gpxTransactions", $days='10')
    {
        global $wpdb;
        $datevar = 'datetime';
        if($table == 'wp_gpxFailedTransactions')
            $datevar = 'date';
            $today = date('Y-m-d');
            $date = date('Y-m-d 00:00:00', strtotime("-".$days." day", strtotime($today)));
            $sql = "SELECT * FROM ".$table." WHERE ".$datevar." >= '".$date."'";
            if($table == 'wp_gpxMemberSearch')
            {
                $results = $wpdb->get_results($sql);
                foreach($results as $row)
                {
                    $userID = $row->userID;
                    $data = json_decode($row->data);
                    foreach($data as $sKey=>$sValue)
                    {
                        $transactionID = '';
                        $user = get_userdata($userID);
                        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $userID ) );
                        $name = $usermeta->first_name." ".$usermeta->last_name;
                        $name = str_replace(",", "", $name);
                        $splitKey = explode('-', $sKey);
                        
                        if($splitKey[0] == 'bookattempt')
                        {
                            $sql = "SELECT id FROM wp_gpxTransactions WHERE weekId='".$sValue->Booking->WeekID."'";
                            $transactionID = $wpdb->get_row($sql);
                            if(!empty($transactionID))
                                continue;
                                $rows['bookattempt'][$n]['sessionID'] = $row->sessionID;
                                $rows['bookattempt'][$n]['cartID'] = $row->cartID;
                                $rows['bookattempt'][$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                                $rows['bookattempt'][$n]['guest_name'] = html_entity_decode($name);
                                $rows['bookattempt'][$n]['email'] = $user->user_email;
                                $rows['bookattempt'][$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                                $rows['bookattempt'][$n]['WeekType'] = $sValue->WeekType;
                                $rows['bookattempt'][$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->paid);
                                $rows['bookattempt'][$n]['id'] = $sValue->$splitKey[1];
                                $rowsv[$n]['weekId'] = $sValue->WeekID;
                        }
                        if($splitKey[0] == 'select')
                        {
                            $rows['select'][$n]['sessionID'] = $row->sessionID;
                            $rows['select'][$n]['cartID'] = $row->cartID;
                            $rows['select'][$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                            $rows['select'][$n]['guest_name'] = html_entity_decode($name);
                            $rows['select'][$n]['email'] = $user->user_email;
                            $rows['select'][$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                            $rows['select'][$n]['refDomain'] = $sValue->refDomain;
                            $rows['select'][$n]['currentPage'] = $sValue->currentPage;
                            $rows['select'][$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->price);
                            $rows['select'][$n]['WeekPrice'] = $sValue->WeekPrice;
                            $rows['select'][$n]['id'] = $sValue->property->id;
                            $rows['select'][$n]['ResortName'] = stripslashes($sValue->property->ResortName);
                            $rows['select'][$n]['WeekType'] = $sValue->property->WeekType;
                            $rows['select'][$n]['bedrooms'] = $sValue->property->bedrooms;
                            $rows['select'][$n]['weekId'] = $sValue->property->weekId;
                            $rows['select'][$n]['checkIn'] = date("m/d/Y", strtotime($sValue->property->checkIn));
                        }
                        if($splitKey[0] == 'view')
                        {
                            $rows['view'][$n]['sessionID'] = $row->sessionID;
                            $rows['view'][$n]['cartID'] = $row->cartID;
                            $rows['view'][$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                            $rows['view'][$n]['guest_name'] = html_entity_decode($name);
                            $rows['view'][$n]['email'] = $user->user_email;
                            $rows['view'][$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                            $rows['view'][$n]['refDomain'] = $sValue->refDomain;
                            $rows['view'][$n]['currentPage'] = $sValue->currentPage;
                            $rows['view'][$n]['WeekType'] = $sValue->week_type;
                            $rows['view'][$n]['price'] = preg_replace("/[^0-9,.]/", "", $sValue->price);
                            $rows['view'][$n]['id'] = $sValue->id;
                            $rows['view'][$n]['ResortName'] = stripslashes($sValue->name);
                            $rows['view'][$n]['checkIn'] = date("m/d/Y", strtotime($sValue->checkIn));
                            $rows['view'][$n]['bedrooms'] = $sValue->beds;
                            $rows['view'][$n]['search_location'] = $sValue->search_location;
                            $rows['view'][$n]['search_month'] = $sValue->search_month;
                            $rows['view'][$n]['search_year'] = $sValue->search_year;
                        }
                        if($splitKey[0] == 'resort')
                        {
                            $rows['resortview'][$n]['sessionID'] = $row->sessionID;
                            $rows['resortview'][$n]['cartID'] = $row->cartID;
                            $rows['resortview'][$n]['daeMemberNo'] = $usermeta->DAEMemberNo;
                            $rows['resortview'][$n]['guest_name'] = html_entity_decode($name);
                            $rows['resortview'][$n]['email'] = $user->user_email;
                            $rows['resortview'][$n]['timestamp'] = date("m/d/Y", strtotime($row->datetime));
                            $rows['resortview'][$n]['ResortName'] = stripslashes($sValue->ResortName);
                            $rows['resortview'][$n]['id'] = $sValue->id;
                            $rows['resortview'][$n]['search_location'] = $sValue->search_location;
                            $rows['resortview'][$n]['search_month'] = $sValue->search_month;
                            $rows['resortview'][$n]['search_year'] = $sValue->search_year;
                        }
                        $n++;
                    }
                }
            }
            else
            {
                $rows = $wpdb->get_results($sql);
                foreach($rows as $row)
                {
                    $data = json_decode($row->data);
                    unset($row->data);
                    $row->data = $data;
                }
            }
            return $rows;
    }
    
    public function gpx_report_writer($return)
    {
        global $wpdb;
        
        /*
         * the first key is the table that will be used
         * Name is the name that will be displayed on a page
         * Fields are the field being used
         *      if the field is an array then it is a different type
         *          join is a joined table
         *              xref on join is the field as used when writing the query
         *          case is used when an integer (enum) represents a variable -- for example: wp_room type is 1=Exchange, 2=Rental, 3=Both
         *              xref on case is the field as used when writing the query
		 *			joincase is both a join and a case
     	 *          usermeta pulls from usermeta table 
		 *			json is used to extract json data from the table -- Key is the json object key and value is what is displayed on the writer or as a column heading   
         */
        //transactins add member address and phone, guest phone, 
        $tables = [
            'wp_gpxOwnerCreditCoupon'=>[
                'table'=>'wp_gpxOwnerCreditCoupon',
                'name'=>'Owner Credit Coupon',
                'fields'=>[
                    'id'=>'ID',
                    'name'=>'Name',
                    'couponcode'=>'Coupon Code',
                    'comments'=>'Comments',
                    'singleuse'=>[
                        'type'=>'case',
                        'column'=>'singleuse',
                        'name'=>'Single Use',
                        'xref'=>'wp_gpxOwnerCreditCoupon.singleuse',
                        'case'=>[
                            '0'=>'No',
                            '1'=>'Yes',
                        ],
                    ],
                    'active'=>[
                        'type'=>'case',
                        'column'=>'active',
                        'name'=>'Active',
                        'xref'=>'wp_gpxOwnerCreditCoupon.active',
                        'case'=>[
                            '0'=>'No',
                            '1'=>'Yes',
                        ],
                    ],
                    'expirationDate'=>'Expiration Date',
                    
                    'memberFirstName'=>[
                        'type'=>'usermeta',
                        'xref'=>'ownerID',
                        'column'=>'first_name',
                        'name'=>'Owner First Name',
                        'key'=>'memberFirstName',
                        'on'=>[
                            'wp_gpxOwnerCreditCoupon_owner ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_owner.couponID'
                        ],
                    ],
                    'memberLastName'=>[
                        'type'=>'usermeta',
                        'xref'=>'ownerID',
                        'column'=>'last_name',
                        'name'=>'Owner Last Name',
                        'key'=>'memberLastName',
                        'on'=>[
                            'wp_gpxOwnerCreditCoupon_owner ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_owner.couponID'
                        ],
                    ],
                    'memberEmail'=>[
                        'type'=>'usermeta',
                        'xref'=>'ownerID',
                        'column'=>'user_email',
                        'name'=>'Owner Email',
                        'key'=>'memberEmail',
                        'on'=>[
                            'wp_gpxOwnerCreditCoupon_owner ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_owner.couponID'
                        ],
                    ],
                    'activity'=>[
                        'type'=>'join',
                        'column'=>'activity',
                        'name'=>'Activity',
                        'xref'=>'wp_gpxOwnerCreditCoupon.activity',
                        'where'=>'wp_gpxOwnerCreditCoupon_activity.activity',
                        'on'=>[
                            'wp_gpxOwnerCreditCoupon_activity ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_activity.couponID'
                        ],
                    ],
                    'amount'=>[
                        'type'=>'join',
                        'column'=>'amount',
                        'name'=>'Amount',
                        'xref'=>'wp_gpxOwnerCreditCoupon.amount',
                        'where'=>'wp_gpxOwnerCreditCoupon_activity.amount',
                        'on'=>[
                            'wp_gpxOwnerCreditCoupon_activity ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_activity.couponID'
                        ],
                    ],
                    'activity_comments'=>[
                        'type'=>'join',
                        'column'=>'activity_comments',
                        'name'=>'Activity Comments',
                        'xref'=>'wp_gpxOwnerCreditCoupon.activity_comments',
                        'on'=>[
                            'wp_gpxOwnerCreditCoupon_activity ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_activity.couponID'
                        ],
                    ],
                    'activity_date'=>[
                        'type'=>'join',
                        'column'=>'datetime',
                        'name'=>'Activity Date',
                        'xref'=>'wp_gpxOwnerCreditCoupon.activity_date',
                        'where'=>'wp_gpxOwnerCreditCoupon_activity.datetime',
                        'on'=>[
                            'wp_gpxOwnerCreditCoupon_activity ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_activity.couponID'
                        ],
                    ],
                    'issuerFirstName'=>[
                        'type'=>'usermeta',
                        'xref'=>'userID',
                        'column'=>'first_name',
                        'name'=>'Issued by First Name',
                        'key'=>'issuerFirstName',
                        'on'=>[
                            'wp_gpxOwnerCreditCoupon_activity ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_activity.couponID'
                        ],
                    ],
                    'issuerLastName'=>[
                        'type'=>'usermeta',
                        'xref'=>'userID',
                        'column'=>'last_name',
                        'name'=>'Issued by Last Name',
                        'key'=>'issuerLastName',
                        'on'=>[
                            'wp_gpxOwnerCreditCoupon_activity ON wp_gpxOwnerCreditCoupon.id=wp_gpxOwnerCreditCoupon_activity.couponID'
                        ],
                    ],
                ],
            ],
            'wp_room'=>[
                'table'=>'wp_room',
                'name'=>'Inventory',
                'fields'=>[
                    'record_id'=>'ID',
                    'GuestName'=>[
                        'type'=>'join_json',
                        'column'=>'data.GuestName',
                        'name'=>'Guest Name',
                        'xref'=>'wp_room.GuestName',
                        'on'=>[
                            'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id'
                        ],
                    ],
                    // Credits Used
                    'credit_add'=>[
                        'type'=>'join_case',
                        'column'=>'wp_partner.user_id',
                        'column_special' => 'credit_add',
                        'name'=>'Credit Add',
                        'xref'=>'wp_room.credit_add',
                        'where'=>'wp_partner.user_id',
                        'column_override'=>'credit_add',
                        'as'=>'credit_add',
                        'case_special'=>[
                            'NULL'=>'0',
                            'NOT NULL'=>'1',
                        ],
                        'on'=>[
                            'wp_partner ON wp_partner.user_id=wp_room.source_partner_id'
                        ],
                    ],
                    'credit_subtract'=>[
                        'type'=>'join_case',
                        'column'=>'query|credit_subtract|(SELECT COUNT(*) FROM wp_partner WHERE wp_partner.user_id=wp_gpxTransactions.userID)',
                        'column_special' => 'credit_subtract',
                        'name'=>'Credit Subtract',
                        'xref'=>'wp_room.credit_subtract',
                        'where'=>'wp_partner.name',
                        'column_override'=>'credit_subtract',
                        'as'=>'credit_subtract',
                        'case'=>[
                            '0'=>'0',
                            '1'=>'1',
                        ],
                        'on'=>[
                            'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id'
                        ],
                    ],
                    'resort_confirmation_number'=>'Resort Confirmation Number',
                    'create_date'=>'Created Date',
                    'active'=>[
                        'type'=>'case',
                        'column'=>'active',
                        'name'=>'Active Yes or No',
                        'xref'=>'wp_room.active',
                        'case'=>[
                            '0'=>'No',
                            '1'=>'Yes',
                        ],
                    ],
                    'source_partner_id'=>[
                        'type'=>'join',
                        'column'=>'source_partner_id',
                        'name'=>'Partner ID',
                        'xref'=>'wp_room.source_partner_id',
                        'on'=>[
                            'wp_partner ON wp_partner.record_id=wp_room.source_partner_id'
                        ],
                    ],
                    'source_partner_name'=>[
                        'type'=>'join',
                        'column'=>'stbl.name',
                        'column_override'=>'source_partner_name',
                        'as'=>' source_partner_name',
                        'name'=>'Source Partner Name',
                        'xref'=>'wp_room.source_partner_name',
                        'on'=>[
                            'wp_partner stbl ON stbl.user_id=wp_room.source_partner_id'
                        ],
                    ],
                    'booked_by_partner_name'=>[
                        'type'=>'join',
                        'column'=>'btbl.name',
                        'column_override'=>'booked_by_partner_name',
                        'as'=>'booked_by_partner_name',
                        'name'=>'Booked By Partner Name',
                        'xref'=>'wp_room.booked_by_partner_name',
                        'on'=>[
                            'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id',
                            'wp_partner btbl ON btbl.user_id=wp_gpxTransactions.userID'
                        ],
                    ],
                    'partner_name'=>[
                        'type'=>'join',
                        'column'=>'COALESCE(stbl.name, btbl.name)',
//                         'columns'=>[
//                             'name'=>'wp_room.partner_name',
//                             'cols'=>[
//                                 'booked_by_partner_name',
//                                 'source_partner_name',
//                                 ],
//                             ],
                        'name'=>'Partner Name',
                        'column_override'=>'partner_name',
                        'as'=>'partner_name',
                        'xref'=>'wp_room.partner_name',
                        'where'=>'COALESCE(stbl.name, btbl.name)',
                        'on'=>[
                            'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id',
                            'wp_partner btbl ON btbl.user_id=wp_gpxTransactions.userID',
                            'wp_partner stbl ON stbl.user_id=wp_room.source_partner_id'
                        ],
                    ],
                    'status'=>[
                        'type'=>'join',
                        'column'=>'status',
                        'name'=>'Status',
                        'xref'=>'wp_room.status',
                        'on'=>[
                            'room_status ON room_status.weekId=wp_room.record_id'
                        ],
                    ],
                    'transactionCancelled'=>[
                        'type'=>'join_case',
                        'column'=>'cancelledDate',
                        'name'=>'Transaction Cancelled Date',
                        'on'=>[
                            'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id',
                        ],
                    ],
                    'user'=>[
                        'type'=>'join',
                        'column'=>'user',
                        'name'=>'Held For',
                        'xref'=>'wp_room.user',
                        'on'=>[
                            'wp_gpxPreHold ON wp_room.record_id=wp_gpxPreHold.weekId AND wp_gpxPreHold.released=0'
                        ],
                    ],
                    'release_on'=>[
                        'type'=>'join',
                        'column'=>'release_on',
                        'name'=>'Release Hold On',
                        'xref'=>'wp_room.release_on',
                        'on'=>[
                            'wp_gpxPreHold ON wp_room.record_id=wp_gpxPreHold.weekId AND wp_gpxPreHold.released=0'
                        ],
                    ],
                    'active_specific_date'=>'Active Date',
                    'check_in_date'=>'Check In',
                    'check_out_date'=>'Check Out',
                    'price'=>'Price',
                    'resort_name'=>[
                        'type'=>'join',
                        'column'=>'ResortName',
                        'name'=>'Resort Name',
                        'xref'=>'wp_room.resort_name',
                        'where'=>'wp_resorts.ResortName',
                        'on'=>[
                            'wp_resorts ON wp_room.resort=wp_resorts.id',
                        ],
                    ],
                    'resort_country'=>[
                        'type'=>'join',
                        'column'=>'Country',
                        'name'=>'Country',
                        'xref'=>'wp_room.resort_country',
                        'on'=>[
                            'wp_resorts ON wp_room.resort=wp_resorts.id',
                        ],
                    ],
                    'resort_state'=>[
                        'type'=>'join',
                        'column'=>'Region',
                        'name'=>'State',
                        'xref'=>'wp_room.resort_state',
                        'on'=>[
                            'wp_resorts ON wp_room.resort=wp_resorts.id',
                        ],
                    ],
                    'resort_city'=>[
                        'type'=>'join',
                        'column'=>'Town',
                        'name'=>'City',
                        'xref'=>'wp_room.resort_city',
                        'on'=>[
                            'wp_resorts ON wp_room.resort=wp_resorts.id',
                        ],
                    ],
//                     'wp_gpxRegion.name'=>[
//                         'type'=>'join',
//                         'column'=>'wp_gpxRegion.name',
//                         'name'=>'Region',
//                         'xref'=>'wp_room.wp_gpxRegion.name',
//                         'on'=>[
//                             'wp_resorts ON wp_room.resort=wp_resorts.id',
//                             'wp_gpxRegion ON wp_resorts.gpxRegionID=wp_gpxRegion.id',
//                         ],
//                     ],
                    'name'=>[
                        'type'=>'join',
                        'column'=>'wp_unit_type.name',
                        'name'=>'Unit Type',
                        'xref'=>'wp_room.name',
                        'column_override'=>'name',
                        'on'=>[
                            'wp_unit_type ON wp_unit_type.record_id=wp_room.unit_type'
                        ],
                    ],
                    'type'=>[
                        'type'=>'case',
                        'column'=>'type',
                        'name'=>'Type',
                        'xref'=>'wp_room.type',
                        'case'=>[
                            '1'=>'Exchange',
                            '2'=>'Rental',
                            '3'=>'Both',
                        ],
                    ],
                    'WeekType'=>[
                        'type'=>'join_json',
                        'column'=>'data.WeekType',
                        'name'=>'Week Type',
                        'xref'=>'wp_room.WeekType',
                        'as'=>'WeekType',
                        'column_override'=>'WeekType',
                        'on'=>[
                            'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id'
                        ],
                    ],
//                     'transaction_type'=>[
//                         'type'=>'join_json',
//                         'column'=>'data.WeekType',
//                         'name'=>'Transaction Week Type',
//                         'xref'=>'wp_room.WeekType',
//                         'on'=>[
//                             'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id'
//                         ],
//                     ],
                    'source_num'=>[
                        'type'=>'case',
                        'column'=>'source_num',
                        'name'=>'Source',
                        'xref'=>'wp_room.source_num',
                        'case'=>[
                            '1'=>'Owner',
                            '2'=>'GPR',
                            '3'=>'Trade Partner',
                        ],
                    ],
                    'cancelledDate'=>[
                        'type'=>'join',
                        'column'=>'wp_gpxTransactions.cancelledDate',
                        'name'=>'Transaction Cancelled Date',
                        'xref'=>'wp_room.cancelledDate',
                        'where'=>'wp_gpxTransactions.cancelledDate',
                        'on'=>[
                            'wp_gpxTransactions ON wp_gpxTransactions.weekId=wp_room.record_id'
                        ],
                    ],
                ],
            ],
            'wp_credit'=>[
                'table'=>'wp_credit',
                'name'=>'Credit',
				'groupBy'=>'wp_credit.id',
                'fields'=>[
                    'id'=>'ID',
                    'created_date'=>'Timestamp',
                    'credit_amount'=>"Credit Banked",
                    'credit_used'=>'Credit Used',
                    'credit_expiration_date'=>'Expiration Date',
                    'interval_number'=>'Interval',
                    'unitinterval'=>'Unit Week',
                    'resort_name'=>'Resort',
                    'deposit_year'=>'Entitlement Year',
                    'owner_id'=>'Member ID',
                    'status'=>'Status',
                    'memberFirstName'=>[
                        'type'=>'usermeta',
                        'xref'=>'owner_id',
                        'column'=>'first_name',
                        'name'=>'Member First Name',
                        'key'=>'memberFirstName',
                    ],
                    'memberLastName'=>[
                        'type'=>'usermeta',
                        'xref'=>'owner_id',
                        'column'=>'last_name',
                        'name'=>'Member Last Name',
                        'key'=>'memberLastName',
                    ],
                    'memberEmail'=>[
                        'type'=>'usermeta',
                        'xref'=>'owner_id',
                        'column'=>'user_email',
                        'name'=>'Member Email',
                        'key'=>'memberEmail',
                    ],
                    'check_in_date'=>'Arrival Date',
                    'extension_date'=>'Extension Date',
                ],
            ],
            'wp_partner'=>[
                'table'=>'wp_partner',
                'name'=>'Partners',
                'fields'=>[
                    'record_id'=>'Partner ID',
                    'create_date'=>'Timestamp',
                    'name'=>'Account Name',
                    'no_of_rooms_given'=>'Rooms Given',
                    'no_of_rooms_received_taken'=>'Rooms Received',
                    'trade_balance'=>'Trade Balance',
                    'debit_balance'=>'Amount Due',
                    'week_id'=>[
                        'type'=>'join',
                        'column'=>'record_id',
                        'name'=>'Week ID',
                        'on'=>[
                            'wp_room ON wp_room.source_partner_id=wp_partner.id',  // TODO: CONFIRM THIS WORKS!
                        ],
                        'xref'=>'wp_room.record_id',
                    ],
                    'check_in'=>[
                        'type'=>'join',
                        'column'=>'check_in',
                        'name'=>'Check In',
                        'xref'=>'wp_room.check_in',
                        'where'=>'wp_room.check_in_date',
                        'on'=>[
                            'wp_room ON wp_room.partner_id=wp_partner.id',
                        ],
                        'xref'=>'wp_partner.check_in',
                    ],
                    'ResortName'=>[
                        'type'=>'join',
                        'column'=>'ResortName',
                        'name'=>'Resort Name',
                        'xref'=>'wp_resorts.ResortName',
                        'on'=>[
                            'wp_room ON wp_room.partner_id=wp_partner.id',
                            'wp_resorts ON wp_room.resort=wp_resorts.id',
                        ],
                    ],
                    'guest_first_name'=>[
                        'type'=>'join',
                        'column'=>'data.guest_last_name',
                        'name'=>'Guest First Name',
                        'on'=>[
                            'wp_room ON wp_room.partner_id=wp_partner.id',
                            'wp_gpxTransactions ON wp_room.id=wp_gpxTransactions.weekId AND wp_gpxTransactions.cancelled=0',
                        ],
                        'xref'=>'wp_partner.guest_first_name',
                    ],
                    'guest_last_name'=>[
                        'type'=>'join',
                        'column'=>'data.guest_last_name',
                        'name'=>'Guest Last Name',
                        'on'=>[
                            'wp_room ON wp_room.partner_id=wp_partner.id',
                            'wp_gpxTransactions ON wp_room.id=wp_gpxTransactions.weekId AND wp_gpxTransactions.cancelled=0',
                        ],
                        'xref'=>'wp_partner.guest_last_name',
                    ],
                ],
            ],
           'wp_gpxTransactions'=>[
                 'table'=>'wp_gpxTransactions',
                 'name'=>'Transactions',
                 'fields'=>[
                   'id'=>'ID',
                   'transactionType'=>'Transaction Type',
                   'cartID'=>'Cart ID',
                   'sessionID'=>'Session ID',
                   'userID'=>'User ID',
                   //                      'resortID'=>'Resort ID',
                 'resort_name'=>[
                     'type'=>'join',
                     'column'=>'ResortName',
                     'name'=>'Resort Name',
                     'xref'=>'wp_gpxTransactions.resort_name',
                     'where'=>'wp_resorts.ResortName',
                     'on'=>[
                         'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                         'wp_resorts ON wp_room.resort=wp_resorts.id',
                     ],
                 ],
                 'room_check_in_date'=>[
                     'type'=>'join',
                     'column'=>'wp_room.check_in_date',
                     'name'=>'Inventory Check In',
                     'xref'=>'wp_gpxTransactions.room_check_in_date',
                     'column_override'=>'check_in_date',
                     'where'=>'wp_room.check_in_date',
                     'on'=>[
                         'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                     ],
                 ],
                 'resort_city'=>[
                     'type'=>'join',
                     'column'=>'Town',
                     'name'=>'Resort City',
                     'xref'=>'wp_gpxTransactions.resort_city',
                     'where'=>'wp_resorts.Town',
                     'on'=>[
                         'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                         'wp_resorts ON wp_room.resort=wp_resorts.id',
                     ],
                 ],
                 'resort_state'=>[
                     'type'=>'join',
                     'column'=>'Region',
                     'name'=>'Resort State',
                     'xref'=>'wp_gpxTransactions.resort_state',
                     'where'=>'wp_resorts.State',
                     'on'=>[
                         'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                         'wp_resorts ON wp_room.resort=wp_resorts.id',
                     ],
                 ],
                 'resort_confirmation_number'=>[
                     'type'=>'join',
                     'column'=>'resort_confirmation_number',
                     'name'=>'Resort Confirmation Number',
                     'xref'=>'wp_gpxTransactions.resort_confirmation_number',
                     'where'=>'wp_room.resort_confirmation_number',
                     'on'=>[
                         'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                     ],
                 ],
                 'name'=>[
                     'type'=>'join',
                     'column'=>'name',
                     'name'=>'Partner Name',
                     'xref'=>'wp_gpxTransactions.name',
                     'where'=>'wp_partner.name',
                     'on'=>[
                         'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                         'wp_partner ON wp_room.source_partner_id=wp_partner.user_id',
                     ],
                 ],
//                  'ResortName'=>[
//                      'type'=>'join',
//                      'column'=>'ResortName',
//                      'name'=>'Resort Name',
//                      'xref'=>'wp_gpxTransactions.ResortName',
//                      'on'=>[
//                          'wp_room ON wp_room.id=wp_gpxTransactions.weekId',
//                          'wp_resorts ON wp_room.resort=wp_resorts.id',
//                      ],
//                  ],
                   'unitType'=>[
                       'type'=>'join',
                       'column'=>'name',
                       'name'=>'Unit Type',
                       'xref'=>'wp_gpxTransactions.unitType',
                       'on'=>[
                           'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                           'wp_unit_type ON wp_unit_type.record_id=wp_room.unit_type',
                       ],
                   ],
                   'inventoryType'=>[
                       'type'=>'join_case',
                       'column'=>'source_num',
                       'name'=>'Inventory Type',
                       'on'=>[
                           'wp_room ON wp_room.record_id=wp_gpxTransactions.weekId',
                       ],
                       'case'=>[
                           '1'=>'Owner',
                           '2'=>'GPR',
                           '3'=>'Trade Partner'
                       ],
                       'xref'=>'wp_gpxTransactions.inventoryType',
                   ],
                   'weekId'=>'Week ID',
                   'paymentGatewayID'=>'Payment Gateway ID',
                   'sfData'=>'Salesforce Return Data',
                     'check_in_date'=> 'Check In Date',
                     'Email'=>[
                         'type'=>'usermeta',
                         'xref'=>'userID',
                         'column'=>'Email',
                         'name'=>'Member Email',
                         'key'=>'Email',
                     ],
                     'DayPhone'=>[
                         'type'=>'usermeta',
                         'xref'=>'userID',
                         'column'=>'DayPhone',
                         'name'=>'Member Phone',
                         'key'=>'DayPhone',
                     ],
                     'address'=>[
                         'type'=>'usermeta',
                         'xref'=>'userID',
                         'column'=>'address',
                         'name'=>'Member Address',
                         'key'=>'address',
                     ],
                     'city'=>[
                         'type'=>'usermeta',
                         'xref'=>'userID',
                         'column'=>'city',
                         'name'=>'Member City',
                         'key'=>'city',
                     ],
                     'state'=>[
                         'type'=>'usermeta',
                         'xref'=>'userID',
                         'column'=>'state',
                         'name'=>'Member State',
                         'key'=>'state',
                     ],
                     'country'=>[
                         'type'=>'usermeta',
                         'xref'=>'userID',
                         'column'=>'country',
                         'name'=>'Member Country',
                         'key'=>'country',
                     ],
                   'data'=>[
                       'type'=>'json',
                       'title'=>'Transaction Details',
                       'data'=>[
                           'MemberNumber'=>'Member Number',
                           'MemberName'=>'Member Name',
                           'GuestName'=>'Guest Name',
//                            'Email'=>'Guest Email',
                           'Adults'=>'Adults',
                           'Children'=>'Children',
                           'UpgradeFee'=>'Upgrade Fee',
                           'CPO'=>'Flex Booking',
//                            'CPOFee'=>'CPO Fee',
                           'Paid'=>'Paid',
                           'WeekType'=>'Week Type',
//                            'resortName'=>'Resort Name',
                           'WeekPrice'=>'Week Price',
                           'Balance'=>'Balance',
                           'ResortID'=>'Resort ID',
                           'sleeps'=>'Sleeps',
                           'bedrooms'=>'Bedrooms',
                           'Size'=>'Size',
                           'noNights'=>'Number of Nights',
                           'checkIn'=>'Check In',
                           'processedBy'=>'Processed By ID',
                           'specialRequest'=>'Special Request',
                           'promoName'=>'Promo Name',
                           'discount'=>'Discount',
                           'coupon'=>'Coupon',
                           'couponDiscount'=>'Coupon Discount',
//                            'taxCharged'=>'Tax Charged',
                           'actWeekPrice'=>'Actual Week Price Paid',
                           'actcpoFee'=>'Actual Flex Fee Paid',
                           'actextensionFee'=>'Actual Extension Fee Paid',
                           'actguestFee'=>'Actual Guest Fee Paid',
                           'actupgradeFee'=>'Actual Upgrade Fee Paid',
                           'acttax'=>'Actual Tax Paid',
                       ],
                   ],
                   'agent'=>[
                       'type'=>'agentname',
                       'from'=>'data.processedBy',
                       'column'=>'agent',
                       'name'=>'Processed By Name',
                       'xref'=>'wp_gpxTransations.agent',
                   ],
                   'datetime'=>'Timestamp',
                     'cancelled'=>[
                        'type'=>'case',
                        'column'=>'cancelled',
                        'name'=>'Cancelled',
                        'xref'=>'wp_gpxTransactions.cancelled',
                        'case'=>[
                            '0'=>'No',
                            '1'=>'Yes',
                        ],
                    ],
                   'cancelledDate'=> 'Transaction Cancelled Date',
                   'cancelledData'=>[
                         'type'=>'json_split',
                         'title'=>'Edit Details',
                         'cancelledData'=>[
//                              'type'=>'Cancelled Type',
                             'action'=>'Cancelled Action',
                             'amount_sub'=>'Cancelled Amount Subtotal',
                             'amount'=>'Cancelled Amount',
                             'name'=>'Cancel Performed By',
//                              'date'=>'Cancel Date',
                         ],
                     ],
               ],
           ],
        ];
        
        if($return == 'tables')
        {
            $output = $tables;
        }
        
        return $output;
    }
    
    
    private function map_dae_to_vest_properties_reports()
    {
        $mapPropertiesToRooms = [
            'id' => 'record_id',
            'checkIn'=>'check_in_date',
            'checkOut'=>'check_out_date',
            'Price'=>'price',
            'weekID'=>'record_id',
            'weekId'=>'record_id',
            'StockDisplay'=>'availability',
            'WeekType' => 'type',
            'noNights' => 'DATEDIFF(a.check_out_date, a.check_in_date)',
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
    
    public function get_csv_download($table, $column, $days='', $email='', $dateFrom='', $dateTo='')
    {
        // ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);
        global $wpdb;
        
        $joinedTbl = $this->map_dae_to_vest_properties_reports();
        
        $where = '';
        if($table == 'wp_cart')
        {
            $where .= ' WHERE datetime > "2017-12-31 23:59:59"';
            $where .= ' AND cartID != ""';
        }
        
        //         if($table == 'wp_gpxMemberSearch' || $table == 'wp_gpxTransactions')
        if($table == 'wp_gpxMemberSearch')
        {
            $today = date('Y-m-d');
            $datefrom = date('Y-m-d 23:59:59', strtotime("-".$days." day", strtotime($today)));
            $where .= ' WHERE datetime > "'.$datefrom.'"';
        }
        if($table == 'wp_specials')
        {
            $ids = explode("_", $days);
            $indIds = explode(",", $ids[2]);
            $where .= ' WHERE id BETWEEN '.$ids[0].' AND '.$ids[1];
            if(!empty($indIds))
            {
                $where .= " OR id in ('".implode("','", $indIds)."')";
            }
        }
        
        $sql = "SELECT * FROM ".$table.$where." ORDER BY `id`";
        
        if($table == 'wp_gpxTransactions')
        {
            $select = "SELECT t.id as transactionID, t.*,
                        ".implode(', ', $joinedTbl['joinRoom']).",
                        ".implode(', ', $joinedTbl['joinResort']).",
                        ".implode(', ', $joinedTbl['joinUnit']).",
                        ".$joinedTbl['roomTable']['alias'].".record_id as PID, ".$joinedTbl['resortTable']['alias'].".id as RID";
            $from = " FROM wp_gpxTransactions t";
            $joinedTable[] = " LEFT OUTER JOIN ".$joinedTbl['roomTable']['table']." ".$joinedTbl['roomTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=t.weekId";
            $joinedTable[] = " LEFT OUTER JOIN ".$joinedTbl['resortTable']['table']." ".$joinedTbl['resortTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".resort=".$joinedTbl['resortTable']['alias']." .id";
            $joinedTable[] = " LEFT OUTER JOIN ".$joinedTbl['unitTable']['table']." ".$joinedTbl['unitTable']['alias']." ON ".$joinedTbl['roomTable']['alias'].".unit_type=".$joinedTbl['unitTable']['alias'].".record_id";
            
            
            $wheres[] = ' WHERE cartID != ""';
            
            if(!empty($dateFrom))
            {
                $wheres[] = ' t.datetime BETWEEN "'.date('Y-m-d 00:00:00', strtotime($dateFrom)).'" AND "'.date('Y-m-d 23:59:59', strtotime($dateTo)).'"';
            }
            $where = implode(" AND ", $wheres);
            $join = implode(" ", $joinedTable);
            $sql = $select.$from.$join.$where." GROUP BY t.id ORDER BY t.id";
        }
        
        
        if($table == 'wpmeta')
        {
            $headStart = [
                'DAEMemberNo',
                'AccountName',
                'FirstName1',
                'FirstName2',
                'LastName1',
                'LastName2',
                'DayPhone',
                'HomePhone',
                'Mobile',
                'Mobile1',
                'Mobile2',
                'Email',
                'Email',
                'Address1',
                'Address2',
                'Address3',
                'Address4',
                'Address5',
                'PostCode',
                'ResortShareID',
                'ResortMemeberID',
                'ReferalID',
                'OwnershipWeekType',
            ];
            $heads = $headStart;
            $args = array(
                'role'    => 'gpx_member',
            );
            $allOwners = get_users($args);
            $aoi = 0;
            foreach($allOwners as $ao)
            {
                foreach($headStart as $meta)
                {
                    $rows[$ao->ID][$meta] = get_user_meta($ao->ID, $meta, true);
                }
            }
        }
        else
        {
            $rows = $wpdb->get_results($sql);
        }
      
        $upload_dir = wp_upload_dir();
        $fileLoc = '/var/www/reports/'.$table.'.csv';
        $file = fopen($fileLoc, 'w');
        
        $heads = array();
        $values = array();
        $n = 0;
        if($table == 'wp_cart')
        {
            $headStart = array('id', 'datetime');
            $heads = $headStart;
        }
        if($table == 'wp_gpxTransactions')
        {
            $headStart = [
                'transactionID', 
                'datetime', 
                'resortName', 
                'weekId', 
                'check_in_date', 
                'noNights', 
                'Size', 
                'WeekType', 
                'MemberNumber', 
                'MemberName', 
                'GuestName', 
                'Adults', 
                'Children', 
                'CPO',
                'cancelled', 
                'actWeekPrice', 
                'actupgradeFee', 
                'actcpoFee', 
                'acttax', 
                'actguestFee', 
                'actextensionFee', 
                'lateDepositFee', 
                'specialRequest', 
                'coupon', 
                'couponDiscount', 
                'promoName', 
                'discount', 
                'ownerCreditCouponID', 
                'ownerCreditCouponAmount',
                'Paid',
                'refundaction', 
                'refundamount',
            ];
            $heads = $headStart;
        }
        if($table == 'wp_gpxMemberSearch')
        {
            $headStart = array('id', 'datetime', 'userID','action','id','price','ResortName','WeekType','bedrooms','weekId','checkIn','refDomain','search_location','search_month','search_year');
            $heads = $headStart;
        }
        if($table == 'wp_specials')
        {
            $headStart = array('id', 'Name', 'first_name', 'last_name', 'emsID');
            $heads = $headStart;
        }
        foreach($rows as $row)
        {
            if($table == 'wpmeta')
            {
                $values[$n] = $row;
            }
            else
            {
                $data = json_decode($row->$column);
            }
            
            if($table == 'wpmeta')
            {
                //nothing
            }
            elseif($table == 'wp_specials')
            {
                $n++;
                $z=1;
                $specificCustomers = json_decode($data->specificCustomer);
                foreach($specificCustomers as $customer)
                {
                    //get their usermeta
                    $values[$row->id.$n.$z]['id'] = $row->id;
                    $values[$row->id.$n.$z]['Name'] = $row->Name;
                    $values[$row->id.$n.$z]['first_name'] = get_user_meta($customer, 'first_name', true);
                    $values[$row->id.$n.$z]['last_name'] = get_user_meta($customer, 'last_name', true);
                    $values[$row->id.$n.$z]['emsID'] = get_user_meta($customer, 'DAEMemberNo', true);
                    $z++;
                }
            }
            elseif($table == 'wp_gpxMemberSearch')
            {
                foreach($data as $sKey=>$sValue)
                {
                    $splitKey = explode('-', $sKey);
                    if($splitKey[0] == 'select')
                    {
                        $values[$n]['id'] = $row->id;
                        $values[$n]['datetime'] = $row->datetime;
                        $values[$n]['action'] = 'select';
                        $values[$n]['action'] = 'select';
                        $values[$n]['userID'] = $row->userID;
                        $values[$n]['refDomain'] = $sValue->refDomain;
                        $values[$n]['currentPage'] = $sValue->currentPage;
                        $values[$n]['price'] = $sValue->price;
                        $values[$n]['WeekPrice'] = $sValue->WeekPrice;
                        $values[$n]['propertyID'] = $sValue->property->id;
                        $values[$n]['ResortName'] = stripslashes($sValue->property->ResortName);
                        $values[$n]['WeekType'] = $sValue->property->WeekType;
                        $values[$n]['bedrooms'] = $sValue->property->bedrooms;
                        $values[$n]['weekId'] = $sValue->property->weekId;
                        $values[$n]['checkIn'] = date("m/d/Y", strtotime($sValue->property->checkIn));
                    }
                    if($splitKey[0] == 'view')
                    {
                        $values[$n]['id'] = $row->id;
                        $values[$n]['datetime'] = $row->datetime;
                        $values[$n]['action'] = 'view';
                        $values[$n]['userID'] = $row->userID;
                        $values[$n]['refDomain'] = $sValue->refDomain;
                        $values[$n]['currentPage'] = $sValue->currentPage;
                        $values[$n]['WeekType'] = $sValue->week_type;
                        $values[$n]['price'] = $sValue->price;
                        $values[$n]['propertyID'] = $sValue->id;
                        $values[$n]['ResortName'] = stripslashes($sValue->name);
                        $values[$n]['checkIn'] = date("m/d/Y", strtotime($sValue->checkIn));
                        $values[$n]['bedrooms'] = $sValue->beds;
                        $values[$n]['search_location'] = $sValue->search_location;
                        $values[$n]['search_month'] = $sValue->search_month;
                        $values[$n]['search_year'] = $sValue->search_year;
                    }
                    if($splitKey[0] == 'bookattempt')
                    {
                        $values[$n]['id'] = $row->id;
                        $values[$n]['datetime'] = $row->datetime;
                        $values[$n]['action'] = 'bookattempt';
                        $values[$n]['userID'] = $row->userID;
                        $values[$n]['WeekType'] = $sValue->WeekType;
                        $values[$n]['price'] = $sValue->AmountPaid;
                        $values[$n]['propertyID'] = $sValue->$splitKey[1];
                        $values[$n]['weekId'] = $sValue->WeekID;
                    }
                    if($splitKey[0] == 'resort')
                    {
                        $values[$n]['id'] = $row->id;
                        $values[$n]['datetime'] = $row->datetime;
                        $values[$n]['action'] = 'resortview';
                        $values[$n]['userID'] = $row->userID;
                        $values[$n]['ResortName'] = stripslashes($sValue->ResortName);
                        $values[$n]['resortID'] = $sValue->id;
                        $values[$n]['search_location'] = $sValue->search_location;
                        $values[$n]['search_month'] = $sValue->search_month;
                        $values[$n]['search_year'] = $sValue->search_year;
                    }
                    if(is_numeric($splitKey[0]))
                    {
                        
                        $values[$n]['id'] = $row->id;
                        $values[$n]['datetime'] = $row->datetime;
                        $values[$n]['action'] = 'search';
                        $values[$n]['userID'] = $row->userID;
                        $values[$n]['search_location'] = $sValue->search->locationSearched->location;
                        $values[$n]['search_month'] = $sValue->search->locationSearched->select_month;
                        $values[$n]['search_year'] = $sValue->search->locationSearched->select_year;
                    }
                    
                }
            }
            else
            {
                
                if($row->transactionType != 'booking' && empty($row->WeekType))
                {
                    $row->WeekType = $row->transactionType;
                }
                
                if($table == 'wp_gpxTransactions')
                {
                    $cxData = [];
                    $refunded = [];
                    $refundAmt = '';
                    if(!empty($row->cancelledData))
                    {
                        $cxData = json_decode($row->cancelledData);
                        foreach($cxData as $cx)
                        {
                            $refunded[] = $cx->amount;
                            if($cx->action == 'refund')
                            {
                                $refundTypes['credit_card'] = 'credit card';
                            }
                            else
                            {
                                $refundTypes['credit'] = 'credit';
                            }
                        }
                        $row->refundamount = array_sum($refunded);
                        $row->refundaction = implode(", ", $refundTypes);
                    }
                    //                     if((isset($data->couponCode) && $data->couponCode == 'NULL'))
                        //                     {
                    //is this an auto coupon?
                    $sql = "SELECT user_id FROM wp_gpxAutoCoupon WHERE transaction_id='".$row->id."'";
                    $sql = "SELECT b.Name from wp_gpxAutoCoupon a
                                INNER JOIN wp_specials b on b.id=a.coupon_id
                                WHERE user_id = (SELECT user_id FROM wp_gpxAutoCoupon WHERE transaction_id='".$row->id."')";
                    $cname = $wpdb->get_row($sql);
                    if(!empty($cname))
                    {
                        $data->AutoCoupon = $cname->Name;
                    }
                    //                     }
                }
                $dcnt = count($data);
                $hcnt = count($heads);
                if(isset($headStart))
                {
                    $hcnt = hcnt - count($headStart);
                }
                    foreach($headStart as $h)
                    {
                        if(is_array($row->$h) || is_object($row->$h))
                        {
                            $values[$n][$h] = implode(", ", (array) $row->$h);
                        }
                        elseif(isset($row->$h))
                        {
                            $values[$n][$h] = $row->$h;
                        }
                        elseif(isset($data->$h))
                        {
                            if(is_array($data->$h) || is_object($data->$h))
                            {
                                $values[$n][$h] = implode(", ", (array) $data->$h);
                            }
                            else
                            {
                                $values[$n][$h] = $data->$h;
                            }
                        }
                    }
//                     foreach($data as $dKey=>$dVal)
//                     {
//                         //        if($dKey == 'couponbogo' || $dKey == 'coupon')
//                         //            continue;
//                         if(is_array($dVal) || is_object($dVal))
//                             foreach($dVal as $aVal)
//                             {
//                                 $values[$n][$dKey] = $aVal;
//                             }
//                         else
//                             $values[$n][$dKey] = $dVal;
//                             if($dcnt > $hcnt)
//                                 $heads[$dKey] = $dKey;
//                     }
            }
            $n++;
        }
        $list = array();
        $list[] = implode(',', $heads);
        $i = 1;
        
        foreach($values as $value)
        {
            if($table != 'wpmeta')
            {
                $value = str_replace(",", "", $value);
                foreach($heads as $head)
                {
                    if(is_object($value[$head]))
                    {
                        $ordered[$i][] = '';
                    }
                    $ordered[$i][] = $value[$head];
                }
            }
            else
            {
                foreach($heads as $head)
                {
                    if(is_object($value[$head]))
                    {
                        $ordered[$i][] = '';
                    }
                    else
                    {
                        $value[$head] = str_replace(",", "", $value[$head]);
                        $ordered[$i][] = $value[$head];
                    }
                }
            }
            $list[$i] = implode(',', $ordered[$i]);
            $i++;
        }
        foreach($list as $line)
        {
            fputcsv($file,explode(",", $line));
            
        }
        fclose($file);
        return $fileLoc;
    }
    
    public function retrieve_password($user_login){
        global $wpdb, $wp_hasher;
        $user_login = sanitize_text_field($user_login);
        if ( empty( $user_login) ) {
            return false;
        } else if ( strpos( $user_login, '@' ) ) {
            $user_data = get_user_by( 'email', trim( $user_login ) );
            if ( empty( $user_data ) )
                return false;
        } else {
            $login = trim($user_login);
            $user_data = get_user_by('login', $login);
        }
        
        do_action('lostpassword_post');
        if ( !$user_data ) return false;
        // redefining user_login ensures we return the right case in the email
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;
        
        $emailTo = $user_email;
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $user_data->ID ) );
        if(isset($usermeta->Email))
            $emailTo = $usermeta->Email;
            
            do_action('retreive_password', $user_login);  // Misspelled and deprecated
            do_action('retrieve_password', $user_login);
            $allow = apply_filters('allow_password_reset', true, $user_data->ID);
            if ( ! $allow )
                return false;
                else if ( is_wp_error($allow) )
                    return false;
                    $key = wp_generate_password( 20, false );
                    do_action( 'retrieve_password_key', $user_login, $key );
                    
                    if ( empty( $wp_hasher ) ) {
                        require_once ABSPATH . 'wp-includes/class-phpass.php';
                        $wp_hasher = new PasswordHash( 8, true );
                    }
                    $hashed = $wp_hasher->HashPassword( $key );
                    $wpdb->update( $wpdb->users, array( 'user_activation_key' => time().":".$hashed ), array( 'user_login' => $user_login ) );
                    $message = __('It looks like you have forgotten your GPX password.  If this is correct, please follow this link to complete your request for a new password.') . "\r\n\r\n";
                    //                 $message .= network_home_url( '/' ) . "\r\n\r\n";
                    //                 $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
                    //                 $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
                    //                 $message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
                    $message .= '<' . network_site_url("?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";
                    
                    if ( is_multisite() )
                        $blogname = $GLOBALS['current_site']->site_name;
                        else
                            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
                            
                            $title = sprintf( __('[%s] Password Reset'), $blogname );
                            
                            $title = apply_filters('retrieve_password_title', $title);
                            $message = apply_filters('retrieve_password_message', $message, $key);
                            
                            if ( $message && !wp_mail($emailTo, $title, $message) )
                                $return = array('success'=>'The e-mail could not be sent.');
                                else
                                    $return = array('success'=>'Link for password reset has been emailed to you. Please check your email.');
                                    
                                    return $return;
    }
    
    public function update_subregions_add_all_resorts()
    {
        global $wpdb;
        $sql = "SELECT id, Town, Region, Country, gpxRegionID FROM wp_resorts WHERE Region='PUERTO PLATA' AND gpxRegionID='551'";
        $resorts = $wpdb->get_results($sql);
        echo '<pre>'.print_r($resorts, true).'</pre>';
        foreach($resorts as $resort)
        {
            $sql = "SELECT id, name FROM wp_gpxRegion WHERE name='".$resort->Region."'";
            $gpxRegion = $wpdb->get_row($sql);
            if(!empty($gpxRegion))
            {
                $subRegion = $gpxRegion->id;
                echo '<pre>'.print_r('already added', true).'</pre>';
            }
            else
            {
                echo '<pre>'.print_r('add it', true).'</pre>';
                $query = "SELECT id, lft, rght FROM wp_gpxRegion WHERE id='".$resort->gpxRegionID."'";
                $plr = $wpdb->get_row($query);
                //if region exists then add the child
                if(!empty($plr))
                {
                    echo '<pre>'.print_r("get to it", true).'</pre>';
                    //                     $right = $plr->rght;
                    
                    //                     $sql1 = "UPDATE wp_gpxRegion SET lft=lft+2 WHERE lft>'".$right."'";
                    //                     $wpdb->query($sql1);
                    //                     $sql2 = "UPDATE wp_gpxRegion SET rght=rght+2 WHERE rght>='".$right."'";
                    //                     $wpdb->query($sql2);
                    
                    //                     $update = array('name'=>ucwords(strtolower($resort->Region)),
                    //                         'parent'=>$plr->id,
                    //                         'lft'=>$right,
                    //                         'rght'=>$right+1
                    //                     );
                    //                     $wpdb->insert('wp_gpxRegion', $update);
                    //                     $subRegion = $wpdb->insert_id;
                }
                //otherwise we need to pull the parent region from the daeRegion table and add both the region and locality as sub region
                else
                {
                    echo '*********';
                    echo '*********';
                    echo '*********';
                    echo '*********';
                    echo '<pre>'.print_r("check id: ".$resort->id, true).'</pre>';
                    echo '*********';
                    echo '*********';
                    echo '*********';
                    echo '*********';
                    //                     $query2 = "SELECT a.id, a.lft, a.rght FROM wp_gpxRegion a
                    //                                 INNER JOIN wp_daeRegion b ON a.RegionID=b.id
                    //                                 WHERE b.RegionID='".$RegionID."'
                    //                                 AND b.CountryID='".$CountryID."'";
                    
                    //                     $parent = $wpdb->get_row($query2);
                    
                    //                     $right = $parent->rght;
                    
                    //                     $sql3 = "UPDATE wp_gpxRegion SET lft=lft+4 WHERE lft>'".$right."'";
                    //                     $wpdb->query($sql3);
                    //                     $sql4 = "UPDATE wp_gpxRegion SET rght=rght+4 WHERE rght>='".$right."'";
                    //                     $wpdb->query($sql4);
                    
                    //                     $updateRegion = array('name'=>$out['region'],
                    //                         'parent'=>$parent->id,
                    //                         'lft'=>$right,
                    //                         'rght'=>$right+3
                    //                     );
                    //                     $wpdb->insert('wp_gpxRegion', $updateRegion);
                    //                     $newid = $wpdb->insert_id;
                    
                    //                     $updateLocality = array('name'=>$out['locality'],
                    //                         'parent'=>$newid,
                    //                         'lft'=>$right+1,
                    //                         'rght'=>$right+2
                    //                     );
                    //                     $wpdb->insert('wp_gpxRegion', $updateLocality);
                    //                     $subRegion = $wpdb->insert_id;
                    
                }
            }
            if(isset($subRegion) && $subRegion != $resort->gpxRegionID)
            {
//                 echo '<pre>'.print_r("update gpxregionid", true).'</pre>';
                $wpdb->update('wp_resorts', array('gpxRegionID'=>$subRegion), array('id'=>$resort->id));
            }
            else
            {
//                 echo '<pre>'.print_r("nothing to update", true).'</pre>';
            }
        }
    }
    
    function xml2array ( $xmlObject, $out = array () )
    {
        foreach ( (array) $xmlObject as $index => $node )
            $out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;
            
            return $out;
    }
    
    public function return_search_no_action()
    {
        global $wpdb;
        
        
        $sql = "SELECT * FROM wp_gpxMemberSearch WHERE UNIX_TIMESTAMP(datetime) >= UNIX_TIMESTAMP(CAST(NOW() - INTERVAL 1 DAY AS DATE)) AND UNIX_TIMESTAMP(datetime) <= UNIX_TIMESTAMP(CAST(NOW() AS DATE))";
        $searches = $wpdb->get_results($sql);
        
        foreach($searches as $key=>$search)
        {
            $datas[$key] = get_object_vars(json_decode($search->data));
        }
        
        foreach($datas as $searchKey=>$data)
        {
            foreach($data as $dk=>$dv)
            {
                if($dk == 'user_type')
                {
                    if($dv == 'Owner')
                    {
                        $owners[$searchKey] = $searches[$searchKey];
                    }
                }
                elseif(is_object($dv))
                {
                    foreach($dv as $nk=>$nv)
                    {
                        if($nk == 'user_type')
                        {
                            if($nv == 'Owner')
                            {
                                $owners[$searchKey] = $searches[$searchKey];
                            }
                        }
                        elseif(is_object($nv))
                        {
                            foreach($nv as $ck=>$cv)
                            {
                                if($ck == 'user_type')
                                {
                                    if($cv == 'Owner')
                                    {
                                        $owners[$searchKey] = $searches[$searchKey];
                                    }
                                }
                                
                            }
                        }
                    }
                }
            }
            
        }
        
        foreach($owners as $owner)
        {
            $sql = "SELECT * FROM wp_cart WHERE sessionID='".$owner->sessionID."'";
            $row = $wpdb->get_row($sql);
            if(!empty($row))
                continue;
                
                $ownerdata = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $owner->userID ) );
                
                $mainOwnerData = get_userdata($owner->userID);
                $email = $mainOwnerData->user_email;
                
                $output[$owner->userID]['Name'] = $ownerdata->first_name." ".$ownerdata->last_name;
                $output[$owner->userID]['Email'] = $email;
                $output[$owner->userID]['EMSID'] = str_replace("U", "", $ownerdata->nickname);
                $output[$owner->userID]['Date'] = date('m/d/Y', strtotime($owner->datetime));
                $output[$owner->userID]['ShareID'] = $ownerdata->ResortShareID;
                $output[$owner->userID]['ExternalThirdPartyID'] = $ownerdata->ExternalPartyID;
                
                $ods = json_decode($owner->data);
                
                $locationArray = array('ResortName', 'search_location', 'search_month', 'search_year');
                
                $ownerLocations = array();
                
                foreach($ods as $od)
                {
                    $locations = array();
                    foreach($od as $dk=>$dv)
                    {
                        if(in_array($dk, $locationArray))
                            $locations[] = $dk.": ".$dv;
                    }
                    $ownerLocations[] = implode($locations, ";");
                }
                $output[$owner->userID]['Location'] = "[";
                $output[$owner->userID]['Location'] .= implode($ownerLocations, "][");
                $output[$owner->userID]['Location'] .= "]";
        }
        
        return $output;
        
        $formUrl =  'https://cs64.salesforce.com/servlet/servlet.WebToCase?encoding=UTF-8';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $formUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sfdata));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        $response = curl_exec($ch);
        curl_close($ch);
    }
}

?>
