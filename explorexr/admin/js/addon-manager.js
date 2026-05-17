/**
 * ExploreXR Addon Manager JavaScript
 * 
 * Handles drag-and-drop functionality and visibility toggling for addons in Edit Mode
 */
jQuery(document).ready(function($) {
    'use strict';
    
    // Variables to track changes
    var addonOrderChanged = false;
    var addonVisibilityChanged = false;
    var ajaxSaving = false;
      // Get model ID from the page
    var modelId = $('#explorexrPremium_model_id').val() || $('input[name="model_id"]').val();
    
    // Debug logging
    if (typeof ExploreXRLogger !== 'undefined') {
        ExploreXRLogger.log('explorexrPremium Addon Manager: Script loaded');
        ExploreXRLogger.log('Model ID:', modelId);
        ExploreXRLogger.log('Sortable container found:', $('#explorexr-premium-addon-sortable').length > 0);
        ExploreXRLogger.log('Sortable items found:', $('.explorexr-premium-sortable-item').length);
    }
    
    // Initialize sortable functionality if the element exists
    if ($('#explorexr-premium-addon-sortable').length) {
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('Initializing sortable...');
        }
        $('#explorexr-premium-addon-sortable').sortable({
            handle: '.explorexr-premium-sortable-handle',
            placeholder: 'explorexr-premium-sortable-placeholder',
            tolerance: 'pointer',
            axis: 'y',
            opacity: 0.7,
            update: function(event, ui) {
                // Get the new order of addons
                var newOrder = [];
                $('.explorexr-premium-sortable-item').each(function() {
                    newOrder.push($(this).data('addon'));
                });
                
                // Update the hidden input with the new order
                $('#explorexrPremium_addon_order').val(newOrder.join(','));
                
                // Add visual feedback that changes need to be saved
                $('.explorexr-premium-card.explorexr-premium-addon-manager-card').addClass('explorexr-premium-unsaved-changes');
                $('.explorexr-premium-form-actions').addClass('explorexr-premium-highlight-save');
                
                // Mark as changed for potential AJAX save
                addonOrderChanged = true;
                
                // Auto-save if quick save is enabled
                if ($('#explorexrPremium_addon_quick_save').is(':checked')) {
                    saveAddonSettings();
                }            }
        });
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('Sortable initialized successfully');
        }
    } else {
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('Sortable container not found - no items to sort');
        }
    }
    
    // Handle visibility toggle switches
    $('.explorexr-premium-sortable-item .explorexr-premium-switch input[type="checkbox"]').on('change', function() {
        var $item = $(this).closest('.explorexr-premium-sortable-item');
        var addonKey = $item.data('addon');
        var isVisible = $(this).is(':checked');
        
        // Add visual feedback for the toggle state
        if (isVisible) {
            $item.removeClass('explorexr-premium-addon-hidden');
        } else {
            $item.addClass('explorexr-premium-addon-hidden');
        }
        
        // Add visual feedback that changes need to be saved
        $('.explorexr-premium-card.explorexr-premium-addon-manager-card').addClass('explorexr-premium-unsaved-changes');
        $('.explorexr-premium-form-actions').addClass('explorexr-premium-highlight-save');
        
        // Update the visible state of the actual addon section in the page
        var $addonSection = $('.explorexr-premium-addon-section[data-addon="' + addonKey + '"]');
        if ($addonSection.length) {
            if (isVisible) {
                $addonSection.removeClass('explorexr-premium-addon-section-hidden').addClass('explorexr-premium-addon-section-visible');
                
                // If the section is now visible but empty (was hidden before), we need to reload the page
                // to get the content, but only do this on AJAX save to avoid too many reloads
                if ($addonSection.is(':empty') && $('#explorexrPremium_addon_quick_save').is(':checked')) {
                    setTimeout(function() {
                        location.reload();
                    }, 1000); // Reload after 1 second to show the save feedback first
                }
            } else {
                $addonSection.removeClass('explorexr-premium-addon-section-visible').addClass('explorexr-premium-addon-section-hidden');
            }
        }
        
        // Mark as changed for potential AJAX save
        addonVisibilityChanged = true;
        
        // Auto-save if quick save is enabled
        if ($('#explorexrPremium_addon_quick_save').is(':checked')) {
            saveAddonSettings();
        }
    });
    
    // Add save button to the addon manager card header
    if ($('.explorexr-premium-card.explorexr-premium-addon-manager-card .card-header-actions').length) {
        $('<div class="explorexr-premium-addon-save-container">')
            .append('<button type="button" id="explorexrPremium_save_addon_settings" class="button button-small">' +
                    '<span class="dashicons dashicons-saved" style="margin-right: 3px; font-size: 14px;"></span> Save Addon Settings</button>')
            .append('<label class="explorexr-premium-quick-save-checkbox"><input type="checkbox" id="explorexrPremium_addon_quick_save"> Quick save</label>')
            .appendTo('.explorexr-premium-card.explorexr-premium-addon-manager-card .explorexr-premium-card-content');
    }
    
    // Handle save button click
    $('#explorexrPremium_save_addon_settings').on('click', function() {
        saveAddonSettings();
    });

    // Function to save addon settings via AJAX
    function saveAddonSettings() {
        // Don't save if already saving or no changes
        if (ajaxSaving || (!addonOrderChanged && !addonVisibilityChanged)) {
            return;
        }
        
        ajaxSaving = true;
        
        // Show saving indicator
        var $saveButton = $('#explorexrPremium_save_addon_settings');
        var originalText = $saveButton.html();
        $saveButton.html('<span class="dashicons dashicons-update explorexr-premium-spin"></span> Saving...');
        $saveButton.prop('disabled', true);
        
        // Collect current settings
        var addonOrder = [];
        $('.explorexr-premium-sortable-item').each(function() {
            addonOrder.push($(this).data('addon'));
        });
        
        var addonVisibility = {};
        $('.explorexr-premium-sortable-item').each(function() {
            var addonKey = $(this).data('addon');
            var isVisible = $(this).find('.explorexr-premium-switch input[type="checkbox"]').is(':checked');
            addonVisibility[addonKey] = isVisible;
        });
        
        // Save via AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'explorexrPremium_save_addon_settings',
                nonce: explorexrPremiumAddonManager.nonce,
                model_id: modelId,
                addon_order: JSON.stringify(addonOrder),
                addon_visibility: JSON.stringify(addonVisibility)
            },
            success: function(response) {
                if (response.success) {                    // Show success message
                    $saveButton.html('<span class="dashicons dashicons-yes"></span> Saved!');
                    
                    // Show a success notification
                    $('<div class="explorexr-premium-addon-save-notification">')
                        .text('Addon settings saved successfully')
                        .appendTo('.explorexr-premium-addon-save-container')
                        .fadeIn(300)
                        .delay(1500)
                        .fadeOut(300, function() { $(this).remove(); });
                    
                    // Remove unsaved changes indicators
                    $('.explorexr-premium-card.explorexr-premium-addon-manager-card').removeClass('explorexr-premium-unsaved-changes');
                    $('.explorexr-premium-form-actions').removeClass('explorexr-premium-highlight-save');
                    
                    // Reset change flags
                    addonOrderChanged = false;
                    addonVisibilityChanged = false;
                    
                    // Update hidden input for form submission
                    $('#explorexrPremium_addon_order').val(addonOrder.join(','));
                    
                    // Also store as a JSON string in a hidden input field
                    if (!$('#explorexrPremium_addon_visibility_json').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            id: 'explorexrPremium_addon_visibility_json',
                            name: 'explorexrPremium_addon_visibility_json'
                        }).appendTo('form');
                    }
                    
                    $('#explorexrPremium_addon_visibility_json').val(JSON.stringify(addonVisibility));
                    
                    // Reset the button after a delay
                    setTimeout(function() {
                        $saveButton.html(originalText);
                        $saveButton.prop('disabled', false);
                    }, 1500);
                } else {
                    // Show error message
                    $saveButton.html('<span class="dashicons dashicons-warning"></span> Failed!');
                    if (typeof ExploreXRLogger !== 'undefined') {
                        ExploreXRLogger.error('ExploreXR: Failed to save addon settings', response);
                    }
                    
                    // Reset the button after a delay
                    setTimeout(function() {
                        $saveButton.html(originalText);
                        $saveButton.prop('disabled', false);
                    }, 1500);
                }
            },
            error: function(xhr, status, error) {
                // Show error message
                $saveButton.html('<span class="dashicons dashicons-warning"></span> Failed!');
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.error('ExploreXR: AJAX error when saving addon settings', error);
                }
                
                // Reset the button after a delay
                setTimeout(function() {
                    $saveButton.html(originalText);
                    $saveButton.prop('disabled', false);
                }, 1500);
            },
            complete: function() {
                ajaxSaving = false;
            }
        });
    }

    // Function to collect addon visibility settings before form submission
    function collectAddonSettings() {
        var visibilitySettings = {};
        
        $('.explorexr-premium-sortable-item').each(function() {
            var addonKey = $(this).data('addon');
            var isVisible = $(this).find('.explorexr-premium-switch input[type="checkbox"]').is(':checked');
            visibilitySettings[addonKey] = isVisible;
        });
        
        // Store as a JSON string in a hidden input field that will be submitted with the form
        if (!$('#explorexrPremium_addon_visibility_json').length) {
            $('<input>').attr({
                type: 'hidden',
                id: 'explorexrPremium_addon_visibility_json',
                name: 'explorexrPremium_addon_visibility_json'
            }).appendTo('form');
        }
        
        $('#explorexrPremium_addon_visibility_json').val(JSON.stringify(visibilitySettings));
    }

    // Attach to form submission
    $('form').on('submit', function() {
        collectAddonSettings();
    });
    
    // Add spinning animation for the saving indicator
    $('<style>')
        .text('@keyframes explorexr-premium-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } } ' +
              '.explorexr-premium-spin { animation: explorexr-premium-spin 2s linear infinite; } ' +
              '.explorexr-premium-quick-save-checkbox { margin-left: 10px; font-size: 12px; } ' +
              '.explorexr-premium-addon-save-container { margin-top: 15px; }')
        .appendTo('head');
});
