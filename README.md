# 🌟 ExploreXR - 3D Model Viewer Plugin


[![WordPress Compatible](https://img.shields.io/badge/WordPress-5.0%2B-0073aa.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-8892bf.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-007ec6.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.3.5-brightgreen.svg)](https://github.com/ExpoXR/ExploreXR/releases)

## 🌐 Transform Your WordPress Site with Interactive 3D Content

ExploreXR brings the power of interactive 3D models to your WordPress website with zero coding required. Using Google's industry-leading Model Viewer technology, your visitors can interact with stunning 3D content directly in their browser—rotating, zooming, and even viewing products in their own space through AR.

**Perfect for:** E-commerce stores, product showcases, portfolios, museums, educational sites, real estate listings, architectural firms, and any website looking to engage users with immersive 3D experiences.

## ✨ Features That Make ExploreXR Special (Free Edition)

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
      <h3>🧭 Seamless Admin Flow</h3>
      <ul>
        <li><b>Custom Editor</b> - Guided create/edit pages for models</li>
        <li><b>Responsive Presets</b> - One-click Small/Medium/Large/Full with device breakpoints</li>
        <li><b>Device Overrides</b> - Tablet and mobile size meta saved explicitly</li>
        <li><b>Secure Uploads</b> - Sanitized GLB/GLTF/USDZ handling into uploads/explorexr_models</li>
        <li><b>Accessibility Ready</b> - Alt text, interaction prompts</li>
        <li><b>Admin Notices</b> - Error/validation feedback built-in</li>
        <li><b>Page Builder Integrations</b> - Elementor, Divi, Avada, Gutenberg widgets included</li>
      </ul>
    </td>
    <td width="33%">
      <h3>⚙️ Advanced Controls</h3>
      <ul>
        <li><b>Loading Attributes</b> - Localized data-attrs for the JS loader</li>
        <li><b>Safe Dimensions</b> - Percent/percent combos auto-corrected to visible sizes</li>
        <li><b>Clean Data Management</b> - Import/export and uninstall options</li>
        <li><b>Performance Tools</b> - Progressive loading + lazy posters</li>
        <li><b>Developer Friendly</b> - Shortcode hooks, model-viewer filters</li>
        <li><b>Robust Security</b> - Nonces, capability checks, file scanners</li>
        <li><b>Free Premium Add-on</b> - Choose one premium add-on for free: AR, Animation, or Loading Options</li>
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
- **AR Mode on Mobile** - USDZ for iOS, WebXR for Android (via free AR add-on)

## 🎥 See It In Action

<p align="center">
  <img src="https://github.com/ExpoXR/ExploreXR/raw/main/assets/img/screenshots/demo-showcase.gif" alt="ExploreXR Demo" width="700"/>
</p>

<details>
  <summary><b>📸 View More Screenshots</b></summary>
  
  <h4>Admin Dashboard</h4>
  <img src="https://github.com/ExpoXR/ExploreXR/raw/main/assets/img/screenshots/admin-dashboard.jpg" alt="Admin Dashboard" width="600"/>
  
  <h4>Model Management</h4>
  <img src="https://github.com/ExpoXR/ExploreXR/raw/main/assets/img/screenshots/model-management.jpg" alt="Model Management" width="600"/>
  
  <h4>AR Mode on Mobile</h4>
  <img src="https://github.com/ExpoXR/ExploreXR/raw/main/assets/img/screenshots/ar-mode-mobile.jpg" alt="AR Mode on Mobile" width="400"/>
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


## 🎨 Usage Examples

### Basic Shortcode
```
[explorexr_model id="123"]
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

## 🚦 Performance Optimization

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

## 🚀 Free Premium Add-on (Choose One)

ExploreXR Free includes one premium add-on of your choice. You can activate one of the following add-ons from the **ExploreXR → Free Add-ons** admin page:

- **AR Addon** — Enable augmented reality on mobile, including USDZ support for iOS and WebXR support for Android
- **Animation Addon** — Play, pause, loop, and control GLTF animations directly inside the 3D viewer
- **Loading Options Addon** — Customize the loading experience with poster images, loading behavior, and smoother model presentation

## 🌟 Premium Add-ons

Unlock all 12 add-ons plus priority support. Details at [expoxr.com/explorexr/premium](https://expoxr.com/explorexr/premium/).

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📋 Changelog (Highlights)


### 1.3.0
* Fixed: GLB/GLTF/USDZ uploads failing with "Invalid file type" due to PHP finfo MIME detection mismatch
* Added: `upload_mimes` filter registering correct MIME types for all 3D model formats
* Added: `wp_check_filetype_and_ext` filter handling finfo returning `application/octet-stream`
* Improved: Conflict notice distinguishes Premium-vs-Free from Free-vs-Free duplicate scenarios
* Added: `function_exists()` guards on all file-scope functions

### 1.2.0
* Fixed: GLB/GLTF/USDZ upload MIME type validation now accepts all three formats reliably
* New: Free add-on system — activate one premium add-on (AR, Animation, or Loading Options) at no cost
* New: Free Add-ons admin page with one-click install and activation
* New: Conflict prevention blocks simultaneous activation with ExploreXR Premium

### 1.1.x
* Stability and compatibility improvements
* PHPCS escaping compliance updates

### 1.0.9
* New: Canonical display size presets (Small/Medium/Large/Full) applied across create/edit/shortcode
* New: Tablet and mobile size meta now populated for presets (no silent fallbacks)
* New: %/% dimension guard in admin + backend + shortcode to prevent invisible viewers
* Fixed: Loading attribute filter alignment so data-* reach the JS loader
* Fixed: Admin CPT slug/meta casing, premium URL wrapper, and existing file constant
* UI: Added early-adopter 50% banner on Dashboard and Go Premium pages

### 1.0.8
* Database query compliance and cache-manager brace fix
* Shared admin components CSS, grid-based layouts, and PHPCS cleanup
* Cache management now uses WP cache APIs; improved styling and focus states

### 1.0.7
* Responsive device sizes honored on frontend; admin title fixes and nonce/sanitization improvements
* Poster previews and upload tab UX refinements

### 1.0.6
* Initial public release: core 3D viewer, shortcode, admin UI, progressive loading, security framework

## 📝 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## 🔗 Links

- **🌐 Official Website**: [expoxr.com](https://expoxr.com)
- **📚 Documentation**: [expoxr.com/explorexr/documentation](https://expoxr.com/explorexr/documentation/)
- **🐛 Issue Tracker**: [GitHub Issues](../../issues)
- **💬 Support Forum**: [WordPress.org Support](https://wordpress.org/support/plugin/explorexr/)

## 📊 Stats

![GitHub stars](https://img.shields.io/github/stars/DragneelMC1988/explorexr?style=social)
![GitHub forks](https://img.shields.io/github/forks/DragneelMC1988/explorexr?style=social)
![GitHub issues](https://img.shields.io/github/issues/DragneelMC1988/explorexr)
![GitHub downloads](https://img.shields.io/github/downloads/ExpoXR/ExploreXR/total)

---

**Made with ❤️ by [Ayal Othman](https://expoxr.com)**

*Transform your WordPress website with immersive 3D experiences. Start with ExploreXR today!*
