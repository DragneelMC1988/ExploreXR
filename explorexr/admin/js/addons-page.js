/**
 * ExploreXR Addons Page JavaScript
 * Handles the direct install (download + activate) button on the Addons admin page.
 */

jQuery(document).ready(function($) {
    'use strict';

    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        const $notification = $(`
            <div class="explorexr-premium-notification ${type}">
                <div class="notification-content">
                    <span class="notification-message">${message}</span>
                    <button class="notification-close">&times;</button>
                </div>
            </div>
        `);

        $('body').append($notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            $notification.remove();
        }, 5000);

        // Manual close
        $notification.find('.notification-close').on('click', () => {
            $notification.remove();
        });
    }

    /**
     * Handle direct install (one-click download + activate) from update server.
     */
    function handleDirectInstall(e) {
        e.preventDefault();
        var $btn = $(this);
        var slug = $btn.data('slug');

        $btn.prop('disabled', true)
            .html('<span class="dashicons dashicons-update explorexr-spin"></span> ' + explorexrPremiumAdminAjax.strings.installing);

        $.ajax({
            url:  explorexrPremiumAdminAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'explorexr_direct_download_addon',
                slug:   slug,
                nonce:  explorexrPremiumAdminAjax.installNonce
            },
            timeout: 60000,
            success: function(response) {
                if (response.success) {
                    showNotification(response.data.message, 'success');
                    if (response.data.reload) {
                        setTimeout(function() { location.reload(); }, 1500);
                    }
                } else {
                    var msg = (response.data && response.data.message) ? response.data.message : explorexrPremiumAdminAjax.strings.error;
                    showNotification(msg, 'error');
                    $btn.prop('disabled', false)
                        .html('<span class="dashicons dashicons-download"></span> Install Add-on');
                }
            },
            error: function() {
                showNotification(explorexrPremiumAdminAjax.strings.error + ': Request failed.', 'error');
                $btn.prop('disabled', false)
                    .html('<span class="dashicons dashicons-download"></span> Install Add-on');
            }
        });
    }

    $('.explorexr-direct-install-btn').on('click', handleDirectInstall);
});
