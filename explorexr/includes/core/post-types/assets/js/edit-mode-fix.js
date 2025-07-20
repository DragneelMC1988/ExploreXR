/**
 * Fix for Model Uploader JavaScript - Edit Mode Issue
 * This script fixes the issue where changes aren't saved in Edit mode
 * Version: 1.0.1
 */

// Enable debugging based on WordPress debug setting
const EXPLOREXR_DEBUG = (typeof exploreXRDebugMode !== 'undefined' && exploreXRDebugMode.debug) ? exploreXRDebugMode.enabled : false;

// Debug logging function
function explorexrDebugLog(message) {
    if (EXPLOREXR_DEBUG) {
        console.log('ExploreXR Fix: ' + message);
    }
}

// Run the fix as soon as DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Get the form element
    const postForm = document.getElementById('post');
    const isEditMode = document.body.classList.contains('post-type-explorexr_model') && 
                      (document.body.classList.contains('post-php') || document.body.classList.contains('post-new-php'));
    
    explorexrDebugLog('Edit mode detection - ' + (isEditMode ? 'Active' : 'Inactive'));
    
    if (postForm && isEditMode) {
        // Create a defensive submit handler
        const originalSubmit = postForm.submit;
        postForm.submit = function() {
            // Process form before submission
            processFormFields();
            processSpecialFields();
            markEditMode();
            explorexrDebugLog('Defensive form submission handler executed');
            return originalSubmit.apply(this, arguments);
        };
        
        // Add event listener for form submission
        postForm.addEventListener('submit', function(e) {
            explorexrDebugLog('Form submission detected');
            
            // Process standard form fields first
            processFormFields();
            
            // Handle special fields with multiple values or complex structures
            processSpecialFields();
            
            // Track and mark edit mode
            markEditMode();
            
            explorexrDebugLog('Form prepared for submission');
        });
        
        // Enable real-time tracking for checkboxes and other fields
        setupFieldTracking();
        
        // Intercept WordPress publish/update button clicks
        const publishButton = document.getElementById('publish');
        if (publishButton) {
            publishButton.addEventListener('click', function() {
                explorexrDebugLog('Publish button clicked - preparing form data');
                processFormFields();
                processSpecialFields();
                markEditMode();
            });
        }
        
        // Log information about the form
        const formInfo = {
            metaboxCount: document.querySelectorAll('.postbox').length,
            fieldCount: document.querySelectorAll('input, select, textarea').length,
            checkboxCount: document.querySelectorAll('input[type="checkbox"]').length,
            postId: document.querySelector('#post_ID') ? document.querySelector('#post_ID').value : 'unknown'
        };
        
        explorexrDebugLog('Edit mode fix loaded successfully');
        explorexrDebugLog('Form info: ' + JSON.stringify(formInfo));
    }
});

/**
 * Process standard form fields to ensure they're included in the form submission
 */
function processFormFields() {
    // Get all input elements in model metaboxes
    const metaboxes = document.querySelectorAll('.postbox[id^="explorexr_model_"]');
    
    metaboxes.forEach(function(metabox) {
        const inputs = metabox.querySelectorAll('input:not([type="file"]), textarea, select');
        
        inputs.forEach(function(input) {
            // Skip hidden tracking fields to avoid duplication
            if (input.name.endsWith('_state') || input.name.endsWith('_tracking')) {
                return;
            }
            
            // Ensure field is marked as changed to be submitted
            if (input.type === 'checkbox' || input.type === 'radio') {
                ensureCheckboxTracking(input);
            } else {
                ensureFieldTracking(input);
            }
        });
    });
}

/**
 * Process special fields that require custom handling (e.g., complex JSON data, arrays)
 */
function processSpecialFields() {
    explorexrDebugLog('Processing special fields');
    
    // 1. Handle annotations if they exist
    const annotationItems = document.querySelectorAll('.explorexr-annotation-item');
    if (annotationItems.length > 0) {
        // Make sure the annotation count field is updated
        const annotationCountField = document.getElementById('explorexr_annotation_count');
        if (annotationCountField) {
            annotationCountField.value = annotationItems.length;
            explorexrDebugLog('Updated annotation count: ' + annotationItems.length);
        }
        
        // Process any annotation data that might be stored in JSON format
        const annotationDataFields = document.querySelectorAll('input[name*="explorexr_annotations"], textarea[name*="explorexr_annotations"]');
        annotationDataFields.forEach(function(field) {
            // Force update to ensure values are tracked
            field.dispatchEvent(new Event('change'));
            explorexrDebugLog('Processed annotation field: ' + field.name);
        });
    }
    
    // 2. Handle AR modes which are often multi-select
    const arModesSelects = document.querySelectorAll('select[name="explorexr_ar_modes[]"]');
    if (arModesSelects.length > 0) {
        // Ensure multi-select values are properly tracked
        arModesSelects.forEach(function(select) {
            // Force a change event to ensure values are updated
            select.dispatchEvent(new Event('change'));
            explorexrDebugLog('Forced change event on AR modes select: ' + select.name);
        });
    }
    
    // 3. Process any hidden JSON data fields
    const jsonFields = document.querySelectorAll('input[type="hidden"][data-format="json"], textarea[data-format="json"]');
    jsonFields.forEach(function(field) {
        // Make sure JSON field has proper tracking
        if (field.name && !field.name.endsWith('_tracking')) {
            ensureFieldTracking(field);
            explorexrDebugLog('Added tracking for JSON field: ' + field.name);
        }
    });
    
    // 4. Process camera presets if they exist
    const cameraPresetFields = document.querySelectorAll('input[name*="camera_preset"], input[name*="camera_orbit"]');
    cameraPresetFields.forEach(function(field) {
        ensureFieldTracking(field);
        explorexrDebugLog('Added tracking for camera field: ' + field.name);
    });
}

