<?php

extract($static);
extract($data);
include $dir.'/templates/admin/header.php';

?>

        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Holds</h3>
              </div>
            </div>
                        
            <div class="clearfix"></div>
            <div class="row">
              <div class="col-md-12">
               <?php 
                  $admin_url = 'admin-ajax.php?&action=get_gpx_holds';      
                ?>
                 	<div class="panel panel-default">
                 		<div class="panel-heading">
                 		  <h2>Active Holds</h2> 
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
                                             data-escape="false"
                                             data-click-to-select="true"
                                             >
                						<thead>
                                            <tr> 
                                                <th data-field="action"></th>
                                                <th data-field="name" data-filter-control="input" data-sortable="true">Owner Name</th>
                                                <th data-field="memberNo" data-filter-control="input" data-sortable="true">GPR ID</th>
                                                <th data-field="week" data-filter-control="input" data-sortable="false">Week ID</th>
                                                <th data-field="resort" data-filter-control="input" data-sortable="false">Resort</th>
                                                <th data-field="roomSize" data-filter-control="input" data-sortable="false">Room Size</th>
                                                <th data-field="checkIn" data-filter-control="input" data-sortable="false">Check In</th>
                                                <th data-field="releaseOn" data-filter-control="input" data-sortable="true">Release On</th>
                                                <th data-field="release" data-filter-control="select" data-sortable="false">Released</th>
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