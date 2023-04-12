<?php 
  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';

?>
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Switch Owners</h3>
              </div>
              <div class="title_right">
                <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search">
                	<a href="" id="remove_switch" class="btn btn-default">Clear Users</a>
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
            <div class="row">
              <div class="col-md-12">
               <?php 
                  $admin_url = 'admin-ajax.php?&action=get_gpx_users_switch';      
                ?>
                 	<div class="panel panel-default">
                 		<div class="panel-heading">
                 		  <h2>All Owners</h2> 
                 		</div>
                 		<div class="panel-body">	
                            <div class="row">
                            	<div class="col-xs-12">
                            		<table data-toggle="table"
                                             data-url="<?=admin_url($admin_url);?>"
                                             data-cache="false"
                                             data-pagination="true"
                                             data-page-list="[10,20,50,100]"
                                             data-sort-name="status"
                                             data-show-refresh="true"
                                             data-show-toggle="true"
                                             data-show-columns="true"
             								                 data-side-pagination='server'
                                             data-search="true"
                                             data-sort-order="asc"
                                             data-show-columns="true"
                                             data-filter-control="true"
                                             data-filter-show-clear="true"
                                             data-escape="false">
                						          <thead>
                                            <tr>
                                                <th data-field="switch"></th>
                                                <th data-field="display_name" data-filter-control="input" data-sortable="true">Name</th>
                                                <th data-field="user_email" data-filter-control="input" data-sortable="true">Email</th>
                                                <th data-field="user_login" data-filter-control="input" data-sortable="true">Username</th>
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
       <?php include $dir.'/templates/admin/footer.php'; ?>