/**
 * Mark the form as being submitted in Edit mode
 */
function markEditMode() {
    const postForm = document.getElementById('post');
    if (!postForm) {
        console.error('ExploreXR Fix: Post form not found!');
        return;
    }
    
    // Add a hidden field to signal this is coming from the edit form
    let editModeField = document.getElementById('explorexr_edit_mode_field');
    if (!editModeField) {
        editModeField = document.createElement('input');
        editModeField.type = 'hidden';
        editModeField.name = 'explorexr_edit_mode';
        editModeField.id = 'explorexr_edit_mode_field';
        editModeField.value = '1';
        postForm.appendChild(editModeField);
        explorexrDebugLog('Added edit mode marker');
    }
      // Ensure nonce field exists
    const nonceField = document.querySelector('input[name="explorexr_nonce"]');
    if (!nonceField) {
        console.warn('ExploreXR Fix: No nonce field found - this will cause security validation to fail');
        
        // Try to find the nonce in any meta box
        const metaboxes = document.querySelectorAll('.postbox');
        let nonceFound = false;
        
        metaboxes.forEach(function(box) {
            const possibleNonceField = box.querySelector('input[name*="nonce"]');
            if (possibleNonceField) {
                console.warn('ExploreXR Fix: Found potential nonce field: ' + possibleNonceField.name);
                
                // Create our specific nonce field as backup
                const backupNonce = document.createElement('input');
                backupNonce.type = 'hidden';
                backupNonce.name = 'explorexr_nonce';
                backupNonce.value = possibleNonceField.value;
                postForm.appendChild(backupNonce);
                
                explorexrDebugLog('Created backup nonce field from: ' + possibleNonceField.name);
                nonceFound = true;
            }
        });
        
        // If no nonce found, create one with empty value as last resort
        if (!nonceFound) {
            const emptyNonce = document.createElement('input');
            emptyNonce.type = 'hidden';
            emptyNonce.name = 'explorexr_nonce';
            emptyNonce.value = ''; // Empty value will fail verification but prevent PHP errors
            postForm.appendChild(emptyNonce);
            explorexrDebugLog('Created empty nonce field as last resort');
        }
    } else {
        explorexrDebugLog('Nonce field found and validated');
    }
    
    // Add diagnostic information
    let diagnosticField = document.getElementById('explorexr_edit_diagnostic');
    if (!diagnosticField) {
        diagnosticField = document.createElement('input');
        diagnosticField.type = 'hidden';
        diagnosticField.name = 'explorexr_edit_diagnostic';
        diagnosticField.id = 'explorexr_edit_diagnostic';
        
        // Store some diagnostic data
        const data = {
            timestamp: new Date().toISOString(),
            browser: navigator.userAgent,
            fields: document.querySelectorAll('.postbox input, .postbox select, .postbox textarea').length,
            metaboxes: document.querySelectorAll('.postbox[id^="explorexr_model_"]').length
        };
        
        diagnosticField.value = JSON.stringify(data);
        postForm.appendChild(diagnosticField);
        explorexrDebugLog('Added diagnostic data');
    }
}

/**
 * Set up tracking for all interactive fields
 */
function setupFieldTracking() {
    explorexrDebugLog('Setting up field tracking');
    
    // Track all checkboxes specifically
    const checkboxes = document.querySelectorAll('.postbox input[type="checkbox"]');
    explorexrDebugLog('Found ' + checkboxes.length + ' checkboxes to track');
    
    checkboxes.forEach(function(checkbox) {
        ensureCheckboxTracking(checkbox);
        
        // Update tracking field when checkbox changes
        checkbox.addEventListener('change', function() {
            const stateField = document.getElementById(checkbox.id + '_state');
            if (stateField) {
                stateField.value = checkbox.checked ? '1' : '0';
                explorexrDebugLog('Updated checkbox state for ' + checkbox.name + ' to ' + (checkbox.checked ? 'checked' : 'unchecked'));
            }
        });
    });
    
    // Track all select fields
    const selects = document.querySelectorAll('.postbox select');
    explorexrDebugLog('Found ' + selects.length + ' select fields to track');
    
    selects.forEach(function(select) {
        ensureFieldTracking(select);
        
        // Update tracking for selects when they change
        select.addEventListener('change', function() {
            const trackingField = document.getElementById(select.id + '_tracking');
            if (trackingField) {
                trackingField.value = select.value;
                explorexrDebugLog('Updated select value for ' + select.name + ' to ' + select.value);
            }
        });
    });
}

