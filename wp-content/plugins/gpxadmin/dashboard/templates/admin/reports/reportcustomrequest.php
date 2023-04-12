<?php

extract($static);
extract($data);
include $dir.'/templates/admin/header.php';

$active = array(
    'request'=>'found',
    'default'=>'no',
    'options'=>array(
        'Inventory Not Found'=>'no',
        'Inventory Found'=>'yes',
        'All Inventory'=>'',
    ),
);

$paginate = 'data-pagination="true"
                             data-page-size="20"
                             data-page-list="[10,20,50,100,All]"';
$admin_url = 'admin-ajax.php?';
$admin_url_vars[] = 'action=get_gpx_customrequests';

if(isset($_POST['dates']) && !empty($_POST['dates']))
{
    $admin_url_vars[] = '&dates='.$_POST['dates'];
    $paginate = '';
}

if(isset($_POST['filtertype']) && !empty($_POST['filtertype']))
{
    $admin_url_vars[] = '&filtertype='.$_POST['filtertype'];
}

if(isset($active) && !empty($active))
{
    if(isset($_REQUEST[$active['request']]))
    {
        $admin_url_vars[] = '&found='.$_REQUEST[$active['request']];
        $activeCurrent = $_REQUEST[$active['request']];
    }
    elseif(!empty($active['default']))
    {
        $admin_url_vars[] = '&'.$active['request'].'='.$active['default'];
        $activeCurrent = $active['default'];
    }
    
}
$admin_url .= implode("&",$admin_url_vars);
?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Special Requests</h3>

              </div>
              <?php if(current_user_can('administrator_plus')):?>
              <div class="pull-right">
              	<h5>Send Emails
					<?php 
                  	$gfActive = get_option('gpx_global_cr_email_send');
                  	if($gfActive == 1)
                  	{
                  	?>
                  	<span class="badge btn-success" id="activeCREmail" data-active="0">Active</span>
                  	<?php 
                  	}
                  	else 
                  	{
                  	?>
                  	<span class="badge btn-danger" id="activeCREmail" data-active="1">Inactive</span>
                  	<?php 
                  	}
                  	?>
                 </h5>
              </div>
              <?php endif;?>
            </div>
                        
            <div class="clearfix"></div>
            <div id="custom-head">
                <div class="row">
                  <div class="col-lg-6">
                  <form id="cr-date-filter" method="POST">
                    <div class="input-group">
                    <?php 
                        $dateval = '';
                        if(isset($_POST['dates']))
                            $dateval = $_POST['dates'];
                    ?>
                      <input type="text" name="dates" class="form-control daterange" id="filteredDates" aria-label="..." style="width: 200px;" value="<?=$dateval?>">
                      <div class="input-group-btn">
                      	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Advanced Date Filter <span class="caret"></span></button>
                        <ul class="dropdown-menu">
                          <li><a href="#" data-filtertype="entry" class="advanced_date_filter">Entry Date</a></li>
                          <li><a href="#" data-filtertype="travel" class="advanced_date_filter">Travel Date</a></li>
                          <li><a href="#" data-filtertype="email" class="advanced_date_filter">Email Sent Date</a></li>
                          <li><a href="#" data-filtertype="clear" class="advanced_date_filter">Clear</a></li>
                        </ul>
                      </div>
                    </div><!-- /input-group -->
                      <input type="hidden" name="filtertype" id="filterType">
                      <?php if(isset($active)): ?>
                      <input type="hidden" name="<?=$active['request']?>" value="<?=$activeCurrent;?>">
                      <?php endif;?>
                    </form>
                  </div><!-- /.col-lg-6 -->
                  <div class="col-lg-offset-2 col-lg-1">
                   
                  </div>
                  <div class="col-lg-3 pull right">
                  <?php if(isset($active)): ?>
                  <form method="POST">
                  	<select class="form-control" name="<?=$active['request']?>" onchange="this.form.submit()">
                  	<?php foreach($active['options'] as $optionKey=>$optionValue): ?>
                  		<option value="<?=$optionValue?>"<?php if(isset($_REQUEST[$active['request']]) && $_REQUEST[$active['request']] == $optionValue) echo ' selected';?>><?=$optionKey?></option>
                  	<?php endforeach; ?>
                  	</select>
                  	<?php if(isset($_REQUEST['filtertype'])):?>
                  		<input type="hidden" name="filtertype" value="<?=$_REQUEST['filtertype']?>">
                  		<input type="hidden" name="dates" value="<?=$dateval;?>">
                  	<?php endif;?>
                  </form>
                  <?php endif; ?>
                  </div>
                </div><!-- /.row -->
			</div>
            <div class="row">
              <div class="col-md-12">
                 	<div class="panel panel-default">
                 		<div class="panel-heading">
                 		  <div class="row">
                 		  	<div class="col-xs-6">
                 		  			<h2>Special Requests</h2> 
                 		  	</div>
                 		  	<div class="col-xs-6 text-right">
                 		  	<?php 
                 		  	/*
                 		  	?>
                     		  		<form method="post">
                     		  			<input type="hidden" name="cr_pdf_reports" value="1">
                     		  			<button class="btn btn-primary" type="submit">Generate Report</button>
                     		  		</form>
                     	    <?php
                     	    */
                     	    ?>
                 		  		</div>
                 		  </div>
                 		</div>
                 		<div class="panel-body">	
                            <div class="row">
                            	<div class="col-xs-12">
                            		<table data-toggle="table"
                                             data-url="<?=admin_url($admin_url);?>"
                                             <?=$paginate?>
                                             data-cache="false"
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
                                             data-toolbar="#custom-head">
                						<thead>
                                            <tr>
                                                <th data-field="emsID" data-filter-control="input" data-sortable="true">EMS ID</th>
                                                <th data-field="owner" data-filter-control="input" data-sortable="true">Name</th>
                                                <th data-field="location" data-filter-control="input" data-sortable="true" data-visible="false">Location</th>
                                                <th data-field="region" data-filter-control="input" data-sortable="true">Region</th>
                                                <th data-field="city" data-filter-control="input" data-sortable="true">City</th>
                                                <th data-field="nearby" data-filter-control="select" data-sortable="true">Nearby</th>
                                                <th data-field="resort" data-filter-control="input" data-sortable="true">Resort</th>
                                                <th data-field="traveldate" data-filter-control="input" data-sortable="true">Travel Date</th>
                                                <th data-field="roomType" data-filter-control="input" data-sortable="true">Room Type</th>
                                                <th data-field="larger" data-filter-control="select" data-sortable="true">Larger</th>
                                                <th data-field="travelers" data-filter-control="input" data-sortable="true">Travelers</th>
                                                <th data-field="found" data-filter-control="select" data-sortable="true">Inventory Found</th>
                                                <th data-field="type" data-filter-control="select" data-sortable="true">Type</th>
                                                
                                                <th data-field="matchEmail" data-filter-control="select" data-sortable="true">Email Sent</th>
<!--                                                 <th data-field="matched" data-filter-control="input" data-sortable="true">Matched Weeks</th> -->
                                                <th data-field="entrydate" data-filter-control="input" data-sortable="true">Entry Date</th>
                                                <th data-field="who" data-filter-control="select" data-sortable="true">Initated By</th>
                                                <!--  <th data-field="converted" data-filter-control="select" data-sortable="true">Converted</th> -->
                                                <!-- <th data-field="revenue" data-filter-control="input" data-sortable="true">Revenue</th> -->
                                                <th data-field="active" data-filter-control="select" data-filter-default="Yes" data-sortable="true">Status</th>
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