<template>
  <el-dialog
    v-model="dialogVisible"
    title="订单发送货"
    class="order-send-dialog"
    :show-close="true"
    width="1000px"
    @closed="handleClosed"
  >
    <template v-if="dialogVisible">
      <!-- 用户信息提示 -->
      <el-alert class="mb20" type="warning" :closable="false">
        <template #title>
          <div class="user-info-box">
            <p>用户姓名：{{ userInfo.real_name || '--' }}</p>
            <p>用户电话：{{ userInfo.user_phone || '--' }}</p>
            <p>用户地址：{{ userInfo.user_address || '--' }}</p>
          </div>
        </template>
        <div class="copy-box">
          <span class="copy-btn" @click="handleCopyUserInfo">复制</span>
        </div>
      </el-alert>

      <!-- 发货表单 -->
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="100px"
        @submit.prevent
        v-loading="pageLoading"
      >
        <!-- 配送类型 -->
        <el-form-item label="选择类型：">
          <el-radio-group v-model="formData.delivery_type" @change="handleDeliveryTypeChange">
            <el-radio label="express" value="express">发货</el-radio>
            <el-radio label="send" value="send">送货</el-radio>
            <el-radio label="fictitious" value="fictitious">无需配送</el-radio>
          </el-radio-group>
        </el-form-item>

        <!-- 快递发货 -->
        <template v-if="formData.delivery_type === 'express'">
          <el-form-item label="发货方式：">
            <el-radio-group v-model="formData.express_record_type" @change="handleRecordTypeChange">
              <el-radio :value="1">手动填写快递单号</el-radio>
              <el-radio v-if="sheetInfo.export_open === 1" :value="2">电子面单</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="快递公司：" prop="delivery_name">
            <el-select
              v-model="formData.delivery_name"
              filterable
              placeholder="请选择快递公司"
              style="width: 60%"
              @change="handleExpressCompanyChange"
            >
              <el-option
                v-for="item in expressList"
                :key="item.code || item.id"
                :label="item.value || item.name"
                :value="item.value || item.name"
              ></el-option>
            </el-select>
          </el-form-item>
          <template v-if="formData.express_record_type === 2">
            <el-form-item label="面单模板：" prop="express_temp_id">
              <el-select
                v-model="formData.express_temp_id"
                filterable
                placeholder="请选择电子面单模板"
                style="width: 60%"
                v-loading="tempLoading"
              >
                <el-option
                  v-for="item in expressTempList"
                  :key="item.temp_id"
                  :label="item.temp_name"
                  :value="item.temp_id"
                ></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="寄件人姓名：" prop="to_name">
              <el-input v-model="formData.to_name" placeholder="请输入寄件人姓名" style="width: 60%"></el-input>
            </el-form-item>
            <el-form-item label="寄件人电话：" prop="to_tel">
              <el-input v-model="formData.to_tel" placeholder="请输入寄件人电话" style="width: 60%"></el-input>
            </el-form-item>
            <el-form-item label="寄件人地址：" prop="to_addr">
              <el-input v-model="formData.to_addr" placeholder="请输入寄件人地址" style="width: 60%"></el-input>
            </el-form-item>
          </template>
          <el-form-item v-else label="快递单号：" prop="delivery_id">
            <el-input v-model="formData.delivery_id" placeholder="请输入快递单号" style="width: 60%"></el-input>
          </el-form-item>
        </template>

        <!-- 送货 -->
        <template v-if="formData.delivery_type === 'send'">
          <el-form-item label="送货人：" prop="sh_delivery">
            <el-select
              v-model="formData.sh_delivery"
              placeholder="请选择送货人"
              style="width: 60%"
              @change="handleDeliveryPersonChange"
            >
              <el-option
                v-for="(item, i) in deliveryPersonList"
                :key="i"
                :value="item.id"
                :label="item.wx_name + '（' + item.phone + '）'"
              ></el-option>
            </el-select>
          </el-form-item>
        </template>

        <!-- 无需配送 -->
        <template v-if="formData.delivery_type === 'fictitious'">
          <el-form-item label="备注：">
            <el-input
              v-model="formData.fictitious_content"
              type="textarea"
              :autosize="{ minRows: 2, maxRows: 5 }"
              placeholder="请输入发货备注（选填）"
              style="width: 60%"
            ></el-input>
          </el-form-item>
        </template>

        <!-- 商品信息 -->
        <el-form-item label="商品信息：" v-if="orderItems.length">
          <el-table :data="orderItems" stripe border style="width: 90%">
            <el-table-column label="商品" min-width="220">
              <template #default="{ row }">
                <div class="product-cell">
                  <el-image :src="row.image" class="product-img" />
                  <div class="product-meta">
                    <p class="product-name">{{ row.store_name || row.title || '--' }}</p>
                    <p v-if="row.suk || row.sku" class="product-sku">{{ row.suk || row.sku }}</p>
                  </div>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="单价" width="100">
              <template #default="{ row }">¥{{ row.true_price || row.price || '0.00' }}</template>
            </el-table-column>
            <el-table-column label="数量" width="80" prop="cart_num" />
            <el-table-column label="小计" width="120">
              <template #default="{ row }"
                >¥{{ (parseFloat(row.true_price || row.price || 0) * (row.cart_num || 1)).toFixed(2) }}</template
              >
            </el-table-column>
          </el-table>
        </el-form-item>
      </el-form>
    </template>

    <template #footer
      ><div>
        <el-button @click="handleCancel">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitLoading">提交</el-button>
      </div></template
    >
  </el-dialog>
