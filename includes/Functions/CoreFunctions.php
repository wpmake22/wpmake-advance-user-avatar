<?php
/**
 * WPMakeAdvanceUserAvatar CoreFunctions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @author   WPMake
 * @category Core
 * @package  WPMakeAdvanceUserAvatar/Handler
 * @version  1.0.0
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'get_avatar', 'wpmake_advance_user_avatar_replace_gravatar_image', 99, 6 );

if ( ! function_exists( 'wpmake_advance_user_avatar_replace_gravatar_image' ) ) {
	/**
	 * Custom function to override get_gavatar function.
	 *
	 * @param string $avatar Avatar of user.
	 * @param string $id_or_email ID or email of user.
	 * @param string $size Size of avatar.
	 * @param string $default Default avatar.
	 * @param string $alt Alt.
	 * @param array  $args Args.
	 */
	function wpmake_advance_user_avatar_replace_gravatar_image( $avatar, $id_or_email, $size, $default, $alt, $args = array() ) {
		remove_all_filters( 'get_avatar' );

		add_filter( 'get_avatar', 'wpmake_advance_user_avatar_replace_gravatar_image', 100, 6 );

		// Process the user identifier.
		$user = false;
		if ( is_numeric( $id_or_email ) ) {
			$user = get_user_by( 'id', absint( $id_or_email ) );
		} elseif ( is_string( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );
		} elseif ( $id_or_email instanceof WP_User ) {
			// User Object.
			$user = $id_or_email;
		} elseif ( $id_or_email instanceof WP_Post ) {
			// Post Object.
			$user = get_user_by( 'id', (int) $id_or_email->post_author );
		} elseif ( $id_or_email instanceof WP_Comment ) {

			if ( ! empty( $id_or_email->user_id ) ) {
				$user = get_user_by( 'id', (int) $id_or_email->user_id );
			}
		}

		if ( ! $user || is_wp_error( $user ) ) {
			return $avatar;
		}

		$profile_picture_url = wp_get_attachment_url( get_user_meta( $user->ID, 'wpmake_advance_user_avatar_attachment_id', true ) );
		$class               = array( 'avatar', 'avatar-' . (int) $args['size'], 'photo' );

		if ( ( isset( $args['found_avatar'] ) && ! $args['found_avatar'] ) || ( isset( $args['force_default'] ) && $args['force_default'] ) ) {
			$class[] = 'avatar-default';
		}

		if ( $args['class'] ) {
			if ( is_array( $args['class'] ) ) {
				$class = array_merge( $class, $args['class'] );
			} else {
				$class[] = $args['class'];
			}
		}

		if ( $profile_picture_url ) {
			$avatar = sprintf(
				"<img alt='%s' src='%s' srcset='%s' class='%s' height='%d' width='%d' %s/>",
				esc_attr( $args['alt'] ),
				esc_url( $profile_picture_url ),
				esc_url( $profile_picture_url ) . ' 2x',
				esc_attr( join( ' ', $class ) ),
				(int) $args['height'],
				(int) $args['width'],
				$args['extra_attr']
			);
		}

		return $avatar;
	}
}

if ( ! function_exists( 'wpmake_aua_get_allowed_html_tags' ) ) {
	/**
	 * WPMAKE AUA KSES.
	 *
	 * @since 1.0.0
	 */
	function wpmake_aua_get_allowed_html_tags() {

		$post_tags = wp_kses_allowed_html( 'post' );

		return wp_parse_args(
			$post_tags,
			array(
				'input'    => array(
					'type'        => true,
					'name'        => true,
					'value'       => true,
					'checked'     => true,
					'class'       => true,
					'placeholder' => true,
				),
				'select'   => array(
					'name'     => true,
					'id'       => true,
					'class'    => true,
					'multiple' => true,
				),
				'option'   => array(
					'value'    => true,
					'selected' => true,
					'class'    => true,
				),
				'textarea' => array(
					'style' => true,
				),
				'label'    => array(
					'for' => array(),
				),
				'p'        => array(
					'class' => true,
				),
			)
		);
	}
}

/**
 * Print js script by properly sanitizing and escaping.
 *
 * @since 1.0.2
 * Output any queued javascript code in the footer.
 */
function wpmake_aua_print_js() {
	global $wpmake_aua_queued_js;

	if ( ! empty( $wpmake_aua_queued_js ) ) {
		// Sanitize.
		$wpmake_aua_queued_js = wp_check_invalid_utf8( $wpmake_aua_queued_js );
		$wpmake_aua_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $wpmake_aua_queued_js );
		$wpmake_aua_queued_js = str_replace( "\r", '', $wpmake_aua_queued_js );

		$js = "<!-- WPMake AUA JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $wpmake_aua_queued_js });\n</script>\n";

		echo wp_kses( apply_filters( 'wpmake_aua_queued_js', $js ), array( 'script' => array( 'type' => true ) ) );

		unset( $wpmake_aua_queued_js );
	}
}

/**
 * Enqueue WPMake AUA js.
 *
 * @since 1.0.2
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code Code to enqueue.
 */
function wpmake_aua_enqueue_js( $code ) {
	global $wpmake_aua_queued_js;

	if ( empty( $wpmake_aua_queued_js ) ) {
		$wpmake_aua_queued_js = '';
	}

	$wpmake_aua_queued_js .= "\n" . $code . "\n";
}


if ( ! function_exists( 'review_notice_content' ) ) {

	/**
	 * Review Content.
	 *
	 * @return bool
	 */
	function review_notice_content() {
		return wp_kses_post(
			sprintf(
				"<p>%s</p><p class='extra-pad'>%s</p>",
				__( 'We hope you’re enjoying a great experience with the <strong>Advance User Avatar</strong> plugin! We kindly request you to consider leaving a positive review for the plugin.', 'wpmake-advance-user-avatar' ),
				__(
					'Your review motivates us to continue providing regular updates with new features and bug fixes, ensuring the plugin works seamlessly for you. It also supports us in offering free assistance, just as we always have. <span class="dashicons dashicons-smiley smile-icon"></span><br>',
					'wpmake-advance-user-avatar'
				)
			)
		);
	}
}

if ( ! function_exists( 'wpmake_aua_check_activation_date' ) ) {

	/**
	 * Check for plugin activation date.
	 *
	 * @param int $days Number of days to check for activation.
	 *
	 * @since 1.0.2
	 *
	 * @return bool
	 */
	function wpmake_aua_check_activation_date( $days ) {

		// Plugin Activation Time.
		$activation_date  = get_option( 'wpmake_aua_activated' );

		$days_to_validate = strtotime( 'now' ) - $days * DAY_IN_SECONDS;
		$days_to_validate = date_i18n( 'Y-m-d', $days_to_validate );

		if ( ! empty( $activation_date ) ) {
			if ( $activation_date <= $days_to_validate ) {
				return true;
			}
		}

		return false;
	}
}
