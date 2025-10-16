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
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only parameter for model display
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
    define('EXPLOREXR_VERSION', '1.0.1');
}

// Get settings from options
$cdn_source = get_option('explorexr_cdn_source', 'local');

// Force local mode for WordPress.org compliance - override any CDN setting
if ($cdn_source === 'cdn') {
    $cdn_source = 'local';
    // Update the option to prevent future issues
    update_option('explorexr_cdn_source', 'local');
}
$model_viewer_version = get_option('explorexr_model_viewer_version', '3.3.0');

// Get the new loading options
$script_location = get_option('explorexr_script_location', 'footer');
$script_loading_timing = get_option('explorexr_script_loading_timing', 'auto');
$lazy_load_poster = get_option('explorexr_lazy_load_poster', false);
$lazy_load_model = get_option('explorexr_lazy_load_model', false);

// Determine script loading settings
$load_in_footer = ($script_location === 'footer');
$script_attributes = array();

// Configure script loading timing
if ($script_loading_timing === 'defer') {
    $script_attributes['defer'] = true;
} elseif ($script_loading_timing === 'immediate') {
    $script_attributes = array(); // No defer or async for immediate loading
} elseif ($script_loading_timing === 'ondemand') {
    // Will be handled via lazy loading mechanism
    add_action('wp_footer', 'explorexr_add_ondemand_script_loader');
}

// Check if script has already been enqueued to prevent duplicates
$script_handle = 'model-viewer-script';
if (!wp_script_is($script_handle, 'enqueued')) {
    if ($cdn_source === 'local') {
        $local_umd_path = EXPLOREXR_PLUGIN_DIR . 'assets/js/model-viewer-umd.js';
        $local_min_path = EXPLOREXR_PLUGIN_DIR . 'assets/js/model-viewer.min.js';
        
        // If UMD version exists, use it (preferred for compatibility)
        if (file_exists($local_umd_path)) {
            wp_enqueue_script($script_handle, EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer-umd.js', array(), $model_viewer_version, $load_in_footer);
            
            // Apply script attributes if needed
            if (!empty($script_attributes)) {
                foreach ($script_attributes as $attr_name => $attr_value) {
                    wp_script_add_data($script_handle, $attr_name, $attr_value);
                }
            }
        }
        // If only the minified version exists, use it properly as a module
        elseif (file_exists($local_min_path)) {
            // Register the script properly with WordPress
            wp_register_script($script_handle, EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer.min.js', array(), $model_viewer_version, $load_in_footer);
            wp_enqueue_script($script_handle);
            
            // Add module attribute for ES6 modules
            wp_script_add_data($script_handle, 'type', 'module');
        }
        // If no local versions exist, show admin error and don't load any script
        else {
            // Add admin notice about missing local files
            if (is_admin() && current_user_can('manage_options')) {
                add_action('admin_notices', function() use ($local_umd_path, $local_min_path) {
                    echo '<div class="notice notice-error is-dismissible">';
                    echo '<p><strong>ExploreXR Error:</strong> Model Viewer script files not found. Please ensure either ' . esc_html($local_umd_path) . ' or ' . esc_html($local_min_path) . ' exists in the plugin directory.</p>';
                    echo '<p><em>The plugin requires local script files to function properly. External CDN resources are not allowed.</em></p>';
                    echo '</div>';
                });
            }
            
            return; // Exit without loading any script
        }
    } else {
        // WordPress.org Plugin Check compliance: External CDN resources are not allowed
        // All scripts must be bundled locally with the plugin
        
        // Add admin notice about CDN source being disabled
        if (is_admin() && current_user_can('manage_options')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p><strong>ExploreXR Error:</strong> CDN source is not allowed. WordPress.org Plugin Check requires all scripts to be bundled locally.</p>';
                echo '<p><em>Please set cdn_source to "local" and ensure model-viewer script files exist in assets/js/ directory.</em></p>';
                echo '</div>';
            });
        }
        
        
        return; // Exit without loading any external script
    }
}

