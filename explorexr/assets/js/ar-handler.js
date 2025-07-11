/**
 * expoXR AR Handler
 * 
 * This file contains specialized code to handle Augmented Reality functionality
 * for the expoXR plugin, addressing issues with AR mode initialization and model disappearing.
 */

(function() {
    // Store refs to all active AR sessions for management
    let activeARSessions = [];
    let arSessionsInitialized = false;
    
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        // Verify model-viewer component is loaded
        if (typeof customElements !== 'undefined' && customElements.get('model-viewer')) {
            console.log('Model Viewer component detected - initializing AR handler');
            // Initialize AR functionality with a delay to ensure model-viewer component is fully loaded
            setTimeout(() => {
                initExpoXRAR();
                arSessionsInitialized = true;
            }, 500);
            
            // Watch for dynamically added model-viewers
            observeNewModelViewers();
            
            // Listen for our custom event when model viewers are added through AJAX
            document.addEventListener('expoxr-model-added', function() {
                // Use a longer timeout for dynamically added models
                setTimeout(initExpoXRAR, 800);
            });
        } else {
            console.error('Model Viewer component not loaded yet - AR functionality may be limited');
            // Try to initialize again after a delay
            setTimeout(() => {
                if (typeof customElements !== 'undefined' && customElements.get('model-viewer')) {
                    initExpoXRAR();
                    arSessionsInitialized = true;
                } else {
                    console.error('Failed to initialize AR handler - Model Viewer component not available');
                }
            }, 2000);
        }
    });
      /**
     * Initialize AR functionality for all model-viewer elements
     */
    function initExpoXRAR() {
        // Target both elements with the AR attribute and those with our custom data attribute
        const modelViewers = document.querySelectorAll('model-viewer[ar], model-viewer[data-expoxr-ar-enabled="true"]');
        
        if (modelViewers.length === 0) {
            console.log('No AR-enabled model viewers found');
            return;
        }
        
        console.log('Initializing AR for ' + modelViewers.length + ' model viewers');
        
        // Check AR support if available
        const arSupport = window.expoxrARFeatures ? window.expoxrARFeatures.isARSupported() : null;
        
        modelViewers.forEach(function(modelViewer) {
            if (!modelViewer.hasAttribute('data-expoxr-ar-initialized')) {
                // Add our marker to prevent double initialization
                modelViewer.setAttribute('data-expoxr-ar-initialized', 'true');
                
                // Check if AR is actually supported on this device
                if (arSupport && !arSupport.anySupported) {
                    console.log('AR not supported on this device, disabling AR for model viewer');
                    // Remove AR attributes
                    modelViewer.removeAttribute('ar');
                    modelViewer.removeAttribute('ar-modes');
                    modelViewer.setAttribute('data-expoxr-ar-supported', 'false');
                    return; // Skip further AR setup for this model viewer
                }
                
                // If it has our data attribute but not the real AR attribute, add it now
                if (modelViewer.hasAttribute('data-expoxr-ar-enabled') && !modelViewer.hasAttribute('ar')) {
                    console.log('Adding AR attribute to model viewer');
                    modelViewer.setAttribute('ar', '');
                }
                
                // Force the model to load completely before AR is available
                if (!modelViewer.loaded) {
                    // If AR button exists in shadow DOM, disable it until model is fully loaded
                    const arButton = getARButton(modelViewer);
                    if (arButton) {
                        arButton.disabled = true;
                        arButton.style.opacity = "0.5";
                    }
                    
                    // Force a preload
                    if (!modelViewer.hasAttribute('preload')) {
                        modelViewer.setAttribute('preload', '');
                    }
                }
                
                // Log that this model viewer is ready for loading
                console.log('Model viewer ready for loading:', modelViewer.getAttribute('src'));
                
                // Setup all AR event handlers and processing
                setupARForModelViewer(modelViewer);
            }
        });
    }
    
    /**
     * Set up AR functionality for a specific model-viewer element
     */
    function setupARForModelViewer(modelViewer) {
        // Create a wrapper around the model-viewer for AR state management
        const uniqueId = 'ar-' + Math.random().toString(36).substr(2, 9);
        modelViewer.setAttribute('data-ar-id', uniqueId);
        
        // Force certain attributes for better AR handling
        if (!modelViewer.hasAttribute('reveal')) {
            modelViewer.setAttribute('reveal', 'interaction');
        }
        
        // Make sure we're catching when the model has finished loading
        modelViewer.addEventListener('load', function() {
            console.log('Model fully loaded - enabling AR');
            
            // Enable AR button when model is loaded
            const arButton = getARButton(modelViewer);
            if (arButton) {
                arButton.disabled = false;
                arButton.style.opacity = "1";
                
                // Replace the default click handler
                replaceARButtonHandler(modelViewer, arButton);
            }
        });
        
        // Listen for model error events
        modelViewer.addEventListener('error', function(event) {
            console.error('ExpoXR AR Error:', event.detail);
            
            // If error occurred while in AR mode, provide recovery options
            if (modelViewer.getAttribute('ar-status') === 'session-started' || 
                modelViewer.getAttribute('ar-status') === 'not-presenting') {
                
                // Get detailed information about the error
                const errorType = event.detail?.type || 'unknown';
                const sourceError = event.detail?.sourceError?.message || 
                                   (typeof event.detail?.sourceError === 'string' ? event.detail.sourceError : '') ||
                                   '';
                
                console.error(`ExpoXR AR Error Details - Type: ${errorType}, Message: ${sourceError}`);
                
                // Display user-friendly error
                displayARError(modelViewer, { type: errorType, message: sourceError });
            }
        });
        
        // Track all AR session state changes
        modelViewer.addEventListener('ar-status', function(event) {
            const status = event.detail.status;
            const arId = modelViewer.getAttribute('data-ar-id');
            
            switch(status) {
                case 'session-started':
                    console.log('AR session started', arId);
                    activeARSessions.push({
                        id: arId,
                        modelViewer: modelViewer,
                        startTime: Date.now()
                    });
                    
                    // Critical fix: Add an invisible element to maintain the AR context
                    addARContextAnchor(modelViewer);
                    break;
                    
                case 'session-ended':
                    console.log('AR session ended', arId);
                    // Remove from active sessions
                    activeARSessions = activeARSessions.filter(session => session.id !== arId);
                    // Clean up any context anchors
                    removeARContextAnchor(modelViewer);
                    break;
                    
                case 'failed':
                    console.error('AR session failed:', event.detail);
                    
                    // Get more detailed error info
                    const errorInfo = {
                        type: event.detail.type || 'unknown',
                        message: event.detail.error || 
                                (event.detail.sourceError ? event.detail.sourceError.message : '') || 
                                'Unknown error'
                    };
                    
                    console.error(`ExpoXR AR Session Failed - Type: ${errorInfo.type}, Message: ${errorInfo.message}`);
                    displayARError(modelViewer, errorInfo);
                    break;
                    
                case 'not-presenting':
                    // This happens when AR mode is exited or fails to start
                    console.log('AR not presenting');
                    removeARContextAnchor(modelViewer);
                    break;
            }
        });
        
        // Track AR object placement and visibility
        modelViewer.addEventListener('ar-tracking', function(event) {
            const status = event.detail.status;
            
            if (status === 'not-tracking') {
                console.log('AR tracking lost - object may disappear');
                // Add intervention to prevent disappearing
                stabilizeARObject(modelViewer);
            } else if (status === 'tracking') {
                console.log('AR tracking established');
            }
        });
    }
    
    /**
     * Replace the default AR button click handler with our custom one
     * This is critical to ensure AR works properly
     */
    function replaceARButtonHandler(modelViewer, arButton) {
        // Remove any existing event listeners by cloning the button
        const newArButton = arButton.cloneNode(true);
        arButton.parentNode.replaceChild(newArButton, arButton);
        
        // Add our custom handler
        newArButton.addEventListener('click', function(event) {
            // Prevent default AR activation
            event.preventDefault();
            event.stopPropagation();
            
            if (!modelViewer.loaded) {
                console.log('Model not fully loaded - preventing AR activation');
                return false;
            }
            
            console.log('Custom AR activation - ensuring model is ready');
            
            // Force model to be visible and ready before activating AR
            modelViewer.dismissPoster();
            
            // Short delay to ensure model is fully processed before AR
            setTimeout(() => {
                // Activate AR programmatically after ensuring model is ready
                try {
                    // The key fix: we need to use the activateAR() method instead of a click
                    if (typeof modelViewer.activateAR === 'function') {
                        modelViewer.activateAR();
                    } else {
                        // Fallback to normal AR activation
                        console.log('Using fallback AR activation');
                        modelViewer.setAttribute('ar', '');
                        
                        // Give original button a delayed click
                        setTimeout(() => {
                            const originalArButton = getARButton(modelViewer);
                            if (originalArButton && originalArButton !== newArButton) {
                                originalArButton.click();
                            }
                        }, 100);
                    }
                } catch (err) {
                    console.error('Error activating AR:', err);
                    displayARError(modelViewer, 'Unable to start AR mode. Please ensure your device supports AR and try again.');
                }
            }, 250);
            
            return false;
        });
    }
    
    /**
     * Add an invisible anchor element to maintain AR context
     * This prevents the model from disappearing in AR
     */
    function addARContextAnchor(modelViewer) {
        // Remove any existing anchor first
        removeARContextAnchor(modelViewer);
        
        const arId = modelViewer.getAttribute('data-ar-id');
        
        // Create an invisible div that helps maintain AR context
        const contextAnchor = document.createElement('div');
        contextAnchor.id = 'ar-context-' + arId;
        contextAnchor.className = 'expoxr-ar-context-anchor';
        contextAnchor.style.position = 'fixed';
        contextAnchor.style.top = '0';
        contextAnchor.style.left = '0';
        contextAnchor.style.width = '100%';
        contextAnchor.style.height = '100%';
        contextAnchor.style.zIndex = '-1'; // Behind everything
        contextAnchor.style.opacity = '0';
        contextAnchor.style.pointerEvents = 'none'; // Don't block interactions
        
        // Add to body
        document.body.appendChild(contextAnchor);
        
        // Store reference on the model viewer
        modelViewer.setAttribute('data-ar-context-anchor', contextAnchor.id);
    }
    
    /**
     * Remove the AR context anchor when session ends
     */
    function removeARContextAnchor(modelViewer) {
        const anchorId = modelViewer.getAttribute('data-ar-context-anchor');
        if (anchorId) {
            const anchor = document.getElementById(anchorId);
            if (anchor) {
                anchor.parentNode.removeChild(anchor);
            }
            modelViewer.removeAttribute('data-ar-context-anchor');
        }
    }
    
    /**
     * Attempt to stabilize an AR object that's losing tracking
     */
    function stabilizeARObject(modelViewer) {
        // Force the model to maintain visibility
        if (modelViewer.getAttribute('ar-status') === 'session-started') {
            // This can help prevent the model from disappearing when tracking is lost
            modelViewer.setAttribute('ar-placement', modelViewer.getAttribute('ar-placement') || 'floor');
            
            // Ensure scale is maintained
            if (!modelViewer.hasAttribute('ar-scale')) {
                modelViewer.setAttribute('ar-scale', 'fixed');
            }
        }
    }
    
    /**
     * Display an error message when AR fails
     */
    function displayARError(modelViewer, errorMessage) {
        console.error('AR Error:', errorMessage);
        
        // Create error overlay
        const container = modelViewer.parentElement;
        let errorDiv = container.querySelector('.expoxr-ar-error');
        
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'expoxr-ar-error';
            errorDiv.style.position = 'absolute';
            errorDiv.style.bottom = '10px';
            errorDiv.style.left = '0';
            errorDiv.style.right = '0';
            errorDiv.style.backgroundColor = 'rgba(0,0,0,0.7)';
            errorDiv.style.color = 'white';
            errorDiv.style.padding = '10px';
            errorDiv.style.textAlign = 'center';
            errorDiv.style.zIndex = '100';
            errorDiv.style.borderRadius = '5px';
            errorDiv.style.margin = '0 10px';
            container.appendChild(errorDiv);
        }
        
        // Show user-friendly error message
        const userMessage = getUserFriendlyARError(errorMessage);
        errorDiv.textContent = userMessage;
        
        // Hide error after 5 seconds
        setTimeout(() => {
            if (errorDiv && errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 5000);
    }
    
    /**
     * Convert technical AR errors into user-friendly messages
     */
    function getUserFriendlyARError(error) {
        const errorStr = error.toString().toLowerCase();
        
        if (errorStr.includes('not supported') || errorStr.includes('ar is not available')) {
            return 'AR is not supported on your device or browser.';
        }
        
        if (errorStr.includes('permission') || errorStr.includes('denied')) {
            return 'Please allow camera access to use AR features.';
        }
        
        if (errorStr.includes('cancel') || errorStr.includes('user canceled')) {
            return 'AR session was canceled.';
        }
        
        if (errorStr.includes('tracking') || errorStr.includes('motion')) {
            return 'Unable to track your environment. Try moving to a well-lit area with more visual features.';
        }
        
        return 'There was a problem starting AR. Please try again.';
    }
    
    /**
     * Get the AR button from a model-viewer's shadow DOM
     */
    function getARButton(modelViewer) {
        // Add thorough null checks to prevent the error
        if (!modelViewer) {
            console.warn('getARButton: modelViewer is null or undefined');
            return null;
        }
        
        // Check if shadowRoot exists before trying to access it
        if (!modelViewer.shadowRoot) {
            console.warn('getARButton: modelViewer.shadowRoot is not yet available');
            return null;
        }
        
        // Safely query the shadowRoot with try-catch
        try {
            const button = modelViewer.shadowRoot.querySelector('button[slot="ar-button"]');
            return button;
        } catch (error) {
            console.warn('getARButton: Error querying shadowRoot', error);
            return null;
        }
    }
    
    /**
     * Observe DOM changes to detect dynamically added model-viewers
     */
    function observeNewModelViewers() {
        const observer = new MutationObserver(function(mutations) {
            let hasNewModelViewers = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeName && node.nodeName.toLowerCase() === 'model-viewer') {
                            hasNewModelViewers = true;
                        } else if (node.querySelectorAll) {
                            const modelViewers = node.querySelectorAll('model-viewer');
                            if (modelViewers.length > 0) {
                                hasNewModelViewers = true;
                            }
                        }
                    });
                }
            });
            
            if (hasNewModelViewers && arSessionsInitialized) {
                // Use timeout to ensure components are fully rendered
                setTimeout(initExpoXRAR, 500);
            }
        });
        
        // Start observing the document for added model-viewer elements
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})();