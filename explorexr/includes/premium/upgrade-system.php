<?php
/**
 * Premium Upgrade System for ExploreXR
 * 
 * Handles upgrade prompts and premium feature detection.
 *
 * @package ExploreXR
 * @since 1.1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if a premium feature is available.
 * Always returns false in the free version.
 *
 * @param string $feature Feature/addon slug.
 * @return bool
 */
function explorexr_is_premium_feature_available($feature) {
    return false;
}

/**
 * Add premium upgrade metaboxes to model edit page.
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
 * Premium features metabox content.
 *
 * @param WP_Post $post Current post object.
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
                <span class="feature-icon">🎬</span>
                <span class="feature-name">Animations</span>
                <span class="premium-badge">Premium</span>
            </div>
            <div class="premium-feature-item">
                <span class="feature-icon">💬</span>
                <span class="feature-name">Annotations</span>
                <span class="premium-badge">Premium</span>
            </div>
            <div class="premium-feature-item">
                <span class="feature-icon">📷</span>
                <span class="feature-name">Expert Camera</span>
                <span class="premium-badge">Premium</span>
            </div>
            <div class="premium-feature-item">
                <span class="feature-icon">🌅</span>
                <span class="feature-name">Environment</span>
                <span class="premium-badge">Premium</span>
            </div>
            <div class="premium-feature-item">
                <span class="feature-icon">🎨</span>
                <span class="feature-name">Materials</span>
                <span class="premium-badge">Premium</span>
            </div>
            <div class="premium-feature-item">
                <span class="feature-icon">✨</span>
                <span class="feature-name">Post-Processing</span>
                <span class="premium-badge">Premium</span>
            </div>
            <div class="premium-feature-item">
                <span class="feature-icon">🛒</span>
                <span class="feature-name">WooCommerce</span>
                <span class="premium-badge">Premium</span>
            </div>
        </div>
        
        <div class="premium-upgrade-cta">
            <p><strong>Unlock 12 powerful addons:</strong></p>
            <ul>
                <li>✅ AR, Animations, Annotations</li>
                <li>✅ Expert Camera & Environment</li>
                <li>✅ Materials, Morphing, Mouse3D</li>
                <li>✅ Post-Processing & WooCommerce</li>
                <li>✅ Draggable, Loading Options</li>
                <li>✅ Debug Toolkit (always free)</li>
            </ul>
            
            <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-premium')); ?>" class="button button-primary button-large" style="width: 100%; text-align: center; margin-top: 15px;">
                🚀 Try Premium Free
            </a>
            <p style="text-align: center; margin: 8px 0 0 0; font-size: 11px; color: #666;">
                14-day free trial at ExpoXR.com
            </p>
        </div>
    </div>
    <?php
}

/**
 * Filter shortcode attributes to remove premium features.
 *
 * @param array $atts       Shortcode attributes.
 * @param int   $model_id   Model post ID.
 * @return array Filtered attributes.
 */
function explorexr_filter_premium_shortcode_attributes($atts, $model_id) {
    $premium_attributes = array('ar', 'camera-controls');
    
    foreach ($premium_attributes as $premium_attr) {
        if (isset($atts[$premium_attr])) {
            unset($atts[$premium_attr]);
        }
    }
    
    return $atts;
}
    
/**
 * Add frontend upgrade prompts.
 */
