<template>
	<div
		v-if="consignment"
		class="dropp-consignment"
		:class="classes"
	>
	<div class="dropp-consignment__inner">
		<div
			class="dropp-consignment__barcode-wrapper"
			:title="consignment.dropp_order_id"
		>
			<div class="dropp-consignment__barcode" v-if="consignment.barcode">
				<span v-if="consignment.test"> [TEST] </span>
				{{ consignment.barcode }}
			</div>
			<context :consignment="consignment"/>
		</div>
		<div class="dropp-consignment__card-content">
			<div class="dropp-consignment__products">
				<div class="dropp-consignment__card-label" v-html="i18n.products"></div>
				<div v-if="consignment.products.length">
					{{ consignment.products.length }}
				</div>
			</div>
			<div class="dropp-consignment__status">
				<div class="dropp-consignment__card-label" v-html="i18n.status"></div>
				<span v-show="!loading" v-html="status"></span>
				<loader v-show="loading"></loader>
			</div>
			<div class="dropp-consignment__created">

				<div class="dropp-consignment__card-label" v-html="i18n.created"></div>
				<time-ago :value="consignment.created_at"/>
			</div>
			<div class="dropp-consignment__updated">

				<div class="dropp-consignment__card-label" v-html="i18n.updated"></div>
				<time-ago :value="consignment.updated_at"/>
			</div>
		</div>
		<download :consignment="consignment"/>
  </div>
	<div class="dropp-consignment__seperator"></div>
	</div>
</template>


<style scoped lang="scss">
.dropp-consignment {
	max-width: 300px;
	margin: 0 auto;
}

.dropp-consignment__inner  {
  padding: 20px 16px;
  border-radius: 4px;
}
.dropp-consignment__seperator  {
	margin: 4px 16px;
  border-bottom: 1px solid #999999;
}

.dropp-consignment:last-child  .dropp-consignment__seperator{
  display: none;
}

.dropp-consignment__card-label {
	font-weight: 600;
}

.dropp-consignment__card-content {
	margin-bottom: 16px;
	line-height: 1.75;
	font-size: 14px;
}

.dropp-consignment__card-content > div {
	display: flex;
	justify-content: space-between;
}

.dropp-consignment__barcode {
	font-size: 18px;
	font-weight: 700;
}

.dropp-consignment__barcode-wrapper {
	display: flex;
	justify-content: space-between;
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
