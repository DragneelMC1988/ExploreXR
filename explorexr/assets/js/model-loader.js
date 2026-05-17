/**
 * ExploreXR Model Loader
 * 
 * Handles loading and displaying 3D models with basic functionality.
 */
(function($) {
    'use strict';

    const MODEL_VIEWER_ANIMATION_RETRY_LIMIT = 50;
    const overlayObservers = [];
    let overlayCleanupObserver = null;

    // Initialize when DOM is ready
    $(document).ready(function() {
        initializeModelViewers();
        setupAnimationControls();
    });

    /**
     * Initialize all model viewer elements
     */
    function initializeModelViewers() {
        const modelViewers = document.querySelectorAll('model-viewer.explorexr-model');

        if ('IntersectionObserver' in window) {
            const viewerObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    initializeSingleModelViewer(entry.target);
                    observer.unobserve(entry.target);
                });
            }, { rootMargin: '200px 0px', threshold: 0.01 });

            modelViewers.forEach(function(modelViewer) {
                if (modelViewer.hasAttribute('data-explorexr-core-initialized')) {
                    return;
                }

                viewerObserver.observe(modelViewer);
            });

            window.addEventListener('pagehide', function() {
                viewerObserver.disconnect();
            }, { once: true });

            return;
        }

        modelViewers.forEach(function(modelViewer) {
            initializeSingleModelViewer(modelViewer);
        });
    }

    function initializeSingleModelViewer(modelViewer) {
        if (!modelViewer || modelViewer.hasAttribute('data-explorexr-core-initialized')) {
            return;
        }

        modelViewer.setAttribute('data-explorexr-core-initialized', 'true');
        modelViewer.addEventListener('load', onModelLoad);
        modelViewer.addEventListener('error', onModelError);
        setupBasicCameraControls(modelViewer);

        if (modelViewer.getAttribute('poster')) {
            setupModelPoster(modelViewer);
        }

        if (modelViewer.loaded) {
            onModelLoad({ target: modelViewer });
        }
    }

    /**
     * Set up basic camera controls for a model viewer
     */
    function setupBasicCameraControls(modelViewer) {
        // Do not override camera-orbit or field-of-view here.
        // The shortcode sets camera-orbit from post meta when explicitly configured,
        // and model-viewer auto-computes the optimal FOV for each model.
        // Forcing defaults here overrides both user settings and the per-model
        // auto-framing that model-viewer performs after the scene loads.
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
            ExploreXRLogger.log('ExploreXR: model error suppressed by notification settings', 'info');
            return;
        }

        // Check if the error is a 404 (file not found)
        let errorMessage = 'Unable to display 3D model';
        let troubleshooting = '';

        ExploreXRLogger.log('ExploreXR: model display issue', 'warn');

        // If we have source error details, use them
        if (event.detail && event.detail.sourceError) {
            ExploreXRLogger.log('ExploreXR: source error — ' + event.detail.sourceError, 'warn');
            
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
        errorContainer.className = 'explorexr-model-error';
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
            <div class="explorexr-model-error-icon" style="font-size: 48px; margin-bottom: 10px;">📦</div>
            <div class="explorexr-model-error-title" style="font-size: 18px; font-weight: 600; margin-bottom: 8px; color: #333;">${errorMessage}</div>
            ${troubleshooting ? `<div class="explorexr-model-error-details" style="font-size: 14px; margin-bottom: 15px;">${troubleshooting}</div>` : ''}
            <button class="explorexr-retry-load" style="
                background: #0073aa;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                margin-right: 10px;
                font-size: 14px;
            ">Try Again</button>
            <button class="explorexr-hide-error" style="
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
        const retryButton = errorContainer.querySelector('.explorexr-retry-load');
        retryButton.addEventListener('click', function() {
            const src = modelViewer.getAttribute('src');
            if (src) {
                // Force reload by adding a timestamp
                const separator = src.includes('?') ? '&' : '?';
                modelViewer.setAttribute('src', src + separator + 't=' + Date.now());
            }
        });
        
        // Add hide functionality
        const hideButton = errorContainer.querySelector('.explorexr-hide-error');
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
        const retryCount = parseInt(modelViewer.getAttribute('data-explorexr-animation-retries') || '0', 10);
        if (!modelViewer.availableAnimations) {
            if (retryCount >= MODEL_VIEWER_ANIMATION_RETRY_LIMIT) {
                if (window.explorexrDebug && window.explorexrDebug.enabled && typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('animation init timeout', 'warn');
                }
                return;
            }

            modelViewer.setAttribute('data-explorexr-animation-retries', String(retryCount + 1));
            setTimeout(() => checkAndSetupAnimations(modelViewer), 100);
            return;
        }

        modelViewer.removeAttribute('data-explorexr-animation-retries');
        
        // Check if the model has animations
        if (modelViewer.availableAnimations && modelViewer.availableAnimations.length > 0) {
            // Log animation availability for debugging (only if debug mode is enabled)
            if (window.exploreXRDebug && window.exploreXRDebug.enabled) {
            }
            
            // Note: Animation controls are available in ExploreXR Premium
            // The free version provides basic model viewing only
            
            // Trigger a custom event for premium features
            const event = new CustomEvent('explorexr-animations-detected', {
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
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.warn('createAnimationControls requires ExploreXR Premium. Upgrade to access animation controls.');
        }
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
        $(document).on('click', '.explorexr-animation-button', function() {
            // This is handled in the createAnimationControls function
        });
    }
    
    /**
     * Debug helper for troubleshooting
     * Usage in console: window.explorexrInspectModels()
     */
    window.explorexrInspectModels = function() {
        const models = document.querySelectorAll('model-viewer');

        const logger = (typeof ExploreXRLogger !== 'undefined')
            ? ExploreXRLogger
            : { log: function() {}, warn: function() {}, error: function() {} };
        
        if (models.length === 0) {
            logger.warn('❌ No model-viewer elements found on page');
            return;
        }
        
        logger.log(`✅ Found ${models.length} model-viewer element(s):`);
        
        models.forEach((model, index) => {
            logger.log(`Model #${index + 1}: ${model.id || '(no ID)'}`);
            
            // Basic info
            logger.log('📍 Source:', model.getAttribute('src') || '(no source)');
            logger.log('🎬 Animation:', model.getAttribute('animation-name') || 'none');
            logger.log('▶️  Autoplay:', model.hasAttribute('autoplay') ? 'YES' : 'NO');
            logger.log('🔁 Loop:', model.hasAttribute('loop') ? 'YES' : 'NO');
            
            // Debug config
            const debugConfig = model.getAttribute('data-debug-config');
            if (debugConfig) {
                try {
                    const config = JSON.parse(debugConfig);
                    logger.log('🐛 Debug Config:', config);
                } catch (e) {
                    logger.warn('⚠️  Debug config parse error:', e);
                }
            }
            
            // Animation attributes
            const animAttrs = {};
            Array.from(model.attributes).forEach(attr => {
                if (attr.name.includes('animation') || attr.name.includes('data-animation')) {
                    animAttrs[attr.name] = attr.value;
                }
            });
            if (Object.keys(animAttrs).length > 0) {
                logger.log('🎬 Animation Attributes:', animAttrs);
            }
            
            // Post-processing attributes
            const ppAttrs = {};
            Array.from(model.attributes).forEach(attr => {
                if (attr.name.startsWith('data-pp-')) {
                    ppAttrs[attr.name] = attr.value;
                }
            });
            if (Object.keys(ppAttrs).length > 0) {
                logger.log('📊 Post-Processing Attributes:', ppAttrs);
            } else if (model.hasAttribute('data-pp-enabled')) {
                logger.warn('⚠️  PP enabled but no effect attributes found');
            }
            
            // Model state
            logger.log('📦 Model Loaded:', model.loaded ? 'YES' : 'NO');
            if (model.availableAnimations && model.availableAnimations.length > 0) {
                logger.log('🎭 Available Animations:', model.availableAnimations);
            }
        });
        
        logger.log('\n💡 Tips:');
        logger.log('  - Check if animation-name matches availableAnimations');
        logger.log('  - Verify autoplay/loop are boolean attributes (no value)');
        logger.log('  - Ensure data-pp-enabled="true" if using post-processing');
        logger.log('  - Use WP_DEBUG to see data-debug-config details');
    };
    
    // Auto-run inspector when debug mode is explicitly requested via URL param.
    // Requires ?explorexr_debug=1 AND the debug flag to be active.
    if (window.location.search.includes('explorexr_debug=1') &&
        window.explorexrDebug && window.explorexrDebug.enabled) {
        $(document).ready(function() {
            setTimeout(function() {
                ExploreXRLogger.log('ExploreXR debug inspector active', 'info');
                window.explorexrInspectModels();
            }, 2000);
        });
    }

    /**
     * Overlay Position Manager
     * Moves addon overlays into shared position containers to prevent overlap.
     * Addons can call: window.explorexrRegisterOverlay(container, element, position)
     * Or add data-overlay-position="top-right" to elements within .ExploreXR-model-container
     */
    function registerOverlayObserver(container, observer) {
        overlayObservers.push({ container: container, observer: observer });
    }

    function cleanupDetachedOverlayObservers() {
        for (let i = overlayObservers.length - 1; i >= 0; i -= 1) {
            const entry = overlayObservers[i];
            if (!entry.container || !document.body.contains(entry.container)) {
                entry.observer.disconnect();
                overlayObservers.splice(i, 1);
            }
        }
    }

    function disconnectOverlayObservers() {
        overlayObservers.forEach(function(entry) {
            entry.observer.disconnect();
        });
        overlayObservers.length = 0;

        if (overlayCleanupObserver) {
            overlayCleanupObserver.disconnect();
            overlayCleanupObserver = null;
        }
    }

    function initOverlayManager() {
        var containers = document.querySelectorAll('.ExploreXR-model-container');
        containers.forEach(function(container) {
            var groups = {};
            container.querySelectorAll('.explorexr-overlay-group[data-position]').forEach(function(g) {
                groups[g.getAttribute('data-position')] = g;
            });

            // Find orphan overlays (addon elements with data-overlay-position)
            // Use a MutationObserver so dynamically-added overlays are also caught
            function moveOrphanOverlays() {
                container.querySelectorAll('[data-overlay-position]').forEach(function(el) {
                    var pos = el.getAttribute('data-overlay-position');
                    var group = groups[pos];
                    if (group && el.parentNode !== group) {
                        group.appendChild(el);
                    }
                });
            }

            // Run once for static content
            moveOrphanOverlays();

            // Watch for dynamically added overlays
            var observer = new MutationObserver(function(mutations) {
                var hasNewNodes = mutations.some(function(m) { return m.addedNodes.length > 0; });
                if (hasNewNodes) {
                    moveOrphanOverlays();
                }
            });
            observer.observe(container, { childList: true, subtree: true });
            registerOverlayObserver(container, observer);
        });

        if (!overlayCleanupObserver && document.body) {
            overlayCleanupObserver = new MutationObserver(function() {
                cleanupDetachedOverlayObservers();
            });
            overlayCleanupObserver.observe(document.body, { childList: true, subtree: true });
        }
    }

    // Expose a global API for addons to register overlays
    window.explorexrRegisterOverlay = function(containerEl, overlayEl, position) {
        if (!containerEl || !overlayEl || !position) return;
        var group = containerEl.querySelector('.explorexr-overlay-group[data-position="' + position + '"]');
        if (group) {
            overlayEl.removeAttribute('data-overlay-position');
            group.appendChild(overlayEl);
        }
    };

    // Init overlay manager after a short delay to let addons render
    $(document).ready(function() {
        setTimeout(initOverlayManager, 100);
    });

    window.addEventListener('pagehide', disconnectOverlayObservers);

})(jQuery);
