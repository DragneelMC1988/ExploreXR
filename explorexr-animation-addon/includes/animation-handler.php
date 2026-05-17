<?php
/**
 * ExploreXR Animation Add-On - Animation Handler
 * 
 * Provides advanced animation functionality for the ExploreXR plugin.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Apply advanced animation attributes to model viewer
 */
function explorexr_premium_animation_get_show_frontend_controls($model_id) {
    global $wpdb;

    $meta_value = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_explorexr_premium_animation_show_frontend_controls' LIMIT 1",
            $model_id
        )
    );

    if ($meta_value === null) {
        $meta_value = '';
    }

    if ($meta_value === '') {
        $legacy_value = get_post_meta($model_id, '_explorexr_animation_show_controls', true);
        if ($legacy_value === '') {
            return true;
        }
        return ($legacy_value === '1' || $legacy_value === 'on' || $legacy_value === true);
    }

    return !in_array($meta_value, array('off', '0', 'false'), true);
}

function explorexr_premium_animation_apply_advanced_attributes($attributes, $model_id) {
    // CRITICAL: Clear cache and get fresh value from database
    // This fixes stale cache issue where save works but filter gets old value
    wp_cache_delete($model_id, 'post_meta');
    
    global $wpdb;
    $db_value = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_explorexr_premium_animation_enabled' LIMIT 1", $model_id));
    
    // Use database value directly instead of cached get_post_meta
    $normalized_premium_enabled = is_string($db_value) ? strtolower(trim($db_value)) : $db_value;
    $premium_enabled = in_array($normalized_premium_enabled, array('on', '1', 'true'), true);
    $premium_disabled = in_array($normalized_premium_enabled, array('off', '0', 'false'), true);
    
    $animation_enabled = $premium_enabled;
    
    // If premium setting is not present, fall back to legacy and derived values
    if (!$premium_enabled && !$premium_disabled) {
        // Legacy key for backward compatibility
        $legacy_value = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_explorexr_animation_enable' LIMIT 1", $model_id));
        $legacy_enabled = in_array($legacy_value, array('1', 'on', 'true'), true);
        
        // If legacy is not set, infer enablement from other animation fields
        if (!$legacy_enabled) {
            $selected_animations = get_post_meta($model_id, '_explorexr_premium_selected_animations', true);
            $has_selected_animations = is_array($selected_animations) ? !empty($selected_animations) : ($selected_animations !== '');
            $animation_name = get_post_meta($model_id, '_explorexr_premium_animation_name', true);
            
            $legacy_enabled = (
                get_post_meta($model_id, '_explorexr_premium_animation_autoplay', true) === 'on' ||
                get_post_meta($model_id, '_explorexr_premium_animation_loop', true) === 'on' ||
                get_post_meta($model_id, '_explorexr_premium_multiple_animations_enabled', true) === 'on' ||
                !empty($animation_name) ||
                $has_selected_animations
            );
        }
        
        $animation_enabled = $legacy_enabled;
    }
    
    if ($animation_enabled) {
        // Animation name - check premium key first, then legacy
        $animation_name = get_post_meta($model_id, '_explorexr_premium_animation_name', true);
        if (empty($animation_name)) {
            $animation_name = get_post_meta($model_id, '_explorexr_animation_name', true);
        }
        
        // CRITICAL FIX: When animation is enabled, ALWAYS add animation-name
        // Empty or missing name → use '*' (model-viewer plays ALL animations)
        // 'static' → skip animation entirely
        if ($animation_name === 'static') {
            // Skip animation attributes
        } else {
            // CRITICAL FIX: Use '*' for empty/missing animation names to play ALL animations
            // This is the model-viewer default behavior and should work even with empty DB values
            $final_animation_name = !empty($animation_name) ? $animation_name : '*';
            $attributes['animation-name'] = $final_animation_name;
            
            // Animation autoplay - check premium key first, then legacy, default off
            $autoplay_meta = get_post_meta($model_id, '_explorexr_premium_animation_autoplay', true);
            if ($autoplay_meta === '') {
                $legacy_autoplay = get_post_meta($model_id, '_explorexr_animation_autoplay', true);
                $animation_autoplay = $legacy_autoplay !== '' ? ($legacy_autoplay === '1') : false;
            } else {
            $animation_autoplay = in_array($autoplay_meta, array('on', '1', 'true'), true);
            }
            
            if ($animation_autoplay) {
                $attributes['autoplay'] = '';
            }
            
            // Animation loop - check premium key first, then legacy, default on
            $loop_meta = get_post_meta($model_id, '_explorexr_premium_animation_loop', true);
            if ($loop_meta === '') {
                $legacy_loop = get_post_meta($model_id, '_explorexr_animation_loop', true);
                $animation_loop = $legacy_loop !== '' ? ($legacy_loop === '1') : true;
            } else {
            $animation_loop = in_array($loop_meta, array('on', '1', 'true'), true);
            }
            
            if ($animation_loop) {
                $attributes['loop'] = '';
            }
        }
        
        // Scroll-based animation trigger - only if NOT static
        if ($animation_name !== 'static') {
            $scroll_trigger = get_post_meta($model_id, '_explorexr_premium_animation_scroll_trigger', true);
            if ($scroll_trigger === 'on') {
                $attributes['data-animation-scroll-trigger'] = 'true';

                // Scroll speed setting (1-100%, default 50%)
                $scroll_speed = get_post_meta($model_id, '_explorexr_premium_animation_scroll_speed', true);
                if ($scroll_speed !== '' && $scroll_speed !== false) {
                    $attributes['data-animation-scroll-speed'] = intval($scroll_speed);
                }

                // When scroll trigger is active, remove autoplay and loop
                // as they conflict with scroll-driven playback
                unset($attributes['autoplay']);
                unset($attributes['loop']);
            }
        }

        // Animation repeat mode (premium setting) - only if NOT static
        // FIX: Actually apply the repeat mode to control playback behavior
        if ($animation_name !== 'static') {
            $animation_repeat = get_post_meta($model_id, '_explorexr_premium_animation_repeat', true);
            
            if (!empty($animation_repeat)) {
                // Map repeat mode to appropriate attributes
                switch ($animation_repeat) {
                    case 'once':
                        // Don't add loop attribute - play once
                        // Remove loop if it was added
                        unset($attributes['loop']);
                        break;
                        
                    case 'loop':
                        // Add standard loop attribute
                        $attributes['loop'] = '';
                        break;
                        
                    case 'pingpong':
                        // Ping-pong requires custom JS handling
                        $attributes['data-animation-ping-pong'] = 'true';
                        // Don't use standard loop for ping-pong
                        unset($attributes['loop']);
                        break;
                }
            }
        }
        
        // Animation speed (timeScale) - only if NOT static
        if ($animation_name !== 'static') {
            $animation_speed = get_post_meta($model_id, '_explorexr_animation_speed', true);
            if (!empty($animation_speed) && $animation_speed != '1.0' && $animation_speed != '1') {
                $attributes['data-animation-speed'] = $animation_speed;
            }
        }
        
        // Show frontend controls - check premium key first, then legacy, default on
        $show_controls = explorexr_premium_animation_get_show_frontend_controls($model_id);
        
        if ($show_controls) {
            $attributes['data-show-animation-controls'] = 'true';
            
            // Multiple animation settings for frontend controls
            $multiple_animations_enabled = get_post_meta($model_id, '_explorexr_premium_multiple_animations_enabled', true) === 'on';
            if ($multiple_animations_enabled) {
                $attributes['data-multiple-animations-enabled'] = 'true';
            }
            
            $selected_animations = get_post_meta($model_id, '_explorexr_premium_selected_animations', true);
            if (is_array($selected_animations) && !empty($selected_animations)) {
                $attributes['data-selected-animations'] = wp_json_encode(array_values($selected_animations));
            }
            
            // Add premium frontend control settings
            // Always output position/style/size so the frontend JS can detect premium config
            // and avoid creating duplicate basic controls.
            $control_position = get_post_meta($model_id, '_explorexr_premium_animation_control_position', true);
            $attributes['data-animation-control-position'] = !empty($control_position) ? $control_position : 'bottom-left';
            
            $control_style = get_post_meta($model_id, '_explorexr_premium_animation_control_style', true);
            $attributes['data-animation-control-style'] = !empty($control_style) ? $control_style : 'default';
            
            $control_size = get_post_meta($model_id, '_explorexr_premium_animation_control_size', true);
            $attributes['data-animation-control-size'] = !empty($control_size) ? $control_size : 'medium';
        }
    }
    
    return $attributes;
}
add_filter('explorexr_premium_model_viewer_attributes', 'explorexr_premium_animation_apply_advanced_attributes', 10, 2);

