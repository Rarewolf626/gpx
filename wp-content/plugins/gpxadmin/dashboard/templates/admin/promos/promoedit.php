<?php
extract($static);
extract($data);
include $dir.'/templates/admin/header.php';
$type = ucfirst($promo->Type);


$metas = array('promoType',
    'usage',
    'stacking',
    'terms',
    'maxCoupon',
    'beforeLogin',
    'GACode',
    'icon',
    'desc',
    'maxValue',
    'resortBlackout',
    'resortTravel'
);
foreach($metas as $meta)
{
    if(!isset($promometa->$meta))
        $promometa->$meta = '';
}
$metadates = array('bookStartDate'=>'m/d/y',
    'bookEndDate'=>'m/d/y',
    'travelStartDate'=>'m/d/y',
    'travelEndDate'=>'m/d/y',
    'flashStart'=>'h:i a',
    'flashEnd'=>'h:i a',
);
foreach($metadates as $metadatekey=>$metadatevalue)
{
    if(isset($promometa->$metadatekey))
    {
        $promometa->$metadatekey = date($metadatevalue, strtotime($promometa->$metadatekey));
    }
    else
        $promometa->$metadatekey = '';
}

?>
        <div class="right_col" role="main">
          <div class="update-nag"></div>
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Edit Special</h3>
              </div>

              <div class="title_right text-right">
              	<button class="btn btn-danger remove-btn" data-id="<?=$promo->id;?>" data-action="add_gpx_promo">Remove <?=$promo->Name?> <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                <?php 
              	if(!empty($master_tied))
              	{
              	?>
              	<h4>This is a master special</h4>
              	<ul>
              	<?php 
                  	foreach($master_tied as $mt)
                  	{
                  	?>
                	<li><a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=promos_edit&id=<?=$mt->id?>" target="_blank"><?=$mt->Name?></a></li>
                  	<?php     
                  	}
              	}
              	?>
              </div>
              </div>
            </div>
                        
            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-8 col-sm-12 col-md-offset-2">
                 <form id="promo-add" data-parsley-validate class="form-horizontal form-label-left well">
                     <input type="hidden" name="specialID" value="<?=$promo->id;?>">
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
								    $selected = '';
								    if($special_master->id == $promo->master)
								    {
								        $selected = 'selected="selected"';
								    }
								?>
								<option value="<?=$special_master->id?>" <?=$selected?>><?=$special_master->Name?></option>
								<?php 
								}
								?>
							</select>
						</div>
					</div>
                     <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="bookingFunnel">Booking Funnel <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<select name="bookingFunnel" id="bookingFunnel" class="form-control col-md-7 col-xs-12">
                        		<?php 
                          		$activeopts = array('No'=>'coupon', 'Yes'=>'promo');
                          		foreach($activeopts as $optvalue=>$activeopt)
                          		{
                          		    $selected = '';
                          		    if($activeopt == $promo->Type)
                          		        $selected = 'selected="selected"';
                          		    echo '<option '.$selected.'>'.$optvalue.'</option>';
                          		}
                          		?>
                        	</select>                        
                        </div>
                     </div>
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Name">Name <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="Name" name="Name" required="required" class="form-control col-md-7 col-xs-12 alphanumeric" value="<?=stripslashes($promo->Name);?>">
                        </div>
                     </div>
                     <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Name" id="promoorcoupon">
                        	<?=$type?> <?php if($type == 'Coupon') echo 'Code'; else echo 'Slug';?> <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="Slug" name="Slug" required="required" class="form-control col-md-7 col-xs-12" value="<?=$promo->Slug?>"><br>
                          <a href="<?=get_permalink('229').$promo->Slug?>" class="" target="_blank"><?=get_permalink('229').$promo->Slug?></a>
                        </div>
                     </div>
                     <div class="form-group promo two4one-hide" <?php if($type == 'Coupon') echo 'style="display: none;"';?>>
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaBeforeLogin">Hide Before Login
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<select name="metaBeforeLogin" id="metaBeforeLogin" class="form-control col-md-7 col-xs-12">
                        		<?php 
                          		$activeopts = array('No'=>'No', 'Yes'=>'Yes');
                          		$beforeLogin = '';
                          		if($type == 'Promo')
                          		    $beforeLogin = $promometa->beforeLogin;
                          		foreach($activeopts as $optkey=>$optvalue)
                          		{
                          		    $selected = '';
                          		    if($optkey == $beforeLogin)
                          		        $selected = 'selected="selected"';
                          		    echo '<option value="'.$optkey.'"'.$selected.'>'.$optvalue.'</option>';
                          		}
                          		?>
                        	</select>                        
                        </div>
                     </div>
                     <div class="form-group promo two4one-hide" sytle="display:none;">
                     	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaGACode">Google Analytics ID
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        	<input type="text" id="metaGACode" name="metaGACode" class="form-control col-md-7 col-xs-12" value="<?=$promometa->GACode?>">
                        </div>
                     </div>
                     <div class="form-group promo two4one-hide" sytle="display:none;">
                     	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaIcon">Slash Through Icon 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        	<input type="text" id="metaIcon" name="metaIcon" class="form-control fapicker col-md-7 col-xs-12" value="<?=$promometa->icon?>">
                        </div>
                     </div>
                     <div class="form-group promo two4one-hide" sytle="display:none;">
                     	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaDesc">Promo Tagging Verbage 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        	<textarea id="metaDesc" name="metaDesc" class="form-control col-md-7 col-xs-12"><?=$promometa->desc?></textarea>
                        </div>
                     </div>
                     <div class="form-group promo two4one-hide" style="display:none;">
                     	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaHighlight">Card Highlighting
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<select id="metaHighlight" name="metaHighlight" class="form-control col-md-7 col-xs-12">
                          		<option></option>
                          		<?php 
                          		$activeopts = array('Highlighted', 'Prevent Highlighting');
                          		foreach($activeopts as $optvalue)
                          		{
                          		    $selected = '';
                          		    if($optvalue == $promometa->highlight)
                          		        $selected = 'selected="selected"';
                          		    echo '<option value="'.$optvalue.'"'.$selected.'>'.$optvalue.'</option>';
                          		}
                          		?>
                            </select>                        
                          </div>
                     </div>
                     <div class="form-group promo two4one-hide" style="display:none;">
                     	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaSlash">Slash Through 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<select id="metaSlash" name="metaSlash" class="form-control col-md-7 col-xs-12">
                          		<option></option>
                          		<?php 
                          		$activeopts = array('Default', 'Force Slash', 'No Slash');
                          		foreach($activeopts as $optvalue)
                          		{
                          		    $selected = '';
                          		    if($optvalue == $promometa->slash)
                          		        $selected = 'selected="selected"';
                          		    echo '<option value="'.$optvalue.'"'.$selected.'>'.$optvalue.'</option>';
                          		}
                          		?>
                            </select>                        
                          </div>
                     </div>
                     <div class="form-group promo two4one-hide" style="display:none;">
                     	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="showIndex">Show on index 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<select id="showIndex" name="showIndex" class="form-control col-md-7 col-xs-12">
                          		<option></option>
                          		<?php 
                          		$activeopts = array('Yes', 'No');
                          		foreach($activeopts as $optvalue)
                          		{
                          		    $selected = '';
                          		    if($optvalue == $promometa->showIndex)
                          		        $selected = 'selected="selected"';
                          		    echo '<option value="'.$optvalue.'"'.$selected.'>'.$optvalue.'</option>';
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
                                if($optvalue == $promometa->availability)
                                        $selected = 'selected="selected"';
                                echo '<option value="' . $optvalue . '"' . $selected . '>' . $optvalue . '</option>';
                            }
                            ?>
                            </select>
						</div>
					 </div>
                     <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaType" ><?=$type;?> Type <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select id="metaType" name="metaType" required="required" class="form-control col-md-7 col-xs-12">
                          		<option></option>
                          		<?php 
                          		$activeopts = array(
                          		    array('type'=>'all', 'val'=>'Pct Off'),
                          		    array('type'=>'all', 'val'=>'Dollar Off'),
                          		    array('type'=>'all', 'val'=>'Set Amt'),
                          		    array('type'=>'Coupon', 'val'=>'BOGO'),
                          		    array('type'=>'Coupon', 'val'=>'BOGOH'),
                                    array('type' => 'Coupon','val' => 'Auto Create Coupon Template -- Pct Off'),
                                    array('type' => 'Coupon','val' => 'Auto Create Coupon Template -- Dollar Off'),
                                    array('type' => 'Coupon','val' => 'Auto Create Coupon Template -- Set Amt'),
                                    array('type' => 'all','val' => '2 for 1 Deposit'),
                          		);
                          		foreach($activeopts as $optvalue)
                          		{
                          		    $selected = '';
                          		    if($optvalue['val'] == $promometa->promoType)
                          		        $selected = 'selected="selected"';
                          		    echo '<option value="'.$optvalue['val'].'"'.$selected.'>'.$optvalue['val'].'</option>';
                          		}
                          		?>
                          </select>
                        </div>
                     </div>
					<div class="form-group two4one-hide" id="acCoupon">
						<label class="control-label col-md-3 col-sm-3 col-xs-12"
							for="acCouponField">Auto Create Coupon </label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input type="checkbox" id="acCouponField" name="acCoupon" value="1" <?php if(isset($promometa->acCoupon) && $promometa->acCoupon == 1) echo 'checked'?>>
						</div>
					</div>
                     <div class="form-group two4one-hide" id="ctSelectRow">
						<label class="control-label col-md-3 col-sm-3 col-xs-12"
							for="ctSelect">Coupon Template </label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<select name="couponTemplate" id="couponTemplate" class="form-control col-md-7 col-xs-12">
								<option value="<?=$promometa->couponTemplate?>" selected></option>
							</select>
						</div>
					</div>
                     <?php 
                     $amountDisabled = '';
                     $promoAmount = '0';
                     if(!empty($promo->Amount))
                         $promoAmount = $promo->Amount;
                     //if($promometa->promoType == 'BOGO' || $promometa->promoType == 'BOGOH')
                         //$amountDisabled = 'disabled="disabled"';
                     ?>
                     <div class="form-group two4one-hide">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Amount"><?=$type?> Amount <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="Amount" name="Amount" required="required" class="form-control col-md-7 col-xs-12" value="<?=$promoAmount;?>" <?=$amountDisabled?>>
                        </div>
                     </div>
					<div class="form-group two4one-hide">
						<label class="control-label col-md-3 col-sm-3 col-xs-12"
							for="metaMinWeekPrice">Week Minimum Cost</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input type="text" id="metaMinWeekPrice" name="metaMinWeekPrice"
								class="form-control col-md-7 col-xs-12" value="<?=$promometa->minWeekPrice?>">
						</div>
					</div>
                     <div class="form-group coupon two4one-hide">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaMaxValue">Max Value
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="metaMaxValue" name="metaMaxValue" class="form-control col-md-7 col-xs-12" value="<?=$promometa->maxValue;?>">
                        </div>
                     </div>
                     <div class="form-group two4one-hide">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Name">Transaction Type <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<select name="metaTransactionType[]"class="form-control col-md-7 col-xs-12" required="required" id="metaTransactionType" multiple>
                        		<option value="any" <?php if((is_array($promometa->transactionType) && in_array('any', $promometa->transactionType)) || $promometa->transactionType == 'any') echo 'selected'?>>Any</option>
                        		<option value="ExchangeWeek" <?php if((is_array($promometa->transactionType) && in_array('ExchangeWeek', $promometa->transactionType)) || $promometa->transactionType == 'ExchangeWeek') echo 'selected'?>>Exchange</option>
                        		<option value="BonusWeek" <?php if((is_array($promometa->transactionType) && in_array('BonusWeek', $promometa->transactionType)) || $promometa->transactionType == 'BonusWeek') echo 'selected'?>>Rental/Bonus</option>
                        		<option value="upsell" <?php if((is_array($promometa->transactionType) && in_array('upsell', $promometa->transactionType)) || $promometa->transactionType == 'upsell') echo 'selected'?>>Upsell Only</option>
                        	</select>                        
                        </div>
                     </div>
                     <div class="form-group upsell two4one-hide" <?php if((is_array($promometa->transactionType) && in_array('upsell', $promometa->transactionType)) || $promometa->transactionType == 'upsell') echo 'style="display: block;"'; else 'style="display: none;'?>>
                     	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaUpsellOptions">Upsell Options
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<select name="metaUpsellOptions[]" class="form-control col-md-7 col-xs-12"  id="metaUpsellOptions" multiple>
                        		<option value=""></option>
                        		<option value="CPO" <?php if(isset($promometa->upsellOptions) && ((is_array($promometa->transactionType) && in_array('CPO', $promometa->upsellOptions)) || $promometa->upsellOptions == 'CPO')) echo 'selected'?>>CPO</option>
                        		<option value="Upgrade" <?php if(isset($promometa->upsellOptions) && ((is_array($promometa->transactionType) && in_array('Upgrade', $promometa->upsellOptions)) || $promometa->upsellOptions == 'Upgrade')) echo 'selected'?>>Upgrade</option>
                        		<option value="Extension Fees" <?php if(isset($promometa->upsellOptions) && ((is_array($promometa->transactionType) && in_array('Extension Fees', $promometa->upsellOptions)) || $promometa->upsellOptions == 'Extension Fees')) echo 'selected'?>>Extension Fees</option>
                        		<option value="Guest Fees" <?php if(isset($promometa->upsellOptions) && ((is_array($promometa->transactionType) && in_array('Guest Fees', $promometa->upsellOptions)) || $promometa->upsellOptions == 'Guest Fees')) echo 'selected'?>>Guest Fees</option>
                        	</select>                        
                        </div>
                     </div>
                     <input type="hidden" name="metaUseExc" id="metaUseExc" value="">
                     <div class="usage-exclusion-group">
                     <?php
                     if(isset($promometa->useExc))
                     {
                         $pmue = str_replace("\r\n ", "", $promometa->useExc);
                         $pmue = str_replace("\t", "", $pmue);
                         
                         echo $pmue;
                     ?>
                     </div>
                     <?php
                     }
                     else
                     {
                     ?>
                     <div class="clone-group well">
                         <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Name">Usage
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-11">
                            	<select name="metaUsage[]" class="form-control col-md-7 col-xs-12 switchmetausage">
                            		<option value="any" <?php if($usage == 'any') echo 'selected'?>>Any</option>
                            		<option value="region" <?php if($usage == 'region') echo 'selected'?>>Region</option>
                            		<option value="resort" <?php if($usage == 'resort') echo 'selected'?>>Resort</option>
                            		<option value="customer" <?php if($usage == 'customer') echo 'selected'?>>Customer</option>
                            	</select>
                            </div>
    						<div class="col-xs-1 add-new">
    							<i class="fa fa-plus" aria-hidden="true"></i>
    						</div>
                         </div>
                         <?php 
                           if(isset($promometa->usage) && $promometa->usage == 'customer')
                           {
                         ?>
                         <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaCustomerResortSpecific">Resort Specific
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <select name="metaCustomerResortSpecific" id="metaCustomerResortSpecific" class="form-control col-md-7 col-xs-12 metaCustomerResortSpecific">
                              	  <option></option>
                              	  <option<?php if($promometa->metaCustomerResortSpecific == "Yes") echo ' selected';?>>Yes</option>
                              	  <option<?php if($promometa->metaCustomerResortSpecific == "No") echo ' selected';?>>No</option>
                              </select>
                            </div>
                          </div>
                        <?php 
                           }
                        ?>
                         <div id="usage-add" class="usage_exclude usage-add" data-type="usage"></div>
                         <div id="rs-add">
                         <?php 
                         if(isset($promometa->specificCustomer) && !empty($promometa->specificCustomer)) echo $promometa->specificCustomer;
                         ?>
                         </div>
                         <div class="form-group<?php if(isset($usage_regionName)) echo ' parent-delete';?>">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Name">
                            	<?php 
                            	   if(isset($usage_regionName))
                            	       echo "Region";
                            	   elseif(isset($usage_resortNames) && !empty($usage_resortNames))
                            	   {
                            	       echo "Resorts";
                            	   }
                            	?>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                            	<?php 
                            	    if(isset($usage_regionName) && !empty($usage_resortNames))
                            	    {
                            	    ?>
                            	    <div class="row form-group parent-delete">
                            	    	<div class="col-md-7 col-xs-11">
                            	    	    <input type="hidden" name="usage_parent[]" value="<?=$promometa->usage_region?>">
                            	    	    <strong>
                            	    	      <?php 
                            	    	        if(isset($parent)) echo $parent." &gt; ";
                            	    	        echo $usage_regionName;
                            	    	      ?>
                            	    	    </strong>
                            	    	</div>
                            	    	<div class="col-xs-1 remove-element newResort" data-type="#switchusage">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </div>
                            	    </div>
                            	    <?php 
                            	    }
                            	    elseif(isset($usage_resortNames) && !empty($usage_resortNames))
                            	    {
                            	        
                            	        foreach($usage_resortNames as $resort)
                            	        {
                            	        ?>
                            		<div class="row form-group parent-delete">
                            			<div class="col-md-7 col-xs-11">
                            				<input type="hidden" name="usage_resort[]" value="<?=$resort->id?>">
                            				<strong><?=$resort->ResortName?></strong>
                            			</div>
                            			<div class="col-xs-1 remove-element">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </div>
                            		</div>        
                            	        <?php 
                            	        }
                            	    ?>
                            	    <div class="row">
                            	    	<div class="col-xs-12 pull-right">
                            	    		<a href="#" class="btn btn-primary newResort" data-type="#switchusage">Add Resort</a>
                            	    	</div>
                            	    </div>
                            	    <?php 
                            	    }
                            	        
                            	?>
                            </div>
                         </div>
                         
                         <div class="ue-blackout col-xs-12 col-sm-6 col-sm-offset-3">
    						<a href="#" class="addBlackoutDates">Add Blackout Dates</a>
    					</div>
    					<?php 
    					if(isset($promometa->resortBlackout) && !empty($promometa->resortBlackout))
    					{
    					    foreach($promometa->resortBlackout as $resortBlackout)
    					    {
    					?>
    					<div class="ue-blackout-fg clear">	
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaFlashStart">Blackout Start  </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" name="metaResortBlackoutStart[]" class="form-control rbodatepicker col-md-7 col-xs-12" value="<?=date('m/d/Y', strtotime($resortBlackout->start))?>">
        						</div>
        					</div>
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaFlashStart">Blackout End  </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" name="metaResortBlackoutEnd[]" class="form-control rbodatepicker col-md-7 col-xs-12" value="<?=date('m/d/Y', strtotime($resortBlackout->end))?>">
        						</div>
        					</div>
    					</div>	
    						<input class="metaResortBlackoutResorts" name="metaResortBlackoutResorts[]" value="<?=impolode(",", $resortBlackout->resorts)?>" type="hidden">
    					<?php    
    					    }
    					}
    					else 
    					{
    					?>
    					<div class="ue-blackout-fg clear">	
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaFlashStart">Blackout Start Date </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text"  name="metaResortBlackoutStart[]"
        								class="form-control rbodatepicker col-md-7 col-xs-12" value="">
        						</div>
        					</div>
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaFlashStart">Blackout End Date </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text"  name="metaResortBlackoutEnd[]"
        								class="form-control rbodatepicker col-md-7 col-xs-12" value="">
        						</div>
        					</div>
    					</div>
                		<div class="boClone"></div>	
    					<?php 
    					}
    					?>
                         
                         <div class="ue-travel col-xs-12 col-sm-6 col-sm-offset-3">
    						<a href="#" class="addTravelDates">Add Specific Travel Dates</a>
    					</div>
    					<?php 
    					if(isset($promometa->resortTravel) && !empty($promometa->resortTravel))
    					{
    					    foreach($promometa->resortTravel as $resortTravel)
    					    {
    					?>
    					<div class="ue-travel-fg clear">	
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaFlashStart">Travel Start  </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" name="metaResortTravelStart[]" class="form-control rbodatepicker col-md-7 col-xs-12" value="<?=date('m/d/Y', strtotime($resortTravel->start))?>">
        						</div>
        					</div>
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaFlashStart">Travel End  </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" name="metaResortTravelEnd[]" class="form-control rbodatepicker col-md-7 col-xs-12" value="<?=date('m/d/Y', strtotime($resortTravel->end))?>">
        						</div>
        					</div>
    					</div>	
    						<input class="metaResortTravelResorts" name="metaResortTravelResorts[]" value="<?=impolode(",", $resortTravel->resorts)?>" type="hidden">
    					<?php    
    					    }
    					}
    					else 
    					{
    					?>
    					<div class="ue-travel-fg clear">	
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaFlashStart">Travel Start Date </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text"  name="metaResortTravelStart[]"
        								class="form-control rbodatepicker col-md-7 col-xs-12" value="">
        						</div>
        					</div>
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaFlashStart">Travel End Date </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text"  name="metaResortTravelEnd[]"
        								class="form-control rbodatepicker col-md-7 col-xs-12" value="">
        						</div>
        					</div>
    					</div>	
    					<?php 
    					}
    					?>
                     </div>
                     <div class="clone-group well">
                         <div class="form-group two4one-hide">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Name">Exclusions 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-11">
                            	<select name="metaExclusions[]" id="switchexclusions" class="form-control col-md-7 col-xs-12 switchmetaexclusions">
                            		<option></option>
                            		<option value="region" <?php if($exclusions == 'region') echo 'selected'?>>Region</option>
                            		<option value="resort" <?php if($exclusions == 'resort') echo 'selected'?>>Resort</option>
                            		<option value="home-resort" <?php if($exclusions == 'home-resort') echo 'selected'?>>Home Resort</option>
                            		<option value="dae" <?php if($exclusions == 'dae') echo 'selected'?>>DAE Inventory</option>
                            	</select>
                            </div>
    						<div class="col-xs-1 add-new">
    							<i class="fa fa-plus" aria-hidden="true"></i>
    						</div>
                         </div>
                         <?php 
                           if(isset($promometa->exclusions) && $promometa->exclusions == 'customer')
                           {
                         ?>
                         <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaCustomerResortSpecific">Resort Specific
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <select name="metaCustomerResortSpecific" id="metaCustomerResortSpecific" class="form-control col-md-7 col-xs-12">
                              	  <option></option>
                              	  <option<?php if($promometa->metaCustomerResortSpecific == "Yes") echo ' selected';?>>Yes</option>
                              	  <option<?php if($promometa->metaCustomerResortSpecific == "No") echo ' selected';?>>No</option>
                              </select>
                            </div>
                          </div>
                        <?php 
                           }
                        ?>
                         <div id="exclusion-add" class="usage_exclude exclusion-add" data-type="exclude"></div>
                         <div id="rs-add"></div>
                         <div class="form-group<?php if(isset($exclude_regionName)) echo ' parent-delete';?>">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Name">
                            	<?php 
                            	   if(isset($exclude_regionName))
                            	       echo "Region";
                            	   elseif(isset($exclude_resortNames))
                            	   {
                            	       echo "Resorts";
                            	   }
                            	?>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                            	<?php 
                            	    if(isset($exclude_regionName))
                            	    {
                            	    ?>
                            	    <div class="row form-group">
                            	    	<div class="col-md-7 col-xs-11">
                            	    	    <input type="hidden" name="exclude_parent[]" value="<?=$promometa->exclude_region?>">
                            	    	    <strong>
                            	    	      <?php 
                            	    	        if(isset($parent)) echo $parent." &gt; ";
                            	    	        echo $exclude_regionName;
                            	    	      ?>
                            	    	    </strong>
                            	    	</div>
                            	    	<div class="col-xs-1 remove-element newResort" data-type="#switchexclusions">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </div>
                            	    </div>
                            	    <?php 
                            	    }
                            	    elseif(isset($exclude_resortNames))
                            	    {
                            	        
                            	        foreach($exclude_resortNames as $resort)
                            	        {
                            	        ?>
                            		<div class="row form-group parent-delete">
                            			<div class="col-md-7 col-xs-11">
                            				<input type="hidden" name="exclude_resort[]" value="<?=$resort->id?>">
                            				<strong><?=$resort->ResortName?></strong>
                            			</div>
                            			<div class="col-xs-1 remove-element">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </div>
                            		</div>        
                            	        <?php 
                            	        }
                            	    ?>
                            	    <div class="row">
                            	    	<div class="col-xs-12 pull-right">
                            	    		<a href="#" data-type="#switchexclusions" class="btn btn-primary newResort">Add Resort</a>
                            	    	</div>
                            	    </div>
                            	    <?php 
                            	    }
                            	        
                            	?>
                            </div>
                         </div>
                     </div>
                     <?php 
                     }
                     ?>
                     <div class="well promo exclusiveWeeksBox">
						<div class="form-group">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="exclusiveWeeks">Exclusive Weeks
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<textarea class="form-control" name="exclusiveWeeks" id="exclusiveWeeks"><?php if(isset($promometa->exclusiveWeeks)) echo $promometa->exclusiveWeeks;?></textarea>
    							<span style="font-size: 10px;">Week ID separated by comma</span>
    						</div>
    					</div>
					 </div>
                     <div class="form-group coupon two4one-hide">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaStacking">Allow Stacking Discount <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<select name="metaStacking" id="metaStacking" class="form-control col-md-7 col-xs-12" required="required">
                        		<?php 
                          		$activeopts = array('No'=>'No', 'Yes'=>'Yes');
                          		foreach($activeopts as $optkey=>$optvalue)
                          		{
                          		    $selected = '';
                          		    if(isset($promometa->stacking) && $optkey == $promometa->stacking)
                          		        $selected = 'selected="selected"';
                          		    echo '<option value="'.$optkey.'"'.$selected.'>'.$optvalue.'</option>';
                          		}
                          		?>
                        	</select>                        
                        </div>
                     </div>
                     <div class="form-group coupon two4one-hide">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaSingleUse">Single Use Per Owner 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<select name="metaSingleUse" id="metaSingleUse" class="form-control col-md-7 col-xs-12">
                        		<?php 
                          		$activeopts = array('No'=>'No', 'Yes'=>'Yes');
                          		foreach($activeopts as $optkey=>$optvalue)
                          		{
                          		    $selected = '';
                          		    if(isset($promometa->singleUse) && $optkey == $promometa->singleUse)
                          		        $selected = 'selected="selected"';
                          		    echo '<option value="'.$optkey.'"'.$selected.'>'.$optvalue.'</option>';
                          		}
                          		?>
                        	</select>                        
                        </div>
                     </div>
                     <div class="form-group coupon two4one-hide" <?php if($type == 'Promo') echo 'style="display: none;"';?>>
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaMaxCoupon">Max Number of Coupons
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="metaMaxCoupon" name="metaMaxCoupon" class="form-control col-md-7 col-xs-12" value="<?php if(isset($promometa->maxCoupon)) echo $promometa->maxCoupon;?>">
                        </div>
                     </div>
    			     <div class="well">
                         <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="StartDate">Start Date  <span class="dateTextSwitch">(Available for Viewing)</span><span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="StartDate" name="StartDate" required="required" class="form-control datepicker col-md-7 col-xs-12" value="<?=date('m/d/y', strtotime($promo->StartDate));?>">
                            </div>
                         </div>
                         <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="EndDate">End Date  <span class="dateTextSwitch">(Available for Viewing)</span><span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="EndDate" name="EndDate" required="required" class="form-control datepicker col-md-7 col-xs-12" value="<?=date('m/d/y', strtotime($promo->EndDate));?>">
                            </div>
                         </div>
                     </div>
    			     <div class="well two4one-hide">
                         <div class="form-group two4one-hide">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaFlashStart">Flash Sale Start Time
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="metaFlashStart" name="metaFlashStart" class="form-control timepicker col-md-7 col-xs-12" value="<?=$promometa->flashStart?>">
                            </div>
                         </div>
                         <div class="form-group two4one-hide">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaFlashEnd">Flash Sale End Time 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="metaFlashEnd" name="metaFlashEnd" class="form-control timepicker col-md-7 col-xs-12" value="<?=$promometa->flashEnd?>">
                            </div>
                         </div>
                     </div>
    			     <div class="well two4one-hide">
                         <div class="form-group two4one-hide">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaBookStartDate">Book Start Date 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="metaBookStartDate" name="metaBookStartDate" class="form-control datepicker col-md-7 col-xs-12" value="<?=$promometa->bookStartDate;?>">
                            </div>
                         </div>
                         <div class="form-group two4one-hide">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaBookEndDate">Book End Date 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="metaBookEndDate" name="metaBookEndDate" class="form-control datepicker col-md-7 col-xs-12" value="<?=$promometa->bookEndDate;?>">
                            </div>
                         </div>
                     </div>
    			     <div class="well two4one-hide">
                         <div class="form-group two4one-hide">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaTravelStartDate">Travel Start Date 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="metaTravelStartDate" name="metaTravelStartDate" class="form-control datepicker col-md-7 col-xs-12" value="<?=$promometa->travelStartDate;?>">
                            </div>
                         </div>
                         <div class="form-group two4one-hide">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaTravelEndDate">Travel End Date 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="metaTravelEndDate" name="metaTravelEndDate" class="form-control datepicker col-md-7 col-xs-12" value="<?=$promometa->travelEndDate;?>">
                            </div>
                         </div>
                     </div>  
                     <div class="blackout-clone-gp">
                     <?php 
                     if(!isset($promometa->blackout))
                     {
                         $promometa->blackout = array('start'=>'');
                     }
                     foreach($promometa->blackout as $blackout)
                     {
                            $start = '';
                            $end = '';
                            if(isset($blackout->start))
                                $start = date('m/d/Y', strtotime($blackout->start));
                            if(isset($blackout->end))
                                $end = date('m/d/Y', strtotime($blackout->end));
                     ?>
						<div class="blackout-clone well two4one-hide">
        					<div class="form-group two4one-hide">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaBlackoutStart">Blackout Start Date </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text"
        								name="metaBlackoutStart[]"
        								class="form-control datepicker col-md-7 col-xs-12" value="<?=$start?>">
        						</div>
        						<div class="col-xs-1 col-sm-offset-2 blackout-clone-btn">
            						<i class="fa fa-plus" aria-hidden="true"></i>
        						</div>
        					</div>
        					<div class="form-group two4one-hide">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="metaBlackoutEnd">Blackout End Date </label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text"
        								name="metaBlackoutEnd[]"
        								class="form-control datepicker col-md-7 col-xs-12" value="<?=$end?>">
        						</div>
        					</div>
    					</div>
    				<?php 
                    }
    				?>
					</div>                   
                     <div class="form-group two4one-hide">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaLeadTimeMin">Lead Time Minimum (days) 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        	<input type="text" id="metaLeadTimeMin" name="metaLeadTimeMin" class="form-control col-md-7 col-xs-12"  value="<?=$promometa->leadTimeMin;?>">
                        </div>
                     </div>
                     <div class="form-group two4one-hide">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaLeadTimeMax">Lead Time Maximum (days) 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="metaLeadTimeMax" name="metaLeadTimeMax" class="form-control col-md-7 col-xs-12"  value="<?=$promometa->leadTimeMax;?>">
                        </div>
                     </div>
                     <div class="form-group promo two4one-hide" sytle="display:none;">
                     	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="metaTerms">Terms & Conditions 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        	<textarea id="metaTerms" name="metaTerms" class="form-control col-md-7 col-xs-12"><?=$promometa->terms?></textarea>
                        </div>
                     </div>
                     <div class="form-group two4one-hide" id="actcFG">
						<label class="control-label col-md-3 col-sm-3 col-xs-12"
							for="Active">Auto Coupon Template TC's
						</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<textarea name="actc" id="actc" style="width: 100%"><?=$promometa->actc?></textarea>
						</div>
					</div>
                     <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Active">Active <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select id="Active" name="Active" required="required" class="form-control col-md-7 col-xs-12">
                          		<option></option>
                          		<?php 
                          		$activeopts = array('1'=>'Yes', '0'=>'No');
                          		foreach($activeopts as $optkey=>$optvalue)
                          		{
                          		    $selected = '';
                          		    if($optkey == $promo->Active)
                          		        $selected = 'selected="selected"';
                          		    echo '<option value="'.$optkey.'"'.$selected.'>'.$optvalue.'</option>';
                          		}
                          		?>
                          </select>
                        </div>
                     </div>
					<div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <a href="" class="btn btn-danger cancel-return">Cancel</a>
                          <button type="submit" class="btn btn-success">Submit <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                        </div>
                      </div>

                    </form>            
              		<h3>Revisions</h3>
              		<?php 
              		if(isset($promo->revisedBy) && !empty($promo->revisedBy))
              		{
              		    $revs = array_reverse((array) json_decode($promo->revisedBy));
              		    echo '<ul>';
              		    foreach($revs as $revK=>$revV)
              		    {
              		        echo '<li><strong>'.$revK.':</strong> '.$revV.'</li>';
              		    }
              		    echo '</ul>';
              		    
              		}
              		?>
              </div>
         	</div>
         </div>
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>