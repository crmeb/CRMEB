<template>
  <div>
    <Table
      :columns="columns"
      :data="orderList"
      ref="table"
      :loading="loading"
      highlight-row
      no-data-text="暂无数据"
      no-filtered-data-text="暂无筛选结果"
      @on-selection-change="onSelectTab"
      @on-select-all="selectAll"
      @on-select-all-cancel="selectAll"
      @on-select-cancel="onSelectCancel"
      class="orderData mt25"
    >
      <template slot-scope="{ row, index }" slot="order_id">
        <span v-text="row.order_id" style="display: block"></span>
        <span v-show="row.is_del == 1" style="color: #ed4014; display: block">用户已删除</span>
      </template>
      <template slot-scope="{ row, index }" slot="nickname">
        <a @click="showUserInfo(row)">{{ row.nickname }}/{{ row.uid }}</a>
      </template>
      <template slot-scope="{ row, index }" slot="info">
        <div class="tabBox">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="row.image" />
          </div>
          <span class="tabBox_tit"> {{ row.store_name + ' | ' }}{{ row.suk ? row.suk : '' }} </span>
          <span class="tabBox_pice">{{ '积分' + row.total_price + ' x ' + row.total_num }}</span>
        </div>
      </template>
      <template slot-scope="{ row, index }" slot="action">
        <!--        <a @click="edit(row)" v-if="row.status === 1">编辑</a>-->
        <a @click="sendOrder(row)" v-if="row.status === 1">发送货</a>
        <a @click="delivery(row)" v-if="row.status === 2">配送信息</a>
        <Divider type="vertical" v-if="row.status === 1 || row.status === 2" />
        <template>
          <Dropdown @on-click="changeMenu(row, $event)">
            <a href="javascript:void(0)">
              更多
              <Icon type="ios-arrow-down"></Icon>
            </a>
            <DropdownMenu slot="list">
              <DropdownItem name="2">订单详情</DropdownItem>
              <DropdownItem name="3">订单记录</DropdownItem>
              <DropdownItem name="11" v-show="row.status >= 1 && row.express_dump">电子面单打印</DropdownItem>
              <DropdownItem name="10" v-show="row.status >= 1">小票打印</DropdownItem>
              <!-- <DropdownItem name="10" v-show="row._status >= 2">订单打印</DropdownItem> -->
              <DropdownItem name="4" v-show="row.status !== 4">订单备注</DropdownItem>
              <DropdownItem name="8" v-show="row.status === 2">已收货</DropdownItem>
              <!-- <DropdownItem name="9">删除订单</DropdownItem> -->
            </DropdownMenu>
          </Dropdown>
        </template>
      </template>
    </Table>
    <div class="acea-row row-right page">
      <Page
        :total="page.total"
        :current="page.pageNum"
        show-elevator
        show-total
        @on-change="pageChange"
        :page-size="page.pageSize"
        @on-page-size-change="limitChange"
        show-sizer
      />
    </div>
    <!-- 编辑 退款 退积分 不退款-->
    <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
    <!-- 会员详情-->
    <user-details ref="userDetails"></user-details>
    <!-- 详情 -->
    <details-from ref="detailss" :orderDatalist="orderDatalist" :orderId="orderId"></details-from>
    <!-- 备注 -->
    <order-remark ref="remarks" :orderId="orderId" @submitFail="submitFail"></order-remark>
    <!-- 记录 -->
    <order-record ref="record"></order-record>
    <!-- 发送货 -->
    <order-send ref="send" :orderId="orderId" @submitFail="submitFail"></order-send>
  </div>
</template>

<script>
import expandRow from './tableExpand.vue';
import {
  orderList,
  getOrdeDatas,
  getDataInfo,
  getRefundFrom,
  getnoRefund,
  refundIntegral,
  getDistribution,
  writeUpdate,
} from '@/api/order';
import { getIntegralOrderDataInfo, integralOrderList, getIntegralOrderDistribution } from '@/api/marketing';
import { mapState, mapMutations } from 'vuex';
import editFrom from '../../../../components/from/from';
import detailsFrom from '../handle/orderDetails';
import orderRemark from '../handle/orderRemark';
import orderRecord from '../handle/orderRecord';
import orderSend from '../handle/orderSend';
import userDetails from '@/pages/user/list/handle/userDetails';

