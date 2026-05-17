/**
 * ExploreXR AR Add-On - AR Options Handler
 *
 * Handles the AR options functionality in the AR options metabox
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        const $arEnabledToggle = $('#explorexr_premium_ar_enabled');
        const $legacyArEnabledToggle = $('input[name="explorexr_ar_enabled"]');
        const $iconEnabledToggle = $('#explorexr_premium_ar_button_icon_enabled');

        function isArEnabled() {
            if ($arEnabledToggle.length) {
                return $arEnabledToggle.is(':checked');
            }
            return $legacyArEnabledToggle.is(':checked');
        }

        function getFieldValue(primarySelector, fallbackSelector) {
            const $primary = $(primarySelector);
            if ($primary.length) {
                return $primary.val();
            }
            const $fallback = $(fallbackSelector);
            return $fallback.length ? $fallback.val() : '';
        }

        function getArScaleValue() {
            const $allowScaling = $('#explorexr_premium_ar_allow_scaling');
            if ($allowScaling.length) {
                return $allowScaling.is(':checked') ? 'auto' : 'fixed';
            }
            return getFieldValue('#explorexr_premium_ar_scale', '#explorexr_ar_scale');
        }

        function isIconEnabled() {
            if ($iconEnabledToggle.length) {
                return $iconEnabledToggle.is(':checked');
            }
            return true;
        }

        // Toggle AR settings based on checkbox state
        function syncArSettingsVisibility() {
            if (isArEnabled()) {
                $('#ar-settings-container').slideDown(300);
            } else {
                $('#ar-settings-container').slideUp(300);
            }
        }
        $arEnabledToggle.on('change', syncArSettingsVisibility);
        $legacyArEnabledToggle.on('change', syncArSettingsVisibility);
        syncArSettingsVisibility();
        
        // Media Library selection for AR button image
        $('#explorexr-premium-select-ar-button').on('click', function(e) {
            e.preventDefault();
            
            // If the wp.media API is available
            if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
                const frame = wp.media({
                    title: 'Select or Upload AR Button Image',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                frame.on('select', function() {
                    const attachment = frame.state().get('selection').first().toJSON();
                    const $buttonImageField = $('#explorexr_premium_ar_button_image').length
                        ? $('#explorexr_premium_ar_button_image')
                        : $('#explorexr_ar_button_image');
                    $buttonImageField.val(attachment.url);
                    
                    // Update preview
                    const previewDiv = $('#ar-button-preview');
                    previewDiv.show().html(`<img src="${attachment.url}" alt="AR Button" style="max-height: 50px; max-width: 200px;">`);
                    
                    // Show remove button
                    if ($('#explorexr-premium-remove-ar-button').length === 0) {
                        $('<button type="button" class="button" id="explorexr-premium-remove-ar-button">Remove</button>')
                            .insertAfter('#explorexr-premium-select-ar-button');
                        
                        // Bind remove event
                        $('#explorexr-premium-remove-ar-button').on('click', removeARButtonImage);
                    }
                });
                
                frame.open();
            } else {
                alert('The WordPress Media Library is not available. Please ensure you are using this in the WordPress admin area.');
            }
        });
        
        // Remove AR button image
        $('#explorexr-premium-remove-ar-button').on('click', removeARButtonImage);
        
        function removeARButtonImage() {
            $('#explorexr_ar_button_image').val('');
            $('#ar-button-preview').hide().html('');
            $('#explorexr-premium-remove-ar-button').remove();
        }
        
        // USDZ file upload button
        $('#explorexr_upload_usdz_btn').on('click', function() {
            $('#explorexr_usdz_upload').toggle();
            
            if ($(this).text() === 'Upload USDZ') {
                $(this).text('Cancel Upload');
            } else {
                $(this).text('Upload USDZ');
            }
        });
        
        // USDZ file validation
        $('#explorexr_usdz_file').on('change', function() {
            const file = this.files[0];
            if (file) {
                // Check file extension
                const fileName = file.name;
                const fileExt = fileName.split('.').pop().toLowerCase();
                
                if (fileExt !== 'usdz') {
                    alert('Please upload a valid USDZ file.');
                    this.value = '';
                    return false;
                }
                
                // Check file size (max 50MB)
                if (file.size > 50 * 1024 * 1024) {
                    alert('File size too large. Maximum file size is 50MB.');
                    this.value = '';
                    return false;
                }
                
                // Show file name
                $('<div class="usdz-file-selected" style="margin-top: 10px;"></div>')
                    .text(`Selected file: ${fileName}`)
                    .insertAfter('#explorexr_usdz_file');
            }
        });
        
        // Handle AR modes checkboxes
        $('.explorexr-premium-checkbox-group input[type="checkbox"]').on('change', function() {
            // Ensure at least one AR mode is selected
            if ($('.explorexr-premium-checkbox-group input[type="checkbox"]:checked').length === 0) {
                $(this).prop('checked', true);
                alert('At least one AR mode must be selected.');
            }
        });
        
        // Preview AR settings on model-viewer if available
        if (document.querySelector('model-viewer')) {
            function updateARPreview() {
                const modelViewer = document.getElementById('main-preview-model-viewer') || document.querySelector('model-viewer');
                const attrs = {};
                if (!modelViewer) {
                    return;
                }
                
                // Check if AR is enabled
                if (isArEnabled()) {
                    // Set AR attribute
                    modelViewer.setAttribute('ar', '');
                    attrs['ar'] = 'true';
                    
                    // Set AR modes
                    const arModes = [];
                    $('.explorexr-premium-checkbox-group input[type="checkbox"]:checked').each(function() {
                        arModes.push($(this).val());
                    });
                    
                    if (arModes.length > 0) {
                        modelViewer.setAttribute('ar-modes', arModes.join(' '));
                        attrs['ar-modes'] = arModes.join(' ');
                    }
                    
                    // Set AR scale
                    const arScale = getArScaleValue();
                    if (arScale) {
                        modelViewer.setAttribute('ar-scale', arScale);
                        attrs['ar-scale'] = arScale;
                    } else {
                        modelViewer.removeAttribute('ar-scale');
                        attrs['ar-scale'] = '';
                    }
                    
                    // Set AR placement
                    const arPlacement = getFieldValue('#explorexr_premium_ar_placement', '#explorexr_ar_placement');
                    modelViewer.setAttribute('ar-placement', arPlacement);
                    attrs['ar-placement'] = arPlacement;
                    
                    // Set USDZ model if available
                    const usdzModel = getFieldValue('#explorexr_premium_ar_usdz_model', '#explorexr_ar_usdz_model');
                    if (usdzModel) {
                        modelViewer.setAttribute('ios-src', usdzModel);
                        attrs['ios-src'] = usdzModel;
                    } else {
                        modelViewer.removeAttribute('ios-src');
                        attrs['ios-src'] = '';
                    }
                    
                    // Set environment image if available
                    const xrEnvironment = getFieldValue('#explorexr_premium_ar_xr_environment', '#explorexr_ar_xr_environment');
                    if (xrEnvironment) {
                        modelViewer.setAttribute('environment-image', xrEnvironment);
                        attrs['environment-image'] = xrEnvironment;
                    }
                    
                    // Create custom AR button if needed
                    updateARButton(modelViewer);
                } else {
                    // Remove AR attribute
                    modelViewer.removeAttribute('ar');
                    modelViewer.removeAttribute('ar-modes');
                    modelViewer.removeAttribute('ar-scale');
                    modelViewer.removeAttribute('ar-placement');
                    modelViewer.removeAttribute('ios-src');
                    attrs['ar'] = '';
                    attrs['ar-modes'] = '';
                    attrs['ar-scale'] = '';
                    attrs['ar-placement'] = '';
                    attrs['ios-src'] = '';
                    
                    // Remove custom AR button
                    const arButton = modelViewer.querySelector('button[slot="ar-button"]');
                    if (arButton) {
                        arButton.remove();
                    }
                }

                document.dispatchEvent(new CustomEvent('explorexr:addon:update', { detail: { attributes: attrs } }));
                if (typeof window.explorexrUpdateMainPreview === 'function') {
                    window.explorexrUpdateMainPreview(attrs);
                }
            }
            
            function updateARButton(modelViewer) {
                // Check if custom button is needed
                const buttonText = getFieldValue('#explorexr_premium_ar_button_text', '#explorexr_ar_button_text');
                
                // CRITICAL: No button if text is empty
                if (!buttonText || buttonText === '') {
                    const existingButton = modelViewer.querySelector('button[slot="ar-button"]');
                    if (existingButton) {
                        existingButton.remove();
                    }
                    return;
                }
                
                const buttonImage = getFieldValue('#explorexr_premium_ar_button_image', '#explorexr_ar_button_image');
                const bgColor = $('#explorexr_premium_ar_button_bg_color').val();
                const textColor = $('#explorexr_premium_ar_button_text_color').val();
                const borderColor = $('#explorexr_premium_ar_button_border_color').val();
                const size = $('#explorexr_premium_ar_button_size').val();
                const radius = $('#explorexr_premium_ar_button_border_radius').val();
                const icon = getFieldValue('#explorexr_premium_ar_button_icon', '#explorexr_ar_button_icon');
                const iconPos = getFieldValue('#explorexr_premium_ar_button_icon_position', '#explorexr_ar_button_icon_position') || 'left';
                const buttonPosition = $('#explorexr_premium_ar_button_position').val() || 'bottom-center';
                const iconEnabled = isIconEnabled();

                const positionMap = {
                    'bottom-center': { bottom: '16px', left: '50%', transform: 'translateX(-50%)' },
                    'bottom-left': { bottom: '16px', left: '16px' },
                    'bottom-right': { bottom: '16px', right: '16px' },
                    'top-left': { top: '16px', left: '16px' },
                    'top-right': { top: '16px', right: '16px' }
                };
                
                // Remove existing custom button if any
                const existingButton = modelViewer.querySelector('button[slot="ar-button"]');
                if (existingButton) {
                    existingButton.remove();
                }
                
                // Create new custom button
                const button = document.createElement('button');
                button.setAttribute('slot', 'ar-button');
                button.className = 'explorexr-premium-ar-button';
                
                // Use CSS custom properties for styling
                if (bgColor) button.style.setProperty('--ar-button-bg', bgColor);
                if (textColor) button.style.setProperty('--ar-button-color', textColor);
                if (borderColor) button.style.setProperty('--ar-button-border', '1px solid ' + borderColor);
                if (radius) button.style.setProperty('--ar-button-radius', radius + 'px');
                
                // Size-based padding/font-size via CSS custom properties
                if (size === 'large') {
                    button.style.setProperty('--ar-button-padding', '12px 18px');
                    button.style.setProperty('--ar-button-font-size', '16px');
                } else if (size === 'small') {
                    button.style.setProperty('--ar-button-padding', '6px 10px');
                    button.style.setProperty('--ar-button-font-size', '13px');
                } else if (size === 'medium') {
                    button.style.setProperty('--ar-button-padding', '10px 14px');
                    button.style.setProperty('--ar-button-font-size', '14px');
                }
                
                button.style.position = 'absolute';
                button.style.zIndex = '1000';

                // Position
                const posStyles = positionMap[buttonPosition] || positionMap['bottom-center'];
                Object.entries(posStyles).forEach(([k, v]) => button.style[k] = v);

                // Icon/text
                if (buttonImage) {
                    const img = document.createElement('img');
                    img.src = buttonImage;
                    img.alt = buttonText;
                    img.style.maxHeight = '50px';
                    img.style.maxWidth = '150px';
                    img.style.borderRadius = `${radius}px`;
                    button.appendChild(img);
                } else {
                    const textSpan = document.createElement('span');
                    textSpan.className = 'explorexr-premium-ar-button-text';
                    textSpan.textContent = buttonText;

                    if (iconEnabled && icon) {
                        const iconWrapper = document.createElement('span');
                        iconWrapper.className = 'explorexr-premium-ar-icon';
                        if (icon.includes('<svg') || icon.includes('<path')) {
                            iconWrapper.innerHTML = icon;
                        } else if (/^https?:\/\//i.test(icon)) {
                            const img = document.createElement('img');
                            img.src = icon;
                            img.alt = '';
                            img.style.width = '20px';
                            img.style.height = '20px';
                            iconWrapper.appendChild(img);
                        } else {
                            iconWrapper.className = `explorexr-premium-ar-icon ${icon}`;
                        }

                        if (iconPos === 'left') {
                            button.appendChild(iconWrapper);
                        }
                        button.appendChild(textSpan);
                        if (iconPos === 'right') {
                            button.appendChild(iconWrapper);
                        }
                    } else {
                        button.appendChild(textSpan);
                    }
                }

                modelViewer.appendChild(button);
            }
           
            // Live preview on field changes
            $('#ar-settings-container').on('change input', 'input, select', function() {
                updateARPreview();
            });
            $arEnabledToggle.on('change', updateARPreview);
            $legacyArEnabledToggle.on('change', updateARPreview);
            updateARPreview();
        }
    });
    
})(jQuery);
