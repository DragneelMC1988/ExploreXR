/**
 * ExploreXR Large Model Handler
 *
 * Handles click/touch events on large-model load buttons.
 * Reads per-instance data from data-* attributes set by large-model-template.php.
 * Replaces per-instance wp_add_inline_script() calls.
 *
 * Required data attributes on each .ExploreXR-load-model-btn:
 *   data-instance-id  — unique model instance identifier
 *   data-model-url    — URL to the .glb / .gltf file
 *   data-model-attrs  — JSON-encoded model attribute array
 *
 * @package ExploreXR
 */

/* global loadExploreXRModel, ExploreXRLogger */

( function () {
	'use strict';

	/**
	 * Attach click + touchend listeners to all load buttons on the page.
	 */
	function initLargeModelButtons() {
		var buttons = document.querySelectorAll( '.ExploreXR-load-model-btn' );

		buttons.forEach( function ( btn ) {
			// Guard against double-initialisation when this script runs more
			// than once (e.g. page-builder previews).
			if ( btn.dataset.exrInit ) {
				return;
			}
			btn.dataset.exrInit = 'true';

			btn.addEventListener( 'click', handleLoadModel );
			btn.addEventListener( 'touchend', function ( e ) {
				e.preventDefault(); // prevent ghost-click on touch devices
				handleLoadModel.call( this, e );
			} );
		} );
	}

	/**
	 * Handle the load-model button activation.
	 *
	 * @param {Event} e
	 */
	function handleLoadModel( e ) {
		e.stopPropagation();

		var instanceId = this.dataset.instanceId;
		var modelUrl   = this.dataset.modelUrl;
		var attrs      = {};

		try {
			attrs = JSON.parse( this.dataset.modelAttrs || '{}' );
		} catch ( err ) {
			if ( typeof ExploreXRLogger !== 'undefined' ) {
				ExploreXRLogger.warn( 'ExploreXR: failed to parse model attrs', err );
			}
		}

		if ( typeof loadExploreXRModel === 'function' ) {
			loadExploreXRModel( instanceId, modelUrl, attrs );
		}
	}

	document.addEventListener( 'DOMContentLoaded', initLargeModelButtons );
} )();
