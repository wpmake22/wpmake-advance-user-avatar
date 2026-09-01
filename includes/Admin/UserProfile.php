<?php
/**
 * WPMakeAdvanceUserAvatar UserProfile.
 *
 * The avatar field on the WordPress user profile screen.
 *
 * @class    UserProfile
 * @version  1.4.0
 * @package  WPMakeAdvanceUserAvatar/Admin
 */

namespace WPMake\WPMakeAdvanceUserAvatar\Admin;

use WPMake\WPMakeAdvanceUserAvatar\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UserProfile Class
 *
 * Adds a Profile Picture field to Users > Profile and Users > Edit User, the
 * most-expected place for an avatar plugin to put one and somewhere this plugin
 * has never had a field at all.
 *
 * Deliberately plainer than the front-end uploader: a file input and a media
 * picker, with no webcam and no crop modal. The front-end bundle has no business
 * in wp-admin.
 */
class UserProfile {

	/**
	 * Nonce action, per user being edited.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'wpmake_aua_save_avatar_';

	/**
	 * Nonce field name.
	 *
	 * @var string
	 */
	const NONCE_NAME = 'wpmake_aua_profile_nonce';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'show_user_profile', array( $this, 'render_field' ) );
		add_action( 'edit_user_profile', array( $this, 'render_field' ) );

		add_action( 'personal_options_update', array( $this, 'save' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save' ) );

		add_action( 'user_edit_form_tag', array( $this, 'add_form_enctype' ) );
		add_action( 'admin_notices', array( $this, 'print_save_error' ) );

		add_filter( 'user_profile_picture_description', array( $this, 'profile_picture_description' ), 10, 2 );
	}

	/**
	 * The user whose profile screen this request is on.
	 *
	 * @since 1.4.0
	 *
	 * @return int User ID, or 0 when this is not a profile screen.
	 */
	public static function get_screen_user_id(): int {
		global $pagenow;

		if ( 'profile.php' === $pagenow ) {
			return get_current_user_id();
		}

		if ( 'user-edit.php' !== $pagenow ) {
			return 0;
		}

		// Reading which user the screen is for, not acting on it. Core checks the
		// nonce and the capability itself before it saves anything.
		$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return $user_id > 0 ? $user_id : get_current_user_id();
	}

	/**
	 * Whether the current user has a Media Library worth opening.
	 *
	 * A subscriber has no `upload_files`, so the picker opens on an empty grid --
	 * which reads as a broken button rather than an empty library. The file input
	 * beside it still works for them, and that is the control they actually need.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	public static function can_use_media_library(): bool {
		return current_user_can( 'upload_files' );
	}

	/**
	 * Let the profile form carry a file.
	 *
	 * Core's form is a plain `method="post"` with no enctype (see
	 * wp-admin/user-edit.php line 277), so without this the file input renders and
	 * silently posts nothing at all.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function add_form_enctype(): void {
		echo ' enctype="multipart/form-data"';
	}

	/**
	 * Replace core's "change your profile picture on Gravatar" text.
	 *
	 * Actively misleading once this plugin is active: changing the Gravatar does
	 * nothing for a user whose avatar is stored on this site.
	 *
	 * @since 1.4.0
	 *
	 * @param string   $description  Description core was going to print.
	 * @param \WP_User $profile_user User whose profile is being shown.
	 * @return string
	 */
	public function profile_picture_description( $description, $profile_user ) {
		if ( ! $profile_user instanceof \WP_User ) {
			return $description;
		}

		return esc_html__( 'This site stores profile pictures itself. Set or change this one in the Profile Picture field below.', 'wpmake-advance-user-avatar' );
	}

	/**
	 * Render the Profile Picture field.
	 *
	 * @since 1.4.0
	 *
	 * @param \WP_User $user User being edited.
	 * @return void
	 */
	public function render_field( $user ): void {
		if ( ! $user instanceof \WP_User || ! wpmake_aua_current_user_can_edit_avatar( $user->ID ) ) {
			return;
		}

		$attachment_id = (int) get_user_meta( $user->ID, 'wpmake_advance_user_avatar_attachment_id', true );
		$has_avatar    = $attachment_id > 0 && wp_attachment_is_image( $attachment_id );

		/*
		 * The fallback the Remove button reverts the preview to. Asking core for it
		 * with force_default skips this plugin's own pre_get_avatar_data filter, so
		 * it is whatever the site would show for a user with no uploaded avatar.
		 */
		$default_url = get_avatar_url(
			$user->ID,
			array(
				'size'          => 96,
				'force_default' => true,
			)
		);
		$current_url = $has_avatar ? wpmake_aua_get_avatar_url( $user->ID, 96 ) : $default_url;
		?>
		<h2 id="wpmake-aua-profile-picture"><?php esc_html_e( 'Profile Picture', 'wpmake-advance-user-avatar' ); ?></h2>

		<table class="form-table" role="presentation">
			<tr class="wpmake-aua-profile-picture-row">
				<th>
					<label for="wpmake_aua_avatar_file"><?php esc_html_e( 'Profile Picture', 'wpmake-advance-user-avatar' ); ?></label>
				</th>
				<td>
					<p>
						<img
							id="wpmake-aua-avatar-preview"
							src="<?php echo esc_url( $current_url ); ?>"
							data-default="<?php echo esc_url( $default_url ); ?>"
							width="96"
							height="96"
							alt=""
							class="avatar avatar-96 photo"
						/>
					</p>

					<p>
						<input
							type="file"
							name="wpmake_aua_avatar_file"
							id="wpmake_aua_avatar_file"
							accept="<?php echo esc_attr( implode( ',', wpmake_aua_get_allowed_mimes() ) ); ?>"
						/>
					</p>

					<p>
						<?php if ( self::can_use_media_library() ) : ?>
							<button type="button" class="button wpmake-aua-choose-media">
								<?php esc_html_e( 'Choose from Media Library', 'wpmake-advance-user-avatar' ); ?>
							</button>
						<?php endif; ?>
						<button
							type="button"
							class="button wpmake-aua-remove-avatar"
							<?php echo $has_avatar ? '' : 'style="display:none"'; ?>
						>
							<?php esc_html_e( 'Remove', 'wpmake-advance-user-avatar' ); ?>
						</button>
					</p>

					<input type="hidden" name="wpmake_aua_avatar_id" id="wpmake_aua_avatar_id" value="" />
					<input type="hidden" name="wpmake_aua_avatar_remove" id="wpmake_aua_avatar_remove" value="" />
					<?php wp_nonce_field( self::NONCE_ACTION . $user->ID, self::NONCE_NAME ); ?>

					<p class="description">
						<?php esc_html_e( 'Upload a picture from this device, or pick one already in the Media Library. The change is saved with the rest of the profile.', 'wpmake-advance-user-avatar' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save the avatar posted from the profile screen.
	 *
	 * Core has already checked its own nonce and `edit_user` by the time this runs,
	 * but neither of those is this field's. The nonce below is specific to this
	 * form section and the capability check is the plugin's own, so the field is
	 * safe wherever else these hooks might be fired from.
	 *
	 * @since 1.4.0
	 *
	 * @param int $user_id User being saved.
	 * @return void
	 */
	public function save( $user_id ): void {
		$user_id = (int) $user_id;

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			// The field was never rendered for this request, so there is nothing here
			// to save. Not an error.
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) );

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION . $user_id ) ) {
			return;
		}

		if ( ! wpmake_aua_current_user_can_edit_avatar( $user_id ) ) {
			return;
		}

		// Remove wins over everything else: it is the only one of the three the user
		// can have chosen after choosing another.
		if ( ! empty( $_POST['wpmake_aua_avatar_remove'] ) ) {
			wpmake_aua_remove_user_avatar( $user_id );

			return;
		}

		if ( ! empty( $_FILES['wpmake_aua_avatar_file']['name'] ) ) {
			$this->save_uploaded_file( $user_id );

			return;
		}

		$attachment_id = isset( $_POST['wpmake_aua_avatar_id'] ) ? absint( wp_unslash( $_POST['wpmake_aua_avatar_id'] ) ) : 0;

		if ( $attachment_id > 0 && ! wpmake_aua_set_user_avatar( $user_id, $attachment_id ) ) {
			$this->set_save_error( esc_html__( 'That media item could not be used as an avatar. Pick an image and try again.', 'wpmake-advance-user-avatar' ) );
		}
	}

	/**
	 * Put an uploaded file through the plugin's own upload pipeline.
	 *
	 * The same Ajax::create_avatar_attachment() the front-end uploader posts to, so
	 * the allowed types, the size ceiling, the upload directory, EXIF orientation,
	 * the configured output size and the avatar sub-sizes are all applied here too.
	 *
	 * @since 1.4.0
	 *
	 * @param int $user_id User being saved.
	 * @return void
	 */
	private function save_uploaded_file( int $user_id ): void {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- validated inside the pipeline, which sniffs the file's real type.
		$file = isset( $_FILES['wpmake_aua_avatar_file'] ) ? $_FILES['wpmake_aua_avatar_file'] : array();

		$attachment_id = Ajax::create_avatar_attachment( (array) $file, array(), $user_id );

		if ( is_wp_error( $attachment_id ) ) {
			$this->set_save_error( $attachment_id->get_error_message() );

			return;
		}

		if ( ! wpmake_aua_set_user_avatar( $user_id, $attachment_id ) ) {
			// The file and the attachment both exist by now; take them back out
			// rather than leaving an attachment nothing points at.
			wp_delete_attachment( $attachment_id, true );

			$this->set_save_error( esc_html__( 'The avatar could not be saved. Please try again.', 'wpmake-advance-user-avatar' ) );
		}
	}

	/**
	 * Remember a failure so it can be shown after the redirect.
	 *
	 * These hooks fire before core redirects back to the profile screen, so there
	 * is nothing on screen to print an error into. Held against the user doing the
	 * editing, not the user being edited, and long enough only for the redirect.
	 *
	 * @since 1.4.0
	 *
	 * @param string $message Message to show.
	 * @return void
	 */
	private function set_save_error( string $message ): void {
		set_transient( 'wpmake_aua_profile_error_' . get_current_user_id(), $message, MINUTE_IN_SECONDS );
	}

	/**
	 * Print, and clear, a failure held over from the save.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function print_save_error(): void {
		$key     = 'wpmake_aua_profile_error_' . get_current_user_id();
		$message = get_transient( $key );

		if ( ! $message ) {
			return;
		}

		delete_transient( $key );

		printf(
			'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
			esc_html( $message )
		);
	}
}
