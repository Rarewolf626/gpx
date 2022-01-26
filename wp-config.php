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

if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'wp-config.local.php')) {
    require __DIR__ . DIRECTORY_SEPARATOR . 'wp-config.local.php';
}

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'gpxvacations.com') {
    if(!defined('DB_HOST')) define('DB_HOST', '192.168.161.34');
} else {
    if(!defined('DB_HOST')) define('DB_HOST', 'localhost');
}
if(!defined('DB_NAME')) define('DB_NAME', 'gpx');
if(!defined('DB_USER')) define('DB_USER', 'gpx');
if(!defined('DB_PASSWORD')) define('DB_PASSWORD', 'B8d7xk3D421');
if(!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8');
if(!defined('DB_COLLATE')) define('DB_COLLATE', '');

if(!defined('GPX_RECAPTCHA_V3_SECRET_KEY')) define("GPX_RECAPTCHA_V3_SECRET_KEY", '6LfzhPIdAAAAAJSGo240JqLPJKXdVU5vjrii0Wqm');
if(!defined('GPX_RECAPTCHA_V3_SITE_KEY')) define("GPX_RECAPTCHA_V3_SITE_KEY", '6LfzhPIdAAAAALbGtjuaU7IX8xfD-dNxvGS0vjQM');

if(!defined('SHIFT4_URL')) define('SHIFT4_URL', 'https://utg.shift4api.net/');
if(!defined('I4GO_URL')) define('I4GO_URL', 'https://access.i4go.com/');
if(!defined('SHIFT4_AUTH_TOKEN')) define('SHIFT4_AUTH_TOKEN', '0C5AAB46-AA53-AB36-6053785657A00AF0');
if(!defined('SHIFT4_CLIENT_GUID')) define('SHIFT4_CLIENT_GUID', '38471D57-EEF4-FA74-43E8BE7F13B82F38');
if(!defined('SHIFT4_ACCESS_TOKEN')) define('SHIFT4_ACCESS_TOKEN', 'BF1CFECB-9B28-4705-8CEC-E14F08E7962B');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
if(!defined('AUTH_KEY')) define('AUTH_KEY', '-kz/SG|ZkPE.VB3u>E/{h7Cnt;awkY[_D,CMBC9^.K|>&_Qtt)ReIlPz0y9zb_@1');
if(!defined('SECURE_AUTH_KEY')) define('SECURE_AUTH_KEY', 'lFnCNGt+uM(4J&:>)S.G%1Ub~=VO,JnU_M5pE!NM~R.b-YpOOK[0.9c|}rYoSP@u');
if(!defined('LOGGED_IN_KEY')) define('LOGGED_IN_KEY', 'tli8,6RE!$#zKPqY=v=zwN:dl,zkG+|32`f/|Cw&,Wq_Cyw<Y Nu?rZj:(k|r%?#');
if(!defined('NONCE_KEY')) define('NONCE_KEY', '(}T:I|W}3Nz.^4|#t%6H?qC|a4$M>7Sl&XL2c~-fR^zUmZ+Fc%vK%$Wj}+*zc-AK');
if(!defined('AUTH_SALT')) define('AUTH_SALT', '.y4`Jo)Y.EI^l&dmOmveRM6sJv<z;gAF/B8!{w{#jb)Y%/:xe8>$Z)f9eu~%5Uj$');
if(!defined('SECURE_AUTH_SALT')) define('SECURE_AUTH_SALT', 'S06OS23?~H>dFCU<|X4Zl{e!`HoA4~M99im[~sEB;s]]N.-$+Cg!Y{:;idc(#hPJ');
if(!defined('LOGGED_IN_SALT')) define('LOGGED_IN_SALT', 'MvqKhcl9{_/H9da}Mfxo{+1W%:/?E]L:3[^9>75(y<]+Pi7|G^xC8C .cn(?vh7B');
if(!defined('NONCE_SALT')) define('NONCE_SALT', 'uMN;T~Quqp.!/YIB&O2ZF8~vg2q@RQjBWt>sO3@bsy8[5Dz#J>6O~ceRtW4%0=9$');

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