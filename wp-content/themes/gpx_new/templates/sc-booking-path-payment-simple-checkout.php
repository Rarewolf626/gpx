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
$nopriceint = '$';
//check to see if booking is disabled
$bookingDisabeledClass = '';
$bookingDisabledActive = get_option('gpx_booking_disabled_active');
$fbFee = get_option('gpx_fb_fee');
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

<?php if(isset($carterror)): ?>
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
                    <span>Pay</span>
                    <span class="icon pay active"></span>
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
<section class="booking booking-payment booking-active" id="booking-3">
    <div class="w-filter dgt-container">
		<h3>There was an error processing your request.  Please <a href="#" class="return-back">return</a> and try again.</h3>
	</div>
</section>
<?php else: ?>
<div class="checkhold" data-pid="<?=$book?>" data-cid="<?=$cid?>"></div>
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
                    <span>Pay</span>
                    <span class="icon pay active"></span>
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
<section class="booking booking-payment booking-active" id="booking-3">
    <div class="w-featured bg-gray-light w-result-home">
        <div class="w-list-view dgt-container">
            <div class="promotional">
                <div class="bk-path-headline"><h3>Coupon Code</h3></div>
                <div class="w-cnt">
                    <form action="" class="material">

                    <div class="inlinebox">
                          <div class="gwrapper">
                            <div class="ginput_container">
                                <input type="text" id="couponCode" name="couponCode" data-book="<?=$book?>" data-cid="<?=$cid?>" data-cartID="<?=$_COOKIE['gpx-cart']?>" data-currentPrice="<?=array_sum($indPrice)-$taxTotal?>" placeholder="Enter a Coupon Code" value="" >
                            </div>
                            <div class="ginput_container">
                                <input type="submit" class="dgt-btn" id="couponAdd" value="Submit">
                            </div>
                            <div id="couponError"></div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
            <div class="payment">
                <h3>Payment</h3>
                <div class="w-cnt" style="position: relative;">
                	<div class="remove-from-cart tvv" data-pid="<?=$prop->id?>" data-cid="<?=$cid?>">
                		Remove From Cart <i class="icon-close"></i>
                	</div>
                    <h3 class="payment-error"></h3>

                    <div class="w-list-cart">
                    	<?php
                    	$zeroDue = '';
                    	if($finalPrice == '0' || $checkoutAmount == '0')
                    	{
                    	   $zeroDue = ' zeroDue';
                    	}
                    	?>
                        <div class="carts <?=$zeroDue;?>">
                        	<div id="autopopulate">
                        		<span class="fauxCheckbox"><b>Use Address on File</span>
                        	</div>
                            <form name="checkout" id="checkout-modal-form" class="material paymentForm">
                	    		<input type="hidden" name="item" id="checkout-item" value="<?=$checkoutItem?>" />
                	    		<input type="hidden" name="cartID" value="<?=$_COOKIE['gpx-cart']?>">
                	    		<input type="hidden" name="amount" id="checkout-amount" value="<?=$checkoutAmount?>" />
                	    		<input type="hidden" name="paymentID" id="checkout-paymentID" class="paymentID" value="" />
                	    		<input type="hidden" name="simpleCheckout" value="true" />
                                <input type="hidden" name="couponDiscount" value="<?=$couponDiscount?>">
                                <?php
                                if(!empty($indCartOCCreditUsed))
                                {
                                ?>
                                <input type="hidden" name="ownerCreditCoupon" value="<?=array_sum($indCartOCCreditUsed)?>">
                            	<?php
                                }
                                ?>
                                <input type="hidden" name="paid" value="<?=$checkoutAmount?>">
                                <ul>
                                    <li><img src="<?php echo get_template_directory_uri(); ?>/images/payment.png" alt="logo" width="" height=""></li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Street Address *" name="billing_address" id="billing_address" autocomplete="off" required>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="City *" name="billing_city" id="billing_city" autocomplete="off" required>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="State *" name="billing_state" id="c" autocomplete="off" required>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Post / Zip Code *" name="billing_zip" id="billing_zip" autocomplete="off" required>
                                        </div>
                                    </li>
                                    <li>
                                    <?php
                                    $countries = array
                                    (
                                        'US' => 'United States',
                                        'AF' => 'Afghanistan',
                                        'AX' => 'Aland Islands',
                                        'AL' => 'Albania',
                                        'DZ' => 'Algeria',
                                        'AS' => 'American Samoa',
                                        'AD' => 'Andorra',
                                        'AO' => 'Angola',
                                        'AI' => 'Anguilla',
                                        'AQ' => 'Antarctica',
                                        'AG' => 'Antigua And Barbuda',
                                        'AR' => 'Argentina',
                                        'AM' => 'Armenia',
                                        'AW' => 'Aruba',
                                        'AU' => 'Australia',
                                        'AT' => 'Austria',
                                        'AZ' => 'Azerbaijan',
                                        'BS' => 'Bahamas',
                                        'BH' => 'Bahrain',
                                        'BD' => 'Bangladesh',
                                        'BB' => 'Barbados',
                                        'BY' => 'Belarus',
                                        'BE' => 'Belgium',
                                        'BZ' => 'Belize',
                                        'BJ' => 'Benin',
                                        'BM' => 'Bermuda',
                                        'BT' => 'Bhutan',
                                        'BO' => 'Bolivia',
                                        'BA' => 'Bosnia And Herzegovina',
                                        'BW' => 'Botswana',
                                        'BV' => 'Bouvet Island',
                                        'BR' => 'Brazil',
                                        'IO' => 'British Indian Ocean Territory',
                                        'BN' => 'Brunei Darussalam',
                                        'BG' => 'Bulgaria',
                                        'BF' => 'Burkina Faso',
                                        'BI' => 'Burundi',
                                        'KH' => 'Cambodia',
                                        'CM' => 'Cameroon',
                                        'CA' => 'Canada',
                                        'CV' => 'Cape Verde',
                                        'KY' => 'Cayman Islands',
                                        'CF' => 'Central African Republic',
                                        'TD' => 'Chad',
                                        'CL' => 'Chile',
                                        'CN' => 'China',
                                        'CX' => 'Christmas Island',
                                        'CC' => 'Cocos (Keeling) Islands',
                                        'CO' => 'Colombia',
                                        'KM' => 'Comoros',
                                        'CG' => 'Congo',
                                        'CD' => 'Congo, Democratic Republic',
                                        'CK' => 'Cook Islands',
                                        'CR' => 'Costa Rica',
                                        'CI' => 'Cote D\'Ivoire',
                                        'HR' => 'Croatia',
                                        'CU' => 'Cuba',
                                        'CY' => 'Cyprus',
                                        'CZ' => 'Czech Republic',
                                        'DK' => 'Denmark',
                                        'DJ' => 'Djibouti',
                                        'DM' => 'Dominica',
                                        'DO' => 'Dominican Republic',
                                        'EC' => 'Ecuador',
                                        'EG' => 'Egypt',
                                        'SV' => 'El Salvador',
                                        'GQ' => 'Equatorial Guinea',
                                        'ER' => 'Eritrea',
                                        'EE' => 'Estonia',
                                        'ET' => 'Ethiopia',
                                        'FK' => 'Falkland Islands (Malvinas)',
                                        'FO' => 'Faroe Islands',
                                        'FJ' => 'Fiji',
                                        'FI' => 'Finland',
                                        'FR' => 'France',
                                        'GF' => 'French Guiana',
                                        'PF' => 'French Polynesia',
                                        'TF' => 'French Southern Territories',
                                        'GA' => 'Gabon',
                                        'GM' => 'Gambia',
                                        'GE' => 'Georgia',
                                        'DE' => 'Germany',
                                        'GH' => 'Ghana',
                                        'GI' => 'Gibraltar',
                                        'GR' => 'Greece',
                                        'GL' => 'Greenland',
                                        'GD' => 'Grenada',
                                        'GP' => 'Guadeloupe',
                                        'GU' => 'Guam',
                                        'GT' => 'Guatemala',
                                        'GG' => 'Guernsey',
                                        'GN' => 'Guinea',
                                        'GW' => 'Guinea-Bissau',
                                        'GY' => 'Guyana',
                                        'HT' => 'Haiti',
                                        'HM' => 'Heard Island & Mcdonald Islands',
                                        'VA' => 'Holy See (Vatican City State)',
                                        'HN' => 'Honduras',
                                        'HK' => 'Hong Kong',
                                        'HU' => 'Hungary',
                                        'IS' => 'Iceland',
                                        'IN' => 'India',
                                        'ID' => 'Indonesia',
                                        'IR' => 'Iran, Islamic Republic Of',
                                        'IQ' => 'Iraq',
                                        'IE' => 'Ireland',
                                        'IM' => 'Isle Of Man',
                                        'IL' => 'Israel',
                                        'IT' => 'Italy',
                                        'JM' => 'Jamaica',
                                        'JP' => 'Japan',
                                        'JE' => 'Jersey',
                                        'JO' => 'Jordan',
                                        'KZ' => 'Kazakhstan',
                                        'KE' => 'Kenya',
                                        'KI' => 'Kiribati',
                                        'KR' => 'Korea',
                                        'KW' => 'Kuwait',
                                        'KG' => 'Kyrgyzstan',
                                        'LA' => 'Lao People\'s Democratic Republic',
                                        'LV' => 'Latvia',
                                        'LB' => 'Lebanon',
                                        'LS' => 'Lesotho',
                                        'LR' => 'Liberia',
                                        'LY' => 'Libyan Arab Jamahiriya',
                                        'LI' => 'Liechtenstein',
                                        'LT' => 'Lithuania',
                                        'LU' => 'Luxembourg',
                                        'MO' => 'Macao',
                                        'MK' => 'Macedonia',
                                        'MG' => 'Madagascar',
                                        'MW' => 'Malawi',
                                        'MY' => 'Malaysia',
                                        'MV' => 'Maldives',
                                        'ML' => 'Mali',
                                        'MT' => 'Malta',
                                        'MH' => 'Marshall Islands',
                                        'MQ' => 'Martinique',
                                        'MR' => 'Mauritania',
                                        'MU' => 'Mauritius',
                                        'YT' => 'Mayotte',
                                        'MX' => 'Mexico',
                                        'FM' => 'Micronesia, Federated States Of',
                                        'MD' => 'Moldova',
                                        'MC' => 'Monaco',
                                        'MN' => 'Mongolia',
                                        'ME' => 'Montenegro',
                                        'MS' => 'Montserrat',
                                        'MA' => 'Morocco',
                                        'MZ' => 'Mozambique',
                                        'MM' => 'Myanmar',
                                        'NA' => 'Namibia',
                                        'NR' => 'Nauru',
                                        'NP' => 'Nepal',
                                        'NL' => 'Netherlands',
                                        'AN' => 'Netherlands Antilles',
                                        'NC' => 'New Caledonia',
                                        'NZ' => 'New Zealand',
                                        'NI' => 'Nicaragua',
                                        'NE' => 'Niger',
                                        'NG' => 'Nigeria',
                                        'NU' => 'Niue',
                                        'NF' => 'Norfolk Island',
                                        'MP' => 'Northern Mariana Islands',
                                        'NO' => 'Norway',
                                        'OM' => 'Oman',
                                        'PK' => 'Pakistan',
                                        'PW' => 'Palau',
                                        'PS' => 'Palestinian Territory, Occupied',
                                        'PA' => 'Panama',
                                        'PG' => 'Papua New Guinea',
                                        'PY' => 'Paraguay',
                                        'PE' => 'Peru',
                                        'PH' => 'Philippines',
                                        'PN' => 'Pitcairn',
                                        'PL' => 'Poland',
                                        'PT' => 'Portugal',
                                        'PR' => 'Puerto Rico',
                                        'QA' => 'Qatar',
                                        'RE' => 'Reunion',
                                        'RO' => 'Romania',
                                        'RU' => 'Russian Federation',
                                        'RW' => 'Rwanda',
                                        'BL' => 'Saint Barthelemy',
                                        'SH' => 'Saint Helena',
                                        'KN' => 'Saint Kitts And Nevis',
                                        'LC' => 'Saint Lucia',
                                        'MF' => 'Saint Martin',
                                        'PM' => 'Saint Pierre And Miquelon',
                                        'VC' => 'Saint Vincent And Grenadines',
                                        'WS' => 'Samoa',
                                        'SM' => 'San Marino',
                                        'ST' => 'Sao Tome And Principe',
                                        'SA' => 'Saudi Arabia',
                                        'SN' => 'Senegal',
                                        'RS' => 'Serbia',
                                        'SC' => 'Seychelles',
                                        'SL' => 'Sierra Leone',
                                        'SG' => 'Singapore',
                                        'SK' => 'Slovakia',
                                        'SI' => 'Slovenia',
                                        'SB' => 'Solomon Islands',
                                        'SO' => 'Somalia',
                                        'ZA' => 'South Africa',
                                        'GS' => 'South Georgia And Sandwich Isl.',
                                        'ES' => 'Spain',
                                        'LK' => 'Sri Lanka',
                                        'SD' => 'Sudan',
                                        'SR' => 'Suriname',
                                        'SJ' => 'Svalbard And Jan Mayen',
                                        'SZ' => 'Swaziland',
                                        'SE' => 'Sweden',
                                        'CH' => 'Switzerland',
                                        'SY' => 'Syrian Arab Republic',
                                        'TW' => 'Taiwan',
                                        'TJ' => 'Tajikistan',
                                        'TZ' => 'Tanzania',
                                        'TH' => 'Thailand',
                                        'TL' => 'Timor-Leste',
                                        'TG' => 'Togo',
                                        'TK' => 'Tokelau',
                                        'TO' => 'Tonga',
                                        'TT' => 'Trinidad And Tobago',
                                        'TN' => 'Tunisia',
                                        'TR' => 'Turkey',
                                        'TM' => 'Turkmenistan',
                                        'TC' => 'Turks And Caicos Islands',
                                        'TV' => 'Tuvalu',
                                        'UG' => 'Uganda',
                                        'UA' => 'Ukraine',
                                        'AE' => 'United Arab Emirates',
                                        'GB' => 'United Kingdom',
                                        'UM' => 'United States Outlying Islands',
                                        'UY' => 'Uruguay',
                                        'UZ' => 'Uzbekistan',
                                        'VU' => 'Vanuatu',
                                        'VE' => 'Venezuela',
                                        'VN' => 'Viet Nam',
                                        'VG' => 'Virgin Islands, British',
                                        'VI' => 'Virgin Islands, U.S.',
                                        'WF' => 'Wallis And Futuna',
                                        'EH' => 'Western Sahara',
                                        'YE' => 'Yemen',
                                        'ZM' => 'Zambia',
                                        'ZW' => 'Zimbabwe',
                                    );
                                    ?>
                                        <div class="ginput_container">
                                            <select name="biling_country" placeholder="Country *">
                                            	<?php
                                            	foreach($countries as $countryKey=>$countryValue)
                                            	{
                                            	?>
                                            	<option><?=$countryValue?></option>
                                            	<?php
                                            	}
                                            	?>
                                            </select>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Email *" name="billing_email" id="billing_email" autocomplete="off" required >
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Cardholder Name" name="billing_cardholder" id="billing_cardholder" autocomplete="off" required>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="Cardholder Number *" name="billing_number" id="billing_number" autocomplete="off" required>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container">
                                            <input type="text" placeholder="CVV *" name="billing_ccv" autocomplete="off" required>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ginput_container ginput_date">
                                            <p>Expiration Date</p>
                                            <div class="selects">
                                                <select name="billing_month" placeholder="Month">
                                                <?php
                                                for($i = 1; $i<=12; $i++)
                                                {
                                                ?>
                                                <option value="<?=$i?>"><?=sprintf('%02d', $i)?>
                                                <?php
                                                }
                                                ?>
                                                </select>
                                                <select name="billing_year" placeholder="Year">
                                                <?php
                                                $minYear = date('Y');
                                                for($i = $minYear; $i<=$minYear+20; $i++)
                                                {
                                                ?>
                                                <option value="<?=$i?>"><?=$i?>
                                                <?php
                                                }
                                                ?>
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
                         <?php
                         if(!empty($extensionFee))
                         {
                             ?>
                        <li>
                            <div class="result">
								<p>Credit Extension Fee:
								 <?php
								 if(isset($extensionSlash) && !empty($extensionSlash))
                                {
                                ?>
                                    <span style="text-deocoration: line-through;">$<?=$extensionSlash?></span>
                                <?php
                                }
                                ?>
								$<?=$extensionFee?>

								</p>
                            </div>
                        </li>
                        <?php
                            }
                            if(isset($GuestFeeAmount) && $GuestFeeAmount)
                            {
                            ?>
                                <li>
                                	<div class="result">
                                		<p>Guest Fee:
                                		<?php
                                		if(isset($gfSlash) && !empty($gfSlash))
                                		{
                                		?>
                                		<span style="text-deocoration: line-through;">$<?=$gfSlash?></span>
                                		<?php
                                		}
                                		?>
                                		$<?=$GuestFeeAmount?>
                                		</p>
                                	</div>
                                </li>
                        <?php
                            }
                            if(isset($LateDepositFeeAmount) && $LateDepositFeeAmount)
                            {
                            ?>
                                <li>
                                	<div class="result">
                                		<p>Late Deposit Fee:
                                		<?php
                                		if(isset($ldfSlash) && !empty($ldfSlash))
                                		{
                                		?>
                                		<span style="text-deocoration: line-through;">$<?=$ldfSlash?></span>
                                		<?php
                                		}
                                		?>
                                		$<?=$LateDepositFeeAmount?>
                                		</p>
                                	</div>
                                </li>
                        <?php
                            }
                            if(!empty($couponDiscount))
                            {
                                ?>
                        <li>
                            <div class="result">
                                <p>
                                    Discount: $<?=$couponDiscount?> <i class="fa fa-times-circle" id="removeCoupon" aria-hidden="true" title="Remove Discount"  data-type="<?=!empty($distinctOwner) ? 'occoupon' : 'coupon'?>"></i>
								</p>
                            </div>
                        </li>
                        <?php
                            }
                        ?>
                        <li>
                            <div class="result total">
                                <p>Total: $<?=$checkoutAmount?></p>
                            </div>
                        </li>
                        <li>
                            <div class="message">
                                <p>This charge will show on your credit card statement as <strong>Grand Pacific Exchange</strong></p>
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
                        <a href="<?php echo site_url(); ?>/booking-path-confirmation/" class="dgt-btn btn-next submit-payment" id="next-3" data-id="booking-4">Pay & Confirm</a>
                    </div>
                </div>
                <p>
                <?php
            	if(isset($atts['terms']) && !empty($atts['terms']))
            	    echo '<p>'.$atts['terms'].'</p>';
            	?>
                </p>
            </div>
        </div>
    </div>
