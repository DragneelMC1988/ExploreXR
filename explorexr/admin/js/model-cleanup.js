/**
 * ExploreXR Model Cleanup
 *
 * Dashboard widget AJAX handler for checking and marking orphaned model files.
 * The nonce and ajaxUrl are supplied via wp_localize_script() as explorexrCleanup.
 * Extracted from includes/models/model-cleanup.php.
 *
 * @package ExploreXR
 */

/* global jQuery, explorexrCleanup */

jQuery( document ).ready( function ( $ ) {
	'use strict';

	$( '#explorexr-check-orphaned-models' ).on( 'click', function ( e ) {
		e.preventDefault();

		var $button  = $( this );
		var $spinner = $button.next( '.spinner' );
		var $result  = $( '#explorexr-check-result' );

		$button.prop( 'disabled', true );
		$spinner.addClass( 'is-active' );
		$result.html( '' );

		$.ajax( {
			url:  explorexrCleanup.ajaxUrl,
			type: 'POST',
			data: {
				action: 'explorexr_cleanup_models',
				nonce:  explorexrCleanup.nonce,
			},
			success: function ( response ) {
				if ( response.success ) {
					$result.html(
						'<p class="explorexr-success">' + response.data.message + '</p>'
					);

					// Refresh the page if orphaned models were found.
					if ( response.data.results.orphaned > 0 ) {
						setTimeout( function () {
							location.reload();
						}, 2000 );
					}
				} else {
					$result.html(
						'<p class="explorexr-error">Error: ' + response.data.message + '</p>'
					);
				}
			},
			error: function () {
				$result.html(
					'<p class="explorexr-error">Error checking models. Please try again.</p>'
				);
			},
			complete: function () {
				$button.prop( 'disabled', false );
				$spinner.removeClass( 'is-active' );
			},
		} );
	} );
} );
