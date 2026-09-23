<template>
  <!-- 浏览记录 -->
	<view>
		<view class="record" :style="colorStyle" v-if="visitList.length">
			<view class="nav acea-row row-between-wrapper">
				<view class="left">{{$t(`共`)}} <text class="num">{{count}}</text>{{$t(`件商品`)}}</view>
				<view class="font-num" v-if="!isShowChecked" @click="switchTap">{{$t(`管理`)}}</view>
				<view v-else @click="switchTap">{{$t(`取消`)}}</view>
			</view>
			<view class="list">
				<checkbox-group @change="checkboxChange">
					<view class="item" v-for="(item,index) in visitList" :key="index">
						<view class="title">
							<checkbox v-if="isShowChecked" :value="item.time" :checked="item.checked" backgroundColor="#ffffff" activeBackgroundColor="var(--view-theme, #E93323)" activeBorderColor="var(--view-theme, #E93323)" iconColor="#ffffff" />
							<text>{{item.time}}</text>
						</view>
						<checkbox-group @change="(e)=>{picCheckbox(e,index)}">
							<view class="picList acea-row row-middle">
								<view class="picTxt" v-for="(j,jindex) in item.picList" :key="jindex" @click.stop="goDetails(j)">
									<view class="pictrue">
										<image :src="j.image"></image>
                      <!-- <lazyLoad :src="j.image" width="100%" height="100%"></lazyLoad> -->
										<checkbox v-if="isShowChecked" :value="(j.id).toString()" :checked="j.checked" class="checkbox" activeBackgroundColor="var(--view-theme, #E93323)" activeBorderColor="var(--view-theme, #E93323)" iconColor="#ffffff" />
										<view class="masks acea-row row-center-wrapper" v-if="!isShowChecked && j.stock<=0">
											<view class="bg">
												<view>{{$t(`已售罄`)}}</view>
											</view>
										</view>
										<view class="masks acea-row row-center-wrapper" v-if="!isShowChecked && !j.is_show">
											<view class="bg">
												<view>{{$t(`已下架`)}}</view>
											</view>
										</view>
									</view>
									<view class="money">¥<text class="num">{{j.product_price}}</text></view>
								</view>
							</view>
						</checkbox-group>
					</view>
				</checkbox-group>
				<view class='loadingicon acea-row row-center-wrapper'>
					<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
				</view>
			</view>
			<view class="footer acea-row row-between-wrapper" v-if="isShowChecked">
				<checkbox-group @change="checkboxAllChange">
					<checkbox value="all" :checked="isAllSelect" backgroundColor="#ffffff" activeBackgroundColor="var(--view-theme, #E93323)" activeBorderColor="var(--view-theme, #E93323)" iconColor="#ffffff" />
					<text class='checkAll'>{{$t(`全选`)}}</text>
				</checkbox-group>
				<view class="acea-row row-middle">
					<view class="bnt acea-row row-center-wrapper" @click="collect">{{$t(`收藏`)}}</view>
					<view class="bnt on acea-row row-center-wrapper" @click="del">{{$t(`删除`)}}</view>
				</view>
			</view>
		</view>
		<view class='noCommodity' v-else-if="!visitList.length && page == 2">
			<view class='pictrue'>
				<image :src="imgHost + '/statics/images/no-thing.png'"></image>
			</view>
			<view class="acea-row row-center-wrapper tip">{{$t(`暂无数据`)}}</view>
			<recommend :hostProduct="hostProduct"></recommend>
		</view>
	</view>
</template>
<script setup>
import { ref } from 'vue';
import { onLoad, onShow, onReachBottom, onPageScroll } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
	getVisitList,
	getProductHot,
	deleteVisitList,
	collectAll
} from '@/api/store.js';
import {
	toLogin
} from '@/libs/login.js';
import recommend from '@/components/recommend';
import home from '@/components/home';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
import { HTTP_REQUEST_URL } from '@/config/app';

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const isShowChecked = ref(0);
const count = ref(0);
const times = ref([]);
const isAllSelect = ref(false);
const hostProduct = ref([]);
const loadTitle = ref(i18n.t(`加载更多`));
const loading = ref(false);
const loadend = ref(false);
const visitList = ref([]);
const limit = ref(21);
const page = ref(1);
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const hotScroll = ref(false);
const hotPage = ref(1);
const hotLimit = ref(10);
const isItemAll = ref([]);
const imgHost = HTTP_REQUEST_URL;

