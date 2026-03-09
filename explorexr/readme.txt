=== ExploreXR – Interactive 3D Model Viewer for WordPress ===
Contributors: expoxr
Tags: 3d viewer, 3d model, glb, woocommerce, elementor
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Interactive 3D models for WordPress. Upload GLB/GLTF files, embed via shortcode, and extend with modular add-ons. No coding required.

== Description ==

**Interactive 3D, Structured For Production.**

ExploreXR is a modular XR platform for WordPress that helps teams publish interactive 3D models and advanced viewer workflows — with performance-aware architecture and clear licensing. Extend to mobile AR and beyond through optional premium add-ons.

Built for businesses, agencies, technical teams, and content owners who need a stable, scalable path to 3D on the web.

[View Pricing](https://expoxr.com/explorexr/preise/) | [Watch The Demo](https://expoxr.com/explorexr/demo/) | [Documentation](https://expoxr.com/explorexr/documentation/) | [Request Free Trial](https://expoxr.com/explorexr/trial-request/)

---

**What ExploreXR Does**

ExploreXR gives WordPress websites a structured way to publish and manage 3D content. Instead of relying on custom front-end work or disconnected viewer tools, teams can:

* Upload models and embed them into pages and product workflows
* Activate advanced capabilities through add-ons instead of a bloated core
* Operate the full system inside a familiar WordPress environment
* Maintain control over performance, loading behavior, and support diagnostics
* Scale from a single-site deployment to multi-site agency use

---

**Free Version Features**

* Display GLB, GLTF, and USDZ 3D models
* Shortcode embedding: `[explorexr_model id="123"]`
* Responsive viewer scaling for desktop, tablet, and mobile
* Camera rotation, zoom, and pan controls
* Basic customization (size, background, controls, poster image)
* Progressive loading with smooth fallback
* Drag-and-drop admin interface
* Debug Toolkit for diagnostics
* Full WordPress Coding Standards compliance
* Compatible with all properly coded themes and page builders

---

**Who ExploreXR Is For**

ExploreXR is designed for teams that need XR capability to function as part of a real website workflow:

* **E-commerce businesses** — stronger product visualization (AR available via Premium add-on)
* **Agencies** — a repeatable way to deliver 3D capability across client sites
* **Architecture and real estate teams** — browser-based model presentation
* **Manufacturing and technical teams** — clearer product communication
* **Education and training teams** — interactive 3D learning content

---

**Premium Add-Ons**

The platform uses a lean core with optional add-ons. Teams activate only the features they need:

* **AR (Augmented Reality)** — iOS Quick Look, Android Scene Viewer, and WebXR
* **Animation Control** — multi-clip playback with crossfade transitions
* **Annotations** — hotspots, labels, dimension lines, and camera-targeted explanations
* **Material Variants** — real-time color, finish, and model state switching
* **Camera & Lighting** — expert camera constraints and HDRI environment lighting
* **Post-Processing** — cinematic visual effects and filters
* **WooCommerce Integration** — 3D models directly on product pages
* **Elementor Widget** — drag-and-drop viewer in Elementor page builder
* **Divi Module** — native integration with Divi builder
* **Avada Integration** — Fusion Element for Avada-based sites
* **Draggable Viewer** — repositionable viewer for advanced layouts
* **Mouse3D Cursor** — 3D-aware pointer control

All 12 add-ons are available on the [ExploreXR Premium plans](https://expoxr.com/explorexr/preise/).

**Try Premium Free For 14 Days — No Credit Card Required.**
[Request your free trial](https://expoxr.com/explorexr/trial-request/)

---

**Why Teams Choose ExploreXR**

= Production-Ready Core =
ExploreXR is built to operate on live websites. Viewer behavior, file handling, device compatibility, and support tooling are all treated as part of the product standard.

= Modular Architecture =
Activate only the features you need. A clear add-on system keeps deployment controlled and creates a straightforward path for growth.

= WordPress-Native Workflow =
Models, content, pages, product data, and publishing decisions remain inside WordPress rather than fragmented across external systems.

= Stability And Performance =
Conditional asset loading, multiple loading strategies, bundled Draco and Basis Universal decoding support, and diagnostics tooling help teams deliver 3D content without losing operational clarity.

= Reliability You Can Depend On =
* Local bundled decoders for Draco and Basis Universal compression
* Multiple loading strategies (direct, lazy, poster-driven)
* Preserved settings when add-ons are deactivated and reactivated
* 90-day grace period after license expiry
* Free diagnostics through the Debug Toolkit
* Roadmap focused on platform expansion, not one-off feature releases

---

**Supported Page Builders**

Works natively with the WordPress Block Editor (Gutenberg), Classic Editor, Elementor, Divi, Avada, and all standard shortcode-compatible builders.

---

**Resources**

* [Plugin Documentation](https://expoxr.com/explorexr/documentation/)
* [View All Add-Ons](https://expoxr.com/explorexr/addons/)
* [Pricing and Plans](https://expoxr.com/explorexr/preise/)
* [Live Demo](https://expoxr.com/explorexr/demo/)
* [Free Trial Request](https://expoxr.com/explorexr/trial-request/)
* [Support Forum](https://wordpress.org/support/plugin/explorexr/)

== Installation ==

= Automatic Installation =
1. Go to **Plugins → Add New**
2. Search for "ExploreXR"
3. Click **Install Now** then **Activate**

= Manual Installation =
1. Download the plugin ZIP from [WordPress.org](https://wordpress.org/plugins/explorexr/)
2. Go to **Plugins → Add New → Upload Plugin**
3. Select the ZIP file and click **Install Now**
4. Activate the plugin via the Plugins menu

= Quick Start =
1. Go to **ExploreXR → Create Model**
2. Upload a GLB or GLTF file
3. Configure viewer settings (size, background, controls)
4. Copy the generated shortcode
5. Paste `[explorexr_model id="123"]` into any page, post, or widget

Full documentation: [https://expoxr.com/explorexr/documentation/](https://expoxr.com/explorexr/documentation/)

== Third-Party Libraries ==

= Google Model Viewer =
* License: Apache 2.0
* Source: [https://github.com/google/model-viewer](https://github.com/google/model-viewer)
* Purpose: Core 3D rendering engine used to display GLB/GLTF/USDZ files in the browser

= Draco Geometry Compression =
* License: Apache 2.0
* Source: [https://github.com/google/draco](https://github.com/google/draco)
* Purpose: Decoding support for Draco-compressed 3D geometry

= Basis Universal Texture Compression =
* License: Apache 2.0
* Source: [https://github.com/BinomialLLC/basis_universal](https://github.com/BinomialLLC/basis_universal)
* Purpose: Decoding support for Basis Universal compressed textures

All libraries are GPL-compatible and bundled locally for performance and compliance.

== Frequently Asked Questions ==

= Which 3D file formats are supported? =
GLB, GLTF, and USDZ. GLB is the recommended format for best compatibility and performance.

= Does ExploreXR require coding knowledge? =
No. Everything is managed through the WordPress admin interface and shortcodes.

= Does it work on all devices? =
Yes. ExploreXR works on all modern browsers and devices that support WebGL, including desktop, tablet, and mobile.

= Is AR (Augmented Reality) included in the free version? =
AR is available in [ExploreXR Premium](https://expoxr.com/explorexr/preise/) via the AR add-on. It supports iOS Quick Look, Android Scene Viewer, and WebXR.

= Is it compatible with my theme? =
Yes. ExploreXR works with all properly coded WordPress themes.

= Will 3D models slow down my site? =
Models load only when the viewer is in view and include progressive loading strategies. Conditional asset loading is built into the core.

= Can I use it with Elementor, Divi, or Avada? =
Basic shortcode embedding works with any page builder. Native widget/module integrations for Elementor, Divi, and Avada are available in Premium.

= Can I use it with WooCommerce? =
WooCommerce product integration is available in [ExploreXR Premium](https://expoxr.com/explorexr/preise/).

= Is there a free trial for Premium? =
Yes. You can try all 12 premium add-ons free for 14 days — no credit card required. [Request your trial here](https://expoxr.com/explorexr/trial-request/).

= Where can I get support? =
Free support is available on the [WordPress.org support forum](https://wordpress.org/support/plugin/explorexr/). Premium users have access to priority support via [expoxr.com](https://expoxr.com/support/).

== Screenshots ==

1. Plugin dashboard — overview of models, files, storage, and system status
2. Create New Model — upload GLB/GLTF files and configure viewer settings
3. Plugin Settings — configure viewer defaults, loading strategy, and CDN options
4. 3D Models Overview — browse, search, and manage all published models
5. Edit 3D Model — fine-tune display options, sizes, poster image, and shortcode

== Changelog ==

= 1.1.0 =
* New: 14-day free trial — try ExploreXR Premium with 4 add-ons of your choice, no credit card required
* New: Elementor widget integration for drag-and-drop 3D viewer placement
* New: Divi module integration for native Divi builder support
* New: Avada Fusion Element integration
* New: Completely redesigned Go Premium page showcasing all 12 premium add-ons with full descriptions
* New: Trial notice directing users to ExpoXR website for the 14-day premium trial
* Updated: Dashboard Upgrade to Premium section now shows all 12 add-ons and trial CTA
* Updated: Premium features metabox on model edit pages lists all available add-ons
* Updated: Feature comparison table expanded with all premium capabilities
* Updated: Recommended add-on combinations section for common use cases
* Fixed: Version constant mismatch resolved
* Improved: Premium feature list expanded from 2 to 13 entries
* Improved: License stub now returns trial status when trial is active

= 1.0.9 =
* New: Unified size presets (Small/Medium/Large/Full) applied to desktop/tablet/mobile meta
* New: Server and admin validation prevents width/height both using % (invisible viewer guard)
* Fixed: Loading option attributes now pass through the correct filter to JS loaders
* Fixed: Admin slug/meta casing and premium URL wrapper

= 1.0.8 =
* Fixed: Database query compliance — replaced direct wpdb queries with WordPress Transients and Cache APIs
* Fixed: Unclosed brace syntax error in cache-manager.php
* Improved: CSS architecture — created shared components.css reducing duplicate code by 336 lines
* Improved: Admin form layouts use CSS Grid with 2-column responsive design
* Enhanced: Cache management uses proper WordPress caching mechanisms
* Enhanced: All PHPCS warnings resolved — full WordPress Coding Standards compliance

= 1.0.7 =
* Fixed: Custom tablet and mobile sizes now properly apply on frontend
* Fixed: Removed unwanted character from admin page titles
* Fixed: WordPress.org security compliance — replaced wp_redirect() with wp_safe_redirect()
* Fixed: All WordPress Coding Standards violations resolved — passes Plugin Check with zero errors
* Improved: Poster image preview in both Upload and Media Library tabs
* Security: Enhanced nonce verification and input sanitization across all admin forms

= 1.0.6 =
* Initial public release on WordPress.org
* Core 3D viewer, shortcode, admin interface, and model upload system

== Upgrade Notice ==

= 1.1.0 =
Adds native Elementor, Divi, and Avada integrations plus a 14-day free trial for Premium. Update recommended.

= 1.0.6 =
Initial release. Install to start displaying 3D models in WordPress.

== Privacy Policy ==

ExploreXR does not collect, store, or transmit any personal data. All 3D rendering occurs entirely within the visitor's browser. No external tracking, analytics, or user data collection is performed by this plugin.

For premium license validation, only a license key is transmitted to ExpoXR servers. No personal user data is included. See the [ExpoXR Privacy Policy](https://expoxr.com/privacy-policy/) for full details.

== Support ==

Free support via the WordPress.org forum:
[https://wordpress.org/support/plugin/explorexr/](https://wordpress.org/support/plugin/explorexr/)

Premium support and documentation:
[https://expoxr.com/support/](https://expoxr.com/support/)

== Credits ==

Powered by [Google Model Viewer](https://modelviewer.dev/) and open-source contributors.
Built by [Ayal Othman](https://expoxr.com) — ExpoXR.
