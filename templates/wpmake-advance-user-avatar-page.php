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
 * @version 2.0.0
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

		// Match the size the container actually renders at, so the widget stops
		// pulling the full-size original for a 96px preview.
		$avatar_size         = ! empty( $wpmake_aua_atts['size'] ) ? absint( $wpmake_aua_atts['size'] ) : 96;
		$gravatar_image      = get_avatar_url( get_current_user_id(), array( 'size' => $avatar_size ) );
		$profile_picture_url = wpmake_aua_get_avatar_url( get_current_user_id(), $avatar_size );
		$image               = ( ! empty( $profile_picture_url ) ) ? $profile_picture_url : $gravatar_image;
	?>
		<img class="profile-preview" alt="profile-picture" src="<?php echo esc_url( $image ); ?>">
	<?php do_action( 'wpmake_advance_user_avatar_after_avatar', $wpmake_aua_atts ); ?>
</div>
