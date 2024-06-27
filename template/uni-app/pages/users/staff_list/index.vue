<template>
	<view :style="colorStyle">
		<view class="promoter-list">
			<view class="promoterHeader bg-color">
				<view class="headerCon acea-row row-between-wrapper">
					<view>
						<view class="name">
							{{ $t(`员工人数`) }}
							<view class="invitation" @click="showCode">{{ $t(`邀请`) }}</view>
						</view>
						<view>
							<text class="num">{{ teamCount }}</text>
							{{ $t(`人`) }}
						</view>
					</view>
					<view class="iconfont icon-tuandui"></view>
				</view>
			</view>
			<!-- <view class='search acea-row row-between-wrapper'>
				<view class='input'><input placeholder='点击搜索会员名称' placeholder-class='placeholder' v-model="keyword"
						@confirm="submitForm" confirm-type='search' name="search"></input></view>
				<button class='iconfont icon-sousuo2' @click="submitForm"></button>
			</view> -->
			<view class="list">
				<block v-for="(item, index) in recordList" :key="index">
					<view class="item acea-row row-between-wrapper">
						<view class="picTxt acea-row row-between-wrapper">
							<view class="pictrue">
								<image :src="item.avatar"></image>
							</view>
							<view class="text">
								<view class="name line1">{{ item.nickname }}</view>
								<view>{{ $t(`加入时间`) }}: {{ item.division_change_time }}</view>
								<view>{{ $t(`分佣比例`) }}: {{ item.division_percent }}%</view>
							</view>
						</view>
						<view class="right">
							<view>
								<text class="num font-color">{{ item.childCount ? item.childCount : 0 }}</text>
								{{ $t(`人`) }}
							</view>
							<view>
								<text class="num">{{ item.orderCount ? item.orderCount : 0 }}</text>
								{{ $t(`单`) }}
							</view>
							<view>
								<text class="num">{{ item.numberCount ? item.numberCount : 0 }}</text>
								{{ $t(`元`) }}
							</view>
						</view>
					</view>
					<view class="item-btn">
						<view class="change" @click="changeData(item)">{{ $t(`修改分佣比例`) }}</view>
						<view class="clear" @click="clear(item, index)">{{ $t(`删除`) }}</view>
					</view>
				</block>
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<home></home>
		<view class="refund-input" :class="refund_close ? 'on' : ''">
			<view class="input-msg">
				<text class="iconfont icon-guanbi5" @tap="refund_close = false"></text>
				<view class="refund-input-title">{{ $t(`修改分佣比例`) }}</view>
				<view class="refund-input-sty">
					<input type="number" v-model="agent_percent" :placeholder="$t(`请输入百分比`)" />
				</view>
				<view class="refund-bth">
					<!-- <view class="close-refund" @click="refund_close = false">取消</view> -->
					<view class="submit-refund" @click="refundSubmit()">{{ $t(`提交`) }}</view>
				</view>
			</view>
		</view>
		<view class="mask invoice-mask" v-if="refund_close" @click="refund_close = false"></view>
		<view class="mask invoice-mask" v-if="codeModal" @click="codeModal = false"></view>
		<view class="poster-pop" v-if="codeModal">
			<image src="/static/images/poster-close.png" class="close" @click="posterImageClose"></image>
			<!-- #ifdef MP -->
			<image :src="codeSrc"></image>
			<!-- #endif -->

			<!-- #ifndef MP -->
			<zb-code
				ref="qrcode"
				:show="codeModal"
				:cid="cid"
				:val="val"
				:size="size"
				:unit="unit"
				:background="background"
				:foreground="foreground"
				:pdground="pdground"
				:icon="icon"
				:iconSize="iconsize"
				:onval="onval"
				:loadMake="loadMake"
				@result="qrR"
			/>
			<!-- #endif -->

			<!-- #ifndef H5  -->
			<view class="save-poster" @click="savePosterPath">{{ $t(`保存到手机`) }}</view>
			<!-- #endif -->
			<!-- #ifdef H5 -->
			<view class="keep">{{ $t(`长按图片可以保存到手机`) }}</view>
			<!-- #endif -->
		</view>
	</view>
</template>

