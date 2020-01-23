<template>
	<div class="dropp-customer">
		<h3 class="dropp-customer__title" v-html="i18n.customer"></h3>
		<label class="form-field">
			<span class="input-label" v-html="i18n.name"></span>
			<input class="input-field" type="text" v-model="customer.name">
		</label>

		<label class="form-field">
			<span class="input-label" v-html="i18n.email_address"></span>
			<input class="input-field" type="email" v-model="customer.emailAddress">
		</label>

		<label class="form-field" v-if="0">
			<span class="input-label" v-html="i18n.social_security_number"></span>
			<input class="input-field" type="text" v-model="customer.socialSecurityNumber">
		</label>

		<label class="form-field">
			<span class="input-label" v-html="i18n.address"></span>
			<input class="input-field" type="text" v-model="customer.address">
		</label>

		<label class="form-field">
			<span class="input-label" v-html="i18n.phone_number"></span>
			<input class="input-field" type="text" v-model="customer.phoneNumber">
		</label>
	</div>
</template>
<style lang="scss">
	.dropp-customer{
		.form-field {
			display: flex;
		}
		.input-label {
			flex: 0 0 10rem;
		}
	}
</style>
<script>
	export default {
		props: ['customer'],
		data: function() {
			return {
				i18n: _dropp.i18n
			};
		}
	};
</script>
