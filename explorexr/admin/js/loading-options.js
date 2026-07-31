/**
 * ExploreXR Loading Options JavaScript
 * 
 * Handles the loading UI for 3D models.
 */
(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initLoadingOptions();
        initAdminPreview();
        initColorPickers();
    });

    /**
     * Replace the plain hex-color text inputs with the WordPress color picker widget.
     */
    function initColorPickers() {
        if (!$.fn.wpColorPicker) {
            return;
        }

        $('.explorexr-color-field').wpColorPicker({
            change: function(event, ui) {
                updateResultTextContrast($(this), ui.color.toString());
            },
            clear: function() {
                const $input = $(this);
                window.setTimeout(function() {
                    updateResultTextContrast($input, '');
                }, 0);
            }
        });

        // Sync the label's contrast color to each field's saved value on load
        // (the 'change' callback above only fires on user interaction).
        $('.explorexr-color-field').each(function() {
            const $input = $(this);
            updateResultTextContrast($input, $input.val());
        });
    }

    /**
     * Set the color-picker button's "Select Color" label to whichever of
     * black/white is more readable against the currently chosen swatch color.
     */
    function updateResultTextContrast($input, color) {
        const $container = $input.closest('.wp-picker-container');
        const $button = $container.find('.wp-color-result');
        const $text = $container.find('.wp-color-result-text');
        const effectiveColor = color || $input.attr('data-default-color') || '';
        if (!$button.length || !$text.length) {
            return;
        }
        if (!effectiveColor) {
            $button.css('background-color', '');
            $text.css('color', '');
            return;
        }
        $button.css('background-color', effectiveColor);
        $text.css('color', getContrastTextColor(effectiveColor));
    }

    /**
     * Relative-luminance (YIQ) based black/white pick for a hex color string.
     */
    function getContrastTextColor(hex) {
        hex = String(hex).replace('#', '');
        if (hex.length === 3) {
            hex = hex.split('').map(function(c) { return c + c; }).join('');
        }
        if (!/^[0-9a-fA-F]{6}$/.test(hex)) {
            return '';
        }
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        return luminance > 0.6 ? '#000000' : '#ffffff';
    }

    /**
     * Initialize loading options in admin preview
     */
    function initAdminPreview() {
        // Update preview when loading type changes
        $('select[name="explorexr_loading_display"]').on('change', function() {
            const loadingType = $(this).val();
            const previewContainer = $('.explorexr-loading-preview');
            
            previewContainer.attr('data-loading-type', loadingType);
            
            // Show/hide elements based on loading type
            if (loadingType === 'bar') {
                previewContainer.find('.explorexr-loading-progress-bar').show();
                previewContainer.find('.explorexr-loading-percentage').hide();
            } else if (loadingType === 'percentage') {
                previewContainer.find('.explorexr-loading-progress-bar').hide();
                previewContainer.find('.explorexr-loading-percentage').show();
            } else {
                previewContainer.find('.explorexr-loading-progress-bar').show();
                previewContainer.find('.explorexr-loading-percentage').show();
            }
        });
    }

    /**
     * Initialize loading options for model viewers
     */
    function initLoadingOptions() {
        // Find all model viewers in the page
        const modelViewers = document.querySelectorAll('model-viewer.explorexr-model');
        
        modelViewers.forEach(function(modelViewer) {
            // Get loading options from data attributes
            const loadingType = modelViewer.dataset.loadingType || 'both';
            const loadingColor = modelViewer.dataset.loadingColor || '#1e88e5';
            
            // Create loading container
            const loadingContainer = document.createElement('div');
            loadingContainer.className = 'explorexr-loading-container';
            
            // Create progress bar
            if (loadingType === 'bar' || loadingType === 'both') {
                const progressBar = document.createElement('div');
                progressBar.className = 'explorexr-loading-progress-bar';
                
                const progressIndicator = document.createElement('div');
                progressIndicator.className = 'explorexr-loading-progress';
                progressIndicator.style.backgroundColor = loadingColor;
                
                progressBar.appendChild(progressIndicator);
                loadingContainer.appendChild(progressBar);
            }
            
            // Create percentage indicator
            if (loadingType === 'percentage' || loadingType === 'both') {
                const percentageIndicator = document.createElement('div');
                percentageIndicator.className = 'explorexr-loading-percentage';
                percentageIndicator.textContent = '0%';
                percentageIndicator.style.color = loadingColor;
                
                loadingContainer.appendChild(percentageIndicator);
            }
            
            // Insert loading container before model viewer
            modelViewer.parentNode.insertBefore(loadingContainer, modelViewer);
            
            // Track loading progress
            modelViewer.addEventListener('progress', function(event) {
                const progress = event.detail.totalProgress * 100;
                const progressPercent = Math.floor(progress);
                
                // Update progress bar if it exists
                const progressBar = loadingContainer.querySelector('.explorexr-loading-progress');
                if (progressBar) {
                    progressBar.style.width = progressPercent + '%';
                }
                
                // Update percentage if it exists
                const percentageIndicator = loadingContainer.querySelector('.explorexr-loading-percentage');
                if (percentageIndicator) {
                    percentageIndicator.textContent = progressPercent + '%';
                }
                
                // Hide loading container when loaded
                if (progressPercent >= 100) {
                    setTimeout(function() {
                        loadingContainer.style.opacity = '0';
                        setTimeout(function() {
                            loadingContainer.remove();
                        }, 500);
                    }, 500);
                }
            });
        });
    }

})(jQuery);
