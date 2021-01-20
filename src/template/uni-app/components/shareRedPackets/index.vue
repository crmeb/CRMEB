<template>
	<view class='sharing-packets' :class='sharePacket.isState==true?"on":""'>
	   <view class='iconfont icon-guanbi' @click="closeShare"></view>
	   <view class='line'></view>
	   <view class='sharing-con' @click='goShare'>
	      <image src='../../static/images/red-packets.png'></image>
	      <view class='text font-color'>
	        <view>会员分享返</view>
	        <view class='money'><text class='label'>￥</text>{{sharePacket.priceName}}</view>
	        <view class='tip'>下单即返佣金</view>
	        <view class='shareBut'>立即分享</view>
	      </view>
	   </view>  
	</view>
</template>

<script>
	export default {
		
		props: {
			    sharePacket: {
					type: Object,
					default: function(){
						return {isState: true,priceName:''}
					}
			    }
		},
		data() {
			return {

			};
		},
		
		methods: {
			closeShare:function(){
				  this.$emit('closeChange');
			    },
				goShare:function(){
				      this.$emit('listenerActionSheet');
				    }
		}
	}
</script>

<style scoped lang="scss">
	.sharing-packets{position:fixed;left:30rpx;bottom:200rpx;z-index:5;transition:all 0.3s ease-in-out 0s;opacity:1;transform: scale(1);}
	.sharing-packets.on{transform: scale(0);opacity:0;}
	.sharing-packets .iconfont{width:44rpx;height:44rpx;border-radius:50%;text-align:center;line-height:44rpx;background-color:#999;font-size:20rpx;color:#fff;margin:0 auto;box-sizing:border-box;padding-left:1px;}
	.sharing-packets .line{width:2rpx;height:40rpx;background-color:#999;margin:0 auto;}
	.sharing-packets .sharing-con{width:187rpx;height:210rpx;position:relative;}
	.sharing-packets .sharing-con image{width:100%;height:100%;}
	.sharing-packets .sharing-con .text{position:absolute;top:30rpx;font-size:20rpx;width:100%;text-align:center;}
	.sharing-packets .sharing-con .text .money{font-size:32rpx;font-weight:bold;margin-top:5rpx;}
	.sharing-packets .sharing-con .text .money .label{font-size:20rpx;}
	.sharing-packets .sharing-con .text .tip{font-size:18rpx;color:#999;margin-top:5rpx;}
	.sharing-packets .sharing-con .text .shareBut{font-size:22rpx;color:#fff;margin-top:18rpx;height:50rpx;line-height:50rpx;}
</style>
