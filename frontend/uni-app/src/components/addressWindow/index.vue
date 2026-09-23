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

<script setup>
	import { ref } from 'vue';
	import {
		getAddressList as getAddressListApi
	} from '@/api/user.js';
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';

	const props = defineProps({
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
	});

	const emit = defineEmits(['OnChangeAddress', 'changeClose', 'changeTextareaStatus', 'onHaveAddressList']);

	const imgHost = ref(HTTP_REQUEST_URL);
	const active = ref(0);
	//地址列表
	const addressList = ref([]);
	const is_loading = ref(true);

	function tapAddress(e, addressid) {
		active.value = e;
		emit('OnChangeAddress', addressid);
	}
	function close() {
		emit('changeClose');
		emit('changeTextareaStatus');
	}
	function goAddressPages() {
		emit('changeClose');
		emit('changeTextareaStatus');
		uni.navigateTo({
			url: props.pagesUrl
		});
	}
	function getAddressList() {
		getAddressListApi({
			page: 1,
			limit: 5
		}).then(res => {
			let list = res.data;
			//处理默认选中项
			for (let i = 0, leng = list.length; i < leng; i++) {
				if (list[i].id == props.address.addressId) {
					active.value = i;
				}
			}
			addressList.value = list;
			is_loading.value = false;
			if (list.length) {
				emit('onHaveAddressList', true)
			}
		})
	}

	// 供父组件通过 $refs.getAddressList() 调用
	defineExpose({
		getAddressList
	});
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
