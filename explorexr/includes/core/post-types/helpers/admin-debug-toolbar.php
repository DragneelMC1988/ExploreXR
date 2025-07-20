<?php
/**
 * Admin Toolbar Debug Functions for ExploreXR
 *
 * Adds a debug option to the WordPress admin toolbar to quickly check model submission data
 * for the model currently being edited.
 *
  * @package ExploreXR
 */// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add debug menu to admin toolbar when in ExploreXR model edit screen
 */
function explorexr_add_debug_toolbar_item($admin_bar) {
    global $pagenow, $post;
    
    // Only show on post edit screens for explorexr_model post type
    if (($pagenow == 'post.php' || $pagenow == 'post-new.php') && 
        isset($post) && $post->post_type == 'explorexr_model') {
        
        // Main debug menu
        $admin_bar->add_node(array(
            'id'    => 'explorexr-debug',
            'title' => 'ExploreXR Debug',
            'href'  => '#',
        ));
        
        // Add model info submenu
        $admin_bar->add_node(array(
            'id'     => 'explorexr-debug-model-info',
            'parent' => 'explorexr-debug',
            'title'  => 'Model Info',
            'href'   => '#',
            'meta'   => array(
                'onclick' => 'explorexrShowModelInfo(); return false;',
            ),
        ));
        
        // Add form data submenu
        $admin_bar->add_node(array(
            'id'     => 'explorexr-debug-form-data',
            'parent' => 'explorexr-debug',
            'title'  => 'Last Form Data',
            'href'   => '#',
            'meta'   => array(
                'onclick' => 'explorexrShowFormData(); return false;',
            ),
        ));
        
        // Add checkbox state submenu
        $admin_bar->add_node(array(
            'id'     => 'explorexr-debug-checkbox-state',
            'parent' => 'explorexr-debug',
            'title'  => 'Checkbox States',
            'href'   => '#',
            'meta'   => array(
                'onclick' => 'explorexrShowCheckboxStates(); return false;',
            ),
        ));
        
        // Run form troubleshooter
        $admin_bar->add_node(array(
            'id'     => 'explorexr-debug-troubleshoot',
            'parent' => 'explorexr-debug',
            'title'  => 'Troubleshoot Form',
            'href'   => '#',
            'meta'   => array(
                'onclick' => 'troubleshootExploreXREditMode(); return false;',
            ),
        ));
    }
}
add_action('admin_bar_menu', 'explorexr_add_debug_toolbar_item', 100);

/**
 * Add debugging JavaScript to admin footer
 */
function explorexr_add_debug_scripts() {
    global $pagenow, $post;
    
    // Only show on post edit screens for explorexr_model post type
    if (($pagenow == 'post.php' || $pagenow == 'post-new.php') && 
        isset($post) && $post->post_type == 'explorexr_model') {
        
        // Get debug data from meta
        $model_info = array(
            'post_id' => $post->ID,
            'model_file' => get_post_meta($post->ID, '_explorexr_model_file', true),
            'model_name' => get_post_meta($post->ID, '_explorexr_model_name', true),
            'camera_controls' => get_post_meta($post->ID, '_explorexr_camera_controls', true),
            'animation_enabled' => get_post_meta($post->ID, '_explorexr_animation_enabled', true),
            'auto_rotate' => get_post_meta($post->ID, '_explorexr_auto_rotate', true)
        );
        
        // Get last edit debug data
        $last_edit_debug = get_post_meta($post->ID, '_explorexr_last_edit_debug', true);
        $last_edit_time = get_post_meta($post->ID, '_explorexr_last_edit_time', true);
        
        // Get checkbox debug data
        $checkbox_debug = get_post_meta($post->ID, '_explorexr_checkbox_debug', true);
        
        // Add JavaScript to display the data
        ?>
        <script>
            // Function to show model info
            function explorexrShowModelInfo() {
                console.group('ExploreXR Model Info');
                console.log('Model ID: <?php echo esc_js($post->ID); ?>');
                console.log('Model Name: <?php echo esc_js($model_info['model_name']); ?>');
                console.log('Model File: <?php echo esc_js($model_info['model_file']); ?>');
                console.log('Camera Controls: <?php echo esc_js($model_info['camera_controls']); ?>');
                console.log('Animation Enabled: <?php echo esc_js($model_info['animation_enabled']); ?>');
                console.log('Auto Rotate: <?php echo esc_js($model_info['auto_rotate']); ?>');
                console.log('AR Enabled: <?php echo esc_js($model_info['ar_enabled']); ?>');
                console.groupEnd();
                
                // Also show an alert for users who don't have console open
                alert('ExploreXR Model Info:\n\n' + 
                      'Model ID: <?php echo esc_js($post->ID); ?>\n' +
                      'Model Name: <?php echo esc_js($model_info['model_name']); ?>\n' +
                      'Model File: <?php echo esc_js($model_info['model_file']); ?>\n' +
                      'Camera Controls: <?php echo esc_js($model_info['camera_controls']); ?>\n' +
                      'Animation Enabled: <?php echo esc_js($model_info['animation_enabled']); ?>\n' +
                      'Auto Rotate: <?php echo esc_js($model_info['auto_rotate']); ?>\n' +
                      'AR Enabled: <?php echo esc_js($model_info['ar_enabled']); ?>');
            }
            
            // Function to show last form data
            function explorexrShowFormData() {
                <?php if (!empty($last_edit_debug)) : ?>
                    console.group('ExploreXR Last Form Data (<?php echo esc_js($last_edit_time); ?>)');
                    console.log(<?php echo wp_json_encode(json_decode($last_edit_debug, true)); ?>);
                    console.groupEnd();
                    
                    // Show a simple alert with some basic info
                    alert('ExploreXR Last Form Data:\n\n' +
                          'Last Edit Time: <?php echo esc_js($last_edit_time); ?>\n\n' +
                          'See browser console for complete form data');
                <?php else : ?>
                    console.log('No form data available. Try saving the form first.');
                    alert('No form data available. Try saving the form first.');
                <?php endif; ?>
            }
            
            // Function to show checkbox states
            function explorexrShowCheckboxStates() {
                <?php if (!empty($checkbox_debug)) : ?>
                    console.group('ExploreXR Checkbox States');
                    console.log(<?php echo wp_json_encode(json_decode($checkbox_debug, true)); ?>);
                    console.groupEnd();
                    
                    // Show a simple alert
                    alert('ExploreXR Checkbox States:\n\n' +
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
add_action('admin_footer', 'explorexr_add_debug_scripts');





