<?php
/**
 * ExploreXR Deactivation Handler
 * 
 * This file handles the deactivation process for the ExpoXR plugin,
 * including showing messages to users about what happens to their data.
 *
 * @package ExpoXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue the deactivation message script
 */
function expoxr_enqueue_deactivation_script($hook) {
    if ($hook !== 'plugins.php') {
        return;
    }
    
    // Make sure jquery is loaded
    wp_enqueue_script('jquery');
    
    wp_enqueue_script(
        'expoxr-deactivation-message',
        EXPOXR_PLUGIN_URL . 'assets/js/deactivation-message.js',
        array('jquery'),
        EXPOXR_VERSION,
        true
    );
    
    // Pass admin URL and other data to the script
    wp_localize_script(
        'expoxr-deactivation-message',
        'expoxrDeactivation',
        array(
            'adminUrl' => admin_url(),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('expoxr_deactivation_nonce'),
            'pluginPath' => 'expoxr/exploreXR.php',
            'isPluginsPage' => true
        )
    );
}
add_action('admin_enqueue_scripts', 'expoxr_enqueue_deactivation_script');

/**
 * Display an admin notice after plugin activation with info about uninstall settings
 */
function expoxr_activation_admin_notice() {
    if (get_transient('expoxr_just_activated')) {
        ?>
        <div class="notice notice-info is-dismissible">
            <p><?php esc_html_e('Thank you for activating ExploreXR! By default, your data will be preserved even if you deactivate or uninstall the plugin.', 'explorexr'); ?></p>
            <p><?php esc_html_e('If you wish to change this behavior, please visit the', 'explorexr'); ?> <a href="<?php echo esc_url(admin_url('admin.php?page=expoxr-settings')); ?>"><?php esc_html_e('Settings page', 'explorexr'); ?></a> <?php esc_html_e('and configure the uninstall options.', 'explorexr'); ?></p>
        </div>
        <?php
        delete_transient('expoxr_just_activated');
    }
}
add_action('admin_notices', 'expoxr_activation_admin_notice');

/**
 * Set transient on plugin activation
 */
function expoxr_plugin_activation() {
    set_transient('expoxr_just_activated', true, 60);
}
register_activation_hook(EXPOXR_PLUGIN_DIR . 'exploreXR.php', 'expoxr_plugin_activation');

/**
 * Display a pre-uninstall admin notice when deactivating the plugin
 */
function expoxr_deactivation_admin_notice() {
    global $pagenow;
    
    if ($pagenow === 'plugins.php') {
        // Simple deactivation notice for free version
        ?>
        <div class="notice notice-info" id="expoxr-deactivation-notice" style="display: none;">
            <h3><?php esc_html_e('ExploreXR Deactivation', 'explorexr'); ?></h3>
            <p><?php esc_html_e('Thank you for using ExploreXR! Your 3D models and settings will be preserved.', 'explorexr'); ?></p>
            <p>
                <button class="button" id="expoxr-dismiss-deactivation-notice"><?php esc_html_e('Dismiss', 'explorexr'); ?></button>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'expoxr_deactivation_admin_notice');

/**
 * Add JavaScript for simple deactivation notice
 */
function expoxr_deactivation_notice_script() {
    global $pagenow;
    
    if ($pagenow === 'plugins.php') {
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Show notice when hovering over the ExploreXR deactivate link
            $('tr[data-plugin="expoxr/expoxr.php"] .deactivate a').hover(function() {
                $('#expoxr-deactivation-notice').show();
            });
            
            // Dismiss notice
            $('#expoxr-dismiss-deactivation-notice').on('click', function() {
                $('#expoxr-deactivation-notice').hide();
            });
        });
        </script>
        <?php
    }
}
add_action('admin_footer', 'expoxr_deactivation_notice_script');





