/**
 * ExpoXR Import/Export Settings Handler
 * 
 * Handles the UI interactions for the import/export settings functionality
 */

jQuery(document).ready(function($) {
    // File input handling
    $('#expoxr-import-file').on('change', function() {
        const fileInput = $(this)[0];
        const importPreview = $('#expoxr-import-preview');
        const importForm = $('#expoxr-import-form');
        
        importPreview.empty();
        
        if (fileInput.files.length === 0) {
            return;
        }
        
        const file = fileInput.files[0];
        
        // Check file size
        if (file.size > 5242880) { // 5MB limit
            importPreview.html('<div class="notice notice-error"><p>File is too large. Maximum size is 5MB.</p></div>');
            fileInput.value = '';
            return;
        }
        
        // Check file extension
        if (!file.name.endsWith('.json')) {
            importPreview.html('<div class="notice notice-error"><p>Invalid file format. Please upload a JSON file.</p></div>');
            fileInput.value = '';
            return;
        }
        
        // Show loading indicator
        importPreview.html('<p><span class="spinner is-active"></span> Reading file...</p>');
        
        // Read the file
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const jsonData = JSON.parse(e.target.result);
                
                // Check if this is a valid ExpoXR settings file
                if (!jsonData._export_info) {
                    importPreview.html('<div class="notice notice-error"><p>Invalid ExpoXR settings file.</p></div>');
                    fileInput.value = '';
                    return;
                }
                
                // Display file info
                importPreview.html(`
                    <div class="import-file-info">
                        <h4>File Information</h4>
                        <p>
                            <strong>Site:</strong> ${jsonData._export_info.site || 'Unknown'}<br>
                            <strong>Export date:</strong> ${jsonData._export_info.date || 'Unknown'}<br>
                            <strong>Plugin version:</strong> ${jsonData._export_info.plugin_version || 'Unknown'}<br>
                            <strong>Settings count:</strong> ${Object.keys(jsonData).length - 1} settings
                        </p>
                        <div class="expoxr-import-settings-preview">
                            <code>${JSON.stringify(jsonData, null, 2)}</code>
                        </div>
                        <p class="import-note"><span class="dashicons dashicons-info"></span> Please verify this is the correct settings file before importing.</p>
                    </div>
                `);
                
                // Enable submit button
                importForm.find('button[type="submit"]').prop('disabled', false);
            } catch (error) {
                importPreview.html(`<div class="notice notice-error"><p>Error reading file: ${error.message}</p></div>`);
                fileInput.value = '';
            }
        };
        
        reader.onerror = function() {
            importPreview.html('<div class="notice notice-error"><p>Error reading file.</p></div>');
            fileInput.value = '';
        };
        
        reader.readAsText(file);
    });
    
    // Confirm import
    $('#expoxr-import-form').on('submit', function(e) {
        if (!confirm('Are you sure you want to import these settings? This may override your current configuration.')) {
            e.preventDefault();
            return false;
        }
        
        // Show loading message
        $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner is-active"></span> Importing...');
    });
    
    // Confirm export
    $('#expoxr-export-form').on('submit', function() {
        $(this).find('button[type="submit"]').html('<span class="spinner is-active"></span> Exporting...');
    });
});
