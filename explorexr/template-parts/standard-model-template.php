<?php
/**
 * Template for displaying standard 3D models.
 * 
 * @package ExploreXR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Available variables:
 * $attributes_html - HTML string of attributes for model-viewer
 * $model_attributes - Array of model attributes
 */

// Extract container dimensions and remove from attributes
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed from parent scope
$container_width = isset($model_attributes['_container_width']) ? $model_attributes['_container_width'] : '100vw';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable passed from parent scope
$container_height = isset($model_attributes['_container_height']) ? $model_attributes['_container_height'] : '500px';

// AR button styling helpers - NO DEFAULTS, use user's AR Card values only
$explorexr_ar_button_styles = array(
    'background-color' => $model_attributes['data-ar-button-bg-color'] ?? $model_attributes['ar-button-bg-color'] ?? null,
    'color' => $model_attributes['data-ar-button-text-color'] ?? $model_attributes['ar-button-text-color'] ?? null,
    'border-color' => $model_attributes['data-ar-button-border-color'] ?? $model_attributes['ar-button-border-color'] ?? null,
    'border-radius' => $model_attributes['data-ar-button-border-radius'] ?? $model_attributes['ar-button-border-radius'] ?? null,
);

$explorexr_size = $model_attributes['data-ar-button-size'] ?? $model_attributes['ar-button-size'] ?? null;
$explorexr_padding_map = array(
    'small' => '6px 10px',
    'medium' => '10px 14px',
    'large' => '14px 18px'
);
$explorexr_font_map = array(
    'small' => '13px',
    'medium' => '14px',
    'large' => '16px'
);
$explorexr_button_padding = isset($explorexr_padding_map[$explorexr_size]) ? $explorexr_padding_map[$explorexr_size] : $explorexr_padding_map['medium'];
$explorexr_button_font_size = isset($explorexr_font_map[$explorexr_size]) ? $explorexr_font_map[$explorexr_size] : $explorexr_font_map['medium'];

// Position map - NO DEFAULT, use user's AR Card position only
$explorexr_position = $model_attributes['data-ar-button-position'] ?? $model_attributes['ar-button-position'] ?? null;
$explorexr_position_style = 'position: absolute; z-index: 1000;';
switch ($explorexr_position) {
    case 'top-left':
        $explorexr_position_style .= ' top: 16px; left: 16px;';
        break;
    case 'top-right':
        $explorexr_position_style .= ' top: 16px; right: 16px;';
        break;
    case 'bottom-left':
        $explorexr_position_style .= ' bottom: 16px; left: 16px;';
        break;
    case 'bottom-right':
        $explorexr_position_style .= ' bottom: 16px; right: 16px;';
        break;
    case 'bottom-center':
    default:
        $explorexr_position_style .= ' bottom: 16px; left: 50%; transform: translateX(-50%);';
        break;
}

// Build AR button icon markup - ONLY if attribute exists (icon is enabled)
$explorexr_ar_icon = $model_attributes['data-ar-button-icon'] ?? $model_attributes['ar-button-icon'] ?? '';
$explorexr_icon_position = $model_attributes['data-ar-button-icon-position'] ?? $model_attributes['ar-button-icon-position'] ?? 'right';
$explorexr_ar_icon_markup = '';

// Only process icon if it exists AND is not empty
if (isset($model_attributes['ar-button-icon']) && !empty($explorexr_ar_icon)) {
    if (strpos($explorexr_ar_icon, '<svg') !== false) {
        // Inline SVG provided
        $explorexr_ar_icon_markup = $explorexr_ar_icon;
    } elseif (filter_var($explorexr_ar_icon, FILTER_VALIDATE_URL)) {
        $explorexr_ar_icon_markup = sprintf('<img src="%s" alt="" loading="lazy" style="width: 24px; height: 24px;">', esc_url($explorexr_ar_icon));
    } else {
        $explorexr_ar_icon_markup = sprintf('<span class="%s" aria-hidden="true"></span>', esc_attr($explorexr_ar_icon));
    }
}

// Button text (support UTF-8) - NO DEFAULT, use user's AR Card text only
$explorexr_ar_button_text = $model_attributes['data-ar-button-text'] ?? $model_attributes['ar-button-text'] ?? null;

// Extract the ID for the container (used for responsive CSS targeting)
$explorexr_container_id = isset($model_attributes['id']) ? $model_attributes['id'] : '';

// CRITICAL FIX: Check if we have responsive sizes - if yes, don't use inline styles
// Let CSS media queries handle sizing instead
$explorexr_has_responsive_sizes = !empty($explorexr_container_id) && (
    isset($model_attributes['_mobile_width']) || 
    isset($model_attributes['_mobile_height']) ||
    isset($model_attributes['_tablet_width']) ||
    isset($model_attributes['_tablet_height'])
);

// Determine size type for CSS class
$explorexr_size_type = 'custom';
if (isset($model_attributes['_size_preset'])) {
    $explorexr_size_type = $model_attributes['_size_preset'];
}
?>

