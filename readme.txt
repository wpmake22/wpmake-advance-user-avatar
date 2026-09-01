=== Advanced User Avatar | Custom Profile Picture Uploader for WordPress, WooCommerce, and BuddyPress ===
Contributors: wpmakedev, iamprazol
Tags: woocommerce-avatar, woocommerce-profile-picture, user-avatar, profile-picture, custom-avatar
Requires at least: 6.0
Tested up to: 7.1
Stable tag: 1.3.0
Requires PHP: 7.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Let WooCommerce customers upload profile photos shown on My Account, reviews, and member pages — no Gravatar account required.

== Description ==

Your WooCommerce customers are faceless. Reviews come from anonymous silhouettes, the My Account dashboard greets returning shoppers with a generic placeholder, and every account in your store feels like a transaction instead of a person. Faceless reviews are trusted less, and anonymous accounts give your customers no reason to feel connected to your brand.

Advanced User Avatar gives every WooCommerce customer a real profile photo, uploaded from their device in seconds. The avatar appears in the WooCommerce My Account dashboard and on the Account Details page automatically. For BuddyPress communities, it replaces the default avatar uploader with a faster, friendlier flow. For any other WordPress site, drop the avatar uploader anywhere using a shortcode or the Gutenberg block.

= Features =

**WooCommerce integration**

* **My Account Dashboard avatar** — customer's profile photo displays on the dashboard the moment they log in
* **Account Details upload field** — customers update their photo from the same page they manage their account
* **Page builder compatible** — works alongside Bricks Builder and other builders without duplicate template rendering
* **Product review avatars** — coming in version 2, so reviewers show as real people on product pages

**BuddyPress integration**

* **Replaces the default BuddyPress avatar uploader** with the same upload, crop, and webcam flow used across your site
* **Displays on member profile pages and the member directory** so communities feel populated by people, not placeholders

**Better Messages integration**

* **Custom avatars appear in the Better Messages chat interface** automatically, replacing the default Gravatar with the user's uploaded photo

**Upload experience**

* **Crop interface** — customers position and size their photo before saving
* **Webcam capture** — take a photo directly in the browser, no file needed
* **EXIF orientation handling** — phone photos stay upright after cropping
* **File type validation** — admin chooses which of JPG, PNG, and GIF are allowed
* **Max file size control** — set the upload ceiling from settings
* **Auto-generated image sizes** — multiple thumbnails created on upload for fast display everywhere

**Placement options for developers and site builders**

* **Shortcode:** `[wpmake_advance_user_avatar_upload]` renders the full upload form on any page
* **Shortcode:** `[wpmake_advance_user_avatar]` outputs the current user's avatar
* **Gutenberg block** for drag-and-drop placement in the block editor

= No Gravatar, no external data sharing =

Gravatar sends a hash of every visitor's email address to an external server to fetch their photo. For EU stores and any site under GDPR scope, that third-party request is a compliance question your legal team would rather not answer. Advanced User Avatar stores every photo on your own server. No outbound requests, no hashed emails leaving your site, no third-party dependency.

Install the plugin and give your customers a face on your store.

== Installation ==

1. Install the plugin from Plugins > Add New, or upload the zip via Plugins > Add New > Upload Plugin.
2. Activate Advanced User Avatar through the Plugins menu in WordPress.
3. Go to Users > User Avatar to set allowed file types, max file size, image dimensions, and toggle WooCommerce and BuddyPress integrations.
4. Add the shortcode `[wpmake_advance_user_avatar_upload]` to any page, or insert the Advanced User Avatar Gutenberg block.
5. For WooCommerce, enable the WooCommerce integration in settings — the avatar then appears in the My Account dashboard and Account Details page automatically.

== Frequently Asked Questions ==

= Does this plugin work with WooCommerce? =

Yes. Once the WooCommerce integration is enabled in settings, the customer's avatar appears on the My Account dashboard and an upload field is added to the Account Details page. No template editing or shortcode placement is required for the standard WooCommerce account pages.

= How does this work on a multisite network? =

An avatar is network-wide: a user uploads once and the same picture appears on every site in the network they belong to. The plugin records which site the image was uploaded to and reads it from there, so the same avatar resolves correctly everywhere.

