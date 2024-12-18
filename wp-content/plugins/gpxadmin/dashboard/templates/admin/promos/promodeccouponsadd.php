<?php
extract($static);
extract($data);
include $dir . '/templates/admin/header.php';

?>
<div class="right_col" role="main">
	<div class="update-nag"></div>
	<div class="">

		<div class="page-title">
			<div class="title_left">
				<h3>Add Owner Credit</h3>
			</div>
		</div>

		<div class="clearfix"></div>

		<div class="row">
			<div class="col-md-8 col-sm-12 col-md-offset-2">
				<form id="coupon-credit-add" data-parsley-validate
					class="form-horizontal form-label-left" method="post">
					<div class="well">
    					<div class="form-group">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="name">Name <span class="required">*</span>
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="text" id=Name name="Name" required="required"
    								class="form-control col-md-7 col-xs-12 alphanumeric" value="<?=$vars['Name']?>">
    						</div>
    					</div>
    					<div class="form-group">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="slug" id="promocode">Coupon Code <span class="required">*</span>
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="text" id="Slug" name="Slug" required="required"
    								class="form-control col-md-7 col-xs-12" value="<?=$vars['Slug']?>">
    						</div>
    					</div>
    					<div class="form-group">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="amount">Coupon Amount <span class="required">*</span> 
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="text" id="amount" name="amount" required="required"
    								class="form-control col-md-7 col-xs-12" value="<?=$vars['amount']?>">
    						</div>
    					</div>
        				<div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-name">
                            	Customer <span class="required">*</span>
                            </label>
                            <div class="col-xs-12 col-md-6">
                            	<input id="userSearch" class="form-control" placeholder="Name or Owner ID">
                            	<a href="#" id="userSearchBtn" class="btn btn-primary">Search</a>
                            	<div class="row">
                            		<div class="col-xs-12 col-sm-6 sflReset">
                            			<label class="label-above">Available</label>
                            			<ul id="selectFromList" class="userSelect">
                            			</ul>
                        			</div>
                           		 	<div class="col-xs-12 col-sm-6 sflReset">
                           		 		<label class="label-above">Selected</label>
                           		 		<select id="selectToList" name="owners[]" class="userSelect" multiple=multiple>
                           		 		<?=$vars['owners']?>
                           		 		</select>
                       		 		</div>
                   		 		</div>
                            </div>
                        </div>
    					<div class="form-group">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="Active">Single Use <span class="required">*</span>
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<select id="single-use" name="singleuse" required="required"
    								class="form-control col-md-7 col-xs-12">
    								<option></option>
                              		<?php
                                $activeopts = array(
                                    '0' => 'No',
                                    '1' => 'Yes',
                                );
                                foreach ($activeopts as $optkey => $optvalue) {
                                    $selected = '';
                                    if($vars['singleuse'] == $optkey)
                                    {
                                        $selected = 'selected="selected"';
                                    }
                                    echo '<option value="' . $optkey . '"' . $selected . '>' . $optvalue . '</option>';
                                }
                                ?>
                              </select>
    						</div>
    					</div>
    					<div class="form-group">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="expirationDate">Expiration Date 
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<input type="date" id="expirationDate" name="expirationDate" value="" />
    						</div>
    					</div>
    					<div class="form-group">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12"
    							for="Active">Active <span class="required">*</span>
    						</label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<select id="Active" name="Active" required="required"
    								class="form-control col-md-7 col-xs-12">
    								<option></option>
                              		<?php
                                $activeopts = array(
                                    '1' => 'Yes',
                                    '0' => 'No'
                                );
                                foreach ($activeopts as $optkey => $optvalue) {
                                    $selected = '';
                                    if($vars['Active'] == $optkey)
                                    {
                                        $selected = 'selected="selected"';
                                    }
                                    echo '<option value="' . $optkey . '"' . $selected . '>' . $optvalue . '</option>';
                                }
                                ?>
                              </select>
    						</div>
    					</div>
						
						<div class="form-group">
    						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="comments"> Comments  <span class="required">*</span></label>
    						<div class="col-md-6 col-sm-6 col-xs-12">
    							<textarea id="comments" name="comments" required="required"><?php if(isset($vars['comments'])){echo $vars['comments'];} ?></textarea>
    						</div>
    					</div>

    				</div>
    					<div class="ln_solid"></div>
    					<div class="form-group">
    						<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
    							<a href="" class="btn btn-danger cancel-return">Cancel</a>
    							<button type="submit" class="btn btn-success" id="submit-btn">
    								Submit <i class="fa fa-circle-o-notch fa-spin fa-fw"
    									style="display: none;"></i>
    							</button>
    						</div>
    					</div>
					</div>
				</form>

			</div>
		</div>
	</div>
</div>
<?php include $dir.'/templates/admin/footer.php';?>