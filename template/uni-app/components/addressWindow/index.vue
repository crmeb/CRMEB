<template>
	<view>
		<view class="address-window" :class="address.address==true?'on':''">
			<view class='title'>{{$t(`选择地址`)}}<text class='iconfont icon-guanbi' @tap='close'></text></view>
			<view class='list'>
				<view class='item acea-row row-between-wrapper' :class='active==index?"font-num":""'
					v-for="(item,index) in addressList" @tap='tapAddress(index,item.id)' :key='index'>
					<text class='iconfont icon-ditu' :class='active==index?"font-num":""'></text>
					<view class='address'>
						<view class='name' :class='active==index?"font-num":""'>{{item.real_name}}<text
								class='phone'>{{item.phone}}</text></view>
						<view class='line1'>{{item.province}}{{item.city}}{{item.district}}{{item.detail}}</view>
					</view>
					<text class='iconfont icon-complete' :class='active==index?"font-num":""'></text>
				</view>
			</view>
			<!-- 无地址 -->
			<view class='pictrue' v-if="!is_loading && !addressList.length">
				<image :src="imgHost + '/statics/images/noAddress.png'"></image>
			</view>
			<view class='addressBnt bg-color' @tap='goAddressPages'>{{$t(`选择其它地址`)}}</view>
		</view>
		<view class='mask' catchtouchmove="true" :hidden='address.address==false' @tap='close'></view>
	</view>
</template>

<script>
	import {
		getAddressList
	} from '@/api/user.js';
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	export default {
		props: {
			pagesUrl: {
				type: String,
				default: '',
			},
			address: {
				type: Object,
				default: function() {
					return {
						address: true,
						addressId: 0,
					};
				}
			},
			isLog: {
				type: Boolean,
				default: false,
			},
		},
		data() {
			return {
				imgHost: HTTP_REQUEST_URL,
				active: 0,
				//地址列表
				addressList: [],
				is_loading: true
			};
		},

		methods: {
			tapAddress: function(e, addressid) {
				this.active = e;
				this.$emit('OnChangeAddress', addressid);
			},
			close: function() {
				this.$emit('changeClose');
				this.$emit('changeTextareaStatus');
			},
			goAddressPages: function() {
				this.$emit('changeClose');
				this.$emit('changeTextareaStatus');
				uni.navigateTo({
					url: this.pagesUrl
				});
			},
			getAddressList: function() {
				let that = this;
				getAddressList({
					page: 1,
					limit: 5
				}).then(res => {
					let addressList = res.data;
					//处理默认选中项
					for (let i = 0, leng = addressList.length; i < leng; i++) {
						if (addressList[i].id == that.address.addressId) {
							that.active = i;
						}
					}
					that.$set(that, 'addressList', addressList);
					that.is_loading = false;
					if (addressList.length) {
						this.$emit('onHaveAddressList', true)
					}
				})
			}
		}
	}
</script>

<style scoped lang="scss">
	.address-window {
		background-color: #fff;
		position: fixed;
		bottom: 0;
		left: 0;
		width: 100%;
		z-index: 1000;
		transform: translate3d(0, 100%, 0);
		transition: all .3s cubic-bezier(.25, .5, .5, .9);
	}

	.address-window.on {
		transform: translate3d(0, 0, 0);
	}

	.address-window .title {
		font-size: 32rpx;
		font-weight: bold;
		text-align: center;
		height: 123rpx;
		line-height: 123rpx;
		position: relative;
	}

	.address-window .title .iconfont {
		position: absolute;
		right: 30rpx;
		color: #8a8a8a;
		font-size: 35rpx;
	}

	.address-window .list .item {
		margin-left: 30rpx;
		padding-right: 30rpx;
		border-bottom: 1px solid #eee;
		height: 129rpx;
		font-size: 25rpx;
		color: #333;
	}

	.address-window .list .item .iconfont {
		font-size: 37rpx;
		color: #2c2c2c;
	}

	.address-window .list .item .iconfont.icon-complete {
		font-size: 30rpx;
		color: #fff;
	}

	.address-window .list .item .address {
		width: 560rpx;
	}

	.address-window .list .item .address .name {
		font-size: 28rpx;
		font-weight: bold;
		color: #282828;
		margin-bottom: 4rpx;
	}

	.address-window .list .item .address .name .phone {
		margin-left: 18rpx;
	}

	.address-window .addressBnt {
		font-size: 30rpx;
		font-weight: bold;
		color: #fff;
		width: 690rpx;
		height: 86rpx;
		border-radius: 43rpx;
		text-align: center;
		line-height: 86rpx;
		margin: 85rpx auto;
	}

	.address-window .pictrue {
		width: 414rpx;
		height: 336rpx;
		margin: 0 auto;
	}

	.address-window .pictrue image {
		width: 100%;
		height: 100%;
	}
</style>
