# Changelog

All notable changes to **My Account Menu Builder for WooCommerce** will be documented in this file.

## [1.2.1] - 2026-02-09

### Updated
- Plugin renamed from "Woo My Account Menu Builder" to "My Account Menu Builder for WooCommerce" (WordPress.org trademark compliance)
- Plugin slug changed to `my-account-menu-builder-for-woocommerce`
- Text domain changed to `my-account-menu-builder-for-woocommerce`
- Tested up to WordPress 6.9

### Fixed
- Removed `load_plugin_textdomain()` — WordPress.org auto-loads translations since WP 4.6
- Font Awesome 6.5.1 now bundled locally (`assets/css/fontawesome.min.css` + `assets/webfonts/`) instead of loading from external CDN
- Improved `$_POST` input handling with proper sanitization annotations
- All `admin_url()` calls wrapped in `esc_url()` for output escaping
- Dynamic CSS output uses `sanitize_html_class()` for safe class names
- `$_GET['tab']` access properly sanitized with `sanitize_text_field()`
- WordPress Plugin Check: all errors resolved, warnings addressed

## [1.2.0] - 2026-02-09

### Added
- WooCommerce HPOS (High-Performance Order Storage) compatibility declaration via `FeaturesUtil::declare_compatibility`
- WooCommerce Cart & Checkout Blocks compatibility declaration
- Full internationalization (i18n) support with `.pot` template file
- Bundled translations for 10 languages: Spanish (es_ES), French (fr_FR), German (de_DE), Portuguese Brazil (pt_BR), Russian (ru_RU), Turkish (tr_TR), Chinese Simplified (zh_CN), Japanese (ja), Arabic (ar), Italian (it_IT)
- `load_plugin_textdomain()` call for proper translation loading
- WordPress.org-ready `readme.txt` with full FAQ, screenshots, contribute, and translate sections
- Plugin banner (772×250) and icon (128×128, 256×256) assets for WordPress.org directory

### Updated
- WC tested up to: 10.5.0 (from 9.6)
- Plugin header description refined for WordPress.org listing

### Fixed
- **Critical:** WooCommerce 10.5+ "incompatible plugins" warning — resolved by declaring HPOS and Blocks compatibility

## [1.1.0] - 2026-02-08

### Added
- Settings relocated to **WooCommerce → Settings → My Account Menu** tab (proper WC Settings API integration)
- Separator / divider support for grouping menu items
- Static badge text (New, Pro, Hot, etc.) per item
- Dynamic badge counters (order count, download count)
- Description / subtitle text per menu item
- Custom CSS class per item
- Duplicate item with one click
- Auto-generate endpoint slug from title
- White-label: hide plugin from Plugins list option
- Plugin action link for quick Settings access on Plugins page
- Dedicated `frontend.js` for reliable client-side functionality

### Fixed
- **Critical:** Endpoint URLs now use the endpoint slug (not internal item ID), so custom endpoints register at `/my-account/your-slug/`
- **Critical:** New tab (`target="_blank"`) now works reliably via dedicated frontend JavaScript
- Fatal error from non-existent `add_item_classes` method hook
- Array key safety checks with null coalescing for forward-compatibility

### Changed
- Complete plugin architecture rewrite for stability
- Refactored frontend class to use proper WooCommerce menu key mapping
- Expanded icon picker to 36 icons (Dashicons + Font Awesome 6)
- Better responsive layout for admin builder

## [1.0.0] - 2026-02-08

### Added
- Initial release
- Visual drag & drop menu builder
- Custom endpoint and external link support
- Icon support (Dashicons + Font Awesome)
- Role-based visibility
- Export / Import JSON configuration
- White-label mode
- Live preview sidebar
