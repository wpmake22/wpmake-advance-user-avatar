=== Advanced User Avatar | Custom Profile Picture Uploader for WordPress, WooCommerce, and BuddyPress ===
Contributors: wpmakedev, iamprazol
Tags: woocommerce-avatar, woocommerce-profile-picture, user-avatar, profile-picture, custom-avatar
Requires at least: 6.0
Tested up to: 7.1
Stable tag: 2.0.0
Requires PHP: 7.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

WooCommerce profile pictures your customers upload themselves, shown on My Account, product reviews and checkout. Stored on your own server.

== Description ==

Your WooCommerce customers are faceless. Reviews come from anonymous silhouettes, the My Account dashboard greets returning shoppers with a generic placeholder, and every account in your store feels like a transaction instead of a person. Faceless reviews are trusted less, and anonymous accounts give your customers no reason to feel connected to your brand.

Advanced User Avatar gives every WooCommerce customer a real profile photo, uploaded from their device in seconds. The avatar appears on the My Account dashboard and the Account Details page automatically, and you choose which other parts of your store show it — product reviews, order history, checkout and wishlists.

Every photo is stored on your own server. Your customers need no Gravatar account, and the picture shows up everywhere WordPress renders an avatar: comments, author boxes, the admin bar and the Users list.

For BuddyPress communities, it replaces the default avatar uploader with a faster flow. For WP-Members sites, it adds the uploader to the profile form. For any other WordPress site, drop the uploader anywhere with a shortcode or the Gutenberg block.

= Features =

**WooCommerce integration**

* **My Account Dashboard avatar** — customer's profile photo displays on the dashboard the moment they log in
* **Account Details upload field** — customers update their photo from the same page they manage their account
* **Product review avatars** — reviewers show as real people on product pages instead of Gravatar silhouettes
* **Choose where avatars appear** — My Account dashboard, Account details, order history, product reviews, checkout and wishlists, each switched on or off independently
* **Secure upload handling** — file types are validated from the file's contents rather than its name, uploads require a logged-in customer, and your size and type limits are enforced on the server whatever the request claims
* **Page builder compatible** — works alongside Bricks Builder and other builders without duplicate template rendering

**Upload experience**

* **Crop interface** — customers position and size their photo before saving
* **Webcam capture** — take a photo directly in the browser, no file needed
* **EXIF orientation handling** — phone photos stay upright after cropping
* **File type validation** — you choose which of JPG, PNG, GIF and WEBP are allowed
* **Max file size control** — set the upload ceiling from settings
* **Media Library picker** — optionally let customers choose an image they already uploaded, off by default
* **Auto-generated image sizes** — 32, 64 and 96 pixel variants created on upload, so a small avatar downloads a small file

**Placement options for developers and site builders**

* **Shortcode:** `[wpmake_advance_user_avatar_upload]` renders the full upload form on any page
* **Shortcode:** `[wpmake_advance_user_avatar]` outputs the current user's avatar
* **Gutenberg block** for drag-and-drop placement in the block editor
* **Profile screen field** at Users > Profile, the place most people look first
* **Bulk avatar manager** — every user with their current picture, searchable and sortable, on the Users > Users Avatar screen
* **Theme template overrides, action hooks and CSS custom properties** for anything the settings do not cover

**BuddyPress integration**

* **Replaces the default BuddyPress avatar uploader** with the same upload, crop, and webcam flow used across your site
* **Displays on member profile pages and the member directory** so communities feel populated by people, not placeholders

**Better Messages integration**

* **Custom avatars appear in the Better Messages chat interface** automatically, replacing the default Gravatar with the user's uploaded photo

**WP-Members integration**

* **Avatar uploader added to the WP-Members profile edit form** — members update their photo from the same page they edit their profile fields
* **No template editing** — the field is inserted into the existing form when the integration is switched on

= No Gravatar, no external data sharing =

Gravatar sends a hash of every visitor's email address to an external server to fetch their photo. For EU stores and any site under GDPR scope, that third-party request is a compliance question your legal team would rather not answer. Advanced User Avatar stores every photo on your own server. No outbound requests, no hashed emails leaving your site, no third-party dependency.

Install the plugin and give your customers a face on your store.

== Installation ==

