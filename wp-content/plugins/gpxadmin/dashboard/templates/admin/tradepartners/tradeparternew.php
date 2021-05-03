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
                <h3>Add Trade Partner</h3>
                
              </div>
            </div>
                        
            <div class="clearfix"></div>
            <div class="row">
              <div class="col-md-12">
                     <div class="x_content">
                    <br />
                    <form id="tradepartner-add" data-parsley-validate class="form-horizontal form-label-left usage_exclude" method="POST" action="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=tradepartners_add">
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Username <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="ResortID" name="ResortID" required="required" class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Email <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="EndpointID" name="EndpointID" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Phone <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="ResortName" name="ResortName" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Address <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="WebLink" name="WebLink" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>

                          <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                              <button type="submit" class="btn btn-success">Submit <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                            </div>
                          </div>                   
                    </form>
                  </div>           
              
              </div>
          </div>
         </div>
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>