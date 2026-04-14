<?php
/**
 * WPMakeAdvanceUserAvatar Settings.
 *
 * @class   Settings
 * @version 1.0.0
 * @package WPMakeAdvanceUserAvatar/Admin
 */

namespace WPMake\WPMakeAdvanceUserAvatar\Admin;

if (! defined('ABSPATH') ) {
    exit;
}

/**
 * Settings Class
 *
 * Manages all plugin settings: section/field definitions, page rendering,
 * WordPress option registration, and sanitization.
 *
 * To add a new setting:
 *  1. Add a field entry inside get_sections() (or get_woocommerce_section() for WC fields).
 *  2. Add a sanitize rule in sanitize().
 *  Rendering is handled automatically based on the field 'type'.
 *
 * Supported field types:
 *   toggle | text | select | image_size | thumbnail_size | checkbox_group
 */
class Settings
{

    const OPTION_KEY = 'wpmake_advance_user_avatar_settings';

    /**
     * Constructor — hooks into admin_init to register the setting.
     */
    public function __construct()
    {
        add_action('admin_init', array( $this, 'register' ));
    }

    // -------------------------------------------------------------------------
    // WooCommerce helpers
    // -------------------------------------------------------------------------

    /**
     * Whether WooCommerce is currently active.
     */
    private function is_woocommerce_active(): bool
    {
        return class_exists('WooCommerce');
    }

    /**
     * Returns the WooCommerce-specific section definition.
     * Pulled out to keep get_sections() readable.
     */
    private function get_woocommerce_section(): array
    {
        return array(
        'title'  => __('WooCommerce Display', 'wpmake-advance-user-avatar'),
        'badge'  => array(
        'text' => __('WooCommerce', 'wpmake-advance-user-avatar'),
        'icon' => 'dashicons-cart',
        'type' => 'woocommerce',
        ),
        'fields' => array(
        'woo_display_locations'   => array(
                    'type'        => 'checkbox_group',
                    'label'       => __('Display avatar on', 'wpmake-advance-user-avatar'),
                    'description' => __('Choose where avatars appear in your store', 'wpmake-advance-user-avatar'),
                    'choices'     => array(
                        'my_account_dashboard' => array(
                            'label'    => __('My Account dashboard', 'wpmake-advance-user-avatar'),
                            'sublabel' => __('Account page header', 'wpmake-advance-user-avatar'),
                        ),
                        'account_details_tab'  => array(
                            'label'    => __('Account details tab', 'wpmake-advance-user-avatar'),
                            'sublabel' => __('Edit profile section', 'wpmake-advance-user-avatar'),
                        ),
                        'order_history'        => array(
                            'label'    => __('Order history', 'wpmake-advance-user-avatar'),
                            'sublabel' => __('Next to each order', 'wpmake-advance-user-avatar'),
                        ),
                        'product_reviews'      => array(
                            'label'    => __('Product reviews', 'wpmake-advance-user-avatar'),
                            'sublabel' => __('Replaces Gravatar', 'wpmake-advance-user-avatar'),
                        ),
                        'checkout_page'        => array(
                            'label'    => __('Checkout page', 'wpmake-advance-user-avatar'),
                            'sublabel' => __('Logged-in customer area', 'wpmake-advance-user-avatar'),
                        ),
                        'wishlist'             => array(
                            'label'    => __('Wishlist (if active)', 'wpmake-advance-user-avatar'),
                            'sublabel' => __('WooCommerce Wishlists', 'wpmake-advance-user-avatar'),
                        ),
        ),
        ),
        'woo_my_account_uploader' => array(
                    'type'        => 'toggle',
                    'label'       => __('My Account uploader', 'wpmake-advance-user-avatar'),
                    'description' => __('Show the avatar upload widget inside My Account', 'wpmake-advance-user-avatar'),
        ),
        ),
        );
    }

    // -------------------------------------------------------------------------
    // Section / Field Definitions
    // -------------------------------------------------------------------------

