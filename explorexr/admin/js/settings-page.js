/**
 * ExploreXR Settings Page JavaScript
 * Handles debugging functionality on the settings page
 */
jQuery(document).ready(function($) {
    // Debug log viewer functionality
    $('#explorexr-view-log').on('click', function() {
        if ($('#explorexr-debug-log-viewer').is(':visible')) {
            $('#explorexr-debug-log-viewer').slideUp();
            return;
        }
        
        $('#explorexr-debug-log-viewer').slideDown();
        $('#explorexr-log-content').text('Loading debug log contents...');
        
        // Ajax request to get log contents
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'explorexr_get_debug_log',
                nonce: explorexr_settings.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.content) {
                        $('#explorexr-log-content').text(response.data.content);
                    } else {
                        $('#explorexr-log-content').text('The debug log is empty.');
                    }
                } else {
                    $('#explorexr-log-content').text('Error loading debug log: ' + response.data.message);
                }
            },
            error: function() {
                $('#explorexr-log-content').text('Error loading debug log. Please try again.');
            }
        });
    });
    
    // Clear debug log functionality
    $('#explorexr-clear-log').on('click', function() {
        if (!confirm('Are you sure you want to clear the debug log? This action cannot be undone.')) {
            return;
        }
        
        // Ajax request to clear log
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'explorexr_clear_debug_log',
                nonce: explorexr_settings.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#explorexr-log-content').text('The debug log has been cleared.');
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
    $('#explorexr-download-log').on('click', function() {
        // Ajax request to get log contents for download
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'explorexr_get_debug_log',
                nonce: explorexr_settings.nonce
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
        'explorexr_view_php_errors',
        'explorexr_console_logging',
        'explorexr_debug_animations',
        'explorexr_debug_annotations',
        'explorexr_debug_loading_info'
    ];
    
    // Add event listeners to all debugging checkboxes
    debuggingCheckboxes.forEach(function(checkboxId) {
        $(`#${checkboxId}`).on('change', function() {
            if ($(this).is(':checked')) {
                // If any debugging option is checked, make sure debug mode is enabled
                $('#explorexr_debug_mode').prop('checked', true);
                
                // Also update the hidden field in the general settings form
                $(`#${checkboxId}_hidden`).val('1');
            } else {
                $(`#${checkboxId}_hidden`).val('');
            }
        });
    });
    
    // Sync general settings with hidden fields in debug form
    $('#explorexr_cdn_source_cdn, #explorexr_cdn_source_local').on('change', function() {
        const selectedValue = $('input[name="explorexr_cdn_source"]:checked').val();
        $('#explorexr_cdn_source_hidden').val(selectedValue);
    });
    
    $('#explorexr_model_viewer_version').on('change', function() {
        $('#explorexr_model_viewer_version_hidden').val($(this).val());
    });
    
    $('#explorexr_max_upload_size').on('change', function() {
        $('#explorexr_max_upload_size_hidden').val($(this).val());
    });
    
    // Sync debug mode checkbox changes with hidden field
    $('#explorexr_debug_mode').on('change', function() {
        if ($(this).is(':checked')) {
            $('#explorexr_debug_mode_hidden').val('1');
        } else {
            // Allow debug mode to be unchecked - also uncheck all debug options
            debuggingCheckboxes.forEach(function(checkboxId) {
                $(`#${checkboxId}`).prop('checked', false);
                $(`#${checkboxId}_hidden`).val('');
            });
            $('#explorexr_debug_mode_hidden').val('');
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
            $('#explorexr_debug_mode').prop('checked', true);
            $('#explorexr_debug_mode_hidden').val('1');
        }
    });
    
    // System information copy functionality
    $('#explorexr-copy-system-info').on('click', function() {
        // Build the text to copy
        let systemInfo = "=== EXPLOREXR SYSTEM INFORMATION ===\n\n";
        
        $('table.widefat tr').each(function() {
            const label = $(this).find('th').text();
            const value = $(this).find('td').text().trim();
            systemInfo += label + ": " + value + "\n";
        });
        
        // Copy to clipboard
        navigator.clipboard.writeText(systemInfo).then(function() {
            const $button = $('#explorexr-copy-system-info');
            $button.text('System Information Copied!');
            setTimeout(function() {
                $button.html('<span class="dashicons dashicons-clipboard settings-icon"></span> Copy System Information');
            }, 2000);
        });
    });
});