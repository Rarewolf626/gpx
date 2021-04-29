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
//var_dump($props);exit;
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

// re sort the props
	$cntResults =0;
	reset($props);
	foreach($props as $prop)
	{
		$this['resid'] = $prop->ResortID;
		$this['propsort'] = $prop->week_date_size;
		        
		$allProps[$this['resid']][$this['propsort']] = $prop;
		
		if(empty($allResorts[$this['resid']]))
			$allResorts[$this['resid']] = $prop;
			
		$cntResults++;
		
		unset($this);
	}


//get the held weeks for this user
$sql = "SELECT * FROM wp_gpxPreHold WHERE user='".$cid."' and released=0";
$holds = $wpdb->get_results($sql);
foreach($holds as $theld)
{
    $held[$theld->weekId] = $theld->weekId;
}
?>       <div class="dgt-container g-w-modal">
            <div class="modal modal-filter dgt-modal" id="modal-filter">
            	<div class="close-modal"><i class="icon-close"></i></div>
            	<div class="w-modal">
            		<form action="">
            			<div class="block">
            				<h2>Sort Results</h2>
            				<?php 
            				?>
            				<label for="select_cities" class="ada-text">Filter City</label>
            				<select id="select_cities" class="dgt-select filter_city" multiple="multiple" data-filter="subregions" name="mySelect" placeholder="All Cities">
            					<?php
            					foreach($filterNames as $filterNameKey => $filterNameValue)
            					{
            					?>
            					<option value="<?=$filterNameKey?>"><?=$filterNameValue?></option>
            					<?php 
            					}
            					?>
            				</select>
            				<label for="select_soonest" class="ada-text">Filter Soonest</label>
            				<select id="select_soonest" class="dgt-select" name="mySelect" placeholder="Date/Soonest to Latest">
            					<option value="1">Date/Soonest to Latest</option>
            					<option value="2">Date/Latest to Soonest</option>
            					<option value="3">Price/Lowest to Hightest</option>
            					<option value="4">Price/Highest to Lowest</option>
            				</select>
            				<h3>- Date Range</h3>
            				<a href="#" class="dgt-btn" id="checkin-btn">Check-In <span class="icon-date"></span></a>
            				<input id="rangepicker" style="display: none">
            			</div>
            			<div class="block">
            				<h2>Filter Results</h2>
            				<h3>- Unit Size (note: currently working on this)</h3>
            				<ul class="list-check">
            				<?php 
            				foreach($allBedrooms as $bkey=>$bval)
            				{
            				?>
            				   <li>
            						<input type="checkbox" class="filter_size" id="chk-<?=$bkey?>" name="addsize[]" value="<?=$bkey?>" data-type="size" data-filter="bedtype" placeholder="Studio">
            						<label for="chk-<?=$bkey?>"><?=$bval?></label>
            					</li>
            				<?php 
            				}
            				?>
            				</ul>
            				<h3>- Type of Week</h3>
            				<ul class="list-check">
            					<li>
            						<input type="checkbox" class="filter_resorttype" id="chk-rental" class="filter_data filter_group" data-type="type" data-filter="resorttype" name="addname[]" value="Rental Week" placeholder="Rental" checked>
            						<label for="chk-rental">Rental</label>
            					</li>
            					<li>
            						<input type="checkbox" class="filter_resorttype" id="chk-exchange" class="filter_data filter_group" data-type="type" data-filter="resorttype" name="addname[]" value="Exchange Week" placeholder="Exchange" checked>
            						<label for="chk-exchange">Exchange</label>
            					</li>
            				</ul>
            				<h3>- Resort Type</h3>
            				<h5 class="aiActive">All Inclusive Resorts - <span id="aiNot"></span>Included in Results</h5>
            				<ul class="list-check">
            					<li>
            						<input type="checkbox" class="filter_ai" id="chk-all-inclusive" name="addai[]" data-type="type" data-filter="resorttype" value="00" placeholder="All-Inclusive Resorts Not Included">
            						<label for="chk-all-inclusive">All-Inclusive Resorts Not Included</label>
            					</li>
            					<li>
            						<input type="checkbox" id="filter_ai_dummy" name="filter_ai_dummy" checked>
            						<label for="filter_ai_dummy">All Inclusive Resorts Included</label>
            					</li>
            				</ul>
            			</div>
            		</form>
            	</div>
            </div>
        </div>
