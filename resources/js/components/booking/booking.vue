<template>
	<div class="dropp-booking">
		<div class="dropp-consignments" v-show="display_consignments">
			<h2>♻️bookedConsignments</h2>
			<table class="dropp-consignments__table">
				<thead>
					<th>Consignment</th>
					<th>Products</th>
					<th>Customer</th>
					<!-- <th>Status</th> Phase2 -->
					<th>Created</th>
					<!-- <th>Updated</th> Phase2 -->
				</thead>
				<tbody>
					<!-- @TODO: Use actuall consignment data to populate the table -->
					<tr>
						<td title="de3128aa-acf6-42c8-a5f3-3501eb23133e">ORDER-AB123</td>
						<td>3</td>
						<td>Egill Skallagrímsson</td>
						<!-- <td>initial</td>  Phase2 -->
						<td>1 day ago</td>
						<!-- <td>3 hours ago</td>  Phase2 -->
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
			<button
				class="dropp-locations__add-location"
				@click.prevent="add_location"
				v-html="i18n.addLocation"
			>
			</button>
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
	.dropp-consignments {
		margin-bottom: 1rem;
		th {
			text-align: left;
		}
		&__table {
			width: 100%;
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
				consignment_container: {
					consignments: [1]
				},
				display_locations: true
			};
		},
		created: function() {
			if ( this.consignment_container.consignments.length ) {
				this.display_locations = false;
			}
		},
		computed: {
			display_consignments: function() {
				return this.consignment_container.consignments.length;
			}
		},
		methods: {
			add_location: function() {
				//@TODO: Location selector.

				// Empty locations should not be added.
				this.locations.push(
					{
						id: false
					}
				);
			}
		},
		components: {
			location: Location
		}
	};
</script>
