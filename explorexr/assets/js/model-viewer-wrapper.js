/**
 * ExpoXR Model Viewer Wrapper
 * Handles the loading UI and initialization of 3D model viewers
 * 
 * Free Version Features:
 * - Basic 3D model viewing
 * - Simple camera controls
 * - Single animation support
 * - Loading options (bar, percentage, or both)
 * - Model poster support
 */

// Helper function to check if debug logging is enabled
function expoxrDebugLog(message, ...args) {
    if (typeof expoxrLoadingOptions !== 'undefined' && expoxrLoadingOptions.debug_mode) {
        console.log(message, ...args);
    }
}

function expoxrDebugWarn(message, ...args) {
    if (typeof expoxrLoadingOptions !== 'undefined' && expoxrLoadingOptions.debug_mode) {
        console.warn(message, ...args);
    }
}

// Function to initialize all model viewers
function initExpoXRModelViewers() {
    // Log loaded configuration (only if debug mode is enabled)
    if (typeof expoxrLoadingOptions !== 'undefined') {
        expoxrDebugLog('[ExpoXR] Loading with options:', expoxrLoadingOptions);
    } else {
        expoxrDebugLog('[ExpoXR] No global loading options found, using defaults');
    }

    // Target all model-viewer elements, not just ones with the expoxr-model class
    // This ensures all existing models get the new loading UI
    const modelViewers = document.querySelectorAll('model-viewer');
      // AR feature detection - simplified for free version, full support in AR add-on
    const isARSupported = () => {
        // Check if AR add-on's feature detection is available
        if (window.expoxrARFeatures && typeof window.expoxrARFeatures.isARSupported === 'function') {
            // Use AR add-on's feature detection
            return window.expoxrARFeatures.isARSupported();
        }
        
        // Fallback basic detection if AR add-on is not present
        return {
            webXR: false,      // AR support - requires AR add-on
            sceneViewer: false, // AR support - requires AR add-on
            quickLook: false,   // AR support - requires AR add-on
            anySupported: false, // AR support - requires AR add-on
            isAndroid: /android/i.test(navigator.userAgent),
            isiOS: /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream
        };
    };// Get AR support info
    const arSupport = isARSupported();
    
    modelViewers.forEach(function(modelViewer, index) {
        // Make sure model viewer is in a container
        let container = modelViewer.parentElement;
        
        // If the parent isn't a proper container, wrap the model-viewer in one
        if (!container.classList.contains('expoxr-model-viewer-container')) {
            // Check if model viewer is already in our container structure
                if (!modelViewer.closest('.expoxr-model-viewer-container')) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'expoxr-model-viewer-container';
                    
                    // Preserve any inline styles and classes from the model-viewer
                    const modelViewerStyle = modelViewer.getAttribute('style');
                    const modelViewerClasses = modelViewer.getAttribute('class') || '';
                    
                    // Ensure the model-viewer has the expoxr-model class for future reference
                    if (!modelViewerClasses.includes('expoxr-model')) {
                        modelViewer.classList.add('expoxr-model');
                    }
                    
                    // Add error event listener
                    modelViewer.addEventListener('error', function(e) {
                        const errorType = e.detail.type || 'unknown';
                        const errorDetails = e.detail.sourceError ? e.detail.sourceError.message : 'Unknown error';
                        const friendlyMessage = typeof getUserFriendlyModelError === 'function' ? 
                            getUserFriendlyModelError(errorType, errorDetails) :                        'Error loading 3D model: ' + errorType;
                    
                    expoxrDebugWarn('ExpoXR Model Issue:', errorType, errorDetails);
                        
                        // Display user-friendly error message
                        const errorContainer = document.createElement('div');
                        errorContainer.className = 'expoxr-model-error';
                        errorContainer.innerHTML = '<p>' + friendlyMessage + '</p>';
                        wrapper.appendChild(errorContainer);
                    });
                    
                    // Insert wrapper before model viewer in the DOM
                    modelViewer.parentNode.insertBefore(wrapper, modelViewer);
                    
                    // Move model viewer into wrapper
                    wrapper.appendChild(modelViewer);
                    
                    // Update container reference
                    container = wrapper;
                }
            }
              // Free Version: Set default camera orbit if camera-controls is enabled
            if (modelViewer.hasAttribute('camera-controls') && !modelViewer.hasAttribute('camera-orbit')) {
                modelViewer.setAttribute('camera-orbit', '0deg 75deg 105%');
            }
            
            // Don't automatically add auto-rotate unless explicitly set
            // This respects user settings and prevents conflicts
            
            // Set up animation - Free version supports a single animation
            if (modelViewer.hasAttribute('animation-name')) {
                // Single animation is already set up, ensure autoplay works
                if (!modelViewer.hasAttribute('autoplay')) {
                    modelViewer.setAttribute('autoplay', '');
                }
            }
            
            // Setup loading UI using the official model-viewer approach
            setupLoadingUI(modelViewer, container, index);
        });
    }
    
    /**
     * Sets up the loading UI for a model viewer based on the official model-viewer approach
     * @param {HTMLElement} modelViewer - The model-viewer element
     * @param {HTMLElement} container - The container element
     * @param {number} index - Index for debugging purposes
     */
    function setupLoadingUI(modelViewer, container, index) {
        expoxrDebugLog('[ExpoXR] Setting up loading UI for model #' + (index + 1));
        
        // 1. Ensure the model-viewer element has the proper loading attributes
        if (!modelViewer.hasAttribute('loading')) {
            modelViewer.setAttribute('loading', 'eager');
        }
        
        if (!modelViewer.hasAttribute('reveal')) {
            modelViewer.setAttribute('reveal', 'interaction');
        }
        
        // Get loading settings from global options or data attributes
        let loadingDisplay = 'bar';
        
        // Check if global options are available (from WordPress)
        if (typeof expoxrLoadingOptions !== 'undefined') {
            expoxrDebugLog('[ExpoXR] Loading options found:', expoxrLoadingOptions);
            loadingDisplay = expoxrLoadingOptions.loading_type || 'both';
        }
        
        // Data attributes can override global settings
        if (modelViewer.dataset.loadingDisplay) {
            loadingDisplay = modelViewer.dataset.loadingDisplay;
        }
        
        const loadingBarColor = modelViewer.dataset.loadingBarColor || 
                               (typeof expoxrLoadingOptions !== 'undefined' ? expoxrLoadingOptions.loading_color : '#1e88e5');
        const loadingBarSize = modelViewer.dataset.loadingBarSize || 'medium';
        const loadingBarPosition = modelViewer.dataset.loadingBarPosition || 'middle';
        const percentageFontSize = modelViewer.dataset.percentageFontSize || 24;
        const percentageFontFamily = modelViewer.dataset.percentageFontFamily || 'Arial, sans-serif';
        const percentageFontColor = modelViewer.dataset.percentageFontColor || '#333333';
        const percentagePosition = modelViewer.dataset.percentagePosition || 'center-center';
        const overlayColor = modelViewer.dataset.overlayColor || '#FFFFFF';
        const overlayOpacity = modelViewer.dataset.overlayOpacity || 0.8;
        
        // 2. Create a poster slot that will be shown before the model loads
        let posterElement = modelViewer.querySelector('[slot="poster"]');
        
        if (!posterElement) {
            posterElement = document.createElement('div');
            posterElement.setAttribute('slot', 'poster');
            posterElement.className = 'expoxr-model-poster';
            posterElement.style.width = '100%';
            posterElement.style.height = '100%';
            
            // Add poster background if specified
            if (modelViewer.hasAttribute('poster')) {
                const posterUrl = modelViewer.getAttribute('poster');
                posterElement.style.backgroundImage = `url(${posterUrl})`;
                posterElement.style.backgroundSize = 'contain';
                posterElement.style.backgroundPosition = 'center';
                posterElement.style.backgroundRepeat = 'no-repeat';
                
                // Add fade-in animation for the poster
                posterElement.style.opacity = '0';
                posterElement.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    posterElement.style.opacity = '1';
                }, 50);
            }
                  // Create poster content - this will show the loading UI
        const posterContent = document.createElement('div');
        posterContent.className = 'expoxr-loading-container';
        posterContent.style.backgroundColor = hexToRgba(overlayColor, overlayOpacity);
        posterContent.style.position = 'absolute';
        posterContent.style.top = '0';
        posterContent.style.left = '0';
        posterContent.style.width = '100%';
        posterContent.style.height = '100%';
        posterContent.style.display = 'flex';
        posterContent.style.flexDirection = 'column';
        posterContent.style.alignItems = 'center';
        posterContent.style.justifyContent = 'center';
        posterElement.appendChild(posterContent);
            
            // Create loading status text
            const loadingStatusText = document.createElement('div');
            loadingStatusText.className = 'loading-status-text';
            loadingStatusText.textContent = 'Loading 3D Model...';
            loadingStatusText.style.fontSize = '16px';
            loadingStatusText.style.fontFamily = 'Arial, sans-serif';
            loadingStatusText.style.color = '#333333';
            
            // Position the loading text based on user preference
            loadingStatusText.style.position = 'absolute';
            
            // Apply positioning based on the position setting
            const textPositionParts = 'top-center'.split('-');
            const textVerticalPosition = textPositionParts[0]; // top, center, bottom
            const textHorizontalPosition = textPositionParts[1]; // left, center, right
            
            // Set vertical position
            if (textVerticalPosition === 'top') {
                loadingStatusText.style.top = '5%';
                loadingStatusText.style.bottom = 'auto';
            } else if (textVerticalPosition === 'center') {
                loadingStatusText.style.top = '50%';
                loadingStatusText.style.bottom = 'auto';
                loadingStatusText.style.transform = (loadingStatusText.style.transform || '') + ' translateY(-50%)';
            } else if (textVerticalPosition === 'bottom') {
                loadingStatusText.style.bottom = '5%';
                loadingStatusText.style.top = 'auto';
            }
            
            // Set horizontal position
            if (textHorizontalPosition === 'left') {
                loadingStatusText.style.left = '5%';
                loadingStatusText.style.right = 'auto';
                loadingStatusText.style.textAlign = 'left';
            } else if (textHorizontalPosition === 'center') {
                loadingStatusText.style.left = '50%';
                loadingStatusText.style.right = 'auto';
                loadingStatusText.style.transform = (loadingStatusText.style.transform || '') + ' translateX(-50%)';
                loadingStatusText.style.textAlign = 'center';
            } else if (textHorizontalPosition === 'right') {
                loadingStatusText.style.right = '5%';
                loadingStatusText.style.left = 'auto';
                loadingStatusText.style.textAlign = 'right';
            }
            
            // Adjust transform property based on combined positioning
            if (textVerticalPosition === 'center' && textHorizontalPosition === 'center') {
                loadingStatusText.style.transform = 'translate(-50%, -50%)';
            }
            
            posterContent.appendChild(loadingStatusText);
                  // Create the loading elements based on preference
        if (loadingDisplay === 'bar' || loadingDisplay === 'both') {
            expoxrDebugLog('[ExpoXR] Adding loading bar with color:', loadingBarColor);
            
            // Add loading bar
            const barContainer = document.createElement('div');
            barContainer.className = 'expoxr-loading-bar-container';
                
                // Apply position based on settings
                barContainer.style.position = 'absolute';
                barContainer.style.left = '5%';
                barContainer.style.right = '5%';
                barContainer.style.width = '90%';
                
                // Set vertical position based on loadingBarPosition
                if (loadingBarPosition === 'top') {
                    barContainer.style.top = '10%';
                    barContainer.style.bottom = 'auto';
                } else if (loadingBarPosition === 'middle') {
                    barContainer.style.top = '50%';
                    barContainer.style.bottom = 'auto';
                    barContainer.style.transform = 'translateY(-50%)';
                } else if (loadingBarPosition === 'bottom') {
                    barContainer.style.bottom = '10%';
                    barContainer.style.top = 'auto';
                }
                
                // Set bar size
                let barHeight = '8px';
                if (loadingBarSize === 'small') {
                    barHeight = '4px';
                } else if (loadingBarSize === 'large') {
                    barHeight = '12px';
                }
                
                const bar = document.createElement('div');
                bar.className = 'expoxr-loading-bar';
                bar.style.backgroundColor = loadingBarColor;
                bar.style.height = barHeight;
                bar.style.width = '0%';
                bar.style.borderRadius = (parseInt(barHeight) / 2) + 'px';
                
                barContainer.appendChild(bar);
                posterContent.appendChild(barContainer);
                
                // Store references for later updates
                posterContent.barContainer = barContainer;
                posterContent.loadingBar = bar;
            }
                  if (loadingDisplay === 'percentage' || loadingDisplay === 'both') {
            expoxrDebugLog('[ExpoXR] Adding percentage counter');
            
            // Add percentage counter
            const percentageCounter = document.createElement('div');
            percentageCounter.className = 'expoxr-percentage-counter';
            percentageCounter.textContent = '0%';percentageCounter.style.fontSize = `${percentageFontSize}px`;
                percentageCounter.style.fontFamily = percentageFontFamily;
                percentageCounter.style.color = percentageFontColor;
                percentageCounter.style.fontWeight = 'bold';
                percentageCounter.style.zIndex = '10';
                
                // Position the counter based on user preference
                percentageCounter.style.position = 'absolute';
                
                // Apply positioning based on the position setting (top, center, bottom)
                const positionParts = percentagePosition.split('-');
                const verticalPosition = positionParts[0]; // top, center, bottom
                const horizontalPosition = positionParts[1]; // left, center, right
                
                // Set vertical position
                if (verticalPosition === 'top') {
                    percentageCounter.style.top = '10%';
                    percentageCounter.style.bottom = 'auto';
                } else if (verticalPosition === 'center') {
                    percentageCounter.style.top = '50%';
                    percentageCounter.style.bottom = 'auto';
                    percentageCounter.style.transform = (percentageCounter.style.transform || '') + ' translateY(-50%)';
                } else if (verticalPosition === 'bottom') {
                    percentageCounter.style.bottom = '10%';
                    percentageCounter.style.top = 'auto';
                }
                
                // Set horizontal position
                if (horizontalPosition === 'left') {
                    percentageCounter.style.left = '10%';
                    percentageCounter.style.right = 'auto';
                } else if (horizontalPosition === 'center') {
                    percentageCounter.style.left = '50%';
                    percentageCounter.style.right = 'auto';
                    percentageCounter.style.transform = (percentageCounter.style.transform || '') + ' translateX(-50%)';
                } else if (horizontalPosition === 'right') {
                    percentageCounter.style.right = '10%';
                    percentageCounter.style.left = 'auto';
                }
                
                // Adjust transform property based on combined positioning
                if (verticalPosition === 'center' && horizontalPosition === 'center') {
                    percentageCounter.style.transform = 'translate(-50%, -50%)';
                }
                
                posterContent.appendChild(percentageCounter);
                
                // Store reference for later updates
                posterContent.percentageCounter = percentageCounter;
            }
            
            // Add the poster element to the model-viewer
            modelViewer.appendChild(posterElement);
            
            // Store a reference to the loading container
            modelViewer.loadingContainer = posterContent;
        }
        
        // 3. Add progress event listener
        modelViewer.addEventListener('progress', function(event) {
            const progress = event.detail.totalProgress * 100;
            // Round to the nearest 25% increment (0%, 25%, 50%, 75%, 100%)
            const roundedProgress = Math.round(event.detail.totalProgress * 4) * 25;
            
            expoxrDebugLog('[ExpoXR] Model #' + (index + 1) + ' loading progress: ' + roundedProgress + '%');
            
            const loadingContainer = modelViewer.loadingContainer;
            if (!loadingContainer) return;
            
            // Update loading bar
            if (loadingContainer.loadingBar) {
                loadingContainer.loadingBar.style.width = roundedProgress + '%';
            }
            
            // Update percentage counter
            if (loadingContainer.percentageCounter) {
                loadingContainer.percentageCounter.textContent = roundedProgress + '%';
            }
            
            // When progress is complete, the model-viewer will handle hiding the poster
            if (progress >= 100) {
                expoxrDebugLog('[ExpoXR] Model #' + (index + 1) + ' loading complete');
            }
        });        // 4. Add load event listener for seamless transition
        modelViewer.addEventListener('load', function() {
            expoxrDebugLog('[ExpoXR] Model #' + (index + 1) + ' loaded successfully');
            
            // Mark model as loaded
            modelViewer.setAttribute('data-loaded', 'true');
            
            // Force poster to be hidden after load is complete
            setTimeout(function() {
                // First, dismiss the poster using the official API
                if (typeof modelViewer.dismissPoster === 'function') {
                    modelViewer.dismissPoster();
                }
                
                // Additional fallback: manually hide the poster element with CSS classes
                const posterSlot = modelViewer.querySelector('[slot="poster"]');
                if (posterSlot) {
                    posterSlot.classList.add('poster-hidden');
                }
                
                // Hide loading container with CSS class
                if (modelViewer.loadingContainer) {
                    modelViewer.loadingContainer.classList.add('loading-hidden');
                }
                
                // Ensure the model is visible
                modelViewer.style.opacity = '1';
                
                // Remove poster element completely after animation
                setTimeout(() => {
                    if (posterSlot && posterSlot.classList.contains('poster-hidden')) {
                        posterSlot.style.display = 'none';
                    }
                }, 500);
            }, 100);
        });
        
        // 5. Add error event listener
        modelViewer.addEventListener('error', function(event) {
            expoxrDebugWarn('[ExpoXR] Model #' + (index + 1) + ' encountered an issue:', event.detail.type);
            
            // Update loading UI to show error
            const loadingContainer = modelViewer.loadingContainer;
            if (loadingContainer) {
                const loadingStatusText = loadingContainer.querySelector('.loading-status-text');
                if (loadingStatusText) {
                    loadingStatusText.textContent = 'Unable to display 3D model';
                    loadingStatusText.style.color = 'red';
                }
            }
        });        // Add click event to poster to properly dismiss it
        posterElement.addEventListener('click', function() {
            expoxrDebugLog('[ExpoXR] Poster clicked for model #' + (index + 1));
            
            // Use the official dismissPoster method if available
            if (typeof modelViewer.dismissPoster === 'function') {
                modelViewer.dismissPoster();
            }
            
            // Mark model as loaded and visible
            modelViewer.setAttribute('data-loaded', 'true');
            
            // Hide poster with CSS class for smooth animation
            posterElement.classList.add('poster-hidden');
            
            // Hide loading container
            if (modelViewer.loadingContainer) {
                modelViewer.loadingContainer.classList.add('loading-hidden');
            }
            
            // Ensure model is visible
            modelViewer.style.opacity = '1';
            
            // Remove poster from DOM after animation completes
            setTimeout(() => {
                posterElement.style.display = 'none';
            }, 500);
        });
        
        // Initialize model with load-on-demand behavior if specified
        if (modelViewer.hasAttribute('load-on-demand') || modelViewer.classList.contains('load-on-demand')) {
            // Hide model initially
            modelViewer.style.opacity = '0';
            
            // Create a load button
            const loadButton = document.createElement('button');
            loadButton.className = 'expoxr-load-model-btn';
            loadButton.textContent = modelViewer.getAttribute('load-button-text') || 'Load 3D Model';
            posterElement.appendChild(loadButton);
            
            // Set click handler for load button
            loadButton.addEventListener('click', function() {
                // Hide the load button
                loadButton.style.display = 'none';
                
                // Set the src attribute to start loading
                if (modelViewer.hasAttribute('data-src') && !modelViewer.getAttribute('src')) {
                    const modelSrc = modelViewer.getAttribute('data-src');
                    modelViewer.setAttribute('src', modelSrc);
                }
                  // Show loading UI
                const loadingContainer = modelViewer.loadingContainer;
                if (loadingContainer) {
                    loadingContainer.style.display = 'flex';
                }            });
        }
    }

