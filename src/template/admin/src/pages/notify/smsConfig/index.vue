<template>
  <div>
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title" v-if="!isShowList">短信账户</span>
        <div v-if="isShowList" class="acea-row row-between-wrapper picTxt">
          <div slot="content">
            <Avatar class="dashboard-workplace-header-avatar" :src="imgUrl" />
            <div class="dashboard-workplace-header-tip">
              <p class="dashboard-workplace-header-tip-title">{{smsAccount}}，祝您每一天开心！</p>
              <p class="dashboard-workplace-header-tip-desc">
                <a href="#" @click="onChangePassswordIndex">修改密码</a>
                <Divider type="vertical" />
                <a href="#" @click="onChangePhone">修改手机号</a>
                <Divider type="vertical" />
                <a href="#" @click="signOut">退出登录</a>
              </p>
            </div>
          </div>
          <div class="dashboard">
            <div class="dashboard-workplace-header-extra">
              <div class="acea-row">
                <div class="header-extra">
                  <p class="mb5"><span>短信条数</span></p>
                  <Button size="small" type="primary" v-if="sms.open ===0" @click="onOpen('sms')">开通服务</Button>
                  <div v-else>
                    <p>{{sms.num || 0}}</p>
                    <Button size="small" type="primary" class="mt3" @click="mealPay('sms')">套餐购买</Button>
                  </div>
                </div>
                <div class="header-extra">
                  <p class="mb5"><span>采集次数</span></p>
                  <Button size="small" type="primary" v-if="copy.open ===0" @click="onOpen('copy')">开通服务</Button>
                  <div v-else>
                    <p>{{copy.num || 0}}</p>
                    <Button size="small" type="primary" class="mt3" @click="mealPay('copy')">套餐购买</Button>
                  </div>
                </div>
                <div class="header-extra">
                  <p class="mb5"><span>物流查询次数</span></p>
                  <Button size="small" type="primary" v-if="query.open ===0" @click="onOpen('query')">开通服务</Button>
                  <div v-else>
                    <p>{{query.num || 0}}</p>
                    <Button size="small" type="primary" class="mt3" @click="mealPay('expr_query')">套餐购买</Button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <login-from @on-change="onChangePasssword" v-if="isShowLogn" @on-changes="onChangeReg" @on-Login="onLogin"></login-from>
      <forget-password v-if="isShow" @goback="goback" @on-Login="onLogin" :isIndex="isIndex"></forget-password>
      <register-from v-if="isShowReg" @on-change="logoup"></register-from>
      <table-list ref="tableLists" v-if="isShowList" :sms="sms" :copy="copy" :query="query" :accountInfo="accountInfo" @openService="openService"></table-list>
      <forget-phone v-if="isForgetPhone" @gobackPhone="gobackPhone" @on-Login="onLogin"></forget-phone>
      <Spin size="large" fix v-if="spinShow"></Spin>
    </Card>
  </div>
</template>

