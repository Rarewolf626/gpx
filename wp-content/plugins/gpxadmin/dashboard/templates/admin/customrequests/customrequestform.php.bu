<?php

extract($static);
extract($data);
include $dir.'/templates/admin/header.php';

?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Custom Request Form</h3>
              </div>
            </div>
                        
            <div class="clearfix"></div>
            <div class="row">
              <div class="col-md-12">
                 	<div class="panel panel-default">
                 		<div class="panel-heading">
                 		  <h2>Edit Custom Request Form</h2> 
                 		</div>
                 		<div class="panel-body">	
                            <div class="row">
                            	<div class="col-xs-12">
                            		<form name="custom-request-form" method="POST">
                            			<div class="row">
                            				<div class="col-xs-12">
                            					<?php wp_editor( stripslashes($crform), 'customrequestform', array('media_buttons'=>false, 'textarea_name'=>'crForm', 'tinymce'=>array('toolbar1'=>'')) ); ?>
                            				</div>
                            			</div>
                            			<div class="row" style="margin-top: 20px;">
                            				<div class="col-xs-12">
                            					<input type="submit" name="submit-custom-request-form" class="btn btn-primary" value="Update">
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