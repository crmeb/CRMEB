<template>
  <el-dialog :visible.sync="modals" width="720px" title="订单发送货" class="order_box" :show-close="true">
    <el-form ref="formItem" :model="formItem" label-width="100px" @submit.native.prevent>
      <el-form-item label="选择类型：">
        <el-radio-group v-model="formItem.type" @input="changeRadio">
          <el-radio label="1">打印电子面单</el-radio>
          <el-radio label="2">送货</el-radio>
          <el-radio label="3">虚拟</el-radio>
        </el-radio-group>
      </el-form-item>
      <div v-show="formItem.type === '1'">
        <el-form-item label="快递公司：">
          <el-select
            v-model="formItem.delivery_name"
            filterable
            placeholder="请选择快递公司"
            style="width: 80%"
            @change="expressChange"
          >
            <el-option
              v-for="(item, i) in express"
              :value="item.value"
              :key="item.value"
              :label="item.value"
            ></el-option>
          </el-select>
        </el-form-item>
        <template v-if="formItem.type === '1'">
          <el-form-item label="电子面单：" class="express_temp_id">
            <el-select
              v-model="formItem.express_temp_id"
              placeholder="请选择电子面单"
              style="width: 80%"
              @change="expressTempChange"
            >
              <el-option
                v-for="(item, i) in expressTemp"
                :value="item.temp_id"
                :key="i"
                :label="item.title"
              ></el-option>
            </el-select>
            <el-button v-if="formItem.express_temp_id" type="text" v-db-click @click="preview">预览</el-button>
          </el-form-item>
          <el-form-item label="寄件人姓名：">
            <el-input v-model="formItem.to_name" placeholder="请输入寄件人姓名" style="width: 80%"></el-input>
          </el-form-item>
          <el-form-item label="寄件人电话：">
            <el-input v-model="formItem.to_tel" placeholder="请输入寄件人电话" style="width: 80%"></el-input>
          </el-form-item>
          <el-form-item label="寄件人地址：">
            <el-input v-model="formItem.to_addr" placeholder="请输入寄件人地址" style="width: 80%"></el-input>
          </el-form-item>
        </template>
      </div>
      <div v-show="formItem.type === '2'">
        <el-form-item label="送货人：">
          <el-select
            v-model="formItem.sh_delivery"
            placeholder="请选择送货人"
            style="width: 80%"
            @change="shDeliveryChange"
          >
            <el-option
              v-for="(item, i) in deliveryList"
              :value="item.id"
              :key="i"
              :label="`${item.wx_name} (${item.phone})`"
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
            style="width: 80%"
          ></el-input>
        </el-form-item>
      </div>
    </el-form>
    <div slot="footer">
      <el-button v-db-click @click="cancel">取消</el-button>
      <el-button type="primary" v-db-click @click="putSend">提交</el-button>
    </div>
    <div ref="viewer" v-viewer v-show="temp">
      <img :src="temp.pic" style="display: none" />
    </div>
  </el-dialog>
</template>

<script>
import { getExpressData, orderExpressTemp, orderDeliveryList, orderSheetInfo, otherBatchDelivery } from '@/api/order';
export default {
  name: 'orderSend',
  props: {
    isAll: {
      type: Number,
      default: 1,
    },
    ids: {
      type: Array,
      default() {
        return [];
      },
    },
    where: {
      type: Object,
      default() {
        return {};
      },
    },
  },
  data() {
    return {
      formItem: {
        type: '1',
        express_record_type: '2',
        delivery_name: '',
        delivery_id: '',
        express_temp_id: '',
        to_name: '',
        to_tel: '',
        to_addr: '',
        sh_delivery: '',
        fictitious_content: '',
      },
      modals: false,
      express: [],
      expressTemp: [],
      deliveryList: [],
      temp: {},
      export_open: true,
    };
  },
  watch: {
    'formItem.express_temp_id'(value) {},
  },
  methods: {
    changeRadio(o) {
      this.$refs.formItem.resetFields();
      switch (o) {
        case '1':
          this.formItem.delivery_name = '';
          this.formItem.delivery_id = '';
          this.formItem.express_temp_id = '';
          this.formItem.express_record_type = '2';
          this.expressTemp = [];
          break;
        case '2':
          this.formItem.sh_delivery = '';
          this.formItem.express_record_type = '1';
          break;
        case '3':
          this.formItem.fictitious_content = '';
          this.formItem.express_record_type = '1';
          break;
      }
    },
    changeExpress(j) {
      switch (j) {
        case '2':
          this.formItem.delivery_name = '';
          this.formItem.express_temp_id = '';
          this.expressTemp = [];
          break;
        case '1':
          this.formItem.delivery_name = '';
          this.formItem.delivery_id = '';
          break;
        default:
          break;
      }
    },
    reset() {
      this.formItem = {
        type: '1',
        express_record_type: '2',
        delivery_name: '',
        delivery_id: '',
        express_temp_id: '',
        expressTemp: [],
        to_name: '',
        to_tel: '',
        to_addr: '',
        sh_delivery: '',
        fictitious_content: '',
      };
    },
    // 物流公司列表
    getList() {
      getExpressData(1)
        .then(async (res) => {
          this.express = res.data;
          this.getSheetInfo();
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 提交
    putSend(name) {
      let data = Object.assign(this.formItem);
      let arr = [];
      this.ids.forEach((item) => {
        arr.push(item.id);
      });
      if (this.isAll == 1) {
        data.all = 1;
        data.where = this.where;
      } else {
        data.all = 0;
        data.ids = arr;
      }
      if (this.formItem.type === '1') {
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
      if (this.formItem.type === '2') {
        if (this.formItem.express_temp_id) {
          this.formItem.express_temp_id = '';
        }
        if (this.formItem.sh_delivery === '') {
          return this.$message.error('送货人不能为空');
        }
      }
      otherBatchDelivery(data)
        .then(async (res) => {
          this.modals = false;
          this.$message.success(res.msg);
          this.reset();
        })
        .catch((res) => {
          this.$message.error(res.msg);
          this.modals = false;
        });
    },
    cancel(name) {
      this.modals = false;
      this.reset();
    },
    // 电子面单列表
    expressChange(value) {
      let expressItem = this.express.find((item) => {
        return item.value === value;
      });
      if (!expressItem) {
        return;
      }
      this.formItem.delivery_code = expressItem.code;
      if (this.formItem.type === '1') {
        this.expressTemp = [];
        this.formItem.express_temp_id = '';
        orderExpressTemp({
          com: this.formItem.delivery_code,
        })
          .then((res) => {
            this.expressTemp = res.data;
            if (!res.data.length) {
              this.$message.error('请配置你所选快递公司的电子面单');
            }
          })
          .catch((err) => {
            this.$message.error(err.msg);
          });
      }
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
            if (data.hasOwnProperty(key) && key !== 'express_temp_id') {
              this.formItem[key] = data[key];
            }
          }
          this.export_open = data.export_open === undefined ? true : data.export_open;
          if (!this.export_open) {
            this.formItem.express_record_type = '1';
          }
          this.formItem.to_addr = data.to_add;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    shDeliveryChange(value) {
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
    },
    preview() {
      this.$refs.viewer.$viewer.show();
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
  right: 110px;
  padding: 0;
  border: none;
  background: none;
  transform: translateY(-50%);
  color: #57a3f3;
}

.ivu-btn-text:focus {
  box-shadow: none;
}
</style>
