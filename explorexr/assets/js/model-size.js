/**
 * ExploreXR Display Size Handler
 *
 * Single authoritative handler for display size tab logic.
 * Manages the predefined/custom tab switch and syncs the hidden
 * viewer_size field that is submitted with the form.
 *
 * Architecture:
 * - One hidden field `#explorexr_viewer_size_field` holds the submitted value.
 * - Predefined tab: radio buttons `name="explorexr_predefined_size"` update
 *   the hidden field on change.
 * - Custom tab: the hidden field is set to "custom".
 * - Tab switch: updates the hidden field immediately.
 */
jQuery(document).ready(function($) {
    'use strict';

    // Bail early if the size card is not on this page
    var $sizeField = $('#explorexr_viewer_size_field');
    if (!$sizeField.length) {
        return;
    }

    // ─── Tab switching (predefined / custom) ─────────────────────────

    // Size-card specific tab handler (scoped to #explorexr-display-size-card)
    var $sizeCard = $('#explorexr-display-size-card');

    $sizeCard.find('.explorexr-tab').on('click', function() {
        var tabId = $(this).data('tab');
        var $parent = $(this).closest('.explorexr-tabs').parent();

        // Switch active tab button
        $parent.find('> .explorexr-tabs .explorexr-tab').removeClass('active');
        $(this).addClass('active');

        // Switch active tab content
        $parent.find('> .explorexr-tab-content').removeClass('active');
        $parent.find('#' + tabId).addClass('active');

        // Sync the hidden field
        if (tabId === 'custom-sizes') {
            $sizeField.val('custom');
        } else if (tabId === 'predefined-sizes') {
            // Set to whatever radio is selected (or default to medium)
            var selected = $('input[name="explorexr_predefined_size"]:checked').val();
            $sizeField.val(selected || 'medium');
        }
    });

    // ─── Device sub-tabs (Desktop / Tablet / Mobile) ─────────────────

    $sizeCard.find('.explorexr-device-tab').on('click', function() {
        var deviceId = $(this).data('device');
        var $group = $(this).closest('.explorexr-device-tabs').parent();

        $group.find('.explorexr-device-tab').removeClass('active');
        $(this).addClass('active');

        $group.find('.explorexr-device-content').removeClass('active');
        $group.find('#' + deviceId + '-size').addClass('active');
    });

    // ─── Predefined size radio selection ─────────────────────────────

    $('input[name="explorexr_predefined_size"]').on('change', function() {
        var val = $(this).val();
        $sizeField.val(val);

        // Also update the custom width/height fields for preview sync
        var map = {
            small:  { w: '300px',  h: '300px' },
            medium: { w: '500px',  h: '500px' },
            large:  { w: '800px',  h: '600px' },
            full:   { w: '100vw',  h: '90vh'  }
        };

        if (map[val]) {
            $('#viewer_width').val(map[val].w).trigger('input');
            $('#viewer_height').val(map[val].h).trigger('input');
        }
    });

    // ─── Media Library poster selector ───────────────────────────────

    $('#explorexr-select-poster').on('click', function(e) {
        e.preventDefault();

        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
            return;
        }

        var frame = wp.media({
            title: 'Select Model Poster Image',
            button: { text: 'Use this image' },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#model_poster_id').val(attachment.id);
            $('#model_poster_url').val(attachment.url);
            $('#explorexr-poster-preview').show().find('img').attr('src', attachment.url);
        });

        frame.open();
    });

    // ─── Percent-to-viewport auto-conversion ──────────────────────
    // model-viewer does not support percentage (%) dimensions.
    // Automatically convert "X%" to "Xvw" (width fields) or "Xvh" (height fields).

    var widthFields  = ['#viewer_width', '#tablet_viewer_width', '#mobile_viewer_width'];
    var heightFields = ['#viewer_height', '#tablet_viewer_height', '#mobile_viewer_height'];

    function convertPercentToViewport(inputId, unit) {
        $(inputId).on('change', function() {
            var val = $(this).val().trim();
            var match = val.match(/^(\d+(?:\.\d+)?)\s*%$/);
            if (match) {
                $(this).val(match[1] + unit);
            }
        });
    }

    widthFields.forEach(function(id) { convertPercentToViewport(id, 'vw'); });
    heightFields.forEach(function(id) { convertPercentToViewport(id, 'vh'); });

    // ─── Initialization ──────────────────────────────────────────────

    // On page load, ensure the hidden field matches current UI state
    (function initSizeState() {
        var activeTab = $sizeCard.find('.explorexr-tab.active').data('tab');

        if (activeTab === 'predefined-sizes') {
            var selected = $('input[name="explorexr_predefined_size"]:checked').val();
            if (selected) {
                $sizeField.val(selected);
            }
        } else {
            $sizeField.val('custom');
        }
    })();
});
