<template>
	<view :style="colorStyle">
		<view class="ChangePassword">
			<form @submit="editPwd">
				<view class="phone">{{$t(`当前手机号`)}}：{{phone}}</view>
				<view class="list">
					<view class="item">
						<input type='password' :placeholder='$t(`设置新密码`)' placeholder-class='placeholder'
							name="password" :value="password"></input>
					</view>
					<view class="item">
						<input type='password' :placeholder='$t(`确认新密码`)' placeholder-class='placeholder'
							name="qr_password" :value="qr_password"></input>
					</view>
					<view class="item acea-row row-between-wrapper">
						<input type='number' :placeholder='$t(`填写验证码`)' placeholder-class='placeholder' class="codeIput"
							name="captcha" :value="captcha"></input>
						<button class="code font-num" :class="disabled === true ? 'on' : ''" :disabled='disabled'
							@click="code">
							{{ text }}
						</button>
					</view>
				</view>
				<button form-type="submit" class="confirmBnt bg-color">{{$t(`确认修改`)}}</button>
			</form>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<Verify @success="success" :captchaType="captchaType" :imgSize="{ width: '330px', height: '155px' }"
			ref="verify"></Verify>
	</view>
</template>

<script>
	import sendVerifyCode from "@/mixins/SendVerifyCode";
	import {
		phoneRegisterReset,
		verifyCode
	} from '@/api/api.js';
	import {
		getUserInfo,
		registerVerify
	} from '@/api/user.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import colors from '@/mixins/color.js';
	import Verify from '../components/verify/index.vue';
	export default {
		mixins: [sendVerifyCode, colors],
		components: {
			// #ifdef MP
			authorize,
			// #endif
			Verify
		},
		data() {
			return {
				userInfo: {},
				phone: '',
				password: '',
				captcha: '',
				qr_password: '',
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				key: '',
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.getUserInfo();
					}
				},
				deep: true
			}
		},
		onLoad() {
			if (this.isLogin) {
				this.getUserInfo();
				verifyCode().then(res => {
					this.$set(this, 'key', res.data.key)
				});
			} else {
				toLogin()
			}
		},
		methods: {
			/**
			 * 授权回调
			 */
			onLoadFun: function(e) {
				this.getUserInfo();
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			/**
			 * 获取个人用户信息
			 */
			getUserInfo: function() {
				let that = this;
				getUserInfo().then(res => {
					let tel = res.data.phone;
					let phone = tel.substr(0, 3) + "****" + tel.substr(7);
					that.$set(that, 'userInfo', res.data);
					that.phone = phone;
				});
			},
			/**
			 * 发送验证码
			 * 
			 */
			async code() {
				let that = this;
				if (!that.userInfo.phone) return that.$util.Tips({
					title: that.$t(`手机号码不存在,无法发送验证码！`)
				});
				this.$refs.verify.show()

			},
			async success(data) {
				let that = this;
				this.$refs.verify.hide()
				await registerVerify({
					phone: that.userInfo.phone,
					type: 'reset',
					key: that.key,
					captchaType: this.captchaType,
					captchaVerification: data.captchaVerification
				}).then(res => {
					this.sendCode()
					that.$util.Tips({
						title: res.msg
					});
				}).catch(err => {
					return that.$util.Tips({
						title: err
					});
				});
			},
			/**
			 * H5登录 修改密码
			 * 
			 */
			editPwd: function(e) {
				let that = this,
					password = e.detail.value.password,
					qr_password = e.detail.value.qr_password,
					captcha = e.detail.value.captcha;
				if (!password) return that.$util.Tips({
					title: that.$t(`请输入新密码`)
				});
				if (qr_password != password) return that.$util.Tips({
					title: that.$t(`两次输入的密码不一致！`)
				});
				if (!captcha) return that.$util.Tips({
					title: that.$t(`请输入验证码`)
				});
				phoneRegisterReset({
					account: that.userInfo.phone,
					captcha: captcha,
					password: password
				}).then(res => {
					return that.$util.Tips({
						title: res.msg
					}, {
						tab: 3,
						url: 1
					});
				}).catch(err => {
					return that.$util.Tips({
						title: err
					});
				});
			}
		}
	}
</script>

<style lang="scss">
	page {
		background-color: #fff !important;
	}

	.ChangePassword .phone {
		font-size: 32rpx;
		font-weight: bold;
		text-align: center;
		margin-top: 55rpx;
	}

	.ChangePassword .list {
		width: 580rpx;
		margin: 53rpx auto 0 auto;
	}

	.ChangePassword .list .item {
		width: 100%;
		height: 110rpx;
		border-bottom: 2rpx solid #f0f0f0;
	}

	.ChangePassword .list .item input {
		width: 100%;
		height: 100%;
		font-size: 32rpx;
	}

	.ChangePassword .list .item .placeholder {
		color: #b9b9bc;
	}

	.ChangePassword .list .item input.codeIput {
		width: 340rpx;
	}

	.ChangePassword .list .item .code {
		font-size: 32rpx;
		background-color: #fff;
	}

	.ChangePassword .list .item .code.on {
		color: #b9b9bc !important;
	}

	.ChangePassword .confirmBnt {
		font-size: 32rpx;
		width: 580rpx;
		height: 90rpx;
		border-radius: 45rpx;
		color: #fff;
		margin: 92rpx auto 0 auto;
		text-align: center;
		line-height: 90rpx;
	}
</style>
