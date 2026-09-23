<template>
  <div>
    <pages-header ref="pageHeader" :title="$route.meta.title"></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-form :model="formData" label-width="100px">
        <el-form-item label="积分抽奖：">
          <el-select v-model="formData.point" clearable class="content_width">
            <el-option v-for="(item, j) in list.point" :value="item.id" :key="item.id" :label="item.name"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="支付抽奖：">
          <el-select v-model="formData.pay" clearable class="content_width">
            <el-option v-for="(item, j) in list.pay" :value="item.id" :key="item.id" :label="item.name"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="评价抽奖：">
          <el-select v-model="formData.evaluate" clearable class="content_width">
            <el-option
              v-for="(item, j) in list.evaluate"
              :value="item.id"
              :key="item.id"
              :label="item.name"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" v-db-click @click="save">保存</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { factorListApi, factorUseApi } from '../api';

defineOptions({ name: 'lotteryConfig' });

const formData = ref({
  evaluate: '', // 评价支付
  pay: '', // 支付
  point: '', // 积分
});
const list = ref({
  evaluate: [], // 评价支付
  pay: [], // 支付
  point: [], // 积分
});

onMounted(() => {
  getList();
});

function getList() {
  factorListApi().then((res) => {
    list.value = res.data;
    formData.value = res.data.info;
  });
}
function save() {
  factorUseApi(formData.value).then((res) => {
    ElMessage.success(res.msg);
  });
}
</script>

<style scoped lang="scss">
.content_width {
  width: 414px;
}
</style>
