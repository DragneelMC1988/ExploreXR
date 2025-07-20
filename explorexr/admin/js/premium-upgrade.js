/**
 * Premium Upgrade JavaScript for ExploreXR
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Initialize premium upgrade features
        initPremiumUpgrade();
        
        // Handle premium feature clicks
        handlePremiumFeatureClicks();
        
        // Add premium upgrade prompts
        addPremiumUpgradePrompts();
        
        // Handle notice dismissal
        handleNoticeDismissal();
    });
    
    /**
     * Initialize premium upgrade features
     */
    function initPremiumUpgrade() {
        // Add premium badges to disabled features
        $('.premium-disabled').each(function() {
            if (!$(this).find('.premium-badge').length) {
                $(this).prepend('<span class="premium-badge">Premium</span>');
            }
        });
        
        // Add hover effects to premium features
        $('.explorexr-premium-card').hover(
            function() {
                $(this).addClass('premium-highlight');
            },
            function() {
                $(this).removeClass('premium-highlight');
            }
        );
    }
    
    /**
     * Handle clicks on premium features
     */
    function handlePremiumFeatureClicks() {
        // Intercept clicks on premium features
        $(document).on('click', '.premium-disabled, .has-premium-feature', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            showPremiumUpgradeModal($(this).data('feature') || 'general');
            return false;
        });
        
        // Handle addon card clicks in edit model page
        $(document).on('click', '.explorexr-free-version .addon-card', function(e) {
            e.preventDefault();
            
            var addonName = $(this).find('h3').text() || 'Premium Addon';
            showPremiumUpgradeModal('addon', addonName);
            return false;
        });
    }
    
    /**
     * Add premium upgrade prompts to various elements
     */
    function addPremiumUpgradePrompts() {
        // Add upgrade prompts to form inputs for premium features
        $('input[data-premium="true"], select[data-premium="true"]').each(function() {
            var $input = $(this);
            var $wrapper = $('<div class="premium-input-wrapper"></div>');
            
            $input.wrap($wrapper);
            $input.after('<div class="premium-input-overlay"><span class="premium-lock">🔒</span> Premium Feature</div>');
            $input.prop('disabled', true);
        });
        
        // Add tooltips to premium features
        $('.has-premium-feature').each(function() {
            var feature = $(this).data('feature') || 'feature';
            $(this).attr('title', 'This ' + feature + ' is available in ExploreXR Premium');
        });
    }
    
    /**
     * Show premium upgrade modal
     */
    function showPremiumUpgradeModal(feature, featureName) {
        feature = feature || 'general';
        featureName = featureName || 'feature';
        
        var modalContent = getPremiumModalContent(feature, featureName);
        
        // Remove existing modal
        $('#explorexr-premium-modal').remove();
        
        // Create modal
        var modal = $('<div id="explorexr-premium-modal" class="explorexr-modal-overlay">' + modalContent + '</div>');
        
        $('body').append(modal);
        
        // Show modal with animation
        setTimeout(function() {
            modal.addClass('show');
        }, 10);
        
        // Handle modal interactions
        modal.on('click', '.modal-close, .explorexr-modal-overlay', function(e) {
            if (e.target === this) {
                closePremiumModal();
            }
        });
        
        modal.on('click', '.premium-upgrade-cta', function() {
            // Track upgrade button click
            if (typeof gtag !== 'undefined') {
                gtag('event', 'premium_upgrade_click', {
                    'feature': feature,
                    'source': 'modal'
                });
            }
        });
    }
    
    /**
     * Get premium modal content based on feature
     */
    function getPremiumModalContent(feature, featureName) {
        var features = {
            'ar': {
                icon: '📱',
                title: 'AR Support',
                description: 'Enable Augmented Reality experiences for your 3D models',
                benefits: [
                    'AR Quick Look for iOS devices',
                    'AR Core support for Android',
                    'WebXR compatibility',
                    'One-click AR activation'
                ]
            },
            'animations': {
                icon: '🎬',
                title: 'Animations',
                description: 'Bring your 3D models to life with smooth animations',
                benefits: [
                    'Auto-play animation controls',
                    'Multiple animation support',
                    'Loop and speed settings',
                    'Interactive play buttons'
                ]
            },
            'annotations': {
                icon: '💬',
                title: 'Annotations',
                description: 'Add interactive hotspots and information to your models',
                benefits: [
                    'Custom hotspot markers',
                    'Rich text content support',
                    'Smart positioning system',
                    'Mobile-friendly interactions'
                ]
            },
            'camera': {
                icon: '📷',
                title: 'Camera Controls',
                description: 'Advanced camera controls for better user experience',
                benefits: [
                    'Orbit and pan controls',
                    'Zoom limits and settings',
                    'Auto-rotate options',
                    'Touch gesture controls'
                ]
            },
            'materials': {
                icon: '🎨',
                title: 'Materials Editor',
                description: 'Customize materials, textures, and colors in real-time',
                benefits: [
                    'Live material editing',
                    'Texture swapping',
                    'Color variations',
                    'PBR material support'
                ]
            },
            'woocommerce': {
                icon: '🛒',
                title: 'WooCommerce Integration',
                description: 'Advanced e-commerce features for product displays',
                benefits: [
                    'Product variant selection',
                    'Shopping cart integration',
                    'Checkout flow optimization',
                    'Inventory management'
                ]
            },
            'addon': {
                icon: '🧩',
                title: featureName,
                description: 'This addon provides advanced functionality for your 3D models',
                benefits: [
                    'Professional-grade features',
                    'Easy activation and setup',
                    'Regular updates and support',
                    'Compatible with all themes'
                ]
            },
            'general': {
                icon: '🚀',
                title: 'Premium Features',
                description: 'Unlock the full potential of ExploreXR',
                benefits: [
                    'All premium addons included',
                    'Priority email support',
                    'Regular feature updates',
                    '30-day money-back guarantee'
                ]
            }
        };
        
        var currentFeature = features[feature] || features['general'];
        
        return `
            <div class="explorexr-modal-content">
                <div class="modal-header">
                    <div class="feature-icon">${currentFeature.icon}</div>
                    <h2>${currentFeature.title}</h2>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="feature-description">${currentFeature.description}</p>
                    <div class="feature-benefits">
                        <h4>What you'll get:</h4>
                        <ul>
                            ${currentFeature.benefits.map(benefit => `<li>${benefit}</li>`).join('')}
                        </ul>
                    </div>
                    <div class="pricing-preview">
                        <div class="pricing-item">
                            <span class="price">$29</span>
                            <span class="period">/month</span>
                            <span class="plan">Pro Plan</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="${explorexr_admin.premium_upgrade_url || '#'}" class="premium-upgrade-cta" target="_blank">
                        Upgrade to Premium
                    </a>
                    <p class="guarantee">30-day money-back guarantee</p>
                </div>
            </div>
        `;
    }
    
    /**
     * Close premium modal
     */
    function closePremiumModal() {
        $('#explorexr-premium-modal').removeClass('show');
        setTimeout(function() {
            $('#explorexr-premium-modal').remove();
        }, 300);
    }
    
    /**
     * Handle notice dismissal
     */
    function handleNoticeDismissal() {
        $(document).on('click', '.explorexr-premium-notice .notice-dismiss', function() {
            $.post(explorexr_premium.ajax_url, {
                action: 'explorexr_dismiss_premium_notice',
                nonce: explorexr_premium.dismiss_nonce
            });
        });
    }
    
    /**
     * Add premium tooltips
     */
    function addPremiumTooltips() {
        // Initialize tooltips for premium features
        $('.has-premium-feature').each(function() {
            var $element = $(this);
            var feature = $element.data('feature');
            var tooltip = $('<div class="premium-tooltip">Premium Feature - Click to learn more</div>');
            
            $element.hover(
                function() {
                    $element.append(tooltip);
                    tooltip.fadeIn(200);
                },
                function() {
                    tooltip.fadeOut(200, function() {
                        tooltip.remove();
                    });
                }
            );
        });
    }
    
    /**
     * Track premium feature interactions
     */
    function trackPremiumInteraction(feature, action) {
        // Google Analytics tracking
        if (typeof gtag !== 'undefined') {
            gtag('event', 'premium_interaction', {
                'feature': feature,
                'action': action
            });
        }
        
        // Custom tracking can be added here
        console.log('Premium interaction:', feature, action);
    }
    
    // Initialize tooltips
    addPremiumTooltips();
    
})(jQuery);

