<template>
	<div class="dropp-customer">
		<h3 class="dropp-customer__title">♻️Customer</h3>
		<label class="form-field">
			<span class="input-label">♻️name</span>
			<input class="input-field" type="text" v-model="customer.name">
		</label>

		<label class="form-field">
			<span class="input-label">♻️emailAddress</span>
			<input class="input-field" type="email" v-model="customer.emailAddress">
		</label>

		<label class="form-field" v-if="0">
			<span class="input-label">♻️socialSecurityNumber</span>
			<input class="input-field" type="text" v-model="customer.socialSecurityNumber">
		</label>

		<label class="form-field">
			<span class="input-label">♻️address</span>
			<input class="input-field" type="text" v-model="customer.address">
		</label>

		<label class="form-field">
			<span class="input-label">♻️phoneNumber</span>
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
			return {};
		}
	};
</script>
