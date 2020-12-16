<?php
if ( !is_admin() ) {
    echo 'Direct access not allowed.';
    exit;
}
global $wpdb;
global $wp_roles;

	if($_POST['settings_form_submit'] == 'Y') {
		//Form data sent
        
        /*echo '<pre>';
        print_r($_POST);
        
        die();*/
        
        
        if(isset($_POST['cookie_enable'])){
    		$cookie_enable = $_POST['cookie_enable'];
    		update_option('cookie_enable', $cookie_enable);
        } else {
            update_option('cookie_enable', 'N');
        }           
        
		$cookie_expired_date = $_POST['cookie_expired_date'];
		update_option('cookie_expired_date', $cookie_expired_date);
        
		$allow_usertypes = serialize($_POST['allow_usertypes']);
		update_option('allow_usertypes', $allow_usertypes);

        
		?>
		<div class="updated"><p><strong><?php _e('Configuration has been saved.'); ?></strong></p></div>
		<?php
	} else {
		//Normal page display
		$cookie_enable = get_option('cookie_enable');
		$cookie_expired_date = get_option('cookie_expired_date');
		$allow_usertypes = get_option('allow_usertypes');
	}

?>

<script type="text/javascript">
  jQuery(function() {
    
    jQuery('.cookie_enable').click(function() {
        if(jQuery(this).is(":checked")) {
            if(jQuery(this).val() == 'Y')
			{
				jQuery('#dateSelectWrap').show();
			} else {
				jQuery('#dateSelectWrap').hide();
			}
        }
    });
    
    jQuery( "#datepicker" ).datepicker();
  });
</script>

<div class="wrap">
	<h2 style="padding-bottom: 10px;"><?php _e( 'Slider Configuration', 'slider_settings' ); ?></h2>

    
<form name="slider_settings_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">

    <input type="hidden" name="settings_form_submit" value="Y" />

    <table class="wpsc-edit-module-options wp-list-table widefat plugins" id="slider-table-settings">
			
			<tbody>

                	<tr>
                		<th style="padding-bottom: 10px;">
                			<label for="cookie_enable"><?php _e('Cookies Enable', 'slider_settings'); ?>:</label>
                		</th>
                		<td style="padding-bottom: 10px;">
            				<label><input <?php echo ($cookie_enable == "Y") ? 'checked="checked"' : ''; ?> type="radio" name="cookie_enable" value="Y" id="cookie_enableY" class="cookie_enable" /> <?php _e('Yes', 'slider_settings'); ?> &nbsp; &nbsp;</label>
            				<label><input <?php echo ($cookie_enable == "N") ? 'checked="checked"' : ''; ?> type="radio" name="cookie_enable" value="N" id="cookie_enableN" class="cookie_enable" /> <?php _e('No', 'slider_settings'); ?></label>
                            <div id="dateSelectWrap" style="padding: 10px 0;<?php echo ($cookie_enable == "Y") ? 'display:block' : 'display:none'; ?>">
                                <b>Expired Date</b> : <br/> <input type="text" id="datepicker" name="cookie_expired_date" value="<?php echo $cookie_expired_date ?>" />
                            </div>
                			<span class="howto">
                  				(The tour will show always if cookies are set to no else consider cookies days.)
                  			</span>
                  		</td>
                  	</tr>
                    <tr>
                        <td colspan="2"><hr /></td>
                    </tr>
            		<tr>
            			<th><label for="autoslideY"><?php _e('Allow User Types', 'slider_settings'); ?></label></th>
            			<td> 
                            <select name="allow_usertypes[]" multiple="multiple">
                            <?php
                                
                                $selected_user_type = unserialize($allow_usertypes);
                              
                                if ( isset( $wp_roles ) )
                                {
                                    $wp_roles = new WP_Roles();
                                    
                                    foreach($wp_roles->get_names() as $key=>$role)
                                    {
                                        echo '<option value="'.$key.'"'.(in_array($key, $selected_user_type) ? ' selected' : '' ).'>'.$role.'</option>';
                                    }
                                
                                }  
                            ?>
                            </select>
            			</td>
            		</tr>    
                	
                
            </tbody>
    </table>
    
    	    
	<p class="submit"><input type="submit" value="Save Changes" class="button button-primary" id="submit" name="submit"></p>
</form>    
</div>