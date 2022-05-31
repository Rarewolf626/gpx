<dialog class="dialog--filter" id="modal-filter-resort" data-width="460" data-min-height="420">
	<div class="w-modal">
		<form action="">
			<div class="block">
				<h2>Filter Results</h2>
				<?php
				/*
				?>
				<select id="select_cities" class="dgt-select filter_resort dd" data-filter="subregions" name="mySelect" placeholder="All Cities">
					<?php
				        $selRegion = '';
				          if(isset($_GET['select_region']))
				              $selRegion = $_GET['select_region'];
					echo do_shortcode('[sc_gpx_subregion_dd type=RegionID country='.$_GET['select_country'].' region='.$selRegion.']')?>
				</select>
				<?
				*/
				?>
				<select id="select_soonest" class="dgt-select filter_resort dd" data-filter="resorttype" name="mySelect" placeholder="Resort /Inventory Type">
					<option value="All" disabled selected ></option>
					<option value="All">All</option>
					<option value="AllInclusive">All-Inclusive</option>
					<option value="ExchangeWeek">Exchange</option>
					<option value="BonusWeek">Rental</option>
				</select>
			</div>
		</form>
	</div>
</dialog>