Managing other people's avatars is limited to super administrators. WordPress only lets a user edit another user's account if they have network-level user permissions, and because avatars are network-wide, letting a single site's administrator change one would change how that person appears on sites they have no rights over. The bulk avatar manager is therefore hidden for administrators without network user permissions; every user can still set their own avatar from their profile or the front-end uploader.

Uploads stay on the site they were made on, in that site's own uploads directory. If you switch on the Advanced deletion setting, each site answers for itself when the plugin is deleted -- a site that left it off keeps its avatars even if a sibling site opted in.

= Do my customers need a Gravatar account to use this? =

No. That is the point of the plugin. Customers upload a photo directly from their device or take one with their webcam. No Gravatar account, no external sign-up, no email hashing.

= Is this plugin GDPR-compliant? =

Yes. Every avatar is uploaded and stored on your own server. The plugin makes no external requests to Gravatar or any other third-party service, so no customer data leaves your site to fetch profile images.

= Will this work with my theme? =

The plugin works with any theme that follows WordPress and WooCommerce template standards. It has been tested with Flatsome, Astra, Storefront, and Hello Elementor. Themes that heavily override the WooCommerce My Account templates may need minor adjustments.

= Can I control what file types and sizes users can upload? =

Yes. From Users > User Avatar, the admin chooses which of JPG, PNG, and GIF are accepted and sets the maximum upload size. Invalid uploads are rejected with a clear error message so the customer knows what to fix.

= Does this replace the default BuddyPress avatar uploader? =

Yes, optionally. When the BuddyPress integration is enabled, Advanced User Avatar takes over the avatar upload flow on member profile pages, giving members the same crop and webcam tools used elsewhere on the site.

= Does this work with Better Messages? =

Yes. Once a user uploads a custom avatar, it appears inside the Better Messages chat interface in place of the default Gravatar. No extra configuration is required.

= Does this work with Bricks Builder? =

Yes. The WooCommerce integration is compatible with Bricks Builder and inserts the avatar uploader and viewer without conflicting with the builder's template rendering.

= Can I display a user's avatar somewhere custom, like an author box or sidebar? =

Yes. Use the shortcode `[wpmake_advance_user_avatar]` to output the current user's avatar anywhere shortcodes are supported, including widgets and page builders.

= Can I change the avatar size, or the size, colour and shape of the upload button? =

Yes, and no `!important` is needed. Every size, shape and colour is read from a CSS custom property on the widget's container, so one rule in Appearance > Customize > Additional CSS is enough:

`.wpmake-advance-user-avatar-container {
    --wpmake-aua-avatar-size: 150px;    /* avatar width/height */
    --wpmake-aua-avatar-radius: 50%;    /* round the avatar */
    --wpmake-aua-width: 100%;           /* container box */
    --wpmake-aua-height: auto;
    --wpmake-aua-padding: 0;
    --wpmake-aua-btn-primary-bg: #2f7d32;   /* Upload file button */
    --wpmake-aua-btn-primary-bg-hover: #27682a;
    --wpmake-aua-btn-padding: 6px 12px;
    --wpmake-aua-btn-font-size: 13px;
    --wpmake-aua-btn-radius: 999px;
}`

The remaining properties follow the same naming: `--wpmake-aua-align`, `--wpmake-aua-avatar-spacing`, `--wpmake-aua-btn-border-width`, `--wpmake-aua-btn-primary-color`, `--wpmake-aua-btn-capture-color`, `--wpmake-aua-btn-capture-color-hover`, `--wpmake-aua-btn-remove-border`, `--wpmake-aua-btn-remove-border-hover`, `--wpmake-aua-btn-remove-color` and `--wpmake-aua-btn-remove-bg-hover`.

= Can I set the size for one placement only? =

Yes. Both shortcodes accept attributes: `size` (avatar width/height in px), `radius` (avatar corner radius, e.g. `50%`), and `class` (extra class names on the container, for your own CSS). The uploader also accepts `upload_text`, `remove_text` and `capture_text` for the button labels.

`[wpmake_advance_user_avatar size="150" radius="50%"]`
`[wpmake_advance_user_avatar_upload size="120" class="my-profile-avatar" upload_text="Choose a photo"]`

= Can I change the uploader's markup or layout? =

Yes. Copy the template you want to change out of `wp-content/plugins/wpmake-advance-user-avatar/templates/` into `wp-content/themes/your-theme/wpmake-advance-user-avatar/`, keeping the file name, and edit your copy. The theme copy is used instead of the plugin's and survives plugin updates.

