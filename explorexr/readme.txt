=== ExploreXR ===
Contributors: ayalothman
Tags: 3d, model-viewer, glb, gltf, ar
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

ExploreXR brings your website to life with interactive 3D models. Display GLB, GLTF, and USDZ files with ease - no coding required!

== Description ==

**Transform Your Website with Immersive 3D Content**

ExploreXR is a powerful WordPress plugin that enables you to showcase interactive 3D models on your website with minimal effort. Using Google's Model Viewer technology, ExploreXR brings depth and engagement to your product displays, portfolios, educational content, and more.

Whether you're showcasing products for e-commerce, architectural designs, artwork, educational models, or manufacturing components — ExploreXR makes it simple with no coding required.

= Key Features =
* **Easy 3D Model Management** - Upload and manage GLB, GLTF, and USDZ formats with drag-and-drop interface
* **Modern Admin Interface** - Intuitive dashboard with model browser and preview capabilities
* **Simple Shortcode System** - Embed models anywhere with `[explorexr_model id="123"]` shortcode
* **Responsive Design** - Device-specific configurations (Desktop, Tablet, Mobile)
* **Progressive Loading** - Customizable loading indicators and smooth fallback options
* **AR Support** - Augmented Reality viewing on compatible devices
* **Camera Controls** - Pan, rotate, zoom, and auto-rotation options
* **Performance Optimized** - Fast loading with minimal impact on page speed
* **Error Handling** - Comprehensive debugging tools with fallback content
* **WordPress Standards** - Built following best practices and coding standards

= Perfect For =
* **E-commerce Stores** - Show products in 3D for better shopping experiences
* **Portfolios** - Showcase 3D designs and artwork
* **Education** - Create interactive learning materials
* **Architecture** - Display building models and interior designs
* **Manufacturing** - Showcase products from every angle
* **Museums & Galleries** - Create virtual exhibitions

= Upgrade to Premium for More =
* **Annotations Add-on** - Interactive hotspots with custom information
* **Camera Controls Add-on** - Professional presentation angles and presets
* **Material Variants Add-on** - Switch between different materials and textures
* **Animation Add-on** - Advanced animation controls and sequencing
* **WooCommerce Add-on** - Enhanced product integration and gallery features
* **Priority Support** - Direct access to our development team
* **Regular Updates** - New features and improvements

