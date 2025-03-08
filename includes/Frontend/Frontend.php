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
		$options = get_option( 'wpmake_advance_user_avatar_settings', array() );

		if ( isset( $options['woocommerce_integration'] ) && $options['woocommerce_integration'] ) {
			add_action( 'woocommerce_account_page_endpoint', array( $this, 'wpmake_insert_avatar_viewer_in_woocommerce_account_dashboard' ) );
			add_action( 'woocommerce_before_edit_account_form', array( $this, 'wpmake_insert_avatar_uploader_in_woocommerce_account_dashboard' ) );
		}

		if ( isset( $options['buddypress_integration'] ) && $options['buddypress_integration'] ) {
			add_action( 'bp_after_member_avatar_upload_content', array( $this, 'wpmake_insert_avatar_uploader_in_bbpress_avatar_change' ) );
			add_filter( 'bp_core_fetch_avatar_url', array( $this, 'wpmake_advance_user_avatar_replace_bbpress_avatar_image_url' ), 10, 2 );
			add_filter( 'bp_core_fetch_avatar', array( $this, 'wpmake_advance_user_avatar_replace_bbpress_avatar_image' ), 10, 9 );
		}

	}

	/**
	 * Enqueue scripts
	 *
	 * @since 1.0.0
	 */
	public function load_scripts() {
		$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$options = get_option( 'wpmake_advance_user_avatar_settings', array() );

		// Enqueue frontend scripts here.
		wp_enqueue_script( 'wpmake-advance-user-avatar-frontend-script', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/js/frontend/wpmake-advance-user-avatar-frontend' . $suffix . '.js', array( 'jquery' ), WPMAKE_ADVANCE_USER_AVATAR_VERSION, false );
		wp_enqueue_script( 'wpmake-sweetalert2', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/js/sweetalert2/sweetalert2.min.js', array( 'jquery' ), '11.4.8', false );
		wp_enqueue_script( 'wpmake-advance-user-avatar-jcrop-script', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/js/jquery-Jcrop/jquery.Jcrop.min.js', array( 'jquery' ), WPMAKE_ADVANCE_USER_AVATAR_VERSION, false );
		wp_enqueue_script( 'wpmake-advance-user-avatar-webcam-script', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/js/webcam/webcam' . $suffix . '.js', array( 'jquery' ), WPMAKE_ADVANCE_USER_AVATAR_VERSION, true );

		// Enqueue frontend styles here.
		wp_enqueue_style( 'wpmake-advance-user-avatar-frontend-style', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/css/wpmake-advance-user-avatar-frontend.css', array(), WPMAKE_ADVANCE_USER_AVATAR_VERSION );
		wp_enqueue_style( 'wpmake-sweetalert2', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/css/sweetalert2/sweetalert2.min.css', array(), '11.4.8' );
		wp_enqueue_style( 'wpmake-advance-user-avatar-jcrop-style', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/css/jquery.Jcrop.min.css', array(), 'WPMAKE_ADVANCE_USER_AVATAR_VERSION' );

		wp_localize_script(
			'wpmake-advance-user-avatar-frontend-script',
			'wpmake_advance_user_avatar_params',
			array(
				'ajax_url'                                 => admin_url( 'admin-ajax.php' ),
				'wpmake_advance_user_avatar_upload_nonce'  => wp_create_nonce( 'wpmake_advance_user_avatar_upload_nonce' ),
				'wpmake_advance_user_avatar_remove_nonce'  => wp_create_nonce( 'wpmake_advance_user_avatar_remove_nonce' ),
				'wpmake_advance_user_avatar_uploading'     => esc_html__( 'Uploading...', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_something_wrong' => esc_html__( 'Something wrong, please try again.', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_crop_picture_title' => esc_html__( 'Crop Your Picture', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_crop_picture_button' => esc_html__( 'Crop Picture', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_capture'       => esc_html__( 'Capture', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_ssl_error_title' => esc_html__( 'SSl Certificate Error', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_ssl_error_text' => esc_html__( 'The site must be secure. Please enable https connection.', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_permission_error_title' => esc_html__( 'Permission Error', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_permission_error_text' => esc_html__( 'Please allow access to webcam.', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_cancel_button' => esc_html__( 'Cancel', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_cancel_button_confirmation' => esc_html__( 'OK', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_enable_cropping_interface' => isset( $options['cropping_interface'] ) ? $options['cropping_interface'] : false,
				'wpmake_assets_url'                        => WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL,
				'wpmake_advance_user_avatar_upload_success_message' => esc_html__( 'Avatar has been uploaded successfully.', 'wpmake-advance-user-avatar' ),
			)
		);
	}

	/**
	 * Insert avatar viewer in WooCommerce account dashboard.
	 */
	public function wpmake_insert_avatar_viewer_in_woocommerce_account_dashboard() {

		echo apply_shortcodes( '[wpmake_advance_user_avatar]' );

		wc_get_template(
			'myaccount/dashboard.php',
			array(
				'current_user' => get_user_by( 'id', get_current_user_id() ),
			)
		);
	}

	/**
	 * Insert avatar uploader in WooCommerce account dashboard.
	 */
	public function wpmake_insert_avatar_uploader_in_woocommerce_account_dashboard() {
		echo apply_shortcodes( '[wpmake_advance_user_avatar_upload]' );
	}

	/**
	 * Insert avatar uploader in WooCommerce account dashboard.
	 */
	public function wpmake_insert_avatar_uploader_in_bbpress_avatar_change() {
		echo apply_shortcodes( '[wpmake_advance_user_avatar_upload]' );
	}

	/**
	 * Replace bbpress avatar image.
	 *
	 * @param string $image Avatar image.
	 * @param array $params Params.
	 * @param string $item_id Item ID.
	 * @param string $avatar_dir Avatar directory.
	 * @param string $html_css_id HTML CSS ID.
	 * @param string $html_width HTML width.
	 * @param string $html_height HTML height.
	 * @param string $avatar_folder_url Avatar folder URL.
	 * @param string $avatar_folder_dir Avatar folder directory.
	 */
	public function wpmake_advance_user_avatar_replace_bbpress_avatar_image($image, $params, $item_id, $avatar_dir, $html_css_id, $html_width, $html_height, $avatar_folder_url, $avatar_folder_dir ) {

		$user = false;
		if ( is_numeric( $item_id ) ) {
			$user = get_user_by( 'id', absint( $item_id ) );
		}

		if ( ! $user || is_wp_error( $user ) ) {
			return $image;
		}

		$profile_picture_url = wp_get_attachment_image_url( get_user_meta( $user->ID, 'wpmake_advance_user_avatar_attachment_id', true ) );

		$class               = array( 'avatar', 'avatar-' . (int) $params['width'], 'photo' );

		if ( ( isset( $args['found_avatar'] ) && ! $args['found_avatar'] ) || ( isset( $args['force_default'] ) && $args['force_default'] ) ) {
			$class[] = 'avatar-default';
		}

		if ( $profile_picture_url ) {

			$image = sprintf(
				"<img alt='%s' src='%s' srcset='%s' class='%s' height='%s' width='%s' %s />",
				esc_attr( $params['alt'] ),
				esc_url( $profile_picture_url ),
				esc_url( $profile_picture_url ),
				esc_attr( join( ' ', $class ) ),
				esc_attr( $params['height'] ),
				esc_attr( $params['width'] ),
				esc_attr( $params['extra_attr'] )
			);
		}

		return $image;
	}

	/**
	 * Replace bbpress avatar image url.
	 *
	 * @param string $image URL.
	 * @param array $params Params.
	 */
	public function wpmake_advance_user_avatar_replace_bbpress_avatar_image_url($image, $params ) {

		$user = false;
		if ( is_numeric( $params['item_id'] ) ) {
			$user = get_user_by( 'id', absint( $params['item_id'] ) );
		}

		if ( ! $user || is_wp_error( $user ) ) {
			return $image;
		}

		$profile_picture_url = wp_get_attachment_thumb_url( get_user_meta( $user->ID, 'wpmake_advance_user_avatar_attachment_id', true ) );

		if ( $profile_picture_url ) {
			$image = $profile_picture_url;
		}

		return $image;
	}
}
