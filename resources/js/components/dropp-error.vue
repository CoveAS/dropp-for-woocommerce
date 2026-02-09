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

<style lang="scss">
// Styles are defined globally in dropp-admin.scss
</style>
