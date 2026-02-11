<?php
/**
 * Plugin Name: My Account Menu Builder for WooCommerce
 * Plugin URI: https://github.com/nurkamol/my-account-menu-builder-for-woocommerce
 * Description: Visual drag & drop builder for WooCommerce My Account menu. Custom endpoints, external links, icons, role-based visibility, badges, separators, and white-label mode.
 * Version: 1.2.2
 * Author: Nurkamol Vakhidov
 * Author URI: https://nurkamol.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: my-account-menu-builder-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 10.5
 *
 * @package WooMyAccountMenuBuilder
 * @author  Nurkamol Vakhidov <nurkamol@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WMAB_VERSION', '1.2.2' );
define( 'WMAB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WMAB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WMAB_PLUGIN_FILE', __FILE__ );
define( 'WMAB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Declare compatibility with WooCommerce features.
 *
 * This plugin only customizes the My Account navigation menu.
 * It does NOT interact with orders, cart, checkout, or product data,
 * so it is fully compatible with HPOS and Cart/Checkout Blocks.
 *
 * @since 1.2.0
 */
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        // High-Performance Order Storage (HPOS / Custom Order Tables).
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true
        );

        // Cart & Checkout Blocks.
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'cart_checkout_blocks',
            __FILE__,
            true
        );
    }
} );

/**
 * Main plugin class.
 */
final class WooMyAccountMenuBuilder {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
    }

    /**
     * Initialize plugin.
     */
    public function init() {
        // Translations are automatically loaded by WordPress.org since WP 4.6.

        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', [ $this, 'wc_missing_notice' ] );
            return;
        }

        require_once WMAB_PLUGIN_DIR . 'includes/class-wmab-settings.php';
        require_once WMAB_PLUGIN_DIR . 'includes/class-wmab-frontend.php';
        require_once WMAB_PLUGIN_DIR . 'includes/class-wmab-endpoints.php';

        WMAB_Settings::instance();
        WMAB_Frontend::instance();
        WMAB_Endpoints::instance();

        // Plugin action links.
        add_filter( 'plugin_action_links_' . WMAB_PLUGIN_BASENAME, [ $this, 'action_links' ] );

        // White-label: hide plugin from list if enabled.
        $wl = WMAB_Settings::get_white_label();
        if ( ! empty( $wl['enabled'] ) && ! empty( $wl['hide_plugin'] ) ) {
            add_filter( 'all_plugins', [ $this, 'hide_plugin' ] );
        }
    }

    /**
     * Plugin action links.
     */
    public function action_links( $links ) {
        $settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=wmab' ) ) . '">' . esc_html__( 'Settings', 'my-account-menu-builder-for-woocommerce' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Hide plugin from plugins list (white-label).
     */
    public function hide_plugin( $plugins ) {
        $plugin_file = plugin_basename( __FILE__ );
        if ( isset( $plugins[ $plugin_file ] ) ) {
            unset( $plugins[ $plugin_file ] );
        }
        return $plugins;
    }

    /**
     * WooCommerce missing notice.
     */
    public function wc_missing_notice() {
        echo '<div class="notice notice-error"><p><strong>' .
            esc_html__( 'My Account Menu Builder for WooCommerce', 'my-account-menu-builder-for-woocommerce' ) .
            '</strong>: ' .
            esc_html__( 'WooCommerce is required. Please install and activate WooCommerce.', 'my-account-menu-builder-for-woocommerce' ) .
            '</p></div>';
    }

    /**
     * Activation.
     */
    public function activate() {
        update_option( 'wmab_flush_rewrite', true );
    }

    /**
     * Deactivation.
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
}

WooMyAccountMenuBuilder::instance();