// ---------- 生命周期 ----------
onLoad(() => {
	if (isLogin.value) {
		loadend.value = false;
		page.value = 1;
		visitList.value = [];
		get_user_visit_list();
		get_host_product();
	} else {
		toLogin();
	}
});
onShow(() => {
	uni.removeStorageSync('form_type_cart');
	times.value = [];
	loadend.value = false;
	page.value = 1;
	visitList.value = [];
	get_user_visit_list();
});
onReachBottom(() => {
	if (visitList.value.length) {
		get_user_visit_list();
	} else {
		get_host_product();
	}
});
// 滚动监听
onPageScroll((e) => {
	// 传入scrollTop值并触发所有easy-loadimage组件下的滚动监听事件
	uni.$emit('scroll');
});

// ---------- methods ----------
function goDetails(item) {
	if (isShowChecked.value || !item.is_show) return false;
	uni.navigateTo({
		url: '/pages/goods_details/index?id=' + item.product_id
	})
}
function switchTap() {
	isShowChecked.value = !isShowChecked.value;
}
function collect() {
	let ids = [];
	visitList.value.forEach(item => {
		item.picList.forEach(j => {
			if (j.checked) {
				ids.push(j.product_id)
			}
		})
	})
	if (!ids.length) {
		return util.Tips({
			title: i18n.t(`请选择收藏商品`)
		});
	}
	let str = ids.join(',');
	collectAll(str).then(res => {
		return util.Tips({
			title: res.msg
		});
	})
}
function del() {
	let ids = [];
	visitList.value.forEach(item => {
		item.picList.forEach(j => {
			if (j.checked) {
				ids.push(j.product_id)
			}
		})
	})
	if (!ids.length) {
		return util.Tips({
			title: i18n.t(`请选择删除商品`)
		});
	}
	deleteVisitList({ ids }).then(res => {
		times.value = [];
		loadend.value = false;
		page.value = 1;
		visitList.value = [];
		get_user_visit_list();
		return util.Tips({
			title: res.msg
		});
	})
}
function picCheckbox(event, index) {
	let picTime = event.detail.value;
	visitList.value[index].picList.forEach(j => {
		if (picTime.indexOf(j.id + '') !== -1) {
			j.checked = true;
		} else {
			j.checked = false;
		}
	})
	if (visitList.value[index].picList.length == picTime.length) {
		visitList.value[index].checked = true;
	} else {
		visitList.value[index].checked = false;
	}
	let visitObj = [];
	visitList.value.forEach(item => {
		if (item.checked) {
			visitObj.push(item.time)
		} else {
			if (visitObj.indexOf(item.time) !== -1) {
				visitObj.remove(item.time);
			}
		}
	})
	if (visitObj.length == visitList.value.length) {
		isAllSelect.value = true;
	} else {
		isAllSelect.value = false;
	}
}
function checkboxChange(event) {
	let timeList = event.detail.value;
	isItemAll.value = timeList;
	visitList.value.forEach((item, index) => {
		if (timeList.indexOf(item.time) !== -1) {
			item.checked = true;
		} else {
			item.checked = false;
		}
		item.picList.forEach(j => {
			if (item.checked) {
				j.checked = true;
			} else {
				j.checked = false;
			}
		})
	})
	if (timeList.length === visitList.value.length) {
		isAllSelect.value = true;
	} else {
		isAllSelect.value = false;
	}
}
function forGoods(val) {
	if (!visitList.value.length) return
	visitList.value.forEach((item) => {
		if (val) {
			item.checked = true;
		} else {
			item.checked = false;
		}
		item.picList.forEach(j => {
			if (val) {
				j.checked = true;
			} else {
				j.checked = false;
			}
		})
	})
}
function checkboxAllChange(event) {
	let value = event.detail.value;
	if (value.length) {
		isAllSelect.value = true;
		forGoods(1)
	} else {
		isAllSelect.value = false;
		forGoods(0)
	}
}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e
}
/**
 * 获取记录产品
 */
