<template>
  <div class="article-manager">
    <div class="i-layout-page-header header-title">
      <div class="header-main">
        <span class="ivu-page-header-title">电子发票</span>
        <span class="header-description">
          <el-icon class="header-description-icon"><InfoFilled /></el-icon>
          开启发票功能并配置税率与发票分类，用户下单后可在线申请开票。
        </span>
      </div>
    </div>
    <el-card shadow="never" class="ivu-mt">
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        :label-width="labelWidth"
        :label-position="labelPosition"
        v-loading="loading"
      >
        <el-divider content-position="left">发票功能</el-divider>
        <el-form-item label="发票功能启用：">
          <el-radio-group v-model="form.invoice_func_status">
            <el-radio :label="1" :value="1">开启</el-radio>
            <el-radio :label="0" :value="0">关闭</el-radio>
          </el-radio-group>
          <div class="tip">是否开启发票功能，关闭后订单将不可申请开具发票</div>
        </el-form-item>
        <el-form-item label="专用发票启用：" v-if="form.invoice_func_status == 1">
          <el-radio-group v-model="form.special_invoice_status">
            <el-radio :label="1" :value="1">开启</el-radio>
            <el-radio :label="0" :value="0">关闭</el-radio>
          </el-radio-group>
          <div class="tip">是否开启增值税专用发票</div>
        </el-form-item>
        <el-divider content-position="left">电子发票</el-divider>
        <el-form-item label="电子发票状态：">
          <el-radio-group v-model="form.elec_invoice">
            <el-radio :label="1" :value="1">开启</el-radio>
            <el-radio :label="0" :value="0">关闭</el-radio>
          </el-radio-group>
          <div class="tip">是否开启电子发票</div>
        </el-form-item>
        <el-form-item label="电子发票分类：" prop="elec_invoice_cate" v-if="form.elec_invoice == 1">
          <el-select
            v-model="form.elec_invoice_cate"
            class="input-width"
            filterable
            remote
            reserve-keyword
            :remote-method="remoteMethod"
            :loading="cateLoading"
            placeholder="请输入并选择电子发票的商品分类"
            @change="onCateChange"
          >
            <el-option v-for="item in cateOptions" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
          <div class="tip">电子发票需输入搜索并选择商品分类，如：电子产品、电子服务等</div>
        </el-form-item>
        <el-form-item label="电子发票税率：" prop="elec_invoice_tax_rate" v-if="form.elec_invoice == 1">
          <el-input-number
            v-model="form.elec_invoice_tax_rate"
            :min="0"
            :max="100"
            :precision="0"
            controls-position="right"
          />
          <span class="ml10">%</span>
          <div class="tip">
            默认填充税率可能存在误差，请确认无误后再保存；填写0-100之间的整数，如：13%的税率请填写13
          </div>
        </el-form-item>
        <el-form-item label="是否自动开票：">
          <el-radio-group v-model="form.auto_invoice">
            <el-radio :label="1" :value="1">开启</el-radio>
            <el-radio :label="0" :value="0">关闭</el-radio>
          </el-radio-group>
          <div class="tip">是否开启自动开票功能，开启后需先开启电子发票状态</div>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :loading="saveLoading" v-db-click @click="onSave">保存</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { elecInvoiceApi, saveElecInvoiceApi } from '@/api/setting';
import { invoiceCategory } from '@/api/order';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'setting_elecInvoice' });

const mediaStore = useMediaStore();
const labelWidth = computed(() => (mediaStore.isMobile ? undefined : '140px'));
const labelPosition = computed(() => (mediaStore.isMobile ? 'top' : 'right'));

const loading = ref(false);
const saveLoading = ref(false);
const formRef = ref(null);
// 电子发票分类（一号通税务类目）远程搜索选项，item 结构：{ id, name, tax_rate_num, ... }
const cateOptions = ref([]);
const cateLoading = ref(false);

const form = reactive({
  invoice_func_status: 0, // 发票功能启用
  special_invoice_status: 0, // 专用发票启用
  elec_invoice: 0, // 电子发票状态
  auto_invoice: 0, // 是否自动开票
  elec_invoice_cate: '', // 电子发票分类（一号通类目 id）
  elec_invoice_tax_rate: 0, // 电子发票税率
  elec_invoice_cate_name: '', // 发票分类名称
});

const rules = {
  elec_invoice_tax_rate: [
    {
      required: true,
      message: '请输入电子发票税率',
      validator: (rule, value, callback) => {
        form.elec_invoice == 1 && (value === null || value === undefined || value < 0 || value > 100)
          ? callback(new Error('税率必须在0-100之间'))
          : callback();
      },
      trigger: 'blur',
    },
  ],
};

// 远程搜索一号通电子发票商品分类
function remoteMethod(query) {
  if (query !== '') {
    cateLoading.value = true;
    invoiceCategory({ name: query })
      .then((res) => {
        cateLoading.value = false;
        // 接口返回分页结构 { list, count }，与 v6 原版一致取 list 并按名称二次过滤
        const list = (res.data && res.data.list) || [];
        cateOptions.value = list.filter((item) => item.name.toLowerCase().indexOf(query.toLowerCase()) > -1);
      })
      .catch(() => {
        cateLoading.value = false;
      });
  } else {
    cateOptions.value = [];
  }
}

// 选择分类后同步分类名称，并自动带出该类目的税率
function onCateChange(value) {
  const item = cateOptions.value.find((o) => o.id === value);
  form.elec_invoice_cate_name = item ? item.name : '';
  if (item && item.tax_rate_num !== undefined && item.tax_rate_num !== null) {
    form.elec_invoice_tax_rate = item.tax_rate_num;
  }
}

// 获取发票配置
function getConfig() {
  loading.value = true;
  elecInvoiceApi()
    .then((res) => {
      Object.keys(form).forEach((key) => {
        if (res.data[key] !== null && res.data[key] !== undefined) form[key] = res.data[key];
      });
      form.elec_invoice_cate = res.data.elec_invoice_cate || '';
      // 已选分类塞入选项用于回显
      const { elec_invoice_cate: cateId, elec_invoice_cate_name: cateName } = res.data;
      if (cateId) cateOptions.value = [{ id: cateId, name: cateName }];
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg || '获取配置失败');
    });
}

// 保存
function onSave() {
  formRef.value.validate((valid) => {
    if (!valid) return;
    saveLoading.value = true;
    saveElecInvoiceApi({ ...form })
      .then((res) => {
        saveLoading.value = false;
        ElMessage.success(res.msg || '保存成功');
      })
      .catch((res) => {
        saveLoading.value = false;
        ElMessage.error(res.msg || '保存失败');
      });
  });
}

onMounted(() => {
  getConfig();
});
</script>

<style lang="scss" scoped>
.tip {
  font-size: 12px;
  color: #999;
  line-height: 20px;
  // 独占一行，使说明显示在控件下方
  flex-basis: 100%;
  width: 100%;
}
.ml10 {
  margin-left: 10px;
}
.input-width {
  width: 300px;
}
</style>
