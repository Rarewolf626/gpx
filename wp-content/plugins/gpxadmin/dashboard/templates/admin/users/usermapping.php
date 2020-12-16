<?php 

  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';

  global $wpdb;

  $wp_mapuser2oid = $wpdb->get_results("SELECT a.*, b.Room_Type__c FROM `wp_mapuser2oid` a
                                        INNER JOIN wp_owner_interval b on b.unitweek = a.unitweek WHERE a.gpx_user_id = '".$_GET['id']."'");
    if($wp_mapuser2oid){

?>
 <div class="right_col" role="main">
          <div class="update-nag"></div>
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>User Mapping</h3>
              </div>
            </div>
                        
            <div class="clearfix"></div>

            <div class="row" id="mapped">
              <div class="col-md-12">
              
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DAEMemberNo">User ID 
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" id="DAEMemberNo" name="DAEMemberNo" class="form-control col-md-7 col-xs-12" value="<?php echo $wp_mapuser2oid[0]->gpx_user_id; ?>" disabled>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DAEMemberNo">Username</label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="disabled" id="DAEMemberNo" name="DAEMemberNo" class="form-control col-md-7 col-xs-12" value="<?php echo $wp_mapuser2oid[0]->gpx_username; ?>" disabled>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DAEMemberNo">GPR OID
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="disabled" id="DAEMemberNo" name="DAEMemberNo" class="form-control col-md-7 col-xs-12" value="<?php echo $wp_mapuser2oid[0]->gpr_oid; ?>" disabled>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DAEMemberNo">Unit week
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="disabled" id="DAEMemberNo" name="DAEMemberNo" class="form-control col-md-7 col-xs-12" value="<?php echo $wp_mapuser2oid[0]->unitweek; ?>" disabled>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DAEMemberNo">GRP oid interval
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="disabled" id="DAEMemberNo" name="DAEMemberNo" class="form-control col-md-7 col-xs-12" value="<?php echo $wp_mapuser2oid[0]->gpr_oid_interval; ?>" disabled>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DAEMemberNo">Type
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="disabled" id="DAEMemberNo" name="DAEMemberNo" class="form-control col-md-7 col-xs-12" value="<?php echo $wp_mapuser2oid[0]->Room_Type__c; ?>" disabled>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DAEMemberNo">Resort Id
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="disabled" id="DAEMemberNo" name="DAEMemberNo" class="form-control col-md-7 col-xs-12" value="<?php echo $wp_mapuser2oid[0]->resortID; ?>" disabled>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DAEMemberNo">User Status
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="disabled" id="DAEMemberNo" name="DAEMemberNo" class="form-control col-md-7 col-xs-12" value="<?php echo $wp_mapuser2oid[0]->user_status; ?>" disabled>
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DAEMemberNo">   Delinquent
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="disabled" id="DAEMemberNo" name="DAEMemberNo" class="form-control col-md-7 col-xs-12" value="<?php echo $wp_mapuser2oid[0]->Delinquent__c; ?>" disabled>
                        </div>
                      </div>

                      

                    </form>
                  </div>           
              
              </div>
          </div>
         </div>
       </div>
       
       <?php 
}
else{
?>
 <div class="right_col" role="main">
          <div class="update-nag"></div>
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Data not found</h3>
              </div>
            </div>
          </div>
        </div>
  <?php
}
   include $dir.'/templates/admin/footer.php';?>