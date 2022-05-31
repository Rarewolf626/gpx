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
        if($prop->WeekType == 'ExchangeWeek')
        {
            $priceorfee = "Exchange Fee";
            $prop->WeekType = 'Exchange Week';
        }
        else
        {
            $priceorfee = 'Price';
            if(!isset($prop))
            {
                $prop = new stdClass();
            }
            $prop->WeekType = 'Rental Week';
        }
        $addLink = site_url();
        if(isset($property_details['bogo']))
            $addLink = site_url()."/promotion/".$property_details['bogo'];
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
    ?>
<?php
//
if(isset($unsetFilterMost))
{
?>
<div class="unset-filter-false"></div>
<?php
}
?>
<div id="ajaxinfo" data-pid="<?=$book?>" data-cid="<?=$cid?>" data-lpid="<?=$lpid?>" data-type="<?=$prop->WeekType?>" data-wid="<?=$prop->weekId?>"></div>
<section class="w-banner w-results w-results-home checklogin">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result rs-col-3 booking-path">
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg" alt="" />
        </li>
    </ul>
    <div class="dgt-container w-box">
        <div class="w-options w-results">

        </div>
        <div class="w-progress-line">
            <ul>
                <li>
                    <span>Select</span>
                    <span class="icon select"></span>
                </li>
                <li>
                    <span>Book</span>
                    <span class="icon book active"></span>
                </li>
                <li>
                    <span>Pay</span>
                    <span class="icon pay"></span>
                </li>
                <li>
                    <span>Confirm</span>
                    <span class="icon confirm"></span>
                </li>
            </ul>
            <div class="line">
                <div class="progress"></div>
            </div>
        </div>
    </div>
