/**
 * ExploreXR Viewer Controls Card
 *
 * Handles auto-rotate toggle / live preview sync for the edit-model page.
 * Extracted from admin/templates/edit-model/viewer-controls-card.php.
 *
 * @package ExploreXR
 */

/* global jQuery */

jQuery( document ).ready( function ( $ ) {
	'use strict';

	var mainPreview = document.getElementById( 'main-preview-model-viewer' );

	// Update admin preview when enable interactions checkbox changes.
	// The admin preview always keeps camera-controls for usability;
	// this toggle only affects frontend rendering.
	$( '#explorexr_enable_interactions' ).on( 'change', function () {
		// No-op: preview always keeps camera-controls.
	} );

	// Toggle auto-rotate settings visibility & sync to main preview.
	$( '#explorexr_auto_rotate' ).on( 'change', function () {
		if ( $( this ).is( ':checked' ) ) {
			$( '#auto-rotate-settings' ).slideDown();
			$( '#explorexr_auto_rotate_delay, #explorexr_auto_rotate_speed' ).prop( 'disabled', false );

			// Sync auto-rotate to main preview.
			if ( mainPreview ) {
				var speed = $( '#explorexr_auto_rotate_speed' ).val() || '30deg';
				var delay = $( '#explorexr_auto_rotate_delay' ).val() || '5000';
				mainPreview.setAttribute( 'auto-rotate', '' );
				mainPreview.setAttribute( 'rotation-per-second', speed );
				mainPreview.setAttribute( 'auto-rotate-delay', delay );
			}
		} else {
			$( '#auto-rotate-settings' ).slideUp();

			// Remove auto-rotate from main preview.
			if ( mainPreview ) {
				mainPreview.removeAttribute( 'auto-rotate' );
				mainPreview.removeAttribute( 'rotation-per-second' );
				mainPreview.removeAttribute( 'auto-rotate-delay' );
			}
		}
	} );

	// Sync auto-rotate speed changes to main preview.
	$( '#explorexr_auto_rotate_speed' ).on( 'input change', function () {
		if ( mainPreview && $( '#explorexr_auto_rotate' ).is( ':checked' ) ) {
			mainPreview.setAttribute( 'rotation-per-second', this.value || '30deg' );
		}
	} );

	// Sync auto-rotate delay changes to main preview.
	$( '#explorexr_auto_rotate_delay' ).on( 'input change', function () {
		if ( mainPreview && $( '#explorexr_auto_rotate' ).is( ':checked' ) ) {
			mainPreview.setAttribute( 'auto-rotate-delay', this.value || '5000' );
		}
	} );

	// Initialise field visibility on page load.
	if ( ! $( '#explorexr_auto_rotate' ).is( ':checked' ) ) {
		$( '#auto-rotate-settings' ).hide();
	}
} );
