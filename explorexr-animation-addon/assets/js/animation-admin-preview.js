/**
 * Animation Admin Preview Script
 * Real-time preview updates for animation settings
 * Following ADDON_UI_DEVELOPMENT_GUIDE.md
 */

jQuery(document).ready(function($) {
    var _debug = (window.explorexrDebug && window.explorexrDebug.enabled) || (typeof WP_DEBUG !== 'undefined' && WP_DEBUG);
    function debugLog(msg) {
        if (_debug && typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log(msg);
        }
    }
    debugLog('Animation Admin Preview: Initializing');
    
    // Wait for model-viewer to be defined
    if (typeof window.customElements !== 'undefined' && window.customElements.get('model-viewer')) {
        initializeAnimationPreview();
    } else {
        debugLog('Waiting for model-viewer to be defined...');
        window.addEventListener('WebComponentsReady', initializeAnimationPreview);
    }
    
    function initializeAnimationPreview() {
        const modelViewer = document.getElementById('explorexr-animation-preview-model');
        
        if (!modelViewer) {
            debugLog('Animation preview model-viewer not found on this page');
            return;
        }
        
        debugLog('Animation preview initialized');
        
        // Animation Name Change
        $('#explorexr_premium_animation_name').on('change', function() {
            const animationName = $(this).val();
            debugLog('Animation name changed: ' + animationName);
            
            if (animationName) {
                modelViewer.setAttribute('animation-name', animationName);
                modelViewer.play();
            } else {
                modelViewer.removeAttribute('animation-name');
                modelViewer.pause();
            }
        });
        
        // Autoplay Change
        $('#explorexr_premium_animation_autoplay').on('change', function() {
            const autoplay = $(this).is(':checked');
            debugLog('Autoplay changed: ' + autoplay);
            
            if (autoplay) {
                modelViewer.setAttribute('autoplay', '');
                modelViewer.play();
            } else {
                modelViewer.removeAttribute('autoplay');
            }
        });
        
        // Animation Repeat Mode (sync preview attributes)
        $('#explorexr_premium_animation_repeat').on('change', function() {
            const repeatMode = $(this).val();
            debugLog('Repeat mode changed: ' + repeatMode);

            if (repeatMode === 'loop') {
                modelViewer.setAttribute('loop', '');
                modelViewer.removeAttribute('data-animation-ping-pong');
            } else if (repeatMode === 'pingpong') {
                modelViewer.removeAttribute('loop');
                modelViewer.setAttribute('data-animation-ping-pong', 'true');
            } else {
                modelViewer.removeAttribute('loop');
                modelViewer.removeAttribute('data-animation-ping-pong');
            }
        });
        
        // Multiple Animations Toggle
        $('#explorexr_premium_multiple_animations_enabled').on('change', function() {
            const enabled = $(this).is(':checked');
            debugLog('Multiple animations enabled: ' + enabled);
            
            if (enabled) {
                $('#multiple-animations-settings').slideDown();
            } else {
                $('#multiple-animations-settings').slideUp();
            }
        });
        
        // Scroll-Based Animation Toggle
        $('#explorexr_premium_animation_scroll_trigger').on('change', function() {
            const scrollEnabled = $(this).is(':checked');
            debugLog('Scroll trigger changed: ' + scrollEnabled);

            if (scrollEnabled) {
                modelViewer.setAttribute('data-animation-scroll-trigger', 'true');
                modelViewer.pause();
                // Show notice and scroll speed settings
                $('#scroll-trigger-notice').slideDown(300);
                $('#scroll-speed-settings').slideDown(300);
                // Check for conflicting settings and show warning
                checkScrollConflicts();
            } else {
                modelViewer.removeAttribute('data-animation-scroll-trigger');
                // Hide notice and scroll speed settings
                $('#scroll-trigger-notice').slideUp(300);
                $('#scroll-speed-settings').slideUp(300);
            }
        });

        // Scroll Speed Slider (1-100%)
        $('#explorexr_premium_animation_scroll_speed').on('input', function() {
            var speed = $(this).val();
            $('#scroll-speed-value').text(speed + '%');
            debugLog('Scroll speed changed: ' + speed + '%');
        });

        // Auto-fix conflicting settings button
        $('#scroll-trigger-auto-fix').on('click', function() {
            debugLog('Auto-fixing scroll conflicts');
            // Uncheck autoplay
            $('#explorexr_premium_animation_autoplay').prop('checked', false).trigger('change');
            // Set repeat mode to "once" (loop is derived from repeat mode)
            $('#explorexr_premium_animation_repeat').val('once').trigger('change');
            // Hide the notice after fix
            $('#scroll-trigger-notice').slideUp(300);
        });

        // Re-check conflicts when autoplay or repeat changes while scroll is active
        $('#explorexr_premium_animation_autoplay').on('change', function() {
            checkScrollConflicts();
        });
        $('#explorexr_premium_animation_repeat').on('change', function() {
            checkScrollConflicts();
        });

        // Check if scroll conflicts exist and show/hide notice accordingly
        function checkScrollConflicts() {
            if (!$('#explorexr_premium_animation_scroll_trigger').is(':checked')) {
                return;
            }
            var hasConflict = (
                $('#explorexr_premium_animation_autoplay').is(':checked') ||
                ($('#explorexr_premium_animation_repeat').val() !== 'once' && $('#explorexr_premium_animation_repeat').val() !== '')
            );
            if (hasConflict) {
                $('#scroll-trigger-notice').slideDown(300);
            } else {
                $('#scroll-trigger-notice').slideUp(300);
            }
        }

        // Frontend Controls Toggle
        $('#explorexr_premium_animation_show_frontend_controls').on('change', function() {
            const show = $(this).is(':checked');
            debugLog('Show frontend controls: ' + show);

            if (show) {
                $('#frontend-control-settings').slideDown();
            } else {
                $('#frontend-control-settings').slideUp();
            }
        });
        
        // Play Button
        $('#animation-play').on('click', function() {
            debugLog('Play button clicked');
            modelViewer.play();
        });
        
        // Pause Button
        $('#animation-pause').on('click', function() {
            debugLog('Pause button clicked');
            modelViewer.pause();
        });
        
        // Reset Button
        $('#animation-reset').on('click', function() {
            debugLog('Reset button clicked');
            modelViewer.currentTime = 0;
            modelViewer.pause();
        });
        
        // Load available animations from model
        modelViewer.addEventListener('load', function() {
            debugLog('Model loaded, checking for animations');
            
            const animations = modelViewer.availableAnimations;
            debugLog('Available animations: ' + (animations ? animations.join(', ') : 'none'));
            
            if (animations && animations.length > 0) {
                updateAnimationDropdown(animations);
                updateAnimationCheckboxes(animations);
            } else {
                debugLog('No animations found in model');
                $('#animation-detection-status').text('No animations found in this model.');
            }
        });
        
        function updateAnimationDropdown(animations) {
            const $select = $('#explorexr_premium_animation_name');
            const savedValue = $select.data('saved-value') || '';
            
            // Clear existing options except first
            $select.find('option:not(:first)').remove();
            
            // Add animation options
            animations.forEach(function(animName) {
                const $option = $('<option></option>')
                    .val(animName)
                    .text(animName);
                
                if (animName === savedValue) {
                    $option.prop('selected', true);
                }
                
                $select.append($option);
            });
            
            $select.find('option:first').text('Select an animation...');
            debugLog('Animation dropdown updated with ' + animations.length + ' animations');
        }
        
        function updateAnimationCheckboxes(animations) {
            const $container = $('#animation-checkboxes');
            let savedAnimations = [];
            const selectedData = $container.attr('data-selected');

            if (selectedData) {
                try {
                    savedAnimations = JSON.parse(selectedData);
                } catch (e) {
                    savedAnimations = [];
                }
            } else {
                savedAnimations = $('input[name="explorexr_premium_selected_animations[]"]').map(function() {
                    return $(this).val();
                }).get();
            }
            
            $container.empty();
            
            animations.forEach(function(animName) {
                const isChecked = savedAnimations.includes(animName);
                const $label = $('<label></label>');
                const $checkbox = $('<input type="checkbox" name="explorexr_premium_selected_animations[]">')
                    .val(animName)
                    .prop('checked', isChecked);
                
                $label.append($checkbox).append(' ' + animName);
                $container.append($label);
            });
            
            $('#animation-detection-status').hide();
            $container.show();
            
            debugLog('Animation checkboxes updated');
        }
    }
});
