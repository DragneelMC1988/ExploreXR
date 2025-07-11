<?php
/**
 * Add custom action links to the plugin entry in the plugins list
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add plugin action links to easily access addon management
 */
function expoxr_plugin_action_links($links) {
    // Add link to Addon Management
    $addon_link = '<a href="' . esc_url(admin_url('admin.php?page=expoxr-addons')) . '">Addons</a>';
    
    // Add at the beginning of the array
    array_unshift($links, $addon_link);
    
    return $links;
}
add_filter('plugin_action_links_expoxr/exploreXR.php', 'expoxr_plugin_action_links');





