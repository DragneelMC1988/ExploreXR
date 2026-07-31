<?php
/**
 * ExploreXR Free uninstall handler.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_transient('explorexr_model_cache');
delete_transient('explorexr_admin_stats');
delete_transient('explorexr_file_validation');
delete_option('explorexr_admin_notice');

if (!get_option('explorexr_remove_data_on_uninstall', false)) {
    return;
}

/**
 * Delete one owned upload directory without following external paths.
 *
 * @param string $directory Directory path.
 */
function explorexr_free_delete_owned_directory($directory) {
    $upload_dir = wp_upload_dir();
    $upload_root = realpath($upload_dir['basedir']);
    $directory_root = realpath($directory);
    if (!$upload_root
        || !$directory_root
        || 0 !== strpos(trailingslashit($directory_root), trailingslashit($upload_root))
        || !in_array(basename($directory_root), array('explorexr-models', 'explorexr_models'), true)) {
        return;
    }

    if (!function_exists('WP_Filesystem')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    global $wp_filesystem;
    if (!WP_Filesystem() || !$wp_filesystem) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory_root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        if ($item->isLink() || $item->isFile()) {
            wp_delete_file($item->getPathname());
        } elseif ($item->isDir()) {
            $wp_filesystem->rmdir($item->getPathname());
        }
    }
    $wp_filesystem->rmdir($directory_root);
}

global $wpdb;
do {
    $explorexr_deleted_models = 0;
    // The CPT is not registered while uninstall.php runs, so query owned rows directly.
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Trusted WordPress table; bounded uninstall batch.
    $explorexr_model_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s ORDER BY ID ASC LIMIT 100",
            'explorexr_model'
        )
    );
    foreach ($explorexr_model_ids as $explorexr_model_id) {
        if (wp_delete_post((int) $explorexr_model_id, true)) {
            $explorexr_deleted_models++;
        }
    }
} while (!empty($explorexr_model_ids) && $explorexr_deleted_models > 0);

$explorexr_option_pattern = $wpdb->esc_like('explorexr_') . '%';
$explorexr_meta_pattern = $wpdb->esc_like('_explorexr_') . '%';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Trusted WordPress table names; bulk owned-data uninstall.
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $explorexr_option_pattern));
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Trusted WordPress table names; bulk owned-data uninstall.
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", $explorexr_meta_pattern));

$explorexr_upload_dir = wp_upload_dir();
explorexr_free_delete_owned_directory(trailingslashit($explorexr_upload_dir['basedir']) . 'explorexr-models');
explorexr_free_delete_owned_directory(trailingslashit($explorexr_upload_dir['basedir']) . 'explorexr_models');
