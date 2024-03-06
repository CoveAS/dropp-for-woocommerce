<script>
import ContextPdf from "./context-pdf.vue";
import ContextButton from "../icons/context-button.vue";
import time_ago from "../../time-ago";
import Loader from "../loader.vue";

export default {
	name: "context",
	components: {Loader, ContextButton, ContextPdf},
	data: function () {
		return {
			i18n: _dropp.i18n,
			loading: false,
			show_context: false,
		};
	},
	props: ['consignment'],
	computed: {
		context_class: function () {
			const classes = [];
			if (this.show_context) {
				classes.push('dropp-context-menu--show');
			}
			if (this.show_context) {
				classes.push('dropp-context-menu--loading');
			}
			return classes.join(' ');
		},
		is_initial: function () {
			return this.consignment.dropp_order_id && this.consignment.status === 'initial';
		},
		barcode_html: function () {
			let consignment = this.consignment;
			let html = '';
			html += (consignment.test ? '[TEST] ' : '') + (consignment.barcode ? consignment.barcode : '');
			html += '<br>' + consignment.products.length + '&nbsp;';
			html += (consignment.products.length === 1 ? this.i18n.product : this.i18n.products);
			return html;
		}
	},
	mounted: function () {
		if (!window._dropp_closers) {
			window._dropp_closers = [];
		}
		window._dropp_closers.push(this.close_context);
	},
	methods: {
		close_context: function () {
			this.show_context = false;
		},
		toggle_context: function () {
			if (this.show_context) {
				this.show_context = false;
			} else {
				for (var i = 0; i < window._dropp_closers.length; i++) {
					window._dropp_closers[i]();
				}
				this.show_context = true;
			}
		},
		check_status: function () {
			if (this.loading) {
				return;
			}
			this.show_context = false;
			this.loading = true;
			jQuery.ajax({
				url: _dropp.ajaxurl,
				method: 'get',
				data: {
					action: 'dropp_status_update',
					consignment_id: this.consignment.id,
				},
				success: this.success,
				error: this.error,
			});
		},
		view_order: function () {
			this.show_context = false;
			this.$parent.$parent.show_modal(this.consignment);
		},
		cancel_order: function () {
			if (this.loading) {
				return;
			}
			this.loading = true;
			jQuery.ajax({
				url: _dropp.ajaxurl,
				method: 'get',
				data: {
					action: 'dropp_cancel',
					consignment_id: this.consignment.id,
					dropp_nonce: _dropp.nonce,
				},
				success: (data) => {
					if (data.status && 'success' === data.status) {
							this.consignment.status = 'cancelled';
							this.consignment.updated_at = data.consignment.updated_at;
					} else {
						alert('An error occured when attempting to cancel the order');
					}
					setTimeout( ()=>{ this.loading = false; }, 500);
					this.close_context();
				},
				error: this.error,
			});
		},
		success: function (data, textStatus, jqXHR) {
			if (data.status) {
				this.response = data;
				if ('success' === data.status) {
					this.consignment.status = data.consignment.status;
					this.consignment.updated_at = data.consignment.updated_at;
				} else {
					alert(data.message);
				}
			} else {
				alert('Invalid ajax response');
			}
			setTimeout( ()=>{ this.loading = false; }, 500);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			let vm = this;
			setTimeout(function () {
				vm.loading = false;
			}, 500);
		},
	},
}
</script>

<template>
	<div
		:class="context_class"
		class="dropp-context-menu"
		v-if="consignment.dropp_order_id"
	>
		<div class="dropp-context-menu__main">
			<span
				class="dropp-context-menu__button"
				@click.prevent="toggle_context"
			>
						<context-button/>
					</span>
		</div>
		<div class="dropp-context-menu__dropdown">
			<div class="dropp-context__overlay" v-show="loading">
				<loader/>
			</div>
			<context-pdf
				v-if="show_context"
				:consignment_id="consignment.id"
				class="dropp-context-menu__pdf"
			/>
			<hr>
			<ul class="dropp-context-menu__actions">
				<li>
					<span
						class="dropp-consignment__action"
						v-html="i18n.check_status"
						@click.prevent="check_status"
						@keydown.enter.space.prevent="check_status"
					></span>
				</li>
				<li>
					<span
						class="dropp-consignment__action"
						v-html="i18n.view_order"
						@click.prevent="view_order"
						@keydown.enter.space.prevent="view_order"
					></span>
				</li>
				<li v-if="is_initial">
					<span
						class="
							dropp-consignment__action
							dropp-consignment__action--cancel
						"
						v-html="i18n.cancel_order"
						@click.prevent="cancel_order"
						@keydown.enter.space.prevent="cancel_order"
					></span>
				</li>
			</ul>
		</div>
	</div>
</template>

<style lang="scss">

.dropp-context__overlay {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	display: flex;
	justify-content: center;
	align-items: center;
	background-color: rgba(255, 255, 255, 0.5);
	z-index: 2;
}
.dropp-context-menu {
	.dropp-consignment__action,
	a {
		cursor: default;
		display: block;
		text-decoration: none;
		padding: 8px 16px;
		border-radius: 4px;
		color: #000000;

		&:focus,
		&:hover {
			background-color: #E5E8FF;
			color: #1007FA;
		}
	}

	ul {
		margin: 0;
	}
	li {
		margin-bottom: 3px;
	}
	li:last-child {
		margin-bottom: 0;
	}

	position: relative;

	&__button {
		// Context menu button
		display: block;
		color: #000000;
		padding: 8px 14px;
		line-height: 1;
		border-radius: 4px;
		margin: 0 -14px;
		user-select: none;
		outline: 1px solid transparent;
		transition: outline-color 0.2s, color 0.2s;

		&:hover {
			outline-color: #ceccff;
			color: #1007FA;
		}
	}

	&__dropdown {
		display: none;
		position: absolute;
		top: 110%;
		margin: 0;
		right: -14px;
		width: 220px;
		background: #fff;
		padding: 8px;
		border-radius: 4px;
		z-index: 999;
		border: 1px solid #E1E1E1;
	}

	&--show {
		z-index: 3;

		.dropp-context-menu__dropdown {
			display: block;
		}

		.dropp-context-menu__main {
			border-bottom-left-radius: 0;
			border-bottom-right-radius: 0;
		}
	}
}

.dropp-context-menu .dropp-consignment__action--cancel {
  color: #AC0000;
}
.dropp-context-menu .dropp-consignment__action--cancel:focus,
.dropp-context-menu .dropp-consignment__action--cancel:hover {
  color: #BD0000;
  background-color: #f6d5d5;
}
</style>
