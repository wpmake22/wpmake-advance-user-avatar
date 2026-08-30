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
			'wpmake_advance_user_avatar'        => __CLASS__ . '::user_avatar',
			'wpmake_advance_user_avatar_upload' => __CLASS__ . '::user_avatar_upload',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "wpmake_advance_user_avatar_{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * Attributes accepted by [wpmake_advance_user_avatar].
	 *
	 * @since 1.3.0
	 *
	 * @return array
	 */
	public static function default_avatar_atts() {
		return array(
			// Avatar width/height in px. Empty leaves the stylesheet default (96px).
			'size'   => '',
			// Corner radius, e.g. 50% for a circle. Empty leaves the stylesheet default.
			'radius' => '',
			// Extra class names added to the container, for theme-side styling.
			'class'  => '',
		);
	}

	/**
	 * Attributes accepted by [wpmake_advance_user_avatar_upload].
	 *
	 * @since 1.3.0
	 *
	 * @return array
	 */
	public static function default_uploader_atts() {
		return array_merge(
			self::default_avatar_atts(),
			array(
				// Button labels. Empty falls back to the translated defaults.
				'upload_text'  => '',
				'remove_text'  => '',
				'capture_text' => '',
			)
		);
	}

	/**
	 * WPMake User Avatar shortcode.
	 *
	 * @param mixed $atts Attributes.
	 */
	public static function user_avatar_upload( $atts ) {
		$atts = self::parse_atts( $atts, self::default_uploader_atts(), 'wpmake_advance_user_avatar_upload' );

		ob_start();
		self::render_avatar_uploader( $atts );
		return ob_get_clean();
	}

	/**
	 * WPMake User Avatar shortcode.
	 *
	 * @param mixed $atts Attributes.
	 */
	public static function user_avatar( $atts ) {
		$atts = self::parse_atts( $atts, self::default_avatar_atts(), 'wpmake_advance_user_avatar' );

		ob_start();
		self::render_avatar( $atts );
		return ob_get_clean();
	}

	/**
	 * Normalise shortcode attributes against their defaults.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed  $atts     Raw attributes as passed by the shortcode API.
	 * @param array  $defaults Known attributes and their default values.
	 * @param string $tag      Shortcode tag, for the filter below.
	 * @return array
	 */
	protected static function parse_atts( $atts, $defaults, $tag ) {
		$atts = shortcode_atts( $defaults, (array) $atts, $tag );

		/**
		 * Filter the parsed shortcode attributes.
		 *
		 * @since 1.3.0
		 *
		 * @param array  $atts Parsed attributes.
		 * @param string $tag  Shortcode tag.
		 */
		return apply_filters( 'wpmake_advance_user_avatar_shortcode_atts', $atts, $tag );
	}

	/**
	 * Output for Avatar Uploader.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Attributes made available to the template as $wpmake_aua_atts.
	 */
	public static function render_avatar_uploader( $atts = array() ) {
		if ( is_user_logged_in() ) {
			// Covers widgets, page builders and template parts that the
			// wp_enqueue_scripts gate in Frontend.php cannot see.
			wpmake_aua_enqueue_frontend_assets();

			$wpmake_aua_atts = wp_parse_args( (array) $atts, self::default_uploader_atts() );

			include wpmake_advance_user_avatar_locate_template( 'wpmake-advance-user-avatar-upload-page.php' );
		}
	}

	/**
	 * Output for Avatar.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Attributes made available to the template as $wpmake_aua_atts.
	 */
	public static function render_avatar( $atts = array() ) {
		if ( is_user_logged_in() ) {
			// Covers widgets, page builders and template parts that the
			// wp_enqueue_scripts gate in Frontend.php cannot see.
			wpmake_aua_enqueue_frontend_assets();

			$wpmake_aua_atts = wp_parse_args( (array) $atts, self::default_avatar_atts() );

			include wpmake_advance_user_avatar_locate_template( 'wpmake-advance-user-avatar-page.php' );
		}
	}

	/**
	 * Build the inline custom-property style for a container from its attributes.
	 *
	 * The stylesheet reads every size/shape value from custom properties, so a
	 * per-shortcode override is just a matter of redeclaring them on the container.
	 *
	 * @since 1.3.0
	 *
	 * @param array $atts Parsed attributes.
	 * @return string Style attribute value, empty when nothing was set.
	 */
	public static function container_style( $atts ) {
		$style = '';

		if ( isset( $atts['size'] ) && '' !== $atts['size'] ) {
			$style .= '--wpmake-aua-avatar-size:' . absint( $atts['size'] ) . 'px;';
		}

		// A length or percentage, optionally up to four of them. Anything else is
		// dropped rather than half-scrubbed into an unusable value.
		if ( isset( $atts['radius'] ) && '' !== $atts['radius']
			&& preg_match( '/^(?:\d+(?:\.\d+)?(?:px|em|rem|%)?)(?:\s+\d+(?:\.\d+)?(?:px|em|rem|%)?){0,3}$/', trim( $atts['radius'] ) )
		) {
			$style .= '--wpmake-aua-avatar-radius:' . trim( $atts['radius'] ) . ';';
		}

		return $style;
	}

	/**
	 * Build the container class list from the shortcode attributes.
	 *
	 * @since 1.3.0
	 *
	 * @param array $atts Parsed attributes.
	 * @return string
	 */
	public static function container_class( $atts ) {
		$classes = array( 'wpmake-advance-user-avatar-container' );

		if ( ! empty( $atts['class'] ) ) {
			$classes = array_merge( $classes, explode( ' ', $atts['class'] ) );
		}

		$classes = array_filter( array_map( 'sanitize_html_class', $classes ) );

		return implode( ' ', $classes );
	}
}
