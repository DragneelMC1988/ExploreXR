<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue styles for the Edit Model page
 * Uses appropriate WordPress hooks and checks to ensure styles are applied correctly
 */
function explorexr_enqueue_edit_model_styles() {
    // Only run in admin and specifically on our edit model page
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for conditional style loading only
    if (!is_admin() || !isset($_GET['page']) || sanitize_text_field(wp_unslash($_GET['page'])) !== 'explorexr-edit-model') {
        return;
    }
    
    // Cache-bust on every load only during development (SCRIPT_DEBUG);
    // otherwise use the plugin version so browsers can cache assets.
    $asset_version = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG)
        ? EXPLOREXR_VERSION . '.' . time()
        : EXPLOREXR_VERSION;

    // Enqueue base admin styles first
    wp_enqueue_style(
        'explorexr-admin-common',
        EXPLOREXR_PLUGIN_URL . 'admin/css/admin-styles.css',
        array(),
        EXPLOREXR_VERSION
    );
    // Core button + card styling reused across addon cards
    wp_enqueue_style(
        'explorexr-button-system',
        EXPLOREXR_PLUGIN_URL . 'admin/css/button-system.css',
        array('explorexr-admin-common'),
        EXPLOREXR_VERSION
    );
    wp_enqueue_style(
        'explorexr-addon-cards',
        EXPLOREXR_PLUGIN_URL . 'admin/css/addon-cards.css',
        array('explorexr-button-system'),
        EXPLOREXR_VERSION
    );
    wp_enqueue_style(
        'explorexr-addon-cards-shared',
        EXPLOREXR_PLUGIN_URL . 'admin/css/addon-cards-shared.css',
        array('explorexr-button-system'),
        EXPLOREXR_VERSION
    );
    
    // Then enqueue the Edit Model specific styles - using higher priority to override WordPress defaults
    wp_enqueue_style(
        'explorexr-edit-model-css',
        EXPLOREXR_PLUGIN_URL . 'admin/css/edit-model.css', 
        array('explorexr-admin-common'), 
        $asset_version
    );
    
    // Also load the create-model.css for consistent styling between create and edit pages
    wp_enqueue_style(
        'explorexr-create-model-css',
        EXPLOREXR_PLUGIN_URL . 'admin/css/create-model.css',
        array('explorexr-admin-common'),
        EXPLOREXR_VERSION
    );
    
    // Enqueue premium upgrade styles for free version
    wp_enqueue_style(
        'explorexr-premium-upgrade-css',
        EXPLOREXR_PLUGIN_URL . 'admin/css/premium-upgrade.css',
        array('explorexr-edit-model-css'),
        EXPLOREXR_VERSION
    );
    
    // Enqueue the Edit Model JavaScript
    wp_enqueue_script(
        'explorexr-edit-model-js',
        EXPLOREXR_PLUGIN_URL . 'admin/js/edit-model.js',
        array('jquery'),
        $asset_version,
        true // Load in footer
    );
    
    // Enqueue the unified display-size handler (single source of truth for size tab/radio logic)
    wp_enqueue_script(
        'explorexr-model-size',
        EXPLOREXR_PLUGIN_URL . 'assets/js/model-size.js',
        array('jquery'),
        $asset_version,
        true // Load in footer
    );
    
    // Enqueue Premium Upgrade JavaScript for free version
    wp_enqueue_script(
        'explorexr-premium-upgrade',
        EXPLOREXR_PLUGIN_URL . 'admin/js/premium-upgrade.js',
        array('jquery', 'explorexr-edit-model-js'),
        $asset_version,
        true // Load in footer
    );
    
    // Enqueue model-viewer for admin previews (already registered earlier)
    wp_enqueue_script('explorexr-premium-model-viewer');

    // Shared layout-controls (used by Materials and Annotations admin metaboxes).
    // Registered (not enqueued) so addons can declare it as a dependency.
    wp_register_style(
        'explorexr-layout-controls',
        EXPLOREXR_PLUGIN_URL . 'admin/css/layout-controls.css',
        array('explorexr-admin-common'),
        EXPLOREXR_VERSION
    );
    wp_register_script(
        'explorexr-layout-controls',
        EXPLOREXR_PLUGIN_URL . 'admin/js/layout-controls.js',
        array('jquery'),
        EXPLOREXR_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'explorexr_enqueue_edit_model_styles', 100);

/**
 * Register model-viewer script early so addons can enqueue it
 * Must run before any addon tries to enqueue it
 */
function explorexr_register_admin_model_viewer() {
    // Only register if not already registered
    if (!wp_script_is('explorexr-premium-model-viewer', 'registered')) {
        // Register guard script with check to prevent double-loading
        if (!wp_script_is('explorexr-premium-model-viewer-guard', 'registered')) {
            wp_register_script('explorexr-premium-model-viewer-guard', false, array(), EXPLOREXR_VERSION, true);
            $guard_script = '
                if (typeof window.explorexrModelViewerLoaded === "undefined") {
                    window.explorexrModelViewerLoaded = false;
                }
                // If model-viewer is already defined, prevent the main script from loading
                if (window.customElements && window.customElements.get("model-viewer")) {
                    if (typeof ExploreXRLogger !== "undefined") {
                        ExploreXRLogger.log("ExploreXR: model-viewer already defined, skipping duplicate load");
                    }
                    window.explorexrModelViewerLoaded = true;
                }
            ';
            wp_add_inline_script('explorexr-premium-model-viewer-guard', $guard_script, 'before');
        }
        
        // Add wrapper script to conditionally load model-viewer
        $model_viewer_src = function_exists('explorexr_model_viewer_script_url')
            ? explorexr_model_viewer_script_url()
            : EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer-umd.js';
        wp_register_script(
            'explorexr-premium-model-viewer',
            $model_viewer_src,
            array('explorexr-premium-model-viewer-guard'),
            '4.1.0',
            true // Load in footer
        );
        
        // Add inline script to prevent execution if already loaded
        $conditional_load = '
            if (window.explorexrModelViewerLoaded) {
                if (typeof ExploreXRLogger !== "undefined") {
                    ExploreXRLogger.log("ExploreXR: Skipping model-viewer load - already loaded");
                }
            }
        ';
        wp_add_inline_script('explorexr-premium-model-viewer', $conditional_load, 'before');
    }
}
add_action('admin_enqueue_scripts', 'explorexr_register_admin_model_viewer', 5);




