<template>
  <div>
    <!-- 修复升级 -->
    <Modal
      v-model="upgrade"
      width="390"
      height="96"
      :closable="false"
      class-name="vertical-center-modal"
      :mask-closable="false"
    >
      <p slot="header" class="header-modal3">
        <img src="../../assets/images/bg3.png" alt="" />
      </p>
      <div class="describe">
        <h2>您有新的修复版本可升级</h2>
        <p>更多惊喜内容等你来探索，快来看看吧～</p>
      </div>
      <div slot="footer" class="footer">
        <Button class="cancel" shape="circle" @click="upgrade = false">暂不升级</Button>
        <Button shape="circle" type="primary" @click="upgradeNow()">立即升级</Button>
      </div>
    </Modal>
  </div>
</template>
<script>
export default {
  data() {
    return {
      upgrade: true,
    };
  },
  methods: {
    upgradeNow() {
      this.$router.push({
        path: '/admin/system/onlineUpgrade/index',
        params: { items: true },
      });
      this.$store.commit('upgrade/TOGGLE_STATUS', true);
      sessionStorage.setItem('status', true);
    },
  },
};
</script>
<style scoped lang="stylus">
  .ivu-modal-content {

  }
  .primary{
    position absolute
    right 0
    bottom  0
  }
  .upgrade .header{
    font-size:12px;
    color: #000;
  }
  .upgrade .header .v{
    color: #1890FF;
    margin-left: 10px;
  }
  .upgrade .header .num{
    color: #1890FF;
    font-size: 24px;
  }
  .upgrade .header .info{
    color: #999999;
  }
  .upgrade .header .title{
    color: #999999;
    display: flex;
  }
  .upgrade .header .info ul{
    color: #999999;
    display: flex;
    margin-left: 40px;
  }
  .upgrade .header .info ul li::marker{
    color: red;
  }
  .upgrade .header .info ul li+li{
    margin-left: 40px;
  }
  .upgrade .contentTime .list{
    width 85%;
  }
  .upgrade .contentTime .info{
    font-size :12px!important;
    color :#999!important;
    margin-top :13px;
  }
  .upgrade .contentTime .info li{
    list-style-type: disc;
    margin-left: 16px;
    line-height: 26px;
  }
  .upgrade .contentTime .collapse{
    width 100%;
  }
  .upgrade .contentTime .ivu-collapse{
    border :0!important;
  }
  .upgrade .contentTime .ivu-collapse > .ivu-collapse-item > .ivu-collapse-header{
    height: unset!important;
    line-height: unset!important;
    border :0!important;
    font-size :16px!important;
    color :#333333;
    font-weight:600;
  }
  .upgrade .contentTime .ivu-collapse > .ivu-collapse-item > .ivu-collapse-header > i{
    color :#BBBBBB!important;
    margin-left :9px;
  }
  .upgrade .contentTime .ivu-collapse > .ivu-collapse-item.ivu-collapse-item-active > .ivu-collapse-header > i{
    transform: rotate(180deg);
  }
  .upgrade .contentTime{
    margin-top :30px;
    position: relative;
  }
  .upgrade .contentTime .btn{
    position: absolute;
    top: 0;
    right: 85px;
    z-index: 99;
  }
  .upgrade .contentTime .time{
    font-size: 14px;
    color: #999;
    text-align: right;
    padding-right: 28px;
  }
  .upgrade .contentTime .ivu-timeline-item:after{
    content: ' ';
    position: absolute;
    top:0;
    left:7px;
    width: 1px;
    height: 100%;
    background-color: #e8eaec;
  }
  .upgrade .contentTime .ivu-timeline-item-head-custom{
    z-index: 2;
    font-size 16px;
    color #DDDCDD;
  }
  .upgrade .contentTime .on .ivu-timeline-item-head-custom{
    color #1890FF;
    font-size 18px;
  }
  .upgrade .contentTime .on .ivu-collapse > .ivu-collapse-item > .ivu-collapse-header{
    color #1890FF;
  }
  .upgrade .contentTime .off .ivu-timeline-item-head-custom{
    color #1890FF;
    font-size 18px;
  }
  .upgrade .contentTime .off .time{
    color #1890FF;
  }
  /deep/.ivu-modal-header {
    padding: 0;
    border-radius: 10px;
    border: none;
  }

  .ivu-modal-header p, .ivu-modal-header-inner {
    height: 96px;
    color: #fff;
    line-height: 96px;
    font-size: 24px;
  }
  .ivu-modal-header p {
    position: relative;
      img {
        width: 100%;
        height: 100%;
        position: absolute;
        top: -20px;
        left: 0;
      }
  }
  .describe {
    text-align: center;
    padding-top: 30px;
    .success {
      font-size: 20px;
      color: #333333;
      margin-top: 10px;
    }
  }
  .ivu-modal-footer {
    border: none;
    display: flex;
    justify-content: space-around;

  }
  .ivu-btn {
    width: 128px;
    height: 40px;
    border: none;
  }
  .cancel {
    background: #EEEEEE;
  }
  .vertical-center-modal {
    display: flex;
    align-items: center;
    justify-content: center;

    .ivu-modal {
      top: 0;
    }
  }
  .footer {
    display: flex;
    justify-content: space-around;
  }
  .demo-i-circle-custom{
    & h1{
        color: #CCCCCC;
        font-size: 12px;
        font-weight: normal;
        line-height: 17px;
    }
    & p{
        color: #2A7EFB;
        font-size: 14px;
        line-height: 20px;
        margin: 6px 0;
    }
    & span{
        display: block;
        color: #CCCCCC;
        font-size: 12px;
        line-height: 17px;
    }
    & span i{
        font-style: normal;
        color: #3f414d;
    }
}
    .proportion {
      font-size: 20px;
      font-weight: 600;
      color: #2A7EFB;
      margin: 12px 0 6px;
    }
    .wait {
      font-size: 12px;
      font-weight: 400;
      color: #999999;
    }
    .footer2 {
      display: flex;
      flex-wrap: wrap;
      flex-direction: column; {}
      .confirm, .back {
        width: 210px;
        height: 40px;
      }
      .back {
        border:none;
      }
    }


  .header-modal3 {
    // background: url('../../assets/images/bg3.png') no-repeat;
    background-size: 100% 100%;
    background-position-y: -20px;
    text-align: center;
  }
  .ivu-modal-header .header-modal3{
    height: 169px;
    line-height: 74px;
    font-size: 20px;
    font-weight: 500;
    overflow: revert;
  }
  .describe {
    padding-top: 0;
  }
  .describe h2{
    font-size: 22px;
    font-weight: 400;
    color: #333333;
    margin-bottom: 12px;
  }

  .vertical-center-modal .ivu-modal {
    top: 0;
  }
</style>