There are also action hooks for adding markup without replacing a template: `wpmake_advance_user_avatar_before_avatar`, `wpmake_advance_user_avatar_after_avatar`, `wpmake_advance_user_avatar_before_uploader`, `wpmake_advance_user_avatar_after_uploader`, `wpmake_advance_user_avatar_before_upload_buttons` and `wpmake_advance_user_avatar_after_upload_buttons`.

== Screenshots ==

1. The avatar upload form — drop it on any page with the shortcode or Gutenberg block. Crop and webcam capture are built in.
2. The customer's uploaded avatar shown on their profile, replacing the default Gravatar silhouette across the site.
3. Admin settings — control allowed file types, max file size, image dimensions, and toggle WooCommerce and BuddyPress integrations.
4. The crop interface — customers position and size their photo before it saves, so headshots are framed the way they want.
5. The WooCommerce Account Details page with the avatar upload field — customers update their photo from the same screen they manage their account.
6. The customer's avatar after upload, displayed on the WooCommerce Account Details page.
7. The BuddyPress member area avatar uploader, using the same upload, crop, and webcam flow as the rest of the site.
8. The uploaded avatar shown in the BuddyPress member area after save.
9. The avatar displayed across BuddyPress member profile pages and the member directory.

== Changelog ==

= 1.4.0   - unreleased =
* Fix     - Multisite: a user's avatar showed the wrong image on other sites in a network. The avatar is stored as an attachment ID in user meta, which is shared across the whole network, but attachment IDs are per-site -- so on another site that ID resolved against that site's own media and rendered whichever image happened to occupy it. The plugin now records which site an avatar was uploaded to and reads it from there, so one upload shows correctly everywhere. Single-site installs are entirely unaffected.
* Fix     - Multisite: deleting the plugin now cleans up every site on the network rather than only the one it was deleted from, with each site honouring its own Advanced deletion setting. A site that left the setting off keeps its avatars even when another site opted in.
* Fix     - Multisite: deleting an attachment no longer clears avatars belonging to a different site that happen to share its ID.
* Fix     - Multisite: the avatar upload directory is created for every site on the network, including sites added later, instead of only the site the plugin was activated from.
* Tweak   - Multisite: the bulk avatar manager is hidden from administrators who do not have network user permissions. WordPress does not let them edit other users, so the screen previously listed everybody and could change nobody -- and since avatars are network-wide, granting them that would change how a user appears on sites they do not administer.
* Feature - An "Advanced" setting to delete every avatar and all plugin data when the plugin is deleted, plus the `uninstall.php` to carry it out. Off by default and safe to leave off: with it off, deleting the plugin leaves everything exactly where it is, and deactivating never removes anything either way. With it on, deleting the plugin removes the plugin's options, the avatar reference on every user, the bulk manager's screen option, the plugin's transients, every avatar the plugin uploaded along with its files, and the upload directory once empty. Avatars chosen from the Media Library are left alone -- those are the site's own images and may be in use elsewhere. Another plugin's options are never touched, BuddyPress's `bp-disable-avatar-uploads` included.
* Feature - Users can optionally pick an existing image from the Media Library instead of uploading one. Off by default, so nothing changes for an existing site, and only ever offered to users who can already upload files -- which most subscribers and customers cannot, and who would otherwise be handed a picker onto an empty library. The media scripts load only where the uploader actually renders and only when both conditions hold.
* Feature - A bulk avatar manager, on a new "Manage Avatars" tab of the Users > Users Avatar screen. Every user with their current picture, searchable by username, email or display name, sortable, paginated at whatever the screen options say, and with Change and Remove on each row that act without reloading the page. Managing avatars one profile at a time was the plugin's largest remaining gap against the alternatives.
* Feature - A Profile Picture field on Users > Profile and Users > Edit User, the place most people look for one first and somewhere this plugin has never had a field at all. Upload from the device or pick an image already in the Media Library, and remove the current one. Administrators can now set and clear any user's picture from the same screen; until now the plugin was self-service only and nobody could change anyone else's.
* Enhance - Core's "You can change your profile picture on Gravatar" text on the profile screen is replaced. It was actively misleading once this plugin is active: changing a Gravatar does nothing for a user whose picture is stored on this site.
* Enhance - Custom avatars now appear everywhere WordPress renders one, not only where `get_avatar()` is called. Avatar resolution moved to the `pre_get_avatar_data` filter, which also covers `get_avatar_url()` -- the REST `/wp/v2/users` response, the core Avatar block, block themes, WooCommerce Blocks and most React admin UI, all of which previously fell back to Gravatar however the avatar was set.
* Enhance - Avatars are served at the size the page asked for instead of the full-size original. A 28px admin bar avatar downloaded a 500x500 image on every page load; it now requests a 32px file.
* Enhance - No hashed email address leaves the site for a user who has uploaded an avatar. WordPress builds the Gravatar hash after the point this plugin now supplies the URL, so on a hit the hash is never constructed.
* Enhance - BuddyPress avatars get a real 2x image in `srcset`. The same URL was previously labelled `2x`, which told the browser a lie and gained nothing on a high-density screen.
* Fix     - Deleting an image that somebody was using as an avatar now clears the reference, whether it is deleted from the Media Library or removed along with the user who uploaded it. Nothing broke before -- the user fell back to Gravatar -- but the stored reference pointed at an attachment that no longer existed, and the bulk manager read that as "has an avatar" and offered to remove it.
* Fix     - Security: the endpoint that assigns an existing attachment as an avatar now requires the Media Library capability. It already checked whose avatar was being changed, but not whether the caller was allowed to pick from the library at all, so a user without `upload_files` could post an attachment ID and take any image on the site as their own avatar regardless of the "Let users choose from the Media Library" setting.
* Fix     - An avatar uploaded on another user's behalf now belongs to the user it depicts rather than to the administrator who uploaded it. Left the other way, deleting that administrator would have offered to delete every avatar they had ever set for somebody else.
* Fix     - The "Uploaded Image Size" setting was also ignored entirely when the crop interface was switched off. Uploads were stored at their original dimensions however large. They are now scaled to the configured size on the server too, centre-cropped to the same shape the browser cropper produces. Images already at or below the configured size are left alone rather than upscaled.
* Fix     - The "Uploaded Image Size" setting was inert. The uploader crops in the browser and the cropper hardcoded 500x500, so every avatar came out 500x500 whatever the setting said. It now produces exactly the configured size. Where that size is not square, the square crop selection is centre-cropped to the configured aspect, so the file is exactly the size asked for with no letterboxing and no stretching.
* Fix     - Cropped uploads were rejected on sites that had unchecked JPEG in Allowed File Type. The cropper always encoded to JPEG and named the file avatar.jpeg, so the browser converted every avatar to a format the server was configured to refuse. It now encodes to the source image's own format when the site allows it, falls back to the first allowed format otherwise, and names the file to match.
* Fix     - Cropping a transparent PNG no longer flattens the transparency to black. PNG and WEBP output keeps its alpha channel; JPEG output, which has no alpha, is composited onto white instead of black.
* Fix     - On a site that allows only GIF, which browsers cannot encode from a canvas, the cropper now uploads the original image untouched rather than silently producing a PNG the server would reject.
* Fix     - The "Store in thumbnail sizes" setting did not do what it said. It is described as generating 32px, 64px and 96px variants and defaults to on, but nothing ever generated them -- the upload only produced the sizes the site already had registered, so a 32px avatar request landed on whichever of those happened to be smallest. The three variants are now generated, for avatar uploads only, so no other image on the site grows extra files.
* Fix     - `review_notice_content()` was declared in the global namespace with no prefix. Renamed to `wpmake_aua_review_notice_content()`.
* Enhance - Plugin assets no longer load on pages that have no avatar widget on them. A logged-out visitor previously downloaded 45KB of avatar JavaScript and CSS on every page of the site, including pages with no avatar anywhere; that is now zero. Logged-in visitors only load them where an avatar widget actually renders -- either shortcode, the block, a widget placement, the WooCommerce account and checkout pages, a BuddyPress member page, or a WP-Members profile.
* Enhance - The webcam library, the largest single asset at 17KB, now loads only when "Capture Picture" is switched on.
* Enhance - Select2 and the plugin's admin stylesheet no longer load on every screen in wp-admin. They load on the plugin's own settings screen only.
* Enhance - An avatar can now be set or removed on another user's behalf, which is the groundwork the profile field and the bulk manager are built on. A request that names a user is accepted only from someone who can already edit that user; everyone else can still change their own avatar and nothing else, exactly as before. The upload and remove handlers were self-service only until now -- both hardcoded the current user, so no administrator could set or clear anyone else's picture.
* Tweak   - The profile screen's "Choose from Media Library" button opens on the Media Library tab rather than on whichever tab was last used elsewhere in wp-admin, and leaves that remembered choice alone for every other media window.
* Tweak   - Removed 944KB of bundled libraries the plugin stopped using in 1.2.0. Jcrop and SweetAlert2 were replaced with custom code then, but their files stayed in the release zip and were still being downloaded as part of every update.
* Tweak   - The review notice's dismiss buttons no longer depend on jQuery or the admin bundle, and the notice carries its own small stylesheet, so it still looks and behaves correctly on every admin screen.
* Dev     - New `wpmake_aua_set_user_avatar( $user_id, $attachment_id )`, `wpmake_aua_remove_user_avatar( $user_id )` and `wpmake_aua_current_user_can_edit_avatar( $user_id )`. Both AJAX handlers now go through them instead of writing user meta directly, so there is one place an avatar changes hands and one place the permission is decided. Setting an avatar validates that the attachment exists and is an image; an attachment may be shared by any number of users, and removing an avatar never deletes it.
* Dev     - Uninstall follows the `wpmake_advance_user_avatar_upload_url` filter rather than assuming the default directory, so a site that has moved its avatar directory has it cleaned up too.
* Dev     - New `wpmake_advance_user_avatar_upload_set_avatar` AJAX action, which assigns an attachment that already exists rather than uploading a new one. Nonce-checked and capability-checked like the other two handlers, and used by the bulk manager's Change action.
* Dev     - The upload pipeline -- allowed types, size ceiling, upload directory, EXIF orientation, configured output size and the avatar sub-sizes -- is now `Ajax::create_avatar_attachment()`, which the profile screen and the front-end uploader both post through. A second implementation in wp-admin would have been a second place for the site owner's settings to stop being honoured.
* Dev     - New `wpmake_aua_avatar_set` and `wpmake_aua_avatar_removed` action hooks, each passed the user ID and the attachment ID, fired after every avatar write.
* Dev     - Added `phpcs.xml`, so `composer phpcs` and `composer phpcbf` run with no arguments. They previously failed outright.
* Dev     - PHPCS now runs in CI on pull requests and pushes to main.
* Dev     - `grunt css` no longer rewrites the bundled third-party stylesheets, and is now a no-op on an unchanged tree.

