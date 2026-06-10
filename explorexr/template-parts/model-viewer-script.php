<?php
/**
 * Template part for including the model-viewer script in the frontend
 * 
 * @package ExploreXR
 */

// Don't load directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if model_id is needed and defined
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only parameter for model display
if (!isset($model_id) && isset($_GET['model_id'])) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for model display
    $model_id = intval($_GET['model_id']);
}

// Include loading options 
if (!function_exists('explorexr_get_loading_options')) {
    if (file_exists(EXPLOREXR_PLUGIN_DIR . 'admin/loading-options.php')) {
        require_once EXPLOREXR_PLUGIN_DIR . 'admin/loading-options.php';
    } elseif (file_exists(EXPLOREXR_PLUGIN_DIR . 'admin/settings/loading-options.php')) {
        require_once EXPLOREXR_PLUGIN_DIR . 'admin/settings/loading-options.php';
    }
}

// Define version fallback if EXPLOREXR_VERSION is not defined
if (!defined('EXPLOREXR_VERSION')) {
    define('EXPLOREXR_VERSION', '1.0.0');
}

// Static once-per-page guard: all script/style registration runs exactly once.
// On pages with multiple [explorexr_model] shortcodes the include fires N times;
// this prevents duplicate wp_enqueue_*/wp_localize_script calls on those pages.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template static flag
static $explorexr_scripts_initialized = false;
if ($explorexr_scripts_initialized) {
    return;
}
$explorexr_scripts_initialized = true;

// Cache all per-request options in a static so repeated shortcode calls never
// re-query the DB or alloptions cache more than once per page load.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables for script configuration
static $mv_cached_opts = null;
if ($mv_cached_opts === null) {
    $mv_cached_opts = array(
        'model_viewer_version'  => get_option('explorexr_model_viewer_version', '4.1.0'),
        'script_location'       => get_option('explorexr_script_location', 'footer'),
        'script_loading_timing' => get_option('explorexr_script_loading_timing', 'auto'),
        'lazy_load_poster'      => get_option('explorexr_lazy_load_poster', false),
    );
}
$model_viewer_version  = $mv_cached_opts['model_viewer_version'];
$script_location       = $mv_cached_opts['script_location'];
$script_loading_timing = $mv_cached_opts['script_loading_timing'];
$lazy_load_poster      = $mv_cached_opts['lazy_load_poster'];

// Determine script loading settings
$load_in_footer    = ($script_location === 'footer');
$script_attributes = array();
// Ensure the plugin URL is available before any model-viewer script executes
$plugin_url_inline_script = 'window.explorexrPluginUrl = window.explorexrPluginUrl || "' . esc_js(EXPLOREXR_PLUGIN_URL) . '";';
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Configure script loading timing
if ($script_loading_timing === 'defer') {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for script attributes
    $script_attributes['defer'] = true;
} elseif ($script_loading_timing === 'immediate') {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for script attributes
    $script_attributes = array(); // No defer or async for immediate loading
} elseif ($script_loading_timing === 'ondemand') {
    // Will be handled via lazy loading mechanism
    add_action('wp_footer', 'explorexr_add_ondemand_script_loader');
}

