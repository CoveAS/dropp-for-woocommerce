<template>
	<div
		v-if="consignment"
		class="dropp-consignment-card"
		:class="classes"
	>
		<div class="dropp-consignment-card__header">
			<div class="dropp-consignment-card__title-wrapper" :title="consignment.dropp_order_id">
				<div class="dropp-consignment-card__barcode" v-if="consignment.barcode">
					<span v-if="consignment.test">[TEST] </span>{{ consignment.barcode }}
				</div>
				<div class="dropp-consignment-card__products" v-if="consignment.products.length">
					{{ consignment.products.length }} {{ consignment.products.length === 1 ? i18n.product : i18n.products }}
				</div>
			</div>
			<context :consignment="consignment"/>
		</div>
		<div class="dropp-consignment-card__content">
			<div class="dropp-consignment-card__row">
				<div class="dropp-consignment-card__label" v-html="i18n.status + ':'"></div>
				<div class="dropp-consignment-card__value">
					<span v-show="!loading" v-html="status"></span>
					<loader v-show="loading"></loader>
				</div>
			</div>
			<div class="dropp-consignment-card__row">
				<div class="dropp-consignment-card__label" v-html="i18n.created + ':'"></div>
				<div class="dropp-consignment-card__value">
					<time-ago :value="consignment.created_at"/>
				</div>
			</div>
			<div class="dropp-consignment-card__row">
				<div class="dropp-consignment-card__label" v-html="i18n.updated + ':'"></div>
				<div class="dropp-consignment-card__value">
					<time-ago :value="consignment.updated_at"/>
				</div>
			</div>
		</div>
		<div class="dropp-consignment-card__actions">
			<download :consignment="consignment"/>
		</div>
	</div>
</template>


<style scoped lang="scss">
.dropp-consignment-card {
	max-width: 400px;
	margin: 0 auto 16px auto;
	background: #fff;
	border: 1px solid #d1d5db;
	border-radius: 12px;
	overflow: hidden;

	&:last-child {
		margin-bottom: 0;
	}

	&.dropp-consignment--cancelled,
	&.dropp-consignment--error {
		.dropp-consignment-card__value span {
			color: #AC0000;
		}
	}
}

.dropp-consignment-card__header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	padding: 20px 20px 0 20px;
}

.dropp-consignment-card__title-wrapper {
	flex: 1;
}

.dropp-consignment-card__barcode {
	font-size: 18px;
	font-weight: 700;
	color: #1f2937;
	margin-bottom: 4px;
}

.dropp-consignment-card__products {
	font-size: 14px;
	color: #6b7280;
}

.dropp-consignment-card__content {
	padding: 16px 20px;
}

.dropp-consignment-card__row {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 8px 0;
}

.dropp-consignment-card__label {
	font-size: 14px;
	color: #374151;
}

.dropp-consignment-card__value {
	font-size: 14px;
	color: #6b7280;
	text-align: right;
}

.dropp-consignment-card__actions {
	padding: 0 20px 20px 20px;
}
</style>

<script>
import ContextPdf from './context-pdf.vue';
import Loader from '../loader.vue';
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
	mounted() {
		if (this.consignment && this.consignment.new) {
			this.$el.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
		}
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
