<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Header -->
<div class="expoxr-admin-header">
    <div class="expoxr-logo">
        <h1><?php echo esc_html($page_title); ?> <span class="expoxr-version"><?php echo esc_html(EXPOXR_VERSION); ?></span></h1>
    </div>
    <div class="expoxr-header-actions">
        <?php if (isset($header_actions)) : ?>
            <?php echo wp_kses_post($header_actions); ?>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="expoxr-quick-actions">
    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr')); ?>">
        <span class="dashicons dashicons-dashboard"></span> Dashboard
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-create-model')); ?>">
        <span class="dashicons dashicons-plus-alt"></span> Create New Model
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-browse-models')); ?>">
        <span class="dashicons dashicons-format-gallery"></span> Browse Models
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-files')); ?>">
        <span class="dashicons dashicons-media-default"></span> Manage Files
    </a>    
    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-loading-options')); ?>">
        <span class="dashicons dashicons-performance"></span> Loading Options
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-settings')); ?>">
        <span class="dashicons dashicons-admin-settings"></span> Settings
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-premium')); ?>" class="expoxr-premium-action">
        <span class="dashicons dashicons-star-filled"></span> Go Premium
    </a>
</div>





