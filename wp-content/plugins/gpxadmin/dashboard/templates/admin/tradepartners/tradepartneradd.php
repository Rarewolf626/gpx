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
                         <div id="tradepartner-add-result"></div>
                    <form id="tradepartner-add"  class="form-horizontal form-label-left usage_exclude" method="POST" action="<?php echo admin_url('admin-ajax.php?action=gpx_partner_add')?>">
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tp_sf_account_id">Salesforce Account ID <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="tp_sf_account_id" name="sf_account_id" class="form-control col-md-7 col-xs-12" value="" required>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tp_name">Name <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="tp_name" name="name" class="form-control col-md-7 col-xs-12" value="" required>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tp_username">Username <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="tp_username" name="username" class="form-control col-md-7 col-xs-12" required>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tp_email">Email <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="email" id="tp_email" name="email" class="form-control col-md-7 col-xs-12" value="" required>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tp_phone">Phone
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="tel" id="tp_phone" name="phone" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tp_address">Address
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="tp_address" name="address" class="form-control col-md-7 col-xs-12" value="">
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
