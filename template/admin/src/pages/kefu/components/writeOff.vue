<template>
  <div v-show="iShidden">
    <div class="WriteOff">
      <div class="pictrue"><img :src="orderInfo.cartInfo[0].productInfo.image" /></div>
      <div class="num acea-row row-center-wrapper">
        {{ orderInfo.order_id }}
        <div class="views" @click="toDetail(orderInfo)">
          查看<span class="iconfont icon-jiantou views-jian"></span>
        </div>
      </div>
      <div class="tip">确定要核销此订单吗？</div>
      <div class="sure" @click="confirm">确定核销</div>
      <div class="sure cancel" @click="cancel">取消</div>
    </div>
    <div class="maskModel" @touchmove.prevent></div>
  </div>
</template>
<script>
export default {
  name: "WriteOff",
  props: {
    iShidden: {
      type: Boolean,
      default: true
    },
    orderInfo: {
      type: Object,
      default: null
    }
  },
  data: function() {
    return {};
  },
  methods: {
    toDetail: function(item) {
      this.$router.push({
        path: "/kefu/orderDetail/" + item.id + "/looks"
      });
    },
    cancel: function() {
      this.$emit("cancel", false);
    },
    confirm: function() {
      this.$emit("confirm", false);
    }
  }
};
</script>
<style scoped>
  .views {
    font-size: 0.16rem;
    background: #c68937;
    border-radius: 4px;
    color: #fff;
    padding: 0.05rem 0.02rem 0.05rem 0.08rem;
    margin-left: 0.1rem;
  }
  .views-jian {
    font-size: 0.1rem;
  }
  .WriteOff {
    width: 5.6rem;
    height: 8rem;
    background-color: #fff;
    border-radius: 0.2rem;
    position: fixed;
    top: 50%;
    left: 50%;
    margin-top: -4rem;
    margin-left: -2.8rem;
    z-index: 99;
    padding-top: 0.55rem;
  }
  .WriteOff .pictrue {
    width: 3.4rem;
    height: 3.4rem;
    margin: 0 auto;
  }
  .WriteOff .pictrue img {
    width: 100%;
    height: 100%;
    display: block;
    border-radius: 0.1rem;
  }
  .WriteOff .num {
    font-size: 0.3rem;
    color: #666;
    margin: 0.28rem 0 0.3rem 0;
  }
  .WriteOff .num .see {
    font-size: 0.16rem;
    color: #fff;
    border-radius: 0.04rem;
    background-color: #c68937;
    padding-left: 0.05rem;
    margin-left: 0.12rem;
  }
  .WriteOff .num .see .iconfont {
    font-size: 0.15rem;
  }
  .WriteOff .tip {
    font-size: 0.36rem;
    color: #282828;
    text-align: center;
    border-top: 1px dashed #ccc;
    padding-top: 0.4rem;
    position: relative;
  }
  .WriteOff .tip:after {
    content: "";
    position: absolute;
    width: 0.25rem;
    height: 0.25rem;
    border-radius: 50%;
    background-color: #7f7f7f;
    right: -0.125rem;
    top: -0.125rem;
  }
  .WriteOff .tip:before {
    content: "";
    position: absolute;
    width: 0.25rem;
    height: 0.25rem;
    border-radius: 50%;
    background-color: #7f7f7f;
    left: -0.125rem;
    top: -0.125rem;
  }
  .WriteOff .sure {
    font-size: 0.32rem;
    color: #fff;
    text-align: center;
    line-height: 0.82rem;
    height: 0.82rem;
    width: 4.6rem;
    border-radius: 0.41rem;
    margin: 0.4rem auto 0 auto;
    background-image: linear-gradient(to right, #f67a38 0%, #f11b09 100%);
    background-image: -webkit-linear-gradient(to right, #f67a38 0%, #f11b09 100%);
    background-image: -moz-linear-gradient(to right, #f67a38 0%, #f11b09 100%);
  }
  .WriteOff .sure.cancel {
    background-image: none;
    color: #999;
    margin-top: 0.1rem;
  }
</style>
