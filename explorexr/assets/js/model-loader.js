/**
 * ExploreXR Model Loader
 * 
 * Handles loading and displaying 3D models with basic functionality.
 */
(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initializeModelViewers();
        setupAnimationControls();
    });

    /**
     * Initialize all model viewer elements
     */
    function initializeModelViewers() {
        // Find all model viewers in the page
        const modelViewers = document.querySelectorAll('model-viewer.expoxr-model');
        
        modelViewers.forEach(function(modelViewer) {
            // Add event listeners for model-viewer events
            modelViewer.addEventListener('load', onModelLoad);
            modelViewer.addEventListener('error', onModelError);
            
            // Set up camera controls
            setupBasicCameraControls(modelViewer);
            
            // Apply model poster if it exists
            if (modelViewer.getAttribute('poster')) {
                setupModelPoster(modelViewer);
            }
        });
    }    /**
     * Set up basic camera controls for a model viewer
     */
    function setupBasicCameraControls(modelViewer) {
        // Don't automatically add camera-controls - only set defaults if already enabled
        if (modelViewer.hasAttribute('camera-controls')) {
            // Set default camera orbit if not specified
            if (!modelViewer.hasAttribute('camera-orbit')) {
                modelViewer.setAttribute('camera-orbit', '0deg 75deg 2m');
            }
        }
        
        // Set default field of view if not specified
        if (!modelViewer.hasAttribute('field-of-view')) {
            modelViewer.setAttribute('field-of-view', '30deg');
        }
    }

    /**
     * Handle model load event
     */
    function onModelLoad(event) {
        const modelViewer = event.target;
        
        // Get model info once loaded
        const modelUrl = modelViewer.getAttribute('src');
        
        // Only log if debug mode is enabled
        if (window.exploreXRDebug && window.exploreXRDebug.enabled) {
            console.log('Model loaded:', modelUrl);
        }
        
        // Check if this model has available animations
        checkAndSetupAnimations(modelViewer);
    }    /**
     * Handle model error event
     */
    function onModelError(event) {
        const modelViewer = event.target;
        
        // Check if error notifications should be shown
        if (typeof expoXRNotifications !== 'undefined' && !expoXRNotifications.show_error_notifications) {
            console.warn('Model display issue occurred but error notifications are disabled');
            return;
        }
        
        // Check if the error is a 404 (file not found)
        let errorMessage = 'Unable to display 3D model';
        let troubleshooting = '';
        
        // Log detailed error information
        console.warn('Model display issue:', event);
        
        // If we have source error details, use them
        if (event.detail && event.detail.sourceError) {
            // For debugging, log the specific error
            console.warn('Source error:', event.detail.sourceError);
            
            // If it's a network error (like 404), show a more specific message
            if (event.detail.sourceError instanceof TypeError || 
                (event.detail.sourceError.message && 
                 event.detail.sourceError.message.includes('Failed to fetch'))) {
                errorMessage = 'Model file not accessible';
                troubleshooting = 'Please check the file path or contact support if the issue persists.';
            } else if (event.detail.sourceError.message && 
                       event.detail.sourceError.message.includes('Invalid')) {
                errorMessage = 'Unsupported model format';
                troubleshooting = 'Please use GLB or GLTF format files.';
            }
        }
        
        // Create a more user-friendly error display
        const errorContainer = document.createElement('div');
        errorContainer.className = 'expoxr-model-error';
        errorContainer.style.cssText = `
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            border: 2px dashed #ddd;
            border-radius: 8px;
            color: #666;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        `;
        
        errorContainer.innerHTML = `
            <div class="expoxr-model-error-icon" style="font-size: 48px; margin-bottom: 10px;">📦</div>
            <div class="expoxr-model-error-title" style="font-size: 18px; font-weight: 600; margin-bottom: 8px; color: #333;">${errorMessage}</div>
            ${troubleshooting ? `<div class="expoxr-model-error-details" style="font-size: 14px; margin-bottom: 15px;">${troubleshooting}</div>` : ''}
            <button class="expoxr-retry-load" style="
                background: #0073aa;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                margin-right: 10px;
                font-size: 14px;
            ">Try Again</button>
            <button class="expoxr-hide-error" style="
                background: #666;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
            ">Hide Error</button>
        `;
        
        // Add retry functionality
        const retryButton = errorContainer.querySelector('.expoxr-retry-load');
        retryButton.addEventListener('click', function() {
            const src = modelViewer.getAttribute('src');
            if (src) {
                // Force reload by adding a timestamp
                const separator = src.includes('?') ? '&' : '?';
                modelViewer.setAttribute('src', src + separator + 't=' + Date.now());
            }
        });
        
        // Add hide functionality
        const hideButton = errorContainer.querySelector('.expoxr-hide-error');
        hideButton.addEventListener('click', function() {
            errorContainer.style.display = 'none';
        });
        
        // Replace model viewer with error message
        const parent = modelViewer.parentNode;
        parent.insertBefore(errorContainer, modelViewer);
        parent.removeChild(modelViewer);
    }

    /**
     * Setup model poster (thumbnail) for the model
     */
    function setupModelPoster(modelViewer) {
        const poster = modelViewer.getAttribute('poster');
        
        
       
    }

    /**
     * Helper function to show the model and hide the poster
     */
    function showModel(modelViewer, posterContainer) {
        // Make sure the model viewer is visible
        modelViewer.style.display = 'block';
        
        // Hide the poster container
        posterContainer.style.opacity = '0';
        
        // After the fade-out animation, completely hide the container
        setTimeout(() => {
            posterContainer.style.display = 'none';
        }, 300);
        
        // Force a redraw of the model viewer
        const currentOrbit = modelViewer.getAttribute('camera-orbit');
        modelViewer.setAttribute('camera-orbit', currentOrbit);
    }    /**
     * Check if the model has animations and set up controls
     */
    function checkAndSetupAnimations(modelViewer) {
        // Wait until animations are available
        if (!modelViewer.availableAnimations) {
            setTimeout(() => checkAndSetupAnimations(modelViewer), 100);
            return;
        }
        
        // Check if the model has animations
        if (modelViewer.availableAnimations && modelViewer.availableAnimations.length > 0) {
            // Log animation availability for debugging (only if debug mode is enabled)
            if (window.exploreXRDebug && window.exploreXRDebug.enabled) {
                console.log('Model has animations:', modelViewer.availableAnimations);
            }
            
            // Note: Animation controls are available in ExploreXR Premium
            // The free version provides basic model viewing only
            
            // Trigger a custom event for premium features
            const event = new CustomEvent('expoxr-animations-detected', {
                detail: {
                    modelViewer: modelViewer,
                    animations: modelViewer.availableAnimations,
                    premiumRequired: true
                }
            });
            document.dispatchEvent(event);
        }
    }    /**
     * Create animation controls for a model
     * NOTE: This function is deprecated in the free version.
     * Animation controls are available in ExploreXR Premium.
     */
    /*
    function createAnimationControls(modelViewer, animationName) {
        // This function requires ExploreXR Premium
        // Animation controls are a premium feature
        console.warn('createAnimationControls requires ExploreXR Premium. Upgrade to access animation controls.');
    }
    */

    /**
     * Format animation name for display
     */
    function formatAnimationName(name) {
        // Remove common prefixes and suffixes
        let formattedName = name.replace(/^Animation_|^anim_|_anim$|\.anim$/, '');
        
        // Replace underscores with spaces
        formattedName = formattedName.replace(/_/g, ' ');
        
        // Capitalize first letter of each word
        formattedName = formattedName.replace(/\b\w/g, c => c.toUpperCase());
        
        return formattedName;
    }

    /**
     * Set up animation controls for all model viewers
     */
    function setupAnimationControls() {
        // Listen for animation button clicks
        $(document).on('click', '.expoxr-animation-button', function() {
            // This is handled in the createAnimationControls function
        });
    }

})(jQuery);