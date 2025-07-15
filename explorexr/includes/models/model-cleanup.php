<?php
/**
 * ExpoXR Model Cleanup Functions
 * Handles cleanup of deleted or missing model files
 *
 * @package ExpoXR
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
function expoxr_model_file_exists($model_file_url) {
    if (empty($model_file_url)) {
        return false;
    }
    
    // Check if it's a local file in our models directory
    if (strpos($model_file_url, EXPOXR_MODELS_URL) === 0) {
        $file_path = str_replace(EXPOXR_MODELS_URL, EXPOXR_MODELS_DIR, $model_file_url);
        return file_exists($file_path);
    }
    
    // For external files, do a lightweight check
    $response = wp_remote_head($model_file_url);
    return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
}

/**
 * Clean up orphaned model entries
 * Checks all ExpoXR models and marks those with missing files
 * 
 * @return array Results of the cleanup operation
 */
function expoxr_cleanup_orphaned_models() {
    $models_query = new WP_Query([
        'post_type' => 'expoxr_model',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);
    
    $results = [
        'checked' => 0,
        'orphaned' => 0,
        'errors' => []
    ];
    
    if (!$models_query->have_posts()) {
        return $results;
    }
    
    while ($models_query->have_posts()) {
        $models_query->the_post();
        $model_id = get_the_ID();
        $results['checked']++;
        
        // Get the model file URL
        $model_file = get_post_meta($model_id, '_expoxr_model_file', true);
        
        // Skip if no file is set
        if (empty($model_file)) {
            continue;
        }
        
        // Check if the file exists
        if (!expoxr_model_file_exists($model_file)) {
            // File doesn't exist, mark as orphaned
            update_post_meta($model_id, '_expoxr_file_missing', '1');
            $results['orphaned']++;
            
            // Log for debugging
            if (get_option('expoxr_debug_mode', false)) {
                error_log(sprintf(
                    'ExpoXR: Model #%d has a missing file: %s',
                    $model_id,
                    $model_file
                ));
            }
        } else {
            // File exists, clear any previous missing flag
            delete_post_meta($model_id, '_expoxr_file_missing');
        }
    }
    
    wp_reset_postdata();
    return $results;
}

/**
 * AJAX handler for cleaning up orphaned models
 */
function expoxr_ajax_cleanup_models() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'expoxr_admin_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
        return;
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
        return;
    }
    
    // Run the cleanup
    $results = expoxr_cleanup_orphaned_models();
    
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
add_action('wp_ajax_expoxr_cleanup_models', 'expoxr_ajax_cleanup_models');

/**
 * Display admin notice for orphaned models
 */
function expoxr_orphaned_models_notice() {
    // Only show on ExpoXR admin pages
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'explorexr') === false) {
        return;
    }
    
    // Check if we have orphaned models using WP_Query for WordPress standards compliance
    $orphaned_query = new WP_Query([
        'post_type' => 'expoxr_model',
        'post_status' => 'publish',
        'posts_per_page' => 50,
        'meta_query' => [
            [
                'key' => '_expoxr_file_missing',
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
                <strong>ExpoXR:</strong> 
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
                <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-models')); ?>"><?php esc_html_e('View models', 'explorexr'); ?></a>
            </p>
        </div>
        <?php
    }
}

// Add the notice hook
add_action('admin_notices', 'expoxr_orphaned_models_notice');

/**
 * Add a dashboard widget to show orphaned models
 */
function expoxr_register_orphaned_models_widget() {
    wp_add_dashboard_widget(
        'expoxr_orphaned_models_widget',
        'ExpoXR 3D Models Status',
        'expoxr_orphaned_models_widget_callback'
    );
}

/**
 * Dashboard widget callback
 */
function expoxr_orphaned_models_widget_callback() {
    // Use WP_Query for WordPress standards compliance
    // Cache the results for 5 minutes to improve dashboard performance
    $cache_key = 'expoxr_orphaned_models_widget';
    $cached_data = wp_cache_get($cache_key, 'explorexr');
    
    if (false === $cached_data) {
        $orphaned_query = new WP_Query([
            'post_type' => 'expoxr_model',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'meta_query' => [
                [
                    'key' => '_expoxr_file_missing',
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
    $total_models = wp_count_posts('expoxr_model')->publish;
    
    ?>
    <div class="expoxr-dashboard-widget">
        <p>
            <strong>Total 3D Models:</strong> <?php echo esc_html($total_models); ?>
        </p>
        
        <?php if ($orphaned_count > 0) : ?>
            <p class="expoxr-warning">
                <strong>Models with missing files:</strong> <?php echo esc_html($orphaned_count); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-models')); ?>" class="button button-small">View</a>
            </p>
            <p class="description">
                These models will show an error in the frontend. Update them with new model files.
            </p>
        <?php else : ?>
            <p class="expoxr-success">
                <strong>All model files are valid ✓</strong>
            </p>
        <?php endif; ?>
        
        <p>
            <a href="#" class="button" id="expoxr-check-orphaned-models">Check for Missing Files</a>
            <span class="spinner" style="float: none; margin-top: 0;"></span>
        </p>
        <div id="expoxr-check-result"></div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#expoxr-check-orphaned-models').on('click', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $spinner = $button.next('.spinner');
            const $result = $('#expoxr-check-result');
            
            $button.prop('disabled', true);
            $spinner.addClass('is-active');
            $result.html('');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'expoxr_cleanup_models',
                    _wpnonce: '<?php echo esc_js(wp_create_nonce('expoxr_cleanup_models')); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $result.html('<p class="expoxr-success">' + response.data.message + '</p>');
                        
                        // Refresh the page if orphaned models were found
                        if (response.data.results.orphaned > 0) {
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        }
                    } else {
                        $result.html('<p class="expoxr-error">Error: ' + response.data.message + '</p>');
                    }
                },
                error: function() {
                    $result.html('<p class="expoxr-error">Error checking models. Please try again.</p>');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');
                }
            });
        });
    });
    </script>
    <style>
    .expoxr-dashboard-widget .expoxr-success {
        color: #46b450;
    }
    .expoxr-dashboard-widget .expoxr-warning {
        color: #ffb900;
    }
    .expoxr-dashboard-widget .expoxr-error {
        color: #dc3232;
    }
    .expoxr-dashboard-widget #expoxr-check-result {
        margin-top: 10px;
    }
    </style>
    <?php
}

// Register the dashboard widget
add_action('wp_dashboard_setup', 'expoxr_register_orphaned_models_widget');





