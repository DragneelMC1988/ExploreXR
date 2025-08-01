# ExploreXR - 3D Model Viewer for WordPress

[![WordPress Compatible](https://img.shields.io/badge/WordPress-5.0%2B-0073aa.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-8892bf.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-007ec6.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.0.3-brightgreen.svg)](https://github.com/DragneelMC1988/ExploreXR/releases)
[![WordPress.org](https://img.shields.io/badge/WordPress.org-Compliant-0073aa.svg)](https://wordpress.org/plugins/)

## 🌐 Transform Your WordPress Site with Interactive 3D Content

ExploreXR brings the power of interactive 3D models to your WordPress website with zero coding required. Using Google's industry-leading Model Viewer technology, your visitors can interact with stunning 3D content directly in their browser—rotating, zooming, and even viewing products in their own space through AR.

**✅ WordPress.org Plugin Directory Approved - Fully Compliant**

**Perfect for:** E-commerce stores, product showcases, portfolios, museums, educational sites, real estate listings, architectural firms, and any website looking to engage users with immersive 3D experiences.

## ✨ Features

### 🎯 Core Capabilities
- **Simple Model Management** - Upload GLB/GLTF/USDZ with ease
- **Intuitive Dashboard** - Streamlined model organization
- **Flexible Shortcode System** - Place models anywhere
- **Responsive Design** - Perfect on all devices
- **Progressive Loading** - Smooth user experience
- **Comprehensive Error Handling** - Never leave users hanging

### 🔧 Technical Features
- **WordPress Standard Debugging** - Uses WP_DEBUG for troubleshooting
- **Security First** - Proper nonce verification and data sanitization
- **Performance Optimized** - Lazy loading and efficient file handling
- **Mobile Ready** - Touch controls for mobile devices
- **AR Support** - Augmented Reality viewing on supported devices

### 🎨 Viewer Controls
- **Camera Controls** - Enable/disable zoom, rotate, pan
- **Auto-rotate** - Automatic model rotation
- **Custom Sizing** - Flexible width and height options
- **Loading States** - Professional loading indicators

## 🚀 Quick Start

### Installation
1. Upload the plugin files to `/wp-content/plugins/explorexr/`
2. Activate the plugin through the WordPress admin
3. Visit **ExploreXR > Dashboard** to get started

### Usage
1. **Upload Models**: Go to ExploreXR > Create Model
2. **Configure Settings**: Set viewer size, controls, etc.
3. **Display Models**: Use shortcode `[explorexr_model id="123"]`

## 📋 System Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **Memory**: 128MB minimum (256MB recommended)
- **Storage**: 50MB+ for plugin files

## 🔧 Supported File Formats

- **GLB** (Recommended) - Binary GLTF format
- **GLTF** - Text-based 3D format
- **USDZ** - Apple's Universal Scene Description

## 🛡️ Security & Compliance

- ✅ WordPress.org Plugin Directory compliant
- ✅ Proper data sanitization and escaping
- ✅ Nonce verification for all forms
- ✅ No external CDN dependencies
- ✅ GPL v2+ licensed
- ✅ ABSPATH protection on all files

## 📂 File Structure

```
explorexr/
├── explorexr.php              # Main plugin file
├── readme.txt                 # WordPress.org readme
├── uninstall.php             # Clean uninstall
├── admin/                    # Admin interface
│   ├── core/                 # Admin core functions
│   ├── pages/                # Admin pages
│   ├── css/                  # Admin styles
│   └── js/                   # Admin scripts
├── assets/                   # Frontend assets
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript files
│   └── vendor/               # Third-party libraries
├── includes/                 # Core functionality
│   ├── core/                 # Core classes
│   ├── utils/                # Utility functions
│   └── ui/                   # UI components
└── template-parts/           # Template files
```

## 🔗 Dependencies

### Included Vendor Libraries (GPL Compatible)
- **Three.js**: 3D graphics library (MIT License)
- **Model Viewer**: Google's 3D model viewer (Apache 2.0)
- **Draco Compression**: 3D geometry compression (Apache 2.0)
- **Basis Universal**: Texture compression (Apache 2.0)

All dependencies are locally included for security and performance.

## � Debugging

ExploreXR uses WordPress standard debugging. To enable debug logging:

1. Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

2. Check logs in `/wp-content/debug.log`

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📋 Changelog

### Version 1.0.3 (August 2025) - Current
**COMPLETE WORDPRESS.ORG COMPLIANCE ACHIEVED**

**CRITICAL FIXES:**
* ✅ Complete WordPress.org compliance
* ✅ Fixed debugging system to use WordPress standards
* ✅ Enhanced security with proper nonce verification
* ✅ Removed empty files and cleaned up codebase
* ✅ Updated contributor information

**WORDPRESS.ORG COMPLIANCE:**
* COMPLIANCE: WordPress standard debugging implementation (no custom file logging)
* COMPLIANCE: All variables properly escaped with esc_js(), esc_attr(), esc_html()
* COMPLIANCE: Complete nonce validation and user permission checks
* COMPLIANCE: Uses WordPress uploads directory for file storage
* COMPLIANCE: All dependencies locally included and properly documented

### Version 1.0.2 (July 2025)
* **MAJOR**: Model storage relocated to WordPress uploads directory (wp-content/uploads/explorexr_models/)
* **SECURITY**: Fixed all external CDN dependencies - now uses local files only
* **SECURITY**: Added comprehensive ABSPATH protection to all PHP files
* **SECURITY**: Enhanced .htaccess protection for models directory
* **FIXED**: PHP syntax error in uninstall.php that prevented proper cleanup
* **FIXED**: Function prefixing - renamed sanitize_hex_color to ExploreXR_sanitize_hex_color
* **FIXED**: Removed all inline CSS/JS from upgrade system for WordPress compliance
* **IMPROVED**: Streamlined uninstall process - preserves user settings and models
* **IMPROVED**: Enhanced script/style enqueueing with proper dependencies
* **IMPROVED**: All vendor dependencies now included locally (Draco, Basis Universal, Three.js)
* **COMPLIANCE**: Full WordPress.org Plugin Directory guidelines compliance
* **GPL**: Verified all dependencies are GPL-compatible

### Version 1.0.1
* Fixed: Premium feature references removed from free version
* Fixed: 3D model delete functionality - added missing AJAX handler
* Fixed: Modal viewer now displays models at full container size
* Fixed: Removed addon integration from free version interface
* Improved: Enhanced security validation for model deletion
* Improved: Better error handling for AJAX operations

### Version 1.0.0
* Initial stable release with complete feature set
* Modern admin interface with comprehensive model management
* Progressive loading system with customizable indicators
* Security framework with input validation and access controls
* Responsive design with device-specific configurations

## 📝 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## 🔗 Links

- **🌐 Official Website**: [expoxr.com](https://expoxr.com)
- **📚 Documentation**: [docs.expoxr.com](https://docs.expoxr.com)
- **🐛 Issue Tracker**: [GitHub Issues](../../issues)
- **💬 Support Forum**: [WordPress.org Support](https://wordpress.org/support/plugin/explorexr/)

## 📊 Stats

![GitHub stars](https://img.shields.io/github/stars/DragneelMC1988/explorexr?style=social)
![GitHub forks](https://img.shields.io/github/forks/DragneelMC1988/explorexr?style=social)
![GitHub issues](https://img.shields.io/github/issues/DragneelMC1988/explorexr)
![GitHub downloads](https://img.shields.io/github/downloads/DragneelMC1988/explorexr/total)

---

**Made with ❤️ by [Ayal Othman](https://expoxr.com)**

*Transform your WordPress website with immersive 3D experiences. Start with ExploreXR today!*
