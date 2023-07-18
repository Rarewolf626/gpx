<?php

  extract($static);
  extract($data);


  include $dir.'/templates/admin/header.php';

  if(isset($_GET['txn_debug']))
  {
      echo '<pre>'.print_r($transaction, true).'</pre>';
  }
  $transaction->Paid = gpx_currency($transaction->Paid, true,false, false);
?>
		<input type="hidden" id="transactionID" value="<?=$transaction->transactionID?>" />
        <div class="right_col <?=$isadmin?>" role="main">
          <div class="" id="admin-modal-content">

            <div class="page-title">
              <div class="title_left">
                <h3>Transaction <?=$transaction->transactionID?><br /><small><?=date('m/d/Y h:i a', strtotime($transaction->datetime))?></small></h3>
              </div>

              <div class="title_right">

                <div class="col-sm-8 col-xs-12 form-group pull-right top_search">
                  <div class="input-group modal-btn-group cancel-hide">
                  <?php
                  if(!empty($transaction->fullcancel) && $transaction->fullcancel == 1)
                  {

                      foreach($transaction->cancelled as $tc)
                      {
                          if(isset($tc->date))
                          {
                              $tcdate = $tc->date;
                          }
                          if(isset($tc->name))
                          {
                              $tcname = $tc->name;
                          }
                      }
                      ?>
                  <h3>Cancelled<br />
                  <small><?=date('m/d/Y', strtotime($tcdate))?> by <?=$tcname?></small></h3>
                  <?php
                  }
                  else
                  {
                  ?>
                  	<button class="btn btn-danger feeupdate" id="cancel-booking" data-transaction="<?=$transaction->transactionID?>" data-type="Cancel Booking">Cancel Booking Request</button>
                  <?php
                  }
                  ?>
                  	<div>
                  			<div class="updateoptions agenthide cancelshow">
								<div class="row">
									<div class="col-xs-12 col-sm-6" style="display: none;">
										<strong>Transaction Amount: $<?=$transaction->Paid?></strong>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-12">
										<?php
										$disableedit = '';
										if(!empty($transaction->fullcancel) && $transaction->fullcancel == 1)
										{
										    $fullcancel = 1;
// 										    $disableedit = 'disabled';
										?>
										<h3 style="text-align: right;">
										<?php
											if(!empty($transaction->cancelled->coupon))
											{

											?>
											<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=promos_deccouponsedit&id=<?=$transaction->cancelled->coupon?>" title="View Coupon" target="_blank"><i class="fa fa-external-link"></i></a>
											<?php
											}

											foreach($transaction->cancelled as $tc)
											{
											    if(isset($tc->date))
											    {
											        $tcdate = $tc->date;
											    }
											    if(isset($tc->name))
											    {
											        $tcname = $tc->name;
											    }
											}
											?>
											Cancelled
										</h3>
										<p style="text-align:right;"><?=date('m/d/Y', strtotime($tcdate))?> by <?=$tcname?></p>

										<?php
										}
										if($refundAmt < $transaction->Paid)
										{
										    $corr = 'Cancel';
										    $amt = '';
										    if($fullcancel)
										    {
										        $corr = 'Refund';
										        $amt = $transaction->Paid - $refundAmt;
										    }
										?>
                                    	<div class="dropdown input-group row">
                                			<input type="hidden" class="refundType" value="">
                                        	<input type="text" data-type="cancel" class="form-control feeamt" data-transaction="<?=$transaction->transactionID?>" id="transactionCancelFee" value="<?=$transaction->Paid?>" style="display: none;"/>
                                            <div class="input-group-btn show">
                                                <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><?=$corr?> Transaction <span class="caret"></span></button>
                                                <ul class="dropdown-menu dropdown-menu-right" role="menu" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(76.3667px, 38px, 0px);">
                                                    <li><a class="dropdown-item submit-on-change cancel-booking-choose" data-amt="<?=$amt?>" data-type="credit" href="#">Credit Owner</a></li>
                                                    <?php
                                                    //if this is a trade partner then we don't need this button
                                                    //we also don't want to display it to call center
                                                    if(empty($partner) && $isadmin == 'admin')
                                                    {
                                                    ?>
                                                    <li><a class="dropdown-item submit-on-change agenthide cancel-booking-choose" data-amt="<?=$amt?>" data-type="refund" href="#">Refund Credit Card</a></li>
                                                	<?php
                                                    }
                                                	?>
                                                </ul>
                                            </div>
                                        </div>
										<div class="">
											<input type="submit" class="btn btn-primary cancel-transaction" data-cancel="<?=$transaction->transactionID?>" value="Submit" />
										</div>
                                        <?php
										}
                                        ?>
									</div>
									<div class="col-xs-12 col-md-5" style="display: none;">
										<ul>
                    						<li>Exchange/Rental Fee: <strong>$<?=number_format($transaction->actWeekPrice, 2)?></strong></li>
                    						<li>Flex Booking: <strong><?=$transaction->CPO?></strong></li>
                    						<?php
                    						if(!empty($transaction->actcpoFee) && $transaction->actcpoFee > 0)
                    						{
                    						?>
                    						<li>Flex Booking Fee: <strong>$<?=number_format($transaction->actcpoFee, 2)?></strong></li>
                    						<?php
                    						}
                    						?>
                    						<?php
                    						if(!empty($transaction->actguestFee) && $transaction->actguestFee > 0)
                    						{
                    						?>
                    						<li>Guest Fee: <strong>$<?=number_format($transaction->actWeekPrice, 2)?></strong></li>
                    						<?php
                    						}
                    						if(!empty($transaction->actupgradeFee) && $transaction->actupgradeFee > 0)
                    						{
                    						?>
                    						<li>Upgrade Fee: <strong>$<?=number_format($transaction->actupgradeFee, 2)?></strong></li>
                    						<?php
                    						}
                    						?>
                    						<?php
                    						if(!empty($transaction->actextensionFee) && $transaction->actextensionFee > 0)
                    						{
                    						?>
                    						<li>Credit Extension Fee: <strong>$<?=number_format($transaction->actextensionFee, 2)?></strong></li>
                    						<?php
                    						}
                    						if(!empty($transaction->lateDepositFee))
                    						{
                    						?>
                    						<li>Late Deposit Fee: <strong>$<?=number_format($transaction->lateDepositFee, 2)?></strong></li>
                    						<?php
                    						}
                    						/*
                    						if(!empty($transaction->discount))
                    						{
                    						?>
                    						<li>Discount: <strong>$<?=$transaction->discount?></strong>
                    						<?php
                    						}
                    						if(!empty($transaction->couponDiscount))
                    						{
                    						?>
                    						<li>Coupon Amount: <strong>$<?=$transaction->couponDiscount?></strong>
                    						<?php
                    						}
                    						?>
                    						<?php
                    						*/
                    						if(!empty($transaction->acttax))
                    						{
                    						?>
                    						<li>Tax Charged: <strong>$<?=number_format($transaction->acttax, 2)?></strong>
                    						<?php
                    						}
                    						?>
                    						<li>Paid: <strong>$<?=number_format($transaction->Paid, 2)?></strong></li>

                    						<?php
                    					    if(
                    					        (!empty($transaction->cancelled) && !empty($transaction->cancelled->refunded))
                					          || (isset($refunded))
                    					        )
                    					    {
                    					        $refundAmt = '';
                    					        if(isset($transaction->cancelled->refunded))
                    					        {
                    					            if(isset($transaction->cancelled->coupon))
                    					            {
                    					                $icon = 'fa fa-gift';
                    					            }
                    					            elseif($transaction->cancelled->refunded > 0)
                    					            {
                    					                $icon = 'fa fa-credit-card';
                    					            }
                    					            else
                    					            {
                    					                $icon = '';
                    					            }
                    					            $refundAmt = '$'.number_format($transaction->cancelled->refunded, 2).' <i class="'.$icon.'"></i>';
                    					        }
                    					        else
                    					        {
                    					            foreach($refunded as $rK=>$rV)
                    					            {

                    					                if($rK == 'credit')
                    					                {
                    					                    $icon = 'fa fa-gift';
                    					                }
                    					                elseif($refundAmt == '$0.00' || $refundAmt == '0' || $rV == '0')
                    					                {
                    					                    $icon = '';
                    					                }
                    					                else
                    					                {
                    					                    $icon = 'fa fa-credit-card';
                    					                }
                    					                $refundAmt .= '$'.$rv.' <i class="'.$icon.'"></i>&nbsp;&nbsp;';
                    					            }
                    					        }
                    					    ?>
                    					    <li>Refunded: <strong><?=$refundAmt?></strong>
                    					    <?php
                    					    }
                    					    ?>
                    					</ul>
									</div>

                                </div>
							</div>
                  		</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="clearfix"></div>
            <div class="update-nag"></div>
			<div class="row">

				<div class="col-sm-12 col-md-3">
				  <div class="well">
					<h3>Guest Info</h3>
					<ul>
						<li>Member Number: <strong><?=$transaction->MemberNumber?></strong></li>
						<li>Member Name: <strong><?=$transaction->MemberName?></strong></li>
						<?php
						//$guestName = '<div data-name="'.$transaction->GuestName.'" class="updateGuestName">';
						$name = explode(" ", $transaction->GuestName);
						$email = '';
						if(isset($transaction->Email))
						{
						    $email = $transaction->Email;
						}
						$guestName = '<div data-name="'.$transaction->GuestName.'" class="updateGuestName"';
						$guestName .= ' data-transaction="'.$transaction->transactionID.'"';
						$guestName .= ' data-fname="'.$name[0].'"';
						$guestName .= ' data-lname="'.$name[1].'"';
						$guestName .= ' data-email="'.$email.'"';
						$guestName .= ' data-adults="'.$transaction->Adults.'"';
						$guestName .= ' data-children="'.$transaction->Children.'"';
						$guestName .= ' data-owner="'.$transaction->Owner.'"';
						$guestName .= ' data-href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=transactions_view&id='.$transaction->transactionID.'"';
						$guestName .= '>';
						//$guestName .= '<input type="text" class="form-control guestNameInput'.$transaction->transactionID.'" name="updateGuest" data-transaction="'.$transaction->transactionID.'" value="'.$transaction->GuestName.'" style="display: none" />';
                        $guestName .= '<a href="#"><i class="fa fa-edit"></i> <span class="guestName guestName'.$transaction->transactionID.'">'.$transaction->GuestName.'</span></a>';
                        $guestName .= '</div>';
                        ?>
						<li class="guestNameTD">Guest Name: <strong><?=$guestName?></strong></li>
						<li>Adults: <strong><?=$transaction->Adults?></strong></li>
						<li>Children: <strong><?=$transaction->Children?></strong></li>
						<li>Special Request: <strong><?=$transaction->specialRequest?></strong></li>
					</ul>
				  </div>
				</div>
				<div class="col-sm-12 col-md-3">
					<div class=" well">
    					<h3>Resort / Room Info</h3>
    					<ul>
    					<?php
    					/*
    					?>
    						<li>Owned By: <strong><?=$transaction->Owner?></strong>
    						<li>Resort ID: <strong><?=$transaction->ResortID?></strong></li>
    					<?php
    					*/
    					$checkin = '';
    					if($transaction->checkIn != '')
    					{
    					    $checkin = date('m/d/Y', strtotime($transaction->checkIn));
    					}
    					?>
    						<li>Ref No: <strong><?=$transaction->weekId?></strong></li>
    						<li>Resort Name: <strong><?=$transaction->ResortName?></strong></li>
    						<li>Size: <strong><?=$transaction->Size?></strong></li>
    						<li>Check In: <strong><?=$checkin?></strong></li>
    						<li>Nights: <strong><?=$transaction->noNights?></strong></li>
    					</ul>
					</div>
				<?php
				if(isset($transaction->depositDetails))
				{
				?>
				  <div class=" well" style="margin-top: 10px;">
						<h3>Deposit</h3>
						<ul>
    						<li>Ref Number: <strong><?=$transaction->depositDetails->id?></strong></li>
    						<li>Resort Name: <strong><?=$transaction->depositDetails->resort_name?></strong></li>
    						<li>Deposit Year: <strong><?=$transaction->depositDetails->deposit_year?></strong></li>
    						<li>Unit: <strong><?=$transaction->depositDetails->unitweek?></strong></li>
						</ul>

				  </div>
				<?php
				}
				?>
				</div>

				<div class="col-sm-12 col-md-3 hide-input">
				  <div class="well">
					    <div id="feeupdate" class="modal fade" role="dialog">
                          <div class="modal-dialog">

                            <!-- Modal content-->
                            <div class="modal-content">
                              <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title"><span id="updateType"></span></h4>
                              </div>
                              <div class="modal-body">

                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-default fee-close" data-dismiss="modal">Close</button>
                              </div>
                            </div>

                          </div>
                        </div>
					<h3>Fees</h3>
					<ul>
						<li class="modal-btn-group">
						<?php
						/*
						 * @todo: Line items might be adjusted.  Display the correct price when they are adjusted.
						 */
						?>
							<a href="#" class="feeupdate agenthide <?=$disableedit?>" data-type="Update Exchange/Rental Fee" data-toggle="modal" data-target="#feeupdate"><i class="fa fa-pencil"></i></a>
							<div class="updateoptions agenthide">
								<div class="row">
									<div class="col-xs-12 col-sm-6">
										<strong>Exchange/Rental Fee: $<?=number_format($transaction->actWeekPrice, 2)?></strong>
									</div>
								</div>
                                <div class="dropdown input-group row" style="margin-top: 20px;">
                                	<input type="hidden" class="refundType" value="">
                                	<input type="text" data-type="erFee" class="form-control feeamt" id="erUpdate" value="<?=number_format($transaction->actWeekPrice, 2)?>" />
                                    <div class="input-group-btn show">
                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Action <span class="caret"></span></button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(76.3667px, 38px, 0px);">
                                            <li><a class="dropdown-item submit-on-change" data-type="credit" href="#">Credit Owner</a></li>
                                            <li><a class="dropdown-item submit-on-change" data-type="refund" href="#">Refund Credit Card</a></li>
                                            <?php
                                            /*
                                            ?>
                                            <li class="divider"></li>
                                            <li><a class="dropdown-item  submit-on-change" href="#">Charge Owner Credit</a></li>
                                            <li><a class="dropdown-item submit-on-change" href="#">Charge Credit Card</a></li>
                                            <?php
                                            */
                                            ?>
                                        </ul>
                                    </div>
									<div class="">
										<input type="submit" class="btn btn-primary update-transaction-fee" data-cancel="<?=$transaction->transactionID?>" value="Submit" />
									</div>
                                </div>
							</div>
						Exchange/Rental Fee: <strong>$<?=number_format($transaction->actWeekPrice, 2)?></strong></li>
						<?php
						$cpoFee = 0;
						if(!empty($transaction->actcpoFee))
						{
						    $cpoFee = str_replace("$", "", $transaction->actcpoFee);
						    $cpoPre = '$';
						    if($cpoFee < 0)
						    {
						        $cpoPre = '-$'.$cpoFee*-1;
						    }
						    else
						    {
						        $cpoPre = '$'.number_format($cpoFee, 2);
						    }
						}
						if($cpoFee > 0)
						{
						?>
						<li class="modal-btn-group">
							<a href="#" class="feeupdate agenthide <?=$disableedit?>" data-type="Update CPO Fee" data-toggle="modal" data-target="#feeupdate"><i class="fa fa-pencil"></i></a>
							<div class="updateoptions agenthide">
								<div class="row">
									<div class="col-xs-12 col-sm-6">
										<strong>Flex Booking Fee: <?=$cpoPre?></strong>
									</div>
								</div>
                                <div class="dropdown input-group row" style="margin-top: 20px;">
                                	<input type="hidden" class="refundType" value="">
                                	<input type="text" data-type="cpofee" class="form-control feeamt"  value="<?=$cpoFee?>" />
                                    <div class="input-group-btn show">
                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Action <span class="caret"></span></button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(76.3667px, 38px, 0px);">
                                            <li><a class="dropdown-item submit-on-change" data-type="credit" href="#">Credit Owner</a></li>
                                            <li><a class="dropdown-item submit-on-change" data-type="refund" href="#">Refund Credit Card</a></li>
											<?php
                                            /*
                                            ?>
                                            <li class="divider"></li>
                                            <li><a class="dropdown-item  submit-on-change" href="#">Charge Owner Credit</a></li>
                                            <li><a class="dropdown-item submit-on-change" href="#">Charge Credit Card</a></li>
                                            <?php
                                            */
                                            ?>
                                            </ul>
                                    </div>
									<div class="">
										<input type="submit" class="btn btn-primary update-transaction-fee" data-cancel="<?=$transaction->transactionID?>" value="Submit" />
									</div>
                                </div>

							</div>
							Flex Booking Fee: <strong><?=$cpoPre?></strong>
						</li>
						<?php
						}
						?>
						<?php
						if(!empty($transaction->actupgradeFee))
						{
						    $upgradefee = 0;
						    if(!empty($transaction->actupgradeFee))
						    {
						        $upgradefee = number_format($transaction->actupgradeFee, 2);
						        if($upgradefee < 0)
						        {
						            $uPre = '-$'.$upgradefee*-1;
						        }
						        else
						        {
						            $uPre = '$'.number_format($upgradefee, 2);
						        }
						    }

						?>
						<li class="modal-btn-group">
							<a href="#" class="feeupdate agenthide <?=$disableedit?>" data-type="Update Discount" data-toggle="modal" data-target="#feeupdate"><i class="fa fa-pencil"></i></a>
							<div class="updateoptions agenthide">
								<div class="row">
									<div class="col-xs-12 col-sm-6">
										<strong>Upgrade Fee: <?=$uPre?></strong>
									</div>
								</div>
                                <div class="dropdown input-group row" style="margin-top: 20px;">
                                	<input type="text" data-type="upgradefee" class="form-control feeamt"  value="<?=$upgradefee?>" />
                                    <div class="input-group-btn show">
                                		<input type="hidden" class="refundType" value="">
                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Action <span class="caret"></span></button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(76.3667px, 38px, 0px);">
                                            <li><a class="dropdown-item submit-on-change " data-type="credit" href="#">Credit Owner</a></li>
                                            <li><a class="dropdown-item submit-on-change" data-type="refund" href="#">Refund Credit Card</a></li>

											<?php
                                            /*
                                            ?>
                                            <li class="divider"></li>
                                            <li><a class="dropdown-item  submit-on-change" href="#">Charge Owner Credit</a></li>
                                            <li><a class="dropdown-item submit-on-change" href="#">Charge Credit Card</a></li>
                                            <?php
                                            */
                                            ?>

										</ul>
                                    </div>
									<div class="">
										<input type="submit" class="btn btn-primary update-transaction-fee" data-cancel="<?=$transaction->transactionID?>" value="Submit" />
									</div>
                                </div>
							</div>

							Upgrade Fee: <strong><?=$uPre?></strong></li>
						<?php
						}
						if(!empty($transaction->actguestFee))
						{
						    $guestFeeAmount = 0;
						    if(!empty($transaction->actguestFee))
						    {
						        $guestFeeAmount = number_format($transaction->actguestFee, 2);
						        if($creditextensionfee < 0)
						        {
						            $gPre = '-$'.$guestFeeAmount*-1;
						        }
						        else
						        {
						            $gPre = '$'.number_format($guestFeeAmount, 2);
						        }
						    }

						?>
						<li class="modal-btn-group">
							<a href="#" class="feeupdate agenthide <?=$disableedit?>" data-type="Update Discount" data-toggle="modal" data-target="#feeupdate"><i class="fa fa-pencil"></i></a>
							<div class="updateoptions agenthide">
								<div class="row">
									<div class="col-xs-12 col-sm-6">
										<strong>Guest Fee: <?=$gPre?></strong>
									</div>
								</div>
                                <div class="dropdown input-group row" style="margin-top: 20px;">
                                	<input type="text" data-type="guestfeeamount" class="form-control feeamt"  value="<?=$guestFeeAmount?>" />
                                    <div class="input-group-btn show">
                                		<input type="hidden" class="refundType" value="">
                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Action <span class="caret"></span></button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(76.3667px, 38px, 0px);">
                                            <li><a class="dropdown-item submit-on-change " data-type="credit" href="#">Credit Owner</a></li>
                                            <li><a class="dropdown-item submit-on-change" data-type="refund" href="#">Refund Credit Card</a></li>

											<?php
                                            /*
                                            ?>
                                            <li class="divider"></li>
                                            <li><a class="dropdown-item  submit-on-change" href="#">Charge Owner Credit</a></li>
                                            <li><a class="dropdown-item submit-on-change" href="#">Charge Credit Card</a></li>
                                            <?php
                                            */
                                            ?>

										</ul>
                                    </div>
									<div class="">
										<input type="submit" class="btn btn-primary update-transaction-fee" data-cancel="<?=$transaction->transactionID?>" value="Submit" />
									</div>
                                </div>
							</div>

							Guest Fee: <strong><?=$gPre?></strong></li>
						<?php
						}
						if(!empty($transaction->lateDepositFee))
						{
						    $lateDepositFee = 0;
						    if(!empty($transaction->lateDepositFee))
						    {
						        $lateDepositFee = $transaction->lateDepositFee;
						        if($creditextensionfee < 0)
						        {
						            $ldPre = '-$'.$lateDepositFee*-1;
						        }
						        else
						        {
						            $ldPre = '$'.number_format($lateDepositFee, 2);
						        }
						    }

						?>
						<li class="modal-btn-group">
							<a href="#" class="feeupdate agenthide <?=$disableedit?>" data-type="Update Discount" data-toggle="modal" data-target="#feeupdate"><i class="fa fa-pencil"></i></a>
							<div class="updateoptions agenthide">
								<div class="row">
									<div class="col-xs-12 col-sm-6">
										<strong>Late Deposit Fee: <?=$uPre?></strong>
									</div>
								</div>
                                <div class="dropdown input-group row" style="margin-top: 20px;">
                                	<input type="text" data-type="latedepositfee" class="form-control feeamt"  value="<?=$lateDepositFee?>" />
                                    <div class="input-group-btn show">
                                		<input type="hidden" class="refundType" value="">
                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Action <span class="caret"></span></button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(76.3667px, 38px, 0px);">
                                            <li><a class="dropdown-item submit-on-change " data-type="credit" href="#">Credit Owner</a></li>
                                            <li><a class="dropdown-item submit-on-change" data-type="refund" href="#">Refund Credit Card</a></li>

											<?php
                                            /*
                                            ?>
                                            <li class="divider"></li>
                                            <li><a class="dropdown-item  submit-on-change" href="#">Charge Owner Credit</a></li>
                                            <li><a class="dropdown-item submit-on-change" href="#">Charge Credit Card</a></li>
                                            <?php
                                            */
                                            ?>

										</ul>
                                    </div>
									<div class="">
										<input type="submit" class="btn btn-primary update-transaction-fee" data-cancel="<?=$transaction->transactionID?>" value="Submit" />
									</div>
                                </div>
							</div>

							Late Deposit Fee: <strong><?=$ldPre?></strong></li>
						<?php
						}
						if(!empty($transaction->actextensionFee))
						{
						    $creditextensionfee = 0;
						    if(!empty($transaction->actextensionFee))
						    {
						        $creditextensionfee = number_format($transaction->actextensionFee, 2);
						        if($creditextensionfee < 0)
						        {
						            $cePre = '-$'.$creditextensionfee*-1;
						        }
						        else
						        {
						            $cePre = '$'.number_format($creditextensionfee,2);
						        }
						    }

						?>
						<li class="modal-btn-group">
							<a href="#" class="feeupdate agenthide <?=$disableedit?>" data-type="Update Discount" data-toggle="modal" data-target="#feeupdate"><i class="fa fa-pencil"></i></a>
							<div class="updateoptions agenthide">
								<div class="row">
									<div class="col-xs-12 col-sm-6">
										<strong>Credit Exension Fee: <?=$cePre?></strong>
									</div>
								</div>
                                <div class="dropdown input-group row" style="margin-top: 20px;">
                                	<input type="text" data-type="creditextensionfee" class="form-control feeamt"  value="<?=$creditextensionfee?>" />
                                    <div class="input-group-btn show">
                                		<input type="hidden" class="refundType" value="">
                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Action <span class="caret"></span></button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(76.3667px, 38px, 0px);">
                                            <li><a class="dropdown-item submit-on-change " data-type="credit" href="#">Credit Owner</a></li>
                                            <li><a class="dropdown-item submit-on-change" data-type="refund" href="#">Refund Credit Card</a></li>

											<?php
                                            /*
                                            ?>
                                            <li class="divider"></li>
                                            <li><a class="dropdown-item  submit-on-change" href="#">Charge Owner Credit</a></li>
                                            <li><a class="dropdown-item submit-on-change" href="#">Charge Credit Card</a></li>
                                            <?php
                                            */
                                            ?>

										</ul>
                                    </div>
									<div class="">
										<input type="submit" class="btn btn-primary update-transaction-fee" data-cancel="<?=$transaction->transactionID?>" value="Submit" />
									</div>
                                </div>
							</div>

							Credit Extension Fee: <strong><?=$cePre?></strong></li>
						<?php
						}
						/*
						$discount = 0;
						$discountPre = "$";
						if(!empty($transaction->discount))
						{
						    $discount = str_replace('$', "", $transaction->discount);
						    if($discount < 0)
						    {
						        $discountPre = '-$'.$discount*-1;
						    }
						    else
						    {
						        $discountPre = '-$'.$discount;
						    }
						}
						?>
						<li class="modal-btn-group">
							<a href="#" class="feeupdate agenthide <?=$disableedit?>" data-type="Update Discount" data-toggle="modal" data-target="#feeupdate"><i class="fa fa-pencil" style="display: none;"></i></a>
							<div class="updateoptions agenthide">
								<div class="row">
									<div class="col-xs-12 col-sm-6">
										<strong>Discount: <?=$discountPre?></strong>
									</div>
								</div>
                                <div class="dropdown input-group row" style="margin-top: 20px;">
                                	<input type="text" data-type="discount" class="form-control feeamt"  value="<?=$discount?>" />
                                    <div class="input-group-btn show">
                                		<input type="hidden" class="refundType" value="">
                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Action <span class="caret"></span></button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(76.3667px, 38px, 0px);">
                                            <li><a class="dropdown-item submit-on-change " data-type="credit" href="#">Credit Owner</a></li>
                                            <li><a class="dropdown-item submit-on-change" data-type="refund" href="#">Refund Credit Card</a></li>

											<?php
                                            /*
                                            ?>
                                            <li class="divider"></li>
                                            <li><a class="dropdown-item  submit-on-change" href="#">Charge Owner Credit</a></li>
                                            <li><a class="dropdown-item submit-on-change" href="#">Charge Credit Card</a></li>
                                            <?php
                                            */
						/*
                                            ?>

										</ul>
                                    </div>
									<div class="">
										<input type="submit" class="btn btn-primary update-transaction-fee" data-cancel="<?=$transaction->transactionID?>" value="Submit" />
									</div>
                                </div>
							</div>
							Discount: <strong><?=$discountPre?></strong></li>

						<?php
						*/
						$couponDiscount = 0;
						$cdPre = '$';
						if(!empty($transaction->couponDiscount) || !empty($transaction->ownerCreditCouponAmount))
