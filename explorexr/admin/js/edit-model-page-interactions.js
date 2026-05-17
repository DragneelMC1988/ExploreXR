/**
 * Edit Model Page Interactions JavaScript
 *
 * Handles tab switching, predefined-size selection, and hidden-field
 * synchronisation for the Display Size card.
 */
jQuery(document).ready(function($) {
    'use strict';

    // -------------------------------------------------------------------------
    // Main tab functionality (Upload / Existing / Poster / Library)
    // -------------------------------------------------------------------------
    $('.explorexr-tab').on('click', function() {
        const tabId = $(this).data('tab');
        const tabGroup = $(this).closest('.explorexr-tabs').parent();

        tabGroup.find('.explorexr-tab').removeClass('active');
        $(this).addClass('active');

        tabGroup.find('.explorexr-tab-content').removeClass('active');
        tabGroup.find('#' + tabId).addClass('active');

        // Keep hidden source/method fields in sync
        if (tabId === 'upload-model') {
            $('#model_source_input').val('upload');
        } else if (tabId === 'existing-model') {
            $('#model_source_input').val('existing');
        } else if (tabId === 'upload-poster') {
            $('#poster_method_input').val('upload');
        } else if (tabId === 'library-poster') {
            $('#poster_method_input').val('library');
        }

        // ---- Display-Size tab synchronisation --------------------------------
        // When switching to "Custom Sizes", mark viewer_size as 'custom'.
        if (tabId === 'custom-sizes') {
            $('#explorexr_viewer_size_field').val('custom');
        }
        // When switching back to "Predefined Sizes", restore the checked radio value.
        if (tabId === 'predefined-sizes') {
            const checked = $('input[name="explorexr_predefined_size"]:checked').val();
            if (checked) {
                $('#explorexr_viewer_size_field').val(checked);
            }
        }
    });

    // -------------------------------------------------------------------------
    // Device tab functionality (Desktop / Tablet / Mobile)
    // -------------------------------------------------------------------------
    $('.explorexr-device-tab').on('click', function() {
        const deviceId = $(this).data('device');
        const deviceGroup = $(this).closest('.explorexr-device-tabs').parent();

        deviceGroup.find('.explorexr-device-tab').removeClass('active');
        $(this).addClass('active');

        deviceGroup.find('.explorexr-device-content').removeClass('active');
        deviceGroup.find('#' + deviceId + '-size').addClass('active');
    });

    // -------------------------------------------------------------------------
    // Predefined size radio — canonical values match shortcodes.php exactly
    // -------------------------------------------------------------------------
    /**
     * Canonical predefined dimensions.
     * MUST stay in sync with the switch in shortcodes.php.
     */
    const PREDEFINED_SIZES = {
        small:  { width: '300px', height: '300px' },
        medium: { width: '500px', height: '500px' },
        large:  { width: '800px', height: '600px' },
        full:   { width: '100vw', height: '90vh'  }
    };

    $('input[name="explorexr_predefined_size"]').on('change', function() {
        if (!$(this).is(':checked')) {
            return;
        }

        const selectedSize = $(this).val();
        const dims = PREDEFINED_SIZES[selectedSize];

        if (!dims) {
            return; // Unknown size — leave everything as-is
        }

        // 1. Update the single authoritative hidden field so the correct value
        //    is submitted with the form.
        $('#explorexr_viewer_size_field').val(selectedSize);

        // 2. Mirror the canonical values into the visible width/height inputs
        //    and trigger 'input' so preview-size-sync.js updates the preview.
        $('#viewer_width').val(dims.width).trigger('input');
        $('#viewer_height').val(dims.height).trigger('input');
    });

    // -------------------------------------------------------------------------
    // Enhanced file input display
    // -------------------------------------------------------------------------
    $('.explorexr-styled-file-input').on('change', function() {
        const $wrapper = $(this).closest('.explorexr-file-input-wrapper');
        const $textElement = $wrapper.find('.explorexr-file-input-text');

        if (this.files.length > 0) {
            $textElement.text(this.files[0].name);
            $wrapper.find('.explorexr-file-input-decoration').css('border-style', 'solid');
        } else {
            $textElement.text('Choose a file or drag it here');
            $wrapper.find('.explorexr-file-input-decoration').css('border-style', 'dashed');
        }
    });

    // -------------------------------------------------------------------------
    // Copy shortcode
    // -------------------------------------------------------------------------
    $('.copy-shortcode').on('click', function() {
        const shortcode = $(this).data('shortcode');

        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(shortcode).select();
        document.execCommand('copy');
        $temp.remove();

        const $this = $(this);
        const originalText = $this.html();
        $this.html('<span class="dashicons dashicons-yes"></span> Copied!');

        setTimeout(function() {
            $this.html(originalText);
        }, 2000);
    });

    // -------------------------------------------------------------------------
    // Poster image — WordPress Media Library
    // -------------------------------------------------------------------------
    $('#explorexr-select-poster').on('click', function(e) {
        e.preventDefault();

        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
            alert('Media Library functionality is not available. Please check your WordPress installation.');
            return;
        }

        var custom_uploader = wp.media({
            title: 'Select Poster Image',
            button: { text: 'Use this image' },
            multiple: false
        });

        custom_uploader.on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();

            $('#model_poster_url').val(attachment.url);
            $('#model_poster_id').val(attachment.id);
            $('#explorexr-poster-preview').show().find('img').attr('src', attachment.url);
            $('.explorexr-poster-remove-btn').show();
        });

        custom_uploader.open();
    });

    $(document).on('click', '.explorexr-poster-remove-btn', function(e) {
        e.preventDefault();

        $('#model_poster_url').val('');
        $('#model_poster_id').val('');
        $('#explorexr-poster-preview').hide().find('img').attr('src', '');
        $(this).hide();
    });

    $('input[name="remove_poster"]').on('change', function() {
        if ($(this).is(':checked')) {
            $('#explorexr-poster-preview').hide();
        } else {
            $('#explorexr-poster-preview').show();
        }
    });

    // -------------------------------------------------------------------------
    // Initialise Display Size state on page load
    // -------------------------------------------------------------------------
    (function initializeDisplaySizeState() {
        const activeTab = $('.explorexr-tab.active[data-tab]').data('tab');
        const selectedPredefinedSize = $('input[name="explorexr_predefined_size"]:checked').val();

        if (activeTab === 'predefined-sizes' && selectedPredefinedSize) {
            // Ensure hidden field reflects the checked radio
            $('#explorexr_viewer_size_field').val(selectedPredefinedSize);

            const dims = PREDEFINED_SIZES[selectedPredefinedSize];
            if (dims) {
                $('#viewer_width').val(dims.width);
                $('#viewer_height').val(dims.height);
            }
        } else {
            // Custom tab is active — ensure hidden field says 'custom'
            if (activeTab === 'custom-sizes') {
                $('#explorexr_viewer_size_field').val('custom');
            }
        }
    })();
});
