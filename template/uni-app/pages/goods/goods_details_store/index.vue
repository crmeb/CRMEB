<template>
	<div :style="colorStyle">
		<div class="storeBox" ref="container">
			<div class="storeBox-box" v-for="(item, index) in storeList" :key="index" @click.stop="checked(item)">
				<div class="store-img"><img :src="item.image" lazy-load="true" /></div>
				<div class="store-cent-left">
					<div class="store-name">{{ item.name }}</div>
					<div class="store-address line1">
						{{ item.address }}{{ ", " + item.detailed_address }}
					</div>
				</div>
				<div class="row-right">
					<div>
						<!-- #ifdef H5 -->
						<a class="store-phone acea-row row-center-wrapper" :href="'tel:' + item.phone"><span
								class="iconfont icon-dadianhua01"></span></a>
						<!-- #endif -->
						<!-- #ifdef MP || APP-PLUS -->
						<view class="store-phone acea-row row-center-wrapper" @click.stop="call(item.phone)"><text
								class="iconfont icon-dadianhua01"></text></view>
						<!-- #endif -->
					</div>
					<div class="store-distance" @click.stop="showMaoLocation(item)">
						<span class="addressTxt" v-if="item.range">{{ item.range }}{{$t(`千米距离`)}}</span>
						<span class="addressTxt" v-else>{{$t(`查看地图`)}}</span>
						<span class="iconfont icon-youjian"></span>
					</div>
				</div>
			</div>
			<Loading :loaded="loaded" :loading="loading"></Loading>
		</div>
	</div>
</template>

<script>
	import Loading from "@/components/Loading";
	import {
		storeListApi
	} from "@/api/store";
	import {
		isWeixin
	} from "@/utils/index";
	// #ifdef H5
	import {
		wechatEvevt,
		wxShowLocation
	} from "@/libs/wechat";
	// #endif
  import colors from "@/mixins/color";
	import {
		mapGetters
	} from "vuex";
	const LONGITUDE = "user_longitude";
	const LATITUDE = "user_latitude";
	const MAPKEY = "mapKey";
	export default {
		name: "storeList",
		components: {
			Loading
		},
		mixins: [colors],
		data() {
			return {
				page: 1,
				limit: 20,
				loaded: false,
				loading: false,
				storeList: [],
				system_store: {},
				user_latitude: 0,
				user_longitude: 0
			};
		},
		onLoad() {
			try {
				this.user_latitude = uni.getStorageSync('user_latitude');
				this.user_longitude = uni.getStorageSync('user_longitude');
			} catch (e) {}
		},
		mounted() {
			if (this.user_latitude && this.user_longitude) {
				this.getList();
			} else {
				this.selfLocation();
			}
		},
		methods: {
			call(phone) {
				uni.makePhoneCall({
					phoneNumber: phone,
				});
			},
			selfLocation() {
				let self = this
				// #ifdef H5
				if (self.$wechat.isWeixin()) {
					self.$wechat.location().then(res => {
						this.user_latitude = res.latitude;
						this.user_longitude = res.longitude;
						uni.setStorageSync('user_latitude', res.latitude);
						uni.setStorageSync('user_longitude', res.longitude);
						self.getList();
					})
				} else {
					// #endif	
					uni.getLocation({
						type: 'wgs84',
						success: (res) => {
							try {
								this.user_latitude = res.latitude;
								this.user_longitude = res.longitude;
								uni.setStorageSync('user_latitude', res.latitude);
								uni.setStorageSync('user_longitude', res.longitude);
							} catch {}
							self.getList();
						},
						complete: function() {
							self.getList();
						}
					});
					// #ifdef H5	
				}
				// #endif
			},
			showMaoLocation(e) {
				let self = this;
				// #ifdef H5
				if (self.$wechat.isWeixin()) {
					self.$wechat.seeLocation({
						latitude: Number(e.latitude),
						longitude: Number(e.longitude)
					}).then(res => {
					})
				} else {
					// #endif	
					uni.openLocation({
						latitude: Number(e.latitude),
						longitude: Number(e.longitude),
						name: e.name,
						address: `${e.address}-${e.detailed_address}`,
						success: function() {
							Number
						}
					});
					// #ifdef H5	
				}
				// #endif
			},
			// 选中门店
			checked(e) {

				uni.$emit("handClick", {
					address: e
				});
				uni.navigateBack();
			},
			// 获取门店列表数据
			getList: function() {
				if (this.loading || this.loaded) return;
				this.loading = true;
				let data = {
					latitude: this.user_latitude || "", //纬度
					longitude: this.user_longitude || "", //经度
					page: this.page,
					limit: this.limit
				};
				storeListApi(data)
					.then(res => {
						this.loading = false;
						this.loaded = res.data.list.length < this.limit;
						this.storeList.push.apply(this.storeList, res.data.list.list);
						this.page = this.page + 1;
					})
					.catch(err => {
						this.$util.Tips({
							title: err
						})
					});
			}
		},
		onReachBottom() {
			this.getList();
		}
	};
</script>

<style lang="scss">
	.geoPage {
		position: fixed;
		width: 100%;
		height: 100%;
		top: 0;
		z-index: 10000;
	}

	.storeBox {
		width: 100%;
		background-color: #fff;
		padding: 0 30rpx;
	}

	.storeBox-box {
		width: 100%;
		height: auto;
		display: flex;
		align-items: center;
		padding: 23rpx 0;
		justify-content: space-between;
		border-bottom: 1px solid #eee;
	}

	.store-cent {
		display: flex;
		align-items: center;
		width: 80%;
	}

	.store-cent-left {
		width: 45%;
	}

	.store-img {
		width: 120rpx;
		height: 120rpx;
		border-radius: 6rpx;
		margin-right: 22rpx;
	}

	.store-img img {
		width: 100%;
		height: 100%;
	}

	.store-name {
		color: #282828;
		font-size: 30rpx;
		margin-bottom: 22rpx;
		font-weight: 800;
	}

	.store-address {
		color: #666666;
		font-size: 24rpx;
	}

	.store-phone {
		width: 50rpx;
		height: 50rpx;
		color: #fff;
		border-radius: 50%;
		background-color: var(--view-theme);
		margin-bottom: 22rpx;
		text-decoration: none;

		.icon-dadianhua01 {
			font-size: 22rpx;
		}
	}

	.store-distance {
		font-size: 22rpx;
		color: var(--view-theme);
	}

	.iconfont {
		font-size: 20rpx;
	}

	.row-right {
		display: flex;
		flex-direction: column;
		align-items: flex-end;
		width: 33.5%;
	}
</style>
