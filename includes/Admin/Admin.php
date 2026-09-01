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
 *
 * Responsible for:
 *  - Registering the admin menu item.
 *  - Enqueueing admin scripts and styles.
 *  - Rendering the settings page shell (delegates content to Settings).
 *  - Admin footer text and review notice.
 */
class Admin {

	/**
	 * Slug of this plugin's admin page.
	 *
	 * @var string
	 */
	const MENU_SLUG = 'wpmake-advance-user-avatar';

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Bulk avatar manager instance.
	 *
	 * Built on load-{screen} rather than in the constructor, because WP_List_Table
	 * has to exist before screen options are registered and must not be built on
	 * admin screens that will never render it.
	 *
	 * @var UsersTable|null
	 */
	private ?UsersTable $users_table = null;

	/**
	 * User profile field instance.
	 *
	 * @var UserProfile
	 */
	private UserProfile $user_profile;

	/**
	 * Hook suffix of this plugin's own admin screen.
	 *
	 * Captured from add_submenu_page() rather than hardcoded, so the asset gate
	 * cannot drift from the menu registration.
	 *
	 * @var string
	 */
	private string $screen_hook = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->settings     = new Settings();
		$this->user_profile = new UserProfile();

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
	private function init_hooks(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 68 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_review_notice_assets' ) );

		/*
		 * Registered here rather than alongside add_screen_option(), because core calls
		 * set_screen_options() at wp-admin/admin.php line 116 -- long before
		 * load-{screen} fires at line 242. A filter added on the load hook is too late
		 * and the screen option silently never saves.
		 */
		add_filter( 'set_screen_option_' . UsersTable::PER_PAGE_OPTION, array( $this, 'save_per_page_option' ), 10, 3 );
	}

