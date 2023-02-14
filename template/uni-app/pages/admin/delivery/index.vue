<template>
	<view class="deliver-goods">
		<header>
			<view class="order-num acea-row row-between-wrapper">
				<view class="num line1">{{$t(`订单号`)}}：{{ order_id }}</view>
				<view class="name line1">
					<span class="iconfont icon-yonghu2"></span>{{ delivery.nickname }}
				</view>
			</view>
			<view class="address">
				<view class="name">
					{{ delivery.real_name
          }}<span class="phone">{{ delivery.user_phone }}</span>
				</view>
				<view>{{ delivery.user_address }}</view>
			</view>
			<view class="line">
				<image src="@/static/images/line.jpg" />
			</view>
		</header>
		<view class="wrapper">
			<view class="item acea-row row-between-wrapper">
				<view>{{$t(`发货方式`)}}</view>
				<view class="mode acea-row row-middle row-right">
					<view class="goods" :class="active === index ? 'on' : ''" v-for="(item, index) in types"
						:key="index" @click="changeType(item, index)">
						{{ item.title }}<span class="iconfont icon-xuanzhong2"></span>
					</view>
				</view>
			</view>
			<block v-if="logistics.length>0">
				<view class="list" v-show="active === 0">
					<view class="item acea-row row-between-wrapper" v-if="delivery.config_export_open == 1">
						<view>{{$t(`发货类型`)}}</view>
						<view class="mode acea-row row-middle row-right">
							<view class="goods" :class="curExpress === item.key ? 'on' : ''"
								v-for="(item, index) in expressType" :key="index" @click="changeExpTpe(item, index)">
								{{ item.title }}<span class="iconfont icon-xuanzhong2"></span>
							</view>
						</view>
					</view>
					<block v-if="curExpress == 1">
						<view class="item acea-row row-between-wrapper">
							<view>{{$t(`快递公司`)}}</view>
							<view class="select-box">
								<picker class="pickerBox" @change="bindPickerChange" :value="seIndex" :range="logistics"
									range-key="name">
									<!-- <view></view> -->
									<view class="uni-input">{{logistics[seIndex].name}}</view>
								</picker>
							</view>
						</view>
						<view class="item acea-row row-between-wrapper">
							<view>{{$t(`快递单号`)}}</view>
							<input type="text" :placeholder="$t(`填写快递单号`)" v-model="delivery_id" class="mode" />
							<!-- #ifdef MP -->
							<text class="iconfont icon-xiangji" @click="scanCode"></text>
							<!-- #endif -->
							<!-- #ifdef H5 -->
							<text v-if="isWeixin" class="iconfont icon-xiangji" @click="scanCode"></text>
							<!-- #endif -->
							<text class="trip" v-if="curExpress == 1">{{$t(`顺丰请输入单号 :收件人或寄件人手机号后四位`)}}</text>
							<text class="trip" v-if="curExpress == 1">{{$t(`例如：SF000000000000:3941`)}}</text>
						</view>
					</block>
					<block v-if="curExpress == 2">
						<view class="item acea-row row-between-wrapper">
							<view>{{$t(`快递公司`)}}</view>
							<view class="select-box">
								<picker class="pickerBox" @change="bindPickerChange" :value="seIndex" :range="logistics"
									range-key="name">
									<!-- <view></view> -->
									<view class="uni-input">{{logistics[seIndex].name}}</view>
								</picker>
							</view>
						</view>
						<view class="item acea-row row-between-wrapper" v-if="expTemp.length>0">
							<view>{{$t(`电子面单`)}}</view>
							<div style="display: flex;align-items: center;">
								<picker class="pickerBox" @change="bindTempChange" :value="expIndex" :range="expTemp"
									range-key="title">
									<view class="uni-input">{{expTemp[expIndex].title}}</view>
								</picker>
								<div class="look" @click="previewImage">{{$t(`预览`)}}</div>
							</div>

						</view>
						<view class="item acea-row row-between-wrapper">
							<view>{{$t(`寄件人姓名`)}}：</view>
							<input type="text" :placeholder="$t(`填写寄件人姓名`)" v-model="to_name" class="mode" />
						</view>
						<view class="item acea-row row-between-wrapper">
							<view>{{$t(`寄件人电话`)}}：</view>
							<input type="text" :placeholder="$t(`填写寄件人电话`)" v-model="to_tel" class="mode" />
						</view>
						<view class="item acea-row row-between-wrapper">
							<view>{{$t(`寄件人地址`)}}：</view>
							<input type="text" :placeholder="$t(`填写寄件人地址`)" v-model="to_addr" class="mode" />
						</view>
					</block>
				</view>
			</block>

			<view class="list" v-show="active === 1">
				<view class="item acea-row row-between-wrapper">
					<view>{{$t(`送货人`)}}</view>
					<view class="select-box" v-if="postPeople.length>0">
						<picker class="pickerBox" @change="bindPostChange" :value="postIndex" :range="postPeople"
							range-key="wx_name">
							<!-- <view></view> -->
							<view class="uni-input">{{postPeople[postIndex].wx_name}}</view>
						</picker>
					</view>
				</view>
			</view>
			<textarea v-show="active === 2" v-model="fictitious_content" class="textarea" @blur="bindTextAreaBlur"
				:placeholder="$t(`remark`)" :maxlength="500" auto-height />
		</view>
		<view style="height:1.2rem;"></view>
		<view class="confirm" @click="saveInfo">{{$t(`确认提交`)}}</view>
	</view>
