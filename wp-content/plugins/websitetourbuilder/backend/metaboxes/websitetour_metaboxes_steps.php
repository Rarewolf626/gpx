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
 

global $wp_version; 
  
?>

<div class="my_meta_control">

<!--Inserire Valori null per evitare il salvataggio di step vuoti
installare su joomlaforce plugin clickdesk-->
 
	<p class="warning"><?php echo __( 'These textareas will NOT work without javascript enabled.', 'websitetourbuilder' );?></p>
	<?php /*?><p><?php echo __( 'Repeating Textareas cannot use WP_Editor() and must rely on tinyMCE javascript', 'websitetourbuilder' );?></p><?php */?>
	<a href="#" class="dodelete-repeating_textareas button"><?php echo __('Remove All', 'websitetourbuilder');?></a>
	<p><?php echo __( 'Add new step by using the "Add Step" button.  Rearrange the order of Step by dragging and dropping.', 'websitetourbuilder' );?></p>
 
	<?php 
    $rn = 1;
    while( $mb->have_fields_and_multi( 'repeating_textareas' ) ):    
    ?>
	<?php $mb->the_group_open(); ?>
	<div class="group-wrap <?php echo $mb->get_the_value( 'toggle_state' ) ? ' closed' : ''; ?>" >
		<?php $mb->the_field('toggle_state'); ?>
		<?php // @ TODO: toggle should be user specific ?>
		<input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked( 1, $mb->get_the_value() ); ?> class="toggle_state hidden" />

		<div class="group-control dodelete" title="<?php echo __( 'Click to remove Step', 'websitetourbuilder' );?>"></div>
		<div class="group-control toggle" title="<?php echo __( 'Click to toggle', 'websitetourbuilder' );?>"></div>

		<?php $mb->the_field('steptitle'); ?>
		<?php // need to html_entity_decode() the value b/c WP Alchemy's get_the_value() runs the data through htmlentities() ?>
		<h3 class="handle"><?php echo $mb->get_the_value() ? substr( strip_tags( html_entity_decode( $mb->get_the_value() ) ), 0, 30 ) : __( 'Step Title', 'websitetourbuilder' );?></h3>
		
      <!--  //INIZIO BOX CAMPI RIPETUTI CON ORDINAMENTO -->
		<div class="group-inside">
        	
			<?php $mb->the_field('steptitle'); ?>
            <p><label><?php echo __( 'Step Title', 'websitetourbuilder' ); ?></label></p>
            <p><input type="text" size="60" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/></p>
                 
			<p class="warning update-warning"><?php echo __( 'Sort order has been changed.  Remember to save the post to save these changes.', 'websitetourbuilder' );?></p>
			<?php
			// 'html' is used for the "Text" editor tab.
			/*if ( 'html' === wp_default_editor() ) {
				add_filter( 'the_editor_content', 'wp_htmledit_pre' );
				$switch_class = 'html-active';
			} else {
				add_filter( 'the_editor_content', 'wp_richedit_pre' );
				$switch_class = 'tmce-active';
			}*/
			?>
            
            <?php 
            //$mb->the_field('textarea'); 
            //the_editor($mb->get_the_value(), $mb->get_the_name());
            
            ?>
            
            <?php 
            /*if ( version_compare( $wp_version, '4.3.0', '<=' ) ) { ?>  

                <?php $mb->the_field('textarea'); ?>
                <p><label>Step Description</label></p>
    			<div class="customEditor wp-core-ui wp-editor-wrap <?php echo 'tmce-active'; //echo $switch_class;?>">			
    				<div class="wp-editor-tools hide-if-no-js">
    					<div class="wp-media-buttons custom_upload_buttons">
    						<?php do_action( 'media_buttons' ); ?>
    					</div>
    					<div class="wp-editor-tabs">
    						<a data-mode="html" class="wp-switch-editor switch-html"> <?php _ex( 'Text', 'Name for the Text editor tab (formerly HTML)' ); ?></a>
    						<a data-mode="tmce" class="wp-switch-editor switch-tmce"><?php _e('Visual'); ?></a>
    					</div>
    				</div><!-- .wp-editor-tools -->
    				<div class="wp-editor-container">
    					  <textarea class="wp-editor-area" rows="10" cols="50" name="<?php $mb->the_name(); ?>" rows="3"><?php echo esc_html( apply_filters( 'the_editor_content', html_entity_decode( $mb->get_the_value() ) ) ); ?></textarea>
    				</div>
    				<p><span><?php echo __('Enter in some text', 'websitetourbuilder');?></span></p>
    			</div> <!-- .end-editor-tools -->           
            
    		<?php } else {*/ ?>
                <p><label><?php echo __( 'Step Description', 'websitetourbuilder' ); ?></label></p>
                <div class="editor_wrap">                
              	<?php $mb->the_field('textarea');
                
                $latest_val =  html_entity_decode( $mb->get_the_value() );
            		$settings = array(
            			'textarea_rows' => '10',
            			'media_buttons' => true,
            			'textarea_name' => $mb->get_the_name()
            		);
            		// need to html_entity_decode() the value b/c WP Alchemy's get_the_value() runs the data through htmlentities()
            		wp_editor( $latest_val,  'mceEditor-'.$rn , $settings );
					//echo "<textarea id=\"mycustomeditor-".$rn."\ name=\"".$mb->get_the_name()."\"></textarea>";
            		?>
                    </div>        
                                                                                
            		<!-- <span>Enter in some text</span>-->
            	
    			<p><span><?php echo __('Enter in some text', 'websitetourbuilder');?></span></p>
            
            <?php //}  ?>
            

                
            <?php $selectedst = ' selected="selected"'; ?>
            <?php $mb->the_field('steptype'); ?>
            <p><label><?php echo __('Step Type', 'websitetourbuilder');?></label></p>
            <p><select name="<?php $mb->the_name(); ?>">
            <option value=""></option>
            <option value="modal"<?php if ($mb->get_the_value() == 'modal') echo $selectedst; ?>><?php echo __('Modal', 'websitetourbuilder');?></option>
            <option value="tooltip"<?php if ($mb->get_the_value() == 'tooltip') echo $selectedst; ?>><?php echo __('Tooltip', 'websitetourbuilder');?></option>
            <option value="nohighlight"<?php if ($mb->get_the_value() == 'nohighlight') echo $selectedst; ?>><?php echo __('No Highlight', 'websitetourbuilder');?></option>
            </select></p>
            
            <?php $selectedwt = ' selected="selected"'; ?>
            <?php $mb->the_field('wraptype'); ?>
            <p><label><?php echo __('Wrapper Type', 'websitetourbuilder');?></label><p>
            <select name="<?php $mb->the_name(); ?>">
            <option value=""></option>
            <option value="id"<?php if ($mb->get_the_value() == 'id') echo $selectedwt; ?>><?php echo __('ID', 'websitetourbuilder');?></option>
            <option value="class"<?php if ($mb->get_the_value() == 'class') echo $selectedwt; ?>><?php echo __('Class', 'websitetourbuilder');?></option>
            <option value="name"<?php if ($mb->get_the_value() == 'name') echo $selectedwt; ?>><?php echo __('Name', 'websitetourbuilder');?></option>
            </select></p>
            
            <?php $mb->the_field('wrapname'); ?> 
            <p> <label><?php echo __('Wrapper Selector', 'websitetourbuilder');?> </label></p>
            <span><?php echo __('for class insert the point. Eg. ".entry-title"', 'websitetourbuilder');?></span>
            <p><input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
            </p>
            
            <?php $mb->the_field('stepwidth'); ?> 
            <p> <label><?php echo __('Width', 'websitetourbuilder');?> </label></p>
            <p> <input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
            </p>
            
            
            <?php $selectedpos = ' selected="selected"'; ?>
            <?php $mb->the_field('steppos'); ?>
            <p><label><?php echo __('Position', 'websitetourbuilder');?></label></p> 
            <p> <select name="<?php $mb->the_name(); ?>">
            <option value=""></option>
            <option value="top"<?php if ($mb->get_the_value() == 'top') echo $selectedpos; ?>><?php echo __('Top', 'websitetourbuilder');?></option>
            <option value="bottom"<?php if ($mb->get_the_value() == 'bottom') echo $selectedpos; ?>><?php echo __('Bottom', 'websitetourbuilder');?></option>
            <option value="right"<?php if ($mb->get_the_value() == 'right') echo $selectedpos; ?>><?php echo __('Right', 'websitetourbuilder');?></option>
            <option value="left"<?php if ($mb->get_the_value() == 'left') echo $selectedpos; ?>><?php echo __('Left', 'websitetourbuilder');?></option>
            </select></p>
            
            <?php $selectedwd = ' selected="selected"'; ?>
            <?php $mb->the_field('wrapdrag'); ?>
            <p><label><?php echo __('Draggable', 'websitetourbuilder');?></label><p>
            <select name="<?php $mb->the_name(); ?>">
            <option value=""></option>
            <option value="no"<?php if ($mb->get_the_value() == 'no') echo $selectedwd; ?>><?php echo __('No', 'websitetourbuilder');?></option>
            <option value="yes"<?php if ($mb->get_the_value() == 'yes') echo $selectedwd; ?>><?php echo __('Yes', 'websitetourbuilder');?></option>
            </select></p>
            
            <?php $mb->the_field('steprotation'); ?>
            <p><label><?php echo __('Rotation (degrees)', 'websitetourbuilder');?></label></p>
            <p><input type="text" size="60" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/></p>
            
            <?php $mb->the_field('stepredirect'); ?>
            <p><label><?php echo __('Redirect Url', 'websitetourbuilder');?></label></p>
            <span><?php echo __('Always insert prefix http://', 'websitetourbuilder');?></span>
            <p><input type="text" size="60" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/></p>
            
          <?php /*?>  <?php $mb->the_field('steptime'); ?>
            <p><label><?php echo __('Time', 'websitetourbuilder');?></label></p>
            <p><input type="text" size="60" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/></p><?php */?>
            
		</div><!-- .group-inside -->
	</div><!-- .group-wrap -->

	<?php $mb->the_group_close(); ?>
	<?php 
    $rn++;
    endwhile;     
    ?>
    
    <script type="text/javascript">
    var total_steps_editor = <?php echo $rn; ?>;
    </script>

	<p><a href="#" class="docopy-repeating_textareas button"><span class="icon add"></span><?php echo __( 'Add Step', 'websitetourbuilder' );?></a></p>	
	<p class="meta-save" style="float:right; padding: 10px;"><button type="submit" class="button-primary" name="save"><?php echo __('Update', 'websitetourbuilder');?></button></p>
	<div style="clear:both"></div>
</div>