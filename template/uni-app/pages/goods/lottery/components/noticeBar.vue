<template>
	<!-- 无缝滚动效果 -->
	<div class="marquee-wrap">
		<ul class="marquee-list" :class="{'animate-up': animateUp}">
			<li v-for="(item, index) in listData" :key="index">{{$t(`恭喜您`)}} {{item.user.nickname || '**'}} {{$t(`获得`)}} {{item.prize.name}}
			</li>
		</ul>
	</div>
</template>

<script>
	export default {
		name: "noticeBar",
		data() {
			return {
				animateUp: false,
				listData: JSON.parse(JSON.stringify(this.showMsg)),
				timer: null
			}
		},
		props: {
			showMsg: {
				type: Array
			}
		},
		mounted() {

			this.timer = setInterval(this.scrollAnimate, 2500);
		},
		methods: {
			scrollAnimate() {
				this.animateUp = true
				setTimeout(() => {
					this.listData.push(this.listData[0])
					this.listData.shift()
					this.animateUp = false
				}, 500)
			}
		},
		destroyed() {
			clearInterval(this.timer)
		}
	};
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
