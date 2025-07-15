/**
 * ExpoXR AR Integration JavaScript
 * 
 * Handles AR addon notice dismissal functionality and console warnings
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Handle AR notice dismissal
    $(document).on('click', '.expoxr-dismiss-ar-notice', function(e) {
        e.preventDefault();
        
        const $notice = $(this).closest('.notice');
        
        // Show loading state
        $(this).prop('disabled', true).text(expoxrARIntegration.strings.dismissing);
        
        $.ajax({
            url: expoxrARIntegration.ajaxUrl,
            type: 'POST',
            data: {
                action: 'expoxr_dismiss_ar_notice',
                nonce: expoxrARIntegration.nonce
            },
            success: function(response) {
                $notice.slideUp(300, function() {
                    $(this).remove();
                });
            },
            error: function() {
                // Reset button on error
                $(this).prop('disabled', false).text(expoxrARIntegration.strings.dismissNotice);
                alert(expoxrARIntegration.strings.dismissError);
            }
        });
    });
    
    // Console warnings removed to reduce noise
    // Users should check the add-ons page for available add-ons
});
