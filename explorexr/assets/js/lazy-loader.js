/**
 * Lazy Loader for ExploreXR 3D Models
 * Uses Intersection Observer API to load models only when they become visible in the viewport
 * 
 * @package ExploreXR
 * @version 1.0.0
 */

(function() {
    'use strict';

    // Check if Intersection Observer is supported
    if (!('IntersectionObserver' in window)) {
        if (typeof ExploreXRLogger !== 'undefined') {
            ExploreXRLogger.warn('ExploreXR: Intersection Observer not supported. Loading all models immediately.');
        }
        loadAllModelsImmediately();
        return;
    }

    // Configuration for the observer
    const observerConfig = {
        root: null, // viewport
        rootMargin: '50px', // Start loading 50px before entering viewport
        threshold: 0.1 // Trigger when 10% of the element is visible
    };

    // Store loaded models to prevent duplicate loading
    const loadedModels = new Set();

    /**
     * Load a model when it becomes visible
     * @param {HTMLElement} container The lazy container element
     */
    function loadModel(container) {
        const wrapper = container.querySelector('.ExploreXR-lazy-viewer-wrapper');
        const placeholder = container.querySelector('.ExploreXR-lazy-placeholder');
        const loadingIndicator = placeholder ? placeholder.querySelector('.ExploreXR-lazy-loading-indicator') : null;
        
        if (!wrapper) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.error('ExploreXR: Lazy viewer wrapper not found');
            }
            return;
        }

        const instanceId = wrapper.getAttribute('data-instance-id');
        
        // Check if already loaded
        if (loadedModels.has(instanceId)) {
            return;
        }

        // Mark as loaded
        loadedModels.add(instanceId);

        // Show loading indicator
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
        }

        // Get model data
        const modelUrl = wrapper.getAttribute('data-model-url');
        const modelAttributesJson = wrapper.getAttribute('data-model-attributes');
        const modelId = wrapper.getAttribute('data-model-id');

        if (!modelUrl || !modelAttributesJson) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.error('ExploreXR: Missing model data for lazy loading');
            }
            return;
        }

        let modelAttributes;
        try {
            modelAttributes = JSON.parse(modelAttributesJson);
        } catch (e) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.error('ExploreXR: Failed to parse model attributes', e);
            }
            return;
        }

        // Check if we have the global loader function
        if (typeof window.loadExploreXRModel === 'function') {
            // Use the existing loader function
            window.loadExploreXRModel(instanceId, modelUrl, modelAttributes);
            
            // Wait a moment for the model to initialize, then hide placeholder
            setTimeout(function() {
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
                wrapper.style.display = 'block';
            }, 500);
        } else {
            // Fallback: Create model-viewer directly
            createModelViewer(wrapper, modelAttributes, function() {
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
                wrapper.style.display = 'block';
            });
        }
    }

    /**
     * Create a model-viewer element directly
     * @param {HTMLElement} wrapper The wrapper element
     * @param {Object} attributes Model attributes
     * @param {Function} callback Callback when model is created
     */
    function createModelViewer(wrapper, attributes, callback) {
        const modelViewer = document.createElement('model-viewer');
        
        // Set all attributes
        for (const [key, value] of Object.entries(attributes)) {
            if (key !== 'class' && key !== 'id') {
                modelViewer.setAttribute(key, value);
            }
        }
        
        // Add class
        if (attributes.class) {
            modelViewer.className = attributes.class;
        }
        
        // Add ID
        if (attributes.id) {
            modelViewer.id = attributes.id;
        }
        
        // Append to wrapper
        wrapper.appendChild(modelViewer);
        
        // Call callback
        if (callback) {
            callback();
        }
        
        // Log for debugging
        if (window.ExploreXR && window.ExploreXR.debugMode) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('ExploreXR: Lazy loaded model', attributes.src);
            }
        }
    }

    /**
     * Create Intersection Observer to watch lazy containers
     */
    function initLazyLoader() {
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const container = entry.target;
                    
                    // Load the model
                    loadModel(container);
                    
                    // Stop observing this container
                    observer.unobserve(container);
                }
            });
        }, observerConfig);

        // Find all lazy containers
        const lazyContainers = document.querySelectorAll('.ExploreXR-lazy-container[data-lazy-load="true"]');
        
        lazyContainers.forEach(function(container) {
            observer.observe(container);
        });

        // Log for debugging
        if (window.ExploreXR && window.ExploreXR.debugMode) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.log('ExploreXR: Initialized lazy loader for ' + lazyContainers.length + ' models');
            }
        }
    }

    /**
     * Fallback: Load all models immediately if Intersection Observer is not supported
     */
    function loadAllModelsImmediately() {
        const lazyContainers = document.querySelectorAll('.ExploreXR-lazy-container[data-lazy-load="true"]');
        
        lazyContainers.forEach(function(container) {
            loadModel(container);
        });
    }

    /**
     * Initialize when DOM is ready
     */
    function init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLazyLoader);
        } else {
            initLazyLoader();
        }
    }

    // Start initialization
    init();

    // Expose function for manual triggering if needed
    window.ExploreXRLazyLoader = {
        loadModel: loadModel,
        init: initLazyLoader
    };

})();