// CSS for modal and premium features
jQuery(document).ready(function($) {
    if (!$('#explorexr-premium-styles').length) {
        $('head').append(`
            <style id="explorexr-premium-styles">
                .explorexr-modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.7);
                    z-index: 999999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                
                .explorexr-modal-overlay.show {
                    opacity: 1;
                }
                
                .explorexr-modal-content {
                    background: white;
                    border-radius: 12px;
                    max-width: 500px;
                    width: 90%;
                    max-height: 80vh;
                    overflow-y: auto;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
                    transform: scale(0.9);
                    transition: transform 0.3s ease;
                }
                
                .explorexr-modal-overlay.show .explorexr-modal-content {
                    transform: scale(1);
                }
                
                .modal-header {
                    display: flex;
                    align-items: center;
                    padding: 25px;
                    border-bottom: 1px solid #eee;
                    gap: 15px;
                }
                
                .feature-icon {
                    font-size: 32px;
                }
                
                .modal-header h2 {
                    flex: 1;
                    margin: 0;
                    font-size: 1.5em;
                }
                
                .modal-close {
                    background: none;
                    border: none;
                    font-size: 24px;
                    cursor: pointer;
                    color: #999;
                    padding: 5px;
                }
                
                .modal-body {
                    padding: 25px;
                }
                
                .feature-description {
                    font-size: 16px;
                    color: #666;
                    margin-bottom: 20px;
                    line-height: 1.6;
                }
                
                .feature-benefits h4 {
                    margin: 0 0 10px 0;
                    color: #333;
                }
                
                .feature-benefits ul {
                    list-style: none;
                    padding: 0;
                    margin: 0 0 20px 0;
                }
                
                .feature-benefits li {
                    padding: 5px 0;
                    position: relative;
                    padding-left: 25px;
                }
                
                .feature-benefits li::before {
                    content: "✓";
                    position: absolute;
                    left: 0;
                    color: #4CAF50;
                    font-weight: bold;
                }
                
                .pricing-preview {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 20px;
                    border-radius: 8px;
                    text-align: center;
                    margin: 20px 0;
                }
                
                .pricing-item {
                    display: flex;
                    align-items: baseline;
                    justify-content: center;
                    gap: 5px;
                }
                
                .price {
                    font-size: 2em;
                    font-weight: bold;
                }
                
                .period {
                    font-size: 0.9em;
                    opacity: 0.8;
                }
                
                .plan {
                    margin-left: 10px;
                    background: rgba(255,255,255,0.2);
                    padding: 2px 8px;
                    border-radius: 12px;
                    font-size: 0.8em;
                }
                
                .modal-footer {
                    padding: 25px;
                    text-align: center;
                    border-top: 1px solid #eee;
                }
                
                .premium-upgrade-cta {
                    display: inline-block;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 12px 30px;
                    border-radius: 6px;
                    text-decoration: none;
                    font-weight: bold;
                    transition: transform 0.2s;
                    margin-bottom: 10px;
                }
                
                .premium-upgrade-cta:hover {
                    transform: translateY(-2px);
                    text-decoration: none;
                    color: white;
                }
                
                .guarantee {
                    font-size: 12px;
                    color: #666;
                    margin: 10px 0 0 0;
                }
                
                .premium-input-wrapper {
                    position: relative;
                }
                
                .premium-input-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(255,255,255,0.9);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    font-weight: bold;
                    color: #667eea;
                    border-radius: 4px;
                }
                
                .premium-lock {
                    margin-right: 5px;
                }
                
                .premium-tooltip {
                    position: absolute;
                    bottom: 100%;
                    left: 50%;
                    transform: translateX(-50%);
                    background: #333;
                    color: white;
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-size: 12px;
                    white-space: nowrap;
                    z-index: 10;
                    margin-bottom: 5px;
                }
                
                .premium-tooltip::after {
                    content: '';
                    position: absolute;
                    top: 100%;
                    left: 50%;
                    transform: translateX(-50%);
                    border: 5px solid transparent;
                    border-top-color: #333;
                }
            </style>
        `);
    }
});
