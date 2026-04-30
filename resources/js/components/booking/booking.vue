<template>
	<div class="dropp-booking">
		<div class="dropp-locations">
			<location
				v-for="location in locations"
				:location="location"
				:key="location.id"
				:consignment_container="consignment_container"
				@booked="processBooked($event, location)"
				@remove="removeLocation"
			>
			</location>

			<div class="dropp-empty" v-show="!locations.length && shipping_items.length">
				<div class="dropp-empty__icon">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
					</svg>
				</div>
				<h3 class="dropp-empty__title">{{ i18n.no_booking_title || 'No active booking' }}</h3>
				<p class="dropp-empty__text">{{ i18n.no_booking_subtitle || 'Start by filling out the booking form to create a new consignment.' }}</p>
			</div>

			<div class="dropp-locations__add-location" v-show="shipping_items.length">
				<select
					class="dropp-locations__add-dropdown"
					v-model="selected_shipping_item"
					v-if="shipping_items.length > 1"
				>
					<option
						v-for="shipping_item in shipping_items"
						:key="shipping_item.id"
						:value="shipping_item.id"
						v-html="shipping_item.label"
					>
					</option>
				</select>
				<button
					class="dropp-locations__add-button dropp-button dropp-button--secondary"
					@click.prevent="add_location"
				>
					<box-icon />
					<span v-html="i18n.add_location"></span>
				</button><button
					v-for="special, shipping_method in special_locations"
					:key="shipping_method"
					class="dropp-locations__add-button dropp-button dropp-button--secondary"
					@click.prevent="add_special_delivery( special.location )"
				>
					<component :is="getSpecialIcon(shipping_method)" />
					<span v-html="special.label"></span>
				</button>
			</div>
		</div>
	</div>
</template>


<style lang="scss">
	.dropp-booking {
		container-type: inline-size;
		a {
			cursor: pointer;
			&:focus,
			&:hover {
				text-decoration: underline;
			}
		}
	}
	.dropp-locations {
		&__add-location {
			margin: 16px 2px 0 2px;
			@container (min-width: 600px) {
				margin: 16px 10px 0 10px;
			}
		}
		&__add-button {
			gap: 8px;
			@container (max-width: 375px) {
				width: 100%;
				justify-content: center;
				margin-right: 0;
			}
		}
	}
	.dropp-booking {
		textarea,
		input[type="email"],
		input[type="number"],
		input[type="text"] {
			border-color: #d1d5db;
			&:hover {
				border-color: #d1d5db;
				box-shadow: none;
				outline: none;
			}
			&:focus {
				border-color: #d1d5db;
				outline: none;
				box-shadow: 0 0 0 2px #e5e7eb;
			}
		}
	}
	.mw160 {
		min-width: 160px;
	}
</style>

<script>
	import Location from './location.vue';
	import BoxIcon from '../icons/box-icon.vue';
	import HomeIcon from '../icons/home-icon.vue';
	import TruckIcon from '../icons/truck-icon.vue';
	export default {
		data: function() {
			return {
				i18n: _dropp.i18n,
				locations: _dropp.locations,
				special_locations: _dropp.special_locations,
				shipping_items: _dropp.shipping_items,
				selected_shipping_item: false,
				consignment_container: {
					consignments: _dropp.consignments
				},
			};
		},
		created: function() {
			if ( this.shipping_items.length ) {
				this.selected_shipping_item = this.shipping_items[0].id;
			}
			// Load the chooseDroppLocation() script
			jQuery.ajax({
				 url:      _dropp.dropplocationsurl,
				 dataType: "script",
				 timeout:  3000,
			});
		},
		computed: {
			toggle_classes: function() {
				let classes = [];
				return classes.join(' ');
			}
		},
		methods: {
			getSpecialIcon: function(shippingMethod) {
				if (shippingMethod.toLowerCase().includes('home')) {
					return 'home-icon';
				}
				return 'truck-icon';
			},
			add_location: function() {
				//@TODO: Location selector.
				let vm = this;
				chooseDroppLocation()
					.then( function( location ) {
						location.order_item_id = vm.selected_shipping_item;
						location.weight_limit = 10;
						// A location was picked. Save it.
						vm.locations.push( location );
					} )
					.catch( function( error ) {
						// Something went wrong.
						// @TODO.
						console.log( error );
					});
			},
			add_special_delivery: function( raw_location ) {
				let location = {
					id: raw_location.id,
					name: raw_location.name,
					barcode: raw_location.barcode,
					weight_limit: raw_location.weight_limit,
					order_item_id: this.selected_shipping_item,
				};
				this.locations.push( location );
			},
			processBooked(consignment, location) {
				jQuery( '#woocommerce-order-dropp-consignments.closed .handlediv' ).click();
				consignment.new = true;
				_dropp.consignments.push(consignment);
				this.removeLocation(location);
				setTimeout(()=>{consignment.new = false;}, 4000);
			},
			removeLocation(location) {
				if (this.locations.indexOf(location) !== -1) {
					this.locations.splice(this.locations.indexOf(location), 1);
				}
			}

		},
		components: {
			location: Location,
			'box-icon': BoxIcon,
			'home-icon': HomeIcon,
			'truck-icon': TruckIcon,
		}
	};
</script>
