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
define('DB_NAME', 'wp_np');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'SnX(+wWn~H`0[&((_i{p@QvL%cqrOO5]oO^#?_<FH6,*W?EYf3=Ps%+u2{I.f>hf');
define('SECURE_AUTH_KEY',  'ROwSTmy`N=;hZtL;%|GD yo3LJ?ytz15DjuauJejS.(B(/4%:OtETQ-fdEKj-SVx');
define('LOGGED_IN_KEY',    'W*c9)]kOcF<K*E<Nup>%U^t+B]Q}iu~I?e{Ks.~rzi+`#f@2,YAFL&/2WY]2Vys4');
define('NONCE_KEY',        'ez`&%NvlG:L:MPT%5Bb+$-_nTIie!m8vKd-J&kA{F^1Xby@p2Lv2;asFEZ&-]1p]');
define('AUTH_SALT',        'p^D3(az#yZ&lml Xl6U)m}LySo@~h]x]i)]ZZZ22j1#]U;5(^)#B?5B9%0obuqZh');
define('SECURE_AUTH_SALT', 'kh*c5_B&}Erp!<fntt>;&kNt=8|M t[CYRJAZ,QI$8%17@rY@9]vfb-sFrmGc+On');
define('LOGGED_IN_SALT',   '5s7U@m4>6e/#M|F>RdooK!aw,0|c^jhtD}i<qvh3{mi|-B%~RL.4Wm}UG)1z>SQH');
define('NONCE_SALT',       'e_*CxM~s6JdTS(=]L^UoH9]A2}8luh^ROsA1jr} *IP}I=mjpIDRiVWG6wwa=85a');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

