<?php
/**
 * ExploreXR Admin Notifications Area Template
 * 
 * This template provides a container for WordPress admin notices and plugin-specific notifications
 * that should appear above the ExploreXR admin header.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- WordPress Notifications Area -->
<div id="explorexr-notifications-area" class="explorexr-notifications-wrapper">
    <?php
    // Display any WordPress admin notices that should appear above the ExploreXR header
    // This area will be populated by WordPress notices and ExploreXR notifications
    
    // Check for plugin-specific messages
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display of URL parameters
    if (isset($_GET['settings-updated']) && sanitize_text_field(wp_unslash($_GET['settings-updated'])) == 'true') {
        echo '<div class="notice notice-success is-dismissible">
                <p><strong>Settings saved successfully!</strong></p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
              </div>';
    }
    
    // Check for any error messages
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display of URL parameters
    if (isset($_GET['error']) && !empty($_GET['error'])) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display of URL parameters
        $error_message = sanitize_text_field(wp_unslash($_GET['error']));
        echo '<div class="notice notice-error is-dismissible">
                <p><strong>Error:</strong> ' . esc_html($error_message) . '</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
              </div>';
    }
    
    // Check for success messages
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display of URL parameters
    if (isset($_GET['success']) && !empty($_GET['success'])) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display of URL parameters
        $success_message = sanitize_text_field(wp_unslash($_GET['success']));
        echo '<div class="notice notice-success is-dismissible">
                <p><strong>Success:</strong> ' . esc_html($success_message) . '</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
              </div>';
    }
    ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Handle dismiss functionality for notices in our notifications area
    $(document).on('click', '#explorexr-notifications-area .notice-dismiss', function(e) {
        e.preventDefault();
        $(this).closest('.notice').fadeOut(300, function() {
            $(this).remove();
        });
    });
    
    // Auto-dismiss notifications after a set time (optional)
    setTimeout(function() {
        $('#explorexr-notifications-area .notice.is-dismissible').each(function() {
            var $notice = $(this);
            // Only auto-dismiss if it's not an error notice
            if (!$notice.hasClass('notice-error')) {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
    }, 8000); // 8 seconds
});
</script>





