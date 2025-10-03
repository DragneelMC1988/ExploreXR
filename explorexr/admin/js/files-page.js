/**
 * ExploreXR Admin - Files Page
 * Handles file interactions and previews in the files admin page
 */
jQuery(document).ready(function($) {
    console.log('ExploreXR Files Page JS loaded successfully'); // Debug log
    
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
    const modal = $('#explorexr-model-modal');
    const modelTitle = $('#explorexr-model-title');
    
    // Open modal when clicking View Model (using event delegation)
    $(document).on('click', '.view-3d-model', function(e) {
        e.preventDefault();
        console.log('Files page: View 3D Model button clicked!'); // Debug log
        
        const modelUrl = $(this).data('model-url');
        const modelName = $(this).data('model-name');
        
        console.log('Files page model data:', { modelUrl, modelName }); // Debug log
        
        // Function to set up model viewer once it's available
        function setupModelViewer() {
            const container = document.getElementById('explorexr-model-viewer-container');
            
            // Wait for the container to exist (created by modal template)
            if (!container) {
                setTimeout(setupModelViewer, 50);
                return;
            }
            
            // Create the model-viewer element if it doesn't exist
            let modelViewer = $('#explorexr-model-viewer');
            if (modelViewer.length === 0) {
                console.log('Files page: Creating model-viewer element');
                
                // Check if model-viewer custom element is defined
                if (typeof customElements !== 'undefined' && !customElements.get('model-viewer')) {
                    console.log('model-viewer custom element not yet defined, waiting...');
                    setTimeout(setupModelViewer, 200);
                    return;
                }
                
                container.innerHTML = '<model-viewer id="explorexr-model-viewer" camera-controls auto-rotate loading="eager" reveal="interaction"></model-viewer>';
                modelViewer = $('#explorexr-model-viewer');
                
                // Wait a moment for the element to be fully created
                setTimeout(function() {
                    continueSetup();
                }, 100);
                return;
            } else {
                continueSetup();
            }
            
            function continueSetup() {
                console.log('Setting up files page model viewer with URL:', modelUrl);
                
                // Update model viewer source and title
                modelViewer.attr('src', modelUrl);
                modelTitle.text('3D Model Preview: ' + modelName);
                
                // Show modal
                modal.css('display', 'block');
            }
        }
        
        // Start the setup process
        setupModelViewer();
    });
    
    // Close modal
    $('.explorexr-model-close').on('click', function() {
        modal.css('display', 'none');
        const modelViewer = $('#explorexr-model-viewer');
        if (modelViewer.length > 0) {
            modelViewer.attr('src', '');
        }
    });
    
    // Close modal when clicking outside of the content
    $(window).on('click', function(e) {
        if (e.target === modal[0]) {
            modal.css('display', 'none');
            const modelViewer = $('#explorexr-model-viewer');
            if (modelViewer.length > 0) {
                modelViewer.attr('src', '');
            }
        }
    });
});