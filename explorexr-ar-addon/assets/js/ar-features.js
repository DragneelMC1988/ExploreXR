/**
 * ExploreXR AR Add-On - AR Feature Detection
 * 
 * Provides enhanced AR feature detection for model viewers
 */

(function() {
    'use strict';    // Check if AR is supported on the device
    function isARSupported() {
        // WebXR support check (for desktop and mobile browsers)
        const webXRSupported = 'xr' in navigator && 'isSessionSupported' in navigator.xr;

        // Device detection with iPadOS fix
        const isAndroid = /android/i.test(navigator.userAgent);

        // iOS detection: Include iPadOS which reports as "Macintosh" in UA
        // iPadOS 13+ identifies as Mac but has touch events
        const isiPhone = /iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        const isiPad = /iPad/.test(navigator.userAgent) && !window.MSStream;

        // Enhanced iPad Pro M1/M2 detection
        // These devices report as "Macintosh" with "MacIntel" platform but have touch support
        const isM1iPad = /Macintosh/.test(navigator.userAgent) &&
                         navigator.maxTouchPoints > 1 &&
                         navigator.platform === 'MacIntel';

        // Comprehensive iPadOS detection for all iPad models including M1/M2
        const isiPadOS = (/Macintosh/.test(navigator.userAgent) &&
                          navigator.maxTouchPoints &&
                          navigator.maxTouchPoints > 1) ||
                         isM1iPad ||
                         (/iPad/.test(navigator.userAgent));

        const isiOS = isiPhone || isiPad || isiPadOS;

        // Debug logging for device detection
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('ExploreXR AR Device Detection:', {
                userAgent: navigator.userAgent,
                platform: navigator.platform,
                maxTouchPoints: navigator.maxTouchPoints,
                isiPhone: isiPhone,
                isiPad: isiPad,
                isM1iPad: isM1iPad,
                isiPadOS: isiPadOS,
                isiOS: isiOS,
                isAndroid: isAndroid
            });
        }
        
        // Tablet detection (includes iPadOS and Android tablets)
        const isTablet = isiPad || isiPadOS || (/android/i.test(navigator.userAgent) && !/mobile/i.test(navigator.userAgent));
        
        // Mobile phone detection
        const isMobilePhone = (isiPhone || (/android/i.test(navigator.userAgent) && /mobile/i.test(navigator.userAgent))) && !isTablet;
        
        // Desktop: Not mobile, not tablet, not iOS
        const isDesktop = !isAndroid && !isiOS && !isTablet && !isMobilePhone;
        
        // Scene Viewer support check (Android only)
        const isSceneViewerSupported = isAndroid && 
            navigator.userAgent.includes('Chrome') && 
            parseInt((navigator.userAgent.match(/Chrome\/([0-9]+)/) || [0, 0])[1], 10) >= 79;
        
        // Quick Look support check (iOS only, including iPadOS)
        // iOS 12+ supports AR Quick Look
        const iosVersionMatch = navigator.userAgent.match(/OS (\d+)_/);
        const iosVersion = iosVersionMatch ? parseInt(iosVersionMatch[1], 10) : null;
        const safariVersionMatch = isiPadOS ? navigator.userAgent.match(/Version\/(\d+)/) : null;
        const safariMajor = safariVersionMatch ? parseInt(safariVersionMatch[1], 10) : null;
        const isQuickLookSupported = isiOS && (
            (iosVersion !== null && iosVersion >= 12) ||
            (iosVersion === null && isiPadOS && safariMajor !== null && safariMajor >= 12) ||
            (iosVersion === null && isiPadOS)
        );
        
        // Mobile/Tablet AR support
        const isMobileTabletARCapable = (isSceneViewerSupported || isQuickLookSupported) && (isMobilePhone || isTablet);
        
        // Desktop AR support (WebXR only, but don't show "not supported" message on desktop)
        const isDesktopARCapable = isDesktop && webXRSupported;
        
        // Determine if we should show "AR not supported" message
        // Show on mobile/tablet ONLY if no AR support at all
        // Never show on desktop
        const shouldShowNotSupported = (isMobilePhone || isTablet) && !isSceneViewerSupported && !isQuickLookSupported && !webXRSupported;
        
        // Return complete support information
        return {
            webXR: webXRSupported,
            sceneViewer: isSceneViewerSupported,
            quickLook: isQuickLookSupported,
            anySupported: webXRSupported || isSceneViewerSupported || isQuickLookSupported,
            isAndroid: isAndroid,
            isiOS: isiOS,
            isiPad: isiPad,
            isiPadOS: isiPadOS,
            isTablet: isTablet,
            isMobilePhone: isMobilePhone,
            isDesktop: isDesktop,
            isMobileOrTablet: isMobilePhone || isTablet,
            shouldShowNotSupported: shouldShowNotSupported
        };
    }    // Initialize AR features
    function initARFeatures() {
        // Get support information
        const arSupport = isARSupported();
        
        // Find all model viewers and check AR status
        const modelViewers = document.querySelectorAll('model-viewer');
        
        modelViewers.forEach(function(modelViewer) {
            // Check if this model has AR enabled
            const hasARAttribute = modelViewer.hasAttribute('ar');
            const isAREnabled = modelViewer.hasAttribute('data-explorexr-premium-ar-enabled') && 
                               modelViewer.getAttribute('data-explorexr-premium-ar-enabled') === 'true';
            
            if (hasARAttribute || isAREnabled) {
                if (arSupport.anySupported) {
                    // AR is supported, enhance the model viewer
                    enhanceARModelViewer(modelViewer, arSupport);
                } else {
                    // AR is not supported, disable AR functionality
                    disableARForModelViewer(modelViewer, arSupport);
                }
            }
        });
    }

    // Enhance a model viewer with AR features
    function enhanceARModelViewer(modelViewer, arSupport) {
        // Set appropriate AR modes based on device support
        let arModes = [];
        
        if (arSupport.webXR) {
            arModes.push('webxr');
        }
        
        if (arSupport.sceneViewer) {
            arModes.push('scene-viewer');
        }
        
        if (arSupport.quickLook) {
            arModes.push('quick-look');
        }
        
        // Only set AR modes if the model doesn't already have them
        if (arModes.length > 0 && !modelViewer.hasAttribute('ar-modes')) {
            modelViewer.setAttribute('ar-modes', arModes.join(' '));
        }
        
        // Set data attribute for AR support
        modelViewer.setAttribute('data-explorexr-premium-ar-supported', arSupport.anySupported);
          // Log AR support information
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('ExploreXR AR Add-on: Enhanced model viewer with AR support:', {
                webXR: arSupport.webXR, 
                sceneViewer: arSupport.sceneViewer, 
                quickLook: arSupport.quickLook
            });
        }
    }    // Disable AR for a model viewer when not supported
    function disableARForModelViewer(modelViewer, arSupport) {
        // Remove AR attribute and related attributes
        modelViewer.removeAttribute('ar');
        modelViewer.removeAttribute('ar-modes');
        modelViewer.removeAttribute('ar-scale');
        modelViewer.removeAttribute('ar-placement');
        modelViewer.removeAttribute('ios-src');
        
        // Set data attribute for AR support status
        modelViewer.setAttribute('data-explorexr-premium-ar-supported', 'false');
        
        // Remove any custom AR buttons
        const customARButtons = modelViewer.querySelectorAll('button[slot="ar-button"], button[data-explorexr-premium-ar-button]');
        customARButtons.forEach(function(button) {
            button.remove();
        });
        
        // Only show "AR not supported" message on mobile devices that truly don't support AR
        // Don't show this message on desktop/laptop computers
        if (arSupport && arSupport.shouldShowNotSupported) {
            const arNotSupportedMsg = modelViewer.parentElement.querySelector('.explorexr-premium-ar-not-supported');
            if (arNotSupportedMsg) {
                arNotSupportedMsg.style.display = 'block';
                
                // Auto-hide after 5 seconds
                setTimeout(function() {
                    if (arNotSupportedMsg) {
                        arNotSupportedMsg.style.display = 'none';
                    }
                }, 5000);
            }
        }
        
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('ExploreXR AR Add-on: Disabled AR for model viewer - device not supported');
        }
    }// Make AR features globally available
    window.explorexrARFeatures = {
        isARSupported: isARSupported,
        initARFeatures: initARFeatures,
        enhanceARModelViewer: enhanceARModelViewer,
        disableARForModelViewer: disableARForModelViewer
    };

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Use centralized loader to ensure model-viewer is available
        if (window.loadModelViewer && !window.isModelViewerLoaded()) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('AR Features: Waiting for model-viewer to load...');
            }
            window.loadModelViewer()
                .then(function() {
                    if (typeof ExploreXRLogger !== 'undefined') {
                        ExploreXRLogger.log('AR Features: Model viewer loaded, initializing AR features');
                    }
                    initARFeatures();
                    observeNewModelViewers();
                })
                .catch(function(error) {
                    if (typeof ExploreXRLogger !== 'undefined') {
                        ExploreXRLogger.error('AR Features: Failed to load model-viewer:', error);
                    }
                    // Fallback with delay
                    setTimeout(() => {
                        initARFeatures();
                        observeNewModelViewers();
                    }, 2000);
                });
        } else if (typeof customElements !== 'undefined' && customElements.get('model-viewer')) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('AR Features: Model viewer already available');
            }
            initARFeatures();
            observeNewModelViewers();
        } else {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('AR Features: Waiting for model-viewer to be available...');
            }
            // Fallback with delay
            setTimeout(() => {
                initARFeatures();
                observeNewModelViewers();
            }, 2000);
        }
    });

    // Listen for custom event when model viewers are added through AJAX
    document.addEventListener('explorexr-premium-model-added', function() {
        // Use a delay to ensure model viewer is fully loaded
        setTimeout(initARFeatures, 500);
    });

    // Observe DOM changes to detect dynamically added model viewers
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
                setTimeout(initARFeatures, 500);
            }
        });
        
        // Start observing the document for added model viewers
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }    // Make AR detection available globally
    window.explorexrARFeatures = {
        isARSupported: isARSupported,
        initARFeatures: initARFeatures,
        enhanceARModelViewer: enhanceARModelViewer,
        disableARForModelViewer: disableARForModelViewer
    };
})();