<script>
  import loginFrom from './components/loginFrom';
  import forgetPassword from './components/forgetPassword';
  import registerFrom from './components/register';
  import tableList from './tableList';
  import forgetPhone from './components/forgetPhone';
  import { isLoginApi, logoutApi, smsNumberApi, serveInfoApi } from '@/api/setting';
  export default {
    name: 'smsConfig',
    components: { loginFrom, forgetPassword, registerFrom, tableList, forgetPhone },
    data () {
      return {
        imgUrl: require('@/assets/images/ren.png'),
        spinShow: false,
        isShowLogn: false, // 登录
        isShow: false, // 修改密码
        isShowReg: false, // 注册
        isShowList: false, // 登录之后列表
        smsAccount: '',
        accountInfo:{},
        isForgetPhone: false, // 修改手机号
        isIndex: false, // 判断忘记密码返回的路径
        sms: { open: 0 }, // 短信信息
        query: { open: 0 }, // 物流查询
        copy: { open: 0 } // 商品采集
      }
    },
    created () {
      this.onIsLogin();
    },
    methods: {
      onChangePhone () {
        this.isForgetPhone = true
        this.isShowLogn = false;
        this.isShowList = false;
      },
      onOpen (val) {
        this.$refs.tableLists.onOpenIndex(val);
      },
      mealPay (val) {
        this.$router.push({ path:'/admin/setting/sms/sms_pay/index',query:{type:val}});
      },
      // 开通服务
      openService (val) {
        switch (val) {
          case 'sms':
            this.sms.open = 1
            break;
          case 'copy':
            this.copy.open = 1
            break;
          case 'query':
            this.query.open = 1
            break;
        }
      },
      // 平台用户信息
      getServeInfo () {
        this.spinShow = true;
        serveInfoApi().then(async res => {
          let data = res.data;
          this.sms = {
            num: data.sms.num,
            open: data.sms.open,
            surp: data.sms.open
          };
          this.query = {
            num: data.query.num,
            open: data.query.open,
            surp: data.query.open
          };
          this.copy = {
            num: data.copy.num,
            open: data.copy.open,
            surp: data.copy.open
          };
          this.spinShow = false;
          this.smsAccount = data.account;
          this.accountInfo = data;
        }).catch(res => {
          this.$Message.error(res.msg);
          this.isShowLogn = true;
          this.isShowList = false;
          this.spinShow = false;
        })
      },
      // 查看是否登录
      onIsLogin () {
        this.spinShow = true;
        isLoginApi().then(async res => {
          let data = res.data;
          this.isShowLogn = !data.status;
          this.isShowList = data.status;
          this.spinShow = false;
          if (data.status) {
            this.getServeInfo();
          }
        }).catch(res => {
          this.spinShow = false;
          this.$Message.error(res.msg);
        })
      },
      // 退出登录
      signOut () {
        logoutApi().then(async res => {
          this.isShowLogn = true;
          this.isShowList = false;
        }).catch(res => {
          this.$Message.error(res.msg);
        })
      },
      // 修改密码
      onChangePassswordIndex () {
        this.isIndex = true;
        this.passsword();
      },
      // 忘记密码
      onChangePasssword () {
        this.isIndex = false;
        this.passsword();
        // this.isShowLogn = false;
        // this.isShow = true;
        // this.isShowList = false;
      },
      passsword () {
        this.isShowLogn = false;
        this.isShow = true;
        this.isShowList = false;
      },

      // 立即注册
      onChangeReg () {
        this.isShowLogn = false;
        this.isShow = false;
        this.isShowReg = true;
      },
      // 立即登录
      logoup () {
        this.isShowLogn = true;
        this.isShow = false;
        this.isShowReg = false;
      },
      // 登录跳转
      onLogin () {
        let url = this.$route.query.url;
        if (url) {
          this.$router.replace(url);
        } else {
          this.isShowLogn = false;
          this.isShow = false;
          this.isShowReg = false;
          this.isForgetPhone = false;
          this.isShowList = true;
          this.getServeInfo();
        }
      },
      // 密码返回
      goback () {
        if (this.isIndex) {
          this.isShowList = true;
          this.isShow = false;
        } else {
          this.isShowLogn = true;
          this.isShow = false;
        }
      },
      // 手机号返回
      gobackPhone () {
        this.isShowList = true;
        this.isForgetPhone = false;
      }
    }
  }
</script>

<style lang="less" scoped>
  .picTxt{
    padding: 8px 0 12px;
  }
  .dashboard{
    width: auto !important;
    min-width: 300px;
  }
  .header-extra{
    /*width: 25%;*/
    border-right: 1px solid #E9E9E9;
    text-align: center;
    padding: 0 18px;
  }
  .page-account-top-tit{
    font-size: 21px;
    color: #1890FF;
  }
  .dashboard-workplace{
    &-header{
      &-avatar{
        width: 64px;
        height: 64px;
        border-radius: 50%;
        margin-right: 16px;
      }
      &-tip{
        display: inline-block;
        vertical-align: middle;
        &-title{
          font-size: 20px;
          font-weight: bold;
          margin-bottom: 12px;
        }
        &-desc{
          color: #808695;
        }
      }
      &-extra{
        width: 100% !important;
        .ivu-col{
          p{
            text-align: right;
          }
          p:first-child{
            span:first-child{
              margin-right: 4px;
            }
            span:last-child{
              color: #808695;
            }
          }
          p:last-child{
            font-size: 22px;
          }
        }
      }
    }
  }
  @aaa: ~'>>>';
  .conBox{
    @{aaa} .ivu-page-header-extra{
      width: auto !important;
      min-width: 457px;
    }
    @{aaa} .ivu-page-header{
      padding: 16px 0px 0 32px !important;
    }
  }
</style>
