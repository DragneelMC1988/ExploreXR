<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue styles for the Edit Model page
 * Uses appropriate WordPress hooks and checks to ensure styles are applied correctly
 */
function expoxr_enqueue_edit_model_styles() {
    // Only run in admin and specifically on our edit model page
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for conditional style loading only
    if (!is_admin() || !isset($_GET['page']) || sanitize_text_field(wp_unslash($_GET['page'])) !== 'expoxr-edit-model') {
        return;
    }
    
    // Enqueue base admin styles first
    wp_enqueue_style(
        'expoxr-admin-common',
        EXPOXR_PLUGIN_URL . 'admin/css/admin-styles.css',
        array(),
        EXPOXR_VERSION
    );
    
    // Then enqueue the Edit Model specific styles - using higher priority to override WordPress defaults
    wp_enqueue_style(
        'expoxr-edit-model-css',
        EXPOXR_PLUGIN_URL . 'admin/css/edit-model.css', 
        array('expoxr-admin-common'), 
        EXPOXR_VERSION . '.' . time() // Add timestamp to force cache refresh during development
    );
    
    // Also load the create-model.css for consistent styling between create and edit pages
    wp_enqueue_style(
        'expoxr-create-model-css',
        EXPOXR_PLUGIN_URL . 'admin/css/create-model.css',
        array('expoxr-admin-common'),
        EXPOXR_VERSION
    );
    
    // Enqueue premium upgrade styles for free version
    wp_enqueue_style(
        'expoxr-premium-upgrade-css',
        EXPOXR_PLUGIN_URL . 'admin/css/premium-upgrade.css',
        array('expoxr-edit-model-css'),
        EXPOXR_VERSION
    );
    
    // Enqueue the Edit Model JavaScript
    wp_enqueue_script(
        'expoxr-edit-model-js',
        EXPOXR_PLUGIN_URL . 'admin/js/edit-model.js',
        array('jquery'),
        EXPOXR_VERSION . '.' . time(), // Add timestamp to force cache refresh during development
        true // Load in footer
    );
    
    // Enqueue Edit Model Page Interactions JavaScript
    wp_enqueue_script(
        'expoxr-edit-model-page-interactions',
        EXPOXR_PLUGIN_URL . 'admin/js/edit-model-page-interactions.js',
        array('jquery', 'expoxr-edit-model-js'),
        EXPOXR_VERSION . '.' . time(),
        true // Load in footer
    );
    
    // Enqueue Premium Upgrade JavaScript for free version
    wp_enqueue_script(
        'expoxr-premium-upgrade',
        EXPOXR_PLUGIN_URL . 'admin/js/premium-upgrade.js',
        array('jquery', 'expoxr-edit-model-js'),
        EXPOXR_VERSION . '.' . time(),
        true // Load in footer
    );
    
    // Add Elementor compatibility script - load with highest priority to ensure it runs last
    wp_enqueue_script(
        'expoxr-elementor-compatibility',
        EXPOXR_PLUGIN_URL . 'admin/js/elementor-compatibility.js',
        array('jquery', 'expoxr-edit-model-js', 'expoxr-edit-model-page-interactions'),
        EXPOXR_VERSION . '.' . time(),
        true // Load in footer
    );
}
add_action('admin_enqueue_scripts', 'expoxr_enqueue_edit_model_styles', 100);






