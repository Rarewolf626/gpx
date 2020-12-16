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
                <h3><?=$tp->name?></h3>
                
              </div>
            </div>
                        
            <div class="clearfix"></div>
            <div class="row" id="admin-modal-content">
              <div class="col-md-12">
                     <div class="x_content">
                    <br />
                    <form id="tradepartner-edit" data-parsley-validate class="form-horizontal form-label-left usage_exclude" method="POST" action="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=tradepartners_edit&id=<?=$tp->record_id?>">
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Salesforce Account ID <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="tp_name" name="sf_account_id" class="form-control col-md-7 col-xs-12" value="<?=$tp->sf_account_id?>" required>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Name <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="tp_name" name="name" class="form-control col-md-7 col-xs-12" value="<?=$tp->name?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Username <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="tp_username" name="username"  class="form-control col-md-7 col-xs-12" value="<?=$tp->username?>" disabled>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Email <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="tp_email" name="email" class="form-control col-md-7 col-xs-12" value="<?=$tp->email?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Phone <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="tp_phone" name="phone" class="form-control col-md-7 col-xs-12" value="<?=$tp->phone?>">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Address <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="tp_address" name="address" class="form-control col-md-7 col-xs-12" value="<?=$tp->address?>">
                            </div>
                          </div>

                          <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                              <button type="submit" class="btn btn-success">Submit <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                            </div>
                          </div>  
                          <div class="form-group"><h3 id="msg-box"></h3></div>                 
                    </form>
                  </div>           
              
              </div>
          </div>
         </div>
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>