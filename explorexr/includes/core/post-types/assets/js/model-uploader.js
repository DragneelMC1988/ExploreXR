/**
 * Model Uploader JavaScript
 * Handles the upload and preview of 3D model files
 */

document.addEventListener('DOMContentLoaded', function() {
    // File input change handlers
    const modelFileInput = document.getElementById('explorexr_model_file_upload');
    const posterFileInput = document.getElementById('explorexr_model_poster_upload');
    
    if (modelFileInput) {
        modelFileInput.addEventListener('change', handleModelFileSelect);
    }
    
    if (posterFileInput) {
        posterFileInput.addEventListener('change', handlePosterFileSelect);
    }
    
    // Handle form submission to ensure files are processed
    const postForm = document.getElementById('post');
    if (postForm) {
        postForm.addEventListener('submit', function(e) {
            
            // Add a hidden field to signal this is coming from the edit form
            let editModeField = document.getElementById('explorexr_edit_mode_field');
            if (!editModeField) {
                editModeField = document.createElement('input');
                editModeField.type = 'hidden';
                editModeField.name = 'explorexr_edit_mode';
                editModeField.id = 'explorexr_edit_mode_field';
                editModeField.value = '1';
                postForm.appendChild(editModeField);
            }
              // Add a nonce field if it doesn't exist
            if (!document.querySelector('input[name="explorexr_nonce"]')) {
                console.warn('ExploreXR: No nonce field found in model-uploader.js - adding backup nonce field');
                
                // Try to find any WordPress nonce field to use its value
                const wpNonce = document.querySelector('input[name="_wpnonce"]');
                const nonceValue = wpNonce ? wpNonce.value : '';
                
                // Create our specific nonce field
                const nonceField = document.createElement('input');
                nonceField.type = 'hidden';
                nonceField.name = 'explorexr_nonce';
                nonceField.id = 'explorexr_nonce';
                nonceField.value = nonceValue;
                postForm.appendChild(nonceField);
                
            }
            
            // Create hidden fields for all checkboxes to ensure their state is saved
            const allCheckboxes = document.querySelectorAll('.postbox input[type="checkbox"]');
            allCheckboxes.forEach(function(cb) {
                // Create hidden field with _state suffix to track checkbox value
                let hiddenField = document.getElementById(cb.id + '_state');
                if (!hiddenField) {
                    hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = cb.name + '_state';
                    hiddenField.id = cb.id + '_state';
                    postForm.appendChild(hiddenField);
                }
                hiddenField.value = cb.checked ? '1' : '0';
            });
            
            // Ensure all model settings are properly passed
            captureFormChanges();
            
        });
    }
    
    // Add change tracking for all checkbox inputs specifically
    setupCheckboxTracking();
    
    // Set up change tracking for all model settings fields
    setupChangeTracking();
    
    // Initialize any existing previews
    initExistingPreviews();
    
    // Add a debug notice to indicate the enhanced JS is loaded
});

/**
 * Set up special handling for checkboxes to ensure they're properly tracked
 * This is critical as unchecked boxes don't get submitted with the form
 */
function setupCheckboxTracking() {
    const checkboxes = document.querySelectorAll('.postbox input[type="checkbox"]');
    
    checkboxes.forEach(function(checkbox) {
        // Create a hidden field to ensure the checkbox state is always submitted
        let hiddenField = document.getElementById(checkbox.id + '_state');
        if (!hiddenField) {
            hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.name = checkbox.name + '_state';
            hiddenField.value = checkbox.checked ? '1' : '0';
            hiddenField.id = checkbox.id + '_state';
            checkbox.parentNode.appendChild(hiddenField);
        }
        
        // Update the hidden field when checkbox changes
        checkbox.addEventListener('change', function() {
            const stateField = document.getElementById(checkbox.id + '_state');
            if (stateField) {
                stateField.value = checkbox.checked ? '1' : '0';
            }
        });
    });
}

/**
 * Capture all form changes before submission
 */
function captureFormChanges() {
    // Get all input fields, textareas and selects within model metaboxes
    const metaboxes = document.querySelectorAll('.postbox');
    
    metaboxes.forEach(function(metabox) {
        if (metabox.id.startsWith('explorexr_model_')) {
            const inputs = metabox.querySelectorAll('input, textarea, select');
            
            inputs.forEach(function(input) {
                // Force a change event to ensure the latest value is captured
                if (input.type !== 'file') {
                    const event = new Event('change', { bubbles: true });
                    input.dispatchEvent(event);
                }
            });
        }
    });
}

/**
 * Set up change tracking for all form fields
 */
