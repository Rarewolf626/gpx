<?php

use Illuminate\Support\Arr;

extract($static);
extract($data);
include $dir . '/templates/admin/header.php';

?>
<div class="right_col" role="main">
	<div class="update-nag"></div>
	<div class="">

		<div class="page-title">
			<div class="title_left">
				<h3>Add Special</h3>
			</div>
		</div>

		<div class="clearfix"></div>

		<div class="row">
			<div class="col-md-8 col-sm-12 col-md-offset-2">
				<form method="post" action="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=promos_add" id="promo-add" data-parsley-validate
					class="form-horizontal form-label-left">
					<div class="well">
    					<div class="form-group">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="master">Master Special
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<select name="master" id="master"
    								class="form-control col-md-7 col-xs-12">
    								<option></option>
    								<?php
    								foreach($special_masters as $special_master)
    								{
    								?>
    								<option value="<?=$special_master->id?>"><?=$special_master->Name?></option>
    								<?php
    								}
    								?>
    							</select>
    						</div>
    					</div>
    					<div class="form-group">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="bookingFunnel">Booking Funnel <span class="required">*</span>
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<select name="bookingFunnel" id="bookingFunnel"
    								class="form-control col-md-7 col-xs-12">
    								<option>No</option>
    								<option>Yes</option>
    							</select>
    						</div>
    					</div>
    					<div class="form-group">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="Name">Name <span class="required">*</span>
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="text" id="Name" name="Name" required="required"
    								class="form-control col-md-7 col-xs-12 alphanumeric">
    						</div>
    					</div>
    					<div class="form-group">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="Name" id="promoorcoupon">Coupon Code <span class="required">*</span>
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="text" id="Slug" name="Slug" required="required"
    								class="form-control col-md-7 col-xs-12">
    						</div>
    					</div>
    					<div class="form-group promo two4one-hide" style="display: none;">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="Name" id="metaBeforeLogin">Hide Before Login </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<select name="metaBeforeLogin" id="metaBeforeLogin"
    								class="form-control col-md-7 col-xs-12">
    								<option>Yes</option>
    								<option>No</option>
    							</select>
    						</div>
    					</div>
    					<div class="form-group promo two4one-hide" sytle="display:none;">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaGACode">Google Analytics ID </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="text" id="metaGACode" name="metaGACode"
    								class="form-control col-md-7 col-xs-12">
    						</div>
    					</div>
    					<div class="form-group promo two4one-hide" sytle="display:none;">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaIcon">Slash Through Icon </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="text" id="metaIcon" name="metaIcon"
    								class="form-control fapicker col-md-7 col-xs-12">
    						</div>
    					</div>
    					<div class="form-group promo two4one-hide" style="display: none;">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaDesc">Promo Tagging Verbage </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<textarea id="metaDesc" name="metaDesc"
    								class="form-control col-md-7 col-xs-12"></textarea>
    						</div>
    					</div>
    					<div class="form-group promo two4one-hide" sytle="display:none;">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaHighlight">Card Highlighting </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<select id="metaHighlight" name="metaHighlight"
    								class="form-control col-md-7 col-xs-12">
    								<option></option>
                              		<?php
                                $activeopts = array(
                                    'Highlighted',
                                    'Prevent Highlighting'
                                );
                                foreach ($activeopts as $optvalue) {
                                    $selected = '';
                                    echo '<option value="' . $optvalue . '"' . $selected . '>' . $optvalue . '</option>';
                                }
                                ?>
                                </select>
    						</div>
    					</div>
    					<div class="form-group promo two4one-hide" sytle="display:none;">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaSlash">Slash Through </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<select id="metaSlash" name="metaSlash"
    								class="form-control col-md-7 col-xs-12">
    								<option></option>
                              		<?php
                                $activeopts = array(
                                    'Default',
                                    'Force Slash',
                                    'No Slash'
                                );
                                foreach ($activeopts as $optvalue) {
                                    $selected = '';
                                    echo '<option value="' . $optvalue . '"' . $selected . '>' . $optvalue . '</option>';
                                }
                                ?>
                                </select>
    						</div>
    					</div>
    					<div class="form-group promo two4one-hide" sytle="display:none;">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="showIndex">Show on index </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<select id="showIndex" name="showIndex"
    								class="form-control col-md-7 col-xs-12">
    								<option></option>
                              		<?php
                                $activeopts = array(
                                    'Yes',
                                    'No'
                                );
                                foreach ($activeopts as $optvalue) {
                                    $selected = '';
                                    echo '<option value="' . $optvalue . '"' . $selected . '>' . $optvalue . '</option>';
                                }
                                ?>
                                </select>
    						</div>
    					</div>
    					<div class="form-group promo two4one-hide" sytle="display:none;">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="availability">Promo Availability </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<select id="availability" name="availability"
    								class="form-control col-md-7 col-xs-12">
    								<option></option>
                              		<?php
                                $activeopts = array(
                                    'Site-wide',
                                    'Landing Page'
                                );
                                foreach ($activeopts as $optvalue) {
                                    $selected = '';
                                    echo '<option value="' . $optvalue . '"' . $selected . '>' . $optvalue . '</option>';
                                }
                                ?>
                                </select>
    						</div>
    					</div>
    					<div class="form-group">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaType"><span class="pcSwitchType">Coupon</span> Type <span
    							class="required">*</span> </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<select id="metaType" name="metaType" required="required"
    								class="form-control col-md-7 col-xs-12">
    								<option></option>
                              		<?php
                                $activeopts = array(
                                    array(
                                        'type' => 'all',
                                        'val' => 'Pct Off'
                                    ),
                                    array(
                                        'type' => 'all',
                                        'val' => 'Dollar Off'
                                    ),
                                    array(
                                        'type' => 'all',
                                        'val' => 'Set Amt'
                                    ),
                                    array(
                                        'type' => 'Coupon',
                                        'val' => 'BOGO'
                                    ),
                                    array(
                                        'type' => 'Coupon',
                                        'val' => 'BOGOH'
                                    ),
                                    array(
                                        'type' => 'Coupon',
                                        'val' => 'Auto Create Coupon Template -- Pct Off'
                                    ),
                                    array(
                                        'type' => 'Coupon',
                                        'val' => 'Auto Create Coupon Template -- Dollar Off'
                                    ),
                                    array(
                                        'type' => 'Coupon',
                                        'val' => 'Auto Create Coupon Template -- Set Amt'
                                    ),
                                    array(
                                        'type' => 'all',
                                        'val' => '2 for 1 Deposit'
                                    )
                                );
                                foreach ($activeopts as $optvalue) {
                                    echo '<option value="' . $optvalue['val'] . '">' . $optvalue['val'] . '</option>';
                                }
                                ?>
                              </select>
    						</div>
    					</div>
    					<div class="form-group two4one-hide" id="acCoupon">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="acCouponField">Auto Create Coupon </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="checkbox" id="acCouponField" name="acCoupon" value="1">
    						</div>
    					</div>
    					<div class="form-group two4one-hide" id="ctSelectRow">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="ctSelect">Coupon Template </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<select name="couponTemplate" id="couponTemplate" class="form-control col-md-7 col-xs-12">
    								<option value="">Select Template</option>
    							</select>
    						</div>
    					</div>
    					<div class="form-group two4one-hide">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="Amount"><span class="pcSwitchType">Coupon</span> Amount <span
    							class="required">*</span> </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="text" id="Amount" name="Amount" required="required"
    								class="form-control col-md-7 col-xs-12" value="">
    						</div>
    					</div>
    					<div class="form-group two4one-hide">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaMinWeekPrice">Week Minimum Cost</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="text" id="metaMinWeekPrice" name="metaMinWeekPrice"
    								class="form-control col-md-7 col-xs-12" value="">
    						</div>
    					</div>
    					<div class="form-group coupon two4one-hide">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaMaxValue">Max Value </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="text" id="metaMaxValue" name="metaMaxValue"
    								class="form-control col-md-7 col-xs-12" value="">
    						</div>
    					</div>
    					<div class="form-group two4one-hide">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaTransactionType">Transaction Type <span class="required">*</span>
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<select name="metaTransactionType" required
    								class="form-control col-md-7 col-xs-12" id="metaTransactionType">
    								<option value="any">Any</option>
    								<option value="ExchangeWeek">Exchange</option>
    								<option value="BonusWeek">Rental/Bonus</option>
    								<option value="upsell">Upsell Only</option>

    							</select>
    						</div>
    					</div>
    					<div class="form-group upsell two4one-hide" style="display: none;">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaUpsellOptions">Upsell Options </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="checkbox">
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="metaUpsellOptions[]"
                                            value="CPO"
                                        />
                                        CPO (Flex Booking Fee)
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="metaUpsellOptions[]"
                                            value="Upgrade"
                                        />
                                        Upgrade
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="metaUpsellOptions[]"
                                            value="Guest Fees"
                                        />
                                        Guest Fees
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="metaUpsellOptions[]"
                                            value="Extension Fees"
                                        />
                                        Extension Fees
                                    </label>
                                </div>
    						</div>
    					</div>
    					<input type="hidden" name="metaUseExc" id="metaUseExc" value="">
    					<div class="usage-exclusion-group promoaddusage">
    						<div class="clone-group well">
            					<div class="form-group">
            						<label class="control-label col-md-3 col-sm-3 col-xs-12"
            							for="metaUsage">Usage </label>
            						<div class="col-md-6 col-sm-6 col-xs-11">
            							<select name="metaUsage[]" id="switchmetausage"
            								class="form-control col-md-7 col-xs-12 switchmetausage">
            								<option value="any">Any</option>
            								<option value="region">Region</option>
            								<option value="resort">Resort</option>
            								<option value="dae">DAE Inventory</option>
            								<option value="customer">Customer</option>
            							</select>
            						</div>
            						<div class="col-xs-1 col-sm-offset-2 add-new">
            							<i class="fa fa-plus" aria-hidden="true"></i>
            						</div>
            					</div>
            					<div id="usage-add" class="usage_exclude usage-add" data-type="usage"></div>
            					<div class="row">
                					<div class="ue-blackout col-xs-12 col-sm-6 col-sm-offset-3">
                						<a href="#" class="addBlackoutDates">Add Blackout Dates</a>
                					</div>
                					<div class="ue-blackout-fg clear">
                    					<div class="form-group">
                    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
                    							for="metaFlashStart">Blackout Start Date </label>
                    						<div class="col-md-6 col-sm-6 col-xs-11">
                    							<input type="text"  name="metaResortBlackoutStart[]"
                    								class="form-control rbodatepicker col-md-7 col-xs-12" value="">
                    						</div>
                    						<div class="col-xs-1"><a href="#" class="remove-blackout"><i class="fa fa-trash" aria-hidden="true"></i></a></div>
                    					</div>
                    					<div class="form-group">
                    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
                    							for="metaFlashStart">Blackout End Date </label>
                    						<div class="col-md-6 col-sm-6 col-xs-11">
                    							<input type="text"  name="metaResortBlackoutEnd[]"
                    								class="form-control rbodatepicker col-md-7 col-xs-12" value="">
                    						</div>
                    					</div>
                					</div>
                					<div class="boClone"></div>
            					</div>
            					<div class="row">
            						<div class="ue-travel col-xs-12 col-sm-6 col-sm-offset-3">
                						<a href="#" class="addTravelDates">Add Specific Travel Dates</a>
                					</div>
                					<div class="ue-travel-fg clear">
                    					<div class="form-group">
                    						<label class="control-label col-md-3 col-sm-3 col-xs-12">Travel Start Date </label>
                    						<div class="col-md-6 col-sm-6 col-xs-12">
                    							<input type="text"  name="metaResortTravelStart[]"class="form-control rbodatepicker col-md-7 col-xs-12" value="">
                    						</div>
                    					</div>
                    					<div class="form-group">
                    						<label class="control-label col-md-3 col-sm-3 col-xs-12">Travel End Date </label>
                    						<div class="col-md-6 col-sm-6 col-xs-12">
                    							<input type="text"  name="metaResortTravelEnd[]" class="form-control rbodatepicker col-md-7 col-xs-12" value="">
                    						</div>
                    					</div>
                					</div>
            					</div>
        					</div>
        					<div class="clone-group well">
            					<div class="form-group two4one-hide">
            						<label class="control-label col-md-3 col-sm-3 col-xs-12"
            							for="metaExclusions">Exclusions </label>
            						<div class="col-md-6 col-sm-6 col-xs-11">
            							<select name="metaExclusions[]" id="switchexclusions"
            								class="form-control col-md-7 col-xs-12 switchmetaexclusions">
            								<option></option>
            								<option value="region">Region</option>
            								<option value="resort">Resort</option>
            								<option value="home-resort">Home Resort</option>
            								<option value="dae">DAE Inventory</option>
            							</select>
            						</div>
            						<div class="col-xs-1 col-sm-offset-2 add-new">
            							<i class="fa fa-plus" aria-hidden="true"></i>
        							</div>
            					</div>
            					<div id="exclusion-add" class="usage_exclude exclusion-add" data-type="exclude"></div>
        					</div>
    					</div>
    					<div class="well promo exclusiveWeeksBox">
    						<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="exclusiveWeeks">Exclusive Weeks
        						</label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<textarea class="form-control" name="exclusiveWeeks" id="exclusiveWeeks"></textarea>
        							<span style="font-size: 10px;">Week ID separated by comma</span>
        						</div>
        					</div>
    					</div>
    					<div class="form-group two4one-hide">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaStacking">Allow Stacking Discount <span class="required">*</span>
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<select name="metaStacking" id="metaStacking"
    								class="form-control col-md-7 col-xs-12" required="required">
    								<option>No</option>
    								<option>Yes</option>
    							</select>
    						</div>
    					</div>
    					<div class="form-group coupon two4one-hide">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaSingleUse">Single Use Per Owner
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
    							<select name="metaSingleUse" id="metaSingleUse" class="form-control col-md-7 col-xs-12">
                            		<option>No</option>
    								<option>Yes</option>
                            	</select>
                            </div>
                         </div>
    					<div class="form-group coupon two4one-hide">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaMaxCoupon">Max Number of Coupons </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="text" id="metaMaxCoupon" name="metaMaxCoupon"
    								class="form-control col-md-7 col-xs-12" value="">
    						</div>
    					</div>
    					<div class="well">
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="StartDate">Start Date <span class="dateTextSwitch">(Available for Viewing)</span> <span
        							class="required">*</span>
        						</label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" id="StartDate" name="StartDate"
        								required="required"
        								class="form-control datepicker col-md-7 col-xs-12" value="">
        						</div>
        					</div>
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="EndDate">End Date <span class="dateTextSwitch">(Available for Viewing)</span> <span
        							class="required">*</span>
        						</label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" id="EndDate" name="EndDate"
        								required="required"
        								class="form-control datepicker col-md-7 col-xs-12" value="">
        						</div>
        					</div>
    					</div>
    					<div class="well two4one-hide">
        					<div class="form-group two4one-hide">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaFlashStart">Flash Sale Start Time </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" id="metaFlashStart" name="metaFlashStart"
        								class="form-control timepicker col-md-7 col-xs-12" value="">
        						</div>
        					</div>
        					<div class="form-group two4one-hide">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaFlashEnd">Flash Sale End Time </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" id="metaFlashEnd" name="metaFlashEnd"
        								class="form-control timepicker col-md-7 col-xs-12" value="">
        						</div>
        					</div>
    					</div>
    					<div class="well two4one-hide">
        					<div class="form-group two4one-hide">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaBookStartDate">Book Start Date </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" id="metaBookStartDate"
        								name="metaBookStartDate"
        								class="form-control datepicker col-md-7 col-xs-12">
        						</div>
        					</div>
        					<div class="form-group two4one-hide">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaBookEndDate">Book End Date </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" id="metaBookEndDate" name="metaBookEndDate"
        								class="form-control datepicker col-md-7 col-xs-12">
        						</div>
        					</div>
    					</div>
    					<div class="well two4one-hide">
        					<div class="form-group two4one-hide">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaTravelStartDate">Travel Start Date </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" id="metaTravelStartDate"
        								name="metaTravelStartDate"
        								class="form-control datepicker col-md-7 col-xs-12">
        						</div>
        					</div>
        					<div class="form-group two4one-hide">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaTravelEndDate">Travel End Date </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" id="metaTravelEndDate"
        								name="metaTravelEndDate"
        								class="form-control datepicker col-md-7 col-xs-12">
        						</div>
        					</div>
    					</div>
    					<div class="blackout-clone-gp">
    						<div class="blackout-clone well two4one-hide">
            					<div class="form-group two4one-hide">
            						<label class="control-label col-md-3 col-sm-3 col-xs-12"
            							for="metaBlackoutStart">Blackout Start Date </label>
            						<div class="col-md-6 col-sm-6 col-xs-12">
            							<input type="text" id="metaBlackoutStart"
            								name="metaBlackoutStart[]"
            								class="form-control datepicker col-md-7 col-xs-12">
            						</div>
            						<div class="col-xs-1 col-sm-offset-2 blackout-clone-btn">
                						<i class="fa fa-plus" aria-hidden="true"></i>
            						</div>
            					</div>
            					<div class="form-group two4one-hide">
            						<label class="control-label col-md-3 col-sm-3 col-xs-12"
            							for="metaBlackoutEnd">Blackout End Date </label>
            						<div class="col-md-6 col-sm-6 col-xs-12">
            							<input type="text" id="metaBlackoutEnd"
            								name="metaBlackoutEnd[]"
            								class="form-control datepicker col-md-7 col-xs-12">
            						</div>
            					</div>
        					</div>
    					</div>
    					<div class="form-group two4one-hide">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaLeadTimeMin">Lead Time Minimum (days) </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="text" id="metaLeadTimeMin" name="metaLeadTimeMin"
    								class="form-control col-md-7 col-xs-12">
    						</div>
    					</div>
    					<div class="form-group two4one-hide">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaLeadTimeMax">Lead Time Maximum (days) </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="text" id="metaLeadTimeMax" name="metaLeadTimeMax"
    								class="form-control col-md-7 col-xs-12">
    						</div>
    					</div>
    					<div class="form-group promo two4one-show" sytle="display:none;">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="metaTerms">Terms & Conditions </label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<textarea id="metaTerms" name="metaTerms"
    								class="form-control col-md-7 col-xs-12"></textarea>
    						</div>
    					</div>
    					<div class="form-group two4one-hide" id="actcFG">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="Active">Auto Coupon Template TC's
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<textarea name="actc" id="actc" style="width: 100%"></textarea>
    						</div>
    					</div>
    					<div class="form-group">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="Active">Active <span class="required">*</span>
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<select id="Active" name="Active" required="required"
    								class="form-control col-md-7 col-xs-12">
    								<option></option>
                              		<?php
                                $activeopts = array(
                                    '1' => 'Yes',
                                    '0' => 'No'
                                );
                                foreach ($activeopts as $optkey => $optvalue) {
                                    $selected = '';
                                    echo '<option value="' . $optkey . '"' . $selected . '>' . $optvalue . '</option>';
                                }
                                ?>
                              </select>
    						</div>
    					</div>
    					<div class="ln_solid"></div>
    					<div class="form-group">
    						<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
    							<a href="" class="btn btn-danger cancel-return">Cancel</a>
    							<button type="submit" class="btn btn-success" id="submit-btn">
    								Submit <i class="fa fa-circle-o-notch fa-spin fa-fw"
    									style="display: none;"></i>
    							</button>
    						</div>
    					</div>
					</div>
				</form>

			</div>
		</div>
	</div>
</div>
<?php include $dir.'/templates/admin/footer.php';?>
