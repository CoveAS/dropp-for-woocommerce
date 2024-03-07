<template>
	<div class="dropp-customer">
		<h3 class="dropp-customer__title" v-html="i18n.customer"></h3>
		<label class="form-field">
			<span class="input-label" v-html="i18n.name"></span>
			<input v-if="editable" class="input-field" type="text" v-model="customer.name">
			<span v-else class="field-value" v-html="customer.name"></span>
		</label>

		<label class="form-field">
			<span class="input-label" v-html="i18n.email_address"></span>
			<input v-if="editable" class="input-field" type="email" v-model="customer.emailAddress">
			<span v-else class="field-value" v-html="customer.emailAddress"></span>
		</label>

		<label class="form-field" v-if="ssn_enabled">
			<span class="input-label" v-html="i18n.social_security_number"></span>
			<input v-if="editable" class="input-field" type="text" v-model="customer.socialSecurityNumber">
			<span v-else class="field-value" v-html="customer.socialSecurityNumber"></span>
		</label>

		<label class="form-field">
			<span class="input-label" v-html="i18n.address"></span>
			<input v-if="editable" class="input-field" type="text" v-model="customer.address">
			<span v-else class="field-value" v-html="customer.address"></span>
		</label>

		<label class="form-field">
			<span class="input-label" v-html="i18n.phone_number"></span>
			<input v-if="editable" class="input-field" type="text" v-model="customer.phoneNumber">
			<span v-else class="field-value" v-html="customer.phoneNumber"></span>
		</label>
	</div>
</template>
<style lang="scss">
.dropp-customer {
	margin-bottom: 24px;
	.form-field {
		display: block;
		margin-bottom: 16px;
		&:last-child {
			margin-bottom: 0;
		}
	}

	.input-label {
		display: block;
		margin-bottom: 4px;

	}

	.input-field,
	.field-value {
		min-width: 10rem;
		max-width: 20rem;
	}

	.input-field {
		border: 1px solid #999999;
		padding: 4px 12px;
		font-size: 14px;
	}
}
</style>
<script>
export default {
	props: ['customer', 'editable'],
	data: function () {
		return {
			i18n: _dropp.i18n,
			ssn_enabled: _dropp.ssn_enabled,
		};
	},
};
</script>
