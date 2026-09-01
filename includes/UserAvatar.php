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
		 * Gutenberg class instance
		 *
		 * @since 1.0.0
		 *
		 * @var use WPMake\WPMakeAdvanceUserAvatar\Gutenberg;
		 */
		public $gutenberg = null;

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
			$this->gutenberg = new Gutenberg();

			$installed_date = get_option( 'wpmake_aua_activated' );

			if ( empty( $installed_date ) ) {
				update_option( 'wpmake_aua_activated', current_time( 'Y-m-d' ) );
				update_option( 'wpmake_aua_updated_at', current_time( 'Y-m-d' ) );
			} else {
				update_option( 'wpmake_aua_updated_at', current_time( 'Y-m-d' ) );
			}
		}

		/**
		 * Includes.
		 */
		public function includes() {
			$this->ajax = new Ajax();

			$this->shortcodes = new Shortcodes();

			// Class admin.
			if ( $this->is_admin() ) {
				// require file.
				$this->admin = new Admin();
			} else {
				// require file.
				$this->frontend = new Frontend();
			}
		}

		/**
		 * Absolute path of the directory avatars are stored in.
		 *
		 * @since 1.3.0
		 *
		 * @return string
		 */
		public static function get_upload_path() {
			return wp_upload_dir()['basedir'] . '/wpmake-advance-user-avatar';
		}

		/**
		 * Runs on activation.
		 *
		 * Creating this directory used to happen inside includes(), which meant a
		 * stat call and a possible mkdir() on every single request. It belongs here,
		 * and the upload handler calls wp_mkdir_p() itself to cover sites that
		 * upgrade without ever re-activating.
		 *
		 * @since 1.3.0
		 *
		 * @param bool $network_wide Whether the plugin is being activated for a whole
		 *                           network. WordPress passes this to the hook.
		 * @return void
		 */
		public static function activate( $network_wide = false ) {
			/*
			 * Uploads are per-site, so a network activation has to create the directory on
			 * every site -- not just whichever one the activation happened from.
			 */
			if ( $network_wide && is_multisite() ) {
				$blog_ids = get_sites(
					array(
						'fields' => 'ids',
						'number' => 0,
					)
				);

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::create_upload_directory();
					restore_current_blog();
				}

				return;
			}

			self::create_upload_directory();
		}

		/**
		 * Create the avatar directory for a site added after activation.
		 *
		 * @since 1.4.0
		 *
		 * @param \WP_Site $new_site Site that was just created.
		 * @return void
		 */
		public static function initialize_site( $new_site ) {
			switch_to_blog( (int) $new_site->blog_id );
			self::create_upload_directory();
			restore_current_blog();
		}

		/**
		 * Create the avatar directory for the current site.
		 *
		 * @since 1.4.0
		 *
		 * @return void
		 */
		private static function create_upload_directory() {
			$path = self::get_upload_path();

			// wp_mkdir_p() applies the filesystem's own permissions rather than a blanket 0777.
			wp_mkdir_p( $path );

			// Keep the directory from being listed on servers with no index rule of their own.
			$index = trailingslashit( $path ) . 'index.php';

			if ( ! file_exists( $index ) ) {
				file_put_contents( $index, "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
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
			$check_context = isset( $_REQUEST['context'] ) && 'frontend' === $_REQUEST['context']; // phpcs:ignore

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
