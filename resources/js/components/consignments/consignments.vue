<template>
	<div class="dropp-consignments">
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
		<ordermodal v-if="modal_consignment" :consignment="modal_consignment"></ordermodal>
	</div>
</template>

<style lang="scss">
#woocommerce-order-dropp-consignments .inside {
	z-index: 2;
}
.dropp-consignments {
	margin: 24px 12px 12px 12px;
	container-type: inline-size;
	container-name: sidebar;

	th {
		text-align: left;
		color: #FFFFFF;
		-webkit-font-smoothing: antialiased;
		background: #000078;
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
		border-left: 1px solid #C4C4DF;
	}

	td:last-child {
		border-right: 1px solid #C4C4DF;
	}

	tbody tr:last-child td {
		border-bottom: 1px solid #C4C4DF;
	}

	thead th:first-child {
		border-top-left-radius: 4px;
	}

	thead th:last-child {
		border-top-right-radius: 4px;
	}

	tbody tr:last-child td:first-child {
		border-bottom-left-radius: 4px;
	}

	tbody tr:last-child td:last-child {
		border-bottom-right-radius: 4px;
	}


	&__table {
		width: 100%;
		border-spacing: 0;
		border-radius: 4px;
	}

}

#woocommerce-order-dropp-consignments h2 {
}

@container (max-width: 599px) {
	.dropp-consignments--large {
		display: none;
	}

	.dropp-consignments--small {
		display: block;
	}
}

@container (min-width: 600px) {
	.dropp-consignments--large {
		display: block;
	}

	.dropp-consignments--small {
		display: none;
	}
}

</style>
<script>

import ConsignmentRow from './consignment-row.vue';
import OrderModal from "./order-modal.vue";
import ConsignmentCard from "./consignment-card.vue";

export default {
	data: function () {
		return {
			i18n: _dropp.i18n,
			consignment_container: {
				consignments: _dropp.consignments
			},
			modal_consignment: null,
		};
	},
	created: function () {
	},
	computed: {
		display_consignments: function () {
			return this.consignment_container.consignments.length;
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
	}
};
</script>
