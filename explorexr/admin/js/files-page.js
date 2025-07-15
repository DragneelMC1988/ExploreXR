/**
 * ExpoXR Admin - Files Page
 * Handles file interactions and previews in the files admin page
 */
jQuery(document).ready(function($) {
    // Delete file confirmation
    $('.delete-file').on('click', function(e) {
        const fileName = $(this).data('filename');
        if (!confirm('Are you sure you want to delete the file "' + fileName + '"? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
    
    // Show/hide upload form
    $('#show-upload-form, #show-upload-form-footer').on('click', function() {
        $('#upload-form-card').show();
    });
    
    $('#cancel-upload').on('click', function() {
        $('#upload-form-card').hide();
    });
    
    // Model viewer modal functionality
    const modal = $('#expoxr-model-modal');
    const modelViewer = $('#expoxr-model-viewer');
    const modelTitle = $('#expoxr-model-title');
    
    // Open modal when clicking View Model
    $('.view-3d-model').on('click', function(e) {
        e.preventDefault();
        const modelUrl = $(this).data('model-url');
        const modelName = $(this).data('model-name');
        
        // Update model viewer source and title
        modelViewer.attr('src', modelUrl);
        modelTitle.text('3D Model Preview: ' + modelName);
        
        // Show modal
        modal.css('display', 'block');
    });
    
    // Close modal
    $('.expoxr-model-close').on('click', function() {
        modal.css('display', 'none');
        modelViewer.attr('src', '');
    });
    
    // Close modal when clicking outside of the content
    $(window).on('click', function(e) {
        if (e.target === modal[0]) {
            modal.css('display', 'none');
            modelViewer.attr('src', '');
        }
    });
});