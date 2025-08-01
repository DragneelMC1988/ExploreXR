<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Allow uploading of 3D model file types
add_filter('upload_mimes', function ($mimes) {
    $mimes['glb'] = 'model/gltf-binary';
    $mimes['gltf'] = 'model/gltf+json';
    $mimes['usdz'] = 'model/vnd.usdz+zip';
    return $mimes;
});

// Handle file uploads and save them in the WordPress uploads models folder
add_action('add_attachment', function ($post_id) {
    $file = get_attached_file($post_id);
    $filetype = wp_check_filetype($file);

    // Check if the uploaded file is a 3D model
    if (in_array($filetype['ext'], ['glb', 'gltf', 'usdz'])) {
        // Ensure constants are defined before using them
        if (!defined('EXPLOREXR_MODELS_DIR')) {
            $upload_dir = wp_upload_dir();
            $models_dir = $upload_dir['basedir'] . '/explorexr_models/';
        } else {
            $models_dir = EXPLOREXR_MODELS_DIR;
        }
        
        // Create the models folder if it doesn't exist
        if (!file_exists($models_dir)) {
            wp_mkdir_p($models_dir);
        }

        // Move the uploaded file to the models folder
        $new_file = $models_dir . basename($file);
        
        // Initialize WP_Filesystem
        if (!function_exists('WP_Filesystem')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        WP_Filesystem();
        global $wp_filesystem;
        
        if ($wp_filesystem && $wp_filesystem->move($file, $new_file)) {
            update_attached_file($post_id, $new_file);
        } elseif (!$wp_filesystem) {
            // Log error if WP_Filesystem is not available
            if (function_exists('error_log') && explorexr_is_debug_enabled()) {
                EXPLOREXR_log('ExploreXR: WP_Filesystem not available for file move operation', 'error');
            }
        }
    }
});

// Note: The EXPLOREXR_handle_model_upload function has been moved to model-helper.php
// to avoid duplicate function declarations.






