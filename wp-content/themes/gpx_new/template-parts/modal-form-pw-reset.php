<?php
if(isset( $_REQUEST['action'] ) && $_REQUEST['action'] = 'rp')
{
    $pwreset = 'class="material" style="display: none;"';
   $pwset = 'class="material"';
   $am = 'active-modal';
   $open = true;
}
else
{
    $pwreset = 'class="material"';
    $pwset = 'class="material" style="display: none;"';
    $am = '';
    $open = false;
}
?>

<dialog id="modal-pwreset" data-width="800" data-height="500" data-close-on-outside-click="false" <?php echo $open ? 'data-open' : ''?>>
	<div class="w-login">
		<h2>Password Reset</h2>
		<div class="gform_wrapper">
			<form id="form-pwreset" <?=$pwreset?>>
				<div class="gform_body">
                 	<ul class="gform_fields">
                 		<input type="hidden" name="action" value="request_password_reset">
						<li class="message-box"><span></span></li>
						<li class="gfield">
							<label for="user_login" class="gfield_label"></label>
							<div class="ginput_container">
								<input aria-label="email" type="text" name="user_login_pwreset" id="user_login_pwreset" placeholder="Email Address or Member Number" class="validate" autocomplete="off" required="required"/>
							</div>
						</li>
						<li class="gfield">
							<a href="#" class="call-modal-login">Return to Sign In</a>
						</li>
					</ul>
                    <div class="gform_footer">
						<input class="btn-user-login" type="submit" value="Reset">
					</div>
				</div>
			</form>
			<form id="form-pwset" <?=$pwset?>>
				<div class="gform_body">
					<ul class="gform_fields">
						<input type="hidden" name="action" value="do_password_reset">
						<input type="hidden" id="user_login_passreset" name="rp_login" value="<?php echo $_REQUEST['login']; ?>" autocomplete="off" />
        				<input type="hidden" name="rp_key" value="<?php echo  $_REQUEST['key']; ?>" />
        				<li class="message-box"><span></span></li>
						<li class="pass1">
							<label for="user_login" class="gfield_label"></label>
							<div class="ginput_container">
								<input aria-label="password" type="password" name="pass1" id="pass1" class="input" size="20" placeholder="Password" value="" autocomplete="off" />
							</div>
						</li>
						<li class="gfield">
							<label for="pass2" class="gfield_label"></label>
							<div class="ginput_container">
								<input aria-label="repeat password" type="password" name="pass2" id="pass2" class="input" size="20" placeholder="Repeat Password" value="" autocomplete="off" />
							</div>
						</li>
						<li class="gfield">
							<a href="#" class="call-modal-login">Return to Sign In</a>
						</li>
					</ul>
                    <div class="gform_footer">
						<input class="btn-user-login" type="submit" value="Reset">
					</div>
				</div>
			</form>
		</div>
	</div>
</dialog>