	/**
	 * Whether the review notice is going to be shown on this request.
	 *
	 * Used by both the renderer and the asset gate, so the notice can never print
	 * without the stylesheet that makes it legible.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	private function should_show_review_notice(): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( get_option( 'wpmake_aua_review_notice_dismissed', false ) ) {
			return false;
		}

		if ( get_transient( 'wpmake_aua_review_notice_snoozed' ) ) {
			return false;
		}

		return false !== wpmake_aua_check_activation_date( '14' );
	}

	/**
	 * Enqueue the review notice's own stylesheet, on any admin screen.
	 *
	 * The notice appears everywhere, but the admin bundle no longer does, so its
	 * styles live in a small stylesheet of their own rather than dragging the whole
	 * bundle onto every screen in wp-admin.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function enqueue_review_notice_assets(): void {
		if ( ! $this->should_show_review_notice() ) {
			return;
		}

		wp_enqueue_style(
			'wpmake-advance-user-avatar-notice-style',
			WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/css/wpmake-advance-user-avatar-notice.css',
			array(),
			WPMAKE_ADVANCE_USER_AVATAR_VERSION
		);
	}

	/**
	 * Enqueue the admin assets, on this plugin's own screen only.
	 *
	 * These used to be enqueued straight from init_hooks(), which runs on every
	 * admin request, so Select2 and the plugin stylesheet loaded on every screen in
	 * wp-admin. The review notice needs no part of this bundle: its script is
	 * dependency-free and printed with the notice itself.
	 *
	 * @since 1.4.0
	 *
	 * @param string $hook_suffix Screen the request is on.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook_suffix ): void {
		if ( in_array( $hook_suffix, array( 'profile.php', 'user-edit.php' ), true ) ) {
			$this->enqueue_user_profile_assets();

			return;
		}

		if ( $hook_suffix !== $this->screen_hook ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( 'avatars' === self::get_active_tab() ) {
			$this->enqueue_users_table_assets( $suffix );

			return;
		}

		wp_enqueue_script(
			'wpmake-advance-user-avatar-admin-script',
			WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/js/admin/wpmake-advance-user-avatar-admin' . $suffix . '.js',
			array( 'jquery' ),
			WPMAKE_ADVANCE_USER_AVATAR_VERSION,
			false
		);
		wp_enqueue_script(
			'select2',
			WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/js/select2/select2.min.js',
			array( 'jquery' ),
			'4.1.0',
			false
		);
		wp_enqueue_style(
			'wpmake-advance-user-avatar-select2-style',
			WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/css/select2/select2.css',
			array(),
			WPMAKE_ADVANCE_USER_AVATAR_VERSION
		);
		wp_enqueue_style(
			'wpmake-advance-user-avatar-admin-style',
			WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/css/wpmake-advance-user-avatar-admin.css',
			array(),
			WPMAKE_ADVANCE_USER_AVATAR_VERSION
		);

		/*
		 * No wp_localize_script here any more. Its only consumer was the review
		 * notice, which now carries its own ajax URL and nonce inline.
		 */
	}

	/**
	 * Enqueue the profile screen's assets.
	 *
	 * Deliberately not the settings bundle: Select2 and the settings stylesheet have
	 * no part in this field, and the front-end uploader -- webcam, cropper and all --
	 * has no business in wp-admin. This is the media picker and one small script.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	private function enqueue_user_profile_assets(): void {
		$user_id = UserProfile::get_screen_user_id();

		// Nothing renders when the field is not shown, so nothing should load either.
		if ( ! $user_id || ! wpmake_aua_current_user_can_edit_avatar( $user_id ) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// The media picker is only rendered for a user who has a library to pick
		// from, so its scripts -- which are not small -- follow the same test.
		if ( UserProfile::can_use_media_library() ) {
			wp_enqueue_media();
		}

		wp_enqueue_script(
			'wpmake-advance-user-avatar-user-profile-script',
			WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/js/admin/wpmake-advance-user-avatar-user-profile' . $suffix . '.js',
			array( 'jquery' ),
			WPMAKE_ADVANCE_USER_AVATAR_VERSION,
			true
		);

		wp_localize_script(
			'wpmake-advance-user-avatar-user-profile-script',
			'wpmake_aua_user_profile_params',
			array(
				'chooseTitle'  => esc_html__( 'Choose a profile picture', 'wpmake-advance-user-avatar' ),
				'chooseButton' => esc_html__( 'Use this picture', 'wpmake-advance-user-avatar' ),
			)
		);
	}

	/**
	 * Enqueue the bulk avatar manager's assets.
	 *
	 * Shares the plugin stylesheet with the settings tab -- it is the same screen --
	 * but not Select2, which nothing on this tab uses.
	 *
	 * @since 1.4.0
	 *
	 * @param string $suffix '.min' unless SCRIPT_DEBUG is on.
	 * @return void
	 */
	private function enqueue_users_table_assets( string $suffix ): void {
		if ( ! UsersTable::current_user_can_manage() ) {
			return;
		}

		if ( UserProfile::can_use_media_library() ) {
			wp_enqueue_media();
		}

		wp_enqueue_script(
			'wpmake-advance-user-avatar-users-table-script',
			WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/js/admin/wpmake-advance-user-avatar-users-table' . $suffix . '.js',
			array( 'jquery' ),
			WPMAKE_ADVANCE_USER_AVATAR_VERSION,
			true
		);

		wp_localize_script(
			'wpmake-advance-user-avatar-users-table-script',
			'wpmake_aua_users_table_params',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'setNonce'     => wp_create_nonce( 'wpmake_advance_user_avatar_set_nonce' ),
				'removeNonce'  => wp_create_nonce( 'wpmake_advance_user_avatar_remove_nonce' ),
				'chooseTitle'  => esc_html__( 'Choose a profile picture', 'wpmake-advance-user-avatar' ),
				'chooseButton' => esc_html__( 'Use this picture', 'wpmake-advance-user-avatar' ),
				'confirmText'  => esc_html__( 'Remove this avatar?', 'wpmake-advance-user-avatar' ),
				'confirmYes'   => esc_html__( 'Yes, remove', 'wpmake-advance-user-avatar' ),
				'confirmNo'    => esc_html__( 'Cancel', 'wpmake-advance-user-avatar' ),
				'savingText'   => esc_html__( 'Saving…', 'wpmake-advance-user-avatar' ),
				'errorText'    => esc_html__( 'Something went wrong. Please try again.', 'wpmake-advance-user-avatar' ),
			)
		);

		wp_enqueue_style(
			'wpmake-advance-user-avatar-admin-style',
			WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/css/wpmake-advance-user-avatar-admin.css',
			array(),
			WPMAKE_ADVANCE_USER_AVATAR_VERSION
		);
	}

	/**
	 * Register the admin sub-menu page under Users.
	 */
	public function register_menu(): void {
		$this->screen_hook = add_submenu_page(
			'users.php',
			esc_html__( 'Advanced Users Avatar', 'wpmake-advance-user-avatar' ),
			esc_html__( 'Users Avatar', 'wpmake-advance-user-avatar' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_settings_page' )
		);

		if ( $this->screen_hook ) {
			add_action( 'load-' . $this->screen_hook, array( $this, 'on_load_screen' ) );
		}
	}

	/**
	 * Which tab of the plugin's screen this request is on.
	 *
	 * @since 1.4.0
	 *
	 * @return string Either 'settings' or 'avatars'.
	 */
	public static function get_active_tab(): string {
		// Reading which tab to show. Nothing is written on the way in.
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return 'avatars' === $tab ? 'avatars' : 'settings';
	}

	/**
	 * Set up the bulk manager before its screen renders.
	 *
	 * Screen options are only honoured if they are added on load-{screen}, and the
	 * list table has to be constructed by then for its column headers to register.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function on_load_screen(): void {
		if ( 'avatars' !== self::get_active_tab() ) {
			return;
		}

		/*
		 * Refused here rather than at render time. This hook runs before
		 * admin-header.php has emitted anything, so wp_die() can still set a 403; by
		 * the time the page callback runs the headers are gone and the same refusal
		 * would go out as a 200.
		 */
		if ( ! UsersTable::current_user_can_manage() ) {
			wp_die(
				esc_html__( 'Sorry, you are not allowed to manage other users\' avatars.', 'wpmake-advance-user-avatar' ),
				esc_html__( 'Advanced User Avatar', 'wpmake-advance-user-avatar' ),
				array( 'response' => 403 )
			);
		}

		add_screen_option(
			'per_page',
			array(
				'label'   => esc_html__( 'Users per page', 'wpmake-advance-user-avatar' ),
				'default' => 20,
				'option'  => UsersTable::PER_PAGE_OPTION,
			)
		);

		$this->users_table = new UsersTable();
	}

	/**
	 * Save the bulk manager's rows-per-page screen option.
	 *
	 * Since WordPress 5.4.2 a non-standard per-page option is only stored if this
	 * filter hands back a value; returning false skips the save. See
	 * wp-admin/includes/misc.php line 797.
	 *
	 * @since 1.4.0
	 *
	 * @param mixed  $status Value to save, or false to skip.
	 * @param string $option Option being saved.
	 * @param int    $value  Value submitted.
	 * @return mixed
	 */
	public function save_per_page_option( $status, $option, $value ) {
		$value = (int) $value;

		// The same bounds core applies to its own per-page options.
		if ( $value < 1 || $value > 999 ) {
			return $status;
		}

		return $value;
	}

	/**
	 * Render the tab strip shared by both tabs of the plugin's screen.
	 *
	 * @since 1.4.0
	 *
	 * @param string $active Tab currently being rendered.
	 * @return void
	 */
	public static function render_tab_nav( string $active ): void {
		$tabs = array(
			'settings' => esc_html__( 'Settings', 'wpmake-advance-user-avatar' ),
		);

		// The bulk manager is only offered to somebody who could actually use it.
		if ( UsersTable::current_user_can_manage() ) {
			$tabs['avatars'] = esc_html__( 'Manage Avatars', 'wpmake-advance-user-avatar' );
		}

		if ( count( $tabs ) < 2 ) {
			return;
		}

		echo '<nav class="nav-tab-wrapper wp-clearfix" aria-label="' . esc_attr__( 'Secondary menu', 'wpmake-advance-user-avatar' ) . '">';

		foreach ( $tabs as $slug => $label ) {
			$url = add_query_arg(
				array(
					'page' => self::MENU_SLUG,
					'tab'  => $slug,
				),
				admin_url( 'users.php' )
			);

			printf(
				'<a href="%1$s" class="nav-tab%2$s">%3$s</a>',
				esc_url( $url ),
				$slug === $active ? ' nav-tab-active' : '',
				esc_html( $label )
			);
		}

		echo '</nav>';
	}

	/**
	 * Render the settings page — delegates to Settings::render_page().
	 */
	public function render_settings_page(): void {
		if ( 'avatars' === self::get_active_tab() ) {
			if ( ! $this->users_table instanceof UsersTable ) {
				// on_load_screen() declined to build it, which only happens when the
				// capability check failed.
				$this->users_table = new UsersTable();
			}

			$this->users_table->render_screen();

			return;
		}

		$this->settings->render_page();
	}

	/**
	 * Change the admin footer text on the settings page.
	 *
	 * @since  1.0.2
	 *
	 * @param  string $footer_text Default footer text.
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		if ( ! isset( $_GET['page'] ) || 'wpmake-advance-user-avatar' !== $_GET['page'] ) {
			return $footer_text;
		}

		if ( ! get_option( 'wpmake_advance_user_avatar_admin_footer_text_rated' ) ) {
			$footer_text = wp_kses_post(
				sprintf(
					/* translators: 1: Plugin name 2: Five-star rating link */
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

		return $footer_text;
	}

	/**
	 * Review notice displayed in the admin header.
	 *
	 * @since  1.0.2
	 * @return void
	 */
	public function review_notice(): void {
		if ( ! $this->should_show_review_notice() ) {
			return;
		}

		$notice_target_link = 'https://wordpress.org/support/plugin/wpmake-advance-user-avatar/reviews/#postform';
		$notice_content     = wpmake_aua_review_notice_content();

		?>
		<div id="wpmake-aua-review-notice" class="notice notice-info wpmake-aua-notice" data-purpose="notice-info" data-notice-id="review">
			<div class="wpmake-aua-notice-thumbnail">
				<img src="<?php echo esc_url( WPMAKE_ADVANCE_USER_AVATAR_URL . '/assets/images/icon.png' ); ?>" alt="">
			</div>
			<div class="wpmake-aua-notice-text">
				<div class="wpmake-aua-notice-body">
					<?php echo wp_kses_post( $notice_content ); ?>
				</div>
				<div class="wpmake-aua-notice-links">
					<ul class="wpmake-aua-notice-ul">
						<li><a class="button button-primary notice-link-visit" data-dismiss-type="rated" href="<?php echo esc_url( $notice_target_link ); ?>" target="_blank"><span class="dashicons dashicons-external"></span><?php esc_html_e( 'Sure, I\'d love to!', 'wpmake-advance-user-avatar' ); ?></a></li>
						<li><a href="#" class="button button-secondary notice-later" data-dismiss-type="later"><span class="dashicons dashicons-clock"></span><?php esc_html_e( 'Maybe Later', 'wpmake-advance-user-avatar' ); ?></a></li>
						<li><a href="#" class="button button-secondary notice-dismiss-permanently" data-dismiss-type="rated"><span class="dashicons dashicons-smiley"></span><?php esc_html_e( 'I already did!', 'wpmake-advance-user-avatar' ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
		<?php
		$this->print_review_notice_script();
	}

	/**
	 * Print the review notice's dismiss handlers.
	 *
	 * Deliberately dependency-free. The notice shows on every admin screen, and
	 * pulling jQuery plus the whole admin bundle onto all of them just to wire up
	 * three buttons is what this milestone exists to stop.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	private function print_review_notice_script(): void {
		$data = wp_json_encode(
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'notice_nonce' ),
			)
		);
		?>
		<script>
		( function () {
			var cfg    = <?php echo $data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode output. ?>;
			var notice = document.getElementById( 'wpmake-aua-review-notice' );

			if ( ! notice ) {
				return;
			}

			function dismiss( type ) {
				var body = new URLSearchParams();
				body.append( 'action', 'wpmake_advance_user_avatar_upload_dismiss_notice' );
				body.append( 'security', cfg.nonce );
				body.append( 'dismissed', 'true' );
				body.append( 'type', type );

				window.fetch( cfg.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: body.toString()
				} );
			}

			notice.querySelectorAll( '[data-dismiss-type]' ).forEach( function ( el ) {
				el.addEventListener( 'click', function ( e ) {
					// "Sure, I'd love to!" is a real link to the review page; let it open.
					var href = el.getAttribute( 'href' );

					if ( ! href || '#' === href ) {
						e.preventDefault();
					}

					dismiss( el.getAttribute( 'data-dismiss-type' ) );
					notice.style.display = 'none';
				} );
			} );
		}() );
		</script>
		<?php
	}
}
