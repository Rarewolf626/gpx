<?php
$redirect_to = get_permalink();
if(str_replace(site_url(), "", $redirect_to) == '/hello-world/')
  $redirect_to = site_url();
if(isset($_GET))
{
    $qs = array();
    foreach($_GET as $key=>$value)
    {
       $qs[] = $key."=".$value;
    }
    if(count($qs) > 0 && !empty($qs[0]))
    $redirect_to .= '?'.implode('&', $qs);
}
?>
<form id="form-login-footer"  class="material">
	<input type="hidden" name="action" value="gpx_user_login">
	<input type="hidden" name="token" value="<?php echo date('dmYHis'); ?>">
	<input type="hidden" name="redirect_to" value="/member-dashboard/">
	<div class="gform_body">
		<ul class="gform_fields">
			<li class="message-box"><span></span></li>
			<li class="gfield">
				<div class="ginput_container">
					<input aria-label="email" type="text" id="user_email_footer" name="user_email_footer" placeholder="Username" class="validate" autocomplete="username" required="required"/>
				</div>
			</li>
			<li class="gfield">
				<div class="ginput_container">
					<input autocomplete="off" aria-label="password" id="user_pass_footer" data-id="user_pass_footer" name="user_pass_footer" type="password" placeholder="Password" class="validate" autocomplete="current-password" required="required"/>
				</div>
			</li>
			<li class="gfield">
				<a href="#" class="call-modal-pwreset">Forgot password?</a>
			</li>
		</ul>
	</div>
	<div class="gform_footer">
		<input class="btn-login btn-user-login" type="submit" value="Sign In">
	</div>
</form>
