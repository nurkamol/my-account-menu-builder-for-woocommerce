<?php
/**
 * Frontend: overrides WooCommerce My Account navigation.
 *
 * @package WooMyAccountMenuBuilder
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WMAB_Frontend {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter( 'woocommerce_account_menu_items', [ $this, 'filter_menu_items' ], 999 );
        add_filter( 'woocommerce_get_endpoint_url', [ $this, 'filter_endpoint_url' ], 10, 4 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_footer', [ $this, 'render_footer_css' ] );
    }

    /* ------------------------------------------------------------------
     * Build the item lookup keyed by the WC menu key
     * ----------------------------------------------------------------*/

    private static function get_items_with_keys() {
        $items   = WMAB_Settings::get_menu_items();
        $current = wp_get_current_user();
        $roles   = (array) $current->roles;
        $result  = [];

        foreach ( $items as $item ) {
            if ( empty( $item['enabled'] ) ) {
                continue;
            }

            // Role filtering.
            if ( ! empty( $item['roles'] ) ) {
                if ( ! array_intersect( $item['roles'], $roles ) ) {
                    continue;
                }
            }

            // Determine the WooCommerce menu array key:
            //  - endpoint  → use the endpoint slug (e.g. "orders", "my-wishlist")
            //  - link      → prefix to avoid collision with real endpoints
            //  - separator → unique key
            switch ( $item['type'] ) {
                case 'link':
                    $key = 'wmab-link--' . $item['id'];
                    break;
                case 'separator':
                    $key = 'wmab-sep--' . $item['id'];
                    break;
                default: // endpoint
                    $key = $item['endpoint'] ?: $item['id'];
                    break;
            }

            $result[ $key ] = $item;
        }

        return $result;
    }

    /* ------------------------------------------------------------------
     * Filter: woocommerce_account_menu_items
     * ----------------------------------------------------------------*/

    public function filter_menu_items( $wc_items ) {
        $keyed = self::get_items_with_keys();

        if ( empty( $keyed ) ) {
            return $wc_items;
        }

        $new = [];
        foreach ( $keyed as $key => $item ) {
            $new[ $key ] = $item['title'];
        }
        return $new;
    }

    /* ------------------------------------------------------------------
     * Filter: woocommerce_get_endpoint_url — handle link-type items
     * ----------------------------------------------------------------*/

    public function filter_endpoint_url( $url, $endpoint, $value, $permalink ) {
        // Link items.
        if ( 0 === strpos( $endpoint, 'wmab-link--' ) ) {
            $id    = str_replace( 'wmab-link--', '', $endpoint );
            $items = WMAB_Settings::get_menu_items();
            foreach ( $items as $item ) {
                if ( $item['id'] === $id && 'link' === $item['type'] && ! empty( $item['url'] ) ) {
                    return esc_url( $item['url'] );
                }
            }
        }

        // Separator items — point nowhere.
        if ( 0 === strpos( $endpoint, 'wmab-sep--' ) ) {
            return '#';
        }

        return $url;
    }

    /* ------------------------------------------------------------------
     * Enqueue frontend assets
     * ----------------------------------------------------------------*/

    public function enqueue_assets() {
        if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
            return;
        }

        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'wmab-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], '6.5.1' );
        wp_enqueue_style( 'wmab-frontend', WMAB_PLUGIN_URL . 'assets/css/frontend.css', [], WMAB_VERSION );

        wp_enqueue_script( 'wmab-frontend', WMAB_PLUGIN_URL . 'assets/js/frontend.js', [ 'jquery' ], WMAB_VERSION, true );

        // Pass item config to JS for new-tab handling, badges, icons, etc.
        $keyed  = self::get_items_with_keys();
        $config = [];
        foreach ( $keyed as $key => $item ) {
            $badge_html = self::get_badge_html( $item );
            $config[ $key ] = [
                'target'      => $item['target'],
                'icon'        => $item['icon'],
                'type'        => $item['type'],
                'css_class'   => $item['css_class'],
                'badge'       => $badge_html,
                'description' => $item['description'],
            ];
        }

        wp_localize_script( 'wmab-frontend', 'wmabFront', [
            'items' => $config,
        ] );
    }

    /* ------------------------------------------------------------------
     * Badge HTML
     * ----------------------------------------------------------------*/

    private static function get_badge_html( $item ) {
        $badge = '';

        // Dynamic count.
        if ( ! empty( $item['badge_count'] ) && is_user_logged_in() ) {
            $count = 0;
            $user_id = get_current_user_id();

            if ( 'orders' === $item['badge_count'] ) {
                $count = (int) wc_get_customer_order_count( $user_id );
            } elseif ( 'downloads' === $item['badge_count'] ) {
                $downloads = wc_get_customer_available_downloads( $user_id );
                $count = is_array( $downloads ) ? count( $downloads ) : 0;
            }

            if ( $count > 0 ) {
                $badge = '<span class="wmab-badge wmab-badge-count">' . esc_html( $count ) . '</span>';
            }
        }
        // Static badge.
        elseif ( ! empty( $item['badge'] ) ) {
            $badge = '<span class="wmab-badge">' . esc_html( $item['badge'] ) . '</span>';
        }

        return $badge;
    }

    /* ------------------------------------------------------------------
     * Render icon CSS in footer
     * ----------------------------------------------------------------*/

    public function render_footer_css() {
        if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
            return;
        }

        $keyed = self::get_items_with_keys();
        $css   = '';

        foreach ( $keyed as $key => $item ) {
            if ( 'separator' === $item['type'] ) {
                $safe_key = sanitize_html_class( $key );
                $css .= '.woocommerce-MyAccount-navigation-link--' . $safe_key . '{pointer-events:none;border-bottom:1px solid #e5e7eb;margin:4px 0;padding:0 !important;height:1px;overflow:hidden;}';
                $css .= '.woocommerce-MyAccount-navigation-link--' . $safe_key . ' a{display:none !important;}';
            }
        }

        if ( $css ) {
            echo '<style id="wmab-dynamic-css">' . wp_kses( $css, [] ) . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS only contains sanitized class names and static properties.
        }
    }
}
