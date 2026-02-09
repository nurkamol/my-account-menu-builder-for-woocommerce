<?php
/**
 * Admin settings and GUI builder — registered as a WooCommerce Settings tab.
 *
 * @package WooMyAccountMenuBuilder
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WMAB_Settings {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Register WooCommerce Settings tab.
        add_filter( 'woocommerce_settings_tabs_array', [ $this, 'add_settings_tab' ], 50 );
        add_action( 'woocommerce_settings_tabs_wmab', [ $this, 'render_page' ] );

        // Enqueue assets only on our tab.
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

        // AJAX handlers.
        add_action( 'wp_ajax_wmab_save_menu', [ $this, 'ajax_save_menu' ] );
        add_action( 'wp_ajax_wmab_export_menu', [ $this, 'ajax_export_menu' ] );
        add_action( 'wp_ajax_wmab_import_menu', [ $this, 'ajax_import_menu' ] );
        add_action( 'wp_ajax_wmab_save_settings', [ $this, 'ajax_save_settings' ] );
    }

    /* ------------------------------------------------------------------
     * WooCommerce Settings tab
     * ----------------------------------------------------------------*/

    /**
     * Add our tab to the WooCommerce Settings tabs array.
     */
    public function add_settings_tab( $tabs ) {
        $wl = self::get_white_label();
        $label = $wl['enabled'] && $wl['menu_title'] ? $wl['menu_title'] : __( 'My Account Menu', 'my-account-menu-builder-for-woocommerce' );
        $tabs['wmab'] = $label;
        return $tabs;
    }

    /**
     * Check if the current admin screen is our WC Settings tab.
     */
    public static function is_our_page() {
        if ( ! is_admin() ) {
            return false;
        }
        $screen = get_current_screen();
        if ( ! $screen || 'woocommerce_page_wc-settings' !== $screen->id ) {
            return false;
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only checking tab parameter for conditional loading.
        return isset( $_GET['tab'] ) && 'wmab' === sanitize_text_field( wp_unslash( $_GET['tab'] ) );
    }

    /* ------------------------------------------------------------------
     * White-label helpers
     * ----------------------------------------------------------------*/

    public static function get_white_label() {
        return wp_parse_args( get_option( 'wmab_white_label', [] ), [
            'enabled'     => false,
            'plugin_name' => 'My Account Menu Builder for WooCommerce',
            'author_name' => '',
            'author_url'  => '',
            'menu_title'  => 'My Account Menu',
            'hide_plugin' => false,
        ] );
    }

    /* ------------------------------------------------------------------
     * Enqueue admin assets
     * ----------------------------------------------------------------*/

    public function enqueue_admin_assets( $hook ) {
        // Only load on WooCommerce Settings page.
        if ( 'woocommerce_page_wc-settings' !== $hook ) {
            return;
        }
        // Only when our tab is active.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only checking tab parameter for conditional loading.
        if ( ! isset( $_GET['tab'] ) || 'wmab' !== sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
            return;
        }

        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'wmab-fontawesome', WMAB_PLUGIN_URL . 'assets/css/fontawesome.min.css', [], '6.5.1' );
        wp_enqueue_style( 'wmab-admin', WMAB_PLUGIN_URL . 'assets/css/admin.css', [], WMAB_VERSION );

        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'wmab-admin', WMAB_PLUGIN_URL . 'assets/js/admin.js', [ 'jquery', 'jquery-ui-sortable' ], WMAB_VERSION, true );

        wp_localize_script( 'wmab-admin', 'wmabAdmin', [
            'ajaxUrl'      => esc_url( admin_url( 'admin-ajax.php' ) ),
            'nonce'        => wp_create_nonce( 'wmab_nonce' ),
            'menuItems'    => self::get_menu_items(),
            'settings'     => self::get_white_label(),
            'roles'        => self::get_all_roles(),
            'defaultItems' => self::get_default_woo_items(),
            'myAccountUrl' => wc_get_page_permalink( 'myaccount' ),
            'strings'      => [
                'saveSuccess'   => __( 'Menu saved successfully!', 'my-account-menu-builder-for-woocommerce' ),
                'saveError'     => __( 'Error saving menu.', 'my-account-menu-builder-for-woocommerce' ),
                'confirmDelete' => __( 'Delete this menu item?', 'my-account-menu-builder-for-woocommerce' ),
                'confirmReset'  => __( 'Reset menu to WooCommerce defaults? This will discard all customizations.', 'my-account-menu-builder-for-woocommerce' ),
                'importSuccess' => __( 'Menu imported successfully!', 'my-account-menu-builder-for-woocommerce' ),
                'importError'   => __( 'Invalid import data.', 'my-account-menu-builder-for-woocommerce' ),
                'titleRequired' => __( 'Title is required.', 'my-account-menu-builder-for-woocommerce' ),
                'slugRequired'  => __( 'Endpoint slug is required.', 'my-account-menu-builder-for-woocommerce' ),
                'urlRequired'   => __( 'URL is required for link items.', 'my-account-menu-builder-for-woocommerce' ),
                'settingsSaved' => __( 'Settings saved! Reload to see changes.', 'my-account-menu-builder-for-woocommerce' ),
            ],
        ] );
    }

    /* ------------------------------------------------------------------
     * Roles
     * ----------------------------------------------------------------*/

    public static function get_all_roles() {
        $roles = [];
        foreach ( wp_roles()->role_names as $slug => $name ) {
            $roles[] = [ 'slug' => $slug, 'name' => $name ];
        }
        return $roles;
    }

    /* ------------------------------------------------------------------
     * Default WooCommerce items
     * ----------------------------------------------------------------*/

    public static function get_default_woo_items() {
        return [
            self::make_item( 'dashboard',       __( 'Dashboard', 'my-account-menu-builder-for-woocommerce' ),       'endpoint', 'dashboard',       'dashicons dashicons-dashboard',    true ),
            self::make_item( 'orders',           __( 'Orders', 'my-account-menu-builder-for-woocommerce' ),           'endpoint', 'orders',           'dashicons dashicons-cart',         true ),
            self::make_item( 'downloads',        __( 'Downloads', 'my-account-menu-builder-for-woocommerce' ),        'endpoint', 'downloads',        'dashicons dashicons-download',     true ),
            self::make_item( 'edit-address',     __( 'Addresses', 'my-account-menu-builder-for-woocommerce' ),        'endpoint', 'edit-address',     'dashicons dashicons-location',     true ),
            self::make_item( 'edit-account',     __( 'Account Details', 'my-account-menu-builder-for-woocommerce' ),  'endpoint', 'edit-account',     'dashicons dashicons-admin-users',  true ),
            self::make_item( 'customer-logout',  __( 'Logout', 'my-account-menu-builder-for-woocommerce' ),           'endpoint', 'customer-logout',  'dashicons dashicons-exit',         true ),
        ];
    }

    private static function make_item( $id, $title, $type, $endpoint, $icon, $is_default = false ) {
        return [
            'id'          => $id,
            'title'       => $title,
            'type'        => $type,       // endpoint | link | separator
            'endpoint'    => $endpoint,
            'url'         => '',
            'content'     => '',
            'icon'        => $icon,
            'roles'       => [],
            'target'      => '_self',
            'enabled'     => true,
            'is_default'  => $is_default,
            'css_class'   => '',
            'badge'       => '',
            'badge_count' => '',
            'description' => '',
        ];
    }

    /* ------------------------------------------------------------------
     * Get saved items
     * ----------------------------------------------------------------*/

    public static function get_menu_items() {
        $items = get_option( 'wmab_menu_items', null );
        if ( null === $items || ! is_array( $items ) ) {
            return self::get_default_woo_items();
        }
        $template = self::make_item( '', '', 'endpoint', '', '', false );
        foreach ( $items as &$item ) {
            $item = wp_parse_args( $item, $template );
        }
        return $items;
    }

    /* ------------------------------------------------------------------
     * Render settings page (output inside WC Settings tab)
     * ----------------------------------------------------------------*/

    public function render_page() {
        $wl = self::get_white_label();
        ?>
        <div class="wmab-wrap">
            <div class="wmab-header">
                <h1>
                    <span class="dashicons dashicons-menu-alt3"></span>
                    <?php echo esc_html( $wl['enabled'] && $wl['plugin_name'] ? $wl['plugin_name'] : __( 'My Account Menu Builder', 'my-account-menu-builder-for-woocommerce' ) ); ?>
                    <span class="wmab-version">v<?php echo esc_html( WMAB_VERSION ); ?></span>
                </h1>
                <div class="wmab-header-actions">
                    <button type="button" class="button wmab-btn-reset-defaults"><span class="dashicons dashicons-image-rotate"></span> <?php esc_html_e( 'Reset Defaults', 'my-account-menu-builder-for-woocommerce' ); ?></button>
                    <button type="button" class="button wmab-btn-export"><span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Export', 'my-account-menu-builder-for-woocommerce' ); ?></button>
                    <button type="button" class="button wmab-btn-import"><span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Import', 'my-account-menu-builder-for-woocommerce' ); ?></button>
                    <input type="file" id="wmab-import-file" accept=".json" style="display:none;">
                </div>
            </div>

            <!-- Sub-tabs (Builder / White Label) -->
            <nav class="wmab-tabs">
                <a href="#" class="wmab-tab active" data-tab="builder"><?php esc_html_e( 'Menu Builder', 'my-account-menu-builder-for-woocommerce' ); ?></a>
                <a href="#" class="wmab-tab" data-tab="settings"><?php esc_html_e( 'White Label', 'my-account-menu-builder-for-woocommerce' ); ?></a>
            </nav>

            <!-- ==================== BUILDER TAB ==================== -->
            <div class="wmab-tab-content active" id="wmab-tab-builder">
                <div class="wmab-builder-layout">
                    <div class="wmab-builder-main">
                        <div class="wmab-toolbar">
                            <div class="wmab-toolbar-left">
                                <button type="button" class="button button-primary wmab-btn-add-item"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add Item', 'my-account-menu-builder-for-woocommerce' ); ?></button>
                                <button type="button" class="button wmab-btn-add-separator"><span class="dashicons dashicons-minus"></span> <?php esc_html_e( 'Add Separator', 'my-account-menu-builder-for-woocommerce' ); ?></button>
                            </div>
                            <button type="button" class="button button-primary wmab-btn-save"><span class="dashicons dashicons-saved"></span> <?php esc_html_e( 'Save Menu', 'my-account-menu-builder-for-woocommerce' ); ?></button>
                        </div>
                        <div class="wmab-notice" style="display:none;"></div>
                        <ul class="wmab-sortable-list" id="wmab-menu-list"></ul>
                        <div class="wmab-empty-state" style="display:none;">
                            <span class="dashicons dashicons-menu-alt3"></span>
                            <p><?php esc_html_e( 'No menu items yet. Click "Add Item" to get started.', 'my-account-menu-builder-for-woocommerce' ); ?></p>
                        </div>
                    </div>
                    <div class="wmab-builder-sidebar">
                        <div class="wmab-preview-box">
                            <h3><span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Live Preview', 'my-account-menu-builder-for-woocommerce' ); ?></h3>
                            <div class="wmab-preview-content" id="wmab-preview"></div>
                        </div>
                        <div class="wmab-info-box">
                            <h3><span class="dashicons dashicons-info"></span> <?php esc_html_e( 'Quick Tips', 'my-account-menu-builder-for-woocommerce' ); ?></h3>
                            <ul>
                                <li><?php esc_html_e( 'Drag items to reorder', 'my-account-menu-builder-for-woocommerce' ); ?></li>
                                <li><?php esc_html_e( 'Use separators to group items', 'my-account-menu-builder-for-woocommerce' ); ?></li>
                                <li><?php esc_html_e( 'Endpoint content supports shortcodes', 'my-account-menu-builder-for-woocommerce' ); ?></li>
                                <li><?php esc_html_e( 'Leave roles empty = visible to all', 'my-account-menu-builder-for-woocommerce' ); ?></li>
                                <li><?php esc_html_e( 'Save Permalinks if new endpoints 404', 'my-account-menu-builder-for-woocommerce' ); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== WHITE LABEL TAB ==================== -->
            <div class="wmab-tab-content" id="wmab-tab-settings">
                <div class="wmab-settings-card">
                    <h2><?php esc_html_e( 'White Label Settings', 'my-account-menu-builder-for-woocommerce' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'Rebrand this plugin for client projects.', 'my-account-menu-builder-for-woocommerce' ); ?></p>
                    <table class="form-table wmab-settings-table">
                        <tr>
                            <th><?php esc_html_e( 'Enable White Label', 'my-account-menu-builder-for-woocommerce' ); ?></th>
                            <td><label class="wmab-toggle"><input type="checkbox" id="wmab-wl-enabled" <?php checked( $wl['enabled'] ); ?>><span class="wmab-toggle-slider"></span></label></td>
                        </tr>
                        <tr>
                            <th><label for="wmab-wl-plugin-name"><?php esc_html_e( 'Plugin Name', 'my-account-menu-builder-for-woocommerce' ); ?></label></th>
                            <td><input type="text" id="wmab-wl-plugin-name" class="regular-text" value="<?php echo esc_attr( $wl['plugin_name'] ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="wmab-wl-author-name"><?php esc_html_e( 'Author Name', 'my-account-menu-builder-for-woocommerce' ); ?></label></th>
                            <td><input type="text" id="wmab-wl-author-name" class="regular-text" value="<?php echo esc_attr( $wl['author_name'] ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="wmab-wl-author-url"><?php esc_html_e( 'Author URL', 'my-account-menu-builder-for-woocommerce' ); ?></label></th>
                            <td><input type="url" id="wmab-wl-author-url" class="regular-text" value="<?php echo esc_url( $wl['author_url'] ); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="wmab-wl-menu-title"><?php esc_html_e( 'Settings Tab Label', 'my-account-menu-builder-for-woocommerce' ); ?></label></th>
                            <td><input type="text" id="wmab-wl-menu-title" class="regular-text" value="<?php echo esc_attr( $wl['menu_title'] ); ?>"><p class="description"><?php esc_html_e( 'Label shown on the WooCommerce Settings tab.', 'my-account-menu-builder-for-woocommerce' ); ?></p></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Hide from Plugins List', 'my-account-menu-builder-for-woocommerce' ); ?></th>
                            <td><label class="wmab-toggle"><input type="checkbox" id="wmab-wl-hide-plugin" <?php checked( ! empty( $wl['hide_plugin'] ) ); ?>><span class="wmab-toggle-slider"></span></label><p class="description"><?php esc_html_e( 'Hides this plugin from the WordPress Plugins page.', 'my-account-menu-builder-for-woocommerce' ); ?></p></td>
                        </tr>
                    </table>
                    <p><button type="button" class="button button-primary wmab-btn-save-settings"><span class="dashicons dashicons-saved"></span> <?php esc_html_e( 'Save Settings', 'my-account-menu-builder-for-woocommerce' ); ?></button></p>
                </div>
            </div>

            <!-- ==================== MODAL ==================== -->
            <div class="wmab-modal-overlay" id="wmab-modal" style="display:none;">
                <div class="wmab-modal">
                    <div class="wmab-modal-header">
                        <h2 id="wmab-modal-title"><?php esc_html_e( 'Add Menu Item', 'my-account-menu-builder-for-woocommerce' ); ?></h2>
                        <button type="button" class="wmab-modal-close">&times;</button>
                    </div>
                    <div class="wmab-modal-body">
                        <!-- Title -->
                        <div class="wmab-field">
                            <label for="wmab-item-title"><?php esc_html_e( 'Title', 'my-account-menu-builder-for-woocommerce' ); ?> <span class="required">*</span></label>
                            <input type="text" id="wmab-item-title" placeholder="<?php esc_attr_e( 'Menu item title', 'my-account-menu-builder-for-woocommerce' ); ?>">
                        </div>

                        <!-- Type -->
                        <div class="wmab-field">
                            <label><?php esc_html_e( 'Type', 'my-account-menu-builder-for-woocommerce' ); ?></label>
                            <div class="wmab-radio-group">
                                <label class="wmab-radio-card"><input type="radio" name="wmab-item-type" value="endpoint" checked><span class="dashicons dashicons-admin-page"></span> <?php esc_html_e( 'Endpoint', 'my-account-menu-builder-for-woocommerce' ); ?></label>
                                <label class="wmab-radio-card"><input type="radio" name="wmab-item-type" value="link"><span class="dashicons dashicons-admin-links"></span> <?php esc_html_e( 'Link', 'my-account-menu-builder-for-woocommerce' ); ?></label>
                            </div>
                        </div>

                        <!-- Endpoint fields -->
                        <div class="wmab-field wmab-field-endpoint">
                            <label for="wmab-item-endpoint"><?php esc_html_e( 'Endpoint Slug', 'my-account-menu-builder-for-woocommerce' ); ?> <span class="required">*</span></label>
                            <input type="text" id="wmab-item-endpoint" placeholder="my-custom-page">
                            <p class="description"><?php esc_html_e( 'URL-friendly slug (lowercase, hyphens). Will appear as /my-account/your-slug/', 'my-account-menu-builder-for-woocommerce' ); ?></p>
                        </div>

                        <div class="wmab-field wmab-field-endpoint">
                            <label for="wmab-item-content"><?php esc_html_e( 'Endpoint Content', 'my-account-menu-builder-for-woocommerce' ); ?></label>
                            <textarea id="wmab-item-content" rows="4" placeholder="<?php esc_attr_e( 'HTML or shortcodes for this page (optional)', 'my-account-menu-builder-for-woocommerce' ); ?>"></textarea>
                        </div>

                        <!-- Link fields -->
                        <div class="wmab-field wmab-field-link" style="display:none;">
                            <label for="wmab-item-url"><?php esc_html_e( 'URL', 'my-account-menu-builder-for-woocommerce' ); ?> <span class="required">*</span></label>
                            <input type="url" id="wmab-item-url" placeholder="https://example.com">
                        </div>

                        <!-- Icon -->
                        <div class="wmab-field">
                            <label for="wmab-item-icon"><?php esc_html_e( 'Icon', 'my-account-menu-builder-for-woocommerce' ); ?></label>
                            <div class="wmab-icon-input-wrap">
                                <span class="wmab-icon-preview"><i class="dashicons dashicons-star-filled"></i></span>
                                <input type="text" id="wmab-item-icon" placeholder="dashicons dashicons-star-filled">
                            </div>
                            <div class="wmab-icon-presets">
                                <p class="description"><?php esc_html_e( 'Quick pick:', 'my-account-menu-builder-for-woocommerce' ); ?></p>
                                <div class="wmab-icon-grid">
                                    <?php
                                    $icons = [
                                        'dashicons dashicons-dashboard', 'dashicons dashicons-cart',
                                        'dashicons dashicons-download', 'dashicons dashicons-location',
                                        'dashicons dashicons-admin-users', 'dashicons dashicons-exit',
                                        'dashicons dashicons-heart', 'dashicons dashicons-star-filled',
                                        'dashicons dashicons-admin-links', 'dashicons dashicons-email',
                                        'dashicons dashicons-phone', 'dashicons dashicons-calendar',
                                        'dashicons dashicons-money-alt', 'dashicons dashicons-clipboard',
                                        'dashicons dashicons-bell', 'dashicons dashicons-shield',
                                        'dashicons dashicons-tag', 'dashicons dashicons-admin-home',
                                        'dashicons dashicons-format-chat', 'dashicons dashicons-tickets-alt',
                                        'fa-solid fa-house', 'fa-solid fa-bag-shopping',
                                        'fa-solid fa-file-invoice', 'fa-solid fa-truck',
                                        'fa-solid fa-credit-card', 'fa-solid fa-gift',
                                        'fa-solid fa-ticket', 'fa-solid fa-headset',
                                        'fa-solid fa-bookmark', 'fa-solid fa-gear',
                                        'fa-solid fa-bell', 'fa-solid fa-wallet',
                                        'fa-regular fa-heart', 'fa-regular fa-star',
                                        'fa-regular fa-file', 'fa-regular fa-circle-question',
                                    ];
                                    foreach ( $icons as $ic ) {
                                        echo '<button type="button" class="wmab-icon-pick" data-icon="' . esc_attr( $ic ) . '" title="' . esc_attr( $ic ) . '"><i class="' . esc_attr( $ic ) . '"></i></button>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Target -->
                        <div class="wmab-field">
                            <label><?php esc_html_e( 'Open In', 'my-account-menu-builder-for-woocommerce' ); ?></label>
                            <div class="wmab-radio-group wmab-radio-group-sm">
                                <label><input type="radio" name="wmab-item-target" value="_self" checked> <?php esc_html_e( 'Same Tab', 'my-account-menu-builder-for-woocommerce' ); ?></label>
                                <label><input type="radio" name="wmab-item-target" value="_blank"> <?php esc_html_e( 'New Tab', 'my-account-menu-builder-for-woocommerce' ); ?></label>
                            </div>
                        </div>

                        <!-- Roles -->
                        <div class="wmab-field">
                            <label><?php esc_html_e( 'Visible to Roles', 'my-account-menu-builder-for-woocommerce' ); ?></label>
                            <p class="description"><?php esc_html_e( 'Leave all unchecked = visible to everyone.', 'my-account-menu-builder-for-woocommerce' ); ?></p>
                            <div class="wmab-roles-grid" id="wmab-roles-grid"></div>
                        </div>

                        <!-- Badge -->
                        <div class="wmab-field-row">
                            <div class="wmab-field wmab-field-half">
                                <label for="wmab-item-badge"><?php esc_html_e( 'Badge Text', 'my-account-menu-builder-for-woocommerce' ); ?></label>
                                <input type="text" id="wmab-item-badge" placeholder="<?php esc_attr_e( 'e.g. New, Pro, Hot', 'my-account-menu-builder-for-woocommerce' ); ?>">
                            </div>
                            <div class="wmab-field wmab-field-half">
                                <label for="wmab-item-badge-count"><?php esc_html_e( 'Dynamic Count', 'my-account-menu-builder-for-woocommerce' ); ?></label>
                                <select id="wmab-item-badge-count">
                                    <option value=""><?php esc_html_e( 'None', 'my-account-menu-builder-for-woocommerce' ); ?></option>
                                    <option value="orders"><?php esc_html_e( 'Order Count', 'my-account-menu-builder-for-woocommerce' ); ?></option>
                                    <option value="downloads"><?php esc_html_e( 'Download Count', 'my-account-menu-builder-for-woocommerce' ); ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="wmab-field">
                            <label for="wmab-item-description"><?php esc_html_e( 'Description / Subtitle', 'my-account-menu-builder-for-woocommerce' ); ?></label>
                            <input type="text" id="wmab-item-description" placeholder="<?php esc_attr_e( 'Short description shown below title', 'my-account-menu-builder-for-woocommerce' ); ?>">
                        </div>

                        <!-- CSS Class -->
                        <div class="wmab-field">
                            <label for="wmab-item-css-class"><?php esc_html_e( 'Custom CSS Class', 'my-account-menu-builder-for-woocommerce' ); ?></label>
                            <input type="text" id="wmab-item-css-class" placeholder="<?php esc_attr_e( 'my-custom-class', 'my-account-menu-builder-for-woocommerce' ); ?>">
                        </div>

                        <!-- Enabled -->
                        <div class="wmab-field">
                            <label class="wmab-toggle-label">
                                <input type="checkbox" id="wmab-item-enabled" checked>
                                <span class="wmab-toggle-slider-sm"></span>
                                <?php esc_html_e( 'Enabled', 'my-account-menu-builder-for-woocommerce' ); ?>
                            </label>
                        </div>
                    </div>
                    <div class="wmab-modal-footer">
                        <button type="button" class="button wmab-modal-cancel"><?php esc_html_e( 'Cancel', 'my-account-menu-builder-for-woocommerce' ); ?></button>
                        <button type="button" class="button button-primary wmab-modal-save"><?php esc_html_e( 'Save Item', 'my-account-menu-builder-for-woocommerce' ); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /* ------------------------------------------------------------------
     * AJAX handlers
     * ----------------------------------------------------------------*/

    public function ajax_save_menu() {
        check_ajax_referer( 'wmab_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON string; individual fields sanitized via sanitize_item() after decoding.
        $raw = isset( $_POST['items'] ) ? json_decode( wp_unslash( $_POST['items'] ), true ) : [];
        if ( ! is_array( $raw ) ) {
            wp_send_json_error( 'Invalid data' );
        }

        $sanitized = array_map( [ __CLASS__, 'sanitize_item' ], $raw );
        update_option( 'wmab_menu_items', $sanitized );
        update_option( 'wmab_flush_rewrite', true );

        wp_send_json_success( [ 'items' => $sanitized ] );
    }

    public function ajax_export_menu() {
        check_ajax_referer( 'wmab_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }
        wp_send_json_success( [
            'plugin'   => 'my-account-menu-builder-for-woocommerce',
            'version'  => WMAB_VERSION,
            'exported' => current_time( 'mysql' ),
            'items'    => self::get_menu_items(),
            'settings' => self::get_white_label(),
        ] );
    }

    public function ajax_import_menu() {
        check_ajax_referer( 'wmab_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON string; individual fields sanitized via sanitize_item() and sanitize_white_label() after decoding.
        $json = isset( $_POST['json'] ) ? json_decode( wp_unslash( $_POST['json'] ), true ) : null;
        if ( ! $json || ! isset( $json['items'] ) || ! is_array( $json['items'] ) ) {
            wp_send_json_error( 'Invalid JSON' );
        }

        $sanitized = array_map( [ __CLASS__, 'sanitize_item' ], $json['items'] );
        update_option( 'wmab_menu_items', $sanitized );

        if ( isset( $json['settings'] ) && is_array( $json['settings'] ) ) {
            update_option( 'wmab_white_label', self::sanitize_white_label( $json['settings'] ) );
        }

        update_option( 'wmab_flush_rewrite', true );
        wp_send_json_success( [ 'items' => $sanitized, 'settings' => self::get_white_label() ] );
    }

    public function ajax_save_settings() {
        check_ajax_referer( 'wmab_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON string; individual fields sanitized via sanitize_white_label() after decoding.
        $raw = isset( $_POST['settings'] ) ? json_decode( wp_unslash( $_POST['settings'] ), true ) : [];
        $sanitized = self::sanitize_white_label( $raw );
        update_option( 'wmab_white_label', $sanitized );
        wp_send_json_success( $sanitized );
    }

    /* ------------------------------------------------------------------
     * Sanitization
     * ----------------------------------------------------------------*/

    public static function sanitize_item( $item ) {
        $type = in_array( $item['type'] ?? '', [ 'endpoint', 'link', 'separator' ], true ) ? $item['type'] : 'endpoint';
        return [
            'id'          => sanitize_key( $item['id'] ?? wp_generate_uuid4() ),
            'title'       => sanitize_text_field( $item['title'] ?? '' ),
            'type'        => $type,
            'endpoint'    => sanitize_title( $item['endpoint'] ?? '' ),
            'url'         => esc_url_raw( $item['url'] ?? '' ),
            'content'     => wp_kses_post( $item['content'] ?? '' ),
            'icon'        => sanitize_text_field( $item['icon'] ?? '' ),
            'roles'       => array_map( 'sanitize_key', (array) ( $item['roles'] ?? [] ) ),
            'target'      => in_array( $item['target'] ?? '', [ '_self', '_blank' ], true ) ? $item['target'] : '_self',
            'enabled'     => (bool) ( $item['enabled'] ?? true ),
            'is_default'  => (bool) ( $item['is_default'] ?? false ),
            'css_class'   => sanitize_text_field( $item['css_class'] ?? '' ),
            'badge'       => sanitize_text_field( $item['badge'] ?? '' ),
            'badge_count' => in_array( $item['badge_count'] ?? '', [ '', 'orders', 'downloads' ], true ) ? $item['badge_count'] : '',
            'description' => sanitize_text_field( $item['description'] ?? '' ),
        ];
    }

    private static function sanitize_white_label( $data ) {
        return [
            'enabled'     => (bool) ( $data['enabled'] ?? false ),
            'plugin_name' => sanitize_text_field( $data['plugin_name'] ?? '' ),
            'author_name' => sanitize_text_field( $data['author_name'] ?? '' ),
            'author_url'  => esc_url_raw( $data['author_url'] ?? '' ),
            'menu_title'  => sanitize_text_field( $data['menu_title'] ?? '' ),
            'hide_plugin' => (bool) ( $data['hide_plugin'] ?? false ),
        ];
    }
}
