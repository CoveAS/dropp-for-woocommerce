<template>
	<ul>
		<li>
			<loader></loader>
		</li>
		<slot></slot>
	</ul>
</template>

<script>
	import Loader from './loader.vue';
	export default {
		components: {
			Loader,
		},
	};
</script>