[Learn more about ExploreXR Premium](https://expoxr.com/explorexr/premium/)

== Installation ==

= Automatic Installation =
1. Go to your WordPress dashboard
2. Navigate to Plugins → Add New
3. Search for "ExploreXR"
4. Click "Install Now" and then "Activate"

= Manual Installation =
1. Download the plugin ZIP file
2. Upload it to `/wp-content/plugins/` directory
3. Extract the files
4. Activate the plugin through the 'Plugins' menu in WordPress

= Quick Start Guide =
1. Navigate to **ExploreXR → Create Model** in your admin menu
2. Upload your 3D model file (GLB/GLTF format recommended)
3. Set a title and description for your model
4. Configure display options for different device sizes
5. Save your model and copy the generated shortcode
6. Paste the shortcode in any post, page, or widget area

For detailed setup instructions, visit our [documentation](https://www.expoxr.com/explorexr/documentation/).

== Frequently Asked Questions ==

= What 3D file formats are supported? =
ExploreXR supports GLB, GLTF, and USDZ file formats. GLB is recommended for most use cases due to its compressed size and single-file format.

= Do I need to know 3D modeling to use this plugin? =
No! ExploreXR is designed to be user-friendly for everyone. You just need 3D models in supported formats, which you can create yourself or download from various online sources.

= Will 3D models work on all devices? =
Yes, ExploreXR is designed to work on all modern devices and browsers with WebGL support. For older browsers or devices without 3D support, you can configure fallback images.

= Can visitors view models in AR (Augmented Reality)? =
Yes! Models can be viewed in AR on compatible devices (most modern iOS and Android devices). The AR button appears automatically on compatible devices.

= Is ExploreXR compatible with my theme? =
ExploreXR is designed to be compatible with any properly coded WordPress theme. The plugin includes responsive sizing options to ensure models look great in any layout.

= Can I customize how models appear? =
Absolutely! ExploreXR offers extensive customization options including size, background, controls, lighting, and more. You can configure these settings globally or per model.

= Does this plugin slow down my website? =
ExploreXR is designed with performance in mind. It includes progressive loading options, and 3D content only loads when needed. You can also configure loading animations and indicators.

= Where can I get 3D models to use with ExploreXR? =
You can create your own 3D models using software like Blender or purchase ready-made models from various online marketplaces. There are also free resources available for certain industries.

= Can I use ExploreXR with Elementor or WooCommerce? =
Basic integration is available in the free version. Advanced features like the Elementor widget and enhanced WooCommerce integration are available in ExploreXR Premium.

== Screenshots ==
1. Admin dashboard with model overview
2. Model creation interface with drag-and-drop upload
3. Model browser with preview capabilities
4. Shortcode configuration options
5. Frontend display with controls
6. Mobile view with AR capabilities
7. Settings page with customization options
8. Loading options configuration

== Changelog ==

= 1.0.2 =
* MAJOR: Model storage relocated to WordPress uploads directory (wp-content/uploads/explorexr_models/)
* SECURITY: Fixed all external CDN dependencies - now uses local files only
* SECURITY: Added comprehensive ABSPATH protection to all PHP files
* SECURITY: Enhanced .htaccess protection for models directory
* FIXED: PHP syntax error in uninstall.php that prevented proper cleanup
* FIXED: Function prefixing - renamed sanitize_hex_color to expoxr_sanitize_hex_color
* FIXED: Removed all inline CSS/JS from upgrade system for WordPress compliance
* IMPROVED: Streamlined uninstall process - preserves user settings and models
* IMPROVED: Enhanced script/style enqueueing with proper dependencies
* IMPROVED: All vendor dependencies now included locally (Draco, Basis Universal, Three.js)
* COMPLIANCE: Full WordPress.org Plugin Directory guidelines compliance
* GPL: Verified all dependencies are GPL-compatible

= 1.0.1 =
* Fixed: AR support attributes removed from free version model-viewer tags
* Fixed: 3D model delete functionality - added missing AJAX handler
* Fixed: Modal viewer now displays models at full container size
* Fixed: Removed troubleshooting tips from model preview overlays
* Improved: Enhanced security validation for model deletion
* Improved: Better error handling for AJAX operations

= 1.0.0 =
* Initial stable release with complete feature set
* Modern admin interface with comprehensive model management
* Progressive loading system with customizable indicators
* Security framework with input validation and access controls
* Responsive design with device-specific configurations
* Extensive customization options via shortcode parameters
* Improved error handling and debugging tools
* Performance optimizations for faster loading
* Enhanced file type validation and security
* Comprehensive documentation and help system

== Upgrade Notice ==

= 1.0.2 =
Major release with WordPress.org compliance fixes: Model storage moved to uploads directory, all external dependencies localized, enhanced security, and streamlined uninstall. Full compliance with WordPress Plugin Directory guidelines. Recommended update for all users.

= 1.0.1 =
Bug fixes and improvements: Fixed model deletion, AR support cleanup, and enhanced modal display. Recommended update for all users.

= 1.0.0 =
Initial release of ExploreXR. Transform your WordPress website with immersive 3D experiences!

== Additional Information ==

= Minimum Requirements =
* WordPress 5.0 or higher
* PHP 7.4 or higher
* Modern browser with WebGL support

= Support =
For support inquiries, please visit [our support forum](https://wordpress.org/support/plugin/explorexr/) or contact us through [our website](https://expoxr.com/support/).

= Documentation =
For detailed documentation, visit [expoxr.com](https://www.expoxr.com/explorexr/documentation/).

= Credits =
ExploreXR is powered by [Google's Model Viewer](https://modelviewer.dev/) web component.
* Clean, secure, and standards-compliant code

== Upgrade Notice ==

= 1.0.0 =
Initial release of ExploreXR. Add 3D models to your site easily with shortcode support.

== Privacy Policy ==

ExploreXR does not collect or transmit any user data. All rendering occurs in the visitor's browser. The plugin stores only the 3D model files and settings you configure within your WordPress site.

== Support ==

= Free Support =
* [Documentation](https://expoxr.com/explorexr/documentation/)
* [Support Forum](https://wordpress.org/support/plugin/explorexr/)
* [Community Help](https://expoxr.com/explorexr/support/)

= Premium Support =
Get access to priority support, WooCommerce & Elementor integrations, AR, and more.
[Upgrade to Premium](https://expoxr.com/explorexr/premium/)

== Credits ==

ExploreXR is powered by:
* [Model Viewer](https://modelviewer.dev/)
* WordPress API and best practices
* Open source contributors

Built with ❤️ by Ayal Othman from ExpoXR
