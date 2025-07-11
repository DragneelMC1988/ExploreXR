/**
 * ExpoXR Model Size Handler
 *
 * Handles the model size options and poster selection in the model size metabox
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Function to toggle size fields based on selection
        window.toggleSizeFields = function(type) {
            const customSizeRow = document.getElementById('custom_size_row');
            
            if (type === 'custom') {
                customSizeRow.style.display = 'block';
            } else {
                customSizeRow.style.display = 'none';
            }
        };
        
        // Toggle between upload and media library options for poster
        $('input[name="poster_method"]').on('change', function() {
            const method = $(this).val();
            
            if (method === 'upload') {
                $('#expoxr-poster-upload').show();
                $('#expoxr-poster-library').hide();
            } else {
                $('#expoxr-poster-upload').hide();
                $('#expoxr-poster-library').show();
            }
        });
        
        // Media Library selection for poster
        $('#expoxr-select-poster').on('click', function(e) {
            e.preventDefault();
            
            // If the wp.media API is available
            if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
                const frame = wp.media({
                    title: 'Select or Upload Poster Image',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                frame.on('select', function() {
                    const attachment = frame.state().get('selection').first().toJSON();
                    $('#model_poster_id').val(attachment.id);
                    $('#model_poster_url').val(attachment.url);
                    
                    // Update preview
                    $('#expoxr-poster-preview').show().find('img').attr('src', attachment.url);
                });
                
                frame.open();
            } else {
                alert('The WordPress Media Library is not available. Please ensure you are using this in the WordPress admin area.');
            }
        });
        
        // Size input validation
        $('.small-text[name$="_width"], .small-text[name$="_height"]').on('blur', function() {
            const value = $(this).val();
            
            // Check if value contains valid CSS units
            if (value && !/^(auto|[0-9]+(%|px|em|rem|vh|vw|vmin|vmax)?)$/.test(value)) {
                alert('Please enter a valid size value with units (e.g., 100%, 500px)');
                $(this).val('').focus();
            }
        });
        
        // Validate poster image file on upload
        $('#model_poster').on('change', function() {
            const file = this.files[0];
            if (file) {
                // Check file type
                const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validImageTypes.includes(file.type)) {
                    alert('Please upload a valid image file (JPG, PNG, GIF, WEBP)');
                    this.value = '';
                    return false;
                }
                
                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image file is too large. Maximum size is 5MB.');
                    this.value = '';
                    return false;
                }
                
                // Preview the poster image
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = new Image();
                    img.src = e.target.result;
                    img.style.maxWidth = '150px';
                    img.style.maxHeight = '150px';
                    img.style.border = '1px solid #ddd';
                    
                    // Create or update preview
                    const previewDiv = $('#expoxr-poster-upload-preview');
                    if (previewDiv.length === 0) {
                        $('<div id="expoxr-poster-upload-preview" style="margin-top: 10px;"></div>')
                            .append(img)
                            .appendTo('#expoxr-poster-upload');
                    } else {
                        previewDiv.html(img);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
})(jQuery);