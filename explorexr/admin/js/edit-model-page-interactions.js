/**
 * Edit Model Page Interactions JavaScript
 * 
 * Handles additional interactions specific to the Edit Model page
 * that are not covered by the main edit-model.js file
 */
jQuery(document).ready(function($) {
    'use strict';
    
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
    });    // When a predefined size is selected, update the width/height fields and disable custom size field
    $('input[name="viewer_size"][value!="custom"]').on('change', function() {
        if ($(this).is(':checked')) {
            // Disable the custom size hidden field so it doesn't interfere
            $('#custom_size_field').prop('disabled', true);
            
            // Update width/height fields based on predefined size
            const selectedSize = $(this).val();
            let width, height;
            
            switch(selectedSize) {
                case 'small':
                    width = '300px';
                    height = '300px';
                    break;
                case 'medium':
                    width = '500px';
                    height = '500px';
                    break;
                case 'large':
                    width = '800px';
                    height = '600px';
                    break;
                case 'full':
                    width = '98vw';
                    height = '98vh';
                    break;
                default:
                    return; // Don't update for unknown sizes
            }
            
            // Update the width/height input fields
            $('#viewer_width').val(width);
            $('#viewer_height').val(height);
        }
    });
    
    // When custom size tab is clicked, enable the custom size field
    $('.explorexr-tab[data-tab="custom-sizes"]').on('click', function() {
        $('#custom_size_field').prop('disabled', false);
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
    $('#explorexr-select-poster').on('click', function(e) {
        e.preventDefault();
        
        // Check if wp.media is available
        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
            console.error('WordPress Media Library is not available');
            alert('Media Library functionality is not available. Please check your WordPress installation.');
            return;
        }
        
        // Initialize or reuse the media uploader instance
        var mediaUploader;
        
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
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            // Get media attachment details from the frame state
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            
            $('#model_poster_id').val(attachment.id);
            $('#model_poster_url').val(attachment.url);
            
            const previewElement = $('#explorexr-poster-preview');
            previewElement.show().find('img').attr('src', attachment.url);
            
            // Bug Fix #4: Hide upload section after poster is selected
            $('#upload-poster').hide();
        });
        
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
    
    // Initialize the display size form state on page load
    function initializeDisplaySizeState() {
        const activeTab = $('.explorexr-tab.active[data-tab]').data('tab');
        const selectedPredefinedSize = $('input[name="viewer_size"]:checked').val();
        
        if (activeTab === 'predefined-sizes' && selectedPredefinedSize && selectedPredefinedSize !== 'custom') {
            // If a predefined size is selected, update width/height but keep field enabled for form submission
            // Note: We don't disable custom_size_field to ensure all device size inputs are submitted
            
            let width, height;
            switch(selectedPredefinedSize) {
                case 'small':
                    width = '300px';
                    height = '300px';
                    break;
                case 'medium':
                    width = '500px';
                    height = '500px';
                    break;
                case 'large':
                    width = '800px';
                    height = '600px';
                    break;
                case 'full':
                    width = '98vw';
                    height = '98vh';
                    break;
            }
            
            if (width && height) {
                $('#viewer_width').val(width);
                $('#viewer_height').val(height);
            }
        }
        // Custom size field is always enabled to ensure all device sizes are submitted with the form
    }
    
    // Initialize on page load
    initializeDisplaySizeState();
});
