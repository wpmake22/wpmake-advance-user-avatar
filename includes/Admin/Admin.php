<?php
/**
 * WPMakeAdvanceUserAvatar Admin.
 *
 * @class    Admin
 * @version  1.0.0
 * @package  WPMakeAdvanceUserAvatar/Admin
 */

namespace WPMake\WPMakeAdvanceUserAvatar\Admin;

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
		add_action( 'admin_menu', array( $this, 'WPMake_Advance_User_Avatar_menu' ), 68 );
		add_action( 'admin_init', array( $this, 'WPMake_Advance_User_Avatar_setting' ) );

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'wpmake-advance-user-avatar-admin-script', WPMake_Advance_User_Avatar_ASSETS_URL . '/js/admin/wpmake-advance-user-avatar-admin' . $suffix . '.js', array( 'jquery' ), WPMake_Advance_User_Avatar_VERSION, false );
		wp_enqueue_script( 'select2', WPMake_Advance_User_Avatar_ASSETS_URL . '/js/select2/select2.min.js', array( 'jquery' ), '10.16.7', false );
		wp_enqueue_style( 'wpmake-advance-user-avatar-select2-style', WPMake_Advance_User_Avatar_ASSETS_URL . '/css/select2/select2.css', array(), WPMake_Advance_User_Avatar_VERSION );
		wp_enqueue_style( 'wpmake-advance-user-avatar-admin-style', WPMake_Advance_User_Avatar_ASSETS_URL . '/css/wpmake-advance-user-avatar-admin.css', array(), WPMake_Advance_User_Avatar_VERSION );
	}

	/**
	 * Add  menu item.
	 */
	public function WPMake_Advance_User_Avatar_menu() {
		$template_page = add_submenu_page(
			'users.php',
			esc_html__( 'WPMake Users Avatar', 'wpmake-advance-user-avatar' ),
			esc_html__( 'Users Avatar', 'wpmake-advance-user-avatar' ),
			'manage_options',
			'wpmake-advance-user-avatar',
			array(
				$this,
				'WPMake_Advance_User_Avatar_settings_page',
			)
		);
	}

	/**
	 *  Init the User Avatar Settings page.
	 */
	public function WPMake_Advance_User_Avatar_settings_page() {
		?>
		<form action='options.php' method='post'>
			<table class="form-table">
				<tbody>
					<?php
					settings_fields( 'WPMake_Advance_User_Avatar_settings' );
					do_settings_sections( 'WPMake_Advance_User_Avatar_settings' );
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
	public function WPMake_Advance_User_Avatar_setting() {
		register_setting( 'WPMake_Advance_User_Avatar_settings', 'WPMake_Advance_User_Avatar_settings' );

		add_settings_section(
			'WPMake_Advance_User_Avatar_setting_section',
			esc_html__( 'Settings', 'wpmake-advance-user-avatar' ),
			'',
			'WPMake_Advance_User_Avatar_settings'
		);

		add_settings_field(
			'WPMake_Advance_User_Avatar_settings_max_size',
			esc_html__( 'Max Avatar Size Allowed', 'wpmake-advance-user-avatar' ),
			array( $this, 'WPMake_Advance_User_Avatar_setting_max_size_callback' ),
			'WPMake_Advance_User_Avatar_settings',
			'WPMake_Advance_User_Avatar_setting_section'
		);

		add_settings_field(
			'WPMake_Advance_User_Avatar_settings_allowed_file_type',
			esc_html__( 'Allowed File Type', 'wpmake-advance-user-avatar' ),
			array( $this, 'WPMake_Advance_User_Avatar_settings_allowed_file_type_callback' ),
			'WPMake_Advance_User_Avatar_settings',
			'WPMake_Advance_User_Avatar_setting_section'
		);

		add_settings_field(
			'WPMake_Advance_User_Avatar_settings_capture_picture',
			esc_html__( 'Capture Picture', 'wpmake-advance-user-avatar' ),
			array( $this, 'WPMake_Advance_User_Avatar_settings_capture_picture_callback' ),
			'WPMake_Advance_User_Avatar_settings',
			'WPMake_Advance_User_Avatar_setting_section'
		);

		add_settings_field(
			'WPMake_Advance_User_Avatar_settings_cropping_interface',
			esc_html__( 'Cropping interface', 'wpmake-advance-user-avatar' ),
			array( $this, 'WPMake_Advance_User_Avatar_settings_cropping_interface_callback' ),
			'WPMake_Advance_User_Avatar_settings',
			'WPMake_Advance_User_Avatar_setting_section'
		);
	}

	/**
	 *  Max avatar size setting.
	 */
	public function WPMake_Advance_User_Avatar_setting_max_size_callback() {
		$options = get_option( 'WPMake_Advance_User_Avatar_settings', array() );

		$max_size = '1024';
		if ( isset( $options['max_size'] ) ) {
			$max_size = esc_html( $options['max_size'] );
		}

		echo '<input name="WPMake_Advance_User_Avatar_settings[max_size]" type="text" value="' . esc_attr( $max_size ) . '" class="wpmake-advance-user-avatar-setting-field" placeholder="" />';
		echo '<p class="wpmake-advance-user-avatar-setting-desc" >' . esc_html__( 'Maximum avatar size allowed for upload. Enter file size in Kb. Leave the field empty for upload without restriction', 'wpmake-advance-user-avatar' ) . '</p>';
	}

	/**
	 *  Allowed file type setting.
	 */
	public function WPMake_Advance_User_Avatar_settings_allowed_file_type_callback() {
		$options           = get_option( 'WPMake_Advance_User_Avatar_settings', array() );
		$allowed_file_type = array();
		if ( isset( $options['allowed_file_type'] ) ) {
			$allowed_file_type = $options['allowed_file_type'];
		}

		echo "<select class='wpmake-advance-user-avatar-enhanced-select wpmake-advance-user-avatar-setting-field' name='WPMake_Advance_User_Avatar_settings[allowed_file_type][]' multiple='multiple' >
		<option value='image/jpg' " . esc_attr( selected( in_array( 'image/jpg', $allowed_file_type ), true, false ) ) . '>' . esc_html__( 'JPG', 'wpmake-advance-user-avatar' ) . "</option>
		<option value='image/jpeg' " . esc_attr( selected( in_array( 'image/jpeg', $allowed_file_type ), true, false ) ) . '>' . esc_html__( 'JPEG', 'wpmake-advance-user-avatar' ) . "</option>
		<option value='image/gif' " . esc_attr( selected( in_array( 'image/gif', $allowed_file_type ), true, false ) ) . '>' . esc_html__( 'GIF', 'wpmake-advance-user-avatar' ) . "</option>
		<option value='image/png' " . esc_attr( selected( in_array( 'image/png', $allowed_file_type ), true, false ) ) . '>' . esc_html__( 'PNG', 'wpmake-advance-user-avatar' ) . '</option>
				</select>';
		echo '<p class="wpmake-advance-user-avatar-setting-desc" >' . esc_html__( 'Choose valid file types allowed for avatar upload', 'wpmake-advance-user-avatar' ) . '</p>';
	}

	/**
	 *  Cropping interface.
	 */
	public function WPMake_Advance_User_Avatar_settings_cropping_interface_callback( $args ) {

		$options = get_option( 'WPMake_Advance_User_Avatar_settings' );

		$cropping_interface = '';
		if ( isset( $options['cropping_interface'] ) ) {
			$cropping_interface = esc_html( $options['cropping_interface'] );
		}

		echo '<input type="checkbox"  name="WPMake_Advance_User_Avatar_settings[cropping_interface]" value="1"' . checked( 1, $cropping_interface, false ) . '/>';
		echo '<p class="wpmake-advance-user-avatar-setting-desc" >' . esc_html__( 'This option will enable avatar cropping interface', 'wpmake-advance-user-avatar' ) . '</p>';
	}

	/**
	 *  Capture picture.
	 */
	public function WPMake_Advance_User_Avatar_settings_capture_picture_callback( $args ) {

		$options = get_option( 'WPMake_Advance_User_Avatar_settings' );

		$capture_picture = '';
		if ( isset( $options['capture_picture'] ) ) {
			$capture_picture = esc_html( $options['capture_picture'] );
		}

		echo '<input type="checkbox"  name="WPMake_Advance_User_Avatar_settings[capture_picture]" value="1"' . checked( 1, $capture_picture, false ) . '/>';
		echo '<p class="wpmake-advance-user-avatar-setting-desc" >' . esc_html__( 'This option will enable taking picture using webcam. Note that your site must be secure', 'wpmake-advance-user-avatar' ) . '</p>';
	}
}
