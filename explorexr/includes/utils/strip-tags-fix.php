<?php
/**
 * PHP 8.1+ Null Safety Fix
 *
 * Prevents null values from being passed to string functions
 * in WordPress admin title handling.
 *
 * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Filter admin title to ensure it's never null (PHP 8.1+ compatibility)
 */
function explorexr_filter_admin_title($admin_title, $title) {
    if ($admin_title === null) {
        $admin_title = '';
    }
    return $admin_title;
}
add_filter('admin_title', 'explorexr_filter_admin_title', 5, 2);

/**
 * Filter document title parts to ensure none are null (PHP 8.1+ compatibility)
 */
add_filter('document_title_parts', function ($title_parts) {
    foreach ($title_parts as $key => $part) {
        if ($part === null) {
            $title_parts[$key] = '';
        }
    }
    return $title_parts;
}, 5);
