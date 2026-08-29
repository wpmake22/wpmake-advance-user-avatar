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
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_hooks(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ), 10, 2 );

		$options = get_option( 'wpmake_advance_user_avatar_settings', array() );

		$this->init_woocommerce_hooks( $options );
		$this->init_buddypress_hooks( $options );
		$this->init_wpmembers_hooks( $options );

		// Better Messages integration — no-op if Better Messages is not active.
		add_filter( 'better_messages_rest_user_item', array( $this, 'better_messages_avatar' ), 10, 3 );
	}

	// -------------------------------------------------------------------------
	// WooCommerce hooks
	// -------------------------------------------------------------------------

	/**
	 * Wires up all WooCommerce display hooks based on the saved location settings.
	 *
	 * Backward compat: if the granular woo_display_locations option has not been
	 * saved yet (user hasn't visited the settings page since the update) but the
	 * legacy woocommerce_integration toggle was enabled, the two original locations
	 * (my_account_dashboard + account_details_tab) are assumed.
	 *
	 * @param array $options Saved plugin options.
	 */
	private function init_woocommerce_hooks( array $options ): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Resolve active display locations, falling back to legacy setting.
		$locations = isset( $options['woo_display_locations'] )
			? (array) $options['woo_display_locations']
			: array();

		if ( empty( $locations ) && ! empty( $options['woocommerce_integration'] ) ) {
			$locations = array( 'my_account_dashboard', 'account_details_tab' );
		}

		// -- Display locations ------------------------------------------------

		// Resolve uploader state early — needed below to suppress a redundant viewer
		// on Account Details (the uploader template already renders a preview image).
		$uploader_enabled = isset( $options['woo_my_account_uploader'] )
			? ! empty( $options['woo_my_account_uploader'] )
			: ! empty( $options['woocommerce_integration'] );

		// My Account dashboard tab — avatar viewer at the top.
		if ( in_array( 'my_account_dashboard', $locations, true ) ) {
			add_action( 'woocommerce_account_dashboard', array( $this, 'woo_render_avatar_viewer' ), 5 );
		}

		// Account details (edit account) tab — avatar viewer above the form.
		// Skip when the uploader is also wired here: it renders its own preview,
		// and adding the viewer would duplicate the image.
		if ( in_array( 'account_details_tab', $locations, true ) && ! $uploader_enabled ) {
			add_action( 'woocommerce_before_edit_account_form', array( $this, 'woo_render_avatar_viewer' ), 5 );
		}

		// My Orders page — avatar viewer above the orders table.
		if ( in_array( 'order_history', $locations, true ) ) {
			add_action( 'woocommerce_before_account_orders', array( $this, 'woo_render_avatar_viewer' ), 5 );
		}

		// Product reviews — the global get_avatar filter in CoreFunctions.php
		// already replaces Gravatar site-wide, so no additional hook is needed.
		// The setting exists so the user can deliberately opt in/out.

		// Checkout page — avatar viewer above customer detail fields.
		if ( in_array( 'checkout_page', $locations, true ) ) {
			add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'woo_render_avatar_viewer' ), 5 );
		}

		// Wishlist — conditional on YITH WooCommerce Wishlist being active.
		if ( in_array( 'wishlist', $locations, true ) && class_exists( 'YITH_WCWL' ) ) {
			add_action( 'yith_wcwl_before_wishlist_title', array( $this, 'woo_render_avatar_viewer' ), 5 );
		}

		// -- My Account uploader ----------------------------------------------

		if ( $uploader_enabled ) {
			// Show the upload widget on the Account Details form.
			add_action( 'woocommerce_before_edit_account_form', array( $this, 'woo_render_avatar_uploader' ), 10 );
		}
	}

	/**
	 * Render the avatar viewer shortcode (display only).
	 * Called by every display-location hook.
	 */
	public function woo_render_avatar_viewer(): void {
		echo apply_shortcodes( '[wpmake_advance_user_avatar]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render the avatar uploader shortcode.
	 * Called by the My Account uploader hook.
	 */
	public function woo_render_avatar_uploader(): void {
		echo apply_shortcodes( '[wpmake_advance_user_avatar_upload]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	// -------------------------------------------------------------------------
	// BuddyPress hooks
	// -------------------------------------------------------------------------

	/**
	 * Wires up BuddyPress avatar hooks when the integration is enabled.
	 *
	 * @param array $options Saved plugin options.
	 */
	private function init_buddypress_hooks( array $options ): void {
		if ( empty( $options['buddypress_integration'] ) ) {
			return;
		}

		add_action( 'bp_after_member_avatar_upload_content', array( $this, 'bp_render_avatar_uploader' ) );
		add_filter( 'bp_core_fetch_avatar_url', array( $this, 'bp_replace_avatar_url' ), 10, 2 );
		add_filter( 'bp_core_fetch_avatar', array( $this, 'bp_replace_avatar_image' ), 10, 9 );
	}

	/**
	 * Render avatar uploader inside BuddyPress Change Avatar section.
	 */
	public function bp_render_avatar_uploader(): void {
		echo apply_shortcodes( '[wpmake_advance_user_avatar_upload]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Replace BuddyPress avatar HTML with the user's custom avatar image.
	 *
	 * @param string $image      Default avatar HTML.
	 * @param array  $params     Avatar parameters.
	 * @param mixed  $item_id    User/item ID.
	 * @param string $avatar_dir Avatar directory.
	 * @param string $html_css_id HTML/CSS ID.
	 * @param string $html_width  HTML width.
	 * @param string $html_height HTML height.
	 * @param string $avatar_folder_url Avatar folder URL.
	 * @param string $avatar_folder_dir Avatar folder directory.
	 * @return string
	 */
	public function bp_replace_avatar_image( $image, $params, $item_id, $avatar_dir, $html_css_id, $html_width, $html_height, $avatar_folder_url, $avatar_folder_dir ) {
		$user = false;
		if ( is_numeric( $item_id ) ) {
			$user = get_user_by( 'id', absint( $item_id ) );
		}

		if ( ! $user || is_wp_error( $user ) ) {
			return $image;
		}

		$profile_picture_url = wp_get_attachment_image_url(
			get_user_meta( $user->ID, 'wpmake_advance_user_avatar_attachment_id', true )
		);

		$class = array( 'avatar', 'avatar-' . (int) $params['width'], 'photo' );

		if ( ( isset( $args['found_avatar'] ) && ! $args['found_avatar'] ) || ( isset( $args['force_default'] ) && $args['force_default'] ) ) {
			$class[] = 'avatar-default';
		}

		if ( $profile_picture_url ) {
			$image = sprintf(
				"<img alt='%s' src='%s' srcset='%s' class='%s' height='%s' width='%s' %s />",
				esc_attr( $params['alt'] ),
				esc_url( $profile_picture_url ),
				esc_url( $profile_picture_url ),
				esc_attr( implode( ' ', $class ) ),
				esc_attr( $params['height'] ),
				esc_attr( $params['width'] ),
				esc_attr( $params['extra_attr'] )
			);
		}

		return $image;
	}

	/**
	 * Replace BuddyPress avatar URL with the user's custom avatar URL.
	 *
	 * @param string $image  Default avatar URL.
	 * @param array  $params Avatar parameters.
	 * @return string
	 */
	public function bp_replace_avatar_url( $image, $params ) {
		$user = false;
		if ( is_numeric( $params['item_id'] ) ) {
			$user = get_user_by( 'id', absint( $params['item_id'] ) );
		}

		if ( ! $user || is_wp_error( $user ) ) {
			return $image;
		}

		$profile_picture_url = wp_get_attachment_image_url(
			get_user_meta( $user->ID, 'wpmake_advance_user_avatar_attachment_id', true ),
			'thumbnail'
		);

		if ( $profile_picture_url ) {
			$image = $profile_picture_url;
		}

		return $image;
	}

	// -------------------------------------------------------------------------
	// WP-Members hooks
	// -------------------------------------------------------------------------

	/**
	 * Wires up WP-Members hooks when the integration is enabled.
	 *
	 * Avatar display needs no hook of its own: WP-Members renders avatars through
	 * get_avatar() (including its own [wpmem_avatar] shortcode), which the filter in
	 * CoreFunctions.php already overrides.
	 *
	 * @param array $options Saved plugin options.
	 */
	private function init_wpmembers_hooks( array $options ): void {
		if ( empty( $options['wpmembers_integration'] ) || ! class_exists( 'WP_Members' ) ) {
			return;
		}

		add_filter( 'wpmem_register_form_rows', array( $this, 'wpmembers_add_avatar_row' ), 10, 2 );
	}

	/**
	 * Add the avatar uploader as a row inside the WP-Members profile edit form.
	 *
	 * Only the profile form gets the row — on the registration form there is no user
	 * yet to attach an avatar to.
	 *
	 * @param array  $rows Form rows, keyed by meta key.
	 * @param string $tag  Form being generated: new|edit.
	 * @return array
	 */
	public function wpmembers_add_avatar_row( $rows, $tag ) {
		if ( 'edit' !== $tag || ! is_user_logged_in() || ! is_array( $rows ) ) {
			return $rows;
		}

		$meta_key = 'wpmake_advance_user_avatar';

		if ( isset( $rows[ $meta_key ] ) ) {
			return $rows;
		}

		$label_text = __( 'Profile Picture', 'wpmake-advance-user-avatar' );

		// The row filter does not receive the form args, so the wrappers are copied from
		// a sibling row to match whatever markup this form was built with.
		$sample = ! empty( $rows ) ? reset( $rows ) : array();
		$wrap   = ! empty( $sample['field_before'] );

		$label = function_exists( 'wpmem_form_label' )
			? wpmem_form_label(
				array(
					'meta_key' => $meta_key,
					'label'    => $label_text,
					'type'     => 'text',
				)
			)
			: '<label class="text">' . esc_html( $label_text ) . '</label>';

		$row = array(
			'meta'         => $meta_key,
			// Deliberately not 'file': the uploader posts over ajax, and a file type would
			// switch the whole WP-Members form to multipart enctype for nothing.
			'type'         => 'text',
			'value'        => '',
			'values'       => '',
			'label_text'   => $label_text,
			'row_before'   => isset( $sample['row_before'] ) ? $sample['row_before'] : '',
			'label'        => $label,
			'field_before' => $wrap ? '<div class="div_text">' : '',
			'field'        => apply_shortcodes( '[wpmake_advance_user_avatar_upload]' ),
			'field_after'  => $wrap ? '</div>' : '',
			'row_after'    => isset( $sample['row_after'] ) ? $sample['row_after'] : '',
		);

		/**
		 * Filter where the avatar row sits in the WP-Members profile form.
		 *
		 * @since 1.2.3
		 *
		 * @param string $position Either 'top' (default) or 'bottom'.
		 */
		$position = apply_filters( 'wpmake_advance_user_avatar_wpmembers_row_position', 'top' );

		$avatar_row = array( $meta_key => $row );

		return ( 'bottom' === $position )
			? array_merge( $rows, $avatar_row )
			: array_merge( $avatar_row, $rows );
	}

	// -------------------------------------------------------------------------
	// Third-party integrations
	// -------------------------------------------------------------------------

	/**
	 * Replace Better Messages avatar with the user's custom avatar.
	 *
	 * Better Messages does not use get_avatar() in its REST responses, so the
	 * standard get_avatar filter is bypassed. This hooks into the plugin's own
	 * better_messages_rest_user_item filter to supply the correct avatar URL.
	 *
	 * @param array $item             User item data array (includes 'avatar' key).
	 * @param int   $user_id          The user ID.
	 * @param bool  $include_personal Whether personal data is included.
	 * @return array
	 */
	public function better_messages_avatar( $item, $user_id, $include_personal ) {
		$attachment_id = get_user_meta( $user_id, 'wpmake_advance_user_avatar_attachment_id', true );

		if ( ! $attachment_id ) {
			return $item;
		}

		$avatar_url = wp_get_attachment_url( $attachment_id );

		if ( ! $avatar_url ) {
			return $item;
		}

		if ( is_ssl() ) {
			$avatar_url = set_url_scheme( $avatar_url, 'https' );
		}

		$item['avatar'] = $avatar_url;

		return $item;
	}

	// -------------------------------------------------------------------------
	// Scripts & styles
	// -------------------------------------------------------------------------

	/**
	 * Enqueue frontend scripts and styles.
	 *
	 * @since 1.0.0
	 */
	public function load_scripts(): void {
		$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$options = get_option( 'wpmake_advance_user_avatar_settings', array() );

		wp_enqueue_script( 'wpmake-advance-user-avatar-frontend-script', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/js/frontend/wpmake-advance-user-avatar-frontend' . $suffix . '.js', array( 'jquery' ), WPMAKE_ADVANCE_USER_AVATAR_VERSION, true );
		wp_enqueue_script( 'wpmake-advance-user-avatar-webcam-script', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/js/webcam/webcam' . $suffix . '.js', array( 'jquery' ), WPMAKE_ADVANCE_USER_AVATAR_VERSION, true );

		wp_enqueue_style( 'wpmake-advance-user-avatar-frontend-style', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/css/wpmake-advance-user-avatar-frontend.css', array(), WPMAKE_ADVANCE_USER_AVATAR_VERSION );

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
				'wpmake_advance_user_avatar_ssl_error_title' => esc_html__( 'Camera Unavailable', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_ssl_error_text' => esc_html__( 'Camera access requires a secure (HTTPS) connection. You can upload a photo from your device instead.', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_permission_error_title' => esc_html__( 'Camera Access Denied', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_permission_error_text' => esc_html__( 'Camera access was blocked. Allow it in your browser settings, or upload a photo instead.', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_cancel_button' => esc_html__( 'Cancel', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_cancel_button_confirmation' => esc_html__( 'Close', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_enable_cropping_interface' => $options['cropping_interface'] ?? false,
				'wpmake_assets_url'                        => WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL,
				'wpmake_advance_user_avatar_upload_success_message' => esc_html__( 'Avatar has been uploaded successfully.', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_remove_confirm_text' => esc_html__( 'Remove your avatar?', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_remove_yes'    => esc_html__( 'Yes, remove', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_use_upload_instead' => esc_html__( 'Upload a Photo', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_crop_ratio_label' => esc_html__( 'Square crop (1:1)', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_retake'        => esc_html__( 'Retake', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_reupload'      => esc_html__( 'Re-upload', 'wpmake-advance-user-avatar' ),
				'wpmake_advance_user_avatar_save_avatar'   => esc_html__( 'Save avatar', 'wpmake-advance-user-avatar' ),
			)
		);
	}
}
