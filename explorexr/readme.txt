=== ExploreXR ===
Contributors: expoxr
Tags: 3d model viewer, glb, gltf, augmented reality, woocommerce 3d
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.3.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free 3D model viewer for WordPress. Embed GLB, GLTF, and USDZ models with Google model-viewer. Includes one Premium Addon of your choice.

== Description ==

**ExploreXR — Free 3D Model Viewer for WordPress.**

ExploreXR brings interactive 3D models to your WordPress site using Google's `<model-viewer>` element. Upload GLB / GLTF / USDZ files, embed them anywhere with a shortcode, and pair the viewer with one Premium Addon of your choice (AR Addon, Animation Addon, or Loading Options Addon).

For multi-addon production setups, upgrade to [ExploreXR Premium](https://expoxr.com/explorexr/pricing/) for 3, 5, or unlimited addons.

[Live Demo](https://expoxr.com/explorexr/demo/) | [Documentation](https://expoxr.com/explorexr/documentation/) | [Pricing](https://expoxr.com/explorexr/pricing/)

== Core Features ==

* Embed interactive GLB, GLTF, and USDZ models in any post, page, or widget
* Shortcode: `[explorexr_model id="123"]` — works with Elementor, Divi, Avada, and any builder
* Fully responsive viewer with per-breakpoint sizing (desktop, tablet, mobile)
* Camera controls: orbit, zoom, pan, touch gestures
* Auto-rotation with configurable speed, delay, direction
* Poster image with lazy loading and "click to load" mode
* Per-model load behavior: direct, poster + button, or lazy via IntersectionObserver
* Bundled Draco, KTX2/Basis Universal, and Meshopt decoders — no CDN dependency
* Drag-and-drop upload in WordPress admin
* Conditional asset loading — scripts only enqueue on pages with a model shortcode
* Customisable Load Model button (text, colors, radius)

== Free Addons ==

ExploreXR Free includes **one Premium Addon of your choice**. You can activate one addon at a time from this curated list:

* **AR Addon** — iOS Quick Look, Android Scene Viewer, WebXR
* **Animation Addon** — play, pause, loop, ping-pong glTF animation clips
* **Loading Options Addon** — custom loading bars, percentage counters, overlays

Each addon installs as a separate plugin from WordPress.org. The free version blocks any second addon from activating and any non-listed addon entirely. To use more than one addon, upgrade to ExploreXR Premium.

== Going Premium ==

ExploreXR Premium adds:

* 3 / 5 / unlimited addon slots (Pro / Plus / Ultra tiers)
* Additional commercial addons and expanded addon slots for professional projects
* Priority email or VIP support
* Multi-site licensing (1 / 5 / 25 site plans)

See the in-plugin **Go Premium** page for the full comparison.

== Installation ==

1. Upload the `explorexr` folder to `/wp-content/plugins/` (or install via WP admin).
2. Activate **ExploreXR** through the WordPress Plugins menu.
3. Go to **ExploreXR → Create New Model**, upload a GLB / GLTF file, and configure your viewer.
4. Copy the shortcode and paste it anywhere on your site.
5. To add your included Premium Addon, visit **ExploreXR → Addons** and choose AR Addon, Animation Addon, or Loading Options Addon.

== Frequently Asked Questions ==

= How many addons can I run on the free version? =
Exactly one, from the curated list (AR Addon, Animation Addon, Loading Options Addon). Activating a second one will be blocked with a notice.

= Why can I not install other ExploreXR addons? =
The free version is locked to the three included Premium Addons: AR Addon, Animation Addon, and Loading Options Addon. Any additional addons or multiple-addon setups require ExploreXR Premium.

= Can I run ExploreXR and ExploreXR Premium at the same time? =
No. If Premium is activated, the free plugin self-deactivates to prevent conflicts. Your models and meta data are preserved.

= Does the plugin call home? =
No. All rendering libraries (Draco, KTX2 / Basis, Meshopt) are bundled. Updates go through the standard WordPress.org repository.

= Does ExploreXR work with Elementor / Divi / Avada / Gutenberg? =
Yes — the shortcode renders in every editor. Native widgets for builders are part of ExploreXR Premium.

== Third-Party Libraries ==

* **Google Model Viewer** (Apache 2.0) — `assets/js/model-viewer-umd.js`. Core WebGL + WebXR renderer.
* **Draco** (Apache 2.0) — `assets/vendor/draco/`. Geometry compression decoder.
* **Basis Universal** (Apache 2.0) — `assets/vendor/basis-universal/`. KTX2 texture transcoder.
* **Meshoptimizer** (MIT) — `assets/vendor/meshopt/`. Mesh compression decoder.

All decoder WASM files are required for compressed model support and have no JavaScript-only equivalents.

== Screenshots ==

1. Dashboard — model count, file storage, system status, and quick actions
2. Create New Model — drag-and-drop GLB/GLTF upload with live viewer preview
3. Free Add-on page — one-click install and selection of your included Premium Addon
4. Plugin Settings — viewer defaults, loading strategy, and model viewer version
5. Browse Models — searchable grid of all published 3D models with shortcodes
6. Edit 3D Model — per-breakpoint sizing, camera, auto-rotation, and poster image
7. Addons — three free-eligible Premium Addons with WordPress.org install links

== Changelog ==

= 1.3.3 =
* Initial WordPress.org release of the free ExploreXR plugin.
* Built on the same rendering core as ExploreXR Premium 1.3.1.
* Single-addon limit with curated whitelist (AR Addon, Animation Addon, Loading Options Addon).
* Compression decoders bundled: Draco, KTX2/Basis Universal, Meshopt.
* Per-model load behavior override (direct / poster + button / lazy).
* Customisable Load Model button.
* New Go Premium page with feature comparison.

== Upgrade Notice ==

= 1.3.2 =
Adds global and per-model lazy loading, Load Model button customization, fixes large model size detection, and enables Draco/Meshopt/Basis texture compression. Update recommended for all users.

= 1.3.1 =
Fixes PHP fatal error on duplicate plugin activation. Update required if you saw "Cannot redeclare function" in your error log.

= 1.3.0 =
Fixes GLB/GLTF/USDZ upload failures caused by PHP finfo MIME detection. Update required if uploads are showing "Invalid file type".

= 1.2.0 =
Adds one included Premium Addon of your choice (AR Addon, Animation Addon, or Loading Options Addon) plus fixes GLB/GLTF file upload failures. Update recommended for all users.

= 1.1.0 =
Adds native Elementor, Divi, and Avada integrations plus a 14-day free trial for Premium. Update recommended.

= 1.0.6 =
Initial release. Install to start displaying interactive 3D models in WordPress.


== Privacy Policy ==

ExploreXR does not collect or transmit any visitor data. All 3D rendering happens client-side using locally bundled libraries.

== Credits ==

ExploreXR is built by Ayal Othman — [ExpoXR](https://expoxr.com).
