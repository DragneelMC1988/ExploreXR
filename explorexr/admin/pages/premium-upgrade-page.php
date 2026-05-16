<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ExploreXR Premium Upgrade Page
 * 
 * Shows all 12 premium addons (+ free Debug Toolkit), pricing, and upgrade options.
 *
 * @since 1.1.0
 */
function explorexr_premium_upgrade_page() {
    $addons  = explorexr_get_available_addons();

    // Set up header variables
    $page_title = 'Go Premium';
    $header_actions = '<a href="' . explorexr_get_premium_upgrade_url() . '" class="button button-primary" target="_blank">
                        <span class="dashicons dashicons-star-filled" style="margin-right: 5px;"></span> Upgrade Now
                       </a>';
    ?>
    <div class="wrap">
        <h1>ExploreXR Premium</h1>
        
        <div class="wp-header-end"></div>
        
        <div class="explorexr-admin-container">
        
        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/notifications-area.php'; ?>
        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-header.php'; ?>
        
        <div class="explorexr-premium-content">

            <!-- ============================================ -->
            <!-- SECTION: Free Trial CTA                      -->
            <!-- ============================================ -->
            <section class="premium-trial-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 40px; margin-bottom: 30px; color: #fff; text-align: center;">
                <h2 style="color: #fff; margin: 0 0 10px; font-size: 28px;">🚀 Try ExploreXR Premium — Free for 14 Days</h2>
                <p style="font-size: 16px; opacity: 0.9; max-width: 600px; margin: 0 auto 25px;">Experience all <strong>12 powerful addons</strong> with full premium functionality for 14 days. No credit card required — just download the trial version from our website.</p>
                
                <a href="<?php echo esc_url(explorexr_get_premium_upgrade_url()); ?>" class="button button-hero" style="background: #fff; color: #764ba2; border: none; font-weight: 700; font-size: 16px; padding: 12px 40px; border-radius: 8px; cursor: pointer;" target="_blank">
                    Get Your Free 14-Day Trial →
                </a>
                <p style="font-size: 12px; opacity: 0.7; margin-top: 10px;">Visit ExpoXR.com to download the premium trial version.</p>
            </section>

            <!-- ============================================ -->
            <!-- SECTION: Pricing Tiers                       -->
            <!-- ============================================ -->
            <section class="premium-summary">
                <h2>Transparent, Modular Pricing</h2>
                <p class="summary-description">Only pay for what you need. Choose a plan that matches your project requirements.</p>
                <div class="pricing-grid">
                    <div class="pricing-card">
                        <h3>Pro</h3>
                        <div class="price">€59<span>/year</span></div>
                        <div class="feature-count">Starter Package</div>
                        <ul>
                            <li>Core 3D Model Viewer</li>
                            <li>Choose any <strong>3 addons</strong></li>
                            <li>Email Support</li>
                            <li>Debug Toolkit included free</li>
                        </ul>
                    </div>

                    <div class="pricing-card featured">
                        <h3>Plus</h3>
                        <div class="price">€99<span>/year</span></div>
                        <div class="badge">Most Popular</div>
                        <div class="feature-count">Professional Package</div>
                        <ul>
                            <li>Core 3D Model Viewer</li>
                            <li>Choose any <strong>5 addons</strong></li>
                            <li>Priority Support</li>
                            <li>Debug Toolkit included free</li>
                        </ul>
                    </div>

                    <div class="pricing-card">
                        <h3>Ultra</h3>
                        <div class="price">€179<span>/year</span></div>
                        <div class="feature-count">Complete Package</div>
                        <ul>
                            <li>Core 3D Model Viewer</li>
                            <li><strong>all 12 addons</strong> included</li>
                            <li>Priority Support</li>
                            <li>Debug Toolkit included free</li>
                        </ul>
                    </div>
                </div>
                <p style="text-align: center; color: #666; margin-top: 15px; font-size: 13px;">All plans include: 30-day money-back guarantee · Secure checkout · Instant delivery</p>
            </section>

            <!-- ============================================ -->
            <!-- SECTION: Free vs Premium Comparison           -->
            <!-- ============================================ -->
            <section class="feature-comparison">
                <h2>Free vs Premium Comparison</h2>
                
                <div class="comparison-table">
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>Feature</th>
                                <th class="free-column">Free</th>
                                <th class="premium-column">Premium</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>3D Model Viewer</strong></td>
                                <td class="free-column">✅ Basic</td>
                                <td class="premium-column">✅ Advanced</td>
                            </tr>
                            <tr>
                                <td><strong>File Formats (GLB, GLTF, USDZ)</strong></td>
                                <td class="free-column">✅ Yes</td>
                                <td class="premium-column">✅ Yes</td>
                            </tr>
                            <tr>
                                <td><strong>Responsive Design</strong></td>
                                <td class="free-column">✅ Yes</td>
                                <td class="premium-column">✅ Yes</td>
                            </tr>
                            <tr>
                                <td><strong>AR (Augmented Reality)</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ iOS + Android + WebXR</td>
                            </tr>
                            <tr>
                                <td><strong>Animations</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ Multi-clip, controls, crossfade</td>
                            </tr>
                            <tr>
                                <td><strong>Annotations (4 Types)</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ Hotspots, dimensions, camera-view</td>
                            </tr>
                            <tr>
                                <td><strong>Expert Camera Controls</strong></td>
                                <td class="free-column">❌ Basic only</td>
                                <td class="premium-column">✅ Full constraint system</td>
                            </tr>
                            <tr>
                                <td><strong>Materials & Variants</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ Real-time switching</td>
                            </tr>
                            <tr>
                                <td><strong>Environment & Lighting</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ HDRI, tone mapping, shadows</td>
                            </tr>
                            <tr>
                                <td><strong>Post-Processing Filters</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ Bloom, DOF, SSAO, SSR</td>
                            </tr>
                            <tr>
                                <td><strong>Morphing Transitions</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ 5 animation styles</td>
                            </tr>
                            <tr>
                                <td><strong>Mouse3D Cursor Interaction</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ 6 presets</td>
                            </tr>
                            <tr>
                                <td><strong>WooCommerce Integration</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ Per-product model + tab display</td>
                            </tr>
                            <tr>
                                <td><strong>Draggable Viewer</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ Floating panel mode</td>
                            </tr>
                            <tr>
                                <td><strong>Advanced Loading Options</strong></td>
                                <td class="free-column">❌ Basic</td>
                                <td class="premium-column">✅ Full customization</td>
                            </tr>
                            <tr>
                                <td><strong>Debug Toolkit (12 tools)</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ Always free, no license needed</td>
                            </tr>                           
                            <tr>
                                <td><strong>Priority Support</strong></td>
                                <td class="free-column">❌ Community</td>
                                <td class="premium-column">✅ Priority Email</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ============================================ -->
            <!-- SECTION: All 12 Addon Showcase + Debug Toolkit -->
            <!-- ============================================ -->
            <section class="features-showcase">
                <h2>12 powerful Addons — Activate What You Need</h2>
                <p class="section-description">ExploreXR Premium is built around a modular addon architecture. Each addon extends the core viewer with a specific capability — activate only what you need, and scale as your business grows.</p>
                
                <div class="features-grid">
                    <!-- AR Addon -->
                    <div class="feature-card">
                        <div class="feature-icon">📱</div>
                        <h3>AR (Augmented Reality)</h3>
                        <p class="feature-description">Place any 3D model into the real world using your phone — no app required.</p>
                        <ul class="feature-features">
                            <li>iOS AR Quick Look (USDZ)</li>
                            <li>Android Scene Viewer (GLB)</li>
                            <li>WebXR browser-native AR</li>
                            <li>Custom AR button styling</li>
                            <li>Floor & wall placement modes</li>
                        </ul>
                        <p class="feature-best-for"><strong>Best for:</strong> E-commerce, furniture, real estate, education</p>
                    </div>

                    <!-- Animation Addon -->
                    <div class="feature-card">
                        <div class="feature-icon">🎬</div>
                        <h3>Animation</h3>
                        <p class="feature-description">Play, pause, and switch between animation clips embedded inside your 3D model files.</p>
                        <ul class="feature-features">
                            <li>Multiple named clips</li>
                            <li>Loop & ping-pong playback</li>
                            <li>Crossfade transitions</li>
                            <li>Playback speed control</li>
                            <li>On-screen visitor controls</li>
                        </ul>
                        <p class="feature-best-for"><strong>Best for:</strong> Product demos, industrial, characters, architecture</p>
                    </div>

                    <!-- Annotations Addon -->
                    <div class="feature-card">
                        <div class="feature-icon">💬</div>
                        <h3>Annotations</h3>
                        <p class="feature-description">Pin interactive hotspots directly onto your 3D model surface with rich content.</p>
                        <ul class="feature-features">
                            <li>Basic Hotspot — static info point</li>
                            <li>Animated Hotspot — tracks mesh</li>
                            <li>Dimension Line — measurements</li>
                            <li>Camera-View — guided tours</li>
                            <li>Full HTML content support</li>
                        </ul>
                        <p class="feature-best-for"><strong>Best for:</strong> Product tours, documentation, museums, education</p>
                    </div>

                    <!-- Expert Camera Mode -->
                    <div class="feature-card">
                        <div class="feature-icon">📷</div>
                        <h3>Expert Camera Mode</h3>
                        <p class="feature-description">Set exact camera limits, positions, zoom ranges, and interaction sensitivity.</p>
                        <ul class="feature-features">
                            <li>Min/max zoom distance</li>
                            <li>Polar & azimuth angle limits</li>
                            <li>Custom camera target point</li>
                            <li>Orbit/pan/zoom sensitivity</li>
                            <li>Interaction prompt styles</li>
                        </ul>
                        <p class="feature-best-for"><strong>Best for:</strong> Showcases, architecture, guided demos, mobile UX</p>
                    </div>

                    <!-- Environment & Lighting -->
                    <div class="feature-card">
                        <div class="feature-icon">🌅</div>
                        <h3>Environment & Lighting</h3>
                        <p class="feature-description">Control exposure, tone mapping, shadows, and HDRI lighting for photorealism.</p>
                        <ul class="feature-features">
                            <li>HDRI / Skybox environment maps</li>
                            <li>Exposure & tone mapping (4 modes)</li>
                            <li>Shadow intensity & softness</li>
                            <li>Per-model overrides</li>
                            <li>Commerce-optimized rendering</li>
                        </ul>
                        <p class="feature-best-for"><strong>Best for:</strong> Jewelry, automotive, electronics, luxury goods</p>
                    </div>

                    <!-- Materials & Variants -->
                    <div class="feature-card">
                        <div class="feature-icon">🎨</div>
                        <h3>Materials & Variants</h3>
                        <p class="feature-description">Let visitors switch colors, textures, and materials in real time on the model.</p>
                        <ul class="feature-features">
                            <li>Auto-detect material slots</li>
                            <li>Named variant creation</li>
                            <li>Dropdown or button switcher</li>
                            <li>Visitor texture upload</li>
                            <li>WooCommerce variant mapping</li>
                        </ul>
                        <p class="feature-best-for"><strong>Best for:</strong> Custom products, fashion, furniture, configurators</p>
                    </div>

                    <!-- Loading Options -->
                    <div class="feature-card">
                        <div class="feature-icon">⏳</div>
                        <h3>Loading Options</h3>
                        <p class="feature-description">Fully customize the loading bar, percentage, overlay, and text while models download.</p>
                        <ul class="feature-features">
                            <li>Branded loading bar colors</li>
                            <li>Percentage counter display</li>
                            <li>Custom loading text</li>
                            <li>Background overlay with blur</li>
                            <li>Lazy loading for performance</li>
                        </ul>
                        <p class="feature-best-for"><strong>Best for:</strong> Performance, multi-model pages, brand UX</p>
                    </div>

                    <!-- WooCommerce -->
                    <div class="feature-card">
                        <div class="feature-icon">🛒</div>
                        <h3>WooCommerce</h3>
                        <p class="feature-description">Attach any 3D model to a WooCommerce product and display on the product page.</p>
                        <ul class="feature-features">
                            <li>Per-product model assignment</li>
                            <li>Product tab or inline display</li>
                            <li>All addons active on product pages</li>
                            <li>AR "try before you buy"</li>
                            <li>Product variation mapping</li>
                        </ul>
                        <p class="feature-best-for"><strong>Best for:</strong> WooCommerce stores, physical products, premium items</p>
                    </div>

                    <!-- Morphing -->
                    <div class="feature-card">
                        <div class="feature-icon">🔄</div>
                        <h3>Morphing</h3>
                        <p class="feature-description">Animate seamless transitions between two different 3D models with a single click.</p>
                        <ul class="feature-features">
                            <li>5 transition styles (fade, zoom, slide, blur)</li>
                            <li>Button or scroll trigger</li>
                            <li>Multi-step sequences (A→B→C)</li>
                            <li>Custom button styling</li>
                            <li>Configurable duration</li>
                        </ul>
                        <p class="feature-best-for"><strong>Best for:</strong> Before/after, assembly demos, design evolution</p>
                    </div>

                    <!-- Mouse3D -->
                    <div class="feature-card">
                        <div class="feature-icon">🎯</div>
                        <h3>Mouse3D Control</h3>
                        <p class="feature-description">Your 3D model reacts to cursor movement in real time — no click required.</p>
                        <ul class="feature-features">
                            <li>6 built-in presets</li>
                            <li>Configurable damping & sensitivity</li>
                            <li>Rotation + zoom on cursor</li>
                            <li>Restrict to viewer bounds</li>
                            <li>Per-model overrides</li>
                        </ul>
                        <p class="feature-best-for"><strong>Best for:</strong> Hero sections, luxury products, portfolios</p>
                    </div>

                    <!-- Post-Processing -->
                    <div class="feature-card">
                        <div class="feature-icon">✨</div>
                        <h3>Post-Processing Filters</h3>
                        <p class="feature-description">Apply cinematic visual effects — bloom, DOF, SSAO, and SSR — on your 3D model.</p>
                        <ul class="feature-features">
                            <li>Bloom (glow effect)</li>
                            <li>Depth of Field (focus blur)</li>
                            <li>SSAO (ambient occlusion)</li>
                            <li>SSR (screen space reflections)</li>
                            <li>Color grading & tone filters</li>
                        </ul>
                        <p class="feature-best-for"><strong>Best for:</strong> High-end products, automotive, jewelry</p>
                    </div>

                    <!-- Draggable -->
                    <div class="feature-card">
                        <div class="feature-icon">🖱️</div>
                        <h3>Draggable</h3>
                        <p class="feature-description">Transform the 3D viewer into a draggable floating panel visitors can reposition.</p>
                        <ul class="feature-features">
                            <li>Click-and-drag repositioning</li>
                            <li>Configurable start position</li>
                            <li>Per-model enable/disable</li>
                            <li>Works with all other addons</li>
                        </ul>
                        <p class="feature-best-for"><strong>Best for:</strong> Documentation, tutorials, comparisons</p>
                    </div>

                    <!-- Debug Toolkit -->
                    <div class="feature-card" style="border: 2px dashed #46b450;">
                        <div class="feature-icon">🛠️</div>
                        <h3>Debug Toolkit <span style="background: #46b450; color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 10px; margin-left: 5px;">FREE</span></h3>
                        <p class="feature-description">12 diagnostic tools for system info, model inspection, performance, and more.</p>
                        <ul class="feature-features">
                            <li>System Info & Model Inspector</li>
                            <li>Addon Diagnostics</li>
                            <li>Shortcode Tester</li>
                            <li>Performance Profiler</li>
                            <li>No license required</li>
                        </ul>
                        <p class="feature-best-for"><strong>Best for:</strong> Developers, agencies, support, site verification</p>
                    </div>
                </div>
            </section>

            <!-- ============================================ -->
            <!-- SECTION: Power Combinations                  -->
            <!-- ============================================ -->
            <section class="feature-comparison" style="margin-top: 30px;">
                <h2>Recommended Addon Combinations</h2>
                <div class="comparison-table">
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>Use Case</th>
                                <th>Recommended Addons</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>🛒 Ultimate E-Commerce</strong></td>
                                <td>WooCommerce + AR + Materials & Variants + Environment</td>
                            </tr>
                            <tr>
                                <td><strong>🎬 Cinematic Showcase</strong></td>
                                <td>Post-Processing + Environment + Camera Mode + Mouse3D</td>
                            </tr>
                            <tr>
                                <td><strong>🏛️ Interactive Tour</strong></td>
                                <td>Annotations + Camera Mode + Animation + Environment</td>
                            </tr>
                            <tr>
                                <td><strong>🔄 Before & After</strong></td>
                                <td>Morphing + Loading Options + Camera Mode + Environment</td>
                            </tr>
                            <tr>
                                <td><strong>💎 Luxury Products</strong></td>
                                <td>Environment + Post-Processing + Mouse3D + AR</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ============================================ -->
            <!-- SECTION: Final CTA                           -->
            <!-- ============================================ -->
            <section class="upgrade-cta">
                <div class="cta-content">
                    <h2>Ready to Transform Your 3D Experience?</h2>
                    <p>Join product brands, agencies, educators, and creators using ExploreXR Premium worldwide.</p>
                    <div class="cta-buttons">
                        <a href="<?php echo esc_url(explorexr_get_premium_upgrade_url()); ?>" class="button button-primary button-hero" target="_blank">
                            Get Premium Now
                        </a>
                        <a href="https://expoxr.com/explorexr/demo/" class="button button-secondary" target="_blank">
                            View Live Demo
                        </a>
                    </div>
                    <div class="guarantee">
                        <span>💰 30-day money-back guarantee · Secure checkout · Instant delivery</span>
                    </div>
                </div>
            </section>
        </div>
    </div>
    
    <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-footer.php'; ?>
    
        </div><!-- .explorexr-admin-container -->
    </div><!-- .wrap -->

    <style type="text/css">
    .feature-best-for {
        font-size: 12px;
        color: #666;
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px solid #eee;
    }
    </style>
    <?php
}






