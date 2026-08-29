# Claude Code Prompt Book — Advanced User Avatar

Paste one block per session. Each is self-contained.

**Rules of use**

- One milestone per session. Long sessions drift.
- Never paste the next milestone until you have read and approved the previous report.
- If a report has anything in **Deviations**, resolve it before moving on. Deviations compound.
- The `wpmake-aua-milestone` skill in `.claude/skills/` runs the loop. A session that has it will pick it up automatically.

**Dependency order** — M00 → {M01, M02, M03} → M04 → {M05 → M07, M06} → M08 → M09 → M10 → M11 → M12.
M01, M02 and M03 are independent of each other and can be done in any order. M04 gates every admin-side milestone.

---

## Ground truth for every milestone

Facts about this repo that milestones assume. Verify rather than trust; if one is stale, say so in Step 0.

- **Released:** 1.3.0. That release contains the Phase 0 security work plus the six commits made after the 1.2.2 tag. Milestones here target 1.4.0.
- **Namespace:** `WPMake\WPMakeAdvanceUserAvatar\`, PSR-4 onto `includes/`. Text domain `wpmake-advance-user-avatar`.
- **Prefixes:** `wpmake_aua_` for new functions and options, `wpmake_advance_user_avatar_` for the older public hooks and shortcodes. Do not rename existing ones — they are documented in readme.txt and in use on live sites.
- **Storage:** the avatar is an attachment ID in user meta `wpmake_advance_user_avatar_attachment_id`. Files live in `uploads/wpmake-advance-user-avatar/`. Settings are one array option, `wpmake_advance_user_avatar_settings`.
- **No test suite.** There is no PHPUnit, no Playwright spec directory, no CI beyond the release workflow. Do not write `composer test` into a report as if it ran. Verification is: `php -l`, `composer phpcs`, and hands-on checking against the local site. Where a milestone can be proved with the team's `themegrill-qa` skills (`/verify-fix`, `/write-spec`), say so and use them.
- **Build:** `npm install` once, then `grunt js` for the minified JS and `grunt css` for the compiled/RTL/minified CSS. `node_modules/` is not committed. **Any change to a file under `assets/js/` or `assets/css/` is not live until the matching grunt task has run** — the plugin loads `.min` files unless `SCRIPT_DEBUG` is on.
- **Local site:** `techhub.local`. WooCommerce, BuddyPress-adjacent plugins, WP-Members and BetterDocs are installed there. Start the site in Local before assuming `wp` will connect; a stopped site fails with "Error establishing a database connection".

---

## M00 — Toolchain repair

> Run once, before M01. Small, but everything downstream reports lint results.

```
Repair this plugin's development toolchain. No plugin logic changes.

The problem: composer.json defines a "phpcs" script as `phpcs -s -p` with no ruleset and no path, and there is no phpcs.xml on disk. Running `composer phpcs` fails with "You must supply at least one file or directory to process." Every milestone after this one is supposed to report a lint result, so fix this first.

Tasks:
1. Add phpcs.xml (that exact name — .distignore already lists `phpcs.xml`, so it stays out of the release zip; a `.dist` variant would ship).
   - Standard: WordPress-Core plus WordPress-Docs. Do NOT add WordPress-Extra yet; run it once and report the count separately so we can decide whether to adopt it.
   - Text domain: wpmake-advance-user-avatar. Prefixes: wpmake_aua, wpmake_advance_user_avatar, WPMake.
   - minimum_supported_wp_version 6.0, PHP 7.4+ compatibility.
   - Exclude: vendor, node_modules, assets/js/select2, assets/js/sweetalert2, assets/js/jquery-Jcrop, assets/js/webcam, assets/css/select2, assets/css/sweetalert2, languages, .wordpress-org.
   - Exclude the sniffs the codebase deliberately does not follow, each with a one-line comment saying why: WordPress.Files.FileName (PSR-4 class files), and whatever else is needed to get a clean baseline WITHOUT touching plugin code.
