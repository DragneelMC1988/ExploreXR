<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detect which addons have per-model settings enabled for a given model.
 *
 * Each addon uses a different meta key to store its per-model "enabled" state.
 * This function checks the canonical keys and returns matching addon slugs.
 *
 * @param int   $model_id      The model post ID.
 * @param array $active_addons Associative array of addon slug => name that are active WP-wide.
 * @return array List of addon slugs that are enabled for this model.
 */
function explorexr_browse_get_model_addons($model_id, $active_addons) {
    // Map of addon slug → meta key(s) and truthy values used to detect per-model enablement
    $addon_meta_map = array(
        'annotations'     => array('key' => '_explorexr_premium_annotations_enabled', 'truthy' => array('1', 'on', 'yes')),
        'animation'       => array('key' => '_explorexr_premium_animation_enabled',   'truthy' => array('1', 'on', 'yes')),
        'ar'              => array('key' => '_explorexr_premium_ar_enabled',          'truthy' => array('1', 'on', 'yes')),
        'camera'          => array('key' => '_explorexr_premium_camera_enabled',      'truthy' => array('1', 'on', 'yes')),
        'environment'     => array('key' => '_explorexr_environment_enabled',         'truthy' => array('1', 'on', 'yes')),
        'loading'         => array('key' => '_explorexr_loading_enable',              'truthy' => array('1', 'on', 'yes')),
        'materials'       => array('key' => '_explorexr_premium_materials_enabled',   'truthy' => array('on')),
        'morphing'        => array('key' => '_explorexr_morphing_enable',             'truthy' => array('1', 'on', 'yes')),
        'mouse3d'         => array('key' => '_explorexr_mouse3d_enabled',             'truthy' => array('1', 'on', 'yes')),
        'post-processing' => array('key' => '_explorexr_pp_enable',                   'truthy' => array('1', 'on', 'yes')),
        'draggable'       => array('key' => '_explorexr_draggable_enabled',           'truthy' => array('1', 'on', 'yes')),
    );

    $enabled = array();
    foreach ($active_addons as $slug => $name) {
        if (!isset($addon_meta_map[$slug])) {
            // Addons without per-model toggle (e.g. debug, woocommerce) — skip
            continue;
        }
        $meta = $addon_meta_map[$slug];
        $value = get_post_meta($model_id, $meta['key'], true);

        // Also check legacy key for morphing
        if ($slug === 'morphing' && empty($value)) {
            $value = get_post_meta($model_id, '_explorexr_morphing_enabled', true);
        }
        // Also check legacy key for AR
        if ($slug === 'ar' && empty($value)) {
            $value = get_post_meta($model_id, '_explorexr_ar_enabled', true);
        }

        if (!empty($value) && in_array(strtolower($value), $meta['truthy'], true)) {
            $enabled[] = $slug;
        }
    }
    return $enabled;
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

    // Collect active addon slugs for filter UI
    $all_active_addons = array();
    if (defined('EXPLOREXR_IS_PREMIUM') && EXPLOREXR_IS_PREMIUM) {
        $browse_license_handler = class_exists('ExploreXR_License_Handler') ? ExploreXR_License_Handler::instance() : null;
        $browse_addon_manager   = class_exists('ExploreXR_Addon_Manager') ? ExploreXR_Addon_Manager::get_instance() : null;

        if ($browse_license_handler) {
            foreach ($browse_license_handler->get_addons() as $slug => $data) {
                $is_active = false;
                if (function_exists('explorexr_premium_is_addon_active')) {
                    $is_active = explorexr_premium_is_addon_active($slug, false);
                }
                if ($is_active) {
                    $all_active_addons[$slug] = isset($data['name']) ? $data['name'] : ucfirst($slug);
                }
            }
        }
        // Also check registered addons in case they are not in the license map
        if ($browse_addon_manager && method_exists($browse_addon_manager, 'get_registered_addons')) {
            foreach ($browse_addon_manager->get_registered_addons() as $slug => $data) {
                if (isset($all_active_addons[$slug])) {
                    continue;
                }
                $is_active = false;
                if (function_exists('explorexr_premium_is_addon_active')) {
                    $is_active = explorexr_premium_is_addon_active($slug, false);
                }
                if ($is_active) {
                    $all_active_addons[$slug] = isset($data['name']) ? $data['name'] : ucfirst($slug);
                }
            }
        }
    }
      // Set up header variables
    $page_title = 'Browse 3D Models';
    $header_actions = '<a href="' . esc_url(admin_url('admin.php?page=explorexr-create-model')) . '" class="button button-primary">
                        <span class="dashicons dashicons-plus" style="margin-right: 5px;"></span> Create New Model
                       </a>';
    ?>
    <div class="wrap">
        <h1>Browse 3D Models</h1>
        
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
                            <input type="text" id="model-search" placeholder="Search by title or ID...">
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
                    <?php if (!empty($all_active_addons)) : ?>
                    <div class="explorexr-addon-filter-bar">
                        <span class="explorexr-addon-filter-label">Filter by Add-on:</span>
                        <?php
                        $addon_filter_icons = array(
                            'annotations'     => 'dashicons-tag',
                            'ar'              => 'dashicons-smartphone',
                            'animation'       => 'dashicons-video-alt3',
                            'camera'          => 'dashicons-camera',
                            'materials'       => 'dashicons-art',
                            'morphing'        => 'dashicons-image-rotate',
                            'mouse3d'         => 'dashicons-move',
                            'environment'     => 'dashicons-admin-site-alt3',
                            'post-processing' => 'dashicons-admin-appearance',
                            'loading'         => 'dashicons-update',
                            'woocommerce'     => 'dashicons-cart',
                            'draggable'       => 'dashicons-screenoptions',
                            'debug'           => 'dashicons-admin-tools',
                        );
                        foreach ($all_active_addons as $addon_slug => $addon_name) :
                            $icon_class = isset($addon_filter_icons[$addon_slug]) ? $addon_filter_icons[$addon_slug] : 'dashicons-admin-plugins';
                        ?>
                        <button type="button" class="explorexr-addon-filter-btn" data-addon="<?php echo esc_attr($addon_slug); ?>" title="<?php echo esc_attr($addon_name); ?>">
                            <span class="dashicons <?php echo esc_attr($icon_class); ?>"></span>
                            <span class="explorexr-addon-filter-name"><?php echo esc_html($addon_name); ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="explorexr-models-grid">                        <?php foreach ($models as $model) : 
                            $model_file = get_post_meta($model->ID, '_explorexr_model_file', true) ?: '';
                            $shortcode = '[explorexr_model id="' . $model->ID . '"]';

                            // Detect which addons are enabled for this specific model
                            $model_addon_slugs = explorexr_browse_get_model_addons($model->ID, $all_active_addons);
                        ?>
                            <div class="explorexr-model-card" data-title="<?php echo esc_attr($model->post_title); ?>" data-date="<?php echo esc_attr($model->post_date); ?>" data-model-id="<?php echo esc_attr($model->ID); ?>" data-addons="<?php echo esc_attr(implode(',', $model_addon_slugs)); ?>">
                                <div class="explorexr-model-preview">
                                    <?php if (!empty($model_file)) : ?>
                                        <?php 
                                        // Use centralized renderer to apply all addon filters
                                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is pre-escaped in explorexr_render_model_viewer()
                                        echo explorexr_render_model_viewer($model->ID, 'admin', [
                                            'style' => 'width: 100%; height: 400px; background-color: #f5f5f5; border-radius: 4px 4px 0 0;'
                                        ]);
                                        ?>
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
                                    <?php if (!empty($model_addon_slugs)) : ?>
                                    <div class="explorexr-model-addon-badges">
                                        <?php foreach ($model_addon_slugs as $badge_slug) :
                                            $badge_name = isset($all_active_addons[$badge_slug]) ? $all_active_addons[$badge_slug] : ucfirst($badge_slug);
                                            $badge_icon = isset($addon_filter_icons[$badge_slug]) ? $addon_filter_icons[$badge_slug] : 'dashicons-admin-plugins';
                                        ?>
                                        <span class="explorexr-addon-badge" title="<?php echo esc_attr($badge_name); ?>">
                                            <span class="dashicons <?php echo esc_attr($badge_icon); ?>"></span>
                                            <?php echo esc_html($badge_name); ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="explorexr-model-actions">
                                        <a href="<?php echo esc_url(get_edit_post_link($model->ID)); ?>" class="button button-small">
                                            <span class="dashicons dashicons-edit"></span> Edit
                                        </a>
                                        <a href="#" class="button button-small copy-shortcode" data-shortcode="<?php echo esc_attr($shortcode); ?>">
                                            <span class="dashicons dashicons-shortcode"></span> Copy
                                        </a>                                        <?php if (!empty($model_file)) : ?>
                                        <button type="button" class="button button-small view-3d-model"
                                           data-model-id="<?php echo esc_attr($model->ID); ?>"
                                           data-model-name="<?php echo esc_attr($model->post_title); ?>">
                                            <span class="dashicons dashicons-visibility"></span> View
                                        </button>
                                        <?php else : ?>
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
    
    <!-- AJAX nonce for model preview -->
    <input type="hidden" id="explorexr-ajax-nonce" value="<?php echo esc_attr(wp_create_nonce('explorexr-admin-ajax')); ?>" />
    
    <!-- Include the model viewer modal -->
    <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/model-viewer-modal.php'; ?>
    
    <!-- ExploreXR Footer -->
    <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-footer.php'; ?>
    
        </div><!-- .explorexr-admin-container -->
    </div><!-- .wrap -->
    <?php
}




