<?php
extract($static);
extract($data);
include $dir.'/templates/admin/header.php';
$GuestFeeAmount = '';
if(isset($resort->GuestFeeAmount))
    $GuestFeeAmount = $resort->GuestFeeAmount;
    
    $resortDates = (array) $resort->dates;
    $defaultAttrs = (array) $resort->defaultAttrs;
    $rmDefaults = (array) $resort->rmdefaults;
    $unit_types = (array) $resort->unit_types;
    ?>
<div id="gpx-ajax-loading">
 	<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
</div>
<div class="modal fade" id="addTax">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<div class="modal-title">Add New Tax</div>
			</div>
			<div class="modal-body">
		     	<form id="resorttax-add" data-parsley-validate class="form-horizontal form-label-left">
		     	   <input type="hidden" name="resortID" value="<?=$resort->ResortID?>">
                  <div class="form-group">
                  	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="TaxAuthority">Tax Authority <span class="required">*</span></label> 
                  	<div class="col-md-6 col-sm-6 col-xs-11">
                      <input type="text" name="TaxAuthority" id="TaxAuthority" class="form-control form-element" value="<?=$tax->TaxAuthority;?>">
                    </div>
                  </div>
                  <div class="form-group">
                  	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="City">City <span class="required">*</span></label> 
                  	<div class="col-md-6 col-sm-6 col-xs-11">
                      <input type="text" name="City" id="City" class="form-control form-element" value="<?=$tax->City;?>">
                    </div>
                  </div>
                  <div class="form-group">
                  	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="State">State <span class="required">*</span></label> 
                  	<div class="col-md-6 col-sm-6 col-xs-11">
                      <input type="text" name="State" id="State" class="form-control form-element" value="<?=$tax->State;?>">
                    </div>
                  </div>
                  <div class="form-group">
                  	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="Country">Country <span class="required">*</span></label> 
                  	<div class="col-md-6 col-sm-6 col-xs-11">
                      <input type="text" name="Country" id="Country" class="form-control form-element" value="<?=$tax->Country;?>">
                    </div>
                  </div>
                  <div class="form-group">
                  	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="TaxPercent1">Tax Percent 1 <span class="required">*</span></label> 
                  	<div class="col-md-6 col-sm-6 col-xs-11">
                      <input type="text" name="TaxPercent1" id="TaxPercent1" class="form-control form-element" value="<?=$tax->TaxPercent1;?>">
                    </div>
                  </div>
                  <div class="form-group">
                  	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="TaxPercent2">Tax Percent 2</label> 
                  	<div class="col-md-6 col-sm-6 col-xs-11">
                      <input type="text" name="TaxPercent2" id="TaxPercent2" class="form-control form-element" value="<?=$tax->TaxPercent2;?>">
                    </div>
                  </div>
                  <div class="form-group">
                  	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="TaxPercent3">Tax Percent 3</label> 
                  	<div class="col-md-6 col-sm-6 col-xs-11">
                      <input type="text" name="TaxPercent3" id="TaxPercent3" class="form-control form-element" value="<?=$tax->TaxPercent3;?>">
                    </div>
                  </div>
                  <div class="form-group">
                  	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="FlatTax1">Flat Tax 1 <span class="required">*</span></label> 
                  	<div class="col-md-6 col-sm-6 col-xs-11">
                      <input type="text" name="FlatTax1" id="FlatTax1" class="form-control form-element" value="<?=$tax->FlatTax1;?>">
                    </div>
                  </div>
                  <div class="form-group">
                  	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="FlatTax2">Flat Tax 2</label> 
                  	<div class="col-md-6 col-sm-6 col-xs-11">
                      <input type="text" name="FlatTax2" id="FlatTax21" class="form-control form-element" value="<?=$tax->FlatTax2;?>">
                    </div>
                  </div>
                  <div class="form-group">
                  	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="FlatTax3">Flat Tax 3</label> 
                  	<div class="col-md-6 col-sm-6 col-xs-11">
                      <input type="text" name="FlatTax3" id="FlatTax3" class="form-control form-element" value="<?=$tax->FlatTax3;?>">
                    </div>
                  </div>
                  <div class="ln_solid"></div>
                  <div class="form-group">
                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                      <button type="submit" class="btn btn-success" id="resorttax-submit">Submit <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                    </div>
                  </div>
                </form>
			</div>
		</div>
	</div>
