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
        <model-viewer id="explorexr-model-viewer" camera-controls auto-rotate
                     loading="eager"
                     reveal="interaction">
        </model-viewer>
    </div>
</div>





