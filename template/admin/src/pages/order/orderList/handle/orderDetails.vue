<template>
  <div v-if="orderDatalist">
    <Drawer title="订单详情" :closable="false" width="700" v-model="modals" scrollable>
      <Card :bordered="false" dis-hover class="i-table-no-border">
        <div class="ivu-description-list-title">收货信息</div>
        <Row class="mb10">
          <Col span="12">用户昵称：{{ orderDatalist.userInfo.nickname }}</Col>
          <Col span="12">收货人：{{ orderDatalist.orderInfo.real_name }}</Col>
        </Row>
        <Row class="mb10">
          <Col span="12">联系电话：{{ orderDatalist.orderInfo.user_phone }}</Col>
          <Col span="12">收货地址：{{ orderDatalist.orderInfo.user_address }}</Col>
        </Row>
        <Divider style="margin: 20px 0 !important" />
        <div class="ivu-description-list-title">订单信息</div>
        <Row class="mb10">
          <Col span="12">订单ID：{{ orderDatalist.orderInfo.order_id }}</Col>
          <Col span="12" class="fontColor1">订单状态：{{ orderDatalist.orderInfo._status._title }}</Col>
        </Row>
        <Row class="mb10">
          <Col span="12">商品总数：{{ orderDatalist.orderInfo.total_num }}</Col>
          <Col span="12">商品总价：{{ orderDatalist.orderInfo.total_price }}</Col>
        </Row>
        <Row class="mb10">
          <Col span="12">交付邮费：{{ orderDatalist.orderInfo.pay_postage }}</Col>
          <Col span="12">优惠券金额：{{ orderDatalist.orderInfo.coupon_price }}</Col>
        </Row>
        <Row class="mb10">
          <Col span="12">用户等级优惠：{{ orderDatalist.orderInfo.levelPrice || 0.0 }}</Col>
          <Col span="12">付费会员优惠：{{ orderDatalist.orderInfo.memberPrice || 0.0 }}</Col>
        </Row>
        <Row class="mb10">
          <Col span="12">积分抵扣：{{ orderDatalist.orderInfo.deduction_price || 0.0 }}</Col>
          <Col span="12">实际支付：{{ orderDatalist.orderInfo.pay_price }}</Col>
        </Row>
        <Row class="mb10">
          <Col span="12" class="fontColor3 mb10" v-if="parseFloat(orderDatalist.orderInfo.refund_price)"
            >退款金额：{{ parseFloat(orderDatalist.orderInfo.refund_price) }}</Col
          >
          <Col span="12" class="fontColor3 mb10" v-if="parseFloat(orderDatalist.orderInfo.use_integral)"
            >使用积分：{{ parseFloat(orderDatalist.orderInfo.use_integral) }}</Col
          >
          <Col span="12" class="fontColor3 mb10" v-if="parseFloat(orderDatalist.orderInfo.back_integral)"
            >退回积分：{{ parseFloat(orderDatalist.orderInfo.back_integral) }}</Col
          >
          <Col span="12" class="fontColor3 mb10" v-if="parseFloat(orderDatalist.orderInfo.gain_integral)"
            >赠送积分：{{ parseFloat(orderDatalist.orderInfo.gain_integral) }}</Col
          >
          <Col span="12" class="mb10">创建时间：{{ orderDatalist.orderInfo._add_time }}</Col>
          <Col span="12" class="mb10">支付时间：{{ orderDatalist.orderInfo._pay_time }}</Col>
          <Col span="12" class="mb10">支付方式：{{ orderDatalist.orderInfo._status._payType }}</Col>
          <Col span="12" class="mb10" v-if="is_refund != 1"
            >推广人：{{ orderDatalist.orderInfo.spread_name + '/' + orderDatalist.orderInfo.spread_uid }}</Col
          >
          <Col
            span="12"
            class="mb10"
            v-if="
              orderDatalist.orderInfo.shipping_type === 2 &&
              orderDatalist.orderInfo.refund_status === 0 &&
              orderDatalist.orderInfo.paid === 1
            "
            >门店名称：{{ orderDatalist.orderInfo._store_name }}</Col
          >
          <Col
            span="12"
            class="mb10"
            v-if="
              orderDatalist.orderInfo.shipping_type === 2 &&
              orderDatalist.orderInfo.refund_status === 0 &&
              orderDatalist.orderInfo.paid === 1
            "
            >核销码：{{ orderDatalist.orderInfo.verify_code }}</Col
          >
          <Col span="24" class="mb10" v-if="orderDatalist.orderInfo.remark"
            >商家备注：{{ orderDatalist.orderInfo.remark }}</Col
          >
          <Col span="24" class="mb10" v-if="orderDatalist.orderInfo.fictitious_content"
            >虚拟发货备注：{{ orderDatalist.orderInfo.fictitious_content }}</Col
          >
        </Row>
        <Divider style="margin: 20px 0 !important" v-if="orderDatalist.orderInfo.custom_form.length" />
        <div class="ivu-description-list-title" v-if="orderDatalist.orderInfo.custom_form.length">表单信息</div>
        <Row class="mb10" v-if="orderDatalist.orderInfo.custom_form.length">
          <Col
            class="mb10"
            :span="item.label !== 'text' ? 12 : 24"
            v-if="item.label !== 'img'"
            v-for="(item, index) in orderDatalist.orderInfo.custom_form"
            :key="index"
            >{{ item.title }}：{{ item.value }}</Col
          >
          <Col span="24" v-else>
            <div>{{ item.title }}：</div>
            <div class="pic">
              <div v-for="(img, i) in item.value" :key="i" class="img">
                <img v-viewer :src="img" alt="" />
              </div>
            </div>
          </Col>
        </Row>
        <div v-if="orderDatalist.orderInfo.delivery_type === 'express'">
          <Divider style="margin: 20px 0 !important" />
          <div class="ivu-description-list-title">物流信息</div>
          <Row class="mb10">
            <Col span="12">快递公司：{{ orderDatalist.orderInfo.delivery_name }}</Col>
            <Col span="12"
              >快递单号：{{ orderDatalist.orderInfo.delivery_id }}
              <Button type="info" size="small" @click="openLogistics">物流查询</Button></Col
            >
          </Row>
        </div>
        <div v-if="orderDatalist.orderInfo.delivery_type === 'send'">
          <Divider style="margin: 20px 0 !important" />
          <div class="ivu-description-list-title">配送信息</div>
          <Row class="mb10">
            <Col span="12">送货人姓名：{{ orderDatalist.orderInfo.delivery_name }}</Col>
            <Col span="12">送货人电话：{{ orderDatalist.orderInfo.delivery_id }}</Col>
          </Row>
        </div>
        <div v-if="orderDatalist.orderInfo.mark">
          <Divider style="margin: 20px 0 !important" v-if="orderDatalist.orderInfo.mark" />
          <div class="ivu-description-list-title" v-if="orderDatalist.orderInfo.mark">备注信息</div>
          <Row class="mb10">
            <Col span="12" class="fontColor2">{{ orderDatalist.orderInfo.mark }}</Col>
          </Row>
        </div>
      </Card>
    </Drawer>
    <Modal v-model="modal2" scrollable title="物流查询" width="350" class="order_box2">
      <div class="logistics acea-row row-top">
        <div class="logistics_img">
          <img src="../../../../assets/images/expressi.jpg" />
        </div>
        <div class="logistics_cent">
          <span>物流公司：{{ orderDatalist.orderInfo.delivery_name }}</span>
          <span>物流单号：{{ orderDatalist.orderInfo.delivery_id }}</span>
        </div>
      </div>
      <div class="acea-row row-column-around trees-coadd">
        <div class="scollhide">
          <Timeline>
            <TimelineItem v-for="(item, i) in result" :key="i">
              <p class="time" v-text="item.time"></p>
              <p class="content" v-text="item.status"></p>
            </TimelineItem>
          </Timeline>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script>
