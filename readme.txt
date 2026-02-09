=== My Account Menu Builder for WooCommerce ===
Contributors: nurkamol
Donate link: https://nurkamol.com
Tags: woocommerce, my account, menu builder, custom endpoints, drag and drop
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
WC requires at least: 5.0
WC tested up to: 10.5
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The easiest way to customize the WooCommerce My Account page menu — drag & drop, custom pages, icons, badges, role-based visibility, and more.

== Description ==

**My Account Menu Builder for WooCommerce** gives you total control over WooCommerce's My Account navigation through a beautiful visual builder. No coding required — just drag, drop, and publish.

The default WooCommerce My Account menu is limited: a fixed set of links with no icons, no reordering, and no way to add new pages. This plugin changes that completely.

= Why You Need This Plugin =

* Your customers deserve a polished, branded account experience
* Support teams need custom account pages (e.g. "Help Center", "My Subscriptions")
* Membership and course sites need role-specific navigation
* Agencies need a white-label solution they can rebrand for clients

= Core Features =

* **Visual Drag & Drop Builder** — Reorder menu items instantly
* **Custom Endpoints** — Create new My Account pages with any HTML content or shortcodes
* **External Links** — Add links to any URL alongside account pages
* **Open in New Tab** — Per-item link target control
* **Live Preview** — See your menu exactly as customers will

= Icons =

* **Dashicons** — WordPress native icon set
* **Font Awesome 6** — Thousands of solid and regular icons
* **Visual Icon Picker** — 36+ quick-pick icons in the editor
* **Manual Input** — Use any CSS icon class

= Access Control =

* **Role-Based Visibility** — Show or hide items per user role (e.g. show "Wholesale" only to wholesale customers)
* **Enable / Disable** — Toggle items on and off without deleting
* **All Roles Supported** — Works with WooCommerce, membership plugins, and custom roles

= Premium Features (Free!) =

* **Separators** — Visual dividers between menu groups
* **Badges** — Static text labels like "New", "Pro", or "Hot"
* **Dynamic Counters** — Auto-display order count or download count
* **Descriptions** — Subtitle text under menu titles
* **Custom CSS Classes** — Advanced per-item styling
* **Duplicate Items** — One-click cloning

= Portability & Branding =

* **Export / Import JSON** — Migrate your menu between sites in seconds
* **White-Label Mode** — Rebrand plugin name, author, and settings tab label
* **Hide from Plugins List** — Full client-facing white-label

= WooCommerce Compatibility =

* ✅ **HPOS** — Fully compatible with High-Performance Order Storage
* ✅ **Cart & Checkout Blocks** — Fully compatible with block-based cart/checkout
* ✅ **WooCommerce 10.5+** — Tested with the latest release

= Multisite & Multilingual Ready =

* Translation-ready with .pot file and bundled translations
* Works on WordPress Multisite

= Getting Started =

After activation, navigate to **WooCommerce → Settings → My Account Menu** tab. That's it — start building!

== Installation ==

= Automatic Installation =

1. Go to **Plugins → Add New** in your WordPress admin
2. Search for **"My Account Menu Builder for WooCommerce"**
3. Click **Install Now** then **Activate**
4. Go to **WooCommerce → Settings → My Account Menu** tab

= Manual Installation =

1. Download the plugin ZIP from WordPress.org or GitHub
2. Go to **Plugins → Add New → Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Activate the plugin

= Finding the Settings =

The settings are located at **WooCommerce → Settings → My Account Menu** tab.

You can also click the **Settings** link on the Plugins page next to the plugin name.

