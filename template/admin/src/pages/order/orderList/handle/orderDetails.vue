<template>
  <div>
    <el-drawer title="订单详情" :size="1000" :visible.sync="modals" wrapperClosable :before-close="handleClose">
      <div v-if="orderDatalist">
        <div class="head">
          <div class="full">
            <img class="order_icon" :src="orderImg" alt="" />
            <div class="text">
              <div class="title">普通订单</div>
              <div>
                <span class="mr20">订单号：{{ orderDatalist.orderInfo.order_id }}</span>
              </div>
            </div>
          </div>
          <ul class="list">
            <li class="item">
              <div class="title">订单状态</div>
              <div>
                {{ orderDatalist.orderInfo._status._title }}
                {{
                  orderDatalist.orderInfo.refund &&
                  orderDatalist.orderInfo.refund.length &&
                  orderDatalist.orderInfo.refund_status < 2
                    ? orderDatalist.orderInfo.is_all_refund
                      ? '退款中'
                      : '部分退款中'
                    : ''
                }}
              </div>
            </li>
            <li class="item">
              <div class="title">实际支付</div>
              <div>¥ {{ orderDatalist.orderInfo.pay_price || '0.0' }}</div>
            </li>
            <li class="item" v-if="orderDatalist.orderInfo.refund_status == 2">
              <div class="title">实际退款</div>
              <div>¥ {{ orderDatalist.orderInfo.refunded_price || '0.0' }}</div>
            </li>
            <li class="item">
              <div class="title">支付方式</div>
              <div>{{ orderDatalist.orderInfo.pay_type | payType }}</div>
            </li>
            <li class="item">
              <div class="title">支付时间</div>
              <div>{{ orderDatalist.orderInfo._pay_time }}</div>
            </li>
          </ul>
        </div>
        <el-tabs type="border-card" v-model="activeName" @tab-click="tabClick">
          <el-tab-pane label="订单信息" name="detail">
            <div class="section">
              <div class="title">用户信息</div>
              <ul class="list">
                <li class="item">
                  <div>用户名称：</div>
                  <div class="value">{{ orderDatalist.userInfo.real_name }}</div>
                </li>
                <li class="item">
                  <div>绑定电话：</div>
                  <div class="value">{{ orderDatalist.orderInfo.user_phone || '' }}</div>
                </li>
              </ul>
            </div>
            <div class="section">
              <div class="title">收货信息</div>
              <ul class="list">
                <!-- <li class="item">
                  <div>收货信息：</div>
                  <div class="value">{{ orderDatalist.orderInfo.user_address || '' }}</div>
                </li> -->
                <li class="item">
                  <div>收货人：</div>
                  <div class="value">
                    {{ orderDatalist.orderInfo.real_name ? orderDatalist.orderInfo.real_name : '-' }}
                  </div>
                </li>
                <li class="item">
                  <div>收货电话：</div>
                  <div class="value">
                    {{ orderDatalist.orderInfo.user_phone ? orderDatalist.orderInfo.user_phone : '-' }}
                  </div>
                </li>
                <li class="item">
                  <div>收货地址：</div>
                  <div class="value">
                    {{ orderDatalist.orderInfo.user_address ? orderDatalist.orderInfo.user_address : '-' }}
                  </div>
                </li>
              </ul>
            </div>
            <div class="section">
              <div class="title">订单信息</div>
              <ul class="list">
                <li class="item">
                  <div>创建时间：</div>
                  <div class="value">{{ orderDatalist.orderInfo._add_time }}</div>
                </li>
                <li class="item">
                  <div>商品总数：</div>
                  <div class="value">{{ orderDatalist.orderInfo.total_num }}</div>
                </li>
                <li class="item">
                  <div>商品总价：</div>
                  <div class="value">{{ orderDatalist.orderInfo.total_price }}</div>
                </li>
                <li class="item">
                  <div>优惠券金额：</div>
                  <div class="value">{{ orderDatalist.orderInfo.coupon_price }}</div>
                </li>
                <li class="item">
                  <div>积分抵扣：</div>
                  <div class="value">{{ orderDatalist.orderInfo.deduction_price || '0.0' }}</div>
                </li>
                <li class="item">
                  <div>交付邮费：</div>
                  <div class="value">{{ orderDatalist.orderInfo.pay_postage }}</div>
                </li>
                <li class="item">
                  <div>用户等级优惠：</div>
                  <div class="value">{{ orderDatalist.orderInfo.levelPrice || '0.0' }}</div>
                </li>
                <li class="item">
                  <div>付费会员优惠：</div>
                  <div class="value">{{ orderDatalist.orderInfo.memberPrice || '0.0' }}</div>
                </li>
                <li class="item">
                  <div>实际支付：</div>
                  <div class="value">{{ orderDatalist.orderInfo.pay_price || '0.0' }}</div>
                </li>
              </ul>
            </div>
            <div class="section" v-if="orderDatalist.orderInfo.delivery_name">
              <div class="title">
                {{ orderDatalist.orderInfo.delivery_type == 'express' ? '物流信息' : '送货人信息' }}
              </div>
              <ul class="list">
                <li class="item">
                  <div>{{ orderDatalist.orderInfo.delivery_type == 'express' ? '物流公司：' : '送货人姓名：' }}</div>
                  <div class="value">
                    {{ orderDatalist.orderInfo.delivery_name ? orderDatalist.orderInfo.delivery_name : '-' }}
                  </div>
                </li>
                <li class="item">
                  <div>{{ orderDatalist.orderInfo.delivery_type == 'express' ? '物流单号：' : '送货人电话：' }}</div>
                  <div class="value">
                    {{ orderDatalist.orderInfo.delivery_id }}
                    <a v-if="orderDatalist.orderInfo.delivery_type == 'express'" v-db-click @click="openLogistics"
                      >物流查询</a
                    >
                  </div>
                </li>
              </ul>
            </div>
            <div class="section" v-if="orderDatalist.orderInfo.invoice">
              <div class="title">发票信息</div>
              <ul class="list">
                <li class="item">
                  <div>发票抬头：</div>
                  <div class="value">
                    {{ orderDatalist.orderInfo.invoice.name }}
                  </div>
                </li>
                <li
                  class="item"
                  v-if="orderDatalist.orderInfo.invoice.header_type === 2 && orderDatalist.orderInfo.invoice.type === 1"
                >
                  <div>企业税号：</div>
                  <div class="value">{{ orderDatalist.orderInfo.invoice.duty_number }}</div>
                </li>
                <li
                  class="item"
                  v-if="orderDatalist.orderInfo.invoice.header_type === 2 && orderDatalist.orderInfo.invoice.type === 1"
                >
                  <div>发票类型：</div>
                  <div class="value">电子普通发票</div>
                </li>
                <li
                  class="item"
                  v-if="orderDatalist.orderInfo.invoice.header_type === 2 && orderDatalist.orderInfo.invoice.type === 1"
                >
                  <div>发票抬头类型：</div>
                  <div class="value">企业</div>
                </li>
                <li
                  class="item"
                  v-if="orderDatalist.orderInfo.invoice.header_type === 1 && orderDatalist.orderInfo.invoice.type === 1"
                >
                  <div>真实姓名：</div>
                  <div class="value">{{ orderDatalist.orderInfo.invoice.name || '' }}</div>
                </li>
                <li
                  class="item"
                  v-if="orderDatalist.orderInfo.invoice.header_type === 1 && orderDatalist.orderInfo.invoice.type === 1"
                >
                  <div>联系电话：</div>
                  <div class="value">{{ orderDatalist.orderInfo.invoice.drawer_phone || '' }}</div>
                </li>
                <li
                  class="item"
                  v-if="orderDatalist.orderInfo.invoice.header_type === 2 && orderDatalist.orderInfo.invoice.type === 1"
                >
                  <div>联系电话：</div>
                  <div class="value">{{ orderDatalist.orderInfo.invoice.user_phone || '' }}</div>
                </li>
                <li
                  class="item"
                  v-if="orderDatalist.orderInfo.invoice.header_type === 2 && orderDatalist.orderInfo.invoice.type === 1"
                >
                  <div>联系邮箱：</div>
                  <div class="value">{{ orderDatalist.orderInfo.invoice.email || '' }}</div>
                </li>
                <li
                  class="item"
                  v-if="orderDatalist.orderInfo.invoice.header_type === 2 && orderDatalist.orderInfo.invoice.type === 1"
                >
                  <div>开票状态：</div>
                  <div class="value">{{ orderDatalist.orderInfo.invoice.is_invoice ? '已开票' : '未开票' }}</div>
                </li>
              </ul>
            </div>
            <div class="section">
              <div class="title">买家留言</div>
              <ul class="list">
                <li class="item">
                  <div>{{ orderDatalist.orderInfo.mark ? orderDatalist.orderInfo.mark : '-' }}</div>
                </li>
              </ul>
            </div>
            <div class="section" v-if="orderDatalist.orderInfo.custom_form.length">
              <div class="title">表单信息</div>
              <ul class="list">
                <li
                  class="item"
                  :class="{ pic: item.label == 'img' }"
                  :span="item.label !== 'text' ? 12 : 24"
                  v-for="(item, index) in orderDatalist.orderInfo.custom_form"
                  :key="index"
                >
                  <template v-if="item.label !== 'img'">
                    <div>{{ item.title }}：{{ item.value }}</div>
                  </template>
                  <template v-else>
                    <div>{{ item.title }}：</div>
                    <div v-for="(img, i) in item.value" :key="i" class="img">
                      <img v-viewer :src="img" alt="" />
                    </div>
                  </template>
                </li>
              </ul>
            </div>
            <div class="section">
              <div class="title">订单备注</div>
              <ul class="list">
                <li class="item">
                  <div>{{ orderDatalist.orderInfo.remark ? orderDatalist.orderInfo.remark : '-' }}</div>
                </li>
              </ul>
            </div>
          </el-tab-pane>
          <el-tab-pane label="商品信息" name="goods">
            <el-table class="mt20" :data="orderDatalist.orderInfo.cartInfo">
              <el-table-column label="商品信息" min-width="300">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="demo-image__preview">
                      <el-image
                        :src="
                          scope.row.productInfo.attrInfo
                            ? scope.row.productInfo.attrInfo.image
                            : scope.row.productInfo.image
                        "
                        :preview-src-list="[scope.row.productInfo.attrInfo.image]"
                      />
                    </div>
                    <div>
                      <div class="line">{{ scope.row.productInfo.store_name }}</div>
                      <div class="line1 gary">
                        规格：{{ scope.row.productInfo.attrInfo ? scope.row.productInfo.attrInfo.suk : '默认' }}
                      </div>
                    </div>
                  </div>
                </template>
              </el-table-column>
              <el-table-column label="支付价格" min-width="90">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="line1">
                      {{ scope.row.truePrice }}
                    </div>
                  </div>
                </template>
              </el-table-column>
              <el-table-column label="购买数量" min-width="90">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="line1">
                      {{ scope.row.cart_num }}
                    </div>
                  </div>
                </template>
              </el-table-column>
              <!-- <el-table-column label="库存" min-width="70">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="line1">
                      {{ scope.row.productInfo.stock }}
                    </div>
                  </div>
                </template>
              </el-table-column> -->
            </el-table>
          </el-tab-pane>
          <el-tab-pane label="订单记录" name="orderList">
            <el-table class="mt20" :data="recordData" v-loading="loading" empty-text="暂无数据" highlight-current-row>
              <el-table-column label="订单ID" min-width="100">
                <template slot-scope="scope">
                  <span>{{ scope.row.oid }}</span>
                </template>
              </el-table-column>
              <el-table-column label="操作记录" min-width="100">
                <template slot-scope="scope">
                  <span>{{ scope.row.change_message }}</span>
                </template>
              </el-table-column>
              <el-table-column label="操作时间" min-width="100">
                <template slot-scope="scope">
                  <span>{{ scope.row.change_time }}</span>
                </template>
              </el-table-column>
            </el-table>
          </el-tab-pane>
        </el-tabs>
      </div>
    </el-drawer>
    <el-drawer :visible.sync="modal2" scrollable title="物流查询" width="350px" class="order_box2">
      <div class="logistics acea-row row-top" v-if="orderDatalist">
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
          <el-timeline>
            <el-timeline-item v-for="(item, i) in result" :key="i">
              <p class="time" v-text="item.time"></p>
              <p class="content" v-text="item.status"></p>
            </el-timeline-item>
          </el-timeline>
        </div>
      </div>
    </el-drawer>
  </div>
