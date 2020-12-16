<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
 <script src="https://parsleyjs.org/dist/parsley.js"></script>
 <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

  <div class=" dashboard_body nav-md">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a href="index.html" class="site_title"><i class="fa fa-building-o"></i> <span>GPX Admin</span></a>
            </div>

            <div class="clearfix"></div>

            <!-- menu profile quick info -->
            <div class="profile">
              <div class="profile_pic">
                <?=get_avatar($user_data->ID, 50, '', $user_data->user_firstname, array('class'=>'img-circle profile_img'))?>
              </div>
              <div class="profile_info">
                <span>Welcome,</span>
                <h2><?=$user_data->user_firstname?> <?=$user_data->user_lastname?></h2>
              </div>
            </div>
            <!-- /menu profile quick info -->

            <br />

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <ul class="nav side-menu">
                <?php 
                $cuser = wp_get_current_user();
                if(in_array('gpx_admin', (array) $cuser->roles) || in_array('gpx_supervisor', (array) $cuser->roles))
                {
                ?>
                  <li><a href="<?=$dashboard?>"><i class="fa fa-home"></i> Home</a></li>
                  <li <?php if($active=='promos') echo 'class="active"'?>><a><i class="fa fa-usd"></i> Specials <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu" <?php if($active=='promos') echo 'style="display: block;"'?>>
                      <?php 
                      if(in_array('gpx_admin', (array) $cuser->roles))
                      {
                      ?>
                      <li><a href="<?=$dashboard?>&gpx-pg=promos_all">View All</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=promos_add">Add</a></li>
                      <?php 
                      }
                      ?>
                      <li><a href="<?=$dashboard?>&gpx-pg=promos_autocoupons">Auto Coupons List</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=promos_deccoupons">Owner Credit Coupons</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=promos_deccouponsadd">New Owner Credit Coupon</a></li>
                    </ul>
                  </li>
                  <?php 
                  }
                  if(in_array('gpx_admin', (array) $cuser->roles))
                  {
                  ?>
                  <li  <?php if($active=='regions') echo 'class="active"'?>><a><i class="fa fa-map-o"></i> Regions <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu" <?php if($active=='regions') echo 'style="display: block;"'?>>
                      <li><a href="<?=$dashboard?>&gpx-pg=regions_all">View All</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=regions_add">Add</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=regions_assignlist">Assign Region</a></li>
                    </ul>
                  </li>
                  <li  <?php if($active=='resorts') echo 'class="active"' ?>><a><i class="fa fa-building-o"></i> Resorts <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu" <?php if($active=='resorts') echo 'style="display: block;"'?>>
                      <li><a href="<?=$dashboard?>&gpx-pg=resorts_all">View All</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=resorts_taxes">Taxes</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=resorts_add">Add</a></li>
                    </ul>
                  </li>
                  <li <?php if($active=='room') echo 'class="active"' ?>><a><i class="fa fa-building-o"></i>Inventory<span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu" <?php if($active=='room') echo 'style="display: block;"'?>>
                      <li><a href="<?=$dashboard?>&gpx-pg=room_all">View All</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=room_add">Rooms Add</a></li>
                      <?php 
                      /*
                      ?>
                      <li><a href="<?=$dashboard?>&gpx-pg=room_import">Room Import</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=room_error">Room Import Error</a></li>
                      <?php
                      */
                      ?>
                    </ul>
                  </li>
              <?php 
                    }
              ?>
                  <li <?php if($active=='users') echo 'class="active"'?>><a><i class="fa fa-users"></i> Owner <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu" <?php if($active=='users') echo 'style="display: block;"'?>>
                      <li><a href="<?=$dashboard?>&gpx-pg=users_all">View All</a></li>
                      
                      <?php 
                      /*
                      ?>
                      <li><a href="<?=$dashboard?>&gpx-pg=users_add">Add</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=users_massdelete">Mass Remove</a></li>
                      <?php
                      */
                      ?>
                    </ul>
                  </li>
                <?php 
                if(in_array('gpx_admin', (array) $cuser->roles) || in_array('gpx_support_staff', (array) $cuser->roles))
                {
                    ?>
                  <li <?php if($active=='tradepartners') echo 'class="active"'?>><a><i class="fa fa-handshake-o"></i> Trade Partners <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu" <?php if($active=='tradepartners') echo 'style="display: block;"'?>>
                      <li><a href="<?=$dashboard?>&gpx-pg=tradepartners_all">View Trade Partners</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=tradepartners_add">Add Trade Partner</a></li>
                    </ul>
                  </li>
                <?php 
                }
                if(in_array('gpx_admin', (array) $cuser->roles) || in_array('gpx_supervisor', (array) $cuser->roles))
                {
                ?> 
                  <li <?php if($active=='transactions') echo 'class="active"'?>><a><i class="fa fa-barcode"></i> Transactions <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu" <?php if($active=='transactions') echo 'style="display: block;"'?>>
                      <li><a href="<?=$dashboard?>&gpx-pg=transactions_all">View All</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=transactions_holds">Holds</a></li>
                      <?php 
                      /*
                      ?>
                      <li><a href="<?=$dashboard?>&gpx-pg=transactions_import">Import</a></li>
                      <?php
                      */
                      ?>
                    </ul>
                  </li>
                <?php 
                }
                if(in_array('gpx_admin', (array) $cuser->roles))
                {
                ?>                  
                  <li  <?php if($active=='reports') echo 'class="active"'?>><a><i class="fa fa-line-chart"></i> Reports <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu" <?php if($active=='reports') echo 'style="display: block;"'?>>
                      <li><a href="<?=$dashboard?>&gpx-pg=reports_writer">Report Writer</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=reports_searches">Resort Searches</a></li>
                      <li><a href="<?=$dashboard?>&&gpx-pg=reports_retarget">Retargeting Report</a></li>
                      <li><a href="<?=$dashboard?>&&gpx-pg=reports_customrequest">Special Requests</a></li>
                      <li><a href="/wp-admin/admin-ajax.php?action=gpx_csv_download&table=wp_gpxTransactions&column=transactionData" id="transactionReport">Transactions CSV Reports</a></li>
                    </ul>
                  </li>
              <?php 
              } 
              ?>
                  <li <?php if($active=='customrequests') echo 'class="active"'?>><a><i class="fa fa-bullhorn"></i> Special Requests <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu" <?php if($active=='customrequests') echo 'style="display: block;"'?>>
                      <li><a href="<?=$dashboard?>&gpx-pg=customrequests_all">View All</a></li>
                <?php 
                if(in_array('gpx_admin', (array) $cuser->roles))
                {
                ?>                        
                      <li><a href="<?=$dashboard?>&gpx-pg=customrequests_form">Form</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=customrequests_email">General Email</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=customrequests_emailresortmatch">Resort Matched Email</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=customrequests_emailresortmissed">Resort Missed Email</a></li>
                      <li><a href="<?=$dashboard?>&gpx-pg=customrequests_emailsixtyday">Sixty Day Email</a></li>
                      <?php 
                      /*
                      ?>
                      <li><a href="<?=$dashboard?>&gpx-pg=customrequests_emailreports">Reports Email</a></li>
                      <?php
                      */
                      ?>
                <?php 
                    }
                ?>
                    </ul>
                  </li>
                </ul>
              </div>
            </div>
            <!-- /sidebar menu -->

            <!-- /menu footer buttons -->
            <div class="sidebar-footer hidden-small">
              <a data-toggle="tooltip" data-placement="top" title="Settings">
                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="FullScreen">
                <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Lock">
                <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Logout">
                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
              </a>
            </div>
            <!-- /menu footer buttons -->
          </div>
        </div>
                <div class="top_nav">
          <div class="nav_menu">
            <nav>
              <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
              </div>

              <ul class="nav navbar-nav navbar-right">
              </ul>
            </nav>
          </div>
        </div>
