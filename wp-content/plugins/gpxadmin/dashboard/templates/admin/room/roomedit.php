<?php

extract($static);
extract($data);
include $dir.'/templates/admin/header.php';

$update_users = $update_users ?? []

?>
        <div class="right_col" role="main">
        <?php
        $shownag = '';
        if(!empty($message))
        {
            $shownag = 'style="display: block;"';
        }
        ?>
          <div class="update-nag" id="sucessmessage"></div>
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Edit Room </h3>
              </div>
            </div>
               <div class="title_right">
                <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search">
                <?php
                if($disabled == '')
                {
                ?>
                   <a href="#" class="btn btn-danger deleteWeek" data-id="<?=$room->record_id?>">Delete Week</a>
                <?php
                }
                //change disabled for
                $cuser = wp_get_current_user();
                if(in_array('gpx_admin', (array) $cuser->roles))
                {
                    $disabled = '';
                }
                ?>
                   <div class="well">
              			<ul>
                      <pre style="display: none;">
                        <?php print_r($room); ?>
                        <?php print_r($updateDets); ?>
                      </pre>

                      <?php if ($room->status != "Available") : ?>
                      <li class="red">Room Status: <?php echo $room->status; ?></li>
                      <?php else : ?>
                      <li>Room Status: <?php echo $room->status; ?></li>
                      <?php endif ?>
              				<li>Added: <?=date('m/d/Y', strtotime($room->create_date))?></li>
              				<li>By: <?=$update_users[$room->create_by] ?? ''?></li>
              				<li><a href="#" class="fulldetails" data-toggle="modal" data-target="#updateDets">See History</a></li>
              			</ul>
                        <div id="updateDets" class="modal fade" role="dialog">
                          <div class="modal-dialog">

                            <!-- Modal content-->
                            <div class="modal-content">
                              <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Room Update History</h4>
                              </div>
                              <div class="modal-body">
                              <ul>
                              <?php
                              foreach($updateDets as $dk=>$dv)
                              {
                              ?>
                              	<li>
                              		<a href="#" class="show-history"><?=$update_users[$dv->update_by]?> on <?=date('m/d/Y H:i:s', $dk)?></a>
                              		<div class="room-history">
                              			<div><strong>Item</strong></div>
                              			<div><strong>Old</strong></div>
                              			<div><strong>New</strong></div>
                              			<?php
                              			$updated = json_decode(base64_decode($dv->details));
                              			foreach($updated as $uk=>$up)
                              			{
                              			    if($uk != 'room_archived' && (empty($up->old) && empty($up->new)))
                              			    {
                              			        continue;
                              			    }
                              			?>
                              			<div><?=$uk?></div>
                              			<?php if($uk != 'room_archived') : ?>
                              			<div><?=$up->old?></div>
                              			<div><?=$up->new?></div>
                              			<?php
                              			endif;
                              			}
                              			?>
                              		</div>

                              	</li>
                              <?php
                              }
                              ?>
                              </ul>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                              </div>
                            </div>

                          </div>
                        </div>
              		</div>
                </div>
              </div>
            <div class="clearfix"></div>
            <div class="row">
              <div class="col-xs-12">
                     <div class="x_content">
                    <br />
                    <form id="roomeditForm" data-parsley-validate class="form-horizontal form-label-left usage_exclude">
                        <div id="usage-add" class="usage_exclude" data-type="usage">
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Resort confirmation number
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input <?=$disabled?> type="text" id="resort_confirmation_number" name="resort_confirmation_number" class="form-control col-md-7 col-xs-12" value="<?php echo $data['room']->resort_confirmation_number; ?>">
                              <span id="resorterror"></span>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Check In Date<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input <?=$disabled?> type="text" id="check_in_date" name="check_in_date" required placeholder="MM/DD/YYYY" data-date-format="MM/DD/YYYY" data-parsley-trigger="keyup" onkeyup="this.value=this.value.replace(/^(\d\d)(\d)$/g,'$1/$2').replace(/^(\d\d\/\d\d)(\d+)$/g,'$1/$2').replace(/[^\d\/]/g,'')" class="form-control col-md-7 col-xs-12" value="<?php echo strftime('%m/%d/%Y', strtotime($data['room']->check_in_date)); ?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Check Out Date
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input <?=$disabled?> type="text" id="check_out_date" name="check_out_date" onkeyup="this.value=this.value.replace(/^(\d\d)(\d)$/g,'$1/$2').replace(/^(\d\d\/\d\d)(\d+)$/g,'$1/$2').replace(/[^\d\/]/g,'')" class="form-control col-md-7 col-xs-12" value="<?php echo strftime('%m/%d/%Y', strtotime($data['room']->check_out_date)); ?>">
                            </div>
                          </div>


                          <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Resort<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <?php

                              echo '<select '.$disabled.' id="resort" name="resort" class="form-control col-md-7 col-xs-12 select2" required="required">
                                      <option value="0">Please Select</option>';

                                foreach($data['resort'] as $resort){

                                  if(isset($data['room']->resort) && $resort->id == $data['room']->resort) {

                                  echo '<option selected="selected" value="'.$resort->id.'">'.$resort->ResortName.'</option>';
                                 }
                                 else
                                 {

                                echo '<option value="'.$resort->id.'">'.$resort->ResortName.'</option>';
                                    }
                                  }
                              echo '</select>';

                               ?>
                            </div>
                          </div>

                          <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Unit Type<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                               <?php

                              echo '<select  id="unit_type_id" name="unit_type_id" class="form-control col-md-7 col-xs-12 select2" required="required">
                                      <option value="0">Please Select</option>';

                                foreach($data['unit_type'] as $unitType){

                                  if(isset($data['room']->unit_type) && $unitType->record_id == $data['room']->unit_type) {

                                  echo '<option selected="selected" value="'.$unitType->record_id.'">'.$unitType->name.'</option>';
                                 }
                                 else
                                 {

                                echo '<option value="'.$unitType->record_id.'">'.$unitType->name.'</option>';
                                    }
                                  }
                              echo '</select>';

                               ?>
                            </div>
                          </div>


                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Source<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <select  id="source" name="source" class="form-control col-md-7 col-xs-12 select2" required="required">
                                <option value="0">Please Select</option>
                                <option <?php if(isset($data['room']->source_num) && $data['room']->source_num == '1'){?> selected="selected" <?php } ?> value="1">Owner</option>
                                <option <?php if(isset($data['room']->source_num) && $data['room']->source_num == '2'){?> selected="selected" <?php } ?> value="2">GPR</option>
                                <option <?php if(isset($data['room']->source_num) && $data['room']->source_num == '3'){?> selected="selected" <?php } ?> value="3">Trade Partner</option>
                              </select>
                            </div>
                          </div>
                          <div class="form-group hide" id="sourcepartnerfield">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Source Partner
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input  type="text" id="autocomplete" class="form-control col-md-7 col-xs-12" value="<?php echo $data['user'][0]->name ?? ''; ?>">
                              <input type="hidden" id="source_partner_id" class="form-control col-md-7 col-xs-12" value="<?php echo $data['room']->source_partner_id ?? ''; ?>">
              				  <input type="hidden" id="room_id" class="form-control col-md-7 col-xs-12" value="<?php echo $data['room']->record_id ?? ''; ?>">

                            </div>
                          </div>

                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Active
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <div class="checkbox">
                              <label><input <?=$disabled?> id="Radio1" type="radio" class="form-control hide_active_date" name="active" value='1' <?php if(isset($data['room']->active) && $data['room']->active == '1'){?> checked="checked" <?php } ?>/>True</label>
                              <label><input <?=$disabled?> id="Radio2" type="radio" class="form-control show_active_date" name="active" value='0' <?php if(isset($data['room']->active) && $data['room']->active == '0'){?> checked="checked" <?php } ?> />False</label>
                              </div>

                            </div>
                          </div>


                          <div class="form-group" id="active_display_date">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="display-date">Display Date
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                            	<select <?=$disabled?> id="active_type" name="active_type" class="form-control select2">
                            	<?php
                            	$options = [
                            	    '0' => 'Please Select',
                            	    'date' => 'Select Date',
                            	    'weeks' => 'Select Weeks Before Check-in',
                            	    'months' => 'Select Months Before Check-in',
                            	];
                            	foreach($options as $ok=>$ov)
                            	{
                            	    $selected = '';
                            	    if($data['room']->active_type == $ok)
                            	    {
                            	        $selected = ' selected="selected"';
                            	    }
                            	?>

                            		<option value="<?=$ok?>" <?=$selected?>><?=$ov?></option>
                           		<?php
                            	}
                           		?>
                            	</select>
                            	<div id="activity_type_selection">
                            	    <input <?=$disabled?> type="text" id="active_specific_date" name="active_specific_date" value="<?=date('m/d/Y', strtotime($data['room']->active_specific_date))?>" onkeyup="this.value=this.value.replace(/^(\d\d)(\d)$/g,'$1/$2').replace(/^(\d\d\/\d\d)(\d+)$/g,'$1/$2').replace(/[^\d\/]/g,'')"  class="form-control col-md-7 col-xs-12" onkeydown="return false">
                            		<div id="active_week_month">
                                		<select <?=$disabled?> id="active_week_month_sel" class="form-control select2" name="active_week_month">
                                			<option value="0">Please Select</option>
                                			<?php
                                			for($i=1;$i<51;$i++)
                                			{
                                			    $selected = '';
                                			    if($data['room']->active_week_month == $i)
                                			    {
                                			        $selected = ' selected="selected"';
                                			    }
                                			?>
                                			<option value="<?=$i?>"><?=$i?></option>
                                			<?php
                                			}
                                			?>
                                		</select>
                            		</div>
                            	</div>
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Availability<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <select <?=$disabled?> id="availability" name="availability" class="form-control col-md-7 col-xs-12 select2" required="required">
                                <option <?php if(isset($data['room']->availability) && $data['room']->availability == '0'){?> selected="selected" <?php } ?> value="0">Please select</option>
                                <option <?php if(isset($data['room']->availability) && $data['room']->availability == '1'){?> selected="selected" <?php } ?> value="1">All</option>
                                <option <?php if(isset($data['room']->availability) && $data['room']->availability == '2'){?> selected="selected" <?php } ?> value="2">Owner Only</option>
                                <option <?php if(isset($data['room']->availability) && $data['room']->availability == '3'){?> selected="selected" <?php } ?> value="3">Partner Only</option>
                              </select>
                            </div>
                          </div>

                          <div class="form-group" id="avaiolablepartnerfield">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Available To
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input <?=$disabled?> type="text" id="autocompleteAvailability" class="form-control col-md-7 col-xs-12" value="<?=$data['room']->available_to_partner_id?>">
                              <input <?=$disabled?> type="hidden" id="available_to_partner_id" name="available_to_partner_id" class="form-control col-md-7 col-xs-12" value="<?=$data['room']->available_to_partner_id?>">

                            </div>
                          </div>
                          <?php
                          /*
                          ?>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Available To Partner
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <?php

                              echo '<select id="available_to_partner_id" name="available_to_partner_id" class="form-control col-md-7 col-xs-12 select2" required="required">
                                      <option value="0">Please Select</option>';

                                foreach($data['partner'] as $part){

                       if(isset($data['room']->available_to_partner_id) && $part->record_id == $data['room']->available_to_partner_id) {

                                  echo '<option selected="selected" value="'.$part->record_id.'">'.$part->name.'</option>';
                                 }
                                  else{
                                    echo '<option value="'.$part->record_id.'">'.$part->name.'</option>';
                                  }

                                    }
                              echo '</select>';
                               ?>
                            </div>

                          </div>
                          <?php
                          */
                          ?>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Type<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <select <?=$disabled?> id="type" name="type" class="form-control col-md-7 col-xs-12 select2" required="required">
                               <option <?php if(isset($data['room']->type) && $data['room']->type == '3'){?> selected="selected" <?php } ?> value="3">Exchange/Rental</option>
                               <option <?php if(isset($data['room']->type) && $data['room']->type == '1'){?> selected="selected" <?php } ?> value="1">Exchange</option>
                                <option <?php if(isset($data['room']->type) && $data['room']->type == '2'){?> selected="selected" <?php } ?> value="2">Rental</option>

                              </select>
                            </div>
                          </div>

                          <div id="pricewrapper">
                          <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Price
                        <span class="required">*</span></label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input <?=$disabled?> type="number" data-min="<?=get_option('gpx_min_rental_fee')?>" id="price" name="price" class="form-control col-md-7 col-xs-12" value="<?php echo $data['room']->price; ?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="price">Rental Available
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input <?=$disabled?> type="text" id="rental_push_date" name="rental_push_date" required placeholder="MM/DD/YYYY" data-date-format="MM/DD/YYYY" data-parsley-trigger="keyup" onkeyup="this.value=this.value.replace(/^(\d\d)(\d)$/g,'$1/$2').replace(/^(\d\d\/\d\d)(\d+)$/g,'$1/$2').replace(/[^\d\/]/g,'')" class="form-control col-md-7 col-xs-12" value="<?php echo strftime('%m/%d/%Y', strtotime($data['room']->active_rental_push_date)); ?>">
                            </div>
                          </div>
                      </div>

                          <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Note</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input <?=$disabled?> type="text" id="note" name="note" class="form-control col-md-7 col-xs-12" value="<?php echo $data['room']->note; ?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <?php
