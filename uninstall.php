<?php
/**
 * Advanced User Avatar uninstall.
 *
 * Runs only when the plugin is deleted from the Plugins screen -- not on
 * deactivation. Does nothing at all unless the site owner has explicitly switched
 * on "Delete all avatars and settings when the plugin is deleted" under Advanced.
 *
 * That default matters. A deactivate-reinstall cycle is a routine thing to do while
 * troubleshooting, and silently destroying every customer's photo when it happens
 * is far worse than leaving some orphaned rows behind.
 *
 * @package WPMakeAdvanceUserAvatar
 * @since   1.4.0
 */

// Only ever reached through WordPress's own uninstall routine.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$wpmake_aua_settings = get_option( 'wpmake_advance_user_avatar_settings', array() );

// The switch is off, absent, or the option was never written. Leave everything.
if ( empty( $wpmake_aua_settings['delete_data_on_uninstall'] ) ) {
	return;
}

/**
 * Delete the attachments this plugin created, and their files.
 *
 * Scoped by file path rather than by which users point at one, for two reasons.
 * An avatar the user later removed is still the plugin's file to clean up, and it
 * is referenced by nobody. And an avatar chosen from the Media Library is *not*
 * the plugin's file -- since 1.4.0 a user can pick an existing image, which may
 * well be in use elsewhere on the site. Deleting that because somebody once used
 * it as a profile picture would destroy the site owner's own media.
 *
 * @since 1.4.0
 *
 * @return void
 */
function wpmake_aua_uninstall_delete_attachments() {
	$batch = 50;

	while ( true ) {
		$attachment_ids = get_posts(
			array(
				'post_type'              => 'attachment',
				'post_status'            => 'any',
				'posts_per_page'         => $batch,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- a one-off on delete, and there is no other way to scope by file path.
					array(
						'key'     => '_wp_attached_file',
						'value'   => 'wpmake-advance-user-avatar/',
						'compare' => 'LIKE',
					),
				),
			)
		);

		if ( empty( $attachment_ids ) ) {
			break;
		}

		$deleted = 0;

		foreach ( $attachment_ids as $attachment_id ) {
			// true: delete the files as well as the database rows.
			if ( wp_delete_attachment( $attachment_id, true ) ) {
				++$deleted;
			}
		}

		/*
		 * Nothing in this batch could be deleted -- a permissions problem, most
		 * likely. Without this the query would return the same rows for ever.
		 */
		if ( 0 === $deleted ) {
			break;
		}
	}
}

/**
 * Remove the plugin's upload directory, if nothing else is left in it.
 *
 * Never recursive. If a file is still there it belongs to somebody else, or a
 * deletion failed, and either way it is not this function's business to remove it.
 *
 * @since 1.4.0
 *
 * @return void
 */
function wpmake_aua_uninstall_remove_directory() {
	$upload_dir = wp_upload_dir();

	if ( ! empty( $upload_dir['error'] ) ) {
		return;
	}

	$path = trailingslashit( $upload_dir['basedir'] ) . 'wpmake-advance-user-avatar';

	if ( ! is_dir( $path ) ) {
		return;
	}

	// The silence-is-golden file the plugin drops on activation is its own.
	$index = trailingslashit( $path ) . 'index.php';

	if ( file_exists( $index ) ) {
		wp_delete_file( $index );
	}

	$remaining = array_diff( (array) scandir( $path ), array( '.', '..' ) );

	if ( empty( $remaining ) ) {
		// Not wp_delete_file(): this is a directory.
		@rmdir( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- nothing to do if it fails, and the filesystem API is not loaded during uninstall.
	}
}

/**
 * Clear the per-user transient the profile screen leaves behind on a failed save.
 *
 * Keyed by the editing user's ID, so there is no single name to delete. Walking the
 * user list is the only way to clear them without raw SQL. They expire after a
 * minute anyway, so this is tidiness rather than necessity.
 *
 * @since 1.4.0
 *
 * @return void
 */
function wpmake_aua_uninstall_delete_profile_transients() {
	$paged = 1;

	do {
		$user_ids = get_users(
			array(
				'fields' => 'ID',
				'number' => 200,
				'paged'  => $paged,
			)
		);

		foreach ( $user_ids as $user_id ) {
			delete_transient( 'wpmake_aua_profile_error_' . $user_id );
		}

		++$paged;
	} while ( ! empty( $user_ids ) );
}

wpmake_aua_uninstall_delete_attachments();
wpmake_aua_uninstall_remove_directory();
wpmake_aua_uninstall_delete_profile_transients();

/*
 * User meta, across every user, without walking them one at a time.
 *
 * The avatar reference, plus the bulk manager's rows-per-page screen option, which
 * WordPress stores as user meta.
 */
delete_metadata( 'user', 0, 'wpmake_advance_user_avatar_attachment_id', '', true );
delete_metadata( 'user', 0, 'wpmake_aua_users_per_page', '', true );

delete_transient( 'wpmake_aua_review_notice_snoozed' );

/*
 * Every option this plugin writes.
 *
 * `bp-disable-avatar-uploads` is deliberately absent. The plugin writes to it when
 * the BuddyPress integration is toggled, but it is BuddyPress's own option and
 * deleting it here would change another plugin's configuration.
 */
$wpmake_aua_options = array(
	'wpmake_advance_user_avatar_settings',
	'wpmake_advance_user_avatar_admin_footer_text_rated',
	'wpmake_aua_activated',
	'wpmake_aua_updated_at',
	'wpmake_aua_woo_migrated_v2',
	'wpmake_aua_review_notice_dismissed',
);

foreach ( $wpmake_aua_options as $wpmake_aua_option ) {
	delete_option( $wpmake_aua_option );
}
