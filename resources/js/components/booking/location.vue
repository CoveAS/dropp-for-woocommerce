<template>
	<form
		class="dropp-location"
		:class="classes"
		action=""
		@submit.prevent=book
	>
		<header class="dropp-location__header">
			<div
				v-if="location.address"
				class="dropp-location__pre-title"
			>
				<span class="dropp-location__pick-up-point" v-html="i18n.pick_up_point"></span>
				<span
					@click.prevent="changeLocation"
					@keydown.enter.space.prevent="changeLocation"
					tabindex="0"
					class="dropp-location__change dropp-location__change--large" v-html="i18n.change_location"
				></span>
			</div>
			<h2 class="dropp-location__name" v-html="location_name" :title="'[' + location.id + ']'"></h2>
			<span v-if="location.address" class="dropp-location__address" v-html="location.address"></span>
			<div
				v-if="location.address"
				@click.prevent="changeLocation"
				@keydown.enter.space.prevent="changeLocation"
				class="dropp-location__change dropp-location__change--small" v-html="i18n.change_location"
			></div>
		</header>
		<div class="dropp-location__booking">
			<droppproducts :key="location.id" :location="location" v-model="products" :editable="editable"/>
			<div class="dropp-grid">
				<droppcustomer :customer="customer" :editable="editable"></droppcustomer>
				<div class="dropp-delivery-instructions">
					<div class="dropp-delivery-instructions__notes" v-if="customer_note">
						<h3 class="dropp-delivery-instructions__title" v-html="i18n.customer_note"></h3>
						<blockquote class="dropp-delivery-instructions__text" v-html="customer_note"></blockquote>
						<button
							v-if="editable"
							type="button"
							v-html="i18n.copy_to_delivery"
							@click.prevent="copy_customer_note"
						></button>
					</div>
					<div class="dropp-delivery-instructions__field">
						<h3 class="dropp-delivery-instructions__title" v-html="i18n.delivery_instructions"></h3>
						<textarea
							v-if="editable"
							class="dropp-delivery-instructions__input"
							v-model="delivery_instructions"
						></textarea>
						<blockquote
							v-else
							class="dropp-delivery-instructions__text"
							v-html="delivery_instructions"
						></blockquote>
						<div class="dropp-day-delivery">
							<label v-if="day_delivery_available">
								<input
									type="checkbox"
									v-model="day_delivery"
								>
								<span v-html="day_delivery_label"></span>
							</label>
						</div>
					</div>
				</div>
			</div>
			<div class="dropp-location__actions">
				<button
					v-if="editable"
					type="button"
					class="dropp-location__action dropp-location__action--book dropp-button"
					:disabled="disabled"
					v-html="book_button_text"
					@click.prevent="book"
				></button>
				<button
					type="button"
					class="dropp-location__action dropp-location__action--remove dropp-button"
					v-html="i18n.remove"
					v-if="show_remove_button"
					@click.prevent="remove_location"
				></button>
			</div>
			<div class="dropp-location__booking-error" v-if="errorMessage">
				<dropp-error
					level="error"
					:title="i18n.error + ' ' + errorStatusCode"
				>{{errorMessage}}</dropp-error>
			</div>
		</div>
	</form>
</template>

<style lang="scss">

.dropp-grid {
	padding: 0 16px;
}

@container (min-width: 600px) {
	.dropp-grid {
		display: grid;
		grid-template-columns: minmax(calc(50% - 12px), 600px) minmax(calc(50% - 12px), 600px);
		gap: 24px;
		padding: 0 24px;
	}
}

.dropp-location {
	margin-left: -12px;
	margin-right: -12px;
	padding-bottom: 1rem;
	opacity: 1;
	transition: opacity 0.5s;
	color: #1C1B1B;
	font-size: 14px;
	border-bottom: 1px solid #e5e5e5;

	&--loading {
		opacity: 0.5;
	}

	.dropp-day-delivery {
		margin-top: 0.5rem;
	}

	.dropp-delivery-instructions {
		&__field {
			flex: 0 1 20rem;
			min-width: 15rem;
		}

		&__input {
			resize-x: none;
			min-height: 100px;
			width: 100%;
			border: 1px solid #999999;
		}

		&__notes {
		}

		blockquote {
			margin: 0 0 0.5rem 0;
			background-color: #eee;
			min-height: 3rem;
		}

		&__text {
			border: 1px solid #999999;
			padding: 0.5rem;
			margin-bottom: 1rem;
		}
	}

	&__actions,
	&__booking-error,
	&__header {
		padding: 0 16px;
	@container (min-width:600px) {
		padding: 0 24px;
	}
	}

	&__header {
		position: relative;
		margin: 12px 0 32px;
	@container (min-width:600px) {
		margin: 24px 0 32px;
	}
	}

	&__pre-title {
		display: flex;
		align-items: baseline;
		@container (min-width:900px) {
			max-width: 588px;
		}
	}

	h3,
	&__pick-up-point {
		font-weight: 700;
		font-size: 14px;
	@container (min-width:600px) {
		font-size: 16px;
	}
	}

	&__change {
		font-weight: 600;
		margin-left: auto;
		text-decoration: underline;
		cursor: pointer;
		color: #1007FA;
		font-size: 13px;

		&:focus,
		&:hover {
			text-decoration: none;
		}

		&--small {
			margin-top: 16px;
		}

		&--large {
			display: none;
		}

	@container (min-width:500px) {
		&--small {
			display: none;
		}

		&--large {
			display: block;
		}
	}
	}

	&__address {
		margin: 0;
	}

	#poststuff &__name {
		padding: 0;
		font-size: 24px;
		font-weight: 500;
	}

	#poststuff &__message {
		font-size: 1.25rem;
	}

	&__booking-errors,
	.response-error {
		color: #CC0000;

		h2 {
			color: #CC0000;
		}

		background: #FFEEEE;
	}

	.response-success {
		color: #00CC00;

		h2 {
			color: #008800;
		}

		background: #AAFFAA;
	}
}
</style>
<script>
import DroppCustomer from './dropp-customer.vue';
import DroppProducts from './dropp-products.vue';
import DroppError from "../dropp-error.vue";

