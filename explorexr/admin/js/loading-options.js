/**
 * ExploreXR Loading Options JavaScript
 * 
 * Handles the loading UI for 3D models.
 */
(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initLoadingOptions();
        initAdminPreview();
    });

    /**
     * Initialize loading options in admin preview
     */
    function initAdminPreview() {
        // Update preview when loading type changes
        $('input[name="expoxr_loading_type"]').on('change', function() {
            const loadingType = $(this).val();
            const previewContainer = $('.expoxr-loading-preview');
            
            previewContainer.attr('data-loading-type', loadingType);
            
            // Show/hide elements based on loading type
            if (loadingType === 'bar') {
                previewContainer.find('.expoxr-loading-progress-bar').show();
                previewContainer.find('.expoxr-loading-percentage').hide();
            } else if (loadingType === 'percentage') {
                previewContainer.find('.expoxr-loading-progress-bar').hide();
                previewContainer.find('.expoxr-loading-percentage').show();
            } else {
                previewContainer.find('.expoxr-loading-progress-bar').show();
                previewContainer.find('.expoxr-loading-percentage').show();
            }
        });
    }

    /**
     * Initialize loading options for model viewers
     */
    function initLoadingOptions() {
        // Find all model viewers in the page
        const modelViewers = document.querySelectorAll('model-viewer.expoxr-model');
        
        modelViewers.forEach(function(modelViewer) {
            // Get loading options from data attributes
            const loadingType = modelViewer.dataset.loadingType || 'both';
            const loadingColor = modelViewer.dataset.loadingColor || '#1e88e5';
            
            // Create loading container
            const loadingContainer = document.createElement('div');
            loadingContainer.className = 'expoxr-loading-container';
            
            // Create progress bar
            if (loadingType === 'bar' || loadingType === 'both') {
                const progressBar = document.createElement('div');
                progressBar.className = 'expoxr-loading-progress-bar';
                
                const progressIndicator = document.createElement('div');
                progressIndicator.className = 'expoxr-loading-progress';
                progressIndicator.style.backgroundColor = loadingColor;
                
                progressBar.appendChild(progressIndicator);
                loadingContainer.appendChild(progressBar);
            }
            
            // Create percentage indicator
            if (loadingType === 'percentage' || loadingType === 'both') {
                const percentageIndicator = document.createElement('div');
                percentageIndicator.className = 'expoxr-loading-percentage';
                percentageIndicator.textContent = '0%';
                percentageIndicator.style.color = loadingColor;
                
                loadingContainer.appendChild(percentageIndicator);
            }
            
            // Insert loading container before model viewer
            modelViewer.parentNode.insertBefore(loadingContainer, modelViewer);
            
            // Track loading progress
            modelViewer.addEventListener('progress', function(event) {
                const progress = event.detail.totalProgress * 100;
                const progressPercent = Math.floor(progress);
                
                // Update progress bar if it exists
                const progressBar = loadingContainer.querySelector('.expoxr-loading-progress');
                if (progressBar) {
                    progressBar.style.width = progressPercent + '%';
                }
                
                // Update percentage if it exists
                const percentageIndicator = loadingContainer.querySelector('.expoxr-loading-percentage');
                if (percentageIndicator) {
                    percentageIndicator.textContent = progressPercent + '%';
                }
                
                // Hide loading container when loaded
                if (progressPercent >= 100) {
                    setTimeout(function() {
                        loadingContainer.style.opacity = '0';
                        setTimeout(function() {
                            loadingContainer.remove();
                        }, 500);
                    }, 500);
                }
            });
        });
    }

})(jQuery);