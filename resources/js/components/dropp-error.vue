<script>

import ExclamationMark from "./icons/exclamation-mark.vue";

export default {
	name: "dropp-error",
	components: {ExclamationMark},
	props: ['level', 'title', 'dismissible'],
	data() {
		return {
			dismissed: false
		}
	},
	methods: {
		dismiss() {
			this.dismissed = true;
			this.$emit('dismiss');
		}
	}
}
</script>

<template>
	<div v-if="!dismissed" class="dropp-product-error" :class="'dropp-product-error--'+level">
		<div class="dropp-product-error__icon">
			<exclamation-mark/>
		</div>
		<div class="dropp-product-error__content">
			<strong class="dropp-product-error__title" v-html="title"></strong>
			<slot/>
		</div>
		<button
			v-if="dismissible"
			class="dropp-product-error__dismiss"
			@click="dismiss"
			type="button"
			aria-label="Dismiss"
		>&times;</button>
	</div>
</template>

<style scoped lang="scss">
.dropp-product-error {
	padding: 16px;
	border-radius: 8px;
	background-color: #fffbeb;
	border: 1px solid #fde68a;
	display: flex;
	gap: 12px;
	align-items: flex-start;
	margin-top: 16px;
}

.dropp-product-error--error {
	background-color: #fffbeb;
}

.dropp-product-error__icon {
	color: #d97706;
	flex-shrink: 0;
}

.dropp-product-error--error .dropp-product-error__icon {
	color: #d97706;
}

.dropp-product-error__title {
	display: block;
	font-weight: 600;
	color: #d97706;
}

.dropp-product-error__content {
	text-wrap: balance;
	flex: 1;
	color: #d97706;
}

.dropp-product-error__dismiss {
	flex-shrink: 0;
	background: none;
	border: none;
	font-size: 24px;
	line-height: 1;
	color: #d97706;
	cursor: pointer;
	padding: 0 4px;
	border-radius: 4px;
	transition: background-color 0.15s;

	&:hover {
		background-color: #fef3c7;
	}

	&:focus {
		outline: none;
		background-color: #fef3c7;
	}
}
</style>
