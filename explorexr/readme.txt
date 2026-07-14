=== ExploreXR ===
Contributors: expoxr
Tags: 3d model viewer, glb, gltf, augmented reality, woocommerce 3d
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.3.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free 3D model viewer for WordPress with AR or animation support. Embed GLB, GLTF, and USDZ models, plus choose one Premium Addon.

== Description ==

**ExploreXR — Free 3D Model Viewer for WordPress.**

ExploreXR brings interactive 3D models to your WordPress site using Google's `<model-viewer>` element. Upload GLB / GLTF / USDZ files, embed them anywhere with a shortcode, and pair the viewer with one Premium Addon of your choice (AR Addon, Animation Addon, or Loading Options Addon).

For multi-addon production setups, upgrade to [ExploreXR Premium](https://expoxr.com/explorexr/pricing/) for 3, 5, or unlimited addons.

[Live Demo](https://expoxr.com/explorexr/demo/) | [Documentation](https://expoxr.com/explorexr/documentation/) | [Pricing](https://expoxr.com/explorexr/pricing/)

---

**Free Version — The 3D Viewer + One Premium Addon**

ExploreXR Free gives you a clean, capable 3D viewer you can embed anywhere on your WordPress site, **plus one Premium Addon of your choice**:

* Display interactive GLB, GLTF, and USDZ 3D models in any post, page, or widget
* Embed with shortcode: `[explorexr_model id="123"]`
* Fully responsive — scales correctly on desktop, tablet, and mobile with per-breakpoint sizing
* Camera controls: rotation, zoom, pan, and touch gestures
* Auto-rotation with configurable speed, delay, and direction
* Poster image support with lazy loading and "click to load" mode
* Per-model load behavior: direct, poster + button, or lazy via IntersectionObserver
* Customizable Load Model button with configurable text, colors, and radius
* Bundled Draco geometry, KTX2/Basis Universal texture, and Meshopt decoders — no CDN needed
* Drag-and-drop model upload through the WordPress admin
* Conditional asset loading — scripts only enqueue on pages with a model shortcode
* Nonce-protected admin actions and strict file upload validation
* Full WordPress Coding Standards compliance, tested on WordPress 5.8 through 7.0

**Choose Your Included Premium Addon:**

* **AR Addon** — iOS Quick Look, Android Scene Viewer, and WebXR augmented reality
* **Animation Addon** — Play, pause, loop, and ping-pong glTF animation clips with crossfade transitions
* **Loading Options Addon** — Custom loading bars, percentage counters, and loading overlays

Each addon installs as a separate plugin from WordPress.org via the **ExploreXR → Addons** page. You can only activate one at a time. To use multiple addons simultaneously, upgrade to ExploreXR Premium.

---

**Who Uses ExploreXR**

* **E-commerce stores** — let shoppers rotate and inspect products before buying (AR available via included addon)
* **Product designers and manufacturers** — present technical models with precision and clarity
* **Architecture and real estate teams** — browser-based model presentations without external tools
* **Agencies** — a repeatable system for delivering 3D capability across multiple client sites
* **Education and training teams** — interactive 3D models embedded directly into course content

---

---

**ExploreXR Premium — The Add-On Platform**

Premium is not just "more features" — it is a different architecture. Where the free version gives you one addon, Premium is a structured add-on system: a lean core with optional capability modules you activate individually. Your site only loads the code you actually use.

= Performance-Aware Architecture =
Conditional asset loading, multiple loading strategies (direct, lazy, or poster-driven), and built-in Draco, Basis Universal, and Meshopt compression decoders ensure heavy 3D assets do not slow your site down. Heavy models, clean Core Web Vitals.

= Modular Add-On System =
A lean core with optional premium add-ons — AR, material variants, animation controls, WooCommerce integration, and more. Activate only the exact capabilities you need. Nothing else loads. No bloat.

= Broad Ecosystem Compatibility =
Complies strictly with WordPress coding standards and works natively with the Block Editor, Classic Editor, and major page builders — Elementor, Divi, and Avada. Tested against every WordPress release including WordPress 7.

**Premium Plans:**

* **Pro** — 3 addon slots
* **Plus** — 5 addon slots
* **Ultra** — Unlimited addons

All plans include priority support and multi-site licensing options (1 / 5 / 25 site plans).

**Available Add-Ons:**

* **AR (Augmented Reality)** — iOS Quick Look, Android Scene Viewer, and WebXR in one add-on
* **Animation Control** — Multi-clip playback with configurable crossfade transitions
* **Annotations** — Interactive hotspots, labels, dimension lines, and camera-targeted explanations
* **Material Variants** — Real-time color, finish, and model state switching without page reload
* **Camera & Lighting** — Expert camera constraints and HDRI environment lighting
* **Post-Processing** — Cinematic visual effects and filters applied directly to the viewer
* **Loading Options** — Custom loading bars, percentage counters, and loading overlays
* **WooCommerce Integration** — Attach 3D models directly to product pages
* **Elementor Widget** — Native drag-and-drop 3D viewer block inside the Elementor editor
* **Divi Module** — Native integration within the Divi visual builder
* **Avada Integration** — Fusion Element for Avada-based sites
* **Draggable Viewer** — User-repositionable viewer for advanced editorial layouts
* **Mouse3D Cursor** — 3D-aware pointer for interactive presentation experiences

All add-ons are included in [ExploreXR Premium plans](https://expoxr.com/explorexr/pricing/).

**Try Premium Free For 14 Days — No Credit Card Required.**
[Request your free trial](https://expoxr.com/explorexr/trial-request/)

---

**Supported Page Builders**

ExploreXR works with:

* WordPress Block Editor (Gutenberg)
* Classic Editor
* Elementor (free version: shortcode; Premium: native widget)
* Divi (free version: shortcode; Premium: native module)
* Avada / Fusion Builder (free version: shortcode; Premium: native element)
* Any shortcode-compatible builder

---

**Resources**

* [Plugin Documentation](https://expoxr.com/explorexr/documentation/)
* [Tutorial: Build an Interactive 3D Design](https://youtu.be/RTTJ6lX6uXw?si=KeMRrSABHfcf1vmr)
* [Live Demo](https://expoxr.com/explorexr/demo/)
* [View All Add-Ons](https://expoxr.com/explorexr/addons/)
* [Pricing and Plans](https://expoxr.com/explorexr/pricing/)
* [Free Trial Request](https://expoxr.com/explorexr/trial-request/)
* [Support Forum](https://wordpress.org/support/plugin/explorexr/)
* [Premium Support](https://expoxr.com/support/)

== Installation ==

= Automatic Installation (Recommended) =
1. Go to **Plugins → Add New** in your WordPress dashboard
2. Search for **ExploreXR**
3. Click **Install Now** then **Activate**

= Manual Installation =
1. Download the plugin ZIP from [WordPress.org](https://wordpress.org/plugins/explorexr/)
2. Go to **Plugins → Add New → Upload Plugin**
3. Select the ZIP file and click **Install Now**
4. Activate the plugin via the Plugins menu

= Quick Start: Embed Your First 3D Model =
1. Go to **ExploreXR → Create Model** in the WordPress admin
2. Upload a GLB or GLTF file (GLB recommended for best performance)
3. Set viewer size, background, controls, and optional poster image
4. Copy the generated shortcode (e.g. `[explorexr_model id="123"]`)
5. Paste the shortcode into any post, page, widget, or page builder block
6. Visit **ExploreXR → Addons** to install your included Premium Addon (AR, Animation, or Loading Options)

Full documentation: [https://expoxr.com/explorexr/documentation/](https://expoxr.com/explorexr/documentation/)

== Third-Party Libraries ==

= Google Model Viewer =
* License: Apache 2.0
* Source: [https://github.com/google/model-viewer](https://github.com/google/model-viewer)
* Purpose: Core 3D rendering engine used to display GLB, GLTF, and USDZ files in the browser

= Draco Geometry Compression =
* License: Apache 2.0
* Source: [https://github.com/google/draco](https://github.com/google/draco)
* Purpose: Decoding support for Draco-compressed 3D geometry — reduces model file size significantly

= Basis Universal Texture Compression =
* License: Apache 2.0
* Source: [https://github.com/BinomialLLC/basis_universal](https://github.com/BinomialLLC/basis_universal)
* Purpose: Decoding support for Basis Universal compressed textures — improves texture loading performance

= Meshoptimizer =
* License: MIT
* Source: [https://github.com/zeux/meshoptimizer](https://github.com/zeux/meshoptimizer)
* Purpose: Decoding support for meshopt-compressed geometry and attributes

All libraries are GPL-compatible and bundled locally. No CDN dependency or external request is required to render 3D models.

== Frequently Asked Questions ==

= What is the difference between the free version and Premium? =
The free version includes a 3D viewer plus **one Premium Addon of your choice** (AR, Animation, or Loading Options). ExploreXR Premium is a full add-on platform built on a modular architecture, giving you 3, 5, or unlimited addon slots depending on your plan — including material variants, WooCommerce integration, native page builder widgets, and more.

= How many addons can I run on the free version? =
Exactly one, from the curated list: AR Addon, Animation Addon, or Loading Options Addon. Activating a second one will be blocked with a notice. To use multiple addons simultaneously, upgrade to ExploreXR Premium.

= Which 3D file formats does ExploreXR support? =
GLB, GLTF, and USDZ. GLB (binary GLTF) is the recommended format — it is self-contained, loads faster, and has the broadest device support. USDZ is optimized for iOS AR workflows.

= How do I embed a 3D model on a WordPress page? =
Create a model under **ExploreXR → Create Model**, then paste the shortcode `[explorexr_model id="123"]` into any post, page, widget, or page builder block. That is all.

= Does ExploreXR require coding knowledge? =
No. The entire workflow — uploading models, configuring the viewer, embedding on pages — is handled through the WordPress admin interface. No PHP, JavaScript, or CSS knowledge is required.

= Does it work on all devices and browsers? =
Yes. ExploreXR works on all modern browsers and devices that support WebGL, including desktop (Chrome, Firefox, Safari, Edge), tablet, and mobile.

= Will 3D models slow down my WordPress site? =
Not when configured correctly. ExploreXR uses conditional asset loading (scripts only load on pages with a 3D model), multiple loading strategies (direct, lazy, and poster-driven for large files), and locally bundled decoders — so models are compressed and loading is deferred until needed.

= Does ExploreXR work with Elementor, Divi, and Avada? =
Yes. The free version works with all three via shortcode. ExploreXR Premium adds native drag-and-drop widgets and modules for Elementor, Divi, and Avada.

= Is AR (Augmented Reality) included in the free version? =
Yes! AR is available as one of the three included Premium Addons. Install the AR Addon from **ExploreXR → Addons** to get iOS Quick Look, Android Scene Viewer, and WebXR support.

= Why can I not install other ExploreXR addons? =
The free version is locked to three included Premium Addons: AR Addon, Animation Addon, and Loading Options Addon. Any additional addons or multiple-addon setups require ExploreXR Premium.

= Can I run ExploreXR and ExploreXR Premium at the same time? =
No. If Premium is activated, the free plugin self-deactivates to prevent conflicts. Your models and meta data are preserved.

= Is ExploreXR compatible with WooCommerce? =
WooCommerce product page integration is available in [ExploreXR Premium](https://expoxr.com/explorexr/pricing/).

= Is there a free trial for Premium? =
Yes. You can try all premium add-ons free for 14 days — no credit card required. [Request your trial here](https://expoxr.com/explorexr/trial-request/).

= Is ExploreXR compatible with WordPress 7? =
Yes. ExploreXR is tested against WordPress 7.x and follows WordPress Coding Standards to ensure long-term compatibility across every major WordPress release.

= What happens to my models if I deactivate the plugin? =
Your model files and all settings are preserved. Deactivating the plugin does not delete any data. Reactivate and everything will be exactly as you left it.

= Where can I get support? =
Free support is available on the [WordPress.org support forum](https://wordpress.org/support/plugin/explorexr/). Premium users have access to priority support via [expoxr.com/support/](https://expoxr.com/support/).

== Screenshots ==

1. ExploreXR Dashboard — model count, file storage, system status, and quick actions in one clean overview
2. Create New 3D Model — drag-and-drop GLB, GLTF, or USDZ upload with an instant live 3D preview before you publish
3. Global Loading Settings — configure lazy loading, poster-click-to-load, and direct load strategies so large 3D models never slow your site down
4. Add-ons Page — one-click install and activation of your included Premium Addon: AR, Animation, or Loading Options
5. Edit 3D Model — the full model editor: responsive sizing, camera controls, auto-rotation, and per-model settings in one screen
6. Add-ons Section (Edit 3D Model) — configure your active Premium Addon's settings directly from the model editor, no extra screens
7. Poster Image & Viewer Controls — set a custom poster image plus camera zoom, pan, and interaction prompts for a polished first impression
8. Animation Addon Controls (Edit 3D Model) — play, pause, loop, and crossfade glTF animation clips with full player controls
9. AR Addon Options (Edit 3D Model) — enable iOS Quick Look, Android Scene Viewer, and WebXR augmented reality per model
10. AR Button Styling (Edit 3D Model) — customize the AR launch button's color, text, icon, and placement to match your brand
11. Loading Options Addon (Edit 3D Model) — build a custom loading bar, percentage counter, or branded overlay for each individual 3D model

== Changelog ==

= 1.3.2 =
* Fixed: Free-tier addon whitelist corrected to AR, Animation, and Loading Options (was incorrectly allowing Camera Controls and Annotations, which are Premium-only, while blocking Loading Options).
* Fixed: Closed a race condition where two addons could briefly end up simultaneously active during install.
* Fixed: Deactivation-notice script and admin bar link no longer silently fail due to a plugin-path case mismatch.
* Fixed: A plugin-wide button style was forcing its own color onto the Loading Options color pickers, so picked colors never displayed correctly.
* Added: Proper WordPress color pickers for the Load Model button colors, with automatic text contrast.
* Removed: Dead code, duplicate admin screens, and unused legacy files.
* Tested up to WordPress 7.0.

= 1.3.1 =
* Fixed: Admin page styling and layout improvements
* Fixed: Free addon selection UI refinements
* Fixed: Dashboard statistics display accuracy
* Improved: Admin navigation consistency
* Improved: Error messages and user feedback

= 1.3.0 =
* Initial WordPress.org release of the free ExploreXR plugin.
* Built on the same rendering core as ExploreXR Premium 1.3.1.
* Single-addon limit with curated whitelist (AR Addon, Animation Addon, Loading Options Addon).
* Compression decoders bundled: Draco, KTX2/Basis Universal, Meshopt.
* Per-model load behavior override (direct / poster + button / lazy).
* Customisable Load Model button.
* New Go Premium page with feature comparison.
* Adds global and per-model lazy loading, Load Model button customization, fixes large model size detection, and enables Draco/Meshopt/Basis texture compression.
* Fixes PHP fatal error on duplicate plugin activation.
* Fixes GLB/GLTF/USDZ upload failures caused by PHP finfo MIME detection.

== Upgrade Notice ==

= 1.3.2 =
Corrects the free-tier addon whitelist (AR, Animation, Loading Options), closes an addon-activation race condition, fixes the Loading Options color pickers, and removes legacy/dead code. Tested up to WordPress 7.0. Update recommended for all users.

= 1.3.1 =
Minor bug fixes and admin UI improvements. Update recommended for better user experience.

= 1.3.0 =
Initial free release with one included Premium Addon (AR, Animation, or Loading Options), compression decoders, per-model loading strategies, and customizable Load Model button. Recommended for all users.

= 1.2.0 =
Adds one included Premium Addon of your choice (AR Addon, Animation Addon, or Loading Options Addon) plus fixes GLB/GLTF file upload failures. Update recommended for all users.

= 1.1.0 =
Adds native Elementor, Divi, and Avada integrations plus a 14-day free trial for Premium. Update recommended.

= 1.0.6 =
Initial release. Install to start displaying interactive 3D models in WordPress.


== Privacy Policy ==

ExploreXR does not collect, store, or transmit any personal data. All 3D rendering occurs entirely within the visitor's browser using locally bundled libraries. No external tracking, analytics, or user data collection is performed by this plugin.

For premium license validation, only a license key is transmitted to ExpoXR servers. No personal user data is included. See the [ExpoXR Privacy Policy](https://expoxr.com/privacy-policy/) for full details.

== Support ==

Free support via the WordPress.org support forum:
[https://wordpress.org/support/plugin/explorexr/](https://wordpress.org/support/plugin/explorexr/)

Premium support and documentation:
[https://expoxr.com/support/](https://expoxr.com/support/)

== Credits ==

Powered by [Google Model Viewer](https://modelviewer.dev/) and open-source contributors.
Built by [Ayal Othman](https://expoxr.com) — ExpoXR.
