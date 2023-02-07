<?php


/**
 *
 *
 *
 *
 */
function gpx_geocode_all()
{

    require_once WP_CONTENT_DIR.'/plugins/wp-store-locator/admin/class-geocode.php';
    $geocode = new WPSL_Geocode();

    $args = array(
        'post_type' => 'wpsl_stores',
        'nopaging' => true,
        'meta_query'=> array(
            array(
                'key'=>'wpsl_lat',
                'value'=>null,
            )
        )
    );

    $loop = new WP_Query( $args );
    while ( $loop->have_posts() ) : $loop->the_post();
        $id = get_the_ID();

        $meta = get_post_meta($id);
        $dataarr = array(
            'address'=>'wpsl_address',
            'city'=>'wpsl_city',
            'state'=>'wpsl_state',
            'zip'=>'wpsl_zip',
            'country'=>'wpsl_country',
            'lat'=>'wpsl_lat',
            'lng'=>'wpsl_lng',
        );
        foreach($dataarr as $k=>$v)
        {

            if(isset($meta[$v]))
            {
                foreach($meta[$v] as $mv)
                {
                    $store_data[$k] = $mv;
                }
            }
        }

        $geocode->check_geocode_data($id, $store_data);
    endwhile;


    wp_send_json($data);
    wp_die();
}

add_action('wp_ajax_gpx_geocode_all', 'gpx_geocode_all');
add_action('wp_ajax_nopriv_gpx_geocode_all', 'gpx_geocode_all');
