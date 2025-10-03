/**
 * ExploreXR Model Viewer Preloader Script
 * Handles preloading UI before the model-viewer-umd.js script loads
 */
(function() {
    // State management for the preloader
    const state = {
        modelViewerContainers: [],
        scriptLoaded: false,
        estimatedProgress: 0,
        estimatedTotalSize: 1000000, // Estimate script size in bytes
        loadTimeout: null
    };

    /**
     * Initialize the preloader when DOM is ready
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Find all model-viewer elements and containers
        initPreloader();
    });    /**
     * Initialize the preloader for all model viewers
     */
    function initPreloader() {
        console.log('ModelViewerPreloader: Initializing');
        
        // Find all model viewers
        const modelViewers = document.querySelectorAll('model-viewer');
        const modelViewerContainers = Array.from(modelViewers).map(modelViewer => {
            return modelViewer.closest('.explorexr-model-viewer-container') || modelViewer.parentElement;
        });
        
        // Store for later use
        state.modelViewerContainers = modelViewerContainers;
        
        console.log('ModelViewerPreloader: Found ' + modelViewerContainers.length + ' model viewers');
        
        // Use the centralized loader manager
        if (!window.ExploreXRModelViewerLoader || !window.ExploreXRModelViewerLoader.isLoaded()) {
            showLoadingIndicators();
            
            // Use centralized loading if available
            if (window.loadModelViewer) {
                const scriptConfig = window.explorexrScriptConfig || {};
                window.loadModelViewer(scriptConfig)
                    .then(handleScriptLoaded)
                    .catch(error => {
                        console.error('ModelViewerPreloader: Script loading failed', error);
                        handleScriptLoaded();
                    });
            } else {
                // Fallback to direct loading
                loadModelViewerScript();
            }
        } else {
            console.log('ModelViewerPreloader: model-viewer already loaded');
            state.scriptLoaded = true;
        }
        
        // Create event listeners for script loading completion
        document.addEventListener('explorexr-script-loaded', handleScriptLoaded);
        
        // Set up a timeout just in case the script loading gets stuck
        state.loadTimeout = setTimeout(function() {
            if (!state.scriptLoaded) {
                console.warn('ModelViewerPreloader: Script loading timed out, forcing completion');
                handleScriptLoaded();
            }
        }, 10000); // 10 second timeout
    }
      /**
     * Load the model-viewer script (UMD or Module)
     */
    function loadModelViewerScript() {
        const attempts = window.modelViewerLoadAttempts || 0;
        window.modelViewerLoadAttempts = attempts + 1;
          console.log('ModelViewerPreloader: Loading model-viewer (attempt ' + window.modelViewerLoadAttempts + ')');
        
        // WordPress.org compliance: Always use local model-viewer file, no CDN fallback
        const scriptConfig = window.explorexrScriptConfig || {};
        const scriptUrl = scriptConfig.modelViewerScriptUrl || window.modelViewerScriptUrl || (window.explorexrPluginUrl + 'assets/js/model-viewer.min.js');
        const scriptType = scriptConfig.scriptType || 'module';
        
        const startTime = performance.now();
        
        // Create script element to load model-viewer
        const script = document.createElement('script');
        script.src = scriptUrl;
        
        // Set script type based on configuration
        if (scriptType === 'module') {
            script.type = 'module';
        }
        // UMD scripts don't need type="module"
        
        // Set up loading progress tracking with fetch for more accurate progress
        fetch(scriptUrl)
            .then(response => {
                const contentLength = response.headers.get('content-length');
                if (contentLength) {
                    state.estimatedTotalSize = parseInt(contentLength, 10);
                }
                
                const reader = response.body.getReader();
                let receivedLength = 0;
                
                return new ReadableStream({
                    start(controller) {
                        function read() {
                            reader.read().then(({ done, value }) => {
                                if (done) {
                                    controller.close();
                                    return;
                                }
                                
                                receivedLength += value.byteLength;
                                updateProgress(receivedLength, state.estimatedTotalSize);
                                
                                controller.enqueue(value);
                                read();
                            }).catch(error => {
                                console.error('ModelViewerPreloader: Error reading script', error);
                                controller.error(error);
                            });
                        }
                        
                        read();
                    }
                });
            })
            .catch(error => {
                console.error('ModelViewerPreloader: Error fetching script', error);
                // If fetch fails, we'll still try to load via script tag
            });
        
        // Event handlers for script element
        script.onload = function() {
            const loadTime = ((performance.now() - startTime) / 1000).toFixed(2);
            console.log('ModelViewerPreloader: Loaded in ' + loadTime + 's');
            
            // Dispatch event that script is loaded
            document.dispatchEvent(new CustomEvent('explorexr-script-loaded', {
                detail: { loadTime: loadTime }
            }));
        };
        
        script.onerror = function() {
            console.error('ModelViewerPreloader: Failed to load script');
            
            // If we have a fallback URL and haven't tried too many times
            if (window.modelViewerFallbackUrl && window.modelViewerLoadAttempts < 3) {
                console.log('ModelViewerPreloader: Trying fallback URL');
                window.modelViewerScriptUrl = window.modelViewerFallbackUrl;
                loadModelViewerScript();
            } else {
                // Show error in loading indicators
                state.modelViewerContainers.forEach(container => {
                    const loadingIndicator = container.querySelector('.model-loading-indicator');
                    if (loadingIndicator) {
                        const loadingStatusText = loadingIndicator.querySelector('.loading-status-text');
                        if (loadingStatusText) {
                            loadingStatusText.textContent = 'Failed to load 3D viewer';
                            loadingStatusText.style.color = 'red';
                        }
                    }
                });
            }
        };
        
        // Add to document
        document.head.appendChild(script);
    }
      /**
     * Handle the model-viewer script loaded event
     */
    function handleScriptLoaded() {
        console.log('ModelViewerPreloader: Script loaded successfully');
        
        // Prevent duplicate handling
        if (state.scriptLoaded) return;
        state.scriptLoaded = true;
        
        // Clear timeout
        if (state.loadTimeout) {
            clearTimeout(state.loadTimeout);
            state.loadTimeout = null;
        }
        
        // Set progress to 100% and force update UI
        updateProgress(state.estimatedTotalSize, state.estimatedTotalSize);
        
        // Dispatch a global event that script loading is complete
        document.dispatchEvent(new CustomEvent('explorexr-model-viewer-ready', {
            detail: { timestamp: Date.now() }
        }));
        
        // Small delay to ensure the model-viewer element is defined
        setTimeout(function() {
            hideLoadingIndicators();
        }, 300);
    }
    
    /**
     * Show loading indicators for all model viewers
     */
    function showLoadingIndicators() {
        state.modelViewerContainers.forEach(container => {
            // Get styling from config - these values come from the Loading Options page
            const loadingDisplay = config.loadingDisplay || 'bar';
            const loadingBarColor = config.loadingBarColor || '#0073aa';
            const loadingBarSize = config.loadingBarSize || 'medium';
            const loadingBarPosition = config.loadingBarPosition || 'middle';
            const percentageFontSize = config.percentageFontSize || 24;
            const percentageFontFamily = config.percentageFontFamily || 'Arial, sans-serif';
            const percentageColor = config.percentageFontColor || '#333333';
            const percentagePosition = config.percentagePosition || 'center-center';
            const overlayColor = config.overlayColor || '#FFFFFF';
            const overlayOpacity = config.overlayOpacity || 0.7;
            
            // Add model-loading-indicator for script loading phase
            let loadingIndicator = container.querySelector('.model-loading-indicator');
            if (!loadingIndicator) {
                loadingIndicator = document.createElement('div');
                loadingIndicator.className = 'model-loading-indicator';
                
                // Create loading status text to indicate what's being loaded
                const loadingStatusText = document.createElement('div');
                loadingStatusText.className = 'loading-status-text';
                loadingStatusText.textContent = 'Loading Script...';
                loadingIndicator.appendChild(loadingStatusText);
                
                // Add progress elements based on loading display setting
                if (loadingDisplay === 'bar' || loadingDisplay === 'both') {
                    // Script loading progress bar
                    const scriptProgressElement = document.createElement('div');
                    scriptProgressElement.className = 'script-loading-progress';
                    
                    const scriptProgressBar = document.createElement('div');
                    scriptProgressBar.className = 'script-loading-progress-bar';
                    scriptProgressBar.style.backgroundColor = loadingBarColor;
                    scriptProgressBar.style.height = getBarHeight(loadingBarSize);
                    
                    scriptProgressElement.appendChild(scriptProgressBar);
                    loadingIndicator.appendChild(scriptProgressElement);
                }
                
                if (loadingDisplay === 'percentage' || loadingDisplay === 'both') {
                    // Script loading percentage
                    const scriptPercentage = document.createElement('div');
                    scriptPercentage.className = 'script-loading-percentage';
                    scriptPercentage.style.fontSize = `${percentageFontSize}px`;
                    scriptPercentage.style.fontFamily = percentageFontFamily;
                    scriptPercentage.style.color = percentageColor;
                    scriptPercentage.textContent = '0%';
                    
                    loadingIndicator.appendChild(scriptPercentage);
                }
                
                // Apply custom overlay color and opacity if set
                if (overlayColor) {
                    loadingIndicator.style.backgroundColor = hexToRgba(overlayColor, overlayOpacity);
                }
                
                container.appendChild(loadingIndicator);
            }
            
            // Don't create model loading elements yet - those will be handled by model-viewer-wrapper.js
            // Just set a flag to indicate the script is loading
            container.dataset.scriptLoading = 'true';
        });
        
        // Initialize progress to 0%
        updateProgress(0, state.estimatedTotalSize);
    }
    
    /**
     * Hide loading indicators for all model viewers
     */
    function hideLoadingIndicators() {
        // Wait a moment to ensure model-viewer has initialized
        setTimeout(() => {
            state.modelViewerContainers.forEach(container => {
                // Hide the script loading indicator
                const loadingIndicator = container.querySelector('.model-loading-indicator');
                if (loadingIndicator) {
                    loadingIndicator.style.opacity = '0';
                    setTimeout(() => {
                        if (loadingIndicator.parentNode) {
                            loadingIndicator.parentNode.removeChild(loadingIndicator);
                        }
                    }, 500);
                }
                
                // Mark that script loading is complete
                container.dataset.scriptLoaded = 'true';
                container.dataset.scriptLoading = 'false';
                
                // Dispatch an event to signal script loading is complete
                container.dispatchEvent(new CustomEvent('scriptLoadComplete', {
                    bubbles: true,
                    detail: { container: container }
                }));
            });
        }, 300);
    }
    
    /**
     * Update progress indicators
     */
    function updateProgress(loaded, total) {
        state.estimatedProgress = Math.min(loaded / total, 0.99);
        const percent = Math.round(state.estimatedProgress * 100);
        
        // Update all progress bars and percentage counters
        state.modelViewerContainers.forEach(container => {
            // Update script loading progress bar
            const scriptProgressBar = container.querySelector('.script-loading-progress-bar');
            if (scriptProgressBar) {
                scriptProgressBar.style.width = `${percent}%`;
            }
            
            // Update script loading percentage counter
            const scriptPercentage = container.querySelector('.script-loading-percentage');
            if (scriptPercentage) {
                scriptPercentage.textContent = `${percent}%`;
            }
            
            // Store the current script loading progress in a data attribute
            container.dataset.scriptProgress = percent;
        });
        
        if (percent >= 100) {
            state.modelViewerContainers.forEach(container => {
                container.dataset.scriptLoaded = 'true';
                container.dataset.scriptProgress = '100';
                
                // Update loading status text to indicate completion
                const loadingStatusText = container.querySelector('.loading-status-text');
                if (loadingStatusText) {
                    loadingStatusText.textContent = 'Script Loaded Successfully';
                }
            });
        }
    }
    
    /**
     * Convert hex color to rgba
     */
    function hexToRgba(hex, opacity) {
        hex = hex.replace(/^#/, '');
        let r, g, b;
        
        if (hex.length === 3) {
            r = parseInt(hex.charAt(0) + hex.charAt(0), 16);
            g = parseInt(hex.charAt(1) + hex.charAt(1), 16);
            b = parseInt(hex.charAt(2) + hex.charAt(2), 16);
        } else {
            r = parseInt(hex.substring(0, 2), 16);
            g = parseInt(hex.substring(2, 4), 16);
            b = parseInt(hex.substring(4, 6), 16);
        }
        
        return `rgba(${r}, ${g}, ${b}, ${opacity})`;
    }
    
    /**
     * Get bar height based on size setting
     */
    function getBarHeight(size) {
        switch (size) {
            case 'small': return '4px';
            case 'large': return '12px';
            default: return '8px';
        }
    }
    
    // Default configuration
    const config = {
        loadingDisplay: 'both',
        loadingBarColor: '#0073aa',
        loadingBarSize: 'medium',
        loadingBarPosition: 'middle',
        percentageFontSize: 24,
        percentageFontFamily: 'Arial, sans-serif',
        percentageFontColor: '#333333',
        percentagePosition: 'center-center',
        overlayColor: '#FFFFFF',
        overlayOpacity: 0.8
    };
})();