function setupChangeTracking() {
    // Track changes on all input fields except file inputs
    const formFields = document.querySelectorAll('.postbox input:not([type="file"]), .postbox textarea, .postbox select');
    
    formFields.forEach(function(field) {
        field.addEventListener('change', function() {
            // Store the changed value in a hidden field if needed
            if (field.dataset.track === 'true') {
                const trackingField = document.getElementById(field.id + '_tracking');
                if (trackingField) {
                    trackingField.value = field.value;
                }
            }
            
            // Mark the form as having unsaved changes
            const postform = document.getElementById('post');
            if (postform) {
                postform.dataset.changed = 'true';
            }
        });
    });
}

/**
 * Initialize previews for existing models
 */
function initExistingPreviews() {
    const modelPreview = document.getElementById('explorexr-model-preview');
    if (!modelPreview) return;
    
    const modelUrl = modelPreview.dataset.modelUrl;
    const posterUrl = modelPreview.dataset.posterUrl;
    
    if (modelUrl) {
        // Initialize model-viewer with existing model
        updateModelPreview(modelUrl, posterUrl);
    }
}

/**
 * Handle model file selection
 */
function handleModelFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Check file type
    const validTypes = ['model/gltf-binary', 'model/gltf+json', 'model/vnd.usdz+zip'];
    const fileExt = file.name.split('.').pop().toLowerCase();
    
    if (!['glb', 'gltf', 'usdz'].includes(fileExt)) {
        alert('Please select a valid 3D model file (GLB, GLTF, or USDZ).');
        event.target.value = '';
        return;
    }
    
    // Create a preview
    const modelPreview = document.getElementById('explorexr-model-preview');
    if (modelPreview) {
        // Get URL for the uploaded file
        const objectUrl = URL.createObjectURL(file);
        
        // Get the current poster URL if any
        const posterUrl = modelPreview.dataset.posterUrl || '';
        
        // Update the preview
        updateModelPreview(objectUrl, posterUrl);
        
        // Update file name display
        const fileNameDisplay = document.getElementById('explorexr-model-filename');
        if (fileNameDisplay) {
            fileNameDisplay.textContent = file.name;
        }
        
        // Store file information in hidden fields for form submission
        document.getElementById('explorexr_model_file_name').value = file.name;
        
        // Mark that we have a new file
        document.getElementById('explorexr_model_has_new_file').value = '1';
    }
}

/**
 * Handle poster image selection
 */
function handlePosterFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Check file type
    if (!file.type.match('image.*')) {
        alert('Please select a valid image file for the poster.');
        event.target.value = '';
        return;
    }
    
    // Create a preview
    const posterPreview = document.getElementById('explorexr-poster-preview');
    if (posterPreview) {
        // Get URL for the uploaded file
        const objectUrl = URL.createObjectURL(file);
        
        // Update the preview image
        posterPreview.src = objectUrl;
        posterPreview.style.display = 'block';
        
        // Get the current model URL
        const modelPreview = document.getElementById('explorexr-model-preview');
        const modelUrl = modelPreview ? modelPreview.dataset.modelUrl : '';
        
        // Update model viewer with new poster
        if (modelUrl) {
            updateModelPreview(modelUrl, objectUrl);
        }
        
        // Store file information in hidden fields for form submission
        document.getElementById('explorexr_model_poster_name').value = file.name;
        
        // Mark that we have a new poster
        document.getElementById('explorexr_model_has_new_poster').value = '1';
    }
}

/**
 * Update the model preview with a new model and/or poster
 */
function updateModelPreview(modelUrl, posterUrl) {
    const modelPreview = document.getElementById('explorexr-model-preview');
    if (!modelPreview) return;
    
    // Store URLs for later reference
    modelPreview.dataset.modelUrl = modelUrl;
    if (posterUrl) {
        modelPreview.dataset.posterUrl = posterUrl;
    }
    
    // Remove existing model-viewer if any
    const existingViewer = modelPreview.querySelector('model-viewer');
    if (existingViewer) {
        modelPreview.removeChild(existingViewer);
    }
    
    // Create new model-viewer element
    const modelViewer = document.createElement('model-viewer');
    modelViewer.src = modelUrl;
    if (posterUrl) {
        modelViewer.poster = posterUrl;
    }
    
    // Set common attributes
    modelViewer.setAttribute('alt', 'A 3D model');
    modelViewer.setAttribute('camera-controls', '');
    modelViewer.setAttribute('auto-rotate', '');
    modelViewer.setAttribute('ar', '');
    
    // Append to the preview container
    modelPreview.appendChild(modelViewer);
    
    // Show the preview container
    modelPreview.style.display = 'block';
}
