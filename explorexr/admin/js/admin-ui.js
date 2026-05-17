/**
 * ExploreXR Admin UI JavaScript
 * 
 * Handles modal functionality for 3D model previews and shortcode copying
 * in the admin dashboard.
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Model viewer is already loaded via wp_enqueue_script
    // No need to manually load it here
    
    // Model viewer modal functionality
    const modal = $('#ExploreXR-model-modal');
    const modelViewer = $('#ExploreXR-model-viewer');
    const modelTitle = $('#ExploreXR-model-title');
    
    // Open modal when clicking View Model
    $('.view-3d-model').on('click', function(e) {
        e.preventDefault();
        const modelUrl = $(this).data('model-url');
        const modelName = $(this).data('model-name');
        const posterUrl = $(this).data('poster-url');
        
        // Update model viewer source and title
        modelViewer.attr('src', modelUrl);
        modelTitle.text(ExploreXRAdminUI.strings.modelPreviewTitle + ': ' + modelName);
        
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
    $('.ExploreXR-model-close').on('click', function() {
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
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.error('Fallback: Could not copy text: ', err);
            }
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
