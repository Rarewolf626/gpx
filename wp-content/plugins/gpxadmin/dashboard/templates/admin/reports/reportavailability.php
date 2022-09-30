<?php

extract($static);
extract($data);
include $dir.'/templates/admin/header.php';

$paginate = 'data-pagination="true"
             data-page-size="20"
             data-page-list="[10,20,50,100,All]"';

$admin_url_vars = [
    'action' => 'gpx_get_report_availability',
    'date-start' => gpx_request('date-start'),
    'date-end' => gpx_request('date-end'),
    'filtertype' => gpx_request('filtertype'),
];
$admin_url = admin_url( 'admin-ajax.php' ) . '?' . http_build_query($admin_url_vars);

?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Master Availability</h3>

              </div>
              <?php if(current_user_can('administrator_plus')):?>
              <div class="pull-right">



              </div>
              <?php endif;?>
            </div>

            <div class="clearfix"></div>
            <div id="custom-head">
                <div class="row">
                  <div class="col-lg-6">
                  <form id="cr-date-filter" method="GET">
                    <div class="input-group">

                        <?php
                        $startdate = $_GET['date-start'] ?? date('Y-m-d');
                        $enddate = $_GET['date-end'] ?? date_create()->modify('+1 year')->format('Y-m-d');

                        // min is the start of this year
                        $first_of_this_year = date('Y').'-01-01';
                        $min = date('Y-m-d',strtotime($first_of_this_year));
                        // max is + 4 years
                        $max = date('Y-m-d',strtotime($min .'+ 4 years'));
                        ?>

                        <label for="start">Start date:</label>
                        <input type="date" id="start" name="date-start"
                               value="<?= $startdate ?>"
                               min="<?= $min ?>>" max="<?= $max ?>">

                        <label for="end">End date:</label>
                        <input type="date" id="end" name="date-end"
                               value="<?= $enddate ?>"
                               min="<?= $min ?>>" max="<?= $max ?>">

                        <button type="submit">Submit</button>

                    </div><!-- /input-group -->
                      <input type="hidden" name="page" value="gpx-admin-page" />
                      <input type="hidden" name="gpx-pg" value="reports_availability" />
                    </form>



                  </div><!-- /.col-lg-6 -->
                  <div class="col-lg-offset-2 col-lg-1">






                  </div>
                  <div class="col-lg-3 pull right">

                  </div>
                </div><!-- /.row -->
			</div>
            <div class="row">
              <div class="col-md-12">
                 	<div class="panel panel-default">
                 		<div class="panel-heading">
                 		  <div class="row">
                 		  	<div class="col-xs-6">
                 		  			<h2>Master Availability</h2>
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
                                             data-url="<?=$admin_url;?>"
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
                                                <th data-field="record_id" data-filter-control="input" data-sortable="true">Week ID</th>
                                                <th data-field="ResortName" data-filter-control="input" data-sortable="true">Resort Name</th>
                                                <th data-field="active" data-filter-control="select" data-sortable="true" data-visible="false">Active</th>
                                                <th data-field="status" data-filter-control="select" data-sortable="true">Status</th>
                                                <th data-field="check_in_date" data-filter-control="input" data-sortable="true">Check In</th>
                                                <th data-field="city" data-filter-control="input" data-sortable="true">City</th>
                                                <th data-field="state" data-filter-control="input" data-sortable="true">State</th>
                                                <th data-field="country" data-filter-control="input" data-sortable="true">Country</th>
                                                <th data-field="Price" data-filter-control="input" data-sortable="true">Price</th>
                                                <th data-field="UnitType" data-filter-control="select" data-sortable="true">Unit Type</th>
                                                <th data-field="type" data-filter-control="input" data-sortable="true">Type</th>
                                                <th data-field="Source" data-filter-control="select" data-sortable="true">Source</th>
                                                <th data-field="held_for" data-filter-control="input" data-sortable="true">Held For</th>
                                                <th data-field="release_on" data-filter-control="input" data-sortable="true">Release Hold On</th>
                                                <th data-field="SourcePartnerName" data-filter-control="input" data-sortable="true">Source Partner Name</th>
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
