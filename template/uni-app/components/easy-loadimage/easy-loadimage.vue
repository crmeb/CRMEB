<template>
	<view class="easy-loadimage" :style="[boxStyle]" :id="uid">
		<image class="origin-img" :src="imageSrc" mode="aspectFill" 
			v-if="loadImg&&!isLoadError" 
			v-show="showImg"
			:style="[imgStyle]"
			:class="{'no-transition':!openTransition,'show-transition':showTransition&&openTransition}"
			@load="handleImgLoad" @error="handleImgError">
		</image>
		<image class="border-img" :src="borderSrc" mode="aspectFill"
			v-if="loadImg&&!isLoadError&&borderSrc"
			v-show="showImg"
			:style="[imgStyle]"
			:class="{'no-transition':!openTransition,'show-transition':showTransition&&openTransition}">
		</image>
		<view class="loadfail-img" v-else-if="isLoadError"></view>
		<view :class="['loading-img','spin-circle',loadingMode]" v-show="!showImg&&!isLoadError"></view>
	</view>
</template>
<script>
	import {Throttle} from '@/utils/validate.js';

	// 生成全局唯一id
	function generateUUID() {
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			let r = Math.random() * 16 | 0,
				v = c == 'x' ? r : (r & 0x3 | 0x8);
			return v.toString(16);
		})
	}
	export default {
		props: {
			imageSrc: {
				type: String,
				default: ""
			},
			borderSrc: {
				type: String,
				default: ""
			},
			mode: {
				type: String,
				default: ""
			},
			loadingMode: {
				type: String,
				default: 'looming-gray'
			},
			openTransition: {
				type: Boolean,
				default: true,
			},
			viewHeight: {
				type: Number,
				default () {
					return uni.getSystemInfoSync().windowHeight;
				}
			},
			width:{
				type: String,
				default: ''
			},
			height:{
				type: String,
				default: ''
			},
			borderRadius:{
				type: String,
				default: ''
			}
		},
		data() {
			const that = this;
			return {
				// uid:'',
				uid: 'uid-' + generateUUID(),
				loadImg: false,
				showImg: false,
				isLoadError: false,
				borderLoaded: 0,
				showTransition: false,
				scrollFn: Throttle(function() {
					// 加载img时才执行滚动监听判断是否可加载
					if (that.loadImg || that.isLoadError) return;
					const id = that.uid
					const query = uni.createSelectorQuery().in(that);
					query.select('#' + id).boundingClientRect(data => {
						if (!data) return;
						if (data.top - that.viewHeight < 0) {
							that.loadImg = !!that.imageSrc;
							that.isLoadError = !that.loadImg;
						}
					}).exec()
				}, 200)
			}
		},
		computed:{
			boxStyle(){
				return {
					width: this.width,
					height: this.height,
					borderRadius: this.borderRadius
				}
			},
			imgStyle(){
				return {
					borderRadius: this.borderRadius
				}
			}
		},
		methods: {
			init() {
				this.$nextTick(this.onScroll)
			},
			handleBorderLoad() {
				this.borderLoaded = 1;
			},
			handleBorderError() {
				this.borderLoaded = 2;
			},
			handleImgLoad(e) {
				this.showImg = true;
				// this.$nextTick(function(){
				//     this.showTransition = true
				// })
				setTimeout(() => {
					this.showTransition = true
				}, 50)
			},
			handleImgError(e) {
				this.isLoadError = true;
			},
			onScroll() {
				this.scrollFn();
			},
		},
		mounted() {
			this.init()
			uni.$on('scroll', this.scrollFn);
			this.onScroll()
		},
		beforeDestroy() {
			uni.$off('scroll', this.scrollFn);
		}
	}
</script>

<style scoped>
	.easy-loadimage {
		position: relative;

	}

	.border-img {
		position: absolute;
		width: 100%;
		height: 100%;
		/* max-height: 360rpx; */
		top: 0;
		left: 0;
	}

	/* 官方优化图片tips */
	image {
		will-change: transform
	}

	/* 渐变过渡效果处理 */
	image.origin-img {
		width: 100%;
		height: 100%;
		opacity: 0.3;
		/* max-height: 360rpx; */
	}

	image.origin-img.show-transition {
		transition: opacity .5s;
		opacity: 1;
	}

	image.origin-img.no-transition {
		opacity: 1;
	}

	/* 渐变过渡效果处理 */
	image.border-img {
		width: 100%;
		height: 100%;
		opacity: 0.3;
		/* max-height: 360rpx; */
	}

	image.border-img.show-transition {
		transition: opacity .5s;
		opacity: 1;
	}

	image.border-img.no-transition {
		opacity: 1;
	}

	/* 加载失败、加载中的占位图样式控制 */
	.loadfail-img {
		height: 100%;
		background: url('~@/static/easy-loadimage/loadfail.png') no-repeat center;
		background-size: 50%;
	}

	.loading-img {
		height: 100%;
	}

	/* 转圈 */
	.spin-circle {
		background: url('~@/static/easy-loadimage/loading.png') no-repeat center;
		background-size: 60%;
	}

	/* 动态灰色若隐若现 */
	.looming-gray {
		animation: looming-gray 1s infinite linear;
		background-color: #e3e3e3;
		border-radius: 12rpx;
	}

	@keyframes looming-gray {
		0% {
			background-color: #e3e3e3aa;
		}

		50% {
			background-color: #e3e3e3;
		}

		100% {
			background-color: #e3e3e3aa;
		}
	}

	/* 骨架屏1 */
	.skeleton-1 {
		background-color: #e3e3e3;
		background-image: linear-gradient(100deg, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0) 80%);
		background-size: 100rpx 100%;
		background-repeat: repeat-y;
		background-position: 0 0;
		animation: skeleton-1 .6s infinite;
	}

	@keyframes skeleton-1 {
		to {
			background-position: 200% 0;
		}
	}

	/* 骨架屏2 */
	.skeleton-2 {
		background-image: linear-gradient(-90deg, #fefefe 0%, #e6e6e6 50%, #fefefe 100%);
		background-size: 400% 400%;
		background-position: 0 0;
		animation: skeleton-2 1.2s ease-in-out infinite;
	}

	@keyframes skeleton-2 {
		to {
			background-position: -135% 0;
		}
	}
</style>