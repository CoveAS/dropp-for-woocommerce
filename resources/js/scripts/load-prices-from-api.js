jQuery( ( $ ) => {
	const loaded_prices = function( price_inputs ) {
		return function( response ) {
			for (let i = 0; i < response.data.length; i++) {
				let price = response.data[i].price;
				$(price_inputs[i]).val(price);
			}
		};
		// @TODO: remove blocker
	};
	const loading_error = function ( error ) {
		alert('An unknown error occured when loading prices. Please report this as an issue on the WordPress support forum for the dropp-for-woocommerce plugin.')
	}
	const init_load_prices_from_api_button = function() {
		let elem = $('[name$="_load_prices_from_api"]');
		if ( ! elem.length ) {
			return;
		}
		let table = elem.closest('table');
		let price_inputs = table.find('[name*="_cost"]');
		let instance_id = $('[name="instance_id"]').val();
		let loading = false;

		elem.on(
			'click',
			() => {
				loading = true;
				// @TODO: Add blocker
				jQuery.ajax( {
					url: _dropp.ajaxurl,
					method: 'get',
					data: {
						action: 'dropp_get_instance_prices',
						instance_id: instance_id,
					},
					success: loaded_prices(price_inputs),
					error: loading_error,
				} );
			}
		)
	}
	$( document.body ).on( 'wc_backbone_modal_loaded', init_load_prices_from_api_button);
} );
