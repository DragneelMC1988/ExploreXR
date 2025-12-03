=== ExploreXR ===
Contributors: expoxr
Tags: 3d, model-viewer, glb, gltf, ar
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

ExploreXR brings your website to life with interactive 3D models. Display GLB, GLTF, and USDZ files with ease – no coding required!

== Description ==

ExploreXR is a modern, secure, and WordPress-compliant 3D model viewer plugin. Upload GLB/GLTF models and embed them on any page using simple shortcodes. The free version includes essential 3D display features perfect for portfolios, e-commerce, education, architecture, and more.

**Free Version Features**
- Display GLB/GLTF 3D models
- Shortcode support: `[explorexr_model id="123"]`
- Basic customization (size, background, controls)
- Responsive model scaling for all devices
- Progressive loading with smooth fallback
- Intuitive drag-and-drop admin interface
- Fully browser compatible (WebGL)

**Great for**
Showcasing products, architecture, museum collections, educational content, engineering models, and 3D portfolios.

**Upgrade for More Features**
Premium version includes AR support, WooCommerce integration, Elementor widget, annotations, material variants, animations, and priority support.

Learn more: https://expoxr.com/explorexr/premium/

== Key Features ==
* GLB, GLTF, and USDZ file support
* Drag-and-drop 3D model upload
* Easy shortcode embedding
* Responsive display for desktop, tablet, mobile
* Progressive loading indicators
* Camera rotation, zoom, and pan
* Customizable viewer options
* Enhanced fallback and error handling
* Built using official WordPress coding standards

== Installation ==

= Automatic Installation =
1. Go to Plugins → Add New  
2. Search for “ExploreXR”  
3. Click Install → Activate  

= Manual Installation =
1. Upload the plugin ZIP to `/wp-content/plugins/`  
2. Extract the folder  
3. Activate via the Plugins menu  

= Quick Start =
1. Go to **ExploreXR → Create Model**  
2. Upload a GLB/GLTF file  
3. Configure settings  
4. Copy the generated shortcode  
5. Paste into any page or post  

Full documentation: https://expoxr.com/explorexr/documentation/

== Third-Party Libraries ==

= Google Model Viewer =
- License: Apache 2.0  
- Source: https://github.com/google/model-viewer  
- Purpose: 3D rendering engine  

= Draco Compression =
- License: Apache 2.0  
- Purpose: Geometry compression support  

= Basis Universal =
- License: Apache 2.0  
- Purpose: Texture compression  

All libraries are GPL-compatible and required for proper 3D rendering performance.

== Frequently Asked Questions ==

= Which formats are supported? =
GLB, GLTF, and USDZ. GLB is recommended.

= Does this require coding knowledge? =
No — everything works through UI and shortcode.

= Does it work on all devices? =
Yes. All modern devices with WebGL are supported.

= Is AR included? =
Available in ExploreXR Premium.

= Is it compatible with my theme? =
Yes, ExploreXR works with all properly coded themes.

= Does it slow down my site? =
Models load only when viewed and include progressive loading.

= Can I use it with Elementor or WooCommerce? =
Basic usage works everywhere. Advanced integrations are in Premium.

== Screenshots ==
1. Plugin dashboard   
2. Create new Model 
3. Plugin Settings  
4. 3D Models Overview  
5. Edit 3D Model

== Changelog ==
= 1.0.7 =
* Fixed: Custom tablet and mobile sizes now properly apply on frontend with responsive CSS
* Fixed: Mobile device size tab now displays correctly in Edit Model page
* Fixed: Removed unwanted "‹" character from admin page titles across all ExploreXR pages
* Fixed: WordPress.org security compliance - replaced wp_redirect() with wp_safe_redirect() for enhanced security
* Fixed: All WordPress Coding Standards violations resolved - plugin now passes Plugin Check with zero errors
* Improved: Added poster image preview in both Upload and Media Library tabs
* Improved: Upload sections now hidden until user clicks respective tabs for cleaner interface
* Improved: Poster upload section hidden after image selection to focus on preview
* Improved: Better responsive size management with proper WordPress breakpoints
* Enhanced: UI/UX improvements for model and poster upload workflow
* Enhanced: Complete PHPCS compliance with proper code annotations for template variables
* Security: Enhanced nonce verification and input sanitization across all admin forms

= 1.0.6 =
* Initial public release on WordPress.org  
* Includes core 3D viewer, shortcode, admin interface, and model upload system  

== Upgrade Notice ==

= 1.0.6 =
Initial release. Install this version to display 3D models in WordPress.

== Privacy Policy ==

ExploreXR does not collect or transmit personal data. All rendering occurs within the visitor's browser.

== Support ==

Free support:  
https://wordpress.org/support/plugin/explorexr/

Premium support:  
https://expoxr.com/explorexr/premium/

== Credits ==
Powered by Google Model Viewer and open-source contributors.  
Built with ❤️ by Ayal Othman (ExpoXR)
