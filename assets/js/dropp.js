jQuery( function( $ ) {
	var button_handler = function( e ) {
		if ( typeof chooseDroppLocation === 'undefined' ) {
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
	};
	var script_loaded = false;
	var init_dropp = function() {
		if ( ! script_loaded && $( '.dropp-location' ).length ) {
			// Only load the external script if Dropp.is is part of the available shipping options
			$.getScript( _dropp.dropplocationsurl );
			script_loaded = true;
		}
		$( '.dropp-location .button' ).on( 'click', button_handler );
	}

	$( document ).on( 'updated_checkout', function() {
		init_dropp();
	} );
	init_dropp();
} );
