<template>
  <div>
    <el-table
      :data="orderList"
      ref="table"
      v-loading="loading"
      highlight-current-row
      empty-text="暂无数据"
      @select="selectAll"
      @select-all="selectAll"
      class="orderData"
    >
      <!-- <el-table-column type="selection" width="55"> </el-table-column> -->
      <el-table-column label="订单号" min-width="150">
        <template slot-scope="scope">
          <span v-text="scope.row.order_id" style="display: block"></span>
          <span v-if="scope.row.is_del == 1" style="color: #ed4014; display: block">用户已删除</span>
        </template>
      </el-table-column>
      <el-table-column label="用户信息" min-width="100">
        <template slot-scope="scope"> {{ scope.row.nickname }}/{{ scope.row.uid }} </template>
      </el-table-column>
      <el-table-column label="商品信息" min-width="330">
        <template slot-scope="scope">
          <div class="tabBox">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
            <span class="tabBox_tit"> {{ scope.row.store_name + ' | ' }}{{ scope.row.suk ? scope.row.suk : '' }} </span>
            <span class="tabBox_pice">{{ '积分' + scope.row.total_price + ' x ' + scope.row.total_num }}</span>
          </div>
        </template>
      </el-table-column>
      <el-table-column label="兑换积分" min-width="100">
        <template slot-scope="scope">
          <span>{{ scope.row.total_price }}</span>
        </template>
      </el-table-column>
      <el-table-column label="订单状态" min-width="100">
        <template slot-scope="scope">
          <span>{{ scope.row.status_name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="下单时间" min-width="100">
        <template slot-scope="scope">
          <span>{{ scope.row.add_time }}</span>
        </template>
      </el-table-column>
      <el-table-column label="操作" fixed="right" width="150">
        <template slot-scope="scope">
          <a v-db-click @click="sendOrder(scope.row)" v-if="scope.row.status === 1">发送货</a>
          <a v-db-click @click="delivery(scope.row)" v-if="scope.row.status === 2">配送信息</a>
          <el-divider direction="vertical" v-if="scope.row.status === 1 || scope.row.status === 2" />
          <template>
            <el-dropdown size="small" @command="changeMenu(scope.row, $event)" :transfer="true">
              <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>

              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item command="2">订单详情</el-dropdown-item>
                <el-dropdown-item command="3">订单记录</el-dropdown-item>
                <el-dropdown-item command="11" v-show="scope.row.status >= 1 && scope.row.express_dump"
                  >电子面单打印</el-dropdown-item
                >
                <!-- <el-dropdown-item command="10" v-show="scope.row.status >= 1">小票打印</el-dropdown-item> -->
                <!-- <el-dropdown-item name="10" v-show="scope.row._status >= 2">订单打印</el-dropdown-item> -->
                <el-dropdown-item command="4" v-show="scope.row.status !== 4">订单备注</el-dropdown-item>
                <el-dropdown-item command="8" v-show="scope.row.status === 2">已收货</el-dropdown-item>
                <el-dropdown-item command="9" v-show="scope.row.is_del === 1">删除订单</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </template>
        </template>
      </el-table-column>
    </el-table>
    <div class="acea-row row-right page">
      <pagination
        v-if="total"
        :total="total"
        :page.sync="page.pageNum"
        :limit.sync="page.pageSize"
        @pagination="getList"
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
      total: 0, // 总条数
      page: {
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
              this.$message.success(res.msg);
              this.getList();
            })
            .catch((res) => {
              this.$message.error(res.msg);
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
              this.$message.success(res.msg);
              this.$emit('changeGetTabs');
              this.getList();
            })
            .catch((res) => {
              this.$message.error(res.msg);
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
              this.$message.success(res.msg);
              this.getList();
            })
            .catch((res) => {
              this.$message.error(res.msg);
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
          this.total = data.count;
          this.$emit('on-changeCards', data.stat);
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
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
            this.$message.success(res.msg);
            this.getList();
          })
          .catch((res) => {
            this.$message.error(res.msg);
          });
      } else {
        this.$message.error('您选择的的订单存在用户未删除的订单，无法删除用户未删除的订单！');
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
          this.$message.error(res.msg);
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
          this.$message.error(res.msg);
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
          this.$message.error(res.msg);
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

<style lang="scss" scoped>
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
.orderData ::v-deep .ivu-table-cell {
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
::v-deep .ivu-table-header {
}
::v-deep .ivu-table th {
  overflow: visible;
}
::v-deep .select-item:hover {
  background-color: #f3f3f3;
}
::v-deep .select-on {
  display: block;
}
::v-deep .select-item.on {
  background: #f3f3f3;
}
</style>
