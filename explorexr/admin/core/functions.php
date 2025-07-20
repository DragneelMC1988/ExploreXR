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
    
    // Enqueue base admin styles first
    wp_enqueue_style(
        'explorexr-admin-common',
        EXPLOREXR_PLUGIN_URL . 'admin/css/admin-styles.css',
        array(),
        EXPLOREXR_VERSION
    );
    
    // Then enqueue the Edit Model specific styles - using higher priority to override WordPress defaults
    wp_enqueue_style(
        'explorexr-edit-model-css',
        EXPLOREXR_PLUGIN_URL . 'admin/css/edit-model.css', 
        array('explorexr-admin-common'), 
        EXPLOREXR_VERSION . '.' . time() // Add timestamp to force cache refresh during development
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
        EXPLOREXR_VERSION . '.' . time(), // Add timestamp to force cache refresh during development
        true // Load in footer
    );
    
    // Enqueue Edit Model Page Interactions JavaScript
    wp_enqueue_script(
        'explorexr-edit-model-page-interactions',
        EXPLOREXR_PLUGIN_URL . 'admin/js/edit-model-page-interactions.js',
        array('jquery', 'explorexr-edit-model-js'),
        EXPLOREXR_VERSION . '.' . time(),
        true // Load in footer
    );
    
    // Enqueue Premium Upgrade JavaScript for free version
    wp_enqueue_script(
        'explorexr-premium-upgrade',
        EXPLOREXR_PLUGIN_URL . 'admin/js/premium-upgrade.js',
        array('jquery', 'explorexr-edit-model-js'),
        EXPLOREXR_VERSION . '.' . time(),
        true // Load in footer
    );
    
    // Add Elementor compatibility script - load with highest priority to ensure it runs last
    wp_enqueue_script(
        'explorexr-elementor-compatibility',
        EXPLOREXR_PLUGIN_URL . 'admin/js/elementor-compatibility.js',
        array('jquery', 'explorexr-edit-model-js', 'explorexr-edit-model-page-interactions'),
        EXPLOREXR_VERSION . '.' . time(),
        true // Load in footer
    );
}
add_action('admin_enqueue_scripts', 'explorexr_enqueue_edit_model_styles', 100);






