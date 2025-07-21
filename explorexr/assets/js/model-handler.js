/**
 * ExploreXR Model Handler
 * 
 * Handles model viewer initialization and integrates with debugging functionality
 */
(function() {
    'use strict';
    
    // De
    let debugMode = false;
    let debugAR = false;
    let debugCamera = false;
    let debugAnimations = false;
    let debugAnnotations = false;
    let debugLoading = false;
    
    // Initialize model viewers on page load
    document.addEventListener('DOMContentLoaded', function() {
        initExploreXR();
    });
    
    /**
     * Check if we're in WordPress admin area
     */
    function isWordPressAdmin() {
        return document.body.classList.contains('wp-admin') || 
               window.location.pathname.includes('/wp-admin/');
    }
    
    /**
     * Initialize ExploreXR functionality
     */
    function initExploreXR() {
        // Get debug settings from global variable set by WordPress
        if (typeof exploreXRDebug !== 'undefined') {
            debugMode = exploreXRDebug.enabled || false;
            debugAR = exploreXRDebug.ar || false;
            debugCamera = exploreXRDebug.camera || false;
            debugAnimations = exploreXRDebug.animations || false;
            debugAnnotations = exploreXRDebug.annotations || false;
            debugLoading = exploreXRDebug.loading || false;
        }
        
        // Log plugin initialization if debug mode is enabled
        if (debugMode && !isWordPressAdmin()) {
            console.log('ExploreXR Debug: Initializing plugin v' + (exploreXRDebug.version || 'unknown'));
        }
        
        // Get all model viewers on the page
        const modelViewers = document.querySelectorAll('model-viewer');
        
        if (debugMode && !isWordPressAdmin()) {
            console.log(`ExploreXR Debug: Found ${modelViewers.length} model viewer(s) on this page`);
        }
        
        // Set up each model viewer
        modelViewers.forEach(function(modelViewer, index) {
            setupModelViewer(modelViewer, index);
        });
    }
    
    /**
     * Set up a single model viewer with event listeners and debugging
     * 
     * @param {Element} modelViewer The model-viewer element
     * @param {number} index Index of the model viewer on the page
     */
    function setupModelViewer(modelViewer, index) {
        // Add ID if missing for easier debugging
        if (!modelViewer.id) {
            modelViewer.id = 'explorexr-model-' + index;
        }
        
        if (debugMode) {
            console.log(`ExploreXR Debug: Setting up model viewer #${index + 1} (ID: ${modelViewer.id})`);
            console.log(`ExploreXR Debug: Source: ${modelViewer.getAttribute('src')}`);
        }
        
        // Set up error handling
        setupErrorHandling(modelViewer, index);
        
        // Set up loading events logging
        if (debugLoading) {
            setupLoadingDebug(modelViewer, index);
        }
        
        // Set up AR debugging
        if (debugAR) {
            setupARDebug(modelViewer, index);
        }
        
        // Set up camera control debugging
        if (debugCamera) {
            setupCameraDebug(modelViewer, index);
        }
        
        // Set up animation debugging
        if (debugAnimations) {
            setupAnimationDebug(modelViewer, index);
        }
        
        // Set up annotation debugging
        if (debugAnnotations) {
            setupAnnotationDebug(modelViewer, index);
        }
        
        // Log success after setup
        if (debugMode) {
            console.log(`ExploreXR Debug: Model viewer #${index + 1} setup complete`);
        }
    }
    
    /**
     * Set up error handling for a model viewer
     * 
     * @param {Element} modelViewer The model-viewer element
     * @param {number} index Index of the model viewer on the page
     */
    function setupErrorHandling(modelViewer, index) {
        // Add error event listener
        modelViewer.addEventListener('error', function(event) {
            const errorType = event.detail.type || 'unknown';
            const errorDetails = event.detail.sourceError ? event.detail.sourceError.message : 'Unknown error';
            
            console.warn(`ExploreXR Info: Model #${index + 1} (${modelViewer.id}) encountered an issue:`, errorType, errorDetails);
            
            // Use the error handler from model-viewer-error-handler.js if available
            if (typeof getUserFriendlyModelError === 'function') {
                const friendlyMessage = getUserFriendlyModelError(errorType, errorDetails);
                console.error(`ExploreXR Error: ${friendlyMessage}`);
                
                // Show error message to user (if not in a container that handles it already)
                if (!modelViewer.closest('.explorexr-model-viewer-container')) {
                    const errorElement = document.createElement('div');
                    errorElement.className = 'explorexr-model-error';
                    errorElement.innerHTML = `<p>${friendlyMessage}</p>`;
                    errorElement.style.cssText = 'color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 12px; margin-top: 10px; border-radius: 4px;';
                    modelViewer.parentNode.insertBefore(errorElement, modelViewer.nextSibling);
                }
            }
            
            // Check WebGL if loading failure
            if (errorType === 'loadfailure') {
                if (typeof checkWebGLSupport === 'function') {
                    const webglStatus = checkWebGLSupport();
                    console.info(`ExploreXR Debug: WebGL Status - ${webglStatus}`);
                }
            }
        });
    }
    
    /**
     * Set up loading debugging for a model viewer
     * 
     * @param {Element} modelViewer The model-viewer element
     * @param {number} index Index of the model viewer on the page
     */
    function setupLoadingDebug(modelViewer, index) {
        let loadStartTime;
        
        modelViewer.addEventListener('preload', function() {
            loadStartTime = performance.now();
            if (debugLoading && !isWordPressAdmin()) {
                console.log(`ExploreXR Debug [${modelViewer.id}]: Preload started`);
            }
        });
        
        modelViewer.addEventListener('progress', function(event) {
            if (debugLoading && !isWordPressAdmin()) {
                // Round to nearest 25% increment (0%, 25%, 50%, 75%, 100%)
                const progressPercent = Math.round(event.detail.totalProgress * 4) * 25;
                console.log(`ExploreXR Debug [${modelViewer.id}]: Loading progress: ${progressPercent}%`);
            }
        });
        
        modelViewer.addEventListener('load', function() {
            if (debugLoading && !isWordPressAdmin()) {
                const loadTime = loadStartTime ? performance.now() - loadStartTime : 0;
                console.log(`ExploreXR Debug [${modelViewer.id}]: Model loaded successfully in ${loadTime.toFixed(2)}ms`);
                logModelDetails(modelViewer);
            }
        });
        
        modelViewer.addEventListener('error', function(error) {
            if (debugMode) {
                console.warn(`ExploreXR Debug [${modelViewer.id}]: Model encountered an issue:`, error);
            }
        });
    }
    
    /**
     * Set up AR debugging for a model viewer
     * 
     * @param {Element} modelViewer The model-viewer element
     * @param {number} index Index of the model viewer on the page
     */
    function setupARDebug(modelViewer, index) {
        // Check AR support
        console.log(`ExploreXR Debug [${modelViewer.id}]: AR Enabled: ${modelViewer.hasAttribute('ar')}`);
        console.log(`ExploreXR Debug [${modelViewer.id}]: AR Modes: ${modelViewer.getAttribute('ar-modes') || 'Not specified'}`);
        
        // Track when the AR button becomes available
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    for (let i = 0; i < mutation.addedNodes.length; i++) {
                        if (mutation.addedNodes[i].classList && mutation.addedNodes[i].classList.contains('ar-button')) {
                            console.log(`ExploreXR Debug [${modelViewer.id}]: AR button added to DOM`);
                            
                            // Check if device supports AR
                            if (modelViewer.canActivateAR) {
                                console.log(`ExploreXR Debug [${modelViewer.id}]: Device supports AR`);
                            } else {
                                console.log(`ExploreXR Debug [${modelViewer.id}]: Device does not support AR`);
                            }
                            
                            observer.disconnect();
                            break;
                        }
                    }
                }
            });
        });
        
        observer.observe(modelViewer, { childList: true, subtree: true });
        
        // Track AR session status
        modelViewer.addEventListener('ar-status', function(event) {
            console.log(`ExploreXR Debug [${modelViewer.id}]: AR Status: ${event.detail.status}`);
            
            if (event.detail.status === 'session-started') {
                console.log(`ExploreXR Debug [${modelViewer.id}]: AR session started`);
            } else if (event.detail.status === 'not-presenting') {
                console.log(`ExploreXR Debug [${modelViewer.id}]: AR session ended`);
            } else if (event.detail.status === 'failed') {
                console.log(`ExploreXR Debug [${modelViewer.id}]: AR session failed to start`);
            }
        });
    }
    
    /**
     * Set up camera debugging for a model viewer
     * 
     * @param {Element} modelViewer The model-viewer element
     * @param {number} index Index of the model viewer on the page
     */
    function setupCameraDebug(modelViewer, index) {
        // Log initial camera settings
        console.log(`ExploreXR Debug [${modelViewer.id}]: Camera controls enabled: ${modelViewer.hasAttribute('camera-controls')}`);
        console.log(`ExploreXR Debug [${modelViewer.id}]: Initial camera orbit: ${modelViewer.getAttribute('camera-orbit') || 'Default'}`);
        console.log(`ExploreXR Debug [${modelViewer.id}]: Initial camera target: ${modelViewer.getAttribute('camera-target') || 'Default'}`);
        console.log(`ExploreXR Debug [${modelViewer.id}]: Field of view: ${modelViewer.getAttribute('field-of-view') || 'Default'}`);
        
        // Log camera changes (throttled to avoid excessive logging)
        let throttleTimeout;
        modelViewer.addEventListener('camera-change', function() {
            clearTimeout(throttleTimeout);
            throttleTimeout = setTimeout(function() {
                console.log(`ExploreXR Debug [${modelViewer.id}]: Camera changed`);
                console.log(`  Orbit: ${modelViewer.getCameraOrbit().toString()}`);
                console.log(`  Target: ${modelViewer.getCameraTarget().toString()}`);
                console.log(`  FOV: ${modelViewer.getFieldOfView()}deg`);
            }, 500); // Throttle to log at most once every 500ms
        });
    }
    
    /**
     * Set up animation debugging for a model viewer
     * 
     * @param {Element} modelViewer The model-viewer element
     * @param {number} index Index of the model viewer on the page
     */
    function setupAnimationDebug(modelViewer, index) {
        // Wait until the model is loaded to check animations
        modelViewer.addEventListener('load', function() {
            // Check available animations
            if (modelViewer.availableAnimations && modelViewer.availableAnimations.length > 0) {
                console.log(`ExploreXR Debug [${modelViewer.id}]: Model has ${modelViewer.availableAnimations.length} animation(s):`);
                modelViewer.availableAnimations.forEach(function(animation, i) {
                    console.log(`  Animation #${i + 1}: ${animation}`);
                });
                
                // Log the autoplay status
                console.log(`ExploreXR Debug [${modelViewer.id}]: Animation autoplay: ${modelViewer.hasAttribute('autoplay') ? 'Enabled' : 'Disabled'}`);
            } else {
                console.log(`ExploreXR Debug [${modelViewer.id}]: Model has no animations`);
            }
        });
        
        // Track animation playback events
        modelViewer.addEventListener('play', function() {
            console.log(`ExploreXR Debug [${modelViewer.id}]: Animation started playing: ${modelViewer.animationName}`);
        });
        
        modelViewer.addEventListener('pause', function() {
            console.log(`ExploreXR Debug [${modelViewer.id}]: Animation paused: ${modelViewer.animationName}`);
        });
        
        modelViewer.addEventListener('timeupdate', function(event) {
            // Only log occasionally to avoid flooding the console
            if (Math.random() < 0.05) { // Log roughly 5% of timeupdate events
                console.log(`ExploreXR Debug [${modelViewer.id}]: Animation time: ${event.detail.time.toFixed(2)}s`);
            }
        });
    }
    
    /**
     * Set up annotation debugging for a model viewer
     * 
     * @param {Element} modelViewer The model-viewer element
     * @param {number} index Index of the model viewer on the page
     */
    function setupAnnotationDebug(modelViewer, index) {
        // Log annotation details after model load
        modelViewer.addEventListener('load', function() {
            const hotspots = modelViewer.querySelectorAll('[slot^="hotspot"]');
            
            console.log(`ExploreXR Debug [${modelViewer.id}]: Model has ${hotspots.length} annotation hotspot(s)`);
            
            hotspots.forEach(function(hotspot, i) {
                const position = hotspot.dataset.position || 'Unknown';
                const normal = hotspot.dataset.normal || 'Not specified';
                const slotName = hotspot.getAttribute('slot');
                
                console.log(`  Hotspot #${i + 1} (${slotName}):`);
                console.log(`    Position: ${position}`);
                console.log(`    Normal: ${normal}`);
            });
        });
        
        // Set up a mutation observer to detect when hotspots are added dynamically
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    for (let i = 0; i < mutation.addedNodes.length; i++) {
                        const node = mutation.addedNodes[i];
                        if (node.nodeType === 1 && node.getAttribute && node.getAttribute('slot') && node.getAttribute('slot').startsWith('hotspot')) {
                            console.log(`ExploreXR Debug [${modelViewer.id}]: Hotspot added dynamically: ${node.getAttribute('slot')}`);
                        }
                    }
                }
            });
        });
        
        observer.observe(modelViewer, { childList: true, subtree: true });
        
        // Log clicks on hotspots
        modelViewer.addEventListener('click', function(event) {
            if (event.target !== modelViewer && event.target.hasAttribute && event.target.hasAttribute('slot') && event.target.getAttribute('slot').startsWith('hotspot')) {
                console.log(`ExploreXR Debug [${modelViewer.id}]: Hotspot clicked: ${event.target.getAttribute('slot')}`);
            }
        });
    }
    
    /**
     * Log model details after loading
     * 
     * @param {Element} modelViewer The model-viewer element
     */
    function logModelDetails(modelViewer) {
        // Don't continue if debug mode is disabled
        if (!debugMode) return;
        
        console.log(`ExploreXR Debug [${modelViewer.id}]: Model details:`);
        
        // Model URL
        console.log(`  Source: ${modelViewer.getAttribute('src')}`);
        
        // Model dimensions
        console.log(`  Dimensions (width × height): ${modelViewer.offsetWidth}px × ${modelViewer.offsetHeight}px`);
        
        // Model format
        const sourceUrl = modelViewer.getAttribute('src');
        const fileExtension = sourceUrl.split('.').pop().toLowerCase();
        console.log(`  File format: ${fileExtension}`);
        
        // Check if model has a poster image
        console.log(`  Poster image: ${modelViewer.hasAttribute('poster') ? 'Yes' : 'No'}`);
        
        // Get bounding box details when model is loaded
        if (modelViewer.model) {
            try {
                const boundingBox = modelViewer.getBoundingBoxCenter();
                console.log(`  Bounding box center: ${boundingBox.toString()}`);
            } catch (error) {
                console.log(`  Couldn't get bounding box: ${error.message}`);
            }
        }
    }
})();