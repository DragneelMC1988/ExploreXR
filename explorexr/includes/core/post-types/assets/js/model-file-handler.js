/**
 * ExpoXR Model File Handler
 *
 * Handles the model file upload and change functionality in the model file metabox
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Toggle the model upload section when the change model button is clicked
        $('#expoxr_change_model_btn').on('click', function() {
            $('#expoxr_model_upload').toggle();
        });
        
        // Update model name when file is selected
        $('#expoxr_new_model').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            if (fileName) {
                // Remove file extension for model name suggestion
                var modelName = fileName.replace(/\.[^/.]+$/, "");
                
                // Only set the model name if it's currently empty
                if (!$('#expoxr_model_name').val()) {
                    $('#expoxr_model_name').val(modelName);
                }
            }
        });
        
        // Listen for paste events on the model file URL input
        $('#expoxr_model_file').on('paste', function() {
            // Use setTimeout to get the pasted value after the paste event
            setTimeout(function() {
                var modelUrl = $('#expoxr_model_file').val();
                if (modelUrl) {
                    // Extract filename from URL
                    var fileName = modelUrl.split('/').pop();
                    
                    // Only set the model name if it's currently empty
                    if (!$('#expoxr_model_name').val() && fileName) {
                        // Remove file extension and URL parameters
                        var modelName = fileName.replace(/\.[^/.]+$/, "").replace(/\?.*$/, "");
                        $('#expoxr_model_name').val(modelName);
                    }
                }
            }, 100);
        });
        
        // Validate file type on selection
        $('#expoxr_new_model').on('change', function() {
            var fileInput = this;
            var fileName = fileInput.value;
            var allowedExtensions = /(\.glb|\.gltf|\.usdz)$/i;
            
            if (!allowedExtensions.exec(fileName)) {
                alert('Please upload file having extensions .glb, .gltf, or .usdz only.');
                fileInput.value = '';
                return false;
            }
            
            // Check file size (max 50MB)
            if (fileInput.files[0].size > 50 * 1024 * 1024) {
                alert('File size too large. Maximum file size is 50MB.');
                fileInput.value = '';
                return false;
            }
            
            return true;
        });
    });
    
})(jQuery);