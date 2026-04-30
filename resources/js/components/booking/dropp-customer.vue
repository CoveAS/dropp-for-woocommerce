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
	&__title {
		margin: 0 0 16px 0;
		font-size: 18px;
		font-weight: 600;
	}

	.form-field {
		display: block;
		margin-bottom: 24px;
		&:last-child {
			margin-bottom: 0;
		}
	}

	.input-label {
		display: block;
		margin-bottom: 8px;
		font-size: 14px;
		font-weight: 500;
	}

	.input-field,
	.field-value {
		width: 100%;
		box-sizing: border-box;
	}

	.input-field {
		border: 1px solid #d1d5db;
		padding: 8px 12px;
		font-size: 16px;
		&::placeholder {
			color: #9ca3af;
		}
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
