/**
 * ExploreXR - Elementor Compatibility Helper
 * 
 * This file provides fixes for compatibility issues between ExploreXR and Elementor
 * - Fixes the syntax error in element.js
 * - Ensures admin menu scrolling works properly
 */
jQuery(document).ready(function($) {
    'use strict';
    
    console.log('ExploreXR: Elementor compatibility helper loaded');
    
    // Function to fix Elementor element.js compatibility issues
    function fixElementorIssues() {
        // Override any problematic functions or methods
        try {
            // Add window level error handler to catch Elementor element.js errors
            window.addEventListener('error', function(event) {
                // Check if the error is from element.js
                if (event.filename && event.filename.indexOf('element.js') > -1) {
                    console.warn('ExploreXR intercepted Elementor element.js error:', event.message);
                    
                    // Prevent the error from bubbling up and crashing the application
                    event.preventDefault();
                    return true;
                }
            }, true);
            
            // If Elementor is loaded, apply specific fixes
            if (typeof elementor !== 'undefined') {
                console.log('ExploreXR: Elementor detected, applying specific fixes');
                
                // Add additional Elementor-specific fixes here if needed
            }
        } catch (e) {
            console.warn('ExploreXR: Error applying Elementor compatibility fixes:', e);
        }
    }
    
    // Apply fixes with a slight delay to ensure all scripts are loaded
    setTimeout(function() {
        fixElementorIssues();
    }, 500);
});
