<?php 
function load_gsa_scripts() 
{
    wp_enqueue_style('custom_css', GPR_SA_URI.'/css/gsa-style.css', '', GPR_SA_VERS);
    
    $handle = 'javascript_cookie';
    $list = 'enqueued';
    if (wp_script_is( $handle, $list )) {
        return;
    } else {
        wp_register_script('javascript_cookie', '//cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.3/js.cookie.min.js');
        wp_enqueue_script( 'javascript_cookie' );
    }
    
    wp_enqueue_script('sra_admin_jquery', GPR_SA_URI.'/js/jquery.gsa.js', array('jquery'), GPR_SA_VERS);

}
add_action( 'wp_enqueue_scripts', 'load_gsa_scripts' );

function load_gsa_admin_scripts() 
{
    wp_enqueue_script('sra_admin_jquery', GPR_SA_URI.'/js/jquery.admin.gsa.js', array('jquery'), GPR_SA_VERS);
    
    wp_localize_script(
        'sra_admin_jquery',
        'gsa_ajax_object',
        [
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'gsa_secure'  => wp_create_nonce( 'gsa-security-nonce' ),
        ]
        );
}
add_action( 'admin_enqueue_scripts', 'load_gsa_admin_scripts' );
