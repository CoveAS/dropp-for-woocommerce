<template>
	<div class="dropp-products">
		<div class="dropp-products__inner">
			<div class="dropp-products__header">
				<h3 v-html="i18n.products"></h3>
				<p class="dropp-products__description" v-html="i18n.products_description"></p>
			</div>
			<div class="dropp-products__errors">
				<dropp-error
					level="error"
					:title="i18n.weight_limit_exceeded"
					v-if="weightLimitExceeded && quantityExceeded"
				>{{i18n.weight_limit_exceeded_quantity_message}}</dropp-error>
				<dropp-error
					level="error"
					:title="i18n.weight_limit_exceeded"
					v-else-if="weightLimitExceeded"
				>{{i18n.weight_limit_exceeded_message}}</dropp-error>
				<dropp-error
					level="warning"
					:title="i18n.quantity_exceeded"
					v-else-if="quantityExceeded"
				>{{i18n.quantity_exceeded_message}}</dropp-error>
			</div>
			<div class="dropp-products__headers">
				<span class="dropp-products__label" v-html="i18n.item"></span>
				<span class="dropp-products__label" v-html="i18n.quantity"></span>
				<span class="dropp-products__label" v-html="i18n.weight"></span>
			</div>
			<div class="dropp-products__product"
					 v-for="product in products"
					 :key="product.barcode"
			>
				<div class="dropp-products__item-info">
					<input type="checkbox" v-model="product.checked">
					<div class="dropp-products__image-container" v-if="product.image">
						<img :src="product.image" :alt="product.name" class="dropp-products__image">
					</div>
					<div class="dropp-products__image-placeholder" v-else></div>
					<div class="dropp-products__name-weight">
						<span class="dropp-products__name" v-html="product.name"></span>
						<div class="dropp-products__weight">
							<span class="dropp-products__label" v-html="i18n.weight + ': '"></span>
							<span v-html="product.weight.toFixed(2) + ' Kg'"></span>
						</div>
					</div>
				</div>
				<div
					class="dropp-products__quantity"
					:class="product._quantity > product.quantity && product.checked ? 'dropp-products__quantity--error' : ''"
				>
					<span class="dropp-products__quantity-label" v-html="i18n.quantity + ':'"></span>
					<quantity v-if="editable" v-model="product._quantity" :disabled="! product.checked"/>
					<span v-else> {{ product._quantity }} </span>
				</div>
			</div>
			<div class="dropp-products__footer">
				<div class="dropp-products__total-weight" :class="weightLimitExceeded? 'dropp-text--error' : ''">
					<span v-html="i18n.total_weight + ': '"></span>
					<span>{{totalWeight}} Kg</span>
					<span v-if="location.weight_limit"> / {{location.weight_limit}} Kg</span>
				</div>
				<div class="dropp-products__weight-exceeded" v-if="weightLimitExceeded">
					<exclamation-mark />
					<span v-html="i18n.weight_limit_exceeded"></span>
				</div>
			</div>
		</div>
	</div>
</template>

