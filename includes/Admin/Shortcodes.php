<?php
/**
 *  Shortcodes.
 *
 * @class    Shortcodes
 * @version  1.0.0
 * @package  WPMakeAdvanceUserAvatar/Classes
 */

namespace WPMake\WPMakeAdvanceUserAvatar\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcodes Class
 */
class Shortcodes {

	/**
	 * Init Shortcodes.
	 */
	public function __construct() {
		$shortcodes = array(
			'WPMake_Advance_User_Avatar'        => __CLASS__ . '::user_avatar',
			'WPMake_Advance_User_Avatar_upload' => __CLASS__ . '::user_avatar_upload',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * WPMake User Avatar shortcode.
	 *
	 * @param mixed $atts Attributes.
	 */
	public static function user_avatar_upload( $atts ) {

		ob_start();
		self::render_avatar_uploader();
		return ob_get_clean();
	}

	/**
	 * WPMake User Avatar shortcode.
	 *
	 * @param mixed $atts Attributes.
	 */
	public static function user_avatar( $atts ) {

		ob_start();
		self::render_avatar();
		return ob_get_clean();
	}

	/**
	 * Output for Avatar Uploader.
	 *
	 * @since 1.0.0
	 */
	public static function render_avatar_uploader() {
		if ( is_user_logged_in() ) {
			include WPMake_Advance_User_Avatar_TEMPLATE_PATH . '/wpmake-advance-user-avatar-upload-page.php';
		}
	}

	/**
	 * Output for Avatar.
	 *
	 * @since 1.0.0
	 */
	public static function render_avatar() {
		if ( is_user_logged_in() ) {
			include WPMake_Advance_User_Avatar_TEMPLATE_PATH . '/wpmake-advance-user-avatar-page.php';
		}
	}
}
