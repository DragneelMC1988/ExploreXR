/**
 * ExploreXR Addons Page JavaScript
 * Handles addon management actions, testing, and modal interactions
 */

jQuery(document).ready(function($) {
    'use strict';

    // Cache DOM elements
    const $testModal = $('#test-results-modal');
    const $testResults = $('#test-results-content');
    const $loadingSpinner = $('.loading-spinner');

    /**
     * Initialize page functionality
     */
    function init() {
        bindEvents();
        initializeTooltips();
    }

    /**
     * Bind event handlers
     */
    function bindEvents() {
        // Test all addons button
        $('#test-all-addons').on('click', handleTestAllAddons);
        
        // Clear cache button
        $('#clear-cache').on('click', handleClearCache);
        
        // Activate all options button
        $('#activate-all-options').on('click', handleActivateAllOptions);
        
        // Individual addon test buttons
        $('.test-addon-btn').on('click', handleTestSingleAddon);
        
        // Modal close handlers
        $('.modal-close, .modal-overlay').on('click', closeModal);
        
        // Direct install buttons
        $('.explorexr-direct-install-btn').on('click', handleDirectInstall);

        // Settings form auto-save
        $('.addon-global-settings input[type="checkbox"]').on('change', handleSettingsChange);
        
        // Escape key closes modal
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $testModal.is(':visible')) {
                closeModal();
            }
        });
    }

    /**
     * Initialize tooltips
     */
    function initializeTooltips() {
        $('.tooltip').each(function() {
            const $tooltip = $(this);
            const text = $tooltip.data('tooltip');
            
            $tooltip.on('mouseenter', function() {
                showTooltip($tooltip, text);
            }).on('mouseleave', function() {
                hideTooltip();
            });
        });
    }

    /**
     * Handle test all addons action
     */
    function handleTestAllAddons(e) {
        e.preventDefault();
        
        showLoading('Testing all addons...');
        
        $.ajax({
            url: explorexrPremiumAdminAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'explorexrPremium_test_all_addons',
                nonce: explorexrPremiumAdminAjax.nonce
            },
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    showTestResults('All Addons Test Results', response.data.results);
                } else {
                    showError('Test failed: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                showError('AJAX error: ' + error);
            }
        });
    }

    /**
     * Handle test single addon action
     */
    function handleTestSingleAddon(e) {
        e.preventDefault();
        
        const $button = $(this);
        const addonSlug = $button.data('addon');
        const addonName = $button.closest('.addon-item').find('.addon-title').text();
        
        $button.prop('disabled', true).text('Testing...');
        
        $.ajax({
            url: explorexrPremiumAdminAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'explorexrPremium_test_single_addon',
                addon: addonSlug,
                nonce: explorexrPremiumAdminAjax.nonce
            },
            success: function(response) {
                $button.prop('disabled', false).text('Test');
                
                if (response.success) {
                    showTestResults(addonName + ' Test Results', [response.data]);
                } else {
                    showError('Test failed: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                $button.prop('disabled', false).text('Test');
                showError('AJAX error: ' + error);
            }
        });
    }

    /**
     * Handle clear cache action
     */
    function handleClearCache(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to clear all addon cache? This may temporarily affect performance.')) {
            return;
        }
        
        const $button = $(this);
        $button.prop('disabled', true).text('Clearing...');
        
        $.ajax({
            url: explorexrPremiumAdminAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'explorexrPremium_clear_addon_cache',
                nonce: explorexrPremiumAdminAjax.nonce
            },
            success: function(response) {
                $button.prop('disabled', false).text('Clear Cache');
                
                if (response.success) {
                    showSuccess('Cache cleared successfully!');
                    // Refresh page to show updated status
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showError('Failed to clear cache: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                $button.prop('disabled', false).text('Clear Cache');
                showError('AJAX error: ' + error);
            }
        });
    }

    /**
     * Handle activate all options action
     */
    function handleActivateAllOptions(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to activate all available addon options? This will enable all features.')) {
            return;
        }
        
        const $button = $(this);
        $button.prop('disabled', true).text('Activating...');
        
        $.ajax({
            url: explorexrPremiumAdminAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'explorexrPremium_activate_all_options',
                nonce: explorexrPremiumAdminAjax.nonce
            },
            success: function(response) {
                $button.prop('disabled', false).text('Activate All Options');
                
                if (response.success) {
                    showSuccess('All options activated successfully!');
                    // Refresh page to show updated status
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showError('Failed to activate options: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                $button.prop('disabled', false).text('Activate All Options');
                showError('AJAX error: ' + error);
            }
        });
    }

    /**
     * Handle settings auto-save
     */
    function handleSettingsChange(e) {
        const $checkbox = $(this);
        const setting = $checkbox.attr('name');
        const value = $checkbox.is(':checked') ? 1 : 0;
        
        // Visual feedback
        $checkbox.closest('.setting-item').addClass('saving');
        
        $.ajax({
            url: explorexrPremiumAdminAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'explorexrPremium_save_addon_setting',
                setting: setting,
                value: value,
                nonce: explorexrPremiumAdminAjax.nonce
            },
            success: function(response) {
                $checkbox.closest('.setting-item').removeClass('saving');
                
                if (response.success) {
                    $checkbox.closest('.setting-item').addClass('saved');
                    setTimeout(() => {
                        $checkbox.closest('.setting-item').removeClass('saved');
                    }, 2000);
                } else {
                    showError('Failed to save setting: ' + (response.data.message || 'Unknown error'));
                    // Revert checkbox state
                    $checkbox.prop('checked', !$checkbox.is(':checked'));
                }
            },
            error: function(xhr, status, error) {
                $checkbox.closest('.setting-item').removeClass('saving');
                showError('AJAX error: ' + error);
                // Revert checkbox state
                $checkbox.prop('checked', !$checkbox.is(':checked'));
            }
        });
    }

    /**
     * Show test results in modal
     */
    function showTestResults(title, results) {
        let html = `<h3>${title}</h3>`;
        
        if (Array.isArray(results)) {
            results.forEach(result => {
                const statusClass = result.success ? 'success' : 'error';
                const statusIcon = result.success ? '✓' : '✗';
                
                html += `
                    <div class="test-result-item ${statusClass}">
                        <div class="test-result-header">
                            <span class="test-status">${statusIcon}</span>
                            <strong>${result.addon_name || result.addon}</strong>
                        </div>
                        <div class="test-result-message">${result.message}</div>
                        ${result.details ? `<div class="test-result-details">${result.details}</div>` : ''}
                    </div>
                `;
            });
        } else {
            html += `<div class="test-result-item">${results}</div>`;
        }
        
        $testResults.html(html);
        openModal();
    }

    /**
     * Show loading state
     */
    function showLoading(message = 'Processing...') {
        $loadingSpinner.find('.loading-text').text(message);
        $loadingSpinner.show();
    }

    /**
     * Hide loading state
     */
    function hideLoading() {
        $loadingSpinner.hide();
    }

    /**
     * Open modal
     */
    function openModal() {
        $testModal.addClass('active');
        $('body').addClass('modal-open');
    }

    /**
     * Close modal
     */
    function closeModal() {
        $testModal.removeClass('active');
        $('body').removeClass('modal-open');
    }

    /**
     * Show success notification
     */
    function showSuccess(message) {
        showNotification(message, 'success');
    }

    /**
     * Show error notification
     */
    function showError(message) {
        showNotification(message, 'error');
    }

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
     * Show tooltip
     */
    function showTooltip($element, text) {
        const $tooltip = $(`<div class="tooltip-popup">${text}</div>`);
        $('body').append($tooltip);
        
        const elementRect = $element[0].getBoundingClientRect();
        const tooltipRect = $tooltip[0].getBoundingClientRect();
        
        $tooltip.css({
            top: elementRect.top - tooltipRect.height - 10,
            left: elementRect.left + (elementRect.width / 2) - (tooltipRect.width / 2)
        });
        
        $tooltip.addClass('visible');
    }

    /**
     * Hide tooltip
     */
    function hideTooltip() {
        $('.tooltip-popup').remove();
    }

    /**
     * Refresh addon status
     */
    function refreshAddonStatus() {
        $.ajax({
            url: explorexrPremiumAdminAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'explorexrPremium_get_addon_status',
                nonce: explorexrPremiumAdminAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateAddonUI(response.data);
                }
            }
        });
    }

    /**
     * Update addon UI with fresh data
     */
    function updateAddonUI(data) {
        // Update addon grid items
        data.addons.forEach(addon => {
            const $addonItem = $(`.addon-item[data-addon="${addon.slug}"]`);
            if ($addonItem.length) {
                $addonItem.removeClass('active inactive not-installed not-licensed')
                         .addClass(addon.status);
                $addonItem.find('.addon-status').text(addon.status_text);
            }
        });
        
        // Update license info
        if (data.license) {
            $('.license-tier').text(data.license.tier);
            $('.license-status').text(data.license.status);
        }
    }    /**
     * Initialize Addon Manager functionality
     */
    function initializeAddonManager() {
        // Initialize sortable addon list
        if ($.fn.sortable && $('#explorexr-premium-global-addon-list').length) {
            $('#explorexr-premium-global-addon-list').sortable({
                handle: '.drag-handle',
                axis: 'y',
                update: function() {
                    // Update hidden inputs when order changes
                    updateAddonOrderInputs();
                }
            });
        }

        // Toggle visibility buttons
        $('.toggle-visibility').on('click', function() {
            const $item = $(this).closest('.explorexr-premium-addon-item');
            const isVisible = $item.hasClass('addon-visible');
            
            // Toggle class
            $item.toggleClass('addon-visible addon-hidden');
            
            // Update icon
            $(this).removeClass('dashicons-visibility dashicons-hidden')
                   .addClass(isVisible ? 'dashicons-hidden' : 'dashicons-visibility')
                   .attr('title', isVisible ? 'Hidden (click to show)' : 'Visible (click to hide)');
            
            // Update hidden input
            $item.find('.visibility-value').val(isVisible ? '0' : '1');
        });

        // Reset to default order button
        $('button[name="explorexrPremium_reset_global_settings"]').on('click', function(e) {
            if (!confirm('Reset addon order and visibility to default settings?')) {
                e.preventDefault();
            }
        });
    }

    /**
     * Update hidden inputs after sorting
     */
    function updateAddonOrderInputs() {
        // Update the order inputs to match the new visual order
        $('#explorexr-premium-global-addon-list .explorexr-premium-addon-item').each(function(index) {
            const addonKey = $(this).data('addon');
            $(this).find('input[name="explorexrPremium_addon_order[]"]').val(addonKey);
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

    // Initialize when DOM is ready
    init();

    // Expose functions for external use
    window.explorexrPremiumAddons = {
        refreshStatus: refreshAddonStatus,
        showTestResults: showTestResults,
        showNotification: showNotification
    };
});
