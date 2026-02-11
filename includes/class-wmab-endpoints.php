<?php
/**
 * Custom endpoint registration and content rendering.
 *
 * @package WMAB_Plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WMAB_Endpoints {

    /** Default WooCommerce endpoint slugs — never re-register these. */
    const WC_DEFAULTS = [
        'dashboard',
        'orders',
        'downloads',
        'edit-address',
        'edit-account',
        'customer-logout',
        'payment-methods',
        'view-order',
        'order-pay',
        'order-received',
        'add-payment-method',
        'delete-payment-method',
        'set-default-payment-method',
        'lost-password',
    ];

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ $this, 'register_endpoints' ], 5 );
        add_action( 'init', [ $this, 'maybe_flush_rewrite' ], 20 );
        add_filter( 'woocommerce_get_query_vars', [ $this, 'add_query_vars' ] );

        // Hook content rendering for each custom endpoint.
        foreach ( self::get_custom_endpoints() as $slug => $item ) {
            add_action( 'woocommerce_account_' . $slug . '_endpoint', function () use ( $item ) {
                self::render_content( $item );
            } );
        }
    }

    /* ------------------------------------------------------------------
     * Get custom (non-default) endpoint items keyed by slug.
     * ----------------------------------------------------------------*/

    private static function get_custom_endpoints() {
        $items   = WMAB_Settings::get_menu_items();
        $custom  = [];

        foreach ( $items as $item ) {
            if ( ( $item['type'] ?? 'endpoint' ) !== 'endpoint' ) {
                continue;
            }
            if ( empty( $item['enabled'] ) ) {
                continue;
            }
            $slug = $item['endpoint'] ?? '';
            if ( empty( $slug ) || in_array( $slug, self::WC_DEFAULTS, true ) ) {
                continue;
            }
            $custom[ $slug ] = $item;
        }

        return $custom;
    }

    /* ------------------------------------------------------------------
     * Register rewrite endpoints.
     * ----------------------------------------------------------------*/

    public function register_endpoints() {
        foreach ( self::get_custom_endpoints() as $slug => $item ) {
            add_rewrite_endpoint( $slug, EP_ROOT | EP_PAGES );
        }
    }

    /* ------------------------------------------------------------------
     * Add query vars so WC recognises the endpoints.
     * ----------------------------------------------------------------*/

    public function add_query_vars( $vars ) {
        foreach ( self::get_custom_endpoints() as $slug => $item ) {
            $vars[ $slug ] = $slug;
        }
        return $vars;
    }

    /* ------------------------------------------------------------------
     * Flush rewrite rules once after save.
     * ----------------------------------------------------------------*/

    public function maybe_flush_rewrite() {
        if ( get_option( 'wmab_flush_rewrite' ) ) {
            flush_rewrite_rules();
            delete_option( 'wmab_flush_rewrite' );
        }
    }

    /* ------------------------------------------------------------------
     * Render endpoint content.
     * ----------------------------------------------------------------*/

    private static function render_content( $item ) {
        $content = $item['content'] ?? '';

        echo '<div class="wmab-endpoint-content">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static HTML tag.

        if ( ! empty( $content ) ) {
            // Support shortcodes + basic HTML.
            $filtered = do_shortcode( wp_kses_post( $content ) );
            echo $filtered; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already sanitized by wp_kses_post.
        } else {
            echo '<p>' . esc_html( sprintf(
                /* translators: %s: page title */
                __( 'Welcome to %s.', 'my-account-menu-builder-for-woocommerce' ),
                $item['title'] ?? ''
            ) ) . '</p>';
        }

        echo '</div>';
    }
}
