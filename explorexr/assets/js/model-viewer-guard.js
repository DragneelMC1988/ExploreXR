/**
 * ExploreXR Model Viewer Guard
 * Prevents duplicate registration of model-viewer custom element
 */
(function() {
    'use strict';
    
    // Set a global flag to track model-viewer registration
    if (!window.explorexr_mODEL_VIEWER_LOADED) {
        window.explorexr_mODEL_VIEWER_LOADED = {
            registered: false,
            version: null,
            loadTime: null,
            source: null
        };
    }
    
    // Check if model-viewer is already registered
    if (window.customElements && window.customElements.get('model-viewer')) {
        
        // Update our tracking object
        window.explorexr_mODEL_VIEWER_LOADED.registered = true;
        window.explorexr_mODEL_VIEWER_LOADED.loadTime = window.explorexr_mODEL_VIEWER_LOADED.loadTime || new Date().toISOString();
        
        // Monkey patch any future attempts to define model-viewer
        const originalDefine = window.customElements.define;
        window.customElements.define = function(name, constructor, options) {
            if (name === 'model-viewer') {
                console.warn('[ExploreXR Guard] Blocked attempt to redefine model-viewer custom element');
                return; // Silently block the redefinition
            }
            return originalDefine.call(this, name, constructor, options);
        };
        
        return;
    }
    
    // Monkey patch customElements.define to prevent duplicate model-viewer registration
    if (window.customElements && !window.explorexr_mODEL_VIEWER_PATCHED) {
        const originalDefine = window.customElements.define;
        
        window.customElements.define = function(name, constructor, options) {
            if (name === 'model-viewer') {
                if (window.explorexr_mODEL_VIEWER_LOADED.registered) {
                    console.warn('[ExploreXR Guard] Blocked attempt to redefine model-viewer custom element');
                    return; // Silently block the redefinition
                } else {
                    // This is the first registration
                    window.explorexr_mODEL_VIEWER_LOADED.registered = true;
                    window.explorexr_mODEL_VIEWER_LOADED.loadTime = new Date().toISOString();
                    window.explorexr_mODEL_VIEWER_LOADED.source = document.currentScript ? document.currentScript.src : 'unknown';
                    
                    try {
                        return originalDefine.call(this, name, constructor, options);
                    } catch (e) {
                        console.error('[ExploreXR Guard] Error during model-viewer registration:', e);
                        // If registration failed but we didn't detect it was already registered,
                        // it might be a different error, so set registered back to false
                        if (!e.message.includes('already been used')) {
                            window.explorexr_mODEL_VIEWER_LOADED.registered = false;
                        }
                        throw e; // Re-throw the error for other handlers
                    }
                }
            }
            
            // For all other elements, proceed normally
            return originalDefine.call(this, name, constructor, options);
        };
        
        window.explorexr_mODEL_VIEWER_PATCHED = true;
    }
    
    // Set up error handler to catch and suppress duplicate registration errors
    const originalOnError = window.onerror;
    
    window.onerror = function(message, source, lineno, colno, error) {
        // Check for the specific model-viewer duplicate registration error
        if (message && typeof message === 'string' && 
            ((message.includes('model-viewer') && message.includes('already been used')) ||
             (message.includes('Failed to execute \'define\' on \'CustomElementRegistry\'') && 
              message.includes('model-viewer')))) {
            
            console.warn('[ExploreXR Guard] Suppressed model-viewer registration error:', message);
            
            // Update our tracking object
            window.explorexr_mODEL_VIEWER_LOADED.registered = true;
            
            // Check if model-viewer is actually available and log status
            const isAvailable = !!(window.customElements && window.customElements.get('model-viewer'));
            
            // Prevent the error from propagating
            return true;
        }
        
        // For all other errors, use the original handler
        if (originalOnError) {
            return originalOnError.apply(this, arguments);
        }
        
        return false;
    };
    
})();

// Global utility function to check if model-viewer is ready
window.isModelViewerReady = function() {
    return !!(window.customElements && window.customElements.get('model-viewer'));
};
