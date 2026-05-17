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
// Modal JS is loaded via admin/js/model-viewer-modal.js (enqueued in admin-menu.php).
?>