</template>
<script>
	import {
		getAdminOrderDelivery,
		setAdminOrderDelivery,
		getLogistics,
		orderExportTemp,
		orderDeliveryInfo,
		orderOrderDelivery
	} from "@/api/admin";
	import {
		checkPhone
	} from '@/utils/validate.js'
	export default {
		name: "GoodsDeliver",
		components: {},
		props: {},
		data: function() {
			return {
				types: [{
						type: "express",
						title: this.$t(`发货`),
						key: 1
					},
					{
						type: "send",
						title: this.$t(`送货`),
						key: 2
					},
					{
						type: "fictitious",
						title: this.$t(`无需物流`),
						key: 3
					}
				],
				expressType: [{
						title: this.$t(`手动填写`),
						key: 1
					},
					{
						title: this.$t(`电子面单打印`),
						key: 2
					},
				],
				curExpress: 1,
				active: 0,
				order_id: "",
				delivery: [],
				logistics: [],
				delivery_type: "1",
				delivery_name: "",
				delivery_id: "",
				seIndex: 0,
				expIndex: 0,
				expTemp: [], // 快递模板
				to_name: '', // 发货人名称	
				to_tel: '', // 发货人电话	
				to_addr: "", // 发货人地址	
				postPeople: [], //配送人
				postIndex: 0,
				fictitious_content: '',
				// #ifdef H5
				isWeixin: this.$wechat.isWeixin()
				// #endif
			};
		},
		watch: {
			"$route.params.oid": function(newVal) {
				let that = this;
				if (newVal != undefined) {
					that.order_id = newVal;
					that.getIndex();
				}
			}
		},
		onLoad: function(option) {
			this.order_id = option.id;
			this.getIndex();
			this.getLogistics();
			this.orderDeliveryInfo()
			this.geTorderOrderDelivery()
		},
		methods: {
			// 扫描快递单号一维码
			scanCode() {
				// #ifdef MP
				let that = this;
				uni.scanCode({
					scanType: ['barCode'],
					success(res) {
						that.delivery_id = res.result.replace('CODE_128,', '');
					}
				})
				// #endif
				// #ifdef H5
				if (this.$wechat.isWeixin()) {
					this.$wechat.wechatEvevt('scanQRCode', {
						needResult: 1,
						scanType: ['barCode']
					}).then(res => {
						this.delivery_id = res.resultStr.replace('CODE_128,', '');
					});
				}
				// #endif
			},
			// 预览图片
			previewImage() {
				uni.previewImage({
					urls: [this.expTemp[this.expIndex].pic],
					success: function() {

					},
					fail: function(error) {

					}
				});
			},
			// 获取配送员列表
			geTorderOrderDelivery() {
				orderOrderDelivery().then(res => {
					this.postPeople = res.data
				})
			},
			// 配送员选择
			bindPostChange(e) {
				this.postIndex = e.detail.value
			},
			// 选择发货类型
			changeExpTpe(item, index) {
				this.curExpress = item.key
				this.getLogistics(index || '');
			},
			changeType: function(item, index) {
				this.active = index;
				this.delivery_type = item.key;
			},
			getIndex: function() {
				let that = this;
				getAdminOrderDelivery(that.order_id).then(
					res => {
						that.delivery = res.data;
					},
					error => {
						that.$util.Tips({
							title: error
						})
					}
				);
			},
			getLogistics(status) {
				let that = this;
				getLogistics({
					status
				}).then(
					res => {
						that.logistics = res.data;
						that.getExpTemp(res.data[0].code)
					},
					error => {
						that.$util.Tips({
							title: error
						})
					}
				);
			},
			async saveInfo() {
				let that = this,
					delivery_type = that.delivery_type,
					delivery_name = that.logistics[that.seIndex].name,
					delivery_id = that.delivery_id,
					userName = that.delivery_name,
					save = {};
				save.delivery_type = delivery_name;
				save.delivery_code = that.logistics[that.seIndex].code
				save.delivery_name = that.logistics[that.seIndex].id
				save.type = that.active + 1
				if (delivery_type == 1 && this.curExpress == 1) {
					if (!delivery_id) {
						return this.$util.Tips({
							title: that.$t(`填写快递单号`)
						})
					}
					save.express_record_type = that.curExpress
					save.delivery_id = delivery_id
					that.setInfo(save);
				}

				if (delivery_type == 1 && this.curExpress == 2) {
					if (!that.to_name) {
						return this.$util.Tips({
							title: that.$t(`填写寄件人姓名`)
						})
					}
					if (!that.to_tel) {
						return this.$util.Tips({
							title: that.$t(`填写寄件人电话`)
						})
					}
					if (!(/^1[3456789]\d{9}$/.test(that.to_tel))) {
						return this.$util.Tips({
							title: that.$t(`填写寄件人电话`)
						})
					}
					if (!that.to_addr) {
						return this.$util.Tips({
							title: that.$t(`填写寄件人地址`) 
						})
					}
					if (that.expTemp.length == 0) {
						return this.$util.Tips({
							title: that.$t(`请选择电子面单`)
						})
					}
					save.express_record_type = that.curExpress
					save.to_name = that.to_name
					save.to_tel = that.to_tel
					save.to_addr = that.to_addr
					save.express_temp_id = that.expTemp[that.expIndex].temp_id
					that.setInfo(save);
				}
				if (delivery_type == 2) {
					let obj = this.postPeople[this.postIndex]
					let params = {}
					params.type = that.delivery_type
					params.sh_delivery_name = obj.wx_name
					params.sh_delivery_id = obj.phone
					params.sh_delivery_uid = obj.uid
					that.setInfo(params);
				}
				if (delivery_type == 3) {
					let params = {}
					params.type = that.delivery_type;
					params.fictitious_content = that.fictitious_content;
					that.setInfo(params);
				}
				// switch (delivery_type) {
				// 	case "2":
				// 		if (!userName) {
				// 			return that.$util.Tips({
				// 				title: '请填写送货人姓名'
				// 			})
				// 		}
				// 		if (!delivery_id || !checkPhone(delivery_id)) {
				// 			return that.$util.Tips({
				// 				title: '请填写正确的手机号码'
				// 			})
				// 		}
				// 		save.delivery_name = userName;
				// 		save.delivery_id = delivery_id;
				// 		that.setInfo(save);
				// 		break;
				// 	case "1":
				// 		if (!delivery_id) {
				// 			return this.$util.Tips({
				// 				title: '请填写快递单号'
				// 			})
				// 		}
				// 		save.delivery_name = delivery_name;
				// 		save.delivery_id = delivery_id;
				// 		that.setInfo(save);
				// 		break;
				// 	case "3":
				// 		that.setInfo(save);
				// 		break;
				// }
			},
			setInfo: function(item) {
				let that = this;
				setAdminOrderDelivery(that.delivery.id, item).then(
					res => {
						that.$util.Tips({
							title: res.msg,
							icon: 'success',
							mask: true
						})
						setTimeout(res => {
							uni.navigateBack();
						}, 2000)
					},
					error => {
						that.$util.Tips({
							title: error
						})
					}
				);
			},
			bindPickerChange(e) {
				this.seIndex = e.detail.value
				this.getExpTemp(this.logistics[e.detail.value].code)

			},
			bindTempChange(e) {
				this.expIndex = e.detail.value
			},
			getExpTemp(code) {
				orderExportTemp({
					com: code
				}).then(res => {
					this.expTemp = res.data.data
				})
			},
			// 获取订单打印默认配置
			orderDeliveryInfo() {
				orderDeliveryInfo().then(res => {
					this.to_name = res.data.to_name;
					this.to_tel = res.data.to_tel;
					this.to_addr = res.data.to_add;
				})
			}
		}
	};
