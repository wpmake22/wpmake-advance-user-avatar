<?php
/**
 * WPMakeAdvanceUserAvatar CoreFunctions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @author   WPMake
 * @category Core
 * @package  WPMakeAdvanceUserAvatar/Handler
 * @version  1.0.0
 */

defined( 'ABSPATH' ) || exit;

/*
 * Resolve avatars through `pre_get_avatar_data` rather than `get_avatar`.
 *
 * `get_avatar` only fires for get_avatar(), so anything calling get_avatar_url()
 * directly -- the REST /wp/v2/users response, the core Avatar block, block themes,
 * WooCommerce Blocks and most React admin UI -- got Gravatar back regardless of
 * what the user had uploaded. `pre_get_avatar_data` sits underneath all of them.
 *
 * Core short-circuits as soon as $args['url'] is set, and it does so *before* it
 * hashes the user's email address, so on a hit no hashed email is built at all.
 * See wp-includes/link-template.php: the filter fires at line 4492, the early
 * return is at 4494, and the SHA-256 hash is not reached until line 4548.
 */
add_filter( 'pre_get_avatar_data', 'wpmake_aua_pre_get_avatar_data', 99, 2 );

if ( ! function_exists( 'wpmake_aua_pre_get_avatar_data' ) ) {
	/**
	 * Point WordPress at the user's uploaded avatar, at the size it asked for.
	 *
	 * @since 1.4.0
	 *
	 * @param array $args        Avatar arguments, already normalised by core.
	 * @param mixed $id_or_email ID, email, or object identifying the user.
	 * @return array
	 */
	function wpmake_aua_pre_get_avatar_data( $args, $id_or_email ) {
		/*
		 * Another callback has already decided this avatar. `isset` is the same test
		 * core uses to short-circuit, so a deliberate `'url' => false` from another
		 * plugin counts as a decision and is left alone.
		 */
		if ( isset( $args['url'] ) ) {
			return $args;
		}

		// The caller explicitly asked for the default avatar, e.g. the previews on
		// Settings > Discussion. Overriding that would misrepresent the setting.
		if ( ! empty( $args['force_default'] ) ) {
			return $args;
		}

		$user_id = wpmake_aua_resolve_user_id( $id_or_email );

		if ( ! $user_id ) {
			return $args;
		}

		$size = isset( $args['size'] ) ? (int) $args['size'] : 96;
		$url  = wpmake_aua_get_avatar_url( $user_id, $size );

		if ( ! $url ) {
			return $args;
		}

		$args['url'] = $url;

		// Core initialises found_avatar to false before this filter runs and adds an
		// `avatar-default` class when it is still false. Without this the uploaded
		// avatar renders with the class meaning "this user has no avatar".
		$args['found_avatar'] = true;

		return $args;
	}
}

if ( ! function_exists( 'wpmake_aua_resolve_user_id' ) ) {
	/**
	 * Resolve the identifier WordPress passes around avatars into a user ID.
	 *
	 * Mirrors the resolution in core's get_avatar_data(), because this now runs
	 * ahead of it rather than after it.
	 *
	 * @since 1.4.0
	 *
	 * @param mixed $id_or_email ID, email, or object identifying the user.
	 * @return int User ID, or 0 when the identifier does not resolve to one.
	 */
	function wpmake_aua_resolve_user_id( $id_or_email ) {
		$user = false;

		// Core normalises a bare comment object to WP_Comment before resolving it.
		if ( is_object( $id_or_email ) && isset( $id_or_email->comment_ID ) ) {
			$id_or_email = get_comment( $id_or_email );
		}

		if ( is_numeric( $id_or_email ) ) {
			$user = get_user_by( 'id', absint( $id_or_email ) );
		} elseif ( is_string( $id_or_email ) ) {
			// A Gravatar hash is not an address; looking one up would always miss.
			if ( str_contains( $id_or_email, '@md5.gravatar.com' ) || str_contains( $id_or_email, '@sha256.gravatar.com' ) ) {
				return 0;
			}

			$user = get_user_by( 'email', $id_or_email );
		} elseif ( $id_or_email instanceof WP_User ) {
			// User Object.
			$user = $id_or_email;
		} elseif ( $id_or_email instanceof WP_Post ) {
			// Post Object.
			$user = get_user_by( 'id', (int) $id_or_email->post_author );
		} elseif ( $id_or_email instanceof WP_Comment ) {
			/*
			 * Core refuses avatars for comment types outside the allowed list, and it
			 * does so *after* this filter. Running ahead of that check means pingbacks
			 * and trackbacks would get an avatar here that core would have denied.
			 */
			if ( ! is_avatar_comment_type( get_comment_type( $id_or_email ) ) ) {
				return 0;
			}

			if ( ! empty( $id_or_email->user_id ) ) {
				$user = get_user_by( 'id', (int) $id_or_email->user_id );
			}
		}

		if ( ! $user || is_wp_error( $user ) ) {
			return 0;
		}

		return (int) $user->ID;
	}
}

