<?php
$activeAlert = get_option('gpx_alert_msg_active');
if($activeAlert == 1):?>
<dialog id="modal-alert" data-width="460" data-min-height="135" data-open>
	<div class="w-modal">
		<p><?=get_option('gpx_alert_msg_msg')?></p>
	</div>
</dialog>
<?php endif; ?>
