<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * This file is kept for backward compatibility.
 * The main post type functionality has been moved to includes/post-types/class-post-types.php
 */

// Only register these functions if the class implementation is not present
if (!class_exists('ExpoXR_Post_Types')) {
    // Register a custom post type for 3D models
    add_action('init', function () {
        register_post_type('expoxr_model', [
            'labels' => [
                'name' => '3D Models',
                'singular_name' => '3D Model',
                'add_new' => 'Add New Model',
                'add_new_item' => 'Add New 3D Model',
                'edit_item' => 'Edit 3D Model',
                'new_item' => 'New 3D Model',
                'view_item' => 'View 3D Model',
                'search_items' => 'Search 3D Models',
                'not_found' => 'No 3D Models found',
                'not_found_in_trash' => 'No 3D Models found in Trash',
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title'], // Only support title, remove editor and thumbnail
            'menu_icon' => 'dashicons-format-gallery',
            'show_in_menu' => false, // Hide from admin menu
        ]);
    });

    // Add multipart/form-data enctype to the post form for file uploads
    add_action('post_edit_form_tag', function() {
        echo ' enctype="multipart/form-data"';
    });

    // Add meta boxes for the post type
    add_action('add_meta_boxes', function () {
        // Add meta boxes only if the class-based implementation isn't active
        if (!has_action('add_meta_boxes', array('ExpoXR_Post_Types', 'add_meta_boxes'))) {
            add_meta_box(
                'expoxr_model_file',
                '3D Model File',
                'expoxr_model_file_meta_box',
                'expoxr_model',
                'normal',
                'high'
            );
            
            // Add other meta boxes as needed
            // ...
        }
    });
}





