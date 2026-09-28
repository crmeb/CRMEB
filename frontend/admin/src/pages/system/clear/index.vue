<template>
  <div>
    <!-- <div class="acea-row row-center clear_tit">
      <el-button type="primary" v-db-click @click="clearCache" class="mr20">清除缓存</el-button>
      <el-button type="primary" v-db-click @click="clearlog">清除日志</el-button>
    </div> -->
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row :gutter="24">
        <el-col v-bind="grid" class="mb20" v-for="(item, index) in tabList" :key="index">
          <div class="clear_box">
            <span class="clear_box_sp1" v-text="item.title"></span>
            <span class="clear_box_sp2" v-text="item.tlt"></span>
            <el-button :type="item.typeName" v-db-click @click="onChange(index)">立即清除</el-button>
          </div>
        </el-col>
      </el-row>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'clear' });

const grid = {
  xl: 6,
  lg: 8,
  md: 12,
  sm: 24,
  xs: 24,
};
const delfromData = ref({});
const tabList = [
  {
    title: '清除缓存',
    tlt: '清除系统的所有缓存',
    typeName: 'primary',
    type: '11',
  },
  {
    title: '清除日志',
    tlt: '清除系统的所有日志文件',
    typeName: 'primary',
    type: 'temp',
  },
];

function onChange(i) {
  if (i) {
    clearlog();
  } else {
    clearCache();
  }
}
function clearCache() {
  let data = {
    title: '清除缓存',
    num: 0,
    url: `system/refresh_cache/cache`,
    method: 'get',
    ids: '',
  };
  modalSure(data)
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function clearlog() {
  let data = {
    title: '清除日志',
    num: 0,
    url: `system/refresh_cache/log`,
    method: 'get',
    ids: '',
  };
  modalSure(data)
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
</script>

<style lang="scss" scoped>
.clear_tit {
  margin-top: 150px;
}
.clear_box {
  border: 1px solid var(--prev-border-color-lighter);
  border-radius: 3px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
  padding: 20px 10px;
  height: 150px;
  box-sizing: border-box;
  .clear_box_sp1 {
    font-size: 16px;
    color: var(--prev-color-text-regular);
    display: block;
  }
  .clear_box_sp2 {
    font-size: 14px;
    color: var(--prev-color-text-secondary);
    display block {
      margin: 12px 0;
    }
  }
}
.clear_box :deep(.ivu-btn-error) {
  color: #fff;
  background-color: #ed4014;
  border-color: #ed4014;
}
</style>
