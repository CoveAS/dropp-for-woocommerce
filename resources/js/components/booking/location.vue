<template>
	<form
		class="dropp-location"
		:class="classes"
		action=""
		@submit.prevent=book
	>
		<header class="dropp-location__header">
			<h2 class="dropp-location__name" v-html="location.name" :title="'[' + location.id + ']'"></h2>
			<p class="dropp-location__address" v-html="location.address"></p>
			<a class="dropp-location__change" v-html="i18n.change_location"></a>
		</header>
		<div class="dropp-location__messages" v-if="response" :class="response_status">
			<h2 class="dropp-location__message" v-html="response.message"></h2>
			<ul class="dropp-location__errors" v-show="response.errors.length">
				<li v-for="error in response.errors" v-html="error"></li>
			</ul>
		</div>
		<div class="dropp-location__booking">
			<div class="dropp-location__booking-errors" v-show="product_errors.length">
				<ul class="dropp-location__errors">
					<li v-for="error in product_errors" v-html="error"></li>
				</ul>
			</div>
			<droppproducts :products="products" :editable="editable"></droppproducts>
			<droppcustomer :customer="customer" :editable="editable"></droppcustomer>
			<div class="dropp-location__actions">
				<button
					v-if="editable"
					class="dropp-location__action dropp-location__action--book"
					:disabled="disabled"
					v-html="book_button_text"
				></button>
				<button
					class="dropp-location__action dropp-location__action--remove"
					v-html="i18n.remove"
					v-if="show_remove_button"
					@click.prevent="remove_location"
				></button>
			</div>
		</div>
	</form>
</template>

<style lang="scss">
	.dropp-location{
		margin-left: -12px;
		margin-right: -12px;
		// background-color: #f1f1f1;
		border-bottom: 1px solid #e5e5e5;
		margin-bottom: 1rem;

		opacity: 1;
		transition: opacity 0.5s;

		&--loading {
			opacity: 0.5;
		}
		.dropp-products,
		.dropp-customer,
		&__actions,
		&__booking-errors,
		&__header {
			padding: 10px;
		}
		&__header {
			position: relative;
			background-color: #e6fdfe;
			color: navy;
			border-top: 2px solid navy;
		}
		&__change {
			position: absolute;
			top: 0.75rem;
			right: 12px;
		}
		&__address {
			margin: 0;
		}

		#poststuff &__name {
			padding: 0;
			color: navy;
			font-size: 1.5rem;
			font-weight: 700;
		}

		#poststuff &__message {
			font-size: 1.25rem;
		}
		&__booking-errors,
		.response-error {
			color: #CC0000;
			h2 { color: #CC0000; }
			background: #FFEEEE;
		}
		.response-success {
			color: #00CC00;
			h2 { color: #008800; }
			background: #AAFFAA;
		}
	}
</style>
<script>
	import DroppCustomer from './dropp-customer.vue';
	import DroppProducts from './dropp-products.vue';
	const new_customer = function() {
		let address = _dropp.customer.address_1;
		let ssn     = _dropp.customer.ssn;
		if ( _dropp.customer.address_2 ) {
			address += ' ' + _dropp.customer.address_2;
		}
		address += ', ' + _dropp.customer.postcode;
		address += ' ' + _dropp.customer.city;
		if ( ! ssn ) {
			ssn = '1234567890';
		}
		return {
			name: _dropp.customer.first_name + ' ' + _dropp.customer.last_name,
			emailAddress: _dropp.customer.email,
			socialSecurityNumber: ssn,
			address: address,
			phoneNumber: _dropp.customer.phone,
		};
	}
	export default {
		data: function() {
			var data =  {
				products: [],
				customer: null,
				i18n: _dropp.i18n,
				loading: false,
				booked: false,
				response: false,
				errors: [],
			};
			if ( this.consignment && this.consignment.customer )
				data.customer = this.consignment.customer;
			else
				data.customer = new_customer();
			return data;
		},
		methods: {
			get_products: function() {
				let products = [];
				for ( var i = 0; i < this.products.length; i++ ) {
					let product = {
						id:       this.products[i].id,
						quantity: this.products[i]._quantity,
					};
					if ( this.products[i].checked ) {
						products.push( product );
					}
				}
				return products;
			},
			remove_location: function() {
				let locations = this.$parent._data.locations;
				for ( let i = 0; i < locations.length; i++ ) {
					let location = locations[i];
					if ( location.id == this.location.id ) {
						locations.splice( i, 1 );break;
					}
				}
			},
			book: function() {
				if ( this.loading || this.booked || ! this.editable ) {
					return;
				}
				this.loading = true;
				this.response = false;
				let params = {
					action: 'dropp_booking',
					location_id: this.location.id,
					order_item_id: this.location.order_item_id,
					products: this.get_products(),
					customer: this.customer,
					dropp_nonce: _dropp.nonce,
				};
				if ( this.consignment ) {
					params.consignment_id = this.consignment.id;
				}
				jQuery.ajax( {
					url: _dropp.ajaxurl,
					method: 'post',
					data: params,
					success: this.success,
					error:   this.error,
				} );
			},
			success: function( data, textStatus, jqXHR ) {
				if ( data.status ) {
					this.response = data;
					if ( consignment_container && data.consignment ) {
						this.$parent._data.consignment_container.consignments.push( data.consignment );
					}
					if ( 'success' === data.status ) {
						this.booked = true;
						jQuery( this.$el ).find( '.dropp-location__booking' ).slideUp();
						window.location.reload();
					}
				}
				let vm = this;
				setTimeout( function() {
					vm.loading = false;
				} );
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				let vm = this;
				this.errors = [
					'Error: Unknown error. Please check your internet connection or contact technical support about this.'
				];
				setTimeout( function() {
					vm.loading = false;
				} );
			},
		},
		computed: {
			product_errors: function() {
				let errors = this.errors;

				let total_weight = 0;
				for ( let i = 0; i < this.products.length; i++ ) {
					let product = this.products[i];
					if ( product.checked ) {
						total_weight += product.weight * product._quantity;
					}
				}
				if ( total_weight > 10 ) {
					errors.push( 'Error: Each consignment must be 10 Kg or less. Please reduce number of items or remove products from booking.' );
				}

				return errors;
			},
			disabled: function() {
				if ( this.product_errors.length - this.errors.length ) {
					return true;
				}
				return false;
			},
			response_status: function() {
				if ( ! this.response ) {
					return '';
				}

				return 'response-' + this.response.status;
			},
			classes: function() {
				let classes = [
					'dropp-location--' + ( this.loading ? 'loading' : 'ready' ),
				];
				return classes.join( ', ' );
			},
			show_remove_button: function() {
				return this.$parent._data.locations && this.$parent._data.locations.length > 1;
			},
			book_button_text: function() {
				console.log( this.i18n );
				console.log( this.consignment );
				return this.consignment ? this.i18n.update_order : this.i18n.submit;
			},
			editable: function() {
				if ( ! this.consignment ) {
					return true;
				}
				return 'initial' === this.consignment.status;
			},
		},
		created: function() {
			for ( let i = 0; i < _dropp.products.length; i++ ) {
				let product = _dropp.products[i];
				product.checked = true;
				product._quantity = product.quantity;
				this.products.push( product );
			}
		},
		props: [
			'consignment',
			'location',
			'consignment_container',
		],
		components: {
			droppcustomer: DroppCustomer,
			droppproducts: DroppProducts,
		}
	};
</script>
