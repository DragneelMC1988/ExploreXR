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
function expoxr_premium_upgrade_page() {
    // Set up header variables
    $page_title = 'Go Premium';
    $header_actions = '<a href="' . expoxr_get_premium_upgrade_url() . '" class="button button-primary" target="_blank">
                        <span class="dashicons dashicons-star-filled" style="margin-right: 5px;"></span> Upgrade Now
                       </a>';
    ?>
    <div class="wrap expoxr-admin-container">
        <!-- WordPress admin notices appear here automatically before our custom content -->
        
        <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/notifications-area.php'; ?>
        <?php include EXPOXR_PLUGIN_DIR . 'admin/templates/admin-header.php'; ?>
        
        <div class="expoxr-premium-content">
            <section class="addon-summary">
                <h2>Addon-Powered Premium Experience</h2>
                <p class="summary-description">ExploreXR Premium offers a modular approach with 7+ specialized addons. Choose the ones that fit your needs based on your subscription tier.</p>
                
                <div class="addon-tier-overview">
                    <div class="tier-overview-card">
                        <h4>Pro</h4>
                        <div class="tier-number">2</div>
                        <p>Choose any 2 addons</p>
                    </div>
                    <div class="tier-overview-card">
                        <h4>Plus</h4>
                        <div class="tier-number">4</div>
                        <p>Choose any 4 addons</p>
                    </div>
                    <div class="tier-overview-card">
                        <h4>Ultra</h4>
                        <div class="tier-number">7+</div>
                        <p>Includes All Current<br> & Future Addons</p>
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
                        <div class="addon-count">2 Addons Included</div>
                        <ul>
                            <li>Basic 3D Features</li>
                            <li>Choose any 2 addons</li>                           
                            <li>Email Support</li>                            
                        </ul>
                    </div>

                    <div class="pricing-card featured">
                        <h3>Plus</h3>
                        <div class="price">€99<span>/year</span></div>
                        <div class="badge">Most Popular</div>
                        <div class="addon-count">4 Addons Included</div>
                        <ul>
                            <li>All Pro Features</li>
                            <li>Choose any 4 addons</li>                           
                            <li>Priority Support</li>                            
                        </ul>
                    </div>

                    <div class="pricing-card">
                        <h3>Ultra</h3>
                        <div class="price">€179<span>/year</span></div>
                        <div class="addon-count">All Addons Included</div>
                        <ul>
                            <li>All Plus Features</li>
                            <li>All 7 Current Addons Included</li>
                            <li>All Future Addons Included</li>
                            <li>Priority Feature Requests</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="addons-showcase">
                <h2>Available Premium Addons</h2>
                <p class="section-description">Enhance your 3D experience with our powerful addon collection. Each addon is designed to provide specific functionality to meet your needs.</p>
                
                <div class="addons-grid">
                    <div class="addon-card">
                        <div class="addon-icon">🚀</div>
                        <h3>AR Addon</h3>
                        <p class="addon-description">Bring your 3D models into the real world with Augmented Reality support for mobile devices.</p>
                        <ul class="addon-features">
                            <li>iOS Quick Look AR support</li>
                            <li>Android Scene Viewer integration</li>
                            <li>Custom AR button styling</li>
                            <li>AR placement optimization</li>
                            <li>Cross-platform compatibility</li>
                        </ul>
                    </div>

                    <div class="addon-card">
                        <div class="addon-icon">🎬</div>
                        <h3>Animation Addon</h3>
                        <p class="addon-description">Add life to your models with interactive animations and smooth transitions.</p>
                        <ul class="addon-features">
                            <li>Multiple animation sequences</li>
                            <li>Animation timeline control</li>
                            <li>Auto-play and loop options</li>
                            <li>Interactive animation triggers</li>
                            <li>Smooth transition effects</li>
                        </ul>
                    </div>

                    <div class="addon-card">
                        <div class="addon-icon">💬</div>
                        <h3>Annotations Addon</h3>
                        <p class="addon-description">Add interactive hotspots and information overlays to highlight specific parts of your models.</p>
                        <ul class="addon-features">
                            <li>Interactive hotspot creation</li>
                            <li>Rich text annotations</li>
                            <li>Custom hotspot styling</li>
                            <li>Click-to-reveal content</li>
                            <li>Responsive positioning</li>
                        </ul>
                    </div>

                    <div class="addon-card">
                        <div class="addon-icon">📷</div>
                        <h3>Camera Controls Addon</h3>
                        <p class="addon-description">Advanced camera interaction and movement controls for enhanced user experience.</p>
                        <ul class="addon-features">
                            <li>Custom camera positions</li>
                            <li>Smooth camera transitions</li>
                            <li>Orbit control limitations</li>
                            <li>Auto-rotate functionality</li>
                            <li>Touch gesture optimization</li>
                        </ul>
                    </div>

                    <div class="addon-card">
                        <div class="addon-icon">⏳</div>
                        <h3>Loading Options Addon</h3>
                        <p class="addon-description">Customize the loading experience with beautiful progress indicators and overlays.</p>
                        <ul class="addon-features">
                            <li>Custom loading animations</li>
                            <li>Progress bar styling</li>
                            <li>Loading text customization</li>
                            <li>Overlay color options</li>
                            <li>Percentage display control</li>
                        </ul>
                    </div>

                    <div class="addon-card">
                        <div class="addon-icon">🎨</div>
                        <h3>Materials Addon</h3>
                        <p class="addon-description">Real-time material editing and customization for dynamic visual experiences.</p>
                        <ul class="addon-features">
                            <li>Real-time material editing</li>
                            <li>Color and texture changes</li>
                            <li>Material variant switching</li>
                            <li>Custom material presets</li>
                            <li>Live preview updates</li>
                        </ul>
                    </div>

                    <div class="addon-card">
                        <div class="addon-icon">🛒</div>
                        <h3>WooCommerce Addon</h3>
                        <p class="addon-description">Enhanced e-commerce integration for product showcasing and sales optimization.</p>
                        <ul class="addon-features">
                            <li>Product gallery integration</li>
                            <li>Variant-based model switching</li>
                            <li>Add-to-cart functionality</li>
                            <li>Custom product fields</li>
                            <li>Advanced product display</li>
                        </ul>
                    </div>

                    <div class="addon-card addon-coming-soon">
                        <div class="addon-icon">🚀</div>
                        <h3>And More Many Addons Coming Soon</h3>
                        <p class="addon-description">We're constantly working on exciting new addons to enhance your 3D experience even further!</p>
                        <ul class="addon-features">
                            <li>Advanced Analytics & Tracking</li>
                            <li>Multi-language Support</li>
                            <li>Custom UI Themes</li>
                            <li>Mobile Optimization Tools</li>
                            <li>Social Media Integration</li>
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

            <section class="testimonials">
                <h2>What Our Users Say</h2>
                <div class="testimonials-grid">
                    <blockquote>
                        "ExploreXR Premium transformed how we showcase our products. The AR feature is a game-changer!"
                        <cite>- Sarah K., E-commerce Store Owner</cite>
                    </blockquote>
                    <blockquote>
                        "The 3D animations bring our models to life. Customers love the interactive experience."
                        <cite>- Mike R., Product Designer</cite>
                    </blockquote>
                    <blockquote>
                        "Interactive annotations help us explain complex products easily. Sales have increased significantly."
                        <cite>- Lisa T., Marketing Manager</cite>
                    </blockquote>
                </div>
            </section>

            <section class="upgrade-cta">
                <div class="cta-content">
                    <h2>Ready to Upgrade?</h2>
                    <p>Join thousands of satisfied customers using ExploreXR Premium</p>
                    <div class="cta-buttons">
                        <a href="<?php echo esc_url(expoxr_get_premium_upgrade_url()); ?>" class="button button-primary button-hero" target="_blank">
                            Get Premium Now
                        </a>
                        <a href="<?php echo esc_url(expoxr_get_premium_upgrade_url()); ?>demo" class="button button-secondary" target="_blank">
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
    <?php
}






