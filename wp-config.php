<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wp_bamem');

/** MySQL database username */
define('DB_USER', 'wpadmin');

/** MySQL database password */
define('DB_PASSWORD', 'wpadmin');

/** MySQL hostname */
define('DB_HOST', 'localhost:/tmp/mysql.sock');

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
define('AUTH_KEY',         'ut^A>HMJ2qGx4}C?>`,tJ19pV,RX>r4IYu7<>;]lBYPU3>Nx*hudV4q,ltDOT_Cw');
define('SECURE_AUTH_KEY',  '(}xXHR3-D:~#wU7*K[!0^#-!6WZc{tZ ~1YBW>@F){+^{CfT1 V[7+wcsF(4Fo]X');
define('LOGGED_IN_KEY',    '#L+9A)7^-!z!nh?k-1?-a0s]xUICE1V]a,SnRDZ<CR;vy)3Gq,=w?VZ-z;{xuV-V');
define('NONCE_KEY',        'e7+_:XU{* !a,eKoV!uEMGd{LO4ASDnDPZQilv+X<L,-vzMPxBc3U)T;bdKwd13u');
define('AUTH_SALT',        'Ns7E/~|+>?G.W%ak<Qv%O&-H|-u9Y_;j]&Os1}_ /hB/|&G+!_4,IN&c@gFTyrf%');
define('SECURE_AUTH_SALT', '^~~|.v9cwuF6?@o%k-h;eQpvQL~(?C=5<:1q a%sZ*g+igV+*sXPQ7nQS9W+]Gi{');
define('LOGGED_IN_SALT',   '5j@$N]o+g:+I?yVW82nyMl:!@8wFo8q1h.Nm>e+1b nc{<T&1s21Y_?G?|2#;tM2');
define('NONCE_SALT',       'k%+?6_,o-XL.V?-_H90@k=#QOw.elQ&z/[KY?D8=eqv|xY{7#E#$qS65D-%@#gS>');
/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'bamem_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
