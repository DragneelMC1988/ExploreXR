/**
 * ExpoXR Admin UI JavaScript
 * 
 * Handles modal functionality for 3D model previews and shortcode copying
 * in the admin dashboard.
 */

jQuery(document).ready(function($) {
    'use strict';    // Include the model-viewer script if it hasn't been included already
    // Use centralized loader if available
    if (window.loadModelViewer && !window.isModelViewerLoaded()) {
        window.loadModelViewer({
            scriptUrl: 'https://unpkg.com/@google/model-viewer/dist/model-viewer-umd.js',
            scriptType: 'umd'
        }).then(function() {
            console.log('Model Viewer loaded successfully via centralized loader');
        }).catch(function(error) {
            console.warn('ExploreXR Admin: Model viewer could not be loaded via centralized loader, trying fallback method.');
            // Fallback to direct loading
            loadModelViewerDirect();
        });
    } else if (!window.customElements || !window.customElements.get('model-viewer')) {
        loadModelViewerDirect();
    }
    
    function loadModelViewerDirect() {
        var script = document.createElement('script');
        
        // Try to use UMD version for better compatibility
        var scriptUrl = 'https://unpkg.com/@google/model-viewer/dist/model-viewer-umd.js';
        
        // If UMD loading fails, fallback to module version
        script.onload = function() {
            console.log('Model Viewer loaded successfully (UMD)');
        };
        
        script.onerror = function() {
            console.warn('UMD version failed, loading module version...');
            var moduleScript = document.createElement('script');
            moduleScript.type = 'module';
            moduleScript.src = 'https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js';
            document.head.appendChild(moduleScript);
        };
        
        script.src = scriptUrl;
        document.head.appendChild(script);
    }
    
    // Model viewer modal functionality
    const modal = $('#expoxr-model-modal');
    const modelViewer = $('#expoxr-model-viewer');
    const modelTitle = $('#expoxr-model-title');
    
    // Open modal when clicking View Model
    $('.view-3d-model').on('click', function(e) {
        e.preventDefault();
        const modelUrl = $(this).data('model-url');
        const modelName = $(this).data('model-name');
        const posterUrl = $(this).data('poster-url');
        
        // Update model viewer source and title
        modelViewer.attr('src', modelUrl);
        modelTitle.text(expoxrAdminUI.strings.modelPreviewTitle + ': ' + modelName);
        
        // Add poster if available
        if (posterUrl) {
            modelViewer.attr('poster', posterUrl);
        } else {
            modelViewer.removeAttr('poster');
        }
        
        // Show modal
        modal.css('display', 'block');
    });
    
    // Close modal
    $('.expoxr-model-close').on('click', function() {
        modal.css('display', 'none');
        modelViewer.attr('src', '');
        modelViewer.removeAttr('poster');
    });
    
    // Close modal when clicking outside of the content
    $(window).on('click', function(e) {
        if (e.target === modal[0]) {
            modal.css('display', 'none');
            modelViewer.attr('src', '');
            modelViewer.removeAttr('poster');
        }
    });
    
    // Copy shortcode functionality
    $('.copy-shortcode').on('click', function() {
        const shortcode = $(this).data('shortcode');
        
        // Try modern clipboard API first
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(shortcode).then(function() {
                showCopySuccess($(this));
            }).catch(function() {
                fallbackCopyTextToClipboard(shortcode, $(this));
            });
        } else {
            fallbackCopyTextToClipboard(shortcode, $(this));
        }
    });
    
    /**
     * Show copy success feedback
     */
    function showCopySuccess($element) {
        const originalIcon = $element.html();
        $element.html('<span class="dashicons dashicons-yes success-icon"></span>');
        
        setTimeout(function() {
            $element.html(originalIcon);
        }, 1500);
    }
    
    /**
     * Fallback copy method for older browsers
     */
    function fallbackCopyTextToClipboard(text, $element) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        
        // Avoid scrolling to bottom
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess($element);
            }
        } catch (err) {
            console.error('Fallback: Could not copy text: ', err);
        }
        
        document.body.removeChild(textArea);
    }
    
    /**
     * Handle escape key to close modal
     */
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && modal.is(':visible')) {
            modal.css('display', 'none');
            modelViewer.attr('src', '');
            modelViewer.removeAttr('poster');
        }
    });
});
