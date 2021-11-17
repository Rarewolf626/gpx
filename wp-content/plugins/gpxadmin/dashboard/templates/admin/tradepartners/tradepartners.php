<?php 

  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';
  
?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Trade Partners</h3>
              </div>
            </div>
                        
            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
              
               <?php 
                  
                  $admin_url = 'admin-ajax.php?&action=get_gpx_tradepartners';      
                ?>
                 	<div class="panel panel-default">
                 		<div class="panel-heading">
                 		  <h2>All Trade Partners</h2> 
                 		</div>
                 		<div class="panel-body">	
                            <div class="row">
                            	<div class="col-xs-12">
                            		<table data-toggle="table"
                                             data-url="<?=admin_url($admin_url);?>"
                                             data-cache="false"
                                             data-pagination="true"
                                             data-page-size="20"
                                             data-page-list="[10,20,50,100]"
                                             data-sort-name="resort"
                                             data-show-refresh="true"
                                             data-show-toggle="true"
                                             data-show-columns="true"
                                             data-show-export="true"
                                             data-export-data-type="all"
                                             data-export-types="['csv', 'txt', 'excel']"
                                             data-search="true"
                                             data-show-columns="true"
                                             data-filter-control="true"
                                             data-filter-show-clear="true"
                                             data-escape="false">
                						<thead>
                                            <tr>
                                                <th data-field="edit"></th>
                                                <th data-field="name" data-filter-control="input" data-sortable="true">Name</th>
                                                <th data-field="email" data-filter-control="input" data-sortable="true" data-visible="false">Email</th>
                                                <th data-field="phone" data-filter-control="select" data-sortable="true" data-visible="false">Phone</th>
                                                <th data-field="address" data-filter-control="select" data-sortable="true" data-visible="false">Address</th>
                                                <th data-field="rooms_given" data-filter-control="input" data-sortable="true">Rooms Given</th>
                                                <th data-field="rooms_received" data-filter-control="input" data-sortable="true">Rooms Received</th>
                                                <th data-field="trade_balance" data-filter-control="input" data-sortable="true">Trade Balance</th>
                                                <th data-field="holds" data-filter-control="input" data-sortable="true">Holds</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div> 		
                 		</div>
                 	</div>                
              
              </div>
         	</div>
         </div>
       </div>
        <div id="gpxModalBalance" class="modal fade" role="dialog">
          <div class="modal-dialog">
        
            <!-- Modal content-->
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Pay Debit for<span id="debitName"></span></h4>
              </div>
              <div class="modal-body">
                <div class="row">
                	<div class="col-xs-12">
                		<input type="hidden" name="debitID" id="debitID" />
                		<p><strong>Current Balance: $<span id="debitBalance"></span></strong>
                		<p>
                    		<label>Amount</label>
                    		<input type="number" class="form-control col-xs-6" id="debit-adjust" placeholder="100" aria-label="Amount">
                		</p>
                		<p style="padding-top: 35px;"><button class="btn btn-primary" id="debit-submit">Submit</button></p>
                	</div>
                </div>
              </div>
            </div>
          </div>
        </div>
                  <div id="guest-details" class="modal fade" role="dialog">
              <div class="modal-dialog">
            	<form name="update-guest-details" id="update-guest-details" method="POST">
            		<input type="hidden" name="transactionID" id="transactionID" value="">
            		<input type="hidden" name="adminTransaction" value="<?=get_current_user_id()?>">
                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Guest Details</h4>
                      </div>
                      <div class="modal-body">
                      	<div class="row">
                      		<div class="col-xs-12 col-xs-6">
                      			<div class="form-group">
                      				<label for="FirstName1">First Name</label>
                      				<input type="text" name="FirstName1" id="FirstName1" class="form-control" value="">
                      			</div>
                      			<div class="form-group">
                      				<label for="LastName1">Last Name</label>
                      				<input type="text" name="LastName1" id="LastName1" class="form-control" value="">
                      			</div>
                      			<div class="form-group">
                      				<label for="Email">Email</label>
                      				<input type="text" name="Email" id="Email" class="form-control" value="">
                      			</div>
                      			<div class="form-group">
                      				<label for="Phone">Phone</label>
                      				<input type="text" name="Phone" id="Phone" class="form-control" value="">
                      			</div>
                      		</div>
                      		<div class="col-xs-12 col-xs-6">			
                      			<div class="form-group">
                      				<label for="Adults">Adults</label>
                      				<input type="text" name="Adults" id="Adults" class="form-control" value="">
                      			</div>
                      			<div class="form-group">
                      				<label for="Children">Children</label>
                      				<input type="text" name="Children" id="Children" class="form-control" value="">
                      			</div>
                      			<div class="form-group">
                      				<label for="Owner">Owned By</label>
                      				<input type="text" name="Owner" id="Owner" class="form-control" value="<?=$transaction->Owner?>">
                      			</div>
                      		</div>
                      	</div>  
                      </div>
                      <div class="modal-footer">
                      	<button type="submit" class="btn btn-success update-guests">Update</button>
                        <a href="#" class="btn btn-default" data-dismiss="modal">Close</a>
                      </div>
                    </div>
            	</form>
              </div>
            </div> 	
          <div id="cancelled-transactions" class="modal fade" role="dialog">
              <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Cancellation Details</h4>
                      </div>
                      <div class="modal-body">
                      	<div class="row">
                      		<div class="col-xs-12 col-xs-6">
                      			<div class="form-group">
                      				<label for="tname">Cancelled By</label>
                      				<input type="text" name="tname" id="tname" class="form-control" value="" disabled>
                      			</div>
                      			<div class="form-group">
                      				<label for="tdate">Date</label>
                      				<input type="text" name="tdate" id="tdate" class="form-control" value="" disabled>
                      			</div>
                      			<div class="form-group">
                      				<label for="trefunded">Refunded</label>
                      				<input type="text" name="trefunded" id="trefunded" class="form-control" value="" disabled>
                      			</div>
                      		</div>
                      	</div>  
                      </div>
                      <div class="modal-footer">
                        <a href="#" class="btn btn-default" data-dismiss="modal">Close</a>
                      </div>
                    </div>
              </div>
            </div> 	  
       <?php include $dir.'/templates/admin/footer.php';?>