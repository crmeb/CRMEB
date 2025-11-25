<template>
  <el-dialog
    :visible.sync="modals"
    title="订单退款"
    class="order_box"
    :show-close="true"
    width="1000px"
    @closed="changeModal"
  >
    <el-form
      v-if="modals"
      ref="formItem"
      :rules="ruleValidate"
      :model="formItem"
      label-width="100px"
      @submit.native.prevent
    >
      <el-form-item label="订单号：">
        <el-input v-model="order_id" disabled placeholder="请输入订单号" style="width: 60%"></el-input>
      </el-form-item>
      <el-form-item label="退款金额：">
        <el-input-number
          v-model="formItem.refund_price"
          placeholder="请输入退款金额"
          style="width: 60%"
        ></el-input-number>
      </el-form-item>
      <div v-if="total_num > 1">
        <el-form-item label="分单退款：">
          <el-switch
            :active-value="1"
            :inactive-value="0"
            size="large"
            v-model="splitSwitch"
            :disabled="orderStatus === 8 || orderStatus === 11"
            @change="changeSplitStatus"
          >
            <span slot="open">开启</span>
            <span slot="close">关闭</span>
          </el-switch>
          <div class="trips">
            <p>可选择表格中的商品单独退款，请谨慎操作！</p>
          </div>
          <el-table
            v-if="splitSwitch && manyFormValidate.length"
            ref="table"
            :data="manyFormValidate"
            @selection-change="selectOne"
          >
            <el-table-column type="selection" width="55"> </el-table-column>
            <el-table-column label="商品信息" width="200">
              <template slot-scope="scope">
                <div class="product-data">
                  <img class="image" :src="scope.row.cart_info.productInfo.image" />
                  <div class="line2">
                    {{ scope.row.cart_info.productInfo.store_name }}
                  </div>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="规格" min-width="120">
              <template slot-scope="scope">
                <div>{{ scope.row.cart_info.productInfo.attrInfo.suk }}</div>
              </template>
            </el-table-column>
            <el-table-column label="价格" min-width="120">
              <template slot-scope="scope">
                <div class="product-data">
                  <div>{{ scope.row.cart_info.truePrice }}</div>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="总数" min-width="120">
              <template slot-scope="scope">
                <div>{{ scope.row.cart_num }}</div>
              </template>
            </el-table-column>
            <el-table-column label="退款数量" width="180">
              <template slot-scope="scope">
                <el-input-number
                  v-model="scope.row.num"
                  :controls="false"
                  :min="1"
                  :max="scope.row.cart_num"
                  @change="
                    (e) => {
                      handleChange(e, scope.row, scope.$index);
                    }
                  "
                ></el-input-number>
              </template>
            </el-table-column>
          </el-table>
        </el-form-item>
      </div>
    </el-form>
    <div slot="footer">
      <el-button v-db-click @click="cancel">取消</el-button>
      <el-button type="primary" v-db-click @click="putSend">提交</el-button>
    </div>
    <!-- <viewer @inited="inited">
            <img :src="temp.pic" style="display:none" />
        </viewer> -->
    <div ref="viewer" v-viewer>
      <img :src="temp.pic" style="display: none" />
    </div>
  </el-dialog>
</template>

<script>
import {
  getExpressData,
  putDelivery,
  splitDelivery,
  orderExpressTemp,
  orderDeliveryList,
  orderSheetInfo,
  splitCartInfo,
  orderPrice,
  refundPrice,
} from '@/api/order';
import printJS from 'print-js';
export default {
  name: 'orderSend',
  props: {
    orderId: Number,
    status: Number,
    // total_num: Number,
    pay_type: String,
    virtual_type: {
      type: Number,
      default: 0,
    },
  },
  data() {
    return {
      orderStatus: 0,
      total_num: 0,
      splitSwitch: true,
      order_id: '',
      formItem: {
        refund_price: '',
        cart_ids: [],
      },
      modals: false,
      express: [],
      kuaidiExpress: [],
      expressTemp: [],
      deliveryList: [],
      temp: {},
      export_open: false,
      manyFormValidate: [],
      selectData: [],
      sendPrice: 0,
      ruleValidate: { sh_delivery: [{ required: true, message: '请输入送货人', trigger: 'change' }] },
      deliveryErrorMsg: '',
      isLoading: true,
      userSendmsg: {},
    };
  },
  mounted() {},
  methods: {
    handleChange(e, params, index) {
      let total = 0;
      this.selectData.forEach((v, i) => {
        console.log(v.num)
        total += v.num * v.cart_info.truePrice;
      });
      this.formItem.refund_price = total;
    },
    selectOne(data) {
      this.selectData = data;
      if (this.selectData.length) {
        let total = 0;
        this.selectData.forEach((v, i) => {
          total += v.num * v.cart_info.truePrice;
        });
        this.formItem.refund_price = total.toFixed(2);
      }
    },
    changeModal() {
      this.cancel();
      this.isLoading = true;
    },
    changeSplitStatus(status) {
      // this.splitSwitch = status;
      if (status) {
        splitCartInfo(this.orderId).then((res) => {
          this.manyFormValidate = [];
          Object.keys(res.data).forEach((key) => {
            this.manyFormValidate.push(res.data[key]);
          });
        });
      } else {
        this.formItem.cart_ids = [];
        this.selectData = [];
      }
    },
    reset() {
      this.formItem = {
        refund_price: '',
        cart_ids: [],
      };
    },
    // 提交
    putSend(name) {
      this.formItem.cart_ids = [];
      let splitNumStatus = true;
      if (this.splitSwitch) {
        this.selectData.forEach((v) => {
          if (!v.num) {
            splitNumStatus = false;
          }
          this.formItem.cart_ids.push({
            cart_id: v.cart_id,
            cart_num: v.num || v.surplus_num,
          });
        });
      }
      if (!splitNumStatus) {
        return this.$message.error('请选择退款数量');
      }
      refundPrice(this.orderId, this.formItem)
        .then((res) => {
          this.modals = false;
          this.$message.success(res.msg);
          this.$emit('submitFail');
          this.reset();
          this.splitSwitch = false;
          if (res.data.label) this.printImg(res.data.label);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    cancel(name) {
      this.modals = false;
      this.orderStatus = 0;
      this.sendPrice = 0;
      this.deliveryErrorMsg = '';
      this.splitSwitch = false;
      this.selectData = [];
      this.formItem.type = '1';
      this.$emit('clearId');
      this.reset();
      // this.$refs[name].resetFields();
      // this.formItem.type = '1';
    },
  },
};
</script>

<style scoped>
.express_temp_id {
  position: relative;
}

.express_temp_id button {
  position: absolute;
  top: 50%;
  left: 61%;
  padding: 0;
  border: none;
  background: none;
  transform: translateY(-50%);
  color: #57a3f3;
}

.ivu-btn-text:focus {
  box-shadow: none;
}
.trips {
  color: #ccc;
  font-size: 12px;
}
.product-data {
  display: flex;
  align-items: center;
  /* width: 200px; */
}
.product-data .image {
  width: 50px !important;
  height: 50px !important;
  margin-right: 10px;
}
.line2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.from-box {
  position: relative;
}
.trip {
  position: absolute;
  bottom: -26px;
  left: 0;
  color: red;
  font-size: 12px;
}
.coumped {
  font-size: 12px;
}
</style>
