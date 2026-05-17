/**
 * ExploreXR Premium - Shared Layout Controls helper.
 *
 * Used by Materials and Annotations addons to read/write layout config from
 * the shared layout-controls partial, and to apply layout config to a
 * frontend container element at runtime.
 *
 * Exposes window.ExploreXRLayoutControls = {
 *   read($root)              -> { flex_direction, gap, ... }
 *   write($root, layoutObj)  -> void
 *   setUiStyle($root, style) -> void   // toggles flex-only / grid-only sections
 *   applyToContainer(el, layoutObj)    // applies inline styles to a frontend node
 * }
 *
 * The helper guards itself against being included multiple times.
 */
(function ($) {
    'use strict';

    if (window.ExploreXRLayoutControls) {
        return;
    }

    var ENUMS = {
        flex_direction: ['row', 'row-reverse', 'column', 'column-reverse'],
        flex_wrap: ['nowrap', 'wrap', 'wrap-reverse'],
        grid_auto_flow: ['row', 'column', 'row dense', 'column dense'],
        justify_content: ['flex-start', 'center', 'flex-end', 'space-between', 'space-around', 'space-evenly'],
        align_items: ['stretch', 'flex-start', 'center', 'flex-end', 'baseline'],
        align_content: ['stretch', 'flex-start', 'center', 'flex-end', 'space-between', 'space-around']
    };
    var NUMERIC = ['gap', 'row_gap', 'column_gap'];
    var FREEFORM_RE = /^[a-zA-Z0-9_\-\(\)\,\.\%\s]+$/;

    function readLayout($root) {
        var out = {};
        if (!$root || !$root.length) {
            return out;
        }
        $root.find('.explorexr-layout-input').each(function () {
            var $field = $(this);
            var key = $field.data('layout-key');
            if (!key) {
                return;
            }
            var val = ($field.val() || '').toString().trim();
            if (val === '') {
                return;
            }
            if (NUMERIC.indexOf(key) !== -1) {
                var n = parseInt(val, 10);
                if (isFinite(n) && n >= 0 && n <= 1000) {
                    out[key] = n;
                }
                return;
            }
            if (key === 'grid_template_columns') {
                if (val.length <= 200 && FREEFORM_RE.test(val)) {
                    out[key] = val;
                }
                return;
            }
            var allowed = ENUMS[key];
            if (allowed && allowed.indexOf(val) !== -1) {
                out[key] = val;
            }
        });
        return out;
    }

    function writeLayout($root, layout) {
        if (!$root || !$root.length) {
            return;
        }
        var data = (layout && typeof layout === 'object') ? layout : {};
        $root.find('.explorexr-layout-input').each(function () {
            var $field = $(this);
            var key = $field.data('layout-key');
            if (!key) {
                return;
            }
            var v = data.hasOwnProperty(key) ? data[key] : '';
            $field.val(v === undefined || v === null ? '' : v);
        });
    }

    function setUiStyle($root, uiStyle) {
        if (!$root || !$root.length) {
            return;
        }
        var style = uiStyle || '';
        $root.attr('data-ui-style', style);
        var isFlex = (style === 'buttons');
        var isGrid = (style === 'grid');
        $root.find('.explorexr-layout-flex-only').toggle(isFlex);
        $root.find('.explorexr-layout-grid-only').toggle(isGrid);
        $root.find('.explorexr-layout-align-only, .explorexr-layout-gap-only').toggle(isFlex || isGrid);
    }

    /**
     * Apply a layout config object to a frontend container element via inline styles.
     * Safe to call with empty/missing values.
     */
    function applyToContainer(el, layout) {
        if (!el || !layout || typeof layout !== 'object') {
            return;
        }
        if (layout.flex_direction)   el.style.flexDirection = layout.flex_direction;
        if (layout.flex_wrap)        el.style.flexWrap = layout.flex_wrap;
        if (layout.justify_content)  el.style.justifyContent = layout.justify_content;
        if (layout.align_items)      el.style.alignItems = layout.align_items;
        if (layout.align_content)    el.style.alignContent = layout.align_content;
        if (layout.grid_template_columns) el.style.gridTemplateColumns = layout.grid_template_columns;
        if (layout.grid_auto_flow)   el.style.gridAutoFlow = layout.grid_auto_flow;
        if (typeof layout.gap === 'number')        el.style.gap = layout.gap + 'px';
        if (typeof layout.row_gap === 'number')    el.style.rowGap = layout.row_gap + 'px';
        if (typeof layout.column_gap === 'number') el.style.columnGap = layout.column_gap + 'px';
    }

    $(document).on('click', '.explorexr-layout-controls-toggle', function () {
        var $btn = $(this);
        var $body = $btn.closest('.explorexr-layout-controls').find('.explorexr-layout-controls-body');
        var expanded = $btn.attr('aria-expanded') === 'true';
        $btn.attr('aria-expanded', expanded ? 'false' : 'true');
        $body.slideToggle(160);
        $btn.find('.dashicons')
            .toggleClass('dashicons-arrow-down-alt2', expanded)
            .toggleClass('dashicons-arrow-up-alt2', !expanded);
    });

    window.ExploreXRLayoutControls = {
        read: readLayout,
        write: writeLayout,
        setUiStyle: setUiStyle,
        applyToContainer: applyToContainer
    };

})(jQuery);
