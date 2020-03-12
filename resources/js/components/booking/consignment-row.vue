<template>
	<tr
		v-if="consignment"
		class="dropp-consignment"
		:class="classes"
	>
		<td
			class="dropp-consignment__barcode"
			:title="consignment.dropp_order_id"
			v-html="barcode_html"
		> </td>
		<td class="dropp-consignment__status">
			<span v-show="!loading" v-html="status"></span>
			<loader v-show="loading"></loader>
		</td>
		<td class="dropp-consignment__created">{{created_at}}</td>
		<td class="dropp-consignment__updated">{{updated_at}}</td>
		<td
			class="dropp-consignment__actions"
		>
			<ul v-if="consignment.dropp_order_id">
				<li>
					<a
					target="_blank"
					:href="download_url(consignment)"
					v-html="i18n.download"
					></a>
				</li>
				<li>
					<a
					href="#"
					v-html="i18n.check_status"
					@click.prevent="check_status"
					></a>
				</li>
			</ul>
		</td>
		<td
			class="dropp-consignment__actions"
			v-if="consignment.dropp_order_id && consignment.status === 'initial'"
		>
			<ul>
				<li>
					<a
						class="dropp-consignment__action"
						href="#"
						v-html="i18n.view_order"
						@click.prevent="view_order"
					></a>
				</li>
				<li>
					<a
						class="dropp-consignment__action"
						href="#"
						v-html="i18n.cancel_order"
						@click.prevent="cancel_order"
					></a>
				</li>
			</ul>
		</td>
		<td
			class="dropp-consignment__actions"
			v-else
		>
			<ul>
				<li>
					<a
						class="dropp-consignment__action"
						href="#"
						v-html="i18n.view_order"
						@click.prevent="view_order"
					></a>
				</li>
				<li>
					<span
						class="dropp-consignment__action dropp-consignment__action--disabled"
						v-html="i18n.cancel_order">
					></span>
				</li>
			</ul>
		</td>
	</tr>
</template>


<style lang="scss">

	.dropp-consignment {
		opacity: 1;
		transition: opacity 0.2s;
		&--loading {
			opacity: 0.5;
		}
		&:nth-of-type(2n) {
			background: darken(#FFF, 5%);
		}
		&--ready {
		}
		&--cancelled,
		&--error {
			background: #FEE;
			&:nth-of-type(2n) {
				background: #FCC;
			}
		}
		&--initial,
		&--transit,
		&--consignment,
		&--delivered {
			color: navy;
			background: #e6fdfe;
			&:nth-of-type(2n) {
				background: darken(#e6fdfe, 5%);
			}
		}
		&__action--disabled {
			color: #999;
			opacity: 0.5;
			cursor: not-allowed;
		}
	}
</style>

<script>
	import Loader from './loader.vue';
	import time_ago from './time-ago.js';
	export default {
		data: function() {
			return {
				i18n: _dropp.i18n,
				loading: false,
			};
		},
		props: [ 'consignment' ],
		computed: {
			classes: function() {
				let classes = [
					'dropp-consignment',
					'dropp-consignment-' + this.consignment.id,
					'dropp-consignment--' + this.consignment.status,
				];
				if ( this.loading ) {
					classes.push( 'dropp-consignment--loading' );
				}
				return classes.join( ' ' );
			},
			created_at: function() {
				return time_ago( this.consignment.created_at );
			},
			updated_at: function() {
				return time_ago( this.consignment.updated_at );
			},
			status: function() {
				return _dropp.status_list[ this.consignment.status ];
			},
			barcode_html: function() {
				let consignment = this.consignment;
				let html = '';
				html += (consignment.test ? '[TEST] ' : '') + (consignment.barcode ? consignment.barcode : '');
				html += '<br>' + consignment.products.length + '&nbsp;';
				html += ( consignment.products.length === 1 ?  this.i18n.product : this.i18n.products );
				return html;
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
			check_status: function() {
				if (this.loading) {
					return;
				}
				this.loading = true;
				jQuery.ajax( {
					url: _dropp.ajaxurl,
					method: 'get',
					data: {
						action: 'dropp_status_update',
						consignment_id: this.consignment.id,
					},
					success: this.success,
					error:   this.error,
				} );
			},
			view_order: function() {
				this.$parent.show_modal( this.consignment );
			},
			cancel_order: function() {
				if (this.loading) {
					return;
				}
				this.loading = true;
				jQuery.ajax( {
					url: _dropp.ajaxurl,
					method: 'get',
					data: {
						action:         'dropp_cancel',
						consignment_id: this.consignment.id,
						dropp_nonce:    _dropp.nonce,
					},
					success: this.success,
					error:   this.error,
				} );
			},
			success: function( data, textStatus, jqXHR ) {
				if ( data.status ) {
					this.response = data;
					if ( 'success' === data.status ) {
						this.consignment.status     = data.consignment.status;
						this.consignment.updated_at = data.consignment.updated_at;
					}
					else {
						alert( data.message );
					}
				} else {
					console.error( 'Invalid ajax response' );
				}
				let vm = this;
				setTimeout( function() {
					vm.loading = false;
				}, 500 );
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				console.log( jqXHR );
				console.log( textStatus );
				console.log( errorThrown );
				let vm = this;
				setTimeout( function() {
					vm.loading = false;
				}, 500 );
			},
			download_url: function( consignment ) {
				if ( ! consignment.dropp_order_id ) {
					return;
				}
				return _dropp.ajaxurl + '?action=dropp_pdf&consignment_id=' + consignment.id;
			},
		},
		components: {
			loader: Loader,
		},
	};
</script>
