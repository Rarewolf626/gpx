<?php
$redirect_to = get_permalink();
if(str_replace(site_url(), "", $redirect_to) == '/hello-world/')
  $redirect_to = site_url();
if(isset($_GET))
{
    if(isset($_GET['login_again']))
    {
    ?>
    <div id="recred"></div>
    <?php
    }
    if(isset($_GET['welcome']))
    {
    ?>
    <div id="welcome_create" data-wc="<?=$_GET['welcome']?>"></div>
    <?php
    }
    $qs = array();
    foreach($_GET as $key=>$value)
    {
       $qs[] = $key."=".$value;
    }
    if(count($qs) > 0 && !empty($qs[0]))
    $redirect_to .= '?'.implode('&', $qs);
}
$redirect_to = home_url();
?>
<dialog id="modal-login" data-width="800" data-height="500" data-close-on-outside-click="false">
	<div class="w-login">
		<h2>GPX Member Sign In</h2>
		<div class="gform_wrapper">
			<form id="form-login" class="material">
				<input type="hidden" name="action" value="gpx_user_login">
				<input type="hidden" name="token" value="<?php echo date('dmYHis'); ?>">
				<input type="hidden" name="redirect_to" id="redirectTo" value="<?=$redirect_to?>">
				<div class="gform_body">
					<ul class="gform_fields">
						<li class="message-box"><span></span></li>
						<li class="gfield">
							<label for="modal_user_email" class="gfield_label sr-only">Username</label>
							<div class="ginput_container">
								<input aria-label="email" type="text" id="modal_user_email" name="user_email" placeholder="Username" class="validate" autocomplete="username" required="required"/>
							</div>
						</li>
						<li class="gfield">
							<label for="modal_user_pass" class="gfield_label sr-only">Password</label>
							<div class="ginput_container">
								<input aria-label="password" id="modal_user_pass" name="user_pass" type="password" placeholder="Password" class="validate" autocomplete="current-password" required="required"/>
							</div>
						</li>
						<li class="gfield">
							<a href="#" class="call-modal-pwreset">Forgot password?</a>
						</li>
					</ul>
				</div>
				<div class="gform_footer">
					<input class="btn-user-login" id="btn-signin" type="submit" value="Sign In">
				</div>
			</form>
		</div>
	</div>
</dialog>