**Developer note:** `wpmake_advance_user_avatar_replace_gravatar_image()` and `wpmake_advance_user_avatar_build_avatar_html()` have been removed. WordPress now builds the avatar markup itself, including class merging and `srcset`. If you replaced either function, that override no longer runs -- filter `pre_get_avatar_data`, or the new `wpmake_aua_avatar_subsizes` filter for the generated sizes.

= 1.3.0   - 29-08-2026 =
* Feature - WP-Members integration: adds the avatar uploader to the WP-Members profile edit form.
* Enhance - Templates can be overridden from the theme. Each one now resolves through `yourtheme/wpmake-advance-user-avatar/`, then `yourtheme/`, then the shipped copy, so customised markup survives an update. The theme sub-directory and the resolved path are both filterable.
* Enhance - Six new action hooks around the markup -- before and after the avatar, before and after the uploader, and before and after the uploader's buttons -- for changes that do not need a whole template override.
* Enhance - Both shortcodes now accept attributes. `size`, `radius` and `class` on either, plus `upload_text`, `remove_text` and `capture_text` on the uploader, so a single placement can differ from the site-wide styling. The attributes were previously parsed and discarded.
* Enhance - Frontend styles are driven by CSS custom properties on the widget container, so one low-specificity rule in Additional CSS is enough to restyle the avatar size and shape and the button colour, padding, font size and radius -- no `!important` needed.
* Fix     - The `get_avatar` filter called `remove_all_filters( 'get_avatar' )` and re-added only itself, discarding every other callback on the hook for the rest of the request -- other plugins' filters, and the site owner's own snippets, silently stopped working. Replaced with a re-entrancy guard that gives the same protection without touching anyone else's filters.
* Fix     - Removed `!important` from `.avatar-50`, `.avatar-100` and `.avatar-150`. `get_avatar()` already emits matching width and height attributes, so it only served to stop the theme, and the site owner, from resizing those avatars.
* Fix     - Review notice buttons not behaving correctly.
* Fix     - Security: the accepted file types and the maximum upload size were read from the AJAX request rather than from the saved settings, so a tampered request could ignore the site owner's limits. Both are now enforced server side.
* Fix     - Security: the upload and remove handlers were registered for logged-out visitors. A file was written and a media attachment created before the handler checked who was asking. Both now require a logged-in user.
* Fix     - Uploaded file types are validated from the file's real contents with `wp_check_filetype_and_ext()` instead of trusting the extension on the supplied filename.
* Fix     - Cropping a PNG or GIF wrote JPEG data back under the original extension, losing transparency and flattening animation. Cropping now goes through `WP_Image_Editor` and preserves the source format.
* Fix     - Cropping a WEBP upload failed outright -- the crop had no WEBP branch and fell through to the JPEG decoder.
* Fix     - Thumbnail sizes were generated from the uncropped upload. Cropping now happens before the attachment is created, so every generated size is cut from the final image.
* Fix     - A failed upload no longer leaves an orphaned file behind in the uploads directory.
* Fix     - The BuddyPress `bp-disable-avatar-uploads` option is only written when BuddyPress is actually installed.
* Tweak   - The avatar directory is created on activation with `wp_mkdir_p()` instead of a `mkdir( 0777 )` attempted on every request.