// 						if(!empty($transaction->couponDiscount))
						{
						    $transaction->couponDiscount = str_replace(",", "", $transaction->couponDiscount);
						    $amts[] = str_replace("$", "", $transaction->couponDiscount);
						    $amts[] = $transaction->ownerCreditCouponAmount;
						    $occ = $transaction->ownerCreditCouponAmount;
						    $camt = array_sum($amts);
						    $couponDiscount = str_replace("$", "", $camt);
						    if($couponDiscount < 0)
						    {
						        $cdPre = '-$'.$couponDiscount*-1;
						    }
						    else
						    {
						        $cdPre = '$'.number_format($couponDiscount, 2);
						    }
						?>
						<li class="modal-btn-group">
							<a href="#" class="feeupdate agenthide <?=$disableedit?>" data-type="Update Coupon Discount" data-toggle="modal" data-target="#feeupdate"><i class="fa fa-pencil" style="display: none;"></i></a>
							<div class="updateoptions agenthide">
								<div class="row">
									<div class="col-xs-12 col-sm-6">
										<strong>Coupon Discount: <?=$cdPre?></strong>
									</div>
								</div>
                                <div class="dropdown input-group row" style="margin-top: 20px;">

                                	<input type="hidden" class="refundType" value="">
                                	<input type="text" data-type="couponDiscount" class="form-control feeamt"  value="<?=$couponDiscount?>" />
                                    <div class="input-group-btn show">
                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Action <span class="caret"></span></button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(76.3667px, 38px, 0px);">
                                            <li><a class="dropdown-item submit-on-change " data-type="credit" href="#">Credit Owner</a></li>
                                            <li><a class="dropdown-item submit-on-change" data-type="refund" href="#">Refund Credit Card</a></li>

											<?php
                                            /*
                                            ?>
                                            <li class="divider"></li>
                                            <li><a class="dropdown-item  submit-on-change" href="#">Charge Owner Credit</a></li>
                                            <li><a class="dropdown-item submit-on-change" href="#">Charge Credit Card</a></li>
                                            <?php
                                            */
                                            ?>

                                        </ul>
                                    </div>
									<div class="">
										<input type="submit" class="btn btn-primary update-transaction-fee" data-cancel="<?=$transaction->transactionID?>" value="Submit" />
									</div>
                                </div>
							</div>
							Coupon Amount: <strong><?=$cdPre?> <?=$eocc = ($occ) ? " (MC: $".$occ.")" : ""?></strong>
						</li>
						<?php

						}
						if(!empty($transaction->acttax))
						{
						?>
						<li>Tax Charged: <strong>$<?=number_format($transaction->acttax, 2)?></strong>
						<?php
						}
						?>
						<li class="modal-btn-group">
						<?php
						/*
						?>
							<a href="#" class="feeupdate agenthide" data-type="Update Transaction Amount" data-toggle="modal" data-target="#feeupdate"><i class="fa fa-pencil"></i></a>
						<?php
						*/
						?>

							<div class="updateoptions agenthide">
								<div class="row">
									<div class="col-xs-12 col-sm-6">
										<strong>Transaction Amount: $<?=number_format($transaction->Paid, 2)?></strong>
									</div>
								</div>
								<div class="row" style="margin-top: 20px;">
									<div class="col-xs-12 col-md-7">

                                	<div class="dropdown input-group row">
                                		<input type="hidden" class="refundType" value="">
                                    	<input type="text" data-type="full" class="form-control feeamt" id="transactionFUllUpdate" value="<?=$transaction->Paid?>" />
                                        <div class="input-group-btn show">
                                            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Action <span class="caret"></span></button>
                                            <ul class="dropdown-menu dropdown-menu-right" role="menu" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(76.3667px, 38px, 0px);">
                                                <li><a class="dropdown-item submit-on-change " data-type="credit" href="#">Credit Owner</a></li>
                                                <li><a class="dropdown-item submit-on-change" data-type="refund" href="#">Refund Credit Card</a></li>

											<?php
                                            /*
                                            ?>
                                            <li class="divider"></li>
                                            <li><a class="dropdown-item  submit-on-change" href="#">Charge Owner Credit</a></li>
                                            <li><a class="dropdown-item submit-on-change" href="#">Charge Credit Card</a></li>
                                            <?php
                                            */
                                            ?>

                                            </ul>
                                        </div>
    									<div class="">
    										<input type="submit" class="btn btn-primary update-transaction-fee" data-cancel="<?=$transaction->transactionID?>" value="Submit" />
    									</div>
                                    </div>

									</div>
									<div class="col-xs-12 col-md-5">
										<ul>
                    						<li>Exchange/Rental Fee: <strong><?=gpx_currency($transaction->actWeekPrice)?></strong></li>
                    						<li>Flex Booking: <strong><?=gpx_currency($transaction->CPO)?></strong></li>
                    						<?php if(!empty($transaction->actcpoFee) && $transaction->actcpoFee > 0): ?>
                    						    <li>Flex Booking Fee: <strong><?=gpx_currency($transaction->actcpoFee)?></strong>
                    						<?php endif; ?>
                    						<?php
                    						if(!empty($transaction->actupgradeFee)): ?>
                    						<li>Upgrade Fee: <strong><?=gpx_currency($transaction->actupgradeFee)?></strong>
                    						<?php endif; ?>
                    						<?php if(!empty($transaction->actguestFee)): ?>
                    						<li>Guest Fee: <strong><?=gpx_currency($transaction->actguestFee)?></strong>
                    						<?php endif; ?>
                    						<?php if(!empty($transaction->lateDepositFee)): ?>
                    						<li>Late Deposit Fee: <strong><?=gpx_currency($transaction->lateDepositFee)?></strong>
                    						<?php endif; ?>
                    						<?php if(!empty($transaction->taxCharged)): ?>
                    						<li>Tax Charged: <strong><?=gpx_currency($transaction->taxCharged)?></strong>
                    						<?php endif; ?>
                    						<li>Paid: <strong><?=gpx_currency($transaction->Paid)?></strong></li>

                    						<?php
                    					    if( (!empty($transaction->cancelled) && !empty($transaction->cancelled->refunded)) || (isset($refunded)) ) {
                    					        $refundAmt = '';
                    					        if(isset($transaction->cancelled->refunded)) {

                    					            if(isset($transaction->cancelled->coupon)) {
                    					                $icon = 'fa fa-gift';
                    					            } elseif($transaction->cancelled->refunded > 0) {
                    					                $icon = 'fa fa-credit-card';
                    					            } else {
                    					                $icon = '';
                    					            }
                    					            $refundAmt = gpx_currency($transaction->cancelled->refunded).' <i class="'.$icon.'"></i>';
                    					        } else {
                    					            foreach($refunded as $rK=>$rV) {
                    					                if($rK == 'credit') {
                    					                    $icon = 'fa fa-gift';
                    					                } elseif ($refundAmt == '$0.00' || $refundAmt == '0' || $rV == '0') {
                    					                    $icon = '';
                    					                } else {
                    					                    $icon = 'fa fa-credit-card';
                    					                }
                    					                $refundAmt .= gpx_currency($rv).' <i class="'.$icon.'"></i>&nbsp;&nbsp;';
                    					            }
                    					        }
                    					    ?>
                    					    <li>Refunded: <strong><?=gpx_currency($refundAmt)?></strong>
                    					    <?php } ?>
                    					</ul>
									</div>

                                </div>
							</div>
						Paid: <strong><?=gpx_currency($transaction->Paid)?></strong></li>

                    						<?php
                    					    if(
                    					        (!empty($transaction->cancelled) && !empty($transaction->cancelled->refunded))
                					          || (isset($refunded))
                    					        )
                    					    {
                    					        $refundAmt = '';
                    					        if(isset($transaction->cancelled->refunded))
                    					        {
                    					            if(isset($transaction->cancelled->coupon))
                    					            {
                    					                $icon = 'fa fa-gift';
                    					            }
                    					            elseif($transaction->cancelled->refunded > 0)
                    					            {
                    					                $icon = 'fa fa-credit-card';
                    					            }
                    					            else
                    					            {
                    					                $icon = '';
                    					            }
                    					            $refundAmt = gpx_currency($transaction->cancelled->refunded).' <i class="'.$icon.'"></i>';
                    					        }
                    					        else
                    					        {
                    					            foreach($refunded as $rK=>$rV)
                    					            {
                    					                if($rK == 'credit')
                    					                {
                    					                    $icon = 'fa fa-gift';
                    					                }
                    					                elseif($refundAmt == '$0.00' || $refundAmt == '0' || $rV == '0')
                    					                {
                    					                    $icon = '';
                    					                }
                    					                else
                    					                {
                    					                    $icon = 'fa fa-credit-card';
                    					                }
                    					                $refundAmt .= gpx_currency($rV).' <i class="'.$icon.'"></i>&nbsp;&nbsp;';
                    					            }
                    					        }
                    					    ?>
                    					    <li>Refunded: <strong><?=$refundAmt?></strong>
                    					    <?php } ?>
					</ul>
				  </div>
				</div>
			</div>
         </div>
       </div>
              <div id="guest-details" class="modal fade" role="dialog">
              <div class="modal-dialog">
            	<form name="update-guest-details" id="update-guest-details" method="POST">
            		<input type="hidden" name="transactionID" value="<?=$transaction->transactionID?>">
                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Guest Details</h4>
                      </div>
                      <div class="modal-body" id="guest-details-info">
                      	<div  class="row">
                      		<div class="col-xs-12 col-sm-6">
                      			<h3 class="feOnly" style="display: none;">Edit Guest Details</h3>
                      		</div>
                      		<div class="col-xs-12 col-sm-6">
                      			<a href="#" class="btn btn-secondary remove-guest" data-id="<?=$transaction->transactionID?>" style="display: inline-block; margin-top: 16px;">Remove Guest</a>
                      		</div>
                      	</div>

                      	<div class="row" style="clear: both;">
                      		<?php
                      		$name = explode(" ", $transaction->GuestName);
                      		$fName = $name[0];
                      		$lName = $name[1];
                      		$email = '';
                      		if(isset($transaction->Email))
                      		{
                      		    $email = $transaction->Email;
                      		}
                      		$phone = '';
                      		if(isset($transaction->Phone))
                      		{
                      		    $phone = $transaction->Phone;
                      		}
                      		?>
                      		<div class="col-xs-12 col-xs-6">
                      			<div class="form-group">
                      				<label for="FirstName1">First Name</label>
                      				<input type="text" name="FirstName1" id="tFirstName1" class="form-control" value="<?=$fName?>">
                      			</div>
                      			<div class="form-group">
                      				<label for="LastName1">Last Name</label>
                      				<input type="text" name="LastName1" id="tLastName1" class="form-control" value="<?=$lName?>">
                      			</div>
                      			<div class="form-group">
                      				<label for="Email">Email</label>
                      				<input type="text" name="Email" id="tEmail" class="form-control" value="<?=$email?>">
                      			</div>
                      			<div class="form-group">
                      				<label for="Phone">Phone</label>
                      				<input type="text" name="Phone" id="tPhone" class="form-control" value="<?=$phone?>">
                      			</div>
                      		</div>
                      		<div class="col-xs-12 col-xs-6">
                      			<div class="form-group">
                      				<label for="Adults">Adults</label>
                      				<input type="text" name="Adults" id="tAdults" class="form-control" value="<?=$transaction->Adults?>">
                      			</div>
                      			<div class="form-group">
                      				<label for="Children">Children</label>
                      				<input type="text" name="Children" id="tChildren" class="form-control" value="<?=$transaction->Children?>">
                      			</div>
                      			<div class="form-group hide-agent">
                      				<label for="Owner">Owned By</label>
                      				<input type="text" name="Owner" id="Owner" class="form-control" value="<?=$transaction->Owner?>">
                      			</div>
                      		</div>
                      	</div>
                      	<div  class="feOnly row" style="display: none;">
                      		<div class="col-xs-12">
                      			<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=transactions_view&id=<?=$transaction->transactionID?>" data-transaction="<?=$transaction->transactionID?>" class="btn btn-default save-edit-transaction">Save</a>
                      		</div>
                      	</div>
                      </div>
                      <div class="modal-footer">
                      	<button type="submit" class="btn btn-success update-guests">Update</button>
                        <a href="#" class="btn btn-default" data-dismiss="modal">Close</a>
                      </div>
                    </div>
            	</form>
              </div>
            </div>
       <?php include $dir.'/templates/admin/footer.php';?>
