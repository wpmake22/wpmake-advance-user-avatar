<?php
/**
 * WPMakeAdvanceUserAvatar UsersTable.
 *
 * The bulk avatar manager: every user, their current avatar, and a way to change
 * or clear it without leaving the screen.
 *
 * @class    UsersTable
 * @version  1.4.0
 * @package  WPMakeAdvanceUserAvatar/Admin
 */

namespace WPMake\WPMakeAdvanceUserAvatar\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// WP_List_Table is not autoloaded, and is not loaded on every admin request.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * UsersTable Class
 *
 * Extends WP_List_Table rather than hand-rolling a grid: search, pagination,
 * sorting, screen options and core's own styling all come with it.
 */
class UsersTable extends \WP_List_Table {

	/**
	 * Screen option storing rows per page.
	 *
	 * @var string
	 */
	const PER_PAGE_OPTION = 'wpmake_aua_users_per_page';

	/**
	 * Capability required to see this screen.
	 *
	 * @var string
	 */
	const CAPABILITY = 'list_users';

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'user',
				'plural'   => 'users',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Rows per page, from the screen option.
	 *
	 * @since 1.4.0
	 *
	 * @return int
	 */
	private function get_per_page(): int {
		$per_page = (int) get_user_option( self::PER_PAGE_OPTION );

		return $per_page > 0 ? $per_page : 20;
	}

	/**
	 * Columns.
	 *
	 * @since 1.4.0
	 *
	 * @return array
	 */
	public function get_columns(): array {
		return array(
			'avatar'       => esc_html__( 'Avatar', 'wpmake-advance-user-avatar' ),
			'display_name' => esc_html__( 'Name', 'wpmake-advance-user-avatar' ),
			'email'        => esc_html__( 'Email', 'wpmake-advance-user-avatar' ),
			'role'         => esc_html__( 'Role', 'wpmake-advance-user-avatar' ),
			'actions'      => esc_html__( 'Actions', 'wpmake-advance-user-avatar' ),
		);
	}

	/**
	 * Sortable columns.
	 *
	 * Only the two WP_User_Query can order by without a join it would not otherwise
	 * make. Role is a serialised capability array and email sorting is native.
	 *
	 * @since 1.4.0
	 *
	 * @return array
	 */
	public function get_sortable_columns(): array {
		return array(
			'display_name' => array( 'display_name', false ),
			'email'        => array( 'email', false ),
		);
	}

	/**
	 * Fetch the page of users, then warm every cache the rows will read.
	 *
	 * The naive version of this screen costs two queries per row -- one for the
	 * avatar meta, one for the attachment -- so a 20-row page would fire 40 queries
	 * nothing needed. Both are primed here in two queries flat, whatever the page
	 * size.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		$per_page = $this->get_per_page();
		$paged    = $this->get_pagenum();

		$args = array(
			'number' => $per_page,
			'paged'  => $paged,
			'fields' => 'all_with_meta',
		);

		$search = $this->get_search_term();

		if ( '' !== $search ) {
			// Wrapped in asterisks so the match is a substring rather than exact.
			$args['search']         = '*' . $search . '*';
			$args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
		}

		$orderby = $this->get_request_orderby();

		if ( '' !== $orderby ) {
			$args['orderby'] = $orderby;
			$args['order']   = $this->get_request_order();
		}

		$query = new \WP_User_Query( $args );

		$this->items = $query->get_results();

		$this->warm_caches( $this->items );

		$this->set_pagination_args(
			array(
				'total_items' => (int) $query->get_total(),
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * Prime the user meta and attachment caches for a page of users.
	 *
	 * @since 1.4.0
	 *
	 * @param array $users Users on this page.
	 * @return void
	 */
	private function warm_caches( array $users ): void {
		if ( empty( $users ) ) {
			return;
		}

		$user_ids = wp_list_pluck( $users, 'ID' );

		// One query for every user's meta, instead of one per row on first read.
		update_meta_cache( 'user', $user_ids );

		$attachment_ids = array();

		foreach ( $user_ids as $user_id ) {
			$attachment_id = (int) get_user_meta( $user_id, 'wpmake_advance_user_avatar_attachment_id', true );

			if ( $attachment_id > 0 ) {
				$attachment_ids[] = $attachment_id;
			}
		}

		$attachment_ids = array_unique( $attachment_ids );

		if ( empty( $attachment_ids ) ) {
			return;
		}

		/*
		 * One query for the attachment posts, which also primes their post meta --
		 * _wp_attached_file and _wp_attachment_metadata, the two things
		 * wp_get_attachment_image_url() reads for every avatar below.
		 */
		get_posts(
			array(
				'post__in'               => $attachment_ids,
				'post_type'              => 'attachment',
				'post_status'            => 'inherit',
				'posts_per_page'         => -1,
				'orderby'                => 'post__in',
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			)
		);
	}

