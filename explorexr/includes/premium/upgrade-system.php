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
function explorexr_is_premium_feature_available($feature) {
    return false;
}

/**
 * Show admin notice to promote premium upgrade
 */
function explorexr_show_premium_upgrade_notice() {
    // Only show on ExploreXR admin pages
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'explorexr') === false) {
        return;
    }
    
    // Don't show on ExploreXR premium page to avoid redundancy
    if ($screen && $screen->id === 'toplevel_page_explorexr-premium') {
        return;
    }
    
    $dismissed = get_user_meta(get_current_user_id(), 'explorexr_premium_notice_dismissed', true);
    
    if (!$dismissed) {
        ?>
        <div class="notice notice-info explorexr-premium-notice is-dismissible" data-notice="premium-upgrade">
            <div style="display: flex; align-items: center; gap: 15px; padding: 10px 0;">
                <div style="font-size: 32px;">🚀</div>
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 5px 0;">Unlock Advanced 3D Features</h3>
                    <p style="margin: 0;">Add AR and Camera Controls to your 3D models with ExploreXR Premium. Transform how users interact with your content!</p>
                </div>
                <div>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-premium')); ?>" class="button button-primary">Upgrade Now</a>
                </div>
            </div>
        </div>
        <?php
    }
}

/**
 * Add premium upgrade metaboxes to model edit page
 */
function explorexr_add_premium_upgrade_metaboxes() {
    add_meta_box(
        'explorexr-premium-features',
        '🚀 Premium Features',
        'explorexr_premium_features_metabox',
        'explorexr_model',
        'side',
        'high'
    );
}

/**
 * Premium features metabox content
 */
function explorexr_premium_features_metabox($post) {
        ?>
        <div class="explorexr-premium-metabox">
            <div class="premium-feature-list">
                <div class="premium-feature-item">
                    <span class="feature-icon">📱</span>
                    <span class="feature-name">AR Support</span>
                    <span class="premium-badge">Premium</span>
                </div>
                <div class="premium-feature-item">
                    <span class="feature-icon">📷</span>
                    <span class="feature-name">Camera Controls</span>
                    <span class="premium-badge">Premium</span>
                </div>
                <div class="premium-feature-item">
                    <span class="feature-icon">🎬</span>
                    <span class="feature-name">Animations</span>
                    <span class="premium-badge">Premium</span>
                </div>
                <div class="premium-feature-item">
                    <span class="feature-icon">💬</span>
                    <span class="feature-name">Annotations</span>
                    <span class="premium-badge">Premium</span>
                </div>
            </div>
            
            <div class="premium-upgrade-cta">
                <p><strong>Unlock powerful features:</strong></p>
                <ul>
                    <li>✅ Interactive AR experiences</li>
                    <li>✅ Advanced camera controls</li>
                    <li>✅ Model animations</li>
                    <li>✅ Information hotspots</li>
                    <li>✅ And much more!</li>
                </ul>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-premium')); ?>" class="button button-primary button-large" style="width: 100%; text-align: center; margin-top: 15px;">
                    🚀 Upgrade to Premium
                </a>
                
                <p style="text-align: center; margin: 10px 0 0 0; font-size: 12px; color: #666;">
                    30-day money-back guarantee
                </p>
            </div>
        </div>
        <?php
}

/**
 * Filter shortcode attributes to remove premium features
 */
function explorexr_filter_premium_shortcode_attributes($atts, $model_id) {
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
function explorexr_add_frontend_upgrade_prompts() {
        // Only add on pages with ExploreXR models
        if (!explorexr_has_explorexr_content()) {
            return;
        }
        
        ?>
        <div id="explorexr-premium-prompt" style="display: none;">
            <div class="explorexr-premium-overlay">
                <div class="explorexr-premium-popup">
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
                        <a href="<?php echo esc_url(explorexr_get_premium_upgrade_url()); ?>" class="premium-upgrade-btn" target="_blank">
                            Upgrade to Premium
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
}

/**
 * Check if current page has ExploreXR content
 */
function explorexr_has_explorexr_content() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        // Check for shortcode in content
        if (has_shortcode($post->post_content, 'explorexr')) {
            return true;
        }
        
        // Check for ExploreXR blocks (if using Gutenberg)
        if (has_block('explorexr/model-viewer', $post)) {
            return true;
        }
        
        return false;
    }
    
/**
 * Get list of premium features
 */
function explorexr_get_premium_features() {
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
add_action('admin_notices', 'explorexr_show_premium_upgrade_notice');
add_action('add_meta_boxes', 'explorexr_add_premium_upgrade_metaboxes');

// AJAX handler for dismissing premium notice
add_action('wp_ajax_explorexr_dismiss_premium_notice', function() {
    check_ajax_referer('explorexr_dismiss_notice', 'nonce');
    update_user_meta(get_current_user_id(), 'explorexr_premium_notice_dismissed', true);
    wp_die();
});

/**
 * Helper function to check if a feature is premium
 */
function explorexr_is_premium_feature($feature) {
    $premium_features = explorexr_get_premium_features();
    return array_key_exists($feature, $premium_features);
}

/**
 * Helper function to show premium upgrade message
 */
function explorexr_premium_upgrade_message($feature = '') {
    $features = explorexr_get_premium_features();
    $feature_name = isset($features[$feature]) ? $features[$feature]['name'] : 'This feature';
    
    return sprintf(
        '%s is available in <a href="%s" target="_blank"><strong>ExploreXR Premium</strong></a>. <a href="%s" target="_blank">Upgrade now</a> to unlock advanced 3D features.',
        $feature_name,
        admin_url('admin.php?page=explorexr-premium'),
        explorexr_get_premium_upgrade_url()
    );
}

/**
 * Reset premium notice dismissal for current user (for testing purposes)
 */
function explorexr_reset_premium_notice_dismissal() {
    delete_user_meta(get_current_user_id(), 'explorexr_premium_notice_dismissed');
}

/**
 * Stub class for license handler (premium-only feature)
 */
class explorexr_free_License_Stub {
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
 * Stub function for license handler (premium-only feature) 
 * Returns stub object in free version
 */
function explorexr_license_handler() {
    static $stub = null;
    if ($stub === null) {
        $stub = new explorexr_free_License_Stub();
    }
    return $stub;
}





