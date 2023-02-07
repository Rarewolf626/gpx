<section class="w-banner w-results w-results-home w-profile">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result rsviewprofile">
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg" alt="" />
        </li>
    </ul>
    <div class="w-options">
        <hgroup>
            <h1><div><?=$usermeta->LastName1?>, <?=$usermeta->FirstName1?> | <a href="/view-profile">View Profile</a></div></h1>
        </hgroup>
        <div class="p">
          <p>Exchange Credits: <strong><span id="creditBal"></span></strong></p>
        </div>
    </div>
</section>
<div class="content content-table transaction-load" data-load="load_transactions" data-id="<?=$cid?>" data-type="transactions"></div>
<div class="content content-table">
	<div class="content-inner">
	<?php 
	if(isset($usermeta->GP_Preferred) && $usermeta->GP_Preferred == 'Yes')
	{
	?>
		<div class="gp-preferred">
			<h3>Grand Pacific Preferred</h3>
		</div>
	<?php 
	}
	?>
		<h1 class="content-heading"><?=$usermeta->FirstName1?>, Your Next Vacation Starts Here</h1>
		<div class="md-boxes">
			<div class="md-box">
				<a class="md-btn" href="/">Find A<br>Vacation</a>
				<p>Browse our full list of weeks to find the perfect one for your next vacation.</p>
			</div>
			<div class="md-box">
				<a class="md-btn custom-request" href="#" data-cid="<?=$cid?>" data-pid="">Submit A Special<br>Request</a>
				<p>Ask our vacation specialist to help you find a week in your bucket list destination.</p>
			</div>
			<div class="md-box">
				<a href="#modal-deposit" class="md-btn deposit better-modal-link">Deposit A<br>Week</a>
				<p>You can deposit your week now and use the credit to book a vacation later.</p>
			</div>
			<?php if((isset($usermeta->ICEStore) && $usermeta->ICEStore != 'No') || !isset($usermeta->ICEStore)):?>
			<div class="md-box" style="display: none;">
				<a href="#" class="md-btn ice-link" data-cid="<?=$cid?>"><br>Cruise-Exchange</a>
				<p>Exchange your week for a discounted cruise vacation.</p>
			</div>
			<?php endif;?>
		</div>
		<hr>
	</div>
</div>
