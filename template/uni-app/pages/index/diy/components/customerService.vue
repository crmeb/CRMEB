<template>
	<view style="touch-action: none;" v-show="!isSortType">
		<!-- #ifdef H5 || APP-PLUS -->
		<view class="customerService" :style="'top:'+topConfig" @touchmove.stop.prevent="setTouchMove">
			<view class="pictrue" @click="goCustomer">
				<image :src="logoConfig"></image>
			</view>
		</view>
		<!-- #endif -->
		<!-- #ifdef MP -->
		<view class="customerService" :style="'top:'+topConfig" @touchmove.stop.prevent="setTouchMove"
			v-if="routineContact === '0'">
			<view class="pictrue" @click="goCustomer">
				<image :src="logoConfig"></image>
			</view>
		</view>
		<button class="customerService-sty" :style="'top:'+topConfig" @touchmove.stop.prevent="setTouchMove"
			open-type='contact' v-if="routineContact === '1'">
			<image class="pictrue" :src="logoConfig"></image>
		</button>
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		getCustomer
	} from '@/utils/index.js'
	export default {
		name: 'customerService',
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
				routineContact: this.dataConfig.routine_contact_type,
				logoConfig: this.dataConfig.logoConfig.url,
				topConfig: this.dataConfig.topConfig.val ? this.dataConfig.topConfig.val + '%' : '30%'
			};
		},
		created() {},
		methods: {
			setTouchMove(e) {
				var that = this;
				if (e.touches[0].clientY < 545 && e.touches[0].clientY > 66) {
					that.topConfig = e.touches[0].clientY + 'px'
				}
			},
			goCustomer() {
				getCustomer(`/pages/extension/customer_list/chat?productId=${this.ids}`)
			},
		}
	}
</script>

<style lang="scss">
	.customerService,
	.customerService-sty {
		position: fixed;
		right: 20rpx;
		z-index: 40;

		.pictrue {
			width: 86rpx;
			height: 86rpx;

			border-radius: 50%;

			image {
				width: 100%;
				height: 100%;
			}
		}
	}

	.customerService-sty {
		background-color: rgba(0, 0, 0, 0) !important;
	}
</style>
