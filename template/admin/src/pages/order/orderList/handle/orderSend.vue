<template>
  <el-dialog
    :visible.sync="modals"
    title="订单发送货"
    class="order_box"
    :show-close="true"
    width="1000px"
    @closed="changeModal"
  >
    <el-alert class="mb10" type="warning" :closable="false">
      <template slot="title">
        <p>用户姓名：{{ userSendmsg.real_name }}</p>
        <p>用户电话：{{ userSendmsg.user_phone }}</p>
        <p>用户地址：{{ userSendmsg.user_address }}</p>
      </template>
    </el-alert>
    <el-form
      v-if="modals"
      ref="formItem"
      :rules="ruleValidate"
      :model="formItem"
      label-width="100px"
      @submit.native.prevent
      v-loading="isLoading"
    >
      <el-form-item label="选择类型：">
        <el-radio-group v-model="formItem.type" @input="changeRadio">
          <el-radio label="1" v-if="virtual_type !== 3">发货</el-radio>
          <el-radio label="2" v-if="virtual_type !== 3">送货</el-radio>
          <el-radio label="3">无需配送</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item v-if="formItem.type == 1" label="发货类型：">
        <el-radio-group v-model="formItem.express_record_type" @input="changeExpress">
          <el-radio label="1">录入单号</el-radio>
          <el-radio label="2" v-show="export_open">电子面单打印</el-radio>
          <el-radio label="3">商家寄件</el-radio>
        </el-radio-group>
      </el-form-item>
      <template v-if="['2', '3'].includes(formItem.express_record_type) && formItem.type == 1">
        <el-form-item label="寄件人姓名：">
          <el-input v-model="formItem.to_name" placeholder="请输入寄件人姓名" style="width: 60%"></el-input>
        </el-form-item>
        <el-form-item label="寄件人电话：">
          <el-input v-model="formItem.to_tel" placeholder="请输入寄件人电话" style="width: 60%"></el-input>
        </el-form-item>
        <el-form-item label="寄件人地址：">
          <el-input
            v-model="formItem.to_addr"
            placeholder="请输入寄件人地址"
            style="width: 60%"
            @blur="watchPrice"
          ></el-input>
        </el-form-item>
      </template>
      <div>
        <el-form-item label="快递公司：" v-if="formItem.type == 1">
          <div class="from-box">
            <el-select
              v-model="formItem.delivery_name"
              filterable
              placeholder="请选择快递公司"
              style="width: 60%"
              @change="expressChange"
            >
              <el-option
                v-for="item in formItem.express_record_type == 3 ? kuaidiExpress : express"
                :value="item.value"
                :key="item.value"
                >{{ item.value }}</el-option
              >
            </el-select>
            <div class="trip">{{ deliveryErrorMsg }}</div>
          </div>
        </el-form-item>
        <el-form-item label="快递业务类型：" v-if="formItem.type == 1 && formItem.express_record_type == 3">
          <el-select
            v-model="formItem.service_type"
            filterable
            placeholder="请选择业务类型"
            style="width: 60%"
            @change="watchPrice"
          >
            <el-option v-for="item in serviceTypeList" :value="item" :key="item">{{ item }}</el-option>
          </el-select>
        </el-form-item>
        <el-form-item v-if="formItem.express_record_type === '1' && formItem.type == 1" label="快递单号：">
          <el-input v-model="formItem.delivery_id" placeholder="请输入快递单号" style="width: 60%"></el-input>
          <div class="trips" v-if="formItem.delivery_name == '顺丰速运'">
            <p>顺丰请输入单号 :收件人或寄件人手机号后四位，</p>
            <p>例如：SF000000000000:3941</p>
          </div>
        </el-form-item>
        <template v-if="['2', '3'].includes(formItem.express_record_type) && formItem.type == 1">
          <el-form-item label="电子面单：" class="express_temp_id">
            <el-select
              v-model="formItem.express_temp_id"
              placeholder="请选择电子面单"
              style="width: 60%"
              @change="expressTempChange"
            >
              <el-option
                v-for="(item, i) in expressTemp"
                :value="item.temp_id"
                :key="i"
                :label="item.title"
              ></el-option>
            </el-select>
            <Button v-if="formItem.express_temp_id" type="text" v-db-click @click="preview">预览</Button>
          </el-form-item>
          <el-form-item label="预计寄件金额：" v-if="formItem.express_record_type == 3">
            <span class="red">{{ sendPrice }}</span>
            <a class="ml10 coumped" v-db-click @click="watchPrice">立即计算</a>
          </el-form-item>
          <el-form-item label="取件日期：" v-if="formItem.express_record_type == 3">
            <el-radio-group v-model="formItem.day_type" type="button">
              <el-radio :label="0">今天</el-radio>
              <el-radio :label="1">明天</el-radio>
              <el-radio :label="2">后天</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="取件时间：" v-if="formItem.express_record_type == 3">
            <el-time-picker
              is-range
              v-model="formItem.pickup_time"
              format="HH:mm"
              value-format="HH:mm"
              range-separator="-"
              start-placeholder="开始时间"
              end-placeholder="结束时间"
              placeholder="选择时间范围"
            />
          </el-form-item>
        </template>
      </div>
      <div v-if="formItem.type === '2'">
        <el-form-item label="送货人：" :prop="formItem.type == '2' ? 'sh_delivery' : ''">
          <el-select
            v-model="formItem.sh_delivery"
            placeholder="请选择送货人"
            style="width: 60%"
            @change="shDeliveryChange"
          >
            <el-option
              v-for="(item, i) in deliveryList"
              :value="item.id"
              :key="i"
              :label="`${item.wx_name}（${item.phone}）`"
            ></el-option>
          </el-select>
        </el-form-item>
      </div>
      <div v-show="formItem.type === '3'">
        <el-form-item label="备注：">
          <el-input
            v-model="formItem.fictitious_content"
            type="textarea"
            :autosize="{ minRows: 2, maxRows: 5 }"
            placeholder="备注"
            style="width: 60%"
          ></el-input>
        </el-form-item>
      </div>
      <div v-if="total_num > 1">
        <el-form-item label="分单发货：">
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
            <p>可选择表格中的商品单独发货，发货后会生成新的订单且不能撤回，请谨慎操作！</p>
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
            <el-table-column label="待发数量" width="180">
              <template slot-scope="scope">
                <el-input-number
                  v-model="scope.row.num"
                  :controls="false"
                  :min="1"
                  :max="scope.row.surplus_num"
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
  kuaidiComsList,
  orderPrice,
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
      formItem: {
        type: '1',
        express_record_type: '3',
        delivery_name: '',
        delivery_id: '',
        express_temp_id: '',
        to_name: '',
        to_tel: '',
        to_addr: '',
        sh_delivery: '',
        fictitious_content: '',
        service_type: '',
        day_type: 0,
        pickup_time: ['', ''],
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
      serviceTypeList: [],
      sendPrice: 0,
      ruleValidate: { sh_delivery: [{ required: true, message: '请输入送货人', trigger: 'change' }] },
      deliveryErrorMsg: '',
      isLoading: true,
      userSendmsg: {},
    };
  },
  watch: {
    virtual_type(val) {
      if (this.virtual_type == 3) this.formItem.type = '3';
    },
  },
  mounted() {
    this.kuaidiComsList(1);
    let delData;
    if (localStorage.getItem('DELIVERY_DATA')) delData = JSON.parse(localStorage.getItem('DELIVERY_DATA'));
    if (delData) {
      this.formItem.delivery_name = delData.delivery_name;
      this.formItem.delivery_code = delData.delivery_code;
    }
  },
  methods: {
    handleChange(e, params, index) {
      params.num = e || 1;
      this.manyFormValidate[index] = params;
      this.selectData.forEach((v, i) => {
        if (v.cart_id === params.cart_id) {
          this.selectData.splice(i, 1, params);
        }
      });
    },
    watchPrice() {
      if (this.formItem.express_record_type != 3) return;
      let data = {
        kuaidicom: this.formItem.delivery_code,
        send_address: this.formItem.to_addr,
        orderId: this.orderId,
        service_type: this.formItem.service_type,
        cart_ids: [],
      };
      this.selectData.forEach((v) => {
        data.cart_ids.push({
          cart_id: v.cart_id,
          cart_num: v.num || v.surplus_num,
        });
      });
      orderPrice(data)
        .then((res) => {
          this.sendPrice = res.data.price;
          this.deliveryErrorMsg = '';
        })
        .catch((err) => {
          if (this.formItem.type == 1) {
            this.deliveryErrorMsg = err.msg;
          }
          this.$message.error(err.msg);
        });
    },
    selectOne(data) {
      this.selectData = data;
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
    changeRadio(o) {
      this.$refs.formItem.resetFields();
      this.deliveryErrorMsg = '';
      switch (o) {
        case '1':
          this.formItem.delivery_name = '';
          this.formItem.delivery_id = '';
          this.formItem.express_temp_id = '';
          this.formItem.express_record_type = '1';
          this.expressTemp = [];
          this.getList(1);
          break;
        case '2':
          this.formItem.sh_delivery = '';
          break;
        case '3':
          this.formItem.fictitious_content = '';
          break;
        default:
          // this.formItem = {
          //     type: '3',
          //     express_record_type: '1',
          //     delivery_name: '',
          //     delivery_id: '',
          //     express_temp_id: '',
          //     to_name: '',
          //     to_tel: '',
          //     to_addr: '',
          //     sh_delivery: ''
          // };
          break;
      }
    },
    changeExpress(j) {
      this.deliveryErrorMsg = '';
      switch (j) {
        case '1':
          this.formItem.delivery_name = '';
          this.formItem.delivery_id = '';
          this.getList(1);
          break;
        case '2':
          this.formItem.delivery_name = '';
          this.formItem.express_temp_id = '';
          this.expressTemp = [];
          this.getList(2);
          break;
        case '3':
          this.formItem.delivery_name = '';
          this.formItem.delivery_id = '';
          break;
        default:
          break;
      }
    },
    kuaidiComsList(status) {
      kuaidiComsList().then((res) => {
        this.kuaidiExpress = res.data;
        if (this.formItem.delivery_name) this.expressChange(this.formItem.delivery_name);
      });
    },
    reset() {
      this.formItem = {
        type: '1',
        express_record_type: '3',
        delivery_name: '',
        delivery_id: '',
        express_temp_id: '',
        expressTemp: [],
        to_name: '',
        to_tel: '',
        to_addr: '',
        sh_delivery: '',
        fictitious_content: '',
        service_type: '',
      };
    },
    // 物流公司列表
    getList(type) {
      let status = type === 2 ? 1 : '';
      getExpressData(status)
        .then(async (res) => {
          this.express = res.data;
          this.getSheetInfo();
          // this.isLoading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    printImg(url) {
      printJS({
        printable: url,
        type: 'image',
        documentTitle: '快递信息',
        style: `img{
          width: 100%;
          height: 476px;
        }`,
      });
    },

    // 提交
    putSend(name) {
      let data = {
        id: this.orderId,
        datas: this.formItem,
      };
      if (this.formItem.type === '1' && this.formItem.express_record_type === '2') {
        if (this.formItem.delivery_name === '') {
          return this.$message.error('快递公司不能为空');
        } else if (this.formItem.express_temp_id === '') {
          return this.$message.error('电子面单不能为空');
        } else if (this.formItem.to_name === '') {
          return this.$message.error('寄件人姓名不能为空');
        } else if (this.formItem.to_tel === '') {
          return this.$message.error('寄件人电话不能为空');
        } else if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(this.formItem.to_tel)) {
          return this.$message.error('请输入正确的手机号码');
        } else if (this.formItem.to_addr === '') {
          return this.$message.error('寄件人地址不能为空');
        }
      }
      if (this.formItem.type === '1' && this.formItem.express_record_type === '1') {
        if (this.formItem.delivery_name === '') {
          return this.$message.error('快递公司不能为空');
        } else if (this.formItem.delivery_id === '') {
          return this.$message.error('快递单号不能为空');
        }
      }
      if (this.formItem.type === '2') {
        if (this.formItem.sh_delivery === '') {
          return this.$message.error('送货人不能为空');
        }
      }
      if (this.splitSwitch) {
        data.datas.cart_ids = [];
        this.selectData.forEach((v) => {
          data.datas.cart_ids.push({
            cart_id: v.cart_id,
            cart_num: v.num || v.surplus_num,
          });
        });
        splitDelivery(data)
          .then((res) => {
            this.modals = false;
            this.$message.success(res.msg);
            localStorage.setItem('DELIVERY_DATA', JSON.stringify(this.formItem));
            this.$emit('submitFail');
            this.reset();
            this.splitSwitch = false;
            if (res.data.label) this.printImg(res.data.label);
          })
          .catch((res) => {
            this.$message.error(res.msg);
          });
      } else {
        putDelivery(data)
          .then(async (res) => {
            this.modals = false;
            this.$message.success(res.msg);
            localStorage.setItem('DELIVERY_DATA', JSON.stringify(this.formItem));
            this.splitSwitch = false;
            this.$emit('submitFail');
            this.reset();
            if (res.data.label) this.printImg(res.data.label);
          })
          .catch((res) => {
            this.$message.error(res.msg);
          });
      }
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
    // 电子面单列表
    expressChange(value) {
      this.formItem.service_type = '';
      let expressItem = (this.formItem.express_record_type == '3' ? this.kuaidiExpress : this.express).find((item) => {
        return item.value === value;
      });
      if (expressItem === undefined) {
        return;
      }
      this.serviceTypeList = expressItem.types;
      if (this.formItem.type == 1 && this.formItem.express_record_type == 3) {
        this.formItem.service_type = expressItem.types.length ? expressItem.types[0] : '';
      }
      this.formItem.delivery_code = expressItem.code;
      if (this.formItem.to_name && this.formItem.to_addr && this.formItem.express_record_type == 3) this.watchPrice();
      if (this.formItem.express_record_type === '2') {
        this.expressTemp = [];
        this.formItem.express_temp_id = '';
        orderExpressTemp({
          com: this.formItem.delivery_code,
        })
          .then((res) => {
            this.expressTemp = res.data;
            this.formItem.express_temp_id = res.data.length ? res.data[0].temp_id : '';
            if (!res.data.length) {
              this.$message.error('请配置你所选快递公司的电子面单');
            }
          })
          .catch((err) => {
            this.$message.error(err.msg);
          });
      } else if (this.formItem.express_record_type == '3') {
        this.expressTemp = expressItem.list;
        if (expressItem.list.length) {
          this.formItem.express_temp_id = expressItem.list[0].temp_id;
          this.temp = expressItem.list[0];
        }
      }
    },
    getCartInfo(data, orderid) {
      this.$set(this, 'orderStatus', data);
      this.$set(this, 'splitSwitch', data === 8 || data === 11 ? true : false);
      splitCartInfo(this.orderId).then((res) => {
        this.manyFormValidate = [];
        Object.keys(res.data).forEach((key) => {
          this.manyFormValidate.push(res.data[key]);
        });
      });
    },
    getDeliveryList() {
      orderDeliveryList()
        .then((res) => {
          this.deliveryList = res.data.list;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    getSheetInfo() {
      orderSheetInfo()
        .then((res) => {
          const data = res.data;
          for (const key in data) {
            if (data.hasOwnProperty(key)) {
              this.formItem[key] = data[key];
            }
          }
          this.export_open = data.export_open === undefined ? true : data.export_open;
          if (!this.export_open) {
            this.formItem.express_record_type = '1';
          }
          this.formItem.to_addr = data.to_add;
          this.isLoading = false;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    shDeliveryChange(value) {
      if (!value) return;
      let deliveryItem = this.deliveryList.find((item) => {
        return item.id === value;
      });
      this.formItem.sh_delivery_name = deliveryItem.wx_name;
      this.formItem.sh_delivery_id = deliveryItem.phone;
      this.formItem.sh_delivery_uid = deliveryItem.uid;
    },
    expressTempChange(tempId) {
      this.temp = this.expressTemp.find((item) => {
        return tempId === item.temp_id;
      });
      if (this.temp === undefined) {
        this.temp = {};
      }
    },
    preview() {
      this.$refs.viewer.$viewer.show();
      // this.$viewer.show();
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
