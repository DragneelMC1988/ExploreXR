<?php
/**
 * ExploreXR Admin Notifications Area Template - WordPress.org Compliant
 * 
 * This template only provides minimal ExploreXR-specific notifications
 * WordPress core admin notices are handled entirely by WordPress's native system
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// WordPress.org Compliance: Do not create custom notice containers
// WordPress handles all notice positioning and display automatically
// Only show ExploreXR-specific success/error messages if passed via URL parameters

// Check for plugin-specific messages that don't interfere with WordPress notices
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display of URL parameters
if (isset($_GET['explorexr-settings-updated']) && sanitize_text_field(wp_unslash($_GET['explorexr-settings-updated'])) == 'true') {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-success is-dismissible">
                <p><strong>ExploreXR Settings saved successfully!</strong></p>
              </div>';
    });
}

// Check for ExploreXR error messages
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display of URL parameters
if (isset($_GET['explorexr-error']) && !empty($_GET['explorexr-error'])) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display of URL parameters
    $error_message = sanitize_text_field(wp_unslash($_GET['explorexr-error']));
    add_action('admin_notices', function() use ($error_message) {
        echo '<div class="notice notice-error is-dismissible">
                <p><strong>ExploreXR Error:</strong> ' . esc_html($error_message) . '</p>
              </div>';
    });
}

// Check for ExploreXR success messages
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display of URL parameters
if (isset($_GET['explorexr-success']) && !empty($_GET['explorexr-success'])) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display of URL parameters
    $success_message = sanitize_text_field(wp_unslash($_GET['explorexr-success']));
    add_action('admin_notices', function() use ($success_message) {
        echo '<div class="notice notice-success is-dismissible">
                <p><strong>ExploreXR Success:</strong> ' . esc_html($success_message) . '</p>
              </div>';
    });
}

// WordPress.org Compliance: Do not manipulate WordPress notice positioning
// All notices will appear in WordPress's standard notice area automatically





