<template>
	<div class="register absolute">
		<div class="shading">
			<div class="pictrue acea-row row-center-wrapper">
				<image src="../static/logo2.png" />
			</div>
		</div>
		<div class="whiteBg">
			<div class="title">{{$t(`找回密码`)}}</div>
			<div class="list">
				<div class="item">
					<div class="acea-row row-middle">
						<image src="../static/phone_1.png"></image>
						<input type="text" :placeholder="$t(`输入手机号码`)" v-model="account" />
					</div>
				</div>
				<div class="item">

					<div class="acea-row row-middle">
						<image src="../static/code_2.png"></image>
						<input type="text" :placeholder="$t(`填写验证码`)" class="codeIput" v-model="captcha" />
						<button class="code" :disabled="disabled" :class="disabled === true ? 'on' : ''" @click="code">
							{{ text }}
						</button>
					</div>
				</div>
				<div class="item">
					<div class="acea-row row-middle">
						<image src="../static//code_2.png"></image>
						<input type="password" :placeholder="$t(`填写您的新密码`)" v-model="password" />
					</div>
				</div>
				<div class="item" v-if="isShowCode">
					<div class="align-left">
						<input type="text" :placeholder="$t(`填写验证码`)" class="codeIput" v-model="codeVal" />
						<div class="code" @click="again"><img :src="codeUrl" /></div>
					</div>
				</div>
			</div>
			<div class="logon" @click="registerReset">{{$t(`确认`)}}</div>
			<div class="tip">
				<span class="font-color-red" @click="back">{{$t(`立即登录`)}}</span>
			</div>
		</div>
		<div class="bottom"></div>
	</div>
</template>

<script>
	import sendVerifyCode from "@/mixins/SendVerifyCode";
	import {
		registerVerify,
		registerReset,
		getCodeApi
	} from "@/api/user";
	// import { validatorDefaultCatch } from "@/utils/dialog";
	// import attrs, { required, alpha_num, chs_phone } from "@utils/validate";
	// import { VUE_APP_API_URL } from "@utils";

	export default {
		name: "RetrievePassword",
		data: function() {
			return {
				account: "",
				password: "",
				captcha: "",
				keyCode: "",
				codeUrl: "",
				codeVal: "",
				isShowCode: false
			};
		},
		mixins: [sendVerifyCode],
		mounted: function() {
			this.getCode();
		},
		methods: {
			back() {
				uni.navigateBack();
			},
			again() {
				this.codeUrl =
					VUE_APP_API_URL + "/captcha?" + this.keyCode + Date.parse(new Date());
			},
			getCode() {
				getCodeApi()
					.then(res => {
						this.keyCode = res.data.key;
					})
					.catch(res => {
						this.$util.Tips({
							title: res.msg.msg || this.$t(`加载失败`)
						})
					});
			},
			async registerReset() {
				var that = this;
				if (!that.account) return that.$util.Tips({
					title: that.$t(`请填写手机号码`)
				});
				if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account)) return that.$util.Tips({
					title: that.$t(`请输入正确的手机号码`)
				});
				if (!that.captcha) return that.$util.Tips({
					title: that.$t(`请填写验证码`)
				});
				registerReset({
						account: that.account,
						captcha: that.captcha,
						password: that.password,
						code: that.codeVal
					})
					.then(res => {
						that.$util.Tips({
							title: res.msg
						}, {
							tab: 3
						})
					})
					.catch(res => {
						that.$util.Tips({
							title: res
						})
					});
			},
			async code() {
				let that = this;
				if (!that.account) return that.$util.Tips({
					title: that.$t(`请填写手机号码`)
				});
				if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account)) return that.$util.Tips({
					title: that.$t(`请输入正确的手机号码`)
				});
				if (that.formItem == 2) that.type = "register";
				await registerVerify({
						phone: that.account,
						type: that.type,
						key: that.keyCode,
						code: that.codeVal
					})
					.then(res => {
						this.$util.Tips({
							title: res.msg || that.$t(`加载失败`)
						})
						that.sendCode();
					})
					.catch(res => {
						that.$util.Tips({
							title: res
						});
					});
			},
		}
	};
</script>
<style scoped>
	.code img {
		width: 100%;
		height: 100%;
	}
</style>