function explorexr_add_frontend_upgrade_prompts() {
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
                    <p>Try it free for 14 days or upgrade now:</p>
                    <ul>
                        <li>✅ 12 powerful addons</li>
                        <li>✅ AR, Animations, Annotations</li>
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
 * Check if current page has ExploreXR content.
 *
 * @return bool
 */
function explorexr_has_explorexr_content() {
    global $post;
    
    if (!$post) {
        return false;
    }
    
    if (has_shortcode($post->post_content, 'explorexr')) {
        return true;
    }
    
    if (has_block('explorexr/model-viewer', $post)) {
        return true;
    }
    
    return false;
}
    
/**
 * Get list of premium features.
 *
 * @return array
 */
function explorexr_get_premium_features() {
    return array(
        'ar' => array(
            'name'        => 'AR (Augmented Reality)',
            'description' => 'Place 3D models in the real world via mobile browsers',
            'icon'        => '📱',
        ),
        'animation' => array(
            'name'        => 'Animation',
            'description' => 'Play, control, and switch animation clips',
            'icon'        => '🎬',
        ),
        'annotations' => array(
            'name'        => 'Annotations',
            'description' => 'Interactive hotspots with 4 annotation types',
            'icon'        => '💬',
        ),
        'camera-mode' => array(
            'name'        => 'Expert Camera Mode',
            'description' => 'Advanced camera constraints and sensitivity',
            'icon'        => '📷',
        ),
        'environment' => array(
            'name'        => 'Environment & Lighting',
            'description' => 'HDRI lighting, tone mapping, shadows',
            'icon'        => '🌅',
        ),
        'materials' => array(
            'name'        => 'Materials & Variants',
            'description' => 'Real-time material and texture switching',
            'icon'        => '🎨',
        ),
        'loading-options' => array(
            'name'        => 'Loading Options',
            'description' => 'Custom loading bars, overlays, lazy loading',
            'icon'        => '⏳',
        ),
        'woocommerce' => array(
            'name'        => 'WooCommerce',
            'description' => 'Deep WooCommerce product integration',
            'icon'        => '🛒',
        ),
        'morphing' => array(
            'name'        => 'Morphing',
            'description' => 'Model-to-model transitions with 5 styles',
            'icon'        => '🔄',
        ),
        'mouse3d' => array(
            'name'        => 'Mouse3D Control',
            'description' => 'Cursor-driven 3D model interaction',
            'icon'        => '🎯',
        ),
        'post-processing' => array(
            'name'        => 'Post-Processing Filters',
            'description' => 'Bloom, DOF, SSAO, SSR visual effects',
            'icon'        => '✨',
        ),
        'draggable' => array(
            'name'        => 'Draggable',
            'description' => 'Floating, repositionable viewer panel',
            'icon'        => '🖱️',
        ),
        'debug-toolkit' => array(
            'name'        => 'Debug Toolkit',
            'description' => '12 diagnostic tools — no license required',
            'icon'        => '🛠️',
        ),
    );
}

/**
 * Show premium upgrade notice in admin.
 * Trial-aware: shows trial status when active/expired, or trial CTA when not started.
 */
function explorexr_show_premium_upgrade_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (get_user_meta(get_current_user_id(), 'explorexr_premium_notice_dismissed', true)) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || empty($screen->id) || strpos($screen->id, 'explorexr') === false) {
        return;
    }
    
    ?>
    <div class="notice notice-info is-dismissible" id="explorexr-premium-notice">
        <p><strong>🚀 ExploreXR Premium:</strong> Try 12 powerful addons free for 14 days — AR, Animations, Annotations, and more. <a href="<?php echo esc_url(explorexr_get_premium_upgrade_url()); ?>" target="_blank">Get Your Free Trial →</a></p>
    </div>
    <?php
    $premium_notice_script = '
    jQuery(document).ready(function($) {
        $("#explorexr-premium-notice").on("click", ".notice-dismiss", function() {
            $.post(ajaxurl, {
                action: "explorexr_dismiss_premium_notice",
                nonce: "' . esc_js(wp_create_nonce('explorexr_dismiss_notice')) . '"
            });
        });
    });
    ';
    wp_add_inline_script('jquery', $premium_notice_script);
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
 * Helper function to check if a feature is premium.
 *
 * @param string $feature Feature slug.
 * @return bool
 */
function explorexr_is_premium_feature($feature) {
    $premium_features = explorexr_get_premium_features();
    return array_key_exists($feature, $premium_features);
}

/**
 * Helper function to show premium upgrade message.
 *
 * @param string $feature Feature slug.
 * @return string HTML upgrade message.
 */
function explorexr_premium_upgrade_message($feature = '') {
    $features     = explorexr_get_premium_features();
    $feature_name = isset($features[$feature]) ? $features[$feature]['name'] : 'This feature';

    return sprintf(
        '%s is available in <a href="%s"><strong>ExploreXR Premium</strong></a>. <a href="%s" target="_blank">Try it free for 14 days</a> or <a href="%s" target="_blank">upgrade now</a>.',
        esc_html($feature_name),
        esc_url(admin_url('admin.php?page=explorexr-premium')),
        esc_url(explorexr_get_premium_upgrade_url()),
        esc_url(explorexr_get_premium_upgrade_url())
    );
}

/**
 * Reset premium notice dismissal for current user (for testing purposes).
 */
function explorexr_reset_premium_notice_dismissal() {
    delete_user_meta(get_current_user_id(), 'explorexr_premium_notice_dismissed');
}

/**
 * Stub class for license handler (premium-only feature).
 */
class explorexr_free_License_Stub {
    public function is_pro_licensed() {
        return false;
    }
    
    public function get_license_info() {
        return array(
            'status' => 'free',
            'tier'   => 'free',
            'type'   => 'Free Version',
        );
    }
}

/**
 * Stub function for license handler (premium-only feature).
 * Returns stub object in free version.
 *
 * @return explorexr_free_License_Stub
 */
function explorexr_license_handler() {
    static $stub = null;
    if ($stub === null) {
        $stub = new explorexr_free_License_Stub();
    }
    return $stub;
}





