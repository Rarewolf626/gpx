<?php 

  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';
  
?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Room Import Errors</h3>
              </div>

              <div class="title_right">

              </div>
            </div>
                        
            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
               <?php 
                  
                  $admin_url = 'admin-ajax.php?&action=get_gpx_users';      
                ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                          <h2>Rooms</h2> 
                        </div>
                        <div class="panel-body">    
                            <div class="row">
                                <div class="col-xs-12">


                                <table data-toggle="table"
                                             data-url="<?=admin_url("admin-ajax.php?&action=gpx_Room_error_page");?>"
                                             data-cache="false"
                                             data-pagination="true"
                                             data-page-size="20"
                                             data-page-list="[10,20,50,100]"
                                             data-sort-name="status"
                                             data-show-refresh="true"
                                             data-show-toggle="true"
                                             data-show-columns="true"
                                             data-show-export="true"
                                             data-export-data-type="all"
                                             data-export-types="['csv', 'txt', 'excel']"
                                             data-search="true"
                                             data-sort-order="asc"
                                             data-show-columns="true"
                                             data-filter-control="true"
                                             data-filter-show-clear="true"
                                             data-escape="false">
                <thead>
                  <tr>
                    <th data-field="record_id" data-filter-control="input" data-sortable="true">Action</th>
                    <th data-field="ID" data-filter-control="input" data-sortable="true">Id</th>
                    <th data-field="check_in_date" data-filter-control="input" data-sortable="true" style="max-width: 124px;">Check In Date</th>
                    <th data-field="check_out_date" data-filter-control="input" data-sortable="true" style="max-width: 124px;">Check Out Date</th>
                    <th data-field="resort" data-filter-control="input" data-sortable="true">Resort</th>
                    <th data-field="type" data-filter-control="input" data-sortable="true">Type</th>
                    <th data-field="price" data-filter-control="input" data-sortable="true">Price</th>
                    <th data-field="unit_type_id" data-filter-control="input" data-sortable="true">Unit Type</th>
                    <th data-field="resort_confirmation_number" data-filter-control="input" data-sortable="true" style="max-width: 200px;">Resort Conf #</th>
                    <th data-field="active" data-filter-control="input" data-sortable="true">Active</th>
                    <th data-field="availability" data-filter-control="input" data-sortable="true">Availability</th>
                    <th data-field="available_to_partner_id" data-filter-control="input" data-sortable="true">Available To Partner</th>
                    
                    
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
       <?php include $dir.'/templates/admin/footer.php';?>