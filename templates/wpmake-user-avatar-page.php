<?php
/**
 * WPMake User Avatar Display Layout
 *
 * Shows user lists in selected layout
 *
 * @package WPMakeUserAvatar/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="wpmake-user-avatar-container">
	<?php
		$gravatar_image      = get_avatar_url( get_current_user_id(), $args = null );
		$profile_picture_url = wp_get_attachment_url( get_user_meta( get_current_user_id(), 'wpmake_user_avatar_url', true ) );
		$image               = ( ! empty( $profile_picture_url ) ) ? $profile_picture_url : $gravatar_image;
	?>
		<img class="profile-preview" alt="profile-picture" src="<?php echo esc_url( $image ); ?>">
</div>
