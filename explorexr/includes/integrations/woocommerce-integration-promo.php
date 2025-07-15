<?php
/**
 * WooCommerce Integration for ExploreXR Free
 * 
 * This file provides information about premium WooCommerce features
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if WooCommerce is active
 */
function expoxr_is_woocommerce_active() {
    return class_exists('WooCommerce');
}

/**
 * Display admin notice about premium WooCommerce features
 */
function expoxr_woocommerce_addon_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Only show on WooCommerce product pages
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->id, ['product', 'edit-product'])) {
        return;
    }
    
    ?>
    <div class="notice notice-info is-dismissible">
        <p>
            <strong>ExploreXR:</strong> Add 3D models to your WooCommerce products with ExploreXR Premium! 
            <a href="<?php echo esc_url(expoxr_get_premium_upgrade_url()); ?>" target="_blank">Upgrade now</a> 
            to unlock WooCommerce integration and many more features.
        </p>
    </div>
    <?php
}

/**
 * Initialize WooCommerce integration
 */
function expoxr_init_woocommerce_integration() {
    if (!expoxr_is_woocommerce_active()) {
        return;
    }
    
    // Add admin notice about premium features
    add_action('admin_notices', 'expoxr_woocommerce_addon_notice');
}
add_action('init', 'expoxr_init_woocommerce_integration');





