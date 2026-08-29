<?php
/**
 * WPMake User Avatar Display Layout
 *
 * Shows user lists in selected layout
 *
 * This template can be overridden by copying it to
 * yourtheme/wpmake-advance-user-avatar/wpmake-advance-user-avatar-page.php
 *
 * @package WPMakeAdvanceUserAvatar/Templates
 * @version 1.2.3
 */

use WPMake\WPMakeAdvanceUserAvatar\Admin\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wpmake_aua_atts  = isset( $wpmake_aua_atts ) ? $wpmake_aua_atts : Shortcodes::default_avatar_atts();
$wpmake_aua_style = Shortcodes::container_style( $wpmake_aua_atts );
?>
<div class="<?php echo esc_attr( Shortcodes::container_class( $wpmake_aua_atts ) ); ?>"<?php echo $wpmake_aua_style ? ' style="' . esc_attr( $wpmake_aua_style ) . '"' : ''; ?>>
	<?php
		do_action( 'wpmake_advance_user_avatar_before_avatar', $wpmake_aua_atts );

		$gravatar_image      = get_avatar_url( get_current_user_id(), null );
		$profile_picture_url = wp_get_attachment_url( get_user_meta( get_current_user_id(), 'wpmake_advance_user_avatar_attachment_id', true ) );
		$image               = ( ! empty( $profile_picture_url ) ) ? $profile_picture_url : $gravatar_image;
	?>
		<img class="profile-preview" alt="profile-picture" src="<?php echo esc_url( $image ); ?>">
	<?php do_action( 'wpmake_advance_user_avatar_after_avatar', $wpmake_aua_atts ); ?>
</div>
