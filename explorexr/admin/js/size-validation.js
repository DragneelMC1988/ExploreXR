/**
 * ExploreXR Size Validation (Client-Side)
 * 
 * Provides real-time validation feedback for width/height inputs.
 * Prevents saving invalid size combinations that would make models invisible.
 * 
 * @package ExploreXR_Premium
 */
(function($) {
    'use strict';
    
    const ExploreXR_SizeValidator = {

        validUnits: ['px', 'em', 'rem', 'vw', 'vh', 'dvw', 'dvh'],
        absoluteUnits: ['px', 'em', 'rem', 'vw', 'vh', 'dvw', 'dvh'],
        
        /**
         * Validate a dimension value
         * @param {string} value - The dimension value (e.g., '500px', '100vw')
         * @returns {object} {valid: boolean, unit: string, error: string}
         */
        validateDimension: function(value) {
            if (!value || value.trim() === '') {
                return {
                    valid: false,
                    unit: '',
                    error: 'Value cannot be empty'
                };
            }
            
            const match = value.trim().match(/^(\d+(?:\.\d+)?)(px|em|rem|vw|vh|dvw|dvh)$/i);
            if (!match) {
                return {
                    valid: false,
                    unit: '',
                    error: 'Invalid format. Use: 500px, 50vw, 90vh, 100dvw, etc. Percentage (%) is not allowed.'
                };
            }
            
            const numeric = parseFloat(match[1]);
            const unit = match[2].toLowerCase();
            
            if (numeric <= 0) {
                return {
                    valid: false,
                    unit: unit,
                    error: 'Value must be greater than 0'
                };
            }
            
            if (unit === 'px' && numeric > 5000) {
                return {
                    valid: true,
                    unit: unit,
                    warning: 'Very large value (may affect performance)'
                };
            }
            
            return {
                valid: true,
                unit: unit,
                error: ''
            };
        },
        
        /**
         * Validate width + height combination
         *
         * @param {string} width - Width value
         * @param {string} height - Height value
         * @returns {object} {valid: boolean, error: string}
         */
        validatePair: function(width, height) {
            const widthResult = this.validateDimension(width);
            const heightResult = this.validateDimension(height);

            if (!widthResult.valid) {
                return {
                    valid: false,
                    error: 'Width: ' + widthResult.error
                };
            }

            if (!heightResult.valid) {
                return {
                    valid: false,
                    error: 'Height: ' + heightResult.error
                };
            }

            return {
                valid: true,
                error: ''
            };
        },
        
        /**
         * Show validation error next to input
         * @param {jQuery} $input - Input element
         * @param {string} message - Error message
         */
        showError: function($input, message) {
            this.clearError($input);
            
            const errorHtml = '<div class="explorexr-size-error" style="color: #dc3232; font-size: 12px; margin-top: 4px; display: flex; align-items: center; gap: 4px;">' +
                '<span class="dashicons dashicons-warning" style="font-size: 14px; margin-top: 2px;"></span> ' +
                '<span>' + message + '</span>' +
                '</div>';
            
            $input.closest('.explorexr-form-row').append(errorHtml);
            $input.css('border-color', '#dc3232');
        },
        
        /**
         * Show validation warning
         * @param {jQuery} $input - Input element
         * @param {string} message - Warning message
         */
        showWarning: function($input, message) {
            this.clearError($input);
            
            const warningHtml = '<div class="explorexr-size-error" style="color: #dba617; font-size: 12px; margin-top: 4px; display: flex; align-items: center; gap: 4px;">' +
                '<span class="dashicons dashicons-info" style="font-size: 14px; margin-top: 2px;"></span> ' +
                '<span>' + message + '</span>' +
                '</div>';
            
            $input.closest('.explorexr-form-row').append(warningHtml);
            $input.css('border-color', '#dba617');
        },
        
        /**
         * Clear validation error/warning
         * @param {jQuery} $input - Input element
         */
        clearError: function($input) {
            $input.closest('.explorexr-form-row').find('.explorexr-size-error').remove();
            $input.css('border-color', '');
        },
        
        /**
         * Validate a device's width/height inputs
         * @param {string} devicePrefix - Device prefix ('', 'tablet_', 'mobile_')
         */
        validateDevice: function(devicePrefix) {
            const widthId = '#' + devicePrefix + 'viewer_width';
            const heightId = '#' + devicePrefix + 'viewer_height';
            
            const $width = $(widthId);
            const $height = $(heightId);
            
            if (!$width.length || !$height.length) {
                return true; // Device inputs not present, skip validation
            }
            
            const widthVal = $width.val();
            const heightVal = $height.val();
            
            // Skip validation if both are empty (optional fields for tablet/mobile)
            if ((!widthVal || widthVal === '') && (!heightVal || heightVal === '')) {
                this.clearError($width);
                this.clearError($height);
                return true;
            }
            
            // If only one is filled, validate individually
            if (widthVal && !heightVal) {
                const result = this.validateDimension(widthVal);
                if (result.valid) {
                    this.clearError($width);
                    if (result.warning) {
                        this.showWarning($width, result.warning);
                    }
                    return true;
                } else {
                    this.showError($width, result.error);
                    return false;
                }
            }
            
            if (heightVal && !widthVal) {
                const result = this.validateDimension(heightVal);
                if (result.valid) {
                    this.clearError($height);
                    if (result.warning) {
                        this.showWarning($height, result.warning);
                    }
                    return true;
                } else {
                    this.showError($height, result.error);
                    return false;
                }
            }
            
            // Both filled, validate pair
            const pairResult = this.validatePair(widthVal, heightVal);
            
            if (pairResult.valid) {
                this.clearError($width);
                this.clearError($height);
                return true;
            } else {
                // Show error on height field (since it's usually the percentage issue)
                this.clearError($width);
                this.showError($height, pairResult.error);
                return false;
            }
        }
    };
    
    // Initialize validation on page load
    $(document).ready(function() {
        // Only run on edit/create model pages
        if (!$('#viewer_width, #viewer_height').length) {
            return;
        }
        
        // Validate desktop, tablet, mobile on blur and input (for real-time feedback)
        $('#viewer_width, #viewer_height').on('blur', function() {
            ExploreXR_SizeValidator.validateDevice('');
        }).on('input', debounce(function() {
            ExploreXR_SizeValidator.validateDevice('');
        }, 800));
        
        $('#tablet_viewer_width, #tablet_viewer_height').on('blur', function() {
            ExploreXR_SizeValidator.validateDevice('tablet_');
        }).on('input', debounce(function() {
            ExploreXR_SizeValidator.validateDevice('tablet_');
        }, 800));
        
        $('#mobile_viewer_width, #mobile_viewer_height').on('blur', function() {
            ExploreXR_SizeValidator.validateDevice('mobile_');
        }).on('input', debounce(function() {
            ExploreXR_SizeValidator.validateDevice('mobile_');
        }, 800));
        
        // Clear error on focus (gives user chance to fix)
        $('#viewer_width, #viewer_height, #tablet_viewer_width, #tablet_viewer_height, #mobile_viewer_width, #mobile_viewer_height').on('focus', function() {
            $(this).css('border-color', '');
        });
        
        // Debounce helper (defined at top level for reuse)
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        // Validate on form submit (prevent invalid submission)
        $('form').on('submit', function(e) {
            const desktopValid = ExploreXR_SizeValidator.validateDevice('');
            const tabletValid = ExploreXR_SizeValidator.validateDevice('tablet_');
            const mobileValid = ExploreXR_SizeValidator.validateDevice('mobile_');
            
            if (!desktopValid || !tabletValid || !mobileValid) {
                e.preventDefault();
                
                // Scroll to first error
                const $firstError = $('.explorexr-size-error').first();
                if ($firstError.length) {
                    $('html, body').animate({
                        scrollTop: $firstError.offset().top - 100
                    }, 500);
                }
                
                // Show alert
                alert('Please fix the size validation errors before saving.');
                return false;
            }
        });
    });
    
    // Export to global scope
    window.ExploreXR_SizeValidator = ExploreXR_SizeValidator;
    
})(jQuery);