	/**
	 * The search term for this request.
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	private function get_search_term(): string {
		// A read-only listing driven by GET. No state changes here, so no nonce.
		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return trim( $search );
	}

	/**
	 * The requested sort column, validated against the sortable list.
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	private function get_request_orderby(): string {
		$orderby = isset( $_REQUEST['orderby'] ) ? sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$allowed = array_keys( $this->get_sortable_columns() );

		return in_array( $orderby, $allowed, true ) ? $orderby : '';
	}

	/**
	 * The requested sort direction.
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	private function get_request_order(): string {
		$order = isset( $_REQUEST['order'] ) ? strtoupper( sanitize_key( wp_unslash( $_REQUEST['order'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return 'DESC' === $order ? 'DESC' : 'ASC';
	}

	/**
	 * Avatar column.
	 *
	 * @since 1.4.0
	 *
	 * @param \WP_User $item User for this row.
	 * @return string
	 */
	public function column_avatar( $item ): string {
		$attachment_id = (int) get_user_meta( $item->ID, 'wpmake_advance_user_avatar_attachment_id', true );
		$has_avatar    = $attachment_id > 0;
		$url           = $has_avatar ? wpmake_aua_get_avatar_url( $item->ID, 96 ) : '';

		if ( '' === $url ) {
			$url = get_avatar_url(
				$item->ID,
				array(
					'size'          => 96,
					'force_default' => true,
				)
			);
		}

		return sprintf(
			'<img src="%1$s" width="48" height="48" alt="" class="avatar avatar-48 photo wpmake-aua-row-avatar" data-user="%2$d" data-default="%3$s" />',
			esc_url( $url ),
			(int) $item->ID,
			esc_url(
				get_avatar_url(
					$item->ID,
					array(
						'size'          => 96,
						'force_default' => true,
					)
				)
			)
		);
	}

	/**
	 * Name column.
	 *
	 * @since 1.4.0
	 *
	 * @param \WP_User $item User for this row.
	 * @return string
	 */
	public function column_display_name( $item ): string {
		$name = '' !== $item->display_name ? $item->display_name : $item->user_login;
		$edit = get_edit_user_link( $item->ID );

		if ( $edit ) {
			return sprintf(
				'<strong><a href="%1$s">%2$s</a></strong><br /><span class="description">%3$s</span>',
				esc_url( $edit ),
				esc_html( $name ),
				esc_html( $item->user_login )
			);
		}

		return sprintf(
			'<strong>%1$s</strong><br /><span class="description">%2$s</span>',
			esc_html( $name ),
			esc_html( $item->user_login )
		);
	}

