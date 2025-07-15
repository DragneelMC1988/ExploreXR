<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard page callback
 */
function expoxr_dashboard_page() {
    // Include the model-viewer script for the popup
    include EXPOXR_PLUGIN_DIR . 'template-parts/model-viewer-script.php';
    
    // Get statistics
    $total_models = wp_count_posts('expoxr_model')->publish;
    
    // Get most recent models
    $recent_models = get_posts([
        'post_type' => 'expoxr_model',
        'post_status' => 'publish',
        'posts_per_page' => 5,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
    
    // Get model files info
    $models_dir = EXPOXR_MODELS_DIR;
    
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
    $total_files = count($files);
    
    // Calculate storage usage
    $total_size = 0;
    foreach ($files as $file) {
        $total_size += filesize($file);
    }
    
    // Format size
    $size_format = function($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    };
    
    $formatted_size = $size_format($total_size);
      // Check if Model Viewer is fully operational
    $cdn_source = get_option('expoxr_cdn_source', 'local');
    $model_viewer_version = get_option('expoxr_model_viewer_version', '3.3.0');
    $model_viewer_url = ($cdn_source === 'cdn') 
        ? "https://unpkg.com/@google/model-viewer@{$model_viewer_version}/dist/model-viewer.min.js"
        : EXPOXR_PLUGIN_URL . 'assets/js/model-viewer.min.js';
    
    $model_viewer_status = 'operational';
    if ($cdn_source === 'cdn') {
        $response = wp_remote_head($model_viewer_url);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            $model_viewer_status = 'issue';
        }
    } else {
        // Check for UMD version first, then minified version
        $umd_path = EXPOXR_PLUGIN_DIR . 'assets/js/model-viewer-umd.js';
        $min_path = EXPOXR_PLUGIN_DIR . 'assets/js/model-viewer.min.js';
        
        if (!file_exists($umd_path) && !file_exists($min_path)) {
            $model_viewer_status = 'missing';
        }
    }
      // Set up header variables
    $page_title = 'ExploreXR Dashboard';
    $header_actions = '<a href="' . esc_url(admin_url('admin.php?page=expoxr-create-model')) . '" class="button button-primary">
                        <span class="dashicons dashicons-plus" style="margin-right: 5px;"></span> Create New Model
                       </a>';
    ?>
    <div class="wrap expoxr-admin-container">
        <!-- WordPress admin notices appear here automatically before our custom content -->
        
        <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/notifications-area.php'; ?>
        
        <!-- Moving Gradient Banner -->
        <div class="expoxr-gradient-banner">
            <div class="expoxr-gradient-banner-content">
                <?php 
                // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Plugin logo for admin interface
                printf('<img src="%s" alt="%s" class="expoxr-banner-logo" loading="lazy">', 
                    esc_url(EXPOXR_PLUGIN_URL . 'assets/img/logos/exploreXR-Logo.png'), 
                    esc_attr__('ExploreXR Logo', 'explorexr')
                );
                ?>
                <p><?php esc_html_e('Enhance your website with stunning 3D model experiences', 'explorexr'); ?></p>
            </div>
        </div>
        
        <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/admin-header.php'; ?>
        
        <?php 
        // Free version always shows upgrade banner unless dismissed
        if (defined('EXPOXR_IS_FREE') && EXPOXR_IS_FREE) : 
            // Check if banner has been dismissed for this session
            $banner_dismissed = get_transient('expoxr_pro_banner_dismissed_' . get_current_user_id());
            if (!$banner_dismissed) :
        ?>
        <div class="expoxr-pro-banner" id="expoxr-pro-banner">
            <div class="expoxr-pro-banner-content">
                <button type="button" class="expoxr-banner-dismiss" aria-label="Dismiss banner">&times;</button>
                <h3>Upgrade to ExploreXR Pro!</h3>
                <p>Enhance your 3D model experience with premium features.</p>
                <ul class="expoxr-pro-features">
                    <li><span class="dashicons dashicons-yes"></span> Advanced AR Features</li>
                    <li><span class="dashicons dashicons-yes"></span> Expert Camera Controls</li>
                    <li><span class="dashicons dashicons-yes"></span> Model Annotations</li>
                    <li><span class="dashicons dashicons-yes"></span> Priority Support</li>
                </ul>
                <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-premium')); ?>" class="button button-primary">Learn More</a>
            </div>           
        </div>
        <?php 
            endif; // End banner dismissed check
        endif; // End free version check
        ?>
        
        <?php if ($model_viewer_status !== 'operational') : 
            $alert_message = '';
            if ($model_viewer_status === 'issue') {
                $alert_message = '<p><strong>Model Viewer CDN Issue:</strong> The plugin is configured to use the CDN, but we couldn\'t connect to it. You may want to switch to the local version in settings.</p>';
            } elseif ($model_viewer_status === 'missing') {
                $alert_message = '<p><strong>Model Viewer Local File Missing:</strong> The local Model Viewer script file is missing. Please switch to CDN mode in settings or reinstall the plugin.</p>';
            }
            include EXPOXR_PLUGIN_DIR . 'admin/templates/info-alert.php';
        endif; ?>
        
        <!-- Status Overview -->
        <div class="expoxr-status-grid">
            <!-- Models Card -->
            <div class="expoxr-card expoxr-status-card">
                <div class="expoxr-status-card-inner">
                    <div class="expoxr-status-icon">
                        <span class="dashicons dashicons-format-gallery"></span>
                    </div>
                    <div class="expoxr-status-details">
                        <h2><?php echo esc_html($total_models); ?></h2>
                        <p>3D Models</p>
                    </div>
                </div>
                <div class="expoxr-status-action">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-browse-models')); ?>">View All</a>
                </div>
            </div>
            
            <!-- Files Card -->
            <div class="expoxr-card expoxr-status-card">
                <div class="expoxr-status-card-inner">
                    <div class="expoxr-status-icon">
                        <span class="dashicons dashicons-media-default"></span>
                    </div>
                    <div class="expoxr-status-details">
                        <h2><?php echo esc_html($total_files); ?></h2>
                        <p>3D Files</p>
                    </div>
                </div>
                <div class="expoxr-status-action">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-files')); ?>">Manage Files</a>
                </div>
            </div>
            
            <!-- Storage Card -->
            <div class="expoxr-card expoxr-status-card">
                <div class="expoxr-status-card-inner">
                    <div class="expoxr-status-icon">
                        <span class="dashicons dashicons-cloud"></span>
                    </div>
                    <div class="expoxr-status-details">
                        <h2><?php echo esc_html($formatted_size); ?></h2>
                        <p>Storage Used</p>
                    </div>
                </div>
                <div class="expoxr-status-action">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-files')); ?>">Optimize</a>
                </div>
            </div>
            
            <!-- System Status Card -->
            <div class="expoxr-card expoxr-status-card">
                <div class="expoxr-status-card-inner">
                    <div class="expoxr-status-icon">
                        <?php if ($model_viewer_status === 'operational') : ?>
                            <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                        <?php else : ?>
                            <span class="dashicons dashicons-warning" style="color: #dc3232;"></span>
                        <?php endif; ?>
                    </div>
                    <div class="expoxr-status-details">
                        <h2>System</h2>
                        <p><?php echo esc_html($model_viewer_status === 'operational' ? 'All Systems Operational' : 'Attention Required'); ?></p>
                    </div>
                </div>
                <div class="expoxr-status-action">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-settings')); ?>">Check Settings</a>
                </div>
            </div>
        </div>
        
        <!-- Recent Models -->
        <?php
        // Setup card parameters
        $card_title = 'Recent Models';
        $card_icon = 'list-view';
        ob_start();
        ?>
        <?php if (empty($recent_models)) : 
            $alert_message = '<p>You haven\'t created any 3D models yet. <a href="' . esc_url(admin_url('admin.php?page=expoxr-create-model')) . '">Create your first 3D model</a>.</p>';
            include EXPOXR_PLUGIN_DIR . 'admin/templates/info-alert.php';
        else : ?>
            <table class="expoxr-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Created</th>
                        <th>Shortcode</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_models as $model) : 
                        $shortcode = '[expoxr_model id="' . $model->ID . '"]';
                    ?>
                        <tr>
                            <td><?php echo esc_html($model->post_title); ?></td>
                            <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($model->post_date))); ?></td>
                            <td>
                                <code><?php echo esc_html($shortcode); ?></code>
                                <button type="button" class="button button-small copy-shortcode" data-shortcode="<?php echo esc_attr($shortcode); ?>" style="margin-left: 5px;">
                                    <span class="dashicons dashicons-clipboard" style="font-size: 14px; width: 14px; height: 14px;"></span> Copy Shortcode
                                </button>
                            </td>
                            <td>
                                <div class="expoxr-action-buttons">
                                    <a href="<?php echo esc_url(get_edit_post_link($model->ID)); ?>" class="button button-small">
                                        <span class="dashicons dashicons-edit" style="font-size: 14px; width: 14px; height: 14px; margin-right: 2px;"></span> Edit
                                    </a>                                    <?php 
                                    // Get model file information
                                    $model_file = get_post_meta($model->ID, '_expoxr_model_file', true);
                                    $poster_url = get_post_meta($model->ID, '_expoxr_model_poster', true);
                                    
                                    // Use the model file URL directly if it's a full URL
                                    // Otherwise, use it as-is
                                    $file_url = (strpos($model_file, 'http') === 0) ? $model_file : $model_file;
                                    
                                    if ($model_file) : 
                                    ?>
                                    <?php else: ?>
                                    <a href="<?php echo esc_url(get_permalink($model->ID)); ?>" class="button button-small" target="_blank">
                                        <span class="dashicons dashicons-visibility" style="font-size: 14px; width: 14px; height: 14px; margin-right: 2px;"></span> View
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($total_models > 5) : ?>
                <div class="expoxr-view-all">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-browse-models')); ?>" class="button">
                        <span class="dashicons dashicons-format-gallery" style="margin-right: 5px;"></span> View All Models
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <?php
        $card_content = ob_get_clean();
        include EXPOXR_PLUGIN_DIR . 'admin/templates/card.php';
        ?>
        

         <!-- Premium Features -->
            <?php
            $card_title = 'Upgrade to Premium';
            $card_icon = 'star-filled';
            ob_start();
            ?>
            <p>Unlock powerful features with our 7 specialized addons. Choose the ones that fit your needs based on your subscription tier:</p>
            <div class="expoxr-premium-features">
                <div class="expoxr-premium-feature">
                    <div class="expoxr-premium-icon">
                        <span class="dashicons dashicons-admin-comments"></span>
                    </div>
                    <div class="expoxr-premium-info">
                        <h4>Interactive Annotations</h4>
                        <p>Add clickable hotspots with rich content to guide your users through your 3D models.</p>
                    </div>
                </div>
                <div class="expoxr-premium-feature">
                    <div class="expoxr-premium-icon">
                        <span class="dashicons dashicons-format-video"></span>
                    </div>
                    <div class="expoxr-premium-info">
                        <h4>Advanced Animations</h4>
                        <p>Control multiple animations, create custom sequences, and add smooth transitions.</p>
                    </div>
                </div>
                <div class="expoxr-premium-feature">
                    <div class="expoxr-premium-icon">
                        <span class="dashicons dashicons-smartphone"></span>
                    </div>
                    <div class="expoxr-premium-info">
                        <h4>Enhanced AR Features</h4>
                        <p>Advanced augmented reality with better tracking, lighting, and iOS Quick Look support.</p>
                    </div>
                </div>
                <div class="expoxr-premium-feature">
                    <div class="expoxr-premium-icon">
                        <span class="dashicons dashicons-camera"></span>
                    </div>
                    <div class="expoxr-premium-info">
                        <h4>Professional Camera Controls</h4>
                        <p>Custom camera paths, smooth animations, and advanced view controls for cinematic experiences.</p>
                    </div>
                </div>
                <div class="expoxr-premium-feature">
                    <div class="expoxr-premium-icon">
                        <span class="dashicons dashicons-art"></span>
                    </div>
                    <div class="expoxr-premium-info">
                        <h4>Material Variants</h4>
                        <p>Switch between different textures, colors, and materials for product customization.</p>
                    </div>
                </div>
                <div class="expoxr-premium-feature">
                    <div class="expoxr-premium-icon">
                        <span class="dashicons dashicons-performance"></span>
                    </div>
                    <div class="expoxr-premium-info">
                        <h4>Loading Customization</h4>
                        <p>Customize loading screens, progress bars, and transitions for better user experience.</p>
                    </div>
                </div>
                <div class="expoxr-premium-feature">
                    <div class="expoxr-premium-icon">
                        <span class="dashicons dashicons-cart"></span>
                    </div>
                    <div class="expoxr-premium-info">
                        <h4>WooCommerce Integration</h4>
                        <p>Seamlessly integrate 3D models with your online store for immersive product experiences.</p>
                    </div>
                </div>
                <div class="expoxr-premium-feature">
                    <div class="expoxr-premium-icon">
                        <span class="dashicons dashicons-info"></span>
                    </div>
                    <div class="expoxr-premium-info">
                        <h4>And More Many Addons Coming Soon</h4>
                        <p>We're constantly working on exciting new addons to enhance your 3D experience even further!</p>
                    </div>
                </div>
            </div>
            <div class="expoxr-premium-pricing">
                <div class="expoxr-pricing-tiers">
                    <div class="expoxr-pricing-tier">
                        <h4>Pro</h4>
                        <div class="expoxr-price">€59/year</div>
                        <div class="expoxr-addon-count">2 Addons</div>
                        <p>Choose any 2 addons</p>
                    </div>
                    <div class="expoxr-pricing-tier featured">
                        <h4>Plus</h4>
                        <div class="expoxr-price">€99/year</div>
                        <div class="expoxr-addon-count">4 Addons</div>
                        <p>Choose any 4 addons</p>
                        <span class="expoxr-popular-badge">Most Popular</span>
                    </div>
                    <div class="expoxr-pricing-tier">
                        <h4>Ultra</h4>
                        <div class="expoxr-price">€179/year</div>
                        <div class="expoxr-addon-count">All Addons</div>
                        <p>Complete collection</p>
                    </div>
                </div>
                <div class="expoxr-addon-note">
                    <p><strong>All tiers include:</strong> Core 3D model viewer + your chosen addons. Addons can be activated/deactivated through your license dashboard.</p>
                </div>
            </div>
            <div class="expoxr-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-premium')); ?>" class="button button-primary button-large">
                    <span class="dashicons dashicons-unlock" style="margin-right: 5px;"></span> Upgrade Now
                </a>
                <a href="https://expoxr.com/explorexr/pricing" target="_blank" class="button">
                    <span class="dashicons dashicons-info" style="margin-right: 5px;"></span> Compare Plans
                </a>
            </div>
            <?php
            $card_content = ob_get_clean();
            include EXPOXR_PLUGIN_DIR . 'admin/templates/card.php';
            ?>
        <!-- Resources and Help -->
        <div class="expoxr-card-grid">           
            
            <!-- Quick Start Guide -->
            <?php
            $card_title = 'Quick Start Guide';
            $card_icon = 'welcome-learn-more';
            ob_start();
            ?>
            <p>New to ExploreXR? Here's how to get started:</p>
            <ol>
                <li><strong>Upload 3D Models</strong> - Go to the Files page to upload your GLB/GLTF files.</li>
                <li><strong>Create a 3D Model</strong> - Use the Create New Model page to configure your model.</li>
                <li><strong>Use the Shortcode</strong> - Copy the shortcode and paste it into any post or page.</li>
            </ol>
            <div class="expoxr-actions">
                <a href="https://expoxr.com/explorexr/documentation/quick-start" target="_blank" class="button">
                    <span class="dashicons dashicons-book" style="margin-right: 5px;"></span> Full Documentation
                </a>
            </div>
            <?php
            $card_content = ob_get_clean();
            include EXPOXR_PLUGIN_DIR . 'admin/templates/card.php';
            ?>
            
            <!-- Support -->
            <?php
            $card_title = 'Support';
            $card_icon = 'editor-help';
            ob_start();
            ?>
            <p>Need help with ExploreXR? Our support team is here to assist you:</p>
            <ul>
                <li><strong>Documentation:</strong> Find detailed guides and tutorials on our website.</li>
                <li><strong>Support Forum:</strong> Ask questions and get help from our community.</li>
                <li><strong>Premium Support:</strong> Direct assistance for premium users.</li>
            </ul>
            <div class="expoxr-actions">
                <a href="https://expoxr.com/support" target="_blank" class="button">
                    <span class="dashicons dashicons-sos" style="margin-right: 5px;"></span> Get Support
                </a>
            </div>
            <?php
            $card_content = ob_get_clean();
            include EXPOXR_PLUGIN_DIR . 'admin/templates/card.php';
            ?>
        </div>
    </div>
    
    <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/shortcode-notification.php'; ?>
    
    <!-- Include the model viewer modal -->
    <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/model-viewer-modal.php'; ?>
    
    <!-- ExpoXR Footer -->
    <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/admin-footer.php'; ?>
    </div>
    <?php
}