<script>
import { clerkPeople, setClerkPercent, delClerkPercent } from '@/api/user.js';
import { toLogin } from '@/libs/login.js';
import { mapGetters } from 'vuex';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
import home from '@/components/home';
import colors from '@/mixins/color.js';
import zbCode from '@/components/zb-code/zb-code.vue';
import { HTTP_REQUEST_URL } from '@/config/app.js';
let app = getApp();
export default {
	components: {
		// #ifdef MP
		authorize,
		// #endif
		home,
		zbCode
	},
	mixins: [colors],
	data() {
		return {
			total: 0,
			totalLevel: 0,
			agent_percent: null,
			teamCount: 0,
			page: 1,
			limit: 20,
			keyword: '',
			sort: '',
			grade: 0,
			uid: 0,
			status: false,
			recordList: [],
			refund_close: false,
			isAuto: false, //没有授权的不会自动授权
			isShowAuth: false, //是否隐藏授权
			//二维码参数
			codeShow: false,
			cid: '1',
			ifShow: true,
			val: HTTP_REQUEST_URL + '/pages/index/index?agent_id=' + this.$store.state.app.uid, // 要生成的二维码值
			size: 430, // 二维码大小
			unit: 'upx', // 单位
			background: '#FFF', // 背景色
			foreground: '#000', // 前景色
			pdground: '#000', // 角标色
			icon: '', // 二维码图标
			iconsize: 70, // 二维码图标大小
			lv: 3, // 二维码容错级别 ， 一般不用设置，默认就行
			onval: true, // val值变化时自动重新生成二维码
			loadMake: true, // 组件加载完成后自动生成二维码
			src: '', // 二维码生成后的图片地址或base64
			codeSrc: '',
			codeModal: false
		};
	},
	computed: mapGetters(['isLogin']),
	onLoad() {
		if (this.isLogin) {
			this.userSpreadNewList();
		} else {
			toLogin();
		}
	},
	onShow: function () {
		if (this.is_show) this.userSpreadNewList();
		// this.getUrlCode()
	},
	onHide: function () {
		this.is_show = true;
	},
	methods: {
		qrR(res) {
			this.codeSrc = res;
		},
		showCode() {
			this.codeModal = true;
		},
		posterImageClose() {
			this.codeModal = false;
		},
		onLoadFun: function (e) {
			this.userSpreadNewList();
		},
		// 授权关闭
		authColse: function (e) {
			this.isShowAuth = e;
		},
		setSort: function (sort) {
			let that = this;
			that.sort = sort;
			that.page = 1;
			that.limit = 20;
			that.status = false;
			that.$set(that, 'recordList', []);
			that.userSpreadNewList();
		},
		// setKeyword: function(e) {
		// 	this.keyword = e.detail.value;
		// },
		submitForm: function () {
			this.page = 1;
			this.limit = 20;
			this.status = false;
			this.$set(this, 'recordList', []);
			this.userSpreadNewList();
		},
		clear(data, index) {
			let that = this;
			uni.showModal({
				title: that.$t(`删除员工`),
				content: that.$t(`确定删除该员工?`),
				success: (res) => {
					if (res.confirm) {
						delClerkPercent(data.uid)
							.then((res) => {
								that.recordList.splice(index, 1);
								that.$set(that, 'recordList', that.recordList);
								// that.userSpreadNewList();
								that.teamCount -= 1;
								return that.$util.Tips({
									title: that.$t(`删除成功`),
									icon: 'success'
								});
							})
							.catch((err) => {
								return that.$util.Tips({
									title: err
								});
							});
					} else if (res.cancel) {
						return that.$util.Tips({
							title: that.$t(`已取消`)
						});
					}
				}
			});
		},
		changeData(data) {
			this.refund_close = true;
			this.uid = data.uid;
		},
		refundSubmit() {
			if (this.agent_percent < 0) {
				return this.$util.Tips({
					title: this.$t(`请输入比例`)
				});
			}
			setClerkPercent({
				agent_percent: this.agent_percent,
				uid: this.uid
			})
				.then((res) => {
					this.$util.Tips(
						{
							title: res.msg,
							icon: 'success'
						},
						() => {
							this.refund_close = false;
							this.page = 1;
							this.limit = 20;
							this.keyword = '';
							this.sort = '';
							this.status = false;
							this.agent_percent = null;
							this.$set(this, 'recordList', []);
							this.userSpreadNewList();
						}
					);
				})
				.catch((err) => {
					this.$util.Tips({
						title: err
					});
				});
		},
		setType: function (grade) {
			if (this.grade != grade) {
				this.grade = grade;
				this.page = 1;
				this.limit = 20;
				this.keyword = '';
				this.sort = '';
				this.status = false;
				this.$set(this, 'recordList', []);
				this.userSpreadNewList();
			}
		},
		userSpreadNewList: function () {
			let that = this;
			let page = that.page;
			let limit = that.limit;
			let status = that.status;
			let keyword = that.keyword;
			let sort = that.sort;
			let grade = that.grade;
			let recordList = that.recordList;
			let recordListNew = [];
			if (status == true) return;
			clerkPeople({
				page: page,
				limit: limit,
				keyword: keyword,
				grade: grade,
				sort: sort
			}).then((res) => {
				let len = res.data.list.length;
				let recordListData = res.data.list;
				recordListNew = recordList.concat(recordListData);
				that.total = res.data.total;
				that.totalLevel = res.data.totalLevel;
				that.teamCount = res.data.count;
				that.codeSrc = res.data.codeUrl;
				that.status = limit > len;
				that.page = page + 1;
				that.$set(that, 'recordList', recordListNew);
			});
		},
		downloadFilestoreImage(url) {
			return new Promise((resolve, reject) => {
				let that = this;
				uni.downloadFile({
					url: url,
					success: function (res) {
						resolve(res.tempFilePath);
					},
					fail: function () {
						return that.$util.Tips({
							title: ''
						});
					}
				});
			});
		},
		savePosterPath() {
			let that = this;
			that.downloadFilestoreImage(that.codeSrc).then((url) => {
				uni.getSetting({
					success(res) {
						if (!res.authSetting['scope.writePhotosAlbum']) {
							uni.authorize({
								scope: 'scope.writePhotosAlbum',
								success() {
									uni.saveImageToPhotosAlbum({
										filePath: url,
										success: function (res) {
											that.posterImageClose();
											that.$util.Tips({
												title: that.$t(`保存成功`),
												icon: 'success'
											});
										},
										fail: function (res) {
											that.$util.Tips({
												title: that.$t(`保存失败`)
											});
										}
									});
								}
							});
						} else {
							uni.saveImageToPhotosAlbum({
								filePath: url,
								success: function (res) {
									that.posterImageClose();
									that.$util.Tips({
										title: that.$t(`保存成功`),
										icon: 'success'
									});
								},
								fail: function (res) {
									that.$util.Tips({
										title: that.$t(`保存失败`)
									});
								}
							});
						}
					}
				});
			});
		}
	},
	onReachBottom: function () {
		this.userSpreadNewList();
	}
};
</script>

