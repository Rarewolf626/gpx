<?php

extract($static);
extract($data);
include $dir.'/templates/admin/header.php';

?>
        <div class="right_col" role="main">
          <div class="" id="admin-modal-content">

            <div class="page-title">
              <div class="title_left">
                <h3>Add Transaction</h3>
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
                    <form id="transactions-add" data-parsley-validate class="form-horizontal form-label-left usage_exclude">
                          <table class="table">
                          	<tr>
                          		<th>Owner ID</th>
                          		<th>Week ID</th>
                          		<th>Adults</th>
                          		<th>Children</th>
                          		<th>Week Type</th>
                          		<th>Week Endpoint ID</th>
                          		<th></th>
                          	</tr>
                          	<tr>
                          		<td><input type="text" class="form-control" id="transactionAdd_OwnerID" name="ownerID"></td>
                          		<td><input type="text" class="form-control" name="weekId"></td>
                          		<td><input type="text" class="form-control" name="adults"></td>
                          		<td><input type="text" class="form-control" name="children"></td>
                          		<td>
                          			<select class="form-control" name="weekType">
                          				<option value="ExchangeWeek">Exchange</option>
                          				<option value="BonusWeek">Bonus</option>
                          			</select>
                          		
                          		</td>
                          		<td>
                          			<select class="form-control" name="weekType">
                          				<option>USA</option>
                          				<option>ASA</option>
                          				<option>AUS</option>
                          				<option>EGY</option>
                          				<option>EUR</option>
                          				<option>ZAR</option>
                          			</select>
                          		
                          		</td>
                          		<td>
                          			<a href="#" class="btn btn-success request-book">Book</a>
                          			<a href="#" class="btn btn-primary add-guest" data-toggle="modal" data-target="#guest-details">Add Guest</a>
                          		</td>
                          	</tr>
                          </table> 
                            <div id="guest-details" class="modal fade" role="dialog">
                              <div class="modal-dialog">
                            
                                <!-- Modal content-->
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">Guest Details</h4>
                                  </div>
                                  <div class="modal-body">
                                  	<div class="row">
                                  		<div class="col-xs-12 col-md-6">
                                  			<div class="form-group">
                                  				<label for="FirstName1">First Name</label>
                                  				<input type="text" name="FirstName1" id="FirstName1" class="form-control">
                                  			</div>
                                  			<div class="form-group">
                                  				<label for="LastName1">Last Name</label>
                                  				<input type="text" name="LastName1" id="LastName1" class="form-control">
                                  			</div>
                                  			<div class="form-group">
                                  				<label for="Email">Email</label>
                                  				<input type="text" name="Email" id="Email" class="form-control">
                                  			</div>
                                  			<div class="form-group">
                                  				<label for="HomePhone">Home Phone</label>
                                  				<input type="text" name="HomePhone" id="HomePhone" class="form-control">
                                  			</div>
                                  			<div class="form-group">
                                  				<label for="Mobile">Mobile Phone</label>
                                  				<input type="text" name="Mobile" id="Mobile" class="form-control">
                                  			</div>
                                  			<div class="form-group">
                                  				<label for="SpecialRequest">Special Request</label>
                                  				<textarea name="SpecialRequest" id="SpecialRequest" class="form-control"></textarea>
                                  			</div>
                                  		</div>
                                  		<div class="col-xs-12 col-md-6">
                                  			<div class="form-group">
                                  				<label for="Address1">Street Address</label>
                                  				<input type="text" name="Address1" id="Address1" class="form-control">
                                  			</div>
                                  			<div class="form-group">
                                  				<label for="Address3">City</label>
                                  				<input type="text" name="Address3" id="Address3" class="form-control">
                                  			</div>
                                  			<div class="form-group">
                                  				<label for="Address4">State</label>
                                  				<input type="text" name="Address4" id="Address4" class="form-control">
                                  			</div>
                                  			<div class="form-group">
                                  				<label for="PostCode">ZIP</label>
                                  				<input type="text" name="PostCode" id="PostCode" class="form-control">
                                  			</div>
                                  			<div class="form-group">
                                  				<label for="Address5">Country</label>
                                  				<input type="text" name="Address5" id="Address5" class="form-control">
                                  			</div>
                                  			<?php 
                                  			/*
                                  			 * This isn't needed on this page (it's added above)
                                  			?>
                                  			<div class="form-group">
                                  				<label for="Address5">Adults</label>
                                  				<input type="text" name="Address5" id="Address5" class="form-control">
                                  			</div>
                                  			<div class="form-group">
                                  				<label for="children">Children</label>
                                  				<input type="text" name="children" id="children" class="form-control">
                                  			</div>
                                  			<?php
                                  			*/
                                  			?>
                                  		</div>
                                  	</div>  
                                  </div>
                                  <div class="modal-footer">
                                  	<a href="#" class="btn btn-success request-book">Book</a>
                                    <a href="#" class="btn btn-default" data-dismiss="modal">Close</a>
                                  </div>
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