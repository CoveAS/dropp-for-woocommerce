jQuery( function( $ ) {
	var loading_status = 0;
	var dropp_handler = {
		click: function( e ) {
			if ( typeof chooseDroppLocation === 'undefined' ) {
				// @TODO: Error handling for when the choose dropp location function does not exist
				return;
			}
			e.preventDefault();
			var elems = {
				id:      $( this ).parent().find( 'input.dropp-location__id' ),
				name:    $( this ).parent().find( 'input.dropp-location__name' ),
				address: $( this ).parent().find( 'input.dropp-location__address' ),
			};
			chooseDroppLocation()
				.then( function( location ) {
					// A location was picked. Save it.
					elems.id.val( location.id );
					elems.name.val( location.name ).show();
					elems.address.val( location.address );
				} )
				.catch( function( error ) {
					// Something went wrong.
					// @TODO.
					console.log( error );
				});
		},
		show_selector: function() {
			$( '.dropp-error' ).hide();
			$( '.dropp-location' ).show();
			$( '.dropp-location .button' ).on( 'click', dropp_handler.click );
			$( '#shipping_method' ).unblock();
		},
		block_shipping_methods: function() {
			$( '#shipping_method' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			} );
		},
		init: function() {
			if ( ! loading_status && $( '.dropp-location' ).length ) {
				// Only load the external script if Dropp.is is part of the available shipping options
				var res = $.ajax(
					{
						url:      _dropp.dropplocationsurl,
						dataType: "script",
						success:  dropp_handler.success,
						error:    dropp_handler.error,
						timeout:  3000,
					}
				);
				dropp_handler.block_shipping_methods();
				loading_status = 1;
			} else if ( 2 == loading_status ) {
				dropp_handler.show_selector();
			} else if ( 1 == loading_status ) {
				// Shipping methods were updated, but the selector is still loading.
				dropp_handler.block_shipping_methods();
			}
		},
		success: function( content, textStatus, jqXHR ) {
			loading_status = 2;
			dropp_handler.show_selector();
		},
		error: function( jqXHR, textStatus, errorThrown ) {
			loading_status = 0;
			$( '.dropp-error' ).show().text( _dropp.i18n.error_loading );
			$( '.dropp-location' ).hide();
			$( '#shipping_method' ).unblock();
		},
	};

	$( document ).on( 'updated_checkout', dropp_handler.init );
	dropp_handler.init();
} );
