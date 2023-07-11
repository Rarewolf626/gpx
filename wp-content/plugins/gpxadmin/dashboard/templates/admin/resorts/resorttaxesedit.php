<?php
  extract($static);
  extract($data);
  include $dir.'/templates/admin/header.php';
?>
        <div class="right_col" role="main">
          <div class="update-nag"></div>
          <div class="">

            <div class="page-title">
              <div class="title_left">
                <h3>Edit Taxes</h3>
              </div>
              <div class="title_right">
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">

                     <div class="x_content">
                    <br />
                    <form id="resorttax-edit" data-parsley-validate class="form-horizontal form-label-left">
                      <input type="hidden" name="taxID" value="<?=$tax->ID?>">
                      <div class="form-group">
                      	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="TaxAuthority">Tax Authority <span class="required">*</span></label>
                      	<div class="col-md-6 col-sm-6 col-xs-11">
                          <input type="text" name="TaxAuthority" id="TaxAuthority" class="form-control form-element" value="<?=$tax->TaxAuthority;?>">
                        </div>
                      </div>
                      <div class="form-group">
                      	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="City">City <span class="required">*</span></label>
                      	<div class="col-md-6 col-sm-6 col-xs-11">
                          <input type="text" name="City" id="City" class="form-control form-element" value="<?=$tax->City;?>">
                        </div>
                      </div>
                      <div class="form-group">
                      	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="State">State <span class="required">*</span></label>
                      	<div class="col-md-6 col-sm-6 col-xs-11">
                          <input type="text" name="State" id="State" class="form-control form-element" value="<?=$tax->State;?>">
                        </div>
                      </div>
                      <div class="form-group">
                      	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="Country">Country <span class="required">*</span></label>
                      	<div class="col-md-6 col-sm-6 col-xs-11">
                          <input type="text" name="Country" id="Country" class="form-control form-element" value="<?=$tax->Country;?>">
                        </div>
                      </div>
                      <div class="form-group">
                      	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="TaxPercent1">Tax Percent 1 <span class="required">*</span></label>
                      	<div class="col-md-6 col-sm-6 col-xs-11">
                          <input type="text" name="TaxPercent1" id="TaxPercent1" class="form-control form-element" value="<?=$tax->TaxPercent1;?>">
                        </div>
                      </div>
                      <div class="form-group">
                      	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="TaxPercent2">Tax Percent 2</label>
                      	<div class="col-md-6 col-sm-6 col-xs-11">
                          <input type="text" name="TaxPercent2" id="TaxPercent2" class="form-control form-element" value="<?=$tax->TaxPercent2;?>">
                        </div>
                      </div>
                      <div class="form-group">
                      	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="TaxPercent3">Tax Percent 3</label>
                      	<div class="col-md-6 col-sm-6 col-xs-11">
                          <input type="text" name="TaxPercent3" id="TaxPercent3" class="form-control form-element" value="<?=$tax->TaxPercent3;?>">
                        </div>
                      </div>
                      <div class="form-group">
                      	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="FlatTax1">Flat Tax 1 <span class="required">*</span></label>
                      	<div class="col-md-6 col-sm-6 col-xs-11">
                          <input type="text" name="FlatTax1" id="FlatTax1" class="form-control form-element" value="<?=$tax->FlatTax1;?>">
                        </div>
                      </div>
                      <div class="form-group">
                      	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="FlatTax2">Flat Tax 2</label>
                      	<div class="col-md-6 col-sm-6 col-xs-11">
                          <input type="text" name="FlatTax2" id="FlatTax21" class="form-control form-element" value="<?=$tax->FlatTax2;?>">
                        </div>
                      </div>
                      <div class="form-group">
                      	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="FlatTax3">Flat Tax 3</label>
                      	<div class="col-md-6 col-sm-6 col-xs-11">
                          <input type="text" name="FlatTax3" id="FlatTax3" class="form-control form-element" value="<?=$tax->FlatTax3;?>">
                        </div>
                      </div>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button type="submit" class="btn btn-success" id="resorttax-submit">Submit <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                        </div>
                      </div>
                    </form>
                  </div>

              </div>
         	</div>
         </div>
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>
