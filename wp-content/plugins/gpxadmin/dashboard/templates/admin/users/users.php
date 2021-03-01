<?php 

  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';    

  
?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Owners</h3>
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
                 		  <h2>View Owners</h2> 
                 		</div>
                 		<div class="panel-body">	
                            <div class="row">
                            	<div class="col-xs-12">
                                <table data-toggle="table"
                                             data-url="<?=admin_url("admin-ajax.php?&action=gpx_Owner_id_c");?>"
                                             data-cache="false"
                                             data-pagination="true"
                                             data-page-size="20"
                                             data-page-list="[10,20,50,100]"
                                             data-sort-name="id"
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
                                             data-side-pagination="server"
                                             data-escape="false">
                                <thead>
                                  <tr>
                                  	<th data-field="action"></th>
                                    <th data-field="id" data-filter-control="input" data-sortable="true">ID</th>
                                    <th data-field="Name" data-filter-control="input" data-sortable="true" data-visible="false">GPX Member</th>
                                    <th data-field="SPI_Owner_Name_1st__c" data-filter-control="input" data-sortable="true">Owner Name</th>
                                    <th data-field="SPI_Email__c" data-filter-control="input" data-sortable="true">Email</th>
                                    <th data-field="SPI_Home_Phone__c">Home Phone</th>
<!--                                     <th data-field="SPI_Work_Phone__c" data-filter-control="input" data-sortable="true">Work Phone</th> -->
                                    <th data-field="SPI_Street__c">Street</th>
                                    <th data-field="SPI_City__c">City</th>
                                    <th data-field="SPI_State__c">State</th>
<!--                                     <th data-field="SPI_Zip_Code__c" data-filter-control="input" data-sortable="true">Zip_Code</th> -->
<!--                                     <th data-field="SPI_Country__c" data-filter-control="input" data-sortable="true">Country</th> -->
                                    <th data-field="Intervals">Intervals</th>
                                  </tr>
                                </thead>
                              </table>






                            		<!-- <table data-toggle="table"
                                             data-url="<?=admin_url($admin_url);?>"
                                             data-cache="false"
                                             data-pagination="true"
                                             data-page-size="20"
                                             data-page-list="[10,20,50,100]"
                                             data-sort-name="status"
                                             data-show-refresh="true"
                                             data-show-toggle="true"
                                             data-show-columns="true"
                                             data-show-export="true"
             								                 data-side-pagination='server'
                                             data-export-data-type="all"
                                             data-export-types="['csv', 'txt', 'excel']"
                                             data-search="true"
                                             data-sort-order="asc"
                                             data-show-columns="true"
                                             data-escape="false">
                						<thead>
                                            <tr>
                                                <th data-field="edit"></th>
                                                <th data-field="first_name" data-sortable="true">First Name</th>
                                                <th data-field="last_name" data-sortable="true">Last Name</th>
                                                <th data-field="user_email" data-sortable="true">Email</th>
                                                <th data-field="user_login" data-sortable="true">Username</th>
                                                <th data-field="EMSAccountID">EMS AccountID</th>
                                                <th data-field="SalesContractID">Sales Contract ID</th>
                                                <th data-field="ResrotMemberID">Resort Member ID</th>
                                            </tr>
                                        </thead>
                                    </table> -->



                                </div>
                            </div> 		
                 		</div>
                 	</div>        
              
              </div>
         	</div>
         </div>
         
         <div id="mapped-user" class="modal fade" role="dialog">
              <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Ownerships</h4>
                      </div>
                      <div class="modal-body" id="modal-mapped-content">
                      
                      </div>
                      <div class="modal-footer">
                        <a href="#" class="btn btn-default" data-dismiss="modal">Close</a>
                      </div>
                    </div>
              </div>
            </div> 
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>