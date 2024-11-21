=== Advance User Avatar ===
Contributors: wpmakedev
Tags: profile picture, avatar, gravatar, picture, user avatar
Requires at least: 5.5
Tested up to: 6.7.1
Stable tag: 1.0.1
Requires PHP: 7.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

The best avatar uploader plugin for WordPress.

== Description ==

**Advance User Avatar – The Best Avatar Uploader Plugin for WordPress**

WordPress doesn't provide a direct way to upload user avatars, relying instead on Gravatar images. This can be inconvenient for users without a Gravatar account, as they won't have a set avatar. To address this issue and allow users to upload avatars directly from their devices, the Advance User Avatar plugin was created.

### Features

The Advance User Avatar plugin includes the following features:

* **Shortcode Support:**
  * `[wpmake_advance_user_avatar]`: Displays the avatar uploaded by the user anywhere you want.
  * `[wpmake_advance_user_avatar_upload]`: Provides an interface for users to upload or remove their avatar.

* **Gutenberg Block Support:** The plugin supports Gutenberg blocks.

* **Valid File Type Selection:** Allows administrators to select accepted file types for uploads.

* **Max File Size Limit:** Lets administrators set a maximum file size for uploaded images.

* **Capture Picture:** Enables users to capture a picture using their webcam with optional cropping.

* **[Premium] Cropping Interface:** Allows users to crop their uploaded images to the desired area.

### How to Use the Plugin Features:

1. Navigate to **Users -> User Avatar**.

2. You'll see three options:

   * **Max Avatar Size Allowed:** Restricts the maximum size of uploaded images. For example, setting this to 20KB only allows images up to 20KB. If a user tries to upload a larger file, they’ll see a file size exceeded error.

   * **Allowed File Type:** Allows the admin to select file types. In the free version, only JPG and JPEG are available, while the PRO version adds GIF and PNG support. Uploads are validated against these selections, and users will see an "Invalid file type" message if they upload an unsupported format.

   * **Cropping Interface:** When enabled, this option allows users to crop their uploaded or captured image to a preferred area.

== Screenshots ==

1. Avatar Upload Interface
2. Avatar Display Interface
3. Global Settings
2. Cropper Interface
3. Uploader Interface
4. Error Display

== Changelog ==

= 1.0.1      - 21-09-2024 =
* Enhance    - Mime type selection design in settings.
* Enhance    - Avatar upload error and success message design .
* Dev 		 - Added WordPress v6.7 compatibility.
* Dev        - Updated WordPress minimum version requirement to 5.5 for better Gutenberg block support.

= 1.0.0 = 11-09-2024
* Initial Release