<style scoped lang="scss">
.invoice-mask {
	background-color: #ccc;
	opacity: 0.7;
}

.promoter-list .nav {
	background-color: #fff;
	height: 86rpx;
	line-height: 86rpx;
	font-size: 28rpx;
	color: #282828;
	border-bottom: 1rpx solid #eee;
}

.promoter-list .nav .item.on {
	border-bottom: 5rpx solid #e93323;
	color: #e93323;
}

.promoter-list .search {
	width: 100%;
	background-color: #fff;
	height: 86rpx;
	padding-left: 30rpx;
	box-sizing: border-box;
}

.promoter-list .search .input {
	width: 610rpx;
	height: 60rpx;
	border-radius: 50rpx;
	background-color: #f5f5f5;
	text-align: center;
	position: relative;
}

.promoter-list .name {
	display: flex;

	.invitation {
		background-color: #fff;
		border-radius: 6rpx;
		padding: 0rpx 10rpx;
		margin-left: 20rpx;
		color: #d13d25;
	}
}

.promoter-list .search .input input {
	height: 100%;
	font-size: 26rpx;
	width: 610rpx;
	text-align: center;
}

.promoter-list .search .input .placeholder {
	color: #bbb;
}

.promoter-list .search .input .iconfont {
	position: absolute;
	right: 28rpx;
	color: #999;
	font-size: 28rpx;
	top: 50%;
	transform: translateY(-50%);
}

.promoter-list .search .iconfont {
	font-size: 45rpx;
	color: #515151;
	width: 110rpx;
	height: 60rpx;
	line-height: 60rpx;
}

.promoter-list .list {
	margin-top: 12rpx;
}

.promoter-list .list .sortNav {
	background-color: #fff;
	height: 76rpx;
	border-bottom: 1rpx solid #eee;
	color: #333;
	font-size: 28rpx;
}

