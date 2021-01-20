<template>
	<view class="register absolute">
	    <view class="shading">
	      <view class="pictrue acea-row row-center-wrapper">
			 <image src="/static/images/logo2.png"></image>
	      </view>
	    </view>
	    <view class="whiteBg">
	      <view class="title">找回密码</view>
	      <view class="list">
	        <view class="item">
	          <view class="acea-row row-middle">
				<image src="/static/images/phone_1.png"></image>
	            <input type="text" placeholder="输入手机号码" placeholder-class="placeholder" v-model="account" class="input"/>
	          </view>
	        </view>
	        <view class="item">
	          <view class="align-left acea-row row-middle">
				<image src="/static/images/code_2.png"></image>
	            <input type="text" placeholder="填写验证码" class="codeIput" v-model="captcha" placeholder-class="placeholder"/>
	            <button class="code" :disabled="disabled" :class="disabled === true ? 'on' : ''" @click="code">
	              {{ text }}
	            </button>
	          </view>
	        </view>
	        <view class="item">
	          <view class="acea-row row-middle">
	            <image src="/static/images/code_1.png"></image>
	            <input type="password" placeholder="填写您的登录密码" v-model="password" placeholder-class="placeholder" class="input"/>
	          </view>
	        </view>
	      </view>
	      <view class="logon" @click="registerReset">确认</view>
	      <navigator url="/pages/users/login/index" class="tip">
			<text class="font-color">立即登录</text>
	      </navigator>
	    </view>
	    <view class="bottom"></view>
	  </view>
</template>

<script>
	import sendVerifyCode from "@/mixins/SendVerifyCode";
	import { registerVerify, registerReset } from "@/api/user";
	export default {
	  data() {
	    return {
	      account: "",
	      password: "",
	      captcha: ""
	    };
	  },
	  mixins: [sendVerifyCode],
	  methods: {
	    registerReset() {
	      let that = this;
		  if (!that.account) return that.$util.Tips({
		  	 title: '请填写手机号码'
		  });
		  if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account)) return that.$util.Tips({
		  	title: '请输入正确的手机号码'
		  });
		  if (!that.captcha) return that.$util.Tips({
		  	title: '请填写验证码'
		  });
		  if (!/^[\w\d]+$/i.test(that.captcha)) return that.$util.Tips({
		  	title: '请输入正确的验证码'
		  });
		  if (!that.password) return that.$util.Tips({
		  	title: '请填写密码'
		  });
		  if (!/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/i.test(that.password)) return that.$util.Tips({
		  	title: '您输入的密码过于简单'
		  });
		  registerReset({
		          account: that.account,
		          captcha: that.captcha,
		          password: that.password
		        })
		          .then(res => {
					  that.$util.Tips({
					    title: res.msg,
					    success: () => {
							uni.navigateTo({
							    url: '/pages/login/index'
							});
					    }
					  });
		          })
		          .catch(res => {
					  console.log(res);
					  that.$util.Tips({
					  	title: res.msg
					  });
		          });
	    },
	     async code() {
	         let that = this;
			 if (!that.account) return that.$util.Tips({
			 	title: '请填写手机号码'
			 });
			 if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account)) return that.$util.Tips({
			 	title: '请输入正确的手机号码'
			 });
	         registerVerify({ phone: that.account })
	           .then(res => {
	             that.$util.Tips({
	             	title: res.msg
	             });
	             that.sendCode();
	           })
	           .catch(res => {
	             that.$util.Tips({
	             	title: res
	             });
	           });
	       }
	  }
	};
</script>

<style>
</style>