1. Install the plugin from Plugins > Add New, or upload the zip via Plugins > Add New > Upload Plugin.
2. Activate Advanced User Avatar through the Plugins menu in WordPress.
3. Go to Users > Users Avatar to set allowed file types, max file size and image dimensions, and to switch on the WooCommerce, BuddyPress, Better Messages and WP-Members integrations.
4. Add the shortcode `[wpmake_advance_user_avatar_upload]` to any page, or insert the Advanced User Avatar Gutenberg block. Users can also set their picture at Users > Profile without any placement at all.
5. For WooCommerce, enable the WooCommerce integration in settings — the avatar then appears in the My Account dashboard and Account Details page automatically, and you choose which other store locations show it.
6. To manage other people's avatars, use the Manage Avatars tab on the Users > Users Avatar screen.

== Frequently Asked Questions ==

= Does this plugin work with WooCommerce? =

Yes. Once the WooCommerce integration is enabled in settings, the customer's avatar appears on the My Account dashboard and an upload field is added to the Account Details page. No template editing or shortcode placement is required for the standard WooCommerce account pages.

= Do my customers need a Gravatar account to use this? =

No. That is the point of the plugin. Customers upload a photo directly from their device or take one with their webcam. No Gravatar account, no external sign-up, no email hashing.

= Is this plugin GDPR-compliant? =

Yes. Every avatar is uploaded and stored on your own server. The plugin makes no external requests to Gravatar or any other third-party service, so no customer data leaves your site to fetch profile images.

= Does this work with Bricks Builder? =

Yes. The WooCommerce integration is compatible with Bricks Builder and inserts the avatar uploader and viewer without conflicting with the builder's template rendering.

= Will this work with my theme? =

The plugin works with any theme that follows WordPress and WooCommerce template standards. It has been tested with Flatsome, Astra, Storefront, and Hello Elementor. Themes that heavily override the WooCommerce My Account templates may need minor adjustments.

= Can I control what file types and sizes users can upload? =

Yes. From Users > User Avatar, the admin chooses which of JPG, PNG, and GIF are accepted and sets the maximum upload size. Invalid uploads are rejected with a clear error message so the customer knows what to fix.

= How does this work on a multisite network? =

An avatar is network-wide: a user uploads once and the same picture appears on every site in the network they belong to. The plugin records which site the image was uploaded to and reads it from there, so the same avatar resolves correctly everywhere.

Managing other people's avatars is limited to super administrators. WordPress only lets a user edit another user's account if they have network-level user permissions, and because avatars are network-wide, letting a single site's administrator change one would change how that person appears on sites they have no rights over. The bulk avatar manager is therefore hidden for administrators without network user permissions; every user can still set their own avatar from their profile or the front-end uploader.

Uploads stay on the site they were made on, in that site's own uploads directory. If you switch on the Advanced deletion setting, each site answers for itself when the plugin is deleted -- a site that left it off keeps its avatars even if a sibling site opted in.

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

There are also action hooks for adding markup without replacing a template: `wpmake_advance_user_avatar_before_avatar`, `wpmake_advance_user_avatar_after_avatar`, `wpmake_advance_user_avatar_before_uploader`, `wpmake_advance_user_avatar_after_uploader`, `wpmake_advance_user_avatar_before_upload_buttons` and `wpmake_advance_user_avatar_after_upload_buttons`. Each receives the resolved shortcode attributes.

= Does this replace the default BuddyPress avatar uploader? =

Yes, optionally. When the BuddyPress integration is enabled, Advanced User Avatar takes over the avatar upload flow on member profile pages, giving members the same crop and webcam tools used elsewhere on the site.

= Does this work with Better Messages? =

Yes. Once a user uploads a custom avatar, it appears inside the Better Messages chat interface in place of the default Gravatar. No extra configuration is required.

= Does this work with WP-Members? =

Yes. When the WP-Members integration is enabled, the avatar uploader is added to the WP-Members profile form, so members set their picture on the same form they use for the rest of their details. No template editing is required.

= Can I display a user's avatar somewhere custom, like an author box or sidebar? =

Yes. Use the shortcode `[wpmake_advance_user_avatar]` to output the current user's avatar anywhere shortcodes are supported, including widgets and page builders.

= Is there an API for setting avatars from my own code? =

