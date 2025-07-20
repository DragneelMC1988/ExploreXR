<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include settings callbacks
require_once EXPLOREXR_PLUGIN_DIR . 'admin/settings/settings-callbacks.php';

// Include individual page files
require_once EXPLOREXR_PLUGIN_DIR . 'admin/pages/dashboard-page.php';
require_once EXPLOREXR_PLUGIN_DIR . 'admin/pages/create-model-page.php';
require_once EXPLOREXR_PLUGIN_DIR . 'admin/pages/browse-models-page.php';
require_once EXPLOREXR_PLUGIN_DIR . 'admin/pages/files-page.php';
require_once EXPLOREXR_PLUGIN_DIR . 'admin/pages/loading-options-page.php';
require_once EXPLOREXR_PLUGIN_DIR . 'admin/pages/settings-page.php';

// Include edit model page if exists
$edit_model_page = EXPLOREXR_PLUGIN_DIR . 'admin/pages/edit-model-page.php';
if (file_exists($edit_model_page)) {
    require_once $edit_model_page;
}

// Include import/export functionality (if exists)
$import_export_file = EXPLOREXR_PLUGIN_DIR . 'admin/settings/import-export.php';
if (file_exists($import_export_file)) {
    require_once $import_export_file;
}

// Additional settings and callbacks can be added here if needed
// The main admin page functions are now in their respective files






