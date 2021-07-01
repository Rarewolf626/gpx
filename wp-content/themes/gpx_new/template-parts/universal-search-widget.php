<section class="universal-search-widget-band">
    <form class="universal-search-widget dgt-container w-box"  role="search" method="post" action="<?php echo home_url( '/result/' ); ?>">
    	<div class="usw-text">Vacation Somewhere New</div>
    	<div class="usw-dest">
			<label for="universal_sw_autocomplete" class="ada-text">Select a location, resort or top destination</label>
			<?php 
			$selLocation = '';
			if(isset($_POST['location']))
			{
			    $selLocation = $_POST['location'];
			}
			elseif(isset($_REQUEST['resortName']))
			{
			    $selLocation = str_replace("+", " ", str_replace("%20", " ", $_REQUEST['resortName']));
			}
			?>
			<input aria-label="location" name="location" id="universal_sw_autocomplete" value="<?=$selLocation?>" placeholder="Select a location, resort or top destination" required="" class="ui-autocomplete-input" autocomplete="off" >
    	</div>
		<div class="SumoSelect sumo_select_month usw-month-year" tabindex="0">
            <label for="select_month" class="ada-text">Select Year</label>
            <?php 
			$m  = 0;
			$selMonth = '';
			if(isset($_POST['select_month']))
			{
			    $selMonth = $_POST['select_month'];
			}
			elseif(isset($_REQUEST['month']) && $$_REQUEST['month'] != 'f')
			{
			    $selMonth = $_REQUEST['month'];
			}
			?>
			<select aria-label="select month" id="select_month" class="dgt-select SumoUnder" name="select_month" placeholder="Month" tabindex="-1">
    			<option value="0" disabled="" selected=""></option>
    			<option value="any" <?php if($selMonth == 'any') echo 'selected="selected"';?>>All</option>
				<?php 
				for ($i = 0; $i < 12; $i++) {
				    $selected = '';
				    $startofmonth = date('01-m-Y');
				    $month  = date('F', strtotime($startofmonth." +{$m} months"));
				    if($month == $selMonth)
				        $selected = ' selected="selected"';
                ?>
                <option value="<?=$month?>" <?=$selected?>><?=$month?></option>
                <?php 
				    $m++;
				
				}
				?>
			</select>
		</div>
		<div class="SumoSelect sumo_select_year usw-month-year" tabindex="0">
            <label for="select_year" class="ada-text">Select Year</label>
			<select aria-label="select year" id="select_year" class="dgt-select SumoUnder" name="select_year" placeholder="Year" tabindex="-1">
				<option value="0" disabled="" selected=""></option>
				<?php 
				$selYear = '';
				$selected = '';
				if(isset($_POST['select_year']))
				{
				    $selYear = $_POST['select_year'];
				}
				elseif(isset($_REQUEST['yr']) && (isset($_REQUEST['month']) && $_REQUEST['month'] != 'f'))
				{
				    $selYear = $_REQUEST['yr'];
				}
				for($date=date('Y');$date<date('Y', strtotime('+ 2 year', time())); $date++)
				{
				    $selected = '';
				    if($date == $selYear)
				        $selected = ' selected';
				?>
				<option value="<?=$date?>" <?=$selected?>><?=$date?></option>
				<?php    
				}
				?>
			</select>
		</div>
    	<div class="usw-button">
    		<input type="submit" class="dgt-btn" value="Search" />
    	</div>
    </form>
</section>