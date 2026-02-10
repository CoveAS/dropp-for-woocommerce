<script>
export default {
	name: "quantity",
	data() {
		return {
		  quantity: this.value,
			zero: 0,
		}
	},
	props: ['value', 'disabled'],
	methods: {
		w(num) {
			const len = Math.min(7, Math.max(1, num.toString().length));
			return 48 + (len * 10) + 'px';
		},
		increase() {
			if (this.disabled) {
				return;
			}
			this.quantity = Math.min(999999, parseInt(this.quantity, 10) + 1)
		},
		decrease() {
			if (this.disabled) {
				return;
			}
			this.quantity = Math.max(0, parseInt(this.quantity, 10) - 1)
		}
	},
  watch: {
    quantity(newVal) {
      this.$emit('input', newVal);
    }
  },
};
</script>

<template>
	<div class="dropp-quantity" :class="disabled ? 'dropp-quantity--disabled' : 'dropp-quantity--active'">
		<button
			type="button"
			class="dropp-quantity__btn dropp-quantity__btn--decrease"
			@click.prevent="decrease"
			:disabled="disabled"
		>
			<svg width="12" height="2" viewBox="0 0 12 2" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M1 1H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
		</button>
		<input
			v-if="!disabled"
			class="dropp-quantity__input"
			type="number"
			step="1"
			min="0"
			:max="999999"
			v-model="quantity"
		>
		<input
			v-else
			class="dropp-quantity__input"
			type="text"
			value="0"
			disabled
		>
		<button
			type="button"
			class="dropp-quantity__btn dropp-quantity__btn--increase"
			@click.prevent="increase"
			:disabled="disabled"
		>
			<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 1V11M1 6H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
		</button>
	</div>
</template>

<style scoped lang="scss">
.dropp-quantity {
	display: inline-flex;
	align-items: center;
	gap: 8px;
}

.dropp-quantity__btn {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 32px;
	height: 32px;
	border-radius: 6px;
	border: 1px solid #d1d5db;
	background: #fff;
	color: #374151;
	cursor: pointer;
	transition: all 0.2s;
	padding: 0;

	&:hover:not(:disabled) {
		border-color: #9ca3af;
		background: #f9fafb;
		color: #111827;
	}

	&:disabled {
		opacity: 0.5;
		cursor: not-allowed;
		background: #f3f4f6;
	}
}

.dropp-quantity__input {
	width: 54px;
	height: 32px;
	text-align: center;
	border: 1px solid #d1d5db;
	border-radius: 6px;
	font-size: 14px;
	font-weight: 500;
	color: #111827;
	background: #fff;
	padding: 0;
	margin: 0;
	-moz-appearance: textfield;
	appearance: textfield;

	&::-webkit-outer-spin-button,
	&::-webkit-inner-spin-button {
		-webkit-appearance: none;
		margin: 0;
	}

	&:focus {
		outline: none;
		border-color: #000;
		box-shadow: 0 0 0 1px #000;
	}

	&:disabled {
		background: #f3f4f6;
		color: #9ca3af;
		cursor: not-allowed;
	}
}
</style>