const new_customer = function () {
	let address = _dropp.customer.address_1;
	let ssn = _dropp.customer.ssn;
	if (_dropp.customer.address_2) {
		address += ' ' + _dropp.customer.address_2;
	}
	address += ', ' + _dropp.customer.postcode;
	address += ' ' + _dropp.customer.city;
	if (!ssn) {
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
	data: function () {
		var data = {
			products: [],
			customer: null,
			delivery_instructions: _dropp.delivery_instructions,
			customer_note: _dropp.customer_note,
			i18n: _dropp.i18n,
			loading: false,
			booked: false,
			response: false,
			day_delivery: false,
			errorStatusCode: 0,
			errorMessage: '',
		};
		if (this.consignment && this.consignment.customer)
			data.customer = this.consignment.customer;
		else
			data.customer = new_customer();
		return data;
	},
	watch: {
		day_delivery(newVal, oldVal) {
			if (newVal) {
				this.location.type = 'dropp_daytime';
			} else {
				this.location.type = 'dropp_home';
			}
		}
	},
	methods: {
		get_products: function () {
			let products = [];
			for (var i = 0; i < this.products.length; i++) {
				let product = {
					id: this.products[i].id,
					quantity: this.products[i]._quantity,
				};
				if (this.products[i].checked) {
					products.push(product);
				}
			}
			return products;
		},
		remove_location() {
		  this.$emit('remove', this.location)
		},
		changeLocation() {
			chooseDroppLocation()
				.then(
					location => {
						//this.order_item_id = location.order_item_id;
						this.location = location
					}
				)
				.catch( function( error ) {
					// Something went wrong.
					// @TODO.
					console.log( error );
				});
		},
		book: function () {
			if (this.loading || this.booked || !this.editable) {
				return;
			}
			this.errorMessage = '';
			this.loading = true;
			this.response = false;
			let params = {
				action: 'dropp_booking',
				location_id: this.location.id,
				order_item_id: this.location.order_item_id,
				day_delivery: this.day_delivery,
				products: this.get_products(),
				comment: this.delivery_instructions,
				customer: this.customer,
				dropp_nonce: _dropp.nonce,
			};
			if (this.consignment) {
				params.consignment_id = this.consignment.id;
			}

			jQuery.ajax({
				url: _dropp.ajaxurl,
				method: 'post',
				data: params,
				timeout: 10000,
				success: this.success,
				error: this.error,
			});
		},
		success: function (data, textStatus, jqXHR) {
			if (data.status) {
				this.response = data;
				if ('success' === data.status) {
					this.booked = true;
					this.$emit('booked', data.consignment)
				} else {
					this.errorStatusCode = 500;
					this.errorMessage = data.message;
				}
			}
			setTimeout(() => { this.loading = false}, 10);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			this.errorStatusCode = jqXHR.status;
			this.errorMessage = this.i18n.booking_error_general;
			setTimeout(() => { this.loading = false}, 10);
		},
		copy_customer_note: function () {
			this.delivery_instructions = this.customer_note;
		}
	},
	computed: {
		day_delivery_available() {
			return this.location.type === 'dropp_daytime' || this.location.type === 'dropp_home';
		},
		day_delivery_label() {
			return this.i18n.day_delivery.charAt(0).toUpperCase() + this.i18n.day_delivery.slice(1);
		},
		location_name() {
			return this.location.name + (this.location.type === 'dropp_daytime' ? ' (' + this.i18n.day_delivery + ')' : '');
		},
		disabled: function () {
			return this.booked || ! this.editable;
		},
		response_status: function () {
			if (!this.response) {
				return '';
			}

			return 'response-' + this.response.status;
		},
		classes: function () {
			let classes = [
				'dropp-location--' + (this.loading ? 'loading' : 'ready'),
			];
			return classes.join(', ');
		},
		show_remove_button: function () {
			return this.$parent._data.locations && this.$parent._data.locations.length > 1;
		},
		book_button_text: function () {
			let testing = _dropp.testing ? ' (' + this.i18n.test + ')' : '';
			return (this.consignment ? this.i18n.update_order : this.i18n.submit) + testing;
		},
		editable: function () {
			if (!this.consignment) {
				return true;
			}
			return 'initial' === this.consignment.status;
		},
	},
	created: function () {
		if (this.location.type === 'dropp_daytime') {
			this.day_delivery = true;
		}
		let products = _dropp.products;
		if (this.consignment && this.consignment.products) {
			products = this.consignment.products;
		}
		for (let i = 0; i < products.length; i++) {
			let product = products[i];
			product.checked = product.quantity > 0;
			product._quantity = product.quantity;
			if (this.consignment) {
				const orderProduct = _.find(
					_dropp.products,
			(orderProduct) => orderProduct.id === product.id
				);
				if (orderProduct) {
					product.quantity = orderProduct.quantity;
				}
			}

			this.products.push(product);
		}

		if (this.consignment) {
			this.delivery_instructions = this.consignment.comment;
		}
	},
	props: [
		'consignment',
		'location',
		'consignment_container',
	],
	components: {
	DroppError,
		droppcustomer: DroppCustomer,
		droppproducts: DroppProducts,
	}
};
</script>
