<template>
	<!-- 无缝滚动效果 -->
	<div class="marquee-wrap">
		<ul class="marquee-list" :class="{'animate-up': animateUp}">
			<li v-for="(item, index) in listData" :key="index">{{$t(`恭喜您`)}} {{item.user.nickname || '**'}} {{$t(`获得`)}} {{item.prize.name}}
			</li>
		</ul>
	</div>
</template>

<script setup>
	import { ref, onMounted, onUnmounted } from 'vue';

	defineOptions({
		name: "noticeBar"
	});

	const props = defineProps({
		showMsg: {
			type: Array
		}
	});

	const animateUp = ref(false);
	const listData = ref(JSON.parse(JSON.stringify(props.showMsg)));
	let timer = null;

	onMounted(() => {
		timer = setInterval(scrollAnimate, 2500);
	});

	function scrollAnimate() {
		animateUp.value = true
		setTimeout(() => {
			listData.value.push(listData.value[0])
			listData.value.shift()
			animateUp.value = false
		}, 500)
	}

	onUnmounted(() => {
		clearInterval(timer)
	})
</script>

<style lang="scss" scoped>
	.marquee-wrap {
		width: 100%;
		height: 40rpx;
		border-radius: 20px;
		margin: 0 auto;
		overflow: hidden;

		.marquee-list {
			padding: 0;

			li {
				width: 100%;
				height: 100%;
				text-overflow: ellipsis;
				overflow: hidden;
				white-space: nowrap;
				padding: 0;
				list-style: none;
				line-height: 40rpx;
				text-align: center;
				color: #fff;
				font-size: 20rpx;
				font-weight: 400;
			}
		}

		.animate-up {
			transition: all 0.5s ease-in-out;
			transform: translateY(-40rpx);
		}
	}
</style>
