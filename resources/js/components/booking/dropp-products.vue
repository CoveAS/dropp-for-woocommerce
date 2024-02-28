<template>
	<div class="dropp-products">
		<h3 v-html="i18n.products"></h3>
		<div class="dropp-products__product"
				 v-for="product in products"
				 :key="product.sku"
		>
			<label class="dropp-products__product-name">
				<input type="checkbox" v-model="product.checked">
				<span v-html="product.name"></span>
			</label>
			<div class="dropp-products__weight">
				<span v-html="i18n.weight + ': '"></span><span v-html="product.weight + ' Kg'"></span>
			</div>
			<div class="dropp-products__quantity">
				<span v-html="i18n.quantity + ': '"></span>
				<div v-if="editable" class="dropp-products__quantity-wrapper">
					<span class="dropp-products__quantity-decrease"
								@click.prevent="product._quantity = Math.max(0, product._quantity - 1)">-</span>
					<input
						v-if="product.checked"
						class="dropp-products__quantity-input"
						type="number"
						step="1"
						min="0"
						:max="999999"
						:style="'width:' + w(product._quantity)"
						v-model.number="product._quantity"
					>
					<input
						v-if="! product.checked"
						class="dropp-products__quantity-input"
						type="text"
						value="0"
						v-model="zero"
						readonly
					>
					<span class="dropp-products__quantity-increase"
								@click.prevent="product._quantity = Math.min(999, product._quantity + 1)">+</span>
				</div>
				<span v-else>
					{{ product._quantity }}
				</span>
			</div>
		</div>
		<div class="dropp-products__total-weight">
			<span v-html="i18n.total_weight + ': '"></span>
			<span v-html="totalWeight + ' Kg'"></span>
		</div>
	</div>
</template>

<style lang="scss">
.dropp-products {
	border-bottom: 1px solid #CCCCCC;
	margin: 0 16px;
	padding-bottom: 16px;
	@container (min-width: 600px) {
		margin: 0 24px;
		padding-bottom: 24px;
	}
	&__product {
		margin-bottom: 24px;

		&:last-child {
			margin-bottom: 0;
		}
	}

	h3 {
		margin-top: 0;
		margin-bottom: 0.5rem;
	}

	&__weight,
	&__quantity {
		color: #999999;
	}

	&__product-name {
		display: block;
		margin-bottom: 8px;
	}

	&__weight {
		margin-bottom: 4px;
	}
  &__total-weight {
		text-align: right;
		font-weight: 600;
  }

	&__quantity {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	& &__quantity-input {
		width: 10px;
		text-align: right;
		border: none !important;
		box-shadow: none !important;
		padding: 0;
	}

	&__quantity-input::-webkit-outer-spin-button,
	&__quantity-input::-webkit-inner-spin-button {
		-webkit-appearance: none;
		margin: 0;
	}

	&__quantity-input[type=number] {
		-moz-appearance: textfield; /* Firefox */
	}

	&__quantity-decrease,
	&__quantity-increase {
		cursor: default;
		user-select: none;
		color: #999999;
		padding: 4px 8px;

		&:hover {
			background-color: #E1E1E1;
			color: #666666;
		}
	}

	&__quantity-wrapper {
		display: inline-flex;
		align-items: center;
		border: 1px solid #999999;
		border-radius: 4px;
	}
}
</style>

<script>
import _ from "lodash";

export default {
	data: function () {
		return {
			i18n: _dropp.i18n,
			zero: 0,
		};
	},
	props: ['products', 'editable'],
	methods: {
		w(num) {
			const len = Math.min(7, Math.max(1, num.toString().length));
			return (len * 10) + 'px';
		}
	},
	computed: {
		totalWeight() {
			return _.reduce(
				this.products,
				(carry, product) => {
					return carry + (product.checked ? 1 : 0) * product._quantity * product.weight;
				},
				0
			);
		}
	}
}
</script>
