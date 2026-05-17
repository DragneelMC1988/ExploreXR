/**
 * ExploreXR Model Preview Card
 *
 * Initialises the live admin preview and exposes explorexrUpdateMainPreview()
 * for addon cards to push live attribute changes into the preview model-viewer.
 *
 * The model ID is read from data-model-id on #explorexr-model-preview-container.
 * Extracted from admin/templates/edit-model/model-preview-card.php.
 *
 * @package ExploreXR
 */

/* global jQuery, ExploreXRLogger */

jQuery( document ).ready( function ( $ ) {
	'use strict';

	var container        = document.getElementById( 'explorexr-model-preview-container' );
	var mainPreviewViewer = document.getElementById( 'main-preview-model-viewer' );

	if ( mainPreviewViewer ) {
		var modelId = container ? parseInt( container.dataset.modelId, 10 ) : 0;

		/**
		 * Push a map of attribute name → value into the live preview model-viewer.
		 * Addon cards call this to reflect setting changes in real time.
		 *
		 * @param {Object} attributes
		 */
		window.explorexrUpdateMainPreview = function ( attributes ) {
			if ( ! attributes || ! mainPreviewViewer ) {
				return;
			}

			Object.entries( attributes ).forEach( function ( entry ) {
				var key   = entry[ 0 ];
				var value = entry[ 1 ];

				if ( value === undefined || value === null || value === '' ) {
					mainPreviewViewer.removeAttribute( key );
					return;
				}
				if ( value === true || value === 'true' ) {
					mainPreviewViewer.setAttribute( key, '' );
					return;
				}
				mainPreviewViewer.setAttribute( key, value );
			} );
		};

		// Listen for addon update events.
		document.addEventListener( 'explorexr:addon:update', function ( event ) {
			if ( event && event.detail && event.detail.attributes ) {
				window.explorexrUpdateMainPreview( event.detail.attributes );
			}
		} );

		// Trigger annotation handler initialisation if the addon is active.
		if (
			typeof window.ExploreXRAnnotations !== 'undefined' &&
			mainPreviewViewer.hasAttribute( 'data-annotations' )
		) {
			if ( typeof ExploreXRLogger !== 'undefined' ) {
				ExploreXRLogger.log( 'Initializing annotations for main preview' );
			}
			// annotations-handler.js will automatically pick up this model-viewer.
		}

		// Signal to other components that the preview is ready.
		$( document ).trigger( 'explorexr:preview:loaded', [ mainPreviewViewer, modelId ] );
	}
} );