</template>

<script setup>
import { ref, reactive, watch, getCurrentInstance } from 'vue';
import { ElMessage } from 'element-plus';
import request from '@/libs/request';

defineOptions({ name: 'OrderSend' });

const props = defineProps({
  orderNo: {
    type: String,
    default: '',
  },
  visible: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['success', 'close']);

const { proxy } = getCurrentInstance();

const formRef = ref(null);
const dialogVisible = ref(false);
const pageLoading = ref(false);
const submitLoading = ref(false);
const tempLoading = ref(false);
const userInfo = ref({});
const orderItems = ref([]);
const expressList = ref([]);
const expressTempList = ref([]);
const deliveryPersonList = ref([]);
const sheetInfo = ref({});
const formData = ref(getDefaultForm());
const formRules = {
  sh_delivery: [{ required: true, message: '请选择送货人', trigger: 'change' }],
  delivery_name: [{ required: true, message: '请选择快递公司', trigger: 'change' }],
  delivery_id: [{ required: true, message: '请输入快递单号', trigger: 'blur' }],
  express_temp_id: [{ required: true, message: '请选择电子面单模板', trigger: 'change' }],
  to_name: [{ required: true, message: '请输入寄件人姓名', trigger: 'blur' }],
  to_tel: [{ required: true, message: '请输入寄件人电话', trigger: 'blur' }],
  to_addr: [{ required: true, message: '请输入寄件人地址', trigger: 'blur' }],
};

function getDefaultForm() {
  return {
    delivery_type: 'express',
    express_record_type: 1,
    delivery_name: '',
    delivery_code: '',
    delivery_id: '',
    express_temp_id: '',
    to_name: '',
    to_tel: '',
    to_addr: '',
    sh_delivery: '',
    sh_delivery_name: '',
    sh_delivery_id: '',
    sh_delivery_uid: 0,
    fictitious_content: '',
  };
}

watch(
  () => props.visible,
  (val) => {
    dialogVisible.value = val;
    if (val && props.orderNo) {
      initData();
    }
  },
  { immediate: true },
);

watch(
  () => props.orderNo,
  (val) => {
    if (dialogVisible.value && val) {
      initData();
    }
  },
);

/**
 * 初始化所有数据
 */
function initData() {
  resetForm();
  pageLoading.value = true;
  loadShipmentDetail();
  loadExpressList();
  loadDeliveryPersons();
  loadSheetInfo();
}

/**
 * 获取寄件配置（电子面单开关 + 寄件人默认信息）
 */
function loadSheetInfo() {
  request
    .get('normal/order/shipment/sheet_info')
    .then((res) => {
      sheetInfo.value = res.data || {};
      // 预填寄件人默认信息
      formData.value.to_name = sheetInfo.value.to_name || '';
      formData.value.to_tel = sheetInfo.value.to_tel || '';
      formData.value.to_addr = sheetInfo.value.to_addr || '';
    })
    .catch(() => {
      // 静默处理，不影响主流程
    });
}

/**
 * 快递公司选择变化：记录编码，电子面单模式下加载对应模板
 */
function handleExpressCompanyChange(value) {
  const selected = expressList.value.find((item) => (item.value || item.name) === value);
  formData.value.delivery_code = selected?.code || '';
  formData.value.express_temp_id = '';
  expressTempList.value = [];
  if (formData.value.express_record_type === 2 && formData.value.delivery_code) {
    loadExpressTempList(formData.value.delivery_code);
  }
}

/**
 * 获取电子面单模板列表
 */
function loadExpressTempList(com) {
  tempLoading.value = true;
  request
    .get('normal/order/shipment/express_temp', { com })
    .then((res) => {
      expressTempList.value = res.data || [];
    })
    .catch(() => {
      expressTempList.value = [];
    })
    .finally(() => {
      tempLoading.value = false;
    });
}

/**
 * 发货方式切换（手动单号 / 电子面单）
 */
function handleRecordTypeChange() {
  formRef.value && formRef.value.clearValidate();
  // 切回电子面单时若已选快递公司则直接加载模板
  if (formData.value.express_record_type === 2 && formData.value.delivery_code) {
    loadExpressTempList(formData.value.delivery_code);
  }
}

/**
 * 获取发货详情
 */
function loadShipmentDetail() {
  request
    .get('normal/order/shipment/detail/' + props.orderNo)
    .then((res) => {
      const data = res.data || {};
      userInfo.value = {
        real_name: data.real_name || '',
        user_phone: data.user_phone || '',
        user_address: data.user_address || '',
      };
      orderItems.value = data.items || [];
    })
    .catch((res) => {
      ElMessage.error(res.msg || '获取发货详情失败');
    })
    .finally(() => {
      pageLoading.value = false;
    });
}

/**
 * 获取快递公司列表
 */
function loadExpressList() {
  request
    .get('normal/order/express_list')
    .then((res) => {
      expressList.value = res.data || [];
    })
    .catch(() => {
      // 静默处理，不影响主流程
    });
}

/**
 * 获取送货员列表
 */
function loadDeliveryPersons() {
  request
    .get('normal/order/shipment/delivery_persons')
    .then((res) => {
      deliveryPersonList.value = res.data || [];
    })
    .catch(() => {
      // 静默处理，不影响主流程
    });
}

/**
 * 配送类型切换
 */
function handleDeliveryTypeChange() {
  formRef.value && formRef.value.clearValidate();
}

/**
 * 送货人选择变化
 */
function handleDeliveryPersonChange(value) {
  if (!value) return;
  const selected = deliveryPersonList.value.find((item) => item.id === value);
  if (selected) {
    formData.value.sh_delivery_name = selected.wx_name;
    formData.value.sh_delivery_id = selected.phone;
    formData.value.sh_delivery_uid = selected.uid || selected.staff_id || 0;
  }
}

/**
 * 提交发货
 */
function handleSubmit() {
  const deliveryType = formData.value.delivery_type;

  // 根据发货类型做前置校验
  if (deliveryType === 'express') {
    if (!formData.value.delivery_name) {
      return ElMessage.error('请选择快递公司');
    }
    if (formData.value.express_record_type === 2) {
      // 电子面单：单号由下单后回填，校验模板与寄件人信息
      if (!formData.value.express_temp_id) {
        return ElMessage.error('请选择电子面单模板');
      }
      if (!formData.value.to_name) {
        return ElMessage.error('请输入寄件人姓名');
      }
      if (!formData.value.to_tel) {
        return ElMessage.error('请输入寄件人电话');
      }
      if (!formData.value.to_addr) {
        return ElMessage.error('请输入寄件人地址');
      }
    } else if (!formData.value.delivery_id) {
      return ElMessage.error('请输入快递单号');
    }
  } else if (deliveryType === 'send') {
    if (!formData.value.sh_delivery) {
      return ElMessage.error('请选择送货人');
    }
  }

  // 组装提交数据
  const submitData = {
    order_no: props.orderNo,
    delivery_type: deliveryType,
  };

  if (deliveryType === 'express') {
    submitData.delivery_name = formData.value.delivery_name;
    if (formData.value.express_record_type === 2) {
      submitData.express_record_type = 2;
      submitData.delivery_code = formData.value.delivery_code;
      submitData.express_temp_id = formData.value.express_temp_id;
      submitData.to_name = formData.value.to_name;
      submitData.to_tel = formData.value.to_tel;
      submitData.to_addr = formData.value.to_addr;
    } else {
      submitData.delivery_id = formData.value.delivery_id;
    }
  } else if (deliveryType === 'send') {
    submitData.sh_delivery_name = formData.value.sh_delivery_name;
    submitData.sh_delivery_id = formData.value.sh_delivery_id;
    submitData.sh_delivery_uid = formData.value.sh_delivery_uid;
  } else if (deliveryType === 'fictitious') {
    submitData.fictitious_content = formData.value.fictitious_content;
  }

  submitLoading.value = true;
  request
    .post('normal/order/shipment', submitData)
    .then((res) => {
      // 电子面单发货带回运单号
      const waybillNo = res.data?.delivery_id || '';
      ElMessage.success(waybillNo ? `发货成功，运单号：${waybillNo}` : res.msg || '发货成功');
      dialogVisible.value = false;
      emit('success');
    })
    .catch((res) => {
      ElMessage.error(res.msg || '发货失败');
    })
    .finally(() => {
      submitLoading.value = false;
    });
}

/**
 * 取消
 */
function handleCancel() {
  dialogVisible.value = false;
  emit('close');
}

/**
 * 弹窗关闭后清理
 */
function handleClosed() {
  emit('close');
  resetForm();
}

/**
 * 重置表单数据
 */
function resetForm() {
  formData.value = getDefaultForm();
  userInfo.value = {};
  orderItems.value = [];
  pageLoading.value = false;
  submitLoading.value = false;
}

/**
 * 复制用户信息
 */
function handleCopyUserInfo() {
  const text =
    '用户姓名：' +
    (userInfo.value.real_name || '') +
    '\n用户电话：' +
    (userInfo.value.user_phone || '') +
    '\n用户地址：' +
    (userInfo.value.user_address || '');
  proxy
    .$copyText(text)
    .then(() => {
      ElMessage.success('复制成功');
    })
    .catch(() => {
      ElMessage.error('复制失败');
    });
}
</script>

<style scoped lang="scss">
.order-send-dialog {
  .mb20 {
    margin-bottom: 20px;
  }
  .user-info-box {
    p {
      margin: 0;
      line-height: 1.8;
    }
  }
  .copy-box {
    position: absolute;
    top: 10px;
    right: 10px;
  }
  .copy-btn {
    color: #57a3f3;
    cursor: pointer;
    margin-left: 10px;
  }
  .product-cell {
    display: flex;
    align-items: center;
  }
  .product-img {
    width: 50px;
    height: 50px;
    border-radius: 4px;
    margin-right: 10px;
    flex-shrink: 0;
  }
  .product-meta {
    min-width: 0;
  }
  .product-name {
    margin: 0;
    font-size: 13px;
    color: var(--prev-color-text-regular);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .product-sku {
    margin: 2px 0 0;
    font-size: 12px;
    color: var(--prev-color-text-secondary);
  }
}
</style>
