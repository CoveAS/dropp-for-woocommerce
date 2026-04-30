<template>
	<div>
	<div class="dropp-consignments">
		<dropp-error
			v-if="has_errors"
			level="warning"
			:title="i18n.booking_error_title || 'Booking error detected'"
			:dismissible="true"
		>
			{{ i18n.booking_error_message || 'There was an unknown error with one or more bookings. Please review the consignments below or contact support for assistance.' }}
		</dropp-error>
		<div class="dropp-consignments--large" v-show="display_consignments">
			<table class="dropp-consignments__table">
				<thead>
				<tr>
					<th v-html="i18n.barcode"></th>
					<th v-html="i18n.status"></th>
					<th v-html="i18n.created"></th>
					<th v-html="i18n.updated"></th>
					<th v-html="i18n.actions" class="dropp-consignment__actions"></th>
				</tr>
				</thead>
				<tbody>
				<consignmentrow
					v-for="consignment in consignment_container.consignments"
					:consignment="consignment"
					:key="consignment.id"
					:classes="classes(consignment)"
					:status="status(consignment)"
				></consignmentrow>
				</tbody>
				<tfoot>
				</tfoot>
			</table>
		</div>
		<div class="dropp-consignments--small" v-show="display_consignments">
			<consignment-card
				v-for="consignment in visible_consignments"
				:consignment="consignment"
				:key="consignment.id"
				:classes="classes(consignment)"
				:status="status(consignment)"
			/>
			<button
				v-if="has_hidden_consignments"
				class="dropp-consignments__show-more"
				@click="show_all_cards = true"
			>
				{{ i18n.show_all || 'Show all' }} ({{ consignment_container.consignments.length }})
			</button>
		</div>
		<div class="dropp-empty" v-show="! display_consignments">
			<div class="dropp-empty__icon">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
				</svg>
			</div>
			<h3 class="dropp-empty__title">{{ i18n.no_consignments_title || 'No booked consignments yet' }}</h3>
			<p class="dropp-empty__text">{{ i18n.no_consignments_subtitle || 'Please use the booking form to create new consignments.' }}</p>
		</div>
	</div>
	<ordermodal v-if="modal_consignment" :consignment="modal_consignment"></ordermodal>
  </div>
</template>

<style lang="scss">
#woocommerce-order-dropp-consignments .inside {
	z-index: 2;
	padding: 0 !important;
}
.dropp-consignments {
	container-type: inline-size;
	container-name: sidebar;

	// Equal width for status, created, updated, and actions columns
	th:nth-child(n+2),
	td:nth-child(n+2) {
		width: 15%;
	}

	th {
		text-align: left;
		color: #374151;
		font-weight: 600;
		background: #f3f4f6;
		border-top: 1px solid #d1d5db;
		border-bottom: 1px solid #d1d5db;
		height: 40px;
		box-sizing: border-box;
		padding: 0 16px;
	}

	th:first-child {
		border-left: 1px solid #d1d5db;
		padding-left: 24px;
	}

	th:last-child {
		border-right: 1px solid #d1d5db;
		padding-right: 24px;
	}

	td {
		height: 64px;
		box-sizing: border-box;
		padding: 0 16px;

		&:first-of-type {
			padding-left: 24px;
		}

		&:last-of-type {
			padding-right: 24px;
		}
	}

	td:first-child {
		border-left: 1px solid #d1d5db;
	}

	td:last-child {
		border-right: 1px solid #d1d5db;
	}

	tbody td {
		border-bottom: 1px solid #d1d5db;
	}

	thead th:first-child {
		border-top-left-radius: 8px;
	}

	thead th:last-child {
		border-top-right-radius: 8px;
	}

	tbody tr:last-child td:first-child {
		border-bottom-left-radius: 8px;
	}

	tbody tr:last-child td:last-child {
		border-bottom-right-radius: 8px;
	}


	&__table {
		width: 100%;
		border-spacing: 0;
		border-radius: 8px;
	}

}


