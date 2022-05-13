<?php
/**
 * WPMakeUserAvatar Admin.
 *
 * @class    Admin
 * @version  1.0.0
 * @package  WPMakeUserAvatar/Admin
 */

namespace WPMake\WPMakeUserAvatar\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Class
 */
class Admin {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'wpmake_user_avatar_menu' ), 68 );
		add_action( 'admin_init', array( $this, 'wpmake_user_avatar_setting' ) );

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'wpmake-user-avatar-admin-script', WPMAKE_USER_AVATAR_ASSETS_URL . '/js/admin/wpmake-user-avatar-admin' . $suffix . '.js', array( 'jquery' ), WPMAKE_USER_AVATAR_VERSION, false );
		wp_enqueue_script( 'select2', WPMAKE_USER_AVATAR_ASSETS_URL . '/js/select2/select2.min.js', array( 'jquery' ), '10.16.7', false );
		wp_enqueue_style( 'wpmake-user-avatar-select2-style', WPMAKE_USER_AVATAR_ASSETS_URL . '/css/select2/select2.css', array(), WPMAKE_USER_AVATAR_VERSION );
	}

	/**
	 * Add  menu item.
	 */
	public function wpmake_user_avatar_menu() {
		$template_page = add_submenu_page(
			'users.php',
			__( 'WPMake Users Avatar', 'wpmake-user-avatar' ),
			__( 'Users Avatar', 'wpmake-user-avatar' ),
			'manage_user_registration',
			'wpmake-user-avatar',
			array(
				$this,
				'wpmake_user_avatar_settings_page',
			)
		);

	}

	/**
	 *  Init the User Avatar Settings page.
	 */
	public function wpmake_user_avatar_settings_page() {
		?>
		<form action='options.php' method='post'>
			<table class="form-table">
				<tbody>
					<?php
					settings_fields( 'wpmake_user_avatar_settings' );
					do_settings_sections( 'wpmake_user_avatar_settings' );
					submit_button();
					?>
				</tbody>
			</table>
		</form>
		<?php

	}

	/**
	 *  Init the User Avatar Settings.
	 */
	public function wpmake_user_avatar_setting() {
		register_setting( 'wpmake_user_avatar_settings', 'wpmake_user_avatar_settings' );

		add_settings_section(
			'wpmake_user_avatar_setting_section',
			__( 'Settings', 'wpmake-user-avatar' ),
			'',
			'wpmake_user_avatar_settings'
		);

		add_settings_field(
			'wpmake_user_avatar_settings_max_size',
			__( 'Max Avatar Size Allowed', 'wpmake-user-avatar' ),
			array( $this, 'wpmake_user_avatar_setting_max_size_callback' ),
			'wpmake_user_avatar_settings',
			'wpmake_user_avatar_setting_section'
		);

		add_settings_field(
			'wpmake_user_avatar_settings_allowed_file_type',
			__( 'Allowed File Type', 'wpmake-user-avatar' ),
			array( $this, 'wpmake_user_avatar_settings_allowed_file_type_callback' ),
			'wpmake_user_avatar_settings',
			'wpmake_user_avatar_setting_section'
		);

		add_settings_field(
			'wpmake_user_avatar_settings_capture_picture',
			__( 'Capture Picture', 'wpmake-user-avatar' ),
			array( $this, 'wpmake_user_avatar_settings_capture_picture_callback' ),
			'wpmake_user_avatar_settings',
			'wpmake_user_avatar_setting_section'
		);

		add_settings_field(
			'wpmake_user_avatar_settings_cropping_interface',
			__( 'Cropping interface', 'wpmake-user-avatar' ),
			array( $this, 'wpmake_user_avatar_settings_cropping_interface_callback' ),
			'wpmake_user_avatar_settings',
			'wpmake_user_avatar_setting_section'
		);
	}

	/**
	 *  Max avatar size setting.
	 */
	public function wpmake_user_avatar_setting_max_size_callback() {
		$options  = get_option( 'wpmake_user_avatar_settings' );

		$max_size = '1024';
		if ( isset( $options['max_size'] ) ) {
			$max_size = esc_html( $options['max_size'] );
		}

		echo '<input name="wpmake_user_avatar_settings[max_size]" type="text" value="' . esc_attr( $max_size ) . '" class="" placeholder="" style="min-width: 350px;"/>';
		echo '<p style="font-style: italic;">' . esc_html__( 'Maximum avatar size allowed for upload. Enter file size in Kb. Leave the field empty for upload without restriction', 'wpmake-user-avatar' ) . '</p>';
	}

	/**
	 *  Allowed file type setting.
	 */
	public function wpmake_user_avatar_settings_allowed_file_type_callback() {
		$options  = get_option( 'wpmake_user_avatar_settings' );
		$allowed_file_type = '';
		if ( isset( $options['allowed_file_type'] ) ) {
			$allowed_file_type = $options['allowed_file_type'];
		}

		echo "<select class='wpmake-user-avatar-enhanced-select' name='wpmake_user_avatar_settings[allowed_file_type][]' multiple='multiple' style='min-width: 350px;'>
		<option value='image/jpg' " . esc_attr( selected( in_array( 'image/jpg', $allowed_file_type ), true, false ) ) . '>' . esc_html__( 'JPG', 'wpmake-user-avatar' ) . "</option>
		<option value='image/jpeg' " . esc_attr( selected( in_array( 'image/jpeg', $allowed_file_type ), true, false ) ) . '>' . esc_html__( 'JPEG', 'wpmake-user-avatar' ) . "</option>
		<option value='image/gif' " . esc_attr( selected( in_array( 'image/gif', $allowed_file_type ), true, false ) ) . '>' . esc_html__( 'GIF', 'wpmake-user-avatar' ) . "</option>
		<option value='image/png' " . esc_attr( selected( in_array( 'image/png', $allowed_file_type ), true, false ) ) . '>' . esc_html__( 'PNG', 'wpmake-user-avatar' ) . '</option>
				</select>';
		echo '<p style="font-style: italic;">' . esc_html__( 'Choose valid file types allowed for avatar upload', 'wpmake-user-avatar' ) . '</p>';
	}

	/**
	 *  Cropping interface.
	 */
	public function wpmake_user_avatar_settings_cropping_interface_callback( $args ) {

		$options = get_option( 'wpmake_user_avatar_settings' );

		$cropping_interface = '';
		if ( isset( $options['cropping_interface'] ) ) {
			$cropping_interface = esc_html( $options['cropping_interface'] );
		}

		echo '<input type="checkbox"  name="wpmake_user_avatar_settings[cropping_interface]" value="1"' . checked( 1, $cropping_interface, false ) . '/>';
		echo '<p style="font-style: italic;">' . esc_html__( 'This option will enable avatar cropping interface', 'wpmake-user-avatar' ) . '</p>';
	}

	/**
	 *  Capture picture.
	 */
	public function wpmake_user_avatar_settings_capture_picture_callback( $args ) {

		$options = get_option( 'wpmake_user_avatar_settings' );

		$capture_picture = '';
		if ( isset( $options['capture_picture'] ) ) {
			$capture_picture = esc_html( $options['capture_picture'] );
		}

		echo '<input type="checkbox"  name="wpmake_user_avatar_settings[capture_picture]" value="1"' . checked( 1, $capture_picture, false ) . '/>';
		echo '<p style="font-style: italic;">' . esc_html__( 'This option will enable taking picture using webcam. Note that your site must be secure', 'wpmake-user-avatar' ) . '</p>';
	}
}
