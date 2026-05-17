<?php
/**
 * Strip Tags Fix
 * 
 * This file adds a filter to prevent null values from being passed to strip_tags() function in WordPress admin.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Filter admin title to ensure it's never null
 */
function explorexr_filter_admin_title($admin_title, $title) {
    // Make sure neither value is null
    if ($admin_title === null) {
        $admin_title = '';
    }
    
    if ($title === null) {
        $title = '';
    }
    
    return $admin_title;
}
add_filter('admin_title', 'explorexr_filter_admin_title', 5, 2);

/**
 * Filter page title in admin to ensure it's never null
 */
function explorexr_ensure_page_title($title) {
    if ($title === null) {
        return '';
    }
    return $title;
}
add_filter('the_title', 'explorexr_ensure_page_title', 5);
add_filter('document_title_parts', function($title_parts) {
    foreach ($title_parts as $key => $part) {
        if ($part === null) {
            $title_parts[$key] = '';
        }
    }
    return $title_parts;
}, 5);

/**
 * Alternative fix for PHP 8.1+ deprecation warning by filtering pre_strip_tags
 */
function explorexr_pre_strip_tags($string) {
    if ($string === null) {
        return '';
    }
    return $string;
}
add_filter('pre_strip_tags', 'explorexr_pre_strip_tags', 5);





