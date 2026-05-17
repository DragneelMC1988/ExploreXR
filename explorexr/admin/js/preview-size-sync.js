/**
 * ExploreXR Preview Size Sync
 * 
 * Synchronizes display size changes to the admin preview in real-time.
 * Updates preview container when predefined sizes are selected or custom values change.
 * 
 * @package ExploreXR_Premium
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Get preview container and model-viewer
        const $previewContainer = $('#explorexr-model-preview-container');
        const $previewModelViewer = $('#main-preview-model-viewer');
        
        if (!$previewContainer.length || !$previewModelViewer.length) {
            return; // Not on edit page or preview not present
        }
        
        /**
         * Update preview size based on current form values
         */
        function updatePreviewSize() {
            // Read from the single authoritative hidden field
            const viewerSize = $('#explorexr_viewer_size_field').val() || 'custom';
            let width = $('#viewer_width').val() || '100vw';
            let height = $('#viewer_height').val() || '500px';

            // Get values based on selected mode
            if (viewerSize && viewerSize !== 'custom') {
                // Use predefined size values
                switch(viewerSize) {
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
                        width = '100vw';
                        height = '90vh';
                        break;
                }
            }
            
            // Apply size to preview container
            $previewContainer.css({
                'width': width,
                'height': height,
                'max-width': '100%',
                'margin': '0 auto',
                'transition': 'all 0.3s ease'
            });
            
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('Preview size updated to ' + width + ' x ' + height, 'info');
            }
        }
        
        /**
         * Debounce helper to avoid excessive updates
         */
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        // Listen for size changes from predefined sizes
        $('input[name="explorexr_predefined_size"]').on('change', function() {
            updatePreviewSize();
            
            // Show visual feedback
            $previewContainer.css('opacity', '0.6');
            setTimeout(function() {
                $previewContainer.css('opacity', '1');
            }, 300);
        });
        
        // Listen for custom size input changes (debounced)
        $('#viewer_width, #viewer_height').on('input', debounce(updatePreviewSize, 500));
        
        // Listen for tab switches to ensure correct size is shown
        $('.explorexr-tab[data-tab]').on('click', function() {
            setTimeout(updatePreviewSize, 100);
        });
        
        // Initial size application on page load
        updatePreviewSize();
    });
    
})(jQuery);
