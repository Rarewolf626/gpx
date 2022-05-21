<?php

global $gpr_sb_version;
$gpr_sb_version = GPR_SA_VERS;

function gpr_sb_database() {
    global $wpdb;
    global $gpr_sb_version;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$wpdb->prefix}gpr_smartbar_hide (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		user_ip varchar(55) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_option( 'gpr_smartbar_db_version', $gpr_sb_version );
}
register_activation_hook( GPR_SA_PLUGIN_DIR, 'gpr_sb_database' );