</template>
<script>
import { getExpress } from '@/api/order';
import { getOrderRecord } from '@/api/order';
export default {
  name: 'orderDetails',
  data() {
    return {
      activeName: 'detail',
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
      orderImg: require('@/assets/images/order_icon.png'),
      recordData: [],
      page: {
        page: 1, // 当前页
        limit: 15, // 每页显示条数
      },
      loading: false,
    };
  },
  props: {
    orderDatalist: {
      type: Object,
      default: () => {
        orderInfo: {
        }
      },
    },
    orderId: Number,
    is_refund: {
      type: Number,
      default: 0,
    },
  },
  watch: {
    modals(val) {
      if (val) {
        this.activeName = 'detail';
      }
    },
  },
  filters: {
    payType(val) {
      let obj = {
        yue: '余额',
        weixin: '微信支付',
        alipay: '支付宝支付',
        offline: '线下支付',
      };
      return obj[val];
    },
  },
  methods: {
    openLogistics() {
      this.getOrderData();
    },
    // 获取订单物流信息
    getOrderData() {
      getExpress(this.orderId)
        .then(async (res) => {
          this.result = res.data.result;
          this.modal2 = true;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    tabClick(tab) {
      if (tab.name == 'orderList') {
        this.getRecordList();
      }
    },
    handleClose() {
      this.modals = false;
    },
    getRecordList() {
      let data = {
        id: this.is_refund ? this.orderDatalist.orderInfo.store_order_id : this.orderDatalist.orderInfo.id,
        datas: this.page,
      };
      this.loading = true;
      getOrderRecord(data)
        .then(async (res) => {
          this.recordData = res.data;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
  },
};
</script>
<style lang="scss" scoped>
::v-deep .el-tabs--border-card > .el-tabs__header .el-tabs__item.is-active {
  border-bottom: none;
}
::v-deep .el-tabs__item {
  height: 40px !important;
  line-height: 40px !important;
}
.head {
  padding: 0 30px 24px;

  .full {
    display: flex;
    align-items: center;
    .order_icon {
      width: 60px;
      height: 60px;
    }
    .iconfont {
      color: var(--prev-color-primary);
      &.sale-after {
        color: #90add5;
      }
    }
    .text {
      align-self: center;
      flex: 1;
      min-width: 0;
      padding-left: 12px;
      font-size: 13px;
      color: #606266;
      .title {
        margin-bottom: 10px;
        font-weight: 500;
        font-size: 16px;
        line-height: 16px;
        color: rgba(0, 0, 0, 0.85);
      }
      .order-num {
        padding-top: 10px;
        white-space: nowrap;
      }
    }
  }
  .list {
    display: flex;
    margin-top: 20px;
    overflow: hidden;
    list-style: none;
    padding: 0;
    .item {
      flex: none;
      width: 200px;
      font-size: 14px;
      line-height: 14px;
      color: rgba(0, 0, 0, 0.85);
      .title {
        margin-bottom: 12px;
        font-size: 13px;
        line-height: 13px;
        color: #666666;
      }
      .value1 {
        color: #f56022;
      }

      .value2 {
        color: #1bbe6b;
      }

      .value3 {
        color: var(--prev-color-primary);
      }

      .value4 {
        color: #6a7b9d;
      }

      .value5 {
        color: #f5222d;
      }
    }
  }
}
.section {
  padding: 25px 0;
  border-bottom: 1px dashed #eeeeee;
  .title {
    padding-left: 10px;
    border-left: 3px solid var(--prev-color-primary);
    font-size: 15px;
    line-height: 15px;
    color: #303133;
  }
  .list {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
  }
  .item {
    flex: 0 0 calc(100% / 3);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: #666666;
    &:nth-child(3n + 1) {
      padding-right: 20px;
    }

    &:nth-child(3n + 2) {
      padding-right: 10px;
      padding-left: 10px;
    }

    &:nth-child(3n + 3) {
      padding-left: 20px;
    }
  }
  .value {
    flex: 1;
    image {
      display: inline-block;
      width: 40px;
      height: 40px;
      margin: 0 12px 12px 0;
      vertical-align: middle;
    }
  }
  .item.pic {
    display: flex;
    img {
      width: 80px;
      height: 80px;
    }
  }
}
.tab {
  display: flex;
  align-items: center;
  .el-image {
    width: 36px;
    height: 36px;
    margin-right: 10px;
  }
}
::v-deep .el-drawer__body {
  // padding: 0;
  overflow: auto;
}
.gary {
  color: #aaa;
}
::v-deep .el-drawer__body {
  padding: 20px 0;
}
::v-deep .el-tabs--border-card > .el-tabs__content {
  padding: 0 35px;
}
::v-deep .el-tabs--border-card > .el-tabs__header,
::v-deep .el-tabs--border-card > .el-tabs__header .el-tabs__item:active {
  border: none;
  height: 40px;
}
::v-deep .el-tabs--border-card > .el-tabs__header .el-tabs__item.is-active {
  border: none;
  border-top: 2px solid var(--prev-color-primary);
  font-size: 13px;
  font-weight: 500;
  color: #303133;
  line-height: 16px;
}
::v-deep .el-tabs--border-card > .el-tabs__header .el-tabs__item {
  border: none;
}
::v-deep .el-tabs--border-card > .el-tabs__header .el-tabs__item {
  margin-top: 0;
  transition: none;
  height: 40px !important;
  line-height: 40px !important;
  width: 92px !important;
  font-size: 13px;
  font-weight: 400;
  color: #303133;
  line-height: 16px;
}
::v-deep .el-tabs--border-card {
  border: none;
  box-shadow: none;
}

.logistics {
  align-items: center;
  padding: 10px 20px;

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
</style>
