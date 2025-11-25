<template>
	<!-- 富文本 -->
	<view v-show="!isSortType" :style="[richTextWrapStyle]">
		<view class="richText" v-if="description" :style="[richTextStyle]">
			<!-- #ifndef APP-PLUS -->
			<jyf-parser :html="description" ref="article" :tag-style="tagStyle"></jyf-parser>
			<!-- #endif -->
			<!-- #ifdef APP-PLUS -->
			<view v-html="description"></view>
			<!-- #endif -->
		</view>
	</view>
</template>


<script>
	import parser from "@/components/jyf-parser/jyf-parser";
	export default {
		name: 'richText',
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
		components: {
			"jyf-parser": parser
		},
		data() {
			return {
				tagStyle: {
					img: 'width:100%;display:block;',
					table: 'width:100%',
					video: 'width:100%'
				},
				bgColor: '',
				lrConfig: 0,
				udConfig: 0
			};
		},
		computed: {
			description() {
				let description = this.dataConfig.richText.val;
				if (description) {
					description = description.replace(
						/<img/gi,
						'<img style="max-width:100%;height:auto;float:left;display:block" '
					);
					description = description.replace(
						/<video/gi,
						'<video style="width:100%;height:auto;display:block" '
					);
				}
				return description;
			},
			richTextStyle() {
				let borderRadius = `${this.dataConfig.fillet.val * 2}rpx`;
				if (this.dataConfig.fillet.type) {
					borderRadius =
						`${this.dataConfig.fillet.valList[0].val * 2}rpx ${this.dataConfig.fillet.valList[1].val * 2}rpx ${this.dataConfig.fillet.valList[3].val * 2}rpx ${this.dataConfig.fillet.valList[2].val * 2}rpx`;
				}
				return {
					'border-radius': borderRadius,
					'background': this.dataConfig.bgColor.color[0].item,
				};
			},
			richTextWrapStyle() {
				return {
					'padding': `${this.dataConfig.topConfig.val*2}rpx ${this.dataConfig.lrConfig.val*2}rpx ${this.dataConfig.bottomConfig.val*2}rpx`,
					'margin-top': `${this.dataConfig.udConfig.val*2}rpx`,
					'background': this.dataConfig.bottomBgColor.color[0].item,
				};
			},
		},
	}
</script>

<style lang="scss">
	.richText {
		padding: 30rpx;
		background-color: #fff;

		// margin: 0 20rpx;
		// border-radius: 24rpx;
		&::after {
			content: "";
			display: table;
			clear: both;
		}
	}

	/deep/uni-video {
		width: 100% !important;
	}

	/deep/video {
		width: 100% !important;
	}
</style>