/**
 * Ensure a checkbox has proper state tracking
 */
function ensureCheckboxTracking(checkbox) {
    // Skip if checkbox has no ID or name
    if (!checkbox.id || !checkbox.name) {
        explorexrDebugLog('Skipping checkbox without ID or name');
        return;
    }
    
    let stateField = document.getElementById(checkbox.id + '_state');
    
    // Create tracking field if it doesn't exist
    if (!stateField) {
        stateField = document.createElement('input');
        stateField.type = 'hidden';
        stateField.name = checkbox.name + '_state';
        stateField.id = checkbox.id + '_state';
        stateField.value = checkbox.checked ? '1' : '0';
        
        // Insert right after the checkbox
        if (checkbox.form) {
            checkbox.form.appendChild(stateField);
            explorexrDebugLog('Created tracking field for checkbox ' + checkbox.name + ' (added to form)');
        } else if (checkbox.parentNode) {
            checkbox.parentNode.insertBefore(stateField, checkbox.nextSibling);
            explorexrDebugLog('Created tracking field for checkbox ' + checkbox.name + ' (added to parent)');
        } else {
            console.error('ExploreXR Fix: Cannot add tracking field for checkbox ' + checkbox.name);
            return;
        }
    } else {
        // Update existing state field
        stateField.value = checkbox.checked ? '1' : '0';
        explorexrDebugLog('Updated existing tracking field for checkbox ' + checkbox.name);
    }
}

/**
 * Ensure a field has proper value tracking
 */
function ensureFieldTracking(field) {
    // Skip fields that don't need tracking
    if (!field.id || !field.name || field.type === 'button' || field.type === 'submit' || field.type === 'reset') {
        return;
    }
    
    let trackingField = document.getElementById(field.id + '_tracking');
    
    // Create tracking field if it doesn't exist
    if (!trackingField) {
        trackingField = document.createElement('input');
        trackingField.type = 'hidden';
        trackingField.name = field.name + '_tracking';
        trackingField.id = field.id + '_tracking';
        trackingField.value = field.value;
        
        // Insert right after the field
        field.parentNode.insertBefore(trackingField, field.nextSibling);
        explorexrDebugLog('Created tracking field for ' + field.name);
    } else {
        // Update existing tracking field
        trackingField.value = field.value;
    }
}

/**
 * Troubleshooting functions for diagnosing edit mode issues
 */

// Function to check if all tracking fields are correctly associated
function troubleshootEditMode() {
    explorexrDebugLog('--- Starting Edit Mode Troubleshooting ---');
    
    // Check if we're in edit mode
    const isEditPage = document.body.classList.contains('post-php') || document.body.classList.contains('post-new-php');
    const isExploreXRPost = document.body.classList.contains('post-type-explorexr_model');
    
    explorexrDebugLog('Is edit page: ' + isEditPage);
    explorexrDebugLog('Is ExploreXR model: ' + isExploreXRPost);
    
    // Check if nonce exists
    const hasNonce = !!document.querySelector('input[name="explorexr_nonce"]');
    explorexrDebugLog('Has nonce field: ' + hasNonce);
    
    // Check for form submission handler
    const hasForm = !!document.getElementById('post');
    explorexrDebugLog('Has post form: ' + hasForm);
    
    // Check all metaboxes
    const metaboxes = document.querySelectorAll('.postbox');
    explorexrDebugLog('Total metaboxes: ' + metaboxes.length);
    
    // Count fields by type
    let fieldCounts = {
        text: 0,
        checkbox: 0,
        select: 0,
        textarea: 0,
        hidden: 0,
        file: 0,
        other: 0
    };
    
    document.querySelectorAll('input, select, textarea').forEach(field => {
        if (field.type === 'text') fieldCounts.text++;
        else if (field.type === 'checkbox') fieldCounts.checkbox++;
        else if (field.type === 'select-one' || field.type === 'select-multiple') fieldCounts.select++;
        else if (field.type === 'textarea') fieldCounts.textarea++;
        else if (field.type === 'hidden') fieldCounts.hidden++;
        else if (field.type === 'file') fieldCounts.file++;
        else fieldCounts.other++;
    });
    
    explorexrDebugLog('Field counts: ' + JSON.stringify(fieldCounts));
    
    explorexrDebugLog('--- Edit Mode Troubleshooting Complete ---');
    return {
        isEditMode: isEditPage && isExploreXRPost,
        hasNonce: hasNonce,
        hasForm: hasForm,
        metaboxCount: metaboxes.length,
        fieldCounts: fieldCounts
    };
}

// Make troubleshooting function available globally for manual checks
window.troubleshootExploreXREditMode = troubleshootEditMode;
