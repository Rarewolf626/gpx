<?php

extract($static);
extract($data);
include $dir.'/templates/admin/header.php';

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
                <h3>Add Room</h3>
              </div>
            </div>
                        
            <div class="clearfix"></div>
            <div class="row" id="admin-modal-content">
              <div class="col-md-12">
                     <div class="x_content">
                    <br />
                    <form id="roomaddForm" data-parsley-validate class="form-horizontal form-label-left usage_exclude">
                        <div id="usage-add" class="usage_exclude" data-type="usage">
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Resort confirmation number
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="resort_confirmation_number" name="resort_confirmation_number" class="form-control col-md-7 col-xs-12">
                              <span id="resorterror"></span>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Check In Date<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="check_in_date" name="check_in_date" required placeholder="MM/DD/YYYY" data-date-format="MM/DD/YYYY" data-parsley-trigger="keyup" onkeyup="this.value=this.value.replace(/^(\d\d)(\d)$/g,'$1/$2').replace(/^(\d\d\/\d\d)(\d+)$/g,'$1/$2').replace(/[^\d\/]/g,'')" class="form-control col-md-7 col-xs-12" onkeydown="return false">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Check Out Date
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="check_out_date" name="check_out_date" onkeyup="this.value=this.value.replace(/^(\d\d)(\d)$/g,'$1/$2').replace(/^(\d\d\/\d\d)(\d+)$/g,'$1/$2').replace(/[^\d\/]/g,'')"  class="form-control col-md-7 col-xs-12" onkeydown="return false">
                            </div>
                          </div>
                          <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Resort<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <?php 

                              echo '<select id="resort" name="resort" class="form-control col-md-7 col-xs-12 select2" required data-parsley-error-message="Please Select resort" data-parsley-min="1" data-parsley-errors-container="#resort-errors" required data-parsley-trigger="keyup">
                                      <option value="0">Please Select</option>';

                                foreach($data['resort'] as $resort){
                                    
                                echo '<option value="'.$resort->id.'">'.$resort->ResortName.'</option>';
                                    }

                              echo '</select>';

                               ?>
                               <div id="resort-errors"></div>
                            </div>
                            
                          </div>
                           
                          <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Unit Type<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <select id="unit_type_id" name="unit_type_id" class="form-control col-md-7 col-xs-12 select2" required data-parsley-error-message="Please Select unit type" data-parsley-min="1" data-parsley-errors-container="#unit-errors" required data-parsley-trigger="keyup">
                                      <option value="0">Please Select</option>';

                               </select>
                               <div id="unit-errors"></div>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Source<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <select id="source" name="source" class="form-control col-md-7 col-xs-12 select2" required data-parsley-error-message="Please Select a source" data-parsley-min="1" data-parsley-errors-container="#owner-errors" required data-parsley-trigger="keyup">
                               <?php 
                               $options = [
                                    '0'=>'Please Select',
                                    '1'=>'Owner',
                                    '2'=>'GPR',
                                    '3'=>'Trade Partner',
                               ];
                               $selected = '';
                               foreach($options as $ok=>$option)
                               {
                                   if(isset($_GET['tp']) && $ok == 3)
                                   {
                                       $selected = 'selected="selected"';
                                   }
                               ?>
                                <option value="<?=$ok?>" <?=$selected?>><?=$option?></option>
                               <?php 
                               }
                               ?>
                              </select>
                              <div id="owner-errors"></div>
                            </div>
                          </div>
                          <div class="form-group hide" id="sourcepartnerfield">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Source
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="autocomplete" class="form-control col-md-7 col-xs-12">
                              <?php 
                              $tpid = '';
                              if(isset($_GET['tp']))
                              {
                                  $tpid = $_GET['tp'];
                              }
                              ?>
                              <input type="hidden" id="source_partner_id" value="<?=$tpid?>" class="form-control col-md-7 col-xs-12">
                              
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Active
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <div class="checkbox">
                              <label><input id="Radio1" type="radio" class="form-control hide_active_date" name="active" value="1" checked="checked" /> True</label>
                              <label><input id="Radio2" type="radio" class="form-control show_active_date" name="active" value="0"/> False</label>
                              </div>
                              
                            </div>
                          </div>
                          <div class="form-group" id="active_display_date">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="display-date">Display Date
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                            	<select id="active_type" name="active_type" class="form-control select2">
                            	<?php 
                            	$options = [
                            	    '0' => 'Please Select',
                            	    'date' => 'Select Date',
                            	    'weeks' => 'Select Weeks Before Check-in',
                            	    'months' => 'Select Months Before Check-in',
                            	];
                            	foreach($options as $ok=>$ov)
                            	{
                            	?>
                            	
                            		<option value="<?=$ok?>"><?=$ov?></option>
                           		<?php 
                            	}
                           		?>
                            	</select>
                            	<div id="activity_type_selection">
                            	    <input type="text" id="active_specific_date" name="active_specific_date" onkeyup="this.value=this.value.replace(/^(\d\d)(\d)$/g,'$1/$2').replace(/^(\d\d\/\d\d)(\d+)$/g,'$1/$2').replace(/[^\d\/]/g,'')"  class="form-control col-md-7 col-xs-12" onkeydown="return false">
                            		<div id="active_week_month">
                                		<select id="active_week_month_sel" class="form-control select2" name="active_week_month">
                                			<option value="0">Please Select</option>
                                			<?php 
                                			for($i=1;$i<51;$i++)
                                			{
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
                              <select id="availability" name="availability" class="form-control col-md-7 col-xs-12 select2" required data-parsley-error-message="Please select availability" data-parsley-min="1" data-parsley-errors-container="#availability-errors" required data-parsley-trigger="keyup">
                                <option value="0">Please select</option>
                                <option value="1">All</option>
                                <option value="2">Owner Only</option>
                                <option value="3">Partner Only</option>
                              </select>
                              <div id="availability-errors"></div>
                            </div>
                          </div>
                          <div class="form-group" id="avaiolablepartnerfield">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Available To
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="autocompleteAvailability" class="form-control col-md-7 col-xs-12">
                              <input type="hidden" id="available_to_partner_id" name="available_to_partner_id" class="form-control col-md-7 col-xs-12">
                              
                            </div>
                          </div>

                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Type<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <select id="type" name="type" class="form-control col-md-7 col-xs-12 select2" required data-parsley-error-message="Please select type" data-parsley-min="1" data-parsley-errors-container="#type-errors" required data-parsley-trigger="keyup">
                                <option value="3">Exchange/Rental</option>
                                <option value="1">Exchange</option>
                                <option value="2">Rental</option>
                              </select>
                              <div id="price-errors"></div>
                            </div>
                          </div>
                          
                         <div  id="pricewrapper">
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="price">Price
                                  <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="number" min="<?=get_option('gpx_min_rental_fee')?>"  id="price" name="price" class="form-control col-md-7 col-xs-12">
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="price">Rental Available<br /><small>(Months Before Check In)</small>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="number" min="1" max="36" id="rental_push" name="rental_push" class="form-control col-md-7 col-xs-12">
                                </div>
                              </div>
                          </div>
                          
                         
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Note
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="note" name="note" class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="count"># of Rooms<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="number" id="count" name="count" class="form-control col-md-7 col-xs-12" value="1" required>
                            </div>
                          </div>
                          <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                              <button id="roomaddsubmitclear" type="submit" class="btn btn-success" data-clear="clear" value="validate">Submit & Clear<i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                              <button id="roomaddsubmit" type="submit" class="btn btn-success" data-clear="submit" value="validate">Submit <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
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
        <p class="text-center">Successfully.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success btn-block" data-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>



              </div>
         	</div>
         </div>
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>