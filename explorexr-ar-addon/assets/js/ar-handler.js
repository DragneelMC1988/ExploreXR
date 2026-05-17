/**
 * ExploreXR AR Add-On - AR Handler JavaScript
 * 
 * Handles AR functionality for 3D models on the client side.
 */

(function($) {
    'use strict';
    
    // Store refs to all active AR sessions for management
    let activeARSessions = [];
    let arSessionsInitialized = false;
      // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        // Use centralized loader to ensure model-viewer is available
        if (window.loadModelViewer && !window.isModelViewerLoaded()) {
            if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('AR Handler: Waiting for model-viewer to load...');
                }
            }
            window.loadModelViewer()
                .then(function() {
                    if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                        if (typeof ExploreXRLogger !== 'undefined') {
                            ExploreXRLogger.log('AR Handler: Model viewer loaded, initializing AR functionality');
                        }
                    }
                    initializeARHandler();
                })
                .catch(function(error) {
                    if (typeof ExploreXRLogger !== 'undefined') {
                        ExploreXRLogger.error('AR Handler: Failed to load model-viewer:', error);
                    }
                    tryFallbackInit();
                });
        } else if (typeof customElements !== 'undefined' && customElements.get('model-viewer')) {
            if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('AR Handler: Model viewer already available');
                }
            }
            initializeARHandler();
        } else {
            if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('AR Handler: Waiting for model-viewer to be available...');
                }
            }
            tryFallbackInit();
        }
    });

    function initializeARHandler() {
        if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('Model Viewer component detected - initializing AR handler');
            }
        }
        // Initialize AR functionality with a delay to ensure model-viewer component is fully loaded
        setTimeout(() => {
            initExploreXRAR();
            arSessionsInitialized = true;
        }, 500);
        
        // Watch for dynamically added model-viewers
        observeNewModelViewers();
        
        // Listen for our custom event when model viewers are added through AJAX
        document.addEventListener('explorexr-premium-model-added', function() {
            // Use a longer timeout for dynamically added models
            setTimeout(initExploreXRAR, 800);
        });
    }

    function tryFallbackInit() {
        // Try to initialize again after a delay
        setTimeout(() => {
            if (typeof customElements !== 'undefined' && customElements.get('model-viewer')) {
                initializeARHandler();
            } else {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.error('AR Handler: Failed to initialize - Model Viewer component not available');
                }
            }
        }, 2000);
    }
      /**
     * Initialize AR functionality for all model-viewer elements
     */
    function initExploreXRAR() {
        // Target both elements with the AR attribute and those with our custom data attribute
        const modelViewers = document.querySelectorAll('model-viewer[ar], model-viewer[data-explorexr-premium-ar-enabled="true"]');
        
        if (modelViewers.length === 0) {
            if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('No AR-enabled model viewers found');
                }
            }
            return;
        }
        
        if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('Initializing AR for ' + modelViewers.length + ' model viewers');
            }
        }
        
        // Check AR support before proceeding
        const arSupport = window.explorexrARFeatures ? window.explorexrARFeatures.isARSupported() : null;
        
        modelViewers.forEach(function(modelViewer) {
            if (!modelViewer.hasAttribute('data-explorexr-premium-ar-initialized')) {
                // Add our marker to prevent double initialization
                modelViewer.setAttribute('data-explorexr-premium-ar-initialized', 'true');
                  // Check if AR is actually supported on this device
                if (arSupport && !arSupport.anySupported) {
                    if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                        if (typeof ExploreXRLogger !== 'undefined') {
                            ExploreXRLogger.log('AR not supported on this device, disabling AR for model viewer');
                        }
                    }
                    // Use AR features module to disable AR if available
                    if (window.explorexrARFeatures && window.explorexrARFeatures.disableARForModelViewer) {
                        window.explorexrARFeatures.disableARForModelViewer(modelViewer, arSupport);
                    } else {
                        // Fallback: manually remove AR attributes
                        modelViewer.removeAttribute('ar');
                        modelViewer.removeAttribute('ar-modes');
                        modelViewer.setAttribute('data-explorexr-premium-ar-supported', 'false');
                    }
                    return; // Skip further AR setup for this model viewer
                }
                
                // If it has our data attribute but not the real AR attribute, add it now
                if (modelViewer.hasAttribute('data-explorexr-premium-ar-enabled') && !modelViewer.hasAttribute('ar')) {
                    if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                        if (typeof ExploreXRLogger !== 'undefined') {
                            ExploreXRLogger.log('Adding AR attribute to model viewer');
                        }
                    }
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
                if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                    if (typeof ExploreXRLogger !== 'undefined') {
                        ExploreXRLogger.log('Model viewer ready for loading:', modelViewer.getAttribute('src'));
                    }
                }
                
                // Setup all AR event handlers and processing
                setupARForModelViewer(modelViewer);
                
                // Set up analytics tracking
                setupARTracking(modelViewer);
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
            if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('Model fully loaded - enabling AR');
                }
            }
            
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
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.error('ExploreXR AR Error:', event.detail);
            }
            
            // If error occurred while in AR mode, provide recovery options
            if (modelViewer.getAttribute('ar-status') === 'session-started' || 
                modelViewer.getAttribute('ar-status') === 'not-presenting') {
                
                // Get detailed information about the error
                const errorType = event.detail?.type || 'unknown';
                const sourceError = event.detail?.sourceError?.message || 
                                   (typeof event.detail?.sourceError === 'string' ? event.detail.sourceError : '') ||
                                   '';
                
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.error(`ExploreXR AR Error Details - Type: ${errorType}, Message: ${sourceError}`);
                }
                
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
                    if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                        if (typeof ExploreXRLogger !== 'undefined') {
                            ExploreXRLogger.log('AR session started', arId);
                        }
                    }
                    activeARSessions.push({
                        id: arId,
                        modelViewer: modelViewer,
                        startTime: Date.now()
                    });
                    
                    // Critical fix: Add an invisible element to maintain the AR context
                    addARContextAnchor(modelViewer);
                    
                    // Add AR UI
                    addARSessionUI(modelViewer);
                    
                    // Track AR session start for analytics
                    trackAREvent(modelViewer, 'ar_session_start');
                    break;
                    
                case 'session-ended':
                    if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                        if (typeof ExploreXRLogger !== 'undefined') {
                            ExploreXRLogger.log('AR session ended', arId);
                        }
                    }
                    // Remove from active sessions
                    activeARSessions = activeARSessions.filter(session => session.id !== arId);
                    // Clean up any context anchors
                    removeARContextAnchor(modelViewer);
                    // Remove AR UI
                    removeARSessionUI();
                    
                    // Track AR session end for analytics
                    trackAREvent(modelViewer, 'ar_session_end');
                    break;
                    
                case 'failed':
                    if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                        if (typeof ExploreXRLogger !== 'undefined') {
                            ExploreXRLogger.error('AR session failed:', event.detail);
                        }
                    }
                    
                    // Get more detailed error info
                    const errorInfo = {
                        type: event.detail.type || 'unknown',
                        message: event.detail.error || 
                                (event.detail.sourceError ? event.detail.sourceError.message : '') || 
                                'Unknown error'
                    };
                    
                    if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                        if (typeof ExploreXRLogger !== 'undefined') {
                            ExploreXRLogger.error(`ExploreXR AR Session Failed - Type: ${errorInfo.type}, Message: ${errorInfo.message}`);
                        }
                    }
                    displayARError(modelViewer, errorInfo);
                    break;
                    
                case 'not-presenting':
                    // This happens when AR mode is exited or fails to start
                    if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                        if (typeof ExploreXRLogger !== 'undefined') {
                            ExploreXRLogger.log('AR not presenting');
                        }
                    }
                    removeARContextAnchor(modelViewer);
                    removeARSessionUI();
                    break;
            }
        });
        
        // Track AR object placement and visibility
        modelViewer.addEventListener('ar-tracking', function(event) {
            const status = event.detail.status;
            
            if (status === 'not-tracking') {
                if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                    if (typeof ExploreXRLogger !== 'undefined') {
                        ExploreXRLogger.log('AR tracking lost - object may disappear');
                    }
                }
                // Add intervention to prevent disappearing
                stabilizeARObject(modelViewer);
            } else if (status === 'tracking') {
                if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                    if (typeof ExploreXRLogger !== 'undefined') {
                        ExploreXRLogger.log('AR tracking established');
                    }
                }
            }
        });
        
        // Check for AR support
        if (!modelViewer.canActivateAR) {
            if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('AR not supported on this device/browser');
                }
            }
            // Add fallback text
            addARFallbackMessage(modelViewer);
        }
    }
    
    /**
     * Set up AR tracking for analytics
     */
    function setupARTracking(modelViewer) {
        // Track AR button view (for analytics)
        trackARButtonView(modelViewer);
    }
    
    /**
     * Add AR session UI elements
     */
    function addARSessionUI(modelViewer) {
        // Only add UI if it doesn't already exist
        if (document.querySelector('.explorexr-premium-ar-controls')) {
            return;
        }
        
        // Create AR controls container
        const arControls = document.createElement('div');
        arControls.className = 'explorexr-premium-ar-controls';
        
        // Create move button
        const moveButton = document.createElement('button');
        moveButton.className = 'explorexr-premium-ar-move-btn active';
        moveButton.textContent = 'Move';
        moveButton.setAttribute('data-mode', 'move');
        
        // Create scale button
        const scaleButton = document.createElement('button');
        scaleButton.className = 'explorexr-premium-ar-scale-btn';
        scaleButton.textContent = 'Scale';
        scaleButton.setAttribute('data-mode', 'scale');
        
        // Add buttons to controls
        arControls.appendChild(moveButton);
        arControls.appendChild(scaleButton);
        
        // Add controls to body
        document.body.appendChild(arControls);
        
        // Create scale indicator
        const scaleIndicator = document.createElement('div');
        scaleIndicator.className = 'explorexr-premium-scale-indicator';
        scaleIndicator.textContent = '100%';
        document.body.appendChild(scaleIndicator);
        
        // Set up event listeners
        moveButton.addEventListener('click', function() {
            setARMode('move');
        });
        
        scaleButton.addEventListener('click', function() {
            setARMode('scale');
        });
    }

    /**
     * Set AR interaction mode
     */
    function setARMode(mode) {
        const moveButton = document.querySelector('.explorexr-premium-ar-move-btn');
        const scaleButton = document.querySelector('.explorexr-premium-ar-scale-btn');
        const scaleIndicator = document.querySelector('.explorexr-premium-scale-indicator');
        
        if (mode === 'move') {
            moveButton.classList.add('active');
            scaleButton.classList.remove('active');
            scaleIndicator.classList.remove('visible');
        } else if (mode === 'scale') {
            moveButton.classList.remove('active');
            scaleButton.classList.add('active');
            scaleIndicator.classList.add('visible');
        }
    }

    /**
     * Remove AR session UI elements
     */
    function removeARSessionUI() {
        const arControls = document.querySelector('.explorexr-premium-ar-controls');
        const scaleIndicator = document.querySelector('.explorexr-premium-scale-indicator');
        
        if (arControls) {
            arControls.parentNode.removeChild(arControls);
        }
        
        if (scaleIndicator) {
            scaleIndicator.parentNode.removeChild(scaleIndicator);
        }
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
                if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                    if (typeof ExploreXRLogger !== 'undefined') {
                        ExploreXRLogger.log('Model not fully loaded - preventing AR activation');
                    }
                }
                return false;
            }
            
            if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('Custom AR activation - ensuring model is ready');
                }
            }
            
            // Force model to be visible and ready before activating AR
            modelViewer.dismissPoster();
            
            // Short delay to ensure model is fully processed before AR
            setTimeout(() => {
                // Activate AR programmatically after ensuring model is ready
                try {
                    // The key fix: use activateAR() when available
                    if (typeof modelViewer.activateAR === 'function') {
                        modelViewer.activateAR();
                        return;
                    }
                    if (typeof modelViewer.showAR === 'function') {
                        modelViewer.showAR();
                        return;
                    }

                    // Fallback to normal AR activation
                    if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                        if (typeof ExploreXRLogger !== 'undefined') {
                            ExploreXRLogger.log('Using fallback AR activation');
                        }
                    }
                    modelViewer.setAttribute('ar', '');
                    
                    // Give original button a delayed click
                    setTimeout(() => {
                        const originalArButton = getARButton(modelViewer);
                        const slotButton = modelViewer.querySelector('button[slot="ar-button"]');
                        const shadowButton = modelViewer.shadowRoot ? modelViewer.shadowRoot.querySelector('.ar-button') : null;
                        const fallbackButton = originalArButton || shadowButton || slotButton;
                        if (fallbackButton && fallbackButton !== newArButton) {
                            fallbackButton.click();
                        }
                    }, 100);
                } catch (err) {
                    if (typeof ExploreXRLogger !== 'undefined') {
                        ExploreXRLogger.error('Error activating AR:', err);
                    }
                    displayARError(modelViewer, 'Failed to activate AR: ' + err.message);
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
        contextAnchor.className = 'explorexr-premium-ar-context-anchor';
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
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.error('AR Error:', errorMessage);
        }
        
        // Create error overlay
        const container = modelViewer.parentElement;
        let errorDiv = container.querySelector('.explorexr-premium-ar-error');
        
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'explorexr-premium-ar-error';
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
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.warn('getARButton: modelViewer is null or undefined');
            }
            return null;
        }
        
        // Check if shadowRoot exists before trying to access it
        if (!modelViewer.shadowRoot) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.warn('getARButton: modelViewer.shadowRoot is not yet available');
            }
            return null;
        }
        
        // Safely query the shadowRoot with try-catch
        try {
            const button = modelViewer.shadowRoot.querySelector('button[slot="ar-button"]');
            // DO NOT apply inline styles here - conflicts with custom button styling
            // Custom button styling is handled by ar-enhanced.js
            return button;
        } catch (error) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.warn('getARButton: Error querying shadowRoot', error);
            }
            return null;
        }
    }    /**
     * Add AR fallback message for unsupported devices
     */
    function addARFallbackMessage(modelViewer) {
        // Check if we should show the message based on device type
        const arSupport = window.explorexrARFeatures ? window.explorexrARFeatures.isARSupported() : null;
        
        // Only show "not supported" message on mobile devices that truly don't support AR
        // Don't show this message on desktop/laptop computers
        if (arSupport && !arSupport.shouldShowNotSupported) {
            if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('AR fallback message suppressed for desktop device');
                }
            }
            return;
        }
        
        // Add a subtle message that AR isn't supported
        const fallbackText = typeof explorexrARSettings !== 'undefined' && explorexrARSettings.fallbackText ? 
            explorexrARSettings.fallbackText : 'AR not supported on this device';
        
        const fallbackMessage = document.createElement('div');
        fallbackMessage.className = 'explorexr-premium-ar-not-supported';
        fallbackMessage.textContent = fallbackText;
        
        // Add next to model viewer
        const container = modelViewer.parentNode;
        container.appendChild(fallbackMessage);
        
        // Fade out after a few seconds
        setTimeout(function() {
            fallbackMessage.style.opacity = '0';
            
            // Remove after fade out
            setTimeout(function() {
                if (fallbackMessage.parentNode) {
                    fallbackMessage.parentNode.removeChild(fallbackMessage);
                }
            }, 500);
        }, 3000);
    }

    /**
     * Track AR button view (for analytics)
     */
    function trackARButtonView(modelViewer) {
        // Create a new IntersectionObserver for the model viewer
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    // Model viewer is now visible
                    trackAREvent(modelViewer, 'ar_view');
                    
                    // Stop observing after tracking
                    observer.disconnect();
                }
            });
        }, { threshold: 0.5 });
        
        // Start observing the model viewer
        observer.observe(modelViewer);
    }

    /**
     * Track AR events for analytics
     */
    function trackAREvent(modelViewer, eventType) {
        // Get model ID from various possible data attributes
        const modelId = modelViewer.dataset.modelId || 
                       modelViewer.dataset.debugModelId || 
                       modelViewer.getAttribute('data-model-id') ||
                       '0';
        
        // Check if tracking is enabled and required data is available
        if (typeof explorexrARSettings === 'undefined' || 
            !explorexrARSettings.enableTracking ||
            !explorexrARSettings.nonce) {
            // Silently skip tracking if not properly configured
            return;
        }
        
        // Determine device type
        const deviceType = getDeviceType();
        
        // Send tracking data via AJAX
        $.ajax({
            url: typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'explorexr_ar_track_usage',
                nonce: explorexrARSettings.nonce,
                model_id: modelId,
                event_type: eventType,
                device_type: deviceType
            },
            success: function(response) {
                if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
                    if (typeof ExploreXRLogger !== 'undefined') {
                        ExploreXRLogger.log('AR tracking successful:', eventType);
                    }
                }
            },
            error: function(xhr, status, error) {
                // Only log error if it's not a configuration issue
                if (xhr.status !== 400) {
                    if (typeof ExploreXRLogger !== 'undefined') {
                        ExploreXRLogger.error('AR tracking error:', error);
                    }
                }
            }
        });
    }

    /**
     * Get device type for tracking
     */
    function getDeviceType() {
        const userAgent = navigator.userAgent || navigator.vendor || window.opera;
        
        // Check for iOS
        if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
            return 'iOS';
        }
        
        // Check for Android
        if (/android/i.test(userAgent)) {
            return 'Android';
        }
        
        // Check if it's a potential AR headset or WebXR capable device
        if ('xr' in navigator) {
            return 'WebXR Capable';
        }
        
        return 'Other';
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
                setTimeout(initExploreXRAR, 500);
            }
        });
        
        // Start observing the document for added model-viewer elements
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})(jQuery);
