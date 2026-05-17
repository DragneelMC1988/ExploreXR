/**
 * ExploreXR Debug Logger - Usage Examples
 * 
 * This file demonstrates how to use the centralized debug logger
 * in your ExploreXR addon JavaScript files.
 */

// ============================================
// BASIC USAGE
// ============================================

// Debug log (only shows when debug is enabled)
ExploreXRLogger.log('Initializing my addon');

// Warning (only shows when debug is enabled)
ExploreXRLogger.warn('Configuration missing, using defaults');

// Info message (only shows when debug is enabled)
ExploreXRLogger.info('User clicked AR button');

// Error message (ALWAYS shows, even when debug is disabled)
ExploreXRLogger.error('Failed to load 3D model');

// ============================================
// ADDON-SPECIFIC LOGS
// ============================================

// Prefix your logs with addon name for easy filtering
ExploreXRLogger.addon('AR', 'AR session started');
ExploreXRLogger.addon('Animation', 'Playing animation: walk_cycle');
ExploreXRLogger.addon('Materials', 'Switching to variant: metal');

// ============================================
// WITH ADDITIONAL DATA
// ============================================

// Log with objects and data
ExploreXRLogger.log('Model loaded:', {
    id: 'model-123',
    fileSize: '2.5MB',
    vertices: 15000
});

// ============================================
// SHORTHAND ALIASES
// ============================================

// Use shorthand for less typing
xrLog('This is easier to type');
xrWarn('Warning message');
xrError('Error message');
xrInfo('Info message');

// ============================================
// CHECKING DEBUG STATE
// ============================================

// Check if debug is enabled before doing expensive operations
if (ExploreXRLogger.isEnabled()) {
    // Only calculate this debug info when needed
    const debugData = calculateExpensiveDebugInfo();
    ExploreXRLogger.log('Debug data:', debugData);
}

// ============================================
// MIGRATION FROM OLD CODE
// ============================================

// OLD WAY (don't use this):
if (typeof window.explorexrDebug !== 'undefined' && window.explorexrDebug.enabled) {
    console.log('My message');
}

// NEW WAY (use this):
ExploreXRLogger.log('My message');

// ============================================
// CONSOLE OUTPUT EXAMPLES
// ============================================

/*
When Debug is ENABLED, you'll see:
[ExploreXR] Initializing my addon
[ExploreXR] Warning: Configuration missing, using defaults
[ExploreXR AR] AR session started
[ExploreXR Animation] Playing animation: walk_cycle

When Debug is DISABLED, you'll see:
(Only errors will show)
[ExploreXR Error] Failed to load 3D model
*/

// ============================================
// BEST PRACTICES
// ============================================

/**
 * 1. Use appropriate log levels:
 *    - .log() for general debug information
 *    - .info() for informational messages
 *    - .warn() for warnings that don't break functionality
 *    - .error() for critical errors
 * 
 * 2. Use addon-specific prefixes:
 *    - Makes filtering in browser console easier
 *    - Helps identify which addon has issues
 * 
 * 3. Include relevant data:
 *    - Log objects and values that help debugging
 *    - Don't log sensitive information
 * 
 * 4. Check isEnabled() for expensive operations:
 *    - Avoid calculating debug data when not needed
 *    - Improves performance in production
 */

// ============================================
// FILTERING IN BROWSER CONSOLE
// ============================================

/**
 * To filter logs in browser console:
 * 
 * 1. Show only ExploreXR logs:
 *    Filter: "ExploreXR"
 * 
 * 2. Show only AR addon logs:
 *    Filter: "ExploreXR AR"
 * 
 * 3. Show only errors:
 *    Filter: "ExploreXR Error"
 * 
 * 4. Hide all ExploreXR logs:
 *    Filter: "-ExploreXR"
 */
