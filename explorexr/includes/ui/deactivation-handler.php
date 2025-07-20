<?php
/**
 * ExploreXR Deactivation Handler
 * 
 * This file handles the deactivation process for the ExploreXR plugin,
 * including showing messages to users about what happens to their data.
 *
  * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue the deactivation message script
 */
function explorexr_enqueue_deactivation_script($hook) {
    if ($hook !== 'plugins.php') {
        return;
    }
    
    // Make sure jquery is loaded
    wp_enqueue_script('jquery');
    
    wp_enqueue_script(
        'explorexr-deactivation-message',
        EXPLOREXR_PLUGIN_URL . 'assets/js/deactivation-message.js',
        array('jquery'),
        EXPLOREXR_VERSION,
        true
    );
    
    // Pass admin URL and other data to the script
    wp_localize_script(
        'explorexr-deactivation-message',
        'explorexrDeactivation',
        array(
            'adminUrl' => admin_url(),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('explorexr_deactivation_nonce'),
            'pluginPath' => 'explorexr/exploreXR.php',
            'isPluginsPage' => true
        )
    );
}
add_action('admin_enqueue_scripts', 'explorexr_enqueue_deactivation_script');

/**
 * Display an admin notice after plugin activation with info about uninstall settings
 */
function explorexr_activation_admin_notice() {
    if (get_transient('explorexr_just_activated')) {
        ?>
        <div class="notice notice-info is-dismissible">
            <p><?php esc_html_e('Thank you for activating ExploreXR! By default, your data will be preserved even if you deactivate or uninstall the plugin.', 'explorexr'); ?></p>
            <p><?php esc_html_e('If you wish to change this behavior, please visit the', 'explorexr'); ?> <a href="<?php echo esc_url(admin_url('admin.php?page=explorexr-settings')); ?>"><?php esc_html_e('Settings page', 'explorexr'); ?></a> <?php esc_html_e('and configure the uninstall options.', 'explorexr'); ?></p>
        </div>
        <?php
        delete_transient('explorexr_just_activated');
    }
}
add_action('admin_notices', 'explorexr_activation_admin_notice');

/**
 * Set transient on plugin activation
 */
function explorexr_plugin_activation() {
    set_transient('explorexr_just_activated', true, 60);
}
register_activation_hook(EXPLOREXR_PLUGIN_DIR . 'exploreXR.php', 'explorexr_plugin_activation');

/**
 * Display a pre-uninstall admin notice when deactivating the plugin
 */
function explorexr_deactivation_admin_notice() {
    global $pagenow;
    
    if ($pagenow === 'plugins.php') {
        // Simple deactivation notice for free version
        ?>
        <div class="notice notice-info" id="explorexr-deactivation-notice" style="display: none;">
            <h3><?php esc_html_e('ExploreXR Deactivation', 'explorexr'); ?></h3>
            <p><?php esc_html_e('Thank you for using ExploreXR! Your 3D models and settings will be preserved.', 'explorexr'); ?></p>
            <p>
                <button class="button" id="explorexr-dismiss-deactivation-notice"><?php esc_html_e('Dismiss', 'explorexr'); ?></button>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'explorexr_deactivation_admin_notice');

/**
 * Add JavaScript for simple deactivation notice
 */
function explorexr_deactivation_notice_script() {
    global $pagenow;
    
    if ($pagenow === 'plugins.php') {
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Show notice when hovering over the ExploreXR deactivate link
            $('tr[data-plugin="explorexr/explorexr.php"] .deactivate a').hover(function() {
                $('#explorexr-deactivation-notice').show();
            });
            
            // Dismiss notice
            $('#explorexr-dismiss-deactivation-notice').on('click', function() {
                $('#explorexr-deactivation-notice').hide();
            });
        });
        </script>
        <?php
    }
}
add_action('admin_footer', 'explorexr_deactivation_notice_script');





