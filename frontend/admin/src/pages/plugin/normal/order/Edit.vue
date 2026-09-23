<template>
  <el-dialog v-model="visible" title="订单编辑" width="720px" @close="handleClose" :close-on-click-modal="false">
    <div v-loading="loading">
      <!-- 订单基本信息 -->
      <div class="order-info" v-if="orderInfo">
        <el-row :gutter="24">
          <el-col :span="12">
            <span class="label">订单号：</span>
            <span class="value">{{ orderNo }}</span>
          </el-col>
          <el-col :span="12">
            <span class="label">订单状态：</span>
            <span class="value">{{ getStatusLabel(orderInfo.status_code) }}</span>
          </el-col>
        </el-row>
        <el-row :gutter="24" style="margin-top: 8px">
          <el-col :span="12">
            <span class="label">商品总价：</span>
            <span class="value">¥{{ orderInfo.total_price || '0.00' }}</span>
          </el-col>
          <el-col :span="12">
            <span class="label">实际支付：</span>
            <span class="value">¥{{ orderInfo.pay_price || '0.00' }}</span>
          </el-col>
        </el-row>
      </div>

      <el-divider></el-divider>

      <!-- 改价表单 -->
      <div class="form-section">
        <div class="section-title">改价</div>
        <el-form ref="priceFormRef" :model="priceData" :rules="priceRules" label-width="120px">
          <el-form-item label="商品总价" prop="total_price">
            <el-input-number
              v-model="priceData.total_price"
              :precision="2"
              :min="0"
              :max="999999.99"
              style="width: 240px"
            />
          </el-form-item>
          <el-form-item label="配送邮费" prop="pay_postage">
            <el-input-number
              v-model="priceData.pay_postage"
              :precision="2"
              :min="0"
              :max="999999.99"
              style="width: 240px"
            />
          </el-form-item>
          <el-form-item label="实际支付" prop="pay_price">
            <el-input-number
              v-model="priceData.pay_price"
              :precision="2"
              :min="0"
              :max="999999.99"
              style="width: 240px"
            />
          </el-form-item>
        </el-form>
      </div>

      <el-divider></el-divider>

      <!-- 积分管理 -->
      <div class="form-section">
        <div class="section-title">积分管理</div>
        <el-form ref="integralFormRef" :model="integralData" label-width="120px">
          <el-form-item label="赠送积分">
            <el-input-number
              v-model="integralData.give_integral"
              :precision="0"
              :min="0"
              :max="999999"
              style="width: 240px"
            />
            <span class="tip">给购买该订单的用户赠送积分</span>
          </el-form-item>
          <el-form-item v-if="orderInfo && orderInfo.use_integral > 0" label="退回积分">
            <el-input-number
              v-model="integralData.back_integral"
              :precision="0"
              :min="0"
              :max="orderInfo.use_integral"
              style="width: 240px"
            />
            <span class="tip"
              >退还用户下单时使用的积分（已退 {{ orderInfo.backed_integral || 0 }}，最多可退
              {{ orderInfo.use_integral }}）</span
            >
          </el-form-item>
        </el-form>
      </div>
    </div>

    <template #footer
      ><div>
        <el-button @click="visible = false">取 消</el-button>
        <el-button type="primary" :loading="saving" @click="saveAll">保 存</el-button>
      </div></template
    >
  </el-dialog>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { ElMessage } from 'element-plus';
import { saveEditOrder, getEditOrderForm, saveOrderIntegral } from '../normalOrder';

defineOptions({ name: 'orderEdit' });

const emit = defineEmits(['saved']);

const priceFormRef = ref(null);
const visible = ref(false);
const loading = ref(false);
const saving = ref(false);
const orderNo = ref('');
const orderInfo = ref(null);
const priceData = ref({
  total_price: 0,
  pay_price: 0,
  pay_postage: 0,
});
const priceRules = {
  total_price: [{ required: true, message: '请输入商品总价', trigger: 'blur' }],
  pay_price: [{ required: true, message: '请输入实际支付', trigger: 'blur' }],
  pay_postage: [{ required: true, message: '请输入配送邮费', trigger: 'blur' }],
};
const integralData = reactive({
  give_integral: 0,
  back_integral: 0,
});

/** 打开弹窗 */
function open(no) {
  orderNo.value = no || '';
  if (!orderNo.value) {
    ElMessage.error('订单号不能为空');
    return;
  }
  resetData();
  loadData();
  visible.value = true;
}

/** 重置表单数据 */
function resetData() {
  priceData.value = { total_price: 0, pay_price: 0, pay_postage: 0 };
  integralData.give_integral = 0;
  integralData.back_integral = 0;
  orderInfo.value = null;
}

/** 加载订单数据 */
async function loadData() {
  loading.value = true;
  try {
    const res = await getEditOrderForm(orderNo.value);
    if (res.status === 200) {
      orderInfo.value = res.data;
      priceData.value = {
        total_price: parseFloat(res.data.total_price || 0),
        pay_price: parseFloat(res.data.pay_price || 0),
        pay_postage: parseFloat(res.data.pay_postage || 0),
      };
      integralData.give_integral = parseInt(res.data.give_integral || 0, 10);
    }
  } catch (e) {
    ElMessage.error('获取订单数据失败');
  } finally {
    loading.value = false;
  }
}

/** 合并保存 */
async function saveAll() {
  try {
    await priceFormRef.value.validate();
  } catch {
    return;
  }
  saving.value = true;
  try {
    // 批量并行保存
    const promises = [];

    // 改价
    promises.push(
      saveEditOrder({
        order_no: orderNo.value,
        total_price: priceData.value.total_price,
        pay_price: priceData.value.pay_price,
        pay_postage: priceData.value.pay_postage,
      }),
    );

    // 积分
    promises.push(
      saveOrderIntegral({
        order_no: orderNo.value,
        give_integral: integralData.give_integral,
        back_integral: integralData.back_integral,
      }),
    );

    await Promise.all(promises);
    ElMessage.success('保存成功');
    visible.value = false;
    emit('saved');
  } catch (e) {
    ElMessage.error(e.msg || '保存失败');
  } finally {
    saving.value = false;
  }
}

function handleClose() {
  visible.value = false;
}

function getStatusLabel(code) {
  const map = {
    pending_payment: '待支付',
    processing: '进行中',
    completed: '已完成',
    canceled: '已取消',
    refunding: '退款中',
    refunded: '已退款',
  };
  return map[code] || code || '--';
}

defineExpose({ open });
</script>

<style scoped>
.order-info {
  background: #f8f8f8;
  padding: 12px 16px;
  border-radius: 4px;
}
.label {
  color: #666;
  font-size: 13px;
}
.value {
  color: #333;
  font-size: 13px;
}
.form-section {
  padding: 0 0;
}
.section-title {
  font-size: 14px;
  font-weight: 600;
  color: #333;
  margin-bottom: 12px;
}
</style>
