<?php
/**
 * Size Validation Utility
 * 
 * Validates width/height inputs for model viewer sizing.
 * Prevents invisible models by disallowing both dimensions to use percentage units.
 * 
 * @package ExploreXR_Premium
 */

if (!defined('ABSPATH')) {
    exit;
}

class ExploreXR_Size_Validator {
    
    /**
     * Valid CSS units for model sizing.
     * Percentage (%) is not allowed because model-viewer cannot resolve
     * percentage dimensions reliably — parent containers in WordPress
     * themes typically lack explicit width/height.
     */
    const VALID_UNITS = ['px', 'em', 'rem', 'vw', 'vh', 'dvw', 'dvh'];

    /**
     * Absolute units (don't require parent height)
     */
    const ABSOLUTE_UNITS = ['px', 'em', 'rem', 'vw', 'vh', 'dvw', 'dvh'];
    
    /**
     * Validate a single dimension value
     * 
     * @param string $value The dimension value (e.g., '500px', '100vw')
     * @return array ['valid' => bool, 'unit' => string, 'numeric' => float, 'error' => string]
     */
    public static function validate_dimension($value) {
        if (empty($value) || !is_string($value)) {
            return [
                'valid' => false,
                'unit' => '',
                'numeric' => 0,
                'error' => 'Value cannot be empty'
            ];
        }
        
        // Trim whitespace
        $value = trim($value);
        
        // Extract numeric and unit parts — percentage (%) is not allowed
        if (!preg_match('/^(\d+(?:\.\d+)?)(px|em|rem|vw|vh|dvw|dvh)$/i', $value, $matches)) {
            return [
                'valid' => false,
                'unit' => '',
                'numeric' => 0,
                'error' => 'Invalid format. Use: 500px, 50vw, 90vh, 100dvw, etc. Percentage (%) is not allowed.'
            ];
        }
        
        $numeric = floatval($matches[1]);
        $unit = strtolower($matches[2]);
        
        // Validate unit is in allowed list
        if (!in_array($unit, self::VALID_UNITS, true)) {
            return [
                'valid' => false,
                'unit' => $unit,
                'numeric' => $numeric,
                'error' => 'Unit "' . $unit . '" is not supported'
            ];
        }
        
        // Validate numeric range
        if ($numeric <= 0) {
            return [
                'valid' => false,
                'unit' => $unit,
                'numeric' => $numeric,
                'error' => 'Value must be greater than 0'
            ];
        }
        
        // Warn about very large values
        if ($unit === 'px' && $numeric > 5000) {
            return [
                'valid' => true,
                'unit' => $unit,
                'numeric' => $numeric,
                'warning' => 'Value is very large (may cause performance issues)'
            ];
        }
        
        return [
            'valid' => true,
            'unit' => $unit,
            'numeric' => $numeric,
            'error' => ''
        ];
    }
    
    /**
     * Validate width and height combination
     *
     * @param string $width Width value
     * @param string $height Height value
     * @return array ['valid' => bool, 'error' => string, 'width' => array, 'height' => array]
     */
    public static function validate_size_pair($width, $height) {
        $width_result = self::validate_dimension($width);
        $height_result = self::validate_dimension($height);

        // Check individual validity
        if (!$width_result['valid']) {
            return [
                'valid' => false,
                'error' => 'Width error: ' . $width_result['error'],
                'width' => $width_result,
                'height' => $height_result
            ];
        }

        if (!$height_result['valid']) {
            return [
                'valid' => false,
                'error' => 'Height error: ' . $height_result['error'],
                'width' => $width_result,
                'height' => $height_result
            ];
        }

        return [
            'valid' => true,
            'error' => '',
            'width' => $width_result,
            'height' => $height_result
        ];
    }
    
    /**
     * Sanitize and validate dimension, with fallback
     * 
     * @param string $value Input value
     * @param string $fallback Fallback value if invalid
     * @return string Validated value or fallback
     */
    public static function sanitize_dimension($value, $fallback = '100vw') {
        $result = self::validate_dimension($value);
        return $result['valid'] ? $value : $fallback;
    }
    
    /**
     * Check if a unit is absolute (doesn't need parent height)
     * 
     * @param string $unit CSS unit (px, %, vw, etc.)
     * @return bool True if absolute unit
     */
    public static function is_absolute_unit($unit) {
        return in_array(strtolower($unit), self::ABSOLUTE_UNITS, true);
    }
}
