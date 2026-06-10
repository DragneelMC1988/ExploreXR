<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Allow uploading of 3D model file types and HDR environment files
add_filter('upload_mimes', function ($mimes) {
    $mimes['glb'] = 'model/gltf-binary';
    $mimes['gltf'] = 'model/gltf+json';
    $mimes['usdz'] = 'model/vnd.usdz+zip';
    
    // HDR/EXR environment files for lighting
    $mimes['hdr'] = 'image/vnd.radiance';
    $mimes['exr'] = 'image/x-exr';

    // WebAssembly decoder binaries (Draco, Basis Universal)
    $mimes['wasm'] = 'application/wasm';

    return $mimes;
});

// Fix wp_check_filetype_and_ext for 3D model files and HDR environment files
// WordPress doesn't recognize GLB/GLTF/USDZ/HDR/EXR file signatures, so we need to bypass the strict MIME check
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
    // Get the file extension
    $filetype = wp_check_filetype($filename, $mimes);
    $ext = $filetype['ext'];
    $type = $filetype['type'];
    
    // If it's a 3D model or HDR environment file, trust the extension
    if (in_array($ext, array('glb', 'gltf', 'usdz', 'hdr', 'exr'), true)) {
        // Check if file exists before accessing
        if (file_exists($file)) {
            $data['ext'] = $ext;
            $data['type'] = $type;
            
            // For GLB files, also accept application/octet-stream
            if ($ext === 'glb' && empty($data['type'])) {
                $data['type'] = 'model/gltf-binary';
            }
            
            // For USDZ files, also accept application/zip
            if ($ext === 'usdz' && empty($data['type'])) {
                $data['type'] = 'model/vnd.usdz+zip';
            }
            
            // For HDR files, set proper MIME type
            if ($ext === 'hdr' && empty($data['type'])) {
                $data['type'] = 'image/vnd.radiance';
            }
            
            // For EXR files, set proper MIME type
            if ($ext === 'exr' && empty($data['type'])) {
                $data['type'] = 'image/x-exr';
            }
        }
    }
    
    return $data;
}, 10, 4);

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
                explorexr_log('ExploreXR: WP_Filesystem not available for file move operation', 'error');
            }
        }
    }
});
