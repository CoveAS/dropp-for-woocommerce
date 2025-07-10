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
					class="dropp-locations__add-button dropp-button dropp-button--secondary mw160"
					@click.prevent="add_location"
					v-html="i18n.add_location"
				>
				</button><button
					v-for="special, shipping_method in special_locations"
					:key="shipping_method"
					class="dropp-locations__add-button dropp-button dropp-button--secondary mw160"
					@click.prevent="add_special_delivery( special.location )"
					v-html="special.label"
				>
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
		}
	}
	.dropp-booking label:hover {
		color: #1007FA;
	}
	.dropp-booking {
		textarea:focus,
		input[type="email"]:focus,
		input[type="number"]:focus,
		input[type="text"]:focus {
			border-color: #1007FA;
			box-shadow: none;
		}
  }
	.mw160 {
		min-width: 160px;
	}
</style>

<script>
	import Location from './location.vue';
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
		}
	};
</script>
