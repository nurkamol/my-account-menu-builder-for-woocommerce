# My Account Menu Builder for WooCommerce

[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0%E2%80%9310.5-96588a.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4.svg)](https://php.net/)
[![HPOS Compatible](https://img.shields.io/badge/HPOS-Compatible-brightgreen.svg)](#)
[![License](https://img.shields.io/badge/License-GPLv2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

The easiest way to customize the WooCommerce My Account page menu — drag & drop, custom pages, icons, badges, role-based visibility, and more.

---

## ✨ Features

**Core** — Drag & drop reorder, custom endpoints with HTML/shortcode content, external links, new-tab support, live preview sidebar

**Icons** — Dashicons + Font Awesome 6, visual picker with 36+ icons, manual class input

**Access Control** — Role-based visibility per item, enable/disable toggle, all default and custom roles

**Premium (Free!)** — Separators, static badges, dynamic order/download counters, descriptions, custom CSS classes, item duplication

**Portability** — Export/Import JSON, white-label mode (rename, rebrand, hide from Plugins list)

**Compatibility** — HPOS ✅, Cart & Checkout Blocks ✅, WooCommerce 10.5 ✅

**i18n** — Translation-ready with `.pot` file and 10 bundled languages

---

## 📦 Installation

1. Download the latest release ZIP
2. **Plugins → Add New → Upload Plugin** in WordPress admin
3. Activate the plugin
4. Go to **WooCommerce → Settings → My Account Menu** tab

### Requirements

- WordPress 5.8+, PHP 7.4+, WooCommerce 5.0+

---

## 📁 File Structure

```
my-account-menu-builder-for-woocommerce/
├── my-account-menu-builder-for-woocommerce.php     # Main plugin file + WC feature declarations
├── includes/
│   ├── class-wmab-settings.php         # Admin GUI, WC Settings tab, AJAX
│   ├── class-wmab-frontend.php         # Frontend menu override, icons, badges
│   └── class-wmab-endpoints.php        # Custom endpoint registration
├── assets/
│   ├── css/admin.css, frontend.css     # Styles
│   ├── js/admin.js, frontend.js        # Scripts
│   ├── banner-772x250.png              # WordPress.org banner
│   ├── icon-128x128.png               # Plugin icon
│   └── screenshot-*.png               # Screenshots
├── languages/
│   ├── woo-myaccount-builder.pot       # Translation template
│   └── woo-myaccount-builder-*.po/mo   # Translations (10 languages)
├── readme.txt                          # WordPress.org readme
├── CHANGELOG.md
└── LICENSE
```

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Translating

Submit `.po` files via PR to `/languages`, or contribute at [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/my-account-menu-builder-for-woocommerce/).

---

## 📄 License

GNU General Public License v2.0 — see [LICENSE](LICENSE).

---

## 👤 Author

**Nurkamol Vakhidov** — [nurkamol.com](https://nurkamol.com) · [nurkamol@gmail.com](mailto:nurkamol@gmail.com) · [@nurkamol](https://github.com/nurkamol)
