<?php
/**
 * ExploreXR Admin UI
 * Custom modern UI for the ExploreXR plugin administration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the modern dashboard UI
 */
function expoxr_custom_ui_page() {
    // Get model stats
    $total_models = wp_count_posts('expoxr_model')->publish;
    
    // Count model files
    $models_dir = EXPOXR_MODELS_DIR;
    $model_files = 0;
    if (file_exists($models_dir)) {
        // Use GLOB_BRACE if available, otherwise fallback to multiple glob calls
        if (defined('GLOB_BRACE')) {
            $files = glob($models_dir . '*.{glb,gltf,usdz}', GLOB_BRACE);
        } else {
            $files = array_merge(
                glob($models_dir . '*.glb') ?: [],
                glob($models_dir . '*.gltf') ?: [],
                glob($models_dir . '*.usdz') ?: []
            );
        }
        $model_files = count($files);
    }
    
    // Get latest version
    $current_version = EXPOXR_VERSION;
    
    // Check if WooCommerce is active
    $woocommerce_active = is_plugin_active('woocommerce/woocommerce.php');
    
    // Check if Elementor is active
    $elementor_active = is_plugin_active('elementor/elementor.php');
    ?>
    <div class="wrap expoxr-admin-container">
        <!-- Header -->
        <div class="expoxr-admin-header">
            <div class="expoxr-logo">
                <h1>ExploreXR <span class="expoxr-version"><?php echo esc_html($current_version); ?></span></h1>
            </div>
            <div class="expoxr-header-actions">
                <a href="https://expoxr.com/explorexr/documentation" target="_blank" class="button">Documentation</a>
            </div>
        </div>
          <!-- Quick Actions -->
        <div class="expoxr-quick-actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr')); ?>">
                <span class="dashicons dashicons-dashboard"></span> Dashboard
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-create-model')); ?>">
                <span class="dashicons dashicons-plus-alt"></span> Create New Model
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-browse-models')); ?>">
                <span class="dashicons dashicons-format-gallery"></span> Browse Models
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-files')); ?>">
                <span class="dashicons dashicons-media-default"></span> Manage Files
            </a>            
            <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-loading-options')); ?>">
                <span class="dashicons dashicons-performance"></span> Loading Options
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-settings')); ?>">
                <span class="dashicons dashicons-admin-settings"></span> Settings
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-premium')); ?>" class="expoxr-premium-action">
                <span class="dashicons dashicons-star-filled"></span> Go Premium
            </a>
        </div>
        
        <!-- Dashboard Grid - Reordered Getting Started to first position -->
        <div class="expoxr-dashboard-grid">
            <!-- Getting Started - Moved to first position -->
            <div class="expoxr-card">
                <div class="expoxr-card-header">
                    <h2>Getting Started</h2>
                    <span class="dashicons dashicons-welcome-learn-more"></span>
                </div>
                <div class="expoxr-card-content">
                    <ol style="margin: 0; padding-left: 20px;">
                        <li style="margin-bottom: 8px;">Upload 3D model files (GLB, GLTF, USDZ)</li>
                        <li style="margin-bottom: 8px;">Create a new 3D model and configure display options</li>
                        <li style="margin-bottom: 8px;">Use the shortcode to display the model on your site</li>
                        <li>Customize loading options for better user experience</li>
                    </ol>
                </div>
                <div class="expoxr-card-footer">
                    <a href="https://expoxr.com/explorexr/documentation" target="_blank" class="button button-small">Read Documentation</a>
                </div>
            </div>
            
            <!-- Stats Overview - Modified to vertical layout -->
            <div class="expoxr-card">
                <div class="expoxr-card-header">
                    <h2>Stats Overview</h2>
                    <span class="dashicons dashicons-chart-bar"></span>
                </div>
                <div class="expoxr-card-content">
                    <!-- Changed from horizontal to vertical layout -->
                    <div class="expoxr-stats-vertical">
                        <div class="expoxr-stat-item-vertical">
                            <div class="expoxr-stat-label">3D Models</div>
                            <div class="expoxr-stat-number"><?php echo esc_html($total_models); ?></div>
                        </div>
                        <div class="expoxr-stat-item-vertical">
                            <div class="expoxr-stat-label">Model Files</div>
                            <div class="expoxr-stat-number"><?php echo esc_html($model_files); ?></div>
                        </div>
                        <div class="expoxr-stat-item-vertical">
                            <div class="expoxr-stat-label">Free Version</div>
                            <div class="expoxr-stat-number"><span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span></div>
                        </div>
                        <div class="expoxr-stat-item-vertical">
                            <div class="expoxr-stat-label">Shortcode Ready</div>
                            <div class="expoxr-stat-number"><span class="dashicons dashicons-yes" style="color:#46b450;"></span></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- System Status -->
            <div class="expoxr-card">
                <div class="expoxr-card-header">
                    <h2>System Status</h2>
                    <span class="dashicons dashicons-performance"></span>
                </div>
                <div class="expoxr-card-content">
                    <?php
                    // Check PHP version
                    $php_version = phpversion();
                    $php_min_version = '7.2';
                    $php_status = version_compare($php_version, $php_min_version, '>=');
                    
                    // Check max upload size
                    $max_upload = min((int)(ini_get('upload_max_filesize')), (int)(ini_get('post_max_size')));
                    $max_upload_status = $max_upload >= 20;
                    
                    // Check model viewer version
                    $model_viewer_version = get_option('expoxr_model_viewer_version', '3.3.0');
                    ?>
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>PHP Version:</span>
                            <span style="color: <?php echo esc_attr($php_status ? '#46b450' : '#dc3232'); ?>">
                                <?php echo esc_html($php_version); ?> <?php echo esc_html($php_status ? '✓' : '✗'); ?>
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>Max Upload Size:</span>
                            <span style="color: <?php echo esc_attr($max_upload_status ? '#46b450' : '#dc3232'); ?>">
                                <?php echo esc_html($max_upload); ?>MB <?php echo esc_html($max_upload_status ? '✓' : '✗'); ?>
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>Model Viewer:</span>
                            <span style="color: #46b450;">
                                v<?php echo esc_html($model_viewer_version); ?> ✓
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>WordPress:</span>
                            <span style="color: #46b450;">
                                <?php echo esc_html(get_bloginfo('version')); ?> ✓
                            </span>
                        </div>
                    </div>
                </div>
                <div class="expoxr-card-footer">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-settings')); ?>" class="button button-small">View Settings</a>
                </div>
            </div>
            
            <!-- Recent Models -->
            <div class="expoxr-card">
                <div class="expoxr-card-header">
                    <h2>Recent Models</h2>
                    <span class="dashicons dashicons-archive"></span>
                </div>
                <div class="expoxr-card-content">
                    <?php
                    $recent_models = new WP_Query([
                        'post_type' => 'expoxr_model',
                        'posts_per_page' => 5,
                        'order' => 'DESC',
                        'orderby' => 'date'
                    ]);
                    
                    if ($recent_models->have_posts()) {
                        echo '<ul style="margin: 0; padding: 0; list-style: none;">';
                        while ($recent_models->have_posts()) {
                            $recent_models->the_post();
                            echo '<li style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f1;">';
                            echo '<span>' . esc_html(get_the_title()) . '</span>';
                            echo '<span style="color: #646970;">' . esc_html(get_the_date('M j, Y')) . '</span>';
                            echo '</li>';
                        }
                        echo '</ul>';
                        wp_reset_postdata();
                    } else {
                        echo '<p>No models found. Create your first 3D model!</p>';
                    }
                    ?>
                </div>
                <div class="expoxr-card-footer">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-browse-models')); ?>" class="button button-small">View All</a>
                </div>
            </div>
            
            <!-- Shortcode Usage -->
            <div class="expoxr-card">
                <div class="expoxr-card-header">
                    <h2>How to Use</h2>
                    <span class="dashicons dashicons-editor-code"></span>
                </div>
                <div class="expoxr-card-content">
                    <p style="margin-bottom: 15px;">
                        <strong>ExploreXR Free Version uses shortcodes to display 3D models.</strong> 
                        Simply copy the shortcode from your model's edit page and paste it into any post, page, or widget.
                    </p>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                        <strong>Example shortcode:</strong><br>
                        <code style="background: #ffffff; padding: 5px; border-radius: 3px; font-size: 14px;">[expoxr_model id="123"]</code>
                    </div>
                    <p style="margin: 0; color: #646970; font-size: 13px;">
                        <strong>Want WooCommerce or Elementor integration?</strong> 
                        <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-premium')); ?>">Upgrade to Premium</a> 
                        to unlock advanced integrations and features.
                    </p>
                </div>
            </div>
            
            <!-- Resources -->
            <div class="expoxr-card">
                <div class="expoxr-card-header">
                    <h2>Resources</h2>
                    <span class="dashicons dashicons-admin-links"></span>
                </div>
                <div class="expoxr-card-content">
                    <ul style="margin: 0; padding-left: 20px;">
                        <li style="margin-bottom: 8px;"><a href="https://modelviewer.dev/" target="_blank">Model Viewer Documentation</a></li>
                        <li style="margin-bottom: 8px;"><a href="https://sketchfab.com/features/free-3d-models" target="_blank">Free 3D Models (Sketchfab)</a></li>
                        <li style="margin-bottom: 8px;"><a href="https://www.blender.org/" target="_blank">Blender - Free 3D Creation Software</a></li>
                        <li style="margin-bottom: 8px;"><a href="https://khronos.org/gltf/" target="_blank">glTF Format Specification</a></li>
                        <li><a href="https://developer.apple.com/augmented-reality/quick-look/" target="_blank">Apple AR Quick Look (USDZ)</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Featured model grid -->
        <?php
        // Optimized query for featured models dashboard widget
        // Cache the featured models query for 5 minutes to improve performance
        $cache_key = 'expoxr_featured_models_dashboard';
        $featured_models_ids = wp_cache_get($cache_key, 'explorexr');
        
        if (false === $featured_models_ids) {
            // Use direct DB query for better performance instead of meta_query
            global $wpdb;
            $featured_models_ids = $wpdb->get_col($wpdb->prepare("
                SELECT p.ID 
                FROM {$wpdb->posts} p 
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                WHERE p.post_type = %s 
                AND p.post_status = 'publish' 
                AND pm.meta_key = '_expoxr_model_file'
                ORDER BY p.post_date DESC 
                LIMIT 3
            ", 'expoxr_model'));
            
            wp_cache_set($cache_key, $featured_models_ids, 'explorexr', 300); // Cache for 5 minutes
        }
        
        $featured_models = new WP_Query([
            'post_type' => 'expoxr_model',
            'post__in' => $featured_models_ids ? $featured_models_ids : [0], // Use 0 if empty to avoid issues
            'orderby' => 'post__in',
            'posts_per_page' => 3,
            'no_found_rows' => true,
            'update_post_meta_cache' => false
        ]);
        
        if ($featured_models->have_posts()) {
            ?>
            <div class="expoxr-section-header" style="margin-top: 40px; margin-bottom: 20px;">
                <h2>Featured Models</h2>
            </div>
            
            <div class="expoxr-dashboard-grid">
                <?php while ($featured_models->have_posts()) : $featured_models->the_post(); 
                      $model_file = get_post_meta(get_the_ID(), '_expoxr_model_file', true);
                      $poster_url = get_post_meta(get_the_ID(), '_expoxr_model_poster', true);
                      $shortcode = '[expoxr_model id="' . get_the_ID() . '"]';
                ?>
                    <div class="expoxr-card expoxr-model-card">
                        <div class="expoxr-model-thumb">
                            <?php if ($poster_url) : ?>
                                <?php
                                // Try to get attachment ID if it's a WordPress attachment
                                $attachment_id = attachment_url_to_postid($poster_url);
                                if ($attachment_id) {
                                    echo wp_get_attachment_image($attachment_id, 'medium', false, ['alt' => esc_attr(get_the_title())]);
                                } else {
                                    // Fallback for external URLs or non-WordPress images
                                    printf('<img src="%s" alt="%s" loading="lazy">', 
                                        esc_url($poster_url), 
                                        esc_attr(get_the_title())
                                    );
                                }
                                ?>
                            <?php else : ?>
                                <span class="dashicons dashicons-format-image" style="font-size: 48px; opacity: 0.3;"></span>
                            <?php endif; ?>
                            
                            <div class="expoxr-model-actions">
                                <a href="<?php echo esc_url(get_edit_post_link()); ?>" class="button" title="Edit Model">
                                    <span class="dashicons dashicons-edit"></span>
                                </a>
                                <button class="copy-shortcode" data-shortcode="<?php echo esc_attr($shortcode); ?>" title="Copy Shortcode">
                                    <span class="dashicons dashicons-shortcode"></span>
                                </button>
                                <?php if ($model_file) : ?>
                                <button class="view-3d-model" 
                                   data-model-url="<?php echo esc_url($model_file); ?>"
                                   data-model-name="<?php echo esc_attr(get_the_title()); ?>"
                                   data-poster-url="<?php echo esc_url($poster_url); ?>"
                                   title="View Model">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="expoxr-model-details">
                            <h3 class="expoxr-model-title"><?php the_title(); ?></h3>
                            <div class="expoxr-model-meta">
                                <span><?php echo esc_html(get_the_date('M j, Y')); ?></span>
                                <span class="expoxr-badge ar">AR Ready</span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <?php
        }
        ?>
    </div>
      <!-- Model viewer modal for preview -->
    <div id="expoxr-model-modal" class="expoxr-model-modal">
        <div class="expoxr-model-modal-content">
            <span class="expoxr-model-close">&times;</span>
            <h3 id="expoxr-model-title" class="expoxr-model-title">3D Model Preview</h3>
            <model-viewer id="expoxr-model-viewer" camera-controls auto-rotate></model-viewer>
        </div>
    </div>
    <?php
}

/**
 * Enqueue admin UI styles and scripts
 */
function expoxr_admin_ui_enqueue_scripts($hook) {
    // Only load on ExpoXR dashboard page
    if ('toplevel_page_expoxr' !== $hook) {
        return;
    }
    
    // Enqueue CSS
    wp_enqueue_style(
        'expoxr-admin-ui',
        EXPOXR_PLUGIN_URL . 'admin/css/admin-ui.css',
        array(),
        EXPOXR_VERSION
    );
    
    // Enqueue JavaScript
    wp_enqueue_script(
        'expoxr-admin-ui',
        EXPOXR_PLUGIN_URL . 'admin/js/admin-ui.js',
        array('jquery'),
        EXPOXR_VERSION,
        true
    );
    
    // Localize script for translations
    wp_localize_script(
        'expoxr-admin-ui',
        'expoxrAdminUI',
        array(
            'nonce' => wp_create_nonce('expoxr_admin_ui_nonce'),
            'strings' => array(
                'modelPreviewTitle' => esc_html__('3D Model Preview', 'explorexr'),
                'copySuccess' => esc_html__('Shortcode copied to clipboard!', 'explorexr'),
                'copyError' => esc_html__('Failed to copy shortcode', 'explorexr')
            )
        )
    );
}
add_action('admin_enqueue_scripts', 'expoxr_admin_ui_enqueue_scripts');





