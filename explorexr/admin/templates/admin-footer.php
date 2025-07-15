<?php
/**
 * ExploreXR Admin Footer Template
 * 
 * Displays the ExpoXR branding footer on all admin pages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="expoxr-admin-footer">
    <div class="expoxr-footer-content">
        <div class="expoxr-footer-branding">
            <?php 
            // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Plugin logo for admin footer
            printf('<img src="%s" alt="%s" class="expoxr-footer-logo" loading="lazy">', 
                esc_url(EXPOXR_PLUGIN_URL . 'assets/img/logos/ExpoXR-Logo.png'), 
                esc_attr__('ExpoXR Logo', 'explorexr')
            );
            ?>
            <p class="expoxr-footer-text">
                <?php esc_html_e('ExploreXR is part of the', 'explorexr'); ?> <strong><?php esc_html_e('ExpoXR Family', 'explorexr'); ?></strong> - 
                <?php esc_html_e('XR solutions for the modern web', 'explorexr'); ?>
            </p>
        </div>
        <div class="expoxr-footer-links">
            <a href="https://expoxr.com" target="_blank">Visit ExpoXR.com</a>
            <a href="https://expoxr.com/explorexr/documentation" target="_blank">Documentation</a>
            <a href="https://expoxr.com/explorexr/support" target="_blank">Support</a>
        </div>
    </div>
</div>