<?php 
if(isset($loginalert))
{
    //$resorts = $featuredresorts;
?>
    <div class="modal dgt-modal modal-alert active-modal" id="modal-alert">
    	<div class="close-modal"><i class="icon-close"></i></div>
    	<div class="w-modal">
    		<div class="icon-alert"></div>
    		<p>These specials are only available to logged in users.   Please <a class="dgt-btn call-modal-login signin" href="#">Sign In</a> to see the promo price.</p>
    	</div>
    </div>
<?php     
}
?> 
    <section class="w-banner w-results w-resulst-reset w-results-home new-style-result-banner">
        <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result rs-col-3">
            <li class="slider-item rsContent"><img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg" alt="" /></li>
        </ul>
        <div class="dgt-container w-box result-page-form">
        <h2 class="hero-main-text">
        	Search Results
        </h2>
            <?php 
            /*
            ?>
        	<form id="home-search" role="search" method="post" action="<?php echo home_url( '/result/' ); ?>">
        		<div class="w-options">
        			<div class="cnt left">
        				<div class="component">
        				<?php 
        				$selLocation = '';
        				if(isset($_POST['location']))
        				    $selLocation = $_POST['location'];
        				?>
        					<label for="location_autocomplete" class="ada-text">Select Location</label>
        					<input name="location" id="location_autocomplete" placeholder="Type a Location" value="<?=$selLocation?>" required>
        				</div>
        			</div>
        			<div class="cnt right thirty-five">
        				<label for="select_month" class="ada-text">Select Month</label>
        				<select id="select_month" class="dgt-select" name="select_month" placeholder="This Month">
        					<option value="0" disabled selected value="foo" ></option>
        					<?php 
        					$m  = 0;
        					$selMonth = '';
        					if(isset($_POST['select_month']))
        					    $selMonth = $_POST['select_month'];
        					?>
        					<option value="any" <?php if($selMonth == 'any') echo 'selected="selected"';?>>All</option>
        					<?php 
        					for ($i = 0; $i < 12; $i++) {
        					    $selected = '';
        					    $startofmonth = date('01-m-Y');
        					    $month  = date('F', strtotime($startofmonth." +{$m} months"));
        					    if($month == $selMonth)
        					        $selected = ' selected="selected"';
                            ?>
                            <option value="<?=$month?>" <?=$selected?>><?=$month?></option>
                            <?php 
        					    $m++;
        					
        					}
        					?>
        				</select>
        				<label for="select_year" class="ada-text">Select Year</label>
        				<select id="select_year" class="dgt-select" name="select_year" placeholder="This Year">
        					<option value="0" disabled selected ></option>
        					<?php 
        					$selYear = '';
        					$selected = '';
        					if(isset($_POST['select_year']))
        					    $selYear = $_POST['select_year'];
        					for($date=date('Y');$date<date('Y', strtotime('+ 2 year', time())); $date++)
        					{
        					    $selected = '';
        					    if($date == $selYear)
        					        $selected = ' selected';
        					?>
        					<option value="<?=$date?>" <?=$selected?>><?=$date?></option>
        					<?php    
        					}
        					?>
        				</select>
        			</div>
        		</div>
        		<input type="submit" class="dgt-btn" value="Search">
        	</form>
        	<?php 
        	*/
        	?>
        </div>
    </section>
    <?php include(locate_template( 'template-parts/universal-search-widget.php' )); ?>
    <section class="w-filter dgt-container">
        <div class="left">
        <?php
			// count real results above
        ?>
            <h3><?=$cntResults?> Search Results</h3>
            <?php 
            if(isset($returnLink) && !empty($returnLink))
                echo $returnLink;
            ?>
        </div>
        <div class="right">
            <ul class="status">
                <li>
                    <div class="status-all">
                        <p>All-Inclusive</p>
                    </div>
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
			<div id="sticky"><a href="" class="dgt-btn call-modal-filter">Filter Results</a></div>
        </div>
    </section>
    <section class="w-featured bg-gray-light w-result-home">
        <ul class="w-list-view dgt-container" id="results-content">
        <?php 
        if(!isset($props) && !isset($newStyle))  // $resorts to $props
        {
            if(isset($insiderweek))
            {
                echo '<div style="text-align:center; margin: 30px 20px 40px 20px; "><h3 style="color:#cc0000;">You must be logged in to view this page</h3><p style="font-size:15px;">Please login below.</p></div>';
                $props = $featuredresorts;
                $disableMonth = true;
            }
            else
            {
                echo '<div style="text-align:center; margin: 30px 20px 40px 20px; "><h3 style="color:#009bd9; font-size:30px; font-weight:normal;">Sorry, Your search didn\'t return any results</h3><p style="font-size:20px;">Please consider expanding your search criteria above, searching for a <a href="/resorts/" style="color:#152136">specific resort</a> or view our featured resorts below.</p></div>';
                $props = $featuredresorts;
                $disableMonth = true;
            }
        }
        if(!empty($props) || isset($newStyle))
        {

//var_dump($allProps);exit;       
        
            $i = 0;
            
            foreach($allProps as $this['resid']=>$nouse)	// start resort loop
            {
              
                if(empty($allResorts[$this['resid']]->ResortName))
                {
                    continue;
                }
        ?>
            <li class="w-item-view filtered" id="rl<?=$i?>" data-subregions='["<?=$allResorts[$this['resid']]->gpxRegionID?>"]'>
                <a href="#" data-resortid="<?=$allResorts[$this['resid']]->RID?>" class="hidden-more-button dgt-btn result-resort-availability">View Availability <i class="fa fa-chevron-down" aria-hidden="true"></i></a>
                <div class="view">
                	<div class="view-cnt">
                	<?php 
                	$metaResortID = $this['resid'];

                	$imgThumb = $allResorts[$this['resid']]->ImagePath1;
                	$imageTitle = strtolower($allResorts[$this['resid']]->ResortName);
                	$imageAlt = $allResorts[$this['resid']]->ResortName;
                	if(empty($imgThumb))
                	{
                    	//check for updated images
                    	$sql = "SELECT meta_value FROM wp_resorts_meta WHERE meta_key='images' AND ResortID='".$metaResortID."'";
                    	$rawResortImages = $wpdb->get_row($sql);
                    	if(get_current_user_id() == 5)
                    	{
                    	    echo '<pre>'.print_r($wpdb->last_query, true).'</pre>';
                    	    echo '<pre>'.print_r($wpdb->last_error, true).'</pre>';
                    	    echo '<pre>'.print_r($wpdb->last_result, true).'</pre>';
                    	}
                    	if(!empty($rawResortImages->meta_value))
                    	{
                    	   $resortImages = json_decode($rawResortImages->meta_value, true);
                    	   $oneImage = $resortImages[0];
                    	   if(!empty($oneImage))
                    	   {
                        	   $imgThumb = $oneImage['src'];
                        	   if($oneImage['type'] == 'uploaded')
                        	   {
                        	       $id = $oneImage['id'];
                        	       $imageAlt = get_post_meta( $id ,'_wp_attachment_image_alt', true);
                        	       $imageTitle = get_the_title($id);
                        	   }
                    	   }
                    	}
                	}
                	$resortLinkID = $allResorts[$this['resid']]->RID;
                	if(empty($resortLinkID))
                	{
                	    $resortLinkID = $allResorts[$this['resid']]->id;
                	}
                	?>
                		<img src="<?=$imgThumb?>" alt="<?=$imageAlt;?>" title="<?=$imageTitle?>">
                	</div>
                	<div class="view-cnt">
                		<div class="descrip">
                			<hgroup>
                				<h2>
                					<?=$allResorts[$this['resid']]->ResortName;?>
                				</h2>
                				<span><?=$allResorts[$this['resid']]->Town;?>, <?=$allResorts[$this['resid']]->Region;?> <?=$allResorts[$this['resid']]->Country;?></span>
                			</hgroup>
                			<p>
                			<a href="/resort-profile?resort=<?=$resortLinkID?>" data-rid="<?=$resortLinkID?>" data-cid="<?=$cid?>" class="dgt-btn resort-btn">View Resort</a>
                			</p>
                			<?php 
                			if($newStyle)
                			{
                			?>
                			<p style="margin-top: 10px">
                				<?php 
                				if(!empty($allCounts[$this['resid']]))
                				{
                				?>
                            	<a href="#" data-resortid="<?=$allResorts[$this['resid']]->RID?>" class="dgt-btn result-resort-availability">View Availability <i class="fa fa-chevron-down" aria-hidden="true"></i></a>
                				<?php 
                				}
                				else 
                				{
                				?>
								<a href="#modal-custom-request" data-cid="<?=$cid?>" data-pid="" class="custom-request gold-link">No Availability – click to submit a custom request</a>                				<?php 
                				}
                				?>
                			</p>
                			<?php 
                			}
                			?>
                			<ul class="status">
                			
                            	<?php 
            // !!! $resort or $prop ??
                            	   $status = array('status-exchange'=>'Exchange Week','status-rental'=>'Rental Week');
                            	   foreach($status as $key=>$value)
                            	   {
                            	       if(isset($resort->WeekType))
                            	       {
                                	       if(in_array($value, $resort->WeekType))
                                	       {
                            	        ?>
                                 <li>
                                    <div class="<?=$key;?>"></div>
                                </li>               	        
                                	        <?php    
                                	       }
                            	       }
                            	   }
                            	   if(isset($allResorts[$this['resid']]->AllInclusive) && $allResorts[$this['resid']]->AllInclusive == '6')
                            	   {
                            	       ?>
               					   <li><div class="status-all"></div></li>
               					   <?php    
               					   }
                            	?>
                			</ul>
                		</div>
                		<div class="w-status">
                			<div class="close">
                				<i class="icon-close"></i>
                			</div>
                			<div class="result">
                			<?php 
                			     if(!isset($disableMonth))
                			     {
                			?>
                    				<span class="count-result" ><?=count($resort['props'])?> Results</span>
                    				<span class="count-result" ><?=count($allProps[$this['resid']])?> Results<!-- here --></span>
                    				<?php 
                    				if(isset($_POST['select_month']) && !isset($disableMonth))
                    				{
                    				    echo '<span class="date-result" >'.$_POST['select_month'].' '.$select_year.'</span>';   
                    				}
                    				?>
                			<?php 
                			     }
                			?>	
                			</div>
                		</div>
                	</div>
                </div>
                <?php 
                $collapseAvailablity = '';
                if($newStyle)
                {
                    $collapseAvailablity = 'collapse';   
                    if(empty($allProps[$this['resid']]))
                    {
                        $collapseAvailablity .= ' no-availability';
                    }
                }
                ?>
                
                <ul id="gpx-listing-result-<?=$allResorts[$this['resid']]->RID?>" class="w-list-result <?=$collapseAvailablity?>" >
                
                <?php 
                  // start props loop                    
				  reset($allProps[$this['resid']]);
	              krsort($allProps[$this['resid']]);
	        	  foreach($allProps[$this['resid']] as $prop)
	        	  {
                    //foreach($resort['props'] as $kp=>$prop)
                    //{
//                         echo '<pre>'.print_r($prop, true).'</pre>';
//                         if($prop->WeekPrice == '0' && $prop->Price != '0')
//                         {
//                             $prop->WeekPrice = $prop->Price;
//                         }
//                        if($prop->Price == 0)
//                       {
//                            continue;
//                        }
                        $wte = explode("--", $kp);
                        
                        if(isset($wte[1]))
                        {
                            $prop->WeekType = $wte[1];
                        }
   // DO THIS BETTER !!                     
                        if(isset($propType[$kp]))
                        {
                            $prop->WeekType = $propType[$kp];
                        }
                        $exchangeprice = get_option('gpx_exchange_fee');
                        if(number_format($propPrice[$kp], 0) == number_format($exchangeprice, 0))
                        {
                            $prop->WeekType = 'ExchangeWeek';
                        }
                        if(isset($prefPropSetDets[$kp]))
                        {
                            $prop->specialPrice = $prefPropSetDets[$kp]['specialPrice'];
                        }
                        if($propPrice[$kp] > 0)
                        {
                            $prop->Price = number_format($propPrice[$kp], 0);
                        }
                        else 
                        {
                            $prop->Price = number_format($prop->Price, 0);
                        }
                        $prop->WeekPrice = $prop->Price;
                        
                        if($prop->WeekType == 'ExchangeWeek')
                        {
                            $prop->WeekType = 'Exchange Week';
                        }
                        else
                        {
                            $prop->WeekType = 'Rental Week';
                        }
                        
//                         $priceint = preg_replace("/[^0-9\.]/", "",$prop->WeekPrice);
//                         if($priceint != $prop->Price)
//                             $prop->Price = $priceint;
//                         if($prop->Price == '0')
//                             continue;
                        $datadate = date('Ymd', strtotime($prop->checkIn));
                        $dddatadate = date('Y-m-d', strtotime($prop->checkIn));
                        $chechbr = strtolower(substr($prop->bedrooms, 0, 1));
                        if(is_numeric($chechbr))
                            $bedtype = $chechbr;
                        elseif($chechbr == 's')
                            $bedtype = 'Studio';
                        else 
                            $bedtype = $prop->bedrooms;
                        $indPrice = $prop->Price;
                        if(!empty($prop->specialPrice))
                            $indPrice = $prop->specialPrice;
                        
                ?>
                	<li id="prop<?=str_replace(" ", "", $prop->WeekType)?><?=$prop->weekId?>" class="item-result<?php 
                	$cmpSP = str_replace(",", "", $prop->specialPrice);
                	$cmpP = str_replace(",", "", $prop->Price);
               if(!empty($prop->specialPrice) && ($cmpSP - $cmpP != 0))
               {
                   //check to see if Prevent Highliting is set
                   if(!empty($setPropDetails[$prop->propkeyset]['preventhighlight']))
//                    if(isset($prop->special->highlight) && $prop->special->highlight == 'Prevent Highlighting')
                   {
                       //prevent the active status when Prevent Highlighting is set in promo meta
                   }
                   else
                   {
                       echo ' active';
                   }
               }
                    ?>"
               		data-resorttype='["<?=$prop->WeekType?>"<?php if(!empty($prop->AllInclusive)) echo ' ,"'.$prop->AllInclusive.'"';?>]'
               		data-bedtype='["<?=$bedtype?>"]'
               		data-date='<?=$dddatadate?>'
               		data-timestamp='<?=strtotime($dddatadate)?>'
               		data-price='<?=$indPrice?>'>
                            	<div class="w-cnt-result">
                            		<div class="result-head">
                            		<?php 
               $pricesplit = explode(" ", $prop->WeekPrice);
               $psit = count($pricesplit)-1;
               $ps = $pricesplit[$psit];
               if (strpos($ps, '$') === false) {
                  $ps = '$'.$ps;
               }
               
               if(empty($prop->specialPrice) || ($cmpSP - $cmpP == 0))
                   echo '<p><strong>$'.$prop->WeekPrice.'</strong></p>';
               else
               {
                          
                   //check to see if Force Slash is set
                   if(!empty($setPropDetails[$prop->propkeyset]['slash']))
//                    if(isset($prop->special->slash) && $prop->special->slash == 'Force Slash')
                   {
                       //Force Slash is set -- let's display the slash through
                       echo '<p class="mach white-text"><strong>'.$ps.'</strong></p>';
                   }
                   elseif(!empty($setPropDetails[$prop->propkeyset]['desc']) && !empty($setPropDetails[$prop->propkeyset]['icon']))
//                    elseif(isset($prop->specialicon) && isset($prop->specialdesc) && !empty($prop->specialicon))
                   {
                       echo '<p class="mach"><strong>$'.$prop->WeekPrice.'</strong></p>';
                   }
                   echo '';
                   if($prop->specialPrice - $prop->Price != 0)
                   {
                       echo '<p class="now">';
                       if(!empty($setPropDetails[$prop->propkeyset]['desc']) && !empty($setPropDetails[$prop->propkeyset]['icon']))
//                        if(isset($prop->specialicon) && isset($prop->specialdesc) && !empty($prop->specialicon))
                       {
                           echo 'Now ';
                       }
                       echo '<strong>$'.number_format($prop->specialPrice, 0).'</strong></p>';
//                        echo '<strong>$'.str_replace(number_format($prop->Price, 0), number_format(str_replace(",", "", $prop->specialPrice),0), $prop->WeekPrice).'</strong></p>';
                   }
               }
               if(!empty($setPropDetails[$prop->propkeyset]['desc']) && !empty($setPropDetails[$prop->propkeyset]['icon']))
//                if(isset($prop->specialicon) && isset($prop->specialdesc))
               {
               ?>
              	   <a href="#" class="special-link" aria-label="promo info"><i class="fa <?=$setPropDetails[$prop->propkeyset]['icon']?>"></i></a>	
                  <div class="modal dgt-modal modal-special">
                   	<div class="close-modal"><i class="icon-close"></i></div>
                   	<div class="w-modal">
                   		<p><?=nl2p($setPropDetails[$prop->propkeyset]['desc'])?></p>
                   	</div>
                   </div> 
               <?php     
               }               
               $lpid = '';
               if(isset($lpCookie))
                   $lpid = $prop->weekId.$lpSPID;
               //Changed from limiting # of holds to just hiding the Hold button for SoCal weeks between Memorial day and Labor day.
               //set an empty hold class
               $holdClass = '';
               //is this in the summer?
               $checkIN = strtotime($prop->checkIn);
               $thisYear = date('Y', $checkIN);
               $heldClass = '';
               if(in_array($prop->weekId, $held))
               {
                   $heldClass = 'week-held';
               }
               $memorialDay = strtotime("-6 days last monday of may $thisYear");
               $laborDay = strtotime("-6 days first monday of september $thisYear");
               if(($memorialDay <= strtotime($prop->checkIn) AND strtotime($prop->checkIn) <= $laborDay)) //the date in the range is between memorial day and labor day
               {
                   //check to see if this gpxRegionID is a restricted one.
                   if(isset($restrictIDs) && in_array($prop->gpxRegionID, $restrictIDs))
                   {
                       //we don't want to show the hold button
                       $holdClass = 'hold-hide';
                   }

               }
               //Chris- We want to disable consumer use until the hold plus one project is done.  Need to keep this simple.
               //Thanks
               //Jeff
//                $holdClass = 'hold-hide';
               ?>
               			              <ul class="status">
                            				<li>
                            					<div class="status-<?=str_replace(" ", "", $prop->WeekType)?>"></div>
                            				</li>
                            			</ul>
                            		</div>
                            		<div class="cnt">
                                        <p class="d-flex">
                                            <strong><?=$prop->WeekType?></strong> 
                                            <?php 
                                            if($prop->prop_count < 6)
                                            {
                                            ?>
                                            <span class="count-<?=str_replace(" ", "", $prop->WeekType)?>"> Only <?php echo $prop->prop_count; ?> remaining </span> 
                                        	<?php 
                                            }
                                        	?>
                                        </p>
                            			<p>Check-In <?=date('m/d/Y', strtotime($prop->checkIn))?></p>
                            			<p><?=$prop->noNights?> Nights</p>
                            			<p>Size <?=$prop->Size?></p>
                            		</div>
                            		<div class="list-button">
                            			<a href="" class="dgt-btn hold-btn <?=$holdClass?> <?=$bookingDisabeledClass?>" data-lpid="<?=$lpid?>" data-wid="<?=$prop->weekId?>" data-pid="<?=$prop->PID?>" data-type="<?=str_replace(" ", "", $prop->WeekType)?>" data-cid="<?php if(isset($cid)) echo $cid;?>" title="Hold Week <?=$prop->weekId?>">Hold<i class="fa fa-refresh fa-spin fa-fw" style="display: none;"></i></a>
                            			<a href="/booking-path/?book=<?=$prop->PID?>&type=<?=str_replace(" ", "", $prop->WeekType)?>" data-type="<?=str_replace(" ", "", $prop->WeekType)?>" data-lpid="<?=$lpid?>" class="dgt-btn active book-btn <?=$holdClass?> <?=$heldClass?> <?=$bookingDisabeledClass?>" data-propertiesID="<?=$prop->PID?>" data-wid="<?=$prop->weekId?>" data-pid="<?=$prop->PID?>" data-cid="<?php if(isset($cid)) echo $cid;?>" title="Book Week <?=$prop->weekId?>">Book</a>
                            		</div>
                            	</div>
                            </li>  
                  <?php 
                    }		// END OF $props sub-loop
                  ?>              
                </ul>
            </li>
        <?php 
                    $i++;
                    			
                }		// END OF $allProps loop
            }   
        ?>
        <?php echo do_shortcode('[websitetour id="18531"]'); ?>
        </ul>
        <div class="dgt-container">
            <div class="w-list-actions">
                <a href="" class="dgt-btn custom-request" data-cid="<?php if(isset($cid)) echo $cid;?>">Submit a Custom Request</a>
                <a href="" class="dgt-btn">Start a New Search</a>
            </div>
        </div>
    </section>
    <?php 
    function nl2p($string)
    {
        $paragraphs = '';
        
        $string = str_replace("\\", "", $string);
        $string = str_replace("\'", "'", $string);
        
        
        foreach (explode("\n", $string) as $line) {
            if (trim($line)) {
                $paragraphs .= '<p>' . $line . '</p>';
            }
        }
        
        return $paragraphs;
    }
    ?>
