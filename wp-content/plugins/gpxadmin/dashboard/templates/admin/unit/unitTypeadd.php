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
          <div class="update-nag" <?=$shownag?>><?=$message?></div>
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Add Unit Type</h3>
                
              </div>
            </div>
                        
            <div class="clearfix"></div>
            <div class="row">
              <div class="col-md-12">
                     <div class="x_content">
                    <br />
                    <form id="resort-add" data-parsley-validate class="form-horizontal form-label-left usage_exclude" method="POST" action="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=resorts_add">
                        <div id="usage-add" class="usage_exclude" data-type="usage">
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Select Resort<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="name" name="resort_name" required="required" class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Name <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="ResortName" name="ResortName" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Number of bedrooms<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="number_of_bedrooms" name="number_of_bedrooms" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Sleeps total<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="sleeps_total" name="sleeps_total" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">note<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="note" name="note" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          
                          <!-- <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">UnitFacilities <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="UnitFacilities" name="UnitFacilities" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">ResortFacilities <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="ResortFacilities" name="ResortFacilities" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">AreaFacilities <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="AreaFacilities" name="AreaFacilities" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Website <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Website" name="Website" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">AreaDescription <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id=" AreaDescription" name="Website" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Website <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Website" name="Website" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Website <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Website" name="Website" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Website <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Website" name="Website" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div> -->





                          <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                              <button type="submit" class="btn btn-success">Submit <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
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