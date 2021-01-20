<template>
	<view>
		<view class="product-window" :class="(attr.cartAttr === true ? 'on' : '') + ' ' + (iSbnt?'join':'') + ' ' + (iScart?'joinCart':'')">
			<view class="textpic acea-row row-between-wrapper">
				<view class="pictrue">
					<image :src="attr.productSelect.image" @click='getpreviewImage'></image>
				</view>
				<view class="text">
					<view class="line1">
						{{ attr.productSelect.store_name }}
					</view>
					<view class="money font-color">
						<view class="acea-row row-middle">
							￥<text class="num">{{ attr.productSelect.price }}</text>
							<text class='vip-money' v-if="is_vip>0 && attr.productSelect.vip_price">￥{{attr.productSelect.vip_price}}</text>
							<view class="vipImg" v-if="is_vip>0"><image src="../../static/images/svip.gif"></image></view>
						</view>
						<text class="stock" v-if='isShow'>库存: {{ attr.productSelect.stock }}</text>
						<text class='stock' v-if="limitNum">限量: {{attr.productSelect.quota}}</text>
					</view>
				</view>
				<view class="iconfont icon-guanbi" @click="closeAttr"></view>
			</view>
			<view class="rollTop">
				<view class="productWinList">
					<view class="item" v-for="(item, indexw) in attr.productAttr" :key="indexw">
						<view class="title">{{ item.attr_name }}</view>
						<view class="listn acea-row row-middle">
							<view class="itemn" :class="item.index === itemn.attr ? 'on' : ''" v-for="(itemn, indexn) in item.attr_value"
							 @click="tapAttr(indexw, indexn)" :key="indexn">
								{{ itemn.attr }}
							</view>
						</view>
					</view>
				</view>
				<view class="cart acea-row row-between-wrapper">
					<view class="title">数量</view>
					<view class="carnum acea-row row-left">
						<view class="item reduce" :class="attr.productSelect.cart_num <= 1 ? 'on' : ''" @click="CartNumDes">
							-
						</view>
						<view class='item num acea-row row-middle'>
						    <input type="number" v-model="attr.productSelect.cart_num" data-name="productSelect.cart_num" @input="bindCode(attr.productSelect.cart_num)"></input>
						</view>
						<view v-if="iSplus" class="item plus" :class="
				      attr.productSelect.cart_num >= attr.productSelect.stock
				        ? 'on'
				        : ''
				    "
						 @click="CartNumAdd">
							+
						</view>
						<view v-else class='item plus' :class='(attr.productSelect.cart_num >= attr.productSelect.quota) || (attr.productSelect.cart_num >= attr.productSelect.product_stock) || (attr.productSelect.cart_num >= attr.productSelect.num)? "on":""' @click='CartNumAdd'>+</view>
					</view>
				</view>
			</view>
			<view class="joinBnt bg-color" v-if="iSbnt && attr.productSelect.product_stock>0 &&attr.productSelect.quota>0" @click="goCat">我要参团</view>
			<view class="joinBnt on" v-else-if="(iSbnt && attr.productSelect.quota<=0)||(iSbnt &&attr.productSelect.product_stock<=0)">已售罄</view>
			<view class="joinBnt bg-color" v-if="iScart && attr.productSelect.stock" @click="goCat">确定</view>
			<view class="joinBnt on" v-else-if="iScart && !attr.productSelect.stock">已售罄</view>
		</view>
		<view class="mask" @touchmove.prevent :hidden="attr.cartAttr === false" @click="closeAttr"></view>
	</view>
</template>

<script>
	export default {

		props: {
			attr: {
				type: Object,
				default: () => {}
			},
			    limitNum:{
			      type: Number,
			      value: 0
			    },
				isShow:{
				  type: Number,
				  value: 0
				},
				iSbnt:{
				   type:Number,
				   value:0
				},
				iSplus:{
					type:Number,
					value:0
			    },
				iScart:{
					type:Number,
					value:0
				},
				is_vip: {
					type:Number,
					value:0
				}
		},
		data() {
			return {};
		},
		mounted(){
			console.log(this.attr);
		},
		methods: {
			getpreviewImage: function() {
				uni.previewImage({
					urls: this.attr.productSelect.image.split(','),
					current: this.attr.productSelect.image
				});
			},
			    goCat:function(){
			      this.$emit('goCat');
			    },
			 /**
			    * 购物车手动输入数量
			    * 
			    */
			    bindCode: function (e) {
			      this.$emit('iptCartNum', this.attr.productSelect.cart_num);
			    },
			closeAttr: function() {
				this.$emit('myevent');
			},
			CartNumDes: function() {
				this.$emit('ChangeCartNum', false);
			},
			CartNumAdd: function() {
				this.$emit('ChangeCartNum', true);
			},
			tapAttr: function(indexw, indexn) {
				let that = this;
				that.$emit("attrVal", {
					indexw: indexw,
					indexn: indexn
				});
				this.$set(this.attr.productAttr[indexw], 'index', this.attr.productAttr[indexw].attr_values[indexn]);
				let value = that
					.getCheckedValue()
					.join(",");
				that.$emit("ChangeAttr", value);

			},
			//获取被选中属性；
			getCheckedValue: function() {
				let productAttr = this.attr.productAttr;
				let value = [];
				for (let i = 0; i < productAttr.length; i++) {
					for (let j = 0; j < productAttr[i].attr_values.length; j++) {
						if (productAttr[i].index === productAttr[i].attr_values[j]) {
							value.push(productAttr[i].attr_values[j]);
						}
					}
				}
				return value;
			}
		}
	}
