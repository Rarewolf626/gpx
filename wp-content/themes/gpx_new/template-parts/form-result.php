<form action="" id="results-form">
	<div class="w-options w-results">
		<h4>Displaying Resorts In:</h4>
		<div class="cnt col-3">
			<label for="select_country" class="ada-text">Select Country</label>
			<select id="select_country" class="dgt-select" name="select_country" placeholder="Country">
				<?php echo do_shortcode('[sc_countryregion_dd]')?>
			</select>
		</div>
		<div class="cnt col-3">
			<label for="select_location" class="ada-text">Select Location</label>
			<select id="select_location" class="dgt-select" name="select_location" placeholder="Location">

			</select>
		</div>
		<div class="cnt col-3">
			<label for="select_monthyear" class="ada-text">Select Time</label>
			<select id="select_monthyear" class="dgt-select" name="select_monthyear" placeholder="Month/Year">

			</select>
		</div>
	</div>
</form>