<template>
  <el-dialog v-model="modals" title="订单发送货" width="720px" class="order_box" :show-close="true">
    <el-form ref="formItemRef" :model="formItem" label-width="100px" @submit.prevent>
      <el-form-item label="选择类型：">
        <el-radio-group v-model="formItem.type" @input="changeRadio">
          <el-radio label="1" value="1">发货</el-radio>
          <el-radio label="2" value="2">送货</el-radio>
          <el-radio label="3" value="3">虚拟</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item v-show="formItem.type == '1' && export_open" label="发货类型：">
        <el-radio-group v-model="formItem.express_record_type" @input="changeExpress">
          <el-radio label="1" value="1">手动填写</el-radio>
          <el-radio label="2" value="2">电子面单打印</el-radio>
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
        <el-form-item v-if="formItem.express_record_type === '1'" label="快递单号：">
          <el-input v-model="formItem.delivery_id" placeholder="请输入快递单号" style="width: 80%"></el-input>
        </el-form-item>
        <template v-if="formItem.express_record_type === '2'">
          <el-form-item label="电子面单：" class="express_temp_id">
            <el-select v-model="formItem.express_temp_id" placeholder="请选择电子面单" style="width: 80%">
              <el-option
                v-for="(item, i) in expressTemp"
                :value="item.temp_id"
                :key="i"
                :label="item.title"
              ></el-option>
            </el-select>
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
    <template #footer
      ><span class="dialog-footer">
        <el-button v-db-click @click="cancel">取消</el-button>
        <el-button type="primary" v-db-click @click="putSend">提交</el-button>
      </span></template
    >
  </el-dialog>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import {
  getExpressData,
  orderExpressTemp,
  orderDeliveryList,
  orderSheetInfo,
  integralOrderPutDelivery,
} from '../../api';

defineOptions({ name: 'integralOrderSend' });

const props = defineProps({
  orderId: Number,
});

const emit = defineEmits(['submitFail']);

const formItemRef = ref(null);
const formItem = ref({
  type: '1',
  express_record_type: '1',
  delivery_name: '',
  delivery_id: '',
  express_temp_id: '',
  to_name: '',
  to_tel: '',
  to_addr: '',
  sh_delivery: '',
  sh_delivery_name: '',
  sh_delivery_id: '',
  sh_delivery_uid: '',
  fictitious_content: '',
});
const modals = ref(false);
const express = ref([]);
const expressTemp = ref([]);
const deliveryList = ref([]);
const export_open = ref(false);

function changeRadio(o) {
  formItemRef.value.resetFields();
  formItem.value.type = o;
  if (o === '1') {
    getList(1);
  }
}
function changeExpress(j) {
  if (j === '2') {
    formItem.value.delivery_name = '';
    formItem.value.express_temp_id = '';
    expressTemp.value = [];
    getList(2);
  } else {
    getList(1);
  }
}
function reset() {
  formItem.value = {
    type: '1',
    express_record_type: '1',
    delivery_name: '',
    delivery_id: '',
    express_temp_id: '',
    to_name: '',
    to_tel: '',
    to_addr: '',
    sh_delivery: '',
    sh_delivery_name: '',
    sh_delivery_id: '',
    sh_delivery_uid: '',
    fictitious_content: '',
  };
}
function getList(type) {
  let status = type === 2 ? 1 : '';
  getExpressData(status)
    .then(async (res) => {
      express.value = res.data;
      getSheetInfo();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function putSend() {
  let data = { id: props.orderId, datas: formItem.value };
  if (formItem.value.type === '1' && formItem.value.express_record_type === '1') {
    if (!formItem.value.delivery_name) return ElMessage.error('快递公司不能为空');
    if (!formItem.value.delivery_id) return ElMessage.error('快递单号不能为空');
  }
  if (formItem.value.type === '2' && !formItem.value.sh_delivery) {
    return ElMessage.error('送货人不能为空');
  }
  integralOrderPutDelivery(data)
    .then(async (res) => {
      emit('submitFail');
      modals.value = false;
      ElMessage.success(res.msg);
      reset();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function cancel() {
  modals.value = false;
  reset();
}
function expressChange(value) {
  let expressItem = express.value.find((item) => item.value === value);
  if (!expressItem) return;
  formItem.value.delivery_code = expressItem.code;
  if (formItem.value.express_record_type === '2') {
    expressTemp.value = [];
    formItem.value.express_temp_id = '';
    orderExpressTemp({ com: formItem.value.delivery_code })
      .then((res) => {
        expressTemp.value = res.data;
        if (!res.data.length) {
          ElMessage.error('请配置所选快递公司的电子面单');
        }
      })
      .catch((err) => {
        ElMessage.error(err.msg);
      });
  }
}
function getDeliveryList() {
  orderDeliveryList()
    .then((res) => {
      deliveryList.value = res.data.list;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
function getSheetInfo() {
  orderSheetInfo()
    .then((res) => {
      const data = res.data;
      for (const key in data) {
        if (data.hasOwnProperty(key)) {
          formItem.value[key] = data[key];
        }
      }
      export_open.value = data.export_open === undefined ? true : data.export_open;
      if (!export_open.value) formItem.value.express_record_type = '1';
      formItem.value.to_addr = data.to_add;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
function shDeliveryChange(value) {
  let deliveryItem = deliveryList.value.find((item) => item.id === value);
  if (deliveryItem) {
    formItem.value.sh_delivery_name = deliveryItem.wx_name;
    formItem.value.sh_delivery_id = deliveryItem.phone;
    formItem.value.sh_delivery_uid = deliveryItem.uid;
  }
}

defineExpose({ modals, getList, getDeliveryList });
</script>

<style scoped>
.express_temp_id {
  position: relative;
}
</style>