.dropp-consignments--large {
  margin: 24px;
}

.dropp-consignments > .dropp-product-error {
  margin: 16px;
  max-width: 400px;
}

@container (max-width: 599px) {
	.dropp-consignments--large {
		display: none;
	}
	.dropp-consignments--small {
		padding: 16px;
	}
	.dropp-consignments > .dropp-product-error {
		margin: 16px 16px 0 16px;
		max-width: none;
	}
}

@container (min-width: 600px) {
	.dropp-consignments--small {
		display: none;
	}
	.dropp-consignments > .dropp-product-error {
		margin: 24px 24px 0 24px;
		max-width: none;
	}
}

@keyframes fadeInAndHighlight {
	0% {
		opacity: 0;
	}
	10% {
		opacity: 0;
	}
	50% {
		opacity: 1;
		background-color: #e2f8e2;
	}
	85% {
		background-color: #e2f8e2;
	}
	100% {
	}
}



.dropp-consignments__show-more {
	display: block;
	width: 100%;
	max-width: 400px;
	margin: 16px auto 0 auto;
	padding: 12px 20px;
	background: #f3f4f6;
	border: 1px solid #d1d5db;
	border-radius: 8px;
	color: #374151;
	font-size: 14px;
	font-weight: 500;
	cursor: pointer;
	transition: background-color 0.2s, border-color 0.2s;

	&:hover {
		background: #e5e7eb;
		border-color: #9ca3af;
	}

	&:focus {
		outline: 2px solid #1e3a8a;
		outline-offset: 2px;
	}
}

.dropp-consignments--small .dropp-consignment--new {
	animation: fadeInAndHighlight 5s ease;
}

@keyframes fadeInAndHighlightLarge {
  0% {
		color: transparent;
  }
  10% {
		color: transparent;
  }
  50% {
		background-color: #e2f8e2;
		color: inherit;
  }
  85% {
		background-color: #e2f8e2;
  }
  100% {
  }
}

.dropp-consignments--large .dropp-consignment--new {
  animation: fadeInAndHighlightLarge 4s ease;
}
.dropp-consignments--large .dropp-consignment--new .dropp-consignment-download-button {
  animation: fadeInAndHighlight 4s ease;
}
</style>
<script>

import ConsignmentRow from './consignment-row.vue';
import OrderModal from "./order-modal.vue";
import ConsignmentCard from "./consignment-card.vue";
import DroppError from "../dropp-error.vue";

export default {
	data() {
		return {
			i18n: _dropp.i18n,
			consignment_container: {
				consignments: _dropp.consignments
			},
			modal_consignment: null,
			show_all_cards: false,
			initial_cards_limit: 3,
		};
	},
	computed: {
		display_consignments: function () {
			return this.consignment_container.consignments.length;
		},
		has_errors: function () {
			return this.consignment_container.consignments.some(c => c.status === 'error');
		},
		visible_consignments: function () {
			if (this.show_all_cards) {
				return this.consignment_container.consignments;
			}
			return this.consignment_container.consignments.slice(0, this.initial_cards_limit);
		},
		has_hidden_consignments: function () {
			return !this.show_all_cards && this.consignment_container.consignments.length > this.initial_cards_limit;
		},
	},
	methods: {
		show_modal: function (consignment) {
			this.modal_consignment = consignment;
		},
		classes: function (consignment) {
			let classes = [
				'dropp-consignment',
				'dropp-consignment-' + consignment.id,
				'dropp-consignment--' + consignment.status,
			];
			if (consignment.new) {
			  classes.push('dropp-consignment--new')
			}
			if (this.loading) {
				classes.push('dropp-consignment--loading');
			}
			return classes.join(' ');
		},
		status: function (consignment) {
			return _dropp.status_list[consignment.status];
		},
	},
	components: {
		ConsignmentCard,
		ordermodal: OrderModal,
		consignmentrow: ConsignmentRow,
		DroppError,
	}
};
</script>
