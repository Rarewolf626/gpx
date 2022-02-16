<?php 
$activeAlert = get_option('gpx_alert_msg_active');
if($activeAlert == 1)
{
?>

<div class="modal dgt-modal modal-alert" id="modal-alert">
	<div class="close-modal"><i class="icon-close"></i></div>
	<div class="w-modal">
		<?php //<div class="icon-alert"></div>?>
		<p><?=get_option('gpx_alert_msg_msg')?></p>
	</div>
</div>
<?php 
}
?>