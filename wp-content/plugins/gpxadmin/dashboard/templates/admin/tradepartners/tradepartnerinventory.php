<?php 

  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';
  
?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Rooms</h3>
              </div>

              <div class="title_right">

              </div>
            </div>
                        
            <div class="clearfix"></div>

            <div class="row" id="admin-modal-content">
              <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                          <h2>View Rooms</h2> 
                        </div>
                        <div class="panel-body">    
                            <div class="row">
                                <div class="col-xs-12">


                                <table id="tp_inventory_table"
                                		data-toggle="table"
                                             data-url="<?=admin_url("admin-ajax.php?&action=gpx_tp_inventory&user=".$_GET['tp']);?>"
                                             data-cache="false"
                                             data-pagination="true"
                                             data-page-size="20"
                                             data-page-list="[10,20,50,100]"
                                             data-select-item-name="ID"
                                             data-select-id-field="ID"
                                             data-sort-name="record_id"
                                             data-show-refresh="true"
                                             data-show-toggle="true"
                                             data-filter-default="true"
                                             data-show-columns="true"
                                             data-show-export="true"
                                             data-export-data-type="all"
                                             data-export-types="['csv', 'txt', 'excel']"
                                             data-search="false"
                                             data-sort-order="asc"
                                             data-show-columns="true"
                                             data-filter-control="true"
                                             data-filter-show-clear="true"
                                             data-side-pagination="server"
                                             data-escape="false">
                <thead>
                  <tr>
                    <th data-checkbox="true"></th>
                    <th data-field="record_id" data-filter-control="input" data-sortable="true">ID</th>
                    <th data-field="check_in_date"  
                    	data-filter-control="datepicker" 
                		data-filter-datepicker-options='{"autoclose":true, "clearBtn": true, "todayHighlight": true}'
                  		data-sortable="true" style="max-width: 124px;">Check In Date</th>
                    <th data-field="check_out_date"  data-sortable="true" style="max-width: 124px;">Check Out Date</th>
                    <th data-field="ResortName" data-filter-control="input" data-sortable="true">Resort</th>
                    <th data-field="type" data-sortable="true">Type</th>
                    <th data-field="price" data-sortable="true">Price</th>
                    <th data-field="unit_type_id" data-sortable="true">Unit Type</th>
                    <th data-field="resort_confirmation_number" data-filter-control="input" data-sortable="true" style="max-width: 200px;">Resort Conf #</th>
                    <th data-field="active" data-filter-control="select" data-sortable="true">Active</th>
                  </tr>
                </thead>
              </table>
                                </div>
                            </div>  
                            <div class="row">
                            	<div class="col-xs-12">
                                	<div class="input-group mb-3">
                                      <div class="input-group-prepend">
                                        <label class="input-group-text" for="inputGroupSelect01">With Selected:</label>
                                      </div>
                                      <select class="custom-select tp-weeks-selected" id="inputGroupSelect01">
                                        <option selected>Choose...</option>
                                        <option data-type="ExchangeWeek" data-id="<?=$_GET['tp']?>">Book Exchange</option>
                                        <option data-type="RentalWeek" data-id="<?=$_GET['tp']?>">Book Rental</option>
                                        <option data-type="hold" data-id="<?=$_GET['tp']?>">Hold</option>
                                      </select>
                                      <div class="input-group-append">
                                      	<button id="tp-claim" class="btn btn-primary">Submit</button>
                                      </div>
                                    </div>
                            	</div>
                            </div>    
                        </div>
                    </div>        
              
              </div>
            </div>
         </div>
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>