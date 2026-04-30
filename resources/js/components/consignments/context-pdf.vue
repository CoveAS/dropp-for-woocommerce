<template>
	<div class="context-pdf">
		<ul class="context-pdf__list">
			<li class="context-pdf__error" v-if="error"> Error </li>
			<li
				class="pdf-actions"
				v-if="! error"
				v-for="pdf, index in pdfs"
				:key="index"
			>
				<a
					class="pdf-action pdf-action--download"
					target="_blank"
					:href="download_url( pdf.barcode )"
				>
					<document-icon class="pdf-action__icon" />
					<span>{{ pdf.label }}</span>
					<span
						tabindex="0"
						v-if="pdf.barcode"
						class="pdf-action pdf-action--delete"
						href=""
						@click.stop.prevent="delete_pdf( pdf.barcode )"
						@keydown.enter.space.stop.prevent="delete_pdf( pdf.barcode )"
					><x-icon /></span>
				</a>
			</li>
			<li v-if="editable">
				<span
					tabindex="0"
					class="dropp-consignment__action pdf-action pdf-action--add"
					href="#"
					@click.prevent="add_pdf"
					@keydown.enter.space.prevent="add_pdf"
				>
					<plus-icon class="pdf-action__icon" />
					<span v-html="i18n.extra_pdf"></span>
				</span>
			</li>
		</ul>
	</div>
</template>
<style lang="scss">
.context-pdf__error {
	background-color: #FFF0F2;
	cursor: default;
	display: block;
	text-decoration: none;
	padding: 8px 12px;
	border-radius: 4px;
}
.context-pdf {
	position: relative;

	a.pdf-action {
		display: flex;
		align-items: center;
		font-weight: normal;
	}

	.pdf-action__icon {
		margin-right: 8px;
		flex-shrink: 0;
	}


	&__list {
		margin-top: 0;
		padding: 0;
		list-style: none;
	}
}

.pdf-actions {
	display: block;
}

.dropp-context-menu .context-pdf .pdf-action--add:focus,
.dropp-context-menu .context-pdf .pdf-action--add:hover {
  background-color: #f3f4f6;
}


.context-pdf .pdf-action--delete {
	margin-left: auto;
	color: #AC0000;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 4px;
	margin-top: -4px;
	margin-bottom: -4px;
	margin-right: -4px;
	line-height: 1;
	border-radius: 4px;
	transition: background-color 0.2s;

	&:hover {
		background-color: #f3f4f6;
	}
}
</style>
<script>
import DocumentIcon from "../icons/document-icon.vue";
import PlusIcon from "../icons/plus-icon.vue";
import XIcon from "../icons/x-icon.vue";

export default {
	data: function () {
		return {
			i18n: _dropp.i18n,
			pdfs: [],
			loading: false,
			error: false,
		}
	},
	props: [
		'consignment_id',
		'editable'
	],
	mounted: function () {
		this.get_pdfs();
	},
	methods: {
		download_url: function (barcode) {
			let url = _dropp.ajaxurl + '?action=dropp_pdf_single&consignment_id=' + this.consignment_id;
			if (barcode) {
				url += '&barcode=' + barcode;
			}
			return url
		},
		get_pdfs: function () {
			if (this.loading) {
				return;
			}
			this.loading = true;
			jQuery.ajax({
				url: _dropp.ajaxurl,
				method: 'get',
				data: {
					action: 'dropp_get_pdf_list',
					consignment_id: this.consignment_id,
				},
				success: this.success,
				error: this.error_handler,
			});
		},
		add_pdf: function () {
			if (this.loading) {
				return;
			}
			this.loading = true;
			jQuery.ajax({
				url: _dropp.ajaxurl,
				method: 'get',
				data: {
					action: 'dropp_add_extra_pdf',
					consignment_id: this.consignment_id,
				},
				success: this.success,
				error: this.error_handler,
			});
		},
		delete_pdf: function (barcode) {
			if (this.loading) {
				return;
			}
			this.loading = true;
			jQuery.ajax({
				url: _dropp.ajaxurl,
				method: 'get',
				data: {
					action: 'dropp_delete_extra_pdf',
					consignment_id: this.consignment_id,
					barcode: barcode
				},
				success: this.success,
				error: this.error_handler,
			});
		},
		success: function (data) {
			this.pdfs = data;
			this.loading = false;
		},
		error_handler: function (jqXHR, textStatus, errorThrown) {
			console.log(jqXHR);
			console.log(textStatus);
			console.log(errorThrown);
			this.loading = false;
			this.error = true;
		},
	},
	components: {DocumentIcon, PlusIcon, XIcon},
};
</script>
