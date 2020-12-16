<?php 

  extract($static);
  extract($data);
  include 'header.php';
  
?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>GPX Dashboard</h3>
              </div>

              <div class="title_right text-right">
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
                <div class="x_panel">
                <!-- 
                  <div class="x_title">
                    <h2>First Dashboard<small>ToDo:</small></h2>
                    <ul class="nav navbar-right panel_toolbox">
                      <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                      </li>
                      <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
                      </li>
                      <li><a class="close-link"><i class="fa fa-close"></i></a>
                      </li>
                    </ul>
                    <div class="clearfix"></div>
                  </div>
                 -->
                  <div class="x_content">
                    <div class="row">
                      <div class="col-sm-3 mail_list_column">
						<h5>Alert Splash Message
						<?php 
                      	$alertActive = get_option('gpx_alert_msg_active');
                      	if($alertActive == 1)
                      	{
                      	?>
                      	<span class="badge btn-success" id="activeAlertMsg" data-active="0">Active</span>
                      	<?php 
                      	}
                      	else 
                      	{
                      	?>
                      	<span class="badge btn-danger" id="activeAlertMsg" data-active="1">Inactive</span>
                      	<?php 
                      	}
                      	?></h5>
                      </div>
                      <div class="col-xs-9 col-sm-4">
                      	<textarea class="form-control " id="alertMsg" disabled><?=get_option('gpx_alert_msg_msg')?></textarea>
                      </div>
                      <div class="col-xs-3">
                      	<i class="fa fa-pencil" id="editAlertMsg" aria-hidden="true"></i><br>
                      	<button class="btn btn-primary" id="alertSubmit" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                      <div class="col-sm-3 mail_list_column">
						<h5>Booking Disabled
						<?php 
                      	$bookingDisabledActive = get_option('gpx_booking_disabled_active');
                      	if($bookingDisabledActive == 1)
                      	{
                      	?>
                      	<span class="badge btn-success" id="activeBookingDisabledMsg" data-active="0">Active</span>
                      	<?php 
                      	}
                      	else 
                      	{
                      	?>
                      	<span class="badge btn-danger" id="activeBookingDisabledMsg" data-active="1">Inactive</span>
                      	<?php 
                      	}
                      	?></h5>
                      </div>
                      <div class="col-xs-9 col-sm-4">
                      	<textarea class="form-control " id="bookingDisabledMsg" disabled><?=get_option('gpx_booking_disabled_msg')?></textarea>
                      </div>
                      <div class="col-xs-3">
                      	<i class="fa fa-pencil" id="editBookingDisabledMsg" aria-hidden="true"></i><br>
                      	<button class="btn btn-primary" id="bookingDisabledSubmit" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                		<h5>Hold Limit Time (in hours)</h5>
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                          	<input class="form-control " id="holdLimitTime" disabled value="<?=get_option('gpx_hold_limt_time')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil" id="editHoldLimitTime" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" id="editHoldLimitTimeSubmit" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                		<h5>Hold Timer Text</h5>
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                          	<input class="form-control " id="holdLimitTimer" disabled value="<?=get_option('gpx_hold_limt_timer')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil" id="editHoldLimitTimer" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" id="editHoldLimitTimerSubmit" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                    		<h5>Hold Limit Error Message</h5>
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                          	<input class="form-control " id="holdLimitMessage" disabled value="<?=get_option('gpx_hold_error_message')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil" id="editHoldLimitMessage" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" id="editHoldLimitSubmit" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                    		<h5>Minimum Rental Price</h5>
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                          	<input class="form-control " id="minRentalFee" disabled value="<?=get_option('gpx_min_rental_fee')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil" id="editminRentalFee" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" id="minRentalFeeSubmit" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                    		<h5>Flex Booking Fee</h5>
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                          	<input class="form-control " id="fbFee" disabled value="<?=get_option('gpx_fb_fee')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil" id="edifbFee" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" id="fbFeeSubmit" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                    		<h5>Late Deposit Fee</h5>
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                          	<input class="form-control " id="lateDepositFee" disabled value="<?=get_option('gpx_late_deposit_fee')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil" id="editLateDepositFee" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" id="lateDepositFeeSubmit" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                    		<h5>Late Deposit Fee within 7 Days</h5>
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                          	<input class="form-control " id="lateDepositFeeWithin" disabled value="<?=get_option('gpx_late_deposit_fee_within')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil" id="editLateDepositFeeWithin" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" id="lateDepositFeeSubmitWithin" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                    		<h5>Extension Fee</h5>
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                          	<input class="form-control " id="ExtensionFee" disabled value="<?=get_option('gpx_extension_fee')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil" id="editExtensionFee" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" id="ExtensionFeeSubmit" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                    		<h5>Exchange Fee</h5>
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                          	<input class="form-control " id="exchangeFee" disabled value="<?=get_option('gpx_exchange_fee')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil" id="editExchagneFee" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" id="exchageFeeSubmit" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                    		<h5>Global Guest Fees
        						<?php 
                              	$gfActive = get_option('gpx_global_guest_fees');
                              	if($gfActive == 1)
                              	{
                              	?>
                              	<span class="badge btn-success" id="activeGF" data-active="0">Active</span>
                              	<?php 
                              	}
                              	else 
                              	{
                              	?>
                              	<span class="badge btn-danger" id="activeGF" data-active="1">Inactive</span>
                              	<?php 
                              	}
                              	?>
                           </h5>
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                          	<input class="form-control " id="gfAmount" disabled value="<?=get_option('gpx_gf_amount')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil" id="editGFAmount" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" id="gfAmountSubmit" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <?php 
                        $ttBonusClass = 'btn-default';
                        $ttBonusActive = '';
                        $ttBonus = get_option('gpx_tax_transaction_bonus');
                        if($ttBonus == 1)
                        {
                            $ttBonusClass = 'btn-success active';
                            $ttBonusActive = 'checked';
                        }
                        
                        $ttExchangeClass = 'btn-default';
                        $ttExchangeActive = '';
                        $ttExchange = get_option('gpx_tax_transaction_exchange');
                        if($ttExchange == 1)
                        {
                            $ttExchangeClass = 'btn-success active';
                            $ttExchangeActive = 'checked';
                        }
                        
                    ?>
                    <div class="row" style="margin-top: 20px;">
                    	<div class="col-xs-12">
                    		<h5>Tax Transation Types
                        		<div class="btn-group cg-btn-group-checkbox" data-toggle="buttons">
                        			<label class="btn <?=$ttBonusClass?>"><input type="checkbox" name="taxTransactionType" value="bonus" class="tax-transaction-type" <?=$ttBonusActive?>>Bonus/Rental</label>
                        			<label class="btn <?=$ttExchangeClass?>"><input type="checkbox" name="taxTransactionType" value="exchange" class="tax-transaction-type" <?=$ttExchangeActive?>>Exchange</label>
                        		</div>
                    		</h5>
                    	</div>
                    </div>
                    <?php 
                    $userforrole = wp_get_current_user();
                    if ( in_array( 'administrator_plus', (array) $userforrole->roles ) ) {
                    ?>
                    <div class="row" style="margin-top: 20px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                    		<h5>DAE Web Services</h5>
                    		<button class="btn btn-primary" id="ping-dae">Ping DAE</button>
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                    		<label for="dae_ws_host">Host</label>
                          	<input class="form-control input-dae-ws" id="dae_ws_host" name="dae_ws_host" disabled value="<?=get_option('dae_ws_host')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil edit-dae-ws" data-input="dae_ws_host" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" class="submit-dae-ws" data-input="dae_ws_host" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 10px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                    		<label for="dae_ws_action">Action</label>
                          	<input class="form-control input-dae-ws" id="dae_ws_action" name="dae_ws_action" disabled value="<?=get_option('dae_ws_action')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil edit-dae-ws" data-input="dae_ws_action" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" class="submit-dae-ws" data-input="dae_ws_action" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 10px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                    		<label for="dae_ws_authid">Auth ID</label>
                          	<input class="form-control input-dae-ws" id="dae_ws_authid" name="dae_ws_authid" disabled value="<?=get_option('dae_ws_authid')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil edit-dae-ws" data-input="dae_ws_authid" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" class="submit-dae-ws" data-input="dae_ws_authid" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 10px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                    		<label for="dae_ws_memberno">DAE Member No</label>
                          	<input class="form-control input-dae-ws" id="dae_ws_memberno" name="dae_ws_memberno" disabled value="<?=get_option('dae_ws_memberno')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil edit-dae-ws" data-input="dae_ws_memberno" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" class="submit-dae-ws" data-input="dae_ws_memberno" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <div class="row" style="margin-top: 10px;">
                    	<div class="col-xs-12 col-sm-4 col-md-3">
                    	</div>
                    	<div class="col-xs-9 col-sm-4">
                    		<label for="dae_ws_mode">Mode</label>
                          	<input class="form-control input-dae-ws" id="dae_ws_mode" name="dae_ws_mode" disabled value="<?=get_option('dae_ws_mode')?>" />
                        </div>
                        <div class="col-xs-3">
                          	<i class="fa fa-pencil edit-dae-ws" data-input="dae_ws_mode" aria-hidden="true"></i><br>
                          	<button class="btn btn-primary" class="submit-dae-ws" data-input="dae_ws_mode" style="display:none;">Submit</button>
                      </div>
                    </div>
                    <?php 
                    }
                    ?>                    
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>	
	  <?php include 'footer.php';?>