if ( ! function_exists( 'wpmake_aua_get_avatar_url' ) ) {
	/**
	 * URL of a user's uploaded avatar at the requested pixel size.
	 *
	 * Asks for the generated thumbnail closest to $size rather than the original
	 * upload. A 32px admin bar avatar used to download the full 500x500 file, which
	 * made the plugin's own "store in thumbnail sizes" setting decorative.
	 *
	 * @since 1.4.0
	 *
	 * @param int $user_id User ID.
	 * @param int $size    Requested square size in pixels.
	 * @return string Avatar URL, or an empty string when the user has not uploaded one.
	 */
	function wpmake_aua_get_avatar_url( $user_id, $size = 96 ) {
		$user_id = (int) $user_id;

		if ( $user_id <= 0 ) {
			return '';
		}

		$attachment_id = (int) get_user_meta( $user_id, 'wpmake_advance_user_avatar_attachment_id', true );

		if ( $attachment_id <= 0 ) {
			return '';
		}

		$size = (int) $size;

		if ( $size <= 0 ) {
			$size = 96;
		}

		$url = wp_get_attachment_image_url( $attachment_id, array( $size, $size ) );

		return $url ? $url : '';
	}
}

if ( ! function_exists( 'wpmake_aua_current_user_can_edit_avatar' ) ) {
	/**
	 * Whether the current user may set or clear a given user's avatar.
	 *
	 * Every avatar write goes through this. Until 1.4.0 the plugin was self-service
	 * only -- both AJAX handlers hardcoded get_current_user_id() -- so there was
	 * nothing to authorise. Now that a request can name a user, there is.
	 *
	 * @since 1.4.0
	 *
	 * @param int $user_id User whose avatar is being changed.
	 * @return bool
	 */
	function wpmake_aua_current_user_can_edit_avatar( $user_id ) {
		$user_id         = (int) $user_id;
		$current_user_id = get_current_user_id();

		if ( $user_id <= 0 || $current_user_id <= 0 ) {
			return false;
		}

		// The plugin's original contract: a logged-in user owns their own avatar,
		// whatever else they can or cannot do. A subscriber has no edit_users
		// capability and must still be able to upload their own picture.
		if ( $current_user_id === $user_id ) {
			return true;
		}

		/*
		 * Core maps `edit_user` straight onto `edit_users` without checking that the
		 * target exists, so an administrator "can edit" a user ID that was never
		 * issued. Callers use this to decide whether to render a form or accept a
		 * write, so answer for a real user or not at all.
		 */
		if ( ! get_userdata( $user_id ) ) {
			return false;
		}

		/*
		 * Anyone else's avatar is part of editing that user, so defer to core's meta
		 * capability rather than inventing one. `edit_user` is mapped per target: on
		 * multisite it resolves to do_not_allow unless the caller has
		 * manage_network_users, so a site administrator does not quietly gain the
		 * ability to edit users from elsewhere on the network.
		 * See wp-includes/capabilities.php line 75.
		 */
		return current_user_can( 'edit_user', $user_id );
	}
}

