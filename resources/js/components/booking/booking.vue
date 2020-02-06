<template>
	<div class="dropp-booking">
		<div class="dropp-consignments" v-show="display_consignments">
			<h2 v-html="i18n.booked_consignments"></h2>
			<table class="dropp-consignments__table">
				<thead>
					<th v-html="i18n.barcode"></th>
					<th v-html="i18n.products"></th>
					<th v-html="i18n.customer"></th>
					<th v-html="i18n.status"></th>
					<th v-html="i18n.actions"></th>
					<th v-html="i18n.created"></th>
					<!-- <th>Updated</th> Phase2 -->
				</thead>
				<tbody>
					<!-- @TODO: Use actuall consignment data to populate the table -->
					<tr
						class="dropp-consignment"
						v-for="consignment in consignment_container.consignments"
						:key="consignment.id"
						:class="'dropp-consignment-' + consignment.id + ' dropp-consignment--' + consignment.status"
					>
						<td
							class="dropp-consignment__barcode"
							:title="consignment.dropp_order_id"
						>{{consignment.barcode}}</td>
						<td class="dropp-consignment__quantity">{{consignment.products.length}}</td>
						<td class="dropp-consignment__customer" v-html="consignment.customer.name"></td>
						<td class="dropp-consignment__status">{{consignment.status}}</td>
						<td class="dropp-consignment__actions">
							<a
								v-if="download_url(consignment)"
								target="_blank"
								:href="download_url(consignment)"
								v-html="i18n.download"
							></a>
						</td>
						<td class="dropp-consignment__created">{{consignment.created_at}}</td>
						<!-- <td class="dropp-consignment__updated">3 hours ago</td>  Phase2 -->
					</tr>
				</tbody>
				<tfoot>
				</tfoot>
			</table>
		</div>
		<div class="dropp-toggle-locations" v-show="display_consignments">
			<a
				class="dropp-toggle-locations__create"
				@click.prevent="display_locations = !display_locations"
			>Show/hide booking form</a>
		</div>
		<div class="dropp-locations" v-show="display_locations">
			<location
				v-for="location in locations"
				:location="location"
				:key="location.id"
				:consignment_container="consignment_container"
			>
			</location>

			<div class="dropp-locations__add-location" v-show="shipping_items.length">
				<select class="dropp-locations__add-dropdown" v-model="selected_shipping_item">
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
				</button>
			</div>
		</div>
	</div>
</template>


<style lang="scss">
	.dropp-booking a {
		cursor: pointer;
		&:focus,
		&:hover {
			text-decoration: underline;
		}
	}
	.dropp-toggle-locations {
		margin-bottom: 1rem;
	}
	.dropp-consignment {
		&:nth-of-type(2n) {
			background: darken(#FFF, 5%);
		}
		&--ready {
		}
		&--error {
			background: #FEE;
			&:nth-of-type(2n) {
				background: #FCC;
			}
		}
		&--initial {
			color: navy;
			background: #e6fdfe;
			&:nth-of-type(2n) {
				background: darken(#e6fdfe, 5%);
			}
		}
	}
	.dropp-consignments {
		margin-bottom: 1rem;
		th {
			text-align: left;
		}
		th, td {
			padding: 2px 4px;
		}
		&__table {
			width: 100%;
			border-spacing: 0;
		}
	}
	.dropp-locations {
		&__add-location {
			margin-top: 1rem;
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
				shipping_items: _dropp.shipping_items,
				selected_shipping_item: false,
				consignment_container: {
					consignments: _dropp.consignments
				},
				display_locations: true
			};
		},
		created: function() {
			if ( this.consignment_container.consignments.length ) {
				this.display_locations = false;
			}

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
			display_consignments: function() {
				return this.consignment_container.consignments.length;
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
			download_url: function( consignment ) {
				if ( ! consignment.dropp_order_id ) {
					return;
				}
				return _dropp.ajaxurl + '?action=dropp_pdf&consignment_id=' + consignment.id;
			},
		},
		components: {
			location: Location
		}
	};
</script>
