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
                <h3>Add User</h3>
              </div>
            </div>
                        
            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
              
                     <div class="x_content">
                    <br />
                    <form id="ownerAdd" data-parsley-validate class="form-horizontal form-label-left">

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DAEMemberNo">EMS Account ID <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="DAEMemberNo" required="required" name="DAEMemberNo" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                      <div class="form-group">
                      	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Ownership Week Type <span class="required">*</span>
                      	</label>
                      	<div class="input-group owner-add-group col-md-6 col-sm-6 col-xs-12">
                      		<input type="text" name="RMN" placeholder="Resort Member Number" required="required" class="form-control">
                      		<select name="OwnershipWeekType" class="form-control">
                      			<option>Standard</option>
                      			<option>Even</option>
                      			<option>Odd</option>
                      		</select>
                      	</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="password">Password <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="password" name="password" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button type="submit" class="btn btn-primary">Cancel</button>
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