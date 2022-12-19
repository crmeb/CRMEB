<template>
  <div>
    <Modal v-model="isTemplate" title="推荐人信息" width="45%" @on-cancel="cancel">
      <div class="Modals">
        <div class="header acea-row row-middle">
          <div class="pictrue"><img :src="spread.avatar" /></div>
          <div class="name">{{ spread.nickname }}</div>
        </div>
        <div class="list">
          <div class="item acea-row row-middle">
            <div class="name money ivu-form-item-content">
              金额：<span>{{ spread.now_money }}</span>
            </div>
            <div class="name ivu-form-item-content">
              UID：<span>{{ spread.uid }}</span>
            </div>
          </div>
          <div class="item acea-row row-middle">
            <div class="name commission ivu-form-item-content">
              佣金：<span>{{ spread.brokerage_pric }}</span>
            </div>
            <div class="name ivu-form-item-content">
              真实姓名：<span>{{ spread.real_name }}</span>
            </div>
          </div>
          <div class="item acea-row row-middle">
            <div class="name ivu-form-item-content">
              身份证：<span>{{ spread.card_id }}</span>
            </div>
            <div class="name ivu-form-item-content">
              手机号码：<span>{{ spread.phone }}</span>
            </div>
          </div>
          <div class="item acea-row row-middle">
            <div class="name ivu-form-item-content">
              生日：<span>{{ spread.birthday }}</span>
            </div>
            <div class="name ivu-form-item-content">
              积分：<span>{{ spread.integral }}</span>
            </div>
          </div>
          <div class="item acea-row row-middle">
            <div class="name ivu-form-item-content">
              用户备注：<span>{{ spread.mark }}</span>
            </div>
            <div class="name ivu-form-item-content">
              最后登录时间：<span>{{ spread.last_time }}</span>
            </div>
          </div>
        </div>
      </div>
      <div slot="footer"></div>
      <Spin size="large" fix v-if="spinShow"></Spin>
    </Modal>
  </div>
</template>

<script>
import { verifySpreadInfoApi } from '@/api/setting';
export default {
  name: 'referrerInfo',
  data() {
    return {
      isTemplate: false,
      spread: {},
      spinShow: false,
    };
  },
  mounted() {},
  methods: {
    verifySpreadInfo(uid) {
      let that = this;
      that.spinShow = true;
      verifySpreadInfoApi(uid)
        .then((res) => {
          that.spinShow = false;
          that.spread = res.data.spread;
        })
        .catch((res) => {
          that.$Message.error(res.msg);
        });
    },
    cancel() {},
  },
};
</script>

<style scoped lang="stylus">
.Modals
   width 100%
   border 1px solid #e8eaec
.Modals .header
    background-color #f5f5f5
    padding 10px 15px
.Modals .header .pictrue
    width 50px
    height 50px
    border-radius 50%
.Modals .header .pictrue img
    width 100%
    height 100%
    border-radius 50%
.Modals .header .name
    color #333
    margin-left 15px
.Modals .list .item .name.money
    color #ff0005 !important
.Modals .list .item .name.commission
    color green !important
.Modals .list .item
    border-top 1px solid #e8eaec
.Modals .list .item .name
    padding 10px 15px
    width 50%
</style>
