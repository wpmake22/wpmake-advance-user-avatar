<?php
/**
 * WPMake User Avatar Uploader Layout
 *
 * Shows user lists in selected layout
 *
 * @package WPMakeAdvanceUserAvatar/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="wpmake-advance-user-avatar-container">
	<?php
		$gravatar_image      = get_avatar_url( get_current_user_id(), $args = null );
		$profile_picture_url = wp_get_attachment_url( get_user_meta( get_current_user_id(), 'wpmake_advance_user_avatar_attachment_id', true ) );
		$image               = ( ! empty( $profile_picture_url ) ) ? $profile_picture_url : $gravatar_image;
		$max_size            = wp_max_upload_size();
		$max_upload_size     = $max_size;
		$options             = get_option( 'wpmake_advance_user_avatar_settings' );

	if ( isset( $options['max_size'] ) ) {
		$max_upload_size = $options['max_size'];
	}

	$wpmake_valid_file_type = 'image/jpeg,image/jpg,image/gif,image/png';

	if ( isset( $options['allowed_file_type'] ) ) {
		$wpmake_valid_file_type = implode( ', ', $options['allowed_file_type'] );
	}
	?>
	<img class="profile-preview" alt="profile-picture" src="<?php echo esc_url( $image ); ?>" >
	<header>
		<div class="button-group">
			<div class="wpmake-advance-user-avatar-upload">
				<p class="form-row " id="profile_pic_url_field" data-priority="">
					<span class="wpmake-advance-user-avatar-upload-node" >
					<input type="file" id="wpmake-advance-user-avatar-pic" name="profile-pic" class="profile-pic-upload" size="<?php echo esc_attr( $max_upload_size ); ?>" accept="<?php echo esc_attr( $wpmake_valid_file_type ); ?>" style="<?php echo esc_attr( ( $gravatar_image !== $image ) ? 'display:none;' : '' ); ?>" />
					<?php echo '<input type="text" class="wpmake-advance-user-avatar-input input-text wpmake-advance-user-avatar-frontend-field" name="profile_pic_url" id="profile_pic_url" value="' . esc_url( $profile_picture_url ) . '" />'; ?>
					</span>
					<?php
					$options = get_option( 'wpmake_advance_user_avatar_settings', array() );

					if ( ! $profile_picture_url ) {
						?>
							<button type="button" class="button wpmake-advance-user-avatar-remove hide-if-no-js" style="display:none"><?php esc_html_e( 'Remove', 'wpmake-advance-user-avatar' ); ?></button>
						<?php
						if ( isset( $options['capture_picture'] ) && $options['capture_picture'] ) {
							?>
							<button type="button" class="button wpmake_advance_user_avatar_take_snapshot hide-if-no-js"><?php esc_html_e( 'Take Picture', 'wpmake-advance-user-avatar' ); ?></button>
							<?php
						}
						?>
							<button type="button" class="button wpmake_advance_user_avatar_upload hide-if-no-js"><?php esc_html_e( 'Upload file', 'wpmake-advance-user-avatar' ); ?></button>
						<?php
					} else {
						?>
							<button type="button" class="button wpmake-advance-user-avatar-remove hide-if-no-js"><?php esc_html_e( 'Remove', 'wpmake-advance-user-avatar' ); ?></button>

							<?php
							if ( aua_fs()->can_use_premium_code__premium_only() && isset( $options['capture_picture'] ) && $options['capture_picture'] ) {
								?>
							<button type="button" class="button wpmake_advance_user_avatar_take_snapshot hide-if-no-js" style="display:none"><?php esc_html_e( 'Take Picture', 'wpmake-advance-user-avatar' ); ?></button>
								<?php
							}
							?>
							<button type="button" class="button wpmake_advance_user_avatar_upload hide-if-no-js" style="display:none"><?php esc_html_e( 'Upload file', 'wpmake-advance-user-avatar' ); ?></button>
						<?php
					}
					?>
				</p>
			</div>
		</div>
	</header>
</div>
