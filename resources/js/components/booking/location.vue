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
			<div class="dropp-location__products">
				<h3 v-html="i18n.products"></h3>
				<div class="dropp-location__product"
					v-for="product in products"
					:key="product.sku"
				>
					<label>
						<input type="checkbox" v-model="product.checked">
						<input
							class="dropp-location__quantity"
							type="number"
							step="1"
							min="0"
							:max="product.quantity"
							v-model.number="product._quantity"
						>
						<span v-html="'&times; ' + product.name"></span>
						<span v-html="product.weight + ' Kg'"></span>
						<span v-html="product.weight * product._quantity + ' Kg'"></span>
					</label>
				</div>
			</div>
			<droppcustomer :customer="customer"></droppcustomer>
			<div class="dropp-location__actions">
				<input
					class="dropp-location__action dropp-location__action--book"
					type="submit"
					:disabled="disabled"
					:value="i18n.submit"
				>
				<button
					class="dropp-location__action dropp-location__action--remove"
					v-html="i18n.remove"
					v-if="show_remove_button"
					@click.prevent="remove_location"
				>
				</button>
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

		.dropp-customer,
		&__actions,
		&__products,
		&__booking-errors,
		&__header {
			padding: 10px;
		}
		&__header {
			position: relative;
			background-color: #e6fdfe;
			color: navy;
		}
		&__change {
			position: absolute;
			top: 0.75rem;
			right: 12px;
		}
		&__address {
			margin: 0;
		}
		&__quantity {
			width: 5rem;
			text-align: right;
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
	export default {
		data: function() {
			let address = _dropp.customer.address_1;
			if ( _dropp.customer.address_2 ) {
				address += ' ' + _dropp.customer.address_2;
			}
			address += ', ' + _dropp.customer.postcode;
			address += ' ' + _dropp.customer.city;

			return {
				products: [],
				customer: {
					name: _dropp.customer.first_name + ' ' + _dropp.customer.last_name,
					emailAddress: _dropp.customer.email,
					socialSecurityNumber: '',
					address: address,
					phoneNumber: _dropp.customer.phone,
				},
				i18n: _dropp.i18n,
				loading: false,
				booked: false,
				response: false,
			};
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
				if (this.loading || this.booked) {
					return;
				}
				this.loading = true;
				this.response = false;
				jQuery.ajax( {
					url: _dropp.ajaxurl,
					method: 'post',
					data: {
						action: 'dropp_booking',
						location_id: this.location.id,
						order_item_id: this.location.order_item_id,
						products: this.get_products(),
						customer: this.customer,
					},
					success: this.success,
					error:   this.error,
				} );
			},
			success: function( data, textStatus, jqXHR ) {
				console.log( this );
				if ( data.status ) {
					this.response = data;
					this.$parent._data.consignment_container.consignments.push( data.consignment );
					if ( 'success' === data.status ) {
						this.booked = true;
						jQuery( this.$el ).find( '.dropp-location__booking' ).slideUp();
					}
				}
				let vm = this;
				setTimeout( function() {
					vm.loading = false;
				} );
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				console.log( jqXHR );
				console.log( textStatus );
				console.log( errorThrown );
				let vm = this;
				setTimeout( function() {
					vm.loading = false;
				} );
			},
		},
		computed: {
			product_errors: function() {
				let errors = [];

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
				if ( this.product_errors.length ) {
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
				return this.$parent._data.locations.length > 1;
			}
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
			'location',
			'consignment_container'
		],
		components: {
			droppcustomer: DroppCustomer
		}
	};
</script>