</script>

<style scoped lang="scss">
	.vip-money{
		color: #282828;
		font-size: 28rpx;
		font-weight: 700;
		margin-left: 6rpx;
	}
	.vipImg{
		width: 68rpx;
		height: 27rpx;
		image{
			width: 100%;
			height: 100%;
		}
	}
	.product-window {
		position: fixed;
		bottom: 0;
		width: 100%;
		left: 0;
		background-color: #fff;
		z-index: 77;
		border-radius: 16rpx 16rpx 0 0;
		padding-bottom: 140rpx;
		transform: translate3d(0, 100%, 0);
		transition: all .3s cubic-bezier(.25, .5, .5, .9);
	}

	.product-window.on {
		transform: translate3d(0, 0, 0);
	}
	
	.product-window.join{padding-bottom: 30rpx;}
	
	.product-window.joinCart{
		padding-bottom: 30rpx;
		z-index: 999;
	}

	.product-window .textpic {
		padding: 0 130rpx 0 30rpx;
		margin-top: 29rpx;
		position: relative;
	}

	.product-window .textpic .pictrue {
		width: 150rpx;
		height: 150rpx;
	}

	.product-window .textpic .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 10rpx;
	}

	.product-window .textpic .text {
		width: 410rpx;
		font-size: 32rpx;
		color: #202020;
	}

	.product-window .textpic .text .money {
		font-size: 24rpx;
		margin-top: 40rpx;
	}

	.product-window .textpic .text .money .num {
		font-size: 36rpx;
	}

	.product-window .textpic .text .money .stock {
		color: #999;
		margin-left: 18rpx;
	}

	.product-window .textpic .iconfont {
		position: absolute;
		right: 30rpx;
		top: -5rpx;
		font-size: 35rpx;
		color: #8a8a8a;
	}
	
	.product-window .rollTop{
		max-height: 500rpx;
		overflow: auto;
		margin-top: 36rpx;
	}

	.product-window .productWinList .item~.item {
		margin-top: 36rpx;
	}

	.product-window .productWinList .item .title {
		font-size: 30rpx;
		color: #999;
		padding: 0 30rpx;
	}

	.product-window .productWinList .item .listn {
		padding: 0 30rpx 0 16rpx;
	}

	.product-window .productWinList .item .listn .itemn {
		border: 1px solid #F2F2F2;
		font-size: 26rpx;
		color: #282828;
		padding: 7rpx 33rpx;
		border-radius: 25rpx;
		margin: 20rpx 0 0 14rpx;
		background-color: #F2F2F2;
	}

	.product-window .productWinList .item .listn .itemn.on {
		color: #E93323;
		background:rgba(255,244,243,1);
		border-color: #E93323;
	}
	
	.product-window .productWinList .item .listn .itemn.limit {
		color: #999;
		text-decoration:line-through;
	}

	.product-window .cart {
		margin-top: 36rpx;
		padding: 0 30rpx;
	}

	.product-window .cart .title {
		font-size: 30rpx;
		color: #999;
	}

	.product-window .cart .carnum {
		height: 54rpx;
		margin-top: 24rpx;
	}

	.product-window .cart .carnum view {
		// border: 1px solid #a4a4a4;
		width: 84rpx;
		text-align: center;
		height: 100%;
		line-height: 54rpx;
		color: #282828;
		font-size: 45rpx;
	}

	.product-window .cart .carnum .reduce {
		border-right: 0;
		border-radius: 6rpx 0 0 6rpx;
		line-height: 48rpx;
	}

	.product-window .cart .carnum .reduce.on {
		// border-color: #e3e3e3;
		color: #DEDEDE;
		font-size: 60rpx;
	}

	.product-window .cart .carnum .plus {
		border-left: 0;
		border-radius: 0 6rpx 6rpx 0;
		line-height: 46rpx;
	}

	.product-window .cart .carnum .plus.on {
		border-color: #e3e3e3;
		color: #dedede;
	}

	.product-window .cart .carnum .num {
		background:rgba(242,242,242,1);
		color: #282828;
		font-size: 28rpx;
	}
	.product-window .joinBnt{font-size: 30rpx;width: 620rpx;height: 86rpx;border-radius: 50rpx;text-align: center;line-height: 86rpx;color: #fff;margin: 21rpx auto 0 auto;}
	.product-window .joinBnt.on{background-color:#bbb;color:#fff;}
</style>