2. Make `composer phpcs` and `composer phpcbf` work with no arguments.
3. Run `npm install` and confirm `grunt js` and `grunt css` both complete. Report any task that fails and why — do not fix a broken grunt task by rewriting assets.
4. Report the current lint baseline: error and warning counts per file, and the count under WordPress-Extra as a separate figure.

Do NOT run phpcbf across the codebase. A mass reformat would bury every future diff. We want the ruleset and the baseline number, nothing else.

Report what the baseline is and which sniffs you excluded, then STOP.
```

---

## M01 — Avatar resolution & sizing

```
Implement MILESTONE 01 — Avatar resolution and sizing.

Goal: custom avatars appear everywhere WordPress renders one, at the right pixel size. Today they appear only where get_avatar() is called, always at full resolution.

Two defects, one cause. includes/Functions/CoreFunctions.php filters `get_avatar` only:
- Anything calling get_avatar_url() directly gets Gravatar back. That is the REST /users response, the core Avatar block, block themes, WooCommerce Blocks, and most React admin UI.
- The replacement markup uses wp_get_attachment_url(), the full-size original. A 32px admin-bar avatar downloads a 500x500 image, which makes the plugin's own "store in thumbnail sizes" setting decorative.

Build:
1. Replace the `get_avatar` filter with `pre_get_avatar_data`. Core short-circuits as soon as $args['url'] is set, so one filter covers get_avatar(), get_avatar_url(), REST, and the Avatar block. It also returns before the Gravatar hash is built, so no hashed email leaves the site even on the miss path — note that, it strengthens the readme's GDPR claim.
2. Extract the existing identifier resolution (numeric, string, WP_User, WP_Post, WP_Comment) into wpmake_aua_resolve_user_id(). Keep every branch; comment avatars depend on the WP_Comment one.
3. Resolve the URL with wp_get_attachment_image_url( $id, array( $size, $size ) ) using $args['size'].
4. Delete wpmake_advance_user_avatar_replace_gravatar_image() and wpmake_advance_user_avatar_build_avatar_html(). Core builds the img tag, including class merging and srcset, so the re-entrancy guard and the manual sprintf both go. Confirm the rendered markup still carries the classes the CSS expects before deleting anything.
5. Apply the same sized lookup in includes/Frontend/Frontend.php (BuddyPress and Better Messages) and in both files under templates/.

Acceptance:
- A user with a custom avatar shows it in: an admin-bar avatar, a comment on the front end, the Users list table, the REST /wp/v2/users response, and the core Avatar block in the editor.
- The admin-bar avatar requests a 32px file, not the 500px original. Prove it from the rendered src, not by reading the code.
- A user with no custom avatar still gets Gravatar exactly as before.
- Removing an avatar reverts to Gravatar with no cache staleness.

Verify: php -l, composer phpcs, then hands-on on techhub.local across the five surfaces above. Use Playwright MCP for the front-end ones.

Watch for: the WP_Comment branch (easiest to regress and least visible), and any place still calling the deleted functions — grep before you delete.

Report, then STOP.
```

---

## M02 — Conditional asset loading

```
Implement MILESTONE 02 — Conditional asset loading.

Goal: stop shipping ~1.9MB of plugin assets to pages that have no avatar widget on them.

Today includes/Frontend/Frontend.php::load_scripts() enqueues jQuery, webcam.js, Jcrop, SweetAlert and the frontend CSS on every front-end request, logged in or not. includes/Admin/Admin.php enqueues Select2 and the admin CSS/JS from its constructor rather than on admin_enqueue_scripts, so they load on every admin screen.

