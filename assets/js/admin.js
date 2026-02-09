/**
 * Woo My Account Menu Builder — Admin
 */
(function ($) {
    'use strict';

    var items     = wmabAdmin.menuItems || [];
    var editIndex = -1;

    /* ==================================================================
     * Boot
     * ================================================================*/
    $(function () {
        render();
        initSortable();
        bindEvents();
    });

    /* ==================================================================
     * Render
     * ================================================================*/
    function render() {
        renderList();
        renderPreview();
    }

    function renderList() {
        var $list  = $('#wmab-menu-list').empty();
        var $empty = $('.wmab-empty-state');

        if (!items.length) {
            $list.hide();
            $empty.show();
            return;
        }
        $list.show();
        $empty.hide();

        $.each(items, function (i, item) {
            if (item.type === 'separator') {
                $list.append(separatorHtml(i, item));
            } else {
                $list.append(itemHtml(i, item));
            }
        });
    }

    function itemHtml(i, item) {
        var icon = item.icon ? '<i class="' + esc(item.icon) + '"></i>' : '<i class="dashicons dashicons-marker"></i>';
        var typeBadge = item.type === 'link'
            ? '<span class="wmab-item-badge wmab-badge-link">Link</span>'
            : '<span class="wmab-item-badge wmab-badge-endpoint">Endpoint</span>';
        var disabledBadge = !item.enabled ? ' <span class="wmab-item-badge wmab-badge-disabled">Disabled</span>' : '';

        var meta = [];
        if (item.type === 'endpoint' && item.endpoint) {
            meta.push('<span><i class="dashicons dashicons-admin-links"></i> /my-account/' + esc(item.endpoint) + '/</span>');
        } else if (item.type === 'link' && item.url) {
            meta.push('<span><i class="dashicons dashicons-external"></i> ' + esc(item.url).substring(0, 40) + '</span>');
        }
        if (item.target === '_blank') meta.push('<span>↗ New tab</span>');
        if (item.roles && item.roles.length) meta.push('<span><i class="dashicons dashicons-groups"></i> ' + item.roles.join(', ') + '</span>');
        if (item.badge) meta.push('<span class="wmab-meta-badge">' + esc(item.badge) + '</span>');
        if (item.badge_count) meta.push('<span class="wmab-meta-badge">Count: ' + esc(item.badge_count) + '</span>');

        return '<li data-index="' + i + '"' + (!item.enabled ? ' class="wmab-item-disabled"' : '') + '>' +
            '<span class="wmab-item-drag dashicons dashicons-menu"></span>' +
            '<div class="wmab-item-icon">' + icon + '</div>' +
            '<div class="wmab-item-info">' +
                '<div class="wmab-item-title">' + esc(item.title) + ' ' + typeBadge + disabledBadge + '</div>' +
                '<div class="wmab-item-meta">' + meta.join('') + '</div>' +
            '</div>' +
            '<div class="wmab-item-actions">' +
                '<button type="button" class="wmab-btn-duplicate" title="Duplicate"><span class="dashicons dashicons-admin-page"></span></button>' +
                '<button type="button" class="wmab-btn-edit" title="Edit"><span class="dashicons dashicons-edit"></span></button>' +
                '<button type="button" class="wmab-btn-delete" title="Delete"><span class="dashicons dashicons-trash"></span></button>' +
            '</div></li>';
    }

    function separatorHtml(i, item) {
        return '<li data-index="' + i + '" class="wmab-separator-item">' +
            '<span class="wmab-item-drag dashicons dashicons-menu"></span>' +
            '<div class="wmab-sep-line"></div>' +
            '<span class="wmab-sep-label">— Separator —</span>' +
            '<div class="wmab-item-actions">' +
                '<button type="button" class="wmab-btn-delete" title="Delete"><span class="dashicons dashicons-trash"></span></button>' +
            '</div></li>';
    }

    function renderPreview() {
        var html = '<ul>';
        $.each(items, function (i, item) {
            if (!item.enabled) return;
            if (item.type === 'separator') {
                html += '<li class="wmab-pv-separator"><hr></li>';
                return;
            }
            var icon = item.icon ? '<span class="wmab-pv-icon"><i class="' + esc(item.icon) + '"></i></span>' : '';
            var badge = item.badge ? '<span class="wmab-pv-badge">' + esc(item.badge) + '</span>' : '';
            var active = i === 0 ? ' class="is-active"' : '';
            var target = item.target === '_blank' ? ' <i class="dashicons dashicons-external" style="font-size:12px;width:12px;height:12px;"></i>' : '';
            html += '<li' + active + '><a href="#">' + icon + '<span>' + esc(item.title) + '</span>' + badge + target + '</a></li>';
        });
        html += '</ul>';
        $('#wmab-preview').html(html);
    }

    /* ==================================================================
     * Sortable
     * ================================================================*/
    function initSortable() {
        $('#wmab-menu-list').sortable({
            handle: '.wmab-item-drag',
            placeholder: 'ui-sortable-placeholder',
            tolerance: 'pointer',
            update: function () {
                var reordered = [];
                $('#wmab-menu-list li').each(function () {
                    var idx = parseInt($(this).data('index'), 10);
                    if (items[idx]) reordered.push(items[idx]);
                });
                items = reordered;
                render();
            }
        });
    }

    /* ==================================================================
     * Events
     * ================================================================*/
    function bindEvents() {
        // Tabs
        $(document).on('click', '.wmab-tab', function (e) {
            e.preventDefault();
            var tab = $(this).data('tab');
            $('.wmab-tab').removeClass('active');
            $(this).addClass('active');
            $('.wmab-tab-content').removeClass('active');
            $('#wmab-tab-' + tab).addClass('active');
        });

        // Add item
        $(document).on('click', '.wmab-btn-add-item', function () {
            editIndex = -1;
            openModal();
        });

        // Add separator
        $(document).on('click', '.wmab-btn-add-separator', function () {
            items.push({
                id: genId(), title: '—', type: 'separator', endpoint: '', url: '', content: '',
                icon: '', roles: [], target: '_self', enabled: true, is_default: false,
                css_class: '', badge: '', badge_count: '', description: ''
            });
            render();
        });

        // Edit
        $(document).on('click', '.wmab-btn-edit', function () {
            editIndex = $(this).closest('li').data('index');
            openModal(items[editIndex]);
        });

        // Duplicate
        $(document).on('click', '.wmab-btn-duplicate', function () {
            var idx  = $(this).closest('li').data('index');
            var copy = JSON.parse(JSON.stringify(items[idx]));
            copy.id  = genId();
            copy.title += ' (copy)';
            copy.is_default = false;
            if (copy.endpoint) copy.endpoint += '-copy';
            items.splice(idx + 1, 0, copy);
            render();
        });

        // Delete
        $(document).on('click', '.wmab-btn-delete', function () {
            if (!confirm(wmabAdmin.strings.confirmDelete)) return;
            items.splice($(this).closest('li').data('index'), 1);
            render();
        });

        // Save
        $(document).on('click', '.wmab-btn-save', saveMenu);

        // Reset
        $(document).on('click', '.wmab-btn-reset-defaults', function () {
            if (!confirm(wmabAdmin.strings.confirmReset)) return;
            items = JSON.parse(JSON.stringify(wmabAdmin.defaultItems));
            render();
            saveMenu();
        });

        // Export / Import
        $(document).on('click', '.wmab-btn-export', doExport);
        $(document).on('click', '.wmab-btn-import', function () { $('#wmab-import-file').click(); });
        $('#wmab-import-file').on('change', doImport);

        // Modal
        $(document).on('click', '.wmab-modal-close, .wmab-modal-cancel', closeModal);
        $(document).on('click', '.wmab-modal-overlay', function (e) {
            if ($(e.target).hasClass('wmab-modal-overlay')) closeModal();
        });
        $(document).on('click', '.wmab-modal-save', saveItem);
        $(document).on('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

        // Type toggle
        $(document).on('change', 'input[name="wmab-item-type"]', function () {
            var isLink = $(this).val() === 'link';
            $('.wmab-field-endpoint').toggle(!isLink);
            $('.wmab-field-link').toggle(isLink);
        });

        // Icon picker
        $(document).on('click', '.wmab-icon-pick', function () {
            var icon = $(this).data('icon');
            $('#wmab-item-icon').val(icon);
            updateIconPreview(icon);
        });
        $(document).on('input', '#wmab-item-icon', function () { updateIconPreview($(this).val()); });

        // Auto-generate slug from title
        $('#wmab-item-title').on('input', function () {
            if (editIndex > -1) return; // only for new items
            var slug = $(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            if ($('#wmab-item-endpoint').data('manual')) return;
            $('#wmab-item-endpoint').val(slug);
        });
        $('#wmab-item-endpoint').on('input', function () {
            $(this).data('manual', true);
        });

        // Roles checkbox styling
        $(document).on('change', '.wmab-roles-grid input[type="checkbox"]', function () {
            $(this).closest('label').toggleClass('checked', this.checked);
        });

        // Save settings
        $(document).on('click', '.wmab-btn-save-settings', saveSettings);
    }

    /* ==================================================================
     * Modal
     * ================================================================*/
    function openModal(item) {
        item = item || {};

        $('#wmab-modal-title').text(editIndex === -1 ? 'Add Menu Item' : 'Edit Menu Item');
        $('#wmab-item-title').val(item.title || '');
        $('input[name="wmab-item-type"][value="' + (item.type || 'endpoint') + '"]').prop('checked', true).trigger('change');
        $('#wmab-item-endpoint').val(item.endpoint || '').data('manual', editIndex > -1);
        $('#wmab-item-url').val(item.url || '');
        $('#wmab-item-content').val(item.content || '');
        $('#wmab-item-icon').val(item.icon || 'dashicons dashicons-star-filled');
        $('input[name="wmab-item-target"][value="' + (item.target || '_self') + '"]').prop('checked', true);
        $('#wmab-item-enabled').prop('checked', item.enabled !== false);
        $('#wmab-item-badge').val(item.badge || '');
        $('#wmab-item-badge-count').val(item.badge_count || '');
        $('#wmab-item-description').val(item.description || '');
        $('#wmab-item-css-class').val(item.css_class || '');

        updateIconPreview(item.icon || 'dashicons dashicons-star-filled');

        // Roles
        var $grid = $('#wmab-roles-grid').empty();
        var roles = item.roles || [];
        $.each(wmabAdmin.roles, function (_, role) {
            var checked = roles.indexOf(role.slug) > -1;
            $grid.append('<label' + (checked ? ' class="checked"' : '') + '><input type="checkbox" value="' + esc(role.slug) + '"' + (checked ? ' checked' : '') + '> ' + esc(role.name) + '</label>');
        });

        $('#wmab-modal').fadeIn(150);
        setTimeout(function () { $('#wmab-item-title').focus(); }, 200);
    }

    function closeModal() {
        $('#wmab-modal').fadeOut(150);
        editIndex = -1;
    }

    function saveItem() {
        var title = $('#wmab-item-title').val().trim();
        if (!title) {
            notice('error', wmabAdmin.strings.titleRequired);
            $('#wmab-item-title').focus();
            return;
        }

        var type     = $('input[name="wmab-item-type"]:checked').val();
        var endpoint = $('#wmab-item-endpoint').val().trim().toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/^-+|-+$/g, '');
        var url      = $('#wmab-item-url').val().trim();

        if (type === 'endpoint' && !endpoint) {
            endpoint = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        }
        if (type === 'endpoint' && !endpoint) {
            notice('error', wmabAdmin.strings.slugRequired);
            $('#wmab-item-endpoint').focus();
            return;
        }
        if (type === 'link' && !url) {
            notice('error', wmabAdmin.strings.urlRequired);
            $('#wmab-item-url').focus();
            return;
        }

        var roles = [];
        $('#wmab-roles-grid input:checked').each(function () { roles.push($(this).val()); });

        var data = {
            id:          (editIndex > -1 && items[editIndex]) ? items[editIndex].id : genId(),
            title:       title,
            type:        type,
            endpoint:    endpoint,
            url:         url,
            content:     $('#wmab-item-content').val(),
            icon:        $('#wmab-item-icon').val().trim(),
            roles:       roles,
            target:      $('input[name="wmab-item-target"]:checked').val(),
            enabled:     $('#wmab-item-enabled').is(':checked'),
            is_default:  (editIndex > -1 && items[editIndex]) ? items[editIndex].is_default || false : false,
            css_class:   $('#wmab-item-css-class').val().trim(),
            badge:       $('#wmab-item-badge').val().trim(),
            badge_count: $('#wmab-item-badge-count').val(),
            description: $('#wmab-item-description').val().trim()
        };

        if (editIndex > -1) {
            items[editIndex] = data;
        } else {
            items.push(data);
        }

        closeModal();
        render();
    }

    /* ==================================================================
     * AJAX
     * ================================================================*/
    function saveMenu() {
        $.post(wmabAdmin.ajaxUrl, {
            action: 'wmab_save_menu',
            nonce:  wmabAdmin.nonce,
            items:  JSON.stringify(items)
        }, function (res) {
            if (res.success) {
                items = res.data.items;
                render();
                notice('success', wmabAdmin.strings.saveSuccess);
            } else {
                notice('error', wmabAdmin.strings.saveError);
            }
        }).fail(function () { notice('error', wmabAdmin.strings.saveError); });
    }

    function doExport() {
        $.post(wmabAdmin.ajaxUrl, { action: 'wmab_export_menu', nonce: wmabAdmin.nonce }, function (res) {
            if (!res.success) return;
            var blob = new Blob([JSON.stringify(res.data, null, 2)], { type: 'application/json' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'woo-myaccount-menu-' + new Date().toISOString().slice(0, 10) + '.json';
            a.click();
        });
    }

    function doImport(e) {
        var file = e.target.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (ev) {
            try {
                var data = JSON.parse(ev.target.result);
                if (!data.items || !Array.isArray(data.items)) {
                    notice('error', wmabAdmin.strings.importError);
                    return;
                }
                $.post(wmabAdmin.ajaxUrl, {
                    action: 'wmab_import_menu', nonce: wmabAdmin.nonce, json: ev.target.result
                }, function (res) {
                    if (res.success) {
                        items = res.data.items;
                        render();
                        notice('success', wmabAdmin.strings.importSuccess);
                    } else {
                        notice('error', wmabAdmin.strings.importError);
                    }
                });
            } catch (err) {
                notice('error', wmabAdmin.strings.importError);
            }
        };
        reader.readAsText(file);
        e.target.value = '';
    }

    function saveSettings() {
        var settings = {
            enabled:     $('#wmab-wl-enabled').is(':checked'),
            plugin_name: $('#wmab-wl-plugin-name').val(),
            author_name: $('#wmab-wl-author-name').val(),
            author_url:  $('#wmab-wl-author-url').val(),
            menu_title:  $('#wmab-wl-menu-title').val(),
            hide_plugin: $('#wmab-wl-hide-plugin').is(':checked')
        };
        $.post(wmabAdmin.ajaxUrl, {
            action: 'wmab_save_settings', nonce: wmabAdmin.nonce, settings: JSON.stringify(settings)
        }, function (res) {
            notice(res.success ? 'success' : 'error', res.success ? wmabAdmin.strings.settingsSaved : 'Error');
        });
    }

    /* ==================================================================
     * Helpers
     * ================================================================*/
    function updateIconPreview(cls) {
        $('.wmab-icon-preview').html('<i class="' + esc(cls || '') + '"></i>');
    }

    function notice(type, msg) {
        var $n = $('.wmab-notice').removeClass('success error').addClass(type).text(msg).fadeIn();
        clearTimeout($n.data('timer'));
        $n.data('timer', setTimeout(function () { $n.fadeOut(); }, 4000));
    }

    function genId() {
        return 'wmab-' + Math.random().toString(36).substr(2, 9);
    }

    function esc(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

})(jQuery);
