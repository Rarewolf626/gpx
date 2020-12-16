<div class="rwmb-field rwmb-text-wrapper">
	<div class="rwmb-label">
		<label for="gpx_offer_start_date">Start Date</label>
	</div>
	<div class="rwmb-input">
		<input type="date" value="<?php echo $gpx_offer_start_date; ?>" id="gpx_offer_start_date" class="rwmb-text " name="gpx_offer_start_date" />
	</div>
</div>
<div class="rwmb-field rwmb-text-wrapper">
	<div class="rwmb-label">
		<label for="gpx_offer_end_date">End Date</label>
	</div>
	<div class="rwmb-input">
		<input type="date" value="<?php echo $gpx_offer_end_date; ?>" id="gpx_offer_end_date" class="rwmb-text " name="gpx_offer_end_date" />
	</div>
</div>
<div class="rwmb-field rwmb-text-wrapper  required">
	<div class="rwmb-label">
		<label for="gpx_subtitle">Offer subtitle</label>
	</div>
	<div class="rwmb-input">
		<input type="text"  size="50" name="gpx_subtitle" value="<?php echo $gpx_subtitle; ?>" placeholder="subtitle" />
	</div>
</div>

<div class="rwmb-field rwmb-divider-wrapper clear_both"><hr></div>

<div class="rwmb-field rwmb-text-wrapper">
	<div class="rwmb-label">
		<label for="gpx_promo_code">Offer code</label>
	</div>
	<div class="rwmb-input">
		<input type="text" size="50" value="<?php echo $gpx_promo_code; ?>" id="gpx_promo_code" class="rwmb-text " name="gpx_promo_code" />
	</div>
</div>

<div class="rwmb-field rwmb-divider-wrapper clear_both"><hr></div>

<div class="rwmb-field rwmb-text-wrapper">
	<div class="rwmb-label-text">
		<label for="gpx_term_condition">Terms and conditions</label>
	</div>
	<div class="rwmb-input-text">
		<?php
		wp_editor( $gpx_term_condition, 'gpx_term_condition', array(
			'wpautop'       => true,
			'media_buttons' => false,
			'textarea_name' => 'gpx_term_condition',
			'textarea_rows' => 10,
			'teeny'         => true
		) );
		?>
	</div>
</div>

<div class="rwmb-field rwmb-divider-wrapper clear_both"><hr></div>

<div class="rwmb-field rwmb-text-wrapper ">
	<div class="rwmb-label">
		<label for="gpx_show">Show on homepage? </label>
	</div>
	<div class="rwmb-input">
		<input value="1" id="gpx_show" class="rwmb-checkbox " name="gpx_show" <?php echo ($gpx_show)? 'checked="checked"' : ''; ?> type="checkbox">
	</div>
</div>

<div class="rwmb-field rwmb-divider-wrapper clear_both"><hr></div>

<div class="rwmb-field rwmb-text-wrapper ">
	<div class="rwmb-label">
		<label for="gpx_extra_title">Homepage title (optional)</label>
	</div>
	<div class="rwmb-input">
		<input type="text" size="50" id="gpx_extra_title" name="gpx_extra_title" value="<?php echo $pgx_extra_title; ?>" />
	</div>
</div>

<div class="rwmb-field rwmb-divider-wrapper clear_both"><hr></div>

<div class="rwmb-field rwmb-text-wrapper ">
	<div class="rwmb-label">
		<label for="gpx_extra_title">Homepage Description</label>
	</div>
	<div class="rwmb-input">
		<input type="text" size="50" id="gpx_extra_desc" name="gpx_extra_desc" value="<?php echo $pgx_extra_desc; ?>" />
	</div>
</div>

<div class="rwmb-field rwmb-divider-wrapper clear_both"><hr></div>

<div class="rwmb-field rwmb-text-wrapper ">
	<div class="rwmb-label">
		<label for="gpx_extra_order">Homepage order</label>
	</div>
	<div class="rwmb-input">
		<select id="gpx_extra_order" class="rwmb-select" name="gpx_extra_order">
		<?php 
		for($i=1;$i<=20;$i++)
		{
		?>
			<option value="<?=$i?>" <?php selected( $gpx_extra_order, $i ); ?>><?=$i?></option>			
		<?php 
		}
		?>
		</select>
	</div>
</div>
<div class="rwmb-field rwmb-divider-wrapper clear_both"><hr></div>
<div class="rwmb-field rwmb-text-wrapper">
	<div class="rwmb-label">
		<label for="gpx_promo_code">Homepage Button Text</label>
	</div>
	<div class="rwmb-input">
		<input type="text" value="<?php echo $gpx_box_button_text; ?>" id="gpx_box_button_text" class="rwmb-text " name="gpx_box_button_text" />
	</div>
</div>
<div class="rwmb-field rwmb-divider-wrapper clear_both"><hr></div>
<div class="rwmb-field rwmb-text-wrapper">
	<div class="rwmb-label">
		<label for="gpx_promo_code">Homepage Button URL</label>
	</div>
	<div class="rwmb-input">
		<input type="text" value="<?php echo $gpx_box_button_url; ?>" id="gpx_box_button_url" class="rwmb-text " name="gpx_box_button_url" />
	</div>
</div>
<div class="rwmb-field rwmb-divider-wrapper clear_both"><hr></div>
<div class="rwmb-field rwmb-text-wrapper">
	<div class="rwmb-label">
		<label for="gpx_promo_code">Offer Page Button Text</label>
	</div>
	<div class="rwmb-input">
		<input type="text" value="<?php echo $gpx_offer_button_text; ?>" id="gpx_offer_button_text" class="rwmb-text " name="gpx_offer_button_text" />
	</div>
</div>
<div class="rwmb-field rwmb-divider-wrapper clear_both"><hr></div>
<div class="rwmb-field rwmb-text-wrapper">
	<div class="rwmb-label">
		<label for="gpx_promo_code">Offer Page Button URL</label>
	</div>
	<div class="rwmb-input">
		<input type="text" value="<?php echo $gpx_offer_button_url; ?>" id="gpx_offer_button_url" class="rwmb-text " name="gpx_offer_button_url" />
	</div>
</div>

<div class="rwmb-field rwmb-divider-wrapper clear_both"><hr></div>