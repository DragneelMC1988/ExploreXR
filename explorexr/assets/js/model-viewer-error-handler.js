// ExpoXR model viewer error handler
// This file contains error handling code for the model viewer

/**
 * Get user-friendly error messages for model-viewer errors
 * @param {string} type - The error type
 * @param {string} details - Additional error details
 * @return {string} User-friendly error message
 */
function getUserFriendlyModelError(type, details) {
    switch(type) {
        case 'loadfailure':
            if (details.includes('Failed to fetch')) {
                return 'Unable to display the 3D model. Please check your internet connection and try again.';
            } else if (details.includes('Failed to load resource')) {
                return 'The 3D model is currently unavailable. Please try refreshing the page.';
            } else if (details.includes('CORS')) {
                return 'Unable to display the model due to server configuration. Please contact support if this persists.';
            }
            return 'The 3D model is temporarily unavailable. Please try again in a moment.';
            
        case 'webglcontextlost':
            return 'Graphics context error. Try refreshing the page or updating your graphics drivers.';
            
        case 'filetoobig':
            return 'The 3D model file is too large for your device to handle.';
            
        default:
            return 'The 3D model is currently unavailable. Please try refreshing the page or contact support if the issue persists.';
    }
}

/**
 * Check WebGL support and version
 * @return {string} WebGL support status
 */
function checkWebGLSupport() {
    try {
        var canvas = document.createElement("canvas");
        var gl = canvas.getContext("webgl2") || canvas.getContext("webgl") || canvas.getContext("experimental-webgl");
        
        if (!gl) {
            return "WebGL not supported";
        }
        
        var debugInfo = gl.getExtension("WEBGL_debug_renderer_info");
        if (debugInfo) {
            return "Renderer: " + gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
        } else {
            return "WebGL supported (renderer info unavailable)";
        }
    } catch (e) {
        return "Error checking WebGL: " + e.message;
    }
}
