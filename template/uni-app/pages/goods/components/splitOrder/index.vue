<template>
	<view class='splitOrder' v-if="splitGoods.length">
		<view class="all" v-if="select_all">
			<checkbox-group @change="checkboxAllChange">
				<checkbox value="all" :checked="isAllSelect" />
				<text class='checkAll'>{{$t(`全选`)}}</text>
			</checkbox-group>
		</view>
		<checkbox-group @change="checkboxChange">
			<block v-for="(item,index) in splitGoods" :key="index">
				<view class='items acea-row row-between-wrapper'>
					<!-- #ifndef MP -->
					<checkbox :value="(item.id).toString()" :checked="item.checked" />
					<!-- #endif -->
					<!-- #ifdef MP -->
					<checkbox :value="item.id" :checked="item.checked"/>
					<!-- #endif -->
					<view class='picTxt acea-row row-between-wrapper'>
						<view class='pictrue'>
							<image :src='item.cart_info.productInfo.image'></image>
						</view>
						<view class='text'>
							<view class="acea-row row-between-wrapper">
								<view class='name line1'>{{item.cart_info.productInfo.store_name}}</view>
								<view>×{{item.cart_num}}</view>
							</view>
							<view class='infor line1'>
								{{$t(`属性`)}}：{{item.cart_info.productInfo.attrInfo.suk || $t(`默认`)}}</view>
							<view class='money'>{{$t(`￥`)}}{{item.cart_info.truePrice}}</view>
						</view>
						<view class='carnum acea-row row-center-wrapper'>
							<view class="reduce" :class="item.surplus_num == 1 ? 'on' : ''"
								@click.stop='subCart(item)'>-</view>
							<view class='num'>{{item.surplus_num}}</view>
							<view class="plus" :class="item.surplus_num == item.numShow ? 'on' : ''"
								@click.stop='addCart(item)'>+</view>
						</view>
					</view>
				</view>
			</block>
		</checkbox-group>
	</view>
</template>

<script>
	export default {
		props:{
			splitGoods: {
				type: Array,
				default: () => []
			},
			select_all: {
				type: Boolean,
				default: true
			}
		},
		data() {
			return {
				isAllSelect:false
			};
		},
		mounted(){
			
		},
		methods: {
			subCart(item){
				item.surplus_num = Number(item.surplus_num) - 1;
				if(item.surplus_num<=1){
					item.surplus_num = 1
				}
				this.$emit('getList',this.splitGoods);
			},
			addCart(item){
				item.surplus_num = Number(item.surplus_num) + 1;
				if(item.surplus_num>=item.numShow){
					item.surplus_num = item.numShow
				}
				this.$emit('getList',this.splitGoods);
			},
			inArray: function(search, array) {
				for (let i in array) {
					if (array[i] == search) {
						return true;
					}
				}
				return false;
			},
			checkboxChange(event){
				let idList = event.detail.value;
				this.splitGoods.forEach((item)=>{
					if(this.inArray(item.id, idList)){
						item.checked = true;
					}else{
						item.checked = false;
					}
				})
				this.$emit('getList',this.splitGoods);
				if(idList.length == this.splitGoods.length){
					this.isAllSelect = true;
				}else{
					this.isAllSelect = false;
				}
			},
			forGoods(val){
				let that = this;
				if(!that.splitGoods.length) return
				that.splitGoods.forEach((item)=>{
					if(val){
						item.checked = true;
					}else{
						item.checked = false;
					}
				})
				that.$emit('getList',that.splitGoods);
			},
			checkboxAllChange(event){
				let value = event.detail.value;
				if(value.length){
					this.forGoods(1)
				}else{
					this.forGoods(0)
				}
			}
		}
	}
</script>

<style lang="scss">
	
	.splitOrder {
		border-bottom: 1px solid #f0f0f0;
	}
	
	.splitOrder .all{
		padding: 20rpx 30rpx;
	}
	
	.splitOrder .all .checkAll{
		margin-left: 20rpx;
	}
	
	.splitOrder .items {
		padding: 25rpx 30rpx;
		background-color: #fff;
		margin-bottom: 15rpx;
	}
	
	.splitOrder .items .picTxt {
		width: 627rpx;
		position: relative;
	}
	
	.splitOrder .items .picTxt .name{
		width: 360rpx;
	}
	
	.splitOrder .items .picTxt .pictrue {
		width: 160rpx;
		height: 160rpx;
	}
	
	.splitOrder .items .picTxt .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}
	
	.splitOrder .items .picTxt .text {
		width: 444rpx;
		font-size: 28rpx;
		color: #282828;
	}
	
	.splitOrder .items .picTxt .text .reColor {
		color: #999;
	}
	
	.splitOrder .items .picTxt .text .reElection {
		margin-top: 20rpx;
	}
	
	.splitOrder .items .picTxt .text .reElection .title {
		font-size: 24rpx;
	}
	
	.splitOrder .items .picTxt .text .reElection .reBnt {
		width: 120rpx;
		height: 46rpx;
		border-radius: 23rpx;
		font-size: 26rpx;
	}
	
	.splitOrder .items .picTxt .text .infor {
		font-size: 24rpx;
		color: #868686;
		margin-top: 16rpx;
	}
	
	.splitOrder .items .picTxt .text .money {
		font-size: 32rpx;
		color: #282828;
		margin-top: 28rpx;
	}
	
	.splitOrder .items .picTxt .carnum {
		height: 47rpx;
		position: absolute;
		bottom: 7rpx;
		right: 0;
	}
	
	.splitOrder .items .picTxt .carnum view {
		border: 1rpx solid #a4a4a4;
		width: 66rpx;
		text-align: center;
		height: 100%;
		line-height: 40rpx;
		font-size: 28rpx;
		color: #a4a4a4;
	}
	
	.splitOrder .items .picTxt .carnum .reduce {
		border-right: 0;
		border-radius: 3rpx 0 0 3rpx;
	}
	
	.splitOrder .items .picTxt .carnum .reduce.on {
		border-color: #e3e3e3;
		color: #dedede;
	}
	
	.splitOrder .items .picTxt .carnum .plus {
		border-left: 0;
		border-radius: 0 3rpx 3rpx 0;
	}
	
	.splitOrder .items .picTxt .carnum .plus.on {
		border-color: #e3e3e3;
		color: #dedede;
	}
	
	.splitOrder .items .picTxt .carnum .num {
		color: #282828;
	}
</style>