	/**
	 * Email column.
	 *
	 * @since 1.4.0
	 *
	 * @param \WP_User $item User for this row.
	 * @return string
	 */
	public function column_email( $item ): string {
		return sprintf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $item->user_email ) );
	}

	/**
	 * Role column.
	 *
	 * Reads $item->roles, which is why prepare_items() warms the user meta cache --
	 * capabilities live in meta and this would otherwise be a query per row.
	 *
	 * @since 1.4.0
	 *
	 * @param \WP_User $item User for this row.
	 * @return string
	 */
	public function column_role( $item ): string {
		$names = wp_roles()->get_names();
		$roles = array();

		foreach ( (array) $item->roles as $role ) {
			$roles[] = isset( $names[ $role ] ) ? translate_user_role( $names[ $role ] ) : $role;
		}

		if ( empty( $roles ) ) {
			return '<span class="description">' . esc_html__( 'None', 'wpmake-advance-user-avatar' ) . '</span>';
		}

		return esc_html( implode( ', ', $roles ) );
	}

	/**
	 * Actions column.
	 *
	 * @since 1.4.0
	 *
	 * @param \WP_User $item User for this row.
	 * @return string
	 */
	public function column_actions( $item ): string {
		$can_edit      = wpmake_aua_current_user_can_edit_avatar( $item->ID );
		$attachment_id = (int) get_user_meta( $item->ID, 'wpmake_advance_user_avatar_attachment_id', true );

		if ( ! $can_edit ) {
			return '<span class="description">' . esc_html__( 'Not editable by you', 'wpmake-advance-user-avatar' ) . '</span>';
		}

		$remove_style = $attachment_id > 0 ? '' : ' style="display:none"';

		return sprintf(
			'<div class="wpmake-aua-row-actions" data-user="%1$d">' .
			'<button type="button" class="button wpmake-aua-row-change" data-user="%1$d">%2$s</button>' .
			'<button type="button" class="button wpmake-aua-row-remove" data-user="%1$d"%3$s>%4$s</button>' .
			'</div>' .
			'<span class="wpmake-aua-row-status" data-user="%1$d" role="status" aria-live="polite"></span>',
			(int) $item->ID,
			esc_html__( 'Change', 'wpmake-advance-user-avatar' ),
			$remove_style, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- a fixed literal chosen above.
			esc_html__( 'Remove', 'wpmake-advance-user-avatar' )
		);
	}

	/**
	 * Fallback for any column without its own method.
	 *
	 * @since 1.4.0
	 *
	 * @param \WP_User $item        User for this row.
	 * @param string   $column_name Column being rendered.
	 * @return string
	 */
	public function column_default( $item, $column_name ): string {
		return isset( $item->$column_name ) ? esc_html( $item->$column_name ) : '';
	}

	/**
	 * Empty-state text.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function no_items(): void {
		esc_html_e( 'No users found.', 'wpmake-advance-user-avatar' );
	}

	/**
	 * Render the whole screen: heading, search, table.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function render_screen(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die(
				esc_html__( 'Sorry, you are not allowed to manage other users\' avatars.', 'wpmake-advance-user-avatar' ),
				esc_html__( 'Advanced User Avatar', 'wpmake-advance-user-avatar' ),
				array( 'response' => 403 )
			);
		}

		$this->prepare_items();
		?>
		<div class="wrap wpmake-aua-settings-page">
			<h1 class="wpmake-aua-page-title">
				<img src="<?php echo esc_url( WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/images/icon.png' ); ?>" width="50" height="50" alt="" />
				<?php esc_html_e( 'Users Avatar', 'wpmake-advance-user-avatar' ); ?>
			</h1>

			<?php Admin::render_tab_nav( 'avatars' ); ?>

			<form method="get">
				<?php
				// Carry the page and tab through search and pagination links.
				printf( '<input type="hidden" name="page" value="%s" />', esc_attr( Admin::MENU_SLUG ) );
				printf( '<input type="hidden" name="tab" value="%s" />', 'avatars' );

				$this->search_box( esc_html__( 'Search users', 'wpmake-advance-user-avatar' ), 'wpmake-aua-user-search' );
				$this->display();
				?>
			</form>
		</div>
		<?php
	}
}
