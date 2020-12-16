<?php 

  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';
?>
        <div class="right_col" role="main">
          <div class="update-nag"></div>
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Assign Region to<br><?=$resort->ResortName;?></h3>
              </div>
            </div>
                        
            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
                  <div class="x_content">
                    <br />
                    <form id="region-assign" data-parsley-validate class="form-horizontal form-label-left">
					  <input type="hidden" name="resortid" value="<?=$_GET['id']?>">
					  <input type="hidden" name="orginalRegion" value="<?=$resort->gpxRegionID?>">
					  <div id="usage-add" class="usage_exclude" data-type="usage">
    					  <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-name">Country <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <select name="country" id="country_1" class="form-control col-md-7 col-xs-12">
                              	  <option></option>
                              <?php 
                                  foreach($countries as $cntry)
                                  {
                                  ?>
                              	  <option value="<?=$cntry->CountryID?>" <?php if($cntry->CountryID == $country['id']) echo " selected"?>><?=$cntry->country?></option>
                                  <?php 
                                  }
                              ?>	
                              </select>
                            </div>
                          </div>
                          <?php 
                            $i = 2;
                            foreach($parent as $k=>$par)
                            {
                          ?>
                          <div id="region_<?=$i?>" class="form-group parent-regions parent-delete">
                          	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-name">
                          		Selected Region 
                          		<span class="required">*</span>
                          	</label>
                          	<div class="col-md-6 col-sm-6 col-xs-11">
                          		<select name="usage_parent[]" class="form-control col-md-7 col-xs-12 parent-region">
                          			<option></option>
                          		<?php 
                          		    foreach($listr[$k] as $region)
                          		    {    
                          		?>
                          			<option value="<?=$region->id?>" <?php if($region->id == $par['tid']) echo 'selected';?>><?=$region->region;?></option>
                          		<?php 
                          		    }
                          		?>
                          		</select>
                          	</div>
                          	<div class="col-xs-1 remove-element">
                          		<i class="fa fa-trash" aria-hidden="true"></i>
                          	</div>
                          </div>
                          <?php 
                                $i++;
                                unset($listr[$k]);
                            }
                          ?>
                          <div id="region_<?=$i?>" class="form-group parent-regions parent-delete">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Region Name <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <select name="usage_parent[]" class="form-control col-md-7 col-xs-12 parent-region">
                              	<option></option>
                          		<?php 
                          		    foreach($listr as $thisregion)
                          		    {
                          		        foreach($thisregion as $region)
                              		    {    
                              		?>
                              			<option value="<?=$region->id?>" <?php if($region->id == $resort->gpxRegionID) echo 'selected';?>><?=$region->region;?></option>
                              		<?php 
                              		    }
                          		    }
                          		?>
                          		</select>
                            </div>
                          	<div class="col-xs-1 remove-element remove-region-assign">
                          		<i class="fa fa-trash" aria-hidden="true"></i>
                          	</div>
                          </div>
                          <div class="ln_solid insert-above"></div>
                          
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="hidden-region">Hide Orignial Region <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <select name="hidden-region" class="form-control col-md-7 col-xs-12">
                              	<option>No</option>
                              	<option>Yes</option>
                          		</select>
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                              <button type="submit" class="btn btn-success" id="region-assign-submit">Submit <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                            </div>
                          </div>
					  </div>
                    </form>
                  </div>               
              
              </div>
         	</div>
         </div>
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>