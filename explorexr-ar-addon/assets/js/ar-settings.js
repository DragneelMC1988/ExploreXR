/**
 * ExploreXR AR Add-On - Settings Page JavaScript
 * 
 * Handles media uploads and interactive elements on the AR settings page
 */

jQuery(document).ready(function($) {
    // Initialize color picker for AR background color
    $('.explorexr-premium-color-picker').wpColorPicker();
    
    // Media uploader for image fields
    $(document).on('click', '.ar-select-image', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const targetField = $('#' + button.data('target'));
        const previewContainer = button.siblings('.image-preview-container');
        
        // Create media uploader
        const frame = wp.media({
            title: explorexrARSettings.selectImage || 'Select Image',
            button: { text: explorexrARSettings.useImage || 'Use this image' },
            multiple: false,
            library: { type: 'image' }
        });
        
        // When image is selected
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            targetField.val(attachment.url);
            
            // Update preview
            previewContainer.html('<div class="image-preview"><img src="' + attachment.url + '" alt="Preview" style="max-height: 50px;"></div>');
            
            // Show remove button if not already present
            if (button.siblings('.ar-remove-image').length === 0) {
                $('<button type="button" class="button ar-remove-image" data-target="' + button.data('target') + '">' + 
                  (explorexrARSettings.removeImage || 'Remove') + '</button>').insertAfter(button);
            }
        });
        
        frame.open();
    });
    
    // Remove image
    $(document).on('click', '.ar-remove-image', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const targetField = $('#' + button.data('target'));
        const previewContainer = button.siblings('.image-preview-container');
        
        targetField.val('');
        previewContainer.empty();
        button.remove();
    });
    
    // File uploader for other file fields
    $(document).on('click', '.ar-select-file', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const targetField = $('#' + button.data('target'));
        
        // Create media uploader
        const frame = wp.media({
            title: explorexrARSettings.selectFile || 'Select File',
            button: { text: explorexrARSettings.useFile || 'Use this file' },
            multiple: false
        });
        
        // When file is selected
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            targetField.val(attachment.url);
        });
        
        frame.open();
    });
    
    // Ensure at least one AR mode is selected
    $('input[name="explorexr_ar_modes[]"]').on('change', function() {
        const checkedModes = $('input[name="explorexr_ar_modes[]"]:checked').length;
        if (checkedModes === 0) {
            $(this).prop('checked', true);
            alert(explorexrARSettings.modeRequired || 'At least one AR mode must be selected.');
        }
    });
    
    // Enhanced UI interactions for better user experience
    setupEnhancedUI();
});

/**
 * Setup enhanced UI interactions
 * Provides visual feedback and improved usability
 */
function setupEnhancedUI() {
    const $ = jQuery;
    
    // Add visual feedback for checkbox interactions
    $('.explorexr-premium-checkbox-label').on('mouseenter', function() {
        $(this).addClass('hover-highlight');
    }).on('mouseleave', function() {
        $(this).removeClass('hover-highlight');
    });
    
    // Enhanced upload button interactions
    $('.ar-select-image, .ar-select-file').on('click', function() {
        $(this).prop('disabled', true).text(explorexrARSettings.selectingFile || 'Selecting...');
    });
    
    // Reset button state if media dialog is cancelled
    $(document).on('click', '.media-modal-close', function() {
        $('.ar-select-image, .ar-select-file').prop('disabled', false).each(function() {
            const isImage = $(this).hasClass('ar-select-image');
            $(this).text(isImage ? 
                (explorexrARSettings.selectImage || 'Select Image') : 
                (explorexrARSettings.selectFile || 'Select File')
            );
        });
    });
    
    // Add confirmation for remove actions
    $(document).on('click', '.ar-remove-image, .ar-remove-file', function(e) {
        if (!confirm(explorexrARSettings.confirmRemove || 'Are you sure you want to remove this file?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Initialize maintenance tools
    setupMaintenanceTools();
}

/**
 * Setup maintenance tools functionality
 * Handles AJAX requests for maintenance actions
 */
function setupMaintenanceTools() {
    const $ = jQuery;
    
    // Handle maintenance tool button clicks
    $('.maintenance-tool button[data-action]').on('click', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const action = button.data('action');
        const resultContainer = button.siblings('.ar-action-result');
        
        // Confirm action with user
        const confirmMessage = getConfirmationMessage(action);
        if (!confirm(confirmMessage)) {
            return;
        }
        
        // Disable button and show loading state
        button.prop('disabled', true).addClass('loading');
        const originalText = button.text();
        button.text('Processing...');
        
        // Clear previous results
        resultContainer.removeClass('success error').empty();
          // Perform AJAX request
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'explorexr_ar_maintenance',
                action_type: action,
                nonce: explorexrARSettings.maintenanceNonce
            },
            success: function(response) {
                if (response.success) {
                    resultContainer.addClass('success').html('<p class="success-message">✓ ' + response.message + '</p>');
                } else {
                    resultContainer.addClass('error').html('<p class="error-message">✗ ' + response.message + '</p>');
                }
            },
            error: function(xhr, status, error) {
                resultContainer.addClass('error').html('<p class="error-message">✗ Error: ' + error + '</p>');
            },
            complete: function() {
                // Re-enable button and restore original text
                button.prop('disabled', false).removeClass('loading').text(originalText);
                
                // Auto-hide success messages after 5 seconds
                setTimeout(function() {
                    resultContainer.filter('.success').fadeOut();
                }, 5000);
            }
        });
    });
}

/**
 * Get confirmation message for maintenance action
 */
function getConfirmationMessage(action) {
    const messages = {
        'reset_ar_options': 'Are you sure you want to reset all AR options to their default values? This action cannot be undone.',
        'clear_ar_cache': 'Are you sure you want to clear the AR cache? This will remove all cached AR data.',
        'clear_model_cache': 'Are you sure you want to clear the model cache? This will remove all cached model data and thumbnails.',
        'flush_js_cache': 'Are you sure you want to flush the AR JavaScript cache? This will clear all cached JavaScript files.'
    };
    
    return messages[action] || 'Are you sure you want to perform this action?';
}
