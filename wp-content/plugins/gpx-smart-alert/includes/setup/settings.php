<?php 

/*
 * set the general fees
 */
/*  swittching this to the settings page.
 * 
 *
function gpr_sb_settings_settings_api_init() 
{
    // Add the section to reading settings so we can add our
    // fields to it
    add_settings_section(
        'gpr_sb_settings',
        'GPR Smartbar Settings',
        'gpr_sb_settings_callback_function',
        'reading'
        );
    
    // Add the field with the names and function to use for our new
    // settings, put it in our new section
    add_settings_field(
        'gpr_smartbar_parent_url',
        'Parent Website URL',
        'gpr_sb_url_setting_callback_function',
        'reading',
        'gpr_sb_settings'
        );
    
    
    // Register our setting so that $_POST handling is done for us and
    // our callback function just has to echo the <input>
    register_setting( 'reading', 'gpr_smartbar_parent_url' );
} // eg_settings_api_init()
add_action( 'admin_init', 'gpr_sb_settings_settings_api_init' );


// ------------------------------------------------------------------
// Settings section callback function
// ------------------------------------------------------------------
//
// This function is needed if we added a new section. This function
// will be run at the start of our section
//

function gpr_sb_settings_callback_function() 
{
    echo '<p>Set SmartBar Parent Website Below...</p>';
}

// ------------------------------------------------------------------
// Callback function for our example setting
// ------------------------------------------------------------------
//
// creates a checkbox true/false option. Other types are surely possible
//

function gpr_sb_url_setting_callback_function() 
{
    echo '<input name="gpr_smartbar_parent_url" id="gpr_smartbar_parent_url" value="'.get_option('gpr_smartbar_parent_url').'" type="text" class="code" placeholder="https://" />';
}
*/
?>