<?php 

  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';
  
  $activeBtn = array(
      'request'=>'Active',
      'default'=>'1',
      'options'=>array(
          'Active'=>'1',
          'Inactive'=>'no',
          'All Promos'=>'',
      ),
  );
  
  $admin_url = 'admin-ajax.php?'; 
  
  $admin_url_vars[] = 'action=get_gpx_promos';
  
  if(isset($activeBtn) && !empty($activeBtn))
  {
      if(isset($_REQUEST[$activeBtn['request']]))
      {
          $admin_url_vars[] = $activeBtn['request'].'='.$_REQUEST[$activeBtn['request']];
          $activeCurrent = $_REQUEST[$activeBtn['request']];
      }
      elseif(!empty($activeBtn['default']))
      {
          $admin_url_vars[] = $activeBtn['request'].'='.$activeBtn['default'];
          $activeCurrent = $activeBtn['default'];
      }
      
  }
  $admin_url .= implode("&",$admin_url_vars);
?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>All Specials</h3>
              </div>

              <div class="title_right">
                <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search">
                  <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search for...">
                    <span class="input-group-btn">
                      <button class="btn btn-default" type="button">Go!</button>
                    </span>
                  </div>
                </div>
              </div>
            </div>
                        
            <div class="clearfix"></div>
            <div id="custom-head">
                <div class="row">
                  <div class="col-xs-12">
                  <?php if(isset($activeBtn)): ?>
                  <form method="POST">
                  	<select class="form-control" name="<?=$activeBtn['request']?>" onchange="this.form.submit()">
                  	<?php foreach($activeBtn['options'] as $optionKey=>$optionValue): ?>
                  		<option value="<?=$optionValue?>"<?php if(isset($_REQUEST[$activeBtn['request']]) && $_REQUEST[$activeBtn['request']] == $optionValue) echo ' selected';?>><?=$optionKey?></option>
                  	<?php endforeach; ?>
                  	</select>
                  </form>
                  <?php endif; ?>
                  </div>
                </div><!-- /.row -->
			</div>

            <div class="row">
              <div class="col-md-12">
               <?php 
                  
                ?>
                 	<div class="panel panel-default">
                 		<div class="panel-heading">
                 		  <h2>Promos/Coupons</h2> 
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
                                             data-escape="false"
                                             data-toolbar="#custom-head">
                						<thead>
                                            <tr>
                                                <th data-field="edit"></th>
                                                <th data-field="Type" data-filter-control="select" data-sortable="true">Type</th>
                                                <th data-field="id" data-filter-control="input" data-sortable="true">Id</th>
						<th data-field="Name" data-filter-control="input" data-sortable="true">Name</th>
                                                <th data-field="Slug" data-filter-control="input" >Slug</th>
                                                <th data-field="Availability" data-filter-control="select" >Availability</th>
                                                <th data-field="TravelStartDate" data-filter-control="input" data-sortable="true">Travel Start Date</th>
                                                <th data-field="TravelEndDate" data-filter-control="input" data-sortable="true">Travel End Date</th>
                                                <th data-field="Active" data-filter-control="select"  data-sortable="true">Active</th>
                                                <th data-field="Redeemed" data-filter-control="select"  data-sortable="true">Redeemed Coupon</th>
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