Build:
1. Frontend: change every wp_enqueue_* in load_scripts() to wp_register_*. Keep wp_localize_script — it attaches to a registered handle fine.
2. Add a needs_assets() check on wp_enqueue_scripts: logged in AND (the queried post has either shortcode, or has_block('wpmake-aua/user-avatar'), or we are on an integration surface — WooCommerce account page, BuddyPress member area, WP-Members profile).
3. Add a render-time fallback: wp_enqueue_script(...) inside Shortcodes::render_avatar_uploader(), Shortcodes::render_avatar() and Gutenberg::render(). This covers widgets, page builders and template parts that needs_assets() cannot see. Styles enqueued at render time print via wp_footer, which is acceptable — confirm it in the browser rather than assuming.
4. Split webcam.js out and enqueue it only when the capture_picture setting is on. It is the largest single file and most sites do not use it.
5. Admin: move the enqueue block out of Admin::init_hooks() into an admin_enqueue_scripts callback, gated on the plugin's own screen and (once M05 lands) the profile screens. The review notice's inline JS stays global — make it dependency-free rather than pulling the whole admin bundle in for it.

Acceptance:
- A logged-out visitor on the home page loads zero plugin JS and zero plugin CSS.
- A logged-in user on a page with no avatar widget loads zero plugin JS and zero plugin CSS.
- A page with the shortcode, the block, a widget placement, and the WooCommerce account page each load them correctly.
- webcam.js loads only when the setting is on.
- No admin screen except the plugin's own loads Select2.
- The review notice still works on every admin screen.

Verify: check the actual network panel via Playwright MCP on each case above. A code read is not sufficient evidence for this milestone.

Report the before/after transferred bytes for a logged-out home page load. Then STOP.
```

---

## M03 — Browser cropper: honour the size and format settings

```
Implement MILESTONE 03 — Make the browser cropper respect plugin settings.

Context, which is not obvious from the settings screen: the shipped uploader crops on a canvas in the browser and posts `cropped_image` as an empty string, so the server-side crop path in includes/Ajax.php is never reached in normal use. Everything the cropper does is decided in assets/js/frontend/wpmake-advance-user-avatar-frontend.js.

Two consequences, both live bugs:
1. The "Uploaded Image Size" setting is inert. The cropper hardcodes `size || 500` and every avatar comes out 500x500 regardless of what the admin set.
2. The canvas always emits toDataURL("image/jpeg", 0.92) as "avatar.jpeg". So if the site owner has unchecked JPEG in Allowed File Type, every cropped upload is rejected by the server as an invalid type — the client converts to a format the server is configured to refuse.

Build:
1. Pass uploaded_image_size and allowed_file_type through to JS in the wp_localize_script call in Frontend::load_scripts().
2. Use the configured width/height for the canvas output instead of the hardcoded 500. The cropper's UI is square; if the configured size is not square, decide and state whether to letterbox, centre-crop or force square — flag it in Step 0 rather than picking silently.
3. Choose the output mime from the allowed list: prefer the source image's own type when it is allowed, else the first allowed type, and name the File to match. Never emit a type the server will reject.
4. Preserve PNG transparency when the output is PNG — the canvas must not be pre-filled with white.
5. Run `grunt js` and commit the rebuilt .min.js. Verify the minified file actually changed.
6. While in this file: remove the now-ignored `valid_extension` and `max_uploaded_size` fields from the FormData. 1.3.0 made the server read both from settings; sending them implies they still matter.

Acceptance:
- Setting Uploaded Image Size to 250x250 produces a 250x250 avatar file.
- With only PNG allowed, cropping a PNG uploads successfully and keeps its transparency.
- With only WEBP allowed, cropping succeeds if the server supports WEBP, and fails with a clear message if it does not.
- With cropping off, behaviour is unchanged.
- The rebuilt .min.js is in the diff.

Verify: upload through the real UI on techhub.local for each case and inspect the resulting file in the media library — dimensions and mime, not just the preview.

Report, then STOP.
```

---

## M04 — Avatar service & capability model

```
Implement MILESTONE 04 — Avatar service and capability model.

This is small and gates M05, M06 and M07. Build it carefully; a permissive check here becomes a privilege escalation in the bulk manager.

Today the avatar is self-service only: Ajax::method_upload() and Ajax::remove_avatar() both hardcode get_current_user_id(). No admin can set or clear another user's avatar, which is the single biggest feature gap against competing plugins.

