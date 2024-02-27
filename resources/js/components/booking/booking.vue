<template>
	<div class="dropp-booking">
		<div class="dropp-locations">
			<location
				v-for="location in locations"
				:location="location"
				:key="location.id"
				:consignment_container="consignment_container"
			>
			</location>

			<div class="dropp-locations__add-location" v-show="shipping_items.length">
				<select
					class="dropp-locations__add-dropdown"
					v-model="selected_shipping_item"
					v-if="selected_shipping_item.length > 1"
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
					class="dropp-locations__add-button"
					@click.prevent="add_location"
					v-html="i18n.add_location"
				>
				</button><button
					v-for="special, shipping_method in special_locations"
					:key="shipping_method"
					class="dropp-locations__add-button"
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

		button,
		[type="submit"] {
			background: #0071a1;
			border-radius: 3px;
			outline: none;
			padding: 0.5rem 1rem;
			border: 1px solid #0071a1;
			color: white;
			transition: background-color 0.2s, border-color 0.2s, color 0.1s;

			&:focus {
				box-shadow: 0 0 0 1px #fff, 0 0 0 3px #007cba;
			}
			&:active {
				background-color: #fff;
				color: #000;
			}
			&:hover {
				background-color: #e6fdfe;
				color: #000;
			}
			&:disabled {
				opacity: 0.4;
			}
		}

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
			margin-top: 1rem;
		}
		&__add-button {
			margin-right: 0.5rem;
		}
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

			var res = jQuery.ajax(
				{
					url:      _dropp.dropplocationsurl,
					dataType: "script",
					// success:  dropp_handler.success,
					// error:    dropp_handler.error,
					timeout:  3000,
				}
			);
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
				};
				location.order_item_id = this.selected_shipping_item;
				this.locations.push( location );
			},
		},
		components: {
			location: Location,
		}
	};
</script>
