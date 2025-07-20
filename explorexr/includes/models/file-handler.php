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
        $upload_dir = EXPOXR_MODELS_DIR;
        
        // Create the models folder if it doesn't exist
        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
        }

        // Move the uploaded file to the models folder
        $new_file = $upload_dir . basename($file);
        
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
            if (function_exists('error_log') && get_option('expoxr_debug_mode', false)) {
                expoxr_log('ExploreXR: WP_Filesystem not available for file move operation', 'error');
            }
        }
    }
});

// Note: The expoxr_handle_model_upload function has been moved to model-helper.php
// to avoid duplicate function declarations.





