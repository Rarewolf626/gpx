<?php
/**
 * Template Name: Resort Profile Page
 * Theme: GPX
 */
get_header();

//include '../models/gpxadmin.php';

global $wpdb;

$cid = get_current_user_id();

if(isset($_COOKIE['switchuser']))
    $cid = $_COOKIE['switchuser'];
    
    
    if(isset($_GET['resort']))
        $sql = "SELECT * FROM wp_resorts WHERE id='".$_GET['resort']."'";
        elseif(isset($_GET['resortName']))
        $sql = "SELECT * FROM wp_resorts WHERE ResortName LIKE '".$_GET['resortName']."%'";
        elseif(isset($_GET['ResortID']))
        $sql = "SELECT * FROM wp_resorts WHERE ResortID='".$_GET['ResortID']."'";
        
        if(isset($sql))
            $resort = $wpdb->get_row($sql);
            
            if(isset($resort) && !empty($resort))
            {
                $sql = "SELECT DISTINCT number_of_bedrooms FROM wp_room a 
                        INNER JOIN wp_unit_type b ON b.id=a.unit_type WHERE a.resort='".$resort->ResortID."'";
                $resortBeds = $wpdb->get_results($sql);
                
                //set the default images for the gallery
                for($i = 1; $i < 4; $i++)
                {
                    $ImagePath = 'ImagePath'.$i;
                    if(!empty($resort->$ImagePath))
                    {
                        $images[$i]['src'] = str_replace("http://", "https://", $resort->$ImagePath);
                        $images[$i]['imageAlt'] = strtolower($resort->ResortName);
                        $images[$i]['imageTitle'] = $resort->ResortName;
                    }
                }
                
                if(isset($cid) && !empty($cid))
                {
                    save_search_resort($resort, array('cid'=>$cid));
                }
                
                $sql = "SELECT meta_key, meta_value FROM  wp_resorts_meta WHERE ResortID='".$resort->ResortID."'";
                $resortMetas = $wpdb->get_results($sql);
                
                $ammenitiesList = [
                    'UnitFacilities'=>'Unit Facilities',
                    'ResortFacilities'=>'Resort Facilities',
                    'AreaFacilities'=>'Area Facilities',
                    'resortConditions'=>'Resort Conditions',
                    'configuration'=>'Conditions',
                ];
                
                $adaList = [
                    'CommonArea'=>'Common Area Accessibility Features',
                    'GuestRoom'=>'Guest Room Accessibility Features',
                    'GuestBathroom'=>'Guest Bathroom Accessibility Features',
                    'UponRequest'=>'The following can be added to any Guest Room upon request',
                ];
                
                $configurationsList = [
                    'UnitConfig'=>'Unit Config',
                ];
                
                $attributesList = array_merge($ammenitiesList, $adaList, $configurationsList);
                
                foreach($resortMetas as $meta)
                {
                    $metaKey = $meta->meta_key;
                    if($metaKey == 'images')
                    {
                        $images = [];
                        $rawResortImages = json_decode($meta->meta_value, true);
                        foreach($rawResortImages as $imgKey=>$oneImage)
                        {
                            $images[$imgKey]['src'] = $oneImage['src'];
                            if($oneImage['type'] == 'uploaded')
                            {
                                $id = $oneImage['id'];
                                $images[$imgKey]['imageAlt'] = get_post_meta( $id ,'_wp_attachment_image_alt', true);
                                $images[$imgKey]['imageVideo'] = get_post_meta( $id ,'gpx_image_video', true);
                                $images[$imgKey]['imageTitle'] = get_the_title($id);
                                $images[$imgKey]['id'] = $id;
                            }
                        }
                    }
                    
                    $rmk = $meta->meta_key;
                    if($rmArr = json_decode($meta->meta_value, true))
                    {
                        ksort($rmArr);
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
                                //check to see if the from date has started
                                if($rmdates[0] < strtotime("now"))
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
                                if(isset($rmdates[1]) && ($rmdates[1] < strtotime("now")))
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
                                    if(isset($rmval['path']) && $rmval['path']['profile'] == 0)
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
                                            $thisVal = stripslashes($rmval['desc']);
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
                        $resort->$rmk = $thisVal;
                    }
                    else
                    {
                        if($meta->meta_value != '[]')
                        {
                            $resort->$rmk = $meta->meta_value;
                        }
                    }
                    
                }
            }
            
            $args = array(
                'post_type' => 'wpsl_stores',
                'nopaging' => true,
                'meta_query'=> array(
                    array(
                        'key'=>'wpsl_resortid',
                        'value'=>$resort->ResortID,
                    )
                )
            );
            
            if(!empty($resort->taID) && $resort->taID != 1)
            {
                require_once ABSPATH.'/wp-content/plugins/gpxadmin/api/functions/class.tripadvisor.php';
                $ta = new TARetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);
                
                $tripadvisor = json_decode($ta->location($resort->taID));
                
                foreach($tripadvisor->review_rating_count as $tarKey=>$tarValue)
                {
                    $totalstars += $tarKey * $tarValue;
                }
                
                $reviews = array_sum( (array) $tripadvisor->review_rating_count  );
                
                $stars = round(number_format($totalstars / $reviews, 1) *2) / 2;
                $starsclass = str_replace(".", "_", $stars);
                $taURL = $tripadvisor->web_url;
            }
            
            $coordniates = '';
            if(!empty($resort->LatitudeLongitude))
                $coordniates = $resort->LatitudeLongitude;
                
                $maplink = '';
                if(!empty($coordinates))
                    $maplink = " http://www.google.com/maps/place/".$coordniates;
                    else
                        $maplink = "http://maps.google.com/?q=".$resort->Address1." ".$resort->Town.", ".$resort->Region." ".$resort->PostCode;
                        while ( have_posts() ) : the_post();
                        
                        ?>
  <div id="cid" data-cid="<?=$cid?>"></div>
