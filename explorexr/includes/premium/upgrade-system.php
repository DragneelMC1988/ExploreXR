<?php
/**
 * Premium Upgrade System for ExploreXR
 * 
 * Handles upgrade prompts and premium feature detection
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if a premium feature is available
 * Always returns false in free version
 */
function expoxr_is_premium_feature_available($feature) {
    return false;
}

/**
 * Check if an addon is licensed
 * Always returns false in free version
 */
function expoxr_is_addon_licensed($addon) {
    return false;
}

/**
 * Check if Camera add-on is installed, active, and licensed
 * Always returns false in free version
 */
function expoxr_camera_addon_available() {
    return false;
}

/**
 * Check if AR add-on is installed, active, and licensed
 * Always returns false in free version
 */
function expoxr_ar_addon_available() {
    return false;
}

/**
 * Check if Animation add-on is installed, active, and licensed
 * Always returns false in free version
 */
function expoxr_animation_addon_available() {
    return false;
}

/**
 * Check if Annotations add-on is installed, active, and licensed
 * Always returns false in free version
 */
function expoxr_annotations_addon_available() {
    return false;
}

/**
 * Dummy function for addon license check
 * Always returns false in free version
 */
function expoxr_is_addon_licensed_function($addon) {
    return false;
}

// Alias the function for backward compatibility
if (!function_exists('expoxr_is_addon_licensed')) {
    function expoxr_is_addon_licensed($addon) {
        return expoxr_is_addon_licensed_function($addon);
    }
}

/**
 * Show admin notice to promote premium upgrade
 */
function expoxr_show_premium_upgrade_notice() {
    // Don't show on ExploreXR premium page to avoid redundancy
    $screen = get_current_screen();
    if ($screen && $screen->id === 'toplevel_page_expoxr-premium') {
        return;
    }
    
    $dismissed = get_user_meta(get_current_user_id(), 'expoxr_premium_notice_dismissed', true);
    
    if (!$dismissed) {
        ?>
        <div class="notice notice-info expoxr-premium-notice is-dismissible" data-notice="premium-upgrade">
            <div style="display: flex; align-items: center; gap: 15px; padding: 10px 0;">
                <div style="font-size: 32px;">🚀</div>
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 5px 0;">Unlock Advanced 3D Features</h3>
                    <p style="margin: 0;">Add AR and Camera Controls to your 3D models with ExploreXR Premium. Transform how users interact with your content!</p>
                </div>
                <div>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-premium')); ?>" class="button button-primary">Upgrade Now</a>
                </div>
            </div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $(document).on('click', '.expoxr-premium-notice .notice-dismiss', function() {
                $.post(ajaxurl, {
                    action: 'expoxr_dismiss_premium_notice',
                    nonce: '<?php echo esc_js(wp_create_nonce('expoxr_dismiss_notice')); ?>'
                });
            });
        });
        </script>
        <?php
    }
}

/**
 * Add premium upgrade metaboxes to model edit page
 */
function expoxr_add_premium_upgrade_metaboxes() {
    add_meta_box(
        'expoxr-premium-addons',
        '🚀 Premium Addons',
        'expoxr_premium_addons_metabox',
        'expoxr_model',
        'side',
        'high'
    );
}

/**
 * Premium addons metabox content
 */
function expoxr_premium_addons_metabox($post) {
        ?>
        <div class="expoxr-premium-metabox">
            <div class="premium-addon-list">
                <div class="premium-addon-item">
                    <span class="addon-icon">📱</span>
                    <span class="addon-name">AR Support</span>
                    <span class="premium-badge">Premium</span>
                </div>
                <div class="premium-addon-item">
                    <span class="addon-icon">📷</span>
                    <span class="addon-name">Camera Controls</span>
                    <span class="premium-badge">Premium</span>
                </div>
            </div>
            
            <div class="premium-upgrade-cta">
                <p><strong>Unlock these powerful features:</strong></p>
                <ul>
                    <li>✅ Interactive AR experiences</li>
                    <li>✅ Advanced camera controls</li>
                </ul>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-premium')); ?>" class="button button-primary button-large" style="width: 100%; text-align: center; margin-top: 15px;">
                    🚀 Upgrade to Premium
                </a>
                
                <p style="text-align: center; margin: 10px 0 0 0; font-size: 12px; color: #666;">
                    30-day money-back guarantee
                </p>
            </div>
        </div>
        
        <style>
        .expoxr-premium-metabox {
            padding: 15px;
        }
        
        .premium-addon-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .premium-addon-item:last-child {
            border-bottom: none;
        }
        
        .addon-icon {
            margin-right: 10px;
            font-size: 16px;
        }
        
        .addon-name {
            flex: 1;
            font-weight: 500;
        }
        
        .premium-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .premium-upgrade-cta ul {
            margin: 10px 0;
            padding-left: 0;
            list-style: none;
        }
        
        .premium-upgrade-cta li {
            padding: 3px 0;
            font-size: 13px;
        }
        </style>
        <?php
}

/**
 * Filter shortcode attributes to remove premium features
 */
function expoxr_filter_premium_shortcode_attributes($atts, $model_id) {
        // Remove premium attributes
        $premium_attributes = array('ar', 'camera-controls');
        
        foreach ($premium_attributes as $premium_attr) {
            if (isset($atts[$premium_attr])) {
                unset($atts[$premium_attr]);
            }
        }
        
        return $atts;
    }
    
