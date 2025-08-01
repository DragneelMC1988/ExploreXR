<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Browse Models page callback
function explorexr_browse_models_page() {
    // Include the model-viewer script
    include EXPLOREXR_PLUGIN_DIR . 'template-parts/model-viewer-script.php';
    
    // Get all 3D models
    $models = get_posts([
        'post_type' => 'explorexr_model',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
      // Set up header variables
    $page_title = 'Browse 3D Models';
    $header_actions = '<a href="' . esc_url(admin_url('admin.php?page=explorexr-create-model')) . '" class="button button-primary">
                        <span class="dashicons dashicons-plus" style="margin-right: 5px;"></span> Create New Model
                       </a>';
    ?>
    <div class="wrap">
        <h1>Browse 3D Models</h1>
        
        <!-- WordPress.org Compliance: This div.wp-header-end is required for WordPress to place admin notices properly -->
        <div class="wp-header-end"></div>
        
        <!-- ExploreXR Plugin Content -->
        <div class="explorexr-admin-container">
        
        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/notifications-area.php'; ?>
        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-header.php'; ?>
        
        <?php
        // Display success/warning/error message when redirected after model creation
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for display purposes only
        if (isset($_GET['created']) && sanitize_text_field(wp_unslash($_GET['created'])) === 'true') {
            $creation_result = get_transient('explorexr_model_created');
            if ($creation_result) {
                delete_transient('explorexr_model_created'); // Clean up transient
                $alert_class = 'success';
                $icon = 'dashicons-yes';
                
                if ($creation_result['type'] === 'warning') {
                    $alert_class = 'warning';
                    $icon = 'dashicons-warning';
                } elseif ($creation_result['type'] === 'error') {
                    $alert_class = 'error';
                    $icon = 'dashicons-no';
                }
                ?>
                <div class="explorexr-alert <?php echo esc_attr($alert_class); ?>">
                    <span class="dashicons <?php echo esc_attr($icon); ?>"></span>
                    <div>
                        <p><?php echo esc_html($creation_result['message']); ?></p>
                    </div>
                </div>
                <?php
            } else {
                // Fallback message if transient is missing
                ?>
                <div class="explorexr-alert success">
                    <span class="dashicons dashicons-yes"></span>
                    <div>
                        <p>3D model created successfully!</p>
                    </div>
                </div>
                <?php
            }
        }
        ?>
         <!-- Shortcode Usage Info -->
         <div class="explorexr-card">
            
            <div class="explorexr-card-content">
               
                <div class="explorexr-usage-tips">
                    <h3>Best Practices</h3>
                    <ul>
                        <li>- Keep your 3D models optimized to ensure fast loading times.</li>
                        <li>- Add a poster image to improve the user experience while models are loading.</li>
                        <li>- Consider providing AR versions of your models for mobile users.</li>
                        <li>- Test your 3D models on different devices and browsers to ensure compatibility.</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Models Browser -->
        <div class="explorexr-card">
            <div class="explorexr-card-header">
                <h2>Your 3D Models</h2>                
            </div>
            <div class="explorexr-card-content">
                <?php if (empty($models)) : ?>
                    <div class="explorexr-alert info">
                        <span class="dashicons dashicons-info"></span>
                        <div>
                            <p>You don't have any 3D models yet. <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-create-model')); ?>">Create your first 3D model</a>.</p>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="explorexr-filter-bar">
                        <div class="explorexr-search-box">
                            <input type="text" id="model-search" placeholder="Search models...">
                            <button type="button" class="button"><span class="dashicons dashicons-search"></span></button>
                        </div>
                        <div class="explorexr-sort-options">
                            <label for="sort-models">Sort by:</label>
                            <select id="sort-models">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="title-az">Title (A-Z)</option>
                                <option value="title-za">Title (Z-A)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="explorexr-models-grid">                        <?php foreach ($models as $model) : 
                            $model_file = get_post_meta($model->ID, '_explorexr_model_file', true);
                            $model_alt_file = get_post_meta($model->ID, '_explorexr_model_alt_file', true);
                            $poster = get_post_meta($model->ID, '_explorexr_model_poster', true);
                            $auto_rotate = get_post_meta($model->ID, '_explorexr_auto_rotate', true) === 'on';
                            $camera_controls = get_post_meta($model->ID, '_explorexr_camera_controls', true) === 'on';
                            $ar_enabled = get_post_meta($model->ID, '_explorexr_model_ar', true);
                            $shortcode = '[explorexr_model id="' . $model->ID . '"]';
                        ?>
                            <div class="explorexr-model-card" data-title="<?php echo esc_attr($model->post_title); ?>" data-date="<?php echo esc_attr($model->post_date); ?>">
                                <div class="explorexr-model-preview">
                                    <?php if (!empty($model_file)) : ?>                                        <model-viewer src="<?php echo esc_url($model_file); ?>"
                                            <?php if (!empty($poster)) : ?>poster="<?php echo esc_url($poster); ?>"<?php endif; ?>
                                            <?php if ($auto_rotate) : ?>auto-rotate<?php endif; ?>
                                            <?php if ($ar_enabled && !empty($model_alt_file)) : ?>ar ar-modes="webxr scene-viewer quick-look" ios-src="<?php echo esc_url($model_alt_file); ?>"<?php endif; ?>
                                            camera-controls
                                            shadow-intensity="1"
                                            style="width: 100%; height: 400px; background-color: #f5f5f5; border-radius: 4px 4px 0 0;">
                                        </model-viewer>
                                    <?php else : ?>
                                        <div class="explorexr-no-preview">
                                            <span class="dashicons dashicons-format-image"></span>
                                            <p>No model preview</p>
                                        </div>
                                    <?php endif; ?>
                                </div>                                <div class="explorexr-model-info">
                                    <h3><?php echo esc_html($model->post_title); ?></h3>
                                    <div class="explorexr-model-meta">
                                        <span><span class="dashicons dashicons-calendar"></span> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($model->post_date))); ?></span>
                                        <span><span class="dashicons dashicons-admin-links"></span> ID: <?php echo esc_html($model->ID); ?></span>
                                    </div>
                                    <div class="explorexr-model-actions">
                                        <a href="<?php echo esc_url(get_edit_post_link($model->ID)); ?>" class="button button-small">
                                            <span class="dashicons dashicons-edit"></span> Edit
                                        </a>
                                        <a href="#" class="button button-small copy-shortcode" data-shortcode="<?php echo esc_attr($shortcode); ?>">
                                            <span class="dashicons dashicons-shortcode"></span> Copy
                                        </a>                                        <?php if (!empty($model_file)) : 
                                            // Use the model file URL directly if it's a full URL
                                            // Otherwise, construct it using the models directory
                                            $file_url = (strpos($model_file, 'http') === 0) ? $model_file : $model_file;
                                        ?>
                                        <button type="button" class="button button-small view-3d-model"
                                           data-model-url="<?php echo esc_url($file_url); ?>"
                                           data-model-name="<?php echo esc_attr($model->post_title); ?>"
                                           data-poster-url="<?php echo esc_url($poster); ?>">
                                            <span class="dashicons dashicons-visibility"></span> View
                                        </button>
                                        <?php else: ?>
                                        <a href="<?php echo esc_url(get_permalink($model->ID)); ?>" class="button button-small" target="_blank">
                                            <span class="dashicons dashicons-visibility"></span> View
                                        </a>
                                        <?php endif; ?>
                                        <button type="button" class="button button-small button-link-delete delete-model" data-model-id="<?php echo esc_attr($model->ID); ?>" data-model-name="<?php echo esc_attr($model->post_title); ?>">
                                            <span class="dashicons dashicons-trash"></span> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
       
    </div>
    
    <!-- Shortcode Copied Notification -->
    <div id="explorexr-copied-notification" style="display: none; position: fixed; bottom: 20px; right: 20px; background-color: #2271b1; color: white; padding: 10px 20px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); z-index: 9999;">
        <p style="margin: 0;"><span class="dashicons dashicons-yes" style="margin-right: 8px;"></span> Shortcode copied to clipboard!</p>
    </div>
    
    <!-- Include the model viewer modal -->
    <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/model-viewer-modal.php'; ?>
    
    <!-- ExploreXR Footer -->
    <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-footer.php'; ?>
    
        </div><!-- .explorexr-admin-container -->
    </div><!-- .wrap -->
    <?php
}





