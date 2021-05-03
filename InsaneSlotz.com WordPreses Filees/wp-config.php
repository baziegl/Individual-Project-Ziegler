<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('WP_CACHE', true);
define( 'WPCACHEHOME', '/home/dh_8dd975/insaneslotz.com/wp-content/plugins/wp-super-cache/' );
define('DB_NAME', 'insaneslotz_com');

/** MySQL database username */
define('DB_USER', 'insaneslotzcom1');

/** MySQL database password */
define('DB_PASSWORD', 'ePxy?hfh');

/** MySQL hostname */
define('DB_HOST', 'mysql.insaneslotz.com');

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
define('AUTH_KEY',         '_SJ_*jboWYSFMKG;JQ*h002*sx;4Q1uo`&;Zbs3Vm|?;$Jy$j1gcrQQ_M$/Ruzf(');
define('SECURE_AUTH_KEY',  'A/^wo/`t0(L0*Cg^L_e79PBHxIuE470t$B(t^Zx0hodSdu0zJrY6+cEZYWR(4tjY');
define('LOGGED_IN_KEY',    'Ol#VkPiS++79&9zx$7_oP`&5R3hY`7qvpYQ+GP|yS~pcPJO)odVWtYaZ&BOFlpv%');
define('NONCE_KEY',        '#Pp_&EABZvppl"`1jD1fzgzfI_lA"#vG/CkfBE9tg5;^^u;hZ7o?|Vcj`q%6gE*&');
define('AUTH_SALT',        'n7&6z1_|;(yZy~e+Rdx@|^*^@e_b%9:!gNr(3hqJNu8kwP@km:2(`4&5hF$i3mZx');
define('SECURE_AUTH_SALT', '/x:ct)jD9o%CCtn~eS?xilzO`D7v%L6fX:2/m6VlorI"+~^~_wWd&HqZJNJw/*%w');
define('LOGGED_IN_SALT',   ':Pdw2;y3o10!c&RKwD~l;C_zMJfLh"%+6dbEzCN@vnZZPlj8^*_T/J3;X5:cDfEE');
define('NONCE_SALT',       '|Xr;~)j_|Hmy)5T*xC%1@X&26u~ku3@@9G(|6R6blPRRq8jLyhj44~eGcGmr7g!f');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_p8chzt_';

/**
 * Limits total Post Revisions saved per Post/Page.
 * Change or comment this line out if you would like to increase or remove the limit.
 */
define('WP_POST_REVISIONS',  10);

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/**
 * Removing this could cause issues with your experience in the DreamHost panel
 */

if (preg_match("/^(.*)\.dream\.website$/", $_SERVER['HTTP_HOST'])) {
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        define('WP_SITEURL', $proto . '://' . $_SERVER['HTTP_HOST']);
        define('WP_HOME',    $proto . '://' . $_SERVER['HTTP_HOST']);
}

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