[youtube https://www.youtube.com/watch?v=PLACEHOLDER]

== Frequently Asked Questions ==

= Where do I find the plugin settings? =

Go to **WooCommerce → Settings** and click the **My Account Menu** tab. It's located after the default WooCommerce tabs (General, Products, Shipping, etc.).

= My custom endpoint shows a 404 page =

After adding new endpoints, go to **Settings → Permalinks** and click **Save Changes** to flush rewrite rules. The plugin does this automatically on save, but a manual flush may be needed in some hosting configurations.

= Can I show different menu items to different user roles? =

Yes! Each menu item has a "Visible to Roles" option. Check the roles that should see the item. Leave all unchecked to make the item visible to everyone.

= Does this work with WooCommerce Memberships / Subscriptions? =

Yes. The plugin works with any WooCommerce extension that registers user roles. Custom roles from membership or subscription plugins will appear in the role visibility checkboxes.

= Can I add icons to menu items? =

Absolutely. Every item supports both WordPress Dashicons and Font Awesome 6 icons. Use the visual picker to choose from 36+ popular icons, or type any icon class manually.

= How do I create a custom "My Account" page? =

1. Add a new menu item and set type to **Endpoint**
2. Enter a title (e.g., "My Wishlist")
3. The slug auto-generates (e.g., `my-wishlist`)
4. Add your page content (HTML or shortcodes) in the "Endpoint Content" field
5. Save and visit `/my-account/my-wishlist/`

= Can I add external links (not WooCommerce pages)? =

Yes. Set the item type to **Link** and enter any URL. You can also choose to open it in a new tab.

= How do I migrate settings to another site? =

Click **Export** to download a JSON file, then **Import** on the other site. This transfers all menu items and white-label settings.

= Is it compatible with WooCommerce HPOS? =

Yes! The plugin explicitly declares compatibility with High-Performance Order Storage (HPOS). It only modifies the My Account navigation and never touches order data.

= Is it compatible with the block-based Cart & Checkout? =

Yes! The plugin declares full compatibility with WooCommerce Cart & Checkout Blocks. It operates exclusively on the My Account page and doesn't interact with cart or checkout functionality.

= Can I hide this plugin from clients? =

Yes! Enable **White Label** mode in the settings. You can rename the plugin, change the author, customize the tab label, and even hide it completely from the Plugins page.

= Does it support multisite? =

Yes, the plugin works on WordPress Multisite. Activate it per-site or network-wide.

= Is the plugin translation-ready? =

Yes! The plugin includes a `.pot` file and bundled translations for Spanish, French, German, Portuguese, Russian, Turkish, Chinese, Japanese, Arabic, and Italian. Community translations are welcome via [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/my-account-menu-builder-for-woocommerce/).

== Screenshots ==

1. Drag & drop menu builder with live preview sidebar
2. Item editor modal — title, type, icon picker, roles, badges
3. WooCommerce Settings tab — integrated right where you'd expect
4. Frontend result — polished My Account navigation with icons and badges
5. White-label settings — rebrand for client projects
6. Export/Import — one-click migration between sites

== Changelog ==

= 1.2.1 - 2026-02-09 =
* Updated: Plugin renamed from "Woo My Account Menu Builder" to "My Account Menu Builder for WooCommerce" to comply with WordPress.org trademark guidelines
* Updated: Plugin slug changed to `my-account-menu-builder-for-woocommerce`
* Updated: Text domain changed to `my-account-menu-builder-for-woocommerce`
* Updated: Tested up to WordPress 6.9
* Fixed: Removed `load_plugin_textdomain()` — WordPress.org handles translations automatically since WP 4.6
* Fixed: Font Awesome now bundled locally instead of loading from external CDN (WordPress.org compliance)
* Fixed: Improved input sanitization for all `$_POST` data with proper escaping annotations
* Fixed: All `admin_url()` calls wrapped in `esc_url()` for output escaping
* Fixed: Dynamic CSS output uses `sanitize_html_class()` for safe class names

= 1.2.0 - 2026-02-09 =
* Added: WooCommerce HPOS (High-Performance Order Storage) compatibility declaration
* Added: WooCommerce Cart & Checkout Blocks compatibility declaration
* Added: Full i18n support with .pot file and 10 bundled translations
* Added: load_plugin_textdomain for proper translation loading
* Updated: WC tested up to 10.5
* Updated: WordPress.org-ready readme with full documentation
* Fixed: WooCommerce "incompatible plugins" warning after WC 10.5 update

= 1.1.0 - 2026-02-08 =
* Added: Settings now live under WooCommerce → Settings → My Account Menu tab
* Added: Separator / divider support for grouping menu items
* Added: Static badge text (New, Pro, Hot, etc.) per item
* Added: Dynamic badge counters (order count, download count)
* Added: Description / subtitle text per menu item
* Added: Custom CSS class per item
* Added: Duplicate item with one click
* Added: Auto-generate endpoint slug from title
* Added: White-label: hide plugin from Plugins list
* Added: Plugin action link for quick Settings access
* Fixed: Endpoint URLs now use the proper endpoint slug
* Fixed: New tab (target="_blank") now works reliably
* Fixed: Fatal error from non-existent method hook

= 1.0.0 - 2026-02-08 =
* Initial release
* Visual drag & drop menu builder
* Custom endpoint and external link support
* Icon support (Dashicons + Font Awesome)
* Role-based visibility
* Export / Import JSON
* White-label mode
* Live preview sidebar

== Upgrade Notice ==

= 1.2.1 =
Plugin renamed to comply with WordPress.org trademark guidelines. Font Awesome bundled locally. Improved security hardening.

= 1.2.0 =
Critical update: Fixes WooCommerce 10.5 incompatibility warning. Adds HPOS and Cart/Checkout Blocks compatibility declarations. Adds 10 languages.

= 1.1.0 =
Major update: Moved settings to WooCommerce → Settings tab. Added separators, badges, counters, descriptions. Fixed endpoint URLs and new tab links.

== Contribute ==

Development happens on GitHub. Pull requests, bug reports, and feature requests are welcome:

* **GitHub:** [github.com/nurkamol/my-account-menu-builder-for-woocommerce](https://github.com/nurkamol/my-account-menu-builder-for-woocommerce)
* **Issues:** [Report a bug or request a feature](https://github.com/nurkamol/my-account-menu-builder-for-woocommerce/issues)

= Contributing Code =

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/your-feature`)
3. Commit your changes (`git commit -m 'Add your feature'`)
4. Push to the branch (`git push origin feature/your-feature`)
5. Open a Pull Request

== Translate ==

Help translate this plugin into your language:

* **WordPress.org:** [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/my-account-menu-builder-for-woocommerce/)
* **GitHub:** Submit `.po` files via pull request to the `/languages` directory

Bundled translations: Spanish, French, German, Portuguese (Brazil), Russian, Turkish, Chinese (Simplified), Japanese, Arabic, and Italian.
