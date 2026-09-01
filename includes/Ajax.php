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
		/*
		 * Every handler here needs a logged-in user, so none of them are exposed to
		 * wp_ajax_nopriv_. Upload and remove used to be, which meant an anonymous
		 * request reached wp_handle_upload() and wp_insert_attachment() before the
		 * handler ever looked at who was asking.
		 */
		$ajax_events = array(
			'method_upload'  => false,
			'set_avatar'     => false,
			'remove_avatar'  => false,
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
	 * The avatar mime types the site owner has allowed.
	 *
	 * Read from the saved settings only. The uploader used to post this list along
	 * with the file and the handler trusted it, so anything able to edit the request
	 * could accept types the site owner had switched off.
	 *
	 * @since 1.3.0
	 *
	 * @return array List of mime types.
	 */
	private static function get_allowed_mimes(): array {
		return wpmake_aua_get_allowed_mimes();
	}

	/**
	 * The allowed types in the extension => mime shape wp_handle_upload() expects.
	 *
	 * @since 1.3.0
	 *
	 * @return array
	 */
	private static function get_allowed_mime_overrides(): array {
		$extensions = array(
			'image/jpeg' => 'jpg|jpeg|jpe',
			'image/png'  => 'png',
			'image/gif'  => 'gif',
			'image/webp' => 'webp',
		);

		$overrides = array();

		foreach ( self::get_allowed_mimes() as $mime ) {
			if ( isset( $extensions[ $mime ] ) ) {
				$overrides[ $extensions[ $mime ] ] = $mime;
			}
		}

		return $overrides;
	}

	/**
	 * Human readable list of accepted types, for error messages.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	private static function get_allowed_types_label(): string {
		$labels = array(
			'image/jpeg' => 'JPG',
			'image/png'  => 'PNG',
			'image/gif'  => 'GIF',
			'image/webp' => 'WEBP',
		);

		$allowed = array();

		foreach ( self::get_allowed_mimes() as $mime ) {
			if ( isset( $labels[ $mime ] ) ) {
				$allowed[] = $labels[ $mime ];
			}
		}

		return implode( ', ', $allowed );
	}

	/**
	 * The upload ceiling in bytes.
	 *
	 * The site owner's setting can only ever tighten the server limit, never raise
	 * it past what PHP will actually accept.
	 *
	 * @since 1.3.0
	 *
	 * @return int
	 */
	private static function get_max_upload_bytes(): int {
		$options = get_option( 'wpmake_advance_user_avatar_settings', array() );
		$server  = (int) wp_max_upload_size();

		$configured = isset( $options['max_size'] ) ? absint( $options['max_size'] ) * 1024 : 0;

		return $configured > 0 ? min( $configured, $server ) : $server;
	}

	/**
	 * The user this request is acting on.
	 *
	 * Defaults to the current user, which covers every request the bundled uploader
	 * makes -- it does not send a user_id and does not need to. An explicit user_id
	 * is what the profile screen and the bulk manager post, and is only honoured
	 * once wpmake_aua_current_user_can_edit_avatar() has agreed to it.
	 *
	 * @since 1.4.0
	 *
	 * @return int
	 */
	private static function get_target_user_id(): int {
		// Both callers run check_ajax_referer() before they reach this.
		$raw = isset( $_REQUEST['user_id'] ) ? wp_unslash( $_REQUEST['user_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- cast to an integer below.

		// Insisting on a scalar rather than casting whatever turns up: absint() of an
		// array is 1, so `user_id[]=` would quietly resolve to user 1.
		$requested = is_scalar( $raw ) ? absint( $raw ) : 0;

		return $requested > 0 ? $requested : get_current_user_id();
	}

	/**
	 * Refuse a request that names a user the caller may not edit.
	 *
	 * Deliberately separate from the nonce check. A valid nonce proves the request
	 * came from this user's own session; it says nothing about whether that user is
	 * allowed to touch the account named in it. The refusal is sent as JSON with a
	 * 403, which a failed nonce is not -- check_ajax_referer() dies with a bare -1 --
	 * so the two are distinguishable by the caller.
	 *
	 * @since 1.4.0
	 *
	 * @param int $user_id User the request wants to act on.
	 * @return void
	 */
	private static function require_avatar_capability( int $user_id ) {
		if ( wpmake_aua_current_user_can_edit_avatar( $user_id ) ) {
			return;
		}

		wp_send_json_error(
			array(
				'message' => esc_html__( 'You are not allowed to change this user\'s avatar.', 'wpmake-advance-user-avatar' ),
			),
			403
		);
	}

	/**
	 * Assign an attachment that already exists as a user's avatar.
	 *
	 * The upload handler needs a file. The bulk manager and the media picker have an
	 * attachment ID and nothing to upload, so they post here instead. Same nonce and
	 * capability discipline as the other two.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public static function set_avatar() {
		check_ajax_referer( 'wpmake_advance_user_avatar_set_nonce', 'security' );

		$user_id = self::get_target_user_id();

		if ( ! $user_id ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You must be logged in to change an avatar.', 'wpmake-advance-user-avatar' ),
				)
			);
		}

		self::require_avatar_capability( $user_id );

		/*
		 * Assigning an attachment that already exists is a Media Library operation, so
		 * it takes the Media Library capability -- on top of the avatar check above,
		 * which only says whose avatar may be changed.
		 *
		 * Without this the endpoint trusts the caller to have obeyed the "Let users
		 * choose from the Media Library" setting. The button is not rendered for a user
		 * who cannot upload_files, but the nonce is localised for everybody, so a
		 * subscriber could post an attachment ID and help themselves to any image on
		 * the site. That is the same shape as the 1.3.0 fix: a value that belongs to
		 * the site owner's settings being decided by the request.
		 */
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You are not allowed to choose from the Media Library.', 'wpmake-advance-user-avatar' ),
				),
				403
			);
		}

		$attachment_id = isset( $_REQUEST['attachment_id'] ) ? absint( wp_unslash( $_REQUEST['attachment_id'] ) ) : 0;

		if ( ! wpmake_aua_set_user_avatar( $user_id, $attachment_id ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'That media item could not be used as an avatar. Pick an image and try again.', 'wpmake-advance-user-avatar' ),
				)
			);
		}

		wp_send_json_success(
			array(
				'attachment_id'       => $attachment_id,
				'profile_picture_url' => wpmake_aua_get_avatar_url( $user_id, 96 ),
				'message'             => esc_html__( 'Avatar updated.', 'wpmake-advance-user-avatar' ),
			)
		);
	}

	/**
	 * User avatar remove function.
	 */
	public static function remove_avatar() {
		check_ajax_referer( 'wpmake_advance_user_avatar_remove_nonce', 'security' );

		$user_id = self::get_target_user_id();

		if ( ! $user_id ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You must be logged in to remove your avatar.', 'wpmake-advance-user-avatar' ),
				)
			);
		}

		self::require_avatar_capability( $user_id );

		if ( ! wpmake_aua_remove_user_avatar( $user_id ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'The avatar could not be removed. Please try again.', 'wpmake-advance-user-avatar' ),
				)
			);
		}

		wp_send_json_success(
			array(
				'message'      => esc_html__( 'User avatar removed successfully', 'wpmake-advance-user-avatar' ),

				/*
				 * What the user falls back to now. force_default skips this plugin's own
				 * pre_get_avatar_data filter, so it is whatever the site shows for
				 * somebody who has never uploaded one -- which the bulk manager needs to
				 * repaint the row without reloading the page.
				 */
				'fallback_url' => get_avatar_url(
					$user_id,
					array(
						'size'          => 96,
						'force_default' => true,
					)
				),
			)
		);
	}

	/**
	 * Turn an uploaded file into an avatar attachment.
	 *
	 * The whole pipeline the site owner's settings govern: the accepted mime types,
	 * the size ceiling, the plugin's own upload directory, EXIF orientation, the
	 * configured output size, and the 32/64/96px avatar variants.
	 *
	 * Extracted from method_upload() so the profile screen posts its file through
	 * exactly the same path. A second implementation in wp-admin would be a second
	 * place for the settings to stop being honoured.
	 *
	 * Does no capability checking -- callers must have cleared
	 * wpmake_aua_current_user_can_edit_avatar() before handing a file to this.
	 *
	 * @since 1.4.0
	 *
	 * @param array $file     One entry from $_FILES.
	 * @param array $crop     Optional crop geometry from the browser cropper.
	 * @param int   $owner_id User the avatar is for. Becomes the attachment's author,
	 *                        so an avatar belongs to the person it depicts rather than
	 *                        to whichever administrator happened to upload it.
	 * @return int|\WP_Error Attachment ID, or a WP_Error describing the failure.
	 */
	public static function create_avatar_attachment( array $file, array $crop = array(), int $owner_id = 0 ) {
		if ( empty( $file['tmp_name'] ) || ! isset( $file['size'] ) || $file['size'] < 1 ) {
			return new \WP_Error( 'wpmake_aua_no_file', esc_html__( 'No file was received. Please select a file and try again.', 'wpmake-advance-user-avatar' ) );
		}

		/*
		 * Sniff the real type off the file itself. The check this replaces looked
		 * only at the extension on the supplied filename.
		 */
		$filetype = wp_check_filetype_and_ext( $file['tmp_name'], isset( $file['name'] ) ? $file['name'] : '' );

		if ( empty( $filetype['type'] ) || ! in_array( $filetype['type'], self::get_allowed_mimes(), true ) ) {
			return new \WP_Error(
				'wpmake_aua_bad_type',
				sprintf(
					/* translators: %s: comma-separated list of accepted file types e.g. JPG, PNG, GIF */
					esc_html__( 'Invalid file type. Accepted: %s.', 'wpmake-advance-user-avatar' ),
					esc_html( self::get_allowed_types_label() )
				)
			);
		}

		$max_upload_bytes = self::get_max_upload_bytes();

		if ( $file['size'] > $max_upload_bytes ) {
			return new \WP_Error(
				'wpmake_aua_too_large',
				/* translators: %s - Max Size */
				sprintf( esc_html__( 'Please upload a picture with size less than %s', 'wpmake-advance-user-avatar' ), size_format( $max_upload_bytes ) )
			);
		}

		$upload_dir  = wp_upload_dir();
		$upload_path = apply_filters( 'wpmake_advance_user_avatar_upload_url', $upload_dir['basedir'] . '/wpmake-advance-user-avatar' ); /*Get path of upload dir of WordPress*/

		// Cheap when the directory is already there; covers upgrades that never re-run activation.
		wp_mkdir_p( $upload_path );

		if ( ! is_writable( $upload_path ) ) {  /*Check if upload dir is writable*/ // phpcs:ignore
			return new \WP_Error( 'wpmake_aua_unwritable', esc_html__( 'Upload path permission deny.', 'wpmake-advance-user-avatar' ) );
		}

		$custom_subdir = '/wpmake-advance-user-avatar';
		$custom_path   = $upload_dir['basedir'] . $custom_subdir;
		$custom_url    = $upload_dir['baseurl'] . $custom_subdir;

		$upload_dir_filter = static function ( $dirs ) use ( $custom_path, $custom_url, $custom_subdir ) {
			$dirs['path']   = $custom_path;
			$dirs['url']    = $custom_url;
			$dirs['subdir'] = $custom_subdir;
			return $dirs;
		};

		add_filter( 'upload_dir', $upload_dir_filter );

		$overrides = array(
			'test_form' => false,
			'mimes'     => self::get_allowed_mime_overrides(),
		);

		$uploaded = wp_handle_upload( $file, $overrides );
		remove_filter( 'upload_dir', $upload_dir_filter );

		if ( ! $uploaded || isset( $uploaded['error'] ) ) {
			return new \WP_Error(
				'wpmake_aua_upload_failed',
				! empty( $uploaded['error'] )
					? $uploaded['error']
					: esc_html__( 'File cannot be uploaded.', 'wpmake-advance-user-avatar' )
			);
		}

		$file_url  = $uploaded['url'];
		$file_path = $uploaded['file'];
		$file_type = $uploaded['type'];

		// Fix image EXIF orientation before processing further.
		self::fix_image_orientation( $file_path );

		$options = get_option( 'wpmake_advance_user_avatar_settings', array() );

		/*
		 * Crop before the attachment exists, so the thumbnail sizes generated below
		 * are cut from the final image rather than from the untouched upload.
		 */
		if ( ! empty( $options['cropping_interface'] ) && ! empty( $crop ) ) {
			$cropped = self::crop_image( $file_path, $crop, $options );

			if ( is_wp_error( $cropped ) ) {
				wp_delete_file( $file_path );

				return $cropped;
			}

			/*
			 * A site filtering image_editor_output_format -- a WebP conversion plugin,
			 * say -- can have the editor write under a different extension than it
			 * read. Follow the file the editor actually produced, or the attachment
			 * would be pointing at the copy we are about to leave behind.
			 */
			if ( $cropped['path'] !== $file_path ) {
				wp_delete_file( $file_path );

				$file_url  = trailingslashit( dirname( $file_url ) ) . wp_basename( $cropped['path'] );
				$file_path = $cropped['path'];
				$file_type = $cropped['mime-type'];
			}
		} else {
			/*
			 * No crop geometry, which is every upload made with the cropper switched
			 * off. The configured "Uploaded Image Size" used to be skipped entirely
			 * here, so the setting did nothing at all unless the cropper was on.
			 */
			$resized = self::resize_image( $file_path, $options );

			if ( is_wp_error( $resized ) ) {
				wp_delete_file( $file_path );

				return $resized;
			}

			// Same output-format caveat as the crop branch above.
			if ( is_array( $resized ) && $resized['path'] !== $file_path ) {
				wp_delete_file( $file_path );

				$file_url  = trailingslashit( dirname( $file_url ) ) . wp_basename( $resized['path'] );
				$file_path = $resized['path'];
				$file_type = $resized['mime-type'];
			}
		}

		$attachment = array(
			'guid'           => $file_url,
			'post_mime_type' => $file_type,
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_path ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		/*
		 * Author the attachment to the user the avatar is for. Left to default it
		 * would belong to whoever was acting, so an administrator setting a
		 * customer's picture would own it -- and deleting that administrator would
		 * offer to delete every avatar they had ever set for somebody else.
		 */
		if ( $owner_id > 0 ) {
			$attachment['post_author'] = $owner_id;
		}

		$attachment_id = wp_insert_attachment( $attachment, $file_path );

		if ( is_wp_error( $attachment_id ) ) {
			wp_delete_file( $file_path );

			return $attachment_id;
		}

		include_once ABSPATH . 'wp-admin/includes/image.php';

		if ( ! isset( $options['thumbnail_size'] ) || $options['thumbnail_size'] ) {
			/*
			 * Add the 32/64/96px avatar variants for this attachment only. The setting
			 * has always promised them; until now nothing generated them.
			 */
			add_filter( 'intermediate_image_sizes_advanced', 'wpmake_aua_add_avatar_subsizes' );

			// Generate and save the attachment metas into the database.
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, get_attached_file( $attachment_id ) ) );

			remove_filter( 'intermediate_image_sizes_advanced', 'wpmake_aua_add_avatar_subsizes' );
		}

		return $attachment_id;
	}

	/**
	 * Handle an avatar upload.
	 *
	 * Who may upload, which types are accepted and how large a file may be are all
	 * decided server side. Until 1.3.0 the accepted types and the size ceiling came
	 * out of the request body, so anything that could edit the form could ignore the
	 * site owner's settings entirely.
	 */
	public static function method_upload() {

		check_ajax_referer( 'wpmake_advance_user_avatar_upload_nonce', 'security' );

		$user_id = self::get_target_user_id();

		if ( ! $user_id ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You must be logged in to upload an avatar.', 'wpmake-advance-user-avatar' ),
				)
			);
		}

		/*
		 * Ahead of anything that touches the file, for the same reason 1.3.0 moved
		 * the logged-in check ahead of wp_handle_upload(): a request that was never
		 * going to be allowed should not leave a file on disk behind it.
		 */
		self::require_avatar_capability( $user_id );

		$upload = isset( $_FILES['file'] ) ? $_FILES['file'] : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Retrieves cropped picture dimensions from ajax request.
		$value = isset( $_REQUEST['cropped_image'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['cropped_image'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$crop  = json_decode( $value, true );

		$attachment_id = self::create_avatar_attachment( $upload, is_array( $crop ) ? $crop : array(), $user_id );

		if ( is_wp_error( $attachment_id ) ) {
			wp_send_json_error(
				array(
					'message' => $attachment_id->get_error_message(),
				)
			);
		}

		if ( ! wpmake_aua_set_user_avatar( $user_id, $attachment_id ) ) {
			/*
			 * The attachment exists on disk and in the database by this point, so take
			 * it back out rather than leaving a file nothing points at.
			 */
			wp_delete_attachment( $attachment_id, true );

			wp_send_json_error(
				array(
					'message' => esc_html__( 'The avatar could not be saved. Please try again.', 'wpmake-advance-user-avatar' ),
				)
			);
		}

		$url = wp_get_attachment_url( $attachment_id );

		if ( empty( $url ) ) {
			// Same value the attachment's guid was set to.
			$url = get_post_field( 'guid', $attachment_id );
		}

		wp_send_json_success(
			array(
				'attachment_id'       => $attachment_id,
				'profile_picture_url' => $url,
			)
		);
	}

	/**
	 * Scale an upload down to the configured avatar size.
	 *
	 * The bundled uploader posts an empty cropped_image, so crop_image() below never
	 * runs in normal use. That left "Uploaded Image Size" applied only by the
	 * browser cropper, and doing nothing whatsoever when the cropper was switched
	 * off. This is the path for those uploads.
	 *
	 * Centre-crops to the configured aspect, matching what the browser cropper does,
	 * so both routes produce the same shape. Images already at or below the
	 * configured size are left alone -- upscaling an avatar only adds bytes and
	 * blur.
	 *
	 * @since 1.4.0
	 *
	 * @param string $file_path Absolute path to the uploaded file.
	 * @param array  $options   Plugin settings.
	 * @return array|true|\WP_Error Saved file details when the image was resized,
	 *                              true when it was already small enough, or a
	 *                              WP_Error describing the failure.
	 */
	private static function resize_image( $file_path, $options ) {
		$size = wpmake_aua_get_uploaded_image_size();

		$dimensions = @getimagesize( $file_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- a malformed image is handled by the check below.

		if ( ! is_array( $dimensions ) || empty( $dimensions[0] ) || empty( $dimensions[1] ) ) {
			// Not readable as an image. The mime checks earlier already rejected
			// anything genuinely invalid, so leave it rather than failing the upload.
			return true;
		}

		if ( $dimensions[0] <= $size['width'] && $dimensions[1] <= $size['height'] ) {
			return true;
		}

		$editor = wp_get_image_editor( $file_path );

		if ( is_wp_error( $editor ) ) {
			return $editor;
		}

		$resized = $editor->resize( $size['width'], $size['height'], true );

		if ( is_wp_error( $resized ) ) {
			return $resized;
		}

		return $editor->save( $file_path );
	}

	/**
	 * Crop an uploaded avatar down to the configured output size.
	 *
	 * Goes through WP_Image_Editor rather than raw GD so the file is read and
	 * written with the codec matching its own type. The implementation this
	 * replaces read PNG and GIF correctly but always wrote back through
	 * imagejpeg(), leaving JPEG bytes behind a .png extension and flattening
	 * animated GIFs, and had no WEBP branch at all -- a WEBP upload fell through
	 * to imagecreatefromjpeg() and failed.
	 *
	 * Note that the bundled uploader crops on a canvas in the browser and posts an
	 * empty cropped_image, so it never reaches this path. It stays for callers that
	 * do post crop geometry, and for any client still running older cached JS.
	 *
	 * @since 1.3.0
	 *
	 * @param string $file_path Absolute path to the uploaded file.
	 * @param array  $crop      Crop geometry from the browser: x, y, w and h, in the
	 *                          holder_width / holder_height space they were measured in.
	 * @param array  $options   Plugin settings.
	 * @return array|\WP_Error The saved file details from WP_Image_Editor::save() on
	 *                         success -- 'path' and 'mime-type' among them -- or a
	 *                         WP_Error describing why the crop could not be applied.
	 */
	private static function crop_image( $file_path, $crop, $options ) {
		$holder_width  = (float) rtrim( isset( $crop['holder_width'] ) ? $crop['holder_width'] : '', 'px' );
		$holder_height = (float) rtrim( isset( $crop['holder_height'] ) ? $crop['holder_height'] : '', 'px' );

		if ( $holder_width <= 0 || $holder_height <= 0 ) {
			return new \WP_Error( 'wpmake_aua_bad_crop', esc_html__( 'The crop area could not be read. Please try again.', 'wpmake-advance-user-avatar' ) );
		}

		$size = getimagesize( $file_path );

		if ( ! $size ) {
			return new \WP_Error( 'wpmake_aua_bad_image', esc_html__( 'The uploaded file could not be read as an image.', 'wpmake-advance-user-avatar' ) );
		}

		list( $original_width, $original_height ) = $size;

		// Scale the browser-space crop box back up to the real image.
		$scale_x = $original_width / $holder_width;
		$scale_y = $original_height / $holder_height;

		$src_x = absint( ( isset( $crop['x'] ) ? $crop['x'] : 0 ) * $scale_x );
		$src_y = absint( ( isset( $crop['y'] ) ? $crop['y'] : 0 ) * $scale_y );
		$src_w = absint( ( isset( $crop['w'] ) ? $crop['w'] : 0 ) * $scale_x );
		$src_h = absint( ( isset( $crop['h'] ) ? $crop['h'] : 0 ) * $scale_y );

		if ( $src_w < 1 || $src_h < 1 ) {
			return new \WP_Error( 'wpmake_aua_bad_crop', esc_html__( 'The crop area could not be read. Please try again.', 'wpmake-advance-user-avatar' ) );
		}

		$dest_width  = 500;
		$dest_height = 500;

		if ( isset( $options['uploaded_image_size'] ) ) {
			$dest_width  = absint( $options['uploaded_image_size']['width'] );
			$dest_height = absint( $options['uploaded_image_size']['height'] );

			$dest_width  = $dest_width > 0 ? $dest_width : 500;
			$dest_height = $dest_height > 0 ? $dest_height : 500;
		}

		$editor = wp_get_image_editor( $file_path );

		if ( is_wp_error( $editor ) ) {
			return $editor;
		}

		$cropped = $editor->crop( $src_x, $src_y, $src_w, $src_h, $dest_width, $dest_height );

		if ( is_wp_error( $cropped ) ) {
			return $cropped;
		}

		return $editor->save( $file_path );
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
	 * Handles three dismissal types sent via $_POST['type']:
	 *   - 'rated'  → permanently hides the notice (user reviewed or already did).
	 *   - 'later'  → snoozes the notice for 14 days via a transient.
	 *   - Anything else is treated as 'rated' for safety.
	 *
	 * @since 1.0.2
	 *
	 * @return void
	 **/
	public static function dismiss_notice() {
		check_admin_referer( 'notice_nonce', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		if ( empty( $_POST['dismissed'] ) ) {
			wp_die();
		}

		$type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'rated';

		if ( 'later' === $type ) {
			// Re-show the notice after 14 days.
			set_transient( 'wpmake_aua_review_notice_snoozed', true, 14 * DAY_IN_SECONDS );
		} else {
			// Permanently suppress the notice.
			update_option( 'wpmake_aua_review_notice_dismissed', true );
		}

		wp_die();
	}

	/**
	 * Fix image orientation based on EXIF data.
	 *
	 * @param string $file_path Path to the image file.
	 * @return bool True if orientation was fixed, false otherwise.
	 */
	private static function fix_image_orientation( $file_path ) {
		if ( ! function_exists( 'exif_read_data' ) ) {
			return false;
		}

		// Only JPEG images contain EXIF orientation.
		$mime_type = mime_content_type( $file_path );
		if ( strpos( $mime_type, 'jpeg' ) === false && strpos( $mime_type, 'jpg' ) === false ) {
			return false;
		}

		$exif = @exif_read_data( $file_path );
		if ( ! $exif || ! isset( $exif['Orientation'] ) ) {
			return false;
		}

		$image = wp_get_image_editor( $file_path );
		if ( is_wp_error( $image ) ) {
			return false;
		}

		$rotated = false;

		switch ( $exif['Orientation'] ) {
			case 3:
				$image->rotate( 180 );
				$rotated = true;
				break;
			case 6:
				$image->rotate( -90 );
				$rotated = true;
				break;
			case 8:
				$image->rotate( 90 );
				$rotated = true;
				break;
		}

		if ( $rotated ) {
			$image->save( $file_path );
		}

		return $rotated;
	}
}
