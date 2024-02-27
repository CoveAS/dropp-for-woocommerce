
import Booking from './components/booking/booking';
import Consignments from './components/consignments/consignments';

import Vue from 'vue';

if ( window._dropp ) {
	if (window['dropp-booking']) {
		new Vue( {
			el: '#dropp-booking',
			render: createElement => createElement( Booking )
			,
		} );
	}
	if (window['dropp-consignments']) {
		new Vue( {
			el: '#dropp-consignments',
			render: createElement => createElement( Consignments )
			,
		} );
	}

	jQuery( function( $ ) {
		if ( _dropp.locations && ! _dropp.locations.length ) {
			$( '#woocommerce-order-dropp-booking' ).addClass( 'closed' );
		}
	} );
}

require( './scripts/free-shipping-threshold.js' );
require( './scripts/load-prices-from-api.js' );