//                             if($disabled == '')
//                             {
                            ?>
                              <button id="roomeditsubmit" type="submit" class="btn btn-success">Submit <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                            <?php
//                             }
                            ?>
                            </div>
                          </div>
                        </div>
                    </form>
                  </div>


                  <div id="myModal" class="modal fade">
                    <div class="modal-dialog modal-confirm">
                      <div class="modal-content">
                        <div class="modal-header">
                          <div class="icon-box">
                            <i class="material-icons">&#xE876;</i>
                          </div>
                          <h4 class="modal-title">Done!</h4>
                        </div>
                        <div class="modal-body">
                          <p class="text-center">Room updated Successfully.</p>
                        </div>
                        <div class="modal-footer">
                          <button class="btn btn-success btn-block" data-dismiss="modal">OK</button>
                        </div>
                      </div>
                    </div>
                  </div>


              </div>
          </div>
                    <div class="row" style="margin-top: 45px;">
                    	<div class="col-xs-12">
                    		<h4>Transactions</h4>
                                <table data-toggle="table"
                                             data-url="<?=admin_url("admin-ajax.php?&action=gpx_admin_owner_transactions&weekID=".$_GET['id']);?>"
                                             data-cache="false"
                                             data-pagination="true"
                                             data-page-size="20"
                                             data-page-list="[10,20,50,100]"
                                             data-sort-name="gpx"
                                             data-show-refresh="true"
                                             data-show-toggle="true"
                                             data-show-columns="true"
                                             data-show-export="true"
                                             data-export-data-type="all"
                                             data-export-types="['csv', 'txt', 'excel']"
                                             data-search="true"
                                             data-sort-order="desc"
                                             data-show-columns="true"
                                             data-filter-control="true"
                                             data-filter-show-clear="true"
                                             data-escape="false"
                                             data-click-to-select="true"
                                             >
                						<thead>
                                            <tr>
                                                <th data-field="view"></th>
                                                <th data-field="id" data-filter-control="input" data-sortable="true">Transaction ID</th>
                                                <th data-field="memberNo" data-filter-control="input" data-sortable="true">Member Number</th>
                                                <th data-field="memberName" data-filter-control="input" data-sortable="true">Member Name</th>
                                                <th data-field="ownedBy" data-filter-control="input" data-sortable="false">Owned By</th>
                                                <th data-field="guest" data-filter-control="input" data-sortable="true" data-width="170" data-class="guestNameTD">Guest Name</th>
                                                <th data-field="adults" data-filter-control="input" data-sortable="true" data-visible="false">Adults</th>
                                                <th data-field="children" data-filter-control="input" data-sortable="true" data-visible="false">Children</th>
                                                <th data-field="upgradefee" data-filter-control="input" data-sortable="true" data-visible="false">Upgrade Fee</th>
                                                <th data-field="cpo" data-filter-control="input" data-sortable="true" data-visible="false">CPO</th>
                                                <th data-field="cpofee" data-filter-control="input" data-sortable="true" data-visible="false">CPO Fee</th>
                                                <th data-field="Resort" data-filter-control="input" data-sortable="true">Resort Name</th>
                                                <th data-field="weekType" data-filter-control="input" data-sortable="true">Week Type</th>
                                                <th data-field="weekPrice" data-filter-control="input" data-sortable="true" data-visible="false">Week Price</th>
                                                <th data-field="balance" data-filter-control="input" data-sortable="true" data-visible="false">Balance</th>
                                                <th data-field="resortID" data-filter-control="input" data-sortable="true" data-visible="false">Resort ID</th>
                                                <th data-field="weekID" data-filter-control="input" data-sortable="true" data-visible="false">WeekID</th>
                                                <th data-field="size" data-filter-control="input" data-sortable="true" data-visible="false">Size</th>
                                                <th data-field="sleeps" data-filter-control="input" data-sortable="true" data-visible="false">Sleeps</th>
                                                <th data-field="bedrooms" data-filter-control="input" data-sortable="true" data-visible="false">Bedrooms</th>
                                                <th data-field="nights" data-filter-control="input" data-sortable="true" data-visible="false">Nights</th>
                                                <th data-field="checkIn" data-filter-control="input" data-sortable="true">Check In</th>
                                                <th data-field="paid" data-filter-control="input" data-sortable="true">Paid</th>
                                                <th data-field="nights" data-filter-control="input" data-sortable="true" data-visible="false">Nights</th>
                                                <th data-field="processedBy" data-filter-control="input" data-sortable="true" data-visible="false">Processed By</th>
                                                <th data-field="promoName" data-filter-control="input" data-sortable="true" data-visible="false">Promo Name</th>
                                                <th data-field="discount" data-filter-control="input" data-sortable="true" data-visible="false">Discount</th>
                                                <th data-field="coupon" data-filter-control="input" data-sortable="true" data-visible="false">Coupon</th>
                                                <th data-field="ownerCreditCouponID" data-filter-control="input" data-sortable="true" data-visible="false">Owner Credit Coupon ID</th>
                                                <th data-field="ownerCreditCouponAmount" data-filter-control="input" data-sortable="true" data-visible="false">Owner Credit Coupon Amount</th>
                                                <th data-field="transactionDate" data-filter-control="input" data-sortable="true">Transaction Date</th>
                                                <th data-field="cancelled" data-filter-control="select" data-sortable="true" data-class="cancelledTransactionTD">Cancelled</th>
                                            </tr>
                                        </thead>
                              </table>

                    	</div>
                    </div>

                    <div class="row" style="margin-top: 45px;">
                    	<div class="col-xs-12">
                    		<h4>
                    			Holds
                    		</h4>

                            		<table id="transactionsTable" data-toggle="table"
                                             data-url="admin-ajax.php?&action=get_gpx_holds&weedID=<?=$_GET['id']?>"
                                             data-cache="false"
                                             data-pagination="true"
                                             data-page-size="20"
                                             data-page-list="[10,20,50,100]"
                                             data-sort-name="gpx"
                                             data-show-refresh="true"
                                             data-show-toggle="true"
                                             data-show-columns="true"
                                             data-show-export="true"
                                             data-export-data-type="all"
                                             data-export-types="['csv', 'txt', 'excel']"
                                             data-search="true"
                                             data-sort-order="desc"
                                             data-show-columns="true"
                                             data-filter-control="true"
                                             data-filter-show-clear="true"
                                             data-escape="false"
                                             data-click-to-select="true"
                                             >
                						<thead>
                                            <tr>
                                                <th data-field="action"></th>
                                                <th data-field="name" data-filter-control="input" data-sortable="true">Owner Name</th>
                                                <th data-field="memberNo" data-filter-control="input" data-sortable="true">GPR ID</th>
                                                <th data-field="week" data-filter-control="input" data-sortable="false">Week ID</th>
                                                <th data-field="resort" data-filter-control="input" data-sortable="false">Resort</th>
                                                <th data-field="roomSize" data-filter-control="input" data-sortable="false">Room Size</th>
                                                <th data-field="checkIn" data-filter-control="input" data-sortable="false">Check In</th>
                                                <th data-field="releaseOn" data-filter-control="input" data-sortable="true">Release On</th>
                                                <th data-field="release" data-filter-control="select" data-sortable="false">Released</th>
                                            </tr>
                                        </thead>
                                    </table>
                    	</div>
                    </div>

           <div id="guest-details" class="modal fade" role="dialog">
              <div class="modal-dialog">
            	<form name="update-guest-details" id="update-guest-details" method="POST">
            		<input type="hidden" name="transactionID" id="transactionID" value="">
                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Guest Details</h4>
                      </div>
                      <div class="modal-body">
                      	<div class="row">
                      		<div class="col-xs-12 col-xs-6">
                      			<div class="form-group">
                      				<label for="FirstName1">First Name</label>
                      				<input type="text" name="FirstName1" id="FirstName1" class="form-control" value="">
                      			</div>
                      			<div class="form-group">
                      				<label for="LastName1">Last Name</label>
                      				<input type="text" name="LastName1" id="LastName1" class="form-control" value="">
                      			</div>
                      			<div class="form-group">
                      				<label for="Email">Email</label>
                      				<input type="text" name="Email" id="Email" class="form-control" value="">
                      			</div>
                      			<div class="form-group">
                      				<label for="Phone">Phone</label>
                      				<input type="text" name="Phone" id="Phone" class="form-control" value="">
                      			</div>
                      		</div>
                      		<div class="col-xs-12 col-xs-6">
                      			<div class="form-group">
                      				<label for="Adults">Adults</label>
                      				<input type="text" name="Adults" id="Adults" class="form-control" value="">
                      			</div>
                      			<div class="form-group">
                      				<label for="Children">Children</label>
                      				<input type="text" name="Children" id="Children" class="form-control" value="">
                      			</div>
                      			<div class="form-group">
                      				<label for="Owner">Owned By</label>
                      				<input type="text" name="Owner" id="Owner" class="form-control" value="<?=$transaction->Owner ?? ''?>">
                      			</div>
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
          <div id="cancelled-transactions" class="modal fade" role="dialog">
              <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Cancellation Details</h4>
                      </div>
                      <div class="modal-body">
                      	<div class="row">
                      		<div class="col-xs-12 col-xs-6">
                      			<div class="form-group">
                      				<label for="tname">Cancelled By</label>
                      				<input type="text" name="tname" id="tname" class="form-control" value="" disabled>
                      			</div>
                      			<div class="form-group">
                      				<label for="tdate">Date</label>
                      				<input type="text" name="tdate" id="tdate" class="form-control" value="" disabled>
                      			</div>
                      			<div class="form-group">
                      				<label for="trefunded">Refunded</label>
                      				<input type="text" name="trefunded" id="trefunded" class="form-control" value="" disabled>
                      			</div>
                      		</div>
                      	</div>
                      </div>
                      <div class="modal-footer">
                        <a href="#" class="btn btn-default" data-dismiss="modal">Close</a>
                      </div>
                    </div>
              </div>
            </div>
         </div>
       </div>

<div id="deleteModal" class="modal fade">
    <div class="modal-dialog modal-confirm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="icon-box">
                    <i class="material-icons">&#xE876;</i>
                </div>
                <h4 class="modal-title">Done!</h4>
            </div>
            <div class="modal-body">
                <p class="text-center">Room archived Successfully.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success btn-block" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery('#deleteModal').on('hide.bs.modal', function () {
        window.location =  '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_all';
    });
</script>

       <?php include $dir.'/templates/admin/footer.php';?>
