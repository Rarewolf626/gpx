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
                <h3>Edit <?=$name?></h3>
              </div>
              <div class="title_right text-right">
             <?php if(is_null($RegionID)):?>
              	<button class="btn btn-danger remove-btn" data-id="<?=$selected;?>" data-action="add_gpx_region">Remove <?=$name?> <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                <br><br>
             <?php endif;?>
                 <a href="" class="btn btn-primary" id="show_fees-region" data-show_fees="<?=$show_fees ? '1' : '0'?>" data-region="<?=$selected?>">
                     Show Resort Fees
              			<i class="show_fees-status fa fa-<?php if($show_fees) echo 'check-';?>square" aria-hidden="true"></i>
              	 </a>
                 <a href="" class="btn btn-primary" id="featured-region" data-featured="<?=$featured?>" data-region="<?=$selected?>">Featured
              			<i class="featured-status fa fa-<?php if($featured == '1') echo 'check-';?>square" aria-hidden="true"></i>
              	 </a>
              	 <?php
//               	 echo '<pre>'.print_r($data, true).'</pre>';
              	 ?>
                 <a href="" class="btn btn-primary" id="hidden-region" data-hidden="<?=$hidden?>" data-region="<?=$selected?>">Hidden
              			<i class="hidden-status fa fa-<?php if($hidden == '1') echo 'check-';?>square" aria-hidden="true"></i>
              	 </a>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
              <?php if(is_null($RegionID)):?>
                  <div class="x_content">
                    <br />
                    <form id="region-add" data-parsley-validate class="form-horizontal form-label-left">
					  <input type="hidden" name="id" value="<?=$selected?>">
					  <div class="form-group parent-delete">
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
                      		Parent Region 
                      		<span class="required">*</span>
                      	</label>
                      	<div class="col-md-6 col-sm-6 col-xs-11">
                      		<select name="parent[]" class="form-control col-md-7 col-xs-12 parent-region">
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
                        }
                      ?>
                      <div class="form-group insert-above">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Region Name <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="edit-region" name="edit-region" required="required" class="form-control col-md-7 col-xs-12" value="<?=$name;?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Display Name
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="display-name" name="display-name" class="form-control col-md-7 col-xs-12" value="<?=$displayName;?>">
                        </div>
                      </div>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button type="submit" class="btn btn-success" id="region-submit">Submit <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                        </div>
                      </div>

                    </form>
                  </div>  
              <?php else:?> 
              	  <h4>This is a base Region.  It cannot be edited, you can only make it a featured region.</h4>            
              <?php endif;?>
              </div>
         	</div>
         </div>
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>