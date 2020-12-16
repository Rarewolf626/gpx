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
                <h3>Add Resort</h3>
                
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
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Resort ID <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="ResortID" name="ResortID" required="required" class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Resort Name <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="ResortName" name="ResortName" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Website
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Website" name="Website" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <?php 
                          /*
                          ?>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">AlertNote 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="AlertNote" name="AlertNote" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <?php
                          */
                          ?>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Address 1 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Address1" name="Address1" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Address 2 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Address2" name="Address2" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">City 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Town" name="Town" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">State 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Region" name="Region" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                         
                         
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">ZIP 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="PostCode" name="PostCode" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Country 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Country" name="Country" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Phone 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Phone" name="Phone" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Fax
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Fax" name="Fax" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Email 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Email" name="Email" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Check In Days 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="CheckInDays" name="CheckInDays" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Check In Time 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="CheckInEarliest" name="CheckInEarliest" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Check Out Time 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="CheckOutLatest" name="CheckOutLatest" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Airport 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Airport" name="Airport" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Directions 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Directions" name="Directions" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Description 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="Description" name="Description" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Additional Info 
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="AdditionalInfo" name="AdditionalInfo" class="form-control col-md-7 col-xs-12" value="">
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