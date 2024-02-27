<template>
	<tr
		v-if="consignment"
		class="dropp-consignment"
		:class="classes"
	>
		<td
			class="dropp-consignment__barcode"
			:title="consignment.dropp_order_id"
		>
			<div v-if="consignment.barcode">
				<span v-if="consignment.test"> [TEST] </span>
				{{consignment.barcode}}
			</div>
			<div v-if="consignment.products.length">
				{{ consignment.products.length }}
				{{ consignment.products.length === 1 ? i18n.product : i18n.products }}
			</div>
		</td>
		<td class="dropp-consignment__status">
			<span v-show="!loading" v-html="status"></span>
			<loader v-show="loading"></loader>
		</td>
		<td class="dropp-consignment__created">
			<time-ago :value="consignment.created_at"/>
		</td>
		<td class="dropp-consignment__updated">
			<time-ago :value="consignment.updated_at"/>
		</td>
		<td
			class="dropp-consignment__actions"
		>
			<div class="dropp-actions">
				<download :consignment="consignment"/>
				<context :consignment="consignment"/>
			</div>
		</td>
	</tr>
</template>

<style scoped lang="scss">

.dropp-actions {
	display: flex;
	gap: 22px;
}
.dropp-consignment {
	opacity: 1;
	transition: opacity 0.2s;

	&--loading {
		opacity: 0.5;
	}

	background: #FFFFFF;

	&:nth-of-type(2n) {
		background: #F5F7FE;
	}

	&--cancelled,
	&--error {
		.dropp-consignment__status {
			color: #AC0000;
		}
	}

	&__actions {
		width: 12rem;
	}

	& &__action--cancel {
		color: #900;
	}

	& &__action--disabled {
		color: #999;
		opacity: 0.5;
		cursor: not-allowed;
	}
}

</style>

<script>
import ContextPdf from './context-pdf.vue';
import Loader from '../loader.vue';
import time_ago from '../../time-ago.js';
import ContextButton from "../icons/context-button.vue";
import Context from "./context.vue";
import Download from "./download.vue";
import TimeAgo from "../time-ago.vue";

export default {
	data: function () {
		return {
			i18n: _dropp.i18n,
			loading: false,
			show_context: false,
		};
	},
	props: ['consignment', 'classes', 'status'],
	components: {
	TimeAgo,
		Download,
		Context,
		ContextButton,
		Loader,
		ContextPdf,
	},
};
</script>
