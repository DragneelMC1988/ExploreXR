/**
 * ExploreXR Admin - Browse Models Page
 * Handles search, sorting, and model interactions on the browse models page
 */
jQuery(document).ready(function($) {
    if (typeof ExploreXRLogger !== 'undefined') {
        ExploreXRLogger.log('Browse Models JS loaded');
    }
    
    // Delete model functionality
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
        const $modelCard = $(this).closest('.explorexr-model-card');
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
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('Delete request:', {
                        model_id: modelId,
                        security_present: !!explorexr_admin.nonce,
                        ajax_url: explorexr_admin.ajax_url
                    });
                }
            }
            
            // Send delete request
            $.ajax({
                url: explorexr_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'explorexr_delete_model',
                    model_id: modelId,
                    security: explorexr_admin.nonce
                },                success: function(response) {
                    if (response.success) {
                        // Show success notification
                        const notification = $('<div id="explorexr-deleted-notification" style="position: fixed; bottom: 20px; right: 20px; background-color: #2271b1; color: white; padding: 10px 20px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); z-index: 9999;">' +
                            '<p style="margin: 0;"><span class="dashicons dashicons-yes" style="margin-right: 8px;"></span> ' + response.data.message + '</p>' +
                        '</div>').appendTo('body').hide().fadeIn(300);
                        
                        // Remove the model card with animation
                        $modelCard.fadeOut(300, function() {
                            $(this).remove();
                            
                            // Check if there are no more models
                            if ($('.explorexr-model-card').length === 0) {
                                // Replace grid with empty message
                                $('.explorexr-models-grid').replaceWith(
                                    '<div class="explorexr-alert info">' +
                                    '<span class="dashicons dashicons-info"></span>' +
                                    '<div>' +
                                    '<p>You don\'t have any 3D models yet. <a href="' + explorexr_admin.create_model_url + '">Create your first 3D model</a>.</p>' +
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
                            if (typeof ExploreXRLogger !== 'undefined') {
                                ExploreXRLogger.error('Delete model error:', response);
                            }
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
                        if (typeof ExploreXRLogger !== 'undefined') {
                            ExploreXRLogger.error('AJAX error:', {xhr, status, error});
                        }
                    }
                    
                    alert('Error: ' + errorMsg);
                    
                    // Reset button
                    $modelCard.find('.delete-model').prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Delete');
                }
            });
        }
    });
    // Track active addon filters
    var activeAddonFilters = [];

    /**
     * Apply all active filters (search + addon) to model cards.
     * A card is shown only if it matches the search term AND all active addon filters.
     */
    function applyFilters() {
        var searchTerm = ($('#model-search').val() || '').toLowerCase().trim();
        
        $('.explorexr-model-card').each(function() {
            var $card = $(this);
            var title = ($card.data('title') || '').toString().toLowerCase();
            var modelId = ($card.data('model-id') || '').toString();
            var cardAddons = ($card.data('addons') || '').toString().toLowerCase();

            // Search match: by title or numeric ID
            var matchesSearch = true;
            if (searchTerm) {
                var titleMatch = title.indexOf(searchTerm) !== -1;
                var idMatch = modelId === searchTerm;
                matchesSearch = titleMatch || idMatch;
            }

            // Addon filter match: card must have ALL active addon filters
            var matchesAddons = true;
            if (activeAddonFilters.length > 0) {
                var cardAddonList = cardAddons ? cardAddons.split(',') : [];
                for (var i = 0; i < activeAddonFilters.length; i++) {
                    if (cardAddonList.indexOf(activeAddonFilters[i]) === -1) {
                        matchesAddons = false;
                        break;
                    }
                }
            }

            if (matchesSearch && matchesAddons) {
                $card.show();
            } else {
                $card.hide();
            }
        });
    }

    // Model search functionality (title + ID)
    $('#model-search').on('input', function() {
        applyFilters();
    });

    // Addon filter toggle buttons
    $(document).on('click', '.explorexr-addon-filter-btn', function() {
        var addon = $(this).data('addon');
        var idx = activeAddonFilters.indexOf(addon);
        if (idx === -1) {
            activeAddonFilters.push(addon);
            $(this).addClass('active');
        } else {
            activeAddonFilters.splice(idx, 1);
            $(this).removeClass('active');
        }
        applyFilters();
    });
    
    // Sorting functionality
    $('#sort-models').on('change', function() {
        const sortValue = $(this).val();
        const $modelsGrid = $('.explorexr-models-grid');
        const $models = $('.explorexr-model-card').toArray();
        
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
            const notification = $('#explorexr-copied-notification');
            notification.fadeIn(300);
            
            // Hide notification after 2 seconds
            setTimeout(function() {
                notification.fadeOut(300);
            }, 2000);
        });
    });
      // 3D Model viewer modal functionality (using event delegation for dynamic content)
    $(document).on('click', '.view-3d-model', function(e) {
        e.preventDefault();
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('View 3D Model button clicked');
        }
        
        const modelId = $(this).data('model-id');
        const modelName = $(this).data('model-name');
        
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('Model ID:', modelId, 'Name:', modelName);
        }
        
        if (!modelId) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.error('No model ID found');
            }
            alert('Error: No model ID found');
            return;
        }
        
        // Show modal immediately with loading state
        const modal = $('#explorexr-model-modal');
        const container = $('#explorexr-model-viewer-container');
        const modalTitle = $('#explorexr-model-title');
        
        modalTitle.text('Loading: ' + modelName);
        container.html('<div style="display: flex; align-items: center; justify-content: center; height: 500px;"><p>Loading 3D model with all addon settings...</p></div>');
        modal.css('display', 'block');
        
        // Fetch rendered model HTML from server with all addon filters applied
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'explorexr_render_model_preview',
                model_id: modelId,
                nonce: $('#explorexr-ajax-nonce').val()
            },
            success: function(response) {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('AJAX response:', response);
                }
                
                if (response.success && response.data.html) {
                    // Insert the fully-rendered model-viewer HTML
                    container.html(response.data.html);
                    modalTitle.text('3D Model Preview: ' + response.data.title);
                } else {
                    container.html('<div style="padding: 20px; color: red;">Error: ' + (response.data?.message || 'Failed to load model') + '</div>');
                }
            },
            error: function(xhr, status, error) {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.error('AJAX error:', status, error);
                }
                container.html('<div style="padding: 20px; color: red;">Error loading model: ' + error + '</div>');
            }
        });
    });
    
    // Close modal functionality
    $(document).on('click', '.explorexr-model-close', function() {
        $('#explorexr-model-modal').css('display', 'none');
    });
    
    // Close modal when clicking outside of modal content
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('explorexr-model-modal')) {
            $('#explorexr-model-modal').css('display', 'none');
        }
    });
});