import { getExpress } from '@/api/order';
export default {
  name: 'orderDetails',
  data() {
    return {
      modal2: false,
      modals: false,
      grid: {
        xl: 8,
        lg: 8,
        md: 12,
        sm: 24,
        xs: 24,
      },
      result: [],
    };
  },
  props: {
    orderDatalist: Object,
    orderId: Number,
    is_refund: Number,
  },
  methods: {
    openLogistics() {
      this.getOrderData();
      this.modal2 = true;
    },
    // 获取订单物流信息
    getOrderData() {
      getExpress(this.orderId)
        .then(async (res) => {
          this.result = res.data.result;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
  },
  computed: {},
};
</script>

<style scoped lang="stylus">
.ivu-description-list-title {
  margin-bottom: 16px;
  color: #17233d;
  font-weight: 500;
  font-size: 14px;
}

.logistics {
  align-items: center;
  padding: 10px 0px;

  .logistics_img {
    width: 45px;
    height: 45px;
    margin-right: 12px;

    img {
      width: 100%;
      height: 100%;
    }
  }

  .logistics_cent {
    span {
      display: block;
      font-size: 12px;
    }
  }
}

.trees-coadd {
  width: 100%;
  height: 400px;
  border-radius: 4px;
  overflow: hidden;

  .scollhide {
    width: 100%;
    height: 100%;
    overflow: auto;
    margin-left: 18px;
    padding: 10px 0 10px 0;
    box-sizing: border-box;

    .content {
      font-size: 12px;
    }

    .time {
      font-size: 12px;
      color: #2d8cf0;
    }
  }
}

.order_box2 {
  position: absolute;
  z-index: 999999999;
}

.order_box >>> .ivu-modal-header {
  padding: 30x 16px !important;
}

.order_box >>> .ivu-card {
  font-size: 12px !important;
}

.fontColor1 {
  color: red !important;
}

.fontColor2 {
  color: #733AF9 !important;
}

.order_box >>> .ivu-description-term {
  padding-bottom: 10px !important;
}

.order_box >>> .ivu-description-detail {
  padding-bottom: 10px !important;
}

.order_box >>> .ivu-modal-body {
  padding: 0 16px !important;
}

.fontColor3 {
  color: #f1a417 !important;
}

.fontColor3 {
  color: #f1a417 !important;
}

.pic {
  display: flex;
  flex-wrap: wrap;
}

.img {
  width: 100px;
  height: 100px;
  margin: 10px 10px;

  img {
    width: 100%;
    height: 100%;
  }
}
</style>
