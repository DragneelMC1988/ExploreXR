jQuery(document).ready(function($) {
    
    // Sync general settings form fields with hidden fields (for preserving settings across form submissions)
    $('#explorexr_cdn_source').on('change', function() {
        $('#explorexr_cdn_source_hidden').val($(this).val());
    });
    
    $('#explorexr_model_viewer_version').on('change', function() {
        $('#explorexr_model_viewer_version_hidden').val($(this).val());
    });
    
    $('#explorexr_max_upload_size').on('change', function() {
        $('#explorexr_max_upload_size_hidden').val($(this).val());
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