    /**
     * Returns all settings sections and their fields.
     * The WooCommerce section is automatically prepended when WooCommerce is active.
     *
     * @return array
     */
    public function get_sections(): array
    {
        $sections = array();

        $woo_section = $this->get_woocommerce_section();
        if (! $this->is_woocommerce_active() ) {
            $woo_section['locked'] = true;
        }
        $sections['woocommerce_display'] = $woo_section;

        $sections['upload_image'] = array(
        'title'  => __('Upload & Image', 'wpmake-advance-user-avatar'),
        'badge'  => null,
        'fields' => array(
        'max_size'            => array(
                    'type'        => 'text',
                    'label'       => __('Max Avatar Size Allowed', 'wpmake-advance-user-avatar'),
                    'description' => __('Enter file size in KB. Leave empty for no restriction.', 'wpmake-advance-user-avatar'),
                    'suffix'      => 'KB',
                    'default'     => '1024',
                    'placeholder' => '',
        ),
        'allowed_file_type'   => array(
        'type'        => 'select',
        'label'       => __('Allowed File Type', 'wpmake-advance-user-avatar'),
        'description' => __('Choose valid file types allowed for avatar upload', 'wpmake-advance-user-avatar'),
        'choices'     => array(
                        'image/jpg'  => 'JPG',
                        'image/jpeg' => 'JPEG',
                        'image/png'  => 'PNG',
                        'image/webp' => 'WEBP',
                        'image/gif'  => 'GIF',
        ),
        ),
        'uploaded_image_size' => array(
        'type'        => 'image_size',
        'label'       => __('Uploaded Image Size', 'wpmake-advance-user-avatar'),
        'description' => __('The size of the final uploaded image (width × height)', 'wpmake-advance-user-avatar'),
        'default'     => array(
                        'width'  => 500,
                        'height' => 500,
        ),
        ),
        'thumbnail_size'      => array(
        'type'        => 'thumbnail_size',
        'label'       => __('Store in thumbnail sizes', 'wpmake-advance-user-avatar'),
        'badge'       => __('New', 'wpmake-advance-user-avatar'),
        'description' => __('Generate 32px, 64px and 96px variants for different page contexts', 'wpmake-advance-user-avatar'),
        'thumbnails'  => array(
                        array(
                            'size'    => 32,
                            'context' => __('admin bar, comments', 'wpmake-advance-user-avatar'),
         ),
         array(
          'size'    => 48,
          'context' => __('My Account header', 'wpmake-advance-user-avatar'),
         ),
         array(
          'size'    => 64,
          'context' => __('product reviews', 'wpmake-advance-user-avatar'),
         ),
        ),
        ),
        'cropping_interface'  => array(
        'type'        => 'toggle',
        'label'       => __('Cropping Interface', 'wpmake-advance-user-avatar'),
        'description' => __('Allow user to crop selected or captured image', 'wpmake-advance-user-avatar'),
        ),
        ),
        );

        $sections['capture_picture'] = array(
        'title'  => __('Capture Picture', 'wpmake-advance-user-avatar'),
        'badge'  => __('New', 'wpmake-advance-user-avatar'),
        'fields' => array(
        'capture_picture' => array(
                    'type'        => 'toggle',
                    'label'       => __('Webcam capture', 'wpmake-advance-user-avatar'),
                    'description' => __("Enable taking a photo directly from the customer's webcam", 'wpmake-advance-user-avatar'),
                    'warning'     => __('Requires valid SSL (HTTPS) on your domain', 'wpmake-advance-user-avatar'),
        ),
        ),
        );

        $sections['integrations'] = array(
        'title'  => __('Integrations', 'wpmake-advance-user-avatar'),
        'badge'  => __('New', 'wpmake-advance-user-avatar'),
        'fields' => array(
        'buddypress_integration'  => array(
                    'type'        => 'toggle',
                    'label'       => __('BuddyPress Integration', 'wpmake-advance-user-avatar'),
                    'description' => __('Display user avatar in BuddyPress avatar areas and Change Avatar section', 'wpmake-advance-user-avatar'),
        ),
        ),
        );

        return $sections;
    }

    // -------------------------------------------------------------------------
    // WordPress Option Registration
    // -------------------------------------------------------------------------

    /**
     * Registers the settings option with WordPress.
     */
    public function register(): void
    {
        register_setting(
            self::OPTION_KEY,
            self::OPTION_KEY,
            array( 'sanitize_callback' => array( $this, 'sanitize' ) )
        );
    }

    // -------------------------------------------------------------------------
    // Backward Compatibility Migration
    // -------------------------------------------------------------------------

