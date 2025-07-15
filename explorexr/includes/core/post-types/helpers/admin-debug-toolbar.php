<?php
/**
 * Admin Toolbar Debug Functions for ExpoXR
 * 
 * Adds a debug option to the WordPress admin toolbar to quickly check model submission data
 * for the model currently being edited.
 * 
 * @package ExpoXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add debug menu to admin toolbar when in ExpoXR model edit screen
 */
function expoxr_add_debug_toolbar_item($admin_bar) {
    global $pagenow, $post;
    
    // Only show on post edit screens for expoxr_model post type
    if (($pagenow == 'post.php' || $pagenow == 'post-new.php') && 
        isset($post) && $post->post_type == 'expoxr_model') {
        
        // Main debug menu
        $admin_bar->add_node(array(
            'id'    => 'expoxr-debug',
            'title' => 'ExpoXR Debug',
            'href'  => '#',
        ));
        
        // Add model info submenu
        $admin_bar->add_node(array(
            'id'     => 'expoxr-debug-model-info',
            'parent' => 'expoxr-debug',
            'title'  => 'Model Info',
            'href'   => '#',
            'meta'   => array(
                'onclick' => 'expoxrShowModelInfo(); return false;',
            ),
        ));
        
        // Add form data submenu
        $admin_bar->add_node(array(
            'id'     => 'expoxr-debug-form-data',
            'parent' => 'expoxr-debug',
            'title'  => 'Last Form Data',
            'href'   => '#',
            'meta'   => array(
                'onclick' => 'expoxrShowFormData(); return false;',
            ),
        ));
        
        // Add checkbox state submenu
        $admin_bar->add_node(array(
            'id'     => 'expoxr-debug-checkbox-state',
            'parent' => 'expoxr-debug',
            'title'  => 'Checkbox States',
            'href'   => '#',
            'meta'   => array(
                'onclick' => 'expoxrShowCheckboxStates(); return false;',
            ),
        ));
        
        // Run form troubleshooter
        $admin_bar->add_node(array(
            'id'     => 'expoxr-debug-troubleshoot',
            'parent' => 'expoxr-debug',
            'title'  => 'Troubleshoot Form',
            'href'   => '#',
            'meta'   => array(
                'onclick' => 'troubleshootExpoXREditMode(); return false;',
            ),
        ));
    }
}
add_action('admin_bar_menu', 'expoxr_add_debug_toolbar_item', 100);

/**
 * Add debugging JavaScript to admin footer
 */
function expoxr_add_debug_scripts() {
    global $pagenow, $post;
    
    // Only show on post edit screens for expoxr_model post type
    if (($pagenow == 'post.php' || $pagenow == 'post-new.php') && 
        isset($post) && $post->post_type == 'expoxr_model') {
        
        // Get debug data from meta
        $model_info = array(
            'post_id' => $post->ID,
            'model_file' => get_post_meta($post->ID, '_expoxr_model_file', true),
            'model_name' => get_post_meta($post->ID, '_expoxr_model_name', true),
            'camera_controls' => get_post_meta($post->ID, '_expoxr_camera_controls', true),
            'animation_enabled' => get_post_meta($post->ID, '_expoxr_animation_enabled', true),
            'auto_rotate' => get_post_meta($post->ID, '_expoxr_auto_rotate', true)
        );
        
        // Get last edit debug data
        $last_edit_debug = get_post_meta($post->ID, '_expoxr_last_edit_debug', true);
        $last_edit_time = get_post_meta($post->ID, '_expoxr_last_edit_time', true);
        
        // Get checkbox debug data
        $checkbox_debug = get_post_meta($post->ID, '_expoxr_checkbox_debug', true);
        
        // Add JavaScript to display the data
        ?>
        <script>
            // Function to show model info
            function expoxrShowModelInfo() {
                console.group('ExpoXR Model Info');
                console.log('Model ID: <?php echo esc_js($post->ID); ?>');
                console.log('Model Name: <?php echo esc_js($model_info['model_name']); ?>');
                console.log('Model File: <?php echo esc_js($model_info['model_file']); ?>');
                console.log('Camera Controls: <?php echo esc_js($model_info['camera_controls']); ?>');
                console.log('Animation Enabled: <?php echo esc_js($model_info['animation_enabled']); ?>');
                console.log('Auto Rotate: <?php echo esc_js($model_info['auto_rotate']); ?>');
                console.log('AR Enabled: <?php echo esc_js($model_info['ar_enabled']); ?>');
                console.groupEnd();
                
                // Also show an alert for users who don't have console open
                alert('ExpoXR Model Info:\n\n' + 
                      'Model ID: <?php echo esc_js($post->ID); ?>\n' +
                      'Model Name: <?php echo esc_js($model_info['model_name']); ?>\n' +
                      'Model File: <?php echo esc_js($model_info['model_file']); ?>\n' +
                      'Camera Controls: <?php echo esc_js($model_info['camera_controls']); ?>\n' +
                      'Animation Enabled: <?php echo esc_js($model_info['animation_enabled']); ?>\n' +
                      'Auto Rotate: <?php echo esc_js($model_info['auto_rotate']); ?>\n' +
                      'AR Enabled: <?php echo esc_js($model_info['ar_enabled']); ?>');
            }
            
            // Function to show last form data
            function expoxrShowFormData() {
                <?php if (!empty($last_edit_debug)) : ?>
                    console.group('ExpoXR Last Form Data (<?php echo esc_js($last_edit_time); ?>)');
                    console.log(<?php echo wp_json_encode(json_decode($last_edit_debug, true)); ?>);
                    console.groupEnd();
                    
                    // Show a simple alert with some basic info
                    alert('ExpoXR Last Form Data:\n\n' +
                          'Last Edit Time: <?php echo esc_js($last_edit_time); ?>\n\n' +
                          'See browser console for complete form data');
                <?php else : ?>
                    console.log('No form data available. Try saving the form first.');
                    alert('No form data available. Try saving the form first.');
                <?php endif; ?>
            }
            
            // Function to show checkbox states
            function expoxrShowCheckboxStates() {
                <?php if (!empty($checkbox_debug)) : ?>
                    console.group('ExpoXR Checkbox States');
                    console.log(<?php echo wp_json_encode(json_decode($checkbox_debug, true)); ?>);
                    console.groupEnd();
                    
                    // Show a simple alert
                    alert('ExpoXR Checkbox States:\n\n' +
                          'See browser console for complete checkbox debug data');
                <?php else : ?>
                    console.log('No checkbox debug data available. Try saving the form first.');
                    alert('No checkbox debug data available. Try saving the form first.');
                <?php endif; ?>
            }
        </script>
        <?php
    }
}
add_action('admin_footer', 'expoxr_add_debug_scripts');





