<section class="universal-search-widget-band">
    <form class="universal-search-widget dgt-container w-box"  role="search" method="get" action="<?php echo home_url( '/result/' ); ?>">
    	<div class="usw-text">Vacation Somewhere New</div>
    	<div class="usw-dest search-autocomplete--results">
			<label for="search-location" class="ada-text">Select a location, resort or top destination</label>
			<?php $selLocation = gpx_request('location', gpx_request('resortName', '')); ?>
            <select aria-label="location" name="location" id="search-location" placeholder="Type a Location OR Select a Top Destination" required>
                <?php if($selLocation):?>
                    <option value="<?= esc_attr($selLocation) ?>" selected><?= esc_html($selLocation) ?></option>
                <?php endif;?>
            </select>
    	</div>
		<div class="SumoSelect sumo_select_month usw-month-year" tabindex="0">
            <label for="select_month" class="ada-text">Select Year</label>
            <?php $selMonth = gpx_search_month(); ?>
            <?php $months = ['January','February','March','April','May','June','July','August','September','October','November','December']; ?>
			<select aria-label="select month" id="select_month" class="dgt-select SumoUnder" name="month" placeholder="Month" tabindex="-1">
    			<option class="placeholder" value="" disabled="" selected=""></option>
    			<option value="any" <?php if(!in_array($selMonth,$months)) echo 'selected="selected"';?>>All</option>
				<?php foreach ($months as $month): ?>
                    <option value="<?= esc_attr($month)?>" <?= $month === $selMonth ? 'selected' : ''?>><?= esc_html($month)?></option>
                <?php endforeach; ?>
			</select>
		</div>
		<div class="SumoSelect sumo_select_year usw-month-year" tabindex="0">
            <label for="select_year" class="ada-text">Select Year</label>
            <?php $selYear = (int)gpx_search_year(); ?>
            <?php $years = range((int)date('Y'), (int)date('Y') + 1); ?>
			<select aria-label="select year" id="select_year" class="dgt-select SumoUnder" name="yr" placeholder="Year" tabindex="-1">
				<option class="placeholder" value="" disabled="" <?php if(!in_array($selYear,$years)) echo 'selected="selected"';?>></option>
				<?php foreach($years as $year): ?>
				    <option value="<?= esc_attr($year) ?>" <?= $year == $selYear ? 'selected' : ''?>><?= esc_html($year) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
    	<div class="usw-button">
    		<input type="submit" class="dgt-btn" value="Search" />
    	</div>
    </form>
</section>
