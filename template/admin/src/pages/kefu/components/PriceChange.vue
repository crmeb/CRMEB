<template>
  <div>
    <div class="priceChange" :class="change === true ? 'on' : ''">
      <div class="priceTitle">
        {{ status === 0 ? (orderInfo.refund_status === 1 ? '立即退款' : '一键改价') : '订单备注' }}
        <span class="iconfontYI icon-guanbi" @click="close"></span>
      </div>
      <div class="listChange" v-if="status === 0">
        <div class="item acea-row row-between-wrapper" v-if="orderInfo.refund_status === 0">
          <div>商品总价(¥)</div>
          <div class="money">{{ orderInfo.total_price }}<span class="iconfontYI icon-suozi"></span></div>
        </div>
        <div class="item acea-row row-between-wrapper" v-if="orderInfo.refund_status === 0">
          <div>原始邮费(¥)</div>
          <div class="money">{{ orderInfo.pay_postage }}<span class="iconfontYI icon-suozi"></span></div>
        </div>
        <div class="item acea-row row-between-wrapper" v-if="orderInfo.refund_status === 0">
          <div>实际支付(¥)</div>
          <div class="money">
            <input type="text" v-model="price" :class="focus === true ? 'on' : ''" @focus="priceChange" />
          </div>
        </div>
        <div class="item acea-row row-between-wrapper" v-if="orderInfo.refund_status === 1">
          <div>实际支付(¥)</div>
          <div class="money">{{ orderInfo.pay_price }}<span class="iconfontYI icon-suozi"></span></div>
        </div>
        <div class="item acea-row row-between-wrapper" v-if="orderInfo.refund_status === 1">
          <div>退款金额(¥)</div>
          <div class="money">
            <input type="text" v-model="refund_price" :class="focus === true ? 'on' : ''" @focus="priceChange" />
          </div>
        </div>
      </div>
      <div class="listChange" v-else>
        <textarea
          :placeholder="orderInfo.remark ? orderInfo.remark : '请填写备注信息...'"
          v-model="remark"
          maxlength="100"
        ></textarea>
      </div>
      <div class="modify" @click="save">
        {{ orderInfo.refund_status === 0 || status === 1 ? '立即修改' : '确认退款' }}
      </div>
      <div class="modify1" @click="refuse" v-if="orderInfo.refund_status === 1 && status === 0">拒绝退款</div>
    </div>
    <div class="maskModel" @touchmove.prevent v-show="change === true"></div>
  </div>
