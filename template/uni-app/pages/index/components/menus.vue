<template>
	<!-- 金刚区 -->
	<view v-show="!isSortType && menus.length" :style="[boxStyle]">
		<view :style="[boxContentStyle]">
			<view class="swiper" v-if="isShowConfig">
				<swiper :interval="3000" :duration="500" :style="'height:'+navHigh+'px;'" @change='bannerfun'>
					<block>
						<swiper-item v-for="(item,indexw) in menuList" :key="indexw">
							<view class="menu" :id="'nav' + indexw" :style="[gridColumns]">
								<view class="flex-col flex-center"  
									v-for="(itemn,indexn) in item.list" :key="indexn" 
									@click="menusTap(itemn.info[1].value)">
									<view class="pictrue skeleton-radius" v-if="menuStyleConfig !== 2"
										>
										<easy-loadimage
										:image-src="itemn.img" 
										width="90rpx"
										height="90rpx"
										:borderRadius="imgStyle"></easy-loadimage>
									</view>
									<view class="fs-24" :style="[textColor]" :class="{'mt-14': menuStyleConfig == 0}" 
										v-if="menuStyleConfig !== 1">{{ itemn.info[0].value }}</view>
								</view>
							</view>
						</swiper-item>
					</block> 
				</swiper>
			</view>
			<view v-else>
				<view class="menu" :style="[gridColumns]">
					<view class="flex-col flex-center"  
						v-for="(item, index) in menus" :key="index"
						@click="menusTap(item.info[1].value)">
						<view class="pictrue skeleton-radius" v-if="menuStyleConfig !== 2"
							>
							<easy-loadimage
							:image-src="item.img" 
							width="90rpx"
							height="90rpx"
							:borderRadius="imgStyle"></easy-loadimage>
						</view>
						<view class="fs-24"  :style="[textColor]" :class="{'mt-14': menuStyleConfig == 0}" 
							v-if="menuStyleConfig !== 1">{{ item.info[0].value }}</view>
					</view>
				</view>
			</view>
			<view class="dot acea-row row-center-wrapper" v-if="menuList.length > 1 && isShowConfig">
				<view class="dot-item line_dot-item" 
				:style="{'background-color': active==index ? dotSelectColor : dotColor}"
				v-for="(item,index) in menuList"></view>
			</view>
		</view>
		<!-- <view class="nav" :style="[boxContentStyle]" >
			<scroll-view scroll-x="true" style="white-space: nowrap; display: flex" show-scrollbar="false">
				<block v-for="(item, index) in menus" :key="index">
					<view class="item" @click="menusTap(item.info[1].value)">
						<view class="pictrue skeleton-radius">
							<easy-loadimage
							:image-src="item.img" 
							width="90rpx"
							height="90rpx"
							:borderRadius="imgStyle"></easy-loadimage>
						</view>
						<view class="fs-24" :class="{'mt-14': menuStyleConfig == 0}">{{ item.info[0].value }}</view>
					</view>
				</block>
			</scroll-view>
		</view> -->
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
				navHigh: 0,
				menuList: [],
				active: 0
			};
		},
		computed: {
			rowsNum(){
				return this.dataConfig.rowsNum.tabVal
			},
			number(){
				return this.dataConfig.number.tabVal
			},
			dotColor(){
				return this.dataConfig.pointerColor.color[0].item
			},
			menus(){
				let list = this.dataConfig.menuConfig.list || [];
				let menuList = [];
				list.forEach(item=>{
					if(item.show){
						menuList.push(item);
					}
				})
				return menuList
			},
			isShowConfig(){
				return this.dataConfig.showConfig.tabVal
			},
			boxStyle(){
				return {
					padding: `${this.dataConfig.topConfig.val * 2}rpx ${this.dataConfig.prConfig.val * 2}rpx ${this.dataConfig.bottomConfig.val * 2}rpx`,
					marginTop: `${this.dataConfig.mbConfig.val * 2}rpx`,
					background: this.dataConfig.bottomBgColor.color[0].item,
				}
			},
			textColor(){
				console.log(this.dataConfig,'this.dataConfig.textColor')
				return {
					color: this.dataConfig.textColor ? this.dataConfig.textColor.color[0].item : '#333'
				}
			},
			boxContentStyle() {
				let borderRadius = `${this.dataConfig.fillet.val * 2}rpx`;
				if (this.dataConfig.fillet.type) {
					borderRadius =
						`${this.dataConfig.fillet.valList[0].val * 2}rpx ${this.dataConfig.fillet.valList[1].val * 2}rpx ${this.dataConfig.fillet.valList[3].val * 2}rpx ${this.dataConfig.fillet.valList[2].val * 2}rpx`;
				}
				return {
					borderRadius,
					background: `linear-gradient(90deg, ${this.dataConfig.bgColor.color[0].item} 0%, ${this.dataConfig.bgColor.color[1].item} 100%)`,
				};
			},
			imgStyle(){
				let borderRadius = `${this.dataConfig.filletImg.val * 2}rpx`;
				if (this.dataConfig.filletImg.type) {
					borderRadius =
						`${this.dataConfig.filletImg.valList[0].val * 2}rpx ${this.dataConfig.filletImg.valList[1].val * 2}rpx ${this.dataConfig.filletImg.valList[3].val * 2}rpx ${this.dataConfig.filletImg.valList[2].val * 2}rpx`;
				}
				return borderRadius
			},
			//分几行展示，一行展示多少个
			gridColumns() {
				if (this.dataConfig.number.tabVal == 0) {
					return {
						gridTemplateColumns: 'repeat(3, 1fr)'
					}
				} else if (this.dataConfig.number.tabVal == 1) {
					return {
						gridTemplateColumns: 'repeat(4, 1fr)'
					}
				} else {
					return {
						gridTemplateColumns: 'repeat(5, 1fr)'
					}
				}
			},
			menuStyleConfig(){
				return this.dataConfig.menuStyleConfig.tabVal
			},
			dotColor(){
				return this.dataConfig.pointerBgColor.color[0].item 
			},
			dotSelectColor(){
				return this.dataConfig.pointerColor.color[0].item 
			}
		},
		mounted() {
			this.getSwiperCount();
			this.$nextTick(() => {
				if (this.menuList.length&&this.isShowConfig) {
					let that = this
					// #ifdef H5
					that.menuHeight()
					// #endif
					// #ifndef H5
					setTimeout(() => {
						that.menuHeight()
					},150)
					// #endif
				}
			})
		},
		methods: {
			getSwiperCount(){
				/* rowsNum 显示行数  0: 1行  1: 2行 2: 3行 3 4行 */
				/* number  单行显示  0: 3个  1: 4个 2: 5个 */
				this.pageNum((this.rowsNum + 1) * (this.number + 3));
			},
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
				this.$util.JumpPath(url);
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
				height: 6rpx;
				border-radius: 3rpx;
			}
		}
	}
	.nav {
		.item {
			margin-right: 42rpx;
			width: 90rpx;
			text-align: center;
			font-size: 24rpx;
			display: inline-block;
		}
	}
	.menu {
		display: grid;
		grid-template-rows: auto;
		grid-row-gap: 24rpx;
		width: 100%;
		padding: 20rpx 0;
	}
	.swiper {
		z-index: 20;
		position: relative;
		overflow: hidden;
		swiper,
		.swiper-item {
			width: 100%;
			display: block;
		}
	}
</style>
