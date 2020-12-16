<?php 
function gpr_sb_store_hidden()
{
    global $wpdb;
    
    $databaseTable = $wpdb->prefix . 'gpr_smartbar_hide';
    
    if(isset($_POST['ip']) && isset($_POST['name']))
    {
        $date = date('Y-m-d H:i:s');
        $wpdb->insert($databaseTable, array('name'=>$_POST['name'], 'user_ip'=>$_POST['ip'], 'time'=>$date));
    }
    $output = array('success'=>true);
    
    echo wp_send_json($output);
    exit();
}
add_action("wp_ajax_gpr_sb_store_hidden","gpr_sb_store_hidden");
add_action("wp_ajax_nopriv_gpr_sb_store_hidden", "gpr_sb_store_hidden");
?>