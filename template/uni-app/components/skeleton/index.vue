<template>
	<view v-if="show"
		:style="{width: systemInfo.width + 'px', height: systemInfo.height + 'px', backgroundColor: bgcolor, position: 'absolute', left: 0, top: 0, zIndex: 9998}">
		<view v-for="(item,rect_idx) in skeletonRectLists" :key="rect_idx + 'rect'"
			:class="[loading == 'chiaroscuro' ? 'chiaroscuro' : '']"
			:style="{width: item.width + 'px', height: item.height + 'px', backgroundColor: 'rgb(194, 207, 214,.3)', position: 'absolute', left: item.left + 'px', top: item.top + 'px'}">
		</view>
		<view v-for="(item,circle_idx) in skeletonCircleLists" :key="circle_idx + 'circle'"
			:class="loading == 'chiaroscuro' ? 'chiaroscuro' : ''"
			:style="{width: item.width + 'px', height: item.height + 'px', backgroundColor: 'rgb(194, 207, 214,.3)', borderRadius: item.width + 'px', position: 'absolute', left: item.left + 'px', top: item.top + 'px'}">
		</view>
		<view class="spinbox" v-if="loading == 'spin'">
			<view class="spin"></view>
		</view>
	</view>
</template>

<script>
	export default {
		name: "skeleton",
		props: {
			bgcolor: {
				type: String,
				value: '#FFF'
			},
			selector: {
				type: String,
				value: 'skeleton'
			},
			loading: {
				type: String,
				value: 'spin'
			},
			show: {
				type: Boolean,
				value: false
			},
			isNodes: {
				type: Number,
				value: false
			} //控制什么时候开始抓取元素节点,只要数值改变就重新抓取
		},
		data() {
			return {
				loadingAni: ['spin', 'chiaroscuro'],
				systemInfo: {},
				skeletonRectLists: [],
				skeletonCircleLists: []
			}
		},
		watch: {
			isNodes(val) {
				this.readyAction();
			}
		},
		mounted() {
			this.attachedAction();
		},
		methods: {
			attachedAction: function() {
				//默认的首屏宽高，防止内容闪现
				const systemInfo = uni.getSystemInfoSync();
				this.systemInfo = {
					width: systemInfo.windowWidth,
					height: systemInfo.windowHeight
				};
				this.loading = this.loadingAni.includes(this.loading) ? this.loading : 'spin';
			},
			readyAction: function() {
				const that = this;
				//绘制背景
				uni.createSelectorQuery().selectAll(`.${this.selector}`).boundingClientRect(function(res) {
					if (res[0] && res[0].length > 0)
						that.systemInfo.height = res[0][0].height + res[0][0].top;
				}).exec()

				//绘制矩形
				this.rectHandle();

				//绘制圆形
				this.radiusHandle();
			},
			rectHandle: function() {
				const that = this;

				//绘制不带样式的节点
				uni.createSelectorQuery().selectAll(`.${this.selector}-rect`).boundingClientRect().exec(function(res) {
					that.skeletonRectLists = res[0];
				});

			},
			radiusHandle() {
				const that = this;

				uni.createSelectorQuery().selectAll(`.${this.selector}-radius`).boundingClientRect().exec(function(res) {
					that.skeletonCircleLists = res[0];
				});
			}
		}
	}
</script>

<style>
	.spinbox {
		position: fixed;
		display: flex;
		justify-content: center;
		align-items: center;
		height: 100%;
		width: 100%;
		z-index: 9999
	}

	.spin {
		display: inline-block;
		width: 64rpx;
		height: 64rpx;
	}

	.spin:after {
		content: " ";
		display: block;
		width: 46rpx;
		height: 46rpx;
		margin: 1rpx;
		border-radius: 50%;
		border: 5rpx solid #409eff;
		border-color: #409eff transparent #409eff transparent;
		animation: spin 1.2s linear infinite;
	}

	@keyframes spin {
		0% {
			transform: rotate(0deg);
		}

		100% {
			transform: rotate(360deg);
		}
	}

	.chiaroscuro {
		width: 100%;
		height: 100%;
		background: rgb(194, 207, 214);
		animation-duration: 2s;
		animation-name: blink;
		animation-iteration-count: infinite;
	}

	@keyframes blink {
		0% {
			opacity: .4;
		}

		50% {
			opacity: 1;
		}

		100% {
			opacity: .4;
		}
	}

	@keyframes flush {
		0% {
			left: -100%;
		}

		50% {
			left: 0;
		}

		100% {
			left: 100%;
		}
	}

	.shine {
		animation: flush 2s linear infinite;
		position: absolute;
		top: 0;
		bottom: 0;
		width: 100%;
		background: linear-gradient(to left,
				rgba(255, 255, 255, 0) 0%,
				rgba(255, 255, 255, .85) 50%,
				rgba(255, 255, 255, 0) 100%)
	}
</style>