</section>
<?php include(locate_template( 'template-parts/universal-search-widget.php' )); ?>
<?php
//if(isset($cid) && !empty($cid) && !isset($property_error) && ($role != 'gpx_member' && $cid != get_current_user_id()))
if(isset($errorMessage) && $prop->WeekType == 'Exchange Week')
{
?>
<section class="booking booking-payment booking-active" id="booking-3">
    <div class="w-filter dgt-container">
		<h3><?=$errorMessage?></h3>
	</div>
</section>
<?php
}
elseif(isset($cid) && !empty($cid) && !isset($property_error))
{
    if($role != 'gpx_member' && $cid == get_current_user_id())
    {
    ?>
    <div class="agentLogin"></div>
    <?php
    }
?>
<section class="booking booking-path booking-active" id="booking-1">
    <div class="w-filter dgt-container">
        <div class="left">
            <h3>Your Next Vacation</h3>
        </div>
        <div class="right">
            <?php
            $referrer = wp_get_referer();
            if (strpos($referrer, 'promotion') !== false)
            {
                $returnLink = $referrer;
            }
            else
            {
                $returnLink = site_url().'/';
            }
            ?>
            <a href="<?php echo $returnLink;?>" class="remove-hold" data-pid="<?=$book?>" data-cid="<?=$cid?>" data-redirect="<?=$returnLink?>" data-bookingpath="1">
                <h3> <span>Cancel and Start New Search </span> <i class="icon-close"></i></h3>
            </a>
        </div>
    </div>
    <div class="w-featured bg-gray-light w-result-home">
        <div class="w-list-view dgt-container">
            <div class="w-item-view filtered">
                <div class="view">
                    <div class="view-cnt">
                                    	<?php
                	$imgThumb = $prop->ImagePath1;
                	$imageTitle = strtolower($prop->ResortName);
        	$imageAlt = $prop->ResortName;
        	//check for updated images
        	$sql = $wpdb->prepare("SELECT meta_value FROM wp_resorts_meta WHERE meta_key='images' AND ResortID=%s", $prop->ResortID);
        	$rawResortImages = $wpdb->get_row($sql);
        	if(!empty($rawResortImages->meta_value))
        	{
        	   $resortImages = json_decode($rawResortImages->meta_value, true);
        	   $oneImage = $resortImages[0];
        	   $imgThumb = $oneImage['src'];
        	   if($oneImage['type'] == 'uploaded')
        	   {
        	       $id = $oneImage['id'];
        	       $imageAlt = get_post_meta( $id ,'_wp_attachment_image_alt', true);
        	       $imageTitle = get_the_title($id);
        	   }
        	}
        	?>
            <img src="<?=$imgThumb?>" alt="<?=$imageAlt;?>" title="<?=$imageTitle?>">
                    </div>
                    <div class="view-cnt">
                        <div class="descrip">
                            <hgroup>
                                <h2><?=$prop->ResortName?></h2>
                                <span><?=$prop->Town?>, <?=$prop->Region?></span>
                            </hgroup>
                            <p>Check-In <?=date('d M Y', strtotime($prop->checkIn))?></p>
                            <p>Check-Out <?=date('d M Y', strtotime($prop->checkIn.' + '.$prop->noNights.' days'))?></p>
                        </div>
                        <div class="w-status">
                            <div class="result">
                            </div>
                            <ul class="status">
                                <?php
                            	   $status = array('status-exchange'=>'ExchangeWeek','status-rental'=>'BonusWeek');
                            	   foreach($status as $key=>$value)
                            	   {
                            	       if($value == $prop->WeekType)
                            	       {
                            	        ?>
                                 <li>
                                    <div class="<?=$key;?>"></div>
                                </li>
                            	        <?php
                            	       }
                            	   }
                            	?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="view-detail">
                    <ul class="list-result">
                        <li>
                            <p><strong>Select Week Number</strong></p>
                            <p><?=$prop->weekId?></p>
                        </li>
                        <li>
                            <p><strong>Week Type</strong></p>
                            <p><?=$prop->WeekType?></p>
                        </li>
                        <li>
                            <p><strong><?=$priceorfee?></strong></p>
                            <p>

                            	<?php

                            	if(isset($_REQUEST['promo_debug']))
                            	{
                            	    echo '<pre>'.print_r($specialPrice, true).'</pre>';
                            	    echo '<pre>'.print_r($prop->WeekPrice, true).'</pre>';
                            	}
                            	   if(empty($specialPrice))
                                        echo '$'.number_format($prop->WeekPrice, 0);
                            	   else
                            	   {
                            	       if($specialPrice != $prop->Price)
                            	       {
                            	           $numformat = 0;
                            	           if(substr($prop->WeekPrice, -3) == '.00')
                            	           {
                            	               $numformat = 0;
                            	           }
                            	           if(!empty($prop->specialIcon) || (isset($prop->slash) && $prop->slash == 'Force Slash'))
                            	           {
                                       ?>
                                           <span style="text-deocoration: line-through;">$<?=$prop->WeekPrice?></span>
                                           <?php
                            	           }
                            	           //let's get the price into the correct format...
                            	           $reformatWeekPrice = str_replace(",", "", $prop->WeekPrice);
                            	           $reformatSpecialPrice = str_replace(",", "", $specialPrice);
                            	           $reformatSpecialPrice = number_format($reformatSpecialPrice, $numformat, ".", "");
                            	           $reformatPrice = str_replace(",", "", $prop->Price);
                            	           $reformatPrice = number_format($reformatPrice, $numformat, ".", "");

                                           ?>
                                           $<?=str_replace($reformatPrice, $reformatSpecialPrice, $reformatWeekPrice)?>
                            	       <?php
                            	       }
                            	       else
                            	       {
                                	       echo '$'.number_format($prop->WeekPrice, 0);
                            	       }

                            	   }
                            	?>
                            </p>
                        </li>
                        <li>
                            <p><strong>Check In</strong></p>
                            <p><?=date('d M Y', strtotime($prop->checkIn))?></p>
                        </li>
                        <li>
                            <p><strong>Check Out</strong></p>
                            <p><?=date('d M Y', strtotime($prop->checkIn.' + '.$prop->noNights.' days'))?></p>
                        </li>
                    </ul>
                    <ul class="list-result">
                        <li>
                            <p><strong>Nights</strong></p>
                            <p><?=$prop->noNights?></p>
                        </li>
                        <li>
                            <p><strong>Bedrooms</strong></p>
                            <p><?=$prop->bedrooms?></p>
                        </li>
                        <li>
                            <p><strong>Sleep</strong></p>
                            <p><?=$prop->sleeps?></p>
                        </li>
                    </ul>
                </div>
                <?php
                if(isset($prop->specialDesc))
                {
                ?>
                   <dialog class="modal-special" id="spDesc<?=$prop->weekId?>" data-close-on-outside-click="false">
                    	<div class="w-modal stupidbt-reset">
                    		<p><?=$prop->specialDesc?></p>
                    	</div>
                    </dialog>
                <?php
                }
                ?>
            </div>
            <div class="tabs">
                <h2>Please Review Booking Policies Before Proceeding</h2>
                <div class="head-tab">
                    <ul>
                        <li>
                            <a href="" data-id="tab-1" class="head-active">Know Before You Go</a>
                        </li>
                        <li>
                            <a href="" data-id="tab-2" >Terms & Conditions</a>
			</ul>
                        <br><br>

                       <h2> </li><strong>All transactions are non-refundable</strong></h2>
                       <br><br>
                       </ul>
                </div>
                <div class="content-tabs">
                    <div id="tab-1" class="item-tab tab-active">
                        <div class="item-tab-cnt">
                        	<p>
                        	<?php
                        	if(!empty($prop->AlertNote))
                        	{
                        	    if(is_array($prop->AlertNote))
                        	    {
                        	        ?>
                        	        <ul class="albullet">
                        	        <?php
                        	        foreach($prop->AlertNote as $ral)
                        	        {
                        	            $theseDates = [];
                        	            foreach($ral['date'] as $thisdate)
                        	            {
                        	                $theseDates[] = date('m/d/y', $thisdate);
                        	            }
                        	            ?>
                        	        <li>
                                    <strong>Beginning <?php echo implode(" Ending ", $theseDates)?>:</strong><br/><?=nl2p(stripslashes($ral['desc']))?>
                        			</li>
                        	<?php
                    		        }
                    		        ?>
                    		        </ul>
                    		        <?php
                    		    }
                    		    else
                    		    {
                    		?>
                                <?=nl2p(stripslashes($prop->AlertNote))?>
                        	<?php
                    		    }
                    		}
                        	?>

                            	<?=nl2p(stripslashes($prop->AdditionalInfo))?>
                            	<?php
                            	if(!empty($prop->HTMLAlertNotes) && empty($prop->AlertNote))
                            	{
                            	?>
                            	<br><br><?=nl2p(stripslashes($prop->HTMLAlertNotes))?>
                            	<?php
                            	}
                            	?>
                        	</p>
                        </div>
                        <div class="item-seemore">
                            <a href="#" class="seemore"> <span>See more</span> <i class="icon-arrow-down"></i></a>
                        </div>
                    </div>
                    <div id="tab-2" class="item-tab" >
                        <div class="item-tab-cnt">
                        	<?php
                        	   foreach($promoTerms as $promoTerm)
                        	   {
                        	   ?>
                        	<p><?=nl2p($promoTerm)?></p>
                        	   <?php
                        	   }
                        	?>
                        	<?php
                        	if(isset($atts['terms']) && !empty($atts['terms']))
                        	    echo '<p>'.$atts['terms'].'</p>';
                        	?>
                        </div>
                        <div class="item-seemore">
                            <a href="#" class="seemore"> <span>See more</span> <i class="icon-arrow-down"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="check">
                <div class="hold-error"></div>
                <div class="cnt">
                    <input type="checkbox" id="chk_terms" required>
                    <label for="chk_terms">
                        I have reviewed and understand the terms and conditions above
                    </label>
                </div>
                <div class="cnt">
                    <a href="" class="dgt-btn btn-next" id="next-1" data-id="booking-2" >Next</a>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="booking booking-exchange" id="booking-2">
    <div class="w-filter dgt-container">
        <div class="left">
            <h3>Your Next Booking</h3>
        </div>
        <div class="right">
            <a href="<?php echo site_url(); ?>/" class="remove-hold" data-pid="<?=$book?>" data-cid="<?=$cid?>" data-redirect="<?=$returnLink?>" data-bookingpath="1">
                 <h3> <span>Cancel and Start New Search </span> <i class="icon-close"></i></h3>
            </a>
        </div>
    </div>
    <div class="w-featured bg-gray-light w-result-home">
        <div class="w-list-view dgt-container">
            <div class="w-item-view filtered">
                <div class="view">
                    <div class="view-cnt">
                                    	<?php
                	$imgThumb = $prop->ImagePath1;
                	$imageTitle = strtolower($prop->ResortName);
        	$imageAlt = $prop->ResortName;
        	//check for updated images
        	$sql = $wpdb->prepare("SELECT meta_value FROM wp_resorts_meta WHERE meta_key='images' AND ResortID=%s", $prop->ResortID);
        	$rawResortImages = $wpdb->get_row($sql);
        	if(!empty($rawResortImages->meta_value))
        	{
        	   $resortImages = json_decode($rawResortImages->meta_value, true);
        	   $oneImage = $resortImages[0];
        	   $imgThumb = $oneImage['src'];
        	   if($oneImage['type'] == 'uploaded')
        	   {
        	       $id = $oneImage['id'];
        	       $imageAlt = get_post_meta( $id ,'_wp_attachment_image_alt', true);
        	       $imageTitle = get_the_title($id);
        	   }
        	}
        	?>
            <img src="<?=$imgThumb?>" alt="<?=$imageAlt;?>" title="<?=$imageTitle?>">
                    </div>
                    <div class="view-cnt">
                        <div class="descrip">
                            <hgroup>
                                <h2><?=$prop->ResortName?></h2>
                                <span><?=$prop->Town?>, <?=$prop->Region?></span>
                            </hgroup>
                            <p>Check-In <?=date('d M Y', strtotime($prop->checkIn))?></p>
                            <p>Check-Out <?=date('d M Y', strtotime($prop->checkIn.' + '.$prop->noNights.' days'))?></p>
                        </div>
                        <div class="w-status">
                            <div class="result">
                            </div>
                            <ul class="status">
                                <?php
                            	   $status = array('status-exchange'=>'ExchangeWeek','status-rental'=>'BonusWeek');
                            	   foreach($status as $key=>$value)
                            	   {
                            	       if($value == $prop->WeekType)
                            	       {
                            	        ?>
                                 <li>
                                    <div class="<?=$key;?>"></div>
                                </li>
                            	        <?php
                            	       }
                            	   }
                            	?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php
                if($prop->WeekType == 'Exchange Week')
                {
                ?>
                <div class="exchange-credit">
                    <div id="exchangeList" data-weekendpointid="<?=$prop->WeekEndpointID?>" data-weekid="<?=$prop->weekId?>" data-weektype="<?=$prop->WeekType?>" data-id="<?=$_GET['book']?>">

                    </div>
                </div>
                <?php
                }
                else
                {
                ?>
                 <div class="bonus-week-details">
                    <div id="bonusWeekDetails" data-weekendpointid="<?=$prop->WeekEndpointID?>" data-weekid="<?=$prop->weekId?>" data-weektype="<?=$prop->WeekType?>" data-id="<?=$_GET['book']?>">

                    </div>
                </div>
                <?php
                }
                ?>
            </div>
            <div class="member-form">
            <?php
            ?>
                <hgroup>
                    <h2>Member / Guest Information</h2>
                    <h2>GPX Member: <strong><?=$usermeta->LastName1?>, <?=$usermeta->FirstName1?></strong></h2>
                </hgroup>
                <div class="w-form">
                    <form action="" id="guestInfoForm" class="material">
                        <input type="hidden" name="user" value="<?=$cid?>">
                        <input type="hidden" name="weekType" value="<?=$prop->WeekType?>">
                        <input type="hidden" name="propertyID" value="<?=$prop->id?>">
                        <input type="hidden" name="weekId" value="<?=$prop->weekId?>">
                        <input type="hidden" name="promoName" value="<?=$promoName?>">
                        <input type="hidden" name="discount" value="<?=$discountAmt?>">
                        <input type="hidden" name="cartID" value="<?=$_COOKIE['gpx-cart']?>">
                        <input type="hidden" name="CPOPrice" id="CPOPrice" value="">
                        <?php
                        if(isset($autoCoupons))
                            {
                               foreach($autoCoupons as $autoCoupon)
                               {
                        ?>
                        <input type="hidden" name="coupon[]" value='<?=$autoCoupon?>'>
						<?php
                               }
                            }
                        if((isset($prop->guestFeesEnabled) && $prop->guestFeesEnabled) || (get_option('gpx_global_guest_fees') == '1' && (get_option('gpx_gf_amount') && get_option('gpx_gf_amount') > $gfAmount)))
                        {
                        ?>
                        <input type="hidden" name="GuestFeeAmount" id="GuestFeeAmount" value="">
                        <?php
                        }
                        ?>
                        <div class="head-form">
                            <input type="checkbox" id="rdb-reservation">
                            <label for="rdb-reservation">Click here to assign this reservation to a guest
                            <?php
                            if((isset($prop->guestFeesEnabled) && $prop->guestFeesEnabled)  || (get_option('gpx_global_guest_fees') == '1' && (get_option('gpx_gf_amount') && get_option('gpx_gf_amount') > $gfAmount)))
                            {


                                if(get_option('gpx_global_guest_fees') == '1' && (get_option('gpx_gf_amount') && get_option('gpx_gf_amount') > $gfAmount))
                                {
                                    $gfAmt = get_option('gpx_gf_amount');
                                }
                                if(isset($prop->GuestFeeAmount) && !empty($prop->GuestFeeAmount))
                                {
                                    $gfAmt = $prop->GuestFeeAmount;
                                }
                                if(isset($prop->upsellDisc))
                                {
                                    $upsellDisc = $prop->upsellDisc;
                                    if(is_array($upsellDisc))
                                    {
                                        foreach($upsellDisc as $ud)
                                        {
                                            if(($ud['option'] == 'Guest Fees' || in_array('Guest Fees', $ud['option'])))
                                            {
                                                $guestfeesenabled = true;
                                            }
                                        }
                                    }
                                    else
                                    {
                                        if(($upsellDisc['option'] == 'Guest Fees' || in_array('Guest Fees', $upsellDisc['option'])))
                                        {
                                            $guestfeesenabled = true;
                                        }
                                    }
                                }

                                if(isset($guestfeesenabled))
                                {
                                    foreach($upsellDisc as $usd)
                                    {
                                        if($usd['type'] == 'Pct Off')
                                        {
                                            $gfDisc = number_format($gfAmt*($usd['amount']/100),0);
                                        }
                                        elseif($usd['type'] == 'Set Amt')
                                        {
                                            $gfDisc = $gfAmt;
                                        }
                                        else
                                        {
                                            $gfDisc = $usd['amount'];
                                        }

                                        if($gfDisc > $gfAmt)
                                        {
                                            $gfDisc = $gfAmt;
                                        }

                                        $gfSlash = $gfSlash + $gfAmt;

                                        $gfAmt = $gfAmt - $gfDisc;
                                    }
                                }
                                $gfAmount = '';
                                if(isset($gfSlash))
                                {
                                    $gfAmount .= '<span style="text-decoration: line-through;">$'.$gfSlash.'</span> ';
                                }
                                $gfAmount .= '$'.$gfAmt;

                                ?>
                            	(a fee of <?=$gfAmount?> will be applied)
                            	<dialog id="modal-guest-fees" data-width="800" data-height="500" data-close-on-outside-click="false">
                                	<div class="w-modal">
                                		<div class="member-form">
                                			<div class="w-form">
                                        		<h2>Guest Fees Required</h2>
                                        		<div class="gform_wrapper">
        											<h4>By continuing you acknowldge that a <?=$gfAmount?> fee will be added to this transaction at checkout.</h4>
        											<a href="#" class="dgt-btn guest-fee-cancel">Cancel</a>
        											<a href="#" class="dgt-btn guest-fee-confirm" style="margin-left: 10px;">Continue</a>
                                        		</div>
                                			</div>
                                		</div>
                                	</div>
                                </dialog>
                            <?php
                            }
                            ?>
                            </label>
                        </div>
                        <div id="gifReplace">
                        <?php
                            foreach($profilecols as $col)
                            {
                             ?>
                             <ul class="list-form guest-form-data">
                             <?php
                                foreach($col as $data)
                                {
                                    //set the variables for the value
                                    $value = '';
                                    $fromvar = $data['value']['from'];
                                    $from = $$fromvar;
                                    $retrieve = $data['value']['retrieve'];
                                    if(isset($from->$retrieve))
                                        $value = $from->$retrieve;
                                ?>
                                <li>
                                    <div class="ginput_container">
                                    <?php
                                    /*
                                        if(isset($data['select']))
                                        {
                                            $sleeps = $prop->sleeps
                                    ?>
                                    <div class="group">
                                        <div class="ginput_container">
                                            <select name="adults" placeholder="Adults"  id="adults" class="sleep-check" data-max="<?=$sleeps?>" required="required">
                                            	<option select></option>
                                            <?php
                                                for($s=1;$s<=$sleeps;$s++)
                                                {
                                            ?>
                                                <option value="<?=$s?>"><?=$s?></option>
                                            <?php
                                                }
                                            ?>
                                            </select>
                                            <select name="children" placeholder="Children" id="children" data-max="<?=$sleeps?>" required="required">
                                                <option select></option>
                                            <?php
                                                for($s=0;$s<=$sleeps;$s++)
                                                {
                                            ?>
                                                <option value="<?=$s?>"><?=$s?></option>
                                            <?php
                                                }
                                            ?>
                                            </select>
                                        </div>
                                    </div>
                                    <?php
                                        }
                                        elseif(isset($data['textarea']))
                                        */
                                        if(isset($data['textarea']))
                                        {
                                    ?>
                                        <textarea maxlength="255" type="text" placeholder="<?=$data['placeholder']?>" name="<?=str_replace(" ", "", $data['placeholder'])?>" class="<?=$data['class']?> guest-reset" value="<?=$value;?>" <?=$data['required']?>></textarea>
                                    <?php
                                        }
                                        else
                                        {
                                            $inputname = $retrieve;
                                            if(isset($data['value']['name']))
                                            {
                                                $inputname = $data['value']['name'];
                                            }
                                    ?>
                                        <input type="<?=$data['type']?>" placeholder="<?=$data['placeholder']?>" name="<?=$inputname?>" class="<?=$data['class']?> guest-reset" value="<?=$value;?>" data-default="<?=$value?>"
                                        <?php
                                            if($retrieve == 'adults' || $retrieve == 'children')
                                            {
                                            ?>
                                         data-max="<?=$prop->sleeps?>"
                                            <?php
                                            }
                                        ?>
                                         <?=$data['required']?>>
                                    <?php
                                        }
                                    ?>
                                    </div>
                                </li>
                                <?php
                                }
                             ?>
                             </ul>
                             <?php
                            }
                            $isdisabled = '';
                            if($weekType == 'Exchange')
                            {
                                $isdisabled = 'disabled';
                            }

                        ?>
                        </div>
                       <div class="gform_footer">
                            <!--<input class=" dgt-btn" type="submit" value="Next">-->
                            <a href="<?php echo $addLink; ?>/" class="dgt-btn submit-guestInfo" data-id="booking-3" style="display: none;">Add Properties</a>
                            <a href="<?php echo site_url(); ?>/booking-path-payment/" class="dgt-btn submit-guestInfo <?=$disabled?>" data-id="booking-3">Checkout</a>
                        </div>
                    </form>
                </div>
                <div id="savedForm" style="display: none;"></div>
            </div>
        </div>
    </div>
</section>
<section class="booking booking-payment" id="booking-3">
    <div class="w-filter dgt-container">
        <div class="left">
            <h3>Hilton Grand Vacations Club at MarBrisa</h3>
        </div>
        <div class="right">
            <a href="<?php echo site_url(); ?>/" class="remove-hold" data-pid="<?=$book?>" data-cid="<?=$cid?>" data-redirect="<?=$returnLink?>" data-bookingpath="1">
                <h3>Cancel and Start New Search</h3>
            </a>
        </div>
    </div>
    <div class="w-featured bg-gray-light w-result-home">
        <div class="w-list-view dgt-container">
            <div class="w-item-view">
                <div class="view-detail">
                    <ul class="list-result">
                        <li>
                            <p><strong>Select Week Number</strong></p>
                            <p>330418</p>
                        </li>
                        <li>
                            <p><strong>Week Type</strong></p>
                            <p>Exchange</p>
                        </li>
                        <li>
                            <p><strong>Price</strong></p>
                            <p>USD $189</p>
                        </li>
                        <li>
                            <p><strong>Check In</strong></p>
                            <p>05 Mar 2016</p>
                        </li>
                        <li>
                            <p><strong>Check Out</strong></p>
                            <p>12 Mar 2016</p>
                        </li>
                    </ul>
                    <ul class="list-result">
                        <li>
                            <p><strong>Nights</strong></p>
                            <p>7</p>
                        </li>
                        <li>
                            <p><strong>Bedrooms</strong></p>
                            <p>Studio</p>
                        </li>
                        <li>
                            <p><strong>Sleep</strong></p>
                            <p>2 + 1</p>
                        </li>
                    </ul>
                </div>
                <div class="exchange-credit">
                    <hgroup>
                        <h2>Exchange Credit</h2>
                        <p>Choose and exchange credit to use for this exchange booking</p>
                    </hgroup>
                    <ul class="exchange-list">
                        <li class="exchange-item">
                            <div class="w-credit">
                                <div class="head-credit">
                                    <input type="checkbox" id="rdb-credit-1" value="1" name="radio[1][]">
                                    <label for="rdb-credit-1">Apply Credit</label>
                                </div>
                                <div class="cnt-credit">
                                    <ul>
                                        <li>
                                            <p><strong>Grand Pacific Palasades Resort and Hotel</strong></p>
                                            <p>2587658</p>
                                        </li>
                                        <li>
                                            <p><strong>Expires:</strong></p>
                                            <span>07 Jan 2017</span>
                                        </li>
                                        <li>
                                            <p><strong>Entitlement Year:</strong></p>
                                            <span>2018</span>
                                            <p>Size: 2bdr./sleeps 7</p>
                                        </li>
                                        <li>
                                            <p>Please note: This booking requires and upgrade fee</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <li class="exchange-item">
                            <div class="w-credit">
                                <div class="head-credit">
                                    <input type="checkbox" id="rdb-credit-2" value="1" name="radio[2][]">
                                    <label for="rdb-credit-2">Apply Credit</label>
                                </div>
                                <div class="cnt-credit">
                                    <ul>
                                        <li>
                                            <p><strong>Grand Pacific Palasades Resort and Hotel</strong></p>
                                            <p>2587658</p>
                                        </li>
                                        <li>
                                            <p><strong>Expires:</strong></p>
                                            <span>07 Jan 2017</span>
                                        </li>
                                        <li>
                                            <p><strong>Entitlement Year:</strong></p>
                                            <span>2018</span>
                                            <p>Size: 2bdr./sleeps 7</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <li class="exchange-item">
                            <div class="w-credit">
                                <div class="head-credit">
                                    <input type="checkbox" id="rdb-credit-3" value="1" name="radio[3][]">
                                    <label for="rdb-credit-3">Apply Credit</label>
                                </div>
                                <div class="cnt-credit">
                                    <ul>
                                        <li>
                                            <p><strong>Grand Pacific Palasades Resort and Hotel</strong></p>
                                            <p>2587658</p>
                                        </li>
                                        <li>
                                            <p><strong>Expires:</strong></p>
                                            <span>07 Jan 2017</span>
                                        </li>
                                        <li>
                                            <p><strong>Entitlement Year:</strong></p>
                                            <span>2018 </span>
                                            <p>Size: 2bdr./sleeps 7</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="promotional">
                <h3>Coupon Code</h3>
                <div class="w-cnt">
                    <form action="" class="material">
                        <div class="gwrapper">
                            <div class="ginput_container">
                                <input type="text" placeholder="Enter a Coupon Code">
                            </div>
                            <div class="ginput_container">
                                <input type="submit" class="dgt-btn" value="Submit">
                            </div>
                        </div>
                        <div class="gwrapper">
                            <div class="ginput_container">
                                <p>You have a <span>$250</span> Credit.</p>
                            </div>
                            <div class="ginput_container">
                                <a href="" class="dgt-btn">Apply Discount</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="payment">
                <h3>Payment</h3>
                <div class="w-cnt">
                    <div class="w-list-cart">
                        <div class="carts">
                            <form action="" class="material">
                                <ul>
                                    <li><img src="<?php echo get_template_directory_uri(); ?>/images/payment.png" alt="logo" width="" height=""></li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Street Address">
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Post/Zip Code">
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <select name="mySelect3" placeholder="Country">
                                                <option value="1" select>Option 1</option>
                                                <option value="2">Option 2</option>
                                                <option value="3">Option 3</option>
                                                <option value="4">Option 4</option>
                                            </select>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Email">
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Cardholder Name">
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Cardholder Number">
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="SVV">
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container ginput_date">
                                            <p>Expiration Date</p>
                                            <div class="selects">
                                                <select name="mySelect4" placeholder="Month">
                                                    <option value="1" select>Option 1</option>
                                                    <option value="2">Option 2</option>
                                                    <option value="3">Option 3</option>
                                                    <option value="4">Option 4</option>
                                                </select>
                                                <select name="mySelect5" placeholder="Year">
                                                    <option value="1" select>Option 1</option>
                                                    <option value="2">Option 2</option>
                                                    <option value="3">Option 3</option>
                                                    <option value="4">Option 4</option>
                                                </select>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </form>
                        </div>
                    </div>
                    <ul class="w-list-details">
                        <li>
                            <div class="gtitle">
                                <span>Payment Details</span>
                            </div>
                        </li>
                        <li>
                            <p>Booking <strong>Hiltron Grand Vacations Club at MarBrisa</strong></p>
                        </li>
                        <li>
                            <div class="result">
                                <p>USD <span>$189.00</span> $129.00</p>
                            </div>
                        </li>
                        <li>
                            <div class="result">
                                <p>Account Credit $0.00</p>
                            </div>
                        </li>
                        <li>
                            <div class="result">
                                <p><strong>remove</strong> CPO $0.00</p>
                            </div>
                        </li>
                        <li>
                            <div class="result">
                                <p>Upgrade Fee $99.00</p>
                            </div>
                        </li>
                        <li>
                            <div class="result">
                                <p>Discount $0.00</p>
                            </div>
                        </li>
                        <li>
                            <div class="result noline">
                                <p> Taxes: Included</p>
                            </div>
                        </li>
                        <li>
                            <div class="result total">
                                <p>Total: $228.00</p>
                            </div>
                        </li>
                        <li>
                            <div class="message">
                                <p>This charge will on your credit card statement as <strong>Grand Pacific Exchange</strong></p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="payconfirm">
                <div class="check">
                    <div class="cnt">
                        <input type="checkbox" id="chk_terms_2">
                        <label for="chk_terms_2">
                            I have reviewed and understand the terms and conditions below
                        </label>
                    </div>
                    <div class="cnt">
                        <a href="" class="dgt-btn btn-next" id="next-3" data-id="booking-4">Pay & Confirm</a>
                    </div>
                </div>
                <p><strong>All transactions are non-refundable</strong></p>
                <br><br>
                <p>
                    I understand, if confirming a larger unit size than what I deposited I am subject to an upgrade fee. Upgrade fees are as follows: studio deposit to a one (1) bedroom exchange is $85; studio deposit to a two (2) or three (3) bedroom exchange is $185; one (1) bedroom deposit to two (2) or three (3) bedroom exchange is $185; no upgrade fee is required when two (2) bedroom deposit is exchanged for a three (3) bedroom. This upgrade fee is in addition to the exchange fee. A GPX representative will call you the next business day to collect this upgrade fee. If GPX is unable to collect this fee within 48 hours, your exchange is subject to cancellation. I understand and agree that my credit card will be charged immediately for the exchange transaction amount indicated and that this transaction is bound by the terms and conditions of Grand Pacific Exchange for the confirmation of this vacation. THIS DOES NOT APPLY TO MEMBERS BOOKING BONUS WEEKS AS THIS DOES NOT REQUIRE A CREDIT DEPOSIT.
                </p>
            </div>
        </div>
    </div>
</section>
<?php
}
elseif(isset($property_error))
{
?>
<section class="booking booking-payment booking-active" id="booking-1">
    <div class="w-filter dgt-container">
        <div class="left">
            <h1>Invalid Property</h1>
        </div>
        <div class="right">

            <a href="<?php echo site_url(); ?>/" class="remove-hold" data-pid="<?=$book?>" data-cid="<?=$cid?>" data-redirect="<?=$returnLink?>" data-bookingpath="1">
                 <h3> <span>Cancel and Start New Search </span> <i class="icon-close"></i></h3>
            </a>
        </div>
    </div>
    <div class="w-featured bg-gray-light w-result-home">
        <div class="w-list-view dgt-container">
            <div class="w-item-view filtered">
                  <h3>This property isn't available. <a href="<?php echo site_url(); ?>/" class="blue remove-hold" data-pid="<?=$book?>" data-cid="<?=$cid?>" data-redirect="<?=$returnLink?>" data-bookingpath="1">Cancel and Start New Search</a></h3>
             </div>
        </div>
    </div>
</section>
<?php
}
else
{
?>
<section class="booking booking-payment booking-active" id="booking-1">
    <div class="w-filter dgt-container">
        <div class="left">
            <h1>Please Login</h1>
        </div>
        <div class="right">

            <a href="<?php echo site_url(); ?>/" class="remove-hold" data-pid="<?=$book?>" data-cid="<?=$cid?>" data-redirect="<?=$returnLink?>" data-bookingpath="1">
                 <h3> <span>Cancel and Start New Search </span> <i class="icon-close"></i></h3>
            </a>
        </div>
    </div>
    <div class="w-featured bg-gray-light w-result-home">
        <div class="w-list-view dgt-container">
            <div class="w-item-view filtered" id="signInError">
                  <h3>You must be logged in to book a property.  Please <a href="#" class="call-modal-login">sign in</a> to continue.</h3>
             </div>
        </div>
    </div>
</section>
<?php
}
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
