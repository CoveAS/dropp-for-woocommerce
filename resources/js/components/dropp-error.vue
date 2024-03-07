<script>

import ExclamationMark from "./icons/exclamation-mark.vue";

export default {
	name: "dropp-error",
	components: {ExclamationMark},
	props: ['level', 'title'],
	mounted() {
		// console.log(this.heading, this, this.title, this.level)
	}
}
</script>

<template>
	<div class="dropp-product-error" :class="'dropp-product-error--'+level">
		<div class="dropp-product-error__icon">
			<exclamation-mark/>
		</div>
		<div class="dropp-product-error__content">
			<strong class="dropp-product-error__title" v-html="title"></strong>
			<slot/>
		</div>
	</div>
</template>

<style scoped lang="scss">
.dropp-product-error {
	padding: 16px;
	border-radius: 4px;
	border: 1px solid #FFCC1B;
	background-color: #FFFDEA;
	display: flex;
	gap: 12px;
}

.dropp-product-error--error {
	border-color: #FF638A;
	background-color: #FFF0F2;
}

.dropp-product-error__icon {
	color: #E28100;
}

.dropp-product-error--error .dropp-product-error__icon {
	color: #CE0147;
}

.dropp-product-error__title {
	display: block;
	font-weight: 600;
}

.dropp-product-error {
	margin-top: 16px;
}
</style>
