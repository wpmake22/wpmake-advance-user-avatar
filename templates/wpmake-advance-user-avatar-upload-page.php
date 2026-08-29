<?php
/**
 * WPMake User Avatar Uploader Layout
 *
 * Shows user lists in selected layout
 *
 * This template can be overridden by copying it to
 * yourtheme/wpmake-advance-user-avatar/wpmake-advance-user-avatar-upload-page.php
 *
 * @package WPMakeAdvanceUserAvatar/Templates
 * @version 1.3.0
 */

use WPMake\WPMakeAdvanceUserAvatar\Admin\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wpmake_aua_atts  = isset( $wpmake_aua_atts ) ? $wpmake_aua_atts : Shortcodes::default_uploader_atts();
$wpmake_aua_style = Shortcodes::container_style( $wpmake_aua_atts );
?>
<div class="<?php echo esc_attr( Shortcodes::container_class( $wpmake_aua_atts ) ); ?>"<?php echo $wpmake_aua_style ? ' style="' . esc_attr( $wpmake_aua_style ) . '"' : ''; ?>>
	<?php
		do_action( 'wpmake_advance_user_avatar_before_uploader', $wpmake_aua_atts );

		$gravatar_image      = get_avatar_url( get_current_user_id(), null );
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

	// Button labels: the shortcode attribute wins, otherwise the translated default.
	$wpmake_upload_label  = ! empty( $wpmake_aua_atts['upload_text'] ) ? $wpmake_aua_atts['upload_text'] : __( 'Upload file', 'wpmake-advance-user-avatar' );
	$wpmake_remove_label  = ! empty( $wpmake_aua_atts['remove_text'] ) ? $wpmake_aua_atts['remove_text'] : __( 'Remove', 'wpmake-advance-user-avatar' );
	$wpmake_capture_label = ! empty( $wpmake_aua_atts['capture_text'] ) ? $wpmake_aua_atts['capture_text'] : __( 'Take Picture', 'wpmake-advance-user-avatar' );
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

					do_action( 'wpmake_advance_user_avatar_before_upload_buttons', $wpmake_aua_atts );

					if ( ! $profile_picture_url ) {
						?>
							<button type="button" class="button wpmake-advance-user-avatar-remove hide-if-no-js" style="display:none"><?php echo esc_html( $wpmake_remove_label ); ?></button>
						<?php
						if ( isset( $options['capture_picture'] ) && $options['capture_picture'] ) {
							?>
							<button type="button" class="button wpmake_advance_user_avatar_take_snapshot hide-if-no-js"><?php echo esc_html( $wpmake_capture_label ); ?></button>
							<?php
						}
						?>
							<button type="button" class="button wpmake_advance_user_avatar_upload hide-if-no-js"><?php echo esc_html( $wpmake_upload_label ); ?></button>
						<?php
					} else {
						?>
							<button type="button" class="button wpmake-advance-user-avatar-remove hide-if-no-js"><?php echo esc_html( $wpmake_remove_label ); ?></button>
							<button type="button" class="button wpmake_advance_user_avatar_upload hide-if-no-js" style="display:none"><?php echo esc_html( $wpmake_upload_label ); ?></button>
						<?php
					}

					do_action( 'wpmake_advance_user_avatar_after_upload_buttons', $wpmake_aua_atts );
					?>
				</p>
			</div>
		</div>
	</header>
	<?php do_action( 'wpmake_advance_user_avatar_after_uploader', $wpmake_aua_atts ); ?>
</div>
