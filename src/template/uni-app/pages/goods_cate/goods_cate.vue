<template>
	<view class='productSort copy-data'>
		<view class='header acea-row row-center-wrapper'>
			<view class='acea-row row-between-wrapper input'>
				<text class='iconfont icon-sousuo'></text>
				<input type='text' placeholder='点击搜索商品信息' @confirm="searchSubmitValue" confirm-type='search' name="search"
				 placeholder-class='placeholder'></input>
			</view>
		</view>
		<view class='aside' :style="{bottom: tabbarH + 'px'}">
			<view class='item acea-row row-center-wrapper' :class='index==navActive?"on":""' v-for="(item,index) in productList"
			 :key="index" @click='tap(index,"b"+index)'><text>{{item.cate_name}}</text></view>
		</view>
		<view class='conter'>
			<scroll-view scroll-y="true" :scroll-into-view="toView" :style='"height:"+height+"rpx;"' @scroll="scroll"
			 scroll-with-animation='true'>
				<block v-for="(item,index) in productList" :key="index">
					<view class='listw' :id="'b'+index">
						<view class='title acea-row row-center-wrapper'>
							<view class='line'></view>
							<view class='name'>{{item.cate_name}}</view>
							<view class='line'></view>
						</view>
						<view class='list acea-row'>
							<block v-for="(itemn,indexn) in item.children" :key="indexn">
								<navigator hover-class='none' :url='"/pages/goods_list/index?sid="+itemn.id+"&title="+itemn.cate_name' class='item acea-row row-column row-middle'>
									<view class='picture'>
										<image :src='itemn.pic' v-if="itemn.pic"></image>
										<image src="/static/images/sort-img.png" v-else></image>
									</view>
									<view class='name line1'>{{itemn.cate_name}}</view>
								</navigator>
							</block>
						</view>
					</view>
				</block>
				<view :style='"height:"+(height-300)+"rpx;"' v-if="number<15"></view>
			</scroll-view>
		</view>
		<view class="uni-p-b-98"></view>
		<z_tabBar :pagePath="'/pages/goods_cate/goods_cate'" @tabbar-h="tabbarH = $event"></z_tabBar>
	</view>
</template>

<script>
	import z_tabBar from "@/pages/index/components/z_tabBar.vue";
	import {
		getCategoryList
	} from '@/api/store.js';
	// import z_tabBar from './components/z_tabBar';
	export default {
		components:{
			z_tabBar
		},
		data() {
			return {
				navlist: [],
				productList: [],
				navActive: 0,
				number: "",
				height: 0,
				hightArr: [],
				toView: "",
				tabbarH: 0
			}
		},
		onLoad(options) {
			this.getAllCategory();
		},
		onReady() {
			// this.$nextTick(()=>{
			// 	const query = uni.createSelectorQuery();
			// 	query.select('#tabbar').boundingClientRect(data => {
			// 		console.log(data)
			// 	}).exec();
			// });
		},
		methods: {
			infoScroll: function() {
				let that = this;
				let len = that.productList.length;
				this.number = that.productList[len - 1].children.length;
				//设置商品列表高度
				uni.getSystemInfo({
					success: function(res) {
						that.height = (res.windowHeight) * (750 / res.windowWidth) - 98;
					},
				});
				let height = 0;
				let hightArr = [];
				for (let i = 0; i < len; i++) {
					//获取元素所在位置
					let query = uni.createSelectorQuery().in(this);
					let idView = "#b" + i;
					query.select(idView).boundingClientRect();
					query.exec(function(res) {
						let top = res[0].top;
						hightArr.push(top);
						that.hightArr = hightArr
					});
				};
			},
			tap: function(index, id) {
				this.toView = id;
				this.navActive = index;
			},
			getAllCategory: function() {
				let that = this;
				getCategoryList().then(res => {
					that.productList = res.data;
					setTimeout(function(){
						that.infoScroll();
					},500)
				})
			},
			scroll: function(e) {
				let scrollTop = e.detail.scrollTop;
				let scrollArr = this.hightArr;
				for (let i = 0; i < scrollArr.length; i++) {
					if (scrollTop >= 0 && scrollTop < scrollArr[1] - scrollArr[0]) {
						this.navActive = 0
					} else if (scrollTop >= scrollArr[i] - scrollArr[0] && scrollTop < scrollArr[i + 1] - scrollArr[0]) {
						this.navActive = i
					} else if (scrollTop >= scrollArr[scrollArr.length - 1] - scrollArr[0]) {
						this.navActive = scrollArr.length - 1
					}
				}
			},
			searchSubmitValue: function(e) {
				if (this.$util.trim(e.detail.value).length > 0)
					uni.navigateTo({
						url: '/pages/goods_list/index?searchValue=' + e.detail.value
					})
				else
					return this.$util.Tips({
						title: '请填写要搜索的产品信息'
					});
			},
		}
	}
</script>

<style scoped lang="scss">
	.productSort .header {
		width: 100%;
		height: 96rpx;
		background-color: #fff;
		position: fixed;
		left: 0;
		right: 0;
		top: 0;
		z-index: 9;
		border-bottom: 1rpx solid #f5f5f5;
	}

	.productSort .header .input {
		width: 700rpx;
		height: 60rpx;
		background-color: #f5f5f5;
		border-radius: 50rpx;
		box-sizing: border-box;
		padding: 0 25rpx;
	}

	.productSort .header .input .iconfont {
		font-size: 35rpx;
		color: #555;
	}

	.productSort .header .input .placeholder {
		color: #999;
	}

	.productSort .header .input input {
		font-size: 26rpx;
		height: 100%;
		width: 597rpx;
	}

	.productSort .aside {
		position: fixed;
		width: 180rpx;
		left: 0;
		top:0;
		background-color: #f7f7f7;
		overflow-y: auto;
		overflow-x: hidden;
		height: auto;
		margin-top: 96rpx;
	}

	.productSort .aside .item {
		height: 100rpx;
		width: 100%;
		font-size: 26rpx;
		color: #424242;
	}

	.productSort .aside .item.on {
		background-color: #fff;
		border-left: 4rpx solid #fc4141;
		width: 100%;
		text-align: center;
		color: #fc4141;
		font-weight: bold;
	}

	.productSort .conter {
		margin: 96rpx 0 0 180rpx;
		padding: 0 14rpx;
		background-color: #fff;
	}

	.productSort .conter .listw {
		padding-top: 20rpx;
	}

	.productSort .conter .listw .title {
		height: 90rpx;
	}

	.productSort .conter .listw .title .line {
		width: 100rpx;
		height: 2rpx;
		background-color: #f0f0f0;
	}

	.productSort .conter .listw .title .name {
		font-size: 28rpx;
		color: #333;
		margin: 0 30rpx;
		font-weight: bold;
	}

	.productSort .conter .list {
		flex-wrap: wrap;
	}

	.productSort .conter .list .item {
		width: 177rpx;
		margin-top: 26rpx;
	}

	.productSort .conter .list .item .picture {
		width: 120rpx;
		height: 120rpx;
		border-radius: 50%;
	}

	.productSort .conter .list .item .picture image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
	}

	.productSort .conter .list .item .name {
		font-size: 24rpx;
		color: #333;
		height: 56rpx;
		line-height: 56rpx;
		width: 120rpx;
		text-align: center;
	}
</style>
