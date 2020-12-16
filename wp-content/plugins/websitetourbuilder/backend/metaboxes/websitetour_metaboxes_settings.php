<?php 
 /**
 * Web Site Tour Builder for Wordpress
 *
 * @package   websitetourbuilder
 * @author    JoomlaForce Team [joomlaforce.com]
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @link      http://joomlaforce.com
 * @copyright Copyright © 2014 JoomlaForce
 */
 
global $wpalchemy_media_access; 
global $wp_roles;
?>

<script type="text/javascript">
  /*jQuery(function() {
    
    jQuery('.cookie_enable').change(function() {
        //if(jQuery(this).is(":selected")) {
            if(jQuery(this).val() == 'yes')
			{
				jQuery('#dateSelectWrap').show();
			} else {
				jQuery('#dateSelectWrap').hide();
			}
        //}
    });
    
    jQuery( "#datepicker" ).datepicker();
  });*/
  
</script>

<div class="my_meta_control">
 
	<p><?php echo __("From this page you can create your own tour. Fill out the general characteristics and define your steps below.", 'websitetourbuilder'); ?></p>
    
	<?php $selecteddas = ' selected="selected"'; ?>
	<p><label><?php echo __("Display As", 'websitetourbuilder'); ?></label></p>
    <p><?php $metabox->the_field('displayas'); ?>
    <select name="<?php $metabox->the_name(); ?>">
    <option value="nodisplay"<?php if ($metabox->get_the_value() == 'nodisplay') echo $selecteddas; ?>><?php echo __("No Display", 'websitetourbuilder'); ?></option>
    <option value="lightbox"<?php if ($metabox->get_the_value() == 'lightbox') echo $selecteddas; ?>><?php echo __("Lightbox on Load", 'websitetourbuilder'); ?></option>
    <option value="autostart"<?php if ($metabox->get_the_value() == 'autostart') echo $selecteddas; ?>><?php echo __("AutoStart on Load", 'websitetourbuilder'); ?></option>
    </select></p>
    
	<p><label><?php echo __("Lightbox Title", 'websitetourbuilder'); ?></label>
	<input type="text" name="<?php $metabox->the_name('lightboxtitle'); ?>" value="<?php $metabox->the_value('lightboxtitle'); ?>"/>
	</p>
 <?php /*?>
	<p><label><?php echo __("Lightbox Description", 'websitetourbuilder'); ?></label>
		<?php $metabox->the_field('lightboxdescription'); ?>
		<textarea name="<?php $metabox->the_name(); ?>" rows="3"><?php $metabox->the_value(); ?></textarea>
	</p>
    <?php */?>
    
    <?php /*?> <p><?php _e('A single textarea that uses the wp_editor() function.');?></p><?php */?>
    <p><label><?php echo __("Lightbox Description", 'websitetourbuilder'); ?></label></p>
	<p><?php $metabox->the_field('lightboxdescription');
		$settings = array(
			'textarea_rows' => '10',
			'media_buttons' => true,
			'tabindex' =>2,
			'textarea_name' => $metabox->get_the_name(),
		);
		// need to html_entity_decode() the value b/c WP Alchemy's get_the_value() runs the data through htmlentities()
		wp_editor( html_entity_decode( $metabox->get_the_value() ),  $metabox->get_the_id() , $settings );
		?>
		<!-- <span>Enter in some text</span>-->
	</p>
    
    <?php $selectedkey = ' selected="selected"'; ?>
    <p><label><?php echo __("Enable Keyboard", 'websitetourbuilder'); ?> </label>
   <?php $metabox->the_field('keyarrows'); ?>
    <select name="<?php $metabox->the_name(); ?>">
    <option value="1"<?php if ($metabox->get_the_value() == '1') echo $selectedkey; ?>><?php echo __("Yes", 'websitetourbuilder'); ?></option>
    <option value="0"<?php if ($metabox->get_the_value() == '0') echo $selectedkey; ?>><?php echo __("No", 'websitetourbuilder'); ?></option>
    </select>
    
  <?php /*?>  <?php $selectedtm = ' selected="selected"'; ?>
    <label><?php echo __("Enable Timer", 'websitetourbuilder'); ?></label>
    <?php $metabox->the_field('wbstime'); ?>
    <select name="<?php $metabox->the_name(); ?>">
    <option value="0"<?php if ($metabox->get_the_value() == '0') echo $selectedtm; ?>><?php echo __("Yes", 'websitetourbuilder'); ?></option>
    <option value="1"<?php if ($metabox->get_the_value() == '1') echo $selectedtm; ?>><?php echo __("No", 'websitetourbuilder'); ?></option>
    </select><?php */?>
    
    <?php $selectedlt = ' selected="selected"'; ?>
    <label><?php echo __("LightBox Theme", 'websitetourbuilder'); ?></label>
    <?php $metabox->the_field('lightboxtheme'); ?>
    <select name="<?php $metabox->the_name(); ?>">
    <option value="default"<?php if ($metabox->get_the_value() == 'default') echo $selectedlt; ?>><?php echo __("Default", 'websitetourbuilder'); ?></option>
    <option value="dark"<?php if ($metabox->get_the_value() == 'dark') echo $selectedlt; ?>><?php echo __("Dark", 'websitetourbuilder'); ?></option>
    </select>
    
    <?php $selectedlt = ' selected="selected"'; ?>
    <label><?php echo __("Popup Theme", 'websitetourbuilder'); ?></label>
   	<?php $metabox->the_field('popuptheme'); ?>
    <select name="<?php $metabox->the_name(); ?>">
    <option value="default"<?php if ($metabox->get_the_value() == 'default') echo $selectedlt; ?>><?php echo __("Default", 'websitetourbuilder'); ?></option>
    <option value="white"<?php if ($metabox->get_the_value() == 'white') echo $selectedlt; ?>><?php echo __("White", 'websitetourbuilder'); ?></option>
    </select></p>
    <hr />
    <p style="width: 25%; float: left;">
        <?php $selectedlt = ' selected="selected"'; ?>
        <label><?php echo __("Enable Cookies", 'websitetourbuilder'); ?> :</label>
        <?php $metabox->the_field('cookie_enable'); 
        $cookie_enable = $metabox->get_the_value()
        ?>
        <select name="<?php $metabox->the_name(); ?>" class="cookie_enable">
        <option value="no"<?php if ($metabox->get_the_value() == 'no') echo $selectedlt; ?>><?php echo __("No", 'websitetourbuilder'); ?></option>
        <option value="yes"<?php if ($metabox->get_the_value() == 'yes') echo $selectedlt; ?>><?php echo __("Yes", 'websitetourbuilder'); ?></option>
        </select>                    
    </p>
    
    <?php $metabox->the_field('cookie_expired_date'); ?>
    <p style="width: 30%; float: left;" id="dateSelectWrap" style="padding: 0;">
        <b><?php echo __("Expire Cookies After", 'websitetourbuilder'); ?> :</b> <input style="width: auto;" type="text" size="10" id="datepicker" name="<?php $metabox->the_name(); ?>" value="<?php echo ($metabox->get_the_value() != '' ? $metabox->get_the_value() : 7); ?>" />
    </p>
    
    <p style="width: 45%; float: left;">
        <?php $selectedlt = ' selected="selected"'; ?>
        <label><?php echo __("Enable User Confirm Dialog", 'websitetourbuilder'); ?> :</label>
        <?php $metabox->the_field('user_confirm_dialog'); 
        $cookie_enable = $metabox->get_the_value()
        ?>
        <select name="<?php $metabox->the_name(); ?>" class="user_confirm_dialog">
        <option value="no"<?php if ($metabox->get_the_value() == 'no') echo $selectedlt; ?>><?php echo __("No", 'websitetourbuilder'); ?></option>
        <option value="yes"<?php if ($metabox->get_the_value() == 'yes') echo $selectedlt; ?>><?php echo __("Yes", 'websitetourbuilder'); ?></option>
        </select>   
    </p>
    
    <div style="clear: both;"></div>
    
    <p>
        <?php $selectedlt = ' selected="selected"'; ?>
        <label><?php echo __("Allow User Types", 'websitetourbuilder'); ?> :</label>
        <?php $metabox->the_field('allow_usertypes'); 
        $selected_user_type = (array)$metabox->get_the_value();
        ?>
        <select name="<?php $metabox->the_name(); ?>[]" class="allow_usertypes" multiple="multiple">
            <option value="all"<?php echo (in_array('all', $selected_user_type) ? ' selected' : '' ); ?>><?php echo __("All", 'websitetourbuilder'); ?></option>
            <?php
                
              
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
    </p>
    
    <p class="meta-save" style="float:right; padding: 10px;" ><button type="submit" class="button-primary" name="save"><?php echo __("Update", 'websitetourbuilder'); ?></button></p>
    <div style="clear:both"></div>
    
</div>