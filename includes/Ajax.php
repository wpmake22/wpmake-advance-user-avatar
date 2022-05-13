<?php
/**
 * WPMakeUserAvatar Ajax
 *
 * Ajax Event Handler
 *
 * @class    Ajax
 * @version  1.0.0
 * @package  WPMakeUserAvatar/Ajax
 */

namespace  WPMake\WPMakeUserAvatar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajax Class
 */
class Ajax {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax)
	 */
	public static function add_ajax_events() {

		$ajax_events = array(
			'method_upload' => true,
			'remove_avatar' => true,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {

			add_action( 'wp_ajax_wpmake_user_avatar_upload_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {

				add_action(
					'wp_ajax_nopriv_wpmake_user_avatar_upload_' . $ajax_event,
					array(
						__CLASS__,
						$ajax_event,
					)
				);
			}
		}
	}

	/**
	 * User avatar remove function.
	 */
	public static function remove_avatar() {
		check_ajax_referer( 'wpmake_user_avatar_remove_nonce', 'security' );
		$nonce = isset( $_REQUEST['security'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ) : false;

		$flag = wp_verify_nonce( $nonce, 'wpmake_user_avatar_remove_nonce' );

		if ( true != $flag || is_wp_error( $flag ) ) {

			wp_send_json_error(
				array(
					'message' => __( 'Nonce error, please reload.', 'wpmake-user-avatar' ),
				)
			);
		}

		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'wpmake_user_avatar_attachment_id', '' );

		wp_send_json_success(
			array(
				'message' => __( 'User avatar removed successfully', 'wpmake-user-avatar' ),
			)
		);

	}

	/**
	 * User input dropped function.
	 */
	public static function method_upload() {

		check_ajax_referer( 'wpmake_user_avatar_upload_nonce', 'security' );

		$nonce = isset( $_REQUEST['security'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ) : false;

		$flag = wp_verify_nonce( $nonce, 'wpmake_user_avatar_upload_nonce' );

		if ( true != $flag || is_wp_error( $flag ) ) {

			wp_send_json_error(
				array(
					'message' => __( 'Nonce error, please reload.', 'wpmake-user-avatar' ),
				)
			);
		}

		$upload = isset( $_FILES['file'] ) ? $_FILES['file'] : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$src_file_name  = isset( $upload['name'] ) ? $upload['name'] : '';
		$file_extension = strtolower( pathinfo( $src_file_name, PATHINFO_EXTENSION ) );

		$max_size = wp_max_upload_size();

		// Retrieves cropped picture dimensions from ajax request.
		$value                          = $_REQUEST['cropped_image'];
		$cropped_image_size             = json_decode( stripslashes( $value ), true );
		$max_uploaded_size_option_value = $_REQUEST['max_uploaded_size'];

		if ( isset( $max_uploaded_size_option_value ) && '' !== $max_uploaded_size_option_value ) {
			$max_upload_size_options_value = $max_uploaded_size_option_value * 1024;
		} else {
			$max_upload_size_options_value = $max_size;
		}

		if ( ! isset( $upload['size'] ) || ( isset( $upload['size'] ) && $upload['size'] < 1 ) ) {

			wp_send_json_error(
				array(
					/* translators: %s - Max Size */
					'message' => sprintf( __( 'Please upload a picture with size less than %s', 'wpmake-user-avatar' ), size_format( $max_size ) ),
				)
			);
		} else if ( $upload['size'] > $max_upload_size_options_value ) {
			wp_send_json_error(
				array(
					/* translators: %s - Max Size */
					'message' => sprintf( __( 'Please upload a picture with size less than %s', 'wpmake-user-avatar' ), size_format( $max_upload_size_options_value ) ),
				)
			);
		}

		$upload_dir  = wp_upload_dir();
		$upload_path = apply_filters( 'wpmake_user_avatar_upload_url', $upload_dir['basedir'] . '/wpmake_user_avatar_uploads' ); /*Get path of upload dir of WordPress*/

		if ( ! is_writable( $upload_path ) ) {  /*Check if upload dir is writable*/

			wp_send_json_error(
				array(
					'message' => __( 'Upload path permission deny.', 'wpmake-user-avatar' ),
				)
			);

		}

		$pic_path = $upload_path . '/' . sanitize_file_name( $upload['name'] );

		if ( move_uploaded_file( $upload['tmp_name'], $pic_path ) ) {

			$attachment_id = wp_insert_attachment(
				array(
					'guid'           => $pic_path,
					'post_mime_type' => $file_extension,
					'post_title'     => preg_replace( '/\.[^.]+$/', '', sanitize_file_name( $upload['name'] ) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				),
				$pic_path
			);

			if ( is_wp_error( $attachment_id ) ) {

				wp_send_json_error(
					array(

						'message' => $attachment_id->get_error_message(),
					)
				);
			}

			include_once ABSPATH . 'wp-admin/includes/image.php';

			// Generate and save the attachment metas into the database.
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $pic_path ) );

			$url = wp_get_attachment_url( $attachment_id );

			// Retrieves original picture height and width.
			list( $original_image_width, $original_image_height ) = getimagesize( $pic_path );

			// Determines the type of uploaded picture and treats them differently.
			switch ( $upload['type'] ) {
				case 'image/png':
					$img_r = imagecreatefrompng( $pic_path );
					break;
				case 'image/gif':
					$img_r = imagecreatefromgif( $pic_path );
					break;
				default:
					$img_r = imagecreatefromjpeg( $pic_path );
			}

			$cropped_image_holder_width  = rtrim( $cropped_image_size['holder_width'], 'px' );
			$cropped_image_holder_height = rtrim( $cropped_image_size['holder_height'], 'px' );

			// Calculates the actual portion of original picture where the cropping is applied.
			$cropped_image_width  = absint( $cropped_image_size['w'] * $original_image_width / $cropped_image_holder_width );
			$cropped_image_left   = absint( $cropped_image_size['x'] * $original_image_width / $cropped_image_holder_width );
			$cropped_image_height = absint( $cropped_image_size['h'] * $original_image_height / $cropped_image_holder_height );
			$cropped_image_right  = absint( $cropped_image_size['y'] * $original_image_height / $cropped_image_holder_height );

			// Creates a frame of original height and width and copies the cropped picture portion to the frame.
			$dst_r = wp_imageCreateTrueColor( $original_image_width, $original_image_height );
			imagecopyresampled( $dst_r, $img_r, 0, 0, $cropped_image_left, $cropped_image_right, $original_image_width, $original_image_height, $cropped_image_width, $cropped_image_height );

			// Retrieves and Resizes the cropped picture to a size defined by user in filter or default of 150 by 150.
			list( $image_width, $image_height ) = apply_filters( 'user_registration_cropped_image_size', array( 150, 150 ) );
			$dest_r = wp_imageCreateTrueColor( $image_width, $image_height );
			imagecopyresampled( $dest_r, $dst_r, 0, 0, 0, 0, $image_width, $image_height, $original_image_width, $original_image_height );

			// Replaces the original picture with the cropped picture.
			$img_r = imagejpeg( $dest_r, $pic_path );

			if ( empty( $url ) ) {
				$url = home_url() . '/wp-includes/images/media/text.png';
			}

			$user_id = get_current_user_id();
			update_user_meta( $user_id, 'wpmake_user_avatar_attachment_id', $attachment_id );

			wp_send_json_success(
				array(
					'attachment_id'       => $attachment_id,
					'profile_picture_url' => $url,
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'File cannot be uploaded.', 'wpmake-user-avatar' ),
				)
			);
		}

	}

}
