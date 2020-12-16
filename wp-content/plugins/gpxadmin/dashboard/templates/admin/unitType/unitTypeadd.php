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
          <div class="update-nag" id="sucessmessage"></div>
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
                    <form id="unitTypeadd" data-parsley-validate class="form-horizontal form-label-left usage_exclude">
                        <div id="usage-add" class="usage_exclude" data-type="usage">
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-code">Name<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="name" name="name" required="required" class="form-control col-md-7 col-xs-12">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Resort<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <?php 

                              echo '<select id="resort_id" name="resort_id" class="form-control col-md-7 col-xs-12 select2">
                                      <option value="0">Please Select Option</option>';

                                foreach($data['resorts'] as $resort){
                                    
                                echo '<option value="'.$resort->id.'">'.$resort->ResortName.'</option>';
                                    }

                              echo '</select>';

                               ?>
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
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Sleeps no of adults <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="sleeps_no_of_adults" name="sleeps_no_of_adults" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit-region">Sleeps no of children<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                              <input type="text" id="sleeps_no_of_children" name="sleeps_no_of_children" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                          </div>
                          <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                              <button id="unitTypeaddsubmit" type="submit" class="btn btn-success">Submit <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
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