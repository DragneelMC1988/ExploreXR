/**
 * ExpoXR Model Size Metabox JavaScript
 *
 * Handles tab functionality and media uploader for the model size metabox
 */

jQuery(document).ready(function($) {
    'use strict';
    
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
        if (tabId === 'custom-sizes') {
            $('#custom_size_field').val('custom');
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
    $('.expoxr-tab[data-tab="custom-sizes"]').on('click', function() {
        $('#custom_size_field').prop('disabled', false);
    });
    
    // Initialize the WordPress Media Uploader for the poster image
    $('#expoxr-select-poster').on('click', function(e) {
        e.preventDefault();
        
        const frame = wp.media({
            title: 'Select Model Poster Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });
        
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            
            $('#model_poster_id').val(attachment.id);
            $('#model_poster_url').val(attachment.url);
            
            const previewElement = $('#expoxr-poster-preview');
            previewElement.show().find('img').attr('src', attachment.url);
        });
        
        frame.open();
    });
    
    // Initialize the display size form state on page load
    function initializeDisplaySizeState() {
        const activeTab = $('.expoxr-tab.active[data-tab]').data('tab');
        const selectedPredefinedSize = $('input[name="viewer_size"]:checked').val();
        
        if (activeTab === 'predefined-sizes' && selectedPredefinedSize && selectedPredefinedSize !== 'custom') {
            // If a predefined size is selected, disable the custom size field and update width/height
            $('#custom_size_field').prop('disabled', true);
            
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
        } else {
            // Custom size is active, enable the custom size field
            $('#custom_size_field').prop('disabled', false);
        }
    }
    
    // Initialize on page load
    initializeDisplaySizeState();
});
