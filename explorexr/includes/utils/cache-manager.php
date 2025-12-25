<?php
/**
 * ExploreXR Cache Manager
 *
 * Handles caching and cache invalidation for model output and settings
 *
 * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate a deterministic cache key for a model
 *
 * @param int $model_id The model post ID
 * @param array $config Optional configuration that affects output
 * @return string Cache key
 */
function explorexr_generate_cache_key($model_id, $config = array()) {
    // Base key with model ID
    $key_parts = array(
        'explorexr_model',
        $model_id,
        EXPLOREXR_VERSION
    );
    
    // Add relevant options that affect model rendering
    $relevant_options = array(
        'explorexr_loading_display',
        'explorexr_large_model_handling',
        'explorexr_large_model_size_threshold',
        'explorexr_model_viewer_version'
    );
    
    foreach ($relevant_options as $option) {
        $key_parts[] = get_option($option, '');
    }
    
    // Add model meta modification time
    $post = get_post($model_id);
    if ($post) {
        $key_parts[] = $post->post_modified;
    }
    
    // Add any custom config
    if (!empty($config)) {
        $key_parts[] = md5(serialize($config));
    }
    
    // Generate hash for deterministic key
    $cache_key = 'explorexr_' . md5(implode('_', $key_parts));
    
    return $cache_key;
}

/**
 * Get cached model output
 *
 * @param int $model_id The model post ID
 * @param array $config Optional configuration
 * @return string|false Cached output or false if not cached
 */
function explorexr_get_cached_model($model_id, $config = array()) {
    $cache_key = explorexr_generate_cache_key($model_id, $config);
    $cached = get_transient($cache_key);
    
    if ($cached !== false) {
        // Add HTML comment to indicate cache hit
        $cached .= "\n<!-- ExploreXR: Cached output -->";
    }
    
    return $cached;
}

/**
 * Set cached model output
 *
 * @param int $model_id The model post ID
 * @param string $output The rendered output to cache
 * @param array $config Optional configuration
 * @param int $expiration Cache expiration in seconds (default 12 hours)
 * @return bool True on success
 */
function explorexr_set_cached_model($model_id, $output, $config = array(), $expiration = 43200) {
    $cache_key = explorexr_generate_cache_key($model_id, $config);
    return set_transient($cache_key, $output, $expiration);
}

/**
 * Clear cache for a specific model
 *
 * @param int $model_id The model post ID
 * @return bool True on success
 */
function explorexr_clear_model_cache($model_id) {
    // Generate possible cache keys for this model
    $base_key = 'explorexr_model_' . $model_id;
    
    // Try to delete the most common transient patterns
    $deleted = false;
    
    // Delete main cache key variations
    for ($i = 0; $i < 10; $i++) {
        $key = 'explorexr_' . md5($base_key . '_' . $i);
        if (delete_transient($key)) {
            $deleted = true;
        }
    }
    
    // Use WordPress cache API to clear object cache
    if (function_exists('wp_cache_delete_group')) {
        wp_cache_delete_group('explorexr_model_' . $model_id);
    }
    
    // Clear any cached data for this specific model
    wp_cache_delete('explorexr_model_' . $model_id, 'explorexr_models');
    
    return $deleted;
}

/**
 * Clear all ExploreXR caches
 *
 * @return int Number of cache entries cleared
 */
function explorexr_clear_all_cache() {
    $cleared = 0;
    
    // Clear known transients using WordPress API
    $known_transients = array(
        'explorexr_viewer_version_check',
        'explorexr_model_cache',
        'explorexr_admin_stats',
        'explorexr_file_validation',
        'explorexr_cache_stats'
    );
    
    foreach ($known_transients as $transient) {
        if (delete_transient($transient)) {
            $cleared++;
        }
    }
    
    // Clear model-specific caches
    $models = get_posts(array(
        'post_type' => 'explorexr_model',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'cache_results' => false,
        'no_found_rows' => true
    ));
    
    foreach ($models as $model_id) {
        if (explorexr_clear_model_cache($model_id)) {
            $cleared++;
        }
    }
    
    // Clear WordPress object cache for ExploreXR groups
    if (function_exists('wp_cache_flush_group')) {
        wp_cache_flush_group('explorexr_models');
        wp_cache_flush_group('explorexr_settings');
        $cleared += 2;
    }
    
    return $cleared;
}

/**
 * Get cache statistics
 *
 * @return array Cache stats
 */
function explorexr_get_cache_stats() {
    // Use WordPress Transients API to check for cached data
    $cached_stats = get_transient('explorexr_cache_stats');
    
    if ($cached_stats !== false) {
        return $cached_stats;
    }
    
    // Count models with potential caches
    $model_count = wp_count_posts('explorexr_model');
    $total_models = 0;
    
    if ($model_count) {
        $total_models = $model_count->publish + $model_count->draft + $model_count->private;
    }
    
    // Estimate cache count (models + known transients)
    $known_transients = array(
        'explorexr_viewer_version_check',
        'explorexr_model_cache',
        'explorexr_admin_stats',
        'explorexr_file_validation'
    );
    
    $active_transients = 0;
    foreach ($known_transients as $transient) {
        if (get_transient($transient) !== false) {
            $active_transients++;
        }
    }
    
    // Approximate size based on model count (rough estimate: 5KB per cached model)
    $estimated_size_kb = ($total_models * 5) + ($active_transients * 1);
    
    $stats = array(
        'count' => $total_models + $active_transients,
        'size_kb' => $estimated_size_kb,
        'is_estimate' => true
    );
    
    // Cache the stats for 5 minutes
    set_transient('explorexr_cache_stats', $stats, 300);
    
    return $stats;
}

/**
 * Hook: Invalidate cache when a model is saved
 */
add_action('save_post_explorexr_model', 'explorexr_invalidate_model_cache_on_save', 10, 3);
function explorexr_invalidate_model_cache_on_save($post_id, $post, $update) {
    // Only clear cache for actual updates, not autosaves or revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (wp_is_post_revision($post_id)) {
        return;
    }
    
    // Clear the cache for this specific model
    explorexr_clear_model_cache($post_id);
}

/**
 * Hook: Invalidate all caches when plugin settings change
 */
add_action('updated_option', 'explorexr_invalidate_cache_on_option_update', 10, 3);
function explorexr_invalidate_cache_on_option_update($option_name, $old_value, $value) {
    // List of options that affect model rendering
    $relevant_options = array(
        'explorexr_loading_display',
        'explorexr_large_model_handling',
        'explorexr_large_model_size_threshold',
        'explorexr_model_viewer_version',
        'explorexr_loading_bar_color',
        'explorexr_loading_bar_size',
        'explorexr_loading_bar_position',
        'explorexr_percentage_font_size',
        'explorexr_percentage_font_family',
        'explorexr_percentage_font_color',
        'explorexr_percentage_position',
        'explorexr_overlay_bg_color',
        'explorexr_overlay_bg_opacity'
    );
    
    // If this option affects rendering, clear all caches
    if (in_array($option_name, $relevant_options)) {
        explorexr_clear_all_cache();
    }
}

/**
 * Hook: Invalidate cache when a model is deleted
 */
add_action('before_delete_post', 'explorexr_invalidate_cache_on_delete');
function explorexr_invalidate_cache_on_delete($post_id) {
    if (get_post_type($post_id) === 'explorexr_model') {
        explorexr_clear_model_cache($post_id);
    }
}
