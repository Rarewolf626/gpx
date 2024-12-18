<?php
  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';

?>
<?php if($user):?>
<div class="modal fade" id="passwordReset">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reseting Password for <?=$user->user_login?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="" id="newpwform" class="material" data-cid="<?=$cid ?? ''?>">
        	<input type="hidden" name="cid" value="<?=$_GET['id']?>">
    		<div class="gpinput">
    			<input type="password" id="chPassword" name="chPassword" class="successclear form-control" placeholder="Type new password" autocomplete="off" required >
    		</div>
    		<div class="gpinput">
    			<p class="pwMsg"></p>
    		</div>
    	</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="submitPWReset">Save changes</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
        <div class="right_col" role="main">
          <div class="">
<?php
$ystyled = '';
if(empty($umap))
{
    $ystyled = 'style="color: #ff0000; text-decoration: line-through;"';
}
?>
            <div class="page-title">
              <div class="title_left">
                <h3 class="user" data-cid="<?=$_GET['id'] ?? null?>" <?=$ystyled?>>Edit <?=$user ? $user->user_login : ''?></h3>
              </div>
                <?php if($user):?>
              <div class="title_right">
                <div class="col-xs-12 form-group pull-right" style="text-align: right; padding-right: 20px">
                <button type="button" class="btn btn-info password-reset" data-toggle="modal" data-target="#passwordReset">Reset Password</button>
                <a href="#" class="btn btn-info password-reset-link" data-userlogin="<?=$user ? $user->user_login : ''?>">Email Password Reset Link</a>


                <?php
                // moved this out ticket#1925
                if(!isset($umap['welcome_email_sent']) || $umap['welcome_email_sent'] == 0)
                {
                ?>
                    <a href="#" class="btn btn-primary" id="send_welcome_email" data-cid="<?=$user ? $user->ID : ''?>">Send Welcome Email</a>
                <?php
                }
                ?>
                </div>
              </div>
                <?php endif; ?>
            </div>

            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">

              	<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post">
              	    <input type="hidden" name="returnurl" class="returnurl" value="<?=$_SERVER['HTTP_REFERER'] ?? '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_all';?>">
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<h3>Account Holder</h3>
							  <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="preferred">GP Preferred
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                	<select name="GP_Preferred" id="preferred" class="form-control">
                                		<option  <?php if(isset($user->GP_Preferred) && $user->GP_Preferred == 'No') echo 'selected'?>>No</option>
                                		<option  <?php if(isset($user->GP_Preferred) && $user->GP_Preferred == 'Yes') echo 'selected'?>>Yes</option>
                                	</select>
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Title1">Title
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="Title1" name="Title1"  class="form-control col-md-7 col-xs-12" value="<?=$user->Title1 ?? '';?>">
                                </div>
                              </div>
                              <?php
                              if(empty($user->first_name) && !empty($user->FirstName1))
                                  $user->first_name = $user->FirstName1;
                              ?>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first_name">First Name <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="first_name" name="first_name" required="required" class="form-control col-md-7 col-xs-12" value="<?=$user->first_name ?? '';?>">
                                </div>
                              </div>
                              <?php
                              if(empty($user->last_name) && !empty($user->LastName1))
                                  $user->last_name = $user->LastName1;
                              ?>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last_name">Last Name <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="last_name" name="last_name" required="required" class="form-control col-md-7 col-xs-12" value="<?=$user->last_name ?? '';?>">
                                </div>
                              </div>
                              <?php
                              if($user) {
                                  $user->user_email = \GPX\Repository\OwnerRepository::instance()->get_email( $_GET['id'] );
                              }
                              ?>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12 " for="user_email">Email <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="email" name="Email" required="required" class="form-control col-md-7 col-xs-12" value="<?=$user?->user_email ?? '';?>">
                                </div>
                              </div>
                              <?php
                              $dayphone = '';
                              if(isset($user->DayPhone) && !empty($user->DayPhone) && !is_object($user->DayPhone))
                                  $dayphone = $user->DayPhone;
                              ?>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DayPhone">Phone
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="DayPhone" name="DayPhone" class="form-control col-md-7 col-xs-12" value="<?=$dayphone;?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Mobile1">Mobile
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="Mobile1" name="Mobile1" class="form-control col-md-7 col-xs-12" value="<?=$user->Mobile1 ?? ''?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Address1">Address1 <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="Address1" name="Address1" required="required" class="form-control col-md-7 col-xs-12" value="<?=$user->Address1 ?? '';?>">
                                </div>
                              </div>

                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Address2">Address2
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="Address2" class="form-control col-md-7 col-xs-12" value="<?=$user->Address2 ?? '';?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Address3">City<span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="Address3" name="Address3" required="required" class="form-control col-md-7 col-xs-12" value="<?=$user->Address3 ?? '';?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Address4">State<span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="Address4" name="Address4" required="required" class="form-control col-md-7 col-xs-12" value="<?=$user->Address4 ?? '';?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="PostCode">Zip Code <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="PostCode" name="PostCode" required="required" class="form-control col-md-7 col-xs-12" value="<?=$user->PostCode ?? '';?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DAEMemberNo">Account ID <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="DAEMemberNo" name="DAEMemberNo" disabled class="form-control col-md-7 col-xs-12" value="<?=$user->DAEMemberNo ?? '';?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DAEMemberNo">Monetary Credit Amount <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="DAEMemberNo" name="OnAccountAmount" disabled class="form-control col-md-7 col-xs-12" value="<?=number_format(floatval($user->OnAccountAmount ?? 0.00) * -1, 2, '.', '')?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ExternalPartyID">Sales Contract ID <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="ExternalPartyID" name="ExternalPartyID" disabled class="form-control col-md-7 col-xs-12" value="<?=$user->ResortShareID ?? '';?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ExternalPartyID">Resort Member Number <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="ExternalPartyID" name="ExternalPartyID" disabled class="form-control col-md-7 col-xs-12" value="<?=$user->ResortMemeberID ?? '';?>">
                                </div>
                              </div>

                              <?php
                                $owt = json_decode($user->OwnershipWeekType ?? '{}');
                                $allRMI = explode(",", $user->ResortMemeberID ?? '');
                                foreach($allRMI as $rmi)
                                {

                                ?>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ExternalPartyID">Ownership Week Type <?=$rmi?> <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                	<select name="OwnershipWeekType[<?=$rmi?>]" class="form-control col-md-7 col-xs-12">
                                		<option
                                			<?php if(isset($owt->$rmi) && $owt->$rmi == 'Standard') echo 'selected'?>
                                		>Standard</option>
                                		<option
                                		<?php if(isset($owt->$rmi) && $owt->$rmi == 'Even') echo 'selected'?>
                                		>Even</option>
                                		<option
                                		<?php if(isset($owt->$rmi) && $owt->$rmi == 'Odd') echo 'selected'?>
                                		>Odd</option>
                                	</select>
                                </div>
                              </div>
                              <?php } ?>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ExternalPartyID">ICE Store Link1 <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                	<select name="ICEStore">
                                		<option>Select One</option>
                                		<option value="Yes" <?php if(isset($user->ICEStore) && $user->ICEStore == 'Yes') echo 'selected'?>>Yes</option>
                                		<option value="No" <?php if(isset($user->ICEStore) && $user->ICEStore == 'No') echo 'selected'?>>No</option>
                                	</select>
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ICENameId">ICE Account NameID <span class="required"></span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="ICENameId" name="ICENameId" disabled class="form-control col-md-7 col-xs-12" value="<?php if(isset($user->ICENameId) && !empty($user->ICENameId)) echo $user->ICENameId;?>">
                                </div>
                              </div>
                        </div>
						<div class="col-xs-12 col-md-6">
							<h3>Secondary Account Info</h3>
                              <div class="form-group">
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="FirstName2">First Name
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="FirstName2" name="FirstName2" class="form-control col-md-7 col-xs-12" value="<?=$user->FirstName2 ?? '';?>" >
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="LastName2">Last Name
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="LastName2" name="LastName2" class="form-control col-md-7 col-xs-12" value="<?=$user->LastName2 ?? '';?>" >
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Email2">Email
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="Email2" name="Email2" class="form-control col-md-7 col-xs-12" value="<?=$user->Email2 ?? '';?>" >
                                </div>
                              </div>
                              <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="Mobile2">Mobile
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                  <input type="text" id="Mobile2" name="Mobile2" class="form-control col-md-7 col-xs-12" value="<?=$user->Mobile2 ?? '';?>" >
                                </div>
                              </div>
						</div>
					</div>
					<div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <a href="" class="btn btn-danger cancel-return">Cancel</a>
                          <button type="submit" class="btn btn-success save-return">Save and Return</button>
                          <?php
                          if(!empty($umap))
                          {
                          ?>
                          <a href="" class="btn btn-info save-continue">Save and Continue</a>
                          <?php
                          }
                          ?>
                        </div>
                      </div>

                    </form>

                    <div class="row" style="margin-top: 45px;">
                    	<div class="col-xs-12">
                    		<h4>Transactions</h4>

                            <div
                                id="gpxadmin-owner-transactions"
                                data-props="<?= esc_attr(json_encode(['owner_id' => $user->ID]))?>"
                            ></div>

                    	</div>
                    </div>
                    <div class="row" style="margin-top: 45px;">
                    	<div class="col-xs-12 col-xl-6">
                    		<h4>
                    			Holds
                    		</h4>

                            		<table id="transactionsTable" data-toggle="table"
                                             data-url="admin-ajax.php?&action=get_gpx_holds&userID=<?=$_GET['id']?>"
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
                                             data-filter-control="true"
                                             data-filter-show-clear="true"
                                             data-escape="false"
                                             data-click-to-select="true"
                                             >
                						<thead>
                                            <tr>
                                                <th data-field="action"></th>
                                                <th data-field="name" data-filter-control="input" data-sortable="true" data-visible="false">Owner Name</th>
                                                <th data-field="memberNo" data-filter-control="input" data-sortable="true" data-visible="false">GPR ID</th>
                                                <th data-field="week" data-filter-control="input" data-sortable="false">Week</th>
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
                    <div class="row" id="depositTable"  style="margin-top: 45px;">
                    	<div class="col-xs-12">
                    		<h4>Deposits</h4>
                                <table data-toggle="table"
                                             data-url="<?=admin_url("admin-ajax.php?&action=gpx_get_owner_credits&userID=".($user->ID ?? ''));?>"
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
                                             data-filter-control="true"
                                             data-filter-show-clear="true"
                                             data-escape="false"
                                             data-click-to-select="true"
                                             >
                						<thead>
                                            <tr>
                                                <th data-field="action"></th>
                                                <th data-field="id" data-filter-control="input" data-sortable="true">Ref No</th>
                                                <th data-field="resort" data-filter-control="input" data-sortable="true">Resort</th>
                                                <th data-field="checkIn" data-filter-control="input" data-sortable="true">Check In Date</th>
                                                <th data-field="depositDate" data-filter-control="input" data-sortable="true" data-visible="false">Deposit Date</th>
                                                <th data-field="depositYear" data-filter-control="input" data-sortable="false" data-visible="false">Deposit Year</th>
                                                <th data-field="unitType" data-filter-control="input" data-sortable="false">Unit Type</th>
                                                <th data-field="status" data-filter-control="input" data-sortable="false">Status</th>
                                                <th data-field="coupon" data-filter-control="input" data-sortable="false">Coupon</th>
                                                <th data-field="creditAmt" data-filter-control="input" data-sortable="true" data-width="170">Credit Amt</th>
                                                <th data-field="creditUsed" data-filter-control="input" data-sortable="true" data-width="170">Credit Used</th>
                                                <th data-field="expirationDate" data-filter-control="input" data-sortable="true">Expiration Date</th>
                                                <th data-field="extensionActivity" data-filter-control="input" data-sortable="true">Extension Activity</th>
                                            </tr>
                                        </thead>
                              </table>

                    	</div>
                    </div>
                    <div id="creModal" class="modal fade" role="dialog">
                      <div class="modal-dialog">

                       <form class="creditExtForm" action="" method="post">
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Credit Extension</h4>
                          </div>
                          <div class="modal-body">
                          <div class="form-group clearfix">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="DAEMemberNo">Credit Extension<span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                             <input type="date" name="dateExtension" class="form-control col-md-7 col-xs-12 dateExtension" value="">
                             <input type="hidden" name="id" id="creID" />
                            </div>
                          </div>
                          </div>
                          <div class="modal-footer">
                            <button type="submit" class="btn btn-success save-return savecontinue">Save</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                          </div>
                        </div>
                        </form>

                      </div>
                    </div>
                </div>
              </div>
         	</div>
         </div>

       </div>
       <?php include $dir.'/templates/admin/footer.php';?>
