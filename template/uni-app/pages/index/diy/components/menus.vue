<template>
	<view v-show="!isSortType && menus.length" :class="bgStyle?'borderRadius15':''" :style="{background:bgColor,margin:'0 '+prConfig*2+'rpx',marginTop:mbConfig*2+'rpx'}">
		<view v-if="isMany">
			<view class="swiper">
				<swiper :interval="interval" :duration="duration" :style="'height:'+(navHigh*2+17)+'rpx;'" @change='bannerfun'>
					<block>
						<swiper-item v-for="(item,indexw) in menuList" :key="indexw">
							<view class="nav acea-row" :id="'nav' + indexw">
								<view :style="'color:' + titleColor" class="item" :class="number===1?'four':number===2?'five':''" v-for="(itemn,indexn) in item.list" :key="indexn" @click="menusTap(itemn.info[1].value)">
									<view class="pictrue skeleton-radius" :class="menuStyle?'':'on'">
										<image :src="itemn.img" mode="aspectFill"></image>
									</view>
									<view class="menu-txt">{{ $t(itemn.info[0].value) }}</view>
								</view>
							</view>
						</swiper-item>
					</block>
				</swiper>
			</view>
			<view class="dot acea-row row-center-wrapper" v-if="docConfig<2">
				<view class="dot-item" :class="{ 'line_dot-item': docConfig === 0,'': docConfig === 1}" :style="active==index?'background:'+ dotColor:''" v-for="(item,index) in menuList"></view>
			</view>
		</view>
		<view class="nav oneNav" v-else>
			<scroll-view scroll-x="true" style="white-space: nowrap; display: flex" show-scrollbar="false">
				<block v-for="(item, index) in menus" :key="index">
					<view class="item" :style="'color:' + titleColor" @click="menusTap(item.info[1].value)">
						<view class="pictrue skeleton-radius" :class="menuStyle?'':'on'">
							<image :src="item.img" mode="aspectFill"></image>
						</view>
						<view class="menu-txt">{{ $t(item.info[0].value) }}</view>
					</view>
				</block>
			</scroll-view>
		</view>
	</view>
</template>

<script>
	export default {
		name: 'menus',
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
			return {
				interval: 3000,
				duration: 500,
				menus: this.dataConfig.menuConfig.list || [],
				titleColor: this.dataConfig.titleColor.color[0].item,
				mbConfig: this.dataConfig.mbConfig.val,
				rowsNum: this.dataConfig.rowsNum.type,
				number: this.dataConfig.number.type,
				isMany: this.dataConfig.tabConfig.tabVal,
				menuStyle: this.dataConfig.menuStyle.type,
				docConfig: this.dataConfig.pointerStyle.type,
				dotColor: this.dataConfig.pointerColor.color[0].item,
				bgColor: this.dataConfig.bgColor.color[0].item,
				bgStyle: this.dataConfig.bgStyle.type,
				prConfig: this.dataConfig.prConfig.val,
				navHigh: 0,
				menuList: [],
				active: 0
			};
		},
		created() {},
		mounted() {
			if (this.rowsNum === 0) {
				if (this.number === 0) {
					this.pageNum(6)
				} else if (this.number === 1) {
					this.pageNum(8)
				} else {
					this.pageNum(10)
				}
			} else if (this.rowsNum === 1) {
				if (this.number === 0) {
					this.pageNum(9)
				} else if (this.number === 1) {
					this.pageNum(12)
				} else {
					this.pageNum(15)
				}
			} else {
				if (this.number === 0) {
					this.pageNum(12)
				} else if (this.number === 1) {
					this.pageNum(16)
				} else {
					this.pageNum(20)
				}
			}
			this.$nextTick(() => {
				if (this.menuList.length&&this.isMany) {
					let that = this
					// #ifdef H5
					that.menuHeight()
					// #endif
					// #ifndef H5
					setTimeout(() => {
						that.menuHeight()
					},100)
					// #endif
				}
			})
		},
		methods: {
			bannerfun(e) {
				this.active = e.detail.current;
			},
			menuHeight(){
				let that = this;
				const query = uni.createSelectorQuery().in(this);
				query.select('#nav0').boundingClientRect(data => {
					that.navHigh = data.height;
				}).exec();
			},
			pageNum(num) {
				let count = Math.ceil(this.menus.length / num);
				let goodArray = new Array();
				for (let i = 0; i < count; i++) {
					let list = this.menus.slice(i * num, i * num + num);
					if (list.length)
						goodArray.push({
							list: list
						});
				}
				this.$set(this, 'menuList', goodArray);
			},
			menusTap(url) {
				if (url.indexOf("http") != -1) {
					// #ifdef H5
					location.href = url
					// #endif
					// #ifdef MP || APP-PLUS
					uni.navigateTo({
						url: `/pages/annex/web_view/index?url=${url}`
					});
					// #endif
				} else {
					if (['/pages/goods_cate/goods_cate', '/pages/order_addcart/order_addcart', '/pages/user/index']
						.indexOf(url) == -1) {
						uni.navigateTo({
							url: url
						})
					} else {
						uni.reLaunch({
							url: url
						})
					}
				}
			}
		}
	};
</script>

<style lang="scss">
	.dot {
		width: 100%;
		padding-bottom: 20rpx;
	
		.instruct {
			width: 50rpx;
			height: 36rpx;
			line-height: 36rpx;
			background-color: rgba(0, 0, 0, 0.8);
			color: #fff;
			border-radius: 16rpx;
			font-size: 24rpx;
			text-align: center;
		}
	
		.dot-item {
			width: 10rpx;
			height: 10rpx;
			background: rgba(0, 0, 0, .4);
			border-radius: 50%;
			margin: 0 4px;
	
			&.line_dot-item {
				width: 20rpx;
				height: 5rpx;
				border-radius: 3rpx;
			}
		}
	}
	.nav {
		&.oneNav{
			padding-bottom: 25rpx;
		}
		.item {
			margin-top: 30rpx;
			width: 160rpx;
			text-align: center;
			font-size: 24rpx;
			display: inline-block;

			.pictrue {
				width: 90rpx;
				height: 90rpx;
				margin: 0 auto;

				image {
					width: 100%;
					height: 100%;
					border-radius: 50%;
				}

				&.on {
					image {
						border-radius: 0;
					}
				}
			}

			.menu-txt {
				margin-top: 15rpx;
			}
		}
	}

	.swiper {
		z-index: 20;
		position: relative;
		overflow: hidden;
		.nav {
			.item {
				width: 33.3333%;

				&.four {
					width: 25%;
				}

				&.five {
					width: 20%;
				}
			}
		}

		swiper,
		.swiper-item {
			width: 100%;
			display: block;
		}
	}
</style>
