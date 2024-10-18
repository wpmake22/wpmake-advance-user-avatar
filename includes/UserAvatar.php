<?php
/**
 * WPMake_Advance_User_Avatar setup
 *
 * @package WPMake_Advance_User_Avatar
 * @since  1.0.0
 */

namespace WPMake\WPMakeAdvanceUserAvatar;

use WPMake\WPMakeAdvanceUserAvatar\Admin\Admin;
use WPMake\WPMakeAdvanceUserAvatar\Admin\Shortcodes;
use WPMake\WPMakeAdvanceUserAvatar\Frontend\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UserAvatar' ) ) :

	/**
	 * Main UserAvatar Class
	 *
	 * @class UserAvatar
	 */
	final class UserAvatar {


		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Plugin Version
		 *
		 * @var string
		 */
		const VERSION = WPMAKE_ADVANCE_USER_AVATAR_VERSION;

		/**
		 * Admin class instance
		 *
		 * @var \Admin
		 * @since 1.0.0
		 */
		public $admin = null;

		/**
		 * Frontend class instance
		 *
		 * @var \Frontend
		 * @since 1.0.0
		 */
		public $frontend = null;

		/**
		 * Ajax class instance
		 *
		 * @since 1.0.0
		 *
		 * @var use WPMake\WPMakeAdvanceUserAvatar\Ajax;
		 */
		public $ajax = null;

		/**
		 * Shortcodes.
		 *
		 * @since 1.0.0
		 *
		 * @var WPMake\WPMakeAdvanceUserAvatar\Admin\Shortcodes;
		 */
		public $shortcodes = null;

		/**
		 * Return an instance of this class
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			require 'Functions/CoreFunctions.php';

			// Actions and Filters.
			add_filter( 'plugin_action_links_' . plugin_basename( WPMAKE_ADVANCE_USER_AVATAR_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );
			add_action( 'init', array( $this, 'includes' ) );
		}

		/**
		 * Includes.
		 */
		public function includes() {
			$this->ajax       = new Ajax();
			$this->shortcodes = new Shortcodes();

			// Class admin.
			if ( $this->is_admin() ) {
				// require file.
				$this->admin = new Admin();
			} else {
				// require file.
				$this->frontend = new Frontend();
			}

			// Create a folder to store avatars if not present.
			$path = WP_CONTENT_DIR . '/uploads/wpmake_advance_user_avatar_uploads';

			if ( ! is_dir( $path ) ) {
				mkdir( $path, 0777, true );
			}
		}

		/**
		 * Check if is admin or not and load the correct class
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function is_admin() {
			$check_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX;
			$check_context = isset( $_REQUEST['context'] ) && $_REQUEST['context'] == 'frontend';

			return is_admin() && ! ( $check_ajax && $check_context );
		}

		/**
		 * Display action links in the Plugins list table.
		 *
		 * @param array $actions Add plugin action link.
		 *
		 * @return array
		 */
		public function plugin_action_links( $actions ) {
			$new_actions = array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=wpmake-advance-user-avatar' ) . '" title="' . esc_attr__( 'View User Avatar Settings', 'wpmake-advance-user-avatar' ) . '">' . esc_html__( 'Settings', 'wpmake-advance-user-avatar' ) . '</a>',
			);

			return array_merge( $new_actions, $actions );
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}
	}
endif;

/**
 * Main instance of UserAvatar.
 *
 * @since  1.0.0
 * @return UserAvatar
 */
function UserAvatar() {
	return UserAvatar::get_instance();
}
