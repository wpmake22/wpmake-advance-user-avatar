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
 * @since   2.0.0
 */

// Only ever reached through WordPress's own uninstall routine.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Whether this site asked for its data to be deleted.
 *
 * Options are per-site on a network, so every site answers for itself. A site that
 * left the switch off keeps everything, even when a sibling site opted in.
 *
 * @since 2.0.0
 *
 * @return bool
 */
function wpmake_aua_uninstall_opted_in() {
	$settings = get_option( 'wpmake_advance_user_avatar_settings', array() );

	return ! empty( $settings['delete_data_on_uninstall'] );
}

/**
 * Where this site keeps its avatars.
 *
 * The upload handler runs the directory through
 * `wpmake_advance_user_avatar_upload_url`, so a site can have moved it. The plugin
 * itself is not loaded during uninstall, but a theme or mu-plugin that registered
 * that filter still is -- so asking for it here is meaningful, and hardcoding the
 * default would quietly leave a relocated directory and all its files behind.
 *
 * @since 2.0.0
 *
 * @return array {
 *     @type string $dir      Absolute path of the avatar directory.
 *     @type string $relative Path relative to the uploads basedir, with a trailing
 *                            slash, for matching against _wp_attached_file.
 * }
 */
function wpmake_aua_uninstall_upload_paths() {
	$default_subdir = 'wpmake-advance-user-avatar';
	$upload_dir     = wp_upload_dir();

	if ( ! empty( $upload_dir['error'] ) ) {
		return array(
			'dir'      => '',
			'relative' => trailingslashit( $default_subdir ),
		);
	}

	$basedir = trailingslashit( $upload_dir['basedir'] );
	$dir     = apply_filters( 'wpmake_advance_user_avatar_upload_url', $upload_dir['basedir'] . '/' . $default_subdir );
	$dir     = untrailingslashit( (string) $dir );

	/*
	 * _wp_attached_file is stored relative to the uploads basedir, so the query below
	 * can only match a directory that lives inside it. A filter pointing somewhere
	 * else is out of reach; fall back to the default rather than matching nothing at
	 * all, and the directory removal still uses the real path.
	 */
	$relative = 0 === strpos( $dir . '/', $basedir )
		? substr( $dir . '/', strlen( $basedir ) )
		: trailingslashit( $default_subdir );

	return array(
		'dir'      => $dir,
		'relative' => $relative,
	);
}

/**
 * Delete the attachments this plugin created, and their files.
 *
 * Scoped by file path rather than by which users point at one, for two reasons.
 * An avatar the user later removed is still the plugin's file to clean up, and it
 * is referenced by nobody. And an avatar chosen from the Media Library is *not*
 * the plugin's file -- since 2.0.0 a user can pick an existing image, which may
 * well be in use elsewhere on the site. Deleting that because somebody once used
 * it as a profile picture would destroy the site owner's own media.
 *
 * @since 2.0.0
 *
 * @return void
 */
function wpmake_aua_uninstall_delete_attachments() {
	$batch    = 50;
	$relative = wpmake_aua_uninstall_upload_paths()['relative'];

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
						'value'   => $relative,
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
 * @since 2.0.0
 *
 * @return void
 */
function wpmake_aua_uninstall_remove_directory() {
	$path = wpmake_aua_uninstall_upload_paths()['dir'];

	if ( '' === $path || ! is_dir( $path ) ) {
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
 * @since 2.0.0
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

/**
 * Remove everything belonging to the current site.
 *
 * Attachments, files, the upload directory, this site's options and this site's
 * transients. Avatar references are user meta, which is global on a network, so they
 * are handled by the caller -- and only for users whose avatar came from a site that
 * opted in.
 *
 * @since 2.0.0
 *
 * @return void
 */
function wpmake_aua_uninstall_current_site() {
	wpmake_aua_uninstall_delete_attachments();
	wpmake_aua_uninstall_remove_directory();
	wpmake_aua_uninstall_delete_profile_transients();

	delete_transient( 'wpmake_aua_review_notice_snoozed' );

	/*
	 * `bp-disable-avatar-uploads` is deliberately absent. The plugin writes to it when
	 * the BuddyPress integration is toggled, but it is BuddyPress's own option and
	 * deleting it here would change another plugin's configuration.
	 */
	$options = array(
		'wpmake_advance_user_avatar_settings',
		'wpmake_advance_user_avatar_admin_footer_text_rated',
		'wpmake_aua_activated',
		'wpmake_aua_updated_at',
		'wpmake_aua_woo_migrated_v2',
		'wpmake_aua_review_notice_dismissed',
	);

	foreach ( $options as $option ) {
		delete_option( $option );
	}
}

/**
 * Clear the avatar reference of every user whose avatar came from a given site.
 *
 * The reference lives in global user meta, so it cannot simply be deleted for all
 * users: on a network, one site opting in must not wipe the avatars of users whose
 * pictures belong to a site that did not. The site is recorded alongside the ID
 * since 2.0.0; where it is absent the avatar belongs to the main site.
 *
 * @since 2.0.0
 *
 * @param int $blog_id Site whose avatars should be forgotten.
 * @return void
 */
function wpmake_aua_uninstall_forget_avatars_for_site( $blog_id ) {
	$blog_id  = (int) $blog_id;
	$is_main  = ( ! is_multisite() ) || (int) get_main_site_id() === $blog_id;
	$user_ids = get_users(
		array(
			'fields'       => 'ID',
			'number'       => -1,
			'blog_id'      => 0,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- a one-off on delete.
			'meta_key'     => 'wpmake_advance_user_avatar_attachment_id',
			'meta_compare' => 'EXISTS',
		)
	);

	foreach ( $user_ids as $user_id ) {
		$recorded = (int) get_user_meta( $user_id, 'wpmake_aua_avatar_blog_id', true );

		// No record means the main site, which is where a pre-2.0.0 avatar lives.
		if ( 0 === $recorded && ! $is_main ) {
			continue;
		}

		if ( $recorded > 0 && $recorded !== $blog_id ) {
			continue;
		}

		delete_user_meta( $user_id, 'wpmake_advance_user_avatar_attachment_id' );
		delete_user_meta( $user_id, 'wpmake_aua_avatar_blog_id' );
	}
}

if ( ! is_multisite() ) {
	if ( wpmake_aua_uninstall_opted_in() ) {
		wpmake_aua_uninstall_current_site();
		wpmake_aua_uninstall_forget_avatars_for_site( 1 );
		delete_metadata( 'user', 0, 'wpmake_aua_users_per_page', '', true );
	}

	return;
}

/*
 * A network. Every site answers for itself, and restore_current_blog() runs on every
 * path out of the loop so a failure part-way through cannot leave the request pointed
 * at the wrong site.
 */
$wpmake_aua_site_ids = get_sites(
	array(
		'fields' => 'ids',
		'number' => 0,
	)
);

$wpmake_aua_any_opted_in = false;

foreach ( $wpmake_aua_site_ids as $wpmake_aua_site_id ) {
	switch_to_blog( (int) $wpmake_aua_site_id );

	if ( wpmake_aua_uninstall_opted_in() ) {
		$wpmake_aua_any_opted_in = true;

		wpmake_aua_uninstall_current_site();
		wpmake_aua_uninstall_forget_avatars_for_site( (int) $wpmake_aua_site_id );
	}

	restore_current_blog();
}

/*
 * The bulk manager's screen option is global user meta for a screen that no longer
 * exists anywhere, since deleting a plugin removes it from the whole network.
 */
if ( $wpmake_aua_any_opted_in ) {
	delete_metadata( 'user', 0, 'wpmake_aua_users_per_page', '', true );
}
