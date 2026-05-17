/**
 * ExploreXR AR Integration JavaScript
 * 
 * Handles AR addon notice dismissal functionality and console warnings
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Handle AR notice dismissal
    $(document).on('click', '.explorexr-dismiss-ar-notice', function(e) {
        e.preventDefault();
        
        const $notice = $(this).closest('.notice');
        
        // Show loading state
        $(this).prop('disabled', true).text(explorexrARIntegration.strings.dismissing);
        
        $.ajax({
            url: explorexrARIntegration.ajaxUrl,
            type: 'POST',
            data: {
                action: 'explorexr_dismiss_ar_notice',
                nonce: explorexrARIntegration.nonce
            },
            success: function(response) {
                $notice.slideUp(300, function() {
                    $(this).remove();
                });
            },
            error: function() {
                // Reset button on error
                $(this).prop('disabled', false).text(explorexrARIntegration.strings.dismissNotice);
                alert(explorexrARIntegration.strings.dismissError);
            }
        });
    });
    
    // Console warnings removed to reduce noise
    // Users should check the add-ons page for available add-ons
});
