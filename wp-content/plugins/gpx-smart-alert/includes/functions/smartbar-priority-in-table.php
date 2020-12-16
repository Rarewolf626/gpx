<?php


function set_custom_gpr_sb_columns($columns) {
    unset($columns['date']);
    unset($columns['wpseo-links']);
    unset($columns['wpseo-score']);
    unset($columns['wpseo-score-readability']);
    
    $columns['priority'] = __( 'Priority', 'four8ightyeast' );
    $columns['start_date'] = __( 'Start Date', 'four8ightyeast' );
    $columns['end_date'] = __( 'End Date', 'four8ightyeast' );
    
    return $columns;
}
add_filter( 'manage_'.GPR_SA.'_posts_columns', 'set_custom_gpr_sb_columns' );

function custom_gpr_sb_column( $column, $post_id ) {
    switch ( $column ) {
        
        case 'priority' :
            echo get_post_meta( $post_id , 'gprsb-priority' , true );
            break;
        
        case 'start_date' :
            echo date('m/d/Y h:i a', strtotime(get_post_meta( $post_id , 'gprsb-start_date' , true )));
            break;
        
        case 'end_date' :
            echo date('m/d/Y h:i a', strtotime(get_post_meta( $post_id , 'gprsb-end_date' , true )));
            break;
            
    }
}
add_action( 'manage_'.GPR_SA.'_posts_custom_column' , 'custom_gpr_sb_column', 10, 2 );