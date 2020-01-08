// import ProductItem from './components/product-item';
import Booking from './components/booking/booking';

if ( window._dropp ) {

	window.Vue = require( 'vue' );

	var settings = new Vue( {
		el: '#dropp-booking',
		render:  function( createElement ) {
			return createElement( Booking );
		},
		data: {
		},
		computed: {
		},
		components: {
			// productitem: ProductItem,
		},
	} );
}
