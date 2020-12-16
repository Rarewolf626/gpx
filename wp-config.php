<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'gpx');

/** MySQL database username */
define('DB_USER', 'gpx');

/** MySQL database password */
define('DB_PASSWORD', 'gpx');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '$!I9tGpR/vW4dZ.)f+{QPqsKvfZK%#W0JhvT|2wD%V{!~,d?*{tN3Tq?*Bx50Vmj');
define('SECURE_AUTH_KEY',  'OK0-.REN zoh-PG9WODn#NG`j.aO-e+~t]d(,ie>VM*Nfnd=yjZHoP&L?+.vj*:^');
define('LOGGED_IN_KEY',    'dg75_Ne`ngA-t^g{]y/$IutHq_$`FZ9TncQ!U2Uc;p?gX0 k? %^1N{CBd/)_-g^');
define('NONCE_KEY',        'p!n[DuGY?(fMoZYp}TZQqZvGz.adP8/1v0X[kRw)>!crjZ-3r|F5>B|(b#tRV HM');
define('AUTH_SALT',        'lF4lS)lu,5eM(/Bt2xVrKoiG;v{T{f;U4;ue+b1h@j!?.xxB6mDOAR=|hQ|ds1*^');
define('SECURE_AUTH_SALT', 'ahmI(*=tZn+*NLtG+t8$$&+pv5+7*M&Rz_p)js@Owtb61p6=3UO0TBAD/kBE0uGY');
define('LOGGED_IN_SALT',   'pW*nKA8@-WO2|q0_+$a<zcpShG;}!<d;x-!DP^~Lsur.kbn)Z(+xU!?JCgrvi<yp');
define('NONCE_SALT',       '@OZqPW/AYEf$$.zoKR?-RsYLTQl]]WC|xh$0Ara4S8q$alJjJjEnd#S95A]FHlfS');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
define('SCRIPT_DEBUG', false);
define('SAVEQUERIES', false);
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
/*
ini_set('log_errors','On');
ini_set('display_errors','Off');
ini_set('error_reporting', E_ALL );
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', false);
*/
define('WP_DEBUG_DISPLAY', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

/** Bypass FTP */
define('FS_METHOD', 'direct');
@ini_set( 'max_input_vars' , 40000 );
@ini_set( 'upload_max_size' , '64M' );
@ini_set( 'post_max_size', '64M');
@ini_set( 'memory_limit', '2048M' );