Build:
1. In includes/Functions/CoreFunctions.php (or a new includes/Avatar.php if that reads better — say which and why):
   - wpmake_aua_set_user_avatar( int $user_id, int $attachment_id ): bool
   - wpmake_aua_remove_user_avatar( int $user_id ): bool
   - wpmake_aua_current_user_can_edit_avatar( int $user_id ): bool — true when the target is the current user, or current_user_can( 'edit_user', $user_id ).
   Setting an avatar must validate that the attachment exists, is an image, and is not already assigned in a way that would break on delete. State what you decided about shared attachments.
2. Refactor both AJAX handlers to call these. Each accepts an optional user_id, defaulting to the current user, and refuses when wpmake_aua_current_user_can_edit_avatar() is false. The nonce check stays; the capability check is in addition to it, not instead.
3. Add wpmake_aua_avatar_set and wpmake_aua_avatar_removed action hooks, firing after the meta write with the user ID and attachment ID.

Acceptance:
- A subscriber can still set and remove their own avatar.
- A subscriber posting another user's ID is refused, and the refusal is a capability failure not a nonce failure.
- An administrator can set and remove any user's avatar.
- On multisite, a site administrator cannot edit a user they do not have edit_user over. (Do not build multisite support here — just confirm the capability check does not accidentally grant it.)

Verify: exercise the refusal path with a real crafted request, not a unit assertion about the code. Write it up in the report.

Report, then STOP.
```

---

## M05 — Users → Profile avatar field

```
Implement MILESTONE 05 — The avatar field on the WordPress user profile screen.

This is the most-expected location for an avatar plugin and the plugin has never had it. Depends on M04.

Build:
1. New includes/Admin/UserProfile.php.
2. show_user_profile and edit_user_profile — render a "Profile Picture" section: the current avatar, an upload control, a "Choose from Media Library" button (wp_enqueue_media + wp.media, image only, single), and Remove.
3. personal_options_update and edit_user_profile_update — save, gated on wpmake_aua_current_user_can_edit_avatar() and a nonce.
4. Filter user_profile_picture_description to replace core's "You can change your profile picture on Gravatar" text, which is actively misleading once this plugin is active. Keep it short and say where the avatar now comes from.
5. Admin assets load only on profile.php and user-edit.php. Extend the M02 admin gate rather than adding a second enqueue path.

Deliberately NOT here: the webcam and the crop modal. The admin context wants a plain media picker and a file input. If you find yourself pulling the frontend bundle into wp-admin, stop and reconsider.

Acceptance:
- A user sees and can change their own avatar at Users > Profile.
- An administrator sees and can change any user's avatar at Users > Edit User.
- An editor who cannot edit_user sees the field on their own profile only.
- The Gravatar description text is replaced.
- The avatar set here appears everywhere M01 made it appear.
- No plugin assets on any other admin screen.

Verify: hands-on on techhub.local as administrator, editor and subscriber. Use Playwright MCP.

Report, then STOP.
```

---

## M06 — Bulk avatar manager

```
Implement MILESTONE 06 — Bulk avatar manager.

The competitor's headline feature and our largest remaining gap. Depends on M04.

Build:
1. New includes/Admin/UsersTable.php extending WP_List_Table. Do not hand-roll a grid: WP_List_Table gives search, pagination, sorting and screen options for free and matches core styling, which is most of the work.
2. Surface it as a second tab on the existing Users > Users Avatar page. Do not add a top-level menu item.
3. Columns: avatar preview, display name, email, role, actions (Change / Remove).
4. Query with get_users( number, paged, search => '*term*', search_columns => array( 'user_login', 'user_email', 'display_name' ) ).
5. Actions post to the M04 endpoints with an explicit user_id. Gate the screen on the list_users capability.
6. PERFORMANCE, non-negotiable: after fetching the page of users, batch-warm the caches. update_meta_cache( 'user', $user_ids ), then one get_posts( post__in => $attachment_ids, post_type => 'attachment', posts_per_page => -1 ) to prime the post cache. The naive version does two queries per row; on a 20-row page that is 40 avoidable queries. Assert the query count before and after and put both numbers in the report.

