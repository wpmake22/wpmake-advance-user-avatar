<?php
/**
 * WPMakeUserAvatar Admin.
 *
 * @class    Admin
 * @version  1.0.0
 * @package  WPMakeUserAvatar/Admin
 */

namespace WPMake\WPMakeUserAvatar\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Class
 */
class Admin {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'wpmake_user_avatar_menu' ), 68 );
	}

	/**
	 * Add  menu item.
	 */
	public function wpmake_user_avatar_menu() {
		$template_page = add_submenu_page(
			'users.php',
			__( 'WPMake Users Avatar', 'wpmake-user-avatar' ),
			__( 'Users Avatar', 'wpmake-user-avatar' ),
			'manage_user_registration',
			'wpmake-user-avatar',
			array(
				$this,
				'wpmake_user_avatar_settings_page',
			)
		);

	}


	/**
	 *  Init the User Avatar Settings page.
	 */
	public function wpmake_user_avatar_settings_page() {
		echo '<h1>Hello</h1>';
	}
}