.promoter-list .list .sortNav .sortItem {
	text-align: center;
	flex: 1;
}

.promoter-list .list .sortNav .sortItem image {
	width: 24rpx;
	height: 24rpx;
	margin-left: 6rpx;
	vertical-align: -3rpx;
}

.promoter-list .list .item {
	background-color: #fff;
	border-bottom: 1rpx solid #eee;
	height: 152rpx;
	padding: 0 30rpx 0 20rpx;
	font-size: 24rpx;
	color: #666;
}

.item-btn {
	display: flex;
	align-items: center;
	justify-content: flex-end;
	background-color: #fff;
}

.clear,
.change {
	padding: 5rpx 15rpx;
	margin: 10rpx;
	border-radius: 6rpx;
}

.clear {
	background-color: #ccc;
	color: #fff;
}

.change {
	background-color: #de4d36;
	color: #fff;
}

.promoter-list .list .item .picTxt {
	width: 440rpx;
}

.promoter-list .list .item .picTxt .pictrue {
	width: 106rpx;
	height: 106rpx;
	border-radius: 50%;
}

.promoter-list .list .item .picTxt .pictrue image {
	width: 100%;
	height: 100%;
	border-radius: 50%;
	border: 3rpx solid #fff;
	box-shadow: 0 0 10rpx #aaa;
	box-sizing: border-box;
}

.promoter-list .list .item .picTxt .text {
	width: 304rpx;
	font-size: 24rpx;
	color: #666;
}

.promoter-list .list .item .picTxt .text .name {
	font-size: 28rpx;
	color: #333;
	margin-bottom: 13rpx;
}

.promoter-list .list .item .right {
	width: 240rpx;
	text-align: right;
	font-size: 22rpx;
	color: #333;
}

.promoter-list .list .item .right .num {
	margin-right: 7rpx;
}

.refund-input {
	position: fixed;
	bottom: 0;
	left: 0;
	width: 100%;
	border-radius: 16rpx 16rpx 0 0;
	background-color: #fff;
	z-index: 99;
	padding: 40rpx 0 70rpx 0;
	transition: all 0.3s cubic-bezier(0.25, 0.5, 0.5, 0.9);
	transform: translate3d(0, 100%, 0);

	.refund-input-title {
		font-size: 32rpx;
		margin-bottom: 60rpx;
		color: #282828;
	}

	.refund-input-sty {
		border: 1px solid #ddd;
		padding: 20rpx 20rpx;
		border-radius: 40rpx;
		width: 100%;
		margin: 20rpx 65rpx;
	}

	.input-msg {
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		position: relative;
		margin: 0 65rpx;

		.iconfont {
			position: absolute;
			font-size: 32rpx;
			color: #282828;
			top: 8rpx;
			right: -30rpx;
		}
	}

	.refund-bth {
		display: flex;
		margin: 0 65rpx;
		margin-top: 20rpx;
		justify-content: space-around;
		width: 100%;

		.close-refund {
			padding: 24rpx 80rpx;
			border-radius: 80rpx;
			color: #fff;
			background-color: #ccc;
		}

		.submit-refund {
			width: 100%;
			padding: 24rpx 0rpx;
			text-align: center;
			border-radius: 80rpx;
			color: #fff;
			background-color: #f62c2c;
		}
	}
}

.refund-input.on {
	transform: translate3d(0, 0, 0);
}

.poster-pop {
	width: 450rpx;
	height: 450rpx;
	position: fixed;
	padding: 10rpx;
	background-color: #fff;
	left: 50%;
	transform: translateX(-50%);
	z-index: 399;
	top: 50%;
	margin-top: -357rpx;
}

.poster-pop image {
	width: 100%;
	height: 100%;
	display: block;
}

.poster-pop .close {
	width: 46rpx;
	height: 75rpx;
	position: fixed;
	right: 0;
	top: -73rpx;
	display: block;
}

.poster-pop .save-poster {
	background-color: #df2d0a;
	font-size: ：22rpx;
	color: #fff;
	text-align: center;
	height: 76rpx;
	line-height: 76rpx;
	width: 450rpx;
	margin-left: -10rpx;
}

.poster-pop .keep {
	color: #fff;
	text-align: center;
	font-size: 25rpx;
	margin-top: 30rpx;
}
</style>