Acceptance:
- All users listed with their current avatar, paginated at the screen-options value.
- Search by login, email and display name each work.
- Changing and removing an avatar from the table works without a page reload and without a full refresh of the table.
- A user who cannot list_users gets a capability error, not a blank screen.
- Rendering a 20-row page adds a bounded number of queries — state the number.

Verify: create at least 30 users on techhub.local, half with avatars, and drive the screen with Playwright MCP. Measure queries with SAVEQUERIES or Query Monitor and report the figure.

Report, then STOP.
```

---

## M07 — Media Library selection on the front end

```
Implement MILESTONE 07 — Optional Media Library selection in the front-end uploader.

Small, and deliberately conservative. Depends on M04 and M05 (reuse that media picker code).

Build:
1. A new setting under Upload & Image, default OFF: "Let users choose from the Media Library".
2. When on, the front-end uploader shows a "Choose from Media Library" button alongside Upload and Take Picture.
3. Gate on current_user_can( 'upload_files' ) regardless of the setting. Most subscribers cannot, and showing them a picker that returns an empty library is worse than not showing it.
4. wp_enqueue_media() on the front end is heavy. Load it only when the setting is on AND the capability check passes AND the uploader is actually rendering.

Acceptance:
- Default off: nothing changes for any existing site.
- On, as an administrator: the picker opens and the selected image becomes the avatar.
- On, as a subscriber without upload_files: no button, and no media scripts in the page source.

Report, then STOP.
```

---

## M08 — Uninstall & data cleanup

```
Implement MILESTONE 08 — Uninstall and data cleanup.

The plugin currently leaves everything behind on delete: six options, a transient, user meta on every user, and the whole uploads/wpmake-advance-user-avatar/ directory.

Build:
1. A setting under a new "Advanced" section, default OFF: "Delete all avatars and settings when the plugin is deleted". Default off is deliberate and must stay off — silently destroying every customer's photo on a deactivate-reinstall cycle is far worse than leaving orphaned rows.
2. uninstall.php guarded by WP_UNINSTALL_PLUGIN. When the setting is off, do nothing at all. When on, remove:
   - options wpmake_advance_user_avatar_settings, wpmake_aua_activated, wpmake_aua_updated_at, wpmake_aua_woo_migrated_v2, wpmake_aua_review_notice_dismissed, wpmake_advance_user_avatar_admin_footer_text_rated
   - transient wpmake_aua_review_notice_snoozed
   - user meta wpmake_advance_user_avatar_attachment_id, via delete_metadata( 'user', 0, ..., '', true )
   - the attachments and their files, via wp_delete_attachment( $id, true ), in batches
   - the uploads directory once empty
   Do not write raw SQL for any of this.
3. Verify the option list against the codebase rather than trusting the list above — grep for get_option, update_option, set_transient and add a line to the report naming anything found that is not listed here.

Acceptance:
- Delete with the setting off: every option, every avatar and every file survives.
- Delete with the setting on: nothing of the plugin's remains in wp_options, wp_usermeta, wp_posts or the uploads directory.
- Neither path errors, and neither touches another plugin's data.

Verify: run both branches on techhub.local against a site with at least three users holding avatars, and inspect the database directly afterwards.

Report, then STOP.
```

---

## M09 — Multisite

```
Implement MILESTONE 09 — Multisite support. Depends on M08.

Decide one thing first and state it in Step 0 before writing code: is an avatar per-site or network-wide? User meta is global in WordPress, but attachment IDs are NOT — an ID stored on site 3 resolves to a different post, or to nothing, on site 5. That mismatch is the whole milestone.

Investigate and report before implementing:
1. What the current code actually does on a network install today. Set an avatar on one site, then view that user on another, and describe what renders.
2. Whether to store a network-wide URL fallback beside the ID, key the meta per blog, or something else. Give the trade-offs and a recommendation.

