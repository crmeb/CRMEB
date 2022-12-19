<template>
  <div class="feedback-wrapper">
    <div class="head">
      <div class="left-wrapper">
        <div class="title">商城客服已离线</div>
        <div class="txt">{{ feedback }}</div>
      </div>
      <div class="img-box"><img src="@/assets/images/feed-icon.png" mode="" /></div>
    </div>
    <div class="main">
      <div class="title">我要反馈</div>
      <div class="input-box">
        <Input type="text" placeholder="请输入您的姓名" v-model="name" />
      </div>
      <div class="input-box">
        <Input type="text" placeholder="请输入您的联系电话" v-model="phone" />
      </div>
      <div class="input-box">
        <Input type="textarea" placeholder="请填写内容" v-model="con" />
      </div>
      <Button class="sub_btn" @click="subMit" :disabled="isDisabled">提交</Button>
    </div>
  </div>
</template>

<script>
import { feedbackDataApi, feedbackFromApi } from '@/api/kefu.js';
export default {
  name: 'feedback',
  data() {
    return {
      name: '',
      phone: '',
      con: '',
      feedback: '',
      isDisabled: false,
    };
  },
  created() {
    this.getInfo();
  },
  methods: {
    getInfo() {
      feedbackDataApi().then((res) => {
        this.feedback = res.data.feedback;
      });
    },
    subMit() {
      if (!this.name) {
        return this.$Message.error('请填写姓名');
      }
      if (!this.phone || !/^1(3|4|5|7|8|9|6)\d{9}$/i.test(this.phone)) {
        return this.$Message.error('请填写正确的手机号码');
      }
      if (!this.con) {
        return this.$Message.error('请填写内容');
      }
      this.isDisabled = true;
      feedbackFromApi({
        rela_name: this.name,
        phone: this.phone,
        content: this.con,
      })
        .then((res) => {
          this.$Message.success(res.msg);
          this.$router.go(-1);
        })
        .catch((error) => {
          this.$Message.error(error.msg);
        });
    },
  },
};
</script>

<style lang="stylus">
.feedback-wrapper
	.head
		display flex
		align-items center
		justify-content space-between
		height 2.15rem
		padding 0 .3rem
		background-color #3A3A3A
		.left-wrapper
			width  4.56rem
			color #fff
			font-size .24rem
			.title
				margin-bottom .15rem
				font-size .32rem
		.img-box
			img
				width 1.73rem
				height 1.56rem
	.info
		display flex
		background-color #fff
		.info-item
			flex 1
			display flex
			flex-direction column
			align-items center
			justify-content center
			height 1.38rem
			border-right 1px solid #F0F1F2
			&:last-child
				border:none
			.big-txt
				font-size .32rem
				font-weight bold
				color #282828
			.small
				margin-top .1rem
				font-size .24rem
				color #9F9F9F
	.main
		margin-top .16rem
		padding .3rem .3rem .68rem
		background-color #FFF
		.title
			font-size .3rem
			font-weight bold
		.input-box
			margin-top .2rem
			input
				display block
				width 100%
				height .78rem
				background #F5F5F5
				font-size .28rem
				padding-left .2rem
			textarea
				display block
				width 100%
				height 2.6rem
				padding .2rem
				background #F5F5F5
				font-size .28rem
				resize none
		.sub_btn
			margin-top 1.3rem
			width 100%
			height .86rem
			font-size .3rem
			text-align center
			color #fff
			border-radius .43rem
			background-color #3875EA
</style>