</template>
<script>
import { required, num } from '@/utils/validate';
import { validatorDefaultCatch } from '@/libs/dialog';
import { orderRemark, editPriceApi, orderRefundApi } from '@/api/kefu';
export default {
  name: 'PriceChange',
  components: {},
  props: {
    change: Boolean,
    orderInfo: {
      type: Object,
      default: null,
    },
    status: {
      type: Number,
      default: 0,
    },
  },
  data: function () {
    return {
      focus: false,
      price: 0,
      refund_price: 0,
      remark: '',
    };
  },
  watch: {
    orderInfo: function () {
      this.price = this.orderInfo.pay_price;
      this.refund_price = this.orderInfo.pay_price;
      this.remark = this.orderInfo.remark;
    },
  },
  methods: {
    priceChange: function () {
      this.focus = true;
    },
    close: function () {
      this.price = this.orderInfo.pay_price;
      this.$emit('closeChange', false);
    },
    save() {
      this.savePrice({
        price: this.price,
        refund_price: this.refund_price,
        type: 1,
        remark: this.remark,
        id: this.orderInfo.id,
        order_id: this.orderInfo.order_id,
      });
    },
    async savePrice(opt) {
      let that = this,
        data = {},
        price = opt.price,
        refund_price = opt.refund_price,
        refund_status = that.orderInfo.refund_status,
        remark = opt.remark;
      if (that.status == 0 && refund_status === 0) {
        try {
          await this.$validator({
            price: [required(required.message('金额')), num(num.message('金额'))],
          }).validate({ price });
        } catch (e) {
          return validatorDefaultCatch(e);
        }
        data.total_price = this.orderInfo.total_price;
        data.total_postage = this.orderInfo.total_price;
        data.pay_postage = this.orderInfo.pay_postage;
        data.gain_integral = this.orderInfo.gain_integral;
        data.pay_price = opt.price;
        data.order_id = opt.order_id;
        editPriceApi(opt.id, data)
          .then(() => {
            this.$emit('closechange', false);
            that.$dialog.success('改价成功');
          })
          .catch((error) => {
            that.$dialog.error(error.msg);
          });
      } else if (that.status == 0 && refund_status === 1) {
        try {
          await this.$validator({
            refund_price: [required(required.message('金额')), num(num.message('金额'))],
          }).validate({ refund_price });
        } catch (e) {
          return validatorDefaultCatch(e);
        }
        data.price = opt.refund_price;
        data.type = opt.type;
        data.order_id = opt.order_id;
        orderRefundApi(data).then(
          (res) => {
            this.$emit('closechange', false);
            that.$dialog.success('操作成功');
          },
          (err) => {
            this.$emit('closechange', false);
            that.$dialog.error(err.msg);
          },
        );
      } else {
        try {
          await this.$validator({
            remark: [required(required.message('备注'))],
          }).validate({ remark });
        } catch (e) {
          return validatorDefaultCatch(e);
        }
        data.remark = remark;
        data.order_id = opt.order_id;
        orderRemark(data).then(
          (res) => {
            this.$emit('closechange', false);
            that.$dialog.success('提交成功');
          },
          (err) => {
            this.$emit('closechange', false);
            that.$dialog.error(err.msg);
          },
        );
      }
    },
    refuse() {
      this.savePrice({
        price: this.price,
        refund_price: this.refund_price,
        type: 2,
        remark: this.remark,
        id: this.orderInfo.id,
        order_id: this.orderInfo.order_id,
      });
    },
  },
};
</script>
<style scoped>
input {
  display: block;
  height: 100%;
  background: none;
  color: inherit;
  opacity: 1;
  -webkit-text-fill-color: currentcolor;
  font: inherit;
  line-height: inherit;
  letter-spacing: inherit;
  text-align: inherit;
  text-indent: inherit;
  text-transform: inherit;
  text-shadow: inherit;
  border: none;
}
/*@import '../../../styles/reset.css';*/
.priceChange {
  position: fixed;
  width: 5.8rem;
  height: 6.7rem;
  background-color: #fff;
  border-radius: 0.1rem;
  top: 50%;
  left: 50%;
  margin-left: -2.9rem;
  margin-top: -3.35rem;
  z-index: 99;
  transition: all 0.3s ease-in-out 0s;
  -webkit-transition: all 0.3s ease-in-out 0s;
  -o-transition: all 0.3s ease-in-out 0s;
  -moz-transition: all 0.3s ease-in-out 0s;
  -webkit-transform: scale(0);
  -o-transform: scale(0);
  -moz-transform: scale(0);
  -ms-transform: scale(0);
  transform: scale(0);
  opacity: 0;
}
.priceChange.on {
  opacity: 1;
  transform: scale(1);
  -webkit-transform: scale(1);
  -o-transform: scale(1);
  -moz-transform: scale(1);
  -ms-transform: scale(1);
}
.priceChange .priceTitle {
  background: url('../../../assets/images/pricetitle.jpg') no-repeat;
  background-size: 100% 100%;
  width: 100%;
  height: 1.6rem;
  border-radius: 0.1rem 0.1rem 0 0;
  text-align: center;
  font-size: 0.4rem;
  color: #fff;
  line-height: 1.6rem;
  position: relative;
}
.priceChange .priceTitle .iconfontYI {
  position: absolute;
  font-size: 0.4rem;
  right: 0.26rem;
  top: 0.23rem;
  width: 0.4rem;
  height: 0.4rem;
  line-height: 0.4rem;
}
.priceChange .listChange {
  padding: 0 0.4rem;
}
.priceChange .listChange .item {
  height: 1.03rem;
  border-bottom: 1px solid #e3e3e3;
  font-size: 0.32rem;
  color: #333;
}
.priceChange .listChange .item .money {
  color: #666;
  width: 3rem;
  text-align: right;
}
.priceChange .listChange .item .money .iconfontYI {
  font-size: 0.32rem;
  margin-left: 0.2rem;
}
.priceChange .listChange .item .money input {
  width: 100%;
  height: 100%;
  text-align: right;
  color: #ccc;
  border: none;
}
.priceChange .listChange .item .money input.on {
  color: #666;
}
.priceChange .modify {
  font-size: 0.32rem;
  color: #fff;
  width: 4.9rem;
  height: 0.9rem;
  text-align: center;
  line-height: 0.9rem;
  border-radius: 0.45rem;
  background-color: #2291f8;
  margin: 0.53rem auto 0 auto;
}
.priceChange .modify1 {
  font-size: 0.32rem;
  color: #312b2b;
  width: 4.9rem;
  height: 0.9rem;
  text-align: center;
  line-height: 0.9rem;
  border-radius: 0.45rem;
  background-color: #eee;
  margin: 0.3rem auto 0 auto;
}
.priceChange .listChange textarea {
  border: 1px solid #eee;
  width: 100%;
  height: 2rem;
  margin-top: 0.5rem;
  border-radius: 0.1rem;
  color: #333;
  padding: 0.2rem;
  font-size: 0.3rem;
}
</style>
