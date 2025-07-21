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
    $('.explorexr-tab').on('click', function() {
        const tabId = $(this).data('tab');
        const tabGroup = $(this).closest('.explorexr-tabs').parent();
        
        // Update active tab
        tabGroup.find('.explorexr-tab').removeClass('active');
        $(this).addClass('active');
        
        // Show the selected tab content
        tabGroup.find('.explorexr-tab-content').removeClass('active');
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
    $('.explorexr-device-tab').on('click', function() {
        const deviceId = $(this).data('device');
        const deviceGroup = $(this).closest('.explorexr-device-tabs').parent();
        
        // Update active device tab
        deviceGroup.find('.explorexr-device-tab').removeClass('active');
        $(this).addClass('active');
        
        // Show the selected device content
        deviceGroup.find('.explorexr-device-content').removeClass('active');
        deviceGroup.find(`#${deviceId}-size`).addClass('active');
    });
    
    // When a predefined size is selected, make sure the Custom Size field is not marked as selected
    $('input[name="viewer_size"][value!="custom"]').on('change', function() {
        if ($(this).is(':checked')) {
            $('#custom_size_field').prop('checked', false);
        }
    });
    
    // Enhanced file input functionality
    $('.explorexr-styled-file-input').on('change', function() {
        const $wrapper = $(this).closest('.explorexr-file-input-wrapper');
        const $textElement = $wrapper.find('.explorexr-file-input-text');
        
        if (this.files.length > 0) {
            $textElement.text(this.files[0].name);
            $wrapper.find('.explorexr-file-input-decoration').css('border-style', 'solid');
        } else {
            $textElement.text('Choose a file or drag it here');
            $wrapper.find('.explorexr-file-input-decoration').css('border-style', 'dashed');
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
    $('#explorexr-select-poster').on('click', function(e) {
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
            var previewElement = $('#explorexr-poster-preview');
            previewElement.show().find('img').attr('src', attachment.url);
        });
        
        // Open the uploader dialog
        mediaUploader.open();
    });
    
    // When removing poster is checked, hide the preview
    $('input[name="remove_poster"]').on('change', function() {
        if ($(this).is(':checked')) {
            $('#explorexr-poster-preview').hide();
        } else {
            $('#explorexr-poster-preview').show();
        }
    });
    
    // Addon integration removed from free version
    
    /**
     * Show notification message
     */
    function showNotification(message, type = 'info') {
        const $notification = $(`
            <div class="explorexr-edit-notification ${type}">
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
    
    // AR functionality removed from free version - available in premium only
    
    // Auto-rotate settings toggle
    $('#explorexr_auto_rotate').on('change', function() {
        const autoRotateSettings = $('#auto-rotate-settings');
        if ($(this).is(':checked')) {
            autoRotateSettings.slideDown();
        } else {
            autoRotateSettings.slideUp();
        }
    });
    
    function isValidURL(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
});
