<template>
	<view :style="[hotspotWrapStyle]">
		<view class="hotspot">
			<image :src="dataConfig.picStyle.url" mode="widthFix" class="image" :style="[imageRadius]"></image>
			<view v-for="(item, index) in dataConfig.picStyle.list" :key="item.number" :style="{
				top: `${item.starY}rpx`,
				left: `${item.starX}rpx`,
				width: `${item.areaWidth}rpx`,
				height: `${item.areaHeight}rpx`,
			}" class="area" @click="goPage(item.link)"></view>
		</view>
	</view>
</template>

<script>
	export default {
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isSortType: {
				type: String | Number,
				default: 0
			}
		},
		data() {
			return {}
		},
		computed: {
			imageRadius() {
				let borderRadius = `${this.dataConfig.fillet.val * 2}rpx`;
				if (this.dataConfig.fillet.type) {
					borderRadius =
						`${this.dataConfig.fillet.valList[0].val * 2}rpx ${this.dataConfig.fillet.valList[1].val * 2}rpx ${this.dataConfig.fillet.valList[3].val * 2}rpx ${this.dataConfig.fillet.valList[2].val * 2}rpx`;
				}
				return {
					'border-radius': borderRadius,
				};
			},
			hotspotWrapStyle() {
				return {
					'padding': `${this.dataConfig.topConfig.val*2}rpx ${this.dataConfig.prConfig.val*2}rpx ${this.dataConfig.bottomConfig.val*2}rpx`,
					'margin-top': `${this.dataConfig.mbConfig.val*2}rpx`,
					'background': this.dataConfig.bottomBgColor.color[0].item,
				};
			},
		},
		methods: {
			goPage(link) {
				this.$util.JumpPath(link);
			},
		},
	}
</script>

<style lang="scss" scoped>
	.hotspot {
		position: relative;

		.image {
			display: block;
			width: 100%;
		}

		.area {
			position: absolute;
		}
	}
</style>