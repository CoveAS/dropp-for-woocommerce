<template>
	<div class="dropp-products">
		<div class="dropp-products__inner">
			<h3 v-html="i18n.products"></h3>
			<div class="dropp-products__description" v-html="i18n.products_description"> </div>
			<div class="dropp-products__headers">
				<span class="dropp-products__label" v-html="i18n.item"></span>
				<span class="dropp-products__label" v-html="i18n.quantity"></span>
				<span class="dropp-products__label" v-html="i18n.weight"></span>
			</div>
			<div class="dropp-products__product"
					 v-for="product in products"
					 :key="product.sku"
			>
				<label class="dropp-products__product-name">
					<input type="checkbox" v-model="product.checked">
					<span v-html="product.name"></span>
				</label>
				<div class="dropp-products__weight">
					<span class="dropp-products__label" v-html="i18n.weight + ': '"></span><span v-html="product.weight.toFixed(2) + ' Kg'"></span>
				</div>
				<div
				class="dropp-products__quantity"
				:class="product._quantity > product.quantity && product.checked ? 'dropp-products__quantity--error' : ''"
				>
					<span class="dropp-products__label" v-html="i18n.quantity + ': '"></span>
					<quantity v-if="editable" v-model="product._quantity" :disabled="! product.checked"/>
					<span v-else> {{ product._quantity }} </span>
				</div>
			</div>
		<div class="dropp-products__total-weight" :class="weightLimitExceeded? 'dropp-text--error' : ''">
			<span v-html="i18n.total_weight + ': '"></span>
			<span>{{totalWeight}} Kg</span>
			<span v-if="location.weight_limit"> / {{location.weight_limit}} Kg</span>
		</div>
	</div>
		<div class="dropp-products__errors">
			<dropp-error
				level="error"
				:title="i18n.weight_limit_exceeded"
				v-if="weightLimitExceeded"
			>{{i18n.weight_limit_exceeded_message}}</dropp-error>
			<dropp-error
				level="warning"
				v-if="quantityExceeded"
				:title="i18n.quantity_exceeded"
			>{{i18n.quantity_exceeded_message}}</dropp-error>
		</div>
	</div>
</template>

<style lang="scss">
.dropp-text--error {
	color: #CC0000;
}

.dropp-products__errors {
	width: 100%;
	min-height: 1px;
  max-width: 600px
}
@container (min-width: 900px) {
	.dropp-products__errors {
		margin-top: 14px;
	}
}
.dropp-products {
	border-bottom: 1px solid #CCCCCC;
	margin: 0 16px;
	padding-bottom: 16px;
	@container (min-width: 600px) {
		margin: 0 24px;
		padding-bottom: 24px;
	}
	@container (min-width: 900px) {
		display: grid;
		grid-template-columns: min(calc(50% - 12px), 600px) min(calc(50% - 12px), 600px);
		gap: 24px;
	}

	input[type="checkbox"] {
		width: 1rem;
		height: 1rem;
	}

	input[type="checkbox"]:checked::before {
		margin: -0.1875rem 0 0 -0.25rem;
		height: 1.3125rem;
		width: 1.3125rem;
	}

	&__product {
		margin-bottom: 20px;

		&:last-child {
			margin-bottom: 0;
		}
	}

	h3 {
		margin-top: 0;
		margin-bottom: 4px;
	}

	&__weight,
	&__quantity {
		color: #999999;
	}
  &__quantity--error .dropp-quantity__input {
		background-color: #FFF0F2;
		border-color: #CE0147 !important;
  }

	&__product-name {
		display: block;
		margin-bottom: 8px;
		&:hover input {
			border-color: #00007D;
			box-shadow: 0 0 0 1px #1007FA;
		}
	}

	&__weight {
		margin-bottom: 4px;
	}

	&__total-weight {
		text-align: right;
		font-weight: 600;
	}
}
.dropp-products__quantity {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.dropp-products__description {
	margin-bottom: 8px;
}

.dropp-products__headers {
	color: #999999;
	display: none;
	margin-bottom: 24px;
}
@container (min-width: 600px) {
  .dropp-products__headers,
	.dropp-products__product {
		display: grid;
		grid-template-columns: 1fr minmax(100px, auto) 110px;
		align-items: center;
		gap: 8px;
  }
	.dropp-quantity {
		margin: 0 auto;
	}
  .dropp-products__product .dropp-products__label {
		display: none;
  }
	.dropp-products__weight {
		text-align: right;
		order: 3;
	}
  .dropp-products__headers span:nth-child(1) {
		padding-left: 23px;
  }
  .dropp-products__headers span:nth-child(2) {
		text-align: center;
  }
  .dropp-products__headers span:nth-child(3) {
		text-align: right;
  }
}
</style>

<script>
import DroppError from "../dropp-error.vue";
import Quantity from "./quantity.vue";

let conserve = window._;
const _ = require("lodash");
window._ = conserve;

export default {
  components: {Quantity, DroppError},
	data: function () {
		return {
			i18n: _dropp.i18n,
			zero: 0,
			products: [],
		};
	},
	mounted() {
		this.products = JSON.parse(JSON.stringify(this.value));
  },
  props: ['location', 'value', 'editable'],
	computed: {
		quantityExceeded() {
			return _.find(
				this.products,
				product => product.checked && product._quantity > product.quantity
			);
		},
		weightLimitExceeded() {
			let totalWeight = _.reduce(
				this.products,
				(carry, product) => carry + (product.checked ? product._quantity * product.weight : 0),
				0
			);
			if (0 === this.location.weight_limit) {
				return false;
			}
			return totalWeight > this.location.weight_limit;
		},
		totalWeight() {
			return _.reduce(
				this.products,
				(carry, product) => carry + (product.checked ? product._quantity * product.weight : 0),
				0.0
			).toFixed(2);
		}
	},
	watch: {
		products: {
		  deep: true,
			handler() {
				console.log('changed');
				this.$emit('input', this.products);
			}
		}
	}
}
</script>
