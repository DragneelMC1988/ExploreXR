/**
 * Edit Model Page JavaScript
 *
 * Handles interactions on the Edit Model page that are NOT related to
 * display size (size tabs, device tabs, predefined radio buttons).
 * All size logic is handled by assets/js/model-size.js.
 */
jQuery(document).ready(function($) {
    'use strict';

    // ─── WordPress admin menu compatibility ──────────────────────────

    function fixAdminMenuScroll() {
        try {
            $('html, body').css({
                'overflow': 'visible',
                'height': 'auto'
            });
        } catch (e) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.warn('ExploreXR: Admin menu scroll fix error:', e);
            }
        }
    }

    setTimeout(function() {
        try {
            fixAdminMenuScroll();
        } catch (e) {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.warn('ExploreXR: Delayed admin menu fix error:', e);
            }
        }
    }, 500);

    // ─── Non-size tab functionality (model source, poster method) ────

    // Handle tabs that are NOT part of the display-size card
    $(document).on('click', '.explorexr-tab', function() {
        var tabId = $(this).data('tab');

        // Skip size-related tabs — those are handled by model-size.js
        if (tabId === 'predefined-sizes' || tabId === 'custom-sizes') {
            return;
        }

        var $parent = $(this).closest('.explorexr-tabs').parent();

        $parent.find('> .explorexr-tabs .explorexr-tab').removeClass('active');
        $(this).addClass('active');

        $parent.find('> .explorexr-tab-content').removeClass('active');
        $parent.find('#' + tabId).addClass('active');

        // Update hidden input values for form submission
        if (tabId === 'upload-model') {
            $('#model_source_input').val('upload');
        } else if (tabId === 'existing-model') {
            $('#model_source_input').val('existing');
        } else if (tabId === 'upload-poster') {
            $('#poster_method_input').val('upload');
        } else if (tabId === 'library-poster') {
            $('#poster_method_input').val('library');
        }
    });

    // ─── Enhanced file input ─────────────────────────────────────────

    $('.explorexr-styled-file-input').on('change', function() {
        var $wrapper = $(this).closest('.explorexr-file-input-wrapper');
        var $textElement = $wrapper.find('.explorexr-file-input-text');

        if (this.files.length > 0) {
            $textElement.text(this.files[0].name);
            $wrapper.find('.explorexr-file-input-decoration').css('border-style', 'solid');
        } else {
            $textElement.text('Choose a file or drag it here');
            $wrapper.find('.explorexr-file-input-decoration').css('border-style', 'dashed');
        }
    });

    // ─── Copy shortcode ──────────────────────────────────────────────

    $('.copy-shortcode').on('click', function() {
        var shortcode = $(this).data('shortcode');

        var $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(shortcode).select();
        document.execCommand('copy');
        $temp.remove();

        var $this = $(this);
        var originalText = $this.html();
        $this.html('<span class="dashicons dashicons-yes"></span> Copied!');

        setTimeout(function() {
            $this.html(originalText);
        }, 2000);
    });

    // ─── Poster handling ─────────────────────────────────────────────

    var mediaUploader;
    $('#explorexr-select-poster').on('click', function(e) {
        e.preventDefault();

        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
            if (typeof ExploreXRLogger !== 'undefined') {
                ExploreXRLogger.error('WordPress Media Library is not available');
            }
            alert('Media Library functionality is not available. Please check your WordPress installation.');
            return;
        }

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Select Model Poster Image',
            button: { text: 'Use this image' },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#model_poster_id').val(attachment.id);
            $('#model_poster_url').val(attachment.url);
            $('#explorexr-poster-preview').show().find('img').attr('src', attachment.url);
        });

        mediaUploader.open();
    });

    $('input[name="remove_poster"]').on('change', function() {
        if ($(this).is(':checked')) {
            $('#explorexr-poster-preview').hide();
        } else {
            $('#explorexr-poster-preview').show();
        }
    });

    // ─── Auto-rotate settings toggle ─────────────────────────────────

    $('#explorexr_auto_rotate').on('change', function() {
        var autoRotateSettings = $('#auto-rotate-settings');
        if ($(this).is(':checked')) {
            autoRotateSettings.slideDown();
        } else {
            autoRotateSettings.slideUp();
        }
    });

    // ─── Notification helper ─────────────────────────────────────────

    function showNotification(message, type) {
        type = type || 'info';
        var $notification = $(
            '<div class="explorexr-edit-notification ' + type + '">' +
                '<span class="notification-message">' + message + '</span>' +
                '<button class="notification-close">&times;</button>' +
            '</div>'
        );

        $('body').append($notification);

        setTimeout(function() {
            $notification.fadeOut(function() { $notification.remove(); });
        }, 3000);

        $notification.find('.notification-close').on('click', function() {
            $notification.fadeOut(function() { $notification.remove(); });
        });
    }

    // ─── URL validation helper ───────────────────────────────────────

    function isValidURL(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
});
