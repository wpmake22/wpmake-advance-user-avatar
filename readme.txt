=== Advanced User Avatar | Custom Profile Picture Uploader for WordPress, WooCommerce, and BuddyPress ===
Contributors: wpmakedev, iamprazol
Tags: woocommerce-avatar, woocommerce-profile-picture, user-avatar, profile-picture, custom-avatar
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 1.2.2
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
