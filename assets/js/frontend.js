/**
 * Woo My Account Menu Builder — Frontend
 *
 * Runs on the My Account page.
 * Handles: target="_blank", icon injection, badges, descriptions, custom CSS classes.
 */
(function ($) {
    'use strict';

    if (typeof wmabFront === 'undefined' || !wmabFront.items) {
        return;
    }

    $(function () {
        var items = wmabFront.items;

        $('.woocommerce-MyAccount-navigation-link').each(function () {
            var $li = $(this);
            var $a  = $li.find('> a');
            var key = getEndpointKey($li);

            if (!key || !items[key]) {
                return;
            }

            var cfg = items[key];

            // — Open in new tab —
            if (cfg.target === '_blank') {
                $a.attr('target', '_blank').attr('rel', 'noopener noreferrer');
            }

            // — Icon —
            if (cfg.icon) {
                var $icon = $('<span class="wmab-nav-icon"><i class="' + escAttr(cfg.icon) + '"></i></span>');
                $a.prepend($icon);
                $li.addClass('wmab-has-icon');
            }

            // — Badge —
            if (cfg.badge) {
                $a.append(' ' + cfg.badge);
            }

            // — Description —
            if (cfg.description) {
                $a.append('<span class="wmab-nav-desc">' + escHtml(cfg.description) + '</span>');
                $li.addClass('wmab-has-desc');
            }

            // — Custom CSS class —
            if (cfg.css_class) {
                $li.addClass(cfg.css_class);
            }
        });
    });

    /**
     * Extract the endpoint key from WooCommerce's class convention:
     * .woocommerce-MyAccount-navigation-link--{key}
     */
    function getEndpointKey($li) {
        var classes = $li.attr('class') || '';
        var match   = classes.match(/woocommerce-MyAccount-navigation-link--([^\s]+)/);
        return match ? match[1] : null;
    }

    function escAttr(str) {
        var div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function escHtml(str) {
        var div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

})(jQuery);
