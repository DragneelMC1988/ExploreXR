/**
 * Global Error Handler for ExploreXR Plugin
 * 
 * This script provides a global error handler to catch and manage JavaScript errors
 * that might occur anywhere in the site, including in third-party plugins like Elementor.
 * 
 * Version: 1.0.1
 * - Added compatibility with model-viewer global error handling
 */
(function() {
    'use strict';
    
    // Only initialize once
    if (window.explorexrErrorHandlerInitialized) {
        return;
    }
    
    // Log that we're initializing
    
    // Set up global error capture
    window.addEventListener('error', function(event) {
        // Check for errors in Elementor's element.js file
        if (event.filename && event.filename.indexOf('element.js') > -1) {
            console.warn('ExploreXR intercepted Elementor element.js error:', event.message);
            console.warn('Line:', event.lineno, 'Column:', event.colno);
            
            // If it's the specific syntax error at line 1299, prevent it from propagating
            if (event.lineno === 1299) {
                // Prevent default handling of the error (stops it from appearing in console)
                event.preventDefault();
                return true;
            }
        }
    }, true);
    
    // Mark as initialized to prevent duplicate initialization
    window.explorexrErrorHandlerInitialized = true;
})();