Yes. Three functions do the work, and every avatar change in the plugin goes through them:

`wpmake_aua_set_user_avatar( int $user_id, int $attachment_id ) : bool`
`wpmake_aua_remove_user_avatar( int $user_id ) : bool`
`wpmake_aua_current_user_can_edit_avatar( int $user_id ) : bool`

Setting an avatar checks that the user exists and that the attachment exists and is an image. The same attachment may be used by any number of users, and removing an avatar never deletes the file — so an image you picked from the Media Library stays in the Media Library.

The capability function is the one to call before accepting a change from a request. It returns true when the target is the current user — everybody owns their own avatar, whatever else they can do — or when the current user can `edit_user` for that person. The two setter functions deliberately do **not** check capabilities themselves, so code running without a current user, such as an importer or a WP-CLI command, can still set an avatar.

Two actions fire after every change, each passed the user ID and the attachment ID:

`do_action( 'wpmake_aua_avatar_set', $user_id, $attachment_id );`
`do_action( 'wpmake_aua_avatar_removed', $user_id, $attachment_id );`

`wpmake_aua_get_avatar_url( int $user_id, int $size )` returns the URL of a user's uploaded avatar at the size you ask for, or an empty string if they have not set one.

= Which filters can I use? =

* `wpmake_advance_user_avatar_shortcode_atts` — the resolved attributes for either shortcode, with the shortcode tag as the second argument
* `wpmake_advance_user_avatar_template_directory` — the theme sub-directory templates are looked up in, `wpmake-advance-user-avatar` by default
* `wpmake_advance_user_avatar_locate_template` — the resolved absolute path of a template, with the file name as the second argument
* `wpmake_advance_user_avatar_upload_url` — the directory avatars are stored in
* `wpmake_aua_avatar_subsizes` — the extra image sizes generated for avatar uploads
* `pre_get_avatar_data` — WordPress's own filter, which is where this plugin supplies the avatar; use it to override the plugin entirely

== Screenshots ==

1. The avatar upload field on the WooCommerce Account Details page. Customers change their photo on the same screen they manage their account — no separate page to find.
2. The customer's photo in place on the WooCommerce Account Details page after upload, replacing the Gravatar silhouette.
3. The crop interface. Customers frame their own headshot before it saves, so photos arrive the right shape.
4. The upload form dropped onto any page with a shortcode or the Gutenberg block. Crop and webcam capture are built in.
5. The Profile Picture field at Users > Profile, the first place most people look for it.
6. The bulk avatar manager. Every customer with their current photo, searchable and sortable, with Change and Remove on each row.
7. Admin settings — allowed file types, maximum file size, image dimensions, and which store locations show avatars.
8. A customer's uploaded photo shown on their profile, replacing the default Gravatar silhouette across the site.
9. The BuddyPress member area uploader, using the same upload, crop, and webcam flow as the rest of your site.
10. The uploaded photo shown in the BuddyPress member area after save.
11. Avatars displayed across BuddyPress member profile pages and the member directory.

== Changelog ==