<style lang="scss">
.dropp-products {
	margin: 0 16px;
	padding-bottom: 24px;
	margin-bottom: 18px;
	max-width: 1200px;

	@container (min-width: 600px) {
		margin: 0 24px;
		padding-bottom: 24px;
	}

	@container (min-width: 900px) {
		margin-bottom: 14px;
		padding-bottom: 34px;
	}

	&__header {
		margin-bottom: 16px;

		body & h3 {
			margin-top: 0;
			margin-bottom: 4px;
			font-weight: 600;
			font-size: 18px;
			line-height: 24px;
			color: #111827;
		}
	}

	&__description {
		margin: 0;
		font-size: 14px;
		line-height: 20px;
		color: #6b7280;
	}

	&__errors:not(:empty) {
		width: 100%;
		margin-top: 16px;
		margin-bottom: 24px;
	}

	&__headers {
		display: none;
		color: #6b7280;
		font-size: 12px;
		font-weight: 500;
		text-transform: uppercase;
		letter-spacing: 0.05em;
		padding: 16px 0;
		border-bottom: 1px solid #e5e7eb;
		margin-bottom: 0;

		@container (min-width: 600px) {
			display: grid;
			grid-template-columns: 1fr 160px 100px;
			gap: 24px;
			justify-items: start;
		}

		span:first-child {
			justify-self: start;
			padding-left: 0;
		}
	}

	&__product {
		display: flex;
		flex-direction: column;
		padding: 0;
		border: 1px solid #e5e7eb;
		border-radius: 12px;
		background: #fff;
		box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
		margin-bottom: 16px;
		overflow: hidden;

		&:last-of-type {
			margin-bottom: 0;
		}

		@container (min-width: 600px) {
			display: grid;
			grid-template-columns: 1fr 160px 100px;
			align-items: center;
			justify-items: start;
			gap: 24px;
			padding: 16px 0;
			text-align: left;
			border: none;
			border-radius: 0;
			background: transparent;
			box-shadow: none;
			border-bottom: 1px solid #e5e7eb;
			margin-bottom: 0;
			overflow: visible;

			&:last-of-type {
				border-bottom: none;
			}
		}
	}

	&__item-info {
		display: flex;
		align-items: center;
		gap: 16px;
		padding: 16px;
		width: 100%;
		box-sizing: border-box;

		@container (min-width: 600px) {
			justify-self: start;
			width: auto;
			padding: 0;
		}
	}

	&__name-weight {
		display: flex;
		flex-direction: column;
		gap: 4px;
		text-align: left;
	}

	&__image-container,
	&__image-placeholder {
		width: 64px;
		height: 64px;
		border-radius: 8px;
		border: 1px solid #e5e7eb;
		overflow: hidden;
		background: #f9fafb;
		flex-shrink: 0;

		@container (min-width: 600px) {
			width: 48px;
			height: 48px;
		}
	}

	&__image {
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	&__name {
		font-weight: 600;
		font-size: 16px;
		color: #111827;
	}

	&__weight {
		display: flex;
		align-items: center;
		color: #6b7280;
		font-size: 14px;

		@container (min-width: 600px) {
			justify-content: flex-start;
			color: #4b5563;
		}
	}

	&__quantity {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 16px;
		border-top: 1px solid #e5e7eb;
		width: 100%;
		box-sizing: border-box;

		@container (min-width: 600px) {
			padding: 0;
			border-top: none;
			justify-content: flex-start;
			width: auto;
		}

		&--error .dropp-quantity__input {
			background-color: #fef2f2;
			border-color: #ef4444 !important;
		}
	}

	&__quantity-label {
		font-size: 16px;
		color: #6b7280;
		font-weight: 500;

		@container (min-width: 600px) {
			display: none;
		}
	}

	&__label {
		color: #6b7280;
		@container (min-width: 600px) {
			display: none;
		}
	}

	&__headers &__label {
		display: block;
	}

	&__headers span:nth-child(2) {
		text-align: left;
	}

	&__headers span:nth-child(3) {
		text-align: left;
	}

	&__footer {
		margin-top: 24px;
		display: flex;
		flex-direction: column;
		align-items: flex-end;
	}

	&__total-weight {
		font-size: 16px;
		color: #374151;
	}

	&__weight-exceeded {
		display: flex;
		align-items: center;
		gap: 6px;
		color: #dc2626;
		font-size: 13px;
		margin-top: 4px;

		svg {
			width: 16px;
			height: 16px;
		}
	}
}

.dropp-text--error {
	color: #dc2626;
}
</style>

<script>
import DroppError from "../dropp-error.vue";
import Quantity from "./quantity.vue";
import ExclamationMark from "../icons/exclamation-mark.vue";

let conserve = window._;
const _ = require("lodash");
window._ = conserve;

export default {
  components: {Quantity, DroppError, ExclamationMark},
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
