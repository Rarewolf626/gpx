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
if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'gpxvacations.com') {
    
    define('DB_NAME', 'gpx');
    
    /** MySQL database username */
    define('DB_USER', 'gpx');
    
    /** MySQL database password */
    define('DB_PASSWORD', 'B8d7xk3D421');
    
    /** MySQL hostname */
    define('DB_HOST', '192.168.161.34');
    
    /** Database Charset to use in creating database tables. */
    define('DB_CHARSET', 'utf8');
    
    /** The Database Collate type. Don't change this if in doubt. */
    define('DB_COLLATE', '');
}
else
{
    
    define('DB_NAME', 'gpx');
    
    /** MySQL database username */
    define('DB_USER', 'gpx');
    
    /** MySQL database password */
    define('DB_PASSWORD', 'B8d7xk3D421');
    
    /** MySQL hostname */
    define('DB_HOST', 'localhost');
    
    /** Database Charset to use in creating database tables. */
    define('DB_CHARSET', 'utf8');
    
    /** The Database Collate type. Don't change this if in doubt. */
    define('DB_COLLATE', '');
}

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '-kz/SG|ZkPE.VB3u>E/{h7Cnt;awkY[_D,CMBC9^.K|>&_Qtt)ReIlPz0y9zb_@1');
define('SECURE_AUTH_KEY',  'lFnCNGt+uM(4J&:>)S.G%1Ub~=VO,JnU_M5pE!NM~R.b-YpOOK[0.9c|}rYoSP@u');
define('LOGGED_IN_KEY',    'tli8,6RE!$#zKPqY=v=zwN:dl,zkG+|32`f/|Cw&,Wq_Cyw<Y Nu?rZj:(k|r%?#');
define('NONCE_KEY',        '(}T:I|W}3Nz.^4|#t%6H?qC|a4$M>7Sl&XL2c~-fR^zUmZ+Fc%vK%$Wj}+*zc-AK');
define('AUTH_SALT',        '.y4`Jo)Y.EI^l&dmOmveRM6sJv<z;gAF/B8!{w{#jb)Y%/:xe8>$Z)f9eu~%5Uj$');
define('SECURE_AUTH_SALT', 'S06OS23?~H>dFCU<|X4Zl{e!`HoA4~M99im[~sEB;s]]N.-$+Cg!Y{:;idc(#hPJ');
define('LOGGED_IN_SALT',   'MvqKhcl9{_/H9da}Mfxo{+1W%:/?E]L:3[^9>75(y<]+Pi7|G^xC8C .cn(?vh7B');
define('NONCE_SALT',       'uMN;T~Quqp.!/YIB&O2ZF8~vg2q@RQjBWt>sO3@bsy8[5Dz#J>6O~ceRtW4%0=9$');

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
