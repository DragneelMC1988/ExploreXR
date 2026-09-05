<?php
/**
 * Helper functions for handling 3D models
 * 
  * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validate glTF JSON and local resource references.
 *
 * @param string $json JSON document.
 * @return true|WP_Error
 */
function explorexr_free_validate_gltf_json($json) {
    $document = json_decode(rtrim($json, "\x00\x20\t\n\r"), true);
    if (!is_array($document)
        || empty($document['asset']['version'])
        || strpos((string) $document['asset']['version'], '2') !== 0) {
        return new WP_Error('invalid_gltf', __('The uploaded file is not a valid glTF 2.0 model.', 'explorexr'));
    }

    foreach (array('buffers', 'images') as $collection) {
        if (empty($document[$collection]) || !is_array($document[$collection])) {
            continue;
        }
        foreach ($document[$collection] as $item) {
            if (!is_array($item) || empty($item['uri']) || !is_string($item['uri'])) {
                continue;
            }
            $uri = trim($item['uri']);
            if (strpos($uri, 'data:') === 0) {
                continue;
            }
            $path = (string) wp_parse_url($uri, PHP_URL_PATH);
            for ($decode_pass = 0; $decode_pass < 3; $decode_pass++) {
                $next_path = rawurldecode($path);
                if ($next_path === $path) {
                    break;
                }
                $path = $next_path;
            }
            if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $uri)
                || strpos($uri, '//') === 0
                || preg_match('/[\x00-\x1F\x7F]/', $path)
                || strpos($path, '\\') !== false
                || strpos($path, '/') === 0
                || preg_match('#(^|/)\.\.(/|$)#', $path)) {
                return new WP_Error('unsafe_gltf_uri', __('The model contains an unsafe external file reference.', 'explorexr'));
            }
        }
    }

    return true;
}

/**
 * Validate a GLB file.
 *
 * @param string $path Uploaded path.
 * @return true|WP_Error
 */
function explorexr_free_validate_glb($path) {
    $size = filesize($path);
    if (false === $size || $size < 20) {
        return new WP_Error('invalid_glb', __('The GLB header is invalid.', 'explorexr'));
    }

    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Bounded local upload read; WP_Filesystem has no offset API.
    $header = file_get_contents($path, false, null, 0, 12);
    $values = is_string($header) && 12 === strlen($header) ? unpack('a4magic/Vversion/Vlength', $header) : false;
    if (!$values || 'glTF' !== $values['magic'] || 2 !== (int) $values['version'] || (int) $values['length'] !== (int) $size) {
        return new WP_Error('invalid_glb', __('The GLB header or length is invalid.', 'explorexr'));
    }

    $offset = 12;
    $found_json = false;
    $chunk_index = 0;
    while ($offset < $size) {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Bounded local upload read; WP_Filesystem has no offset API.
        $chunk_header = file_get_contents($path, false, null, $offset, 8);
        $chunk = is_string($chunk_header) && 8 === strlen($chunk_header) ? unpack('Vlength/Vtype', $chunk_header) : false;
        if (!$chunk) {
            return new WP_Error('invalid_glb', __('The GLB chunk table is incomplete.', 'explorexr'));
        }
        $length = (int) $chunk['length'];
        $offset += 8;
        if (0 !== $length % 4 || $offset + $length > $size) {
            return new WP_Error('invalid_glb', __('The GLB contains an invalid chunk.', 'explorexr'));
        }
        if (0 === $chunk_index && 0x4E4F534A !== (int) $chunk['type']) {
            return new WP_Error('invalid_glb_json', __('The first GLB chunk must contain JSON.', 'explorexr'));
        }
        if (0x4E4F534A === (int) $chunk['type']) {
            if ($found_json || $length > 32 * 1024 * 1024) {
                return new WP_Error('invalid_glb_json', __('The GLB JSON chunk is invalid or too large.', 'explorexr'));
            }
            $json = '';
            $remaining = $length;
            while ($remaining > 0) {
                $part_offset = $offset + $length - $remaining;
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Bounded 1 MB local upload read; WP_Filesystem has no offset API.
                $part = file_get_contents($path, false, null, $part_offset, min(1048576, $remaining));
                if (false === $part || '' === $part) {
                    return new WP_Error('invalid_glb_json', __('The GLB JSON chunk is incomplete.', 'explorexr'));
                }
                $json .= $part;
                $remaining -= strlen($part);
            }
            $result = explorexr_free_validate_gltf_json($json);
            if (is_wp_error($result)) {
                return $result;
            }
            $found_json = true;
        }
        $offset += $length;
        $chunk_index++;
    }

    return $found_json && $offset === $size
        ? true
        : new WP_Error('invalid_glb', __('The GLB lacks a valid JSON chunk.', 'explorexr'));
}

