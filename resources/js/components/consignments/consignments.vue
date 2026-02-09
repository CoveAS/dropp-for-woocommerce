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
				v-for="consignment in consignment_container.consignments"
				:consignment="consignment"
				:key="consignment.id"
				:classes="classes(consignment)"
				:status="status(consignment)"
			/>
		</div>
		<p v-show="! display_consignments" v-html="i18n.no_consignments">
		</p>
	</div>
	<ordermodal v-if="modal_consignment" :consignment="modal_consignment"></ordermodal>
  </div>
</template>

<style lang="scss">
#woocommerce-order-dropp-consignments .inside {
	z-index: 2;
}
.dropp-consignments > p {
	margin: 0;
}
.dropp-consignments {
	container-type: inline-size;
	container-name: sidebar;

	th {
		text-align: left;
		color: #374151;
		font-weight: 600;
		background: #f3f4f6;
		border-top: 1px solid #d1d5db;
		border-bottom: 1px solid #d1d5db;
	}

	th:first-child {
		border-left: 1px solid #d1d5db;
	}

	th:last-child {
		border-right: 1px solid #d1d5db;
	}

	th, td {
		padding: 16px;

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

#woocommerce-order-dropp-consignments h2 {
}

.dropp-consignments--large {
  margin: 24px 12px 12px 12px;
}

.dropp-consignments > .dropp-product-error {
  margin: 12px 12px 0 12px;
}

@container (max-width: 599px) {
	.dropp-consignments--large {
		display: none;
	}
}

@container (min-width: 600px) {
	.dropp-consignments--small {
		display: none;
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
		};
	},
	mounted() {
		// TODO: Remove this mock data after testing
		if (this.consignment_container.consignments.length > 0) {
			const original = this.consignment_container.consignments[0];
			for (let i = 0; i < 4; i++) {
				this.consignment_container.consignments.push({
					...original,
					id: original.id + '_mock_' + i,
					barcode: 'DR' + Math.random().toString(36).substring(2, 10).toUpperCase(),
					status: i === 2 ? 'error' : 'initial',
				});
			}
		}
	},
	computed: {
		display_consignments: function () {
			return this.consignment_container.consignments.length;
		},
		has_errors: function () {
			return this.consignment_container.consignments.some(c => c.status === 'error');
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
