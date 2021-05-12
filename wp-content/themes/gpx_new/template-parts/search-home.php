<div class="dgt-container w-box">
	<div class="gsub-title">
		<h3>GPX Is your private exchange service</h3>
	</div>
	<h2 class="gtitle"> Vacation Somewhere New </h2>
	<form id="home-search" role="search" method="post" action="<?php echo home_url( '/result/' ); ?>">
		<div class="w-options">
			<div class="cnt left">
				<div class="component">
					<label for="location_autocomplete" class="ada-text">Select Year</label>
					<input name="location" id="location_autocomplete" placeholder="Type a Location OR Select a Top Destination" required>
				</div>
			</div>
			<div class="cnt right">
				<label for="select_month" class="ada-text">Select Month</label>
				<select id="select_month" class="dgt-select" name="select_month" placeholder="This Month">
					<option value="0" disabled selected value="foo" ></option>
<!-- 					<option value="any">All</option> -->
					<?php 
					$m  = 0;
					for ($i = 0; $i < 12; $i++) {
					    $startofmonth= date('01-m-Y');
					    $month  = date('F', strtotime($startofmonth." +{$m} months"));
                    ?>
                    <option value="<?=$month?>" ><?=$month?></option>
                    <?php 
					    $m++;
					
					}
					?>
				</select>
				<label for="select_year" class="ada-text">Select Year</label>
				<select id="select_year" class="dgt-select" name="select_year" placeholder="This Year">
					<option value="0" disabled selected ></option>
					<?php 
					for($date=date('Y');$date<date('Y', strtotime('+ 2 year', time())); $date++)
					{
					?>
					<option value="<?=$date?>"><?=$date?></option>
					<?php    
					}
					?>
				</select>
			</div>
		</div>
		<input type="submit" class="dgt-btn" value="Search">
	</form>

	<div id="trigger1"></div>
	<div id="trigger2"></div>
	<div id="trigger3"></div>
</div>