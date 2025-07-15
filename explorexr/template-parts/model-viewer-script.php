<?php
/**
 * Template part for including the model-viewer script in the frontend
 * 
 * @package ExpoXR
 */

// Don't load directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if model_id is needed and defined
if (!isset($model_id) && isset($_GET['model_id'])) {
    $model_id = intval($_GET['model_id']);
}

// Include loading options 
if (!function_exists('expoxr_get_loading_options')) {
    if (file_exists(EXPOXR_PLUGIN_DIR . 'admin/loading-options.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'admin/loading-options.php';
    } elseif (file_exists(EXPOXR_PLUGIN_DIR . 'admin/settings/loading-options.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'admin/settings/loading-options.php';
    }
}

// Define version fallback if EXPOXR_VERSION is not defined
if (!defined('EXPOXR_VERSION')) {
    define('EXPOXR_VERSION', '1.0.0');
}

// Get settings from options
$cdn_source = get_option('expoxr_cdn_source', 'local');

// Force local mode for WordPress.org compliance - override any CDN setting
if ($cdn_source === 'cdn') {
    $cdn_source = 'local';
    // Update the option to prevent future issues
    update_option('expoxr_cdn_source', 'local');
}
$model_viewer_version = get_option('expoxr_model_viewer_version', '3.3.0');

// Get the new loading options
$script_location = get_option('expoxr_script_location', 'footer');
$script_loading_timing = get_option('expoxr_script_loading_timing', 'auto');
$lazy_load_poster = get_option('expoxr_lazy_load_poster', false);
$lazy_load_model = get_option('expoxr_lazy_load_model', false);

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
    add_action('wp_footer', 'expoxr_add_ondemand_script_loader');
}

// Check if script has already been enqueued to prevent duplicates
$script_handle = 'model-viewer-script';
if (!wp_script_is($script_handle, 'enqueued')) {
    if ($cdn_source === 'local') {
        $local_umd_path = EXPOXR_PLUGIN_DIR . 'assets/js/model-viewer-umd.js';
        $local_min_path = EXPOXR_PLUGIN_DIR . 'assets/js/model-viewer.min.js';
        
        // If UMD version exists, use it (preferred for compatibility)
        if (file_exists($local_umd_path)) {
            wp_enqueue_script($script_handle, EXPOXR_PLUGIN_URL . 'assets/js/model-viewer-umd.js', array(), $model_viewer_version, $load_in_footer);
            
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
            wp_register_script($script_handle, EXPOXR_PLUGIN_URL . 'assets/js/model-viewer.min.js', array(), $model_viewer_version, $load_in_footer);
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
            
            // Log error for debugging
            if (function_exists('error_log') && get_option('expoxr_debug_mode', false)) {
                error_log('ExploreXR: Model Viewer script files not found. UMD: ' . $local_umd_path . ', Min: ' . $local_min_path);
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
        
        // Log error for debugging
        if (function_exists('error_log') && get_option('expoxr_debug_mode', false)) {
            error_log('ExploreXR: CDN source attempted but not allowed. WordPress.org Plugin Check requires local assets only.');
        }
        
        return; // Exit without loading any external script
    }
}

// Enqueue centralized loader manager first
wp_enqueue_script('expoxr-model-viewer-loader-manager', EXPOXR_PLUGIN_URL . 'assets/js/model-viewer-loader-manager.js', array(), EXPOXR_VERSION, true);

// Enqueue the custom model loader script
wp_enqueue_script('expoxr-model-loader', EXPOXR_PLUGIN_URL . 'assets/js/model-loader.js', array('jquery', 'expoxr-model-viewer-loader-manager'), EXPOXR_VERSION, true);

// Pass debug settings to model-loader script as well
$debug_mode = get_option('expoxr_debug_mode', false);
if ($debug_mode) {
    wp_localize_script('expoxr-model-loader', 'exploreXRDebug', array(
        'enabled' => true,
        'version' => EXPOXR_VERSION
    ));
}

// Enqueue model viewer wrapper for enhanced UI
wp_enqueue_script('expoxr-model-viewer-wrapper', EXPOXR_PLUGIN_URL . 'assets/js/model-viewer-wrapper.js', array('jquery', 'expoxr-model-viewer-loader-manager'), EXPOXR_VERSION, true);

// Pass loading options to the wrapper script
$loading_options = expoxr_get_loading_options();
wp_localize_script('expoxr-model-viewer-wrapper', 'expoxrLoadingOptions', $loading_options);

// Pass script configuration for preloader
$script_config = array();
if ($cdn_source === 'local') {
    $local_umd_path = EXPOXR_PLUGIN_DIR . 'assets/js/model-viewer-umd.js';
    if (file_exists($local_umd_path)) {
        $script_config['modelViewerScriptUrl'] = EXPOXR_PLUGIN_URL . 'assets/js/model-viewer-umd.js';
        $script_config['scriptType'] = 'umd';
    } else {
        $script_config['modelViewerScriptUrl'] = EXPOXR_PLUGIN_URL . 'assets/js/model-viewer.min.js';
        $script_config['scriptType'] = 'module';
    }
} else {
    // CDN UMD version for better compatibility
    $script_config['modelViewerScriptUrl'] = 'https://unpkg.com/@google/model-viewer@v' . $model_viewer_version . '/dist/model-viewer-umd.js';
    $script_config['scriptType'] = 'umd';
}
wp_localize_script('expoxr-model-viewer-wrapper', 'expoxrScriptConfig', $script_config);

// Enqueue model-handler.js for debugging features
wp_enqueue_script('expoxr-model-handler', EXPOXR_PLUGIN_URL . 'assets/js/model-handler.js', array('jquery'), EXPOXR_VERSION, true);

// Enqueue custom CSS
wp_enqueue_style('expoxr-model-viewer', EXPOXR_PLUGIN_URL . 'assets/css/model-viewer.css', array(), EXPOXR_VERSION);

// Add AR session CSS fixes
$ar_fix_css = "
    /* ExpoXR AR mode fixes */
    model-viewer {
        --poster-color: transparent;
    }
    
    /* Ensure model remains visible during AR session */
    .expoxr-ar-session-active model-viewer {
        visibility: visible !important;
        opacity: 1 !important;
        display: block !important;
    }
    
    /* iOS AR mode specific fixes */
    @supports (-webkit-touch-callout: none) {
        model-viewer::part(default-ar-button) {
            transform: scale(1.5);
        }
        
        .expoxr-ar-session-active {
            height: 100vh !important;
            overflow: hidden !important;
        }
    }
";
wp_add_inline_style('expoxr-model-viewer', $ar_fix_css);

// Add AR session handling JavaScript
$ar_fix_js = "
    // AR session event handling
    document.addEventListener('DOMContentLoaded', function() {
        // Function to handle AR session events globally
        function handleARSessions() {
            document.addEventListener('expoxr-ar-session-started', function(event) {
                console.log('AR session started for model:', event.detail.instanceId);
                document.body.classList.add('expoxr-ar-session-active');
                
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
            
            document.addEventListener('expoxr-ar-session-ended', function(event) {
                console.log('AR session ended for model:', event.detail.instanceId);
                document.body.classList.remove('expoxr-ar-session-active');
                
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
                    (event.target.closest && event.target.closest('.expoxr-ar-button'))
                )) {
                    // Mark potential AR session start
                    setTimeout(function() {
                        if (!document.body.classList.contains('expoxr-ar-session-active')) {
                            document.body.classList.add('expoxr-ar-session-active');
                            
                            // Trigger custom event
                            const modelEl = event.target.closest('model-viewer');
                            if (modelEl) {
                                const modelId = modelEl.id || 'unknown';
                                const arStartEvent = new CustomEvent('expoxr-ar-session-started', {
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
wp_add_inline_script('expoxr-model-viewer-wrapper', $ar_fix_js, 'after');

// Pass debugging settings to JavaScript
$debug_mode = get_option('expoxr_debug_mode', false);
$debug_ar = get_option('expoxr_debug_ar_features', false);
$debug_camera = get_option('expoxr_debug_camera_controls', false);
$debug_animations = get_option('expoxr_debug_animations', false);
$debug_annotations = get_option('expoxr_debug_annotations', false);
$debug_loading = get_option('expoxr_debug_loading_info', false);
$console_logging = get_option('expoxr_console_logging', false);

// Only add debug settings if at least one debug option is enabled
if ($debug_mode || $debug_ar || $debug_camera || $debug_animations || $debug_annotations || $debug_loading || $console_logging) {
    wp_localize_script('expoxr-model-handler', 'exploreXRDebug', array(
        'enabled' => $debug_mode ? true : false,
        'ar' => $debug_ar ? true : false,
        'camera' => $debug_camera ? true : false,
        'animations' => $debug_animations ? true : false,
        'annotations' => $debug_annotations ? true : false,
        'loading' => $debug_loading ? true : false,
        'version' => EXPOXR_VERSION
    ));
}

// Add debug information if debug mode is enabled
if ($debug_mode && current_user_can('manage_options')) {
    wp_add_inline_script($script_handle, "
        console.log('ExploreXR Debug Mode: ON');
        console.log('Model Viewer Version: " . esc_js($model_viewer_version) . "');
        console.log('Source: " . esc_js($cdn_source === 'local' ? 'Local' : 'CDN') . "');
    ", 'before');
}

// Only add this filter once
static $filter_added = false;
if (!$filter_added) {
    add_filter('expoxr_model_viewer_attributes', 'expoxr_add_model_viewer_attributes', 10, 2);
    $filter_added = true;
}

// Define the function only if it doesn't exist
if (!function_exists('expoxr_add_model_viewer_attributes')) {    /**
     * Add data attributes to the model viewer based on plugin settings
     * 
     * @param array $attributes Existing model viewer attributes
     * @param int $model_id Model ID (optional, for compatibility)
     * @return array Updated attributes
     */
    function expoxr_add_model_viewer_attributes($attributes, $model_id = null) {
        // Get settings from options
        $loading_display = get_option('expoxr_loading_display', 'bar');
        $loading_bar_color = get_option('expoxr_loading_bar_color', '#1e88e5');
        $loading_bar_size = get_option('expoxr_loading_bar_size', 'medium');
        $loading_bar_position = get_option('expoxr_loading_bar_position', 'middle');
        $percentage_font_size = get_option('expoxr_percentage_font_size', 24);
        $percentage_font_family = get_option('expoxr_percentage_font_family', 'Arial, sans-serif');
        $percentage_font_color = get_option('expoxr_percentage_font_color', '#333333');
        $percentage_position = get_option('expoxr_percentage_position', 'center-center');
        
        // Get custom loading text settings
        $loading_text = get_option('expoxr_loading_text', 'Loading 3D Model...');
        $loading_text_position = get_option('expoxr_loading_text_position', 'top-center');
        $loading_text_font_size = get_option('expoxr_loading_text_font_size', 16);
        $loading_text_font_family = get_option('expoxr_loading_text_font_family', 'Arial, sans-serif');
        $loading_text_font_color = get_option('expoxr_loading_text_font_color', '#333333');
        
        // Get overlay color and opacity settings
        $overlay_bg_color = get_option('expoxr_overlay_bg_color', '#FFFFFF');
        $overlay_bg_opacity = get_option('expoxr_overlay_bg_opacity', 70) / 100;
        
        // Get lazy loading settings
        $lazy_load_poster = get_option('expoxr_lazy_load_poster', false);
        $lazy_load_model = get_option('expoxr_lazy_load_model', false);
        
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
        
        // AR support attributes
        $ar_support_bg_color = get_option('expoxr_ar_background_color', '#FFFFFF');
        $ar_button_text = get_option('expoxr_ar_button_text', 'View in AR');
        $ar_fallback_text = get_option('expoxr_ar_fallback_text', 'AR not supported on this device');
        
        $attributes['data-ar-background'] = $ar_support_bg_color;
        $attributes['data-ar-button-text'] = $ar_button_text;
        $attributes['data-ar-fallback-text'] = $ar_fallback_text;
        
        return $attributes;
    }
}

// Add the on-demand script loader function
if (!function_exists('expoxr_add_ondemand_script_loader')) {
    /**
     * Add on-demand script loader for model-viewer
     * This function loads the model-viewer script only when needed
     */
    function expoxr_add_ondemand_script_loader() {
        $model_viewer_version = get_option('expoxr_model_viewer_version', '3.3.0');
        $script_url = 'https://unpkg.com/@google/model-viewer@v' . $model_viewer_version . '/dist/model-viewer-umd.js';
        
        // Check if local storage is preferred
        $cdn_source = get_option('expoxr_cdn_source', 'local');
        
        // Force local mode for WordPress.org compliance
        if ($cdn_source === 'cdn') {
            $cdn_source = 'local';
        }
        
        if ($cdn_source === 'local') {
            $local_umd_path = EXPOXR_PLUGIN_DIR . 'assets/js/model-viewer-umd.js';
            if (file_exists($local_umd_path)) {
                $script_url = EXPOXR_PLUGIN_URL . 'assets/js/model-viewer-umd.js';
            } else {
                // If local file doesn't exist, show error instead of using CDN
                if (get_option('expoxr_debug_mode', false)) {
                    error_log('ExploreXR: model-viewer-umd.js not found for on-demand loading');
                }
                return; // Don't output any script loader
            }
        } else {
            // CDN is disabled for WordPress.org compliance
            if (get_option('expoxr_debug_mode', false)) {
                error_log('ExploreXR: CDN usage attempted but disabled for WordPress.org compliance');
            }
            return; // Don't output any script loader
        }
        
        // Create the script loader JavaScript
        ?>
        <script>
        (function() {
            // Store references to model viewers on the page
            var expoxrModelViewers = document.querySelectorAll('.expoxr-model-viewer-container');
            var scriptLoaded = false;
            var scriptIsLoading = false;
            var modelViewersToInit = [];
            
            // Function to load the model-viewer script
            function loadModelViewerScript(callback) {
                if (scriptLoaded) {
                    if (typeof callback === 'function') callback();
                    return;
                }
                
                if (scriptIsLoading) {
                    // Add callback to queue if script is already loading
                    window.expoxrOnScriptLoad = window.expoxrOnScriptLoad || [];
                    if (typeof callback === 'function') {
                        window.expoxrOnScriptLoad.push(callback);
                    }
                    return;
                }
                
                scriptIsLoading = true;
                window.expoxrOnScriptLoad = window.expoxrOnScriptLoad || [];
                if (typeof callback === 'function') {
                    window.expoxrOnScriptLoad.push(callback);
                }
                
                // Create script element
                var script = document.createElement('script');
                script.src = '<?php echo esc_js($script_url); ?>';
                script.async = true;
                script.onload = function() {
                    scriptLoaded = true;
                    scriptIsLoading = false;
                    console.log('Model Viewer script loaded on demand');
                    
                    // Call all queued callbacks
                    if (window.expoxrOnScriptLoad && window.expoxrOnScriptLoad.length) {
                        window.expoxrOnScriptLoad.forEach(function(fn) {
                            if (typeof fn === 'function') fn();
                        });
                        window.expoxrOnScriptLoad = [];
                    }
                    
                    // Initialize all waiting model viewers
                    initQueuedModelViewers();
                };
                
                script.onerror = function() {
                    console.warn('ExploreXR: Model viewer script could not be loaded from the selected source.');
                    
                    // Try to show user-friendly notification if notification system is available
                    if (typeof window.expoXRCreateNotification !== 'undefined') {
                        window.expoXRCreateNotification(
                            'Model viewer is temporarily unavailable. Please check your internet connection or contact support.',
                            'error',
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
                if (container.dataset.initialized === 'true') {
                    return;
                }
                
                // Mark as initialized
                container.dataset.initialized = 'true';
                
                // Get the model URL from data attribute
                var modelUrl = container.dataset.modelUrl;
                if (!modelUrl) return;
                
                // Find the model-viewer element
                var modelViewerEl = container.querySelector('model-viewer');
                if (!modelViewerEl) return;
                
                // Set model source if not already set
                if (!modelViewerEl.hasAttribute('src') && modelUrl) {
                    modelViewerEl.setAttribute('src', modelUrl);
                }
                
                // Remove loading class
                container.classList.remove('expoxr-loading');
                container.classList.add('expoxr-loaded');
                
                // Trigger a custom event for further processing
                var event = new CustomEvent('expoxr-model-viewer-initialized', {
                    bubbles: true,
                    detail: { container: container, modelViewer: modelViewerEl }
                });
                container.dispatchEvent(event);
            }
            
            // Use Intersection Observer to detect when viewers come into view
            if ('IntersectionObserver' in window) {
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
                }, { rootMargin: '200px 0px' });
                
                // Start observing all model viewer containers
                expoxrModelViewers.forEach(function(container) {
                    observer.observe(container);
                });
            } else {
                // Fallback for browsers without Intersection Observer support
                loadModelViewerScript(function() {
                    expoxrModelViewers.forEach(function(container) {
                        initializeModelViewer(container);
                    });
                });
            }
            
            // Expose the loader function to the global scope for external access
            window.expoxrLoadModelViewerScript = loadModelViewerScript;
            
            // Make model initialization function available globally
            window.expoxrInitModelViewer = initializeModelViewer;
        })();
        </script>
        <?php
    }
}
?>





