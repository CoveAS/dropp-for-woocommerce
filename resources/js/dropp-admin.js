
import Booking from './components/booking/booking';
import Consignments from './components/consignments/consignments';
import Snackbar from './includes/snackbar'

import Vue from 'vue';

if ( window._dropp ) {
	if (window['dropp-booking']) {
		new Vue( {
			el: '#dropp-booking',
			render: createElement => createElement( Booking ),
		} );
	}
	if (window['dropp-consignments']) {
		new Vue( {
			el: '#dropp-consignments',
			render: createElement => createElement( Consignments ),
		} );
	}

	jQuery( function( $ ) {
		if ( _dropp.consignments && ! _dropp.consignments.length ) {
			$( '#woocommerce-order-dropp-consignments' ).addClass( 'closed' );
		}
	} );

	window._dropp.snack = (new Snackbar()).snack;
}



require( './scripts/free-shipping-threshold.js' );
require( './scripts/load-prices-from-api.js' );
require( './scripts/orders-actions.js' );
