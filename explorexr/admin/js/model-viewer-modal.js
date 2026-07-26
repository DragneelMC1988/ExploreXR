/**
 * ExploreXR Model Viewer Modal
 *
 * Lazily initialises the <model-viewer> element inside the admin preview
 * modal the first time a "preview-model" / "view-3d-model" button is clicked.
 * Extracted from admin/templates/model-viewer-modal.php.
 *
 * @package ExploreXR
 */

document.addEventListener( 'DOMContentLoaded', function () {
	'use strict';

	var modelViewerLoaded = false;

	/**
	 * Create the <model-viewer> element inside the modal container.
	 */
	function loadModelViewer() {
		if ( modelViewerLoaded ) {
			return;
		}

		var container = document.getElementById( 'explorexr-model-viewer-container' );
		if ( container ) {
			container.innerHTML =
				'<model-viewer id="explorexr-model-viewer" camera-controls auto-rotate ' +
				'loading="eager" reveal="interaction"></model-viewer>';
			modelViewerLoaded = true;

			// Trigger model-viewer script loading if needed
			if ( typeof window.loadExploreXRModelViewer === 'function' ) {
				window.loadExploreXRModelViewer();
			}
		}
	}

	// Load model-viewer when a preview button is clicked
	document.addEventListener( 'click', function ( e ) {
		if (
			e.target.classList.contains( 'preview-model' ) ||
			( e.target.closest && e.target.closest( '.preview-model' ) ) ||
			e.target.classList.contains( 'view-3d-model' ) ||
			( e.target.closest && e.target.closest( '.view-3d-model' ) )
		) {
			loadModelViewer();
		}
	} );
} );
