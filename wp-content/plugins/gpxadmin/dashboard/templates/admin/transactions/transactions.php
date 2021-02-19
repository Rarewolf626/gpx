<?php

extract($static);
extract($data);
include $dir.'/templates/admin/header.php';

?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>All Transactions</h3>
              </div>
            </div>
                        
            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
              
               <?php 
                  $admin_url = 'admin-ajax.php?&action=get_gpx_transactions';      
                ?>
                 	<div class="panel panel-default">
                 		<div class="panel-heading">
                 		  <h2>All Transactions</h2> 
                 		</div>
                 		<div class="panel-body">	
                            <div class="row">
                            	<div class="col-xs-12">
                            		<table id="transactionsTable" data-toggle="table"
                                             data-url="<?=admin_url($admin_url);?>"
                                             data-cache="false"
                                             data-pagination="true"
                                             data-page-size="20"
                                             data-page-list="[10,20,50,100]"
                                             data-select-item-name="id"
                                             data-select-id-field="id"
                                             data-sort-name="a.id"
                                             data-show-refresh="true"
                                             data-show-toggle="true"
                                             data-show-columns="true"
                                             data-show-export="true"
                                             data-export-data-type="all"
                                             data-export-types="['csv', 'txt', 'excel']"
                                             data-search="false"
                                             data-sort-order="desc"
                                             data-show-columns="true"
                                             data-filter-control="true"
                                             data-filter-show-clear="true"
                                             data-escape="false"
                                             data-click-to-select="true"
                                             data-side-pagination="server"
                                             >
                						<thead>
                                            <tr>
                                                <th data-field="view"></th>
                                                <th data-field="id" data-filter-control="input" data-sortable="true">Transaction ID</th>
                                                <th data-field="transactionType" data-filter-control="select" data-sortable="true">Transaction Type</th>
                                                <th data-field="memberNo" data-filter-control="input" data-sortable="true">Member Number</th>
                                                <th data-field="memberName" data-sortable="true">Member Name</th>
                                                <th data-field="ownedBy" data-filter-control="input" data-sortable="false" data-visible="false">Owned By</th>
                                                <th data-field="guest" data-sortable="true" data-width="170" data-class="guestNameTD">Guest Name</th>
                                                <th data-field="adults" data-filter-control="input" data-sortable="true" data-visible="false">Adults</th>
                                                <th data-field="children" data-filter-control="input" data-sortable="true" data-visible="false">Children</th>
                                                <th data-field="upgradefee" data-filter-control="input" data-sortable="true" data-visible="false">Upgrade Fee</th>
                                                <th data-field="cpo" data-filter-control="input" data-sortable="true" data-visible="false">CPO</th>
                                                <th data-field="cpofee" data-filter-control="input" data-sortable="true" data-visible="false">CPO Fee</th>
                                                <th data-field="Resort" data-filter-control="input" data-sortable="true">Resort Name</th>
                                                <th data-field="room_type" data-filter-control="input" data-sortable="true">Room Type</th>
                                                <th data-field="weekType" data-filter-control="input" data-sortable="true">Week Type</th>
                                                <th data-field="balance" data-filter-control="input" data-sortable="true" data-visible="false">Balance</th>
                                                <th data-field="resortID" data-filter-control="input" data-sortable="true" data-visible="false">Resort ID</th>
                                                <th data-field="weekID" data-filter-control="input" data-sortable="true">WeekID</th>
                                                <th data-field="size" data-filter-control="input" data-sortable="true" data-visible="false">Size</th>
                                                <th data-field="sleeps" data-filter-control="input" data-sortable="true" data-visible="false">Sleeps</th>
                                                <th data-field="bedrooms" data-filter-control="input" data-sortable="true" data-visible="false">Bedrooms</th>
                                                <th data-field="nights" data-filter-control="input" data-sortable="true" data-visible="false">Nights</th>
                                                <th data-field="checkIn" data-filter-control="input" data-sortable="true">Check In</th>
                                                <th data-field="paid" data-filter-control="input" data-sortable="true">Paid</th>
                                                <th data-field="nights" data-filter-control="input" data-sortable="true" data-visible="false">Nights</th>
                                                <th data-field="processedBy" data-filter-control="input" data-sortable="true" data-visible="false">Processed By</th>
                                                <th data-field="promoName" data-filter-control="input" data-sortable="true" data-visible="false">Promo Name</th>
                                                <th data-field="discount" data-filter-control="input" data-sortable="true" data-visible="false">Discount</th>
                                                <th data-field="coupon" data-filter-control="input" data-sortable="true" data-visible="false">Coupon</th>
                                                <th data-field="ownerCreditCouponID" data-filter-control="input" data-sortable="true" data-visible="false">Owner Credit Coupon ID</th>
                                                <th data-field="ownerCreditCouponAmount" data-filter-control="input" data-sortable="true" data-visible="false">Owner Credit Coupon Amount</th>
                                                <th data-field="transactionDate" data-filter-control="input" data-sortable="true">Transaction Date</th>
                                                <th data-field="cancelled" data-filter-control="input" data-sortable="true" data-class="cancelledTransactionTD">Cancelled</th>
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
          <div id="guest-details" class="modal fade" role="dialog">
              <div class="modal-dialog">
            	<form name="update-guest-details" id="update-guest-details" method="POST">
            		<input type="hidden" name="transactionID" id="transactionID" value="">
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
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>