export default {
  name: 'table_list',
  components: {
    expandRow,
    editFrom,
    detailsFrom,
    orderRemark,
    orderRecord,
    orderSend,
    userDetails,
  },
  props: ['where', 'isAll'],
  data() {
    return {
      delfromData: {},
      modal: false,
      orderList: [],
      orderCards: [],
      loading: false,
      orderId: 0,
      columns: [
        {
          type: 'expand',
          width: 30,
          render: (h, params) => {
            return h(expandRow, {
              props: {
                row: params.row,
              },
            });
          },
        },
        {
          title: '订单号',
          align: 'center',
          slot: 'order_id',
          minWidth: 150,
        },
        {
          title: '用户信息',
          slot: 'nickname',
          minWidth: 100,
        },
        {
          title: '商品信息',
          slot: 'info',
          minWidth: 330,
        },
        {
          title: '兑换积分',
          key: 'total_price',
          minWidth: 70,
        },
        {
          title: '订单状态',
          key: 'status_name',
          minWidth: 100,
        },
        {
          title: '下单时间',
          key: 'add_time',
          minWidth: 100,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 150,
          align: 'center',
        },
      ],
      page: {
        total: 0, // 总条数
        pageNum: 1, // 当前页
        pageSize: 10, // 每页显示条数
      },
      data: [],
      FromData: null,
      orderDatalist: null,
      modalTitleSs: '',
      isDelIdList: [],
      checkBox: false,
      formSelection: [],
      selectionCopy: [],
      display: 'none',
      autoDisabled: false,
      // isAll: -1,
    };
  },
  computed: {
    ...mapState('integralOrder', ['orderPayType', 'orderStatus', 'orderTime', 'orderNum', 'fieldKey', 'orderType']),
  },
  mounted() {
    this.getList();
  },
  activated() {
    this.getList();
  },
  watch: {
    orderType: function () {
      this.page.pageNum = 1;
      this.getList();
    },
    formSelection(value) {
      this.$emit('order-select', value);
      if (value.length) {
        this.$emit('auto-disabled', 0);
      } else {
        this.$emit('auto-disabled', 1);
      }
      let isDel = value.some((item) => {
        return item.is_del === 1;
      });
      this.getIsDel(isDel);
      this.getisDelIdListl(value);
    },
    orderList: {
      deep: true,
      handler(value) {
        value.forEach((item) => {
          this.formSelection.forEach((itm) => {
            if (itm.id === item.id) {
              item.checkBox = true;
            }
          });
        });
        const arr = this.orderList.filter((item) => item.checkBox);
        if (this.orderList.length) {
          this.checkBox = this.orderList.length === arr.length;
        } else {
          this.checkBox = false;
        }
      },
    },
  },
  methods: {
    ...mapMutations('integralOrder', ['getIsDel', 'getisDelIdListl']),
    selectAll(row) {
      if (row.length) {
        this.formSelection = row;
        this.selectionCopy = row;
      }
      this.selectionCopy.forEach((item, index) => {
        item.checkBox = this.checkBox;
        this.$set(this.orderList, index, item);
      });
    },
    showUserInfo(row) {
      this.$refs.userDetails.modals = true;
      this.$refs.userDetails.getDetails(row.uid);
    },
    // 操作
    changeMenu(row, name) {
      this.orderId = row.id;
      switch (name) {
        case '2':
          this.getData(row.id);
          break;
        case '3':
          this.$refs.record.modals = true;
          this.$refs.record.getList(row.id);
          break;
        case '4':
          this.$refs.remarks.modals = true;
          this.$refs.remarks.formValidate.remark = row.remark;
          break;
        case '5':
          this.getRefundData(row.id);
          break;
        case '6':
          this.getRefundIntegral(row.id);
          break;
        case '7':
          this.getNoRefundData(row.id);
          break;
        case '8':
          this.delfromData = {
            title: '修改确认收货',
            url: `marketing/integral/order/take/${row.id}`,
            method: 'put',
            ids: '',
          };
          this.$modalSure(this.delfromData)
            .then((res) => {
              this.$Message.success(res.msg);
              this.getList();
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
          // this.modalTitleSs = '修改确认收货';
          break;
        case '10':
          this.delfromData = {
            title: '立即打印订单',
            info: '您确认打印此订单吗?',
            url: `marketing/integral/order/print/${row.id}`,
            method: 'get',
            ids: '',
          };
          this.$modalSure(this.delfromData)
            .then((res) => {
              this.$Message.success(res.msg);
              this.$emit('changeGetTabs');
              this.getList();
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
          break;
        case '11':
          this.delfromData = {
            title: '立即打印电子面单',
            info: '您确认打印此电子面单吗?',
            url: `/order/order_dump/${row.id}`,
            method: 'get',
            ids: '',
          };
          this.$modalSure(this.delfromData)
            .then((res) => {
              this.$Message.success(res.msg);
              this.getList();
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
          break;
        default:
          this.delfromData = {
            title: '删除订单',
            url: `marketing/integral/order/del/${row.id}`,
            method: 'DELETE',
            ids: '',
          };
          // this.modalTitleSs = '删除订单';
          this.delOrder(row, this.delfromData);
      }
    },
    // 立即支付 /确认收货//删除单条订单
    submitModel() {
      this.getList();
    },
    pageChange(index) {
      this.page.pageNum = index;
      this.getList();
    },
    limitChange(limit) {
      this.page.pageSize = limit;
      this.getList();
    },
    // 订单列表
    getList(res) {
      this.page.pageNum = res === 1 ? 1 : this.page.pageNum;
      this.loading = true;
      integralOrderList({
        page: this.page.pageNum,
        limit: this.page.pageSize,
        status: this.orderStatus,
        pay_type: this.orderPayType,
        data: this.orderTime,
        real_name: this.orderNum,
        field_key: this.fieldKey,
        type: this.orderType === 0 ? '' : this.orderType,
        product_id: this.$route.query.product_id,
      })
        .then(async (res) => {
          let data = res.data;
          // this.orderList = data.data;
          this.orderList = data.data.map((item) => {
            // item.checkBox = false;
            if (this.isAll === 1) {
              item.checkBox = true;
            } else {
              item.checkBox = false;
            }
            return item;
          });
          this.orderCards = data.stat;
          this.page.total = data.count;
          this.$emit('on-changeCards', data.stat);
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    // 全选
    onSelectTab(selection) {
      this.formSelection = selection;
      let isDel = selection.some((item) => {
        return item.is_del === 1;
      });
      this.getIsDel(isDel);
      this.getisDelIdListl(selection);
    },
    // 编辑
    edit(row) {
      this.getOrderData(row.id);
    },
    // 删除单条订单
    delOrder(row, data) {
      if (row.is_del === 1) {
        this.$modalSure(data)
          .then((res) => {
            this.$Message.success(res.msg);
            this.getList();
          })
          .catch((res) => {
            this.$Message.error(res.msg);
          });
      } else {
        const title = '错误！';
        const content = '<p>您选择的的订单存在用户未删除的订单，无法删除用户未删除的订单！</p>';
        this.$Modal.error({
          title: title,
          content: content,
        });
      }
    },
    // 获取编辑表单数据
    getOrderData(id) {
      getOrdeDatas(id)
        .then(async (res) => {
          if (res.data.status === false) {
            return this.$authLapse(res.data);
          }
          this.$authLapse(res.data);
          this.FromData = res.data;
          this.$refs.edits.modals = true;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 获取详情表单数据
    getData(id) {
      getIntegralOrderDataInfo(id)
        .then(async (res) => {
          this.$refs.detailss.modals = true;
          this.orderDatalist = res.data;
          if (this.orderDatalist.orderInfo.refund_reason_wap_img) {
            try {
              this.orderDatalist.orderInfo.refund_reason_wap_img = JSON.parse(
                this.orderDatalist.orderInfo.refund_reason_wap_img,
              );
            } catch (e) {
              this.orderDatalist.orderInfo.refund_reason_wap_img = [];
            }
          }
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 修改成功
    submitFail() {
      this.$emit('updata');
      this.getList();
    },
    // 发送货
    sendOrder(row) {
      this.$refs.send.modals = true;
      this.$refs.send.getList();
      this.$refs.send.getDeliveryList();
      // this.$refs.send.getSheetInfo();
      this.orderId = row.id;
    },
    // 配送信息表单数据
    delivery(row) {
      getIntegralOrderDistribution(row.id)
        .then(async (res) => {
          this.FromData = res.data;
          this.$refs.edits.modals = true;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    change(status) {},
    // 数据导出；
    exportData: function () {
      this.$refs.table.exportCsv({
        filename: '商品列表',
      });
    },
    onSelectCancel(selection, row) {},
  },
};
</script>

<style scoped lang="stylus">
img {
  height: 36px;
  display: block;
}

.tabBox {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;

  .tabBox_img {
    width: 36px;
    height: 36px;

    img {
      width: 100%;
      height: 100%;
    }
  }

  .tabBox_tit {
    width: 60%;
    font-size: 12px !important;
    margin: 0 2px 0 10px;
    letter-spacing: 1px;
    padding: 5px 0;
    box-sizing: border-box;
  }
}

.orderData >>>.ivu-table-cell {
  padding-left: 0 !important;
}

.vertical-center-modal {
  display: flex;
  align-items: center;
  justify-content: center;
}

.orderData .ivu-table {
  overflow: visible !important;
}

.orderData .ivu-table th {
  overflow: visible !important;
}

.orderData .ivu-table-header {
  overflow: visible !important;
}

/deep/.ivu-table-header {
  // overflow: visible;
}

/deep/.ivu-table th {
  overflow: visible;
}

/deep/.select-item:hover {
  background-color: #f3f3f3;
}

/deep/.select-on {
  display: block;
}

/deep/.select-item.on {
  background: #f3f3f3;
}
</style>
