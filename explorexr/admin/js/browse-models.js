/**
 * ExpoXR Admin - Browse Models Page
 * Handles search, sorting, and model interactions on the browse models page
 */
jQuery(document).ready(function($) {    // Delete model functionality
    $('.delete-model').on('click', function(e) {
        e.preventDefault();
        // Get model ID from data attribute, fallback to extracting from delete URL if needed
        let modelId = $(this).data('model-id');
        if (!modelId && $(this).data('delete-url')) {
            // Extract model ID from delete URL query parameter
            const urlParams = new URLSearchParams($(this).data('delete-url').split('?')[1]);
            modelId = urlParams.get('model_id');
        }
        const modelName = $(this).data('model-name');
        const $modelCard = $(this).closest('.expoxr-model-card');
          // Show confirmation dialog
        if (confirm(`Are you sure you want to delete "${modelName}"? This action cannot be undone.`)) {
            // Validate model ID is available
            if (!modelId) {
                alert('Error: Could not determine model ID. Please refresh the page and try again.');
                return;
            }
        
            // Show loading state
            $(this).prop('disabled', true).html('<span class="dashicons dashicons-update spinning"></span> Deleting...');
            
            // Log for debugging (only in development)
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                console.log('Delete request:', {
                    model_id: modelId,
                    security_present: !!expoxr_admin.nonce,
                    ajax_url: expoxr_admin.ajax_url
                });
            }
            
            // Send delete request
            $.ajax({
                url: expoxr_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'expoxr_delete_model',
                    model_id: modelId,
                    security: expoxr_admin.nonce
                },                success: function(response) {
                    if (response.success) {
                        // Show success notification
                        const notification = $('<div id="expoxr-deleted-notification" style="position: fixed; bottom: 20px; right: 20px; background-color: #2271b1; color: white; padding: 10px 20px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); z-index: 9999;">' +
                            '<p style="margin: 0;"><span class="dashicons dashicons-yes" style="margin-right: 8px;"></span> ' + response.data.message + '</p>' +
                        '</div>').appendTo('body').hide().fadeIn(300);
                        
                        // Remove the model card with animation
                        $modelCard.fadeOut(300, function() {
                            $(this).remove();
                            
                            // Check if there are no more models
                            if ($('.expoxr-model-card').length === 0) {
                                // Replace grid with empty message
                                $('.expoxr-models-grid').replaceWith(
                                    '<div class="expoxr-alert info">' +
                                    '<span class="dashicons dashicons-info"></span>' +
                                    '<div>' +
                                    '<p>You don\'t have any 3D models yet. <a href="' + expoxr_admin.create_model_url + '">Create your first 3D model</a>.</p>' +
                                    '</div>' +
                                    '</div>'
                                );
                            }
                        });
                        
                        // Hide notification after 2 seconds
                        setTimeout(function() {
                            notification.fadeOut(300, function() {
                                $(this).remove();
                            });
                        }, 2000);                    } else {
                        // Show error notification with detailed message
                        const errorMsg = response.data && response.data.message 
                            ? response.data.message 
                            : 'Could not delete model. Unknown error.';
                            
                        // Log detailed error info for debugging
                        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                            console.error('Delete model error:', response);
                        }
                        
                        alert('Error: ' + errorMsg);
                        
                        // Reset button
                        $modelCard.find('.delete-model').prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Delete');
                    }
                },
                error: function(xhr, status, error) {
                    // Show error notification with detailed message
                    const errorMsg = 'Could not connect to the server. ' + status + ': ' + error;
                    
                    // Log detailed error info for debugging
                    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                        console.error('AJAX error:', {xhr, status, error});
                    }
                    
                    alert('Error: ' + errorMsg);
                    
                    // Reset button
                    $modelCard.find('.delete-model').prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Delete');
                }
            });
        }
    });
    // Model search functionality
    $('#model-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        // Search through all model cards
        $('.expoxr-model-card').each(function() {
            const modelTitle = $(this).data('title').toLowerCase();
            
            // Show/hide based on search match
            if (modelTitle.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Sorting functionality
    $('#sort-models').on('change', function() {
        const sortValue = $(this).val();
        const $modelsGrid = $('.expoxr-models-grid');
        const $models = $('.expoxr-model-card').toArray();
        
        // Sort the models based on the selected option
        $models.sort(function(a, b) {
            const $a = $(a);
            const $b = $(b);
            
            if (sortValue === 'newest') {
                return new Date($b.data('date')) - new Date($a.data('date'));
            } else if (sortValue === 'oldest') {
                return new Date($a.data('date')) - new Date($b.data('date'));
            } else if (sortValue === 'title-az') {
                return $a.data('title').localeCompare($b.data('title'));
            } else if (sortValue === 'title-za') {
                return $b.data('title').localeCompare($a.data('title'));
            }
        });
        
        // Re-append the sorted models to the grid
        $modelsGrid.empty();
        $models.forEach(function(model) {
            $modelsGrid.append(model);
        });
    });
    
    // Copy shortcode functionality
    $('.copy-shortcode').on('click', function() {
        const shortcode = $(this).data('shortcode');
        
        // Use Clipboard API to copy text
        navigator.clipboard.writeText(shortcode).then(function() {
            // Show success notification
            const notification = $('#expoxr-copied-notification');
            notification.fadeIn(300);
            
            // Hide notification after 2 seconds
            setTimeout(function() {
                notification.fadeOut(300);
            }, 2000);
        });
    });
      // 3D Model viewer modal functionality
    $('.view-3d-model').on('click', function(e) {
        e.preventDefault();
        const modelUrl = $(this).data('model-url');
        const modelName = $(this).data('model-name');
        const posterUrl = $(this).data('poster-url');
        
        // Update model viewer source and title
        const modelViewer = $('#expoxr-model-viewer');
        const modalTitle = $('#expoxr-model-title');
        
        console.log('Loading 3D model from URL:', modelUrl);
        
        // Reset any previous error messages
        $('.error-details').text('');
        
        // Register error handler before setting source
        modelViewer[0].addEventListener('error', function(event) {
            console.error('Model viewer error:', event);
            $('.error-details').text('Error type: ' + (event.detail?.type || 'unknown') + 
                                     ' - Path: ' + modelUrl);
        });
        
        // Add event listener for when model is successfully loaded
        modelViewer[0].addEventListener('load', function() {
            console.log('Model loaded successfully');
        });
        
        // Set source and other attributes
        modelViewer.attr('src', modelUrl);
        modalTitle.text('3D Model Preview: ' + modelName);
        
        // Add poster if available
        if (posterUrl) {
            modelViewer.attr('poster', posterUrl);
        } else {
            modelViewer.removeAttr('poster');
        }
        
        // Show modal
        $('#expoxr-model-modal').css('display', 'block');
    });
    
    // Close modal when clicking on X
    $('.expoxr-model-close').on('click', function() {
        $('#expoxr-model-modal').css('display', 'none');
        $('#expoxr-model-viewer').attr('src', '');
        $('#expoxr-model-viewer').removeAttr('poster');
    });
    
    // Close modal when clicking outside of modal content
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('expoxr-model-modal')) {
            $('#expoxr-model-modal').css('display', 'none');
            $('#expoxr-model-viewer').attr('src', '');
            $('#expoxr-model-viewer').removeAttr('poster');
        }
    });
});