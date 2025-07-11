# 🌟 ExploreXR - WordPress 3D Model Viewer Plugin

[![WordPress Compatible](https://img.shields.io/badge/WordPress-5.0%2B-0073aa.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-8892bf.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-007ec6.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.0.0-brightgreen.svg)](https://github.com/DragneelMC1988/explorexr/releases)

## 🌐 Transform Your WordPress Site with Interactive 3D Content

ExploreXR brings the power of interactive 3D models to your WordPress website with zero coding required. Using Google's industry-leading Model Viewer technology, your visitors can interact with stunning 3D content directly in their browser—rotating, zooming, and even viewing products in their own space through AR.

**Perfect for:** E-commerce stores, product showcases, portfolios, museums, educational sites, real estate listings, architectural firms, and any website looking to engage users with immersive 3D experiences.

## ✨ Features That Make ExploreXR Special

<table>
  <tr>
    <td width="33%">
      <h3>🎯 Core Capabilities</h3>
      <ul>
        <li><b>Simple Model Management</b> - Upload GLB/GLTF/USDZ with ease</li>
        <li><b>Intuitive Dashboard</b> - Streamlined model organization</li>
        <li><b>Flexible Shortcode System</b> - Place models anywhere</li>
        <li><b>Responsive Design</b> - Perfect on all devices</li>
        <li><b>Progressive Loading</b> - Smooth user experience</li>
        <li><b>Comprehensive Error Handling</b> - Never leave users hanging</li>
      </ul>
    </td>
    <td width="33%">
      <h3>� Seamless Integrations</h3>
      <ul>
        <li><b>Elementor Widget</b> - Visual drag-and-drop placement</li>
        <li><b>WooCommerce Support</b> - 3D product visualization</li>
        <li><b>Augmented Reality</b> - View models in your space</li>
        <li><b>Universal Theme Support</b> - Works everywhere</li>
        <li><b>Accessibility Ready</b> - Inclusive design principles</li>
        <li><b>GDPR Compliant</b> - Privacy-focused implementation</li>
      </ul>
    </td>
    <td width="33%">
      <h3>⚙️ Advanced Controls</h3>
      <ul>
        <li><b>Custom Loading Experience</b> - Branded loading screens</li>
        <li><b>Device-specific Settings</b> - Optimize for any screen</li>
        <li><b>Clean Data Management</b> - Import/export and uninstall options</li>
        <li><b>Performance Tools</b> - Optimization for fast loading</li>
        <li><b>Developer Friendly</b> - Extensive hooks and filters</li>
        <li><b>Robust Security</b> - Enterprise-grade protection</li>
      </ul>
    </td>
  </tr>
</table>

### 🎬 Model Showcase Made Simple

ExploreXR handles the technical complexities so you can focus on showcasing your 3D content:

- **Drag-and-drop Uploads** - No technical knowledge needed
- **Instant Previews** - See your models before publishing
- **Multiple Display Options** - Control size, position, and behavior
- **Interactive Controls** - Zoom, rotate, pan with intuitive controls
- **Cross-browser Support** - Consistent experience across all major browsers

## � See It In Action

<p align="center">
  <img src="https://github.com/DragneelMC1988/explorexr/raw/main/assets/img/screenshots/demo-showcase.gif" alt="ExploreXR Demo" width="700"/>
</p>

<details>
  <summary><b>📸 View More Screenshots</b></summary>
  
  <h4>Admin Dashboard</h4>
  <img src="https://github.com/DragneelMC1988/explorexr/raw/main/assets/img/screenshots/admin-dashboard.jpg" alt="Admin Dashboard" width="600"/>
  
  <h4>Model Management</h4>
  <img src="https://github.com/DragneelMC1988/explorexr/raw/main/assets/img/screenshots/model-management.jpg" alt="Model Management" width="600"/>
  
  <h4>AR Mode on Mobile</h4>
  <img src="https://github.com/DragneelMC1988/explorexr/raw/main/assets/img/screenshots/ar-mode-mobile.jpg" alt="AR Mode on Mobile" width="400"/>
</details>

## 🚀 Quick Start Guide

### Installation

```bash
# Option 1: WordPress Admin
1. Download the ZIP from GitHub
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the ZIP file and click "Install Now"
4. Click "Activate Plugin"

# Option 2: Manual Installation
1. Download and unzip the plugin
2. Upload the 'explorexr' folder to /wp-content/plugins/
3. Activate through the WordPress admin interface
```

### Your First 3D Model in 60 Seconds

1. **Navigate** to "ExploreXR → Create Model" in your WordPress admin
2. **Upload** your GLB/GLTF file via drag-and-drop
3. **Configure** basic settings (size, position, controls)
4. **Save** your model and copy the generated shortcode
5. **Paste** the shortcode into any post or page:

```
[explorexr_model id="123" width="100%" height="400px" auto-rotate="true"]
```

### System Requirements

<table>
  <tr>
    <th>Requirement</th>
    <th>Minimum</th>
    <th>Recommended</th>
  </tr>
  <tr>
    <td>WordPress</td>
    <td>5.0+</td>
    <td>6.0+</td>
  </tr>
  <tr>
    <td>PHP</td>
    <td>7.4+</td>
    <td>8.0+</td>
  </tr>
  <tr>
    <td>Memory Limit</td>
    <td>64MB</td>
    <td>128MB+</td>
  </tr>
  <tr>
    <td>Browser</td>
    <td>Modern browsers with WebGL support</td>
    <td>Chrome, Firefox, Safari, Edge (latest versions)</td>
  </tr>
</table>

## � Plugin Structure

```
explorexr/
├── 📄 explorexr.php           # Main plugin file
├── 📄 readme.txt              # WordPress.org plugin readme
├── 📄 uninstall.php           # Clean uninstall handler
├── 📂 admin/                  # Admin interface & dashboard
│   ├── 📂 ajax/               # AJAX handlers
│   ├── 📂 core/               # Core admin functionality
│   ├── 📂 css/                # Admin stylesheets
│   ├── 📂 js/                 # Admin JavaScript
│   ├── 📂 models/             # Model management interface
│   ├── 📂 pages/              # Admin page templates
│   ├── 📂 settings/           # Settings management
│   └── 📂 templates/          # Admin UI templates
├── 📂 assets/                 # Frontend resources
│   ├── 📂 css/                # Frontend stylesheets
│   ├── 📂 js/                 # Frontend JavaScript
│   └── 📂 img/                # Images and icons
├── 📂 includes/               # Core plugin functionality
│   ├── 📄 shortcodes.php      # Shortcode implementation
│   ├── 📂 core/               # Core functions
│   ├── 📂 integrations/       # Third-party integrations
│   ├── 📂 models/             # Model handling
│   ├── 📂 premium/            # Premium features
│   ├── 📂 security/           # Security functions
│   ├── 📂 ui/                 # UI components
│   └── 📂 utils/              # Utility functions
├── 📂 models/                 # Sample/demo models
└── 📂 template-parts/         # Frontend templates
    ├── 📄 large-model-template.php
    ├── 📄 model-viewer-script.php
    └── 📄 standard-model-template.php
```

## 🎨 Usage Examples

### Basic Shortcode
```
[explorexr_model id="123"]
```

### Advanced Shortcode with Options
```
[explorexr_model id="123" width="800px" height="600px" auto-rotate="true" controls="orbit" ar="true"]
```

### PHP Template Integration
```php
<?php
// Display model in theme template
if (function_exists('explorexr_display_model')) {
    explorexr_display_model(123, array(
        'width' => '100%',
        'height' => '500px',
        'auto-rotate' => true
    ));
}
?>
```

## 🔧 Configuration

### Model Display Options
- **Dimensions**: Custom width/height or responsive sizing
- **Controls**: Orbit, zoom, pan, auto-rotate
- **Loading**: Custom loading text, progress indicators
- **AR Mode**: Enable/disable augmented reality viewing
- **Camera**: Default position, field of view, limits

### Device-Specific Settings
Configure different display options for:
- �️ **Desktop** (1024px+)
- 📱 **Tablet** (768px - 1023px)  
- 📱 **Mobile** (< 768px)

## � Performance Optimization

ExploreXR includes multiple features to ensure optimal performance:

- **Progressive Loading** - Models load in stages to minimize perceived wait time
- **Lazy Loading** - Models only load when they come into view
- **Custom Poster Images** - Display static images until model loads
- **Device-Specific Settings** - Automatically adjust quality based on device capabilities
- **Compression Support** - Compatible with Draco and other compressed formats
- **Optimized Assets** - Efficient loading of required scripts and styles
- **Caching** - Browser caching for improved repeat visits

## 🔒 Security & Privacy

ExploreXR is built with security as a top priority:

- **File Validation** - Strict checking of uploaded model files
- **Sanitized Input** - All user inputs are properly sanitized
- **Capability Checks** - WordPress permission system integration
- **NONCE Protection** - Protection against CSRF attacks
- **XSS Prevention** - Proper output escaping throughout
- **GDPR Compliance** - No personal data collection
- **Clean Code** - Following WordPress security best practices

## 🚀 Future Premium Add-ons

ExploreXR is designed with a modular architecture to support premium add-ons:

- **🎥 Camera Controls Add-on** - Advanced camera presets and controls
- **📍 Annotations Add-on** - Interactive hotspots and information panels  
- **🎨 Materials Add-on** - Material variant switching and customization
- **🎬 Animation Add-on** - Advanced animation controls and sequencing
- **🛒 E-commerce Add-on** - Enhanced WooCommerce integration
- **📊 Analytics Add-on** - Detailed engagement tracking and insights

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

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

**Made with ❤️ by [Ayal Othman](https://expoxr.de)**

*Transform your WordPress website with immersive 3D experiences. Start with ExploreXR today!*
