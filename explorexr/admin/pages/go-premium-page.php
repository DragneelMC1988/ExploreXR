<?php
/**
 * Go Premium page.
 *
 * Marketing-grade overview of ExploreXR Premium tiers and addon catalog.
 * Pure static HTML — no remote calls, no AJAX, no images downloaded.
 */

if (!defined('ABSPATH')) {
    exit;
}

function explorexr_free_go_premium_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'explorexr'));
    }

    $pricing_url = 'https://expoxr.com/explorexr/pricing/';
    $demo_url    = 'https://expoxr.com/explorexr/demo/';
    $docs_url    = 'https://expoxr.com/explorexr/documentation/';
    $logo_url    = EXPLOREXR_PLUGIN_URL . 'assets/img/logos/exploreXR-Logo.png';

    // Tier definitions. Prices in EUR / year, sourced from the Premium
    // codebase pricing payload (admin/core/admin-menu.php:444-468).
    $tiers = array(
        'free' => array(
            'label'      => __('Free', 'explorexr'),
            'price'      => __('€0', 'explorexr'),
            'period'     => __('forever', 'explorexr'),
            'slots'      => __('1 addon', 'explorexr'),
            'color'      => '#6c757d',
            'highlight'  => false,
            'tagline'    => __('Get started with 3D on WordPress.', 'explorexr'),
            'features'   => array(
                __('Core 3D viewer (GLB / GLTF / USDZ)', 'explorexr'),
                __('One free addon (AR, Animation, Loading, or Annotations)', 'explorexr'),
                __('Compression decoders: Draco, KTX2, Meshopt', 'explorexr'),
                __('Community support', 'explorexr'),
            ),
            'cta_label'  => __('You are here', 'explorexr'),
            'cta_url'    => '',
            'disabled'   => true,
        ),
        'pro' => array(
            'label'      => __('Pro', 'explorexr'),
            'price'      => __('€59', 'explorexr'),
            'period'     => __('per year', 'explorexr'),
            'slots'      => __('3 addons', 'explorexr'),
            'color'      => '#0073aa',
            'highlight'  => false,
            'tagline'    => __('Focused production deployments.', 'explorexr'),
            'features'   => array(
                __('Everything in Free', 'explorexr'),
                __('Choose any 3 commercial addons', 'explorexr'),
                __('Email support', 'explorexr'),
                __('Automatic updates from expoxr.com', 'explorexr'),
            ),
            'cta_label'  => __('Choose Pro', 'explorexr'),
            'cta_url'    => $pricing_url . '#tier',
            'disabled'   => false,
        ),
        'plus' => array(
            'label'      => __('Plus', 'explorexr'),
            'price'      => __('€99', 'explorexr'),
            'period'     => __('per year', 'explorexr'),
            'slots'      => __('5 addons', 'explorexr'),
            'color'      => '#7c3aed',
            'highlight'  => true,
            'tagline'    => __('Rich production sites with AR + commerce.', 'explorexr'),
            'features'   => array(
                __('Everything in Pro', 'explorexr'),
                __('Choose any 5 commercial addons', 'explorexr'),
                __('Priority email support', 'explorexr'),
                __('Page builder widgets (Elementor, Divi, Avada)', 'explorexr'),
            ),
            'cta_label'  => __('Choose Plus', 'explorexr'),
            'cta_url'    => $pricing_url . '#tier',
            'disabled'   => false,
        ),
        'ultra' => array(
            'label'      => __('Ultra', 'explorexr'),
            'price'      => __('€179', 'explorexr'),
            'period'     => __('per year', 'explorexr'),
            'slots'      => __('Unlimited', 'explorexr'),
            'color'      => '#b91c1c',
            'highlight'  => false,
            'tagline'    => __('Agencies, marketplaces, multi-brand.', 'explorexr'),
            'features'   => array(
                __('Everything in Plus', 'explorexr'),
                __('All 12 commercial addons included', 'explorexr'),
                __('VIP support', 'explorexr'),
                __('White-label / custom branding options', 'explorexr'),
            ),
            'cta_label'  => __('Choose Ultra', 'explorexr'),
            'cta_url'    => $pricing_url . '#tier',
            'disabled'   => false,
        ),
    );

    $tier_badges = array(
        'free'  => array('label' => __('Free', 'explorexr'),    'bg' => '#1a7f37'),
        'pro'   => array('label' => __('Premium', 'explorexr'), 'bg' => '#7c3aed'),
        'plus'  => array('label' => __('Premium', 'explorexr'), 'bg' => '#7c3aed'),
        'ultra' => array('label' => __('Premium', 'explorexr'), 'bg' => '#7c3aed'),
    );

    // Full commercial addon catalog. Descriptions taken from each addon's
    // own plugin header (see Phase 1 audit).
    $addons = array(
        array('slug' => 'ar',              'name' => __('AR Viewer', 'explorexr'),         'tier' => 'free',  'icon' => 'dashicons-smartphone',         'desc' => __('Augmented Reality on iOS Quick Look, Android Scene Viewer, and WebXR — configurable per model.', 'explorexr')),
        array('slug' => 'animation',       'name' => __('Animation', 'explorexr'),         'tier' => 'free',  'icon' => 'dashicons-controls-play',       'desc' => __('Play, pause, loop and crossfade glTF animation clips with full player controls.', 'explorexr')),
        array('slug' => 'loading',         'name' => __('Loading Options', 'explorexr'),   'tier' => 'free',  'icon' => 'dashicons-update',              'desc' => __('Custom loading bars, percentage counters, branded overlays and progress text.', 'explorexr')),
        array('slug' => 'annotations',     'name' => __('Annotations', 'explorexr'),       'tier' => 'pro',   'icon' => 'dashicons-marker',              'desc' => __('Interactive hotspots, dimension lines and labels pinned to 3D model points.', 'explorexr')),
        array('slug' => 'camera',          'name' => __('Camera Controls', 'explorexr'),   'tier' => 'pro',   'icon' => 'dashicons-camera-alt',          'desc' => __('Advanced orbit, zoom and pan with per-model presets, limits and sensitivity tuning.', 'explorexr')),
        array('slug' => 'environment',     'name' => __('Environment & Lighting', 'explorexr'), 'tier' => 'pro', 'icon' => 'dashicons-art',           'desc' => __('HDR skybox, exposure, tone mapping, and contact shadows for cinematic scenes.', 'explorexr')),
        array('slug' => 'materials',       'name' => __('Materials Variants', 'explorexr'),'tier' => 'pro',   'icon' => 'dashicons-color-picker',        'desc' => __('Swap between material variants stored in GLB files — colour swatches with no reload.', 'explorexr')),
        array('slug' => 'morphing',        'name' => __('Morphing', 'explorexr'),          'tier' => 'plus',  'icon' => 'dashicons-leftright',           'desc' => __('Switch between two 3D models with smooth fade transitions on click or scroll.', 'explorexr')),
        array('slug' => 'mouse3d',         'name' => __('Mouse3D', 'explorexr'),           'tier' => 'plus',  'icon' => 'dashicons-image-rotate',        'desc' => __('Mouse-driven camera rotation and zoom with customisable sensitivity profiles.', 'explorexr')),
        array('slug' => 'draggable',       'name' => __('Draggable Viewer', 'explorexr'),  'tier' => 'plus',  'icon' => 'dashicons-move',                'desc' => __('Let visitors drag and reposition the viewer on the page for interactive layouts.', 'explorexr')),
        array('slug' => 'post-processing', 'name' => __('Post-Processing', 'explorexr'),   'tier' => 'plus',  'icon' => 'dashicons-filter',              'desc' => __('SSR, SSAO, DOF, Bloom and other GPU shader effects applied to the viewer.', 'explorexr')),
        array('slug' => 'woocommerce',     'name' => __('WooCommerce', 'explorexr'),       'tier' => 'ultra', 'icon' => 'dashicons-cart',                'desc' => __('Attach 3D models to WooCommerce products with full gallery integration.', 'explorexr')),
    );

    ?>
    <div class="wrap explorexr-admin-container explorexr-go-premium-page">
        <?php
        $page_title = esc_html__('Go Premium', 'explorexr');
        $insert_header_end_marker = true;
        include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-header.php';
        ?>

        <?php // Styles are in admin/css/go-premium-page.css (enqueued via explorexr_admin_enqueue_scripts). ?>

        <!-- HERO -->
        <div class="gp-hero">
            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php esc_attr_e('ExploreXR logo', 'explorexr'); ?>">
            <div>
                <h1>
                    <?php esc_html_e('Take ExploreXR to the next level', 'explorexr'); ?>
                </h1>
                <p>
                    <?php esc_html_e('You are running the free version. ExploreXR Premium unlocks the full commercial addon catalog, multi-addon support, page-builder widgets, and priority updates.', 'explorexr'); ?>
                </p>
                <div class="gp-cta">
                    <a href="<?php echo esc_url($pricing_url); ?>" target="_blank" rel="noopener" class="button button-primary button-hero">
                        <?php esc_html_e('Upgrade Now', 'explorexr'); ?>
                    </a>
                    <a href="<?php echo esc_url($demo_url); ?>" target="_blank" rel="noopener" class="button button-secondary button-hero">
                        <?php esc_html_e('Live Demo', 'explorexr'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- WHY -->
        <div class="gp-why">
            <div class="gp-why-item">
                <span class="dashicons dashicons-screenoptions"></span>
                <h3><?php esc_html_e('More Addons', 'explorexr'); ?></h3>
                <p><?php esc_html_e('Combine AR, materials, post-processing, and 9 other commercial modules — pick 3, 5, or all 12.', 'explorexr'); ?></p>
            </div>
            <div class="gp-why-item">
                <span class="dashicons dashicons-admin-multisite"></span>
                <h3><?php esc_html_e('More Sites', 'explorexr'); ?></h3>
                <p><?php esc_html_e('License one, five, or twenty-five domains — perfect for agencies and multi-brand portfolios.', 'explorexr'); ?></p>
            </div>
            <div class="gp-why-item">
                <span class="dashicons dashicons-businessperson"></span>
                <h3><?php esc_html_e('Priority Support', 'explorexr'); ?></h3>
                <p><?php esc_html_e('Email support on Pro, priority email on Plus, VIP response time on Ultra. Plus a 90-day grace period.', 'explorexr'); ?></p>
            </div>
        </div>

        <!-- TIERS -->
        <h2 class="gp-section-title"><?php esc_html_e('Choose your plan', 'explorexr'); ?></h2>
        <p class="description"><?php esc_html_e('Every tier ships in Personal (1 site), Business (5 sites), and Agency (25 sites) site plans.', 'explorexr'); ?></p>
        <div class="gp-tiers">
            <?php foreach ($tiers as $tier_slug => $tier) : ?>
                <div class="gp-tier gp-tier--<?php echo esc_attr($tier_slug); ?> <?php echo $tier['highlight'] ? 'gp-highlight' : ''; ?>">
                    <?php if ($tier['highlight']) : ?>
                        <span class="gp-pill"><?php esc_html_e('Most Popular', 'explorexr'); ?></span>
                    <?php endif; ?>
                    <h2><?php echo esc_html($tier['label']); ?></h2>
                    <div class="gp-price"><?php echo esc_html($tier['price']); ?></div>
                    <div class="gp-period"><?php echo esc_html($tier['period']); ?></div>
                    <div class="gp-slots"><?php echo esc_html($tier['slots']); ?></div>
                    <div class="gp-tagline"><?php echo esc_html($tier['tagline']); ?></div>
                    <ul>
                        <?php foreach ($tier['features'] as $feat) : ?>
                            <li><?php echo esc_html($feat); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($tier['disabled']) : ?>
                        <button class="button" disabled><?php echo esc_html($tier['cta_label']); ?></button>
                    <?php else : ?>
                        <a href="<?php echo esc_url($tier['cta_url']); ?>" target="_blank" rel="noopener" class="button button-primary">
                            <?php echo esc_html($tier['cta_label']); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ADDON CATALOG -->
        <h2 class="gp-section-title-lg"><?php esc_html_e('Available addons', 'explorexr'); ?></h2>
        <p class="description">
            <?php esc_html_e('With ExploreXR Free, you can choose and activate one of the four add-ons marked “Free.” If you want to use multiple add-ons at the same time, please upgrade to ExploreXR Premium.', 'explorexr'); ?>
        </p>
        <div class="gp-addons-grid">
            <?php foreach ($addons as $addon) :
                $badge = isset($tier_badges[$addon['tier']]) ? $tier_badges[$addon['tier']] : $tier_badges['pro'];
                ?>
                <div class="gp-addon gp-addon--<?php echo esc_attr($addon['tier']); ?>">
                    <div class="gp-addon-head">
                        <div>
                            <span class="dashicons <?php echo esc_attr($addon['icon']); ?>"></span>
                            <strong><?php echo esc_html($addon['name']); ?></strong>
                        </div>
                        <span class="gp-badge">
                            <?php echo esc_html($badge['label']); ?>
                        </span>
                    </div>
                    <p><?php echo esc_html($addon['desc']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- SITE PLANS -->
        <h2 class="gp-section-title-lg"><?php esc_html_e('Site plans', 'explorexr'); ?></h2>
        <p class="description">
            <?php esc_html_e('Pick the licence size that matches your portfolio. Every Premium tier (Pro, Plus, Ultra) is available in all three site plans.', 'explorexr'); ?>
        </p>
        <div class="gp-sites">
            <div class="gp-site">
                <div class="gp-site-num">1</div>
                <strong><?php esc_html_e('Personal', 'explorexr'); ?></strong>
                <p class="gp-site-desc"><?php esc_html_e('Single website — portfolio, product page, or focused deployment.', 'explorexr'); ?></p>
            </div>
            <div class="gp-site">
                <div class="gp-site-num">5</div>
                <strong><?php esc_html_e('Business', 'explorexr'); ?></strong>
                <p class="gp-site-desc"><?php esc_html_e('Growing teams or multi-brand environments running several active sites.', 'explorexr'); ?></p>
            </div>
            <div class="gp-site">
                <div class="gp-site-num">25</div>
                <strong><?php esc_html_e('Agency', 'explorexr'); ?></strong>
                <p class="gp-site-desc"><?php esc_html_e('Agencies, implementation partners, larger client and site portfolios.', 'explorexr'); ?></p>
            </div>
        </div>
        <!-- FAQ -->
        <h2 class="gp-section-title-lg"><?php esc_html_e('Frequently asked questions', 'explorexr'); ?></h2>
        <div class="gp-faq">
            <details>
                <summary><?php esc_html_e('What happens to my models if I upgrade?', 'explorexr'); ?></summary>
                <p><?php esc_html_e('Nothing breaks. Premium reads the same custom post type and the same meta keys. Your models, posters, shortcodes and per-model load behaviour all carry over automatically.', 'explorexr'); ?></p>
            </details>
            <details>
                <summary><?php esc_html_e('Can I switch addons later?', 'explorexr'); ?></summary>
                <p><?php esc_html_e('Yes. On Pro, Plus and Ultra you can change your selected addons at any time from the License screen. Deactivating an addon snapshots its settings so they restore when you reactivate.', 'explorexr'); ?></p>
            </details>
            <details>
                <summary><?php esc_html_e('How does the free trial work?', 'explorexr'); ?></summary>
                <p><?php esc_html_e('The Premium plugin includes a 14-day trial with Plus-level access to four addons — no credit card required, just a domain-bound trial key.', 'explorexr'); ?></p>
            </details>
            <details>
                <summary><?php esc_html_e('What happens when a Premium licence expires?', 'explorexr'); ?></summary>
                <p><?php esc_html_e('There is a 90-day grace period: the plugin keeps working with admin warnings so you never lose a live 3D experience. After grace expiry, commercial addons deactivate and the site reverts to the free viewer.', 'explorexr'); ?></p>
            </details>
        </div>

        <!-- FOOTER CTA -->
        <div class="gp-footer-cta">
            <h2><?php esc_html_e('Ready to unlock the full ExploreXR experience?', 'explorexr'); ?></h2>
            <p class="gp-footer-desc">
                <?php esc_html_e('Upgrade in two minutes. Your free models, settings, and shortcodes carry over automatically.', 'explorexr'); ?>
            </p>
            <a href="<?php echo esc_url($pricing_url); ?>" target="_blank" rel="noopener" class="button button-primary button-hero">
                <?php esc_html_e('See pricing on expoxr.com', 'explorexr'); ?>
            </a>
            &nbsp;
            <a href="<?php echo esc_url($docs_url); ?>" target="_blank" rel="noopener" class="button button-secondary button-hero">
                <?php esc_html_e('Read the docs', 'explorexr'); ?>
            </a>
        </div>

        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-footer.php'; ?>
    </div>
    <?php
}