if ( ! function_exists( 'wpmake_aua_set_user_avatar' ) ) {
	/**
	 * Store an attachment as a user's avatar.
	 *
	 * Does no capability checking of its own. Callers handling a request must gate
	 * on wpmake_aua_current_user_can_edit_avatar() first; the two are kept separate
	 * so that code running without a current user -- an importer, WP-CLI, a
	 * migration -- can still set an avatar.
	 *
	 * Attachments are deliberately allowed to be shared between users. Nothing in
	 * the plugin deletes an attachment when an avatar is removed: removal clears the
	 * meta and leaves the file in the media library, so a second user pointing at
	 * the same ID cannot have their avatar pulled out from under them. If the
	 * attachment is deleted from the media library outright, everyone holding it
	 * falls back to Gravatar, because wpmake_aua_get_avatar_url() returns an empty
	 * string as soon as wp_get_attachment_image_url() stops resolving.
	 *
	 * @since 1.4.0
	 *
	 * @param int $user_id       User to set the avatar for.
	 * @param int $attachment_id Attachment to use.
	 * @return bool True when the user's avatar is that attachment afterwards.
	 */
	function wpmake_aua_set_user_avatar( $user_id, $attachment_id ) {
		$user_id       = (int) $user_id;
		$attachment_id = (int) $attachment_id;

		if ( $user_id <= 0 || $attachment_id <= 0 ) {
			return false;
		}

		// Meta on a user that does not exist would never be read back, and nothing
		// would ever clean it up. Refuse rather than leave an orphan row behind.
		if ( ! get_userdata( $user_id ) ) {
			return false;
		}

		$attachment = get_post( $attachment_id );

		if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
			return false;
		}

		// A PDF or a zip would render as a broken image on every surface M01 put
		// avatars on, with nothing to explain why.
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return false;
		}

		$previous = (int) get_user_meta( $user_id, 'wpmake_advance_user_avatar_attachment_id', true );

		/*
		 * update_user_meta() returns false when the stored value already matches,
		 * which is indistinguishable from a failed write. Only write when there is
		 * something to change.
		 */
		if ( $previous !== $attachment_id && ! update_user_meta( $user_id, 'wpmake_advance_user_avatar_attachment_id', $attachment_id ) ) {
			return false;
		}

		/**
		 * Fires once a user's avatar has been set.
		 *
		 * Also fires when the attachment was already the user's avatar, since the
		 * end state is the same either way.
		 *
		 * @since 1.4.0
		 *
		 * @param int $user_id       User the avatar belongs to.
		 * @param int $attachment_id Attachment now in use.
		 */
		do_action( 'wpmake_aua_avatar_set', $user_id, $attachment_id );

		return true;
	}
}

if ( ! function_exists( 'wpmake_aua_remove_user_avatar' ) ) {
	/**
	 * Clear a user's avatar.
	 *
	 * Capability checking is the caller's job, as with wpmake_aua_set_user_avatar().
	 *
	 * The attachment itself stays in the media library. That has always been the
	 * behaviour, and it is what makes an attachment safe to share between users.
	 *
	 * @since 1.4.0
	 *
	 * @param int $user_id User to clear the avatar for.
	 * @return bool True when the user has no avatar afterwards.
	 */
	function wpmake_aua_remove_user_avatar( $user_id ) {
		$user_id = (int) $user_id;

		if ( $user_id <= 0 || ! get_userdata( $user_id ) ) {
			return false;
		}

		$stored = get_user_meta( $user_id, 'wpmake_advance_user_avatar_attachment_id', true );

		/*
		 * Blanked rather than deleted, which is what the remove handler has always
		 * done. Skipped when the value is already empty, both because
		 * update_user_meta() reports that unchanged write as a failure and because
		 * there is no reason to create a row for a user who never had one.
		 */
		if ( '' !== $stored && ! update_user_meta( $user_id, 'wpmake_advance_user_avatar_attachment_id', '' ) ) {
			return false;
		}

		/**
		 * Fires once a user's avatar has been removed.
		 *
		 * @since 1.4.0
		 *
		 * @param int $user_id       User the avatar belonged to.
		 * @param int $attachment_id Attachment that was in use, or 0 if there was none.
		 *                           It is not deleted; it stays in the media library.
		 */
		do_action( 'wpmake_aua_avatar_removed', $user_id, (int) $stored );

		return true;
	}
}

