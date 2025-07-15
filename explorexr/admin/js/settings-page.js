/**
 * ExploreXR Settings Page JavaScript
 * Handles debugging functionality on the settings page
 */
jQuery(document).ready(function($) {
    // Debug log viewer functionality
    $('#expoxr-view-log').on('click', function() {
        if ($('#expoxr-debug-log-viewer').is(':visible')) {
            $('#expoxr-debug-log-viewer').slideUp();
            return;
        }
        
        $('#expoxr-debug-log-viewer').slideDown();
        $('#expoxr-log-content').text('Loading debug log contents...');
        
        // Ajax request to get log contents
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'expoxr_get_debug_log',
                nonce: expoxr_settings.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.content) {
                        $('#expoxr-log-content').text(response.data.content);
                    } else {
                        $('#expoxr-log-content').text('The debug log is empty.');
                    }
                } else {
                    $('#expoxr-log-content').text('Error loading debug log: ' + response.data.message);
                }
            },
            error: function() {
                $('#expoxr-log-content').text('Error loading debug log. Please try again.');
            }
        });
    });
    
    // Clear debug log functionality
    $('#expoxr-clear-log').on('click', function() {
        if (!confirm('Are you sure you want to clear the debug log? This action cannot be undone.')) {
            return;
        }
        
        // Ajax request to clear log
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'expoxr_clear_debug_log',
                nonce: expoxr_settings.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#expoxr-log-content').text('The debug log has been cleared.');
                } else {
                    alert('Error clearing debug log: ' + response.data.message);
                }
            },
            error: function() {
                alert('Error clearing debug log. Please try again.');
            }
        });
    });
    
    // Download debug log functionality
    $('#expoxr-download-log').on('click', function() {
        // Ajax request to get log contents for download
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'expoxr_get_debug_log',
                nonce: expoxr_settings.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.content) {
                        // Create blob and download
                        const blob = new Blob([response.data.content], {type: 'text/plain'});
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
                        
                        a.style.display = 'none';
                        a.href = url;
                        a.download = `explorexr-debug-log-${timestamp}.txt`;
                        document.body.appendChild(a);
                        a.click();
                        
                        // Clean up
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                    } else {
                        alert('The debug log is empty.');
                    }
                } else {
                    alert('Error downloading debug log: ' + response.data.message);
                }
            },
            error: function() {
                alert('Error downloading debug log. Please try again.');
            }
        });
    });
    
    // Fix for debugging options: Ensure debug mode is enabled when any debugging option is checked
    // Get all debugging section checkboxes
    const debuggingCheckboxes = [
        'expoxr_debug_log',
        'expoxr_view_php_errors',
        'expoxr_console_logging',
        'expoxr_debug_ar_features',
        'expoxr_debug_camera_controls',
        'expoxr_debug_animations',
        'expoxr_debug_annotations',
        'expoxr_debug_loading_info'
    ];
    
    // Add event listeners to all debugging checkboxes
    debuggingCheckboxes.forEach(function(checkboxId) {
        $(`#${checkboxId}`).on('change', function() {
            if ($(this).is(':checked')) {
                // If any debugging option is checked, make sure debug mode is enabled
                $('#expoxr_debug_mode').prop('checked', true);
                
                // Also update the hidden field in the general settings form
                $(`#${checkboxId}_hidden`).val('1');
            } else {
                $(`#${checkboxId}_hidden`).val('');
            }
        });
    });
    
    // Sync general settings with hidden fields in debug form
    $('#expoxr_cdn_source_cdn, #expoxr_cdn_source_local').on('change', function() {
        const selectedValue = $('input[name="expoxr_cdn_source"]:checked').val();
        $('#expoxr_cdn_source_hidden').val(selectedValue);
    });
    
    $('#expoxr_model_viewer_version').on('change', function() {
        $('#expoxr_model_viewer_version_hidden').val($(this).val());
    });
    
    $('#expoxr_max_upload_size').on('change', function() {
        $('#expoxr_max_upload_size_hidden').val($(this).val());
    });
    
    // Sync debug mode checkbox changes with hidden field
    $('#expoxr_debug_mode').on('change', function() {
        if ($(this).is(':checked')) {
            $('#expoxr_debug_mode_hidden').val('1');
        } else {
            let anyDebugOptionChecked = false;
            debuggingCheckboxes.forEach(function(checkboxId) {
                if ($(`#${checkboxId}`).is(':checked')) {
                    anyDebugOptionChecked = true;
                }
            });
            
            // If any debug option is checked, don't allow debug mode to be disabled
            if (anyDebugOptionChecked) {
                $('#expoxr_debug_mode').prop('checked', true);
                $('#expoxr_debug_mode_hidden').val('1');
            } else {
                $('#expoxr_debug_mode_hidden').val('');
            }
        }
    });
    
    // Ensure debug mode is enabled before form submission if any debugging option is checked
    $('form').submit(function() {
        let anyDebugOptionChecked = false;
        
        // Check if any debugging option is enabled
        debuggingCheckboxes.forEach(function(checkboxId) {
            if ($(`#${checkboxId}`).is(':checked')) {
                anyDebugOptionChecked = true;
                $(`#${checkboxId}_hidden`).val('1');
            } else {
                $(`#${checkboxId}_hidden`).val('');
            }
        });
        
        // If any debug option is checked, ensure debug mode is enabled
        if (anyDebugOptionChecked) {
            $('#expoxr_debug_mode').prop('checked', true);
            $('#expoxr_debug_mode_hidden').val('1');
        }
    });
    
    // System information copy functionality
    $('#expoxr-copy-system-info').on('click', function() {
        // Build the text to copy
        let systemInfo = "=== EXPLOREXR SYSTEM INFORMATION ===\n\n";
        
        $('table.widefat tr').each(function() {
            const label = $(this).find('th').text();
            const value = $(this).find('td').text().trim();
            systemInfo += label + ": " + value + "\n";
        });
        
        // Copy to clipboard
        navigator.clipboard.writeText(systemInfo).then(function() {
            const $button = $('#expoxr-copy-system-info');
            $button.text('System Information Copied!');
            setTimeout(function() {
                $button.html('<span class="dashicons dashicons-clipboard settings-icon"></span> Copy System Information');
            }, 2000);
        });
    });
});