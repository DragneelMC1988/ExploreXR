/**
 * ExploreXR Animation Frontend Handler
 * 
 * Applies animation settings from WordPress to model-viewer on frontend
 */

(function() {
    'use strict';

    // Debug helper - exposes configuration for inspection
    // Only outputs when manually invoked via console; uses ExploreXRLogger when available.
    window.explorexrDebugConfig = function(modelViewer) {
        var _log = (typeof ExploreXRLogger !== 'undefined') ? ExploreXRLogger.log.bind(ExploreXRLogger) : function() {};
        if (!modelViewer) {
            _log('ExploreXR Debug: Finding all model-viewers...');
            const viewers = document.querySelectorAll('model-viewer');
            _log('Found ' + viewers.length + ' model-viewer elements');
            viewers.forEach(function(viewer, index) {
                _log('Model Viewer #' + (index + 1) + ':', {
                    id: viewer.id,
                    src: viewer.src,
                    animationName: viewer.getAttribute('animation-name'),
                    autoplay: viewer.hasAttribute('autoplay'),
                    loop: viewer.hasAttribute('loop'),
                    availableAnimations: viewer.availableAnimations,
                    dataAnimationSpeed: viewer.getAttribute('data-animation-speed'),
                    dataAnimationPingPong: viewer.getAttribute('data-animation-ping-pong'),
                    dataShowAnimationControls: viewer.getAttribute('data-show-animation-controls'),
                    dataPostProcessing: viewer.getAttribute('data-pp-enabled'),
                    allAttributes: Array.from(viewer.attributes).map(a => ({ name: a.name, value: a.value }))
                });
            });
            return;
        }
        
        _log('ExploreXR Model Viewer Configuration:', {
            element: modelViewer,
            src: modelViewer.src,
            animationName: modelViewer.getAttribute('animation-name'),
            autoplay: modelViewer.hasAttribute('autoplay'),
            loop: modelViewer.hasAttribute('loop'),
            availableAnimations: modelViewer.availableAnimations,
            currentTime: modelViewer.currentTime,
            duration: modelViewer.duration,
            timeScale: modelViewer.timeScale,
            playing: modelViewer.paused === false,
            dataAttributes: {
                animationSpeed: modelViewer.getAttribute('data-animation-speed'),
                animationPingPong: modelViewer.getAttribute('data-animation-ping-pong'),
                showAnimationControls: modelViewer.getAttribute('data-show-animation-controls'),
                controlPosition: modelViewer.getAttribute('data-animation-control-position'),
                controlStyle: modelViewer.getAttribute('data-animation-control-style'),
                controlSize: modelViewer.getAttribute('data-animation-control-size')
            },
            allAttributes: Array.from(modelViewer.attributes).map(a => ({ name: a.name, value: a.value }))
        });
    };

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAnimations);
    } else {
        initAnimations();
    }

    function initAnimations() {
        // Find all model-viewers with animation settings
        const modelViewers = document.querySelectorAll('model-viewer[data-animation-speed], model-viewer[data-show-animation-controls], model-viewer[data-animation-ping-pong], model-viewer[data-animation-crossfade], model-viewer[animation-name], model-viewer[data-animation-scroll-trigger]');
        
        // Auto-debug if WP_DEBUG and SCRIPT_DEBUG are enabled
        const autoDebug = document.querySelector('model-viewer[data-debug-config="true"]');
        if (autoDebug && typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('ExploreXR Animation: Debug mode enabled');
            ExploreXRLogger.log('Found ' + modelViewers.length + ' model-viewer(s) with animation settings');
        }
        
        modelViewers.forEach(function(viewer) {
            const showControlsAttr = viewer.getAttribute('data-show-animation-controls');
            if (showControlsAttr !== 'true') {
                const existingControls = viewer.querySelector('.explorexr-animation-controls');
                if (existingControls) {
                    existingControls.remove();
                }
            } else if (hasPremiumControlsConfig(viewer)) {
                const existingControls = viewer.querySelector('.explorexr-animation-controls');
                if (existingControls) {
                    existingControls.remove();
                }
            }

            if (autoDebug && typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('Setting up animations for: ' + (viewer.id || 'unnamed model-viewer'));
            }
            if (!viewer.hasAttribute('data-explorexr-animation-frontend-init')) {
                viewer.setAttribute('data-explorexr-animation-frontend-init', 'true');
                setupAnimationViewer(viewer);
            }
        });
        
        // Log debug info if enabled
        if (autoDebug && modelViewers.length > 0 && typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.log('Call window.explorexrDebugConfig() to inspect configuration');
        }
    }

    function setupAnimationViewer(viewer) {
        // FIX 5: Guard against double initialization
        if (viewer._explorexrAnimationInitialized) {
            return;
        }
        viewer._explorexrAnimationInitialized = true;
        
        function syncAnimationForAR() {
            const speed = viewer.getAttribute('data-animation-speed');
            if (speed) {
                viewer.timeScale = parseFloat(speed);
            }
            
            const animationName = viewer.getAttribute('animation-name');
            if (animationName) {
                viewer.animationName = animationName;
            }
            
            // Ensure ping-pong handler is attached when needed
            const pingPong = viewer.getAttribute('data-animation-ping-pong');
            if (pingPong === 'true') {
                setupPingPongAnimation(viewer);
            }
            
            // Start playback when AR session begins
            if (viewer.animationName || viewer.hasAttribute('autoplay')) {
                try {
                    viewer.play();
                } catch (e) {
                    // Ignore play failures if AR session isn't ready yet
                }
            }
        }
        
        // Wait for model to load
        viewer.addEventListener('load', function() {
            // Apply animation speed
            const speed = viewer.getAttribute('data-animation-speed');
            if (speed) {
                viewer.timeScale = parseFloat(speed);
            }
            
            // Handle ping-pong mode
            const pingPong = viewer.getAttribute('data-animation-ping-pong');
            if (pingPong === 'true') {
                setupPingPongAnimation(viewer);
            }
            
            // Handle crossfade (model-viewer native support via animation-crossfade-duration attribute)
            // This is already handled by the animation-crossfade-duration attribute
            
            // Set up scroll-based animation if enabled
            const scrollTrigger = viewer.getAttribute('data-animation-scroll-trigger');
            if (scrollTrigger === 'true') {
                setupScrollAnimation(viewer);
            }

            // Show animation controls if enabled
            const showControls = viewer.getAttribute('data-show-animation-controls');
            if (showControls === 'true' && !hasPremiumControlsConfig(viewer)) {
                createAnimationControls(viewer);
                syncAnimationControlState(viewer);
            }

            // Only autoplay if scroll trigger is not active
            if (scrollTrigger !== 'true') {
                startAutoplayIfEnabled(viewer);
            }
        });
        
        // Re-apply animation settings when AR session starts
        viewer.addEventListener('ar-status', function(event) {
            if (event.detail && event.detail.status === 'session-started') {
                setTimeout(syncAnimationForAR, 300);
            }
        });
    }

    function hasPremiumControlsConfig(viewer) {
        const data = viewer.dataset || {};
        return Boolean(
            data.animationControlPosition ||
            data.animationControlStyle ||
            data.animationControlSize ||
            data.multipleAnimationsEnabled ||
            data.selectedAnimations
        );
    }

    function setupPingPongAnimation(viewer) {
        // Guard: prevent double initialization if animation-handler.js already set this up
        if (viewer._pingPongInitialized) {
            return;
        }
        viewer._pingPongInitialized = true;

        // Cancel any stale RAF loop from a previous initialisation attempt.
        if (viewer._pingPongRAF) {
            cancelAnimationFrame(viewer._pingPongRAF);
            viewer._pingPongRAF = null;
        }

        // model-viewer has no timeScale / playbackRate API.  Reverse playback must be
        // implemented by decrementing currentTime manually each animation frame.
        function getSpeed() {
            var raw = viewer.getAttribute('data-animation-speed');
            var parsed = raw ? parseFloat(raw) : NaN;
            return (!isNaN(parsed) && parsed > 0) ? parsed : 1.0;
        }

        var lastTimestamp = null;

        function reverseStep(timestamp) {
            if (!lastTimestamp) {
                lastTimestamp = timestamp;
            }
            var delta = (timestamp - lastTimestamp) / 1000;
            lastTimestamp = timestamp;

            var newTime = viewer.currentTime - (delta * getSpeed());

            if (newTime <= 0) {
                viewer.currentTime = 0;
                viewer._pingPongRAF = null;
                viewer.play();
                return;
            }

            viewer.currentTime = newTime;
            viewer._pingPongRAF = requestAnimationFrame(reverseStep);
        }

        viewer.addEventListener('finished', function() {
            if (viewer._pingPongRAF) {
                return; // reverse pass already in progress
            }
            lastTimestamp = null;
            viewer.pause();
            viewer.currentTime = viewer.duration || 0;
            viewer._pingPongRAF = requestAnimationFrame(reverseStep);
        });
    }

    function startAutoplayIfEnabled(viewer) {
        if (!viewer.hasAttribute('autoplay')) {
            return;
        }
        try {
            viewer.play();
            syncAnimationControlState(viewer);
        } catch (e) {
            // Ignore play failures if model-viewer isn't ready yet.
        }
    }

    function syncAnimationControlState(viewer) {
        const controls = viewer.querySelector('.explorexr-animation-controls');
        if (!controls) {
            return;
        }
        const playBtn = controls.querySelector('.play-btn');
        const pauseBtn = controls.querySelector('.pause-btn');
        if (!playBtn || !pauseBtn) {
            return;
        }
        if (viewer.paused === false) {
            playBtn.style.display = 'none';
            pauseBtn.style.display = 'block';
        } else {
            pauseBtn.style.display = 'none';
            playBtn.style.display = 'block';
        }
    }

    /**
     * Set up scroll-based animation control.
     *
     * Animation playback is driven by page scroll:
     *  - Scroll down  -> animation advances forward
     *  - Scroll up    -> animation reverses
     *  - Faster scroll -> faster animation advancement
     *  - Scroll stops  -> animation pauses in place
     *
     * The animation's currentTime is set directly on each frame rather
     * than relying on model-viewer's internal playback clock.  This gives
     * precise scrub-like control tied to the user's scroll.
     */
    function setupScrollAnimation(viewer) {
        // Initialise the animation action, then immediately pause the internal
        // clock so model-viewer does not auto-advance time on its own.
        //
        // Why play() then pause() instead of the previous timeScale=0 + play()?
        //
        // In model-viewer v4.x the currentTime setter delegates to
        // THREE.AnimationMixer.setTime(), which internally calls mixer.update()
        // and multiplies the delta by mixer.timeScale.  Setting timeScale=0
        // therefore made every viewer.currentTime = X call resolve to
        // mixer.update(0) — no animation advancement at all.
        //
        // Using pause() instead leaves mixer.timeScale at 1, so
        // viewer.currentTime = X correctly advances the animation to time X.
        // The currentTime setter also calls queueShadowRender(), which triggers
        // a render pass and re-evaluates bone transforms, so surface-tracked
        // hotspot annotations (data-surface) still update correctly.
        viewer.play();
        viewer.pause();

        // Remove autoplay and loop attributes for scroll mode
        viewer.removeAttribute('autoplay');
        viewer.removeAttribute('loop');

        // State ----------------------------------------------------------
        var scrollAccumulator = 0;   // raw delta pixels accumulated
        var currentTime       = 0;   // our tracked animation time (seconds)
        var rafId             = null;
        var isRunning         = false;

        // Scroll speed: user-configurable via data attribute (1-100%).
        // Uses exponential mapping from speed percentage to PIXELS_PER_CYCLE:
        //   1%  (slowest) = 20000px per cycle  (~1 frame per scroll tick)
        //   50% (medium)  = ~2000px per cycle   (default)
        //   100% (fastest) = 200px per cycle    (~30 frames per scroll tick)
        var scrollSpeedAttr = parseInt(viewer.getAttribute('data-animation-scroll-speed'), 10) || 50;
        var t = (scrollSpeedAttr - 1) / 99; // normalise 1-100 to 0-1
        var PIXELS_PER_CYCLE = Math.round(20000 * Math.pow(200 / 20000, t));
        // Clamp to safe bounds
        if (PIXELS_PER_CYCLE < 200) PIXELS_PER_CYCLE = 200;
        if (PIXELS_PER_CYCLE > 20000) PIXELS_PER_CYCLE = 20000;

        // After scroll stops, the accumulator decays toward 0 each frame
        // using this friction factor (0-1, lower = faster stop).
        var FRICTION = 0.9;

        // Below this threshold (seconds per frame) we consider motion stopped.
        var EPSILON = 0.0001;

        // Indicator element (optional UI badge) -------------------------
        var indicator = document.createElement('div');
        indicator.className = 'explorexr-scroll-indicator';
        indicator.textContent = 'Scroll to animate';
        var parentEl = viewer.parentNode;
        if (parentEl) {
            parentEl.style.position = 'relative';
            parentEl.appendChild(indicator);
        }

        // Scroll listener -----------------------------------------------
        function onWheel(e) {
            // Only capture scroll when the viewer is in (or near) the viewport.
            var rect = viewer.getBoundingClientRect();
            var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
            if (rect.bottom < 0 || rect.top > viewportHeight) {
                return; // completely off-screen, let page scroll normally
            }

            // Prevent the page from scrolling while we're driving the animation.
            e.preventDefault();

            // Accumulate the delta (normalised across browsers/devices).
            var delta = e.deltaY;
            if (e.deltaMode === 1) { delta *= 40; }  // lines -> px
            if (e.deltaMode === 2) { delta *= 800; } // pages -> px

            scrollAccumulator += delta;

            // Show the indicator as active
            indicator.classList.add('active');

            // Kick the render loop if it isn't already running.
            if (!isRunning) {
                isRunning = true;
                rafId = requestAnimationFrame(tick);
            }
        }

        // Animation frame loop ------------------------------------------
        function tick() {
            var duration = viewer.duration;
            if (!duration || duration <= 0) {
                isRunning = false;
                return;
            }

            // Convert accumulated scroll pixels into seconds of animation.
            var timeStep = (scrollAccumulator / PIXELS_PER_CYCLE) * duration;

            // Advance our tracked time.
            currentTime += timeStep;

            // Wrap time so it loops within [0, duration].
            currentTime = currentTime % duration;
            if (currentTime < 0) {
                currentTime += duration;
            }

            // Apply time to viewer. The animation is paused, so the internal
            // clock does not advance on its own — we drive time via scroll.
            viewer.currentTime = currentTime;

            // Decay the accumulator (friction).
            scrollAccumulator *= FRICTION;

            // Once the remaining delta is negligible, stop the loop.
            if (Math.abs(scrollAccumulator / PIXELS_PER_CYCLE * duration) < EPSILON) {
                scrollAccumulator = 0;
                isRunning = false;

                // Fade out indicator after a short delay
                setTimeout(function() {
                    if (!isRunning) {
                        indicator.classList.remove('active');
                    }
                }, 600);
                return;
            }

            rafId = requestAnimationFrame(tick);
        }

        // Attach the wheel listener with passive:false so we can preventDefault.
        viewer.addEventListener('wheel', onWheel, { passive: false });

        // Also support touch-based scrolling for mobile devices.
        var touchStartY = 0;
        var touchPrevY  = 0;

        viewer.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1) {
                touchStartY = e.touches[0].clientY;
                touchPrevY  = touchStartY;
            }
        }, { passive: true });

        viewer.addEventListener('touchmove', function(e) {
            if (e.touches.length !== 1) { return; }

            var rect = viewer.getBoundingClientRect();
            var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
            if (rect.bottom < 0 || rect.top > viewportHeight) { return; }

            var touchY = e.touches[0].clientY;
            var delta  = touchPrevY - touchY; // positive = finger moves up = scroll down
            touchPrevY = touchY;

            scrollAccumulator += delta;
            indicator.classList.add('active');

            if (!isRunning) {
                isRunning = true;
                rafId = requestAnimationFrame(tick);
            }
        }, { passive: true });

        viewer.addEventListener('touchend', function() {
            touchStartY = 0;
            touchPrevY  = 0;
        }, { passive: true });

        // Cleanup helper (not strictly needed on static pages, but good practice).
        viewer._scrollAnimationCleanup = function() {
            viewer.removeEventListener('wheel', onWheel);
            if (rafId) { cancelAnimationFrame(rafId); }
            if (indicator.parentNode) { indicator.parentNode.removeChild(indicator); }
        };
    }

    function createAnimationControls(viewer) {
        // Check if controls already exist
        if (viewer.querySelector('.explorexr-animation-controls')) {
            return;
        }

        // Get animation name for display
        var animName = viewer.animationName || viewer.getAttribute('animation-name') || '';
        var displayName = formatAnimName(animName);

        // Create controls container
        const controls = document.createElement('div');
        controls.className = 'explorexr-animation-controls';
        controls.innerHTML =
            '<div class="explorexr-anim-row explorexr-anim-row--main">' +
                '<button class="explorexr-anim-btn play-btn" title="Play" aria-label="Play animation">' +
                    '<svg width="16" height="16" viewBox="0 0 16 16" fill="none">' +
                        '<path d="M4 2.5L13 8L4 13.5V2.5Z" fill="currentColor"/>' +
                    '</svg>' +
                '</button>' +
                '<button class="explorexr-anim-btn pause-btn" title="Pause" aria-label="Pause animation" style="display:none;">' +
                    '<svg width="16" height="16" viewBox="0 0 16 16" fill="none">' +
                        '<rect x="3" y="2" width="4" height="12" rx="1" fill="currentColor"/>' +
                        '<rect x="9" y="2" width="4" height="12" rx="1" fill="currentColor"/>' +
                    '</svg>' +
                '</button>' +
                '<div class="explorexr-anim-progress-wrap">' +
                    (displayName ? '<span class="explorexr-anim-name">' + escapeHtml(displayName) + '</span>' : '') +
                    '<div class="explorexr-anim-progress-bar">' +
                        '<div class="explorexr-anim-progress-track">' +
                            '<div class="explorexr-anim-progress-fill"></div>' +
                            '<div class="explorexr-anim-progress-thumb"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<span class="explorexr-anim-time">0:00</span>' +
                '<button class="explorexr-anim-btn reset-btn" title="Reset" aria-label="Reset animation">' +
                    '<svg width="16" height="16" viewBox="0 0 16 16" fill="none">' +
                        '<path d="M8 3a5 5 0 1 0 5 5h-1.5a3.5 3.5 0 1 1-3.5-3.5V3z" fill="currentColor"/>' +
                        '<path d="M8 1l2.5 2.5L8 6V1z" fill="currentColor"/>' +
                    '</svg>' +
                '</button>' +
            '</div>';

        // Add controls to viewer
        viewer.appendChild(controls);

        // Bind control events
        const playBtn = controls.querySelector('.play-btn');
        const pauseBtn = controls.querySelector('.pause-btn');
        const resetBtn = controls.querySelector('.reset-btn');
        const timeDisplay = controls.querySelector('.explorexr-anim-time');
        const progressFill = controls.querySelector('.explorexr-anim-progress-fill');
        const progressThumb = controls.querySelector('.explorexr-anim-progress-thumb');
        const progressBar = controls.querySelector('.explorexr-anim-progress-bar');
        const nameDisplay = controls.querySelector('.explorexr-anim-name');

        function setPlayState(playing) {
            if (playing) {
                playBtn.style.display = 'none';
                pauseBtn.style.display = 'flex';
            } else {
                pauseBtn.style.display = 'none';
                playBtn.style.display = 'flex';
            }
        }

        playBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            viewer.play();
            setPlayState(true);
        });

        pauseBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            viewer.pause();
            setPlayState(false);
        });

        resetBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            viewer.currentTime = 0;
            const speed = viewer.getAttribute('data-animation-speed');
            if (speed) {
                viewer.timeScale = parseFloat(speed);
            } else {
                viewer.timeScale = 1;
            }
            viewer.play();
            setPlayState(true);
        });

        // Progress bar scrubbing
        var isScrubbing = false;

        function scrubTo(e) {
            var rect = progressBar.getBoundingClientRect();
            var ratio = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            if (viewer.duration) {
                viewer.currentTime = ratio * viewer.duration;
                updateProgress();
            }
        }

        progressBar.addEventListener('mousedown', function(e) {
            e.stopPropagation();
            isScrubbing = true;
            viewer.pause();
            scrubTo(e);
        });

        document.addEventListener('mousemove', function(e) {
            if (isScrubbing) {
                scrubTo(e);
            }
        });

        document.addEventListener('mouseup', function() {
            if (isScrubbing) {
                isScrubbing = false;
            }
        });

        // Touch support for scrubbing
        progressBar.addEventListener('touchstart', function(e) {
            e.stopPropagation();
            isScrubbing = true;
            viewer.pause();
            if (e.touches.length > 0) {
                scrubTo(e.touches[0]);
            }
        }, { passive: true });

        progressBar.addEventListener('touchmove', function(e) {
            if (isScrubbing && e.touches.length > 0) {
                scrubTo(e.touches[0]);
            }
        }, { passive: true });

        progressBar.addEventListener('touchend', function() {
            isScrubbing = false;
        }, { passive: true });

        // Update progress display
        function formatTime(seconds) {
            if (!seconds || !isFinite(seconds)) return '0:00';
            var mins = Math.floor(seconds / 60);
            var secs = Math.floor(seconds % 60);
            return mins + ':' + (secs < 10 ? '0' : '') + secs;
        }

        function updateProgress() {
            if (viewer.currentTime !== undefined && viewer.duration && viewer.duration > 0) {
                var ratio = viewer.currentTime / viewer.duration;
                var pct = (ratio * 100).toFixed(1) + '%';
                progressFill.style.width = pct;
                progressThumb.style.left = pct;
                timeDisplay.textContent = formatTime(viewer.currentTime);
            }
        }

        viewer.addEventListener('play', function() { setPlayState(true); });
        viewer.addEventListener('pause', function() { setPlayState(false); });

        // Handle animation finish
        viewer.addEventListener('finished', function() {
            var pingPong = viewer.getAttribute('data-animation-ping-pong');
            var loop = viewer.hasAttribute('loop');
            if (pingPong !== 'true' && !loop) {
                setPlayState(false);
            }
        });

        viewer.addEventListener('timeupdate', updateProgress);
        updateProgress();

        // Update displayed animation name when it changes
        if (nameDisplay) {
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(m) {
                    if (m.attributeName === 'animation-name') {
                        var newName = viewer.getAttribute('animation-name') || '';
                        nameDisplay.textContent = formatAnimName(newName);
                    }
                });
            });
            observer.observe(viewer, { attributes: true, attributeFilter: ['animation-name'] });
        }
        
        // Check initial playing state
        if (viewer.paused === false) {
            setPlayState(true);
        }
    }

    /**
     * Format animation name for display (basic controls).
     */
    function formatAnimName(name) {
        if (!name) return '';
        var formatted = name.replace(/^Animation_|^anim_|_anim$|\.anim$/i, '');
        formatted = formatted.replace(/[_-]/g, ' ');
        formatted = formatted.split(' ').map(function(w) {
            return w.charAt(0).toUpperCase() + w.slice(1);
        }).join(' ');
        return formatted;
    }

    /**
     * Escape HTML entities for safe insertion.
     */
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

})();