if ( ! function_exists( 'wpmake_aua_get_allowed_mimes' ) ) {
	/**
	 * Image mime types this site accepts for an avatar.
	 *
	 * Lives here rather than in Ajax because the browser cropper needs exactly the
	 * same list: it decides what format to encode to, and encoding to a type the
	 * server refuses is what made cropped uploads fail when JPEG was unchecked.
	 * Two copies of this list would be two chances to disagree.
	 *
	 * @since 1.4.0
	 *
	 * @return array Mime type strings.
	 */
	function wpmake_aua_get_allowed_mimes() {
		$supported = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );
		$options   = get_option( 'wpmake_advance_user_avatar_settings', array() );

		$configured = isset( $options['allowed_file_type'] ) && is_array( $options['allowed_file_type'] )
			? $options['allowed_file_type']
			: array();

		// The settings screen offers 'image/jpg', which is not a real mime type.
		$configured = array_map(
			static function ( $mime ) {
				return 'image/jpg' === $mime ? 'image/jpeg' : $mime;
			},
			$configured
		);

		$allowed = array_values( array_unique( array_intersect( $configured, $supported ) ) );

		return empty( $allowed ) ? array( 'image/jpeg', 'image/png', 'image/gif' ) : $allowed;
	}
}

if ( ! function_exists( 'wpmake_aua_get_uploaded_image_size' ) ) {
	/**
	 * Configured output size for an uploaded avatar.
	 *
	 * @since 1.4.0
	 *
	 * @return array {
	 *     @type int $width  Width in pixels.
	 *     @type int $height Height in pixels.
	 * }
	 */
	function wpmake_aua_get_uploaded_image_size() {
		$options = get_option( 'wpmake_advance_user_avatar_settings', array() );
		$size    = isset( $options['uploaded_image_size'] ) && is_array( $options['uploaded_image_size'] )
			? $options['uploaded_image_size']
			: array();

		$width  = isset( $size['width'] ) ? absint( $size['width'] ) : 0;
		$height = isset( $size['height'] ) ? absint( $size['height'] ) : 0;

		return array(
			'width'  => $width > 0 ? $width : 500,
			'height' => $height > 0 ? $height : 500,
		);
	}
}

