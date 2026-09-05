<?php
/**
 * ExploreXR Model Cleanup Functions
 * Handles cleanup of deleted or missing model files
 *
  * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if a model file still exists and is valid
 * 
 * @param string $model_file_url The URL of the model file
 * @return bool True if the file exists, false otherwise
 */
function explorexr_model_file_exists($model_file_url) {
    if (empty($model_file_url)) {
        return false;
    }
    
    // Check if it's a local file in our models directory
    if (defined('EXPLOREXR_MODELS_URL') && defined('EXPLOREXR_MODELS_DIR') && 
        !empty($model_file_url) && strpos($model_file_url, EXPLOREXR_MODELS_URL) === 0) {
        $file_path = str_replace(EXPLOREXR_MODELS_URL, EXPLOREXR_MODELS_DIR, $model_file_url ?? '');
        return file_exists($file_path);
    }
    
    // For external files, do a lightweight check
    $response = wp_safe_remote_head($model_file_url, array('timeout' => 3, 'redirection' => 2));
    return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
}

/**
 * Clean up orphaned model entries
 * Checks all ExploreXR models and marks those with missing files
 * 
 * @return array Results of the cleanup operation
 */
function explorexr_cleanup_orphaned_models() {
    $results = [
        'checked' => 0,
        'orphaned' => 0,
        'errors' => []
    ];

    $page = 1;
    do {
        $models_query = new WP_Query([
            'post_type' => 'explorexr_model',
            'posts_per_page' => 50,
            'paged' => $page,
            'post_status' => 'publish',
            'fields' => 'ids',
            'no_found_rows' => false
        ]);

        foreach ($models_query->posts as $model_id) {
            $results['checked']++;
            $model_file = get_post_meta($model_id, '_explorexr_model_file', true) ?: '';
            if (empty($model_file)) {
                continue;
            }

            if (!explorexr_model_file_exists($model_file)) {
                update_post_meta($model_id, '_explorexr_file_missing', '1');
                $results['orphaned']++;
            } else {
                delete_post_meta($model_id, '_explorexr_file_missing');
            }
        }
        $page++;
    } while ($page <= (int) $models_query->max_num_pages);

    return $results;
}

/**
 * AJAX handler for cleaning up orphaned models
 */
function explorexr_ajax_cleanup_models() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'explorexr_admin_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
        return;
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
        return;
    }
    
    // Run the cleanup
    $results = explorexr_cleanup_orphaned_models();
    
    // Format a nice response message
    $message = sprintf(
        'Checked %d models. Found %d with missing files.',
        $results['checked'],
        $results['orphaned']
    );
    
    wp_send_json_success([
        'message' => $message,
        'results' => $results
    ]);
}

// Register the AJAX handler
add_action('wp_ajax_explorexr_cleanup_models', 'explorexr_ajax_cleanup_models');

/**
 * Display admin notice for orphaned models
 */
function explorexr_orphaned_models_notice() {
    // Only show on ExploreXR admin pages
    $screen = get_current_screen();
    if (!$screen || empty($screen->id) || strpos($screen->id, 'explorexr') === false) {
        return;
    }
    
    // Check if we have orphaned models using WP_Query for WordPress standards compliance
    $orphaned_query = new WP_Query([
        'post_type' => 'explorexr_model',
        'post_status' => 'publish',
        'posts_per_page' => 50,
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required for cleanup functionality to find orphaned models
        'meta_query' => [
            [
                'key' => '_explorexr_file_missing',
                'value' => '1'
            ]
        ],
        'fields' => 'ids'
    ]);
    
    $orphaned_count = $orphaned_query->found_posts;
    wp_reset_postdata();
    
    if ($orphaned_count > 0) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong>ExploreXR:</strong> 
                <?php 
                printf(
                    // translators: %d: Number of 3D models with missing files
                    esc_html(_n(
                        'Found %d 3D model with a missing file. The model will not display correctly until you update it with a new file.',
                        'Found %d 3D models with missing files. These models will not display correctly until you update them with new files.',
                        $orphaned_count,
                        'explorexr'
                    )),
                    esc_html($orphaned_count)
                ); 
                ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-models')); ?>"><?php esc_html_e('View models', 'explorexr'); ?></a>
            </p>
        </div>
        <?php
    }
}

// Add the notice hook
add_action('admin_notices', 'explorexr_orphaned_models_notice');

/**
 * Add a dashboard widget to show orphaned models
 */
function explorexr_register_orphaned_models_widget() {
    wp_add_dashboard_widget(
        'explorexr_orphaned_models_widget',
        'ExploreXR 3D Models Status',
        'explorexr_orphaned_models_widget_callback'
    );
}

/**
 * Dashboard widget callback
 */
function explorexr_orphaned_models_widget_callback() {
    // Use WP_Query for WordPress standards compliance
    // Cache the results for 5 minutes to improve dashboard performance
    $cache_key = 'explorexr_orphaned_models_widget';
    $cached_data = wp_cache_get($cache_key, 'explorexr');
    
    if (false === $cached_data) {
        $orphaned_query = new WP_Query([
            'post_type' => 'explorexr_model',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required for cleanup functionality to find orphaned models
            'meta_query' => [
                [
                    'key' => '_explorexr_file_missing',
                    'value' => '1'
                ]
            ],
            'fields' => 'ids'
        ]);
        
        $orphaned_count = $orphaned_query->found_posts;
        wp_reset_postdata();
        wp_cache_set($cache_key, $orphaned_count, 'explorexr', 300); // Cache for 5 minutes
    } else {
        $orphaned_count = $cached_data;
    }
    
    // Get total models count
    $total_models = wp_count_posts('explorexr_model')->publish;
    
    ?>
    <div class="explorexr-dashboard-widget">
        <p>
            <strong>Total 3D Models:</strong> <?php echo esc_html($total_models); ?>
        </p>
        
        <?php if ($orphaned_count > 0) : ?>
            <p class="explorexr-warning">
                <strong>Models with missing files:</strong> <?php echo esc_html($orphaned_count); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-models')); ?>" class="button button-small">View</a>
            </p>
            <p class="description">
                These models will show an error in the frontend. Update them with new model files.
            </p>
        <?php else : ?>
            <p class="explorexr-success">
                <strong>All model files are valid ✓</strong>
            </p>
        <?php endif; ?>
        
        <p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-browse-models')); ?>" class="button">Browse Models</a>
        </p>
    </div>
    <?php
}

// Register the dashboard widget
add_action('wp_dashboard_setup', 'explorexr_register_orphaned_models_widget');



