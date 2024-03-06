<template>
	<div class="dropp-order-modal" @click.prevent="close_modal">
		<div class="dropp-order-modal__inner"
				 @click.prevent.stop="">
			<div class="dropp-order-modal__bar">
				<div
					class="dropp-close-modal"
					@click.prevent="close_modal"
					@keydown.enter.space.prevent="close_modal"
					tabindex="0"
				><span v-html="i18n.close_modal"></span> &times;
				</div>
			</div>
			<location
				@booked="processBooked($event, location)"
				:consignment="consignment"
				:location="location"
			></location>
		</div>
	</div>
</template>
<style lang="scss">
body.dropp-modal-open {
	height: 100vh;
	overflow-y: hidden;
}

.dropp-order-modal {
	position: fixed;
	top: 0;
	bottom: 0;
	left: 0;
	right: 0;
	display: flex;
	align-items: center;
	z-index: 1000000;
	overflow: visible;
	background-color: #cccccc;
}

.dropp-close-modal {
	margin-left: auto;
	padding: 8px 16px;
	cursor: default;
	user-select: none;
	transition: color 0.2s;
}
.dropp-close-modal:hover {
	color: #990000;
}

.dropp-order-modal__bar {
	display: flex;
	border-bottom: 1px solid #CCCCCC;
	background-color: white;
	margin: 0 -16px;
}

.dropp-order-modal__inner {
	container-type: inline-size;
	background: white;
	padding: 0 16px;
	min-width: 16rem;
	max-width: 1080px;
	width: 100%;
	height: auto;
	max-height: 100%;
	overflow: auto;
  margin: 0 auto;
}
@media (min-width: 800px) {
	.dropp-order-modal {
		padding: 40px 32px;
		background: rgba(0, 0, 0, 0.6);
	}
  .dropp-order-modal__inner {
	border-radius: 4px;
}
}
</style>

<script>
import Location from '../booking/location.vue';

export default {
	props: ['consignment'],
	data: function () {
		return {
			location: this.consignment.location,
			loading: false,
			i18n: _dropp.i18n,
		}
	},
	components: {
		location: Location,
	},
	mounted() {
		jQuery('body').addClass('dropp-modal-open');
	},
	methods: {
		close_modal: function () {
			jQuery('body').removeClass('dropp-modal-open');
			this.$parent.modal_consignment = null;
		},
		processBooked(consignment, location) {
			jQuery('.dropp-consignment-' + consignment.id).removeClass('dropp-consignment--new');
			consignment.new = true;
			const index = _.findIndex(_dropp.consignments, {id: consignment.id});
			_dropp.consignments.splice(index, 1, consignment);
			this.close_modal();
			setTimeout(()=>{
				consignment.new = false;
			}, 4000);
		},
	}
};
</script>
