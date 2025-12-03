<?php
/**
 * Post Types Registration Class
 *
 * Handles the registration of custom post types and loads related metaboxes
 *
  * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class ExploreXR_Post_Types {

    /**
     * Constructor
     */
    public function __construct() {
        // Register post types
        add_action('init', array($this, 'register_post_types'));
        
        // Add form enctype for file uploads
        add_action('post_edit_form_tag', array($this, 'add_form_enctype'));
        
        // Add meta boxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        
        // Handle saving post meta
        add_action('save_post', array($this, 'save_post_meta'));
        
        // Customize admin UI for post type
        add_action('admin_head', array($this, 'customize_admin_ui'));
        
        // Enqueue required scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Register custom post types
     */
    public function register_post_types() {
        register_post_type('explorexr_model', [
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
    }

    /**
     * Add multipart/form-data enctype to the post form for file uploads
     */
    public function add_form_enctype() {
        echo ' enctype="multipart/form-data"';
    }

    /**
     * Add meta boxes to the post edit screen
     */
    public function add_meta_boxes() {
        // Include metabox files
        $this->include_metabox_files();
        
        // Model file upload metabox
        add_meta_box(
            'explorexr_model_file',
            '3D Model File',
            'explorexr_model_file_meta_box',
            'explorexr_model',
            'normal',
            'high'
        );
        
        // Model viewer size settings metabox
        add_meta_box(
            'explorexr_model_size',
            'Model Viewer Size',
            'explorexr_edit_model_size_box',
            'explorexr_model',
            'normal',
            'default'
        );
        
        // Camera controls are handled in the edit-model-page.php through cards system
        
        // Animation settings are not available in the Free version
        // This feature is available in the Pro version only
    }
    /**
     * Include all metabox files
     */
    private function include_metabox_files() {
        $metabox_dir = plugin_dir_path(__FILE__) . 'metaboxes/';
        
        // Include core metabox files
        require_once $metabox_dir . 'model-file.php';
        require_once $metabox_dir . 'model-size.php';
        // Animation features not available in Free version
        
        // Include helper files
        require_once plugin_dir_path(__FILE__) . 'helpers/meta-handlers.php';
        require_once plugin_dir_path(__FILE__) . 'helpers/sanitization.php';
    }
      /**
     * Save post meta when the post is saved
     *
     * @param int $post_id The ID of the post being saved
     */
    public function save_post_meta($post_id) {
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check post type
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled in explorexr_save_all_post_meta()
        if (!isset($_POST['post_type']) || 'explorexr_model' != sanitize_text_field(wp_unslash($_POST['post_type']))) {
            return;
        }
        
        // Make sure we're not in a post revision
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        // The main save_post_meta function is in meta-handlers.php
        // and will be called from there after these basic checks
        if (function_exists('explorexr_save_all_post_meta')) {
            $result = explorexr_save_all_post_meta($post_id);
        } else {
        }
    }

    /**
     * Customize the admin UI for our post type
     */
    public function customize_admin_ui() {
        global $post, $pagenow;
        
        // Only apply to our custom post type edit screens
        if (($pagenow == 'post.php' || $pagenow == 'post-new.php') && 
            isset($post) && $post->post_type == 'explorexr_model') {
            
            // WordPress.org compliance: Use wp_add_inline_style instead of echo
            $admin_styles = '
                /* Hide unnecessary UI elements */
                #post-body-content {
                    margin-bottom: 20px;
                }
                #titlediv {
                    margin-bottom: 20px;
                }
                /* Custom styling for our metaboxes */
                .post-type-explorexr_model .postbox {
                    margin-bottom: 20px;
                }
            ';
            wp_add_inline_style('wp-admin', $admin_styles);
            
            // WordPress.org compliance: Use wp_add_inline_script instead of echo
            $admin_script = '
                jQuery(document).ready(function($) {
                    // Any custom UI enhancements can go here
                });
            ';
            wp_add_inline_script('jquery', $admin_script);
        }
    }

    /**
     * Enqueue scripts and styles for the admin
     *
     * @param string $hook Current admin page
     */
    public function enqueue_scripts($hook) {
        global $post;
        
        // Only load on post edit screens for our custom post type
        if (($hook == 'post.php' || $hook == 'post-new.php') && 
            isset($post) && $post->post_type == 'explorexr_model') {
              // First load the guard script
            wp_enqueue_script('explorexr-model-viewer-guard');
            
            // Use the centralized model-viewer script with guard as dependency
            wp_enqueue_script('explorexr-model-viewer');
            
            // Enqueue custom styles and scripts
            wp_enqueue_style('explorexr-model-viewer', EXPLOREXR_PLUGIN_URL . 'assets/css/model-viewer.css', array(), EXPLOREXR_VERSION);
            wp_enqueue_script('explorexr-model-viewer-wrapper', EXPLOREXR_PLUGIN_URL . 'assets/js/model-viewer-wrapper.js', array('jquery', 'explorexr-model-viewer'), EXPLOREXR_VERSION, true);
              // IMPORTANT: Enqueue edit mode fix script FIRST to establish field tracking framework
            wp_enqueue_script('explorexr-edit-mode-fix', EXPLOREXR_PLUGIN_URL . 'includes/post-types/assets/js/edit-mode-fix.js', array('jquery'), '1.0.4', true);
  
            
            // Now enqueue model uploader script (after the fix script)
            wp_enqueue_script('explorexr-model-uploader', EXPLOREXR_PLUGIN_URL . 'includes/post-types/assets/js/model-uploader.js', array('jquery', 'explorexr-edit-mode-fix'), '1.0.4', true);
            
            // Enqueue admin notifications script for save confirmations
            wp_enqueue_script('explorexr-admin-notifications', EXPLOREXR_PLUGIN_URL . 'includes/post-types/assets/js/admin-notifications.js', array('jquery'), '1.0.0', true);
            
            // Enqueue save notification styles
            wp_enqueue_style('explorexr-save-notification', EXPLOREXR_PLUGIN_URL . 'includes/post-types/assets/css/save-notification.css', array(), '1.0.0');
              // Enqueue custom metabox styles
            wp_enqueue_style('explorexr-metabox-styles', EXPLOREXR_PLUGIN_URL . 'includes/post-types/assets/css/metabox-styles.css', array(), EXPLOREXR_VERSION);
            
            // Enqueue core metabox scripts AFTER the model uploader and fix script
            wp_enqueue_script('explorexr-model-file-handler', EXPLOREXR_PLUGIN_URL . 'includes/post-types/assets/js/model-file-handler.js', array('jquery', 'explorexr-model-uploader', 'explorexr-edit-mode-fix'), '1.0.1', true);
        }
    }
}

// Initialize the post types class
new ExploreXR_Post_Types();