= 2.0.0   - 02/09/2026 =
* Feature - Set your profile picture from Users > Profile, the screen most people look at first.
* Feature - Administrators can now change or remove any user's picture. Until now everyone could only manage their own.
* Feature - New "Manage Avatars" tab lists every user with their current picture. Search it, sort it, and change or remove a picture without leaving the page.
* Feature - Optional setting to let people choose a picture they have already uploaded, instead of uploading a new one. Off by default.
* Feature - Optional setting to delete every picture and all plugin data when you delete the plugin. Off by default, and deactivating never deletes anything.
* Enhance - Pictures now appear everywhere WordPress shows one: comments, the admin bar, the Users list, the block editor and the REST API. Several of these still showed Gravatar before.
* Enhance - Pictures load at the size the page needs, so a small avatar no longer downloads the full-size photo.
* Enhance - Faster pages. Plugin scripts and styles now load only where a picture or upload form is actually shown; a logged-out visitor loads none at all.
* Enhance - The webcam feature only loads its code when "Capture Picture" is switched on.
* Enhance - The download is 944KB smaller, after removing bundled libraries the plugin no longer uses.
* Enhance - Nothing at all is sent to Gravatar for a user who has uploaded their own picture -- not even a scrambled version of their email address.
* Enhance - Sharper pictures on high-resolution screens in BuddyPress.
* Enhance - Replaced the misleading "You can change your profile picture on Gravatar" text on the profile screen. Changing a Gravatar has no effect once this plugin is active.
* Fix     - "Uploaded Image Size" did nothing. Every picture was saved at 500x500 whatever you set. It now saves at the size you choose.
* Fix     - "Store in thumbnail sizes" never created the small versions it promised, so small avatars used a larger file than needed.
* Fix     - Uploads failed on sites that had turned JPEG off under "Allowed File Type".
* Fix     - Cropping a see-through PNG no longer turns the transparent areas black.
* Fix     - On sites that allow only GIF, the picture is now saved as it is instead of being rejected.
* Fix     - Deleting a picture from the Media Library now clears it from anyone using it, instead of leaving them with a missing image.
* Fix     - A picture an administrator sets for someone now belongs to that person. Deleting the administrator's account no longer offers to delete other people's pictures with it.
* Fix     - Security: someone without permission to use the Media Library can no longer take an image from it as their own picture.
* Fix     - Multisite: a user's picture showed a completely different image on other sites in the network. It now shows correctly everywhere.
* Fix     - Multisite: deleting the plugin now tidies up every site in the network, not just the one you deleted it from, and each site keeps its own choice about whether to delete.
* Fix     - Multisite: the upload folder is created for every site, including sites added later.
* Fix     - Multisite: deleting an image on one site no longer clears people's pictures on another.
* Fix     - "Product reviews" in the WooCommerce display settings did nothing; unticking it now hides review avatars.
* Fix     - The upload widget no longer overflows its box when its buttons need more room.
* Tweak   - Redesigned the plugin's screens: a proper navigation bar across the top, and a cleaner user table that is easier to read.
* Tweak   - The Media Library window now opens on the Media Library tab, not on whichever tab you used last somewhere else.
* Tweak   - Multisite: the "Manage Avatars" tab is hidden from administrators who cannot edit users, because it could not change anything for them.
* Tweak   - The review notice is lighter and no longer loads the admin scripts just to show three buttons.
* Tweak   - WooCommerce settings now say where the order history avatar appears: above the orders table.
* Tweak   - The thumbnail preview now shows the sizes actually generated: 32, 64 and 96 pixels.
* Dev     - New functions for setting avatars from your own code: `wpmake_aua_set_user_avatar()`, `wpmake_aua_remove_user_avatar()` and `wpmake_aua_current_user_can_edit_avatar()`. See the FAQ.
* Dev     - New actions `wpmake_aua_avatar_set` and `wpmake_aua_avatar_removed`, each passed the user ID and the attachment ID.
* Dev     - Avatars are now supplied through the `pre_get_avatar_data` filter, at priority 99, instead of `get_avatar`.
* Dev     - New `wpmake_aua_avatar_subsizes` filter for the extra image sizes generated on upload.
* Dev     - New AJAX action `wpmake_advance_user_avatar_upload_set_avatar` assigns an existing attachment as an avatar.
* Dev     - Uninstall honours the `wpmake_advance_user_avatar_upload_url` filter, so a relocated upload folder is cleaned up too.
* Dev     - `review_notice_content()` was unprefixed in the global namespace. Renamed to `wpmake_aua_review_notice_content()`.
* Dev     - Added `phpcs.xml`, so `composer phpcs` and `composer phpcbf` run with no arguments. PHPCS also runs in CI now.
* Dev     - `grunt css` no longer rewrites the bundled third-party stylesheets.

**Developer note -- why this is 2.0.0:** two public functions have been removed, so the version is a major one. `wpmake_advance_user_avatar_replace_gravatar_image()` and `wpmake_advance_user_avatar_build_avatar_html()` are gone. WordPress now builds the avatar markup itself, including class merging and `srcset`. If you replaced either function, that override no longer runs -- filter `pre_get_avatar_data` (this plugin hooks it at priority 99, so use a later one to override it), or the new `wpmake_aua_avatar_subsizes` filter for the generated sizes.

Both shipped templates changed in this release and are now marked `@version 2.0.0`. If you copied either into your theme, compare your copy against the new one -- the uploader template gained the optional Media Library button.

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
