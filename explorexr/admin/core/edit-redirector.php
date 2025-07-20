<?php
/**
 * Redirector for Edit Links
 * Redirects standard WordPress edit URLs to our custom edit page
 * 
  * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Filter to modify the edit link for 3D models
 * 
 * @param string $url The original edit URL
 * @param int $post_id The post ID
 * @param string $context The context for the edit link
 * @return string The modified URL
 */
function explorexr_custom_edit_link($url, $post_id, $context) {
    // Ensure we have valid post ID
    if (empty($post_id)) {
        return $url;
    }
    
    // Check if this is a 3D model post type and if the custom edit page function exists
    $post_type = get_post_type($post_id);
    if ($post_type === 'explorexr_model' && function_exists('explorexr_edit_model_page')) {
        // Create our custom edit URL
        $custom_url = admin_url('admin.php?page=explorexr-edit-model&model_id=' . $post_id);
        
        // Return the custom URL
        return $custom_url;
    }
    
    // Return the original URL for all other post types or if custom edit page doesn't exist
    return $url;
}
add_filter('get_edit_post_link', 'explorexr_custom_edit_link', 10, 3);

/**
 * Check for standard WordPress edit.php access and redirect if needed
 */
function explorexr_redirect_standard_edit_page() {
    global $pagenow, $typenow;
    
    // Check if we're on the post edit page and the post ID is set
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for redirection purposes only
    if (is_admin() && ($pagenow === 'post.php' || $pagenow === 'post-new.php') && isset($_GET['post'])) {
        
        // Get the post ID and validate it
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used for redirection purposes only
        $post_id = intval(sanitize_text_field(wp_unslash($_GET['post'])));
        $post_type = get_post_type($post_id);
        
        // Check if this is a 3D model post type and if the custom edit page function exists
        if ($post_type === 'explorexr_model' && function_exists('explorexr_edit_model_page')) {
            // Create our custom edit URL
            $redirect_url = admin_url('admin.php?page=explorexr-edit-model&model_id=' . $post_id);
            
            // Make sure no output has been sent to the browser yet
            if (!headers_sent()) {
                // Redirect to our custom edit page
                wp_redirect($redirect_url);
                exit;
            }
        }
    }
}
add_action('init', 'explorexr_redirect_standard_edit_page');

/**
 * Add a custom row action for edit links in the post list table
 */
function explorexr_custom_row_actions($actions, $post) {
    // Make sure $post is a valid object and has a post_type property
    if (!is_object($post) || !isset($post->post_type) || !isset($post->ID)) {
        return $actions;
    }
    
    // Check if this is a 3D model post type and if the custom edit page function exists
    if ($post->post_type === 'explorexr_model' && function_exists('explorexr_edit_model_page')) {
        // Create our custom edit URL
        $custom_url = admin_url('admin.php?page=explorexr-edit-model&model_id=' . $post->ID);
        
        // Replace the edit action with our custom URL
        if (isset($actions['edit'])) {
            $actions['edit'] = '<a href="' . esc_url($custom_url) . '">' . __('Edit', 'explorexr') . '</a>';
        }
    }
    
    return $actions;
}
add_filter('post_row_actions', 'explorexr_custom_row_actions', 10, 2);





