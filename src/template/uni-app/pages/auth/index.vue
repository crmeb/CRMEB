<!-- #ifdef H5 -->
<template>
	<view v-if="bindPhone">
		<form @submit="editPwd" report-submit='true'>
			<view class="ChangePassword">
				<view class="list">
					<view class="item">
						<input type='number' placeholder='填写手机号码' placeholder-class='placeholder' v-model="phone"></input>
					</view>
					<view class="item acea-row row-between-wrapper">
						<input type='number' placeholder='填写验证码' placeholder-class='placeholder' class="codeIput" v-model="captcha"></input>
						<button class="code font-color" :class="disabled === true ? 'on' : ''" :disabled='disabled' @click="code">
							{{ text }}
						</button>
					</view>
				</view>
				<button form-type="submit" class="confirmBnt bg-color">确认绑定</button>
			</view>
		</form>
	</view>
	<view class="lottie-bg" v-else>
		<view id="lottie">
			<image src="/static/img/live-logo.gif" rel="preload" />
		</view>
	</view>
</template>

<script>
	import wechat from "@/libs/wechat";
	import sendVerifyCode from "@/mixins/SendVerifyCode";
	import {
		getUserInfo
	} from "@/api/user";
	import {
		WX_AUTH,
		STATE_KEY,
		LOGINTYPE,
		BACK_URL
	} from '@/config/cache';
	import { silenceAuth } from '@/api/public';
	import {
		registerVerify,
		bindingPhone,
		verifyCode
	} from '@/api/api.js';
	export default {
		name: "Auth",
		mixins: [sendVerifyCode],
		data() {
			return {
				phone:'',
				captcha:'',
				key: '',
				authKey:'',
				scope: '',
				bindPhone: false,
			};
		},
		mounted() {

		},
		onLoad(options) {

			let that = this
			const {
				code,
				state,
				scope
			} = options;
			if (scope === 'snsapi_base') {
				this.url = options.url || options.back_url || '';
				let spread = this.$Cache.get("spread");
				let loginType = this.$Cache.get(LOGINTYPE);
				silenceAuth({ code : options.code || '',spread : spread }).then( res => {
					if (res.data.key !== undefined) {
						this.bindPhone = true;
						this.authKey = res.data.key;
					} else {
						this.$store.commit("LOGIN", {
							token: res.data.token,
							time: parseInt(res.data.expires_time) - this.$Cache.time()
						});
						this.$Cache.set(WX_AUTH, options.code);
						this.$Cache.clear(STATE_KEY);
						loginType && this.$Cache.clear(LOGINTYPE);
						location.href = decodeURIComponent(
							decodeURIComponent(options.back_url)
						);
					}
				})
			} else {
				wechat.auth(code, state).then(() => {
					location.href = decodeURIComponent(
						decodeURIComponent(options.back_url)
					);
					getUserInfo().then(res => {
						that.$store.commit("SETUID", res.data.uid);			
					});
				}).catch(() => {
					location.replace("/");
				});
			}
		},
		methods: {
			editPwd: function() {
				let that = this;
				if (!that.phone) {
					return that.$util.Tips({
						title: '请填写手机号码！'
					});
				}
				if (!(/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.phone))) {
					return that.$util.Tips({
						title: '请输入正确的手机号码！'
					});
				}
				if (!that.captcha) {
					return that.$util.Tips({
						title: '请填写验证码'
					});
				}
				bindingPhone({
					phone: that.phone,
					captcha: that.captcha,
					key: this.authKey
				}).then(res => {
					let time = res.data.expires_time - this.$Cache.time();
					this.$store.commit('LOGIN', { token : res.data.token, time : time });
					if (this.url && this.url.indexOf('http') !== -1) {
						location.href = this.url;
					} else {
						return that.$util.Tips({
							title: '绑定成功！',
							icon: 'success'
						}, {
							tab: 4,
							url: '/pages/index/index'
						});
					}
				}).catch(err => {
					return that.$util.Tips({
						title: err
					});
				})
			},
			/**
			 * 发送验证码
			 * 
			 */
			code() {
				let that = this;
				if (!that.phone) return that.$util.Tips({
					title: '请填写手机号码！'
				});
				if (!(/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.phone))) return that.$util.Tips({
					title: '请输入正确的手机号码！'
				});
				verifyCode().then(res => {
					registerVerify(that.phone, 'reset', res.data.key, that.captcha).then(res => {
						that.$util.Tips({
							title: res.msg
						});
						that.sendCode();
					}).catch(err => {
						return that.$util.Tips({
							title: err
						});
					});
				});
				
			}
		}
	};
</script>

<style scoped lang="scss">
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
	.lottie-bg {
		position: fixed;
		left: 0;
		top: 0;
		background-color: #fff;
		width: 100%;
		height: 100%;
		z-index: 999;

		display: flex;
		align-items: center;
		justify-content: center;
	}

	#lottie {
		display: block;
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		overflow: hidden;
		transform: translate3d(0, 0, 0);
		margin: auto;

		image {
			width: 200rpx;
			height: 200rpx;
		}
	}
</style>
<!-- #endif -->