<?php
add_action( 'admin_init', 'wpsl_csv_check_upgrade' );

/**
 * If the db doesn't hold the current version, run the upgrade procedure
 *
 * @since 1.1.0
 * @return void
 */
function wpsl_csv_check_upgrade() {

    $current_version = get_option( 'wpsl_csv_version' );

    if ( version_compare( $current_version, WPSL_CSV_VERSION_NUM, '===' ) )
        return;

    if ( version_compare( $current_version, '1.1.0', '<' ) ) {
        require_once( WPSL_CSV_PLUGIN_DIR . 'admin/roles.php' );

        wpsl_csv_create_roles();
    }

    update_option( 'wpsl_csv_version', WPSL_CSV_VERSION_NUM );
}