</div>
        <div class="right_col" role="main">
          <div class="update-nag"></div>
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Edit <a href="/resort-profile/?resortName=<?=$resort->ResortName?>" target="_blank"><?=$resort->ResortName?></a></h3>
              </div>
              <div class="title_right">
              <?php 
              /*
              ?>
              	<div class="row">
              		<div class="col-xs-12 col-md-6 pull-right">
              		<a href="" class="btn btn-primary" id="active-resort" data-active="<?=$resort->active?>" data-resort="<?=$resort->ResortID?>">Active 
              			<i class="active-status fa fa-<?php if($resort->active == '1') echo 'check-';?>square" aria-hidden="true"></i>
              		</a><br>
              		<a href="" class="btn btn-primary" id="featured-resort" data-featured="<?=$resort->featured?>" data-resort="<?=$resort->ResortID?>">Featured 
              			<i class="featured-status fa fa-<?php if($resort->featured == '1') echo 'check-';?>square" aria-hidden="true"></i>
              		</a><br>
              		<a href="" class="btn btn-primary" id="ai-resort" data-ai="<?=$resort->ai?>" data-resort="<?=$resort->ResortID?>">All Inclusive 
              			<i class="ai-status fa fa-<?php if($resort->ai == '1') echo 'check-';?>square" aria-hidden="true"></i>
              		</a><br>
              		<a href="" class="btn btn-primary" id="guest-fees" data-enabled="<?=$resort->guestFeesEnabled?>" data-resort="<?=$resort->ResortID?>">Guest Fees Enabled 
              			<i class="gfEnabled-status fa fa-<?php if($resort->guestFeesEnabled == '1') echo 'check-';?>square" aria-hidden="true"></i>
              		</a><br>
              		<a href="" class="btn btn-primary" id="reload-resort" data-resort="<?=$resort->ResortID?>">Manually Refresh Resort Cache</a><br>
              		<div class="row" style="margin-bottom: 5px;">
              			<div class="col-xs-12">
              				<label class="control-label">Tax Method (from price set)</label> 
              				<div class="btn-group cg-btn-group" data-toggle="buttons">
              					<label class="btn btn-<?php if($resort->taxMethod == 1) echo 'primary'; else echo 'default';?>">
              						<input type="radio" data-toggle="toggle tax-method" data-resort="<?=$resort->ResortID?>" id="taxAdd" name="taxMethod" value="1" <?php if($resort->taxMethod == 1) echo 'checked';?>> Add
              					</label>
              					<label class="btn btn-<?php if($resort->taxMethod == 2) echo 'primary'; else echo 'default';?>">
              						<input type="radio" data-toggle="toggle tax-method" data-resort="<?=$resort->ResortID?>" id="taxDeduct" name="taxMethod" value="2" <?php if($resort->taxMethod == 2) echo 'checked';?>> Deduct 
              					</label>
              				</div>
              			</div>
              		</div>
              		<div class="row" style="margin-bottom: 5px;">
              			<div class="col-xs-12">
              				<label class="control-label">Resort Tax</label>
              				<select name="taxID" id="taxID" class="selectpicker" data-resort="<?=$resort->ResortID?>">
              					<optgroup label="Existing">
              						<option></option>
              						<?php 
              						foreach($resort->taxes as $tax)
              						{
              						?>
              						<option value="<?=$tax->ID?>" <?php if($tax->ID == $resort->taxID) echo 'selected';?>><?=$tax->TaxAuthority?> <?=$tax->City?> <?=$tax->State?> <?=$tax->Country?></option>
              						<?php 
              						}
              						?>
              					</optgroup>
              					<optgroup label="New" class="newTax">
              						<option value="new">New</option>
              					</optgroup>
              				</select>
              			</div>
              		</div>
              		<div class="row">
              			<div class="col-xs-12">
              				       <button type="button" id="btn-ta" class="btn btn-primary" data-toggle="modal" data-target="#modal-ta">
                                      Trip Advisor ID <span class="taID"><?=$resort->taID?></span>
                                   </button>
              			</div>
              		</div>
              		Note: Click a lock to edit a corresonding field.  Fields that remain locked will not be updated.
              		</div>
              	</div>
              	<?php 
              	*/
              	?>
              </div>
            </div>
                        
            <div class="clearfix"></div>
         	<div class="row">
         		<div class="col-xs-12">
         			<ul class="nav nav-tabs">
         			<?php 
         			$activeClass = [
         			    'alertnotes'=>'active in',
         			    'description'=>'',
         			    'attributes'=>'',
         			    'ada'=>'',
         			    'images'=>'',
         			    'resort-fees'=>'',
         			    'unittype'=>'',
         			    'resort-settings'=>'',
         			];
         			if($resort->newfile)
         			{
         			    $activeClass['alertnotes'] = '';
         			    $activeClass['images'] = 'active in';
         			}
         			if(isset($_COOKIE['resort-tab']))
         			{
         			    $activeTab = str_replace("#", "", $_COOKIE['resort-tab']);
             			foreach($activeClass as $acK=>$acV)
             			{
             			    if($acK == $activeTab)
             			    {
             			        $activeClass[$acK] = 'active in';
             			    }
             			    else 
             			    {
             			        $activeClass[$acK] = '';
             			    }
             			}
         			}
         			$active['images'] = 'active in';
         			?>
                      <li class="<?=$activeClass['alertnotes']?> tab-click"><a href="#alertnotes" role="tab" data-toggle="tab">Alert Notes</a></li>
                      <li class="<?=$activeClass['description']?> tab-click"><a href="#description" role="tab" data-toggle="tab">Description</a></li>
                      <li class="<?=$activeClass['ada']?> tab-click"><a href="#ada" role="tab" data-toggle="tab">ADA</a></li>
                      <li class="<?=$activeClass['attributes']?> tab-click"><a href="#attributes" role="tab" data-toggle="tab">Attributes</a></li>
                      <li class="<?=$activeClass['images']?> tab-click"><a href="#images" role="tab" data-toggle="tab">Gallery</a></li>
                      <li class="<?=$activeClass['resort-fees']?> tab-click"><a href="#resort-fees" role="tab" data-toggle="tab">Resort Fees</a></li>
                      <li class="<?=$activeClass['unittype']?> tab-click"><a href="#unittype" role="tab" data-toggle="tab">Unit Type</a></li>
                      <li class="<?=$activeClass['resort-settings']?> tab-click"><a href="#resort-settings" role="tab" data-toggle="tab">Resort Settings</a></li>
                    </ul>
                    <div class="tab-content resort-tabs">
                    	<div class="tab-pane fade tab-padding two-column-grid <?=$activeClass['alertnotes']?>" id="alertnotes">
						<?php 
						$msi = 0;
						foreach($resortDates['alertnotes'] as $repeatableDate=>$resortAttribute)
						{
						    $oldorder = $msi;
                    	    $displayDateFrom = '';
                    	    $displayDateTo = '';
                    	    $dates = explode("_", $repeatableDate);
                    	    if(count($dates) == 1 and $dates[0] == '0')
                    	    {
                    	        $displayDateFrom = date('Y-m-d');
                    	    }
                    	    else
                    	    {
                    	        $oldorder = date('s', $dates[0]);
                    	        $displayDateFrom = date('Y-m-d', $dates[0]);
                    	        if(isset($dates[1]))
                    	        {
                    	            $displayDateTo = date('Y-m-d', $dates[1]);
                    	        }
                    	    }
                    	?>
                    	  <div class="repeatable well" data-seq="<?=$msi;?>">
                    	  		<div class="clone-group">
                    	  			<i class="fa fa-copy"></i>
                    	  			<i class="fa fa-times-circle-o" style="margin-left: 10px;" data-type="descriptions" data-resortid="<?=$resort->ResortID?>"></i>
                    	  		</div>
                    	      	<div id="date-select">
                                    <div class="filterRow">
                                    	<div class="filterBox">
                                    		<strong>Active Date
                                    		<a href="#" title="Different date ranges are required for each alert note." onclick="preventDefault();"><i class="fa fa-info-circle"></i></a>
                                    		</strong>
                                    	</div>
                                    </div>
                                    <div class="filterRow">
                                    	<div class="filterBox">
                                    		<input type="date" id="" class="from-date dateFilterFrom" placeholder="from" value="<?=$displayDateFrom;?>" data-oldfrom="<?=$displayDateFrom;?>" data-oldorder="<?=$oldorder;?>" /><span class="hyphen">-</span>
                                    	</div>
                                    	<div class="filterBox">
                                    		<input type="date" id="" class="to-date dateFilterTo" placeholder="to" value="<?=$displayDateTo;?>" data-oldto="<?=$displayDateTo;?>" />
                                    	</div>
                                    	<?php 
                                    	/*
                                    	?>
                                    	<div class="filterBox">
                                    		<a href="#" class="btn btn-apply date-filter-desc">Apply Date</a>
                                    	</div>
                                    	<?php 
                                    	*/
                                    	?>
                                    </div>
                                </div>	
                    		<form class="resort-edit" data-parsley-validate class="form-horizontal form-label-left">
                      			<input type="hidden" name="ResortID" value="<?=$resort->ResortID?>">
                      			<div class="two-column-grid">
                                	<?php 
                                	   $descs = [
                                	       'AlertNote' => 'Alert Note',
                                	   ];
                                	   $btns = [
                                	      'bookingpathdesc' => 'Booking Path',
                                	      'resortprofiledesc' => 'Resort Profile', 
                                	   ];
                                	   $i = 0;
                                	   foreach($descs as $descKey=>$descVal)
                                	   {
                                	       $defaultModals[$descKey] = [
                                	           'type'=>$descVal,
                                	           'desc'=>stripslashes($rmDefaults[$descKey])
                                	       ];
                                	       $attrDates = json_decode($resort->$descKey, true);
                                	       $thisAttr = [];
                                	       $thisBtn['bookingpathdesc'] = '0';
                                	       $thisBtn['resortprofiledesc'] = '0';
                                	     
                                	       if(!empty($attrDates))
                                	       {
                                    	       $thisAttrs = end($attrDates[$repeatableDate]);
                                    	       if(empty($thisAttrs['desc']))
                                    	       {
//                                     	           $thisAttr = stripslashes($resort->$descKey);
                                    	           $thisAttr = stripslashes($rmDefaults[$descKey]);
                                    	       }
                                    	       else 
                                    	       {
                                    	           $thisAttr = stripslashes($thisAttrs['desc']);
                                    	       }
                                    	       $thisAttrBk = '0';
                                    	       $thisAttrP = '0';
                                    	       if($thisAttrs['path']['booking'] != 0)
                                    	       {
                                    	           $thisAttrBk = 1;
                                    	       }
                                    	       if($thisAttrs['path']['profile'] != 0)
                                    	       {
                                    	           $thisAttrP = 1;
                                    	       }
                                    	       $thisBtn['bookingpathdesc'] = $thisAttrBk; 
                                    	       $thisBtn['resortprofiledesc'] = $thisAttrP; 
                                	       }
                                	       if(empty($thisAttr))
                                	       {
                                	           if(!isset($rmDefaults[$descKey]) && isset($resort->$descKey) && $resort->$descKey != '[]')
                                	           {
                                	               $thisAttr = stripslashes($resort->$descKey);
                                	           }
                                	           elseif($resort->$descKey == '[]')
                                	           {
                                	               $thisAttr = stripslashes($rmDefaults[$descKey]);
                                	           }
                                	           else 
                                	           {
                                	               $thisAttr = '';
                                	           }
                                	       }
                                	       
                                	   ?>
                                	   <div class=" edit-resort-group well">
                                	   
                                    	   <div class="row">
                                        		<div class="col-xs-12 col-sm-4">
                                        			<label for="<?=$descKey?>"><?=$descVal?> 
                                        				<a href="#" data-toggle="modal" data-target="#myModal" title="Default <?=$descVal?>">
                                        					<i class="fa fa-info-circle"></i>
                                        				</a>
                                        			</label>
                                        		</div>
                                        		
                                        		<div class="col-xs-12 col-sm-8 text-right">
                                        			<div class="btn-group">
                                        			<?php
                                            		  foreach($btns as $btnKey=>$btnVal)
                                            		  {
                                            		      $btnstatus[$descKey][$btnKey] = 'default';
                                            		      if($thisBtn[$btnKey] == '1')
                                            		      {
                                            		          $btnstatus[$descKey][$btnKey] = 'primary';
                                            		      }
                                            		?>
                                            			<a href="" class="btn btn-<?=$btnstatus[$descKey][$btnKey]?> <?=$btnKey?> path-btn" data-active="<?=$thisBtn[$btnKey]?>"  data-resort="<?=$resort->ResortID?>"><?=$btnVal?> 
                                                  			<i class="active-status fa fa-<?php if($thisBtn[$btnKey] == '1') echo 'check-';?>square" aria-hidden="true"></i>
                                                  		</a>
                                                  	<?php 
                                            		  }
                                                  	?>
                                              		</div>
                                        		</div>
                                        	</div>
                                        	<div class="row form-group">
                                        		<div class="col-xs-10">
                                                  <textarea name="<?=$descKey?>" class="form-control form-element new-attribute resort-descriptions" rows="4" data-type="<?=$descKey?>" data-resort="<?=$resort->ResortID?>" disabled><?=$thisAttr;?></textarea>
                                                </div>
                                                <div class="col-xs-1" style="cursor: pointer"><i class="fa fa-lock col-xs-1 resort-lock" aria-hidden="true" style="font-size: 20px"></i></div>
                                        	</div>
                                    	</div>
                                    	<div class="submit-box">
                                    		<a href="#" class="btn btn-primary ran-btn">Update</a>
                                    	</div>
                                	   <?php     
                                	   }
                                	?> 
                                </div>                       		
                            </form>
                          </div>
                          <?php 
                          $msi++;
                    	}
                          ?>
                    	</div>
                    	<div class="tab-pane fade tab-padding two-column-grid <?=$activeClass['description']?>" id="description">
						<?php 
						foreach($resortDates['descriptions'] as $repeatableDate=>$resortAttribute)
                    	{
                    	    $displayDateFrom = '';
                    	    $displayDateTo = '';
                    	    $dates = explode("_", $repeatableDate);
                    	    if(count($dates) == 1 and $dates[0] == '0')
                    	    {
                    	        $displayDateFrom = date('Y-m-d');
                    	    }
                    	    else
                    	    {
                    	        $displayDateFrom = date('Y-m-d', $dates[0]);
                    	        if(isset($dates[1]))
                    	        {
                    	            $displayDateTo = date('Y-m-d', $dates[1]);
                    	        }
                    	    }
                    	?>
                    	  <div class="repeatable well">
                    	  		<div class="clone-group" style="display:none;">
                    	  			<i class="fa fa-copy"></i>
                    	  			<i class="fa fa-times-circle-o" style="margin-left: 10px;" data-type="descriptions" data-resortid="<?=$resort->ResortID?>"></i>
                    	  		</div>
                    	      	<div id="date-select" style="display: none;">
                                    <div class="filterRow">
                                    	<div class="filterBox">
                                    		<strong>Active Date</strong>
                                    	</div>
                                    </div>
                                    <div class="filterRow">
                                    	<div class="filterBox">
                                    		<input type="date" id="" class=" from-date dateFilterFrom" placeholder="from" value="<?=$displayDateFrom;?>" data-oldfrom="<?=$displayDateFrom;?>" /><span class="hyphen">-</span>
                                    	</div>
                                    	<div class="filterBox">
                                    		<input type="date" id="" class=" to-date dateFilterTo" placeholder="to" value="<?=$displayDateTo;?>" data-oldto="<?=$displayDateTo;?>" />
                                    	</div>
                                    	<div class="filterBox">
                                    		<a href="#" class="btn btn-apply date-filter-desc">Apply</a>
                                    	</div>
                                    </div>
                                </div>	
                    		<form class="resort-edit" data-parsley-validate class="form-horizontal form-label-left">
                      			<input type="hidden" name="ResortID" value="<?=$resort->ResortID?>">
                      			<div class="two-column-grid">
                                	<?php 
                                	   $descs = [
                                	       'AreaDescription' => 'Area Description',
                                	       'UnitDescription' => 'Unit Description',
                                	       'AdditionalInfo' => 'Additional Info',
                                	       'Description' => 'Description',
                                	       'Website' => 'Website',
                                	       'CheckInDays' => 'Check In Days',
                                	       'CheckInEarliest' => 'Check In Earliest',
                                	       'CheckInLatest' => 'Check In Latest',
                                	       'CheckOutEarliest' => 'Check Out Earliest',
                                	       'CheckOutLatest' => 'Check Out Latest',
                                	       'Address1' => 'Address 1',
                                	       'Address2' => 'Address 2',
                                	       'Town' => 'City',
                                	       'Region' => 'State/Region',
                                	       'Country' => 'Country',
                                	       'PostCode' => 'ZIP/Post Code',
                                	       'Phone' => 'Phone',
                                	       'Fax' => 'Fax',
                                	       'Airport' => 'Closest Airport',
                                	       'Directions' => 'Directions',
                                	   ];
                                	   $btns = [
                                	      'bookingpathdesc' => 'Booking Path',
                                	      'resortprofiledesc' => 'Resort Profile', 
                                	   ];
                                	   $i = 0;
                                	   foreach($descs as $descKey=>$descVal)
                                	   {
                                	       $attrDates = json_decode($resort->$descKey, true);
                                	       $thisAttr = [];
                                	       $thisBtn['bookingpathdesc'] = '0';
                                	       $thisBtn['resortprofiledesc'] = '0';
                                	       if(!empty($attrDates))
                                	       {
                                    	       $thisAttrs = end($attrDates[$repeatableDate]);
                                    	       if(empty($thisAttrs['desc']))
                                    	       {
//                                     	           $thisAttr = stripslashes($resort->$descKey);
                                    	           $thisAttr = stripslashes($rmDefaults[$descKey]);
                                    	       }
                                    	       else 
                                    	       {
                                    	           $thisAttr = stripslashes($thisAttrs['desc']);
                                    	       }
                                    	       $thisBtn['bookingpathdesc'] = $thisAttrs['path']['booking']; 
                                    	       $thisBtn['resortprofiledesc'] = $thisAttrs['path']['profile']; 
                                	       }
                                	       if(empty($thisAttr))
                                	       {
                                	           if(!isset($rmDefaults[$descKey]) && isset($resort->$descKey))
                                	           {
                                	               $thisAttr = stripslashes($resort->$descKey);
                                	           }
                                	           else 
                                	           {
                                	               $thisAttr = '';
                                	           }
                                	       }
                                	       
                                	   ?>
                                	   <div class=" edit-resort-group well">
                                	   
                                    	   <div class="row">
                                        		<div class="col-xs-12 col-sm-4">
                                        			<label for="<?=$descKey?>"><?=$descVal?></label>
                                        		</div>
                                        		
                                        		<div class="col-xs-12 col-sm-8 text-right">
                                        			<div class="btn-group">
                                        			<?php
                                            		  foreach($btns as $btnKey=>$btnVal)
                                            		  {
                                            		      $btnstatus[$descKey][$btnKey] = 'default';
                                            		      if($thisBtn[$btnKey] == '1')
                                            		      {
                                            		          $btnstatus[$descKey][$btnKey] = 'primary';
                                            		      }
                                            		?>
                                            			<a href="" class="btn btn-<?=$btnstatus[$descKey][$btnKey]?> <?=$btnKey?> path-btn" data-active="<?=$thisBtn[$btnKey]?>"  data-resort="<?=$resort->ResortID?>"><?=$btnVal?> 
                                                  			<i class="active-status fa fa-<?php if($thisBtn[$btnKey] == '1') echo 'check-';?>square" aria-hidden="true"></i>
                                                  		</a>
                                                  	<?php 
                                            		  }
                                                  	?>
                                              		</div>
                                        		</div>
                                        	</div>
                                        	<div class="row form-group">
                                        		<div class="col-xs-10">
												<?php 
                                        		$textareas = [
                                        		    'AreaDescription',
                                        		    'UnitDescription',
                                        		    'AdditionalInfo',
                                        		    'Description',  
                                        		];
                                        		if(in_array($descKey, $textareas))
                                        		{
                                        		?>
                                                  <textarea name="<?=$descKey?>" class="form-control form-element new-attribute resort-descriptions" rows="4" data-type="<?=$descKey?>" data-resort="<?=$resort->ResortID?>" disabled><?=$thisAttr;?></textarea>
                                                <?php 
                                        		}
                                        		else 
                                        		{
                                                ?>
                                                  <input name="<?=$descKey?>" class="form-control form-element new-attribute resort-descriptions" data-type="<?=$descKey?>" data-resort="<?=$resort->ResortID?>" disabled value="<?=$thisAttr;?>" />
                                                <?php 
                                        		}
                                                ?>                                                
                                                </div>
                                                <div class="col-xs-1" style="cursor: pointer"><i class="fa fa-lock col-xs-1 resort-lock" aria-hidden="true" style="font-size: 20px"></i></div>
                                        	</div>
                                    	</div>
                                	   <?php     
                                	   }
                                	?> 
                                </div>                       		
                            </form>
                          </div>
                          <?php 
                    	}
                          ?>
                    	</div>
                    	
                    	<div class="tab-pane fade tab-padding <?=$activeClass['ada']?>" id="ada">
                    	<?php 
                    	foreach($resortDates['ada'] as $repeatableDate=>$resortAttribute)
                    	{
                    	    $displayDateFrom = '';
                    	    $displayDateTo = '';
                    	    $dates = explode("_", $repeatableDate);
                    	    if(count($dates) == 1 && $dates[0] == '0')
                    	    {
                    	        $displayDateFrom = date('Y-m-d');
                    	    }
                    	    else
                    	    {
                    	        $displayDateFrom = date('Y-m-d', $dates[0]);
                    	        if(isset($dates[1]))
                    	        {
                    	            $displayDateTo = date('Y-m-d', $dates[1]);
                    	        }
                    	    }
                    	?>
                    	  <div class="repeatable well">
                    	  		<div class="clone-group" style="display: none;">
                    	  			<i class="fa fa-copy"></i>
                    	  			<i class="fa fa-times-circle-o" style="margin-left: 10px;" data-type="ada" data-resortid="<?=$resort->ResortID?>"></i>
                    	  		</div>
                    	      	<div id="date-select" style="display: none;">
                                    <div class="filterRow">
                                    	<div class="filterBox">
                                    		<strong>Active Date</strong>
                                    	</div>
                                    </div>
                                    <div class="filterRow">
                                    	<div class="filterBox">
                                    		<input type="date" id="" class=" from-date dateFilterFrom" placeholder="from" value="<?=$displayDateFrom;?>"  data-oldfrom="<?=$displayDateFrom;?>" /><span class="hyphen">-</span>
                                    	</div>
                                    	<div class="filterBox">
                                    		<input type="date" id="" class=" to-date dateFilterTo" placeholder="to" value="<?=$displayDateTo;?>" data-oldto="<?=$displayDateTo;?>"/>
                                    	</div>
                                    	<div class="filterBox">
                                    		<a href="#" class="btn btn-apply date-filter">Apply</a>
                                    	</div>
                                    </div>
                                </div>
							<div class="two-column-grid">

                        	<?php 
                        	$adaAtts = [
                        	    'CommonArea'=>'Common Area Accessibility Features',
                        	    'GuestRoom'=>'Guest Room Accessibility Features',
                        	    'GuestBathroom'=>'Guest Bathroom Accessibility Features',
                        	    'UponRequest'=>'Upon Request',
                        	];
                        	$i = 0;
                        	foreach($adaAtts as $attributeType=>$attributeValue)
                        	{
                        	    $thisAttr = $resortAttribute[$attributeType];
                        	    if(empty($resortAttribute[$attributeType]) && !empty($defaultAttrs[$attributeType]))
                        	    {
                        	        $thisAttr = $defaultAttrs[$attributeType];
                        	    }
                        	?>
                          		<div class=" edit-resort-group well">
                          			<form class="resort-edit" data-parsley-validate class="form-horizontal form-label-left">
                          			    <input type="hidden" name="ResortID" class="resortID" value="<?=$resort->ResortID?>">
                          			    <input type="hidden" name="attributeType" class="attributeType" value="<?=$attributeType?>">
                                		<div class="row">
                                			<div class="col-xs-12 col-sm-6">
                                        			<label for="<?=$resortFeeKey?>"><?=$attributeValue?></label>
                                        	</div>
                                    		<div class="col-xs-12 col-sm-6 text-right">
                                    		</div>
                                		</div>
                                		<ul class="attribute-list">
                                		<?php 
                                		
                                		foreach($thisAttr as $attributeKey=>$attributeItem)
                                		{
                                		?>
                                			<li class="attribute-list-item" id="<?=$attributeType?>-<?=$attributeKey?>" data-id="<?=$attributeKey?>"><?=stripslashes($attributeItem)?><span class="attribute-list-item-remove"><i class="fa fa-times-circle-o"></i></span></li>
                                		<?php    
                                		}
                                		?>
                                		</ul>
                                		<div class="row form-group attribute-group">
                                			<input type="text" class="form-control form-element new-attribute" name="new-attribute" data-type="<?=$attributeType?>" data-resort="<?=$resort->ResortID?>" value="">
                                            <input type="button" class="btn btn-primary insert-attribute" value="Add Attribute" name="add-attribute" />
                                       	</div>
                                	</form>   
                                </div>   
                            <?php 
                        	}
                            ?>  
                        	</div> 
                          </div>  
                          <?php 
                    	}
                          ?>            	
                    	</div>
                    	
                    	<div class="tab-pane fade tab-padding <?=$activeClass['attributes']?>" id="attributes">
                    	<?php 
                    	foreach($resortDates['attributes'] as $repeatableDate=>$resortAttribute)
                    	{
                    	    $displayDateFrom = '';
                    	    $displayDateTo = '';
                    	    $dates = explode("_", $repeatableDate);
                    	    if(count($dates) == 1 && $dates[0] == '0')
                    	    {
                    	        $displayDateFrom = date('Y-m-d');
                    	    }
                    	    else
                    	    {
                    	        $displayDateFrom = date('Y-m-d', $dates[0]);
                    	        if(isset($dates[1]))
                    	        {
                    	            $displayDateTo = date('Y-m-d', $dates[1]);
                    	        }
                    	    }
                    	?>
                    	  <div class="repeatable well">
                    	  		<div class="clone-group" style="display: none;">
                    	  			<i class="fa fa-copy"></i>
                    	  			<i class="fa fa-times-circle-o" style="margin-left: 10px;" data-type="attributes" data-resortid="<?=$resort->ResortID?>"></i>
                    	  		</div>
                    	      	<div id="date-select" style="display: none;">
                                    <div class="filterRow">
                                    	<div class="filterBox">
                                    		<strong>Active Date</strong>
                                    	</div>
                                    </div>
                                    <div class="filterRow">
                                    	<div class="filterBox">
                                    		<input type="date" id="" class=" from-date dateFilterFrom" placeholder="from" value="<?=$displayDateFrom;?>"  data-oldfrom="<?=$displayDateFrom;?>" /><span class="hyphen">-</span>
                                    	</div>
                                    	<div class="filterBox">
                                    		<input type="date" id="" class=" to-date dateFilterTo" placeholder="to" value="<?=$displayDateTo;?>" data-oldto="<?=$displayDateTo;?>"/>
                                    	</div>
                                    	<div class="filterBox">
                                    		<a href="#" class="btn btn-apply date-filter">Apply</a>
                                    	</div>
                                    </div>
                                </div>
							<div class="two-column-grid">

                        	<?php 
                        	$attributes = [
                        	    'UnitFacilities'=>'Unit Facilities',
                        	    'ResortFacilities'=>'Resort Facilities',
                        	    'AreaFacilities'=>'Area Facilities',
                        	    'UnitConfig'=>'Unit Config',
//                         	    'configuration'=>'Conditions',
//                         	    'resortConditions'=>'Resort Conditions',
                        	];
                        	$i = 0;
                        	foreach($attributes as $attributeType=>$attributeValue)
                        	{
                        	    $thisAttr = $resortAttribute[$attributeType];
                        	    if(empty($resortAttribute[$attributeType]) && !empty($defaultAttrs[$attributeType]))
                        	    {
                        	        $thisAttr = $defaultAttrs[$attributeType];
                        	    }
                        	?>
                          		<div class=" edit-resort-group well">
                          			<form class="resort-edit" data-parsley-validate class="form-horizontal form-label-left">
                          			    <input type="hidden" name="ResortID" class="resortID" value="<?=$resort->ResortID?>">
                          			    <input type="hidden" name="attributeType" class="attributeType" value="<?=$attributeType?>">
                                		<div class="row">
                                			<div class="col-xs-12 col-sm-6">
                                        			<label for="<?=$resortFeeKey?>"><?=$attributeValue?></label>
                                        	</div>
                                    		<div class="col-xs-12 col-sm-6 text-right">
                                    		</div>
                                		</div>
                                		<ul class="attribute-list">
                                		<?php 
                                		
                                		foreach($thisAttr as $attributeKey=>$attributeItem)
                                		{
                                		?>
                                			<li class="attribute-list-item" id="<?=$attributeType?>-<?=$attributeKey?>" data-id="<?=$attributeKey?>"><?=stripslashes($attributeItem)?><span class="attribute-list-item-remove"><i class="fa fa-times-circle-o"></i></span></li>
                                		<?php    
                                		}
                                		?>
                                		</ul>
                                		<div class="row form-group attribute-group">
                                			<input type="text" class="form-control form-element new-attribute" name="new-attribute" data-type="<?=$attributeType?>" data-resort="<?=$resort->ResortID?>" value="">
                                            <input type="button" class="btn btn-primary insert-attribute" value="Add Attribute" name="add-attribute" />
                                       	</div>
                                	</form>   
                                </div>   
                            <?php 
                        	}
                            ?>  
                        	</div> 
                          </div>  
                          <?php 
                    	}
                          ?>            	
                    	</div>
                    	<div class="tab-pane fade tab-padding <?=$activeClass['images']?>" id="images">
                    		<form class="resort-edit" data-parsley-validate class="form-horizontal form-label-left">
                          		<input type="hidden" name="ResortID" class="resortID" value="<?=$resort->ResortID?>">
                        		<?php 
                        		$images = json_decode($resort->images);
                        		?>
                        		<ul class="three-column-grid images-list images-sortable">
                        			<?php 
                        			foreach($images as $imageKey=>$imageInfo)
                        			{
                        			    $image = $imageInfo->src;
                        			    $image_video = '';
                        			    if(isset($imageInfo->video) && !empty($imageInfo->video))
                        			    {
                        			        $image_video = $imageInfo->video;
                        			    }
                        			    
                        			?>
                        			<li class="sortable-image well" id="image-<?=$imageKey?>" data-id="<?=$imageKey?>">
                        				<img src="<?=$image?>" class="resort-set-image" />
                        				<?php 
                        				if($imageInfo->type == 'uploaded')
                        			    {
                        			        //we can get the alt and title for this image
                        			        $image_alt = get_post_meta( $imageInfo->id, '_wp_attachment_image_alt', true);
                        			        $image_video = get_post_meta( $imageInfo->id, 'gpx_image_video', true);
                        			        $image_title = get_the_title($imageInfo->id);
                        			    ?>
                        			    <br />
                        			    <div class="image-attr-row">
                            			    <label>Alt: </label>
                            				<input type="text" name="alt" class="image_alt" value="<?=$image_alt?>" data-id="<?=$imageInfo->id?>" />
                        			    </div>
                        			    <div class="image-attr-row">
                            			    <label>Title: </label>
                        				<input type="text" name="title" class="image_title" value="<?=$image_title?>" data-id="<?=$imageInfo->id?>"  />
                        			    </div>
                        			    <div class="image-attr-row">
                            			    <label>Video: </label>
                        				<input type="text" name="video" class="image_video" value="<?=$image_video?>" data-id="<?=$imageInfo->id?>"  />
                        			    </div>
                        				
                        				<?php 
                        			    }
                        				?>
                        				<input type="hidden" class="image-input" name="resortImages[]" value="<?=$image?>" />
                    					<i class="fa fa-times-circle"></i>
                					</li>
                        			<?php 
                        			}
                        			?>
                        		</ul>
                        	</form>
                        	<h2>Upload An Image</h2>
                            <!-- Form to handle the upload - The enctype value here is very important -->
                            <form  method="post" enctype="multipart/form-data">
                                    <input type='file' id='upload_image' name='new_image'></input>
                                    <input type="text" id="image_alt" name="alt" placeholder="Alt Text"><br />
                                    <input type="text" id="image_title" name="title" placeholder="Title Text"><br />
                                    <input type="text" id="image_video" name="video" placeholder="Video URL"> (optional YouTube video)
                                    <?php submit_button('Upload') ?>
                            </form>
                    	</div>
                    	<div class="tab-pane fade tab-padding <?=$activeClass['resort-fees']?>" id="resort-fees">
 						<?php 
                    	foreach($resortDates['fees'] as $repeatableDate=>$resortAttribute)
                    	{
                    	    $displayDateFrom = '';
                    	    $displayDateTo = '';
                    	    $dates = explode("_", $repeatableDate);
                    	    if(count($dates) == 1 && $dates[0] == '0')
                    	    {
                    	        $displayDateFrom = date('Y-m-d');
                    	    }
                    	    else
                    	    {
                    	        $displayDateFrom = date('Y-m-d', $dates[0]);
                    	        if(isset($dates[1]))
                    	        {
                    	            $displayDateTo = date('Y-m-d', $dates[1]);
                    	        }
                    	    }
                    	    ?>
                    	  <div class="repeatable well">
                    	  		<div class="clone-group">
                    	  			<i class="fa fa-copy"></i>
                    	  			<i class="fa fa-times-circle-o" style="margin-left: 10px;" data-type="fees" data-resortid="<?=$resort->ResortID?>"></i>
                    	  		</div>
                    	      	<div id="date-select">
                                    <div class="filterRow">
                                    	<div class="filterBox">
                                    		<strong>Check In Date</strong>
                                    	</div>
                                    </div>
                                    <div class="filterRow">
                                    	<div class="filterBox">
                                    		<input type="date" id="" class=" from-date dateFilterFrom" placeholder="from" value="<?=$displayDateFrom;?>" data-oldfrom="<?=$displayDateFrom;?>" /><span class="hyphen">-</span>
                                    	</div>
                                    	<div class="filterBox">
                                    		<input type="date" id="" class=" to-date dateFilterTo" placeholder="to" value="<?=$displayDateTo;?>" data-oldto="<?=$displayDateTo;?>" />
                                    	</div>
                                    	<div class="filterBox">
                                    		<a href="#" class="btn btn-apply rf-date-filter">Apply</a>
                                    	</div>
                                    </div>
                                </div>
                    		<div class="two-column-grid">
                            	<?php 
                                /*
                                 * all other resort fees
                                 */
                                ?>                	
                        		
                          		<div class="edit-resort-group well">
                          			<form class="resort-edit" data-parsley-validate class="form-horizontal form-label-left">
                          			    <input type="hidden" name="ResortID" class="resortID" value="<?=$resort->ResortID?>">
                          			    <input type="hidden" name="attributeType" class="attributeType" value="resortFees">
                                		<div class="row">
                                			<div class="col-xs-12 col-sm-4">
                                        			<label for="<?=$resortFeeKey?>">Resort Fees</label>
                                        	</div>
                                    		<div class="col-xs-12 col-sm-8 text-right">
                                    		</div>
                                		</div>
                                		<ul class="attribute-list">
                                		<?php 
                                		$attributeType = 'resortFees';
                                		$resortFees = json_decode($resort->$attributeType);
                                		foreach($resortFees->$repeatableDate as $resortFeeKey=>$resortFeeItem)
                                		{
                                		?>
                                			<li class="attribute-list-item" id="<?=$attributeType?>-<?=$resortFeeKey?>" data-id="<?=$resortFeeKey?>" data-fee="<?=stripslashes($resortFeeItem)?>"><?=stripslashes($resortFeeItem)?><span class="attribute-list-item-remove"><i class="fa fa-times-circle-o"></i></span></li>
                                		<?php    
                                		}
                                		?>
                                		</ul>
                                		<div class="row form-group attribute-group">
                                    			<input type="text" class="form-control form-element new-attribute" name="new-attribute" data-type="<?=$attributeType?>" data-resort="<?=$resort->ResortID?>" value="">
                                            <input type="button" class="btn btn-primary insert-attribute" value="Add Fee" name="add-attribute" />
                                       	</div>
                                	</form>   
                                </div> 
                                <?php 
                        		/*
                        		 * guest fees
                        		 */
                        		?>
                          			
                          		<div class="edit-resort-group well">
                          			<form class="resort-edit fees-group" data-parsley-validate class="form-horizontal form-label-left">
                          			   <input type="hidden" name="ResortID" value="<?=$resort->ResortID?>">  
                                	   <div class="row">
                                	   
                                    	<?php 
                                    	   $resortFees = [
                                    	       'ExchangeFeeAmount' => 'Exchange Fee',
                                    	       'RentalFeeAmount' => 'Rental Fee',
                                    	       'CPOFeeAmount' => 'CPO Fee',
                                    	       'GuestFeeAmount' => 'Guest Fee',
                                    	       'UpgradeFeeAmount' => 'Upgrade Fee',
                                    	   ];
                                    	   foreach($resortFees as $resortFeeKey=>$resortFeeVal)
                                    	   {
                                    	       $attrDates = json_decode($resort->$resortFeeKey);
                                    	       $thisAttrs = $attrDates->$repeatableDate;
                                    	       $thisAttr = end($thisAttrs);
                                    	   ?>
                                    	   
                                        	   
                                            		<div class="col-xs-12 col-sm-4">
                                            			<label for="<?=$resortFeeKey?>"><?=$resortFeeVal?></label>
                                            		</div>
                                            		
                                            		<div class="col-xs-12 col-sm-8 text-right">
                                            		</div>
                                            	<div class="row form-group attribute-group">
                                            		<div class="col-xs-10">
                                            			<input type="text" class="form-control form-element resort-general-edit new-attribute" name="<?=$resortFeeKey?>" value="<?=$thisAttr;?>" data-type="<?=$resortFeeKey?>" data-resort="<?=$resort->ResortID?>"  disabled>
                                                    </div>
                                                    <div class="col-xs-1" style="cursor: pointer"><i class="fa fa-lock col-xs-1 resort-lock" aria-hidden="true" style="font-size: 20px"></i></div>
                                                </div>
                                    	   <?php     
                                    	   }
                                    	?>   
                            	    	</div>
                            	    </form>
                                </div> 
                              </div>
                          </div> 
                          <?php 
                    	}
                          ?>                 	
                    	</div>
                    	<div class="tab-pane fade tab-padding  <?=$activeClass['unittype']?>" id="unittype">
							<div class="row">
								<div class="col-xs-12 col-sm-7">
									<?php 
									$neworedit = "Add";
									$uname = '';
									if(isset($_GET['unitID']))
									{
									    $unitID = $_GET['unitID'];
									    $thisUnit = $unit_types[$unitID];
									    $uname = $thisUnit->name;
									    $ubedrooms = $thisUnit->number_of_bedrooms;
									    $usleeps = $thisUnit->sleeps_total;
									    $neworedit = "Edit";
									}
									?>
    							    <form id="unitTypeadd" data-parsley-validate class="form-horizontal form-label-left usage_exclude">
                                        <input type="hidden" name="resort_id" id="resort_id" value="<?=$resort->id?>" />
                                        <input type="hidden" name="unit_id" id="unit_id" value="<?=$unitID?>" />
                                        <div id="usage-add" class="usage_exclude" data-type="usage">
                                          <div class="form-group">
                                          	<h4><?=$neworedit?> Unit Type</h4>
                                          </div>
                                          <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Name<span class="required">*</span>
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                              <input type="text" id="name" name="name" required="required" class="form-control col-md-7 col-xs-12" value="<?=$uname?>">
                                            </div>
                                          </div>
                                          <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Number of Bedrooms<span class="required">*</span>
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                            	<select name="number_of_bedrooms" id="number_of_bedrooms" class="form-control">
                                            	<?php 
                                            	$uoptions = [
                                            	    'STD',
                                            	    '1',
                                            	    '2',
                                            	    '3',
                                            	];
                                            	foreach($uoptions as $op)
                                            	{
                                            	    $selected = '';
                                            	    if(isset($ubedrooms) && $ubedrooms == $op)
                                            	    {
                                            	        $selected = 'selected="selected"';
                                            	    }
                                            	?>
                                            		<option <?=$selected?>><?=$op?></option>
                                            	<?php 
                                            	}
                                            	?>
                                            	</select>
                                            </div>
                                          </div>
                                          <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Sleeps Total<span class="required">*</span>
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                           	  <select id="sleeps_total" name="sleeps_total" class="form-control">
                                           	  <?php 
                                            	for($i=2; $i <= 12; $i++)
                                            	{
                                            	    $selected = '';
                                            	    if(isset($usleeps) && $usleeps == $i)
                                            	    {
                                            	        $selected = 'selected="selected"';
                                            	    }
                                            	?>
                                            		<option <?=$selected?>><?=$i?></option>
                                            	<?php 
                                            	}
                                            	?>
                                           	  </select>
                                            </div>
                                          </div>
                                          <div class="form-group">
                                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                              <button id="unitTypeaddsubmit" type="submit" class="btn btn-success">Submit <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                                              <a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=resorts_edit&id=<?=$_GET['id']?>" class="btn btn-secondary">Cancel</a>
                                            </div>
                                          </div>                   
                                        </div>
                                    </form>
								</div>
								<div class="col-xs-5">
									<h3>Unit Types</h3>
									<ul>
									<?php 
									foreach($unit_types as $utK=>$unit_type)
									{
									?>
										<li style="margin-bottom: 15px;">
											<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=resorts_edit&id=<?=$_GET['id']?>&unitID=<?=$utK?>"><?=$unit_type->name?> <i class="fa fa-pencil"></i></a>
											&nbsp;&nbsp;<a href="#" class="delete-unit" style="color: #f00;" data-id="<?=$utK?>"><i class="fa fa-remove"></i></a>
										</li>
									<?php 
									}
									?>
									</ul>
								</div>
							</div>
						</div>
                    	<div class="tab-pane fade tab-padding  <?=$activeClass['resort-settings']?>" id="resort-settings">
							<div class="row">
								<div class="col-xs-12 title_right">
								<?php 
								$settings = [
								    'active-resort'=> [
								        'name'=>'Active',
								        'type' => 'checkbox',
								        'var' => 'active',
								    ],
								    'is-gpr'=> [
								        'name'=>'GPR',
								        'type' => 'checkbox',
								        'var'=>'gpr',
								    ],
								    'featured-resort'=> [
								        'name'=>'Featured',
								        'type' => 'checkbox',
								        'var'=>'featured',
								    ],
								    'ai-resort'=> [
								        'name'=>'All Inclusive',
								        'type' => 'checkbox',
								        'var' => 'ai',
								    ],
								    'guest-fees'=> [
								        'name'=>'Guest Fees Enabled',
								        'type' => 'checkbox',
								        'var' => 'guestFeesEnabled',
								    ],
								    'reload-resort'=> [
								        'name'=>'Manually Refresh Resort Cache',
								        'type' => 'button',
								        'var' => 'ResortID',
								    ],
								    'taxMethod'=> [
								        'name'=>'Tax Method (from price set)',
								        'type' => 'radio',
								        'class' => '',
								        'options' => [
								                'taxAdd'=>'Add',
								                'taxDeduct'=>'Deduct',
								        ],
								    ],
								    'taxID'=> [
								        'name'=>'Resort Tax',
								        'type' => 'select',
								        'custom' => true,
								    ],
								    'taID'=> [
								        'name'=>'Trip Advisor ID ',
								        'type' => 'buttonContent',
								        'var' => 'taID',
								    ],
								    'featured-resort',
								    'ai-resort'
								];
								
								foreach($settings as $sKey=>$sVal)
								{
								    $btnStatus = 'default';
								    $var = $sVal['var'];
								    
								    if($resort->$sVal['var'] == 1)
								    {
								        $btnStatus = 'primary';
								    }
								    ?>
								    <div class="row">
								    	<div class="col-xs-12 resort-settings-action">
								    <?php 
								    if($sVal['type'] == 'checkbox')
								    {
								    ?>
								    <a href="" class="btn btn-<?=$btnStatus?>" id="<?=$sKey?>" data-active="<?=$resort->$var?>" data-resort="<?=$resort->ResortID?>"><?=$sVal['name']?> 
                              			<i class="active-status fa fa-<?php if($resort->$var == '1') echo 'check-';?>square" aria-hidden="true"></i>
                              		</a>
								    <?php 
								    }
								    
								    if($sVal['type'] == 'button')
								    {
								        $btnStatus = 'primary';
								    ?>
                              		<a href="" class="btn btn-<?=$btnStatus?>" id="<?=$sKey?>" data-resort="<?=$resort->$var?>"><?=$sVal['name']?></a><br>
								    <?php    
								    }
								    
								    if($sVal['type'] == 'radio')
								    {
								    ?>
                              		<div class="row" style="margin-bottom: 5px;">
                              			<div class="col-xs-12 resort-settings-action">
                              				<label class="control-label">Tax Method (from price set)</label> 
                              				<div class="btn-group cg-btn-group" data-toggle="buttons">
                              					<label class="btn btn-<?php if($resort->taxMethod == 1) echo 'primary'; else echo 'default';?>">
                              						<input type="radio" data-toggle="toggle tax-method" data-resort="<?=$resort->ResortID?>" id="taxAdd" name="taxMethod" value="1" <?php if($resort->taxMethod == 1) echo 'checked';?>> Add
                              					</label>
                              					<label class="btn btn-<?php if($resort->taxMethod == 2) echo 'primary'; else echo 'default';?>">
                              						<input type="radio" data-toggle="toggle tax-method" data-resort="<?=$resort->ResortID?>" id="taxDeduct" name="taxMethod" value="2" <?php if($resort->taxMethod == 2) echo 'checked';?>> Deduct 
                              					</label>
                              				</div>
                              			</div>
                              		</div>								    
								    <?php     
								    }
								    
								    if($sVal['type'] == 'select')
								    {
								    ?>
                              		<div class="row" style="margin-bottom: 5px;">
                              			<div class="col-xs-12 resort-settings-action">
                              				<label class="control-label">Resort Tax</label>
                              				<select name="taxID" id="taxID" class="selectpicker" data-resort="<?=$resort->ResortID?>">
                              					<optgroup label="Existing">
                              						<option></option>
                              						<?php 
                              						foreach($resort->taxes as $tax)
                              						{
                              						?>
                              						<option value="<?=$tax->ID?>" <?php if($tax->ID == $resort->taxID) echo 'selected';?>><?=$tax->TaxAuthority?> <?=$tax->City?> <?=$tax->State?> <?=$tax->Country?></option>
                              						<?php 
                              						}
                              						?>
                              					</optgroup>
                              					<optgroup label="New" class="newTax">
                              						<option value="new">New</option>
                              					</optgroup>
                              				</select>
                              			</div>
                              		</div>								    
								    <?php  
								    }
								    
								    if($sVal['type'] == 'buttonContent')
								    {
								    ?>
                              		<div class="row">
                              			<div class="col-xs-12 resort-settings-action">
                              				       <button type="button" id="btn-ta" class="btn btn-primary" data-toggle="modal" data-target="#modal-ta">
                                                      <?=$sVal['name']?> <span class="taID"><?=$resort->$var?></span>
                                                   </button>
                              			</div>
                              		</div>								    
								    <?php 
								    }
								    ?>
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
         </div>
       </div>
       <div class="modal" id="modal-ta" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Trip Advisor Update</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
              	<div class="row">
              		<div class="col-xs-12 col-sm-6">
              			<?=$resort->ResortName?><br>
              			<?=$resort->Address1?><br>
              			<?=$resort->Town?>, <?=$resort->Region?>
              		</div>
              		<div class="col-xs-12 col-sm-6">
              		    <div class="row form-group">
                  			<div class="col-xs-12 text-right">Current ID: <span class="taID"><?=$resort->taID?></span></div>
                  		</div>
                  		<div class="row form-group">
                  			<div class="col-xs-12 text-right">
                  				<label for="coords">Coordinates</label> <input type="text" id="coords" value="<?=$resort->LatitudeLongitude?>">
                  			</div>
                  		</div>
              		</div>
          		</div>
          		<div class="row form-group">
          			<div class="col-xs-12 text-center">
          				<button id="taRefresh" class="btn btn-primary" data-rid="<?=$resort->id?>" >Refresh</button>
          			</div>
          		</div>
          		<div class="row form-group">
          			<div class="col-xs-12 text-center" id="refresh-return">
          				
          			</div>
          		</div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
        <?php 
        foreach($defaultModals as $dmKey=>$dmVal)
        {
        ?>
            <div id="myModal" class="modal fade" role="dialog">
              <div class="modal-dialog">
            
                <!-- Modal content-->
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Default <?=$dmVal['type']?></h4>
                  </div>
                  <div class="modal-body">
                    <p><?=$dmVal['desc']?></p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
                </div>
            
              </div>
            </div>        
        <?php 
        }
        ?>
       <?php include $dir.'/templates/admin/footer.php';?>