/**
 * Validate a USDZ archive.
 *
 * @param string $path Uploaded path.
 * @return true|WP_Error
 */
function explorexr_free_validate_usdz($path) {
    if (!class_exists('ZipArchive')) {
        return new WP_Error('zip_support_missing', __('ZIP support is required to validate USDZ files.', 'explorexr'));
    }
    $zip = new ZipArchive();
    if (true !== $zip->open($path, ZipArchive::RDONLY) || $zip->numFiles < 1 || $zip->numFiles > 2048) {
        return new WP_Error('invalid_usdz', __('The USDZ archive is invalid.', 'explorexr'));
    }
    $compressed_size = max(1, (int) filesize($path));
    $total_size = 0;
    $has_usd = false;
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $stat = $zip->statIndex($index);
        $name = $stat && isset($stat['name']) ? $stat['name'] : '';
        for ($decode_pass = 0; $decode_pass < 3; $decode_pass++) {
            $next_name = rawurldecode($name);
            if ($next_name === $name) {
                break;
            }
            $name = $next_name;
        }
        if ('' === $name || false !== strpos($name, "\0") || strpos($name, '\\') !== false || strpos($name, '/') === 0 || preg_match('#(^|/)\.\.(/|$)#', $name)) {
            $zip->close();
            return new WP_Error('unsafe_usdz_path', __('The USDZ archive contains an unsafe path.', 'explorexr'));
        }
        $operations = 0;
        $attributes = 0;
        if ($zip->getExternalAttributesIndex($index, $operations, $attributes)
            && 0120000 === (($attributes >> 16) & 0170000)) {
            $zip->close();
            return new WP_Error('unsafe_usdz_link', __('The USDZ archive contains a symbolic link.', 'explorexr'));
        }
        $total_size += isset($stat['size']) ? (int) $stat['size'] : 0;
        if ($total_size > 1024 * 1024 * 1024 || $total_size > $compressed_size * 50) {
            $zip->close();
            return new WP_Error('usdz_archive_bomb', __('The USDZ archive expands beyond safe limits.', 'explorexr'));
        }
        $has_usd = $has_usd || (bool) preg_match('/\.(usd|usda|usdc)$/i', $name);
    }
    $zip->close();
    return $has_usd ? true : new WP_Error('invalid_usdz', __('The USDZ archive does not contain a USD model.', 'explorexr'));
}

/**
 * Validate a model upload.
 *
 * @param array $file Uploaded file data.
 * @return array|WP_Error
 */
