<tr>
	<th><?php _e('Export All Login Records', 'when-last-login-export-user-records'); ?></th>
	<td><a href='<?php echo admin_url('admin.php?page=when-last-login-settings&tab=export-user-records').'&export=login-records&type=csv'; ?>' target='_BLANK' class='button button-primary'><?php _e('Export to CSV', 'when-last-login-export-user-records'); ?></a> <a href='<?php echo admin_url('admin.php?page=when-last-login-settings&tab=export-user-records').'&export=login-records&type=json'; ?>' target='_BLANK' class='button button-primary'><?php _e('Export to JSON', 'when-last-login-export-user-records'); ?></a></td>
</tr>

<tr>
	<th><?php _e('Export All User Records', 'when-last-login-export-user-records'); ?></th>
	<td><a href='<?php echo admin_url('admin.php?page=when-last-login-settings&tab=export-user-records').'&export=user-records&type=csv'; ?>' target='_BLANK' class='button button-primary'><?php _e('Export to CSV', 'when-last-login-export-user-records'); ?></a> <a href='<?php echo admin_url('admin.php?page=when-last-login-settings&tab=export-user-records').'&export=user-records&type=json'; ?>' target='_BLANK' class='button button-primary'><?php _e('Export to JSON', 'when-last-login-export-user-records'); ?></a></td>
</tr>