<section class="w-banner w-results w-results-home w-profile new-style-result-banner">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result rs-col-3">
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg" alt="" />
        </li>
    </ul>
    <div class="w-options">
         <?php 
            if(isset($resort) && !empty($resort))
            {
        ?>   
        <hgroup>

            <h1><?=$resort->ResortName?></h1>
            <h3><?=$resort->Town.", ".$resort->Region." ".$resort->Country?></h3>
        </hgroup>

        <a href="#" class="dgt-btn search show-availabilty cal-av-toggle" data-resortid="<?=$resort->id?>">
            <span>Check Pricing & Availability</span>
            <i class="fa fa-th-large"></i>
        </a>
          <?php 
            }
        ?>      
    </div>
</section>
<?php //include(locate_template( 'template-parts/universal-search-widget.php' )); ?>
<?php 
    if(isset($resort) && !empty($resort))
    {
?>

<section class="resort-detail dgt-container">
    <?php include(locate_template( 'template-parts/resort-profile-gallery.php' )); ?>

    <?php include(locate_template( 'template-parts/resort-profile-info-detail.php' )); ?>
</section>
<section class="review bg-gray-light">
    <div class="dgt-container profile">
        <div class="overview w-list-availables">
            <div class="title">
                <div class="close">
                    <i class="icon-close"></i>
                </div>
                <h4>Resort Overview</h4>
            </div>
            <div class="cnt-list cnt">
                <div class="p">
                	<p><?=$resort->Description;?>
                </div>
            </div>
<!--             <a href="" class="dgt-btn search search-availability" data-resort="<?=$resort->ResortID?>"> -->
<!--                 <span>Search Availability</span> -->
<!--                 <i class="icon-calendar"></i> -->
<!--             </a> -->
        </div>

        <div class="w-list-availables" id="expand_4">
            <div id="availiblity-calendar-btn">
            	<a href="#" class="dgt-btn search search-availability cal-av-toggle" id="search-availability" data-resort="<?=$resort->ResortID?>">
                    <span>Availability Calendar</span>
                    <i class="icon-calendar"></i>
                </a>
                <?php 
                $dsmonth = 'f';
                if(isset($_GET['month']) && !empty($_GET['month']))
                {
                    $dsmonth = $_GET['month'];
                }
                $dsyear = date('Y');
                if(isset($_GET['yr']) && !empty($_GET['yr']))
                {
                    $dsyear = $_GET['yr'];
                }
                
                ?>
                <a href="#" style="display: none;" class="dgt-btn search show-availabilty cal-av-toggle show-availability-btn" id="show-availability" data-month="<?=$dsmonth?>" data-year="<?=$dsyear?>" data-resortid="<?=$resort->id?>">
                    <span>Check Pricing & Availability</span>
                    <i class="fa fa-th-large"></i>
                </a>
        	</div>
            <div class="title">
                <div class="close">
                    <i class="icon-close"></i>
                </div>
                <h4>Availability</h4>
            </div>
            <div class="cnt-list">
              <div id="availability-cards"></div>
              <section class="resort-availablility dgt-container">
                <div id="resort-calendar-filter">
                		<h3>Filter Results</h3>
                			<p>
                				<select id="calendar-type" class="dgt-select" name="calendar-type" placeholder="Week Type">
                						<option value="All" selected></option>
                						<option value="All">All</option>
                						<option value="BonusWeek">Rental</option>
                						<option value="ExchangeWeek">Exchange</option>
                				</select>
            				</p>
                			<p>
                				<select id="calendar-bedrooms" class="dgt-select" name="calendar-bedrooms" placeholder="Bedrooms">
                				    <option value="All"  selected ></option>
                				    <option value="All">All</option>
                				    <?php 
                				    foreach($resortBeds as $bed)
                				    {
            		                    if($bed->bedrooms == 'St')
                                            $bedtext = 'Studio';
                                        else 
                                            $bedtext = str_replace("b", ' Bedroom', $bed->bedrooms);
                				    ?>
                				    <option value="<?=$bed->bedrooms?>"><?=$bedtext?></option>
                				    <?php     
                				    }
                				    ?>
                				</select>
            				</p>
            				<p>
                				<select id="calendar-month" class="dgt-select" name="calendar-month" placeholder="Month">
                					<option value="0" disabled selected ></option>
                					<?php 
                					   $months = array(
                					       '01'=>'January',
                					       '02'=>'February',
                					       '03'=>'March',
                					       '04'=>'April',
                					       '05'=>'May',
                					       '06'=>'June',
                					       '07'=>'July',
                					       '08'=>'August',
                					       '09'=>'September',
                					       '10'=>'October',
                					       '11'=>'November',
                					       '12'=>'December',
                					   );
                					   foreach($months as $mkey=>$month)
                					   {
                					?>
                						<option value="<?=$mkey?>"><?=$month?></option>
                					<?php 
                					   }
                					?>
                				</select>
            				</p>
            				<p>
                				<select id="calendar-year" class="dgt-select" name="calendar-year" placeholder="Year">
                					<option value="0" disabled selected ></option>
                					<?php 
                					   $currentYear = date('Y');
                					   for($z=$currentYear;$z<=$currentYear+2;$z++)
                					   {
                					?>
                						<option value="<?=$z?>"><?=$z?></option>
                					<?php 
                					   }
                					?>
                				</select>
            				</p>
            				<p>
            				<ul class="status status-block">
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
            				</p>
                </div>
            	<div id="resort-calendar"></div>
            </section>
            </div>
        </div>
        <div class="w-list-availables" id="expand_1">
            <?php include(locate_template( 'template-parts/resort-profile-amenities.php' )); ?>
        </div>
        <div class="w-list-availables" id="expand_2">
            <?php include(locate_template( 'template-parts/resort-profile-unit.php' )); ?>
        </div>
        <div class="w-list-availables" id="expand_3">
            <?php include(locate_template( 'template-parts/resort-profile-important-information.php' )); ?>
        </div>
        <div class="w-list-availables" id="expand_3">
            <?php include(locate_template( 'template-parts/resort-profile-ada.php' )); ?>
        </div>
    </div>
</section>
<?php 
    }
    else
    {
?>
<section class="resort-detail dgt-container">
	<h3 style="text-align: center;">Your search resulted in empty results.  Please try again.</h3>
</section>
<?php 
    }
?>
<?php echo do_shortcode('[websitetour id="18526"]'); ?>
<?php endwhile;
get_footer(); ?>