= 1.2.2   - 30-05-2026 =
* Fix     - Design issue in Gutenberg block.

= 1.2.1   - 23-05-2026 =
* Enhance - WordPress 7.0 Compatibility.
* Tweak	  - Avatar uploaded success message design.
* Fix 	  - Avatar Uploader interface not appearing.

= 1.2.0   - 20-05-2026 =
* Feature - Better Messages integration: custom avatars now appear in the Better Messages chat interface.
* Fix     - WooCommerce integration conflicting with Bricks Builder and other page builders due to duplicate dashboard template rendering.
* Fix     - Review notice not dismissing after clicking "Sure, I'd love to!" — the notice kept reappearing on every admin page.
* Fix     - `wp_get_attachment_thumb_url()` replaced with `wp_get_attachment_image_url()` (deprecated since WordPress 6.0).
* Fix     - `date_i18n()` replaced with `wp_date()` (deprecated since WordPress 5.3).
* Fix     - `upload_dir` filter was never properly removed after file upload due to an anonymous closure reference mismatch.
* Fix     - `size_format()` was called on an already-formatted string, producing incorrect upload limit messages.
* Fix     - `remove_avatar()` no longer runs when no user is logged in.
* Fix     - Removed redundant double nonce verification in AJAX upload and remove handlers.
* Fix     - `maybe_later` dismiss action now uses a transient; `dismiss_notice()` now includes a capability check.
* Fix     - Leading space typo in `WPMAKE_ADVANCE_USER_AVATAR_TEMPLATE_PATH` constant definition.
* Dev     - Updated minimum WordPress version requirement to 6.0.
* Fix     - `join()` replaced with `implode()` throughout (PHPCS standard).
* Fix     - `$args['class']` now guarded with `empty()` to prevent PHP notices on non-standard calls.

= 1.1.2   - 15-11-2025 =
* Enhance - EXIF orientation metadata support.
* Dev     - Brand Assets Updated.
* Fix     - Images rotated when cropping from mobile phones.

= 1.1.1   - 11-06-2025 =
* Dev     - WordPress 6.8 Compatibility.
* Tweak   - Plugin Name Typo.

= 1.1.0   - 08-03-2025 =
* Feature - WooCommerce Integration.
* Feature - BuddyPress Integration.

= 1.0.3   - 05-01-2025 =
* Feature - Store avatar in different thumbnail sizes.
* Feature - Allow users to use camera or webcam to capture picture.
* Feature - Ability for site owner to change uploaded image's width and height.
* Tweak   - Updated Readme.

= 1.0.2 - 21-10-2024 =
* Tweak - Added a review prompt.
* Tweak - Updated admin footer text.

= 1.0.1 - 21-09-2024 =
* Enhance - Improved design for selecting file types in settings.
* Enhance - Better styling for upload success and error messages.
* Dev - Added compatibility with WordPress v6.7.
* Dev - Updated minimum WordPress version requirement to 5.5 for better block support.

= 1.0.0 - 11-09-2024 =
* Initial Release
