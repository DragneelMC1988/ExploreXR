<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Model viewer modal for previewing 3D models -->
<div id="expoxr-model-modal" class="expoxr-model-modal">
    <div class="expoxr-model-modal-content">
        <span class="expoxr-model-close">&times;</span>
        <h3 id="expoxr-model-title" class="expoxr-model-title">3D Model Preview</h3>
        <model-viewer id="expoxr-model-viewer" camera-controls auto-rotate
                     loading="eager"
                     reveal="interaction">
        </model-viewer>
    </div>
</div>





