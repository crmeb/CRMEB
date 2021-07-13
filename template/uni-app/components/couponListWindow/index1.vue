<template>
	<view>
		<view class='coupon-list-window' :class='coupon.coupon==true?"on":""'>
		   <view class='title'>优惠券<text class='iconfont icon-guanbi' @click='close'></text></view>
		   <view class='coupon-list' v-if="coupon.list.length">
		      <view class='item acea-row row-center-wrapper' v-for="(item,index) in coupon.list" @click="getCouponUser(index,item.id)" :key='index'>
		        <view class='money acea-row row-column row-center-wrapper' :class='item.is_use?"moneyGray":""'>
					<view>￥<text class='num'>{{item.coupon_price}}</text></view>
					<view class="pic-num">满{{item.use_min_price}}元可用</view>
				</view>
		        <view class='text'>
					 <view class='condition line1'>
					    <span class='line-title' :class='item.is_use?"gray":""' v-if='item.type===0'>通用劵</span>
					    <span class='line-title' :class='item.is_use?"gray":""' v-else-if='item.type===1'>品类券</span>
					    <span class='line-title' :class='item.is_use?"gray":""' v-else>商品券</span>
					    <span>{{item.title}}</span>
					</view>
		            <view class='data acea-row row-between-wrapper'>
		              <view>{{ item.start_time ? item.start_time + "-" : ""}}{{ item.end_time }}</view>
		              <view class='bnt gray' v-if="item.is_use">{{item.use_title || '已领取'}}</view>
		              <view class='bnt bg-color' v-else>{{coupon.statusTile || '立即领取'}}</view>
		            </view>
		        </view>
		      </view>
		   </view>
		   <!-- 无优惠券 -->
		   <view class='pictrue' v-else><image src='../../static/images/noCoupon.png'></image></view>
		</view>
		<view class='mask' catchtouchmove="true" :hidden='coupon.coupon==false' @click='close'></view>
	</view>
</template>

<script>
	import { setCouponReceive } from '@/api/api.js';
	export default {
		props: {
			//打开状态 0=领取优惠券,1=使用优惠券
			 openType: {
			      type: Number,
			      default: 0,
			    },
			    coupon: {
					type: Object,
					default: function(){
						return {};
					}
			    }
		},
		data() {
			return {
	
			};
		},
		
		methods: {
			 close: function () {
			      this.$emit('ChangCouponsClone');
			    },
			    getCouponUser:function(index,id){
			      let that = this;
			      let list = that.coupon.list;
			      if (list[index].is_use == true && this.openType==0) return true;
			      switch (this.openType){
			        case 0:
			          //领取优惠券
			          setCouponReceive(id).then(res=>{
						  that.$emit('ChangCouponsUseState', index);
						  that.$util.Tips({title: "领取成功"});
						  that.$emit('ChangCoupons', list[index]);
			          })
			        break;
			        case 1:
			          that.$emit('ChangCoupons',index);
			        break;
			      }
			    }
		}
	}
</script>

<style scoped lang="scss">
	.coupon-list-window{position:fixed;bottom:0;left:0;width:100%;background-color:#f5f5f5;border-radius:16rpx 16rpx 0 0;z-index:555;transform:translate3d(0,100%,0);transition:all .3s cubic-bezier(.25,.5,.5,.9);}
	.coupon-list-window.on{transform:translate3d(0,0,0);}
	.coupon-list-window .title{height:124rpx;width:100%;text-align:center;line-height:124rpx;font-size:32rpx;font-weight:bold;position:relative;}
	.coupon-list-window .title .iconfont{position:absolute;right:30rpx;top:50%;transform:translateY(-50%);font-size:35rpx;color:#8a8a8a;font-weight:normal;}
	.coupon-list-window .coupon-list{margin:0 0 50rpx 0;height:550rpx;overflow:auto;}
	.coupon-list-window .pictrue{width:414rpx;height:336rpx;margin:0 auto 50rpx auto;}
	.coupon-list-window .pictrue image{width:100%;height:100%;}
	.pic-num{color: #fff;font-size: 24rpx;}
	.line-title{
	  width:90rpx;
	  padding: 0 10rpx;
	  box-sizing: border-box;
	  background:rgba(255,247,247,1);
	  border:1px solid rgba(232,51,35,1);
	  opacity:1;
	  border-radius:20rpx;
	  font-size:20rpx;
	  color: #E83323;
	  margin-right: 12rpx;
	}
	.line-title.gray{
	  border-color:#BBB;
	  color:#bbb;
	  background-color:#F5F5F5;
	}
</style>
