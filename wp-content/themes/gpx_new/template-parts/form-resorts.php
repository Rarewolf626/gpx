<form class="" id="resortsSearchForm" role="search" method="get" action="<?php echo home_url( '/resorts-result' ); ?>">
    <div class="w-options">
        <div class="cnt left">
            <h4>Find a Resort by Name</h4>
            <div class="component">
                <label for="resort_autocomplete" class="ada-text">Resort Name</label><input id="resort_autocomplete" name="resortName" placeholder="Type a Resort Name">
            </div>
        </div>
        <div class="center">
            <span>Or</span>
        </div>
        <div class="cnt right">
            <h4>Find Resort by Location</h4>
            <label for="select_country" class="ada-text">Select Country</label>
            <select id="select_country" class="dgt-select" name="select_country" placeholder="Country">
				<?php echo do_shortcode('[sc_countryregion_dd]')?>
            </select>
            <label for="select_location" class="ada-text">Select Location</label>
            <select id="select_location" class="dgt-select" name="select_region" placeholder="Location" disabled>
            	<option class="disabled"></option>
            	<option>Please Select A Country First</option>
            </select>
        </div>
    </div>
    <input type="submit" class="dgt-btn" value="Explore">
</form>