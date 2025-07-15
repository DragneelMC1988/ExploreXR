/**
 * ExploreXR Unified Error Handler
 * 
 * Centralized error handling for all ExploreXR functionality
 */
(function() {
    'use strict';
    
    // Prevent multiple initializations
    if (window.ExploreXRErrorHandler) {
        return;
    }
    
    const ExploreXRErrorHandler = {
        
        errors: [],
        errorCallbacks: [],
        debugMode: false,
        
        /**
         * Initialize error handler
         */
        init: function() {
            this.setupGlobalErrorHandling();
            this.setupModelViewerErrorHandling();
            
            // Check for debug mode
            if (typeof exploreXRDebug !== 'undefined') {
                this.debugMode = exploreXRDebug.enabled || false;
            }
            
            this.log('ExploreXR Error Handler initialized');
        },
        
        /**
         * Set up global error handling
         */
        setupGlobalErrorHandling: function() {
            window.addEventListener('error', (event) => {
                this.handleGlobalError(event);
            });
            
            window.addEventListener('unhandledrejection', (event) => {
                this.handlePromiseRejection(event);
            });
        },
        
        /**
         * Set up model viewer specific error handling
         */
        setupModelViewerErrorHandling: function() {
            document.addEventListener('DOMContentLoaded', () => {
                this.observeModelViewers();
            });
        },
        
        /**
         * Observe model viewers for error events
         */
        observeModelViewers: function() {
            const modelViewers = document.querySelectorAll('model-viewer');
            
            modelViewers.forEach((viewer, index) => {
                this.attachErrorHandlerToModelViewer(viewer, index);
            });
            
            // Watch for dynamically added model viewers
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1 && node.tagName === 'MODEL-VIEWER') {
                            this.attachErrorHandlerToModelViewer(node, document.querySelectorAll('model-viewer').length);
                        }
                    });
                });
            });
            
            observer.observe(document.body, { childList: true, subtree: true });
        },
        
        /**
         * Attach error handler to a specific model viewer
         */
        attachErrorHandlerToModelViewer: function(viewer, index) {
            if (viewer._exploreXRErrorHandlerAttached) {
                return;
            }
            
            viewer._exploreXRErrorHandlerAttached = true;
            
            viewer.addEventListener('error', (event) => {
                this.handleModelViewerError(viewer, event, index);
            });
        },
        
        /**
         * Handle global JavaScript errors
         */
        handleGlobalError: function(event) {
            // Ignore third-party errors we can't fix
            if (event.filename && this.isThirdPartyError(event.filename)) {
                return;
            }
            
            const error = {
                type: 'javascript',
                message: event.message,
                filename: event.filename,
                line: event.lineno,
                column: event.colno,
                stack: event.error ? event.error.stack : null,
                timestamp: new Date().toISOString()
            };
            
            this.logError(error);
        },
        
        /**
         * Handle promise rejections
         */
        handlePromiseRejection: function(event) {
            const error = {
                type: 'promise',
                message: event.reason ? event.reason.toString() : 'Promise rejected',
                stack: event.reason ? event.reason.stack : null,
                timestamp: new Date().toISOString()
            };
            
            this.logError(error);
        },
        
        /**
         * Handle model viewer specific errors
         */
        handleModelViewerError: function(viewer, event, index) {
            const errorType = event.detail.type || 'unknown';
            const sourceError = event.detail.sourceError;
            
            const error = {
                type: 'model-viewer',
                modelIndex: index,
                modelId: viewer.id || `model-${index}`,
                modelSrc: viewer.getAttribute('src'),
                errorType: errorType,
                errorDetails: sourceError ? sourceError.message : 'Unknown error',
                timestamp: new Date().toISOString()
            };
            
            this.logError(error);
            this.displayUserFriendlyError(viewer, error);
        },
        
        /**
         * Display user-friendly error message
         */
        displayUserFriendlyError: function(viewer, error) {
            const friendlyMessage = this.getFriendlyErrorMessage(error.errorType);
            
            // Create error display
            const errorContainer = document.createElement('div');
            errorContainer.className = 'exploreXR-error-display';
            errorContainer.innerHTML = `
                <div class="exploreXR-error-icon">⚠️</div>
                <div class="exploreXR-error-message">${friendlyMessage}</div>
                ${this.debugMode ? `<div class="exploreXR-error-details">${error.errorDetails}</div>` : ''}
            `;
            
            // Style the error container
            this.styleErrorContainer(errorContainer);
            
            // Replace or overlay the model viewer
            this.showErrorInPlace(viewer, errorContainer);
        },
        
        /**
         * Get user-friendly error message
         */
        getFriendlyErrorMessage: function(errorType) {
            const messages = {
                'loadfailure': 'Unable to display the 3D model. Please check your connection and try again.',
                'invalidformat': 'The 3D model format is not supported.',
                'webglunsupported': 'Your browser does not support 3D graphics (WebGL).',
                'networkerror': 'Network issue while accessing the 3D model.',
                'timeout': 'The 3D model is taking longer than expected to load.',
                'memoryerror': 'This 3D model requires more memory than available.',
                'unknown': 'Unable to display the 3D model at this time.'
            };
            
            return messages[errorType] || messages['unknown'];
        },
        
        /**
         * Style error container
         */
        styleErrorContainer: function(container) {
            container.style.cssText = `
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 20px;
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                border-radius: 4px;
                color: #721c24;
                font-family: Arial, sans-serif;
                text-align: center;
                min-height: 200px;
                width: 100%;
                height: 100%;
                box-sizing: border-box;
            `;
            
            const icon = container.querySelector('.exploreXR-error-icon');
            if (icon) {
                icon.style.fontSize = '48px';
                icon.style.marginBottom = '10px';
            }
            
            const message = container.querySelector('.exploreXR-error-message');
            if (message) {
                message.style.fontSize = '16px';
                message.style.fontWeight = 'bold';
                message.style.marginBottom = '10px';
            }
            
            const details = container.querySelector('.exploreXR-error-details');
            if (details) {
                details.style.fontSize = '12px';
                details.style.opacity = '0.7';
                details.style.fontFamily = 'monospace';
            }
        },
        
        /**
         * Show error in place of model viewer
         */
        showErrorInPlace: function(viewer, errorContainer) {
            const parent = viewer.parentNode;
            const wrapper = viewer.closest('.exploreXR-model-viewer-container');
            
            if (wrapper) {
                wrapper.appendChild(errorContainer);
                viewer.style.display = 'none';
            } else {
                parent.insertBefore(errorContainer, viewer);
                viewer.style.display = 'none';
            }
        },
        
        /**
         * Check if error is from third-party source
         */
        isThirdPartyError: function(filename) {
            const thirdPartyPatterns = [
                'elementor',
                'jquery',
                'chrome-extension',
                'moz-extension',
                'extension',
                'ads',
                'analytics'
            ];
            
            return thirdPartyPatterns.some(pattern => 
                filename && filename.toLowerCase().includes(pattern)
            );
        },
        
        /**
         * Log error
         */
        logError: function(error) {
            this.errors.push(error);
            
            if (this.debugMode) {
                console.error('ExploreXR Error:', error);
            }
            
            // Call registered callbacks
            this.errorCallbacks.forEach(callback => {
                try {
                    callback(error);
                } catch (e) {
                    console.error('Error in error callback:', e);
                }
            });
            
            // Send to analytics if configured
            this.sendErrorToAnalytics(error);
        },
        
        /**
         * Send error to analytics
         */
        sendErrorToAnalytics: function(error) {
            // Implementation depends on your analytics setup
            if (typeof gtag !== 'undefined') {
                gtag('event', 'exception', {
                    description: `ExploreXR ${error.type}: ${error.message}`,
                    fatal: false
                });
            }
        },
        
        /**
         * Register error callback
         */
        onError: function(callback) {
            if (typeof callback === 'function') {
                this.errorCallbacks.push(callback);
            }
        },
        
        /**
         * Get all errors
         */
        getErrors: function() {
            return [...this.errors];
        },
        
        /**
         * Clear errors
         */
        clearErrors: function() {
            this.errors = [];
        },
        
        /**
         * Log message (only in debug mode)
         */
        log: function(message) {
            if (this.debugMode) {
                console.log(`[ExploreXR] ${message}`);
            }
        }
    };
    
    // Initialize immediately
    ExploreXRErrorHandler.init();
    
    // Make globally available
    window.ExploreXRErrorHandler = ExploreXRErrorHandler;
    
})();
