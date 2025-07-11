/**
 * Edit Model Page JavaScript
 * 
 * Handles all interactions on the Edit Model page
 */
jQuery(document).ready(function($) {
    'use strict';
    
    // WordPress admin menu compatibility - avoid interfering with WordPress menu functionality
    function fixAdminMenuScroll() {
        try {
            // Only fix critical scroll issues without breaking WordPress admin menu hover
            $('html, body').css({
                'overflow': 'visible',
                'height': 'auto'
            });
            
            // Don't override WordPress admin menu positioning as it breaks hover functionality
            // The WordPress core handles admin menu positioning correctly
            
        } catch (e) {
            console.warn('ExploreXR: Admin menu scroll fix error:', e);
        }
    }
    
    // Elementor compatibility - delay our fix to avoid conflicts
    setTimeout(function() {
        try {
            // Call the fix function
            fixAdminMenuScroll();
        } catch (e) {
            console.warn('ExploreXR: Delayed admin menu fix error:', e);
        }
    }, 500);
    
    // Tab functionality
    $('.expoxr-tab').on('click', function() {
        const tabId = $(this).data('tab');
        const tabGroup = $(this).closest('.expoxr-tabs').parent();
        
        // Update active tab
        tabGroup.find('.expoxr-tab').removeClass('active');
        $(this).addClass('active');
        
        // Show the selected tab content
        tabGroup.find('.expoxr-tab-content').removeClass('active');
        tabGroup.find(`#${tabId}`).addClass('active');
        
        // Update hidden input values for form submission
        if (tabId === 'upload-model') {
            $('#model_source_input').val('upload');
        } else if (tabId === 'existing-model') {
            $('#model_source_input').val('existing');
        } else if (tabId === 'upload-poster') {
            $('#poster_method_input').val('upload');
        } else if (tabId === 'library-poster') {
            $('#poster_method_input').val('library');
        }
    });
    
    // Device tab functionality
    $('.expoxr-device-tab').on('click', function() {
        const deviceId = $(this).data('device');
        const deviceGroup = $(this).closest('.expoxr-device-tabs').parent();
        
        // Update active device tab
        deviceGroup.find('.expoxr-device-tab').removeClass('active');
        $(this).addClass('active');
        
        // Show the selected device content
        deviceGroup.find('.expoxr-device-content').removeClass('active');
        deviceGroup.find(`#${deviceId}-size`).addClass('active');
    });
    
    // When a predefined size is selected, make sure the Custom Size field is not marked as selected
    $('input[name="viewer_size"][value!="custom"]').on('change', function() {
        if ($(this).is(':checked')) {
            $('#custom_size_field').prop('checked', false);
        }
    });
    
    // Enhanced file input functionality
    $('.expoxr-styled-file-input').on('change', function() {
        const $wrapper = $(this).closest('.expoxr-file-input-wrapper');
        const $textElement = $wrapper.find('.expoxr-file-input-text');
        
        if (this.files.length > 0) {
            $textElement.text(this.files[0].name);
            $wrapper.find('.expoxr-file-input-decoration').css('border-style', 'solid');
        } else {
            $textElement.text('Choose a file or drag it here');
            $wrapper.find('.expoxr-file-input-decoration').css('border-style', 'dashed');
        }
    });
    
    // Copy shortcode functionality
    $('.copy-shortcode').on('click', function() {
        const shortcode = $(this).data('shortcode');
        
        // Create a temporary textarea element to copy the text
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(shortcode).select();
        document.execCommand('copy');
        $temp.remove();
        
        // Show a temporary tooltip
        const $this = $(this);
        const originalText = $this.html();
        $this.html('<span class="dashicons dashicons-yes"></span> Copied!');
        
        setTimeout(function() {
            $this.html(originalText);
        }, 2000);
    });
    
    // Media library for poster image
    var mediaUploader;
    $('#expoxr-select-poster').on('click', function(e) {
        e.preventDefault();
        
        // Check if wp.media is available
        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
            console.error('WordPress Media Library is not available');
            alert('Media Library functionality is not available. Please check your WordPress installation.');
            return;
        }
        
        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        // Create the media uploader
        mediaUploader = wp.media({
            title: 'Select Model Poster Image',
            button: {
                text: 'Use this image'
            },
            multiple: false  // Set to true if you want to select multiple images
        });
        
        // When an image is selected in the media manager...
        mediaUploader.on('select', function() {
            // Get the selected attachment details
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            
            // Update the form fields with the selected image details
            $('#model_poster_id').val(attachment.id);
            $('#model_poster_url').val(attachment.url);
            
            // Show the preview
            var previewElement = $('#expoxr-poster-preview');
            previewElement.show().find('img').attr('src', attachment.url);
        });
        
        // Open the uploader dialog
        mediaUploader.open();
    });
    
    // When removing poster is checked, hide the preview
    $('input[name="remove_poster"]').on('change', function() {
        if ($(this).is(':checked')) {
            $('#expoxr-poster-preview').hide();
        } else {
            $('#expoxr-poster-preview').show();
        }
    });
    
    // Addon Settings Functionality
    initAddonSettings();
    
    function initAddonSettings() {
        // Reset addon settings to defaults
        $('.reset-addon-settings').on('click', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const addonSlug = $button.data('addon');
            const $addonSection = $button.closest('.expoxr-addon-settings-section');
            
            if (!confirm('Are you sure you want to reset all settings for this addon to their default values?')) {
                return;
            }
            
            // Get default values from data attributes or make AJAX call
            resetAddonToDefaults(addonSlug, $addonSection);
        });
        
        // Add change tracking for addon settings
        $('.addon-option-field input, .addon-option-field select, .addon-option-field textarea').on('change', function() {
            const $field = $(this);
            $field.closest('.addon-option-field').addClass('modified');
            
            // Add unsaved changes warning
            window.addEventListener('beforeunload', function(e) {
                if ($('.addon-option-field.modified').length > 0) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });
        
        // Remove unsaved changes warning on form submit
        $('form').on('submit', function() {
            window.removeEventListener('beforeunload', arguments.callee);
        });
        
        // Color picker enhancement
        $('.addon-option-field input[type="color"]').each(function() {
            const $colorInput = $(this);
            const $wrapper = $('<div class="color-picker-wrapper"></div>');
            
            $colorInput.wrap($wrapper);
            $colorInput.after('<div class="color-preview" style="background-color: ' + $colorInput.val() + '"></div>');
            
            $colorInput.on('change input', function() {
                $(this).siblings('.color-preview').css('background-color', this.value);
            });
        });
        
        // Collapsible addon sections for better UX
        $('.addon-settings-title').on('click', function() {
            const $title = $(this);
            const $section = $title.closest('.expoxr-addon-settings-section');
            const $container = $section.find('.addon-options-container');
            
            $container.slideToggle(300);
            $section.toggleClass('collapsed');
        });
        
        // Add expand/collapse indicator
        $('.addon-settings-title').append('<span class="collapse-indicator">−</span>');
        
        $('.expoxr-addon-settings-section').on('click', '.addon-settings-title', function() {
            const $indicator = $(this).find('.collapse-indicator');
            const isCollapsed = $(this).closest('.expoxr-addon-settings-section').hasClass('collapsed');
            $indicator.text(isCollapsed ? '+' : '−');
        });
    }
    
    /**
     * Reset addon settings to default values
     */
    function resetAddonToDefaults(addonSlug, $addonSection) {
        // Make AJAX call to get default values
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'expoxr_get_addon_defaults',
                addon: addonSlug,
                nonce: $('#expoxr_edit_nonce').val()
            },
            success: function(response) {
                if (response.success && response.data.defaults) {
                    const defaults = response.data.defaults;
                    
                    // Reset each field to its default value
                    Object.keys(defaults).forEach(function(optionKey) {
                        const defaultValue = defaults[optionKey];
                        const fieldName = `expoxr_addon_settings[${addonSlug}][${optionKey}]`;
                        const $field = $addonSection.find(`[name="${fieldName}"]`);
                        
                        if ($field.length) {
                            if ($field.is(':checkbox')) {
                                $field.prop('checked', !!defaultValue);
                            } else {
                                $field.val(defaultValue);
                            }
                            
                            // Trigger change event
                            $field.trigger('change');
                        }
                    });
                    
                    // Show success message
                    showNotification('Addon settings reset to defaults', 'success');
                } else {
                    showNotification('Failed to reset settings: ' + (response.data.message || 'Unknown error'), 'error');
                }
            },
            error: function() {
                showNotification('AJAX error while resetting settings', 'error');
            }
        });
    }
      /**
     * Show notification message
     */
    function showNotification(message, type = 'info') {
        const $notification = $(`
            <div class="expoxr-edit-notification ${type}">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `);
        
        $('body').append($notification);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            $notification.fadeOut(() => $notification.remove());
        }, 3000);
        
        // Manual close
        $notification.find('.notification-close').on('click', () => {
            $notification.fadeOut(() => $notification.remove());
        });
    }
    
    // AR Settings Functionality
    initARSettings();
    
    function initARSettings() {
        // Toggle AR settings based on checkbox state
        $('input[name="expoxr_ar_enabled"]').on('change', function() {
            if ($(this).is(':checked')) {
                $('#ar-settings-container').slideDown(300);
            } else {
                $('#ar-settings-container').slideUp(300);
            }
        });
        
        // Media Library selection for AR button image
        var arButtonMediaUploader;
        $('#expoxr-select-ar-button').on('click', function(e) {
            e.preventDefault();
            
            // If the wp.media API is available
            if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
                // If the uploader object has already been created, reopen the dialog
                if (arButtonMediaUploader) {
                    arButtonMediaUploader.open();
                    return;
                }
                
                // Create the media uploader
                arButtonMediaUploader = wp.media({
                    title: 'Select or Upload AR Button Image',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                arButtonMediaUploader.on('select', function() {
                    const attachment = arButtonMediaUploader.state().get('selection').first().toJSON();
                    $('#expoxr_ar_button_image').val(attachment.url);
                    
                    // Update preview
                    const previewDiv = $('#ar-button-preview');
                    previewDiv.show().html(`<img src="${attachment.url}" alt="AR Button Preview">`);
                    
                    // Show remove button if not already present
                    if ($('#expoxr-remove-ar-button').length === 0) {
                        const $removeBtn = $('<button type="button" class="expoxr-button expoxr-button-link" id="expoxr-remove-ar-button">Remove</button>');
                        $('#expoxr-select-ar-button').after($removeBtn);
                        
                        // Bind remove event
                        $removeBtn.on('click', removeARButtonImage);
                    }
                });
                
                arButtonMediaUploader.open();
            } else {
                alert('Media Library functionality is not available.');
            }
        });
        
        // Remove AR button image
        $(document).on('click', '#expoxr-remove-ar-button', removeARButtonImage);
        
        function removeARButtonImage(e) {
            e.preventDefault();
            $('#expoxr_ar_button_image').val('');
            $('#ar-button-preview').hide().empty();
            $('#expoxr-remove-ar-button').remove();
        }
        
        // USDZ file upload toggle
        $('#expoxr_upload_usdz_btn').on('click', function(e) {
            e.preventDefault();
            $('#expoxr_usdz_upload').slideToggle(300);
        });
        
        // Handle USDZ file selection
        $('#expoxr_usdz_file').on('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                const fileName = file.name;
                
                // Validate USDZ file
                if (!fileName.toLowerCase().endsWith('.usdz')) {
                    alert('Please select a valid USDZ file.');
                    $(this).val('');
                    return;
                }
                
                // Show selected file name
                const $feedback = $('<div class="file-feedback">Selected: ' + fileName + '</div>');
                $(this).siblings('.file-feedback').remove();
                $(this).after($feedback);
                
                // Auto-hide the upload section after selection
                setTimeout(() => {
                    $('#expoxr_usdz_upload').slideUp(300);
                }, 1000);
            }
        });
        
        // AR field validation
        $('#expoxr_ar_button_text').on('blur', function() {
            const value = $(this).val().trim();
            if (value === '') {
                $(this).val('View in AR'); // Reset to default
                showNotification('AR button text cannot be empty. Reset to default.', 'warning');
            }
        });
        
        // XR Environment URL validation
        $('#expoxr_ar_xr_environment').on('blur', function() {
            const value = $(this).val().trim();
            if (value !== '' && !isValidURL(value)) {
                showNotification('Please enter a valid URL for XR environment.', 'warning');
                $(this).focus();
            }
        });
        
        // USDZ Model URL validation
        $('#expoxr_ar_usdz_model').on('blur', function() {
            const value = $(this).val().trim();
            if (value !== '' && !isValidURL(value)) {
                showNotification('Please enter a valid URL for USDZ model.', 'warning');
                $(this).focus();
            }
        });
        
        // Height validation
        $('#expoxr_ar_min_height').on('blur', function() {
            const value = $(this).val().trim();
            if (value !== '' && !value.match(/^\d+(px|%|em|rem|vh|vw)$/)) {
                showNotification('Please enter a valid height value (e.g., 400px, 50vh).', 'warning');
                $(this).focus();
            }
        });
        
        // AR modes validation - ensure at least one is selected
        $('input[name="expoxr_ar_modes[]"]').on('change', function() {
            const checkedModes = $('input[name="expoxr_ar_modes[]"]:checked').length;
            if (checkedModes === 0) {
                showNotification('Please select at least one AR technology.', 'warning');
                $(this).prop('checked', true); // Keep this one checked
            }
        });
        
        // Add AR preview functionality if model is available
        const modelPreview = $('#expoxr-model-preview-container model-viewer');
        if (modelPreview.length > 0) {
            updateARPreview();
            
            // Update AR preview when settings change
            $('#ar-settings-container input, #ar-settings-container select').on('change', function() {
                setTimeout(updateARPreview, 100); // Small delay to ensure values are updated
            });
        }
        
        function updateARPreview() {
            const $modelViewer = $('#expoxr-model-preview-container model-viewer');
            if ($modelViewer.length === 0) return;
            
            const arEnabled = $('#expoxr_ar_enabled').is(':checked');
            const arModes = [];
            $('input[name="expoxr_ar_modes[]"]:checked').each(function() {
                arModes.push($(this).val());
            });
            
            if (arEnabled && arModes.length > 0) {
                $modelViewer.attr('ar', '');
                $modelViewer.attr('ar-modes', arModes.join(' '));
                
                const placement = $('#expoxr_ar_placement').val();
                if (placement && placement !== 'floor') {
                    $modelViewer.attr('ar-placement', placement);
                } else {
                    $modelViewer.removeAttr('ar-placement');
                }
                
                const scale = $('#expoxr_ar_scale').val();
                if (scale && scale !== 'auto') {
                    $modelViewer.attr('ar-scale', scale);
                } else {
                    $modelViewer.removeAttr('ar-scale');
                }
                
                const usdzModel = $('#expoxr_ar_usdz_model').val();
                if (usdzModel) {
                    $modelViewer.attr('ios-src', usdzModel);
                } else {
                    $modelViewer.removeAttr('ios-src');
                }
                
                const xrEnvironment = $('#expoxr_ar_xr_environment').val();
                if (xrEnvironment) {
                    $modelViewer.attr('xr-environment', xrEnvironment);
                } else {
                    $modelViewer.removeAttr('xr-environment');
                }
            } else {
                $modelViewer.removeAttr('ar');
                $modelViewer.removeAttr('ar-modes');
                $modelViewer.removeAttr('ar-placement');
                $modelViewer.removeAttr('ar-scale');
                $modelViewer.removeAttr('ios-src');
                $modelViewer.removeAttr('xr-environment');
            }
        }
        
        function isValidURL(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
    }
});
