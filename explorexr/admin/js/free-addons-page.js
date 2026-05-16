/**
 * ExploreXR Free — Free Add-on Admin Page
 *
 * Handles Install & Select and Select-only AJAX calls.
 * Data is injected via wp_add_inline_script as explorexrFreeAddons global.
 */
(function ($) {
    'use strict';

    if (typeof explorexrFreeAddons === 'undefined') {
        return;
    }

    var cfg = explorexrFreeAddons;

    function showError($btn, message) {
        $btn.removeClass('is-loading').prop('disabled', false);
        $btn.closest('.explorexr-free-addon-card')
            .find('.explorexr-free-addon-card__status')
            .html('<span style="color:#d63638;">' + message + '</span>');
    }

    // Dismiss feature notice
    $(document).on('click', '#explorexr-new-feature-notice .notice-dismiss', function () {
        $.post(cfg.ajaxUrl, {
            action: 'explorexr_dismiss_free_addon_hint',
            nonce:  cfg.dismissNonce
        });
    });

    // Install & Select
    $(document).on('click', '.explorexr-install-addon-btn', function () {
        var $btn  = $(this);
        var slug  = $btn.data('slug');

        $btn.addClass('is-loading').prop('disabled', true)
            .text(cfg.strings.installing);

        $.post(cfg.ajaxUrl, {
            action: 'explorexr_direct_download_addon',
            nonce:  cfg.installNonce,
            slug:   slug
        })
        .done(function (response) {
            if (response && response.success) {
                $btn.text(cfg.strings.installed);
                if (response.data && response.data.reload) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 800);
                }
            } else {
                var msg = (response && response.data && response.data.message)
                    ? response.data.message
                    : cfg.strings.error;
                showError($btn, msg);
            }
        })
        .fail(function () {
            showError($btn, cfg.strings.error);
        });
    });

    // Select (already installed)
    $(document).on('click', '.explorexr-select-addon-btn', function () {
        var $btn = $(this);
        var slug = $btn.data('slug');

        $btn.addClass('is-loading').prop('disabled', true);

        $.post(cfg.ajaxUrl, {
            action: 'explorexr_free_select_addon',
            nonce:  cfg.selectNonce,
            slug:   slug
        })
        .done(function (response) {
            if (response && response.success) {
                window.location.reload();
            } else {
                var msg = (response && response.data && response.data.message)
                    ? response.data.message
                    : cfg.strings.error;
                showError($btn, msg);
            }
        })
        .fail(function () {
            showError($btn, cfg.strings.error);
        });
    });

})(jQuery);
