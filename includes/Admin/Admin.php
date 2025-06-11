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
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
		add_action( 'admin_footer', 'wpmake_aua_print_js', 25 );
		add_action( 'admin_notices', array( $this, 'review_notice' ) );
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
		wp_enqueue_script( 'select2', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/js/select2/select2.min.js', array( 'jquery' ), '4.1.0', false );
		wp_enqueue_style( 'wpmake-advance-user-avatar-select2-style', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/css/select2/select2.css', array(), WPMAKE_ADVANCE_USER_AVATAR_VERSION );
		wp_enqueue_style( 'wpmake-advance-user-avatar-admin-style', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/css/wpmake-advance-user-avatar-admin.css', array(), WPMAKE_ADVANCE_USER_AVATAR_VERSION );

		wp_localize_script(
			'wpmake-advance-user-avatar-admin-script',
			'wpmake_aua_admin_params',
			array(
				'ajax_url'     => admin_url( 'admin-ajax.php' ),
				'notice_nonce' => wp_create_nonce( 'notice_nonce' ),
			)
		);
	}

	/**
	 * Add  menu item.
	 */
	public function wpmake_advance_user_avatar_menu() {
		$template_page = add_submenu_page(
			'users.php',
			esc_html__( 'Advanced Users Avatar', 'wpmake-advance-user-avatar' ),
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
		register_setting(
			'wpmake_advance_user_avatar_settings',
			'wpmake_advance_user_avatar_settings',
			array(
				'sanitize_callback' => array( $this, 'wpmake_advance_user_avatar_sanitize_settings' ),
			)
		);

		add_settings_section(
			'wpmake_advance_user_avatar_setting_section',
			esc_html__( 'Settings', 'wpmake-advance-user-avatar' ),
			'',
			'wpmake_advance_user_avatar_settings'
		);

		add_settings_field(
			'wpmake_advance_user_avatar_settings_thumbnail_size',
			wp_kses_post( __( 'Store avatar in different thumbnail sizes <span style="color:#0693e3;">( New )</span>', 'wpmake-advance-user-avatar' ) ),
			array( $this, 'wpmake_advance_user_avatar_setting_thumbnail_size_callback' ),
			'wpmake_advance_user_avatar_settings',
			'wpmake_advance_user_avatar_setting_section'
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

		add_settings_field(
			'wpmake_advance_user_avatar_settings_capture_picture',
			wp_kses_post( __( 'Capture Picture <span style="color:#0693e3;">( New )</span>', 'wpmake-advance-user-avatar' ) ),
			array( $this, 'wpmake_advance_user_avatar_settings_capture_picture_callback' ),
			'wpmake_advance_user_avatar_settings',
			'wpmake_advance_user_avatar_setting_section'
		);

		add_settings_field(
			'wpmake_advance_user_avatar_settings_cropping_interface',
			esc_html__( 'Cropping interface', 'wpmake-advance-user-avatar' ),
			array( $this, 'wpmake_advance_user_avatar_settings_cropping_interface_callback' ),
			'wpmake_advance_user_avatar_settings',
			'wpmake_advance_user_avatar_setting_section'
		);

		add_settings_field(
			'wpmake_advance_user_avatar_settings_uploaded_image_size',
			wp_kses_post( __( 'Uploaded Image Size <span style="color:#0693e3;">( New )</span>', 'wpmake-advance-user-avatar' ) ),
			array( $this, 'wpmake_advance_user_avatar_settings_uploaded_image_size_callback' ),
			'wpmake_advance_user_avatar_settings',
			'wpmake_advance_user_avatar_setting_section'
		);

		add_settings_field(
			'wpmake_advance_user_avatar_settings_woocommerce_integration',
			wp_kses_post( __( 'WooCommerce Integration <span style="color:#0693e3;">( New )</span>', 'wpmake-advance-user-avatar' ) ),
			array( $this, 'wpmake_advance_user_avatar_settings_woocommerce_integration_callback' ),
			'wpmake_advance_user_avatar_settings',
			'wpmake_advance_user_avatar_setting_section'
		);

		add_settings_field(
			'wpmake_advance_user_avatar_settings_buddypress_integration',
			wp_kses_post( __( 'BuddyPress Integration <span style="color:#0693e3;">( New )</span>', 'wpmake-advance-user-avatar' ) ),
			array( $this, 'wpmake_advance_user_avatar_settings_buddypress_integration_callback' ),
			'wpmake_advance_user_avatar_settings',
			'wpmake_advance_user_avatar_setting_section'
		);
	}

	/**
	 * Sanitize settings options before save.
	 *
	 * @param array $options Settings options.
	 */
	public function wpmake_advance_user_avatar_sanitize_settings( $options ) {

		$sanitized_option = array();

		if ( isset( $options['thumbnail_size'] ) ) {
			$sanitized_option['thumbnail_size'] = sanitize_text_field( $options['thumbnail_size'] );
		} else {
			$sanitized_option['thumbnail_size'] = false;
		}

		if ( isset( $options['uploaded_image_size'] ) ) {
			$sanitized_option['uploaded_image_size'] = array(
				'width'  => sanitize_text_field( $options['uploaded_image_size']['width'] ),
				'height' => sanitize_text_field( $options['uploaded_image_size']['height'] ),
			);
		} else {
			$sanitized_option['uploaded_image_size'] = array(
				'width'  => 500,
				'height' => 500,
			);
		}

		if ( isset( $options['max_size'] ) ) {
			$sanitized_option['max_size'] = absint( $options['max_size'] );
		}

		if ( isset( $options['allowed_file_type'] ) ) {
			foreach ( $options['allowed_file_type'] as $key => $value ) {
				$options['allowed_file_type'][ $key ] = sanitize_text_field( $value );
			}

			$sanitized_option['allowed_file_type'] = $options['allowed_file_type'];
		}

		if ( isset( $options['cropping_interface'] ) ) {
			$sanitized_option['cropping_interface'] = sanitize_text_field( $options['cropping_interface'] );
		}

		if ( isset( $options['thumbnail_size'] ) ) {
			$sanitized_option['thumbnail_size'] = sanitize_text_field( $options['thumbnail_size'] );
		}

		if ( isset( $options['capture_picture'] ) ) {
			$sanitized_option['capture_picture'] = sanitize_text_field( $options['capture_picture'] );
		}

		if ( isset( $options['woocommerce_integration'] ) ) {
			$sanitized_option['woocommerce_integration'] = sanitize_text_field( $options['woocommerce_integration'] );
		}

		if ( isset( $options['buddypress_integration'] ) ) {
			$sanitized_option['buddypress_integration'] = sanitize_text_field( $options['buddypress_integration'] );
			update_option( 'bp-disable-avatar-uploads', $sanitized_option['buddypress_integration']);
		} else {
			update_option( 'bp-disable-avatar-uploads', '');
		}

		return $sanitized_option;
	}

	/**
	 *  Store avatars different thumbnail sizes setting.
	 */
	public function wpmake_advance_user_avatar_setting_thumbnail_size_callback() {
		$options = get_option( 'wpmake_advance_user_avatar_settings' );

		$thumbnail_size = '';

		if ( isset( $options['thumbnail_size'] ) ) {
			$thumbnail_size = esc_html( $options['thumbnail_size'] );
		} else {
			$thumbnail_size = true;
		}

		ob_start();
		?>
			<input type="checkbox"  name="wpmake_advance_user_avatar_settings[thumbnail_size]" value="1" <?php echo checked( 1, $thumbnail_size, false ); ?> />
			<p class="wpmake-advance-user-avatar-setting-desc" ><?php esc_html_e( 'Stores avatar in different thumbnail sizes so that a perfect avatar will be displayed anywhere in your site.', 'wpmake-advance-user-avatar' ); ?></p>
		<?php
		$settings = ob_get_clean();
		echo wp_kses( $settings, wpmake_aua_get_allowed_html_tags() );
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
	 *  Uploaded Image Size setting.
	 */
	public function wpmake_advance_user_avatar_settings_uploaded_image_size_callback() {
		$options = get_option( 'wpmake_advance_user_avatar_settings', array() );

		$uploaded_image_size = array();

		if ( isset( $options['uploaded_image_size'] ) ) {
			$uploaded_image_size = $options['uploaded_image_size'];
		} else {
			$uploaded_image_size = array(
				'width'  => 500,
				'height' => 500,
			);
		}

		ob_start();
		?>
		<span>
			<input name="wpmake_advance_user_avatar_settings[uploaded_image_size][width]" type="text" value="<?php echo esc_attr( $uploaded_image_size['width'] ); ?>" class="wpmake-advance-user-avatar-setting-field" placeholder="" />
			by
			<input name="wpmake_advance_user_avatar_settings[uploaded_image_size][height]" type="text" value="<?php echo esc_attr( $uploaded_image_size['height'] ); ?>" class="wpmake-advance-user-avatar-setting-field" placeholder="" />
			px
		</span>
		<p class="wpmake-advance-user-avatar-setting-desc" ><?php esc_html_e( 'The size of the final uploaded image, defaults to 500 width by 500 height.', 'wpmake-advance-user-avatar' ); ?></p>
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
			<option value='image/gif' <?php echo esc_attr( selected( in_array( 'image/gif', $allowed_file_type ), true, false ) ); ?> ><?php esc_html_e( 'GIF', 'wpmake-advance-user-avatar' ); ?></option>
			<option value='image/png' <?php echo esc_attr( selected( in_array( 'image/png', $allowed_file_type ), true, false ) ); ?> ><?php esc_html_e( 'PNG', 'wpmake-advance-user-avatar' ); ?></option>
		</select>
		<p class="wpmake-advance-user-avatar-setting-desc" ><?php esc_html_e( 'Choose valid file types allowed for avatar upload.', 'wpmake-advance-user-avatar' ); ?></p>
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
			<p class="wpmake-advance-user-avatar-setting-desc" ><?php esc_html_e( 'Allow user to crop selected or captured image.', 'wpmake-advance-user-avatar' ); ?></p>
		<?php
		$settings = ob_get_clean();
		echo wp_kses( $settings, wpmake_aua_get_allowed_html_tags() );
	}

	/**
	 * Option to enable Woocommerce Integration.
	 *
	 * @param array $args Arguments.
	 */
	public function wpmake_advance_user_avatar_settings_woocommerce_integration_callback( $args ) {

		$options = get_option( 'wpmake_advance_user_avatar_settings' );

		$woocommerce_integration = '';
		if ( isset( $options['woocommerce_integration'] ) ) {
			$woocommerce_integration = esc_html( $options['woocommerce_integration'] );
		}

		ob_start();
		?>
			<input type="checkbox"  name="wpmake_advance_user_avatar_settings[woocommerce_integration]" value="1" <?php echo checked( 1, $woocommerce_integration, false ); ?> />
			<p class="wpmake-advance-user-avatar-setting-desc" ><?php esc_html_e( 'Display user avatar in dashboard and uploader in account details of WooCommerce My Account.', 'wpmake-advance-user-avatar' ); ?></p>
		<?php
		$settings = ob_get_clean();
		echo wp_kses( $settings, wpmake_aua_get_allowed_html_tags() );
	}

	/**
	 * Option to enable BuddyPress Integration.
	 *
	 * @param array $args Arguments.
	 */
	public function wpmake_advance_user_avatar_settings_buddypress_integration_callback( $args ) {

		$options = get_option( 'wpmake_advance_user_avatar_settings' );

		$buddypress_integration = '';
		if ( isset( $options['buddypress_integration'] ) ) {
			$buddypress_integration = esc_html( $options['buddypress_integration'] );
		}

		ob_start();
		?>
			<input type="checkbox"  name="wpmake_advance_user_avatar_settings[buddypress_integration]" value="1" <?php echo checked( 1, $buddypress_integration, false ); ?> />
			<p class="wpmake-advance-user-avatar-setting-desc" ><?php esc_html_e( 'Display user avatar in BuddyPress avatar areas and uploader in Change Avatar section of BuddyPress Profile section.', 'wpmake-advance-user-avatar' ); ?></p>
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
		<p class="wpmake-advance-user-avatar-setting-desc" ><?php esc_html_e( 'This option will enable taking picture using webcam. Note that your site must have valid SSL enabled.', 'wpmake-advance-user-avatar' ); ?></p>
		<?php
		$settings = ob_get_clean();
		echo wp_kses( $settings, wpmake_aua_get_allowed_html_tags() );
	}

	/**
	 * Change the admin footer text on setting page.
	 *
	 * @since  1.0.2
	 *
	 * @param  string $footer_text Advanced User Avatar Plugin footer text.
	 *
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		if ( ! isset( $_GET['page'] ) || ( isset( $_GET['page'] ) && 'wpmake-advance-user-avatar' !== $_GET['page'] ) ) {
			return $footer_text;
		}

		/**
		 * Filter to display admin footer text
		 *
		 * @param boolean Whether current screen is a settings page of the plugin
		 */
		if ( 'wpmake-advance-user-avatar' === $_GET['page'] ) {
			// Change the footer text.
			if ( ! get_option( 'wpmake_advance_user_avatar_admin_footer_text_rated' ) ) {
				$footer_text = wp_kses_post(
					sprintf(
						/* translators: 1: Advanced User Avatar 2:: five stars */
						__( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', 'wpmake-advance-user-avatar' ),
						sprintf( '<strong>%s</strong>', esc_html( 'Advanced User Avatar' ) ),
						'<a href="https://wordpress.org/support/plugin/wpmake-advance-user-avatar/reviews?rate=5#new-post" rel="noreferrer noopener" target="_blank" class="wpmake-aua-rating-link" data-rated="' . esc_attr__( 'Thank You!', 'wpmake-advance-user-avatar' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
					)
				);
				wpmake_aua_enqueue_js(
					"
				jQuery( 'a.wpmake-aua-rating-link' ).on('click', function() {
						jQuery.post( '" . admin_url( 'admin-ajax.php', 'relative' ) . "', { action: 'wpmake_advance_user_avatar_upload_rated' } );
						jQuery( this ).parent().text( jQuery( this ).data( 'rated' ) );
					});
				"
				);
			} else {
				$footer_text = esc_html__( 'Thank you for using Advanced User Avatar.', 'wpmake-advance-user-avatar' );
			}
		}

		return $footer_text;
	}

	/**
	 * Review notice on header.
	 *
	 * @since  1.0.2
	 * @return void
	 */
	public function review_notice() {

		// Show only to Admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$notice_dismissed = get_option( 'wpmake_aua_review_notice_dismissed', false );

		if ( $notice_dismissed ) {
			return;
		}

		// Return if activation date is less than 1 day.
		if ( wpmake_aua_check_activation_date( '1' ) === false ) {
			return;
		}

		$notice_target_link = 'https://wordpress.org/support/plugin/wpmake-advance-user-avatar/reviews/#postform';
		$notice_content     = review_notice_content();

		ob_start();
		?>
		<div id="wpmake-aua-review-notice" class="notice notice-info wpmake-aua-notice" data-purpose="notice-info" data-notice-id="review">
			<div class="wpmake-aua-notice-thumbnail">
				<img src="<?php echo esc_url( WPMAKE_ADVANCE_USER_AVATAR_URL . '/assets/images/icon.png' ); ?>" alt="">
			</div>
			<div class="wpmake-aua-notice-text">

				<div class="wpmake-aua-notice-body">
					<?php
					echo wp_kses_post( $notice_content );
					?>
				</div>
				<div class="wpmake-aua-notice-links">
					<ul class="wpmake-aua-notice-ul">
						<li><a class="button button-primary notice-link-visit" href="<?php echo esc_url( $notice_target_link ); ?>" target="_blank"><span class="dashicons dashicons-external"></span><?php esc_html_e( 'Sure, I\'d love to!', 'wpmake-advance-user-avatar' ); ?></a></li>
						<li><a href="#" class="button button-secondary notice-dismiss notice-dismiss-permanently"><span  class="dashicons dashicons-smiley"></span><?php esc_html_e( 'I already did!', 'wpmake-advance-user-avatar' ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
		<?php

		$notice = ob_get_clean();

		echo $notice;
	}
}