if ( ! function_exists( 'wpmake_aua_enqueue_frontend_assets' ) ) {
	/**
	 * Enqueue the front-end avatar assets.
	 *
	 * Frontend::load_scripts() registers the handles on every front-end request but
	 * enqueues nothing. This turns them on, and is called both from the
	 * wp_enqueue_scripts gate and from each render point, so a widget, page builder
	 * or template part that the gate cannot see still gets its assets.
	 *
	 * Enqueuing the same handle twice is a no-op in WordPress, so the two paths do
	 * not conflict.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	function wpmake_aua_enqueue_frontend_assets() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		wp_enqueue_script( 'wpmake-advance-user-avatar-frontend-script' );
		wp_enqueue_style( 'wpmake-advance-user-avatar-frontend-style' );

		// The webcam library is the single largest asset and most sites never
		// capture from a camera, so it follows its own setting.
		$options = get_option( 'wpmake_advance_user_avatar_settings', array() );

		if ( ! empty( $options['capture_picture'] ) ) {
			wp_enqueue_script( 'wpmake-advance-user-avatar-webcam-script' );
		}
	}
}

if ( ! function_exists( 'wpmake_aua_can_choose_from_media_library' ) ) {
	/**
	 * Whether the front-end uploader should offer the Media Library.
	 *
	 * Two conditions, and the capability is not optional. The setting is the site
	 * owner's choice; `upload_files` is whether there is a library to choose from at
	 * all. A subscriber without it gets an empty grid, which reads as a broken button
	 * rather than an empty library -- so they are not shown one however the setting
	 * is set.
	 *
	 * The template, the asset gate and the localised script data all need the same
	 * answer, which is why it lives here rather than being re-derived three times.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	function wpmake_aua_can_choose_from_media_library() {
		$options = get_option( 'wpmake_advance_user_avatar_settings', array() );

		if ( empty( $options['media_library'] ) ) {
			return false;
		}

		return current_user_can( 'upload_files' );
	}
}

if ( ! function_exists( 'wpmake_aua_enqueue_media_picker' ) ) {
	/**
	 * Load the media modal, only where the uploader is actually rendering.
	 *
	 * The media modal is a large dependency to put on a front-end page -- Backbone,
	 * the media views and their templates -- so wp_enqueue_media() is deliberately not
	 * part of wpmake_aua_enqueue_frontend_assets(). That runs for the avatar display
	 * shortcode too, which has no picker on it.
	 *
	 * Safe to call during the_content: wp_enqueue_media() prints its templates on
	 * wp_footer (see wp-includes/media.php line 5314), which has not fired yet.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	function wpmake_aua_enqueue_media_picker() {
		if ( ! wpmake_aua_can_choose_from_media_library() ) {
			return;
		}

		wp_enqueue_media();
	}
}

if ( ! function_exists( 'wpmake_aua_avatar_subsizes' ) ) {
	/**
	 * Extra square sizes generated for avatar uploads.
	 *
	 * The "Store in thumbnail sizes" setting has always described these three
	 * variants, but nothing ever generated them: the upload path only asked for the
	 * sizes the site already had registered. A 32px avatar therefore landed on
	 * whichever registered size happened to be smallest.
	 *
	 * These are deliberately not registered with add_image_size(). That would add
	 * three sub-sizes to every image uploaded anywhere on the site, which on a store
	 * with a large media library is a real cost for no benefit. They are injected for
	 * the avatar upload only, and matched back by dimension rather than by name.
	 *
	 * @since 1.4.0
	 *
	 * @return array Size definitions keyed by name.
	 */
	function wpmake_aua_avatar_subsizes() {
		return apply_filters(
			'wpmake_aua_avatar_subsizes',
			array(
				'wpmake_aua_32' => array(
					'width'  => 32,
					'height' => 32,
					'crop'   => true,
				),
				'wpmake_aua_64' => array(
					'width'  => 64,
					'height' => 64,
					'crop'   => true,
				),
				'wpmake_aua_96' => array(
					'width'  => 96,
					'height' => 96,
					'crop'   => true,
				),
			)
		);
	}
}

if ( ! function_exists( 'wpmake_aua_add_avatar_subsizes' ) ) {
	/**
	 * Add the avatar sub-sizes to one wp_generate_attachment_metadata() run.
	 *
	 * Hooked and unhooked around the avatar upload rather than left on, so no other
	 * upload on the site grows extra files.
	 *
	 * @since 1.4.0
	 *
	 * @param array $sizes Sizes WordPress is about to generate.
	 * @return array
	 */
	function wpmake_aua_add_avatar_subsizes( $sizes ) {
		return array_merge( (array) $sizes, wpmake_aua_avatar_subsizes() );
	}
}

if ( ! function_exists( 'wpmake_advance_user_avatar_locate_template' ) ) {
	/**
	 * Locate a plugin template, letting the active theme override it.
	 *
	 * Checked in order:
	 *   yourtheme/wpmake-advance-user-avatar/$template_name
	 *   yourtheme/$template_name
	 *   wp-content/plugins/wpmake-advance-user-avatar/templates/$template_name
	 *
	 * @since 1.3.0
	 *
	 * @param string $template_name Template file name, e.g. wpmake-advance-user-avatar-page.php.
	 * @return string Absolute path of the template to load.
	 */
	function wpmake_advance_user_avatar_locate_template( $template_name ) {
		$template_name = ltrim( $template_name, '/' );
		$default       = WPMAKE_ADVANCE_USER_AVATAR_TEMPLATE_PATH . '/' . $template_name;

		/**
		 * Filter the theme sub-directory templates are looked up in.
		 *
		 * @since 1.3.0
		 *
		 * @param string $directory Sub-directory name, without slashes.
		 */
		$directory = apply_filters( 'wpmake_advance_user_avatar_template_directory', 'wpmake-advance-user-avatar' );

		$template = locate_template(
			array(
				trailingslashit( $directory ) . $template_name,
				$template_name,
			)
		);

		if ( ! $template ) {
			$template = $default;
		}

		/**
		 * Filter the resolved template path.
		 *
		 * @since 1.3.0
		 *
		 * @param string $template      Absolute path of the template to load.
		 * @param string $template_name Template file name.
		 */
		$template = apply_filters( 'wpmake_advance_user_avatar_locate_template', $template, $template_name );

		// Never hand back a path that cannot be included -- fall back to the shipped file.
		return file_exists( $template ) ? $template : $default;
	}
}

