<?php 

  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';
  
?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>All Regions</h3>
              </div>
            </div>
                        
            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
              
               <?php 
                  
                  $admin_url = 'admin-ajax.php?&action=get_gpx_regions';      
                ?>
                 	<div class="panel panel-default">
                 		<div class="panel-heading">
                 		  <h2>All Regions</h2> 
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
                                             data-sort-name="gpx"
                                             data-show-refresh="true"
                                             data-show-toggle="true"
                                             data-show-columns="true"
                                             data-show-export="true"
                                             data-export-data-type="all"
                                             data-export-types="['csv', 'txt', 'excel']"
                                             data-search="true"
                                             data-sort-order="desc"
                                             data-show-columns="true"
                                             data-filter-control="true"
                                             data-filter-show-clear="true"
                                             data-escape="false">
                						<thead>
                                            <tr>
                                                <th data-field="edit"></th>
                                                <th data-field="gpx" data-filter-control="select" data-sortable="true">GPX Sub Region</th>
                                                <th data-field="region" data-filter-control="input" data-sortable="true">Region</th>
                                                <th data-field="displayName" data-filter-control="input" data-sortable="true">Display Name</th>
                                                <th data-field="parent" data-filter-control="input" data-sortable="true">Parent</th>
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