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
              
				<div class="row">
					<div class="col-xs-12 col-sm-4"><a href="/wp-admin/admin-ajax.php?action=gpx_csv_download&table=wp_cart&column=data" class="btn btn-primary">Download Cart Data</a></div>
					<div class="col-xs-12 col-sm-4"><a href="/wp-admin/admin-ajax.php?action=gpx_csv_download&table=wp_gpxTransactions&column=data" class="btn btn-primary">Download Transaction Data</a></div>
					<div class="col-xs-12 col-sm-4"><a href="/wp-admin/admin-ajax.php?action=gpx_csv_download&table=wp_gpxMemberSearch&column=data" class="btn btn-primary">Not In Use</a></div>
				</div>              
              
              </div>
         	</div>
         </div>
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>