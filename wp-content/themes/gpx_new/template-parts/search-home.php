<div class="dgt-container w-box">
	<div class="gsub-title">
		<h3>GPX Is your private exchange service</h3>
	</div>
	<h2 class="gtitle"> Vacation Somewhere New </h2>

	<form id="home-search" class="" role="search" method="get" action="<?php echo home_url( '/result/' ); ?>">
        <label for="search-location" class="ada-text">Type a Location OR Select a Top Destination</label>
		<fieldset class="location-search-fields">
			<div class="location-search-field location-search-field--location search-autocomplete">
                <select aria-label="location" name="location" id="search-location" placeholder="Type a Location OR Select a Top Destination" required>
                    <option value="">Type a Location OR Select a Top Destination</option>
                </select>
			</div>
            <div class="location-search-dates">
			<div class="location-search-field location-search-field--month">
				<label for="select_month" class="ada-text">Select Month</label>
                <?php $months = ['January','February','March','April','May','June','July','August','September','October','November','December']; ?>
                <?php $selMonth = gpx_search_month(); ?>
                <div class="gpx-custom-select">
				<select aria-label="select month" id="select_month" name="month">
					<option class="placeholder" value="" disabled <?= $selMonth ? '' : 'selected'?>>Select Month</option>
 					<option value="any" <?= 'any' === $selMonth ? 'selected' : ''?>>All</option>

                    <?php foreach ($months as $month): ?>
                        <option value="<?= esc_attr($month)?>" <?= $month === $selMonth ? 'selected' : ''?>><?= esc_html($month)?></option>
                    <?php endforeach; ?>
				</select>
                </div>
            </div>
            <div class="location-search-field location-search-field--year">
				<label for="select_year" class="ada-text">Select Year</label>
                <?php $years = range((int)date('Y'), (int)date('Y') + 1); ?>
                <?php $selYear = (int)gpx_search_year(); ?>
                <div class="gpx-custom-select">
				<select aria-label="select year" id="select_year"  name="yr">
                    <option class="placeholder" value="" disabled="" <?php if(!in_array($selYear,$years)) echo 'selected="selected"';?>>This Year</option>
                    <?php foreach($years as $year): ?>
                        <option value="<?= esc_attr($year) ?>" <?= $year == $selYear ? 'selected' : ''?>><?= esc_html($year) ?></option>
                    <?php endforeach; ?>
				</select>
                </div>
			</div>
            </div>
		</fieldset>
        <div>
            <button type="submit" class="dgt-btn">Search</button>
        </div>
	</form>

	<div id="trigger1"></div>
	<div id="trigger2"></div>
	<div id="trigger3"></div>
</div>
