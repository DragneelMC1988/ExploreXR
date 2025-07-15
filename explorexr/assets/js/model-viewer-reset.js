/**
 * ExploreXR Model Viewer Reset
 * Provides reset functionality for 3D model viewer settings in edit mode
 */

document.addEventListener('DOMContentLoaded', function() {
    // Default settings for different model viewer properties
    const defaultSettings = {
        camera: {
            orbit: '0deg 75deg 105%',
            target: '0m 0m 0m',
            fieldOfView: '30deg'
        },
        exposure: 1,
        shadowIntensity: 1,
        shadowSoftness: 1,
        environment: 'neutral',
        autoRotate: false,
        autoRotateDelay: 3000,
        rotationPerSecond: '30deg',
        touchAction: 'pan-y',
        backgroundColor: '#ffffff',
        exposure: 1,
        toneMapping: 'auto',
        annotations: [],
        hotspots: []
    };
    
    /**
     * Reset specific property to default value for the given model viewer
     * @param {HTMLElement} modelViewer - The model-viewer element
     * @param {string} property - The property to reset
     */
    function resetProperty(modelViewer, property) {
        if (!modelViewer) return;
        
        switch (property) {
            case 'camera':
                modelViewer.setAttribute('camera-orbit', defaultSettings.camera.orbit);
                modelViewer.setAttribute('camera-target', defaultSettings.camera.target);
                modelViewer.setAttribute('field-of-view', defaultSettings.camera.fieldOfView);
                break;
                
            case 'lighting':
                modelViewer.setAttribute('environment-image', defaultSettings.environment);
                modelViewer.setAttribute('exposure', defaultSettings.exposure);
                modelViewer.setAttribute('shadow-intensity', defaultSettings.shadowIntensity);
                modelViewer.setAttribute('shadow-softness', defaultSettings.shadowSoftness);
                break;
                
            case 'animation':
                if (defaultSettings.autoRotate) {
                    modelViewer.setAttribute('auto-rotate', '');
                    modelViewer.setAttribute('auto-rotate-delay', defaultSettings.autoRotateDelay);
                    modelViewer.setAttribute('rotation-per-second', defaultSettings.rotationPerSecond);
                } else {
                    modelViewer.removeAttribute('auto-rotate');
                }
                break;
                
            case 'interaction':
                modelViewer.setAttribute('touch-action', defaultSettings.touchAction);
                break;
                
            case 'rendering':
                modelViewer.style.backgroundColor = defaultSettings.backgroundColor;
                modelViewer.setAttribute('exposure', defaultSettings.exposure);
                modelViewer.setAttribute('tone-mapping', defaultSettings.toneMapping);
                break;
                
            case 'annotations':
                // Remove all existing annotations/hotspots
                const hotspots = modelViewer.querySelectorAll('[slot^="hotspot-"]');
                hotspots.forEach(hotspot => hotspot.remove());
                break;
                
            case 'all':
                // Reset all properties
                resetProperty(modelViewer, 'camera');
                resetProperty(modelViewer, 'lighting');
                resetProperty(modelViewer, 'animation');
                resetProperty(modelViewer, 'interaction');
                resetProperty(modelViewer, 'rendering');
                resetProperty(modelViewer, 'annotations');
                break;
        }
        
        // Fire a custom event to notify that properties have been reset
        const resetEvent = new CustomEvent('expoxr-property-reset', {
            detail: {
                property: property,
                modelViewer: modelViewer
            }
        });
        document.dispatchEvent(resetEvent);
    }
    
    /**
     * Initialize reset buttons in the edit interface
     */
    function initResetButtons() {
        // For the admin edit screen where model properties are edited
        const editInterface = document.querySelector('.expoxr-model-edit-interface');
        if (!editInterface) return;
        
        const modelViewer = document.querySelector('#expoxr-preview-model');
        if (!modelViewer) return;
        
        // Add reset buttons to each settings section
        const sections = [
            { id: 'camera-settings', label: 'Camera', property: 'camera' },
            { id: 'lighting-settings', label: 'Lighting', property: 'lighting' },
            { id: 'animation-settings', label: 'Animation', property: 'animation' },
            { id: 'interaction-settings', label: 'Interaction', property: 'interaction' },
            { id: 'rendering-settings', label: 'Rendering', property: 'rendering' },
            { id: 'annotations-settings', label: 'Annotations', property: 'annotations' }
        ];
        
        sections.forEach(section => {
            const sectionElement = document.getElementById(section.id);
            if (!sectionElement) return;
            
            // Create reset button
            const resetButton = document.createElement('button');
            resetButton.type = 'button';
            resetButton.className = 'expoxr-reset-button button button-secondary';
            resetButton.textContent = `Reset ${section.label}`;
            resetButton.dataset.property = section.property;
            
            // Add click handler
            resetButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Confirm reset
                if (confirm(`Reset ${section.label} settings to default values?`)) {
                    resetProperty(modelViewer, section.property);
                    
                    // If this is the admin form, also update form fields
                    updateFormFields(section.property);
                }
            });
            
            // Add button to section
            const heading = sectionElement.querySelector('h3, h4, .expoxr-section-header');
            if (heading) {
                heading.appendChild(resetButton);
            } else {
                sectionElement.prepend(resetButton);
            }
        });
        
        // Add a full reset button
        const fullResetButton = document.createElement('button');
        fullResetButton.type = 'button';
        fullResetButton.className = 'expoxr-reset-all-button button button-secondary';
        fullResetButton.textContent = 'Reset All Settings';
        fullResetButton.style.marginTop = '20px';
        
        fullResetButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Confirm full reset
            if (confirm('Reset all model viewer settings to default values? This cannot be undone.')) {
                resetProperty(modelViewer, 'all');
                
                // Update all form fields
                sections.forEach(section => {
                    updateFormFields(section.property);
                });
            }
        });
        
        // Add full reset button at the bottom of the form
        const submitButton = editInterface.querySelector('.submit');
        if (submitButton) {
            submitButton.prepend(fullResetButton);
        } else {
            editInterface.appendChild(fullResetButton);
        }
    }
    
    /**
     * Update form fields after a reset
     * @param {string} propertyGroup - The property group that was reset
     */
    function updateFormFields(propertyGroup) {
        switch (propertyGroup) {
            case 'camera':
                document.getElementById('_expoxr_camera_orbit')?.value = defaultSettings.camera.orbit;
                document.getElementById('_expoxr_camera_target')?.value = defaultSettings.camera.target;
                document.getElementById('_expoxr_field_of_view')?.value = defaultSettings.camera.fieldOfView;
                break;
                
            case 'lighting':
                document.getElementById('_expoxr_environment_image')?.value = defaultSettings.environment;
                document.getElementById('_expoxr_exposure')?.value = defaultSettings.exposure;
                document.getElementById('_expoxr_shadow_intensity')?.value = defaultSettings.shadowIntensity;
                document.getElementById('_expoxr_shadow_softness')?.value = defaultSettings.shadowSoftness;
                break;
                
            case 'animation':
                document.getElementById('_expoxr_auto_rotate')?.checked = defaultSettings.autoRotate;
                document.getElementById('_expoxr_auto_rotate_delay')?.value = defaultSettings.autoRotateDelay;
                document.getElementById('_expoxr_rotation_per_second')?.value = defaultSettings.rotationPerSecond;
                break;
                
            case 'interaction':
                document.getElementById('_expoxr_touch_action')?.value = defaultSettings.touchAction;
                break;
                
            case 'rendering':
                document.getElementById('_expoxr_background_color')?.value = defaultSettings.backgroundColor;
                document.getElementById('_expoxr_exposure')?.value = defaultSettings.exposure;
                document.getElementById('_expoxr_tone_mapping')?.value = defaultSettings.toneMapping;
                break;
                
            case 'annotations':
                // Clear annotation fields - this depends on your annotation structure
                document.querySelectorAll('.expoxr-annotation-item').forEach(item => {
                    if (item.parentNode) {
                        item.parentNode.removeChild(item);
                    }
                });
                break;
        }
    }
    
    // Check if we're on a model edit page
    if (document.querySelector('.expoxr-model-edit-interface') || 
        document.querySelector('#expoxr-preview-model')) {
        // Initialize reset buttons
        initResetButtons();
        
        // Listen for the event that indicates the model editor has loaded
        document.addEventListener('expoxr-editor-loaded', function() {
            initResetButtons();
        });
    }
});