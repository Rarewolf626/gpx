<?php 

  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';
  
?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>GPX Retargeting Report</h3>
              </div>
            </div>
                        
            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
              	<form name="retargeting" method="post" class="form-horizontal form-label-left" action="/wp-admin/admin-ajax.php?action=gpx_retarget_report">
              		<div class="form-group">
						<label class="control-label col-md-2 col-sm-3 col-xs-12" for="startDate">Start Date <span class="required">*</span>
						</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input id="startDate" name="startDate" required="required" class="form-control datepicker col-md-3 col-xs-12" type="text">
						</div>
					</div>
              		<div class="form-group">
						<label class="control-label col-md-2 col-sm-3 col-xs-12" for="endDate">End Date <span class="required">*</span>
						</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input id="endDate" name="endDate" required="required" class="form-control datepicker col-md-3 col-xs-12" type="text">
						</div>
					</div>
					<div class="form-group">
                        <label class="control-label col-md-2 col-sm-3 col-xs-12" for="bookingComplete">
                        Booking Complete <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        	<select id="bookingComplete" class="form-control col-md-3 col-xs-12" name="bookingComplete">
                        		<option>Yes</option>
								<option>No</option>
							</select>
                        </div>
                    </div>
					<div class="form-group">
						 	<label class="control-label col-md-2 col-sm-3 col-xs-12"></label>
						 	<div class="col-md-6 col-sm-6 col-xs-12">
								<input type="submit" name="submit" value="Submit" class="btn btn-primary">
							</div>
					</div>             	
              	</form>
              </div>
         	</div>
         </div>
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>