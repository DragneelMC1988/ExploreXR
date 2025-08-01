/**
 * ExploreXR Admin Notifications
 * Adds save confirmation messages for 3D model updates - WordPress Compliant Version
 */
jQuery(document).ready(function($) {
    // Check if we're on the model edit page
    if ($('body').hasClass('post-type-explorexr_model') && 
        ($('body').hasClass('post-php') || $('body').hasClass('post-new-php'))) {
        
        // Look for WordPress update message
        const $message = $('#message');
        if ($message.length && $message.hasClass('updated')) {
            // WordPress.org Compliance: Do not manipulate WordPress notice positioning
            // Instead, enhance the existing WordPress notice with our content
            
            // Find the existing WordPress message paragraph
            const $existingP = $message.find('p').first();
            if ($existingP.length) {
                // Enhance the existing message instead of creating new notice containers
                $existingP.html('<strong>3D Model updated successfully!</strong> All changes have been saved.');
            }
            
            // Add visual highlight to the publish button to confirm save
            $('#publish').addClass('button-primary-disabled');
            setTimeout(function() {
                $('#publish').removeClass('button-primary-disabled');
            }, 1000);
        }
    }
});