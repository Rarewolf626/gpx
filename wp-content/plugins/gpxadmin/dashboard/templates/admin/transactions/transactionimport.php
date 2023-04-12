<?php

extract($static);
extract($data);
include $dir.'/templates/admin/header.php';

?>
        <div class="right_col" role="main">
      	<?php 
      	if(isset($msg))
      	{
      	    ?>
      	    <div class="update-nag relative-nag <?=$msg['type']?>" style="display: block;"><?=$msg['text']?></div>
      	    <?php 
      	}
      	?>
          <div class="" id="admin-modal-content">

            <div class="page-title">
              <div class="title_left">
                <h3>Import Transactions</h3>
              </div>

              <div class="title_right">
                <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search">
                </div>
              </div>
            </div>
                        

            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
                 <div class="x_content">
                    <br />
                    <form id="transactions-import" data-parsley-validate class="form-horizontal form-label-left" method="post"  enctype="multipart/form-data">
                        <?php wp_nonce_field( 'gpx_admin', 'gpx_import_transaction' ); ?>
                        <div class="well">
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="week">Week ID *
        						</label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" name="weekId" required="required" value="<?=$weekId?>" />
        						</div>
        					</div>
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="week">Owner ID *
        						</label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" name="ownerID" required="required" value="<?=$ownerID?>" />
        						</div>
        					</div>
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="week">Resort ID
        						</label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" name="resortID" value="<?=$resortID?>" />
        						</div>
        					</div>
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="week">Depsit ID
        						</label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<input type="text" name="depositID" value="<?=$depositID?>" />
        						</div>
        					</div>
        					<div class="form-group">
        						<label class="control-label col-md-3 col-sm-3 col-xs-12"
        							for="week">Overwrite</label>
        						<div class="col-md-6 col-sm-6 col-xs-12">
        							<select name="overwrite">
        								<option>No</option>
        								<option>Yes</option>
        							</select>
        						</div>
        					</div>
        				</div>
        				<div class="ln_solid"></div>
    					<div class="form-group">
    						<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
    							<a href="" class="btn btn-danger cancel-return">Cancel</a>
    							<button type="submit" class="btn btn-success" id="submit-btn">
    								Submit <i class="fa fa-circle-o-notch fa-spin fa-fw"
    									style="display: none;"></i>
    							</button>
    						</div>
    					</div>
                    </form>
                  </div>           
              </div>
         	</div>
         </div>
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>