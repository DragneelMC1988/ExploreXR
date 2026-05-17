/**
 * ExploreXR Deactivation Script
 *
 * This script displays a message to users when they deactivate the plugin,
 * informing them about the uninstall settings and data preservation options.
 */

// Add deactivation script
jQuery(document).ready(function($) {
    // Check if we're on plugins page by checking URL or other elements on the page
    if (!window.location.href.includes('plugins.php') && !$('body').hasClass('plugins-php')) {
        return;
    }
    
    // Find our plugin's deactivate link
    var $deactivateLink = $('tr[data-plugin="explorexr/exploreXR.php"] .deactivate a');
      // When deactivation link is clicked
    $deactivateLink.on('click', function(e) {
        // Prevent default action
        e.preventDefault();
        
        // Get the deactivation URL
        var deactivateURL = $(this).attr('href');
        
        // Create and show modal dialog
        var $modalContainer = $('<div id="explorexr-deactivation-dialog"></div>').appendTo('body');
        
        // Add modal content
        $modalContainer.html(
            '<div class="explorexr-modal">' +
                '<div class="explorexr-modal-content">' +
                    '<h3>Deactivating ExploreXR</h3>' +
                    '<p>You are about to deactivate ExploreXR. Your 3D models and settings will be preserved.</p>' +
                    '<p><strong>Note:</strong> If you plan to completely remove the plugin, you can configure what data is removed in the <a href="' + explorexrDeactivation.adminUrl + 'admin.php?page=explorexr-settings">Settings Page</a> first.</p>' +
                    '<div class="explorexr-modal-footer">' +
                        '<a href="#" class="button explorexr-cancel-deactivation">Cancel</a>' +
                        '<a href="' + explorexrDeactivation.adminUrl + 'admin.php?page=explorexr-settings" class="button">Go to Settings</a>' +
                        '<a href="#" id="explorexr-confirm-deactivation" class="button button-primary" data-url="' + deactivateURL + '">Deactivate Plugin</a>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );
          // Close dialog on outside click
        $('body').on('click', function(e) {
            if (!$(e.target).closest('.explorexr-modal-content').length && !$(e.target).is('.explorexr-modal-content')) {
                $modalContainer.remove();
            }
        });
        
        // Handle deactivation confirmation click
        $('#explorexr-confirm-deactivation').on('click', function(e) {
            e.preventDefault();
            var deactivateURL = $(this).data('url');
            
            // Remove the modal first
            $modalContainer.remove();
            
            // Navigate to the deactivation URL immediately
            window.location.href = deactivateURL;
        });
        
        // Add cancel/close button functionality
        $modalContainer.on('click', '.explorexr-cancel-deactivation', function(e) {
            e.preventDefault();
            $modalContainer.remove();
        });
        
        // Style the modal
        $('<style>' +
            '#explorexr-deactivation-dialog {' +
                'position: fixed;' +
                'top: 0;' +
                'left: 0;' +
                'right: 0;' +
                'bottom: 0;' +
                'background: rgba(0,0,0,0.6);' +
                'z-index: 9999;' +
                'display: flex;' +
                'align-items: center;' +
                'justify-content: center;' +
            '}' +
            '.explorexr-modal {' +
                'background: #fff;' +
                'padding: 20px;' +
                'border-radius: 5px;' +
                'max-width: 500px;' +
                'width: 100%;' +
                'box-shadow: 0 5px 15px rgba(0,0,0,0.2);' +
            '}' +
            '.explorexr-modal h3 {' +
                'margin-top: 0;' +
                'border-bottom: 1px solid #eee;' +
                'padding-bottom: 10px;' +
            '}' +
            '.explorexr-modal-footer {' +
                'text-align: right;' +
                'margin-top: 20px;' +
                'border-top: 1px solid #eee;' +
                'padding-top: 10px;' +
            '}' +
            '.explorexr-modal-footer .button {' +
                'margin-left: 10px;' +
            '}' +
        '</style>').appendTo('head');
    });
});
