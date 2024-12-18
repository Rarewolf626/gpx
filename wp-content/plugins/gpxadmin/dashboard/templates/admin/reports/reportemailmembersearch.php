<?php

extract($static);
extract($data);
include $dir.'/templates/admin/header.php';

?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Special Request Reports Email</h3>
              </div>
              <?php if(current_user_can('administrator_plus')):?>
              <div class="pull-right">
              	<h5>Send Emails
					<?php 
                  	$gfActive = get_option('gpx_global_cr_email_send');
                  	if($gfActive == 1)
                  	{
                  	?>
                  	<span class="badge btn-success" id="activeCREmail" data-active="0">Active</span>
                  	<?php 
                  	}
                  	else 
                  	{
                  	?>
                  	<span class="badge btn-danger" id="activeCREmail" data-active="1">Inactive</span>
                  	<?php 
                  	}
                  	?>
                 </h5>
              </div>
              <?php endif;?>
            </div>
                        
            <div class="clearfix"></div>
            <div class="row">
              <div class="col-md-12">
                 	<div class="panel panel-default">
                 		<div class="panel-heading">
                 		  <h2>Edit Member Search CSV Report Email</h2> 
                 		</div>
                 		<div class="panel-body">	
                            <div class="row">
                            	<div class="col-xs-12">
                            		<form name="custom-request-form" method="POST">
                            			<div class="row" style="margin-bottom: 20px;">
                            				<div class="col-xs-12">
                            					<label>Email To (comma spaced)</label>
                            					<input type="text" class="form-control" name="msEmailTo" value="<?=$msemailTo;?>" placeholder="To Email">
                            				</div>
                            			</div>
                            			<div class="row" style="margin-bottom: 20px;">
                            				<div class="col-xs-12">
                            					<label>Email From</label>
                            					<input type="text" class="form-control" name="msEmail" value="<?=$msemail;?>" placeholder="From Email">
                            				</div>
                            			</div>
                            			<div class="row" style="margin-bottom: 20px;">
                            				<div class="col-xs-12">
                            					<label>Email From Name</label>
                            					<input type="text" class="form-control" name="msEmailName" value="<?=$msemailName;?>" placeholder="From Email Name">
                            				</div>
                            			</div>
                            			<div class="row" style="margin-bottom: 20px;">
                            				<div class="col-xs-12">
                            					<label>Subject</label>
                            					<input type="text" class="form-control" name="msEmailSubject" value="<?=$msemailSubject;?>" placeholder="Email Subject">
                            				</div>
                            			</div>
                            			<div class="row" style="margin-bottom: 20px;">
                            				<div class="col-xs-12">
                            					<label>Days to Report</label>
                            					<input type="text" class="form-control" name="msEmailDays" value="<?=$msemailDays;?>" placeholder="Number of days to report">
                            				</div>
                            			</div>
                            			<div class="row">
                            				<div class="col-xs-12">
                            					<label>Message</label>
                            					<?php wp_editor( stripslashes($msemailMessage), 'membersearchemail', array('textarea_name'=>'msEmailMessage') ); ?>
                            				</div>
                            			</div>
                            			<div class="row" style="margin-top: 20px;">
                            				<div class="col-xs-12">
                            					<input type="submit" name="submit-custom-request-email" class="btn btn-primary" value="Update">
                            				</div>
                            			</div>
                            		</form>
                                </div>
                            </div> 
                 		</div>
                 	</div>                
              
              </div>
         	</div>
         </div>
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>