/**
 * Add frontend upgrade prompts
 */
function expoxr_add_frontend_upgrade_prompts() {
        // Only add on pages with ExploreXR models
        if (!expoxr_has_expoxr_content()) {
            return;
        }
        
        ?>
        <div id="expoxr-premium-prompt" style="display: none;">
            <div class="expoxr-premium-overlay">
                <div class="expoxr-premium-popup">
                    <div class="premium-popup-header">
                        <h3>🚀 Premium Feature</h3>
                        <button class="close-popup" onclick="this.parentElement.parentElement.parentElement.style.display='none'">&times;</button>
                    </div>
                    <div class="premium-popup-content">
                        <p>This feature is available in <strong>ExploreXR Premium</strong>.</p>
                        <p>Upgrade now to unlock:</p>
                        <ul>
                            <li>✅ AR experiences</li>
                            <li>✅ Advanced controls</li>
                            <li>✅ And much more!</li>
                        </ul>
                        <a href="<?php echo esc_url(expoxr_get_premium_upgrade_url()); ?>" class="premium-upgrade-btn" target="_blank">
                            Upgrade to Premium
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .expoxr-premium-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .expoxr-premium-popup {
            background: white;
            border-radius: 12px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        
        .premium-popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .premium-popup-header h3 {
            margin: 0;
            font-size: 1.4em;
        }
        
        .close-popup {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }
        
        .premium-popup-content {
            padding: 20px 25px 25px;
        }
        
        .premium-popup-content ul {
            margin: 15px 0;
            padding-left: 0;
            list-style: none;
        }
        
        .premium-popup-content li {
            padding: 3px 0;
            font-size: 14px;
        }
        
        .premium-upgrade-btn {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 15px;
            transition: transform 0.2s;
        }
        
        .premium-upgrade-btn:hover {
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }
        </style>
        <?php
}

/**
 * Check if current page has ExploreXR content
 */
function expoxr_has_expoxr_content() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        // Check for shortcode in content
        if (has_shortcode($post->post_content, 'explorexr')) {
            return true;
        }
        
        // Check for ExploreXR blocks (if using Gutenberg)
        if (has_block('expoxr/model-viewer', $post)) {
            return true;
        }
        
        return false;
    }
    
/**
 * Get list of premium features
 */
function expoxr_get_premium_features() {
        return array(
            'ar' => array(
                'name' => 'AR Support',
                'description' => 'Augmented Reality experiences',
                'icon' => '📱'
            ),
            'camera-controls' => array(
                'name' => 'Camera Controls',
                'description' => 'Advanced viewing controls',
                'icon' => '📷'
            )
        );
    }

// Initialize the premium upgrade system hooks
add_action('admin_notices', 'expoxr_show_premium_upgrade_notice');
add_action('add_meta_boxes', 'expoxr_add_premium_upgrade_metaboxes');

// AJAX handler for dismissing premium notice
add_action('wp_ajax_expoxr_dismiss_premium_notice', function() {
    check_ajax_referer('expoxr_dismiss_notice', 'nonce');
    update_user_meta(get_current_user_id(), 'expoxr_premium_notice_dismissed', true);
    wp_die();
});

/**
 * Helper function to check if a feature is premium
 */
function expoxr_is_premium_feature($feature) {
    $premium_features = expoxr_get_premium_features();
    return array_key_exists($feature, $premium_features);
}

/**
 * Helper function to show premium upgrade message
 */
function expoxr_premium_upgrade_message($feature = '') {
    $features = expoxr_get_premium_features();
    $feature_name = isset($features[$feature]) ? $features[$feature]['name'] : 'This feature';
    
    return sprintf(
        '%s is available in <a href="%s" target="_blank"><strong>ExploreXR Premium</strong></a>. <a href="%s" target="_blank">Upgrade now</a> to unlock advanced 3D features.',
        $feature_name,
        admin_url('admin.php?page=expoxr-premium'),
        expoxr_get_premium_upgrade_url()
    );
}

/**
 * Reset premium notice dismissal for current user (for testing purposes)
 */
function expoxr_reset_premium_notice_dismissal() {
    delete_user_meta(get_current_user_id(), 'expoxr_premium_notice_dismissed');
}

/**
 * Stub class for license handler (premium-only feature)
 */
class ExpoXR_Free_License_Stub {
    public function is_pro_licensed() {
        return false;
    }
    
    public function get_license_info() {
        return array(
            'status' => 'free',
            'tier' => 'free',
            'type' => 'Free Version'
        );
    }
}

/**
 * Stub class for addon manager (premium-only feature)
 */
class ExpoXR_Free_Addon_Stub {
    public function get_registered_addons() {
        return array();
    }
    
    public function is_addon_active($slug) {
        return false;
    }
    
    public function get_addon($slug) {
        return null;
    }
}

/**
 * Stub function for addon manager (premium-only feature)
 * Returns stub object in free version
 */
function exploreXR_addon_manager() {
    static $stub = null;
    if ($stub === null) {
        $stub = new ExpoXR_Free_Addon_Stub();
    }
    return $stub;
}

/**
 * Stub function for license handler (premium-only feature) 
 * Returns stub object in free version
 */
function expoxr_license_handler() {
    static $stub = null;
    if ($stub === null) {
        $stub = new ExpoXR_Free_License_Stub();
    }
    return $stub;
}





