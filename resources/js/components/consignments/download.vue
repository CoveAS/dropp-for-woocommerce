<script>
import DownloadIcon from "../icons/download-icon.vue";

export default {
	name: "download",
	components: {DownloadIcon},
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
	>
		<download-icon />
		{{ i18n.download }}
	</a>
</template>

<style scoped lang="scss">

</style>
