# ExploreXR AR Add-On

This add-on extends the ExploreXR 3D Model Viewer with advanced Augmented Reality functionality.

## Description

The ExploreXR AR Add-On allows your website visitors to view your 3D models in augmented reality directly from your website. It supports multiple AR technologies to ensure compatibility with a wide range of devices:

- **WebXR**: For AR-capable browsers
- **Scene Viewer**: For Android devices
- **Quick Look**: For iOS devices

## Features

- Enable AR for any 3D model on your site
- Customize AR button appearance
- Set model placement options (floor, wall, ceiling)
- Control model scaling in AR
- Upload USDZ files for enhanced iOS support
- Track AR usage statistics
- Customize AR environment lighting

## Requirements

- ExploreXR 3D Model Viewer plugin (version 0.2.0 or higher)
- WordPress 5.9 or higher
- PHP 7.4 or higher

## Installation

1. Upload the `explorexr-ar-addon` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to ExploreXR → AR Settings to configure global AR options
4. Edit any 3D model to enable and customize AR options

## Frequently Asked Questions

### How do I enable AR for a 3D model?

Edit any 3D model and check the "Enable AR Mode" option in the Augmented Reality Options metabox.

### What devices support AR viewing?

- iOS devices with iOS 12+ using Safari browser
- Android devices with ARCore support (Android 8.0+) using Chrome
- Any device with a browser that supports WebXR

### How can I improve AR performance?

For best results, optimize your 3D models for web use:
- Keep file sizes under 10MB
- Reduce polygon count when possible
- Compress textures appropriately
- For iOS users, provide a USDZ version of your model

### Why doesn't AR work on all devices?

AR capability depends on the device hardware, operating system, and browser. Some older devices or browsers may not support AR features.

## Plugin Structure

The plugin follows WordPress best practices with a structured file organization:

- `explorexr-ar-addon.php` - Main plugin file with activation/deactivation hooks
- `uninstall.php` - Cleanup code when the plugin is uninstalled
- `includes/` - Core functionality
  - `ar-handler.php` - AR functionality and integration
  - `settings.php` - Settings page and options
  - `migration.php` - Data migration from main plugin
  - `metaboxes/` - Model-specific AR settings
- `assets/` - CSS, JavaScript, and image files
  - `css/` - Stylesheet files
  - `js/` - JavaScript files

## Activation and Deactivation

- **Activation**: The plugin checks for the main ExploreXR plugin, sets default AR options, and migrates any existing AR settings from the main plugin.
- **Deactivation**: Cleans up transients but preserves AR settings.
- **Uninstall**: Removes plugin options but preserves AR data in models.

## Migration

When first activated, the plugin will automatically migrate any existing AR settings from the main ExploreXR plugin.

## Changelog

### 1.1.2
* Bug fixes and stability improvements
* Version aligned with ExploreXR Premium core

### 1.1.0
* Complete admin UI redesign with tab-based interface — separate tabs for General, Button, Placement, and Advanced settings
* Comprehensive audit and fix of all AR configuration options
* Improved iOS (USDZ) and Android (WebXR/SceneViewer) mode handling
* Better AR button customisation controls in admin
* Corrected AR option defaults; removed stale legacy defaults

### 1.0.0
* Initial release

## Support

For support, please contact us through our website or visit the plugin's support forum.