/**
 * Convert hex color to rgba
 * @param {string} hex - Hex color code
 * @param {number} opacity - Opacity value
 * @returns {string} RGBA color string
 */
function hexToRgba(hex, opacity) {
        // Default fallback color
        if (!hex) return `rgba(255, 255, 255, ${opacity})`;
        
        // Remove hash if present
        hex = hex.replace('#', '');
        
        // Handle shorthand hex
        if (hex.length === 3) {
            hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        }
          // Parse hex to rgb
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);          // Return rgba
        return `rgba(${r}, ${g}, ${b}, ${opacity})`;
    }

/**
 * Function to load a 3D model dynamically (used by large model template)
 * @param {string} modelInstanceId - Unique ID for the model instance
 * @param {string} modelFileUrl - URL to the 3D model file
 * @param {Object} modelAttributes - JSON object containing model attributes
 */
function loadExpoXRModel(modelInstanceId, modelFileUrl, modelAttributes) {
        expoxrDebugLog(`[ExpoXR] Loading model dynamically: ${modelInstanceId}`);
        
        // Hide poster container and show viewer container
        const posterContainer = document.getElementById(`${modelInstanceId}-poster`);
        const viewerContainer = document.getElementById(`${modelInstanceId}-viewer`);
        
        if (posterContainer && viewerContainer) {
            // Hide poster with animation
            posterContainer.style.opacity = '0';
            setTimeout(() => {
                posterContainer.style.display = 'none';
                viewerContainer.style.display = 'block';
                
                // Create model-viewer element
                const modelViewer = document.createElement('model-viewer');
                
                // Set required attributes
                modelViewer.setAttribute('src', modelFileUrl);
                modelViewer.setAttribute('id', `${modelInstanceId}-model`);
                modelViewer.classList.add('expoxr-model');
                
                // Set default attributes if not provided
                if (!modelAttributes.hasOwnProperty('camera-controls')) {
                    modelViewer.setAttribute('camera-controls', '');
                }
                
                if (!modelAttributes.hasOwnProperty('camera-orbit')) {
                    modelViewer.setAttribute('camera-orbit', '0deg 75deg 105%');
                }
                
                if (!modelAttributes.hasOwnProperty('loading')) {
                    modelViewer.setAttribute('loading', 'eager');
                }
                
                if (!modelAttributes.hasOwnProperty('reveal')) {
                    modelViewer.setAttribute('reveal', 'interaction');
                }
                
                // Set width and height to 100% to fill container
                modelViewer.style.width = '100%';
                modelViewer.style.height = '100%';
                
                // Apply all provided model attributes
                for (const [key, value] of Object.entries(modelAttributes)) {
                    if (value !== null && value !== undefined) {
                        modelViewer.setAttribute(key, value);
                    }
                }
                
                // Create wrapper container if needed
                const wrapper = document.createElement('div');
                wrapper.className = 'expoxr-model-viewer-container';
                wrapper.style.width = '100%';
                wrapper.style.height = '100%';
                
                // Add error event listener
                modelViewer.addEventListener('error', function(e) {
                    const errorType = e.detail.type || 'unknown';
                    const errorDetails = e.detail.sourceError ? e.detail.sourceError.message : 'Unknown error';
                    const friendlyMessage = typeof getUserFriendlyModelError === 'function' ? 
                        getUserFriendlyModelError(errorType, errorDetails) :                        'Error loading 3D model: ' + errorType;
                    
                    expoxrDebugWarn('[ExpoXR] Model Issue:', errorType, errorDetails);
                    
                    // Display user-friendly error message
                    const errorContainer = document.createElement('div');
                    errorContainer.className = 'expoxr-model-error';
                    errorContainer.innerHTML = '<p>' + friendlyMessage + '</p>';
                    wrapper.appendChild(errorContainer);
                });
                
                // Append model viewer to wrapper
                wrapper.appendChild(modelViewer);
                
                // Append wrapper to viewer container
                viewerContainer.appendChild(wrapper);
                
                // Initialize loading UI
                setupLoadingUI(modelViewer, wrapper, 0);
                
                // Make viewer container visible with animation
                setTimeout(() => {
                    viewerContainer.style.opacity = '1';
                    
                    // Dispatch event to notify that model has been added
                    document.dispatchEvent(new CustomEvent('expoxr-model-added', {
                        detail: {
                            modelId: modelInstanceId,
                            modelElement: modelViewer,
                            timestamp: Date.now()
                        }
                    }));                    // Log for debugging
                    if (window.expoxrDebug) {
                        console.log(`[ExpoXR Debug] Model ${modelInstanceId} dynamically loaded:`, modelViewer);
                    }
                }, 100);
            }, 300);        } else {
            expoxrDebugWarn(`[ExpoXR] Warning: Could not find containers for model ${modelInstanceId}`);
        }    }