if ( ! function_exists( 'wpmake_aua_get_allowed_html_tags' ) ) {
	/**
	 * WPMAKE AUA KSES.
	 *
	 * @since 1.0.0
	 */
	function wpmake_aua_get_allowed_html_tags() {

		$post_tags = wp_kses_allowed_html( 'post' );

		return wp_parse_args(
			$post_tags,
			array(
				'input'    => array(
					'type'        => true,
					'name'        => true,
					'value'       => true,
					'checked'     => true,
					'class'       => true,
					'placeholder' => true,
				),
				'select'   => array(
					'name'     => true,
					'id'       => true,
					'class'    => true,
					'multiple' => true,
				),
				'option'   => array(
					'value'    => true,
					'selected' => true,
					'class'    => true,
				),
				'textarea' => array(
					'style' => true,
				),
				'label'    => array(
					'for' => array(),
				),
				'p'        => array(
					'class' => true,
				),
			)
		);
	}
}

/**
 * Print js script by properly sanitizing and escaping.
 *
 * @since 1.0.2
 * Output any queued javascript code in the footer.
 */
function wpmake_aua_print_js() {
	global $wpmake_aua_queued_js;

	if ( ! empty( $wpmake_aua_queued_js ) ) {
		// Sanitize.
		$wpmake_aua_queued_js = wp_check_invalid_utf8( $wpmake_aua_queued_js );
		$wpmake_aua_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $wpmake_aua_queued_js );
		$wpmake_aua_queued_js = str_replace( "\r", '', $wpmake_aua_queued_js );

		$js = "<!-- WPMake AUA JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $wpmake_aua_queued_js });\n</script>\n";

		echo wp_kses( apply_filters( 'wpmake_aua_queued_js', $js ), array( 'script' => array( 'type' => true ) ) );

		unset( $wpmake_aua_queued_js );
	}
}

/**
 * Enqueue WPMake AUA js.
 *
 * @since 1.0.2
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code Code to enqueue.
 */
function wpmake_aua_enqueue_js( $code ) {
	global $wpmake_aua_queued_js;

	if ( empty( $wpmake_aua_queued_js ) ) {
		$wpmake_aua_queued_js = '';
	}

	$wpmake_aua_queued_js .= "\n" . $code . "\n";
}


if ( ! function_exists( 'wpmake_aua_review_notice_content' ) ) {

	/**
	 * Review Content.
	 *
	 * @return string
	 */
	function wpmake_aua_review_notice_content() {
		return wp_kses_post(
			sprintf(
				"<p>%s</p><p class='extra-pad'>%s</p>",
				__( 'We hope you’re enjoying a great experience with the <strong>Advanced User Avatar</strong> plugin! We kindly request you to consider leaving a positive review for the plugin.', 'wpmake-advance-user-avatar' ),
				__(
					'Your review motivates us to continue providing regular updates with new features and bug fixes, ensuring the plugin works seamlessly for you. It also supports us in offering free assistance, just as we always have. <span class="dashicons dashicons-smiley smile-icon"></span><br>',
					'wpmake-advance-user-avatar'
				)
			)
		);
	}
}

if ( ! function_exists( 'wpmake_aua_check_activation_date' ) ) {

	/**
	 * Check for plugin activation date.
	 *
	 * @param int $days Number of days to check for activation.
	 *
	 * @since 1.0.2
	 *
	 * @return bool
	 */
	function wpmake_aua_check_activation_date( $days ) {

		// Plugin Activation Time.
		$activation_date  = get_option( 'wpmake_aua_activated' );

		$days_to_validate = strtotime( 'now' ) - $days * DAY_IN_SECONDS;
		$days_to_validate = wp_date( 'Y-m-d', $days_to_validate );

		if ( ! empty( $activation_date ) ) {
			if ( $activation_date <= $days_to_validate ) {
				return true;
			}
		}

		return false;
	}
}
