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
                <h3>Inventory</h3>
              </div>
            </div>
                        
            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
              
                     <div class="x_content">
                    <br />
                   <!--  <form id="region-add" data-parsley-validate class="form-horizontal form-label-left usage_exclude">
                        <div id="usage-add" class="usage_exclude" data-type="usage">
                <div class="form-group parent-delete">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-name">Country <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <select name="country" id="country_1" class="form-control col-md-7 col-xs-12">
                                  <option></option>
                              <?php 
                                  foreach($countries as $country)
                                  {
                                  ?>
                                  <option value="<?=$country->CountryID?>"><?=$country->country?></option>
                                  <?php 
                                  }
                              ?>  
                              </select>
                            </div>
                          </div>
                          <div class="form-group insert-above">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Region Name <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="new-region" name="new-region" required="required" class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Display Name
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="display-name" name="display-name" class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>
                          <div class="ln_solid"></div>
                          <div class="form-group">
                            <label class="control-label col-sm-6 col-xs-12 col-md-offset-3">
                              Reassign parent to new region in resorts table<input type="checkbox" class="form-control" name="reassign">
                            </label>
                          </div>
                          <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                              <button type="submit" class="btn btn-success" id="region-submit">Submit <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                            </div>
                          </div>                   
                        </div>
                    </form> -->
                  </div>           
              
              </div>
          </div>
         </div>
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>