Then, once the approach is approved:
3. Implement the chosen model.
4. Wrap the M08 uninstall cleanup in a get_sites() / switch_to_blog() loop, with restore_current_blog() in every exit path.
5. Confirm the M04 capability check behaves on a network — a site administrator must not gain edit rights over a user they do not have edit_user over.

Acceptance:
- Stated and documented behaviour when a user views their avatar across two sites in a network.
- Uninstall cleans every site, not just the current one.
- Single-site behaviour is completely unchanged.

Do NOT implement before the investigation is approved. Report the investigation, then STOP.
```

---

## M10 — Readme, positioning & screenshots

```
Implement MILESTONE 10 — readme.txt and store positioning. No plugin code.

The readme currently reads as a WooCommerce-only plugin. Its three tags (custom avatar, profile picture, user avatar) are generic, so the majority of searchers arrive from a generic query, read a WooCommerce pitch, and leave. The fix is not to drop the WooCommerce angle — that is a real differentiator — but to stop it being the only thing above the fold.

Tasks:
1. Open the Description with a short generic paragraph: custom profile pictures for any WordPress site, no Gravatar, no external requests. Then the WooCommerce section, which stays as strong as it is.
2. WP-Members ships as a supported integration but appears nowhere in readme.txt outside the changelog. Add a Description section and an FAQ entry matching the BuddyPress and Better Messages ones, and fix the Installation step that still reads "toggle WooCommerce and BuddyPress integrations".
3. Document the M04 capability model, the six template action hooks, the template override paths and the wpmake_advance_user_avatar_shortcode_atts filter in an FAQ entry for developers.
4. Add screenshots for the profile field (M05) and the bulk manager (M06) — these are the two that close the comparison against competing plugins. Update the numbered captions.
5. Re-check the "Product review avatars — coming in version 2" line is gone; that feature already ships as the product_reviews location.
6. Run `composer makepot` and confirm no new untranslated or concatenated strings.

Acceptance: someone searching "custom profile picture wordpress" reads the first two paragraphs and understands the plugin works for any site. Someone searching for WooCommerce avatars still gets the specific pitch.

Report, then STOP.
```

---

## M11 — Release 1.4.0

```
Implement MILESTONE 11 — Release preparation for 1.4.0.

Tasks:
1. Bump the version in three places and confirm all three agree: the plugin header, WPMAKE_ADVANCE_USER_AVATAR_VERSION, and readme.txt Stable tag. Update every @since 1.4.0 placeholder left by earlier milestones.
2. Write the 1.4.0 changelog, grouped Feature / Enhance / Fix / Tweak / Dev, matching the house style in readme.txt. Derive it from `git log <last tag>..HEAD` — do not write it from memory, and check nothing landed that no milestone reported.
3. `composer makepot` and commit the regenerated .pot.
4. `npm install && grunt js && grunt css`, and confirm every .min asset in the diff matches its source.
5. Verification runs on techhub.local: clean install; upgrade from 1.3.0 with existing avatars and settings in place; deactivate and reactivate; delete with the M08 setting off and then on.
6. Confirm .distignore excludes vendor, node_modules, tests, docs, .claude and dev config. Run `grunt zip` and report the resulting file count and size.
7. Confirm Tested up to matches the current WordPress release.

The upgrade-from-1.3.0 run is the one that matters most — every prior milestone was verified on a site that already had the plugin. Report each verification line by line with pass/fail. Then STOP.
```

---

## M12 — BetterDocs documentation

```
Implement MILESTONE 12 — user-facing documentation in BetterDocs, following the pattern already used for Post-Purchase Hub. Read ../../../../pph-docs/README.md first; it records the structure, the import procedure and the known quirks, and this milestone should produce something a person can import the same way.

Ship after M11 so every screenshot shows released behaviour.

Build, on techhub.local, using the BetterDocs plugin already installed there:
1. A doc_category "Advanced User Avatar" with three subsections, mirroring the Post-Purchase Hub shape:
   - Getting started — what the plugin does; installing and placing the uploader; finding the settings
   - Configuring the plugin — upload and image settings; cropping and webcam; WooCommerce display locations; BuddyPress, WP-Members and Better Messages integrations; styling with CSS custom properties and shortcode attributes
   - Everyday tasks — managing avatars for other users (the M06 bulk manager); setting an avatar from the profile screen; removing an avatar; data retention and uninstalling
