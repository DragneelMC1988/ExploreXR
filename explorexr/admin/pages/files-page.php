<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Files page callback
function expoxr_files_page() {
    // Handle file upload
    if (isset($_POST['upload_file_submit']) && isset($_FILES['model_file_upload']) && isset($_FILES['model_file_upload']['size']) && $_FILES['model_file_upload']['size'] > 0) {
        // Verify nonce for security
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'expoxr_upload_file')) {
            echo '<div class="notice notice-error"><p>Security check failed. Please try again.</p></div>';
        } else {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- File upload array is handled by expoxr_handle_model_upload()
            $upload_result = expoxr_handle_model_upload($_FILES['model_file_upload']);
            
            if ($upload_result) {
                echo '<div class="notice notice-success"><p>File uploaded successfully! Refresh the page to see it in the list.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Failed to upload file.</p></div>';
            }
        }
    }
    
    // Handle file deletion
    if (isset($_GET['action']) && sanitize_text_field(wp_unslash($_GET['action'])) === 'delete' && isset($_GET['file'])) {
        // Verify nonce for security
        if (isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'expoxr_delete_file')) {
            $file_name = sanitize_file_name(wp_unslash($_GET['file']));
            $file_path = EXPOXR_MODELS_DIR . $file_name;
            
            // Check if the file exists and is within our models directory to prevent path traversal
            if (file_exists($file_path) && strpos(realpath($file_path), realpath(EXPOXR_MODELS_DIR)) === 0) {
                // Check if the file is being used by any models using WP_Query
                $file_url = EXPOXR_MODELS_URL . $file_name;
                
                $usage_query = new WP_Query([
                    'post_type' => 'expoxr_model',
                    'post_status' => 'publish',
                    'posts_per_page' => 1,
                    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required for checking if file is in use before deletion
                    'meta_query' => [
                        [
                            'key' => '_expoxr_model_file',
                            'value' => $file_url,
                            'compare' => 'LIKE'
                        ]
                    ],
                    'fields' => 'ids'
                ]);
                
                $is_used = $usage_query->have_posts();
                wp_reset_postdata();
                
                // Get model names if the file is used
                if ($is_used) {
                    $model_query = new WP_Query([
                        'post_type' => 'expoxr_model',
                        'post_status' => 'publish',
                        'posts_per_page' => 5,
                        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required for displaying which models use the file
                        'meta_query' => [
                            [
                                'key' => '_expoxr_model_file',
                                'value' => $file_url,
                                'compare' => 'LIKE'
                            ]
                        ]
                    ]);
                    
                    $model_names = [];
                    if ($model_query->have_posts()) {
                        while ($model_query->have_posts()) {
                            $model_query->the_post();
                            $model_names[] = '<a href="' . esc_url(get_edit_post_link(get_the_ID())) . '">' . esc_html(get_the_title()) . '</a>';
                        }
                    }
                    wp_reset_postdata();
                    
                    echo '<div class="notice notice-error"><p>Cannot delete file because it is being used by the following 3D models: ' . esc_html(implode(', ', $model_names)) . '.</p></div>';
                } else {
                    // Delete the file using WordPress function
                    if (wp_delete_file($file_path)) {
                        echo '<div class="notice notice-success"><p>File "' . esc_html($file_name) . '" has been deleted successfully.</p></div>';
                    } else {
                        echo '<div class="notice notice-error"><p>Failed to delete file "' . esc_html($file_name) . '". Please check file permissions.</p></div>';
                    }
                }
            } else {
                echo '<div class="notice notice-error"><p>Invalid file specified.</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>Security check failed. Please try again.</p></div>';
        }
    }
    
    // Include the model-viewer script
    include EXPOXR_PLUGIN_DIR . 'template-parts/model-viewer-script.php';
    
    // Set up header variables
    $page_title = '3D Model Files';
    $header_actions = '<button type="button" class="button button-primary" id="show-upload-form">
                        <span class="dashicons dashicons-upload" style="margin-right: 5px;"></span> Upload New File
                       </button>';
    ?>
    <div class="wrap expoxr-admin-container">
        <!-- WordPress admin notices appear here automatically before our custom content -->
        
        <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/notifications-area.php'; ?>
        <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/admin-header.php'; ?>
        
        <!-- Upload form card (hidden by default) -->
        <div class="expoxr-card" id="upload-form-card" style="display: none; margin-bottom: 20px;">
            <div class="expoxr-card-header">
                <h2>Upload New Model File</h2>
                <span class="dashicons dashicons-upload"></span>
            </div>
            <div class="expoxr-card-content">
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('expoxr_upload_file'); ?>
                    <div class="expoxr-form-group">
                        <label for="model_file_upload">Select 3D Model File</label>
                        <input name="model_file_upload" type="file" id="model_file_upload" accept=".glb,.gltf,.usdz" required />
                        <p class="description">Accepted formats: GLB, GLTF, USDZ. Maximum file size: <?php echo esc_html(get_option('expoxr_max_upload_size', 50)); ?>MB</p>
                    </div>
                    <div class="expoxr-card-footer" style="border-top: none; padding-top: 0;">
                        <input type="submit" name="upload_file_submit" class="button button-primary" value="Upload File" />
                        <button type="button" class="button" id="cancel-upload">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php
        $models_dir = EXPOXR_MODELS_DIR;
        
        // Create models directory if it doesn't exist
        if (!file_exists($models_dir)) {
            wp_mkdir_p($models_dir);
            echo '<div class="notice notice-info"><p>Created models directory.</p></div>';
        }

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
        ?>
          <!-- Files card -->
        <div class="expoxr-card">
            <div class="expoxr-card-header">
                <h2>Available 3D Model Files</h2>
                
            </div>
            <div class="expoxr-card-content">
                <?php if (!empty($files)) : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Preview</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file) : 
                                $file_name = basename($file);
                                $file_type = pathinfo($file, PATHINFO_EXTENSION);
                                $file_size = size_format(filesize($file));
                                $file_url = EXPOXR_MODELS_URL . $file_name;
                                $delete_url = wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'page' => 'expoxr-files',
                                            'action' => 'delete',
                                            'file' => $file_name,
                                        ),
                                        admin_url('admin.php')
                                    ),
                                    'expoxr_delete_file'
                                );
                            ?>
                                <tr>
                                    <td><?php echo esc_html($file_name); ?></td>
                                    <td><?php echo esc_html(strtoupper($file_type)); ?></td>
                                    <td><?php echo esc_html($file_size); ?></td>
                                    <td>
                                        <a href="#" class="view-3d-model" 
                                        data-model-url="<?php echo esc_url($file_url); ?>"
                                        data-model-name="<?php echo esc_attr($file_name); ?>">View Model</a>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url($delete_url); ?>" class="button button-small delete-file" data-filename="<?php echo esc_attr($file_name); ?>">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="expoxr-alert info">
                        <span class="dashicons dashicons-info"></span>
                        <div>
                            <p>No 3D model files found in the models directory. Upload models using the form above.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (empty($files)) : ?>
                <div class="expoxr-card-footer">
                    <button type="button" class="button button-primary" id="show-upload-form-footer">Upload New File</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
    // Include the model viewer modal
    include EXPOXR_PLUGIN_DIR . 'admin/templates/model-viewer-modal.php';
    ?>
    
    <!-- ExpoXR Footer -->
    <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/admin-footer.php'; ?>
    
    <?php
}