    /**
     * One-time migration for users who had the legacy woocommerce_integration
     * toggle enabled before the granular WooCommerce Display section existed.
     *
     * On first load after the update:
     *  - woo_display_locations is seeded with [my_account_dashboard, account_details_tab]
     *  - woo_my_account_uploader is set to '1'
     *
     * A flag option prevents this from running more than once.
     */
    private function maybe_migrate_woo_settings(): void
    {
        if (get_option('wpmake_aua_woo_migrated_v2') ) {
            return;
        }

        $options = (array) get_option(self::OPTION_KEY, array());

        // Only migrate users who had the old toggle enabled.
        if (! empty($options['woocommerce_integration']) ) {
            if (empty($options['woo_display_locations']) ) {
                $options['woo_display_locations'] = array( 'my_account_dashboard', 'account_details_tab' );
            }
            if (! isset($options['woo_my_account_uploader']) ) {
                $options['woo_my_account_uploader'] = '1';
            }
            update_option(self::OPTION_KEY, $options);
        }

        update_option('wpmake_aua_woo_migrated_v2', '1');
    }

    // -------------------------------------------------------------------------
    // Page Rendering
    // -------------------------------------------------------------------------

    /**
     * Renders the full settings page HTML.
     */
    public function render_page(): void
    {
        $this->maybe_migrate_woo_settings();

        $options  = (array) get_option(self::OPTION_KEY, array());
        $sections = $this->get_sections();
        ?>
        <div class="wrap wpmake-aua-settings-page">
            <h1 class="wpmake-aua-page-title">
                <img src="<?php echo esc_url(WPMAKE_ADVANCE_USER_AVATAR_ASSETS_URL . '/images/icon.png'); ?>" width="50" height="50" alt="" />
        <?php esc_html_e('Users Avatar', 'wpmake-advance-user-avatar'); ?>
            </h1>

        <?php if ($this->is_woocommerce_active() ) : ?>
                <div class="wpmake-aua-woo-banner">
                    <span class="dashicons dashicons-cart wpmake-aua-woo-banner-icon"></span>
                    <p>
                        <strong><?php esc_html_e('WooCommerce detected.', 'wpmake-advance-user-avatar'); ?></strong>
            <?php esc_html_e("Avatar settings below are applied to your store's My Account page, product reviews, and checkout — giving customers a personalised shopping experience.", 'wpmake-advance-user-avatar'); ?>
                    </p>
                </div>
        <?php endif; ?>

            <div class="wpmake-aua-layout">
                <div class="wpmake-aua-layout-main">
                    <form method="post" action="options.php">
        <?php settings_fields(self::OPTION_KEY); ?>

        <?php foreach ( $sections as $section_id => $section ) : ?>
            <?php $is_locked = ! empty($section['locked']); ?>
                            <div class="wpmake-aua-section<?php echo $is_locked ? ' wpmake-aua-section--locked' : ''; ?>">
                                <div class="wpmake-aua-section-header">
                                    <h2 class="wpmake-aua-section-title">
            <?php echo esc_html($section['title']); ?>
            <?php if (! empty($section['badge']) ) : ?>
                <?php if (is_array($section['badge']) ) : ?>
                                                <span class="wpmake-aua-badge wpmake-aua-badge--<?php echo esc_attr($section['badge']['type']); ?>">
                                                    <span class="dashicons <?php echo esc_attr($section['badge']['icon']); ?>"></span>
                    <?php echo esc_html($section['badge']['text']); ?>
                                                </span>
                                            <?php else : ?>
                                                <span class="wpmake-aua-badge"><?php echo esc_html($section['badge']); ?></span>
                                            <?php endif; ?>
            <?php endif; ?>
                                    </h2>
                                </div>

                                <div class="wpmake-aua-section-body-wrap">
                                    <div class="wpmake-aua-section-body">
            <?php foreach ( $section['fields'] as $field_key => $field ) : ?>
                                            <div class="wpmake-aua-field-row">
                                                <div class="wpmake-aua-field-label">
                                                    <strong>
                <?php echo esc_html($field['label']); ?>
                <?php if (! empty($field['badge']) && is_string($field['badge']) ) : ?>
                                                            <span class="wpmake-aua-badge"><?php echo esc_html($field['badge']); ?></span>
                <?php endif; ?>
                                                    </strong>
                <?php if (! empty($field['description']) ) : ?>
                                                        <p><?php echo esc_html($field['description']); ?></p>
                <?php endif; ?>
                                                </div>
                                                <div class="wpmake-aua-field-control">
                <?php $this->render_field($field_key, $field, $options); ?>
                                                </div>
                                            </div>
            <?php endforeach; ?>
                                    </div>

            <?php if ($is_locked ) : ?>
                                        <div class="wpmake-aua-locked-overlay">
                                            <div class="wpmake-aua-locked-message">
                                                <span class="dashicons dashicons-lock"></span>
                                                <p><?php esc_html_e('WooCommerce is required', 'wpmake-advance-user-avatar'); ?></p>
                                            </div>
                                        </div>
            <?php endif; ?>
                                </div>
                            </div>
        <?php endforeach; ?>

                        <div class="wpmake-aua-form-actions">
        <?php submit_button(__('Save Changes', 'wpmake-advance-user-avatar'), 'primary wpmake-aua-save-btn', 'submit', false); ?>
                            <button type="button" class="button button-secondary wpmake-aua-cancel-btn"
                                onclick="window.history.back();">
                                <?php esc_html_e('Cancel', 'wpmake-advance-user-avatar'); ?>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="wpmake-aua-layout-sidebar">
        <?php $this->render_sidebar(); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Renders the static sidebar card with shortcodes and documentation link.
     */
    private function render_sidebar(): void
    {
        $shortcodes = array(
        array(
        'code'        => '[wpmake_advance_user_avatar]',
        'description' => __('Add a profile picture upload form, allowing users to upload or remove their avatar image.', 'wpmake-advance-user-avatar'),
        ),
        array(
        'code'        => '[wpmake_advance_user_avatar_upload]',
        'description' => __('Display the avatar upload form for the logged-in user.', 'wpmake-advance-user-avatar'),
        ),
        );
        ?>
        <div class="wpmake-aua-sidebar-card">
            <div class="wpmake-aua-sidebar-card-header">
                <span class="dashicons dashicons-shortcode"></span>
        <?php esc_html_e('Available Shortcodes', 'wpmake-advance-user-avatar'); ?>
            </div>
            <div class="wpmake-aua-sidebar-card-body">
                <ul class="wpmake-aua-shortcode-list">
        <?php foreach ( $shortcodes as $shortcode ) : ?>
                        <li class="wpmake-aua-shortcode-item">
                            <button
                                type="button"
                                class="wpmake-aua-shortcode-copy"
                                data-code="<?php echo esc_attr($shortcode['code']); ?>"
                                title="<?php esc_attr_e('Copy shortcode', 'wpmake-advance-user-avatar'); ?>"
                            >
                                <code><?php echo esc_html($shortcode['code']); ?></code>
                                <span class="wpmake-aua-copy-icon dashicons dashicons-clipboard"></span>
                            </button>
                            <p><?php echo esc_html($shortcode['description']); ?></p>
                        </li>
        <?php endforeach; ?>
                </ul>
            </div>
            <div class="wpmake-aua-sidebar-card-footer">
                <a href="https://wpmake.net" target="_blank" rel="noopener noreferrer" class="wpmake-aua-docs-link">
                    <span class="dashicons dashicons-external"></span>
        <?php esc_html_e('Visit wpmake.net for full documentation', 'wpmake-advance-user-avatar'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // Field Rendering — dispatches by type
    // -------------------------------------------------------------------------

    /**
     * Dispatches rendering to the appropriate method based on field type.
     *
     * @param string $key     The field/option key.
     * @param array  $field   Field definition from get_sections().
     * @param array  $options Current saved option values.
     */
    private function render_field( string $key, array $field, array $options ): void
    {
        switch ( $field['type'] ) {
        case 'toggle':
            $this->render_toggle($key, $field, $options);
            break;
        case 'text':
            $this->render_text($key, $field, $options);
            break;
        case 'select':
            $this->render_select($key, $field, $options);
            break;
        case 'image_size':
            $this->render_image_size($key, $field, $options);
            break;
        case 'thumbnail_size':
            $this->render_thumbnail_size($key, $field, $options);
            break;
        case 'checkbox_group':
            $this->render_checkbox_group($key, $field, $options);
            break;
        }
    }

    /**
     * Renders a toggle switch (on/off).
     */
    private function render_toggle( string $key, array $field, array $options ): void
    {
        $enabled = ! empty($options[ $key ]);
        $name    = self::OPTION_KEY . '[' . $key . ']';
        $id      = 'wpmake_aua_' . $key;
        $warning = $field['warning'] ?? null;
        ?>
        <div class="wpmake-aua-toggle-row">
            <label class="wpmake-aua-toggle" for="<?php echo esc_attr($id); ?>">
                <input
                    type="checkbox"
                    id="<?php echo esc_attr($id); ?>"
                    name="<?php echo esc_attr($name); ?>"
                    value="1"
        <?php checked($enabled); ?>
                />
                <span class="wpmake-aua-toggle-slider"></span>
            </label>
            <span class="wpmake-aua-toggle-label"><?php esc_html_e('Enable', 'wpmake-advance-user-avatar'); ?></span>
        </div>
        <?php if ($warning ) : ?>
            <div class="wpmake-aua-field-warning">
                <span class="dashicons dashicons-warning"></span>
            <?php echo esc_html($warning); ?>
            </div>
        <?php endif;
    }

    /**
     * Renders a text input, optionally with a unit suffix.
     */
    private function render_text( string $key, array $field, array $options ): void
    {
        $default = $field['default'] ?? '';
        $value   = $options[ $key ] ?? $default;
        $suffix  = $field['suffix'] ?? '';
        $name    = self::OPTION_KEY . '[' . $key . ']';
        ?>
        <div class="wpmake-aua-text-field-wrap">
            <input
                type="text"
                name="<?php echo esc_attr($name); ?>"
                value="<?php echo esc_attr($value); ?>"
                class="wpmake-aua-text-input"
                placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
            />
        <?php if ($suffix ) : ?>
                <span class="wpmake-aua-field-suffix"><?php echo esc_html($suffix); ?></span>
        <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Renders a multi-select (uses select2 enhancement).
     */
    private function render_select( string $key, array $field, array $options ): void
    {
        $selected = isset($options[ $key ]) ? (array) $options[ $key ] : array();
        $name     = self::OPTION_KEY . '[' . $key . '][]';
        ?>
        <select
            class="wpmake-advance-user-avatar-enhanced-select wpmake-aua-select-field"
            name="<?php echo esc_attr($name); ?>"
            multiple="multiple"
        >
        <?php foreach ( $field['choices'] as $val => $label ) : ?>
                <option
                    value="<?php echo esc_attr($val); ?>"
            <?php selected(in_array($val, $selected, true)); ?>
                >
            <?php echo esc_html($label); ?>
                </option>
        <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Renders a width × height dimension pair.
     */
    private function render_image_size( string $key, array $field, array $options ): void
    {
        $defaults = $field['default'] ?? array( 'width' => 500, 'height' => 500 );
        $size     = $options[ $key ] ?? $defaults;
        $base     = self::OPTION_KEY . '[' . $key . ']';
        ?>
        <div class="wpmake-aua-image-size-wrap">
            <input
                type="text"
                name="<?php echo esc_attr($base); ?>[width]"
                value="<?php echo esc_attr($size['width']); ?>"
                class="wpmake-aua-size-input"
            />
            <span class="wpmake-aua-size-sep"><?php esc_html_e('by', 'wpmake-advance-user-avatar'); ?></span>
            <input
                type="text"
                name="<?php echo esc_attr($base); ?>[height]"
                value="<?php echo esc_attr($size['height']); ?>"
                class="wpmake-aua-size-input"
            />
            <span class="wpmake-aua-field-suffix">px</span>
        </div>
        <?php
    }

    /**
     * Renders a toggle with avatar preview thumbnails below it.
     */
    private function render_thumbnail_size( string $key, array $field, array $options ): void
    {
        $enabled    = ! empty($options[ $key ]);
        $name       = self::OPTION_KEY . '[' . $key . ']';
        $id         = 'wpmake_aua_' . $key;
        $thumbnails = $field['thumbnails'] ?? array();
        ?>
        <div class="wpmake-aua-toggle-row">
            <label class="wpmake-aua-toggle" for="<?php echo esc_attr($id); ?>">
                <input
                    type="checkbox"
                    id="<?php echo esc_attr($id); ?>"
                    name="<?php echo esc_attr($name); ?>"
                    value="1"
        <?php checked($enabled); ?>
                />
                <span class="wpmake-aua-toggle-slider"></span>
            </label>
            <span class="wpmake-aua-toggle-label"><?php esc_html_e('Enable', 'wpmake-advance-user-avatar'); ?></span>
        </div>

        <?php if (! empty($thumbnails) ) : ?>
            <div class="wpmake-aua-thumbnail-previews">
            <?php foreach ( $thumbnails as $thumb ) : ?>
                    <div class="wpmake-aua-thumbnail-item">
                        <span
                            class="wpmake-aua-avatar-circle"
                            style="width:<?php echo esc_attr($thumb['size']); ?>px;height:<?php echo esc_attr($thumb['size']); ?>px;font-size:<?php echo esc_attr((int) round($thumb['size'] * 0.35)); ?>px;"
                        >IP</span>
                        <span class="wpmake-aua-thumbnail-label">
                            <strong><?php echo esc_html($thumb['size']); ?> px</strong>
                            &mdash; <?php echo esc_html($thumb['context']); ?>
                        </span>
                    </div>
            <?php endforeach; ?>
            </div>
        <?php endif;
    }

    /**
     * Renders a 2-column grid of checkboxes, each with a label and optional sub-label.
     *
     * Choices schema:
     *   'value_key' => [ 'label' => '...', 'sublabel' => '...' ]
     */
    private function render_checkbox_group( string $key, array $field, array $options ): void
    {
        $checked_values = isset($options[ $key ]) ? (array) $options[ $key ] : array();
        $base_name      = self::OPTION_KEY . '[' . $key . '][]';
        ?>
        <div class="wpmake-aua-checkbox-group">
        <?php foreach ( $field['choices'] as $value => $choice ) : ?>
                <label class="wpmake-aua-checkbox-item">
                    <input
                        type="checkbox"
                        name="<?php echo esc_attr($base_name); ?>"
                        value="<?php echo esc_attr($value); ?>"
            <?php checked(in_array($value, $checked_values, true)); ?>
                    />
                    <span class="wpmake-aua-checkbox-text">
                        <span class="wpmake-aua-checkbox-label"><?php echo esc_html($choice['label']); ?></span>
            <?php if (! empty($choice['sublabel']) ) : ?>
                            <span class="wpmake-aua-checkbox-sublabel"><?php echo esc_html($choice['sublabel']); ?></span>
            <?php endif; ?>
                    </span>
                </label>
        <?php endforeach; ?>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // Sanitization
    // -------------------------------------------------------------------------

    /**
     * Sanitizes settings before they are saved to the database.
     *
     * Add a sanitize rule here whenever a new field is added in get_sections().
     *
     * @param  mixed $input Raw submitted values.
     * @return array Sanitized values.
     */
    public function sanitize( $input ): array
    {
        if (! is_array($input) ) {
            return array();
        }

        $output = array();

        // Max size — positive integer.
        if (isset($input['max_size']) ) {
            $output['max_size'] = absint($input['max_size']);
        }

        // Allowed file types — array of sanitized strings.
        if (isset($input['allowed_file_type']) && is_array($input['allowed_file_type']) ) {
            $output['allowed_file_type'] = array_map('sanitize_text_field', $input['allowed_file_type']);
        }

        // Uploaded image dimensions — fallback to 500×500.
        if (isset($input['uploaded_image_size']) ) {
            $output['uploaded_image_size'] = array(
            'width'  => absint($input['uploaded_image_size']['width'] ?? 500),
            'height' => absint($input['uploaded_image_size']['height'] ?? 500),
            );
        } else {
            $output['uploaded_image_size'] = array( 'width' => 500, 'height' => 500 );
        }

        // Boolean toggles — stored as '1' or ''.
        $output['thumbnail_size']         = ! empty($input['thumbnail_size']) ? '1' : '';
        $output['cropping_interface']     = ! empty($input['cropping_interface']) ? '1' : '';
        $output['capture_picture']        = ! empty($input['capture_picture']) ? '1' : '';
        $output['woocommerce_integration'] = ! empty($input['woocommerce_integration']) ? '1' : '';

        // WooCommerce Display — granular location checkboxes.
        $allowed_locations = array(
        'my_account_dashboard',
        'account_details_tab',
        'order_history',
        'product_reviews',
        'checkout_page',
        'wishlist',
        );
        if (isset($input['woo_display_locations']) && is_array($input['woo_display_locations']) ) {
            $output['woo_display_locations'] = array_values(
                array_intersect($input['woo_display_locations'], $allowed_locations)
            );
        } else {
            $output['woo_display_locations'] = array();
        }

        $output['woo_my_account_uploader'] = ! empty($input['woo_my_account_uploader']) ? '1' : '';

        // BuddyPress — also syncs the bp-disable-avatar-uploads option.
        if (! empty($input['buddypress_integration']) ) {
            $output['buddypress_integration'] = '1';
            update_option('bp-disable-avatar-uploads', '1');
        } else {
            $output['buddypress_integration'] = '';
            update_option('bp-disable-avatar-uploads', '');
        }

        return $output;
    }
}
