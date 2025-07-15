<?php
/**
 * Plugin Name: ExploreXR
 * Plugin URI: https://expoxr.com/explorexr/
 * Description: Bring your website to life with interactive 3D models. ExploreXR lets you showcase GLB, GLTF, and USDZ files with ease — no coding required. Start free, upgrade anytime.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: Ayal Othman
 * Author URI: https://expoxr.de
 * Text Domain: explorexr
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * ExploreXR is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * 
 * ExploreXR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Prevent conflicts with premium version
if (defined('EXPOXR_VERSION') || class_exists('ExpoXR_License_Handler')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p><strong>ExploreXR:</strong> Cannot activate while ExploreXR Premium is active. Please deactivate the Premium version first.</p></div>';
    });
    return;
}

// Define plugin constants
define('EXPOXR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EXPOXR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EXPOXR_MODELS_DIR', EXPOXR_PLUGIN_DIR . 'models/');
define('EXPOXR_MODELS_URL', EXPOXR_PLUGIN_URL . 'models/');
define('EXPOXR_VERSION', '1.0.0');

// Development mode constant (set to false for production)
define('EXPOXR_DEV_MODE', false);

// Mark this as the free version
define('EXPOXR_IS_FREE', true);

// Create models directory if it doesn't exist
add_action('plugins_loaded', function () {
    // Ensure models directory exists
    if (!file_exists(EXPOXR_MODELS_DIR)) {
        wp_mkdir_p(EXPOXR_MODELS_DIR);
        
        // Create index.php for security
        $index_content = "<?php\n// Silence is golden.\n";
        file_put_contents(EXPOXR_MODELS_DIR . 'index.php', $index_content);
    }
    
    // Load all includes after WordPress is ready
    expoxr_free_load_includes();
});

function expoxr_free_load_includes() {
    // Load emergency script fix first to prevent WordPress corruption
    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/utils/emergency-script-fix.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/utils/emergency-script-fix.php';
    }

    // Core functionality
    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/core/post-types/class-post-types.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/core/post-types/class-post-types.php';
    }
    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/core/post-types.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/core/post-types.php';
    }
    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/core/shortcodes.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/core/shortcodes.php';
    }
    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/core/model-validator.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/core/model-validator.php';
    }

    // Models functionality
    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/models/file-handler.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/models/file-handler.php';
    }
    
    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/models/model-helper.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/models/model-helper.php';
    }
    
    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/models/model-cleanup.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/models/model-cleanup.php';
    }

    // UI components
    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/ui/form-submission-handler.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/ui/form-submission-handler.php';
    }

    // Admin functionality
    if (file_exists(EXPOXR_PLUGIN_DIR . 'admin/core/admin-menu.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'admin/core/admin-menu.php';
    }

    // Load admin pages (required by admin menu)
    if (file_exists(EXPOXR_PLUGIN_DIR . 'admin/core/admin-pages.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'admin/core/admin-pages.php';
    }

    // AJAX handlers
    if (file_exists(EXPOXR_PLUGIN_DIR . 'admin/ajax/ajax-handlers.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'admin/ajax/ajax-handlers.php';
    }

    // Integrations removed from Free version
    // Free version only supports shortcode usage
    // For WooCommerce and Elementor integration, upgrade to Premium

    // Security (basic level for free version)
    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/security/security-handler.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/security/security-handler.php';
    }

    // Utils
    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/utils/safe-string-ops.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/utils/safe-string-ops.php';
    }

    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/utils/debugging.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/utils/debugging.php';
        // Initialize enhanced debugging system
        expoxr_init_debugging();
    }

    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/utils/strip-tags-fix.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/utils/strip-tags-fix.php';
    }

    // Premium upgrade system for free version
    if (file_exists(EXPOXR_PLUGIN_DIR . 'includes/premium/upgrade-system.php')) {
        require_once EXPOXR_PLUGIN_DIR . 'includes/premium/upgrade-system.php';
    }
}

// Register activation hook
register_activation_hook(__FILE__, 'expoxr_free_activate');

/**
 * Plugin activation function
 */
function expoxr_free_activate() {
    // Create models directory
    if (!file_exists(EXPOXR_MODELS_DIR)) {
        wp_mkdir_p(EXPOXR_MODELS_DIR);
    }
    
    // Set default options
    add_option('expoxr_free_activated', true);
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Register deactivation hook
register_deactivation_hook(__FILE__, 'expoxr_free_deactivate');

/**
 * Plugin deactivation function
 */
function expoxr_free_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Helper function to check if premium is available
function expoxr_is_premium_available() {
    return false; // Always false in free version
}

// Helper function to get premium upgrade URL
function expoxr_get_premium_upgrade_url() {
    return 'https://expoxr.com/explorexr/premium/';
}






