<?php
/**
 * ExploreXR Admin Footer Template
 * 
 * Displays the ExploreXR branding footer on all admin pages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="explorexr-admin-footer">
    <div class="explorexr-footer-content">
        <div class="explorexr-footer-branding">
            <?php 
            // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Plugin logo for admin footer
            printf('<img src="%s" alt="%s" class="explorexr-footer-logo" loading="lazy">', 
                esc_url(EXPLOREXR_PLUGIN_URL . 'assets/img/logos/explorexr-Logo-dark.png'), 
                                esc_attr__('ExploreXR Logo', 'explorexr')
            );
            ?>
            <p class="explorexr-footer-text">
                <?php esc_html_e('ExploreXR is part of the', 'explorexr'); ?> <strong><?php esc_html_e('ExpoXR Family', 'explorexr'); ?></strong> - 
                <?php esc_html_e('XR solutions for the modern web', 'explorexr'); ?>
            </p>
        </div>
        <div class="explorexr-footer-links">
            <a href="https://expoxr.com" target="_blank">Visit expoxr.com</a>
            <a href="https://expoxr.com/explorexr/documentation" target="_blank">Documentation</a>
            <a href="https://expoxr.com/explorexr/support" target="_blank">Support</a>
        </div>
    </div>
</div>





