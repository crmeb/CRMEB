<template>
	<view class="txtPic">
		<view class="ueditor acea-row row-center-wrapper" v-if="isIframe && !description">
			<text class="iconfont icon-icon_im_keyboard"></text>
		</view>
		<jyf-parser :html="description" ref="article" :tag-style="tagStyle" v-if="description && isShow && !isIframe"></jyf-parser>
		<jyf-parser :html="description" ref="article" :tag-style="tagStyle" v-if="description && isIframe"></jyf-parser>
	</view>
</template>

<script>
	let app = getApp();
	import parser from "@/components/jyf-parser/jyf-parser";
	import { goPage } from '@/libs/order.js';
	export default {
		name: 'picTxt',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			}
		},
		components: {
			"jyf-parser": parser
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if(nVal){
						this.description = nVal.richText.val;
						this.isShow = nVal.isShow.val;
					}
				}
			}
		},
		data() {
			return {
				description: '',
				name:this.$options.name,
				isIframe:app.globalData.isIframe,
				isShow:true,
				tagStyle: {
					img: 'width:100%;display:block;',
					table: 'width:100%',
					video: 'width: 100% !important;'
				}
			};
		},
		created() {
		},
		mounted() {},
		methods: {
		}
	}
</script>

<style scoped lang="scss">
	.txtPic{
		.ueditor{
			width: 690rpx;
			height: 300rpx;
			border-radius: 14rpx;
			margin: 26rpx auto 0 auto;
			background-color: $uni-bg-color;
			text-align: center;
			line-height: 300rpx;
			.iconfont{
				font-size: 50rpx;
			}
		}
	}
</style>