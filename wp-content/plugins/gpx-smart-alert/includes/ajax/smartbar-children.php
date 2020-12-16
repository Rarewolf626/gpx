<?php 
function gpr_new_child()
{
    global $wpdb;
    
    if(!check_ajax_referer('gsa-security-nonce', 'secure'))
    {
        wp_send_json_error('Invalid security token sent!');
    }
    
    $current = get_option('gpr_smartbar_children');
    
    if(empty($current))
    {
        $thisisnew = true;
    }
    
    $currentChildren = unserialize( base64_decode( $current ) );;
    $currentChildren[] = [
        'name'=>sanitize_text_field($_POST['name']),
        'url'=>sanitize_text_field($_POST['url']),
    ];
    
    $newChildren = base64_encode(serialize($currentChildren));
    
    if($thisisnew)
    {
        add_option('gpr_smartbar_children', $newChildren);
    }
    else
    {
        update_option('gpr_smartbar_children', $newChildren);
    }
    
    $output = array('success'=>true);
    
    echo wp_send_json($output);
    exit();
}
add_action("wp_ajax_gpr_new_child","gpr_new_child");

function gpr_remove_child()
{
    global $wpdb;
    
    if(!check_ajax_referer('gsa-security-nonce', 'secure'))
    {
        wp_send_json_error('Invalid security token sent!');
    }
    
    $current = get_option('gpr_smartbar_children');
    
    $currentChildren = unserialize( base64_decode( $current ) );
    
    $remove = sanitize_text_field($_POST['item']);
    
    foreach($currentChildren as $key=>$value)
    {
        if($value['name'] == $remove)
        {
            unset($currentChildren[$key]);
            break;
        }
    }
    
    $newChildren = base64_encode(serialize($currentChildren));
    
    update_option('gpr_smartbar_children', $newChildren);
    
    $output = array('success'=>true);
    
    echo wp_send_json($output);
    exit();
}
add_action("wp_ajax_gpr_remove_child","gpr_remove_child");

function gpr_update_parent()
{
    
    if(!check_ajax_referer('gsa-security-nonce', 'secure'))
    {
        wp_send_json_error('Invalid security token sent!');
    }
    
    $item = sanitize_text_field($_POST['item']);
    $value = sanitize_text_field($_POST['value']);
    
    update_option($item, $value);
    
    $output = array('success'=>true);
    
    echo wp_send_json($output);
    exit();
}
add_action("wp_ajax_gpr_update_parent","gpr_update_parent");
?>