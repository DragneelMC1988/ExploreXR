/**
 * ExpoXR Model Viewer Loading Manager
 * Prevents duplicate loading and coordinates multiple scripts attempting to load model-viewer
 */
(function() {
    'use strict';

    // Helper function to check if debug logging is enabled
    function expoxrDebugLog(message, ...args) {
        // Check if loading options are available and debug mode is enabled
        if (typeof expoxrLoadingOptions !== 'undefined' && expoxrLoadingOptions.debug_mode) {
            console.log(message, ...args);
        }
    }

    function expoxrDebugError(message, ...args) {
        // Always show errors, but check for debug mode for additional context
        if (typeof expoxrLoadingOptions !== 'undefined' && expoxrLoadingOptions.debug_mode) {
            console.error(message, ...args);
        }
    }

    // Global state management
    window.ExpoXRModelViewerLoader = window.ExpoXRModelViewerLoader || {
        state: 'unloaded', // 'unloaded', 'loading', 'loaded', 'error'
        loadingPromise: null,
        callbacks: [],
        loadAttempts: 0,
        maxAttempts: 3,
        scriptUrl: null,
        scriptType: 'umd',
          /**
         * Get the current loading state
         */
        getState: function() {
            // Check if model-viewer is already available
            if (window.customElements && window.customElements.get('model-viewer')) {
                this.state = 'loaded';
                return this.state;
            }
            
            // Check if model-viewer is in the process of being defined
            if (typeof window.customElements !== 'undefined') {
                try {
                    // Try to define a dummy element to check if 'model-viewer' is available
                    const TestElement = class extends HTMLElement {};
                    window.customElements.define('test-element-' + Date.now(), TestElement);
                } catch (e) {
                    // If any error occurs, continue with normal flow
                }
            }
            
            return this.state;
        },

        /**
         * Request to load model-viewer script
         * Returns a promise that resolves when script is loaded
         */
        load: function(config = {}) {
            // If already loaded, resolve immediately
            if (this.getState() === 'loaded') {
                expoxrDebugLog('ExpoXR ModelViewerLoader: Already loaded');
                return Promise.resolve();
            }

            // If currently loading, return the existing promise
            if (this.state === 'loading' && this.loadingPromise) {
                expoxrDebugLog('ExpoXR ModelViewerLoader: Loading in progress, waiting...');
                return this.loadingPromise;
            }

            // If too many attempts, reject
            if (this.loadAttempts >= this.maxAttempts) {
                expoxrDebugError('ExpoXR ModelViewerLoader: Max load attempts reached');
                return Promise.reject(new Error('Max load attempts reached'));
            }

            // Set up script configuration
            this.scriptUrl = config.scriptUrl || this.scriptUrl || this._getDefaultScriptUrl();
            this.scriptType = config.scriptType || this.scriptType || 'umd';

            expoxrDebugLog('ExpoXR ModelViewerLoader: Starting load attempt', this.loadAttempts + 1);
            
            // Create loading promise
            this.state = 'loading';
            this.loadAttempts++;
            
            this.loadingPromise = new Promise((resolve, reject) => {
                this._loadScript()
                    .then(() => {
                        // Verify the element is actually registered
                        if (window.customElements && window.customElements.get('model-viewer')) {
                            this.state = 'loaded';
                            this._notifyCallbacks(null);
                            expoxrDebugLog('ExpoXR ModelViewerLoader: Successfully loaded');
                            resolve();
                        } else {
                            const error = new Error('model-viewer element not registered after script load');
                            this.state = 'error';
                            this._notifyCallbacks(error);
                            reject(error);
                        }
                    })
                    .catch(error => {
                        expoxrDebugError('ExpoXR ModelViewerLoader: Load failed', error);
                        this.state = 'error';
                        this._notifyCallbacks(error);
                        reject(error);
                    });
            });

            return this.loadingPromise;
        },

        /**
         * Add a callback to be notified when loading completes
         */
        onLoad: function(callback) {
            if (this.getState() === 'loaded') {
                callback(null);
                return;
            }
            
            if (this.state === 'error') {
                callback(new Error('Unable to load 3D model viewer. Please refresh the page and try again.'));
                return;
            }

            this.callbacks.push(callback);
        },

        /**
         * Check if model-viewer is available
         */
        isLoaded: function() {
            return this.getState() === 'loaded';
        },

        /**
         * Private: Get default script URL
         */
        _getDefaultScriptUrl: function() {
            // Try to get from global config
            if (window.expoxrScriptConfig && window.expoxrScriptConfig.modelViewerScriptUrl) {
                this.scriptType = window.expoxrScriptConfig.scriptType || 'umd';
                return window.expoxrScriptConfig.modelViewerScriptUrl;
            }

            // Fallback to CDN UMD version
            const version = '3.3.0';
            this.scriptType = 'umd';
            return `https://unpkg.com/@google/model-viewer@v${version}/dist/model-viewer-umd.js`;
        },

        /**
         * Private: Load the script
         */
        _loadScript: function() {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = this.scriptUrl;
                
                // Set script type based on configuration
                if (this.scriptType === 'module') {
                    script.type = 'module';
                }                script.onload = () => {
                    console.log('ExpoXR ModelViewerLoader: Script loaded from', this.scriptUrl);
                    
                    // Check if model-viewer is now available
                    if (window.customElements && window.customElements.get('model-viewer')) {
                        resolve();
                        return;
                    }
                    
                    // For UMD scripts, element should be immediately available
                    // For ES modules, may need a small delay
                    if (this.scriptType === 'module') {
                        setTimeout(() => {
                            if (window.customElements && window.customElements.get('model-viewer')) {
                                resolve();
                            } else {
                                reject(new Error('model-viewer element not available after module load'));
                            }
                        }, 100);
                    } else {
                        // Give UMD a moment to register the element
                        setTimeout(() => {
                            if (window.customElements && window.customElements.get('model-viewer')) {
                                resolve();
                            } else {
                                reject(new Error('model-viewer element not available after UMD load'));
                            }
                        }, 50);
                    }
                };

                script.onerror = () => {
                    const error = new Error(`Failed to load script: ${this.scriptUrl}`);
                    reject(error);
                };
                
                // Handle potential duplicate registration errors
                const originalError = window.onerror;
                window.onerror = function(message, source, lineno, colno, error) {
                    if (message && typeof message === 'string' && 
                        message.includes('model-viewer') && 
                        message.includes('already been used')) {
                        console.warn('ExpoXR ModelViewerLoader: model-viewer already registered, continuing...');
                        if (window.customElements && window.customElements.get('model-viewer')) {
                            resolve();
                            return true;
                        }
                    }
                    
                    // Restore original error handler
                    if (originalError) {
                        return originalError.apply(this, arguments);
                    }
                    return false;
                };

                document.head.appendChild(script);
            });
        },

        /**
         * Private: Notify all callbacks
         */
        _notifyCallbacks: function(error) {
            const callbacks = this.callbacks.slice(); // Copy array
            this.callbacks = []; // Clear callbacks
            
            callbacks.forEach(callback => {
                try {
                    callback(error);
                } catch (e) {
                    console.error('ExpoXR ModelViewerLoader: Callback error', e);
                }
            });
        },

        /**
         * Reset the loader state (for testing)
         */
        reset: function() {
            this.state = 'unloaded';
            this.loadingPromise = null;
            this.callbacks = [];
            this.loadAttempts = 0;
        }
    };

    // Initialize state check
    window.ExpoXRModelViewerLoader.getState();

    // Expose convenience methods globally
    window.loadModelViewer = function(config) {
        return window.ExpoXRModelViewerLoader.load(config);
    };

    window.isModelViewerLoaded = function() {
        return window.ExpoXRModelViewerLoader.isLoaded();
    };

    // Listen for custom events that indicate script loading
    document.addEventListener('expoxr-script-loaded', function() {
        window.ExpoXRModelViewerLoader.getState();
    });

    document.addEventListener('expoxr-model-viewer-ready', function() {
        window.ExpoXRModelViewerLoader.getState();
    });

    expoxrDebugLog('ExpoXR ModelViewerLoader: Manager initialized');
})();
