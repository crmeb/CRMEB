<template>
  <div class="coupon-setting">
    <el-form :model="couponForm" label-width="120px" label-position="right">
      <el-form-item label="优惠券面值：" prop="coupon_value">
        <el-input-number
          :controls="false"
          :min="0"
          v-model="couponForm.coupon_value"
          placeholder="请输入面值"
          class="input-number-unit-class"
          class-unit="元"
        ></el-input-number>
      </el-form-item>
      <el-form-item label="优惠门槛：" prop="coupon_min">
        <el-input-number
          :controls="false"
          :min="0"
          v-model="couponForm.coupon_min"
          placeholder="请输入门槛金额"
          class="input-number-unit-class"
          class-unit="元"
        ></el-input-number>
      </el-form-item>
      <el-form-item label="有效期：" prop="coupon_start_time">
        <el-date-picker
          v-model="couponForm.coupon_start_time"
          type="date"
          placeholder="开始日期"
          style="width: 48%"
        ></el-date-picker>
        <span style="margin: 0 10px">至</span>
        <el-date-picker
          v-model="couponForm.coupon_end_time"
          type="date"
          placeholder="结束日期"
          style="width: 48%"
        ></el-date-picker>
      </el-form-item>
      <el-form-item label="使用范围：" prop="coupon_scope">
        <el-radio-group v-model="couponForm.coupon_scope">
          <el-radio :label="0" :value="0">全店通用</el-radio>
          <el-radio :label="1" :value="1">指定分类</el-radio>
          <el-radio :label="2" :value="2">指定商品</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item label="指定分类：" prop="coupon_cate_id" v-if="couponForm.coupon_scope === 1">
        <el-cascader
          v-model="couponForm.coupon_cate_id"
          :options="treeSelect"
          :props="{ multiple: true, emitPath: false, checkStrictly: true }"
          clearable
        ></el-cascader>
      </el-form-item>
      <el-form-item label="指定商品：" prop="coupon_product_id" v-if="couponForm.coupon_scope === 2">
        <el-select
          v-model="couponForm.coupon_product_id"
          multiple
          clearable
          placeholder="请选择商品"
          style="width: 100%"
        >
        </el-select>
      </el-form-item>
      <el-form-item label="每人限领：" prop="coupon_limit">
        <el-input-number
          :controls="false"
          :min="0"
          v-model="couponForm.coupon_limit"
          placeholder="0表示不限"
        ></el-input-number>
        <span>张</span>
      </el-form-item>
    </el-form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { cascaderListApi } from '@/api/product';

defineOptions({ name: 'couponSetting' });

const props = defineProps({
  productId: {
    type: Number,
    default: 0,
  },
});

const treeSelect = ref([]);
const couponForm = ref({
  coupon_value: 0,
  coupon_min: 0,
  coupon_start_time: '',
  coupon_end_time: '',
  coupon_scope: 0,
  coupon_cate_id: [],
  coupon_product_id: [],
  coupon_limit: 0,
});

loadCategory();
if (props.productId) {
  loadData();
}

async function loadCategory() {
  try {
    const res = await cascaderListApi(1);
    treeSelect.value = res.data;
  } catch (error) {
    ElMessage.error('加载分类失败');
  }
}
async function loadData() {
  try {
  } catch (error) {
    ElMessage.error('加载失败');
  }
}
function getFormData() {
  return { ...couponForm.value };
}

defineExpose({ getFormData });
</script>

<style scoped>
.coupon-setting {
  padding: 20px;
}
</style>
