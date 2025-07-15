/**
 * ExpoXR Animation Handler
 *
 * Handles the animation functionality in the animation metabox
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Get the animation preview model-viewer element
        const modelViewer = document.getElementById('expoxr-animation-preview-model');
        
        // Toggle animation settings based on checkbox state
        $('input[name="expoxr_animation_enabled"]').on('change', function() {
            if ($(this).is(':checked')) {
                $('#animation-settings, #animation-preview').slideDown(300);
            } else {
                $('#animation-settings, #animation-preview').slideUp(300);
            }
        });
        
        // Handle animation controls if model-viewer exists
        if (modelViewer) {
            // Play button
            $('#animation-play').on('click', function() {
                // Get animation parameters from inputs
                const animationName = $('#expoxr_animation_name').val();
                
                if (modelViewer.availableAnimations && modelViewer.availableAnimations.length > 0) {
                    if (animationName) {
                        // Try to play the specified animation
                        try {
                            modelViewer.animationName = animationName;
                            modelViewer.play();
                        } catch (e) {
                            alert(`Could not play animation "${animationName}". Please check if the name is correct.`);
                            console.error(e);
                        }
                    } else {
                        // Play the first available animation if no name specified
                        modelViewer.animationName = modelViewer.availableAnimations[0];
                        modelViewer.play();
                    }
                } else {
                    alert('This model does not contain any animations.');
                }
            });
            
            // Pause button
            $('#animation-pause').on('click', function() {
                modelViewer.pause();
            });
            
            // Reset button
            $('#animation-reset').on('click', function() {
                modelViewer.pause();
                modelViewer.currentTime = 0;
            });
            
            // When model loads, check for available animations
            modelViewer.addEventListener('load', () => {
                if (modelViewer.availableAnimations && modelViewer.availableAnimations.length > 0) {
                    // Enable animation controls
                    $('.expoxr-animation-controls button').prop('disabled', false);
                    
                    // Display available animations
                    let animationList = $('<div class="expoxr-available-animations"></div>');
                    animationList.append('<h5>Available Animations:</h5>');
                    
                    const listElement = $('<ul></ul>');
                    modelViewer.availableAnimations.forEach(animation => {
                        listElement.append(`<li><a href="#" class="animation-select">${animation}</a></li>`);
                    });
                    
                    animationList.append(listElement);
                    animationList.insertBefore('.expoxr-animation-controls');
                    
                    // Bind click event to animation selection links
                    $('.animation-select').on('click', function(e) {
                        e.preventDefault();
                        const animationName = $(this).text();
                        $('#expoxr_animation_name').val(animationName);
                        
                        // Update model-viewer
                        modelViewer.animationName = animationName;
                        
                        // Autoplay the selected animation
                        modelViewer.play();
                    });
                    
                    // Apply animation settings from form
                    applyAnimationSettings();
                } else {
                    // Disable animation controls if no animations available
                    $('.expoxr-animation-controls button').prop('disabled', true);
                    
                    // Show message
                    $('<div class="notice notice-warning inline"><p>This model does not contain any animations.</p></div>')
                        .insertBefore('.expoxr-animation-controls');
                }
            });
            
            // Apply changes to animation settings in real-time
            function applyAnimationSettings() {
                // Apply animation name
                const animationName = $('#expoxr_animation_name').val();
                if (animationName && modelViewer.availableAnimations && modelViewer.availableAnimations.includes(animationName)) {
                    modelViewer.animationName = animationName;
                }
                
                // Apply autoplay setting
                if ($('input[name="expoxr_animation_autoplay"]').is(':checked')) {
                    modelViewer.autoplay = true;
                } else {
                    modelViewer.autoplay = false;
                }
                
                // Apply animation repeat mode
                const repeatMode = $('#expoxr_animation_repeat').val();
                switch (repeatMode) {
                    case 'once':
                        modelViewer.loop = false;
                        break;
                    case 'loop':
                        modelViewer.loop = true;
                        break;
                    case 'pingpong':
                        // Model-viewer doesn't natively support ping-pong,
                        // but we can simulate it with custom JS
                        modelViewer.loop = false;
                        
                        // Remove any existing event listener for ping-pong mode
                        if (modelViewer._pingPongHandler) {
                            modelViewer.removeEventListener('timeupdate', modelViewer._pingPongHandler);
                        }
                        
                        // Add event listener for ping-pong mode
                        const pingPongHandler = () => {
                            if (modelViewer.currentTime >= modelViewer.duration) {
                                // Reverse the animation when it reaches the end
                                modelViewer.timeScale = -1;
                                modelViewer.play();
                            } else if (modelViewer.currentTime <= 0) {
                                // Forward the animation when it reaches the beginning
                                modelViewer.timeScale = 1;
                                modelViewer.play();
                            }
                        };
                        
                        modelViewer.addEventListener('timeupdate', pingPongHandler);
                        modelViewer._pingPongHandler = pingPongHandler;
                        break;
                }
            }
            
            // Update animation preview when settings change
            $('#expoxr_animation_name, #expoxr_animation_repeat').on('change', applyAnimationSettings);
            $('input[name="expoxr_animation_autoplay"]').on('change', applyAnimationSettings);
        }
        
        // Preview animation settings button
        $('<button type="button" class="button button-secondary" id="preview-animation-settings">Apply Settings to Preview</button>')
            .insertBefore('.expoxr-animation-controls')
            .on('click', function(e) {
                e.preventDefault();
                
                if (modelViewer) {
                    applyAnimationSettings();
                    
                    // Play the animation
                    modelViewer.play();
                }
            });
    });
    
})(jQuery);