2. Articles as Gutenberg block markup so they open and edit normally in the block editor after import.
3. Screenshots taken with Playwright MCP against the released build on techhub.local. Name each PNG for what it shows. Store the sources so any one can be re-taken.
4. An export at aua-docs/ in the techhub root, alongside pph-docs/:
   - export/advanced-user-avatar-docs.xml — the WXR, with attachments
   - export/after-import.php — the one-time fix-up that rebuilds article ordering, since BetterDocs stores it as source-site post IDs which cannot survive an import
   - screenshots/ — the source PNGs
   - README.md — what is here, the structure, the import procedure including the "Download and import file attachments" step, and any quirks you hit
5. Verify the export by importing it into a clean site or a second BetterDocs category and confirming images resolve and ordering is right. An unverified export is not a deliverable.

Known quirks to expect, from the Post-Purchase Hub run: BetterDocs renders subsections newest-term-first on the category grid regardless of ordering meta, and the built-in /docs archive does not show the category grid until a docs page is configured. Both are documented in pph-docs/README.md — confirm whether they still apply and update your README accordingly rather than copying the claim across.

Acceptance:
- Every setting on the Users > Users Avatar screen is explained somewhere in the docs.
- Both shortcodes, the block, and all six template action hooks are documented with a working example.
- The export imports cleanly with images intact.

Report the article list, the export path and the import verification result. Then STOP.
```

---

# Utility prompts

Use these between milestones, not instead of them.

## Pre-flight before a risky milestone

```
Before we implement MILESTONE {NN}, do a read-only investigation. Do not modify any file.

1. Read every existing file this milestone will touch and summarise what is actually there versus what the milestone assumes.
2. Read the relevant WordPress core source (name the files and line numbers) and tell me what core already provides that we would otherwise duplicate.
3. List every assumption in the milestone description you cannot verify from the codebase.
4. Name the three most likely ways this milestone breaks something already working.
5. Propose the implementation order within the milestone.

Output only that analysis. No code.
```

## Mid-milestone course correction

```
Stop implementing. Show me the current diff, then answer:
1. What in the milestone's acceptance criteria is now satisfied, and what is not?
2. What have you changed that the milestone did not ask for?
3. What did you discover that should change the plan?
4. Is anything in the diff unverified — claimed to work but never actually run?

Do not continue until I respond.
```

## Adversarial review of a completed milestone

```
Review MILESTONE {NN} as a hostile senior reviewer who did not write it and assumes it is broken.

For each finding, cite file and line:
1. Security: any avatar write reaching user meta without wpmake_aua_current_user_can_edit_avatar(); any missing nonce; any state change on GET; any unescaped output; any trust placed in a request value that belongs in settings.
2. Correctness: unhandled failure paths; assumptions about attachment shape; behaviour when the attachment has been deleted from the media library underneath us.
3. Performance: queries in loops; assets enqueued outside their gate; autoloaded options that should not be.
4. Compatibility: anything that behaves differently with SCRIPT_DEBUG on; anything depending on a .min asset that was not rebuilt.
5. Evidence: every claim in the report that was asserted from a code read rather than actually run. Name each one.

Rank by severity. Propose fixes. Do not implement anything yet.
```

## Session handoff

```
This session is ending. Write docs/HANDOFF.md containing:
1. Milestone in progress and its state against each acceptance criterion.
2. Every file created or modified this session, with why.
3. Decisions made that are not in docs/MILESTONE-PROMPTS.md, and the rationale.
4. Open questions needing my input, each with options and trade-offs.
5. The exact next action for the following session.
6. Anything left broken or half-finished, named explicitly — including any asset changed under assets/ without a grunt rebuild.

Be blunt about incomplete work. A handoff that overstates progress costs more than one that understates it.
```
