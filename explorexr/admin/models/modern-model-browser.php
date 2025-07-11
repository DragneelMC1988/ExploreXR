<?php
/**
 * ExploreXR Modern Model Browser
 * A card-based grid layout for browsing 3D models with enhanced filtering
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the modern model browser UI
 */
function expoxr_modern_model_browser_page() {
    // Add a class to the body for specific CSS targeting
    add_filter('admin_body_class', function($classes) {
        return $classes . ' admin-page-expoxr-browse-models';
    });
    
    // Include the model-viewer script for the popup
    include EXPOXR_PLUGIN_DIR . 'template-parts/model-viewer-script.php';
    
    // Handle model deletion if requested
    if (isset($_GET['action']) && sanitize_text_field($_GET['action']) === 'delete' && isset($_GET['model_id'])) {
        $model_id = intval($_GET['model_id']);
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field($_GET['_wpnonce']) : '';
        
        if (wp_verify_nonce($nonce, 'delete_model_' . $model_id)) {
            wp_delete_post($model_id, true);
            ?>
            <div class="notice notice-success">
                <p>Model deleted successfully.</p>
            </div>
            <?php
        } else {
            ?>
            <div class="notice notice-error">
                <p>Security check failed. Please try again.</p>
            </div>
            <?php
        }
    }
    
    // Get filters
    $search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'date';
    $sort_order = isset($_GET['sort_order']) ? sanitize_text_field($_GET['sort_order']) : 'desc';
    
    // Get total models count
    $total_models = wp_count_posts('expoxr_model')->publish;
    ?>
    
    <div class="wrap expoxr-admin-container">
        <!-- Header -->
        <div class="expoxr-admin-header">
            <div class="expoxr-logo">
                <h1>Browse 3D Models</h1>
            </div>
            <div class="expoxr-header-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-create-model')); ?>" class="button button-primary">
                    <span class="dashicons dashicons-plus-alt" style="margin-right: 5px;"></span> Add New Model
                </a>
            </div>
        </div>
        
        <!-- Filters & Search -->
        <div class="expoxr-card" style="margin-bottom: 30px;">
            <div class="expoxr-card-content">
                <form method="get" style="display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-end;">
                    <input type="hidden" name="page" value="expoxr-browse-models">
                    
                    <div class="expoxr-form-group" style="flex: 1; min-width: 200px;">
                        <label for="search">Search Models</label>
                        <input type="text" id="search" name="search" value="<?php echo esc_attr($search_term); ?>" placeholder="Search by title..." class="regular-text">
                    </div>
                    
                    <div class="expoxr-form-group" style="width: 150px;">
                        <label for="sort_by">Sort By</label>
                        <select name="sort_by" id="sort_by">
                            <option value="date" <?php selected($sort_by, 'date'); ?>>Date</option>
                            <option value="title" <?php selected($sort_by, 'title'); ?>>Title</option>
                        </select>
                    </div>
                    
                    <div class="expoxr-form-group" style="width: 150px;">
                        <label for="sort_order">Order</label>
                        <select name="sort_order" id="sort_order">
                            <option value="desc" <?php selected($sort_order, 'desc'); ?>>Newest First</option>
                            <option value="asc" <?php selected($sort_order, 'asc'); ?>>Oldest First</option>
                        </select>
                    </div>
                    
                    <div class="expoxr-form-group" style="align-self: flex-end;">
                        <button type="submit" class="button">Apply Filters</button>
                        <?php if (!empty($search_term) || $sort_by !== 'date' || $sort_order !== 'desc') : ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-browse-models')); ?>" class="button">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Models Count -->
        <div style="margin-bottom: 20px;">
            <span style="font-size: 14px; color: #646970;">
                <?php 
                if ($total_models === 0) {
                    echo 'No models found.';
                } elseif ($total_models === 1) {
                    echo '1 model found.';
                } else {
                    echo esc_html($total_models) . ' models found.';
                }
                ?>
            </span>
            <?php if (!empty($search_term)) : ?>
                <span style="font-size: 14px; color: #646970; margin-left: 10px;">
                    Search results for: <strong><?php echo esc_html($search_term); ?></strong>
                </span>
            <?php endif; ?>
        </div>
        
        <!-- Models Grid -->
        <?php
        // Set up query args
        $args = array(
            'post_type' => 'expoxr_model',
            'posts_per_page' => -1,
            'order' => strtoupper($sort_order),
            'orderby' => ($sort_by === 'title') ? 'title' : 'date'
        );
        
        // Add search if provided
        if (!empty($search_term)) {
            $args['s'] = $search_term;
        }
        
        $models = new WP_Query($args);
        
        if ($models->have_posts()) {
            ?>
            <div class="expoxr-dashboard-grid">
                <?php while ($models->have_posts()) : $models->the_post(); 
                      $model_file = get_post_meta(get_the_ID(), '_expoxr_model_file', true);
                      $poster_url = get_post_meta(get_the_ID(), '_expoxr_model_poster', true);
                      $poster_id = get_post_meta(get_the_ID(), '_expoxr_model_poster_id', true);
                      $viewer_width = get_post_meta(get_the_ID(), '_expoxr_viewer_width', true);
                      $viewer_height = get_post_meta(get_the_ID(), '_expoxr_viewer_height', true);
                      $shortcode = '[expoxr_model id="' . get_the_ID() . '"]';
                      
                      // Delete URL with nonce for security
                      $delete_url = wp_nonce_url(
                          add_query_arg(
                              array(
                                  'page' => 'expoxr-browse-models',
                                  'action' => 'delete',
                                  'model_id' => get_the_ID(),
                              ),
                              admin_url('admin.php')
                          ),
                          'delete_model_' . get_the_ID()
                      );
                ?>
                    <div class="expoxr-card expoxr-model-card">
                        <div class="expoxr-model-thumb">
                            <?php if ($poster_url) : ?>
                                <?php
                                if (!empty($poster_id)) {
                                    echo wp_get_attachment_image($poster_id, 'medium', false, array('alt' => esc_attr(get_the_title())));
                                } else {
                                    // Fallback for cases where we have URL but no attachment ID
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
                                <?php if ($model_file) : 
                                    // Extract filename from file path
                                    $file_name = basename($model_file);
                                    // Generate URL using EXPOXR_MODELS_URL
                                    $file_url = EXPOXR_MODELS_URL . $file_name;
                                ?>
                                <button class="view-3d-model" 
                                   data-model-url="<?php echo esc_url($file_url); ?>"
                                   data-model-name="<?php echo esc_attr(get_the_title()); ?>"
                                   data-poster-url="<?php echo esc_url($poster_url); ?>"
                                   title="View Model">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <?php endif; ?>                                <button class="delete-model" 
                                   data-delete-url="<?php echo esc_url($delete_url); ?>"
                                   data-model-id="<?php echo esc_attr(get_the_ID()); ?>"
                                   data-model-name="<?php echo esc_attr(get_the_title()); ?>" title="Delete Model">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </div>
                        <div class="expoxr-model-details">
                            <h3 class="expoxr-model-title"><?php the_title(); ?></h3>
                            <div class="expoxr-model-meta">
                                <span><?php echo esc_html(get_the_date('M j, Y')); ?></span>
                                <?php if ($model_file) : ?>
                                    <span class="expoxr-badge ar">AR Ready</span>
                                <?php else : ?>
                                    <span class="expoxr-badge">No Model</span>
                                <?php endif; ?>
                            </div>
                            <div class="expoxr-model-info" style="margin-top: 10px; font-size: 12px; color: #646970;">
                                <div><strong>ID:</strong> <?php echo esc_html(get_the_ID()); ?></div>
                                <?php if ($viewer_width && $viewer_height) : ?>
                                    <div><strong>Size:</strong> <?php echo esc_html($viewer_width); ?> × <?php echo esc_html($viewer_height); ?></div>
                                <?php endif; ?>
                                <div style="margin-top: 5px;"><?php echo esc_html($shortcode); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <?php
        } else {
            ?>
            <div class="expoxr-alert info">
                <span class="dashicons dashicons-info"></span>
                <div>
                    <p>No 3D models found. <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-create-model')); ?>">Create your first model</a>.</p>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    
    <!-- Include the model viewer modal -->
    <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/model-viewer-modal.php'; ?>
    
    <script>
    jQuery(document).ready(function($) {
        // Model viewer modal functionality
        const modal = $('#expoxr-model-modal');
        const modelViewer = $('#expoxr-model-viewer');
        const modelTitle = $('#expoxr-model-title');
        
        // Open modal when clicking View Model
        $('.view-3d-model').on('click', function(e) {
            e.preventDefault();
            const modelUrl = $(this).data('model-url');
            const modelName = $(this).data('model-name');
            const posterUrl = $(this).data('poster-url');
            
            // Update model viewer source and title
            modelViewer.attr('src', modelUrl);
            modelTitle.text('3D Model Preview: ' + modelName);
            
            // Add poster if available
            if (posterUrl) {
                modelViewer.attr('poster', posterUrl);
            } else {
                modelViewer.removeAttr('poster');
            }
            
            // Show modal
            modal.css('display', 'block');
        });
        
        // Close modal
        $('.expoxr-model-close').on('click', function() {
            modal.css('display', 'none');
            modelViewer.attr('src', '');
            modelViewer.removeAttr('poster');
        });
        
        // Close modal when clicking outside of the content
        $(window).on('click', function(e) {
            if (e.target === modal[0]) {
                modal.css('display', 'none');
                modelViewer.attr('src', '');
                modelViewer.removeAttr('poster');
            }
        });
        
        // Copy shortcode functionality
        $('.copy-shortcode').on('click', function() {
            const shortcode = $(this).data('shortcode');
            navigator.clipboard.writeText(shortcode);
            
            // Show a quick success notification
            const $this = $(this);
            const originalIcon = $this.html();
            $this.html('<span class="dashicons dashicons-yes" style="color:#46b450;"></span>');
            
            setTimeout(function() {
                $this.html(originalIcon);
            }, 1500);
        });
        
        // Delete model confirmation
        $('.delete-model').on('click', function() {
            const modelName = $(this).data('model-name');
            const deleteUrl = $(this).data('delete-url');
            
            if (confirm('Are you sure you want to delete the model "' + modelName + '"? This action cannot be undone.')) {
                window.location.href = deleteUrl;
            }
        });
    });
    </script>
    <?php
}





