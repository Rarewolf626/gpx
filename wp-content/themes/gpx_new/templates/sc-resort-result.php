        <div class="dgt-container g-w-modal">
            <dialog class="dialog--filter" id="modal-filter-resort" data-width="460" data-min-height="420">
            	<div class="w-modal">
            		<form action="">
            			<div class="block">
            				<h2>Filter Results</h2>
            				<label for="select_cities" class="ada-text">Select City</label>
            				<select id="select_cities" class="dgt-select dd filter_resort_city" multiple="multiple" data-filter="subregions" name="mySelect" placeholder="All Cities">
            					<?php
            				        $selRegion = '';
            				          if(isset($_GET['select_region']))
            				              $selRegion = $_GET['select_region'];
            				          foreach($filterCities as $fcKey=>$fcValue)
            				          {
            				              echo '<option value="'.$fcKey.'">'.$fcValue.'</option>';
            				          }
            				   ?>
            				</select>
            				<div class="block">
            				<h3>- Type of Week</h3>
            				<ul class="list-check">
            					<li>
            						<input type="checkbox" class="filter_resort_resorttype" id="chk-rental" class="filter_data filter_group" data-type="type" data-filter="resorttype" name="addname[]" value="BonusWeek" placeholder="Rental" checked>
            						<label for="chk-rental">Rental</label>
            					</li>
            					<li>
            						<input type="checkbox" class="filter_resort_resorttype" id="chk-exchange" class="filter_data filter_group" data-type="type" data-filter="resorttype" name="addname[]" value="ExchangeWeek" placeholder="Exchange" checked>
            						<label for="chk-exchange">Exchange</label>
            					</li>
            				</ul>
            				<!--
            				<h3>- Resort Type</h3>
            				<ul class="list-check">
            					<li>
            						<input type="checkbox" class="filter_resort_ai" id="chk-all-inclusive" name="addai[]" data-type="type" data-filter="resorttype" value="6" placeholder="All-Inclusive Resorts Only">
            						<label for="chk-all-inclusive">All-Inclusive Resorts Only</label>
            					</li>
            				</ul>
            				 -->
            			</div>
            			</div>
            		</form>
            	</div>
            </dialog>
        </div>
<section class="w-banner w-results">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result">
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg" alt="" />
        </li>
    </ul>
    <div class="dgt-container w-box">
        <form action="">
            <div class="w-options w-results">
                <h4>Displaying Resorts In:</h4>
                <div class="cnt left resort">
                	<label for="select_country" class="ada-text">Select Country</label>
                    <select id="select_country" class="dgt-select" name="select_country" placeholder="Country">
        				<?php echo do_shortcode('[sc_countryregion_dd]')?>
                    </select>
                </div>
                <div class="cnt right resort location_select">
                	<label for="select_location" class="ada-text">Select Location</label>
                    <select id="select_location" class="dgt-select submit-change" name="select_location" placeholder="Location">
        				<?php echo do_shortcode('[sc_newcountryregion_dd country='.$_GET['select_country'].']')?>
                    </select>
                </div>
            </div>
        </form>
    </div>
</section>
<?php include(locate_template( 'template-parts/universal-search-widget.php' )); ?>
<section class="w-filter dgt-container">
    <div class="left">
        <h3><?=count($resorts)?>
        <?php
        if(isset($regionName) && !empty($resorts))
            echo $regionName.", ".$resorts[0]->Country;
        else
            echo 'Resorts'
        ?>
        </h3>
    </div>
    <?php
    if(!empty($resorts))
    {
    ?>
    <div class="right">
        <ul class="status">
            <li>
                <div class="status-all">
                    <p>All-Inclusive</p>
                </div>
            </li>
            <li>
                <div class="status-exchange">
                    <p>Exchange</p>
                </div>
            </li>
            <li>
                <div class="status-rental">
                    <p>Rental</p>
                </div>
            </li>
        </ul>
        <a href="" class="dgt-btn call-modal-filter-resort">Filter Result</a>
    </div>
    <?php
    }
    ?>
</section>

<section class="w-featured bg-gray-light">
    <ul class="w-list-view dgt-container">
        <?php
        if(empty($resorts))
        {
        ?>
    <span class="tag">
        <img src="<?php echo get_template_directory_uri(); ?>/images/tag03.png" alt="Featured Resorts">
    </span>
    <div style="margin-top: 100px;"></div>
    	<?php
            echo do_shortcode('[gpx_display_featured_resorts location="resorts" get="9"]');
        }
        else
            foreach($resorts as $resort)
            {
            ?>
            <li id="resortbox<?=$resort->id;?>" class="w-item-view filtered" data-subregions='<?=$resort->SubRegion?>' data-resorttype='<?=$resort->ResortType?>'>
                <div class="view">
                    <div class="view-cnt">
                	<?php
                    	$imgThumb = $resort->ImagePath1;
                    	$imageTitle = strtolower($resort->ResortName);
                    	$imageAlt = $resort->ResortName;
                    	//check for updated images
                    	$sql = $wpdb->prepare("SELECT meta_value FROM wp_resorts_meta WHERE meta_key='images' AND ResortID=%s", $resort->ResortID);
                    	$rawResortImages = $wpdb->get_row($sql);
                    	if(!empty($rawResortImages->meta_value))
                    	{
                    	   $resortImages = json_decode($rawResortImages->meta_value, true);
                    	   $oneImage = $resortImages[0];
                    	   $imgThumb = $oneImage['src'];
                    	   if($oneImage['type'] == 'uploaded')
                    	   {
                    	       $id = $oneImage['id'];
                    	       $imageAlt = get_post_meta( $id ,'_wp_attachment_image_alt', true);
                    	       $imageTitle = get_the_title($id);
                    	   }
                    	}
                    	?>
                        <img src="<?=$imgThumb?>" alt="<?=$imageAlt;?>" title="<?=$imageTitle?>">
                    </div>
                    <div class="view-cnt">
                        <div class="descrip">
                            <hgroup>
                                <h2><?=$resort->ResortName;?></h2>
                                <span><?=$resort->Town?>  <?=$resort->Region.", ".$resort->Country;?></span>
                            </hgroup>
                            <p><a href="/resort-profile/?resort=<?=$resort->id?>" class="dgt-btn">View Resort</a></p>
                            <p style="margin-top: 10px;">
                            	<?php
                            	   if($resort->propCount > 0)
                            	   {
                            	?>
                            	<a href="#" data-resortid="<?=$resort->id?>" class="dgt-btn resort-availability">View Availablity <i class="fa fa-chevron-down" aria-hidden="true"></i></a>
                            	<?php
                            	   }
                            	   else
                            	   {
                            	?>
                            	<a href="#modal-custom-request" data-cid="<?=$cid?>" data-pid="" class="custom-request">No Availability – click to submit a custom request</a>
                            	<?php
                            	   }
                            	?>
                            </p>
                        </div>
                        <div class="w-status">
                            <ul class="status">
                            	<?php
                            	   $status = array('status-exchange'=>'ExchangeWeek','status-rental'=>'BonusWeek');
                            	   foreach($status as $key=>$value)
                            	   {
                            	       if(in_array($value, $resort->WeekType))
                            	       {
                            	        ?>
                                 <li>
                                    <div class="<?=$key;?>"></div>
                                </li>
                            	        <?php
                            	       }
                            	   }
                            	?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="resort-availablity-view">
                	<div class="ra-loading"></div>
                	<div class="ra-content"></div>
                </div>
            </li>
            <?php
            }
            ?>
    </ul>
</section>
