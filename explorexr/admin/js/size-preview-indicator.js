/**
 * ExploreXR Size Preview Indicator
 * 
 * Provides a visual scale indicator showing the selected display size
 * relative to common screen dimensions.
 * 
 * @package ExploreXR_Premium
 */
(function($) {
    'use strict';
    
    const ExploreXR_SizePreview = {
        
        /**
         * Reference dimensions for scale comparison
         */
        referenceDimensions: {
            desktop: { width: 1920, height: 1080, label: 'Desktop (1920×1080)' },
            laptop: { width: 1366, height: 768, label: 'Laptop (1366×768)' },
            tablet: { width: 768, height: 1024, label: 'Tablet (768×1024)' },
            mobile: { width: 375, height: 667, label: 'Mobile (375×667)' }
        },
        
        /**
         * Parse CSS dimension to pixels
         * @param {string} value - CSS value (e.g., '500px', '50%', '50vw')
         * @param {string} dimension - 'width' or 'height'
         * @param {string} device - Device type for viewport reference
         * @returns {number} Approximate pixel value
         */
        parseToPixels: function(value, dimension, device) {
            if (!value) return 0;
            
            const match = value.match(/^(\d+(?:\.\d+)?)(px|vw|vh|dvw|dvh|em|rem)$/i);
            if (!match) return 0;

            const numeric = parseFloat(match[1]);
            const unit = match[2].toLowerCase();
            const ref = this.referenceDimensions[device] || this.referenceDimensions.desktop;

            switch(unit) {
                case 'px':
                    return numeric;
                case 'vw':
                case 'dvw':
                    return (numeric / 100) * (ref.width || 1920);
                case 'vh':
                case 'dvh':
                    return (numeric / 100) * (ref.height || 1080);
                case 'em':
                case 'rem':
                    // Approximate: 1em ≈ 16px
                    return numeric * 16;
                default:
                    return 0;
            }
        },
        
        /**
         * Create or update size indicator
         * @param {string} devicePrefix - Device prefix ('', 'tablet_', 'mobile_')
         * @param {string} deviceType - Device type for reference ('desktop', 'tablet', 'mobile')
         */
        updateIndicator: function(devicePrefix, deviceType) {
            const widthId = '#' + devicePrefix + 'viewer_width';
            const heightId = '#' + devicePrefix + 'viewer_height';
            
            const $width = $(widthId);
            const $height = $(heightId);
            
            if (!$width.length || !$height.length) {
                return;
            }
            
            const widthVal = $width.val();
            const heightVal = $height.val();
            
            if (!widthVal || !heightVal) {
                this.removeIndicator(devicePrefix);
                return;
            }
            
            // Parse to approximate pixels
            const widthPx = this.parseToPixels(widthVal, 'width', deviceType);
            const heightPx = this.parseToPixels(heightVal, 'height', deviceType);
            
            if (widthPx === 0 || heightPx === 0) {
                this.removeIndicator(devicePrefix);
                return;
            }
            
            // Calculate scale for visualization (max 200px wide)
            const maxDisplay = 200;
            const scale = Math.min(maxDisplay / widthPx, maxDisplay / heightPx);
            const displayWidth = Math.round(widthPx * scale);
            const displayHeight = Math.round(heightPx * scale);
            
            // Calculate aspect ratio
            const aspectRatio = (widthPx / heightPx).toFixed(2);
            const aspectLabel = this.getAspectRatioLabel(aspectRatio);
            
            // Create or update indicator
            const containerId = devicePrefix ? devicePrefix + 'size-indicator' : 'desktop-size-indicator';
            let $indicator = $('#' + containerId);
            
            if (!$indicator.length) {
                $indicator = $('<div/>', {
                    id: containerId,
                    class: 'explorexr-size-indicator',
                    css: {
                        'margin-top': '15px',
                        'padding': '12px',
                        'background': '#f6f7f7',
                        'border': '1px solid #dcdcde',
                        'border-radius': '4px'
                    }
                });
                
                // Insert after height input
                $height.closest('.explorexr-form-row').after($indicator);
            }
            
            // Build indicator HTML
            const indicatorHTML = `
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div style="flex-shrink: 0;">
                        <div style="width: ${displayWidth}px; height: ${displayHeight}px; background: linear-gradient(135deg, #2AACE2 0%, #1e88e5 100%); border-radius: 2px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: relative; display: flex; align-items: center; justify-content: center;">
                            <span style="color: white; font-size: 10px; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">PREVIEW</span>
                        </div>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; margin-bottom: 4px; color: #1d2327;">
                            <span class="dashicons dashicons-visibility" style="font-size: 16px; vertical-align: middle; margin-right: 4px;"></span>
                            Preview Size
                        </div>
                        <div style="font-size: 12px; color: #646970; line-height: 1.6;">
                            <div><strong>Dimensions:</strong> ${Math.round(widthPx)} × ${Math.round(heightPx)} pixels</div>
                            <div><strong>Aspect Ratio:</strong> ${aspectRatio}:1 ${aspectLabel}</div>
                            <div style="margin-top: 4px; font-size: 11px; color: #787c82;">
                                <span class="dashicons dashicons-info-outline" style="font-size: 12px; vertical-align: middle;"></span>
                                Approximate rendering based on ${this.referenceDimensions[deviceType].label}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $indicator.html(indicatorHTML);
        },
        
        /**
         * Remove indicator
         * @param {string} devicePrefix - Device prefix
         */
        removeIndicator: function(devicePrefix) {
            const containerId = devicePrefix ? devicePrefix + 'size-indicator' : 'desktop-size-indicator';
            $('#' + containerId).remove();
        },
        
        /**
         * Get common aspect ratio label
         * @param {number} ratio - Aspect ratio
         * @returns {string} Label
         */
        getAspectRatioLabel: function(ratio) {
            const numRatio = parseFloat(ratio);
            
            if (Math.abs(numRatio - 1.0) < 0.05) return '(Square)';
            if (Math.abs(numRatio - 1.33) < 0.05) return '(4:3)';
            if (Math.abs(numRatio - 1.78) < 0.05) return '(16:9)';
            if (Math.abs(numRatio - 1.60) < 0.05) return '(16:10)';
            if (Math.abs(numRatio - 2.35) < 0.05) return '(Cinematic)';
            
            if (numRatio > 1.5) return '(Wide)';
            if (numRatio < 0.8) return '(Tall)';
            
            return '';
        },
        
        /**
         * Initialize indicators for all devices
         */
        init: function() {
            const self = this;
            
            // Debounce helper
            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }
            
            // Desktop indicators
            const updateDesktop = debounce(function() {
                self.updateIndicator('', 'desktop');
            }, 300);
            
            $('#viewer_width, #viewer_height').on('input blur', updateDesktop);
            
            // Tablet indicators
            const updateTablet = debounce(function() {
                self.updateIndicator('tablet_', 'tablet');
            }, 300);
            
            $('#tablet_viewer_width, #tablet_viewer_height').on('input blur', updateTablet);
            
            // Mobile indicators
            const updateMobile = debounce(function() {
                self.updateIndicator('mobile_', 'mobile');
            }, 300);
            
            $('#mobile_viewer_width, #mobile_viewer_height').on('input blur', updateMobile);
            
            // Update on predefined size selection
            $('input[name="explorexr_predefined_size"]').on('change', function() {
                setTimeout(updateDesktop, 100);
            });
            
            // Initial update on page load
            setTimeout(function() {
                updateDesktop();
                updateTablet();
                updateMobile();
            }, 500);
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        // Only run on edit/create model pages
        if ($('#viewer_width, #viewer_height').length) {
            ExploreXR_SizePreview.init();
        }
    });
    
    // Export to global scope
    window.ExploreXR_SizePreview = ExploreXR_SizePreview;
    
})(jQuery);
