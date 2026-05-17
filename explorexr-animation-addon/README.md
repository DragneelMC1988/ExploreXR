# ExploreXR Animation Add-On

## Overview

This add-on extends the ExploreXR 3D Model Viewer with advanced animation capabilities. It provides a comprehensive solution for controlling and displaying animations in 3D models.

## Features

- Multiple animation support with animation selection
- Advanced animation controls (play, pause, reset)
- Custom animation timing and sequencing
- Ping-pong animation mode (animations play forward then backward)
- Animation crossfade transitions
- Responsive animation UI controls

## Plugin Structure

This plugin follows the standardized ExploreXR add-on structure:

1. **Main Plugin File (`explorexr-animation-addon.php`)**
   - Plugin header with version, description, and requirements
   - Constants for paths and versions
   - Main plugin initialization with dependency checks
   - Activation/deactivation hooks
   - Welcome notice implementation
   - Migration triggers

2. **Activation Process**
   - Check for main ExploreXR plugin
   - Set default options
   - Set transient for welcome notice
   - Trigger migration via action hook

3. **Deactivation Process**
   - Clean up transients
   - Preserve user data and settings for potential reactivation

4. **Uninstall Process**
   - Proper security check to prevent direct access
   - Remove all plugin options
   - Preserve model data to prevent data loss

5. **Migration System**
   - Action-based migration trigger
   - Schedule single event to run migration after activation
   - Track migration completion, count, and date
   - Display notice upon successful migration

6. **Core Files**
   - `animation-handler.php`: Handles animation functionality
   - `settings.php`: Manages add-on settings
   - `migration.php`: Handles migration from main plugin

7. **Assets**
   - JS files: Frontend and admin scripts
   - CSS files: Styling for animation controls

## Installation

1. Upload the `explorexr-animation-addon` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The add-on requires the main ExploreXR plugin to be active

## Usage

### Adding Animations to a 3D Model

1. Edit any 3D model in the ExploreXR model editor
2. Go to the "Advanced Animation Settings" metabox
3. Enable animations
4. Set animation options (name, autoplay, repeat mode)
5. Save the model

### Animation Settings

Access the animation settings page at ExploreXR > Animation Settings to configure:

- Ping-pong animation mode
- Default crossfade duration
- Animation control visibility

## Developer Information

The add-on provides several filters and actions for developers:

- `explorexr_animation_attributes`: Filter to modify animation attributes before they're applied to the model viewer
- `explorexr_animation_before_save`: Action that runs before animation settings are saved
- `explorexr_animation_after_save`: Action that runs after animation settings are saved

## Changelog

### 1.1.2
* Fixed animation frontend JavaScript compatibility with Google Model Viewer 4.1.0
* Fixed animation play/pause event listener registration under new model-viewer load lifecycle
* Version aligned with ExploreXR Premium core

### 1.1.0
* Restructured addon initialisation to match ExploreXR Premium 1.1.0 lifecycle
* Improved compatibility with new responsive model container sizing
* Internal code improvements and stabilisation

### 1.0.0
* Initial release

## License

GPL2 or later
