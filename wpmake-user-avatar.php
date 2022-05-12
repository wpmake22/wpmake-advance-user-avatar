<?php
/**
 * Plugin Name: User Avatar
 * Plugin URI: https://wpmake.com/wordpress-plugins/
 * Description: User avatar uploader and updater plugin for WordPress.
 * Version: 1.0.0
 * Author: WPMake
 * Author URI: https://wpmake.com
 * Text Domain: wpmake-user-avatar
 * Domain Path: /languages/
 *
 * Copyright: © 2022 WPMake.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WPMake_User_Avatar
 */

defined( 'ABSPATH' ) || exit;

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

use WPMake\WPMakeUserAvatar\UserAvatar;

if ( ! defined( 'WPMAKE_USER_AVATAR_VERSION' ) ) {
	define( 'WPMAKE_USER_AVATAR_VERSION', '1.0.0' );
}

// Define WPMAKE_USER_AVATAR_PLUGIN_FILE.
if ( ! defined( 'WPMAKE_USER_AVATAR_PLUGIN_FILE' ) ) {
	define( 'WPMAKE_USER_AVATAR_PLUGIN_FILE', __FILE__ );
}

// Define WPMAKE_USER_AVATAR_DIR.
if ( ! defined( 'WPMAKE_USER_AVATAR_DIR' ) ) {
	define( 'WPMAKE_USER_AVATAR_DIR', plugin_dir_path( __FILE__ ) );
}

// Define WPMAKE_USER_AVATAR_DS.
if ( ! defined( 'WPMAKE_USER_AVATAR_DS' ) ) {
	define( 'WPMAKE_USER_AVATAR_DS', DIRECTORY_SEPARATOR );
}

// Define WPMAKE_USER_AVATAR_URL.
if ( ! defined( 'WPMAKE_USER_AVATAR_URL' ) ) {
	define( 'WPMAKE_USER_AVATAR_URL', plugin_dir_url( __FILE__ ) );
}

// Define WPMAKE_USER_AVATAR_ASSETS_URL.
if ( ! defined( 'WPMAKE_USER_AVATAR_ASSETS_URL' ) ) {
	define( 'WPMAKE_USER_AVATAR_ASSETS_URL', WPMAKE_USER_AVATAR_URL . 'assets' );
}

// Define WPMAKE_USER_AVATAR_TEMPLATE_PATH.
if ( ! defined( ' WPMAKE_USER_AVATAR_TEMPLATE_PATH' ) ) {
	define( 'WPMAKE_USER_AVATAR_TEMPLATE_PATH', WPMAKE_USER_AVATAR_DIR . 'templates' );
}

/**
 * Initialization of UserAvatar instance.
 **/
function wpmake_user_avatar() {
	return UserAvatar::get_instance();
}

wpmake_user_avatar();
