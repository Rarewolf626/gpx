<div class="modal modal-filter dgt-modal" id="modal-filter">
	<div class="close-modal"><i class="icon-close"></i></div>
	<div class="w-modal">
		<form action="">
			<div class="block">
				<h2>Sort Results</h2>
				<select id="select_cities" class="dgt-select" name="mySelect" placeholder="All Cities">
					<?php
					echo do_shortcode('[sc_gpx_subregion_dd region='.implode(",", $regions).']')?>
				</select>
				<select id="select_soonest" class="dgt-select" name="mySelect" placeholder="Date/Soonest to Latest">
					<option value="0" disabled selected ></option>
					<option value="1">Date/Soonest to Latest</option>
					<option value="2">Date/Latest to Soonest</option>
					<option value="3">Price/Lowest to Hightest</option>
					<option value="4">Price/Highest to Lowest</option>
				</select>
				<h3>- Date Range</h3>
				<a href="#" class="dgt-btn">Check-In <span class="icon-date"></span></a>
			</div>
			<div class="block">
				<h2>Filter Results</h2>
				<h3>- Unit Size</h3>
				<ul class="list-check">
					<li>
						<input type="checkbox" id="chk-studio" name="check[]" value="1" placeholder="Studio">
						<label for="chk-studio">Studio</label>
					</li>
					<li>
						<input type="checkbox" id="chk-1-bedroom" name="check[]" value="2" placeholder="1 Bedroom">
						<label for="chk-1-bedroom">1 Bedroom</label>
					</li>
					<li>
						<input type="checkbox" id="chk-2-bedroom" name="check[]" value="3" placeholder=" 2 Bedroom +">
						<label for="chk-2-bedroom">2 Bedroom +</label>
					</li>
				</ul>
				<h3>- Type of Week</h3>
				<ul class="list-check">
					<li>
						<input type="checkbox" id="chk-rental" name="check[]" value="4" placeholder="Rental">
						<label for="chk-rental">Rental</label>
					</li>
					<li>
						<input type="checkbox" id="chk-exchange" name="check[]" value="5" placeholder="Exchange">
						<label for="chk-exchange">Exchange</label>
					</li>
				</ul>
				<h3>- Resort Type</h3>
				<ul class="list-check">
					<li>
						<input type="checkbox" id="chk-all-inclusive" name="check[]" value="6" placeholder="All-Inclusive Resorts Only">
						<label for="chk-all-inclusive">All-Inclusive Resorts Only</label>
					</li>
				</ul>
			</div>
		</form>
	</div>
</div>