<template>
	<!-- 在线客服 -->
	<view class="custmer" v-show="!isSortType">
		<!-- #ifdef H5 || APP-PLUS -->
		<view class="customerService" :class="positions?'':'on'" :style="'top:'+topConfig" @touchmove.stop.prevent="setTouchMove" @click="licks">
			<view class="pictrue">
				<image :src="logoConfig"></image>
			</view>
		</view>
		<!-- #endif -->
		<!-- #ifdef MP -->
		<view class="customerService" :class="positions?'':'on'" :style="'top:'+topConfig" @touchmove.stop.prevent="setTouchMove" v-if="routineContact === 0" @click="licks">
			<view class="pictrue">
				<image :src="logoConfig"></image>
			</view>
		</view>
		<button class="customerService-sty" :class="positions?'':'on'" :style="'top:'+topConfig" @touchmove.stop.prevent="setTouchMove" open-type='contact' v-if="routineContact === 1">
			<image class="pictrue" :src="logoConfig"></image>
		</button>
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		mapGetters
	} from "vuex";
	import {
			getCustomer
		} from '@/utils/index.js'
	export default {
		name: 'customerService',
		computed: mapGetters(['userInfo']),
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isSortType:{
				type: String | Number,
				default:0
			}
		},
		data() {
			return {
				routineContact: parseFloat(this.dataConfig.routine_contact_type),
				logoConfig: this.dataConfig.logoConfig.url,
				topConfig: this.dataConfig.topConfig.val?this.dataConfig.topConfig.val>=80?80+'%':this.dataConfig.topConfig.val+'%':'30%',
				positions: this.dataConfig.locationConfig.tabVal
			};
		},
		created() {
		},
		methods: {
			licks(){
				if (this.dataConfig.buttonConfig.tabVal) {
					getCustomer(`/pages/extension/customer_list/chat`)
				} else{
					this.$util.JumpPath(this.dataConfig.logoConfig.link);
				}
			},
			setTouchMove(e) {
				var that = this;
				if (e.touches[0].clientY < 545 && e.touches[0].clientY > 66) {
					that.topConfig = e.touches[0].clientY+'px'
				}
			},
		}
	}
</script>

<style lang="scss">
  .custmer {
    touch-action: none;
  }
	.customerService,.customerService-sty {
		position: fixed;
		right: 20rpx;
		z-index: 40;
		&.on {
			left:20rpx;
		}
		.pictrue {
			width: 86rpx;
			height: 86rpx;
			border-radius: 50%;

			image {
				width: 100%;
				height: 100%;
				border-radius: 50%;
			}
		}
	}
	.customerService-sty{
		background-color: rgba(0,0,0,0) !important;
	}
</style>