</section>
<div id="flexbooking" style="display: none;">
	<p>Grand Pacific Exchange offers the option of purchasing Flex Booking for an additional fee of $<?=$fbFee?> paid
	   concurrently when booking an Exchange provided that the Exchange is made more than 45 days from the Check-In Date.
	   If purchased, Flex Booking covers you in the event you are not able to utilize your reserved vacation and need to
	   request cancellation of your Exchange. Provided your cancellation is submitted more than forty-five (45) days prior
	   to the Confirmed Exchange Check-In date, under Flex Booking (i) the original Exchange Credit will be returned to the
	   your GPX Account and will expire two (2) years from the original Exchange Credit start date; and (ii) the Exchange and
	   any Upgrade Fees paid for the Confirmed Exchange will be refunded in the form of a coupon code valid for one year that
	   may be applied towards your next Exchange or Rental Booking. If at the time of the new booking, the cost exceeds the
	   amount of the coupon code, the GPX Member must pay the incremental increase. Flex Booking may be purchased for any Exchange.
	   No monetary refunds are distributed for cancellations at any time.</p>
	<p>Flex Booking cannot be used to cancel a Confirmed Exchange and then re-book the same Resort Week as either a Rental Week or
	   Additional Benefit.</p>
	<p>Flex Booking is optional. Members who decline Flex Booking in connection with a Confirmed Exchange will forfeit their Exchange
	   Fee upon cancellation of a Confirmed Exchange, which includes any change in dates, unit type, vacation area or Resorts. Flex
	   Booking is not available for Rental Weeks including, without limitation, special or promotional offers.</p>
</div>
<?php endif; ?>
