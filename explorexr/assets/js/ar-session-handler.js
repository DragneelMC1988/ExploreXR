/**
 * ExploreXR AR Session Handler
 *
 * Handles AR session start/end events and applies iOS Quick Look polyfills.
 * Extracted from template-parts/model-viewer-script.php.
 *
 * @package ExploreXR
 */

/* global ExploreXRLogger */

( function () {
	'use strict';

	// AR session event handling
	document.addEventListener( 'DOMContentLoaded', function () {

		// Function to handle AR session events globally
		function handleARSessions() {
			document.addEventListener( 'explorexr-ar-session-started', function ( event ) {
				document.body.classList.add( 'explorexr-ar-session-active' );

				// Add visibility fix for iOS devices
				var isIOS = /iPad|iPhone|iPod/.test( navigator.userAgent ) && ! window.MSStream;
				if ( isIOS ) {
					// Force visibility in iOS AR mode
					var modelId = event.detail.instanceId;
					var modelEl = document.querySelector( '#' + modelId + '-viewer model-viewer' );
					if ( modelEl ) {
						modelEl.style.visibility = 'visible';
						modelEl.style.opacity    = '1';
						modelEl.style.transform  = 'translateZ(0)';
					}
				}
			} );

			document.addEventListener( 'explorexr-ar-session-ended', function ( event ) {
				document.body.classList.remove( 'explorexr-ar-session-active' );

				// Restore model visibility after AR session ends
				setTimeout( function () {
					var modelId = event.detail.instanceId;
					var modelEl = document.querySelector( '#' + modelId + '-viewer model-viewer' );
					if ( modelEl ) {
						modelEl.style.visibility = 'visible';
						modelEl.style.opacity    = '1';
					}
				}, 300 );
			} );
		}

		// Initialize AR session handling
		handleARSessions();

		// Polyfill for quick-look AR session detection on iOS
		var isIOS = /iPad|iPhone|iPod/.test( navigator.userAgent ) && ! window.MSStream;
		if ( isIOS ) {
			// Monitor for Quick Look session
			document.body.addEventListener( 'click', function ( event ) {
				if (
					event.target && (
						( event.target.closest && event.target.closest( 'model-viewer[ar]' ) ) ||
						( event.target.closest && event.target.closest( 'button[slot="ar-button"]' ) ) ||
						( event.target.closest && event.target.closest( '.explorexr-ar-button' ) )
					)
				) {
					// Mark potential AR session start
					setTimeout( function () {
						if ( ! document.body.classList.contains( 'explorexr-ar-session-active' ) ) {
							document.body.classList.add( 'explorexr-ar-session-active' );

							// Trigger custom event
							var modelEl = event.target.closest( 'model-viewer' );
							if ( modelEl ) {
								var modelId      = modelEl.id || 'unknown';
								var arStartEvent = new CustomEvent( 'explorexr-ar-session-started', {
									detail: {
										instanceId: modelId,
										modelUrl:   modelEl.src || 'unknown',
									},
								} );
								document.dispatchEvent( arStartEvent );
							}
						}
					}, 100 );
				}
			} );
		}
	} );
} )();