// Enqueue centralized loader manager first
wp_enqueue_script('explorexr-model-viewer-loader-manager', EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer-loader-manager.js', array(), EXPLOREXR_VERSION, true);

// Enqueue the custom model loader script
wp_enqueue_script('explorexr-model-loader', EXPLOREXR_PLUGIN_URL . 'assets/js/model-loader.js', array('jquery', 'explorexr-model-viewer-loader-manager'), EXPLOREXR_VERSION, true);

// Enqueue model viewer wrapper for enhanced UI
wp_enqueue_script('explorexr-model-viewer-wrapper', EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer-wrapper.js', array('jquery', 'explorexr-model-viewer-loader-manager'), EXPLOREXR_VERSION, true);

// Pass loading options to the wrapper script
$loading_options = explorexr_get_loading_options();
wp_localize_script('explorexr-model-viewer-wrapper', 'ExploreXRLoadingOptions', $loading_options);

// Pass script configuration for preloader
$script_config = array();
if ($cdn_source === 'local') {
    $local_umd_path = EXPLOREXR_PLUGIN_DIR . 'assets/js/model-viewer-umd.js';
    if (file_exists($local_umd_path)) {
        $script_config['modelViewerScriptUrl'] = EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer-umd.js';
        $script_config['scriptType'] = 'umd';
    } else {
        $script_config['modelViewerScriptUrl'] = EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer.min.js';
        $script_config['scriptType'] = 'module';
    }
} else {
    // Local UMD version for better compatibility (WordPress.org compliance)
    $script_config['modelViewerScriptUrl'] = EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer-umd.js';
    $script_config['scriptType'] = 'umd';
}

// Add plugin URL for local dependencies
$script_config['pluginUrl'] = EXPLOREXR_PLUGIN_URL;

wp_localize_script('explorexr-model-viewer-wrapper', 'explorexrScriptConfig', $script_config);

// Set global plugin URL for Model Viewer dependencies (WordPress.org compliance - properly escaped)
wp_add_inline_script('explorexr-model-viewer-wrapper', 'window.explorexrPluginUrl = "' . esc_js(EXPLOREXR_PLUGIN_URL) . '";', 'before');

// Enqueue model-handler.js for debugging features
wp_enqueue_script('explorexr-model-handler', EXPLOREXR_PLUGIN_URL . 'assets/js/model-handler.js', array('jquery'), EXPLOREXR_VERSION, true);

// Enqueue custom CSS
wp_enqueue_style('explorexr-model-viewer', EXPLOREXR_PLUGIN_URL . 'assets/css/model-viewer.css', array(), EXPLOREXR_VERSION);

// Add AR session CSS fixes
$ar_fix_css = "
    /* ExploreXR AR mode fixes */
    model-viewer {
        --poster-color: transparent;
    }
    
    /* Ensure model remains visible during AR session */
    .explorexr-ar-session-active model-viewer {
        visibility: visible !important;
        opacity: 1 !important;
        display: block !important;
    }
    
    /* iOS AR mode specific fixes */
    @supports (-webkit-touch-callout: none) {
        model-viewer::part(default-ar-button) {
            transform: scale(1.5);
        }
        
        .explorexr-ar-session-active {
            height: 100vh !important;
            overflow: hidden !important;
        }
    }
";
wp_add_inline_style('explorexr-model-viewer', $ar_fix_css);

// Add AR session handling JavaScript
$ar_fix_js = "
    // AR session event handling
    document.addEventListener('DOMContentLoaded', function() {
        // Function to handle AR session events globally
        function handleARSessions() {
            document.addEventListener('explorexr-ar-session-started', function(event) {
                document.body.classList.add('explorexr-ar-session-active');
                
                // Add visibility fix for iOS devices
                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                if (isIOS) {
                    // Force visibility in iOS AR mode
                    const modelId = event.detail.instanceId;
                    const modelEl = document.querySelector('#' + modelId + '-viewer model-viewer');
                    if (modelEl) {
                        modelEl.style.visibility = 'visible';
                        modelEl.style.opacity = '1';
                        modelEl.style.transform = 'translateZ(0)';
                    }
                }
            });
            
            document.addEventListener('explorexr-ar-session-ended', function(event) {
                document.body.classList.remove('explorexr-ar-session-active');
                
                // Restore model visibility after AR session ends
                setTimeout(function() {
                    const modelId = event.detail.instanceId;
                    const modelEl = document.querySelector('#' + modelId + '-viewer model-viewer');
                    if (modelEl) {
                        modelEl.style.visibility = 'visible';
                        modelEl.style.opacity = '1';
                    }
                }, 300);
            });
        }
        
        // Initialize AR session handling
        handleARSessions();
        
        // Polyfill for quick-look AR session detection on iOS
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        if (isIOS) {
            // Monitor for Quick Look session
            document.body.addEventListener('click', function(event) {                if (event.target && (
                    (event.target.closest && event.target.closest('model-viewer[ar]')) || 
                    (event.target.closest && event.target.closest('button[slot=\"ar-button\"]')) || 
                    (event.target.closest && event.target.closest('.explorexr-ar-button'))
                )) {
                    // Mark potential AR session start
                    setTimeout(function() {
                        if (!document.body.classList.contains('explorexr-ar-session-active')) {
                            document.body.classList.add('explorexr-ar-session-active');
                            
                            // Trigger custom event
                            const modelEl = event.target.closest('model-viewer');
                            if (modelEl) {
                                const modelId = modelEl.id || 'unknown';
                                const arStartEvent = new CustomEvent('explorexr-ar-session-started', {
                                    detail: { 
                                        instanceId: modelId,
                                        modelUrl: modelEl.src || 'unknown'
                                    }
                                });
                                document.dispatchEvent(arStartEvent);
                            }
                        }
                    }, 100);
                }
            });
        }
    });
";
wp_add_inline_script('explorexr-model-viewer-wrapper', $ar_fix_js, 'after');

// Only add this filter once
static $filter_added = false;
if (!$filter_added) {
    add_filter('explorexr_model_viewer_attributes', 'explorexr_add_model_viewer_attributes', 10, 2);
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
        // Get settings from options
        $loading_display = get_option('explorexr_loading_display', 'bar');
        $loading_bar_color = get_option('explorexr_loading_bar_color', '#1e88e5');
        $loading_bar_size = get_option('explorexr_loading_bar_size', 'medium');
        $loading_bar_position = get_option('explorexr_loading_bar_position', 'middle');
        $percentage_font_size = get_option('explorexr_percentage_font_size', 24);
        $percentage_font_family = get_option('explorexr_percentage_font_family', 'Arial, sans-serif');
        $percentage_font_color = get_option('explorexr_percentage_font_color', '#333333');
        $percentage_position = get_option('explorexr_percentage_position', 'center-center');
        
        // Get custom loading text settings
        $loading_text = get_option('explorexr_loading_text', 'Loading 3D Model...');
        $loading_text_position = get_option('explorexr_loading_text_position', 'top-center');
        $loading_text_font_size = get_option('explorexr_loading_text_font_size', 16);
        $loading_text_font_family = get_option('explorexr_loading_text_font_family', 'Arial, sans-serif');
        $loading_text_font_color = get_option('explorexr_loading_text_font_color', '#333333');
        
        // Get overlay color and opacity settings
        $overlay_bg_color = get_option('explorexr_overlay_bg_color', '#FFFFFF');
        $overlay_bg_opacity = get_option('explorexr_overlay_bg_opacity', 70) / 100;
        
        // Get lazy loading settings
        $lazy_load_poster = get_option('explorexr_lazy_load_poster', false);
        $lazy_load_model = get_option('explorexr_lazy_load_model', false);
        
        // Add data attributes for JS to use
        $attributes['data-loading-display'] = $loading_display;
        $attributes['data-loading-bar-color'] = $loading_bar_color;
        $attributes['data-loading-bar-size'] = $loading_bar_size;
        $attributes['data-loading-bar-position'] = $loading_bar_position;
        $attributes['data-percentage-font-size'] = $percentage_font_size;
        $attributes['data-percentage-font-family'] = $percentage_font_family;
        $attributes['data-percentage-font-color'] = $percentage_font_color;
        $attributes['data-percentage-position'] = $percentage_position;
        
        // Add new custom loading text attributes
        $attributes['data-loading-text'] = $loading_text;
        $attributes['data-loading-text-position'] = $loading_text_position;
        $attributes['data-loading-text-font-size'] = $loading_text_font_size;
        $attributes['data-loading-text-font-family'] = $loading_text_font_family;
        $attributes['data-loading-text-font-color'] = $loading_text_font_color;
        
        // Add new overlay attributes
        $attributes['data-overlay-color'] = $overlay_bg_color;
        $attributes['data-overlay-opacity'] = $overlay_bg_opacity;
        
        // Add lazy loading attributes
        if ($lazy_load_poster) {
            $attributes['data-lazy-load-poster'] = 'true';
            $attributes['loading'] = 'lazy'; // Add native lazy loading attribute
        }
        
        if ($lazy_load_model) {
            $attributes['data-lazy-load-model'] = 'true';
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
        $model_viewer_version = get_option('explorexr_model_viewer_version', '3.3.0');
        $script_url = EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer-umd.js';
        
        // Check if local storage is preferred
        $cdn_source = get_option('explorexr_cdn_source', 'local');
        
        // Force local mode for WordPress.org compliance
        if ($cdn_source === 'cdn') {
            $cdn_source = 'local';
        }
        
        if ($cdn_source === 'local') {
            $local_umd_path = EXPLOREXR_PLUGIN_DIR . 'assets/js/model-viewer-umd.js';
            if (file_exists($local_umd_path)) {
                $script_url = EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer-umd.js';
            } else {
                // If local file doesn't exist, show error instead of using CDN
                return; // Don't output any script loader
            }
        } else {
            // CDN is disabled for WordPress.org compliance
            return; // Don't output any script loader
        }
        
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
                    console.warn("ExploreXR: Model viewer script could not be loaded from the selected source.");
                    
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





