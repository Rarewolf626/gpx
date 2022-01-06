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
                <h3>Reassign Region</h3>
              </div>
              <div class="title_right text-right">
              </div>
            </div>
                        
            <div class="clearfix"></div>
			
            <div class="row">
              <div class="col-md-6">
                  <div class="x_content">
                    <br />
                    <form  data-parsley-validate class="form-horizontal form-label-left region-reassign">
                    	<div class="form-group">
                    		<label class="control-label col-md-3 col-sm-3 col-xs-12" for="cats">Old Countries</label>
                    		<div class="col-sm-6 col-xs-12">
                    			<select id="cats" name="category">
                    			    <option></option>
                    			<?php 
                    			foreach($cats as $cat)
                    			{
                    			?>
                    				<option value="<?=$cat->CountryID?>"><?=$cat->country?></option>
                    			<?php 
                    			}
                    			?>
                    			</select>
                    		</div>
                    	</div>
                    	<div class="form-group">
                    		<label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Name</label>
                    		<div class="col-sm-6 col-xs-12">
                    			<input type="text" name="name" id="ckRegion" class="form-control col-md-7 col-xs-12" />
                    		</div>
                    	</div>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button type="submit" class="btn btn-success">Submit <i class="fa fa-circle-o-notch fa-spin fa-fw" style="display: none;"></i></button>
                        </div>
                      </div>

                    </form>
                  </div>  
              </div>
         	</div>
         </div>
       </div>
       <?php include $dir.'/templates/admin/footer.php';?>