// Hook save function to save_post
add_action('save_post_explorexr_model', 'explorexr_premium_animation_save_settings', 10, 1);

/**
 * Enqueue frontend scripts and styles
 * Only loads on pages that contain model viewers.
 */
function explorexr_premium_animation_enqueue_frontend_assets() {
    // Only enqueue on pages with model viewers
    global $post;
    $has_model_viewers = false;

    if (function_exists('explorexr_premium_has_model_viewers') && explorexr_premium_has_model_viewers($post)) {
        $has_model_viewers = true;
    }

    if ($post && (has_shortcode($post->post_content, 'explorexr_model') || has_shortcode($post->post_content, 'explorexr_premium_model') || get_post_type($post) === 'explorexr_model')) {
        $has_model_viewers = true;
    }

    if (!$has_model_viewers) {
        return;
    }

    // Enqueue frontend CSS
    wp_enqueue_style(
        'explorexr-animation-frontend',
        explorexr_premium_ANIMATION_PLUGIN_URL . 'assets/css/animation-frontend.css',
        array(),
        explorexr_premium_ANIMATION_VERSION
    );
    
    // Enqueue frontend JS
    wp_enqueue_script(
        'explorexr-animation-frontend',
        explorexr_premium_ANIMATION_PLUGIN_URL . 'assets/js/animation-frontend.js',
        array(),
        explorexr_premium_ANIMATION_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'explorexr_premium_animation_enqueue_frontend_assets');

/**
 * Get animation settings for a model
 */
function explorexr_premium_animation_get_settings($model_id) {
    return array(
        'animation_enabled' => get_post_meta($model_id, '_explorexr_premium_animation_enabled', true) === 'on',
        'animation_name' => get_post_meta($model_id, '_explorexr_premium_animation_name', true),
        'animation_crossfade_duration' => get_post_meta($model_id, '_explorexr_premium_animation_crossfade_duration', true) ?: '300',
        'animation_autoplay' => get_post_meta($model_id, '_explorexr_premium_animation_autoplay', true) === 'on',
        'animation_repeat' => get_post_meta($model_id, '_explorexr_premium_animation_repeat', true) ?: 'once',
        'animation_loop' => get_post_meta($model_id, '_explorexr_premium_animation_loop', true) !== 'off',
        'scroll_trigger' => get_post_meta($model_id, '_explorexr_premium_animation_scroll_trigger', true) === 'on',
        'scroll_speed' => get_post_meta($model_id, '_explorexr_premium_animation_scroll_speed', true) ?: '50',
        'multiple_animations_enabled' => get_post_meta($model_id, '_explorexr_premium_multiple_animations_enabled', true) === 'on',
        'selected_animations' => get_post_meta($model_id, '_explorexr_premium_selected_animations', true) ?: array(),
        'show_frontend_controls' => explorexr_premium_animation_get_show_frontend_controls($model_id),
        'control_position' => get_post_meta($model_id, '_explorexr_premium_animation_control_position', true) ?: 'bottom-left',
        'control_style' => get_post_meta($model_id, '_explorexr_premium_animation_control_style', true) ?: 'default',
        'control_size' => get_post_meta($model_id, '_explorexr_premium_animation_control_size', true) ?: 'medium'
    );
}

/**
 * Save animation settings
 */
function explorexr_premium_animation_save_settings($post_id, $edit_mode = false) {
    // Skip autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check capabilities
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Animation enabled
    $animation_enabled = isset($_POST['explorexr_premium_animation_enabled']) ? 'on' : 'off';
    update_post_meta($post_id, '_explorexr_premium_animation_enabled', $animation_enabled);
    
    // Animation name
    if (isset($_POST['explorexr_premium_animation_name'])) {
        $anim_name = sanitize_text_field(wp_unslash($_POST['explorexr_premium_animation_name']));
        update_post_meta($post_id, '_explorexr_premium_animation_name', $anim_name);
    }
    
    // Animation crossfade duration
    if (isset($_POST['explorexr_premium_animation_crossfade_duration'])) {
        update_post_meta($post_id, '_explorexr_premium_animation_crossfade_duration', sanitize_text_field(wp_unslash($_POST['explorexr_premium_animation_crossfade_duration'])));
    }
    
    // Animation autoplay
    $animation_autoplay = isset($_POST['explorexr_premium_animation_autoplay']) ? 'on' : 'off';
    update_post_meta($post_id, '_explorexr_premium_animation_autoplay', $animation_autoplay);
    
    // Animation repeat mode (CRITICAL FIX: This was never being saved!)
    if (isset($_POST['explorexr_premium_animation_repeat'])) {
        $animation_repeat = sanitize_text_field(wp_unslash($_POST['explorexr_premium_animation_repeat']));
        update_post_meta($post_id, '_explorexr_premium_animation_repeat', $animation_repeat);
    } else {
        $animation_repeat = 'once';
    }
    
    // Derive loop from repeat mode for backward compatibility
    // The Loop checkbox has been removed from the UI; repeat mode is the single source of truth.
    $animation_loop = ($animation_repeat === 'loop') ? 'on' : 'off';
    update_post_meta($post_id, '_explorexr_premium_animation_loop', $animation_loop);
    
    // Scroll-based animation trigger
    $scroll_trigger = isset($_POST['explorexr_premium_animation_scroll_trigger']) ? 'on' : 'off';
    update_post_meta($post_id, '_explorexr_premium_animation_scroll_trigger', $scroll_trigger);

    // Scroll animation speed (1-100%, default 50%)
    if (isset($_POST['explorexr_premium_animation_scroll_speed'])) {
        $scroll_speed = intval(wp_unslash($_POST['explorexr_premium_animation_scroll_speed']));
        $scroll_speed = max(1, min(100, $scroll_speed));
        update_post_meta($post_id, '_explorexr_premium_animation_scroll_speed', $scroll_speed);
    }

    // Multiple animations enabled
    $multiple_animations_enabled = isset($_POST['explorexr_premium_multiple_animations_enabled']) ? 'on' : 'off';
    update_post_meta($post_id, '_explorexr_premium_multiple_animations_enabled', $multiple_animations_enabled);
    
    // Selected animations (array of animation names)
    if (isset($_POST['explorexr_premium_selected_animations']) && is_array($_POST['explorexr_premium_selected_animations'])) {
        $selected_animations = array_map('sanitize_text_field', array_map('wp_unslash', $_POST['explorexr_premium_selected_animations']));
        update_post_meta($post_id, '_explorexr_premium_selected_animations', $selected_animations);
    } else {
        update_post_meta($post_id, '_explorexr_premium_selected_animations', array());
    }
    
    // Frontend control settings
    $show_frontend_controls = isset($_POST['explorexr_premium_animation_show_frontend_controls']) ? 'on' : 'off';
    update_post_meta($post_id, '_explorexr_premium_animation_show_frontend_controls', $show_frontend_controls);
    
    if (isset($_POST['explorexr_premium_animation_control_position'])) {
        update_post_meta($post_id, '_explorexr_premium_animation_control_position', sanitize_text_field(wp_unslash($_POST['explorexr_premium_animation_control_position'])));
    }
    
    if (isset($_POST['explorexr_premium_animation_control_style'])) {
        update_post_meta($post_id, '_explorexr_premium_animation_control_style', sanitize_text_field(wp_unslash($_POST['explorexr_premium_animation_control_style'])));
    }
    
    if (isset($_POST['explorexr_premium_animation_control_size'])) {
        update_post_meta($post_id, '_explorexr_premium_animation_control_size', sanitize_text_field(wp_unslash($_POST['explorexr_premium_animation_control_size'])));
    }
    
    // CRITICAL: Clear object cache to ensure fresh values are used immediately
    // This fixes the issue where get_post_meta() returns stale cached values after save
    wp_cache_delete($post_id, 'post_meta');
    clean_post_cache($post_id);
}

/**
 * AJAX handler to get animation frontend settings
 */
function explorexr_premium_animation_ajax_get_frontend_settings() {
    // Check nonce for security
    $nonce = isset($_POST['nonce'])
        ? sanitize_text_field(wp_unslash($_POST['nonce']))
        : '';

    if (!wp_verify_nonce($nonce, 'explorexr_premium_ajax')) {
        wp_send_json_error(['message' => 'Security check failed']);
        return;
    }
    
    $model_id = isset($_POST['model_id']) ? intval($_POST['model_id']) : 0;
    
    if (!$model_id) {
        wp_send_json_error(['message' => 'Invalid model ID']);
        return;
    }

    // Get frontend control settings
    $show_controls = explorexr_premium_animation_get_show_frontend_controls($model_id);
    $position = get_post_meta($model_id, '_explorexr_premium_animation_control_position', true) ?: 'bottom-left';
    $style = get_post_meta($model_id, '_explorexr_premium_animation_control_style', true) ?: 'default';
    $size = get_post_meta($model_id, '_explorexr_premium_animation_control_size', true) ?: 'medium';
    
    // Get animation playback settings
    $animation_repeat = get_post_meta($model_id, '_explorexr_premium_animation_repeat', true);
    $animation_loop_setting = get_post_meta($model_id, '_explorexr_premium_animation_loop', true) !== 'off';
    if ($animation_repeat === 'once' || $animation_repeat === 'pingpong') {
        $animation_loop = false;
    } elseif ($animation_repeat === 'loop') {
        $animation_loop = true;
    } else {
        $animation_loop = $animation_loop_setting;
    }
    $multiple_animations_enabled = get_post_meta($model_id, '_explorexr_premium_multiple_animations_enabled', true) === 'on';
    $selected_animations = get_post_meta($model_id, '_explorexr_premium_selected_animations', true) ?: array();
    
    wp_send_json_success([
        'show_controls' => $show_controls,
        'position' => $position,
        'style' => $style,
        'size' => $size,
        'loop' => $animation_loop,
        'multiple_animations' => $multiple_animations_enabled,
        'selected_animations' => $selected_animations
    ]);
}
add_action('wp_ajax_explorexr_premium_get_animation_frontend_settings', 'explorexr_premium_animation_ajax_get_frontend_settings');
add_action('wp_ajax_nopriv_explorexr_premium_get_animation_frontend_settings', 'explorexr_premium_animation_ajax_get_frontend_settings');
