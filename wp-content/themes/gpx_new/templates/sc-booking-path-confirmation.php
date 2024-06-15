<?php
/**
 * @var ?array[] $acCoupon
 * @var stdClass $row
 */
//if auto coupon is present then present the message
if(isset($acCoupon))
{
?>
<dialog id="modal-autocoupon" data-width="800" data-height="500" data-close-on-outside-click="false">
    	<div class="w-modal">
    	  <div class="member-form">
    	    <div class="w-form auto-coupon">
    	    	<h3>Your transaction qualifies for the following discount on a future transaction...</h3>
    	    	<?php
    	    	  foreach($acCoupon as $coupon)
    	    	  {
    	    	?>
    	    	<p>
    	    	<strong>Coupon Name: </strong> <a href="/promotion/<?=$coupon['slug']?>"><?=$coupon['name']?></a><br>
    	    	<strong>Coupon Code: </strong> <?=$coupon['code']?><br>
    	    	<strong>Details: </strong> <?=$coupon['tc']?>
    	    	</p>
    	    	<?php
    	    	  }
    	    	?>
    	    </div>
    	  </div>
    	</div>
  	</dialog>
<?php
}
?>

<div class="cookieremove" data-cookie="gpx-cart"></div>
<section class="w-banner w-results w-results-home">
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
                    <span class="icon book "></span>
                </li>
                <li>
                    <span>Pay</span>
                    <span class="icon pay"></span>
                </li>
                <li>
                    <span>Confirm</span>
                    <span class="icon confirm active"></span>
                </li>
            </ul>
            <div class="line">
                <div class="progress"></div>
            </div>
        </div>
    </div>
