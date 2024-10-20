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
		add_action( 'admin_menu', array( $this, 'wpmake_advance_user_avatar_menu' ), 68 );
		add_action( 'admin_init', array( $this, 'wpmake_advance_user_avatar_setting' ) );

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'wpmake-advance-user-avatar-admin-script', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/js/admin/wpmake-advance-user-avatar-admin' . $suffix . '.js', array( 'jquery' ), WPMAKE_ADVANCE_USER_AVATAR_VERSION, false );
		wp_enqueue_script( 'select2', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/js/select2/select2.min.js', array( 'jquery' ), '10.16.7', false );
		wp_enqueue_style( 'wpmake-advance-user-avatar-select2-style', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/css/select2/select2.css', array(), WPMAKE_ADVANCE_USER_AVATAR_VERSION );
		wp_enqueue_style( 'wpmake-advance-user-avatar-admin-style', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/css/wpmake-advance-user-avatar-admin.css', array(), WPMAKE_ADVANCE_USER_AVATAR_VERSION );
	}

	/**
	 * Add  menu item.
	 */
	public function wpmake_advance_user_avatar_menu() {
		$template_page = add_submenu_page(
			'users.php',
			esc_html__( 'Advance Users Avatar', 'wpmake-advance-user-avatar' ),
			esc_html__( 'Users Avatar', 'wpmake-advance-user-avatar' ),
			'manage_options',
			'wpmake-advance-user-avatar',
			array(
				$this,
				'wpmake_advance_user_avatar_settings_page',
			)
		);
	}

	/**
	 *  Init the User Avatar Settings page.
	 */
	public function wpmake_advance_user_avatar_settings_page() {
		?>
		<form action='options.php' method='post'>
			<table class="form-table">
				<tbody>
					<?php
					settings_fields( 'wpmake_advance_user_avatar_settings' );
					do_settings_sections( 'wpmake_advance_user_avatar_settings' );
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
	public function wpmake_advance_user_avatar_setting() {
		register_setting( 'wpmake_advance_user_avatar_settings', 'wpmake_advance_user_avatar_settings' );

		add_settings_section(
			'wpmake_advance_user_avatar_setting_section',
			esc_html__( 'Settings', 'wpmake-advance-user-avatar' ),
			'',
			'wpmake_advance_user_avatar_settings'
		);

		add_settings_field(
			'wpmake_advance_user_avatar_settings_max_size',
			esc_html__( 'Max Avatar Size Allowed', 'wpmake-advance-user-avatar' ),
			array( $this, 'wpmake_advance_user_avatar_setting_max_size_callback' ),
			'wpmake_advance_user_avatar_settings',
			'wpmake_advance_user_avatar_setting_section'
		);

		add_settings_field(
			'wpmake_advance_user_avatar_settings_allowed_file_type',
			esc_html__( 'Allowed File Type', 'wpmake-advance-user-avatar' ),
			array( $this, 'wpmake_advance_user_avatar_settings_allowed_file_type_callback' ),
			'wpmake_advance_user_avatar_settings',
			'wpmake_advance_user_avatar_setting_section'
		);

		if ( aua_fs()->can_use_premium_code__premium_only() ) {
			add_settings_field(
				'wpmake_advance_user_avatar_settings_capture_picture',
				esc_html__( 'Capture Picture', 'wpmake-advance-user-avatar' ),
				array( $this, 'wpmake_advance_user_avatar_settings_capture_picture_callback' ),
				'wpmake_advance_user_avatar_settings',
				'wpmake_advance_user_avatar_setting_section'
			);
		}

		add_settings_field(
			'wpmake_advance_user_avatar_settings_cropping_interface',
			esc_html__( 'Cropping interface', 'wpmake-advance-user-avatar' ),
			array( $this, 'wpmake_advance_user_avatar_settings_cropping_interface_callback' ),
			'wpmake_advance_user_avatar_settings',
			'wpmake_advance_user_avatar_setting_section'
		);
	}

		/**
		 *  Max avatar size setting.
		 */
	public function wpmake_advance_user_avatar_setting_max_size_callback() {
		$options = get_option( 'wpmake_advance_user_avatar_settings', array() );

		$max_size = '1024';
		if ( isset( $options['max_size'] ) ) {
			$max_size = esc_html( $options['max_size'] );
		}

		ob_start();
		?>
		<input name="wpmake_advance_user_avatar_settings[max_size]" type="text" value="<?php echo esc_attr( $max_size ); ?>" class="wpmake-advance-user-avatar-setting-field" placeholder="" />
		<p class="wpmake-advance-user-avatar-setting-desc" ><?php esc_html_e( 'Maximum avatar size allowed for upload. Enter file size in Kb. Leave the field empty for upload without restriction', 'wpmake-advance-user-avatar' ); ?></p>
		<?php
		$settings = ob_get_clean();
		echo wp_kses( $settings, wpmake_aua_get_allowed_html_tags() );
	}

		/**
		 *  Allowed file type setting.
		 */
	public function wpmake_advance_user_avatar_settings_allowed_file_type_callback() {
		$options           = get_option( 'wpmake_advance_user_avatar_settings', array() );
		$allowed_file_type = array();
		if ( isset( $options['allowed_file_type'] ) ) {
			$allowed_file_type = $options['allowed_file_type'];
		}

		ob_start();
		?>
		<select class='wpmake-advance-user-avatar-enhanced-select wpmake-advance-user-avatar-setting-field' name='wpmake_advance_user_avatar_settings[allowed_file_type][]' multiple='multiple' >
			<option value='image/jpg' <?php echo esc_attr( selected( in_array( 'image/jpg', $allowed_file_type ), true, false ) ); ?> ><?php esc_html_e( 'JPG', 'wpmake-advance-user-avatar' ); ?></option>
			<option value='image/jpeg' <?php echo esc_attr( selected( in_array( 'image/jpeg', $allowed_file_type ), true, false ) ); ?> ><?php esc_html_e( 'JPEG', 'wpmake-advance-user-avatar' ); ?></option>
			<?php
			if ( aua_fs()->can_use_premium_code__premium_only() ) {
				?>
				<option value='image/gif' <?php echo esc_attr( selected( in_array( 'image/gif', $allowed_file_type ), true, false ) ); ?> ><?php esc_html_e( 'GIF', 'wpmake-advance-user-avatar' ); ?></option>
				<option value='image/png' <?php echo esc_attr( selected( in_array( 'image/png', $allowed_file_type ), true, false ) ); ?> ><?php esc_html_e( 'PNG', 'wpmake-advance-user-avatar' ); ?></option>
				<?php
			}
			?>
		</select>
		<p class="wpmake-advance-user-avatar-setting-desc" ><?php esc_html_e( 'Choose valid file types allowed for avatar upload', 'wpmake-advance-user-avatar' ); ?></p>
		<?php
		$settings = ob_get_clean();
		echo wp_kses( $settings, wpmake_aua_get_allowed_html_tags() );
	}

	/**
	 * Cropping interface.
	 *
	 * @param array $args Arguments.
	 */
	public function wpmake_advance_user_avatar_settings_cropping_interface_callback( $args ) {

		$options = get_option( 'wpmake_advance_user_avatar_settings' );

		$cropping_interface = '';
		if ( isset( $options['cropping_interface'] ) ) {
			$cropping_interface = esc_html( $options['cropping_interface'] );
		}

		ob_start();
		?>
			<input type="checkbox"  name="wpmake_advance_user_avatar_settings[cropping_interface]" value="1" <?php echo checked( 1, $cropping_interface, false ); ?> />
			<p class="wpmake-advance-user-avatar-setting-desc" ><?php esc_html_e( 'This option will enable avatar cropping interface', 'wpmake-advance-user-avatar' ); ?></p>
		<?php
		$settings = ob_get_clean();
		echo wp_kses( $settings, wpmake_aua_get_allowed_html_tags() );
	}

	/**
	 *  Capture picture.
	 *
	 * @param array $args Arguments.
	 */
	public function wpmake_advance_user_avatar_settings_capture_picture_callback( $args ) {

		$options = get_option( 'wpmake_advance_user_avatar_settings' );

		$capture_picture = '';
		if ( isset( $options['capture_picture'] ) ) {
			$capture_picture = esc_html( $options['capture_picture'] );
		}

		ob_start();
		?>
		<input type="checkbox"  name="wpmake_advance_user_avatar_settings[capture_picture]" value="1" <?php echo checked( 1, $capture_picture, false ); ?> />
		<p class="wpmake-advance-user-avatar-setting-desc" ><?php esc_html_e( 'This option will enable taking picture using webcam. Note that your site must be secure', 'wpmake-advance-user-avatar' ); ?></p>
		<?php
		$settings = ob_get_clean();
		echo wp_kses( $settings, wpmake_aua_get_allowed_html_tags() );
	}
}
