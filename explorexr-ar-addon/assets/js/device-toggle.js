/**
 * AR Addon Device Preview Toggle
 * 
 * Handles device frame switching for testing AR button visibility
 *
 * @package ExploreXR AR Addon
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Device toggle buttons
        $('.device-toggle').on('click', function() {
            const targetDevice = $(this).data('device');
            
            // Update button states
            $('.device-toggle').removeClass('active');
            $(this).addClass('active');
            
            // Update device frame visibility
            $('.device-frame').removeClass('active');
            $('.device-frame.' + targetDevice).addClass('active');
            
            // Optional: Save preference to localStorage
            localStorage.setItem('explorexr_ar_preview_device', targetDevice);
        });
        
        // Restore last selected device from localStorage
        const savedDevice = localStorage.getItem('explorexr_ar_preview_device');
        if (savedDevice && $('.device-frame.' + savedDevice).length > 0) {
            $('.device-toggle[data-device="' + savedDevice + '"]').trigger('click');
        }
    });

})(jQuery);
