/**
 * ExploreXR Centralized Debug Logger
 * 
 * Provides a unified console logging interface that respects debug mode settings.
 * All addons should use this instead of direct console.log calls.
 * 
 * Usage:
 *   ExploreXRLogger.log('My message');
 *   ExploreXRLogger.warn('Warning message');
 *   ExploreXRLogger.error('Error message'); // Always logs
 *   ExploreXRLogger.info('Info message');
 */

(function(window) {
    'use strict';

    // Check if logger already exists
    if (window.ExploreXRLogger) {
        return;
    }

    /**
     * Check if debug mode is enabled
     * @returns {boolean}
     */
    function isDebugEnabled() {
        return typeof window.explorexrDebug !== 'undefined' && 
               window.explorexrDebug.enabled === true;
    }

    /**
     * ExploreXR Debug Logger
     */
    const ExploreXRLogger = {
        /**
         * Log a debug message (only shows when debug is enabled)
         * @param {string} message - The message to log
         * @param {...any} args - Additional arguments to log
         */
        log: function(message, ...args) {
            if (isDebugEnabled()) {
                console.log('[ExploreXR]', message, ...args);
            }
        },

        /**
         * Log a warning message (only shows when debug is enabled)
         * @param {string} message - The warning message
         * @param {...any} args - Additional arguments to log
         */
        warn: function(message, ...args) {
            if (isDebugEnabled()) {
                console.warn('[ExploreXR]', message, ...args);
            }
        },

        /**
         * Log an info message (only shows when debug is enabled)
         * @param {string} message - The info message
         * @param {...any} args - Additional arguments to log
         */
        info: function(message, ...args) {
            if (isDebugEnabled()) {
                console.info('[ExploreXR]', message, ...args);
            }
        },

        /**
         * Log an error message (ALWAYS logs, regardless of debug mode)
         * @param {string} message - The error message
         * @param {...any} args - Additional arguments to log
         */
        error: function(message, ...args) {
            console.error('[ExploreXR Error]', message, ...args);
        },

        /**
         * Check if debug mode is currently enabled
         * @returns {boolean}
         */
        isEnabled: function() {
            return isDebugEnabled();
        },

        /**
         * Log to a specific addon (only shows when debug is enabled)
         * @param {string} addonName - Name of the addon
         * @param {string} message - The message to log
         * @param {...any} args - Additional arguments to log
         */
        addon: function(addonName, message, ...args) {
            if (isDebugEnabled()) {
                console.log(`[ExploreXR ${addonName}]`, message, ...args);
            }
        }
    };

    // Expose globally
    window.ExploreXRLogger = ExploreXRLogger;

    // Convenience shorthand
    window.xrLog = ExploreXRLogger.log;
    window.xrWarn = ExploreXRLogger.warn;
    window.xrError = ExploreXRLogger.error;
    window.xrInfo = ExploreXRLogger.info;

})(window);