function explorexr_sanitize_file_upload($file, $args = array()) {
    if (empty($file) || !is_array($file) || empty($file['tmp_name']) || empty($file['name'])) {
        return new WP_Error('no_file', __('No file was uploaded.', 'explorexr'));
    }
    if (!current_user_can('upload_files')) {
        return new WP_Error('permission_denied', __('You do not have permission to upload files.', 'explorexr'));
    }
    if (!isset($file['error']) || UPLOAD_ERR_OK !== (int) $file['error'] || !is_uploaded_file($file['tmp_name'])) {
        return new WP_Error('invalid_upload', __('The file was not uploaded securely.', 'explorexr'));
    }

    $name = sanitize_file_name($file['name']);
    $extension = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($extension, array('glb', 'gltf', 'usdz'), true)) {
        return new WP_Error('invalid_file_type', __('Only GLB, GLTF, and USDZ files are allowed.', 'explorexr'));
    }

    $size = filesize($file['tmp_name']);
    $configured_max = isset($args['max_size'])
        ? absint($args['max_size'])
        : absint(get_option('explorexr_max_upload_size', 150)) * 1024 * 1024;
    $server_max = wp_max_upload_size();
    $max_size = $server_max > 0 ? min($configured_max, $server_max) : $configured_max;
    if (false === $size || $size < 1 || $size > $max_size) {
        return new WP_Error('file_too_large', __('The model file is empty or exceeds the upload limit.', 'explorexr'));
    }

    if ('glb' === $extension) {
        $result = explorexr_free_validate_glb($file['tmp_name']);
    } elseif ('gltf' === $extension) {
        if ($size > 32 * 1024 * 1024) {
            return new WP_Error('invalid_gltf', __('The glTF JSON file is too large.', 'explorexr'));
        }
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local verified upload; bounded to the 32 MB GLTF limit above and WP_Filesystem has no byte-range equivalent.
        $json = file_get_contents($file['tmp_name'], false, null, 0, 32 * 1024 * 1024);
        $result = false === $json
            ? new WP_Error('read_error', __('Unable to read the glTF file.', 'explorexr'))
            : explorexr_free_validate_gltf_json($json);
    } else {
        $result = explorexr_free_validate_usdz($file['tmp_name']);
    }
    if (is_wp_error($result)) {
        return $result;
    }

    $file['name'] = $name;
    $file['size'] = $size;
    return $file;
}

/**
 * Backward-compatible model upload validation entry point.
 *
 * @param array $file Uploaded file data.
 * @return array|WP_Error
 */
function explorexr_validate_model_file_upload($file) {
    return explorexr_sanitize_file_upload($file);
}

/**
 * Handle model file upload
 * 
 * @param array $file The uploaded file data
 * @return array|bool Array of file data on success, false on failure
 */
function explorexr_handle_model_upload($file) {
    $file = explorexr_sanitize_file_upload($file);
    if (is_wp_error($file)) {
        return false;
    }
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Use the WordPress uploads models directory
    $models_dir = EXPLOREXR_MODELS_DIR;
    
    // Create models directory if it doesn't exist
    if (!file_exists($models_dir)) {
        wp_mkdir_p($models_dir);
    }
    
    // Secure the filename
    $filename = sanitize_file_name($file['name']);
    $filename = wp_unique_filename($models_dir, $filename);
    $new_file = $models_dir . $filename;
    
    // Move the file to our models directory using WordPress upload handling
    $upload_result = wp_handle_upload($file, array(
        'test_form' => false,
        'test_type' => false,
        'upload_error_handler' => 'wp_handle_upload_error'
    ));
    
    if (!$upload_result || isset($upload_result['error'])) {
        return false;
    }
    
    // Move to our models directory
    $new_file = $models_dir . $filename;
    if (!copy($upload_result['file'], $new_file)) {
        wp_delete_file($upload_result['file']); // Clean up temp file
        return false;
    }
    
    // Clean up original upload
    wp_delete_file($upload_result['file']);
    
    // Set proper permissions using WP_Filesystem
    if (!function_exists('WP_Filesystem')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    
    WP_Filesystem();
    global $wp_filesystem;
    
    if ($wp_filesystem) {
        $wp_filesystem->chmod($new_file, 0644);
    }
    
    // Return the model data using plugin-defined constants
    $file_url = EXPLOREXR_MODELS_URL . $filename;
    
    return array(
        'file_path' => $new_file,
        'file_url' => $file_url,
        'file_name' => $filename,
        'file_type' => $file_ext
    );
}
