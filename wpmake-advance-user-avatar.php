<?php
/**
 * Plugin Name: Advance User Avatar
 * Plugin URI: https://wpmake.net/advance-user-avatar/
 * Description: User avatar uploader and updater plugin for WordPress.
 * Version: 1.0.0
 * Author: WPMake
 * Author URI: https://wpmake.net
 * Text Domain: wpmake-advance-user-avatar
 * Domain Path: /languages/
 *
 * Copyright: © 2022 WPMake.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WPMake_Advance_User_Avatar
 */

defined( 'ABSPATH' ) || exit;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

use WPMake\WPMakeAdvanceUserAvatar\UserAvatar;

if ( ! defined( 'WPMake_Advance_User_Avatar_VERSION' ) ) {
	define( 'WPMake_Advance_User_Avatar_VERSION', '1.0.0' );
}

// Define WPMake_Advance_User_Avatar_PLUGIN_FILE.
if ( ! defined( 'WPMake_Advance_User_Avatar_PLUGIN_FILE' ) ) {
	define( 'WPMake_Advance_User_Avatar_PLUGIN_FILE', __FILE__ );
}

// Define WPMake_Advance_User_Avatar_DIR.
if ( ! defined( 'WPMake_Advance_User_Avatar_DIR' ) ) {
	define( 'WPMake_Advance_User_Avatar_DIR', plugin_dir_path( __FILE__ ) );
}

// Define WPMake_Advance_User_Avatar_DS.
if ( ! defined( 'WPMake_Advance_User_Avatar_DS' ) ) {
	define( 'WPMake_Advance_User_Avatar_DS', DIRECTORY_SEPARATOR );
}

// Define WPMake_Advance_User_Avatar_URL.
if ( ! defined( 'WPMake_Advance_User_Avatar_URL' ) ) {
	define( 'WPMake_Advance_User_Avatar_URL', plugin_dir_url( __FILE__ ) );
}

// Define WPMake_Advance_User_Avatar_ASSETS_URL.
if ( ! defined( 'WPMake_Advance_User_Avatar_ASSETS_URL' ) ) {
	define( 'WPMake_Advance_User_Avatar_ASSETS_URL', WPMake_Advance_User_Avatar_URL . 'assets' );
}

// Define WPMake_Advance_User_Avatar_TEMPLATE_PATH.
if ( ! defined( ' WPMake_Advance_User_Avatar_TEMPLATE_PATH' ) ) {
	define( 'WPMake_Advance_User_Avatar_TEMPLATE_PATH', WPMake_Advance_User_Avatar_DIR . 'templates' );
}

/**
 * Initialization of UserAvatar instance.
 **/
function WPMake_Advance_User_Avatar() {
	return UserAvatar::get_instance();
}

WPMake_Advance_User_Avatar();
