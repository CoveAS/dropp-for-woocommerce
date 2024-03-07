<script>

import time_ago from "../time-ago";

export default {
	name: "time-ago",
	props: ['value'],
	computed: {
		timeAgo() {
			return time_ago(this.value);
		}
	}
};
</script>

<template>
	<span>{{ timeAgo }}</span>
</template>

<style scoped lang="scss">

</style>
