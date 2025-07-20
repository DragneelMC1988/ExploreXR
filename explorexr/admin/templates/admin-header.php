<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Header -->
<div class="explorexr-admin-header">
    <div class="explorexr-logo">
        <h1><?php echo esc_html($page_title); ?> <span class="explorexr-version"><?php echo esc_html(EXPLOREXR_VERSION); ?></span></h1>
    </div>
    <div class="explorexr-header-actions">
        <?php if (isset($header_actions)) : ?>
            <?php echo wp_kses_post($header_actions); ?>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="explorexr-quick-actions">
    <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr')); ?>">
        <span class="dashicons dashicons-dashboard"></span> Dashboard
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-create-model')); ?>">
        <span class="dashicons dashicons-plus-alt"></span> Create New Model
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-browse-models')); ?>">
        <span class="dashicons dashicons-format-gallery"></span> Browse Models
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-files')); ?>">
        <span class="dashicons dashicons-media-default"></span> Manage Files
    </a>    
    <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-loading-options')); ?>">
        <span class="dashicons dashicons-performance"></span> Loading Options
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-settings')); ?>">
        <span class="dashicons dashicons-admin-settings"></span> Settings
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-premium')); ?>" class="explorexr-premium-action">
        <span class="dashicons dashicons-star-filled"></span> Go Premium
    </a>
</div>





