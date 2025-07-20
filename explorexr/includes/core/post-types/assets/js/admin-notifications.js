/**
 * ExploreXR Admin Notifications
 * Adds save confirmation messages for 3D model updates
 */
jQuery(document).ready(function($) {
    // Check if we're on the model edit page
    if ($('body').hasClass('post-type-explorexr_model') && 
        ($('body').hasClass('post-php') || $('body').hasClass('post-new-php'))) {
        
        // Look for WordPress update message
        const $message = $('#message');
        if ($message.length && $message.hasClass('updated')) {
            // Use the global notification system if available
            if (typeof window.explorexrCreateNotification === 'function') {
                window.explorexrCreateNotification(
                    '<strong>3D Model updated successfully!</strong> All changes have been saved.',
                    'save',
                    true
                );
            } else {
                // Fallback to original method
                const $customMessage = $('<div class="notice notice-success explorexr-save-notice is-dismissible">' +
                    '<p><strong>3D Model updated successfully!</strong> All changes have been saved.</p>' +
                    '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>' +
                    '</div>');
                
                // Add the message to the page
                $('#wpbody-content').prepend($customMessage);
                
                // Automatically dismiss after 5 seconds
                setTimeout(function() {
                    $customMessage.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 5000);
                
                // Handle manual dismiss
                $customMessage.find('.notice-dismiss').on('click', function() {
                    $customMessage.fadeOut(300, function() {
                        $(this).remove();
                    });
                });
            }
            
            // Add visual highlight to the publish button to confirm save
            $('#publish').addClass('button-primary-disabled');
            setTimeout(function() {
                $('#publish').removeClass('button-primary-disabled');
            }, 1000);
        }
    }
});