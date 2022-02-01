<form action="">
    <div class="w-options w-results">
        <h4>Displaying Resorts In:</h4>
        <div class="cnt left resort">
            <select id="select_country" class="dgt-select" name="select_country" placeholder="Country">
				<?php echo do_shortcode('[sc_countryregion_dd]')?>
            </select>
        </div>
        <div class="cnt right resort">
            <select id="select_location" class="dgt-select" name="select_location" placeholder="Location">
				<?php echo do_shortcode('[sc_countryregion_dd country='.$_GET['select_country'].']')?>
            </select>
        </div>
    </div>
</form>