/**
 * ExploreXR Animation Add-On - Animation Handler JavaScript
 * 
 * Provides advanced animation functionality for 3D models.
 */
(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initAnimations();
        
        // Listen for animation detection from the main plugin
        document.addEventListener('explorexr-premium-animations-detected', function(event) {
            const { modelViewer, animations } = event.detail;
            handleAnimationDetection(modelViewer, animations);
        });
    });

    /**
     * Handle animation detection from main plugin
     */
    function handleAnimationDetection(modelViewer, animations) {
        // Get model ID from data attributes or nearby elements
        const modelId = getModelIdFromViewer(modelViewer);
        
        if (modelId) {
            // Check if frontend controls should be shown for this model
            checkAndCreateFrontendControls(modelViewer, modelId, animations);
        }
    }

    /**
     * Get model ID from model viewer
     */
    function getModelIdFromViewer(modelViewer) {
        // Try to get from data attribute
        if (modelViewer.dataset.modelId) {
            return modelViewer.dataset.modelId;
        }
        
        // Try to extract from ID
        const id = modelViewer.id;
        if (id && id.includes('model')) {
            const match = id.match(/model-(\d+)/);
            if (match) {
                return match[1];
            }
        }
        
        // Try to get from container
        const container = modelViewer.closest('[data-model-id]');
        if (container) {
            return container.dataset.modelId;
        }
        
        return null;
    }

    /**
     * Check if frontend controls should be created for this model
     */
    function checkAndCreateFrontendControls(modelViewer, modelId, animations) {
        const ajaxConfig = window.explorexr_premium_animation_ajax || window.explorexr_animation_ajax;
        if (!ajaxConfig || !ajaxConfig.ajax_url) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.warn('Missing AJAX config for frontend controls');
            }
            return;
        }

        // Make AJAX request to check if frontend controls are enabled for this model
        $.ajax({
            url: ajaxConfig.ajax_url,
            type: 'POST',
            data: {
                action: 'explorexr_premium_get_animation_frontend_settings',
                model_id: modelId,
                nonce: ajaxConfig.nonce
            },
            success: function(response) {
                if (response.success && response.data.show_controls) {
                    createFrontendAnimationControls(modelViewer, animations, response.data);
                } else if (response.success && !response.data.show_controls) {
                    const container = modelViewer.parentNode;
                    const existingControls = container.querySelectorAll(
                        '.explorexr-premium-frontend-animation-controls, .explorexr-premium-advanced-animation-controls'
                    );
                    existingControls.forEach(function(controls) {
                        controls.remove();
                    });
                }
            },
            error: function() {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.warn('Could not load frontend control settings');
                }
            }
        });
    }

    /**
     * Initialize animations for model viewers
     */
    function initAnimations() {
        // Find all model viewers with animation attributes or data-show-animation-controls
        const modelViewers = document.querySelectorAll('model-viewer[animation-name], model-viewer[data-show-animation-controls="true"]');
        modelViewers.forEach(function(modelViewer, index) {
            if (modelViewer.hasAttribute('data-explorexr-animation-handler-init')) {
                return;
            }

            modelViewer.setAttribute('data-explorexr-animation-handler-init', 'true');
            if (modelViewer.dataset.showAnimationControls !== 'true') {
                const container = modelViewer.parentNode;
                const existingControls = container.querySelectorAll(
                    '.explorexr-premium-frontend-animation-controls, .explorexr-premium-advanced-animation-controls'
                );
                existingControls.forEach(function(controls) {
                    controls.remove();
                });
            }

            setupAdvancedAnimation(modelViewer, index);
            
            // Check if we should create frontend controls directly from data attributes
            if (modelViewer.dataset.showAnimationControls === 'true') {
                let selectedAnimations = [];
                if (modelViewer.dataset.selectedAnimations) {
                    try {
                        selectedAnimations = JSON.parse(modelViewer.dataset.selectedAnimations);
                    } catch (e) {
                        selectedAnimations = [];
                    }
                }

                const settings = {
                    show_controls: true,
                    position: modelViewer.dataset.animationControlPosition || 'bottom-left',
                    style: modelViewer.dataset.animationControlStyle || 'default',
                    size: modelViewer.dataset.animationControlSize || 'medium',
                    multiple_animations: modelViewer.dataset.multipleAnimationsEnabled === 'true',
                    selected_animations: selectedAnimations,
                    loop: modelViewer.hasAttribute('loop')
                };
                
                // Create frontend controls if animations are available
                modelViewer.addEventListener('load', function() {
                    if (modelViewer.availableAnimations && modelViewer.availableAnimations.length > 0) {
                        if (typeof ExploreXRLogger !== 'undefined') {
                            ExploreXRLogger.log('Creating frontend controls from data attributes with position: ' + settings.position);
                        }
                        createFrontendAnimationControls(modelViewer, modelViewer.availableAnimations, settings);
                    }
                });
            }
        });
    }

    /**
     * Set up advanced animation for a model viewer
     */
    function setupAdvancedAnimation(modelViewer, index) {
        // Wait for model to load before setting up animations
        modelViewer.addEventListener('load', function() {
            // Check for available animations
            if (modelViewer.availableAnimations && modelViewer.availableAnimations.length > 0) {
                if (typeof ExploreXRLogger !== 'undefined') {
                    ExploreXRLogger.log('Model #' + (index + 1) + ' has ' + modelViewer.availableAnimations.length + ' animations.');
                }
                
                // Legacy advanced controls (createAdvancedAnimationControls) have been superseded
                // by the modern basic controls in animation-frontend.js and the premium
                // configurable controls (createFrontendAnimationControls) in this file.
                // When premium config exists, createFrontendAnimationControls() handles it.
                // When no premium config exists, animation-frontend.js creates the basic controls.
                
                if (modelViewer.hasAttribute('autoplay')) {
                    try {
                        modelViewer.play();
                    } catch (e) {
                        // Ignore play failures if the viewer isn't ready yet.
                    }
                }
                
                // Set up ping-pong animation if enabled
                const pingPongEnabled = modelViewer.getAttribute('data-animation-ping-pong') === 'true';
                if (pingPongEnabled) {
                    setupPingPongAnimation(modelViewer);
                }
                
                // Set up animation sequence if defined
                const animationSequence = modelViewer.dataset.animationSequence;
                if (animationSequence) {
                    setupAnimationSequence(modelViewer, animationSequence);
                }
            }
        });
    }

    /**
     * Create frontend animation controls
     */
    function createFrontendAnimationControls(modelViewer, animations, settings) {
        const container = modelViewer.parentNode;
        
        // Remove any existing controls (both animation and camera)
        const existingAnimationControls = container.querySelector('.explorexr-premium-frontend-animation-controls');
        if (existingAnimationControls) {
            existingAnimationControls.remove();
        }
        
        // Remove any existing camera controls to prevent duplication
        const existingCameraControls = container.querySelector('.explorexr-premium-camera-controls');
        if (existingCameraControls) {
            existingCameraControls.remove();
        }
        
        // Create controls container
        const controlsContainer = document.createElement('div');
        controlsContainer.className = `explorexr-premium-frontend-animation-controls explorexr-premium-controls-${settings.style} explorexr-premium-controls-${settings.size} explorexr-premium-position-${settings.position}`;
        
        // Check if camera addon is active and model has camera controls enabled
        const hasCameraControls = modelViewer.hasAttribute('camera-controls') || modelViewer.dataset.expertCamera === 'true';
        
        // Create animation controls section
        const animationSection = document.createElement('div');
        animationSection.className = 'explorexr-premium-animation-section';
        
        // Create play/pause button
        const playButton = document.createElement('button');
        playButton.className = 'explorexr-premium-animation-play-button';
        playButton.setAttribute('aria-label', 'Play Animation');
        playButton.innerHTML = '<svg class="explorexr-premium-play-icon" width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M4 2.5L13 8L4 13.5V2.5Z" fill="currentColor"/></svg>';
        
        animationSection.appendChild(playButton);
        
        // Create animation name display (if multiple animations)
        let animationNameDisplay;
        if (settings.multiple_animations && animations.length > 1) {
            animationNameDisplay = document.createElement('span');
            animationNameDisplay.className = 'explorexr-premium-animation-name-display';
            animationSection.appendChild(animationNameDisplay);
        }
        
        // Filter animations based on selected animations setting
        let availableAnimations = animations;
        if (settings.selected_animations && settings.selected_animations.length > 0) {
            availableAnimations = animations.filter(anim => settings.selected_animations.includes(anim));
        }
        
        // Create animation selector (dropdown or checkboxes based on multiple_animations setting)
        let animationSelector;
        if (availableAnimations.length > 1) {
            if (settings.multiple_animations) {
                // Create checkbox list for multiple animation selection
                animationSelector = document.createElement('div');
                animationSelector.className = 'explorexr-premium-animation-checkbox-list';
                
                const label = document.createElement('label');
                label.textContent = 'Select Animations:';
                label.className = 'explorexr-premium-animation-selector-label';
                animationSelector.appendChild(label);
                
                availableAnimations.forEach(animation => {
                    const checkboxContainer = document.createElement('div');
                    checkboxContainer.className = 'explorexr-premium-animation-checkbox-item';
                    
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.value = animation;
                    checkbox.id = `anim_${animation}`;
                    checkbox.className = 'explorexr-premium-animation-checkbox';
                    
                    const checkboxLabel = document.createElement('label');
                    checkboxLabel.setAttribute('for', checkbox.id);
                    checkboxLabel.textContent = formatAnimationName(animation);
                    
                    checkboxContainer.appendChild(checkbox);
                    checkboxContainer.appendChild(checkboxLabel);
                    animationSelector.appendChild(checkboxContainer);
                });
            } else {
                // Create dropdown for single animation selection
                animationSelector = document.createElement('select');
                animationSelector.className = 'explorexr-premium-animation-selector';
                
                availableAnimations.forEach(animation => {
                    const option = document.createElement('option');
                    option.value = animation;
                    option.textContent = formatAnimationName(animation);
                    animationSelector.appendChild(option);
                });
            }
        }

        // Add animation section to controls container
        controlsContainer.appendChild(animationSection);
        
        // Add animation selector if available
        if (animationSelector) {
            controlsContainer.appendChild(animationSelector);
        }
        
        // Add camera controls if camera addon is active
        if (hasCameraControls) {
            const cameraSection = document.createElement('div');
            cameraSection.className = 'explorexr-premium-camera-section';
            
            // Reset camera button
            const resetButton = document.createElement('button');
            resetButton.className = 'explorexr-premium-camera-reset';
            resetButton.title = 'Reset Camera';
            resetButton.innerHTML = '<svg class="explorexr-premium-camera-icon" width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M8 3a5 5 0 1 0 5 5h-1.5a3.5 3.5 0 1 1-3.5-3.5V3z" fill="currentColor"/><path d="M8 1l2.5 2.5L8 6V1z" fill="currentColor"/></svg>';
            
            // Zoom in button
            const zoomInButton = document.createElement('button');
            zoomInButton.className = 'explorexr-premium-camera-zoom-in';
            zoomInButton.title = 'Zoom In';
            zoomInButton.innerHTML = '<svg class="explorexr-premium-camera-icon" width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
            
            // Zoom out button
            const zoomOutButton = document.createElement('button');
            zoomOutButton.className = 'explorexr-premium-camera-zoom-out';
            zoomOutButton.title = 'Zoom Out';
            zoomOutButton.innerHTML = '<svg class="explorexr-premium-camera-icon" width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M3 8h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
            
            cameraSection.appendChild(resetButton);
            cameraSection.appendChild(zoomInButton);
            cameraSection.appendChild(zoomOutButton);
            
            controlsContainer.appendChild(cameraSection);
            
            // Set up camera control event handlers
            setupCameraControlEvents(modelViewer, resetButton, zoomInButton, zoomOutButton);
        }
        
        // Position the controls
        controlsContainer.style.position = 'absolute';
        controlsContainer.style.zIndex = '10';
        
        // Apply positioning
        applyControlPosition(controlsContainer, settings.position);
        
        // Add to container
        container.style.position = 'relative';
        container.appendChild(controlsContainer);
        
        // Set up event handlers
        setupFrontendControlEvents(modelViewer, playButton, animationSelector, animationNameDisplay, settings);
        
        // Set initial animation
        if (availableAnimations.length > 0) {
            modelViewer.animationName = availableAnimations[0];
            if (animationNameDisplay) {
                animationNameDisplay.textContent = formatAnimationName(availableAnimations[0]);
            }
        }
        
        // Apply loop setting to model viewer
        if (settings.loop) {
            modelViewer.setAttribute('loop', '');
        } else {
            modelViewer.removeAttribute('loop');
        }

        if (modelViewer.hasAttribute('autoplay')) {
            try {
                modelViewer.play();
                playButton.innerHTML = '<svg class="explorexr-premium-pause-icon" width="14" height="14" viewBox="0 0 16 16" fill="none"><rect x="3" y="2" width="4" height="12" rx="1" fill="currentColor"/><rect x="9" y="2" width="4" height="12" rx="1" fill="currentColor"/></svg>';
                playButton.setAttribute('aria-label', 'Pause Animation');
            } catch (e) {
                // Ignore play failures if the viewer isn't ready yet.
            }
        }
    }
    
    /**
     * Apply positioning styles to controls
     */
    function applyControlPosition(controlsContainer, position) {
        // Apply position via CSS classes instead of inline styles
        // First remove any existing position classes
        controlsContainer.classList.remove(
            'explorexr-premium-position-bottom-left',
            'explorexr-premium-position-bottom-right',
            'explorexr-premium-position-top-left',
            'explorexr-premium-position-top-right',
            'explorexr-premium-position-bottom-center'
        );
        
        // Add the appropriate position class
        controlsContainer.classList.add(`explorexr-premium-position-${position}`);
        
        // Log position for debugging
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('Applied position: ' + position);
        }
    }

    /**
     * Set up event handlers for frontend controls
     */
    function setupFrontendControlEvents(modelViewer, playButton, animationSelector, animationNameDisplay, settings) {
        let isPlaying = false;
        let currentPlayingAnimations = [];

        function setPlayButtonState(playing) {
            if (playing) {
                playButton.innerHTML = '<svg class="explorexr-premium-pause-icon" width="14" height="14" viewBox="0 0 16 16" fill="none"><rect x="3" y="2" width="4" height="12" rx="1" fill="currentColor"/><rect x="9" y="2" width="4" height="12" rx="1" fill="currentColor"/></svg>';
                playButton.setAttribute('aria-label', 'Pause Animation');
                isPlaying = true;
            } else {
                playButton.innerHTML = '<svg class="explorexr-premium-play-icon" width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M4 2.5L13 8L4 13.5V2.5Z" fill="currentColor"/></svg>';
                playButton.setAttribute('aria-label', 'Play Animation');
                isPlaying = false;
            }
        }
        
        // Play/pause button
        playButton.addEventListener('click', function() {
            if (isPlaying) {
                modelViewer.pause();
                setPlayButtonState(false);
            } else {
                // Handle multiple animations or single animation
                if (settings.multiple_animations && animationSelector && animationSelector.className.includes('checkbox-list')) {
                    // Get selected animations from checkboxes
                    const selectedCheckboxes = animationSelector.querySelectorAll('input[type="checkbox"]:checked');
                    if (selectedCheckboxes.length > 0) {
                        // Play multiple animations (we'll play them in sequence for simplicity)
                        currentPlayingAnimations = Array.from(selectedCheckboxes).map(cb => cb.value);
                        playSelectedAnimations(modelViewer, currentPlayingAnimations, settings.loop);
                    }
                } else {
                    // Single animation
                    modelViewer.play();
                }
                
                setPlayButtonState(true);
            }
        });
        
        // Animation selector (dropdown)
        if (animationSelector && animationSelector.tagName === 'SELECT') {
            animationSelector.addEventListener('change', function() {
                const selectedAnimation = this.value;
                modelViewer.animationName = selectedAnimation;
                
                if (animationNameDisplay) {
                    animationNameDisplay.textContent = formatAnimationName(selectedAnimation);
                }
                
                // Auto-play the new animation if currently playing
                if (isPlaying) {
                    modelViewer.play();
                }
            });
        }
        
        // Animation selector (checkboxes) - for multiple animations
        if (animationSelector && animationSelector.className.includes('checkbox-list')) {
            const checkboxes = animationSelector.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Update the display if needed
                    if (animationNameDisplay) {
                        const checkedBoxes = animationSelector.querySelectorAll('input[type="checkbox"]:checked');
                        const checkedAnimations = Array.from(checkedBoxes).map(cb => formatAnimationName(cb.value));
                        animationNameDisplay.textContent = checkedAnimations.length > 0 ? 
                            checkedAnimations.join(', ') : 'None selected';
                    }
                });
            });
        }
        
        // Listen for animation events
        modelViewer.addEventListener('finished', function() {
            if (!settings.loop) {
                setPlayButtonState(false);
            }
        });

        modelViewer.addEventListener('play', function() {
            setPlayButtonState(true);
        });

        modelViewer.addEventListener('pause', function() {
            setPlayButtonState(false);
        });

        if (modelViewer.hasAttribute('autoplay') || modelViewer.paused === false) {
            setPlayButtonState(true);
        }
    }
    
    /**
     * Play selected animations in sequence or simultaneously
     */
    function playSelectedAnimations(modelViewer, animations, loop) {
        if (animations.length === 0) return;

        // Clean up previous multi-animation handler to prevent listener buildup
        if (modelViewer._multiAnimationHandler) {
            modelViewer.removeEventListener('finished', modelViewer._multiAnimationHandler);
            modelViewer._multiAnimationHandler = null;
        }

        let currentIndex = 0;

        function playNextAnimation() {
            if (currentIndex < animations.length) {
                modelViewer.animationName = animations[currentIndex];
                modelViewer.play();
                currentIndex++;

                // If loop is enabled and we've played all animations, restart
                if (loop && currentIndex >= animations.length) {
                    currentIndex = 0;
                }
            }
        }

        // Start with the first animation
        playNextAnimation();

        // Listen for animation finish to play next one
        const animationFinishHandler = function() {
            if (currentIndex < animations.length || loop) {
                setTimeout(() => {
                    playNextAnimation();
                }, 100); // Small delay between animations
            }
        };

        // Add the event listener
        modelViewer.addEventListener('finished', animationFinishHandler);

        // Store handler for cleanup
        modelViewer._multiAnimationHandler = animationFinishHandler;
    }

    /**
     * Set up ping-pong animation mode.
     *
     * model-viewer does not expose a timeScale / playbackRate property that
     * affects the underlying THREE.js AnimationMixer.  Setting a custom
     * `timeScale` property on the element has no effect on playback direction,
     * so the previous approach (timeScale = -1 → play()) simply restarted the
     * animation forward, producing an infinite forward loop.
     *
     * The correct approach is to drive the reverse pass manually with
     * requestAnimationFrame: pause the viewer, decrement currentTime each frame
     * at the natural playback rate, and call play() again once time reaches 0.
     */
    function setupPingPongAnimation(modelViewer) {
        // Guard: prevent double initialization if animation-frontend.js already set this up
        if (modelViewer._pingPongInitialized) {
            return;
        }
        modelViewer._pingPongInitialized = true;

        // Cancel any previous reverse-pass RAF loop on this viewer.
        if (modelViewer._pingPongRAF) {
            cancelAnimationFrame(modelViewer._pingPongRAF);
            modelViewer._pingPongRAF = null;
        }

        // Read optional playback speed multiplier (set by the speed control).
        function getSpeed() {
            var raw = modelViewer.getAttribute('data-animation-speed');
            var parsed = raw ? parseFloat(raw) : NaN;
            return (!isNaN(parsed) && parsed > 0) ? parsed : 1.0;
        }

        var lastTimestamp = null;

        function reverseStep(timestamp) {
            if (!lastTimestamp) {
                lastTimestamp = timestamp;
            }
            var delta = (timestamp - lastTimestamp) / 1000; // seconds
            lastTimestamp = timestamp;

            var newTime = modelViewer.currentTime - (delta * getSpeed());

            if (newTime <= 0) {
                // Reached the beginning — switch back to forward play.
                modelViewer.currentTime = 0;
                modelViewer._pingPongRAF = null;
                modelViewer.play();
                return;
            }

            modelViewer.currentTime = newTime;
            modelViewer._pingPongRAF = requestAnimationFrame(reverseStep);
        }

        // When the forward pass finishes, begin the manual reverse pass.
        modelViewer.addEventListener('finished', function() {
            // Only start the reverse if we are NOT already reversing (RAF active).
            if (modelViewer._pingPongRAF) {
                return;
            }
            lastTimestamp = null;
            modelViewer.pause();
            modelViewer.currentTime = modelViewer.duration || 0;
            modelViewer._pingPongRAF = requestAnimationFrame(reverseStep);
        });
    }

    /**
     * Set up animation sequence
     */
    function setupAnimationSequence(modelViewer, sequenceStr) {
        // Parse sequence
        const sequence = sequenceStr.split(',').map(s => s.trim());
        
        // Set current sequence index
        modelViewer._sequenceIndex = 0;
        modelViewer._sequence = sequence;
        
        // Play first animation
        if (sequence.length > 0) {
            modelViewer.animationName = sequence[0];
            
            // If autoplay is enabled, start playing
            if (modelViewer.hasAttribute('autoplay')) {
                modelViewer.play();
            }
            
            // Add event listener for animation end
            modelViewer.addEventListener('finished', function() {
                // Increment sequence index
                modelViewer._sequenceIndex = (modelViewer._sequenceIndex + 1) % modelViewer._sequence.length;
                
                // Play next animation
                modelViewer.animationName = modelViewer._sequence[modelViewer._sequenceIndex];
                modelViewer.play();
            });
        }
    }

    /**
     * Format animation name for display
     */
    function formatAnimationName(name) {
        // Remove common prefixes and suffixes
        let formattedName = name.replace(/^Animation_|^anim_|_anim$|\.anim$/, '');
        
        // Replace underscores and hyphens with spaces
        formattedName = formattedName.replace(/[_-]/g, ' ');
        
        // Capitalize first letter of each word
        formattedName = formattedName.split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
        
        return formattedName;
    }

    /**
     * Set up camera control event handlers for integrated controls
     */
    function setupCameraControlEvents(modelViewer, resetButton, zoomInButton, zoomOutButton) {
        // Capture the camera state using Lit properties (not HTML attributes) so we get
        // model-viewer's actual computed/fitted camera position after the model loads,
        // not the raw attribute string which may be absent or use generic fallbacks.
        // Defer by one rAF to ensure model-viewer has finished its initial camera setup.
        let initialOrbit = null;
        let initialTarget = null;
        let initialFov = null;

        requestAnimationFrame(function() {
            initialOrbit = modelViewer.cameraOrbit;
            initialTarget = modelViewer.cameraTarget;
            initialFov = modelViewer.fieldOfView;
        });

        // Reset camera to the fitted initial view captured above
        resetButton.addEventListener('click', function() {
            if (initialOrbit) {
                modelViewer.cameraOrbit = initialOrbit;
                modelViewer.cameraTarget = initialTarget;
                modelViewer.fieldOfView = initialFov;
            }
            modelViewer.setAttribute('interpolation-decay', '300');
        });

        // Zoom in — read live Lit property for current FOV
        zoomInButton.addEventListener('click', function() {
            const fovDeg = parseFloat(modelViewer.fieldOfView);
            const newFov = Math.max(fovDeg * 0.8, parseFloat(modelViewer.getAttribute('min-field-of-view') || '10'));
            modelViewer.fieldOfView = newFov + 'deg';
            modelViewer.setAttribute('interpolation-decay', '300');
        });

        // Zoom out — read live Lit property for current FOV
        zoomOutButton.addEventListener('click', function() {
            const fovDeg = parseFloat(modelViewer.fieldOfView);
            const newFov = Math.min(fovDeg * 1.2, parseFloat(modelViewer.getAttribute('max-field-of-view') || '90'));
            modelViewer.fieldOfView = newFov + 'deg';
            modelViewer.setAttribute('interpolation-decay', '300');
        });
    }

})(jQuery);
