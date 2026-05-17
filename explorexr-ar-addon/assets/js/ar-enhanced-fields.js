/**
 * ExploreXR AR Add-On - Enhanced Fields JavaScript
 * 
 * Handles enhanced field interactions on the model edit page
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Media uploader for image fields
    $(document).on('click', '.ar-select-image', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const targetField = $('#' + button.data('target'));
        const previewContainer = button.siblings('.image-preview-container');
        
        const frame = wp.media({
            title: explorexrAREnhanced.selectImage || 'Select or Upload AR Button Image',
            button: { text: explorexrAREnhanced.useImage || 'Use this image' },
            multiple: false,
            library: { type: 'image' }
        });
        
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            targetField.val(attachment.url);
            
            // Update preview
            previewContainer.html('<div class="image-preview"><img src="' + attachment.url + '" alt="' + 
                (explorexrAREnhanced.buttonPreview || 'AR Button Preview') + '" style="max-height: 50px;"></div>');
            
            // Show remove button if not already present
            if (button.siblings('.ar-remove-image').length === 0) {
                $('<button type="button" class="button ar-remove-image" data-target="' + button.data('target') + '">' + 
                  (explorexrAREnhanced.removeImage || 'Remove') + '</button>').insertAfter(button);
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
        
        if (confirm(explorexrAREnhanced.confirmRemove || 'Are you sure you want to remove this image?')) {
            targetField.val('');
            previewContainer.empty();
            button.remove();
        }
    });
    
    // File uploader for other file fields
    $(document).on('click', '.ar-select-file', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const targetField = $('#' + button.data('target'));
        
        // Change button text while selecting
        const originalText = button.text();
        button.text(explorexrAREnhanced.selectingFile || 'Selecting...');
        
        const frame = wp.media({
            title: explorexrAREnhanced.selectFile || 'Select or Upload File',
            button: { text: explorexrAREnhanced.useFile || 'Use this file' },
            multiple: false
        });
        
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            targetField.val(attachment.url);
            button.text(originalText);
        });
        
        frame.on('close', function() {
            button.text(originalText);
        });
        
        frame.open();
    });
    
    // Ensure at least one AR mode is selected
    $(document).on('change', 'input[name="explorexr_addon_settings[explorexr-premium-ar][explorexr_ar_modes][]"]', function() {
        const checkedModes = $('input[name="explorexr_addon_settings[explorexr-premium-ar][explorexr_ar_modes][]"]:checked').length;
        if (checkedModes === 0) {
            $(this).prop('checked', true);
            alert(explorexrAREnhanced.modeRequired || 'At least one AR mode must be selected.');
        }
    });
    
    // Toggle dependent fields based on AR enabled state
    $(document).on('change', 'input[name="explorexr_addon_settings[explorexr-premium-ar][explorexr_ar_enabled]"]', function() {
        const arSection = $(this).closest('.explorexr-premium-addon-settings-section');
        const otherFields = arSection.find('.addon-option-field').not(':first');
        
        if ($(this).is(':checked')) {
            otherFields.slideDown(300);
        } else {
            otherFields.slideUp(300);
        }
    });
    
    // Enhanced AR mode validation
    function validateARModes() {
        const $modes = $('input[name="explorexr_addon_settings[explorexr-premium-ar][explorexr_ar_modes][]"]');
        const checkedCount = $modes.filter(':checked').length;
        
        if (checkedCount === 0) {
            // Auto-select the most compatible mode
            $modes.filter('[value="webxr"]').prop('checked', true);
        }
        
        return checkedCount > 0;
    }
    
    // Form validation before submit
    $('form').on('submit', function(e) {
        if (!validateARModes()) {
            e.preventDefault();
            alert(explorexrAREnhanced.modeRequired || 'At least one AR mode must be selected.');
            return false;
        }
    });
    
    // Initialize enhanced fields on page load
    function initializeAREnhancedFields() {
        // Set initial state for AR enabled checkbox
        const arEnabled = $('input[name="explorexr_addon_settings[explorexr-premium-ar][explorexr_ar_enabled]"]').is(':checked');
        if (!arEnabled) {
            const arSection = $('input[name="explorexr_addon_settings[explorexr-premium-ar][explorexr_ar_enabled]"]').closest('.explorexr-premium-addon-settings-section');
            arSection.find('.addon-option-field').not(':first').hide();
        }
        
        // Add visual feedback for file uploads
        $('.ar-select-image, .ar-select-file').each(function() {
            $(this).hover(
                function() {
                    $(this).addClass('hover-effect');
                },
                function() {
                    $(this).removeClass('hover-effect');
                }
            );
        });
        
        // Add tooltip support for AR mode checkboxes
        $('.explorexr-premium-checkbox-label').each(function() {
            const $label = $(this);
            const $input = $label.find('input[type="checkbox"]');
            
            if ($input.attr('name') && $input.attr('name').includes('ar_modes')) {
                $label.attr('title', getARModeDescription($input.val()));
            }
        });
    }
    
    // Get AR mode descriptions for tooltips
    function getARModeDescription(mode) {
        const descriptions = {
            'webxr': explorexrAREnhanced.webxrDesc || 'AR support in web browsers that support WebXR',
            'scene-viewer': explorexrAREnhanced.sceneViewerDesc || 'Android AR using Google Scene Viewer',
            'quick-look': explorexrAREnhanced.quickLookDesc || 'iOS AR using Apple Quick Look'
        };
        
        return descriptions[mode] || '';
    }
    
    // Initialize when DOM is ready
    initializeAREnhancedFields();
    
    // Add change tracking for user feedback
    let hasUnsavedChanges = false;
    
    $('input, select, textarea').on('change', function() {
        hasUnsavedChanges = true;
    });
    
    $('form').on('submit', function() {
        hasUnsavedChanges = false;
    });
    
    // Warn user about unsaved changes
    $(window).on('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            const message = explorexrAREnhanced.unsavedChanges || 'You have unsaved changes. Are you sure you want to leave?';
            e.returnValue = message;
            return message;
        }
    });
});
