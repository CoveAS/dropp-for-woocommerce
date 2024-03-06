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
			this.quantity = Math.min(999999, this.quantity + 1)
		},
		decrease() {
			if (this.disabled) {
				return;
			}
			this.quantity = Math.max(0, this.quantity - 1)
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
		<span
			class="dropp-quantity__decrease"
			@click.prevent="decrease"
		>-</span>
		<input
			v-if="!disabled"
			class="dropp-quantity__input"
			type="number"
			step="1"
			min="0"
			:max="999999"
			:style="'width:' + w(quantity)"
			v-model="quantity"
		>
		<input
			v-if="disabled"
			class="dropp-quantity__input"
			type="text"
			value="0"
			v-model="zero"
			disabled
		>
		<span
			class="dropp-quantity__increase"
			@click.prevent="increase"
		>+</span>
	</div>
</template>

<style scoped lang="scss">

.dropp-quantity {
	position: relative;
}
.dropp-quantity__input {
  min-height: 20px;
  width: 58px;
  text-align: right;
  border: 1px solid #999999;
  box-shadow: none !important;
	font-size: 14px;
  padding: 0 24px;
}

.dropp-quantity__input::-webkit-outer-spin-button,
.dropp-quantity__input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.dropp-quantity__input[type=number] {
  -moz-appearance: textfield; /* Firefox */
}

.dropp-quantity__decrease,
.dropp-quantity__increase {
  position: absolute;
  cursor: default;
  user-select: none;
  color: #999999;
  padding: 4px 8px;

  .dropp-quantity--active &:hover {
		background-color: rgba(0,0,0,0.1);
		color: #666666;
  }
	top: 0;
	bottom: 0;
}
.dropp-quantity__decrease {
  left: 0;
  border-top-left-radius: 4px;
  border-bottom-left-radius: 4px;
}
.dropp-quantity__increase {
	right: 0;
	border-top-right-radius: 4px;
  border-bottom-right-radius: 4px;
}

</style>