// Cache file-existence checks — these filesystem syscalls would otherwise run on
// every shortcode call on a page; with statics they run exactly once per request.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template static file-existence cache
static $umd_exists = null;
static $min_exists = null;
$local_umd_path = EXPLOREXR_PLUGIN_DIR . 'assets/js/model-viewer-umd.js';
$local_min_path = EXPLOREXR_PLUGIN_DIR . 'assets/js/model-viewer.min.js';
if ($umd_exists === null) {
    $umd_exists = file_exists($local_umd_path);
    $min_exists = file_exists($local_min_path);
}
// Preferred UMD URL: minified bundle when available (see helper-functions.php).
$umd_script_url = function_exists('explorexr_model_viewer_script_url')
    ? explorexr_model_viewer_script_url()
    : EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer-umd.js';
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Check if script has already been enqueued to prevent duplicates.
// First check if the global registration from admin/core/functions.php exists.
if (wp_script_is('explorexr-premium-model-viewer', 'registered') || wp_script_is('explorexr-premium-model-viewer', 'enqueued')) {
    if (!wp_script_is('explorexr-premium-model-viewer', 'enqueued')) {
        wp_enqueue_script('explorexr-premium-model-viewer');
    }
    wp_add_inline_script('explorexr-premium-model-viewer', $plugin_url_inline_script, 'before');
} else {
    // Fallback: Use template's own registration (legacy support).
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for script handle
    $script_handle = 'model-viewer-script';
    if (!wp_script_is($script_handle, 'enqueued')) {
        if ($umd_exists) {
            wp_enqueue_script($script_handle, $umd_script_url, array(), $model_viewer_version, $load_in_footer);
            if (!empty($script_attributes)) {
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Loop variables in template
                foreach ($script_attributes as $attr_name => $attr_value) {
                    wp_script_add_data($script_handle, $attr_name, $attr_value);
                }
            }
        } elseif ($min_exists) {
            wp_register_script($script_handle, EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer.min.js', array(), $model_viewer_version, $load_in_footer);
            wp_enqueue_script($script_handle);
            wp_script_add_data($script_handle, 'type', 'module');
        } else {
            if (is_admin() && current_user_can('manage_options')) {
                add_action('admin_notices', function() use ($local_umd_path, $local_min_path) {
                    echo '<div class="notice notice-error is-dismissible">';
                    echo '<p><strong>ExploreXR Error:</strong> Model Viewer script files not found. Please ensure either ' . esc_html($local_umd_path) . ' or ' . esc_html($local_min_path) . ' exists in the plugin directory.</p>';
                    echo '<p><em>The plugin requires local script files to function properly. External CDN resources are not allowed.</em></p>';
                    echo '</div>';
                });
            }
            return;
        }
    }
    wp_add_inline_script($script_handle, $plugin_url_inline_script, 'before');
}

// Enqueue centralized loader manager first
wp_enqueue_script('explorexr-model-viewer-loader-manager', EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer-loader-manager.js', array(), EXPLOREXR_VERSION, true);

// Enqueue the custom model loader script
wp_enqueue_script('explorexr-model-loader', EXPLOREXR_PLUGIN_URL . 'assets/js/model-loader.js', array('jquery', 'explorexr-model-viewer-loader-manager'), EXPLOREXR_VERSION, true);

// Enqueue model viewer wrapper for enhanced UI
wp_enqueue_script('explorexr-model-viewer-wrapper', EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer-wrapper.js', array('jquery', 'explorexr-model-viewer-loader-manager'), EXPLOREXR_VERSION, true);

// Pass loading options to the wrapper script
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for script localization
$loading_options = explorexr_get_loading_options();
wp_localize_script('explorexr-model-viewer-wrapper', 'ExploreXRLoadingOptions', $loading_options);

// Pass script configuration for preloader.
// Use the same static file-existence results from above to avoid redundant syscalls.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for script configuration
if ($umd_exists) {
    $script_config = array(
        'modelViewerScriptUrl' => $umd_script_url,
        'scriptType'           => 'umd',
    );
} else {
    $script_config = array(
        'modelViewerScriptUrl' => EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer.min.js',
        'scriptType'           => 'module',
    );
}

// Add plugin URL for local dependencies
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for script config
$script_config['pluginUrl'] = EXPLOREXR_PLUGIN_URL;

wp_localize_script('explorexr-model-viewer-wrapper', 'explorexrScriptConfig', $script_config);

// Set global plugin URL for Model Viewer dependencies (WordPress.org compliance - properly escaped)
wp_add_inline_script('explorexr-model-viewer-wrapper', 'window.explorexrPluginUrl = "' . esc_js(EXPLOREXR_PLUGIN_URL) . '";', 'before');

// debug-logger and model-handler are only needed when WP_DEBUG is on.
// On production sites they add unnecessary weight — ExploreXRLogger stubs
// are already inlined so log calls are safe to make without the full file.
if (defined('WP_DEBUG') && WP_DEBUG) {
    wp_enqueue_script('explorexr-debug-logger', EXPLOREXR_PLUGIN_URL . 'assets/js/debug-logger.js', array(), EXPLOREXR_VERSION, true);
    wp_add_inline_script('explorexr-debug-logger',
        'window.explorexrDebug = window.explorexrDebug || { enabled: false };',
        'before'
    );
    wp_enqueue_script('explorexr-model-handler', EXPLOREXR_PLUGIN_URL . 'assets/js/model-handler.js', array('jquery', 'explorexr-debug-logger'), EXPLOREXR_VERSION, true);
} else {
    // Provide a lightweight no-op stub so ExploreXRLogger calls don't throw.
    wp_add_inline_script('explorexr-model-viewer-wrapper',
        'window.explorexrDebug = window.explorexrDebug || { enabled: false };' .
        'window.ExploreXRLogger = window.ExploreXRLogger || { log: function(){}, warn: function(){}, error: function(){} };',
        'before'
    );
}

// Enqueue custom CSS
wp_enqueue_style('explorexr-model-viewer', EXPLOREXR_PLUGIN_URL . 'assets/css/model-viewer.css', array(), EXPLOREXR_VERSION);

// AR session handler (replaces $ar_fix_css inline style — CSS lives in assets/css/model-viewer.css)
// AR session JS extracted to assets/js/ar-session-handler.js and enqueued below

// Load Model button: project configured colors into CSS custom properties so the
// hardcoded fallbacks in model-viewer.css can be overridden from the settings UI.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for inline CSS
$loadbtn_bg          = get_option('explorexr_load_button_bg', '');
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$loadbtn_color       = get_option('explorexr_load_button_color', '');
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$loadbtn_hover_bg    = get_option('explorexr_load_button_hover_bg', '');
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$loadbtn_hover_color = get_option('explorexr_load_button_hover_color', '');
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$loadbtn_radius      = get_option('explorexr_load_button_radius', '');
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template-scoped CSS variable accumulator
$loadbtn_vars        = '';
if ($loadbtn_bg !== '') {
    $loadbtn_vars .= '--exr-loadbtn-bg:' . esc_attr($loadbtn_bg) . ';';
}
if ($loadbtn_color !== '') {
    $loadbtn_vars .= '--exr-loadbtn-color:' . esc_attr($loadbtn_color) . ';';
}
if ($loadbtn_hover_bg !== '') {
    $loadbtn_vars .= '--exr-loadbtn-hover-bg:' . esc_attr($loadbtn_hover_bg) . ';';
}
if ($loadbtn_hover_color !== '') {
    $loadbtn_vars .= '--exr-loadbtn-hover-color:' . esc_attr($loadbtn_hover_color) . ';';
}
if ($loadbtn_radius !== '') {
    $loadbtn_vars .= '--exr-loadbtn-radius:' . esc_attr($loadbtn_radius) . ';';
}
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if ($loadbtn_vars !== '') {
    wp_add_inline_style('explorexr-model-viewer', '.ExploreXR-load-model-btn, .explorexr-load-model-btn {' . $loadbtn_vars . '}');
}

// Enqueue AR session handler (JS extracted from inline script — CSS in model-viewer.css)
wp_enqueue_script(
    'explorexr-ar-session-handler',
    EXPLOREXR_PLUGIN_URL . 'assets/js/ar-session-handler.js',
    array( 'explorexr-model-viewer-wrapper' ),
    EXPLOREXR_VERSION,
    true
);

// Enqueue large-model load button handler (replaces per-instance wp_add_inline_script)
wp_enqueue_script(
    'explorexr-large-model-handler',
    EXPLOREXR_PLUGIN_URL . 'assets/js/large-model-handler.js',
    array( 'explorexr-model-loader' ),
    EXPLOREXR_VERSION,
    true
);

// Only add this filter once
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for filter control
static $filter_added = false;
if (!$filter_added) {
    add_filter('explorexr_premium_model_viewer_attributes', 'explorexr_add_model_viewer_attributes', 10, 2);
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable for filter control
    $filter_added = true;
}

// Define the function only if it doesn't exist
if (!function_exists('explorexr_add_model_viewer_attributes')) {    /**
     * Add data attributes to the model viewer based on plugin settings
     * 
     * @param array $attributes Existing model viewer attributes
     * @param int $model_id Model ID (optional, for compatibility)
     * @return array Updated attributes
     */
    function explorexr_add_model_viewer_attributes($attributes, $model_id = null) {
        // Resolve lazy poster: per-model override wins, otherwise global.
        // Cached in static so the option is fetched once per page, not per model.
        static $lazy_load_poster_global = null;
        if ($lazy_load_poster_global === null) {
            $lazy_load_poster_global = get_option('explorexr_lazy_load_poster', false);
        }
        $lazy_load_poster = $lazy_load_poster_global;
        if (!empty($model_id)) {
            $per_model_lazy_poster = get_post_meta($model_id, '_explorexr_premium_lazy_load_poster', true);
            if ($per_model_lazy_poster === 'on') {
                $lazy_load_poster = true;
            } elseif ($per_model_lazy_poster === 'off') {
                $lazy_load_poster = false;
            }
        }

        // NOTE: Loading addon now handles all loading attributes via filter hook
        // These old attributes have been removed to prevent conflicts
        // The Loading Options Add-on injects the new field structure via
        // the 'explorexr_premium_model_viewer_attributes' filter

        // Add lazy loading attributes
        if ($lazy_load_poster) {
            $attributes['data-lazy-load-poster'] = 'true';
            $attributes['loading'] = 'lazy'; // Add native lazy loading attribute
        }

        // Inject compression decoder locations on every model-viewer.
        // model-viewer fetches these only when the .glb actually contains the
        // matching compression payload, so there is no overhead for plain models.
        if (defined('EXPLOREXR_PLUGIN_URL')) {
            $attributes['draco-decoder-location']   = EXPLOREXR_PLUGIN_URL . 'assets/vendor/draco/';
            $attributes['ktx2-transcoder-location'] = EXPLOREXR_PLUGIN_URL . 'assets/vendor/basis-universal/';
            static $meshopt_exists = null;
            if ($meshopt_exists === null) {
                $meshopt_exists = file_exists(EXPLOREXR_PLUGIN_DIR . 'assets/vendor/meshopt/meshopt_decoder.module.js');
            }
            if ($meshopt_exists) {
                $attributes['meshopt-decoder'] = EXPLOREXR_PLUGIN_URL . 'assets/vendor/meshopt/meshopt_decoder.module.js';
            }
        }

        // AR support is not available in the Free version
        // Premium AR features are available in the Pro version only

        return $attributes;
    }
}

// Add the on-demand script loader function
if (!function_exists('explorexr_add_ondemand_script_loader')) {
    /**
     * Add on-demand script loader for model-viewer
     * This function loads the model-viewer script only when needed
     */
    function explorexr_add_ondemand_script_loader() {
        $model_viewer_version = get_option('explorexr_model_viewer_version', '4.1.0');

        // Local files only (CDN is disabled for WordPress.org compliance).
        if (!file_exists(EXPLOREXR_PLUGIN_DIR . 'assets/js/model-viewer-umd.js')) {
            return; // Don't output any script loader
        }
        $script_url = function_exists('explorexr_model_viewer_script_url')
            ? explorexr_model_viewer_script_url()
            : EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer-umd.js';
        
        // WordPress.org compliance: Convert inline script to wp_add_inline_script
        $script_loader_js = '
        (function() {
            // Store references to model viewers on the page
            var ExploreXRModelViewers = document.querySelectorAll(".explorexr-model-viewer-container");
            var scriptLoaded = false;
            var scriptIsLoading = false;
            var modelViewersToInit = [];
            
            // Function to load the model-viewer script
            function loadModelViewerScript(callback) {
                if (scriptLoaded) {
                    if (typeof callback === "function") callback();
                    return;
                }
                
                if (scriptIsLoading) {
                    // Add callback to queue if script is already loading
                    window.ExploreXROnScriptLoad = window.ExploreXROnScriptLoad || [];
                    if (typeof callback === "function") {
                        window.ExploreXROnScriptLoad.push(callback);
                    }
                    return;
                }
                
                scriptIsLoading = true;
                window.ExploreXROnScriptLoad = window.ExploreXROnScriptLoad || [];
                if (typeof callback === "function") {
                    window.ExploreXROnScriptLoad.push(callback);
                }
                
                // Create script element
                var script = document.createElement("script");
                script.src = "' . esc_js($script_url) . '";
                script.async = true;
                script.onload = function() {
                    scriptLoaded = true;
                    scriptIsLoading = false;
                    
                    // Call all queued callbacks
                    if (window.ExploreXROnScriptLoad && window.ExploreXROnScriptLoad.length) {
                        window.ExploreXROnScriptLoad.forEach(function(fn) {
                            if (typeof fn === "function") fn();
                        });
                        window.ExploreXROnScriptLoad = [];
                    }
                    
                    // Initialize all waiting model viewers
                    initQueuedModelViewers();
                };
                
                script.onerror = function() {
                    if (typeof ExploreXRLogger !== "undefined") {
                        ExploreXRLogger.log("ExploreXR: model-viewer script could not be loaded", "warn");
                    }
                    
                    // Try to show user-friendly notification if notification system is available
                    if (typeof window.ExploreXRCreateNotification !== "undefined") {
                        window.ExploreXRCreateNotification(
                            "Model viewer is temporarily unavailable. Please check your internet connection or contact support.",
                            "error",
                            true
                        );
                    }
                    
                    scriptIsLoading = false;
                };
                
                document.body.appendChild(script);
            }
            
            // Function to initialize model viewers after script loads
            function initQueuedModelViewers() {
                if (!scriptLoaded) return;
                
                modelViewersToInit.forEach(function(container) {
                    initializeModelViewer(container);
                });
                modelViewersToInit = [];
            }
            
            // Function to initialize a specific model viewer
            function initializeModelViewer(container) {
                if (!container) return;
                
                // Check if container has already been initialized
                if (container.dataset.initialized === "true") {
                    return;
                }
                
                // Mark as initialized
                container.dataset.initialized = "true";
                
                // Get the model URL from data attribute
                var modelUrl = container.dataset.modelUrl;
                if (!modelUrl) return;
                
                // Find the model-viewer element
                var modelViewerEl = container.querySelector("model-viewer");
                if (!modelViewerEl) return;
                
                // Set model source if not already set
                if (!modelViewerEl.hasAttribute("src") && modelUrl) {
                    modelViewerEl.setAttribute("src", modelUrl);
                }
                
                // Remove loading class
                container.classList.remove("explorexr-loading");
                container.classList.add("explorexr-loaded");
                
                // Trigger a custom event for further processing
                var event = new CustomEvent("explorexr-model-viewer-initialized", {
                    bubbles: true,
                    detail: { container: container, modelViewer: modelViewerEl }
                });
                container.dispatchEvent(event);
            }
            
            // Use Intersection Observer to detect when viewers come into view
            if ("IntersectionObserver" in window) {
                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var container = entry.target;
                            
                            // Stop observing this element
                            observer.unobserve(container);
                            
                            // If script is already loaded, initialize immediately
                            if (scriptLoaded) {
                                initializeModelViewer(container);
                            } else {
                                // Queue for initialization after script loads
                                modelViewersToInit.push(container);
                                loadModelViewerScript();
                            }
                        }
                    });
                }, { rootMargin: "200px 0px" });
                
                // Start observing all model viewer containers
                ExploreXRModelViewers.forEach(function(container) {
                    observer.observe(container);
                });
            } else {
                // Fallback for browsers without Intersection Observer support
                loadModelViewerScript(function() {
                    ExploreXRModelViewers.forEach(function(container) {
                        initializeModelViewer(container);
                    });
                });
            }
            
            // Expose the loader function to the global scope for external access
            window.ExploreXRLoadModelViewerScript = loadModelViewerScript;
            
            // Make model initialization function available globally
            window.ExploreXRInitModelViewer = initializeModelViewer;
        })();
        ';
        
        // Use wp_add_inline_script for WordPress.org compliance
        wp_add_inline_script('explorexr-model-viewer-wrapper', $script_loader_js);
    }
}
?>