</script>

<style lang="scss">
	/*发货*/
	.deliver-goods header {
		width: 100%;
		background-color: #fff;
		margin-top: 10upx;
	}

	.deliver-goods header .order-num {
		padding: 0 30upx;
		border-bottom: 1px solid #f5f5f5;
		height: 67upx;
	}

	.deliver-goods header .order-num .num {
		width: 430upx;
		font-size: 26upx;
		color: #282828;
		position: relative;
	}

	.deliver-goods header .order-num .num:after {
		position: absolute;
		content: '';
		width: 1px;
		height: 30upx;
		background-color: #ddd;
		top: 50%;
		margin-top: -15upx;
		right: 0;
	}

	.deliver-goods header .order-num .name {
		width: 260upx;
		font-size: 26upx;
		color: #282828;
		text-align: center;
	}

	.deliver-goods header .order-num .name .iconfont {
		font-size: 35upx;
		color: #477ef3;
		vertical-align: middle;
		margin-right: 10upx;
	}

	.deliver-goods header .address {
		font-size: 26upx;
		color: #868686;
		background-color: #fff;
		padding: 30upx;
	}

	.deliver-goods header .address .name {
		font-size: 34upx;
		color: #282828;
		margin-bottom: 10upx;
	}

	.deliver-goods header .address .name .phone {
		margin-left: 40upx;
	}

	.deliver-goods header .line {
		width: 100%;
		height: 3upx;
	}

	.deliver-goods header .line image {
		width: 100%;
		height: 100%;
		display: block;
	}

	.deliver-goods .wrapper {
		width: 100%;
		background-color: #fff;
	}

	.deliver-goods .wrapper .item {
		border-bottom: 1px solid #f0f0f0;
		padding: 0 30upx;
		height: 96upx;
		font-size: 32upx;
		color: #282828;
		position: relative;
	}

	.deliver-goods .wrapper .item .mode {
		width: 460upx;
		height: 100%;
		text-align: right;
	}

	.deliver-goods .wrapper .item .mode .iconfont {
		font-size: 30upx;
		margin-left: 13upx;
	}

	.deliver-goods .wrapper .item .mode .goods~.goods {
		margin-left: 30upx;
	}

	.deliver-goods .wrapper .item .mode .goods {
		color: #bbb;
	}

	.deliver-goods .wrapper .item .mode .goods.on {
		color: #477ef3;
	}

	.deliver-goods .wrapper .item .icon-up {
		position: absolute;
		font-size: 35upx;
		color: #2c2c2c;
		right: 30upx;
	}

	.deliver-goods .wrapper .item select {
		direction: rtl;
		padding-right: 60upx;
		position: relative;
		z-index: 2;
	}

	.deliver-goods .wrapper .item input::placeholder {
		color: #bbb;
	}

	.deliver-goods .confirm {
		font-size: 32upx;
		color: #fff;
		width: 100%;
		height: 100upx;
		background-color: #477ef3;
		text-align: center;
		line-height: 100upx;
		position: fixed;
		bottom: 0;
	}

	.select-box {
		flex: 1;
		height: 100%;

		.pickerBox {
			display: flex;
			align-items: center;
			justify-content: flex-end;
			width: 100%;
			height: 100%;
		}
	}

	.look {
		margin-left: 20rpx;
		color: #1890FF;
	}

	.textarea {
		display: block;
		min-height: 192rpx;
		padding: 30rpx;
	}

	.icon-xiangji {
		font-size: 35rpx;
		color: #477ef3;
	}

	.trip {
		font-size: 22rpx;
		color: #ccc;
		padding: 6rpx 10rpx;
	}
</style>
