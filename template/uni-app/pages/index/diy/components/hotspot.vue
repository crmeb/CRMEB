<template>
	<view class="w-full" :style="[hotspotWrapStyle]">
		<view class="hotspot">
			<image :src="dataConfig.picStyle.url" mode="widthFix" class="image" :style="[imageRadius]"></image>
			<view
				v-for="(item, index) in dataConfig.picStyle.list"
				:key="item.number"
				:style="{
					top: `${item.starY / 2}px`,
					left: `${item.starX / 2}px`,
					width: `${item.areaWidth / 2}px`,
					height: `${item.areaHeight / 2}px`
				}"
				class="area"
				@click="goPage(item.link)"
			></view>
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
		return {};
	},
	computed: {
		imageRadius() {
			// let borderRadius = `${this.dataConfig.fillet.val * 2}rpx`;
			// if (this.dataConfig.fillet.type) {
			// 	borderRadius =
			// 		`${this.dataConfig.fillet.valList[0].val * 2}rpx ${this.dataConfig.fillet.valList[1].val * 2}rpx ${this.dataConfig.fillet.valList[2].val * 2}rpx ${this.dataConfig.fillet.valList[3].val * 2}rpx`;
			// }
			// return {
			// 	'border-radius': borderRadius,
			// };
		},
		hotspotWrapStyle() {
			// let windowWidth = uni.getSystemInfoSync().windowWidth;
			// let boxWidth = windowWidth * 2 - this.dataConfig.prConfig.val * 2;
			return {
				'margin-top': `${this.dataConfig.mbConfig.val}rpx`
			};
		}
	},
	methods: {
		goPage(link) {
			this.$util.JumpPath(link);
		}
	}
};
</script>

<style lang="scss" scoped>
.hotspot {
	position: relative;
	width: 100%;
	.image {
		display: block;
		width: 100%;
	}

	.area {
		position: absolute;
	}
}
</style>
