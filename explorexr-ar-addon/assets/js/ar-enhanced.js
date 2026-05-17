/**
 * Enhanced AR Addon Integration
 * 
 * This script enhances AR functionality by providing better integration
 * with the addon settings system.
 */

(function() {
    'use strict';
    
    // Store reference to the addon settings
    const addonSettings = window.explorexrARSettings || {};
      /**
     * Initialize enhanced AR functionality
     */
    function initEnhancedAR() {
        document.addEventListener('DOMContentLoaded', () => {
            // Use centralized loader to ensure model-viewer is available
            if (window.loadModelViewer && !window.isModelViewerLoaded()) {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('AR Enhanced: Waiting for model-viewer to load...');
                }
                window.loadModelViewer()
                    .then(function() {
                        if (typeof ExploreXRLogger !== 'undefined') {
                            ExploreXRLogger.log('AR Enhanced: Model viewer loaded, enhancing AR functionality');
                        }
                        enhanceAllModelViewers();
                        observeModelViewerAdditions();
                    })
                    .catch(function(error) {
                        if (typeof ExploreXRLogger !== 'undefined') {
                            ExploreXRLogger.error('AR Enhanced: Failed to load model-viewer:', error);
                        }
                        // Fallback with delay
                        setTimeout(() => {
                            enhanceAllModelViewers();
                            observeModelViewerAdditions();
                        }, 2000);
                    });
            } else if (typeof customElements !== 'undefined' && customElements.get('model-viewer')) {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('AR Enhanced: Model viewer already available');
                }
                enhanceAllModelViewers();
                observeModelViewerAdditions();
            } else {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('AR Enhanced: Waiting for model-viewer to be available...');
                }
                // Fallback with delay
                setTimeout(() => {
                    enhanceAllModelViewers();
                    observeModelViewerAdditions();
                }, 2000);
            }
        });
    }
    
    /**
     * Enhance all model-viewer elements with AR customizations
     */
    function enhanceAllModelViewers() {
        // Find all model viewers with AR enabled
        const modelViewers = document.querySelectorAll('model-viewer[ar]');
        
        modelViewers.forEach(modelViewer => {
            enhanceModelViewerAR(modelViewer);
        });
    }
    
    /**
     * Enhance a specific model-viewer with AR customizations
     * 
     * @param {HTMLElement} modelViewer The model-viewer element
     */
    function enhanceModelViewerAR(modelViewer) {
        // CRITICAL FIX: Wait for model to be ready before checking AR capabilities
        // This is especially important for iOS AR Quick Look
        const applyEnhancements = () => {
            // Apply custom button if specified
            applyCustomARButton(modelViewer);

            // Add custom AR fallback message
            addARFallbackMessage(modelViewer);

            // Setup AR event tracking
            setupAREventTracking(modelViewer);

            // Verify button was created successfully and retry if needed
            setTimeout(() => {
                const button = modelViewer.querySelector('button[slot="ar-button"]');
                if (!button) {
                    if (typeof ExploreXRLogger !== 'undefined') {
                        ExploreXRLogger.warn('ExploreXR AR: Button not found after creation, retrying...');
                    }
                    // Retry button creation
                    applyCustomARButton(modelViewer);
                } else {
                    if (typeof ExploreXRLogger !== 'undefined') {
                        ExploreXRLogger.log('ExploreXR AR: Button successfully created and verified');
                    }
                }
            }, 1000);
        };

        // If model is already loaded, apply immediately
        if (modelViewer.loaded) {
            applyEnhancements();
        } else {
            // Wait for model to load before applying AR enhancements
            modelViewer.addEventListener('load', applyEnhancements, { once: true });

            // Fallback: Apply after delay if load event doesn't fire
            setTimeout(applyEnhancements, 1000);
        }
    }
    
    /**
     * Apply custom AR button styling if specified in attributes
     * 
     * @param {HTMLElement} modelViewer The model-viewer element
     */
    function applyCustomARButton(modelViewer) {
        // Check if model-viewer is fully ready - give more time if document is still loading
        if (document.readyState === 'loading') {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('ExploreXR AR: Document still loading, deferring button creation');
            }
            setTimeout(() => applyCustomARButton(modelViewer), 300);
            return;
        }

        // Check if AR is enabled for this model
        const arDisabled = modelViewer.getAttribute('data-explorexr-premium-ar-disabled');
        if (arDisabled === 'true') {
            return; // Skip if AR is disabled
        }

        // CRITICAL FIX: Check if template already created a button with correct marker
        // If yes, only update styles rather than recreating the entire button
        const existingButton = modelViewer.querySelector('button[slot="ar-button"]');
        const templateButton = existingButton && (
            existingButton.hasAttribute('data-ExploreXR-ar-button') ||
            existingButton.classList.contains('ExploreXR-ar-button')
        );

        const resolveButtonText = () => {
            const dataText = modelViewer.getAttribute('data-ar-button-text');
            if (dataText && dataText.trim() !== '') {
                return dataText;
            }
            const attrText = modelViewer.getAttribute('ar-button-text');
            if (attrText && attrText.trim() !== '') {
                return attrText;
            }
            return '';
        };
        
        if (templateButton) {
            // Template button exists - just update styles if needed, don't recreate
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('AR Enhanced: Template button exists, skipping recreation');
            }
            
            // Optionally enhance the existing button with any missing attributes
            const bgColor = modelViewer.getAttribute('data-ar-button-bg-color');
            const textColor = modelViewer.getAttribute('data-ar-button-text-color');
            const desiredText = resolveButtonText();
            
            if (bgColor && !existingButton.style.backgroundColor) {
                existingButton.style.backgroundColor = bgColor;
            }
            if (textColor && !existingButton.style.color) {
                existingButton.style.color = textColor;
            }
            if (desiredText) {
                const textNode = existingButton.querySelector('.explorexr-premium-ar-button-text');
                if (textNode) {
                    textNode.textContent = desiredText;
                } else {
                    existingButton.textContent = desiredText;
                }
            }
            existingButton.setAttribute('data-ExploreXR-ar-button', 'true');
            
            return; // Don't recreate button
        }
        
        // No template button found - remove any existing custom button and create new one
        if (existingButton) {
            existingButton.remove();
        }
        modelViewer.setAttribute('data-explorexr-premium-ar-custom-button', 'true');
        
        // Create new custom button
        const button = document.createElement('button');
        button.setAttribute('slot', 'ar-button');
        button.className = 'explorexr-premium-ar-button';
        button.setAttribute('data-ExploreXR-ar-button', 'true');
        
        // Get button text from attribute - with fallback default for edge cases
        let buttonText = resolveButtonText();
        if (!buttonText && existingButton) {
            const existingTextNode = existingButton.querySelector('.explorexr-premium-ar-button-text');
            const existingText = existingTextNode ? existingTextNode.textContent : existingButton.textContent;
            if (existingText && existingText.trim() !== '') {
                buttonText = existingText.trim();
            }
        }
        if (!buttonText) {
            buttonText = 'View in AR';
        }
        const buttonImageUrl = modelViewer.getAttribute('data-ar-button-image') ||
                               modelViewer.getAttribute('ar-button-image');
        // CRITICAL FIX: Only use icon if explicitly enabled
        const buttonIconEnabled = modelViewer.getAttribute('data-ar-button-icon-enabled') === '1' ||
                                  modelViewer.getAttribute('ar-button-icon-enabled') === '1';
        const buttonIcon = buttonIconEnabled ? (modelViewer.getAttribute('data-ar-button-icon') || 
                          modelViewer.getAttribute('ar-button-icon')) : null;
        const buttonIconPosition = modelViewer.getAttribute('data-ar-button-icon-position') || 
                                   modelViewer.getAttribute('ar-button-icon-position') || 
                                   'left';
        
        // Apply custom styling from data attributes
        const bgColor = modelViewer.getAttribute('data-ar-button-bg-color');
        const textColor = modelViewer.getAttribute('data-ar-button-text-color');
        const borderColor = modelViewer.getAttribute('data-ar-button-border-color');
        const buttonSize = modelViewer.getAttribute('data-ar-button-size');
        const borderRadius = modelViewer.getAttribute('data-ar-button-border-radius');
        const buttonPosition = modelViewer.getAttribute('data-ar-button-position');
        
        // Debug log to check if attributes are present
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('AR Button Styling:', {
                text: buttonText,
                bgColor: bgColor || 'default',
                textColor: textColor || 'default',
                borderColor: borderColor || 'default',
                size: buttonSize || 'default',
                borderRadius: borderRadius || 'default',
                position: buttonPosition || 'default',
                iconEnabled: buttonIconEnabled,
                icon: buttonIcon
            });
        }
        
        // Add version attribute for cache busting
        button.setAttribute('data-explorexr-ar-version', '1.0.6');
        
        // CRITICAL: Use CSS custom properties instead of inline styles with !important
        // CSS rules in ar-styles.css will apply these with !important
        let buttonStyles = [];

        // Set CSS custom properties for styling (CSS will apply with !important)
        if (bgColor && bgColor !== '') {
            buttonStyles.push('--ar-button-bg: ' + bgColor);
        }

        if (textColor && textColor !== '') {
            buttonStyles.push('--ar-button-color: ' + textColor);
        }

        if (borderColor && borderColor !== '') {
            buttonStyles.push('--ar-button-border: 1px solid ' + borderColor);
        }

        if (borderRadius && borderRadius !== '') {
            buttonStyles.push('--ar-button-radius: ' + borderRadius + 'px');
        }
        
        // Size-based padding and font size via CSS custom properties
        if (buttonSize === 'small') {
            buttonStyles.push('--ar-button-padding: 6px 10px');
            buttonStyles.push('--ar-button-font-size: 12px');
        } else if (buttonSize === 'large') {
            buttonStyles.push('--ar-button-padding: 12px 18px');
            buttonStyles.push('--ar-button-font-size: 16px');
        } else if (buttonSize === 'medium') {
            buttonStyles.push('--ar-button-padding: 8px 12px');
            buttonStyles.push('--ar-button-font-size: 14px');
        }

        // Position still needs inline styles (absolute positioning)
        buttonStyles.push('position: absolute');
        buttonStyles.push('z-index: 10000');

        if (buttonPosition && buttonPosition !== '') {
            const positions = buttonPosition.split('-');
            const vertical = positions[0]; // top or bottom
            const horizontal = positions[1]; // left, center, or right
            
            if (vertical === 'top') {
                buttonStyles.push('top: 16px');
                buttonStyles.push('bottom: auto');
            } else {
                buttonStyles.push('bottom: 16px');
                buttonStyles.push('top: auto');
            }
            
            if (horizontal === 'left') {
                buttonStyles.push('left: 16px');
                buttonStyles.push('right: auto');
            } else if (horizontal === 'center') {
                buttonStyles.push('left: 50%');
                buttonStyles.push('right: auto');
                buttonStyles.push('transform: translateX(-50%)');
            } else {
                buttonStyles.push('right: 16px');
                buttonStyles.push('left: auto');
            }
        }
        
        // Apply all styles at once - CSS rules in ar-styles.css handle styling with !important
        button.style.cssText = buttonStyles.join('; ');

        // Debug: Log applied styles
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('ExploreXR AR: Button styles applied:', {
                cssText: button.style.cssText,
                computedDisplay: window.getComputedStyle(button).display,
                computedVisibility: window.getComputedStyle(button).visibility,
                computedPosition: window.getComputedStyle(button).position,
                computedZIndex: window.getComputedStyle(button).zIndex
            });
        }
        
        // Helper to build icon element
        const buildIconElement = (iconValue) => {
            if (!iconValue) {
                return null;
            }

            const wrapper = document.createElement('span');
            wrapper.className = 'explorexr-premium-ar-button-icon';
            wrapper.style.display = 'inline-flex';
            wrapper.style.alignItems = 'center';

            const isUrl = /^https?:\/\//i.test(iconValue) || iconValue.startsWith('data:') || iconValue.startsWith('/');
            if (isUrl) {
                const img = document.createElement('img');
                img.src = iconValue;
                img.alt = '';
                img.style.width = '20px';
                img.style.height = '20px';
                wrapper.appendChild(img);
            } else if (iconValue.trim().startsWith('<')) {
                wrapper.innerHTML = iconValue;
            } else {
                const span = document.createElement('span');
                span.className = iconValue;
                wrapper.appendChild(span);
            }

            return wrapper;
        };

        const iconElement = buildIconElement(buttonIcon);
        // CRITICAL FIX: Don't add fallback icon unless icon is explicitly enabled
        const fallbackIconNeeded = buttonIconEnabled && !buttonImageUrl && !iconElement;

        // Add button image if provided
        if (buttonImageUrl) {
            const img = document.createElement('img');
            img.src = buttonImageUrl;
            img.alt = buttonText;
            img.style.maxHeight = buttonSize === 'small' ? '30px' : (buttonSize === 'large' ? '60px' : '50px');
            img.style.maxWidth = buttonSize === 'small' ? '100px' : (buttonSize === 'large' ? '200px' : '150px');
            button.appendChild(img);
        }

        // Add icon/text content
        if (!buttonImageUrl) {
            const textSpan = document.createElement('span');
            textSpan.className = 'explorexr-premium-ar-button-text';
            textSpan.textContent = buttonText;

            const iconToUse = iconElement || (function() {
                if (!fallbackIconNeeded) return null;
                const fallback = document.createElement('span');
                fallback.className = 'explorexr-premium-ar-button-icon';
                fallback.style.display = 'inline-flex';
                fallback.style.alignItems = 'center';
                fallback.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M3,4c0-0.55,0.45-1,1-1h2V1H4C2.35,1,1,2.35,1,4v2h2V4z M20,3c0.55,0,1,0.45,1,1v2h2V4c0-1.65-1.35-3-3-3h-2v2H20z M4,21c-0.55,0-1-0.45-1-1v-2H1v2c0,1.65,1.35,3,3,3h2v-2H4z M20,21c0.55,0,1-0.45,1-1v-2h2v2c0,1.65-1.35,3-3,3h-2v-2H20z M18.25,7.6l-5.5-3.18c-0.46-0.27-1.04-0.27-1.5,0L5.75,7.6C5.29,7.87,5,8.36,5,8.9v6.35c0,0.54,0.29,1.03,0.75,1.3l5.5,3.18c0.46,0.27,1.04,0.27,1.5,0l5.5-3.18c0.46-0.27,0.75-0.76,0.75-1.3V8.9C19,8.36,18.71,7.87,18.25,7.6z M7,14.96v-4.62l4,2.32v4.61L7,14.96z M12,10.93L8,8.61l4-2.31l4,2.31L12,10.93z M13,17.27v-4.61l4-2.32v4.62L13,17.27z"/></svg>';
                return fallback;
            })();

            const fragments = [];
            if (iconToUse && buttonIconPosition !== 'right') {
                iconToUse.style.marginRight = '8px';
                fragments.push(iconToUse);
            }

            fragments.push(textSpan);

            if (iconToUse && buttonIconPosition === 'right') {
                iconToUse.style.marginLeft = '8px';
                fragments.push(iconToUse);
            }

            fragments.forEach(node => button.appendChild(node));
        }
        
        // Add button to model viewer
        modelViewer.appendChild(button);
    }
      /**
     * Add custom AR fallback message
     * 
     * @param {HTMLElement} modelViewer The model-viewer element
     */
    function addARFallbackMessage(modelViewer) {
        // CRITICAL FIX: Don't check canActivateAR early on iOS - it may not be ready yet
        // Let model-viewer handle AR availability and only show fallback on actual failure
        
        // Check if we should show the message based on device type
        const arSupport = window.explorexrARFeatures ? window.explorexrARFeatures.isARSupported() : null;
        
        // Only show "not supported" message on mobile devices that truly don't support AR
        // Don't show this message on desktop/laptop computers
        if (arSupport && !arSupport.shouldShowNotSupported) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('AR fallback message suppressed for desktop device');
            }
            return;
        }
        
        // Get fallback text
        const fallbackText = modelViewer.getAttribute('ar-fallback-text') || 
                            addonSettings.fallbackText || 
                            'AR not supported on this device';
        
        // Create fallback message element
        if (!document.querySelector('.explorexr-premium-ar-not-supported')) {
            const fallbackMessage = document.createElement('div');
            fallbackMessage.className = 'explorexr-premium-ar-not-supported';
            fallbackMessage.textContent = fallbackText;
            fallbackMessage.style.display = 'none';
            modelViewer.appendChild(fallbackMessage);
            
            // Show message when AR button is clicked but not supported
            const arButton = modelViewer.querySelector('button[slot="ar-button"]');
            if (arButton) {
                arButton.addEventListener('click', function(event) {
                    if (!modelViewer.canActivateAR) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        fallbackMessage.style.display = 'block';
                        setTimeout(() => {
                            fallbackMessage.style.display = 'none';
                        }, 3000);
                    }
                });
            }
        }
    }
    
    /**
     * Setup AR event tracking
     * 
     * @param {HTMLElement} modelViewer The model-viewer element
     */
    function setupAREventTracking(modelViewer) {
        // Skip if tracking is disabled
        if (!addonSettings.enableTracking) {
            return;
        }
        
        // Generate unique ID for this model viewer if not already present
        if (!modelViewer.getAttribute('data-ar-id')) {
            const uniqueId = 'ar-' + Math.random().toString(36).substring(2, 10);
            modelViewer.setAttribute('data-ar-id', uniqueId);
        }
        
        // Track AR sessions
        modelViewer.addEventListener('ar-status', function(event) {
            const status = event.detail.status;
            const arId = modelViewer.getAttribute('data-ar-id');
            
            // Track status changes
            if (status === 'session-started') {
                trackAREvent(modelViewer, 'ar_session_start', {
                    model_id: modelViewer.getAttribute('data-model-id'),
                    ar_id: arId
                });
            } else if (status === 'session-ended') {
                trackAREvent(modelViewer, 'ar_session_end', {
                    model_id: modelViewer.getAttribute('data-model-id'),
                    ar_id: arId
                });
            } else if (status === 'failed') {
                trackAREvent(modelViewer, 'ar_session_failed', {
                    model_id: modelViewer.getAttribute('data-model-id'),
                    ar_id: arId,
                    error_type: event.detail.type || 'unknown'
                });
            }
        });
    }
    
    /**
     * Track AR events if analytics is available
     * 
     * @param {HTMLElement} modelViewer The model-viewer element
     * @param {string} eventName The event name
     * @param {Object} eventData Additional event data
     */
    function trackAREvent(modelViewer, eventName, eventData = {}) {
        // Check if analytics tracking is enabled
        if (!addonSettings.enableTracking) {
            return;
        }
        
        // Track with available analytics system
        if (typeof window.gtag === 'function') {
            // Google Analytics 4
            window.gtag('event', eventName, eventData);
        } else if (typeof window.ga === 'function') {
            // Universal Analytics
            window.ga('send', 'event', 'AR Interaction', eventName, JSON.stringify(eventData));
        } else if (window.explorexrAnalytics && typeof window.explorexrAnalytics.trackEvent === 'function') {
            // ExploreXR's own analytics
            window.explorexrAnalytics.trackEvent('ar', eventName, eventData);
        }
        
        // Also track with WordPress AJAX if needed
        if (addonSettings.ajaxurl) {
            const formData = new FormData();
            formData.append('action', 'explorexr_ar_track_event');
            formData.append('event', eventName);
            formData.append('data', JSON.stringify(eventData));
            formData.append('nonce', addonSettings.nonce || '');
            
            fetch(addonSettings.ajaxurl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            }).catch(error => {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.error('ExploreXR AR tracking error:', error);
                }
            });
        }
    }
    
    /**
     * Observe DOM for dynamically added model-viewers
     */
    function observeModelViewerAdditions() {
        if (!window.MutationObserver) {
            return;
        }
        
        const observer = new MutationObserver(mutations => {
            let newModelViewerAdded = false;
            
            mutations.forEach(mutation => {
                if (mutation.type === 'childList' && mutation.addedNodes.length) {
                    mutation.addedNodes.forEach(node => {
                        // Check direct model-viewer additions
                        if (node.nodeName === 'MODEL-VIEWER' && node.hasAttribute('ar')) {
                            enhanceModelViewerAR(node);
                            newModelViewerAdded = true;
                        }
                        // Check for model-viewers inside added elements
                        else if (node.nodeType === 1) {
                            const childModelViewers = node.querySelectorAll('model-viewer[ar]');
                            if (childModelViewers.length) {
                                childModelViewers.forEach(modelViewer => {
                                    enhanceModelViewerAR(modelViewer);
                                });
                                newModelViewerAdded = true;
                            }
                        }
                    });
                }
            });
            
            if (newModelViewerAdded) {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('ExploreXR AR: Enhanced dynamically added model-viewer(s)');
                }
            }
        });
        
        observer.observe(document.body, { 
            childList: true,
            subtree: true
        });
    }
    
    // Initialize enhanced AR functionality
    initEnhancedAR();
})();
