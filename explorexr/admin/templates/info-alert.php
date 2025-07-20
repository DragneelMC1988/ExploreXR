<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Info alert box with configurable content -->
<div class="explorexr-alert info">
    <span class="dashicons dashicons-info"></span>
    <div>
        <?php echo wp_kses_post($alert_message); ?>
    </div>
</div>





