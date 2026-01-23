/**
 * ExploreXR Admin - Create Model Page
 * Handles tab functionality and media uploader for model creation
 */
jQuery(document).ready(function($) {
    const percentRuleError = 'Width and height cannot both be percentages. Please use px, vw, or vh for one dimension.';
    
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
    
    // Device tab functionality for responsive sizes
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
    
    // Enhanced file input functionality
    $('.explorexr-styled-file-input').on('change', function() {
        const $wrapper = $(this).closest('.explorexr-file-input-wrapper');
        const $textElement = $wrapper.find('.explorexr-file-input-text');
        
        if (this.files.length > 0) {
            const fileName = this.files[0].name;
            $textElement.text(fileName);
            $wrapper.addClass('file-selected');
        } else {
            $textElement.text('Choose a file or drag it here');
            $wrapper.removeClass('file-selected');
        }
    });
    
    // File drag and drop behavior
    const $fileInput = $('.explorexr-styled-file-input');
    const $dropArea = $('.explorexr-file-input-decoration');
    
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        $dropArea.on(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
    });
    
    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        $dropArea.on(eventName, function() {
            $(this).css({
                'border-color': '#2271b1',
                'background-color': '#f0f7fc'
            });
        });
    });
    
    // Remove highlight when item is dragged out or dropped
    ['dragleave', 'drop'].forEach(eventName => {
        $dropArea.on(eventName, function() {
            $(this).css({
                'border-color': '',
                'background-color': ''
            });
        });
    });
    
    // Handle dropped files
    $dropArea.on('drop', function(e) {
        const dt = e.originalEvent.dataTransfer;
        const files = dt.files;
        const $wrapper = $(this).closest('.explorexr-file-input-wrapper');
        const $input = $wrapper.find('input[type="file"]');
        const $textElement = $wrapper.find('.explorexr-file-input-text');
        
        $input[0].files = files;
        
        if (files.length > 0) {
            const fileName = files[0].name;
            $textElement.text(fileName);
            $wrapper.addClass('file-selected');
        }
    });
    
    // Initialize the WordPress Media Uploader for the poster image
    var mediaUploader;
    
    $('#explorexr-select-poster').on('click', function(e) {
        e.preventDefault();
        
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
            multiple: false  // Set to false for single image selection
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
    
    /**
     * Block submission when any breakpoint uses %/% which would hide the model.
     */
    function violatesPercentRule(width, height) {
        const isPercent = (value) => typeof value === 'string' && value.trim().endsWith('%');
        return isPercent(width) && isPercent(height);
    }
    
    $('form').on('submit', function(e) {
        const desktopWidth = $('#viewer_width').val();
        const desktopHeight = $('#viewer_height').val();
        const tabletWidth = $('#tablet_viewer_width').val();
        const tabletHeight = $('#tablet_viewer_height').val();
        const mobileWidth = $('#mobile_viewer_width').val();
        const mobileHeight = $('#mobile_viewer_height').val();
        
        if (
            violatesPercentRule(desktopWidth, desktopHeight) ||
            violatesPercentRule(tabletWidth, tabletHeight) ||
            violatesPercentRule(mobileWidth, mobileHeight)
        ) {
            e.preventDefault();
            alert(percentRuleError);
            return false;
        }
        
        return true;
    });
});
