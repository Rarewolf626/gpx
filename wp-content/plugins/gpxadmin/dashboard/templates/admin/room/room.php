<?php 

  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';
  
  $activeBtn = array(
      'request'=>'Archived',
      'default'=>'0',
      'options'=>array(
          'Not Archived'=>'0',
          'Archived'=>'1',
      ),
  );
  
  $futureDateBtn = array(
      'request'=>'future_dates',
      'title'=>'Only Future Dates',
      'default'=>'Only Future Dates',
      'options'=>array(
          'Only Future Dates'=>1,
          'All Dates'=>0,
      ),
  );
  
  $admin_url = admin_url("admin-ajax.php?");
  $admin_url_vars[] = 'action=gpx_Room';
  
  
  if(isset($activeBtn) && !empty($activeBtn))
  {
      if(isset($_REQUEST[$activeBtn['request']]))
      {
          $admin_url_vars[] = $activeBtn['request'].'='.$_REQUEST[$activeBtn['request']];
          $activeCurrent = $_REQUEST[$activeBtn['request']];
      }
      elseif($activeBtn['default'] == '0')
      {
          $admin_url_vars[] = $activeBtn['request'].'='.$activeBtn['default'];
          $activeCurrent = $activeBtn['default'];
      }
      
  }
  
  if(isset($futureDateBtn) && !empty($futureDateBtn))
  {
      if(isset($_REQUEST[$futureDateBtn['request']]))
      {
          $admin_url_vars[] = $futureDateBtn['request'].'='.$_REQUEST[$futureDateBtn['request']];
          $activeCurrent = $_REQUEST[$futureDateBtn['request']];
      }
      elseif($futureDateBtn['default'] == '1')
      {
          $admin_url_vars[] = $futureDateBtn['request'].'='.$futureDateBtn['default'];
          $activeCurrent = $futureDateBtn['default'];
      }
      
  }
  $admin_url .= implode("&",$admin_url_vars);
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
                  	<select class="form-control" name="<?=$futureDateBtn['request']?>" onchange="this.form.submit()">
                  	<?php foreach($futureDateBtn['options'] as $optionKey=>$optionValue): ?>
                  		<option value="<?=$optionValue?>"<?php if(isset($_REQUEST[$futureDateBtn['request']]) && $_REQUEST[$futureDateBtn['request']] == $optionValue) echo ' selected';?>><?=$optionKey?></option>
                  	<?php endforeach; ?>
                  	</select>
                  </form>
                  <?php endif; ?>
                  </div>
                </div><!-- /.row -->
			</div>

            <div class="row">
              <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                          <h2>View Rooms</h2> 
                        </div>
                        <div class="panel-body">    
                            <div class="row">
                                <div class="col-xs-12">


                                <table id="inventory_rooms_table" data-toggle="table"
                                             data-url="<?=$admin_url;?>"
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
                                             data-escape="false"
                                             data-side-pagination="server"
                                             data-toolbar="#custom-head" data-bDestroy="true"  data-bServerSide="true">
                <thead>
                  <tr>
                    <th data-checkbox="true" data-formatter="stateFormatter"></th>
                    <th data-field="action">Action</th>
                    <th data-field="record_id" data-filter-control="input" data-sortable="true" >ID</th>
                    <th data-field="check_in_date" data-filter-control="input" data-sortable="true"  >Check In Date
                      <?php 
                      /* @Traci: I don't know why there is a calendar here.  I didn't need the date range, I just asked for something that would show future dates.
                      ?>
                      <p id="date_filter">
                        <input  class="daterange" />
                        <input class="hiddenrange"/>
                      </p>
                      <?php
                      */
                      ?>
                    </th> 
                    <th data-field="check_out_date" style="max-width: 124px;">Check Out Date</th>
                    <th data-field="ResortName" data-filter-control="input" data-sortable="true">Resort</th>
                    <th data-field="room_type"data-sortable="true">Room Type</th>
                    <th data-field="type" data-sortable="true">Type</th>
                    <!-- <th data-field="price" data-filter-control="input" data-sortable="true">Price</th>  -->
                    <th data-field="resort_confirmation_number" data-filter-control="input" data-sortable="true" style="max-width: 200px;">Resort Conf #</th>
                    <th data-field="active" data-filter-control="input" data-sortable="true">Active</th>
                    <th data-field="archived" data-filter-control="select" data-filter-default="Yes" data-sortable="true" data-visible="false">Archived</th>
                 	<th data-field="room_status" data-sortable="true">Status</th>
                  </tr>
                </thead>
              </table>
              <script>
                function stateFormatter(value, row, index) {
                  if (row.room_status == "Booked" || row.room_status == "Held"){
                    return {
                      disabled: true,
                      checked: false
                    }
                  }
                  return value;
                }
              </script>
                                </div>
                            </div>  
                            <div class="row wswrap">
                            	<div class="col-xs-2">
                                	<div class="row" style="margin-bottom: 20px;">
                                      <div class="input-group-prepend">
                                        <label class="input-group-text" for="inputGroupSelect01">With Selected:</label>
                                      </div>
                                      <select class="custom-select inventory-weeks-selected" id="inputGroupSelect01">
                                        <option selected>Choose...</option>
                                        <option data-type="hold" data-id="<?=$_GET['tp']?>">Hold</option>
                                      </select>
                                    </div>
                                    <div class="row" id="holdTillRow" style="display: none; margin-bottom: 20px;">
                                      	<label for="holddate">Until</label>
                                      	<input type="date" class="form-control" name="holdtill" id="holdTill" />
                                    </div>
                                    <div class="row" style="margin-bottom: 20px;">
                                      <div class="input-group-append">
                                      	<button id="inventory-bulk-hold" class="btn btn-primary">Submit</button>
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


       <div id="deleteModal" class="modal fade">
                    <div class="modal-dialog modal-confirm">
                      <div class="modal-content">
                        <div class="modal-header">
                          <div class="icon-box">
                            <i class="material-icons">&#xE876;</i>
                          </div>        
                          <h4 class="modal-title">Done!</h4> 
                        </div>
                        <div class="modal-body">
                          <p class="text-center">Room archived Successfully.</p>
                        </div>
                        <div class="modal-footer">
                          <button class="btn btn-success btn-block" data-dismiss="modal">OK</button>
                        </div>
                      </div>
                    </div>
                  </div>              

       <?php include $dir.'/templates/admin/footer.php';?>
            <script type="text/javascript">
            jQuery('#deleteModal').on('hide.bs.modal', function () {
              window.location =  '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_all';
            })
          function queryParams(params)
          {
            var range = jQuery('.hiddenrange').val();
            if(range != ''){
                var explode = range.split('=');
                if(explode[0]){
                  params.from_date = explode[0];
                }
                if(explode[1]){
                  params.to_date = explode[1];
                }
            }
            return params
          }

          jQuery(document).ready(function()
          {
            jQuery('.daterange').daterangepicker({
                autoUpdateInput: false

            });
            jQuery('.daterange').val('');

            jQuery('.daterange').on('cancel.daterangepicker', function(ev, picker) {
              jQuery('.hiddenrange').val('');
              jQuery(this).val('');
            });

            jQuery('.daterange').on('apply.daterangepicker', function(ev, picker)
            {
              jQuery('.hiddenrange').val(picker.startDate.format('YYYY-MM-DD')+'='+picker.endDate.format('YYYY-MM-DD'));

              jQuery.ajax({
                url : 'admin-ajax.php?&action=gpx_Room',
                type : 'POST',
                data: {
                    from_date: picker.startDate.format('YYYY-MM-DD'),
                    to_date : picker.endDate.format('YYYY-MM-DD')
                },
                success : function(newDataArray) {
                  jQuery('#inventory_rooms_table').bootstrapTable('load', newDataArray);
                  console.log(newDataArray.rows);
                }
              });
                //console.log(picker.startDate.format('YYYY-MM-DD'));
                //console.log(picker.endDate.format('YYYY-MM-DD'));
            });
          });  
        </script>
        <style>
          .daterange{
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
          }
          #date_filter{
            position:relative;
          }
        </style>