<div class="ExploreXR-model-container explorexr-size-<?php echo esc_attr($explorexr_size_type); ?>"
     id="<?php echo esc_attr($explorexr_container_id); ?>"
     data-width="<?php echo esc_attr($container_width); ?>"
     data-height="<?php echo esc_attr($container_height); ?>">
    <model-viewer<?php echo $attributes_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributes are generated and escaped by EXPLOREXR_generate_attributes_html() 
    ?>>
        <?php if (isset($model_attributes['ar'])) : ?>
            <?php
                // Build button styles using CSS custom properties
                $explorexr_button_style_parts = array();
                
                // Add CSS custom properties for colors/borders/sizing (CSS will apply !important)
                if ($explorexr_ar_button_styles['background-color'] !== null && $explorexr_ar_button_styles['background-color'] !== '') {
                    $explorexr_button_style_parts[] = '--ar-button-bg:' . esc_attr($explorexr_ar_button_styles['background-color']);
                }
                
                if ($explorexr_ar_button_styles['color'] !== null && $explorexr_ar_button_styles['color'] !== '') {
                    $explorexr_button_style_parts[] = '--ar-button-color:' . esc_attr($explorexr_ar_button_styles['color']);
                }
                
                // Border: convert color to full border value for CSS variable
                if ($explorexr_ar_button_styles['border-color'] !== null && $explorexr_ar_button_styles['border-color'] !== '') {
                    $explorexr_button_style_parts[] = '--ar-button-border:1px solid ' . esc_attr($explorexr_ar_button_styles['border-color']);
                } else {
                    $explorexr_button_style_parts[] = '--ar-button-border:none';
                }
                
                if ($explorexr_ar_button_styles['border-radius'] !== null && $explorexr_ar_button_styles['border-radius'] !== '') {
                    $explorexr_button_style_parts[] = '--ar-button-radius:' . esc_attr($explorexr_ar_button_styles['border-radius']) . 'px';
                }
                
                // Size-based padding and font-size via CSS custom properties
                if ($explorexr_size !== null && isset($explorexr_padding_map[$explorexr_size])) {
                    $explorexr_button_style_parts[] = '--ar-button-padding:' . esc_attr($explorexr_padding_map[$explorexr_size]);
                }
                if ($explorexr_size !== null && isset($explorexr_font_map[$explorexr_size])) {
                    $explorexr_button_style_parts[] = '--ar-button-font-size:' . esc_attr($explorexr_font_map[$explorexr_size]);
                }
                
                // Position still needs inline styles (absolute positioning)
                if ($explorexr_position !== null) {
                    $explorexr_button_style_parts[] = 'position:absolute';
                    $explorexr_button_style_parts[] = 'z-index:1000';
                    switch ($explorexr_position) {
                        case 'top-left':
                            $explorexr_button_style_parts[] = 'top:16px';
                            $explorexr_button_style_parts[] = 'left:16px';
                            break;
                        case 'top-right':
                            $explorexr_button_style_parts[] = 'top:16px';
                            $explorexr_button_style_parts[] = 'right:16px';
                            break;
                        case 'bottom-left':
                            $explorexr_button_style_parts[] = 'bottom:16px';
                            $explorexr_button_style_parts[] = 'left:16px';
                            break;
                        case 'bottom-right':
                            $explorexr_button_style_parts[] = 'bottom:16px';
                            $explorexr_button_style_parts[] = 'right:16px';
                            break;
                        case 'bottom-center':
                        default:
                            $explorexr_button_style_parts[] = 'bottom:16px';
                            $explorexr_button_style_parts[] = 'left:50%';
                            $explorexr_button_style_parts[] = 'transform:translateX(-50%)';
                            break;
                    }
                }
                
                $explorexr_button_style = implode('; ', $explorexr_button_style_parts);
            ?>
            <?php 
            // CRITICAL: Only render button if user has set text - NO DEFAULT
            if ($explorexr_ar_button_text !== null && $explorexr_ar_button_text !== '') : 
            ?>
                <button slot="ar-button" class="ExploreXR-ar-button explorexr-premium-ar-button" data-ExploreXR-ar-button="true" style="<?php echo esc_attr( $explorexr_button_style ); ?>">
                    <?php if ($explorexr_ar_icon_markup && $explorexr_icon_position === 'left') : ?>
                        <span class="explorexr-premium-ar-button-icon" aria-hidden="true"><?php echo wp_kses_post( $explorexr_ar_icon_markup ); ?></span>
                    <?php endif; ?>
                    
                    <span class="explorexr-premium-ar-button-text"><?php echo esc_html($explorexr_ar_button_text); ?></span>
                    
                    <?php if ($explorexr_ar_icon_markup && $explorexr_icon_position !== 'left') : ?>
                        <span class="explorexr-premium-ar-button-icon" aria-hidden="true"><?php echo wp_kses_post( $explorexr_ar_icon_markup ); ?></span>
                    <?php endif; ?>
                </button>
            <?php endif; ?>
            
            <!-- AR not supported message (hidden by default, shown by JavaScript if needed) -->
            <div class="ExploreXR-ar-not-supported" style="display: none; position: absolute; bottom: 16px; right: 16px; background: rgba(0,0,0,0.8); color: white; padding: 8px 12px; border-radius: 4px; font-size: 14px; z-index: 10;">
                <?php esc_html_e('AR not supported on this device', 'explorexr'); ?>
            </div>
        <?php endif; ?>
    </model-viewer>

    <!-- Overlay position containers: addons inject their UI here to prevent overlap -->
    <div class="explorexr-overlay-group explorexr-overlay-top-left" data-position="top-left"></div>
    <div class="explorexr-overlay-group explorexr-overlay-top-right" data-position="top-right"></div>
    <div class="explorexr-overlay-group explorexr-overlay-bottom-left" data-position="bottom-left"></div>
    <div class="explorexr-overlay-group explorexr-overlay-bottom-right" data-position="bottom-right"></div>
    <div class="explorexr-overlay-group explorexr-overlay-bottom-center" data-position="bottom-center"></div>

    <?php
    // Action hook for addons to inject content after model-viewer
    // Used by morphing addon for buttons, post-processing overlays, etc.
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- $model_id is template variable
    if (isset($model_id) && $model_id) {
        do_action('explorexr_after_model_viewer', $model_id);
    }
    ?>
</div>
