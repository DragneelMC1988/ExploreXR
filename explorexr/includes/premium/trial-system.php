<?php
/**
 * ExploreXR Premium Trial Information
 * 
 * Provides addon information and a trial notice directing users to the ExpoXR website
 * to obtain the 14-day premium trial version.
 * 
 * @package ExploreXR
 * @since 1.1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the full list of available premium addons.
 *
 * @return array Associative array of addon_slug => addon details.
 */
function explorexr_get_available_addons() {
    return array(
        'ar' => array(
            'name'        => 'AR (Augmented Reality)',
            'icon'        => '📱',
            'description' => 'Place any 3D model into the real world using your phone — no app required, just a browser tap.',
            'best_for'    => 'E-commerce, furniture, real estate, education',
        ),
        'animation' => array(
            'name'        => 'Animation',
            'icon'        => '🎬',
            'description' => 'Play, pause, and switch between named animation clips embedded inside your 3D model files.',
            'best_for'    => 'Product demos, industrial, characters, architecture',
        ),
        'annotations' => array(
            'name'        => 'Annotations',
            'icon'        => '💬',
            'description' => 'Pin interactive hotspots directly onto your 3D model surface with titles, descriptions, and HTML content.',
            'best_for'    => 'Product tours, documentation, museums, education',
        ),
        'camera-mode' => array(
            'name'        => 'Expert Camera Mode',
            'icon'        => '📷',
            'description' => 'Set exact camera limits, default positions, zoom ranges, and interaction sensitivity for every model.',
            'best_for'    => 'Showcases, architecture, guided demos, mobile UX',
        ),
        'debug-toolkit' => array(
            'name'        => 'Debug Toolkit',
            'icon'        => '🛠️',
            'description' => 'A complete diagnostic suite — inspect models, addons, licenses, and performance instantly.',
            'best_for'    => 'Developers, agencies, support, site verification',
        ),
        'draggable' => array(
            'name'        => 'Draggable',
            'icon'        => '🖱️',
            'description' => 'Let visitors freely click and drag the 3D viewer to any position on the page.',
            'best_for'    => 'Documentation, tutorials, comparisons',
        ),
        'environment' => array(
            'name'        => 'Environment & Lighting',
            'icon'        => '🌅',
            'description' => 'Control exposure, tone mapping, shadows, and HDRI environment lighting for photorealistic presentation.',
            'best_for'    => 'Jewelry, automotive, electronics, luxury goods',
        ),
        'loading-options' => array(
            'name'        => 'Loading Options',
            'icon'        => '⏳',
            'description' => 'Fully customize the loading bar, percentage display, overlay, and text shown while your model downloads.',
            'best_for'    => 'Performance, multi-model pages, brand UX',
        ),
        'materials' => array(
            'name'        => 'Materials & Variants',
            'icon'        => '🎨',
            'description' => 'Let visitors switch between colors, textures, and material variants of your 3D model in real time.',
            'best_for'    => 'Custom products, fashion, furniture, configurators',
        ),
        'morphing' => array(
            'name'        => 'Morphing',
            'icon'        => '🔄',
            'description' => 'Animate a seamless transition between two completely different 3D models with a single click.',
            'best_for'    => 'Before/after, assembly demos, campaigns',
        ),
        'mouse3d' => array(
            'name'        => 'Mouse3D Control',
            'icon'        => '🎯',
            'description' => 'Your 3D model reacts to cursor movement in real time — rotating and shifting as the mouse moves.',
            'best_for'    => 'Hero sections, luxury products, portfolios',
        ),
        'post-processing' => array(
            'name'        => 'Post-Processing Filters',
            'icon'        => '✨',
            'description' => 'Apply cinematic visual effects — bloom, depth of field, and ambient occlusion — directly on your 3D model.',
            'best_for'    => 'High-end products, automotive, jewelry',
        ),
        'woocommerce' => array(
            'name'        => 'WooCommerce',
            'icon'        => '🛒',
            'description' => 'Attach any ExploreXR 3D model to a WooCommerce product and display it on the product page.',
            'best_for'    => 'WooCommerce stores, physical products, premium items',
        ),
    );
}

/**
 * Render the trial notice banner for admin pages.
 * Directs users to the ExpoXR website to download the premium trial version.
 */
function explorexr_render_trial_banner() {
    ?>
    <div class="notice notice-success is-dismissible explorexr-trial-banner" style="border-left-color: #46b450; padding: 12px 16px;">
        <p>
            <strong>🚀 Try ExploreXR Premium — Free for 14 Days</strong> — 
            Experience the full power of ExploreXR Premium with all 12 addons. No credit card required.
            <a href="<?php echo esc_url(explorexr_get_premium_upgrade_url()); ?>" class="button button-primary button-small" style="margin-left: 8px;" target="_blank">Get Your Free Trial →</a>
        </p>
    </div>
    <?php
}
