<?php
/**
 * WPMakeAdvanceUserAvatar Frontend.
 *
 * @class    Frontend
 * @version  1.0.0
 * @package  WPMakeAdvanceUserAvatar/Frontend
 */

namespace WPMake\WPMakeAdvanceUserAvatar\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend Class
 */
class Frontend {

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
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ), 10, 2 );
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 1.0.0
	 */
	public function load_scripts() {
		$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$options = get_option( 'WPMake_Advance_User_Avatar_settings', array() );

		// Enqueue frontend scripts here.
		wp_enqueue_script( 'wpmake-advance-user-avatar-frontend-script', WPMake_Advance_User_Avatar_ASSETS_URL . '/js/frontend/wpmake-advance-user-avatar-frontend' . $suffix . '.js', array( 'jquery' ), WPMake_Advance_User_Avatar_VERSION, false );
		wp_enqueue_script( 'wpmake-sweetalert2', WPMake_Advance_User_Avatar_ASSETS_URL . '/js/sweetalert2/sweetalert2.min.js', array( 'jquery' ), '10.16.7', false );
		wp_enqueue_script( 'wpmake-advance-user-avatar-jcrop-script', WPMake_Advance_User_Avatar_ASSETS_URL . '/js/jquery-Jcrop/jquery.Jcrop.min.js', array( 'jquery' ), WPMake_Advance_User_Avatar_VERSION, false );
		wp_enqueue_script( 'wpmake-advance-user-avatar-webcam-script', WPMake_Advance_User_Avatar_ASSETS_URL . '/js/webcam/webcam' . $suffix . '.js', array( 'jquery' ), WPMake_Advance_User_Avatar_VERSION );

		// Enqueue frontend styles here.
		wp_enqueue_style( 'wpmake-advance-user-avatar-frontend-style', WPMake_Advance_User_Avatar_ASSETS_URL . '/css/wpmake-advance-user-avatar-frontend.css', array(), WPMake_Advance_User_Avatar_VERSION );
		wp_enqueue_style( 'wpmake-sweetalert2', WPMake_Advance_User_Avatar_ASSETS_URL . '/css/sweetalert2/sweetalert2.min.css', array(), '10.16.7' );
		wp_enqueue_style( 'wpmake-advance-user-avatar-jcrop-style', WPMake_Advance_User_Avatar_ASSETS_URL . '/css/jquery.Jcrop.min.css', array(), 'WPMake_Advance_User_Avatar_VERSION' );

		wp_localize_script(
			'wpmake-advance-user-avatar-frontend-script',
			'WPMake_Advance_User_Avatar_params',
			array(
				'ajax_url'                                 => admin_url( 'admin-ajax.php' ),
				'WPMake_Advance_User_Avatar_upload_nonce'  => wp_create_nonce( 'WPMake_Advance_User_Avatar_upload_nonce' ),
				'WPMake_Advance_User_Avatar_remove_nonce'  => wp_create_nonce( 'WPMake_Advance_User_Avatar_remove_nonce' ),
				'WPMake_Advance_User_Avatar_uploading'     => esc_html__( 'Uploading...', 'wpmake-advance-user-avatar' ),
				'WPMake_Advance_User_Avatar_something_wrong' => esc_html__( 'Something wrong, please try again.', 'wpmake-advance-user-avatar' ),
				'WPMake_Advance_User_Avatar_crop_picture_title' => esc_html__( 'Crop Your Picture', 'wpmake-advance-user-avatar' ),
				'WPMake_Advance_User_Avatar_crop_picture_button' => esc_html__( 'Crop Picture', 'wpmake-advance-user-avatar' ),
				'WPMake_Advance_User_Avatar_capture'       => esc_html__( 'Capture', 'wpmake-advance-user-avatar' ),
				'WPMake_Advance_User_Avatar_ssl_error_title' => esc_html__( 'SSl Certificate Error', 'wpmake-advance-user-avatar' ),
				'WPMake_Advance_User_Avatar_ssl_error_text' => esc_html__( 'The site must be secure. Please enable https connection.', 'wpmake-advance-user-avatar' ),
				'WPMake_Advance_User_Avatar_permission_error_title' => esc_html__( 'Permission Error', 'wpmake-advance-user-avatar' ),
				'WPMake_Advance_User_Avatar_permission_error_text' => esc_html__( 'Please allow access to webcam.', 'wpmake-advance-user-avatar' ),
				'WPMake_Advance_User_Avatar_cancel_button' => esc_html__( 'Cancel', 'wpmake-advance-user-avatar' ),
				'WPMake_Advance_User_Avatar_cancel_button_confirmation' => esc_html__( 'OK', 'wpmake-advance-user-avatar' ),
				'WPMake_Advance_User_Avatar_enable_cropping_interface' => isset( $options['cropping_interface'] ) ? $options['cropping_interface'] : false,
			)
		);
	}
}
