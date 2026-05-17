<?php
/**
 * Add Admin Bar Menu Item for Addon Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add a menu item to the admin bar for quick access to addon management
 */
function explorexr_admin_bar_addons_link($admin_bar) {
    // Only show for users who can manage options
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Add main ExploreXR node if it doesn't exist
    if (!$admin_bar->get_node('explorexr')) {
        $admin_bar->add_node(array(
            'id'    => 'explorexr',
            'title' => '<span class="ab-icon dashicons dashicons-admin-customizer"></span><span class="ab-label">ExploreXR</span>',
            'href'  => admin_url('admin.php?page=ExploreXR'),
            'meta'  => array(
                'title' => 'explorexr',
                'class' => 'explorexr-admin-bar-item'
            )
        ));
    }
    
    // Add Addon Manager submenu
    $admin_bar->add_node(array(
        'id'     => 'explorexr-addons',
        'parent' => 'explorexr',
        'title'  => 'Addon Manager',
        'href'   => admin_url('admin.php?page=explorexr-addons'),
        'meta'   => array(
            'title' => 'Manage ExploreXR Addons',
            'class' => 'explorexr-admin-bar-addon-item'
        )
    ));
}
add_action('admin_bar_menu', 'explorexr_admin_bar_addons_link', 100);
