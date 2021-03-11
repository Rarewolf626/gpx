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

$admin_url_vars[] = 'action=get_gpx_desccoupons';

if(isset($activeBtn) && !empty($activeBtn))
{
    if(isset($_REQUEST[$activeBtn['request']]))
    {
        $admin_url_vars[] = '&'.$activeBtn['request'].'='.$_REQUEST[$activeBtn['request']];
        $activeCurrent = $_REQUEST[$activeBtn['request']];
    }
    elseif(!empty($activeBtn['default']))
    {
        $admin_url_vars[] = '&'.$activeBtn['request'].'='.$activeBtn['default'];
        $activeCurrent = $activeBtn['default'];
    }
    
}
$admin_url .= implode("&",$admin_url_vars);
?>
        <div class="right_col" role="main">
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>All Owner Credit Coupons</h3>
              </div>

              <div class="title_right">
                <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search">
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
                 		  <h2>Owner Credit Coupons</h2> 
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
                            data-sort-name="id"
                            data-show-refresh="true"
                            data-show-toggle="true"
                            data-show-columns="true"
                            data-show-export="true"
                            data-export-data-type="all"
                            data-export-types="['csv', 'txt', 'excel']"
                            data-sort-order="asc"
                            data-show-columns="true"
                            data-filter-control="true"
                            data-filter-show-clear="true"
                            data-escape="false"
                            data-toolbar="#custom-head"
                            data-side-pagination="server"
                            data-bDestroy="true"  data-bServerSide="true">
                            <thead>
                              <tr>
                                <th data-field="edit"></th>
                                <th data-field="id" data-filter-control="input" data-sortable="true">ID</th>
                                <th data-field="Name" data-sortable="true">Name</th>
                                <th data-field="Slug" data-filter-control="input" >Slug</th>
                                <th data-field="EMSOwnerID" data-filter-control="input" >Owner ID</th>
                                <th data-field="Balance" data-sortable="true">Balance</th>
                                <th data-field="Redeemed" data-sortable="true">Redeemed</th>
                                <th data-field="SingleUse">Single Use</th>
                                <th data-field="ExpiryDate"
                                    data-filter-control="datepicker"
                                    data-filter-datepicker-options='{"autoclose":true, "clearBtn": true, "todayHighlight": true, "orientation": "top"}'
                                    data-sortable="true" style="max-width: 124px;">Expiry Date</th>
                                <th data-field="ExpiryStatus" data-sortable="true">Active</th>
                                <th data-field="comments" data-visible="false">Comments</th>
                                <th data-field="IssuedOn" data-visible="false">Issued On</th>
                                <th data-field="IssuedBy" data-visible="false">Issued By</th>
                                <th data-field="Activity" data-visible="false">Activity</th>
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
