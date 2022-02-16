<?php 

  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';
  
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

            <div class="row" id="admin-modal-content">
              <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                          <h2>View Activity</h2> 
                        </div>
                        <div class="panel-body">    
                            <div class="row">
                                <div class="col-xs-12">


                                <table id="tp_activity_table"
                                		data-toggle="table"
                                             data-url="<?=admin_url("admin-ajax.php?&action=gpx_tp_activity&id=".$_GET['id']);?>"
                                             data-cache="false"
                                             data-pagination="true"
                                             data-page-size="20"
                                             data-page-list="[10,20,50,100]"
                                             data-select-item-name="ID"
                                             data-select-id-field="ID"
                                             data-sort-name="status"
                                             data-show-refresh="true"
                                             data-show-toggle="true"
                                             data-filter-default="true"
                                             data-show-columns="true"
                                             data-show-export="true"
                                             data-export-data-type="all"
                                             data-export-types="['csv', 'txt', 'excel']"
                                             data-search="true"
                                             data-sort-order="asc"
                                             data-show-columns="true"
                                             data-filter-control="true"
                                             data-filter-show-clear="true"
                                             data-escape="false">
                <thead>
                  <tr>
                    <th data-field="edit" data-filter-control="input" data-sortable="true">Action</th>
                    <th data-field="ID" data-filter-control="input" data-sortable="true">Week ID</th>
                    <th data-field="activity" data-filter-control="select" data-sortable="true" style="max-width: 124px;">Activity</th>
                    <th data-field="check_in_date" data-filter-control="input" data-sortable="true" style="max-width: 124px;">Check In Date</th>
                    <th data-field="resort" data-filter-control="input" data-sortable="true">Resort</th>
                    <th data-field="unit_type" data-filter-control="select" data-sortable="true">Unit Type</th>
                    <th data-field="resort_confirmation_number" data-filter-control="input" data-sortable="true" style="max-width: 200px;">Resort Conf #</th>
                    <th data-field="guest_name" data-filter-control="input" data-sortable="true" data-footer-formatter="footerLabel">Guest Name</th>
                    <th data-field="debit" data-filter-control="input" data-sortable="true" data-footer-formatter="balanceTotal">Balance</th>
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
       <script>
//        	  function footerLabel() {
//     	    return 'Total"
//     	  }

//     	  function balanceTotal(data) {
//         	  console.log('stocked')
//     	    var field = this.field
//     	    return '$' + data.map(function (row) {
//     	      return +row[field].substring(1)
//     	    }).reduce(function (sum, i) {
//     	      return sum + i
//     	    }, 0)
//     	  }	
    	  
//     	  function footerStyle(column) {
//     		    return {
//     		      id: {
//     		        classes: 'uppercase'
//     		      },
//     		      guest_name: {
//     		        css: {'font-weight': 'normal'}
//     		      },
//     		      debit: {
//     		        css: {color: 'red'}
//     		      }
//     		    }[column.field]
//     		  }		
       </script>
       <?php include $dir.'/templates/admin/footer.php';?>