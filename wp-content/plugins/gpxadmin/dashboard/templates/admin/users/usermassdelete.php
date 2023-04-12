<?php 

  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';
  
?>
        <div class="right_col" role="main">
        
          
          	<?php 
          	if(isset($updated))
          	{
          	?>
          	    <div class="update-nag shown nag-relative-success">
          	    	<?=$updated?> Removed.
          	    </div>
          	<?php 
          	}
          	if(isset($notFound))
          	{
          	?>
          	    <div class="update-nag shown nag-relative-fail">
          	    	Not Found: <?=implode(",", $notFound)?>
          	    </div>
          	<?php 
          	}
          	?>
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Mass Remove Users</h3>
              </div>
            </div>
                        
            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
              
                     <div class="x_content">
                    <br />
                    <form id="ownerMassDelete" method="post" data-parsley-validate class="form-horizontal form-label-left">

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DAEMemberNo">EMS Account ID's <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        	<textarea name="emsnums" class="form-control col-md-7 col-xs-12"></textarea>
                        </div>
                      </div>
                      <div class="ln_solid"></div>
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