// Make loadExpoXRModel available globally for large model template
window.loadModelContent = loadExpoXRModel;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Use centralized loader to ensure model-viewer is available
    if (window.loadModelViewer && !window.isModelViewerLoaded()) {
        const scriptConfig = window.expoxrScriptConfig || {};
        window.loadModelViewer(scriptConfig)
            .then(function() {
                console.log('ExpoXR: Model viewer loaded via centralized manager');
                initExpoXRModelViewers();
            })
            .catch(function(error) {
                console.warn('ExpoXR: Model viewer could not be loaded, models will show in fallback mode.');
                // Initialize anyway to show error state
                initExpoXRModelViewers();
            });
    } else {
        // Initialize all model viewers with our custom loading UI
        initExpoXRModelViewers();
    }
});

// Also listen for the model-viewer script loaded event from preloader
document.addEventListener('expoxr-model-viewer-ready', function(event) {
    console.log('ExpoXR: Model viewer ready event received at ' + new Date(event.detail.timestamp).toISOString());
    
    // Re-initialize to ensure any delayed content is processed
    setTimeout(function() {
        initExpoXRModelViewers();
    }, 300);
});

// Also initialize when new content might be loaded via AJAX
document.addEventListener('expoxr-model-added', function() {
    initExpoXRModelViewers();
});

// Set up a mutation observer to detect newly added model viewers
const observer = new MutationObserver(function(mutations) {
    let shouldInit = false;
    
    mutations.forEach(function(mutation) {
        if (mutation.type === 'childList') {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeName && node.nodeName.toLowerCase() === 'model-viewer') {
                    shouldInit = true;
                } else if (node.querySelectorAll) {
                    const modelViewers = node.querySelectorAll('model-viewer');
                    if (modelViewers.length > 0) {
                        shouldInit = true;
                    }
                }
            });
        }
    });
    
    if (shouldInit) {
        initExpoXRModelViewers();
    }
});

// Start observing the document for added model-viewer elements
observer.observe(document.body, { childList: true, subtree: true });