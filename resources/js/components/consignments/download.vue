<script>

export default {
	name: "download",
	data: function () {
		return {
			i18n: _dropp.i18n,
		};
	},
	props: ['consignment'],
	methods: {
		download_url: function (consignment) {
			if (!consignment.dropp_order_id) {
				return;
			}
			return _dropp.ajaxurl + '?action=dropp_pdf&consignment_id=' + consignment.id;
		},
	}
};
</script>

<template>
	<a
		v-if="consignment.dropp_order_id"
		class="dropp-consignment-download-button"
		target="_blank"
		:href="download_url(consignment)"
		v-html="i18n.download"
	></a>
</template>

<style scoped lang="scss">

.dropp-consignment-download-button {
	// First item. Ie. "Download" button
	display: block;
	line-height: 1.2;
	background-color: #FFFFFF;
	color: #1007FA;
	border: 1px solid #0000EE;
	padding: 8px 24px;
	user-select: none;
	border-radius: 4px;
	flex: 1 0 auto;
	text-align: center;
	border-radius: 5px;
	text-decoration: none;

	&:hover {
		color: #1007FA;
		background-color: #f1f1f1;
	}
}
</style>
