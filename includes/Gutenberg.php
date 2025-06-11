<?php
/**
 * WPMakeAdvanceUserAvatar blocks.
 *
 * @since 3.1.5
 * @package WPMakeAdvanceUserAvatar/Gutenberg
 */

namespace WPMake\WPMakeAdvanceUserAvatar;

use WPMake\WPMakeAdvanceUserAvatar\Admin\Shortcodes;

defined( 'ABSPATH' ) || exit;
/**
 * WPMakeAdvanceUserAvatar blocks class.
 */
class Gutenberg {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 *
	 * @since 3.1.5
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'register_block_types' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}
	/**
	 * Enqueue Block Editor Assets.
	 *
	 * @return void.
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_script(
			'wpmake-aua-gutenberg-block-script',
			WPMAKE_ADVANCE_USER_AVATAR_URL . '/chunks/blocks.js',
			array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor', 'wp-components', 'react', 'react-dom' ),
			WPMAKE_ADVANCE_USER_AVATAR_VERSION,
			true
		);

		wp_localize_script(
			'wpmake-aua-gutenberg-block-script',
			'wpmake_plugins_params',
			array(
				'wpmake_aua_nonce' => wp_create_nonce( 'wpmake_aua_nonce' ),
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_enqueue_style( 'wpmake-advance-user-avatar-frontend-style', WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/css/wpmake-advance-user-avatar-frontend.css', array( 'wp-edit-blocks' ), WPMAKE_ADVANCE_USER_AVATAR_VERSION );
	}

	/**
	 * Register gutenberg block.
	 *
	 * @return Void.
	 */
	public function register_block_types() {

		$metadata = dirname( WPMAKE_ADVANCE_USER_AVATAR_PLUGIN_FILE ) . '/chunks/user-avatar/block.json';

		if ( ! file_exists( $metadata ) ) {
			_doing_it_wrong(
				__CLASS__,
				/* Translators: 1: Block name */
				esc_html( sprintf( __( 'Metadata file for %s block does not exist.', 'wpmake-advance-user-avatar' ), 'Advanced User Avatar' ) ),
				'2.0.9'
			);
			return;
		}
		register_block_type_from_metadata(
			$metadata,
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Render callback.
	 *
	 * @param array     $attributes Block attributes.
	 * @param string    $content Block content.
	 * @param \WP_Block $block Block object.
	 *
	 * @return string
	 */
	public function render( $attributes, $content, $block ) {
		$content = apply_filters(
			'wpmake_aua_block_content',
			$this->wpmake_advance_user_avatar_content( $attributes ),
			$this
		);

		return $content;
	}

	/**
	 * Advanced Avatar content.
	 *
	 * @param array $attributes Block Attributes.
	 */
	public function wpmake_advance_user_avatar_content( $attributes ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		if ( isset( $attributes['blockType'] ) && 'normal' === $attributes['blockType'] ) {
			return Shortcodes::user_avatar( array() );
		}

		return Shortcodes::user_avatar_upload( array() );
	}
}
