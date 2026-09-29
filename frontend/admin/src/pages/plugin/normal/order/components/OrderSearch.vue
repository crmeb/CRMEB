<template>
  <el-card shadow="never" :body-style="{ padding: 0 }">
    <div class="padding-add">
      <el-form ref="searchFormRef" :model="formData" label-width="80px" label-position="right" inline @submit.prevent>
        <el-form-item label="订单号：">
          <el-input
            v-model="formData.order_no"
            placeholder="订单号"
            clearable
            @keyup.enter="handleSearch"
            class="form_content_width"
          />
        </el-form-item>
        <el-form-item label="订单类型：">
          <el-select
            v-model="formData.type"
            clearable
            @change="handleSearch"
            placeholder="全部"
            class="form_content_width"
          >
            <el-option label="全部订单" value="" />
            <el-option label="普通订单" value="1" />
            <el-option label="拼团订单" value="2" />
            <el-option label="秒杀订单" value="3" />
            <el-option label="砍价订单" value="4" />
            <el-option label="预售订单" value="5" />
          </el-select>
        </el-form-item>
        <el-form-item label="支付方式：">
          <el-select
            v-model="formData.pay_type"
            clearable
            @change="handleSearch"
            placeholder="全部"
            class="form_content_width"
          >
            <el-option label="全部" value="" />
            <el-option v-for="item in payTypeOptions" :key="item.key" :value="item.key" :label="item.label" />
          </el-select>
        </el-form-item>
        <el-form-item label="创建时间：">
          <date-range-picker
            width="250px"
            clearable
            v-model="timeVal"
            @change="onchangeTime"
            format="YYYY/MM/DD"
            value-format="YYYY-MM-DD"
          ></date-range-picker>
        </el-form-item>
        <el-form-item label="订单搜索：">
          <el-input
            clearable
            v-model="formData.keyword"
            placeholder="订单号/联系人/UID"
            class="form_content_width"
            @keyup.enter="handleSearch"
          >
            <template #prepend
              ><el-select v-model="formData.search_field" style="width: 100px">
                <el-option value="all" label="全部"></el-option>
                <el-option value="order_no" label="订单号"></el-option>
                <el-option value="contact_name" label="联系人"></el-option>
                <el-option value="contact_phone" label="联系电话"></el-option>
                <el-option value="uid" label="UID"></el-option> </el-select
            ></template>
          </el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" v-db-click @click="handleSearch">查询</el-button>
          <el-button v-db-click @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
  </el-card>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';

/**
 * 实物商品订单 - 搜索栏组件
 * 提供订单号/支付方式/创建时间/关键词等多维度搜索
 */
defineOptions({ name: 'OrderSearch' });

const props = defineProps({
  /** 搜索初始值 */
  value: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['search', 'reset']);

const searchFormRef = ref(null);

const defaultSearch = () => ({
  page: 1,
  limit: 15,
  order_no: '',
  keyword: '',
  search_field: 'all',
  date_range: '',
  pay_type: '',
  type: '',
});

const formData = reactive(defaultSearch());
const timeVal = ref([]);
const payTypeOptions = [
  { key: 'weixin', label: '微信支付' },
  { key: 'yue', label: '余额支付' },
  { key: 'alipay', label: '支付宝' },
  { key: 'offline', label: '线下支付' },
];

watch(
  () => props.value,
  (val) => {
    if (val && Object.keys(val).length) {
      Object.assign(formData, val);
    }
  },
  { immediate: true },
);

/**
 * 触发搜索，重置到第一页
 */
function handleSearch() {
  formData.page = 1;
  emit('search', { ...formData, date_range: formData.date_range });
}

/**
 * 重置搜索条件
 */
function handleReset() {
  Object.assign(formData, defaultSearch());
  timeVal.value = [];
  emit('search', { ...formData });
  emit('reset');
}

/**
 * 时间范围变化
 */
function onchangeTime(e) {
  timeVal.value = e || [];
  formData.date_range = timeVal.value[0] ? timeVal.value.join(',') : '';
  handleSearch();
}
</script>
