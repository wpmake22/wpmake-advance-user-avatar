<?php
/**
 * WPMakeUserAvatar CoreFunctions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @author   WPMake
 * @category Core
 * @package  WPMakeUserAvatar/Handler
 * @version  1.0.0
 */

 add_filter( 'get_avatar', 'wpmake_user_avatar_replace_gravatar_image', 99, 6 );

if ( ! function_exists( 'wpmake_user_avatar_replace_gravatar_image' ) ) {
	/**
	 * Custom function to override get_gavatar function.
	 *
	 * @param [type] $avatar Avatar of user.
	 * @param [type] $id_or_email ID or email of user.
	 * @param [type] $size Size of avatar.
	 * @param [type] $default Default avatar.
	 * @param [type] $alt Alt.
	 * @param array  $args Args.
	 */
	function wpmake_user_avatar_replace_gravatar_image( $avatar, $id_or_email, $size, $default, $alt, $args = array() ) {
		global $wp_filter;

		remove_all_filters( 'get_avatar' );

		add_filter( 'get_avatar', 'wpmake_user_avatar_replace_gravatar_image', 100, 6 );

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

		$profile_picture_url = wp_get_attachment_url( get_user_meta( $user->ID, 'wpmake_user_avatar_attachment_id', true ) );
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