function get_user_visit_list() {
	if (loading.value) return;
	if (loadend.value) return;
	loading.value = true;
	loadTitle.value = "";
	getVisitList({
		page: page.value,
		limit: limit.value
	}).then(res => {
		count.value = res.data.count;
		for (let i = 0; i < res.data.time.length; i++) {
			if (times.value.indexOf(res.data.time[i]) == -1) {
				times.value.push(res.data.time[i])
				visitList.value.push({
					time: res.data.time[i],
					picList: []
				})
			}
		}
		for (let x = 0; x < times.value.length; x++) {
			visitList.value[x].checked = isAllSelect.value ? true : false;
			for (let j = 0; j < res.data.list.length; j++) {
				if (times.value[x] === res.data.list[j].time_key) {
					if (isAllSelect.value) {
						res.data.list[j].checked = true;
					} else {
						res.data.list[j].checked = false;
					}
					visitList.value[x].picList.push(res.data.list[j])
				}
			}
		}
		let loadendNew = res.data.list.length < limit.value;
		loadend.value = loadendNew;
		loadTitle.value = loadendNew ? i18n.t(`没有更多内容啦~`) : i18n.t(`加载更多`);
		page.value = page.value + 1;
		loading.value = false;
	}).catch(err => {
		loading.value = false;
		loadTitle.value = i18n.t(`加载更多`);
	});
}
/**
 * 获取我的推荐
 */
function get_host_product() {
	if (hotScroll.value) return
	getProductHot(
		hotPage.value,
		hotLimit.value,
	).then(res => {
		hotPage.value++
		hotScroll.value = res.data.length < hotLimit.value
		hostProduct.value = hostProduct.value.concat(res.data)
	});
}
</script>
<style lang="scss">
	page {
		background-color: #fff;
	}
	
	.noCommodity .pictrue{
		width: 414rpx;
		height: 304rpx;
		margin: 30rpx auto 0 auto;
		.tip{
			color: #bbb;
			font-size: 25rpx;
		}
	}

	.record .pictrue :deep(checkbox .uni-checkbox-input){
		background-color: rgba(0, 0, 0, 0.16);
	}
	
	.record .pictrue :deep(checkbox .wx-checkbox-input){
		background-color: rgba(0, 0, 0, 0.16);
	}

	.record {
		.footer {
			box-sizing: border-box;
			padding: 0 30rpx;
			width: 100%;
			height: 96rpx;
			box-shadow: 0px -4px 20px 0px rgba(0, 0, 0, 0.06);
			background-color: #fff;
			position: fixed;
			bottom: 0;
			z-index: 30;
			height: calc(96rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
			height: calc(96rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
			padding-bottom: constant(safe-area-inset-bottom); ///兼容 IOS<11.2/
			padding-bottom: env(safe-area-inset-bottom); ///兼容 IOS>11.2/

			.bnt {
				width: 160rpx;
				height: 60rpx;
				border-radius: 30rpx;
				border: 1rpx solid #ccc;
				color: #666666;

				&.on {
					border: 1rpx solid var(--view-theme);
					margin-left: 16rpx;
					color: var(--view-theme);
				}
			}
		}

		.nav {
			border-bottom: 1rpx solid #eee;
			color: #999999;
			font-size: 28rpx;
			height: 74rpx;
			padding: 0 30rpx;

			.left {
				color: #333;

				.num {
					color: var(--view-theme);
					margin: 0 10rpx;
				}
			}
		}

		.list {
			padding-top: 32rpx;
			padding-bottom: 96rpx;

			.item {
				.title {
					padding: 0 30rpx;
					margin-bottom: 34rpx;
					font-size: 34rpx;
					font-weight: 600;
				}

				.picList {
					padding: 0 30rpx 0 12rpx;

					.picTxt {
						margin-left: 18rpx;
						margin-bottom: 48rpx;

						.pictrue {
							width: 218rpx;
							height: 218rpx;
							border-radius: 10rpx;
							position: relative;

							image {
								width: 100%;
								height: 100%;
								border-radius: 10rpx;
							}
							
							.masks {
								position: absolute;
								top: 0;
								left: 0;
								right: 0;
								bottom: 0;
								background: rgba(0, 0, 0, 0.2);
								border-radius: 10rpx;
							
								.bg {
									width: 110rpx;
									height: 110rpx;
									background: #000000;
									opacity: 0.6;
									color: #fff;
									font-size: 22rpx;
									border-radius: 50%;
									// padding: 22rpx 0;
									line-height: 110rpx;
									text-align: center;
								}
							}

							.checkbox {
								position: absolute;
								right: 10rpx;
								top: 14rpx;
							}
						}

						.money {
							font-size: 24rpx;
							color: var(--view-priceColor);
							font-weight: 600;
							margin-top: 15rpx;

							.num {
								font-size: 32rpx;
								margin-left: 6rpx;
							}
						}
					}
				}
			}
		}
	}
</style>
