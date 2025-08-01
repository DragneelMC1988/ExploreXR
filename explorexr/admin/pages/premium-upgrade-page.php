<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ExploreXR Premium Upgrade Page
 * 
 * Shows premium features and upgrade options
 */
function explorexr_premium_upgrade_page() {
    // Set up header variables
    $page_title = 'Go Premium';
    $header_actions = '<a href="' . explorexr_get_premium_upgrade_url() . '" class="button button-primary" target="_blank">
                        <span class="dashicons dashicons-star-filled" style="margin-right: 5px;"></span> Upgrade Now
                       </a>';
    ?>
    <div class="wrap">
        <h1>ExploreXR Premium</h1>
        
        <!-- WordPress.org Compliance: This div.wp-header-end is required for WordPress to place admin notices properly -->
        <div class="wp-header-end"></div>
        
        <!-- ExploreXR Plugin Content -->
        <div class="explorexr-admin-container">
        <!-- WordPress admin notices appear here automatically before our custom content -->
        
        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/notifications-area.php'; ?>
        <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-header.php'; ?>
        
        <div class="explorexr-premium-content">
            <section class="premium-summary">
                <h2>Premium Feature Experience</h2>
                <p class="summary-description">ExploreXR Premium offers powerful features to enhance your 3D models. Choose the subscription tier that fits your needs.</p>
                
                <div class="premium-tier-overview">
                    <div class="tier-overview-card">
                        <h4>Pro</h4>
                        <div class="tier-number">2</div>
                        <p>Essential Premium Features</p>
                    </div>
                    <div class="tier-overview-card">
                        <h4>Plus</h4>
                        <div class="tier-number">4</div>
                        <p>Advanced Premium Features</p>
                    </div>
                    <div class="tier-overview-card">
                        <h4>Ultra</h4>
                        <div class="tier-number">All</div>
                        <p>Complete Premium<br>Feature Set</p>
                    </div>
                </div>
            </section>

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
                                <td><strong>File Formats</strong></td>
                                <td class="free-column">✅ GLB, GLTF, USDZ</td>
                                <td class="premium-column">✅ GLB, GLTF, USDZ</td>
                            </tr>
                            <tr>
                                <td><strong>Responsive Design</strong></td>
                                <td class="free-column">✅ Yes</td>
                                <td class="premium-column">✅ Yes</td>
                            </tr>
                            <tr>
                                <td><strong>Advanced Features</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ Full Access</td>
                            </tr>
                            <tr>
                                <td><strong>AR (Augmented Reality)</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ Yes</td>
                            </tr>
                            <tr>
                                <td><strong>Animations</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ Yes</td>
                            </tr>
                            <tr>
                                <td><strong>Annotations</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ Yes</td>
                            </tr>
                            <tr>
                                <td><strong>Camera Controls</strong></td>
                                <td class="free-column">❌ Basic</td>
                                <td class="premium-column">✅ Advanced</td>
                            </tr>
                            <tr>
                                <td><strong>Material Editor</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ Yes</td>
                            </tr>
                            <tr>
                                <td><strong>WooCommerce Integration</strong></td>
                                <td class="free-column">❌ Basic</td>
                                <td class="premium-column">✅ Advanced</td>
                            </tr>
                            <tr>
                                <td><strong>Priority Support</strong></td>
                                <td class="free-column">❌ Community</td>
                                <td class="premium-column">✅ Priority Email</td>
                            </tr>
                            <tr>
                                <td><strong>License Tiers</strong></td>
                                <td class="free-column">❌ No</td>
                                <td class="premium-column">✅ Pro, Plus, Ultra</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="pricing-tiers">
                <h2>Premium Pricing Tiers</h2>
                <div class="pricing-grid">
                    <div class="pricing-card">
                        <h3>Pro</h3>
                        <div class="price">€59<span>/year</span></div>
                        <div class="feature-count">Essential Features</div>
                        <ul>
                            <li>Basic 3D Features</li>
                            <li>AR Support</li>
                            <li>Camera Controls</li>                            
                            <li>Email Support</li>                            
                        </ul>
                    </div>

                    <div class="pricing-card featured">
                        <h3>Plus</h3>
                        <div class="price">€99<span>/year</span></div>
                        <div class="badge">Most Popular</div>
                        <div class="feature-count">Advanced Features</div>
                        <ul>
                            <li>All Pro Features</li>
                            <li>Animations</li>
                            <li>Annotations</li>                           
                            <li>Priority Support</li>                            
                        </ul>
                    </div>

                    <div class="pricing-card">
                        <h3>Ultra</h3>
                        <div class="price">€179<span>/year</span></div>
                        <div class="feature-count">Complete Feature Set</div>
                        <ul>
                            <li>All Plus Features</li>
                            <li>Materials Editing</li>
                            <li>WooCommerce Integration</li>
                            <li>Analytics & Tracking</li>
                            <li>Priority Feature Requests</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="features-showcase">
                <h2>Available Premium Features</h2>
                <p class="section-description">Enhance your 3D experience with our powerful feature collection. Each feature is designed to provide specific functionality to meet your needs.</p>
                
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🚀</div>
                        <h3>AR Support</h3>
                        <p class="feature-description">Bring your 3D models into the real world with Augmented Reality support for mobile devices.</p>
                        <ul class="feature-features">
                            <li>iOS Quick Look AR support</li>
                            <li>Android Scene Viewer integration</li>
                            <li>Custom AR button styling</li>
                            <li>AR placement optimization</li>
                            <li>Cross-platform compatibility</li>
                        </ul>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">🎬</div>
                        <h3>Animations</h3>
                        <p class="feature-description">Add life to your models with interactive animations and smooth transitions.</p>
                        <ul class="feature-features">
                            <li>Multiple animation sequences</li>
                            <li>Animation timeline control</li>
                            <li>Auto-play and loop options</li>
                            <li>Interactive animation triggers</li>
                            <li>Smooth transition effects</li>
                        </ul>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">💬</div>
                        <h3>Annotations</h3>
                        <p class="feature-description">Add interactive hotspots and information overlays to highlight specific parts of your models.</p>
                        <ul class="feature-features">
                            <li>Interactive hotspot creation</li>
                            <li>Rich text annotations</li>
                            <li>Custom hotspot styling</li>
                            <li>Click-to-reveal content</li>
                            <li>Responsive positioning</li>
                        </ul>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">📷</div>
                        <h3>Camera Controls</h3>
                        <p class="feature-description">Advanced camera interaction and movement controls for enhanced user experience.</p>
                        <ul class="feature-features">
                            <li>Custom camera positions</li>
                            <li>Smooth camera transitions</li>
                            <li>Orbit control limitations</li>
                            <li>Auto-rotate functionality</li>
                            <li>Touch gesture optimization</li>
                        </ul>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">⏳</div>
                        <h3>Loading Options</h3>
                        <p class="feature-description">Customize the loading experience with beautiful progress indicators and overlays.</p>
                        <ul class="feature-features">
                            <li>Custom loading animations</li>
                            <li>Progress bar styling</li>
                            <li>Loading text customization</li>
                            <li>Overlay color options</li>
                            <li>Percentage display control</li>
                        </ul>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">🎨</div>
                        <h3>Materials Editing</h3>
                        <p class="feature-description">Real-time material editing and customization for dynamic visual experiences.</p>
                        <ul class="feature-features">
                            <li>Real-time material editing</li>
                            <li>Color and texture changes</li>
                            <li>Material variant switching</li>
                            <li>Custom material presets</li>
                            <li>Live preview updates</li>
                        </ul>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">🛒</div>
                        <h3>WooCommerce Integration</h3>
                        <p class="feature-description">Enhanced e-commerce integration for product showcasing and sales optimization.</p>
                        <ul class="feature-features">
                            <li>Product gallery integration</li>
                            <li>Variant-based model switching</li>
                            <li>Add-to-cart functionality</li>
                            <li>Custom product fields</li>
                            <li>Advanced product display</li>
                        </ul>
                    </div>

                    <div class="feature-card feature-coming-soon">
                        <div class="feature-icon">🚀</div>
                        <h3>And Many More Features Coming Soon</h3>
                        <p class="feature-description">We're constantly working on exciting new features to enhance your 3D experience even further!</p>
                        <ul class="feature-features">
                            <li>Advanced Analytics & Tracking</li>
                            <li>Multi-language Support</li>
                            <li>Mouse Tracker</li>
                            <li>Mobile Optimization Tools</li>
                            <li>Post Processing</li>
                            <li>Performance Optimization</li>
                        </ul>
                        <div class="coming-soon-badge">Coming Soon</div>
                    </div>
                </div>

                <div class="addons-note">
                    <p><strong>Addon Availability by Tier:</strong></p>
                    <ul class="tier-addon-list">
                        <li><strong>Pro (€59/year):</strong> Choose any 2 addons from the collection above</li>
                        <li><strong>Plus (€99/year):</strong> Choose any 4 addons from the collection above</li>
                        <li><strong>Ultra (€179/year):</strong> Access to all addons included</li>
                    </ul>
                    <p><em>All tiers include the core 3D model viewer functionality. Addons can be activated/deactivated through your license dashboard.</em></p>
                </div>
            </section>

            <section class="upgrade-cta">
                <div class="cta-content">
                    <h2>Ready to Upgrade?</h2>
                    <p>Join thousands of satisfied customers using ExploreXR Premium</p>
                    <div class="cta-buttons">
                        <a href="<?php echo esc_url(explorexr_get_premium_upgrade_url()); ?>" class="button button-primary button-hero" target="_blank">
                            Get Premium Now
                        </a>
                        <a href="<?php echo esc_url(explorexr_get_premium_upgrade_url()); ?>demo" class="button button-secondary" target="_blank">
                            View Live Demo
                        </a>
                    </div>
                    <div class="guarantee">
                        <span>💰 30-day money-back guarantee</span>
                    </div>
                </div>
            </section>
        </div>
    </div>
    
    <!-- ExploreXR Footer -->
    <?php include EXPLOREXR_PLUGIN_DIR . 'admin/templates/admin-footer.php'; ?>
    
        </div><!-- .explorexr-admin-container -->
    </div><!-- .wrap -->
    
    <?php
}






