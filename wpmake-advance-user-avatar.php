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
 * @package WPMAKEAdvance_User_Avatar
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'aua_fs' ) ) {
	aua_fs()->set_basename( true, __FILE__ );
} else {
	// DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
	if ( ! function_exists( 'aua_fs' ) ) {
		// Create a helper function for easy SDK access.
		function aua_fs() {
			global $aua_fs;

			if ( ! isset( $aua_fs ) ) {
				// Include Freemius SDK.
				require_once __DIR__ . '/freemius/start.php';

				$aua_fs = fs_dynamic_init(
					array(
						'id'                  => '16812',
						'slug'                => 'wpmake-advance-user-avatar',
						'type'                => 'plugin',
						'public_key'          => 'pk_86bd3b7752efc2dcb5420eee4bf99',
						'is_premium'          => true,
						'premium_suffix'      => '(Pro)',
						// If your plugin is a serviceware, set this option to false.
						'has_premium_version' => true,
						'has_addons'          => false,
						'has_paid_plans'      => true,
						'menu'                => array(
							'slug'    => 'wpmake-advance-user-avatar',
							'support' => false,
							'parent'  => array(
								'slug' => 'users.php',
							),
						),
					)
				);
			}

			return $aua_fs;
		}

		// Init Freemius.
		aua_fs();
		// Signal that SDK was initiated.
		do_action( 'aua_fs_loaded' );
	}
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

use WPMake\WPMakeAdvanceUserAvatar\UserAvatar;

if ( ! defined( 'WPMAKE_ADVANCE_USER_AVATAR_VERSION' ) ) {
	define( 'WPMAKE_ADVANCE_USER_AVATAR_VERSION', '1.0.0' );
}

// Define WPMAKE_ADVANCE_USER_AVATAR_PLUGIN_FILE.
if ( ! defined( 'WPMAKE_ADVANCE_USER_AVATAR_PLUGIN_FILE' ) ) {
	define( 'WPMAKE_ADVANCE_USER_AVATAR_PLUGIN_FILE', __FILE__ );
}

// Define WPMAKE_ADVANCE_USER_AVATAR_DIR.
if ( ! defined( 'WPMAKE_ADVANCE_USER_AVATAR_DIR' ) ) {
	define( 'WPMAKE_ADVANCE_USER_AVATAR_DIR', plugin_dir_path( __FILE__ ) );
}

// Define WPMAKE_ADVANCE_USER_AVATAR_DS.
if ( ! defined( 'WPMAKE_ADVANCE_USER_AVATAR_DS' ) ) {
	define( 'WPMAKE_ADVANCE_USER_AVATAR_DS', DIRECTORY_SEPARATOR );
}

// Define WPMAKE_ADVANCE_USER_AVATAR_URL.
if ( ! defined( 'WPMAKE_ADVANCE_USER_AVATAR_URL' ) ) {
	define( 'WPMAKE_ADVANCE_USER_AVATAR_URL', plugin_dir_url( __FILE__ ) );
}

// Define WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL.
if ( ! defined( 'WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL' ) ) {
	define( 'WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL', WPMAKE_ADVANCE_USER_AVATAR_URL . 'assets' );
}

// Define WPMAKE_ADVANCE_USER_AVATAR_TEMPLATE_PATH.
if ( ! defined( ' WPMAKE_ADVANCE_USER_AVATAR_TEMPLATE_PATH' ) ) {
	define( 'WPMAKE_ADVANCE_USER_AVATAR_TEMPLATE_PATH', WPMAKE_ADVANCE_USER_AVATAR_DIR . 'templates' );
}

/**
 * Initialization of UserAvatar instance.
 **/
function wpmake_advance_user_avatar() {
	return UserAvatar::get_instance();
}

wpmake_advance_user_avatar();