</section>
<section class="booking booking-path booking-active booking-confirmation" id="booking-1">
    <div class="w-featured bg-gray-light w-result-home print-el">
        <?php
        if(!isset($transactions))
        {
        ?>
        <div class="w-list-view dgt-container">
            <div class="confirm">
                <div class="cnt">
                <h3>There was an error displaying your confirmation.  Please contact us if this is you're first time viewing this page.</h3>
            </div>
        </div>
        <?php
        } else {
            foreach($transactions as $key=>$transaction)
            {
            ?>
            <div class="w-list-view dgt-container">
            	<?php
            	if(isset($resort))
            	{
            	?>
                <div class="confirm">
                    <div class="cnt">
                        <h3>Payment Confirmation</h3>
                        <p>Please take a moment to check the details of the reservation to ensure they are correct. Any changes or cancellations to this reservation are subject to GPX’s Terms & Conditions and must be made through GPX. This Confirmation must be presented at the time of check-in at the Resort by the person whose name appears as the Arriving Guest below.</p>
                    </div>
                </div>
                <div class="w-item-view filtered">
                    <div class="view">
                        <div class="view-cnt">
                            <div class="descrip">
                                <hgroup>
                                    <h1><?=$resort[$key]->ResortName?></h1>
                                    <span><?=$resort[$key]->Country?> / <?=$resort[$key]->Town?> <?=$resort[$key]->Region?></span>
                                    <br>
                                    <span><strong>Resort ID:</strong> <?=$resort[$key]->ResortID?></span>
                                </hgroup>
                                <p>
                                  <?=$resort[$key]->Address1?>&nbsp;
                                  <?php
                                  if(!empty($resort[$key]->Address2))
                                      echo $resort[$key]->Address2."&nbsp;";
                                  ?>
                                  <?=$resort[$key]->Town?>,&nbsp;
                                  <?=$resort[$key]->Region?>&nbsp;
                                  <?=$resort[$key]->Country?>
                                </p>
                                <p>Assistance? Email: <a href="" mailto:"gpx@gpresorts.com">gpx@gpresorts.com</a> <a href="teL:866.325.6295" aria-label="call"></a>Call: 866.325.6295</p>
                            </div>
                            <div class="w-status">
                                <a href="/resort-profile/?ResortID=<?=$resort[$key]->ResortID?>" class="dgt-btn view" target="_blank">View Resort Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            	}
            	else
            	{
                ?>
                <div class="cnt">
                    <h3>Payment Confirmation</h3>
                </div>
                <?php
            	}
                ?>
                <div class="container-profile">
                    <div class="gateway">
                        <p>Reference Number: <?=$row->id?> Payment Gateway Ref.: <?=$row->paymentGatewayID?></p>
                    </div>
                    <div class="list">
                    	<?php
                    	if(isset($resort))
                    	{
                    	?>
                        <div class="item">
                            <ul>
                                <li>
                                    <p>Membership Number:</p>
                                    <p><strong><?=$transaction->MemberNumber?></strong></p>
                                </li>
                                <li>
                                    <p>Member:</p>
                                    <p><strong><?=$transaction->MemberName?></strong></p>
                                </li>
                                <li>
                                    <p>Arriving Guest(s):</p>
                                    <p><strong><?=$transaction->GuestName?></strong></p>
                                    <p><strong><?=$transaction->Adults?> Adults, <?=$transaction->Children?> Children</strong></p>
                                </li>
                            </ul>
                        </div>
                        <div class="item">
                            <ul>
                                <li>
                                    <p>Check-In:</p>
                                    <?php
                                    if(strtotime($resort[$key]->CheckInEarliest)) {
                                        $justdate = date('m/d/Y', strtotime($transaction->checkIn));
                                        $checkin = date('d F, Y \a\t h:i A', strtotime($justdate." ".$resort[$key]->CheckInEarliest));
                                    } else {
                                        $checkin = date('d F, Y', strtotime($transaction->checkIn));
                                    }
                                        $checkout = date('d F, Y', strtotime($transaction->checkIn." +".$transaction->noNights." days"));
                                    ?>
                                    <p><strong><?=$checkin?></strong></p>
                                </li>
                                <li>
                                    <p>Check-Out:</p>
                                    <p><strong><?=$checkout?></strong></p>
                                </li>
                                <li>
                                    <p>Nights:</p>
                                    <p><strong><?=$transaction->noNights?></strong></p>
                                </li>
                                <li>
                                    <p>Unit Size:</p>
                                    <p><strong><?=$transaction->bedrooms?></strong> (sleeps <?=$transaction->sleeps?> max)</p>
                                </li>
                            </ul>
                        </div>
                        <div class="item last">
                            <ul>
                            </ul>
                        </div>
                        <?php
                    	}
                        ?>
                        <div class="item result">
                            <ul>
                                <li>
                                    <div>
                                        <p>Total Paid:</p>
                                        <p><span>$<?=$transaction->Paid?></span></p>
                                    </div>
                                </li>
                                <li style="font-size:14px;font-style:italic;">* Additional Required Fees paid at resort</li>
                            </ul>
                        </div>
                    </div>
                    <?php
                    if(isset($resort))
                    {
                    ?>
                    <div class="w-expand">
                        <div class="expand_item" id="expand_item_1">
                            <div class="cnt-expand">
                                <h2>Important</h2>
                        	    <div class="w-list-availables" id="expand_3">
                                    <?php
                                    if(!empty($resort[$key]->AlertNote) || !empty($resort[$key]->HTMLAlertNotes) || (isset($resort[$key]->AdditionalInfo) && !empty($resort[$key]->AdditionalInfo)) ||!empty($resort[$key]->DisabledNotes))
                                            {
                                            ?>
                                            <div class="cnt-list">
                                                <ul class="list-cnt full-list">
                                                	<!--
                                                    <li>
                                                        <p><strong>Office Hours</strong></p>
                                                    </li>
                                                    <li>
                                                        <p>Mon and Tues: 8.3 0am – 3 pm</p>
                                                    </li>
                                                    <li>
                                                        <p>Wed and Thurs: 8.30 am – 5 pm</p>
                                                    </li>
                                                    <li>
                                                        <p>Fri and Sat: 8 am – 6 pm</p>
                                                    </li>
                                                    <li>
                                                        <p>Sun and Public Holidays: 9 am – 12 noon</p>
                                                    </li>
                                                    -->
                                                    <?php

                                                    ?>
                                                    <li>
                                                        <p><strong>Alert Note</strong></p>
                                                    </li>
                                                    <?php
                                                    if(!empty($resort[$key]->AlertNote))
                                                	{
                                                	    if(is_array($resort[$key]->AlertNote))
                                                	    {
                                                	        foreach($resort[$key]->AlertNote as $ral)
                                                	        {
                                                	            $theseDates = [];
                                                	            foreach($ral['date'] as $thisdate)
                                                	            {
                                                	                $theseDates[] = date('m/d/y', $thisdate);
                                                	            }
                                                	            ?>
                                                	<li>
                                                            Beginning <?php echo implode(" Ending ", $theseDates)?>:<br/><?=nl2br(stripslashes($ral['desc']))?>
                                                	</li>
                                                	<?php
                                            		        }
                                            		    }
                                            		    else
                                            		    {
                                            		?>
                                                        <?=nl2br(stripslashes($resort[$key]->AlertNote))?>
                                                	<?php
                                            		    }
                                            		}
                                            		if(!empty($resort[$key]->HTHMLAlertNotes) && empty($resort[$key]->AlertNote))
                                            		{
                                            	    ?>
                                                    <li>
                                            			<div><?=nl2br(stripslashes($resort[$key]->HTMLAlertNotes))?></div>
                                                    </li>
                                                        <?php
                                            		}
                                                        if(isset($resort[$key]->AdditionalInfo) && !empty($resort[$key]->AdditionalInfo))
                                                        {
                                                    ?>
                                                    <li>
                                                        <p><strong>Additional Info</strong></p>
                                                    </li>
                                                    <li>
                                                        <div><?=nl2br(stripslashes($resort[$key]->AdditionalInfo))?></div>
                                                    </li>
                                                    <?php
                                                        }
                                                    ?>

                                                </ul>
                                            </div>
                                            <?php
                                            }
                                            ?>
                                </div>
                            </div>
                            <div class="cnt-seemore">
                                <a href="#" class="seemore">
                                    <span class="less">Read more</span>
                                    <i class="icon-arrow-down"></i>
                                </a>
                            </div>
                        </div>
                        <div class="expand_item" id="expand_item_2">
                            <div class="cnt-expand">
                                <h2>Terms & Conditions</h2>
                                <div class="cnt">
                                <?php
                                if(isset($tcs))
                                    foreach($tcs as $promoTerm)
                                    {
                                        ?>
                            	<div><?=nl2br($promoTerm)?></div>
                                <?php
                                   }
                                if(isset($atts['terms']) && !empty($atts['terms']))
                                    echo '<p>'.$atts['terms'].'</p>';
                                ?>
                                </div>
                            </div>
                            <div class="cnt-seemore">
                                <a href="#" class="seemore">
                                    <span class="less">Read more</span>
                                    <i class="icon-arrow-down"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                    }
                    ?>
                    <div class="more-info">
                        <p>Grand Pacific Exchange • 5900 Pasteur Court Suite 100 • Carlsbad, CA 92008</p>
                        <p>Telephone: 1 (866) 325-6295 • (760) 827-4417 • Facsimile (760) 828-4242 • GPX@gpresorts.com • www.gpxvacations.com</p>
                    </div>
                </div>
            </div>
            <?php
            }
        }
        ?>
    </div>
</section>
