<?php
/**
 * WPMakeUserAvatar Frontend.
 *
 * @class    Frontend
 * @version  1.0.0
 * @package  WPMakeUserAvatar/Frontend
 */

namespace  WPMake\WPMakeUserAvatar\Frontend;

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
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$options  = get_option( 'wpmake_user_avatar_settings' );

		// Enqueue frontend scripts here.
		wp_enqueue_script( 'wpmake-user-avatar-frontend-script', WPMAKE_USER_AVATAR_ASSETS_URL . '/js/frontend/wpmake-user-avatar-frontend' . $suffix . '.js', array( 'jquery' ), WPMAKE_USER_AVATAR_VERSION, false );
		wp_enqueue_script( 'sweetalert2', WPMAKE_USER_AVATAR_ASSETS_URL . '/js/sweetalert2/sweetalert2.min.js', array( 'jquery' ), '10.16.7', false );
		wp_enqueue_script( 'wpmake-user-avatar-jcrop-script', WPMAKE_USER_AVATAR_ASSETS_URL . '/js/jquery-Jcrop/jquery.Jcrop.min.js', array( 'jquery' ), WPMAKE_USER_AVATAR_VERSION, false );
		wp_enqueue_script( 'wpmake-user-avatar-webcam-script', WPMAKE_USER_AVATAR_ASSETS_URL . '/js/webcam/webcam' . $suffix . '.js', array( 'jquery' ), WPMAKE_USER_AVATAR_VERSION );

		// Enqueue frontend styles here.
		wp_enqueue_style( 'wpmake-user-avatar-frontend-style', WPMAKE_USER_AVATAR_ASSETS_URL . '/css/wpmake-user-avatar-frontend.css', array(), WPMAKE_USER_AVATAR_VERSION );
		wp_enqueue_style( 'sweetalert2', WPMAKE_USER_AVATAR_ASSETS_URL . '/css/sweetalert2/sweetalert2.min.css', array(), '10.16.7' );
		wp_enqueue_style( 'wpmake-user-avatar-jcrop-style', WPMAKE_USER_AVATAR_ASSETS_URL . '/css/jquery.Jcrop.min.css', array(), 'WPMAKE_USER_AVATAR_VERSION' );

		wp_localize_script(
			'wpmake-user-avatar-frontend-script',
			'wpmake_user_avatar_params',
			array(
				'ajax_url'                                 => admin_url( 'admin-ajax.php' ),
				'wpmake_user_avatar_upload_nonce'          => wp_create_nonce( 'wpmake_user_avatar_upload_nonce' ),
				'wpmake_user_avatar_remove_nonce'          => wp_create_nonce( 'wpmake_user_avatar_remove_nonce' ),
				'wpmake_user_avatar_uploading'             => __( 'Uploading...', 'wpmake-user-avatar' ),
				'wpmake_user_avatar_something_wrong'       => __( 'Something wrong, please try again.', 'wpmake-user-avatar' ),
				'wpmake_user_avatar_crop_picture_title'    => esc_html__( 'Crop Your Picture', 'wpmake-user-avatar' ),
				'wpmake_user_avatar_crop_picture_button'   => esc_html__( 'Crop Picture', 'wpmake-user-avatar' ),
				'wpmake_user_avatar_capture'               => esc_html__( 'Capture', 'wpmake-user-avatar' ),
				'wpmake_user_avatar_ssl_error_title'       => esc_html__( 'SSl Certificate Error', 'wpmake-user-avatar' ),
				'wpmake_user_avatar_ssl_error_text'        => esc_html__( 'The site must be secure. Please enable https connection.', 'wpmake-user-avatar' ),
				'wpmake_user_avatar_permission_error_title' => esc_html__( 'Permission Error', 'wpmake-user-avatar' ),
				'wpmake_user_avatar_permission_error_text' => esc_html__( 'Please allow access to webcam.', 'wpmake-user-avatar' ),
				'wpmake_user_avatar_cancel_button'         => esc_html__( 'Cancel', 'wpmake-user-avatar' ),
				'wpmake_user_avatar_cancel_button_confirmation' => esc_html__( 'OK', 'wpmake-user-avatar' ),
				'wpmake_user_avatar_enable_cropping_interface' => isset( $options['cropping_interface'] ) ? $options['cropping_interface'] : false,
			)
		);
	}
}
