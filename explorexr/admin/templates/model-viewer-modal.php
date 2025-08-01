<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Model viewer modal for previewing 3D models -->
<div id="explorexr-model-modal" class="explorexr-model-modal">
    <div class="explorexr-model-modal-content">
        <span class="explorexr-model-close">&times;</span>
        <h3 id="explorexr-model-title" class="explorexr-model-title">3D Model Preview</h3>
        <!-- Model viewer will be dynamically created when modal is opened -->
        <div id="explorexr-model-viewer-container"></div>
    </div>
</div>

<?php
// WordPress.org compliance: Convert inline script to wp_add_inline_script
$modal_script = '
// Only load model-viewer when the modal is actually opened
document.addEventListener("DOMContentLoaded", function() {
    let modelViewerLoaded = false;
    
    function loadModelViewer() {
        if (modelViewerLoaded) return;
        
        const container = document.getElementById("explorexr-model-viewer-container");
        if (container) {
            container.innerHTML = "<model-viewer id=\"explorexr-model-viewer\" camera-controls auto-rotate loading=\"eager\" reveal=\"interaction\"></model-viewer>";
            modelViewerLoaded = true;
            
            // Trigger model-viewer script loading if needed
            if (typeof window.loadExploreXRModelViewer === "function") {
                window.loadExploreXRModelViewer();
            }
        }
    }
    
    // Load model-viewer when modal is opened
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("preview-model") || 
            e.target.closest(".preview-model")) {
            loadModelViewer();
        }
    });
});
';
wp_add_inline_script('jquery', $modal_script);
?>
            loadModelViewer();
        }
    });
});
</script>





