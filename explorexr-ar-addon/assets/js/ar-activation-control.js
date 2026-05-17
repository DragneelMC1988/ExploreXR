/**
 * ExploreXR AR Add-On - AR Activation Control
 * 
 * Manages AR element display/removal based on:
 * 1. AR enabled/disabled status from admin settings
 * 2. Device AR support capability
 * 3. Proper cleanup when AR is disabled or not supported
 */

(function() {
    'use strict';

    /**
     * Initialize AR activation control
     */
    function initARActivationControl() {
        // Wait for AR features module to be available
        if (window.explorexrARFeatures && window.explorexrARFeatures.isARSupported) {
            processAllModelViewers();
            observeNewModelViewers();
        } else {
            // Retry after a short delay
            setTimeout(initARActivationControl, 500);
        }
    }

    /**
     * Observe DOM for newly added model-viewer elements
     */
    function observeNewModelViewers() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type !== 'childList') {
                    return;
                }

                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType !== Node.ELEMENT_NODE) {
                        return;
                    }

                    if (node.tagName && node.tagName.toLowerCase() === 'model-viewer') {
                        if (!node.hasAttribute('data-explorexr-premium-ar-processed')) {
                            node.setAttribute('data-explorexr-premium-ar-processed', 'true');
                            processModelViewerAR(node);
                        }
                        return;
                    }

                    if (node.querySelectorAll) {
                        const viewers = node.querySelectorAll('model-viewer');
                        viewers.forEach((viewer) => {
                            if (!viewer.hasAttribute('data-explorexr-premium-ar-processed')) {
                                viewer.setAttribute('data-explorexr-premium-ar-processed', 'true');
                                processModelViewerAR(viewer);
                            }
                        });
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    /**
     * Process all model viewers for AR activation control
     */
    function processAllModelViewers() {
        const modelViewers = document.querySelectorAll('model-viewer');
        
        modelViewers.forEach(function(modelViewer) {
            if (!modelViewer.hasAttribute('data-explorexr-premium-ar-processed')) {
                modelViewer.setAttribute('data-explorexr-premium-ar-processed', 'true');
                processModelViewerAR(modelViewer);
            }
        });
    }

    /**
     * Process a single model viewer for AR activation control
     * 
     * @param {HTMLElement} modelViewer The model-viewer element
     */
    function processModelViewerAR(modelViewer) {
        // Check if AR is disabled for this model
        if (modelViewer.hasAttribute('data-explorexr-premium-ar-disabled')) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('AR disabled for this model viewer via settings');
            }
            disableARCompletely(modelViewer);
            return;
        }

        // Check if AR is enabled
        const hasARAttribute = modelViewer.hasAttribute('ar');
        const isAREnabled = modelViewer.hasAttribute('data-explorexr-premium-ar-enabled') && 
                           modelViewer.getAttribute('data-explorexr-premium-ar-enabled') === 'true';

        if (!hasARAttribute && !isAREnabled) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('AR not enabled for this model viewer');
            }
            return;
        }

        // Check device support
        const arSupport = window.explorexrARFeatures.isARSupported();
        
        if (!arSupport.anySupported) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('AR not supported on this device');
            }
            disableARForUnsupportedDevice(modelViewer);
        } else {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('AR supported and enabled for this model viewer');
            }
            enableARWithSupport(modelViewer, arSupport);
        }
    }

    /**
     * Completely disable AR (when disabled in settings)
     * 
     * @param {HTMLElement} modelViewer The model-viewer element
     */
    function disableARCompletely(modelViewer) {
        // Remove all AR-related attributes
        modelViewer.removeAttribute('ar');
        modelViewer.removeAttribute('ar-modes');
        modelViewer.removeAttribute('ar-scale');
        modelViewer.removeAttribute('ar-placement');
        modelViewer.removeAttribute('ios-src');
        modelViewer.removeAttribute('environment-image');
        modelViewer.removeAttribute('data-explorexr-premium-ar-enabled');
        
        // Set disabled state
        modelViewer.setAttribute('data-explorexr-premium-ar-status', 'disabled');
        
        // Remove any custom AR buttons
        removeCustomARButtons(modelViewer);
        
        // Hide AR not supported message (not needed when disabled)
        hideARNotSupportedMessage(modelViewer);
        
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('AR completely disabled for model viewer');
        }
    }

    /**
     * Disable AR for unsupported devices
     * 
     * @param {HTMLElement} modelViewer The model-viewer element
     */
    function disableARForUnsupportedDevice(modelViewer) {
        // Remove AR attribute to prevent model-viewer from showing AR button
        modelViewer.removeAttribute('ar');
        modelViewer.removeAttribute('ar-modes');
        
        // Set support status
        modelViewer.setAttribute('data-explorexr-premium-ar-supported', 'false');
        modelViewer.setAttribute('data-explorexr-premium-ar-status', 'unsupported');
        
        // Remove any custom AR buttons
        removeCustomARButtons(modelViewer);
        
        // Show "AR not supported" message
        showARNotSupportedMessage(modelViewer);
        
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('AR disabled for unsupported device');
        }
    }

    /**
     * Enable AR with proper device support
     * 
     * @param {HTMLElement} modelViewer The model-viewer element
     * @param {Object} arSupport AR support information
     */
    function enableARWithSupport(modelViewer, arSupport) {
        // Ensure AR attribute is present
        if (!modelViewer.hasAttribute('ar')) {
            modelViewer.setAttribute('ar', '');
        }

        // Set appropriate AR modes based on device support
        let supportedModes = [];
        
        if (arSupport.webXR) {
            supportedModes.push('webxr');
        }
        
        if (arSupport.sceneViewer) {
            supportedModes.push('scene-viewer');
        }
        
        if (arSupport.quickLook) {
            supportedModes.push('quick-look');
        }

        // Update AR modes to only include supported ones
        if (supportedModes.length > 0) {
            const currentModes = modelViewer.getAttribute('ar-modes');
            if (currentModes) {
                // Filter existing modes to only include supported ones
                const existingModes = currentModes.split(' ');
                const filteredModes = existingModes.filter(mode => supportedModes.includes(mode));
                if (filteredModes.length > 0) {
                    modelViewer.setAttribute('ar-modes', filteredModes.join(' '));
                } else {
                    modelViewer.setAttribute('ar-modes', supportedModes.join(' '));
                }
            } else {
                modelViewer.setAttribute('ar-modes', supportedModes.join(' '));
            }
        }

        // Set support status
        modelViewer.setAttribute('data-explorexr-premium-ar-supported', 'true');
        modelViewer.setAttribute('data-explorexr-premium-ar-status', 'enabled');
        
        // Hide AR not supported message
        hideARNotSupportedMessage(modelViewer);
        
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('AR enabled with device support:', supportedModes);
        }
    }

    /**
     * Remove custom AR buttons
     * 
     * @param {HTMLElement} modelViewer The model-viewer element
     */
    function removeCustomARButtons(modelViewer) {
        const customButtons = modelViewer.querySelectorAll('button[slot="ar-button"], button[data-explorexr-premium-ar-button]');
        customButtons.forEach(function(button) {
            button.remove();
        });
    }    /**
     * Show AR not supported message
     * 
     * @param {HTMLElement} modelViewer The model-viewer element
     */
    function showARNotSupportedMessage(modelViewer) {
        // Check if we should show the message based on device type
        const arSupport = window.explorexrARFeatures ? window.explorexrARFeatures.isARSupported() : null;
        
        // Only show "not supported" message on mobile devices that truly don't support AR
        // Don't show this message on desktop/laptop computers
        if (arSupport && !arSupport.shouldShowNotSupported) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('AR not supported message suppressed for desktop device');
            }
            return;
        }
        
        const container = modelViewer.parentElement;
        if (!container) return;

        let message = container.querySelector('.explorexr-premium-ar-not-supported');
        if (!message) {
            // Create the message if it doesn't exist
            message = document.createElement('div');
            message.className = 'explorexr-premium-ar-not-supported';
            message.style.cssText = `
                position: absolute;
                bottom: 16px;
                right: 16px;
                background: rgba(0,0,0,0.8);
                color: white;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 14px;
                z-index: 10;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            `;
            message.textContent = 'AR not supported on this device';
            container.appendChild(message);
        }

        message.style.display = 'block';

        // Auto-hide after 5 seconds
        setTimeout(function() {
            if (message && message.parentElement) {
                message.style.display = 'none';
            }
        }, 5000);
    }

    /**
     * Hide AR not supported message
     * 
     * @param {HTMLElement} modelViewer The model-viewer element
     */
    function hideARNotSupportedMessage(modelViewer) {
        const container = modelViewer.parentElement;
        if (!container) return;

        const message = container.querySelector('.explorexr-premium-ar-not-supported');
        if (message) {
            message.style.display = 'none';
        }
    }

    /**
     * Re-evaluate AR status for dynamically added model viewers
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
            
            if (hasNewModelViewers) {
                // Use timeout to ensure components are fully rendered
                setTimeout(processAllModelViewers, 500);
            }
        });
        
        // Start observing the document for added model viewers
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Make functions globally available for other modules
    window.explorexrARActivationControl = {
        processModelViewerAR: processModelViewerAR,
        disableARCompletely: disableARCompletely,
        disableARForUnsupportedDevice: disableARForUnsupportedDevice,
        enableARWithSupport: enableARWithSupport
    };

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initARActivationControl();
        observeNewModelViewers();
    });

    // Also listen for custom events from other ExploreXR modules
    document.addEventListener('explorexr-premium-model-added', function() {
        setTimeout(processAllModelViewers, 500);
    });

})();
