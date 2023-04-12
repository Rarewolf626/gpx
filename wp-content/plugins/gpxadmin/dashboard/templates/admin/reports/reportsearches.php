<?php 

  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';
  
?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Resort Searches</h3>
              </div>
            </div>
                        
            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
              
               <?php 
                  
                  $admin_url = 'admin-ajax.php?&action=get_gpx_reportsearches';      
                ?>
                 	<div class="panel panel-default">
                 		<div class="panel-heading">
                 		  <h2>All Resorts (90 Days)</h2> 
                 		</div>
                 		<div class="panel-body">	
                            <div class="row">
                            	<div class="col-xs-12">
                            		<table data-toggle="table"
                                             data-url="<?=admin_url($admin_url);?>"
                                             data-cache="false"
                                             data-pagination="true"
                                             data-page-size="10"
                                             data-page-list="[10,20,50,100]"
                                             data-sort-name="resort"
                                             data-show-refresh="true"
                                             data-show-toggle="true"
                                             data-show-columns="true"
                                             data-show-export="true"
                                             data-export-data-type="all"
                                             data-export-types="['csv', 'txt', 'excel']"
                                             data-show-columns="true"
                                             data-filter-control="true"
                                             data-filter-show-clear="true"
                                             data-escape="false">
                						<thead>
                                            <tr>
                                                <th data-field="resort">Resort</th>
                                                <th data-field="ref" data-visible="false">Referring Page</th>
                                                <th data-field="date">Date</th>
                                                <th data-field="resortID" data-visible="false">Resort ID</th>
                                                <th data-field="userID">User ID</th>
                                                <th data-field="user_name" data-visible="false">User Name</th>
                                                <th data-field="search_location" data-visible="false">Search Location</th>
                                                <th data-field="search_month" data-visible="false">Search Month</th>
                                                <th data-field="search_year" data-visible="false">Search Year</th>
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