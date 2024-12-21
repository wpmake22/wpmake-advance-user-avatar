<?php
/**
 * WPMakeAdvanceUserAvatar Ajax
 *
 * Ajax Event Handler
 *
 * @class    Ajax
 * @version  1.0.0
 * @package  WPMakeAdvanceUserAvatar/Ajax
 */

namespace WPMake\WPMakeAdvanceUserAvatar;

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
			'method_upload'  => true,
			'remove_avatar'  => true,
			'rated'          => false,
			'dismiss_notice' => false,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {

			add_action( 'wp_ajax_wpmake_advance_user_avatar_upload_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {

				add_action(
					'wp_ajax_nopriv_wpmake_advance_user_avatar_upload_' . $ajax_event,
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
		check_ajax_referer( 'wpmake_advance_user_avatar_remove_nonce', 'security' );
		$nonce = isset( $_REQUEST['security'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ) : false;

		$flag = wp_verify_nonce( $nonce, 'wpmake_advance_user_avatar_remove_nonce' );

		if ( true != $flag || is_wp_error( $flag ) ) {

			wp_send_json_error(
				array(
					'message' => esc_html__( 'Nonce error, please reload.', 'wpmake-advance-user-avatar' ),
				)
			);
		}

		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'wpmake_advance_user_avatar_attachment_id', '' );

		wp_send_json_success(
			array(
				'message' => esc_html__( 'User avatar removed successfully', 'wpmake-advance-user-avatar' ),
			)
		);
	}

	/**
	 * User input dropped function.
	 */
	public static function method_upload() {

		check_ajax_referer( 'wpmake_advance_user_avatar_upload_nonce', 'security' );

		$nonce = isset( $_REQUEST['security'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ) : false;

		$flag = wp_verify_nonce( $nonce, 'wpmake_advance_user_avatar_upload_nonce' );

		if ( true != $flag || is_wp_error( $flag ) ) {

			wp_send_json_error(
				array(
					'message' => esc_html__( 'Nonce error, please reload.', 'wpmake-advance-user-avatar' ),
				)
			);
		}

		$upload = isset( $_FILES['file'] ) ? $_FILES['file'] : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// valid extension for image.
		$valid_extensions     = isset( $_REQUEST['valid_extension'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['valid_extension'] ) ) : array();
		$valid_extension_type = explode( ',', $valid_extensions );
		$valid_ext            = array();

		foreach ( $valid_extension_type as $key => $value ) {
			$image_extension   = explode( '/', $value );
			$valid_ext[ $key ] = $image_extension[1];
		}

		$src_file_name  = isset( $upload['name'] ) ? $upload['name'] : '';
		$file_extension = strtolower( pathinfo( $src_file_name, PATHINFO_EXTENSION ) );

		// Validates if the uploaded file has the acceptable extension.
		if ( ! in_array( $file_extension, $valid_ext ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Invalid file type, please contact with site administrator.', 'wpmake-advance-user-avatar' ),
				)
			);
		}

		$max_size = wp_max_upload_size();
		$max_size = size_format( $max_size );

		// Retrieves cropped picture dimensions from ajax request.
		$value                          = isset( $_REQUEST['cropped_image'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['cropped_image'] ) ) : '';
		$cropped_image_size             = json_decode( $value, true );
		$max_uploaded_size_option_value = isset( $_REQUEST['max_uploaded_size'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['max_uploaded_size'] ) ) : '';

		if ( isset( $max_uploaded_size_option_value ) && '' !== $max_uploaded_size_option_value ) {
			$max_upload_size_options_value = $max_uploaded_size_option_value * 1024;
		} else {
			$max_upload_size_options_value = $max_size;
		}

		if ( ! isset( $upload['size'] ) || ( isset( $upload['size'] ) && $upload['size'] < 1 ) ) {

			wp_send_json_error(
				array(
					/* translators: %s - Max Size */
					'message' => sprintf( esc_html__( 'Please upload a picture with size less than %s', 'wpmake-advance-user-avatar' ), size_format( $max_size ) ),
				)
			);
		} elseif ( $upload['size'] > $max_upload_size_options_value ) {
			wp_send_json_error(
				array(
					/* translators: %s - Max Size */
					'message' => sprintf( esc_html__( 'Please upload a picture with size less than %s', 'wpmake-advance-user-avatar' ), size_format( $max_upload_size_options_value ) ),
				)
			);
		}

		$upload_dir  = wp_upload_dir();
		$upload_path = apply_filters( 'wpmake_advance_user_avatar_upload_url', $upload_dir['basedir'] . '/wpmake-advance-user-avatar' ); /*Get path of upload dir of WordPress*/

		if ( ! is_writable( $upload_path ) ) {  /*Check if upload dir is writable*/ // phpcs:ignore

			wp_send_json_error(
				array(
					'message' => esc_html__( 'Upload path permission deny.', 'wpmake-advance-user-avatar' ),
				)
			);

		}

		$custom_subdir = '/wpmake-advance-user-avatar';
		$custom_path   = $upload_dir['basedir'] . $custom_subdir;
		$custom_url    = $upload_dir['baseurl'] . $custom_subdir;

		add_filter(
			'upload_dir',
			function ( $dirs ) use ( $custom_path, $custom_url, $custom_subdir ) {
				$dirs['path']   = $custom_path;
				$dirs['url']    = $custom_url;
				$dirs['subdir'] = $custom_subdir;
				return $dirs;
			}
		);

		$overrides = array(
			'test_form' => false,
		);

		$uploaded = wp_handle_upload( $upload, $overrides );
		remove_filter( 'upload_dir', '__return_true' );

		if ( $uploaded && ! isset( $uploaded['error'] ) ) {
			$file_url  = $uploaded['url'];
			$file_path = $uploaded['file'];
			$file_type = $uploaded['type'];

			$attachment_id = wp_insert_attachment(
				array(
					'guid'           => $file_url,
					'post_mime_type' => $file_type,
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_path ) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				),
				$file_path
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
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file_path ) );

			$url = wp_get_attachment_url( $attachment_id );

			$options = get_option( 'wpmake_advance_user_avatar_settings', array() );

			if ( isset( $options['cropping_interface'] ) && $options['cropping_interface'] ) {
				// Retrieves original picture height and width.
				list( $original_image_width, $original_image_height ) = getimagesize( $file_path );

				// Determines the type of uploaded picture and treats them differently.
				switch ( $upload['type'] ) {
					case 'image/png':
						$img_r = imagecreatefrompng( $file_path );
						break;
					case 'image/gif':
						$img_r = imagecreatefromgif( $file_path );
						break;
					default:
						$img_r = imagecreatefromjpeg( $file_path );
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
				list( $image_width, $image_height ) = apply_filters( 'wpmake_advance_user_avatar_cropped_image_size', array( 150, 150 ) );
				$dest_r                             = wp_imageCreateTrueColor( $image_width, $image_height );
				imagecopyresampled( $dest_r, $dst_r, 0, 0, 0, 0, $image_width, $image_height, $original_image_width, $original_image_height );

				// Replaces the original picture with the cropped picture.
				$img_r = imagejpeg( $dest_r, $file_path );
			}

			if ( empty( $url ) ) {
				$url = home_url() . '/wp-includes/images/media/text.png';
			}

			$user_id = get_current_user_id();
			update_user_meta( $user_id, 'wpmake_advance_user_avatar_attachment_id', $attachment_id );

			wp_send_json_success(
				array(
					'attachment_id'       => $attachment_id,
					'profile_picture_url' => $url,
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'File cannot be uploaded.', 'wpmake-advance-user-avatar' ),
				)
			);
		}
	}

	/**
	 * Triggered when clicking the rating footer.
	 *
	 * @since 1.0.2
	 */
	public static function rated() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( - 1 );
		}
		update_option( 'wpmake_advance_user_avatar_admin_footer_text_rated', 1 );
		wp_die();
	}

	/**
	 * Dismiss notices.
	 *
	 * @since 1.0.2
	 *
	 * @return void
	 **/
	public static function dismiss_notice() {
		check_admin_referer( 'notice_nonce', 'security' );
		if ( ! empty( $_POST['dismissed'] ) ) {
			update_option( 'wpmake_aua_review_notice_dismissed', true );
		}
		wp_die();
	}
}
